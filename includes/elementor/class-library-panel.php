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
 * The panel inserts the `free` templates and shows the `pro` ones locked, with
 * a link to the Pro checkout, as Elementor does with its own library.
 *
 * @package Themeasy\Elementor
 * @since 1.0.0
 */

namespace Themeasy\Elementor;

use Themeasy\Admin\Welcome_Page;

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
      THEMEASY_E_ASSETS_URL . 'css/themeasy-el-library.min.css',
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
      THEMEASY_E_ASSETS_URL . 'js/themeasy-el-library.min.js',
      [],
      $this->asset_ver( 'js/themeasy-el-library.min.js' ),
      true
    );

    // Inline JSON rather than wp_localize_script: localize stringifies every
    // scalar (true -> "1", false -> ""), which would make a boolean flag depend
    // on JS truthiness quirks. wp_json_encode keeps real booleans/ints.
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
   * filemtime keeps same-version edits from serving stale: THEMEASY_VER stays pinned
   * across a release, so it would mask edits made during development. Falls back
   * to the version constant if the file is ever missing.
   *
   * @param string $rel Path relative to includes/elementor/assets/.
   * @return string
   */
  private function asset_ver( string $rel ): string {
    $path = THEMEASY_PATH . 'includes/elementor/assets/' . $rel;

    return file_exists( $path ) ? (string) filemtime( $path ) : themeasy_get_asset_version();
  }

  /**
   * Build the bootstrap payload the modal reads on init.
   *
   * @return array<string,mixed>
   */
  private function bootstrap_data(): array {
    /**
     * Filter the public template catalog base URL (library.themeasy.co).
     *
     * Card "Preview" links point to the catalog's template pages
     * (/templates/{category}/{slug}/), never to the private cms backstage.
     *
     * @param string $catalog_url Catalog base URL.
     */
    $catalog_url = (string) apply_filters( 'themeasy/library_catalog_url', 'https://library.themeasy.co' );

    $data = [
      'ajaxUrl' => admin_url( 'admin-ajax.php' ),
      'nonce' => wp_create_nonce( Library_Ajax::NONCE ),
      'perPage' => self::PER_PAGE,
      // A Themeasy theme lays out full-page templates itself; on any other theme
      // the insert may warn about the Page Layout (backlog #292).
      'themeasyTheme' => current_theme_supports( 'themeasy-compatible' ),
      // Where the button of a `pro` card goes: the card shows locked.
      'upgradeUrl' => esc_url_raw( Welcome_Page::checkout_url( 'pro' ) ),
      'catalogUrl' => esc_url_raw( $catalog_url ),
      'i18n' => $this->strings(),
    ];

    return $data;
  }

  /**
   * Translatable UI strings handed to the modal.
   *
   * @return array<string,string>
   */
  private function strings(): array {
    // The modal writes these as text (textContent, setAttribute), so the name
    // is not HTML-escaped: esc_html() would print "&amp;" in "Smith & Co".
    $brand = 'Themeasy';

    $library = sprintf(
      /* translators: %s: the brand name (Themeasy by default). */
      __( '%s Library', 'themeasy-lite' ),
      $brand
    );

    $left_out_reason = esc_html__( 'They use widgets this site does not have.', 'themeasy-lite' );

    return [
      'launch' => $library,
      'title' => $library,
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
      'leftOutOne' => esc_html__( 'Template inserted, but 1 element was left out.', 'themeasy-lite' ),
      /* translators: %1$s: number of elements left out of the inserted template. */
      'leftOutMany' => esc_html__( 'Template inserted, but %1$s elements were left out.', 'themeasy-lite' ),
      // Why an insert left widgets out (backlog #291): the editor only has the
      // widgets this plugin registers.
      'leftOutReason' => $left_out_reason,
      'fullWidthHint' => esc_html__( 'For full width, set Page Layout → Elementor Full Width.', 'themeasy-lite' ),
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
