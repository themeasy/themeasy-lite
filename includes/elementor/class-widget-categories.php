<?php
/**
 * Widget Categories registry.
 *
 * Single source of truth for the Themeasy Elementor widget categories.
 * Used by:
 *  - Admin Widget Manager tab to render section groups in the same order.
 *  - Elementor_Loader to register and reorder editor categories, and to
 *    decide which categories to show based on the current edit context.
 *
 * The registry key IS the physical widget directory (first path segment under
 * either widget tree) — dir, editor category, and admin group stay coherent by
 * construction. A widget's get_categories() must always include its own dir's
 * category_slug; extra slugs are allowed for cross-context visibility but must
 * follow the minimal-set rule (see .claude/docs/widget-map.md, the canonical
 * category/context/ordering map).
 *
 * Free build: the six content-tier categories collapse into a single
 * "Themeasy — Essentials" category (FREE_CATEGORY_SLUG). Free widgets declare
 * [<thematic slug>, FREE_CATEGORY_SLUG]; each build registers only one of the
 * two, and Elementor ignores declared-but-unregistered categories.
 *
 * @package Themeasy\Elementor
 * @since 1.0.0
 */

namespace Themeasy\Elementor;

defined( 'ABSPATH' ) || exit;

class Widget_Categories {
  /**
   * The single editor category registered in the Free build ("Essentials").
   *
   * Free widgets declare it as their second category; the premium build never
   * registers it, so there it is inert. Keeps the historical slug so nothing
   * user-facing depends on a rename.
   *
   * @var string
   */
  public const FREE_CATEGORY_SLUG = 'themeasy_content_category';

  /**
   * Canonical category definitions. The array order is the display order
   * for both the admin panel and the Elementor editor (after `favorites`),
   * and the deterministic widget REGISTRATION order across directories
   * (which fixes where cross-category widgets land inside a panel group).
   *
   * Keys are the physical widget directories. The first six are the
   * content-tier thematic categories (split of the former `content/`);
   * the rest are the contextual categories.
   *
   * @return array<string, array{category_slug: string, admin_label: string, editor_label: string, requires_woo: bool}>
   */
  private static function definitions(): array {
    return [
      'text' => [
        'category_slug' => 'themeasy_text_category',
        'admin_label' => __( 'Text & Typography', 'themeasy-lite' ),
        'editor_label' => __( 'Themeasy — Text & Typography', 'themeasy-lite' ),
        'requires_woo' => false,
      ],
      'media' => [
        'category_slug' => 'themeasy_media_category',
        'admin_label' => __( 'Images & Media', 'themeasy-lite' ),
        'editor_label' => __( 'Themeasy — Images & Media', 'themeasy-lite' ),
        'requires_woo' => false,
      ],
      'elements' => [
        'category_slug' => 'themeasy_elements_category',
        'admin_label' => __( 'Elements', 'themeasy-lite' ),
        'editor_label' => __( 'Themeasy — Elements', 'themeasy-lite' ),
        'requires_woo' => false,
      ],
      'blocks' => [
        'category_slug' => 'themeasy_blocks_category',
        'admin_label' => __( 'Blocks & Cards', 'themeasy-lite' ),
        'editor_label' => __( 'Themeasy — Blocks & Cards', 'themeasy-lite' ),
        'requires_woo' => false,
      ],
      'showcase' => [
        'category_slug' => 'themeasy_showcase_category',
        'admin_label' => __( 'Sliders & Showcase', 'themeasy-lite' ),
        'editor_label' => __( 'Themeasy — Sliders & Showcase', 'themeasy-lite' ),
        'requires_woo' => false,
      ],
      'data' => [
        'category_slug' => 'themeasy_data_category',
        'admin_label' => __( 'Data & Conversion', 'themeasy-lite' ),
        'editor_label' => __( 'Themeasy — Data & Conversion', 'themeasy-lite' ),
        'requires_woo' => false,
      ],
      'post' => [
        'category_slug' => 'themeasy_post_category',
        'admin_label' => __( 'Post & Portfolio', 'themeasy-lite' ),
        'editor_label' => __( 'Themeasy — Post & Portfolio', 'themeasy-lite' ),
        'requires_woo' => false,
      ],
      'woocommerce' => [
        'category_slug' => 'themeasy_woocommerce_category',
        'admin_label' => __( 'WooCommerce', 'themeasy-lite' ),
        'editor_label' => __( 'Themeasy — WooCommerce', 'themeasy-lite' ),
        'requires_woo' => true,
      ],
      'header' => [
        'category_slug' => 'themeasy_header_category',
        'admin_label' => __( 'Header', 'themeasy-lite' ),
        'editor_label' => __( 'Themeasy — Header', 'themeasy-lite' ),
        'requires_woo' => false,
      ],
      'footer' => [
        'category_slug' => 'themeasy_footer_category',
        'admin_label' => __( 'Footer', 'themeasy-lite' ),
        'editor_label' => __( 'Themeasy — Footer', 'themeasy-lite' ),
        'requires_woo' => false,
      ],
      'site' => [
        'category_slug' => 'themeasy_site_category',
        'admin_label' => __( 'Site Utilities', 'themeasy-lite' ),
        'editor_label' => __( 'Themeasy — Site Utilities', 'themeasy-lite' ),
        'requires_woo' => false,
      ],
      'megamenu' => [
        'category_slug' => 'themeasy_megamenu_category',
        'admin_label' => __( 'Megamenu', 'themeasy-lite' ),
        'editor_label' => __( 'Themeasy — Megamenu', 'themeasy-lite' ),
        'requires_woo' => false,
      ],
    ];
  }

