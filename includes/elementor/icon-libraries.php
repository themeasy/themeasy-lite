<?php
/**
 * Themeasy Icons libraries (ty-*) for the Elementor icon picker.
 *
 * Registers each library listed in the generated manifest
 * (assets/media/svg/ty-manifest.php, built by `npm run icons:build`) as a
 * native tab in Elementor's icon picker. Every library is a self-contained
 * flat dir under assets/media/svg/:
 *
 *   {dir}/*.svg       — one inline SVG per icon (frontend rendering)
 *   {dir}/icons.json  — the picker's fetchJson icon list
 *   {dir}/picker.css  — editor-only preview classes (url() mask references
 *                       to the sibling .svg files, currentColor via
 *                       background-color)
 *
 * Premium libraries (the Solar tabs) live in __premium_only-suffixed dirs:
 * the Free build strip removes the whole dir — icons, JSON and CSS together —
 * and the existence checks below skip the tab, so no entitlement code is
 * involved.
 *
 * The picker preview is CSS mask based, but the frontend NEVER loads that
 * CSS: themeasy_render_icon_html() intercepts every ty-* library and outputs
 * the inline SVG instead (zero extra requests, currentColor preserved — for
 * Solar Duotone the secondary tone is an opacity attribute, so one color
 * paints both tones), so Elementor's font-icon enqueue path is never reached
 * for these tabs.
 *
 * Free baseline: ty-feather ships in both builds and is the ONLY defaults
 * library — Free widgets (Card, Icon, …) default to ty-feather icons; the
 * Solar libraries are customization-only, never a widget default.
 *
 * @package Themeasy\Elementor
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the ty-* libraries as Elementor icon picker tabs.
 *
 * The stored control value follows Elementor's class-shaped convention
 * ("ty-solar-line-heart", library "ty-solar-line");
 * themeasy_parse_ty_icon_name() maps it back to the SVG file at render time.
 *
 * @param array $tabs Registered additional tabs.
 * @return array
 */
function themeasy_register_ty_icon_tabs( array $tabs ): array {
  $manifest = themeasy_get_ty_icon_manifest();

  if ( empty( $manifest['libraries'] ) || !is_array( $manifest['libraries'] ) ) {
    return $tabs;
  }

  // Picker tab order — libraries missing from this list keep their manifest
  // position after it.
  $tab_order = [ 'ty-feather', 'ty-solar-line', 'ty-solar-solid', 'ty-solar-duotone', 'ty-solar-broken' ];
  $libraries = array_merge(
    array_intersect_key( array_flip( $tab_order ), $manifest['libraries'] ),
    $manifest['libraries']
  );

  // The manifest labels read "Themeasy — Feather"; the Agency White Label
  // swaps the brand for its own (backlog #272). Themeasy by default.
  $brand = (string) apply_filters( 'themeasy/brand/name', 'Themeasy' );

  foreach ( $libraries as $library => $data ) {
    $library = (string) $library;
    $dir = (string) ( $data['dir'] ?? $library );
    $css_path = TMS_PATH . "assets/media/svg/{$dir}/picker.css";
    $json_path = TMS_PATH . "assets/media/svg/{$dir}/icons.json";

    // The existence checks double as the Free-build gate: premium libraries
    // live in __premium_only-suffixed dirs the strip removes, so their tabs
    // self-skip in the Free build.
    if ( !str_starts_with( $library, 'ty-' ) || !file_exists( $css_path ) || !file_exists( $json_path ) ) {
      continue;
    }

    // filemtime, not TMS_VER: regenerations (`npm run icons:build`) land
    // between releases and each tab's picker CSS + fetchJson must bust with
    // them.
    $css_version = (string) filemtime( $css_path );

    // Tab glyph: the manifest's label_icon (fallback: first icon of the set) —
    // the picker CSS is loaded in the editor, so the mask class renders it
    // natively.
    $label_icon = (string) ( $data['label_icon'] ?? ( $data['icons'][0] ?? '' ) );

    $label = (string) ( $data['label'] ?? $library );
    if ( str_starts_with( $label, 'Themeasy — ' ) ) {
      $label = $brand . substr( $label, strlen( 'Themeasy' ) );
    }

    $tabs[$library] = [
      'name' => $library,
      'label' => $label,
      'labelIcon' => '' !== $label_icon ? $library . '-' . $label_icon : 'eicon-star',
      'prefix' => $library . '-',
      'displayPrefix' => '',
      'url' => TMS_URL . "assets/media/svg/{$dir}/picker.css",
      'ver' => $css_version,
      'fetchJson' => add_query_arg( 'ver', (string) filemtime( $json_path ), TMS_URL . "assets/media/svg/{$dir}/icons.json" ),
      'native' => false,
    ];
  }

  return $tabs;
}
add_filter( 'elementor/icons_manager/additional_tabs', 'themeasy_register_ty_icon_tabs' );

/**
 * Keep the picker CSS out of the frontend.
 *
 * Elementor records every icon library a page uses and enqueues its tab CSS
 * on the frontend (handle "elementor-icons-{tab}"). For ty-* tabs that CSS is
 * pure editor concern — the frontend glyph is ALWAYS the inline SVG emitted by
 * themeasy_render_icon_html(), so the mask stylesheet would be a dead request
 * on every page using a ty-* icon. Dequeue late; the editor document loads it
 * through its own admin-side pipeline, which this hook never touches.
 * Dequeuing a handle that never registered (a stripped premium library in the
 * Free build) is a no-op.
 *
 * @return void
 */
function themeasy_dequeue_ty_picker_css_frontend(): void {
  $manifest = themeasy_get_ty_icon_manifest();

  foreach ( array_keys( $manifest['libraries'] ?? [] ) as $library ) {
    wp_dequeue_style( 'elementor-icons-' . $library );
    wp_deregister_style( 'elementor-icons-' . $library );
  }
}
add_action( 'wp_enqueue_scripts', 'themeasy_dequeue_ty_picker_css_frontend', 20 );
