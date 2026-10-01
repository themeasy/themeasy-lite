<?php
/**
 * Media Meta Fields for Attachments
 *
 * Adds custom fields (Category, Custom Link, Video URL) to WordPress media attachments.
 *
 * @package Themeasy\Core
 * @since 1.0.0
 */

namespace Themeasy\Core;

defined( 'ABSPATH' ) || exit;

class Custom_Media_Meta_Fields {
  /**
   * Singleton instance.
   *
   * @var self|null
   */
  private static ?self $instance = null;

  /**
   * Initialize hooks.
   *
   * @return void
   */
  public static function instance(): self {
    if ( is_null( self::$instance ) ) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  /**
   * Register WordPress hooks for media meta fields.
   *
   * Registered in the constructor to match the singleton convention used by the
   * other core singletons (hooks bound on construction, not in instance()).
   */
  private function __construct() {
    add_filter( 'attachment_fields_to_edit', [$this, 'add_fields'], 10, 2 );
    add_filter( 'attachment_fields_to_save', [$this, 'save_fields'], 10, 2 );
  }

  /**
   * Add custom fields to the attachment edit form.
   *
   * @param array   $form_fields Existing fields.
   * @param WP_Post $post        The attachment post object.
   * @return array Modified fields.
   */
  public function add_fields( array $form_fields, \WP_Post $post ): array {
    $form_fields['tms_category'] = [
      'label' => __( 'Category', 'themeasy-lite' ),
      'input' => 'text',
      'value' => get_post_meta( $post->ID, '_tms_category', true ),
      'helps' => __( 'Enter categories separated by commas to group images in the gallery.', 'themeasy-lite' ),
    ];

    $form_fields['tms_custom_link'] = [
      'label' => __( 'Custom Link', 'themeasy-lite' ),
      'input' => 'text',
      'value' => get_post_meta( $post->ID, '_tms_custom_link', true ),
      'helps' => __( 'Enter an external URL to make this image clickable in the gallery.', 'themeasy-lite' ),
    ];

    $form_fields['tms_video_url'] = [
      'label' => __( 'Video URL', 'themeasy-lite' ),
      'input' => 'text',
      'value' => get_post_meta( $post->ID, '_tms_video_url', true ),
      'helps' => __( 'Enter a YouTube or Vimeo URL to show a video in the product gallery.', 'themeasy-lite' ),
    ];

    return $form_fields;
  }

  /**
   * Save custom media fields when the attachment is saved.
   *
   * @param array $post       The attachment post data.
   * @param array $attachment Form data.
   * @return array Modified post data.
   */
  public function save_fields( array $post, array $attachment ): array {
    if ( !current_user_can( 'edit_post', $post['ID'] ) ) {
      return $post;
    }

    // Category
    if ( isset( $attachment['tms_category'] ) ) {
      update_post_meta( $post['ID'], '_tms_category', sanitize_text_field( $attachment['tms_category'] ) );
    } else {
      delete_post_meta( $post['ID'], '_tms_category' );
    }

    // Custom Link
    if ( isset( $attachment['tms_custom_link'] ) ) {
      update_post_meta( $post['ID'], '_tms_custom_link', esc_url_raw( $attachment['tms_custom_link'] ) );
    } else {
      delete_post_meta( $post['ID'], '_tms_custom_link' );
    }

    // Video URL
    if ( isset( $attachment['tms_video_url'] ) ) {
      update_post_meta( $post['ID'], '_tms_video_url', esc_url_raw( $attachment['tms_video_url'] ) );
    } else {
      delete_post_meta( $post['ID'], '_tms_video_url' );
    }

    return $post;
  }
}
