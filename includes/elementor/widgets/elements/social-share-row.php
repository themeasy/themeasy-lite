<?php
/**
 * Themeasy Elementor Widget: Social Share Row
 *
 * Aligned row (or vertical rail) of share icons supporting the most-used
 * networks (X / Twitter, Facebook, LinkedIn, WhatsApp, Email) plus a
 * Copy-Link action and free-form Custom entries. Ships three skins:
 *
 *   - inline  (icon-only, horizontal row; default)
 *   - labeled (icon + brand label, horizontal row)
 *   - rail    (icon-only, vertical column for blog post side rails)
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
 * Responsible for registering controls and rendering the Social Share Row widget.
 */
class SocialShareRow extends Widget_Base {
  public function get_name() {
    return 'themeasy-social-share-row';
  }

  public function get_title() {
    return esc_html__( 'Social Share Row', 'themeasy-lite' );
  }

  public function get_icon() {
    return 'eicon-share';
  }

  public function get_custom_help_url() {
    return 'https://hub.themeasy.co/docs';
  }

  public function get_categories() {
    return ['themeasy_elements_category', 'themeasy_content_category'];
  }

  public function get_keywords() {
    return ['themeasy', 'social', 'share', 'twitter', 'x', 'facebook', 'linkedin', 'whatsapp', 'copy link', 'rail'];
  }

  public function get_style_depends() {
    return ['themeasy-shared', 'themeasy-social-share-row'];
  }

  public function get_script_depends() {
    return ['themeasy-social-share-row'];
  }

  /**
   * Network catalog used at render time and inside the editor preview.
   *
   * @return array<string, array<string, string>>
   */
  public static function get_network_catalog(): array {
    // Plain (non-pre-escaped) strings: the render path always escapes them at
    // the output sink (`esc_html()` / `esc_attr()`), so pre-escaping here would
    // double-encode entities for any translated label containing apostrophes
    // or similar characters.
    return [
      'x' => [
        'label' => 'X',
        'aria' => __( 'Share on X', 'themeasy-lite' ),
        'icon_library' => 'ty-feather',
        'icon_name' => 'x-twitter',
      ],
      'facebook' => [
        'label' => 'Facebook',
        'aria' => __( 'Share on Facebook', 'themeasy-lite' ),
        'icon_library' => 'ty-feather',
        'icon_name' => 'facebook',
      ],
      'linkedin' => [
        'label' => 'LinkedIn',
        'aria' => __( 'Share on LinkedIn', 'themeasy-lite' ),
        'icon_library' => 'ty-feather',
        'icon_name' => 'linkedin',
      ],
      'whatsapp' => [
        'label' => 'WhatsApp',
        'aria' => __( 'Share on WhatsApp', 'themeasy-lite' ),
        'icon_library' => 'ty-feather',
        'icon_name' => 'whatsapp',
      ],
      'email' => [
        'label' => __( 'Email', 'themeasy-lite' ),
        'aria' => __( 'Share via email', 'themeasy-lite' ),
        'icon_library' => 'ty-feather',
        'icon_name' => 'mail',
      ],
      'copy' => [
        'label' => __( 'Copy link', 'themeasy-lite' ),
        'aria' => __( 'Copy link to this page', 'themeasy-lite' ),
        'icon_library' => 'ty-feather',
        'icon_name' => 'link-2',
      ],
    ];
  }

  /**
   * Network options used in the network SELECT control.
   *
   * @return array<string, string>
   */
  private function get_network_options(): array {
    return [
      'x' => 'X (Twitter)',
      'facebook' => 'Facebook',
      'linkedin' => 'LinkedIn',
      'whatsapp' => 'WhatsApp',
      'email' => esc_html__( 'Email', 'themeasy-lite' ),
      'copy' => esc_html__( 'Copy Link', 'themeasy-lite' ),
      'custom' => esc_html__( 'Custom', 'themeasy-lite' ),
    ];
  }

  /**
   * Register widget customization controls.
   *
   * @return void
   */
  protected function register_controls() {
    $this->register_content_layout_section();
    $this->register_content_networks_section();
    $this->register_content_share_data_section();
    $this->register_content_messages_section();
    $this->register_style_item_section();
    $this->register_style_icon_section();
    $this->register_style_label_section();
    $this->register_style_tooltip_section();
    $this->register_style_toast_section();
  }

