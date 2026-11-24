<?php
/**
 * Themeasy Elementor Widget: Code Terminal
 *
 * An animated terminal window (macOS-style chrome by default) that types
 * command lines character-by-character behind a blinking block cursor and
 * reveals their output lines in sequence, optionally looping. Built as a
 * marketing artifact for SaaS / dev-tool landing pages: repeater-driven
 * command / output / success / error / comment lines, a global or per-line
 * prompt symbol, and a copy button in the title bar that copies every
 * command line to the clipboard.
 *
 * Layering / degradation:
 *  - The DOM ships the FULL final transcript: with no JavaScript the terminal
 *    is complete and readable. The engine only hides lines after it snapshots
 *    them (a JS-applied --armed class gates the hidden state).
 *  - In the Elementor editor the widget renders --static: the full transcript
 *    shows and the typing engine never runs (no fight with inline editing).
 *  - Under prefers-reduced-motion the engine never arms and the CSS stops the
 *    cursor blink — the transcript stays fully visible and still.
 *  - Screen readers get a visually-hidden block with the full transcript; the
 *    animated list is aria-hidden so the flicker is never announced.
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
class CodeTerminal extends Widget_Base {
  public function get_name() {
    return 'themeasy-code-terminal';
  }

  public function get_title() {
    return esc_html__( 'Code Terminal', 'themeasy-lite' );
  }

  public function get_icon() {
    return 'eicon-code';
  }

  public function get_custom_help_url() {
    return 'https://hub.themeasy.co/docs';
  }

  public function get_categories() {
    return ['themeasy_text_category', 'themeasy_content_category'];
  }

  public function get_keywords() {
    return ['themeasy', 'terminal', 'console', 'code', 'command', 'typing', 'cli', 'developer'];
  }

  public function get_style_depends() {
    return ['themeasy-shared', 'themeasy-code-terminal'];
  }

  public function get_script_depends() {
    return ['themeasy-code-terminal'];
  }

  /**
   * The line-type keys shared by render() and content_template().
   *
   * @return array<int, string>
   */
  protected function get_line_types(): array {
    return ['command', 'output', 'success', 'error', 'comment'];
  }

  /**
   * Register widget customization controls.
   *
   * @return void
   */
  protected function register_controls() {
    // ------------------------------------------------------------------------
    // Content section: Lines
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'code_terminal_lines_section',
        [
          'label' => esc_html__( 'Lines', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $repeater = new Repeater();

      $repeater->add_control(
        'line_type',
        [
          'label' => esc_html__( 'Type', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'command',
          'options' => [
            'command' => esc_html__( 'Command', 'themeasy-lite' ),
            'output' => esc_html__( 'Output', 'themeasy-lite' ),
            'success' => esc_html__( 'Success', 'themeasy-lite' ),
            'error' => esc_html__( 'Error', 'themeasy-lite' ),
            'comment' => esc_html__( 'Comment', 'themeasy-lite' ),
          ],
        ]
      );

      $repeater->add_control(
        'line_text',
        [
          'label' => esc_html__( 'Text', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXT,
          'default' => '',
          'placeholder' => esc_html__( 'Type the line text', 'themeasy-lite' ),
          'dynamic' => [
            'active' => true,
          ],
        ]
      );

      $repeater->add_control(
        'line_prompt',
        [
          'label' => esc_html__( 'Prompt', 'themeasy-lite' ),
          'description' => esc_html__( 'Overrides the global prompt symbol for this command. Empty inherits it.', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'default' => '',
          'placeholder' => '$',
          'condition' => [
            'line_type' => 'command',
          ],
        ]
      );

      $repeater->add_control(
        'line_delay',
        [
          'label' => esc_html__( 'Delay Before', 'themeasy-lite' ),
          'description' => esc_html__( 'Extra pause before this line appears.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['s'],
          'range' => [
            's' => [
              'min' => 0,
              'max' => 5,
              'step' => 0.1,
            ],
          ],
          'default' => [
            'unit' => 's',
            'size' => 0,
          ],
        ]
      );

      $this->add_control(
        'lines',
        [
          'label' => esc_html__( 'Lines', 'themeasy-lite' ),
          'type' => Controls_Manager::REPEATER,
          'fields' => $repeater->get_controls(),
          'default' => [
            [
              'line_type' => 'command',
              'line_text' => 'npm install themeasy',
            ],
            [
              'line_type' => 'output',
              'line_text' => esc_html__( 'added 42 packages in 1.2s', 'themeasy-lite' ),
            ],
            [
              'line_type' => 'success',
              'line_text' => esc_html__( '✓ Build completed', 'themeasy-lite' ),
            ],
            [
              'line_type' => 'command',
              'line_text' => 'npm run dev',
            ],
            [
              'line_type' => 'output',
              'line_text' => 'Local: http://localhost:3000',
            ],
          ],
          'title_field' => '{{{ line_text }}}',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Window
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'code_terminal_window_section',
        [
          'label' => esc_html__( 'Window', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $this->add_control(
        'chrome_style',
        [
          'label' => esc_html__( 'Window Chrome', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'mac',
          'options' => [
            'mac' => esc_html__( 'macOS', 'themeasy-lite' ),
            'minimal' => esc_html__( 'Minimal', 'themeasy-lite' ),
            'none' => esc_html__( 'None', 'themeasy-lite' ),
          ],
        ]
      );

      $this->add_control(
        'window_title',
        [
          'label' => esc_html__( 'Window Title', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( '~ — zsh', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'Title bar text', 'themeasy-lite' ),
          'dynamic' => [
            'active' => true,
          ],
          'condition' => [
            'chrome_style!' => 'none',
          ],
        ]
      );

      $this->add_control(
        'prompt_symbol',
        [
          'label' => esc_html__( 'Prompt Symbol', 'themeasy-lite' ),
          'description' => esc_html__( 'Printed before every command line. Individual lines can override it.', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'default' => '$',
        ]
      );

      $this->add_control(
        'show_copy',
        [
          'label' => esc_html__( 'Copy Button', 'themeasy-lite' ),
          'description' => esc_html__( 'Title-bar button that copies all command lines to the clipboard.', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Show', 'themeasy-lite' ),
          'label_off' => esc_html__( 'Hide', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
          'condition' => [
            'chrome_style!' => 'none',
          ],
        ]
      );

      $this->add_responsive_control(
        'min_height',
        [
          'label' => esc_html__( 'Minimum Height', 'themeasy-lite' ),
          'description' => esc_html__( 'Minimum height of the terminal body. Empty grows with the content.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => [
              'min' => 0,
              'max' => 800,
              'step' => 10,
            ],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-code-terminal__body' => 'min-height: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Typing
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'code_terminal_typing_section',
        [
          'label' => esc_html__( 'Typing', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $this->add_control(
        'start_on_view',
        [
          'label' => esc_html__( 'Start On View', 'themeasy-lite' ),
          'description' => esc_html__( 'Start typing when the terminal scrolls into view.', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
        ]
      );

      $this->add_control(
        'loop',
        [
          'label' => esc_html__( 'Loop', 'themeasy-lite' ),
          'description' => esc_html__( 'Restart the sequence a moment after it completes.', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => '',
        ]
      );

      $this->add_control(
        'typing_speed',
        [
          'label' => esc_html__( 'Typing Speed', 'themeasy-lite' ),
          'description' => esc_html__( 'Milliseconds per character while a command types.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['ms'],
          'range' => [
            'ms' => [
              'min' => 10,
              'max' => 150,
              'step' => 5,
            ],
          ],
          'default' => [
            'unit' => 'ms',
            'size' => 45,
          ],
        ]
      );

      $this->add_control(
        'line_pause',
        [
          'label' => esc_html__( 'Line Pause', 'themeasy-lite' ),
          'description' => esc_html__( 'Pause between lines; output lines reveal with a faster stagger.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['s'],
          'range' => [
            's' => [
              'min' => 0,
              'max' => 5,
              'step' => 0.1,
            ],
          ],
          'default' => [
            'unit' => 's',
            'size' => 0.5,
          ],
        ]
      );

      $this->add_control(
        'start_delay',
        [
          'label' => esc_html__( 'Start Delay', 'themeasy-lite' ),
          'description' => esc_html__( 'Pause before the first line starts typing.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['s'],
          'range' => [
            's' => [
              'min' => 0,
              'max' => 5,
              'step' => 0.1,
            ],
          ],
          'default' => [
            'unit' => 's',
            'size' => 0.3,
          ],
        ]
      );

      $this->add_control(
        'show_cursor',
        [
          'label' => esc_html__( 'Show Cursor', 'themeasy-lite' ),
          'description' => esc_html__( 'Blinking block cursor at the end of the active command.', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
        ]
      );

      $this->add_control(
        'natural_rhythm',
        [
          'label' => esc_html__( 'Natural Rhythm', 'themeasy-lite' ),
          'description' => esc_html__( 'Subtle per-character speed variation so the typing feels human.', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Window
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'code_terminal_window_styles_section',
        [
          'label' => esc_html__( 'Window', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_control(
        'window_background_color',
        [
          'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-code-terminal' => 'background-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'window_padding',
        [
          'label' => esc_html__( 'Body Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'em', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-code-terminal__body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Border::get_type(),
        [
          'name' => 'window_border',
          'selector' => '{{WRAPPER}} .tms-code-terminal',
        ]
      );

      $this->add_responsive_control(
        'window_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', '%'],
          'selectors' => [
            '{{WRAPPER}} .tms-code-terminal' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Box_Shadow::get_type(),
        [
          'name' => 'window_box_shadow',
          'selector' => '{{WRAPPER}} .tms-code-terminal',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Title Bar
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'code_terminal_bar_styles_section',
        [
          'label' => esc_html__( 'Title Bar', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'chrome_style!' => 'none',
          ],
        ]
      );

      $this->add_control(
        'bar_background_color',
        [
          'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-code-terminal__bar' => 'background-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'bar_title_color',
        [
          'label' => esc_html__( 'Title Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-code-terminal__title' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'bar_title_typography',
          'label' => esc_html__( 'Title Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-code-terminal__title',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Text
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'code_terminal_text_styles_section',
        [
          'label' => esc_html__( 'Text', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      // The CSS default is a ui-monospace stack; the control stays family-free
      // so an unset group keeps the authentic terminal face.
      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'terminal_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-code-terminal__body',
        ]
      );

      $this->add_responsive_control(
        'line_gap',
        [
          'label' => esc_html__( 'Line Gap', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'em'],
          'range' => [
            'px' => [
              'min' => 0,
              'max' => 40,
              'step' => 1,
            ],
            'em' => [
              'min' => 0,
              'max' => 2,
              'step' => 0.05,
            ],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-code-terminal__lines' => 'row-gap: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'text_colors_heading',
        [
          'label' => esc_html__( 'Colors', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'prompt_color',
        [
          'label' => esc_html__( 'Prompt Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-code-terminal__prompt' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'command_color',
        [
          'label' => esc_html__( 'Command Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-code-terminal__line--command .tms-code-terminal__text' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'output_color',
        [
          'label' => esc_html__( 'Output Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-code-terminal__line--output .tms-code-terminal__text' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'success_color',
        [
          'label' => esc_html__( 'Success Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-code-terminal__line--success .tms-code-terminal__text' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'error_color',
        [
          'label' => esc_html__( 'Error Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-code-terminal__line--error .tms-code-terminal__text' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'comment_color',
        [
          'label' => esc_html__( 'Comment Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-code-terminal__line--comment .tms-code-terminal__text' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'cursor_color',
        [
          'label' => esc_html__( 'Cursor Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-code-terminal__cursor' => 'background-color: {{VALUE}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Copy Button
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'code_terminal_copy_styles_section',
        [
          'label' => esc_html__( 'Copy Button', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'chrome_style!' => 'none',
            'show_copy' => 'yes',
          ],
        ]
      );

      $this->start_controls_tabs(
        'copy_tabs'
        );

        $this->start_controls_tab(
          'copy_normal_tab',
            [
              'label' => esc_html__( 'Normal', 'themeasy-lite' ),
            ]
          );

          $this->add_control(
            'copy_color',
            [
              'label' => esc_html__( 'Icon Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-code-terminal__copy' => 'color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

        $this->start_controls_tab(
          'copy_hover_tab',
            [
              'label' => esc_html__( 'Hover', 'themeasy-lite' ),
            ]
          );

          $this->add_control(
            'copy_hover_color',
            [
              'label' => esc_html__( 'Icon Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-code-terminal__copy:hover,
                 {{WRAPPER}} .tms-code-terminal__copy:focus-visible' => 'color: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            'copy_hover_background_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-code-terminal__copy:hover,
                 {{WRAPPER}} .tms-code-terminal__copy:focus-visible' => 'background-color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

    $this->end_controls_section();

    // No Advanced sections: the typing sequence IS this widget's entrance.
  }

  /**
   * Normalize the repeater rows into render-ready line arrays.
   *
   * @param array $settings Widget settings.
   * @return array<int, array{type: string, text: string, prompt: string, delay: float}>
   */
  protected function get_prepared_lines( array $settings ): array {
    $allowed_types = $this->get_line_types();
    $global_prompt = trim( (string) ( $settings['prompt_symbol'] ?? '$' ) );
    $lines = [];

    if ( empty( $settings['lines'] ) || !is_array( $settings['lines'] ) ) {
      return $lines;
    }

    foreach ( $settings['lines'] as $index => $item ) {
      $text = isset( $item['line_text'] ) ? (string) $item['line_text'] : '';

      if ( '' === trim( $text ) ) {
        continue;
      }

      $type = isset( $item['line_type'] ) && in_array( $item['line_type'], $allowed_types, true )
        ? $item['line_type']
        : 'command';

      $prompt = '';

      if ( 'command' === $type ) {
        $own_prompt = trim( (string) ( $item['line_prompt'] ?? '' ) );
        $prompt = '' !== $own_prompt ? $own_prompt : $global_prompt;
      }

      $delay = is_numeric( $item['line_delay']['size'] ?? null ) ? (float) $item['line_delay']['size'] : 0;

      $lines[] = [
        // Original repeater index — blank lines are skipped, so the array key
        // would drift out of step with the setting key the editor expects.
        'index' => $index,
        'type' => $type,
        'text' => $text,
        'prompt' => $prompt,
        'delay' => max( 0, $delay ),
      ];
    }

    return $lines;
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
    $lines = $this->get_prepared_lines( $settings );

    // Early return if no content.
    if ( empty( $lines ) ) {
      return;
    }

    $chrome = $settings['chrome_style'] ?? 'mac';

    if ( !in_array( $chrome, ['mac', 'minimal', 'none'], true ) ) {
      $chrome = 'mac';
    }

    $window_title = 'none' !== $chrome ? (string) ( $settings['window_title'] ?? '' ) : '';
    $show_copy = 'none' !== $chrome && ( $settings['show_copy'] ?? 'yes' ) === 'yes';

    // is_numeric() (not isset) so a cleared slider falls back exactly like the
    // editor template does, keeping frontend and preview data-attrs in step.
    $typing_speed = is_numeric( $settings['typing_speed']['size'] ?? null ) ? (float) $settings['typing_speed']['size'] : 45;
    $line_pause = is_numeric( $settings['line_pause']['size'] ?? null ) ? (float) $settings['line_pause']['size'] : 0.5;
    $start_delay = is_numeric( $settings['start_delay']['size'] ?? null ) ? (float) $settings['start_delay']['size'] : 0.3;

    $start_on_view = ( $settings['start_on_view'] ?? 'yes' ) === 'yes';
    $loop = ( $settings['loop'] ?? '' ) === 'yes';
    $show_cursor = ( $settings['show_cursor'] ?? 'yes' ) === 'yes';
    $natural_rhythm = ( $settings['natural_rhythm'] ?? 'yes' ) === 'yes';

    $is_editor = themeasy_is_elementor_editor();

    // ------------------------------------------------------------------------
    // Wrapper classes + attributes.
    // ------------------------------------------------------------------------
    $wrapper_classes = ['tms-code-terminal', 'tms-code-terminal--' . $chrome];

    if ( $is_editor ) {
      $wrapper_classes[] = 'tms-code-terminal--static';
    }

    $wrapper_classes_output = implode( ' ', array_filter( $wrapper_classes ) );

    $wrapper_atts = [
      'data-typing-speed' => (string) $typing_speed,
      'data-line-pause' => (string) $line_pause,
      'data-start-delay' => (string) $start_delay,
      'data-start-on-view' => $start_on_view ? 'true' : 'false',
      'data-loop' => $loop ? 'true' : 'false',
      'data-show-cursor' => $show_cursor ? 'true' : 'false',
      'data-natural-rhythm' => $natural_rhythm ? 'true' : 'false',
    ];

    // ------------------------------------------------------------------------
    // Render attributes.
    // ------------------------------------------------------------------------
    $this->add_render_attribute( 'window_title', 'class', 'tms-code-terminal__title' );
    $this->add_inline_editing_attributes( 'window_title', 'none' );

    // ------------------------------------------------------------------------
    // Output. The list ships the FULL transcript (no-JS complete); the engine
    // hides and retypes it only after snapshotting. The animated list stays
    // aria-hidden — screen readers read the visually-hidden transcript block.
    // ------------------------------------------------------------------------
    ?>
    <div class="<?php echo esc_attr( $wrapper_classes_output ); ?>"<?php themeasy_print_html_attributes( $wrapper_atts ); ?>>

      <?php if ( 'none' !== $chrome ) : ?>
        <div class="tms-code-terminal__bar">

          <?php if ( 'mac' === $chrome ) : ?>
            <span class="tms-code-terminal__dots" aria-hidden="true">
              <span class="tms-code-terminal__dot tms-code-terminal__dot--close"></span>
              <span class="tms-code-terminal__dot tms-code-terminal__dot--minimize"></span>
              <span class="tms-code-terminal__dot tms-code-terminal__dot--zoom"></span>
            </span>
          <?php endif; ?>

          <?php if ( '' !== $window_title ) : ?>
            <span <?php $this->print_render_attribute_string( 'window_title' ); ?>><?php echo esc_html( $window_title ); ?></span>
          <?php endif; ?>

          <?php if ( $show_copy ) : ?>
            <button
              type="button"
              class="tms-code-terminal__copy"
              aria-label="<?php echo esc_attr__( 'Copy commands', 'themeasy-lite' ); ?>"
              data-copied-label="<?php echo esc_attr__( 'Copied', 'themeasy-lite' ); ?>"
            >
              <svg
                class="tms-code-terminal__copy-icon tms-code-terminal__copy-icon--copy"
                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"
              >
                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
              </svg>
              <svg
                class="tms-code-terminal__copy-icon tms-code-terminal__copy-icon--check"
                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"
              >
                <path d="M20 6 9 17l-5-5"></path>
              </svg>
            </button>
            <span class="tms-code-terminal__copy-status" role="status" aria-live="polite"></span>
          <?php endif; ?>

        </div><!-- /.tms-code-terminal__bar -->
      <?php endif; ?>

      <div class="tms-code-terminal__body">
        <ol class="tms-code-terminal__lines" aria-hidden="true">
          <?php foreach ( $lines as $line ) : ?>
            <?php
            $text_key = $this->get_repeater_setting_key( 'line_text', 'lines', $line['index'] );
            $this->add_render_attribute( $text_key, 'class', 'tms-code-terminal__text' );
            $this->add_inline_editing_attributes( $text_key, 'none' );

            $line_atts = [
              'data-delay' => $line['delay'] > 0 ? (string) $line['delay'] : '',
            ];
            ?>
            <li class="tms-code-terminal__line tms-code-terminal__line--<?php echo esc_attr( $line['type'] ); ?>"<?php themeasy_print_html_attributes( $line_atts ); ?>>
              <?php if ( 'command' === $line['type'] && '' !== $line['prompt'] ) : ?>
                <span class="tms-code-terminal__prompt" aria-hidden="true"><?php echo esc_html( $line['prompt'] ); ?></span>
              <?php endif; ?>
              <span <?php $this->print_render_attribute_string( $text_key ); ?>><?php echo esc_html( $line['text'] ); ?></span>
            </li>
          <?php endforeach; ?>
        </ol>
      </div><!-- /.tms-code-terminal__body -->

      <div class="tms-code-terminal__sr">
        <?php foreach ( $lines as $line ) : ?>
          <p><?php echo esc_html( 'command' === $line['type'] && '' !== $line['prompt'] ? $line['prompt'] . ' ' . $line['text'] : $line['text'] ); ?></p>
        <?php endforeach; ?>
      </div><!-- /.tms-code-terminal__sr -->

    </div><!-- /.tms-code-terminal -->
    <?php
  }

  /**
   * Render the live preview template used by the Elementor editor.
   *
   * The preview is hard-coded --static: the full transcript renders and the
   * typing engine never runs, so it can never fight inline editing.
   *
   * @return void
   */
  protected function content_template() {
    ?>
    <#
      // ------------------------------------------------------------------------
      // Settings.
      // ------------------------------------------------------------------------
      var allowedTypes = [ 'command', 'output', 'success', 'error', 'comment' ];
      var globalPrompt = String( settings.prompt_symbol || '' ).trim();
      var lines = [];

      if ( settings.lines && Array.isArray( settings.lines ) ) {
        _.each( settings.lines, function( item, index ) {
          var text = item && item.line_text ? String( item.line_text ) : '';

          if ( text.trim() === '' ) {
            return;
          }

          var type = item.line_type && allowedTypes.indexOf( item.line_type ) !== -1 ? item.line_type : 'command';
          var prompt = '';

          if ( type === 'command' ) {
            var ownPrompt = String( item.line_prompt || '' ).trim();
            prompt = ownPrompt !== '' ? ownPrompt : globalPrompt;
          }

          var delay = item.line_delay ? parseFloat( item.line_delay.size ) : NaN;

          lines.push({
            index: index,
            type: type,
            text: text,
            prompt: prompt,
            delay: isNaN( delay ) ? 0 : Math.max( 0, delay ),
          });
        });
      }

      if ( ! lines.length ) {
        return;
      }

      var chrome = settings.chrome_style || 'mac';

      if ( [ 'mac', 'minimal', 'none' ].indexOf( chrome ) === -1 ) {
        chrome = 'mac';
      }

      var windowTitle = chrome !== 'none' ? String( settings.window_title || '' ) : '';
      var showCopy = chrome !== 'none' && settings.show_copy === 'yes';

      // parseFloat + isNaN mirrors render()'s is_numeric fallbacks.
      var typingSpeed = settings.typing_speed ? parseFloat( settings.typing_speed.size ) : NaN;
      var linePause = settings.line_pause ? parseFloat( settings.line_pause.size ) : NaN;
      var startDelay = settings.start_delay ? parseFloat( settings.start_delay.size ) : NaN;

      var startOnView = settings.start_on_view === 'yes';
      var loop = settings.loop === 'yes';
      var showCursor = settings.show_cursor === 'yes';
      var naturalRhythm = settings.natural_rhythm === 'yes';

      // ------------------------------------------------------------------------
      // Wrapper classes + attributes.
      // ------------------------------------------------------------------------
      var wrapperClasses = [
        'tms-code-terminal',
        'tms-code-terminal--' + chrome,
        'tms-code-terminal--static',
      ];

      var wrapperClassesStr = wrapperClasses.filter( Boolean ).join( ' ' );

      var wrapperAtts = {
        'data-typing-speed': isNaN( typingSpeed ) ? '45' : String( typingSpeed ),
        'data-line-pause': isNaN( linePause ) ? '0.5' : String( linePause ),
        'data-start-delay': isNaN( startDelay ) ? '0.3' : String( startDelay ),
        'data-start-on-view': startOnView ? 'true' : 'false',
        'data-loop': loop ? 'true' : 'false',
        'data-show-cursor': showCursor ? 'true' : 'false',
        'data-natural-rhythm': naturalRhythm ? 'true' : 'false',
      };

      var wrapperAttsOutput = Themeasy.htmlAttributes( wrapperAtts );

      // ------------------------------------------------------------------------
      // Render attributes.
      // ------------------------------------------------------------------------
      view.addRenderAttribute( 'window_title', 'class', 'tms-code-terminal__title' );
      view.addInlineEditingAttributes( 'window_title', 'none' );
    #>

    <div class="{{ wrapperClassesStr }}" {{{ wrapperAttsOutput }}}>

      <# if ( chrome !== 'none' ) { #>
        <div class="tms-code-terminal__bar">

          <# if ( chrome === 'mac' ) { #>
            <span class="tms-code-terminal__dots" aria-hidden="true">
              <span class="tms-code-terminal__dot tms-code-terminal__dot--close"></span>
              <span class="tms-code-terminal__dot tms-code-terminal__dot--minimize"></span>
              <span class="tms-code-terminal__dot tms-code-terminal__dot--zoom"></span>
            </span>
          <# } #>

          <# if ( windowTitle !== '' ) { #>
            <span {{{ view.getRenderAttributeString( 'window_title' ) }}}>{{ windowTitle }}</span>
          <# } #>

          <# if ( showCopy ) { #>
            <button
              type="button"
              class="tms-code-terminal__copy"
              aria-label="<?php echo esc_attr__( 'Copy commands', 'themeasy-lite' ); ?>"
              data-copied-label="<?php echo esc_attr__( 'Copied', 'themeasy-lite' ); ?>"
            >
              <svg
                class="tms-code-terminal__copy-icon tms-code-terminal__copy-icon--copy"
                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"
              >
                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
              </svg>
              <svg
                class="tms-code-terminal__copy-icon tms-code-terminal__copy-icon--check"
                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"
              >
                <path d="M20 6 9 17l-5-5"></path>
              </svg>
            </button>
            <span class="tms-code-terminal__copy-status" role="status" aria-live="polite"></span>
          <# } #>

        </div><!-- /.tms-code-terminal__bar -->
      <# } #>

      <div class="tms-code-terminal__body">
        <ol class="tms-code-terminal__lines" aria-hidden="true">
          <# _.each( lines, function( line ) {
            var textKey = view.getRepeaterSettingKey( 'line_text', 'lines', line.index );
            view.addRenderAttribute( textKey, 'class', 'tms-code-terminal__text' );
            view.addInlineEditingAttributes( textKey, 'none' );

            var lineAtts = line.delay > 0 ? { 'data-delay': String( line.delay ) } : {};
          #>
            <li class="tms-code-terminal__line tms-code-terminal__line--{{ line.type }}" {{{ Themeasy.htmlAttributes( lineAtts ) }}}>
              <# if ( line.type === 'command' && line.prompt !== '' ) { #>
                <span class="tms-code-terminal__prompt" aria-hidden="true">{{ line.prompt }}</span>
              <# } #>
              <span {{{ view.getRenderAttributeString( textKey ) }}}>{{ line.text }}</span>
            </li>
          <# }); #>
        </ol>
      </div><!-- /.tms-code-terminal__body -->

      <div class="tms-code-terminal__sr">
        <# _.each( lines, function( line ) { #>
          <p>{{ line.type === 'command' && line.prompt !== '' ? line.prompt + ' ' + line.text : line.text }}</p>
        <# }); #>
      </div><!-- /.tms-code-terminal__sr -->

    </div><!-- /.tms-code-terminal -->

    <?php
  }
}
