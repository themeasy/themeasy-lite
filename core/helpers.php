<?php
/**
 * Themeasy Helper Functions
 *
 * Organized utility functions for Elementor, media, SVGs, taxonomy, UI and integrations.
 *
 * @package Themeasy\Core
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

// ==============================
// CONTEXT
// ==============================

/**
 * Get Themeasy version for cache busting.
 *
 * @return string
 */
function themeasy_get_asset_version(): string {
  return defined( 'TMS_VER' ) ? TMS_VER : '1.0.0';
}

// ==============================
// INTEGRATIONS
// ==============================

/**
 * Retrieve a Themeasy Admin setting.
 *
 * Friendly wrapper around \Themeasy\Admin\Settings::get().
 * Provides a consistent API for templates, widgets, and frontend logic.
 *
 * @package Themeasy
 * @since 1.0.0
 *
 * @param string $key     Setting key (as registered in Settings schema).
 * @param mixed  $default Optional. Default value if not found.
 * @return mixed Setting value or default.
 */
function themeasy_get_setting( string $key, $default = null ) {
  return \Themeasy\Admin\Settings::get( $key, $default );
}

/**
 * The document whose Elementor Page Settings drive the current view.
 *
 * The queried post on a singular view. On a product archive rendered by a
 * Product Archive section (Global Sections), that section: it stands in for
 * the page there, so its Page Layout, variants and header options apply.
 *
 * @package Themeasy
 * @since 1.0.0
 *
 * @return int Post ID, or 0 when the view has no Page Settings.
 */
function themeasy_get_page_settings_id(): int {
  if ( is_singular() ) {
    return (int) get_queried_object_id();
  }

  return function_exists( 'themeasy_wc_get_archive_section_id' ) ? themeasy_wc_get_archive_section_id() : 0;
}

/**
 * Retrieve a per-page setting stored by Elementor's Page Settings.
 *
 * Reads from the `_elementor_page_settings` post meta of the document that
 * drives the view (themeasy_get_page_settings_id()). Returns $default when
 * there is none or when the key is absent, so callers can treat the result as
 * a tri-state "Inherit / value" override.
 *
 * Neutral to Elementor's runtime — works as a plain WP meta read, so it stays
 * safe to call from Core modules that may load before Elementor.
 *
 * @package Themeasy
 * @since 1.0.0
 *
 * @param string $key     Page Setting control ID.
 * @param mixed  $default Optional. Default value if not found.
 * @return mixed Setting value or default.
 */
function themeasy_get_page_setting( string $key, $default = null ) {
  $post_id = themeasy_get_page_settings_id();
  if ( !$post_id ) {
    return $default;
  }

  $settings = get_post_meta( $post_id, '_elementor_page_settings', true );
  if ( !is_array( $settings ) || !array_key_exists( $key, $settings ) ) {
    return $default;
  }

  return $settings[$key];
}

/**
 * Retrieve the active Page Variant slugs for a post.
 *
 * Page Variants are per-page traits derived from Elementor Page Settings
 * (Dark Mode today, more in the future) and mirrored to the
 * `_themeasy_page_variants` post meta on save. Used by Global Sections
 * display rules to target groups of pages by trait.
 *
 * Accepts an explicit $post_id for cross-post queries. With $post_id = 0
 * (default), falls back to the document that drives the view — the same
 * resolution as themeasy_get_page_setting() (themeasy_get_page_settings_id()).
 *
 * @package Themeasy
 * @since 1.0.0
 *
 * @param int $post_id Optional. Defaults to the document that drives the view.
 * @return array<int,string> List of active variant slugs (e.g. ['dark']).
 */
function themeasy_get_page_variants( int $post_id = 0 ): array {
  if ( 0 === $post_id ) {
    $post_id = themeasy_get_page_settings_id();
  }

  if ( !$post_id ) {
    return [];
  }

  // Only when Global Sections booted: `false` keeps the class autoloader from
  // loading (and building its class map for) a module the boot left out.
  if ( !class_exists( '\Themeasy\Global_Sections\Page_Variants', false ) ) {
    return [];
  }

  return \Themeasy\Global_Sections\Page_Variants::get_for_post( $post_id );
}

/**
 * Returns a list of registered WordPress menus.
 *
 * Useful for select dropdowns in Elementor widgets.
 *
 * @return array Associative array of menu slug => name.
 */
function themeasy_get_registered_menus(): array {
  $menus = wp_get_nav_menus();

  if ( empty( $menus ) || is_wp_error( $menus ) ) {
    return [
      '' => esc_html__( 'No menus found. Please create one in Appearance > Menus.', 'themeasy-lite' ),
    ];
  }

  $options = ['' => esc_html__( 'Select a Menu', 'themeasy-lite' )];

  foreach ( $menus as $menu ) {
    if ( !empty( $menu->name ) ) {
      $options[$menu->slug] = $menu->name;
    }
  }

  return $options;
}

/**
 * Normalizes a URL path for comparison (decoded, leading slash, no trailing slash).
 *
 * @param string $path Raw path.
 * @return string Normalized path.
 */
function themeasy_normalize_menu_path( string $path ): string {
  return '/' . trim( rawurldecode( $path ), '/' );
}

/**
 * Whether a manually authored menu URL points at the page being displayed.
 *
 * Registered menus get their active state from WordPress; manual items have only
 * their URL, so it is matched against the current request: same host, same path,
 * and any query args the item declares must be in the request (a plain-permalink
 * link such as `?page_id=12` shares its path with every other page). Anchor-only
 * links (one-page menus) are never current, and a link to the site root — the path
 * every plain-permalink request carries — is current on the front page only.
 *
 * Shared by the Menu and Mobile Menu widgets.
 *
 * @param string $url Authored item URL.
 * @return bool
 */
function themeasy_is_current_menu_url( string $url ): bool {
  $url = trim( $url );

  if ( $url === '' || strpos( $url, '#' ) === 0 ) {
    return false;
  }

  $item = wp_parse_url( $url );

  if ( !is_array( $item ) ) {
    return false;
  }

  // Off-site links are never current.
  if ( !empty( $item['host'] ) ) {
    $home_host = (string) wp_parse_url( home_url(), PHP_URL_HOST );

    if ( strcasecmp( $item['host'], $home_host ) !== 0 ) {
      return false;
    }
  }

  $request = isset( $_SERVER['REQUEST_URI'] )
    ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) )
    : '';
  $current = wp_parse_url( $request );
  $current = is_array( $current ) ? $current : [];

  $item_path = themeasy_normalize_menu_path( $item['path'] ?? '' );

  if ( $item_path !== themeasy_normalize_menu_path( $current['path'] ?? '' ) ) {
    return false;
  }

  // A bare link to the site root only marks the front page itself.
  if ( empty( $item['query'] ) ) {
    $home_path = themeasy_normalize_menu_path( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ) );

    return $item_path === $home_path ? is_front_page() : true;
  }

  // An item carrying query args only matches when the request carries them too.
  parse_str( $item['query'], $item_args );
  parse_str( $current['query'] ?? '', $current_args );

  foreach ( $item_args as $key => $value ) {
    if ( !isset( $current_args[$key] ) || $current_args[$key] !== $value ) {
      return false;
    }
  }

  return true;
}

/**
 * Retrieves categories for a specific post type (post, portfolio, product).
 *
 * @package Themeasy
 * @since 1.0.0
 *
 * @param string       $post_type   Post type to fetch categories for.
 * @param bool         $full_data   Whether to return full category objects.
 * @param array        $custom_args Custom args (only valid for 'product').
 * @return array|false List of categories or false on error.
 */
function themeasy_get_categories( string $post_type, bool $full_data = false, array $custom_args = [] ) {
  $taxonomy_map = [
    'post' => 'category',
    'portfolio' => 'portfolio-category',
    'product' => 'product_cat',
  ];

  if ( !isset( $taxonomy_map[$post_type] ) ) {
    return false;
  }

  $args = [
    'taxonomy' => $taxonomy_map[$post_type],
    'orderby' => 'name',
    'order' => 'ASC',
  ];

  // Only allow custom args override for product post type.
  if ( $post_type === 'product' && is_array( $custom_args ) ) {
    $args = array_merge( $args, $custom_args );
  }

  $terms = get_categories( $args );
  if ( empty( $terms ) || is_wp_error( $terms ) ) {
    return false;
  }

  if ( $full_data ) {
    return $terms;
  }

  $list = [];
  foreach ( $terms as $term ) {
    $list[esc_attr( $term->term_id )] = esc_html( $term->name );
  }

  return $list;
}