  /**
   * Editor label for the Free build's single category.
   *
   * @return string
   */
  public static function free_editor_label(): string {
    return __( 'Themeasy — Essentials', 'themeasy-lite' );
  }

  /**
   * Returns all category definitions, filtering out WooCommerce when WC is inactive.
   *
   * @return array<string, array{category_slug: string, admin_label: string, editor_label: string, requires_woo: bool}>
   */
  public static function get_all(): array {
    $definitions = self::definitions();

    if ( !class_exists( 'WooCommerce' ) ) {
      foreach ( $definitions as $dir => $def ) {
        if ( $def['requires_woo'] ) {
          unset( $definitions[$dir] );
        }
      }
    }

    return $definitions;
  }

  /**
   * Detects the current editor context based on the post being edited.
   *
   * Possible return values:
   *  - 'megamenu'       → editing a megamenu post.
   *  - 'header'         → global section with template_type section_header / section_before_header.
   *  - 'footer'         → global section with template_type section_footer / section_before_footer.
   *  - 'content'        → global section that wraps/overrides body content:
   *                       section_before_content, section_page, section_post, section_product,
   *                       section_archive.
   *  - 'unset_section'  → global section without a template_type set yet (show all categories).
   *  - 'default'        → regular pages, posts, portfolio, etc. (includes section_404,
   *                       which benefits from the full authoring widget set).
   *
   * @return string
   */
  public static function detect_context(): string {
    $post_id = (int) get_the_ID();

    if ( get_post_type( $post_id ) === 'themeasy-megamenu' ) {
      return 'megamenu';
    }

    $template_type = get_post_meta( $post_id, 'themeasy_template_type', true );

    if ( in_array( $template_type, ['section_header', 'section_before_header'], true ) ) {
      return 'header';
    }

    if ( in_array( $template_type, ['section_footer', 'section_before_footer'], true ) ) {
      return 'footer';
    }

    $content_types = [
      'section_before_content',
      'section_page',
      'section_post',
      'section_product',
      'section_archive',
      'section_sidebar_left',
      'section_sidebar_right',
    ];

    if ( in_array( $template_type, $content_types, true ) ) {
      return 'content';
    }

    if ( get_post_type( $post_id ) === 'themeasy-sections' && empty( $template_type ) ) {
      return 'unset_section';
    }

    return 'default';
  }

