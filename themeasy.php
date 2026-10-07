<?php
/**
 * Plugin Name:        Themeasy Lite
 * Plugin URI:         https://themeasy.co/features/
 * Description:        Free Elementor content widgets and design controls that work with any WordPress theme.
 * Version:            1.0.1
 * Requires at least:  6.8
 * Requires PHP:       7.4
 * Requires Plugins:   elementor
 * Author:             Themeasy
 * Author URI:         https://themeasy.co
 * License:            GPL-2.0-or-later
 * License URI:        https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:        themeasy-lite
 */

namespace Themeasy;

defined( 'ABSPATH' ) || exit;

use Themeasy\Admin\Welcome_Page;
use Themeasy\Core\Core_Loader;
use Themeasy\Elementor\Elementor_Loader;

// Free/premium pair (the Freemius set_basename() pattern, backlog #268). Both builds
// ship this file. When the other one (the Lite or the premium) is active, it has
// already booted the SDK and declared Themeasy\Themeasy: this copy hands its
// basename to that SDK, which deactivates the other build when this one is
// activated, and declares nothing (a second Themeasy class is a fatal that makes
// WordPress refuse the activation). The plugin lives in the else block, never at
// the top level: PHP binds a top-level class as soon as an opcache-cached file
// loads, before an early return could run. The strip flips the true to false in
// the Free build, as the Freemius processor does.
if ( function_exists( 'themeasy_fs' ) ) {
  themeasy_fs()->set_basename( false, __FILE__ );
} else {
  // Boot the licensing layer (defines the global themeasy_fs() accessor and inits
  // the embedded SDK) before the plugin instantiates, so it can register its hooks
  // early. Non-namespaced because themeasy_fs()/fs_dynamic_init() are global.
  require_once __DIR__ . '/core/licensing.php';

  final class Themeasy {
    /**
     * Singleton instance.
     *
     * @var Themeasy|null
     */
    private static $instance = null;

    /**
     * Retrieves the singleton instance.
     *
     * @return Themeasy
     */
    public static function instance(): Themeasy {
      if ( self::$instance === null ) {
        self::$instance = new self();
      }
      return self::$instance;
    }

    /**
     * Themeasy constructor.
     */
    private function __construct() {
      $this->define_constants();

      // Register the SPL autoloader for the Themeasy\ namespace before any class is
      // referenced, so the manual require_once chains below are a convenience, not a
      // load-order dependency (a missed/reordered require still resolves).
      require_once THEMEASY_PATH . 'core/autoload.php';

      $this->add_hooks();
    }

    /**
     * Defines plugin constants.
     */
    private function define_constants(): void {
      define( 'THEMEASY', true );

      // Single source of truth: read the version from the plugin header so it never
      // drifts from the distributed metadata.
      $headers = get_file_data( __FILE__, ['Version' => 'Version'] );
      define( 'THEMEASY_VER', !empty( $headers['Version'] ) ? $headers['Version'] : '1.0.0' );

      define( 'THEMEASY_SLUG', 'themeasy' );
      define( 'THEMEASY_BASENAME', plugin_basename( __FILE__ ) );
      define( 'THEMEASY_PATH', plugin_dir_path( __FILE__ ) );
      define( 'THEMEASY_URL', plugin_dir_url( __FILE__ ) );
      define( 'THEMEASY_ASSETS_URL', plugins_url( 'assets/', __FILE__ ) );
    }

    /**
     * Registers plugin hooks.
     */
    private function add_hooks(): void {
      add_action( 'after_setup_theme', [$this, 'maybe_init'], 20 );
    }

    /**
     * Boots the plugin: an Elementor widgets addon that runs on any theme.
     *
     * Runs on after_setup_theme, so the theme has registered its supports by
     * then. Loads the widget runtime, the admin page behind the Themeasy menu
     * and the content widgets.
     */
    public function maybe_init(): void {
      $this->load_widget_runtime();

      $this->load_admin_page();
      $this->load_elementor_widgets();
    }

    /**
     * Loads the always-on widget runtime: helpers, the asset registrar, the
     * settings-read layer, and the SVG sanitizer (render path). This is the shared
     * closure every content widget renders against.
     */
    private function load_widget_runtime(): void {
      require_once THEMEASY_PATH . 'core/class-core-loader.php';
      Core_Loader::load_widget_runtime();
    }

    /**
     * Loads the admin page behind the Themeasy menu: the Welcome page, a Getting
     * Started guide that showcases the template library and the paid plans.
     */
    private function load_admin_page(): void {
      require_once THEMEASY_PATH . 'admin/class-welcome-page.php';

      Welcome_Page::init();
    }

    /**
     * Loads the Elementor widget plumbing: the categories, the content widgets
     * and the editor helpers.
     *
     * Boot-order contract: this runs on after_setup_theme, after every active
     * plugin's file has loaded — so `elementor/loaded` (fired while Elementor's
     * file is included) is normally long past, and the boot runs inline. When it
     * has NOT fired (Elementor inactive, or activated programmatically later in
     * this same request), defer to the action instead of silently dropping the
     * integration: if Elementor never loads, the callback simply never runs —
     * same net effect as the old bail, without the timing assumption.
     */
    private function load_elementor_widgets(): void {
      $boot = static function (): void {
        require_once THEMEASY_PATH . 'includes/elementor/class-elementor-loader.php';
        Elementor_Loader::instance()->init();
      };

      if ( did_action( 'elementor/loaded' ) ) {
        $boot();
        return;
      }
      add_action( 'elementor/loaded', $boot );
    }
  }

  // Initialize the plugin.
  Themeasy::instance();
}
