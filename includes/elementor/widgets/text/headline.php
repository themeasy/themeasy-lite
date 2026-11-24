<?php
/**
 * Themeasy Elementor Widget: Headline
 *
 * Displays an introductory text section with customizable pre-title, multi-part
 * title (up to 3 styled segments with icons), and description. Supports
 * outline/gradient/masked-image text styles and multiple layouts.
 *
 * @package Themeasy
 * @since 1.0.0
 */

namespace Themeasy\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Utils;
use Elementor\Widget_Base;

/**
 * Responsible for registering controls and rendering the widget.
 */
class Headline extends Widget_Base {
  /**
   * Migrate saves from before 'title_display_block' became 'title_flow'.
   *
   * The old key was a switcher: 'yes' put every title part on its own line,
   * empty left them in a flex row. The replacement adds a third value —
   * 'inline', where the parts merge into one continuous text run so an icon can
   * sit INSIDE the sentence — and 'inline' is the default a new Headline gets.
   *
   * Elementor only stores non-default values, so a headline that never toggled
   * the old switcher saved no key at all and is indistinguishable, key by key,
   * from a brand-new widget. The discriminator is therefore the presence of ANY
   * saved setting: a real instance carries its text, an instance the editor is
   * building fresh carries nothing. That keeps every published title rendering
   * exactly as it did (measured on this install: 151 of 163 live headlines hold
   * the key and all 151 hold 'yes'; the other 12 rendered as a row) while new
   * widgets still open in the flow the feature was built for.
   *
   * It only holds while every save made after the control exists carries the
   * key, so 'title_flow' is registered with 'save_default': without it the
   * editor drops an 'inline' from the payload, like any value equal to the
   * default, and this constructor stored the title as a row.
   *
   * Dropping the old key here also self-migrates the stored data on the next
   * editor save.
   *
   * @param array      $data Widget data (settings included on real instances).
   * @param array|null $args Widget default args.
   */
  public function __construct( $data = [], $args = null ) {
    $settings = $data['settings'] ?? null;

    if ( is_array( $settings ) && [] !== $settings ) {
      if ( !isset( $settings['title_flow'] ) ) {
        $data['settings']['title_flow'] = ( ( $settings['title_display_block'] ?? '' ) === 'yes' )
          ? 'block'
          : 'row';
      }

      unset( $data['settings']['title_display_block'] );
    }

    parent::__construct( $data, $args );
  }

  public function get_name() {
    return 'themeasy-headline';
  }

  public function get_title() {
    return esc_html__( 'Headline', 'themeasy-lite' );
  }

  public function get_icon() {
    return 'eicon-heading';
  }

  public function get_custom_help_url() {
    return 'https://hub.themeasy.co/docs';
  }

  public function get_categories() {
    return ['themeasy_text_category', 'themeasy_content_category'];
  }

  public function get_keywords() {
    return ['themeasy', 'headline', 'heading', 'title', 'subtitle', 'text', 'intro'];
  }

