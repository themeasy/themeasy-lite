<?php
/**
 * Elementor Template Shortcodes
 *
 * Provides the [elementor-template] shortcode and admin enhancements.
 *
 * @package Themeasy\Elementor
 * @since 1.0.0
 */

namespace Themeasy\Elementor;

defined( 'ABSPATH' ) || exit;

class Template_Shortcodes {
  /**
   * Singleton instance.
   *
   * @var self|null
   */
  private static $instance = null;

  /**
   * Get instance.
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
   * Hook into WordPress.
   */
  public function init(): void {
    add_shortcode( 'elementor-template', [$this, 'render_shortcode'] );
    add_action( 'manage_elementor_library_posts_columns', [$this, 'add_column'] );
    add_action( 'manage_elementor_library_posts_custom_column', [$this, 'render_column'], 10, 2 );
  }

  /**
   * Render the [elementor-template id="123"] shortcode.
   *
   * @param array $params Shortcode parameters.
   * @return string
   */
  public function render_shortcode( array $params = [] ): string {
    $args = shortcode_atts(
      [
        'id' => '',
        'css' => 'true',
      ],
      $params,
      'elementor-template'
    );

    $template_id = absint( $args['id'] );
    $include_css = trim( $args['css'] );

    if ( !$template_id || !get_post( $template_id ) ) {
      return '';
    }

    // Prevent recursive rendering
    if ( get_the_ID() === (int) $template_id ) {
      return esc_html__( 'Template ID cannot be the same as the current page.', 'themeasy-lite' );
    }

    // Apply WPML filter if present
    $template_id = apply_filters( 'wpml_object_id', $template_id, 'elementor_library', true );

    return \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $template_id, $include_css );
  }

  /**
   * Add shortcode column to Elementor templates list.
   *
   * @param array $columns Existing columns.
   * @return array
   */
  public function add_column( array $columns ): array {
    $columns['themeasy_shortcode'] = esc_html__( 'Shortcode', 'themeasy-lite' );
    return $columns;
  }

  /**
   * Render shortcode input in custom column.
   *
   * @param string $column_name Column key.
   * @param int    $post_id     Post ID.
   */
  public function render_column( string $column_name, int $post_id ): void {
    if ( 'themeasy_shortcode' !== $column_name ) {
      return;
    }

    $post = get_post( $post_id );

    if ( $post && 'publish' === $post->post_status ) {
      printf(
        '<input type="text" class="widefat" onfocus="this.select()" value="%s" readonly>',
        esc_attr( '[elementor-template id="' . absint( $post_id ) . '"]' )
      );
    }
  }
}
