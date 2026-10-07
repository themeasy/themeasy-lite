<?php
/**
 * Welcome page: the Themeasy menu of the Free build.
 *
 * Registers the branded `themeasy` top-level menu and its page: a Getting
 * Started guide first (the free widgets, how to reach them in Elementor,
 * help), how a buyer swaps this plugin for the Themeasy plugin (backlog #268),
 * a look at the template library, and what each paid plan adds, with a plain
 * link to its checkout (launch runbook S12, decisions O2-b and C4-b).
 *
 * The page reads no license, plan or trial: it is the same for every site
 * (backlog #362).
 *
 * @package Themeasy\Admin
 * @since 1.0.0
 */

namespace Themeasy\Admin;

use Themeasy\Elementor\Widget_Categories;

defined( 'ABSPATH' ) || exit;

class Welcome_Page {
  /** Top-level menu + page slug. Matches the Freemius menu slug (themeasy). */
  protected const PAGE_SLUG = 'themeasy';

  /** Hook suffix WordPress assigns to the top-level page. */
  protected const SCREEN_ID = 'toplevel_page_themeasy';

  /** External Themeasy template library. */
  protected const LIBRARY_URL = 'https://library.themeasy.co';

  /**
   * The Freemius checkout of each plan, as the pricing page sells them: Widgets
   * is the plugin alone (product 31006), Pro and Agency the bundle (31981, the
   * plugin plus every Themeasy theme).
   */
  protected const CHECKOUT_URLS = [
    'widgets' => 'https://checkout.freemius.com/plugin/31006/plan/70021/',
    'pro' => 'https://checkout.freemius.com/bundle/31981/plan/52465/',
    'agency' => 'https://checkout.freemius.com/bundle/31981/plan/52629/',
  ];

  /**
   * The plugin's product in the Themeasy Hub, where a buyer downloads the
   * Themeasy plugin.
   */
  protected const HUB_PRODUCT_URL = 'https://hub.themeasy.co/account/products/themeasy';

  /** The Themeasy Help Center (the widgets' own help links point here too). */
  protected const HELP_URL = 'https://themeasy.co/help-center';

  /**
   * Widgets per paid plan, as the Themeasy plugin registers them: the Widgets
   * plan, and Pro with a Themeasy theme and on any other theme, where the
   * WooCommerce widgets and the two cart widgets stay out (they need the theme's
   * WooCommerce runtime). The copy names them, and this build has no Pro tree to
   * count, so they are numbers here: tests/widgets-plan-drift.php recounts them
   * from the widget trees.
   */
  protected const WIDGETS_PLAN_COUNT = 87;

  protected const FULL_OFFER_COUNT = 128;

  protected const FULL_OFFER_ANY_THEME_COUNT = 119;

  /**
   * Register the menu and the page.
   *
   * @return void
   */
  public static function init(): void {
    add_action( 'admin_menu', [__CLASS__, 'register_menu'] );
    add_action( 'admin_enqueue_scripts', [__CLASS__, 'enqueue_assets'] );
  }

  /**
   * Where a plan's buy button goes: the plan's checkout, where the Widgets plan
   * starts its 3-day trial, as on the pricing page (runbook S17).
   *
   * @param string $plan One of 'widgets', 'pro', 'agency'.
   * @return string
   */
  public static function checkout_url( string $plan ): string {
    $checkout = self::CHECKOUT_URLS[$plan] ?? self::CHECKOUT_URLS['pro'];

    return 'widgets' === $plan ? add_query_arg( 'trial', 'paid', $checkout ) : $checkout;
  }

  /**
   * Register the branded top-level menu + the Getting Started page.
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
      esc_html__( 'Getting Started', 'themeasy-lite' ),
      esc_html__( 'Getting Started', 'themeasy-lite' ),
      'manage_options',
      self::PAGE_SLUG,
      [__CLASS__, 'render_page']
    );
  }

  /**
   * Enqueue the page stylesheet (only on that page).
   *
   * @param string $hook_suffix Current admin screen hook suffix.
   * @return void
   */
  public static function enqueue_assets( string $hook_suffix ): void {
    if ( self::SCREEN_ID !== $hook_suffix ) {
      return;
    }

    $rel = 'admin/assets/css/admin.min.css';
    $path = THEMEASY_PATH . $rel;

    wp_enqueue_style(
      'themeasy-admin',
      THEMEASY_URL . $rel,
      [],
      file_exists( $path ) ? (string) filemtime( $path ) : THEMEASY_VER
    );
  }