/**
 * Resolve the primary term a post declares for a taxonomy.
 *
 * WordPress has no native concept of a primary term, so the value is read from
 * the post meta written by the major SEO plugins (Yoast SEO, Rank Math and
 * SEOPress). The filter lets any other source take over.
 *
 * @package Themeasy
 * @since 1.0.0
 *
 * @param int    $post_id  Post ID owning the terms.
 * @param string $taxonomy Taxonomy slug.
 * @return int Term ID, or 0 when the post declares no primary term.
 */
function themeasy_get_primary_term_id( int $post_id, string $taxonomy ): int {
  $primary_term_id = 0;

  if ( $post_id > 0 && '' !== $taxonomy ) {
    // Yoast SEO and Rank Math both store it per taxonomy.
    $meta_keys = [
      '_yoast_wpseo_primary_' . $taxonomy,
      'rank_math_primary_' . $taxonomy,
    ];

    // SEOPress only supports a primary category, under a fixed key.
    if ( 'category' === $taxonomy ) {
      $meta_keys[] = '_seopress_robots_primary_cat';
    }

    foreach ( $meta_keys as $meta_key ) {
      $stored = absint( get_post_meta( $post_id, $meta_key, true ) );

      if ( $stored ) {
        $primary_term_id = $stored;
        break;
      }
    }
  }

  /**
   * Filters the primary term ID resolved for a post.
   *
   * @param int    $primary_term_id Resolved term ID (0 when none was found).
   * @param int    $post_id         Post being rendered.
   * @param string $taxonomy        Taxonomy slug being resolved.
   */
  return absint( apply_filters( 'themeasy/primary_term_id', $primary_term_id, $post_id, $taxonomy ) );
}

/**
 * Pick the term a post leads with, out of an already fetched term list.
 *
 * Used wherever a single term stands for the post (card badges, meta rows).
 * Falls back to the first usable term when the post declares no primary term,
 * or when the primary term is not part of the given list.
 *
 * @package Themeasy
 * @since 1.0.0
 *
 * @param int    $post_id  Post ID owning the terms.
 * @param mixed  $terms    Term list to choose from; anything but a non-empty array yields null.
 * @param string $taxonomy Optional. Taxonomy slug; derived from the list when empty.
 * @return \WP_Term|null Chosen term, or null when the list holds no usable term.
 */
function themeasy_get_primary_term( int $post_id, $terms, string $taxonomy = '' ) {
  if ( !is_array( $terms ) || empty( $terms ) ) {
    return null;
  }

  $usable = [];

  foreach ( $terms as $term ) {
    if ( $term instanceof \WP_Term && $term->term_id ) {
      $usable[] = $term;
    }
  }

  if ( empty( $usable ) ) {
    return null;
  }

  if ( '' === $taxonomy ) {
    $taxonomy = (string) $usable[0]->taxonomy;
  }

  $primary_term_id = themeasy_get_primary_term_id( $post_id, $taxonomy );

  if ( $primary_term_id ) {
    foreach ( $usable as $term ) {
      if ( $primary_term_id === (int) $term->term_id ) {
        return $term;
      }
    }
  }

  return $usable[0];
}

// ==============================
// ANIMATIONS
// ==============================

/**
 * Returns a list of animation options based on the animation type.
 *
 * Used in Elementor controls to populate animation dropdowns.
 *
 * @param string $type Animation type: text, block, reveal, hover, slider, menu, submenu.
 * @return array Associative array of animation keys and labels.
 */
function themeasy_get_animation_options( string $type ): array {
  $type = trim( strtolower( $type ) );

  switch ( $type ) {
    case 'text':
      return [
        '' => 'None',
        'cascading' => 'Cascading',
        'elastic' => 'Elastic',
        'blurIn' => 'Blur In',
        'blurReveal' => 'Blur Reveal',
        'fadeIn' => 'Fade In',
        'fadeInUp' => 'Fade In Up',
        'fadeRandom' => 'Fade Random',
        'zoomIn' => 'Zoom In',
        'zoomInUp' => 'Zoom In Up',
        'popIn' => 'Pop In',
        'slideInUp' => 'Slide In Up',
        'slideInLeft' => 'Slide In Left',
        'slideInRight' => 'Slide In Right',
        'sliding' => 'Sliding',
        // The 'typing' key is a staggered opacity fade with an ease-in curve, not a
        // typewriter — the label names the curve so it stops promising a caret.
        // The typewriter effect lives in the dedicated Typed Text widget.
        'typing' => 'Fade In Slow',
        'flipping' => 'Flipping',
        'twirl' => 'Twirl',
        'bounce' => 'Bounce',
        'scrollFade' => 'Scroll Fade',
      ];

    case 'block':
      return [
        '' => 'None',
        'blurIn' => 'Blur In',
        'blurReveal' => 'Blur Reveal',
        'fadeIn' => 'Fade In',
        'fadeInUp' => 'Fade In Up',
        'zoomIn' => 'Zoom In',
        'zoomInUp' => 'Zoom In Up',
        'popIn' => 'Pop In',
        'slideInUp' => 'Slide In Up',
        'slideInLeft' => 'Slide In Left',
        'slideInRight' => 'Slide In Right',
        'bounce' => 'Bounce',
      ];

    case 'reveal':
      return [
        '' => 'None',
        'slideRight' => 'Slide Right',
        'slideLeft' => 'Slide Left',
        'zoomInRight' => 'Zoom In Right',
        'zoomOutRight' => 'Zoom Out Right',
        'horizontal' => 'Center — Horizontal',
        'vertical' => 'Center — Vertical',
        'diagonalTopRight' => 'Diagonal — Top Right',
      ];

    case 'hover':
      return [
        '' => 'None',
        'float' => 'Float',
        'slideUp' => 'Slide Up',
        'slideUpElastic' => 'Slide Up Elastic',
        'zoomIn' => 'Zoom In',
        'zoomInElastic' => 'Zoom In Elastic',
        'pulse' => 'Pulse',
        'flip' => 'Flip',
        'flipX' => 'Flip X',
        'tilt' => 'Tilt',
        'bounce' => 'Bounce',
        'wobble' => 'Wobble',
        'skew' => 'Skew',
        'glitch' => 'Glitch',
        'rotate3D' => 'Rotate 3D',
        'magnetic' => 'Magnetic',
        'viewCursor' => 'View Cursor',
      ];

    case 'slider':
      return [
        'slideInLeft' => 'Slide In Left',
        'zoomIn' => 'Zoom In',
        'zoomOut' => 'Zoom Out',
        'blurIn' => 'Blur In',
      ];

    case 'menu':
      return [
        '' => 'None',
        'fadeIn' => 'Fade In',
        'fadeInUp' => 'Fade In Up',
        'zoomIn' => 'Zoom In',
        'zoomInUp' => 'Zoom In Up',
        'blurIn' => 'Blur In',
      ];

    case 'submenu':
      return [
        '' => 'None',
        'fadeIn' => 'Fade In',
        'fadeInUp' => 'Fade In Up',
        'zoomIn' => 'Zoom In',
        'zoomInUp' => 'Zoom In Up',
        'blurIn' => 'Blur In',
        'slideDown' => 'Slide Down',
      ];

    default:
      return [];
  }
}

/**
 * Register the Themeasy Motion upsell section on a Free widget.
 *
 * Themeasy Motion (entrance, hover, text and loop animations, scroll text,
 * draw-on) runs on GSAP, which the Free build must neither ship nor load: its
 * license is not GPL-compatible (backlog #253). A Free widget registers its
 * motion controls only when Entitlement::can_use_widgets() and calls this
 * otherwise, so the Advanced tab says where the effects live instead of
 * silently losing them.
 *
 * @param \Elementor\Controls_Stack $element The widget registering its controls.
 * @return void
 */
