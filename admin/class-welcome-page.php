<?php
/**
 * Welcome page: the Themeasy menu of the Free build.
 *
 * Registers the branded `themeasy` top-level menu and its page, in this order:
 * a Getting Started guide (the free widgets, how to reach them in Elementor,
 * help), real Free templates of the library, a short card on Themeasy AI, and
 * how a buyer swaps this plugin for the Themeasy plugin (backlog #268). The
 * plans live on the Site: the page holds one Upgrade button, in its header and
 * in the submenu (owner, 2026-11-23).
 *
 * The page reads no license, plan or trial: it is the same for every site
 * (backlog #362).
 *
 * @package Themeasy\Admin
 * @since 1.0.0
 */

namespace Themeasy\Admin;

use Themeasy\Core\SVG_Sanitizer;
use Themeasy\Elementor\Widget_Categories;

defined( 'ABSPATH' ) || exit;

class Welcome_Page {
  /** Top-level menu + page slug. Matches the Freemius menu slug (themeasy). */
  protected const PAGE_SLUG = 'themeasy';

  /** Hook suffix WordPress assigns to the top-level page. */
  protected const SCREEN_ID = 'toplevel_page_themeasy';

  /** The Template Library's templates, for the hero button of this page. */
  protected const TEMPLATES_URL = 'https://library.themeasy.co/templates/';

  /**
   * The fixed tags of this page's external links: never the site, the user, the
   * version or anything else about the install.
   */
  protected const LINK_TAGS = [
    'utm_source' => 'themeasy-lite',
    'utm_medium' => 'plugin',
    'utm_campaign' => 'lite-admin',
  ];

  /** The checkout a locked template of the library's panel opens. */
  protected const CHECKOUT_URLS = [
    'pro' => 'https://checkout.freemius.com/bundle/31981/plan/52465/',
  ];

  /**
   * The plugin's product in the Themeasy Hub, where a buyer downloads the
   * Themeasy plugin.
   */
  protected const HUB_PRODUCT_URL = 'https://hub.themeasy.co/account/products/themeasy';

  /** Where the plans are, for the one Upgrade button. */
  protected const PRICING_URL = 'https://themeasy.co/pricing/';

  /** The Themeasy AI page. */
  protected const AI_URL = 'https://themeasy.co/ai/';

  /** The Themeasy Help Center (the widgets' own help links point here too). */
  protected const HELP_URL = 'https://hub.themeasy.co/docs';

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
   * The plain checkout of a plan.
   *
   * @param string $plan The plan's slug.
   * @return string
   */
  public static function checkout_url( string $plan ): string {
    return self::CHECKOUT_URLS[$plan] ?? self::CHECKOUT_URLS['pro'];
  }

  /**
   * An external link of this page with its fixed tags and the slot it sits in.
   *
   * @param string $url  The plain URL.
   * @param string $slot Where the link sits on the page.
   * @return string
   */
  protected static function tagged_url( string $url, string $slot ): string {
    return add_query_arg( array_merge( self::LINK_TAGS, ['utm_content' => $slot] ), $url );
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

    add_submenu_page(
      self::PAGE_SLUG,
      esc_html__( 'Upgrade', 'themeasy-lite' ),
      esc_html__( 'Upgrade', 'themeasy-lite' ),
      'manage_options',
      static::tagged_url( self::PRICING_URL, 'upgrade-menu' )
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
   * Render the page: Getting Started, the library, Themeasy AI, Already purchased.
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

          <div class="themeasy-admin__hero themeasy-admin__hero--compact">
            <a class="themeasy-admin__button themeasy-admin__upgrade button button-outline" href="<?php echo esc_url( static::tagged_url( self::PRICING_URL, 'upgrade-header' ) ); ?>" target="_blank" rel="noopener">
              <?php esc_html_e( 'Upgrade', 'themeasy-lite' ); ?>
            </a>
            <div class="themeasy-admin__brand">
              <?php echo wp_kses( self::logo_svg(), SVG_Sanitizer::get_allowed_tags() ); ?>
            </div>
            <h1 class="screen-reader-text">
              <?php esc_html_e( 'Welcome to Themeasy', 'themeasy-lite' ); ?>
            </h1>
            <p class="themeasy-admin__lead">
              <?php esc_html_e( 'Free Elementor widgets for any WordPress theme. No license key required.', 'themeasy-lite' ); ?>
            </p>
            <div class="themeasy-admin__actions">
              <a class="themeasy-admin__button button button-primary button-hero" href="<?php echo esc_url( self::new_page_url() ); ?>">
                <?php esc_html_e( 'Create a Page', 'themeasy-lite' ); ?>
              </a>
              <a class="themeasy-admin__button button button-outline button-hero" href="<?php echo esc_url( static::tagged_url( self::TEMPLATES_URL, 'library-hero' ) ); ?>" target="_blank" rel="noopener">
                <?php esc_html_e( 'Browse the Template Library', 'themeasy-lite' ); ?>
              </a>
            </div>
          </div>

          <?php self::render_getting_started(); ?>
          <?php self::render_library(); ?>
          <?php self::render_ai_card(); ?>
          <?php self::render_already_purchased(); ?>

        </div><!-- /.themeasy-admin__content -->
      </div><!-- /.themeasy-admin__container -->
    </div><!-- /.themeasy-admin -->

    <?php
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
          '<a href="' . esc_url( static::tagged_url( self::HELP_URL, 'help-center' ) ) . '" target="_blank" rel="noopener">' . esc_html__( 'Themeasy Help Center', 'themeasy-lite' ) . '</a>'
        );
        ?>
      </p>
    </div>
    <?php
  }

