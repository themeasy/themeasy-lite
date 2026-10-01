<?php
/**
 * Themeasy Elementor Widget: Blockquote / Pull Quote
 *
 * Highlighted citation block with author/source attribution. Differs from the
 * testimonial widget (person photo + rating + card) by focusing on the quote
 * itself: a decorative quote mark, a body text, and a source line. Ships three
 * editorial skins (classic, pull-quote, card).
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
use Elementor\Widget_Base;

/**
 * Responsible for registering controls and rendering the Blockquote widget.
 */
class Blockquote extends Widget_Base {
  public function get_name() {
    return 'themeasy-blockquote';
  }

  public function get_title() {
    return esc_html__( 'Blockquote', 'themeasy-lite' );
  }

  public function get_icon() {
    return 'eicon-blockquote';
  }

  public function get_custom_help_url() {
    return 'https://themeasy.co/help-center';
  }

  public function get_categories() {
    return ['themeasy_text_category', 'themeasy_content_category'];
  }

  public function get_keywords() {
    return ['themeasy', 'blockquote', 'quote', 'pull quote', 'citation', 'cite', 'author'];
  }

  public function get_style_depends() {
    return ['themeasy-shared', 'themeasy-blockquote'];
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
    // Content section: Quote
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'blockquote_quote_section',
        [
          'label' => esc_html__( 'Quote', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
    );

      $this->add_control(
        'quote_text',
        [
          'label' => esc_html__( 'Quote', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXTAREA,
          'rows' => 5,
          'default' => esc_html__( 'Design is not just what it looks like and feels like. Design is how it works.', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'Type the quote here', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
        ]
      );

      $this->add_control(
        'quote_tag',
        [
          'label' => esc_html__( 'Wrapper Tag', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'blockquote',
          'options' => [
            'blockquote' => 'blockquote',
            'q' => 'q',
            'div' => 'div',
          ],
          'description' => esc_html__( 'Use "blockquote" for standalone quotes, "q" for inline, "div" for decorative.', 'themeasy-lite' ),
        ]
      );

      $this->add_control(
        'cite_url',
        [
          'label' => esc_html__( 'Citation URL', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::URL,
          'placeholder' => esc_html__( 'https://source-of-the-quote.com', 'themeasy-lite' ),
          'options' => ['url', 'is_external', 'nofollow'],
          'default' => [
            'url' => '',
            'is_external' => false,
            'nofollow' => false,
          ],
          'description' => esc_html__( 'Optional. Sets the cite attribute and, when provided, wraps the author name in a link.', 'themeasy-lite' ),
        ]
      );

      $this->add_control(
        'author_name',
        [
          'label' => esc_html__( 'Author', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'Steve Jobs', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'Leave blank to hide', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
        ]
      );

      $this->add_control(
        'author_role',
        [
          'label' => esc_html__( 'Role / Source', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'Co-founder, Apple', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'Leave blank to hide', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
        ]
      );

      $this->add_control(
        'show_separator',
        [
          'label' => esc_html__( 'Show Author Separator', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Show', 'themeasy-lite' ),
          'label_off' => esc_html__( 'Hide', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
          'description' => esc_html__( 'Small accent line displayed before the author name.', 'themeasy-lite' ),
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Layout
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'blockquote_layout_section',
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
          'default' => 'classic',
          'options' => [
            'classic' => esc_html__( 'Classic (minimal)', 'themeasy-lite' ),
            'pull-quote' => esc_html__( 'Pull Quote (editorial)', 'themeasy-lite' ),
            'card' => esc_html__( 'Card (elevated)', 'themeasy-lite' ),
          ],
          'description' => esc_html__( 'Choose the overall visual treatment of the quote block.', 'themeasy-lite' ),
        ]
      );

      $this->add_control(
        'pull_quote_border_side',
        [
          'label' => esc_html__( 'Accent Border Side', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'left',
          'options' => [
            'left' => esc_html__( 'Left', 'themeasy-lite' ),
            'top' => esc_html__( 'Top', 'themeasy-lite' ),
            'both' => esc_html__( 'Left + Top', 'themeasy-lite' ),
            '' => esc_html__( 'None', 'themeasy-lite' ),
          ],
          'condition' => [
            'skin' => 'pull-quote',
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
          'default' => 'center',
          'selectors' => [
            '{{WRAPPER}} .tms-blockquote' => 'text-align: {{VALUE}}; align-items: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'show_quote_mark',
        [
          'label' => esc_html__( 'Show Quote Mark', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Show', 'themeasy-lite' ),
          'label_off' => esc_html__( 'Hide', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'quote_mark_position',
        [
          'label' => esc_html__( 'Quote Mark Position', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'top',
          'options' => [
            'top' => esc_html__( 'Above Text', 'themeasy-lite' ),
            'inline-start' => esc_html__( 'Inline, before text', 'themeasy-lite' ),
            'behind' => esc_html__( 'Behind Text (watermark)', 'themeasy-lite' ),
          ],
          'condition' => [
            'show_quote_mark' => 'yes',
          ],
        ]
      );

      $this->add_control(
        'quote_mark_icon',
        [
          'label' => esc_html__( 'Custom Quote Icon', 'themeasy-lite' ),
          'type' => Controls_Manager::ICONS,
          'description' => esc_html__( 'Leave empty to use the default quote glyph.', 'themeasy-lite' ),
          'condition' => [
            'show_quote_mark' => 'yes',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Box
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'blockquote_box_styles_section',
        [
          'label' => esc_html__( 'Box', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      $this->add_control(
        'box_background_color',
        [
          'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-blockquote' => 'background-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'box_max_width',
        [
          'label' => esc_html__( 'Max Width', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', '%', 'em', 'rem', 'vw'],
          'range' => [
            'px' => ['min' => 200, 'max' => 1200, 'step' => 1],
            '%' => ['min' => 10, 'max' => 100, 'step' => 1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-blockquote' => 'max-width: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'box_padding',
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', 'em'],
          'selectors' => [
            '{{WRAPPER}} .tms-blockquote' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Border::get_type(),
        [
          'name' => 'box_border',
          'selector' => '{{WRAPPER}} .tms-blockquote',
        ]
      );

      $this->add_control(
        'box_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', '%', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-blockquote' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Box_Shadow::get_type(),
        [
          'name' => 'box_shadow',
          'label' => esc_html__( 'Box Shadow', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-blockquote',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Quote Text
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'blockquote_text_styles_section',
        [
          'label' => esc_html__( 'Quote Text', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      $this->add_control(
        'quote_text_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-blockquote .tms-blockquote__text' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'quote_text_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-blockquote .tms-blockquote__text',
        ]
      );

      $this->add_responsive_control(
        'quote_text_spacing',
        [
          'label' => esc_html__( 'Bottom Spacing', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem', 'em'],
          'range' => [
            'px' => ['min' => 0, 'max' => 80, 'step' => 1],
            'rem' => ['min' => 0, 'max' => 6, 'step' => 0.1],
          ],
          'default' => ['unit' => 'px', 'size' => 24],
          'selectors' => [
            '{{WRAPPER}} .tms-blockquote .tms-blockquote__text' => 'margin-bottom: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Author
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'blockquote_author_styles_section',
        [
          'label' => esc_html__( 'Author', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      $this->add_control(
        'author_name_heading',
        [
          'label' => esc_html__( 'Name', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
        ]
      );

      $this->add_control(
        'author_name_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-blockquote .tms-blockquote__author,
             {{WRAPPER}} .tms-blockquote .tms-blockquote__author-link' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'author_name_hover_color',
        [
          'label' => esc_html__( 'Hover Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-blockquote .tms-blockquote__author-link:hover,
             {{WRAPPER}} .tms-blockquote .tms-blockquote__author-link:focus-visible' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'author_name_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-blockquote .tms-blockquote__author',
        ]
      );

      $this->add_responsive_control(
        'author_spacing',
        [
          'label' => esc_html__( 'Top Spacing', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem', 'em'],
          'range' => [
            'px' => ['min' => 0, 'max' => 80, 'step' => 1],
            'rem' => ['min' => 0, 'max' => 6, 'step' => 0.1],
          ],
          'default' => ['unit' => 'px', 'size' => 12],
          'selectors' => [
            '{{WRAPPER}} .tms-blockquote .tms-blockquote__source' => 'margin-top: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'author_role_heading',
        [
          'label' => esc_html__( 'Role / Source', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'author_role_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-blockquote .tms-blockquote__role' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'author_role_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-blockquote .tms-blockquote__role',
        ]
      );

      $this->add_responsive_control(
        'author_role_spacing',
        [
          'label' => esc_html__( 'Top Spacing', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem', 'em'],
          'range' => [
            'px' => ['min' => 0, 'max' => 40, 'step' => 1],
            'rem' => ['min' => 0, 'max' => 4, 'step' => 0.1],
          ],
          'default' => ['unit' => 'px', 'size' => 2],
          'selectors' => [
            '{{WRAPPER}} .tms-blockquote .tms-blockquote__role' => 'margin-top: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Quote Mark
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'blockquote_mark_styles_section',
        [
          'label' => esc_html__( 'Quote Mark', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'show_quote_mark' => 'yes',
          ],
        ]
    );

      $this->add_control(
        'quote_mark_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-blockquote .tms-blockquote__mark' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'quote_mark_fill_color',
        [
          'label' => esc_html__( 'Fill Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'description' => esc_html__( 'Overrides the icon fill for SVG icons. Leave empty to inherit the color above.', 'themeasy-lite' ),
          'selectors' => [
            '{{WRAPPER}} .tms-blockquote .tms-blockquote__mark svg' => 'fill: {{VALUE}};',
          ],
          'conditions' => [
            'terms' => [
              ['name' => 'quote_mark_icon[library]', 'operator' => 'in', 'value' => themeasy_svg_icon_condition_libraries()],
            ],
          ],
        ]
      );

      $this->add_control(
        'quote_mark_stroke_color',
        [
          'label' => esc_html__( 'Stroke Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'description' => esc_html__( 'Overrides the icon stroke for outline (Feather-style) SVG icons.', 'themeasy-lite' ),
          'selectors' => [
            '{{WRAPPER}} .tms-blockquote .tms-blockquote__mark svg' => 'stroke: {{VALUE}};',
          ],
          'conditions' => [
            'terms' => [
              ['name' => 'quote_mark_icon[library]', 'operator' => 'in', 'value' => themeasy_svg_icon_condition_libraries()],
            ],
          ],
        ]
      );

      $this->add_responsive_control(
        'quote_mark_size',
        [
          'label' => esc_html__( 'Size', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'em', 'rem'],
          'range' => [
            'px' => ['min' => 12, 'max' => 280, 'step' => 1],
            'em' => ['min' => 0.5, 'max' => 16, 'step' => 0.1],
            'rem' => ['min' => 0.5, 'max' => 16, 'step' => 0.1],
          ],
          // No default on purpose: the control writes a single token and each mark
          // position supplies its own fallback in CSS (3rem for the glyph, 8rem for
          // the watermark). A hard default here would emit on every blockquote and,
          // at Elementor's specificity, silently overwrite those per-position
          // defaults -- which is exactly what used to flatten the watermark to 48px.
          'selectors' => [
            '{{WRAPPER}} .tms-blockquote' => '--tms-blockquote-mark-size: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'quote_mark_thickness',
        [
          'label' => esc_html__( 'Stroke Thickness', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => ['min' => 0.5, 'max' => 10, 'step' => 0.5],
          ],
          'default' => ['unit' => 'px', 'size' => 2],
          'selectors' => [
            '{{WRAPPER}} .tms-blockquote .tms-blockquote__mark svg,
             {{WRAPPER}} .tms-blockquote .tms-blockquote__mark svg *' => 'stroke-width: {{SIZE}}{{UNIT}};',
          ],
          'conditions' => [
            'terms' => [
              ['name' => 'quote_mark_icon[library]', 'operator' => 'in', 'value' => themeasy_svg_icon_condition_libraries()],
            ],
          ],
        ]
      );

      $this->add_control(
        'quote_mark_opacity',
        [
          'label' => esc_html__( 'Opacity', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['u'],
          'range' => ['u' => ['min' => 0, 'max' => 1, 'step' => 0.05]],
          // The default names the unit but stays sizeless on purpose: naming it is
          // what makes the 0-1 range above reachable (Elementor otherwise resolves
          // the unit to 'px' and serves its own 0-100 range), while a hard 1 would
          // be a visual no-op everywhere except the watermark, where it overrode
          // the 0.08 wash the "Behind Text" position depends on.
          'default' => ['unit' => 'u', 'size' => ''],
          'selectors' => [
            '{{WRAPPER}} .tms-blockquote .tms-blockquote__mark' => 'opacity: {{SIZE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'quote_mark_offset',
        [
          'label' => esc_html__( 'Bottom Spacing', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem', 'em'],
          'range' => [
            'px' => ['min' => 0, 'max' => 80, 'step' => 1],
            'rem' => ['min' => 0, 'max' => 6, 'step' => 0.1],
          ],
          'default' => ['unit' => 'px', 'size' => 16],
          'selectors' => [
            '{{WRAPPER}} .tms-blockquote .tms-blockquote__mark' => 'margin-bottom: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'quote_mark_position' => 'top',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Accent
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'blockquote_accent_styles_section',
        [
          'label' => esc_html__( 'Accent', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'description' => esc_html__( 'Controls the pull-quote accent border and the author separator.', 'themeasy-lite' ),
        ]
    );

      $this->add_control(
        'accent_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-blockquote' => '--tms-blockquote-accent: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'accent_thickness',
        [
          'label' => esc_html__( 'Thickness', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => ['px' => ['min' => 1, 'max' => 12, 'step' => 1]],
          'default' => ['unit' => 'px', 'size' => 2],
          'selectors' => [
            '{{WRAPPER}} .tms-blockquote' => '--tms-blockquote-accent-thickness: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'separator_length',
        [
          'label' => esc_html__( 'Author Separator Length', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem', 'em'],
          'range' => [
            'px' => ['min' => 8, 'max' => 120, 'step' => 1],
            'rem' => ['min' => 0.5, 'max' => 8, 'step' => 0.1],
          ],
          'default' => ['unit' => 'px', 'size' => 24],
          'selectors' => [
            '{{WRAPPER}} .tms-blockquote .tms-blockquote__separator' => 'width: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'show_separator' => 'yes',
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
    $quote_text = $settings['quote_text'] ?? '';
    $quote_tag = in_array( ( $settings['quote_tag'] ?? 'blockquote' ), ['blockquote', 'q', 'div'], true )
      ? $settings['quote_tag']
      : 'blockquote';
    $cite_url_raw = $settings['cite_url']['url'] ?? '';
    $author_name = $settings['author_name'] ?? '';
    $author_role = $settings['author_role'] ?? '';
    $show_separator = ( $settings['show_separator'] ?? '' ) === 'yes';

    // Allowlist the values that feed CSS class names (defends against tampered stored data).
    $skin = in_array( ( $settings['skin'] ?? 'classic' ), ['classic', 'pull-quote', 'card'], true )
      ? $settings['skin']
      : 'classic';
    $border_side = in_array( ( $settings['pull_quote_border_side'] ?? 'left' ), ['left', 'top', 'both', ''], true )
      ? $settings['pull_quote_border_side']
      : 'left';
    $show_quote_mark = ( $settings['show_quote_mark'] ?? '' ) === 'yes';
    $mark_position = in_array( ( $settings['quote_mark_position'] ?? 'top' ), ['top', 'inline-start', 'behind'], true )
      ? $settings['quote_mark_position']
      : 'top';
    $mark_icon = $settings['quote_mark_icon'] ?? [];

    // Early return when there is nothing to show.
    if ( empty( $quote_text ) && empty( $author_name ) && empty( $author_role ) ) {
      return;
    }

    // ------------------------------------------------------------------------
    // Wrapper classes + attributes.
    // ------------------------------------------------------------------------
    $wrapper_classes = ['tms-blockquote', 'tms-blockquote--skin-' . $skin];
    $wrapper_atts = [];

    if ( 'pull-quote' === $skin && $border_side ) {
      $wrapper_classes[] = 'tms-blockquote--border-' . $border_side;
    }

    if ( $show_quote_mark ) {
      $wrapper_classes[] = 'tms-blockquote--mark-' . $mark_position;
    } else {
      $wrapper_classes[] = 'tms-blockquote--no-mark';
    }

    $wrapper_classes_output = implode( ' ', array_filter( $wrapper_classes ) );
    $wrapper_atts_output = themeasy_html_attributes( $wrapper_atts );

    // The cite attribute is emitted manually with esc_url(); routing it through
    // themeasy_html_attributes() would re-apply esc_attr() and double-encode "&".
    $cite_attr_output = !empty( $cite_url_raw ) ? ' cite="' . esc_url( $cite_url_raw ) . '"' : '';

    // ------------------------------------------------------------------------
    // Link attributes (author link uses the citation URL when present).
    // ------------------------------------------------------------------------
    $has_author_link = !empty( $cite_url_raw ) && !empty( $author_name );

    if ( $has_author_link ) {
      $this->add_link_attributes( 'author_link', $settings['cite_url'] );
      themeasy_add_external_link_rel( $this, 'author_link', $settings['cite_url'] );
      $this->add_render_attribute( 'author_link', 'class', 'tms-blockquote__author-link' );
    }

    // ------------------------------------------------------------------------
    // Render attributes.
    // ------------------------------------------------------------------------
    $this->add_render_attribute( 'quote_text', 'class', 'tms-blockquote__text' );
    $this->add_inline_editing_attributes( 'quote_text', 'advanced' );

    $this->add_render_attribute( 'author_name', 'class', 'tms-blockquote__author' );
    $this->add_inline_editing_attributes( 'author_name', 'none' );

    $this->add_render_attribute( 'author_role', 'class', 'tms-blockquote__role' );
    $this->add_inline_editing_attributes( 'author_role', 'none' );

    // ------------------------------------------------------------------------
    // Quote mark icon.
    // ------------------------------------------------------------------------
    $mark_html = '';

    if ( $show_quote_mark ) {
      if ( !empty( $mark_icon['value'] ) ) {
        $mark_html = themeasy_render_icon_html(
          $mark_icon,
          ['class' => 'tms-blockquote__mark-icon', 'aria-hidden' => 'true']
        );
      } else {
        $mark_html = themeasy_get_svg_icon( 'ty-feather', 'quote-open', 'tms-blockquote__mark-icon' );
      }
    }

    // ------------------------------------------------------------------------
    // Output.
    // ------------------------------------------------------------------------
    $tag = tag_escape( $quote_tag );
    ?>
    <<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>
      class="<?php echo esc_attr( $wrapper_classes_output ); ?>"<?php echo $cite_attr_output . $wrapper_atts_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>>

      <?php if ( $show_quote_mark && $mark_html ) : ?>
        <span class="tms-blockquote__mark" aria-hidden="true">
          <?php echo $mark_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>
        </span>
      <?php endif; ?>

      <?php if ( $quote_text ) : ?>
        <p <?php $this->print_render_attribute_string( 'quote_text' ); ?>>
          <?php echo wp_kses( $quote_text, themeasy_get_kses_allowed_tags( 'inline' ) ); ?>
        </p>
      <?php endif; ?>

      <?php if ( $author_name || $author_role ) : ?>
        <footer class="tms-blockquote__source">

          <?php if ( $show_separator ) : ?>
            <span class="tms-blockquote__separator" aria-hidden="true"></span>
          <?php endif; ?>

          <?php if ( $author_name ) : ?>
            <?php if ( $has_author_link ) : ?>
              <a <?php $this->print_render_attribute_string( 'author_link' ); ?>>
                <span <?php $this->print_render_attribute_string( 'author_name' ); ?>>
                  <?php echo esc_html( $author_name ); ?>
                </span>
              </a>
            <?php else : ?>
              <span <?php $this->print_render_attribute_string( 'author_name' ); ?>>
                <?php echo esc_html( $author_name ); ?>
              </span>
            <?php endif; ?>
          <?php endif; ?>

          <?php if ( $author_role ) : ?>
            <span <?php $this->print_render_attribute_string( 'author_role' ); ?>>
              <?php echo esc_html( $author_role ); ?>
            </span>
          <?php endif; ?>

        </footer>
      <?php endif; ?>

    </<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>><!-- /.tms-blockquote -->
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
      var quoteText     = sanitizeInline( settings.quote_text || '' );
      var allowedTags   = [ 'blockquote', 'q', 'div' ];
      var quoteTag      = settings.quote_tag || 'blockquote';
      if ( allowedTags.indexOf( quoteTag ) === -1 ) { quoteTag = 'blockquote'; }

      var citeUrlRaw    = ( settings.cite_url && settings.cite_url.url ) || '';
      var authorName    = settings.author_name || '';
      var authorRole    = settings.author_role || '';
      var showSeparator = settings.show_separator === 'yes';

      var skin           = settings.skin || 'classic';
      // Not `|| 'left'`: "None" is the empty string, which `||` would coerce back to
      // 'left' and re-draw the accent border in the preview (mirrors render()'s `??`).
      var borderSide     = ( typeof settings.pull_quote_border_side === 'undefined' )
        ? 'left'
        : settings.pull_quote_border_side;
      var showQuoteMark  = settings.show_quote_mark === 'yes';
      var markPosition   = settings.quote_mark_position || 'top';
      var markIconObj    = settings.quote_mark_icon || {};

      // Allowlist the values that feed CSS class names (mirrors render()).
      if ( [ 'classic', 'pull-quote', 'card' ].indexOf( skin ) === -1 ) { skin = 'classic'; }
      if ( [ 'left', 'top', 'both', '' ].indexOf( borderSide ) === -1 ) { borderSide = 'left'; }
      if ( [ 'top', 'inline-start', 'behind' ].indexOf( markPosition ) === -1 ) { markPosition = 'top'; }

      if ( ! quoteText && ! authorName && ! authorRole ) {
        return;
      }

      // ------------------------------------------------------------------------
      // Wrapper classes + attributes.
      // ------------------------------------------------------------------------
      var wrapperClasses = [ 'tms-blockquote', 'tms-blockquote--skin-' + skin ];
      var wrapperAtts    = {};

      if ( skin === 'pull-quote' && borderSide ) {
        wrapperClasses.push( 'tms-blockquote--border-' + borderSide );
      }

      if ( showQuoteMark ) {
        wrapperClasses.push( 'tms-blockquote--mark-' + markPosition );
      } else {
        wrapperClasses.push( 'tms-blockquote--no-mark' );
      }

      // Scheme guard for the cite attribute and the author link. The regex lives
      // in exactly one place; the fallback fails closed rather than duplicating
      // it (a bare ^ anchor misses the C0 controls browsers strip first).
      var safeUrl = ( window.Themeasy && window.Themeasy.safeUrl )
        ? window.Themeasy.safeUrl
        : function () { return ''; };

      var safeCite = safeUrl( citeUrlRaw );
      if ( safeCite ) {
        wrapperAtts['cite'] = safeCite;
      }

      var wrapperClassStr = wrapperClasses.filter( Boolean ).join( ' ' );

      // ------------------------------------------------------------------------
      // Render attributes.
      // ------------------------------------------------------------------------
      view.addRenderAttribute( 'quote_text', 'class', 'tms-blockquote__text' );
      view.addInlineEditingAttributes( 'quote_text', 'advanced' );

      view.addRenderAttribute( 'author_name', 'class', 'tms-blockquote__author' );
      view.addInlineEditingAttributes( 'author_name', 'none' );

      view.addRenderAttribute( 'author_role', 'class', 'tms-blockquote__role' );
      view.addInlineEditingAttributes( 'author_role', 'none' );

      var hasAuthorLink = !! ( safeCite && authorName );

      if ( hasAuthorLink ) {
        view.addRenderAttribute( 'author_link', 'class', 'tms-blockquote__author-link' );
        view.addRenderAttribute( 'author_link', 'href', safeCite );

        // Mirror add_link_attributes(): open in new tab / nofollow from the URL control.
        if ( settings.cite_url && settings.cite_url.is_external ) {
          view.addRenderAttribute( 'author_link', 'target', '_blank' );
        }
        if ( settings.cite_url && settings.cite_url.nofollow ) {
          view.addRenderAttribute( 'author_link', 'rel', 'nofollow' );
        }
      }

      // ------------------------------------------------------------------------
      // Quote mark icon.
      // ------------------------------------------------------------------------
      var markMarkup = '';

      if ( showQuoteMark ) {
        if ( markIconObj && markIconObj.value ) {
          markMarkup = ( window.Themeasy && window.Themeasy.renderIconMarkup )
            ? window.Themeasy.renderIconMarkup(
                view, markIconObj, null,
                { 'class': 'tms-blockquote__mark-icon', 'aria-hidden': 'true' }
              )
            : '';
        } else {
          markMarkup = <?php echo wp_json_encode( themeasy_get_svg_icon( 'ty-feather', 'quote-open', 'tms-blockquote__mark-icon' ) ); ?>;
        }
      }
    #>

    <{{ quoteTag }} class="{{ wrapperClassStr }}" {{{ Themeasy.htmlAttributes( wrapperAtts ) }}}>

      <# if ( showQuoteMark && markMarkup ) { #>
        <span class="tms-blockquote__mark" aria-hidden="true">{{{ markMarkup }}}</span>
      <# } #>

      <# if ( quoteText ) { #>
        <p {{{ view.getRenderAttributeString( 'quote_text' ) }}}>{{{ quoteText }}}</p>
      <# } #>

      <# if ( authorName || authorRole ) { #>
        <footer class="tms-blockquote__source">

          <# if ( showSeparator ) { #>
            <span class="tms-blockquote__separator" aria-hidden="true"></span>
          <# } #>

          <# if ( authorName ) { #>
            <# if ( hasAuthorLink ) { #>
              <a {{{ view.getRenderAttributeString( 'author_link' ) }}}>
                <span {{{ view.getRenderAttributeString( 'author_name' ) }}}>{{ authorName }}</span>
              </a>
            <# } else { #>
              <span {{{ view.getRenderAttributeString( 'author_name' ) }}}>{{ authorName }}</span>
            <# } #>
          <# } #>

          <# if ( authorRole ) { #>
            <span {{{ view.getRenderAttributeString( 'author_role' ) }}}>{{ authorRole }}</span>
          <# } #>

        </footer>
      <# } #>

    </{{ quoteTag }}>

    <?php
  }
}
