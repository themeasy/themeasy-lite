<?php
/**
 * Themeasy Elementor Widget: Accordion
 *
 * Collapsible accordion with repeater items, Bootstrap collapse integration,
 * optional multi-open behavior, and typography controls.
 *
 * @package Themeasy
 * @since 1.0.0
 */

namespace Themeasy\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Widget_Base;

/**
 * Accordion widget — registers controls and renders output.
 */
class Accordion extends Widget_Base {
  public function get_name() {
    return 'themeasy-accordion';
  }

  public function get_title() {
    return esc_html__( 'Accordion', 'themeasy-lite' );
  }

  public function get_icon() {
    return 'eicon-accordion';
  }

  public function get_custom_help_url() {
    return 'https://themeasy.co/help-center';
  }

  public function get_categories() {
    return ['themeasy_blocks_category', 'themeasy_content_category'];
  }

  public function get_keywords() {
    return ['themeasy', 'accordion', 'toggle', 'collapse', 'faq', 'expandable', 'content'];
  }

  public function get_style_depends() {
    return ['themeasy-bundle'];
  }

  // Collapse runs on Bootstrap's data-api, and nothing else loads Bootstrap's
  // script, so the Accordion asks for it itself.
  public function get_script_depends() {
    return ['themeasy-bootstrap'];
  }