function themeasy_register_motion_upsell_section( \Elementor\Controls_Stack $element ): void {
  $element->start_controls_section(
    'themeasy_motion_upsell_section',
    [
      'label' => esc_html__( 'Themeasy Motion', 'themeasy-lite' ),
      'tab' => \Elementor\Controls_Manager::TAB_ADVANCED,
    ]
  );

  $element->add_control(
    'themeasy_motion_upsell',
    [
      'type' => \Elementor\Controls_Manager::RAW_HTML,
      'raw' => sprintf(
        '%1$s <a href="%2$s" target="_blank" rel="noopener">%3$s</a>',
        esc_html__( 'Animations and hover effects for this widget are part of Themeasy Pro.', 'themeasy-lite' ),
        esc_url( admin_url( 'admin.php?page=themeasy' ) ),
        esc_html__( 'Upgrade to Pro', 'themeasy-lite' )
      ),
      'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
    ]
  );

  $element->end_controls_section();
}

// ==============================
// UI & HTML HELPERS
// ==============================

/**
 * Sanitize a heading tag (h1..h6). Fallback if invalid.
 *
 * @param mixed  $tag      The requested tag.
 * @param string $fallback Fallback tag. Default 'h2'.
 * @param array  $allowed  Optional. Allowed heading tags. Default h1..h6.
 * @return string Sanitized heading tag.
 * @since 1.0.0
 */
function themeasy_sanitize_heading_tag( $tag, $fallback = 'h2', $allowed = [] ) {
  $default_allowed = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
  $allowed_tags = !empty( $allowed ) && is_array( $allowed ) ? $allowed : $default_allowed;

  $tag = is_string( $tag ) ? strtolower( $tag ) : '';
  $fallback = is_string( $fallback ) ? strtolower( $fallback ) : 'h2';

  // Ensure fallback is always safe.
  if ( !in_array( $fallback, $allowed_tags, true ) ) {
    $fallback = 'h2';
  }

  $result = in_array( $tag, $allowed_tags, true ) ? $tag : $fallback;

  /**
   * Filter the sanitized heading tag.
   *
   * The return value is re-validated against $allowed_tags below — the filter can
   * only pick a different tag from the allowlist, never widen it. Callers echo this
   * value straight into markup as a tag name, where esc_html() would not neutralize
   * an injected attribute (no <>&"' characters are required to smuggle one).
   *
   * @param string $result  The sanitized tag.
   * @param mixed  $tag     The requested tag.
   * @param string $fallback The fallback tag.
   * @param array  $allowed_tags Allowed tags.
   */
  $result = apply_filters( 'themeasy/sanitize_heading_tag', $result, $tag, $fallback, $allowed_tags );

  return in_array( $result, $allowed_tags, true ) ? $result : $fallback;
}

/**
 * Get Themeasy allowed HTML tags for wp_kses().
 *
 * Usage:
 * - wp_kses( $text, themeasy_get_kses_allowed_tags() );
 * - wp_kses( $text, themeasy_get_kses_allowed_tags( 'rich' ) );
 * - wp_kses( $text, themeasy_get_kses_allowed_tags( 'inline', $extra_tags ) );
 *
 * Every allowed tag also accepts `class` and `style` (see $global_attrs below),
 * so authored text can carry inline formatting such as
 * `<span style="color:#F43D00">*</span>`.
 *
 * @param string $context Supported: 'inline', 'rich'. Default 'inline'.
 * @param array  $extra_allowed Optional. Extra tags/attributes to merge (overrides defaults).
 * @return array Allowed tags array compatible with wp_kses().
 * @since 1.0.0
 */
function themeasy_get_kses_allowed_tags( $context = 'inline', $extra_allowed = [] ) {
  // ------------------------------------------------------------------------
  // Attributes every allowed tag accepts.
  //
  // `style` is safe to expose here because wp_kses() never takes the value at
  // face value: wp_kses_attr() intercepts the attribute and runs it through
  // WordPress core's safecss_filter_attr() (wp-includes/kses.php), which keeps
  // only allow-listed properties (plus `--custom-props`), validates url()
  // tokens against wp_allowed_protocols(), and drops any declaration whose
  // value still holds a backslash, an unrecognized function call, an ampersand,
  // an equals sign, a brace or a CSS comment. That is the very filtering
  // wp_kses_post() applies to post content, so authored widget text is no more
  // permissive than the body copy WordPress already accepts from the same user.
  //
  // Keep the editor twin in sync: SANITIZE_GLOBAL_ATTRS + safeCssText() in
  // themeasy-el-editor-helpers.min.js mirror this map and safecss_filter_attr(),
  // and tests/kses-inline-style-parity.php fails the build if the preview ever
  // drifts looser than the frontend.
  // ------------------------------------------------------------------------
  $global_attrs = [
    'class' => true,
    'style' => true,
  ];

  // ------------------------------------------------------------------------
  // Base: safe inline formatting.
  // Per-tag entries list only what is EXTRA to $global_attrs.
  // ------------------------------------------------------------------------
  $allowed = [
    'mark' => [],
    'b' => [],
    'strong' => [],
    'em' => [],
    'i' => [],
    'u' => [],
    'del' => [],
    's' => [],
    'sub' => [],
    'sup' => [],
    'small' => [],
    'span' => [],
    'br' => [],
    'wbr' => [],
  ];

  // Optional: allow basic links (still safe with wp_kses + esc_url on output if you build URLs).
  $allowed['a'] = [
    'href' => true,
    'title' => true,
    'target' => true,
    'rel' => true,
    'aria-label' => true,
  ];

  // ------------------------------------------------------------------------
  // Rich text: paragraphs + simple lists.
  // Use this only where you truly expect richer content.
  // ------------------------------------------------------------------------
  if ( 'rich' === $context ) {
    $allowed = array_merge(
      $allowed,
      [
        'p' => [],
        'div' => [],
        'ul' => [],
        'ol' => [],
        'li' => [],
      ]
    );
  }

  // ------------------------------------------------------------------------
  // Apply the global attributes to every tag. Done before the custom merge so
  // a caller can still widen (or narrow) a single tag on top of them.
  // ------------------------------------------------------------------------
  foreach ( $allowed as $tag => $attributes ) {
    $allowed[$tag] = $attributes + $global_attrs;
  }

  // ------------------------------------------------------------------------
  // Merge custom allowed tags/attributes (widget-specific needs).
  // If you pass the same tag, your attributes override the defaults.
  // ------------------------------------------------------------------------
  if ( is_array( $extra_allowed ) && !empty( $extra_allowed ) ) {
    $allowed = array_replace_recursive( $allowed, $extra_allowed );
  }

  /**
   * Filter Themeasy allowed tags for wp_kses.
   *
   * @param array  $allowed Allowed tags array.
   * @param string $context Context string ('inline'|'rich').
   */
  return apply_filters( 'themeasy/kses_allowed_tags', $allowed, $context );
}

/**
 * Converts an associative array into HTML attributes.
 *
 * Example:
 * ['id' => 'main', 'class' => 'wrapper'] → ' id="main" class="wrapper"'
 *
 * Values are escaped with esc_attr(). Names are normalized to a safe subset
 * (alphanumerics, colon, underscore, hyphen): browsers do not decode entities
 * in attribute-name position, so escaping cannot stop a hostile name from
 * splitting the attribute — stripping can. Mirrors Themeasy.htmlAttributes()
 * in JS.
 *
 * @param array $attributes Associative array of attributes.
 * @return string
 * @since 1.0.0
 */
function themeasy_html_attributes( array $attributes ): string {
  $output = '';

  foreach ( $attributes as $key => $value ) {
    if ( $value === null || $value === '' || $value === false ) {
      continue;
    }

    if ( is_array( $value ) || is_object( $value ) ) {
      continue;
    }

    $safe_key = preg_replace( '/[^a-z0-9:_-]/i', '', (string) $key );

    if ( $safe_key === null || $safe_key === '' ) {
      continue;
    }

    $output .= sprintf( ' %s="%s"', $safe_key, esc_attr( (string) $value ) );
  }

  return $output;
}

