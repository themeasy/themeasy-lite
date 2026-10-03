<?php
/**
 * Themeasy Elementor Widget: Divider (SVG Separator)
 *
 * Decorative separator with three skins:
 *   - line:  classic rule (solid/dashed/dotted/double or gradient fill), with
 *            optional fade-out ends.
 *   - dots:  editorial dot rule with configurable dot size and gap.
 *   - shape: stretched SVG silhouette (curve, wave, zigzag, triangle,
 *            mountains, tilt, arrow, clouds) with horizontal/vertical flip,
 *            painted either filled (a CSS mask closed down to the bottom edge)
 *            or as a line (an inline <svg> stroke tracing the open contour).
 *
 * Line and dots are the two "rule" skins and share the same structure: either
 * horizontal or vertical, rendered as a single continuous rule or as two
 * segments flanking an optional centered element (icon, text, or badge) with
 * start/center/end positioning.
 *
 * Designed for section breaks, form dividers (e.g. "OR"), editorial accents
 * and full-bleed section separators. Everything is CSS/SVG and fully visible
 * with no JS; the optional draw-on animation of the shape line is the single
 * feature that needs GSAP, and its absence only skips the motion.
 *
 * @package Themeasy
 * @since 1.0.0
 */

namespace Themeasy\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;
use Themeasy\Core\Entitlement;

/**
 * Responsible for registering controls and rendering the Divider widget.
 */
class Divider extends Widget_Base {
  /**
   * Closing edge appended to every open contour to turn it into a silhouette.
   *
   * Declaring it once is what keeps the filled mask and the stroked line on
   * exactly the same geometry — see get_shape_contours() / get_shape_paths().
   */
  private const SHAPE_CLOSING_EDGE = ' L1200,120 L0,120 Z';

  /**
   * Widest divider a tiled Line SVG is built to cover, in CSS pixels.
   *
   * The repeat count is baked into the markup, so it is sized against a
   * generous full-bleed 4K viewport rather than the container's real width
   * (which only exists at paint time). The surplus is clipped.
   */
  private const SHAPE_TILE_REFERENCE_WIDTH = 3840;

  /** Hard ceiling on those repeats, so a tiny Pattern Width cannot flood the DOM. */
  private const SHAPE_TILE_MAX_REPEATS = 24;

  public function get_name() {
    return 'themeasy-divider';
  }

  public function get_title() {
    return esc_html__( 'Divider', 'themeasy-lite' );
  }

  public function get_icon() {
    return 'eicon-divider';
  }

  public function get_custom_help_url() {
    return 'https://themeasy.co/help-center';
  }

  public function get_categories() {
    return ['themeasy_elements_category', 'themeasy_content_category'];
  }

  public function get_keywords() {
    return ['themeasy', 'divider', 'separator', 'svg', 'shape', 'wave', 'zigzag', 'hr', 'rule'];
  }

  public function get_style_depends() {
    return ['themeasy-shared', 'themeasy-divider'];
  }

  // The Divider's only script is the draw-on, and that is Pro (GSAP): the
  // Free build has no such handle, and its stroke is drawn server-side.
  public function get_script_depends() {
    return Entitlement::can_use_widgets() ? ['themeasy-divider'] : [];
  }

  /**
   * Allowed shape types. Shape key => label.
   *
   * @return array<string,string>
   */
  private function get_shape_options() {
    return [
      'curve' => esc_html__( 'Curve', 'themeasy-lite' ),
      'wave' => esc_html__( 'Wave', 'themeasy-lite' ),
      'zigzag' => esc_html__( 'Zig Zag', 'themeasy-lite' ),
      'triangle' => esc_html__( 'Triangle', 'themeasy-lite' ),
      'mountains' => esc_html__( 'Mountains', 'themeasy-lite' ),
      'tilt' => esc_html__( 'Tilt', 'themeasy-lite' ),
      'arrow' => esc_html__( 'Arrow', 'themeasy-lite' ),
      'clouds' => esc_html__( 'Clouds', 'themeasy-lite' ),
    ];
  }

  /**
   * Shape keys whose motif is a seamless horizontal pattern.
   *
   * These repeat at their authored width instead of stretching; every other
   * shape is a single silhouette meant to span the full width, where
   * stretching is the correct behavior.
   *
   * @return array<int,string> Shape keys.
   */
  private function get_tiling_shapes() {
    return ['wave', 'clouds', 'zigzag'];
  }

  /**
   * Build the CSS mask value that paints a FILLED shape.
   *
   * The silhouette ships as a data URI mask rather than an inline <svg> so the
   * tiling shapes can repeat horizontally at a fixed width while still
   * stretching to the authored height. Color still comes from currentColor
   * (via background-color), so shape_color and dark mode are untouched. The
   * Line style takes the other route entirely — a real inline <svg> stroke.
   *
   * @param string $shape Shape key (see get_shape_options()).
   * @return string CSS url() value.
   */
  private function get_shape_mask( $shape ) {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">'
      . '<path d="' . $this->get_shape_path( $shape ) . '"/></svg>';

    return 'url("data:image/svg+xml,' . rawurlencode( $svg ) . '")';
  }

  /**
   * Return the SVG path definition for a shape type.
   *
   * Paths share a common viewBox (0 0 1200 120) and are painted through a
   * mask with preserveAspectRatio="none": a single silhouette stretches to
   * the wrapper width, a tiling motif repeats at its own width instead.
   *
   * @param string $shape Shape key (see get_shape_options()).
   * @return string SVG path "d" attribute.
   */
  private function get_shape_path( $shape ) {
    $paths = $this->get_shape_paths();

    return $paths[$shape] ?? $paths['curve'];
  }

  /**
   * Open contour of every shape type — the motif line itself, nothing else.
   *
   * These are the primary geometry: the filled silhouettes are built from them
   * (see get_shape_paths()), so the mask and the stroked line can never drift
   * apart. Every contour lives in the shared 0 0 1200 120 viewBox, and the
   * three tiling motifs start and end at the same y so a repeat is seamless.
   *
   * Single source of truth for both render() and content_template().
   *
   * @return array<string,string> Shape key => SVG path "d" attribute.
   */
  private function get_shape_contours() {
    return [
      'curve' => 'M0,0 C400,80 800,80 1200,0',
      'wave' => 'M0,60 C200,10 400,110 600,60 C800,10 1000,110 1200,60',
      'zigzag' => 'M0,0 L150,80 L300,0 L450,80 L600,0 L750,80 L900,0 L1050,80 L1200,0',
      'triangle' => 'M0,0 L600,80 L1200,0',
      'mountains' => 'M0,60 L120,20 L240,70 L360,10 L500,80 L650,15 L820,75 L980,25 L1100,70 L1200,40',
      'tilt' => 'M0,0 L1200,80',
      'arrow' => 'M0,0 L540,0 L600,80 L660,0 L1200,0',
      'clouds' => 'M0,60 Q100,10 200,60 T400,60 T600,60 T800,60 T1000,60 T1200,60',
    ];
  }

  /**
   * Closed silhouette of every shape type — the contour plus the bottom edge.
   *
   * @return array<string,string> Shape key => SVG path "d" attribute.
   */
  private function get_shape_paths() {
    $paths = [];

    foreach ( $this->get_shape_contours() as $shape => $contour ) {
      $paths[$shape] = $contour . self::SHAPE_CLOSING_EDGE;
    }

    return $paths;
  }