  /**
   * Returns the directory keys visible in a given editor context, already in
   * the desired display order (each context defines both what shows and how
   * it is ordered — `get_editor_order()` consumes this list directly).
   *
   * Full matrix + rationale: .claude/docs/widget-map.md (section 3.3).
   *
   * Per-context rationale:
   *  - default       → the six content-tier categories + post/woocommerce:
   *                    the standard authoring set (includes section_404).
   *  - header        → header first; elements/text cover what a navbar
   *                    actually embeds (button, icons, socials, search, small
   *                    text); site for header-targeted utilities. Media,
   *                    blocks, showcase and data are deliberate noise cuts.
   *  - footer        → footer first; everything except showcase (sliders in
   *                    a footer are not a pattern) so authors can build link
   *                    columns, newsletter forms and "Latest Posts" sections.
   *  - content       → before/after-content sections wrap regular post/page
   *                    content, so the full authoring set + site fits best —
   *                    structural header/footer/megamenu categories hidden.
   *  - megamenu      → megamenu first; elements/text/media/blocks for panel
   *                    content, plus post/woocommerce (recent posts and
   *                    product embeds are standard megamenu fare).
   *  - unset_section → all categories in canonical order, as an escape hatch
   *                    when the user hasn't picked a section type yet.
   *
   * @param string $context Context returned by detect_context().
   * @return array<int, string>
   */
  public static function get_visible_for_context( string $context ): array {
    $rules = [
      'default' => ['text', 'media', 'elements', 'blocks', 'showcase', 'data', 'post', 'woocommerce'],
      'header' => ['header', 'elements', 'text', 'site'],
      'footer' => ['footer', 'text', 'media', 'elements', 'blocks', 'data', 'post', 'woocommerce', 'site'],
      'content' => ['text', 'media', 'elements', 'blocks', 'showcase', 'data', 'post', 'woocommerce', 'site'],
      'megamenu' => ['megamenu', 'elements', 'text', 'media', 'blocks', 'post', 'woocommerce'],
      'unset_section' => array_keys( self::definitions() ),
    ];

    $visible = $rules[$context] ?? $rules['default'];
    $allowed = array_keys( self::get_all() );

    return array_values( array_intersect( $visible, $allowed ) );
  }

  /**
   * Returns the category slug order for the Elementor editor in the given
   * context, including the built-in `favorites` category at the top.
   *
   * @param string $context Context returned by detect_context().
   * @return array<int, string>
   */
  public static function get_editor_order( string $context = 'default' ): array {
    $order = ['favorites'];
    $definitions = self::get_all();

    foreach ( self::get_visible_for_context( $context ) as $dir ) {
      if ( isset( $definitions[$dir] ) ) {
        $order[] = $definitions[$dir]['category_slug'];
      }
    }

    return $order;
  }

