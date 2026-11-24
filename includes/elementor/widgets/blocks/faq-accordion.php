<?php
/**
 * Themeasy Elementor Widget: FAQ Accordion
 *
 * Editorial FAQ accordion with large question typography, optional leading
 * counters (01, 02, ...) and a morphing plus/minus (or rotating chevron)
 * indicator, optionally seated on a background shape. Panels collapse via
 * the CSS grid 0fr/1fr technique, so the open/close motion is
 * height-accurate with no JS measurement; the tiny JS
 * layer only toggles classes and ARIA state through document-level event
 * delegation (which also makes it interactive inside the Elementor editor).
 *
 * Degradation: with no JS every answer stays visible (progressive
 * enhancement — content is never lost); reduced motion disables the
 * collapse/morph transitions.
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
 * Responsible for registering controls and rendering the FAQ Accordion widget.
 */
class FaqAccordion extends Widget_Base {
  public function get_name() {
    return 'themeasy-faq-accordion';
  }

  public function get_title() {
    return esc_html__( 'FAQ Accordion', 'themeasy-lite' );
  }

  public function get_icon() {
    return 'eicon-accordion';
  }

  public function get_custom_help_url() {
    return 'https://hub.themeasy.co/docs';
  }

  public function get_categories() {
    return ['themeasy_blocks_category', 'themeasy_content_category'];
  }

  public function get_keywords() {
    return ['themeasy', 'faq', 'accordion', 'questions', 'answers', 'toggle', 'collapse'];
  }

  public function get_style_depends() {
    return ['themeasy-shared', 'themeasy-faq-accordion'];
  }

  public function get_script_depends() {
    return ['themeasy-faq-accordion'];
  }