/**
 * Adds rel="noopener noreferrer" to a link that opens in a new tab.
 *
 * Elementor's add_link_attributes() writes target="_blank" when the URL control
 * has "Open in new window" checked, and rel="nofollow" when nofollow is checked
 * — but it never emits noopener/noreferrer (see
 * elementor/includes/base/element-base.php, add_link_attributes()). WordPress no
 * longer covers the gap either: wp_targeted_link_rel() was deprecated in 6.7.
 *
 * Call it immediately after add_link_attributes(), passing the same render
 * attribute key and URL control value. Elementor merges render attribute values,
 * so an existing rel="nofollow" is kept ("nofollow noopener noreferrer") rather
 * than replaced.
 *
 * @param \Elementor\Element_Base $element Widget rendering the link.
 * @param string                  $key     Render attribute key used for the link.
 * @param array                   $link    Elementor URL control value.
 * @return void
 * @since 1.0.0
 */
function themeasy_add_external_link_rel( $element, string $key, array $link ): void {
  if ( empty( $link['is_external'] ) ) {
    return;
  }

  $element->add_render_attribute( $key, 'rel', 'noopener noreferrer' );
}

/**
 * Builds a CSS url() token from an Elementor MEDIA control value.
 *
 * Never bind a MEDIA control's {{URL}} into a control's `selectors` array.
 * Elementor only re-derives the URL safely when the value carries an attachment
 * id; the built-in "Insert from URL" flow leaves the id empty, and
 * Control_Base_Multiple::get_style_value() then returns the raw string, which is
 * spliced into the generated per-post stylesheet unescaped. A crafted value
 * closes the declaration and injects arbitrary CSS for anyone who can edit the
 * page (confirmed against text-marquee, 2026-07-19). Resolve it here instead and
 * emit the result as an inline style.
 *
 * The double-quoted token is the safe shape: esc_url_raw() strips `"` (the only
 * character able to break out of it), while the `(`/`)`/`;` it does preserve are
 * inert inside a quoted CSS string. An unquoted url() token would NOT be safe.
 *
 * @param mixed  $media Elementor MEDIA control value ( ['id' => int, 'url' => string] ).
 * @param string $size  Registered image size used for the attachment lookup.
 * @return string A `url("…")` token, or '' when no usable image is set.
 * @since 1.0.0
 */
function themeasy_media_css_url( $media, string $size = 'full' ): string {
  if ( !is_array( $media ) ) {
    return '';
  }

  $attachment_id = absint( $media['id'] ?? 0 );

  $url = $attachment_id
    ? (string) wp_get_attachment_image_url( $attachment_id, $size )
    : esc_url_raw( (string) ( $media['url'] ?? '' ) );

  if ( '' === $url ) {
    return '';
  }

  return 'url("' . $url . '")';
}

/**
 * Collapses an Elementor responsive control into the
 * "desktop,laptop,tablet,mobile" CSV the slider module reads.
 *
 * The module receives one attribute per setting, so without this only the
 * desktop value of a responsive control would ever reach Swiper. Elementor
 * stores a device value only when the user sets one, so an empty breakpoint
 * inherits the previous (wider) one — the same cascade the editor shows.
 *
 * Mirrors Themeasy.sliderResponsiveCsv() in JS (editor preview parity).
 *
 * Example: 25 / – / 8 / – → '25,25,8,8'
 *
 * @param array     $settings Widget settings.
 * @param string    $control  Base control key.
 * @param int|float $fallback Value used when the desktop slot is empty.
 * @return string
 * @since 1.0.0
 */
function themeasy_slider_responsive_csv( array $settings, string $control, $fallback ): string {
  $values = [];
  $previous = $fallback;

  foreach ( ['', '_laptop', '_tablet', '_mobile'] as $suffix ) {
    $size = $settings[$control . $suffix]['size'] ?? '';

    if ( is_numeric( $size ) ) {
      $previous = $size + 0;
    }

    $values[] = $previous;
  }

  return implode( ',', $values );
}

/**
 * Box Shadow group `fields_options` that also publish the shadow's reach to
 * the carousel (backlog #220).
 *
 * A module carousel's `.swiper` root must keep `overflow: hidden`, so it clips
 * whatever a slide paints outside it. Next to the regular `box-shadow` rule,
 * the group now writes the shadow's vertical offset and reach (blur + spread)
 * as custom properties on the widget wrapper, and the slider module turns them
 * into the root's block padding: only a widget whose slide carries a shadow
 * buys room, sized to that shadow, in the editor and on the front alike.
 *
 * The `selectors` of the inner field are replaced whole (Elementor merges
 * `fields_options` shallowly), so the stock declaration is repeated verbatim.
 *
 * @param string $slot '' for the slide's resting shadow, 'alt' for a second one
 *                     the slide can paint (its hover state, or an inner box).
 * @return array
 * @since 1.0.0
 */
function themeasy_carousel_shadow_room_fields( string $slot = '' ): array {
  $prefix = '--tms-carousel-shadow' . ( '' !== $slot ? '-' . $slot : '' );

  return [
    'box_shadow' => [
      'selectors' => [
        '{{SELECTOR}}' => 'box-shadow: {{HORIZONTAL}}px {{VERTICAL}}px {{BLUR}}px {{SPREAD}}px {{COLOR}} {{box_shadow_position.VALUE}};',
        '{{WRAPPER}}' => $prefix . '-y: {{VERTICAL}}px; ' . $prefix . '-reach: calc({{BLUR}}px + {{SPREAD}}px);',
      ],
    ],
  ];
}

/**
 * Arguments of the carousel `stage_padding` control (backlog #220).
 *
 * Same key and meaning as the Coverflow's: the room inside the clipping
 * `.swiper` root. Empty by default, because the room is sized from the
 * slide's Box Shadow on its own (themeasy_carousel_shadow_room_fields()); a
 * value here overrides that automatic room.
 *
 * @return array
 * @since 1.0.0
 */
function themeasy_carousel_stage_padding_control(): array {
  return [
    'label' => esc_html__( 'Stage Padding', 'themeasy-lite' ),
    'description' => esc_html__( 'Room inside the carousel, which clips whatever leaves it. Leave empty to size it from the Box Shadow automatically; raise the side a shadow is still cut off on.', 'themeasy-lite' ),
    'type' => \Elementor\Controls_Manager::DIMENSIONS,
    'size_units' => ['px', 'rem'],
    'allowed_dimensions' => 'vertical',
    'selectors' => [
      '{{WRAPPER}} .tms-slider__wrapper' =>
        '--tms-carousel-room-top: {{TOP}}{{UNIT}}; --tms-carousel-room-bottom: {{BOTTOM}}{{UNIT}};',
    ],
  ];
}

/**
 * Carries a saved pagination shadow over to the Default / Custom / None select.
 *
 * Post Grid, Archive Results and Product Grid used to expose the pager shadow
 * as a Box Shadow popover seeded ON, with the component's token shadow zeroed
 * inside them, so a theme without shadows could never flatten the pager. The
 * `pagination_shadow` select now owns the choice (Default = the .tms-pager
 * token) and the popover only renders under Custom. Elementor stores only what
 * diverged from the old seed, which leaves two populations to carry over:
 * the popover switched off ('' stored, also what the Builder writes for
 * "none") becomes None, and a stored shadow becomes Custom. An instance that
 * stored neither rode the seed and now follows the theme. Called from each
 * widget's constructor, so the data self-migrates on the next editor save.
 *
 * @param array $data Widget data (settings included on real instances).
 * @return array
 * @since 1.0.0
 */
function themeasy_migrate_pagination_shadow( array $data ): array {
  $settings = $data['settings'] ?? null;

  if ( !is_array( $settings ) || isset( $settings['pagination_shadow'] ) ) {
    return $data;
  }

  $type = $settings['pagination_box_shadow_box_shadow_type'] ?? null;

  if ( '' === $type ) {
    $data['settings']['pagination_shadow'] = 'none';
  } elseif ( 'yes' === $type || isset( $settings['pagination_box_shadow_box_shadow'] ) ) {
    $data['settings']['pagination_shadow'] = 'custom';
  }

  return $data;
}

