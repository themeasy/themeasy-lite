<?php
/**
 * Themeasy Elementor Widget: Business Hours
 *
 * Opening-hours list for local businesses: repeater rows of day label + hours
 * joined by an optional leader line, with automatic "today" highlighting
 * (wp_date('N') in the site timezone), an optional live Open/Closed status
 * badge and a footer exception note (e.g. "Holidays: closed"). The badge state
 * is server-rendered from the site clock, then re-synced client-side on every
 * minute boundary — so it flips at the exact opening/closing minute and heals
 * page-cache staleness. Time ranges are parsed from the hours text (24h, e.g.
 * "09:00 – 18:00", split shifts "09:00 – 12:00, 14:00 – 18:00" and overnight
 * "22:00 – 02:00" incl. previous-day spill all supported). The list is fully
 * usable with no JavaScript, in the editor preview and under
 * (prefers-reduced-motion: reduce) — motion is limited to a status-dot pulse
 * that reduced motion disables.
 *
 * @package Themeasy
 * @since 1.0.0
 */

namespace Themeasy\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Widget_Base;
use Themeasy\Core\Entitlement;

/**
 * Responsible for registering controls and rendering the widget.
 */
class BusinessHours extends Widget_Base {
  public function get_name() {
    return 'themeasy-business-hours';
  }

  public function get_title() {
    return esc_html__( 'Business Hours', 'themeasy-lite' );
  }

  public function get_icon() {
    return 'eicon-clock-o';
  }

  public function get_custom_help_url() {
    return 'https://themeasy.co/help-center';
  }

  public function get_categories() {
    return ['themeasy_data_category', 'themeasy_content_category'];
  }

  public function get_keywords() {
    return ['themeasy', 'business', 'hours', 'opening', 'schedule', 'open', 'closed', 'time'];
  }

  public function get_style_depends() {
    return ['themeasy-shared', 'themeasy-business-hours'];
  }

  public function get_script_depends() {
    return ['themeasy-business-hours'];
  }

