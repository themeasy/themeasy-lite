<?php
/**
 * Themeasy Elementor Widget: Comparison Table
 *
 * A semantic plan-vs-feature comparison matrix. Plans are repeater-driven
 * column headers (name, price, subtitle, badge, CTA); each feature row maps its
 * positional "[y] | [n] | text" values to those columns left-to-right
 * ([y] = check, [n] = cross, empty = neutral dash, anything else = a text value).
 * Any plan column can be flagged featured to get the accent header + tinted
 * column treatment, and a feature row can become a group heading band (one
 * cell per plan, so the featured tint runs through it uninterrupted).
 *
 * Pure CSS: a sticky plan header, a pinned feature-label column with horizontal
 * scroll on narrow screens, row-hover highlighting and hover/focus tooltips all
 * work with no JavaScript. Degrades to a fully readable, accessible table in the
 * editor, with no JS and under reduced motion.
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
use Elementor\Repeater;
use Elementor\Widget_Base;

/**
 * Responsible for registering controls and rendering the widget.
 */
class ComparisonTable extends Widget_Base {
  public function get_name() {
    return 'themeasy-comparison-table';
  }

  public function get_title() {
    return esc_html__( 'Comparison Table', 'themeasy-lite' );
  }

  public function get_icon() {
    return 'eicon-table';
  }

  public function get_custom_help_url() {
    return 'https://hub.themeasy.co/docs';
  }

  public function get_categories() {
    return ['themeasy_data_category', 'themeasy_content_category'];
  }

  public function get_keywords() {
    return ['themeasy', 'comparison', 'table', 'compare', 'pricing', 'plans', 'features', 'matrix'];
  }

