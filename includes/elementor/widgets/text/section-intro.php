<?php
/**
 * Themeasy Elementor Widget: Section Intro
 *
 * Composition-first opener for page sections: eyebrow (six visual styles +
 * optional icon), title with inline <em>/<mark> accent styling and a global
 * display type-scale, description, and up to two action buttons. Structure
 * multiplies across three orthogonal axes — five layouts (stacked, split,
 * offset eyebrow column, row with inline actions, vertical rail), six frame
 * treatments (top/bottom hairline, outline, panel, blueprint corner ticks)
 * and an adaptive divider.
 *
 * Every layout arranges the same two content groups (heading + content) on a
 * grid; the widget maps eyebrow/title/description/actions into those groups
 * per layout, so spacing stays exact with any field left empty. Pure CSS
 * structure with no widget JS: layouts collapse to a single column on
 * tablet/mobile.
 *
 * @package Themeasy
 * @since 1.0.0
 */

namespace Themeasy\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

/**
 * Responsible for registering controls and rendering the widget.
 */
class SectionIntro extends Widget_Base {
  public function get_name() {
    return 'themeasy-section-intro';
  }

  public function get_title() {
    return esc_html__( 'Section Intro', 'themeasy-lite' );
  }

  public function get_icon() {
    return 'eicon-t-letter';
  }

  public function get_custom_help_url() {
    return 'https://themeasy.co/help-center';
  }

  public function get_categories() {
    return ['themeasy_text_category', 'themeasy_content_category'];
  }

  public function get_keywords() {
    return ['themeasy', 'section intro', 'heading', 'eyebrow', 'kicker', 'intro', 'section header', 'title'];
  }

