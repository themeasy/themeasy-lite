<?php
/**
 * Themeasy Assets Loader
 *
 * Centralized asset registration/enqueue:
 * - The design token defaults (tokens)
 * - Core CSS layers (core / components / effects / accessibility)
 * - Vendor libraries (Bootstrap, GLightbox)
 * - Optional modules (the lightbox module)
 * - Auto-registered widget assets (assets/widgets/**)
 *
 * @package Themeasy\Core
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Cache-busting version for a first-party asset.
 *
 * Returns the file's modification time when readable — so edits to the in-place
 * .min source files bust browser/CDN caches between releases (the project's
 * documented convention) — and falls back to the plugin version otherwise.
 * Vendor libraries keep their own upstream version string.
 *
 * @param string $relative Asset path relative to the plugin root (e.g. 'assets/css/core.min.css').
 * @return string|int Version string or file mtime.
 */
function themeasy_asset_ver( string $relative ) {
  $path = THEMEASY_PATH . ltrim( $relative, '/' );

  return is_readable( $path ) ? filemtime( $path ) : themeasy_get_asset_version();
}

/**
 * Whether the plugin's front-end layers load on demand on this request.
 *
 * The layers are the design tokens, the core, components, effects and
 * accessibility CSS and the Bootstrap CSS under them. A Themeasy theme is built on them (its style.css depends on
 * the Bootstrap layer), so there they load on every page. On any other theme
 * they serve the Themeasy widgets alone: the FOUC preload enqueues them in
 * <head> when a document of the page holds a Themeasy widget, and a document
 * rendered later (a theme-builder header, a template shortcode) pulls them in
 * as it renders, printed in the footer (backlog #469). The Elementor preview
 * keeps them all, so a widget dropped on the canvas is styled at once.
 *
 * @return bool
 */
function themeasy_front_layers_on_demand(): bool {
  static $on_demand = null;

  if ( null === $on_demand ) {
    $on_demand = !current_theme_supports( 'themeasy-compatible' ) && !themeasy_is_elementor_preview();
  }

  return $on_demand;
}

/**
 * Mark the body of a Themeasy theme's page, the scope of the page-wide rules of
 * core.min.css (font smoothing).
 *
 * The theme's own `tbase-body` misses a page on Elementor's Canvas template,
 * which prints its own <body>, and an older theme cannot learn a new class: the
 * plugin puts it on every template, from the theme support it already reads.
 * Off a Themeasy theme those rules reach the Themeasy widgets only.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function themeasy_add_theme_body_class( array $classes ): array {
  if ( current_theme_supports( 'themeasy-compatible' ) ) {
    $classes[] = 'tms-themeasy-theme';
  }

  return $classes;
}
add_filter( 'body_class', 'themeasy_add_theme_body_class' );

/**
 * The handles of the front-end layers, in cascade order.
 *
 * The Bootstrap CSS comes in as the dependency of themeasy-core.
 *
 * @return array{style: string[], script: string[]}
 */
function themeasy_get_front_layer_handles(): array {
  static $layers = null;

  if ( null !== $layers ) {
    return $layers;
  }

  /**
   * Filters the handles of the front-end layers.
   *
   * @param array $layers { style: string[], script: string[] }
   */
  $layers = (array) apply_filters(
    'themeasy/front_layers',
    [
      'style' => [
        'themeasy-tokens',
        'themeasy-core',
        'themeasy-components',
        'themeasy-effects',
        'themeasy-accessibility',
      ],
      'script' => [],
    ]
  );

  return $layers;
}

/**
 * Put the front-end layers ahead of an asset's own dependencies when they load
 * on demand, so a widget asset pulls them in wherever it prints.
 *
 * @param string   $type 'style' or 'script'.
 * @param string[] $deps The asset's own dependencies.
 * @return string[]
 */
function themeasy_with_front_layer_deps( string $type, array $deps ): array {
  if ( !themeasy_front_layers_on_demand() ) {
    return $deps;
  }

  $layers = themeasy_get_front_layer_handles();

  return array_values( array_unique( array_merge( (array) ( $layers[$type] ?? [] ), $deps ) ) );
}

/**
 * Enqueue the front-end layers (see themeasy_front_layers_on_demand()).
 *
 * A block theme renders the page's documents before wp_head, so a call that
 * comes before the layers are registered waits for wp_enqueue_scripts.
 *
 * @return void
 */
