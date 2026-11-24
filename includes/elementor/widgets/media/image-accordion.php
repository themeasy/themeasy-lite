<?php
/**
 * Themeasy Elementor Widget: Image Accordion
 *
 * Horizontal or vertical accordion of image panels that expand on click or
 * hover. Each panel reveals overlay content (icon, title, description, CTA
 * button) when active. Three skins (classic, bordered, stacked-labels) cover
 * Apple-style product showcases, services overviews and portfolio highlights.
 * Full keyboard navigation, ARIA semantics and reduced-motion fallback.
 *
 * @package Themeasy
 * @since 1.0.0
 */

namespace Themeasy\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Control_Media;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Utils;
use Elementor\Widget_Base;

/**
 * Image Accordion widget class.
 */
class ImageAccordion extends Widget_Base {
  public function get_name() {
    return 'themeasy-image-accordion';
  }

  public function get_title() {
    return esc_html__( 'Image Accordion', 'themeasy-lite' );
  }

  public function get_icon() {
    return 'eicon-image-rollover';
  }

  public function get_custom_help_url() {
    return 'https://hub.themeasy.co/docs';
  }

  public function get_categories() {
    return ['themeasy_media_category', 'themeasy_content_category'];
  }

  public function get_keywords() {
    return ['themeasy', 'image', 'accordion', 'horizontal', 'panels', 'showcase', 'gallery'];
  }

  public function get_style_depends() {
    return ['themeasy-shared', 'themeasy-image-accordion'];
  }

  public function get_script_depends() {
    return ['themeasy-image-accordion'];
  }

