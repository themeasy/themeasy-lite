<?php
/**
 * SVG Sanitizer
 *
 * Render-time sanitization of SVG markup: a DOMDocument walk against a
 * whitelist of elements and attributes.
 *
 * @package Themeasy\Core
 * @since 1.0.0
 */

namespace Themeasy\Core;

defined( 'ABSPATH' ) || exit;

class SVG_Sanitizer {

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
      // The qualified name (xlink:href, xml:base): $attr->name is the local
      // part alone, so an `xlink:href` would pass the href check as `href`, and
      // removeAttribute() by that name would miss it. Removal goes by node.
      $attr_name = strtolower( $attr->nodeName );

      // Block all event handlers.
      if ( 0 === strpos( $attr_name, 'on' ) ) {
        $to_remove[] = $attr;
        continue;
      }

      // Reference attributes may only point at a same-document fragment (#id).
      // Anything else — javascript:, data:, absolute or protocol-relative URLs —
      // is stripped to block script execution and external resource references.
      if ( in_array( $attr_name, ['href', 'xlink:href'], true ) ) {
        if ( !preg_match( '/^\s*#/', (string) $attr->value ) ) {
          $to_remove[] = $attr;
          continue;
        }
      }

      // Allow any data-* attribute. They're safe metadata: no URIs, no script
      // execution.
      if ( 0 === strpos( $attr_name, 'data-' ) ) {
        continue;
      }

      if ( !isset( $allowed_attrs[$attr_name] ) ) {
        $to_remove[] = $attr;
        continue;
      }

      // Scrub inline CSS down to a safe property allowlist (drops url(),
      // expression(), @import, behavior, javascript: and unknown properties)
      // so the style attribute keeps fill/stroke/size declarations without
      // becoming an injection surface.
      if ( 'style' === $attr_name ) {
        $clean_style = self::sanitize_style( (string) $attr->value );

        if ( '' === $clean_style ) {
          $to_remove[] = $attr;
        } else {
          $to_update[$attr->nodeName] = $clean_style;
        }
      }
    }

    foreach ( $to_remove as $attr ) {
      $node->removeAttributeNode( $attr );
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

}