  /**
   * Register widget customization controls.
   *
   * @return void
   */
  protected function register_controls() {
    // ------------------------------------------------------------------------
    // Content section: FAQ Items
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'faq_accordion_items_section',
        [
          'label' => esc_html__( 'FAQ Items', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $repeater = new Repeater();

      $repeater->add_control(
        'question',
        [
          'label' => esc_html__( 'Question', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'What is included in the plan?', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
        ]
      );

      $repeater->add_control(
        'answer',
        [
          'label' => esc_html__( 'Answer', 'themeasy-lite' ),
          'type' => Controls_Manager::WYSIWYG,
          'default' => esc_html__(
            'Every plan includes all core features, free updates and access to our support team.',
            'themeasy-lite'
          ),
          'dynamic' => ['active' => true],
        ]
      );

      $this->add_control(
        'items',
        [
          'label' => esc_html__( 'Items', 'themeasy-lite' ),
          'type' => Controls_Manager::REPEATER,
          'fields' => $repeater->get_controls(),
          'title_field' => '{{{ question }}}',
          'default' => [
            [
              'question' => esc_html__( 'What is included in the plan?', 'themeasy-lite' ),
              'answer' => esc_html__(
                'Every plan includes all core features, free updates and access to our support team.',
                'themeasy-lite'
              ),
            ],
            [
              'question' => esc_html__( 'Can I cancel at any time?', 'themeasy-lite' ),
              'answer' => esc_html__(
                'Yes — you can cancel your subscription whenever you want, no questions asked.',
                'themeasy-lite'
              ),
            ],
            [
              'question' => esc_html__( 'Do you offer refunds?', 'themeasy-lite' ),
              'answer' => esc_html__(
                'We offer a 14-day money-back guarantee on all plans, starting from the purchase date.',
                'themeasy-lite'
              ),
            ],
          ],
        ]
      );

      $this->add_control(
        'question_tag',
        [
          'label' => esc_html__( 'Question Tag', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'separator' => 'before',
          'default' => 'h3',
          'options' => [
            'h2' => 'H2',
            'h3' => 'H3',
            'h4' => 'H4',
            'h5' => 'H5',
            'h6' => 'H6',
            'div' => 'DIV',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Settings
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'faq_accordion_settings_section',
        [
          'label' => esc_html__( 'Settings', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $this->add_control(
        'first_open',
        [
          'label' => esc_html__( 'Open First Item', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
        ]
      );

      $this->add_control(
        'allow_multiple_open',
        [
          'label' => esc_html__( 'Allow Multiple Open', 'themeasy-lite' ),
          'description' => esc_html__( 'Allow multiple items open simultaneously.', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => '',
        ]
      );

      $this->add_control(
        'show_numbers',
        [
          'label' => esc_html__( 'Show Numbers', 'themeasy-lite' ),
          'description' => esc_html__( 'Leading counters (01, 02, ...) before each question.', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Show', 'themeasy-lite' ),
          'label_off' => esc_html__( 'Hide', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
        ]
      );

      $this->add_control(
        'indicator_style',
        [
          'label' => esc_html__( 'Indicator', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'plus',
          'options' => [
            'plus' => esc_html__( 'Plus / Minus (Morphing)', 'themeasy-lite' ),
            'chevron' => esc_html__( 'Chevron (Rotating)', 'themeasy-lite' ),
            'none' => esc_html__( 'None', 'themeasy-lite' ),
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Items
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'faq_accordion_items_styles_section',
        [
          'label' => esc_html__( 'Items', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_control(
        'divider_color',
        [
          'label' => esc_html__( 'Divider Color', 'themeasy-lite' ),
          'description' => esc_html__( 'Color of the lines between questions. Set it to transparent to remove them — useful when Item Border turns every item into a card.', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-faq-accordion' => '--tms-faq-accordion-divider-color: {{VALUE}};',
          ],
        ]
      );

      $this->start_controls_tabs( 'item_states_tabs', ['separator' => 'before'] );

        $this->start_controls_tab(
          'item_normal_tab',
          [
            'label' => esc_html__( 'Normal', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'item_background_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-faq-accordion .tms-faq-accordion__item' => 'background-color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

        $this->start_controls_tab(
          'item_open_tab',
          [
            'label' => esc_html__( 'Open', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'item_open_background_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-faq-accordion .tms-faq-accordion__item--open' => 'background-color: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            'item_open_border_color',
            [
              'label' => esc_html__( 'Border Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                // Recolors both channels of the open item: the Item Border group
                // and the divider that the item draws on its own ::after.
                '{{WRAPPER}} .tms-faq-accordion .tms-faq-accordion__item--open' =>
                  'border-color: {{VALUE}}; --tms-faq-accordion-divider-color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

      // A real flex `gap` on the list, not a per-item margin: it only exists
      // BETWEEN items (nothing to cancel on the first/last one) and never
      // collapses, so the rhythm is exact with or without item chrome.
      $this->add_responsive_control(
        'item_gap',
        [
          'label' => esc_html__( 'Gap Between Items', 'themeasy-lite' ),
          'description' => esc_html__( 'Space between items. Pair it with Item Padding and a background or border for a stack of separate cards.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'separator' => 'before',
          'size_units' => ['px', 'rem', 'em'],
          'range' => [
            'px' => ['min' => 0, 'max' => 80, 'step' => 1],
            'rem' => ['min' => 0, 'max' => 5, 'step' => 0.125],
            'em' => ['min' => 0, 'max' => 5, 'step' => 0.125],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-faq-accordion' => '--tms-faq-accordion-item-gap: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'item_spacing',
        [
          'label' => esc_html__( 'Item Spacing', 'themeasy-lite' ),
          'description' => esc_html__( 'Vertical breathing room above and below each question.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem'],
          'range' => [
            'px' => ['min' => 8, 'max' => 80, 'step' => 1],
            'rem' => ['min' => 0.5, 'max' => 5, 'step' => 0.125],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-faq-accordion' => '--tms-faq-accordion-pad: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'item_padding',
        [
          'label' => esc_html__( 'Item Padding', 'themeasy-lite' ),
          'description' => esc_html__( 'Inset around each question. Use it together with a background or border to turn every item into a card.', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem'],
          'separator' => 'before',
          'selectors' => [
            '{{WRAPPER}} .tms-faq-accordion .tms-faq-accordion__item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Border::get_type(),
        [
          'name' => 'item_border',
          'selector' => '{{WRAPPER}} .tms-faq-accordion .tms-faq-accordion__item',
        ]
      );

      $this->add_responsive_control(
        'item_border_radius',
        [
          'label' => esc_html__( 'Item Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', '%'],
          'selectors' => [
            '{{WRAPPER}} .tms-faq-accordion .tms-faq-accordion__item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'toggle_transition_duration',
        [
          'label' => esc_html__( 'Transition Duration', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['s'],
          'range' => ['s' => ['min' => 0.1, 'max' => 1.5, 'step' => 0.05]],
          'default' => ['unit' => 's', 'size' => 0.4],
          'selectors' => [
            '{{WRAPPER}} .tms-faq-accordion' => '--tms-faq-accordion-duration: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Number
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'faq_accordion_number_styles_section',
        [
          'label' => esc_html__( 'Number', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'show_numbers' => 'yes',
          ],
        ]
      );

      $this->add_control(
        'number_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-faq-accordion__number' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'number_open_color',
        [
          'label' => esc_html__( 'Open Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-faq-accordion__item--open .tms-faq-accordion__number' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control( // number_typography
        Group_Control_Typography::get_type(),
        [
          'name' => 'number_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-faq-accordion__number',
        ]
      );

      $this->add_responsive_control(
        'number_column_width',
        [
          'label' => esc_html__( 'Column Width', 'themeasy-lite' ),
          'description' => esc_html__( 'Reserved width for the counter — answers align to the same edge.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem', 'em'],
          'range' => [
            'px' => ['min' => 20, 'max' => 120, 'step' => 1],
            'rem' => ['min' => 1, 'max' => 8, 'step' => 0.125],
            'em' => ['min' => 1, 'max' => 6, 'step' => 0.1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-faq-accordion' => '--tms-faq-accordion-number-width: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Question
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'faq_accordion_question_styles_section',
        [
          'label' => esc_html__( 'Question', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      // The pointer is where the three states meet, so the Hover color's
      // selector sits between two of the Open color's: one class above its
      // plain hover selector, which keeps painting the hover while Hover is
      // empty (as it did before the key existed), and one below its
      // open-and-hovered selector, so an open question holds its Open color.
      // Plain `color` declarations only: a global color rewrites every value
      // of a rule, which a var() chain would not survive.
      $question_hover = '{{WRAPPER}} .tms-faq-accordion'
        . ' .tms-faq-accordion__trigger:hover .tms-faq-accordion__question';
      $question_open_hover = '{{WRAPPER}} .tms-faq-accordion .tms-faq-accordion__item--open'
        . ' .tms-faq-accordion__trigger:hover .tms-faq-accordion__question';

      $this->start_controls_tabs( 'question_states_tabs' );

        $this->start_controls_tab(
          'question_normal_tab',
          [
            'label' => esc_html__( 'Normal', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'question_color',
            [
              'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-faq-accordion__question' => 'color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

        $this->start_controls_tab(
          'question_hover_tab',
          [
            'label' => esc_html__( 'Hover', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'question_hover_color',
            [
              'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
              'description' => esc_html__( 'Leave empty to follow the Open color.', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                $question_hover => 'color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

        $this->start_controls_tab(
          'question_open_tab',
          [
            'label' => esc_html__( 'Open', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'question_open_color',
            [
              'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-faq-accordion__item--open .tms-faq-accordion__question,
                 {{WRAPPER}} .tms-faq-accordion__trigger:hover .tms-faq-accordion__question' => 'color: {{VALUE}};',
                $question_open_hover => 'color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

      $this->add_group_control( // question_typography
        Group_Control_Typography::get_type(),
        [
          'name' => 'question_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-faq-accordion__question',
          'separator' => 'before',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Answer
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'faq_accordion_answer_styles_section',
        [
          'label' => esc_html__( 'Answer', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_control(
        'answer_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-faq-accordion__answer' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control( // answer_typography
        Group_Control_Typography::get_type(),
        [
          'name' => 'answer_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-faq-accordion__answer',
        ]
      );

      $this->add_responsive_control(
        'answer_max_width',
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
            '{{WRAPPER}} .tms-faq-accordion__answer' => 'max-width: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Indicator
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'faq_accordion_indicator_styles_section',
        [
          'label' => esc_html__( 'Indicator', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'indicator_style!' => 'none',
          ],
        ]
      );

      $this->add_control(
        'indicator_shape',
        [
          'label' => esc_html__( 'Background Shape', 'themeasy-lite' ),
          'description' => esc_html__( 'Seats the indicator on a circle that inverts to the primary color when its item opens. Shape, size and colors are editable below.', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Show', 'themeasy-lite' ),
          'label_off' => esc_html__( 'Hide', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => '',
        ]
      );

      // Every color below writes a wrapper token instead of the property, so
      // the Normal and Open values never shadow each other: a normal color
      // set at control specificity would otherwise freeze the open state too.
      $this->start_controls_tabs( 'indicator_states_tabs', ['separator' => 'before'] );

        $this->start_controls_tab(
          'indicator_normal_tab',
          [
            'label' => esc_html__( 'Normal', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'indicator_color',
            [
              'label' => esc_html__( 'Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-faq-accordion' => '--tms-faq-accordion-icon-color: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            'indicator_background_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'condition' => [
                'indicator_shape' => 'yes',
              ],
              'selectors' => [
                '{{WRAPPER}} .tms-faq-accordion' => '--tms-faq-accordion-node-bg: {{VALUE}};',
              ],
            ]
          );

          // Re-registered color field of the `indicator_border` group (same key
          // the excluded field used), so it can live in the state tabs.
          $this->add_control(
            'indicator_border_color',
            [
              'label' => esc_html__( 'Border Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'condition' => [
                'indicator_shape' => 'yes',
                'indicator_border_border!' => ['', 'none'],
              ],
              'selectors' => [
                '{{WRAPPER}} .tms-faq-accordion' => '--tms-faq-accordion-node-border-color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

        $this->start_controls_tab(
          'indicator_open_tab',
          [
            'label' => esc_html__( 'Open', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'indicator_open_color',
            [
              'label' => esc_html__( 'Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-faq-accordion' => '--tms-faq-accordion-icon-open-color: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            'indicator_open_background_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'condition' => [
                'indicator_shape' => 'yes',
              ],
              'selectors' => [
                '{{WRAPPER}} .tms-faq-accordion' => '--tms-faq-accordion-node-open-bg: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            'indicator_open_border_color',
            [
              'label' => esc_html__( 'Border Color', 'themeasy-lite' ),
              'description' => esc_html__( 'Leave empty to match the open background.', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'condition' => [
                'indicator_shape' => 'yes',
                'indicator_border_border!' => ['', 'none'],
              ],
              'selectors' => [
                '{{WRAPPER}} .tms-faq-accordion' => '--tms-faq-accordion-node-open-border-color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

      $this->add_responsive_control(
        'indicator_size',
        [
          'label' => esc_html__( 'Icon Size', 'themeasy-lite' ),
          'description' => esc_html__( 'Glyph size. The background shape scales around it unless Node Size is set.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'separator' => 'before',
          'size_units' => ['px', 'rem', 'em'],
          'range' => [
            'px' => ['min' => 8, 'max' => 48, 'step' => 1],
            'rem' => ['min' => 0.5, 'max' => 3, 'step' => 0.05],
            'em' => ['min' => 0.5, 'max' => 3, 'step' => 0.05],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-faq-accordion__icon' => 'font-size: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'indicator_thickness',
        [
          'label' => esc_html__( 'Thickness', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => ['min' => 1, 'max' => 6, 'step' => 0.5],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-faq-accordion' => '--tms-faq-accordion-icon-thickness: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'indicator_node_size',
        [
          'label' => esc_html__( 'Node Size', 'themeasy-lite' ),
          'description' => esc_html__( 'Size of the shape behind the icon. Leave empty for 2.5 times the Icon Size; in em it stays a multiple of the Icon Size.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'separator' => 'before',
          'size_units' => ['px', 'rem', 'em'],
          'range' => [
            'px' => ['min' => 16, 'max' => 120, 'step' => 1],
            'rem' => ['min' => 1, 'max' => 7.5, 'step' => 0.125],
            'em' => ['min' => 1, 'max' => 5, 'step' => 0.05],
          ],
          'condition' => [
            'indicator_shape' => 'yes',
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-faq-accordion' => '--tms-faq-accordion-node-size: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      // Type + width only (state-independent); the color is re-registered in
      // the Normal/Open tabs above. box-sizing is border-box on the node, so
      // the border is drawn inside it and the glyph never shifts.
      $this->add_group_control(
        Group_Control_Border::get_type(),
        [
          'name' => 'indicator_border',
          'label' => esc_html__( 'Border', 'themeasy-lite' ),
          'exclude' => ['color'],
          'condition' => [
            'indicator_shape' => 'yes',
          ],
          'selector' => '{{WRAPPER}} .tms-faq-accordion--indicator-shaped .tms-faq-accordion__icon',
        ]
      );

      $this->add_responsive_control(
        'indicator_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'description' => esc_html__( 'Leave empty for a circle.', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', '%', 'em', 'rem'],
          'condition' => [
            'indicator_shape' => 'yes',
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-faq-accordion--indicator-shaped .tms-faq-accordion__icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
    $items = $settings['items'] ?? [];

    // Early return if no items.
    if ( empty( $items ) ) {
      return;
    }

    // ------------------------------------------------------------------------
    // Settings.
    // ------------------------------------------------------------------------
    $question_tag = themeasy_sanitize_heading_tag(
      $settings['question_tag'] ?? 'h3',
      'h3',
      ['h2', 'h3', 'h4', 'h5', 'h6', 'div']
    );
    $first_open = ( $settings['first_open'] ?? 'yes' ) === 'yes';
    $allow_multiple = ( $settings['allow_multiple_open'] ?? '' ) === 'yes';
    $show_numbers = ( $settings['show_numbers'] ?? 'yes' ) === 'yes';
    $indicator = $settings['indicator_style'] ?? 'plus';
    $indicator = in_array( $indicator, ['plus', 'chevron', 'none'], true ) ? $indicator : 'plus';
    $is_shaped = 'none' !== $indicator && ( $settings['indicator_shape'] ?? '' ) === 'yes';

    // An item that draws its own bottom border is a card: the open state then
    // recolors that border only, and the divider keeps its rest colour.
    $item_border = $settings['item_border_border'] ?? '';
    $is_bordered = '' !== $item_border && 'none' !== $item_border
      && (float) ( $settings['item_border_width']['bottom'] ?? 0 ) > 0;

    // ------------------------------------------------------------------------
    // Wrapper classes + attributes.
    // ------------------------------------------------------------------------
    $wrapper_classes = ['tms-faq-accordion', 'tms-faq-accordion--indicator-' . $indicator];
    $wrapper_atts = [];

    if ( $is_bordered ) {
      $wrapper_classes[] = 'tms-faq-accordion--item-bordered';
    }

    if ( $allow_multiple ) {
      $wrapper_classes[] = 'tms-faq-accordion--multiple';
    }

    if ( $show_numbers ) {
      $wrapper_classes[] = 'tms-faq-accordion--numbered';
    }

    if ( $is_shaped ) {
      $wrapper_classes[] = 'tms-faq-accordion--indicator-shaped';
    }

    // Editor preview is collapsed statically (no JS init runs in the canvas);
    // the document-level delegation still makes clicks toggle items there.
    if ( themeasy_is_elementor_editor() ) {
      $wrapper_classes[] = 'tms-faq-accordion--static';
    }

    $wrapper_classes_output = implode( ' ', array_filter( $wrapper_classes ) );

    // ------------------------------------------------------------------------
    // Output.
    // ------------------------------------------------------------------------
    ?>
    <div class="<?php echo esc_attr( $wrapper_classes_output ); ?>"<?php themeasy_print_html_attributes( $wrapper_atts ); ?>>

      <?php foreach ( $items as $index => $item ) : ?>
        <?php
        $is_open = $first_open && 0 === $index;
        $item_uid = 'tms-faq-' . (string) ( $item['_id'] ?? $index );

        $item_classes = [
          'tms-faq-accordion__item',
          'elementor-repeater-item-' . ( $item['_id'] ?? '' ),
        ];

        if ( $is_open ) {
          $item_classes[] = 'tms-faq-accordion__item--open';
        }

        $item_classes_output = implode( ' ', array_filter( $item_classes ) );

        $question_key = $this->get_repeater_setting_key( 'question', 'items', $index );
        $this->add_render_attribute( $question_key, 'class', 'tms-faq-accordion__question' );
        $this->add_inline_editing_attributes( $question_key, 'none' );

        $answer_key = $this->get_repeater_setting_key( 'answer', 'items', $index );
        $this->add_render_attribute( $answer_key, 'class', 'tms-faq-accordion__answer' );
        $this->add_inline_editing_attributes( $answer_key, 'advanced' );
        ?>

        <div class="<?php echo esc_attr( $item_classes_output ); ?>">

          <<?php echo tag_escape( $question_tag ); ?> class="tms-faq-accordion__header">
            <button
              type="button"
              class="tms-faq-accordion__trigger"
              id="<?php echo esc_attr( $item_uid ); ?>-trigger"
              aria-controls="<?php echo esc_attr( $item_uid ); ?>-panel"
              aria-expanded="<?php echo esc_attr( $is_open ? 'true' : 'false' ); ?>"
            >
              <?php if ( $show_numbers ) : ?>
                <span class="tms-faq-accordion__number" aria-hidden="true"></span>
              <?php endif; ?>

              <span <?php $this->print_render_attribute_string( $question_key ); ?>>
                <?php echo esc_html( $item['question'] ?? '' ); ?>
              </span>

              <?php if ( 'none' !== $indicator ) : ?>
                <span class="tms-faq-accordion__icon" aria-hidden="true">
                  <?php if ( 'chevron' === $indicator ) : ?>
                    <?php
                    echo wp_kses(
                      themeasy_get_svg_icon( 'ty-feather', 'chevron-down', 'tms-faq-accordion__chevron' ),
                      themeasy_get_icon_kses_tags()
                    );
                    ?>
                  <?php endif; ?>
                </span>
              <?php endif; ?>
            </button>
          </<?php echo tag_escape( $question_tag ); ?>>

          <div
            class="tms-faq-accordion__panel"
            id="<?php echo esc_attr( $item_uid ); ?>-panel"
            role="region"
            aria-labelledby="<?php echo esc_attr( $item_uid ); ?>-trigger"
          >
            <div class="tms-faq-accordion__body">
              <div <?php $this->print_render_attribute_string( $answer_key ); ?>>
                <?php echo wp_kses_post( $item['answer'] ?? '' ); ?>
              </div>
            </div>
          </div>

        </div><!-- /.tms-faq-accordion__item -->
      <?php endforeach; ?>

    </div><!-- /.tms-faq-accordion -->
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
      var items = settings.items || [];

      if ( ! items.length ) {
        return;
      }

      var allowedTags = [ 'h2', 'h3', 'h4', 'h5', 'h6', 'div' ];
      var questionTag = allowedTags.indexOf( settings.question_tag ) !== -1 ? settings.question_tag : 'h3';
      var firstOpen = settings.first_open === 'yes';
      var allowMultiple = settings.allow_multiple_open === 'yes';
      var showNumbers = settings.show_numbers === 'yes';
      var indicator = [ 'plus', 'chevron', 'none' ].indexOf( settings.indicator_style ) !== -1
        ? settings.indicator_style
        : 'plus';
      var isShaped = indicator !== 'none' && settings.indicator_shape === 'yes';

      // An item that draws its own bottom border is a card: the open state then
      // recolors that border only, and the divider keeps its rest colour.
      var itemBorder = settings.item_border_border || '';
      var isBordered = itemBorder !== '' && itemBorder !== 'none'
        && parseFloat( ( settings.item_border_width || {} ).bottom ) > 0;

      var sanitizeInline = ( window.Themeasy && window.Themeasy.sanitizeInlineHtml )
        ? window.Themeasy.sanitizeInlineHtml
        : _.escape;

      // ------------------------------------------------------------------------
      // Wrapper classes + attributes. The editor preview is always static.
      // ------------------------------------------------------------------------
      var wrapperClasses = [
        'tms-faq-accordion',
        'tms-faq-accordion--indicator-' + indicator,
        'tms-faq-accordion--static'
      ];
      var wrapperAtts = {};

      if ( isBordered ) {
        wrapperClasses.push( 'tms-faq-accordion--item-bordered' );
      }

      if ( allowMultiple ) {
        wrapperClasses.push( 'tms-faq-accordion--multiple' );
      }

      if ( showNumbers ) {
        wrapperClasses.push( 'tms-faq-accordion--numbered' );
      }

      if ( isShaped ) {
        wrapperClasses.push( 'tms-faq-accordion--indicator-shaped' );
      }

      var wrapperClassStr = wrapperClasses.filter( Boolean ).join( ' ' );
    #>

    <div class="{{ wrapperClassStr }}" {{{ Themeasy.htmlAttributes( wrapperAtts ) }}}>

      <# _.each( items, function( item, index ) {
        var isOpen = firstOpen && index === 0;
        var itemUid = 'tms-faq-' + ( item._id || index );

        var itemClasses = [
          'tms-faq-accordion__item',
          'elementor-repeater-item-' + ( item._id || '' )
        ];

        if ( isOpen ) {
          itemClasses.push( 'tms-faq-accordion__item--open' );
        }

        var itemClassStr = itemClasses.filter( Boolean ).join( ' ' );

        var questionKey = view.getRepeaterSettingKey( 'question', 'items', index );
        view.addRenderAttribute( questionKey, 'class', 'tms-faq-accordion__question' );
        view.addInlineEditingAttributes( questionKey, 'none' );

        var answerKey = view.getRepeaterSettingKey( 'answer', 'items', index );
        view.addRenderAttribute( answerKey, 'class', 'tms-faq-accordion__answer' );
        view.addInlineEditingAttributes( answerKey, 'advanced' );

        var answerHtml = sanitizeInline( item.answer || '', 'rich' );
      #>

        <div class="{{ itemClassStr }}">

          <{{ questionTag }} class="tms-faq-accordion__header">
            <button
              type="button"
              class="tms-faq-accordion__trigger"
              id="{{ itemUid }}-trigger"
              aria-controls="{{ itemUid }}-panel"
              aria-expanded="{{ isOpen ? 'true' : 'false' }}"
            >
              <# if ( showNumbers ) { #>
                <span class="tms-faq-accordion__number" aria-hidden="true"></span>
              <# } #>

              <span {{{ view.getRenderAttributeString( questionKey ) }}}>{{ item.question || '' }}</span>

              <# if ( indicator !== 'none' ) { #>
                <span class="tms-faq-accordion__icon" aria-hidden="true">
                  <# if ( indicator === 'chevron' ) { #>
                    <?php
                    echo wp_kses(
                      themeasy_get_svg_icon( 'ty-feather', 'chevron-down', 'tms-faq-accordion__chevron' ),
                      themeasy_get_icon_kses_tags()
                    );
                    ?>
                  <# } #>
                </span>
              <# } #>
            </button>
          </{{ questionTag }}>

          <div
            class="tms-faq-accordion__panel"
            id="{{ itemUid }}-panel"
            role="region"
            aria-labelledby="{{ itemUid }}-trigger"
          >
            <div class="tms-faq-accordion__body">
              <div {{{ view.getRenderAttributeString( answerKey ) }}}>{{{ answerHtml }}}</div>
            </div>
          </div>

        </div><!-- /.tms-faq-accordion__item -->

      <# }); #>

    </div><!-- /.tms-faq-accordion -->
    <?php
  }
}
