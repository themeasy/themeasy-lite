<?php
/**
 * Themeasy Elementor Widget: Card
 *
 * A customizable card widget with an icon or image header, title, description,
 * and flexible link options (a styled button with multiple presets, or a full
 * card-cover link). Built on the shared widget styles and the button scroll-text
 * interaction.
 *
 * @package Themeasy
 * @since 1.0.0
 */

namespace Themeasy\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Control_Media;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;
use Elementor\Utils;
use Elementor\Widget_Base;
use Themeasy\Core\Entitlement;

/**
 * Responsible for registering controls and rendering the widget.
 */
class Card extends Widget_Base {
  public function get_name() {
    return 'themeasy-card';
  }

  public function get_title() {
    return esc_html__( 'Card', 'themeasy-lite' );
  }

  public function get_icon() {
    return 'eicon-icon-box';
  }

  public function get_custom_help_url() {
    return 'https://themeasy.co/help-center';
  }

  public function get_categories() {
    return ['themeasy_blocks_category', 'themeasy_content_category'];
  }

  public function get_keywords() {
    return ['themeasy', 'card', 'icon box', 'feature', 'info', 'content', 'cta'];
  }

  public function get_style_depends() {
    return ['themeasy-shared'];
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
    // Content section: Card
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'card_content_section',
        [
          'label' => esc_html__( 'Card', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
    );

      $this->add_control(
        'card_header_type',
        [
          'label' => esc_html__( 'Header Type', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'icon',
          'options' => [
            'icon' => esc_html__( 'Icon', 'themeasy-lite' ),
            'image' => esc_html__( 'Image', 'themeasy-lite' ),
            '' => esc_html__( 'None', 'themeasy-lite' ),
          ],
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
          'condition' => [
            'card_header_type' => 'icon',
          ],
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
          'condition' => [
            'card_header_type' => 'image',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Image_Size::get_type(),
        [
          'name' => 'image_resolution',
          'exclude' => ['custom'],
          'include' => [],
          'default' => 'full',
          'condition' => [
            'card_header_type' => 'image',
          ],
        ]
      );

      $this->add_control(
        'image_loading_priority',
        [
          'label' => esc_html__( 'Loading Behavior', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'lazy',
          'options' => [
            'lazy' => esc_html__( 'Load When Needed (Lazy Loading)', 'themeasy-lite' ),
            'high' => esc_html__( 'Prioritize Loading (Above the Fold)', 'themeasy-lite' ),
          ],
          'description' => esc_html__( 'Choose how this image loads. Lazy loading saves performance, while high priority is ideal for images at the top of the page.', 'themeasy-lite' ),
          'condition' => [
            'card_header_type' => 'image',
          ],
        ]
      );

      $this->add_control(
        'title',
        [
          'label' => esc_html__( 'Title', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXTAREA,
          'rows' => 2,
          'separator' => 'before',
          'default' => esc_html__( 'Lorem ipsum dolor', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'Leave blank to hide', 'themeasy-lite' ),
        ]
      );

      $this->add_control(
        'description',
        [
          'label' => esc_html__( 'Description', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXTAREA,
          'separator' => 'before',
          'default' => esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean lobortis magna et ipsum rutrum lobortis.', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'Leave blank to hide', 'themeasy-lite' ),
        ]
      );

      $this->add_control(
        'link_type',
        [
          'label' => esc_html__( 'Link Type', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'separator' => 'before',
          'default' => 'button',
          'options' => [
            'button' => esc_html__( 'Button', 'themeasy-lite' ),
            'card' => esc_html__( 'Card', 'themeasy-lite' ),
            '' => esc_html__( 'None', 'themeasy-lite' ),
          ],
        ]
      );

      $this->add_control(
        'button_label',
        [
          'label' => esc_html__( 'Label', 'themeasy-lite' ),
          'label_block' => false,
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'Call to Action', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'Type your text here', 'themeasy-lite' ),
          'dynamic' => [
            'active' => true,
          ],
          'condition' => [
            'link_type' => 'button',
          ],
        ]
      );

      // The hidden label only feeds the scroll text animation, which is Pro.
      if ( Entitlement::can_use_widgets() ) {
        $this->add_control(
          'button_hidden_label',
          [
            'label' => esc_html__( 'Hidden Label', 'themeasy-lite' ),
            'description' => esc_html__( 'This hidden label is used for scroll text animations.', 'themeasy-lite' ),
            'type' => Controls_Manager::TEXT,
            'default' => esc_html__( 'Call to Action', 'themeasy-lite' ),
            'placeholder' => esc_html__( 'Type your text here', 'themeasy-lite' ),
            'dynamic' => [
              'active' => true,
            ],
            'condition' => [
              'link_type' => 'button',
            ],
          ]
        );
      }

      $this->add_control(
        'button_icon',
        [
          'label' => esc_html__( 'Icon', 'themeasy-lite' ),
          'type' => Controls_Manager::ICONS,
          'condition' => [
            'link_type' => 'button',
          ],
        ]
      );

      $this->add_control(
        'button_icon_alignment',
        [
          'label' => esc_html__( 'Icon Alignment', 'themeasy-lite' ),
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
            'link_type' => 'button',
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
            'url' => 'https://your-link.com',
            'is_external' => false,
            'nofollow' => true,
          ],
          'condition' => [
            'link_type' => ['button', 'card'],
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Card (wrapper)
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'card_wrapper_styles_section',
        [
          'label' => esc_html__( 'Card', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      $this->add_control(
        'horizontal_alignment',
        [
          'label' => esc_html__( 'Horizontal Alignment', 'themeasy-lite' ),
          'type' => Controls_Manager::CHOOSE,
          'options' => [
            'start' => [
              'title' => esc_html__( 'Left', 'themeasy-lite' ),
              'icon' => 'eicon-align-start-h',
            ],
            'center' => [
              'title' => esc_html__( 'Center', 'themeasy-lite' ),
              'icon' => 'eicon-align-center-v',
            ],
            'end' => [
              'title' => esc_html__( 'Right', 'themeasy-lite' ),
              'icon' => 'eicon-align-end-h',
            ],
          ],
          'default' => 'start',
          'selectors' => [
            '{{WRAPPER}} .tms-card .tms-card__header,
             {{WRAPPER}} .tms-card .tms-card__body' => 'align-items: {{VALUE}}; text-align: {{VALUE}};',
            '{{WRAPPER}} .tms-card .tms-button-group' => 'justify-content: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'block_background_color',
        [
          'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-card' => 'background-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'block_height',
        [
          'label' => esc_html__( 'Height', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'separator' => 'before',
          'default' => '',
          'options' => [
            '' => esc_html__( 'Fit Content', 'themeasy-lite' ),
            'stretch' => esc_html__( 'Match Tallest Card', 'themeasy-lite' ),
          ],
          'description' => esc_html__( 'Match Tallest Card makes the card fill its grid cell, so cards placed side by side share the same height and their buttons line up, no matter how much text each one holds.', 'themeasy-lite' ),
          'render_type' => 'template',
          'selectors_dictionary' => [
            'stretch' => '100%',
          ],
          'selectors' => [
            '{{WRAPPER}} > .elementor-widget-container' => 'height: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'block_min_height',
        [
          'label' => esc_html__( 'Minimum Height', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem', 'vh'],
          'range' => [
            'px' => ['min' => 0, 'max' => 900, 'step' => 1],
            'rem' => ['min' => 0, 'max' => 60, 'step' => 0.5],
            'vh' => ['min' => 0, 'max' => 100, 'step' => 1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-card' => 'min-height: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'block_padding',
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Border::get_type(),
        [
          'name' => 'block_border',
          'selector' => '{{WRAPPER}} .tms-card',
        ]
      );

      $this->add_control(
        'block_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Box_Shadow::get_type(),
        [
          'name' => 'block_box_shadow',
          'label' => esc_html__( 'Box Shadow', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-card',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Image
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'card_image_styles_section',
        [
          'label' => esc_html__( 'Image', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'card_header_type' => 'image',
          ],
        ]
    );

      $this->add_control(
        'image_opacity',
        [
          'label' => esc_html__( 'Opacity', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['u'],
          'range' => [
            'u' => [
              'min' => 0,
              'max' => 1,
              'step' => 0.1,
            ],
          ],
          'default' => [
            'unit' => 'u',
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-card .tms-card__image' => 'opacity: {{SIZE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Css_Filter::get_type(),
        [
          'name' => 'image_css_filters',
          'selector' => '{{WRAPPER}} .tms-card .tms-card__image',
        ]
      );

      $this->add_control(
        'image_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-card .tms-card__image' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Title
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'card_title_styles_section',
        [
          'label' => esc_html__( 'Title', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      $this->add_control(
        'title_text_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-card .tms-card__title' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'title_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-card .tms-card__title',
        ]
      );

      $this->add_responsive_control(
        'title_line_clamp',
        [
          'label' => esc_html__( 'Line Clamp', 'themeasy-lite' ),
          'description' => esc_html__( '0 = no clamp (the phone default).', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['u'],
          'range' => [
            'u' => [
              'min' => 0,
              'max' => 6,
              'step' => 1,
            ],
          ],
          'default' => [
            'unit' => 'u',
            'size' => 2,
          ],
          'mobile_default' => [
            'unit' => 'u',
            'size' => 0,
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-card .tms-card__title' => '--tms-line-clamp: {{SIZE}}; display: -webkit-box; -webkit-box-orient: vertical; overflow: hidden; -webkit-line-clamp: var(--tms-line-clamp);',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Description
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'card_description_styles_section',
        [
          'label' => esc_html__( 'Description', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      $this->add_control(
        'description_text_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-card .tms-card__description' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'description_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-card .tms-card__description',
        ]
      );

      $this->add_responsive_control(
        'description_line_clamp',
        [
          'label' => esc_html__( 'Line Clamp', 'themeasy-lite' ),
          'description' => esc_html__( '0 = no clamp (the phone default).', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['u'],
          'range' => [
            'u' => [
              'min' => 0,
              'max' => 6,
              'step' => 1,
            ],
          ],
          'default' => [
            'unit' => 'u',
            'size' => 3,
          ],
          'mobile_default' => [
            'unit' => 'u',
            'size' => 0,
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-card .tms-card__description' => '--tms-line-clamp: {{SIZE}}; display: -webkit-box; -webkit-box-orient: vertical; overflow: hidden; -webkit-line-clamp: var(--tms-line-clamp);',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Icon
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'card_icon_styles_section',
        [
          'label' => esc_html__( 'Icon', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'card_header_type' => 'icon',
          ],
        ]
    );

      $this->add_control(
        'icon_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-card .tms-card__header .tms-card__icon' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'icon_fill_color',
        [
          'label' => esc_html__( 'Fill Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-card .tms-card__header .tms-card__icon svg' => 'fill: {{VALUE}};',
          ],
          'condition' => [
            'icon[library]' => themeasy_svg_icon_condition_libraries(),
          ],
        ]
      );

      $this->add_control(
        'icon_stroke_color',
        [
          'label' => esc_html__( 'Stroke Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-card .tms-card__header .tms-card__icon svg' => 'stroke: {{VALUE}};',
          ],
          'condition' => [
            'icon[library]' => themeasy_svg_icon_condition_libraries(),
          ],
        ]
      );

      $this->add_responsive_control(
        'icon_size',
        [
          'label' => esc_html__( 'Size', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'em', 'rem'],
          'range' => [
            'px' => ['min' => 5, 'max' => 150, 'step' => 1],
            'em' => ['min' => 0.1, 'max' => 10, 'step' => 0.1],
            'rem' => ['min' => 0.1, 'max' => 10, 'step' => 0.1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-card .tms-card__header .tms-card__icon' => 'font-size: {{SIZE}}{{UNIT}};',
            '{{WRAPPER}} .tms-card .tms-card__header .tms-card__icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'icon_thickness',
        [
          'label' => esc_html__( 'Thickness', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => ['min' => 0.5, 'max' => 10, 'step' => 0.5],
          ],
          'default' => ['unit' => 'px', 'size' => 2],
          'selectors' => [
            '{{WRAPPER}} .tms-card .tms-card__header .tms-card__icon svg,
             {{WRAPPER}} .tms-card .tms-card__header .tms-card__icon svg *' => 'stroke-width: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'icon[library]' => themeasy_svg_icon_condition_libraries(),
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
            '{{WRAPPER}} .tms-card .tms-card__header .tms-card__icon' => 'transform: rotate({{SIZE}}{{UNIT}});',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Button
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'card_button_styles_section',
        [
          'label' => esc_html__( 'Button', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'link_type' => 'button',
          ],
        ]
    );

      $this->add_control(
        'button_style',
        [
          'label' => esc_html__( 'Button Style', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'solid-fill',
          'options' => [
            'simple' => esc_html__( 'Simple (Text Only)', 'themeasy-lite' ),
            'solid-fill' => esc_html__( 'Solid Fill', 'themeasy-lite' ),
            'outline' => esc_html__( 'Outline', 'themeasy-lite' ),
            'brutalist' => esc_html__( 'Brutalist', 'themeasy-lite' ),
            'muted-fill' => esc_html__( 'Muted Fill', 'themeasy-lite' ),
            'muted-outline' => esc_html__( 'Muted Outline', 'themeasy-lite' ),
            'gradient' => esc_html__( 'Gradient Fill', 'themeasy-lite' ),
            'gradient-outline' => esc_html__( 'Gradient Outline', 'themeasy-lite' ),
            'duocolor' => esc_html__( 'Duocolor Fill', 'themeasy-lite' ),
            'duocolor-outline' => esc_html__( 'Duocolor Outline', 'themeasy-lite' ),
          ],
        ]
      );

      // Scroll text runs on the core GSAP engine: Pro only.
      if ( Entitlement::can_use_widgets() ) {
        $this->add_control(
          'button_scroll_text',
          [
            'label' => esc_html__( 'Scroll Text Animation', 'themeasy-lite' ),
            'description' => esc_html__( 'Choose the scroll text animation for the button hover effect.', 'themeasy-lite' ),
            'type' => Controls_Manager::SELECT,
            'default' => '',
            'options' => [
              '' => esc_html__( 'None', 'themeasy-lite' ),
              'vertical' => esc_html__( 'Vertical', 'themeasy-lite' ),
              'horizontal' => esc_html__( 'Horizontal', 'themeasy-lite' ),
            ],
            'condition' => [
              'button_style!' => ['simple', 'duocolor', 'duocolor-outline'],
            ],
          ]
        );
      }

      // Gradient-outline tokens (own pair of colors, sits before the tabbed colors).
      $this->add_control(
        'button_gradient_outline_primary_color',
        [
          'label' => esc_html__( 'Primary Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-button.tms-button--gradient-outline' => '--tms-primary-color: {{VALUE}};',
          ],
          'condition' => [
            'button_style' => 'gradient-outline',
          ],
        ]
      );

      $this->add_control(
        'button_gradient_outline_secondary_color',
        [
          'label' => esc_html__( 'Secondary Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-button.tms-button--gradient-outline' => '--tms-secondary-color: {{VALUE}};',
          ],
          'condition' => [
            'button_style' => 'gradient-outline',
          ],
        ]
      );

      // Colors (Normal/Hover tabs) — first by the property-order rule.
      $this->start_controls_tabs(
        'button_color_tabs',
        [
          'condition' => ['button_style!' => 'gradient-outline'],
        ]
      );

        $this->start_controls_tab(
          'button_normal_tab',
            [
              'label' => esc_html__( 'Normal', 'themeasy-lite' ),
              'condition' => [
                'button_style!' => 'gradient-outline',
              ],
            ]
        );

          $this->add_control(
            'button_background_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-button' => 'background-color: {{VALUE}};',
                '{{WRAPPER}} .tms-button.tms-button--brutalist::after' => 'background-color: {{VALUE}};',
              ],
              'condition' => [
                'button_style!' => ['simple', 'outline', 'gradient', 'gradient-outline', 'duocolor-outline'],
              ],
            ]
          );

          $this->add_control(
            'button_border_color',
            [
              'label' => esc_html__( 'Border Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-button' => 'border-color: {{VALUE}};',
                '{{WRAPPER}} .tms-button.tms-button--brutalist::before' => 'background-color: {{VALUE}};',
              ],
              'condition' => [
                'button_style' => ['outline', 'brutalist', 'muted-outline', 'duocolor-outline'],
              ],
            ]
          );

          $this->add_control(
            'button_text_color',
            [
              'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-button .tms-button__text,
                 {{WRAPPER}} .tms-button .tms-button__text > *' => 'color: {{VALUE}};',
              ],
              'condition' => [
                'button_style!' => 'gradient-outline',
              ],
            ]
          );

          $this->add_control(
            'button_icon_color',
            [
              'label' => esc_html__( 'Icon Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-button .tms-button__icon' => 'color: {{VALUE}};',
              ],
              'condition' => [
                'button_style!' => 'gradient-outline',
              ],
            ]
          );

          $this->add_control(
            'button_icon_fill_color',
            [
              'label' => esc_html__( 'Icon Fill Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-button .tms-button__icon svg' => 'fill: {{VALUE}};',
              ],
              'conditions' => [
                'terms' => [
                  ['name' => 'button_icon[library]', 'operator' => 'in', 'value' => themeasy_svg_icon_condition_libraries()],
                ],
              ],
            ]
          );

          $this->add_control(
            'button_icon_stroke_color',
            [
              'label' => esc_html__( 'Icon Stroke Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-button .tms-button__icon svg' => 'stroke: {{VALUE}};',
              ],
              'conditions' => [
                'terms' => [
                  ['name' => 'button_icon[library]', 'operator' => 'in', 'value' => themeasy_svg_icon_condition_libraries()],
                ],
              ],
            ]
          );

          $this->add_group_control(
            Group_Control_Background::get_type(),
            [
              'name' => 'button_gradient_color',
              'label' => esc_html__( 'Gradient Color', 'themeasy-lite' ),
              'types' => ['gradient'],
              'selector' => '{{WRAPPER}} .tms-button.tms-button--gradient::before',
              'condition' => [
                'button_style' => 'gradient',
              ],
            ]
          );

          $this->add_control(
            'button_duocolor_color',
            [
              'label' => esc_html__( 'Icon Box Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-button .tms-button__duocolor-icon' => 'background-color: {{VALUE}};',
              ],
              'condition' => [
                'button_style' => ['duocolor', 'duocolor-outline'],
              ],
            ]
          );

        $this->end_controls_tab();

        $this->start_controls_tab(
          'button_hover_tab',
            [
              'label' => esc_html__( 'Hover', 'themeasy-lite' ),
              'condition' => [
                'button_style!' => 'gradient-outline',
              ],
            ]
        );

          $this->add_control(
            'button_hover_background_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-button:hover' => 'background-color: {{VALUE}};',
                '{{WRAPPER}} .tms-button.tms-button--brutalist:hover::after' => 'background-color: {{VALUE}};',
              ],
              'condition' => [
                'button_style!' => ['simple', 'gradient', 'gradient-outline'],
              ],
            ]
          );

          $this->add_control(
            'button_hover_border_color',
            [
              'label' => esc_html__( 'Border Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-button:hover' => 'border-color: {{VALUE}};',
                '{{WRAPPER}} .tms-button.tms-button--brutalist:hover::before' => 'background-color: {{VALUE}};',
              ],
              'condition' => [
                'button_style' => ['outline', 'brutalist', 'muted-outline', 'duocolor-outline'],
              ],
            ]
          );

          $this->add_control(
            'button_hover_text_color',
            [
              'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-button:hover .tms-button__text,
                 {{WRAPPER}} .tms-button:hover .tms-button__text > *' => 'color: {{VALUE}};',
              ],
              'condition' => [
                'button_style!' => 'gradient-outline',
              ],
            ]
          );

          $this->add_control(
            'button_hover_icon_color',
            [
              'label' => esc_html__( 'Icon Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-button:hover .tms-button__icon' => 'color: {{VALUE}};',
              ],
              'condition' => [
                'button_style!' => 'gradient-outline',
              ],
            ]
          );

          $this->add_group_control(
            Group_Control_Background::get_type(),
            [
              'name' => 'button_hover_gradient_color',
              'label' => esc_html__( 'Gradient Color', 'themeasy-lite' ),
              'types' => ['gradient'],
              'selector' => '{{WRAPPER}} .tms-button.tms-button--gradient::after',
              'condition' => [
                'button_style' => 'gradient',
              ],
            ]
          );

          $this->add_control(
            'button_hover_duocolor_color',
            [
              'label' => esc_html__( 'Icon Box Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-button:hover .tms-button__duocolor-icon' => 'background-color: {{VALUE}};',
              ],
              'condition' => [
                'button_style' => ['duocolor', 'duocolor-outline'],
              ],
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

      // Typography.
      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'button_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-button .tms-button__text',
          'separator' => 'before',
        ]
      );

      // Icon sizing.
      $this->add_control(
        'button_icon_heading',
        [
          'label' => esc_html__( 'Icon', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_responsive_control(
        'button_icon_size',
        [
          'label' => esc_html__( 'Icon Size', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem', 'em'],
          'range' => [
            'px' => ['min' => 0, 'max' => 100, 'step' => 1],
            'rem' => ['min' => 0, 'max' => 10, 'step' => 0.1],
            'em' => ['min' => 0, 'max' => 10, 'step' => 0.1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-button .tms-button__icon' => 'font-size: {{SIZE}}{{UNIT}};',
            '{{WRAPPER}} .tms-button .tms-button__icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'button_icon_thickness',
        [
          'label' => esc_html__( 'Icon Thickness', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => ['min' => 0.5, 'max' => 10, 'step' => 0.5],
          ],
          'default' => ['unit' => 'px', 'size' => 2],
          'selectors' => [
            '{{WRAPPER}} .tms-button .tms-button__icon svg,
             {{WRAPPER}} .tms-button .tms-button__icon svg *' => 'stroke-width: {{SIZE}}{{UNIT}};',
          ],
          'conditions' => [
            'terms' => [
              ['name' => 'button_icon[library]', 'operator' => 'in', 'value' => themeasy_svg_icon_condition_libraries()],
            ],
          ],
        ]
      );

      // Dimensions.
      $this->add_control(
        'button_dimensions_heading',
        [
          'label' => esc_html__( 'Dimensions', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'button_width',
        [
          'label' => esc_html__( 'Width', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', '%'],
          'range' => [
            'px' => [
              'min' => 0,
              'max' => 1200,
              'step' => 1,
            ],
            '%' => [
              'min' => 0,
              'max' => 100,
              'step' => 1,
            ],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-button-group,
             {{WRAPPER}} .tms-button' => 'width: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'button_padding',
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
          'condition' => [
            'button_style!' => ['simple', 'duocolor', 'duocolor-outline'],
          ],
        ]
      );

      $this->add_control(
        'button_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            '{{WRAPPER}} .tms-button .tms-button__duocolor-icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
          'condition' => [
            'button_style!' => ['simple', 'gradient-outline'],
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Advanced section: Themeasy Motion upsell (the button scroll text is Pro:
    // it runs on GSAP, which the Free build neither ships nor loads).
    // ------------------------------------------------------------------------
    if ( !Entitlement::can_use_widgets() ) {
      themeasy_register_motion_upsell_section( $this );
    }
  }

  /**
   * Render the widget output on the frontend and in the editor preview.
   *
   * @return void
   */
  protected function render() {
    $settings = $this->get_settings_for_display();

    // ------------------------------------------------------------------------
    // Settings.
    // ------------------------------------------------------------------------
    $title = $settings['title'] ?? '';
    $description = $settings['description'] ?? '';
    $icon = is_array( $settings['icon'] ?? null ) ? $settings['icon'] : [];
    $image = $settings['image'] ?? [];
    $header_type = $settings['card_header_type'] ?? '';
    $link_type = $settings['link_type'] ?? '';
    $height_mode = $settings['block_height'] ?? '';

    // Button settings.
    $button_label = $settings['button_label'] ?? '';
    $button_hidden_label = $settings['button_hidden_label'] ?? '';
    $button_icon = is_array( $settings['button_icon'] ?? null ) ? $settings['button_icon'] : [];
    $button_icon_align = $settings['button_icon_alignment'] ?? 'right';
    $button_style = $settings['button_style'] ?? 'solid-fill';
    $button_scroll_text = Entitlement::can_use_widgets() ? ( $settings['button_scroll_text'] ?? '' ) : '';
    $is_scroll_text = in_array( $button_scroll_text, ['vertical', 'horizontal'], true );

    // ------------------------------------------------------------------------
    // Wrapper classes + attributes.
    // ------------------------------------------------------------------------
    $card_classes = ['tms-card'];
    $button_classes = ['tms-button'];

    if ( 'stretch' === $height_mode ) {
      $card_classes[] = 'tms-card--stretch';
    }

    if ( 'left' === $button_icon_align ) {
      $button_classes[] = 'tms-button--icon-left';
    }

    if ( $button_style ) {
      $button_classes[] = 'tms-button--' . $button_style;
    }

    if ( $button_scroll_text ) {
      $button_classes[] = 'tms-' . $button_scroll_text . '-scroll-text';
    }

    $card_classes_output = implode( ' ', array_filter( $card_classes ) );
    $button_classes_output = implode( ' ', array_filter( $button_classes ) );

    // ------------------------------------------------------------------------
    // Render attributes.
    // ------------------------------------------------------------------------
    if ( !empty( $settings['link']['url'] ) ) {
      $this->add_link_attributes( 'link', $settings['link'] );
      themeasy_add_external_link_rel( $this, 'link', $settings['link'] );
    } else {
      $this->add_render_attribute( 'link', 'href', '#' );
      $this->add_render_attribute( 'link', 'role', 'button' );
    }

    if ( $title ) {
      $this->add_render_attribute( 'title', 'class', 'tms-card__title' );
      $this->add_inline_editing_attributes( 'title', 'basic' );
    }

    if ( $description ) {
      $this->add_render_attribute( 'description', 'class', 'tms-card__description' );
      $this->add_inline_editing_attributes( 'description', 'advanced' );
    }

    if ( 'button' === $link_type ) {
      $this->add_render_attribute( 'button_label', 'class', 'tms-button__text' );
      $this->add_inline_editing_attributes( 'button_label', 'none' );
    }

    // Icon rendering.
    $icon_html = '';

    if ( 'icon' === $header_type && !empty( $icon ) ) {
      $icon_html = themeasy_render_icon_html( $icon );

      if ( $icon_html ) {
        $icon_html = '<span class="tms-card__icon" aria-hidden="true">' . $icon_html . '</span>';
      }
    }

    // Button icon rendering.
    $button_icon_html = '';

    if ( 'button' === $link_type && !empty( $button_icon ) ) {
      $button_icon_html = themeasy_render_icon_html( $button_icon );

      if ( $button_icon_html ) {
        $button_icon_html = '<span class="tms-button__icon" aria-hidden="true">' . $button_icon_html . '</span>';
      }
    }

    // ------------------------------------------------------------------------
    // Output.
    // ------------------------------------------------------------------------
    ?>
    <div class="<?php echo esc_attr( $card_classes_output ); ?>">
      <div class="tms-card__wrapper">

        <?php if ( $header_type ) : ?>
          <div class="tms-card__header">

            <?php if ( 'icon' === $header_type && $icon_html ) : ?>
              <?php echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>

            <?php elseif ( 'image' === $header_type && !empty( $image['url'] ) ) : ?>
              <?php
              $image_id = absint( $image['id'] ?? 0 );
              $image_alt = Control_Media::get_image_alt( $image );
              $image_size = $settings['image_resolution_size'] ?? 'full';
              $image_priority = $settings['image_loading_priority'] ?? 'lazy';

              themeasy_render_attachment_image(
                $image_id,
                $image_size,
                $image['url'],
                $image_alt,
                'tms-card__image',
                [],
                $image_priority
              );
              ?>
            <?php endif; ?>

          </div><!-- /.tms-card__header -->
        <?php endif; ?>

        <div class="tms-card__body">

          <?php if ( $title ) : ?>
            <h3 <?php $this->print_render_attribute_string( 'title' ); ?>>
              <?php echo wp_kses_post( $title ); ?>
            </h3>
          <?php endif; ?>

          <?php if ( $description ) : ?>
            <p <?php $this->print_render_attribute_string( 'description' ); ?>>
              <?php echo wp_kses_post( $description ); ?>
            </p>
          <?php endif; ?>

        </div><!-- /.tms-card__body -->

        <?php if ( 'button' === $link_type ) : ?>
          <div class="tms-button-group">
            <a <?php $this->print_render_attribute_string( 'link' ); ?> class="<?php echo esc_attr( $button_classes_output ); ?>">

              <?php if ( $is_scroll_text ) : ?>
                <span class="tms-scroll-text--visible">
              <?php endif; ?>

              <span <?php $this->print_render_attribute_string( 'button_label' ); ?>>
                <?php echo wp_kses( $button_label, themeasy_get_kses_allowed_tags() ); ?>
              </span>

              <?php if ( in_array( $button_style, ['duocolor', 'duocolor-outline'], true ) ) : ?>
                <span class="tms-button__duocolor-icon">
                  <?php echo $button_icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>
                </span>
              <?php else : ?>
                <?php echo $button_icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>
              <?php endif; ?>

              <?php if ( $is_scroll_text ) : ?>
                </span><!-- /.tms-scroll-text--visible -->

                <span class="tms-scroll-text--hidden">
                  <span class="tms-button__text">
                    <?php echo wp_kses( $button_hidden_label, themeasy_get_kses_allowed_tags() ); ?>
                  </span>
                  <?php echo $button_icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>
                </span><!-- /.tms-scroll-text--hidden -->
              <?php endif; ?>

            </a><!-- /.tms-button -->
          </div><!-- /.tms-button-group -->
        <?php endif; ?>

        <?php if ( 'card' === $link_type ) : ?>
          <a <?php $this->print_render_attribute_string( 'link' ); ?> class="tms-cover-link"></a>
        <?php endif; ?>

      </div><!-- /.tms-card__wrapper -->
    </div><!-- /.tms-card -->
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

      // User text reaches raw {{{ }}} output: sanitize with kses parity.
      var sanitizeInline = ( window.Themeasy && window.Themeasy.sanitizeInlineHtml )
        ? window.Themeasy.sanitizeInlineHtml
        : _.escape;

      // Scheme guard for every href below. The regex lives in exactly one
      // place; the fallback fails closed rather than duplicating it.
      var safeUrl = ( window.Themeasy && window.Themeasy.safeUrl )
        ? window.Themeasy.safeUrl
        : function () { return ''; };

      // ------------------------------------------------------------------------
      // Settings.
      // ------------------------------------------------------------------------
      var title       = sanitizeInline( settings.title || '', 'rich' );
      var description = sanitizeInline( settings.description || '', 'rich' );
      var image       = settings.image || {};
      var headerType  = settings.card_header_type || '';
      var linkType    = settings.link_type || '';
      var heightMode  = settings.block_height || '';

      // Button settings.
      var buttonLabel       = sanitizeInline( settings.button_label || '' );
      var buttonHiddenLabel = sanitizeInline( settings.button_hidden_label || '' );
      var buttonIconAlign   = settings.button_icon_alignment || 'right';
      var buttonStyle       = settings.button_style || 'solid-fill';
      var buttonScrollText  = motion ? ( settings.button_scroll_text || '' ) : '';
      var isScrollText      = [ 'vertical', 'horizontal' ].indexOf( buttonScrollText ) !== -1;

      // Link.
      var link    = settings.link || {};
      // A blocked (or unset) URL falls back to the inert '#'.
      var linkUrl = safeUrl( ( link && link.url ) || '' ) || '#';

      // ------------------------------------------------------------------------
      // Wrapper classes + attributes.
      // ------------------------------------------------------------------------
      var cardClasses   = [ 'tms-card' ];
      var buttonClasses = [ 'tms-button' ];

      if ( heightMode === 'stretch' ) {
        cardClasses.push( 'tms-card--stretch' );
      }

      if ( buttonIconAlign === 'left' ) {
        buttonClasses.push( 'tms-button--icon-left' );
      }

      if ( buttonStyle ) {
        buttonClasses.push( 'tms-button--' + buttonStyle );
      }

      if ( buttonScrollText ) {
        buttonClasses.push( 'tms-' + buttonScrollText + '-scroll-text' );
      }

      var cardClassStr   = cardClasses.filter( Boolean ).join( ' ' );
      var buttonClassStr = buttonClasses.filter( Boolean ).join( ' ' );

      // ------------------------------------------------------------------------
      // Render attributes.
      // ------------------------------------------------------------------------
      if ( title ) {
        view.addRenderAttribute( 'title', 'class', 'tms-card__title' );
        view.addInlineEditingAttributes( 'title', 'basic' );
      }

      if ( description ) {
        view.addRenderAttribute( 'description', 'class', 'tms-card__description' );
        view.addInlineEditingAttributes( 'description', 'advanced' );
      }

      if ( linkType === 'button' ) {
        view.addRenderAttribute( 'button_label', 'class', 'tms-button__text' );
        view.addInlineEditingAttributes( 'button_label', 'none' );
      }

      // Icon rendering.
      var iconMarkup = ( window.Themeasy && window.Themeasy.renderIconMarkup )
        ? window.Themeasy.renderIconMarkup( view, settings.icon, null, {} )
        : '';

      if ( iconMarkup ) {
        iconMarkup = '<span class="tms-card__icon" aria-hidden="true">' + iconMarkup + '</span>';
      }

      // Button icon rendering.
      var buttonIconMarkup = ( window.Themeasy && window.Themeasy.renderIconMarkup )
        ? window.Themeasy.renderIconMarkup( view, settings.button_icon, null, {} )
        : '';

      if ( buttonIconMarkup ) {
        buttonIconMarkup = '<span class="tms-button__icon" aria-hidden="true">' + buttonIconMarkup + '</span>';
      }
    #>

    <div class="{{ cardClassStr }}">
      <div class="tms-card__wrapper">

        <# if ( headerType ) { #>
          <div class="tms-card__header">

            <# if ( headerType === 'icon' && iconMarkup ) { #>
              {{{ iconMarkup }}}
            <# } else if ( headerType === 'image' && image.url ) { #>
              <img src="{{ image.url }}" alt="{{ image.alt || '' }}" class="tms-card__image" />
            <# } #>

          </div><!-- /.tms-card__header -->
        <# } #>

        <div class="tms-card__body">

          <# if ( title ) { #>
            <h3 {{{ view.getRenderAttributeString( 'title' ) }}}>
              {{{ title }}}
            </h3>
          <# } #>

          <# if ( description ) { #>
            <p {{{ view.getRenderAttributeString( 'description' ) }}}>
              {{{ description }}}
            </p>
          <# } #>

        </div><!-- /.tms-card__body -->

        <# if ( linkType === 'button' ) { #>
          <div class="tms-button-group">
            <a href="{{ linkUrl }}" class="{{ buttonClassStr }}">

              <# if ( isScrollText ) { #>
                <span class="tms-scroll-text--visible">
              <# } #>

              <span {{{ view.getRenderAttributeString( 'button_label' ) }}}>
                {{{ buttonLabel }}}
              </span>

              <# if ( _.contains( [ 'duocolor', 'duocolor-outline' ], buttonStyle ) ) { #>
                <span class="tms-button__duocolor-icon">
                  {{{ buttonIconMarkup }}}
                </span>
              <# } else { #>
                {{{ buttonIconMarkup }}}
              <# } #>

              <# if ( isScrollText ) { #>
                </span><!-- /.tms-scroll-text--visible -->

                <span class="tms-scroll-text--hidden">
                  <span class="tms-button__text">{{{ buttonHiddenLabel }}}</span>
                  {{{ buttonIconMarkup }}}
                </span><!-- /.tms-scroll-text--hidden -->
              <# } #>

            </a><!-- /.tms-button -->
          </div><!-- /.tms-button-group -->
        <# } #>

        <# if ( linkType === 'card' ) { #>
          <a href="{{ linkUrl }}" class="tms-cover-link"></a>
        <# } #>

      </div><!-- /.tms-card__wrapper -->
    </div><!-- /.tms-card -->

    <?php
  }
}
