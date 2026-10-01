<?php
/**
 * Free admin (the Upgrade/Welcome page) — the Freemius funnel.
 *
 * Registers a branded `themeasy` top-level menu and an Upgrade/Welcome page that
 * showcases the template library, for the Free (widgets-only) build. The full
 * settings panel is premium (Settings_Loader); to avoid registering the menu
 * twice, this loader stands down on the full offer with a Themeasy theme
 * active — the Pro admin then owns the menu. On any other theme that admin
 * never loads, so the page stays and says what needs a Themeasy theme: the
 * widgets work, the site features do not (backlog #264).
 *
 * The Widgets plan (can_use_widgets() without the full offer) keeps this page
 * as its only admin surface (backlog #262), with its own copy: the license is
 * active, the upgrade goes to the full offer, and the license is managed on
 * the Themeasy Hub.
 *
 * Ships in BOTH builds (no premium marker): the premium build keeps it inert
 * on the full offer (can_use_premium() true → Settings_Loader owns the menu);
 * the Free build has it own the whole Themeasy admin surface.
 *
 * @package Themeasy\Admin
 * @since 1.0.0
 */

namespace Themeasy\Admin;

use Themeasy\Core\Entitlement;

defined( 'ABSPATH' ) || exit;

class Upgrade_Page {
  /** Top-level menu + page slug. Matches the Freemius menu slug (themeasy). */
  private const PAGE_SLUG = 'themeasy';

  /** Hook suffix WordPress assigns to the top-level page. */
  private const SCREEN_ID = 'toplevel_page_themeasy';

  /** External Themeasy template library. */
  private const LIBRARY_URL = 'https://library.themeasy.co';

  /** Themeasy pricing page. The Free build routes upgrades here (the site), not the SDK's in-WP pricing. */
  private const PRICING_URL = 'https://themeasy.co/pricing';

  /** Themeasy Hub account, where a Widgets-plan buyer manages the license. */
  private const HUB_ACCOUNT_URL = 'https://hub.themeasy.co/account';

  /** The Themeasy themes, for a full-offer site on another theme. */
  private const THEMES_URL = 'https://themeasy.co';

  /**
   * Initialize the minimal Free admin.
   *
   * Stands down on the full offer with a Themeasy theme active — the Pro
   * Settings_Loader owns the `themeasy` menu there, so registering it here
   * would duplicate it. The Widgets plan keeps it, and so does the full offer
   * on any other theme, where the Pro admin does not load.
   *
   * @return void
   */
  public static function init(): void {
    if ( Entitlement::can_use_premium() && current_theme_supports( 'themeasy-compatible' ) ) {
      return;
    }

    add_action( 'admin_menu', [__CLASS__, 'register_menu'] );
    add_action( 'admin_enqueue_scripts', [__CLASS__, 'enqueue_assets'] );
  }

  /**
   * Register the branded top-level menu + the Upgrade page.
   *
   * @return void
   */
  public static function register_menu(): void {
    add_menu_page(
      esc_html__( 'Themeasy', 'themeasy-lite' ),
      esc_html__( 'Themeasy', 'themeasy-lite' ),
      'manage_options',
      self::PAGE_SLUG,
      [__CLASS__, 'render_page'],
      self::menu_icon(),
      59.97 // Just before Appearance (60).
    );

    // Rename the auto-generated first submenu to match the page intent.
    add_submenu_page(
      self::PAGE_SLUG,
      esc_html__( 'Upgrade Themeasy', 'themeasy-lite' ),
      esc_html__( 'Upgrade', 'themeasy-lite' ),
      'manage_options',
      self::PAGE_SLUG,
      [__CLASS__, 'render_page']
    );
  }

  /**
   * Enqueue the Upgrade page stylesheet (only on that page).
   *
   * @param string $hook_suffix Current admin screen hook suffix.
   * @return void
   */
  public static function enqueue_assets( string $hook_suffix ): void {
    if ( self::SCREEN_ID !== $hook_suffix ) {
      return;
    }

    $rel = 'admin/assets/css/admin.min.css';
    $path = TMS_PATH . $rel;

    wp_enqueue_style(
      'themeasy-admin',
      TMS_URL . $rel,
      [],
      file_exists( $path ) ? (string) filemtime( $path ) : TMS_VER
    );
  }

