<?php
/**
 * Themeasy Elementor Widget: Price List
 *
 * The classic price menu (restaurants, salons, service businesses): repeater
 * rows of title + description + price joined by a dotted leader line, with an
 * optional image, badge, crossed-out original price and a featured highlight
 * per item. Pure repeater + CSS — rows are fully usable with no JavaScript;
 * the hover wash, image zoom and title accent are CSS-only flourishes that
 * respect (prefers-reduced-motion: reduce), and per-item links render as a
 * stretched overlay anchor so every row stays keyboard-focusable.
 *
 * @package Themeasy
 * @since 1.0.0
 */

namespace Themeasy\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Widget_Base;

/**
 * Responsible for registering controls and rendering the widget.
 */
class PriceList extends Widget_Base {
  public function get_name() {
    return 'themeasy-price-list';
  }

  public function get_title() {
    return esc_html__( 'Price List', 'themeasy-lite' );
  }

  public function get_icon() {
    return 'eicon-price-list';
  }

  public function get_custom_help_url() {
    return 'https://hub.themeasy.co/docs';
  }

  public function get_categories() {
    return ['themeasy_data_category', 'themeasy_content_category'];
  }

  public function get_keywords() {
    return ['themeasy', 'price', 'list', 'menu', 'restaurant', 'pricing', 'services'];
  }

  public function get_style_depends() {
    return ['themeasy-shared', 'themeasy-price-list'];
  }

  public function get_script_depends() {
    // Pure CSS widget — no JavaScript by design.
    return [];
  }

