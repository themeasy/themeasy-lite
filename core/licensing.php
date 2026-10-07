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
 *
 * This file boots the SDK and routes its screens: the opt-in, the deactivation
 * feedback form and the upgrade links. It reads no license.
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
      // plugin-row upgrade link entirely; the branded Themeasy page owns the
      // funnel and links out to the hosted checkouts. Consequence: the 3-day
      // trial is started from the site/hosted checkout, not from wp-admin.
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

      // Account messaging is owned by the Themeasy site + the Themeasy Hub: the
      // SDK's own admin notices duplicate or contradict it. Hide all.
      $themeasy_fs->add_filter( 'show_admin_notice', '__return_false' );

      // On an install that never opted in, the deactivation feedback form starts
      // with "Anonymous feedback" ticked. The SDK default leaves it unticked, and
      // an answer then opts the whole site in when the plugin is deleted (name,
      // email, site URL, plugin list). Now only a user who unticks it opts in
      // (backlog #267; readme-lite.txt → External services).
      $themeasy_fs->add_filter( 'default_to_anonymous_feedback', '__return_true' );

      // Account management lives on the Themeasy site + the Hub, never the SDK's
      // in-WP screens (owner decision, backlogs #262, #288). Hide the SDK Pricing
      // and Account submenu items, as the theme's theme_fs() config does. This
      // hides menu items only: the SDK keeps working.
      $themeasy_fs->add_filter(
        'is_submenu_visible',
        static function ( $is_visible, $menu_id ) {
          return in_array( $menu_id, ['account', 'pricing'], true ) ? false : $is_visible;
        },
        10,
        2
      );
    }

    return $themeasy_fs;
  }

  // Boot the SDK and signal dependents that it is ready.
  themeasy_fs();
  do_action( 'themeasy_fs_loaded' );
}