  /**
   * Render the Upgrade / Welcome page — the Free build's conversion hook, and
   * the Widgets plan's home (license status + upgrade to the full offer).
   *
   * @return void
   */
  public static function render_page(): void {
    if ( !current_user_can( 'manage_options' ) ) {
      return;
    }

    // Upgrades route to the Themeasy site (not the SDK's in-WP pricing), keeping
    // the buyer in the branded funnel. Filterable so the Hub can repoint it.
    $upgrade_url = (string) apply_filters( 'themeasy/upgrade_url', self::PRICING_URL );

    // The full offer reaches this page only on a theme without Themeasy support
    // (init() stands down otherwise): its license is active, and what it lacks is
    // the theme, not an upgrade. Any other entitled site is on the Widgets plan:
    // a paying buyer, not a Free user. The Free page offers both paid plans; the
    // Widgets page offers Pro.
    $is_full_offer = Entitlement::can_use_premium();
    $is_widgets_plan = !$is_full_offer && Entitlement::can_use_widgets();

    // A plugin-plan buyer manages the license in the SDK's Account page, the
    // only place that can deactivate or change it today (the Hub cannot release
    // a subscription's sites yet — backlog #262); anyone else in the Hub.
    $account_url = Entitlement::own_license_account_url();

    /**
     * Filter where a Widgets-plan buyer manages the license.
     *
     * @param string $manage_url The SDK Account page for an install that holds
     *                           its own license, else the Themeasy Hub account.
     */
    $manage_url = (string) apply_filters( 'themeasy/manage_license_url', '' !== $account_url ? $account_url : self::HUB_ACCOUNT_URL );
    $manage_is_external = 0 !== strpos( $manage_url, admin_url() );

    // Pro's site features (theme builder, global sections, settings panel) need
    // a Themeasy theme; its widgets work anywhere. Say so where it matters.
    $needs_themeasy_theme = !current_theme_supports( 'themeasy-compatible' );
    ?>
    <div class="themeasy-admin themeasy-admin--dark-mode">

      <div class="themeasy-admin__container container">
        <div class="themeasy-admin__content">

          <div class="themeasy-admin__hero">
            <div class="themeasy-admin__brand">
              <?php echo self::logo_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — trusted bundled SVG.?>
            </div>
            <?php if ( $is_full_offer ) : ?>
              <h1 class="themeasy-admin__title">
                <?php esc_html_e( 'Your Themeasy license is active', 'themeasy-lite' ); ?>
              </h1>
              <p class="themeasy-admin__lead">
                <?php esc_html_e( 'The Pro widgets and Themeasy Motion work in this theme. The theme builder, global sections, the settings panel, and the WooCommerce and Contact Form 7 integrations need a Themeasy theme: activate one to use them.', 'themeasy-lite' ); ?>
              </p>
            <?php elseif ( $is_widgets_plan ) : ?>
              <h1 class="themeasy-admin__title">
                <?php esc_html_e( 'Themeasy Widgets is active', 'themeasy-lite' ); ?>
              </h1>
              <p class="themeasy-admin__lead">
                <?php esc_html_e( 'Your license unlocks the Pro content widgets and Themeasy Motion, in any theme. Upgrade to Pro for the complete site toolkit: the theme builder, global sections, the settings panel, every widget category, WooCommerce, and the living template library.', 'themeasy-lite' ); ?>
              </p>
            <?php else : ?>
              <h1 class="themeasy-admin__title">
                <?php esc_html_e( 'Welcome to Themeasy', 'themeasy-lite' ); ?>
              </h1>
              <p class="themeasy-admin__lead">
                <?php esc_html_e( 'You are running the free Elementor content widgets. Themeasy Widgets adds the Pro content widgets and Themeasy Motion, in any theme. Themeasy Pro adds the complete site toolkit on top.', 'themeasy-lite' ); ?>
              </p>
            <?php endif; ?>
            <div class="themeasy-admin__actions">
              <?php if ( $is_full_offer ) : ?>
                <a class="themeasy-admin__button button button-primary button-hero" href="<?php echo esc_url( self::THEMES_URL ); ?>" target="_blank" rel="noopener">
                  <?php esc_html_e( 'View Themeasy Themes', 'themeasy-lite' ); ?>
                </a>
              <?php else : ?>
                <a class="themeasy-admin__button button button-primary button-hero" href="<?php echo esc_url( $upgrade_url ); ?>">
                  <?php echo esc_html( $is_widgets_plan ? __( 'Upgrade to Pro', 'themeasy-lite' ) : __( 'Compare Plans', 'themeasy-lite' ) ); ?>
                </a>
              <?php endif; ?>
              <?php if ( $is_widgets_plan ) : ?>
                <a class="themeasy-admin__button button button-outline button-hero" href="<?php echo esc_url( $manage_url ); ?>"<?php echo $manage_is_external ? ' target="_blank" rel="noopener"' : ''; ?>>
                  <?php esc_html_e( 'Manage License', 'themeasy-lite' ); ?>
                </a>
              <?php endif; ?>
              <a class="themeasy-admin__button button button-outline button-hero" href="<?php echo esc_url( self::LIBRARY_URL ); ?>" target="_blank" rel="noopener">
                <?php esc_html_e( 'Browse the Template Library', 'themeasy-lite' ); ?>
              </a>
            </div>
          </div>

          <div class="themeasy-admin__library" aria-labelledby="themeasy-admin-library-title">
            <h2 id="themeasy-admin-library-title" class="themeasy-admin__section-title">
              <?php esc_html_e( 'The Template Library', 'themeasy-lite' ); ?>
            </h2>
            <p class="themeasy-admin__section-lead">
              <?php esc_html_e( 'Import ready-made, fully editable page and section templates — copy, paste, and customize. The library keeps growing.', 'themeasy-lite' ); ?>
            </p>

            <div class="themeasy-admin__grid">
              <?php
              // Each card pairs a library thumbnail (admin/assets/img/library/,
              // shipped in the Free build) with its tier badge. The thumbnails are
              // temporary placeholders — drop in real template screenshots by
              // overwriting the slot file (any web image format; update the
              // extension here to match).
              $templates = [
                ['image' => 'template-1.svg', 'tier' => 'pro'],
                ['image' => 'template-2.svg', 'tier' => 'pro'],
                ['image' => 'template-3.svg', 'tier' => 'pro'],
                ['image' => 'template-4.svg', 'tier' => 'free'],
                ['image' => 'template-5.svg', 'tier' => 'free'],
                ['image' => 'template-6.svg', 'tier' => 'pro'],
              ];

              foreach ( $templates as $template ) :
                $is_free = 'free' === $template['tier'];
                ?>
                <div class="themeasy-admin__card themeasy-admin__card--<?php echo esc_attr( $is_free ? 'free' : 'pro' ); ?>" aria-hidden="true">
                  <img class="themeasy-admin__card-image" src="<?php echo esc_url( TMS_URL . 'admin/assets/img/library/' . $template['image'] ); ?>" alt="" loading="lazy" />
                  <span class="themeasy-admin__badge">
                    <?php echo $is_free ? esc_html__( 'Free', 'themeasy-lite' ) : esc_html__( 'Pro', 'themeasy-lite' ); ?>
                  </span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <?php if ( !$is_full_offer && !$is_widgets_plan ) : ?>
            <div class="themeasy-admin__features">
              <h2 class="themeasy-admin__section-title">
                <?php esc_html_e( 'Themeasy Widgets', 'themeasy-lite' ); ?>
              </h2>
              <p class="themeasy-admin__section-lead">
                <?php esc_html_e( 'The Pro widgets and their motion, in the theme you already use.', 'themeasy-lite' ); ?>
              </p>
              <?php
              self::render_feature_list(
                [
                  [
                    'icon' => 'feature-widgets.svg',
                    'text' => __( 'Pro content widgets — text, media, elements, blocks, sliders, and data', 'themeasy-lite' ),
                  ],
                  [
                    'icon' => 'feature-motion.svg',
                    'text' => __( 'Themeasy Motion — entrance, hover, scroll, and text animations', 'themeasy-lite' ),
                  ],
                  [
                    'icon' => 'feature-any-theme.svg',
                    'text' => __( 'Any theme — Hello Elementor, Astra, Twenty Twenty-Five, and more', 'themeasy-lite' ),
                  ],
                ]
              );
              ?>
              <a class="themeasy-admin__button button button-primary button-hero" href="<?php echo esc_url( $upgrade_url ); ?>">
                <?php esc_html_e( 'Get Themeasy Widgets', 'themeasy-lite' ); ?>
              </a>
            </div>
          <?php endif; ?>

          <?php if ( !$is_full_offer ) : ?>
            <div class="themeasy-admin__features">
              <h2 class="themeasy-admin__section-title">
                <?php echo esc_html( $is_widgets_plan ? __( 'Upgrade to Pro', 'themeasy-lite' ) : __( 'Everything in Pro', 'themeasy-lite' ) ); ?>
              </h2>
              <p class="themeasy-admin__section-lead">
                <?php echo esc_html( $is_widgets_plan ? __( 'Everything in your plan, plus the complete site toolkit:', 'themeasy-lite' ) : __( 'Everything in Themeasy Widgets, plus the complete site toolkit:', 'themeasy-lite' ) ); ?>
              </p>
              <?php
              self::render_feature_list(
                [
                  [
                    'icon' => 'feature-theme-builder.svg',
                    'text' => __( 'Theme builder — custom headers, footers, and global sections', 'themeasy-lite' ),
                  ],
                  [
                    'icon' => 'feature-widgets.svg',
                    'text' => __( 'Every widget category — header, post, site, footer, WooCommerce, mega menu', 'themeasy-lite' ),
                  ],
                  [
                    'icon' => 'feature-settings.svg',
                    'text' => __( 'Full settings panel — colors, typography, performance, custom code', 'themeasy-lite' ),
                  ],
                  [
                    'icon' => 'feature-woocommerce.svg',
                    'text' => __( 'WooCommerce enhancements — variation swatches, custom fields, and more', 'themeasy-lite' ),
                  ],
                  [
                    'icon' => 'feature-library.svg',
                    'text' => __( 'The living template library — copy, paste, and customize', 'themeasy-lite' ),
                  ],
                  [
                    'icon' => 'feature-support.svg',
                    'text' => __( 'Priority support', 'themeasy-lite' ),
                  ],
                ]
              );
              ?>
              <?php if ( $needs_themeasy_theme ) : ?>
                <p class="themeasy-admin__section-lead">
                  <?php esc_html_e( 'The theme builder, global sections, and settings panel run on a Themeasy theme. The widgets work in any theme.', 'themeasy-lite' ); ?>
                </p>
              <?php endif; ?>
              <a class="themeasy-admin__button button button-primary button-hero" href="<?php echo esc_url( $upgrade_url ); ?>">
                <?php echo esc_html( $is_widgets_plan ? __( 'Upgrade to Pro', 'themeasy-lite' ) : __( 'See Pricing', 'themeasy-lite' ) ); ?>
              </a>
            </div>
          <?php endif; ?>

        </div><!-- /.themeasy-admin__content -->
      </div><!-- /.themeasy-admin__container -->
    </div><!-- /.themeasy-admin -->

    <?php
  }