  /**
   * Curated display order of the individual widgets, per category.
   *
   * Single source of truth for the widget order in BOTH the Elementor editor
   * panel (Elementor_Loader::register_widgets) and the admin Widget Manager
   * (Tab_Widget_Manager::scan_widgets). Slugs are the file basenames (hyphen
   * form, e.g. `text-editor`) regardless of tier — a widget is placed by its
   * slug whether it lives in the Free `widgets/` tree or the Pro
   * `widgets__premium_only/` tree.
   *
   * The order inside each list is intentional (grouped by purpose, not
   * alphabetical). Any widget NOT listed here still registers — it falls to the
   * end of its category in alphabetical order (see sort_widget_slugs()), so a
   * newly added widget never disappears; it just wants an entry here to claim a
   * deliberate position.
   *
   * @return array<string, array<int, string>> Map of category dir => ordered slugs.
   */
  public static function widget_order(): array {
    return [
      // Content-tier categories: essential/static first, specialized/motion last.
      'text' => [
        'headline', 'section-intro', 'text-editor', 'blockquote', 'highlighted-text',
        'typed-text', 'text-reveal', 'text-scramble', 'code-terminal', 'spotlight-hero',
        'text-marquee', 'scrolling-text', 'ribbon-marquee',
        'circular-text', 'table-of-contents',
      ],
      'media' => [
        'image', 'gallery', 'bento-gallery', 'image-shapes', 'device-showcase', 'video', 'audio-player',
        'image-carousel', 'image-accordion', 'before-after', 'hotspot-image', 'image-reveal', 'image-hover-scroll',
        'image-marquee', 'image-distortion', 'image-trail', 'parallax-tilt', 'lottie',
      ],
      'elements' => [
        'button', 'slide-button', 'content-switch', 'icon', 'icon-list', 'badge-list', 'social-proof', 'divider',
        'social-share-row', 'social-profiles', 'site-search',
      ],
      'blocks' => [
        'card', 'card-extended', 'flip-box', 'cta', 'team-member',
        'testimonial', 'testimonial-carousel', 'testimonial-wall', 'client-logos', 'logo-wall',
        'accordion', 'faq-accordion', 'tabs', 'nested-tabs', 'steps', 'timeline', 'horizontal-timeline',
        'bento-grid', 'hover-reveal-list', 'feature-orbit',
      ],
      'showcase' => [
        'slider', 'immersive-slider', 'coverflow', 'card-deck', 'horizontal-showcase', 'drag-canvas',
        'scroll-stack', 'scroll-sync', 'sticky-zoom-hero', 'liquid-hero', 'scroll-sequence',
      ],
      'data' => [
        'counter', 'star-rating', 'line-progress-bar', 'radial-progress-bar', 'animated-chart',
        'pricing-table', 'price-list', 'comparison-table', 'countdown-timer', 'business-hours',
        'form', 'maps', 'modal-popup',
      ],
      // Contextual categories.
      'post' => [
        'post-title', 'featured-image', 'post-meta', 'post-taxonomy', 'post-comments', 'author-box',
        'post-navigation', 'post-grid', 'post-carousel', 'archive-results',
      ],
      'woocommerce' => [
        'product-grid', 'product-carousel', 'product-categories-carousel', 'product-single',
        'product-attributes', 'product-reviews', 'ajax-product-filter',
      ],
      'header' => [
        'site-logo', 'menu', 'vertical-megamenu', 'mobile-menu', 'menu-toggle', 'search-toggle',
        'mini-cart', 'mini-cart-toggle', 'shop-toggle', 'language-switcher', 'currency-switcher', 'custom-toggle',
      ],
      'footer' => [
        'copyright-bar',
      ],
      'site' => [
        'breadcrumbs', 'notification-bar', 'notice-box', 'scroll-to-top', 'side-icons', 'side-label', 'cursor',
      ],
      'megamenu' => [
        'category-list', 'featured-card', 'promo-banner',
      ],
    ];
  }

  /**
   * Sorts a list of widget slugs into the curated order for a category.
   *
   * Listed slugs come first, in the order declared by widget_order(); any slug
   * not listed is appended afterwards in alphabetical order, so unknown/newly
   * added widgets degrade gracefully instead of vanishing.
   *
   * @param string          $category Category directory key (e.g. 'text').
   * @param array<int, string> $slugs Widget slugs in hyphen form (file basenames).
   * @return array<int, string> The same slugs, reordered.
   */
  public static function sort_widget_slugs( string $category, array $slugs ): array {
    $order = self::widget_order()[$category] ?? [];
    $index = array_flip( $order );
    $fallback = count( $order );

    usort( $slugs, static function ( string $a, string $b ) use ( $index, $fallback ): int {
      $ia = $index[$a] ?? $fallback;
      $ib = $index[$b] ?? $fallback;

      return $ia === $ib ? strcmp( $a, $b ) : $ia - $ib;
    } );

    return $slugs;
  }
}