  /**
   * Content section: Layout.
   *
   * @return void
   */
  private function register_content_layout_section(): void {
    // ------------------------------------------------------------------------
    // Content section: Layout
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'social_share_row_layout_section',
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
          'default' => 'inline',
          'options' => [
            'inline' => esc_html__( 'Inline (icon-only row)', 'themeasy-lite' ),
            'labeled' => esc_html__( 'Labeled (icon + label row)', 'themeasy-lite' ),
            'rail' => esc_html__( 'Rail (vertical column)', 'themeasy-lite' ),
          ],
          'description' => esc_html__(
            'The rail skin is ideal as a side share column on blog posts.',
            'themeasy-lite'
          ),
        ]
      );

      $this->add_control(
        'shape',
        [
          'label' => esc_html__( 'Item Shape', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'circle',
          'options' => [
            'circle' => esc_html__( 'Circle', 'themeasy-lite' ),
            'square' => esc_html__( 'Square', 'themeasy-lite' ),
            'rounded' => esc_html__( 'Rounded', 'themeasy-lite' ),
            'none' => esc_html__( 'None (no background)', 'themeasy-lite' ),
          ],
          'condition' => [
            'skin!' => 'labeled',
          ],
        ]
      );

      $this->add_responsive_control(
        'alignment',
        [
          'label' => esc_html__( 'Alignment', 'themeasy-lite' ),
          'type' => Controls_Manager::CHOOSE,
          'options' => [
            'flex-start' => [
              'title' => esc_html__( 'Start', 'themeasy-lite' ),
              'icon' => 'eicon-text-align-left',
            ],
            'center' => [
              'title' => esc_html__( 'Center', 'themeasy-lite' ),
              'icon' => 'eicon-text-align-center',
            ],
            'flex-end' => [
              'title' => esc_html__( 'End', 'themeasy-lite' ),
              'icon' => 'eicon-text-align-right',
            ],
          ],
          'default' => 'flex-start',
          'selectors' => [
            '{{WRAPPER}} .tms-social-share-row__list' => 'justify-content: {{VALUE}};',
            '{{WRAPPER}} .tms-social-share-row--skin-rail .tms-social-share-row__list' => 'align-items: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'item_gap',
        [
          'label' => esc_html__( 'Items Gap', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem'],
          'range' => [
            'px' => ['min' => 0, 'max' => 60, 'step' => 1],
            'rem' => ['min' => 0, 'max' => 4, 'step' => 0.1],
          ],
          'default' => ['unit' => 'px', 'size' => 8],
          'selectors' => [
            '{{WRAPPER}} .tms-social-share-row__list' => 'gap: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'show_tooltip',
        [
          'label' => esc_html__( 'Show Tooltip on Hover', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Show', 'themeasy-lite' ),
          'label_off' => esc_html__( 'Hide', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
          'description' => esc_html__( 'Tooltips read the per-item label (or network name).', 'themeasy-lite' ),
          'condition' => [
            'skin!' => 'labeled',
          ],
        ]
      );

      $this->add_control(
        'tooltip_position',
        [
          'label' => esc_html__( 'Tooltip Position', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'top',
          'options' => [
            'top' => esc_html__( 'Top', 'themeasy-lite' ),
            'bottom' => esc_html__( 'Bottom', 'themeasy-lite' ),
            'left' => esc_html__( 'Left', 'themeasy-lite' ),
            'right' => esc_html__( 'Right', 'themeasy-lite' ),
          ],
          'condition' => [
            'show_tooltip' => 'yes',
            'skin!' => 'labeled',
          ],
        ]
      );

    $this->end_controls_section();
  }

  /**
   * Content section: Networks (repeater).
   *
   * @return void
   */
  private function register_content_networks_section(): void {
    // ------------------------------------------------------------------------
    // Content section: Networks
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'social_share_row_networks_section',
        [
          'label' => esc_html__( 'Networks', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $repeater = new Repeater();

      $repeater->add_control(
        'network',
        [
          'label' => esc_html__( 'Network', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'x',
          'options' => $this->get_network_options(),
        ]
      );

      $repeater->add_control(
        'label',
        [
          'label' => esc_html__( 'Label', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'label_block' => true,
          'placeholder' => esc_html__( 'Leave blank to use the network name', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
          'description' => esc_html__(
            'Shown next to the icon in the Labeled skin and inside the tooltip.',
            'themeasy-lite'
          ),
        ]
      );

      $repeater->add_control(
        'custom_url',
        [
          'label' => esc_html__( 'Custom URL', 'themeasy-lite' ),
          'type' => Controls_Manager::URL,
          'placeholder' => esc_html__( 'https://your-link.com', 'themeasy-lite' ),
          'options' => ['url', 'is_external', 'nofollow'],
          'default' => [
            'url' => '',
            'is_external' => true,
            'nofollow' => true,
          ],
          'dynamic' => ['active' => true],
          'condition' => [
            'network' => 'custom',
          ],
        ]
      );

      $repeater->add_control(
        'custom_icon',
        [
          'label' => esc_html__( 'Custom Icon', 'themeasy-lite' ),
          'type' => Controls_Manager::ICONS,
          'condition' => [
            'network' => 'custom',
          ],
        ]
      );

      $repeater->add_control(
        'item_color_override',
        [
          'label' => esc_html__( 'Per-item Color (override)', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} {{CURRENT_ITEM}}' => '--tms-social-share-row-item-color: {{VALUE}};',
          ],
        ]
      );

      $repeater->add_control(
        'item_background_color_override',
        [
          'label' => esc_html__( 'Per-item Background (override)', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} {{CURRENT_ITEM}}' => '--tms-social-share-row-item-bg: {{VALUE}};',
          ],
        ]
      );

      $repeater->add_control(
        'item_border_color_override',
        [
          'label' => esc_html__( 'Per-item Border Color (override)', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} {{CURRENT_ITEM}}' => '--tms-social-share-row-item-border-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'networks',
        [
          'label' => esc_html__( 'Networks', 'themeasy-lite' ),
          'type' => Controls_Manager::REPEATER,
          'fields' => $repeater->get_controls(),
          'title_field' => '{{{ label || ( network === "custom" ? "Custom" : network.charAt(0).toUpperCase() + network.slice(1) ) }}}',
          'default' => [
            ['network' => 'x'],
            ['network' => 'facebook'],
            ['network' => 'linkedin'],
            ['network' => 'whatsapp'],
            ['network' => 'copy'],
          ],
        ]
      );

    $this->end_controls_section();
  }

  /**
   * Content section: Share Data.
   *
   * @return void
   */
  private function register_content_share_data_section(): void {
    // ------------------------------------------------------------------------
    // Content section: Share Data
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'social_share_row_data_section',
        [
          'label' => esc_html__( 'Share Data', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $this->add_control(
        'url_source',
        [
          'label' => esc_html__( 'URL to Share', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'current',
          'options' => [
            'current' => esc_html__( 'Current page URL', 'themeasy-lite' ),
            'custom' => esc_html__( 'Custom URL', 'themeasy-lite' ),
          ],
        ]
      );

      $this->add_control(
        'custom_share_url',
        [
          'label' => esc_html__( 'Custom Share URL', 'themeasy-lite' ),
          'type' => Controls_Manager::URL,
          'placeholder' => esc_html__( 'https://your-link.com', 'themeasy-lite' ),
          'options' => ['url'],
          'default' => ['url' => ''],
          'dynamic' => ['active' => true],
          'condition' => [
            'url_source' => 'custom',
          ],
        ]
      );

      $this->add_control(
        'text_source',
        [
          'label' => esc_html__( 'Text to Share', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'title',
          'options' => [
            'title' => esc_html__( 'Current page title', 'themeasy-lite' ),
            'custom' => esc_html__( 'Custom text', 'themeasy-lite' ),
            'none' => esc_html__( 'No text (URL only)', 'themeasy-lite' ),
          ],
        ]
      );

      $this->add_control(
        'custom_share_text',
        [
          'label' => esc_html__( 'Custom Share Text', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXTAREA,
          'rows' => 3,
          'dynamic' => ['active' => true],
          'condition' => [
            'text_source' => 'custom',
          ],
        ]
      );

    $this->end_controls_section();
  }

  /**
   * Content section: Messages (copy toast).
   *
   * @return void
   */
  private function register_content_messages_section(): void {
    // ------------------------------------------------------------------------
    // Content section: Messages
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'social_share_row_messages_section',
        [
          'label' => esc_html__( 'Messages', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $this->add_control(
        'copy_success_message',
        [
          'label' => esc_html__( 'Copy Success Message', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'Link copied to clipboard', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
        ]
      );

      $this->add_control(
        'copy_error_message',
        [
          'label' => esc_html__( 'Copy Error Message', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'Could not copy the link', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
        ]
      );

      $this->add_control(
        'toast_duration',
        [
          'label' => esc_html__( 'Toast Duration', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['s'],
          'range' => ['s' => ['min' => 1, 'max' => 8, 'step' => 0.5]],
          'default' => ['unit' => 's', 'size' => 2.5],
        ]
      );

    $this->end_controls_section();
  }

  /**
   * Style section: Item button.
   *
   * @return void
   */
  private function register_style_item_section(): void {
    // ------------------------------------------------------------------------
    // Style section: Item
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'social_share_row_item_styles_section',
        [
          'label' => esc_html__( 'Item', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_responsive_control(
        'item_size',
        [
          'label' => esc_html__( 'Item Size', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem'],
          'range' => [
            'px' => ['min' => 20, 'max' => 120, 'step' => 1],
            'rem' => ['min' => 1, 'max' => 8, 'step' => 0.1],
          ],
          'default' => ['unit' => 'px', 'size' => 40],
          'selectors' => [
            '{{WRAPPER}} .tms-social-share-row' => '--tms-social-share-row-item-size: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'item_padding',
        [
          'label' => esc_html__( 'Item Padding (labeled skin)', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', 'em'],
          'default' => ['top' => 10, 'right' => 14, 'bottom' => 10, 'left' => 14, 'unit' => 'px'],
          'selectors' => [
            '{{WRAPPER}} .tms-social-share-row--skin-labeled .tms-social-share-row__item'
              => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
          'condition' => [
            'skin' => 'labeled',
          ],
        ]
      );

      $this->add_group_control( // item_border
        Group_Control_Border::get_type(),
        [
          'name' => 'item_border',
          'separator' => 'before',
          // Type + width only: the color lives in the Normal/Hover tabs below so
          // the two states write the same layer instead of racing on specificity
          // (the group's own color field targets the item at 0,3,0 and would
          // outrank the stylesheet's :hover rule).
          'exclude' => ['color'],
          'selector' => '{{WRAPPER}} .tms-social-share-row__item',
        ]
      );

      $this->add_responsive_control(
        'item_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', '%'],
          'selectors' => [
            '{{WRAPPER}} .tms-social-share-row__item'
              => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
          'description' => esc_html__(
            'Overrides the Shape setting (circle / square / rounded) when set.',
            'themeasy-lite'
          ),
        ]
      );

      $this->start_controls_tabs( 'item_states_tabs' );

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
                // Written on the wrapper, not the item: a per-item override in the
                // Networks repeater declares the same token on the item itself, so
                // it keeps winning by inheritance no matter the source order.
                '{{WRAPPER}} .tms-social-share-row'
                  => '--tms-social-share-row-item-bg: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            'item_border_color',
            [
              'label' => esc_html__( 'Border Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                // Token, not the property itself — see item_background_color.
                '{{WRAPPER}} .tms-social-share-row'
                  => '--tms-social-share-row-item-border-color: {{VALUE}};',
              ],
            ]
          );

          $this->add_group_control( // item_box_shadow
            Group_Control_Box_Shadow::get_type(),
            [
              'name' => 'item_box_shadow',
              'label' => esc_html__( 'Box Shadow', 'themeasy-lite' ),
              'selector' => '{{WRAPPER}} .tms-social-share-row__item',
            ]
          );

        $this->end_controls_tab();

        $this->start_controls_tab(
          'item_hover_tab',
          [
            'label' => esc_html__( 'Hover', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'item_background_hover_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'description' => esc_html__(
                'Defaults to the theme primary color.',
                'themeasy-lite'
              ),
              'selectors' => [
                '{{WRAPPER}} .tms-social-share-row'
                  => '--tms-social-share-row-item-hover-bg: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            'item_border_hover_color',
            [
              'label' => esc_html__( 'Border Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-social-share-row'
                  => '--tms-social-share-row-item-hover-border-color: {{VALUE}};',
              ],
            ]
          );

          $this->add_group_control( // item_box_shadow_hover
            Group_Control_Box_Shadow::get_type(),
            [
              'name' => 'item_box_shadow_hover',
              'label' => esc_html__( 'Box Shadow', 'themeasy-lite' ),
              'selector' => '{{WRAPPER}} .tms-social-share-row__item:hover,
                            {{WRAPPER}} .tms-social-share-row__item:focus-visible',
            ]
          );

          $this->add_control(
            'item_lift_on_hover',
            [
              'label' => esc_html__( 'Lift on Hover', 'themeasy-lite' ),
              'type' => Controls_Manager::SLIDER,
              'size_units' => ['px'],
              'range' => ['px' => ['min' => 0, 'max' => 12, 'step' => 1]],
              'default' => ['unit' => 'px', 'size' => 2],
              'selectors' => [
                '{{WRAPPER}} .tms-social-share-row' => '--tms-social-share-row-lift: -{{SIZE}}{{UNIT}};',
              ],
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

      $this->add_control(
        'item_transition_duration',
        [
          'label' => esc_html__( 'Transition Duration', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['s'],
          'range' => ['s' => ['min' => 0.1, 'max' => 2, 'step' => 0.05]],
          'default' => ['unit' => 's', 'size' => 0.25],
          'separator' => 'before',
          'selectors' => [
            '{{WRAPPER}} .tms-social-share-row' => '--tms-social-share-row-duration: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();
  }

  /**
   * Style section: Icon.
   *
   * @return void
   */
  private function register_style_icon_section(): void {
    // ------------------------------------------------------------------------
    // Style section: Icon
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'social_share_row_icon_styles_section',
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
            // color: only — brand glyphs carry fill="currentColor" and feather outlines
            // carry stroke="currentColor" + fill="none", so the cascade styles both.
            // Explicit fill/stroke templates here break under GLOBAL values: Elementor
            // maps every property of the template to the var(), clobbering `fill: none`.
            '{{WRAPPER}} .tms-social-share-row__item .tms-social-share-row__icon' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'icon_hover_color',
        [
          'label' => esc_html__( 'Hover Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            // color: only — see icon_color above for why fill/stroke templates are unsafe.
            '{{WRAPPER}} .tms-social-share-row__item:hover .tms-social-share-row__icon,
             {{WRAPPER}} .tms-social-share-row__item:focus-visible .tms-social-share-row__icon'
              => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'icon_size',
        [
          'label' => esc_html__( 'Icon Size', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'em', 'rem'],
          'range' => [
            'px' => ['min' => 10, 'max' => 64, 'step' => 1],
            'em' => ['min' => 0.5, 'max' => 4, 'step' => 0.1],
            'rem' => ['min' => 0.5, 'max' => 4, 'step' => 0.1],
          ],
          'default' => ['unit' => 'px', 'size' => 18],
          'separator' => 'before',
          'selectors' => [
            '{{WRAPPER}} .tms-social-share-row__icon' => 'font-size: {{SIZE}}{{UNIT}};',
            '{{WRAPPER}} .tms-social-share-row__icon svg'
              => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();
  }

  /**
   * Style section: Label (labeled skin only).
   *
   * @return void
   */
  private function register_style_label_section(): void {
    // ------------------------------------------------------------------------
    // Style section: Label
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'social_share_row_label_styles_section',
        [
          'label' => esc_html__( 'Label', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'skin' => 'labeled',
          ],
        ]
      );

      $this->add_responsive_control(
        'label_gap',
        [
          'label' => esc_html__( 'Gap to Icon', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem'],
          'range' => [
            'px' => ['min' => 0, 'max' => 32, 'step' => 1],
            'rem' => ['min' => 0, 'max' => 3, 'step' => 0.1],
          ],
          'default' => ['unit' => 'px', 'size' => 8],
          'selectors' => [
            '{{WRAPPER}} .tms-social-share-row--skin-labeled .tms-social-share-row__item'
              => 'gap: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'label_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-social-share-row__label' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'label_hover_color',
        [
          'label' => esc_html__( 'Hover Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-social-share-row__item:hover .tms-social-share-row__label,
             {{WRAPPER}} .tms-social-share-row__item:focus-visible .tms-social-share-row__label'
              => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control( // label_typography
        Group_Control_Typography::get_type(),
        [
          'name' => 'label_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-social-share-row__label',
        ]
      );

    $this->end_controls_section();
  }

  /**
   * Style section: Tooltip (when tooltip is enabled).
   *
   * @return void
   */
  private function register_style_tooltip_section(): void {
    // ------------------------------------------------------------------------
    // Style section: Tooltip
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'social_share_row_tooltip_styles_section',
        [
          'label' => esc_html__( 'Tooltip', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'show_tooltip' => 'yes',
            'skin!' => 'labeled',
          ],
        ]
      );

      $this->add_control(
        'tooltip_background_color',
        [
          'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-social-share-row' => '--tms-social-share-row-tooltip-bg: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'tooltip_text_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-social-share-row' => '--tms-social-share-row-tooltip-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control( // tooltip_typography
        Group_Control_Typography::get_type(),
        [
          'name' => 'tooltip_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-social-share-row__tooltip',
        ]
      );

      $this->add_responsive_control(
        'tooltip_padding',
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', 'em'],
          'default' => ['top' => 6, 'right' => 10, 'bottom' => 6, 'left' => 10, 'unit' => 'px'],
          'selectors' => [
            '{{WRAPPER}} .tms-social-share-row__tooltip'
              => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'tooltip_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-social-share-row__tooltip'
              => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'tooltip_offset',
        [
          'label' => esc_html__( 'Offset', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => ['px' => ['min' => 0, 'max' => 40, 'step' => 1]],
          'default' => ['unit' => 'px', 'size' => 8],
          'selectors' => [
            '{{WRAPPER}} .tms-social-share-row' => '--tms-social-share-row-tooltip-offset: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();
  }

  /**
   * Style section: Copy toast.
   *
   * @return void
   */
  private function register_style_toast_section(): void {
    // ------------------------------------------------------------------------
    // Style section: Copy Toast
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'social_share_row_toast_styles_section',
        [
          'label' => esc_html__( 'Copy Toast', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_control(
        'toast_background_color',
        [
          'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-social-share-row' => '--tms-social-share-row-toast-bg: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'toast_text_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-social-share-row' => '--tms-social-share-row-toast-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control( // toast_typography
        Group_Control_Typography::get_type(),
        [
          'name' => 'toast_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-social-share-row__toast',
        ]
      );

      $this->add_responsive_control(
        'toast_padding',
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', 'em'],
          'default' => ['top' => 8, 'right' => 14, 'bottom' => 8, 'left' => 14, 'unit' => 'px'],
          'selectors' => [
            '{{WRAPPER}} .tms-social-share-row__toast'
              => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'toast_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-social-share-row__toast'
              => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
    $networks = $settings['networks'] ?? [];

    if ( empty( $networks ) ) {
      return;
    }

    $skin = $settings['skin'] ?? 'inline';
    $shape = $settings['shape'] ?? 'circle';
    $show_tooltip = ( $settings['show_tooltip'] ?? '' ) === 'yes' && 'labeled' !== $skin;
    $tooltip_pos = $settings['tooltip_position'] ?? 'top';
    $copy_success = $settings['copy_success_message'] ?? esc_html__( 'Link copied to clipboard', 'themeasy-lite' );
    $copy_error = $settings['copy_error_message'] ?? esc_html__( 'Could not copy the link', 'themeasy-lite' );
    $toast_duration = (float) ( $settings['toast_duration']['size'] ?? 2.5 );

    // Resolve share URL.
    $url_source = $settings['url_source'] ?? 'current';
    $custom_share_url = $settings['custom_share_url']['url'] ?? '';

    if ( 'custom' === $url_source && ! empty( $custom_share_url ) ) {
      // Sanitize through esc_url_raw() so a javascript:/data: scheme can never
      // reach data-share-url (which the JS feeds to window.open / clipboard).
      $share_url = esc_url_raw( $custom_share_url );
    } else {
      $share_url = $this->get_current_url();
    }

    // Resolve share text.
    $text_source = $settings['text_source'] ?? 'title';
    $custom_share_text = sanitize_textarea_field( $settings['custom_share_text'] ?? '' );

    if ( 'custom' === $text_source ) {
      $share_text = $custom_share_text;
    } elseif ( 'none' === $text_source ) {
      $share_text = '';
    } else {
      $share_text = wp_get_document_title();
    }

    $catalog = self::get_network_catalog();

    // ------------------------------------------------------------------------
    // Wrapper classes + attributes.
    // ------------------------------------------------------------------------
    $wrapper_classes = [
      'tms-social-share-row',
      'tms-social-share-row--skin-' . $skin,
    ];

    if ( 'labeled' !== $skin ) {
      $wrapper_classes[] = 'tms-social-share-row--shape-' . $shape;
    }

    if ( $show_tooltip ) {
      $wrapper_classes[] = 'tms-social-share-row--has-tooltip';
      $wrapper_classes[] = 'tms-social-share-row--tooltip-' . $tooltip_pos;
    }

    $wrapper_atts = [
      'data-share-url' => $share_url,
      'data-share-text' => $share_text,
      'data-copy-ok' => $copy_success,
      'data-copy-error' => $copy_error,
      'data-toast-duration' => (string) max( 0.5, $toast_duration ),
    ];

    $wrapper_classes_output = implode( ' ', array_filter( $wrapper_classes ) );

    // ------------------------------------------------------------------------
    // Output.
    // ------------------------------------------------------------------------
    ?>
    <div class="<?php echo esc_attr( $wrapper_classes_output ); ?>"
      <?php themeasy_print_html_attributes( $wrapper_atts ); ?>>

      <ul class="tms-social-share-row__list" role="list">

        <?php foreach ( $networks as $index => $item ) : ?>
          <?php $this->render_network_item( $item, $index, $share_url, $share_text, $catalog, $skin ); ?>
        <?php endforeach; ?>

      </ul>

      <div class="tms-social-share-row__toast" role="status" aria-live="polite" hidden>
        <span class="tms-social-share-row__toast-text"></span>
      </div>

    </div><!-- /.tms-social-share-row -->
    <?php
  }

  /**
   * Render a single network item.
   *
   * @param array               $item       Repeater item settings.
   * @param int                 $index      Repeater index.
   * @param string              $share_url  Resolved URL to share.
   * @param string              $share_text Resolved text to share.
   * @param array<string,array> $catalog    Network catalog.
   * @param string              $skin       Current skin.
   * @return void
   */
  private function render_network_item(
    array $item,
    int $index,
    string $share_url,
    string $share_text,
    array $catalog,
    string $skin
  ): void {
    $network = $item['network'] ?? 'custom';
    $label = trim( (string) ( $item['label'] ?? '' ) );

    if ( ! isset( $catalog[$network] ) && 'custom' !== $network ) {
      return;
    }

    $is_custom = 'custom' === $network;
    $is_copy = 'copy' === $network;
    $is_email = 'email' === $network;

    $display_label = $label;

    if ( '' === $display_label && ! $is_custom ) {
      $display_label = $catalog[$network]['label'];
    }

    if ( '' === $display_label && $is_custom ) {
      $display_label = esc_html__( 'Share', 'themeasy-lite' );
    }

    $aria_label = $label !== ''
      ? $label
      : ( $is_custom ? esc_html__( 'Share', 'themeasy-lite' ) : $catalog[$network]['aria'] );

    // ------------------------------------------------------------------------
    // Resolve href + tag (anchor vs button).
    // ------------------------------------------------------------------------
    $href = '#';
    $tag = 'a';
    $extra_attrs = [];

    if ( $is_copy ) {
      $tag = 'button';
      $href = '';
    } elseif ( $is_custom ) {
      // esc_url() strips disallowed schemes (javascript:, data:, etc.) so the
      // per-item href can never become a script-execution vector.
      $custom_url = esc_url( $item['custom_url']['url'] ?? '' );

      if ( empty( $custom_url ) ) {
        return;
      }

      $href = $custom_url;
    } else {
      $href = $this->build_share_url( $network, $share_url, $share_text );

      if ( ! $is_email ) {
        $extra_attrs['target'] = '_blank';
        $extra_attrs['rel'] = 'noopener noreferrer';
      }
    }

    // ------------------------------------------------------------------------
    // Item attributes.
    // ------------------------------------------------------------------------
    $item_classes = [
      'tms-social-share-row__item',
      'tms-social-share-row__item--' . $network,
      'elementor-repeater-item-' . ( $item['_id'] ?? '' ),
    ];

    $item_classes_output = implode( ' ', array_filter( $item_classes ) );

    $link_key = 'social_share_link_' . $index;

    $this->add_render_attribute( $link_key, 'class', $item_classes_output );
    $this->add_render_attribute( $link_key, 'aria-label', $aria_label );
    $this->add_render_attribute( $link_key, 'data-network', $network );

    if ( 'a' === $tag ) {
      $this->add_render_attribute( $link_key, 'href', $href );
    } else {
      $this->add_render_attribute( $link_key, 'type', 'button' );
    }

    foreach ( $extra_attrs as $name => $value ) {
      $this->add_render_attribute( $link_key, $name, $value );
    }

    // ------------------------------------------------------------------------
    // Icon HTML.
    // ------------------------------------------------------------------------
    $icon_html = '';

    if ( $is_custom && ! empty( $item['custom_icon']['value'] ) ) {
      $icon_html = themeasy_render_icon_html(
        $item['custom_icon'],
        ['class' => 'tms-social-share-row__icon-svg', 'aria-hidden' => 'true']
      );
    } elseif ( isset( $catalog[$network] ) ) {
      $icon_html = themeasy_get_svg_icon(
        $catalog[$network]['icon_library'],
        $catalog[$network]['icon_name'],
        'tms-social-share-row__icon-svg'
      );
    }

    // ------------------------------------------------------------------------
    // Output.
    // ------------------------------------------------------------------------
    ?>
    <li class="tms-social-share-row__cell">
      <<?php echo tag_escape( $tag ); ?>
        <?php $this->print_render_attribute_string( $link_key ); ?>>

        <span class="tms-social-share-row__icon" aria-hidden="true">
          <?php echo wp_kses( $icon_html, themeasy_get_icon_kses_tags() ); ?>
        </span>

        <?php if ( 'labeled' === $skin ) : ?>
          <span class="tms-social-share-row__label"><?php echo esc_html( $display_label ); ?></span>
        <?php elseif ( '' !== $display_label ) : ?>
          <span class="tms-social-share-row__tooltip" role="tooltip">
            <?php echo esc_html( $display_label ); ?>
          </span>
        <?php endif; ?>

      </<?php echo tag_escape( $tag ); ?>>
    </li>
    <?php
  }

  /**
   * Build the network-specific share URL.
   *
   * @param string $network    Network slug.
   * @param string $share_url  URL to share.
   * @param string $share_text Optional text snippet.
   * @return string
   */
  private function build_share_url( string $network, string $share_url, string $share_text ): string {
    $share_url = (string) $share_url;
    $share_text = (string) $share_text;

    switch ( $network ) {
      case 'x':
        $params = ['url' => $share_url];

        if ( '' !== $share_text ) {
          $params['text'] = $share_text;
        }

        return 'https://twitter.com/intent/tweet?' . http_build_query( $params );
      case 'facebook':
        return 'https://www.facebook.com/sharer/sharer.php?' . http_build_query( ['u' => $share_url] );
      case 'linkedin':
        return 'https://www.linkedin.com/sharing/share-offsite/?' . http_build_query( ['url' => $share_url] );
      case 'whatsapp':
        $message = '' !== $share_text ? trim( $share_text . ' ' . $share_url ) : $share_url;

        return 'https://api.whatsapp.com/send?' . http_build_query( ['text' => $message] );
      case 'email':
        $params = [];

        if ( '' !== $share_text ) {
          $params['subject'] = $share_text;
        }

        $params['body'] = $share_url;

        return 'mailto:?' . http_build_query( $params );
    }

    return $share_url;
  }

  /**
   * Resolve the current page URL with a safe fallback for archive contexts.
   *
   * Anchored on home_url() so the host can never be poisoned via the Host
   * header. Only the request path is taken from $_SERVER and is sanitized.
   *
   * @return string
   */
  private function get_current_url(): string {
    if ( is_singular() ) {
      $permalink = get_permalink();

      if ( ! empty( $permalink ) ) {
        return $permalink;
      }
    }

    $path = isset( $_SERVER['REQUEST_URI'] )
      ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) )
      : '';

    return esc_url_raw( home_url( $path ) );
  }

  /**
   * Render the live preview template used by the Elementor editor.
   *
   * @return void
   */
  protected function content_template() {
    // Harden JSON encoding so any future translated label containing `<`, `>`,
    // `&`, or quotes cannot break out of the inline <script> the Elementor
    // editor wraps content_template() in.
    $json_flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
    $svg_map = [];

    foreach ( self::get_network_catalog() as $key => $info ) {
      $svg_map[$key] = themeasy_get_svg_icon(
        $info['icon_library'],
        $info['icon_name'],
        'tms-social-share-row__icon-svg'
      );
    }
    ?>
    <#
      // ------------------------------------------------------------------------
      // Settings.
      // ------------------------------------------------------------------------
      var networks = settings.networks || [];

      if ( ! networks.length ) {
        return;
      }

      var skin           = settings.skin || 'inline';
      var shape          = settings.shape || 'circle';
      var showTooltip    = settings.show_tooltip === 'yes' && skin !== 'labeled';
      var tooltipPos     = settings.tooltip_position || 'top';
      var copySuccess    = settings.copy_success_message || '<?php echo esc_js( esc_html__( 'Link copied to clipboard', 'themeasy-lite' ) ); ?>';
      var copyError      = settings.copy_error_message || '<?php echo esc_js( esc_html__( 'Could not copy the link', 'themeasy-lite' ) ); ?>';
      var toastDuration  = ( settings.toast_duration && settings.toast_duration.size )
        ? parseFloat( settings.toast_duration.size )
        : 2.5;

      var catalog = <?php echo wp_json_encode( self::get_network_catalog(), $json_flags ); ?>;
      var svgMap  = <?php echo wp_json_encode( $svg_map, $json_flags ); ?>;

      // Editor preview share data — we only display labels; clicks are inert.
      var shareUrl  = location && location.href ? location.href : '';
      var shareText = '';

      // ------------------------------------------------------------------------
      // Wrapper classes + attributes.
      // ------------------------------------------------------------------------
      var wrapperClasses = [
        'tms-social-share-row',
        'tms-social-share-row--skin-' + skin
      ];

      if ( skin !== 'labeled' ) {
        wrapperClasses.push( 'tms-social-share-row--shape-' + shape );
      }

      if ( showTooltip ) {
        wrapperClasses.push( 'tms-social-share-row--has-tooltip' );
        wrapperClasses.push( 'tms-social-share-row--tooltip-' + tooltipPos );
      }

      var wrapperAtts = {
        'data-share-url': shareUrl,
        'data-share-text': shareText,
        'data-copy-ok': copySuccess,
        'data-copy-error': copyError,
        'data-toast-duration': String( Math.max( 0.5, toastDuration ) )
      };

      var wrapperClassStr = wrapperClasses.filter( Boolean ).join( ' ' );

      // Scheme guard for the custom-network href. The regex lives in exactly one
      // place; the fallback fails closed rather than duplicating it (a bare ^
      // anchor misses the C0 controls browsers strip before resolving a scheme).
      var safeUrl = ( window.Themeasy && window.Themeasy.safeUrl )
        ? window.Themeasy.safeUrl
        : function () { return ''; };
    #>

    <div class="{{ wrapperClassStr }}" {{{ Themeasy.htmlAttributes( wrapperAtts ) }}}>

      <ul class="tms-social-share-row__list" role="list">

        <# _.each( networks, function( item, index ) {
          var network = item.network || 'custom';
          var label   = ( item.label || '' ).toString().trim();
          var isCustom = network === 'custom';
          var isCopy   = network === 'copy';

          if ( ! isCustom && ! catalog[ network ] ) { return; }

          var displayLabel = label;

          if ( ! displayLabel && ! isCustom ) {
            displayLabel = catalog[ network ].label;
          }

          if ( ! displayLabel && isCustom ) {
            displayLabel = '<?php echo esc_js( esc_html__( 'Share', 'themeasy-lite' ) ); ?>';
          }

          var ariaLabel = label
            ? label
            : ( isCustom
                ? '<?php echo esc_js( esc_html__( 'Share', 'themeasy-lite' ) ); ?>'
                : catalog[ network ].aria );

          var tag = isCopy ? 'button' : 'a';
          var href = '#';

          if ( isCustom ) {
            href = safeUrl( ( item.custom_url && item.custom_url.url ) || '' );
            if ( ! href ) { return; }
          } else if ( ! isCopy ) {
            href = '#';
          }

          var itemClass = 'tms-social-share-row__item tms-social-share-row__item--' + network
            + ' elementor-repeater-item-' + ( item._id || '' );

          // Icon markup.
          var iconMarkup = '';

          if ( isCustom && item.custom_icon && item.custom_icon.value ) {
            iconMarkup = ( window.Themeasy && window.Themeasy.renderIconMarkup )
              ? window.Themeasy.renderIconMarkup(
                  view, item.custom_icon, null,
                  { 'class': 'tms-social-share-row__icon-svg', 'aria-hidden': 'true' }
                )
              : '';
          } else if ( svgMap[ network ] ) {
            iconMarkup = svgMap[ network ];
          }
        #>

          <li class="tms-social-share-row__cell">
            <# if ( tag === 'button' ) { #>
              <button type="button"
                class="{{ itemClass }}"
                aria-label="{{ ariaLabel }}"
                data-network="{{ network }}">
            <# } else { #>
              <a href="{{ href }}"
                class="{{ itemClass }}"
                aria-label="{{ ariaLabel }}"
                data-network="{{ network }}">
            <# } #>

              <span class="tms-social-share-row__icon" aria-hidden="true">{{{ iconMarkup }}}</span>

              <# if ( skin === 'labeled' ) { #>
                <span class="tms-social-share-row__label">{{ displayLabel }}</span>
              <# } else if ( displayLabel ) { #>
                <span class="tms-social-share-row__tooltip" role="tooltip">{{ displayLabel }}</span>
              <# } #>

            <# if ( tag === 'button' ) { #>
              </button>
            <# } else { #>
              </a>
            <# } #>
          </li>
        <# }); #>

      </ul>

      <div class="tms-social-share-row__toast" role="status" aria-live="polite" hidden>
        <span class="tms-social-share-row__toast-text"></span>
      </div>

    </div><!-- /.tms-social-share-row -->

    <?php
  }
}