  /**
   * Render the page: the Getting Started guide, then what the paid plans add.
   *
   * @return void
   */
  public static function render_page(): void {
    if ( !current_user_can( 'manage_options' ) ) {
      return;
    }
    ?>
    <div class="themeasy-admin themeasy-admin--dark-mode">

      <div class="themeasy-admin__container container">
        <div class="themeasy-admin__content">

          <div class="themeasy-admin__hero">
            <div class="themeasy-admin__brand">
              <?php echo self::logo_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted bundled SVG.?>
            </div>
            <h1 class="themeasy-admin__title">
              <?php esc_html_e( 'Welcome to Themeasy', 'themeasy-lite' ); ?>
            </h1>
            <p class="themeasy-admin__lead">
              <?php esc_html_e( 'Free Elementor widgets that work in any theme, with no license to activate. Here is how to start building with them.', 'themeasy-lite' ); ?>
            </p>
            <div class="themeasy-admin__actions">
              <a class="themeasy-admin__button button button-primary button-hero" href="<?php echo esc_url( self::new_page_url() ); ?>">
                <?php esc_html_e( 'Create a Page', 'themeasy-lite' ); ?>
              </a>
              <a class="themeasy-admin__button button button-outline button-hero" href="<?php echo esc_url( self::LIBRARY_URL ); ?>" target="_blank" rel="noopener">
                <?php esc_html_e( 'Browse the Template Library', 'themeasy-lite' ); ?>
              </a>
            </div>
          </div>

          <?php self::render_getting_started(); ?>
          <?php self::render_already_purchased(); ?>
          <?php self::render_library(); ?>

          <div class="themeasy-admin__features">
            <h2 class="themeasy-admin__section-title">
              <?php esc_html_e( 'Themeasy Widgets', 'themeasy-lite' ); ?>
            </h2>
            <p class="themeasy-admin__section-lead">
              <?php esc_html_e( 'The Pro widgets and their motion, in the theme you already use.', 'themeasy-lite' ); ?>
            </p>
            <?php self::render_feature_list( self::widgets_plan_features() ); ?>
            <p class="themeasy-admin__section-lead">
              <?php esc_html_e( 'Card required. Billed yearly after 3 days unless you cancel.', 'themeasy-lite' ); ?>
            </p>
            <a class="themeasy-admin__button button button-primary button-hero" href="<?php echo esc_url( self::checkout_url( 'widgets' ) ); ?>">
              <?php esc_html_e( 'Start 3-day trial', 'themeasy-lite' ); ?>
            </a>
          </div>

          <div class="themeasy-admin__features">
            <h2 class="themeasy-admin__section-title">
              <?php esc_html_e( 'Everything in Pro', 'themeasy-lite' ); ?>
            </h2>
            <p class="themeasy-admin__section-lead">
              <?php esc_html_e( 'Everything in Themeasy Widgets, plus the complete site toolkit:', 'themeasy-lite' ); ?>
            </p>
            <?php self::render_feature_list( self::pro_features() ); ?>
            <?php if ( !current_theme_supports( 'themeasy-compatible' ) ) : ?>
              <p class="themeasy-admin__section-lead">
                <?php esc_html_e( 'The theme builder, global sections, container controls, settings panel, and WooCommerce features run on a Themeasy theme, and every Themeasy theme comes with Pro. The other widgets work in any theme.', 'themeasy-lite' ); ?>
              </p>
            <?php endif; ?>
            <a class="themeasy-admin__button button button-primary button-hero" href="<?php echo esc_url( self::checkout_url( 'pro' ) ); ?>">
              <?php esc_html_e( 'Get Themeasy Pro', 'themeasy-lite' ); ?>
            </a>
          </div>

          <div class="themeasy-admin__features">
            <h2 class="themeasy-admin__section-title">
              <?php esc_html_e( 'Themeasy Agency', 'themeasy-lite' ); ?>
            </h2>
            <p class="themeasy-admin__section-lead">
              <?php esc_html_e( 'Everything in Pro, plus White Label: your brand in place of Themeasy\'s, for the sites you build for clients.', 'themeasy-lite' ); ?>
            </p>
            <?php self::render_feature_list( self::agency_features() ); ?>
            <a class="themeasy-admin__button button button-primary button-hero" href="<?php echo esc_url( self::checkout_url( 'agency' ) ); ?>">
              <?php esc_html_e( 'Get Themeasy Agency', 'themeasy-lite' ); ?>
            </a>
          </div>

        </div><!-- /.themeasy-admin__content -->
      </div><!-- /.themeasy-admin__container -->
    </div><!-- /.themeasy-admin -->

    <?php
  }

