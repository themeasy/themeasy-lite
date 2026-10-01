<?php
/**
 * Portfolio Post Type and Taxonomies Registration.
 *
 * @package Themeasy\Core
 * @since 1.0.0
 */

namespace Themeasy\Core;

defined( 'ABSPATH' ) || exit;

class Portfolio_Post_Type {
  /**
   * Singleton instance.
   *
   * @var self|null
   */
  private static ?self $instance = null;

  /**
   * Returns the singleton instance.
   */
  public static function instance(): self {
    return self::$instance ??= new self();
  }

  /**
   * Class constructor.
   */
  private function __construct() {
    add_action( 'init', [$this, 'register_post_type'] );
    add_action( 'init', [$this, 'register_taxonomies'] );
  }

  /**
   * Registers the custom post type for Portfolio items.
   */
  public function register_post_type(): void {
    $labels = [
      'name' => __( 'Portfolio', 'themeasy-lite' ),
      'singular_name' => __( 'Item', 'themeasy-lite' ),
      'menu_name' => __( 'Portfolio', 'themeasy-lite' ),
      'all_items' => __( 'All Items', 'themeasy-lite' ),
      'add_new' => __( 'Add New', 'themeasy-lite' ),
      'add_new_item' => __( 'Add Portfolio Item', 'themeasy-lite' ),
      'edit_item' => __( 'Edit Item', 'themeasy-lite' ),
      'new_item' => __( 'New Portfolio Item', 'themeasy-lite' ),
      'view_item' => __( 'View Item', 'themeasy-lite' ),
      'search_items' => __( 'Search Items', 'themeasy-lite' ),
      'not_found' => __( 'No items found', 'themeasy-lite' ),
      'not_found_in_trash' => __( 'No items found in trash', 'themeasy-lite' ),
      'parent_item_colon' => __( 'Parent Portfolio:', 'themeasy-lite' ),
      'featured_image' => __( 'Featured image', 'themeasy-lite' ),
      'set_featured_image' => __( 'Set featured image', 'themeasy-lite' ),
      'remove_featured_image' => __( 'Remove featured image', 'themeasy-lite' ),
      'use_featured_image' => __( 'Use featured image', 'themeasy-lite' ),
      'archives' => __( 'Portfolio items archive', 'themeasy-lite' ),
      'insert_into_item' => __( 'Insert into item', 'themeasy-lite' ),
      'uploaded_to_this_item' => __( 'Upload to this item', 'themeasy-lite' ),
      'filter_items_list' => __( 'Filter items', 'themeasy-lite' ),
      'items_list_navigation' => __( 'Portfolio items list navigation', 'themeasy-lite' ),
      'items_list' => __( 'Portfolio items list', 'themeasy-lite' ),
    ];

    $args = [
      'label' => __( 'Portfolio', 'themeasy-lite' ),
      'labels' => $labels,
      'description' => __( 'Portfolio post type.', 'themeasy-lite' ),
      'public' => true,
      'publicly_queryable' => true,
      'show_ui' => true,
      'show_in_rest' => true,
      'has_archive' => false,
      'show_in_menu' => true,
      'exclude_from_search' => false,
      'capability_type' => 'post',
      'map_meta_cap' => true,
      'hierarchical' => false,
      'rewrite' => ['slug' => 'portfolio', 'with_front' => true],
      'query_var' => true,
      'menu_position' => 5,
      'menu_icon' => 'dashicons-open-folder',
      'supports' => ['title', 'excerpt', 'comments', 'editor', 'thumbnail', 'elementor'],
    ];

    register_post_type( 'portfolio', $args );
  }

  /**
   * Registers the portfolio taxonomies: category and tag.
   */
  public function register_taxonomies(): void {
    // Category
    $category_labels = [
      'name' => _x( 'Categories', 'taxonomy general name', 'themeasy-lite' ),
      'singular_name' => _x( 'Category', 'taxonomy singular name', 'themeasy-lite' ),
      'search_items' => __( 'Search Categories', 'themeasy-lite' ),
      'all_items' => __( 'Categories', 'themeasy-lite' ),
      'edit_item' => __( 'Edit Category', 'themeasy-lite' ),
      'update_item' => __( 'Update Category', 'themeasy-lite' ),
      'add_new_item' => __( 'Add New Category', 'themeasy-lite' ),
      'new_item_name' => __( 'New Portfolio Category', 'themeasy-lite' ),
      'menu_name' => __( 'Categories', 'themeasy-lite' ),
      'not_found' => __( 'No categories found.', 'themeasy-lite' ),
    ];

    $category_args = [
      'hierarchical' => true,
      'labels' => $category_labels,
      'show_ui' => true,
      'show_in_rest' => true,
      'show_admin_column' => true,
      'query_var' => true,
      'rewrite' => ['slug' => 'portfolio-category'],
    ];

    register_taxonomy( 'portfolio-category', ['portfolio'], $category_args );

    // Tag
    $tag_labels = [
      'name' => _x( 'Tags', 'taxonomy general name', 'themeasy-lite' ),
      'singular_name' => _x( 'Tag', 'taxonomy singular name', 'themeasy-lite' ),
      'search_items' => __( 'Search Tags', 'themeasy-lite' ),
      'all_items' => __( 'Tags', 'themeasy-lite' ),
      'edit_item' => __( 'Edit Tag', 'themeasy-lite' ),
      'update_item' => __( 'Update Tag', 'themeasy-lite' ),
      'add_new_item' => __( 'Add New Tag', 'themeasy-lite' ),
      'new_item_name' => __( 'New Portfolio Tag', 'themeasy-lite' ),
      'menu_name' => __( 'Tags', 'themeasy-lite' ),
      'not_found' => __( 'No tags found.', 'themeasy-lite' ),
    ];

    $tag_args = [
      'hierarchical' => false,
      'labels' => $tag_labels,
      'show_ui' => true,
      'show_in_rest' => true,
      'show_admin_column' => true,
      'query_var' => true,
      'rewrite' => ['slug' => 'portfolio-tag'],
    ];

    register_taxonomy( 'portfolio-tag', ['portfolio'], $tag_args );
  }
}