  /**
   * Register widget controls.
   *
   * @return void
   */
  protected function register_controls() {
    // ------------------------------------------------------------------------
    // Content section: Accordion Items
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'accordion_content_section',
        [
          'label' => esc_html__( 'Accordion', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $repeater = new Repeater();

      $repeater->add_control(
        'expanded',
        [
          'label' => esc_html__( 'Start Expanded', 'themeasy-lite' ),
          'description' => esc_html__( 'Keep this accordion item open by default when the page loads.', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => '',
        ]
      );

      $repeater->add_control(
        'title',
        [
          'label' => esc_html__( 'Title', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXTAREA,
          'placeholder' => esc_html__( 'Type your text here', 'themeasy-lite' ),
        ]
      );

      $repeater->add_control(
        'description',
        [
          'label' => esc_html__( 'Description', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::WYSIWYG,
          'placeholder' => esc_html__( 'Type your text here', 'themeasy-lite' ),
        ]
      );

      $this->add_control(
        'accordion_items',
        [
          'label' => esc_html__( 'Items', 'themeasy-lite' ),
          'type' => Controls_Manager::REPEATER,
          'fields' => $repeater->get_controls(),
          'default' => [
            [
              'title' => esc_html__( 'Item #1', 'themeasy-lite' ),
              'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean lobortis magna et ipsum rutrum lobortis.',
            ],
            [
              'title' => esc_html__( 'Item #2', 'themeasy-lite' ),
              'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean lobortis magna et ipsum rutrum lobortis.',
            ],
            [
              'title' => esc_html__( 'Item #3', 'themeasy-lite' ),
              'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean lobortis magna et ipsum rutrum lobortis.',
            ],
          ],
          'title_field' => '{{{ title }}}',
        ]
      );

      $this->add_control(
        'allow_multiple_open',
        [
          'label' => esc_html__( 'Allow Multiple Open', 'themeasy-lite' ),
          'description' => esc_html__( 'Allow multiple accordion items to remain open simultaneously, without closing others.', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'separator' => 'before',
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => '',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Accordion
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'accordion_styles_section',
        [
          'label' => esc_html__( 'Accordion', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_control(
        'border_color',
        [
          'label' => esc_html__( 'Border Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-accordion .accordion-item' => 'border-color: {{VALUE}}',
          ],
        ]
      );

      $this->add_control(
        'item_background_color',
        [
          'label' => esc_html__( 'Item Background Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-accordion .accordion-item' => 'background-color: {{VALUE}};',
          ],
        ]
      );

      // The rule also zeroes the list rhythm of the title link (components.min.css reads
      // --tms-accordion-rhythm), so the authored padding is the whole inset. It rides the
      // padding rule to follow the breakpoint the padding is authored at. 0px, not 0: calc() reads it.
      $this->add_responsive_control(
        'item_padding',
        [
          'label' => esc_html__( 'Item Padding', 'themeasy-lite' ),
          'description' => esc_html__( 'Inset around each item. Use it together with a background or border to turn every item into a card.', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem'],
          'separator' => 'before',
          'selectors' => [
            '{{WRAPPER}} .tms-accordion .accordion-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; --tms-accordion-rhythm: 0px;',
          ],
        ]
      );

      // Registered after 'border_color' on purpose: both share the same selector, so
      // Elementor merges them into one rule and this group's color wins when both are set.
      $this->add_group_control(
        Group_Control_Border::get_type(),
        [
          'name' => 'item_border',
          'selector' => '{{WRAPPER}} .tms-accordion .accordion-item',
        ]
      );

      $this->add_responsive_control(
        'item_border_radius',
        [
          'label' => esc_html__( 'Item Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', '%'],
          'selectors' => [
            '{{WRAPPER}} .tms-accordion .accordion-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      // -- Title --

      $this->add_control(
        'title_heading',
        [
          'label' => esc_html__( 'Title', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'title_text_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-accordion .accordion-item .tms-accordion__title,
             {{WRAPPER}} .tms-accordion .accordion-item .accordion-button' => 'color: {{VALUE}}',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'title_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-accordion .accordion-item .tms-accordion__title',
        ]
      );

      // -- Description --

      $this->add_control(
        'description_heading',
        [
          'label' => esc_html__( 'Description', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'description_text_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-accordion .accordion-item .tms-accordion__description' => 'color: {{VALUE}}',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'description_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-accordion .accordion-item .tms-accordion__description',
        ]
      );

    $this->end_controls_section();
  }

  /**
   * Render the widget output on the frontend and in the editor preview.
   *
   * @return void
   */
  protected function render() {
    $settings = $this->get_settings_for_display();

    // ------------------------------------------------------------------------
    // Settings.
    // ------------------------------------------------------------------------
    $items = $settings['accordion_items'] ?? [];
    $allow_multiple_open = !empty( $settings['allow_multiple_open'] );

    // Early return if no content.
    if ( empty( $items ) ) {
      return;
    }

    // ------------------------------------------------------------------------
    // Wrapper classes + attributes.
    // ------------------------------------------------------------------------
    $element_id = $this->get_id();
    $parent_id = 'accordion-' . $element_id;
    $wrapper_classes = ['tms-accordion', 'accordion', 'accordion-flush'];

    $wrapper_classes_output = implode( ' ', array_filter( $wrapper_classes ) );

    // ------------------------------------------------------------------------
    // Output.
    // ------------------------------------------------------------------------
    ?>
    <div id="<?php echo esc_attr( $parent_id ); ?>"
      class="<?php echo esc_attr( $wrapper_classes_output ); ?>">

      <?php foreach ( $items as $index => $item ) : ?>
        <?php
        $collapse_id = 'collapse-' . $element_id . '-' . ( $index + 1 );
        $is_expanded = !empty( $item['expanded'] );
        $expanded_attr = $is_expanded ? 'true' : 'false';
        $button_class = $is_expanded ? '' : 'collapsed';
        $content_class = $is_expanded ? 'show' : '';

        // data-bs-parent should be present only when multiple open is NOT allowed.
        $collapse_atts = [];
        if ( !$allow_multiple_open ) {
          $collapse_atts['data-bs-parent'] = '#' . $parent_id;
        }
        $collapse_atts_output = themeasy_html_attributes( $collapse_atts );

        // -- Render attributes --
        $title_key = $this->get_repeater_setting_key( 'title', 'accordion_items', $index );
        $this->add_render_attribute( $title_key, 'class', 'tms-accordion__title' );
        $this->add_inline_editing_attributes( $title_key, 'basic' );

        $desc_key = $this->get_repeater_setting_key( 'description', 'accordion_items', $index );
        $this->add_render_attribute( $desc_key, 'class', 'tms-accordion__description' );
        $this->add_inline_editing_attributes( $desc_key, 'advanced' );
        ?>

        <div class="accordion-item">
          <h2 class="accordion-header">
            <a href="#"
              class="accordion-button <?php echo esc_attr( $button_class ); ?>"
              data-bs-toggle="collapse"
              data-bs-target="#<?php echo esc_attr( $collapse_id ); ?>"
              aria-expanded="<?php echo esc_attr( $expanded_attr ); ?>"
              aria-controls="<?php echo esc_attr( $collapse_id ); ?>">

              <div <?php $this->print_render_attribute_string( $title_key ); ?>>
                <?php echo wp_kses_post( $item['title'] ?? '' ); ?>
              </div>

            </a>
          </h2>

          <div id="<?php echo esc_attr( $collapse_id ); ?>"
            class="accordion-collapse collapse <?php echo esc_attr( $content_class ); ?>"<?php echo $collapse_atts_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>>
            <div class="accordion-body">

              <div <?php $this->print_render_attribute_string( $desc_key ); ?>>
                <?php echo wp_kses_post( $item['description'] ?? '' ); ?>
              </div>

            </div>
          </div>
        </div><!-- /.accordion-item -->

      <?php endforeach; ?>

    </div><!-- /.tms-accordion -->
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
      var items             = settings.accordion_items || [];
      var allowMultipleOpen = !! settings.allow_multiple_open;

      if ( ! items.length ) {
        return;
      }

      // ------------------------------------------------------------------------
      // Wrapper classes + attributes.
      // ------------------------------------------------------------------------
      var elementId       = view.getID();
      var parentId        = 'accordion-' + elementId;
      var wrapperClasses  = [ 'tms-accordion', 'accordion', 'accordion-flush' ];
      var wrapperClassStr = wrapperClasses.filter( Boolean ).join( ' ' );
    #>

    <div id="{{ parentId }}" class="{{ wrapperClassStr }}">

      <# _.each( items, function( item, index ) {
        var collapseId   = 'collapse-' + elementId + '-' + ( index + 1 );
        var isExpanded   = !! item.expanded;
        var expandedAttr = isExpanded ? 'true' : 'false';
        var buttonClass  = isExpanded ? '' : 'collapsed';
        var contentClass = isExpanded ? 'show' : '';

        // data-bs-parent only when multiple open is NOT allowed.
        var collapseAtts = {};
        if ( ! allowMultipleOpen ) {
          collapseAtts['data-bs-parent'] = '#' + parentId;
        }
        var collapseAttsOutput = Themeasy.htmlAttributes( collapseAtts );

        // -- Render attributes --
        var titleKey = view.getRepeaterSettingKey( 'title', 'accordion_items', index );
        view.addRenderAttribute( titleKey, 'class', 'tms-accordion__title' );
        view.addInlineEditingAttributes( titleKey, 'basic' );

        var descKey = view.getRepeaterSettingKey( 'description', 'accordion_items', index );
        view.addRenderAttribute( descKey, 'class', 'tms-accordion__description' );
        view.addInlineEditingAttributes( descKey, 'advanced' );
      #>

        <div class="accordion-item">
          <h2 class="accordion-header">
            <a href="#"
              class="accordion-button {{ buttonClass }}"
              data-bs-toggle="collapse"
              data-bs-target="#{{ collapseId }}"
              aria-expanded="{{ expandedAttr }}"
              aria-controls="{{ collapseId }}">

              <div {{{ view.getRenderAttributeString( titleKey ) }}}>
                {{{ sanitizeInline( item.title || '', 'rich' ) }}}
              </div>

            </a>
          </h2>

          <div id="{{ collapseId }}"
            class="accordion-collapse collapse {{ contentClass }}"
            {{{ collapseAttsOutput }}}>
            <div class="accordion-body">

              <div {{{ view.getRenderAttributeString( descKey ) }}}>
                {{{ sanitizeInline( item.description || '', 'rich' ) }}}
              </div>

            </div>
          </div>
        </div><!-- /.accordion-item -->

      <# }); #>

    </div><!-- /.tms-accordion -->

    <?php
  }
}