  public function get_style_depends() {
    return ['themeasy-shared', 'themeasy-section-intro'];
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
    // Content section: Content
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'section_intro_content_section',
        [
          'label' => esc_html__( 'Content', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      // Eyebrow.
      $this->add_control(
        'eyebrow_heading_content',
        [
          'label' => esc_html__( 'Eyebrow', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
        ]
      );

      $this->add_control(
        'eyebrow',
        [
          'label' => esc_html__( 'Eyebrow', 'themeasy-lite' ),
          'show_label' => false,
          'label_block' => true,
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'What we do', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'Leave blank to hide', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
        ]
      );

      $this->add_control(
        'eyebrow_style',
        [
          'label' => esc_html__( 'Eyebrow Style', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'dot',
          'options' => [
            'text' => esc_html__( 'Text', 'themeasy-lite' ),
            'pill' => esc_html__( 'Pill (Border)', 'themeasy-lite' ),
            'tag' => esc_html__( 'Tag (Background)', 'themeasy-lite' ),
            'line' => esc_html__( 'Line (Leading Dash)', 'themeasy-lite' ),
            'dot' => esc_html__( 'Dot (Status Marker)', 'themeasy-lite' ),
            'brackets' => esc_html__( 'Brackets (Technical Label)', 'themeasy-lite' ),
          ],
          'condition' => [
            'eyebrow!' => '',
          ],
        ]
      );

      $this->add_control(
        'eyebrow_icon',
        [
          'label' => esc_html__( 'Eyebrow Icon', 'themeasy-lite' ),
          'type' => Controls_Manager::ICONS,
          'default' => [],
          'condition' => [
            'eyebrow!' => '',
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
          'raw' => esc_html__(
            'Wrap words in <em> for an accent or <mark> for a highlight - style both under Style - Title Accent.',
            'themeasy-lite'
          ),
          'content_classes' => 'elementor-control-field-description no-margin',
        ]
      );

      $this->add_control(
        'title',
        [
          'label' => esc_html__( 'Title', 'themeasy-lite' ),
          'show_label' => false,
          'label_block' => true,
          'type' => Controls_Manager::TEXTAREA,
          'rows' => 3,
          'default' => __( 'Ideas that <em>move brands</em> forward', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'Leave blank to hide', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
        ]
      );

      $this->add_control(
        'title_tag',
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
            'div' => 'DIV',
          ],
          'condition' => [
            'title!' => '',
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
          'label_block' => true,
          'type' => Controls_Manager::TEXTAREA,
          'default' => esc_html__(
            'We pair strategy with craft to design digital experiences people remember and return to.',
            'themeasy-lite'
          ),
          'placeholder' => esc_html__( 'Leave blank to hide', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Buttons
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'section_intro_buttons_section',
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
        'arrow-right',
        'show_secondary_button'
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Layout
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'section_intro_layout_section',
        [
          'label' => esc_html__( 'Layout', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $this->add_control(
        'layout',
        [
          'label' => esc_html__( 'Layout', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'stacked',
          'options' => [
            'stacked' => esc_html__( 'Stacked', 'themeasy-lite' ),
            'split' => esc_html__( 'Split - Title | Content', 'themeasy-lite' ),
            'offset' => esc_html__( 'Offset - Eyebrow Column', 'themeasy-lite' ),
            'row' => esc_html__( 'Row - Title + Actions', 'themeasy-lite' ),
            'rail' => esc_html__( 'Vertical Rail - Eyebrow', 'themeasy-lite' ),
          ],
        ]
      );

      $this->add_responsive_control(
        'alignment',
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
          'default' => 'start',
          'toggle' => false,
          'selectors' => [
            '{{WRAPPER}} .tms-section-intro' => '--tms-section-intro-align: {{VALUE}}; text-align: {{VALUE}};',
          ],
          'condition' => [
            'layout' => 'stacked',
          ],
        ]
      );

      $this->add_responsive_control(
        'split_ratio',
        [
          'label' => esc_html__( 'Title Column Width', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['%'],
          'range' => [
            '%' => ['min' => 20, 'max' => 80, 'step' => 1],
          ],
          'default' => ['unit' => '%', 'size' => 50],
          'selectors' => [
            '{{WRAPPER}} .tms-section-intro' => '--tms-section-intro-split: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'layout' => 'split',
          ],
        ]
      );

      $this->add_responsive_control(
        'split_vertical_alignment',
        [
          'label' => esc_html__( 'Vertical Alignment', 'themeasy-lite' ),
          'type' => Controls_Manager::CHOOSE,
          'options' => [
            'start' => [
              'title' => esc_html__( 'Top', 'themeasy-lite' ),
              'icon' => 'eicon-v-align-top',
            ],
            'center' => [
              'title' => esc_html__( 'Middle', 'themeasy-lite' ),
              'icon' => 'eicon-v-align-middle',
            ],
            'end' => [
              'title' => esc_html__( 'Bottom', 'themeasy-lite' ),
              'icon' => 'eicon-v-align-bottom',
            ],
          ],
          'default' => 'start',
          'toggle' => false,
          'selectors' => [
            '{{WRAPPER}} .tms-section-intro--layout-split' => 'align-items: {{VALUE}};',
          ],
          'condition' => [
            'layout' => 'split',
          ],
        ]
      );

      $this->add_responsive_control(
        'offset_column_width',
        [
          'label' => esc_html__( 'Eyebrow Column Width', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem', '%'],
          'range' => [
            'px' => ['min' => 80, 'max' => 480, 'step' => 1],
            'rem' => ['min' => 5, 'max' => 30, 'step' => 0.5],
            '%' => ['min' => 10, 'max' => 40, 'step' => 1],
          ],
          'default' => ['unit' => 'px', 'size' => 200],
          'selectors' => [
            '{{WRAPPER}} .tms-section-intro' => '--tms-section-intro-offset-col: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'layout' => 'offset',
          ],
        ]
      );

      $this->add_control(
        'swap_columns',
        [
          'label' => esc_html__( 'Swap Columns', 'themeasy-lite' ),
          'description' => esc_html__(
            'Mirror the columns: content left, title (or eyebrow column) right.',
            'themeasy-lite'
          ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => '',
          'condition' => [
            'layout' => ['split', 'offset'],
          ],
        ]
      );

      $this->add_control(
        'stack_on',
        [
          'label' => esc_html__( 'Stack Columns On', 'themeasy-lite' ),
          'description' => esc_html__(
            'Breakpoint where multi-column layouts collapse into a single column.',
            'themeasy-lite'
          ),
          'type' => Controls_Manager::SELECT,
          'default' => 'md',
          'options' => [
            'md' => esc_html__( 'Tablet & Below', 'themeasy-lite' ),
            'sm' => esc_html__( 'Mobile Only', 'themeasy-lite' ),
            '' => esc_html__( 'Never', 'themeasy-lite' ),
          ],
          'condition' => [
            'layout!' => 'stacked',
          ],
        ]
      );

      $this->add_control(
        'frame',
        [
          'label' => esc_html__( 'Frame', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'separator' => 'before',
          'default' => '',
          'options' => [
            '' => esc_html__( 'None', 'themeasy-lite' ),
            'line-top' => esc_html__( 'Top Line', 'themeasy-lite' ),
            'line-bottom' => esc_html__( 'Bottom Line', 'themeasy-lite' ),
            'outline' => esc_html__( 'Outline', 'themeasy-lite' ),
            'panel' => esc_html__( 'Panel (Background)', 'themeasy-lite' ),
            'blueprint' => esc_html__( 'Blueprint (Corner Ticks)', 'themeasy-lite' ),
          ],
        ]
      );

      $this->add_control(
        'show_divider',
        [
          'label' => esc_html__( 'Show Divider', 'themeasy-lite' ),
          'description' => esc_html__(
            'Hairline between the groups: vertical in column layouts, horizontal in Stacked, a bottom rule in Row.',
            'themeasy-lite'
          ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => '',
        ]
      );

      $this->add_responsive_control(
        'vertical_gap',
        [
          'label' => esc_html__( 'Vertical Spacing', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'separator' => 'before',
          'size_units' => ['rem', 'px'],
          'range' => [
            'rem' => ['min' => 0, 'max' => 10, 'step' => 0.1],
            'px' => ['min' => 0, 'max' => 160, 'step' => 1],
          ],
          'default' => ['unit' => 'rem', 'size' => 2],
          'selectors' => [
            '{{WRAPPER}} .tms-section-intro' => 'row-gap: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'horizontal_gap',
        [
          'label' => esc_html__( 'Horizontal Spacing', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['rem', 'px'],
          'range' => [
            'rem' => ['min' => 0, 'max' => 12, 'step' => 0.1],
            'px' => ['min' => 0, 'max' => 200, 'step' => 1],
          ],
          'default' => ['unit' => 'rem', 'size' => 3],
          'selectors' => [
            '{{WRAPPER}} .tms-section-intro' => 'column-gap: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'layout!' => 'stacked',
          ],
        ]
      );

      $this->add_responsive_control(
        'head_gap',
        [
          'label' => esc_html__( 'Heading Group Spacing', 'themeasy-lite' ),
          'description' => esc_html__(
            'Gap between the elements inside the heading group (e.g. eyebrow to title).',
            'themeasy-lite'
          ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['rem', 'px'],
          'range' => [
            'rem' => ['min' => 0, 'max' => 6, 'step' => 0.1],
            'px' => ['min' => 0, 'max' => 100, 'step' => 1],
          ],
          'default' => ['unit' => 'rem', 'size' => 1],
          'selectors' => [
            '{{WRAPPER}} .tms-section-intro' => '--tms-section-intro-head-gap: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'body_gap',
        [
          'label' => esc_html__( 'Content Group Spacing', 'themeasy-lite' ),
          'description' => esc_html__(
            'Gap between the elements inside the content group (e.g. description to buttons).',
            'themeasy-lite'
          ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['rem', 'px'],
          'range' => [
            'rem' => ['min' => 0, 'max' => 6, 'step' => 0.1],
            'px' => ['min' => 0, 'max' => 100, 'step' => 1],
          ],
          'default' => ['unit' => 'rem', 'size' => 1.5],
          'selectors' => [
            '{{WRAPPER}} .tms-section-intro' => '--tms-section-intro-body-gap: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'max_width',
        [
          'label' => esc_html__( 'Max Width', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'separator' => 'before',
          'size_units' => ['%', 'px', 'rem'],
          'range' => [
            '%' => ['min' => 10, 'max' => 100, 'step' => 1],
            'px' => ['min' => 200, 'max' => 2000, 'step' => 10],
            'rem' => ['min' => 10, 'max' => 120, 'step' => 1],
          ],
          'default' => ['unit' => '%', 'size' => 100],
          'selectors' => [
            '{{WRAPPER}} .tms-section-intro' => 'max-width: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'block_position',
        [
          'label' => esc_html__( 'Block Position', 'themeasy-lite' ),
          'description' => esc_html__(
            'Where the block sits inside the column once Max Width is reduced.',
            'themeasy-lite'
          ),
          'type' => Controls_Manager::CHOOSE,
          'options' => [
            'start' => [
              'title' => esc_html__( 'Left', 'themeasy-lite' ),
              'icon' => 'eicon-align-start-h',
            ],
            'center' => [
              'title' => esc_html__( 'Center', 'themeasy-lite' ),
              'icon' => 'eicon-align-center-h',
            ],
            'end' => [
              'title' => esc_html__( 'Right', 'themeasy-lite' ),
              'icon' => 'eicon-align-end-h',
            ],
          ],
          'default' => 'start',
          'toggle' => false,
          'selectors_dictionary' => [
            'start' => 'margin-right: auto; margin-left: 0;',
            'center' => 'margin-left: auto; margin-right: auto;',
            'end' => 'margin-left: auto; margin-right: 0;',
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-section-intro' => '{{VALUE}}',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Frame
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'section_intro_frame_styles_section',
        [
          'label' => esc_html__( 'Frame', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'frame!' => '',
          ],
        ]
      );

      $this->add_control(
        'frame_border_color',
        [
          'label' => esc_html__( 'Border Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-section-intro' => '--tms-section-intro-border-color: {{VALUE}};',
          ],
          'condition' => [
            'frame' => ['line-top', 'line-bottom', 'outline', 'blueprint'],
          ],
        ]
      );

      $this->add_responsive_control(
        'frame_border_width',
        [
          'label' => esc_html__( 'Border Width', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => ['min' => 1, 'max' => 10, 'step' => 1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-section-intro' => '--tms-section-intro-border-width: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'frame' => ['line-top', 'line-bottom', 'outline', 'blueprint'],
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Background::get_type(),
        [
          'name' => 'frame_background',
          'label' => esc_html__( 'Background', 'themeasy-lite' ),
          'types' => ['classic', 'gradient'],
          'selector' => '{{WRAPPER}} .tms-section-intro',
          'condition' => [
            'frame' => ['outline', 'panel', 'blueprint'],
          ],
        ]
      );

      $this->add_control(
        'frame_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', '%'],
          'selectors' => [
            '{{WRAPPER}} .tms-section-intro'
              => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
          'condition' => [
            'frame' => ['outline', 'panel', 'blueprint'],
          ],
        ]
      );

      $this->add_responsive_control(
        'frame_padding',
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', '%'],
          'selectors' => [
            '{{WRAPPER}} .tms-section-intro'
              => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Box_Shadow::get_type(),
        [
          'name' => 'frame_box_shadow',
          'label' => esc_html__( 'Box Shadow', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-section-intro',
          'condition' => [
            'frame' => ['outline', 'panel', 'blueprint'],
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Divider
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'section_intro_divider_styles_section',
        [
          'label' => esc_html__( 'Divider', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
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
            '{{WRAPPER}} .tms-section-intro' => '--tms-section-intro-divider-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'divider_thickness',
        [
          'label' => esc_html__( 'Thickness', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => ['min' => 1, 'max' => 10, 'step' => 1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-section-intro' => '--tms-section-intro-divider-size: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Eyebrow
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'section_intro_eyebrow_styles_section',
        [
          'label' => esc_html__( 'Eyebrow', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'eyebrow!' => '',
          ],
        ]
      );

      $this->add_control(
        'eyebrow_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-section-intro__eyebrow' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'eyebrow_marker_color',
        [
          'label' => esc_html__( 'Marker Color', 'themeasy-lite' ),
          'description' => esc_html__(
            'Color of the leading dash, dot or brackets. Defaults to the text color.',
            'themeasy-lite'
          ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-section-intro__eyebrow::before,
             {{WRAPPER}} .tms-section-intro__eyebrow::after' => 'color: {{VALUE}};',
          ],
          'condition' => [
            'eyebrow_style' => ['line', 'dot', 'brackets'],
          ],
        ]
      );

      $this->add_control(
        'eyebrow_background_color',
        [
          'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-section-intro__eyebrow' => 'background-color: {{VALUE}};',
          ],
          'condition' => [
            'eyebrow_style' => 'tag',
          ],
        ]
      );

      $this->add_control(
        'eyebrow_border_color',
        [
          'label' => esc_html__( 'Border Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-section-intro__eyebrow' => 'border-color: {{VALUE}};',
          ],
          'condition' => [
            'eyebrow_style' => 'pill',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'eyebrow_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-section-intro__eyebrow',
        ]
      );

      $this->add_responsive_control(
        'eyebrow_padding',
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'em', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-section-intro__eyebrow'
              => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
          'condition' => [
            'eyebrow_style' => ['pill', 'tag'],
          ],
        ]
      );

      $this->add_control(
        'eyebrow_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'em', '%'],
          'selectors' => [
            '{{WRAPPER}} .tms-section-intro__eyebrow'
              => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
          'condition' => [
            'eyebrow_style' => ['pill', 'tag'],
          ],
        ]
      );

      // Eyebrow icon.
      $this->add_control(
        'eyebrow_icon_styles_heading',
        [
          'label' => esc_html__( 'Icon', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
          'condition' => [
            'eyebrow_icon[value]!' => '',
          ],
        ]
      );

      $this->add_control(
        'eyebrow_icon_fill_color',
        [
          'label' => esc_html__( 'Icon Fill Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} svg.tms-section-intro__eyebrow-icon' => 'fill: {{VALUE}};',
          ],
          'conditions' => [
            'terms' => [
              ['name' => 'eyebrow_icon[library]', 'operator' => 'in', 'value' => themeasy_svg_icon_condition_libraries()],
            ],
          ],
        ]
      );

      $this->add_control(
        'eyebrow_icon_stroke_color',
        [
          'label' => esc_html__( 'Icon Stroke Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} svg.tms-section-intro__eyebrow-icon' => 'stroke: {{VALUE}};',
          ],
          'conditions' => [
            'terms' => [
              ['name' => 'eyebrow_icon[library]', 'operator' => 'in', 'value' => themeasy_svg_icon_condition_libraries()],
            ],
          ],
        ]
      );

      $this->add_responsive_control(
        'eyebrow_icon_size',
        [
          'label' => esc_html__( 'Icon Size', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'em', 'rem'],
          'range' => [
            'px' => ['min' => 8, 'max' => 64, 'step' => 1],
            'em' => ['min' => 0.5, 'max' => 4, 'step' => 0.1],
            'rem' => ['min' => 0.5, 'max' => 4, 'step' => 0.1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-section-intro__eyebrow-icon' => 'font-size: {{SIZE}}{{UNIT}};',
            '{{WRAPPER}} svg.tms-section-intro__eyebrow-icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'eyebrow_icon[value]!' => '',
          ],
        ]
      );

      $this->add_responsive_control(
        'eyebrow_icon_gap',
        [
          'label' => esc_html__( 'Icon Spacing', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'em'],
          'range' => [
            'px' => ['min' => 0, 'max' => 40, 'step' => 1],
            'em' => ['min' => 0, 'max' => 3, 'step' => 0.1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-section-intro__eyebrow' => 'gap: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'eyebrow_icon[value]!' => '',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Title
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'section_intro_title_styles_section',
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
            '{{WRAPPER}} .tms-section-intro__title' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'title_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          // Default look = the synced "Display 3" Kit preset, if the Kit has it; detach or switch in the popover.
          'global' => themeasy_display_typography_global( 3 ),
          'selector' => '{{WRAPPER}} .tms-section-intro__title',
        ]
      );

      $this->add_responsive_control(
        'title_max_width',
        [
          'label' => esc_html__( 'Max Width', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem', 'ch', '%'],
          'range' => [
            'px' => ['min' => 200, 'max' => 1400, 'step' => 1],
            'ch' => ['min' => 10, 'max' => 80, 'step' => 1],
            '%' => ['min' => 10, 'max' => 100, 'step' => 1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-section-intro__title' => 'max-width: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'title_margin',
        [
          'label' => esc_html__( 'Margin', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'em', 'rem', '%'],
          'selectors' => [
            '{{WRAPPER}} .tms-section-intro__title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Title Accent
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'section_intro_title_accent_styles_section',
        [
          'label' => esc_html__( 'Title Accent', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'title!' => '',
          ],
        ]
      );

      $this->add_control(
        'accent_note_style',
        [
          'show_label' => false,
          'type' => Controls_Manager::RAW_HTML,
          'raw' => esc_html__(
            'These styles apply to words wrapped in <em> (accent) and <mark> (highlight) inside the Title.',
            'themeasy-lite'
          ),
          'content_classes' => 'elementor-control-field-description no-margin',
        ]
      );

      // Accent (<em>).
      $this->add_control(
        'accent_em_styles_heading',
        [
          'label' => esc_html__( 'Accent (em)', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
        ]
      );

      $this->add_control(
        'accent_em_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-section-intro__title em' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'accent_em_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-section-intro__title em',
        ]
      );

      // Highlight (<mark>).
      $this->add_control(
        'accent_mark_styles_heading',
        [
          'label' => esc_html__( 'Highlight (mark)', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'accent_mark_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-section-intro__title mark' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'accent_mark_highlight_color',
        [
          'label' => esc_html__( 'Highlight Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-section-intro__title mark' => '--tms-section-intro-mark-color: {{VALUE}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Description
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'section_intro_description_styles_section',
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
            '{{WRAPPER}} .tms-section-intro__description' => 'color: {{VALUE}};',
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
            '{{WRAPPER}} .tms-section-intro__description' => 'font-size: var(--tms-font-size-{{VALUE}});',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'description_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-section-intro__description',
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
            '{{WRAPPER}} .tms-section-intro__description' => 'max-width: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Buttons
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'section_intro_buttons_styles_section',
        [
          'label' => esc_html__( 'Buttons', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_responsive_control(
        'buttons_direction',
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
            '{{WRAPPER}} .tms-section-intro__actions' => 'flex-direction: {{VALUE}};',
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
            '{{WRAPPER}} .tms-section-intro__actions' => 'gap: {{SIZE}}{{UNIT}};',
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

    // ------------------------------------------------------------------------
    // Advanced section: Motion.
    // ------------------------------------------------------------------------
    themeasy_register_motion_upsell_section( $this );
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
        'placeholder' => esc_html__( 'Leave blank to hide', 'themeasy-lite' ),
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
      "section_intro_{$prefix}_button_styles_section",
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
          'default' => ( 'primary' === $prefix ) ? 'solid-fill' : 'simple',
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
                "{{WRAPPER}} .tms-section-intro .tms-section-intro__button--{$prefix}"
                  => 'background-color: {{VALUE}};',
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
                "{{WRAPPER}} .tms-section-intro .tms-section-intro__button--{$prefix}" => 'border-color: {{VALUE}};',
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
                "{{WRAPPER}} .tms-section-intro .tms-section-intro__button--{$prefix} .tms-button__text,
                 {{WRAPPER}} .tms-section-intro .tms-section-intro__button--{$prefix} .tms-button__text > *,
                 {{WRAPPER}} .tms-section-intro .tms-section-intro__button--{$prefix} .tms-button__icon,
                 {{WRAPPER}} .tms-section-intro .tms-section-intro__button--{$prefix} svg" => 'color: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            "{$prefix}_button_icon_fill_color",
            [
              'label' => esc_html__( 'Icon Fill Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                "{{WRAPPER}} .tms-section-intro .tms-section-intro__button--{$prefix} svg" => 'fill: {{VALUE}};',
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
                "{{WRAPPER}} .tms-section-intro .tms-section-intro__button--{$prefix} svg" => 'stroke: {{VALUE}};',
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
                "{{WRAPPER}} .tms-section-intro .tms-section-intro__button--{$prefix}:hover,
                 {{WRAPPER}} .tms-section-intro .tms-section-intro__button--{$prefix}:focus-visible"
                  => 'background-color: {{VALUE}};',
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
                "{{WRAPPER}} .tms-section-intro .tms-section-intro__button--{$prefix}:hover,
                 {{WRAPPER}} .tms-section-intro .tms-section-intro__button--{$prefix}:focus-visible"
                  => 'border-color: {{VALUE}};',
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
                "{{WRAPPER}} .tms-section-intro .tms-section-intro__button--{$prefix}:hover .tms-button__text,
                 {{WRAPPER}} .tms-section-intro .tms-section-intro__button--{$prefix}:hover .tms-button__text > *,
                 {{WRAPPER}} .tms-section-intro .tms-section-intro__button--{$prefix}:hover .tms-button__icon,
                 {{WRAPPER}} .tms-section-intro .tms-section-intro__button--{$prefix}:hover svg,
                 {{WRAPPER}} .tms-section-intro .tms-section-intro__button--{$prefix}:focus-visible .tms-button__text,
                 {{WRAPPER}} .tms-section-intro .tms-section-intro__button--{$prefix}:focus-visible .tms-button__icon,
                 {{WRAPPER}} .tms-section-intro .tms-section-intro__button--{$prefix}:focus-visible svg"
                  => 'color: {{VALUE}};',
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
          'selector' => "{{WRAPPER}} .tms-section-intro .tms-section-intro__button--{$prefix} .tms-button__text",
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
            "{{WRAPPER}} .tms-section-intro .tms-section-intro__button--{$prefix} .tms-button__icon"
              => 'font-size: {{SIZE}}{{UNIT}};',
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
            "{{WRAPPER}} .tms-section-intro .tms-section-intro__button--{$prefix} svg,
             {{WRAPPER}} .tms-section-intro .tms-section-intro__button--{$prefix} svg *"
              => 'stroke-width: {{SIZE}}{{UNIT}};',
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
            "{{WRAPPER}} .tms-section-intro .tms-section-intro__button--{$prefix}"
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
            "{{WRAPPER}} .tms-section-intro .tms-section-intro__button--{$prefix}"
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
   * Map each intro element into the heading or content group for a layout.
   *
   * @param string $layout Layout key.
   * @return array Array with 'head' and 'body' element lists.
   */
  private function get_layout_groups( string $layout ): array {
    switch ( $layout ) {
      case 'offset':
      case 'rail':
        return [
          'head' => ['eyebrow'],
          'body' => ['title', 'description', 'actions'],
        ];

      case 'row':
        return [
          'head' => ['eyebrow', 'title', 'description'],
          'body' => ['actions'],
        ];

      default:
        return [
          'head' => ['eyebrow', 'title'],
          'body' => ['description', 'actions'],
        ];
    }
  }

  /**
   * Render one intro element (eyebrow|title|description|actions).
   *
   * @param string $part     Element key.
   * @param array  $settings Widget settings.
   * @param array  $ctx      Precomputed render context.
   * @return void
   */
  private function render_intro_part( string $part, array $settings, array $ctx ): void {
    switch ( $part ) {
      case 'eyebrow':
        if ( '' === $ctx['eyebrow'] ) {
          break;
        }

        $eyebrow_sanitized = wp_kses( $ctx['eyebrow'], themeasy_get_kses_allowed_tags() );

        $eyebrow_text = '<span ' . $this->get_render_attribute_string( 'eyebrow' ) . '>'
          . $eyebrow_sanitized . '</span>';

        ?>
        <span class="tms-section-intro__eyebrow">
          <?php
          if ( $ctx['eyebrow_icon_html'] ) {
            echo $ctx['eyebrow_icon_html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
          }

          echo $eyebrow_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
          ?>
        </span>
        <?php
        break;

      case 'title':
        if ( '' === $ctx['title'] ) {
          break;
        }

        $title_tag = esc_html( $ctx['title_tag'] );
        $title_sanitized = wp_kses( $ctx['title'], themeasy_get_kses_allowed_tags() );

        $title_html = '<' . $title_tag . ' ' . $this->get_render_attribute_string( 'title' ) . '>'
          . $title_sanitized . '</' . $title_tag . '>';

        echo $title_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        break;

      case 'description':
        if ( '' === $ctx['description'] ) {
          break;
        }

        $description_sanitized = wp_kses( $ctx['description'], themeasy_get_kses_allowed_tags() );

        $description_html = '<div ' . $this->get_render_attribute_string( 'description' ) . '>'
          . $description_sanitized . '</div>';

        echo $description_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        break;

      case 'actions':
        if ( !$ctx['has_primary'] && !$ctx['has_secondary'] ) {
          break;
        }
        ?>
        <div class="tms-section-intro__actions">
          <?php $this->render_button( 'primary', $settings ); ?>
          <?php if ( $ctx['has_secondary'] ) : ?>
            <?php $this->render_button( 'secondary', $settings ); ?>
          <?php endif; ?>
        </div>
        <?php
        break;
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
    $style = $settings["{$prefix}_button_style"] ?? ( 'primary' === $prefix ? 'solid-fill' : 'simple' );
    $icon_align = $settings["{$prefix}_button_icon_alignment"] ?? 'right';
    $link = $settings["{$prefix}_button_link"] ?? [];
    $has_link = !empty( $link['url'] );

    if ( '' === $label ) {
      return;
    }

    $classes = [
      'tms-button',
      'tms-section-intro__button',
      'tms-section-intro__button--' . $prefix,
      'tms-button--' . sanitize_html_class( $style ),
    ];

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
      is_array( $settings["{$prefix}_button_icon"] ?? null ) ? $settings["{$prefix}_button_icon"] : [],
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
   * Render the widget output on the frontend.
   *
   * @return void
   */
  protected function render() {
    $settings = $this->get_settings_for_display();

    // ------------------------------------------------------------------------
    // Settings.
    // ------------------------------------------------------------------------
    $eyebrow = $settings['eyebrow'] ?? '';
    $eyebrow_style = $settings['eyebrow_style'] ?? 'text';
    $title = $settings['title'] ?? '';
    $title_tag = themeasy_sanitize_heading_tag(
      $settings['title_tag'] ?? 'h2',
      'h2',
      ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div']
    );
    $description = $settings['description'] ?? '';
    $layout = $settings['layout'] ?? 'stacked';
    $frame = $settings['frame'] ?? '';
    $stack_on = $settings['stack_on'] ?? 'md';
    $swap = ( 'yes' === ( $settings['swap_columns'] ?? '' ) );
    $show_divider = ( 'yes' === ( $settings['show_divider'] ?? '' ) );
    $show_secondary = ( 'yes' === ( $settings['show_secondary_button'] ?? '' ) );

    $has_primary = '' !== ( $settings['primary_button_label'] ?? '' );
    $has_secondary = $show_secondary && '' !== ( $settings['secondary_button_label'] ?? '' );

    // Element presence + per-layout group mapping.
    $present = [
      'eyebrow' => '' !== $eyebrow,
      'title' => '' !== $title,
      'description' => '' !== $description,
      'actions' => $has_primary || $has_secondary,
    ];

    $groups = $this->get_layout_groups( $layout );
    $has_head = (bool) array_filter( array_intersect_key( $present, array_flip( $groups['head'] ) ) );
    $has_body = (bool) array_filter( array_intersect_key( $present, array_flip( $groups['body'] ) ) );

    // Early return if no content at all.
    if ( !$has_head && !$has_body ) {
      return;
    }

    // ------------------------------------------------------------------------
    // Wrapper classes.
    // ------------------------------------------------------------------------
    $wrapper_classes = [
      'tms-section-intro',
      sanitize_html_class( 'tms-section-intro--layout-' . $layout ),
    ];

    if ( $present['eyebrow'] ) {
      $wrapper_classes[] = sanitize_html_class( 'tms-section-intro--eyebrow-' . $eyebrow_style );
    }

    if ( $frame ) {
      $wrapper_classes[] = sanitize_html_class( 'tms-section-intro--frame-' . $frame );
    }

    if ( $swap && in_array( $layout, ['split', 'offset'], true ) ) {
      $wrapper_classes[] = 'tms-section-intro--swap';
    }

    if ( $show_divider ) {
      $wrapper_classes[] = 'tms-section-intro--has-divider';
    }

    if ( $stack_on && 'stacked' !== $layout ) {
      $wrapper_classes[] = sanitize_html_class( 'tms-section-intro--stack-' . $stack_on );
    }

    if ( !$has_head ) {
      $wrapper_classes[] = 'tms-section-intro--no-head';
    }

    if ( !$has_body ) {
      $wrapper_classes[] = 'tms-section-intro--no-body';
    }

    $wrapper_classes_output = implode( ' ', array_filter( $wrapper_classes ) );

    // ------------------------------------------------------------------------
    // Render attributes.
    // ------------------------------------------------------------------------
    if ( $present['eyebrow'] ) {
      $this->add_render_attribute( 'eyebrow', 'class', 'tms-section-intro__eyebrow-text' );
      $this->add_inline_editing_attributes( 'eyebrow', 'basic' );
    }

    if ( $present['title'] ) {
      $this->add_render_attribute( 'title', 'class', 'tms-section-intro__title' );
      $this->add_inline_editing_attributes( 'title', 'basic' );
    }

    if ( $present['description'] ) {
      $this->add_render_attribute( 'description', 'class', 'tms-section-intro__description' );
      $this->add_inline_editing_attributes( 'description', 'advanced' );
    }

    // Eyebrow icon.
    $eyebrow_icon_html = '';

    if ( $present['eyebrow'] ) {
      $eyebrow_icon_html = themeasy_render_icon_html(
        is_array( $settings['eyebrow_icon'] ?? null ) ? $settings['eyebrow_icon'] : [],
        ['class' => 'tms-section-intro__eyebrow-icon', 'aria-hidden' => 'true']
      );
    }

    // Shared context for the part renderers.
    $ctx = [
      'eyebrow' => $eyebrow,
      'eyebrow_icon_html' => $eyebrow_icon_html,
      'title' => $title,
      'title_tag' => $title_tag,
      'description' => $description,
      'has_primary' => $has_primary,
      'has_secondary' => $has_secondary,
    ];

    // ------------------------------------------------------------------------
    // Output.
    // ------------------------------------------------------------------------
    ?>
    <div class="<?php echo esc_attr( $wrapper_classes_output ); ?>">

      <?php if ( 'blueprint' === $frame ) : ?>
        <span class="tms-section-intro__tick tms-section-intro__tick--tl" aria-hidden="true"></span>
        <span class="tms-section-intro__tick tms-section-intro__tick--tr" aria-hidden="true"></span>
        <span class="tms-section-intro__tick tms-section-intro__tick--bl" aria-hidden="true"></span>
        <span class="tms-section-intro__tick tms-section-intro__tick--br" aria-hidden="true"></span>
      <?php endif; ?>

      <?php if ( $has_head ) : ?>
        <div class="tms-section-intro__head">
          <?php
          foreach ( $groups['head'] as $part ) {
            $this->render_intro_part( $part, $settings, $ctx );
          }
          ?>
        </div><!-- /.tms-section-intro__head -->
      <?php endif; ?>

      <?php if ( $show_divider ) : ?>
        <span class="tms-section-intro__divider" aria-hidden="true"></span>
      <?php endif; ?>

      <?php if ( $has_body ) : ?>
        <div class="tms-section-intro__body">
          <?php
          foreach ( $groups['body'] as $part ) {
            $this->render_intro_part( $part, $settings, $ctx );
          }
          ?>
        </div><!-- /.tms-section-intro__body -->
      <?php endif; ?>

    </div><!-- /.tms-section-intro -->
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
      var allowedTags = [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div' ];

      // Kses-parity sanitizer for user text in raw preview output. Falls back
      // to plain escaping when the shared helper is unavailable (safe — only
      // inline formatting is lost).
      var sanitizeInline = ( window.Themeasy && window.Themeasy.sanitizeInlineHtml )
        ? window.Themeasy.sanitizeInlineHtml
        : _.escape;

      var eyebrow      = sanitizeInline( settings.eyebrow || '' );
      var eyebrowStyle = settings.eyebrow_style || 'text';
      var title        = sanitizeInline( settings.title || '' );
      var titleTag     = allowedTags.includes( settings.title_tag ) ? settings.title_tag : 'h2';
      var description  = sanitizeInline( settings.description || '' );
      var layout       = settings.layout || 'stacked';
      var frame        = settings.frame || '';
      var stackOn      = settings.stack_on || 'md';
      var swap         = settings.swap_columns === 'yes';
      var showDivider  = settings.show_divider === 'yes';
      var showSecondary = settings.show_secondary_button === 'yes';

      var hasPrimary   = !! ( settings.primary_button_label || '' );
      var hasSecondary = showSecondary && !! ( settings.secondary_button_label || '' );

      // Element presence + per-layout group mapping (mirrors get_layout_groups()).
      var present = {
        eyebrow: !! eyebrow,
        title: !! title,
        description: !! description,
        actions: hasPrimary || hasSecondary
      };

      var groups;

      if ( layout === 'offset' || layout === 'rail' ) {
        groups = { head: [ 'eyebrow' ], body: [ 'title', 'description', 'actions' ] };
      } else if ( layout === 'row' ) {
        groups = { head: [ 'eyebrow', 'title', 'description' ], body: [ 'actions' ] };
      } else {
        groups = { head: [ 'eyebrow', 'title' ], body: [ 'description', 'actions' ] };
      }

      var hasHead = groups.head.some( function( part ) { return present[ part ]; } );
      var hasBody = groups.body.some( function( part ) { return present[ part ]; } );

      if ( ! hasHead && ! hasBody ) {
        return;
      }

      // Scheme guard for every href below. The regex lives in exactly one place;
      // the fallback fails closed rather than duplicating it (a bare ^ anchor
      // misses the C0 controls browsers strip before resolving a scheme).
      var safeUrl = ( window.Themeasy && window.Themeasy.safeUrl )
        ? window.Themeasy.safeUrl
        : function () { return ''; };

      // ------------------------------------------------------------------------
      // Wrapper classes.
      // ------------------------------------------------------------------------
      var wrapperClasses = [ 'tms-section-intro', 'tms-section-intro--layout-' + layout ];

      if ( present.eyebrow ) {
        wrapperClasses.push( 'tms-section-intro--eyebrow-' + eyebrowStyle );
      }

      if ( frame ) {
        wrapperClasses.push( 'tms-section-intro--frame-' + frame );
      }

      if ( swap && ( layout === 'split' || layout === 'offset' ) ) {
        wrapperClasses.push( 'tms-section-intro--swap' );
      }

      if ( showDivider ) {
        wrapperClasses.push( 'tms-section-intro--has-divider' );
      }

      if ( stackOn && layout !== 'stacked' ) {
        wrapperClasses.push( 'tms-section-intro--stack-' + stackOn );
      }

      if ( ! hasHead ) {
        wrapperClasses.push( 'tms-section-intro--no-head' );
      }

      if ( ! hasBody ) {
        wrapperClasses.push( 'tms-section-intro--no-body' );
      }

      var wrapperClassStr = wrapperClasses.filter( Boolean ).join( ' ' );

      // ------------------------------------------------------------------------
      // Render attributes.
      // ------------------------------------------------------------------------
      if ( present.eyebrow ) {
        view.addRenderAttribute( 'eyebrow', 'class', 'tms-section-intro__eyebrow-text' );
        view.addInlineEditingAttributes( 'eyebrow', 'basic' );
      }

      if ( present.title ) {
        view.addRenderAttribute( 'title', 'class', 'tms-section-intro__title' );
        view.addInlineEditingAttributes( 'title', 'basic' );
      }

      if ( present.description ) {
        view.addRenderAttribute( 'description', 'class', 'tms-section-intro__description' );
        view.addInlineEditingAttributes( 'description', 'advanced' );
      }

      // Eyebrow icon.
      var eyebrowIconMarkup = '';

      if ( present.eyebrow && window.Themeasy && window.Themeasy.renderIconMarkup ) {
        eyebrowIconMarkup = window.Themeasy.renderIconMarkup(
          view, settings.eyebrow_icon, null,
          { 'class': 'tms-section-intro__eyebrow-icon', 'aria-hidden': 'true' }
        );
      }

      // ------------------------------------------------------------------------
      // Part renderers (return HTML strings).
      // ------------------------------------------------------------------------
      var renderButton = function( prefix ) {
        var label = sanitizeInline( settings[ prefix + '_button_label' ] || '' );

        if ( ! label ) { return ''; }

        var style = settings[ prefix + '_button_style' ] || ( prefix === 'primary' ? 'solid-fill' : 'simple' );
        var iconAlign = settings[ prefix + '_button_icon_alignment' ] || 'right';
        var link = settings[ prefix + '_button_link' ] || {};
        // A blocked scheme yields '' — mirror render()'s no-link shape
        // (href="#" + role="button") instead of emitting a live hostile href.
        var href = safeUrl( link.url || '' );
        var roleAttr = href ? '' : ' role="button"';

        if ( ! href ) { href = '#'; }

        var classes = [
          'tms-button',
          'tms-section-intro__button',
          'tms-section-intro__button--' + prefix,
          'tms-button--' + style
        ];

        if ( iconAlign === 'left' ) { classes.push( 'tms-button--icon-left' ); }

        var labelKey = prefix + '_button_label';
        view.addRenderAttribute( labelKey, 'class', 'tms-button__text' );
        view.addInlineEditingAttributes( labelKey, 'none' );

        var iconMarkup = ( window.Themeasy && window.Themeasy.renderIconMarkup )
          ? window.Themeasy.renderIconMarkup(
              view, settings[ prefix + '_button_icon' ], null,
              { 'class': 'tms-button__icon', 'aria-hidden': 'true' }
            )
          : '';

        var html = '<a href="' + _.escape( href ) + '"'
          + ' class="' + _.escape( classes.join( ' ' ) ) + '"' + roleAttr + '>';
        html += '<span ' + view.getRenderAttributeString( labelKey ) + '>' + label + '</span>';
        if ( iconMarkup ) { html += iconMarkup; }
        html += '</a>';

        return html;
      };

      var renderPart = function( part ) {
        var html = '';

        if ( part === 'eyebrow' && present.eyebrow ) {
          html += '<span class="tms-section-intro__eyebrow">';

          if ( eyebrowIconMarkup ) { html += eyebrowIconMarkup; }

          var eyebrowHtml = '<span ' + view.getRenderAttributeString( 'eyebrow' ) + '>' + eyebrow + '</span>';

          html += eyebrowHtml + '</span>';
        }

        if ( part === 'title' && present.title ) {
          var titleHtml = '<' + titleTag + ' ' + view.getRenderAttributeString( 'title' ) + '>'
            + title + '</' + titleTag + '>';

          html += titleHtml;
        }

        if ( part === 'description' && present.description ) {
          var descriptionHtml = '<div ' + view.getRenderAttributeString( 'description' ) + '>' + description + '</div>';

          html += descriptionHtml;
        }

        if ( part === 'actions' && present.actions ) {
          var primaryButtonHtml = hasPrimary ? renderButton( 'primary' ) : '';
          var secondaryButtonHtml = hasSecondary ? renderButton( 'secondary' ) : '';

          html += '<div class="tms-section-intro__actions">' + primaryButtonHtml + secondaryButtonHtml + '</div>';
        }

        return html;
      };

      var headHtml = groups.head.map( renderPart ).join( '' );
      var bodyHtml = groups.body.map( renderPart ).join( '' );
    #>

    <div class="{{ wrapperClassStr }}">

      <# if ( frame === 'blueprint' ) { #>
        <span class="tms-section-intro__tick tms-section-intro__tick--tl" aria-hidden="true"></span>
        <span class="tms-section-intro__tick tms-section-intro__tick--tr" aria-hidden="true"></span>
        <span class="tms-section-intro__tick tms-section-intro__tick--bl" aria-hidden="true"></span>
        <span class="tms-section-intro__tick tms-section-intro__tick--br" aria-hidden="true"></span>
      <# } #>

      <# if ( hasHead ) { #>
        <div class="tms-section-intro__head">{{{ headHtml }}}</div><!-- /.tms-section-intro__head -->
      <# } #>

      <# if ( showDivider ) { #>
        <span class="tms-section-intro__divider" aria-hidden="true"></span>
      <# } #>

      <# if ( hasBody ) { #>
        <div class="tms-section-intro__body">{{{ bodyHtml }}}</div><!-- /.tms-section-intro__body -->
      <# } #>

    </div><!-- /.tms-section-intro -->
    <?php
  }
}
