<?php
/**
 * Themeasy Elementor Widget: Steps
 *
 * Displays a series of steps with icons (or auto-numbered badges), titles, and
 * descriptions in a connected card layout. Supports horizontal/vertical
 * orientation and Material/Outline skins.
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
 * Steps widget — renders numbered or icon-based step items in a card layout.
 */
class Steps extends Widget_Base {
  public function get_name() {
    return 'themeasy-steps';
  }

  public function get_title() {
    return esc_html__( 'Steps', 'themeasy-lite' );
  }

  public function get_icon() {
    return 'eicon-number-field';
  }

  public function get_custom_help_url() {
    return 'https://themeasy.co/help-center';
  }

  public function get_categories() {
    return ['themeasy_blocks_category', 'themeasy_content_category'];
  }

  public function get_keywords() {
    return ['themeasy', 'steps', 'process', 'timeline', 'progress', 'numbered', 'workflow'];
  }

  public function get_style_depends() {
    return ['themeasy-steps'];
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
    // Content section: Steps Items
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'steps_items_section',
        [
          'label' => esc_html__( 'Steps Items', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
    );

      $repeater = new Repeater();

      $repeater->add_control(
        'title',
        [
          'label' => esc_html__( 'Title', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXTAREA,
          'placeholder' => esc_html__( 'Type your text here', 'themeasy-lite' ),
          'dynamic' => [
            'active' => true,
          ],
        ]
      );

      $repeater->add_control(
        'content',
        [
          'label' => esc_html__( 'Content', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::WYSIWYG,
          'default' => esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean lobortis magna et ipsum rutrum lobortis.', 'themeasy-lite' ),
        ]
      );

      $repeater->add_control(
        'icon',
        [
          'label' => esc_html__( 'Icon', 'themeasy-lite' ),
          'description' => esc_html__( 'Leave empty to show an auto-incrementing number badge.', 'themeasy-lite' ),
          'type' => Controls_Manager::ICONS,
        ]
      );

      $this->add_control(
        'steps_items',
        [
          'label' => esc_html__( 'Items', 'themeasy-lite' ),
          'type' => Controls_Manager::REPEATER,
          'fields' => $repeater->get_controls(),
          'default' => [
            [
              'title' => esc_html__( 'Item #1', 'themeasy-lite' ),
              'content' => esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean lobortis magna et ipsum rutrum lobortis.', 'themeasy-lite' ),
            ],
            [
              'title' => esc_html__( 'Item #2', 'themeasy-lite' ),
              'content' => esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean lobortis magna et ipsum rutrum lobortis.', 'themeasy-lite' ),
            ],
            [
              'title' => esc_html__( 'Item #3', 'themeasy-lite' ),
              'content' => esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean lobortis magna et ipsum rutrum lobortis.', 'themeasy-lite' ),
            ],
          ],
          'title_field' => '{{{ title }}}',
        ]
      );

      $this->add_control(
        'steps_orientation',
        [
          'label' => esc_html__( 'Orientation', 'themeasy-lite' ),
          'type' => Controls_Manager::CHOOSE,
          'separator' => 'before',
          'options' => [
            'horizontal' => [
              'title' => esc_html__( 'Horizontal', 'themeasy-lite' ),
              'icon' => 'eicon-v-align-top',
            ],
            'vertical' => [
              'title' => esc_html__( 'Vertical', 'themeasy-lite' ),
              'icon' => 'eicon-h-align-left',
            ],
          ],
          'default' => 'horizontal',
          'toggle' => false,
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Settings
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'steps_settings_section',
        [
          'label' => esc_html__( 'Settings', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
    );

      $this->add_control(
        'steps_skin',
        [
          'label' => esc_html__( 'Skin', 'themeasy-lite' ),
          'description' => esc_html__( 'Switch between the shadowed Material card and the bordered Outline look. It sets the starting card and badge radius, which the Style controls then override.', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'material',
          'options' => [
            'material' => esc_html__( 'Material Shadow', 'themeasy-lite' ),
            'outline' => esc_html__( 'Outline Bold', 'themeasy-lite' ),
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Block
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'steps_block_section',
        [
          'label' => esc_html__( 'Block', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      $this->add_control(
        'block_width',
        [
          'label' => esc_html__( 'Width', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['%', 'px', 'rem', 'em', 'vw', 'custom'],
          'range' => [
            '%' => [
              'min' => 10,
              'max' => 90,
            ],
            'px' => [
              'min' => 200,
              'max' => 1200,
            ],
            'rem' => [
              'min' => 10,
              'max' => 120,
            ],
          ],
          'default' => [
            'unit' => '%',
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-steps.tms-steps--vertical' => 'max-width: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'steps_orientation' => 'vertical',
          ],
        ]
      );

      $this->add_control(
        'block_background_color',
        [
          'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'separator' => 'before',
          'selectors' => [
            '{{WRAPPER}} .tms-steps .tms-steps__item .tms-steps__content .tms-card' => 'background-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'block_padding',
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-steps .tms-steps__item .tms-steps__content .tms-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Border::get_type(),
        [
          'name' => 'block_border',
          'selector' => '{{WRAPPER}} .tms-steps .tms-steps__item .tms-steps__content .tms-card',
        ]
      );

      $this->add_control(
        'block_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'description' => esc_html__( 'Rounds the step card. Leave empty to follow the theme radius picked by the Skin.', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', '%', 'em', 'rem', 'custom'],
          'selectors' => [
            '{{WRAPPER}} .tms-steps .tms-steps__item .tms-steps__content .tms-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Box_Shadow::get_type(),
        [
          'name' => 'block_box_shadow',
          'selector' => '{{WRAPPER}} .tms-steps .tms-steps__item .tms-steps__content .tms-card',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Title
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'steps_title_section',
        [
          'label' => esc_html__( 'Title', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      $this->add_control(
        'title_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-steps .tms-steps__item .tms-steps__content .tms-card__title' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'title_typography',
          'selector' => '{{WRAPPER}} .tms-steps .tms-steps__item .tms-steps__content .tms-card__title',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Content
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'steps_content_section',
        [
          'label' => esc_html__( 'Content', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      $this->add_control(
        'content_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-steps .tms-steps__item .tms-steps__content .tms-card__description' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'content_typography',
          'selector' => '{{WRAPPER}} .tms-steps .tms-steps__item .tms-steps__content .tms-card__description',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Icon
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'steps_icon_section',
        [
          'label' => esc_html__( 'Icon', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      $this->add_control(
        'icon_color',
        [
          'label' => esc_html__( 'Icon Color', 'themeasy-lite' ),
          'description' => esc_html__( 'Drives the glyph via currentColor: stroke for outline icons, fill for solid ones.', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-steps .tms-steps__item .tms-steps__icon,
             {{WRAPPER}} .tms-steps .tms-steps__item .tms-steps__number' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'icon_fill_color',
        [
          'label' => esc_html__( 'Icon Fill Color', 'themeasy-lite' ),
          'description' => esc_html__( 'Applies to SVG icons only.', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-steps .tms-steps__item .tms-steps__icon svg' => 'fill: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'icon_stroke_color',
        [
          'label' => esc_html__( 'Icon Stroke Color', 'themeasy-lite' ),
          'description' => esc_html__( 'Applies to SVG icons only.', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-steps .tms-steps__item .tms-steps__icon svg' => 'stroke: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'icon_background_color',
        [
          'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'separator' => 'before',
          'selectors' => [
            '{{WRAPPER}} .tms-steps .tms-steps__item .tms-steps__icon,
             {{WRAPPER}} .tms-steps .tms-steps__item .tms-steps__number' => 'background-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'icon_size',
        [
          'label' => esc_html__( 'Icon Size', 'themeasy-lite' ),
          'description' => esc_html__( 'Glyph size; the circular node (icon or number badge) scales around it.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'separator' => 'before',
          'size_units' => ['px'],
          'range' => [
            'px' => ['min' => 8, 'max' => 48, 'step' => 1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-steps' => '--tms-steps-icon-size: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'icon_thickness',
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
            '{{WRAPPER}} .tms-steps .tms-steps__item .tms-steps__icon svg,
             {{WRAPPER}} .tms-steps .tms-steps__item .tms-steps__icon svg *' => 'stroke-width: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'icon_node_size',
        [
          'label' => esc_html__( 'Node Size', 'themeasy-lite' ),
          'description' => esc_html__( 'Size of the badge tile behind the icon or number. Leave empty to track the Icon Size at twice the glyph.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'separator' => 'before',
          'size_units' => ['px'],
          'range' => [
            'px' => ['min' => 16, 'max' => 160, 'step' => 1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-steps' => '--tms-steps-node-size: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      // box-sizing is border-box project-wide, so the hairline is drawn inside
      // the node — the badge keeps its size and the glyph never shifts.
      $this->add_group_control(
        Group_Control_Border::get_type(),
        [
          'name' => 'icon_border',
          'label' => esc_html__( 'Border', 'themeasy-lite' ),
          'separator' => 'before',
          'selector' => '{{WRAPPER}} .tms-steps .tms-steps__item .tms-steps__icon,
                         {{WRAPPER}} .tms-steps .tms-steps__item .tms-steps__number',
        ]
      );

      $this->add_control(
        'icon_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'description' => esc_html__( 'Rounds the badge node behind the icon or number. Leave empty for the shape set by the Skin (circle / rounded square).', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'separator' => 'before',
          'size_units' => ['px', '%', 'em', 'rem', 'custom'],
          'selectors' => [
            '{{WRAPPER}} .tms-steps .tms-steps__item .tms-steps__icon,
             {{WRAPPER}} .tms-steps .tms-steps__item .tms-steps__number' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Connector Line
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'steps_line_section',
        [
          'label' => esc_html__( 'Connector Line', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      $this->add_control(
        'line_color',
        [
          'label' => esc_html__( 'Line Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-steps .tms-steps__item::before' => 'background-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'line_width',
        [
          'label' => esc_html__( 'Line Thickness', 'themeasy-lite' ),
          'description' => esc_html__( 'Thickness of the connector line. It stays centred on the badge nodes in both orientations. Leave empty to follow the Skin (4px Material, 2px Outline).', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => ['min' => 1, 'max' => 20, 'step' => 1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-steps' => '--tms-steps-line-width: {{SIZE}}{{UNIT}};',
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
    $items = $settings['steps_items'] ?? [];
    $orientation = $settings['steps_orientation'] ?? 'horizontal';
    $skin = $settings['steps_skin'] ?? 'material';

    // Early return if no content.
    if ( empty( $items ) ) {
      return;
    }

    // ------------------------------------------------------------------------
    // Wrapper classes + attributes.
    // ------------------------------------------------------------------------
    $wrapper_classes = ['tms-steps'];
    $wrapper_atts = [];

    // Columns.
    $columns = count( $items );
    $col_class = themeasy_bootstrap_columns( (int) $columns );

    if ( 'vertical' === $orientation ) {
      $wrapper_classes[] = 'tms-steps--vertical';
      $col_class = 'tms-steps__wrapper';
    }

    // Skin.
    if ( 'outline' === $skin ) {
      $wrapper_classes[] = 'tms-steps--outline';
    }

    $wrapper_classes_output = implode( ' ', array_filter( $wrapper_classes ) );
    $wrapper_atts_output = themeasy_html_attributes( $wrapper_atts );

    // ------------------------------------------------------------------------
    // Output.
    // ------------------------------------------------------------------------
    ?>
    <div class="<?php echo esc_attr( $wrapper_classes_output ); ?>"<?php echo $wrapper_atts_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>>
      <div class="<?php echo esc_attr( $col_class ); ?>">

        <?php foreach ( $items as $index => $item ) : ?>
          <?php
          // Render attributes.
          $title_key = $this->get_repeater_setting_key( 'title', 'steps_items', $index );
          $content_key = $this->get_repeater_setting_key( 'content', 'steps_items', $index );

          $this->add_render_attribute( $title_key, 'class', 'tms-card__title' );
          $this->add_render_attribute( $content_key, 'class', 'tms-card__description' );

          $this->add_inline_editing_attributes( $title_key, 'none' );
          $this->add_inline_editing_attributes( $content_key, 'advanced' );

          // Icon (contract: the widget owns the wrapper span; the helper
          // returns the normalized glyph element only). Falls back to an
          // auto-incrementing number badge.
          $icon_html = !empty( $item['icon']['value'] )
            ? themeasy_render_icon_html( $item['icon'] )
            : '';
          ?>

          <div class="tms-steps__item">

            <?php if ( $icon_html ) : ?>
              <span class="tms-steps__icon" aria-hidden="true">
                <?php echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
              </span>
            <?php else : ?>
              <span class="tms-steps__number"><?php echo esc_html( $index + 1 ); ?></span>
            <?php endif; ?>

            <div class="tms-steps__content">
              <div class="tms-animation__target">
                <div class="tms-card">
                  <div class="tms-card__body">

                    <?php if ( !empty( $item['title'] ) ) : ?>
                      <h4 <?php $this->print_render_attribute_string( $title_key ); ?>>
                        <?php echo esc_html( $item['title'] ); ?>
                      </h4>
                    <?php endif; ?>

                    <?php if ( !empty( $item['content'] ) ) : ?>
                      <div <?php $this->print_render_attribute_string( $content_key ); ?>>
                        <?php echo wp_kses_post( $item['content'] ); ?>
                      </div>
                    <?php endif; ?>

                  </div>
                </div>
              </div>
            </div>
          </div><!-- /.tms-steps__item -->

        <?php endforeach; ?>

      </div>
    </div><!-- /.tms-steps -->
    <?php
  }

  /**
   * Render the editor preview template.
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
      var items       = settings.steps_items || [];
      var orientation = settings.steps_orientation || 'horizontal';
      var skin        = settings.steps_skin || 'material';

      if ( ! items.length ) {
        return;
      }

      // ------------------------------------------------------------------------
      // Wrapper classes + attributes.
      // ------------------------------------------------------------------------
      var wrapperClasses = [ 'tms-steps' ];
      var wrapperAtts    = {};

      // Columns.
      var columns  = items.length;
      var colClass = Themeasy.bootstrapColumns( parseInt( columns ) );

      if ( orientation === 'vertical' ) {
        wrapperClasses.push( 'tms-steps--vertical' );
        colClass = 'tms-steps__wrapper';
      }

      // Skin.
      if ( skin === 'outline' ) {
        wrapperClasses.push( 'tms-steps--outline' );
      }

      var wrapperClassStr = wrapperClasses.filter( Boolean ).join( ' ' );
    #>

    <div class="{{ wrapperClassStr }}" {{{ Themeasy.htmlAttributes( wrapperAtts ) }}}>
      <div class="{{ colClass }}">

        <# _.each( items, function( item, index ) {
          // Render attributes.
          var titleKey = view.getRepeaterSettingKey( 'title', 'steps_items', index );
          var contentKey = view.getRepeaterSettingKey( 'content', 'steps_items', index );
          var itemContent = sanitizeInline( item.content || '', 'rich' );

          view.addRenderAttribute( titleKey, 'class', 'tms-card__title' );
          view.addRenderAttribute( contentKey, 'class', 'tms-card__description' );

          view.addInlineEditingAttributes( titleKey, 'none' );
          view.addInlineEditingAttributes( contentKey, 'advanced' );

          // Icon (contract: the widget owns the wrapper span; the helper
          // returns the normalized glyph element only). Falls back to an
          // auto-incrementing number badge.
          var iconMarkup = ( item.icon && item.icon.value && window.Themeasy && window.Themeasy.renderIconMarkup )
            ? Themeasy.renderIconMarkup( view, item.icon, null, {} )
            : '';
        #>

          <div class="tms-steps__item">

            <# if ( iconMarkup ) { #>
              <span class="tms-steps__icon" aria-hidden="true">{{{ iconMarkup }}}</span>
            <# } else { #>
              <span class="tms-steps__number">{{ index + 1 }}</span>
            <# } #>

            <div class="tms-steps__content">
              <div class="tms-animation__target">
                <div class="tms-card">
                  <div class="tms-card__body">

                    <# if ( item.title ) { #>
                      <h4 {{{ view.getRenderAttributeString( titleKey ) }}}>{{ item.title }}</h4>
                    <# } #>

                    <# if ( item.content ) { #>
                      <div {{{ view.getRenderAttributeString( contentKey ) }}}>{{{ itemContent }}}</div>
                    <# } #>

                  </div>
                </div>
              </div>
            </div>
          </div><!-- /.tms-steps__item -->

        <# }); #>

      </div>
    </div><!-- /.tms-steps -->

    <?php
  }
}
