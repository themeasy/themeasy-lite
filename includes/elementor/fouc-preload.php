<?php
/**
 * Themeasy Elementor FOUC Preload.
 *
 * Pre-enqueues every Themeasy widget's get_style_depends() CSS for the
 * above-the-fold Elementor documents that render on the current request, so
 * those handles print render-blocking in <head> instead of being late-printed
 * near </body>, which shows as a Flash of Unstyled Content.
 *
 * The document scanned here is the page itself. A document rendered outside
 * the main content (on a theme body hook, after wp_enqueue_scripts and after
 * Elementor's own style pass) joins through the `themeasy/fouc/document_ids`
 * filter.
 *
 * This pass covers the structural widget CSS only. The per-element settings CSS
 * (Elementor post-{id}.css) is handled by Elementor.
 *
 * @package Themeasy\Elementor
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Collect the render-bound documents and pre-enqueue their widget CSS early.
 *
 * Hooked at priority 11: after widget asset registration (priority 7) so the
 * handles are already registered, and after the plugin core CSS (priority 10)
 * so widget styles keep their original cascade precedence over the core
 * utilities (before this pass they printed last, in the body). Still well
 * ahead of Elementor's own style pass (priority 20), so the styles print
 * render-blocking in <head> instead of late in the body.
 *
 * @return void
 */
function themeasy_preload_widget_styles(): void {
  if ( !class_exists( '\Elementor\Plugin' ) || !did_action( 'elementor/loaded' ) ) {
    return;
  }

  // Never run inside the editor panel or the preview iframe — Elementor manages
  // its own asset lifecycle there and the body-hook render model does not apply.
  $elementor = \Elementor\Plugin::$instance;

  if ( $elementor->editor->is_edit_mode() || $elementor->preview->is_preview_mode() ) {
    return;
  }

  $document_ids = themeasy_get_render_bound_document_ids();

  if ( empty( $document_ids ) ) {
    return;
  }

  $widget_types = [];

  foreach ( $document_ids as $document_id ) {
    foreach ( themeasy_collect_document_widget_types( $document_id ) as $type ) {
      // De-dupe via array keys.
      $widget_types[$type] = true;
    }
  }

  if ( empty( $widget_types ) ) {
    return;
  }

  themeasy_enqueue_widget_style_depends( array_keys( $widget_types ) );
}
add_action( 'wp_enqueue_scripts', 'themeasy_preload_widget_styles', 11 );

/**
 * Gather the above-the-fold Elementor document IDs that render on this request.
 *
 * "Above the fold" means every region that paints at or near the top of the
 * viewport, so its CSS must be render-blocking in <head>. Here that is the main
 * queried document on singular views.
 *
 * @return int[] Unique, positive document IDs.
 */
function themeasy_get_render_bound_document_ids(): array {
  $ids = [];

  if ( is_singular() ) {
    $queried_id = (int) get_queried_object_id();

    if ( $queried_id ) {
      $ids[] = $queried_id;
    }
  }

  /**
   * Filter the set of documents scanned for FOUC preloading.
   *
   * Add the documents a template renders outside the main content (a header,
   * a sidebar, a footer), or drop specific documents per template.
   *
   * @param int[] $ids Document IDs.
   */
  $ids = (array) apply_filters( 'themeasy/fouc/document_ids', $ids );

  return array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );
}

/**
 * Recursively collect every widget type present in an Elementor document,
 * following nested template references (Template widget, Loop Grid, global
 * widgets) into their child documents.
 *
 * Results are statically cached per document ID for the request, because
 * get_elements_data() decodes post-meta JSON on every call. The cache is also
 * seeded early so cyclic template references cannot loop forever.
 *
 * @param int $doc_id Elementor document (post) ID.
 * @return string[] Widget type slugs (may contain duplicates; caller de-dupes).
 */
function themeasy_collect_document_widget_types( int $doc_id ): array {
  static $cache = [];

  if ( isset( $cache[$doc_id] ) ) {
    return $cache[$doc_id];
  }

  // Seed early to guard against cyclic template references.
  $cache[$doc_id] = [];

  $document = \Elementor\Plugin::$instance->documents->get( $doc_id );

  if ( !$document || !$document->is_built_with_elementor() ) {
    return $cache[$doc_id];
  }

  $elements = $document->get_elements_data();

  if ( !is_array( $elements ) ) {
    return $cache[$doc_id];
  }

  $types = [];
  $refs = [];

  themeasy_walk_elements_for_widget_types( $elements, $types, $refs );

  // Resolve nested template references; recursion depth and cycles are bounded
  // by the early-seeded static cache above.
  foreach ( $refs as $ref_id ) {
    $ref_id = absint( $ref_id );

    if ( $ref_id && $ref_id !== $doc_id ) {
      $types = array_merge( $types, themeasy_collect_document_widget_types( $ref_id ) );
    }
  }

  $cache[$doc_id] = $types;

  return $types;
}

/**
 * Depth-first walk of an Elementor elements tree, collecting widget types and
 * referenced template IDs.
 *
 * @param array    $elements Element nodes (each may have a nested 'elements').
 * @param string[] $types    Accumulator for widget types, passed by reference.
 * @param int[]    $refs     Accumulator for template references, passed by reference.
 * @return void
 */
function themeasy_walk_elements_for_widget_types( array $elements, array &$types, array &$refs ): void {
  foreach ( $elements as $element ) {
    if ( !is_array( $element ) ) {
      continue;
    }

    if ( !empty( $element['widgetType'] ) ) {
      $types[] = (string) $element['widgetType'];
    }

    // Template references: global widgets store templateID at element level;
    // the Template widget / Loop Grid store template_id in their settings.
    if ( !empty( $element['templateID'] ) ) {
      $refs[] = $element['templateID'];
    }

    if ( !empty( $element['settings']['template_id'] ) ) {
      $refs[] = $element['settings']['template_id'];
    }

    if ( !empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
      themeasy_walk_elements_for_widget_types( $element['elements'], $types, $refs );
    }
  }
}

/**
 * Resolve widget types to their registered instances and enqueue each style
 * dependency that WordPress already knows about.
 *
 * Only Themeasy widgets are processed — native Elementor widgets already print
 * their CSS in <head> via Elementor's own pass. Only registered handles are
 * enqueued, so WordPress pulls in vendor/module dependencies via the dependency
 * graph automatically and unknown handles are skipped safely.
 *
 * @param string[] $widget_types Unique widget type slugs.
 * @return void
 */
function themeasy_enqueue_widget_style_depends( array $widget_types ): void {
  $widgets_manager = \Elementor\Plugin::$instance->widgets_manager;

  // widgetType => string[] of style handles, cached for the request.
  static $handle_cache = [];

  foreach ( $widget_types as $type ) {
    // Native Elementor widgets are handled by Elementor itself.
    if ( 0 !== strpos( $type, 'themeasy-' ) ) {
      continue;
    }

    if ( !isset( $handle_cache[$type] ) ) {
      $widget = $widgets_manager->get_widget_types( $type );

      $handle_cache[$type] = ( $widget instanceof \Elementor\Widget_Base )
        ? (array) $widget->get_style_depends()
        : [];
    }

    foreach ( $handle_cache[$type] as $handle ) {
      if ( wp_style_is( $handle, 'registered' ) && !wp_style_is( $handle, 'enqueued' ) ) {
        wp_enqueue_style( $handle );
      }
    }
  }
}