  /**
   * Register widget customization controls.
   *
   * @return void
   */
  protected function register_controls() {
    // ------------------------------------------------------------------------
    // Content section: Price List
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'price_list_content_section',
        [
          'label' => esc_html__( 'Price List', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $repeater = new Repeater();

      $repeater->add_control(
        'item_image',
        [
          'label' => esc_html__( 'Image', 'themeasy-lite' ),
          'description' => esc_html__( 'Optional. Leave empty for a text-only row.', 'themeasy-lite' ),
          'type' => Controls_Manager::MEDIA,
        ]
      );

      $repeater->add_control(
        'item_title',
        [
          'label' => esc_html__( 'Title', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'Menu Item', 'themeasy-lite' ),
          'dynamic' => [
            'active' => true,
          ],
        ]
      );

      $repeater->add_control(
        'item_description',
        [
          'label' => esc_html__( 'Description', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXTAREA,
          'rows' => 3,
          'placeholder' => esc_html__( 'Leave blank to hide', 'themeasy-lite' ),
          'dynamic' => [
            'active' => true,
          ],
        ]
      );

      $repeater->add_control(
        'item_price',
        [
          'label' => esc_html__( 'Price', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( '$9.99', 'themeasy-lite' ),
          'dynamic' => [
            'active' => true,
          ],
        ]
      );

      $repeater->add_control(
        'item_original_price',
        [
          'label' => esc_html__( 'Original Price', 'themeasy-lite' ),
          'description' => esc_html__( 'Optional. Shown crossed out before the price (sale).', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXT,
          'dynamic' => [
            'active' => true,
          ],
        ]
      );

      $repeater->add_control(
        'item_badge',
        [
          'label' => esc_html__( 'Badge', 'themeasy-lite' ),
          'description' => esc_html__( 'Optional pill next to the title (e.g. New, Chef\'s Choice).', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXT,
          'dynamic' => [
            'active' => true,
          ],
        ]
      );

      $repeater->add_control(
        'item_link',
        [
          'label' => esc_html__( 'Link', 'themeasy-lite' ),
          'type' => Controls_Manager::URL,
          'placeholder' => esc_html__( 'https://your-link.com', 'themeasy-lite' ),
          'description' => esc_html__( 'Makes the whole row clickable.', 'themeasy-lite' ),
        ]
      );

      $repeater->add_control(
        'item_featured',
        [
          'label' => esc_html__( 'Highlight Item', 'themeasy-lite' ),
          'description' => esc_html__( 'Accent-styled row that stands out in the list.', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => '',
        ]
      );

      $this->add_control(
        'items',
        [
          'label' => esc_html__( 'Items', 'themeasy-lite' ),
          'type' => Controls_Manager::REPEATER,
          'fields' => $repeater->get_controls(),
          'default' => [
            [
              'item_title' => esc_html__( 'Burrata & Heirloom Tomatoes', 'themeasy-lite' ),
              'item_description' => esc_html__( 'Creamy burrata, marinated heirloom tomatoes, basil oil, grilled sourdough.', 'themeasy-lite' ),
              'item_price' => esc_html__( '$14', 'themeasy-lite' ),
            ],
            [
              'item_title' => esc_html__( 'Wild Mushroom Risotto', 'themeasy-lite' ),
              'item_description' => esc_html__( 'Carnaroli rice, porcini, aged parmesan, white truffle oil.', 'themeasy-lite' ),
              'item_price' => esc_html__( '$22', 'themeasy-lite' ),
              'item_badge' => esc_html__( 'Chef\'s Choice', 'themeasy-lite' ),
              'item_featured' => 'yes',
            ],
            [
              'item_title' => esc_html__( 'Charred Octopus', 'themeasy-lite' ),
              'item_description' => esc_html__( 'Slow-cooked octopus, smoked paprika, confit potatoes, salsa verde.', 'themeasy-lite' ),
              'item_price' => esc_html__( '$26', 'themeasy-lite' ),
            ],
            [
              'item_title' => esc_html__( 'Tiramisù della Casa', 'themeasy-lite' ),
              'item_description' => esc_html__( 'Espresso-soaked savoiardi, mascarpone cream, bitter cocoa.', 'themeasy-lite' ),
              'item_price' => esc_html__( '$9', 'themeasy-lite' ),
              'item_original_price' => esc_html__( '$12', 'themeasy-lite' ),
            ],
            [
              'item_title' => esc_html__( 'House Negroni', 'themeasy-lite' ),
              'item_description' => esc_html__( 'Gin, sweet vermouth, Campari, orange zest.', 'themeasy-lite' ),
              'item_price' => esc_html__( '$11', 'themeasy-lite' ),
            ],
          ],
          'title_field' => '{{{ item_title }}}',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Layout
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'price_list_layout_section',
        [
          'label' => esc_html__( 'Layout', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $this->add_responsive_control(
        'columns',
        [
          'label' => esc_html__( 'Columns', 'themeasy-lite' ),
          'description' => esc_html__( 'Items flow left to right, top to bottom.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['u'],
          'range' => [
            'u' => [
              'min' => 1,
              'max' => 3,
              'step' => 1,
            ],
          ],
          'default' => [
            'unit' => 'u',
            'size' => 1,
          ],
          'mobile_default' => [
            'unit' => 'u',
            'size' => 1,
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-price-list' => '--tms-price-list-columns: {{SIZE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'column_gap',
        [
          'label' => esc_html__( 'Column Gap', 'themeasy-lite' ),
          'description' => esc_html__( 'Applies when the list has more than one column.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['rem', 'px'],
          'range' => [
            'rem' => [
              'min' => 0,
              'max' => 8,
              'step' => 0.1,
            ],
            'px' => [
              'min' => 0,
              'max' => 128,
              'step' => 1,
            ],
          ],
          'default' => [
            'unit' => 'rem',
            'size' => 3,
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-price-list' => '--tms-price-list-column-gap: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'item_gap',
        [
          'label' => esc_html__( 'Item Spacing', 'themeasy-lite' ),
          'description' => esc_html__( 'Vertical spacing between rows. It sits outside the row padding, so the two together set the rhythm.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['rem', 'px'],
          'range' => [
            'rem' => [
              'min' => 0,
              'max' => 6,
              'step' => 0.125,
            ],
            'px' => [
              'min' => 0,
              'max' => 96,
              'step' => 1,
            ],
          ],
          'default' => [
            'unit' => 'rem',
            'size' => 0.875,
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-price-list' => '--tms-price-list-item-gap: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'title_html_tag',
        [
          'label' => esc_html__( 'Title HTML Tag', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'separator' => 'before',
          'default' => 'h4',
          'options' => [
            'h2' => 'H2',
            'h3' => 'H3',
            'h4' => 'H4',
            'h5' => 'H5',
            'h6' => 'H6',
            'p' => 'p',
            'div' => 'div',
            'span' => 'span',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: List Item
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'price_list_item_styles_section',
        [
          'label' => esc_html__( 'List Item', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_control(
        'item_hover_color',
        [
          'label' => esc_html__( 'Hover Highlight Color', 'themeasy-lite' ),
          'description' => esc_html__( 'Soft wash painted behind the row on hover.', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-price-list' => '--tms-price-list-hover-wash: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'item_padding',
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'description' => esc_html__( 'Breathing room inside the row. It is what the hover / featured highlight fills, so 0 makes the wash hug the text.', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'em', 'rem'],
          'default' => [
            'top' => 10,
            'right' => 15,
            'bottom' => 10,
            'left' => 15,
            'unit' => 'px',
            'isLinked' => false,
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-price-list' => '--tms-price-list-item-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'item_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'description' => esc_html__( 'Corners of the highlight plate. The row link\'s focus ring follows it.', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'em', 'rem', '%'],
          'selectors' => [
            '{{WRAPPER}} .tms-price-list' => '--tms-price-list-item-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      // Divider.
      $this->add_control(
        'divider_heading',
        [
          'label' => esc_html__( 'Divider', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'divider',
        [
          'label' => esc_html__( 'Show Divider', 'themeasy-lite' ),
          'description' => esc_html__( 'Thin line between rows, centered in the item spacing.', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => '',
        ]
      );

      $this->add_control(
        'divider_style',
        [
          'label' => esc_html__( 'Style', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'solid',
          'options' => [
            'solid' => esc_html__( 'Solid', 'themeasy-lite' ),
            'dashed' => esc_html__( 'Dashed', 'themeasy-lite' ),
            'dotted' => esc_html__( 'Dotted', 'themeasy-lite' ),
            'double' => esc_html__( 'Double', 'themeasy-lite' ),
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-price-list' => '--tms-price-list-divider-style: {{VALUE}};',
          ],
          'condition' => [
            'divider' => 'yes',
          ],
        ]
      );

      $this->add_responsive_control(
        'divider_weight',
        [
          'label' => esc_html__( 'Weight', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => [
              'min' => 1,
              'max' => 6,
              'step' => 0.5,
            ],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-price-list' => '--tms-price-list-divider-weight: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'divider' => 'yes',
          ],
        ]
      );

      $this->add_control(
        'divider_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-price-list' => '--tms-price-list-divider-color: {{VALUE}};',
          ],
          'condition' => [
            'divider' => 'yes',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Image
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'price_list_image_styles_section',
        [
          'label' => esc_html__( 'Image', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_responsive_control(
        'image_size',
        [
          'label' => esc_html__( 'Size', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem'],
          'range' => [
            'px' => [
              'min' => 32,
              'max' => 160,
            ],
            'rem' => [
              'min' => 2,
              'max' => 10,
              'step' => 0.125,
            ],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-price-list' => '--tms-price-list-image-size: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'image_ratio',
        [
          'label' => esc_html__( 'Aspect Ratio', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => '1 / 1',
          'options' => [
            '1 / 1' => esc_html__( 'Square (1:1)', 'themeasy-lite' ),
            '4 / 3' => esc_html__( 'Landscape (4:3)', 'themeasy-lite' ),
            '3 / 4' => esc_html__( 'Portrait (3:4)', 'themeasy-lite' ),
            'auto' => esc_html__( 'Natural', 'themeasy-lite' ),
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-price-list' => '--tms-price-list-image-ratio: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'image_gap',
        [
          'label' => esc_html__( 'Spacing', 'themeasy-lite' ),
          'description' => esc_html__( 'Gap between the image and the row content.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem'],
          'range' => [
            'px' => [
              'min' => 0,
              'max' => 64,
            ],
            'rem' => [
              'min' => 0,
              'max' => 4,
              'step' => 0.125,
            ],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-price-list' => '--tms-price-list-image-gap: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Border::get_type(),
        [
          'name' => 'image_border',
          'selector' => '{{WRAPPER}} .tms-price-list__media',
        ]
      );

      $this->add_control(
        'image_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', '%'],
          'selectors' => [
            '{{WRAPPER}} .tms-price-list__media' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Badge
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'price_list_badge_styles_section',
        [
          'label' => esc_html__( 'Badge', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_control(
        'badge_background_color',
        [
          'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-price-list__badge' => 'background-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'badge_text_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-price-list__badge' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'badge_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-price-list__badge',
          'separator' => 'before',
        ]
      );

      $this->add_responsive_control(
        'badge_vertical_offset',
        [
          'label' => esc_html__( 'Vertical Offset', 'themeasy-lite' ),
          'description' => esc_html__( 'Use negative values to raise the badge above the text line.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'em'],
          'range' => [
            'px' => ['min' => -30, 'max' => 30, 'step' => 1],
            'em' => ['min' => -2, 'max' => 2, 'step' => 0.05],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-price-list__badge' => 'translate: 0 {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'badge_padding',
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', 'em'],
          'separator' => 'before',
          'selectors' => [
            '{{WRAPPER}} .tms-price-list__badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Border::get_type(),
        [
          'name' => 'badge_border',
          'selector' => '{{WRAPPER}} .tms-price-list__badge',
        ]
      );

      $this->add_responsive_control(
        'badge_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', '%', 'em', 'rem', 'custom'],
          'selectors' => [
            '{{WRAPPER}} .tms-price-list__badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Title
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'price_list_title_styles_section',
        [
          'label' => esc_html__( 'Title', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_control(
        'title_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-price-list__title' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'title_hover_color',
        [
          'label' => esc_html__( 'Hover Color', 'themeasy-lite' ),
          'description' => esc_html__( 'Applied while the row is hovered or keyboard-focused.', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-price-list__item:hover .tms-price-list__title,
             {{WRAPPER}} .tms-price-list__item:has(.tms-price-list__link:focus-visible) .tms-price-list__title' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'title_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-price-list__title',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Leader Line
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'price_list_leader_styles_section',
        [
          'label' => esc_html__( 'Leader Line', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_control(
        'leader_style',
        [
          'label' => esc_html__( 'Style', 'themeasy-lite' ),
          'description' => esc_html__( 'The line connecting the title to the price.', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'dotted',
          'options' => [
            'dotted' => esc_html__( 'Dotted', 'themeasy-lite' ),
            'dashed' => esc_html__( 'Dashed', 'themeasy-lite' ),
            'solid' => esc_html__( 'Solid', 'themeasy-lite' ),
            'double' => esc_html__( 'Double', 'themeasy-lite' ),
            'none' => esc_html__( 'None', 'themeasy-lite' ),
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-price-list' => '--tms-price-list-leader-style: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'leader_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-price-list' => '--tms-price-list-leader-color: {{VALUE}};',
          ],
          'condition' => [
            'leader_style!' => 'none',
          ],
        ]
      );

      $this->add_responsive_control(
        'leader_weight',
        [
          'label' => esc_html__( 'Weight', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => [
              'min' => 1,
              'max' => 6,
              'step' => 0.5,
            ],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-price-list' => '--tms-price-list-leader-weight: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'leader_style!' => 'none',
          ],
        ]
      );

      $this->add_responsive_control(
        'leader_spacing',
        [
          'label' => esc_html__( 'Spacing', 'themeasy-lite' ),
          'description' => esc_html__( 'Gap between the line and the title / price. Also keeps them apart when the line is set to None.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'em'],
          'range' => [
            'px' => [
              'min' => 0,
              'max' => 40,
            ],
            'em' => [
              'min' => 0,
              'max' => 2.5,
              'step' => 0.125,
            ],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-price-list' => '--tms-price-list-leader-spacing: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'leader_offset',
        [
          'label' => esc_html__( 'Vertical Offset', 'themeasy-lite' ),
          'description' => esc_html__( 'Fine-tune the line position relative to the text baseline.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => [
              'min' => -10,
              'max' => 10,
            ],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-price-list' => '--tms-price-list-leader-offset: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'leader_style!' => 'none',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Price
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'price_list_price_styles_section',
        [
          'label' => esc_html__( 'Price', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_control(
        'price_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-price-list__price' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'price_hover_color',
        [
          'label' => esc_html__( 'Hover Color', 'themeasy-lite' ),
          'description' => esc_html__( 'Applied while the row is hovered or keyboard-focused.', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-price-list__item:hover .tms-price-list__price,
             {{WRAPPER}} .tms-price-list__item:has(.tms-price-list__link:focus-visible) .tms-price-list__price' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'price_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-price-list__price',
        ]
      );

      // Original Price.
      $this->add_control(
        'original_price_heading',
        [
          'label' => esc_html__( 'Original Price', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'original_price_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-price-list__price-old' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'original_price_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-price-list__price-old',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Description
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'price_list_description_styles_section',
        [
          'label' => esc_html__( 'Description', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_control(
        'description_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-price-list__description' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'description_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-price-list__description',
        ]
      );

      $this->add_responsive_control(
        'description_spacing',
        [
          'label' => esc_html__( 'Spacing', 'themeasy-lite' ),
          'description' => esc_html__( 'Gap between the title row and the description.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem'],
          'range' => [
            'px' => [
              'min' => 0,
              'max' => 40,
            ],
            'rem' => [
              'min' => 0,
              'max' => 2.5,
              'step' => 0.125,
            ],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-price-list' => '--tms-price-list-description-spacing: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Featured Item
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'price_list_featured_styles_section',
        [
          'label' => esc_html__( 'Featured Item', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_control(
        'featured_accent_color',
        [
          'label' => esc_html__( 'Accent Color', 'themeasy-lite' ),
          'description' => esc_html__( 'Drives the featured ring, the badge tint, the hover title color and the focus outline.', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-price-list' => '--tms-price-list-accent: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'featured_background_color',
        [
          'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
          'description' => esc_html__( 'Replaces the default accent wash behind featured rows.', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-price-list' => '--tms-price-list-featured-bg: {{VALUE}};',
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
    $items = is_array( $settings['items'] ?? null ) ? $settings['items'] : [];

    // Early return if no content.
    if ( empty( $items ) ) {
      return;
    }

    $title_tag = themeasy_sanitize_heading_tag(
      $settings['title_html_tag'] ?? 'h4',
      'h4',
      ['h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div', 'span']
    );

    // ------------------------------------------------------------------------
    // Wrapper classes + attributes.
    // ------------------------------------------------------------------------
    $wrapper_classes = ['tms-price-list'];
    $wrapper_atts = [];

    if ( 'yes' === ( $settings['divider'] ?? '' ) ) {
      $wrapper_classes[] = 'tms-price-list--divider';
    }

    $wrapper_classes_output = implode( ' ', array_filter( $wrapper_classes ) );

    // ------------------------------------------------------------------------
    // Output.
    // ------------------------------------------------------------------------
    ?>
    <div class="<?php echo esc_attr( $wrapper_classes_output ); ?>"<?php themeasy_print_html_attributes( $wrapper_atts ); ?>>
      <ul class="tms-price-list__items tms-animation__target">

        <?php foreach ( $items as $index => $item ) : ?>
          <?php
          $item_title = $item['item_title'] ?? '';
          $item_description = $item['item_description'] ?? '';
          $item_price = $item['item_price'] ?? '';
          $item_original_price = $item['item_original_price'] ?? '';
          $item_badge = $item['item_badge'] ?? '';

          // Skip rows with no meaningful content.
          if ( '' === $item_title && '' === $item_price && '' === $item_description ) {
            continue;
          }

          $item_image = is_array( $item['item_image'] ?? null ) ? $item['item_image'] : [];
          $item_image_id = absint( $item_image['id'] ?? 0 );
          $item_image_url = $item_image['url'] ?? '';
          // Elementor's MEDIA control does not persist alt; resolve it from the attachment.
          $item_image_alt = $item_image_id
            ? (string) get_post_meta( $item_image_id, '_wp_attachment_image_alt', true )
            : '';

          // Row classes.
          $item_classes = ['tms-price-list__item'];

          if ( 'yes' === ( $item['item_featured'] ?? '' ) ) {
            $item_classes[] = 'tms-price-list__item--featured';
          }

          $item_classes_output = implode( ' ', array_filter( $item_classes ) );

          // Inline editing render attributes.
          $title_key = $this->get_repeater_setting_key( 'item_title', 'items', $index );
          $this->add_render_attribute( $title_key, 'class', 'tms-price-list__title' );
          $this->add_inline_editing_attributes( $title_key, 'none' );

          $description_key = $this->get_repeater_setting_key( 'item_description', 'items', $index );
          $this->add_render_attribute( $description_key, 'class', 'tms-price-list__description' );
          $this->add_inline_editing_attributes( $description_key, 'basic' );

          $price_key = $this->get_repeater_setting_key( 'item_price', 'items', $index );
          $this->add_render_attribute( $price_key, 'class', 'tms-price-list__price' );
          $this->add_inline_editing_attributes( $price_key, 'none' );

          $original_price_key = $this->get_repeater_setting_key( 'item_original_price', 'items', $index );
          $this->add_render_attribute( $original_price_key, 'class', 'tms-price-list__price-old' );
          $this->add_inline_editing_attributes( $original_price_key, 'none' );

          // Whole-row link (rendered as an overlay anchor so the markup stays
          // semantic and the row is never an unfocusable div).
          $link_key = 'item_link_' . $index;
          $has_link = !empty( $item['item_link']['url'] );

          if ( $has_link ) {
            $this->add_link_attributes( $link_key, $item['item_link'] );
            themeasy_add_external_link_rel( $this, $link_key, $item['item_link'] );
            $this->add_render_attribute( $link_key, 'class', 'tms-price-list__link' );
          }
          ?>

          <li class="<?php echo esc_attr( $item_classes_output ); ?>">

            <?php if ( $item_image_url ) : ?>
              <span class="tms-price-list__media">
                <?php themeasy_render_attachment_image( $item_image_id, 'medium', $item_image_url, $item_image_alt, 'tms-price-list__img', [], 'lazy' ); ?>
              </span>
            <?php endif; ?>

            <div class="tms-price-list__body">
              <div class="tms-price-list__header">

                <?php if ( $item_title ) : ?>
                  <<?php echo tag_escape( $title_tag ); ?> <?php $this->print_render_attribute_string( $title_key ); ?>>
                    <?php echo esc_html( $item_title ); ?>
                  </<?php echo tag_escape( $title_tag ); ?>>
                <?php endif; ?>

                <?php if ( $item_badge ) : ?>
                  <span class="tms-price-list__badge tms-tag tms-tag--micro"><?php echo esc_html( $item_badge ); ?></span>
                <?php endif; ?>

                <span class="tms-price-list__leader" aria-hidden="true"></span>

                <?php // Compare with '' (as the empty-row guard above does): a price of "0" is falsy in PHP. ?>
                <?php if ( '' !== $item_price || '' !== $item_original_price ) : ?>
                  <span class="tms-price-list__prices">
                    <?php if ( '' !== $item_original_price ) : ?>
                      <del <?php $this->print_render_attribute_string( $original_price_key ); ?>>
                        <?php echo esc_html( $item_original_price ); ?>
                      </del>
                    <?php endif; ?>

                    <?php if ( '' !== $item_price ) : ?>
                      <span <?php $this->print_render_attribute_string( $price_key ); ?>>
                        <?php echo esc_html( $item_price ); ?>
                      </span>
                    <?php endif; ?>
                  </span>
                <?php endif; ?>

              </div><!-- /.tms-price-list__header -->

              <?php if ( $item_description ) : ?>
                <p <?php $this->print_render_attribute_string( $description_key ); ?>>
                  <?php echo wp_kses_post( $item_description ); ?>
                </p>
              <?php endif; ?>

            </div><!-- /.tms-price-list__body -->

            <?php if ( $has_link ) : ?>
              <a <?php $this->print_render_attribute_string( $link_key ); ?>>
                <span class="tms-price-list__link-label"><?php echo esc_html( $item_title ? $item_title : __( 'View item', 'themeasy-lite' ) ); ?></span>
              </a>
            <?php endif; ?>

          </li><!-- /.tms-price-list__item -->
        <?php endforeach; ?>

      </ul><!-- /.tms-price-list__items -->
    </div><!-- /.tms-price-list -->
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
      // User text reaches raw {{{ }}} output: sanitize with kses parity.
      var sanitizeInline = ( window.Themeasy && window.Themeasy.sanitizeInlineHtml )
        ? window.Themeasy.sanitizeInlineHtml
        : _.escape;

      // ------------------------------------------------------------------------
      // Settings.
      // ------------------------------------------------------------------------
      var items = settings.items || [];

      if ( ! items.length ) {
        return;
      }

      var allowedTitleTags = [ 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div', 'span' ];
      var titleTag = ( allowedTitleTags.indexOf( settings.title_html_tag ) !== -1 )
        ? settings.title_html_tag
        : 'h4';

      // ------------------------------------------------------------------------
      // Wrapper classes + attributes.
      // ------------------------------------------------------------------------
      var wrapperClasses = [ 'tms-price-list' ];
      var wrapperAtts    = {};

      // Scheme guard for every URL interpolated below — the editor view has no
      // add_link_attributes()/esc_url(). Fail closed when the helper is absent:
      // the guard lives in exactly one place so it can never drift narrow again.
      var safeUrl = ( window.Themeasy && window.Themeasy.safeUrl )
        ? window.Themeasy.safeUrl
        : function () { return ''; };

      if ( settings.divider === 'yes' ) {
        wrapperClasses.push( 'tms-price-list--divider' );
      }

      var wrapperClassStr = wrapperClasses.filter( Boolean ).join( ' ' );
    #>

    <div class="{{ wrapperClassStr }}" {{{ Themeasy.htmlAttributes( wrapperAtts ) }}}>
      <ul class="tms-price-list__items tms-animation__target">

        <# _.each( items, function( item, index ) {
          var itemTitle         = item.item_title || '';
          var itemDescription   = item.item_description || '';
          var itemPrice         = item.item_price || '';
          var itemOriginalPrice = item.item_original_price || '';
          var itemBadge         = item.item_badge || '';

          // Skip rows with no meaningful content.
          if ( ! itemTitle && ! itemPrice && ! itemDescription ) {
            return;
          }

          var itemImageUrl = safeUrl( ( item.item_image && item.item_image.url ) ? item.item_image.url : '' );
          var itemImageAlt = ( item.item_image && item.item_image.alt ) ? item.item_image.alt : '';

          // Row classes.
          var itemClasses = [ 'tms-price-list__item' ];

          if ( item.item_featured === 'yes' ) {
            itemClasses.push( 'tms-price-list__item--featured' );
          }

          var itemClassStr = itemClasses.filter( Boolean ).join( ' ' );

          // Inline editing render attributes.
          var titleKey = view.getRepeaterSettingKey( 'item_title', 'items', index );
          view.addRenderAttribute( titleKey, 'class', 'tms-price-list__title' );
          view.addInlineEditingAttributes( titleKey, 'none' );

          var descriptionKey = view.getRepeaterSettingKey( 'item_description', 'items', index );
          view.addRenderAttribute( descriptionKey, 'class', 'tms-price-list__description' );
          view.addInlineEditingAttributes( descriptionKey, 'basic' );

          var priceKey = view.getRepeaterSettingKey( 'item_price', 'items', index );
          view.addRenderAttribute( priceKey, 'class', 'tms-price-list__price' );
          view.addInlineEditingAttributes( priceKey, 'none' );

          var originalPriceKey = view.getRepeaterSettingKey( 'item_original_price', 'items', index );
          view.addRenderAttribute( originalPriceKey, 'class', 'tms-price-list__price-old' );
          view.addInlineEditingAttributes( originalPriceKey, 'none' );

          // Whole-row link (overlay anchor — mirrors render()). The editor view
          // has no addLinkAttributes, so replicate what it emits: esc_url()'s
          // protocol guard plus the target/rel the URL control toggles carry.
          var hasLink = !! ( item.item_link && item.item_link.url );
          var linkUrl = hasLink ? item.item_link.url : '';
          var safeLinkUrl = safeUrl( linkUrl );
          var linkIsExternal = hasLink && !! item.item_link.is_external;
          var linkNofollow = hasLink && !! item.item_link.nofollow;
        #>

          <li class="{{ itemClassStr }}">

            <# if ( itemImageUrl ) { #>
              <span class="tms-price-list__media">
                <img class="tms-price-list__img" src="{{ itemImageUrl }}" alt="{{ itemImageAlt }}" loading="lazy" />
              </span>
            <# } #>

            <div class="tms-price-list__body">
              <div class="tms-price-list__header">

                <# if ( itemTitle ) { #>
                  <{{{ titleTag }}} {{{ view.getRenderAttributeString( titleKey ) }}}>
                    {{ itemTitle }}
                  </{{{ titleTag }}}>
                <# } #>

                <# if ( itemBadge ) { #>
                  <span class="tms-price-list__badge tms-tag tms-tag--micro">{{ itemBadge }}</span>
                <# } #>

                <span class="tms-price-list__leader" aria-hidden="true"></span>

                <# if ( itemPrice || itemOriginalPrice ) { #>
                  <span class="tms-price-list__prices">
                    <# if ( itemOriginalPrice ) { #>
                      <del {{{ view.getRenderAttributeString( originalPriceKey ) }}}>
                        {{ itemOriginalPrice }}
                      </del>
                    <# } #>

                    <# if ( itemPrice ) { #>
                      <span {{{ view.getRenderAttributeString( priceKey ) }}}>
                        {{ itemPrice }}
                      </span>
                    <# } #>
                  </span>
                <# } #>

              </div><!-- /.tms-price-list__header -->

              <# if ( itemDescription ) { #>
                <p {{{ view.getRenderAttributeString( descriptionKey ) }}}>
                  {{{ sanitizeInline( itemDescription, 'rich' ) }}}
                </p>
              <# } #>

            </div><!-- /.tms-price-list__body -->

            <# if ( hasLink ) { #>
              <a class="tms-price-list__link" href="{{ safeLinkUrl }}"<# if ( linkIsExternal ) { #> target="_blank"<# } #><# if ( linkNofollow ) { #> rel="nofollow"<# } #>>
                <span class="tms-price-list__link-label">{{ itemTitle || '<?php echo esc_js( __( 'View item', 'themeasy-lite' ) ); ?>' }}</span>
              </a>
            <# } #>

          </li><!-- /.tms-price-list__item -->

        <# }); #>

      </ul><!-- /.tms-price-list__items -->
    </div><!-- /.tms-price-list -->

    <?php
  }
}
