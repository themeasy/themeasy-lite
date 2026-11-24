<?php
/**
 * Themeasy Elementor Widget: Countdown Timer
 *
 * Countdown widget that supports two timing modes:
 *   - due_date: fixed deadline shared by all visitors (stored in site timezone).
 *   - evergreen: per-visitor offset from the first view, persisted via the
 *     selected storage driver (localStorage / sessionStorage / cookie) so the
 *     countdown resumes across reloads.
 *
 * Ships with three skins:
 *   - boxes: each unit (days/hours/minutes/seconds) in its own card.
 *   - inline: horizontal flow with values + labels + separators.
 *   - circles: radial progress rings per unit (conic-gradient driven).
 *
 * Expire behaviour is configurable (message, hide, redirect). Rendering is
 * accessible (aria-live region for expire message, role="timer" on the ticker)
 * and respects prefers-reduced-motion for digit-tick transitions.
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
use Elementor\Widget_Base;

/**
 * Responsible for registering controls and rendering the Countdown Timer widget.
 */
class CountdownTimer extends Widget_Base {
  public function get_name() {
    return 'themeasy-countdown-timer';
  }

  public function get_title() {
    return esc_html__( 'Countdown Timer', 'themeasy-lite' );
  }

  public function get_icon() {
    return 'eicon-countdown';
  }

  public function get_custom_help_url() {
    return 'https://hub.themeasy.co/docs';
  }

  public function get_categories() {
    return ['themeasy_data_category', 'themeasy_content_category'];
  }

  public function get_keywords() {
    return ['themeasy', 'countdown', 'timer', 'clock', 'deadline', 'evergreen', 'promo'];
  }

  public function get_style_depends() {
    return ['themeasy-shared', 'themeasy-countdown-timer'];
  }

  public function get_script_depends() {
    return ['themeasy-countdown-timer'];
  }