function themeasy_enqueue_front_layers(): void {
  static $enqueued = false;

  if ( $enqueued ) {
    return;
  }

  if ( !did_action( 'wp_enqueue_scripts' ) ) {
    add_action( 'wp_enqueue_scripts', 'themeasy_enqueue_front_layers', 11 );

    return;
  }

  $enqueued = true;
  $layers = themeasy_get_front_layer_handles();

  foreach ( (array) ( $layers['style'] ?? [] ) as $handle ) {
    wp_enqueue_style( $handle );
  }

  foreach ( (array) ( $layers['script'] ?? [] ) as $handle ) {
    wp_enqueue_script( $handle );
  }
}

/**
 * Register vendor assets (JS/CSS).
 *
 * @return void
 */
function themeasy_register_vendor_assets() {
  // ------------------------------------------------------------------------
  // Vendor CSS: Bootstrap
  //
  // A Themeasy theme is built on Bootstrap's reboot: its style.css loads after
  // it and overrides it. On any other theme the reboot's element rules (body,
  // headings, links, lists) would restyle the host page, so it gets the same
  // release with those rules scoped to the Themeasy widgets, generated by the
  // repository's bootstrap-scoped.mjs script (backlog #265).
  // ------------------------------------------------------------------------
  if ( current_theme_supports( 'themeasy-compatible' ) ) {
    wp_register_style(
      'themeasy-bootstrap',
      THEMEASY_URL . 'assets/css/vendor/bootstrap.min.css',
      [],
      '5.3.8'
    );
  } else {
    wp_register_style(
      'themeasy-bootstrap',
      THEMEASY_URL . 'assets/css/vendor/bootstrap-scoped.min.css',
      [],
      themeasy_asset_ver( 'assets/css/vendor/bootstrap-scoped.min.css' )
    );
  }

  // ------------------------------------------------------------------------
  // Vendor JS: Bootstrap
  // ------------------------------------------------------------------------
  wp_register_script(
    'themeasy-bootstrap',
    THEMEASY_URL . 'assets/js/vendor/bootstrap.bundle.min.js',
    [],
    '5.3.8',
    true
  );

  // ------------------------------------------------------------------------
  // Vendor CSS: GLightbox
  // ------------------------------------------------------------------------
  wp_register_style(
    'themeasy-glightbox',
    THEMEASY_URL . 'assets/css/vendor/glightbox.min.css',
    [],
    '3.3.1'
  );

  // ------------------------------------------------------------------------
  // Vendor JS: GLightbox
  // ------------------------------------------------------------------------
  wp_register_script(
    'themeasy-glightbox',
    THEMEASY_URL . 'assets/js/vendor/glightbox.min.js',
    [],
    '3.3.1',
    true
  );
}
add_action( 'wp_enqueue_scripts', 'themeasy_register_vendor_assets', 5 );

/**
 * Register Themeasy modules (optional feature files).
 *
 * Modules are loaded on-demand by widgets via get_style_depends() /
 * get_script_depends() or a manual enqueue.
 *
 * @return void
 */
function themeasy_register_modules_assets() {
  // ------------------------------------------------------------------------
  // Module CSS: Lightbox
  // ------------------------------------------------------------------------
  // Widgets declare the module as their own handle (the Image), so it carries
  // the front-end layers too.
  wp_register_style(
    'themeasy-lightbox-module',
    THEMEASY_URL . 'assets/css/modules/lightbox.min.css',
    themeasy_with_front_layer_deps( 'style', ['themeasy-glightbox'] ),
    themeasy_asset_ver( 'assets/css/modules/lightbox.min.css' )
  );

  // ------------------------------------------------------------------------
  // Module JS: Lightbox
  // ------------------------------------------------------------------------
  wp_register_script(
    'themeasy-lightbox-module',
    THEMEASY_URL . 'assets/js/modules/lightbox.min.js',
    themeasy_with_front_layer_deps( 'script', ['themeasy-glightbox'] ),
    themeasy_asset_ver( 'assets/js/modules/lightbox.min.js' ),
    true
  );

  // Plyr, GLightbox's video player, served from the plugin. GLightbox injects
  // it lazily when a video slide opens, from cdn.plyr.io by default, and Plyr
  // pulls its icon sprite from there too (backlog #257). The version in the
  // URLs is the one of the files in assets/*/vendor/: bump the two together.
  wp_localize_script(
    'themeasy-lightbox-module',
    'themeasyLightbox',
    [
      'plyr' => [
        'css' => THEMEASY_URL . 'assets/css/vendor/plyr.min.css?ver=3.8.5',
        'js' => THEMEASY_URL . 'assets/js/vendor/plyr.min.js?ver=3.8.5',
        'iconUrl' => THEMEASY_URL . 'assets/js/vendor/plyr.svg?ver=3.8.5',
      ],
    ]
  );
}
add_action( 'wp_enqueue_scripts', 'themeasy_register_modules_assets', 6 );

