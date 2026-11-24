<?php
/**
 * Themeasy Elementor Widget: Hotspot Image
 *
 * Interactive image with configurable pins (hotspots) that reveal rich
 * tooltips on hover or click. Supports three skins (dot / marker / numbered),
 * keyboard navigation, ARIA semantics and reduced-motion fallbacks.
 *
 * @package Themeasy
 * @since 1.0.0
 */

namespace Themeasy\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Control_Media;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Utils;
use Elementor\Widget_Base;

/**
 * Hotspot Image widget class.
 */
class HotspotImage extends Widget_Base {
  /**
   * Tooltip placements (the widget default and the per-hotspot override).
   * Mirrored in content_template() — keep the two in step.
   */
  private const TOOLTIP_POSITIONS = ['top', 'right', 'bottom', 'left'];

  public function get_name() {
    return 'themeasy-hotspot-image';
  }

  public function get_title() {
    return esc_html__( 'Hotspot Image', 'themeasy-lite' );
  }

  public function get_icon() {
    return 'eicon-image-hotspot';
  }

  public function get_custom_help_url() {
    return 'https://hub.themeasy.co/docs';
  }

  public function get_categories() {
    return ['themeasy_media_category', 'themeasy_content_category'];
  }

  public function get_keywords() {
    return ['themeasy', 'hotspot', 'image', 'pin', 'marker', 'tooltip', 'interactive'];
  }

  public function get_style_depends() {
    return ['themeasy-shared', 'themeasy-hotspot-image'];
  }

  public function get_script_depends() {
    return ['themeasy-hotspot-image'];
  }

