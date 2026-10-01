<?php
/**
 * Themeasy Library — Elementor editor panel.
 *
 * Surfaces the Themeasy Template Library inside the Elementor editor: a launcher
 * button opens a modal that browses the backstage catalog (cms.themeasy.co) and
 * inserts a chosen template into the open document with one click. This class
 * only wires the editor surface (asset enqueue + the localized bootstrap data);
 * the catalog traffic goes through Library_Ajax (same-origin proxy) and the
 * insertion runs client-side via the Elementor command API.
 *
 * Ships in BOTH builds (no premium marker): it is a Free funnel surface that
 * inserts `free` templates and locks `pro` behind an upgrade CTA. The Pro gate is
 * a runtime check (Entitlement::is_subscriber()) mirrored server-side by the
 * backstage 403 — only a real SaaS subscriber carries a Freemius license proof.
 *
 * @package Themeasy\Elementor
 * @since 1.0.0
 */

namespace Themeasy\Elementor;

use Themeasy\Core\Entitlement;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the Themeasy Library launcher + modal in the Elementor editor.
 */
class Library_Panel {
  /**
   * Default templates fetched per page (matches the frozen contract default).
   *
   * @var int
   */
  private const PER_PAGE = 24;

  /**
   * Singleton instance.
   *
   * @var self|null
   */
  private static ?self $instance = null;

