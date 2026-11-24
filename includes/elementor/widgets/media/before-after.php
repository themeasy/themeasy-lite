<?php
/**
 * Themeasy Elementor Widget: Before / After (Image Comparison)
 *
 * Side-by-side image comparison with a draggable divider. Supports three skins
 * (handle / reveal / hover), horizontal and vertical orientation, optional
 * before/after labels, keyboard navigation, ARIA slider semantics and reduced
 * motion fallback.
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
use Elementor\Utils;
use Elementor\Widget_Base;

/**
 * Before / After widget class.
 */
class BeforeAfter extends Widget_Base {
  public function get_name() {
    return 'themeasy-before-after';
  }

  public function get_title() {
    return esc_html__( 'Before / After', 'themeasy-lite' );
  }

  public function get_icon() {
    return 'eicon-image-before-after';
  }

  public function get_custom_help_url() {
    return 'https://hub.themeasy.co/docs';
  }

  public function get_categories() {
    return ['themeasy_media_category', 'themeasy_content_category'];
  }

  public function get_keywords() {
    return ['themeasy', 'before', 'after', 'comparison', 'image', 'slider', 'reveal'];
  }

  public function get_style_depends() {
    return ['themeasy-shared', 'themeasy-before-after'];
  }

  public function get_script_depends() {
    return ['themeasy-before-after'];
  }

