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
      require_once dirname( __DIR__ ) . '/freemius/start.php';

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
      // Free build) owns the funnel and links out to the site. Consequence: the
      // 3-day trial is started from the site/hosted checkout, not from wp-admin.
      $themeasy_fs->add_filter( 'is_pricing_page_visible', '__return_false' );

      // Every SDK link to its pricing page (the Account page's Upgrade buttons)
      // goes to the Themeasy site too. This filter is documented in
      // admin/class-upgrade-page.php.
      $themeasy_fs->add_filter(
        'pricing_url',
        static function () {
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
      // SDK's in-WP screens (owner decision). Hide the SDK Pricing submenu item
      // (mirrors the theme's theme_fs() config) and the Account one, with a single
      // exception: an install that holds its OWN plugin license and no full offer
      // (the Widgets plan) keeps the Account page under the Themeasy menu. Its
      // buyer has no theme wizard, and the Hub cannot release a subscription's
      // sites yet, so that page is the only place to deactivate or change the
      // license (backlog #262). This hides menu items only — the SDK keeps working,
      // so a SaaS subscriber's license still syncs.
      $themeasy_fs->add_filter(
        'is_submenu_visible',
        static function ( $is_visible, $menu_id ) {
          if ( 'account' === $menu_id ) {
            return \Themeasy\Core\Entitlement::has_own_license() && !\Themeasy\Core\Entitlement::can_use_premium();
          }

          return 'pricing' === $menu_id ? false : $is_visible;
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

      if ( !$fs || $fs->is_registered() ) {
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
}
