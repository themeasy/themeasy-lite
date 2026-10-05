<?php
/**
 * Themeasy Elementor Widget: Pricing Table
 *
 * Customizable pricing table with repeater-based plans, feature checklists, a
 * price toggle (monthly/yearly), featured badge variants, and multiple button
 * styles. Cards are pure CSS; the price toggle animates values via the widget's
 * own JS and degrades to a static table without it.
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
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Widget_Base;
use Themeasy\Core\Entitlement;

/**
 * Pricing Table widget -- registers controls and renders output.
 */
class PricingTable extends Widget_Base {
  public function get_name() {
    return 'themeasy-pricing-table';
  }

  public function get_title() {
    return esc_html__( 'Pricing Table', 'themeasy-lite' );
  }

  public function get_icon() {
    return 'eicon-price-table';
  }

  public function get_custom_help_url() {
    return 'https://themeasy.co/help-center';
  }

  public function get_categories() {
    return ['themeasy_data_category', 'themeasy_content_category'];
  }

  public function get_keywords() {
    return ['themeasy', 'pricing', 'table', 'plan', 'price', 'toggle', 'subscription'];
  }

  public function get_style_depends() {
    return ['themeasy-shared', 'themeasy-pricing-table'];
  }

  // No themeasy-shared script: the table renders none of the shared JS
  // components (progress, side widget, sliding tabs), and that script is
  // premium — the Free build has no such handle to load.
  public function get_script_depends() {
    return ['themeasy-pricing-table'];
  }

