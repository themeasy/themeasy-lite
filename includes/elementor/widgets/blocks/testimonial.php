<?php
/**
 * Themeasy Elementor Widget: Testimonial
 *
 * Displays a testimonial card with person image, name, occupation info, an
 * optional company logo, testimonial text, star rating, and optional quote
 * icons. Supports horizontal, vertical and split (large portrait beside a
 * featured quote) layouts plus entrance and hover motion.
 *
 * @package Themeasy
 * @since 1.0.0
 */

namespace Themeasy\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Typography;
use Elementor\Utils;
use Elementor\Widget_Base;
use Themeasy\Core\Entitlement;

/**
 * Responsible for registering controls and rendering the widget.
 */
class Testimonial extends Widget_Base {
  public function get_name() {
    return 'themeasy-testimonial';
  }

  public function get_title() {
    return esc_html__( 'Testimonial', 'themeasy-lite' );
  }

  public function get_icon() {
    return 'eicon-testimonial';
  }

  public function get_custom_help_url() {
    return 'https://themeasy.co/help-center';
  }

  public function get_categories() {
    return ['themeasy_blocks_category', 'themeasy_content_category'];
  }

  public function get_keywords() {
    return ['themeasy', 'testimonial', 'review', 'quote', 'rating', 'feedback', 'star'];
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
    // Content section: Testimonial
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'testimonial_section',
        [
          'label' => esc_html__( 'Testimonial', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
    );

      $this->add_control(
        'person_image',
        [
          'label' => esc_html__( 'Image', 'themeasy-lite' ),
          'type' => Controls_Manager::MEDIA,
          'default' => [
            'url' => Utils::get_placeholder_image_src(),
          ],
        ]
      );

      $this->add_control(
        'person_name',
        [
          'label' => esc_html__( 'Name', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'Person Name', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'Leave blank to hide', 'themeasy-lite' ),
          'dynamic' => [
            'active' => true,
          ],
        ]
      );

      $this->add_control(
        'occupation_info',
        [
          'label' => esc_html__( 'Occupation Info', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'Person Occupation Info', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'Leave blank to hide', 'themeasy-lite' ),
          'dynamic' => [
            'active' => true,
          ],
        ]
      );

      $this->add_control(
        'company_logo',
        [
          'label' => esc_html__( 'Company Logo', 'themeasy-lite' ),
          'description' => esc_html__( 'Optional brand mark shown above the name.', 'themeasy-lite' ),
          'type' => Controls_Manager::MEDIA,
        ]
      );

      $this->add_control(
        'testimonial_text',
        [
          'label' => esc_html__( 'Testimonial Text', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXTAREA,
          'default' => esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean lobortis magna et ipsum rutrum lobortis.', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'Leave blank to hide', 'themeasy-lite' ),
          'dynamic' => [
            'active' => true,
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
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Layout
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'testimonial_layout_section',
        [
          'label' => esc_html__( 'Layout', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
    );

      $this->add_control(
        'block_layout',
        [
          'label' => esc_html__( 'Block Layout', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'vertical',
          'options' => [
            'horizontal' => esc_html__( 'Horizontal', 'themeasy-lite' ),
            'vertical' => esc_html__( 'Vertical', 'themeasy-lite' ),
            'split' => esc_html__( 'Split', 'themeasy-lite' ),
          ],
        ]
      );

      $this->add_control(
        'split_image_position',
        [
          'label' => esc_html__( 'Image Position', 'themeasy-lite' ),
          'description' => esc_html__( 'Narrow cards stack the image on top regardless of this setting.', 'themeasy-lite' ),
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
            'block_layout' => 'split',
          ],
        ]
      );

      $this->add_control(
        'show_divider',
        [
          'label' => esc_html__( 'Show Divider', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Show', 'themeasy-lite' ),
          'label_off' => esc_html__( 'Hide', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
          'condition' => [
            'block_layout' => 'split',
          ],
        ]
      );

      $this->add_responsive_control(
        'content_alignment',
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
          'default' => 'center',
          'condition' => [
            'block_layout' => 'vertical',
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-profile-card' => '--tms-profile-card-align: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'show_quote_icon',
        [
          'label' => esc_html__( 'Show Quote Icon', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Show', 'themeasy-lite' ),
          'label_off' => esc_html__( 'Hide', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
        ]
      );

      $this->add_control(
        'show_star_rating',
        [
          'label' => esc_html__( 'Show Star Rating', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Show', 'themeasy-lite' ),
          'label_off' => esc_html__( 'Hide', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
        ]
      );

      $this->add_control(
        'icons_note_content',
        [
          'show_label' => false,
          'type' => Controls_Manager::RAW_HTML,
          // The Agency White Label renames the icon library (backlog #272).
          'raw' => esc_html(
            sprintf(
              /* translators: %s: the brand name (Themeasy, or the Agency's White Label name). */
              __( 'You can use custom icons for the quote and star rating. If you do not select any icon, the default icons will be used. Quote glyphs live only in the %s — Feather library; the Solar libraries have none.', 'themeasy-lite' ),
              (string) apply_filters( 'themeasy/brand/name', 'Themeasy' )
            )
          ),
          'content_classes' => 'elementor-control-field-description no-margin',
        ]
      );

      $this->start_controls_tabs( 'icons_tabs' );

        $this->start_controls_tab(
          'icons_quote_tab',
          [
            'label' => esc_html__( 'Quote Icons', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'open_quote_icon',
            [
              'label' => esc_html__( 'Open Quote Icon', 'themeasy-lite' ),
              'type' => Controls_Manager::ICONS,
              'condition' => [
                'block_layout' => 'vertical',
                'show_quote_icon' => 'yes',
              ],
            ]
          );

          $this->add_control(
            'closed_quote_icon',
            [
              'label' => esc_html__( 'Closed Quote Icon', 'themeasy-lite' ),
              'type' => Controls_Manager::ICONS,
              'condition' => [
                'show_quote_icon' => 'yes',
              ],
            ]
          );

        $this->end_controls_tab();

        $this->start_controls_tab(
          'icons_star_rating_tab',
          [
            'label' => esc_html__( 'Star Rating Icons', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'star_rating_icon',
            [
              'label' => esc_html__( 'Star Rating Icon', 'themeasy-lite' ),
              'type' => Controls_Manager::ICONS,
              'condition' => [
                'show_star_rating' => 'yes',
              ],
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Block
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'testimonial_block_styles_section',
        [
          'label' => esc_html__( 'Block', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
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
        'block_width',
        [
          'label' => esc_html__( 'Width', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', '%', 'em', 'rem', 'vw', 'custom'],
          'range' => [
            '%' => [
              'min' => 1,
              'max' => 100,
            ],
            'px' => [
              'min' => 1,
              'max' => 1000,
            ],
            'vw' => [
              'min' => 1,
              'max' => 100,
            ],
          ],
          'default' => [
            'unit' => '%',
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-card' => 'width: {{SIZE}}{{UNIT}}; max-width: {{SIZE}}{{UNIT}};',
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
      'testimonial_image_styles_section',
        [
          'label' => esc_html__( 'Image', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      $this->add_responsive_control(
        'image_width',
        [
          'label' => esc_html__( 'Width', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', '%', 'em', 'rem', 'vw', 'custom'],
          'range' => [
            '%' => [
              'min' => 1,
              'max' => 100,
            ],
            'px' => [
              'min' => 1,
              'max' => 1000,
            ],
            'vw' => [
              'min' => 1,
              'max' => 100,
            ],
          ],
          'default' => [
            'unit' => '%',
          ],
          'condition' => [
            'block_layout!' => 'split',
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-image' => 'width: 100%; max-width: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'split_image_width',
        [
          'label' => esc_html__( 'Width', 'themeasy-lite' ),
          'description' => esc_html__( 'Share of the card taken by the image column.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['%'],
          'range' => [
            '%' => [
              'min' => 20,
              'max' => 75,
            ],
          ],
          'default' => [
            'unit' => '%',
          ],
          'condition' => [
            'block_layout' => 'split',
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-profile-card' => '--tms-profile-card-media-width: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'image_height',
        [
          'label' => esc_html__( 'Height', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', '%', 'em', 'rem', 'vw', 'custom'],
          'range' => [
            '%' => [
              'min' => 1,
              'max' => 100,
            ],
            'px' => [
              'min' => 1,
              'max' => 1000,
            ],
            'vw' => [
              'min' => 1,
              'max' => 100,
            ],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-image' => 'height: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'image_object_fit',
        [
          'label' => esc_html__( 'Object Fit', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => '',
          'options' => [
            '' => esc_html__( 'None', 'themeasy-lite' ),
            'contain' => esc_html__( 'Contain', 'themeasy-lite' ),
            'cover' => esc_html__( 'Cover', 'themeasy-lite' ),
            'fill' => esc_html__( 'Fill', 'themeasy-lite' ),
            'revert' => esc_html__( 'Revert', 'themeasy-lite' ),
            'scale-down' => esc_html__( 'Scale Down', 'themeasy-lite' ),
            'unset' => esc_html__( 'Unset', 'themeasy-lite' ),
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-image .tms-image__img' => 'object-fit: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'image_object_position',
        [
          'label' => esc_html__( 'Object Position', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => '',
          'options' => [
            '' => esc_html__( 'None', 'themeasy-lite' ),
            'center' => esc_html__( 'Center', 'themeasy-lite' ),
            'top' => esc_html__( 'Top', 'themeasy-lite' ),
            'right' => esc_html__( 'Right', 'themeasy-lite' ),
            'bottom' => esc_html__( 'Bottom', 'themeasy-lite' ),
            'left' => esc_html__( 'Left', 'themeasy-lite' ),
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-image .tms-image__img' => 'object-position: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Css_Filter::get_type(),
        [
          'name' => 'image_css_filters',
          'selector' => '{{WRAPPER}} .tms-image .tms-image__img',
        ]
      );

      $this->add_group_control(
        Group_Control_Border::get_type(),
        [
          'name' => 'image_border',
          'selector' => '{{WRAPPER}} .tms-image .tms-image__img',
        ]
      );

      $this->add_control(
        'image_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-image .tms-image__img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Box_Shadow::get_type(),
        [
          'name' => 'image_box_shadow',
          'label' => esc_html__( 'Box Shadow', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-image .tms-image__img',
        ]
      );

      // Company Logo.
      $this->add_control(
        'logo_heading',
        [
          'label' => esc_html__( 'Company Logo', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_responsive_control(
        'logo_height',
        [
          'label' => esc_html__( 'Height', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem', 'em'],
          'range' => [
            'px' => ['min' => 8, 'max' => 120],
            'rem' => ['min' => 0.5, 'max' => 8, 'step' => 0.125],
            'em' => ['min' => 0.5, 'max' => 8, 'step' => 0.125],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-profile-card .tms-profile-card__logo-img' => 'height: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Css_Filter::get_type(),
        [
          'name' => 'logo_css_filters',
          'selector' => '{{WRAPPER}} .tms-profile-card .tms-profile-card__logo-img',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Text
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'testimonial_text_styles_section',
        [
          'label' => esc_html__( 'Text', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      // Person Name.
      $this->add_control(
        'person_name_heading',
        [
          'label' => esc_html__( 'Person Name', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
        ]
      );

      $this->add_control(
        'person_name_text_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-card .tms-profile-card__name' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'person_name_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-card .tms-profile-card__name',
        ]
      );

      // Occupation Info.
      $this->add_control(
        'occupation_info_heading',
        [
          'label' => esc_html__( 'Occupation Info', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'occupation_info_text_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-card .tms-profile-card__info' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Background::get_type(),
        [
          'name' => 'occupation_info_background',
          'label' => esc_html__( 'Background', 'themeasy-lite' ),
          'types' => ['classic', 'gradient'],
          'selector' => '{{WRAPPER}} .tms-card .tms-profile-card__info',
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'occupation_info_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-card .tms-profile-card__info',
        ]
      );

      $this->add_responsive_control(
        'occupation_info_padding',
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', 'em'],
          'selectors' => [
            '{{WRAPPER}} .tms-card .tms-profile-card__info'
              => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
          'description' => esc_html__(
            'Space around the text — needed for a background or border to read as a badge.',
            'themeasy-lite'
          ),
        ]
      );

      $this->add_control(
        'occupation_info_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', '%'],
          'selectors' => [
            '{{WRAPPER}} .tms-card .tms-profile-card__info'
              => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Border::get_type(),
        [
          'name' => 'occupation_info_border',
          'selector' => '{{WRAPPER}} .tms-card .tms-profile-card__info',
        ]
      );

      // Testimonial Text.
      $this->add_control(
        'testimonial_text_heading',
        [
          'label' => esc_html__( 'Testimonial Text', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'testimonial_text_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-card .tms-profile-card__testimonial' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'testimonial_text_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-card .tms-profile-card__testimonial',
        ]
      );

      $this->add_responsive_control(
        'testimonial_text_line_clamp',
        [
          'label' => esc_html__( 'Line Clamp', 'themeasy-lite' ),
          'description' => esc_html__( 'Maximum number of lines before the text is truncated. 0 = no clamp (the phone default).', 'themeasy-lite' ),
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
            'size' => 4,
          ],
          'mobile_default' => [
            'unit' => 'u',
            'size' => 0,
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-card .tms-profile-card__testimonial' => '--tms-line-clamp: {{SIZE}}; display: -webkit-box; -webkit-box-orient: vertical; overflow: hidden; -webkit-line-clamp: var(--tms-line-clamp);',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Icons
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'testimonial_icon_styles_section',
        [
          'label' => esc_html__( 'Icons', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      // Quote Icons.
      $this->add_control(
        'quote_icon_heading',
        [
          'label' => esc_html__( 'Quote Icons', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'condition' => [
            'show_quote_icon' => 'yes',
          ],
        ]
      );

      $this->add_control(
        'quote_icon_color',
        [
          'label' => esc_html__( 'Icon Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-card .tms-profile-card__overlay-icon .icon,
             {{WRAPPER}} .tms-card .tms-profile-card__overlay-icon svg' => 'color: {{VALUE}}; opacity: 1;',
          ],
          'condition' => [
            'show_quote_icon' => 'yes',
          ],
        ]
      );

      $this->add_control(
        'quote_icon_fill_color',
        [
          'label' => esc_html__( 'Icon Fill Color', 'themeasy-lite' ),
          'description' => esc_html__( 'Applies to SVG icons only.', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-card .tms-profile-card__overlay-icon svg' => 'fill: {{VALUE}};',
          ],
          'condition' => [
            'show_quote_icon' => 'yes',
          ],
        ]
      );

      $this->add_control(
        'quote_icon_stroke_color',
        [
          'label' => esc_html__( 'Icon Stroke Color', 'themeasy-lite' ),
          'description' => esc_html__( 'Applies to SVG icons only.', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-card .tms-profile-card__overlay-icon svg' => 'stroke: {{VALUE}};',
          ],
          'condition' => [
            'show_quote_icon' => 'yes',
          ],
        ]
      );

      $this->add_responsive_control(
        'quote_icon_size',
        [
          'label' => esc_html__( 'Icon Size', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'em', 'rem'],
          'range' => [
            'px' => ['min' => 1, 'max' => 100],
            'em' => ['min' => 0.1, 'max' => 10, 'step' => 0.1],
            'rem' => ['min' => 0.1, 'max' => 10, 'step' => 0.1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-card .tms-profile-card__overlay-icon .icon' => 'font-size: {{SIZE}}{{UNIT}};',
            '{{WRAPPER}} .tms-card .tms-profile-card__overlay-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
            // Split reserves room for the mark beside the author group.
            '{{WRAPPER}} .tms-profile-card' => '--tms-profile-card-quote-size: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'show_quote_icon' => 'yes',
          ],
        ]
      );

      $this->add_responsive_control(
        'quote_icon_thickness',
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
            '{{WRAPPER}} .tms-card .tms-profile-card__overlay-icon svg,
             {{WRAPPER}} .tms-card .tms-profile-card__overlay-icon svg *' => 'stroke-width: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'show_quote_icon' => 'yes',
          ],
        ]
      );

      // Star Rating Icons.
      $this->add_control(
        'star_rating_icon_heading',
        [
          'label' => esc_html__( 'Star Rating Icons', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
          'condition' => [
            'show_star_rating' => 'yes',
          ],
        ]
      );

      $this->add_control(
        'star_rating_icon_color',
        [
          'label' => esc_html__( 'Icon Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-card .tms-horizontal-icons .tms-horizontal-icons__icon,
             {{WRAPPER}} .tms-card .tms-horizontal-icons svg' => 'color: {{VALUE}};',
          ],
          'condition' => [
            'show_star_rating' => 'yes',
          ],
        ]
      );

      $this->add_control(
        'star_rating_icon_fill_color',
        [
          'label' => esc_html__( 'Icon Fill Color', 'themeasy-lite' ),
          'description' => esc_html__( 'Applies to SVG icons only.', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-card .tms-horizontal-icons svg' => 'fill: {{VALUE}};',
          ],
          'condition' => [
            'show_star_rating' => 'yes',
          ],
        ]
      );

      $this->add_control(
        'star_rating_icon_stroke_color',
        [
          'label' => esc_html__( 'Icon Stroke Color', 'themeasy-lite' ),
          'description' => esc_html__( 'Applies to SVG icons only.', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-card .tms-horizontal-icons svg' => 'stroke: {{VALUE}};',
          ],
          'condition' => [
            'show_star_rating' => 'yes',
          ],
        ]
      );

      $this->add_responsive_control(
        'star_rating_icon_size',
        [
          'label' => esc_html__( 'Icon Size', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'em', 'rem'],
          'range' => [
            'px' => ['min' => 1, 'max' => 100],
            'em' => ['min' => 0.1, 'max' => 10, 'step' => 0.1],
            'rem' => ['min' => 0.1, 'max' => 10, 'step' => 0.1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-card .tms-horizontal-icons .tms-horizontal-icons__icon' => 'font-size: {{SIZE}}{{UNIT}};',
            '{{WRAPPER}} .tms-card .tms-horizontal-icons svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'show_star_rating' => 'yes',
          ],
        ]
      );

      $this->add_responsive_control(
        'star_rating_icon_thickness',
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
            '{{WRAPPER}} .tms-card .tms-horizontal-icons svg,
             {{WRAPPER}} .tms-card .tms-horizontal-icons svg *' => 'stroke-width: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'show_star_rating' => 'yes',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Divider
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'testimonial_divider_styles_section',
        [
          'label' => esc_html__( 'Divider', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'block_layout' => 'split',
            'show_divider' => 'yes',
          ],
        ]
    );

      $this->add_control(
        'divider_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-profile-card .tms-profile-card__divider' => 'border-top-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'divider_style',
        [
          'label' => esc_html__( 'Style', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'dashed',
          'options' => [
            'solid' => esc_html__( 'Solid', 'themeasy-lite' ),
            'dashed' => esc_html__( 'Dashed', 'themeasy-lite' ),
            'dotted' => esc_html__( 'Dotted', 'themeasy-lite' ),
            'double' => esc_html__( 'Double', 'themeasy-lite' ),
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-profile-card .tms-profile-card__divider' => 'border-top-style: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'divider_weight',
        [
          'label' => esc_html__( 'Weight', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => ['min' => 1, 'max' => 10],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-profile-card .tms-profile-card__divider' => 'border-top-width: {{SIZE}}{{UNIT}};',
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

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Advanced section: Hover Interactions
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'widget_hover_section',
        [
          'label' => esc_html__( 'Motion — Hover Interactions', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_ADVANCED,
        ]
    );

      $this->add_control(
        'widget_hover_animation',
        [
          'label' => esc_html__( 'Hover Animation', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => '',
          'options' => themeasy_get_animation_options( 'hover' ),
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
    $block_layout = in_array( $settings['block_layout'] ?? '', ['horizontal', 'vertical', 'split'], true )
      ? $settings['block_layout']
      : 'vertical';
    $is_split = 'split' === $block_layout;
    $show_quote_icon = 'yes' === ( $settings['show_quote_icon'] ?? '' );
    $show_star_rating = 'yes' === ( $settings['show_star_rating'] ?? '' );
    $show_divider = $is_split && 'yes' === ( $settings['show_divider'] ?? '' );
    $person_name = $settings['person_name'] ?? '';
    $occupation_info = $settings['occupation_info'] ?? '';
    $testimonial_text = $settings['testimonial_text'] ?? '';

    // The control default is Elementor's placeholder.png, so an untouched slot is
    // indistinguishable from a chosen one by value alone. The default stays a
    // legitimate drop target in the editor (content_template() still shows it); on
    // the front it is a grey box posing as a person, so it renders no portrait at
    // all — same treatment as testimonial-carousel and testimonial-wall.
    $person_image = is_array( $settings['person_image'] ?? null ) ? $settings['person_image'] : [];
    $person_image_id = absint( $person_image['id'] ?? 0 );
    $person_image_url = $person_image['url'] ?? '';

    if ( $person_image_url === Utils::get_placeholder_image_src() ) {
      $person_image_url = '';
      $person_image_id = 0;
    }

    // Elementor's MEDIA control does not persist alt; resolve it from the attachment.
    $person_image_alt = $person_image_id
      ? (string) get_post_meta( $person_image_id, '_wp_attachment_image_alt', true )
      : '';

    // Company logo: optional, no placeholder default — empty means no logo.
    $company_logo = is_array( $settings['company_logo'] ?? null ) ? $settings['company_logo'] : [];
    $company_logo_id = absint( $company_logo['id'] ?? 0 );
    $company_logo_url = $company_logo['url'] ?? '';
    $company_logo_alt = $company_logo_id
      ? (string) get_post_meta( $company_logo_id, '_wp_attachment_image_alt', true )
      : '';

    $star_rating = isset( $settings['star_rating']['size'] ) ? (int) $settings['star_rating']['size'] : 0;

    // Icons.
    $closed_quote_icon = !empty( $settings['closed_quote_icon']['value'] )
      ? themeasy_render_icon_html( $settings['closed_quote_icon'], ['class' => 'icon', 'aria-hidden' => 'true'] )
      : themeasy_get_svg_icon( 'ty-feather', 'quote-close', 'icon' );

    $open_quote_icon = !empty( $settings['open_quote_icon']['value'] )
      ? themeasy_render_icon_html( $settings['open_quote_icon'], ['class' => 'icon', 'aria-hidden' => 'true'] )
      : themeasy_get_svg_icon( 'ty-feather', 'quote-open', 'icon' );

    $star_rating_icon = !empty( $settings['star_rating_icon']['value'] )
      ? themeasy_render_icon_html( $settings['star_rating_icon'], ['class' => 'tms-horizontal-icons__icon', 'aria-hidden' => 'true'] )
      : themeasy_get_svg_icon( 'system-ui', 'star-filled', 'icon' );

    // ------------------------------------------------------------------------
    // Wrapper classes + attributes.
    // ------------------------------------------------------------------------
    $wrapper_classes = ['tms-testimonial'];
    $wrapper_atts = [];

    // Editor preview: keep motion static so it never fights the canvas.
    if ( themeasy_is_elementor_editor() ) {
      $wrapper_classes[] = 'tms-testimonial--static';
    }

    // Entrance animation.
    $block_animation = $motion ? ( $settings['widget_animation'] ?? '' ) : '';

    if ( $block_animation ) {
      $wrapper_classes[] = 'tms-block-animation';
      $wrapper_classes[] = 'tms-animation--on-view';
      $wrapper_classes[] = 'tms-animation--hidden';

      $wrapper_atts['tms-block-animation'] = $block_animation;
      $wrapper_atts['duration'] = $settings['widget_animation_duration']['size'] ?? '1';
      $wrapper_atts['delay'] = $settings['widget_animation_delay']['size'] ?? '0';
    }

    // Hover animation.
    $hover_animation = $motion ? ( $settings['widget_hover_animation'] ?? '' ) : '';

    if ( $hover_animation ) {
      $wrapper_classes[] = 'tms-hover-animation';
      $wrapper_atts['tms-hover-animation'] = $hover_animation;
    }

    $profile_card_classes = ['tms-profile-card', 'tms-card', 'tms-animation__target'];

    if ( $block_layout ) {
      $profile_card_classes[] = 'tms-profile-card--' . $block_layout;
    }

    if ( $is_split && 'right' === ( $settings['split_image_position'] ?? '' ) ) {
      $profile_card_classes[] = 'tms-profile-card--image-right';
    }

    $wrapper_classes_output = implode( ' ', array_filter( $wrapper_classes ) );
    $wrapper_atts_output = themeasy_html_attributes( $wrapper_atts );
    $profile_card_classes_output = implode( ' ', array_filter( $profile_card_classes ) );

    // ------------------------------------------------------------------------
    // Render attributes.
    // ------------------------------------------------------------------------
    $this->add_render_attribute( 'person_name', 'class', 'tms-profile-card__name' );
    $this->add_inline_editing_attributes( 'person_name', 'none' );

    $this->add_render_attribute( 'occupation_info', 'class', 'tms-profile-card__info' );
    $this->add_inline_editing_attributes( 'occupation_info', 'none' );

    $this->add_render_attribute( 'testimonial_text', 'class', ['tms-profile-card__testimonial', 'tms-card__description'] );
    $this->add_inline_editing_attributes( 'testimonial_text', 'advanced' );

    // ------------------------------------------------------------------------
    // Output.
    // ------------------------------------------------------------------------
    ?>
    <div class="<?php echo esc_attr( $wrapper_classes_output ); ?>"<?php echo $wrapper_atts_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>>
      <div class="<?php echo esc_attr( $profile_card_classes_output ); ?>">

        <?php if ( $show_quote_icon && ( 'horizontal' === $block_layout || $is_split ) ) : ?>
          <div class="tms-profile-card__overlay-icon">
            <div class="icon-wrapper">
              <?php echo $closed_quote_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>
            </div>
          </div>
        <?php endif; ?>

        <div class="tms-card__header">

          <?php if ( !empty( $person_image_url ) ) : ?>

            <?php if ( $show_quote_icon && 'vertical' === $block_layout ) : ?>
              <div class="icon-wrapper">
                <div class="tms-profile-card__overlay-icon">
                  <div class="icon-wrapper">
                    <?php echo $open_quote_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>
                  </div>
                </div>
            <?php endif; ?>

            <div class="tms-image">
              <?php themeasy_render_attachment_image( $person_image_id, 'full', $person_image_url, $person_image_alt, 'tms-image__img', [], 'lazy' ); ?>
            </div>

            <?php if ( $show_quote_icon && 'vertical' === $block_layout ) : ?>
                <div class="tms-profile-card__overlay-icon">
                  <div class="icon-wrapper">
                    <?php echo $closed_quote_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>
                  </div>
                </div>
              </div><!-- /.icon-wrapper -->
            <?php endif; ?>

          <?php endif; ?>

          <div class="tms-profile-card__content">

            <?php if ( $company_logo_url ) : ?>
              <div class="tms-profile-card__logo">
                <?php themeasy_render_attachment_image( $company_logo_id, 'full', $company_logo_url, $company_logo_alt, 'tms-profile-card__logo-img', [], 'lazy' ); ?>
              </div>
            <?php endif; ?>

            <?php if ( $person_name ) : ?>
              <h3 <?php $this->print_render_attribute_string( 'person_name' ); ?>>
                <?php echo esc_html( $person_name ); ?>
              </h3>
            <?php endif; ?>

            <?php // Caption, not a section heading — matching team-member's role. ?>
            <?php if ( $occupation_info ) : ?>
              <div <?php $this->print_render_attribute_string( 'occupation_info' ); ?>>
                <?php echo esc_html( $occupation_info ); ?>
              </div>
            <?php endif; ?>

          </div><!-- /.tms-profile-card__content -->
        </div><!-- /.tms-card__header -->

        <div class="tms-card__body">

          <?php if ( $testimonial_text ) : ?>
            <div <?php $this->print_render_attribute_string( 'testimonial_text' ); ?>>
              <?php echo wp_kses_post( $testimonial_text ); ?>
            </div>
          <?php endif; ?>

          <?php if ( $show_divider ) : ?>
            <div class="tms-profile-card__divider" aria-hidden="true"></div>
          <?php endif; ?>

          <?php if ( $show_star_rating ) : ?>
            <div class="tms-card__icons">
              <ul class="tms-horizontal-icons">
                <?php for ( $count = 1; $count <= 5; $count++ ) :
                  $item_class = ( $count > $star_rating )
                    ? 'tms-horizontal-icons__item tms-horizontal-icons--disabled'
                    : 'tms-horizontal-icons__item';
                  ?>
                  <li class="<?php echo esc_attr( $item_class ); ?>">
                    <?php echo $star_rating_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>
                  </li>
                <?php endfor; ?>
              </ul>
            </div><!-- /.tms-card__icons -->
          <?php endif; ?>

        </div><!-- /.tms-card__body -->
      </div><!-- /.tms-profile-card -->
    </div><!-- /.tms-testimonial -->
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
      var sanitizeInline = ( window.Themeasy && window.Themeasy.sanitizeInlineHtml )
        ? window.Themeasy.sanitizeInlineHtml
        : _.escape;

      var safeUrl = ( window.Themeasy && window.Themeasy.safeUrl )
        ? window.Themeasy.safeUrl
        : function () { return ''; };

      var blockLayout     = ( ['horizontal', 'vertical', 'split'].indexOf( settings.block_layout ) !== -1 )
        ? settings.block_layout
        : 'vertical';
      var isSplit         = blockLayout === 'split';
      var showQuoteIcon   = settings.show_quote_icon === 'yes';
      var showStarRating  = settings.show_star_rating === 'yes';
      var showDivider     = isSplit && settings.show_divider === 'yes';
      var personName      = settings.person_name || '';
      var occupationInfo  = settings.occupation_info || '';
      var testimonialText = sanitizeInline( settings.testimonial_text || '', 'rich' );
      var personImageUrl  = settings.person_image.url || '';
      var personImageAlt  = settings.person_image.alt || '';
      var companyLogoUrl  = safeUrl( ( settings.company_logo && settings.company_logo.url ) || '' );
      var companyLogoAlt  = ( settings.company_logo && settings.company_logo.alt ) || '';
      var starRating      = settings.star_rating.size ? parseInt( settings.star_rating.size ) : 0;

      // Icons.
      var closedQuoteIcon = ( settings.closed_quote_icon && settings.closed_quote_icon.value )
        ? Themeasy.renderIconMarkup( view, settings.closed_quote_icon, null, { 'class': 'icon', 'aria-hidden': 'true' } )
        : <?php echo wp_json_encode( themeasy_get_svg_icon( 'ty-feather', 'quote-close', 'icon' ) ); ?>;

      var openQuoteIcon = ( settings.open_quote_icon && settings.open_quote_icon.value )
        ? Themeasy.renderIconMarkup( view, settings.open_quote_icon, null, { 'class': 'icon', 'aria-hidden': 'true' } )
        : <?php echo wp_json_encode( themeasy_get_svg_icon( 'ty-feather', 'quote-open', 'icon' ) ); ?>;

      var starRatingIcon = ( settings.star_rating_icon && settings.star_rating_icon.value )
        ? Themeasy.renderIconMarkup( view, settings.star_rating_icon, null, { 'class': 'tms-horizontal-icons__icon', 'aria-hidden': 'true' } )
        : <?php echo wp_json_encode( themeasy_get_svg_icon( 'system-ui', 'star-filled', 'icon' ) ); ?>;

      // ------------------------------------------------------------------------
      // Wrapper classes + attributes.
      // ------------------------------------------------------------------------
      var wrapperClasses = [ 'tms-testimonial', 'tms-testimonial--static' ];
      var wrapperAtts    = {};

      // Entrance animation.
      var blockAnimation = motion ? ( settings.widget_animation || '' ) : '';

      if ( blockAnimation ) {
        wrapperClasses.push( 'tms-block-animation', 'tms-animation--on-view', 'tms-animation--hidden' );

        wrapperAtts['tms-block-animation'] = blockAnimation;
        wrapperAtts['duration'] = ( settings.widget_animation_duration && settings.widget_animation_duration.size )
          ? settings.widget_animation_duration.size
          : '1';
        wrapperAtts['delay'] = ( settings.widget_animation_delay && settings.widget_animation_delay.size )
          ? settings.widget_animation_delay.size
          : '0';
      }

      // Hover animation.
      var hoverAnimation = motion ? ( settings.widget_hover_animation || '' ) : '';

      if ( hoverAnimation ) {
        wrapperClasses.push( 'tms-hover-animation' );
        wrapperAtts['tms-hover-animation'] = hoverAnimation;
      }

      var profileCardClasses = [ 'tms-profile-card', 'tms-card', 'tms-animation__target' ];

      if ( blockLayout ) {
        profileCardClasses.push( 'tms-profile-card--' + blockLayout );
      }

      if ( isSplit && settings.split_image_position === 'right' ) {
        profileCardClasses.push( 'tms-profile-card--image-right' );
      }

      var wrapperClassStr     = wrapperClasses.filter( Boolean ).join( ' ' );
      var profileCardClassStr = profileCardClasses.filter( Boolean ).join( ' ' );

      // ------------------------------------------------------------------------
      // Render attributes.
      // ------------------------------------------------------------------------
      view.addRenderAttribute( 'person_name', 'class', 'tms-profile-card__name' );
      view.addInlineEditingAttributes( 'person_name', 'none' );

      view.addRenderAttribute( 'occupation_info', 'class', 'tms-profile-card__info' );
      view.addInlineEditingAttributes( 'occupation_info', 'none' );

      view.addRenderAttribute( 'testimonial_text', 'class', [ 'tms-profile-card__testimonial', 'tms-card__description' ] );
      view.addInlineEditingAttributes( 'testimonial_text', 'advanced' );
    #>

    <div class="{{ wrapperClassStr }}" {{{ Themeasy.htmlAttributes( wrapperAtts ) }}}>
      <div class="{{ profileCardClassStr }}">

        <# if ( showQuoteIcon && ( blockLayout === 'horizontal' || isSplit ) ) { #>
          <div class="tms-profile-card__overlay-icon">
            <div class="icon-wrapper">
              {{{ closedQuoteIcon }}}
            </div>
          </div>
        <# } #>

        <div class="tms-card__header">

          <# if ( personImageUrl ) { #>

            <# if ( showQuoteIcon && blockLayout === 'vertical' ) { #>
              <div class="icon-wrapper">
                <div class="tms-profile-card__overlay-icon">
                  <div class="icon-wrapper">
                    {{{ openQuoteIcon }}}
                  </div>
                </div>
            <# } #>

            <div class="tms-image">
              <img src="{{ personImageUrl }}" alt="{{ personImageAlt }}" class="tms-image__img" loading="lazy" />
            </div>

            <# if ( showQuoteIcon && blockLayout === 'vertical' ) { #>
                <div class="tms-profile-card__overlay-icon">
                  <div class="icon-wrapper">
                    {{{ closedQuoteIcon }}}
                  </div>
                </div>
              </div><!-- /.icon-wrapper -->
            <# } #>

          <# } #>

          <div class="tms-profile-card__content">

            <# if ( companyLogoUrl ) { #>
              <div class="tms-profile-card__logo">
                <img src="{{ companyLogoUrl }}" alt="{{ companyLogoAlt }}" class="tms-profile-card__logo-img" loading="lazy" />
              </div>
            <# } #>

            <# if ( personName ) { #>
              <h3 {{{ view.getRenderAttributeString( 'person_name' ) }}}>
                {{ personName }}
              </h3>
            <# } #>

            <# if ( occupationInfo ) { #>
              <div {{{ view.getRenderAttributeString( 'occupation_info' ) }}}>
                {{ occupationInfo }}
              </div>
            <# } #>

          </div><!-- /.tms-profile-card__content -->
        </div><!-- /.tms-card__header -->

        <div class="tms-card__body">

          <# if ( testimonialText ) { #>
            <div {{{ view.getRenderAttributeString( 'testimonial_text' ) }}}>
              {{{ testimonialText }}}
            </div>
          <# } #>

          <# if ( showDivider ) { #>
            <div class="tms-profile-card__divider" aria-hidden="true"></div>
          <# } #>

          <# if ( showStarRating ) { #>
            <div class="tms-card__icons">
              <ul class="tms-horizontal-icons">
                <# for ( var count = 1; count <= 5; count++ ) {
                  var itemClass = ( count > starRating )
                    ? 'tms-horizontal-icons__item tms-horizontal-icons--disabled'
                    : 'tms-horizontal-icons__item';
                #>
                  <li class="{{ itemClass }}">
                    {{{ starRatingIcon }}}
                  </li>
                <# } #>
              </ul>
            </div><!-- /.tms-card__icons -->
          <# } #>

        </div><!-- /.tms-card__body -->
      </div><!-- /.tms-profile-card -->
    </div><!-- /.tms-testimonial -->

    <?php
  }
}
