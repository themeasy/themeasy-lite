<?php
/**
 * Themeasy Settings
 *
 * Core registration, sanitization, and typed access to plugin settings.
 * Tabs contribute fields via the 'themeasy/admin/field_schema' filter.
 *
 * @package Themeasy\Admin
 * @since 1.0.0
 */

namespace Themeasy\Admin;

defined( 'ABSPATH' ) || exit;

class Settings {
  /* ---------------------------------------------------------------------
   * Initialization
   * ------------------------------------------------------------------- */

  /**
   * Hook settings registration.
   */
  public static function init(): void {
    \add_action( 'admin_init', [__CLASS__, 'register_settings'], 10 );
  }

  /**
   * Register the single-array option with WordPress.
   */
  public static function register_settings(): void {
    \register_setting(
      'themeasy_settings_group',
      'themeasy_settings',
      [
        'type' => 'array',
        'show_in_rest' => false,
        'capability' => 'manage_options',
        'sanitize_callback' => [__CLASS__, 'sanitize_settings'],
      ]
    );
  }

  /**
   * Expose the option name (for notices).
   */
  public static function option_name(): string {
    return 'themeasy_settings';
  }

  /* ---------------------------------------------------------------------
   * Schema
   * ------------------------------------------------------------------- */

  /**
   * Return the consolidated field schema.
   *
   * Tabs should extend this via:
   *   add_filter( 'themeasy/admin/field_schema', [ Tab_Class::class, 'extend_schema' ] );
   *
   * Each field entry supports (optional) keys:
   *  - type: string|int|float|bool|color|url|email|select|multiselect|radio|media_id
   *  - default: mixed
   *  - choices: array (for select/radio/multiselect)
   *  - min, max (int/float)
   *  - required (bool) or allow_empty (bool, default true)
   *  - min_length, max_length (int) for string-like
   *  - truncate_on_max (bool) => cut instead of error on max_length
   *  - trim (bool, default true for strings)
   *  - label (string) => friendly label in messages
   *
   * @return array<string, array<string, mixed>>
   */
  public static function get_schema(): array {
    static $cached = null;
    if ( \is_array( $cached ) ) {
      return $cached;
    }
    $schema = [];
    $schema = \apply_filters( 'themeasy/admin/field_schema', $schema );
    $cached = \is_array( $schema ) ? $schema : [];
    return $cached;
  }

  /* ---------------------------------------------------------------------
   * Sanitization
   * ------------------------------------------------------------------- */

