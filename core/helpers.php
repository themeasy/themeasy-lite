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
  return defined( 'THEMEASY_VER' ) ? THEMEASY_VER : '1.0.0';
}

// ==============================
// MOTION
// ==============================

/**
 * Register the Motion section of a widget's Advanced tab: a note that says
 * where the animations live.
 *
 * Themeasy Motion (entrance, hover, text and loop animations, scroll text,
 * draw-on) runs on GSAP, which the build hosted on wordpress.org neither ships
 * nor loads: the GSAP license is not GPL-compatible (backlog #253). So the
 * Advanced tab says where the effects live instead of silently lacking them.
 *
 * @param \Elementor\Controls_Stack $element The widget registering its controls.
 * @return void
 */
function themeasy_register_motion_upsell_section( \Elementor\Controls_Stack $element ): void {
  $element->start_controls_section(
    'themeasy_motion_upsell_section',
    [
      'label' => esc_html__( 'Motion', 'themeasy-lite' ),
      'tab' => \Elementor\Controls_Manager::TAB_ADVANCED,
    ]
  );

  $element->add_control(
    'themeasy_motion_upsell',
    [
      'type' => \Elementor\Controls_Manager::RAW_HTML,
      'raw' => sprintf(
        '%1$s <a href="%2$s" target="_blank" rel="noopener">%3$s</a>',
        esc_html__( 'Animations and hover effects for this widget come with the paid plans, starting with Themeasy Widgets.', 'themeasy-lite' ),
        esc_url( admin_url( 'admin.php?page=themeasy' ) ),
        esc_html__( 'Compare Plans', 'themeasy-lite' )
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
 * `global` argument of a Typography group whose default look is a Text Display
 * preset (backlog #273).
 *
 * The `tms-display-1..8` presets are Kit globals that only the Themeasy
 * plugin's Typography_Sync writes, on a save of its settings on a Themeasy
 * theme. The setup wizard and the demo import save none, so a new Themeasy
 * site has no preset until that first save, and a Kit keeps them after a theme
 * switch or a downgrade (the sync never deletes). So the active Kit is asked,
 * once per request. A default that points at a missing global shows in the
 * editor as an active global named "undefined", with nothing to pick (measured
 * on Hello, 2026-10-01).
 *
 * An empty array behaves exactly like no `global` argument: Elementor merges it
 * into the popover's own `['active' => true]`.
 *
 * @param int $level Text Display level, 1 (the largest) to 8.
 * @return array
 * @since 1.0.0
 */
function themeasy_display_typography_global( int $level ): array {
  static $kit_ids = null;

  if ( null === $kit_ids ) {
    $kit_ids = [];
    $plugin = class_exists( '\Elementor\Plugin' ) ? \Elementor\Plugin::$instance : null;
    $kit = isset( $plugin->kits_manager ) ? $plugin->kits_manager->get_active_kit() : null;
    $custom = $kit ? $kit->get_settings( 'custom_typography' ) : [];

    foreach ( is_array( $custom ) ? $custom : [] as $entry ) {
      if ( is_array( $entry ) && !empty( $entry['_id'] ) ) {
        $kit_ids[(string) $entry['_id']] = true;
      }
    }
  }

  $id = 'tms-display-' . $level;

  return isset( $kit_ids[$id] ) ? ['default' => 'globals/typography?id=' . $id] : [];
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

  $extra_attr = themeasy_html_attributes( $attributes );

  printf(
    '<img src="%s" alt="%s"%s loading="lazy" decoding="async"%s />',
    esc_url( $src ),
    esc_attr( $alt ),
    $class ? ' class="' . esc_attr( $class ) . '"' : '',
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- values escaped by themeasy_html_attributes().
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

  $extra_attr = themeasy_html_attributes( $attributes );

  printf(
    '<img src="%s" alt="%s"%s%s%s%s%s%s%s decoding="async" />',
    esc_url( $src ),
    esc_attr( $alt ),
    $class ? ' class="' . esc_attr( $class ) . '"' : '',
    $width  ? sprintf( ' width="%d"', (int) $width ) : '',
    $height ? sprintf( ' height="%d"', (int) $height ) : '',
    $srcset ? sprintf( ' srcset="%s"', esc_attr( $srcset ) ) : '',
    $sizes  ? sprintf( ' sizes="%s"', esc_attr( $sizes ) ) : '',
    $loading_priority === 'high' ? ' fetchpriority="high"' : ' loading="lazy"',
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- values escaped by themeasy_html_attributes().
    $extra_attr ? ' ' . $extra_attr : ''
  );
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

    $base_path = THEMEASY_PATH . 'assets/media/svg/' . $library;
    $file_path = $subfolder
      ? "{$base_path}/{$subfolder}/{$icon}.svg"
      : "{$base_path}/{$icon}.svg";

    if ( file_exists( $file_path ) ) {
      // Ensure the resolved path stays within the expected SVG directory.
      $resolved = realpath( $file_path );
      $base_real = realpath( THEMEASY_PATH . 'assets/media/svg/' );

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

  if ( !$svg_url || '.svg' !== substr( $svg_url, -4 ) ) {
    return '';
  }

  $upload_dir = wp_get_upload_dir();
  $base_url = trailingslashit( $upload_dir['baseurl'] );
  $base_path = trailingslashit( $upload_dir['basedir'] );

  if ( 0 === strncmp( $svg_url, $base_url, strlen( $base_url ) ) ) {
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
 * display label, the SOURCE DIR under assets/media/svg/ (consumers must
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

  $file = THEMEASY_PATH . 'assets/media/svg/ty-manifest.php';
  $loaded = file_exists( $file ) ? include $file : null;
  $manifest = is_array( $loaded ) ? $loaded : [];

  return $manifest;
}

/**
 * Extracts the icon file name from a ty-* icon picker value.
 *
 * The Themeasy Icons libraries (ty-*) register as Elementor
 * icon picker tabs, so the stored value is a CSS class string built by the
 * picker — "ty-feather-heart" (and, defensively, any "displayPrefix
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

  if ( 0 === strncmp( $token, $prefix, strlen( $prefix ) ) ) {
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
 * libraries join automatically. The static fallback keeps the controls alive
 * if the manifest ever goes missing.
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
    if ( 0 === strpos( (string) $key, 'ty-' ) ) {
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
 *   - "ty-*" (Themeasy Icons) — the picker stores a class-shaped value
 *     ("ty-feather-heart"); we resolve it to an inline SVG from the library's
 *     manifest dir under assets/media/svg/. The picker preview uses the
 *     generated mask CSS (editor-only); the frontend is always inline SVG.
 *
 *   - "themeasy-svg" — legacy: resolves to an inline SVG from the plugin's
 *     assets/media/svg/<lib>/ directory without touching the Media Library.
 *     Values accepted: "arrow-right" (defaults to ty-feather/) or
 *     "ty-feather/heart" (explicit library/name form; a "feather/" lib is
 *     shimmed to the renamed ty-feather/ dir).
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
  // each library to its source dir; a library whose dir is not on disk just
  // renders ''.
  if ( is_string( $library ) && 0 === strpos( $library, 'ty-' ) && is_string( $value ) ) {
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
