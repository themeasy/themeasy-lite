<?php
/**
 * Themeasy Elementor Widget: Slide Button
 *
 * A call-to-action button with a dual-label slide swap: on hover the
 * main label glides up and a second label (or the same text) slides in from
 * below, while an optional circular icon chip inverts its colors and nudges
 * the glyph forward. The whole interaction is pure CSS — no JS dependency —
 * and collapses to a regular static button under reduced motion.
 *
 * @package Themeasy
 * @since 1.0.0
 */

namespace Themeasy\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

/**
 * Responsible for registering controls and rendering the Slide Button widget.
 */
class SlideButton extends Widget_Base {
  public function get_name() {
    return 'themeasy-slide-button';
  }

  public function get_title() {
    return esc_html__( 'Slide Button', 'themeasy-lite' );
  }

  public function get_icon() {
    return 'eicon-button';
  }

  public function get_custom_help_url() {
    return 'https://hub.themeasy.co/docs';
  }

  public function get_categories() {
    return ['themeasy_elements_category', 'themeasy_content_category'];
  }

  public function get_keywords() {
    return ['themeasy', 'button', 'slide', 'cta', 'link', 'hover', 'label swap'];
  }

  public function get_style_depends() {
    return ['themeasy-shared', 'themeasy-slide-button'];
  }

  public function get_script_depends() {
    return [];
  }