  /**
   * Register widget customization controls.
   *
   * @return void
   */
  protected function register_controls() {
    // ------------------------------------------------------------------------
    // Content section: Countdown
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'countdown_content_section',
        [
          'label' => esc_html__( 'Countdown', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
    );

      $this->add_control(
        'timer_mode',
        [
          'label' => esc_html__( 'Timer Mode', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'due_date',
          'options' => [
            'due_date' => esc_html__( 'Fixed Due Date', 'themeasy-lite' ),
            'evergreen' => esc_html__( 'Evergreen (Per Visitor)', 'themeasy-lite' ),
          ],
          'description' => esc_html__(
            'Fixed mode ticks toward a shared deadline. Evergreen mode starts counting down from the visitor\'s first view.',
            'themeasy-lite'
          ),
        ]
      );

      $this->add_control(
        'due_date',
        [
          'label' => esc_html__( 'Due Date', 'themeasy-lite' ),
          'type' => Controls_Manager::DATE_TIME,
          'default' => gmdate( 'Y-m-d H:i', time() + ( 7 * DAY_IN_SECONDS ) ),
          'description' => esc_html__( 'Interpreted in the site timezone.', 'themeasy-lite' ),
          'condition' => [
            'timer_mode' => 'due_date',
          ],
        ]
      );

      $this->add_control(
        'evergreen_days',
        [
          'label' => esc_html__( 'Days', 'themeasy-lite' ),
          'type' => Controls_Manager::NUMBER,
          'min' => 0,
          'max' => 365,
          'step' => 1,
          'default' => 0,
          'condition' => [
            'timer_mode' => 'evergreen',
          ],
        ]
      );

      $this->add_control(
        'evergreen_hours',
        [
          'label' => esc_html__( 'Hours', 'themeasy-lite' ),
          'type' => Controls_Manager::NUMBER,
          'min' => 0,
          'max' => 23,
          'step' => 1,
          'default' => 1,
          'condition' => [
            'timer_mode' => 'evergreen',
          ],
        ]
      );

      $this->add_control(
        'evergreen_minutes',
        [
          'label' => esc_html__( 'Minutes', 'themeasy-lite' ),
          'type' => Controls_Manager::NUMBER,
          'min' => 0,
          'max' => 59,
          'step' => 1,
          'default' => 0,
          'condition' => [
            'timer_mode' => 'evergreen',
          ],
        ]
      );

      $this->add_control(
        'evergreen_storage',
        [
          'label' => esc_html__( 'Persistence', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'local',
          'options' => [
            'local' => esc_html__( 'Local Storage (across sessions)', 'themeasy-lite' ),
            'session' => esc_html__( 'Session Storage (single session)', 'themeasy-lite' ),
            'cookie' => esc_html__( 'Cookie', 'themeasy-lite' ),
          ],
          'description' => esc_html__(
            'Controls how long the evergreen start time is remembered for each visitor.',
            'themeasy-lite'
          ),
          'condition' => [
            'timer_mode' => 'evergreen',
          ],
        ]
      );

      $this->add_control(
        'evergreen_storage_key',
        [
          'label' => esc_html__( 'Storage Key', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'default' => 'default',
          'description' => esc_html__(
            'Unique key so multiple evergreen timers on the same site don\'t share state.',
            'themeasy-lite'
          ),
          'condition' => [
            'timer_mode' => 'evergreen',
          ],
        ]
      );

      $this->add_control(
        'units_heading',
        [
          'label' => esc_html__( 'Units', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'show_days',
        [
          'label' => esc_html__( 'Show Days', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
        ]
      );

      $this->add_control(
        'days_label',
        [
          'label' => esc_html__( 'Days Label', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'Days', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
          'condition' => [
            'show_days' => 'yes',
          ],
        ]
      );

      $this->add_control(
        'show_hours',
        [
          'label' => esc_html__( 'Show Hours', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'separator' => 'before',
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
        ]
      );

      $this->add_control(
        'hours_label',
        [
          'label' => esc_html__( 'Hours Label', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'Hours', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
          'condition' => [
            'show_hours' => 'yes',
          ],
        ]
      );

      $this->add_control(
        'show_minutes',
        [
          'label' => esc_html__( 'Show Minutes', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'separator' => 'before',
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
        ]
      );

      $this->add_control(
        'minutes_label',
        [
          'label' => esc_html__( 'Minutes Label', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'Minutes', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
          'condition' => [
            'show_minutes' => 'yes',
          ],
        ]
      );

      $this->add_control(
        'show_seconds',
        [
          'label' => esc_html__( 'Show Seconds', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'separator' => 'before',
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
        ]
      );

      $this->add_control(
        'seconds_label',
        [
          'label' => esc_html__( 'Seconds Label', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'Seconds', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
          'condition' => [
            'show_seconds' => 'yes',
          ],
        ]
      );

      $this->add_control(
        'pad_values',
        [
          'label' => esc_html__( 'Pad Values With Zero', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'separator' => 'before',
          'description' => esc_html__( 'Always show two digits (e.g. 03 instead of 3).', 'themeasy-lite' ),
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Layout
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'countdown_layout_section',
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
          'default' => 'boxes',
          'options' => [
            'boxes' => esc_html__( 'Boxes (Card per Unit)', 'themeasy-lite' ),
            'inline' => esc_html__( 'Inline (Digits + Separators)', 'themeasy-lite' ),
            'circles' => esc_html__( 'Circles (Radial Progress)', 'themeasy-lite' ),
          ],
          'description' => esc_html__( 'Choose how the countdown units are arranged.', 'themeasy-lite' ),
        ]
      );

      $this->add_responsive_control(
        'content_alignment',
        [
          'label' => esc_html__( 'Alignment', 'themeasy-lite' ),
          'type' => Controls_Manager::CHOOSE,
          'options' => [
            'flex-start' => [
              'title' => esc_html__( 'Left', 'themeasy-lite' ),
              'icon' => 'eicon-text-align-left',
            ],
            'center' => [
              'title' => esc_html__( 'Center', 'themeasy-lite' ),
              'icon' => 'eicon-text-align-center',
            ],
            'flex-end' => [
              'title' => esc_html__( 'Right', 'themeasy-lite' ),
              'icon' => 'eicon-text-align-right',
            ],
          ],
          'default' => 'center',
          'selectors' => [
            '{{WRAPPER}} .tms-countdown-timer' => 'justify-content: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'separator_character',
        [
          'label' => esc_html__( 'Separator', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'colon',
          'options' => [
            'colon' => esc_html__( 'Colon ( : )', 'themeasy-lite' ),
            'dot' => esc_html__( 'Dot ( . )', 'themeasy-lite' ),
            'dash' => esc_html__( 'Dash ( - )', 'themeasy-lite' ),
            'none' => esc_html__( 'None', 'themeasy-lite' ),
          ],
          'condition' => [
            'skin' => 'inline',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: On Expire
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'countdown_expire_section',
        [
          'label' => esc_html__( 'On Expire', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
    );

      $this->add_control(
        'expire_action',
        [
          'label' => esc_html__( 'Action', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'message',
          'options' => [
            'message' => esc_html__( 'Show Message', 'themeasy-lite' ),
            'hide' => esc_html__( 'Hide Widget', 'themeasy-lite' ),
            'redirect' => esc_html__( 'Redirect', 'themeasy-lite' ),
          ],
        ]
      );

      $this->add_control(
        'expire_message',
        [
          'label' => esc_html__( 'Message', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXTAREA,
          'rows' => 3,
          'default' => esc_html__( 'The countdown has ended.', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
          'condition' => [
            'expire_action' => 'message',
          ],
        ]
      );

      $this->add_control(
        'expire_message_tag',
        [
          'label' => esc_html__( 'HTML Tag', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'div',
          'options' => [
            'h2' => 'H2',
            'h3' => 'H3',
            'h4' => 'H4',
            'h5' => 'H5',
            'h6' => 'H6',
            'p' => 'P',
            'div' => 'DIV',
          ],
          'condition' => [
            'expire_action' => 'message',
          ],
        ]
      );

      $this->add_control(
        'expire_redirect',
        [
          'label' => esc_html__( 'Redirect To', 'themeasy-lite' ),
          'type' => Controls_Manager::URL,
          'placeholder' => esc_html__( 'https://your-link.com', 'themeasy-lite' ),
          'options' => ['url'],
          'default' => [
            'url' => '',
            'is_external' => false,
            'nofollow' => false,
          ],
          'condition' => [
            'expire_action' => 'redirect',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Wrapper
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'countdown_wrapper_style_section',
        [
          'label' => esc_html__( 'Wrapper', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      $this->add_responsive_control(
        'unit_gap',
        [
          'label' => esc_html__( 'Gap Between Units', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem'],
          'range' => [
            'px' => ['min' => 0, 'max' => 120, 'step' => 1],
            'rem' => ['min' => 0, 'max' => 8, 'step' => 0.1],
          ],
          'default' => ['unit' => 'px', 'size' => 16],
          'selectors' => [
            '{{WRAPPER}} .tms-countdown-timer' => '--tms-countdown-gap: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'unit_min_width',
        [
          'label' => esc_html__( 'Unit Min Width', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem', 'ch'],
          'range' => [
            'px' => ['min' => 40, 'max' => 320, 'step' => 1],
            'rem' => ['min' => 2, 'max' => 20, 'step' => 0.1],
            'ch' => ['min' => 2, 'max' => 10, 'step' => 0.5],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-countdown-timer__unit' => 'min-width: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'skin' => ['boxes', 'circles'],
          ],
        ]
      );

      $this->add_responsive_control(
        'unit_padding',
        [
          'label' => esc_html__( 'Unit Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', 'em'],
          'selectors' => [
            '{{WRAPPER}} .tms-countdown-timer__unit'
              => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
          'default' => [
            'top' => 16, 'right' => 16, 'bottom' => 16, 'left' => 16, 'unit' => 'px', 'isLinked' => true,
          ],
          'condition' => [
            'skin' => 'boxes',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Background::get_type(),
        [
          'name' => 'unit_background',
          'label' => esc_html__( 'Unit Background', 'themeasy-lite' ),
          'types' => ['classic', 'gradient'],
          'selector' => '{{WRAPPER}} .tms-countdown-timer__unit',
          'condition' => [
            'skin' => 'boxes',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Border::get_type(),
        [
          'name' => 'unit_border',
          'selector' => '{{WRAPPER}} .tms-countdown-timer__unit',
          'condition' => [
            'skin' => 'boxes',
          ],
        ]
      );

      $this->add_control(
        'unit_border_radius',
        [
          'label' => esc_html__( 'Unit Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', '%'],
          'selectors' => [
            '{{WRAPPER}} .tms-countdown-timer__unit'
              => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
          'condition' => [
            'skin' => 'boxes',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Box_Shadow::get_type(),
        [
          'name' => 'unit_box_shadow',
          'label' => esc_html__( 'Unit Shadow', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-countdown-timer__unit',
          'condition' => [
            'skin' => 'boxes',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Value
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'countdown_value_style_section',
        [
          'label' => esc_html__( 'Value', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      $this->add_control(
        'value_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-countdown-timer__value' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'value_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-countdown-timer__value',
        ]
      );

      $this->add_responsive_control(
        'value_label_spacing',
        [
          'label' => esc_html__( 'Space to Label', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem', 'em'],
          'range' => [
            'px' => ['min' => 0, 'max' => 60, 'step' => 1],
            'rem' => ['min' => 0, 'max' => 4, 'step' => 0.1],
          ],
          'default' => ['unit' => 'px', 'size' => 4],
          'selectors' => [
            '{{WRAPPER}} .tms-countdown-timer__unit' => '--tms-countdown-value-gap: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Label
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'countdown_label_style_section',
        [
          'label' => esc_html__( 'Label', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      $this->add_control(
        'label_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-countdown-timer__label' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'label_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-countdown-timer__label',
        ]
      );

      $this->add_responsive_control(
        'label_text_transform',
        [
          'label' => esc_html__( 'Text Transform', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => '',
          'options' => [
            '' => esc_html__( 'Default', 'themeasy-lite' ),
            'uppercase' => esc_html__( 'Uppercase', 'themeasy-lite' ),
            'lowercase' => esc_html__( 'Lowercase', 'themeasy-lite' ),
            'capitalize' => esc_html__( 'Capitalize', 'themeasy-lite' ),
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-countdown-timer__label' => 'text-transform: {{VALUE}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Separator (inline skin)
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'countdown_separator_style_section',
        [
          'label' => esc_html__( 'Separator', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'skin' => 'inline',
            'separator_character!' => 'none',
          ],
        ]
    );

      $this->add_control(
        'separator_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-countdown-timer__separator' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'separator_size',
        [
          'label' => esc_html__( 'Size', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'em', 'rem'],
          'range' => [
            'px' => ['min' => 8, 'max' => 200, 'step' => 1],
            'em' => ['min' => 0.5, 'max' => 4, 'step' => 0.05],
            'rem' => ['min' => 0.5, 'max' => 10, 'step' => 0.1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-countdown-timer__separator' => 'font-size: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Rings (circles skin)
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'countdown_rings_style_section',
        [
          'label' => esc_html__( 'Rings', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'skin' => 'circles',
          ],
        ]
    );

      $this->add_control(
        'ring_track_color',
        [
          'label' => esc_html__( 'Track Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-countdown-timer--skin-circles' => '--tms-countdown-ring-track: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'ring_progress_color',
        [
          'label' => esc_html__( 'Progress Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-countdown-timer--skin-circles' => '--tms-countdown-ring-progress: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'ring_size',
        [
          'label' => esc_html__( 'Ring Size', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem'],
          'range' => [
            'px' => ['min' => 60, 'max' => 320, 'step' => 1],
            'rem' => ['min' => 4, 'max' => 20, 'step' => 0.1],
          ],
          'default' => ['unit' => 'px', 'size' => 120],
          'selectors' => [
            '{{WRAPPER}} .tms-countdown-timer--skin-circles' => '--tms-countdown-ring-size: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'ring_thickness',
        [
          'label' => esc_html__( 'Ring Thickness', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => ['min' => 2, 'max' => 32, 'step' => 1],
          ],
          'default' => ['unit' => 'px', 'size' => 6],
          'selectors' => [
            '{{WRAPPER}} .tms-countdown-timer--skin-circles' => '--tms-countdown-ring-thickness: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Expire Message
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'countdown_expire_style_section',
        [
          'label' => esc_html__( 'Expire Message', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'expire_action' => 'message',
          ],
        ]
    );

      $this->add_control(
        'expire_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-countdown-timer__expire' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'expire_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-countdown-timer__expire',
        ]
      );

      $this->add_responsive_control(
        'expire_alignment',
        [
          'label' => esc_html__( 'Alignment', 'themeasy-lite' ),
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
          'selectors' => [
            '{{WRAPPER}} .tms-countdown-timer__expire' => 'text-align: {{VALUE}};',
          ],
        ]
      );

    $this->end_controls_section();

  }

  /**
   * Compute the target timestamp (UTC seconds) for the due_date mode using
   * the WordPress site timezone.
   *
   * @param string $due_date_raw Raw Y-m-d H:i(:s) value from the control.
   * @return int Unix timestamp in seconds.
   */
  private function compute_due_date_timestamp( string $due_date_raw ): int {
    if ( '' === trim( $due_date_raw ) ) {
      return 0;
    }

    try {
      $tz = wp_timezone();
      $dt = new \DateTime( $due_date_raw, $tz );

      return (int) $dt->format( 'U' );
    } catch ( \Exception $e ) {
      return 0;
    }
  }

  /**
   * Build the ordered list of unit descriptors currently visible.
   *
   * @param array $settings Widget settings.
   * @return array<int, array{key:string, label:string}>
   */
  private function get_visible_units( array $settings ): array {
    $units = [];

    foreach ( ['days', 'hours', 'minutes', 'seconds'] as $key ) {
      if ( 'yes' !== ( $settings['show_' . $key] ?? '' ) ) {
        continue;
      }

      $label = $settings[$key . '_label'] ?? '';

      $units[] = [
        'key' => $key,
        'label' => is_string( $label ) ? $label : '',
      ];
    }

    return $units;
  }

  /**
   * Map the separator_character setting to its display glyph.
   *
   * @param string $choice Character key.
   * @return string Glyph (empty when no separator should be rendered).
   */
  private function resolve_separator_glyph( string $choice ): string {
    switch ( $choice ) {
      case 'dot':
        return '.';
      case 'dash':
        return '-';
      case 'none':
        return '';
      case 'colon':
      default:
        return ':';
    }
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
    $skin = $settings['skin'] ?? 'boxes';
    $separator_choice = $settings['separator_character'] ?? 'colon';
    $separator_glyph = $this->resolve_separator_glyph( $separator_choice );
    $pad_values = 'yes' === ( $settings['pad_values'] ?? 'yes' );

    $timer_mode = $settings['timer_mode'] ?? 'due_date';
    $due_date_raw = $settings['due_date'] ?? '';
    $due_ts = $this->compute_due_date_timestamp( $due_date_raw );

    $evergreen_storage = $settings['evergreen_storage'] ?? 'local';
    $evergreen_key_raw = $settings['evergreen_storage_key'] ?? 'default';
    $evergreen_key = sanitize_key( $evergreen_key_raw );

    if ( '' === $evergreen_key ) {
      $evergreen_key = 'default';
    }

    $evergreen_duration = (
      max( 0, (int) ( $settings['evergreen_days'] ?? 0 ) ) * DAY_IN_SECONDS
      + max( 0, (int) ( $settings['evergreen_hours'] ?? 0 ) ) * HOUR_IN_SECONDS
      + max( 0, (int) ( $settings['evergreen_minutes'] ?? 0 ) ) * MINUTE_IN_SECONDS
    );

    $expire_action = $settings['expire_action'] ?? 'message';
    $expire_message = $settings['expire_message'] ?? '';
    $expire_message_tag = themeasy_sanitize_heading_tag(
      $settings['expire_message_tag'] ?? 'div',
      'div',
      ['h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div']
    );
    $expire_link = $settings['expire_redirect'] ?? [];
    $expire_redirect = ( 'redirect' === $expire_action && !empty( $expire_link['url'] ) )
      ? esc_url_raw( $expire_link['url'] )
      : '';

    $units = $this->get_visible_units( $settings );

    // ------------------------------------------------------------------------
    // Wrapper classes + attributes.
    // ------------------------------------------------------------------------
    $wrapper_classes = [
      'tms-countdown-timer',
      'tms-countdown-timer--skin-' . $skin,
    ];

    if ( $pad_values ) {
      $wrapper_classes[] = 'tms-countdown-timer--padded';
    }

    $wrapper_atts = [
      'role' => 'timer',
      'aria-atomic' => 'true',
      'aria-live' => 'off',
      'data-mode' => $timer_mode,
      'data-pad' => $pad_values ? 'yes' : 'no',
      'data-expire-action' => $expire_action,
    ];

    if ( 'due_date' === $timer_mode && $due_ts > 0 ) {
      // ISO 8601 with UTC offset — JS parses Date() reliably from this.
      $wrapper_atts['data-end-iso'] = gmdate( 'c', $due_ts );
    }

    if ( 'evergreen' === $timer_mode ) {
      $wrapper_atts['data-duration'] = (string) $evergreen_duration;
      $wrapper_atts['data-storage'] = $evergreen_storage;
      $wrapper_atts['data-storage-key'] = 'themeasy_countdown_' . $evergreen_key;
    }

    if ( 'redirect' === $expire_action && '' !== $expire_redirect ) {
      $wrapper_atts['data-expire-redirect'] = $expire_redirect;
    }

    $wrapper_classes_output = implode( ' ', array_filter( $wrapper_classes ) );

    // ------------------------------------------------------------------------
    // Render attributes.
    // ------------------------------------------------------------------------
    if ( 'message' === $expire_action && $expire_message ) {
      $this->add_render_attribute( 'expire_message', 'class', 'tms-countdown-timer__expire' );
      $this->add_render_attribute( 'expire_message', 'hidden', 'hidden' );
      $this->add_inline_editing_attributes( 'expire_message', 'basic' );
    }

    // ------------------------------------------------------------------------
    // Output.
    // ------------------------------------------------------------------------
    ?>
    <div class="<?php echo esc_attr( $wrapper_classes_output ); ?>"<?php themeasy_print_html_attributes( $wrapper_atts ); ?>>

      <div class="tms-countdown-timer__ticker">

        <?php $last_index = count( $units ) - 1; ?>
        <?php foreach ( $units as $index => $unit ) : ?>

          <div class="tms-countdown-timer__unit tms-countdown-timer__unit--<?php echo esc_attr( $unit['key'] ); ?>"
               data-unit="<?php echo esc_attr( $unit['key'] ); ?>">

            <?php if ( 'circles' === $skin ) : ?>
              <div class="tms-countdown-timer__ring" aria-hidden="true">
                <span class="tms-countdown-timer__ring-track"></span>
                <span class="tms-countdown-timer__ring-progress"></span>
                <span class="tms-countdown-timer__ring-inner"></span>
              </div>
            <?php endif; ?>

            <span class="tms-countdown-timer__value" aria-hidden="true"><?php echo esc_html( $pad_values ? '00' : '0' ); ?></span>

            <?php if ( $unit['label'] ) : ?>
              <span class="tms-countdown-timer__label"><?php echo esc_html( $unit['label'] ); ?></span>
            <?php endif; ?>

            <span class="tms-countdown-timer__sr-label"><?php echo esc_html( $unit['label'] ?: $unit['key'] ); ?></span>

          </div><!-- /.tms-countdown-timer__unit -->

          <?php if ( 'inline' === $skin && '' !== $separator_glyph && $index < $last_index ) : ?>
            <span class="tms-countdown-timer__separator" aria-hidden="true"><?php echo esc_html( $separator_glyph ); ?></span>
          <?php endif; ?>

        <?php endforeach; ?>

      </div><!-- /.tms-countdown-timer__ticker -->

      <?php if ( 'message' === $expire_action && $expire_message ) : ?>
        <<?php echo tag_escape( $expire_message_tag ); ?> <?php $this->print_render_attribute_string( 'expire_message' ); ?>>
          <?php echo wp_kses( $expire_message, themeasy_get_kses_allowed_tags() ); ?>
        </<?php echo tag_escape( $expire_message_tag ); ?>>
      <?php endif; ?>

    </div><!-- /.tms-countdown-timer -->
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
      var skin            = settings.skin || 'boxes';
      var separatorChoice = settings.separator_character || 'colon';
      var padValues       = settings.pad_values === 'yes';

      var timerMode       = settings.timer_mode || 'due_date';
      var dueDate         = settings.due_date || '';

      var evergreenDays   = parseInt( settings.evergreen_days || 0, 10 );
      var evergreenHours  = parseInt( settings.evergreen_hours || 0, 10 );
      var evergreenMins   = parseInt( settings.evergreen_minutes || 0, 10 );
      if ( isNaN( evergreenDays ) ) { evergreenDays = 0; }
      if ( isNaN( evergreenHours ) ) { evergreenHours = 0; }
      if ( isNaN( evergreenMins ) ) { evergreenMins = 0; }
      var evergreenDuration =
        Math.max( 0, evergreenDays ) * 86400 +
        Math.max( 0, evergreenHours ) * 3600 +
        Math.max( 0, evergreenMins ) * 60;

      var evergreenStorage = settings.evergreen_storage || 'local';
      var evergreenKey     = ( settings.evergreen_storage_key || 'default' )
        .toString().toLowerCase().replace( /[^a-z0-9_\-]/g, '' );
      if ( ! evergreenKey ) { evergreenKey = 'default'; }

      var expireAction    = settings.expire_action || 'message';
      var expireMessage   = settings.expire_message || '';
      var expireMessageTag = settings.expire_message_tag || 'div';
      var expireAllowedTags = [ 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div' ];
      if ( expireAllowedTags.indexOf( expireMessageTag ) === -1 ) { expireMessageTag = 'div'; }

      // Scheme guard for the redirect target. The regex lives in exactly one
      // place; the fallback fails closed rather than duplicating it (a bare ^
      // anchor misses the C0 controls browsers strip before resolving a scheme).
      var safeUrl = ( window.Themeasy && window.Themeasy.safeUrl )
        ? window.Themeasy.safeUrl
        : function () { return ''; };

      var expireLink      = settings.expire_redirect || {};
      var expireRedirect  = '';

      // A blocked scheme yields '' — no redirect target is emitted at all.
      if ( expireAction === 'redirect' && expireLink && expireLink.url ) {
        expireRedirect = safeUrl( expireLink.url );
      }

      // ------------------------------------------------------------------------
      // Separator glyph.
      // ------------------------------------------------------------------------
      var separatorGlyph = ':';
      if ( separatorChoice === 'dot' ) { separatorGlyph = '.'; }
      else if ( separatorChoice === 'dash' ) { separatorGlyph = '-'; }
      else if ( separatorChoice === 'none' ) { separatorGlyph = ''; }

      // ------------------------------------------------------------------------
      // Visible units.
      // ------------------------------------------------------------------------
      var unitDefs = [
        { key: 'days',    show: settings.show_days    === 'yes', label: settings.days_label    || '' },
        { key: 'hours',   show: settings.show_hours   === 'yes', label: settings.hours_label   || '' },
        { key: 'minutes', show: settings.show_minutes === 'yes', label: settings.minutes_label || '' },
        { key: 'seconds', show: settings.show_seconds === 'yes', label: settings.seconds_label || '' }
      ];

      var units = _.filter( unitDefs, function( u ) { return u.show; } );

      // ------------------------------------------------------------------------
      // Wrapper classes + attributes.
      // ------------------------------------------------------------------------
      var wrapperClasses = [ 'tms-countdown-timer', 'tms-countdown-timer--skin-' + skin ];

      if ( padValues ) {
        wrapperClasses.push( 'tms-countdown-timer--padded' );
      }

      var wrapperAtts = {
        'role': 'timer',
        'aria-atomic': 'true',
        'aria-live': 'off',
        'data-mode': timerMode,
        'data-pad': padValues ? 'yes' : 'no',
        'data-expire-action': expireAction
      };

      if ( timerMode === 'due_date' && dueDate ) {
        // Editor preview: simple pass-through; PHP converts using site timezone.
        wrapperAtts['data-end-iso'] = dueDate;
      }

      if ( timerMode === 'evergreen' ) {
        wrapperAtts['data-duration']    = String( evergreenDuration );
        wrapperAtts['data-storage']     = evergreenStorage;
        wrapperAtts['data-storage-key'] = 'themeasy_countdown_' + evergreenKey;
      }

      if ( expireAction === 'redirect' && expireRedirect ) {
        wrapperAtts['data-expire-redirect'] = expireRedirect;
      }

      var wrapperClassStr = wrapperClasses.filter( Boolean ).join( ' ' );

      // ------------------------------------------------------------------------
      // Render attributes.
      // ------------------------------------------------------------------------
      if ( expireAction === 'message' && expireMessage ) {
        view.addRenderAttribute( 'expire_message', 'class', 'tms-countdown-timer__expire' );
        view.addRenderAttribute( 'expire_message', 'hidden', 'hidden' );
        view.addInlineEditingAttributes( 'expire_message', 'basic' );
      }

      var padZero = function() {
        return padValues ? '00' : '0';
      };
    #>

    <div class="{{ wrapperClassStr }}" {{{ Themeasy.htmlAttributes( wrapperAtts ) }}}>

      <div class="tms-countdown-timer__ticker">

        <# _.each( units, function( unit, index ) { #>

          <div class="tms-countdown-timer__unit tms-countdown-timer__unit--{{ unit.key }}" data-unit="{{ unit.key }}">

            <# if ( skin === 'circles' ) { #>
              <div class="tms-countdown-timer__ring" aria-hidden="true">
                <span class="tms-countdown-timer__ring-track"></span>
                <span class="tms-countdown-timer__ring-progress"></span>
                <span class="tms-countdown-timer__ring-inner"></span>
              </div>
            <# } #>

            <span class="tms-countdown-timer__value" aria-hidden="true">{{ padZero() }}</span>

            <# if ( unit.label ) { #>
              <span class="tms-countdown-timer__label">{{ unit.label }}</span>
            <# } #>

            <span class="tms-countdown-timer__sr-label">{{ unit.label || unit.key }}</span>

          </div><!-- /.tms-countdown-timer__unit -->

          <# if ( skin === 'inline' && separatorGlyph && index < units.length - 1 ) { #>
            <span class="tms-countdown-timer__separator" aria-hidden="true">{{ separatorGlyph }}</span>
          <# } #>

        <# } ); #>

      </div><!-- /.tms-countdown-timer__ticker -->

      <# if ( expireAction === 'message' && expireMessage ) { #>
        <{{ expireMessageTag }} {{{ view.getRenderAttributeString( 'expire_message' ) }}}>{{{ sanitizeInline( expireMessage ) }}}</{{ expireMessageTag }}>
      <# } #>

    </div><!-- /.tms-countdown-timer -->

    <?php
  }
}