/**
 * Returns responsive Bootstrap column classes based on number of columns.
 *
 * Example: 4 → 'row row-cols-1 row-cols-xs-2 row-cols-sm-4'
 *
 * Each breakpoint caps how many columns fit from that width up. Widgets with
 * denser items (a pricing plan needs far more room than a gallery thumb) pass
 * their own caps, ordered from the narrowest breakpoint up; mirrored by
 * Themeasy.bootstrapColumns() for content_template().
 *
 * @param int                $columns     Number of columns (1–12).
 * @param array<string, int> $breakpoints Optional breakpoint => column cap map.
 * @return string
 * @since 1.0.0
 */
function themeasy_bootstrap_columns( int $columns, array $breakpoints = [] ): string {
  $columns = max( 1, min( 12, $columns ) );

  if ( empty( $breakpoints ) ) {
    $breakpoints = [
      'xs' => 2,
      'sm' => 4,
      'md' => 6,
      'lg' => 8,
      'xl' => 10,
      'xxl' => 12,
    ];
  }

  $classes = 'row row-cols-1';

  if ( 1 === $columns ) {
    return $classes;
  }

  foreach ( $breakpoints as $bp => $min_col ) {
    $classes .= sprintf( ' row-cols-%s-%d', $bp, ( $columns >= $min_col ) ? $min_col : $columns );
    if ( $columns <= $min_col ) {
      break;
    }
  }

  return $classes;
}

/**
 * Column classes for a product loop: themeasy_bootstrap_columns() with the
 * phone count set by the store.
 *
 * The shared helper starts every grid at one column and only doubles it from
 * 400px, so the 375–390px phones most shoppers hold saw one product per row on
 * every store (Builder session, greengage, 2026-09-27). Below 576px the loop
 * shows `$phone` columns (capped at the desktop count).
 *
 * From 576px up a product card needs about 160px (rating stars, a struck-out
 * price beside the sale price), so the loop passes its own caps instead of the
 * shared gallery-sized ones (sm 4 / md 6): sm 3 / md 4 / lg 5 / xl 6. The
 * shared helper had a 5-column grid showing 118px cards at 768px (backlog #223).
 *
 * Example: (4, 2) → 'row row-cols-2 row-cols-sm-3 row-cols-md-4'
 *
 * @param int $columns Desktop products per row (1–12).
 * @param int $phone   Products per row below 576px (default 2).
 * @return string
 * @since 1.0.1
 */
function themeasy_product_columns( int $columns, int $phone = 2 ): string {
  $classes = themeasy_bootstrap_columns(
    $columns,
    [
      'xs' => 2,
      'sm' => 3,
      'md' => 4,
      'lg' => 5,
      'xl' => 6,
      'xxl' => 12,
    ]
  );
  $phone = max( 1, min( $phone, max( 1, min( 12, $columns ) ) ) );

  // The base count holds up to 576px now: the 400px step is dropped, so an
  // explicit 1 stays one column across every phone width too.
  $classes = preg_replace( '/\brow-cols-1\b/', 'row-cols-' . $phone, $classes, 1 );

  return (string) preg_replace( '/\s+row-cols-xs-\d+\b/', '', $classes );
}

// ==============================
// MEDIA / IMAGE HELPERS
// ==============================

/**
 * Renders a basic <img> tag if a source URL is provided.
 *
 * @param string $src        Image source URL.
 * @param string $alt        Image alt text.
 * @param string $class      Optional CSS class.
 * @param array  $attributes Optional extra HTML attributes.
 * @since 1.0.0
 */
function themeasy_render_image_tag( string $src, string $alt = '', string $class = '', array $attributes = [] ): void {
  if ( empty( $src ) ) {
    return;
  }

  $class_attr = $class ? sprintf( ' class="%s"', esc_attr( $class ) ) : '';
  $extra_attr = themeasy_html_attributes( $attributes );

  printf(
    '<img src="%s" alt="%s"%s loading="lazy" decoding="async"%s />',
    esc_url( $src ),
    esc_attr( $alt ),
    $class_attr,
    $extra_attr ? ' ' . $extra_attr : ''
  );
}

/**
 * Renders an <img> tag with responsive support via attachment ID.
 *
 * @param int    $image_id         Attachment ID (optional).
 * @param string $size             Image size (e.g., 'full', 'medium').
 * @param string $fallback_src     Optional fallback src (if ID not provided).
 * @param string $alt              Image alt text.
 * @param string $class            Optional CSS class.
 * @param array  $attributes       Optional extra HTML attributes.
 * @param string $loading_priority 'lazy' or 'high' (uses fetchpriority).
 * @since 1.0.0
 */
function themeasy_render_attachment_image( int $image_id = 0, string $size = 'full', string $fallback_src = '', string $alt = '', string $class = '', array $attributes = [], string $loading_priority = 'lazy' ): void {
  $src = $fallback_src;
  $width = $height = $srcset = $sizes = '';

  if ( $image_id ) {
    $image_data = wp_get_attachment_image_src( $image_id, $size );
    if ( !empty( $image_data[0] ) ) {
      $src = $image_data[0];
      $width = (int) ( $image_data[1] ?? 0 );
      $height = (int) ( $image_data[2] ?? 0 );
    }

    $srcset = wp_get_attachment_image_srcset( $image_id, $size );
    $sizes = wp_get_attachment_image_sizes( $image_id, $size );
  }

  if ( empty( $src ) ) {
    return;
  }

  $class_attr = $class ? sprintf( ' class="%s"', esc_attr( $class ) ) : '';
  $extra_attr = themeasy_html_attributes( $attributes );
  $loading = $loading_priority === 'high' ? ' fetchpriority="high"' : ' loading="lazy"';

  printf(
    '<img src="%s" alt="%s"%s%s%s%s%s%s%s decoding="async" />',
    esc_url( $src ),
    esc_attr( $alt ),
    $class_attr,
    $width  ? sprintf( ' width="%d"', $width ) : '',
    $height ? sprintf( ' height="%d"', $height ) : '',
    $srcset ? sprintf( ' srcset="%s"', esc_attr( $srcset ) ) : '',
    $sizes  ? sprintf( ' sizes="%s"', esc_attr( $sizes ) ) : '',
    $loading,
    $extra_attr ? ' ' . $extra_attr : ''
  );
}

/**
 * Retrieves multiple meta fields for an image.
 *
 * Returns an array with metadata like alt, title, caption, etc., including custom Themeasy fields.
 *
 * @param int   $image_id Attachment ID.
 * @param array $fields   List of field keys to retrieve.
 * @return array Associative array of field => value.
 */
function themeasy_get_image_meta_fields( int $image_id, array $fields ): array {
  if ( !$image_id || empty( $fields ) ) {
    return [];
  }

  $data = [];

  foreach ( $fields as $field ) {
    switch ( $field ) {
      case 'category':
        $data['category'] = get_post_meta( $image_id, '_tms_category', true );
        break;
      case 'custom_link':
        $data['custom_link'] = get_post_meta( $image_id, '_tms_custom_link', true );
        break;
      case 'video':
        $data['video'] = get_post_meta( $image_id, '_tms_video_url', true );
        break;
      case 'alt':
        $data['alt'] = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
        break;
      case 'title':
        $data['title'] = get_the_title( $image_id );
        break;
      case 'caption':
        $data['caption'] = wp_get_attachment_caption( $image_id );
        break;
      case 'description':
        $data['description'] = get_post_field( 'post_content', $image_id );
        break;
      default:
        $data[$field] = null;
    }
  }

  return $data;
}

/**
 * Retrieves a list of unique image categories stored via _tms_category postmeta.
 *
 * Used for filtering or dropdown controls related to images with custom metadata.
 *
 * @package Themeasy
 * @since 1.0.0
 *
 * @return array Associative array: [slug => Category Name]
 */
