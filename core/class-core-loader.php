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
   * Load the widget runtime.
   *
   * The shared closure every content widget renders against: the helper layer
   * (themeasy_* functions), the SVG sanitizer (its static render path is
   * reached by themeasy_render_icon_html()), and the asset registrar (vendors,
   * modules, the core CSS bundles, and auto-registered widget assets).
   *
   * @return void
   */
  public static function load_widget_runtime(): void {
    require_once THEMEASY_PATH . 'core/helpers.php';
    require_once THEMEASY_PATH . 'core/class-svg-sanitizer.php';
    require_once THEMEASY_PATH . 'core/enqueue-assets.php';
  }
}
