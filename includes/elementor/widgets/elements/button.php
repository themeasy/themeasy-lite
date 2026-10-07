<?php
/**
 * Themeasy Elementor Widget: Button
 *
 * A customizable button widget with multiple styles (solid, outline, gradient,
 * brutalist, duocolor) and icon support. The visual structure is pure CSS
 * (consumed from the shared component stylesheet).
 *
 * The link can either point at a URL or open a video in a modal, handed to the
 * shared lightbox module (GLightbox) through the `glightbox` class.
 *
 * @package Themeasy
 * @since 1.0.0
 */

namespace Themeasy\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

/**
 * Responsible for registering controls and rendering the widget.
 */
class Button extends Widget_Base {
  public function get_name() {
    return 'themeasy-button';
  }

  public function get_title() {
    return esc_html__( 'Button', 'themeasy-lite' );
  }

  public function get_icon() {
    return 'eicon-button';
  }

  public function get_custom_help_url() {
    return 'https://themeasy.co/help-center';
  }

  public function get_categories() {
    return ['themeasy_elements_category', 'themeasy_content_category'];
  }

  public function get_keywords() {
    return ['themeasy', 'button', 'cta', 'call to action', 'link', 'click', 'icon', 'video', 'lightbox'];
  }

  // The lightbox module only serves the "Video Lightbox" link type, but both
  // handles are declared unconditionally: Elementor collects widget assets from
  // the registered widget *type* (Page_Assets), and the Element Cache can serve
  // a widget's HTML without ever running render() — a settings-aware dependency
  // would drop the player on a cached button.
  public function get_style_depends() {
    return ['themeasy-shared', 'themeasy-lightbox-module'];
  }

  public function get_script_depends() {
    return ['themeasy-lightbox-module'];
  }

  /**
   * Register widget customization controls.
   *
   * @return void
   */
  protected function register_controls() {
    // ------------------------------------------------------------------------
    // Content section: Button
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'button_content_section',
        [
          'label' => esc_html__( 'Button', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
    );

      $this->add_control(
        'button_label',
        [
          'label' => esc_html__( 'Label', 'themeasy-lite' ),
          'label_block' => false,
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'Call to Action', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'Type your text here', 'themeasy-lite' ),
          'dynamic' => [
            'active' => true,
          ],
        ]
      );

      $this->add_control(
        'button_icon',
        [
          'label' => esc_html__( 'Icon', 'themeasy-lite' ),
          'type' => Controls_Manager::ICONS,
        ]
      );

      $this->add_control(
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
        ]
      );