function themeasy_get_unique_image_categories(): array {
  global $wpdb;

  $meta_key = '_tms_category';
  $query = $wpdb->prepare(
    "SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value != ''",
    $meta_key
  );

  $raw_results = $wpdb->get_col( $query );
  if ( empty( $raw_results ) ) {
    return [];
  }

  $categories = [];

  foreach ( $raw_results as $value ) {
    $names = array_map( 'trim', explode( ',', $value ) );

    foreach ( $names as $name ) {
      if ( $name ) {
        $slug = sanitize_title( $name );
        if ( !isset( $categories[$slug] ) ) {
          $categories[$slug] = ucwords( esc_html( $name ) );
        }
      }
    }
  }

  return $categories;
}

// ==============================
// SVG / ICON HELPERS
// ==============================

/**
 * Returns inline SVG icon from assets with optional class injection.
 *
 * @param string $library    Icon library folder name.
 * @param string $icon       Icon file name without .svg extension.
 * @param string $class      Optional CSS class to inject into <svg>.
 * @param string $subfolder  Optional subfolder (e.g. '1x1' for flags).
 * @return string Inline SVG content or empty string if not found.
 * @since 1.0.0
 */
function themeasy_get_svg_icon( string $library, string $icon, string $class = '', string $subfolder = '' ): string {
  // Per-request static cache of the RAW file contents (class injection varies
  // per call): icons repeat inside loops (carousel arrows, checklist glyphs,
  // post grids), and re-reading the same file from disk each item adds up.
  // Misses are cached as '' too, so a broken reference costs one lookup only.
  static $cache = [];

  // Path-segment guard. NOT sanitize_file_name(): that is an UPLOAD-filename
  // sanitizer, and it rewrites a dotless name that happens to be a registered
  // MIME extension into "unnamed-file.{ext}" — 'key' (Apple Keynote) became
  // 'unnamed-file.key' and the glyph silently vanished from every ty-* library
  // (backlog #112). This class keeps the shape every library dir, subfolder and
  // icon name already has, and strips '.', '/' and '\' outright, so directory
  // traversal cannot survive it.
  $library = (string) preg_replace( '/[^a-z0-9_-]/i', '', $library );
  $icon = (string) preg_replace( '/[^a-z0-9_-]/i', '', $icon );
  $subfolder = (string) preg_replace( '/[^a-z0-9_-]/i', '', $subfolder );

  $cache_key = "{$library}/{$subfolder}/{$icon}";

  if ( !isset( $cache[$cache_key] ) ) {
    $cache[$cache_key] = '';

    $base_path = TMS_PATH . 'assets/media/svg/' . $library;
    $file_path = $subfolder
      ? "{$base_path}/{$subfolder}/{$icon}.svg"
      : "{$base_path}/{$icon}.svg";

    if ( file_exists( $file_path ) ) {
      // Ensure the resolved path stays within the expected SVG directory.
      $resolved = realpath( $file_path );
      $base_real = realpath( TMS_PATH . 'assets/media/svg/' );

      if ( false !== $resolved && false !== $base_real && strpos( $resolved, $base_real ) === 0 ) {
        $svg = file_get_contents( $file_path );

        if ( false !== $svg ) {
          $cache[$cache_key] = $svg;
        }
      }
    }
  }

  if ( '' === $cache[$cache_key] ) {
    return '';
  }

  return themeasy_inject_svg_class( $cache[$cache_key], $class );
}

/**
 * Inject HTML attributes into the root <svg> element.
 *
 * Targets only the opening <svg> tag so child elements (paths, groups)
 * that happen to carry their own attributes aren't rewritten. The `class`
 * attribute is merged into any existing class. Other attributes replace
 * the existing value for the same name, or are appended when absent.
 *
 * Attribute values that are arrays are joined with spaces. Empty, null,
 * or false values are skipped. Attribute names are normalized to a safe
 * subset (alphanumerics, colon, underscore, hyphen).
 *
 * @param string $svg SVG markup.
 * @param array $attributes Map of attribute name to value.
 * @return string SVG markup with the attributes injected.
 * @since 1.0.0
 */
function themeasy_inject_svg_attributes( string $svg, array $attributes ): string {
  if ( empty( $attributes ) ) {
    return $svg;
  }

  if ( !preg_match( '/<svg\b([^>]*)>/i', $svg, $matches, PREG_OFFSET_CAPTURE ) ) {
    return $svg;
  }

  $svg_tag = $matches[0][0];
  $svg_offset = $matches[0][1];
  $existing_atts = $matches[1][0];
  $injected = '';

  foreach ( $attributes as $name => $value ) {
    if ( is_array( $value ) ) {
      $value = implode( ' ', $value );
    }
    if ( null === $value || false === $value || '' === $value ) {
      continue;
    }
    $value = (string) $value;

    $safe_name = preg_replace( '/[^a-z0-9:_-]/i', '', (string) $name );
    if ( '' === $safe_name ) {
      continue;
    }
    $escaped = esc_attr( $value );

    if ( 'class' === $safe_name ) {
      if ( false !== strpos( $existing_atts, 'class="' ) ) {
        $existing_atts = preg_replace_callback(
          '/class="([^"]*)"/',
          function ( $m ) use ( $escaped ) {
            $existing = trim( $m[1] );
            return 'class="' . ( '' === $existing ? $escaped : $existing . ' ' . $escaped ) . '"';
          },
          $existing_atts,
          1
        );
      } else {
        $injected .= ' class="' . $escaped . '"';
      }
      continue;
    }

    $pattern = '/\s' . preg_quote( $safe_name, '/' ) . '="[^"]*"/i';
    if ( preg_match( $pattern, $existing_atts ) ) {
      // Callback replacement: a plain replacement string would expand
      // $n/\n backreferences carried in the attribute value.
      $existing_atts = preg_replace_callback(
        $pattern,
        function () use ( $safe_name, $escaped ) {
          return ' ' . $safe_name . '="' . $escaped . '"';
        },
        $existing_atts,
        1
      );
    } else {
      $injected .= ' ' . $safe_name . '="' . $escaped . '"';
    }
  }

  $new_tag = '<svg' . $existing_atts . $injected . '>';

  return substr_replace( $svg, $new_tag, $svg_offset, strlen( $svg_tag ) );
}

/**
 * Merge a CSS class into the root <svg> element's class attribute.
 *
 * Thin wrapper around themeasy_inject_svg_attributes() preserved for
 * backwards compatibility. New callers should use the attributes helper
 * directly.
 *
 * @param string $svg SVG markup.
 * @param string $class Class string to append (already unescaped).
 * @return string SVG markup with the class merged (or original when empty).
 * @since 1.0.0
 */
function themeasy_inject_svg_class( string $svg, string $class ): string {
  if ( '' === $class ) {
    return $svg;
  }
  return themeasy_inject_svg_attributes( $svg, ['class' => $class] );
}

/**
 * Returns inline SVG content from a media library attachment ID.
 *
 * This function ensures that:
 * - The file is an SVG.
 * - It is located within the uploads folder.
 * - It removes unsafe elements such as <script> and inline JS.
 *
 * @param int $attachment_id Attachment ID.
 * @return string Sanitized inline SVG content or empty string on failure.
 * @since 1.0.0
 */
function themeasy_get_inline_svg_from_attachment( int $attachment_id ): string {
  $svg_url = wp_get_attachment_url( $attachment_id );

  if ( !$svg_url || !str_ends_with( $svg_url, '.svg' ) ) {
    return '';
  }

  $upload_dir = wp_get_upload_dir();
  $base_url = trailingslashit( $upload_dir['baseurl'] );
  $base_path = trailingslashit( $upload_dir['basedir'] );

  if ( str_starts_with( $svg_url, $base_url ) ) {
    $relative_path = str_replace( $base_url, '', $svg_url );
    $absolute_path = $base_path . $relative_path;

    if ( file_exists( $absolute_path ) ) {
      $svg = file_get_contents( $absolute_path );

      if ( false === $svg ) {
        return '';
      }

      return \Themeasy\Core\SVG_Sanitizer::sanitize_string( $svg );
    }
  }

  return '';
}

/**
 * Extracts a two-letter country code from a Polylang flag image URL.
 *
 * @param string $flag_url Full image URL (e.g., https://site.com/wp-content/.../flags/br.png).
 * @return string|null ISO 3166-1 alpha-2 country code or null if invalid.
 * @since 1.0.0
 */
