<?php
/**
 * Enqueue Themeasy assets for the Elementor editor and frontend preview.
 *
 * Free widget runtime: the editor-helpers JS (+ window.Themeasy bootstrap + the
 * inline SVG library), the preview CSS/JS, and the editor-panel CSS — Free
 * widgets need these so their content_template() previews render in the editor.
 *
 * The header-builder and container-extension FRONTEND bundles are premium and
 * call GSAP, so they live in extensions__premium_only/enqueue-assets.php, with
 * their files under assets__premium_only/ (backlog #253).
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
    TMS_E_ASSETS_URL . 'css/themeasy-el-preview.min.css',
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
 * it. It belongs to the widget layer, not to a site feature: every build and
 * every plan emits it, on any theme. It used to ride the premium Features
 * class, which only the full boot loads (backlog #262).
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

  // The preview runtime replays Themeasy Motion through the core engines, so an
  // entitled site prints it after the premium core bundle (which pulls GSAP in;
  // it loads on the widget-layer gate, like the widgets' motion).
  // The Free build has neither — its widgets emit no motion markup — and a
  // missing premium dependency would make WordPress drop this script entirely.
  $deps = \Themeasy\Core\Entitlement::can_use_widgets() ? ['jquery', 'themeasy-core'] : ['jquery'];

  wp_enqueue_script(
    'themeasy-elementor-preview',
    TMS_E_ASSETS_URL . 'js/themeasy-el-preview.min.js',
    $deps,
    $version,
    true
  );

  // Localize the variant registry so the preview JS can mirror body classes
  // live while the user toggles Page Variant switchers in the editor. Each
  // entry exposes only the fields the JS needs — the Elementor setting key
  // to listen to, the activation value, and the body class to toggle. Only
  // when Global Sections booted (the full offer): `false` keeps the class
  // autoloader from loading a site feature the boot left out (backlog #262).
  $variants_payload = [];

  if ( class_exists( '\\Themeasy\\Global_Sections\\Page_Variants', false ) ) {
    foreach ( \Themeasy\Global_Sections\Page_Variants::get_registered() as $slug => $variant ) {
      $setting_key = isset( $variant['setting'] ) ? (string) $variant['setting'] : '';
      if ( '' === $setting_key ) {
        continue;
      }

      $variants_payload[(string) $slug] = [
        'setting' => $setting_key,
        'value_on' => isset( $variant['value_on'] ) ? (string) $variant['value_on'] : 'yes',
        'body_class' => \Themeasy\Global_Sections\Page_Variants::get_body_class( (string) $slug ),
      ];
    }
  }

  // Whether the page TEMPLATE already reserves the header's height
  // (Features::header_reserves_flow()). The preview JS mirrors the tri-state
  // "First Section" override live, and Reserve emits the very same body class
  // the heuristic does — without this flag the canvas cannot tell the two
  // apart, and flipping the override back to Inherit would strip a reserve the
  // template legitimately owns. False when the premium Features class is not
  // loaded: no heuristic running means nothing for the JS to preserve. The
  // `false` is load-bearing: an autoloaded Features would construct and hook
  // its site features (body classes, excerpts, breadcrumbs) into the preview
  // of a site whose boot left them out (backlog #262).
  $template_reserves_header = class_exists( '\Themeasy\Core\Features', false )
    && \Themeasy\Core\Features::instance()->header_reserves_flow();

  // Is the document being edited a Global Section? On the FRONT a GS is wrapped
  // in `.themeasy-gs`, which is what scopes the container-padding reset in
  // themeasy-el-container (section 01b). In the editor there is no wrapper —
  // the canvas IS the section — so without this flag the same containers
  // preview at Elementor's 10px default and render flush on the site, and the
  // user is left correcting a difference that does not exist.
  // The id comes from the preview REQUEST, not from documents->get_current():
  // during `elementor/frontend/after_enqueue_scripts` inside the preview
  // iframe there is no current document yet, and the flag came back empty
  // (measured on grideon.local, 2026-08-31). `elementor-preview=<post_id>` is
  // the same fallback themeasy_is_elementor_preview() already trusts.
  $previewed_id = isset( $_GET['elementor-preview'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    ? absint( wp_unslash( $_GET['elementor-preview'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    : (int) get_the_ID();
  $editing_global_section = $previewed_id > 0
    && 'themeasy-sections' === get_post_type( $previewed_id );

  wp_localize_script(
    'themeasy-elementor-preview',
    'themeasyElPreview',
    [
      'variants' => $variants_payload,
      'templateReservesHeader' => $template_reserves_header,
      'editingGlobalSection' => $editing_global_section,
    ]
  );
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
    TMS_E_ASSETS_URL . 'css/themeasy-el-editor-panel.min.css',
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
  // entries flagged preload=true are inlined here (ty-feather). The Solar
  // sets (~4.9k files) would be a multi-MB payload on every editor load, so
  // they stay out and the editor JS lazy-fetches their icons one by one.
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
   * @param array $libraries List of library folder names.
   *                         Default [ 'ty-feather', 'animated',
   *                         ...preload-flagged ty-* dirs ].
   */
  $libraries = apply_filters(
    'themeasy/editor_svg_libraries',
    array_values( array_unique( array_merge( ['ty-feather', 'animated'], $ty_dirs ) ) )
  );

  $map = [];
  $base = TMS_PATH . 'assets/media/svg/';

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

  if ( !defined( 'TMS_BASENAME' ) || !in_array( TMS_BASENAME, $plugins, true ) ) {
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
  // filemtime, not TMS_VER: content_template() markup and these helpers must
  // move in lockstep (icon contract) — a version-pinned URL serves stale
  // editor JS across same-version builds and silently breaks the parity.
  $helpers_path = __DIR__ . '/assets/js/themeasy-el-editor-helpers.min.js';
  $version = file_exists( $helpers_path )
    ? (string) filemtime( $helpers_path )
    : themeasy_get_asset_version();

  wp_enqueue_script(
    'themeasy-editor-helpers',
    TMS_E_ASSETS_URL . 'js/themeasy-el-editor-helpers.min.js',
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

      // Only dirs that exist on disk: the Free build strips the premium
      // Solar dirs, and their absence here also gates the JS lazy fetch
      // (no 404 spam for stray saved values).
      if ( !is_dir( TMS_PATH . 'assets/media/svg/' . $dir ) ) {
        continue;
      }

      $ty_library_dirs[(string) $lib] = $dir;
    }
  }

  $inline = 'window.Themeasy = window.Themeasy || {};'
    . ' window.Themeasy.pluginUrl = ' . wp_json_encode( TMS_URL, JSON_HEX_TAG ) . ';'
    . ' window.Themeasy.tyLibraryDirs = ' . wp_json_encode( $ty_library_dirs, JSON_HEX_TAG ) . ';'
    . ' window.Themeasy.svgLibrary = ' . wp_json_encode( themeasy_get_editor_svg_library(), JSON_HEX_TAG ) . ';';

  wp_add_inline_script( 'themeasy-editor-helpers', $inline, 'before' );
}
add_action( 'elementor/editor/after_enqueue_scripts', 'themeasy_enqueue_editor_helpers_script' );