  /**
   * Sanitize a single value according to the field schema.
   *
   * @param mixed               $value Raw submitted value
   * @param array<string,mixed> $field Field schema (type, default, choices, ...)
   * @param string              $key   Setting key
   * @return mixed
   */
  private static function sanitize_by_schema( $value, array $field, string $key ) {
    $type = $field['type'] ?? 'string';

    switch ( $type ) {
      case 'bool':
        if ( \is_bool( $value ) ) { return $value ? 1 : 0; }
        if ( \is_numeric( $value ) ) { return ( (int) $value ) === 1 ? 1 : 0; }
        if ( \is_string( $value ) ) {
          $v = \strtolower( $value );
          return \in_array( $v, ['1', 'true', 'yes', 'on'], true ) ? 1 : 0;
        }
        return 0;
      case 'int':
        // Fields that opt into allow_empty keep '' as "unset" (e.g. inherit
        // a theme default) instead of collapsing to a min-clamped 0.
        if ( '' === $value && !empty( $field['allow_empty'] ) ) { return ''; }
        $v = \is_numeric( $value ) ? (int) $value : 0;
        if ( isset( $field['min'] ) ) { $v = \max( (int) $field['min'], $v ); }
        if ( isset( $field['max'] ) ) { $v = \min( (int) $field['max'], $v ); }
        return $v;
      case 'float':
        if ( '' === $value && !empty( $field['allow_empty'] ) ) { return ''; }
        $v = \is_numeric( $value ) ? (float) $value : 0.0;
        if ( isset( $field['min'] ) ) { $v = \max( (float) $field['min'], $v ); }
        if ( isset( $field['max'] ) ) { $v = \min( (float) $field['max'], $v ); }
        return $v;
      case 'color':
        // Accept hex with optional alpha (#rgb, #rgba, #rrggbb, #rrggbbaa)
        // and CSS color functions: rgb(), rgba(), hsl(), hsla().
        // WP's sanitize_hex_color() only validates 3 or 6 digit hex, so it
        // can't be used directly when Coloris is configured with `alpha: true`
        // and `formatToggle: true`.
        $candidate = \trim( (string) $value );
        if ( $candidate === '' ) {
          return $field['default'] ?? '';
        }

        // Hex (3/4/6/8 digits) → normalize to lowercase.
        if ( \preg_match( '/^#(?:[0-9a-fA-F]{3,4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $candidate ) ) {
          return \strtolower( $candidate );
        }

        // rgb()/rgba() → keep original casing/spacing; reject malformed input.
        if ( \preg_match( '/^rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*(?:,\s*(?:0|1|0?\.\d+|\d{1,3}%)\s*)?\)$/', $candidate ) ) {
          return $candidate;
        }

        // hsl()/hsla() with optional angle unit and percent/decimal alpha.
        if ( \preg_match( '/^hsla?\(\s*-?\d+(?:\.\d+)?(?:deg|rad|grad|turn)?\s*,\s*\d{1,3}%\s*,\s*\d{1,3}%\s*(?:,\s*(?:0|1|0?\.\d+|\d{1,3}%)\s*)?\)$/', $candidate ) ) {
          return $candidate;
        }

        return $field['default'] ?? '';
      case 'url':
        return \esc_url_raw( (string) $value );
      case 'email':
        $email = \sanitize_email( (string) $value );
        return $email ?: '';
      case 'select':
      case 'radio':
        $raw_choices = (array) ( $field['choices'] ?? [] );

        // If choices is an indexed list, allowed = its values.
        // If choices is associative, allowed = its keys.
        $is_list = array_keys( $raw_choices ) === range( 0, count( $raw_choices ) - 1 );
        $allowed = $is_list
          ? array_map( 'strval', array_values( $raw_choices ) )
          : array_map( 'strval', array_keys( $raw_choices ) );

        $v = (string) $value;
        return in_array( $v, $allowed, true )
          ? $v
          : ( $field['default'] ?? ( $allowed[0] ?? '' ) );

      case 'multiselect':
        $choices = isset( $field['choices'] ) ? \array_map( 'strval', \array_keys( (array) $field['choices'] ) ) : [];
        $vals = \is_array( $value ) ? \array_map( 'strval', $value ) : [];
        return \array_values( \array_intersect( $vals, $choices ) );
      case 'media_id':
        $id = \absint( $value );
        if ( $id && \get_post_type( $id ) !== 'attachment' ) {
          $id = 0;
        }
        return $id;
      case 'code_css':
      case 'code_js':
        // Accept raw code (admins only); normalize line endings & trim BOM.
        $s = is_string( $value ) ? (string) $value : '';
        $s = str_replace( ["\r\n", "\r"], "\n", $s );
        // Remove UTF-8 BOM if present.
        if ( substr( $s, 0, 3 ) === "\xEF\xBB\xBF" ) { $s = substr( $s, 3 ); }
        return $s;
      case 'code_html':
        // Raw HTML (may include <script>, <noscript>, <meta>, etc.)
        // Do NOT kses here; restrict via capability and kill-switch.
        $s = is_string( $value ) ? (string) $value : '';
        $s = str_replace( ["\r\n", "\r"], "\n", $s );
        if ( substr( $s, 0, 3 ) === "\xEF\xBB\xBF" ) { $s = substr( $s, 3 ); }
        return $s;
      case 'string':
      default:
        return \is_string( $value ) ? \sanitize_text_field( $value ) : \sanitize_text_field( (string) $value );
    }
  }

  /**
   * Sanitize and merge submitted values with existing ones.
   *
   * - Uses wp_unslash() because sanitize_callback receives slashed input.
   * - Validates against the consolidated schema (from Tabs via filter).
   * - Unknown keys are ignored (warned) only if the schema is present.
   * - Merges with saved values so that non-submitted tabs aren't reset.
   *
   * @param mixed $input Raw input from $_POST (slashed)
   * @return array
   */
  public static function sanitize_settings( $input ): array {
    $saved = \get_option( 'themeasy_settings', [] );
    $input = \is_array( $input ) ? $input : [];
    $input = \wp_unslash( $input );

    $schema = self::get_schema();
    $clean = [];

    $has_schema = !empty( $schema ) && \is_array( $schema );

    foreach ( $input as $key => $raw ) {
      $k = \is_string( $key ) ? \sanitize_key( $key ) : $key;

      if ( $has_schema && isset( $schema[$k] ) ) {
        $field = $schema[$k];

        // 1) Type-level sanitization
        $clean[$k] = self::sanitize_by_schema( $raw, $field, (string) $k );

        // 2) Normalization (trim for strings)
        if ( \is_string( $clean[$k] ) ) {
          $do_trim = array_key_exists( 'trim', $field ) ? (bool) $field['trim'] : true;
          if ( $do_trim ) {
            $clean[$k] = \trim( $clean[$k] );
          }
        }

        // 3) Cross-field validation (required / empty / lengths)
        $label = self::field_label( (string) $k, $field );
        $is_required = !empty( $field['required'] ) || ( isset( $field['allow_empty'] ) && $field['allow_empty'] === false );

        // required / non-empty
        if ( $is_required && self::is_logically_empty( $clean[$k], $field ) ) {
          Admin_Notices::error(
            \sprintf(
              /* translators: %s: field label */
              \esc_html__( 'The field "%s" is required.', 'themeasy-lite' ),
              \esc_html( $label )
            ),
            "themeasy_required_{$k}"
          );
          if ( isset( $saved[$k] ) ) { $clean[$k] = $saved[$k]; } else { unset( $clean[$k] ); }
          continue;
        }

        // string lengths
        if ( \is_string( $clean[$k] ) ) {
          if ( isset( $field['min_length'] ) ) {
            $min = (int) $field['min_length'];
            if ( self::strlen_mb( $clean[$k] ) < $min ) {
              Admin_Notices::error(
                \sprintf(
                  /* translators: 1: field label, 2: minimum character count */
                  \esc_html__( 'The field "%1$s" must be at least %2$d characters.', 'themeasy-lite' ),
                  \esc_html( $label ),
                  $min
                ),
                "themeasy_min_length_{$k}"
              );
              if ( isset( $saved[$k] ) ) { $clean[$k] = $saved[$k]; } else { unset( $clean[$k] ); }
              continue;
            }
          }
          if ( isset( $field['max_length'] ) ) {
            $max = (int) $field['max_length'];
            if ( self::strlen_mb( $clean[$k] ) > $max ) {
              if ( !empty( $field['truncate_on_max'] ) ) {
                // Soft enforcement: truncate and warn
                $clean[$k] = \function_exists( 'mb_substr' ) ? \mb_substr( $clean[$k], 0, $max ) : \substr( $clean[$k], 0, $max );
                Admin_Notices::warning(
                  \sprintf(
                    /* translators: 1: field label, 2: maximum character count */
                    \esc_html__( 'The field "%1$s" exceeded %2$d characters and was truncated.', 'themeasy-lite' ),
                    \esc_html( $label ),
                    $max
                  ),
                  "themeasy_truncated_{$k}"
                );
              } else {
                // Hard enforcement: revert
                Admin_Notices::error(
                  \sprintf(
                    /* translators: 1: field label, 2: maximum character count */
                    \esc_html__( 'The field "%1$s" must be at most %2$d characters.', 'themeasy-lite' ),
                    \esc_html( $label ),
                    $max
                  ),
                  "themeasy_max_length_{$k}"
                );
                if ( isset( $saved[$k] ) ) { $clean[$k] = $saved[$k]; } else { unset( $clean[$k] ); }
                continue;
              }
            }
          }
        }

        // 4) Example: flag invalid color (submitted ≠ accepted == default)
        if ( ( $field['type'] ?? '' ) === 'color' ) {
          $submitted = (string) $raw;
          $accepted = (string) $clean[$k];
          $default = (string) ( $field['default'] ?? '' );
          if (
            $submitted !== '' &&
            !\hash_equals( \ltrim( \strtolower( $submitted ), '#' ), \ltrim( \strtolower( $accepted ), '#' ) ) &&
            $accepted === $default
          ) {
            Admin_Notices::error(
              \sprintf(
                /* translators: %s: field label */
                \esc_html__( 'Invalid color for "%s". Reverted to default.', 'themeasy-lite' ),
                \esc_html( $label )
              ),
              "themeasy_invalid_color_{$k}"
            );
          }
        }

        // 5) Flag a discarded choice (submitted value absent from the options).
        // The form UI can only ever submit an option it rendered, so this fires
        // on the import path, where a payload written on another site may carry
        // a choice this site does not offer (a Google family with no API key
        // configured, a custom font that lives only on the source site). Falling
        // back to the default silently reads as a clean import while quietly
        // rewriting the value.
        if ( \in_array( $field['type'] ?? '', ['select', 'radio'], true ) && \is_scalar( $raw ) ) {
          $submitted = (string) $raw;
          if ( '' !== $submitted && $submitted !== (string) $clean[$k] ) {
            Admin_Notices::warning(
              \sprintf(
                /* translators: 1: field label, 2: the submitted value that was not among the field's options */
                \esc_html__( 'The field "%1$s" does not offer the value "%2$s". It was reset to the default.', 'themeasy-lite' ),
                \esc_html( $label ),
                \esc_html( $submitted )
              ),
              "themeasy_invalid_choice_{$k}"
            );
          }
        }
      } elseif ( $has_schema ) {
        // Unknown key (warn and ignore)
        Admin_Notices::warning(
          \sprintf(
            /* translators: %s: setting key */
            \esc_html__( 'Unknown setting "%s" was ignored.', 'themeasy-lite' ),
            \esc_html( (string) $k )
          ),
          "themeasy_unknown_key_{$k}"
        );
      } else {
        // Rare race: no schema yet — accept safe text so the save flow isn't blocked
        $clean[$k] = \is_scalar( $raw ) ? \sanitize_text_field( (string) $raw ) : '';
      }
    }

    // Allow external filters to adjust sanitized values before merging.
    $clean = \apply_filters( 'themeasy/admin/sanitized_input', $clean, $saved, $schema );

    // Merge to avoid resetting values from non-submitted tabs.
    $merged = \wp_parse_args( $clean, \is_array( $saved ) ? $saved : [] );

    return \apply_filters( 'themeasy/admin/settings_merged', $merged, $clean, $saved, $schema );
  }

  /* ---------------------------------------------------------------------
   * Helpers (empty detection, labels, mb-safe length)
   * ------------------------------------------------------------------- */

  /**
   * Determine if a value should be considered "empty" for a given field.
   * - Strings: empty after optional trim
   * - Arrays: empty() check
   * - media_id: 0 (or falsy) is empty
   * - Numeric/bool (non-media): 0/false are NOT empty (users may set them intentionally)
   */
  private static function is_logically_empty( $value, array $field ): bool {
    $type = $field['type'] ?? 'string';
    $trim = array_key_exists( 'trim', $field ) ? (bool) $field['trim'] : true;

    // media_id: treat 0 as empty (no attachment selected)
    if ( 'media_id' === $type ) {
      return (int) $value === 0;
    }

    if ( \is_string( $value ) ) {
      $v = $trim ? \trim( $value ) : $value;
      return $v === '';
    }

    if ( \is_array( $value ) ) {
      return empty( $value );
    }

    // For numeric/bool (non-media), 0/false are valid values and not "empty".
    if ( \is_int( $value ) || \is_float( $value ) || \is_bool( $value ) ) {
      return false;
    }

    // Fallback
    return empty( $value );
  }

  /**
   * Get the field label for a given key.
   */
  private static function field_label( string $key, array $field ): string {
    if ( isset( $field['label'] ) && \is_string( $field['label'] ) && $field['label'] !== '' ) {
      return $field['label'];
    }
    return $key;
  }

  /**
   * Get the length of a string (multibyte safe).
   */
  private static function strlen_mb( string $s ): int {
    return \function_exists( 'mb_strlen' ) ? (int) \mb_strlen( $s ) : (int) \strlen( $s );
  }

  /* ---------------------------------------------------------------------
   * Accessors
   * ------------------------------------------------------------------- */

  /**
   * Get all settings.
   */
  public static function all(): array {
    $opts = \get_option( 'themeasy_settings', [] );
    return \is_array( $opts ) ? $opts : [];
  }

  /**
   * Replace all settings.
   */
  public static function replace( array $settings ): void {
    \update_option( 'themeasy_settings', $settings );
  }

  /**
   * Get a specific setting value.
   */
  public static function get( string $key, $default = null ) {
    $options = self::all();
    return $options[$key] ?? $default;
  }

  /**
   * Update a specific setting value.
   */
  public static function update( string $key, $value ): void {
    $options = self::all();
    $options[$key] = $value;
    \update_option( 'themeasy_settings', $options );
  }

  /* ---------------------------------------------------------------------
   * Typed getters
   * ------------------------------------------------------------------- */

  /**
   * Get a specific setting value as a string.
   */
  public static function get_string( string $key, string $default = '' ): string {
    $val = self::get( $key, $default );
    return \is_string( $val ) ? $val : (string) $val;
  }

  /**
   * Get a specific setting value as an integer.
   */
  public static function get_int( string $key, int $default = 0 ): int {
    $val = self::get( $key, $default );
    return \is_numeric( $val ) ? (int) $val : $default;
  }

  /**
   * Get a specific setting value as a float.
   */
  public static function get_float( string $key, float $default = 0.0 ): float {
    $val = self::get( $key, $default );
    return \is_numeric( $val ) ? (float) $val : $default;
  }

  /**
   * Get a specific setting value as a boolean.
   */
  public static function get_bool( string $key, bool $default = false ): bool {
    $val = self::get( $key, $default ? 1 : 0 );
    if ( \is_bool( $val ) ) { return $val; }
    if ( \is_numeric( $val ) ) { return ( (int) $val ) === 1; }
    if ( \is_string( $val ) ) {
      $lower = \strtolower( $val );
      if ( \in_array( $lower, ['1', 'true', 'yes', 'on'], true ) ) { return true; }
      if ( \in_array( $lower, ['0', 'false', 'no', 'off'], true ) ) { return false; }
    }
    return (bool) $val;
  }

  /**
   * Get a specific setting value as an array.
   */
  public static function get_array( string $key, bool $sanitize_strings = false ): array {
    $val = self::get( $key, [] );
    if ( !\is_array( $val ) ) { return []; }
    if ( !$sanitize_strings ) { return $val; }
    $out = [];
    foreach ( $val as $k => $v ) {
      $nk = \is_string( $k ) ? \sanitize_key( $k ) : $k;
      $out[$nk] = \is_string( $v ) ? \sanitize_text_field( $v ) : $v;
    }
    return $out;
  }

  /* ---------------------------------------------------------------------
   * Admin UI helpers
   * ------------------------------------------------------------------- */

  /**
   * Return inline SVG markup from a file within the plugin admin assets.
   *
   * @param string $filename SVG filename inside assets/svg/, e.g. 'moon.svg'.
   * @return string SVG markup or empty string on failure.
   */
  public static function themeasy_inline_svg( string $filename ): string {
    // Resolve the admin SVG directory. This shared Settings class physically
    // lives in admin/, but its inline-SVG assets (logo, dark/light toggle,
    // checklist icons) ship from the premium admin tree, so resolve against
    // TMS_ADMIN_PATH when the premium admin loader has defined it; fall back to
    // this file's own admin/ directory otherwise (the stripped Free build,
    // which has no callers for this helper).
    $admin_dir = defined( 'TMS_ADMIN_PATH' ) ? TMS_ADMIN_PATH : dirname( __DIR__ );

    // Build an absolute filesystem path for the asset.
    // Use wp_normalize_path() so the strpos() traversal check works on Windows,
    // where realpath() returns backslashes but trailingslashit() appends '/'.
    $base_dir = realpath( trailingslashit( $admin_dir ) . 'assets/svg' );
    if ( !$base_dir ) {
      return '';
    }
    $base_dir = trailingslashit( wp_normalize_path( $base_dir ) );

    $path = realpath( $base_dir . ltrim( $filename, '/' ) );
    if ( !$path ) {
      return '';
    }
    $path = wp_normalize_path( $path );
    if ( strpos( $path, $base_dir ) !== 0 ) {
      return ''; // Block path traversal
    }

    if ( !is_readable( $path ) ) {
      return '';
    }

    $svg = file_get_contents( $path );

    // Strip XML declaration for cleaner inline output
    if ( str_starts_with( $svg, '<?xml' ) ) {
      $svg = preg_replace( '/^\s*<\?xml[^>]*>\s*/', '', $svg );
    }

    return $svg ?: '';
  }
}