  /**
   * Register widget controls.
   *
   * @return void
   */
  protected function register_controls() {
    // ------------------------------------------------------------------------
    // Content section: Pricing Plans
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'pricing_table_plans_section',
        [
          'label' => esc_html__( 'Pricing Plans', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
    );

      $repeater = new Repeater();

      $repeater->add_control(
        'plan_name',
        [
          'label' => esc_html__( 'Plan Name', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'placeholder' => esc_html__( 'e.g. Starter, Pro, Business', 'themeasy-lite' ),
        ]
      );

      $repeater->add_control(
        'price_prefix',
        [
          'label' => esc_html__( 'Currency Symbol', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'e.g. $ or EUR', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'separator' => 'before',
        ]
      );

      $repeater->add_control(
        'price_suffix',
        [
          'label' => esc_html__( 'Billing Period', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'e.g. /month or /year', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
        ]
      );

      $repeater->add_control(
        'price_suffix_yearly',
        [
          'label' => esc_html__( 'Billing Period (Yearly)', 'themeasy-lite' ),
          'description' => esc_html__( 'Shown instead of the Billing Period while the price toggle is on the yearly price. Leave empty to keep one text for both periods.', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'e.g. /year', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
        ]
      );

      $repeater->add_control(
        'yearly_price',
        [
          'label' => esc_html__( 'Yearly Price', 'themeasy-lite' ),
          'type' => Controls_Manager::NUMBER,
          'step' => 0.1,
        ]
      );

      $repeater->add_control(
        'monthly_price',
        [
          'label' => esc_html__( 'Monthly Price (Optional)', 'themeasy-lite' ),
          'description' => esc_html__( 'Used when the price toggle is enabled. Leave empty if this plan has no monthly price.', 'themeasy-lite' ),
          'type' => Controls_Manager::NUMBER,
          'step' => 0.1,
        ]
      );

      $repeater->add_control(
        'is_featured',
        [
          'label' => esc_html__( 'Highlight This Plan', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'separator' => 'before',
          'default' => '',
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
        ]
      );

      $repeater->add_control(
        'featured_badge_text',
        [
          'label' => esc_html__( 'Badge Text', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'e.g. Most Popular, Best Value', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'default' => 'Most Popular',
          'condition' => ['is_featured' => 'yes'],
        ]
      );

      $repeater->add_control(
        'features_list',
        [
          'label' => esc_html__( 'Features List', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'e.g. Feature A [y] | Feature B [n]', 'themeasy-lite' ),
          'description' => esc_html__( 'Separate items with "|" and use [y] for check or [n] for uncheck icons.', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXTAREA,
          'separator' => 'before',
          'default' => esc_html__( 'Unlimited Projects [y] | Email Support [y] | Custom Domain [y] | Team Collaboration [n] | Advanced Analytics [n]', 'themeasy-lite' ),
        ]
      );

      $repeater->add_control(
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

      $repeater->add_control(
        'button_label',
        [
          'label' => esc_html__( 'Label', 'themeasy-lite' ),
          'label_block' => false,
          'placeholder' => esc_html__( 'Type your text here', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'Call to Action', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
          'condition' => ['link_type' => 'button'],
        ]
      );

      // The hidden label only feeds the scroll text animation, which is Pro.
      if ( Entitlement::can_use_widgets() ) {
        $repeater->add_control(
          'button_hidden_label',
          [
            'label' => esc_html__( 'Hidden Label', 'themeasy-lite' ),
            'placeholder' => esc_html__( 'Type your text here', 'themeasy-lite' ),
            'description' => esc_html__( 'This hidden label is used for scroll text animations.', 'themeasy-lite' ),
            'type' => Controls_Manager::TEXT,
            'default' => esc_html__( 'Call to Action', 'themeasy-lite' ),
            'dynamic' => ['active' => true],
            'condition' => ['link_type' => 'button'],
          ]
        );
      }

      $repeater->add_control(
        'button_icon',
        [
          'label' => esc_html__( 'Icon', 'themeasy-lite' ),
          'type' => Controls_Manager::ICONS,
          'condition' => ['link_type' => 'button'],
        ]
      );

      $repeater->add_control(
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
          'condition' => ['link_type' => 'button'],
        ]
      );

      $repeater->add_control(
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
          'condition' => ['link_type' => ['button', 'card']],
        ]
      );

      $this->add_control(
        'pricing_table',
        [
          'label' => esc_html__( 'Pricing Plans', 'themeasy-lite' ),
          'type' => Controls_Manager::REPEATER,
          'fields' => $repeater->get_controls(),
          'title_field' => '{{{ plan_name }}}',
          'default' => [
            [
              'plan_name' => 'Starter',
              'price_prefix' => '$',
              'yearly_price' => 16,
              'monthly_price' => 22,
              'price_suffix' => '/month',
              'features_list' => '1 Website [y] | 1 GB Storage [y] | Basic Support [y] | Team Access [n] | Analytics Dashboard [n]',
              'button_label' => 'Get Started',
              'link' => ['url' => '', 'is_external' => true, 'nofollow' => true],
            ],
            [
              'plan_name' => 'Pro',
              'price_prefix' => '$',
              'yearly_price' => 29,
              'monthly_price' => 39,
              'price_suffix' => '/month',
              'features_list' => '5 Websites [y] | 10 GB Storage [y] | Priority Support [y] | Team Access [y] | Analytics Dashboard [n]',
              'button_label' => 'Get Started',
              'link' => ['url' => '', 'is_external' => true, 'nofollow' => true],
            ],
            [
              'plan_name' => 'Business',
              'price_prefix' => '$',
              'yearly_price' => 45,
              'monthly_price' => 60,
              'price_suffix' => '/month',
              'features_list' => 'Unlimited Websites [y] | 100 GB Storage [y] | 24/7 Support [y] | Team Access [y] | Analytics Dashboard [y]',
              'button_label' => 'Get Started',
              'link' => ['url' => '', 'is_external' => true, 'nofollow' => true],
            ],
          ],
        ]
      );

      $this->add_control(
        'price_thousand_separator',
        [
          'label' => esc_html__( 'Thousand Separator', 'themeasy-lite' ),
          'description' => esc_html__( 'Group the digits of the price using the site locale separator (e.g. 6400 becomes 6,400).', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => '',
          'separator' => 'before',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Price Toggle
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'pricing_table_toggle_section',
        [
          'label' => esc_html__( 'Price Toggle', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
    );

      $this->add_control(
        'enable_price_toggle',
        [
          'label' => esc_html__( 'Enable Price Toggle', 'themeasy-lite' ),
          'description' => esc_html__( 'Allow users to toggle between pricing options (e.g. monthly vs yearly).', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
        ]
      );

      $this->add_control(
        'toggle_default_period',
        [
          'label' => esc_html__( 'Default Period', 'themeasy-lite' ),
          'description' => esc_html__( 'Which period the table opens on.', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'yearly',
          'options' => [
            'monthly' => esc_html__( 'Monthly (toggle off)', 'themeasy-lite' ),
            'yearly' => esc_html__( 'Yearly (toggle on)', 'themeasy-lite' ),
          ],
          'condition' => ['enable_price_toggle' => 'yes'],
        ]
      );

      $this->add_control(
        'toggle_label_off',
        [
          'label' => esc_html__( 'Label for Toggle Off', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'e.g. Monthly', 'themeasy-lite' ),
          'description' => esc_html__( 'Label shown when the toggle is OFF.', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'separator' => 'before',
          'default' => esc_html__( 'Monthly', 'themeasy-lite' ),
          'condition' => ['enable_price_toggle' => 'yes'],
        ]
      );

      $this->add_control(
        'toggle_label_on',
        [
          'label' => esc_html__( 'Label for Toggle On', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'e.g. Yearly', 'themeasy-lite' ),
          'description' => esc_html__( 'Label shown when the toggle is ON.', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'Yearly', 'themeasy-lite' ),
          'condition' => ['enable_price_toggle' => 'yes'],
        ]
      );

      $this->add_control(
        'toggle_label_on_badge',
        [
          'label' => esc_html__( 'Yearly Badge Text', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'e.g. Save 25%', 'themeasy-lite' ),
          'description' => esc_html__( 'Text badge shown next to the yearly label (toggle ON). Useful for highlighting discounts.', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'separator' => 'before',
          'default' => esc_html__( 'SAVE 25%', 'themeasy-lite' ),
          'condition' => ['enable_price_toggle' => 'yes'],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Table Layout
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'pricing_table_layout_styles_section',
        [
          'label' => esc_html__( 'Table Layout', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      $this->add_control(
        'enable_grid_border',
        [
          'label' => esc_html__( 'Enable Grid Border', 'themeasy-lite' ),
          'description' => esc_html__( 'Adds clean borders between pricing cards, creating a sleek and organized layout.', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => '',
        ]
      );

      $this->add_control(
        'horizontal_alignment',
        [
          'label' => esc_html__( 'Horizontal Alignment', 'themeasy-lite' ),
          'type' => Controls_Manager::CHOOSE,
          'options' => [
            'flex-start' => [
              'title' => esc_html__( 'Left', 'themeasy-lite' ),
              'icon' => 'eicon-align-start-h',
            ],
            'center' => [
              'title' => esc_html__( 'Center', 'themeasy-lite' ),
              'icon' => 'eicon-align-center-v',
            ],
            'flex-end' => [
              'title' => esc_html__( 'Right', 'themeasy-lite' ),
              'icon' => 'eicon-align-end-h',
            ],
          ],
          'default' => 'center',
          'selectors' => [
            '{{WRAPPER}} .tms-pricing-table__card .tms-card__body,
             {{WRAPPER}} .tms-pricing-table__card .tms-card__wrapper,
             {{WRAPPER}} .tms-pricing-table__card .tms-card__header' => 'align-items: {{VALUE}}; text-align: {{VALUE}};',
          ],
        ]
      );

      // ---- Grid border sub-group ----
      $this->add_control(
        'grid_border_color',
        [
          'label' => esc_html__( 'Border Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'separator' => 'before',
          'selectors' => [
            '{{WRAPPER}} .tms-grid-border' => '--tms-grid-border-color: {{VALUE}};',
          ],
          'condition' => ['enable_grid_border' => 'yes'],
        ]
      );

      $this->add_control(
        'grid_border_width',
        [
          'label' => esc_html__( 'Border Width', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => ['min' => 1, 'max' => 10, 'step' => 1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-grid-border' => '--tms-grid-border-width: {{SIZE}}{{UNIT}};',
          ],
          'condition' => ['enable_grid_border' => 'yes'],
        ]
      );

      $this->add_control(
        'grid_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'description' => esc_html__( 'Rounds the outer corners of the grid. The lines between cards stay straight.', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', '%'],
          'selectors' => [
            '{{WRAPPER}} .tms-grid-border' => '--tms-grid-border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
          'condition' => ['enable_grid_border' => 'yes'],
        ]
      );

      $this->add_control(
        'grid_border_divider',
        [
          'type' => Controls_Manager::DIVIDER,
        ]
      );

      // ---- Card sub-group (border, radius, shadow and spacing only apply
      // without the grid border, which owns the cell edges) ----
      $this->add_control(
        'card_background_color',
        [
          'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-card' => 'background-color: {{VALUE}}',
          ],
        ]
      );

      $this->add_responsive_control(
        'spacing_between_items',
        [
          'label' => esc_html__( 'Spacing Between Items', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['rem', 'px'],
          'range' => [
            'rem' => ['min' => 0, 'max' => 10, 'step' => 0.1],
            'px' => ['min' => 0, 'max' => 100, 'step' => 1],
          ],
          'default' => ['unit' => 'rem', 'size' => 3],
          'selectors' => [
            '{{WRAPPER}} [class*="row-cols"]' => '--tms-gutter-x: {{SIZE}}{{UNIT}}; --tms-gutter-y: {{SIZE}}{{UNIT}};',
          ],
          'condition' => ['enable_grid_border' => ''],
        ]
      );

      $this->add_control(
        'card_padding',
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem'],
          'selectors' => [
            // With the grid border the card itself stays flush (its header and
            // body dividers run edge to edge), so the value pads each section.
            '{{WRAPPER}} .tms-pricing-table:not(.tms-grid-border) .tms-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            '{{WRAPPER}} .tms-grid-border' => '--tms-grid-border-cell-pt: {{TOP}}{{UNIT}}; --tms-grid-border-cell-pr: {{RIGHT}}{{UNIT}}; --tms-grid-border-cell-pb: {{BOTTOM}}{{UNIT}}; --tms-grid-border-cell-pl: {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Border::get_type(),
        [
          'name' => 'card_border',
          'selector' => '{{WRAPPER}} .tms-card',
          'condition' => ['enable_grid_border' => ''],
        ]
      );

      $this->add_control(
        'card_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem'],
          'selectors' => [
            // Corner-var fan-out: the card consumes these, and the featured
            // label variants mirror the card's top corners so the two always
            // read as one rounded block.
            '{{WRAPPER}} .tms-pricing-table' => '--tms-pt-radius-tl: {{TOP}}{{UNIT}}; --tms-pt-radius-tr: {{RIGHT}}{{UNIT}}; --tms-pt-radius-br: {{BOTTOM}}{{UNIT}}; --tms-pt-radius-bl: {{LEFT}}{{UNIT}};',
          ],
          'condition' => ['enable_grid_border' => ''],
        ]
      );

      $this->add_group_control(
        Group_Control_Box_Shadow::get_type(),
        [
          'name' => 'card_box_shadow',
          'label' => esc_html__( 'Box Shadow', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-card',
          'condition' => ['enable_grid_border' => ''],
        ]
      );

      // ---- Featured card sub-group ----
      // The plan flagged as featured also carries .tms-pricing-table--active,
      // which until now no rule targeted at all: the badge was the only way to
      // set it apart. These three outrank the card keys above on specificity
      // (two classes vs one) and ship with empty defaults, so a table that
      // never authors them renders exactly as before.
      $this->add_control(
        'featured_card_heading',
        [
          'label' => esc_html__( 'Featured Card', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'featured_card_background_color',
        [
          'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
          'description' => esc_html__( 'Applies only to the plan marked as featured. Falls back to the card background when empty.', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-pricing-table__card.tms-pricing-table--active' => 'background-color: {{VALUE}}',
          ],
        ]
      );

      // Text, icons and button of the featured card. The background above
      // could not go dark on its own: every text and button color was
      // table-wide, so the ink of a light table had nowhere to be inverted on
      // one dark card. Each selector adds the --active class to its table-wide
      // twin, so it outranks it; all empty by default.

      $this->add_control(
        'featured_plan_name_text_color',
        [
          'label' => esc_html__( 'Plan Name Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-pricing-table__card.tms-pricing-table--active .tms-pricing-table__plan-name'
              => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'featured_price_text_color',
        [
          'label' => esc_html__( 'Price Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-pricing-table__card.tms-pricing-table--active
             .tms-pricing-table__price-wrapper .tms-pricing-table__price'
              => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'featured_price_affix_text_color',
        [
          'label' => esc_html__( 'Currency & Billing Period Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-pricing-table__card.tms-pricing-table--active
             .tms-pricing-table__price-wrapper .tms-pricing-table__affix-text'
              => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'featured_features_list_text_color',
        [
          'label' => esc_html__( 'Features Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-pricing-table__card.tms-pricing-table--active
             .tms-pricing-table__checklist .tms-pricing-table__checklist-text'
              => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'featured_features_list_icon_color',
        [
          'label' => esc_html__( 'Features Icon Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-pricing-table__card.tms-pricing-table--active .tms-pricing-table__checklist svg'
              => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'featured_features_list_icon_excluded_color',
        [
          'label' => esc_html__( 'Excluded Icon Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-pricing-table__card.tms-pricing-table--active .tms-pricing-table__checklist
              svg.tms-pricing-table__checklist-icon--excluded' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'featured_button_background_color',
        [
          'label' => esc_html__( 'Button Background', 'themeasy-lite' ),
          'description' => esc_html__( 'Outranks the table-wide hover: set the featured hover too.', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-pricing-table__card.tms-pricing-table--active .tms-button'
              => 'background-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'featured_button_border_color',
        [
          'label' => esc_html__( 'Button Border Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-pricing-table__card.tms-pricing-table--active .tms-button' => 'border-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'featured_button_text_color',
        [
          'label' => esc_html__( 'Button Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-pricing-table__card.tms-pricing-table--active .tms-button .tms-button__text,
             {{WRAPPER}} .tms-pricing-table__card.tms-pricing-table--active .tms-button .tms-button__text > *'
              => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'featured_button_icon_color',
        [
          'label' => esc_html__( 'Button Icon Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-pricing-table__card.tms-pricing-table--active .tms-button .tms-button__icon,
             {{WRAPPER}} .tms-pricing-table__card.tms-pricing-table--active .tms-button svg' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'featured_button_hover_background_color',
        [
          'label' => esc_html__( 'Button Hover Background', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-pricing-table__card.tms-pricing-table--active .tms-button:hover'
              => 'background-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'featured_button_hover_border_color',
        [
          'label' => esc_html__( 'Button Hover Border Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-pricing-table__card.tms-pricing-table--active .tms-button:hover'
              => 'border-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'featured_button_hover_text_color',
        [
          'label' => esc_html__( 'Button Hover Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-pricing-table__card.tms-pricing-table--active .tms-button:hover .tms-button__text,
             {{WRAPPER}} .tms-pricing-table__card.tms-pricing-table--active .tms-button:hover .tms-button__text > *'
              => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'featured_button_hover_icon_color',
        [
          'label' => esc_html__( 'Button Hover Icon Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-pricing-table__card.tms-pricing-table--active .tms-button:hover .tms-button__icon,
             {{WRAPPER}} .tms-pricing-table__card.tms-pricing-table--active .tms-button:hover svg'
              => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Border::get_type(),
        [
          'name' => 'featured_card_border',
          'selector' => '{{WRAPPER}} .tms-pricing-table__card.tms-pricing-table--active',
          'condition' => ['enable_grid_border' => ''],
        ]
      );

      $this->add_control(
        'featured_card_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem'],
          'selectors' => [
            // Same corner-var fan-out as card_border_radius, but redeclared on
            // the featured card itself, so its label keeps mirroring the very
            // corners this card is drawn with.
            '{{WRAPPER}} .tms-pricing-table__card.tms-pricing-table--active' => '--tms-pt-radius-tl: {{TOP}}{{UNIT}}; --tms-pt-radius-tr: {{RIGHT}}{{UNIT}}; --tms-pt-radius-br: {{BOTTOM}}{{UNIT}}; --tms-pt-radius-bl: {{LEFT}}{{UNIT}};',
          ],
          'condition' => ['enable_grid_border' => ''],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Featured Badge
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'pricing_table_featured_badge_styles_section',
        [
          'label' => esc_html__( 'Featured Badge', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      $this->add_control(
        'featured_badge_style',
        [
          'label' => esc_html__( 'Label Style', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'top-label-filled',
          'options' => [
            'top-label-filled' => esc_html__( 'Top Label Filled', 'themeasy-lite' ),
            'vertical-label' => esc_html__( 'Vertical Label', 'themeasy-lite' ),
            'vertical-label-filled' => esc_html__( 'Vertical Label Filled', 'themeasy-lite' ),
            'diagonal-label-filled' => esc_html__( 'Diagonal Label Filled', 'themeasy-lite' ),
          ],
        ]
      );

      $this->add_control(
        'featured_badge_background_color',
        [
          'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'separator' => 'before',
          'selectors' => [
            // The diagonal variant paints its band on the text span (the root
            // is a transparent corner clipper), so the color must land there.
            '{{WRAPPER}} .tms-pricing-table__card .tms-vertical-label:not(.tms-diagonal-label--filled)' => 'background-color: {{VALUE}}',
            '{{WRAPPER}} .tms-pricing-table__card .tms-diagonal-label--filled .tms-vertical-label__text' => 'background-color: {{VALUE}}',
          ],
          'condition' => ['featured_badge_style!' => 'vertical-label'],
        ]
      );

      $this->add_control(
        'featured_badge_text_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-pricing-table__card .tms-vertical-label .tms-vertical-label__text' => 'color: {{VALUE}}',
            '{{WRAPPER}} .tms-pricing-table__card .tms-vertical-label .tms-vertical-label__line' => 'background-color: {{VALUE}}',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'featured_badge_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-pricing-table__card .tms-vertical-label .tms-vertical-label__text',
        ]
      );

      $this->add_control(
        'featured_badge_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-pricing-table__card .tms-vertical-label' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Plan Name
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'pricing_table_plan_name_styles_section',
        [
          'label' => esc_html__( 'Plan Name', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      $this->add_control(
        'plan_name_text_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-pricing-table__card .tms-pricing-table__plan-name' => 'color: {{VALUE}}',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'plan_name_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-pricing-table__card .tms-pricing-table__plan-name',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Price
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'pricing_table_price_styles_section',
        [
          'label' => esc_html__( 'Price', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      $this->start_controls_tabs( 'price_tabs_content' );

        $this->start_controls_tab(
          'price_tab_style',
            [
              'label' => esc_html__( 'Price', 'themeasy-lite' ),
            ]
        );

          $this->add_control(
            'price_text_color',
            [
              'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-pricing-table__card .tms-pricing-table__price-wrapper .tms-pricing-table__price' => 'color: {{VALUE}}',
              ],
            ]
          );

          $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
              'name' => 'price_text_typography',
              'label' => esc_html__( 'Typography', 'themeasy-lite' ),
              'selector' => '{{WRAPPER}} .tms-pricing-table__card .tms-pricing-table__price-wrapper .tms-pricing-table__price',
            ]
          );

        $this->end_controls_tab();

        $this->start_controls_tab(
          'price_affix_tab_style',
            [
              'label' => esc_html__( 'Prefix & Suffix', 'themeasy-lite' ),
            ]
        );

          $this->add_control(
            'price_affix_text_color',
            [
              'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-pricing-table__card .tms-pricing-table__price-wrapper .tms-pricing-table__affix-text' => 'color: {{VALUE}};',
              ],
            ]
          );

          $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
              'name' => 'price_affix_text_typography',
              'label' => esc_html__( 'Typography', 'themeasy-lite' ),
              'selector' => '{{WRAPPER}} .tms-pricing-table__card .tms-pricing-table__price-wrapper .tms-pricing-table__affix-text',
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Features List
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'pricing_table_features_styles_section',
        [
          'label' => esc_html__( 'Features List', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      // ---- Text ----
      $this->add_control(
        'features_list_text_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-pricing-table__card .tms-pricing-table__checklist .tms-pricing-table__checklist-text' => 'color: {{VALUE}}',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'features_list_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-pricing-table__card .tms-pricing-table__checklist .tms-pricing-table__checklist-text',
        ]
      );

      $this->add_control(
        'features_list_width',
        [
          'label' => esc_html__( 'List Width', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', '%'],
          'range' => [
            'px' => ['min' => 0, 'max' => 500, 'step' => 1],
            '%' => ['min' => 0, 'max' => 100, 'step' => 1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-pricing-table__card .tms-pricing-table__checklist' => 'width: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      // ---- Icon ----
      $this->add_control(
        'features_list_icon_heading',
        [
          'label' => esc_html__( 'Icon', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'features_list_icon_color',
        [
          'label' => esc_html__( 'Icon Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-pricing-table__card .tms-pricing-table__checklist svg' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'features_list_icon_fill_color',
        [
          'label' => esc_html__( 'Icon Fill Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-pricing-table__card .tms-pricing-table__checklist svg' => 'fill: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'features_list_icon_stroke_color',
        [
          'label' => esc_html__( 'Icon Stroke Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-pricing-table__card .tms-pricing-table__checklist svg' => 'stroke: {{VALUE}};',
          ],
        ]
      );

      // ---- Excluded icon ([n]) ----
      // The excluded glyph carries an extra modifier class, so these three
      // outrank the generic icon colors above on specificity. They ship with
      // no default on purpose: an unauthored excluded icon keeps inheriting
      // the affirmative color, so no published page changes appearance.
      $this->add_control(
        'features_list_icon_excluded_heading',
        [
          'label' => esc_html__( 'Excluded Icon', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'features_list_icon_excluded_color',
        [
          'label' => esc_html__( 'Icon Color', 'themeasy-lite' ),
          'description' => esc_html__( 'Color for the icon of items marked as not included ([n]). Inherits the icon color above when left empty.', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-pricing-table__card .tms-pricing-table__checklist svg.tms-pricing-table__checklist-icon--excluded' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'features_list_icon_excluded_fill_color',
        [
          'label' => esc_html__( 'Icon Fill Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-pricing-table__card .tms-pricing-table__checklist svg.tms-pricing-table__checklist-icon--excluded' => 'fill: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'features_list_icon_excluded_stroke_color',
        [
          'label' => esc_html__( 'Icon Stroke Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-pricing-table__card .tms-pricing-table__checklist svg.tms-pricing-table__checklist-icon--excluded' => 'stroke: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'features_list_icon_size',
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
            '{{WRAPPER}} .tms-pricing-table__card .tms-pricing-table__checklist svg' => 'font-size: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'features_list_icon_thickness',
        [
          'label' => esc_html__( 'Icon Thickness', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => ['min' => 0.5, 'max' => 10, 'step' => 0.5],
          ],
          'default' => ['unit' => 'px', 'size' => 2],
          'selectors' => [
            '{{WRAPPER}} .tms-pricing-table__card .tms-pricing-table__checklist svg,
             {{WRAPPER}} .tms-pricing-table__card .tms-pricing-table__checklist svg *' => 'stroke-width: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Button
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'pricing_table_button_styles_section',
        [
          'label' => esc_html__( 'Button', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
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

      // ---- Colors ----
      $this->add_control(
        'button_gradient_outline_primary_color',
        [
          'label' => esc_html__( 'Primary Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'separator' => 'before',
          'selectors' => [
            '{{WRAPPER}} .tms-button.tms-button--gradient-outline' => '--tms-primary-color: {{VALUE}}',
          ],
          'condition' => ['button_style' => 'gradient-outline'],
        ]
      );

      $this->add_control(
        'button_gradient_outline_secondary_color',
        [
          'label' => esc_html__( 'Secondary Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-button.tms-button--gradient-outline' => '--tms-secondary-color: {{VALUE}}',
          ],
          'condition' => ['button_style' => 'gradient-outline'],
        ]
      );

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
              'condition' => ['button_style!' => 'gradient-outline'],
            ]
        );

          $this->add_control(
            'button_background_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-button' => 'background-color: {{VALUE}}',
                '{{WRAPPER}} .tms-button.tms-button--brutalist::after' => 'background-color: {{VALUE}}',
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
                '{{WRAPPER}} .tms-button' => 'border-color: {{VALUE}}',
                '{{WRAPPER}} .tms-button.tms-button--brutalist::before' => 'background-color: {{VALUE}}',
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
                 {{WRAPPER}} .tms-button .tms-button__text > *' => 'color: {{VALUE}}',
              ],
              'condition' => ['button_style!' => 'gradient-outline'],
            ]
          );

          $this->add_control(
            'button_icon_color',
            [
              'label' => esc_html__( 'Icon Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-button .tms-button__icon,
                 {{WRAPPER}} .tms-button svg' => 'color: {{VALUE}};',
              ],
              'condition' => ['button_style!' => 'gradient-outline'],
            ]
          );

          $this->add_control(
            'button_icon_fill_color',
            [
              'label' => esc_html__( 'Icon Fill Color', 'themeasy-lite' ),
              'description' => esc_html__( 'Applies to SVG icons only.', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-button svg' => 'fill: {{VALUE}};',
              ],
              'condition' => ['button_style!' => 'gradient-outline'],
            ]
          );

          $this->add_control(
            'button_icon_stroke_color',
            [
              'label' => esc_html__( 'Icon Stroke Color', 'themeasy-lite' ),
              'description' => esc_html__( 'Applies to SVG icons only.', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-button svg' => 'stroke: {{VALUE}};',
              ],
              'condition' => ['button_style!' => 'gradient-outline'],
            ]
          );

          $this->add_group_control(
            Group_Control_Background::get_type(),
            [
              'name' => 'button_gradient_color',
              'label' => esc_html__( 'Gradient Color', 'themeasy-lite' ),
              'types' => ['gradient'],
              'selector' => '{{WRAPPER}} .tms-button.tms-button--gradient::before',
              'condition' => ['button_style' => 'gradient'],
            ]
          );

          $this->add_control(
            'button_duocolor_color',
            [
              'label' => esc_html__( 'Icon Box Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-button .tms-button__duocolor-icon' => 'background-color: {{VALUE}}',
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
              'condition' => ['button_style!' => 'gradient-outline'],
            ]
        );

          $this->add_control(
            'button_hover_background_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-button:hover' => 'background-color: {{VALUE}}',
                '{{WRAPPER}} .tms-button.tms-button--brutalist:hover::after' => 'background-color: {{VALUE}}',
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
                '{{WRAPPER}} .tms-button:hover' => 'border-color: {{VALUE}}',
                '{{WRAPPER}} .tms-button.tms-button--brutalist:hover::before' => 'background-color: {{VALUE}}',
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
                 {{WRAPPER}} .tms-button:hover .tms-button__text > *' => 'color: {{VALUE}}',
              ],
              'condition' => ['button_style!' => 'gradient-outline'],
            ]
          );

          $this->add_control(
            'button_hover_icon_color',
            [
              'label' => esc_html__( 'Icon Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-button:hover .tms-button__icon,
                 {{WRAPPER}} .tms-button:hover svg' => 'color: {{VALUE}}',
              ],
              'condition' => ['button_style!' => 'gradient-outline'],
            ]
          );

          $this->add_group_control(
            Group_Control_Background::get_type(),
            [
              'name' => 'button_hover_gradient_color',
              'label' => esc_html__( 'Gradient Color', 'themeasy-lite' ),
              'types' => ['gradient'],
              'selector' => '{{WRAPPER}} .tms-button.tms-button--gradient::after',
              'condition' => ['button_style' => 'gradient'],
            ]
          );

          $this->add_control(
            'button_hover_duocolor_color',
            [
              'label' => esc_html__( 'Icon Box Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-button:hover .tms-button__duocolor-icon' => 'background-color: {{VALUE}}',
              ],
              'condition' => [
                'button_style' => ['duocolor', 'duocolor-outline'],
              ],
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

      // ---- Typography ----
      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'button_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-button .tms-button__text',
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
          'description' => esc_html__( 'Applies to SVG icons only.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => ['min' => 0.5, 'max' => 10, 'step' => 0.5],
          ],
          'default' => ['unit' => 'px', 'size' => 2],
          'selectors' => [
            '{{WRAPPER}} .tms-button svg,
             {{WRAPPER}} .tms-button svg *' => 'stroke-width: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      // ---- Dimensions ----
      $this->add_control(
        'button_width',
        [
          'label' => esc_html__( 'Width', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', '%'],
          'range' => [
            'px' => ['min' => 0, 'max' => 1200, 'step' => 1],
            '%' => ['min' => 0, 'max' => 100, 'step' => 1],
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

      // ---- Border ----
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
    // Style section: Price Toggle
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'pricing_table_toggle_styles_section',
        [
          'label' => esc_html__( 'Price Toggle', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => ['enable_price_toggle' => 'yes'],
        ]
    );

      // ---- Toggle labels ----
      $this->add_control(
        'toggle_labels_heading',
        [
          'label' => esc_html__( 'Toggle Labels', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
        ]
      );

      $this->add_control(
        'toggle_labels_text_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-price-toggle__field .form-label' => 'color: {{VALUE}}',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'toggle_labels_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-price-toggle__field .form-label',
        ]
      );

      // ---- Toggle button ----
      $this->add_control(
        'toggle_button_heading',
        [
          'label' => esc_html__( 'Toggle Button', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->start_controls_tabs( 'toggle_button_tabs_style' );

        $this->start_controls_tab(
          'toggle_button_normal_tab_style',
            [
              'label' => esc_html__( 'Normal', 'themeasy-lite' ),
            ]
        );

          $this->add_control(
            'toggle_button_border_color',
            [
              'label' => esc_html__( 'Border Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-price-toggle__field .form-check-input.tms-form__switch' => 'border-color: {{VALUE}}',
              ],
            ]
          );

          $this->add_control(
            'toggle_button_background_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-price-toggle__field .form-check-input.tms-form__switch' => 'background-color: {{VALUE}}',
              ],
            ]
          );

        $this->end_controls_tab();

        $this->start_controls_tab(
          'toggle_button_checked_tab_style',
            [
              'label' => esc_html__( 'Checked', 'themeasy-lite' ),
            ]
        );

          $this->add_control(
            'toggle_button_checked_border_color',
            [
              'label' => esc_html__( 'Border Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-price-toggle__field .form-check-input.tms-form__switch:checked' => 'border-color: {{VALUE}} !important',
              ],
            ]
          );

          $this->add_control(
            'toggle_button_checked_background_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-price-toggle__field .form-check-input.tms-form__switch:checked' => 'background-color: {{VALUE}} !important',
              ],
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

      // ---- Toggle badge ----
      $this->add_control(
        'toggle_badge_heading',
        [
          'label' => esc_html__( 'Toggle Badge', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->start_controls_tabs( 'toggle_badge_tabs_content' );

        $this->start_controls_tab(
          'toggle_badge_normal_tab_style',
            [
              'label' => esc_html__( 'Normal', 'themeasy-lite' ),
            ]
        );

          $this->add_control(
            'toggle_badge_background_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-price-toggle__field .tms-price-toggle__badge' => 'background-color: {{VALUE}}',
              ],
            ]
          );

          $this->add_control(
            'toggle_badge_border_color',
            [
              'label' => esc_html__( 'Border Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-price-toggle__field .tms-price-toggle__badge' => 'border-color: {{VALUE}}',
              ],
            ]
          );

          $this->add_control(
            'toggle_badge_text_color',
            [
              'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-price-toggle__field .tms-price-toggle__badge-text' => 'color: {{VALUE}}',
              ],
            ]
          );

        $this->end_controls_tab();

        $this->start_controls_tab(
          'toggle_badge_hover_tab_style',
            [
              'label' => esc_html__( 'Hover', 'themeasy-lite' ),
            ]
        );

          $this->add_control(
            'toggle_badge_hover_background_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-price-toggle__field .tms-price-toggle__badge:hover' => 'background-color: {{VALUE}}',
              ],
            ]
          );

          $this->add_control(
            'toggle_badge_hover_border_color',
            [
              'label' => esc_html__( 'Border Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-price-toggle__field .tms-price-toggle__badge:hover' => 'border-color: {{VALUE}}',
              ],
            ]
          );

          $this->add_control(
            'toggle_badge_hover_text_color',
            [
              'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-price-toggle__field .tms-price-toggle__badge:hover .tms-price-toggle__badge-text' => 'color: {{VALUE}}',
              ],
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'toggle_badge_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-price-toggle__field .tms-price-toggle__badge-text',
        ]
      );

      $this->add_responsive_control(
        'toggle_badge_vertical_offset',
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
            '{{WRAPPER}} .tms-price-toggle__field .tms-price-toggle__badge' => 'translate: 0 {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'toggle_badge_padding',
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', 'em'],
          'selectors' => [
            '{{WRAPPER}} .tms-price-toggle__field .tms-price-toggle__badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      // Border color lives in the Normal/Hover tabs above, so the group only
      // carries the style and width (its own color field would outrank :hover).
      $this->add_group_control(
        Group_Control_Border::get_type(),
        [
          'name' => 'toggle_badge_border',
          'selector' => '{{WRAPPER}} .tms-price-toggle__field .tms-price-toggle__badge',
          'exclude' => ['color'],
        ]
      );

      $this->add_responsive_control(
        'toggle_badge_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', '%'],
          'selectors' => [
            '{{WRAPPER}} .tms-price-toggle__field .tms-price-toggle__badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
   * Thousand separator of the current site locale.
   *
   * Read from WP_Locale so PHP and the toggle script always group digits the
   * same way; the value is echoed into data-thousands-sep for the script.
   *
   * @return string Separator character, or a comma when the locale has none.
   */
  private function get_thousands_separator() {
    $wp_locale = $GLOBALS['wp_locale'] ?? null;
    $separator = ( $wp_locale instanceof \WP_Locale )
      ? ( $wp_locale->number_format['thousands_sep'] ?? '' )
      : '';

    return '' !== $separator ? $separator : ',';
  }

  /**
   * Decimal separator of the current site locale.
   *
   * Needed so the editor template groups digits exactly like
   * number_format_i18n() does in render().
   *
   * @return string Separator character, or a dot when the locale has none.
   */
  private function get_decimal_separator() {
    $wp_locale = $GLOBALS['wp_locale'] ?? null;
    $separator = ( $wp_locale instanceof \WP_Locale )
      ? ( $wp_locale->number_format['decimal_point'] ?? '' )
      : '';

    return '' !== $separator ? $separator : '.';
  }

  /**
   * Formats a price for display, keeping the authored decimal places.
   *
   * Only the rendered TEXT is grouped — data-yearly / data-monthly stay raw,
   * because the toggle script reads them as machine values.
   *
   * @param mixed $value   Authored price (NUMBER control, so numeric or empty).
   * @param bool  $enabled Whether the thousand separator is switched on.
   * @return string Price as it should be printed.
   */
  private function format_price( $value, $enabled ) {
    if ( ! $enabled || '' === $value || null === $value || ! is_numeric( $value ) ) {
      return (string) $value;
    }

    // Mirror the authored precision: number_format_i18n() defaults to 0 and
    // would silently round 19.9 down to 20.
    $raw = (string) $value;
    $dot = strpos( $raw, '.' );
    $decimals = ( false === $dot ) ? 0 : strlen( $raw ) - $dot - 1;

    return number_format_i18n( (float) $value, $decimals );
  }

  /**
   * Column caps per breakpoint for the plan grid.
   *
   * A plan (big price, checklist, full-width CTA) needs roughly 260px, so the
   * generic gallery-grade caps of themeasy_bootstrap_columns() packed four plans
   * into 576px and spilled prices and buttons over their cells. A cap that
   * would strand a single-column remainder drops by one when that divides the
   * plans evenly (4 plans read 2 + 2, not 3 + 1). Mirrored in content_template().
   *
   * @param int $count Number of plans.
   * @return array<string, int> Breakpoint => column cap.
   */
  private function get_column_caps( int $count ): array {
    $caps = [
      'sm' => 2,
      'lg' => 3,
      'xl' => 4,
      'xxl' => 6,
    ];

    foreach ( $caps as $breakpoint => $cap ) {
      if ( $count > $cap && 0 !== $count % $cap && $cap > 2 && 0 === $count % ( $cap - 1 ) ) {
        $caps[ $breakpoint ] = $cap - 1;
      }
    }

    return $caps;
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
    $pricing_table = $settings['pricing_table'] ?? [];

    if ( empty( $pricing_table ) || ! is_array( $pricing_table ) ) {
      return;
    }

    $enable_price_toggle = ! empty( $settings['enable_price_toggle'] )
      && $settings['enable_price_toggle'] === 'yes';
    $enable_grid_border = ! empty( $settings['enable_grid_border'] )
      && $settings['enable_grid_border'] === 'yes';
    $format_price = ! empty( $settings['price_thousand_separator'] )
      && $settings['price_thousand_separator'] === 'yes';
    $thousands_sep = $format_price ? $this->get_thousands_separator() : '';

    // Initial period: yearly = switch ON, monthly = switch OFF. The rendered
    // price text is what the JS reads as the current period, so the switch and
    // the printed value must agree.
    $starts_yearly = ( $settings['toggle_default_period'] ?? 'yearly' ) !== 'monthly';

    // Validate SELECT values against their allowlists before they reach class names.
    $allowed_badge_styles = ['top-label-filled', 'vertical-label', 'vertical-label-filled', 'diagonal-label-filled'];
    $allowed_button_styles = [
      'simple', 'solid-fill', 'outline', 'brutalist', 'muted-fill',
      'muted-outline', 'gradient', 'gradient-outline', 'duocolor', 'duocolor-outline',
    ];

    $featured_badge_style = $settings['featured_badge_style'] ?? 'top-label-filled';
    if ( ! in_array( $featured_badge_style, $allowed_badge_styles, true ) ) {
      $featured_badge_style = 'top-label-filled';
    }

    $button_style = $settings['button_style'] ?? 'solid-fill';
    if ( ! in_array( $button_style, $allowed_button_styles, true ) ) {
      $button_style = 'solid-fill';
    }

    $button_scroll_text = Entitlement::can_use_widgets() ? ( $settings['button_scroll_text'] ?? '' ) : '';
    if ( ! in_array( $button_scroll_text, ['vertical', 'horizontal'], true ) ) {
      $button_scroll_text = '';
    }
    $is_scroll_text = '' !== $button_scroll_text;

    // ------------------------------------------------------------------------
    // Wrapper classes + attributes.
    // ------------------------------------------------------------------------
    $table_classes = ['tms-pricing-table'];
    $label_classes = ['tms-vertical-label'];
    $card_classes = ['tms-pricing-table__card', 'tms-card'];
    $button_classes = ['tms-pricing-table__button', 'tms-button'];

    // Layout.
    $columns = count( $pricing_table );
    $col_class = themeasy_bootstrap_columns( $columns, $this->get_column_caps( $columns ) );

    // Price toggle.
    if ( $enable_price_toggle ) {
      $table_classes[] = 'tms-price-toggle';
    }

    // Grid border.
    if ( $enable_grid_border ) {
      $table_classes[] = 'tms-grid-border';
      $card_classes[] = 'tms-grid-border__element';
    }

    // Featured badge style.
    switch ( $featured_badge_style ) {
      case 'top-label-filled':
        $label_classes[] = 'tms-vertical-label--top-filled';
        break;

      case 'vertical-label':
        $label_classes[] = 'tms-vertical-label--default';
        break;

      case 'vertical-label-filled':
        $label_classes[] = 'tms-vertical-label--filled';
        break;

      case 'diagonal-label-filled':
        $label_classes[] = 'tms-diagonal-label--filled';
        break;
    }

    // Button style.
    if ( $button_style ) {
      $button_classes[] = 'tms-button--' . $button_style;
    }

    // Button scroll text animation.
    if ( $button_scroll_text ) {
      $button_classes[] = 'tms-' . $button_scroll_text . '-scroll-text';
    }

    // ------------------------------------------------------------------------
    // Render attributes.
    // ------------------------------------------------------------------------
    $this->add_render_attribute( 'toggle_label_on_badge', 'class', 'tms-price-toggle__badge-text' );
    $this->add_inline_editing_attributes( 'toggle_label_on_badge', 'none' );

    $this->add_render_attribute( 'toggle_label_on', 'class', 'form-label' );
    $this->add_inline_editing_attributes( 'toggle_label_on', 'none' );

    $this->add_render_attribute( 'toggle_label_off', 'class', 'form-label' );
    $this->add_inline_editing_attributes( 'toggle_label_off', 'none' );

    // Convert class arrays to strings.
    $table_classes_str = implode( ' ', array_filter( $table_classes ) );
    $label_classes_str = implode( ' ', array_filter( $label_classes ) );

    // ------------------------------------------------------------------------
    // Output.
    // ------------------------------------------------------------------------
    ?>
    <div class="<?php echo esc_attr( $table_classes_str ); ?>">

      <?php if ( $enable_price_toggle ) : ?>
        <div class="tms-form">
          <div class="tms-price-toggle__field">
            <span <?php $this->print_render_attribute_string( 'toggle_label_off' ); ?>>
              <?php echo esc_html( $settings['toggle_label_off'] ?? '' ); ?>
            </span>

            <div class="form-check tms-form__switch">
              <input class="form-check-input tms-form__switch tms-price-toggle__button" type="checkbox"<?php checked( $starts_yearly ); ?> aria-label="<?php echo esc_attr__( 'Toggle pricing period', 'themeasy-lite' ); ?>">
            </div>

            <span <?php $this->print_render_attribute_string( 'toggle_label_on' ); ?>>
              <?php echo esc_html( $settings['toggle_label_on'] ?? '' ); ?>
            </span>

            <span class="tms-price-toggle__badge tms-tag">
              <span <?php $this->print_render_attribute_string( 'toggle_label_on_badge' ); ?>>
                <?php echo esc_html( $settings['toggle_label_on_badge'] ?? '' ); ?>
              </span>
            </span>
          </div>
        </div>
      <?php endif; ?>

      <div class="tms-grid-border__frame">
        <div class="<?php echo esc_attr( $col_class ); ?>">

          <?php foreach ( $pricing_table as $index => $item ) : ?>
            <?php
            // Per-item settings.
            $is_featured = ! empty( $item['is_featured'] ) && $item['is_featured'] === 'yes';
            $button_label = $item['button_label'] ?? '';
            $button_hidden_label = $item['button_hidden_label'] ?? '';
            $button_icon = is_array( $item['button_icon'] ?? null ) ? $item['button_icon'] : [];
            $button_icon_alignment = $item['button_icon_alignment'] ?? 'right';
            $link_type = $item['link_type'] ?? '';

            // Per-item card classes (clone base to avoid accumulation).
            $item_card_classes = $card_classes;
            $item_button_classes = $button_classes;

            if ( $is_featured ) {
              $item_card_classes[] = 'tms-pricing-table--active';
            }

            if ( 'left' === $button_icon_alignment ) {
              $item_button_classes[] = 'tms-button--icon-left';
            }

            // Link attributes.
            $link_key = 'link_' . (int) $index;

            if ( ! empty( $item['link']['url'] ) ) {
              $this->add_link_attributes( $link_key, $item['link'] );
              themeasy_add_external_link_rel( $this, $link_key, $item['link'] );
            } else {
              $this->add_render_attribute( $link_key, 'href', '#' );
              $this->add_render_attribute( $link_key, 'role', 'button' );
            }

            // Price printed on load — the chosen period, falling back to the other
            // one when the plan leaves it empty: either field may be the only
            // price a plan has, and a single price must still print.
            $initial_price = $item['yearly_price'] ?? '';
            $monthly_price = $item['monthly_price'] ?? '';

            if ( '' !== $monthly_price && ( ! $starts_yearly || '' === $initial_price ) ) {
              $initial_price = $monthly_price;
            }

            // Repeater setting keys.
            $featured_badge_text_key = $this->get_repeater_setting_key( 'featured_badge_text', 'pricing_table', $index );
            $plan_name_key = $this->get_repeater_setting_key( 'plan_name', 'pricing_table', $index );
            $price_prefix_key = $this->get_repeater_setting_key( 'price_prefix', 'pricing_table', $index );
            $yearly_price_key = $this->get_repeater_setting_key( 'yearly_price', 'pricing_table', $index );
            // Billing period: one text for both periods, unless the plan
            // authors a yearly one and the toggle is there to swap them. The
            // node edits inline the control whose text it is showing.
            $price_suffix = $item['price_suffix'] ?? '';
            $price_suffix_yearly = $item['price_suffix_yearly'] ?? '';
            $swaps_suffix = $enable_price_toggle && '' !== $price_suffix_yearly;
            $shows_yearly_suffix = $swaps_suffix && $starts_yearly;
            $initial_suffix = $shows_yearly_suffix ? $price_suffix_yearly : $price_suffix;

            $price_suffix_key = $this->get_repeater_setting_key(
              $shows_yearly_suffix ? 'price_suffix_yearly' : 'price_suffix',
              'pricing_table',
              $index
            );
            $button_label_key = $this->get_repeater_setting_key( 'button_label', 'pricing_table', $index );

            // Render attributes.
            $this->add_render_attribute( $featured_badge_text_key, 'class', 'tms-vertical-label__text' );
            $this->add_render_attribute( $plan_name_key, 'class', 'tms-pricing-table__plan-name' );
            $this->add_render_attribute(
              $price_prefix_key,
              'class',
              ['tms-pricing-table__currency', 'tms-pricing-table__affix-text']
            );
            $price_attributes = [
              'class' => 'tms-pricing-table__price',
              'data-yearly' => esc_attr( $item['yearly_price'] ?? '' ),
              'data-monthly' => esc_attr( $item['monthly_price'] ?? '' ),
            ];

            // The script reads this to group the digits it writes back, and to
            // strip the separator before parsing the rendered text.
            if ( $format_price ) {
              $price_attributes['data-thousands-sep'] = esc_attr( $thousands_sep );
            }

            $this->add_render_attribute( $yearly_price_key, $price_attributes );
            $this->add_render_attribute(
              $price_suffix_key,
              'class',
              ['tms-pricing-table__billing', 'tms-pricing-table__affix-text']
            );

            // The script swaps the text with the price, off these two values.
            if ( $swaps_suffix ) {
              $this->add_render_attribute(
                $price_suffix_key,
                [
                  'data-yearly' => esc_attr( $price_suffix_yearly ),
                  'data-monthly' => esc_attr( $price_suffix ),
                ]
              );
            }
            $this->add_render_attribute( $button_label_key, 'class', 'tms-button__text' );

            // Inline editing attributes.
            $this->add_inline_editing_attributes( $featured_badge_text_key, 'none' );
            $this->add_inline_editing_attributes( $plan_name_key, 'basic' );
            $this->add_inline_editing_attributes( $price_prefix_key, 'none' );
            $this->add_inline_editing_attributes( $yearly_price_key, 'none' );
            $this->add_inline_editing_attributes( $price_suffix_key, 'none' );
            $this->add_inline_editing_attributes( $button_label_key, 'none' );

            // Convert per-item class arrays to strings.
            $card_classes_str = implode( ' ', array_filter( $item_card_classes ) );
            $button_classes_str = implode( ' ', array_filter( $item_button_classes ) );

            // Button icon HTML.
            $button_icon_html = themeasy_render_icon_html(
              $button_icon,
              ['class' => 'tms-button__icon', 'aria-hidden' => 'true']
            );
            ?>

            <div class="tms-grid-border__col tms-card-layout-col">
              <div class="<?php echo esc_attr( $card_classes_str ); ?>">
                <div class="tms-card__wrapper">

                  <?php if ( $is_featured ) : ?>
                    <span class="<?php echo esc_attr( $label_classes_str ); ?>">
                      <?php if ( 'vertical-label' === $featured_badge_style ) : ?>
                        <span class="tms-vertical-label__line"></span>
                      <?php endif; ?>

                      <span <?php $this->print_render_attribute_string( $featured_badge_text_key ); ?>>
                        <?php echo esc_html( $item['featured_badge_text'] ?? '' ); ?>
                      </span>
                    </span>
                  <?php endif; ?>

                  <div class="tms-card__header">
                    <h3 <?php $this->print_render_attribute_string( $plan_name_key ); ?>>
                      <?php echo wp_kses_post( $item['plan_name'] ?? '' ); ?>
                    </h3>

                    <div class="tms-pricing-table__price-wrapper">
                      <span <?php $this->print_render_attribute_string( $price_prefix_key ); ?>>
                        <?php echo esc_html( $item['price_prefix'] ?? '' ); ?>
                      </span>

                      <span <?php $this->print_render_attribute_string( $yearly_price_key ); ?>>
                        <?php echo esc_html( $this->format_price( $initial_price, $format_price ) ); ?>
                      </span>

                      <span <?php $this->print_render_attribute_string( $price_suffix_key ); ?>>
                        <?php echo esc_html( $initial_suffix ); ?>
                      </span>
                    </div>
                  </div><!-- /.tms-card__header -->

                  <div class="tms-card__body">
                    <?php
                    $features_list = $item['features_list'] ?? '';

                    if ( $features_list ) {
                      $features = array_filter( array_map( 'trim', explode( '|', (string) $features_list ) ) );

                      if ( ! empty( $features ) ) {
                        echo '<ul class="tms-pricing-table__checklist">';

                        foreach ( $features as $feature ) {
                          $has_check = strpos( $feature, '[y]' ) !== false;
                          $has_x = strpos( $feature, '[n]' ) !== false;
                          $icon = '';
                          $text = trim( str_replace( ['[y]', '[n]'], '', $feature ) );

                          if ( $has_check ) {
                            $icon = themeasy_get_svg_icon( 'ty-feather', 'check', 'tms-pricing-table__checklist-icon' );
                          } elseif ( $has_x ) {
                            $icon = themeasy_get_svg_icon(
                              'ty-feather',
                              'x',
                              'tms-pricing-table__checklist-icon tms-pricing-table__checklist-icon--excluded'
                            );
                          }

                          printf(
                            '<li class="tms-pricing-table__checklist-item"><span class="tms-pricing-table__checklist-text">%1$s</span>%2$s</li>',
                            esc_html( $text ),
                            $icon // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                          );
                        }

                        echo '</ul>';
                      }
                    }
                    ?>
                  </div><!-- /.tms-card__body -->

                  <?php if ( 'button' === $link_type ) : ?>
                    <div class="tms-button-group">
                      <a <?php $this->print_render_attribute_string( $link_key ); ?> class="<?php echo esc_attr( $button_classes_str ); ?>">

                        <?php if ( $is_scroll_text ) : ?>
                          <span class="tms-scroll-text--visible">
                        <?php endif; ?>

                          <span <?php $this->print_render_attribute_string( $button_label_key ); ?>>
                            <?php echo wp_kses( $button_label, themeasy_get_kses_allowed_tags() ); ?>
                          </span>

                          <?php if ( in_array( $button_style, ['duocolor', 'duocolor-outline'], true ) ) : ?>
                            <span class="tms-button__duocolor-icon">
                              <?php echo $button_icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            </span>
                          <?php else : ?>
                            <?php echo $button_icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                          <?php endif; ?>

                        <?php if ( $is_scroll_text ) : ?>
                          </span><!-- /.tms-scroll-text--visible -->

                          <span class="tms-scroll-text--hidden">
                            <span class="tms-button__text">
                              <?php echo wp_kses( $button_hidden_label, themeasy_get_kses_allowed_tags() ); ?>
                            </span>
                            <?php echo $button_icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                          </span><!-- /.tms-scroll-text--hidden -->
                        <?php endif; ?>

                      </a><!-- /.tms-button -->
                    </div><!-- /.tms-button-group -->
                  <?php endif; ?>

                  <?php if ( 'card' === $link_type ) : ?>
                    <a <?php $this->print_render_attribute_string( $link_key ); ?> class="tms-cover-link"></a>
                  <?php endif; ?>

                </div><!-- /.tms-card__wrapper -->
              </div><!-- /.tms-pricing-table__card -->
            </div><!-- /.tms-grid-border__col -->

          <?php endforeach; ?>

        </div><!-- /.row -->
      </div><!-- /.tms-grid-border__frame -->
    </div><!-- /.tms-pricing-table -->
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
      var pricingTable = settings.pricing_table || [];

      if ( ! pricingTable.length ) {
        return;
      }

      var enablePriceToggle  = settings.enable_price_toggle === 'yes';
      var enableGridBorder   = settings.enable_grid_border === 'yes';
      var formatPrice        = settings.price_thousand_separator === 'yes';
      var thousandsSep       = <?php echo wp_json_encode( $this->get_thousands_separator() ); ?>;
      var decimalSep         = <?php echo wp_json_encode( $this->get_decimal_separator() ); ?>;

      // Mirrors format_price() in render(): group the integer part only, and
      // keep the authored decimals untouched.
      var groupPrice = function( value ) {
        if ( ! formatPrice || value === '' || value === null || value === undefined ) {
          return value;
        }

        var raw = String( value );

        if ( ! /^-?\d+(\.\d+)?$/.test( raw ) ) {
          return value;
        }

        var parts = raw.split( '.' );
        parts[0] = parts[0].replace( /\B(?=(\d{3})+(?!\d))/g, thousandsSep );

        return parts.join( decimalSep );
      };

      // Initial period: yearly = switch ON, monthly = switch OFF. The rendered
      // price text is what the JS reads as the current period, so the switch and
      // the printed value must agree.
      var startsYearly       = ( settings.toggle_default_period || 'yearly' ) !== 'monthly';
      var featuredBadgeStyle = settings.featured_badge_style || 'top-label-filled';
      var buttonStyle        = settings.button_style || 'solid-fill';
      var buttonScrollText   = motion ? ( settings.button_scroll_text || '' ) : '';
      var isScrollText       = [ 'vertical', 'horizontal' ].includes( buttonScrollText );

      // ------------------------------------------------------------------------
      // Wrapper classes + attributes.
      // ------------------------------------------------------------------------
      var tableClasses  = [ 'tms-pricing-table' ];
      var labelClasses  = [ 'tms-vertical-label' ];
      var cardClasses   = [ 'tms-pricing-table__card', 'tms-card' ];
      var buttonClasses = [ 'tms-pricing-table__button', 'tms-button' ];

      // Layout.
      // Mirrors get_column_caps() in render().
      var columns    = pricingTable.length;
      var columnCaps = { sm: 2, lg: 3, xl: 4, xxl: 6 };

      _.each( columnCaps, function( cap, breakpoint ) {
        if ( columns > cap && columns % cap !== 0 && cap > 2 && columns % ( cap - 1 ) === 0 ) {
          columnCaps[ breakpoint ] = cap - 1;
        }
      } );

      var colClass = Themeasy.bootstrapColumns( columns, columnCaps );

      // Price toggle.
      if ( enablePriceToggle ) {
        tableClasses.push( 'tms-price-toggle' );
      }

      // Grid border.
      if ( enableGridBorder ) {
        tableClasses.push( 'tms-grid-border' );
        cardClasses.push( 'tms-grid-border__element' );
      }

      // Featured badge style.
      switch ( featuredBadgeStyle ) {
        case 'top-label-filled':
          labelClasses.push( 'tms-vertical-label--top-filled' );
          break;

        case 'vertical-label':
          labelClasses.push( 'tms-vertical-label--default' );
          break;

        case 'vertical-label-filled':
          labelClasses.push( 'tms-vertical-label--filled' );
          break;

        case 'diagonal-label-filled':
          labelClasses.push( 'tms-diagonal-label--filled' );
          break;
      }

      // Button style.
      if ( buttonStyle ) {
        buttonClasses.push( 'tms-button--' + buttonStyle );
      }

      // Button scroll text animation.
      if ( buttonScrollText ) {
        buttonClasses.push( 'tms-' + buttonScrollText + '-scroll-text' );
      }

      // ------------------------------------------------------------------------
      // Render attributes.
      // ------------------------------------------------------------------------
      view.addRenderAttribute( 'toggle_label_on_badge', 'class', 'tms-price-toggle__badge-text' );
      view.addInlineEditingAttributes( 'toggle_label_on_badge', 'none' );

      view.addRenderAttribute( 'toggle_label_on', 'class', 'form-label' );
      view.addInlineEditingAttributes( 'toggle_label_on', 'none' );

      view.addRenderAttribute( 'toggle_label_off', 'class', 'form-label' );
      view.addInlineEditingAttributes( 'toggle_label_off', 'none' );

      // Convert class arrays to strings.
      var tableClassesStr = tableClasses.filter( Boolean ).join( ' ' );
      var labelClassesStr = labelClasses.filter( Boolean ).join( ' ' );
    #>

    <div class="{{ tableClassesStr }}">

      <# if ( enablePriceToggle ) { #>
        <div class="tms-form">
          <div class="tms-price-toggle__field">
            <span {{{ view.getRenderAttributeString( 'toggle_label_off' ) }}}>
              {{ settings.toggle_label_off }}
            </span>

            <div class="form-check tms-form__switch">
              <input class="form-check-input tms-form__switch tms-price-toggle__button" type="checkbox"<# if ( startsYearly ) { #> checked="checked"<# } #> aria-label="<?php echo esc_attr__( 'Toggle pricing period', 'themeasy-lite' ); ?>">
            </div>

            <span {{{ view.getRenderAttributeString( 'toggle_label_on' ) }}}>
              {{ settings.toggle_label_on }}
            </span>

            <span class="tms-price-toggle__badge tms-tag">
              <span {{{ view.getRenderAttributeString( 'toggle_label_on_badge' ) }}}>
                {{ settings.toggle_label_on_badge }}
              </span>
            </span>
          </div>
        </div>
      <# } #>

      <div class="tms-grid-border__frame">
        <div class="{{ colClass }}">

          <# _.each( pricingTable, function( item, index ) {

            // Per-item settings.
            var isFeatured          = item.is_featured === 'yes';
            var buttonLabel         = sanitizeInline( item.button_label || '' );
            var buttonHiddenLabel   = sanitizeInline( item.button_hidden_label || '' );
            var buttonIconAlignment = item.button_icon_alignment || 'right';
            var linkType            = item.link_type || '';
            var link                = item.link || {};
            // A blocked (or unset) URL falls back to the inert '#'.
            var linkUrl             = safeUrl( ( link && link.url ) || '' ) || '#';

            // Per-item card classes (clone base to avoid accumulation).
            var itemCardClasses   = cardClasses.slice();
            var itemButtonClasses = buttonClasses.slice();

            if ( isFeatured ) {
              itemCardClasses.push( 'tms-pricing-table--active' );
            }

            if ( buttonIconAlignment === 'left' ) {
              itemButtonClasses.push( 'tms-button--icon-left' );
            }

            // Button icon.
            var buttonIconMarkup = ( window.Themeasy && window.Themeasy.renderIconMarkup )
              ? window.Themeasy.renderIconMarkup( view, item.button_icon, null, { 'class': 'tms-button__icon', 'aria-hidden': 'true' } )
              : '';

            // Price printed on load — the chosen period, falling back to the other
            // one when the plan leaves it empty: either field may be the only
            // price a plan has, and a single price must still print.
            var hasMonthly   = item.monthly_price !== '' && item.monthly_price !== undefined && item.monthly_price !== null;
            var hasYearly    = item.yearly_price !== '' && item.yearly_price !== undefined && item.yearly_price !== null;
            var initialPrice = ( hasMonthly && ( ! startsYearly || ! hasYearly ) ) ? item.monthly_price : item.yearly_price;

            // Repeater setting keys.
            var featuredBadgeTextKey = view.getRepeaterSettingKey( 'featured_badge_text', 'pricing_table', index );
            var planNameKey          = view.getRepeaterSettingKey( 'plan_name', 'pricing_table', index );
            var pricePrefixKey       = view.getRepeaterSettingKey( 'price_prefix', 'pricing_table', index );
            var yearlyPriceKey       = view.getRepeaterSettingKey( 'yearly_price', 'pricing_table', index );
            // Billing period: same rule as render() — one text for both
            // periods unless the plan authors a yearly one under the toggle.
            var priceSuffix       = item.price_suffix || '';
            var priceSuffixYearly = item.price_suffix_yearly || '';
            var swapsSuffix       = enablePriceToggle && priceSuffixYearly !== '';
            var showsYearlySuffix = swapsSuffix && startsYearly;
            var initialSuffix     = showsYearlySuffix ? priceSuffixYearly : priceSuffix;

            var priceSuffixKey       = view.getRepeaterSettingKey(
              showsYearlySuffix ? 'price_suffix_yearly' : 'price_suffix', 'pricing_table', index
            );
            var buttonLabelKey       = view.getRepeaterSettingKey( 'button_label', 'pricing_table', index );

            // Render attributes.
            view.addRenderAttribute( featuredBadgeTextKey, 'class', 'tms-vertical-label__text' );
            view.addRenderAttribute( planNameKey, 'class', 'tms-pricing-table__plan-name' );
            view.addRenderAttribute( pricePrefixKey, 'class',
              [ 'tms-pricing-table__currency', 'tms-pricing-table__affix-text' ]
            );
            view.addRenderAttribute( yearlyPriceKey, {
              'class': 'tms-pricing-table__price',
              'data-yearly': item.yearly_price,
              'data-monthly': item.monthly_price
            } );

            if ( formatPrice ) {
              view.addRenderAttribute( yearlyPriceKey, 'data-thousands-sep', thousandsSep );
            }
            view.addRenderAttribute( priceSuffixKey, 'class',
              [ 'tms-pricing-table__billing', 'tms-pricing-table__affix-text' ]
            );

            if ( swapsSuffix ) {
              view.addRenderAttribute( priceSuffixKey, {
                'data-yearly': priceSuffixYearly,
                'data-monthly': priceSuffix
              } );
            }
            view.addRenderAttribute( buttonLabelKey, 'class', 'tms-button__text' );

            // Inline editing attributes.
            view.addInlineEditingAttributes( featuredBadgeTextKey, 'none' );
            view.addInlineEditingAttributes( planNameKey, 'basic' );
            view.addInlineEditingAttributes( pricePrefixKey, 'none' );
            view.addInlineEditingAttributes( yearlyPriceKey, 'none' );
            view.addInlineEditingAttributes( priceSuffixKey, 'none' );
            view.addInlineEditingAttributes( buttonLabelKey, 'none' );

            // Convert per-item class arrays to strings.
            var cardClassesStr   = itemCardClasses.filter( Boolean ).join( ' ' );
            var buttonClassesStr = itemButtonClasses.filter( Boolean ).join( ' ' );
          #>

            <div class="tms-grid-border__col tms-card-layout-col">
              <div class="{{ cardClassesStr }}">
                <div class="tms-card__wrapper">

                  <# if ( isFeatured ) { #>
                    <span class="{{ labelClassesStr }}">
                      <# if ( featuredBadgeStyle === 'vertical-label' ) { #>
                        <span class="tms-vertical-label__line"></span>
                      <# } #>
                      <span {{{ view.getRenderAttributeString( featuredBadgeTextKey ) }}}>
                        {{ item.featured_badge_text }}
                      </span>
                    </span>
                  <# } #>

                  <div class="tms-card__header">
                    <h3 {{{ view.getRenderAttributeString( planNameKey ) }}}>
                      {{{ sanitizeInline( item.plan_name || '', 'rich' ) }}}
                    </h3>

                    <div class="tms-pricing-table__price-wrapper">
                      <span {{{ view.getRenderAttributeString( pricePrefixKey ) }}}>
                        {{ item.price_prefix }}
                      </span>
                      <span {{{ view.getRenderAttributeString( yearlyPriceKey ) }}}>
                        {{ groupPrice( initialPrice ) }}
                      </span>
                      <span {{{ view.getRenderAttributeString( priceSuffixKey ) }}}>
                        {{ initialSuffix }}
                      </span>
                    </div>
                  </div><!-- /.tms-card__header -->

                  <div class="tms-card__body">
                    <#
                    var featuresList = item.features_list || '';

                    if ( featuresList.length ) {
                      var features = featuresList.split( '|' ).map( function( f ) {
                        return f.trim();
                      } );
                    #>

                      <ul class="tms-pricing-table__checklist">
                        <# _.each( features, function( feature ) {
                          feature = ( feature || '' ).trim();

                          var icon = '';
                          var text = feature.replace( /\[y\]|\[n\]/g, '' ).trim();

                          if ( feature.indexOf( '[y]' ) !== -1 ) {
                            icon = <?php echo wp_json_encode( themeasy_get_svg_icon( 'ty-feather', 'check', 'tms-pricing-table__checklist-icon' ) ); ?>;
                          } else if ( feature.indexOf( '[n]' ) !== -1 ) {
                            icon = <?php echo wp_json_encode( themeasy_get_svg_icon( 'ty-feather', 'x', 'tms-pricing-table__checklist-icon tms-pricing-table__checklist-icon--excluded' ) ); ?>;
                          }
                        #>
                          <li class="tms-pricing-table__checklist-item">
                            <span class="tms-pricing-table__checklist-text">{{ text }}</span>
                            {{{ icon }}}
                          </li>
                        <# } ); #>
                      </ul>

                    <# } #>
                  </div><!-- /.tms-card__body -->

                  <# if ( linkType === 'button' ) { #>
                    <div class="tms-button-group">
                      <a href="{{ linkUrl }}" class="{{ buttonClassesStr }}">

                        <# if ( isScrollText ) { #>
                          <span class="tms-scroll-text--visible">
                        <# } #>

                          <span {{{ view.getRenderAttributeString( buttonLabelKey ) }}}>
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
              </div><!-- /.tms-pricing-table__card -->
            </div><!-- /.tms-grid-border__col -->

          <# } ); #>

        </div><!-- /.row -->
      </div><!-- /.tms-grid-border__frame -->
    </div><!-- /.tms-pricing-table -->

    <?php
  }
}
