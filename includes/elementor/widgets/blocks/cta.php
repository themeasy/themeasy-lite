<?php
/**
 * Themeasy Elementor Widget: Call to Action
 *
 * Conversion-focused CTA block with three layout skins:
 *   - banner: horizontal band with optional media + content + action buttons.
 *   - cover:  full-bleed background image with overlay and centered content.
 *   - split:  50/50 split between media panel and content panel.
 *
 * Supports optional ribbon/badge, pre-title, icon or image header, headline,
 * description, and up to two action buttons (primary + optional secondary)
 * with independent styles and links.
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
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;
use Elementor\Utils;
use Elementor\Widget_Base;

/**
 * Responsible for registering controls and rendering the widget.
 */
class Cta extends Widget_Base {
  public function get_name() {
    return 'themeasy-cta';
  }

  public function get_title() {
    return esc_html__( 'Call to Action', 'themeasy-lite' );
  }

  public function get_icon() {
    return 'eicon-call-to-action';
  }

  public function get_custom_help_url() {
    return 'https://themeasy.co/help-center';
  }

  public function get_categories() {
    return ['themeasy_blocks_category', 'themeasy_content_category'];
  }

  public function get_keywords() {
    return ['themeasy', 'cta', 'call to action', 'banner', 'promo', 'conversion', 'ribbon'];
  }

