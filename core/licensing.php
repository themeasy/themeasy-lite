<?php
/**
 * Licensing bootstrap.
 *
 * Defines the global themeasy_fs() accessor and initializes the embedded
 * licensing SDK before the plugin's own logic runs, so it can register its hooks
 * early. This file is intentionally NOT namespaced: themeasy_fs() and
 * fs_dynamic_init() must live in the global namespace.
 *
 * The id and public_key are the product's PUBLIC credentials (embedded in the
 * shipped build); the secret_key is never referenced here and must never ship.
 * Only the Entitlement facade consumes the instance this returns.
 *
 * @package Themeasy\Core
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( !function_exists( 'themeasy_fs' ) ) {
  /**
   * Boots the embedded Freemius SDK once and returns the shared instance.
   *
   * @return Freemius
   */
  function themeasy_fs() {
    global $themeasy_fs;

    if ( !isset( $themeasy_fs ) ) {
      // Include the vendored Freemius SDK (lives at the plugin root, one level up from core/).
      require_once dirname( __DIR__ ) . '/vendor/freemius/start.php';

      $themeasy_fs = fs_dynamic_init( [
        'id' => '31006',
        'slug' => 'themeasy-lite',
        'premium_slug' => 'themeasy',
        'type' => 'plugin',
        'public_key' => 'pk_8b0f293631eafa2c58b0ca66ea13c',
        'is_premium' => false,
        'has_premium_version' => true,
        // wp.org gatekeeper: authorizes only the Freemius-generated Free build
        // for the themeasy-lite slug. Stripped from the Free build (Freemius
        // server-side + strip.mjs), so the secret never reaches wordpress.org.
        'has_addons' => false,
        'has_paid_plans' => true,
        'trial' => [
          'days' => 3,
          'is_require_payment' => true,
        ],
        'menu' => [
          'slug' => 'themeasy',
          'first-path' => 'admin.php?page=themeasy',
          'account' => true,
          'contact' => false,
          'support' => false,
          'pricing' => true,
        ],
        'is_org_compliant' => true,
        'anonymous_mode' => true,
      ] );

      // The Upgrade/Pricing funnel routes to the Themeasy site, never the SDK's
      // in-WP pricing (owner decision). Hide the Freemius pricing page AND the
      // plugin-row upgrade link entirely; the branded Upgrade page (Upgrade_Page,
      // Free build) owns the funnel and links out to the hosted checkouts.
      // Consequence: the 3-day trial is started from the site/hosted checkout,
      // not from wp-admin.
      $themeasy_fs->add_filter( 'is_pricing_page_visible', '__return_false' );

      // Every SDK link to its pricing page (the hidden Account page's Upgrade
      // buttons) goes to the Themeasy site too.
      $themeasy_fs->add_filter(
        'pricing_url',
        static function () {
          /**
           * Filter the Themeasy pricing page: where the SDK's pricing links go, and
           * the Library's locks when they do not open a checkout.
           *
           * @param string $url The pricing page on the Themeasy site.
           */
          return (string) apply_filters( 'themeasy/upgrade_url', 'https://themeasy.co/pricing' );
        }
      );

      // License/account messaging is owned by the theme wizard + the Themeasy
      // Hub — the SDK's own admin notices duplicate or contradict it. Hide all.
      $themeasy_fs->add_filter( 'show_admin_notice', '__return_false' );

      // On an install that never opted in, the deactivation feedback form starts
      // with "Anonymous feedback" ticked. The SDK default leaves it unticked, and
      // an answer then opts the whole site in when the plugin is deleted (name,
      // email, site URL, plugin list). Now only a user who unticks it opts in
      // (backlog #267; readme-lite.txt → External services).
      $themeasy_fs->add_filter( 'default_to_anonymous_feedback', '__return_true' );

      // License/account management lives on the Themeasy site + the Hub, never the
      // SDK's in-WP screens (owner decision). Hide the SDK Pricing and Account
      // submenu items, as the theme's theme_fs() config does: the Hub shows a
      // plugin subscription's key and sites and releases them, and the Themeasy
      // page's "Manage License" links there on every plan (backlogs #262, #288).
      // This hides menu items only — the SDK keeps working, so a SaaS
      // subscriber's license still syncs.
      $themeasy_fs->add_filter(
        'is_submenu_visible',
        static function ( $is_visible, $menu_id ) {
          return in_array( $menu_id, ['account', 'pricing'], true ) ? false : $is_visible;
        },
        10,
        2
      );

      // Freemius adds an "Activate License" / "Change License" action link on the
      // plugins screen. That popup is the genuine activation path ONLY for a
      // standalone plugin install on a third-party theme (the SaaS cohort). On a
      // Themeasy theme the license flow lives in the theme's setup wizard + the
      // Hub (M2: the plugin goes premium via the licensed theme), so the link is
      // a confusing dead-end there — strip it whenever a Themeasy theme is
      // active OR the site is already entitled. A plugin-plan buyer on a
      // Themeasy theme activates from the Themeasy page instead, whose "Plugin
      // license" block opens the same SDK dialog (backlog #271). Priority 100 so
      // it runs after Freemius has added the link.
      add_filter(
        'plugin_action_links_' . plugin_basename( dirname( __DIR__ ) . '/themeasy.php' ),
        static function ( $links ) {
          if ( !is_array( $links ) ) {
            return $links;
          }

          if ( !current_theme_supports( 'themeasy-compatible' ) && !\Themeasy\Core\Entitlement::can_use_widgets() ) {
            return $links;
          }

          foreach ( $links as $key => $html ) {
            // Freemius registers the link under the array key
            // "activate-license {affix}" for both the Activate and Change
            // variants — the anchor itself carries no identifying class
            // (see Freemius::_modify_plugin_action_links_hook()).
            if ( is_string( $key ) && 0 === strpos( $key, 'activate-license' ) ) {
              unset( $links[$key] );
            }
          }

          return $links;
        },
        100
      );
    }

    return $themeasy_fs;
  }

  // Boot the SDK and signal dependents that it is ready.
  themeasy_fs();
  do_action( 'themeasy_fs_loaded' );

  // One-shot: consume a license key handed over by the theme setup wizard (D4).
  // On a SaaS-bundle site this activates the plugin's OWN Freemius SDK so it
  // becomes a real subscriber; a theme-avulso key fails silently here (M2 still
  // carries premium via the theme signal). Deferred to admin_init because the
  // SDK needs the admin context; priority 20 runs after Freemius' own
  // priority-10 admin_init handlers. The option is deleted BEFORE the attempt so
  // a bad key can never retry-loop on every request. Harmless and inert in the
  // Free build (no wizard ever writes the option there), so it stays unmarked.
  add_action(
    'admin_init',
    static function () {
      // Only an administrator's request takes the key: admin_init also runs on
      // a guest's admin-ajax.php call (the cart count, Quick View), which would
      // consume the option with no one there to own the activation.
      if ( !current_user_can( 'manage_options' ) ) {
        return;
      }

      $key = get_option( 'themeasy_pending_license', '' );

      if ( '' === $key || !is_string( $key ) ) {
        return;
      }

      delete_option( 'themeasy_pending_license' );

      // The key is an opaque secret, and Freemius keys carry ?, +, %, @ and #:
      // sanitize_text_field() would strip a %xx run or a tag and hand the SDK a
      // different key. Validate the shape instead (printable ASCII, 8-128 chars,
      // the themeasy-library License_Gate::clean_key() rule) and pass the key
      // through byte for byte; anything else is dropped (backlog #270).
      if ( !preg_match( '/\A[\x21-\x7E]{8,128}\z/', $key ) ) {
        return;
      }

      $fs = function_exists( 'themeasy_fs' ) ? themeasy_fs() : null;

      // An install with its own license keeps it: the key would replace it. A
      // registered one without a license (opted in, or its license released or
      // expired) takes the key like an anonymous one (backlog #306).
      if ( !$fs || \Themeasy\Core\Entitlement::has_own_license() ) {
        return;
      }

      try {
        $fs->activate_migrated_license( $key );
      } catch ( \Throwable $e ) {
        unset( $e );
      }
    },
    20
  );

  // Clear Elementor's caches when the entitlement level changes (backlog #289).
  // The Element Cache stores a nested widget (Nested Tabs) and the containers as
  // static HTML for up to 24 h, so after a downgrade the Pro markup keeps
  // printing with no CSS or JS, and after an upgrade a cache built on the lower
  // level hides it. Nothing announces the change, so the last level is kept in
  // an option and the first request that sees a new one clears the caches (the
  // element cache and the page CSS, rebuilt on demand), the Color_Sync idiom.
  // elementor/init runs after the theme registered its M2 signal. Inert in the
  // Free build (the level never leaves 'free'), so it stays unmarked.
  add_action(
    'elementor/init',
    static function () {
      if ( \Themeasy\Core\Entitlement::can_use_premium() ) {
        $level = 'full';
      } elseif ( \Themeasy\Core\Entitlement::can_use_widgets() ) {
        $level = 'widgets';
      } else {
        $level = 'free';
      }

      if ( get_option( 'themeasy_entitlement_level' ) === $level ) {
        return;
      }

      update_option( 'themeasy_entitlement_level', $level );
      \Elementor\Plugin::instance()->files_manager->clear_cache();
    }
  );
}
