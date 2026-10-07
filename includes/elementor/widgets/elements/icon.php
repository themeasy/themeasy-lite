<?php
/**
 * Themeasy Elementor Widget: Icon
 *
 * Displays an icon with configurable shape, color style (solid or gradient)
 * and an optional link.
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
    // Advanced section: Motion.
    // ------------------------------------------------------------------------
    themeasy_register_motion_upsell_section( $this );
  }

  /**
   * Render the widget output on the frontend.
   *
   * @return void
   */
  protected function render() {
    $settings = $this->get_settings_for_display();

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

    // Icon HTML.
    $icon_atts = [
      'class' => 'tms-icon',
      'aria-hidden' => 'true',
    ];

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

      var wrapperClassStr = wrapperClasses.filter( Boolean ).join( ' ' );

      // ------------------------------------------------------------------------
      // Render attributes.
      // ------------------------------------------------------------------------
      var iconAtts = {
        'class': 'tms-icon',
        'aria-hidden': 'true'
      };

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