  /**
   * How many copies of a tiling contour the Line SVG carries.
   *
   * A stroked <svg> cannot repeat itself the way a CSS mask does, so the motif
   * is drawn N times inside one viewBox that is N tiles wide; the wrapper then
   * clips whatever the divider is not wide enough to show. N is sized against
   * the SMALLEST Pattern Width the user configured across breakpoints, because
   * the markup is shared by all three and a narrower tile needs more copies.
   *
   * @param array $settings Widget settings (get_settings_for_display()).
   * @return int Repeat count, at least 1.
   */
  private function get_shape_repeats( array $settings ) {
    $tiles = [];

    foreach ( ['shape_tile_width', 'shape_tile_width_tablet', 'shape_tile_width_mobile'] as $key ) {
      $size = $settings[$key]['size'] ?? '';

      if ( is_numeric( $size ) && (float) $size > 0 ) {
        $tiles[] = (float) $size;
      }
    }

    $tile = $tiles ? min( $tiles ) : 1200.0;
    $repeats = (int) ceil( self::SHAPE_TILE_REFERENCE_WIDTH / $tile );

    return max( 1, min( self::SHAPE_TILE_MAX_REPEATS, $repeats ) );
  }

  /**
   * Register widget customization controls.
   *
   * @return void
   */
  protected function register_controls() {
    // ------------------------------------------------------------------------
    // Content section: Layout
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'divider_layout_section',
        [
          'label' => esc_html__( 'Layout', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
    );

      $this->add_control(
        'skin',
        [
          'label' => esc_html__( 'Skin', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'line',
          'options' => [
            'line' => esc_html__( 'Line', 'themeasy-lite' ),
            'dots' => esc_html__( 'Dots', 'themeasy-lite' ),
            'shape' => esc_html__( 'SVG Shape', 'themeasy-lite' ),
          ],
          'description' => esc_html__( 'Choose between a classic rule, a dotted rule (both take an optional center element) or a decorative SVG silhouette.', 'themeasy-lite' ),
        ]
      );

      $this->add_control(
        'divider_orientation',
        [
          'label' => esc_html__( 'Orientation', 'themeasy-lite' ),
          'type' => Controls_Manager::CHOOSE,
          'options' => [
            'horizontal' => [
              'title' => esc_html__( 'Horizontal', 'themeasy-lite' ),
              'icon' => 'eicon-ellipsis-h',
            ],
            'vertical' => [
              'title' => esc_html__( 'Vertical', 'themeasy-lite' ),
              'icon' => 'eicon-ellipsis-v',
            ],
          ],
          'default' => 'horizontal',
          'toggle' => false,
          'condition' => [
            'skin' => ['line', 'dots'],
          ],
          'description' => esc_html__( 'A short vertical rule separates inline blocks. The SVG Shape skin is horizontal only.', 'themeasy-lite' ),
        ]
      );

      $this->add_responsive_control(
        'divider_alignment',
        [
          'label' => esc_html__( 'Alignment', 'themeasy-lite' ),
          'type' => Controls_Manager::CHOOSE,
          'options' => [
            'flex-start' => [
              'title' => esc_html__( 'Left', 'themeasy-lite' ),
              'icon' => 'eicon-h-align-left',
            ],
            'center' => [
              'title' => esc_html__( 'Center', 'themeasy-lite' ),
              'icon' => 'eicon-h-align-center',
            ],
            'flex-end' => [
              'title' => esc_html__( 'Right', 'themeasy-lite' ),
              'icon' => 'eicon-h-align-right',
            ],
          ],
          'default' => 'center',
          'selectors' => [
            '{{WRAPPER}} .tms-divider' => 'justify-content: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'divider_width',
        [
          'label' => esc_html__( 'Width', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['%', 'px', 'em', 'rem', 'vw'],
          'range' => [
            '%' => ['min' => 10, 'max' => 100, 'step' => 1],
            'px' => ['min' => 40, 'max' => 2000, 'step' => 1],
          ],
          'default' => ['unit' => '%', 'size' => 100],
          'selectors' => [
            '{{WRAPPER}} .tms-divider__inner' => 'width: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'divider_orientation' => 'horizontal',
          ],
        ]
      );

      // The vertical rule needs its own length: a column flex item with no
      // height collapses to zero, so this control is what makes it visible.
      $this->add_responsive_control(
        'divider_height',
        [
          'label' => esc_html__( 'Height', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'em', 'rem', 'vh'],
          'range' => [
            'px' => ['min' => 8, 'max' => 400, 'step' => 1],
            'em' => ['min' => 0.5, 'max' => 30, 'step' => 0.1],
            'rem' => ['min' => 0.5, 'max' => 30, 'step' => 0.1],
            'vh' => ['min' => 1, 'max' => 50, 'step' => 1],
          ],
          'default' => ['unit' => 'px', 'size' => 40],
          'selectors' => [
            '{{WRAPPER}} .tms-divider__inner' => 'height: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'skin' => ['line', 'dots'],
            'divider_orientation' => 'vertical',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Center Element
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'divider_element_section',
        [
          'label' => esc_html__( 'Center Element', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
          'condition' => [
            'skin' => ['line', 'dots'],
          ],
        ]
    );

      $this->add_control(
        'show_element',
        [
          'label' => esc_html__( 'Show Element', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Show', 'themeasy-lite' ),
          'label_off' => esc_html__( 'Hide', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => '',
          'description' => esc_html__( 'Display an icon or text between two line segments (e.g. "OR").', 'themeasy-lite' ),
        ]
      );

      $this->add_control(
        'element_type',
        [
          'label' => esc_html__( 'Element Type', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'text',
          'options' => [
            'icon' => esc_html__( 'Icon', 'themeasy-lite' ),
            'text' => esc_html__( 'Text', 'themeasy-lite' ),
          ],
          'condition' => [
            'show_element' => 'yes',
          ],
        ]
      );

      $this->add_control(
        'element_icon',
        [
          'label' => esc_html__( 'Icon', 'themeasy-lite' ),
          'type' => Controls_Manager::ICONS,
          'default' => [
            'value' => 'ty-feather-star',
            'library' => 'ty-feather',
          ],
          'condition' => [
            'show_element' => 'yes',
            'element_type' => 'icon',
          ],
        ]
      );

      $this->add_control(
        'element_text',
        [
          'label' => esc_html__( 'Text', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'label_block' => true,
          'default' => __( 'OR', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'OR', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
          'condition' => [
            'show_element' => 'yes',
            'element_type' => 'text',
          ],
        ]
      );

      $this->add_control(
        'element_position',
        [
          'label' => esc_html__( 'Element Position', 'themeasy-lite' ),
          'type' => Controls_Manager::CHOOSE,
          'options' => [
            'start' => [
              'title' => esc_html__( 'Left', 'themeasy-lite' ),
              'icon' => 'eicon-h-align-left',
            ],
            'center' => [
              'title' => esc_html__( 'Center', 'themeasy-lite' ),
              'icon' => 'eicon-h-align-center',
            ],
            'end' => [
              'title' => esc_html__( 'Right', 'themeasy-lite' ),
              'icon' => 'eicon-h-align-right',
            ],
          ],
          'default' => 'center',
          'toggle' => false,
          'condition' => [
            'show_element' => 'yes',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Shape
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'divider_shape_section',
        [
          'label' => esc_html__( 'Shape', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
          'condition' => [
            'skin' => 'shape',
          ],
        ]
    );

      $this->add_control(
        'shape_type',
        [
          'label' => esc_html__( 'Shape', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'curve',
          'options' => $this->get_shape_options(),
        ]
      );

      $this->add_control(
        'shape_style',
        [
          'label' => esc_html__( 'Shape Style', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'filled',
          'options' => [
            'filled' => esc_html__( 'Filled', 'themeasy-lite' ),
            'line' => esc_html__( 'Line', 'themeasy-lite' ),
          ],
          'description' => esc_html__( 'Filled closes the motif down to the bottom edge, like a section silhouette. Line traces it as a rule whose two edges both follow the shape.', 'themeasy-lite' ),
        ]
      );

      $this->add_control(
        'shape_flip_horizontal',
        [
          'label' => esc_html__( 'Flip Horizontal', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => '',
        ]
      );

      $this->add_control(
        'shape_flip_vertical',
        [
          'label' => esc_html__( 'Flip Vertical', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => '',
          'description' => esc_html__( 'Flip the silhouette vertically (useful when placing the divider above a section).', 'themeasy-lite' ),
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Wrapper (Spacing)
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'divider_wrapper_styles_section',
        [
          'label' => esc_html__( 'Wrapper', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      $this->add_responsive_control(
        'divider_padding',
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'em', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-divider' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Line
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'divider_line_styles_section',
        [
          'label' => esc_html__( 'Line', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'skin' => 'line',
          ],
        ]
    );

      $this->add_control(
        'line_color_type',
        [
          'label' => esc_html__( 'Color Type', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'solid',
          'options' => [
            'solid' => esc_html__( 'Solid', 'themeasy-lite' ),
            'gradient' => esc_html__( 'Gradient', 'themeasy-lite' ),
          ],
          'description' => esc_html__( 'A gradient is painted as a background fill, so Line Style (dashed, dotted, double) does not apply and its control is hidden.', 'themeasy-lite' ),
        ]
      );

      $this->add_control(
        'line_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-divider__line' => '--tms-divider-line-color: {{VALUE}};',
          ],
          'condition' => [
            'line_color_type' => 'solid',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Background::get_type(),
        [
          'name' => 'line_gradient',
          'label' => esc_html__( 'Gradient', 'themeasy-lite' ),
          'types' => ['gradient'],
          'selector' => '{{WRAPPER}} .tms-divider--line-gradient .tms-divider__line',
          'condition' => [
            'line_color_type' => 'gradient',
          ],
        ]
      );

      $this->add_control(
        'line_style',
        [
          'label' => esc_html__( 'Line Style', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'solid',
          'options' => [
            'solid' => esc_html__( 'Solid', 'themeasy-lite' ),
            'dashed' => esc_html__( 'Dashed', 'themeasy-lite' ),
            'dotted' => esc_html__( 'Dotted', 'themeasy-lite' ),
            'double' => esc_html__( 'Double', 'themeasy-lite' ),
          ],
          'condition' => [
            'line_color_type' => 'solid',
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-divider__line' => '--tms-divider-line-style: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'line_weight',
        [
          'label' => esc_html__( 'Thickness', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => ['min' => 1, 'max' => 40, 'step' => 1],
          ],
          'default' => ['unit' => 'px', 'size' => 2],
          'selectors' => [
            '{{WRAPPER}} .tms-divider__line' => '--tms-divider-line-weight: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'line_fade_edges',
        [
          'label' => esc_html__( 'Fade Edges', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => '',
          'separator' => 'before',
          'description' => esc_html__( 'Dissolve the rule into transparency at both ends. With a center element only the outer ends fade — the rule stays solid where it meets the element.', 'themeasy-lite' ),
        ]
      );

      $this->add_responsive_control(
        'line_fade_length',
        [
          'label' => esc_html__( 'Fade Length', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['%', 'px'],
          'range' => [
            '%' => ['min' => 1, 'max' => 50, 'step' => 1],
            'px' => ['min' => 4, 'max' => 400, 'step' => 1],
          ],
          'default' => ['unit' => '%', 'size' => 20],
          'selectors' => [
            '{{WRAPPER}} .tms-divider' => '--tms-divider-fade-length: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'line_fade_edges' => 'yes',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Dots
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'divider_dots_styles_section',
        [
          'label' => esc_html__( 'Dots', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'skin' => 'dots',
          ],
        ]
    );

      $this->add_control(
        'dots_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-divider__line' => '--tms-divider-dot-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'dots_size',
        [
          'label' => esc_html__( 'Dot Size', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => ['min' => 1, 'max' => 40, 'step' => 1],
          ],
          'default' => ['unit' => 'px', 'size' => 4],
          'selectors' => [
            '{{WRAPPER}} .tms-divider__line' => '--tms-divider-dot-size: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'dots_gap',
        [
          'label' => esc_html__( 'Dot Gap', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => ['min' => 0, 'max' => 80, 'step' => 1],
          ],
          'default' => ['unit' => 'px', 'size' => 12],
          'selectors' => [
            '{{WRAPPER}} .tms-divider__line' => '--tms-divider-dot-gap: {{SIZE}}{{UNIT}};',
          ],
          'description' => esc_html__( 'Empty space between dots. The dots sit on a repeating tile, so a fractional dot can appear at the end of a rule whose length is not a whole number of tiles.', 'themeasy-lite' ),
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Shape
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'divider_shape_styles_section',
        [
          'label' => esc_html__( 'Shape', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'skin' => 'shape',
          ],
        ]
    );

      $this->add_control(
        'shape_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-divider__shape' => '--tms-divider-shape-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'shape_height',
        [
          'label' => esc_html__( 'Height', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'vh', 'rem'],
          'range' => [
            'px' => ['min' => 10, 'max' => 500, 'step' => 1],
            'vh' => ['min' => 2, 'max' => 60, 'step' => 1],
            'rem' => ['min' => 0.5, 'max' => 30, 'step' => 0.1],
          ],
          'default' => ['unit' => 'px', 'size' => 80],
          'selectors' => [
            '{{WRAPPER}} .tms-divider__shape' => '--tms-divider-shape-height: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'shape_weight',
        [
          'label' => esc_html__( 'Thickness', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => ['min' => 1, 'max' => 60, 'step' => 1],
          ],
          'default' => ['unit' => 'px', 'size' => 6],
          'description' => esc_html__( 'Thickness of the stroke. The divider still measures exactly its Height — the line is inset by half a thickness at both ends of that box so nothing is clipped.', 'themeasy-lite' ),
          'condition' => [
            'shape_style' => 'line',
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-divider__shape' => '--tms-divider-shape-weight: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      // Only a real stroke has ends to shape — the filled silhouette has none.
      $this->add_control(
        'shape_linecap',
        [
          'label' => esc_html__( 'Line Cap', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'butt',
          'options' => [
            'butt' => esc_html__( 'Flat', 'themeasy-lite' ),
            'round' => esc_html__( 'Round', 'themeasy-lite' ),
            'square' => esc_html__( 'Square', 'themeasy-lite' ),
          ],
          'description' => esc_html__( 'How the two ends of the line are finished. Round and Square extend past the divider, so they only show when the line does not run edge to edge.', 'themeasy-lite' ),
          'condition' => [
            'shape_style' => 'line',
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-divider__shape' => '--tms-divider-shape-linecap: {{VALUE}};',
          ],
        ]
      );

      // A drawn stroke has to be ONE continuous path, so it cannot tile: the
      // motif stretches instead and this control would be a dead knob. Elementor
      // only offers the draw switch on the line style, so "drawing" alone is
      // enough to exclude it.
      $this->add_responsive_control(
        'shape_tile_width',
        [
          'label' => esc_html__( 'Pattern Width', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => ['min' => 200, 'max' => 2400, 'step' => 10],
          ],
          'default' => ['unit' => 'px', 'size' => 1200],
          'description' => esc_html__( 'Width of one repetition of the motif. The pattern repeats across the divider instead of being stretched, so it keeps its proportions on any screen width.', 'themeasy-lite' ),
          'conditions' => [
            'terms' => [
              ['name' => 'shape_type', 'operator' => 'in', 'value' => $this->get_tiling_shapes()],
              ['name' => 'shape_draw', 'operator' => '!==', 'value' => 'yes'],
            ],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-divider__shape' => '--tms-divider-shape-tile: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'shape_opacity',
        [
          'label' => esc_html__( 'Opacity', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['u'],
          'range' => ['u' => ['min' => 0, 'max' => 1, 'step' => 0.05]],
          'default' => ['unit' => 'u', 'size' => 1],
          'selectors' => [
            '{{WRAPPER}} .tms-divider__shape' => 'opacity: {{SIZE}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Element (Icon / Text / Badge)
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'divider_element_styles_section',
        [
          'label' => esc_html__( 'Element', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'skin' => ['line', 'dots'],
            'show_element' => 'yes',
          ],
        ]
    );

      $this->add_control(
        'element_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-divider__element' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'element_icon_fill_color',
        [
          'label' => esc_html__( 'Icon Fill Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-divider__element svg' => 'fill: {{VALUE}};',
          ],
          'conditions' => [
            'terms' => [
              ['name' => 'element_type', 'operator' => '==', 'value' => 'icon'],
              ['name' => 'element_icon[library]', 'operator' => 'in', 'value' => themeasy_svg_icon_condition_libraries()],
            ],
          ],
        ]
      );

      $this->add_control(
        'element_icon_stroke_color',
        [
          'label' => esc_html__( 'Icon Stroke Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-divider__element svg' => 'stroke: {{VALUE}};',
          ],
          'conditions' => [
            'terms' => [
              ['name' => 'element_type', 'operator' => '==', 'value' => 'icon'],
              ['name' => 'element_icon[library]', 'operator' => 'in', 'value' => themeasy_svg_icon_condition_libraries()],
            ],
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'element_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-divider__element',
          'condition' => [
            'element_type' => 'text',
          ],
        ]
      );

      $this->add_responsive_control(
        'element_icon_size',
        [
          'label' => esc_html__( 'Icon Size', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'em', 'rem'],
          'range' => [
            'px' => ['min' => 8, 'max' => 120, 'step' => 1],
            'em' => ['min' => 0.1, 'max' => 10, 'step' => 0.1],
            'rem' => ['min' => 0.1, 'max' => 10, 'step' => 0.1],
          ],
          'default' => ['unit' => 'px', 'size' => 20],
          'selectors' => [
            '{{WRAPPER}} .tms-divider__element' => 'font-size: {{SIZE}}{{UNIT}};',
            '{{WRAPPER}} .tms-divider__element svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'element_type' => 'icon',
          ],
        ]
      );

      $this->add_responsive_control(
        'element_icon_thickness',
        [
          'label' => esc_html__( 'Icon Thickness', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => ['min' => 0.5, 'max' => 10, 'step' => 0.5],
          ],
          'default' => ['unit' => 'px', 'size' => 2],
          'selectors' => [
            '{{WRAPPER}} .tms-divider__element svg,
             {{WRAPPER}} .tms-divider__element svg *' => 'stroke-width: {{SIZE}}{{UNIT}};',
          ],
          'conditions' => [
            'terms' => [
              ['name' => 'element_type', 'operator' => '==', 'value' => 'icon'],
              ['name' => 'element_icon[library]', 'operator' => 'in', 'value' => themeasy_svg_icon_condition_libraries()],
            ],
          ],
        ]
      );

      // The gap belongs to the element, not to the rule: it is the breathing
      // room on both sides of it, and it applies to every rule skin.
      $this->add_responsive_control(
        'line_element_gap',
        [
          'label' => esc_html__( 'Element Gap', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'em', 'rem'],
          'range' => [
            'px' => ['min' => 0, 'max' => 80, 'step' => 1],
            'em' => ['min' => 0, 'max' => 6, 'step' => 0.1],
            'rem' => ['min' => 0, 'max' => 6, 'step' => 0.1],
          ],
          'default' => ['unit' => 'px', 'size' => 16],
          'selectors' => [
            '{{WRAPPER}} .tms-divider__inner' => 'gap: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'element_badge_heading',
        [
          'label' => esc_html__( 'Badge', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'element_bg_color',
        [
          'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-divider__element' => 'background-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'element_padding',
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'em', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-divider__element' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Border::get_type(),
        [
          'name' => 'element_border',
          'selector' => '{{WRAPPER}} .tms-divider__element',
        ]
      );

      $this->add_control(
        'element_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', '%', 'em', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-divider__element' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Advanced sections: Themeasy Motion (Pro — the engines run on GSAP, which
    // the Free build neither ships nor loads).
    // ------------------------------------------------------------------------
    if ( Entitlement::can_use_widgets() ) {
      $this->register_motion_controls();
    } else {
      themeasy_register_motion_upsell_section( $this );
    }
  }

  /**
   * Register the Themeasy Motion sections (Advanced tab).
   *
   * Pro only: the motion engines run on GSAP, which the Free build neither
   * ships nor loads (backlog #253). register_controls() calls this when the
   * site is entitled and registers the upsell section otherwise.
   *
   * @return void
   */
  private function register_motion_controls() {
    // ------------------------------------------------------------------------
    // Advanced section: Entrance Animation
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'widget_animation_section',
        [
          'label' => esc_html__( 'Motion — Animation', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_ADVANCED,
        ]
    );

      $this->add_control(
        'widget_animation',
        [
          'label' => esc_html__( 'Entrance Animation', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => '',
          'options' => themeasy_get_animation_options( 'block' ),
        ]
      );

      $this->add_control(
        'widget_animation_duration',
        [
          'label' => esc_html__( 'Duration', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['s'],
          'range' => ['s' => ['min' => 0, 'max' => 5, 'step' => 0.1]],
          'default' => ['unit' => 's', 'size' => 1],
          'condition' => ['widget_animation!' => ''],
        ]
      );

      $this->add_control(
        'widget_animation_delay',
        [
          'label' => esc_html__( 'Delay', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['s'],
          'range' => ['s' => ['min' => 0, 'max' => 5, 'step' => 0.1]],
          'default' => ['unit' => 's', 'size' => 0],
          'condition' => ['widget_animation!' => ''],
        ]
      );

      // Draw-on for the SVG Shape line. The motif is a real inline <svg>
      // stroke carrying pathLength="1000", so the reveal is a normalized
      // stroke-dashoffset tween (1000 -> 0) that survives resizes with no
      // re-measuring and no DrawSVG plugin. It lives here rather than in its
      // own section
      // because Themeasy Motion is where every other widget keeps its
      // animation knobs (see the Icon widget's Loop Animation).
      $this->add_control(
        'shape_draw_heading',
        [
          'label' => esc_html__( 'Draw Shape', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
          'condition' => [
            'skin' => 'shape',
            'shape_style' => 'line',
          ],
        ]
      );

      $this->add_control(
        'shape_draw',
        [
          'label' => esc_html__( 'Draw the Line', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => '',
          'description' => esc_html__( 'Reveal the motif by drawing it from one end to the other. The line becomes one continuous stroke across the full width, so Pattern Width no longer applies. Without GSAP, or under reduced motion, the line simply renders fully drawn.', 'themeasy-lite' ),
          'condition' => [
            'skin' => 'shape',
            'shape_style' => 'line',
          ],
        ]
      );

      $this->add_control(
        'shape_draw_trigger',
        [
          'label' => esc_html__( 'Draw Mode', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'view',
          'options' => [
            'view' => esc_html__( 'Play when visible', 'themeasy-lite' ),
            'scrub' => esc_html__( 'Scrub with scroll', 'themeasy-lite' ),
          ],
          'description' => esc_html__( 'Play runs once at its own speed when the divider enters the viewport. Scrub ties the drawing to the scrollbar, so the reader controls it.', 'themeasy-lite' ),
          'condition' => [
            'skin' => 'shape',
            'shape_style' => 'line',
            'shape_draw' => 'yes',
          ],
        ]
      );

      $this->add_control(
        'shape_draw_direction',
        [
          'label' => esc_html__( 'Draw From', 'themeasy-lite' ),
          'type' => Controls_Manager::CHOOSE,
          'options' => [
            'start' => [
              'title' => esc_html__( 'Left', 'themeasy-lite' ),
              'icon' => 'eicon-h-align-left',
            ],
            'end' => [
              'title' => esc_html__( 'Right', 'themeasy-lite' ),
              'icon' => 'eicon-h-align-right',
            ],
          ],
          'default' => 'start',
          'toggle' => false,
          'description' => esc_html__( 'Which end the stroke grows from. Flip Horizontal mirrors the motif, so it swaps this too.', 'themeasy-lite' ),
          'condition' => [
            'skin' => 'shape',
            'shape_style' => 'line',
            'shape_draw' => 'yes',
          ],
        ]
      );

      $this->add_control(
        'shape_draw_duration',
        [
          'label' => esc_html__( 'Draw Duration', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['s'],
          'range' => ['s' => ['min' => 0.2, 'max' => 5, 'step' => 0.1]],
          'default' => ['unit' => 's', 'size' => 1.6],
          'condition' => [
            'skin' => 'shape',
            'shape_style' => 'line',
            'shape_draw' => 'yes',
            'shape_draw_trigger' => 'view',
          ],
        ]
      );

      $this->add_control(
        'shape_draw_delay',
        [
          'label' => esc_html__( 'Draw Delay', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['s'],
          'range' => ['s' => ['min' => 0, 'max' => 3, 'step' => 0.1]],
          'default' => ['unit' => 's', 'size' => 0],
          'condition' => [
            'skin' => 'shape',
            'shape_style' => 'line',
            'shape_draw' => 'yes',
            'shape_draw_trigger' => 'view',
          ],
        ]
      );

      $this->add_control(
        'shape_draw_easing',
        [
          'label' => esc_html__( 'Easing', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'power2.out',
          'options' => [
            'none' => esc_html__( 'None', 'themeasy-lite' ),
            'power1.out' => esc_html__( 'Power1 Out', 'themeasy-lite' ),
            'power2.out' => esc_html__( 'Power2 Out', 'themeasy-lite' ),
            'power3.out' => esc_html__( 'Power3 Out', 'themeasy-lite' ),
            'power4.out' => esc_html__( 'Power4 Out', 'themeasy-lite' ),
            'expo.out' => esc_html__( 'Expo Out', 'themeasy-lite' ),
            'circ.out' => esc_html__( 'Circ Out', 'themeasy-lite' ),
            'sine.out' => esc_html__( 'Sine Out', 'themeasy-lite' ),
          ],
          'condition' => [
            'skin' => 'shape',
            'shape_style' => 'line',
            'shape_draw' => 'yes',
            'shape_draw_trigger' => 'view',
          ],
        ]
      );

      $this->add_control(
        'shape_draw_scrub',
        [
          'label' => esc_html__( 'Scrub Smoothing', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['s'],
          'range' => ['s' => ['min' => 0, 'max' => 2, 'step' => 0.1]],
          'default' => ['unit' => 's', 'size' => 0.6],
          'description' => esc_html__( 'Lag between scroll and stroke. 0 locks the drawing to the scrollbar; higher values let it ease in.', 'themeasy-lite' ),
          'condition' => [
            'skin' => 'shape',
            'shape_style' => 'line',
            'shape_draw' => 'yes',
            'shape_draw_trigger' => 'scrub',
          ],
        ]
      );

      $this->add_control(
        'shape_draw_start',
        [
          'label' => esc_html__( 'Draw Start', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['%'],
          'range' => ['%' => ['min' => 50, 'max' => 100, 'step' => 1]],
          'default' => ['unit' => '%', 'size' => 85],
          'description' => esc_html__( 'Where the divider sits in the viewport (measured from the top) when drawing begins.', 'themeasy-lite' ),
          'condition' => [
            'skin' => 'shape',
            'shape_style' => 'line',
            'shape_draw' => 'yes',
          ],
        ]
      );

      $this->add_control(
        'shape_draw_end',
        [
          'label' => esc_html__( 'Draw End', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['%'],
          'range' => ['%' => ['min' => 0, 'max' => 60, 'step' => 1]],
          'default' => ['unit' => '%', 'size' => 40],
          'description' => esc_html__( 'Where the divider sits in the viewport when the stroke completes.', 'themeasy-lite' ),
          'condition' => [
            'skin' => 'shape',
            'shape_style' => 'line',
            'shape_draw' => 'yes',
            'shape_draw_trigger' => 'scrub',
          ],
        ]
      );

    $this->end_controls_section();
  }

  /**
   * Render the widget output on the frontend.
   *
   * @return void
   */
  protected function render() {
    $settings = $this->get_settings_for_display();

    // Themeasy Motion is Pro (GSAP — backlog #253). Without it the motion
    // settings read as empty, so saved values (a template, a site back from
    // premium) emit no markup: the hidden guard would leave the widget
    // invisible with no engine to reveal it.
    $motion = Entitlement::can_use_widgets();

    // ------------------------------------------------------------------------
    // Settings.
    // ------------------------------------------------------------------------
    $show_element = ( $settings['show_element'] ?? '' ) === 'yes';
    $fade_edges = ( $settings['line_fade_edges'] ?? '' ) === 'yes';
    $element_text = $settings['element_text'] ?? '';
    $element_icon = is_array( $settings['element_icon'] ?? null ) ? $settings['element_icon'] : [];
    $flip_horizontal = ( $settings['shape_flip_horizontal'] ?? '' ) === 'yes';
    $flip_vertical = ( $settings['shape_flip_vertical'] ?? '' ) === 'yes';

    // Validate enum-backed settings against their allowlists before they are
    // composed into CSS class names (defense in depth, even though output is
    // escaped at print time).
    $skin = in_array( $settings['skin'] ?? '', ['line', 'dots', 'shape'], true ) ? $settings['skin'] : 'line';
    $element_type = in_array( $settings['element_type'] ?? '', ['icon', 'text'], true ) ? $settings['element_type'] : 'text';
    $element_position = in_array( $settings['element_position'] ?? '', ['start', 'center', 'end'], true ) ? $settings['element_position'] : 'center';
    $line_color_type = in_array( $settings['line_color_type'] ?? '', ['solid', 'gradient'], true ) ? $settings['line_color_type'] : 'solid';
    $shape_type = array_key_exists( $settings['shape_type'] ?? '', $this->get_shape_paths() ) ? $settings['shape_type'] : 'curve';
    $shape_style = in_array( $settings['shape_style'] ?? '', ['filled', 'line'], true ) ? $settings['shape_style'] : 'filled';
    $orientation = in_array( $settings['divider_orientation'] ?? '', ['horizontal', 'vertical'], true ) ? $settings['divider_orientation'] : 'horizontal';
    $draw_trigger = in_array( $settings['shape_draw_trigger'] ?? '', ['view', 'scrub'], true ) ? $settings['shape_draw_trigger'] : 'view';
    $draw_direction = in_array( $settings['shape_draw_direction'] ?? '', ['start', 'end'], true ) ? $settings['shape_draw_direction'] : 'start';

    // The draw-on only exists for the stroked line: a filled silhouette has no
    // contour to trace. A stale 'yes' left behind by switching back to Filled
    // must therefore never reach the markup.
    $draw = ( $motion && 'shape' === $skin && 'line' === $shape_style && ( $settings['shape_draw'] ?? '' ) === 'yes' );

    // Line geometry: the open contour, repeated across one viewBox when the
    // motif tiles. A stroked <svg> cannot repeat itself the way a CSS mask
    // does, and a drawn stroke must stay a single continuous path anyway.
    $shape_contour = $this->get_shape_contours()[$shape_type];
    $shape_repeats = ( !$draw && in_array( $shape_type, $this->get_tiling_shapes(), true ) )
      ? $this->get_shape_repeats( $settings )
      : 1;

    // Line and dots are the two rule skins: same structure (segments plus an
    // optional center element), only the paint differs. Everything structural
    // keys off this, never off 'line' alone.
    $is_rule = in_array( $skin, ['line', 'dots'], true );

    // The shape skin paints a fixed 1200x120 motif across the width and has no
    // vertical form, so orientation only ever applies to the rule skins.
    $is_vertical = ( $is_rule && 'vertical' === $orientation );

    // ------------------------------------------------------------------------
    // Wrapper classes + attributes.
    // ------------------------------------------------------------------------
    $wrapper_classes = [
      'tms-divider',
      'tms-divider--skin-' . $skin,
    ];
    $wrapper_atts = [];

    // Decide semantics: a text-bearing line announces as a separator
    // (role + orientation); a purely decorative divider is aria-hidden.
    $has_text_element = ( $is_rule && $show_element && 'text' === $element_type && '' !== $element_text );

    // The rule only splits into two flanking segments when there is a center
    // element to flank; on its own it is a single continuous line.
    $has_element = ( $is_rule && $show_element && ( $element_text || ! empty( $element_icon['value'] ) ) );

    if ( $is_rule ) {
      // Color type (and the border/gradient painting it selects) belongs to the
      // line skin only; dots paint from their own tokens.
      if ( 'line' === $skin ) {
        $wrapper_classes[] = 'tms-divider--line-' . $line_color_type;
      }

      if ( $is_vertical ) {
        $wrapper_classes[] = 'tms-divider--vertical';
      }

      if ( $fade_edges ) {
        $wrapper_classes[] = 'tms-divider--fade-edges';
      }

      if ( $has_element ) {
        $wrapper_classes[] = 'tms-divider--has-element';
        $wrapper_classes[] = 'tms-divider--element-' . $element_position;
      } else {
        $wrapper_classes[] = 'tms-divider--no-element';
      }
    } else {
      $wrapper_classes[] = 'tms-divider--shape-' . $shape_type;

      // Line style: the same mask is composited against a pushed-down copy of
      // itself, leaving only the band between both contours (see the widget
      // CSS). 'line' is a skin name, never a shape key, so the modifier cannot
      // collide with the shape classes above.
      if ( 'line' === $shape_style ) {
        $wrapper_classes[] = 'tms-divider--shape-line';
      }

      // A periodic motif repeats at its own width so it keeps its proportions;
      // a single silhouette keeps stretching to span the divider. A drawn
      // stroke is one continuous path, so it opts out and stretches too.
      if ( in_array( $shape_type, $this->get_tiling_shapes(), true ) && !$draw ) {
        $wrapper_classes[] = 'tms-divider--shape-tiled';
      }

      if ( $draw ) {
        $wrapper_classes[] = 'tms-divider--draw';

        // The editor canvas shows the finished stroke: a scroll-driven reveal
        // needs a real scroll position, which the preview iframe does not
        // have. The frontend module plays it once there instead (see the
        // Preview Runtime), and --static is what tells it to.
        if ( themeasy_is_elementor_editor() ) {
          $wrapper_classes[] = 'tms-divider--static';
        } else {
          // Hide the stroke before the footer script boots, or the finished
          // line flashes in on first paint and then jumps back to zero. The
          // CSS carries its own failsafe for a JS that never arrives.
          $wrapper_classes[] = 'tms-divider--draw-pending';
        }

        $wrapper_atts['data-draw'] = $draw_trigger;
        $wrapper_atts['data-draw-from'] = $draw_direction;
        $wrapper_atts['data-draw-duration'] = (string) ( $settings['shape_draw_duration']['size'] ?? 1.6 );
        $wrapper_atts['data-draw-delay'] = (string) ( $settings['shape_draw_delay']['size'] ?? 0 );
        $wrapper_atts['data-draw-ease'] = (string) ( $settings['shape_draw_easing'] ?? 'power2.out' );
        $wrapper_atts['data-draw-scrub'] = (string) ( $settings['shape_draw_scrub']['size'] ?? 0.6 );
        $wrapper_atts['data-draw-start'] = (string) ( $settings['shape_draw_start']['size'] ?? 85 );
        $wrapper_atts['data-draw-end'] = (string) ( $settings['shape_draw_end']['size'] ?? 40 );
      }

      if ( $flip_horizontal ) {
        $wrapper_classes[] = 'tms-divider--flip-h';
      }

      if ( $flip_vertical ) {
        $wrapper_classes[] = 'tms-divider--flip-v';
      }
    }

    if ( $has_text_element ) {
      $wrapper_atts['role'] = 'separator';
      $wrapper_atts['aria-orientation'] = $is_vertical ? 'vertical' : 'horizontal';
    } else {
      $wrapper_atts['aria-hidden'] = 'true';
    }

    // Entrance animation.
    $animation = $motion ? ( $settings['widget_animation'] ?? '' ) : '';

    if ( $animation ) {
      $wrapper_classes[] = 'tms-block-animation';
      $wrapper_classes[] = 'tms-animation--on-view';
      $wrapper_classes[] = 'tms-animation--hidden';

      $wrapper_atts['tms-block-animation'] = $animation;
      $wrapper_atts['duration'] = $settings['widget_animation_duration']['size'] ?? '1';
      $wrapper_atts['delay'] = $settings['widget_animation_delay']['size'] ?? '0';
    }

    $wrapper_classes_output = implode( ' ', array_filter( $wrapper_classes ) );
    $wrapper_atts_output = themeasy_html_attributes( $wrapper_atts );

    // ------------------------------------------------------------------------
    // Render attributes.
    // ------------------------------------------------------------------------
    if ( $is_rule && $show_element && 'text' === $element_type ) {
      $this->add_render_attribute( 'element_text', 'class', 'tms-divider__element tms-divider__element--text' );
      $this->add_inline_editing_attributes( 'element_text', 'none' );
    }

    // ------------------------------------------------------------------------
    // Icon markup for the line skin.
    // ------------------------------------------------------------------------
    $icon_html = '';

    if ( $is_rule && $show_element && 'icon' === $element_type && ! empty( $element_icon['value'] ) ) {
      $icon_html = themeasy_render_icon_html( $element_icon );
    }

    // ------------------------------------------------------------------------
    // Output.
    // ------------------------------------------------------------------------
    ?>
    <div class="<?php echo esc_attr( $wrapper_classes_output ); ?>"<?php echo $wrapper_atts_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
      <div class="tms-divider__inner">

        <?php if ( 'shape' === $skin && 'line' === $shape_style ) : ?>
          <?php /* The line style is a real stroke. pathLength="1000" normalizes
                   the dash space, so the draw-on needs no measuring and
                   survives every resize; 1000 rather than 1 because GSAP
                   rounds the pixel values it writes, and a whole animation
                   inside a single unit would round away to an on/off snap. */ ?>
          <div class="tms-divider__shape" aria-hidden="true">
            <svg
              class="tms-divider__shape-svg"
              viewBox="0 0 <?php echo esc_attr( (string) ( $shape_repeats * 1200 ) ); ?> 120"
              preserveAspectRatio="none"
              focusable="false"
              aria-hidden="true"
              style="--tms-divider-shape-repeats: <?php echo esc_attr( (string) $shape_repeats ); ?>;">
              <?php for ( $repeat = 0; $repeat < $shape_repeats; $repeat++ ) : ?>
                <path
                  class="tms-divider__shape-path"
                  d="<?php echo esc_attr( $shape_contour ); ?>"
                  pathLength="1000"
                  transform="translate(<?php echo esc_attr( (string) ( $repeat * 1200 ) ); ?>,0)"/>
              <?php endfor; ?>
            </svg>
          </div>

        <?php elseif ( 'shape' === $skin ) : ?>
          <div class="tms-divider__shape" aria-hidden="true">
            <span
              class="tms-divider__shape-motif"
              style="--tms-divider-shape-mask: <?php echo esc_attr( $this->get_shape_mask( $shape_type ) ); ?>;"></span>
          </div>

        <?php elseif ( $has_element ) : ?>

          <?php /* Each segment exists only on the side the element is not on. */ ?>
          <?php if ( 'start' !== $element_position ) : ?>
            <span class="tms-divider__line tms-divider__line--start" aria-hidden="true"></span>
          <?php endif; ?>

          <?php if ( 'icon' === $element_type && $icon_html ) : ?>
            <span class="tms-divider__element tms-divider__element--icon" aria-hidden="true">
              <?php echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </span>
          <?php elseif ( 'text' === $element_type && '' !== $element_text ) : ?>
            <span <?php $this->print_render_attribute_string( 'element_text' ); ?>>
              <?php echo esc_html( $element_text ); ?>
            </span>
          <?php endif; ?>

          <?php if ( 'end' !== $element_position ) : ?>
            <span class="tms-divider__line tms-divider__line--end" aria-hidden="true"></span>
          <?php endif; ?>

        <?php else : ?>

          <span class="tms-divider__line" aria-hidden="true"></span>

        <?php endif; ?>

      </div><!-- /.tms-divider__inner -->
    </div><!-- /.tms-divider -->
    <?php
  }

  /**
   * Render the live preview template used by the Elementor editor.
   *
   * @return void
   */
  protected function content_template() {
    $shape_paths = $this->get_shape_paths();
    $shape_contours = $this->get_shape_contours();
    $tiling_shapes = $this->get_tiling_shapes();
    ?>
    <#
      // Themeasy Motion is Pro (render() parity): without it the motion
      // settings read as empty.
      var motion = <?php echo Entitlement::can_use_widgets() ? 'true' : 'false'; ?>;

      // ------------------------------------------------------------------------
      // Settings.
      // ------------------------------------------------------------------------
      var skin            = settings.skin || 'line';
      var showElement     = settings.show_element === 'yes';
      var fadeEdges       = settings.line_fade_edges === 'yes';
      var elementType     = settings.element_type || 'text';
      var elementPosition = settings.element_position || 'center';
      var elementText     = settings.element_text || '';
      var elementIcon     = settings.element_icon || {};
      var lineColorType   = settings.line_color_type || 'solid';
      var shapeType       = settings.shape_type || 'curve';
      var shapeStyle      = settings.shape_style || 'filled';
      var flipHorizontal  = settings.shape_flip_horizontal === 'yes';
      var flipVertical    = settings.shape_flip_vertical === 'yes';
      var orientation     = settings.divider_orientation || 'horizontal';

      // Line and dots are the two rule skins: same structure (segments plus an
      // optional center element), only the paint differs. Everything structural
      // keys off this, never off 'line' alone.
      var isRule          = ( skin === 'line' || skin === 'dots' );

      // The shape skin paints a fixed 1200x120 motif across the width and has
      // no vertical form, so orientation only ever applies to the rule skins.
      var isVertical      = ( isRule && orientation === 'vertical' );

      var shapePaths    = <?php echo wp_json_encode( $shape_paths ); ?>;
      var shapeContours = <?php echo wp_json_encode( $shape_contours ); ?>;
      var tilingShapes  = <?php echo wp_json_encode( $tiling_shapes ); ?>;
      var shapePath     = shapePaths[ shapeType ] || shapePaths['curve'];
      var shapeContour  = shapeContours[ shapeType ] || shapeContours['curve'];
      var shapeIsTiled  = ( tilingShapes.indexOf( shapeType ) !== -1 );

      // Twin of render(): the draw-on only exists for the stroked line, so a
      // stale 'yes' left behind by switching back to Filled must not count.
      var shapeDraw = ( motion && skin === 'shape' && shapeStyle === 'line' && settings.shape_draw === 'yes' );

      // Twin of get_shape_repeats(): size the repeat count against the
      // SMALLEST Pattern Width configured across breakpoints.
      var tileSizes = ['shape_tile_width', 'shape_tile_width_tablet', 'shape_tile_width_mobile']
        .map( function ( key ) {
          return settings[ key ] ? parseFloat( settings[ key ].size ) : NaN;
        } )
        .filter( function ( size ) {
          return !isNaN( size ) && size > 0;
        } );

      var shapeTile    = tileSizes.length ? Math.min.apply( null, tileSizes ) : 1200;
      var shapeRepeats = ( !shapeDraw && shapeIsTiled )
        ? Math.max( 1, Math.min( <?php echo (int) self::SHAPE_TILE_MAX_REPEATS; ?>, Math.ceil( <?php echo (int) self::SHAPE_TILE_REFERENCE_WIDTH; ?> / shapeTile ) ) )
        : 1;

      // Twin of get_shape_mask(): the motif is painted as a data URI mask, so
      // color keeps flowing from currentColor and a periodic motif can repeat.
      var shapeMask = 'url("data:image/svg+xml,' + encodeURIComponent(
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">'
        + '<path d="' + shapePath + '"/></svg>'
      ) + '")';

      // ------------------------------------------------------------------------
      // Wrapper classes + attributes.
      // ------------------------------------------------------------------------
      var wrapperClasses = [ 'tms-divider', 'tms-divider--skin-' + skin ];
      var wrapperAtts    = {};

      var hasElementValue = ( elementType === 'icon' )
        ? !! ( elementIcon && elementIcon.value )
        : !! elementText;

      var hasTextElement = ( isRule && showElement && elementType === 'text' && !! elementText );

      // The rule only splits into two flanking segments when there is a center
      // element to flank; on its own it is a single continuous line.
      var hasElement = ( isRule && showElement && hasElementValue );

      if ( isRule ) {
        // Color type (and the border/gradient painting it selects) belongs to
        // the line skin only; dots paint from their own tokens.
        if ( skin === 'line' ) {
          wrapperClasses.push( 'tms-divider--line-' + lineColorType );
        }

        if ( isVertical ) {
          wrapperClasses.push( 'tms-divider--vertical' );
        }

        if ( fadeEdges ) {
          wrapperClasses.push( 'tms-divider--fade-edges' );
        }

        if ( hasElement ) {
          wrapperClasses.push( 'tms-divider--has-element' );
          wrapperClasses.push( 'tms-divider--element-' + elementPosition );
        } else {
          wrapperClasses.push( 'tms-divider--no-element' );
        }
      } else {
        wrapperClasses.push( 'tms-divider--shape-' + shapeType );

        // Line style: the same mask is composited against a pushed-down copy
        // of itself, leaving only the band between both contours (see the
        // widget CSS).
        if ( shapeStyle === 'line' ) {
          wrapperClasses.push( 'tms-divider--shape-line' );
        }

        // A periodic motif repeats at its own width so it keeps its
        // proportions; a single silhouette keeps stretching to span the
        // divider. A drawn stroke is one continuous path, so it stretches too.
        if ( shapeIsTiled && ! shapeDraw ) {
          wrapperClasses.push( 'tms-divider--shape-tiled' );
        }

        // The canvas always shows the finished stroke — a scroll-driven reveal
        // has no real scroll position inside the preview iframe. The Preview
        // Runtime plays it once per render instead, and --static is its cue.
        if ( shapeDraw ) {
          wrapperClasses.push( 'tms-divider--draw', 'tms-divider--static' );

          // A plain truthiness check would swallow a legitimate ZERO — Scrub
          // Smoothing 0 ("lock to the scrollbar") and Draw End 0 are both real
          // authored values, and PHP passes them through.
          var drawSize = function ( key, fallback ) {
            var size = settings[ key ] ? parseFloat( settings[ key ].size ) : NaN;
            return isNaN( size ) ? fallback : String( size );
          };

          wrapperAtts['data-draw'] = settings.shape_draw_trigger || 'view';
          wrapperAtts['data-draw-from'] = settings.shape_draw_direction || 'start';
          wrapperAtts['data-draw-ease'] = settings.shape_draw_easing || 'power2.out';
          wrapperAtts['data-draw-duration'] = drawSize( 'shape_draw_duration', '1.6' );
          wrapperAtts['data-draw-delay'] = drawSize( 'shape_draw_delay', '0' );
          wrapperAtts['data-draw-scrub'] = drawSize( 'shape_draw_scrub', '0.6' );
          wrapperAtts['data-draw-start'] = drawSize( 'shape_draw_start', '85' );
          wrapperAtts['data-draw-end'] = drawSize( 'shape_draw_end', '40' );
        }

        if ( flipHorizontal ) {
          wrapperClasses.push( 'tms-divider--flip-h' );
        }

        if ( flipVertical ) {
          wrapperClasses.push( 'tms-divider--flip-v' );
        }
      }

      if ( hasTextElement ) {
        wrapperAtts['role'] = 'separator';
        wrapperAtts['aria-orientation'] = isVertical ? 'vertical' : 'horizontal';
      } else {
        wrapperAtts['aria-hidden'] = 'true';
      }

      // Entrance animation.
      var animation = motion ? ( settings.widget_animation || '' ) : '';

      if ( animation ) {
        wrapperClasses.push( 'tms-block-animation', 'tms-animation--on-view', 'tms-animation--hidden' );
        wrapperAtts['tms-block-animation'] = animation;
        wrapperAtts['duration'] = ( settings.widget_animation_duration && settings.widget_animation_duration.size )
          ? settings.widget_animation_duration.size
          : '1';
        wrapperAtts['delay'] = ( settings.widget_animation_delay && settings.widget_animation_delay.size )
          ? settings.widget_animation_delay.size
          : '0';
      }

      var wrapperClassStr = wrapperClasses.filter( Boolean ).join( ' ' );

      // ------------------------------------------------------------------------
      // Render attributes.
      // ------------------------------------------------------------------------
      if ( isRule && showElement && elementType === 'text' ) {
        view.addRenderAttribute( 'element_text', 'class', 'tms-divider__element tms-divider__element--text' );
        view.addInlineEditingAttributes( 'element_text', 'none' );
      }

      // ------------------------------------------------------------------------
      // Icon markup for the line skin.
      // ------------------------------------------------------------------------
      var iconMarkup = '';

      if ( isRule && showElement && elementType === 'icon' && elementIcon && elementIcon.value ) {
        iconMarkup = ( window.Themeasy && window.Themeasy.renderIconMarkup )
          ? window.Themeasy.renderIconMarkup( view, elementIcon, null, {} )
          : '';
      }
    #>

    <div class="{{ wrapperClassStr }}" {{{ Themeasy.htmlAttributes( wrapperAtts ) }}}>
      <div class="tms-divider__inner">

        <# if ( skin === 'shape' && shapeStyle === 'line' ) { #>
          <div class="tms-divider__shape" aria-hidden="true">
            <svg
              class="tms-divider__shape-svg"
              viewBox="0 0 {{ shapeRepeats * 1200 }} 120"
              preserveAspectRatio="none"
              focusable="false"
              aria-hidden="true"
              style="--tms-divider-shape-repeats: {{ shapeRepeats }};">
              <# for ( var repeat = 0; repeat < shapeRepeats; repeat++ ) { #>
                <path
                  class="tms-divider__shape-path"
                  d="{{ shapeContour }}"
                  pathLength="1000"
                  transform="translate({{ repeat * 1200 }},0)"/>
              <# } #>
            </svg>
          </div>

        <# } else if ( skin === 'shape' ) { #>
          <div class="tms-divider__shape" aria-hidden="true">
            <span class="tms-divider__shape-motif" style="--tms-divider-shape-mask: {{ shapeMask }};"></span>
          </div>

        <# } else if ( hasElement ) { #>

          <# /* Each segment exists only on the side the element is not on. */ #>
          <# if ( elementPosition !== 'start' ) { #>
            <span class="tms-divider__line tms-divider__line--start" aria-hidden="true"></span>
          <# } #>

          <# if ( elementType === 'icon' && iconMarkup ) { #>
            <span class="tms-divider__element tms-divider__element--icon" aria-hidden="true">
              {{{ iconMarkup }}}
            </span>
          <# } else if ( elementType === 'text' && elementText ) { #>
            <span {{{ view.getRenderAttributeString( 'element_text' ) }}}>{{ elementText }}</span>
          <# } #>

          <# if ( elementPosition !== 'end' ) { #>
            <span class="tms-divider__line tms-divider__line--end" aria-hidden="true"></span>
          <# } #>

        <# } else { #>

          <span class="tms-divider__line" aria-hidden="true"></span>

        <# } #>

      </div><!-- /.tms-divider__inner -->
    </div><!-- /.tms-divider -->

    <?php
  }
}