  /**
   * What the Widgets plan adds, for its block of the page.
   *
   * @return array<int, array{icon: string, text: string}>
   */
  protected static function widgets_plan_features(): array {
    return [
      [
        'icon' => 'feature-widgets.svg',
        'text' => sprintf(
          /* translators: %s: number of widgets. */
          _n(
            '%s widget — the free ones plus the Pro content widgets: text, media, elements, blocks, sliders, and data',
            '%s widgets — the free ones plus the Pro content widgets: text, media, elements, blocks, sliders, and data',
            self::WIDGETS_PLAN_COUNT,
            'themeasy-lite'
          ),
          number_format_i18n( self::WIDGETS_PLAN_COUNT )
        ),
      ],
      [
        'icon' => 'feature-motion.svg',
        'text' => __( 'Themeasy Motion — entrance, hover, scroll, and text animations', 'themeasy-lite' ),
      ],
      [
        'icon' => 'feature-any-theme.svg',
        'text' => __( 'Any theme — Hello Elementor, Astra, Twenty Twenty-Five, and more', 'themeasy-lite' ),
      ],
    ];
  }

  /**
   * What Pro adds to the Widgets plan, for its block of the page.
   *
   * @return array<int, array{icon: string, text: string}>
   */
  protected static function pro_features(): array {
    return [
      [
        'icon' => 'feature-themes.svg',
        'text' => __( 'Every Themeasy theme — the whole collection, and each new one', 'themeasy-lite' ),
      ],
      [
        'icon' => 'feature-theme-builder.svg',
        'text' => __( 'Theme builder — custom headers, footers, and global sections', 'themeasy-lite' ),
      ],
      [
        'icon' => 'feature-widgets.svg',
        'text' => sprintf(
          /* translators: 1: number of widgets with a Themeasy theme, 2: number of widgets in any other theme. */
          _n(
            'Every widget category — header, post, site, footer, WooCommerce, mega menu: %1$s widget with a Themeasy theme, %2$s in any other theme',
            'Every widget category — header, post, site, footer, WooCommerce, mega menu: %1$s widgets with a Themeasy theme, %2$s in any other theme',
            self::FULL_OFFER_COUNT,
            'themeasy-lite'
          ),
          number_format_i18n( self::FULL_OFFER_COUNT ),
          number_format_i18n( self::FULL_OFFER_ANY_THEME_COUNT )
        ),
      ],
      [
        'icon' => 'feature-containers.svg',
        'text' => __( 'Container controls — sticky columns, scroll and hover animations, and background effects', 'themeasy-lite' ),
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
        'text' => __( 'The Pro templates of the Themeasy Library, inserted in one click', 'themeasy-lite' ),
      ],
      [
        'icon' => 'feature-support.svg',
        'text' => __( 'Support through the Themeasy help desk', 'themeasy-lite' ),
      ],
    ];
  }

  /**
   * What Agency adds to Pro: exactly where the White Label rebrands (decision
   * C4-b); the rest of the editor carries no brand at all since backlog #282.
   *
   * @return array<int, array{icon: string, text: string}>
   */
  protected static function agency_features(): array {
    return [
      [
        'icon' => 'feature-white-label.svg',
        'text' => __( 'Your brand in the WordPress admin — the plugin\'s menu, page headers, and row on the Plugins screen', 'themeasy-lite' ),
      ],
      [
        'icon' => 'feature-editor.svg',
        'text' => __( 'Your brand in the Elementor editor — the widget categories, the template library, and the icon library tabs', 'themeasy-lite' ),
      ],
    ];
  }

