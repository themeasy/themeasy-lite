<?php
/**
 * Themeasy Elementor Widget: Badge List
 *
 * Displays a list of badges (tags, chips, pills) with optional links, full
 * style controls (typography, spacing, border, shadow, normal/hover colors).
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
class BadgeList extends Widget_Base {
  public function get_name() {
    return 'themeasy-badge-list';
  }

  public function get_title() {
    return esc_html__( 'Badge List', 'themeasy-lite' );
  }

  public function get_icon() {
    return 'eicon-form-vertical';
  }

  public function get_custom_help_url() {
    return 'https://hub.themeasy.co/docs';
  }

  public function get_categories() {
    return ['themeasy_elements_category', 'themeasy_content_category'];
  }

  public function get_keywords() {
    return ['themeasy', 'badge', 'list', 'tag', 'label', 'chip', 'pill'];
  }

  public function get_style_depends() {
    return ['themeasy-bundle'];
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
    // Content section: Badge List
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'badge_list_section',
        [
          'label' => esc_html__( 'Badge List', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $repeater = new Repeater();

      // Optional per badge — the glyph is the exception, not the rule, so it
      // carries no default (same shape as the icon-list repeater).
      $repeater->add_control(
        'icon',
        [
          'label' => esc_html__( 'Icon', 'themeasy-lite' ),
          'type' => Controls_Manager::ICONS,
        ]
      );

      $repeater->add_control(
        'text',
        [
          'label' => esc_html__( 'Text', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'Text', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'Type your text here', 'themeasy-lite' ),
        ]
      );

      $repeater->add_control(
        'link',
        [
          'label' => esc_html__( 'Link', 'themeasy-lite' ),
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

      $this->add_control(
        'badge_list',
        [
          'label' => esc_html__( 'Items', 'themeasy-lite' ),
          'type' => Controls_Manager::REPEATER,
          'fields' => $repeater->get_controls(),
          'default' => [
            [
              'text' => esc_html__( 'Item #1', 'themeasy-lite' ),
              'link' => ['url' => '', 'is_external' => false, 'nofollow' => true],
            ],
            [
              'text' => esc_html__( 'Item #2', 'themeasy-lite' ),
              'link' => ['url' => '', 'is_external' => false, 'nofollow' => true],
            ],
            [
              'text' => esc_html__( 'Item #3', 'themeasy-lite' ),
              'link' => ['url' => '', 'is_external' => false, 'nofollow' => true],
            ],
          ],
          'title_field' => '{{{ text }}}',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Badge
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'badge_styles_section',
        [
          'label' => esc_html__( 'Badge', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      // Colors (Normal / Hover).
      $this->start_controls_tabs( 'badge_color_tabs' );

        $this->start_controls_tab(
          'badge_normal_tab',
          [
            'label' => esc_html__( 'Normal', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'badge_text_color',
            [
              'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                // The icon rides the text color through currentColor; without
                // it the shared .tms-badge__icon rule keeps painting the
                // heading color against an authored text color.
                '{{WRAPPER}} .tms-badge__text,
                 {{WRAPPER}} .tms-badge__icon' => 'color: {{VALUE}}',
              ],
            ]
          );

          $this->add_control(
            'badge_background_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-badge' => 'background-color: {{VALUE}}',
              ],
            ]
          );

          $this->add_group_control(
            Group_Control_Border::get_type(),
            [
              'name' => 'badge_border',
              'selector' => '{{WRAPPER}} .tms-badge',
            ]
          );

        $this->end_controls_tab();

        $this->start_controls_tab(
          'badge_hover_tab',
          [
            'label' => esc_html__( 'Hover', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'badge_text_hover_color',
            [
              'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-badge:hover .tms-badge__text,
                 {{WRAPPER}} .tms-badge:hover .tms-badge__icon' => 'color: {{VALUE}}',
              ],
            ]
          );

          $this->add_control(
            'badge_background_hover_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-badge:hover' => 'background-color: {{VALUE}}',
              ],
            ]
          );

          $this->add_control(
            'badge_border_hover_color',
            [
              'label' => esc_html__( 'Border Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-badge:hover' => 'border-color: {{VALUE}}',
              ],
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

      // Typography.
      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'badge_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-badge__text',
        ]
      );

      // Layout heading.
      $this->add_control(
        'badge_layout_heading',
        [
          'label' => esc_html__( 'Layout', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'badge_alignment',
        [
          'label' => esc_html__( 'Alignment', 'themeasy-lite' ),
          'type' => Controls_Manager::CHOOSE,
          'options' => [
            'flex-start' => [
              'title' => esc_html__( 'Left', 'themeasy-lite' ),
              'icon' => 'eicon-text-align-left',
            ],
            'center' => [
              'title' => esc_html__( 'Center', 'themeasy-lite' ),
              'icon' => 'eicon-text-align-center',
            ],
            'flex-end' => [
              'title' => esc_html__( 'Right', 'themeasy-lite' ),
              'icon' => 'eicon-text-align-right',
            ],
          ],
          // The list is inline-flex, a box as wide as its badges: with no free
          // space, justify-content only acted once the badges wrapped. An
          // authored alignment makes the list take the row.
          'selectors' => [
            '{{WRAPPER}} .tms-badge-list' => 'display: flex; justify-content: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'badge_spacing_between',
        [
          'label' => esc_html__( 'Spacing Between', 'themeasy-lite' ),
          'description' => esc_html__( 'Gap between badges.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['rem', 'px'],
          'range' => [
            'rem' => ['min' => 0, 'max' => 10, 'step' => 0.1],
            'px' => ['min' => 0, 'max' => 100, 'step' => 1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-badge-list' => 'gap: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'badge_padding',
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      // Border heading.
      $this->add_control(
        'badge_border_heading',
        [
          'label' => esc_html__( 'Border', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'badge_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      // Box shadow.
      $this->add_group_control(
        Group_Control_Box_Shadow::get_type(),
        [
          'name' => 'badge_box_shadow',
          'label' => esc_html__( 'Box Shadow', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-badge',
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
    $items = $settings['badge_list'] ?? [];

    // Early return if no content.
    if ( empty( $items ) ) {
      return;
    }

    // ------------------------------------------------------------------------
    // Wrapper classes + attributes.
    // ------------------------------------------------------------------------
    $wrapper_classes = ['tms-badge-list'];
    $wrapper_atts = [];

    $wrapper_classes_output = implode( ' ', array_filter( $wrapper_classes ) );

    // ------------------------------------------------------------------------
    // Output.
    // ------------------------------------------------------------------------
    ?>
    <div class="<?php echo esc_attr( $wrapper_classes_output ); ?>"<?php themeasy_print_html_attributes( $wrapper_atts ); ?>>

      <?php foreach ( $items as $index => $item ) : ?>
        <?php
        $text = $item['text'] ?? '';

        // Icon (optional). The helper returns the glyph only — the wrapper span
        // below owns the BEM class the style rules target.
        $icon_html = themeasy_render_icon_html(
          is_array( $item['icon'] ?? null ) ? $item['icon'] : []
        );

        // Render attributes + inline editing.
        $text_key = $this->get_repeater_setting_key( 'text', 'badge_list', $index );
        $this->add_render_attribute( $text_key, 'class', 'tms-badge__text' );
        $this->add_inline_editing_attributes( $text_key, 'none' );

        // Link (per-item unique key to avoid attribute collisions).
        $has_link = !empty( $item['link']['url'] );
        $link_key = 'badge_link_' . $index;

        if ( $has_link ) {
          $this->add_render_attribute( $link_key, 'class', 'link' );
          $this->add_link_attributes( $link_key, $item['link'] );
          themeasy_add_external_link_rel( $this, $link_key, $item['link'] );
        }
        ?>

        <?php if ( $has_link ) : ?>
          <a <?php $this->print_render_attribute_string( $link_key ); ?>>
        <?php endif; ?>

          <div class="tms-animation__target">
            <div class="tms-badge">
              <?php if ( $icon_html ) : ?>
                <span class="tms-badge__icon" aria-hidden="true">
                  <?php echo wp_kses( $icon_html, themeasy_get_icon_kses_tags() ); ?>
                </span>
              <?php endif; ?>
              <span <?php $this->print_render_attribute_string( $text_key ); ?>>
                <?php echo esc_html( $text ); ?>
              </span>
            </div>
          </div>

        <?php if ( $has_link ) : ?>
          </a>
        <?php endif; ?>

      <?php endforeach; ?>

    </div><!-- /.tms-badge-list -->
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
      // Escaping helper. The template has no esc_url() to fall back on, so
      // every href built from a setting goes through safeUrl — it returns ''
      // for an unsafe scheme, which the markup below treats as "no link".
      // Never re-inline the guard (see .claude/rules/elementor-content-template.md, "URLs").
      // ------------------------------------------------------------------------
      var safeUrl = ( window.Themeasy && window.Themeasy.safeUrl )
        ? window.Themeasy.safeUrl
        : function () { return ''; };

      // ------------------------------------------------------------------------
      // Settings.
      // ------------------------------------------------------------------------
      var items = settings.badge_list || [];

      if ( ! items.length ) {
        return;
      }

      // ------------------------------------------------------------------------
      // Wrapper classes + attributes.
      // ------------------------------------------------------------------------
      var wrapperClasses = [ 'tms-badge-list' ];
      var wrapperAtts    = {};

      var wrapperClassStr = wrapperClasses.filter( Boolean ).join( ' ' );
    #>

    <div class="{{ wrapperClassStr }}" {{{ Themeasy.htmlAttributes( wrapperAtts ) }}}>

      <# _.each( items, function( item, index ) {
        var linkUrl = safeUrl( ( item.link && item.link.url ) || '' );
        var hasLink = !! linkUrl;

        // Render attributes + inline editing.
        var textKey = view.getRepeaterSettingKey( 'text', 'badge_list', index );
        view.addRenderAttribute( textKey, 'class', 'tms-badge__text' );
        view.addInlineEditingAttributes( textKey, 'none' );

        // Icon (optional) — mirrors render(): the helper returns the glyph only.
        var iconMarkup = ( window.Themeasy && window.Themeasy.renderIconMarkup )
          ? window.Themeasy.renderIconMarkup( view, item.icon, null, {} )
          : '';

        // Link attributes (editor preview uses href only). Escape the guarded
        // URL so a quote in the field cannot break out of the attribute.
        var linkAttrs = hasLink ? 'href="' + _.escape( linkUrl ) + '"' : '';
      #>

        <# if ( hasLink ) { #>
          <a {{{ linkAttrs }}} class="link">
        <# } #>

          <div class="tms-animation__target">
            <div class="tms-badge">
              <# if ( iconMarkup ) { #>
                <span class="tms-badge__icon" aria-hidden="true">{{{ iconMarkup }}}</span>
              <# } #>
              <span {{{ view.getRenderAttributeString( textKey ) }}}>{{ item.text }}</span>
            </div>
          </div>

        <# if ( hasLink ) { #>
          </a>
        <# } #>

      <# }); #>

    </div><!-- /.tms-badge-list -->

    <?php
  }
}