      $this->add_control(
        'button_link_type',
        [
          'label' => esc_html__( 'Link Type', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'url',
          'options' => [
            'url' => esc_html__( 'URL', 'themeasy-lite' ),
            'video' => esc_html__( 'Video Lightbox', 'themeasy-lite' ),
          ],
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'button_link',
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
          'condition' => [
            'button_link_type' => 'url',
          ],
        ]
      );

      $this->add_control(
        'button_video_link',
        [
          'label' => esc_html__( 'Video Link', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::URL,
          'placeholder' => esc_html__( 'https://www.youtube.com/watch?v=...', 'themeasy-lite' ),
          'description' => esc_html__(
            'YouTube, Vimeo or a self-hosted file. The link stays clickable without JavaScript.',
            'themeasy-lite'
          ),
          // The modal is the destination, so the target / nofollow toggles have
          // nothing to act on — only the URL itself is meaningful here.
          'options' => false,
          'dynamic' => [
            'active' => true,
          ],
          'condition' => [
            'button_link_type' => 'video',
          ],
        ]
      );

      $this->add_control(
        'button_video_label',
        [
          'label' => esc_html__( 'Accessible Label', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'placeholder' => esc_html__( 'Play video', 'themeasy-lite' ),
          'description' => esc_html__(
            'Announced by screen readers when the button opens the video modal.',
            'themeasy-lite'
          ),
          'dynamic' => [
            'active' => true,
          ],
          'condition' => [
            'button_link_type' => 'video',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Button
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'button_styles_section',
        [
          'label' => esc_html__( 'Button', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      // Variant choosers — gate every property group below.
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

      // ---- Colors ----
      $this->add_control(
        'button_colors_heading',
        [
          'label' => esc_html__( 'Colors', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'button_gradient_outline_primary_color',
        [
          'label' => esc_html__( 'Primary Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-button.tms-button--gradient-outline' => '--tms-primary-color: {{VALUE}}',
          ],
          'condition' => [
            'button_style' => 'gradient-outline',
          ],
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
          'condition' => [
            'button_style' => 'gradient-outline',
          ],
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
            'condition' => [
              'button_style!' => 'gradient-outline',
            ],
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
              'condition' => [
                'button_style!' => 'gradient-outline',
              ],
            ]
          );

          $this->add_control(
            'button_icon_color',
            [
              'label' => esc_html__( 'Icon Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-button .tms-button__icon,
                 {{WRAPPER}} .tms-button svg' => 'color: {{VALUE}}',
              ],
              'condition' => [
                'button_style!' => 'gradient-outline',
              ],
            ]
          );

          $this->add_control(
            'button_icon_fill_color',
            [
              'label' => esc_html__( 'Icon Fill Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-button svg' => 'fill: {{VALUE}};',
              ],
              'conditions' => [
                'terms' => [
                  [
                    'name' => 'button_icon[library]',
                    'operator' => 'in',
                    'value' => themeasy_svg_icon_condition_libraries(),
                  ],
                ],
              ],
            ]
          );

          $this->add_control(
            'button_icon_stroke_color',
            [
              'label' => esc_html__( 'Icon Stroke Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-button svg' => 'stroke: {{VALUE}};',
              ],
              'conditions' => [
                'terms' => [
                  [
                    'name' => 'button_icon[library]',
                    'operator' => 'in',
                    'value' => themeasy_svg_icon_condition_libraries(),
                  ],
                ],
              ],
            ]
          );

          $this->add_group_control(
            Group_Control_Background::get_type(),
            [
              'name' => 'button_gradient_color',
              'label' => esc_html__( 'Gradient Color', 'themeasy-lite' ),
              'types' => ['gradient'],
              'selector' => '{{WRAPPER}} .tms-button.tms-button--gradient::before',
              'condition' => [
                'button_style' => 'gradient',
              ],
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
            'condition' => [
              'button_style!' => 'gradient-outline',
            ],
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
              'condition' => [
                'button_style!' => 'gradient-outline',
              ],
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
              'condition' => [
                'button_style!' => 'gradient-outline',
              ],
            ]
          );

          $this->add_group_control(
            Group_Control_Background::get_type(),
            [
              'name' => 'button_hover_gradient_color',
              'label' => esc_html__( 'Gradient Color', 'themeasy-lite' ),
              'types' => ['gradient'],
              'selector' => '{{WRAPPER}} .tms-button.tms-button--gradient::after',
              'condition' => [
                'button_style' => 'gradient',
              ],
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
          'separator' => 'before',
          'selector' => '{{WRAPPER}} .tms-button .tms-button__text',
        ]
      );

      // ---- Icon ----
      $this->add_control(
        'button_icon_heading',
        [
          'label' => esc_html__( 'Icon', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
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
          'conditions' => [
            'terms' => [
              [
                'name' => 'button_icon[library]',
                'operator' => 'in',
                'value' => themeasy_svg_icon_condition_libraries(),
              ],
            ],
          ],
        ]
      );

      // ---- Dimensions ----
      $this->add_control(
        'button_dimensions_heading',
        [
          'label' => esc_html__( 'Dimensions', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

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
          // The group is shrink-to-fit, so the width lives on it and the button
          // fills it: a percentage set on the button resolved against the
          // button's own width and did nothing. The custom property only carries
          // the placeholder: a rule without one is printed with the control empty.
          // `min-width: 0` lifts the shared 130px floor (components.min.css) in
          // the same declaration, or a width below it shrinks the group and
          // leaves the button overflowing it (same reason as the Form button).
          'selectors' => [
            '{{WRAPPER}} .tms-button-group' => 'width: {{SIZE}}{{UNIT}};',
            '{{WRAPPER}} .tms-button' => 'width: 100%; min-width: 0; --tms-button-width: {{SIZE}}{{UNIT}};',
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

      $this->add_control(
        'button_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            '{{WRAPPER}} .tms-button.tms-button--brutalist::before' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            '{{WRAPPER}} .tms-button .tms-button__duocolor-icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
          'condition' => [
            'button_style!' => ['simple', 'gradient-outline'],
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Advanced section: Motion.
    // ------------------------------------------------------------------------
    themeasy_register_motion_upsell_section( $this );
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
    $button_label = $settings['button_label'] ?? '';
    $button_icon = is_array( $settings['button_icon'] ?? null ) ? $settings['button_icon'] : [];
    $button_style = $settings['button_style'] ?? 'solid-fill';
    $button_icon_align = $settings['button_icon_alignment'] ?? 'right';
    $is_duocolor = in_array( $button_style, ['duocolor', 'duocolor-outline'], true );

    // Link type. A video needs a resolved URL before it earns the lightbox
    // wiring — otherwise the button would open an empty modal. Escape first and
    // gate on the RESULT: esc_url() empties a disallowed scheme, and gating on
    // the raw value would leave a `.glightbox` trigger with no href behind
    // (content_template() gates on its safeUrl() output for the same reason).
    $link_type = ( $settings['button_link_type'] ?? 'url' ) === 'video' ? 'video' : 'url';
    $video_url = 'video' === $link_type
      ? esc_url( trim( (string) ( $settings['button_video_link']['url'] ?? '' ) ) )
      : '';
    $is_video = '' !== $video_url;

    // ------------------------------------------------------------------------
    // Wrapper classes + attributes.
    // ------------------------------------------------------------------------
    $wrapper_classes = ['tms-button-group'];
    $wrapper_atts = [];
    $button_classes = ['tms-button'];

    // Icon alignment.
    if ( 'left' === $button_icon_align ) {
      $button_classes[] = 'tms-button--icon-left';
    }

    // Style modifier.
    if ( $button_style ) {
      $button_classes[] = 'tms-button--' . $button_style;
    }

    // Video lightbox. The shared lightbox module auto-binds GLightbox to this
    // class on the frontend (it deliberately no-ops in the editor preview).
    if ( $is_video ) {
      $button_classes[] = 'glightbox';
    }

    $wrapper_classes_output = implode( ' ', array_filter( $wrapper_classes ) );
    $wrapper_atts_output = themeasy_html_attributes( $wrapper_atts );
    $button_classes_output = implode( ' ', array_filter( $button_classes ) );

    // ------------------------------------------------------------------------
    // Render attributes.
    // ------------------------------------------------------------------------

    // Link.
    if ( $is_video ) {
      // A real href keeps the video reachable with no JS, and severs
      // window.opener for the modifier-click that opens it in a new tab.
      $aria_label = trim( (string) ( $settings['button_video_label'] ?? '' ) );

      $this->add_render_attribute( 'button_link', [
        // Already esc_url()'d above — that escape is what gated $is_video.
        'href' => $video_url,
        'rel' => 'noopener noreferrer',
        // Spares GLightbox's extension sniffing, which fails on extensionless
        // CDN / streaming URLs.
        'data-type' => 'video',
        // Scope the modal to this button: GLightbox groups every ungrouped
        // `.glightbox` on the page into one gallery, which would otherwise wire
        // prev/next arrows from a video CTA to unrelated page images.
        'data-gallery' => 'tms-button-' . $this->get_id(),
        'aria-haspopup' => 'dialog',
        'aria-label' => '' !== $aria_label ? $aria_label : __( 'Play video', 'themeasy-lite' ),
      ] );
    } elseif ( 'url' === $link_type && !empty( $settings['button_link']['url'] ) ) {
      $this->add_link_attributes( 'button_link', $settings['button_link'] );
      themeasy_add_external_link_rel( $this, 'button_link', $settings['button_link'] );
    } else {
      $this->add_render_attribute( 'button_link', 'href', '#' );
      $this->add_render_attribute( 'button_link', 'role', 'button' );
    }

    $this->add_render_attribute( 'button_link', 'class', $button_classes_output );

    // Label inline editing.
    $this->add_render_attribute( 'button_label', 'class', 'tms-button__text' );
    $this->add_inline_editing_attributes( 'button_label', 'none' );

    // Icon.
    $icon_html = themeasy_render_icon_html(
      $button_icon,
      ['class' => 'tms-button__icon', 'aria-hidden' => 'true']
    );

    // ------------------------------------------------------------------------
    // Output.
    // ------------------------------------------------------------------------
    ?>
    <div class="<?php echo esc_attr( $wrapper_classes_output ); ?>"<?php echo $wrapper_atts_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>>
      <a <?php $this->print_render_attribute_string( 'button_link' ); ?>>

        <span <?php $this->print_render_attribute_string( 'button_label' ); ?>>
          <?php echo wp_kses( $button_label, themeasy_get_kses_allowed_tags() ); ?>
        </span>

        <?php if ( $icon_html ) : ?>
          <?php if ( $is_duocolor ) : ?>
            <span class="tms-button__duocolor-icon">
              <?php echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>
            </span>
          <?php else : ?>
            <?php echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>
          <?php endif; ?>
        <?php endif; ?>

      </a><!-- /.tms-button -->
    </div><!-- /.tms-button-group -->
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
      var buttonLabel       = sanitizeInline( settings.button_label || '' );
      var buttonStyle       = settings.button_style || 'solid-fill';
      var buttonIconAlign   = settings.button_icon_alignment || 'right';
      var isDuocolor        = [ 'duocolor', 'duocolor-outline' ].indexOf( buttonStyle ) !== -1;

      // content_template() has no esc_url(): every interpolated href goes
      // through the canonical, fail-closed scheme guard.
      var safeUrl = ( window.Themeasy && window.Themeasy.safeUrl )
        ? window.Themeasy.safeUrl
        : function () { return ''; };

      // Link type (mirrors render()).
      var linkType    = settings.button_link_type === 'video' ? 'video' : 'url';
      var videoLink   = settings.button_video_link || {};
      // trim() mirrors render(): a whitespace-only Video Link is "no video".
      var videoUrl    = linkType === 'video' ? safeUrl( String( videoLink.url || '' ).trim() ) : '';
      var isVideo     = !! videoUrl;

      // Icon.
      var iconMarkup = ( window.Themeasy && window.Themeasy.renderIconMarkup )
        ? window.Themeasy.renderIconMarkup(
            view,
            settings.button_icon,
            null,
            { 'class': 'tms-button__icon', 'aria-hidden': 'true' }
          )
        : '';

      // ------------------------------------------------------------------------
      // Wrapper classes + attributes.
      // ------------------------------------------------------------------------
      var wrapperClasses = [ 'tms-button-group' ];
      var wrapperAtts    = {};
      var buttonClasses  = [ 'tms-button' ];

      // Icon alignment.
      if ( buttonIconAlign === 'left' ) {
        buttonClasses.push( 'tms-button--icon-left' );
      }

      // Style modifier.
      if ( buttonStyle ) {
        buttonClasses.push( 'tms-button--' + buttonStyle );
      }

      // Video lightbox. The class is cosmetic in the editor — the lightbox
      // module no-ops in the preview so the widget stays selectable.
      if ( isVideo ) {
        buttonClasses.push( 'glightbox' );
      }

      var wrapperClassStr = wrapperClasses.filter( Boolean ).join( ' ' );
      var buttonClassStr  = buttonClasses.filter( Boolean ).join( ' ' );

      // ------------------------------------------------------------------------
      // Render attributes.
      // ------------------------------------------------------------------------

      // Link.
      var buttonLink    = settings.button_link || {};
      var buttonLinkUrl = ( linkType === 'url' && buttonLink.url ) ? safeUrl( buttonLink.url ) : '';
      var linkAtts      = { 'href': buttonLinkUrl || '#', 'class': buttonClassStr };

      if ( isVideo ) {
        var videoLabel = settings.button_video_label || '';

        linkAtts['href']          = videoUrl;
        linkAtts['rel']           = 'noopener noreferrer';
        linkAtts['data-type']     = 'video';
        linkAtts['data-gallery']  = 'tms-button-' + ( view.getID ? view.getID() : '' );
        linkAtts['aria-haspopup'] = 'dialog';
        linkAtts['aria-label']    = videoLabel || '<?php echo esc_js( __( 'Play video', 'themeasy-lite' ) ); ?>';
      } else if ( ! buttonLinkUrl ) {
        linkAtts['role'] = 'button';
      }

      // Label inline editing.
      view.addRenderAttribute( 'button_label', 'class', 'tms-button__text' );
      view.addInlineEditingAttributes( 'button_label', 'none' );
    #>

    <div class="{{ wrapperClassStr }}" {{{ Themeasy.htmlAttributes( wrapperAtts ) }}}>
      <a {{{ Themeasy.htmlAttributes( linkAtts ) }}}>

        <span {{{ view.getRenderAttributeString( 'button_label' ) }}}>
          {{{ buttonLabel }}}
        </span>

        <# if ( iconMarkup ) { #>
          <# if ( isDuocolor ) { #>
            <span class="tms-button__duocolor-icon">
              {{{ iconMarkup }}}
            </span>
          <# } else { #>
            {{{ iconMarkup }}}
          <# } #>
        <# } #>

      </a><!-- /.tms-button -->
    </div><!-- /.tms-button-group -->

    <?php
  }
}
