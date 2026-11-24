<?php
/**
 * Themeasy Elementor Widget: Card Extended
 *
 * Extended card widget featuring an image with vertical label, title, description,
 * overlay icon, and a flexible icon list (custom icons or star rating).
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
use Elementor\Repeater;
use Elementor\Utils;
use Elementor\Widget_Base;

/**
 * Responsible for registering controls and rendering the widget.
 */
class CardExtended extends Widget_Base {
  public function get_name() {
    return 'themeasy-card-extended';
  }

  public function get_title() {
    return esc_html__( 'Card Extended', 'themeasy-lite' );
  }

  public function get_icon() {
    return 'eicon-twitter-feed';
  }

  public function get_custom_help_url() {
    return 'https://hub.themeasy.co/docs';
  }

  public function get_categories() {
    return ['themeasy_blocks_category', 'themeasy_content_category'];
  }

  public function get_keywords() {
    return ['themeasy', 'card', 'extended', 'image', 'icon', 'rating', 'label'];
  }

  public function get_style_depends() {
    return ['themeasy-shared', 'themeasy-card-extended'];
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
    // Content section: Card
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'card_extended_content_section',
        [
          'label' => esc_html__( 'Card', 'themeasy-lite' ),
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

      $this->add_group_control(
        Group_Control_Image_Size::get_type(),
        [
          'name' => 'image_resolution',
          'exclude' => ['custom'],
          'include' => [],
          'default' => 'full',
          'condition' => [
            'image[url]!' => '',
          ],
        ]
      );

      $loading_description = esc_html__( 'Choose how this image loads. Lazy loading saves performance, while high priority is ideal for images at the top of the page.', 'themeasy-lite' );

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
          'description' => $loading_description,
          'condition' => [
            'image[url]!' => '',
          ],
        ]
      );

