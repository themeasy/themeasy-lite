<?php
/**
 * Themeasy Elementor Widget: Icon
 *
 * Displays an icon with configurable shape, color style (solid or gradient),
 * optional link, entrance animation, and hover interactions.
 *
 * @package Themeasy
 * @since 1.0.0
 */

namespace Themeasy\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Widget_Base;
use Themeasy\Core\Entitlement;

/**
 * Icon widget — registers controls and renders output.
 */
class Icon extends Widget_Base {
  public function get_name() {
    return 'themeasy-icon';
  }

  public function get_title() {
    return esc_html__( 'Icon', 'themeasy-lite' );
  }

  public function get_icon() {
    return 'eicon-shape';
  }

  public function get_custom_help_url() {
    return 'https://themeasy.co/help-center';
  }

  public function get_categories() {
    return ['themeasy_elements_category', 'themeasy_content_category'];
  }

  public function get_keywords() {
    return ['themeasy', 'icon', 'shape', 'symbol', 'svg', 'font awesome', 'graphic'];
  }

  public function get_style_depends() {
    return ['themeasy-bundle'];
  }

  public function get_script_depends() {
    return [];
  }

  /**
   * Register widget controls.
   *
   * @return void
   */
  protected function register_controls() {
    // ------------------------------------------------------------------------
    // Content section: Icon
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'icon_section',
        [
          'label' => esc_html__( 'Icon', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
    );

      $this->add_control(
        'icon',
        [
          'label' => esc_html__( 'Icon', 'themeasy-lite' ),
          'type' => Controls_Manager::ICONS,
          'default' => [
            'value' => 'ty-feather-layers',
            'library' => 'ty-feather',
          ],
        ]
      );

      $this->add_control(
        'shape',
        [
          'label' => esc_html__( 'Shape', 'themeasy-lite' ),
          'description' => esc_html__( 'Wrap the icon in a solid or outlined container.', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'options' => [
            '' => esc_html__( 'None', 'themeasy-lite' ),
            'solid' => esc_html__( 'Solid', 'themeasy-lite' ),
            'outline' => esc_html__( 'Outline', 'themeasy-lite' ),
          ],
          'default' => '',
        ]
      );

      $this->add_control(
        'shape_style',
        [
          'label' => esc_html__( 'Shape Style', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'options' => [
            'square' => esc_html__( 'Square', 'themeasy-lite' ),
            'rounded' => esc_html__( 'Rounded', 'themeasy-lite' ),
            'circle' => esc_html__( 'Circle', 'themeasy-lite' ),
          ],
          'default' => 'circle',
          'condition' => [
            'shape!' => '',
          ],
        ]
      );

      $this->add_control(
        'link',
        [
          'label' => esc_html__( 'Link', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'Leave empty to disable link', 'themeasy-lite' ),
          'type' => Controls_Manager::URL,
          'dynamic' => [
            'active' => true,
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Shape
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'icon_shape_styles_section',
        [
          'label' => esc_html__( 'Shape', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'shape!' => '',
          ],
        ]
    );

      $this->add_group_control(
        Group_Control_Background::get_type(),
        [
          'name' => 'shape_background',
          'selector' => '{{WRAPPER}} .tms-icon-wrapper',
          'condition' => [
            'shape' => 'solid',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Border::get_type(),
        [
          'name' => 'shape_border',
          'selector' => '{{WRAPPER}} .tms-icon-wrapper',
          'condition' => [
            'shape' => 'outline',
          ],
        ]
      );

      $this->add_responsive_control(
        'shape_size',
        [
          'label' => esc_html__( 'Shape Size', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'separator' => 'before',
          'size_units' => ['px', '%', 'em', 'rem', 'vw', 'custom'],
          'range' => [
            'px' => [
              'min' => 10,
              'max' => 1000,
            ],
          ],
          'default' => ['unit' => 'px', 'size' => 64],
          'selectors' => [
            '{{WRAPPER}} .tms-icon-wrapper' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'shape_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', '%', 'em', 'rem', 'custom'],
          'selectors' => [
            '{{WRAPPER}} .tms-icon-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
          'condition' => [
            'shape_style' => 'rounded',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Icon
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'icon_styles_section',
        [
          'label' => esc_html__( 'Icon', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      $this->add_control(
        'icon_color_style',
        [
          'label' => esc_html__( 'Color Style', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'options' => [
            'solid' => esc_html__( 'Solid', 'themeasy-lite' ),
            'gradient' => esc_html__( 'Gradient', 'themeasy-lite' ),
          ],
          'default' => 'solid',
        ]
      );

      $this->add_group_control(
        Group_Control_Background::get_type(),
        [
          'name' => 'icon_background',
          'types' => ['gradient'],
          'selector' => '{{WRAPPER}} .tms-icon-wrapper .tms-icon,
                         {{WRAPPER}} .tms-icon-wrapper svg',
          'condition' => [
            'icon_color_style' => 'gradient',
          ],
        ]
      );

      $this->add_control(
        'icon_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-icon-wrapper .tms-icon,
             {{WRAPPER}} .tms-icon-wrapper svg' => 'color: {{VALUE}};',
          ],
          'condition' => [
            'icon_color_style' => 'solid',
          ],
        ]
      );

      $this->add_control(
        'icon_hover_color',
        [
          'label' => esc_html__( 'Hover Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-icon-wrapper:hover .tms-icon,
             {{WRAPPER}} .tms-icon-wrapper:hover svg' => 'color: {{VALUE}};',
          ],
          'condition' => [
            'icon_color_style' => 'solid',
          ],
        ]
      );

      $this->add_control(
        'icon_fill_color',
        [
          'label' => esc_html__( 'Fill Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-icon-wrapper svg' => 'fill: {{VALUE}};',
          ],
          'conditions' => [
            'terms' => [
              [
                'name' => 'icon[library]',
                'operator' => 'in',
                'value' => themeasy_svg_icon_condition_libraries(),
              ],
            ],
          ],
        ]
      );

      $this->add_control(
        'icon_stroke_color',
        [
          'label' => esc_html__( 'Stroke Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-icon-wrapper svg' => 'stroke: {{VALUE}};',
          ],
          'conditions' => [
            'terms' => [
              [
                'name' => 'icon[library]',
                'operator' => 'in',
                'value' => themeasy_svg_icon_condition_libraries(),
              ],
            ],
          ],
        ]
      );

      $this->add_responsive_control(
        'icon_size',
        [
          'label' => esc_html__( 'Icon Size', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'separator' => 'before',
          'size_units' => ['px', 'rem', 'em', 'vh'],
          'range' => [
            'px' => ['min' => 1, 'max' => 100, 'step' => 1],
            'rem' => ['min' => 0.1, 'max' => 10, 'step' => 0.1],
            'em' => ['min' => 0.1, 'max' => 10, 'step' => 0.1],
            'vh' => ['min' => 1, 'max' => 100, 'step' => 1],
          ],
          'default' => ['unit' => 'px', 'size' => 32],
          'selectors' => [
            '{{WRAPPER}} .tms-icon-wrapper' => 'font-size: {{SIZE}}{{UNIT}};',
            '{{WRAPPER}} .tms-icon-wrapper svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'icon_thickness',
        [
          'label' => esc_html__( 'Icon Thickness', 'themeasy-lite' ),
          'description' => esc_html__( 'Stroke width for outline-style SVG icons.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => ['min' => 0.5, 'max' => 10, 'step' => 0.5],
          ],
          'default' => ['unit' => 'px', 'size' => 2],
          'selectors' => [
            '{{WRAPPER}} .tms-icon-wrapper svg,
             {{WRAPPER}} .tms-icon-wrapper svg *' => 'stroke-width: {{SIZE}}{{UNIT}};',
          ],
          'conditions' => [
            'terms' => [
              [
                'name' => 'icon[library]',
                'operator' => 'in',
                'value' => themeasy_svg_icon_condition_libraries(),
              ],
            ],
          ],
        ]
      );

      $this->add_control(
        'icon_rotate',
        [
          'label' => esc_html__( 'Rotate', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['deg', 'rad', 'grad', 'turn'],
          'range' => [
            'deg' => [
              'min' => 0,
              'max' => 360,
              'step' => 1,
            ],
            'rad' => [
              'min' => 0,
              'max' => 6.283,
              'step' => 0.01,
            ],
            'grad' => [
              'min' => 0,
              'max' => 400,
              'step' => 1,
            ],
            'turn' => [
              'min' => 0,
              'max' => 1,
              'step' => 0.01,
            ],
          ],
          'default' => [
            'size' => 0,
            'unit' => 'deg',
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-icon-wrapper .tms-icon,
             {{WRAPPER}} .tms-icon-wrapper svg' => 'transform: rotate({{SIZE}}{{UNIT}}); transform-origin: center;',
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
          'label' => esc_html__( 'Themeasy Motion — Animation', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_ADVANCED,
        ]
    );

      $this->add_control(
        'icon_animation',
        [
          'label' => esc_html__( 'Entrance Animation', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => '',
          'options' => themeasy_get_animation_options( 'block' ),
        ]
      );

      $this->add_control(
        'icon_animation_duration',
        [
          'label' => esc_html__( 'Duration', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['s'],
          'range' => ['s' => ['min' => 0, 'max' => 5, 'step' => 0.1]],
          'default' => ['unit' => 's', 'size' => 1],
          'condition' => ['icon_animation!' => ''],
        ]
      );

      $this->add_control(
        'icon_animation_delay',
        [
          'label' => esc_html__( 'Delay', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['s'],
          'range' => ['s' => ['min' => 0, 'max' => 5, 'step' => 0.1]],
          'default' => ['unit' => 's', 'size' => 0],
          'condition' => ['icon_animation!' => ''],
        ]
      );

      // Continuous motion, driven by the tmsIconAnimation engine on the icon
      // node itself. Own key: icon_animation is the entrance pool and
      // icon_hover_animation is the hover pool — both already taken.
      $this->add_control(
        'icon_loop_animation',
        [
          'label' => esc_html__( 'Loop Animation', 'themeasy-lite' ),
          'description' => esc_html__( 'Continuous motion applied to the icon itself, independent of the entrance animation. Draw Stroke needs an SVG icon with strokes.', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => '',
          'options' => [
            '' => esc_html__( 'None', 'themeasy-lite' ),
            'spin' => esc_html__( 'Spin', 'themeasy-lite' ),
            'pulse' => esc_html__( 'Pulse', 'themeasy-lite' ),
            'wiggle' => esc_html__( 'Wiggle', 'themeasy-lite' ),
            'float' => esc_html__( 'Float', 'themeasy-lite' ),
            'dash' => esc_html__( 'Draw Stroke', 'themeasy-lite' ),
          ],
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'icon_loop_trigger',
        [
          'label' => esc_html__( 'Loop Trigger', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'scroll',
          'options' => [
            'scroll' => esc_html__( 'On View', 'themeasy-lite' ),
            'hover' => esc_html__( 'On Hover', 'themeasy-lite' ),
          ],
          'condition' => ['icon_loop_animation!' => ''],
        ]
      );

      // Both sliders default to an EMPTY size on purpose: every preset carries
      // its own timing in the engine (spin 6s, pulse 0.8s, wiggle 0.7s, float
      // 1.2s, dash 1.6s), so an unset slider emits no attribute and keeps that
      // per-preset value instead of flattening all five to a single number.
      // The unit is still declared — without it Elementor stamps 'px' on a
      // control whose only size_unit is seconds.
      $this->add_control(
        'icon_loop_duration',
        [
          'label' => esc_html__( 'Loop Duration', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['s'],
          'range' => ['s' => ['min' => 0.1, 'max' => 10, 'step' => 0.1]],
          'default' => ['unit' => 's', 'size' => ''],
          'condition' => ['icon_loop_animation!' => ''],
        ]
      );

      $this->add_control(
        'icon_loop_delay',
        [
          'label' => esc_html__( 'Loop Delay', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['s'],
          'range' => ['s' => ['min' => 0, 'max' => 5, 'step' => 0.1]],
          'default' => ['unit' => 's', 'size' => ''],
          'condition' => ['icon_loop_animation!' => ''],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Advanced section: Hover Interactions
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'widget_hover_section',
        [
          'label' => esc_html__( 'Themeasy Motion — Hover Interactions', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_ADVANCED,
        ]
    );

      $this->add_control(
        'icon_hover_animation',
        [
          'label' => esc_html__( 'Hover Animation', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => '',
          'options' => themeasy_get_animation_options( 'hover' ),
        ]
      );

      $this->add_control(
        'cursor_text',
        [
          'label' => esc_html__( 'Cursor Text', 'themeasy-lite' ),
          'label_block' => false,
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'VIEW', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'Type your text here', 'themeasy-lite' ),
          'dynamic' => [
            'active' => true,
          ],
          'condition' => [
            'icon_hover_animation' => 'viewCursor',
          ],
        ]
      );

      $this->add_control(
        'cursor_text_width',
        [
          'label' => esc_html__( 'Width', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => ['min' => 30, 'max' => 300, 'step' => 5],
          ],
          'default' => ['size' => 100, 'unit' => 'px'],
          'selectors' => [
            '.tms-cursor-interaction' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; margin-top: calc(-{{SIZE}}{{UNIT}} / 2); margin-left: calc(-{{SIZE}}{{UNIT}} / 2);',
          ],
          'condition' => [
            'icon_hover_animation' => 'viewCursor',
          ],
        ]
      );

      $this->add_control(
        'cursor_text_background_color',
        [
          'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '.tms-cursor-interaction' => 'background-color: {{VALUE}};',
          ],
          'condition' => [
            'icon_hover_animation' => 'viewCursor',
          ],
        ]
      );

      $this->add_control(
        'cursor_text_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '.tms-cursor-interaction' => 'color: {{VALUE}};',
          ],
          'condition' => [
            'icon_hover_animation' => 'viewCursor',
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
    $icon = $settings['icon'] ?? [];

    // Early return if no icon.
    if ( empty( $icon['value'] ) ) {
      return;
    }

    $shape = $settings['shape'] ?? '';
    $shape_style = $settings['shape_style'] ?? '';
    $color_style = $settings['icon_color_style'] ?? 'solid';

    // ------------------------------------------------------------------------
    // Wrapper classes + attributes.
    // ------------------------------------------------------------------------
    $wrapper_classes = ['tms-icon-wrapper'];
    $wrapper_atts = [];

    // Shape modifier (solid-circle, outline-square, etc.).
    if ( $shape ) {
      $resolved_shape_style = $shape_style ?: 'circle';
      $wrapper_classes[] = 'tms-icon--' . $shape . '-' . $resolved_shape_style;
    }

    // Gradient color style for font icons.
    if ( 'gradient' === $color_style ) {
      $wrapper_classes[] = 'tms-icon--gradient';
    }

    // Entrance animation.
    $animation = $motion ? ( $settings['icon_animation'] ?? '' ) : '';

    if ( $animation ) {
      $wrapper_classes[] = 'tms-block-animation';
      $wrapper_classes[] = 'tms-animation--on-view';
      $wrapper_classes[] = 'tms-animation--hidden';

      $wrapper_atts['tms-block-animation'] = $animation;
      $wrapper_atts['duration'] = $settings['icon_animation_duration']['size'] ?? '1';
      $wrapper_atts['delay'] = $settings['icon_animation_delay']['size'] ?? '0';
    }

    // Hover animation.
    $hover_animation = $motion ? ( $settings['icon_hover_animation'] ?? '' ) : '';

    if ( $hover_animation ) {
      $wrapper_classes[] = 'tms-hover-animation';
      $wrapper_classes[] = 'tms-animation__target';
      $wrapper_atts['tms-hover-animation'] = $hover_animation;

      if ( 'viewCursor' === $hover_animation ) {
        $wrapper_atts['tms-cursor-text'] = $settings['cursor_text'] ?? 'VIEW';
      }
    }

    $wrapper_classes_output = implode( ' ', array_filter( $wrapper_classes ) );
    $wrapper_atts_output = themeasy_html_attributes( $wrapper_atts );

    // ------------------------------------------------------------------------
    // Render attributes.
    // ------------------------------------------------------------------------
    $has_link = !empty( $settings['link']['url'] );

    if ( $has_link ) {
      $this->add_link_attributes( 'link', $settings['link'] );
      themeasy_add_external_link_rel( $this, 'link', $settings['link'] );
    }

    // Icon HTML. The loop animation rides on the icon node itself (the inline
    // SVG root), not on the wrapper: the engine pivots an <svg> with
    // transform-box: fill-box, and "dash" needs the stroke children.
    $icon_atts = [
      'class' => 'tms-icon',
      'aria-hidden' => 'true',
    ];

    $loop_animation = $motion ? ( $settings['icon_loop_animation'] ?? '' ) : '';

    if ( $loop_animation ) {
      $loop_trigger = 'hover' === ( $settings['icon_loop_trigger'] ?? 'scroll' ) ? 'hover' : 'scroll';

      $icon_atts['class'] .= ' tms-icon-animation';
      $icon_atts['data-animation'] = $loop_animation;
      $icon_atts['data-trigger'] = $loop_trigger;

      // Editor opt-in: a control-driven effect also plays on the Elementor
      // canvas. Animated SVG assets without it animate on the live site only.
      $icon_atts['data-editor-preview'] = 'true';

      // A 1em glyph is a poor hover target — arm the replay from the whole
      // icon box (shape included) instead.
      if ( 'hover' === $loop_trigger ) {
        $icon_atts['data-hover-target'] = '.tms-icon-wrapper';
      }

      $loop_duration = $settings['icon_loop_duration']['size'] ?? '';

      if ( '' !== $loop_duration && null !== $loop_duration ) {
        $icon_atts['data-duration'] = (string) $loop_duration;
      }

      // getDelaySeconds() reads a dot-less value as MILLISECONDS (the
      // hardcoded animated SVGs ship data-delay="1200"), so a value in
      // seconds must always carry its decimal point.
      $loop_delay = $settings['icon_loop_delay']['size'] ?? '';

      if ( '' !== $loop_delay && null !== $loop_delay ) {
        $icon_atts['data-delay'] = number_format( (float) $loop_delay, 2, '.', '' );
      }
    }

    $icon_html = themeasy_render_icon_html( $icon, $icon_atts );

    // ------------------------------------------------------------------------
    // Output.
    // ------------------------------------------------------------------------
    ?>
    <div class="<?php echo esc_attr( $wrapper_classes_output ); ?>"<?php echo $wrapper_atts_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>>

      <?php if ( $has_link ) : ?>
        <a <?php $this->print_render_attribute_string( 'link' ); ?>>
      <?php endif; ?>

      <?php if ( $icon_html ) : ?>
        <?php echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>
      <?php endif; ?>

      <?php if ( $has_link ) : ?>
        </a>
      <?php endif; ?>

    </div><!-- /.tms-icon-wrapper -->
    <?php
  }

  /**
   * Render the live preview template used by the Elementor editor.
   *
   * @return void
   */
  protected function content_template() {
    ?>
    <#
      // Themeasy Motion is Pro (render() parity): without it the motion
      // settings read as empty.
      var motion = <?php echo Entitlement::can_use_widgets() ? 'true' : 'false'; ?>;

      // ------------------------------------------------------------------------
      // Settings.
      // ------------------------------------------------------------------------
      var safeUrl = ( window.Themeasy && window.Themeasy.safeUrl )
        ? window.Themeasy.safeUrl
        : function () { return ''; };

      var icon = settings.icon || {};

      if ( ! icon.value ) {
        return;
      }

      var shape      = settings.shape || '';
      var shapeStyle = settings.shape_style || '';
      var colorStyle = settings.icon_color_style || 'solid';

      // ------------------------------------------------------------------------
      // Wrapper classes + attributes.
      // ------------------------------------------------------------------------
      var wrapperClasses = [ 'tms-icon-wrapper' ];
      var wrapperAtts    = {};

      // Shape modifier.
      if ( shape ) {
        var resolvedShapeStyle = shapeStyle || 'circle';
        wrapperClasses.push( 'tms-icon--' + shape + '-' + resolvedShapeStyle );
      }

      // Gradient color style.
      if ( colorStyle === 'gradient' ) {
        wrapperClasses.push( 'tms-icon--gradient' );
      }

      // Entrance animation.
      var animation = motion ? ( settings.icon_animation || '' ) : '';

      if ( animation ) {
        wrapperClasses.push( 'tms-block-animation', 'tms-animation--on-view', 'tms-animation--hidden' );
        wrapperAtts['tms-block-animation'] = animation;
        wrapperAtts['duration'] = ( settings.icon_animation_duration && settings.icon_animation_duration.size )
          ? settings.icon_animation_duration.size
          : '1';
        wrapperAtts['delay'] = ( settings.icon_animation_delay && settings.icon_animation_delay.size )
          ? settings.icon_animation_delay.size
          : '0';
      }

      // Hover animation.
      var hoverAnimation = motion ? ( settings.icon_hover_animation || '' ) : '';

      if ( hoverAnimation ) {
        wrapperClasses.push( 'tms-hover-animation', 'tms-animation__target' );
        wrapperAtts['tms-hover-animation'] = hoverAnimation;

        if ( hoverAnimation === 'viewCursor' ) {
          wrapperAtts['tms-cursor-text'] = settings.cursor_text || 'VIEW';
        }
      }

      var wrapperClassStr = wrapperClasses.filter( Boolean ).join( ' ' );

      // ------------------------------------------------------------------------
      // Render attributes.
      // ------------------------------------------------------------------------
      // Loop animation rides on the icon node itself (see render()).
      var iconAtts = {
        'class': 'tms-icon',
        'aria-hidden': 'true'
      };

      var loopAnimation = motion ? ( settings.icon_loop_animation || '' ) : '';

      if ( loopAnimation ) {
        var loopTrigger = ( settings.icon_loop_trigger === 'hover' ) ? 'hover' : 'scroll';

        iconAtts['class'] += ' tms-icon-animation';
        iconAtts['data-animation'] = loopAnimation;
        iconAtts['data-trigger'] = loopTrigger;

        // Editor opt-in (see render()).
        iconAtts['data-editor-preview'] = 'true';

        if ( loopTrigger === 'hover' ) {
          iconAtts['data-hover-target'] = '.tms-icon-wrapper';
        }

        var loopDuration = ( settings.icon_loop_duration && settings.icon_loop_duration.size !== undefined )
          ? settings.icon_loop_duration.size
          : '';

        if ( loopDuration !== '' && loopDuration !== null ) {
          iconAtts['data-duration'] = String( loopDuration );
        }

        // Dot-less values read as milliseconds engine-side — always emit the
        // decimal point (mirrors number_format() in render()).
        var loopDelay = ( settings.icon_loop_delay && settings.icon_loop_delay.size !== undefined )
          ? settings.icon_loop_delay.size
          : '';

        if ( loopDelay !== '' && loopDelay !== null ) {
          iconAtts['data-delay'] = Number( loopDelay ).toFixed( 2 );
        }
      }

      var iconMarkup = ( window.Themeasy && window.Themeasy.renderIconMarkup )
        ? window.Themeasy.renderIconMarkup( view, settings.icon, null, iconAtts )
        : '';

      // Scheme-guarded: a javascript:/data: Link URL must never reach the editor DOM.
      var safeLinkUrl = safeUrl( ( settings.link && settings.link.url ) ? settings.link.url : '' );
      var hasLink = !! safeLinkUrl;

      if ( hasLink ) {
        view.addRenderAttribute( 'link', 'href', safeLinkUrl );

        if ( settings.link.is_external ) {
          view.addRenderAttribute( 'link', 'target', '_blank' );
        }

        if ( settings.link.nofollow ) {
          view.addRenderAttribute( 'link', 'rel', 'nofollow' );
        }
      }
    #>

    <div class="{{ wrapperClassStr }}" {{{ Themeasy.htmlAttributes( wrapperAtts ) }}}>

      <# if ( hasLink ) { #>
        <a {{{ view.getRenderAttributeString( 'link' ) }}}>
      <# } #>

      <# if ( iconMarkup ) { #>
        {{{ iconMarkup }}}
      <# } #>

      <# if ( hasLink ) { #>
        </a>
      <# } #>

    </div><!-- /.tms-icon-wrapper -->

    <?php
  }
}
