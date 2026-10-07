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
   * Initialize the Elementor integration.
   *
   * Registers the widget categories, the content widgets, the editor
   * helpers/preview assets and the Library panel.
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

    // Themeasy Library panel: browse the catalog and insert its templates.
    Library_Ajax::instance()->init();
    Library_Panel::instance()->init();
  }

  /**
   * Defines plugin constants.
   *
   * @return void
   */
  private static function define_constants(): void {
    if ( !defined( 'THEMEASY_E_ASSETS_URL' ) ) {
      define( 'THEMEASY_E_ASSETS_URL', plugins_url( 'assets/', __FILE__ ) );
    }
  }

  /**
   * Includes the Elementor widget plumbing: the category registry, the Library
   * panel, the editor-helpers/preview assets, the icon libraries and the FOUC
   * preloader.
   *
   * @return void
   */
  private static function includes(): void {
    require_once THEMEASY_PATH . 'includes/elementor/class-widget-categories.php';
    require_once THEMEASY_PATH . 'includes/elementor/class-library-ajax.php';
    require_once THEMEASY_PATH . 'includes/elementor/class-library-local.php';
    require_once THEMEASY_PATH . 'includes/elementor/class-library-panel.php';
    require_once THEMEASY_PATH . 'includes/elementor/enqueue-assets.php';
    require_once THEMEASY_PATH . 'includes/elementor/icon-libraries.php';
    require_once THEMEASY_PATH . 'includes/elementor/fouc-preload.php';
  }

  /**
   * Register Themeasy widget categories in Elementor.
   *
   * Registers the single Essentials category. The thematic categories stay
   * unregistered, so the widgets (which declare [<thematic>, Essentials]) group
   * together, and Elementor ignores a category nobody registered.
   *
   * @param \Elementor\Elements_Manager $elements_manager Elementor category manager.
   */
  public function register_widget_categories( $elements_manager ): void {
    $elements_manager->add_category(
      Widget_Categories::FREE_CATEGORY_SLUG,
      [
        'title' => Widget_Categories::free_editor_label(),
        'icon' => 'fa fa-plug',
      ]
    );

    $this->reorder_editor_categories( $elements_manager, ['favorites', Widget_Categories::FREE_CATEGORY_SLUG] );
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

      // The property is private: below PHP 8.1 it has to be opened first, or
      // setValue() throws and the reorder is skipped (backlog #368). From 8.1
      // on every property is accessible, and 8.5 deprecates the call.
      if ( PHP_VERSION_ID < 80100 ) {
        $ref->setAccessible( true );
      }

      $ref->setValue( $elements_manager, $ready_categories );
    } catch ( \ReflectionException $e ) {
      // Skip reordering if Elementor internals change.
    }
  }

  /**
   * Register the Themeasy widgets.
   *
   * The widgets live in includes/elementor/widgets/. The first path segment is
   * the logical category (text, media, elements, blocks, showcase, data) and
   * the file basename is the widget slug.
   */
  public function register_widgets(): void {
    // Discovered widget files grouped by category: [category => [slug => path]].
    // Registration is deferred until the scan is over so the widgets can be
    // emitted in the curated order (Widget_Categories::widget_order()): the
    // filesystem iteration order is arbitrary (not alphabetical), so without
    // this step the editor panel order would be non-deterministic.
    $discovered = self::scan_widget_tree( THEMEASY_PATH . 'includes/elementor/widgets/' );

    // Emit each category's widgets in the curated order; Elementor renders
    // widgets within a category in registration order, so this is what fixes the
    // panel ordering. Slugs not present in the curated list fall to the end
    // alphabetically (see Widget_Categories::sort_widget_slugs()).
    //
    // Directories are emitted in the order of the curated list (filesystem
    // discovery order is arbitrary). Unknown dirs fall to the end.
    $canonical = array_keys( Widget_Categories::widget_order() );

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
   * Scan one widget tree for its widget files.
   *
   * @param string $root Absolute path of the tree, with a trailing slash.
   * @return array<string, array<string, string>> [category => [slug => path]].
   */
  private static function scan_widget_tree( string $root ): array {
    $found = [];
    $root = wp_normalize_path( $root );

    if ( !is_dir( $root ) ) {
      return $found;
    }

    try {
      $iterator = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator( $root, \FilesystemIterator::SKIP_DOTS ),
        \RecursiveIteratorIterator::LEAVES_ONLY
      );
    } catch ( \Exception $e ) {
      return $found;
    }

    foreach ( $iterator as $file ) {
      if ( !$file->isFile() || $file->getExtension() !== 'php' ) {
        continue;
      }

      // getPathname() returns OS-native separators (backslashes on Windows), so
      // it must be normalized BEFORE stripping the already-normalized $root —
      // otherwise the prefix never matches on Windows and the category resolves
      // to the drive letter ("D:"), breaking the class lookup.
      $relative = ltrim( str_replace( $root, '', wp_normalize_path( $file->getPathname() ) ), '/' );
      $slug_parts = explode( '/', $relative );

      $found[$slug_parts[0] ?? ''][basename( $relative, '.php' )] = $file->getPathname();
    }

    return $found;
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
