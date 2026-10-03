<?php
/**
 * Themeasy Elementor Widget: Logo Wall
 *
 * Static wall of brand logos with shared hairline borders — every cell is
 * framed by a single collapsed 1px divider (the modern "table wall" look),
 * with an optional outer frame and per-cell hover treatments (desaturate to
 * color, grayscale, zoom) plus a micro-label that reveals the brand name on
 * hover. Pure CSS — no JS, no carousel; complements the Client Logos widget
 * (which owns the gap-based grid / carousel / marquee skins).
 *
 * @package Themeasy
 * @since 1.0.0
 */

namespace Themeasy\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Control_Media;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Utils;
use Elementor\Widget_Base;
use Themeasy\Core\Entitlement;

/**
 * Responsible for registering controls and rendering the Logo Wall widget.
 */
class LogoWall extends Widget_Base {
  public function get_name() {
    return 'themeasy-logo-wall';
  }

  public function get_title() {
    return esc_html__( 'Logo Wall', 'themeasy-lite' );
  }

  public function get_icon() {
    return 'eicon-gallery-grid';
  }

  public function get_custom_help_url() {
    return 'https://themeasy.co/help-center';
  }

  public function get_categories() {
    return ['themeasy_blocks_category', 'themeasy_content_category'];
  }

  public function get_keywords() {
    return ['themeasy', 'logo', 'wall', 'brands', 'clients', 'partners', 'grid', 'borders'];
  }