  /**
   * Print the "Already purchased?" steps, collapsed (backlog #268). This plugin takes no
   * license key: a buyer downloads the Themeasy plugin, which deactivates this
   * one when it is activated.
   *
   * @return void
   */
  protected static function render_already_purchased(): void {
    ?>
    <details class="themeasy-admin__start themeasy-admin__details">
      <summary class="themeasy-admin__section-title">
        <?php esc_html_e( 'Already purchased?', 'themeasy-lite' ); ?>
      </summary>
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
              '<a href="' . esc_url( static::tagged_url( self::HUB_PRODUCT_URL, 'hub-download' ) ) . '" target="_blank" rel="noopener">' . esc_html__( 'Themeasy Hub', 'themeasy-lite' ) . '</a>'
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
    </details>
    <?php
  }

  /**
   * Print the short Themeasy AI card. It is no feature of this plugin: the card
   * says where it is included and links out.
   *
   * @return void
   */
  protected static function render_ai_card(): void {
    ?>
    <div class="themeasy-admin__ai">
      <div class="themeasy-admin__ai-text">
        <h2 class="themeasy-admin__section-title">
          <?php esc_html_e( 'Themeasy AI', 'themeasy-lite' ); ?>
          <span class="themeasy-admin__new-badge"><?php esc_html_e( 'New', 'themeasy-lite' ); ?></span>
        </h2>
        <p class="themeasy-admin__section-lead">
          <?php esc_html_e( 'Build complete WordPress websites with your own AI assistant. A separate AI service included with Pro and Agency plans.', 'themeasy-lite' ); ?>
        </p>
      </div>
      <a class="themeasy-admin__button button button-outline" href="<?php echo esc_url( static::tagged_url( self::AI_URL, 'ai-card' ) ); ?>" target="_blank" rel="noopener">
        <?php esc_html_e( 'Learn more', 'themeasy-lite' ); ?>
      </a>
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
        foreach ( static::library_cards() as $template ) :
          ?>
          <div class="themeasy-admin__card themeasy-admin__card--<?php echo esc_attr( $template['tier'] ); ?>" aria-hidden="true">
            <img class="themeasy-admin__card-image" src="<?php echo esc_url( $template['url'] ); ?>" alt="" loading="lazy" />
            <span class="themeasy-admin__badge">
              <?php echo esc_html( $template['label'] ); ?>
            </span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php
  }

  /**
   * The library's cards on this page: real Free templates, captured from this
   * build, 1080x810 webp (admin.min.css holds that 4:3 ratio). They carry no
   * photograph, so everything the plugin bundles stays GPL-compatible.
   *
   * @return array<int, array{url: string, tier: string, label: string}>
   */
  protected static function library_cards(): array {
    $cards = [];

    for ( $index = 1; $index <= 6; $index++ ) {
      $cards[] = [
        'url' => THEMEASY_URL . 'admin/assets/img/library/free-template-' . $index . '.webp',
        'tier' => 'free',
        'label' => __( 'Free', 'themeasy-lite' ),
      ];
    }

    return $cards;
  }

  /**
   * The titles of the free widgets, in the order the editor panel lists them.
   *
   * Read from Elementor's widget registry and filtered to the Free category, so
   * the page names exactly what the editor offers, never a second copy of the
   * widget list. Empty when Elementor is not running.
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
