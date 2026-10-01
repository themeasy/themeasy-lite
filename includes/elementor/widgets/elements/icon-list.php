<?php
/**
 * Themeasy Elementor Widget: Icon List
 *
 * Displays a list of items with icons, text, optional badges, counters, and
 * links. Supports vertical/horizontal layout, start/end icon placement,
 * per-item links, and full styling for the item box, title, text, badge, icon,
 * and counter.
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
 * Icon List widget — renders a styled list with icons, text, counters, and links.
 */
class IconList extends Widget_Base {
  public function get_name() {
    return 'themeasy-icon-list';
  }

  public function get_title() {
    return esc_html__( 'Icon List', 'themeasy-lite' );
  }

  public function get_icon() {
    return 'eicon-bullet-list';
  }

  public function get_custom_help_url() {
    return 'https://themeasy.co/help-center';
  }

  public function get_categories() {
    return ['themeasy_elements_category', 'themeasy_content_category'];
  }

  public function get_keywords() {
    return ['themeasy', 'icon', 'list', 'bullet', 'counter', 'items', 'features'];
  }

  public function get_style_depends() {
    return ['themeasy-bundle'];
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
    // Content section: Icon List
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'icon_list_items_section',
        [
          'label' => esc_html__( 'Icon List', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $this->add_control( 'title',
        [
          'label' => esc_html__( 'Title', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXT,
          'default' => '',
          'placeholder' => esc_html__( 'Leave blank to hide', 'themeasy-lite' ),
        ]
      );

      $repeater = new Repeater();

      $repeater->add_control( 'icon',
        [
          'label' => esc_html__( 'Icon', 'themeasy-lite' ),
          'type' => Controls_Manager::ICONS,
        ]
      );

      $repeater->add_control( 'text',
        [
          'label' => esc_html__( 'Text', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXTAREA,
          'placeholder' => esc_html__( 'Type your text here', 'themeasy-lite' ),
          'rows' => 2,
        ]
      );

      // A short tag rendered beside the text ("New", "Hot", "-20%"). Replaces
      // hand-written `<span class="tms-badge">` markup inside the text field.
      $repeater->add_control( 'badge_text',
        [
          'label' => esc_html__( 'Badge', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'placeholder' => esc_html__( 'Leave blank to hide', 'themeasy-lite' ),
          'ai' => ['active' => false],
        ]
      );

      // Per-item overrides write the badge tokens on the item itself, so they
      // win over the Style tab values (declared on the list) by inheritance.
      $repeater->add_control( 'item_badge_background_color',
        [
          'label' => esc_html__( 'Badge Background', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-icon-list {{CURRENT_ITEM}}' => '--tms-icon-list-badge-bg: {{VALUE}};',
          ],
          'condition' => ['badge_text!' => ''],
        ]
      );

      $repeater->add_control( 'item_badge_text_color',
        [
          'label' => esc_html__( 'Badge Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-icon-list {{CURRENT_ITEM}}' => '--tms-icon-list-badge-color: {{VALUE}};',
          ],
          'condition' => ['badge_text!' => ''],
        ]
      );

      $repeater->add_control( 'counter',
        [
          'label' => esc_html__( 'Counter', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'placeholder' => esc_html__( 'Leave blank to hide', 'themeasy-lite' ),
          'ai' => ['active' => false],
        ]
      );

      $repeater->add_control( 'link',
        [
          'label' => esc_html__( 'URL', 'themeasy-lite' ),
          'type' => Controls_Manager::URL,
          'placeholder' => esc_html__( 'https://your-link.com', 'themeasy-lite' ),
          'options' => ['url', 'is_external', 'nofollow'],
          'default' => [
            'url' => '',
            'is_external' => false,
            'nofollow' => true,
          ],
        ]
      );

      $this->add_control( 'icon_list',
        [
          'label' => esc_html__( 'Items', 'themeasy-lite' ),
          'type' => Controls_Manager::REPEATER,
          'fields' => $repeater->get_controls(),
          'default' => [
            [
              'icon' => ['value' => 'ty-feather-check', 'library' => 'ty-feather'],
              'text' => esc_html__( 'Item #1', 'themeasy-lite' ),
              'link' => ['url' => '', 'is_external' => false, 'nofollow' => true],
            ],
            [
              'icon' => ['value' => 'ty-feather-check', 'library' => 'ty-feather'],
              'text' => esc_html__( 'Item #2', 'themeasy-lite' ),
              'link' => ['url' => '', 'is_external' => false, 'nofollow' => true],
            ],
            [
              'icon' => ['value' => 'ty-feather-check', 'library' => 'ty-feather'],
              'text' => esc_html__( 'Item #3', 'themeasy-lite' ),
              'link' => ['url' => '', 'is_external' => false, 'nofollow' => true],
            ],
          ],
          'title_field' => '{{{ text }}}',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Layout
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'icon_list_layout_section',
        [
          'label' => esc_html__( 'Layout', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      // Not responsive: the direction picks a server-side class on the list
      // (`--horizontal`), and render() reads only the desktop value — per-device
      // keys would register in the panel and then do nothing.
      $this->add_control( 'direction',
        [
          'label' => esc_html__( 'Direction', 'themeasy-lite' ),
          'type' => Controls_Manager::CHOOSE,
          'options' => [
            'vertical' => [
              'title' => esc_html__( 'Vertical', 'themeasy-lite' ),
              'icon' => 'eicon-editor-list-ul',
            ],
            'horizontal' => [
              'title' => esc_html__( 'Horizontal', 'themeasy-lite' ),
              'icon' => 'eicon-ellipsis-h',
            ],
          ],
          'default' => 'vertical',
          'toggle' => false,
        ]
      );

      $this->add_control( 'icon_position',
        [
          'label' => esc_html__( 'Icon Position', 'themeasy-lite' ),
          'type' => Controls_Manager::CHOOSE,
          'options' => [
            'start' => [
              'title' => esc_html__( 'Start', 'themeasy-lite' ),
              'icon' => 'eicon-h-align-left',
            ],
            'end' => [
              'title' => esc_html__( 'End', 'themeasy-lite' ),
              'icon' => 'eicon-h-align-right',
            ],
          ],
          'default' => 'start',
          'toggle' => false,
        ]
      );

      $this->add_responsive_control( 'items_gap',
        [
          'label' => esc_html__( 'Spacing Between Items', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem', 'em'],
          'range' => [
            'px' => ['min' => 0, 'max' => 100, 'step' => 1],
            'rem' => ['min' => 0, 'max' => 10, 'step' => 0.1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-icon-list' => 'gap: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control( 'item_gap',
        [
          'label' => esc_html__( 'Gap Between Icon and Text', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem', 'em'],
          'range' => [
            'px' => ['min' => 0, 'max' => 50, 'step' => 1],
            'rem' => ['min' => 0, 'max' => 5, 'step' => 0.1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-icon-list .tms-icon-list__item' => '--tms-icon-list-item-gap: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      // Distributes the icon and the text inside each row. Only vertical lists
      // stretch their items to the full width, so a horizontal row has no free
      // space for this to act on.
      $this->add_responsive_control( 'item_justify_content',
        [
          'label' => esc_html__( 'Icon & Text Alignment', 'themeasy-lite' ),
          'description' => esc_html__( 'Space Between pushes the icon and the text to opposite edges of the row.', 'themeasy-lite' ),
          'type' => Controls_Manager::CHOOSE,
          'options' => [
            'flex-start' => [
              'title' => esc_html__( 'Start', 'themeasy-lite' ),
              'icon' => 'eicon-flex eicon-justify-start-h',
            ],
            'center' => [
              'title' => esc_html__( 'Center', 'themeasy-lite' ),
              'icon' => 'eicon-flex eicon-justify-center-h',
            ],
            'flex-end' => [
              'title' => esc_html__( 'End', 'themeasy-lite' ),
              'icon' => 'eicon-flex eicon-justify-end-h',
            ],
            'space-between' => [
              'title' => esc_html__( 'Space Between', 'themeasy-lite' ),
              'icon' => 'eicon-flex eicon-justify-space-between-h',
            ],
          ],
          'default' => '',
          'selectors' => [
            '{{WRAPPER}} .tms-icon-list .tms-icon-list__item' => 'justify-content: {{VALUE}};',
          ],
          'condition' => [
            'direction' => 'vertical',
          ],
        ]
      );

      $this->add_responsive_control( 'justify_content',
        [
          'label' => esc_html__( 'Justify Content', 'themeasy-lite' ),
          'type' => Controls_Manager::CHOOSE,
          'options' => [
            'flex-start' => [
              'title' => esc_html__( 'Start', 'themeasy-lite' ),
              'icon' => 'eicon-flex eicon-justify-start-h',
            ],
            'center' => [
              'title' => esc_html__( 'Center', 'themeasy-lite' ),
              'icon' => 'eicon-flex eicon-justify-center-h',
            ],
            'flex-end' => [
              'title' => esc_html__( 'End', 'themeasy-lite' ),
              'icon' => 'eicon-flex eicon-justify-end-h',
            ],
            'space-between' => [
              'title' => esc_html__( 'Space Between', 'themeasy-lite' ),
              'icon' => 'eicon-flex eicon-justify-space-between-h',
            ],
            'space-around' => [
              'title' => esc_html__( 'Space Around', 'themeasy-lite' ),
              'icon' => 'eicon-flex eicon-justify-space-around-h',
            ],
            'space-evenly' => [
              'title' => esc_html__( 'Space Evenly', 'themeasy-lite' ),
              'icon' => 'eicon-flex eicon-justify-space-evenly-h',
            ],
          ],
          'default' => '',
          'selectors' => [
            '{{WRAPPER}} .tms-icon-list--horizontal' => 'justify-content: {{VALUE}};',
          ],
          'condition' => [
            'direction' => 'horizontal',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Items
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'icon_list_items_styles_section',
        [
          'label' => esc_html__( 'Items', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->start_controls_tabs( 'item_background_tabs' );

        $this->start_controls_tab( 'item_background_normal_tab',
          [
            'label' => esc_html__( 'Normal', 'themeasy-lite' ),
          ]
        );

          $this->add_control( 'item_background_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-icon-list .tms-icon-list__item' => 'background-color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

        $this->start_controls_tab( 'item_background_hover_tab',
          [
            'label' => esc_html__( 'Hover', 'themeasy-lite' ),
          ]
        );

          $this->add_control( 'item_hover_background_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-icon-list .tms-icon-list__item:hover' => 'background-color: {{VALUE}};',
              ],
            ]
          );

          $this->add_control( 'item_hover_border_color',
            [
              'label' => esc_html__( 'Border Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-icon-list .tms-icon-list__item:hover' => 'border-color: {{VALUE}};',
              ],
            ]
          );

          $this->add_responsive_control( 'item_hover_lift',
            [
              'label' => esc_html__( 'Lift on Hover', 'themeasy-lite' ),
              'description' => esc_html__( 'Vertical offset on hover. Use negative values to lift the item upwards.', 'themeasy-lite' ),
              'type' => Controls_Manager::SLIDER,
              'size_units' => ['px', 'rem'],
              'range' => [
                'px' => ['min' => -30, 'max' => 30, 'step' => 1],
                'rem' => ['min' => -3, 'max' => 3, 'step' => 0.1],
              ],
              'selectors' => [
                '{{WRAPPER}} .tms-icon-list .tms-icon-list__item:hover' => 'transform: translateY({{SIZE}}{{UNIT}});',
              ],
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

      $this->add_responsive_control( 'item_padding',
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem'],
          'separator' => 'before',
          'selectors' => [
            '{{WRAPPER}} .tms-icon-list .tms-icon-list__item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Border::get_type(),
        [
          'name' => 'item_border',
          'selector' => '{{WRAPPER}} .tms-icon-list .tms-icon-list__item',
        ]
      );

      $this->add_responsive_control( 'item_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', '%'],
          'selectors' => [
            '{{WRAPPER}} .tms-icon-list .tms-icon-list__item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->start_controls_tabs( 'item_box_shadow_tabs', ['separator' => 'before'] );

        $this->start_controls_tab( 'item_box_shadow_normal_tab',
          [
            'label' => esc_html__( 'Normal', 'themeasy-lite' ),
          ]
        );

          $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
              'name' => 'item_box_shadow',
              'label' => esc_html__( 'Box Shadow', 'themeasy-lite' ),
              'selector' => '{{WRAPPER}} .tms-icon-list .tms-icon-list__item',
            ]
          );

        $this->end_controls_tab();

        $this->start_controls_tab( 'item_box_shadow_hover_tab',
          [
            'label' => esc_html__( 'Hover', 'themeasy-lite' ),
          ]
        );

          $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
              'name' => 'item_hover_box_shadow',
              'label' => esc_html__( 'Box Shadow', 'themeasy-lite' ),
              'selector' => '{{WRAPPER}} .tms-icon-list .tms-icon-list__item:hover',
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Title
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'icon_list_title_styles_section',
        [
          'label' => esc_html__( 'Title', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => ['title!' => ''],
        ]
      );

      $this->add_control( 'title_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-icon-list__title' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'title_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-icon-list__title',
        ]
      );

      $this->add_responsive_control( 'title_spacing',
        [
          'label' => esc_html__( 'Spacing', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem', 'em'],
          'range' => [
            'px' => ['min' => 0, 'max' => 100, 'step' => 1],
            'rem' => ['min' => 0, 'max' => 10, 'step' => 0.1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-icon-list__title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Text
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'icon_list_text_styles_section',
        [
          'label' => esc_html__( 'Text', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->start_controls_tabs( 'text_color_tabs' );

        $this->start_controls_tab( 'text_color_normal_tab',
          [
            'label' => esc_html__( 'Normal', 'themeasy-lite' ),
          ]
        );

          $this->add_control( 'text_color',
            [
              'label' => esc_html__( 'Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-icon-list,
                 {{WRAPPER}} .tms-icon-list .tms-icon-list__text' => 'color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

        $this->start_controls_tab( 'text_color_hover_tab',
          [
            'label' => esc_html__( 'Hover', 'themeasy-lite' ),
          ]
        );

          $this->add_control( 'text_hover_color',
            [
              'label' => esc_html__( 'Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-icon-list .tms-icon-list__item:hover,
                 {{WRAPPER}} .tms-icon-list .tms-icon-list__item:hover .tms-icon-list__text' => 'color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'text_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-icon-list',
          'separator' => 'before',
        ]
      );

      $this->add_control( 'text_white_space',
        [
          'label' => esc_html__( 'White Space', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'normal',
          'options' => [
            'normal' => esc_html__( 'Normal', 'themeasy-lite' ),
            'nowrap' => esc_html__( 'Nowrap', 'themeasy-lite' ),
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-icon-list .tms-icon-list__text' => 'white-space: {{VALUE}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Badge
    // ------------------------------------------------------------------------
    // Colors route through `--tms-icon-list-badge-*` tokens declared on the
    // list, so a per-item color from the repeater (declared on the item) still
    // wins by inheritance.
    $this->start_controls_section(
      'icon_list_badge_styles_section',
        [
          'label' => esc_html__( 'Badge', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->start_controls_tabs( 'badge_color_tabs' );

        $this->start_controls_tab( 'badge_color_normal_tab',
          [
            'label' => esc_html__( 'Normal', 'themeasy-lite' ),
          ]
        );

          $this->add_control( 'badge_background_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-icon-list' => '--tms-icon-list-badge-bg: {{VALUE}};',
              ],
            ]
          );

          $this->add_control( 'badge_text_color',
            [
              'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-icon-list' => '--tms-icon-list-badge-color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

        $this->start_controls_tab( 'badge_color_hover_tab',
          [
            'label' => esc_html__( 'Hover', 'themeasy-lite' ),
          ]
        );

          $this->add_control( 'badge_hover_background_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-icon-list' => '--tms-icon-list-badge-hover-bg: {{VALUE}};',
              ],
            ]
          );

          $this->add_control( 'badge_hover_text_color',
            [
              'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-icon-list' => '--tms-icon-list-badge-hover-color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'badge_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-icon-list .tms-icon-list__badge',
          'separator' => 'before',
        ]
      );

      $this->add_responsive_control( 'badge_spacing',
        [
          'label' => esc_html__( 'Spacing', 'themeasy-lite' ),
          'description' => esc_html__( 'Distance between the text and the badge.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem', 'em'],
          'range' => [
            'px' => ['min' => 0, 'max' => 50, 'step' => 1],
            'rem' => ['min' => 0, 'max' => 5, 'step' => 0.1],
            'em' => ['min' => 0, 'max' => 5, 'step' => 0.1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-icon-list .tms-icon-list__label' => 'gap: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control( 'badge_vertical_offset',
        [
          'label' => esc_html__( 'Vertical Offset', 'themeasy-lite' ),
          'description' => esc_html__( 'Use negative values to raise the badge above the text line.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'em'],
          'range' => [
            'px' => ['min' => -30, 'max' => 30, 'step' => 1],
            'em' => ['min' => -2, 'max' => 2, 'step' => 0.05],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-icon-list .tms-icon-list__badge' => 'translate: 0 {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control( 'badge_padding',
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', 'em'],
          'separator' => 'before',
          'selectors' => [
            '{{WRAPPER}} .tms-icon-list .tms-icon-list__badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Border::get_type(),
        [
          'name' => 'badge_border',
          'selector' => '{{WRAPPER}} .tms-icon-list .tms-icon-list__badge',
        ]
      );

      $this->add_responsive_control( 'badge_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', '%'],
          'selectors' => [
            '{{WRAPPER}} .tms-icon-list .tms-icon-list__badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Icon
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'icon_list_icon_styles_section',
        [
          'label' => esc_html__( 'Icon', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->start_controls_tabs( 'icon_color_tabs' );

        $this->start_controls_tab( 'icon_color_normal_tab',
          [
            'label' => esc_html__( 'Normal', 'themeasy-lite' ),
          ]
        );

          $this->add_control( 'icon_color',
            [
              'label' => esc_html__( 'Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-icon-list .tms-icon-list__icon' => 'color: {{VALUE}};',
                '{{WRAPPER}} .tms-icon-list .tms-icon-list__icon svg,
                 {{WRAPPER}} .tms-icon-list svg' => 'color: {{VALUE}};',
              ],
            ]
          );

          $this->add_control( 'icon_fill_color',
            [
              'label' => esc_html__( 'Fill Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-icon-list svg' => 'fill: {{VALUE}};',
              ],
            ]
          );

          $this->add_control( 'icon_stroke_color',
            [
              'label' => esc_html__( 'Stroke Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-icon-list svg' => 'stroke: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

        $this->start_controls_tab( 'icon_color_hover_tab',
          [
            'label' => esc_html__( 'Hover', 'themeasy-lite' ),
          ]
        );

          $this->add_control( 'icon_hover_color',
            [
              'label' => esc_html__( 'Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-icon-list .tms-icon-list__item:hover .tms-icon-list__icon,
                 {{WRAPPER}} .tms-icon-list .tms-icon-list__item:hover svg' => 'color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

      $this->add_responsive_control( 'icon_size',
        [
          'label' => esc_html__( 'Size', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem', 'em'],
          'separator' => 'before',
          'range' => [
            'px' => ['min' => 5, 'max' => 100, 'step' => 1],
            'rem' => ['min' => 0.1, 'max' => 10, 'step' => 0.1],
            'em' => ['min' => 0.1, 'max' => 10, 'step' => 0.1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-icon-list .tms-icon-list__icon' => 'font-size: {{SIZE}}{{UNIT}};',
            '{{WRAPPER}} .tms-icon-list .tms-icon-list__icon svg,
             {{WRAPPER}} .tms-icon-list svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; font-size: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control( 'icon_thickness',
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
            '{{WRAPPER}} .tms-icon-list svg,
             {{WRAPPER}} .tms-icon-list svg *' => 'stroke-width: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Counter
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'icon_list_counter_styles_section',
        [
          'label' => esc_html__( 'Counter', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_control( 'counter_color',
        [
          'label' => esc_html__( 'Counter Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-icon-list .tms-icon-list__counter' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control( 'line_color',
        [
          'label' => esc_html__( 'Line Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-icon-list .tms-icon-list__line' => 'background-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control( 'line_thickness',
        [
          'label' => esc_html__( 'Line Thickness', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => ['min' => 0.5, 'max' => 20, 'step' => 0.5],
          ],
          'default' => ['unit' => 'px', 'size' => 2],
          'selectors' => [
            '{{WRAPPER}} .tms-icon-list .tms-icon-list__line' => 'height: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'counter_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-icon-list .tms-icon-list__counter',
          'separator' => 'before',
        ]
      );

    $this->end_controls_section();
  }

  /**
   * Render widget output on the frontend.
   *
   * @return void
   */
  protected function render() {
    $settings = $this->get_settings_for_display();

    // ------------------------------------------------------------------------
    // Settings.
    // ------------------------------------------------------------------------
    $title = $settings['title'] ?? '';
    $items = $settings['icon_list'] ?? [];
    $icon_position = $settings['icon_position'] ?? 'start';
    $direction = $settings['direction'] ?? 'vertical';

    // Early return if no content.
    if ( empty( $items ) ) {
      return;
    }

    // ------------------------------------------------------------------------
    // Wrapper classes + attributes.
    // ------------------------------------------------------------------------
    $list_classes = ['tms-icon-list'];

    if ( 'horizontal' === $direction ) {
      $list_classes[] = 'tms-icon-list--horizontal';
    }

    if ( 'end' === $icon_position ) {
      $list_classes[] = 'tms-icon-list--icon-end';
    }

    $list_classes_output = implode( ' ', array_filter( $list_classes ) );

    // ------------------------------------------------------------------------
    // Render attributes.
    // ------------------------------------------------------------------------
    if ( $title ) {
      $this->add_render_attribute( 'title', 'class', 'tms-icon-list__title' );
      $this->add_inline_editing_attributes( 'title', 'none' );
    }

    // ------------------------------------------------------------------------
    // Output.
    // ------------------------------------------------------------------------
    ?>
    <div class="tms-icon-list__wrapper">

      <?php if ( $title ) : ?>
        <h3 <?php $this->print_render_attribute_string( 'title' ); ?>>
          <?php echo esc_html( $title ); ?>
        </h3>
      <?php endif; ?>

      <ul class="<?php echo esc_attr( $list_classes_output ); ?>">

        <?php foreach ( $items as $index => $item ) : ?>
          <?php
          // Text render attributes.
          $text_key = $this->get_repeater_setting_key( 'text', 'icon_list', $index );
          $this->add_render_attribute( $text_key, 'class', 'tms-icon-list__text' );
          $this->add_inline_editing_attributes( $text_key, 'basic' );

          // Item classes (include the repeater-item class for CURRENT_ITEM selectors).
          $item_classes = ['tms-icon-list__item'];

          if ( ! empty( $item['_id'] ) ) {
            $item_classes[] = 'elementor-repeater-item-' . $item['_id'];
          }

          // Link attributes.
          $link_key = 'item_link_' . $index;
          $has_link = ! empty( $item['link']['url'] );

          if ( $has_link ) {
            $this->add_link_attributes( $link_key, $item['link'] );
            themeasy_add_external_link_rel( $this, $link_key, $item['link'] );
            $this->add_render_attribute( $link_key, 'class', 'tms-cover-link' );
            $this->add_render_attribute( $link_key, 'aria-label', sanitize_text_field( $item['text'] ?? '' ) );
          }

          // Icon.
          $icon_html = themeasy_render_icon_html(
            is_array( $item['icon'] ?? null ) ? $item['icon'] : [],
            ['class' => 'tms-icon-list__icon', 'aria-hidden' => 'true']
          );

          // Badge. Only rows that carry one get the label wrapper, so saved
          // lists without a badge keep their exact markup.
          $badge_text = $item['badge_text'] ?? '';

          if ( '' !== $badge_text ) {
            $badge_key = $this->get_repeater_setting_key( 'badge_text', 'icon_list', $index );
            $this->add_render_attribute( $badge_key, 'class', ['tms-icon-list__badge', 'tms-tag'] );
            $this->add_inline_editing_attributes( $badge_key, 'none' );
          }

          // Counter.
          $counter = $item['counter'] ?? '';
          ?>

          <li class="<?php echo esc_attr( implode( ' ', $item_classes ) ); ?>">

            <?php if ( $icon_html ) : ?>
              <?php echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php endif; ?>

            <?php if ( '' !== $badge_text ) : ?>
              <span class="tms-icon-list__label">
            <?php endif; ?>

            <span <?php $this->print_render_attribute_string( $text_key ); ?>>
              <?php echo wp_kses_post( $item['text'] ?? '' ); ?>
            </span>

            <?php if ( '' !== $badge_text ) : ?>
                <span <?php $this->print_render_attribute_string( $badge_key ); ?>><?php echo esc_html( $badge_text ); ?></span>
              </span><!-- /.tms-icon-list__label -->
            <?php endif; ?>

            <?php if ( $counter ) : ?>
              <span class="tms-icon-list__line"></span>
              <span class="tms-icon-list__counter"><?php echo esc_html( $counter ); ?></span>
            <?php endif; ?>

            <?php if ( $has_link ) : ?>
              <a <?php $this->print_render_attribute_string( $link_key ); ?>></a>
            <?php endif; ?>

          </li><!-- /.tms-icon-list__item -->

        <?php endforeach; ?>

      </ul>
    </div><!-- /.tms-icon-list__wrapper -->
    <?php
  }

  /**
   * Render the editor live preview template.
   *
   * @return void
   */
  protected function content_template() {
    ?>
    <#
      // ------------------------------------------------------------------------
      // Settings.
      // ------------------------------------------------------------------------
      var safeUrl = ( window.Themeasy && window.Themeasy.safeUrl )
        ? window.Themeasy.safeUrl
        : function () { return ''; };

      // User text reaches raw {{{ }}} output: sanitize with kses parity.
      var sanitizeInline = ( window.Themeasy && window.Themeasy.sanitizeInlineHtml )
        ? window.Themeasy.sanitizeInlineHtml
        : _.escape;

      var title        = settings.title || '';
      var items        = settings.icon_list || [];
      var iconPosition = settings.icon_position || 'start';
      var direction    = settings.direction || 'vertical';

      if ( ! items.length ) {
        return;
      }

      // ------------------------------------------------------------------------
      // Wrapper classes + attributes.
      // ------------------------------------------------------------------------
      var listClasses = [ 'tms-icon-list' ];

      if ( direction === 'horizontal' ) {
        listClasses.push( 'tms-icon-list--horizontal' );
      }

      if ( iconPosition === 'end' ) {
        listClasses.push( 'tms-icon-list--icon-end' );
      }

      var listClassStr = listClasses.filter( Boolean ).join( ' ' );

      // ------------------------------------------------------------------------
      // Render attributes.
      // ------------------------------------------------------------------------
      if ( title ) {
        view.addRenderAttribute( 'title', 'class', 'tms-icon-list__title' );
        view.addInlineEditingAttributes( 'title', 'none' );
      }
    #>

    <div class="tms-icon-list__wrapper">

      <# if ( title ) { #>
        <h3 {{{ view.getRenderAttributeString( 'title' ) }}}>{{ title }}</h3>
      <# } #>

      <ul class="{{ listClassStr }}">

        <# _.each( items, function( item, index ) {

          // Text render attributes.
          var textKey = view.getRepeaterSettingKey( 'text', 'icon_list', index );
          view.addRenderAttribute( textKey, 'class', 'tms-icon-list__text' );
          view.addInlineEditingAttributes( textKey, 'basic' );

          // Item classes (include the repeater-item class for CURRENT_ITEM selectors).
          var itemClasses = [ 'tms-icon-list__item' ];

          if ( item._id ) {
            itemClasses.push( 'elementor-repeater-item-' + item._id );
          }

          // Link attributes. Scheme-guarded: a javascript:/data: Link URL must
          // never reach the editor DOM.
          var linkKey = 'item_link_' + index;
          var linkHref = safeUrl( ( item.link && item.link.url ) ? item.link.url : '' );
          var hasLink = !! linkHref;

          if ( hasLink ) {
            view.addRenderAttribute( linkKey, 'href', linkHref );
            view.addRenderAttribute( linkKey, 'class', 'tms-cover-link' );
            view.addRenderAttribute( linkKey, 'aria-label', item.text || '' );

            if ( item.link.is_external ) {
              view.addRenderAttribute( linkKey, 'target', '_blank' );
            }

            if ( item.link.nofollow ) {
              view.addRenderAttribute( linkKey, 'rel', 'nofollow' );
            }
          }

          // Icon.
          var iconMarkup = ( window.Themeasy && window.Themeasy.renderIconMarkup )
            ? window.Themeasy.renderIconMarkup( view, item.icon, null, { 'class': 'tms-icon-list__icon', 'aria-hidden': 'true' } )
            : '';

          // Badge. Only rows that carry one get the label wrapper (render() parity).
          var badgeText = item.badge_text || '';
          var badgeKey = '';

          if ( badgeText ) {
            badgeKey = view.getRepeaterSettingKey( 'badge_text', 'icon_list', index );
            view.addRenderAttribute( badgeKey, 'class', [ 'tms-icon-list__badge', 'tms-tag' ] );
            view.addInlineEditingAttributes( badgeKey, 'none' );
          }

          // Counter.
          var counter = item.counter || '';
        #>

          <li class="{{ itemClasses.join( ' ' ) }}">

            <# if ( iconMarkup ) { #>
              {{{ iconMarkup }}}
            <# } #>

            <# if ( badgeText ) { #>
              <span class="tms-icon-list__label">
            <# } #>

            <span {{{ view.getRenderAttributeString( textKey ) }}}>{{{ sanitizeInline( item.text || '', 'rich' ) }}}</span>

            <# if ( badgeText ) { #>
                <span {{{ view.getRenderAttributeString( badgeKey ) }}}>{{ badgeText }}</span>
              </span><!-- /.tms-icon-list__label -->
            <# } #>

            <# if ( counter ) { #>
              <span class="tms-icon-list__line"></span>
              <span class="tms-icon-list__counter">{{ counter }}</span>
            <# } #>

            <# if ( hasLink ) { #>
              <a {{{ view.getRenderAttributeString( linkKey ) }}}></a>
            <# } #>

          </li><!-- /.tms-icon-list__item -->

        <# }); #>

      </ul>
    </div><!-- /.tms-icon-list__wrapper -->

    <?php
  }
}