  /**
   * Register widget customization controls.
   *
   * @return void
   */
  protected function register_controls() {
    // ------------------------------------------------------------------------
    // Content section: Button
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'slide_button_content_section',
        [
          'label' => esc_html__( 'Button', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $this->add_control(
        'text',
        [
          'label' => esc_html__( 'Label', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'Discover More', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'Type your label here', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
        ]
      );

      $this->add_control(
        'hover_text',
        [
          'label' => esc_html__( 'Hover Label', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXT,
          'default' => '',
          'placeholder' => esc_html__( 'Defaults to the main label', 'themeasy-lite' ),
          'description' => esc_html__(
            'Label that slides in on hover. Keep a similar length to the main label for a clean swap.',
            'themeasy-lite'
          ),
          'dynamic' => ['active' => true],
        ]
      );

      $this->add_control(
        'icon',
        [
          'label' => esc_html__( 'Icon', 'themeasy-lite' ),
          'type' => Controls_Manager::ICONS,
          'default' => [
            'value' => 'ty-feather-arrow-right',
            'library' => 'ty-feather',
          ],
        ]
      );

      $this->add_control(
        'icon_position',
        [
          'label' => esc_html__( 'Icon Position', 'themeasy-lite' ),
          'type' => Controls_Manager::CHOOSE,
          'options' => [
            'left' => [
              'title' => esc_html__( 'Left', 'themeasy-lite' ),
              'icon' => 'eicon-align-start-h',
            ],
            'right' => [
              'title' => esc_html__( 'Right', 'themeasy-lite' ),
              'icon' => 'eicon-align-end-h',
            ],
          ],
          'default' => 'right',
          'toggle' => false,
          'condition' => [
            'icon[value]!' => '',
          ],
        ]
      );

      $this->add_control(
        'link',
        [
          'label' => esc_html__( 'Link', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::URL,
          'placeholder' => esc_html__( 'https://your-link.com', 'themeasy-lite' ),
          'options' => ['url', 'is_external', 'nofollow'],
          'default' => [
            'url' => '#',
            'is_external' => false,
            'nofollow' => false,
          ],
          'dynamic' => ['active' => true],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Layout
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'slide_button_layout_section',
        [
          'label' => esc_html__( 'Layout', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $this->add_responsive_control(
        'alignment',
        [
          'label' => esc_html__( 'Alignment', 'themeasy-lite' ),
          'type' => Controls_Manager::CHOOSE,
          'options' => [
            'flex-start' => [
              'title' => esc_html__( 'Left', 'themeasy-lite' ),
              'icon' => 'eicon-text-align-left',
            ],
            'center' => [
              'title' => esc_html__( 'Center', 'themeasy-lite' ),
              'icon' => 'eicon-text-align-center',
            ],
            'flex-end' => [
              'title' => esc_html__( 'Right', 'themeasy-lite' ),
              'icon' => 'eicon-text-align-right',
            ],
          ],
          'default' => 'flex-start',
          'selectors' => [
            '{{WRAPPER}} .tms-slide-button' => 'justify-content: {{VALUE}};',
          ],
          'condition' => [
            'full_width!' => 'yes',
          ],
        ]
      );

      $this->add_control(
        'full_width',
        [
          'label' => esc_html__( 'Full Width', 'themeasy-lite' ),
          'description' => esc_html__( 'Stretch the button across the container, pushing the icon to the far edge.', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => '',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Button
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'slide_button_styles_section',
        [
          'label' => esc_html__( 'Button', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->start_controls_tabs( 'button_states_tabs' );

        $this->start_controls_tab(
          'button_normal_tab',
          [
            'label' => esc_html__( 'Normal', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'button_background_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-slide-button__link' => 'background-color: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            'button_text_color',
            [
              'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-slide-button__link' => 'color: {{VALUE}};',
              ],
            ]
          );

          $this->add_group_control( // button_border
            Group_Control_Border::get_type(),
            [
              'name' => 'button_border',
              'selector' => '{{WRAPPER}} .tms-slide-button__link',
            ]
          );

          $this->add_group_control( // button_box_shadow
            Group_Control_Box_Shadow::get_type(),
            [
              'name' => 'button_box_shadow',
              'label' => esc_html__( 'Box Shadow', 'themeasy-lite' ),
              'selector' => '{{WRAPPER}} .tms-slide-button__link',
            ]
          );

        $this->end_controls_tab();

        $this->start_controls_tab(
          'button_hover_tab',
          [
            'label' => esc_html__( 'Hover', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'button_background_hover_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-slide-button__link:hover,
                 {{WRAPPER}} .tms-slide-button__link:focus-visible' => 'background-color: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            'button_text_hover_color',
            [
              'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-slide-button__link:hover,
                 {{WRAPPER}} .tms-slide-button__link:focus-visible' => 'color: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            'button_border_hover_color',
            [
              'label' => esc_html__( 'Border Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-slide-button__link:hover,
                 {{WRAPPER}} .tms-slide-button__link:focus-visible' => 'border-color: {{VALUE}};',
              ],
            ]
          );

          $this->add_group_control( // button_box_shadow_hover
            Group_Control_Box_Shadow::get_type(),
            [
              'name' => 'button_box_shadow_hover',
              'label' => esc_html__( 'Box Shadow', 'themeasy-lite' ),
              'selector' => '{{WRAPPER}} .tms-slide-button__link:hover,
                            {{WRAPPER}} .tms-slide-button__link:focus-visible',
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

      $this->add_group_control( // button_typography
        Group_Control_Typography::get_type(),
        [
          'name' => 'button_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-slide-button__label',
          'separator' => 'before',
        ]
      );

      $this->add_responsive_control(
        'button_padding',
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', 'em'],
          'selectors' => [
            '{{WRAPPER}} .tms-slide-button__link'
              => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'button_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', '%'],
          'selectors' => [
            '{{WRAPPER}} .tms-slide-button__link'
              => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'button_transition_duration',
        [
          'label' => esc_html__( 'Transition Duration', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['s'],
          'range' => ['s' => ['min' => 0.1, 'max' => 1.5, 'step' => 0.05]],
          'default' => ['unit' => 's', 'size' => 0.4],
          'separator' => 'before',
          'selectors' => [
            '{{WRAPPER}} .tms-slide-button' => '--tms-slide-button-duration: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Icon
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'slide_button_icon_styles_section',
        [
          'label' => esc_html__( 'Icon', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'icon[value]!' => '',
          ],
        ]
      );

      $this->add_control(
        'show_icon_chip',
        [
          'label' => esc_html__( 'Circular Chip', 'themeasy-lite' ),
          'description' => esc_html__( 'Wrap the icon in a circular chip that inverts its colors on hover.', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
        ]
      );

      $this->start_controls_tabs( 'icon_states_tabs' );

        $this->start_controls_tab(
          'icon_normal_tab',
          [
            'label' => esc_html__( 'Normal', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'icon_color',
            [
              'label' => esc_html__( 'Icon Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-slide-button__icon' => 'color: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            'chip_background_color',
            [
              'label' => esc_html__( 'Chip Background', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-slide-button--chip .tms-slide-button__icon' => 'background-color: {{VALUE}};',
              ],
              'condition' => [
                'show_icon_chip' => 'yes',
              ],
            ]
          );

        $this->end_controls_tab();

        $this->start_controls_tab(
          'icon_hover_tab',
          [
            'label' => esc_html__( 'Hover', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'icon_hover_color',
            [
              'label' => esc_html__( 'Icon Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-slide-button__link:hover .tms-slide-button__icon,
                 {{WRAPPER}} .tms-slide-button__link:focus-visible .tms-slide-button__icon' => 'color: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            'chip_background_hover_color',
            [
              'label' => esc_html__( 'Chip Background', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-slide-button--chip .tms-slide-button__link:hover .tms-slide-button__icon,
                 {{WRAPPER}} .tms-slide-button--chip .tms-slide-button__link:focus-visible .tms-slide-button__icon'
                  => 'background-color: {{VALUE}};',
              ],
              'condition' => [
                'show_icon_chip' => 'yes',
              ],
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

      $this->add_responsive_control(
        'icon_size',
        [
          'label' => esc_html__( 'Icon Size', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem', 'em'],
          'range' => [
            'px' => ['min' => 8, 'max' => 60, 'step' => 1],
            'rem' => ['min' => 0.5, 'max' => 4, 'step' => 0.05],
            'em' => ['min' => 0.5, 'max' => 4, 'step' => 0.05],
          ],
          'separator' => 'before',
          'selectors' => [
            '{{WRAPPER}} .tms-slide-button__icon' => 'font-size: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'chip_size',
        [
          'label' => esc_html__( 'Chip Size', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem', 'em'],
          'range' => [
            'px' => ['min' => 16, 'max' => 120, 'step' => 1],
            'rem' => ['min' => 1, 'max' => 8, 'step' => 0.1],
            'em' => ['min' => 1, 'max' => 6, 'step' => 0.1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-slide-button' => '--tms-slide-button-chip-size: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'show_icon_chip' => 'yes',
          ],
        ]
      );

      $this->add_responsive_control(
        'icon_thickness',
        [
          'label' => esc_html__( 'Icon Thickness', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => ['min' => 0.5, 'max' => 6, 'step' => 0.5],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-slide-button__icon svg,
             {{WRAPPER}} .tms-slide-button__icon svg *' => 'stroke-width: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'icon[library]' => themeasy_svg_icon_condition_libraries(),
          ],
        ]
      );

      $this->add_responsive_control(
        'icon_gap',
        [
          'label' => esc_html__( 'Gap', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem', 'em'],
          'range' => [
            'px' => ['min' => 0, 'max' => 60, 'step' => 1],
            'rem' => ['min' => 0, 'max' => 4, 'step' => 0.1],
            'em' => ['min' => 0, 'max' => 4, 'step' => 0.1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-slide-button__link' => 'gap: {{SIZE}}{{UNIT}};',
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

    // ------------------------------------------------------------------------
    // Settings.
    // ------------------------------------------------------------------------
    $text = $settings['text'] ?? '';
    $hover_text = trim( (string) ( $settings['hover_text'] ?? '' ) );
    $icon_position = ( $settings['icon_position'] ?? 'right' ) === 'left' ? 'left' : 'right';
    $show_chip = ( $settings['show_icon_chip'] ?? 'yes' ) === 'yes';
    $full_width = ( $settings['full_width'] ?? '' ) === 'yes';
    $link = $settings['link'] ?? [];

    $icon_html = themeasy_render_icon_html( is_array( $settings['icon'] ?? null ) ? $settings['icon'] : [] );

    // Early return if there is nothing to render.
    if ( '' === $text && !$icon_html ) {
      return;
    }

    // The hover label falls back to the main label so the swap always lands.
    if ( '' === $hover_text ) {
      $hover_text = $text;
    }

    // ------------------------------------------------------------------------
    // Wrapper classes + attributes.
    // ------------------------------------------------------------------------
    $wrapper_classes = ['tms-slide-button'];
    $wrapper_atts = [];

    if ( $icon_html ) {
      $wrapper_classes[] = 'tms-slide-button--icon-' . $icon_position;

      if ( $show_chip ) {
        $wrapper_classes[] = 'tms-slide-button--chip';
      }
    }

    if ( $full_width ) {
      $wrapper_classes[] = 'tms-slide-button--full';
    }

    $wrapper_classes_output = implode( ' ', array_filter( $wrapper_classes ) );

    // ------------------------------------------------------------------------
    // Render attributes.
    // ------------------------------------------------------------------------
    $this->add_render_attribute( 'link', 'class', 'tms-slide-button__link' );

    if ( !empty( $link['url'] ) ) {
      $this->add_link_attributes( 'link', $link );
      themeasy_add_external_link_rel( $this, 'link', $link );
    }

    $this->add_render_attribute( 'text', 'class', 'tms-slide-button__text tms-slide-button__text--main' );
    $this->add_inline_editing_attributes( 'text', 'none' );

    // ------------------------------------------------------------------------
    // Output.
    // ------------------------------------------------------------------------
    ?>
    <div class="<?php echo esc_attr( $wrapper_classes_output ); ?>"<?php themeasy_print_html_attributes( $wrapper_atts ); ?>>

      <a <?php $this->print_render_attribute_string( 'link' ); ?>>

        <?php if ( '' !== $text ) : ?>
          <span class="tms-slide-button__label">
            <span <?php $this->print_render_attribute_string( 'text' ); ?>>
              <?php echo esc_html( $text ); ?>
            </span>
            <span class="tms-slide-button__text tms-slide-button__text--hover" aria-hidden="true">
              <?php echo esc_html( $hover_text ); ?>
            </span>
          </span>
        <?php endif; ?>

        <?php if ( $icon_html ) : ?>
          <span class="tms-slide-button__icon" aria-hidden="true">
            <?php echo wp_kses( $icon_html, themeasy_get_icon_kses_tags() ); ?>
          </span>
        <?php endif; ?>

      </a>

    </div><!-- /.tms-slide-button -->
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
      var text = settings.text || '';
      var hoverText = ( settings.hover_text || '' ).toString().trim() || text;
      var iconPosition = settings.icon_position === 'left' ? 'left' : 'right';
      var showChip = settings.show_icon_chip === 'yes';
      var fullWidth = settings.full_width === 'yes';
      var link = settings.link || {};

      var iconMarkup = ( window.Themeasy && window.Themeasy.renderIconMarkup )
        ? window.Themeasy.renderIconMarkup( view, settings.icon, null, {} )
        : '';

      if ( ! text && ! iconMarkup ) {
        return;
      }

      // Scheme guard for every href/src below. The regex lives in exactly one
      // place; the fallback fails closed rather than duplicating it (a bare ^
      // anchor misses the C0 controls browsers strip before resolving a scheme).
      var safeUrl = ( window.Themeasy && window.Themeasy.safeUrl )
        ? window.Themeasy.safeUrl
        : function () { return ''; };

      // ------------------------------------------------------------------------
      // Wrapper classes + attributes.
      // ------------------------------------------------------------------------
      var wrapperClasses = [ 'tms-slide-button' ];
      var wrapperAtts    = {};

      if ( iconMarkup ) {
        wrapperClasses.push( 'tms-slide-button--icon-' + iconPosition );

        if ( showChip ) {
          wrapperClasses.push( 'tms-slide-button--chip' );
        }
      }

      if ( fullWidth ) {
        wrapperClasses.push( 'tms-slide-button--full' );
      }

      var wrapperClassStr = wrapperClasses.filter( Boolean ).join( ' ' );

      // Link attributes (mirrors add_link_attributes(): target + rel).
      var linkAtts = { 'class': 'tms-slide-button__link' };
      var linkUrl = safeUrl( link.url || '' );

      if ( linkUrl ) {
        linkAtts.href = linkUrl;
      }

      if ( link.is_external ) {
        linkAtts.target = '_blank';
      }

      if ( link.nofollow ) {
        linkAtts.rel = 'nofollow';
      }

      // ------------------------------------------------------------------------
      // Render attributes.
      // ------------------------------------------------------------------------
      view.addRenderAttribute( 'text', 'class', 'tms-slide-button__text tms-slide-button__text--main' );
      view.addInlineEditingAttributes( 'text', 'none' );
    #>

    <div class="{{ wrapperClassStr }}" {{{ Themeasy.htmlAttributes( wrapperAtts ) }}}>

      <a {{{ Themeasy.htmlAttributes( linkAtts ) }}}>

        <# if ( text ) { #>
          <span class="tms-slide-button__label">
            <span {{{ view.getRenderAttributeString( 'text' ) }}}>{{ text }}</span>
            <span class="tms-slide-button__text tms-slide-button__text--hover" aria-hidden="true">{{ hoverText }}</span>
          </span>
        <# } #>

        <# if ( iconMarkup ) { #>
          <span class="tms-slide-button__icon" aria-hidden="true">{{{ iconMarkup }}}</span>
        <# } #>

      </a>

    </div><!-- /.tms-slide-button -->
    <?php
  }
}
