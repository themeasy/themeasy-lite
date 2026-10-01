<?php
/**
 * Themeasy Elementor FOUC Preload.
 *
 * Pre-enqueues every Themeasy widget's get_style_depends() CSS for the
 * above-the-fold Elementor documents that render on the current request, so
 * those handles print render-blocking in <head> instead of being late-printed
 * near </body>.
 *
 * Without this, widgets inside Global Sections (rendered on theme body hooks
 * such as tbase_header / tbase_main_top, after wp_enqueue_scripts and after
 * Elementor's own style pass) enqueue their CSS too late and cause a Flash of
 * Unstyled Content. The breadcrumbs widget is the worst case: it sits at the
 * very top of the page yet its CSS loads last.
 *
 * This pass covers the structural widget CSS only. The per-element settings CSS
 * (Elementor post-{id}.css) is handled separately, and for the header it is
 * already preloaded in modules__premium_only/global-sections/enqueue-assets.php.
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
 * "Above the fold" here means every region that paints at or near the top of
 * the viewport, so its CSS must be render-blocking in <head>:
 *  - site-wide top: header, before-header, before-content;
 *  - companion sidebars (their top sits beside the first screen of content);
 *  - the post/page entry intro: entry_thumbnail (featured image) + entry_header
 *    (title / meta / taxonomy — typically the LCP element), context-scoped;
 *  - the archive / search listing (the main content on those views), or the
 *    Product Archive section on a product archive;
 *  - the Summary section on a product page (Global Sections → Product);
 *  - the main queried document on singular views.
 *
 * Intentionally excluded (left to load lazily, no visible FOUC): footer,
 * before-footer, entry_footer, comments. Add them per-template via the
 * `themeasy/fouc/document_ids` filter if ever needed.
 *
 * The global-section template tags are guarded with function_exists() so this
 * file degrades gracefully when the Global Sections module is not loaded. The
 * context guards (is_singular / is_page / is_search / is_archive) mirror the
 * module's own render scoping in Action::register_hooks, so we never preload
 * CSS for a slot that will not render in the current context.
 *
 * @return int[] Unique, positive document IDs.
 */
function themeasy_get_render_bound_document_ids(): array {
  $ids = [];

  // Site-wide single-slot top regions, plus the companion sidebars.
  $single_slot_tags = [
    'themeasy_get_header_id',
    'themeasy_get_before_content_id',
    'themeasy_get_sidebar_left_id',
    'themeasy_get_sidebar_right_id',
  ];

  foreach ( $single_slot_tags as $tag ) {
    if ( function_exists( $tag ) ) {
      $id = $tag();

      if ( $id ) {
        $ids[] = (int) $id;
      }
    }
  }

  // Multi-slot before-header (returns a flat array|false of IDs).
  if ( function_exists( 'themeasy_get_before_header_id' ) ) {
    $slot = themeasy_get_before_header_id();

    if ( is_array( $slot ) ) {
      foreach ( $slot as $id ) {
        if ( $id ) {
          $ids[] = (int) $id;
        }
      }
    }
  }

  // Singular views: the main queried document + the entry intro overrides
  // (featured image and header), scoped to post vs page like the module does.
  if ( is_singular() ) {
    $queried_id = (int) get_queried_object_id();

    if ( $queried_id ) {
      $ids[] = $queried_id;
    }

    if ( is_singular( 'post' ) && function_exists( 'themeasy_get_post_id' ) ) {
      $get_override_id = 'themeasy_get_post_id';
    } elseif ( is_page() && function_exists( 'themeasy_get_page_id' ) ) {
      $get_override_id = 'themeasy_get_page_id';
    } else {
      $get_override_id = '';
    }

    if ( $get_override_id ) {
      foreach ( ['entry_thumbnail', 'entry_header'] as $override ) {
        $id = $get_override_id( $override );

        if ( $id ) {
          $ids[] = (int) $id;
        }
      }
    }

    // A product page's Summary section is its top region (gallery + summary);
    // the product lists below it stay out.
    if ( function_exists( 'themeasy_wc_get_product_section_id' ) ) {
      $id = themeasy_wc_get_product_section_id( 'summary' );

      if ( $id ) {
        $ids[] = $id;
      }
    }
  }

  // Archive / search listing — the above-the-fold content on those views. A
  // product archive (shop, product taxonomies, product search) renders its
  // Product Archive section when one matches (tms-archive-section.php), and
  // never an Archive Loop or Search Results section.
  if ( function_exists( 'themeasy_get_archive_id' ) ) {
    if ( function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() ) ) {
      $id = function_exists( 'themeasy_wc_get_archive_section_id' ) ? themeasy_wc_get_archive_section_id() : false;
    } elseif ( is_search() ) {
      $id = themeasy_get_archive_id( 'search_results' );
    } elseif ( is_archive() || is_home() ) {
      $id = themeasy_get_archive_id( 'archive' );
    } else {
      $id = false;
    }

    if ( $id ) {
      $ids[] = (int) $id;
    }
  }

  /**
   * Filter the set of documents scanned for FOUC preloading.
   *
   * Escape hatch to add below-the-fold sections (e.g. footer, comments) or to
   * drop specific documents per template.
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
    if ( !str_starts_with( $type, 'themeasy-' ) ) {
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
