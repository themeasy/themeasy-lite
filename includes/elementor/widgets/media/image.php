<?php
/**
 * Themeasy Elementor Widget: Image
 *
 * Displays an image with customizable styles, an optional caption, a media-file
 * lightbox or custom link, entrance animations, and pointer hover interactions.
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
use Elementor\Utils;
use Elementor\Widget_Base;
use Themeasy\Core\Entitlement;

/**
 * Image widget — registers controls and renders output.
 */
class Image extends Widget_Base {
  public function get_name() {
    return 'themeasy-image';
  }

  public function get_title() {
    return esc_html__( 'Image', 'themeasy-lite' );
  }

  public function get_icon() {
    return 'eicon-image';
  }

  public function get_custom_help_url() {
    return 'https://themeasy.co/help-center';
  }

  public function get_categories() {
    return ['themeasy_media_category', 'themeasy_content_category'];
  }

  public function get_keywords() {
    return ['themeasy', 'image', 'photo', 'picture', 'media', 'caption', 'lightbox'];
  }

  public function get_style_depends() {
    return ['themeasy-lightbox-module'];
  }

  public function get_script_depends() {
    return ['themeasy-lightbox-module'];
  }

  /**
   * Register widget controls.
   *
   * @return void
   */
  protected function register_controls() {
    // ------------------------------------------------------------------------
    // Content section: Image
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'image_content_section',
        [
          'label' => esc_html__( 'Image', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
    );

      $this->add_control(
        'image',
        [
          'label' => esc_html__( 'Select Image', 'themeasy-lite' ),
          'type' => Controls_Manager::MEDIA,
          'default' => [
            'url' => Utils::get_placeholder_image_src(),
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Image_Size::get_type(),
        [
          'name' => 'image_resolution',
          'separator' => 'before',
          'exclude' => ['custom'],
          'include' => [],
          'default' => 'full',
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
          'description' => esc_html__( 'Choose how this image loads. Lazy loading saves performance, while high priority is ideal for images at the top of the page.', 'themeasy-lite' ),
        ]
      );

      $this->add_control(
        'image_caption',
        [
          'label' => esc_html__( 'Image Caption', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXT,
          'separator' => 'before',
          'placeholder' => esc_html__( 'Leave blank to hide', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
        ]
      );

      $this->add_control(
        'image_caption_alignment',
        [
          'label' => esc_html__( 'Caption Position', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'options' => [
            'top-left' => esc_html__( 'Top Left', 'themeasy-lite' ),
            'top-center' => esc_html__( 'Top Center', 'themeasy-lite' ),
            'top-right' => esc_html__( 'Top Right', 'themeasy-lite' ),
            'bottom-left' => esc_html__( 'Bottom Left', 'themeasy-lite' ),
            'bottom-center' => esc_html__( 'Bottom Center', 'themeasy-lite' ),
            'bottom-right' => esc_html__( 'Bottom Right', 'themeasy-lite' ),
          ],
          'default' => 'bottom-center',
          'description' => esc_html__( 'Anchor the caption to one of the six corners/edges of the image.', 'themeasy-lite' ),
          'condition' => ['image_caption!' => ''],
        ]
      );

      $this->add_control(
        'image_link_type',
        [
          'label' => esc_html__( 'Link Type', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'separator' => 'before',
          'default' => '',
          'options' => [
            '' => esc_html__( 'None', 'themeasy-lite' ),
            'file' => esc_html__( 'Media File (Lightbox)', 'themeasy-lite' ),
            'custom' => esc_html__( 'Custom Link', 'themeasy-lite' ),
          ],
        ]
      );

      $this->add_control(
        'image_custom_link',
        [
          'label' => esc_html__( 'Custom Link', 'themeasy-lite' ),
          'type' => Controls_Manager::URL,
          'placeholder' => esc_html__( 'Enter a custom link...', 'themeasy-lite' ),
          'options' => ['url', 'is_external', 'nofollow'],
          'default' => [
            'url' => '',
            'is_external' => false,
            'nofollow' => true,
          ],
          'label_block' => true,
          'condition' => [
            'image_link_type' => 'custom',
          ],
          'dynamic' => ['active' => true],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Image
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'image_styles_section',
        [
          'label' => esc_html__( 'Image', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
    );

      // Colors.
      $this->add_control(
        'image_overlay_color',
        [
          'label' => esc_html__( 'Overlay Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-image .tms-image__overlay:not(:hover)' => 'background-color: {{VALUE}}',
          ],
        ]
      );

      $this->add_control(
        'image_overlay_hover_color',
        [
          'label' => esc_html__( 'Overlay Hover Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-image:hover .tms-image__overlay' => 'background-color: {{VALUE}}',
          ],
        ]
      );

      $this->add_control(
        'image_opacity',
        [
          'label' => esc_html__( 'Opacity', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['u'],
          'range' => [
            'u' => [
              'min' => 0,
              'max' => 1,
              'step' => 0.1,
            ],
          ],
          'default' => [
            'unit' => 'u',
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-image .tms-image__img' => 'opacity: {{SIZE}}',
          ],
        ]
      );

      // Dimensions.
      $this->add_responsive_control(
        'image_width',
        [
          'label' => esc_html__( 'Width', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'separator' => 'before',
          'size_units' => ['px', '%', 'em', 'rem', 'vw', 'custom'],
          'range' => [
            '%' => ['min' => 1, 'max' => 100],
            'px' => ['min' => 1, 'max' => 1000],
            'vw' => ['min' => 1, 'max' => 100],
          ],
          'default' => [
            'unit' => '%',
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-image .tms-image__img,
             {{WRAPPER}} .tms-image .tms-image__overlay' => 'width: 100%; max-width: {{SIZE}}{{UNIT}}',
          ],
        ]
      );

      $this->add_responsive_control(
        'image_height',
        [
          'label' => esc_html__( 'Height', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', '%', 'em', 'rem', 'vw', 'custom'],
          'range' => [
            '%' => ['min' => 1, 'max' => 100],
            'px' => ['min' => 1, 'max' => 1000],
            'vw' => ['min' => 1, 'max' => 100],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-image .tms-image__img,
             {{WRAPPER}} .tms-image .tms-image__overlay' => 'height: {{SIZE}}{{UNIT}}',
          ],
        ]
      );

      $this->add_control(
        'image_object_fit',
        [
          'label' => esc_html__( 'Object Fit', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => '',
          'options' => [
            '' => esc_html__( 'None', 'themeasy-lite' ),
            'contain' => esc_html__( 'Contain', 'themeasy-lite' ),
            'cover' => esc_html__( 'Cover', 'themeasy-lite' ),
            'fill' => esc_html__( 'Fill', 'themeasy-lite' ),
            'revert' => esc_html__( 'Revert', 'themeasy-lite' ),
            'scale-down' => esc_html__( 'Scale Down', 'themeasy-lite' ),
            'unset' => esc_html__( 'Unset', 'themeasy-lite' ),
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-image .tms-image__img' => 'object-fit: {{VALUE}}',
          ],
        ]
      );

      $this->add_responsive_control(
        'image_object_position',
        [
          'label' => esc_html__( 'Object Position', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => '',
          'options' => [
            '' => esc_html__( 'None', 'themeasy-lite' ),
            'center' => esc_html__( 'Center', 'themeasy-lite' ),
            'top' => esc_html__( 'Top', 'themeasy-lite' ),
            'right' => esc_html__( 'Right', 'themeasy-lite' ),
            'bottom' => esc_html__( 'Bottom', 'themeasy-lite' ),
            'left' => esc_html__( 'Left', 'themeasy-lite' ),
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-image .tms-image__img' => 'object-position: {{VALUE}}',
          ],
        ]
      );

      // Filters.
      $this->add_group_control(
        Group_Control_Css_Filter::get_type(),
        [
          'name' => 'image_css_filters',
          'selector' => '{{WRAPPER}} .tms-image .tms-image__img',
        ]
      );

      // Border.
      $this->add_group_control(
        Group_Control_Border::get_type(),
        [
          'name' => 'image_border',
          'separator' => 'before',
          'selector' => '{{WRAPPER}} .tms-image .tms-image__img',
        ]
      );

      $this->add_responsive_control(
        'image_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-image,
             {{WRAPPER}} .tms-image .tms-image__img,
             {{WRAPPER}} .tms-image .tms-image__overlay' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      // Box shadow.
      $this->add_group_control(
        Group_Control_Box_Shadow::get_type(),
        [
          'name' => 'image_box_shadow',
          'label' => esc_html__( 'Box Shadow', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-image',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Caption
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'caption_styles_section',
        [
          'label' => esc_html__( 'Caption', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => ['image_caption!' => ''],
        ]
    );

      // Colors.
      $this->add_control(
        'image_caption_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-image__caption' => 'color: {{VALUE}}',
          ],
        ]
      );

      $this->add_control(
        'image_caption_background_color',
        [
          'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-image__caption' => 'background-color: {{VALUE}}',
          ],
        ]
      );

      $this->add_control(
        'image_caption_blur',
        [
          'label' => esc_html__( 'Blur Amount', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => [
              'min' => 0,
              'max' => 50,
              'step' => 1,
            ],
          ],
          'default' => [
            'unit' => 'px',
            'size' => 15,
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-image__caption' => 'backdrop-filter: blur({{SIZE}}{{UNIT}}); -webkit-backdrop-filter: blur({{SIZE}}{{UNIT}});',
          ],
        ]
      );

      // Typography.
      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'image_caption_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'separator' => 'before',
          'selector' => '{{WRAPPER}} .tms-image__caption',
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
        'image_animation',
        [
          'label' => esc_html__( 'Entrance Animation', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => '',
          'options' => themeasy_get_animation_options( 'block' ),
        ]
      );

      $this->add_control(
        'image_animation_duration',
        [
          'label' => esc_html__( 'Duration', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['s'],
          'range' => ['s' => ['min' => 0, 'max' => 5, 'step' => 0.1]],
          'default' => ['unit' => 's', 'size' => 1],
          'condition' => ['image_animation!' => ''],
        ]
      );

      $this->add_control(
        'image_animation_delay',
        [
          'label' => esc_html__( 'Delay', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['s'],
          'range' => ['s' => ['min' => 0, 'max' => 5, 'step' => 0.1]],
          'default' => ['unit' => 's', 'size' => 0],
          'condition' => ['image_animation!' => ''],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Advanced section: Hover Interactions
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'widget_hover_section',
        [
          'label' => esc_html__( 'Motion — Hover Interactions', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_ADVANCED,
        ]
    );

      $this->add_control(
        'image_hover_animation',
        [
          'label' => esc_html__( 'Hover Animation', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => '',
          'options' => themeasy_get_animation_options( 'hover' ),
        ]
      );

      $this->add_control(
        'cursor_text',
        [
          'label' => esc_html__( 'Cursor Text', 'themeasy-lite' ),
          'label_block' => false,
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'VIEW', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'Type your text here', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
          'condition' => [
            'image_hover_animation' => 'viewCursor',
          ],
        ]
      );

      $this->add_control(
        'cursor_text_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '.tms-cursor-interaction' => 'color: {{VALUE}}',
          ],
          'condition' => [
            'image_hover_animation' => 'viewCursor',
          ],
        ]
      );

      $this->add_control(
        'cursor_text_background_color',
        [
          'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '.tms-cursor-interaction' => 'background-color: {{VALUE}}',
          ],
          'condition' => [
            'image_hover_animation' => 'viewCursor',
          ],
        ]
      );

      $this->add_control(
        'cursor_text_width',
        [
          'label' => esc_html__( 'Width', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => ['min' => 30, 'max' => 300, 'step' => 5],
          ],
          'default' => [
            'size' => 100,
            'unit' => 'px',
          ],
          'selectors' => [
            '.tms-cursor-interaction' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; margin-top: calc(-{{SIZE}}{{UNIT}} / 2); margin-left: calc(-{{SIZE}}{{UNIT}} / 2);',
          ],
          'condition' => [
            'image_hover_animation' => 'viewCursor',
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

    // Themeasy Motion is Pro (GSAP — backlog #253). Without it the motion
    // settings read as empty, so saved values (a template, a site back from
    // premium) emit no markup: the hidden guard would leave the widget
    // invisible with no engine to reveal it.
    $motion = Entitlement::can_use_widgets();

    // ------------------------------------------------------------------------
    // Settings.
    // ------------------------------------------------------------------------
    $image_url = $settings['image']['url'] ?? '';

    // Early return if no image.
    if ( ! $image_url ) {
      return;
    }

    $image_id = absint( $settings['image']['id'] ?? 0 );
    $image_alt = Control_Media::get_image_alt( $settings['image'] );
    $image_size = $settings['image_resolution_size'] ?? 'full';
    $image_loading_priority = $settings['image_loading_priority'] ?? 'lazy';
    $link_type = $settings['image_link_type'] ?? '';
    $caption_text = $settings['image_caption'] ?? '';
    $caption_alignment = $settings['image_caption_alignment'] ?? 'bottom-center';
    $has_caption = (bool) $caption_text;

    // ------------------------------------------------------------------------
    // Wrapper classes + attributes.
    // ------------------------------------------------------------------------
    $wrapper_classes = ['tms-image-group'];
    $wrapper_atts = [];

    // Entrance animation.
    $block_animation = $motion ? ( $settings['image_animation'] ?? '' ) : '';

    if ( $block_animation ) {
      $wrapper_classes[] = 'tms-block-animation';
      $wrapper_classes[] = 'tms-animation--on-view';
      $wrapper_classes[] = 'tms-animation--hidden';

      $wrapper_atts['tms-block-animation'] = $block_animation;
      $wrapper_atts['duration'] = $settings['image_animation_duration']['size'] ?? '1';
      $wrapper_atts['delay'] = $settings['image_animation_delay']['size'] ?? '0';
    }

    // Hover animation.
    $hover_animation = $motion ? ( $settings['image_hover_animation'] ?? '' ) : '';

    if ( $hover_animation ) {
      $wrapper_classes[] = 'tms-hover-animation';
      $wrapper_classes[] = 'tms-animation__target';
      $wrapper_atts['tms-hover-animation'] = $hover_animation;

      if ( 'viewCursor' === $hover_animation ) {
        $wrapper_atts['tms-cursor-text'] = $settings['cursor_text'] ?? 'VIEW';
      }
    }

    $wrapper_classes_str = implode( ' ', array_filter( $wrapper_classes ) );
    $wrapper_atts_html = themeasy_html_attributes( $wrapper_atts );

    // ------------------------------------------------------------------------
    // Render attributes.
    // ------------------------------------------------------------------------

    // Link. Only treat as linked once a usable destination is confirmed, so an
    // empty custom-link URL never renders a bare, focusable <a> with no href.
    $link_key = 'image_link';
    $has_link = false;
    $link_classes = ['tms-image__link'];

    if ( 'file' === $link_type ) {
      $has_link = true;
      $link_classes[] = 'glightbox';
      $this->add_render_attribute( $link_key, 'href', esc_url( $image_url ) );
    } elseif ( 'custom' === $link_type && ! empty( $settings['image_custom_link']['url'] ) ) {
      $has_link = true;
      $link_classes[] = 'tms-image__link--custom';
      $this->add_link_attributes( $link_key, $settings['image_custom_link'] );
      themeasy_add_external_link_rel( $this, $link_key, $settings['image_custom_link'] );
    }

    if ( $has_link ) {
      $this->add_render_attribute( $link_key, 'class', implode( ' ', array_filter( $link_classes ) ) );
    }

    // Caption.
    if ( $has_caption ) {
      $caption_classes = ['tms-image__caption'];

      // Legacy values stored by the previous three-option control, mapped onto
      // the closest of the six anchors so saved pages keep their layout.
      $caption_legacy_map = [
        'left' => 'bottom-left',
        'right' => 'bottom-right',
        'full' => 'bottom-center',
      ];

      $caption_alignment = $caption_legacy_map[$caption_alignment] ?? $caption_alignment;

      $caption_positions = ['top-left', 'top-center', 'top-right', 'bottom-left', 'bottom-center', 'bottom-right'];

      // Allowlist the value that feeds a CSS class name (defends against tampered stored data).
      if ( ! in_array( $caption_alignment, $caption_positions, true ) ) {
        $caption_alignment = 'bottom-center';
      }

      $caption_classes[] = 'tms-image__caption--' . $caption_alignment;

      $this->add_render_attribute( 'image_caption', 'class', implode( ' ', array_filter( $caption_classes ) ) );
      $this->add_inline_editing_attributes( 'image_caption', 'none' );
    }

    // ------------------------------------------------------------------------
    // Output.
    // ------------------------------------------------------------------------
    ?>
    <div class="<?php echo esc_attr( $wrapper_classes_str ); ?>"<?php echo $wrapper_atts_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

      <?php if ( $has_link ) : ?>
        <a <?php $this->print_render_attribute_string( $link_key ); ?>>
      <?php endif; ?>

        <div class="tms-image">
          <div class="tms-image__overlay"></div>

          <?php
          themeasy_render_attachment_image(
            $image_id,
            $image_size,
            $image_url,
            $image_alt,
            'tms-image__img',
            [],
            $image_loading_priority
          );
          ?>

          <?php if ( $has_caption ) : ?>
            <figcaption <?php $this->print_render_attribute_string( 'image_caption' ); ?>>
              <?php echo esc_html( $caption_text ); ?>
            </figcaption>
          <?php endif; ?>

        </div><!-- /.tms-image -->

      <?php if ( $has_link ) : ?>
        </a>
      <?php endif; ?>

    </div><!-- /.tms-image-group -->
    <?php
  }

  /**
   * Render the editor preview template (Backbone/Underscore).
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
      var safeUrl = ( window.Themeasy && window.Themeasy.safeUrl )
        ? window.Themeasy.safeUrl
        : function () { return ''; };

      var imageUrl = safeUrl( ( settings.image && settings.image.url ) ? settings.image.url : '' );

      if ( ! imageUrl ) {
        return;
      }

      var imageAlt         = ( settings.image && settings.image.alt ) ? settings.image.alt : '';
      var linkType         = settings.image_link_type || '';
      var captionText      = settings.image_caption || '';
      var captionAlignment = settings.image_caption_alignment || 'bottom-center';
      var hasCaption       = !! captionText;
      var hasLink          = false;

      // ------------------------------------------------------------------------
      // Wrapper classes + attributes.
      // ------------------------------------------------------------------------
      var wrapperClasses = [ 'tms-image-group' ];
      var wrapperAtts    = {};

      // Entrance animation.
      var blockAnimation = motion ? ( settings.image_animation || '' ) : '';

      if ( blockAnimation ) {
        wrapperClasses.push( 'tms-block-animation', 'tms-animation--on-view', 'tms-animation--hidden' );

        wrapperAtts['tms-block-animation'] = blockAnimation;
        wrapperAtts['duration'] = ( settings.image_animation_duration && settings.image_animation_duration.size )
          ? settings.image_animation_duration.size
          : '1';
        wrapperAtts['delay'] = ( settings.image_animation_delay && settings.image_animation_delay.size )
          ? settings.image_animation_delay.size
          : '0';
      }

      // Hover animation.
      var hoverAnimation = motion ? ( settings.image_hover_animation || '' ) : '';

      if ( hoverAnimation ) {
        wrapperClasses.push( 'tms-hover-animation', 'tms-animation__target' );
        wrapperAtts['tms-hover-animation'] = hoverAnimation;

        if ( hoverAnimation === 'viewCursor' ) {
          wrapperAtts['tms-cursor-text'] = settings.cursor_text || 'VIEW';
        }
      }

      var wrapperClassStr = wrapperClasses.filter( Boolean ).join( ' ' );

      // ------------------------------------------------------------------------
      // Render attributes.
      // ------------------------------------------------------------------------

      // Link. Mirror render(): only treat as linked once a destination exists.
      // Elementor's render-attribute pipeline only entity-escapes the href, so
      // every URL is scheme-guarded through safeUrl() before it gets there.
      var linkClasses = [ 'tms-image__link' ];

      var safeCustomUrl = safeUrl(
        ( settings.image_custom_link && settings.image_custom_link.url ) ? settings.image_custom_link.url : ''
      );

      if ( linkType === 'file' ) {
        hasLink = true;
        linkClasses.push( 'glightbox' );
        view.addRenderAttribute( 'image_link', 'href', imageUrl );
      } else if ( linkType === 'custom' && safeCustomUrl ) {
        hasLink = true;
        linkClasses.push( 'tms-image__link--custom' );
        view.addRenderAttribute( 'image_link', 'href', safeCustomUrl );
      }

      if ( hasLink ) {
        view.addRenderAttribute( 'image_link', 'class', linkClasses.filter( Boolean ).join( ' ' ) );
      }

      // Caption.
      if ( hasCaption ) {
        // Legacy values stored by the previous three-option control (mirrors render()).
        var captionLegacyMap = {
          'left':  'bottom-left',
          'right': 'bottom-right',
          'full':  'bottom-center',
        };

        var captionPositions = [ 'top-left', 'top-center', 'top-right', 'bottom-left', 'bottom-center', 'bottom-right' ];

        captionAlignment = captionLegacyMap[ captionAlignment ] || captionAlignment;

        if ( captionPositions.indexOf( captionAlignment ) === -1 ) {
          captionAlignment = 'bottom-center';
        }

        var captionClasses = [ 'tms-image__caption', 'tms-image__caption--' + captionAlignment ];

        view.addRenderAttribute( 'image_caption', 'class', captionClasses.filter( Boolean ).join( ' ' ) );
        view.addInlineEditingAttributes( 'image_caption', 'none' );
      }
    #>

    <div class="{{ wrapperClassStr }}" {{{ Themeasy.htmlAttributes( wrapperAtts ) }}}>

      <# if ( hasLink ) { #>
        <a {{{ view.getRenderAttributeString( 'image_link' ) }}}>
      <# } #>

        <div class="tms-image">
          <div class="tms-image__overlay"></div>

          <img src="{{ imageUrl }}" alt="{{ imageAlt }}" class="tms-image__img" />

          <# if ( hasCaption ) { #>
            <figcaption {{{ view.getRenderAttributeString( 'image_caption' ) }}}>{{ captionText }}</figcaption>
          <# } #>

        </div><!-- /.tms-image -->

      <# if ( hasLink ) { #>
        </a>
      <# } #>

    </div><!-- /.tms-image-group -->

    <?php
  }
}