  public function get_style_depends() {
    return ['themeasy-headline'];
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
    // Content section: Text
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'headline_text_section',
        [
          'label' => esc_html__( 'Text', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      // Pre-Title.
      $this->add_control(
        'pre_title_heading_content',
        [
          'label' => esc_html__( 'Pre-Title', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
        ]
      );

      $this->add_control(
        'pre_title',
        [
          'label' => esc_html__( 'Pre Title', 'themeasy-lite' ),
          'show_label' => false,
          'label_block' => true,
          'type' => Controls_Manager::TEXTAREA,
          'default' => esc_html__( 'Lorem ipsum', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'Leave blank to hide', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
        ]
      );

      $this->add_control(
        'pre_title_html_tag',
        [
          'label' => esc_html__( 'HTML Tag', 'themeasy-lite' ),
          'description' => esc_html__( 'The pre-title sits before the title in the DOM, so a heading tag here lands above the section heading in the document outline. Keep a non-heading tag unless the kicker is really a heading.', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'span',
          'options' => [
            'h1' => 'H1',
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

      // Title.
      $this->add_control(
        'title_heading_content',
        [
          'label' => esc_html__( 'Title', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'title_note_content',
        [
          'show_label' => false,
          'type' => Controls_Manager::RAW_HTML,
          'raw' => esc_html__( 'Use these fields to mix and match styles. For example, you can set Text 1 to a solid font style and apply outline or italic effects to Text 2, allowing for versatile and visually appealing text combinations.', 'themeasy-lite' ),
          'content_classes' => 'elementor-control-field-description no-margin',
        ]
      );

      $this->start_controls_tabs( 'title_tabs_content' );

        // Tab: Text 1.
        $this->start_controls_tab(
          'title_text_1_tab_content',
          [
            'label' => esc_html__( 'Text 1', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'title_text_1',
            [
              'label' => esc_html__( 'Text 1', 'themeasy-lite' ),
              'show_label' => false,
              'type' => Controls_Manager::TEXTAREA,
              'default' => esc_html__( 'Lorem ipsum dolor sit amet', 'themeasy-lite' ),
              'placeholder' => esc_html__( 'Leave blank to hide', 'themeasy-lite' ),
              'dynamic' => ['active' => true],
            ]
          );

          $this->add_control(
            'title_text_1_icon',
            [
              'label' => esc_html__( 'Icon', 'themeasy-lite' ),
              'type' => Controls_Manager::ICONS,
              'default' => [
                'value' => 'ty-feather-layers',
                'library' => 'ty-feather',
              ],
              'condition' => [
                'title_enable_icon' => 'yes',
              ],
            ]
          );

          $this->add_control(
            'title_text_1_icon_alignment',
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
              'default' => 'left',
              'toggle' => false,
              'condition' => [
                'title_enable_icon' => 'yes',
              ],
            ]
          );

        $this->end_controls_tab();

        // Tab: Text 2.
        $this->start_controls_tab(
          'title_text_2_tab_content',
          [
            'label' => esc_html__( 'Text 2', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'title_text_2',
            [
              'label' => esc_html__( 'Text 2', 'themeasy-lite' ),
              'show_label' => false,
              'type' => Controls_Manager::TEXTAREA,
              'placeholder' => esc_html__( 'Leave blank to hide', 'themeasy-lite' ),
              'dynamic' => ['active' => true],
            ]
          );

          $this->add_control(
            'title_text_2_icon',
            [
              'label' => esc_html__( 'Icon', 'themeasy-lite' ),
              'type' => Controls_Manager::ICONS,
              'default' => [
                'value' => 'ty-feather-layers',
                'library' => 'ty-feather',
              ],
              'condition' => [
                'title_enable_icon' => 'yes',
              ],
            ]
          );

          $this->add_control(
            'title_text_2_icon_alignment',
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
              'default' => 'left',
              'toggle' => false,
              'condition' => [
                'title_enable_icon' => 'yes',
              ],
            ]
          );

        $this->end_controls_tab();

        // Tab: Text 3.
        $this->start_controls_tab(
          'title_text_3_tab_content',
          [
            'label' => esc_html__( 'Text 3', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'title_text_3',
            [
              'label' => esc_html__( 'Text 3', 'themeasy-lite' ),
              'show_label' => false,
              'type' => Controls_Manager::TEXTAREA,
              'placeholder' => esc_html__( 'Leave blank to hide', 'themeasy-lite' ),
              'dynamic' => ['active' => true],
            ]
          );

          $this->add_control(
            'title_text_3_icon',
            [
              'label' => esc_html__( 'Icon', 'themeasy-lite' ),
              'type' => Controls_Manager::ICONS,
              'default' => [
                'value' => 'ty-feather-layers',
                'library' => 'ty-feather',
              ],
              'condition' => [
                'title_enable_icon' => 'yes',
              ],
            ]
          );

          $this->add_control(
            'title_text_3_icon_alignment',
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
              'default' => 'left',
              'toggle' => false,
              'condition' => [
                'title_enable_icon' => 'yes',
              ],
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

      // 'save_default' keeps the key in the editor's save payload even when it
      // holds the default: Elementor drops default values there, and the
      // constructor reads a save without the key as one from before the control.
      $this->add_control(
        'title_flow',
        [
          'label' => esc_html__( 'Title Flow', 'themeasy-lite' ),
          'description' => esc_html__( 'How the title texts relate to each other. Inline merges them into one continuous sentence that wraps as a whole — the flow that lets an icon sit between words. Block gives each text its own line. Row keeps them side by side on a single line.', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'inline',
          'save_default' => true,
          'options' => [
            'inline' => esc_html__( 'Inline (one sentence)', 'themeasy-lite' ),
            'block' => esc_html__( 'Block (one line each)', 'themeasy-lite' ),
            'row' => esc_html__( 'Row (side by side)', 'themeasy-lite' ),
          ],
        ]
      );

      $this->add_control(
        'title_enable_icon',
        [
          'label' => esc_html__( 'Enable Icon', 'themeasy-lite' ),
          'description' => esc_html__( 'Mix icons into the title text. Each text can carry one icon, before or after it, and the icon flows and wraps with the words around it.', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => '',
        ]
      );

      $this->add_control(
        'title_icon_placement',
        [
          'label' => esc_html__( 'Icon Placement', 'themeasy-lite' ),
          'description' => esc_html__( 'Inline puts the icon in the sentence, so the text flows around it. Beside gives the icon its own column with the text block next to it — unavailable in the Inline flow, where the text is a single run.', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'inline',
          'options' => [
            'inline' => esc_html__( 'Inline with text', 'themeasy-lite' ),
            'beside' => esc_html__( 'Beside text', 'themeasy-lite' ),
          ],
          'condition' => [
            'title_enable_icon' => 'yes',
            'title_flow!' => 'inline',
          ],
        ]
      );

      $this->add_control(
        'title_html_tag',
        [
          'label' => esc_html__( 'HTML Tag', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'h2',
          'options' => [
            'h1' => 'H1',
            'h2' => 'H2',
            'h3' => 'H3',
            'h4' => 'H4',
            'h5' => 'H5',
            'h6' => 'H6',
          ],
        ]
      );

      // Description.
      $this->add_control(
        'description_heading_content',
        [
          'label' => esc_html__( 'Description', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'description',
        [
          'label' => esc_html__( 'Description', 'themeasy-lite' ),
          'show_label' => false,
          'type' => Controls_Manager::TEXTAREA,
          'default' => esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean lobortis magna et ipsum rutrum lobortis.', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'Leave blank to hide', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Layout
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'headline_layout_section',
        [
          'label' => esc_html__( 'Layout', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $this->add_control(
        'text_layout',
        [
          'label' => esc_html__( 'Text Layout', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'stacked',
          'options' => [
            'stacked' => esc_html__( 'Stacked', 'themeasy-lite' ),
            'split' => esc_html__( 'Split', 'themeasy-lite' ),
            'vertical-label' => esc_html__( 'Vertical Label', 'themeasy-lite' ),
          ],
        ]
      );

      $this->add_responsive_control(
        'max_width',
        [
          'label' => esc_html__( 'Max Width', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['%', 'px'],
          'range' => [
            '%' => ['min' => 10, 'max' => 100, 'step' => 1],
            'px' => ['min' => 10, 'max' => 2000, 'step' => 10],
          ],
          'default' => ['unit' => '%', 'size' => 100],
          'selectors' => [
            '{{WRAPPER}} .tms-headline' => 'max-width: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'vertical_spacing',
        [
          'label' => esc_html__( 'Vertical Spacing', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['rem', 'px'],
          'range' => [
            'rem' => ['min' => 0, 'max' => 10, 'step' => 0.1],
            'px' => ['min' => 0, 'max' => 100, 'step' => 1],
          ],
          'default' => ['unit' => 'rem', 'size' => 1.5],
          'selectors' => [
            '{{WRAPPER}} .tms-headline' => 'row-gap: {{SIZE}}{{UNIT}};',
            // Split reserves the band of the out-of-flow pre-title on the title
            // itself. The :has() gate keeps the reservation off a headline with
            // no pre-title rendered, where it would only be a dead band — it
            // mirrors the same gate on the widget stylesheet's 3rem fallback.
            '{{WRAPPER}} .tms-headline.tms-headline--split:has(> .tms-headline__pre-title) .tms-headline__title'
              => 'padding-top: calc( {{SIZE}}{{UNIT}} * 2);',
          ],
          'condition' => [
            'text_layout' => ['stacked', 'split'],
          ],
        ]
      );

      // Per-element rhythm, layered on the container gap above. Each slider is
      // the space BELOW its own element, so it ADDS to Vertical Spacing instead
      // of replacing it; negative values pull the pair back under the general
      // rhythm. Every slider ships empty, so an untouched one emits nothing at
      // all and the Margin controls in the Style tab keep the whole box.
      //
      // Stacked only, on purpose. In split and vertical-label the pre-title is
      // absolutely positioned at every breakpoint (a margin on it moves
      // nothing), and title/description share a single horizontal gap already
      // owned by Horizontal Spacing below -- one slider whose axis flips at the
      // 768px breakpoint reads worse than the four-sided Margin control those
      // layouts already have in the Style tab.
      $this->add_control(
        'individual_spacing_heading',
        [
          'label' => esc_html__( 'Individual Spacing', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
          'condition' => [
            'text_layout' => 'stacked',
          ],
        ]
      );

      $this->add_control(
        'individual_spacing_note',
        [
          'show_label' => false,
          'type' => Controls_Manager::RAW_HTML,
          'raw' => esc_html__( 'Adds to the Vertical Spacing above, below each element. Negative values tighten the gap.', 'themeasy-lite' ),
          'content_classes' => 'elementor-control-field-description no-margin',
          'condition' => [
            'text_layout' => 'stacked',
          ],
        ]
      );

      // The child selector is not decoration: at 0,3,0 it outranks the Margin
      // control in Style > Pre-Title (0,2,0), whose `margin` shorthand is
      // registered later and would otherwise land in the same generated rule
      // and reset this longhand. The two sliders below do the same, so the rule
      // is uniform: in Stacked the bottom side answers to Layout, the other
      // three to Style. Empty by default, so nothing is taken until asked.
      $this->add_responsive_control(
        'pre_title_spacing',
        [
          'label' => esc_html__( 'Pre-Title', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['rem', 'px'],
          'range' => [
            'rem' => ['min' => -5, 'max' => 10, 'step' => 0.1],
            'px' => ['min' => -50, 'max' => 100, 'step' => 1],
          ],
          'default' => ['unit' => 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-headline--stacked > .tms-headline__pre-title'
              => 'margin-bottom: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'text_layout' => 'stacked',
            'pre_title!' => '',
          ],
        ]
      );

      $this->add_responsive_control(
        'title_spacing',
        [
          'label' => esc_html__( 'Title', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['rem', 'px'],
          'range' => [
            'rem' => ['min' => -5, 'max' => 10, 'step' => 0.1],
            'px' => ['min' => -50, 'max' => 100, 'step' => 1],
          ],
          'default' => ['unit' => 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-headline--stacked > .tms-headline__title'
              => 'margin-bottom: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'text_layout' => 'stacked',
          ],
        ]
      );

      // The last element in the column, so this one is a trailing offset rather
      // than a gap between two texts: it detaches the block from whatever
      // follows without spending the widget's own Advanced > Margin.
      $this->add_responsive_control(
        'description_spacing',
        [
          'label' => esc_html__( 'Description', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['rem', 'px'],
          'range' => [
            'rem' => ['min' => -5, 'max' => 10, 'step' => 0.1],
            'px' => ['min' => -50, 'max' => 100, 'step' => 1],
          ],
          'default' => ['unit' => 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-headline--stacked > .tms-headline__description'
              => 'margin-bottom: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'text_layout' => 'stacked',
            'description!' => '',
          ],
        ]
      );

      $this->add_responsive_control(
        'horizontal_spacing',
        [
          'label' => esc_html__( 'Horizontal Spacing', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['rem', 'px'],
          'range' => [
            'rem' => ['min' => 0, 'max' => 10, 'step' => 0.1],
            'px' => ['min' => 0, 'max' => 100, 'step' => 1],
          ],
          'default' => ['unit' => 'rem', 'size' => 1.5],
          'condition' => [
            'text_layout' => ['split', 'vertical-label'],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-headline' => 'column-gap: {{SIZE}}{{UNIT}};',
            // The gutter exists to clear the rotated pre-title, so it is gated
            // on the pre-title being rendered — same reasoning as the split
            // reservation on vertical_spacing. Mirrors the stylesheet fallback.
            '{{WRAPPER}} .tms-headline.tms-headline--vertical-label:has(> .tms-headline__pre-title)'
              => 'padding-left: max( 1.25rem, {{SIZE}}{{UNIT}} );',
          ],
        ]
      );

      $this->add_responsive_control(
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
              'icon' => 'eicon-align-center-h',
            ],
            'flex-end' => [
              'title' => esc_html__( 'Right', 'themeasy-lite' ),
              'icon' => 'eicon-align-end-h',
            ],
          ],
          'default' => 'flex-start',
          'toggle' => false,
          'condition' => [
            'text_layout' => 'stacked',
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-headline__wrapper' => 'justify-content: {{VALUE}}; align-items: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'text_alignment',
        [
          'label' => esc_html__( 'Text Alignment', 'themeasy-lite' ),
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
          'toggle' => false,
          'selectors' => [
            '{{WRAPPER}} .tms-headline__wrapper' => 'text-align: {{VALUE}};',
            // The description rides the same align-self rule as the pre-title
            // and the title so a capped description (description_max_width)
            // keeps tracking them: a stretched flex item whose cross size is
            // clamped by max-width stays parked at the START of the cross axis,
            // which would leave a centered headline with a left-hugging
            // paragraph. Stacked only — the control is conditioned on it, and
            // Elementor honours control conditions when generating CSS
            // (get_active_controls() filters the style controls), so the split
            // and vertical-label rows keep their own flex-end alignment.
            '{{WRAPPER}} .tms-headline__pre-title,
             {{WRAPPER}} .tms-headline__title,
             {{WRAPPER}} .tms-headline__description' => 'align-self: {{VALUE}};',
            // Only the "Beside" icon placement makes the part a flex row, so it
            // is the only case that needs justify-content. An inline icon is a
            // box inside the text run and already answers to text-align above —
            // the old catch-all on every part holding an icon is what made the
            // alignment fight the flow.
            '{{WRAPPER}} .tms-headline .tms-headline__title--icon-beside,
             {{WRAPPER}} .tms-headline .tms-headline__title--icon-beside > .line' => 'justify-content: {{VALUE}};',
          ],
          'condition' => [
            'text_layout' => 'stacked',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Pre-Title
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'headline_pre_title_styles_section',
        [
          'label' => esc_html__( 'Pre-Title', 'themeasy-lite' ),
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
            '{{WRAPPER}} .tms-headline__pre-title' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'pre_title_background_color',
        [
          'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-headline__pre-title' => 'background-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'pre_title_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-headline__pre-title',
        ]
      );

      // Margin sits before Padding so the outer box comes first. Accepts
      // negative values, which is the point in the split and vertical-label
      // layouts: the pre-title is absolutely positioned there, so a negative
      // margin nudges it off its anchor without touching the layout spacing
      // controls.
      $this->add_control(
        'pre_title_margin',
        [
          'label' => esc_html__( 'Margin', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', '%', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-headline__pre-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'pre_title_padding',
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', '%', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-headline__pre-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Border::get_type(),
        [
          'name' => 'pre_title_border',
          'selector' => '{{WRAPPER}} .tms-headline__pre-title',
        ]
      );

      $this->add_control(
        'pre_title_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-headline__pre-title' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      // Underline.
      $this->add_control(
        'pre_title_underline_styles_heading',
        [
          'label' => esc_html__( 'Underline Styles', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'pre_title_underline_color',
        [
          'label' => esc_html__( 'Underline Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-headline__pre-title .tms-u' => '--tms-u-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'pre_title_underline_thickness',
        [
          'label' => esc_html__( 'Underline Thickness', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['rem', 'px'],
          'range' => [
            'rem' => ['min' => 0, 'max' => 10, 'step' => 0.1],
            'px' => ['min' => 1, 'max' => 100, 'step' => 1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-headline__pre-title .tms-u' => '--tms-u-height: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'pre_title_underline_offset',
        [
          'label' => esc_html__( 'Underline Offset', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['%', 'rem', 'px'],
          'range' => [
            '%' => ['min' => 0, 'max' => 100, 'step' => 1],
            'rem' => ['min' => 0, 'max' => 10, 'step' => 0.1],
            'px' => ['min' => 1, 'max' => 100, 'step' => 1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-headline__pre-title .tms-u' => '--tms-u-top: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Title
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'headline_title_styles_section',
        [
          'label' => esc_html__( 'Title', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_control(
        'title_note_style',
        [
          'show_label' => false,
          'type' => Controls_Manager::RAW_HTML,
          'raw' => esc_html__( 'Use these fields to mix and match styles. For example, you can set Text 1 to a solid font style and apply outline or italic effects to Text 2, allowing for versatile and visually appealing text combinations.', 'themeasy-lite' ),
          'content_classes' => 'elementor-control-field-description no-margin',
        ]
      );

      $this->start_controls_tabs( 'title_tabs_style' );

        // Tab: Text 1 Style.
        $this->start_controls_tab(
          'title_text_1_tab_style',
          [
            'label' => esc_html__( 'Text 1', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'title_text_1_style',
            [
              'label' => esc_html__( 'Text Style', 'themeasy-lite' ),
              'type' => Controls_Manager::SELECT,
              'default' => 'solid-fill',
              'options' => [
                'solid-fill' => esc_html__( 'Solid Fill', 'themeasy-lite' ),
                'outline' => esc_html__( 'Outline Only', 'themeasy-lite' ),
                'gradient' => esc_html__( 'Gradient Fill', 'themeasy-lite' ),
                'gradient-outline' => esc_html__( 'Gradient Outline', 'themeasy-lite' ),
                'masked-image' => esc_html__( 'Masked Image', 'themeasy-lite' ),
              ],
            ]
          );

          $this->add_control(
            'title_text_1_color',
            [
              'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-headline__title-1,
                 {{WRAPPER}} .tms-headline__title-1 *' => 'color: {{VALUE}};',
              ],
              'condition' => [
                'title_text_1_style' => ['solid-fill', 'outline', 'gradient-outline'],
              ],
            ]
          );

          $this->add_group_control(
            Group_Control_Background::get_type(),
            [
              'name' => 'title_text_1_gradient',
              'label' => esc_html__( 'Gradient', 'themeasy-lite' ),
              'types' => ['gradient'],
              'selector' => '{{WRAPPER}} .tms-headline__title-1, {{WRAPPER}} .tms-headline__title-1 *',
              'condition' => [
                'title_text_1_style' => ['gradient', 'gradient-outline'],
              ],
            ]
          );

          $this->add_responsive_control(
            'title_text_1_outline_width',
            [
              'label' => esc_html__( 'Outline Width', 'themeasy-lite' ),
              'type' => Controls_Manager::SLIDER,
              'size_units' => ['px'],
              'range' => [
                'px' => ['min' => 0.5, 'max' => 10, 'step' => 0.5],
              ],
              'default' => ['unit' => 'px', 'size' => 2],
              'selectors' => [
                '{{WRAPPER}} .tms-headline__title-1' => '-webkit-text-stroke-width: {{SIZE}}{{UNIT}}; padding-left: {{SIZE}}{{UNIT}}; padding-right: {{SIZE}}{{UNIT}};',
              ],
              'condition' => [
                'title_text_1_style' => ['outline'],
              ],
            ]
          );

          $this->add_responsive_control(
            'title_text_1_gradient_outline_width',
            [
              'label' => esc_html__( 'Outline Width', 'themeasy-lite' ),
              'type' => Controls_Manager::SLIDER,
              'size_units' => ['px'],
              'range' => [
                'px' => ['min' => 1, 'max' => 20, 'step' => 1],
              ],
              'default' => ['unit' => 'px', 'size' => 4],
              'selectors' => [
                '{{WRAPPER}} .tms-headline__title-1' => '-webkit-text-stroke-width: {{SIZE}}{{UNIT}}; padding-left: {{SIZE}}{{UNIT}}; padding-right: {{SIZE}}{{UNIT}};',
              ],
              'condition' => [
                'title_text_1_style' => ['gradient-outline'],
              ],
            ]
          );

          $this->add_responsive_control(
            'title_text_1_letter_spacing',
            [
              'label' => esc_html__( 'Letter Spacing', 'themeasy-lite' ),
              'type' => Controls_Manager::SLIDER,
              'size_units' => ['px'],
              'range' => [
                'px' => ['min' => 0, 'max' => 20, 'step' => 0.5],
              ],
              'default' => ['unit' => 'px', 'size' => 5],
              'selectors' => [
                '{{WRAPPER}} .tms-headline__title-1' => 'letter-spacing: {{SIZE}}{{UNIT}};',
              ],
              'condition' => [
                'title_text_1_style' => ['outline', 'gradient-outline'],
              ],
            ]
          );

          $this->add_control(
            'title_text_1_masked_image',
            [
              'label' => esc_html__( 'Select Image', 'themeasy-lite' ),
              'type' => Controls_Manager::MEDIA,
              'default' => [
                'url' => Utils::get_placeholder_image_src(),
              ],
                // No 'selectors': render() emits --tms-masked-image itself.
                // Elementor would splice this URL into the stylesheet unescaped
                // whenever the attachment id is empty (themeasy_media_css_url()).
              'condition' => [
                'title_text_1_style' => 'masked-image',
              ],
            ]
          );

          $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
              'name' => 'title_text_1_typography',
              'label' => esc_html__( 'Typography', 'themeasy-lite' ),
              // Default look = the synced "Display N" Kit preset, if the Kit has it; detach or switch in the popover.
              'global' => themeasy_display_typography_global( 3 ),
              'selector' => '{{WRAPPER}} .tms-headline__title-1',
              // In the Inline flow the parts are inline boxes inside the title
              // tag, whose own size and line height are the floor of every line
              // and size the space between two parts. So the tag follows text 1
              // there, or a title smaller than the site heading keeps the site's
              // leading. Only these two fields: any other one would reach texts 2
              // and 3 through inheritance.
              'fields_options' => [
                'font_size' => [
                  'selectors' => [
                    '{{SELECTOR}},
                     {{WRAPPER}} .tms-headline--title-inline .tms-headline__title' => 'font-size: {{SIZE}}{{UNIT}};',
                  ],
                ],
                'line_height' => [
                  'selectors' => [
                    '{{SELECTOR}},
                     {{WRAPPER}} .tms-headline--title-inline .tms-headline__title' => 'line-height: {{SIZE}}{{UNIT}};',
                  ],
                ],
              ],
            ]
          );

          $this->add_control(
            'title_text_1_margin',
            [
              'label' => esc_html__( 'Margin', 'themeasy-lite' ),
              'type' => Controls_Manager::DIMENSIONS,
              'size_units' => ['px', '%', 'rem'],
              'selectors' => [
                '{{WRAPPER}} .tms-headline__title-1' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
              ],
              'condition' => [
                // Vertical margin is inert on an inline box, so the control only
                // shows where the part is a block: the Block and Row flows. In the
                // Inline flow the rhythm belongs to Layout > Vertical Spacing.
                'title_flow!' => 'inline',
              ],
            ]
          );

          $this->add_control(
            'title_text_1_icon_color',
            [
              'label' => esc_html__( 'Icon Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'separator' => 'before',
              'selectors' => [
                '{{WRAPPER}} .tms-headline__title-1 .icon,
                 {{WRAPPER}} .tms-headline__title-1 .icon *,
                 {{WRAPPER}} .tms-headline__title-1 svg,
                 {{WRAPPER}} .tms-headline__title-1 svg *' => 'color: {{VALUE}};',
              ],
              'condition' => [
                'title_enable_icon' => 'yes',
              ],
            ]
          );

          $this->add_control(
            'title_text_1_icon_fill_color',
            [
              'label' => esc_html__( 'Icon Fill Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-headline__title-1 svg' => 'fill: {{VALUE}};',
              ],
              'conditions' => [
                'terms' => [
                  ['name' => 'title_enable_icon', 'operator' => '==', 'value' => 'yes'],
                  ['name' => 'title_text_1_icon[library]', 'operator' => 'in', 'value' => themeasy_svg_icon_condition_libraries()],
                ],
              ],
            ]
          );

          $this->add_control(
            'title_text_1_icon_stroke_color',
            [
              'label' => esc_html__( 'Icon Stroke Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-headline__title-1 svg' => 'stroke: {{VALUE}};',
              ],
              'conditions' => [
                'terms' => [
                  ['name' => 'title_enable_icon', 'operator' => '==', 'value' => 'yes'],
                  ['name' => 'title_text_1_icon[library]', 'operator' => 'in', 'value' => themeasy_svg_icon_condition_libraries()],
                ],
              ],
            ]
          );

          $this->add_responsive_control(
            'title_text_1_icon_size',
            [
              'label' => esc_html__( 'Icon Size', 'themeasy-lite' ),
              'type' => Controls_Manager::SLIDER,
              'size_units' => ['px', 'rem', 'em'],
              'range' => [
                'px' => ['min' => 0, 'max' => 200, 'step' => 1],
                'rem' => ['min' => 0, 'max' => 15, 'step' => 0.1],
                'em' => ['min' => 0, 'max' => 15, 'step' => 0.1],
              ],
              'selectors' => [
                // The size drives a custom property on the PART, not font-size on
                // the glyph: the wrapper needs the value too, to keep its layout box
                // pinned to the text cap zone while the glyph paints at any scale
                // (see the leading note in headline.min.css). em resolves against
                // the title text, so 1em is exactly cap-to-cap with the type.
                '{{WRAPPER}} .tms-headline__title-1' => '--tms-title-icon-size: {{SIZE}}{{UNIT}};',
              ],
              'condition' => [
                'title_enable_icon' => 'yes',
              ],
            ]
          );

          $this->add_responsive_control(
            'title_text_1_icon_thickness',
            [
              'label' => esc_html__( 'Icon Thickness', 'themeasy-lite' ),
              'type' => Controls_Manager::SLIDER,
              'size_units' => ['px'],
              'range' => [
                'px' => ['min' => 0.5, 'max' => 10, 'step' => 0.5],
              ],
              'default' => ['unit' => 'px', 'size' => 2],
              'selectors' => [
                '{{WRAPPER}} .tms-headline__title-1 svg,
                 {{WRAPPER}} .tms-headline__title-1 svg *' => 'stroke-width: {{SIZE}}{{UNIT}};',
              ],
              'conditions' => [
                'terms' => [
                  ['name' => 'title_enable_icon', 'operator' => '==', 'value' => 'yes'],
                  ['name' => 'title_text_1_icon[library]', 'operator' => 'in', 'value' => themeasy_svg_icon_condition_libraries()],
                ],
              ],
            ]
          );

        $this->end_controls_tab();

        // Tab: Text 2 Style.
        $this->start_controls_tab(
          'title_text_2_tab_style',
          [
            'label' => esc_html__( 'Text 2', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'title_text_2_style',
            [
              'label' => esc_html__( 'Text Style', 'themeasy-lite' ),
              'type' => Controls_Manager::SELECT,
              'default' => 'solid-fill',
              'options' => [
                'solid-fill' => esc_html__( 'Solid Fill', 'themeasy-lite' ),
                'outline' => esc_html__( 'Outline Only', 'themeasy-lite' ),
                'gradient' => esc_html__( 'Gradient Fill', 'themeasy-lite' ),
                'gradient-outline' => esc_html__( 'Gradient Outline', 'themeasy-lite' ),
                'masked-image' => esc_html__( 'Masked Image', 'themeasy-lite' ),
              ],
            ]
          );

          $this->add_control(
            'title_text_2_color',
            [
              'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-headline__title-2,
                 {{WRAPPER}} .tms-headline__title-2 *' => 'color: {{VALUE}};',
              ],
              'condition' => [
                'title_text_2_style' => ['solid-fill', 'outline', 'gradient-outline'],
              ],
            ]
          );

          $this->add_group_control(
            Group_Control_Background::get_type(),
            [
              'name' => 'title_text_2_gradient',
              'label' => esc_html__( 'Gradient', 'themeasy-lite' ),
              'types' => ['gradient'],
              'selector' => '{{WRAPPER}} .tms-headline__title-2, {{WRAPPER}} .tms-headline__title-2 *',
              'condition' => [
                'title_text_2_style' => ['gradient', 'gradient-outline'],
              ],
            ]
          );

          $this->add_responsive_control(
            'title_text_2_outline_width',
            [
              'label' => esc_html__( 'Outline Width', 'themeasy-lite' ),
              'type' => Controls_Manager::SLIDER,
              'size_units' => ['px'],
              'range' => [
                'px' => ['min' => 0.5, 'max' => 10, 'step' => 0.5],
              ],
              'default' => ['unit' => 'px', 'size' => 2],
              'selectors' => [
                '{{WRAPPER}} .tms-headline__title-2' => '-webkit-text-stroke-width: {{SIZE}}{{UNIT}}; padding-left: {{SIZE}}{{UNIT}}; padding-right: {{SIZE}}{{UNIT}};',
              ],
              'condition' => [
                'title_text_2_style' => ['outline'],
              ],
            ]
          );

          $this->add_responsive_control(
            'title_text_2_gradient_outline_width',
            [
              'label' => esc_html__( 'Outline Width', 'themeasy-lite' ),
              'type' => Controls_Manager::SLIDER,
              'size_units' => ['px'],
              'range' => [
                'px' => ['min' => 1, 'max' => 20, 'step' => 1],
              ],
              'default' => ['unit' => 'px', 'size' => 4],
              'selectors' => [
                '{{WRAPPER}} .tms-headline__title-2' => '-webkit-text-stroke-width: {{SIZE}}{{UNIT}}; padding-left: {{SIZE}}{{UNIT}}; padding-right: {{SIZE}}{{UNIT}};',
              ],
              'condition' => [
                'title_text_2_style' => ['gradient-outline'],
              ],
            ]
          );

          $this->add_responsive_control(
            'title_text_2_letter_spacing',
            [
              'label' => esc_html__( 'Letter Spacing', 'themeasy-lite' ),
              'type' => Controls_Manager::SLIDER,
              'size_units' => ['px'],
              'range' => [
                'px' => ['min' => 0, 'max' => 20, 'step' => 0.5],
              ],
              'default' => ['unit' => 'px', 'size' => 5],
              'selectors' => [
                '{{WRAPPER}} .tms-headline__title-2' => 'letter-spacing: {{SIZE}}{{UNIT}};',
              ],
              'condition' => [
                'title_text_2_style' => ['outline', 'gradient-outline'],
              ],
            ]
          );

          $this->add_control(
            'title_text_2_masked_image',
            [
              'label' => esc_html__( 'Select Image', 'themeasy-lite' ),
              'type' => Controls_Manager::MEDIA,
              'default' => [
                'url' => Utils::get_placeholder_image_src(),
              ],
                // No 'selectors': render() emits --tms-masked-image itself.
                // Elementor would splice this URL into the stylesheet unescaped
                // whenever the attachment id is empty (themeasy_media_css_url()).
              'condition' => [
                'title_text_2_style' => 'masked-image',
              ],
            ]
          );

          $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
              'name' => 'title_text_2_typography',
              'label' => esc_html__( 'Typography', 'themeasy-lite' ),
              // Default look = the synced "Display N" Kit preset, if the Kit has it; detach or switch in the popover.
              'global' => themeasy_display_typography_global( 3 ),
              'selector' => '{{WRAPPER}} .tms-headline__title-2',
            ]
          );

          $this->add_control(
            'title_text_2_margin',
            [
              'label' => esc_html__( 'Margin', 'themeasy-lite' ),
              'type' => Controls_Manager::DIMENSIONS,
              'size_units' => ['px', '%', 'rem'],
              'selectors' => [
                '{{WRAPPER}} .tms-headline__title-2' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
              ],
              'condition' => [
                // Vertical margin is inert on an inline box, so the control only
                // shows where the part is a block: the Block and Row flows. In the
                // Inline flow the rhythm belongs to Layout > Vertical Spacing.
                'title_flow!' => 'inline',
              ],
            ]
          );

          $this->add_control(
            'title_text_2_icon_color',
            [
              'label' => esc_html__( 'Icon Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'separator' => 'before',
              'selectors' => [
                '{{WRAPPER}} .tms-headline__title-2 .icon,
                 {{WRAPPER}} .tms-headline__title-2 .icon *,
                 {{WRAPPER}} .tms-headline__title-2 svg,
                 {{WRAPPER}} .tms-headline__title-2 svg *' => 'color: {{VALUE}};',
              ],
              'condition' => [
                'title_enable_icon' => 'yes',
              ],
            ]
          );

          $this->add_control(
            'title_text_2_icon_fill_color',
            [
              'label' => esc_html__( 'Icon Fill Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-headline__title-2 svg' => 'fill: {{VALUE}};',
              ],
              'conditions' => [
                'terms' => [
                  ['name' => 'title_enable_icon', 'operator' => '==', 'value' => 'yes'],
                  ['name' => 'title_text_2_icon[library]', 'operator' => 'in', 'value' => themeasy_svg_icon_condition_libraries()],
                ],
              ],
            ]
          );

          $this->add_control(
            'title_text_2_icon_stroke_color',
            [
              'label' => esc_html__( 'Icon Stroke Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-headline__title-2 svg' => 'stroke: {{VALUE}};',
              ],
              'conditions' => [
                'terms' => [
                  ['name' => 'title_enable_icon', 'operator' => '==', 'value' => 'yes'],
                  ['name' => 'title_text_2_icon[library]', 'operator' => 'in', 'value' => themeasy_svg_icon_condition_libraries()],
                ],
              ],
            ]
          );

          $this->add_responsive_control(
            'title_text_2_icon_size',
            [
              'label' => esc_html__( 'Icon Size', 'themeasy-lite' ),
              'type' => Controls_Manager::SLIDER,
              'size_units' => ['px', 'rem', 'em'],
              'range' => [
                'px' => ['min' => 0, 'max' => 200, 'step' => 1],
                'rem' => ['min' => 0, 'max' => 15, 'step' => 0.1],
                'em' => ['min' => 0, 'max' => 15, 'step' => 0.1],
              ],
              'selectors' => [
                // The size drives a custom property on the PART, not font-size on
                // the glyph: the wrapper needs the value too, to keep its layout box
                // pinned to the text cap zone while the glyph paints at any scale
                // (see the leading note in headline.min.css). em resolves against
                // the title text, so 1em is exactly cap-to-cap with the type.
                '{{WRAPPER}} .tms-headline__title-2' => '--tms-title-icon-size: {{SIZE}}{{UNIT}};',
              ],
              'condition' => [
                'title_enable_icon' => 'yes',
              ],
            ]
          );

          $this->add_responsive_control(
            'title_text_2_icon_thickness',
            [
              'label' => esc_html__( 'Icon Thickness', 'themeasy-lite' ),
              'type' => Controls_Manager::SLIDER,
              'size_units' => ['px'],
              'range' => [
                'px' => ['min' => 0.5, 'max' => 10, 'step' => 0.5],
              ],
              'default' => ['unit' => 'px', 'size' => 2],
              'selectors' => [
                '{{WRAPPER}} .tms-headline__title-2 svg,
                 {{WRAPPER}} .tms-headline__title-2 svg *' => 'stroke-width: {{SIZE}}{{UNIT}};',
              ],
              'conditions' => [
                'terms' => [
                  ['name' => 'title_enable_icon', 'operator' => '==', 'value' => 'yes'],
                  ['name' => 'title_text_2_icon[library]', 'operator' => 'in', 'value' => themeasy_svg_icon_condition_libraries()],
                ],
              ],
            ]
          );

        $this->end_controls_tab();

        // Tab: Text 3 Style.
        $this->start_controls_tab(
          'title_text_3_tab_style',
          [
            'label' => esc_html__( 'Text 3', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'title_text_3_style',
            [
              'label' => esc_html__( 'Text Style', 'themeasy-lite' ),
              'type' => Controls_Manager::SELECT,
              'default' => 'solid-fill',
              'options' => [
                'solid-fill' => esc_html__( 'Solid Fill', 'themeasy-lite' ),
                'outline' => esc_html__( 'Outline Only', 'themeasy-lite' ),
                'gradient' => esc_html__( 'Gradient Fill', 'themeasy-lite' ),
                'gradient-outline' => esc_html__( 'Gradient Outline', 'themeasy-lite' ),
                'masked-image' => esc_html__( 'Masked Image', 'themeasy-lite' ),
              ],
            ]
          );

          $this->add_control(
            'title_text_3_color',
            [
              'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-headline__title-3,
                 {{WRAPPER}} .tms-headline__title-3 *' => 'color: {{VALUE}};',
              ],
              'condition' => [
                'title_text_3_style' => ['solid-fill', 'outline', 'gradient-outline'],
              ],
            ]
          );

          $this->add_group_control(
            Group_Control_Background::get_type(),
            [
              'name' => 'title_text_3_gradient',
              'label' => esc_html__( 'Gradient', 'themeasy-lite' ),
              'types' => ['gradient'],
              'selector' => '{{WRAPPER}} .tms-headline__title-3, {{WRAPPER}} .tms-headline__title-3 *',
              'condition' => [
                'title_text_3_style' => ['gradient', 'gradient-outline'],
              ],
            ]
          );

          $this->add_responsive_control(
            'title_text_3_outline_width',
            [
              'label' => esc_html__( 'Outline Width', 'themeasy-lite' ),
              'type' => Controls_Manager::SLIDER,
              'size_units' => ['px'],
              'range' => [
                'px' => ['min' => 0.5, 'max' => 10, 'step' => 0.5],
              ],
              'default' => ['unit' => 'px', 'size' => 2],
              'selectors' => [
                '{{WRAPPER}} .tms-headline__title-3' => '-webkit-text-stroke-width: {{SIZE}}{{UNIT}}; padding-left: {{SIZE}}{{UNIT}}; padding-right: {{SIZE}}{{UNIT}};',
              ],
              'condition' => [
                'title_text_3_style' => ['outline'],
              ],
            ]
          );

          $this->add_responsive_control(
            'title_text_3_gradient_outline_width',
            [
              'label' => esc_html__( 'Outline Width', 'themeasy-lite' ),
              'type' => Controls_Manager::SLIDER,
              'size_units' => ['px'],
              'range' => [
                'px' => ['min' => 1, 'max' => 20, 'step' => 1],
              ],
              'default' => ['unit' => 'px', 'size' => 4],
              'selectors' => [
                '{{WRAPPER}} .tms-headline__title-3' => '-webkit-text-stroke-width: {{SIZE}}{{UNIT}}; padding-left: {{SIZE}}{{UNIT}}; padding-right: {{SIZE}}{{UNIT}};',
              ],
              'condition' => [
                'title_text_3_style' => ['gradient-outline'],
              ],
            ]
          );

          $this->add_responsive_control(
            'title_text_3_letter_spacing',
            [
              'label' => esc_html__( 'Letter Spacing', 'themeasy-lite' ),
              'type' => Controls_Manager::SLIDER,
              'size_units' => ['px'],
              'range' => [
                'px' => ['min' => 0, 'max' => 20, 'step' => 0.5],
              ],
              'default' => ['unit' => 'px', 'size' => 5],
              'selectors' => [
                '{{WRAPPER}} .tms-headline__title-3' => 'letter-spacing: {{SIZE}}{{UNIT}};',
              ],
              'condition' => [
                'title_text_3_style' => ['outline', 'gradient-outline'],
              ],
            ]
          );

          $this->add_control(
            'title_text_3_masked_image',
            [
              'label' => esc_html__( 'Select Image', 'themeasy-lite' ),
              'type' => Controls_Manager::MEDIA,
              'default' => [
                'url' => Utils::get_placeholder_image_src(),
              ],
                // No 'selectors': render() emits --tms-masked-image itself.
                // Elementor would splice this URL into the stylesheet unescaped
                // whenever the attachment id is empty (themeasy_media_css_url()).
              'condition' => [
                'title_text_3_style' => 'masked-image',
              ],
            ]
          );

          $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
              'name' => 'title_text_3_typography',
              'label' => esc_html__( 'Typography', 'themeasy-lite' ),
              // Default look = the synced "Display N" Kit preset, if the Kit has it; detach or switch in the popover.
              'global' => themeasy_display_typography_global( 3 ),
              'selector' => '{{WRAPPER}} .tms-headline__title-3',
            ]
          );

          $this->add_control(
            'title_text_3_margin',
            [
              'label' => esc_html__( 'Margin', 'themeasy-lite' ),
              'type' => Controls_Manager::DIMENSIONS,
              'size_units' => ['px', '%', 'rem'],
              'selectors' => [
                '{{WRAPPER}} .tms-headline__title-3' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
              ],
              'condition' => [
                // Vertical margin is inert on an inline box, so the control only
                // shows where the part is a block: the Block and Row flows. In the
                // Inline flow the rhythm belongs to Layout > Vertical Spacing.
                'title_flow!' => 'inline',
              ],
            ]
          );

          $this->add_control(
            'title_text_3_icon_color',
            [
              'label' => esc_html__( 'Icon Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'separator' => 'before',
              'selectors' => [
                '{{WRAPPER}} .tms-headline__title-3 .icon,
                 {{WRAPPER}} .tms-headline__title-3 .icon *,
                 {{WRAPPER}} .tms-headline__title-3 svg,
                 {{WRAPPER}} .tms-headline__title-3 svg *' => 'color: {{VALUE}};',
              ],
              'condition' => [
                'title_enable_icon' => 'yes',
              ],
            ]
          );

          $this->add_control(
            'title_text_3_icon_fill_color',
            [
              'label' => esc_html__( 'Icon Fill Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-headline__title-3 svg' => 'fill: {{VALUE}};',
              ],
              'conditions' => [
                'terms' => [
                  ['name' => 'title_enable_icon', 'operator' => '==', 'value' => 'yes'],
                  ['name' => 'title_text_3_icon[library]', 'operator' => 'in', 'value' => themeasy_svg_icon_condition_libraries()],
                ],
              ],
            ]
          );

          $this->add_control(
            'title_text_3_icon_stroke_color',
            [
              'label' => esc_html__( 'Icon Stroke Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-headline__title-3 svg' => 'stroke: {{VALUE}};',
              ],
              'conditions' => [
                'terms' => [
                  ['name' => 'title_enable_icon', 'operator' => '==', 'value' => 'yes'],
                  ['name' => 'title_text_3_icon[library]', 'operator' => 'in', 'value' => themeasy_svg_icon_condition_libraries()],
                ],
              ],
            ]
          );

          $this->add_responsive_control(
            'title_text_3_icon_size',
            [
              'label' => esc_html__( 'Icon Size', 'themeasy-lite' ),
              'type' => Controls_Manager::SLIDER,
              'size_units' => ['px', 'rem', 'em'],
              'range' => [
                'px' => ['min' => 0, 'max' => 200, 'step' => 1],
                'rem' => ['min' => 0, 'max' => 15, 'step' => 0.1],
                'em' => ['min' => 0, 'max' => 15, 'step' => 0.1],
              ],
              'selectors' => [
                // The size drives a custom property on the PART, not font-size on
                // the glyph: the wrapper needs the value too, to keep its layout box
                // pinned to the text cap zone while the glyph paints at any scale
                // (see the leading note in headline.min.css). em resolves against
                // the title text, so 1em is exactly cap-to-cap with the type.
                '{{WRAPPER}} .tms-headline__title-3' => '--tms-title-icon-size: {{SIZE}}{{UNIT}};',
              ],
              'condition' => [
                'title_enable_icon' => 'yes',
              ],
            ]
          );

          $this->add_responsive_control(
            'title_text_3_icon_thickness',
            [
              'label' => esc_html__( 'Icon Thickness', 'themeasy-lite' ),
              'type' => Controls_Manager::SLIDER,
              'size_units' => ['px'],
              'range' => [
                'px' => ['min' => 0.5, 'max' => 10, 'step' => 0.5],
              ],
              'default' => ['unit' => 'px', 'size' => 2],
              'selectors' => [
                '{{WRAPPER}} .tms-headline__title-3 svg,
                 {{WRAPPER}} .tms-headline__title-3 svg *' => 'stroke-width: {{SIZE}}{{UNIT}};',
              ],
              'conditions' => [
                'terms' => [
                  ['name' => 'title_enable_icon', 'operator' => '==', 'value' => 'yes'],
                  ['name' => 'title_text_3_icon[library]', 'operator' => 'in', 'value' => themeasy_svg_icon_condition_libraries()],
                ],
              ],
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

      // Icon placement. Shared by the three parts on purpose: where a glyph sits
      // against the baseline is a typographic contract for the whole title, not a
      // per-segment decoration — three copies of it would only invite drift.
      $this->add_control(
        'title_icon_styles_heading',
        [
          'label' => esc_html__( 'Icon Placement', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
          'condition' => [
            'title_enable_icon' => 'yes',
          ],
        ]
      );

      $this->add_control(
        'title_icon_align',
        [
          'label' => esc_html__( 'Vertical Align', 'themeasy-lite' ),
          'description' => esc_html__( 'Where the glyph sits against the text. Cap Height optically centers it on the capitals, which is what reads as typographic next to a heading. None of the options can change the line height.', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'cap',
          'options' => [
            'cap' => esc_html__( 'Cap Height (centered)', 'themeasy-lite' ),
            'baseline' => esc_html__( 'Baseline', 'themeasy-lite' ),
            'middle' => esc_html__( 'Middle (x-height)', 'themeasy-lite' ),
            'top' => esc_html__( 'Cap Top', 'themeasy-lite' ),
          ],
          'condition' => [
            'title_enable_icon' => 'yes',
          ],
        ]
      );

      $this->add_responsive_control(
        'title_icon_offset',
        [
          'label' => esc_html__( 'Vertical Offset', 'themeasy-lite' ),
          'description' => esc_html__( 'Nudge the glyph up or down from the alignment above. Negative moves it up. Purely visual — the line height never follows it.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          // em is named in the default so Elementor keeps this range instead of
          // falling back to its own 0-100 px one (no 'px' first in the list).
          'size_units' => ['em', 'px', 'rem'],
          'range' => [
            'em' => ['min' => -1, 'max' => 1, 'step' => 0.01],
            'px' => ['min' => -100, 'max' => 100, 'step' => 1],
            'rem' => ['min' => -5, 'max' => 5, 'step' => 0.05],
          ],
          'default' => ['unit' => 'em'],
          'selectors' => [
            '{{WRAPPER}} .tms-headline__title' => '--tms-title-icon-offset: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'title_enable_icon' => 'yes',
          ],
        ]
      );

      $this->add_responsive_control(
        'title_icon_gap',
        [
          'label' => esc_html__( 'Icon Gap', 'themeasy-lite' ),
          'description' => esc_html__( 'Space held on each side of the glyph, so it breathes inside the sentence.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['em', 'px', 'rem'],
          'range' => [
            'em' => ['min' => 0, 'max' => 2, 'step' => 0.01],
            'px' => ['min' => 0, 'max' => 100, 'step' => 1],
            'rem' => ['min' => 0, 'max' => 5, 'step' => 0.05],
          ],
          'default' => ['unit' => 'em'],
          'selectors' => [
            '{{WRAPPER}} .tms-headline__title' => '--tms-title-icon-gap: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'title_enable_icon' => 'yes',
          ],
        ]
      );

      // Underline.
      $this->add_control(
        'title_underline_styles_heading',
        [
          'label' => esc_html__( 'Underline Styles', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'title_underline_color',
        [
          'label' => esc_html__( 'Underline Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-headline__title .tms-u' => '--tms-u-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'title_underline_thickness',
        [
          'label' => esc_html__( 'Underline Thickness', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['rem', 'px'],
          'range' => [
            'rem' => ['min' => 0, 'max' => 10, 'step' => 0.1],
            'px' => ['min' => 1, 'max' => 100, 'step' => 1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-headline__title .tms-u' => '--tms-u-height: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'title_underline_offset',
        [
          'label' => esc_html__( 'Underline Offset', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['%', 'rem', 'px'],
          'range' => [
            '%' => ['min' => 0, 'max' => 100, 'step' => 1],
            'rem' => ['min' => 0, 'max' => 10, 'step' => 0.1],
            'px' => ['min' => 1, 'max' => 100, 'step' => 1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-headline__title .tms-u' => '--tms-u-top: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Description
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'headline_description_styles_section',
        [
          'label' => esc_html__( 'Description', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'description!' => '',
          ],
        ]
      );

      $this->add_control(
        'description_text_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-headline__description' => 'color: {{VALUE}};',
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
            '{{WRAPPER}} .tms-headline__description' => 'font-size: var(--tms-font-size-{{VALUE}});',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'description_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-headline__description',
        ]
      );

      // Margin before Max Width so the outer box comes first, mirroring the
      // Pre-Title section and the title parts. Responsive because the creative
      // use for it is an asymmetric indent -- a left gutter under a full-bleed
      // title -- which has to collapse on a phone. In the stacked layout the
      // bottom side is owned by Description in Layout > Individual Spacing,
      // which outranks this shorthand by design; the other three always answer
      // here, in every layout.
      $this->add_responsive_control(
        'description_margin',
        [
          'label' => esc_html__( 'Margin', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', '%', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-headline__description' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      // Caps the paragraph on its own axis, on top of the block-wide Max Width
      // in Layout: the measure that keeps a long title full-bleed while the
      // description breaks where the copy reads best. Left unset by default so
      // the description keeps inheriting the block width. In the split and
      // vertical-label rows it caps the flex item instead of its flex-basis,
      // which is the same intent on the horizontal axis.
      $this->add_responsive_control(
        'description_max_width',
        [
          'label' => esc_html__( 'Max Width', 'themeasy-lite' ),
          'description' => esc_html__( 'Caps the description only, on top of the block Max Width in Layout. Use "ch" to break the paragraph on a character measure.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem', 'ch', '%'],
          'range' => [
            'px' => ['min' => 200, 'max' => 1200, 'step' => 1],
            'rem' => ['min' => 10, 'max' => 80, 'step' => 0.5],
            'ch' => ['min' => 20, 'max' => 120, 'step' => 1],
            '%' => ['min' => 10, 'max' => 100, 'step' => 1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-headline__description' => 'max-width: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      // Underline.
      $this->add_control(
        'description_underline_styles_heading',
        [
          'label' => esc_html__( 'Underline Styles', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'description_underline_text_color',
        [
          'label' => esc_html__( 'Underline Text Color', 'themeasy-lite' ),
          'description' => esc_html__(
            'Color of the words wrapped in an underline. Leave empty to keep the Text Color.',
            'themeasy-lite'
          ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-headline__description .tms-u' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'description_underline_color',
        [
          'label' => esc_html__( 'Underline Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-headline__description .tms-u' => '--tms-u-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'description_underline_thickness',
        [
          'label' => esc_html__( 'Underline Thickness', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['rem', 'px'],
          'range' => [
            'rem' => ['min' => 0, 'max' => 10, 'step' => 0.1],
            'px' => ['min' => 1, 'max' => 100, 'step' => 1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-headline__description .tms-u' => '--tms-u-height: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'description_underline_offset',
        [
          'label' => esc_html__( 'Underline Offset', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['%', 'rem', 'px'],
          'range' => [
            '%' => ['min' => 0, 'max' => 100, 'step' => 1],
            'rem' => ['min' => 0, 'max' => 10, 'step' => 0.1],
            'px' => ['min' => 1, 'max' => 100, 'step' => 1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-headline__description .tms-u' => '--tms-u-top: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

  }

  /**
   * True while Elementor renders the plain-text copy of the document.
   *
   * @var bool
   */
  private $plain_render = false;

  /**
   * Render the plain-text copy Elementor saves as the post_content.
   *
   * That copy is this widget's HTML with the tags stripped, and it is what
   * search results, excerpts, feeds and the demo export read. Outside the
   * Inline flow the title parts print adjacent, so the copy fused the last
   * word of one part with the first of the next ("Tell us whereyou will walk",
   * backlog #502). Here the parts are joined by a space; the page is unchanged.
   *
   * @return void
   */
  public function render_plain_content() {
    $this->plain_render = true;
    parent::render_plain_content();
    $this->plain_render = false;
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
    $pre_title = $settings['pre_title'] ?? '';

    // The kicker is typographic decoration, not document structure, so its
    // allowlist widens past the headings and defaults to a neutral inline tag.
    $pre_title_tag = themeasy_sanitize_heading_tag(
      $settings['pre_title_html_tag'] ?? 'span',
      'span',
      ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div', 'span']
    );
    $title_tag = themeasy_sanitize_heading_tag( $settings['title_html_tag'] ?? 'h2', 'h2' );
    $description = $settings['description'] ?? '';
    $text_layout = $settings['text_layout'] ?? 'stacked';

    $title_enable_icon = ( $settings['title_enable_icon'] ?? '' ) === 'yes';

    // Flow and icon placement. The constructor guarantees a value for saved
    // instances; the ?? keeps a hand-built settings array honest.
    $title_flow = $settings['title_flow'] ?? 'inline';

    if ( !in_array( $title_flow, ['inline', 'block', 'row'], true ) ) {
      $title_flow = 'inline';
    }

    // "Beside" needs the part to be a block box, and in the Inline flow it is an
    // inline one, so the placement collapses back to inline there — the control
    // is conditioned away in that flow, but a stale saved value must not leak.
    $title_icon_placement = ( 'inline' !== $title_flow && ( $settings['title_icon_placement'] ?? '' ) === 'beside' )
      ? 'beside'
      : 'inline';

    $title_icon_align = $settings['title_icon_align'] ?? 'cap';

    if ( !in_array( $title_icon_align, ['cap', 'baseline', 'middle', 'top'], true ) ) {
      $title_icon_align = 'cap';
    }

    // ------------------------------------------------------------------------
    // Wrapper classes + attributes.
    // ------------------------------------------------------------------------
    $headline_classes = ['tms-headline'];

    if ( $text_layout ) {
      $headline_classes[] = sanitize_html_class( 'tms-headline--' . $text_layout );
    }

    // The Title Flow modifier lives on the container, not on the title: every
    // part-wide rule in headline.min.css reaches the parts through
    // [class*="tms-headline__title-"], which a `tms-headline__title--x`
    // modifier would match too. See the Title block in that file.
    $headline_classes[] = 'tms-headline--title-' . $title_flow;

    $headline_classes_output = implode( ' ', array_filter( $headline_classes ) );

    // ------------------------------------------------------------------------
    // Render attributes.
    // ------------------------------------------------------------------------
    $pre_title_classes = ['tms-headline__pre-title'];

    if ( '' !== $pre_title ) {
      $this->add_render_attribute( 'pre_title', 'class', implode( ' ', $pre_title_classes ) );
      $this->add_inline_editing_attributes( 'pre_title', 'basic' );
    }

    if ( '' !== $description ) {
      $this->add_render_attribute( 'description', 'class', 'tms-headline__description' );
      $this->add_inline_editing_attributes( 'description', 'advanced' );
    }

    // ------------------------------------------------------------------------
    // Output.
    // ------------------------------------------------------------------------
    ?>
    <div class="tms-headline__wrapper">
      <div class="<?php echo esc_attr( $headline_classes_output ); ?>">

        <?php
        // ---- Pre-Title output ----
        if ( '' !== $pre_title ) :
          ?>
          <<?php echo tag_escape( $pre_title_tag ); ?> <?php $this->print_render_attribute_string( 'pre_title' ); ?>><?php echo wp_kses( $pre_title, themeasy_get_kses_allowed_tags() ); ?></<?php echo tag_escape( $pre_title_tag ); ?>>
        <?php endif; ?>

        <?php
        // ---- Title parts output (1..3) ----
        $title_parts = [];

        for ( $i = 1; $i <= 3; $i++ ) :
          $text_key = 'title_text_' . $i;
          $text_value = $settings[$text_key] ?? '';

          if ( '' === $text_value ) {
            continue;
          }

          // Part settings.
          $text_style = $settings[$text_key . '_style'] ?? '';
          $text_icon = $settings[$text_key . '_icon'] ?? [];
          $text_icon_alignment = $settings[$text_key . '_icon_alignment'] ?? 'left';

          // Each part owns its own masked-image picture, so the token rides an
          // inline custom property on the part itself — never an Elementor
          // selector, which would reach the generated stylesheet unescaped
          // whenever the attachment id is empty (themeasy_media_css_url()).
          // Resolved before the classes are built: the paint clips the glyphs
          // out of the picture, so with no usable one the part would render
          // fully transparent. Degrade to solid ink instead.
          $masked_style = '';

          if ( 'masked-image' === $text_style ) {
            $masked_image = themeasy_media_css_url( $settings[$text_key . '_masked_image'] ?? null );

            if ( $masked_image ) {
              $masked_style = '--tms-masked-image: ' . $masked_image . ';';
            } else {
              $text_style = 'solid-fill';
            }
          }

          // Icon output.
          $icon_html = '';
          $has_icon = $title_enable_icon && !empty( $text_icon ) && !empty( $text_icon['value'] );

          if ( $has_icon ) {
            $icon_html = themeasy_render_icon_html( $text_icon, ['class' => 'icon', 'aria-hidden' => 'true'] );

            if ( $icon_html ) {
              // The wrapper is the icon's whole contract: it is the inline-level
              // box that flows with the words, its size is what keeps the line
              // height untouched. See the alignment model in headline.min.css.
              $icon_html = '<span class="icon-wrapper">' . $icon_html . '</span>';
            }
          }

          $icon_class = ( 'right' === $text_icon_alignment )
            ? 'tms-headline__title--icon-right'
            : 'tms-headline__title--icon-left';

          // Part classes.
          $part_classes = [
            'tms-headline__title-' . $i,
            'tms-text-style',
            $text_style ? 'tms-text-style--' . $text_style : '',
            $icon_class,
          ];

          if ( $has_icon ) {
            if ( 'beside' === $title_icon_placement ) {
              $part_classes[] = 'tms-headline__title--icon-beside';
            }

            if ( 'cap' !== $title_icon_align ) {
              $part_classes[] = 'tms-headline__title--icon-align-' . $title_icon_align;
            }
          }

          $part_classes_string = implode( ' ', array_filter( $part_classes ) );
          $this->add_render_attribute( $text_key, 'class', $part_classes_string );
          $this->add_inline_editing_attributes( $text_key, 'none' );

          if ( $masked_style ) {
            $this->add_render_attribute( $text_key, 'style', $masked_style );
          }

          $title_parts[] = [
            'key' => $text_key,
            'text' => $text_value,
            'icon' => $icon_html,
            'icon_right' => 'right' === $text_icon_alignment,
          ];
        endfor;

        if ( !empty( $title_parts ) ) :
          // In the Inline flow the parts are one sentence, so they are joined by
          // a real space: the Row flow's `gap` does not exist in inline layout,
          // and only actual whitespace both separates the words and gives the
          // line a place to break between two parts. The other flows keep the
          // parts adjacent — their own box already separates them, and a stray
          // text node would show up as an indent. The plain-text copy has no
          // boxes, so it always takes the space.
          $title_parts_glue = ( 'inline' === $title_flow || $this->plain_render ) ? ' ' : '';
          ?>
          <<?php echo tag_escape( $title_tag ); ?> class="tms-headline__title"><?php
            foreach ( $title_parts as $part_index => $part ) {
              if ( $part_index > 0 ) {
                echo esc_html( $title_parts_glue );
              }

              $this->print_title_part( $part );
            }
          ?></<?php echo tag_escape( $title_tag ); ?>>
        <?php endif; ?>

        <?php
        // ---- Description output ----
        if ( '' !== $description ) :
          ?>
          <p <?php $this->print_render_attribute_string( 'description' ); ?>><?php echo wp_kses( $description, themeasy_get_kses_allowed_tags() ); ?></p>
        <?php endif; ?>

      </div><!-- /.tms-headline -->
    </div><!-- /.tms-headline__wrapper -->
    <?php
  }

  /**
   * Prints one title part: its span, the sanitized text and the icon on its
   * side. No space is printed around the glyph: the breathing room is a margin
   * in CSS, which (unlike whitespace) is NOT a break opportunity, so the icon
   * can never be orphaned from its own word.
   *
   * @param array $part A part built in render(): 'key' (its render attribute
   *                    key), 'text', 'icon' and 'icon_right'.
   * @return void
   */
  private function print_title_part( array $part ): void {
    echo '<span ';
    $this->print_render_attribute_string( $part['key'] );
    echo '>';

    if ( !$part['icon_right'] ) {
      echo wp_kses( $part['icon'], themeasy_get_icon_kses_tags() );
    }

    echo wp_kses( $part['text'], themeasy_get_kses_allowed_tags() );

    if ( $part['icon_right'] ) {
      echo wp_kses( $part['icon'], themeasy_get_icon_kses_tags() );
    }

    echo '</span>';
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
      var allowedTags = [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ];

      // The kicker is typographic decoration, not document structure, so its
      // allowlist widens past the headings and defaults to a neutral inline tag.
      var allowedPreTitleTags = allowedTags.concat( [ 'p', 'div', 'span' ] );

      // Kses-parity sanitizer for user text in raw preview output. Falls back
      // to plain escaping when the shared helper is unavailable (safe — only
      // inline formatting is lost).
      var sanitizeInline = ( window.Themeasy && window.Themeasy.sanitizeInlineHtml )
        ? window.Themeasy.sanitizeInlineHtml
        : _.escape;

      var preTitle    = sanitizeInline( settings.pre_title || '' );
      var preTitleTag = allowedPreTitleTags.includes( settings.pre_title_html_tag )
        ? settings.pre_title_html_tag
        : 'span';
      var titleTag    = allowedTags.includes( settings.title_html_tag )
        ? settings.title_html_tag
        : 'h2';
      var description = sanitizeInline( settings.description || '' );
      var textLayout  = settings.text_layout || 'stacked';

      var titleEnableIcon = !! settings.title_enable_icon;

      // Flow and icon placement — mirrors render(). The editor has no
      // constructor migration to lean on for an instance it is building fresh,
      // so the same fallbacks are spelled out here.
      var titleFlow = [ 'inline', 'block', 'row' ].includes( settings.title_flow )
        ? settings.title_flow
        : 'inline';

      // "Beside" needs a block part, which the Inline flow does not have.
      var titleIconPlacement = ( titleFlow !== 'inline' && settings.title_icon_placement === 'beside' )
        ? 'beside'
        : 'inline';

      var titleIconAlign = [ 'cap', 'baseline', 'middle', 'top' ].includes( settings.title_icon_align )
        ? settings.title_icon_align
        : 'cap';

      // ------------------------------------------------------------------------
      // Wrapper classes + attributes.
      // ------------------------------------------------------------------------
      var headlineClasses = [ 'tms-headline' ];

      if ( textLayout ) {
        headlineClasses.push( 'tms-headline--' + textLayout );
      }

      // Mirror render(): the Title Flow modifier rides the container, because a
      // modifier on the title would also match the part-wide
      // [class*="tms-headline__title-"] selectors in headline.min.css.
      headlineClasses.push( 'tms-headline--title-' + titleFlow );

      var headlineClassesString = headlineClasses.join( ' ' );

      // ------------------------------------------------------------------------
      // Render attributes.
      // ------------------------------------------------------------------------
      var preTitleClasses = [ 'tms-headline__pre-title' ];

      if ( '' !== preTitle ) {
        view.addRenderAttribute( 'pre_title', 'class', preTitleClasses.join( ' ' ) );
        view.addInlineEditingAttributes( 'pre_title', 'basic' );
      }

      if ( '' !== description ) {
        view.addRenderAttribute( 'description', 'class', 'tms-headline__description' );
        view.addInlineEditingAttributes( 'description', 'advanced' );
      }
    #>

    <div class="tms-headline__wrapper">
      <div class="{{ headlineClassesString }}">

        <#
        // ---- Pre-Title output ----
        if ( '' !== preTitle ) {
          var preTitleAttrs = view.getRenderAttributeString( 'pre_title' );

          #>
          <{{ preTitleTag }} {{{ preTitleAttrs }}}>
            {{{ preTitle }}}
          </{{ preTitleTag }}>
        <# } #>

        <#
        // ---- Title parts output (1..3) ----
        var titleParts = [];

        for ( var i = 1; i <= 3; i++ ) {
          var textKey   = 'title_text_' + i;
          var textValue = sanitizeInline( settings[ textKey ] ? settings[ textKey ] : '' );

          if ( '' === textValue ) {
            continue;
          }

          // Part settings.
          var textStyle         = settings[ textKey + '_style' ] || '';
          var textIcon          = settings[ textKey + '_icon' ] || {};
          var textIconAlignment = settings[ textKey + '_icon_alignment' ] || 'left';

          // Icon output.
          var iconHtml = '';
          var hasIcon  = titleEnableIcon && textIcon && textIcon.value;

          if ( hasIcon ) {
            iconHtml = ( window.Themeasy && window.Themeasy.renderIconMarkup )
              ? window.Themeasy.renderIconMarkup( view, textIcon, null, { 'class': 'icon', 'aria-hidden': 'true' } )
              : '';

            if ( iconHtml ) {
              // Mirror render(): the wrapper is the icon's whole contract — the
              // inline box that flows with the words and the size that keeps the
              // line height untouched.
              iconHtml = '<span class="icon-wrapper">' + iconHtml + '</span>';
            }
          }

          // Mirror render(): no separating space around the glyph — the gap is a
          // margin in CSS, which is not a break opportunity.
          var iconClass = ( textIconAlignment === 'right' )
            ? 'tms-headline__title--icon-right'
            : 'tms-headline__title--icon-left';

          var textWithIcon = ( textIconAlignment === 'right' )
            ? ( textValue + iconHtml )
            : ( iconHtml + textValue );

          // Mirror render(): the part's picture rides an inline custom property,
          // so the editor preview keeps updating live without a selectors
          // binding. Resolved BEFORE the classes: with no usable picture the
          // masked paint would clip the glyphs out of nothing, so degrade.
          var maskedStyle = '';

          if ( textStyle === 'masked-image' ) {
            var maskedImage = Themeasy.mediaCssUrl( settings[ textKey + '_masked_image' ] );

            if ( maskedImage ) {
              maskedStyle = '--tms-masked-image: ' + maskedImage + ';';
            } else {
              textStyle = 'solid-fill';
            }
          }

          // Part classes.
          var partClasses = [
            'tms-headline__title-' + i,
            'tms-text-style',
            textStyle ? 'tms-text-style--' + textStyle : '',
            iconClass
          ];

          if ( hasIcon ) {
            if ( titleIconPlacement === 'beside' ) {
              partClasses.push( 'tms-headline__title--icon-beside' );
            }

            if ( titleIconAlign !== 'cap' ) {
              partClasses.push( 'tms-headline__title--icon-align-' + titleIconAlign );
            }
          }

          var partClassesString = partClasses.filter( Boolean ).join( ' ' );
          view.addRenderAttribute( textKey, 'class', partClassesString );
          view.addInlineEditingAttributes( textKey, 'none' );

          if ( maskedStyle ) {
            view.addRenderAttribute( textKey, 'style', maskedStyle );
          }

          var partHtml = '<span ' + view.getRenderAttributeString( textKey ) + '>' + textWithIcon + '</span>';

          titleParts.push( partHtml );
        }
        #>

        <#
        // Mirror render(): the Inline flow joins the parts with a real space, the
        // others keep them adjacent. The tag is written on ONE line on purpose —
        // the indented form used to wrap the parts in whitespace text nodes,
        // which flex layout discards but inline layout renders as a leading
        // indent, i.e. an editor-only divergence the moment the flow went inline.
        var titlePartsGlue = ( titleFlow === 'inline' ) ? ' ' : '';
        #>

        <# if ( titleParts.length ) { #>
          <{{ titleTag }} class="tms-headline__title">{{{ titleParts.join( titlePartsGlue ) }}}</{{ titleTag }}>
        <# } #>

        <#
        // ---- Description output ----
        if ( '' !== description ) {
          var descriptionAttrs = view.getRenderAttributeString( 'description' );

          #>
          <p {{{ descriptionAttrs }}}>{{{ description }}}</p>
        <# } #>

      </div><!-- /.tms-headline -->
    </div><!-- /.tms-headline__wrapper -->
    <?php
  }
}
