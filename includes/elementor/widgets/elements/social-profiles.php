<?php
/**
 * Themeasy Elementor Widget: Social Profiles
 *
 * Renders a row or column of social network icons with built-in accessibility
 * (aria-label per item, rel=noopener for external links) and three visual
 * variants: filled, outline, and minimal. Styling ships in the shared widget
 * bundle (handle themeasy-bundle); the widget itself owns no CSS/JS folder.
 *
 * @package Themeasy
 * @since 1.0.0
 */

namespace Themeasy\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Repeater;
use Elementor\Widget_Base;

/**
 * Social Profiles widget — renders a list of social network icons with shared shape and variant.
 */
class SocialProfiles extends Widget_Base {
  public function get_name() {
    return 'themeasy-social-profiles';
  }

  public function get_title() {
    return esc_html__( 'Social Profiles', 'themeasy-lite' );
  }

  public function get_icon() {
    return 'eicon-social-icons';
  }

  public function get_custom_help_url() {
    return 'https://themeasy.co/help-center';
  }

  public function get_categories() {
    return ['themeasy_elements_category', 'themeasy_content_category'];
  }

  public function get_keywords() {
    return ['themeasy', 'social', 'profiles', 'icons', 'follow', 'share', 'network'];
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
    // Content section: Profiles
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'social_profiles_content_section',
        [
          'label' => esc_html__( 'Profiles', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
    );

      $repeater = new Repeater();

      $repeater->add_control(
        'icon',
        [
          'label' => esc_html__( 'Icon', 'themeasy-lite' ),
          'type' => Controls_Manager::ICONS,
          'default' => [
            'value' => 'ty-feather-instagram',
            'library' => 'ty-feather',
          ],
        ]
      );

      $repeater->add_control(
        'url',
        [
          'label' => esc_html__( 'URL', 'themeasy-lite' ),
          'type' => Controls_Manager::URL,
          'placeholder' => esc_html__( 'https://your-link.com', 'themeasy-lite' ),
          'options' => ['url', 'is_external', 'nofollow'],
          'default' => [
            'url' => '',
            'is_external' => true,
            'nofollow' => true,
          ],
        ]
      );

      $repeater->add_control(
        'label',
        [
          'label' => esc_html__( 'Accessible Label', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'placeholder' => esc_html__( 'e.g. Follow us on Instagram', 'themeasy-lite' ),
          'description' => esc_html__( 'Used as aria-label for screen readers. Leave blank to auto-generate.', 'themeasy-lite' ),
        ]
      );

      $repeater->add_control(
        'item_color',
        [
          'label' => esc_html__( 'Custom Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'description' => esc_html__( 'Optional brand color override (e.g. #E1306C for Instagram).', 'themeasy-lite' ),
          'selectors' => [
            '{{WRAPPER}} .tms-social-profiles__item{{CURRENT_ITEM}}' => '--tms-social-item-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'profiles',
        [
          'label' => esc_html__( 'Profiles', 'themeasy-lite' ),
          'type' => Controls_Manager::REPEATER,
          'fields' => $repeater->get_controls(),
          'default' => [
            [
              'icon' => ['value' => 'ty-feather-instagram', 'library' => 'ty-feather'],
              'url' => ['url' => '#your-link', 'is_external' => true, 'nofollow' => false],
              'label' => esc_html__( 'Instagram', 'themeasy-lite' ),
            ],
            [
              'icon' => ['value' => 'ty-feather-x-twitter', 'library' => 'ty-feather'],
              'url' => ['url' => '#your-link', 'is_external' => true, 'nofollow' => false],
              'label' => esc_html__( 'X', 'themeasy-lite' ),
            ],
            [
              'icon' => ['value' => 'ty-feather-linkedin', 'library' => 'ty-feather'],
              'url' => ['url' => '#your-link', 'is_external' => true, 'nofollow' => false],
              'label' => esc_html__( 'LinkedIn', 'themeasy-lite' ),
            ],
          ],
          'title_field' => '{{{ label || "Profile" }}}',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Layout
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'social_profiles_layout_section',
        [
          'label' => esc_html__( 'Layout', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
    );

      $this->add_responsive_control(
        'direction',
        [
          'label' => esc_html__( 'Direction', 'themeasy-lite' ),
          'type' => Controls_Manager::CHOOSE,
          'options' => [
            'horizontal' => [
              'title' => esc_html__( 'Horizontal', 'themeasy-lite' ),
              'icon' => 'eicon-ellipsis-h',
            ],
            'vertical' => [
              'title' => esc_html__( 'Vertical', 'themeasy-lite' ),
              'icon' => 'eicon-ellipsis-v',
            ],
          ],
          'default' => 'horizontal',
          'toggle' => false,
          'selectors_dictionary' => [
            'horizontal' => 'row',
            'vertical' => 'column',
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-social-profiles' => 'flex-direction: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'align',
        [
          'label' => esc_html__( 'Alignment', 'themeasy-lite' ),
          'type' => Controls_Manager::CHOOSE,
          'options' => [
            'flex-start' => [
              'title' => esc_html__( 'Start', 'themeasy-lite' ),
              'icon' => 'eicon-h-align-left',
            ],
            'center' => [
              'title' => esc_html__( 'Center', 'themeasy-lite' ),
              'icon' => 'eicon-h-align-center',
            ],
            'flex-end' => [
              'title' => esc_html__( 'End', 'themeasy-lite' ),
              'icon' => 'eicon-h-align-right',
            ],
          ],
          'default' => 'flex-start',
          'toggle' => true,
          'selectors' => [
            '{{WRAPPER}} .tms-social-profiles' => 'justify-content: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'items_gap',
        [
          'label' => esc_html__( 'Gap', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem', 'em'],
          'range' => [
            'px' => ['min' => 0, 'max' => 60, 'step' => 1],
            'rem' => ['min' => 0, 'max' => 4, 'step' => 0.1],
          ],
          'default' => ['unit' => 'px', 'size' => 8],
          'selectors' => [
            '{{WRAPPER}} .tms-social-profiles' => 'gap: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Settings
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'social_profiles_settings_section',
        [
          'label' => esc_html__( 'Settings', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
    );

      $this->add_control(
        'shape',
        [
          'label' => esc_html__( 'Shape', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'circle',
          'options' => [
            'square' => esc_html__( 'Square', 'themeasy-lite' ),
            'rounded' => esc_html__( 'Rounded', 'themeasy-lite' ),
            'circle' => esc_html__( 'Circle', 'themeasy-lite' ),
            'none' => esc_html__( 'No Background', 'themeasy-lite' ),
          ],
        ]
      );

      $this->add_control(
        'variant',
        [
          'label' => esc_html__( 'Variant', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'filled',
          'options' => [
            'filled' => esc_html__( 'Filled', 'themeasy-lite' ),
            'outline' => esc_html__( 'Outline', 'themeasy-lite' ),
            'minimal' => esc_html__( 'Minimal', 'themeasy-lite' ),
          ],
        ]
      );

      $this->add_control(
        'hover_effect',
        [
          'label' => esc_html__( 'Hover Effect', 'themeasy-lite' ),
          'description' => esc_html__( 'Motion applied to each icon on hover.', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => '',
          'options' => [
            '' => esc_html__( 'None', 'themeasy-lite' ),
            'scale' => esc_html__( 'Scale', 'themeasy-lite' ),
            'lift' => esc_html__( 'Lift', 'themeasy-lite' ),
            'rotate' => esc_html__( 'Rotate', 'themeasy-lite' ),
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Items
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'social_profiles_items_section',
        [
          'label' => esc_html__( 'Items', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      // Background color (normal / hover).
      $this->add_control(
        'item_background_heading',
        [
          'label' => esc_html__( 'Background', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->start_controls_tabs( 'item_background_tabs' );

        $this->start_controls_tab(
          'item_background_normal_tab',
          ['label' => esc_html__( 'Normal', 'themeasy-lite' )]
        );

          $this->add_control(
            'background_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-social-profiles__item' => 'background-color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

        $this->start_controls_tab(
          'item_background_hover_tab',
          ['label' => esc_html__( 'Hover', 'themeasy-lite' )]
        );

          $this->add_control(
            'background_hover_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-social-profiles__item:hover' => 'background-color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

      // Dimensions. The node's box is the counterpart to the Icon Size control
      // in the Icon section: leaving it empty keeps the historical behaviour
      // (the node tracks the glyph at 2.5em), while an authored px value pins
      // the node so both can be set precisely and independently.
      $this->add_responsive_control(
        'icon_node_size',
        [
          'label' => esc_html__( 'Node Size', 'themeasy-lite' ),
          'description' => esc_html__( 'Size of the shape behind the icon. Leave empty to track the Icon Size at 2.5x the glyph.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'separator' => 'before',
          'size_units' => ['px'],
          'range' => [
            'px' => ['min' => 16, 'max' => 120, 'step' => 1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-social-profiles' => '--tms-social-node-size: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'shape!' => 'none',
            'variant!' => 'minimal',
          ],
        ]
      );

      // Spacing. box-sizing is border-box project-wide, so padding cannot grow
      // a node that has a size -- it only sizes the two shapes that opt out of
      // the node box ("No Background" and the Minimal variant).
      $this->add_responsive_control(
        'item_padding',
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'description' => esc_html__( 'Sizes the item for the No Background shape and the Minimal variant. Every other shape is sized by Node Size.', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', 'em'],
          'separator' => 'before',
          'selectors' => [
            '{{WRAPPER}} .tms-social-profiles__item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      // Border — type/width from the group control, color tabbed below so it stays
      // reachable when the border comes from the variant CSS (Outline) instead of
      // an explicit border type.
      $this->add_group_control(
        Group_Control_Border::get_type(),
        [
          'name' => 'item_border',
          'separator' => 'before',
          'exclude' => ['color'],
          'selector' => '{{WRAPPER}} .tms-social-profiles__item',
        ]
      );

      $this->start_controls_tabs( 'item_border_color_tabs' );

        $this->start_controls_tab(
          'item_border_color_normal_tab',
          ['label' => esc_html__( 'Normal', 'themeasy-lite' )]
        );

          $this->add_control(
            'item_border_color',
            [
              'label' => esc_html__( 'Border Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-social-profiles__item' => 'border-color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

        $this->start_controls_tab(
          'item_border_color_hover_tab',
          ['label' => esc_html__( 'Hover', 'themeasy-lite' )]
        );

          $this->add_control(
            'border_hover_color',
            [
              'label' => esc_html__( 'Border Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-social-profiles__item:hover' => 'border-color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

      $this->add_responsive_control(
        'item_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', '%'],
          'selectors' => [
            '{{WRAPPER}} .tms-social-profiles__item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
          'condition' => ['shape!' => 'circle'],
        ]
      );

      // Box shadow.
      $this->add_group_control(
        Group_Control_Box_Shadow::get_type(),
        [
          'name' => 'item_box_shadow',
          'label' => esc_html__( 'Box Shadow', 'themeasy-lite' ),
          'separator' => 'before',
          'selector' => '{{WRAPPER}} .tms-social-profiles__item',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Icon
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'social_profiles_icon_section',
        [
          'label' => esc_html__( 'Icon', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      // Color (normal / hover).
      $this->start_controls_tabs( 'icon_color_tabs' );

        $this->start_controls_tab(
          'icon_color_normal_tab',
          ['label' => esc_html__( 'Normal', 'themeasy-lite' )]
        );

          $this->add_control(
            'icon_color',
            [
              'label' => esc_html__( 'Icon Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-social-profiles__item,
                 {{WRAPPER}} .tms-social-profiles__item svg' => 'color: {{VALUE}};',
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
                '{{WRAPPER}} .tms-social-profiles__item svg' => 'fill: {{VALUE}};',
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
                '{{WRAPPER}} .tms-social-profiles__item svg' => 'stroke: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

        $this->start_controls_tab(
          'icon_color_hover_tab',
          ['label' => esc_html__( 'Hover', 'themeasy-lite' )]
        );

          $this->add_control(
            'icon_hover_color',
            [
              'label' => esc_html__( 'Icon Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-social-profiles__item:hover,
                 {{WRAPPER}} .tms-social-profiles__item:hover svg' => 'color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

      // Size. Icon contract: the glyph (svg / i / img) is 1em, so font-size on
      // the item is the single sizing knob -- never a second width/height rule
      // on the svg, which would double-resolve a rem/em value against the
      // item's own font-size and desync the glyph from the node.
      $this->add_responsive_control(
        'item_size',
        [
          'label' => esc_html__( 'Icon Size', 'themeasy-lite' ),
          'description' => esc_html__( 'Glyph size. The shape behind it keeps its own size -- see Node Size under Items.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem', 'em'],
          'separator' => 'before',
          'range' => [
            'px' => ['min' => 8, 'max' => 60, 'step' => 1],
            'rem' => ['min' => 0.5, 'max' => 4, 'step' => 0.1],
            'em' => ['min' => 0.5, 'max' => 4, 'step' => 0.1],
          ],
          'default' => ['unit' => 'px', 'size' => 16],
          'selectors' => [
            '{{WRAPPER}} .tms-social-profiles__item' => 'font-size: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'item_thickness',
        [
          'label' => esc_html__( 'Icon Thickness', 'themeasy-lite' ),
          'description' => esc_html__( 'Stroke width for outline (Feather) icons.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => ['min' => 0.5, 'max' => 10, 'step' => 0.5],
          ],
          'default' => ['unit' => 'px', 'size' => 2],
          'selectors' => [
            '{{WRAPPER}} .tms-social-profiles__item svg,
             {{WRAPPER}} .tms-social-profiles__item svg *' => 'stroke-width: {{SIZE}}{{UNIT}};',
          ],
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
    $profiles = (array) ( $settings['profiles'] ?? [] );
    $shape = $settings['shape'] ?? 'circle';
    $variant = $settings['variant'] ?? 'filled';
    $hover_effect = $settings['hover_effect'] ?? '';

    // Early return if no profiles.
    if ( empty( $profiles ) ) {
      return;
    }

    // ------------------------------------------------------------------------
    // Wrapper classes.
    // ------------------------------------------------------------------------
    $list_classes = [
      'tms-social-profiles',
      'tms-social-profiles--' . $shape,
      'tms-social-profiles--' . $variant,
    ];

    if ( $hover_effect ) {
      $list_classes[] = 'tms-social-profiles--hover-' . $hover_effect;
    }

    $list_classes_output = implode( ' ', array_filter( $list_classes ) );

    // ------------------------------------------------------------------------
    // Output.
    // ------------------------------------------------------------------------
    ?>
    <ul class="<?php echo esc_attr( $list_classes_output ); ?>">

      <?php foreach ( $profiles as $index => $profile ) : ?>
        <?php
        $url = esc_url( $profile['url']['url'] ?? '' );
        $label = $profile['label'] ?? '';
        $item_id = sanitize_html_class( $profile['_id'] ?? '' );
        $link_key = 'profile_link_' . $index;

        // Auto-generate the aria-label from the icon value if not provided.
        // Each picker stores a different shape -- ty-* libraries a class-shaped
        // value ("ty-feather-instagram"), themeasy-svg an optional "library/name"
        // pair, font icons a prefixed class -- so normalize all three down to the
        // bare icon name before title-casing it. A Media Library icon stores an
        // array, which the is_string() guard skips.
        if ( empty( $label ) ) {
          $icon_library = $profile['icon']['library'] ?? '';
          $icon_value = $profile['icon']['value'] ?? '';

          if ( is_string( $icon_library ) && is_string( $icon_value ) && '' !== $icon_value ) {
            if ( 0 === strpos( $icon_library, 'ty-' ) ) {
              $icon_value = themeasy_parse_ty_icon_name( $icon_library, $icon_value );
            }

            $slash = strrpos( $icon_value, '/' );
            if ( false !== $slash ) {
              $icon_value = substr( $icon_value, $slash + 1 );
            }

            $label = trim( str_replace( ['fab fa-', 'fa-brands fa-', 'fa-'], '', $icon_value ) );
            $label = ucwords( str_replace( '-', ' ', $label ) );
          }
        }

        $label = sanitize_text_field( $label );

        $item_classes = ['tms-social-profiles__item'];

        if ( $item_id ) {
          $item_classes[] = 'elementor-repeater-item-' . $item_id;
        }

        $this->add_render_attribute( $link_key, 'class', $item_classes );

        if ( $url ) {
          $link = $profile['url'];
          $link['url'] = $url;
          $this->add_link_attributes( $link_key, $link );
          themeasy_add_external_link_rel( $this, $link_key, $link );
        }

        if ( $label ) {
          $this->add_render_attribute( $link_key, 'aria-label', $label );
        }

        $icon_html = themeasy_render_icon_html(
          is_array( $profile['icon'] ?? null ) ? $profile['icon'] : [],
          ['class' => 'tms-social-profiles__icon', 'aria-hidden' => 'true']
        );
        ?>

        <li class="tms-social-profiles__list-item">
          <?php if ( $url ) : ?>
            <a <?php $this->print_render_attribute_string( $link_key ); ?>>
              <?php echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>
            </a>
          <?php else : ?>
            <span <?php $this->print_render_attribute_string( $link_key ); ?>>
              <?php echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>
            </span>
          <?php endif; ?>
        </li>

      <?php endforeach; ?>

    </ul><!-- /.tms-social-profiles -->
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
      // Escaping helper. The template has no esc_url() to fall back on, so
      // every href built from a setting goes through safeUrl — it returns ''
      // for an unsafe scheme, which the markup below treats as "no link".
      // Never re-inline the guard (see includes/elementor/CLAUDE.md).
      // ------------------------------------------------------------------------
      var safeUrl = ( window.Themeasy && window.Themeasy.safeUrl )
        ? window.Themeasy.safeUrl
        : function () { return ''; };

      // ------------------------------------------------------------------------
      // Settings.
      // ------------------------------------------------------------------------
      var profiles    = settings.profiles || [];
      var shape       = settings.shape || 'circle';
      var variant     = settings.variant || 'filled';
      var hoverEffect = settings.hover_effect || '';

      if ( ! profiles.length ) {
        return;
      }

      // ------------------------------------------------------------------------
      // Wrapper classes.
      // ------------------------------------------------------------------------
      var listClasses = [
        'tms-social-profiles',
        'tms-social-profiles--' + shape,
        'tms-social-profiles--' + variant
      ];

      if ( hoverEffect ) {
        listClasses.push( 'tms-social-profiles--hover-' + hoverEffect );
      }

      var listClassStr = listClasses.filter( Boolean ).join( ' ' );
    #>

    <ul class="{{ listClassStr }}">

      <# _.each( profiles, function( profile, index ) {
        var url    = safeUrl( ( profile.url && profile.url.url ) || '' );
        var label  = profile.label || '';
        var itemId = profile._id || '';

        // Mirror of the PHP label fallback: normalize the ty-* class-shaped
        // value and the themeasy-svg "library/name" pair down to the bare icon
        // name before title-casing it.
        if ( ! label ) {
          var iconLib   = ( profile.icon && profile.icon.library ) || '';
          var iconValue = ( profile.icon && profile.icon.value ) || '';

          if ( typeof iconLib === 'string' && typeof iconValue === 'string' && iconValue ) {
            if ( iconLib.indexOf( 'ty-' ) === 0 && window.Themeasy && window.Themeasy.parseTyIconName ) {
              iconValue = window.Themeasy.parseTyIconName( iconLib, iconValue ) || iconValue;
            }

            iconValue = iconValue.slice( iconValue.lastIndexOf( '/' ) + 1 );

            label = iconValue.replace( /fab fa-|fa-brands fa-|fa-/g, '' ).replace( /-/g, ' ' );
            label = label.replace( /\b\w/g, function( c ) { return c.toUpperCase(); } );
          }
        }

        var itemClass = 'tms-social-profiles__item';
        if ( itemId ) {
          itemClass += ' elementor-repeater-item-' + _.escape( itemId );
        }

        var linkAttrs = '';
        if ( url ) {
          linkAttrs += ' href="' + _.escape( url ) + '"';
          var relParts = [];
          if ( profile.url.is_external ) {
            linkAttrs += ' target="_blank"';
            relParts.push( 'noopener' );
          }
          if ( profile.url.nofollow ) { relParts.push( 'nofollow' ); }
          if ( relParts.length ) { linkAttrs += ' rel="' + relParts.join( ' ' ) + '"'; }
        }
        if ( label ) {
          linkAttrs += ' aria-label="' + _.escape( label ) + '"';
        }

        var iconMarkup = ( window.Themeasy && window.Themeasy.renderIconMarkup )
          ? window.Themeasy.renderIconMarkup( view, profile.icon, null, { 'class': 'tms-social-profiles__icon', 'aria-hidden': 'true' } )
          : '';
      #>

        <li class="tms-social-profiles__list-item">
          <# if ( url ) { #>
            <a class="{{ itemClass }}"{{{ linkAttrs }}}>
              {{{ iconMarkup }}}
            </a>
          <# } else { #>
            <span class="{{ itemClass }}"{{{ linkAttrs }}}>
              {{{ iconMarkup }}}
            </span>
          <# } #>
        </li>

      <# }); #>

    </ul><!-- /.tms-social-profiles -->

    <?php
  }
}
