<?php
/**
 * Free admin (the Getting Started / Upgrade page) — the Freemius funnel.
 *
 * Registers a branded `themeasy` top-level menu and its page. For the Free
 * (widgets-only) build the page is a Getting Started guide first (the free
 * widgets, how to reach them in Elementor, help), with the upgrade after it
 * (backlog #267), and the Lite build tells a buyer how to swap it for the
 * Themeasy plugin (backlog #268); it also showcases the template library. The full
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
use Themeasy\Elementor\Widget_Categories;

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

  /** The Themeasy Help Center (the widgets' own help links point here too). */
  private const HELP_URL = 'https://themeasy.co/help-center';

  /** Where a plugin-plan buyer downloads the premium build (the Freemius customer portal). */
  private const CUSTOMER_PORTAL_URL = 'https://users.freemius.com';

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
   * Register the branded top-level menu + the page (Getting Started on Free,
   * Upgrade on a paid plan).
   *
   * @return void
   */
  public static function register_menu(): void {
    $is_free = !Entitlement::can_use_widgets();

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
      $is_free ? esc_html__( 'Getting Started', 'themeasy-lite' ) : esc_html__( 'Upgrade Themeasy', 'themeasy-lite' ),
      $is_free ? esc_html__( 'Getting Started', 'themeasy-lite' ) : esc_html__( 'Upgrade', 'themeasy-lite' ),
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
   * Render the page — the Free build's Getting Started guide and conversion
   * hook, and the Widgets plan's home (license status + upgrade to the full
   * offer).
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

    // Free: the page opens on how to use the free widgets; the upgrade follows.
    $is_free = !$is_full_offer && !$is_widgets_plan;
    ?>
    <div class="themeasy-admin themeasy-admin--dark-mode">

      <div class="themeasy-admin__container container">
        <div class="themeasy-admin__content">

          <div class="themeasy-admin__hero">
            <div class="themeasy-admin__brand">
              <?php echo self::logo_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted bundled SVG.?>
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
                <?php esc_html_e( 'Free Elementor widgets that work in any theme, with no license to activate. Here is how to start building with them.', 'themeasy-lite' ); ?>
              </p>
            <?php endif; ?>
            <div class="themeasy-admin__actions">
              <?php if ( $is_full_offer ) : ?>
                <a class="themeasy-admin__button button button-primary button-hero" href="<?php echo esc_url( self::THEMES_URL ); ?>" target="_blank" rel="noopener">
                  <?php esc_html_e( 'View Themeasy Themes', 'themeasy-lite' ); ?>
                </a>
              <?php elseif ( $is_free ) : ?>
                <a class="themeasy-admin__button button button-primary button-hero" href="<?php echo esc_url( self::new_page_url() ); ?>">
                  <?php esc_html_e( 'Create a Page', 'themeasy-lite' ); ?>
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

          <?php if ( $is_free ) : ?>
            <?php self::render_getting_started(); ?>
            <?php if ( Entitlement::is_lite_build() ) : ?>
              <?php self::render_already_purchased(); ?>
            <?php endif; ?>
          <?php endif; ?>

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
   * Print the Free build's Getting Started section: three steps from a blank
   * page to a finished section, the free widgets by name, and where to get help.
   *
   * @return void
   */
  private static function render_getting_started(): void {
    $titles = self::free_widget_titles();
    $category = class_exists( Widget_Categories::class, false )
      ? Widget_Categories::free_editor_label()
      : __( 'Themeasy', 'themeasy-lite' );
    ?>
    <div class="themeasy-admin__start" aria-labelledby="themeasy-admin-start-title">
      <h2 id="themeasy-admin-start-title" class="themeasy-admin__section-title">
        <?php esc_html_e( 'Getting Started', 'themeasy-lite' ); ?>
      </h2>

      <ol class="themeasy-admin__feature-list">
        <li class="themeasy-admin__feature">
          <span class="themeasy-admin__feature-icon themeasy-admin__step" aria-hidden="true">1</span>
          <span class="themeasy-admin__feature-text">
            <?php
            printf(
              /* translators: %s: link to create a new page with Elementor. */
              esc_html__( 'Open a page with Elementor: %s, or choose "Edit with Elementor" on a page you already have.', 'themeasy-lite' ),
              '<a href="' . esc_url( self::new_page_url() ) . '">' . esc_html__( 'create a new one', 'themeasy-lite' ) . '</a>'
            );
            ?>
          </span>
        </li>
        <li class="themeasy-admin__feature">
          <span class="themeasy-admin__feature-icon themeasy-admin__step" aria-hidden="true">2</span>
          <span class="themeasy-admin__feature-text">
            <?php
            printf(
              /* translators: %s: the Elementor panel category that holds the free widgets. */
              esc_html__( 'In the Elements panel, open the %s category, or search for a widget by name, and drag it onto the page.', 'themeasy-lite' ),
              '<strong>' . esc_html( $category ) . '</strong>'
            );
            ?>
          </span>
        </li>
        <li class="themeasy-admin__feature">
          <span class="themeasy-admin__feature-icon themeasy-admin__step" aria-hidden="true">3</span>
          <span class="themeasy-admin__feature-text">
            <?php esc_html_e( 'For a ready-made section, click the Themeasy button in the editor\'s "Drag widget here" area. The Themeasy Library opens, and its Free templates insert in one click.', 'themeasy-lite' ); ?>
          </span>
        </li>
      </ol>

      <?php if ( !empty( $titles ) ) : ?>
        <h3 class="themeasy-admin__subsection-title">
          <?php
          printf(
            /* translators: %s: number of free widgets. */
            esc_html( _n( 'The %s free widget', 'The %s free widgets', count( $titles ), 'themeasy-lite' ) ),
            esc_html( number_format_i18n( count( $titles ) ) )
          );
          ?>
        </h3>
        <ul class="themeasy-admin__widget-list">
          <?php foreach ( $titles as $title ) : ?>
            <li class="themeasy-admin__widget"><?php echo esc_html( $title ); ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <p class="themeasy-admin__section-lead">
        <?php
        printf(
          /* translators: %s: link to the Themeasy Help Center. */
          esc_html__( 'Need a hand? Every widget has a guide in the %s.', 'themeasy-lite' ),
          '<a href="' . esc_url( self::HELP_URL ) . '" target="_blank" rel="noopener">' . esc_html__( 'Themeasy Help Center', 'themeasy-lite' ) . '</a>'
        );
        ?>
      </p>
    </div>
    <?php
  }

  /**
   * Print the Lite's "Already purchased?" steps (backlog #268). The Lite cannot
   * take a license key: a buyer downloads the Themeasy plugin, which deactivates
   * the Lite when it is activated and takes the key.
   *
   * @return void
   */
  private static function render_already_purchased(): void {
    ?>
    <div class="themeasy-admin__start" aria-labelledby="themeasy-admin-purchased-title">
      <h2 id="themeasy-admin-purchased-title" class="themeasy-admin__section-title">
        <?php esc_html_e( 'Already purchased?', 'themeasy-lite' ); ?>
      </h2>
      <p class="themeasy-admin__section-lead">
        <?php esc_html_e( 'Your plan comes with the Themeasy plugin, a separate download that takes the place of Themeasy Lite.', 'themeasy-lite' ); ?>
      </p>

      <ol class="themeasy-admin__feature-list">
        <li class="themeasy-admin__feature">
          <span class="themeasy-admin__feature-icon themeasy-admin__step" aria-hidden="true">1</span>
          <span class="themeasy-admin__feature-text">
            <?php
            printf(
              /* translators: %s: link to the customer portal. */
              esc_html__( 'Download Themeasy from the link in your purchase email, or from the %s.', 'themeasy-lite' ),
              '<a href="' . esc_url( self::CUSTOMER_PORTAL_URL ) . '" target="_blank" rel="noopener">' . esc_html__( 'customer portal', 'themeasy-lite' ) . '</a>'
            );
            ?>
          </span>
        </li>
        <li class="themeasy-admin__feature">
          <span class="themeasy-admin__feature-icon themeasy-admin__step" aria-hidden="true">2</span>
          <span class="themeasy-admin__feature-text">
            <?php
            printf(
              /* translators: %s: link to the Upload Plugin screen. */
              esc_html__( 'Open %s in Plugins and install the zip file.', 'themeasy-lite' ),
              '<a href="' . esc_url( admin_url( 'plugin-install.php?tab=upload' ) ) . '">' . esc_html__( 'Upload Plugin', 'themeasy-lite' ) . '</a>'
            );
            ?>
          </span>
        </li>
        <li class="themeasy-admin__feature">
          <span class="themeasy-admin__feature-icon themeasy-admin__step" aria-hidden="true">3</span>
          <span class="themeasy-admin__feature-text">
            <?php esc_html_e( 'Activate Themeasy: it deactivates Themeasy Lite for you. Then click Activate License on its row in Plugins and enter your license key.', 'themeasy-lite' ); ?>
          </span>
        </li>
      </ol>
    </div>
    <?php
  }

  /**
   * The titles of the free widgets, in the order the editor panel lists them.
   *
   * Read from Elementor's widget registry and filtered to the Free category, so
   * the page names exactly what the editor offers, never a second copy of the
   * tier list (Widget_Tiers). Empty when Elementor is not running.
   *
   * @return string[]
   */
  private static function free_widget_titles(): array {
    if ( !did_action( 'elementor/loaded' ) || !class_exists( Widget_Categories::class, false ) ) {
      return [];
    }

    $titles = [];

    foreach ( \Elementor\Plugin::$instance->widgets_manager->get_widget_types() as $widget ) {
      if ( in_array( Widget_Categories::FREE_CATEGORY_SLUG, $widget->get_categories(), true ) ) {
        $titles[] = $widget->get_title();
      }
    }

    return $titles;
  }

  /**
   * Where "Create a Page" goes: Elementor's own new-page action (it opens the
   * editor on a fresh draft), else the core new-page screen.
   *
   * @return string
   */
  private static function new_page_url(): string {
    if ( class_exists( '\Elementor\Core\Documents_Manager' ) ) {
      return \Elementor\Core\Documents_Manager::get_create_new_post_url( 'page' );
    }

    return admin_url( 'post-new.php?post_type=page' );
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
            <?php echo self::read_svg( $feature['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted bundled SVG.?>
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