function themeasy_get_flag_code_from_url( string $flag_url ): ?string {
  if ( empty( $flag_url ) ) {
    return null;
  }

  $basename = basename( $flag_url );
  $code = strtolower( pathinfo( $basename, PATHINFO_FILENAME ) );

  return preg_match( '/^[a-z]{2}$/', $code ) ? $code : null;
}

/**
 * Merge the icon-contract SVG normalization into caller attributes.
 *
 * Inline SVG icons must never rely on the file's intrinsic dimensions: an
 * <svg> without width/height stretches to 100% of its container, and fixed
 * pixel attributes fight the widget's size controls. The contract pins the
 * root element to width/height="1em" so the glyph tracks the icon wrapper's
 * font-size, while any CSS (widget rules or Elementor control output) still
 * overrides it — presentation attributes lose to every stylesheet rule.
 *
 * Mirrored by normalizeIconSvgAttributes() in themeasy-el-editor-helpers.
 * Caller-provided attributes win over the normalization defaults.
 *
 * @param array $attributes Caller attributes for the root <svg>.
 * @return array Attributes with the normalization defaults merged in.
 * @since 1.0.0
 */
function themeasy_get_icon_svg_normalization( array $attributes ): array {
  return array_merge( ['width' => '1em', 'height' => '1em'], $attributes );
}

/**
 * Returns the generated Themeasy Icons manifest.
 *
 * Generated in-repo by `npm run icons:build` (scripts/icons/build-icons.mjs)
 * into assets/media/svg/ty-manifest.php. Each library entry carries its
 * display label, the SOURCE DIR under assets/media/svg/ (premium libraries
 * use a __premium_only-suffixed dir the Free strip removes — consumers must
 * existence-check the dir), an optional label_icon for the picker tab, the
 * editor `preload` flag (only preloaded libraries enter the editor's inline
 * SVG blob; the rest lazy-fetch per icon), and the icon name list.
 *
 * Lives in core (not the Elementor module) because the render helper below
 * needs it wherever icons render.
 *
 * @return array{version?: string, libraries?: array<string, array{label: string, dir: string, label_icon?: string, preload?: bool, icons: string[]}>}
 * @since 1.0.0
 */
function themeasy_get_ty_icon_manifest(): array {
  static $manifest = null;

  if ( null !== $manifest ) {
    return $manifest;
  }

  $file = TMS_PATH . 'assets/media/svg/ty-manifest.php';
  $loaded = file_exists( $file ) ? include $file : null;
  $manifest = is_array( $loaded ) ? $loaded : [];

  return $manifest;
}

/**
 * Extracts the icon file name from a ty-* icon picker value.
 *
 * The Themeasy Icons libraries (ty-feather, ty-solar-*) register as Elementor
 * icon picker tabs, so the stored value is a CSS class string built by the
 * picker — "ty-solar-line-heart" (and, defensively, any "displayPrefix
 * prefix-name" multi-token form Elementor may produce). This helper keeps the
 * last token, strips the "{library}-" prefix, and normalizes the remainder to
 * a safe file-name subset. Mirrored by Themeasy.parseTyIconName() in JS.
 *
 * @param string $library Icon library slug (e.g. 'ty-feather').
 * @param string $value   Raw icon picker value.
 * @return string Icon file name without extension, or '' when unparsable.
 * @since 1.0.0
 */
function themeasy_parse_ty_icon_name( string $library, string $value ): string {
  $value = trim( $value );

  if ( '' === $value ) {
    return '';
  }

  $parts = preg_split( '/\s+/', $value );
  $token = end( $parts );
  $prefix = $library . '-';

  if ( str_starts_with( $token, $prefix ) ) {
    $token = substr( $token, strlen( $prefix ) );
  }

  return (string) preg_replace( '/[^a-z0-9_-]/i', '', $token );
}

/**
 * Icon libraries whose picked value renders as an inline SVG.
 *
 * The svg-only style controls (fill/stroke/thickness) condition on
 * 'icon[library]' being one of these — always call this helper in those
 * conditions, never hand-list libraries. Manifest-driven so new ty-*
 * libraries (including premium ones stripped from the Free build — harmless
 * surplus there, and correct after a premium-to-Free downgrade with saved
 * Solar icons) join automatically. The static fallback keeps the controls
 * alive if the manifest ever goes missing.
 *
 * @return string[]
 * @since 1.0.0
 */
function themeasy_svg_icon_condition_libraries(): array {
  static $libraries = null;

  if ( null !== $libraries ) {
    return $libraries;
  }

  $ty_keys = [];

  foreach ( array_keys( themeasy_get_ty_icon_manifest()['libraries'] ?? [] ) as $key ) {
    if ( str_starts_with( (string) $key, 'ty-' ) ) {
      $ty_keys[] = (string) $key;
    }
  }

  $libraries = array_merge( ['svg', 'themeasy-svg'], $ty_keys ?: ['ty-feather'] );

  return $libraries;
}

/**
 * Renders an icon from Elementor's Icons_Manager and returns it as HTML string.
 *
 * Also recognizes three library shapes that bypass Elementor's pipeline:
 *
 *   - "ty-*" (Themeasy Icons: ty-feather, ty-solar-*) — the picker stores a
 *     class-shaped value ("ty-solar-line-heart"); we resolve it to an inline
 *     SVG from the library's manifest dir under assets/media/svg/. The picker
 *     preview uses the generated mask CSS (editor-only); the frontend is
 *     always inline SVG (Solar Duotone's secondary tone is an opacity
 *     attribute, so a single currentColor paints both tones).
 *
 *   - "themeasy-svg" — legacy: resolves to an inline SVG from the plugin's
 *     assets/media/svg/<lib>/ directory without touching the Media Library.
 *     Values accepted: "arrow-right" (defaults to ty-feather/) or
 *     "animated/radiant-spin" (explicit library/name form; a "feather/" lib
 *     is shimmed to the renamed ty-feather/ dir).
 *
 *   - "svg" (uploaded SVG from the Media Library) — we read and sanitize
 *     the file ourselves so our Themeasy animation attributes (data-*) are
 *     preserved end-to-end. Elementor's Icons_Manager would otherwise cache
 *     a sanitized copy on the attachment that strips anything outside its
 *     own whitelist.
 *
 * All inline-SVG branches apply the icon-contract normalization (root svg
 * pinned to 1em; see themeasy_get_icon_svg_normalization()). The editor twin
 * is Themeasy.renderIconMarkup() — keep their output byte-identical.
 *
 * @param array $icon Icon array with keys 'library' and 'value'.
 * @param array $attributes Optional. Additional attributes for the icon element.
 * @return string Rendered icon HTML or empty string if invalid.
 * @since 1.0.0
 */
function themeasy_render_icon_html( array $icon, array $attributes = [] ): string {
  if ( empty( $icon['value'] ) ) {
    return '';
  }

  $library = $icon['library'] ?? '';
  $value = $icon['value'];

  // Themeasy Icons libraries (registered as picker tabs). The manifest maps
  // each library to its source dir (premium libraries resolve to their
  // __premium_only-suffixed dir; a stripped dir just renders '').
  if ( is_string( $library ) && str_starts_with( $library, 'ty-' ) && is_string( $value ) ) {
    $icon_name = themeasy_parse_ty_icon_name( $library, $value );
    $dir = themeasy_get_ty_icon_manifest()['libraries'][$library]['dir'] ?? $library;
    $svg = '' !== $icon_name ? themeasy_get_svg_icon( $dir, $icon_name ) : '';

    if ( '' === $svg ) {
      return '';
    }

    return themeasy_inject_svg_attributes( $svg, themeasy_get_icon_svg_normalization( $attributes ) );
  }

  if ( 'themeasy-svg' === $library && is_string( $value ) && '' !== $value ) {
    if ( false !== strpos( $value, '/' ) ) {
      [$svg_lib, $icon_name] = explode( '/', $value, 2 );
    } else {
      $svg_lib = 'ty-feather';
      $icon_name = $value;
    }

    // Legacy shim: the feather dir became ty-feather (2026-07-22); saved
    // explicit "feather/name" values keep resolving. Mirrored in JS.
    if ( 'feather' === $svg_lib ) {
      $svg_lib = 'ty-feather';
    }
    $svg = themeasy_get_svg_icon( $svg_lib, $icon_name );
    if ( '' === $svg ) {
      return '';
    }
    return themeasy_inject_svg_attributes( $svg, themeasy_get_icon_svg_normalization( $attributes ) );
  }

  // Uploaded SVG: render inline via our sanitizer so data-* animation
  // attributes survive. Elementor's own renderer caches a stripped copy.
  if ( 'svg' === $library && is_array( $value ) && !empty( $value['id'] ) ) {
    $svg = themeasy_get_inline_svg_from_attachment( (int) $value['id'] );
    if ( '' === $svg ) {
      return '';
    }
    return themeasy_inject_svg_attributes( $svg, themeasy_get_icon_svg_normalization( $attributes ) );
  }

  ob_start();
  \Elementor\Icons_Manager::render_icon( $icon, $attributes );
  return ob_get_clean();
}

