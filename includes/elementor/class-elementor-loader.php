<?php
/**
 * Elementor integration for Themeasy.
 *
 * Loads and initializes custom widgets, container extensions, and UI controls
 * to provide seamless integration with the Elementor editor.
 *
 * @package Themeasy\Elementor
 * @since 1.0.0
 */

namespace Themeasy\Elementor;

use Elementor\Plugin as Elementor;
use Themeasy\Admin\Settings;
use Themeasy\Core\Entitlement;

defined( 'ABSPATH' ) || exit;

class Elementor_Loader {
  /**
   * Singleton instance.
   *
   * @var self|null
   */
  private static $instance = null;

  /**
   * Returns the singleton instance.
   *
   * @return self
   */
  public static function instance(): self {
    if ( is_null( self::$instance ) ) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  /**
   * Initialize Elementor integration in WIDGETS-ONLY mode (Free).
   *
   * Registers the widget categories, the content widgets, and the editor
   * helpers/preview assets — the entire Free Elementor surface. Premium tooling
   * (header builder, container extensions, color/typography sync, page settings,
   * template shortcodes, post comments) is initialized separately by
   * init_premium__premium_only().
   *
   * Called from Themeasy::load_elementor_widgets().
   *
   * @return void
   */
  public function init(): void {
    self::define_constants();
    self::includes();

    add_action( 'elementor/elements/categories_registered', [$this, 'register_widget_categories'] );
    add_action( 'elementor/widgets/register', [$this, 'register_widgets'] );

    Template_Shortcodes::instance()->init();

    // Themeasy Library panel — a Free funnel surface (browse + insert `free`,
    // lock `pro`), so it loads in the Free baseline, not behind the premium gate.
    Library_Ajax::instance()->init();
    Library_Panel::instance()->init();
  }


  /**
   * Defines plugin constants.
   *
   * @return void
   */
  private static function define_constants(): void {
    if ( !defined( 'TMS_E_ASSETS_URL' ) ) {
      define( 'TMS_E_ASSETS_URL', plugins_url( 'assets/', __FILE__ ) );
    }
  }

  /**
   * Includes the Free Elementor widget plumbing.
   *
   * Registration + categories + the tier map + editor-helpers/preview assets +
   * the FOUC preloader (the last two degrade gracefully when premium modules are
   * absent). Premium classes are included by includes_premium__premium_only().
   *
   * @return void
   */
  private static function includes(): void {
    require_once TMS_PATH . 'includes/elementor/trait-template-context.php';
    require_once TMS_PATH . 'includes/elementor/class-widget-categories.php';
    require_once TMS_PATH . 'includes/elementor/widget-tiers.php';
    require_once TMS_PATH . 'includes/elementor/class-template-shortcodes.php';
    require_once TMS_PATH . 'includes/elementor/class-library-ajax.php';
    require_once TMS_PATH . 'includes/elementor/class-library-local.php';
    require_once TMS_PATH . 'includes/elementor/class-library-panel.php';
    require_once TMS_PATH . 'includes/elementor/enqueue-assets.php';
    require_once TMS_PATH . 'includes/elementor/icon-libraries.php';
    require_once TMS_PATH . 'includes/elementor/fouc-preload.php';

    // The header/container frontend bundles call GSAP, so they are premium
    // files. They serve the full offer only (the header/megamenu widgets and the
    // container tooling), so the Widgets plan never loads them (backlog #262).
    // The full offer's gate, not the theme-gated boot: the Vertical Megamenu
    // widget depends on the header sheet on any theme. The widget-layout rules
    // that content widgets need live in the Free core.min.css.
    if ( Entitlement::can_use_premium() ) {
    }
  }



  /**
   * Register Themeasy widget categories in Elementor.
   *
   * Entitled: registers the context-visible categories from the registry —
   * only the content-tier ones on the Widgets plan (can_use_widgets() without
   * the full offer), since that is all it registers. Free build / unentitled
   * premium: registers ONLY the single Essentials category — the thematic
   * categories stay unregistered, so the Free widgets (which declare
   * [<thematic>, Essentials]) group together and Pro-only declarations are
   * ignored by Elementor.
   *
   * @param \Elementor\Elements_Manager $elements_manager Elementor category manager.
   */
  public function register_widget_categories( $elements_manager ): void {
    if ( !Entitlement::can_use_widgets() ) {
      $elements_manager->add_category(
        Widget_Categories::FREE_CATEGORY_SLUG,
        [
          'title' => Widget_Categories::free_editor_label(),
          'icon' => 'fa fa-plug',
        ]
      );

      $this->reorder_editor_categories( $elements_manager, ['favorites', Widget_Categories::FREE_CATEGORY_SLUG] );

      return;
    }

    $context = Widget_Categories::detect_context();
    $visible = Widget_Categories::get_visible_for_context( $context );
    $definitions = Widget_Categories::get_all();

    if ( !Entitlement::can_use_premium() ) {
      $visible = array_values( array_intersect( $visible, Widget_Tiers::content_dirs() ) );
    }

    foreach ( $visible as $dir ) {
      if ( !isset( $definitions[$dir] ) ) {
        continue;
      }

      $elements_manager->add_category(
        $definitions[$dir]['category_slug'],
        [
          'title' => $definitions[$dir]['editor_label'],
          'icon' => 'fa fa-plug',
        ]
      );
    }

    $this->reorder_editor_categories( $elements_manager, Widget_Categories::get_editor_order( $context ) );
  }

  /**
   * Reorders the editor panel categories so Themeasy groups lead in the given
   * order; Elementor's own categories (Basic, General, …) fall to the end.
   *
   * @param \Elementor\Elements_Manager $elements_manager Elementor category manager.
   * @param array<int, string>          $custom_order     Category slugs, first to last.
   */
  private function reorder_editor_categories( $elements_manager, array $custom_order ): void {
    $current_categories = $elements_manager->get_categories();
    $ordered_categories = [];

    foreach ( $custom_order as $key ) {
      if ( isset( $current_categories[$key] ) ) {
        $ordered_categories[$key] = $current_categories[$key];
        unset( $current_categories[$key] );
      }
    }

    $ready_categories = array_merge( $ordered_categories, $current_categories );

    try {
      $ref = new \ReflectionProperty( $elements_manager, 'categories' );
      $ref->setValue( $elements_manager, $ready_categories );
    } catch ( \ReflectionException $e ) {
      // Skip reordering if Elementor internals change.
    }
  }

  /**
   * Register all Themeasy widgets from the two parallel widget trees.
   *
   * The tier is encoded by the ROOT directory, not by a per-file marker:
   *  - includes/elementor/widgets/               → Free widgets
   *  - includes/elementor/widgets__premium_only/ → Pro widgets (Freemius strips
   *    this whole tree from the Free build, so the loop simply skips the missing
   *    root there)
   *
   * Inside each tree the first path segment is the logical category (text,
   * media, elements, blocks, showcase, data, header, footer, …) with a clean,
   * suffix-free name; the file basename is the widget slug. The Pro tree is
   * gated by Entitlement::can_use_widgets() so an unentitled premium build
   * never registers Pro widgets, and without the full offer
   * (Entitlement::can_use_premium()) it registers only the Widgets plan: the
   * content categories, per Widget_Tiers::in_widgets_plan() (backlog #262).
   */
  public function register_widgets(): void {
    $trees = [
      ['root' => TMS_PATH . 'includes/elementor/widgets/',               'premium' => false],
      ['root' => TMS_PATH . 'includes/elementor/widgets__premium_only/', 'premium' => true],
    ];

    // Discovered widget files grouped by category: [category => [slug => path]].
    // Registration is deferred until every tree has been scanned so the widgets
    // can be emitted in the curated order (Widget_Categories::widget_order()) —
    // the filesystem iteration order is arbitrary (not alphabetical), so without
    // this step the editor panel order would be non-deterministic.
    $discovered = [];

    // The WooCommerce widgets (and the two cart widgets in header/) render
    // against the Themeasy WooCommerce runtime: helpers, themeasy-wc-* assets,
    // endpoints. WooCommerce being active is not enough — the runtime loads only
    // in the theme-gated premium boot, so on a third-party theme a registered
    // Product Grid fatals in register_controls() and takes down the editor's
    // batched controls request for every widget (backlog #263).
    $has_wc_runtime = defined( 'TMS_WC_TEMPLATES' );
    $wc_runtime_slugs = ['mini-cart', 'mini-cart-toggle'];

    // The Widgets plan (can_use_widgets() without the full offer) gets the
    // content categories only; the contextual ones are site features.
    $full_offer = Entitlement::can_use_premium();

    foreach ( $trees as $tree ) {
      // Tier gate = the tree. Pro widgets register only on an entitled premium
      // build; in the Free build the whole Pro tree is physically absent, so the
      // is_dir() check below also short-circuits it.
      if ( $tree['premium'] && !Entitlement::can_use_widgets() ) {
        continue;
      }

      $root = wp_normalize_path( $tree['root'] );

      if ( !is_dir( $root ) ) {
        continue;
      }

      try {
        $iterator = new \RecursiveIteratorIterator(
          new \RecursiveDirectoryIterator( $root, \FilesystemIterator::SKIP_DOTS ),
          \RecursiveIteratorIterator::LEAVES_ONLY
        );
      } catch ( \Exception $e ) {
        continue;
      }

      foreach ( $iterator as $file ) {
        if ( !$file->isFile() || $file->getExtension() !== 'php' ) {
          continue;
        }

        // getPathname() returns OS-native separators (backslashes on Windows), so
        // it must be normalized BEFORE stripping the already-normalized $root —
        // otherwise the prefix never matches on Windows and $category resolves to
        // the drive letter ("D:"), breaking the WooCommerce guard and class lookup.
        $relative = ltrim( str_replace( $root, '', wp_normalize_path( $file->getPathname() ) ), '/' );
        $slug_parts = explode( '/', $relative );
        $file_slug = basename( $relative, '.php' );
        $category = $slug_parts[0] ?? '';

        // WooCommerce runtime check.
        if ( !$has_wc_runtime && ( 'woocommerce' === $category || in_array( $file_slug, $wc_runtime_slugs, true ) ) ) {
          continue;
        }

        // Widgets plan check.
        if ( $tree['premium'] && !$full_offer && !Widget_Tiers::in_widgets_plan( $category, $file_slug ) ) {
          continue;
        }

        // Per-widget toggle (Widget Manager, Pro). Defaults to enabled, so Free
        // widgets register without the settings UI present.
        $setting_key = 'widget_' . str_replace( '-', '_', $file_slug );

        if ( !Settings::get_bool( $setting_key, true ) ) {
          continue;
        }

        $discovered[$category][$file_slug] = $file->getPathname();
      }
    }

    // Emit each category's widgets in the curated order; Elementor renders
    // widgets within a category in registration order, so this is what fixes the
    // panel ordering. Slugs not present in the curated list fall to the end
    // alphabetically (see Widget_Categories::sort_widget_slugs()).
    //
    // Directories are emitted in the canonical registry order (filesystem
    // discovery order is arbitrary): a cross-category widget (e.g. breadcrumbs
    // from site/ declaring the Post category) then lands deterministically
    // AFTER the panel group's own-dir widgets. Unknown dirs fall to the end.
    $canonical = array_keys( Widget_Categories::get_all() );
    $ordered_dirs = array_merge(
      array_values( array_intersect( $canonical, array_keys( $discovered ) ) ),
      array_values( array_diff( array_keys( $discovered ), $canonical ) )
    );

    foreach ( $ordered_dirs as $category ) {
      $files = $discovered[$category];
      $ordered_slugs = Widget_Categories::sort_widget_slugs( $category, array_keys( $files ) );

      foreach ( $ordered_slugs as $file_slug ) {
        require_once $files[$file_slug];
        $this->register_widget_class( $file_slug );
      }
    }
  }

  /**
   * Registers a widget class in Elementor.
   *
   * Transforms the widget file slug into a class name and registers it with Elementor.
   *
   * @param string $file_slug The widget file slug (basename without extension), e.g. 'product-grid'.
   */
  private function register_widget_class( string $file_slug ): void {
    // Transform the file slug to a class name, e.g. 'product-grid' becomes 'ProductGrid'.
    $class_name = '\\Themeasy\\Elementor\\' . str_replace( ' ', '', ucwords( str_replace( '-', ' ', $file_slug ) ) );

    // A widget file whose declared class does not match its filename would
    // otherwise vanish silently — surface it in debug builds.
    if ( !class_exists( $class_name ) ) {
      if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( sprintf( 'Themeasy: Elementor widget class %s not found for slug %s.', $class_name, $file_slug ) );
      }

      return;
    }

    // Only register genuine Elementor widgets.
    if ( !is_subclass_of( $class_name, '\\Elementor\\Widget_Base' ) ) {
      return;
    }

    Elementor::instance()->widgets_manager->register( new $class_name );
  }
}
