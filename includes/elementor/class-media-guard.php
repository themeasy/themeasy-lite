<?php
/**
 * Media guard: every media value a Themeasy widget renders has a text URL.
 *
 * A media control (MEDIA, a GALLERY item, a MEDIA or GALLERY field of a
 * repeater) holds `['url' => string, 'id' => int|string, ...]`, and the widgets
 * read its `url` as text. A document written outside the editor can break that
 * shape: a value nested whole inside `url`
 * (`['url' => ['url' => …, 'id' => …, 'tms_placeholder' => …]]`) once ended the page
 * in a TypeError.
 * Before a Themeasy widget renders, the guard flattens a nested value and
 * empties any other `url` that is not text, so no widget meets one.
 *
 * A `url` that esc_url() would empty (a blocked scheme: javascript:, data:) is
 * no image too (backlog #546): the widget takes its own no-image branch instead
 * of printing an <img src=""> in an empty frame, and the front matches what
 * content_template() does with Themeasy.safeUrl(). An attachment keeps its file.
 *
 * This is the net under every way a document reaches a page.
 * tests/widget-media-guard-drift.php.
 *
 * @package Themeasy\Elementor
 * @since 1.0.0
 */

namespace Themeasy\Elementor;

use Elementor\Controls_Manager;
use Elementor\Element_Base;

defined( 'ABSPATH' ) || exit;

final class Media_Guard {
  /**
   * Before every other listener: a widget's display settings are parsed once,
   * at the first get_settings_for_display(), and a later fix would not reach
   * them.
   *
   * @var int
   */
  public const PRIORITY = -100;

  /**
   * How deep a nested value is unwrapped.
   *
   * @var int
   */
  private const MAX_DEPTH = 3;

  /**
   * Singleton instance.
   *
   * @var self|null
   */
  private static $instance = null;

  /**
   * The media settings of each widget type, by widget name.
   *
   * @var array<string,array<string,array{type: string, fields: array<string,string>}>>
   */
  private static $media_settings = [];

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
   * Hook the guard before every element render.
   *
   * @return void
   */
  public function init(): void {
    add_action( 'elementor/frontend/before_render', [$this, 'before_render'], self::PRIORITY );
  }

  /**
   * Repair the media settings of a Themeasy widget before it renders.
   *
   * @param Element_Base $element The element about to render.
   * @return void
   */
  public function before_render( Element_Base $element ): void {
    if ( 'widget' !== $element->get_type() || 0 !== strpos( (string) $element->get_name(), 'themeasy-' ) ) {
      return;
    }

    foreach ( self::media_settings( $element ) as $name => $setting ) {
      $value = $element->get_settings( $name );

      if ( null === $value ) {
        continue;
      }

      $fixed = self::repair( $value, $setting['type'], $setting['fields'] );

      if ( $fixed !== $value ) {
        $element->set_settings( $name, $fixed );
      }
    }
  }

  /**
   * One media value with a text `url`: a nested value is unwrapped (its inner
   * keys win, unless empty), and a `url` that is still not text becomes ''.
   *
   * @param mixed $value A media control value.
   * @return array<string,mixed>
   */
  public static function media_value( $value ): array {
    if ( !is_array( $value ) ) {
      return ['url' => '', 'id' => ''];
    }

    for ( $depth = 0; $depth < self::MAX_DEPTH && is_array( $value['url'] ?? null ); ++$depth ) {
      $inner = $value['url'];
      $value['url'] = $inner['url'] ?? '';

      foreach ( $inner as $key => $item ) {
        if ( 'url' !== $key && ( ( '' !== $item && null !== $item ) || !array_key_exists( $key, $value ) ) ) {
          $value[$key] = $item;
        }
      }
    }

    if ( array_key_exists( 'url', $value ) && !is_string( $value['url'] ) ) {
      $value['url'] = is_scalar( $value['url'] ) ? (string) $value['url'] : '';
    }

    return $value;
  }

  /**
   * One media value as a render may print it. Kept apart from media_value().
   *
   * @param mixed $value A media control value.
   * @return array<string,mixed>
   */
  private static function render_value( $value ): array {
    $value = self::media_value( $value );
    $url = $value['url'] ?? '';

    if ( '' === $url || '' !== esc_url_raw( $url ) ) {
      return $value;
    }

    $id = absint( $value['id'] ?? 0 );
    $value['url'] = $id ? (string) wp_get_attachment_url( $id ) : '';

    return $value;
  }

  /**
   * A setting with every media value in it repaired.
   *
   * @param mixed                $value  The setting.
   * @param string               $type   media, gallery or repeater.
   * @param array<string,string> $fields A repeater's media fields: name => media or gallery.
   * @return mixed
   */
  public static function repair( $value, string $type, array $fields = [] ) {
    if ( 'media' === $type ) {
      return self::render_value( $value );
    }

    if ( !is_array( $value ) ) {
      return $value;
    }

    foreach ( $value as $index => $item ) {
      if ( 'gallery' === $type ) {
        $value[$index] = self::render_value( $item );
        continue;
      }

      if ( !is_array( $item ) ) {
        continue;
      }

      foreach ( $fields as $field => $field_type ) {
        if ( array_key_exists( $field, $item ) ) {
          $item[$field] = self::repair( $item[$field], $field_type );
        }
      }

      $value[$index] = $item;
    }

    return $value;
  }

  /**
   * The media settings of a widget type, read once from its controls.
   *
   * @param Element_Base $element The widget.
   * @return array<string,array{type: string, fields: array<string,string>}>
   */
  private static function media_settings( Element_Base $element ): array {
    $name = (string) $element->get_name();

    if ( isset( self::$media_settings[$name] ) ) {
      return self::$media_settings[$name];
    }

    $types = [Controls_Manager::MEDIA => 'media', Controls_Manager::GALLERY => 'gallery'];
    $settings = [];

    foreach ( (array) $element->get_controls() as $key => $control ) {
      $type = (string) ( $control['type'] ?? '' );

      if ( isset( $types[$type] ) ) {
        $settings[(string) $key] = ['type' => $types[$type], 'fields' => []];
        continue;
      }

      if ( Controls_Manager::REPEATER !== $type ) {
        continue;
      }

      $fields = [];

      foreach ( (array) ( $control['fields'] ?? [] ) as $field_key => $field ) {
        $field_type = (string) ( $field['type'] ?? '' );

        if ( isset( $types[$field_type] ) ) {
          $fields[(string) ( $field['name'] ?? $field_key )] = $types[$field_type];
        }
      }

      if ( $fields ) {
        $settings[(string) $key] = ['type' => 'repeater', 'fields' => $fields];
      }
    }

    self::$media_settings[$name] = $settings;

    return $settings;
  }
}
