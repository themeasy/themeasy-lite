<?php
/**
 * Enqueue Themeasy assets for the Elementor editor and frontend preview.
 *
 * The widget runtime of the editor: the editor-helpers JS (+ window.Themeasy
 * bootstrap + the inline SVG library), the preview CSS/JS, and the editor-panel
 * CSS. The widgets need these so their content_template() previews render in
 * the editor.
 *
 *
 * @package Themeasy\Elementor
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Load CSS for the Elementor preview area.
 *
 * @return void
 */
function themeasy_enqueue_elementor_preview_style(): void {
  if ( !themeasy_is_elementor_preview() ) {
    return;
  }

  $version = themeasy_get_asset_version();

  wp_enqueue_style(
    'themeasy-elementor-preview',
    THEMEASY_E_ASSETS_URL . 'css/themeasy-el-preview.min.css',
    [],
    $version
  );
}
add_action( 'elementor/frontend/after_enqueue_styles', 'themeasy_enqueue_elementor_preview_style' );

/**
 * Mark the Elementor preview iframe with the `tms-elementor-preview` body class.
 *
 * The widgets' editor contract: their CSS and JS opt out of frontend-only
 * behavior on this class (the dash icon pre-boot hide, the Coverflow intro,
 * the lightbox module, the Nested Tabs panels…), because the Element Cache can
 * serve frontend HTML inside the preview, so a PHP branch alone never reaches
 * it. It belongs to the widget layer: every build emits it, on any theme
 * (backlog #262).
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function themeasy_add_elementor_preview_body_class( array $classes ): array {
  if ( themeasy_is_elementor_preview() ) {
    $classes[] = 'tms-elementor-preview';
  }

  return $classes;
}
add_filter( 'body_class', 'themeasy_add_elementor_preview_body_class' );

/**
 * Load JS for the Elementor preview area.
 *
 * @return void
 */
function themeasy_enqueue_elementor_preview_script(): void {
  if ( !themeasy_is_elementor_preview() ) {
    return;
  }

  $version = themeasy_get_asset_version();
  $deps = ['jquery'];
  $data = [];

  wp_enqueue_script(
    'themeasy-elementor-preview',
    THEMEASY_E_ASSETS_URL . 'js/themeasy-el-preview.min.js',
    $deps,
    $version,
    true
  );

  if ( $data ) {
    wp_localize_script( 'themeasy-elementor-preview', 'themeasyElPreview', $data );
  }
}
add_action( 'elementor/frontend/after_enqueue_scripts', 'themeasy_enqueue_elementor_preview_script' );

/**
 * Load CSS for the Elementor editor panel.
 *
 * @return void
 */
function themeasy_enqueue_editor_panel_style(): void {
  $version = themeasy_get_asset_version();

  wp_enqueue_style(
    'themeasy-editor-panel',
    THEMEASY_E_ASSETS_URL . 'css/themeasy-el-editor-panel.min.css',
    [],
    $version
  );
}
add_action( 'elementor/editor/after_enqueue_styles', 'themeasy_enqueue_editor_panel_style' );

/**
 * Build a map of inline SVG contents for the Elementor editor preview.
 *
 * Reads every .svg file under assets/media/svg/{library}/ for each library
 * listed in the 'themeasy/editor_svg_libraries' filter and returns a nested
 * array: [ library => [ icon_name => svg_content ] ].
 *
 * The result is cached via transient, keyed by the current asset version so
 * plugin releases invalidate the cache automatically. The cache is bypassed
 * when WP_DEBUG is enabled so local changes to SVG files are picked up without
 * manual flushing.
 *
 * @return array
 */
function themeasy_get_editor_svg_library(): array {
  $cache_key = 'themeasy_editor_svg_library_' . themeasy_get_asset_version();
  $debug = defined( 'WP_DEBUG' ) && WP_DEBUG;

  if ( !$debug ) {
    $cached = get_transient( $cache_key );
    if ( is_array( $cached ) ) {
      return $cached;
    }
  }

  // The ty-* libraries come from the generated Themeasy Icons manifest; only
  // entries flagged preload=true are inlined here (ty-feather). A large set
  // would be a multi-MB payload on every editor load, so it stays out and the
  // editor JS lazy-fetches its icons one by one.
  // Preload by SOURCE DIR (svgLibrary is keyed by dir on both sides).
  $ty_dirs = [];

  if ( function_exists( 'themeasy_get_ty_icon_manifest' ) ) {
    foreach ( themeasy_get_ty_icon_manifest()['libraries'] ?? [] as $lib => $data ) {
      if ( empty( $data['preload'] ) ) {
        continue;
      }
      $ty_dirs[] = (string) ( $data['dir'] ?? $lib );
    }
  }

  /**
   * Filter the SVG libraries preloaded into the Elementor editor.
   *
   * Each entry must match a subfolder name under assets/media/svg/. Only
   * flat folders are scanned; nested structures are ignored.
   *
   * @param array $libraries List of library folder names. Default: the
   *                         preload-flagged ty-* dirs.
   */
  $default_libraries = ['ty-feather'];

  $libraries = apply_filters(
    'themeasy/editor_svg_libraries',
    array_values( array_unique( array_merge( $default_libraries, $ty_dirs ) ) )
  );

  $map = [];
  $base = THEMEASY_PATH . 'assets/media/svg/';

  foreach ( (array) $libraries as $lib ) {
    $lib = sanitize_file_name( (string) $lib );
    if ( '' === $lib ) {
      continue;
    }

    $dir = $base . $lib;
    if ( !is_dir( $dir ) ) {
      continue;
    }

    $files = glob( $dir . '/*.svg' );
    if ( empty( $files ) ) {
      continue;
    }

    $map[$lib] = [];

    foreach ( $files as $file ) {
      $name = basename( $file, '.svg' );
      $content = file_get_contents( $file );
      if ( false !== $content ) {
        $map[$lib][$name] = $content;
      }
    }
  }

  if ( !$debug ) {
    set_transient( $cache_key, $map, DAY_IN_SECONDS );
  }

  return $map;
}