  /**
   * Print the Getting Started section: three steps from a blank page to a
   * finished section, the free widgets by name, and where to get help.
   *
   * @return void
   */
  protected static function render_getting_started(): void {
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
   * Print the "Already purchased?" steps (backlog #268). This plugin takes no
   * license key: a buyer downloads the Themeasy plugin, which deactivates this
   * one when it is activated.
   *
   * @return void
   */
  protected static function render_already_purchased(): void {
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
            // The Hub links a purchase to the account that signs up with its email.
            printf(
              /* translators: %s: link to the Themeasy Hub. */
              esc_html__( 'Download Themeasy from the link in your purchase email, or from the %s (sign up with the email you bought with).', 'themeasy-lite' ),
              '<a href="' . esc_url( self::HUB_PRODUCT_URL ) . '" target="_blank" rel="noopener">' . esc_html__( 'Themeasy Hub', 'themeasy-lite' ) . '</a>'
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
            <?php esc_html_e( 'Activate Themeasy: it deactivates Themeasy Lite for you. Then open Themeasy in the admin menu, click Activate License and enter your license key.', 'themeasy-lite' ); ?>
          </span>
        </li>
      </ol>
    </div>
    <?php
  }

  /**
   * Print the Template Library section: what it is, and a grid of its templates.
   *
   * @return void
   */
  protected static function render_library(): void {
    ?>
    <div class="themeasy-admin__library" aria-labelledby="themeasy-admin-library-title">
      <h2 id="themeasy-admin-library-title" class="themeasy-admin__section-title">
        <?php esc_html_e( 'The Template Library', 'themeasy-lite' ); ?>
      </h2>
      <p class="themeasy-admin__section-lead">
        <?php esc_html_e( 'Ready-made, fully editable page and section templates that insert into the Elementor editor in one click. The library keeps growing.', 'themeasy-lite' ); ?>
      </p>

      <div class="themeasy-admin__grid">
        <?php
        // Each card pairs a library thumbnail (admin/assets/img/library/) with
        // its tier badge: real template screenshots, 1080x810 webp
        // (admin.min.css holds that 4:3 ratio). Swap one by overwriting its
        // slot file; another format needs the extension updated here.
        $templates = [
          ['image' => 'template-1.webp', 'tier' => 'pro'],
          ['image' => 'template-2.webp', 'tier' => 'pro'],
          ['image' => 'template-3.webp', 'tier' => 'pro'],
          ['image' => 'template-4.webp', 'tier' => 'pro'],
          ['image' => 'template-5.webp', 'tier' => 'pro'],
          ['image' => 'template-6.webp', 'tier' => 'pro'],
        ];

        foreach ( $templates as $template ) :
          $is_free_template = 'free' === $template['tier'];
          ?>
          <div class="themeasy-admin__card themeasy-admin__card--<?php echo esc_attr( $is_free_template ? 'free' : 'pro' ); ?>" aria-hidden="true">
            <img class="themeasy-admin__card-image" src="<?php echo esc_url( THEMEASY_URL . 'admin/assets/img/library/' . $template['image'] ); ?>" alt="" loading="lazy" />
            <span class="themeasy-admin__badge">
              <?php echo $is_free_template ? esc_html__( 'Free', 'themeasy-lite' ) : esc_html__( 'Pro', 'themeasy-lite' ); ?>
            </span>
          </div>
        <?php endforeach; ?>
      </div>
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
  protected static function free_widget_titles(): array {
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
  protected static function new_page_url(): string {
    if ( class_exists( '\Elementor\Core\Documents_Manager' ) ) {
      return \Elementor\Core\Documents_Manager::get_create_new_post_url( 'page' );
    }

    return admin_url( 'post-new.php?post_type=page' );
  }

  /**
   * Print a feature list: each entry pairs an admin SVG (admin/assets/svg/)
   * with its label. Swap an icon by overwriting the slot file.
   *
   * @param array<int, array{icon: string, text: string}> $features Icon filename + label.
   * @return void
   */
  protected static function render_feature_list( array $features ): void {
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
  protected static function menu_icon(): string {
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
  protected static function logo_svg(): string {
    return self::read_svg( 'themeasy-logo.svg' );
  }

  /**
   * Read a bundled admin SVG by filename (logos and feature icons).
   *
   * @param string $filename SVG filename inside admin/assets/svg/.
   * @return string
   */
  protected static function read_svg( string $filename ): string {
    $path = THEMEASY_PATH . 'admin/assets/svg/' . basename( sanitize_file_name( $filename ) );

    if ( !is_readable( $path ) ) {
      return '';
    }

    $svg = file_get_contents( $path );

    return false !== $svg ? $svg : '';
  }
}