  public function get_style_depends() {
    return ['themeasy-shared', 'themeasy-cta'];
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
    // Content section: Layout
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'cta_layout_section',
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
          'default' => 'banner',
          'options' => [
            'banner' => esc_html__( 'Banner (Inline Band)', 'themeasy-lite' ),
            'cover' => esc_html__( 'Cover (Full-bleed Background)', 'themeasy-lite' ),
            'split' => esc_html__( 'Split (Side-by-side Media)', 'themeasy-lite' ),
          ],
          'description' => esc_html__( 'Choose the overall composition of the CTA block.', 'themeasy-lite' ),
        ]
      );

      $this->add_control(
        'split_side',
        [
          'label' => esc_html__( 'Media Side', 'themeasy-lite' ),
          'type' => Controls_Manager::CHOOSE,
          'options' => [
            'left' => [
              'title' => esc_html__( 'Left', 'themeasy-lite' ),
              'icon' => 'eicon-h-align-left',
            ],
            'right' => [
              'title' => esc_html__( 'Right', 'themeasy-lite' ),
              'icon' => 'eicon-h-align-right',
            ],
          ],
          'default' => 'left',
          'toggle' => false,
          'condition' => [
            'skin' => 'split',
          ],
        ]
      );

      $this->add_responsive_control(
        'content_horizontal_alignment',
        [
          'label' => esc_html__( 'Content Alignment', 'themeasy-lite' ),
          'type' => Controls_Manager::CHOOSE,
          'options' => [
            'start' => [
              'title' => esc_html__( 'Left', 'themeasy-lite' ),
              'icon' => 'eicon-text-align-left',
            ],
            'center' => [
              'title' => esc_html__( 'Center', 'themeasy-lite' ),
              'icon' => 'eicon-text-align-center',
            ],
            'end' => [
              'title' => esc_html__( 'Right', 'themeasy-lite' ),
              'icon' => 'eicon-text-align-right',
            ],
          ],
          'default' => 'start',
          'selectors' => [
            '{{WRAPPER}} .tms-cta__body' => 'align-items: {{VALUE}}; text-align: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'content_vertical_alignment',
        [
          'label' => esc_html__( 'Vertical Alignment', 'themeasy-lite' ),
          'type' => Controls_Manager::CHOOSE,
          'options' => [
            'flex-start' => [
              'title' => esc_html__( 'Top', 'themeasy-lite' ),
              'icon' => 'eicon-v-align-top',
            ],
            'center' => [
              'title' => esc_html__( 'Middle', 'themeasy-lite' ),
              'icon' => 'eicon-v-align-middle',
            ],
            'flex-end' => [
              'title' => esc_html__( 'Bottom', 'themeasy-lite' ),
              'icon' => 'eicon-v-align-bottom',
            ],
          ],
          'default' => 'center',
          'selectors' => [
            '{{WRAPPER}} .tms-cta__body' => 'justify-content: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'min_height',
        [
          'label' => esc_html__( 'Minimum Height', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'vh', 'rem'],
          'range' => [
            'px' => ['min' => 80, 'max' => 1200, 'step' => 1],
            'vh' => ['min' => 10, 'max' => 100, 'step' => 1],
            'rem' => ['min' => 5, 'max' => 80, 'step' => 0.5],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-cta' => 'min-height: {{SIZE}}{{UNIT}};',
          ],
          // Available on every skin: it is what gives Vertical Alignment room to
          // work on the Banner, which has no min-height of its own.
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Content
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'cta_content_section',
        [
          'label' => esc_html__( 'Content', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $this->add_control(
        'badge_text',
        [
          'label' => esc_html__( 'Ribbon / Badge', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXT,
          'default' => '',
          'placeholder' => esc_html__( 'e.g. Limited Time', 'themeasy-lite' ),
          'description' => esc_html__( 'Leave blank to hide the ribbon.', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
        ]
      );

      $this->add_control(
        'badge_position',
        [
          'label' => esc_html__( 'Ribbon Position', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'top-right',
          'options' => [
            'top-left' => esc_html__( 'Top Left', 'themeasy-lite' ),
            'top-right' => esc_html__( 'Top Right', 'themeasy-lite' ),
            'inline' => esc_html__( 'Inline (Above Title)', 'themeasy-lite' ),
          ],
          'condition' => [
            'badge_text!' => '',
          ],
        ]
      );

      $this->add_control(
        'media_type',
        [
          'label' => esc_html__( 'Media Type', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'separator' => 'before',
          'default' => 'icon',
          'options' => [
            'icon' => esc_html__( 'Icon', 'themeasy-lite' ),
            'image' => esc_html__( 'Image', 'themeasy-lite' ),
            '' => esc_html__( 'None', 'themeasy-lite' ),
          ],
          'description' => esc_html__(
            'For the Split skin, this is the side media. For Banner/Cover, it appears before the title.',
            'themeasy-lite'
          ),
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
            'media_type' => 'icon',
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
            'media_type' => 'image',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Image_Size::get_type(),
        [
          'name' => 'image_resolution',
          'exclude' => ['custom'],
          'include' => [],
          'default' => 'large',
          'condition' => [
            'media_type' => 'image',
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
            'lazy' => esc_html__( 'Load when needed (Lazy Loading)', 'themeasy-lite' ),
            'high' => esc_html__( 'Prioritize loading (Above the Fold)', 'themeasy-lite' ),
          ],
          'condition' => [
            'media_type' => 'image',
          ],
        ]
      );

      $this->add_control(
        'pre_title',
        [
          'label' => esc_html__( 'Pre Title', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXT,
          'separator' => 'before',
          'default' => esc_html__( 'Ready to start?', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'Leave blank to hide', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
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
          'default' => esc_html__( 'Ready to grow your business?', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'Leave blank to hide', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
        ]
      );

      $this->add_control(
        'title_tag',
        [
          'label' => esc_html__( 'Title Tag', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'h2',
          'options' => [
            'h1' => 'H1',
            'h2' => 'H2',
            'h3' => 'H3',
            'h4' => 'H4',
            'h5' => 'H5',
            'h6' => 'H6',
            'div' => 'DIV',
          ],
          'condition' => [
            'title!' => '',
          ],
        ]
      );

      $this->add_control(
        'description',
        [
          'label' => esc_html__( 'Description', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXTAREA,
          'separator' => 'before',
          'default' => esc_html__(
            'Join thousands of teams already building faster with a toolkit made for modern websites.',
            'themeasy-lite'
          ),
          'placeholder' => esc_html__( 'Leave blank to hide', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Background Media
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'cta_background_media_section',
        [
          'label' => esc_html__( 'Background Media', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
          'condition' => [
            'skin' => 'cover',
          ],
        ]
      );

      $this->add_control(
        'background_image',
        [
          'label' => esc_html__( 'Background Image', 'themeasy-lite' ),
          'type' => Controls_Manager::MEDIA,
          'default' => [
            'url' => Utils::get_placeholder_image_src(),
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Image_Size::get_type(),
        [
          'name' => 'background_image_resolution',
          'exclude' => ['custom'],
          'include' => [],
          'default' => 'full',
          'condition' => [
            'background_image[url]!' => '',
          ],
        ]
      );

      $this->add_control(
        'background_loading_priority',
        [
          'label' => esc_html__( 'Loading Behavior', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'lazy',
          'options' => [
            'lazy' => esc_html__( 'Load when needed (Lazy Loading)', 'themeasy-lite' ),
            'high' => esc_html__( 'Prioritize loading (Above the Fold)', 'themeasy-lite' ),
          ],
          'condition' => [
            'background_image[url]!' => '',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Buttons
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'cta_buttons_section',
        [
          'label' => esc_html__( 'Buttons', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $this->register_button_content_controls( 'primary', esc_html__( 'Get Started', 'themeasy-lite' ), 'arrow-right' );

      $this->add_control(
        'show_secondary_button',
        [
          'label' => esc_html__( 'Show Secondary Button', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'separator' => 'before',
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => '',
        ]
      );

      $this->register_button_content_controls(
        'secondary',
        esc_html__( 'Learn More', 'themeasy-lite' ),
        '',
        'show_secondary_button'
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Box
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'cta_box_style_section',
        [
          'label' => esc_html__( 'Box', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_responsive_control(
        'box_padding',
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', 'em'],
          'default' => [
            'top' => 48, 'right' => 48, 'bottom' => 48, 'left' => 48, 'unit' => 'px', 'isLinked' => true,
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-cta__body'
              => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'content_gap',
        [
          'label' => esc_html__( 'Items Spacing', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem'],
          'range' => [
            'px' => ['min' => 0, 'max' => 80, 'step' => 1],
            'rem' => ['min' => 0, 'max' => 6, 'step' => 0.1],
          ],
          'default' => ['unit' => 'px', 'size' => 16],
          'selectors' => [
            '{{WRAPPER}} .tms-cta__body' => 'gap: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Background::get_type(),
        [
          'name' => 'box_background',
          'label' => esc_html__( 'Background', 'themeasy-lite' ),
          'types' => ['classic', 'gradient'],
          'selector' => '{{WRAPPER}} .tms-cta',
          'condition' => [
            'skin!' => 'cover',
          ],
        ]
      );

      $this->add_control(
        'box_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', '%'],
          'default' => [
            'top' => 16, 'right' => 16, 'bottom' => 16, 'left' => 16, 'unit' => 'px', 'isLinked' => true,
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-cta'
              => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Border::get_type(),
        [
          'name' => 'box_border',
          'selector' => '{{WRAPPER}} .tms-cta',
        ]
      );

      $this->add_group_control(
        Group_Control_Box_Shadow::get_type(),
        [
          'name' => 'box_shadow',
          'label' => esc_html__( 'Box Shadow', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-cta',
        ]
      );

      $this->add_responsive_control(
        'split_media_width',
        [
          'label' => esc_html__( 'Media Panel Width', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['%', 'px'],
          'range' => [
            '%' => ['min' => 20, 'max' => 80, 'step' => 1],
            'px' => ['min' => 120, 'max' => 900, 'step' => 4],
          ],
          'default' => ['unit' => '%', 'size' => 50],
          'selectors' => [
            '{{WRAPPER}} .tms-cta--skin-split' => '--tms-cta-media-size: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'skin' => 'split',
          ],
        ]
      );

      $this->add_control(
        'cover_overlay_color',
        [
          'label' => esc_html__( 'Overlay Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'separator' => 'before',
          'selectors' => [
            '{{WRAPPER}} .tms-cta__overlay' => 'background-color: {{VALUE}};',
          ],
          'condition' => [
            'skin' => 'cover',
            'background_image[url]!' => '',
          ],
        ]
      );

      $this->add_control(
        'cover_overlay_opacity',
        [
          'label' => esc_html__( 'Overlay Opacity', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['u'],
          'range' => ['u' => ['min' => 0, 'max' => 1, 'step' => 0.05]],
          // The default MUST name the unit: Elementor merges the widget range
          // into its own unit map, so without it the control falls back to
          // unit 'px' and renders the 0-100 range instead of the 0-1 CSS scale.
          'default' => ['unit' => 'u', 'size' => 0.45],
          'selectors' => [
            '{{WRAPPER}} .tms-cta__overlay' => 'opacity: {{SIZE}};',
          ],
          'condition' => [
            'skin' => 'cover',
            'background_image[url]!' => '',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Icon / Image
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'cta_media_style_section',
        [
          'label' => esc_html__( 'Icon / Image', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'media_type!' => '',
          ],
        ]
      );

      $this->add_responsive_control(
        'icon_size',
        [
          'label' => esc_html__( 'Icon Size', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem', 'em'],
          'range' => [
            'px' => ['min' => 8, 'max' => 200, 'step' => 1],
            'rem' => ['min' => 0.5, 'max' => 12, 'step' => 0.1],
          ],
          'default' => ['unit' => 'px', 'size' => 56],
          'selectors' => [
            '{{WRAPPER}} .tms-cta__icon' => 'font-size: {{SIZE}}{{UNIT}};',
            '{{WRAPPER}} svg.tms-cta__icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'media_type' => 'icon',
          ],
        ]
      );

      $this->add_control(
        'icon_color',
        [
          'label' => esc_html__( 'Icon Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-cta__icon' => 'color: {{VALUE}};',
          ],
          'condition' => [
            'media_type' => 'icon',
          ],
        ]
      );

      $this->add_control(
        'icon_fill_color',
        [
          'label' => esc_html__( 'Icon Fill Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} svg.tms-cta__icon' => 'fill: {{VALUE}};',
          ],
          'conditions' => [
            'terms' => [
              ['name' => 'media_type', 'operator' => '==', 'value' => 'icon'],
              ['name' => 'icon[library]', 'operator' => 'in', 'value' => themeasy_svg_icon_condition_libraries()],
            ],
          ],
        ]
      );

      $this->add_control(
        'icon_stroke_color',
        [
          'label' => esc_html__( 'Icon Stroke Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} svg.tms-cta__icon' => 'stroke: {{VALUE}};',
          ],
          'conditions' => [
            'terms' => [
              ['name' => 'media_type', 'operator' => '==', 'value' => 'icon'],
              ['name' => 'icon[library]', 'operator' => 'in', 'value' => themeasy_svg_icon_condition_libraries()],
            ],
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
            'px' => ['min' => 0.5, 'max' => 10, 'step' => 0.5],
          ],
          'default' => ['unit' => 'px', 'size' => 2],
          'selectors' => [
            '{{WRAPPER}} svg.tms-cta__icon,
             {{WRAPPER}} svg.tms-cta__icon *' => 'stroke-width: {{SIZE}}{{UNIT}};',
          ],
          'conditions' => [
            'terms' => [
              ['name' => 'media_type', 'operator' => '==', 'value' => 'icon'],
              ['name' => 'icon[library]', 'operator' => 'in', 'value' => themeasy_svg_icon_condition_libraries()],
            ],
          ],
        ]
      );

      $this->add_responsive_control(
        'image_max_width',
        [
          'label' => esc_html__( 'Image Max Width', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', '%', 'rem'],
          'range' => [
            'px' => ['min' => 40, 'max' => 800, 'step' => 1],
            '%' => ['min' => 10, 'max' => 100, 'step' => 1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-cta__image' => 'max-width: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'media_type' => 'image',
          ],
        ]
      );

      $this->add_control(
        'image_border_radius',
        [
          'label' => esc_html__( 'Image Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', '%', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-cta__image'
              => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
          'condition' => [
            'media_type' => 'image',
          ],
        ]
      );

      $this->add_responsive_control(
        'media_spacing',
        [
          'label' => esc_html__( 'Spacing', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem'],
          'range' => [
            'px' => ['min' => 0, 'max' => 120, 'step' => 1],
            'rem' => ['min' => 0, 'max' => 8, 'step' => 0.1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-cta__media' => 'margin-bottom: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Ribbon / Badge
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'cta_ribbon_style_section',
        [
          'label' => esc_html__( 'Ribbon / Badge', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'badge_text!' => '',
          ],
        ]
      );

      $this->add_control(
        'ribbon_text_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-cta__ribbon' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'ribbon_background_color',
        [
          'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-cta__ribbon' => 'background-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'ribbon_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-cta__ribbon',
        ]
      );

      $this->add_responsive_control(
        'ribbon_padding',
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'em'],
          'selectors' => [
            '{{WRAPPER}} .tms-cta__ribbon'
              => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'ribbon_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'em', '%'],
          'selectors' => [
            '{{WRAPPER}} .tms-cta__ribbon'
              => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Pre Title
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'cta_pre_title_style_section',
        [
          'label' => esc_html__( 'Pre Title', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'pre_title!' => '',
          ],
        ]
      );

      $this->add_control(
        'pre_title_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-cta__pre-title' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'pre_title_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-cta__pre-title',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Title
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'cta_title_style_section',
        [
          'label' => esc_html__( 'Title', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'title!' => '',
          ],
        ]
      );

      $this->add_control(
        'title_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-cta__title' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'title_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-cta__title',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Description
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'cta_description_style_section',
        [
          'label' => esc_html__( 'Description', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'description!' => '',
          ],
        ]
      );

      $this->add_control(
        'description_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-cta__description' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'description_font_size',
        [
          'label' => esc_html__( 'Font Size', 'themeasy-lite' ),
          'description' => esc_html__( 'Steps from the global Body Sizes scale. Default inherits the standard body size.', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => '',
          'options' => [
            '' => esc_html__( 'Default', 'themeasy-lite' ),
            'xs' => esc_html__( 'Extra Small', 'themeasy-lite' ),
            'sm' => esc_html__( 'Small', 'themeasy-lite' ),
            'md' => esc_html__( 'Medium', 'themeasy-lite' ),
            'lg' => esc_html__( 'Large', 'themeasy-lite' ),
            'xl' => esc_html__( 'Extra Large', 'themeasy-lite' ),
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-cta__description' => 'font-size: var(--tms-font-size-{{VALUE}});',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'description_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-cta__description',
        ]
      );

      $this->add_responsive_control(
        'description_max_width',
        [
          'label' => esc_html__( 'Max Width', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem', 'ch', '%'],
          'range' => [
            'px' => ['min' => 200, 'max' => 1200, 'step' => 1],
            'ch' => ['min' => 20, 'max' => 120, 'step' => 1],
            '%' => ['min' => 10, 'max' => 100, 'step' => 1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-cta__description' => 'max-width: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Buttons Wrapper
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'cta_buttons_wrapper_style_section',
        [
          'label' => esc_html__( 'Buttons', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_responsive_control(
        'buttons_layout',
        [
          'label' => esc_html__( 'Layout', 'themeasy-lite' ),
          'type' => Controls_Manager::CHOOSE,
          'options' => [
            'row' => [
              'title' => esc_html__( 'Row', 'themeasy-lite' ),
              'icon' => 'eicon-ellipsis-h',
            ],
            'column' => [
              'title' => esc_html__( 'Column', 'themeasy-lite' ),
              'icon' => 'eicon-ellipsis-v',
            ],
          ],
          'default' => 'row',
          'toggle' => false,
          'selectors' => [
            '{{WRAPPER}} .tms-cta__actions' => 'flex-direction: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'buttons_gap',
        [
          'label' => esc_html__( 'Gap', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem'],
          'range' => [
            'px' => ['min' => 0, 'max' => 80, 'step' => 1],
            'rem' => ['min' => 0, 'max' => 6, 'step' => 0.1],
          ],
          'default' => ['unit' => 'px', 'size' => 12],
          'selectors' => [
            '{{WRAPPER}} .tms-cta__actions' => 'gap: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'buttons_top_spacing',
        [
          'label' => esc_html__( 'Top Spacing', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem'],
          'range' => [
            'px' => ['min' => 0, 'max' => 120, 'step' => 1],
            'rem' => ['min' => 0, 'max' => 8, 'step' => 0.1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-cta__actions' => 'margin-top: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Primary Button
    // ------------------------------------------------------------------------
    $this->register_button_style_controls( 'primary', esc_html__( 'Primary Button', 'themeasy-lite' ) );

    // ------------------------------------------------------------------------
    // Style section: Secondary Button
    // ------------------------------------------------------------------------
    $this->register_button_style_controls(
      'secondary',
      esc_html__( 'Secondary Button', 'themeasy-lite' ),
      ['show_secondary_button' => 'yes']
    );
  }

  /**
   * Register the content-side controls for a single action button.
   *
   * @param string $prefix        Prefix for control ids (primary|secondary).
   * @param string $default_label Default button label text.
   * @param string $default_icon  Optional default feather icon slug (rendered via the ty-feather library).
   * @param string $show_switch   Control id of the "show" switcher (or '' when always visible).
   * @return void
   */
  private function register_button_content_controls(
    string $prefix,
    string $default_label,
    string $default_icon = '',
    string $show_switch = ''
  ): void {
    $base_condition = ( '' !== $show_switch ) ? [$show_switch => 'yes'] : [];

    $this->add_control(
      "{$prefix}_button_heading",
      [
        'label' => ( 'primary' === $prefix )
          ? esc_html__( 'Primary Button', 'themeasy-lite' )
          : esc_html__( 'Secondary Button', 'themeasy-lite' ),
        'type' => Controls_Manager::HEADING,
        'condition' => $base_condition,
      ]
    );

    $this->add_control(
      "{$prefix}_button_label",
      [
        'label' => esc_html__( 'Label', 'themeasy-lite' ),
        'type' => Controls_Manager::TEXT,
        'default' => $default_label,
        'placeholder' => esc_html__( 'Type your text here', 'themeasy-lite' ),
        'dynamic' => ['active' => true],
        'condition' => $base_condition,
      ]
    );

    $icon_default = $default_icon
      ? ['value' => 'ty-feather-' . $default_icon, 'library' => 'ty-feather']
      : [];

    $this->add_control(
      "{$prefix}_button_icon",
      [
        'label' => esc_html__( 'Icon', 'themeasy-lite' ),
        'type' => Controls_Manager::ICONS,
        'default' => $icon_default,
        'condition' => $base_condition,
      ]
    );

    $this->add_control(
      "{$prefix}_button_icon_alignment",
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
        'condition' => $base_condition,
      ]
    );

    $this->add_control(
      "{$prefix}_button_link",
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
        'condition' => $base_condition,
      ]
    );
  }

  /**
   * Register the style-side section for a single action button.
   *
   * @param string $prefix          Prefix for control ids (primary|secondary).
   * @param string $label           Section label.
   * @param array  $extra_condition Extra condition merged into the section condition.
   * @return void
   */
  private function register_button_style_controls( string $prefix, string $label, array $extra_condition = [] ): void {
    $this->start_controls_section(
      "cta_{$prefix}_button_style_section",
        [
          'label' => $label,
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => $extra_condition,
        ]
      );

      $this->add_control(
        "{$prefix}_button_style",
        [
          'label' => esc_html__( 'Button Style', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => ( 'primary' === $prefix ) ? 'solid-fill' : 'outline',
          'options' => [
            'simple' => esc_html__( 'Simple (Text Only)', 'themeasy-lite' ),
            'solid-fill' => esc_html__( 'Solid Fill', 'themeasy-lite' ),
            'outline' => esc_html__( 'Outline', 'themeasy-lite' ),
            'muted-fill' => esc_html__( 'Muted Fill', 'themeasy-lite' ),
            'muted-outline' => esc_html__( 'Muted Outline', 'themeasy-lite' ),
          ],
        ]
      );

      $this->start_controls_tabs( "{$prefix}_button_color_tabs" );

        $this->start_controls_tab(
          "{$prefix}_button_normal_tab",
          [
            'label' => esc_html__( 'Normal', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            "{$prefix}_button_background_color",
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                "{{WRAPPER}} .tms-cta .tms-cta__button--{$prefix}" => 'background-color: {{VALUE}};',
              ],
              'condition' => [
                "{$prefix}_button_style!" => ['simple', 'outline'],
              ],
            ]
          );

          $this->add_control(
            "{$prefix}_button_border_color",
            [
              'label' => esc_html__( 'Border Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                "{{WRAPPER}} .tms-cta .tms-cta__button--{$prefix}" => 'border-color: {{VALUE}};',
              ],
              'condition' => [
                "{$prefix}_button_style" => ['outline', 'muted-outline'],
              ],
            ]
          );

          $this->add_control(
            "{$prefix}_button_text_color",
            [
              'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                "{{WRAPPER}} .tms-cta .tms-cta__button--{$prefix} .tms-button__text,
                 {{WRAPPER}} .tms-cta .tms-cta__button--{$prefix} .tms-button__text > *,
                 {{WRAPPER}} .tms-cta .tms-cta__button--{$prefix} .tms-button__icon,
                 {{WRAPPER}} .tms-cta .tms-cta__button--{$prefix} svg" => 'color: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            "{$prefix}_button_icon_fill_color",
            [
              'label' => esc_html__( 'Icon Fill Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                "{{WRAPPER}} .tms-cta .tms-cta__button--{$prefix} svg" => 'fill: {{VALUE}};',
              ],
              'conditions' => [
                'terms' => [
                  [
                    'name' => "{$prefix}_button_icon" . '[library]',
                    'operator' => 'in',
                    'value' => themeasy_svg_icon_condition_libraries(),
                  ],
                ],
              ],
            ]
          );

          $this->add_control(
            "{$prefix}_button_icon_stroke_color",
            [
              'label' => esc_html__( 'Icon Stroke Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                "{{WRAPPER}} .tms-cta .tms-cta__button--{$prefix} svg" => 'stroke: {{VALUE}};',
              ],
              'conditions' => [
                'terms' => [
                  [
                    'name' => "{$prefix}_button_icon" . '[library]',
                    'operator' => 'in',
                    'value' => themeasy_svg_icon_condition_libraries(),
                  ],
                ],
              ],
            ]
          );

        $this->end_controls_tab();

        $this->start_controls_tab(
          "{$prefix}_button_hover_tab",
          [
            'label' => esc_html__( 'Hover', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            "{$prefix}_button_hover_background_color",
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                "{{WRAPPER}} .tms-cta .tms-cta__button--{$prefix}:hover,
                 {{WRAPPER}} .tms-cta .tms-cta__button--{$prefix}:focus-visible" => 'background-color: {{VALUE}};',
              ],
              'condition' => [
                "{$prefix}_button_style!" => 'simple',
              ],
            ]
          );

          $this->add_control(
            "{$prefix}_button_hover_border_color",
            [
              'label' => esc_html__( 'Border Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                "{{WRAPPER}} .tms-cta .tms-cta__button--{$prefix}:hover,
                 {{WRAPPER}} .tms-cta .tms-cta__button--{$prefix}:focus-visible" => 'border-color: {{VALUE}};',
              ],
              'condition' => [
                "{$prefix}_button_style" => ['outline', 'muted-outline'],
              ],
            ]
          );

          $this->add_control(
            "{$prefix}_button_hover_text_color",
            [
              'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                "{{WRAPPER}} .tms-cta .tms-cta__button--{$prefix}:hover .tms-button__text,
                 {{WRAPPER}} .tms-cta .tms-cta__button--{$prefix}:hover .tms-button__text > *,
                 {{WRAPPER}} .tms-cta .tms-cta__button--{$prefix}:hover .tms-button__icon,
                 {{WRAPPER}} .tms-cta .tms-cta__button--{$prefix}:hover svg,
                 {{WRAPPER}} .tms-cta .tms-cta__button--{$prefix}:focus-visible .tms-button__text,
                 {{WRAPPER}} .tms-cta .tms-cta__button--{$prefix}:focus-visible .tms-button__icon,
                 {{WRAPPER}} .tms-cta .tms-cta__button--{$prefix}:focus-visible svg" => 'color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => "{$prefix}_button_typography",
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => "{{WRAPPER}} .tms-cta .tms-cta__button--{$prefix} .tms-button__text",
          'separator' => 'before',
        ]
      );

      $this->add_responsive_control(
        "{$prefix}_button_icon_size",
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
            "{{WRAPPER}} .tms-cta .tms-cta__button--{$prefix} .tms-button__icon" => 'font-size: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        "{$prefix}_button_icon_thickness",
        [
          'label' => esc_html__( 'Icon Thickness', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => ['min' => 0.5, 'max' => 10, 'step' => 0.5],
          ],
          'default' => ['unit' => 'px', 'size' => 2],
          'selectors' => [
            "{{WRAPPER}} .tms-cta .tms-cta__button--{$prefix} svg,
             {{WRAPPER}} .tms-cta .tms-cta__button--{$prefix} svg *" => 'stroke-width: {{SIZE}}{{UNIT}};',
          ],
          'conditions' => [
            'terms' => [
              ['name' => "{$prefix}_button_icon" . '[library]', 'operator' => 'in', 'value' => themeasy_svg_icon_condition_libraries()],
            ],
          ],
        ]
      );

      $this->add_responsive_control(
        "{$prefix}_button_padding",
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', 'em'],
          'selectors' => [
            "{{WRAPPER}} .tms-cta .tms-cta__button--{$prefix}"
              => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
          'condition' => [
            "{$prefix}_button_style!" => 'simple',
          ],
        ]
      );

      $this->add_control(
        "{$prefix}_button_border_radius",
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', '%'],
          'selectors' => [
            "{{WRAPPER}} .tms-cta .tms-cta__button--{$prefix}"
              => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
          'condition' => [
            "{$prefix}_button_style!" => 'simple',
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
    $skin = $settings['skin'] ?? 'banner';
    $split_side = $settings['split_side'] ?? 'left';
    $badge_text = $settings['badge_text'] ?? '';
    $badge_position = $settings['badge_position'] ?? 'top-right';
    $media_type = $settings['media_type'] ?? '';
    $pre_title = $settings['pre_title'] ?? '';
    $title = $settings['title'] ?? '';
    $title_tag = themeasy_sanitize_heading_tag(
      $settings['title_tag'] ?? 'h2',
      'h2',
      ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div']
    );
    $description = $settings['description'] ?? '';
    $show_secondary = ( 'yes' === ( $settings['show_secondary_button'] ?? '' ) );
    $bg_image = $settings['background_image'] ?? [];
    $bg_image_url = $bg_image['url'] ?? '';
    $bg_image_size = $settings['background_image_resolution_size'] ?? 'full';
    $bg_image_id = absint( $bg_image['id'] ?? 0 );
    $bg_image_alt = Control_Media::get_image_alt( $bg_image );
    $bg_priority = $settings['background_loading_priority'] ?? 'lazy';

    // ------------------------------------------------------------------------
    // Wrapper classes + attributes.
    // ------------------------------------------------------------------------
    $wrapper_classes = ['tms-cta', 'tms-cta--skin-' . $skin];

    if ( 'split' === $skin ) {
      $wrapper_classes[] = 'tms-cta--media-' . $split_side;
    }

    // Gates the light-on-dark text: without the image there is no overlay to
    // sit on, so the block falls back to the regular token colors.
    if ( 'cover' === $skin && $bg_image_url ) {
      $wrapper_classes[] = 'tms-cta--has-cover-image';
    }

    if ( $badge_text ) {
      $wrapper_classes[] = 'tms-cta--has-ribbon';
      $wrapper_classes[] = 'tms-cta--ribbon-' . $badge_position;
    }

    $wrapper_classes_output = implode( ' ', array_filter( $wrapper_classes ) );

    // ------------------------------------------------------------------------
    // Inline editing attributes.
    // ------------------------------------------------------------------------
    if ( $badge_text ) {
      $this->add_render_attribute( 'badge_text', 'class', 'tms-cta__ribbon' );
      $this->add_inline_editing_attributes( 'badge_text', 'none' );
    }

    if ( $pre_title ) {
      $this->add_render_attribute( 'pre_title', 'class', 'tms-cta__pre-title' );
      $this->add_inline_editing_attributes( 'pre_title', 'none' );
    }

    if ( $title ) {
      $this->add_render_attribute( 'title', 'class', 'tms-cta__title' );
      $this->add_inline_editing_attributes( 'title', 'basic' );
    }

    if ( $description ) {
      $this->add_render_attribute( 'description', 'class', 'tms-cta__description' );
      $this->add_inline_editing_attributes( 'description', 'advanced' );
    }

    // ------------------------------------------------------------------------
    // Output.
    // ------------------------------------------------------------------------
    ?>
    <div class="<?php echo esc_attr( $wrapper_classes_output ); ?>">

      <?php if ( 'cover' === $skin && $bg_image_url ) : ?>
        <div class="tms-cta__background" aria-hidden="true">
          <?php
          themeasy_render_attachment_image(
            $bg_image_id,
            $bg_image_size,
            $bg_image_url,
            $bg_image_alt,
            'tms-cta__background-image',
            [],
            $bg_priority
          );
          ?>
          <span class="tms-cta__overlay"></span>
        </div>
      <?php endif; ?>

      <?php if ( $badge_text && 'inline' !== $badge_position ) : ?>
        <span <?php $this->print_render_attribute_string( 'badge_text' ); ?>>
          <?php echo esc_html( $badge_text ); ?>
        </span>
      <?php endif; ?>

      <?php if ( 'split' === $skin && $media_type ) : ?>
        <div class="tms-cta__panel tms-cta__panel--media">
          <?php $this->render_media( $settings ); ?>
        </div>
      <?php endif; ?>

      <div class="tms-cta__panel tms-cta__panel--body">
        <div class="tms-cta__body">

          <?php if ( 'split' !== $skin && $media_type ) : ?>
            <div class="tms-cta__media">
              <?php $this->render_media( $settings ); ?>
            </div>
          <?php endif; ?>

          <?php if ( $badge_text && 'inline' === $badge_position ) : ?>
            <span <?php $this->print_render_attribute_string( 'badge_text' ); ?>>
              <?php echo esc_html( $badge_text ); ?>
            </span>
          <?php endif; ?>

          <?php if ( $pre_title ) : ?>
            <span <?php $this->print_render_attribute_string( 'pre_title' ); ?>>
              <?php echo esc_html( $pre_title ); ?>
            </span>
          <?php endif; ?>

          <?php if ( $title ) : ?>
            <<?php echo esc_html( $title_tag ); ?> <?php $this->print_render_attribute_string( 'title' ); ?>>
              <?php echo wp_kses_post( $title ); ?>
            </<?php echo esc_html( $title_tag ); ?>>
          <?php endif; ?>

          <?php if ( $description ) : ?>
            <div <?php $this->print_render_attribute_string( 'description' ); ?>>
              <?php echo wp_kses_post( $description ); ?>
            </div>
          <?php endif; ?>

          <?php
          $has_primary_label = !empty( $settings['primary_button_label'] );
          $has_secondary_label = $show_secondary && !empty( $settings['secondary_button_label'] );
          ?>
          <?php if ( $has_primary_label || $has_secondary_label ) : ?>
            <div class="tms-cta__actions">
              <?php $this->render_button( 'primary', $settings ); ?>
              <?php if ( $show_secondary ) : ?>
                <?php $this->render_button( 'secondary', $settings ); ?>
              <?php endif; ?>
            </div>
          <?php endif; ?>

        </div><!-- /.tms-cta__body -->
      </div><!-- /.tms-cta__panel--body -->

    </div><!-- /.tms-cta -->
    <?php
  }

  /**
   * Render the media (icon or image) block according to the current settings.
   *
   * @param array $settings Widget settings.
   * @return void
   */
  private function render_media( array $settings ): void {
    $media_type = $settings['media_type'] ?? '';

    if ( 'icon' === $media_type ) {
      $icon_html = themeasy_render_icon_html(
        $settings['icon'] ?? [],
        ['class' => 'tms-cta__icon', 'aria-hidden' => 'true']
      );

      if ( $icon_html ) {
        echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
      }

      return;
    }

    if ( 'image' === $media_type && !empty( $settings['image']['url'] ) ) {
      $image = $settings['image'];
      $image_id = absint( $image['id'] ?? 0 );
      $image_url = $image['url'];
      $image_alt = Control_Media::get_image_alt( $image );
      $image_size = $settings['image_resolution_size'] ?? 'large';
      $image_priority = $settings['image_loading_priority'] ?? 'lazy';

      themeasy_render_attachment_image(
        $image_id,
        $image_size,
        $image_url,
        $image_alt,
        'tms-cta__image',
        [],
        $image_priority
      );
    }
  }

  /**
   * Render a single action button.
   *
   * @param string $prefix   Button prefix (primary|secondary).
   * @param array  $settings Widget settings.
   * @return void
   */
  private function render_button( string $prefix, array $settings ): void {
    $label = $settings["{$prefix}_button_label"] ?? '';
    $style = $settings["{$prefix}_button_style"] ?? 'solid-fill';
    $icon_align = $settings["{$prefix}_button_icon_alignment"] ?? 'right';
    $link = $settings["{$prefix}_button_link"] ?? [];
    $has_link = !empty( $link['url'] );

    if ( '' === $label ) {
      return;
    }

    $classes = ['tms-button', 'tms-cta__button', 'tms-cta__button--' . $prefix, 'tms-button--' . $style];

    if ( 'left' === $icon_align ) {
      $classes[] = 'tms-button--icon-left';
    }

    $link_key = "{$prefix}_button_link";
    $label_key = "{$prefix}_button_label";

    if ( $has_link ) {
      $this->add_link_attributes( $link_key, $link );
      themeasy_add_external_link_rel( $this, $link_key, $link );
    } else {
      $this->add_render_attribute( $link_key, 'href', '#' );
      $this->add_render_attribute( $link_key, 'role', 'button' );
    }

    $this->add_render_attribute( $link_key, 'class', implode( ' ', array_filter( $classes ) ) );

    $this->add_render_attribute( $label_key, 'class', 'tms-button__text' );
    $this->add_inline_editing_attributes( $label_key, 'none' );

    $icon_html = themeasy_render_icon_html(
      $settings["{$prefix}_button_icon"] ?? [],
      ['class' => 'tms-button__icon', 'aria-hidden' => 'true']
    );
    ?>
    <a <?php $this->print_render_attribute_string( $link_key ); ?>>
      <span <?php $this->print_render_attribute_string( $label_key ); ?>>
        <?php echo wp_kses( $label, themeasy_get_kses_allowed_tags() ); ?>
      </span>
      <?php if ( $icon_html ) : ?>
        <?php echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>
      <?php endif; ?>
    </a>
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
      // Kses-parity sanitizer for user text in raw preview output. Falls back
      // to plain escaping when the shared helper is unavailable (safe — only
      // inline formatting is lost).
      var sanitizeInline = ( window.Themeasy && window.Themeasy.sanitizeInlineHtml )
        ? window.Themeasy.sanitizeInlineHtml
        : _.escape;

      var skin           = settings.skin || 'banner';
      var splitSide      = settings.split_side || 'left';
      var badgeText      = settings.badge_text || '';
      var badgePosition  = settings.badge_position || 'top-right';
      var mediaType      = settings.media_type || '';
      var preTitle       = settings.pre_title || '';
      var title          = sanitizeInline( settings.title || '' );
      var titleTag       = settings.title_tag || 'h2';
      var allowedTags    = [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div' ];
      if ( allowedTags.indexOf( titleTag ) === -1 ) { titleTag = 'h2'; }
      var description    = sanitizeInline( settings.description || '', 'rich' );
      var showSecondary  = settings.show_secondary_button === 'yes';
      var bgImageObj     = settings.background_image || {};

      // Scheme guard for every href/src below. The regex lives in exactly one
      // place; the fallback fails closed rather than duplicating it (a bare ^
      // anchor misses the C0 controls browsers strip before resolving a scheme).
      var safeUrl = ( window.Themeasy && window.Themeasy.safeUrl )
        ? window.Themeasy.safeUrl
        : function () { return ''; };

      var bgImageUrl     = safeUrl( bgImageObj.url || '' );

      // ------------------------------------------------------------------------
      // Wrapper classes.
      // ------------------------------------------------------------------------
      var wrapperClasses = [ 'tms-cta', 'tms-cta--skin-' + skin ];

      if ( skin === 'split' ) {
        wrapperClasses.push( 'tms-cta--media-' + splitSide );
      }

      // Mirrors render(): the light-on-dark text is gated on a real image.
      if ( skin === 'cover' && bgImageUrl ) {
        wrapperClasses.push( 'tms-cta--has-cover-image' );
      }

      if ( badgeText ) {
        wrapperClasses.push( 'tms-cta--has-ribbon', 'tms-cta--ribbon-' + badgePosition );
      }

      var wrapperClassStr = wrapperClasses.filter( Boolean ).join( ' ' );

      // ------------------------------------------------------------------------
      // Render attributes.
      // ------------------------------------------------------------------------
      if ( badgeText ) {
        view.addRenderAttribute( 'badge_text', 'class', 'tms-cta__ribbon' );
        view.addInlineEditingAttributes( 'badge_text', 'none' );
      }

      if ( preTitle ) {
        view.addRenderAttribute( 'pre_title', 'class', 'tms-cta__pre-title' );
        view.addInlineEditingAttributes( 'pre_title', 'none' );
      }

      if ( title ) {
        view.addRenderAttribute( 'title', 'class', 'tms-cta__title' );
        view.addInlineEditingAttributes( 'title', 'basic' );
      }

      if ( description ) {
        view.addRenderAttribute( 'description', 'class', 'tms-cta__description' );
        view.addInlineEditingAttributes( 'description', 'advanced' );
      }

      // ------------------------------------------------------------------------
      // Media renderer.
      // ------------------------------------------------------------------------
      var renderMedia = function() {
        if ( mediaType === 'icon' ) {
          var iconMarkup = ( window.Themeasy && window.Themeasy.renderIconMarkup )
            ? window.Themeasy.renderIconMarkup(
                view, settings.icon, null,
                { 'class': 'tms-cta__icon', 'aria-hidden': 'true' }
              )
            : '';
          return iconMarkup;
        }

        if ( mediaType === 'image' && settings.image && settings.image.url ) {
          var imageUrl = safeUrl( settings.image.url );

          // A blocked scheme yields '' — skip the <img> entirely.
          if ( ! imageUrl ) { return ''; }

          var imageAlt = settings.image.alt || '';
          return '<img src="' + _.escape( imageUrl ) + '" alt="' + _.escape( imageAlt ) + '" class="tms-cta__image" />';
        }

        return '';
      };

      // ------------------------------------------------------------------------
      // Button renderer.
      // ------------------------------------------------------------------------
      var renderButton = function( prefix ) {
        var label     = sanitizeInline( settings[ prefix + '_button_label' ] || '' );
        if ( ! label ) { return ''; }

        var style     = settings[ prefix + '_button_style' ] || ( prefix === 'primary' ? 'solid-fill' : 'outline' );
        var iconAlign = settings[ prefix + '_button_icon_alignment' ] || 'right';
        var link      = settings[ prefix + '_button_link' ] || {};
        var href      = safeUrl( link.url || '' );

        var classes = [ 'tms-button', 'tms-cta__button', 'tms-cta__button--' + prefix, 'tms-button--' + style ];
        if ( iconAlign === 'left' ) { classes.push( 'tms-button--icon-left' ); }
        var classStr = classes.join( ' ' );

        var labelKey = prefix + '_button_label';
        view.addRenderAttribute( labelKey, 'class', 'tms-button__text' );
        view.addInlineEditingAttributes( labelKey, 'none' );

        var iconMarkup = ( window.Themeasy && window.Themeasy.renderIconMarkup )
          ? window.Themeasy.renderIconMarkup(
              view, settings[ prefix + '_button_icon' ], null,
              { 'class': 'tms-button__icon', 'aria-hidden': 'true' }
            )
          : '';

        // A blocked scheme yields '' — mirror render()'s no-link shape
        // (href="#" + role="button") instead of emitting a live hostile href.
        var html = href
          ? '<a href="' + _.escape( href ) + '" class="' + _.escape( classStr ) + '">'
          : '<a href="#" role="button" class="' + _.escape( classStr ) + '">';
        html += '<span ' + view.getRenderAttributeString( labelKey ) + '>' + label + '</span>';
        if ( iconMarkup ) { html += iconMarkup; }
        html += '</a>';

        return html;
      };

      var primaryButtonHtml   = renderButton( 'primary' );
      var secondaryButtonHtml = showSecondary ? renderButton( 'secondary' ) : '';
      var hasButtons          = !! ( primaryButtonHtml || secondaryButtonHtml );

      var bgImageAltPreview = ( bgImageObj && bgImageObj.alt ) ? bgImageObj.alt : '';
    #>

    <div class="{{ wrapperClassStr }}">

      <# if ( skin === 'cover' && bgImageUrl ) { #>
        <div class="tms-cta__background" aria-hidden="true">
          <img src="{{ bgImageUrl }}" alt="{{ bgImageAltPreview }}" class="tms-cta__background-image" />
          <span class="tms-cta__overlay"></span>
        </div>
      <# } #>

      <# if ( badgeText && badgePosition !== 'inline' ) { #>
        <span {{{ view.getRenderAttributeString( 'badge_text' ) }}}>{{ badgeText }}</span>
      <# } #>

      <# if ( skin === 'split' && mediaType ) { #>
        <div class="tms-cta__panel tms-cta__panel--media">
          {{{ renderMedia() }}}
        </div>
      <# } #>

      <div class="tms-cta__panel tms-cta__panel--body">
        <div class="tms-cta__body">

          <# if ( skin !== 'split' && mediaType ) { #>
            <div class="tms-cta__media">{{{ renderMedia() }}}</div>
          <# } #>

          <# if ( badgeText && badgePosition === 'inline' ) { #>
            <span {{{ view.getRenderAttributeString( 'badge_text' ) }}}>{{ badgeText }}</span>
          <# } #>

          <# if ( preTitle ) { #>
            <span {{{ view.getRenderAttributeString( 'pre_title' ) }}}>{{ preTitle }}</span>
          <# } #>

          <# if ( title ) { #>
            <{{ titleTag }} {{{ view.getRenderAttributeString( 'title' ) }}}>{{{ title }}}</{{ titleTag }}>
          <# } #>

          <# if ( description ) { #>
            <div {{{ view.getRenderAttributeString( 'description' ) }}}>{{{ description }}}</div>
          <# } #>

          <# if ( hasButtons ) { #>
            <div class="tms-cta__actions">
              <# if ( primaryButtonHtml ) { #>{{{ primaryButtonHtml }}}<# } #>
              <# if ( secondaryButtonHtml ) { #>{{{ secondaryButtonHtml }}}<# } #>
            </div>
          <# } #>

        </div>
      </div>

    </div><!-- /.tms-cta -->
    <?php
  }
}