  /**
   * Returns the singleton instance.
   *
   * @return self
   */
  public static function instance(): self {
    if ( null === self::$instance ) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  /**
   * Register the editor enqueue hooks.
   *
   * @return void
   */
  public function init(): void {
    add_action( 'elementor/editor/after_enqueue_styles', [$this, 'enqueue_styles'] );
    add_action( 'elementor/editor/after_enqueue_scripts', [$this, 'enqueue_scripts'] );
  }

  /**
   * Enqueue the library modal stylesheet (editor only).
   *
   * @return void
   */
  public function enqueue_styles(): void {
    wp_enqueue_style(
      'themeasy-library',
      TMS_E_ASSETS_URL . 'css/themeasy-el-library.min.css',
      [],
      $this->asset_ver( 'css/themeasy-el-library.min.css' )
    );
  }

  /**
   * Enqueue and localize the library modal script (editor only).
   *
   * @return void
   */
  public function enqueue_scripts(): void {
    wp_enqueue_script(
      'themeasy-library',
      TMS_E_ASSETS_URL . 'js/themeasy-el-library.min.js',
      [],
      $this->asset_ver( 'js/themeasy-el-library.min.js' ),
      true
    );

    // Inline JSON rather than wp_localize_script: localize stringifies every
    // scalar (true -> "1", false -> ""), which would make the canInsertPro gate
    // depend on JS truthiness quirks. wp_json_encode keeps real booleans/ints.
    // Assigned to window.* (not `var`) so it stays a cross-<script>-tag global the
    // module reads, while honoring the no-var house rule.
    wp_add_inline_script(
      'themeasy-library',
      'window.themeasyLibrary = ' . wp_json_encode( $this->bootstrap_data(), JSON_HEX_TAG ) . ';',
      'before'
    );
  }

  /**
   * Resolve an editor-asset cache-busting version from filemtime.
   *
   * filemtime keeps same-version edits from serving stale: TMS_VER stays pinned
   * across a release, so it would mask edits made during development. Falls back
   * to the version constant if the file is ever missing.
   *
   * @param string $rel Path relative to includes/elementor/assets/.
   * @return string
   */
  private function asset_ver( string $rel ): string {
    $path = TMS_PATH . 'includes/elementor/assets/' . $rel;

    return file_exists( $path ) ? (string) filemtime( $path ) : themeasy_get_asset_version();
  }

  /**
   * Build the bootstrap payload the modal reads on init.
   *
   * @return array<string,mixed>
   */
  private function bootstrap_data(): array {
    // Subscriber-only unlock: the living library is exclusive to the SaaS plan
    // (anti-cannibalization), and only a SaaS subscriber holds the Freemius
    // license proof the backstage /content gate requires — so the UI lock state
    // mirrors what the server will actually allow.
    $can_insert_pro = Entitlement::is_subscriber();

    /** This filter is documented in admin/class-upgrade-page.php. */
    $upgrade_url = (string) apply_filters( 'themeasy/upgrade_url', 'https://themeasy.co/pricing' );

    /**
     * Filter the public template catalog base URL (library.themeasy.co).
     *
     * Card "Preview" links point to the catalog's template pages
     * (/templates/{category}/{slug}/), never to the private cms backstage.
     *
     * @param string $catalog_url Catalog base URL.
     */
    $catalog_url = (string) apply_filters( 'themeasy/library_catalog_url', 'https://library.themeasy.co' );

    return [
      'ajaxUrl' => admin_url( 'admin-ajax.php' ),
      'nonce' => wp_create_nonce( Library_Ajax::NONCE ),
      'perPage' => self::PER_PAGE,
      'canInsertPro' => $can_insert_pro,
      'plan' => Entitlement::plan(),
      'upgradeUrl' => esc_url_raw( $upgrade_url ),
      'catalogUrl' => esc_url_raw( $catalog_url ),
      'i18n' => $this->strings(),
    ];
  }

  /**
   * Translatable UI strings handed to the modal.
   *
   * @return array<string,string>
   */
  private function strings(): array {
    return [
      'launch' => esc_html__( 'Themeasy Library', 'themeasy-lite' ),
      'title' => esc_html__( 'Themeasy Library', 'themeasy-lite' ),
      'searchPlaceholder' => esc_html__( 'Search templates...', 'themeasy-lite' ),
      'allCategories' => esc_html__( 'All categories', 'themeasy-lite' ),
      /* translators: %1$s: group tab name (e.g. Pages, Blocks). */
      'allIn' => esc_html__( 'All %1$s', 'themeasy-lite' ),
      'allTags' => esc_html__( 'All tags', 'themeasy-lite' ),
      'close' => esc_html__( 'Close', 'themeasy-lite' ),
      'preview' => esc_html__( 'Preview', 'themeasy-lite' ),
      'insert' => esc_html__( 'Insert', 'themeasy-lite' ),
      'inserting' => esc_html__( 'Inserting...', 'themeasy-lite' ),
      'inserted' => esc_html__( 'Template inserted.', 'themeasy-lite' ),
      'proBadge' => esc_html__( 'Pro', 'themeasy-lite' ),
      'freeBadge' => esc_html__( 'Free', 'themeasy-lite' ),
      'themeBadge' => esc_html__( 'Theme', 'themeasy-lite' ),
      'locked' => esc_html__( 'Pro template', 'themeasy-lite' ),
      // Short label for the card action button (the row is narrow); the full
      // wording stays on the hover lock overlay + the button's title tooltip.
      'upgrade' => esc_html__( 'Upgrade', 'themeasy-lite' ),
      'upgradeToInsert' => esc_html__( 'Upgrade to insert', 'themeasy-lite' ),
      'loading' => esc_html__( 'Loading templates...', 'themeasy-lite' ),
      'empty' => esc_html__( 'No templates match your filters.', 'themeasy-lite' ),
      'error' => esc_html__( 'Something went wrong. Please try again.', 'themeasy-lite' ),
      'retry' => esc_html__( 'Retry', 'themeasy-lite' ),
      'prev' => esc_html__( 'Previous', 'themeasy-lite' ),
      'next' => esc_html__( 'Next', 'themeasy-lite' ),
      /* translators: 1: current page number, 2: total page count. */
      'pageOf' => esc_html__( 'Page %1$s of %2$s', 'themeasy-lite' ),
      'results' => esc_html__( 'templates', 'themeasy-lite' ),
    ];
  }
}