  /**
   * Register widget customization controls.
   *
   * @return void
   */
  protected function register_controls() {
    // ------------------------------------------------------------------------
    // Content section: Before Image
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'before_after_before_section',
        [
          'label' => esc_html__( 'Before Image', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
    );

      $this->add_control(
        'before_image',
        [
          'label' => esc_html__( 'Image', 'themeasy-lite' ),
          'type' => Controls_Manager::MEDIA,
          'default' => [
            'url' => Utils::get_placeholder_image_src(),
          ],
        ]
      );

      $this->add_control(
        'before_label',
        [
          'label' => esc_html__( 'Label', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'Before', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'Leave blank to hide', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: After Image
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'before_after_after_section',
        [
          'label' => esc_html__( 'After Image', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
    );

      $this->add_control(
        'after_image',
        [
          'label' => esc_html__( 'Image', 'themeasy-lite' ),
          'type' => Controls_Manager::MEDIA,
          'default' => [
            'url' => Utils::get_placeholder_image_src(),
          ],
        ]
      );

      $this->add_control(
        'after_label',
        [
          'label' => esc_html__( 'Label', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'After', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'Leave blank to hide', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Layout
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'before_after_layout_section',
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
          'default' => 'handle',
          'options' => [
            'handle' => esc_html__( 'Handle (Draggable)', 'themeasy-lite' ),
            'reveal' => esc_html__( 'Reveal (Minimal Line)', 'themeasy-lite' ),
            'hover' => esc_html__( 'Follow Cursor', 'themeasy-lite' ),
          ],
          'description' => esc_html__( 'Choose how the divider interacts with the user.', 'themeasy-lite' ),
        ]
      );

      $this->add_control(
        'orientation',
        [
          'label' => esc_html__( 'Orientation', 'themeasy-lite' ),
          'type' => Controls_Manager::CHOOSE,
          'options' => [
            'horizontal' => [
              'title' => esc_html__( 'Horizontal (Vertical Divider)', 'themeasy-lite' ),
              'icon' => 'eicon-ellipsis-v',
            ],
            'vertical' => [
              'title' => esc_html__( 'Vertical (Horizontal Divider)', 'themeasy-lite' ),
              'icon' => 'eicon-ellipsis-h',
            ],
          ],
          'default' => 'horizontal',
          'toggle' => false,
        ]
      );

      $this->add_control(
        'start_position',
        [
          'label' => esc_html__( 'Initial Position', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['%'],
          'range' => [
            '%' => ['min' => 0, 'max' => 100, 'step' => 1],
          ],
          'default' => ['unit' => '%', 'size' => 50],
          'description' => esc_html__( 'Starting position of the divider (0-100%).', 'themeasy-lite' ),
        ]
      );

      $this->add_responsive_control(
        'aspect_ratio',
        [
          'label' => esc_html__( 'Aspect Ratio', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => '16-9',
          'options' => [
            '16-9' => esc_html__( '16:9', 'themeasy-lite' ),
            '4-3' => esc_html__( '4:3', 'themeasy-lite' ),
            '3-2' => esc_html__( '3:2', 'themeasy-lite' ),
            '1-1' => esc_html__( '1:1 (Square)', 'themeasy-lite' ),
            '3-4' => esc_html__( '3:4', 'themeasy-lite' ),
            '9-16' => esc_html__( '9:16', 'themeasy-lite' ),
            'auto' => esc_html__( 'Auto (from Image)', 'themeasy-lite' ),
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-before-after' => '--tms-before-after-ratio: {{VALUE}};',
          ],
          'selectors_dictionary' => [
            '16-9' => '16 / 9',
            '4-3' => '4 / 3',
            '3-2' => '3 / 2',
            '1-1' => '1 / 1',
            '3-4' => '3 / 4',
            '9-16' => '9 / 16',
            'auto' => 'auto',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Settings
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'before_after_settings_section',
        [
          'label' => esc_html__( 'Settings', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
    );

      $this->add_group_control(
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
          'description' => esc_html__( 'Use high priority only for images at the top of the page.', 'themeasy-lite' ),
        ]
      );

      $this->add_control(
        'show_labels',
        [
          'label' => esc_html__( 'Show Labels', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
          'separator' => 'before',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Box
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'before_after_box_styles_section',
        [
          'label' => esc_html__( 'Box', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      $this->add_responsive_control(
        'box_max_width',
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
            '{{WRAPPER}} .tms-before-after' => 'max-width: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'box_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', '%'],
          'selectors' => [
            '{{WRAPPER}} .tms-before-after'
              => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Border::get_type(),
        [
          'name' => 'box_border',
          'selector' => '{{WRAPPER}} .tms-before-after',
        ]
      );

      $this->add_group_control(
        Group_Control_Box_Shadow::get_type(),
        [
          'name' => 'box_shadow',
          'label' => esc_html__( 'Box Shadow', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-before-after',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Labels
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'before_after_labels_styles_section',
        [
          'label' => esc_html__( 'Labels', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'show_labels' => 'yes',
          ],
        ]
    );

      $this->add_responsive_control(
        'labels_position',
        [
          'label' => esc_html__( 'Position', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'corners-top',
          'options' => [
            'corners-top' => esc_html__( 'Top Corners', 'themeasy-lite' ),
            'corners-bottom' => esc_html__( 'Bottom Corners', 'themeasy-lite' ),
            'center' => esc_html__( 'Center', 'themeasy-lite' ),
          ],
        ]
      );

      $this->add_control(
        'labels_text_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'separator' => 'before',
          'selectors' => [
            '{{WRAPPER}} .tms-before-after__label' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'labels_background_color',
        [
          'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-before-after__label' => 'background-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'labels_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-before-after__label',
        ]
      );

      $this->add_responsive_control(
        'labels_padding',
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', 'em'],
          'default' => [
            'top' => 6, 'right' => 12, 'bottom' => 6, 'left' => 12, 'unit' => 'px', 'isLinked' => false,
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-before-after__label'
              => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'labels_offset',
        [
          'label' => esc_html__( 'Edge Offset', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem'],
          'range' => [
            'px' => ['min' => 0, 'max' => 80, 'step' => 1],
            'rem' => ['min' => 0, 'max' => 6, 'step' => 0.1],
          ],
          'default' => ['unit' => 'px', 'size' => 16],
          'selectors' => [
            '{{WRAPPER}} .tms-before-after' => '--tms-before-after-label-offset: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'labels_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', '%'],
          'default' => [
            'top' => 4, 'right' => 4, 'bottom' => 4, 'left' => 4, 'unit' => 'px', 'isLinked' => true,
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-before-after__label'
              => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Divider
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'before_after_divider_styles_section',
        [
          'label' => esc_html__( 'Divider', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      $this->add_control(
        'divider_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-before-after' => '--tms-before-after-divider-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'divider_width',
        [
          'label' => esc_html__( 'Width', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => ['min' => 0, 'max' => 20, 'step' => 1],
          ],
          'default' => ['unit' => 'px', 'size' => 2],
          'selectors' => [
            '{{WRAPPER}} .tms-before-after' => '--tms-before-after-divider-width: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Handle
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'before_after_handle_styles_section',
        [
          'label' => esc_html__( 'Handle', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'skin' => 'handle',
          ],
        ]
    );

      $this->start_controls_tabs( 'handle_state_tabs' );

        $this->start_controls_tab(
          'handle_normal_tab',
          [
            'label' => esc_html__( 'Normal', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'handle_background_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-before-after__handle' => 'background-color: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            'handle_icon_color',
            [
              'label' => esc_html__( 'Icon Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-before-after__handle' => 'color: {{VALUE}};',
                '{{WRAPPER}} .tms-before-after__handle svg' => 'fill: {{VALUE}}; stroke: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

        $this->start_controls_tab(
          'handle_hover_tab',
          [
            'label' => esc_html__( 'Hover / Active', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'handle_hover_background_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-before-after__handle:hover,
                 {{WRAPPER}} .tms-before-after__handle:focus-visible,
                 {{WRAPPER}} .tms-before-after--dragging .tms-before-after__handle'
                  => 'background-color: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            'handle_hover_icon_color',
            [
              'label' => esc_html__( 'Icon Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-before-after__handle:hover,
                 {{WRAPPER}} .tms-before-after__handle:focus-visible,
                 {{WRAPPER}} .tms-before-after--dragging .tms-before-after__handle'
                  => 'color: {{VALUE}};',
                '{{WRAPPER}} .tms-before-after__handle:hover svg,
                 {{WRAPPER}} .tms-before-after__handle:focus-visible svg,
                 {{WRAPPER}} .tms-before-after--dragging .tms-before-after__handle svg'
                  => 'fill: {{VALUE}}; stroke: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

      $this->add_responsive_control(
        'handle_size',
        [
          'label' => esc_html__( 'Size', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem'],
          'separator' => 'before',
          'range' => [
            'px' => ['min' => 16, 'max' => 120, 'step' => 1],
            'rem' => ['min' => 1, 'max' => 8, 'step' => 0.1],
          ],
          'default' => ['unit' => 'px', 'size' => 44],
          'selectors' => [
            '{{WRAPPER}} .tms-before-after__handle' => '--tms-before-after-handle-size: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Border::get_type(),
        [
          'name' => 'handle_border',
          'selector' => '{{WRAPPER}} .tms-before-after__handle',
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'handle_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', '%'],
          'default' => [
            'top' => 50, 'right' => 50, 'bottom' => 50, 'left' => 50, 'unit' => '%', 'isLinked' => true,
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-before-after__handle'
              => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Box_Shadow::get_type(),
        [
          'name' => 'handle_box_shadow',
          'label' => esc_html__( 'Box Shadow', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-before-after__handle',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Advanced section: Transition
    // ------------------------------------------------------------------------
    $transition_label = esc_html__( 'Transition', 'themeasy-lite' );

    $this->start_controls_section(
      'before_after_motion_section',
        [
          'label' => $transition_label,
          'tab' => Controls_Manager::TAB_ADVANCED,
        ]
    );

      $this->add_control(
        'transition_duration',
        [
          'label' => esc_html__( 'Transition Duration', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['s'],
          'range' => ['s' => ['min' => 0, 'max' => 2, 'step' => 0.05]],
          'default' => ['unit' => 's', 'size' => 0.25],
          'description' => esc_html__(
            'Eased transition used on click, keyboard and hover-follow skin. Drag stays instant.',
            'themeasy-lite'
          ),
          'selectors' => [
            '{{WRAPPER}} .tms-before-after' => '--tms-before-after-duration: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'transition_timing',
        [
          'label' => esc_html__( 'Easing', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'ease-out',
          'options' => [
            'ease' => esc_html__( 'Ease', 'themeasy-lite' ),
            'ease-in' => esc_html__( 'Ease In', 'themeasy-lite' ),
            'ease-out' => esc_html__( 'Ease Out', 'themeasy-lite' ),
            'ease-in-out' => esc_html__( 'Ease In Out', 'themeasy-lite' ),
            'linear' => esc_html__( 'Linear', 'themeasy-lite' ),
            'cubic-bezier(0.25, 0.46, 0.45, 0.94)' => esc_html__( 'Smooth', 'themeasy-lite' ),
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-before-after' => '--tms-before-after-timing: {{VALUE}};',
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
    $before_url = $settings['before_image']['url'] ?? '';
    $after_url = $settings['after_image']['url'] ?? '';

    // Early return if either image is missing.
    if ( empty( $before_url ) || empty( $after_url ) ) {
      return;
    }

    $skin = $settings['skin'] ?? 'handle';
    $orientation = $settings['orientation'] ?? 'horizontal';
    $start_position = isset( $settings['start_position']['size'] )
      ? max( 0, min( 100, floatval( $settings['start_position']['size'] ) ) )
      : 50;
    $show_labels = ( $settings['show_labels'] ?? 'yes' ) === 'yes';
    $labels_position = $settings['labels_position'] ?? 'corners-top';
    $priority = $settings['image_loading_priority'] ?? 'lazy';
    $resolution = $settings['image_resolution_size'] ?? 'large';

    $before_id = absint( $settings['before_image']['id'] ?? 0 );
    $after_id = absint( $settings['after_image']['id'] ?? 0 );
    $before_alt = Control_Media::get_image_alt( $settings['before_image'] );
    $after_alt = Control_Media::get_image_alt( $settings['after_image'] );
    $before_label = $settings['before_label'] ?? '';
    $after_label = $settings['after_label'] ?? '';

    // ------------------------------------------------------------------------
    // Wrapper classes + attributes.
    // ------------------------------------------------------------------------
    $wrapper_classes = [
      'tms-before-after',
      'tms-before-after--skin-' . $skin,
      'tms-before-after--' . $orientation,
      'tms-before-after--labels-' . $labels_position,
    ];

    // Editor preview is JS-inert until re-initialised; keep the divider centered/static there.
    if ( themeasy_is_elementor_editor() ) {
      $wrapper_classes[] = 'tms-before-after--static';
    }

    $wrapper_atts = [
      'data-skin' => $skin,
      'data-orientation' => $orientation,
      'data-start' => (string) $start_position,
      'style' => '--tms-before-after-position: ' . $start_position . '%;',
    ];

    $wrapper_classes_output = implode( ' ', array_filter( $wrapper_classes ) );

    // ------------------------------------------------------------------------
    // Render attributes.
    // ------------------------------------------------------------------------
    $slider_label = sprintf(
      /* translators: 1: before label, 2: after label */
      esc_html__( 'Compare %1$s and %2$s images', 'themeasy-lite' ),
      !empty( $before_label ) ? $before_label : esc_html__( 'before', 'themeasy-lite' ),
      !empty( $after_label ) ? $after_label : esc_html__( 'after', 'themeasy-lite' )
    );

    $this->add_render_attribute( 'slider', [
      'class' => 'tms-before-after__slider',
      'role' => 'slider',
      'tabindex' => '0',
      'aria-valuemin' => '0',
      'aria-valuemax' => '100',
      'aria-valuenow' => (string) intval( $start_position ),
      'aria-orientation' => $orientation,
      'aria-label' => $slider_label,
    ] );

    if ( $before_label ) {
      $this->add_render_attribute( 'before_label', 'class', 'tms-before-after__label tms-before-after__label--before' );
      $this->add_inline_editing_attributes( 'before_label', 'none' );
    }

    if ( $after_label ) {
      $this->add_render_attribute( 'after_label', 'class', 'tms-before-after__label tms-before-after__label--after' );
      $this->add_inline_editing_attributes( 'after_label', 'none' );
    }

    // ------------------------------------------------------------------------
    // Output.
    // ------------------------------------------------------------------------
    ?>
    <div class="<?php echo esc_attr( $wrapper_classes_output ); ?>"<?php themeasy_print_html_attributes( $wrapper_atts ); ?>>

      <div class="tms-before-after__frame">

        <div class="tms-before-after__image tms-before-after__image--before">
          <?php
          themeasy_render_attachment_image(
            $before_id,
            $resolution,
            $before_url,
            $before_alt,
            'tms-before-after__img',
            [],
            $priority
          );
          ?>
          <?php if ( $show_labels && $before_label ) : ?>
            <span <?php $this->print_render_attribute_string( 'before_label' ); ?>>
              <?php echo esc_html( $before_label ); ?>
            </span>
          <?php endif; ?>
        </div><!-- /.tms-before-after__image--before -->

        <div class="tms-before-after__image tms-before-after__image--after" aria-hidden="true">
          <div class="tms-before-after__image-inner">
            <?php
            themeasy_render_attachment_image(
              $after_id,
              $resolution,
              $after_url,
              $after_alt,
              'tms-before-after__img',
              [],
              $priority
            );
            ?>
            <?php if ( $show_labels && $after_label ) : ?>
              <span <?php $this->print_render_attribute_string( 'after_label' ); ?>>
                <?php echo esc_html( $after_label ); ?>
              </span>
            <?php endif; ?>
          </div><!-- /.tms-before-after__image-inner -->
        </div><!-- /.tms-before-after__image--after -->

        <div <?php $this->print_render_attribute_string( 'slider' ); ?>>
          <span class="tms-before-after__line" aria-hidden="true"></span>

          <?php if ( 'handle' === $skin ) : ?>
            <span class="tms-before-after__handle" aria-hidden="true">
              <?php if ( 'horizontal' === $orientation ) : ?>
                <?php
                echo wp_kses(
                  themeasy_get_svg_icon( 'ty-feather', 'chevron-left', 'tms-before-after__handle-arrow' ),
                  themeasy_get_icon_kses_tags()
                );
                echo wp_kses(
                  themeasy_get_svg_icon( 'ty-feather', 'chevron-right', 'tms-before-after__handle-arrow' ),
                  themeasy_get_icon_kses_tags()
                );
                ?>
              <?php else : ?>
                <?php
                echo wp_kses(
                  themeasy_get_svg_icon( 'ty-feather', 'chevron-up', 'tms-before-after__handle-arrow' ),
                  themeasy_get_icon_kses_tags()
                );
                echo wp_kses(
                  themeasy_get_svg_icon( 'ty-feather', 'chevron-down', 'tms-before-after__handle-arrow' ),
                  themeasy_get_icon_kses_tags()
                );
                ?>
              <?php endif; ?>
            </span>
          <?php endif; ?>
        </div><!-- /.tms-before-after__slider -->

      </div><!-- /.tms-before-after__frame -->

    </div><!-- /.tms-before-after -->
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
      // Scheme guard for every src below. The regex lives in exactly one
      // place; the fallback fails closed rather than duplicating it.
      var safeUrl = ( window.Themeasy && window.Themeasy.safeUrl )
        ? window.Themeasy.safeUrl
        : function () { return ''; };

      // ------------------------------------------------------------------------
      // Settings.
      // ------------------------------------------------------------------------
      var beforeUrl = safeUrl( ( settings.before_image && settings.before_image.url ) || '' );
      var afterUrl  = safeUrl( ( settings.after_image && settings.after_image.url ) || '' );

      if ( ! beforeUrl || ! afterUrl ) {
        return;
      }

      var skin           = settings.skin || 'handle';
      var orientation    = settings.orientation || 'horizontal';
      var startPosition  = ( settings.start_position && typeof settings.start_position.size !== 'undefined' )
        ? Math.max( 0, Math.min( 100, parseFloat( settings.start_position.size ) ) )
        : 50;
      var showLabels     = settings.show_labels === 'yes';
      var labelsPosition = settings.labels_position || 'corners-top';
      var beforeAlt      = ( settings.before_image && settings.before_image.alt ) ? settings.before_image.alt : '';
      var afterAlt       = ( settings.after_image && settings.after_image.alt ) ? settings.after_image.alt : '';
      var beforeLabel    = settings.before_label || '';
      var afterLabel     = settings.after_label || '';

      // ------------------------------------------------------------------------
      // Wrapper classes + attributes.
      // ------------------------------------------------------------------------
      var wrapperClasses = [
        'tms-before-after',
        'tms-before-after--skin-' + skin,
        'tms-before-after--' + orientation,
        'tms-before-after--labels-' + labelsPosition,
        'tms-before-after--static'
      ];

      var wrapperAtts = {
        'data-skin': skin,
        'data-orientation': orientation,
        'data-start': String( startPosition ),
        'style': '--tms-before-after-position: ' + startPosition + '%;'
      };

      var wrapperClassStr = wrapperClasses.filter( Boolean ).join( ' ' );

      // ------------------------------------------------------------------------
      // Render attributes.
      // ------------------------------------------------------------------------
      if ( beforeLabel ) {
        view.addRenderAttribute( 'before_label', 'class', 'tms-before-after__label tms-before-after__label--before' );
        view.addInlineEditingAttributes( 'before_label', 'none' );
      }

      if ( afterLabel ) {
        view.addRenderAttribute( 'after_label', 'class', 'tms-before-after__label tms-before-after__label--after' );
        view.addInlineEditingAttributes( 'after_label', 'none' );
      }

      var chevronLeft  = <?php echo wp_json_encode( themeasy_get_svg_icon( 'ty-feather', 'chevron-left', 'tms-before-after__handle-arrow' ) ); ?>;
      var chevronRight = <?php echo wp_json_encode( themeasy_get_svg_icon( 'ty-feather', 'chevron-right', 'tms-before-after__handle-arrow' ) ); ?>;
      var chevronUp    = <?php echo wp_json_encode( themeasy_get_svg_icon( 'ty-feather', 'chevron-up', 'tms-before-after__handle-arrow' ) ); ?>;
      var chevronDown  = <?php echo wp_json_encode( themeasy_get_svg_icon( 'ty-feather', 'chevron-down', 'tms-before-after__handle-arrow' ) ); ?>;
    #>

    <div class="{{ wrapperClassStr }}" {{{ Themeasy.htmlAttributes( wrapperAtts ) }}}>

      <div class="tms-before-after__frame">

        <div class="tms-before-after__image tms-before-after__image--before">
          <img src="{{ beforeUrl }}" alt="{{ beforeAlt }}" class="tms-before-after__img" />
          <# if ( showLabels && beforeLabel ) { #>
            <span {{{ view.getRenderAttributeString( 'before_label' ) }}}>{{ beforeLabel }}</span>
          <# } #>
        </div><!-- /.tms-before-after__image--before -->

        <div class="tms-before-after__image tms-before-after__image--after" aria-hidden="true">
          <div class="tms-before-after__image-inner">
            <img src="{{ afterUrl }}" alt="{{ afterAlt }}" class="tms-before-after__img" />
            <# if ( showLabels && afterLabel ) { #>
              <span {{{ view.getRenderAttributeString( 'after_label' ) }}}>{{ afterLabel }}</span>
            <# } #>
          </div><!-- /.tms-before-after__image-inner -->
        </div><!-- /.tms-before-after__image--after -->

        <div class="tms-before-after__slider"
             role="slider"
             tabindex="0"
             aria-valuemin="0"
             aria-valuemax="100"
             aria-valuenow="{{ Math.round( startPosition ) }}"
             aria-orientation="{{ orientation }}">
          <span class="tms-before-after__line" aria-hidden="true"></span>

          <# if ( skin === 'handle' ) { #>
            <span class="tms-before-after__handle" aria-hidden="true">
              <# if ( orientation === 'horizontal' ) { #>
                {{{ chevronLeft }}}{{{ chevronRight }}}
              <# } else { #>
                {{{ chevronUp }}}{{{ chevronDown }}}
              <# } #>
            </span>
          <# } #>
        </div><!-- /.tms-before-after__slider -->

      </div><!-- /.tms-before-after__frame -->

    </div><!-- /.tms-before-after -->

    <?php
  }
}