  public function get_style_depends() {
    return ['themeasy-shared', 'themeasy-logo-wall'];
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
    // Content section: Logos
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'logo_wall_items_section',
        [
          'label' => esc_html__( 'Logos', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $repeater = new Repeater();

      $repeater->add_control(
        'logo_image',
        [
          'label' => esc_html__( 'Logo', 'themeasy-lite' ),
          'type' => Controls_Manager::MEDIA,
          'default' => [
            'url' => Utils::get_placeholder_image_src(),
          ],
          'dynamic' => ['active' => true],
        ]
      );

      $repeater->add_control(
        'logo_name',
        [
          'label' => esc_html__( 'Name', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'Brand Name', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'Used for alt text and the hover label', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
        ]
      );

      $repeater->add_control(
        'logo_link',
        [
          'label' => esc_html__( 'Link', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::URL,
          'options' => ['url', 'is_external', 'nofollow'],
          'default' => [
            'url' => '',
            'is_external' => true,
            'nofollow' => false,
          ],
          'dynamic' => ['active' => true],
        ]
      );

      $this->add_control(
        'logos',
        [
          'label' => esc_html__( 'Logos', 'themeasy-lite' ),
          'type' => Controls_Manager::REPEATER,
          'fields' => $repeater->get_controls(),
          'title_field' => '{{{ logo_name }}}',
          'default' => [
            ['logo_name' => esc_html__( 'Brand One', 'themeasy-lite' )],
            ['logo_name' => esc_html__( 'Brand Two', 'themeasy-lite' )],
            ['logo_name' => esc_html__( 'Brand Three', 'themeasy-lite' )],
            ['logo_name' => esc_html__( 'Brand Four', 'themeasy-lite' )],
            ['logo_name' => esc_html__( 'Brand Five', 'themeasy-lite' )],
            ['logo_name' => esc_html__( 'Brand Six', 'themeasy-lite' )],
            ['logo_name' => esc_html__( 'Brand Seven', 'themeasy-lite' )],
            ['logo_name' => esc_html__( 'Brand Eight', 'themeasy-lite' )],
          ],
        ]
      );

      $this->add_group_control( // image_resolution
        Group_Control_Image_Size::get_type(),
        [
          'name' => 'image_resolution',
          'separator' => 'before',
          'exclude' => ['custom'],
          'include' => [],
          'default' => 'medium',
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
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Layout
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'logo_wall_layout_section',
        [
          'label' => esc_html__( 'Layout', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $this->add_responsive_control(
        'columns',
        [
          'label' => esc_html__( 'Columns', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['u'],
          'range' => ['u' => ['min' => 1, 'max' => 8, 'step' => 1]],
          'default' => ['unit' => 'u', 'size' => 4],
          'tablet_default' => ['unit' => 'u', 'size' => 3],
          'mobile_default' => ['unit' => 'u', 'size' => 2],
          'selectors' => [
            '{{WRAPPER}} .tms-logo-wall' => '--tms-logo-wall-columns: {{SIZE}};',
          ],
        ]
      );

      $this->add_control(
        'outer_frame',
        [
          'label' => esc_html__( 'Outer Frame', 'themeasy-lite' ),
          'description' => esc_html__( 'Close the wall with an outer border; off keeps inner dividers only.', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Settings
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'logo_wall_settings_section',
        [
          'label' => esc_html__( 'Settings', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $this->add_control(
        'hover_effect',
        [
          'label' => esc_html__( 'Hover Effect', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'color',
          'options' => [
            'none' => esc_html__( 'None', 'themeasy-lite' ),
            'color' => esc_html__( 'Desaturate to Color', 'themeasy-lite' ),
            'grayscale' => esc_html__( 'Color to Grayscale', 'themeasy-lite' ),
            'zoom' => esc_html__( 'Zoom In', 'themeasy-lite' ),
          ],
        ]
      );

      $this->add_control(
        'show_labels',
        [
          'label' => esc_html__( 'Reveal Name on Hover', 'themeasy-lite' ),
          'description' => esc_html__( 'Shows the brand name as a micro-label in the cell corner on hover.', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Show', 'themeasy-lite' ),
          'label_off' => esc_html__( 'Hide', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => '',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Cells
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'logo_wall_cell_styles_section',
        [
          'label' => esc_html__( 'Cells', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->start_controls_tabs( 'cell_states_tabs' );

        $this->start_controls_tab(
          'cell_normal_tab',
          [
            'label' => esc_html__( 'Normal', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'cell_background_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-logo-wall__item' => 'background-color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

        $this->start_controls_tab(
          'cell_hover_tab',
          [
            'label' => esc_html__( 'Hover', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'cell_background_hover_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-logo-wall__item:hover,
                 {{WRAPPER}} .tms-logo-wall__item:focus-within' => 'background-color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

      $this->add_control(
        'cell_dimensions_heading',
        [
          'label' => esc_html__( 'Dimensions', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_responsive_control(
        'cell_min_height',
        [
          'label' => esc_html__( 'Minimum Height', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem'],
          'range' => [
            'px' => ['min' => 60, 'max' => 320, 'step' => 1],
            'rem' => ['min' => 4, 'max' => 20, 'step' => 0.25],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-logo-wall' => '--tms-logo-wall-cell-height: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'cell_padding',
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', 'em'],
          'selectors' => [
            '{{WRAPPER}} .tms-logo-wall__link'
              => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'divider_heading',
        [
          'label' => esc_html__( 'Dividers', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'divider_color',
        [
          'label' => esc_html__( 'Divider Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-logo-wall' => '--tms-logo-wall-border-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'divider_style',
        [
          'label' => esc_html__( 'Divider Style', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'solid',
          'options' => [
            'solid' => esc_html__( 'Solid', 'themeasy-lite' ),
            'dashed' => esc_html__( 'Dashed', 'themeasy-lite' ),
            'dotted' => esc_html__( 'Dotted', 'themeasy-lite' ),
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-logo-wall' => '--tms-logo-wall-border-style: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'divider_width',
        [
          'label' => esc_html__( 'Divider Width', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => ['px' => ['min' => 1, 'max' => 6, 'step' => 1]],
          'selectors' => [
            '{{WRAPPER}} .tms-logo-wall' => '--tms-logo-wall-border-width: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'frame_border_radius',
        [
          'label' => esc_html__( 'Frame Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-logo-wall'
              => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
          'condition' => [
            'outer_frame' => 'yes',
          ],
        ]
      );

      $this->add_control(
        'cell_transition_duration',
        [
          'label' => esc_html__( 'Transition Duration', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['s'],
          'range' => ['s' => ['min' => 0.1, 'max' => 2, 'step' => 0.05]],
          'default' => ['unit' => 's', 'size' => 0.35],
          'separator' => 'before',
          'selectors' => [
            '{{WRAPPER}} .tms-logo-wall' => '--tms-logo-wall-duration: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Logo Image
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'logo_wall_image_styles_section',
        [
          'label' => esc_html__( 'Logo Image', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_responsive_control(
        'image_max_width',
        [
          'label' => esc_html__( 'Max Width', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', '%', 'rem'],
          'range' => [
            'px' => ['min' => 40, 'max' => 400, 'step' => 1],
            '%' => ['min' => 10, 'max' => 100, 'step' => 1],
            'rem' => ['min' => 2, 'max' => 24, 'step' => 0.5],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-logo-wall__image' => 'max-width: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'image_max_height',
        [
          'label' => esc_html__( 'Max Height', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem'],
          'range' => [
            'px' => ['min' => 20, 'max' => 200, 'step' => 1],
            'rem' => ['min' => 1, 'max' => 12, 'step' => 0.25],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-logo-wall__image' => 'max-height: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->start_controls_tabs( 'image_states_tabs' );

        $this->start_controls_tab(
          'image_normal_tab',
          [
            'label' => esc_html__( 'Normal', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'image_opacity',
            [
              'label' => esc_html__( 'Opacity', 'themeasy-lite' ),
              'type' => Controls_Manager::SLIDER,
              'size_units' => ['u'],
              'range' => ['u' => ['min' => 0, 'max' => 1, 'step' => 0.05]],
              // Naming the unit is what makes the 0-1 range reachable; the size stays
              // empty because the resting opacity belongs to the Hover Effect mode
              // (0.55 under "Color", 1 under "Grayscale") and a seeded value would
              // override both.
              'default' => ['unit' => 'u', 'size' => ''],
              'selectors' => [
                '{{WRAPPER}} .tms-logo-wall__item .tms-logo-wall__image' => 'opacity: {{SIZE}};',
              ],
            ]
          );

        $this->end_controls_tab();

        $this->start_controls_tab(
          'image_hover_tab',
          [
            'label' => esc_html__( 'Hover', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'image_opacity_hover',
            [
              'label' => esc_html__( 'Opacity', 'themeasy-lite' ),
              'type' => Controls_Manager::SLIDER,
              'size_units' => ['u'],
              'range' => ['u' => ['min' => 0, 'max' => 1, 'step' => 0.05]],
              // Naming the unit is what makes the 0-1 range reachable; the size stays
              // empty because the resting opacity belongs to the Hover Effect mode
              // (0.55 under "Color", 1 under "Grayscale") and a seeded value would
              // override both.
              'default' => ['unit' => 'u', 'size' => ''],
              'selectors' => [
                '{{WRAPPER}} .tms-logo-wall__item:hover .tms-logo-wall__image,
                 {{WRAPPER}} .tms-logo-wall__item:focus-within .tms-logo-wall__image' => 'opacity: {{SIZE}};',
              ],
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Hover Label
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'logo_wall_label_styles_section',
        [
          'label' => esc_html__( 'Hover Label', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'show_labels' => 'yes',
          ],
        ]
      );

      $this->add_control(
        'label_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-logo-wall__label' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control( // label_typography
        Group_Control_Typography::get_type(),
        [
          'name' => 'label_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-logo-wall__label',
        ]
      );

      $this->add_responsive_control(
        'label_offset',
        [
          'label' => esc_html__( 'Corner Offset', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem'],
          'range' => [
            'px' => ['min' => 4, 'max' => 40, 'step' => 1],
            'rem' => ['min' => 0.25, 'max' => 3, 'step' => 0.125],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-logo-wall' => '--tms-logo-wall-label-offset: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Wordmark
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'logo_wall_wordmark_styles_section',
        [
          'label' => esc_html__( 'Wordmark', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      $this->add_control(
        'wordmark_note',
        [
          'label' => esc_html__( 'Wordmark', 'themeasy-lite' ),
          'type' => Controls_Manager::RAW_HTML,
          'raw' => esc_html__( 'Applies to cells that have a name but no logo image: the name is painted as the cell content instead of the image.', 'themeasy-lite' ),
          'content_classes' => 'elementor-control-field-description',
        ]
      );

      $this->add_control(
        'wordmark_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-logo-wall__wordmark' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control( // wordmark_typography
        Group_Control_Typography::get_type(),
        [
          'name' => 'wordmark_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-logo-wall__wordmark',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Advanced sections: Themeasy Motion (Pro — the engines run on GSAP, which
    // the Free build neither ships nor loads).
    // ------------------------------------------------------------------------
    if ( Entitlement::can_use_widgets() ) {
      $this->register_motion_controls();
    } else {
      themeasy_register_motion_upsell_section( $this );
    }
  }

  /**
   * Register the Themeasy Motion sections (Advanced tab).
   *
   * Pro only: the motion engines run on GSAP, which the Free build neither
   * ships nor loads (backlog #253). register_controls() calls this when the
   * site is entitled and registers the upsell section otherwise.
   *
   * @return void
   */
  private function register_motion_controls() {
    // ------------------------------------------------------------------------
    // Advanced section: Entrance Animation
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'widget_animation_section',
        [
          'label' => esc_html__( 'Motion — Animation', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_ADVANCED,
        ]
      );

      $this->add_control(
        'widget_animation',
        [
          'label' => esc_html__( 'Entrance Animation', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => '',
          'options' => themeasy_get_animation_options( 'block' ),
        ]
      );

      $this->add_control(
        'widget_animation_duration',
        [
          'label' => esc_html__( 'Duration', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['s'],
          'range' => ['s' => ['min' => 0, 'max' => 5, 'step' => 0.1]],
          'default' => ['unit' => 's', 'size' => 1],
          'condition' => ['widget_animation!' => ''],
        ]
      );

      $this->add_control(
        'widget_animation_delay',
        [
          'label' => esc_html__( 'Delay', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['s'],
          'range' => ['s' => ['min' => 0, 'max' => 5, 'step' => 0.1]],
          'default' => ['unit' => 's', 'size' => 0],
          'condition' => ['widget_animation!' => ''],
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

    // Themeasy Motion is Pro (GSAP — backlog #253). Without it the motion
    // settings read as empty, so saved values (a template, a site back from
    // premium) emit no markup: the hidden guard would leave the widget
    // invisible with no engine to reveal it.
    $motion = Entitlement::can_use_widgets();
    $logos = $settings['logos'] ?? [];

    // Early return if no logos.
    if ( empty( $logos ) ) {
      return;
    }

    // ------------------------------------------------------------------------
    // Settings.
    // ------------------------------------------------------------------------
    $outer_frame = ( $settings['outer_frame'] ?? 'yes' ) === 'yes';
    $show_labels = ( $settings['show_labels'] ?? '' ) === 'yes';
    $hover_effect = $settings['hover_effect'] ?? 'color';
    $hover_effect = in_array( $hover_effect, ['none', 'color', 'grayscale', 'zoom'], true ) ? $hover_effect : 'color';

    // Group_Control_Image_Size names its size key `{name}_size` (name = image_resolution).
    $image_size = $settings['image_resolution_size'] ?? 'medium';
    $image_prio = $settings['image_loading_priority'] ?? 'lazy';

    // ------------------------------------------------------------------------
    // Wrapper classes + attributes.
    // ------------------------------------------------------------------------
    $wrapper_classes = ['tms-logo-wall', 'tms-logo-wall--hover-' . $hover_effect];
    $wrapper_atts = [];

    if ( $outer_frame ) {
      $wrapper_classes[] = 'tms-logo-wall--framed';
    }

    if ( $show_labels ) {
      $wrapper_classes[] = 'tms-logo-wall--labels';
    }

    // Animation.
    $animation = $motion ? ( $settings['widget_animation'] ?? '' ) : '';

    if ( $animation ) {
      $wrapper_classes[] = 'tms-block-animation';
      $wrapper_classes[] = 'tms-animation--on-view';
      $wrapper_classes[] = 'tms-animation--hidden';
      $wrapper_atts['tms-block-animation'] = $animation;
      $wrapper_atts['duration'] = $settings['widget_animation_duration']['size'] ?? '1';
      $wrapper_atts['delay'] = $settings['widget_animation_delay']['size'] ?? '0';
    }

    $wrapper_classes_output = implode( ' ', array_filter( $wrapper_classes ) );
    $wrapper_atts_output = themeasy_html_attributes( $wrapper_atts );

    // ------------------------------------------------------------------------
    // Output.
    // ------------------------------------------------------------------------
    ?>
    <div class="<?php echo esc_attr( $wrapper_classes_output ); ?>"<?php echo $wrapper_atts_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>>

      <ul class="tms-logo-wall__list" role="list">

        <?php foreach ( $logos as $index => $logo ) : ?>
          <?php
          $logo_image = $logo['logo_image'] ?? [];
          $logo_id = absint( $logo_image['id'] ?? 0 );
          $logo_url = $logo_image['url'] ?? '';

          // The MEDIA control defaults to Elementor's placeholder image, so an
          // untouched repeater row is indistinguishable from a chosen one by
          // value alone. That default is a legitimate drop target in the editor
          // (content_template() still shows it), but on the front it is a grey
          // box posing as a client's brand — so it counts as "no image" here,
          // exactly as in the sibling client-logos.php.
          if ( $logo_url === Utils::get_placeholder_image_src() ) {
            $logo_url = '';
            $logo_id = 0;
          }

          $logo_name = trim( (string) ( $logo['logo_name'] ?? '' ) );

          // Text-only (wordmark) mode is derived, never authored: a row with a
          // name and no image paints the name as the cell content, so a wall of
          // typographic wordmarks needs no brand assets. A row with neither
          // renders nothing — there is nothing to show.
          $is_wordmark = $logo_url === '';

          if ( $is_wordmark && $logo_name === '' ) {
            continue;
          }

          $logo_link = $logo['logo_link'] ?? [];
          $alt_text = $logo_name !== '' ? $logo_name : Control_Media::get_image_alt( $logo_image );
          $has_link = !empty( $logo_link['url'] );

          $item_classes = [
            'tms-logo-wall__item',
            'elementor-repeater-item-' . ( $logo['_id'] ?? '' ),
          ];

          if ( $is_wordmark ) {
            $item_classes[] = 'tms-logo-wall__item--wordmark';
          }

          $item_classes_output = implode( ' ', array_filter( $item_classes ) );

          $link_key = 'logo_link_' . $index;

          if ( $has_link ) {
            $this->add_link_attributes( $link_key, $logo_link );
            themeasy_add_external_link_rel( $this, $link_key, $logo_link );
            $this->add_render_attribute( $link_key, 'class', 'tms-logo-wall__link' );

            if ( $logo_name !== '' ) {
              $this->add_render_attribute( $link_key, 'aria-label', $logo_name );
            }
          }

          // Inline-editing wiring for the name. The two roles are exclusive: as
          // a wordmark the name IS the cell content, so it must stay in the
          // accessibility tree; as the hover micro-label it duplicates the
          // image's alt/aria-label and is hidden from it.
          $name_key = $this->get_repeater_setting_key( 'logo_name', 'logos', $index );

          if ( $is_wordmark ) {
            $this->add_render_attribute( $name_key, 'class', 'tms-logo-wall__wordmark' );
          } else {
            $this->add_render_attribute( $name_key, 'class', 'tms-logo-wall__label' );
            $this->add_render_attribute( $name_key, 'aria-hidden', 'true' );
          }

          $this->add_inline_editing_attributes( $name_key, 'none' );
          ?>

          <li class="<?php echo esc_attr( $item_classes_output ); ?>">

            <?php if ( $has_link ) : ?>
              <a <?php $this->print_render_attribute_string( $link_key ); ?>>
            <?php else : ?>
              <span class="tms-logo-wall__link tms-logo-wall__link--static">
            <?php endif; ?>

              <?php if ( $is_wordmark ) : ?>
                <span <?php $this->print_render_attribute_string( $name_key ); ?>>
                  <?php echo esc_html( $logo_name ); ?>
                </span>
              <?php else : ?>
                <?php
                themeasy_render_attachment_image(
                  $logo_id,
                  $image_size,
                  $logo_url,
                  $alt_text,
                  'tms-logo-wall__image',
                  [],
                  $image_prio
                );
                ?>

                <?php if ( $show_labels && $logo_name !== '' ) : ?>
                  <span <?php $this->print_render_attribute_string( $name_key ); ?>>
                    <?php echo esc_html( $logo_name ); ?>
                  </span>
                <?php endif; ?>
              <?php endif; ?>

            <?php if ( $has_link ) : ?>
              </a>
            <?php else : ?>
              </span>
            <?php endif; ?>

          </li>
        <?php endforeach; ?>

      </ul>

    </div><!-- /.tms-logo-wall -->
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
      // Themeasy Motion is Pro (render() parity): without it the motion
      // settings read as empty.
      var motion = <?php echo Entitlement::can_use_widgets() ? 'true' : 'false'; ?>;

      // ------------------------------------------------------------------------
      // Settings.
      // ------------------------------------------------------------------------
      var logos = settings.logos || [];

      if ( ! logos.length ) {
        return;
      }

      var outerFrame = settings.outer_frame === 'yes';
      var showLabels = settings.show_labels === 'yes';
      var hoverEffect = [ 'none', 'color', 'grayscale', 'zoom' ].indexOf( settings.hover_effect ) !== -1
        ? settings.hover_effect
        : 'color';

      // Scheme guard for every href/src below. The regex lives in exactly one
      // place; the fallback fails closed rather than duplicating it (a bare ^
      // anchor misses the C0 controls browsers strip before resolving a scheme).
      var safeUrl = ( window.Themeasy && window.Themeasy.safeUrl )
        ? window.Themeasy.safeUrl
        : function () { return ''; };

      // Build the <a> attribute string for a link, mirroring add_link_attributes().
      var linkAttrs = function( link, name ) {
        var atts = { 'href': safeUrl( link.url || '' ), 'class': 'tms-logo-wall__link' };

        if ( link.is_external ) {
          atts.target = '_blank';
        }

        if ( link.nofollow ) {
          atts.rel = 'nofollow';
        }

        if ( name ) {
          atts['aria-label'] = name;
        }

        return Themeasy.htmlAttributes( atts );
      };

      // Register inline-editing attributes for the name and return the attribute
      // string (mirrors render()'s wiring). Wordmark and hover micro-label are
      // exclusive roles over the same repeater key.
      var nameAttrs = function( index, wordmark ) {
        var key = view.getRepeaterSettingKey( 'logo_name', 'logos', index );

        if ( wordmark ) {
          view.addRenderAttribute( key, 'class', 'tms-logo-wall__wordmark' );
        } else {
          view.addRenderAttribute( key, 'class', 'tms-logo-wall__label' );
          view.addRenderAttribute( key, 'aria-hidden', 'true' );
        }

        view.addInlineEditingAttributes( key, 'none' );
        return view.getRenderAttributeString( key );
      };

      // ------------------------------------------------------------------------
      // Wrapper classes + attributes.
      // ------------------------------------------------------------------------
      var wrapperClasses = [ 'tms-logo-wall', 'tms-logo-wall--hover-' + hoverEffect ];
      var wrapperAtts    = {};

      if ( outerFrame ) {
        wrapperClasses.push( 'tms-logo-wall--framed' );
      }

      if ( showLabels ) {
        wrapperClasses.push( 'tms-logo-wall--labels' );
      }

      // Animation.
      var animation = motion ? ( settings.widget_animation || '' ) : '';

      if ( animation ) {
        wrapperClasses.push( 'tms-block-animation', 'tms-animation--on-view', 'tms-animation--hidden' );
        wrapperAtts['tms-block-animation'] = animation;
        wrapperAtts['duration'] = ( settings.widget_animation_duration && settings.widget_animation_duration.size )
          ? settings.widget_animation_duration.size
          : '1';
        wrapperAtts['delay'] = ( settings.widget_animation_delay && settings.widget_animation_delay.size )
          ? settings.widget_animation_delay.size
          : '0';
      }

      var wrapperClassStr = wrapperClasses.filter( Boolean ).join( ' ' );
    #>

    <div class="{{ wrapperClassStr }}" {{{ Themeasy.htmlAttributes( wrapperAtts ) }}}>

      <ul class="tms-logo-wall__list" role="list">

        <# _.each( logos, function( logo, index ) {
          var logoImage = logo.logo_image || {};
          var logoUrl   = safeUrl( logoImage.url || '' );
          var logoName  = ( logo.logo_name || '' ).toString().trim();

          // Text-only (wordmark) mode, mirroring render(): no usable image
          // (unset, or a scheme the guard blocked) plus a name paints the name
          // as the cell content; neither renders nothing.
          var isWordmark = ! logoUrl;

          if ( isWordmark && ! logoName ) {
            return;
          }

          var logoLink = logo.logo_link || {};
          var hasLink  = !! safeUrl( logoLink.url || '' );
          var altText  = logoName || ( logoImage.alt || '' );
          var itemClass = 'tms-logo-wall__item'
            + ( isWordmark ? ' tms-logo-wall__item--wordmark' : '' )
            + ' elementor-repeater-item-' + ( logo._id || '' );
        #>

          <li class="{{ itemClass }}">

            <# if ( hasLink ) { #>
              <a {{{ linkAttrs( logoLink, logoName ) }}}>
            <# } else { #>
              <span class="tms-logo-wall__link tms-logo-wall__link--static">
            <# } #>

              <# if ( isWordmark ) { #>
                <span {{{ nameAttrs( index, true ) }}}>{{ logoName }}</span>
              <# } else { #>
                <img class="tms-logo-wall__image"
                  src="{{ logoUrl }}"
                  alt="{{ altText }}"
                  loading="lazy"
                  decoding="async" />

                <# if ( showLabels && logoName ) { #>
                  <span {{{ nameAttrs( index, false ) }}}>{{ logoName }}</span>
                <# } #>
              <# } #>

            <# if ( hasLink ) { #>
              </a>
            <# } else { #>
              </span>
            <# } #>

          </li>

        <# }); #>

      </ul>

    </div><!-- /.tms-logo-wall -->
    <?php
  }
}
