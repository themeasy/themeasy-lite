<?php
/**
 * Widget tier map — single source of truth for Free vs Pro widgets.
 *
 * Free ships the curated "build a simple site" subset of the content-tier
 * categories (strategic curation 2026-07-16, revised 2026-09-29 for backlog
 * #253; dependency-clean is an entry requirement, not the criterion).
 * Content-tier = the six thematic dirs the former content/ split into: text,
 * media, elements, blocks, showcase, data. Every other content-tier widget and
 * every contextual category is Pro: physically stripped from the Free build
 * (the widgets__premium_only tree) and runtime-gated in the premium build —
 * the Widgets plan by Entitlement::can_use_widgets(), everything else by the
 * full offer, Entitlement::can_use_premium().
 *
 * The folder is the mechanism: Elementor_Loader::register_widgets() picks the
 * tier from the tree a widget lives in. This class is the audited policy the
 * drift tests hold the folders to — every widget in the Free tree must be
 * listed here, and no widget in the Pro tree may be.
 *
 * It also holds the Widgets plan (backlog #262): the Pro widgets a
 * can_use_widgets()-only site registers. That one IS read at runtime, by
 * register_widgets() and register_widget_categories().
 *
 * Ships in BOTH builds (no premium marker).
 *
 * @package Themeasy\Elementor
 * @since 1.0.0
 */

namespace Themeasy\Elementor;

defined( 'ABSPATH' ) || exit;

final class Widget_Tiers {
  /**
   * The content-tier category directories (the former content/ split).
   *
   * Only widgets under these dirs are Free-eligible; contextual categories
   * (header, footer, post, site, megamenu, woocommerce) are always Pro.
   *
   * @var string[]
   */
  private const CONTENT_TIER_DIRS = ['text', 'media', 'elements', 'blocks', 'showcase', 'data'];

  /**
   * Free content-tier widgets (file slugs, no extension).
   *
   * The curated basics needed to build a simple site (hero, about, services,
   * pricing, team, social proof, FAQ, contact) — the Free tier of the product
   * line, powering the Free themes. They sit beside Elementor's own free
   * widgets (Heading, Text Editor, Image Gallery, Counter…), so a basic that
   * Elementor already covers is not duplicated here. The one deliberate
   * exception is Image: it stays for what Elementor's Image lacks, the
   * normal/hover overlay layer and the caption backdrop blur (backlog #258).
   *
   * No Free widget may need GSAP (its license is not GPL-compatible, and the
   * Free build ships to wordpress.org — backlog #253): Themeasy Motion is Pro,
   * and a widget whose main function needs GSAP stays Pro. Motion/wow widgets,
   * carousels (Swiper), conversion widgets, and everything dynamic stay Pro;
   * the only vendor left on the Free surface is GLightbox (image/video
   * lightbox). Each entry must still resolve entirely inside the Free runtime
   * (helpers + asset registrar + settings-read + vendors) with no premium
   * coupling. Anything not listed here is Pro.
   *
   * @var string[]
   */
  private const FREE_CONTENT = [
    'accordion',
    'blockquote',
    'business-hours',
    'button',
    'card',
    'cta',
    'divider',
    'icon',
    'icon-list',
    'image',
    'logo-wall',
    'pricing-table',
    'section-intro',
    'social-profiles',
    'steps',
    'team-member',
    'testimonial',
    'video',
  ];

  /**
   * Content-tier Pro widgets left out of the Widgets plan (file slugs).
   *
   * The plan is the content categories (owner decision, 2026-09-30), minus the
   * widgets whose runtime is a site feature:
   *  - form: its CF7 runtime rewrites every CF7 form on the site and switches
   *    autop off, so it loads only with the full offer on a Themeasy theme. It
   *    comes back once a widget-scoped CF7 runtime exists.
   *
   * @var string[]
   */
  private const WIDGETS_PLAN_EXCLUDED = ['form'];

  /**
   * Whether a widget ships in the Free build.
   *
   * Free iff it lives in a content-tier directory AND is in the audited Free
   * allowlist. Every contextual category (header, post, site, footer,
   * woocommerce, megamenu) is Pro.
   *
   * @param string $category Widget category directory (e.g. 'text', 'header').
   * @param string $slug     Widget file slug (e.g. 'button').
   * @return bool
   */
  public static function is_free( string $category, string $slug ): bool {
    return in_array( $category, self::CONTENT_TIER_DIRS, true ) && in_array( $slug, self::FREE_CONTENT, true );
  }

  /**
   * The Free content allowlist (file slugs).
   *
   * Exposed for the drift test and tooling.
   *
   * @return string[]
   */
  public static function free_content(): array {
    return self::FREE_CONTENT;
  }

  /**
   * Whether a widget belongs to the Widgets plan.
   *
   * Any widget of a content-tier directory, Free or Pro, except the excluded
   * ones: a new content widget joins the plan by itself. Contextual categories
   * never do; they stay with the full offer.
   *
   * @param string $category Widget category directory (e.g. 'text', 'header').
   * @param string $slug     Widget file slug (e.g. 'button').
   * @return bool
   */
  public static function in_widgets_plan( string $category, string $slug ): bool {
    return in_array( $category, self::CONTENT_TIER_DIRS, true )
      && !in_array( $slug, self::WIDGETS_PLAN_EXCLUDED, true );
  }

  /**
   * The content-tier category directories.
   *
   * @return string[]
   */
  public static function content_dirs(): array {
    return self::CONTENT_TIER_DIRS;
  }

  /**
   * The content-tier widgets left out of the Widgets plan (file slugs).
   *
   * Exposed for the drift test.
   *
   * @return string[]
   */
  public static function widgets_plan_excluded(): array {
    return self::WIDGETS_PLAN_EXCLUDED;
  }
}