/**
 * Enqueue core Themeasy assets.
 *
 * Registers the CSS layers in cascade order (core → components → effects →
 * accessibility), after the design token defaults they all consume, and
 * enqueues them on every page unless they load on demand (off a Themeasy
 * theme: themeasy_front_layers_on_demand()). No core JS loads here: the
 * widgets need none, and the one that needs Bootstrap JS (the Accordion)
 * declares it itself.
 *
 * @return void
 */
function themeasy_enqueue_core_assets() {
  // ------------------------------------------------------------------------
  // Design tokens: the --tms-* defaults at zero specificity, so the widgets
  // render on any theme. On a Themeasy theme, its style.css and the generated
  // settings.css outrank them (backlog #256).
  // ------------------------------------------------------------------------
  wp_register_style(
    'themeasy-tokens',
    THEMEASY_URL . 'assets/css/tokens.min.css',
    [],
    themeasy_asset_ver( 'assets/css/tokens.min.css' )
  );

  // ------------------------------------------------------------------------
  // Core CSS
  // ------------------------------------------------------------------------
  wp_register_style(
    'themeasy-core',
    THEMEASY_URL . 'assets/css/core.min.css',
    ['themeasy-bootstrap'],
    themeasy_asset_ver( 'assets/css/core.min.css' )
  );

  // ------------------------------------------------------------------------
  // Components CSS
  // ------------------------------------------------------------------------
  wp_register_style(
    'themeasy-components',
    THEMEASY_URL . 'assets/css/components.min.css',
    ['themeasy-core'],
    themeasy_asset_ver( 'assets/css/components.min.css' )
  );

  // ------------------------------------------------------------------------
  // Effects CSS
  // ------------------------------------------------------------------------
  wp_register_style(
    'themeasy-effects',
    THEMEASY_URL . 'assets/css/effects.min.css',
    ['themeasy-core'],
    themeasy_asset_ver( 'assets/css/effects.min.css' )
  );

  // ------------------------------------------------------------------------
  // Accessibility CSS
  // ------------------------------------------------------------------------
  wp_register_style(
    'themeasy-accessibility',
    THEMEASY_URL . 'assets/css/accessibility.min.css',
    ['themeasy-effects'],
    themeasy_asset_ver( 'assets/css/accessibility.min.css' )
  );

  if ( themeasy_front_layers_on_demand() ) {
    return;
  }

  wp_enqueue_style( 'themeasy-tokens' );
  wp_enqueue_style( 'themeasy-core' );
  wp_enqueue_style( 'themeasy-components' );
  wp_enqueue_style( 'themeasy-effects' );
  wp_enqueue_style( 'themeasy-accessibility' );
}
add_action( 'wp_enqueue_scripts', 'themeasy_enqueue_core_assets', 10 );

/**
 * Register the widgets' CSS and JS files.
 *
 * @return void
 */
function themeasy_auto_register_widget_assets() {
  themeasy_register_widget_assets_in( 'assets/widgets/' );
}
add_action( 'wp_enqueue_scripts', 'themeasy_auto_register_widget_assets', 7 );

/**
 * Register every widget CSS and JS file under one asset root.
 *
 * A file placed directly in the root (e.g. bundle.min.css) registers as
 * themeasy-{basename}; a file inside a subdirectory registers as
 * themeasy-{folder}, so the handle follows the widget slug.
 *
 * @param string $relative Asset root relative to the plugin root (e.g. 'assets/widgets/').
 * @return void
 */
function themeasy_register_widget_assets_in( string $relative ): void {
  $base_url = trailingslashit( THEMEASY_URL . ltrim( $relative, '/' ) );

  foreach ( themeasy_get_widget_asset_files( $relative ) as [$file, $prefix, $version] ) {
    $folder = dirname( $file );
    $url = '.' === $folder ? $base_url : trailingslashit( $base_url . $folder );

    themeasy_register_asset_from_file( basename( $file ), $url, $version, '' === $prefix ? null : $prefix );
  }
}