/**
 * Flush every transient created by themeasy_get_editor_svg_library().
 *
 * The cache key embeds the plugin version, so a release naturally routes to a
 * fresh key — but the old transient would still linger in wp_options for up
 * to DAY_IN_SECONDS, and any SVG shipped outside a version bump would stay
 * masked until that TTL elapsed. Removing every matching row (live value +
 * timeout) keeps the table tidy and guarantees the next editor load rebuilds
 * from disk.
 *
 * @return void
 */
function themeasy_flush_editor_svg_library_cache(): void {
  global $wpdb;

  $live = $wpdb->esc_like( '_transient_themeasy_editor_svg_library_' ) . '%';
  $timeout = $wpdb->esc_like( '_transient_timeout_themeasy_editor_svg_library_' ) . '%';

  $wpdb->query(
    $wpdb->prepare(
      "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
      $live,
      $timeout
    )
  );

  // Drop persistent object-cache copies too (Redis, Memcached). Core added this
  // helper in 6.1; older sites fall back to the DB delete above.
  if ( function_exists( 'wp_cache_flush_group' ) ) {
    wp_cache_flush_group( 'transient' );
  }
}

/**
 * Flush the editor SVG library cache when the plugin is updated.
 *
 * Fires on `upgrader_process_complete`, the hook WordPress runs after an
 * install, update, or bulk operation. We only act when the current plugin is
 * in the upgraded set so activity on unrelated plugins is ignored.
 *
 * @param \WP_Upgrader $upgrader   Upgrader instance.
 * @param array        $hook_extra Context data from the upgrader.
 * @return void
 */
function themeasy_flush_editor_svg_library_cache_on_upgrade( $upgrader, $hook_extra ): void {
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

  themeasy_flush_editor_svg_library_cache();
}
add_action( 'upgrader_process_complete', 'themeasy_flush_editor_svg_library_cache_on_upgrade', 10, 2 );

/**
 * Load JS helpers for content_template() and inline editing in Elementor.
 *
 * @return void
 */
function themeasy_enqueue_editor_helpers_script(): void {
  // filemtime, not THEMEASY_VER: content_template() markup and these helpers must
  // move in lockstep (icon contract) — a version-pinned URL serves stale
  // editor JS across same-version builds and silently breaks the parity.
  $helpers_path = __DIR__ . '/assets/js/themeasy-el-editor-helpers.min.js';
  $version = file_exists( $helpers_path )
    ? (string) filemtime( $helpers_path )
    : themeasy_get_asset_version();

  wp_enqueue_script(
    'themeasy-editor-helpers',
    THEMEASY_E_ASSETS_URL . 'js/themeasy-el-editor-helpers.min.js',
    ['jquery'],
    $version,
    true
  );

  // Expose the plugin URL and the preloaded SVG library so the JS helper can
  // render themeasy-svg icons inline in the editor preview (matching frontend).
  // tyLibraryDirs maps each ty-* picker library to its source dir under
  // assets/media/svg/ (svgLibrary is keyed by DIR, mirroring the PHP side).
  $ty_library_dirs = [];

  if ( function_exists( 'themeasy_get_ty_icon_manifest' ) ) {
    foreach ( themeasy_get_ty_icon_manifest()['libraries'] ?? [] as $lib => $data ) {
      $dir = (string) ( $data['dir'] ?? $lib );

      // Only dirs that exist on disk: a library this build does not carry
      // stays out, and its absence here also stops the JS lazy fetch (no 404
      // spam for stray saved values).
      if ( !is_dir( THEMEASY_PATH . 'assets/media/svg/' . $dir ) ) {
        continue;
      }

      $ty_library_dirs[(string) $lib] = $dir;
    }
  }

  $inline = 'window.Themeasy = window.Themeasy || {};'
    . ' window.Themeasy.pluginUrl = ' . wp_json_encode( THEMEASY_URL, JSON_HEX_TAG ) . ';'
    . ' window.Themeasy.tyLibraryDirs = ' . wp_json_encode( $ty_library_dirs, JSON_HEX_TAG ) . ';'
    . ' window.Themeasy.svgLibrary = ' . wp_json_encode( themeasy_get_editor_svg_library(), JSON_HEX_TAG ) . ';';

  wp_add_inline_script( 'themeasy-editor-helpers', $inline, 'before' );
}
add_action( 'elementor/editor/after_enqueue_scripts', 'themeasy_enqueue_editor_helpers_script' );
