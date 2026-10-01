<?php
/**
 * Core Loader
 *
 * Loads and initializes core components of the Themeasy plugin.
 *
 * @package Themeasy\Core
 * @since 1.0.0
 */

namespace Themeasy\Core;

defined( 'ABSPATH' ) || exit;

class Core_Loader {
  /**
   * Load the Free widget runtime.
   *
   * The shared closure every Free content widget renders against: the helper
   * layer (themeasy_* functions), the SVG sanitizer (its static render path is
   * reached by themeasy_render_icon_html()), the settings-read layer
   * (Settings::get* — consumed on the frontend by widgets and by the loader's
   * widget_{slug} toggle), and the asset registrar (vendors, modules, the core
   * CSS/JS bundles, and auto-registered widget assets).
   *
   * Ships in BOTH builds (no premium marker). The settings UI, customizer,
   * CPTs, and SVG-upload hooks live in init__premium_only().
   *
   * The premium asset half (GSAP, the core JS runtime, the slider module, the
   * Pro widgets' assets) loads on the same gate as the Pro widget tree —
   * can_use_widgets(), not the theme-gated premium boot — because a Pro widget
   * registers on entitlement alone and must find its assets.
   *
   * @return void
   */
  public static function load_widget_runtime(): void {
    require_once TMS_PATH . 'core/helpers.php';
    require_once TMS_PATH . 'core/class-svg-sanitizer.php';
    require_once TMS_PATH . 'admin/settings/class-settings.php';
    require_once TMS_PATH . 'core/enqueue-assets.php';

    if ( Entitlement::can_use_widgets() ) {
    }
  }


  /**
   * Initialize the Free content features.
   *
   * Promoted out of premium core in the extensions__premium_only split: the
   * Portfolio CPT and the attachment meta fields (category / custom link /
   * video URL) back the Free gallery and image widgets, so they load on the
   * always-on Free baseline. Self-contained — no premium coupling.
   *
   * Ships in BOTH builds (no premium marker). Called from the Free baseline in
   * Themeasy::maybe_init().
   *
   * @return void
   */
  public static function init_free_content(): void {
    require_once TMS_PATH . 'core/class-custom-media-meta-fields.php';
    require_once TMS_PATH . 'core/class-portfolio-post-type.php';

    Custom_Media_Meta_Fields::instance();
    Portfolio_Post_Type::instance();
  }

}