/**
 * Build the separator markup placed between post meta items.
 *
 * Shared by the Post Grid and the Post Carousel, which render the same
 * template part. Mirrors the Post Meta widget's separator vocabulary so a
 * suite can match the card index to the single — the difference is the
 * default, which stays 'pipe' here because that is what the cards have
 * always drawn.
 *
 * @param array<string,mixed> $settings Widget settings.
 * @return string Rendered HTML, or empty string when no separator.
 * @since 1.0.0
 */
function themeasy_get_post_meta_separator_markup( array $settings ): string {
  $type = (string) ( $settings['separator_type'] ?? 'pipe' );

  if ( 'none' === $type ) {
    return '';
  }

  if ( 'icon' === $type ) {
    $icon = $settings['separator_icon'] ?? [];
    $icon_html = is_array( $icon ) ? themeasy_render_icon_html( $icon ) : '';

    if ( '' === $icon_html ) {
      return '';
    }

    return '<span class="tms-post-grid__sep" aria-hidden="true">' . $icon_html . '</span>';
  }

  $texts = [
    'slash' => '/',
    'chevron' => "\u{203A}",
    'dot' => "\u{2022}",
    'dash' => "\u{2014}",
    'pipe' => '|',
  ];

  $text = ( 'custom' === $type )
    ? (string) ( $settings['separator_custom'] ?? '|' )
    : ( $texts[$type] ?? '' );

  if ( '' === trim( $text ) ) {
    return '';
  }

  return sprintf(
    '<span class="tms-post-grid__sep" aria-hidden="true">%s</span>',
    esc_html( $text )
  );
}

/**
 * Build the prev/next arrow markup handed to paginate_links().
 *
 * Shared by every widget that exposes pagination icon controls (Post Grid,
 * Archive Results). The widget owns the wrapper span — the icon helper returns
 * the bare 1em glyph — so `.tms-pager__icon` in components.min.css can size and
 * colour it, and the Icon Size control has something to target. The glyph is
 * decorative, so the link carries its own screen-reader label: paginate_links()
 * emits a bare `<a>` and gives us no way to set an attribute on it.
 *
 * @param mixed  $icon     Elementor ICONS control value (array, or anything else when unset).
 * @param string $fallback Feather icon name used when the control was cleared.
 * @param string $label    Already-escaped screen-reader label for the link.
 * @return string Rendered HTML.
 * @since 1.0.0
 */
function themeasy_get_pager_arrow_markup( $icon, string $fallback, string $label ): string {
  $icon_html = themeasy_render_icon_html( is_array( $icon ) ? $icon : [] );

  // An emptied icon control must not cost the pager its prev/next affordance.
  if ( '' === $icon_html ) {
    $icon_html = themeasy_get_svg_icon( 'ty-feather', $fallback );
  }

  return sprintf(
    '<span class="tms-pager__icon" aria-hidden="true">%s</span><span class="screen-reader-text">%s</span>',
    $icon_html,
    $label
  );
}

// ==============================
// ELEMENTOR
// ==============================

/**
 * Returns true if Elementor is in edit mode.
 *
 * @since 1.0.0
 */
function themeasy_is_elementor_editor(): bool {
  // Free-baseline helper: guard against Elementor being inactive (its sister
  // themeasy_is_elementor_preview() does the same) so a null $instance never
  // fatals on a non-Elementor site.
  if ( !did_action( 'elementor/loaded' ) || !isset( \Elementor\Plugin::$instance->editor ) ) {
    return false;
  }

  return \Elementor\Plugin::$instance->editor->is_edit_mode();
}

/**
 * Check if current request is Elementor preview.
 *
 * @since 1.0.0
 */
function themeasy_is_elementor_preview(): bool {
  // Must be Elementor loaded.
  if ( !did_action( 'elementor/loaded' ) ) {
    return false;
  }

  // Elementor API (most reliable when available).
  if ( class_exists( Elementor\Plugin::class ) && isset( Elementor\Plugin::$instance->preview ) ) {
    if ( Elementor\Plugin::$instance->preview->is_preview_mode() ) {
      return true;
    }
  }

  // Fallback: Elementor preview URL usually contains elementor-preview=<post_id>.
  if ( isset( $_GET['elementor-preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    return true;
  }

  return false;
}

/**
 * Get Elementor templates filtered by template type.
 *
 * @package Themeasy
 * @since 1.0.0
 *
 * Pass an array to match several types at once. Block-level pickers should ask for
 * ['section', 'container']: with Elementor's Flexbox Container layout active, a
 * template saved from a container is stored as 'container', never 'section'.
 *
 * @param string|string[] $type Optional. Template type(s): section, container, page, popup, kit. Empty = all.
 * @return array List of templates with [ID => Title]
 */
function themeasy_get_elementor_templates_by_type( $type = '' ): array {
  $types = array_values( array_filter( array_map( 'sanitize_key', (array) $type ) ) );

  $args = [
    'post_type' => 'elementor_library',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
  ];

  if ( $types ) {
    $args['meta_query'] = [
      [
        'key' => '_elementor_template_type',
        'value' => $types,
        'compare' => 'IN',
      ],
    ];
  }

  $query = new \WP_Query( $args );
  $templates = [];

  // Raw post_title, not get_the_title(): the SELECT/SELECT2 controls escape option
  // labels themselves, so texturized entities (&#8211;) would print literally.
  if ( $query->have_posts() ) {
    foreach ( $query->posts as $template ) {
      $templates[$template->ID] = $template->post_title;
    }
  }

  return $templates;
}

/**
 * Renders an Elementor template by its ID using the frontend rendering engine.
 *
 * Useful for widgets, global sections, or dynamic templates via shortcode.
 *
 * @package Themeasy
 * @since 1.0.0
 *
 * @param int  $template_id The ID of the template.
 * @param bool $with_css    Whether to include CSS output. Default true.
 * @return string Rendered HTML content or empty string on failure.
 */
function themeasy_render_elementor_template( int $template_id, bool $with_css = true ): string {
  $template_id = absint( $template_id );

  if ( !$template_id ) {
    return '';
  }

  $post = get_post( $template_id );
  if ( !$post || $post->post_type !== 'elementor_library' ) {
    return '';
  }

  return \Elementor\Plugin::instance()->frontend->get_builder_content( $template_id, $with_css );
}

// ==============================
// THIRD-PARTY PLUGINS
// ==============================

/**
 * Retrieves a list of published Contact Form 7 forms for use in select fields.
 *
 * @package Themeasy
 * @since 1.0.0
 *
 * @return array Associative array of form IDs => titles.
 */
function themeasy_get_contact_form_7_forms(): array {
  $options = [
    '' => esc_html__( 'Select a Form', 'themeasy-lite' ),
  ];

  $forms = get_posts( [
    'post_type' => 'wpcf7_contact_form',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
    'fields' => 'ids',
  ] );

  if ( !empty( $forms ) ) {
    foreach ( $forms as $form_id ) {
      $title = get_the_title( $form_id );
      if ( $title ) {
        $options[$form_id] = esc_html( $title );
      }
    }
  } else {
    $options['none'] = esc_html__( 'No contact form found', 'themeasy-lite' );
  }

  return $options;
}