/**
 * The widget CSS and JS files under one asset root, with their versions.
 *
 * The scan (a scandir, two globs per widget folder, a stat per file) cost ~75 ms
 * of every front request over the two roots, for a list that changes only with
 * the plugin's files (backlog #474). So it is cached by plugin version, the file
 * mtimes included, and flushed when the plugin is updated
 * (themeasy_flush_widget_asset_files_cache_on_upgrade()). A development site
 * scans live, so editing an asset still changes its ?ver: WP_DEBUG, or the local
 * and development environment types (every LocalWP site declares 'local').
 *
 * @param string $relative Asset root relative to the plugin root (e.g. 'assets/widgets/').
 * @return array<int, array{0: string, 1: string, 2: string}> Path from the root,
 *         handle prefix ('' for a file at the root), version.
 */
function themeasy_get_widget_asset_files( string $relative ): array {
  static $roots = null;

  $relative = trailingslashit( ltrim( $relative, '/' ) );
  $cache_key = 'themeasy_widget_asset_files_' . themeasy_get_asset_version();
  $is_dev = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || in_array( wp_get_environment_type(), ['local', 'development'], true );

  /**
   * Filters whether the widget asset list is cached (default: off on a development site).
   *
   * @param bool $cache Whether to cache the list and the file versions.
   */
  $cache = (bool) apply_filters( 'themeasy/cache_widget_asset_files', !$is_dev );

  if ( null === $roots ) {
    $stored = $cache ? get_transient( $cache_key ) : false;
    $roots = is_array( $stored ) ? $stored : [];
  }

  if ( isset( $roots[$relative] ) ) {
    return $roots[$relative];
  }

  $base_path = THEMEASY_PATH . $relative;
  $version = themeasy_get_asset_version();
  $files = [];
  $items = is_dir( $base_path ) ? scandir( $base_path ) : false;

  foreach ( is_array( $items ) ? $items : [] as $item ) {
    if ( in_array( $item, ['.', '..'], true ) ) {
      continue;
    }

    $item_path = $base_path . $item;

    // Direct files in /widgets/ (e.g. bundle.min.css).
    if ( is_file( $item_path ) ) {
      $paths = in_array( pathinfo( $item, PATHINFO_EXTENSION ), ['css', 'js'], true ) ? [$item_path] : [];
      $prefix = '';
    } elseif ( is_dir( $item_path ) ) {
      // Subdirectories like /video/, /steps/, etc. Two globs, never
      // GLOB_BRACE: musl builds of PHP (Alpine) do not define it, and an
      // undefined constant is a fatal on PHP 8.
      $css_files = glob( $item_path . '/*.css' );
      $js_files = glob( $item_path . '/*.js' );
      $paths = array_merge( is_array( $css_files ) ? $css_files : [], is_array( $js_files ) ? $js_files : [] );
      $prefix = $item;
    } else {
      continue;
    }

    foreach ( $paths as $path ) {
      $files[] = [
        ( '' === $prefix ? '' : $prefix . '/' ) . basename( $path ),
        $prefix,
        (string) ( is_readable( $path ) ? filemtime( $path ) : $version ),
      ];
    }
  }

  $roots[$relative] = $files;

  if ( $cache ) {
    set_transient( $cache_key, $roots, DAY_IN_SECONDS );
  }

  return $files;
}

/**
 * Flush every transient created by themeasy_get_widget_asset_files().
 *
 * The key embeds the plugin version, so a release routes to a fresh key, but the
 * old row would linger for up to DAY_IN_SECONDS, and files changed outside a
 * version bump would keep their old list and versions until then.
 *
 * @return void
 */
function themeasy_flush_widget_asset_files_cache(): void {
  global $wpdb;

  // The running version's key by name first: it also drops the copy this request
  // already holds, and the one a persistent object cache keeps out of wp_options.
  delete_transient( 'themeasy_widget_asset_files_' . themeasy_get_asset_version() );

  // Then the older versions' rows, by prefix: no API lists them.
  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
  $wpdb->query(
    $wpdb->prepare(
      "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
      $wpdb->esc_like( '_transient_themeasy_widget_asset_files_' ) . '%',
      $wpdb->esc_like( '_transient_timeout_themeasy_widget_asset_files_' ) . '%'
    )
  );
}

/**
 * Flush the widget asset list when this plugin is installed or updated.
 *
 * @param \WP_Upgrader $upgrader   Upgrader instance.
 * @param array        $hook_extra Context data from the upgrader.
 * @return void
 */