      $this->add_control(
        'label',
        [
          'label' => esc_html__( 'Vertical Label', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXT,
          'separator' => 'before',
          'default' => esc_html__( 'Label', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'Leave blank to hide', 'themeasy-lite' ),
          'condition' => [
            'image[url]!' => '',
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
          'default' => 'Lorem ipsum dolor',
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
          'default' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean lobortis magna et ipsum rutrum lobortis.',
          'placeholder' => esc_html__( 'Leave blank to hide', 'themeasy-lite' ),
        ]
      );

      $this->add_control(
        'overlay_icon',
        [
          'label' => esc_html__( 'Overlay Icon', 'themeasy-lite' ),
          'type' => Controls_Manager::ICONS,
          'separator' => 'before',
          'default' => [
            'value' => 'ty-feather-layers',
            'library' => 'ty-feather',
          ],
        ]
      );

      $this->add_control(
        'link_type',
        [
          'label' => esc_html__( 'Link Type', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'separator' => 'before',
          'default' => '',
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
            'url' => '#your-link',
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
    // Content section: Icon List
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'card_extended_icon_list_section',
        [
          'label' => esc_html__( 'Icon List', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
    );

      $this->add_control(
        'icon_list_type',
        [
          'label' => esc_html__( 'Icon List Type', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'custom_icons',
          'options' => [
            'custom_icons' => esc_html__( 'Custom Icons', 'themeasy-lite' ),
            'star_rating' => esc_html__( 'Star Rating', 'themeasy-lite' ),
            '' => esc_html__( 'None', 'themeasy-lite' ),
          ],
        ]
      );

      $this->add_control(
        'star_rating_icon',
        [
          'label' => esc_html__( 'Rating Icon', 'themeasy-lite' ),
          'type' => Controls_Manager::ICONS,
          'default' => [
            'value' => 'ty-feather-star',
            'library' => 'ty-feather',
          ],
          'condition' => [
            'icon_list_type' => 'star_rating',
          ],
        ]
      );

      $this->add_control(
        'star_rating',
        [
          'label' => esc_html__( 'Star Rating', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['un'],
          'range' => [
            'un' => [
              'min' => 0,
              'max' => 5,
              'step' => 1,
            ],
          ],
          'default' => [
            'unit' => 'un',
            'size' => 3,
          ],
          'condition' => [
            'icon_list_type' => 'star_rating',
          ],
        ]
      );

      $repeater = new Repeater();

      $repeater->add_control(
        'icon',
        [
          'label' => esc_html__( 'Icon', 'themeasy-lite' ),
          'type' => Controls_Manager::ICONS,
        ]
      );

      $repeater->add_control(
        'link',
        [
          'label' => esc_html__( 'URL', 'themeasy-lite' ),
          'type' => Controls_Manager::URL,
          'placeholder' => esc_html__( 'https://your-link.com', 'themeasy-lite' ),
          'options' => ['url', 'is_external', 'nofollow'],
          'default' => [
            'url' => '',
            'is_external' => false,
            'nofollow' => true,
          ],
        ]
      );

      $this->add_control(
        'icon_list',
        [
          'label' => esc_html__( 'Icon List', 'themeasy-lite' ),
          'type' => Controls_Manager::REPEATER,
          'fields' => $repeater->get_controls(),
          'default' => [
            [
              'icon' => ['value' => 'ty-feather-instagram', 'library' => 'ty-feather'],
              'link' => ['url' => '', 'is_external' => true, 'nofollow' => true],
            ],
            [
              'icon' => ['value' => 'ty-feather-x-twitter', 'library' => 'ty-feather'],
              'link' => ['url' => '', 'is_external' => true, 'nofollow' => true],
            ],
            [
              'icon' => ['value' => 'ty-feather-linkedin', 'library' => 'ty-feather'],
              'link' => ['url' => '', 'is_external' => true, 'nofollow' => true],
            ],
          ],
          'condition' => [
            'icon_list_type' => 'custom_icons',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Text Box
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'card_extended_box_style_section',
        [
          'label' => esc_html__( 'Text Box', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      $this->add_control(
        'card_background_color',
        [
          'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-card-extended .tms-card-extended__body' => 'background-color: {{VALUE}}',
          ],
        ]
      );

      $this->add_responsive_control(
        'card_padding',
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-card-extended .tms-card-extended__body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Border::get_type(),
        [
          'name' => 'card_border',
          'selector' => '{{WRAPPER}} .tms-card-extended .tms-card-extended__body',
        ]
      );

      $this->add_responsive_control(
        'card_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-card-extended .tms-card-extended__body' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Box_Shadow::get_type(),
        [
          'name' => 'card_box_shadow',
          'label' => esc_html__( 'Box Shadow', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-card-extended .tms-card-extended__body',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Image
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'card_extended_image_style_section',
        [
          'label' => esc_html__( 'Image', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'image[url]!' => '',
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
            '{{WRAPPER}} .tms-card-extended .tms-image__img' => 'opacity: {{SIZE}}',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Css_Filter::get_type(),
        [
          'name' => 'image_css_filters',
          'selector' => '{{WRAPPER}} .tms-card-extended .tms-image__img',
        ]
      );

      $this->add_responsive_control(
        'image_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-card-extended .tms-image__img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Vertical Label
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'card_extended_label_style_section',
        [
          'label' => esc_html__( 'Vertical Label', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'image[url]!' => '',
          ],
        ]
    );

      // Responsive: from 768px the label sits in the strip beside the 80% image (on the section
      // ground), below it the image is full width and the label lies on the photo.
      $this->add_responsive_control(
        'label_text_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-card-extended .tms-vertical-label .tms-vertical-label__text' => 'color: {{VALUE}}',
            '{{WRAPPER}} .tms-card-extended .tms-vertical-label .tms-vertical-label__line' => 'background-color: {{VALUE}}',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'label_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-card-extended .tms-vertical-label .tms-vertical-label__text',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Title
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'card_extended_title_style_section',
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
            '{{WRAPPER}} .tms-card-extended .tms-card__title' => 'color: {{VALUE}}',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'title_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-card-extended .tms-card__title',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Description
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'card_extended_description_style_section',
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
            '{{WRAPPER}} .tms-card-extended .tms-card__description' => 'color: {{VALUE}}',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'description_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-card-extended .tms-card__description',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Overlay Icon
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'card_extended_overlay_icon_style_section',
        [
          'label' => esc_html__( 'Overlay Icon', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      $this->add_control(
        'overlay_icon_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-card-extended .tms-card-extended__overlay-icon' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'overlay_icon_fill_color',
        [
          'label' => esc_html__( 'Fill Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'description' => esc_html__( 'Overrides the fill for SVG icons only.', 'themeasy-lite' ),
          'selectors' => [
            '{{WRAPPER}} .tms-card-extended .tms-card-extended__overlay-icon svg' => 'fill: {{VALUE}};',
          ],
          'conditions' => [
            'terms' => [
              ['name' => 'overlay_icon[library]', 'operator' => 'in', 'value' => themeasy_svg_icon_condition_libraries()],
            ],
          ],
        ]
      );

      $this->add_control(
        'overlay_icon_stroke_color',
        [
          'label' => esc_html__( 'Stroke Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'description' => esc_html__( 'Overrides the stroke for outline SVG icons only.', 'themeasy-lite' ),
          'selectors' => [
            '{{WRAPPER}} .tms-card-extended .tms-card-extended__overlay-icon svg' => 'stroke: {{VALUE}};',
          ],
          'conditions' => [
            'terms' => [
              ['name' => 'overlay_icon[library]', 'operator' => 'in', 'value' => themeasy_svg_icon_condition_libraries()],
            ],
          ],
        ]
      );

      $this->add_responsive_control(
        'overlay_icon_size',
        [
          'label' => esc_html__( 'Size', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem', 'em'],
          'range' => [
            'px' => ['min' => 0, 'max' => 100, 'step' => 1],
            'rem' => ['min' => 0, 'max' => 10, 'step' => 0.1],
            'em' => ['min' => 0, 'max' => 10, 'step' => 0.1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-card-extended .tms-card-extended__overlay-icon' => 'font-size: {{SIZE}}{{UNIT}};',
            '{{WRAPPER}} .tms-card-extended .tms-card-extended__overlay-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'overlay_icon_thickness',
        [
          'label' => esc_html__( 'Stroke Thickness', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => ['min' => 0.5, 'max' => 10, 'step' => 0.5],
          ],
          'default' => ['unit' => 'px', 'size' => 2],
          'selectors' => [
            '{{WRAPPER}} .tms-card-extended .tms-card-extended__overlay-icon svg,
             {{WRAPPER}} .tms-card-extended .tms-card-extended__overlay-icon svg *' => 'stroke-width: {{SIZE}}{{UNIT}};',
          ],
          'conditions' => [
            'terms' => [
              ['name' => 'overlay_icon[library]', 'operator' => 'in', 'value' => themeasy_svg_icon_condition_libraries()],
            ],
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Icon List
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'card_extended_icon_list_style_section',
        [
          'label' => esc_html__( 'Icon List', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      $this->add_control(
        'icon_list_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-card-extended .tms-horizontal-icons .tms-horizontal-icons__item .tms-horizontal-icons__icon' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'icon_list_fill_color',
        [
          'label' => esc_html__( 'Fill Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'description' => esc_html__( 'Overrides the fill for SVG icons only.', 'themeasy-lite' ),
          'selectors' => [
            '{{WRAPPER}} .tms-card-extended .tms-horizontal-icons .tms-horizontal-icons__item .tms-horizontal-icons__icon svg' => 'fill: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'icon_list_stroke_color',
        [
          'label' => esc_html__( 'Stroke Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'description' => esc_html__( 'Overrides the stroke for outline SVG icons only.', 'themeasy-lite' ),
          'selectors' => [
            '{{WRAPPER}} .tms-card-extended .tms-horizontal-icons .tms-horizontal-icons__item .tms-horizontal-icons__icon svg' => 'stroke: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'icon_list_hover_color',
        [
          'label' => esc_html__( 'Hover Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'separator' => 'before',
          'selectors' => [
            '{{WRAPPER}} .tms-card-extended .tms-horizontal-icons .tms-horizontal-icons__item .tms-horizontal-icons__link:hover .tms-horizontal-icons__icon' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'icon_list_size',
        [
          'label' => esc_html__( 'Size', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'separator' => 'before',
          'size_units' => ['px', 'rem', 'em'],
          'range' => [
            'px' => ['min' => 0, 'max' => 100, 'step' => 1],
            'rem' => ['min' => 0, 'max' => 10, 'step' => 0.1],
            'em' => ['min' => 0, 'max' => 10, 'step' => 0.1],
          ],
          'default' => [
            'unit' => 'rem',
            'size' => 1.2,
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-card-extended .tms-horizontal-icons .tms-horizontal-icons__item .tms-horizontal-icons__icon' => 'font-size: {{SIZE}}{{UNIT}};',
            '{{WRAPPER}} .tms-card-extended .tms-horizontal-icons .tms-horizontal-icons__item .tms-horizontal-icons__icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'icon_list_thickness',
        [
          'label' => esc_html__( 'Stroke Thickness', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => ['min' => 0.5, 'max' => 10, 'step' => 0.5],
          ],
          'default' => ['unit' => 'px', 'size' => 2],
          'selectors' => [
            '{{WRAPPER}} .tms-card-extended .tms-horizontal-icons .tms-horizontal-icons__item .tms-horizontal-icons__icon svg,
             {{WRAPPER}} .tms-card-extended .tms-horizontal-icons .tms-horizontal-icons__item .tms-horizontal-icons__icon svg *' => 'stroke-width: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'icon_list_alignment',
        [
          'label' => esc_html__( 'Alignment', 'themeasy-lite' ),
          'type' => Controls_Manager::CHOOSE,
          'separator' => 'before',
          'options' => [
            'flex-start' => [
              'title' => esc_html__( 'Left', 'themeasy-lite' ),
              'icon' => 'eicon-align-start-h',
            ],
            'center' => [
              'title' => esc_html__( 'Center', 'themeasy-lite' ),
              'icon' => 'eicon-align-center-h',
            ],
            'flex-end' => [
              'title' => esc_html__( 'Right', 'themeasy-lite' ),
              'icon' => 'eicon-align-end-h',
            ],
          ],
          'default' => 'flex-start',
          'toggle' => false,
          'selectors' => [
            '{{WRAPPER}} .tms-card-extended .tms-card__icons' => 'justify-content: {{VALUE}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Button
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'card_extended_button_styles_section',
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
    $has_image = (bool) $image_url;
    $label = $settings['label'] ?? '';
    $title = $settings['title'] ?? '';
    $description = $settings['description'] ?? '';
    $icon_list_type = $settings['icon_list_type'] ?? '';
    $link_type = $settings['link_type'] ?? '';

    // Button settings.
    $button_label = $settings['button_label'] ?? '';
    $button_icon = is_array( $settings['button_icon'] ?? null ) ? $settings['button_icon'] : [];
    $button_icon_align = $settings['button_icon_alignment'] ?? 'right';
    $button_style = $settings['button_style'] ?? 'solid-fill';

    // ------------------------------------------------------------------------
    // Wrapper classes + attributes.
    // ------------------------------------------------------------------------
    $wrapper_classes = ['tms-card-extended', 'tms-card'];
    $button_classes = ['tms-button'];

    if ( !$has_image ) {
      $wrapper_classes[] = 'tms-card-extended--no-image';
    }

    if ( 'left' === $button_icon_align ) {
      $button_classes[] = 'tms-button--icon-left';
    }

    if ( $button_style ) {
      $button_classes[] = 'tms-button--' . $button_style;
    }

    $wrapper_classes_output = implode( ' ', array_filter( $wrapper_classes ) );
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

    if ( 'button' === $link_type ) {
      $this->add_render_attribute( 'button_label', 'class', 'tms-button__text' );
      $this->add_inline_editing_attributes( 'button_label', 'none' );
    }

    // Button icon rendering.
    $button_icon_html = '';

    if ( 'button' === $link_type && !empty( $button_icon ) ) {
      $button_icon_html = themeasy_render_icon_html( $button_icon );

      if ( $button_icon_html ) {
        $button_icon_html = '<span class="tms-button__icon" aria-hidden="true">' . $button_icon_html . '</span>';
      }
    }

    $this->add_render_attribute( 'label', 'class', 'tms-vertical-label__text' );
    $this->add_inline_editing_attributes( 'label', 'none' );

    $this->add_render_attribute( 'title', 'class', 'tms-card__title' );
    $this->add_inline_editing_attributes( 'title', 'basic' );

    $this->add_render_attribute( 'description', 'class', 'tms-card__description' );
    $this->add_inline_editing_attributes( 'description', 'advanced' );

    // ------------------------------------------------------------------------
    // Output.
    // ------------------------------------------------------------------------
    ?>
    <div class="<?php echo esc_attr( $wrapper_classes_output ); ?>">

      <?php if ( $has_image ) : ?>
        <div class="tms-image">
          <?php
          $image_id = absint( $settings['image']['id'] ?? 0 );
          $image_alt = Control_Media::get_image_alt( $settings['image'] );
          $image_size = $settings['image_resolution_size'] ?? 'full';
          $image_loading_priority = $settings['image_loading_priority'] ?? 'lazy';

          themeasy_render_attachment_image(
            $image_id,
            $image_size,
            $image_url,
            $image_alt,
            'tms-image__img',
            [],
            $image_loading_priority
          );
          ?>
        </div>
      <?php endif; ?>

      <div class="tms-card-extended__body">

        <?php if ( $label ) : ?>
          <span class="tms-vertical-label">
            <span class="tms-vertical-label__line"></span>
            <span <?php $this->print_render_attribute_string( 'label' ); ?>>
              <?php echo esc_html( $label ); ?>
            </span>
          </span>
        <?php endif; ?>

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

        <?php
        $overlay_icon_html = themeasy_render_icon_html(
          is_array( $settings['overlay_icon'] ?? null ) ? $settings['overlay_icon'] : []
        );

        if ( $overlay_icon_html ) :
          ?>
          <span class="tms-card-extended__overlay-icon" aria-hidden="true">
            <?php echo wp_kses( $overlay_icon_html, themeasy_get_icon_kses_tags() ); ?>
          </span>
          <?php
        endif;
        ?>

        <?php if ( $icon_list_type ) : ?>
          <div class="tms-card__icons">
            <ul class="tms-horizontal-icons">
              <?php
              // ----------------------------------------------------------------
              // Custom icons.
              // ----------------------------------------------------------------
              if ( 'custom_icons' === $icon_list_type ) :
                $icons = $settings['icon_list'] ?? [];

                if ( !empty( $icons ) && is_array( $icons ) ) :
                  foreach ( $icons as $index => $icon_item ) :
                    $has_icon_link = !empty( $icon_item['link']['url'] );
                    $link_key = 'icon_link_' . $index;

                    if ( $has_icon_link ) {
                      $this->add_link_attributes( $link_key, $icon_item['link'], true );
                      themeasy_add_external_link_rel( $this, $link_key, $icon_item['link'] );
                    } else {
                      $this->add_render_attribute( $link_key, 'href', '#' );
                      $this->add_render_attribute( $link_key, 'role', 'button' );
                    }

                    $item_icon_html = themeasy_render_icon_html(
                      is_array( $icon_item['icon'] ?? null ) ? $icon_item['icon'] : []
                    );
                    ?>
                    <li class="tms-horizontal-icons__item">
                      <a <?php $this->print_render_attribute_string( $link_key ); ?> class="tms-horizontal-icons__link">
                        <?php if ( $item_icon_html ) : ?>
                          <span class="tms-horizontal-icons__icon" aria-hidden="true">
                            <?php echo wp_kses( $item_icon_html, themeasy_get_icon_kses_tags() ); ?>
                          </span>
                        <?php endif; ?>
                      </a>
                    </li>
                    <?php
                  endforeach;
                endif;
              endif;

              // ----------------------------------------------------------------
              // Star rating.
              // ----------------------------------------------------------------
              if ( 'star_rating' === $icon_list_type ) :
                $rating = isset( $settings['star_rating']['size'] ) ? (int) $settings['star_rating']['size'] : 0;
                $star_icon = is_array( $settings['star_rating_icon'] ?? null ) ? $settings['star_rating_icon'] : [];

                for ( $count = 1; $count <= 5; $count++ ) :
                  $item_classes = ['tms-horizontal-icons__item'];

                  if ( $count > $rating ) {
                    $item_classes[] = 'tms-horizontal-icons--disabled';
                  }

                  $star_icon_html = themeasy_render_icon_html( $star_icon );
                  ?>
                  <li class="<?php echo esc_attr( implode( ' ', $item_classes ) ); ?>">
                    <?php if ( $star_icon_html ) : ?>
                      <span class="tms-horizontal-icons__icon" aria-hidden="true">
                        <?php echo wp_kses( $star_icon_html, themeasy_get_icon_kses_tags() ); ?>
                      </span>
                    <?php endif; ?>
                  </li>
                  <?php
                endfor;
              endif;
              ?>
            </ul>
          </div>
        <?php endif; ?>

        <?php if ( 'button' === $link_type ) : ?>
          <div class="tms-button-group">
            <a <?php $this->print_render_attribute_string( 'link' ); ?> class="<?php echo esc_attr( $button_classes_output ); ?>">

              <span <?php $this->print_render_attribute_string( 'button_label' ); ?>>
                <?php echo wp_kses( $button_label, themeasy_get_kses_allowed_tags() ); ?>
              </span>

              <?php if ( in_array( $button_style, ['duocolor', 'duocolor-outline'], true ) ) : ?>
                <span class="tms-button__duocolor-icon">
                  <?php echo wp_kses( $button_icon_html, themeasy_get_icon_kses_tags() ); ?>
                </span>
              <?php else : ?>
                <?php echo wp_kses( $button_icon_html, themeasy_get_icon_kses_tags() ); ?>
              <?php endif; ?>

            </a><!-- /.tms-button -->
          </div><!-- /.tms-button-group -->
        <?php endif; ?>

      </div><!-- /.tms-card-extended__body -->

      <?php if ( 'card' === $link_type ) : ?>
        <a <?php $this->print_render_attribute_string( 'link' ); ?> class="tms-cover-link"></a>
      <?php endif; ?>

    </div><!-- /.tms-card-extended -->
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

      // User text reaches raw {{{ }}} output: sanitize with kses parity.
      var sanitizeInline = ( window.Themeasy && window.Themeasy.sanitizeInlineHtml )
        ? window.Themeasy.sanitizeInlineHtml
        : _.escape;

      // A blocked scheme yields '' — the <img> is then skipped entirely.
      var imageUrl     = safeUrl( ( settings.image && settings.image.url ) ? settings.image.url : '' );
      var hasImage     = !! imageUrl;
      var label        = settings.label || '';
      var title        = sanitizeInline( settings.title || '', 'rich' );
      var description  = sanitizeInline( settings.description || '', 'rich' );
      var iconListType = settings.icon_list_type || '';
      var linkType     = settings.link_type || '';

      // Button settings.
      var buttonLabel       = sanitizeInline( settings.button_label || '' );
      var buttonIconAlign   = settings.button_icon_alignment || 'right';
      var buttonStyle       = settings.button_style || 'solid-fill';

      // A blocked (or unset) URL falls back to the inert '#'.
      var linkUrl = safeUrl( ( settings.link && settings.link.url ) || '' ) || '#';

      // ------------------------------------------------------------------------
      // Wrapper classes + attributes.
      // ------------------------------------------------------------------------
      var wrapperClasses = [ 'tms-card-extended', 'tms-card' ];
      var buttonClasses  = [ 'tms-button' ];

      if ( ! hasImage ) {
        wrapperClasses.push( 'tms-card-extended--no-image' );
      }

      if ( buttonIconAlign === 'left' ) {
        buttonClasses.push( 'tms-button--icon-left' );
      }

      if ( buttonStyle ) {
        buttonClasses.push( 'tms-button--' + buttonStyle );
      }

      var wrapperClassStr = wrapperClasses.filter( Boolean ).join( ' ' );
      var buttonClassStr  = buttonClasses.filter( Boolean ).join( ' ' );

      // ------------------------------------------------------------------------
      // Render attributes.
      // ------------------------------------------------------------------------
      if ( linkType === 'button' ) {
        view.addRenderAttribute( 'button_label', 'class', 'tms-button__text' );
        view.addInlineEditingAttributes( 'button_label', 'none' );
      }

      view.addRenderAttribute( 'label', 'class', 'tms-vertical-label__text' );
      view.addInlineEditingAttributes( 'label', 'none' );

      view.addRenderAttribute( 'title', 'class', 'tms-card__title' );
      view.addInlineEditingAttributes( 'title', 'basic' );

      view.addRenderAttribute( 'description', 'class', 'tms-card__description' );
      view.addInlineEditingAttributes( 'description', 'advanced' );

      // ------------------------------------------------------------------------
      // Icons.
      // ------------------------------------------------------------------------
      var overlayIconMarkup = ( window.Themeasy && window.Themeasy.renderIconMarkup )
        ? window.Themeasy.renderIconMarkup( view, settings.overlay_icon, null, {} )
        : '';

      if ( overlayIconMarkup ) {
        overlayIconMarkup = '<span class="tms-card-extended__overlay-icon" aria-hidden="true">' + overlayIconMarkup + '</span>';
      }

      var starIconMarkup = ( window.Themeasy && window.Themeasy.renderIconMarkup )
        ? window.Themeasy.renderIconMarkup( view, settings.star_rating_icon, null, {} )
        : '';

      if ( starIconMarkup ) {
        starIconMarkup = '<span class="tms-horizontal-icons__icon" aria-hidden="true">' + starIconMarkup + '</span>';
      }

      var buttonIconMarkup = ( window.Themeasy && window.Themeasy.renderIconMarkup )
        ? window.Themeasy.renderIconMarkup( view, settings.button_icon, null, {} )
        : '';

      if ( buttonIconMarkup ) {
        buttonIconMarkup = '<span class="tms-button__icon" aria-hidden="true">' + buttonIconMarkup + '</span>';
      }

      var iconList = settings.icon_list || [];

      var rating = ( settings.star_rating && settings.star_rating.size )
        ? parseInt( settings.star_rating.size, 10 )
        : 0;
    #>

    <div class="{{ wrapperClassStr }}">

      <# if ( hasImage ) { #>
        <div class="tms-image">
          <img src="{{ imageUrl }}" alt="{{ settings.image.alt }}" class="tms-image__img" />
        </div>
      <# } #>

      <div class="tms-card-extended__body">

        <# if ( label ) { #>
          <span class="tms-vertical-label">
            <span class="tms-vertical-label__line"></span>
            <span {{{ view.getRenderAttributeString( 'label' ) }}}>{{ label }}</span>
          </span>
        <# } #>

        <# if ( title ) { #>
          <h3 {{{ view.getRenderAttributeString( 'title' ) }}}>{{{ title }}}</h3>
        <# } #>

        <# if ( description ) { #>
          <p {{{ view.getRenderAttributeString( 'description' ) }}}>{{{ description }}}</p>
        <# } #>

        <# if ( overlayIconMarkup ) { #>
          {{{ overlayIconMarkup }}}
        <# } #>

        <# if ( iconListType ) { #>
          <div class="tms-card__icons">
            <ul class="tms-horizontal-icons">

              <# if ( iconListType === 'custom_icons' && iconList && iconList.length ) { #>
                <# _.each( iconList, function( iconItem, index ) {
                  // A blocked (or unset) URL falls back to the inert '#'.
                  var href = safeUrl( ( iconItem.link && iconItem.link.url ) || '' ) || '#';

                  var itemIconMarkup = ( window.Themeasy && window.Themeasy.renderIconMarkup )
                    ? window.Themeasy.renderIconMarkup( view, iconItem.icon, null, {} )
                    : '';

                  if ( ! itemIconMarkup ) {
                    return;
                  }
                #>
                  <li class="tms-horizontal-icons__item">
                    <a href="{{ href }}" class="tms-horizontal-icons__link">
                      <span class="tms-horizontal-icons__icon" aria-hidden="true">{{{ itemIconMarkup }}}</span>
                    </a>
                  </li>
                <# } ); #>
              <# } #>

              <# if ( iconListType === 'star_rating' ) { #>
                <# for ( var count = 1; count <= 5; count++ ) {
                  var itemClass = ( count > rating )
                    ? 'tms-horizontal-icons__item tms-horizontal-icons--disabled'
                    : 'tms-horizontal-icons__item';
                #>
                  <li class="{{ itemClass }}">
                    {{{ starIconMarkup }}}
                  </li>
                <# } #>
              <# } #>

            </ul>
          </div>
        <# } #>

        <# if ( linkType === 'button' ) { #>
          <div class="tms-button-group">
            <a href="{{ linkUrl }}" class="{{ buttonClassStr }}">

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

            </a><!-- /.tms-button -->
          </div><!-- /.tms-button-group -->
        <# } #>

      </div><!-- /.tms-card-extended__body -->

      <# if ( linkType === 'card' ) { #>
        <a href="{{ linkUrl }}" class="tms-cover-link"></a>
      <# } #>

    </div><!-- /.tms-card-extended -->

    <?php
  }
}