  /**
   * Register widget customization controls.
   *
   * @return void
   */
  protected function register_controls() {
    // ------------------------------------------------------------------------
    // Content section: Image
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'hotspot_image_image_section',
        [
          'label' => esc_html__( 'Image', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
    );

      $this->add_control(
        'image',
        [
          'label' => esc_html__( 'Image', 'themeasy-lite' ),
          'type' => Controls_Manager::MEDIA,
          'default' => [
            'url' => Utils::get_placeholder_image_src(),
          ],
        ]
      );

      $this->add_group_control( // image_resolution
        Group_Control_Image_Size::get_type(),
        [
          'name' => 'image_resolution',
          'exclude' => ['custom'],
          'include' => [],
          'default' => 'large',
        ]
      );

      $this->add_control(
        'image_loading_priority',
        [
          'label' => esc_html__( 'Loading Behavior', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'lazy',
          'options' => [
            'lazy' => esc_html__( 'Load when needed (Lazy Loading)', 'themeasy-lite' ),
            'high' => esc_html__( 'Prioritize loading (Above the Fold)', 'themeasy-lite' ),
          ],
        ]
      );

      $this->add_responsive_control(
        'image_max_width',
        [
          'label' => esc_html__( 'Max Width', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', '%', 'rem', 'vw'],
          'range' => [
            'px' => ['min' => 120, 'max' => 1920, 'step' => 1],
            '%' => ['min' => 10, 'max' => 100, 'step' => 1],
            'vw' => ['min' => 10, 'max' => 100, 'step' => 1],
          ],
          'default' => ['unit' => '%', 'size' => 100],
          'selectors' => [
            '{{WRAPPER}} .tms-hotspot-image__frame' => 'max-width: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Hotspots
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'hotspot_image_hotspots_section',
        [
          'label' => esc_html__( 'Hotspots', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
    );

      $repeater = new Repeater();

      $repeater->add_control(
        'hotspot_label',
        [
          'label' => esc_html__( 'Admin Label', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'Hotspot', 'themeasy-lite' ),
          'description' => esc_html__( 'Internal label (editor only).', 'themeasy-lite' ),
        ]
      );

      $repeater->add_control(
        'hotspot_x',
        [
          'label' => esc_html__( 'Horizontal (X)', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['%'],
          'range' => ['%' => ['min' => 0, 'max' => 100, 'step' => 0.1]],
          'default' => ['unit' => '%', 'size' => 50],
        ]
      );

      $repeater->add_control(
        'hotspot_y',
        [
          'label' => esc_html__( 'Vertical (Y)', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['%'],
          'range' => ['%' => ['min' => 0, 'max' => 100, 'step' => 0.1]],
          'default' => ['unit' => '%', 'size' => 50],
        ]
      );

      $repeater->add_control(
        'hotspot_tooltip_position',
        [
          'label' => esc_html__( 'Tooltip Position', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => '',
          'options' => [
            '' => esc_html__( 'Default', 'themeasy-lite' ),
            'top' => esc_html__( 'Top', 'themeasy-lite' ),
            'right' => esc_html__( 'Right', 'themeasy-lite' ),
            'bottom' => esc_html__( 'Bottom', 'themeasy-lite' ),
            'left' => esc_html__( 'Left', 'themeasy-lite' ),
          ],
          'description' => esc_html__( 'Overrides the widget\'s Tooltip Position for this hotspot.', 'themeasy-lite' ),
        ]
      );

      $repeater->add_control(
        'hotspot_number',
        [
          'label' => esc_html__( 'Number / Label', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'default' => '',
          'placeholder' => esc_html__( 'Leave empty to auto-number', 'themeasy-lite' ),
        ]
      );

      $repeater->add_control(
        'hotspot_icon',
        [
          'label' => esc_html__( 'Icon', 'themeasy-lite' ),
          'type' => Controls_Manager::ICONS,
          'default' => [
            'value' => 'ty-feather-map-pin',
            'library' => 'ty-feather',
          ],
        ]
      );

      $repeater->add_control(
        'tooltip_heading',
        [
          'label' => esc_html__( 'Tooltip Heading', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'label_block' => true,
          'default' => esc_html__( 'Feature Title', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
        ]
      );

      $repeater->add_control(
        'tooltip_description',
        [
          'label' => esc_html__( 'Tooltip Description', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXTAREA,
          'rows' => 3,
          'default' => esc_html__( 'Short description explaining the feature or point of interest.', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
        ]
      );

      $repeater->add_control(
        'tooltip_image',
        [
          'label' => esc_html__( 'Tooltip Image', 'themeasy-lite' ),
          'type' => Controls_Manager::MEDIA,
          'default' => ['url' => ''],
        ]
      );

      $repeater->add_control(
        'tooltip_link',
        [
          'label' => esc_html__( 'Link', 'themeasy-lite' ),
          'type' => Controls_Manager::URL,
          'label_block' => true,
          'placeholder' => esc_html__( 'https://your-link.com', 'themeasy-lite' ),
          'options' => ['url', 'is_external', 'nofollow'],
          'default' => [
            'url' => '',
            'is_external' => false,
            'nofollow' => false,
          ],
        ]
      );

      $repeater->add_control(
        'tooltip_link_label',
        [
          'label' => esc_html__( 'Link Label', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'Learn More', 'themeasy-lite' ),
          'condition' => [
            'tooltip_link[url]!' => '',
          ],
        ]
      );

      $this->add_control(
        'hotspots',
        [
          'label' => esc_html__( 'Hotspots', 'themeasy-lite' ),
          'type' => Controls_Manager::REPEATER,
          'fields' => $repeater->get_controls(),
          'default' => [
            [
              'hotspot_label' => esc_html__( 'Hotspot 1', 'themeasy-lite' ),
              'hotspot_x' => ['unit' => '%', 'size' => 30],
              'hotspot_y' => ['unit' => '%', 'size' => 40],
              'tooltip_heading' => esc_html__( 'Premium materials', 'themeasy-lite' ),
              'tooltip_description' => esc_html__( 'Crafted from high-grade aluminum with a matte finish.', 'themeasy-lite' ),
            ],
            [
              'hotspot_label' => esc_html__( 'Hotspot 2', 'themeasy-lite' ),
              'hotspot_x' => ['unit' => '%', 'size' => 65],
              'hotspot_y' => ['unit' => '%', 'size' => 65],
              'tooltip_heading' => esc_html__( 'Precision engineering', 'themeasy-lite' ),
              'tooltip_description' => esc_html__( 'Every detail refined for a flawless fit and finish.', 'themeasy-lite' ),
            ],
          ],
          'title_field' => '{{{ hotspot_label }}}',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Settings
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'hotspot_image_settings_section',
        [
          'label' => esc_html__( 'Settings', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
    );

      $this->add_control(
        'skin',
        [
          'label' => esc_html__( 'Hotspot Skin', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'dot',
          'options' => [
            'dot' => esc_html__( 'Dot (Pulse)', 'themeasy-lite' ),
            'marker' => esc_html__( 'Marker (Icon)', 'themeasy-lite' ),
            'numbered' => esc_html__( 'Numbered', 'themeasy-lite' ),
          ],
          'description' => esc_html__( 'Choose how each hotspot is displayed on the image.', 'themeasy-lite' ),
        ]
      );

      $this->add_control(
        'trigger',
        [
          'label' => esc_html__( 'Trigger', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'hover',
          'options' => [
            'hover' => esc_html__( 'Hover / Focus', 'themeasy-lite' ),
            'click' => esc_html__( 'Click / Tap', 'themeasy-lite' ),
          ],
          'description' => esc_html__( 'Hover uses CSS; click uses keyboard-accessible JS (Enter / Space / Esc).', 'themeasy-lite' ),
        ]
      );

      $this->add_control(
        'tooltip_position',
        [
          'label' => esc_html__( 'Tooltip Position', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'top',
          'options' => [
            'top' => esc_html__( 'Top', 'themeasy-lite' ),
            'right' => esc_html__( 'Right', 'themeasy-lite' ),
            'bottom' => esc_html__( 'Bottom', 'themeasy-lite' ),
            'left' => esc_html__( 'Left', 'themeasy-lite' ),
          ],
          'description' => esc_html__( 'Default for every hotspot. Tooltips flip to the opposite side when they would leave the screen.', 'themeasy-lite' ),
        ]
      );

      $this->add_control(
        'pulse_animation',
        [
          'label' => esc_html__( 'Pulse Animation', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'On', 'themeasy-lite' ),
          'label_off' => esc_html__( 'Off', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
          'condition' => [
            'skin' => 'dot',
          ],
        ]
      );

      $this->add_control(
        'show_arrow',
        [
          'label' => esc_html__( 'Tooltip Arrow', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Show', 'themeasy-lite' ),
          'label_off' => esc_html__( 'Hide', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Box
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'hotspot_image_box_styles_section',
        [
          'label' => esc_html__( 'Box', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      $this->add_control(
        'box_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', '%'],
          'selectors' => [
            '{{WRAPPER}} .tms-hotspot-image__img'
              => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control( // box_border
        Group_Control_Border::get_type(),
        [
          'name' => 'box_border',
          'selector' => '{{WRAPPER}} .tms-hotspot-image__img',
        ]
      );

      $this->add_group_control( // box_shadow
        Group_Control_Box_Shadow::get_type(),
        [
          'name' => 'box_shadow',
          'label' => esc_html__( 'Box Shadow', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-hotspot-image__img',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Hotspot
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'hotspot_image_hotspot_styles_section',
        [
          'label' => esc_html__( 'Hotspot', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      // Color (Normal / Hover).
      $this->start_controls_tabs( 'hotspot_state_tabs' );

        $this->start_controls_tab(
          'hotspot_normal_tab',
          [
            'label' => esc_html__( 'Normal', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'hotspot_background_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                // The var feeds the default pulse color (the pin's own fill).
                '{{WRAPPER}} .tms-hotspot-image__hotspot'
                  => 'background-color: {{VALUE}}; --tms-hotspot-bg: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            'hotspot_icon_color',
            [
              'label' => esc_html__( 'Icon / Number Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-hotspot-image__hotspot-icon,
                 {{WRAPPER}} .tms-hotspot-image__hotspot-number' => 'color: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            'hotspot_icon_fill_color',
            [
              'label' => esc_html__( 'Icon Fill Color', 'themeasy-lite' ),
              'description' => esc_html__( 'Applies to SVG icons only.', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-hotspot-image__hotspot-icon svg' => 'fill: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            'hotspot_icon_stroke_color',
            [
              'label' => esc_html__( 'Icon Stroke Color', 'themeasy-lite' ),
              'description' => esc_html__( 'Applies to SVG icons only.', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-hotspot-image__hotspot-icon svg' => 'stroke: {{VALUE}};',
              ],
            ]
          );

          $this->add_responsive_control(
            'hotspot_icon_thickness',
            [
              'label' => esc_html__( 'Icon Thickness', 'themeasy-lite' ),
              'description' => esc_html__( 'Applies to SVG icons only.', 'themeasy-lite' ),
              'type' => Controls_Manager::SLIDER,
              'size_units' => ['px'],
              'range' => [
                'px' => ['min' => 0.5, 'max' => 10, 'step' => 0.5],
              ],
              'default' => ['unit' => 'px', 'size' => 2],
              'selectors' => [
                '{{WRAPPER}} .tms-hotspot-image__hotspot-icon svg,
                 {{WRAPPER}} .tms-hotspot-image__hotspot-icon svg *' => 'stroke-width: {{SIZE}}{{UNIT}};',
              ],
            ]
          );

        $this->end_controls_tab();

        $this->start_controls_tab(
          'hotspot_hover_tab',
          [
            'label' => esc_html__( 'Hover / Active', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'hotspot_hover_background_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-hotspot-image__hotspot:hover,
                 {{WRAPPER}} .tms-hotspot-image__hotspot:focus-visible,
                 {{WRAPPER}} .tms-hotspot-image__hotspot--open'
                  => 'background-color: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            'hotspot_hover_icon_color',
            [
              'label' => esc_html__( 'Icon / Number Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-hotspot-image__hotspot:hover .tms-hotspot-image__hotspot-icon,
                 {{WRAPPER}} .tms-hotspot-image__hotspot:hover .tms-hotspot-image__hotspot-number,
                 {{WRAPPER}} .tms-hotspot-image__hotspot:focus-visible .tms-hotspot-image__hotspot-icon,
                 {{WRAPPER}} .tms-hotspot-image__hotspot:focus-visible .tms-hotspot-image__hotspot-number,
                 {{WRAPPER}} .tms-hotspot-image__hotspot--open .tms-hotspot-image__hotspot-icon,
                 {{WRAPPER}} .tms-hotspot-image__hotspot--open .tms-hotspot-image__hotspot-number'
                  => 'color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

      // Typography.
      $this->add_group_control( // hotspot_number_typography
        Group_Control_Typography::get_type(),
        [
          'name' => 'hotspot_number_typography',
          'label' => esc_html__( 'Number Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-hotspot-image__hotspot-number',
          'separator' => 'before',
          'condition' => [
            'skin' => 'numbered',
          ],
        ]
      );

      // Spacing / Dimensions.
      $this->add_responsive_control(
        'hotspot_size',
        [
          'label' => esc_html__( 'Size', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem', 'em'],
          'range' => [
            'px' => ['min' => 8, 'max' => 120, 'step' => 1],
            'rem' => ['min' => 0.5, 'max' => 8, 'step' => 0.1],
          ],
          'default' => ['unit' => 'px', 'size' => 24],
          'separator' => 'before',
          'selectors' => [
            '{{WRAPPER}} .tms-hotspot-image__point' => '--tms-hotspot-size: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'hotspot_icon_size',
        [
          'label' => esc_html__( 'Icon Size', 'themeasy-lite' ),
          'description' => esc_html__( 'Leave empty to scale with the hotspot size.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem', 'em'],
          'range' => [
            'px' => ['min' => 6, 'max' => 80, 'step' => 1],
            'rem' => ['min' => 0.4, 'max' => 5, 'step' => 0.1],
            'em' => ['min' => 0.4, 'max' => 5, 'step' => 0.1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-hotspot-image__hotspot-icon' => 'font-size: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'skin' => 'marker',
          ],
        ]
      );

      $this->add_control(
        'hotspot_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', '%'],
          'default' => [
            'top' => 50, 'right' => 50, 'bottom' => 50, 'left' => 50, 'unit' => '%', 'isLinked' => true,
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-hotspot-image__hotspot'
              => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      // Border.
      $this->add_group_control( // hotspot_border
        Group_Control_Border::get_type(),
        [
          'name' => 'hotspot_border',
          'selector' => '{{WRAPPER}} .tms-hotspot-image__hotspot',
          'separator' => 'before',
        ]
      );

      // Box Shadow.
      $this->add_group_control( // hotspot_box_shadow
        Group_Control_Box_Shadow::get_type(),
        [
          'name' => 'hotspot_box_shadow',
          'label' => esc_html__( 'Box Shadow', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-hotspot-image__hotspot',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Pulse
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'hotspot_image_pulse_styles_section',
        [
          'label' => esc_html__( 'Pulse', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'skin' => 'dot',
            'pulse_animation' => 'yes',
          ],
        ]
    );

      $this->add_control(
        'pulse_color',
        [
          'label' => esc_html__( 'Pulse Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-hotspot-image__point' => '--tms-hotspot-pulse-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'pulse_duration',
        [
          'label' => esc_html__( 'Duration', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['s'],
          'range' => ['s' => ['min' => 0.5, 'max' => 5, 'step' => 0.1]],
          'default' => ['unit' => 's', 'size' => 1.8],
          'selectors' => [
            '{{WRAPPER}} .tms-hotspot-image__point' => '--tms-hotspot-pulse-duration: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Tooltip
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'hotspot_image_tooltip_styles_section',
        [
          'label' => esc_html__( 'Tooltip', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      // Color.
      $this->add_control(
        'tooltip_background_color',
        [
          'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-hotspot-image__tooltip' => '--tms-hotspot-tooltip-bg: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'tooltip_heading_color',
        [
          'label' => esc_html__( 'Heading Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-hotspot-image__tooltip-heading' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'tooltip_text_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-hotspot-image__tooltip,
             {{WRAPPER}} .tms-hotspot-image__tooltip-description' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'tooltip_link_color',
        [
          'label' => esc_html__( 'Link Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-hotspot-image__tooltip-link' => 'color: {{VALUE}};',
          ],
        ]
      );

      // Typography.
      $this->add_group_control( // tooltip_heading_typography
        Group_Control_Typography::get_type(),
        [
          'name' => 'tooltip_heading_typography',
          'label' => esc_html__( 'Heading Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-hotspot-image__tooltip-heading',
          'separator' => 'before',
        ]
      );

      $this->add_group_control( // tooltip_description_typography
        Group_Control_Typography::get_type(),
        [
          'name' => 'tooltip_description_typography',
          'label' => esc_html__( 'Description Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-hotspot-image__tooltip-description',
        ]
      );

      $this->add_group_control( // tooltip_link_typography
        Group_Control_Typography::get_type(),
        [
          'name' => 'tooltip_link_typography',
          'label' => esc_html__( 'Link Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-hotspot-image__tooltip-link',
        ]
      );

      // Spacing / Dimensions.
      $this->add_responsive_control(
        'tooltip_max_width',
        [
          'label' => esc_html__( 'Max Width', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem'],
          'range' => [
            'px' => ['min' => 120, 'max' => 600, 'step' => 1],
            'rem' => ['min' => 8, 'max' => 40, 'step' => 0.1],
          ],
          'default' => ['unit' => 'px', 'size' => 260],
          'separator' => 'before',
          'selectors' => [
            '{{WRAPPER}} .tms-hotspot-image__tooltip' => 'max-width: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'tooltip_offset',
        [
          'label' => esc_html__( 'Offset', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem'],
          'range' => [
            'px' => ['min' => 0, 'max' => 60, 'step' => 1],
            'rem' => ['min' => 0, 'max' => 4, 'step' => 0.1],
          ],
          'default' => ['unit' => 'px', 'size' => 14],
          'selectors' => [
            '{{WRAPPER}} .tms-hotspot-image__point' => '--tms-hotspot-tooltip-offset: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'tooltip_padding',
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', 'em'],
          'default' => [
            'top' => 14, 'right' => 16, 'bottom' => 14, 'left' => 16, 'unit' => 'px', 'isLinked' => false,
          ],
          'selectors' => [
            // The vars let the tooltip image bleed to the edges at any padding.
            '{{WRAPPER}} .tms-hotspot-image__tooltip'
              => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
                . ' --tms-hotspot-tooltip-padding-top: {{TOP}}{{UNIT}};'
                . ' --tms-hotspot-tooltip-padding-right: {{RIGHT}}{{UNIT}};'
                . ' --tms-hotspot-tooltip-padding-left: {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'tooltip_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', '%'],
          'default' => [
            'top' => 8, 'right' => 8, 'bottom' => 8, 'left' => 8, 'unit' => 'px', 'isLinked' => true,
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-hotspot-image__tooltip'
              => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      // Box Shadow.
      $this->add_group_control( // tooltip_box_shadow
        Group_Control_Box_Shadow::get_type(),
        [
          'name' => 'tooltip_box_shadow',
          'label' => esc_html__( 'Box Shadow', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-hotspot-image__tooltip',
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
    $image_url = $settings['image']['url'] ?? '';

    // Early return if no image.
    if ( empty( $image_url ) ) {
      return;
    }

    $skin = $settings['skin'] ?? 'dot';
    $trigger = $settings['trigger'] ?? 'hover';
    $tooltip_position = in_array( $settings['tooltip_position'] ?? '', self::TOOLTIP_POSITIONS, true )
      ? $settings['tooltip_position']
      : 'top';
    $pulse = ( $settings['pulse_animation'] ?? '' ) === 'yes';
    $show_arrow = ( $settings['show_arrow'] ?? 'yes' ) === 'yes';
    $hotspots = !empty( $settings['hotspots'] ) && is_array( $settings['hotspots'] )
      ? $settings['hotspots']
      : [];

    $image_id = absint( $settings['image']['id'] ?? 0 );
    $image_alt = Control_Media::get_image_alt( $settings['image'] );
    $resolution = $settings['image_resolution_size'] ?? 'large';
    $priority = $settings['image_loading_priority'] ?? 'lazy';

    // ------------------------------------------------------------------------
    // Wrapper classes + attributes.
    // ------------------------------------------------------------------------
    $wrapper_classes = [
      'tms-hotspot-image',
      'tms-hotspot-image--skin-' . sanitize_html_class( $skin ),
      'tms-hotspot-image--trigger-' . sanitize_html_class( $trigger ),
    ];

    if ( 'dot' === $skin && $pulse ) {
      $wrapper_classes[] = 'tms-hotspot-image--pulse';
    }

    if ( !$show_arrow ) {
      $wrapper_classes[] = 'tms-hotspot-image--no-arrow';
    }

    $wrapper_atts = [
      'data-skin' => $skin,
      'data-trigger' => $trigger,
    ];

    $wrapper_classes_output = implode( ' ', array_filter( $wrapper_classes ) );

    // ------------------------------------------------------------------------
    // Output.
    // ------------------------------------------------------------------------
    ?>
    <div class="<?php echo esc_attr( $wrapper_classes_output ); ?>"<?php themeasy_print_html_attributes( $wrapper_atts ); ?>>

      <div class="tms-hotspot-image__frame">

        <?php
        themeasy_render_attachment_image(
          $image_id,
          $resolution,
          $image_url,
          $image_alt,
          'tms-hotspot-image__img',
          [],
          $priority
        );
        ?>

        <?php if ( !empty( $hotspots ) ) : ?>
          <div class="tms-hotspot-image__hotspots">
            <?php foreach ( $hotspots as $index => $hotspot ) : ?>
              <?php $this->render_hotspot( $index, $hotspot, $skin, $trigger, $tooltip_position ); ?>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

      </div><!-- /.tms-hotspot-image__frame -->

    </div><!-- /.tms-hotspot-image -->
    <?php
  }

  /**
   * Render a single hotspot + tooltip.
   *
   * @param int    $index            Repeater index.
   * @param array  $hotspot          Hotspot settings.
   * @param string $skin             Widget skin.
   * @param string $trigger          Widget trigger.
   * @param string $tooltip_position Widget default tooltip position (allowlisted).
   * @return void
   */
  private function render_hotspot( int $index, array $hotspot, string $skin, string $trigger, string $tooltip_position ): void {
    // ------------------------------------------------------------------------
    // Settings.
    // ------------------------------------------------------------------------

    // Per-hotspot override; empty (Default) or anything unknown falls back.
    $item_position = $hotspot['hotspot_tooltip_position'] ?? '';

    if ( in_array( $item_position, self::TOOLTIP_POSITIONS, true ) ) {
      $tooltip_position = $item_position;
    }

    $pos_x = isset( $hotspot['hotspot_x']['size'] )
      ? max( 0, min( 100, floatval( $hotspot['hotspot_x']['size'] ) ) )
      : 50;
    $pos_y = isset( $hotspot['hotspot_y']['size'] )
      ? max( 0, min( 100, floatval( $hotspot['hotspot_y']['size'] ) ) )
      : 50;

    $heading = $hotspot['tooltip_heading'] ?? '';
    $description = $hotspot['tooltip_description'] ?? '';
    $number = !empty( $hotspot['hotspot_number'] )
      ? (string) $hotspot['hotspot_number']
      : (string) ( $index + 1 );
    $icon = is_array( $hotspot['hotspot_icon'] ?? null ) ? $hotspot['hotspot_icon'] : [];
    $link = $hotspot['tooltip_link'] ?? [];
    $link_label = $hotspot['tooltip_link_label'] ?? '';
    $tooltip_img = $hotspot['tooltip_image'] ?? [];

    $has_link = !empty( $link['url'] );
    $tooltip_id = 'tms-hotspot-tip-' . $this->get_id() . '-' . (int) $index;

    // ------------------------------------------------------------------------
    // Render attributes.
    // ------------------------------------------------------------------------
    $hotspot_classes = [
      'tms-hotspot-image__hotspot',
      'tms-hotspot-image__hotspot--' . sanitize_html_class( $tooltip_position ),
    ];

    $hotspot_atts = [
      'type' => 'button',
      'class' => implode( ' ', array_filter( $hotspot_classes ) ),
      'aria-label' => !empty( $heading )
        ? $heading
        : sprintf( /* translators: %d: hotspot number */ esc_html__( 'Hotspot %d', 'themeasy-lite' ), $index + 1 ),
      'aria-controls' => $tooltip_id,
      'aria-expanded' => 'click' === $trigger ? 'false' : null,
      'aria-describedby' => 'hover' === $trigger ? $tooltip_id : null,
      'data-position' => $tooltip_position,
      'data-trigger' => $trigger,
    ];

    // Inline editing for repeater text fields.
    $heading_key = $this->get_repeater_setting_key( 'tooltip_heading', 'hotspots', $index );
    $this->add_render_attribute( $heading_key, 'class', 'tms-hotspot-image__tooltip-heading' );
    $this->add_inline_editing_attributes( $heading_key, 'none' );

    $description_key = $this->get_repeater_setting_key( 'tooltip_description', 'hotspots', $index );
    $this->add_render_attribute( $description_key, 'class', 'tms-hotspot-image__tooltip-description' );
    $this->add_inline_editing_attributes( $description_key, 'advanced' );

    $point_style = sprintf( 'left:%s%%;top:%s%%;', (string) $pos_x, (string) $pos_y );

    // ------------------------------------------------------------------------
    // Output.
    // ------------------------------------------------------------------------
    ?>
    <div class="tms-hotspot-image__point" style="<?php echo esc_attr( $point_style ); ?>">
      <button<?php themeasy_print_html_attributes( $hotspot_atts ); ?>>
        <span class="tms-hotspot-image__hotspot-ring" aria-hidden="true"></span>
        <span class="tms-hotspot-image__hotspot-inner" aria-hidden="true">
          <?php if ( 'marker' === $skin ) : ?>
            <?php
            $icon_html = themeasy_render_icon_html( $icon );

            if ( $icon_html ) {
              ?>
              <span class="tms-hotspot-image__hotspot-icon" aria-hidden="true">
                <?php echo wp_kses( $icon_html, themeasy_get_icon_kses_tags() ); ?>
              </span>
              <?php
            }
            ?>
          <?php elseif ( 'numbered' === $skin ) : ?>
            <span class="tms-hotspot-image__hotspot-number">
              <?php echo esc_html( $number ); ?>
            </span>
          <?php endif; ?>
        </span>
      </button>

      <div class="tms-hotspot-image__tooltip"
           id="<?php echo esc_attr( $tooltip_id ); ?>"
           role="tooltip"
           data-position="<?php echo esc_attr( $tooltip_position ); ?>">

        <?php if ( !empty( $tooltip_img['url'] ) ) : ?>
          <div class="tms-hotspot-image__tooltip-media">
            <?php
            $tooltip_img_id = absint( $tooltip_img['id'] ?? 0 );
            $tooltip_img_alt = Control_Media::get_image_alt( $tooltip_img );

            themeasy_render_attachment_image(
              $tooltip_img_id,
              'medium',
              $tooltip_img['url'],
              $tooltip_img_alt,
              'tms-hotspot-image__tooltip-img',
              [],
              'lazy'
            );
            ?>
          </div>
        <?php endif; ?>

        <?php if ( $heading ) : ?>
          <h4 <?php $this->print_render_attribute_string( $heading_key ); ?>>
            <?php echo esc_html( $heading ); ?>
          </h4>
        <?php endif; ?>

        <?php if ( $description ) : ?>
          <div <?php $this->print_render_attribute_string( $description_key ); ?>>
            <?php echo wp_kses_post( $description ); ?>
          </div>
        <?php endif; ?>

        <?php if ( $has_link && !empty( $link_label ) ) : ?>
          <?php
          $link_key = 'hotspot_link_' . $index;
          $this->add_link_attributes( $link_key, $link );
          themeasy_add_external_link_rel( $this, $link_key, $link );
          ?>
          <a <?php $this->print_render_attribute_string( $link_key ); ?>
             class="tms-hotspot-image__tooltip-link">
            <?php echo esc_html( $link_label ); ?>
          </a>
        <?php endif; ?>

        <span class="tms-hotspot-image__tooltip-arrow" aria-hidden="true"></span>
      </div><!-- /.tms-hotspot-image__tooltip -->
    </div><!-- /.tms-hotspot-image__point -->
    <?php
  }

  /**
   * Render the editor live preview template.
   *
   * @return void
   */
  protected function content_template() {
    ?>
    <#
      // ------------------------------------------------------------------------
      // Settings.
      // ------------------------------------------------------------------------
      // Scheme guard for every href/src below. The regex lives in exactly one
      // place; the fallback fails closed rather than duplicating it (a bare ^
      // anchor misses the C0 controls browsers strip before resolving a scheme).
      var safeUrl = ( window.Themeasy && window.Themeasy.safeUrl )
        ? window.Themeasy.safeUrl
        : function () { return ''; };

      // Kses-parity sanitizer for user text printed raw ({{{ }}}).
      var sanitizeInline = ( window.Themeasy && window.Themeasy.sanitizeInlineHtml )
        ? window.Themeasy.sanitizeInlineHtml
        : _.escape;

      var imageUrl = safeUrl( ( settings.image && settings.image.url ) ? settings.image.url : '' );

      // No usable image (unset, or a scheme the guard blocked) — render nothing.
      if ( ! imageUrl ) {
        return;
      }

      var skin             = settings.skin || 'dot';
      var trigger          = settings.trigger || 'hover';
      // Mirrors TOOLTIP_POSITIONS (PHP).
      var tooltipPositions = [ 'top', 'right', 'bottom', 'left' ];
      var tooltipPosition  = tooltipPositions.indexOf( settings.tooltip_position ) !== -1
        ? settings.tooltip_position
        : 'top';
      var pulse            = settings.pulse_animation === 'yes';
      var showArrow        = settings.show_arrow === 'yes';
      var hotspots         = settings.hotspots || [];
      var imageAlt         = ( settings.image && settings.image.alt ) ? settings.image.alt : '';

      // ------------------------------------------------------------------------
      // Wrapper classes + attributes.
      // ------------------------------------------------------------------------
      var wrapperClasses = [
        'tms-hotspot-image',
        'tms-hotspot-image--skin-' + skin,
        'tms-hotspot-image--trigger-' + trigger
      ];

      if ( skin === 'dot' && pulse ) {
        wrapperClasses.push( 'tms-hotspot-image--pulse' );
      }

      if ( ! showArrow ) {
        wrapperClasses.push( 'tms-hotspot-image--no-arrow' );
      }

      var wrapperAtts = {
        'data-skin': skin,
        'data-trigger': trigger
      };

      var wrapperClassStr = wrapperClasses.filter( Boolean ).join( ' ' );

      var widgetId = view.getID ? view.getID() : '';

      var hotspotFallbackLabel = <?php echo wp_json_encode( __( 'Hotspot', 'themeasy-lite' ) ); ?>;
    #>

    <div class="{{ wrapperClassStr }}" {{{ Themeasy.htmlAttributes( wrapperAtts ) }}}>

      <div class="tms-hotspot-image__frame">

        <img src="{{ imageUrl }}" alt="{{ imageAlt }}" class="tms-hotspot-image__img" />

        <# if ( hotspots.length ) { #>
          <div class="tms-hotspot-image__hotspots">

            <# _.each( hotspots, function( hotspot, index ) {
              var posX = ( hotspot.hotspot_x && typeof hotspot.hotspot_x.size !== 'undefined' )
                ? Math.max( 0, Math.min( 100, parseFloat( hotspot.hotspot_x.size ) ) )
                : 50;
              var posY = ( hotspot.hotspot_y && typeof hotspot.hotspot_y.size !== 'undefined' )
                ? Math.max( 0, Math.min( 100, parseFloat( hotspot.hotspot_y.size ) ) )
                : 50;

              // Per-hotspot override; empty (Default) or unknown falls back.
              var itemPosition = tooltipPositions.indexOf( hotspot.hotspot_tooltip_position ) !== -1
                ? hotspot.hotspot_tooltip_position
                : tooltipPosition;

              var heading     = hotspot.tooltip_heading || '';
              var description = sanitizeInline( hotspot.tooltip_description || '', 'rich' );
              var numberRaw   = hotspot.hotspot_number || '';
              var number      = numberRaw !== '' ? numberRaw : String( index + 1 );
              var iconSetting = hotspot.hotspot_icon || {};
              var link        = hotspot.tooltip_link || {};

              // A blocked scheme yields '' — the tooltip link is then dropped
              // entirely, the same as an unset link.
              var linkHref    = safeUrl( link.url || '' );
              var hasLink     = !! linkHref;
              var linkLabel   = hotspot.tooltip_link_label || '';
              var tooltipImg  = hotspot.tooltip_image || {};
              var tooltipImgUrl = safeUrl( tooltipImg.url || '' );
              var tooltipImgAlt = tooltipImg.alt || '';
              var tooltipId   = 'tms-hotspot-tip-' + widgetId + '-' + index;

              var hotspotClasses = [
                'tms-hotspot-image__hotspot',
                'tms-hotspot-image__hotspot--' + itemPosition
              ];

              var ariaLabel = heading ? heading : ( hotspotFallbackLabel + ' ' + ( index + 1 ) );

              var hotspotAtts = {
                'type': 'button',
                'class': hotspotClasses.join( ' ' ),
                'aria-label': ariaLabel,
                'aria-controls': tooltipId,
                'data-position': itemPosition,
                'data-trigger': trigger
              };

              if ( trigger === 'click' ) {
                hotspotAtts['aria-expanded'] = 'false';
              } else {
                hotspotAtts['aria-describedby'] = tooltipId;
              }

              var pointStyle = 'left:' + posX + '%;top:' + posY + '%;';

              var headingKey = view.getRepeaterSettingKey( 'tooltip_heading', 'hotspots', index );
              view.addRenderAttribute( headingKey, 'class', 'tms-hotspot-image__tooltip-heading' );
              view.addInlineEditingAttributes( headingKey, 'none' );

              var descriptionKey = view.getRepeaterSettingKey( 'tooltip_description', 'hotspots', index );
              view.addRenderAttribute( descriptionKey, 'class', 'tms-hotspot-image__tooltip-description' );
              view.addInlineEditingAttributes( descriptionKey, 'advanced' );

              var iconMarkup = ( window.Themeasy && window.Themeasy.renderIconMarkup )
                ? window.Themeasy.renderIconMarkup( view, iconSetting, null, {} )
                : '';
            #>

              <div class="tms-hotspot-image__point" style="{{ pointStyle }}">
                <button {{{ Themeasy.htmlAttributes( hotspotAtts ) }}}>
                  <span class="tms-hotspot-image__hotspot-ring" aria-hidden="true"></span>
                  <span class="tms-hotspot-image__hotspot-inner" aria-hidden="true">
                    <# if ( skin === 'marker' && iconMarkup ) { #>
                      <span class="tms-hotspot-image__hotspot-icon" aria-hidden="true">{{{ iconMarkup }}}</span>
                    <# } else if ( skin === 'numbered' ) { #>
                      <span class="tms-hotspot-image__hotspot-number">{{ number }}</span>
                    <# } #>
                  </span>
                </button>

                <div class="tms-hotspot-image__tooltip"
                     id="{{ tooltipId }}"
                     role="tooltip"
                     data-position="{{ itemPosition }}">

                  <# if ( tooltipImgUrl ) { #>
                    <div class="tms-hotspot-image__tooltip-media">
                      <img src="{{ tooltipImgUrl }}"
                           alt="{{ tooltipImgAlt }}"
                           class="tms-hotspot-image__tooltip-img" />
                    </div>
                  <# } #>

                  <# if ( heading ) { #>
                    <h4 {{{ view.getRenderAttributeString( headingKey ) }}}>{{ heading }}</h4>
                  <# } #>

                  <# if ( description ) { #>
                    <div {{{ view.getRenderAttributeString( descriptionKey ) }}}>{{{ description }}}</div>
                  <# } #>

                  <# if ( hasLink && linkLabel ) { #>
                    <a href="{{ linkHref }}" class="tms-hotspot-image__tooltip-link">{{ linkLabel }}</a>
                  <# } #>

                  <span class="tms-hotspot-image__tooltip-arrow" aria-hidden="true"></span>
                </div>
              </div>

            <# } ); #>

          </div>
        <# } #>

      </div>

    </div>

    <?php
  }
}
