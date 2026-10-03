<?php
/**
 * Themeasy Elementor Widget: Video
 *
 * Premium video facade for YouTube, Vimeo and self-hosted files. Renders a
 * designed poster (custom image, auto-fetched YouTube thumbnail, or a token
 * gradient placeholder) with a glass play button, optional pulse ring, scrim
 * and micro-label badge. Nothing third-party loads until the visitor clicks:
 * Play in Place swaps in a privacy-enhanced embed (youtube-nocookie / Vimeo
 * dnt) on demand, Lightbox opens the video in GLightbox, and self-hosted
 * files can run as an ambient muted loop with no facade at all. With no JS
 * the facade is a plain link to the video; in the editor and under reduced
 * motion every flourish stays still.
 *
 * @package Themeasy
 * @since 1.0.0
 */

namespace Themeasy\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;
use Themeasy\Core\Entitlement;

/**
 * Responsible for registering controls and rendering the Video widget.
 */
class Video extends Widget_Base {
  public function get_name() {
    return 'themeasy-video';
  }

  public function get_title() {
    return esc_html__( 'Video', 'themeasy-lite' );
  }

  public function get_icon() {
    return 'eicon-play';
  }

  public function get_custom_help_url() {
    return 'https://themeasy.co/help-center';
  }

  public function get_categories() {
    return ['themeasy_media_category', 'themeasy_content_category'];
  }

  public function get_keywords() {
    return ['themeasy', 'video', 'youtube', 'vimeo', 'player', 'embed', 'lightbox', 'media'];
  }

  public function get_style_depends() {
    return ['themeasy-shared', 'themeasy-video'];
  }

  public function get_script_depends() {
    return ['themeasy-video'];
  }