  public function get_style_depends() {
    return ['themeasy-shared', 'themeasy-comparison-table'];
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
    // Content section: Plans
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'comparison_table_plans_section',
        [
          'label' => esc_html__( 'Plans', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $plan_repeater = new Repeater();

      $plan_repeater->add_control(
        'plan_name',
        [
          'label' => esc_html__( 'Plan Name', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'Plan', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'e.g. Starter, Pro, Business', 'themeasy-lite' ),
        ]
      );

      $plan_repeater->add_control(
        'plan_price',
        [
          'label' => esc_html__( 'Price', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'default' => '',
          'placeholder' => esc_html__( 'e.g. $29', 'themeasy-lite' ),
        ]
      );

      $plan_repeater->add_control(
        'plan_period',
        [
          'label' => esc_html__( 'Billing Period', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'default' => '',
          'placeholder' => esc_html__( 'e.g. /month', 'themeasy-lite' ),
        ]
      );

      $plan_repeater->add_control(
        'plan_subtitle',
        [
          'label' => esc_html__( 'Subtitle', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXT,
          'default' => '',
          'placeholder' => esc_html__( 'Short line under the name', 'themeasy-lite' ),
        ]
      );

      $plan_repeater->add_control(
        'plan_featured',
        [
          'label' => esc_html__( 'Highlight This Column', 'themeasy-lite' ),
          'description' => esc_html__( 'Applies the accent treatment so this plan stands out.', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'separator' => 'before',
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => '',
        ]
      );

      $plan_repeater->add_control(
        'plan_badge',
        [
          'label' => esc_html__( 'Badge Text', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'Most Popular', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'e.g. Most Popular', 'themeasy-lite' ),
          'condition' => ['plan_featured' => 'yes'],
        ]
      );

      $plan_repeater->add_control(
        'plan_button_text',
        [
          'label' => esc_html__( 'Button Text', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'separator' => 'before',
          'default' => '',
          'placeholder' => esc_html__( 'e.g. Get Started', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
        ]
      );

      $plan_repeater->add_control(
        'plan_button_link',
        [
          'label' => esc_html__( 'Button Link', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::URL,
          'placeholder' => esc_html__( 'https://your-link.com', 'themeasy-lite' ),
          'options' => ['url', 'is_external', 'nofollow'],
          'dynamic' => ['active' => true],
          'condition' => ['plan_button_text!' => ''],
        ]
      );

      $this->add_control(
        'plans',
        [
          'label' => esc_html__( 'Plans', 'themeasy-lite' ),
          'type' => Controls_Manager::REPEATER,
          'fields' => $plan_repeater->get_controls(),
          'default' => [
            [
              'plan_name' => esc_html__( 'Starter', 'themeasy-lite' ),
              'plan_price' => '$0',
              'plan_period' => esc_html__( '/mo', 'themeasy-lite' ),
              'plan_subtitle' => esc_html__( 'For individuals', 'themeasy-lite' ),
              'plan_button_text' => esc_html__( 'Get Started', 'themeasy-lite' ),
            ],
            [
              'plan_name' => esc_html__( 'Pro', 'themeasy-lite' ),
              'plan_price' => '$29',
              'plan_period' => esc_html__( '/mo', 'themeasy-lite' ),
              'plan_subtitle' => esc_html__( 'For growing teams', 'themeasy-lite' ),
              'plan_featured' => 'yes',
              'plan_badge' => esc_html__( 'Most Popular', 'themeasy-lite' ),
              'plan_button_text' => esc_html__( 'Start Free Trial', 'themeasy-lite' ),
            ],
            [
              'plan_name' => esc_html__( 'Business', 'themeasy-lite' ),
              'plan_price' => '$79',
              'plan_period' => esc_html__( '/mo', 'themeasy-lite' ),
              'plan_subtitle' => esc_html__( 'For organizations', 'themeasy-lite' ),
              'plan_button_text' => esc_html__( 'Contact Sales', 'themeasy-lite' ),
            ],
          ],
          'title_field' => '{{ plan_name }}',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Features
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'comparison_table_features_section',
        [
          'label' => esc_html__( 'Features', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $feature_repeater = new Repeater();

      $feature_repeater->add_control(
        'feature_is_heading',
        [
          'label' => esc_html__( 'Group Heading Row', 'themeasy-lite' ),
          'description' => esc_html__( 'Render this row as a full-width section heading instead of a feature.', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => '',
        ]
      );

      $feature_repeater->add_control(
        'feature_label',
        [
          'label' => esc_html__( 'Label', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'Feature', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'Feature or heading text', 'themeasy-lite' ),
        ]
      );

      $feature_repeater->add_control(
        'feature_tooltip',
        [
          'label' => esc_html__( 'Tooltip', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXT,
          'default' => '',
          'placeholder' => esc_html__( 'Optional help text shown on an info icon', 'themeasy-lite' ),
          'condition' => ['feature_is_heading!' => 'yes'],
        ]
      );

      $feature_repeater->add_control(
        'feature_values',
        [
          'label' => esc_html__( 'Values', 'themeasy-lite' ),
          'description' => esc_html__(
            'One value per plan, left to right, separated by "|". Use [y] for a check, [n] for a cross, or type any text (e.g. "10 GB"). Leave a slot empty for a neutral dash.',
            'themeasy-lite'
          ),
          'type' => Controls_Manager::TEXTAREA,
          'rows' => 2,
          'default' => '[y] | [y] | [y]',
          'placeholder' => '[y] | [n] | 10 GB',
          'condition' => ['feature_is_heading!' => 'yes'],
        ]
      );

      $this->add_control(
        'features',
        [
          'label' => esc_html__( 'Features', 'themeasy-lite' ),
          'type' => Controls_Manager::REPEATER,
          'fields' => $feature_repeater->get_controls(),
          'default' => [
            [
              'feature_is_heading' => 'yes',
              'feature_label' => esc_html__( 'Core', 'themeasy-lite' ),
            ],
            [
              'feature_label' => esc_html__( 'Projects', 'themeasy-lite' ),
              'feature_tooltip' => esc_html__( 'Number of active projects you can run.', 'themeasy-lite' ),
              'feature_values' => esc_html__( '3 | Unlimited | Unlimited', 'themeasy-lite' ),
            ],
            [
              'feature_label' => esc_html__( 'Storage', 'themeasy-lite' ),
              'feature_values' => '5 GB | 100 GB | 1 TB',
            ],
            [
              'feature_label' => esc_html__( 'Team Members', 'themeasy-lite' ),
              'feature_values' => esc_html__( '1 | 10 | Unlimited', 'themeasy-lite' ),
            ],
            [
              'feature_is_heading' => 'yes',
              'feature_label' => esc_html__( 'Support & Security', 'themeasy-lite' ),
            ],
            [
              'feature_label' => esc_html__( 'Priority Support', 'themeasy-lite' ),
              'feature_values' => '[n] | [y] | [y]',
            ],
            [
              'feature_label' => esc_html__( 'SSO & SAML', 'themeasy-lite' ),
              'feature_tooltip' => esc_html__( 'Single sign-on for enterprise identity providers.', 'themeasy-lite' ),
              'feature_values' => '[n] | [n] | [y]',
            ],
            [
              'feature_label' => esc_html__( 'Analytics Dashboard', 'themeasy-lite' ),
              'feature_values' => '[n] | [y] | [y]',
            ],
            [
              'feature_label' => esc_html__( 'API Access', 'themeasy-lite' ),
              'feature_values' => '[y] | [y] | [y]',
            ],
          ],
          'title_field' => '{{ feature_label }}',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Settings
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'comparison_table_settings_section',
        [
          'label' => esc_html__( 'Settings', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $this->add_control(
        'first_column_label',
        [
          'label' => esc_html__( 'Feature Column Label', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'default' => '',
          'placeholder' => esc_html__( 'e.g. Features', 'themeasy-lite' ),
        ]
      );

      $this->add_control(
        'check_icon',
        [
          'label' => esc_html__( 'Check Icon', 'themeasy-lite' ),
          'type' => Controls_Manager::ICONS,
          'separator' => 'before',
          'default' => [
            'value' => 'fas fa-check',
            'library' => 'fa-solid',
          ],
        ]
      );

      $this->add_control(
        'cross_icon',
        [
          'label' => esc_html__( 'Cross Icon', 'themeasy-lite' ),
          'type' => Controls_Manager::ICONS,
          'default' => [
            'value' => 'fas fa-times',
            'library' => 'fa-solid',
          ],
        ]
      );

      $this->add_control(
        'sticky_header',
        [
          'label' => esc_html__( 'Sticky Header', 'themeasy-lite' ),
          'description' => esc_html__( 'Keeps the plan header pinned while scrolling long tables (desktop).', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'separator' => 'before',
          'label_on' => esc_html__( 'On', 'themeasy-lite' ),
          'label_off' => esc_html__( 'Off', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
        ]
      );

      $this->add_control(
        'highlight_on_hover',
        [
          'label' => esc_html__( 'Highlight Row on Hover', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'On', 'themeasy-lite' ),
          'label_off' => esc_html__( 'Off', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Table
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'comparison_table_table_styles_section',
        [
          'label' => esc_html__( 'Table', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_control(
        'table_background',
        [
          'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
          'description' => esc_html__( 'The table surface — header, feature column and group rows follow it.', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-comparison-table' => '--tms-ct-surface: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'row_divider_color',
        [
          'label' => esc_html__( 'Row Divider Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-comparison-table__row .tms-comparison-table__cell,
             {{WRAPPER}} .tms-comparison-table__row .tms-comparison-table__feature,
             {{WRAPPER}} .tms-comparison-table__group-label,
             {{WRAPPER}} .tms-comparison-table__group-cell' => 'border-top-color: {{VALUE}};',
            // The pinned column's edge (<= 1024px) is a divider too: it reads this
            // variable, so it follows the authored colour instead of the site's.
            '{{WRAPPER}} .tms-comparison-table' => '--tms-ct-divider: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'row_hover_background',
        [
          'label' => esc_html__( 'Row Hover Background', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-comparison-table--row-hover .tms-comparison-table__row:hover .tms-comparison-table__cell,
             {{WRAPPER}} .tms-comparison-table--row-hover .tms-comparison-table__row:hover .tms-comparison-table__feature' => 'background-color: {{VALUE}};',
          ],
          'condition' => ['highlight_on_hover' => 'yes'],
        ]
      );

      $this->add_responsive_control(
        'feature_column_width',
        [
          'label' => esc_html__( 'Feature Column Width', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'separator' => 'before',
          'size_units' => ['px', '%'],
          'range' => [
            'px' => ['min' => 120, 'max' => 480, 'step' => 1],
            '%' => ['min' => 15, 'max' => 50, 'step' => 1],
          ],
          // min-width too: below 1024px the sheet floors the column at
          // clamp(150px, 42vw, 240px), which would swallow a narrower phone value.
          'selectors' => [
            '{{WRAPPER}} .tms-comparison-table__corner,
             {{WRAPPER}} .tms-comparison-table__feature' => 'width: {{SIZE}}{{UNIT}}; min-width: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'column_min_width',
        [
          'label' => esc_html__( 'Column Min Width (scroll)', 'themeasy-lite' ),
          'description' => esc_html__( 'Minimum width per column before the table scrolls horizontally on narrow screens.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => ['min' => 120, 'max' => 360, 'step' => 1],
          ],
          'default' => ['unit' => 'px', 'size' => 180],
          'selectors' => [
            '{{WRAPPER}} .tms-comparison-table__table' => '--tms-ct-col-min: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'cell_padding',
        [
          'label' => esc_html__( 'Cell Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', 'em'],
          'selectors' => [
            '{{WRAPPER}} .tms-comparison-table__cell,
             {{WRAPPER}} .tms-comparison-table__feature' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            // The group band keeps its own compact vertical rhythm, but has to
            // track the columns horizontally or its heading stops lining up
            // with the feature labels underneath it.
            '{{WRAPPER}} .tms-comparison-table__group-label' => 'padding-right: {{RIGHT}}{{UNIT}}; padding-left: {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Border::get_type(),
        [
          'name' => 'table_border',
          'selector' => '{{WRAPPER}} .tms-comparison-table__scroll',
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'table_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem'],
          'selectors' => [
            // Corner-var fan-out: the table emulates its radius per corner cell
            // (overflow stays visible so the sticky header keeps working), so
            // the four values land on the frame, the table surface AND the
            // corner cells through these variables.
            '{{WRAPPER}} .tms-comparison-table' => '--tms-ct-radius-tl: {{TOP}}{{UNIT}}; --tms-ct-radius-tr: {{RIGHT}}{{UNIT}}; --tms-ct-radius-br: {{BOTTOM}}{{UNIT}}; --tms-ct-radius-bl: {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Box_Shadow::get_type(),
        [
          'name' => 'table_box_shadow',
          'selector' => '{{WRAPPER}} .tms-comparison-table__scroll',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Plan Header
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'comparison_table_header_styles_section',
        [
          'label' => esc_html__( 'Plan Header', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_control(
        'header_background',
        [
          'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
          'description' => esc_html__( 'The featured plan keeps its own background — style it under Featured Column.', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-comparison-table__plan:not(.tms-comparison-table__plan--featured),
             {{WRAPPER}} .tms-comparison-table__corner' => 'background-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'header_padding',
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', 'em'],
          'selectors' => [
            '{{WRAPPER}} .tms-comparison-table__plan-inner,
             {{WRAPPER}} .tms-comparison-table__corner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      // No default: the stylesheet anchors the header cells to the bottom.
      $this->add_responsive_control(
        'header_vertical_align',
        [
          'label' => esc_html__( 'Vertical Alignment', 'themeasy-lite' ),
          'description' => esc_html__(
            'When one plan is taller: Top lines up the plan names, Bottom (the default) the prices and buttons.',
            'themeasy-lite'
          ),
          'type' => Controls_Manager::CHOOSE,
          'options' => [
            'top' => [
              'title' => esc_html__( 'Top', 'themeasy-lite' ),
              'icon' => 'eicon-v-align-top',
            ],
            'middle' => [
              'title' => esc_html__( 'Middle', 'themeasy-lite' ),
              'icon' => 'eicon-v-align-middle',
            ],
            'bottom' => [
              'title' => esc_html__( 'Bottom', 'themeasy-lite' ),
              'icon' => 'eicon-v-align-bottom',
            ],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-comparison-table__head .tms-comparison-table__corner,
             {{WRAPPER}} .tms-comparison-table__plan' => 'vertical-align: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'plan_name_heading',
        [
          'label' => esc_html__( 'Plan Name', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'plan_name_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-comparison-table__plan-name' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'plan_name_typography',
          'selector' => '{{WRAPPER}} .tms-comparison-table__plan-name',
        ]
      );

      $this->add_control(
        'plan_price_heading',
        [
          'label' => esc_html__( 'Price', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'plan_price_color',
        [
          'label' => esc_html__( 'Price Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-comparison-table__plan-price' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'plan_price_typography',
          'selector' => '{{WRAPPER}} .tms-comparison-table__plan-price',
        ]
      );

      $this->add_control(
        'plan_period_color',
        [
          'label' => esc_html__( 'Period Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'separator' => 'before',
          'selectors' => [
            '{{WRAPPER}} .tms-comparison-table__plan-period' => 'color: {{VALUE}};',
          ],
        ]
      );

      // The period sits inside the price element, so without its own group it
      // inherits the price's face, weight and tracking at the sheet's 0.85rem.
      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'plan_period_typography',
          'selector' => '{{WRAPPER}} .tms-comparison-table__plan-period',
        ]
      );

      $this->add_control(
        'plan_subtitle_heading',
        [
          'label' => esc_html__( 'Subtitle', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'plan_subtitle_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-comparison-table__plan-subtitle' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'plan_subtitle_typography',
          'selector' => '{{WRAPPER}} .tms-comparison-table__plan-subtitle',
        ]
      );

      // The corner cell's label (Content > First Column Label): the one text of
      // the header that had no colour and no typography of its own.
      $this->add_control(
        'first_column_label_heading',
        [
          'label' => esc_html__( 'First Column Label', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
          'condition' => [ 'first_column_label!' => '' ],
        ]
      );

      $this->add_control(
        'first_column_label_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-comparison-table__corner-label' => 'color: {{VALUE}};',
          ],
          'condition' => [ 'first_column_label!' => '' ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'first_column_label_typography',
          'selector' => '{{WRAPPER}} .tms-comparison-table__corner-label',
          'condition' => [ 'first_column_label!' => '' ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Feature Label
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'comparison_table_feature_styles_section',
        [
          'label' => esc_html__( 'Feature Label', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_control(
        'feature_label_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-comparison-table__feature-label' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'feature_column_background',
        [
          'label' => esc_html__( 'Column Background', 'themeasy-lite' ),
          'description' => esc_html__( 'Give the feature column its own tone, distinct from the table surface.', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-comparison-table__feature' => 'background-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'feature_label_typography',
          'selector' => '{{WRAPPER}} .tms-comparison-table__feature-label',
        ]
      );

      $this->add_control(
        'tooltip_heading',
        [
          'label' => esc_html__( 'Tooltip', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'tooltip_icon_color',
        [
          'label' => esc_html__( 'Icon Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-comparison-table__tooltip-trigger' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'tooltip_bubble_background',
        [
          'label' => esc_html__( 'Bubble Background', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-comparison-table__tooltip-bubble' => 'background-color: {{VALUE}};',
            '{{WRAPPER}} .tms-comparison-table__tooltip-bubble::after' => 'border-top-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'tooltip_bubble_text_color',
        [
          'label' => esc_html__( 'Bubble Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-comparison-table__tooltip-bubble' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'group_heading_label',
        [
          'label' => esc_html__( 'Group Heading', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'group_label_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-comparison-table__group-label' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'group_background',
        [
          'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
          'description' => esc_html__( 'Paints the whole band — the featured column keeps its accent tint on top of it.', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            // Every cell of the band, featured column included: the featured
            // tint is an overlay layer (see the widget CSS), so the band stays
            // continuous instead of being punched out by the accent column.
            '{{WRAPPER}} .tms-comparison-table__group-label,
             {{WRAPPER}} .tms-comparison-table__group-cell' => 'background-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'group_label_typography',
          'selector' => '{{WRAPPER}} .tms-comparison-table__group-label',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Cell Values
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'comparison_table_cells_styles_section',
        [
          'label' => esc_html__( 'Cell Values', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_control(
        'value_color',
        [
          'label' => esc_html__( 'Text Value Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-comparison-table__value' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'value_typography',
          'selector' => '{{WRAPPER}} .tms-comparison-table__value',
        ]
      );

      $this->add_control(
        'icon_heading',
        [
          'label' => esc_html__( 'Icons', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'check_color',
        [
          'label' => esc_html__( 'Check Icon Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-comparison-table__icon--check' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'cross_color',
        [
          'label' => esc_html__( 'Cross Icon Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-comparison-table__icon--cross' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'icon_size',
        [
          'label' => esc_html__( 'Icon Size', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'em'],
          'range' => [
            'px' => ['min' => 10, 'max' => 48, 'step' => 1],
            'em' => ['min' => 0.5, 'max' => 3, 'step' => 0.1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-comparison-table__icon' => 'font-size: {{SIZE}}{{UNIT}};',
            '{{WRAPPER}} .tms-comparison-table__icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Button
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'comparison_table_button_styles_section',
        [
          'label' => esc_html__( 'Button', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->start_controls_tabs( 'button_style_tabs' );

        $this->start_controls_tab(
          'button_normal_tab',
          [
            'label' => esc_html__( 'Normal', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'button_background',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-comparison-table__button' => 'background-color: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            'button_text_color',
            [
              'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-comparison-table__button' => 'color: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            'button_border_color',
            [
              'label' => esc_html__( 'Border Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-comparison-table__button' => 'border-color: {{VALUE}};',
              ],
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
            'button_hover_background',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-comparison-table__button:hover,
                 {{WRAPPER}} .tms-comparison-table__button:focus-visible' => 'background-color: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            'button_hover_text_color',
            [
              'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-comparison-table__button:hover,
                 {{WRAPPER}} .tms-comparison-table__button:focus-visible' => 'color: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            'button_hover_border_color',
            [
              'label' => esc_html__( 'Border Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-comparison-table__button:hover,
                 {{WRAPPER}} .tms-comparison-table__button:focus-visible' => 'border-color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'button_typography',
          'selector' => '{{WRAPPER}} .tms-comparison-table__button',
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
            '{{WRAPPER}} .tms-comparison-table__button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'button_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-comparison-table__button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Featured Column
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'comparison_table_featured_styles_section',
        [
          'label' => esc_html__( 'Featured Column', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_control(
        'accent_color',
        [
          'label' => esc_html__( 'Accent Color', 'themeasy-lite' ),
          'description' => esc_html__( 'Drives the featured column tint, the header accent and the badge.', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-comparison-table' => '--tms-ct-accent: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'featured_header_background',
        [
          'label' => esc_html__( 'Featured Header Background', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            // Both featured backgrounds drive the overlay layer, not the cells'
            // background-color: an opaque pick still covers everything below it,
            // while a translucent one keeps compositing over the row underneath.
            '{{WRAPPER}} .tms-comparison-table' => '--tms-ct-featured-head-layer: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'featured_column_background',
        [
          'label' => esc_html__( 'Featured Column Background', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-comparison-table' => '--tms-ct-featured-layer: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'badge_heading',
        [
          'label' => esc_html__( 'Badge', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'badge_background',
        [
          'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-comparison-table__badge' => 'background-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'badge_text_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-comparison-table__badge' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'badge_typography',
          'selector' => '{{WRAPPER}} .tms-comparison-table__badge',
        ]
      );

      $this->add_responsive_control(
        'badge_padding',
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', 'em'],
          'selectors' => [
            '{{WRAPPER}} .tms-comparison-table__badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Border::get_type(),
        [
          'name' => 'badge_border',
          'selector' => '{{WRAPPER}} .tms-comparison-table__badge',
        ]
      );

      $this->add_responsive_control(
        'badge_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', '%'],
          'selectors' => [
            '{{WRAPPER}} .tms-comparison-table__badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

  }

  /**
   * Parse a feature row's positional "[y] | [n] | text" values into cells.
   *
   * Mirrored by the JS parser in content_template().
   *
   * @param string $values Raw, pipe-separated values string.
   * @return array<int, array{type: string, text: string}>
   */
  protected function parse_feature_cells( string $values ): array {
    $cells = [];

    if ( '' === trim( $values ) ) {
      return $cells;
    }

    foreach ( explode( '|', $values ) as $part ) {
      $token = trim( $part );
      $lower = strtolower( $token );

      if ( '[y]' === $lower ) {
        $cells[] = ['type' => 'check', 'text' => ''];
      } elseif ( '[n]' === $lower ) {
        $cells[] = ['type' => 'cross', 'text' => ''];
      } elseif ( '' === $token ) {
        $cells[] = ['type' => 'empty', 'text' => ''];
      } else {
        $cells[] = ['type' => 'text', 'text' => $token];
      }
    }

    return $cells;
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
    $plans = $settings['plans'] ?? [];
    $features = $settings['features'] ?? [];

    // Early return if no content.
    if ( empty( $plans ) || !is_array( $plans ) ) {
      return;
    }

    $first_column_label = $settings['first_column_label'] ?? '';
    $sticky_header = ( $settings['sticky_header'] ?? 'yes' ) === 'yes';
    $row_hover = ( $settings['highlight_on_hover'] ?? 'yes' ) === 'yes';

    // Render the check / cross icons once and reuse them for every cell.
    $check_html = themeasy_render_icon_html(
      is_array( $settings['check_icon'] ?? null ) ? $settings['check_icon'] : [],
      ['aria-hidden' => 'true']
    );
    $cross_html = themeasy_render_icon_html(
      is_array( $settings['cross_icon'] ?? null ) ? $settings['cross_icon'] : [],
      ['aria-hidden' => 'true']
    );

    // Structural tooltip glyph (ty-feather so it exists in every build).
    $tooltip_icon = themeasy_get_svg_icon( 'ty-feather', 'info' );

    // ------------------------------------------------------------------------
    // Wrapper classes + attributes.
    // ------------------------------------------------------------------------
    $wrapper_classes = ['tms-comparison-table'];

    // Editor preview is always static so sticky/scroll never fight the canvas.
    if ( themeasy_is_elementor_editor() ) {
      $wrapper_classes[] = 'tms-comparison-table--static';
    }

    if ( $sticky_header ) {
      $wrapper_classes[] = 'tms-comparison-table--sticky';
    }

    if ( $row_hover ) {
      $wrapper_classes[] = 'tms-comparison-table--row-hover';
    }

    $wrapper_atts = [];

    $wrapper_classes_output = implode( ' ', array_filter( $wrapper_classes ) );

    // ------------------------------------------------------------------------
    // Output.
    // ------------------------------------------------------------------------
    ?>
    <div class="<?php echo esc_attr( $wrapper_classes_output ); ?>"<?php themeasy_print_html_attributes( $wrapper_atts ); ?>>
      <div class="tms-comparison-table__scroll">
        <table class="tms-comparison-table__table">

          <thead class="tms-comparison-table__head">
            <tr class="tms-comparison-table__head-row">
              <th class="tms-comparison-table__corner" scope="col">
                <?php if ( $first_column_label ) : ?>
                  <span class="tms-comparison-table__corner-label"><?php echo esc_html( $first_column_label ); ?></span>
                <?php endif; ?>
              </th>

              <?php foreach ( $plans as $index => $plan ) : ?>
                <?php
                $plan_name = $plan['plan_name'] ?? '';
                $plan_price = $plan['plan_price'] ?? '';
                $plan_period = $plan['plan_period'] ?? '';
                $plan_subtitle = $plan['plan_subtitle'] ?? '';
                $is_featured = ( $plan['plan_featured'] ?? '' ) === 'yes';
                $badge = $plan['plan_badge'] ?? '';
                $button_text = $plan['plan_button_text'] ?? '';
                $button_url = $plan['plan_button_link']['url'] ?? '';

                $plan_classes = ['tms-comparison-table__plan'];

                if ( $is_featured ) {
                  $plan_classes[] = 'tms-comparison-table__plan--featured';
                }

                // Plan name render attribute (inline editing).
                $name_key = $this->get_repeater_setting_key( 'plan_name', 'plans', $index );
                $this->add_render_attribute( $name_key, 'class', 'tms-comparison-table__plan-name' );
                $this->add_inline_editing_attributes( $name_key, 'none' );

                // CTA link attributes (target / rel handled by the helper).
                $button_key = 'plan_button_' . $index;

                if ( $button_url ) {
                  $this->add_link_attributes( $button_key, $plan['plan_button_link'] );
                  themeasy_add_external_link_rel( $this, $button_key, $plan['plan_button_link'] );
                  $this->add_render_attribute( $button_key, 'class', 'tms-comparison-table__button' );
                }
                ?>
                <th class="<?php echo esc_attr( implode( ' ', $plan_classes ) ); ?>" scope="col">
                  <div class="tms-comparison-table__plan-inner">

                    <?php if ( $is_featured && $badge ) : ?>
                      <span class="tms-comparison-table__badge tms-tag tms-tag--micro"><?php echo esc_html( $badge ); ?></span>
                    <?php endif; ?>

                    <?php if ( $plan_name ) : ?>
                      <span <?php $this->print_render_attribute_string( $name_key ); ?>><?php echo esc_html( $plan_name ); ?></span>
                    <?php endif; ?>

                    <?php if ( $plan_subtitle ) : ?>
                      <span class="tms-comparison-table__plan-subtitle"><?php echo esc_html( $plan_subtitle ); ?></span>
                    <?php endif; ?>

                    <?php if ( '' !== $plan_price || '' !== $plan_period ) : ?>
                      <span class="tms-comparison-table__plan-price">
                        <?php echo esc_html( $plan_price ); ?>
                        <?php if ( '' !== $plan_period ) : ?>
                          <span class="tms-comparison-table__plan-period"><?php echo esc_html( $plan_period ); ?></span>
                        <?php endif; ?>
                      </span>
                    <?php endif; ?>

                    <?php if ( '' !== $button_text ) : ?>
                      <?php if ( $button_url ) : ?>
                        <a <?php $this->print_render_attribute_string( $button_key ); ?>><span class="tms-comparison-table__button-text"><?php echo esc_html( $button_text ); ?></span></a>
                      <?php else : ?>
                        <span class="tms-comparison-table__button"><span class="tms-comparison-table__button-text"><?php echo esc_html( $button_text ); ?></span></span>
                      <?php endif; ?>
                    <?php endif; ?>

                  </div>
                </th>
              <?php endforeach; ?>
            </tr>
          </thead>

          <tbody class="tms-comparison-table__body">
            <?php foreach ( $features as $f_index => $feature ) : ?>
              <?php
              $is_heading = ( $feature['feature_is_heading'] ?? '' ) === 'yes';
              $label = $feature['feature_label'] ?? '';
              ?>

              <?php if ( $is_heading ) : ?>
                <tr class="tms-comparison-table__group">
                  <th class="tms-comparison-table__group-label" scope="row">
                    <?php echo esc_html( $label ); ?>
                  </th>
                  <?php foreach ( $plans as $plan ) : ?>
                    <?php
                    // Per-plan cells (instead of a colspan) let the featured
                    // column tint run continuously through group rows.
                    $group_cell_classes = ['tms-comparison-table__group-cell'];

                    if ( ( $plan['plan_featured'] ?? '' ) === 'yes' ) {
                      $group_cell_classes[] = 'tms-comparison-table__group-cell--featured';
                    }
                    ?>
                    <td class="<?php echo esc_attr( implode( ' ', $group_cell_classes ) ); ?>"></td>
                  <?php endforeach; ?>
                </tr>
              <?php else : ?>
                <?php
                $tooltip = $feature['feature_tooltip'] ?? '';
                $cells = $this->parse_feature_cells( (string) ( $feature['feature_values'] ?? '' ) );

                $label_key = $this->get_repeater_setting_key( 'feature_label', 'features', $f_index );
                $this->add_render_attribute( $label_key, 'class', 'tms-comparison-table__feature-label' );
                $this->add_inline_editing_attributes( $label_key, 'none' );
                ?>
                <tr class="tms-comparison-table__row">
                  <th class="tms-comparison-table__feature" scope="row">
                    <span <?php $this->print_render_attribute_string( $label_key ); ?>><?php echo esc_html( $label ); ?></span>

                    <?php if ( '' !== $tooltip && $tooltip_icon ) : ?>
                      <span class="tms-comparison-table__tooltip">
                        <button type="button" class="tms-comparison-table__tooltip-trigger" aria-label="<?php echo esc_attr( $tooltip ); ?>">
                          <?php echo wp_kses( $tooltip_icon, themeasy_get_icon_kses_tags() ); ?>
                        </button>
                        <span class="tms-comparison-table__tooltip-bubble" role="tooltip"><?php echo esc_html( $tooltip ); ?></span>
                      </span>
                    <?php endif; ?>
                  </th>

                  <?php foreach ( $plans as $p_index => $plan ) : ?>
                    <?php
                    $cell = $cells[$p_index] ?? ['type' => 'empty', 'text' => ''];
                    $cell_featured = ( $plan['plan_featured'] ?? '' ) === 'yes';

                    $cell_classes = ['tms-comparison-table__cell', 'tms-comparison-table__cell--' . $cell['type']];

                    if ( $cell_featured ) {
                      $cell_classes[] = 'tms-comparison-table__cell--featured';
                    }
                    ?>
                    <td class="<?php echo esc_attr( implode( ' ', $cell_classes ) ); ?>">
                      <?php if ( 'check' === $cell['type'] && $check_html ) : ?>
                        <span class="tms-comparison-table__icon tms-comparison-table__icon--check"><?php echo wp_kses( $check_html, themeasy_get_icon_kses_tags() ); ?></span>
                        <span class="tms-comparison-table__sr-only"><?php esc_html_e( 'Included', 'themeasy-lite' ); ?></span>
                      <?php elseif ( 'cross' === $cell['type'] && $cross_html ) : ?>
                        <span class="tms-comparison-table__icon tms-comparison-table__icon--cross"><?php echo wp_kses( $cross_html, themeasy_get_icon_kses_tags() ); ?></span>
                        <span class="tms-comparison-table__sr-only"><?php esc_html_e( 'Not included', 'themeasy-lite' ); ?></span>
                      <?php elseif ( 'text' === $cell['type'] ) : ?>
                        <span class="tms-comparison-table__value"><?php echo esc_html( $cell['text'] ); ?></span>
                      <?php else : ?>
                        <span class="tms-comparison-table__value tms-comparison-table__value--empty" aria-hidden="true">&mdash;</span>
                      <?php endif; ?>
                    </td>
                  <?php endforeach; ?>
                </tr>
              <?php endif; ?>

            <?php endforeach; ?>
          </tbody>

        </table>
      </div><!-- /.tms-comparison-table__scroll -->
    </div><!-- /.tms-comparison-table -->
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
      var plans = settings.plans || [];

      if ( ! plans.length ) {
        return;
      }

      var firstColumnLabel = settings.first_column_label || '';
      var stickyHeader = settings.sticky_header === 'yes';
      var rowHover = settings.highlight_on_hover === 'yes';

      // Scheme guard for every plan CTA href. The regex lives in exactly one
      // place; the fallback fails closed rather than duplicating it (a bare ^
      // anchor misses the C0 controls browsers strip before resolving a scheme).
      var safeUrl = ( window.Themeasy && window.Themeasy.safeUrl )
        ? window.Themeasy.safeUrl
        : function () { return ''; };

      // Render the check / cross icons once and reuse them for every cell.
      var checkMarkup = ( window.Themeasy && window.Themeasy.renderIconMarkup )
        ? window.Themeasy.renderIconMarkup( view, settings.check_icon, null, { 'aria-hidden': 'true' } )
        : '';
      var crossMarkup = ( window.Themeasy && window.Themeasy.renderIconMarkup )
        ? window.Themeasy.renderIconMarkup( view, settings.cross_icon, null, { 'aria-hidden': 'true' } )
        : '';

      // Mirror the server-side positional values parser.
      var parseCells = function ( values ) {
        var cells = [];

        if ( ! values || ! values.trim() ) {
          return cells;
        }

        _.each( values.split( '|' ), function ( part ) {
          var token = part.trim();
          var lower = token.toLowerCase();

          if ( lower === '[y]' ) {
            cells.push( { type: 'check', text: '' } );
          } else if ( lower === '[n]' ) {
            cells.push( { type: 'cross', text: '' } );
          } else if ( token === '' ) {
            cells.push( { type: 'empty', text: '' } );
          } else {
            cells.push( { type: 'text', text: token } );
          }
        } );

        return cells;
      };

      // ------------------------------------------------------------------------
      // Wrapper classes + attributes. The editor preview is always static.
      // ------------------------------------------------------------------------
      var wrapperClasses = [ 'tms-comparison-table', 'tms-comparison-table--static' ];
      var wrapperAtts    = {};

      if ( stickyHeader ) {
        wrapperClasses.push( 'tms-comparison-table--sticky' );
      }

      if ( rowHover ) {
        wrapperClasses.push( 'tms-comparison-table--row-hover' );
      }

      var wrapperClassStr = wrapperClasses.filter( Boolean ).join( ' ' );
      var wrapperAttsHtml = Themeasy.htmlAttributes( wrapperAtts );
    #>

    <div class="{{ wrapperClassStr }}" {{{ wrapperAttsHtml }}}>
      <div class="tms-comparison-table__scroll">
        <table class="tms-comparison-table__table">

          <thead class="tms-comparison-table__head">
            <tr class="tms-comparison-table__head-row">
              <th class="tms-comparison-table__corner" scope="col">
                <# if ( firstColumnLabel ) { #>
                  <span class="tms-comparison-table__corner-label">{{ firstColumnLabel }}</span>
                <# } #>
              </th>

              <# _.each( plans, function ( plan, index ) {
                var planName = plan.plan_name || '';
                var planPrice = plan.plan_price || '';
                var planPeriod = plan.plan_period || '';
                var planSubtitle = plan.plan_subtitle || '';
                var isFeatured = plan.plan_featured === 'yes';
                var badge = plan.plan_badge || '';
                var buttonText = plan.plan_button_text || '';
                var planButtonLink = plan.plan_button_link || {};

                // A blocked scheme yields '' — the CTA then renders as the
                // <span> below, the same as a plan with no link set.
                var buttonUrl = safeUrl( planButtonLink.url || '' );
                var buttonTarget = planButtonLink.is_external === 'on' ? '_blank' : '';
                var buttonRel = planButtonLink.nofollow === 'on' ? 'nofollow' : '';

                if ( buttonTarget ) {
                  buttonRel = ( buttonRel ? buttonRel + ' ' : '' ) + 'noopener noreferrer';
                }

                var planClasses = [ 'tms-comparison-table__plan' ];

                if ( isFeatured ) {
                  planClasses.push( 'tms-comparison-table__plan--featured' );
                }

                var nameKey = view.getRepeaterSettingKey( 'plan_name', 'plans', index );
                view.addRenderAttribute( nameKey, 'class', 'tms-comparison-table__plan-name' );
                view.addInlineEditingAttributes( nameKey, 'none' );
              #>
                <th class="{{ planClasses.join(' ') }}" scope="col">
                  <div class="tms-comparison-table__plan-inner">

                    <# if ( isFeatured && badge ) { #>
                      <span class="tms-comparison-table__badge tms-tag tms-tag--micro">{{ badge }}</span>
                    <# } #>

                    <# if ( planName ) { #>
                      <span {{{ view.getRenderAttributeString( nameKey ) }}}>{{ planName }}</span>
                    <# } #>

                    <# if ( planSubtitle ) { #>
                      <span class="tms-comparison-table__plan-subtitle">{{ planSubtitle }}</span>
                    <# } #>

                    <# if ( planPrice || planPeriod ) { #>
                      <span class="tms-comparison-table__plan-price">
                        {{ planPrice }}
                        <# if ( planPeriod ) { #>
                          <span class="tms-comparison-table__plan-period">{{ planPeriod }}</span>
                        <# } #>
                      </span>
                    <# } #>

                    <# if ( buttonText ) { #>
                      <# if ( buttonUrl ) { #>
                        <a href="{{ buttonUrl }}" class="tms-comparison-table__button"<# if ( buttonTarget ) { #> target="{{ buttonTarget }}"<# } #><# if ( buttonRel ) { #> rel="{{ buttonRel }}"<# } #>><span class="tms-comparison-table__button-text">{{ buttonText }}</span></a>
                      <# } else { #>
                        <span class="tms-comparison-table__button"><span class="tms-comparison-table__button-text">{{ buttonText }}</span></span>
                      <# } #>
                    <# } #>

                  </div>
                </th>
              <# }); #>
            </tr>
          </thead>

          <tbody class="tms-comparison-table__body">
            <# _.each( settings.features, function ( feature, fIndex ) {
              var isHeading = feature.feature_is_heading === 'yes';
              var label = feature.feature_label || '';
            #>

              <# if ( isHeading ) { #>
                <tr class="tms-comparison-table__group">
                  <th class="tms-comparison-table__group-label" scope="row">{{ label }}</th>
                  <# _.each( plans, function ( plan ) {
                    // Per-plan cells (instead of a colspan) let the featured
                    // column tint run continuously through group rows.
                    var groupCellClasses = [ 'tms-comparison-table__group-cell' ];

                    if ( plan.plan_featured === 'yes' ) {
                      groupCellClasses.push( 'tms-comparison-table__group-cell--featured' );
                    }
                  #>
                    <td class="{{ groupCellClasses.join(' ') }}"></td>
                  <# }); #>
                </tr>
              <# } else {
                var tooltip = feature.feature_tooltip || '';
                var cells = parseCells( feature.feature_values || '' );

                var labelKey = view.getRepeaterSettingKey( 'feature_label', 'features', fIndex );
                view.addRenderAttribute( labelKey, 'class', 'tms-comparison-table__feature-label' );
                view.addInlineEditingAttributes( labelKey, 'none' );
              #>
                <tr class="tms-comparison-table__row">
                  <th class="tms-comparison-table__feature" scope="row">
                    <span {{{ view.getRenderAttributeString( labelKey ) }}}>{{ label }}</span>

                    <# if ( tooltip ) { #>
                      <span class="tms-comparison-table__tooltip">
                        <button type="button" class="tms-comparison-table__tooltip-trigger" aria-label="{{ tooltip }}">
                          <?php echo wp_kses( themeasy_get_svg_icon( 'ty-feather', 'info' ), themeasy_get_icon_kses_tags() ); ?>
                        </button>
                        <span class="tms-comparison-table__tooltip-bubble" role="tooltip">{{ tooltip }}</span>
                      </span>
                    <# } #>
                  </th>

                  <# _.each( plans, function ( plan, pIndex ) {
                    var cell = cells[ pIndex ] || { type: 'empty', text: '' };
                    var cellFeatured = plan.plan_featured === 'yes';

                    var cellClasses = [ 'tms-comparison-table__cell', 'tms-comparison-table__cell--' + cell.type ];

                    if ( cellFeatured ) {
                      cellClasses.push( 'tms-comparison-table__cell--featured' );
                    }
                  #>
                    <td class="{{ cellClasses.join(' ') }}">
                      <# if ( cell.type === 'check' && checkMarkup ) { #>
                        <span class="tms-comparison-table__icon tms-comparison-table__icon--check">{{{ checkMarkup }}}</span>
                        <span class="tms-comparison-table__sr-only"><?php echo esc_js( __( 'Included', 'themeasy-lite' ) ); ?></span>
                      <# } else if ( cell.type === 'cross' && crossMarkup ) { #>
                        <span class="tms-comparison-table__icon tms-comparison-table__icon--cross">{{{ crossMarkup }}}</span>
                        <span class="tms-comparison-table__sr-only"><?php echo esc_js( __( 'Not included', 'themeasy-lite' ) ); ?></span>
                      <# } else if ( cell.type === 'text' ) { #>
                        <span class="tms-comparison-table__value">{{ cell.text }}</span>
                      <# } else { #>
                        <span class="tms-comparison-table__value tms-comparison-table__value--empty" aria-hidden="true">&mdash;</span>
                      <# } #>
                    </td>
                  <# }); #>
                </tr>
              <# } #>

            <# }); #>
          </tbody>

        </table>
      </div><!-- /.tms-comparison-table__scroll -->
    </div><!-- /.tms-comparison-table -->

    <?php
  }
}