/**
 * Register the Nested Tabs element type in the editor. (Premium — gated.)
 *
 * Widget_Nested_Base alone does not make Elementor mount a widget's child
 * containers: the editor also needs an element type whose view extends the
 * nested-elements NestedView. Without it the panels render empty on the canvas
 * even though the document model holds the whole nested tree.
 *
 * Enqueued on `before_enqueue_scripts`, depending on `nested-elements` — the
 * same slot Elementor's own Nested Tabs / Accordion use. Since Elementor 4.3
 * the `elementor/nested-element-type-loaded` event fires in the microtasks
 * right after `elementor-editor-loader` calls `elementor.start()`, so a script
 * printed after the loader (anything on `after_enqueue_scripts`) attaches its
 * listener too late: the type never registers, and a freshly dropped widget
 * gets the plain widget model with NO child containers to drop widgets into.
 *
 * filemtime, not TMS_VER: this file and the widget's content_template() must
 * move in lockstep, and a version-pinned URL serves stale editor JS across
 * same-version builds.
 *
 * @return void
 */
function themeasy_enqueue_editor_nested_tabs_script(): void {
  if ( !\Themeasy\Core\Entitlement::can_use_widgets() ) {
    return;
  }

  $path = __DIR__ . '/assets/js/themeasy-el-nested-tabs.min.js';

  if ( !file_exists( $path ) ) {
    return;
  }

  wp_enqueue_script(
    'themeasy-editor-nested-tabs',
    TMS_E_ASSETS_URL . 'js/themeasy-el-nested-tabs.min.js',
    ['nested-elements'],
    (string) filemtime( $path ),
    true
  );
}
add_action( 'elementor/editor/before_enqueue_scripts', 'themeasy_enqueue_editor_nested_tabs_script' );