  /**
   * Register widget customization controls.
   *
   * @return void
   */
  protected function register_controls() {
    // Ambient only applies to self-hosted videos. Facade controls hide behind
    // this OR group (not a plain 'ambient_mode!' condition) so a stale
    // ambient_mode=yes saved under 'hosted' cannot strand them hidden after
    // the user switches to YouTube/Vimeo — where the toggle itself is gone.
    $not_ambient = [
      'relation' => 'or',
      'terms' => [
        ['name' => 'ambient_mode', 'operator' => '!==', 'value' => 'yes'],
        ['name' => 'video_source', 'operator' => '!==', 'value' => 'hosted'],
      ],
    ];

    // ------------------------------------------------------------------------
    // Content section: Video
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'video_content_section',
        [
          'label' => esc_html__( 'Video', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $this->add_control(
        'video_source',
        [
          'label' => esc_html__( 'Source', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'youtube',
          'options' => [
            'youtube' => esc_html__( 'YouTube', 'themeasy-lite' ),
            'vimeo' => esc_html__( 'Vimeo', 'themeasy-lite' ),
            'hosted' => esc_html__( 'Self-Hosted', 'themeasy-lite' ),
          ],
        ]
      );

      $this->add_control(
        'youtube_url',
        [
          'label' => esc_html__( 'YouTube URL', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXT,
          'default' => 'https://www.youtube.com/watch?v=XHOmBV4js_E',
          'placeholder' => esc_html__( 'https://www.youtube.com/watch?v=…', 'themeasy-lite' ),
          'description' => esc_html__( 'Any YouTube link works — watch, share, Shorts or embed URLs.', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
          'condition' => [
            'video_source' => 'youtube',
          ],
        ]
      );

      $this->add_control(
        'vimeo_url',
        [
          'label' => esc_html__( 'Vimeo URL', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXT,
          'default' => '',
          'placeholder' => esc_html__( 'https://vimeo.com/235215203', 'themeasy-lite' ),
          'description' => esc_html__( 'Unlisted videos with a privacy hash (vimeo.com/id/hash) are supported.', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
          'condition' => [
            'video_source' => 'vimeo',
          ],
        ]
      );

      $this->add_control(
        'hosted_external',
        [
          'label' => esc_html__( 'External File', 'themeasy-lite' ),
          'description' => esc_html__( 'Point to a video hosted outside the Media Library (e.g. a CDN).', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => '',
          'condition' => [
            'video_source' => 'hosted',
          ],
        ]
      );

      $this->add_control(
        'hosted_video',
        [
          'label' => esc_html__( 'Video File', 'themeasy-lite' ),
          'type' => Controls_Manager::MEDIA,
          'media_types' => ['video'],
          'dynamic' => ['active' => true],
          'condition' => [
            'video_source' => 'hosted',
            'hosted_external' => '',
          ],
        ]
      );

      $this->add_control(
        'hosted_external_url',
        [
          'label' => esc_html__( 'File URL', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::URL,
          'options' => false,
          'placeholder' => esc_html__( 'https://cdn.your-site.com/video.mp4', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
          'condition' => [
            'video_source' => 'hosted',
            'hosted_external' => 'yes',
          ],
        ]
      );

      $this->add_control(
        'start_time',
        [
          'label' => esc_html__( 'Start Time (s)', 'themeasy-lite' ),
          'type' => Controls_Manager::NUMBER,
          'min' => 0,
          'step' => 1,
          'separator' => 'before',
          'description' => esc_html__( 'Seconds into the video where playback begins. Applies to every source.', 'themeasy-lite' ),
        ]
      );

      $this->add_control(
        'end_time',
        [
          'label' => esc_html__( 'End Time (s)', 'themeasy-lite' ),
          'type' => Controls_Manager::NUMBER,
          'min' => 0,
          'step' => 1,
          'condition' => [
            'video_source' => 'youtube',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Poster & Play Button
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'video_poster_section',
        [
          'label' => esc_html__( 'Poster & Play Button', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $this->add_control(
        'poster_image',
        [
          'label' => esc_html__( 'Poster', 'themeasy-lite' ),
          'type' => Controls_Manager::MEDIA,
          'description' => esc_html__( 'Optional. YouTube fills in the video\'s own thumbnail automatically; Vimeo and self-hosted fall back to a neutral gradient.', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
        ]
      );
      $this->add_group_control( // poster_resolution
        Group_Control_Image_Size::get_type(),
        [
          'name' => 'poster_resolution',
          'default' => 'large',
          'exclude' => ['custom'],
          'condition' => [
            'poster_image[url]!' => '',
          ],
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
          'condition' => [
            'poster_image[url]!' => '',
          ],
        ]
      );

      $this->add_control(
        'play_icon',
        [
          'label' => esc_html__( 'Play Icon', 'themeasy-lite' ),
          'type' => Controls_Manager::ICONS,
          'separator' => 'before',
          'default' => [
            'value' => 'ty-feather-play',
            'library' => 'ty-feather',
          ],
          'conditions' => $not_ambient,
        ]
      );

      $this->add_control(
        'badge_text',
        [
          'label' => esc_html__( 'Badge', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'default' => '',
          'placeholder' => esc_html__( 'e.g. 2:34 or Watch the film', 'themeasy-lite' ),
          'description' => esc_html__( 'Optional micro-label chip over the poster — a duration or a call-to-watch.', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
          'conditions' => $not_ambient,
        ]
      );

      $this->add_control(
        'video_label',
        [
          'label' => esc_html__( 'Accessible Label', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'default' => '',
          'placeholder' => esc_html__( 'Play video', 'themeasy-lite' ),
          'description' => esc_html__( 'Announced to screen readers on the play control. Defaults to "Play video".', 'themeasy-lite' ),
          'conditions' => $not_ambient,
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Layout
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'video_layout_section',
        [
          'label' => esc_html__( 'Layout', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $this->add_responsive_control(
        'aspect_ratio',
        [
          'label' => esc_html__( 'Aspect Ratio', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => '169',
          'options' => [
            '169' => '16:9',
            '219' => '21:9',
            '43' => '4:3',
            '32' => '3:2',
            '11' => '1:1',
            '916' => '9:16',
          ],
          'selectors_dictionary' => [
            '169' => '16 / 9',
            '219' => '21 / 9',
            '43' => '4 / 3',
            '32' => '3 / 2',
            '11' => '1 / 1',
            '916' => '9 / 16',
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-video__frame' => 'aspect-ratio: {{VALUE}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Settings
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'video_settings_section',
        [
          'label' => esc_html__( 'Settings', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $this->add_control(
        'ambient_mode',
        [
          'label' => esc_html__( 'Ambient Loop', 'themeasy-lite' ),
          'description' => esc_html__( 'Plays immediately as a silent, looping background clip — no poster, no controls.', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => '',
          'condition' => [
            'video_source' => 'hosted',
          ],
        ]
      );

      $this->add_control(
        'video_mode',
        [
          'label' => esc_html__( 'Play Mode', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'inline',
          'options' => [
            'inline' => esc_html__( 'Play in Place (Click to Load)', 'themeasy-lite' ),
            'lightbox' => esc_html__( 'Open in Lightbox', 'themeasy-lite' ),
          ],
          'description' => esc_html__( 'Play in Place loads nothing until the click, then swaps in a privacy-enhanced player. The lightbox uses its own player settings.', 'themeasy-lite' ),
          'conditions' => $not_ambient,
        ]
      );

      $this->add_control(
        'video_controls',
        [
          'label' => esc_html__( 'Player Controls', 'themeasy-lite' ),
          'description' => esc_html__( 'Vimeo honors hidden controls on paid plans only.', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Show', 'themeasy-lite' ),
          'label_off' => esc_html__( 'Hide', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
          'separator' => 'before',
          'conditions' => [
            'relation' => 'and',
            'terms' => [
              ['name' => 'video_mode', 'operator' => '===', 'value' => 'inline'],
              $not_ambient,
            ],
          ],
        ]
      );

      $this->add_control(
        'video_muted',
        [
          'label' => esc_html__( 'Start Muted', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => '',
          'conditions' => [
            'relation' => 'and',
            'terms' => [
              ['name' => 'video_mode', 'operator' => '===', 'value' => 'inline'],
              $not_ambient,
            ],
          ],
        ]
      );

      $this->add_control(
        'video_loop',
        [
          'label' => esc_html__( 'Loop', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => '',
          'conditions' => [
            'relation' => 'and',
            'terms' => [
              ['name' => 'video_mode', 'operator' => '===', 'value' => 'inline'],
              $not_ambient,
            ],
          ],
        ]
      );

      $this->add_control(
        'play_pulse',
        [
          'label' => esc_html__( 'Pulse Ring', 'themeasy-lite' ),
          'description' => esc_html__( 'A soft ping that radiates from the play button, inviting the click.', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'On', 'themeasy-lite' ),
          'label_off' => esc_html__( 'Off', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
          'separator' => 'before',
          'conditions' => $not_ambient,
        ]
      );

      $this->add_control(
        'show_scrim',
        [
          'label' => esc_html__( 'Poster Scrim', 'themeasy-lite' ),
          'description' => esc_html__( 'A soft bottom gradient that keeps the play button and badge readable on bright posters.', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'On', 'themeasy-lite' ),
          'label_off' => esc_html__( 'Off', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
          'conditions' => $not_ambient,
        ]
      );

      $this->add_control(
        'hover_zoom',
        [
          'label' => esc_html__( 'Hover Zoom', 'themeasy-lite' ),
          'description' => esc_html__( 'Gently scales the poster while the pointer rests on it.', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'On', 'themeasy-lite' ),
          'label_off' => esc_html__( 'Off', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
          'conditions' => $not_ambient,
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Frame
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'video_frame_styles_section',
        [
          'label' => esc_html__( 'Frame', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_control(
        'frame_background_color',
        [
          'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
          'description' => esc_html__( 'Visible behind letterboxing and while the player loads.', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-video__frame' => 'background-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'frame_border_heading',
        [
          'label' => esc_html__( 'Border', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );
      $this->add_group_control( // frame_border
        Group_Control_Border::get_type(),
        [
          'name' => 'frame_border',
          'selector' => '{{WRAPPER}} .tms-video__frame',
        ]
      );

      $this->add_responsive_control(
        'frame_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', '%', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-video__frame'
              => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );
      $this->add_group_control( // frame_box_shadow
        Group_Control_Box_Shadow::get_type(),
        [
          'name' => 'frame_box_shadow',
          'label' => esc_html__( 'Box Shadow', 'themeasy-lite' ),
          'separator' => 'before',
          'selector' => '{{WRAPPER}} .tms-video__frame',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Poster & Overlay
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'video_poster_styles_section',
        [
          'label' => esc_html__( 'Poster & Overlay', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'conditions' => $not_ambient,
        ]
      );

      $this->add_control(
        'scrim_color',
        [
          'label' => esc_html__( 'Scrim Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-video' => '--tms-video-scrim-color: {{VALUE}};',
          ],
          'condition' => [
            'show_scrim' => 'yes',
          ],
        ]
      );

      $this->add_control(
        'scrim_opacity',
        [
          'label' => esc_html__( 'Scrim Opacity', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['u'],
          'range' => ['u' => ['min' => 0, 'max' => 1, 'step' => 0.05]],
          'default' => ['unit' => 'u', 'size' => 0.5],
          'selectors' => [
            '{{WRAPPER}} .tms-video' => '--tms-video-scrim-opacity: {{SIZE}};',
          ],
          'condition' => [
            'show_scrim' => 'yes',
          ],
        ]
      );
      $this->add_group_control( // poster_css_filters
        Group_Control_Css_Filter::get_type(),
        [
          'name' => 'poster_css_filters',
          'selector' => '{{WRAPPER}} .tms-video__poster',
        ]
      );

      $this->add_control(
        'hover_zoom_scale',
        [
          'label' => esc_html__( 'Hover Zoom Scale', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['u'],
          'range' => ['u' => ['min' => 1, 'max' => 1.3, 'step' => 0.01]],
          'default' => ['unit' => 'u', 'size' => 1.04],
          'selectors' => [
            '{{WRAPPER}} .tms-video' => '--tms-video-zoom-scale: {{SIZE}};',
          ],
          'condition' => [
            'hover_zoom' => 'yes',
          ],
        ]
      );

      $this->add_control(
        'video_transition_duration',
        [
          'label' => esc_html__( 'Transition Duration', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['s'],
          'range' => ['s' => ['min' => 0.1, 'max' => 2, 'step' => 0.05]],
          'default' => ['unit' => 's', 'size' => 0.35],
          'separator' => 'before',
          'selectors' => [
            '{{WRAPPER}} .tms-video' => '--tms-video-duration: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Badge
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'video_badge_styles_section',
        [
          'label' => esc_html__( 'Badge', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'conditions' => [
            'relation' => 'and',
            'terms' => [
              ['name' => 'badge_text', 'operator' => '!==', 'value' => ''],
              $not_ambient,
            ],
          ],
        ]
      );

      $this->add_control(
        'badge_color',
        [
          'label' => esc_html__( 'Text Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-video__badge' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'badge_background_color',
        [
          'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-video__badge' => 'background-color: {{VALUE}};',
          ],
        ]
      );
      $this->add_group_control( // badge_typography
        Group_Control_Typography::get_type(),
        [
          'name' => 'badge_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-video__badge',
        ]
      );

      $this->add_responsive_control(
        'badge_padding',
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'em', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-video__badge'
              => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Border::get_type(),
        [
          'name' => 'badge_border',
          'selector' => '{{WRAPPER}} .tms-video__badge',
        ]
      );

      $this->add_responsive_control(
        'badge_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', '%', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-video__badge'
              => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'badge_offset',
        [
          'label' => esc_html__( 'Corner Offset', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem'],
          'range' => [
            'px' => ['min' => 4, 'max' => 48, 'step' => 1],
            'rem' => ['min' => 0.25, 'max' => 3, 'step' => 0.125],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-video' => '--tms-video-badge-offset: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Play Button
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'video_play_styles_section',
        [
          'label' => esc_html__( 'Play Button', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'conditions' => $not_ambient,
        ]
      );

      $this->start_controls_tabs( 'play_states_tabs' );

        $this->start_controls_tab(
          'play_normal_tab',
          [
            'label' => esc_html__( 'Normal', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'play_icon_color',
            [
              'label' => esc_html__( 'Icon Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-video__play' => 'color: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            'play_background_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-video__play' => 'background-color: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            'play_border_color',
            [
              'label' => esc_html__( 'Border Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-video__play' => 'border-color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

        $this->start_controls_tab(
          'play_hover_tab',
          [
            'label' => esc_html__( 'Hover', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'play_icon_hover_color',
            [
              'label' => esc_html__( 'Icon Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-video__facade:hover .tms-video__play,
                 {{WRAPPER}} .tms-video__facade:focus-visible .tms-video__play' => 'color: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            'play_background_hover_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-video__facade:hover .tms-video__play,
                 {{WRAPPER}} .tms-video__facade:focus-visible .tms-video__play' => 'background-color: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            'play_border_hover_color',
            [
              'label' => esc_html__( 'Border Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-video__facade:hover .tms-video__play,
                 {{WRAPPER}} .tms-video__facade:focus-visible .tms-video__play' => 'border-color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

      $this->add_control(
        'play_ring_color',
        [
          'label' => esc_html__( 'Pulse Ring Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'separator' => 'before',
          'selectors' => [
            '{{WRAPPER}} .tms-video' => '--tms-video-play-ring-color: {{VALUE}};',
          ],
          'condition' => [
            'play_pulse' => 'yes',
          ],
        ]
      );

      $this->add_responsive_control(
        'play_box_size',
        [
          'label' => esc_html__( 'Button Size', 'themeasy-lite' ),
          'description' => esc_html__( 'Circle diameter. In em it tracks the icon size; px/rem pin it.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['em', 'px', 'rem'],
          'range' => [
            'em' => ['min' => 1, 'max' => 8, 'step' => 0.1],
            'px' => ['min' => 24, 'max' => 200, 'step' => 1],
            'rem' => ['min' => 1.5, 'max' => 12, 'step' => 0.125],
          ],
          'default' => ['unit' => 'em', 'size' => 2.6],
          'separator' => 'before',
          'selectors' => [
            '{{WRAPPER}} .tms-video' => '--tms-video-play-box: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'play_size',
        [
          'label' => esc_html__( 'Icon Size', 'themeasy-lite' ),
          'description' => esc_html__( 'Sets the glyph. An em-based Button Size scales along with it.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem'],
          'range' => [
            'px' => ['min' => 14, 'max' => 72, 'step' => 1],
            'rem' => ['min' => 0.875, 'max' => 4.5, 'step' => 0.125],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-video' => '--tms-video-play-size: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'play_backdrop_blur',
        [
          'label' => esc_html__( 'Glass Blur', 'themeasy-lite' ),
          'description' => esc_html__( 'Blurs the poster behind the button. Set 0 for a flat plate.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => ['min' => 0, 'max' => 40, 'step' => 1],
          ],
          'default' => ['unit' => 'px', 'size' => 10],
          'selectors' => [
            '{{WRAPPER}} .tms-video' => '--tms-video-play-blur: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'play_border_heading',
        [
          'label' => esc_html__( 'Border', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );
      // Type and width only: the Normal/Hover Border Color controls above stay
      // authoritative, so the button keeps its per-state color (the group's own
      // color field targets the same selector and would outrank the stylesheet's
      // :hover rule). "Default" leaves the module CSS border (1px solid) alone.
      $this->add_group_control( // play_border
        Group_Control_Border::get_type(),
        [
          'name' => 'play_border',
          'exclude' => ['color'],
          'selector' => '{{WRAPPER}} .tms-video__play',
        ]
      );

      $this->add_responsive_control(
        'play_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'description' => esc_html__( 'Rounds the button — the pulse ring inherits the same corners.', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', '%', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-video__play'
              => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );
      $this->add_group_control( // play_box_shadow
        Group_Control_Box_Shadow::get_type(),
        [
          'name' => 'play_box_shadow',
          'label' => esc_html__( 'Box Shadow', 'themeasy-lite' ),
          'separator' => 'before',
          'selector' => '{{WRAPPER}} .tms-video__play',
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
   * Extract the YouTube video ID from any common URL shape.
   *
   * Accepts watch, share (youtu.be), Shorts, live and embed URLs, including
   * the nocookie host. Returns an empty string when no ID is found.
   *
   * @param string $url YouTube URL as typed by the user.
   * @return string
   */
  private function parse_youtube_id( string $url ): string {
    if ( '' === trim( $url ) ) {
      return '';
    }

    if ( !preg_match( '~(?:youtube(?:-nocookie)?\.com/(?:watch\?(?:[^#]*&)?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{6,20})~i', $url, $matches ) ) {
      return '';
    }

    return $matches[1];
  }

  /**
   * Extract the Vimeo video ID (and optional unlisted privacy hash) from a URL.
   *
   * @param string $url Vimeo URL as typed by the user.
   * @return array{0: string, 1: string} [id, hash] — empty strings when unresolved.
   */
  private function parse_vimeo_parts( string $url ): array {
    if ( '' === trim( $url ) ) {
      return ['', ''];
    }

    if ( !preg_match( '~vimeo\.com/(?:video/|channels/[^/]+/|groups/[^/]+/videos/|event/)?(\d+)(?:/([A-Za-z0-9]+))?~i', $url, $matches ) ) {
      return ['', ''];
    }

    return [$matches[1], $matches[2] ?? ''];
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
    $source_raw = $settings['video_source'] ?? 'youtube';
    $source = in_array( $source_raw, ['youtube', 'vimeo', 'hosted'], true ) ? $source_raw : 'youtube';

    $mode_raw = $settings['video_mode'] ?? 'inline';
    $mode = in_array( $mode_raw, ['inline', 'lightbox'], true ) ? $mode_raw : 'inline';
    $ambient = 'hosted' === $source && ( $settings['ambient_mode'] ?? '' ) === 'yes';

    $start = max( 0, (int) ( $settings['start_time'] ?? 0 ) );
    $end = max( 0, (int) ( $settings['end_time'] ?? 0 ) );

    $show_controls = ( $settings['video_controls'] ?? 'yes' ) === 'yes';
    $muted = ( $settings['video_muted'] ?? '' ) === 'yes';
    $loop = ( $settings['video_loop'] ?? '' ) === 'yes';
    $pulse = ( $settings['play_pulse'] ?? 'yes' ) === 'yes';
    $show_scrim = ( $settings['show_scrim'] ?? 'yes' ) === 'yes';
    $hover_zoom = ( $settings['hover_zoom'] ?? 'yes' ) === 'yes';

    // ------------------------------------------------------------------------
    // Resolve the video source.
    // ------------------------------------------------------------------------
    $youtube_id = '';
    $vimeo_id = '';
    $vimeo_hash = '';
    $hosted_url = '';

    if ( 'youtube' === $source ) {
      $youtube_id = $this->parse_youtube_id( (string) ( $settings['youtube_url'] ?? '' ) );
    } elseif ( 'vimeo' === $source ) {
      [$vimeo_id, $vimeo_hash] = $this->parse_vimeo_parts( (string) ( $settings['vimeo_url'] ?? '' ) );
    } else {
      $hosted_url = ( $settings['hosted_external'] ?? '' ) === 'yes'
        ? (string) ( $settings['hosted_external_url']['url'] ?? '' )
        : (string) ( $settings['hosted_video']['url'] ?? '' );
    }

    // A source-less widget still renders when a poster was authored (the
    // poster-first facade below); the silent return only covers the truly
    // empty case, decided once the poster is resolved.
    $has_source = '' !== $youtube_id || '' !== $vimeo_id || '' !== $hosted_url;

    // The ambient skin is a bare <video>: with no file it has nothing to show.
    $ambient = $ambient && $has_source;

    // ------------------------------------------------------------------------
    // Canonical watch URL (the no-JS href) + privacy-enhanced embed URL.
    // ------------------------------------------------------------------------
    $watch_url = '';
    $embed_url = '';

    if ( '' !== $youtube_id ) {
      $watch_url = 'https://www.youtube.com/watch?v=' . $youtube_id . ( $start ? '&t=' . $start . 's' : '' );

      $embed_args = ['autoplay' => '1', 'rel' => '0', 'playsinline' => '1'];

      if ( $start ) {
        $embed_args['start'] = (string) $start;
      }

      if ( $end > $start ) {
        $embed_args['end'] = (string) $end;
      }

      if ( !$show_controls ) {
        $embed_args['controls'] = '0';
      }

      if ( $muted ) {
        $embed_args['mute'] = '1';
      }

      if ( $loop ) {
        $embed_args['loop'] = '1';
        $embed_args['playlist'] = $youtube_id;
      }

      $embed_url = add_query_arg( $embed_args, 'https://www.youtube-nocookie.com/embed/' . $youtube_id );
    } elseif ( '' !== $vimeo_id ) {
      $watch_url = 'https://vimeo.com/' . $vimeo_id
        . ( $vimeo_hash ? '/' . $vimeo_hash : '' )
        . ( $start ? '#t=' . $start . 's' : '' );

      $embed_args = ['autoplay' => '1', 'dnt' => '1'];

      if ( $vimeo_hash ) {
        $embed_args['h'] = $vimeo_hash;
      }

      if ( !$show_controls ) {
        $embed_args['controls'] = '0';
      }

      if ( $muted ) {
        $embed_args['muted'] = '1';
      }

      if ( $loop ) {
        $embed_args['loop'] = '1';
      }

      $embed_url = add_query_arg( $embed_args, 'https://player.vimeo.com/video/' . $vimeo_id );

      if ( $start ) {
        $embed_url .= '#t=' . $start . 's';
      }
    } else {
      $watch_url = $hosted_url . ( $start ? '#t=' . $start : '' );
    }

    // ------------------------------------------------------------------------
    // Poster: custom image > YouTube auto thumbnail > gradient placeholder.
    // ------------------------------------------------------------------------
    $poster_id = absint( $settings['poster_image']['id'] ?? 0 );
    $poster_url = (string) ( $settings['poster_image']['url'] ?? '' );
    $poster_size = $settings['poster_resolution_size'] ?? 'large';
    $poster_prio = ( $settings['image_loading_priority'] ?? 'lazy' ) === 'high' ? 'high' : 'lazy';

    $auto_poster = '' === $poster_url && '' !== $youtube_id
      ? 'https://i.ytimg.com/vi/' . $youtube_id . '/maxresdefault.jpg'
      : '';
    $auto_poster_fallback = '' !== $auto_poster
      ? 'https://i.ytimg.com/vi/' . $youtube_id . '/hqdefault.jpg'
      : '';

    // Nothing playable and nothing to look at: stay out of the DOM.
    if ( !$has_source && '' === $poster_url ) {
      return;
    }

    // ------------------------------------------------------------------------
    // Wrapper classes + attributes.
    // ------------------------------------------------------------------------
    $wrapper_classes = ['tms-video', 'tms-video--mode-' . ( $ambient ? 'ambient' : $mode )];
    $wrapper_atts = [];

    if ( themeasy_is_elementor_editor() ) {
      $wrapper_classes[] = 'tms-video--static';
    }

    if ( !$ambient && $pulse ) {
      $wrapper_classes[] = 'tms-video--pulse';
    }

    if ( !$ambient && $hover_zoom ) {
      $wrapper_classes[] = 'tms-video--hover-zoom';
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
    // Ambient loop: a native muted autoplay video, no facade, no JS.
    // ------------------------------------------------------------------------
    if ( $ambient ) {
      $player_atts = [
        'class' => 'tms-video__player tms-video__player--ambient',
        // Raw-escaped: themeasy_html_attributes() runs esc_attr() on every value.
        'src' => esc_url_raw( $watch_url ),
        'autoplay' => 'autoplay',
        'muted' => 'muted',
        'loop' => 'loop',
        'playsinline' => 'playsinline',
        'tabindex' => '-1',
        'aria-hidden' => 'true',
      ];

      if ( '' !== $poster_url ) {
        $player_atts['poster'] = esc_url_raw( $poster_url );
      }
      ?>
      <div class="<?php echo esc_attr( $wrapper_classes_output ); ?>"<?php echo $wrapper_atts_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>>
        <div class="tms-video__frame">
          <video <?php echo themeasy_html_attributes( $player_atts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>></video>
        </div><!-- /.tms-video__frame -->
      </div><!-- /.tms-video -->
      <?php
      return;
    }

    // ------------------------------------------------------------------------
    // Facade attributes: a real link so the video stays reachable with no JS.
    // ------------------------------------------------------------------------
    $badge_text = trim( (string) ( $settings['badge_text'] ?? '' ) );
    $label_raw = trim( (string) ( $settings['video_label'] ?? '' ) );
    // Plain __(): themeasy_html_attributes() esc_attr()s the value once at output.
    $aria_label = '' !== $label_raw ? $label_raw : __( 'Play video', 'themeasy-lite' );

    // GLightbox detects self-hosted files by extension, so the lightbox href
    // must not carry the '#t=' media fragment.
    $facade_href = 'lightbox' === $mode && '' !== $hosted_url ? $hosted_url : $watch_url;

    $facade_classes = ['tms-video__facade'];
    $facade_atts = [
      // Raw-escaped: themeasy_html_attributes() runs esc_attr() on every value.
      'href' => esc_url_raw( $facade_href ),
      'aria-label' => $aria_label,
      // The href may point at an external host (watch page / external file):
      // sever window.opener so modifier-clicks cannot tabnab the page.
      'rel' => 'noopener noreferrer',
    ];

    // Poster-first facade with no source yet: a non-interactive <span>, so the
    // play affordance reads as disabled instead of as a dead link. activate()
    // finds no data-* payload and bails, leaving the click inert.
    $facade_tag = $has_source ? 'a' : 'span';

    if ( !$has_source ) {
      $facade_atts = [
        'aria-label' => $aria_label,
        'aria-disabled' => 'true',
      ];
    } elseif ( 'lightbox' === $mode ) {
      // The Themeasy lightbox module auto-binds GLightbox to this class. The
      // explicit type spares GLightbox's extension-sniffing, which fails on
      // extensionless CDN/streaming URLs.
      $facade_classes[] = 'glightbox';
      $facade_atts['data-type'] = 'video';
    } elseif ( '' !== $embed_url ) {
      $facade_atts['data-embed-url'] = esc_url_raw( $embed_url );
    } else {
      $facade_atts['data-video-src'] = esc_url_raw( $watch_url );
      $facade_atts['data-controls'] = $show_controls ? 'true' : 'false';

      if ( $muted ) {
        $facade_atts['data-muted'] = 'true';
      }

      if ( $loop ) {
        $facade_atts['data-loop'] = 'true';
      }
    }

    $facade_atts['class'] = implode( ' ', array_filter( $facade_classes ) );
    $facade_atts_output = themeasy_html_attributes( $facade_atts );

    $play_icon_html = themeasy_render_icon_html( is_array( $settings['play_icon'] ?? null ) ? $settings['play_icon'] : [] );

    // ------------------------------------------------------------------------
    // Render attributes.
    // ------------------------------------------------------------------------
    if ( '' !== $badge_text ) {
      $this->add_render_attribute( 'badge_text', 'class', ['tms-video__badge', 'tms-tag', 'tms-tag--micro'] );
      $this->add_render_attribute( 'badge_text', 'aria-hidden', 'true' );
      $this->add_inline_editing_attributes( 'badge_text', 'none' );
    }

    // ------------------------------------------------------------------------
    // Output.
    // ------------------------------------------------------------------------
    ?>
    <div class="<?php echo esc_attr( $wrapper_classes_output ); ?>"<?php echo $wrapper_atts_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>>
      <div class="tms-video__frame">

        <<?php echo esc_attr( $facade_tag ); ?> <?php echo $facade_atts_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>>

          <?php if ( '' !== $poster_url ) : ?>
            <?php
            themeasy_render_attachment_image(
              $poster_id,
              $poster_size,
              $poster_url,
              '',
              'tms-video__poster',
              [],
              $poster_prio
            );
            ?>
          <?php elseif ( '' !== $auto_poster ) : ?>
            <?php
            themeasy_render_image_tag(
              $auto_poster,
              '',
              'tms-video__poster',
              ['data-poster-fallback' => esc_url_raw( $auto_poster_fallback )]
            );
            ?>
          <?php else : ?>
            <span class="tms-video__poster tms-video__poster--placeholder" aria-hidden="true"></span>
          <?php endif; ?>

          <?php if ( $show_scrim ) : ?>
            <span class="tms-video__scrim" aria-hidden="true"></span>
          <?php endif; ?>

          <?php if ( $play_icon_html ) : ?>
            <span class="tms-video__play" aria-hidden="true">
              <?php if ( $pulse ) : ?>
                <span class="tms-video__play-ring"></span>
              <?php endif; ?>
              <span class="tms-video__play-icon" aria-hidden="true">
                <?php echo $play_icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>
              </span>
            </span>
          <?php endif; ?>

          <?php if ( '' !== $badge_text ) : ?>
            <span <?php $this->print_render_attribute_string( 'badge_text' ); ?>>
              <?php echo esc_html( $badge_text ); ?>
            </span>
          <?php endif; ?>

        </<?php echo esc_attr( $facade_tag ); ?>><!-- /.tms-video__facade -->

      </div><!-- /.tms-video__frame -->
    </div><!-- /.tms-video -->
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
      var source = ['youtube', 'vimeo', 'hosted'].indexOf( settings.video_source ) !== -1
        ? settings.video_source
        : 'youtube';
      var mode = settings.video_mode === 'lightbox' ? 'lightbox' : 'inline';
      var ambient = source === 'hosted' && settings.ambient_mode === 'yes';

      var start = parseInt( settings.start_time, 10 );
      start = ( ! isNaN( start ) && start > 0 ) ? start : 0;
      var end = parseInt( settings.end_time, 10 );
      end = ( ! isNaN( end ) && end > 0 ) ? end : 0;

      // Mirror PHP's `?? 'yes'` exactly: only a MISSING key falls back — the
      // switchers' Off state is '' and must stay falsy ('' || 'yes' would not).
      var showControls = ( settings.video_controls === undefined ? 'yes' : settings.video_controls ) === 'yes';
      var muted = settings.video_muted === 'yes';
      var loop = settings.video_loop === 'yes';
      var pulse = ( settings.play_pulse === undefined ? 'yes' : settings.play_pulse ) === 'yes';
      var showScrim = ( settings.show_scrim === undefined ? 'yes' : settings.show_scrim ) === 'yes';
      var hoverZoom = ( settings.hover_zoom === undefined ? 'yes' : settings.hover_zoom ) === 'yes';

      // Scheme guard for every href/src below. The regex lives in exactly one
      // place; the fallback fails closed rather than duplicating it (a bare ^
      // anchor misses the C0 controls browsers strip before resolving a scheme).
      var safeUrl = ( window.Themeasy && window.Themeasy.safeUrl )
        ? window.Themeasy.safeUrl
        : function () { return ''; };

      // Mirrors parse_youtube_id() / parse_vimeo_parts() on the PHP side.
      var parseYoutubeId = function( url ) {
        var m = /(?:youtube(?:-nocookie)?\.com\/(?:watch\?(?:[^#]*&)?v=|embed\/|shorts\/|live\/)|youtu\.be\/)([A-Za-z0-9_-]{6,20})/i.exec( url || '' );
        return m ? m[1] : '';
      };
      var parseVimeoParts = function( url ) {
        var m = /vimeo\.com\/(?:video\/|channels\/[^\/]+\/|groups\/[^\/]+\/videos\/|event\/)?(\d+)(?:\/([A-Za-z0-9]+))?/i.exec( url || '' );
        return m ? [ m[1], m[2] || '' ] : [ '', '' ];
      };

      // ------------------------------------------------------------------------
      // Resolve the video source.
      // ------------------------------------------------------------------------
      var youtubeId = '';
      var vimeoId = '';
      var vimeoHash = '';
      var hostedUrl = '';

      if ( source === 'youtube' ) {
        youtubeId = parseYoutubeId( settings.youtube_url || '' );
      } else if ( source === 'vimeo' ) {
        var vimeoParts = parseVimeoParts( settings.vimeo_url || '' );
        vimeoId = vimeoParts[0];
        vimeoHash = vimeoParts[1];
      } else {
        hostedUrl = settings.hosted_external === 'yes'
          ? safeUrl( settings.hosted_external_url && settings.hosted_external_url.url ? settings.hosted_external_url.url : '' )
          : safeUrl( settings.hosted_video && settings.hosted_video.url ? settings.hosted_video.url : '' );
      }

      // A source-less widget still renders when a poster was authored (the
      // poster-first facade below); the silent return only covers the truly
      // empty case, decided once the poster is resolved.
      var hasSource = !! ( youtubeId || vimeoId || hostedUrl );

      // The ambient skin is a bare <video>: with no file it has nothing to show.
      ambient = ambient && hasSource;

      // ------------------------------------------------------------------------
      // Canonical watch URL + privacy-enhanced embed URL (attribute parity).
      // ------------------------------------------------------------------------
      var watchUrl = '';
      var embedUrl = '';

      if ( youtubeId ) {
        watchUrl = 'https://www.youtube.com/watch?v=' + youtubeId + ( start ? '&t=' + start + 's' : '' );
        embedUrl = 'https://www.youtube-nocookie.com/embed/' + youtubeId + '?autoplay=1&rel=0&playsinline=1'
          + ( start ? '&start=' + start : '' )
          + ( end > start ? '&end=' + end : '' )
          + ( ! showControls ? '&controls=0' : '' )
          + ( muted ? '&mute=1' : '' )
          + ( loop ? '&loop=1&playlist=' + youtubeId : '' );
      } else if ( vimeoId ) {
        watchUrl = 'https://vimeo.com/' + vimeoId
          + ( vimeoHash ? '/' + vimeoHash : '' )
          + ( start ? '#t=' + start + 's' : '' );
        embedUrl = 'https://player.vimeo.com/video/' + vimeoId + '?autoplay=1&dnt=1'
          + ( vimeoHash ? '&h=' + vimeoHash : '' )
          + ( ! showControls ? '&controls=0' : '' )
          + ( muted ? '&muted=1' : '' )
          + ( loop ? '&loop=1' : '' )
          + ( start ? '#t=' + start + 's' : '' );
      } else {
        watchUrl = hostedUrl + ( start ? '#t=' + start : '' );
      }

      // ------------------------------------------------------------------------
      // Poster: custom image > YouTube auto thumbnail > gradient placeholder.
      // ------------------------------------------------------------------------
      var posterUrl = safeUrl( settings.poster_image && settings.poster_image.url ? settings.poster_image.url : '' );
      var posterFallback = '';

      if ( ! posterUrl && youtubeId ) {
        posterUrl = 'https://i.ytimg.com/vi/' + youtubeId + '/maxresdefault.jpg';
        posterFallback = 'https://i.ytimg.com/vi/' + youtubeId + '/hqdefault.jpg';
      }

      // Nothing playable and nothing to look at: stay out of the DOM.
      if ( ! hasSource && ! posterUrl ) {
        return;
      }

      // ------------------------------------------------------------------------
      // Wrapper classes + attributes.
      // ------------------------------------------------------------------------
      var wrapperClasses = [ 'tms-video', 'tms-video--mode-' + ( ambient ? 'ambient' : mode ), 'tms-video--static' ];
      var wrapperAtts = {};

      if ( ! ambient && pulse ) {
        wrapperClasses.push( 'tms-video--pulse' );
      }

      if ( ! ambient && hoverZoom ) {
        wrapperClasses.push( 'tms-video--hover-zoom' );
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

      // ------------------------------------------------------------------------
      // Facade attributes.
      // ------------------------------------------------------------------------
      var badgeText = ( settings.badge_text || '' ).toString().trim();
      var labelRaw = ( settings.video_label || '' ).toString().trim();
      var ariaLabel = labelRaw !== '' ? labelRaw : '<?php echo esc_js( __( 'Play video', 'themeasy-lite' ) ); ?>';

      // GLightbox detects self-hosted files by extension, so the lightbox href
      // must not carry the '#t=' media fragment.
      var facadeHref = ( mode === 'lightbox' && hostedUrl ) ? hostedUrl : watchUrl;

      var facadeClasses = [ 'tms-video__facade' ];
      var facadeAtts = {
        'href': safeUrl( facadeHref ),
        'aria-label': ariaLabel,
        'rel': 'noopener noreferrer'
      };

      // Poster-first facade with no source yet: a non-interactive <span>, so the
      // play affordance reads as disabled instead of as a dead link.
      var facadeTag = hasSource ? 'a' : 'span';

      if ( ! hasSource ) {
        facadeAtts = {
          'aria-label': ariaLabel,
          'aria-disabled': 'true'
        };
      } else if ( mode === 'lightbox' ) {
        facadeClasses.push( 'glightbox' );
        facadeAtts['data-type'] = 'video';
      } else if ( embedUrl ) {
        facadeAtts['data-embed-url'] = embedUrl;
      } else {
        facadeAtts['data-video-src'] = safeUrl( watchUrl );
        facadeAtts['data-controls'] = showControls ? 'true' : 'false';

        if ( muted ) {
          facadeAtts['data-muted'] = 'true';
        }

        if ( loop ) {
          facadeAtts['data-loop'] = 'true';
        }
      }

      facadeAtts['class'] = facadeClasses.filter( Boolean ).join( ' ' );

      var iconMarkup = ( window.Themeasy && window.Themeasy.renderIconMarkup )
        ? window.Themeasy.renderIconMarkup( view, settings.play_icon, null, {} )
        : '';

      // ------------------------------------------------------------------------
      // Render attributes.
      // ------------------------------------------------------------------------
      if ( badgeText !== '' ) {
        view.addRenderAttribute( 'badge_text', 'class', [ 'tms-video__badge', 'tms-tag', 'tms-tag--micro' ] );
        view.addRenderAttribute( 'badge_text', 'aria-hidden', 'true' );
        view.addInlineEditingAttributes( 'badge_text', 'none' );
      }
    #>

    <# if ( ambient ) { #>

      <div class="{{ wrapperClassStr }}" {{{ Themeasy.htmlAttributes( wrapperAtts ) }}}>
        <div class="tms-video__frame">
          <video class="tms-video__player tms-video__player--ambient"
            src="{{ safeUrl( watchUrl ) }}"
            autoplay muted loop playsinline tabindex="-1" aria-hidden="true"
            <# if ( posterUrl ) { #>poster="{{ posterUrl }}"<# } #>></video>
        </div><!-- /.tms-video__frame -->
      </div><!-- /.tms-video -->

    <# } else { #>

      <div class="{{ wrapperClassStr }}" {{{ Themeasy.htmlAttributes( wrapperAtts ) }}}>
        <div class="tms-video__frame">

          <{{ facadeTag }} {{{ Themeasy.htmlAttributes( facadeAtts ) }}}>

            <# if ( posterUrl ) { #>
              <img class="tms-video__poster"
                src="{{ posterUrl }}"
                alt=""
                loading="lazy"
                decoding="async"
                <# if ( posterFallback ) { #>data-poster-fallback="{{ posterFallback }}"<# } #> />
            <# } else { #>
              <span class="tms-video__poster tms-video__poster--placeholder" aria-hidden="true"></span>
            <# } #>

            <# if ( showScrim ) { #>
              <span class="tms-video__scrim" aria-hidden="true"></span>
            <# } #>

            <# if ( iconMarkup ) { #>
              <span class="tms-video__play" aria-hidden="true">
                <# if ( pulse ) { #>
                  <span class="tms-video__play-ring"></span>
                <# } #>
                <span class="tms-video__play-icon" aria-hidden="true">{{{ iconMarkup }}}</span>
              </span>
            <# } #>

            <# if ( badgeText !== '' ) { #>
              <span {{{ view.getRenderAttributeString( 'badge_text' ) }}}>{{ badgeText }}</span>
            <# } #>

          </{{ facadeTag }}><!-- /.tms-video__facade -->

        </div><!-- /.tms-video__frame -->
      </div><!-- /.tms-video -->

    <# } #>

    <?php
  }
}