  /**
   * Register widget customization controls.
   *
   * @return void
   */
  protected function register_controls() {
    // ------------------------------------------------------------------------
    // Content section: Panels
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'image_accordion_items_section',
        [
          'label' => esc_html__( 'Panels', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
    );

      $repeater = new Repeater();

      $repeater->add_control(
        'image',
        [
          'label' => esc_html__( 'Image', 'themeasy-lite' ),
          'type' => Controls_Manager::MEDIA,
          'default' => [
            'url' => Utils::get_placeholder_image_src(),
          ],
        ]
      );

      $repeater->add_control(
        'title',
        [
          'label' => esc_html__( 'Title', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'Panel Title', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
        ]
      );

      $repeater->add_control(
        'description',
        [
          'label' => esc_html__( 'Description', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXTAREA,
          'default' => esc_html__( 'A short description shown when the panel is active.', 'themeasy-lite' ),
          'rows' => 3,
          'dynamic' => ['active' => true],
        ]
      );

      $repeater->add_control(
        'icon',
        [
          'label' => esc_html__( 'Icon', 'themeasy-lite' ),
          'type' => Controls_Manager::ICONS,
          'default' => [
            'value' => 'ty-feather-layers',
            'library' => 'ty-feather',
          ],
        ]
      );

      $repeater->add_control(
        'button_label',
        [
          'label' => esc_html__( 'Button Label', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'default' => '',
          'placeholder' => esc_html__( 'Leave blank to hide the button', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
        ]
      );

      $repeater->add_control(
        'link',
        [
          'label' => esc_html__( 'Link', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::URL,
          'placeholder' => esc_html__( 'https://your-link.com', 'themeasy-lite' ),
          'options' => ['url', 'is_external', 'nofollow'],
          'default' => [
            'url' => '',
            'is_external' => false,
            'nofollow' => false,
          ],
        ]
      );

      $repeater->add_control(
        'overlay_color',
        [
          'label' => esc_html__( 'Overlay Color (Override)', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'description' => esc_html__( 'Leaves default when empty. Otherwise overrides the overlay color for this item.', 'themeasy-lite' ),
          'selectors' => [
            '{{WRAPPER}} .tms-image-accordion__item{{CURRENT_ITEM}} .tms-image-accordion__overlay'
              => 'background-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'items',
        [
          'label' => esc_html__( 'Panels', 'themeasy-lite' ),
          'type' => Controls_Manager::REPEATER,
          'fields' => $repeater->get_controls(),
          'default' => [
            [
              'title' => esc_html__( 'Panel #1', 'themeasy-lite' ),
              'description' => esc_html__( 'Discover the first highlight of your showcase.', 'themeasy-lite' ),
            ],
            [
              'title' => esc_html__( 'Panel #2', 'themeasy-lite' ),
              'description' => esc_html__( 'A second story to tell visitors what makes you unique.', 'themeasy-lite' ),
            ],
            [
              'title' => esc_html__( 'Panel #3', 'themeasy-lite' ),
              'description' => esc_html__( 'Close the showcase with a compelling final panel.', 'themeasy-lite' ),
            ],
            [
              'title' => esc_html__( 'Panel #4', 'themeasy-lite' ),
              'description' => esc_html__( 'Optional extra panel — remove or duplicate as needed.', 'themeasy-lite' ),
            ],
          ],
          'title_field' => '{{{ title }}}',
        ]
      );

      $this->add_control(
        'title_tag',
        [
          'label' => esc_html__( 'Title Tag', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'h3',
          'separator' => 'before',
          'options' => [
            'h1' => 'H1',
            'h2' => 'H2',
            'h3' => 'H3',
            'h4' => 'H4',
            'h5' => 'H5',
            'h6' => 'H6',
            'div' => 'DIV',
          ],
        ]
      );

      $this->add_control(
        'button_icon',
        [
          'label' => esc_html__( 'Button Icon', 'themeasy-lite' ),
          'type' => Controls_Manager::ICONS,
          'default' => [
            'value' => 'ty-feather-arrow-right',
            'library' => 'ty-feather',
          ],
        ]
      );

      $this->add_control(
        'button_icon_alignment',
        [
          'label' => esc_html__( 'Button Icon Alignment', 'themeasy-lite' ),
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
        'button_style',
        [
          'label' => esc_html__( 'Button Style', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'solid-fill',
          'options' => [
            'simple' => esc_html__( 'Simple (Text Only)', 'themeasy-lite' ),
            'solid-fill' => esc_html__( 'Solid Fill', 'themeasy-lite' ),
            'outline' => esc_html__( 'Outline', 'themeasy-lite' ),
            'muted-fill' => esc_html__( 'Muted Fill', 'themeasy-lite' ),
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Layout
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'image_accordion_layout_section',
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
            'classic' => esc_html__( 'Classic (Overlay Bar)', 'themeasy-lite' ),
            'bordered' => esc_html__( 'Bordered (Minimal)', 'themeasy-lite' ),
            'stacked-labels' => esc_html__( 'Stacked Labels', 'themeasy-lite' ),
          ],
          'description' => esc_html__( 'Choose the visual style of each panel.', 'themeasy-lite' ),
        ]
      );

      $this->add_responsive_control(
        'orientation',
        [
          'label' => esc_html__( 'Orientation', 'themeasy-lite' ),
          'type' => Controls_Manager::CHOOSE,
          'options' => [
            'horizontal' => [
              'title' => esc_html__( 'Horizontal', 'themeasy-lite' ),
              'icon' => 'eicon-ellipsis-v',
            ],
            'vertical' => [
              'title' => esc_html__( 'Vertical', 'themeasy-lite' ),
              'icon' => 'eicon-ellipsis-h',
            ],
          ],
          'default' => 'horizontal',
          'tablet_default' => 'horizontal',
          'mobile_default' => 'vertical',
          'toggle' => false,
          // Elementor swaps %s for the device suffix ('', '-tablet', '-mobile') and
          // appends the value: 'orientation%s-' yields orientation-mobile-vertical,
          // the class the CSS reads. '-%s' yielded orientation--mobilevertical, so
          // a phone kept rotated labels in a stacked accordion (backlog #294).
          'prefix_class' => 'tms-image-accordion-orientation%s-',
        ]
      );

      $this->add_responsive_control(
        'height',
        [
          'label' => esc_html__( 'Height', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'vh', 'rem'],
          'range' => [
            'px' => ['min' => 160, 'max' => 1000, 'step' => 1],
            'vh' => ['min' => 20, 'max' => 100, 'step' => 1],
            'rem' => ['min' => 10, 'max' => 60, 'step' => 0.5],
          ],
          'default' => ['unit' => 'px', 'size' => 480],
          'selectors' => [
            '{{WRAPPER}} .tms-image-accordion' => '--tms-image-accordion-height: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'active_flex',
        [
          'label' => esc_html__( 'Active Panel Ratio', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'range' => [
            'px' => ['min' => 1, 'max' => 10, 'step' => 0.1],
          ],
          'default' => ['size' => 4],
          'description' => esc_html__( 'How many times wider (or taller) the active panel grows compared to collapsed ones.', 'themeasy-lite' ),
          'selectors' => [
            '{{WRAPPER}} .tms-image-accordion' => '--tms-image-accordion-active-flex: {{SIZE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'gap',
        [
          'label' => esc_html__( 'Gap Between Panels', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem'],
          'range' => [
            'px' => ['min' => 0, 'max' => 40, 'step' => 1],
            'rem' => ['min' => 0, 'max' => 4, 'step' => 0.1],
          ],
          'default' => ['unit' => 'px', 'size' => 6],
          'selectors' => [
            '{{WRAPPER}} .tms-image-accordion' => '--tms-image-accordion-gap: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Settings
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'image_accordion_settings_section',
        [
          'label' => esc_html__( 'Settings', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
    );

      $this->add_control(
        'trigger',
        [
          'label' => esc_html__( 'Trigger', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'click',
          'options' => [
            'click' => esc_html__( 'Click / Tap', 'themeasy-lite' ),
            'hover' => esc_html__( 'Hover', 'themeasy-lite' ),
          ],
          'description' => esc_html__( 'On touch devices, hover trigger falls back to tap automatically.', 'themeasy-lite' ),
        ]
      );

      $this->add_control(
        'active_index',
        [
          'label' => esc_html__( 'Default Active Panel', 'themeasy-lite' ),
          'type' => Controls_Manager::NUMBER,
          'min' => 0,
          'step' => 1,
          'default' => 1,
          'description' => esc_html__( 'Index of the panel that starts expanded. Use 0 to start with no active panel.', 'themeasy-lite' ),
        ]
      );

      $this->add_control(
        'transition_heading',
        [
          'label' => esc_html__( 'Transition', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'transition_duration',
        [
          'label' => esc_html__( 'Duration', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['s'],
          'range' => ['s' => ['min' => 0.1, 'max' => 2, 'step' => 0.05]],
          'default' => ['unit' => 's', 'size' => 0.6],
          'selectors' => [
            '{{WRAPPER}} .tms-image-accordion' => '--tms-image-accordion-duration: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'transition_timing',
        [
          'label' => esc_html__( 'Easing', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'cubic-bezier(0.25, 0.46, 0.45, 0.94)',
          'options' => [
            'ease' => esc_html__( 'Ease', 'themeasy-lite' ),
            'ease-in' => esc_html__( 'Ease In', 'themeasy-lite' ),
            'ease-out' => esc_html__( 'Ease Out', 'themeasy-lite' ),
            'ease-in-out' => esc_html__( 'Ease In Out', 'themeasy-lite' ),
            'linear' => esc_html__( 'Linear', 'themeasy-lite' ),
            'cubic-bezier(0.25, 0.46, 0.45, 0.94)' => esc_html__( 'Smooth', 'themeasy-lite' ),
            'cubic-bezier(0.68, -0.55, 0.27, 1.55)' => esc_html__( 'Bouncy', 'themeasy-lite' ),
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-image-accordion' => '--tms-image-accordion-timing: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'image_heading',
        [
          'label' => esc_html__( 'Image', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_group_control( // image_resolution
        Group_Control_Image_Size::get_type(),
        [
          'name' => 'image_resolution',
          'exclude' => ['custom'],
          'include' => [],
          'default' => 'large',
        ]
      );

      $this->add_control(
        'image_loading_priority',
        [
          'label' => esc_html__( 'Loading Behavior', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'lazy',
          'options' => [
            'lazy' => esc_html__( 'Load when needed (Lazy Loading)', 'themeasy-lite' ),
            'high' => esc_html__( 'Prioritize loading (Above the Fold)', 'themeasy-lite' ),
          ],
          'description' => esc_html__( 'Use high priority only for accordions above the fold.', 'themeasy-lite' ),
        ]
      );

      $this->add_control(
        'image_object_fit',
        [
          'label' => esc_html__( 'Object Fit', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'cover',
          'options' => [
            'cover' => esc_html__( 'Cover', 'themeasy-lite' ),
            'contain' => esc_html__( 'Contain', 'themeasy-lite' ),
            'fill' => esc_html__( 'Fill', 'themeasy-lite' ),
            'scale-down' => esc_html__( 'Scale Down', 'themeasy-lite' ),
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-image-accordion__img' => 'object-fit: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'image_object_position',
        [
          'label' => esc_html__( 'Object Position', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'center center',
          'options' => [
            'center center' => esc_html__( 'Center', 'themeasy-lite' ),
            'top center' => esc_html__( 'Top', 'themeasy-lite' ),
            'bottom center' => esc_html__( 'Bottom', 'themeasy-lite' ),
            'center left' => esc_html__( 'Left', 'themeasy-lite' ),
            'center right' => esc_html__( 'Right', 'themeasy-lite' ),
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-image-accordion__img' => 'object-position: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control( // image_css_filters
        Group_Control_Css_Filter::get_type(),
        [
          'name' => 'image_css_filters',
          'selector' => '{{WRAPPER}} .tms-image-accordion__img',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Panel
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'image_accordion_panel_styles_section',
        [
          'label' => esc_html__( 'Panel', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      $this->add_control(
        'panel_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', '%'],
          'default' => [
            'top' => 12, 'right' => 12, 'bottom' => 12, 'left' => 12, 'unit' => 'px', 'isLinked' => true,
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-image-accordion__item'
              => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control( // panel_border
        Group_Control_Border::get_type(),
        [
          'name' => 'panel_border',
          'selector' => '{{WRAPPER}} .tms-image-accordion__item',
        ]
      );

      $this->add_group_control( // panel_box_shadow
        Group_Control_Box_Shadow::get_type(),
        [
          'name' => 'panel_box_shadow',
          'label' => esc_html__( 'Box Shadow', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-image-accordion__item',
        ]
      );

      $this->add_control(
        'panel_active_heading',
        [
          'label' => esc_html__( 'Active Panel', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_group_control( // panel_active_box_shadow
        Group_Control_Box_Shadow::get_type(),
        [
          'name' => 'panel_active_box_shadow',
          'label' => esc_html__( 'Box Shadow', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-image-accordion__item.tms-image-accordion__item--active',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Overlay
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'image_accordion_overlay_styles_section',
        [
          'label' => esc_html__( 'Overlay', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      $this->start_controls_tabs( 'overlay_state_tabs' );

        $this->start_controls_tab(
          'overlay_normal_tab',
          [
            'label' => esc_html__( 'Collapsed', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'overlay_background_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-image-accordion__overlay' => 'background-color: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            'overlay_gradient_color',
            [
              'label' => esc_html__( 'Gradient Stop (Bottom)', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-image-accordion__overlay'
                  => '--tms-image-accordion-overlay-gradient-stop: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

        $this->start_controls_tab(
          'overlay_active_tab',
          [
            'label' => esc_html__( 'Active', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'overlay_active_background_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-image-accordion__item--active .tms-image-accordion__overlay'
                  => 'background-color: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            'overlay_active_gradient_color',
            [
              'label' => esc_html__( 'Gradient Stop (Bottom)', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-image-accordion__item--active .tms-image-accordion__overlay'
                  => '--tms-image-accordion-overlay-gradient-stop: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

      $this->add_responsive_control(
        'overlay_padding',
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', 'em'],
          'default' => [
            'top' => 24, 'right' => 24, 'bottom' => 24, 'left' => 24, 'unit' => 'px', 'isLinked' => true,
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-image-accordion__content'
              => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'content_gap',
        [
          'label' => esc_html__( 'Items Gap', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem', 'em'],
          'range' => [
            'px' => ['min' => 0, 'max' => 60, 'step' => 1],
            'rem' => ['min' => 0, 'max' => 4, 'step' => 0.1],
          ],
          'default' => ['unit' => 'px', 'size' => 10],
          // The custom property carries the gap into the reveal, which owns
          // the gaps above and between the description and the button.
          'selectors' => [
            '{{WRAPPER}} .tms-image-accordion__content'
              => 'gap: {{SIZE}}{{UNIT}}; --tms-image-accordion-content-gap: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'content_text_align',
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
          'selectors' => [
            '{{WRAPPER}} .tms-image-accordion__content' => 'align-items: {{VALUE}}; text-align: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'content_vertical_align',
        [
          'label' => esc_html__( 'Vertical Alignment', 'themeasy-lite' ),
          'type' => Controls_Manager::CHOOSE,
          'options' => [
            'flex-start' => [
              'title' => esc_html__( 'Top', 'themeasy-lite' ),
              'icon' => 'eicon-v-align-top',
            ],
            'center' => [
              'title' => esc_html__( 'Middle', 'themeasy-lite' ),
              'icon' => 'eicon-v-align-middle',
            ],
            'flex-end' => [
              'title' => esc_html__( 'Bottom', 'themeasy-lite' ),
              'icon' => 'eicon-v-align-bottom',
            ],
          ],
          'default' => 'flex-end',
          'selectors' => [
            '{{WRAPPER}} .tms-image-accordion__content' => 'justify-content: {{VALUE}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Title
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'image_accordion_title_styles_section',
        [
          'label' => esc_html__( 'Title', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      $this->start_controls_tabs( 'title_state_tabs' );

        $this->start_controls_tab(
          'title_normal_tab',
          [
            'label' => esc_html__( 'Collapsed', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'title_color',
            [
              'label' => esc_html__( 'Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-image-accordion__title' => 'color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

        $this->start_controls_tab(
          'title_active_tab',
          [
            'label' => esc_html__( 'Active', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'title_active_color',
            [
              'label' => esc_html__( 'Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-image-accordion__item--active .tms-image-accordion__title'
                  => 'color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

      $this->add_group_control( // title_typography
        Group_Control_Typography::get_type(),
        [
          'name' => 'title_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-image-accordion__title',
          'separator' => 'before',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Description
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'image_accordion_description_styles_section',
        [
          'label' => esc_html__( 'Description', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      $this->add_control(
        'description_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-image-accordion__description' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control( // description_typography
        Group_Control_Typography::get_type(),
        [
          'name' => 'description_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-image-accordion__description',
          'separator' => 'before',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Icon
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'image_accordion_icon_styles_section',
        [
          'label' => esc_html__( 'Icon', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      $this->add_control(
        'icon_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-image-accordion__icon' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'icon_fill_color',
        [
          'label' => esc_html__( 'Fill Color (Override)', 'themeasy-lite' ),
          'description' => esc_html__( 'Applies to SVG icons only.', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-image-accordion__icon svg' => 'fill: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'icon_stroke_color',
        [
          'label' => esc_html__( 'Stroke Color (Override)', 'themeasy-lite' ),
          'description' => esc_html__( 'Applies to SVG icons only.', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-image-accordion__icon svg' => 'stroke: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'icon_size',
        [
          'label' => esc_html__( 'Size', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem', 'em'],
          'range' => [
            'px' => ['min' => 8, 'max' => 200, 'step' => 1],
            'rem' => ['min' => 0.5, 'max' => 10, 'step' => 0.1],
          ],
          'default' => ['unit' => 'px', 'size' => 32],
          'separator' => 'before',
          'selectors' => [
            '{{WRAPPER}} .tms-image-accordion__icon' => 'font-size: {{SIZE}}{{UNIT}};',
            '{{WRAPPER}} .tms-image-accordion__icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'icon_thickness',
        [
          'label' => esc_html__( 'Thickness', 'themeasy-lite' ),
          'description' => esc_html__( 'Applies to SVG icons only.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => ['min' => 0.5, 'max' => 10, 'step' => 0.5],
          ],
          'default' => ['unit' => 'px', 'size' => 2],
          'selectors' => [
            '{{WRAPPER}} .tms-image-accordion__icon svg,
             {{WRAPPER}} .tms-image-accordion__icon svg *' => 'stroke-width: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Button
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'image_accordion_button_styles_section',
        [
          'label' => esc_html__( 'Button', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      $this->start_controls_tabs( 'button_state_tabs' );

        $this->start_controls_tab(
          'button_normal_tab',
          [
            'label' => esc_html__( 'Normal', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'button_background_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-image-accordion__button' => 'background-color: {{VALUE}};',
              ],
              'condition' => [
                'button_style!' => ['simple', 'outline'],
              ],
            ]
          );

          $this->add_control(
            'button_border_color',
            [
              'label' => esc_html__( 'Border Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-image-accordion__button' => 'border-color: {{VALUE}};',
              ],
              'condition' => [
                'button_style' => 'outline',
              ],
            ]
          );

          $this->add_control(
            'button_text_color',
            [
              'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-image-accordion__button' => 'color: {{VALUE}};',
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
            'button_hover_background_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-image-accordion__button:hover,
                 {{WRAPPER}} .tms-image-accordion__button:focus-visible' => 'background-color: {{VALUE}};',
              ],
              'condition' => [
                'button_style!' => 'simple',
              ],
            ]
          );

          $this->add_control(
            'button_hover_border_color',
            [
              'label' => esc_html__( 'Border Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-image-accordion__button:hover,
                 {{WRAPPER}} .tms-image-accordion__button:focus-visible' => 'border-color: {{VALUE}};',
              ],
              'condition' => [
                'button_style' => 'outline',
              ],
            ]
          );

          $this->add_control(
            'button_hover_text_color',
            [
              'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-image-accordion__button:hover,
                 {{WRAPPER}} .tms-image-accordion__button:focus-visible' => 'color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

      $this->add_group_control( // button_typography
        Group_Control_Typography::get_type(),
        [
          'name' => 'button_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-image-accordion__button-text',
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
            '{{WRAPPER}} .tms-image-accordion__button'
              => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
          'condition' => [
            'button_style!' => 'simple',
          ],
        ]
      );

      $this->add_control(
        'button_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', '%'],
          'selectors' => [
            '{{WRAPPER}} .tms-image-accordion__button'
              => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
          'condition' => [
            'button_style!' => 'simple',
          ],
        ]
      );

      $this->add_control(
        'button_icon_heading',
        [
          'label' => esc_html__( 'Button Icon', 'themeasy-lite' ),
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
            '{{WRAPPER}} .tms-image-accordion__button-icon' => 'font-size: {{SIZE}}{{UNIT}};',
            '{{WRAPPER}} .tms-image-accordion__button-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'button_icon_thickness',
        [
          'label' => esc_html__( 'Icon Thickness', 'themeasy-lite' ),
          'description' => esc_html__( 'Applies to SVG icons only.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => ['min' => 0.5, 'max' => 10, 'step' => 0.5],
          ],
          'default' => ['unit' => 'px', 'size' => 2],
          'selectors' => [
            '{{WRAPPER}} .tms-image-accordion__button-icon svg,
             {{WRAPPER}} .tms-image-accordion__button-icon svg *' => 'stroke-width: {{SIZE}}{{UNIT}};',
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
    $items = $settings['items'] ?? [];

    if ( empty( $items ) ) {
      return;
    }

    // Bail if no panel carries an image — the widget has nothing to render.
    $has_any_image = false;

    foreach ( $items as $item ) {
      if ( !empty( $item['image']['url'] ?? '' ) ) {
        $has_any_image = true;
        break;
      }
    }

    if ( !$has_any_image ) {
      return;
    }

    // Clamp SELECT values to their known option sets before they flow into
    // class names / data-attributes (defense-in-depth against tampered meta).
    $skin = $settings['skin'] ?? 'classic';
    $skin = in_array( $skin, ['classic', 'bordered', 'stacked-labels'], true ) ? $skin : 'classic';

    $trigger = $settings['trigger'] ?? 'click';
    $trigger = in_array( $trigger, ['click', 'hover'], true ) ? $trigger : 'click';

    $button_style = $settings['button_style'] ?? 'solid-fill';
    $button_style = in_array( $button_style, ['simple', 'solid-fill', 'outline', 'muted-fill'], true )
      ? $button_style
      : 'solid-fill';

    $active_index = isset( $settings['active_index'] ) ? max( 0, intval( $settings['active_index'] ) ) : 1;
    $title_tag = themeasy_sanitize_heading_tag(
      $settings['title_tag'] ?? 'h3',
      'h3',
      ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div']
    );
    $button_icon = $settings['button_icon'] ?? [];
    $button_icon_alignment = $settings['button_icon_alignment'] ?? 'right';
    $resolution = $settings['image_resolution_size'] ?? 'large';
    $loading_priority = $settings['image_loading_priority'] ?? 'lazy';

    // ------------------------------------------------------------------------
    // Wrapper classes + attributes.
    // ------------------------------------------------------------------------
    $wrapper_classes = [
      'tms-image-accordion',
      'tms-image-accordion--skin-' . $skin,
      'tms-image-accordion--trigger-' . $trigger,
    ];

    $wrapper_atts = [
      'data-skin' => $skin,
      'data-trigger' => $trigger,
      'data-active-index' => (string) $active_index,
      'role' => 'group',
      'aria-label' => esc_attr__( 'Image accordion', 'themeasy-lite' ),
    ];

    $wrapper_classes_output = implode( ' ', array_filter( $wrapper_classes ) );

    // ------------------------------------------------------------------------
    // Render attributes.
    // ------------------------------------------------------------------------
    // Shared button icon markup (same icon for every panel).
    $button_icon_html = themeasy_render_icon_html( is_array( $button_icon ) ? $button_icon : [] );

    if ( $button_icon_html ) {
      $button_icon_html = '<span class="tms-image-accordion__button-icon" aria-hidden="true">' . $button_icon_html . '</span>';
    }

    // Keep the original $items keys so get_repeater_setting_key() can point to
    // the correct repeater row, but use a separate rendered-index counter for
    // data-index / is_active / panel_id so PHP and JS share the same rendered
    // index space when items without an image are skipped.
    $rendered_index = 0;

    // ------------------------------------------------------------------------
    // Output.
    // ------------------------------------------------------------------------
    ?>
    <div class="<?php echo esc_attr( $wrapper_classes_output ); ?>"<?php themeasy_print_html_attributes( $wrapper_atts ); ?>>

      <?php foreach ( $items as $index => $item ) : ?>
        <?php
        $image = $item['image'] ?? [];
        $image_url = $image['url'] ?? '';

        if ( empty( $image_url ) ) {
          continue;
        }

        $image_id = absint( $image['id'] ?? 0 );
        $image_alt = Control_Media::get_image_alt( $image );
        $title = $item['title'] ?? '';
        $description = $item['description'] ?? '';
        $icon = $item['icon'] ?? [];
        $button_label = $item['button_label'] ?? '';
        $link = $item['link'] ?? [];
        $has_link = !empty( $link['url'] );
        $has_button = ( $button_label !== '' );
        $rendered_index++;
        $is_active = ( $active_index === $rendered_index );

        $item_key = !empty( $item['_id'] ) ? $item['_id'] : (string) $index;
        $panel_id = 'tms-ia-panel-' . $this->get_id() . '-' . $item_key;

        $item_classes = [
          'tms-image-accordion__item',
          'elementor-repeater-item-' . $item_key,
        ];

        if ( $is_active ) {
          $item_classes[] = 'tms-image-accordion__item--active';
        }

        // -- Text render attributes (use original $index so the repeater setting
        // key points to the correct row in Elementor's data model). --
        $title_key = $this->get_repeater_setting_key( 'title', 'items', $index );
        $this->add_render_attribute( $title_key, 'class', 'tms-image-accordion__title' );
        $this->add_inline_editing_attributes( $title_key, 'basic' );

        $desc_key = $this->get_repeater_setting_key( 'description', 'items', $index );
        $this->add_render_attribute( $desc_key, 'class', 'tms-image-accordion__description' );
        $this->add_inline_editing_attributes( $desc_key, 'basic' );

        $label_key = $this->get_repeater_setting_key( 'button_label', 'items', $index );
        $this->add_render_attribute( $label_key, 'class', 'tms-image-accordion__button-text' );
        $this->add_inline_editing_attributes( $label_key, 'none' );

        $icon_html = '';

        if ( !empty( $icon['value'] ) ) {
          $icon_html = themeasy_render_icon_html( is_array( $icon ) ? $icon : [] );
        }

        // -- Link attributes --
        $link_key = 'item_link_' . $index;

        if ( $has_link ) {
          $this->add_link_attributes( $link_key, $link );
          themeasy_add_external_link_rel( $this, $link_key, $link );
        }
        ?>

        <div class="<?php echo esc_attr( implode( ' ', array_filter( $item_classes ) ) ); ?>"
          data-index="<?php echo esc_attr( (string) $rendered_index ); ?>">

          <button type="button"
            class="tms-image-accordion__trigger"
            aria-expanded="<?php echo esc_attr( $is_active ? 'true' : 'false' ); ?>"
            aria-controls="<?php echo esc_attr( $panel_id ); ?>"
            aria-label="<?php echo esc_attr( $title ? $title : sprintf( /* translators: %d: panel index */ __( 'Panel %d', 'themeasy-lite' ), $rendered_index ) ); ?>">

            <span class="tms-image-accordion__media">
              <?php
              themeasy_render_attachment_image(
                $image_id,
                $resolution,
                $image_url,
                $image_alt,
                'tms-image-accordion__img',
                [],
                $loading_priority
              );
              ?>
            </span>

            <span class="tms-image-accordion__overlay" aria-hidden="true"></span>

          </button>

          <div id="<?php echo esc_attr( $panel_id ); ?>"
            class="tms-image-accordion__content"
            role="region"
            aria-label="<?php echo esc_attr( $title ? $title : sprintf( /* translators: %d: panel index */ __( 'Panel %d content', 'themeasy-lite' ), $rendered_index ) ); ?>">

            <?php if ( $icon_html ) : ?>
              <span class="tms-image-accordion__icon-wrap">
                <span class="tms-image-accordion__icon" aria-hidden="true">
                  <?php echo wp_kses( $icon_html, themeasy_get_icon_kses_tags() ); ?>
                </span>
              </span>
            <?php endif; ?>

            <?php if ( $title ) : ?>
              <<?php echo tag_escape( $title_tag ); ?> <?php $this->print_render_attribute_string( $title_key ); ?>>
                <?php echo wp_kses_post( $title ); ?>
              </<?php echo tag_escape( $title_tag ); ?>>
            <?php endif; ?>

            <?php if ( $description || $has_button ) : ?>
              <?php // What only an open panel shows: the reveal grows from zero, so the title glides up instead of jumping. ?>
              <div class="tms-image-accordion__reveal">
                <div class="tms-image-accordion__reveal-inner">

                  <?php if ( $description ) : ?>
                    <div <?php $this->print_render_attribute_string( $desc_key ); ?>>
                      <?php echo wp_kses_post( $description ); ?>
                    </div>
                  <?php endif; ?>

                  <?php if ( $has_button ) : ?>
                    <?php
                    $button_classes = [
                      'tms-image-accordion__button',
                      'tms-image-accordion__button--' . $button_style,
                    ];

                    if ( 'left' === $button_icon_alignment ) {
                      $button_classes[] = 'tms-image-accordion__button--icon-left';
                    }

                    if ( !$has_link ) {
                      $this->add_render_attribute( $link_key, 'href', '#' );
                      $this->add_render_attribute( $link_key, 'role', 'button' );
                    }

                    $this->add_render_attribute( $link_key, 'class', implode( ' ', $button_classes ) );
                    $this->add_render_attribute( $link_key, 'tabindex', $is_active ? '0' : '-1' );
                    ?>
                    <a <?php $this->print_render_attribute_string( $link_key ); ?>>
                      <span <?php $this->print_render_attribute_string( $label_key ); ?>>
                        <?php echo esc_html( $button_label ); ?>
                      </span>
                      <?php echo wp_kses( $button_icon_html, themeasy_get_icon_kses_tags() ); ?>
                    </a>
                  <?php endif; ?>

                </div>
              </div><!-- /.tms-image-accordion__reveal -->
            <?php endif; ?>

          </div><!-- /.tms-image-accordion__content -->

        </div><!-- /.tms-image-accordion__item -->

      <?php endforeach; ?>

    </div><!-- /.tms-image-accordion -->
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
      var items = settings.items || [];

      if ( ! items.length ) {
        return;
      }

      var skin                = settings.skin || 'classic';
      var trigger             = settings.trigger || 'click';
      var activeIndex         = parseInt( settings.active_index, 10 );
      activeIndex             = isNaN( activeIndex ) ? 1 : Math.max( 0, activeIndex );
      var titleTag            = settings.title_tag || 'h3';
      var allowedTags         = [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div' ];
      if ( allowedTags.indexOf( titleTag ) === -1 ) { titleTag = 'h3'; }

      var buttonStyle         = settings.button_style || 'solid-fill';
      var buttonIconAlignment = settings.button_icon_alignment || 'right';

      // ------------------------------------------------------------------------
      // Wrapper classes + attributes.
      // ------------------------------------------------------------------------
      var wrapperClasses = [
        'tms-image-accordion',
        'tms-image-accordion--skin-' + skin,
        'tms-image-accordion--trigger-' + trigger
      ];

      var wrapperAtts = {
        'data-skin': skin,
        'data-trigger': trigger,
        'data-active-index': String( activeIndex ),
        'role': 'group'
      };

      var wrapperClassStr = wrapperClasses.filter( Boolean ).join( ' ' );

      // Scheme guard for every href/src below. The regex lives in exactly one
      // place; the fallback fails closed rather than duplicating it (a bare ^
      // anchor misses the C0 controls browsers strip before resolving a scheme).
      var safeUrl = ( window.Themeasy && window.Themeasy.safeUrl )
        ? window.Themeasy.safeUrl
        : function () { return ''; };

      // Shared button icon markup (same icon for every panel).
      var buttonIconMarkup = ( window.Themeasy && window.Themeasy.renderIconMarkup )
        ? window.Themeasy.renderIconMarkup( view, settings.button_icon, null, {} )
        : '';

      if ( buttonIconMarkup ) {
        buttonIconMarkup = '<span class="tms-image-accordion__button-icon" aria-hidden="true">' + buttonIconMarkup + '</span>';
      }

      var elementId = view.getID();
    #>

    <div class="{{ wrapperClassStr }}" {{{ Themeasy.htmlAttributes( wrapperAtts ) }}}>

      <#
      var renderedIndex = 0;

      _.each( items, function( item, index ) {
        var imageObj    = item.image || {};
        var imageUrl    = safeUrl( imageObj.url || '' );

        // No usable image (unset, or a scheme the guard blocked) — skip the item.
        if ( ! imageUrl ) { return; }

        renderedIndex++;

        var imageAlt    = imageObj.alt || '';
        var title       = item.title || '';
        var description = item.description || '';
        var buttonLabel = item.button_label || '';
        var link        = item.link || {};

        // A blocked scheme yields '' — the button then degrades to render()'s
        // no-link shape (href="#" + role="button"), never a live hostile href.
        var buttonHref  = safeUrl( link.url || '' );
        var hasLink     = !! buttonHref;
        var hasButton   = !! buttonLabel;
        var isActive    = ( activeIndex === renderedIndex );
        var itemKey     = item._id ? item._id : String( index );
        var panelId     = 'tms-ia-panel-' + elementId + '-' + itemKey;

        var itemClasses = [
          'tms-image-accordion__item',
          'elementor-repeater-item-' + itemKey
        ];

        if ( isActive ) {
          itemClasses.push( 'tms-image-accordion__item--active' );
        }

        var itemClassStr = itemClasses.filter( Boolean ).join( ' ' );

        // Text render attributes.
        var titleKey = view.getRepeaterSettingKey( 'title', 'items', index );
        view.addRenderAttribute( titleKey, 'class', 'tms-image-accordion__title' );
        view.addInlineEditingAttributes( titleKey, 'basic' );

        var descKey = view.getRepeaterSettingKey( 'description', 'items', index );
        view.addRenderAttribute( descKey, 'class', 'tms-image-accordion__description' );
        view.addInlineEditingAttributes( descKey, 'basic' );

        var labelKey = view.getRepeaterSettingKey( 'button_label', 'items', index );
        view.addRenderAttribute( labelKey, 'class', 'tms-image-accordion__button-text' );
        view.addInlineEditingAttributes( labelKey, 'none' );

        var iconMarkup = ( window.Themeasy && window.Themeasy.renderIconMarkup && item.icon )
          ? window.Themeasy.renderIconMarkup( view, item.icon, null, {} )
          : '';

        var buttonClasses = [
          'tms-image-accordion__button',
          'tms-image-accordion__button--' + buttonStyle
        ];

        if ( buttonIconAlignment === 'left' ) {
          buttonClasses.push( 'tms-image-accordion__button--icon-left' );
        }

        var buttonClassStr = buttonClasses.join( ' ' );
      #>

        <div class="{{ itemClassStr }}" data-index="{{ renderedIndex }}">

          <button type="button"
            class="tms-image-accordion__trigger"
            aria-expanded="{{ isActive ? 'true' : 'false' }}"
            aria-controls="{{ panelId }}">

            <span class="tms-image-accordion__media">
              <img src="{{ imageUrl }}" alt="{{ imageAlt }}" class="tms-image-accordion__img" />
            </span>

            <span class="tms-image-accordion__overlay" aria-hidden="true"></span>

          </button>

          <div id="{{ panelId }}" class="tms-image-accordion__content" role="region">

            <# if ( iconMarkup ) { #>
              <span class="tms-image-accordion__icon-wrap">
                <span class="tms-image-accordion__icon" aria-hidden="true">{{{ iconMarkup }}}</span>
              </span>
            <# } #>

            <# if ( title ) { #>
              <{{ titleTag }} {{{ view.getRenderAttributeString( titleKey ) }}}>{{{ sanitizeInline( title, 'rich' ) }}}</{{ titleTag }}>
            <# } #>

            <# if ( description || hasButton ) { #>
              <div class="tms-image-accordion__reveal">
                <div class="tms-image-accordion__reveal-inner">

                  <# if ( description ) { #>
                    <div {{{ view.getRenderAttributeString( descKey ) }}}>{{{ sanitizeInline( description, 'rich' ) }}}</div>
                  <# } #>

                  <# if ( hasButton ) { #>
                    <a href="{{ hasLink ? buttonHref : '#' }}"<# if ( ! hasLink ) { #> role="button"<# } #> class="{{ buttonClassStr }}" tabindex="{{ isActive ? '0' : '-1' }}">
                      <span {{{ view.getRenderAttributeString( labelKey ) }}}>{{ buttonLabel }}</span>
                      <# if ( buttonIconMarkup ) { #>{{{ buttonIconMarkup }}}<# } #>
                    </a>
                  <# } #>

                </div>
              </div><!-- /.tms-image-accordion__reveal -->
            <# } #>

          </div><!-- /.tms-image-accordion__content -->

        </div><!-- /.tms-image-accordion__item -->

      <# }); #>

    </div><!-- /.tms-image-accordion -->

    <?php
  }
}
