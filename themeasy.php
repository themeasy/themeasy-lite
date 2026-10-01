<?php
/**
 * Plugin Name:        Themeasy Lite
 * Plugin URI:         https://themeasy.co
 * Description:        Free Elementor content widgets and design controls that work with any WordPress theme.
 * Version:            1.0.1
 * Requires at least:  6.8
 * Tested up to:       7.1
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

use Themeasy\Admin\Admin_Loader;
use Themeasy\Admin\Upgrade_Page;
use Themeasy\Admin\White_Label;
use Themeasy\ContactForm7\Loader as ContactForm7_Loader;
use Themeasy\Core\Core_Loader;
use Themeasy\Core\Entitlement;
use Themeasy\Elementor\Elementor_Loader;
use Themeasy\Global_Sections\Global_Sections;
use Themeasy\Product_Custom_Field\Product_Custom_Field;
use Themeasy\Variation_Swatches\Variation_Swatches;
use Themeasy\WooCommerce\WooCommerce_Loader;

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
     * Whether the active theme is incompatible.
     *
     * @var bool
     */
    private bool $incompatible_theme = false;

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
      require_once TMS_PATH . 'core/autoload.php';

      // Load the Entitlement facade immediately so the single source of
      // license/plan truth is available to every hook — including the always-on
      // admin and any early bootstrap added in later phases.
      require_once TMS_PATH . 'core/class-entitlement.php';

      $this->add_hooks();
    }

    /**
     * Defines plugin constants.
     */
    private function define_constants(): void {
      define( 'THEMEASY', true );

      // Single source of truth: read the version from the plugin header so it never
      // drifts from the distributed metadata (or the Hub updater's version_compare).
      $headers = get_file_data( __FILE__, ['Version' => 'Version'] );
      define( 'TMS_VER', !empty( $headers['Version'] ) ? $headers['Version'] : '1.0.0' );

      define( 'TMS_SLUG', 'themeasy' );
      define( 'TMS_BASENAME', plugin_basename( __FILE__ ) );
      define( 'TMS_PATH', plugin_dir_path( __FILE__ ) );
      define( 'TMS_URL', plugin_dir_url( __FILE__ ) );
      define( 'TMS_ASSETS_URL', plugins_url( 'assets/', __FILE__ ) );
    }

    /**
     * Registers plugin hooks.
     */
    private function add_hooks(): void {
      add_action( 'plugins_loaded', [$this, 'load_textdomain'] );
      add_action( 'after_setup_theme', [$this, 'maybe_init'], 20 );
    }

    /**
     * Loads translation files.
     */
    public function load_textdomain(): void {
      load_plugin_textdomain( 'themeasy-lite', false, dirname( TMS_BASENAME ) . '/languages' );
    }

    /**
     * Checks if a compatible theme is active.
     *
     * Instead of maintaining a hard-coded list of text-domains, this checks for
     * the 'themeasy-compatible' theme support flag. Every theme replicated from
     * the Themeasy Base registers this flag in its setup, so the plugin works
     * with any replica without code changes.
     *
     * Runs on after_setup_theme so the theme has already registered its supports.
     *
     * No admin notice: on another theme the full offer's widgets still work, and
     * the Upgrade page says what needs a Themeasy theme (backlog #264).
     */
    public function check_theme_compatibility(): void {
      if ( !current_theme_supports( 'themeasy-compatible' ) ) {
        $this->incompatible_theme = true;
      }
    }

    /**
     * Boots the plugin: a thin Free baseline always, premium features gated.
     *
     * Runs on after_setup_theme so the theme has registered its supports
     * (themeasy-compatible) and any license signal before we read entitlement.
     *
     * The Free baseline (widget runtime + content widgets + a minimal branded
     * admin) is an Elementor widgets addon that runs on ANY theme with no license
     * — this is exactly what ships to wordpress.org. The Widgets plan
     * (Entitlement::can_use_widgets()) adds the Pro content widgets, their assets
     * and Themeasy Motion inside that same baseline, on any theme. The full
     * offer's site features (full settings panel, theme builder, Global Sections,
     * header/container tooling, WooCommerce, Contact Form 7) are built around a
     * Themeasy theme, so they load only on the full offer
     * (Entitlement::can_use_premium()) AND with a compatible theme active.
     *
     * Premium loader methods carry the __premium_only suffix so Freemius strips
     * them from the Free zip. The can_use_premium()/is_agency() guards resolve to
     * SDK methods that return false in the Free build, so those stripped-method
     * calls are never reached — no fatal.
     */
    public function maybe_init(): void {
      // Free baseline — always, theme-agnostic, no license.
      $this->load_widget_runtime();
      $this->load_core_free_content();
      $this->load_upgrade_page();
      $this->load_elementor_widgets();

      if ( !Entitlement::can_use_premium() ) {
        return;
      }

      // Everything premium lives behind a single __premium_only entry point, so the
      // stripped Free build carries one guarded (and unreachable) call instead of
      // one per feature — and leaks no per-feature method names.
    }


    /**
     * Loads the always-on Free widget runtime: helpers, the asset registrar, the
     * settings-read layer, and the SVG sanitizer (render path). This is the shared
     * closure every Free content widget renders against.
     */
    private function load_widget_runtime(): void {
      require_once TMS_PATH . 'core/class-core-loader.php';
      Core_Loader::load_widget_runtime();
    }

    /**
     * Loads the Free content features promoted out of premium core: the Portfolio
     * CPT and the attachment meta fields that back the Free gallery/image widgets.
     */
    private function load_core_free_content(): void {
      Core_Loader::init_free_content();
    }

    /**
     * Loads the Free admin: a branded menu + an Upgrade/Welcome page that
     * showcases the template library (the Freemius funnel). No settings panel.
     */
    private function load_upgrade_page(): void {
      require_once TMS_PATH . 'admin/class-upgrade-page.php';
      Upgrade_Page::init();
    }

    /**
     * Loads Elementor widget plumbing in WIDGETS-ONLY mode (categories + the
     * content widgets + editor helpers). Premium Elementor tooling is loaded
     * separately by load_elementor_premium__premium_only().
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
        require_once TMS_PATH . 'includes/elementor/class-elementor-loader.php';
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
