<?php
/**
 * Template Context Trait
 *
 * Provides cached helper methods for determining the current template type
 * inside the Elementor editor and frontend rendering context.
 *
 * Used by Container_Layout, Container_Style, Container_Animations, and
 * Header_Builder to avoid duplicating the same post-meta lookup.
 *
 * @package Themeasy\Elementor
 * @since 1.0.0
 */

namespace Themeasy\Elementor;

defined( 'ABSPATH' ) || exit;

trait Template_Context {
  /**
   * Returns true if the Elementor document currently being processed
   * is a header template (section_header or section_before_header).
   *
   * Resolves the post ID from the current Elementor document rather
   * than the queried object. This is required for global sections
   * rendered as headers on regular pages, where the queried object
   * is the page being viewed (not the section itself) — which would
   * otherwise make the check return false and cause the header
   * wrapper classes to be cached without `.tms-e-header`,
   * `.tms-e-navbar`, etc., breaking the section once Elementor's
   * element-cache module clears its cache (e.g., on plugin/theme
   * activation, deactivation, or switch).
   *
   * Result is intentionally not cached: in the same request the
   * answer differs between the section render (true) and the
   * surrounding page render (false).
   *
   * @return bool
   */
  protected static function is_header_template(): bool {
    $post_id = self::resolve_template_post_id();

    if ( !$post_id ) {
      return false;
    }

    $template_type = get_post_meta( $post_id, 'themeasy_template_type', true );

    return in_array( $template_type, ['section_header', 'section_before_header'], true );
  }

  /**
   * Returns true if the header-template controls should be registered
   * on the current Container element.
   *
   * On the frontend, ALWAYS register: the Container controls stack is
   * shared across all documents in a request, and on Elementor-built
   * pages it gets initialized for the page (in Frontend::enqueue_styles
   * → Post_CSS::create(get_the_ID())->enqueue()) BEFORE tbase_header
   * runs the section render. If we conditioned registration on
   * is_header_template() here, the stack would be cached without
   * Themeasy header controls, and the section's regenerated post-CSS
   * would lack the `{{WRAPPER}}.tms-e-navbar--*` selectors — leaving
   * the header unstyled until the section is saved again.
   *
   * In the editor, only register when editing a header template, to
   * keep the controls panel clean for non-header containers.
   *
   * Functional safety: even when registered globally, the controls'
   * selectors are gated by header-only wrapper classes (e.g.
   * `.tms-e-navbar--desktop`) which are added exclusively by
   * Header_Builder::before_render() after its own is_header_template()
   * check, so non-header containers receive no CSS effect from these
   * registrations.
   *
   * @return bool
   */
  protected static function should_register_header_controls(): bool {
    if (
      did_action( 'elementor/loaded' )
      && class_exists( '\Elementor\Plugin' )
      && \Elementor\Plugin::$instance->editor->is_edit_mode()
    ) {
      return self::is_header_template();
    }

    return true;
  }

  /**
   * Resolves the post ID of the template currently being processed.
   *
   * Order of preference:
   *  1. Elementor's current document — set during render of the section
   *     (via documents->switch_to_document) and during editor controls
   *     registration. Works for frontend global-section renders, the
   *     editor, and preview.
   *  2. get_the_ID() in admin — for admin screens where the document
   *     manager isn't initialized yet (e.g., very early hooks).
   *  3. get_queried_object_id() — last resort, e.g., direct frontend
   *     visit to the section permalink (canvas template).
   *
   * @return int
   */
  private static function resolve_template_post_id(): int {
    if ( did_action( 'elementor/loaded' ) && class_exists( '\Elementor\Plugin' ) ) {
      $current_doc = \Elementor\Plugin::$instance->documents->get_current();
      if ( $current_doc ) {
        return (int) $current_doc->get_main_id();
      }
    }

    if ( is_admin() ) {
      return (int) get_the_ID();
    }

    return (int) get_queried_object_id();
  }
}
