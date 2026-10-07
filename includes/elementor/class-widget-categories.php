<?php
/**
 * Widget Categories registry.
 *
 * Single source of truth for the Themeasy Elementor widget category and for
 * the order of the widgets inside it. Used by Elementor_Loader to register the
 * category and to emit the widgets in a curated order.
 *
 * The widgets live in one directory per theme (text, media, elements, blocks,
 * data) and all show in a single "Themeasy — Essentials" category
 * (FREE_CATEGORY_SLUG). A widget declares [<thematic slug>, FREE_CATEGORY_SLUG],
 * and Elementor ignores a declared category nobody registered.
 *
 * @package Themeasy\Elementor
 * @since 1.0.0
 */

namespace Themeasy\Elementor;

defined( 'ABSPATH' ) || exit;

class Widget_Categories {
  /**
   * The single editor category ("Essentials").
   *
   * Every widget in includes/elementor/widgets/ declares it as its second
   * category. Keeps the historical slug so nothing user-facing depends on a
   * rename.
   *
   * @var string
   */
  public const FREE_CATEGORY_SLUG = 'themeasy_content_category';

  /**
   * Editor label of the Essentials category.
   *
   * @return string
   */
  public static function free_editor_label(): string {
    /* translators: %s: the brand name (Themeasy by default). */
    return sprintf( __( '%s — Essentials', 'themeasy-lite' ), self::brand_name() );
  }

  /**
   * The brand the editor labels carry. Plain text: Elementor prints a category
   * title as HTML.
   *
   * @return string
   */
  private static function brand_name(): string {
    $brand = 'Themeasy';

    return $brand;
  }

  /**
   * Curated display order of the individual widgets, per directory.
   *
   * Single source of truth for the widget order in the Elementor editor panel
   * (Elementor_Loader::register_widgets). Slugs are the file basenames (hyphen
   * form, e.g. `icon-list`). The keys, in this order, are also the order the
   * directories register in.
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
    $order = self::free_widget_order();

    return $order;
  }

  /**
   * The order of the widgets in includes/elementor/widgets/.
   *
   * @return array<string, array<int, string>> Map of category dir => ordered slugs.
   */
  public static function free_widget_order(): array {
    return [
      'text' => ['section-intro', 'blockquote'],
      'media' => ['image', 'video'],
      'elements' => ['button', 'icon', 'icon-list', 'divider', 'social-profiles'],
      'blocks' => ['card', 'cta', 'team-member', 'testimonial', 'logo-wall', 'accordion', 'steps'],
      'data' => ['pricing-table', 'business-hours'],
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