  /**
   * Register widget customization controls.
   *
   * @return void
   */
  protected function register_controls() {
    // ------------------------------------------------------------------------
    // Content section: Business Hours
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'business_hours_content_section',
        [
          'label' => esc_html__( 'Business Hours', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $repeater = new Repeater();

      $repeater->add_control(
        'item_label',
        [
          'label' => esc_html__( 'Day Label', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'Monday', 'themeasy-lite' ),
          'dynamic' => [
            'active' => true,
          ],
        ]
      );

      $repeater->add_control(
        'item_days',
        [
          'label' => esc_html__( 'Weekdays Covered', 'themeasy-lite' ),
          'description' => esc_html__( 'Drives the automatic "today" highlight and the Open/Closed badge.', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::SELECT2,
          'multiple' => true,
          'options' => [
            '1' => esc_html__( 'Monday', 'themeasy-lite' ),
            '2' => esc_html__( 'Tuesday', 'themeasy-lite' ),
            '3' => esc_html__( 'Wednesday', 'themeasy-lite' ),
            '4' => esc_html__( 'Thursday', 'themeasy-lite' ),
            '5' => esc_html__( 'Friday', 'themeasy-lite' ),
            '6' => esc_html__( 'Saturday', 'themeasy-lite' ),
            '7' => esc_html__( 'Sunday', 'themeasy-lite' ),
          ],
          'default' => [],
        ]
      );

      $repeater->add_control(
        'item_hours',
        [
          'label' => esc_html__( 'Hours', 'themeasy-lite' ),
          'description' => esc_html__( 'Free text. Use 24h times so the badge can read them: 09:00 – 18:00, split shifts (09:00 – 12:00, 14:00 – 18:00), overnight (22:00 – 02:00). All day = 00:00 – 24:00.', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( '09:00 – 18:00', 'themeasy-lite' ),
          'dynamic' => [
            'active' => true,
          ],
          'condition' => [
            'item_closed!' => 'yes',
          ],
        ]
      );

      $repeater->add_control(
        'item_closed',
        [
          'label' => esc_html__( 'Closed', 'themeasy-lite' ),
          'description' => esc_html__( 'Shows the Closed label instead of hours.', 'themeasy-lite' ),
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
          'label' => esc_html__( 'Days', 'themeasy-lite' ),
          'type' => Controls_Manager::REPEATER,
          'fields' => $repeater->get_controls(),
          'default' => [
            [
              'item_label' => esc_html__( 'Monday – Friday', 'themeasy-lite' ),
              'item_days' => ['1', '2', '3', '4', '5'],
              'item_hours' => esc_html__( '09:00 – 18:00', 'themeasy-lite' ),
            ],
            [
              'item_label' => esc_html__( 'Saturday', 'themeasy-lite' ),
              'item_days' => ['6'],
              'item_hours' => esc_html__( '09:00 – 13:00', 'themeasy-lite' ),
            ],
            [
              'item_label' => esc_html__( 'Sunday', 'themeasy-lite' ),
              'item_days' => ['7'],
              'item_closed' => 'yes',
            ],
          ],
          'title_field' => '{{{ item_label }}}',
        ]
      );

      $this->add_control(
        'note_text',
        [
          'label' => esc_html__( 'Exception Note', 'themeasy-lite' ),
          'description' => esc_html__( 'Optional footer line for exceptions (e.g. Holidays: closed). Leave empty to hide.', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXT,
          'separator' => 'before',
          'default' => esc_html__( 'Holidays: closed', 'themeasy-lite' ),
          'dynamic' => [
            'active' => true,
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Header
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'business_hours_header_section',
        [
          'label' => esc_html__( 'Header', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $this->add_control(
        'title_text',
        [
          'label' => esc_html__( 'Title', 'themeasy-lite' ),
          'description' => esc_html__( 'Leave empty to hide.', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'Opening Hours', 'themeasy-lite' ),
          'dynamic' => [
            'active' => true,
          ],
        ]
      );

      $this->add_control(
        'title_html_tag',
        [
          'label' => esc_html__( 'Title HTML Tag', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'h3',
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

      $this->add_control(
        'title_icon',
        [
          'label' => esc_html__( 'Icon', 'themeasy-lite' ),
          'description' => esc_html__( 'Optional icon before the title (e.g. a clock).', 'themeasy-lite' ),
          'type' => Controls_Manager::ICONS,
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Status Badge
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'business_hours_badge_section',
        [
          'label' => esc_html__( 'Status Badge', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $this->add_control(
        'badge_show',
        [
          'label' => esc_html__( 'Show Open/Closed Badge', 'themeasy-lite' ),
          'description' => esc_html__( 'Live pill computed from today\'s hours in the site timezone; it re-checks every minute. Needs readable 24h times (e.g. 09:00 – 18:00) or the Closed toggle.', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
        ]
      );

      $this->add_control(
        'badge_open_text',
        [
          'label' => esc_html__( 'Open Text', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'Open now', 'themeasy-lite' ),
          'dynamic' => [
            'active' => true,
          ],
          'condition' => [
            'badge_show' => 'yes',
          ],
        ]
      );

      $this->add_control(
        'badge_closed_text',
        [
          'label' => esc_html__( 'Closed Text', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'Closed now', 'themeasy-lite' ),
          'dynamic' => [
            'active' => true,
          ],
          'condition' => [
            'badge_show' => 'yes',
          ],
        ]
      );

      $this->add_control(
        'badge_pulse',
        [
          'label' => esc_html__( 'Pulse While Open', 'themeasy-lite' ),
          'description' => esc_html__( 'Soft radar pulse on the status dot while open. Disabled under reduced motion.', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
          'condition' => [
            'badge_show' => 'yes',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Settings
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'business_hours_settings_section',
        [
          'label' => esc_html__( 'Settings', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $this->add_control(
        'today_badge_text',
        [
          'label' => esc_html__( 'Today Pill Text', 'themeasy-lite' ),
          'description' => esc_html__( 'Small pill shown next to today\'s day label. Leave empty to disable.', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'Today', 'themeasy-lite' ),
        ]
      );

      $this->add_control(
        'closed_label_text',
        [
          'label' => esc_html__( 'Closed Label', 'themeasy-lite' ),
          'description' => esc_html__( 'Hours-cell text for rows marked Closed.', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'Closed', 'themeasy-lite' ),
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Header
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'business_hours_header_styles_section',
        [
          'label' => esc_html__( 'Header', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_control(
        'title_color',
        [
          'label' => esc_html__( 'Title Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-business-hours__title' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'title_typography',
          'label' => esc_html__( 'Title Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-business-hours__title',
        ]
      );

      // Icon.
      $this->add_control(
        'icon_heading',
        [
          'label' => esc_html__( 'Icon', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'icon_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-business-hours__icon' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'icon_size',
        [
          'label' => esc_html__( 'Size', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'em'],
          'range' => [
            'px' => [
              'min' => 10,
              'max' => 48,
            ],
            'em' => [
              'min' => 0.5,
              'max' => 3,
              'step' => 0.05,
            ],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-business-hours__icon' => 'font-size: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'icon_gap',
        [
          'label' => esc_html__( 'Spacing', 'themeasy-lite' ),
          'description' => esc_html__( 'Gap between the icon and the title.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'em'],
          'range' => [
            'px' => [
              'min' => 0,
              'max' => 32,
            ],
            'em' => [
              'min' => 0,
              'max' => 2,
              'step' => 0.05,
            ],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-business-hours' => '--tms-business-hours-icon-gap: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'header_spacing',
        [
          'label' => esc_html__( 'Bottom Spacing', 'themeasy-lite' ),
          'description' => esc_html__( 'Gap between the header and the list.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'separator' => 'before',
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
            '{{WRAPPER}} .tms-business-hours' => '--tms-business-hours-header-spacing: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Status Badge
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'business_hours_badge_styles_section',
        [
          'label' => esc_html__( 'Status Badge', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'badge_show' => 'yes',
          ],
        ]
      );

      // Open state.
      $this->add_control(
        'badge_open_heading',
        [
          'label' => esc_html__( 'Open', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
        ]
      );

      $this->add_control(
        'badge_open_background_color',
        [
          'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-business-hours' => '--tms-business-hours-open-bg: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'badge_open_text_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'description' => esc_html__( 'Also tints the status dot and its pulse.', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-business-hours' => '--tms-business-hours-open-color: {{VALUE}};',
          ],
        ]
      );

      // Closed state.
      $this->add_control(
        'badge_closed_heading',
        [
          'label' => esc_html__( 'Closed', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'badge_closed_background_color',
        [
          'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-business-hours' => '--tms-business-hours-closed-bg: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'badge_closed_text_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-business-hours' => '--tms-business-hours-closed-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'badge_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-business-hours__badge',
          'separator' => 'before',
        ]
      );

      $this->add_responsive_control(
        'badge_padding',
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'em'],
          'selectors' => [
            '{{WRAPPER}} .tms-business-hours__badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'badge_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'em', '%'],
          'selectors' => [
            '{{WRAPPER}} .tms-business-hours__badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: List
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'business_hours_list_styles_section',
        [
          'label' => esc_html__( 'List', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_responsive_control(
        'row_gap',
        [
          'label' => esc_html__( 'Row Spacing', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['rem', 'px'],
          'range' => [
            'rem' => [
              'min' => 0,
              'max' => 4,
              'step' => 0.1,
            ],
            'px' => [
              'min' => 0,
              'max' => 64,
              'step' => 1,
            ],
          ],
          'default' => [
            'unit' => 'rem',
            'size' => 0.875,
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-business-hours' => '--tms-business-hours-row-gap: {{SIZE}}{{UNIT}};',
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
          'description' => esc_html__( 'Thin line between rows, centered in the row spacing.', 'themeasy-lite' ),
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
            '{{WRAPPER}} .tms-business-hours' => '--tms-business-hours-divider-style: {{VALUE}};',
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
            '{{WRAPPER}} .tms-business-hours' => '--tms-business-hours-divider-weight: {{SIZE}}{{UNIT}};',
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
            '{{WRAPPER}} .tms-business-hours' => '--tms-business-hours-divider-color: {{VALUE}};',
          ],
          'condition' => [
            'divider' => 'yes',
          ],
        ]
      );

      // Leader line.
      $this->add_control(
        'leader_heading',
        [
          'label' => esc_html__( 'Leader Line', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'leader_style',
        [
          'label' => esc_html__( 'Style', 'themeasy-lite' ),
          'description' => esc_html__( 'The line connecting the day to the hours.', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'none',
          'options' => [
            'none' => esc_html__( 'None', 'themeasy-lite' ),
            'dotted' => esc_html__( 'Dotted', 'themeasy-lite' ),
            'dashed' => esc_html__( 'Dashed', 'themeasy-lite' ),
            'solid' => esc_html__( 'Solid', 'themeasy-lite' ),
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-business-hours' => '--tms-business-hours-leader-style: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'leader_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-business-hours' => '--tms-business-hours-leader-color: {{VALUE}};',
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
            '{{WRAPPER}} .tms-business-hours' => '--tms-business-hours-leader-weight: {{SIZE}}{{UNIT}};',
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
          'description' => esc_html__( 'Gap between the line and the day / hours. Also keeps them apart when the line is set to None.', 'themeasy-lite' ),
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
            '{{WRAPPER}} .tms-business-hours' => '--tms-business-hours-leader-spacing: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Day
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'business_hours_day_styles_section',
        [
          'label' => esc_html__( 'Day', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_control(
        'day_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-business-hours__day' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'day_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-business-hours__day',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Hours
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'business_hours_hours_styles_section',
        [
          'label' => esc_html__( 'Hours', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_control(
        'hours_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-business-hours__hours' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'hours_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-business-hours__hours,
                         {{WRAPPER}} .tms-business-hours__closed-label',
        ]
      );

      // Closed label.
      $this->add_control(
        'closed_label_heading',
        [
          'label' => esc_html__( 'Closed Label', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'closed_label_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-business-hours__closed-label' => 'color: {{VALUE}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Today Highlight
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'business_hours_today_styles_section',
        [
          'label' => esc_html__( 'Today Highlight', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_control(
        'today_accent_color',
        [
          'label' => esc_html__( 'Accent Color', 'themeasy-lite' ),
          'description' => esc_html__( 'Drives the edge bar, the default background wash and the Today pill.', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-business-hours' => '--tms-business-hours-accent: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'today_background_color',
        [
          'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
          'description' => esc_html__( 'Replaces the default accent wash behind today\'s row.', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-business-hours' => '--tms-business-hours-today-bg: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'today_day_color',
        [
          'label' => esc_html__( 'Day Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-business-hours__row--today .tms-business-hours__day' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'today_hours_color',
        [
          'label' => esc_html__( 'Hours Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-business-hours__row--today .tms-business-hours__hours,
             {{WRAPPER}} .tms-business-hours__row--today .tms-business-hours__closed-label' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'today_padding',
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'description' => esc_html__( 'Breathing room inside the highlight. Today\'s row is the only padded one, so it reads as a raised plate.', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'separator' => 'before',
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
            '{{WRAPPER}} .tms-business-hours' => '--tms-business-hours-today-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'today_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'em', 'rem', '%'],
          'selectors' => [
            '{{WRAPPER}} .tms-business-hours' => '--tms-business-hours-today-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'today_bar_width',
        [
          'label' => esc_html__( 'Edge Bar Width', 'themeasy-lite' ),
          'description' => esc_html__( 'Accent bar on the start edge of today\'s row. Set to 0 to hide it.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => [
              'min' => 0,
              'max' => 12,
            ],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-business-hours' => '--tms-business-hours-today-bar-width: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Exception Note
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'business_hours_note_styles_section',
        [
          'label' => esc_html__( 'Exception Note', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'note_text!' => '',
          ],
        ]
      );

      $this->add_control(
        'note_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-business-hours__note' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'note_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-business-hours__note',
        ]
      );

      $this->add_responsive_control(
        'note_spacing',
        [
          'label' => esc_html__( 'Top Spacing', 'themeasy-lite' ),
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
            '{{WRAPPER}} .tms-business-hours' => '--tms-business-hours-note-spacing: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'note_align',
        [
          'label' => esc_html__( 'Alignment', 'themeasy-lite' ),
          'type' => Controls_Manager::CHOOSE,
          'options' => [
            'start' => [
              'title' => esc_html__( 'Start', 'themeasy-lite' ),
              'icon' => 'eicon-text-align-left',
            ],
            'center' => [
              'title' => esc_html__( 'Center', 'themeasy-lite' ),
              'icon' => 'eicon-text-align-center',
            ],
            'end' => [
              'title' => esc_html__( 'End', 'themeasy-lite' ),
              'icon' => 'eicon-text-align-right',
            ],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-business-hours__note' => 'text-align: {{VALUE}};',
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
          'label' => esc_html__( 'Themeasy Motion — Animation', 'themeasy-lite' ),
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
  }

  /**
   * Parse 24h time ranges out of a free-text hours string.
   *
   * Accepts "09:00 – 18:00", "9 - 18", "09h30 – 18h", "9.30 - 18.00" and
   * comma/plus-separated multiples. Returns [start, end] pairs in minutes
   * since midnight; "– 00:00" / "– 24:00" ends normalize to 1440, an end
   * below its start means the range wraps past midnight, and zero-length
   * ranges (start = end) are rejected as ambiguous.
   *
   * @param string $text Hours display text.
   * @return array<int, array{0:int, 1:int}> Parsed ranges.
   */
  private function parse_time_ranges( string $text ): array {
    if ( '' === trim( $text ) ) {
      return [];
    }

    $pattern = '/(\d{1,2})(?:[:h.](\d{2}))?\s*h?\s*[-–—~]\s*(\d{1,2})(?:[:h.](\d{2}))?\s*h?/iu';

    if ( !preg_match_all( $pattern, $text, $matches, PREG_SET_ORDER ) ) {
      return [];
    }

    $ranges = [];

    foreach ( $matches as $match ) {
      $start_hour = (int) $match[1];
      $start_minute = isset( $match[2] ) && '' !== $match[2] ? (int) $match[2] : 0;
      $end_hour = (int) $match[3];
      $end_minute = isset( $match[4] ) && '' !== $match[4] ? (int) $match[4] : 0;

      // Reject impossible clock values ("24" is only valid as "24:00").
      if ( $start_hour > 24 || $end_hour > 24 || $start_minute > 59 || $end_minute > 59 ) {
        continue;
      }

      if ( ( 24 === $start_hour && 0 !== $start_minute ) || ( 24 === $end_hour && 0 !== $end_minute ) ) {
        continue;
      }

      $start = ( $start_hour * 60 ) + $start_minute;
      $end = ( $end_hour * 60 ) + $end_minute;

      // "24:00" as a start is midnight; "00:00" / "24:00" as an end is end-of-day.
      if ( $start >= 1440 ) {
        $start = 0;
      }

      if ( 0 === $end ) {
        $end = 1440;
      }

      // Zero-length ranges (e.g. the "18:00 – 18:00" typo) are ambiguous —
      // rejecting beats "open forever from 18:00". All day is 00:00 – 24:00.
      if ( $start === $end ) {
        continue;
      }

      $ranges[] = [$start, $end];
    }

    return $ranges;
  }

  /**
   * Compute the open/closed status from the normalized rows.
   *
   * Open when the current minute falls inside any of today's ranges (an end
   * below its start counts from the start onward — the overnight case) or
   * inside the spill of an overnight range that started yesterday. "unknown"
   * means today's hours exist but carry no parseable times (e.g. "By
   * appointment") — the badge stays hidden rather than guessing.
   *
   * @param array  $rows          Normalized rows (days/ranges/closed/indeterminate).
   * @param string $today_iso     ISO-8601 weekday for today (1 = Monday).
   * @param string $yesterday_iso ISO-8601 weekday for yesterday.
   * @param int    $now_minutes   Minutes since midnight, site timezone.
   * @return string 'open' | 'closed' | 'unknown'.
   */
  private function compute_status( array $rows, string $today_iso, string $yesterday_iso, int $now_minutes ): string {
    $is_open = false;
    $any_today = false;
    $has_unknown = false;

    foreach ( $rows as $row ) {
      $covers_today = in_array( $today_iso, $row['days'], true );
      $covers_yesterday = in_array( $yesterday_iso, $row['days'], true );

      if ( $covers_today ) {
        $any_today = true;

        if ( !$row['closed'] && $row['indeterminate'] ) {
          $has_unknown = true;
        }

        foreach ( $row['ranges'] as $range ) {
          [$start, $end] = $range;

          if ( $end > $start ? ( $now_minutes >= $start && $now_minutes < $end ) : ( $now_minutes >= $start ) ) {
            $is_open = true;
          }
        }
      }

      // Overnight spill: yesterday's "22:00 – 02:00" keeps today's early hours open.
      if ( $covers_yesterday ) {
        foreach ( $row['ranges'] as $range ) {
          [$start, $end] = $range;

          if ( $end <= $start && $now_minutes < $end ) {
            $is_open = true;
          }
        }
      }
    }

    if ( $is_open ) {
      return 'open';
    }

    return ( $any_today && $has_unknown ) ? 'unknown' : 'closed';
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
    $items = is_array( $settings['items'] ?? null ) ? $settings['items'] : [];

    // Early return if no content.
    if ( empty( $items ) ) {
      return;
    }

    $title_text = $settings['title_text'] ?? '';
    $title_icon = is_array( $settings['title_icon'] ?? null ) ? $settings['title_icon'] : [];
    $note_text = $settings['note_text'] ?? '';
    $today_pill = $settings['today_badge_text'] ?? '';

    $title_tag = themeasy_sanitize_heading_tag(
      $settings['title_html_tag'] ?? 'h3',
      'h3',
      ['h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div', 'span']
    );

    $closed_label = trim( (string) ( $settings['closed_label_text'] ?? '' ) );

    if ( '' === $closed_label ) {
      $closed_label = __( 'Closed', 'themeasy-lite' );
    }

    $badge_show = 'yes' === ( $settings['badge_show'] ?? '' );
    $badge_pulse = 'yes' === ( $settings['badge_pulse'] ?? '' );
    $badge_open_text = trim( (string) ( $settings['badge_open_text'] ?? '' ) );
    $badge_closed_text = trim( (string) ( $settings['badge_closed_text'] ?? '' ) );

    if ( '' === $badge_open_text ) {
      $badge_open_text = __( 'Open now', 'themeasy-lite' );
    }

    if ( '' === $badge_closed_text ) {
      $badge_closed_text = __( 'Closed now', 'themeasy-lite' );
    }

    // ------------------------------------------------------------------------
    // Site clock (all math happens in the site timezone, never the server's).
    // ------------------------------------------------------------------------
    $today_iso = (string) wp_date( 'N' );
    $yesterday_iso = (string) ( '1' === $today_iso ? 7 : (int) $today_iso - 1 );
    $now_minutes = ( (int) wp_date( 'G' ) * 60 ) + (int) wp_date( 'i' );
    // Current UTC offset in minutes, handed to the JS re-sync so the client
    // computes "now" on the site clock (a cached page across a DST switch can
    // drift by the DST hour until the cache refreshes — acceptable).
    $utc_offset = (int) round( wp_timezone()->getOffset( new \DateTimeImmutable( 'now' ) ) / 60 );

    // ------------------------------------------------------------------------
    // Normalize rows (parse ranges once; render + status share this pass).
    // ------------------------------------------------------------------------
    $rows = [];

    foreach ( $items as $index => $item ) {
      $label = $item['item_label'] ?? '';
      $closed = 'yes' === ( $item['item_closed'] ?? '' );
      $hours = $closed ? '' : ( $item['item_hours'] ?? '' );

      // Skip rows with no meaningful content.
      if ( '' === $label && '' === $hours && !$closed ) {
        continue;
      }

      $days = array_map( 'strval', is_array( $item['item_days'] ?? null ) ? $item['item_days'] : [] );
      $ranges = $closed ? [] : $this->parse_time_ranges( $hours );

      $rows[] = [
        'index' => $index,
        'label' => $label,
        'hours' => $hours,
        'closed' => $closed,
        'days' => $days,
        'ranges' => $ranges,
        'indeterminate' => !$closed && empty( $ranges ) && '' !== trim( $hours ),
        'is_today' => in_array( $today_iso, $days, true ),
      ];
    }

    if ( empty( $rows ) ) {
      return;
    }

    $status = $this->compute_status( $rows, $today_iso, $yesterday_iso, $now_minutes );

    // ------------------------------------------------------------------------
    // Wrapper classes + attributes.
    // ------------------------------------------------------------------------
    $wrapper_classes = ['tms-business-hours'];
    $wrapper_atts = [];

    if ( 'yes' === ( $settings['divider'] ?? '' ) ) {
      $wrapper_classes[] = 'tms-business-hours--divider';
    }

    if ( themeasy_is_elementor_editor() ) {
      $wrapper_classes[] = 'tms-business-hours--static';
    }

    // The JS re-sync recomputes "today" + the badge on the site clock.
    $wrapper_atts['data-utc-offset'] = (string) $utc_offset;

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

    $wrapper_classes_output = implode( ' ', array_filter( $wrapper_classes ) );
    $wrapper_atts_output = themeasy_html_attributes( $wrapper_atts );

    // ------------------------------------------------------------------------
    // Header pieces.
    // ------------------------------------------------------------------------
    $icon_html = themeasy_render_icon_html( $title_icon, ['aria-hidden' => 'true'] );
    $has_header = '' !== $title_text || '' !== $icon_html || $badge_show;

    // Badge classes + attributes (rendered hidden when status is unknown so
    // the JS re-sync can still reveal it once a parseable day comes around).
    $badge_classes = ['tms-business-hours__badge'];
    $badge_atts = [];

    if ( $badge_show ) {
      if ( 'open' === $status ) {
        $badge_classes[] = 'tms-business-hours__badge--open';
      } elseif ( 'closed' === $status ) {
        $badge_classes[] = 'tms-business-hours__badge--closed';
      }

      if ( $badge_pulse ) {
        $badge_classes[] = 'tms-business-hours__badge--pulse';
      }

      $badge_atts['role'] = 'status';
      $badge_atts['aria-live'] = 'polite';
      $badge_atts['aria-atomic'] = 'true';
      $badge_atts['data-open-text'] = $badge_open_text;
      $badge_atts['data-closed-text'] = $badge_closed_text;

      if ( 'unknown' === $status ) {
        $badge_atts['hidden'] = 'hidden';
      }
    }

    $badge_classes_output = implode( ' ', array_filter( $badge_classes ) );
    $badge_atts_output = themeasy_html_attributes( $badge_atts );

    // ------------------------------------------------------------------------
    // Render attributes.
    // ------------------------------------------------------------------------
    $this->add_render_attribute( 'title_text', 'class', 'tms-business-hours__title' );
    $this->add_inline_editing_attributes( 'title_text', 'none' );

    $this->add_render_attribute( 'note_text', 'class', 'tms-business-hours__note' );
    $this->add_inline_editing_attributes( 'note_text', 'basic' );

    // ------------------------------------------------------------------------
    // Output.
    // ------------------------------------------------------------------------
    ?>
    <div class="<?php echo esc_attr( $wrapper_classes_output ); ?>"<?php echo $wrapper_atts_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>>
      <div class="tms-business-hours__inner tms-animation__target">

        <?php if ( $has_header ) : ?>
          <div class="tms-business-hours__header">

            <?php if ( '' !== $title_text || '' !== $icon_html ) : ?>
              <div class="tms-business-hours__heading">

                <?php if ( $icon_html ) : ?>
                  <span class="tms-business-hours__icon" aria-hidden="true">
                    <?php echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>
                  </span>
                <?php endif; ?>

                <?php if ( '' !== $title_text ) : ?>
                  <<?php echo $title_tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?> <?php $this->print_render_attribute_string( 'title_text' ); ?>>
                    <?php echo esc_html( $title_text ); ?>
                  </<?php echo $title_tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>>
                <?php endif; ?>

              </div><!-- /.tms-business-hours__heading -->
            <?php endif; ?>

            <?php if ( $badge_show ) : ?>
              <span class="<?php echo esc_attr( $badge_classes_output ); ?>"<?php echo $badge_atts_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>>
                <span class="tms-business-hours__badge-dot" aria-hidden="true"></span>
                <span class="tms-business-hours__badge-label"><?php echo esc_html( 'open' === $status ? $badge_open_text : $badge_closed_text ); ?></span>
              </span>
            <?php endif; ?>

          </div><!-- /.tms-business-hours__header -->
        <?php endif; ?>

        <dl class="tms-business-hours__list">

          <?php foreach ( $rows as $row ) : ?>
            <?php
            // Row classes.
            $row_classes = ['tms-business-hours__row'];

            if ( $row['is_today'] ) {
              $row_classes[] = 'tms-business-hours__row--today';
            }

            if ( $row['closed'] ) {
              $row_classes[] = 'tms-business-hours__row--closed';
            }

            $row_classes_output = implode( ' ', array_filter( $row_classes ) );

            // Row data for the client-side re-sync (truthy strings only —
            // themeasy_html_attributes() drops empty values by design).
            $row_ranges = array_map(
              static function ( array $range ): string {
                return $range[0] . '-' . $range[1];
              },
              $row['ranges']
            );

            $row_atts = [
              'data-days' => implode( ',', $row['days'] ),
              'data-ranges' => implode( ',', $row_ranges ),
              'data-closed' => $row['closed'] ? 'true' : '',
              'data-indeterminate' => $row['indeterminate'] ? 'true' : '',
            ];

            $row_atts_output = themeasy_html_attributes( $row_atts );

            // Inline editing render attributes.
            $label_key = $this->get_repeater_setting_key( 'item_label', 'items', $row['index'] );
            $this->add_render_attribute( $label_key, 'class', 'tms-business-hours__day-label' );
            $this->add_inline_editing_attributes( $label_key, 'none' );

            $hours_key = $this->get_repeater_setting_key( 'item_hours', 'items', $row['index'] );
            $this->add_render_attribute( $hours_key, 'class', 'tms-business-hours__hours' );
            $this->add_inline_editing_attributes( $hours_key, 'none' );
            ?>

            <div class="<?php echo esc_attr( $row_classes_output ); ?>"<?php echo $row_atts_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>>

              <dt class="tms-business-hours__day">
                <span <?php $this->print_render_attribute_string( $label_key ); ?>>
                  <?php echo esc_html( $row['label'] ); ?>
                </span>

                <?php if ( '' !== $today_pill ) : ?>
                  <span class="tms-business-hours__today-pill"><?php echo esc_html( $today_pill ); ?></span>
                <?php endif; ?>
              </dt>

              <dd class="tms-business-hours__cell">
                <span class="tms-business-hours__leader" aria-hidden="true"></span>

                <?php if ( $row['closed'] ) : ?>
                  <span class="tms-business-hours__closed-label"><?php echo esc_html( $closed_label ); ?></span>
                <?php elseif ( '' !== $row['hours'] ) : ?>
                  <span <?php $this->print_render_attribute_string( $hours_key ); ?>>
                    <?php echo esc_html( $row['hours'] ); ?>
                  </span>
                <?php endif; ?>
              </dd>

            </div><!-- /.tms-business-hours__row -->
          <?php endforeach; ?>

        </dl><!-- /.tms-business-hours__list -->

        <?php if ( '' !== $note_text ) : ?>
          <p <?php $this->print_render_attribute_string( 'note_text' ); ?>>
            <?php echo wp_kses_post( $note_text ); ?>
          </p>
        <?php endif; ?>

      </div><!-- /.tms-business-hours__inner -->
    </div><!-- /.tms-business-hours -->
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

      // ------------------------------------------------------------------------
      // Settings.
      // ------------------------------------------------------------------------
      var items = settings.items || [];

      if ( ! items.length ) {
        return;
      }

      var titleText = settings.title_text || '';
      var noteText = sanitizeInline( settings.note_text || '', 'rich' );
      var todayPill = settings.today_badge_text || '';

      var allowedTitleTags = [ 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div', 'span' ];
      var titleTag = ( allowedTitleTags.indexOf( settings.title_html_tag ) !== -1 )
        ? settings.title_html_tag
        : 'h3';

      var closedLabel = ( settings.closed_label_text || '' ).trim() || '<?php echo esc_js( __( 'Closed', 'themeasy-lite' ) ); ?>';

      var badgeShow = settings.badge_show === 'yes';
      var badgePulse = settings.badge_pulse === 'yes';
      var badgeOpenText = ( settings.badge_open_text || '' ).trim() || '<?php echo esc_js( __( 'Open now', 'themeasy-lite' ) ); ?>';
      var badgeClosedText = ( settings.badge_closed_text || '' ).trim() || '<?php echo esc_js( __( 'Closed now', 'themeasy-lite' ) ); ?>';

      // ------------------------------------------------------------------------
      // Preview clock — the browser clock stands in for the site clock (close
      // enough for design time; the frontend uses wp_date + the site offset).
      // ------------------------------------------------------------------------
      var previewNow = new Date();
      var todayIso = String( ( ( previewNow.getDay() + 6 ) % 7 ) + 1 );
      var yesterdayIso = String( todayIso === '1' ? 7 : parseInt( todayIso, 10 ) - 1 );
      var nowMinutes = ( previewNow.getHours() * 60 ) + previewNow.getMinutes();

      // Mirrors PHP parse_time_ranges().
      function parseTimeRanges( text ) {
        var ranges = [];

        if ( ! text || ! text.trim() ) {
          return ranges;
        }

        var pattern = /(\d{1,2})(?:[:h.](\d{2}))?\s*h?\s*[-–—~]\s*(\d{1,2})(?:[:h.](\d{2}))?\s*h?/gi;
        var match;

        while ( ( match = pattern.exec( text ) ) !== null ) {
          var startHour = parseInt( match[1], 10 );
          var startMinute = match[2] ? parseInt( match[2], 10 ) : 0;
          var endHour = parseInt( match[3], 10 );
          var endMinute = match[4] ? parseInt( match[4], 10 ) : 0;

          if ( startHour > 24 || endHour > 24 || startMinute > 59 || endMinute > 59 ) {
            continue;
          }

          if ( ( startHour === 24 && startMinute !== 0 ) || ( endHour === 24 && endMinute !== 0 ) ) {
            continue;
          }

          var start = ( startHour * 60 ) + startMinute;
          var end = ( endHour * 60 ) + endMinute;

          if ( start >= 1440 ) {
            start = 0;
          }

          if ( end === 0 ) {
            end = 1440;
          }

          // Zero-length ranges are ambiguous — mirror PHP and reject them.
          if ( start === end ) {
            continue;
          }

          ranges.push( [ start, end ] );
        }

        return ranges;
      }

      // ------------------------------------------------------------------------
      // Normalize rows (mirrors render(): parse once, share with the status).
      // ------------------------------------------------------------------------
      var rows = [];

      _.each( items, function( item, index ) {
        var label = item.item_label || '';
        var closed = item.item_closed === 'yes';
        var hours = closed ? '' : ( item.item_hours || '' );

        // Skip rows with no meaningful content.
        if ( ! label && ! hours && ! closed ) {
          return;
        }

        var days = _.map( item.item_days || [], String );
        var ranges = closed ? [] : parseTimeRanges( hours );

        rows.push( {
          index: index,
          label: label,
          hours: hours,
          closed: closed,
          days: days,
          ranges: ranges,
          indeterminate: ! closed && ! ranges.length && !! hours.trim(),
          isToday: days.indexOf( todayIso ) !== -1
        } );
      } );

      if ( ! rows.length ) {
        return;
      }

      // Mirrors PHP compute_status().
      var isOpen = false;
      var anyToday = false;
      var hasUnknown = false;

      _.each( rows, function( row ) {
        if ( row.days.indexOf( todayIso ) !== -1 ) {
          anyToday = true;

          if ( ! row.closed && row.indeterminate ) {
            hasUnknown = true;
          }

          _.each( row.ranges, function( range ) {
            if ( range[1] > range[0] ? ( nowMinutes >= range[0] && nowMinutes < range[1] ) : ( nowMinutes >= range[0] ) ) {
              isOpen = true;
            }
          } );
        }

        // Overnight spill from yesterday.
        if ( row.days.indexOf( yesterdayIso ) !== -1 ) {
          _.each( row.ranges, function( range ) {
            if ( range[1] <= range[0] && nowMinutes < range[1] ) {
              isOpen = true;
            }
          } );
        }
      } );

      var status = isOpen ? 'open' : ( anyToday && hasUnknown ? 'unknown' : 'closed' );

      // ------------------------------------------------------------------------
      // Wrapper classes + attributes.
      // ------------------------------------------------------------------------
      var wrapperClasses = [ 'tms-business-hours', 'tms-business-hours--static' ];
      var wrapperAtts = {};

      if ( settings.divider === 'yes' ) {
        wrapperClasses.push( 'tms-business-hours--divider' );
      }

      // Parity with render(); the JS stays inert in the editor (--static).
      wrapperAtts['data-utc-offset'] = String( -previewNow.getTimezoneOffset() );

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

      var wrapperClassStr = wrapperClasses.filter( Boolean ).join( ' ' );

      // ------------------------------------------------------------------------
      // Header pieces.
      // ------------------------------------------------------------------------
      var iconMarkup = ( window.Themeasy && window.Themeasy.renderIconMarkup )
        ? window.Themeasy.renderIconMarkup( view, settings.title_icon, null, { 'aria-hidden': 'true' } )
        : '';

      var hasHeader = !! ( titleText || iconMarkup || badgeShow );

      var badgeClasses = [ 'tms-business-hours__badge' ];

      if ( status === 'open' ) {
        badgeClasses.push( 'tms-business-hours__badge--open' );
      } else if ( status === 'closed' ) {
        badgeClasses.push( 'tms-business-hours__badge--closed' );
      }

      if ( badgePulse ) {
        badgeClasses.push( 'tms-business-hours__badge--pulse' );
      }

      var badgeClassStr = badgeClasses.filter( Boolean ).join( ' ' );

      // ------------------------------------------------------------------------
      // Render attributes.
      // ------------------------------------------------------------------------
      view.addRenderAttribute( 'title_text', 'class', 'tms-business-hours__title' );
      view.addInlineEditingAttributes( 'title_text', 'none' );

      view.addRenderAttribute( 'note_text', 'class', 'tms-business-hours__note' );
      view.addInlineEditingAttributes( 'note_text', 'basic' );
    #>

    <div class="{{ wrapperClassStr }}" {{{ Themeasy.htmlAttributes( wrapperAtts ) }}}>
      <div class="tms-business-hours__inner tms-animation__target">

        <# if ( hasHeader ) { #>
          <div class="tms-business-hours__header">

            <# if ( titleText || iconMarkup ) { #>
              <div class="tms-business-hours__heading">

                <# if ( iconMarkup ) { #>
                  <span class="tms-business-hours__icon" aria-hidden="true">{{{ iconMarkup }}}</span>
                <# } #>

                <# if ( titleText ) { #>
                  <{{{ titleTag }}} {{{ view.getRenderAttributeString( 'title_text' ) }}}>
                    {{ titleText }}
                  </{{{ titleTag }}}>
                <# } #>

              </div><!-- /.tms-business-hours__heading -->
            <# } #>

            <# if ( badgeShow ) { #>
              <span class="{{ badgeClassStr }}" role="status" aria-live="polite" aria-atomic="true" data-open-text="{{ badgeOpenText }}" data-closed-text="{{ badgeClosedText }}"<# if ( status === 'unknown' ) { #> hidden<# } #>>
                <span class="tms-business-hours__badge-dot" aria-hidden="true"></span>
                <span class="tms-business-hours__badge-label">{{ status === 'open' ? badgeOpenText : badgeClosedText }}</span>
              </span>
            <# } #>

          </div><!-- /.tms-business-hours__header -->
        <# } #>

        <dl class="tms-business-hours__list">

          <# _.each( rows, function( row ) {
            // Row classes.
            var rowClasses = [ 'tms-business-hours__row' ];

            if ( row.isToday ) {
              rowClasses.push( 'tms-business-hours__row--today' );
            }

            if ( row.closed ) {
              rowClasses.push( 'tms-business-hours__row--closed' );
            }

            var rowClassStr = rowClasses.filter( Boolean ).join( ' ' );

            // Row data attributes (parity with render()).
            var rowAtts = {
              'data-days': row.days.join( ',' ),
              'data-ranges': _.map( row.ranges, function( range ) {
                return range[0] + '-' + range[1];
              } ).join( ',' ),
              'data-closed': row.closed ? 'true' : '',
              'data-indeterminate': row.indeterminate ? 'true' : ''
            };

            // Inline editing render attributes.
            var labelKey = view.getRepeaterSettingKey( 'item_label', 'items', row.index );
            view.addRenderAttribute( labelKey, 'class', 'tms-business-hours__day-label' );
            view.addInlineEditingAttributes( labelKey, 'none' );

            var hoursKey = view.getRepeaterSettingKey( 'item_hours', 'items', row.index );
            view.addRenderAttribute( hoursKey, 'class', 'tms-business-hours__hours' );
            view.addInlineEditingAttributes( hoursKey, 'none' );
          #>

            <div class="{{ rowClassStr }}" {{{ Themeasy.htmlAttributes( rowAtts ) }}}>

              <dt class="tms-business-hours__day">
                <span {{{ view.getRenderAttributeString( labelKey ) }}}>{{ row.label }}</span>

                <# if ( todayPill ) { #>
                  <span class="tms-business-hours__today-pill">{{ todayPill }}</span>
                <# } #>
              </dt>

              <dd class="tms-business-hours__cell">
                <span class="tms-business-hours__leader" aria-hidden="true"></span>

                <# if ( row.closed ) { #>
                  <span class="tms-business-hours__closed-label">{{ closedLabel }}</span>
                <# } else if ( row.hours ) { #>
                  <span {{{ view.getRenderAttributeString( hoursKey ) }}}>{{ row.hours }}</span>
                <# } #>
              </dd>

            </div><!-- /.tms-business-hours__row -->

          <# } ); #>

        </dl><!-- /.tms-business-hours__list -->

        <# if ( noteText ) { #>
          <p {{{ view.getRenderAttributeString( 'note_text' ) }}}>{{{ noteText }}}</p>
        <# } #>

      </div><!-- /.tms-business-hours__inner -->
    </div><!-- /.tms-business-hours -->

    <?php
  }
}
