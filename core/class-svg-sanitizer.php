<?php
/**
 * SVG Sanitizer
 *
 * Provides secure SVG upload support and render-time sanitization.
 * Sanitizes SVG content on upload using DOMDocument and restricts
 * uploads to administrators. Replaces the previous regex-based
 * approach with a proper DOM-walking whitelist.
 *
 * @package Themeasy\Core
 * @since 1.0.0
 */

namespace Themeasy\Core;

defined( 'ABSPATH' ) || exit;

class SVG_Sanitizer {
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
    if ( is_null( self::$instance ) ) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  /**
   * Initialize SVG upload and sanitization hooks.
   *
   * @return void
   */
  public function init(): void {
    add_filter( 'upload_mimes', [$this, 'register_svg_mime'] );
    add_filter( 'wp_check_filetype_and_ext', [$this, 'validate_svg_filetype'], 10, 4 );
    add_filter( 'wp_handle_upload_prefilter', [$this, 'sanitize_svg_upload'] );
    add_filter( 'wp_generate_attachment_metadata', [$this, 'skip_svg_subsizes'], 10, 2 );
  }

  /**
   * Returns the SVG element and attribute whitelist.
   *
   * Covers common elements exported by design tools (Figma, Illustrator,
   * Inkscape) while blocking dangerous content like scripts and event
   * handlers. Filterable via 'themeasy/svg/allowed_tags'.
   *
   * @return array Whitelist in wp_kses format.
   */
  public static function get_allowed_tags(): array {
    $common = [
      'id' => true,
      'class' => true,
      'style' => true,
      'transform' => true,
      'opacity' => true,
      'clip-path' => true,
      'mask' => true,
      'filter' => true,
    ];

    $fill_stroke = array_merge( $common, [
      'fill' => true,
      'fill-rule' => true,
      'fill-opacity' => true,
      'stroke' => true,
      'stroke-width' => true,
      'stroke-linecap' => true,
      'stroke-linejoin' => true,
      'stroke-dasharray' => true,
      'stroke-dashoffset' => true,
      'stroke-opacity' => true,
    ] );

    $tags = [
      'svg' => array_merge( $fill_stroke, [
        'xmlns' => true,
        'xmlns:xlink' => true,
        'width' => true,
        'height' => true,
        'viewbox' => true,
        'role' => true,
        'aria-hidden' => true,
        'aria-label' => true,
        'focusable' => true,
        'version' => true,
        'x' => true,
        'y' => true,
      ] ),
      'g' => $fill_stroke,
      'path' => array_merge( ['d' => true], $fill_stroke ),
      'circle' => array_merge( ['cx' => true, 'cy' => true, 'r' => true], $fill_stroke ),
      'rect' => array_merge( ['x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'ry' => true], $fill_stroke ),
      'line' => array_merge( ['x1' => true, 'y1' => true, 'x2' => true, 'y2' => true], $fill_stroke ),
      'polyline' => array_merge( ['points' => true], $fill_stroke ),
      'polygon' => array_merge( ['points' => true], $fill_stroke ),
      'ellipse' => array_merge( ['cx' => true, 'cy' => true, 'rx' => true, 'ry' => true], $fill_stroke ),
      'title' => [],
      'desc' => [],
      'defs' => [],
      'clippath' => ['id' => true, 'clippathunits' => true],
      'use' => array_merge( ['href' => true, 'xlink:href' => true, 'x' => true, 'y' => true, 'width' => true, 'height' => true], $common ),
      'symbol' => array_merge( ['viewbox' => true], $common ),
      'lineargradient' => ['id' => true, 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'gradientunits' => true, 'gradienttransform' => true],
      'radialgradient' => ['id' => true, 'cx' => true, 'cy' => true, 'r' => true, 'fx' => true, 'fy' => true, 'gradientunits' => true, 'gradienttransform' => true],
      'stop' => ['offset' => true, 'stop-color' => true, 'stop-opacity' => true, 'style' => true],
      'mask' => array_merge( ['id' => true, 'maskunits' => true, 'x' => true, 'y' => true, 'width' => true, 'height' => true], $fill_stroke ),
      'pattern' => ['id' => true, 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'patternunits' => true, 'patterntransform' => true, 'viewbox' => true],
      'text' => array_merge( ['x' => true, 'y' => true, 'dx' => true, 'dy' => true, 'text-anchor' => true, 'font-size' => true, 'font-family' => true, 'font-weight' => true], $fill_stroke ),
      'tspan' => ['x' => true, 'y' => true, 'dx' => true, 'dy' => true, 'fill' => true, 'font-size' => true],
    ];

    return apply_filters( 'themeasy/svg/allowed_tags', $tags );
  }

  /**
   * Register SVG as an allowed upload mime type for administrators.
   *
   * @param array $mimes Allowed mime types keyed by extension.
   * @return array
   */
  public function register_svg_mime( array $mimes ): array {
    if ( current_user_can( 'manage_options' ) ) {
      $mimes['svg'] = 'image/svg+xml';
    }

    return $mimes;
  }

  /**
   * Validate that a .svg file is actually valid SVG/XML.
   *
   * WordPress uses getimagesize() to validate image uploads, which
   * fails for SVG (XML-based, not bitmap). This filter parses the
   * file content to confirm it is valid XML with an <svg> root.
   *
   * @param array       $data     File data with 'ext', 'type', 'proper_filename'.
   * @param string      $file     Full path to the file.
   * @param string      $filename The name of the file.
   * @param array|null  $mimes    Allowed mime types.
   * @return array
   */
  public function validate_svg_filetype( array $data, string $file, string $filename, ?array $mimes ): array {
    $ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );

    if ( 'svg' !== $ext ) {
      return $data;
    }

    $content = @file_get_contents( $file );

    if ( false === $content || false === stripos( $content, '<svg' ) ) {
      return $data;
    }

    $prev = libxml_use_internal_errors( true );
    $doc = new \DOMDocument();
    $ok = $doc->loadXML( $content );
    libxml_clear_errors();
    libxml_use_internal_errors( $prev );

    if ( !$ok || !$doc->documentElement || 'svg' !== strtolower( $doc->documentElement->tagName ) ) {
      return $data;
    }

    $data['ext'] = 'svg';
    $data['type'] = 'image/svg+xml';

    return $data;
  }

  /**
   * Sanitize SVG content on upload.
   *
   * Parses the uploaded file with DOMDocument, walks the DOM tree to
   * remove disallowed elements and attributes, then rewrites the temp
   * file with clean content. Rejects files that are not valid XML or
   * do not have an <svg> root element.
   *
   * @param array $file Upload data array with 'tmp_name', 'name', 'type'.
   * @return array Original array or array with 'error' key on failure.
   */
  public function sanitize_svg_upload( array $file ): array {
    $ext = strtolower( pathinfo( $file['name'] ?? '', PATHINFO_EXTENSION ) );

    if ( 'svg' !== $ext ) {
      return $file;
    }

    if ( !current_user_can( 'manage_options' ) ) {
      $file['error'] = __( 'Sorry, you are not allowed to upload SVG files.', 'themeasy-lite' );
      return $file;
    }

    $content = @file_get_contents( $file['tmp_name'] );

    if ( false === $content || '' === trim( $content ) ) {
      $file['error'] = __( 'The uploaded SVG file is empty or unreadable.', 'themeasy-lite' );
      return $file;
    }

    // Strip BOM.
    $content = preg_replace( '/^\xEF\xBB\xBF/', '', $content );

    if ( false === stripos( $content, '<svg' ) ) {
      $file['error'] = __( 'The uploaded file does not appear to be a valid SVG.', 'themeasy-lite' );
      return $file;
    }

    $prev = libxml_use_internal_errors( true );
    $doc = new \DOMDocument();
    $ok = $doc->loadXML( $content );
    libxml_clear_errors();
    libxml_use_internal_errors( $prev );

    if ( !$ok || !$doc->documentElement || 'svg' !== strtolower( $doc->documentElement->tagName ) ) {
      $file['error'] = __( 'The uploaded file contains invalid XML.', 'themeasy-lite' );
      return $file;
    }

    // Walk DOM tree and strip disallowed content.
    self::sanitize_node( $doc->documentElement );

    // Remove processing instructions, comments, and doctype from root.
    foreach ( iterator_to_array( $doc->childNodes ) as $child ) {
      if (
        $child instanceof \DOMProcessingInstruction
        || $child instanceof \DOMComment
        || $child instanceof \DOMDocumentType
      ) {
        $doc->removeChild( $child );
      }
    }

    // Write sanitized content back to temp file.
    $clean = $doc->saveXML( $doc->documentElement );
    $written = file_put_contents( $file['tmp_name'], $clean );

    if ( false === $written ) {
      $file['error'] = __( 'Could not save sanitized SVG file.', 'themeasy-lite' );
    }

    return $file;
  }

  /**
   * Recursively sanitize a DOM node against the whitelist.
   *
   * Removes disallowed child elements, event handler attributes, and any
   * attributes not in the whitelist; restricts href/xlink:href to local
   * fragment (#id) references; and scrubs inline style to a safe CSS allowlist.
   *
   * @param \DOMElement $node The node to sanitize.
   * @return void
   */
  private static function sanitize_node( \DOMElement $node ): void {
    $allowed = self::get_allowed_tags();
    $children = iterator_to_array( $node->childNodes );

    foreach ( $children as $child ) {
      if ( $child instanceof \DOMElement ) {
        $child_tag = strtolower( $child->tagName );

        if ( !isset( $allowed[$child_tag] ) ) {
          $node->removeChild( $child );
        } else {
          self::sanitize_node( $child );
        }
      } elseif (
        $child instanceof \DOMComment
        || $child instanceof \DOMProcessingInstruction
      ) {
        $node->removeChild( $child );
      }
    }

    // Remove or scrub disallowed attributes.
    $tag = strtolower( $node->tagName );
    $allowed_attrs = $allowed[$tag] ?? [];
    $to_remove = [];
    $to_update = [];

    foreach ( $node->attributes as $attr ) {
      $attr_name = strtolower( $attr->name );

      // Block all event handlers.
      if ( str_starts_with( $attr_name, 'on' ) ) {
        $to_remove[] = $attr->name;
        continue;
      }

      // Reference attributes may only point at a same-document fragment (#id).
      // Anything else — javascript:, data:, absolute or protocol-relative URLs —
      // is stripped to block script execution and external resource references.
      if ( in_array( $attr_name, ['href', 'xlink:href'], true ) ) {
        if ( !preg_match( '/^\s*#/', (string) $attr->value ) ) {
          $to_remove[] = $attr->name;
          continue;
        }
      }

      // Allow any data-* attribute. They're safe metadata — no URIs, no
      // script execution — and the Themeasy animation engine reads them
      // (data-animation, data-delay, data-duration, data-loop) to wire
      // SVG effects at runtime.
      if ( str_starts_with( $attr_name, 'data-' ) ) {
        continue;
      }

      if ( !isset( $allowed_attrs[$attr_name] ) ) {
        $to_remove[] = $attr->name;
        continue;
      }

      // Scrub inline CSS down to a safe property allowlist (drops url(),
      // expression(), @import, behavior, javascript: and unknown properties)
      // so the style attribute keeps fill/stroke/size declarations without
      // becoming an injection surface.
      if ( 'style' === $attr_name ) {
        $clean_style = self::sanitize_style( (string) $attr->value );

        if ( '' === $clean_style ) {
          $to_remove[] = $attr->name;
        } else {
          $to_update[$attr->name] = $clean_style;
        }
      }
    }

    foreach ( $to_remove as $name ) {
      $node->removeAttribute( $name );
    }

    foreach ( $to_update as $name => $value ) {
      $node->setAttribute( $name, $value );
    }
  }

  /**
   * Scrub an inline style attribute to a safe CSS-property allowlist.
   *
   * Keeps the presentation properties icons rely on (fill, stroke,
   * stroke-width, width, height, color, opacity, transform, CSS custom
   * properties, …) and drops any declaration whose value carries an active
   * payload (url(), expression(), @import, behavior, javascript:) or whose
   * property is not allowlisted.
   *
   * @param string $value Raw style attribute value.
   * @return string Sanitized declarations (empty when nothing survives).
   */
  private static function sanitize_style( string $value ): string {
    $allowed = [
      'fill', 'fill-opacity', 'fill-rule',
      'stroke', 'stroke-width', 'stroke-linecap', 'stroke-linejoin',
      'stroke-dasharray', 'stroke-dashoffset', 'stroke-opacity', 'stroke-miterlimit',
      'color', 'opacity', 'width', 'height',
      'transform', 'transform-origin', 'transform-box',
      'display', 'visibility', 'overflow', 'vector-effect', 'paint-order',
      'mix-blend-mode', 'isolation', 'stop-color', 'stop-opacity',
      'font-size', 'font-family', 'font-weight', 'text-anchor', 'letter-spacing',
    ];

    $clean = [];

    foreach ( explode( ';', $value ) as $declaration ) {
      if ( false === strpos( $declaration, ':' ) ) {
        continue;
      }

      [$prop, $val] = explode( ':', $declaration, 2 );
      $prop = strtolower( trim( $prop ) );
      $val = trim( $val );

      if ( '' === $prop || '' === $val ) {
        continue;
      }

      // Allow CSS custom properties (--token) and the safe presentation set only.
      if ( 0 !== strpos( $prop, '--' ) && !in_array( $prop, $allowed, true ) ) {
        continue;
      }

      // Reject any value carrying an active payload.
      if ( preg_match( '/url\s*\(|expression\s*\(|@import|behavior\s*:|javascript\s*:|[<>]/i', $val ) ) {
        continue;
      }

      $clean[] = $prop . ':' . $val;
    }

    return implode( ';', $clean );
  }

  /**
   * Sanitize an SVG string for safe inline rendering.
   *
   * Uses the same DOM walker as the upload path, which preserves
   * case-sensitive attribute names (viewBox, gradientUnits, …) and the
   * data-* attributes our animation engine reads. wp_kses wasn't suitable:
   * it lowercases attributes and offers no wildcard for data-*.
   *
   * @param string $svg Raw SVG markup.
   * @return string Sanitized SVG markup (empty on parse failure).
   */
  public static function sanitize_string( string $svg ): string {
    if ( '' === trim( $svg ) ) {
      return '';
    }

    // Strip BOM and bail if there's no <svg> root to parse.
    $svg = preg_replace( '/^\xEF\xBB\xBF/', '', $svg );
    if ( false === stripos( $svg, '<svg' ) ) {
      return '';
    }

    $prev = libxml_use_internal_errors( true );
    $doc = new \DOMDocument();
    $ok = $doc->loadXML( $svg );
    libxml_clear_errors();
    libxml_use_internal_errors( $prev );

    if ( !$ok || !$doc->documentElement || 'svg' !== strtolower( $doc->documentElement->tagName ) ) {
      return '';
    }

    self::sanitize_node( $doc->documentElement );

    foreach ( iterator_to_array( $doc->childNodes ) as $child ) {
      if (
        $child instanceof \DOMProcessingInstruction
        || $child instanceof \DOMComment
        || $child instanceof \DOMDocumentType
      ) {
        $doc->removeChild( $child );
      }
    }

    $result = $doc->saveXML( $doc->documentElement );

    return is_string( $result ) ? $result : '';
  }

  /**
   * Skip image subsize generation for SVG uploads.
   *
   * WordPress tries to create thumbnails using GD/Imagick after upload,
   * which fails for SVG files since they are XML-based, not bitmap.
   * Returns basic metadata with dimensions extracted from the file's
   * viewBox or width/height attributes instead.
   *
   * @param array $metadata      Attachment metadata.
   * @param int   $attachment_id Attachment post ID.
   * @return array
   */
  public function skip_svg_subsizes( array $metadata, int $attachment_id ): array {
    $file = get_attached_file( $attachment_id );

    if ( 'svg' !== pathinfo( $file, PATHINFO_EXTENSION ) ) {
      return $metadata;
    }

    $svg = @simplexml_load_file( $file );

    if ( false === $svg ) {
      return $metadata;
    }

    $attr = $svg->attributes();
    $width = 0;
    $height = 0;

    if ( isset( $attr->viewBox ) ) {
      $parts = explode( ' ', (string) $attr->viewBox );
      $width = isset( $parts[2] ) ? (int) round( (float) $parts[2] ) : 0;
      $height = isset( $parts[3] ) ? (int) round( (float) $parts[3] ) : 0;
    }

    if ( 0 === $width && isset( $attr->width ) ) {
      $width = (int) round( (float) $attr->width );
    }

    if ( 0 === $height && isset( $attr->height ) ) {
      $height = (int) round( (float) $attr->height );
    }

    return [
      'width' => $width,
      'height' => $height,
      'file' => _wp_relative_upload_path( $file ),
      'sizes' => [],
    ];
  }
}
