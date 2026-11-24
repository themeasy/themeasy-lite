<?php
/**
 * Themeasy Elementor Widget: Text Editor
 *
 * A rich text editor widget with alignment, measure (max width), multi-column
 * flow and drop-cap controls, and typography styling.
 *
 * @package Themeasy
 * @since 1.0.0
 */

namespace Themeasy\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

/**
 * Text Editor widget — registers controls and renders output.
 */
class TextEditor extends Widget_Base {
  public function get_name() {
    return 'themeasy-text-editor';
  }

  public function get_title() {
    return esc_html__( 'Text Editor', 'themeasy-lite' );
  }

  public function get_icon() {
    return 'eicon-text';
  }

  public function get_custom_help_url() {
    return 'https://hub.themeasy.co/docs';
  }

  public function get_categories() {
    return ['themeasy_text_category', 'themeasy_content_category'];
  }

  public function get_keywords() {
    return ['themeasy', 'text', 'editor', 'wysiwyg', 'paragraph', 'content', 'rich text'];
  }

  public function get_style_depends() {
    return ['themeasy-text-editor'];
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
    // Content section: Text
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'text_section',
        [
          'label' => esc_html__( 'Text', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $this->add_control(
        'text',
        [
          'label' => esc_html__( 'Text Editor', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::WYSIWYG,
          // Wrapped in <p> so a freshly dropped widget already has the block structure
          // Paragraph Spacing and Columns act on, in the editor preview as well.
          'default' => '<p>' . __(
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.',
            'themeasy-lite'
          ) . '</p>',
          'placeholder' => esc_html__( 'Type your text here', 'themeasy-lite' ),
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Layout
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'text_layout_section',
        [
          'label' => esc_html__( 'Layout', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
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
            'justify' => [
              'title' => esc_html__( 'Justified', 'themeasy-lite' ),
              'icon' => 'eicon-text-align-justify',
            ],
          ],
          'default' => 'start',
          'toggle' => false,
          'selectors' => [
            '{{WRAPPER}} .tms-text-editor' => 'text-align: {{VALUE}}',
          ],
        ]
      );

      $this->add_responsive_control(
        'text_max_width',
        [
          'label' => esc_html__( 'Max Width', 'themeasy-lite' ),
          'description' => esc_html__(
            'Caps line length without depending on the container. In ch, 45-75 characters per line reads best.',
            'themeasy-lite'
          ),
          'type' => Controls_Manager::SLIDER,
          'separator' => 'before',
          'size_units' => ['px', 'rem', 'ch', '%'],
          'range' => [
            'px' => ['min' => 200, 'max' => 1600, 'step' => 10],
            'rem' => ['min' => 10, 'max' => 100, 'step' => 1],
            'ch' => ['min' => 20, 'max' => 120, 'step' => 1],
            '%' => ['min' => 10, 'max' => 100, 'step' => 1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-text-editor' => 'max-width: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'text_block_position',
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
            '{{WRAPPER}} .tms-text-editor' => '{{VALUE}}',
          ],
        ]
      );

      $this->add_responsive_control(
        'text_columns',
        [
          'label' => esc_html__( 'Columns', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'separator' => 'before',
          'default' => '',
          'options' => [
            '' => esc_html__( 'Default', 'themeasy-lite' ),
            '1' => '1',
            '2' => '2',
            '3' => '3',
            '4' => '4',
            '5' => '5',
            '6' => '6',
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-text-editor' => 'column-count: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'text_columns_gap',
        [
          'label' => esc_html__( 'Columns Gap', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['rem', 'px', 'em', '%'],
          'range' => [
            'rem' => ['min' => 0, 'max' => 8, 'step' => 0.1],
            'px' => ['min' => 0, 'max' => 120, 'step' => 1],
            'em' => ['min' => 0, 'max' => 8, 'step' => 0.1],
            '%' => ['min' => 0, 'max' => 20, 'step' => 0.5],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-text-editor' => 'column-gap: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'text_columns!' => '1',
          ],
        ]
      );

      $this->add_control(
        'text_drop_cap',
        [
          'label' => esc_html__( 'Drop Cap', 'themeasy-lite' ),
          'description' => esc_html__(
            'Enlarges the first letter of the text. Styled under Style > Drop Cap.',
            'themeasy-lite'
          ),
          'type' => Controls_Manager::SWITCHER,
          'separator' => 'before',
          'label_on' => esc_html__( 'On', 'themeasy-lite' ),
          'label_off' => esc_html__( 'Off', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => '',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Text
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'text_styles_section',
        [
          'label' => esc_html__( 'Text', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_control(
        'text_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-text-editor,
             {{WRAPPER}} .tms-text-editor > *' => 'color: {{VALUE}}',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'text_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-text-editor',
        ]
      );

      $this->add_responsive_control(
        'text_paragraph_spacing',
        [
          'label' => esc_html__( 'Paragraph Spacing', 'themeasy-lite' ),
          'description' => esc_html__(
            'Gap between paragraphs. The last one is left alone, so the block keeps no trailing space.',
            'themeasy-lite'
          ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['rem', 'px', 'em'],
          'range' => [
            'rem' => ['min' => 0, 'max' => 6, 'step' => 0.1],
            'px' => ['min' => 0, 'max' => 100, 'step' => 1],
            'em' => ['min' => 0, 'max' => 6, 'step' => 0.1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-text-editor p:not(:last-child)' => 'margin-block-end: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'text_links_heading',
        [
          'label' => esc_html__( 'Links', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->start_controls_tabs( 'text_link_colors' );

        $this->start_controls_tab(
          'text_link_colors_normal',
          [
            'label' => esc_html__( 'Normal', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'text_link_color',
            [
              'label' => esc_html__( 'Link Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-text-editor a' => 'color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

        $this->start_controls_tab(
          'text_link_colors_hover',
          [
            'label' => esc_html__( 'Hover', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'text_link_hover_color',
            [
              'label' => esc_html__( 'Link Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-text-editor a:hover,
                 {{WRAPPER}} .tms-text-editor a:focus-visible' => 'color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

      // Underline. Styles the <span class="tms-u"> highlights written in the editor.
      $this->add_control(
        'text_underline_styles_heading',
        [
          'label' => esc_html__( 'Underline Styles', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'text_underline_text_color',
        [
          'label' => esc_html__( 'Underline Text Color', 'themeasy-lite' ),
          'description' => esc_html__(
            'Color of the words wrapped in an underline. Leave empty to keep the Text Color.',
            'themeasy-lite'
          ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-text-editor .tms-u' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'text_underline_color',
        [
          'label' => esc_html__( 'Underline Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-text-editor .tms-u' => '--tms-u-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'text_underline_thickness',
        [
          'label' => esc_html__( 'Underline Thickness', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['rem', 'px'],
          'range' => [
            'rem' => ['min' => 0, 'max' => 10, 'step' => 0.1],
            'px' => ['min' => 1, 'max' => 100, 'step' => 1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-text-editor .tms-u' => '--tms-u-height: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'text_underline_offset',
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
            '{{WRAPPER}} .tms-text-editor .tms-u' => '--tms-u-top: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Columns
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'text_columns_styles_section',
        [
          'label' => esc_html__( 'Columns', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'text_columns!' => '1',
          ],
        ]
      );

      $this->add_control(
        'text_column_rule_style',
        [
          'label' => esc_html__( 'Divider Style', 'themeasy-lite' ),
          'description' => esc_html__( 'Draws a rule in the gap between columns.', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => '',
          'options' => [
            '' => esc_html__( 'None', 'themeasy-lite' ),
            'solid' => esc_html__( 'Solid', 'themeasy-lite' ),
            'dashed' => esc_html__( 'Dashed', 'themeasy-lite' ),
            'dotted' => esc_html__( 'Dotted', 'themeasy-lite' ),
            'double' => esc_html__( 'Double', 'themeasy-lite' ),
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-text-editor' => 'column-rule-style: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'text_column_rule_color',
        [
          'label' => esc_html__( 'Divider Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-text-editor' => 'column-rule-color: {{VALUE}};',
          ],
          'condition' => [
            'text_column_rule_style!' => '',
          ],
        ]
      );

      $this->add_responsive_control(
        'text_column_rule_width',
        [
          'label' => esc_html__( 'Divider Width', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem'],
          'range' => [
            'px' => ['min' => 1, 'max' => 20, 'step' => 1],
            'rem' => ['min' => 0.05, 'max' => 1, 'step' => 0.05],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-text-editor' => 'column-rule-width: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'text_column_rule_style!' => '',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Drop Cap
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'text_drop_cap_styles_section',
        [
          'label' => esc_html__( 'Drop Cap', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'text_drop_cap' => 'yes',
          ],
        ]
      );

      $this->add_control(
        'text_drop_cap_view',
        [
          'label' => esc_html__( 'View', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'default',
          'options' => [
            'default' => esc_html__( 'Default', 'themeasy-lite' ),
            'stacked' => esc_html__( 'Stacked', 'themeasy-lite' ),
            'framed' => esc_html__( 'Framed', 'themeasy-lite' ),
          ],
        ]
      );

      $this->add_control(
        'text_drop_cap_color',
        [
          'label' => esc_html__( 'Letter Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-text-editor' => '--tms-text-editor-drop-cap-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'text_drop_cap_background_color',
        [
          'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-text-editor' => '--tms-text-editor-drop-cap-background: {{VALUE}};',
          ],
          'condition' => [
            'text_drop_cap_view!' => 'default',
          ],
        ]
      );

      $this->add_control(
        'text_drop_cap_border_color',
        [
          'label' => esc_html__( 'Border Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-text-editor' => '--tms-text-editor-drop-cap-border-color: {{VALUE}};',
          ],
          'condition' => [
            'text_drop_cap_view' => 'framed',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'text_drop_cap_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-text-editor--drop-cap::first-letter',
        ]
      );

      $this->add_responsive_control(
        'text_drop_cap_padding',
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['em', 'px', 'rem'],
          'range' => [
            'em' => ['min' => 0, 'max' => 1, 'step' => 0.01],
            'px' => ['min' => 0, 'max' => 60, 'step' => 1],
            'rem' => ['min' => 0, 'max' => 4, 'step' => 0.1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-text-editor' => '--tms-text-editor-drop-cap-padding: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'text_drop_cap_view!' => 'default',
          ],
        ]
      );

      $this->add_responsive_control(
        'text_drop_cap_space',
        [
          'label' => esc_html__( 'Space', 'themeasy-lite' ),
          'description' => esc_html__( 'Gap between the drop cap and the text wrapping around it.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['em', 'px', 'rem'],
          'range' => [
            'em' => ['min' => 0, 'max' => 1, 'step' => 0.01],
            'px' => ['min' => 0, 'max' => 60, 'step' => 1],
            'rem' => ['min' => 0, 'max' => 4, 'step' => 0.1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-text-editor' => '--tms-text-editor-drop-cap-space: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'text_drop_cap_border_width',
        [
          'label' => esc_html__( 'Border Width', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem'],
          'range' => [
            'px' => ['min' => 0, 'max' => 20, 'step' => 1],
            'rem' => ['min' => 0, 'max' => 1, 'step' => 0.05],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-text-editor' => '--tms-text-editor-drop-cap-border-width: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'text_drop_cap_view' => 'framed',
          ],
        ]
      );

      $this->add_control(
        'text_drop_cap_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem', '%'],
          'range' => [
            'px' => ['min' => 0, 'max' => 100, 'step' => 1],
            'rem' => ['min' => 0, 'max' => 6, 'step' => 0.1],
            '%' => ['min' => 0, 'max' => 50, 'step' => 1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-text-editor' => '--tms-text-editor-drop-cap-radius: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'text_drop_cap_view!' => 'default',
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
    $text = $settings['text'] ?? '';

    // Early return if no content.
    if ( !$text ) {
      return;
    }

    // Rich text pipeline. Order is load-bearing:
    // 1. wp_kses_post() sanitizes what the user stored - it must run FIRST because it
    //    strips <iframe>, which the shortcodes and oEmbeds below legitimately produce.
    // 2. wpautop() wraps loose lines in <p> so Paragraph Spacing and Columns have real
    //    blocks to work with (the WYSIWYG only stores <p> when TinyMCE wrote them).
    // 3. parse_text_editor() is Elementor's own parser: balanceTags, shortcode_unautop,
    //    do_shortcode, wptexturize and oEmbed autoembed.
    $text = $this->parse_text_editor( wpautop( wp_kses_post( $text ) ) );

    // ------------------------------------------------------------------------
    // Wrapper classes + attributes.
    // ------------------------------------------------------------------------
    $wrapper_classes = ['tms-text-editor'];
    $wrapper_atts = [];

    // Drop cap. The view is whitelisted so the class can never carry raw setting data.
    if ( ( $settings['text_drop_cap'] ?? '' ) === 'yes' ) {
      $drop_cap_view = $settings['text_drop_cap_view'] ?? 'default';

      if ( !in_array( $drop_cap_view, ['default', 'stacked', 'framed'], true ) ) {
        $drop_cap_view = 'default';
      }

      $wrapper_classes[] = 'tms-text-editor--drop-cap';
      $wrapper_classes[] = 'tms-text-editor--drop-cap-' . $drop_cap_view;
    }

    $wrapper_classes_output = implode( ' ', array_filter( $wrapper_classes ) );

    // ------------------------------------------------------------------------
    // Render attributes.
    // ------------------------------------------------------------------------
    $this->add_render_attribute( 'text', 'class', $wrapper_classes_output );
    $this->add_inline_editing_attributes( 'text', 'advanced' );

    // ------------------------------------------------------------------------
    // Output.
    // ------------------------------------------------------------------------
    ?>
    <div <?php $this->print_render_attribute_string( 'text' ); ?><?php themeasy_print_html_attributes( $wrapper_atts ); ?>>
      <?php

      echo wp_kses( $text, themeasy_get_parsed_text_kses_tags() );
      ?>
    </div><!-- /.tms-text-editor -->
    <?php
  }

  /**
   * Render the live preview template for the Elementor editor.
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
      var text = settings.text || '';

      if ( ! text ) {
        return;
      }

      // ------------------------------------------------------------------------
      // Wrapper classes + attributes.
      // ------------------------------------------------------------------------
      var wrapperClasses = [ 'tms-text-editor' ];
      var wrapperAtts    = {};

      // Drop cap. The view is whitelisted so the class can never carry raw setting data.
      if ( settings.text_drop_cap === 'yes' ) {
        var dropCapView = settings.text_drop_cap_view || 'default';

        if ( [ 'default', 'stacked', 'framed' ].indexOf( dropCapView ) === -1 ) {
          dropCapView = 'default';
        }

        wrapperClasses.push(
          'tms-text-editor--drop-cap',
          'tms-text-editor--drop-cap-' + dropCapView
        );
      }

      var wrapperClassStr = wrapperClasses.filter( Boolean ).join( ' ' );

      // ------------------------------------------------------------------------
      // Render attributes.
      // ------------------------------------------------------------------------
      view.addRenderAttribute( 'text', 'class', wrapperClassStr );
      view.addInlineEditingAttributes( 'text', 'advanced' );
    #>

    <div {{{ view.getRenderAttributeString( 'text' ) }}} {{{ Themeasy.htmlAttributes( wrapperAtts ) }}}>
      {{{ sanitizeInline( text, 'rich' ) }}}
    </div><!-- /.tms-text-editor -->

    <?php
  }
}