  /**
   * Print a feature list: each entry pairs an admin SVG (admin/assets/svg/,
   * shipped in the Free build) with its label. Swap an icon by overwriting the
   * slot file.
   *
   * @param array<int, array{icon: string, text: string}> $features Icon filename + label.
   * @return void
   */
  private static function render_feature_list( array $features ): void {
    ?>
    <ul class="themeasy-admin__feature-list">
      <?php foreach ( $features as $feature ) : ?>
        <li class="themeasy-admin__feature">
          <span class="themeasy-admin__feature-icon" aria-hidden="true">
            <?php echo self::read_svg( $feature['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — trusted bundled SVG.?>
          </span>
          <span class="themeasy-admin__feature-text"><?php echo esc_html( $feature['text'] ); ?></span>
        </li>
      <?php endforeach; ?>
    </ul>
    <?php
  }

  /**
   * The branded menu icon as a base64 data URI, with a dashicon fallback.
   *
   * @return string
   */
  private static function menu_icon(): string {
    $svg = self::read_svg( 'themeasy-logo-menu.svg' );

    return ( '' !== $svg )
      ? 'data:image/svg+xml;base64,' . base64_encode( $svg )
      : 'dashicons-art';
  }

  /**
   * The full Themeasy logo SVG for the hero (empty string when unreadable).
   *
   * @return string
   */
  private static function logo_svg(): string {
    return self::read_svg( 'themeasy-logo.svg' );
  }

  /**
   * Read a bundled admin SVG by filename (logos and feature icons).
   *
   * @param string $filename SVG filename inside admin/assets/svg/.
   * @return string
   */
  private static function read_svg( string $filename ): string {
    $path = TMS_PATH . 'admin/assets/svg/' . basename( sanitize_file_name( $filename ) );

    if ( !is_readable( $path ) ) {
      return '';
    }

    $svg = file_get_contents( $path );

    return false !== $svg ? $svg : '';
  }
}