function themeasy_flush_widget_asset_files_cache_on_upgrade( $upgrader, $hook_extra ): void {
  if ( empty( $hook_extra['type'] ) || 'plugin' !== $hook_extra['type'] ) {
    return;
  }

  $plugins = isset( $hook_extra['plugins'] ) ? (array) $hook_extra['plugins'] : [];

  if ( isset( $hook_extra['plugin'] ) ) {
    $plugins[] = (string) $hook_extra['plugin'];
  }

  if ( !defined( 'THEMEASY_BASENAME' ) || !in_array( THEMEASY_BASENAME, $plugins, true ) ) {
    return;
  }

  themeasy_flush_widget_asset_files_cache();
}
add_action( 'upgrader_process_complete', 'themeasy_flush_widget_asset_files_cache_on_upgrade', 10, 2 );

/**
 * Register a single CSS or JS file as a WordPress asset.
 *
 * @param string      $filename File name (e.g. core.min.css).
 * @param string      $url      Public URL to the file directory.
 * @param string      $version  The file's version (its mtime, from themeasy_get_widget_asset_files()).
 * @param string|null $prefix   Optional prefix used as the handle base (usually subfolder name).
 * @return void
 */
function themeasy_register_asset_from_file( string $filename, string $url, string $version, ?string $prefix = null ): void {
  // ------------------------------------------------------------------------
  // Handle and URL generation.
  // ------------------------------------------------------------------------
  $ext = pathinfo( $filename, PATHINFO_EXTENSION );

  // Remove ".min" from basename if present.
  $basename = preg_replace( '/\.min$/', '', pathinfo( $filename, PATHINFO_FILENAME ) );
  $basename = sanitize_key( $basename );

  // Final handle.
  $handle = $prefix ? "themeasy-{$prefix}" : "themeasy-{$basename}";
  $file_url = $url . $filename;

  // ------------------------------------------------------------------------
  // Dependencies (registration-time load order; see themeasy_get_widget_asset_deps()).
  // ------------------------------------------------------------------------
  $deps = themeasy_get_widget_asset_deps();
  $style_deps = array_values( array_filter( (array) ( $deps['style'][$handle] ?? [] ) ) );
  $script_deps = array_values( array_filter( (array) ( $deps['script'][$handle] ?? [] ) ) );

  // Off a Themeasy theme the front-end layers ride every widget asset, so a
  // widget printed outside the documents the FOUC preload scanned still gets
  // them (backlog #469).
  $style_deps = themeasy_with_front_layer_deps( 'style', $style_deps );
  $script_deps = themeasy_with_front_layer_deps( 'script', $script_deps );

  // ------------------------------------------------------------------------
  // Register the asset based on its type. The version is the file mtime, so
  // in-place edits to the .min sources invalidate caches.
  // ------------------------------------------------------------------------
  if ( 'css' === $ext ) {
    wp_register_style( $handle, $file_url, $style_deps, $version );
    return;
  }

  if ( 'js' === $ext ) {
    wp_register_script( $handle, $file_url, $script_deps, $version, true );
    return;
  }
}

/**
 * Registration-time dependency maps for auto-registered widget assets.
 *
 * Some widgets have implicit dependencies on vendor libraries or modules. They
 * only define dependency order at registration time: WordPress uses them to
 * guarantee the load order even when only the widget handle itself is enqueued
 * later (e.g. GLightbox loading after the video widget).
 *
 * An entry applies only to a handle that really registers the matching file
 * type. The map is filterable.
 *
 * @return array{script: array<string, string[]>, style: array<string, string[]>}
 */
function themeasy_get_widget_asset_deps(): array {
  static $deps = null;

  if ( null !== $deps ) {
    return $deps;
  }

  $script_deps_map = [
    'themeasy-image' => ['themeasy-lightbox-module'],
    // Video only needs GLightbox for its Lightbox play mode; the map keeps the load order safe for it.
    'themeasy-video' => ['themeasy-lightbox-module'],
  ];

  $style_deps_map = [
    'themeasy-image' => ['themeasy-lightbox-module'],
    'themeasy-video' => ['themeasy-lightbox-module'],
  ];

  /**
   * Filters the widget asset dependency maps.
   *
   * @param array $deps { script: handle => deps[], style: handle => deps[] }
   */
  $deps = (array) apply_filters(
    'themeasy/widget_asset_deps',
    [
      'script' => $script_deps_map,
      'style' => $style_deps_map,
    ]
  );

  return $deps;
}
