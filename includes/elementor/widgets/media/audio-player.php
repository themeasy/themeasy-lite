<?php
/**
 * Themeasy Elementor Widget: Audio Player
 *
 * A custom-designed HTML5 audio player for podcasts and music, built on a
 * tracks repeater. Two skins — card (cover art, track info, full transport and
 * timeline) and compact (a slim horizontal bar) — plus an optional playlist
 * below. Ships ±15s skip buttons, a playback-speed cycle (1x / 1.25x / 1.5x /
 * 2x), mute, autoplay-next with optional playlist looping, an animated
 * equalizer indicator that doubles as the active-row marker, and a buffered-
 * aware range scrubber with tabular time labels. When one player starts, every
 * other Themeasy audio player on the page pauses.
 *
 * Layering / degradation:
 *  - With no JavaScript the native <audio controls> element plays the first
 *    track and every playlist row is a plain link straight to its audio file.
 *    The custom chrome only appears once JS adds the --enhanced class, so
 *    nothing is ever hidden without a working replacement standing by.
 *  - In the Elementor editor the widget renders --static: the custom chrome
 *    shows in a polished resting state (first track, ~35% progress) and the
 *    engine never runs, so it can never fight inline editing.
 *  - Under prefers-reduced-motion the equalizer stops animating and every
 *    transition is dropped; the player itself stays fully usable.
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
 * Responsible for registering controls and rendering the Audio Player widget.
 */
class AudioPlayer extends Widget_Base {
  public function get_name() {
    return 'themeasy-audio-player';
  }

  public function get_title() {
    return esc_html__( 'Audio Player', 'themeasy-lite' );
  }

  public function get_icon() {
    return 'eicon-headphones';
  }

  public function get_custom_help_url() {
    return 'https://hub.themeasy.co/docs';
  }

  public function get_categories() {
    return ['themeasy_media_category', 'themeasy_content_category'];
  }

  public function get_keywords() {
    return ['themeasy', 'audio', 'player', 'podcast', 'music', 'playlist', 'sound', 'mp3'];
  }

  public function get_style_depends() {
    return ['themeasy-shared', 'themeasy-audio-player'];
  }

  public function get_script_depends() {
    return ['themeasy-audio-player'];
  }

  /**
   * Register widget customization controls.
   *
   * @return void
   */
  protected function register_controls() {
    // ------------------------------------------------------------------------
    // Content section: Tracks
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'audio_player_tracks_section',
        [
          'label' => esc_html__( 'Tracks', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $repeater = new Repeater();

      $repeater->add_control(
        'track_title',
        [
          'label' => esc_html__( 'Title', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXT,
          'default' => '',
          'placeholder' => esc_html__( 'Track title', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
        ]
      );

      $repeater->add_control(
        'track_artist',
        [
          'label' => esc_html__( 'Artist', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXT,
          'default' => '',
          'placeholder' => esc_html__( 'Artist or show name', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
        ]
      );

      $repeater->add_control(
        'track_cover',
        [
          'label' => esc_html__( 'Cover', 'themeasy-lite' ),
          'description' => esc_html__( 'Artwork shown while this track is selected.', 'themeasy-lite' ),
          'type' => Controls_Manager::MEDIA,
          'media_types' => ['image'],
          'dynamic' => ['active' => true],
        ]
      );

      $repeater->add_control(
        'track_external',
        [
          'label' => esc_html__( 'External File', 'themeasy-lite' ),
          'description' => esc_html__( 'Point to an audio file hosted outside the Media Library (e.g. a CDN or a podcast host).', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => '',
          'separator' => 'before',
        ]
      );

      $repeater->add_control(
        'track_audio',
        [
          'label' => esc_html__( 'Audio File', 'themeasy-lite' ),
          'type' => Controls_Manager::MEDIA,
          'media_types' => ['audio'],
          'dynamic' => ['active' => true],
          'condition' => [
            'track_external' => '',
          ],
        ]
      );

      $repeater->add_control(
        'track_audio_url',
        [
          'label' => esc_html__( 'File URL', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::URL,
          'options' => false,
          'placeholder' => esc_html__( 'https://cdn.your-site.com/episode-01.mp3', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
          'condition' => [
            'track_external' => 'yes',
          ],
        ]
      );

      $repeater->add_control(
        'track_duration',
        [
          'label' => esc_html__( 'Duration', 'themeasy-lite' ),
          'description' => esc_html__( 'Shown until the browser reads the real length from the file. Format: 3:42 or 1:04:20.', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'default' => '',
          'placeholder' => '3:42',
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'tracks',
        [
          'label' => esc_html__( 'Tracks', 'themeasy-lite' ),
          'type' => Controls_Manager::REPEATER,
          'fields' => $repeater->get_controls(),
          'default' => [
            [
              'track_title' => esc_html__( 'Midnight Signals', 'themeasy-lite' ),
              'track_artist' => esc_html__( 'Aria Lune', 'themeasy-lite' ),
              'track_duration' => '3:42',
            ],
            [
              'track_title' => esc_html__( 'Paper Cities', 'themeasy-lite' ),
              'track_artist' => esc_html__( 'Aria Lune', 'themeasy-lite' ),
              'track_duration' => '4:05',
            ],
          ],
          'title_field' => '{{{ track_title }}}',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Layout
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'audio_player_layout_section',
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
          'default' => 'card',
          'options' => [
            'card' => esc_html__( 'Card (cover + full controls)', 'themeasy-lite' ),
            'compact' => esc_html__( 'Compact (slim bar)', 'themeasy-lite' ),
          ],
        ]
      );

      $this->add_control(
        'show_cover',
        [
          'label' => esc_html__( 'Cover Art', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Show', 'themeasy-lite' ),
          'label_off' => esc_html__( 'Hide', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
        ]
      );

      $this->add_control(
        'show_playlist',
        [
          'label' => esc_html__( 'Playlist', 'themeasy-lite' ),
          'description' => esc_html__( 'Shown when the widget has more than one track.', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Show', 'themeasy-lite' ),
          'label_off' => esc_html__( 'Hide', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
        ]
      );

      $this->add_control(
        'playlist_numbers',
        [
          'label' => esc_html__( 'Track Numbers', 'themeasy-lite' ),
          'description' => esc_html__( 'Numbers each playlist row (01, 02, ...). The playing row swaps its number for the equalizer.', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Show', 'themeasy-lite' ),
          'label_off' => esc_html__( 'Hide', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
          'condition' => [
            'show_playlist' => 'yes',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Player Settings
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'audio_player_settings_section',
        [
          'label' => esc_html__( 'Player Settings', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $this->add_control(
        'autoplay_next',
        [
          'label' => esc_html__( 'Autoplay Next', 'themeasy-lite' ),
          'description' => esc_html__( 'Continues to the next track when the current one ends.', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
        ]
      );

      $this->add_control(
        'loop_playlist',
        [
          'label' => esc_html__( 'Loop Playlist', 'themeasy-lite' ),
          'description' => esc_html__( 'Wraps from the last track back to the first instead of stopping.', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => '',
        ]
      );

      $this->add_control(
        'show_skip',
        [
          'label' => esc_html__( 'Skip Buttons', 'themeasy-lite' ),
          'description' => esc_html__( 'Jump back or forward 15 seconds — the podcast staple.', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Show', 'themeasy-lite' ),
          'label_off' => esc_html__( 'Hide', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'show_speed',
        [
          'label' => esc_html__( 'Speed Button', 'themeasy-lite' ),
          'description' => esc_html__( 'Cycles the playback rate through 1x, 1.25x, 1.5x and 2x.', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Show', 'themeasy-lite' ),
          'label_off' => esc_html__( 'Hide', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
        ]
      );

      $this->add_control(
        'show_playing_indicator',
        [
          'label' => esc_html__( 'Playing Indicator', 'themeasy-lite' ),
          'description' => esc_html__( 'Animated equalizer bars next to the track title and on the playing row.', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Show', 'themeasy-lite' ),
          'label_off' => esc_html__( 'Hide', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
        ]
      );

      $this->add_control(
        'preload',
        [
          'label' => esc_html__( 'Preload', 'themeasy-lite' ),
          'description' => esc_html__( 'Metadata fetches just the track length. None waits for the first play — lighter on bandwidth.', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'metadata',
          'options' => [
            'metadata' => esc_html__( 'Metadata (length only)', 'themeasy-lite' ),
            'none' => esc_html__( 'None', 'themeasy-lite' ),
          ],
          'separator' => 'before',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Player Box
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'audio_player_box_styles_section',
        [
          'label' => esc_html__( 'Player Box', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_control(
        'box_background_color',
        [
          'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-audio-player' => 'background-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'box_padding',
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'em', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-audio-player'
              => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Border::get_type(),
        [
          'name' => 'box_border',
          'selector' => '{{WRAPPER}} .tms-audio-player',
        ]
      );

      $this->add_responsive_control(
        'box_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', '%', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-audio-player'
              => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Box_Shadow::get_type(),
        [
          'name' => 'box_box_shadow',
          'label' => esc_html__( 'Box Shadow', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-audio-player',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Cover
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'audio_player_cover_styles_section',
        [
          'label' => esc_html__( 'Cover', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'show_cover' => 'yes',
          ],
        ]
      );

      $this->add_responsive_control(
        'cover_size',
        [
          'label' => esc_html__( 'Size', 'themeasy-lite' ),
          'description' => esc_html__( 'The artwork is always square — this sets its width.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem'],
          'range' => [
            'px' => ['min' => 32, 'max' => 320, 'step' => 1],
            'rem' => ['min' => 2, 'max' => 20, 'step' => 0.125],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-audio-player' => '--tms-audio-player-cover-size: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'cover_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', '%', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-audio-player__cover'
              => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Box_Shadow::get_type(),
        [
          'name' => 'cover_box_shadow',
          'label' => esc_html__( 'Box Shadow', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-audio-player__cover',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Track Info
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'audio_player_info_styles_section',
        [
          'label' => esc_html__( 'Track Info', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_control(
        'title_heading',
        [
          'label' => esc_html__( 'Title', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
        ]
      );

      $this->add_control(
        'title_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-audio-player__title' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'title_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-audio-player__title',
        ]
      );

      $this->add_control(
        'artist_heading',
        [
          'label' => esc_html__( 'Artist', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'artist_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-audio-player__artist' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'artist_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-audio-player__artist',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Controls
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'audio_player_controls_styles_section',
        [
          'label' => esc_html__( 'Controls', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_control(
        'play_heading',
        [
          'label' => esc_html__( 'Play Button', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
        ]
      );

      $this->start_controls_tabs(
        'play_state_tabs'
        );

        $this->start_controls_tab(
          'play_normal_tab',
            [
              'label' => esc_html__( 'Normal', 'themeasy-lite' ),
            ]
          );

          $this->add_control(
            'play_color',
            [
              'label' => esc_html__( 'Icon Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-audio-player__button--play' => 'color: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            'play_background',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-audio-player__button--play' => 'background-color: {{VALUE}};',
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
            'play_hover_color',
            [
              'label' => esc_html__( 'Icon Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-audio-player__button--play:hover,
                 {{WRAPPER}} .tms-audio-player__button--play:focus-visible' => 'color: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            'play_hover_background',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-audio-player__button--play:hover,
                 {{WRAPPER}} .tms-audio-player__button--play:focus-visible' => 'background-color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

      $this->add_responsive_control(
        'play_size',
        [
          'label' => esc_html__( 'Size', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem'],
          'range' => [
            'px' => ['min' => 36, 'max' => 96, 'step' => 1],
            'rem' => ['min' => 2.25, 'max' => 6, 'step' => 0.125],
          ],
          'separator' => 'before',
          'selectors' => [
            '{{WRAPPER}} .tms-audio-player' => '--tms-audio-player-play-size: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'play_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', '%', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-audio-player__button--play'
              => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'control_heading',
        [
          'label' => esc_html__( 'Secondary Buttons', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->start_controls_tabs(
        'control_state_tabs'
        );

        $this->start_controls_tab(
          'control_normal_tab',
            [
              'label' => esc_html__( 'Normal', 'themeasy-lite' ),
            ]
          );

          $this->add_control(
            'control_color',
            [
              'label' => esc_html__( 'Icon Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-audio-player__button--secondary' => 'color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

        $this->start_controls_tab(
          'control_hover_tab',
            [
              'label' => esc_html__( 'Hover', 'themeasy-lite' ),
            ]
          );

          $this->add_control(
            'control_hover_color',
            [
              'label' => esc_html__( 'Icon Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-audio-player__button--secondary:hover,
                 {{WRAPPER}} .tms-audio-player__button--secondary:focus-visible' => 'color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

      $this->add_responsive_control(
        'control_size',
        [
          'label' => esc_html__( 'Size', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem'],
          'range' => [
            'px' => ['min' => 14, 'max' => 40, 'step' => 1],
            'rem' => ['min' => 0.875, 'max' => 2.5, 'step' => 0.125],
          ],
          'separator' => 'before',
          'selectors' => [
            '{{WRAPPER}} .tms-audio-player' => '--tms-audio-player-control-size: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Progress & Time
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'audio_player_progress_styles_section',
        [
          'label' => esc_html__( 'Progress & Time', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_control(
        'progress_track_color',
        [
          'label' => esc_html__( 'Track Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-audio-player' => '--tms-audio-player-track-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'progress_fill_color',
        [
          'label' => esc_html__( 'Fill Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-audio-player' => '--tms-audio-player-fill-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'progress_handle_color',
        [
          'label' => esc_html__( 'Handle Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-audio-player' => '--tms-audio-player-handle-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'progress_height',
        [
          'label' => esc_html__( 'Track Height', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => ['min' => 2, 'max' => 16, 'step' => 1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-audio-player' => '--tms-audio-player-progress-height: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'time_heading',
        [
          'label' => esc_html__( 'Time Labels', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'time_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-audio-player__time' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'time_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-audio-player__time',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Playlist
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'audio_player_playlist_styles_section',
        [
          'label' => esc_html__( 'Playlist', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'show_playlist' => 'yes',
          ],
        ]
      );

      $this->start_controls_tabs(
        'playlist_item_tabs'
        );

        $this->start_controls_tab(
          'playlist_normal_tab',
            [
              'label' => esc_html__( 'Normal', 'themeasy-lite' ),
            ]
          );

          $this->add_control(
            'item_title_color',
            [
              'label' => esc_html__( 'Title Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-audio-player__item-title' => 'color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

        $this->start_controls_tab(
          'playlist_hover_tab',
            [
              'label' => esc_html__( 'Hover', 'themeasy-lite' ),
            ]
          );

          $this->add_control(
            'item_hover_title_color',
            [
              'label' => esc_html__( 'Title Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-audio-player__item-link:hover .tms-audio-player__item-title,
                 {{WRAPPER}} .tms-audio-player__item-link:focus-visible .tms-audio-player__item-title'
                  => 'color: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            'item_hover_background_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-audio-player__item-link:hover,
                 {{WRAPPER}} .tms-audio-player__item-link:focus-visible' => 'background-color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

        $this->start_controls_tab(
          'playlist_active_tab',
            [
              'label' => esc_html__( 'Active', 'themeasy-lite' ),
            ]
          );

          $this->add_control(
            'item_active_title_color',
            [
              'label' => esc_html__( 'Title Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-audio-player__item--active .tms-audio-player__item-title'
                  => 'color: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            'item_active_background_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-audio-player__item--active .tms-audio-player__item-link'
                  => 'background-color: {{VALUE}};',
              ],
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

      $this->add_control(
        'divider_color',
        [
          'label' => esc_html__( 'Divider Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'separator' => 'before',
          'selectors' => [
            '{{WRAPPER}} .tms-audio-player' => '--tms-audio-player-divider-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'item_title_typography',
          'label' => esc_html__( 'Title Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-audio-player__item-title',
        ]
      );

      $this->add_responsive_control(
        'item_padding',
        [
          'label' => esc_html__( 'Item Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'em', 'rem'],
          'selectors' => [
            '{{WRAPPER}} .tms-audio-player__item-link'
              => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'item_meta_heading',
        [
          'label' => esc_html__( 'Number & Duration', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'item_meta_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-audio-player__item-index,
             {{WRAPPER}} .tms-audio-player__item-duration' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'item_meta_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-audio-player__item-index,
             {{WRAPPER}} .tms-audio-player__item-duration',
        ]
      );

      $this->add_control(
        'indicator_heading',
        [
          'label' => esc_html__( 'Playing Indicator', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
          'condition' => [
            'show_playing_indicator' => 'yes',
          ],
        ]
      );

      $this->add_control(
        'indicator_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-audio-player' => '--tms-audio-player-eq-color: {{VALUE}};',
          ],
          'condition' => [
            'show_playing_indicator' => 'yes',
          ],
        ]
      );

    $this->end_controls_section();

  }

  /**
   * Normalize the repeater rows into render-ready track arrays.
   *
   * Rows that carry no title, artist or source at all are dropped, but every
   * kept row PRESERVES its original repeater index so render() and
   * content_template() resolve the same inline-editing keys.
   *
   * @param array $settings Widget settings.
   * @return array<int, array{index: int, title: string, artist: string, src: string, cover: string, cover_id: int, duration: string}>
   */
  protected function get_prepared_tracks( array $settings ): array {
    $tracks = [];

    if ( empty( $settings['tracks'] ) || !is_array( $settings['tracks'] ) ) {
      return $tracks;
    }

    foreach ( $settings['tracks'] as $index => $item ) {
      $title = trim( (string) ( $item['track_title'] ?? '' ) );
      $artist = trim( (string) ( $item['track_artist'] ?? '' ) );

      $src = ( $item['track_external'] ?? '' ) === 'yes'
        ? (string) ( $item['track_audio_url']['url'] ?? '' )
        : (string) ( $item['track_audio']['url'] ?? '' );
      $src = trim( $src );

      // A row with nothing at all is noise — everything else stays, so an
      // in-progress track still draws its chrome in the editor.
      if ( '' === $title && '' === $artist && '' === $src ) {
        continue;
      }

      $tracks[] = [
        'index' => (int) $index,
        'title' => $title,
        'artist' => $artist,
        'src' => $src,
        'cover' => trim( (string) ( $item['track_cover']['url'] ?? '' ) ),
        'cover_id' => absint( $item['track_cover']['id'] ?? 0 ),
        'duration' => trim( (string) ( $item['track_duration'] ?? '' ) ),
      ];
    }

    return $tracks;
  }

  /**
   * Parse an authored duration label ("3:42", "1:04:20") into seconds.
   *
   * @param string $label Duration label.
   * @return float Seconds, or 0 when the label is not parseable.
   */
  protected function parse_clock( string $label ): float {
    $label = trim( $label );

    if ( '' === $label || !preg_match( '/^\d{1,2}(:\d{1,2}){1,2}$/', $label ) ) {
      return 0;
    }

    $parts = array_map( 'intval', explode( ':', $label ) );
    $seconds = 0;

    foreach ( $parts as $part ) {
      $seconds = ( $seconds * 60 ) + $part;
    }

    return (float) $seconds;
  }

  /**
   * Format seconds as m:ss, or h:mm:ss once past the hour.
   *
   * @param float $seconds Seconds to format.
   * @return string
   */
  protected function format_clock( float $seconds ): string {
    $total = (int) max( 0, floor( $seconds ) );
    $hours = (int) floor( $total / 3600 );
    $minutes = (int) floor( ( $total % 3600 ) / 60 );
    $rest = $total % 60;

    if ( $hours > 0 ) {
      return sprintf( '%d:%02d:%02d', $hours, $minutes, $rest );
    }

    return sprintf( '%d:%02d', $minutes, $rest );
  }

  /**
   * Static template glyphs shared by render() and content_template().
   *
   * These are decorative chrome, not the ICONS-control contract: they keep
   * their own explicit classes so the widget CSS can size and color them.
   *
   * @param string $name Glyph key.
   * @return string Inline SVG markup.
   */
  protected function get_icon_markup( string $name ): string {
    $class = 'tms-audio-player__icon tms-audio-player__icon--' . $name;

    $filled = '<svg class="%1$s" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">%2$s</svg>';
    $stroked = '<svg class="%1$s" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"'
      . ' stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%2$s</svg>';

    $speaker = '<path d="M12.2 4.9 7.4 8.8H4.5a1 1 0 0 0-1 1v4.4a1 1 0 0 0 1 1h2.9l4.8 3.9V4.9Z" fill="currentColor"/>';

    switch ( $name ) {
      case 'play':
        return sprintf(
          $filled,
          esc_attr( $class ),
          '<path d="M8 5.2v13.6a1 1 0 0 0 1.53.85l10.72-6.8a1 1 0 0 0 0-1.7L9.53 4.35A1 1 0 0 0 8 5.2Z"/>'
        );

      case 'pause':
        return sprintf(
          $filled,
          esc_attr( $class ),
          '<rect x="7.5" y="4.75" width="3.5" height="14.5" rx="1.25"/>'
            . '<rect x="13" y="4.75" width="3.5" height="14.5" rx="1.25"/>'
        );

      case 'prev':
        return sprintf(
          $filled,
          esc_attr( $class ),
          '<rect x="5.5" y="5.5" width="2.4" height="13" rx="1.2"/>'
            . '<path d="M18.5 6.4v11.2a1 1 0 0 1-1.53.85l-8.35-5.6a1 1 0 0 1 0-1.7l8.35-5.6a1 1 0 0 1 1.53.85Z"/>'
        );

      case 'next':
        return sprintf(
          $filled,
          esc_attr( $class ),
          '<path d="M5.5 6.4v11.2a1 1 0 0 0 1.53.85l8.35-5.6a1 1 0 0 0 0-1.7L7.03 5.55A1 1 0 0 0 5.5 6.4Z"/>'
            . '<rect x="16.1" y="5.5" width="2.4" height="13" rx="1.2"/>'
        );

      case 'rewind':
        return sprintf(
          $stroked,
          esc_attr( $class ),
          '<path d="M12 4.8A7.2 7.2 0 1 1 6.91 6.91"/><path d="M14.4 2.6 12 4.8l2.4 2.2"/>'
        );

      case 'forward':
        return sprintf(
          $stroked,
          esc_attr( $class ),
          '<path d="M12 4.8A7.2 7.2 0 1 0 17.09 6.91"/><path d="M9.6 2.6 12 4.8l-2.4 2.2"/>'
        );

      case 'volume':
        return sprintf(
          $stroked,
          esc_attr( $class ),
          $speaker . '<path d="M15.8 9.6a3.4 3.4 0 0 1 0 4.8"/><path d="M18.4 7.2a7 7 0 0 1 0 9.6"/>'
        );

      case 'muted':
        return sprintf(
          $stroked,
          esc_attr( $class ),
          $speaker . '<path d="M16.4 9.8 21 14.4"/><path d="M21 9.8 16.4 14.4"/>'
        );
    }

    return '';
  }

  /**
   * Output the four equalizer bars.
   *
   * @param string $modifier BEM element name for the wrapper.
   * @return void
   */
  protected function render_equalizer( string $modifier ): void {
    ?>
    <span class="tms-audio-player__<?php echo esc_attr( $modifier ); ?> tms-audio-player__eq" aria-hidden="true">
      <span class="tms-audio-player__eq-bar"></span>
      <span class="tms-audio-player__eq-bar"></span>
      <span class="tms-audio-player__eq-bar"></span>
      <span class="tms-audio-player__eq-bar"></span>
    </span>
    <?php
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
    $tracks = $this->get_prepared_tracks( $settings );

    // Early return if no content.
    if ( empty( $tracks ) ) {
      return;
    }

    $skin = ( $settings['skin'] ?? 'card' ) === 'compact' ? 'compact' : 'card';
    $preload = ( $settings['preload'] ?? 'metadata' ) === 'none' ? 'none' : 'metadata';

    $show_cover = ( $settings['show_cover'] ?? 'yes' ) === 'yes';
    $show_playlist = ( $settings['show_playlist'] ?? 'yes' ) === 'yes';
    $playlist_numbers = ( $settings['playlist_numbers'] ?? 'yes' ) === 'yes';
    $autoplay_next = ( $settings['autoplay_next'] ?? 'yes' ) === 'yes';
    $loop_playlist = ( $settings['loop_playlist'] ?? '' ) === 'yes';
    $show_skip = ( $settings['show_skip'] ?? 'yes' ) === 'yes';
    $show_speed = ( $settings['show_speed'] ?? 'yes' ) === 'yes';
    $show_indicator = ( $settings['show_playing_indicator'] ?? 'yes' ) === 'yes';

    $first = $tracks[0];
    $has_multiple = count( $tracks ) > 1;
    $render_playlist = $show_playlist && $has_multiple;

    // The cover column only exists when some track can actually fill it.
    $has_artwork = false;

    foreach ( $tracks as $track ) {
      if ( '' !== $track['cover'] ) {
        $has_artwork = true;
        break;
      }
    }

    $render_cover = $show_cover && $has_artwork;

    // Editor preview is JS-inert: show the chrome in a designed resting state
    // instead of an empty 0% bar.
    $is_static = themeasy_is_elementor_editor();
    $progress = $is_static ? 35 : 0;
    $total_seconds = $this->parse_clock( $first['duration'] );
    $current_label = $is_static && $total_seconds > 0
      ? $this->format_clock( $total_seconds * 0.35 )
      : '0:00';
    $total_label = '' !== $first['duration'] ? $first['duration'] : '0:00';

    // Plain __(): every use site escapes once, so pre-escaping here would ship
    // a literal &#039; to the visitor in any translation carrying an apostrophe.
    $title_label = '' !== $first['title'] ? $first['title'] : __( 'Untitled track', 'themeasy-lite' );

    // ------------------------------------------------------------------------
    // Wrapper classes + attributes.
    // ------------------------------------------------------------------------
    $wrapper_classes = ['tms-audio-player', 'tms-audio-player--' . $skin];

    if ( $render_cover ) {
      $wrapper_classes[] = 'tms-audio-player--with-cover';
    }

    if ( $is_static ) {
      $wrapper_classes[] = 'tms-audio-player--static';
    }

    $wrapper_atts = [
      'data-autoplay-next' => $autoplay_next ? 'true' : 'false',
      'data-loop-playlist' => $loop_playlist ? 'true' : 'false',
      'style' => '--tms-audio-player-progress: ' . $progress . '%;',
    ];

    $wrapper_classes_output = implode( ' ', array_filter( $wrapper_classes ) );

    // ------------------------------------------------------------------------
    // Native element — the no-JS player AND the engine the custom UI drives.
    // ------------------------------------------------------------------------
    $native_atts = [
      'class' => 'tms-audio-player__native',
      'preload' => $preload,
    ];

    if ( '' !== $first['src'] ) {
      // Raw-escaped: themeasy_html_attributes() runs esc_attr() on every value.
      $native_atts['src'] = esc_url_raw( $first['src'] );
      $native_atts['controls'] = 'controls';
    }

    // ------------------------------------------------------------------------
    // Button labels. Plain __() inside sprintf(): the values are escaped once
    // at output with esc_attr(); the JS re-templates them on every track swap.
    // ------------------------------------------------------------------------
    /* translators: %s: track title. */
    $label_play = __( 'Play %s', 'themeasy-lite' );
    /* translators: %s: track title. */
    $label_pause = __( 'Pause %s', 'themeasy-lite' );
    /* translators: %s: playback rate, e.g. 1.5x. */
    $label_speed = __( 'Playback speed: %s', 'themeasy-lite' );

    // ------------------------------------------------------------------------
    // Output.
    // ------------------------------------------------------------------------
    ?>
    <div class="<?php echo esc_attr( $wrapper_classes_output ); ?>"<?php themeasy_print_html_attributes( $wrapper_atts ); ?>>

      <audio<?php themeasy_print_html_attributes( $native_atts ); ?>></audio>

      <div class="tms-audio-player__ui" role="group" aria-label="<?php echo esc_attr__( 'Audio player', 'themeasy-lite' ); ?>">

        <?php if ( $render_cover ) : ?>
          <div class="tms-audio-player__cover">
            <span class="tms-audio-player__cover-fallback" aria-hidden="true"></span>
            <?php if ( '' !== $first['cover'] ) : ?>
              <?php
              themeasy_render_attachment_image(
                $first['cover_id'],
                'medium_large',
                $first['cover'],
                '',
                'tms-audio-player__cover-img',
                [],
                'lazy'
              );
              ?>
            <?php else : ?>
              <?php // The column renders when ANY track has art, so ship the node
                // even when the first track lacks it - otherwise the JS has
                // nothing to swap and later covers could never appear. ?>
              <img class="tms-audio-player__cover-img" src="" alt="" hidden loading="lazy" decoding="async" />
            <?php endif; ?>
          </div><!-- /.tms-audio-player__cover -->
        <?php endif; ?>

        <div class="tms-audio-player__head">
          <?php if ( $show_indicator ) : ?>
            <?php $this->render_equalizer( 'head-eq' ); ?>
          <?php endif; ?>
          <span class="tms-audio-player__titles">
            <span class="tms-audio-player__title"><?php echo esc_html( $title_label ); ?></span>
            <?php if ( '' !== $first['artist'] ) : ?>
              <span class="tms-audio-player__artist"><?php echo esc_html( $first['artist'] ); ?></span>
            <?php endif; ?>
          </span>
        </div><!-- /.tms-audio-player__head -->

        <div class="tms-audio-player__timeline">
          <span class="tms-audio-player__time tms-audio-player__time--current"><?php echo esc_html( $current_label ); ?></span>
          <input
            type="range"
            class="tms-audio-player__scrubber"
            min="0"
            max="1000"
            step="1"
            value="<?php echo esc_attr( (string) ( $progress * 10 ) ); ?>"
            aria-label="<?php echo esc_attr__( 'Seek', 'themeasy-lite' ); ?>"
            aria-valuetext="<?php echo esc_attr( $current_label ); ?>"
          />
          <span class="tms-audio-player__time tms-audio-player__time--total"><?php echo esc_html( $total_label ); ?></span>
        </div><!-- /.tms-audio-player__timeline -->

        <div class="tms-audio-player__transport">

          <?php if ( $has_multiple ) : ?>
            <button
              type="button"
              class="tms-audio-player__button tms-audio-player__button--secondary tms-audio-player__button--prev"
              aria-label="<?php echo esc_attr__( 'Previous track', 'themeasy-lite' ); ?>"
            >
              <?php echo wp_kses( $this->get_icon_markup( 'prev' ), themeasy_get_icon_kses_tags() ); ?>
            </button>
          <?php endif; ?>

          <?php if ( $show_skip ) : ?>
            <button
              type="button"
              class="tms-audio-player__button tms-audio-player__button--secondary tms-audio-player__button--rewind"
              aria-label="<?php echo esc_attr__( 'Rewind 15 seconds', 'themeasy-lite' ); ?>"
            >
              <?php echo wp_kses( $this->get_icon_markup( 'rewind' ), themeasy_get_icon_kses_tags() ); ?>
              <span class="tms-audio-player__skip-amount" aria-hidden="true">15</span>
            </button>
          <?php endif; ?>

          <button
            type="button"
            class="tms-audio-player__button tms-audio-player__button--play"
            aria-label="<?php echo esc_attr( sprintf( $label_play, $title_label ) ); ?>"
            data-label-play="<?php echo esc_attr( $label_play ); ?>"
            data-label-pause="<?php echo esc_attr( $label_pause ); ?>"
          >
            <?php echo wp_kses( $this->get_icon_markup( 'play' ), themeasy_get_icon_kses_tags() ); ?>
            <?php echo wp_kses( $this->get_icon_markup( 'pause' ), themeasy_get_icon_kses_tags() ); ?>
          </button>

          <?php if ( $show_skip ) : ?>
            <button
              type="button"
              class="tms-audio-player__button tms-audio-player__button--secondary tms-audio-player__button--forward"
              aria-label="<?php echo esc_attr__( 'Forward 15 seconds', 'themeasy-lite' ); ?>"
            >
              <?php echo wp_kses( $this->get_icon_markup( 'forward' ), themeasy_get_icon_kses_tags() ); ?>
              <span class="tms-audio-player__skip-amount" aria-hidden="true">15</span>
            </button>
          <?php endif; ?>

          <?php if ( $has_multiple ) : ?>
            <button
              type="button"
              class="tms-audio-player__button tms-audio-player__button--secondary tms-audio-player__button--next"
              aria-label="<?php echo esc_attr__( 'Next track', 'themeasy-lite' ); ?>"
            >
              <?php echo wp_kses( $this->get_icon_markup( 'next' ), themeasy_get_icon_kses_tags() ); ?>
            </button>
          <?php endif; ?>

          <span class="tms-audio-player__extras">
            <?php if ( $show_speed ) : ?>
              <button
                type="button"
                class="tms-audio-player__button tms-audio-player__button--secondary tms-audio-player__button--speed"
                aria-label="<?php echo esc_attr( sprintf( $label_speed, '1×' ) ); ?>"
                data-label-speed="<?php echo esc_attr( $label_speed ); ?>"
              >
                <span class="tms-audio-player__speed-label" aria-hidden="true">1×</span>
              </button>
            <?php endif; ?>

            <button
              type="button"
              class="tms-audio-player__button tms-audio-player__button--secondary tms-audio-player__button--mute"
              aria-label="<?php echo esc_attr__( 'Mute', 'themeasy-lite' ); ?>"
              data-label-mute="<?php echo esc_attr__( 'Mute', 'themeasy-lite' ); ?>"
              data-label-unmute="<?php echo esc_attr__( 'Unmute', 'themeasy-lite' ); ?>"
            >
              <?php echo wp_kses( $this->get_icon_markup( 'volume' ), themeasy_get_icon_kses_tags() ); ?>
              <?php echo wp_kses( $this->get_icon_markup( 'muted' ), themeasy_get_icon_kses_tags() ); ?>
            </button>
          </span><!-- /.tms-audio-player__extras -->

        </div><!-- /.tms-audio-player__transport -->

      </div><!-- /.tms-audio-player__ui -->

      <?php if ( $render_playlist ) : ?>
        <ol class="tms-audio-player__playlist">
          <?php foreach ( $tracks as $position => $track ) : ?>
            <?php
            $title_key = $this->get_repeater_setting_key( 'track_title', 'tracks', $track['index'] );
            $this->add_render_attribute( $title_key, 'class', 'tms-audio-player__item-title' );
            $this->add_inline_editing_attributes( $title_key, 'none' );

            $item_classes = ['tms-audio-player__item'];

            if ( 0 === $position ) {
              $item_classes[] = 'tms-audio-player__item--active';
            }

            $item_title = '' !== $track['title'] ? $track['title'] : __( 'Untitled track', 'themeasy-lite' );

            $link_atts = [
              'class' => 'tms-audio-player__item-link',
              // Raw-escaped: themeasy_html_attributes() runs esc_attr() on every value.
              'data-src' => '' !== $track['src'] ? esc_url_raw( $track['src'] ) : '',
              'data-title' => $item_title,
              'data-artist' => $track['artist'],
              'data-cover' => '' !== $track['cover'] ? esc_url_raw( $track['cover'] ) : '',
              'data-duration' => $track['duration'],
            ];

            // No source yet (the file is still being picked): keep the row as a
            // non-link placeholder rather than a dead href.
            if ( '' !== $track['src'] ) {
              $link_atts['href'] = esc_url_raw( $track['src'] );
            }
            ?>
            <li class="<?php echo esc_attr( implode( ' ', $item_classes ) ); ?>">
              <a<?php themeasy_print_html_attributes( $link_atts ); ?>>
                <?php if ( $playlist_numbers ) : ?>
                  <span class="tms-audio-player__item-index" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $position + 1 ) ); ?></span>
                <?php endif; ?>
                <?php if ( $show_indicator ) : ?>
                  <?php $this->render_equalizer( 'item-eq' ); ?>
                <?php endif; ?>
                <span <?php $this->print_render_attribute_string( $title_key ); ?>><?php echo esc_html( $item_title ); ?></span>
                <?php // Always emitted, even empty: the JS backfills it from the
                  // file's real metadata once loadedmetadata lands. ?>
                <span class="tms-audio-player__item-duration"><?php echo esc_html( $track['duration'] ); ?></span>
              </a>
            </li>
          <?php endforeach; ?>
        </ol><!-- /.tms-audio-player__playlist -->
      <?php endif; ?>

    </div><!-- /.tms-audio-player -->
    <?php
  }

  /**
   * Render the live preview template used by the Elementor editor.
   *
   * Hard-coded --static: the chrome shows in its resting state (first track,
   * ~35% progress) and the engine never runs, so it cannot fight inline
   * editing on the playlist titles.
   *
   * @return void
   */
  protected function content_template() {
    ?>
    <#
      // ------------------------------------------------------------------------
      // Settings.
      // ------------------------------------------------------------------------
      // Scheme guard for every URL interpolated below — the editor view has no
      // add_link_attributes()/esc_url(). Fail closed when the helper is absent:
      // the guard lives in exactly one place so it can never drift narrow again.
      var safeUrl = ( window.Themeasy && window.Themeasy.safeUrl )
        ? window.Themeasy.safeUrl
        : function () { return ''; };

      var tracks = [];

      if ( settings.tracks && Array.isArray( settings.tracks ) ) {
        _.each( settings.tracks, function ( item, index ) {
          var title = String( ( item && item.track_title ) || '' ).trim();
          var artist = String( ( item && item.track_artist ) || '' ).trim();

          var src = item.track_external === 'yes'
            ? String( ( item.track_audio_url && item.track_audio_url.url ) || '' ).trim()
            : String( ( item.track_audio && item.track_audio.url ) || '' ).trim();

          if ( title === '' && artist === '' && src === '' ) {
            return;
          }

          tracks.push({
            index: index,
            title: title,
            artist: artist,
            src: safeUrl( src ),
            cover: safeUrl( String( ( item.track_cover && item.track_cover.url ) || '' ).trim() ),
            duration: String( ( item && item.track_duration ) || '' ).trim(),
          });
        });
      }

      if ( ! tracks.length ) {
        return;
      }

      var skin = settings.skin === 'compact' ? 'compact' : 'card';
      var preload = settings.preload === 'none' ? 'none' : 'metadata';

      // Mirror PHP's `?? 'yes'` exactly: only a MISSING key falls back — the
      // switchers' Off state is '' and must stay falsy ('' || 'yes' would not).
      var showCover = ( settings.show_cover === undefined ? 'yes' : settings.show_cover ) === 'yes';
      var showPlaylist = ( settings.show_playlist === undefined ? 'yes' : settings.show_playlist ) === 'yes';
      var playlistNumbers = ( settings.playlist_numbers === undefined ? 'yes' : settings.playlist_numbers ) === 'yes';
      var autoplayNext = ( settings.autoplay_next === undefined ? 'yes' : settings.autoplay_next ) === 'yes';
      var loopPlaylist = settings.loop_playlist === 'yes';
      var showSkip = ( settings.show_skip === undefined ? 'yes' : settings.show_skip ) === 'yes';
      var showSpeed = ( settings.show_speed === undefined ? 'yes' : settings.show_speed ) === 'yes';
      var showIndicator = ( settings.show_playing_indicator === undefined ? 'yes' : settings.show_playing_indicator ) === 'yes';

      var first = tracks[0];
      var hasMultiple = tracks.length > 1;
      var renderPlaylist = showPlaylist && hasMultiple;

      var hasArtwork = tracks.some( function ( track ) {
        return track.cover !== '';
      });

      var renderCover = showCover && hasArtwork;

      // Mirrors parse_clock() / format_clock() on the PHP side.
      var parseClock = function ( label ) {
        var value = String( label || '' ).trim();

        if ( ! /^\d{1,2}(:\d{1,2}){1,2}$/.test( value ) ) {
          return 0;
        }

        return value.split( ':' ).reduce( function ( carry, part ) {
          return ( carry * 60 ) + ( parseInt( part, 10 ) || 0 );
        }, 0 );
      };

      var formatClock = function ( seconds ) {
        var total = Math.max( 0, Math.floor( seconds ) );
        var hours = Math.floor( total / 3600 );
        var minutes = Math.floor( ( total % 3600 ) / 60 );
        var rest = total % 60;
        var pad = function ( n ) { return n < 10 ? '0' + n : String( n ); };

        return hours > 0
          ? hours + ':' + pad( minutes ) + ':' + pad( rest )
          : minutes + ':' + pad( rest );
      };

      var progress = 35;
      var totalSeconds = parseClock( first.duration );
      var currentLabel = totalSeconds > 0 ? formatClock( totalSeconds * 0.35 ) : '0:00';
      var totalLabel = first.duration !== '' ? first.duration : '0:00';

      var untitled = '<?php echo esc_js( __( 'Untitled track', 'themeasy-lite' ) ); ?>';
      var titleLabel = first.title !== '' ? first.title : untitled;

      // ------------------------------------------------------------------------
      // Wrapper classes + attributes.
      // ------------------------------------------------------------------------
      var wrapperClasses = [ 'tms-audio-player', 'tms-audio-player--' + skin ];

      if ( renderCover ) {
        wrapperClasses.push( 'tms-audio-player--with-cover' );
      }

      wrapperClasses.push( 'tms-audio-player--static' );

      var wrapperAtts = {
        'data-autoplay-next': autoplayNext ? 'true' : 'false',
        'data-loop-playlist': loopPlaylist ? 'true' : 'false',
        'style': '--tms-audio-player-progress: ' + progress + '%;'
      };

      var wrapperClassStr = wrapperClasses.filter( Boolean ).join( ' ' );

      // ------------------------------------------------------------------------
      // Native element attributes.
      // ------------------------------------------------------------------------
      var nativeAtts = {
        'class': 'tms-audio-player__native',
        'preload': preload
      };

      if ( first.src !== '' ) {
        nativeAtts['src'] = first.src;
        nativeAtts['controls'] = 'controls';
      }

      // ------------------------------------------------------------------------
      // Button labels (sprintf parity with render()).
      // ------------------------------------------------------------------------
      var labelPlay = '<?php /* translators: %s: track title. */ echo esc_js( __( 'Play %s', 'themeasy-lite' ) ); ?>';
      var labelPause = '<?php /* translators: %s: track title. */ echo esc_js( __( 'Pause %s', 'themeasy-lite' ) ); ?>';
      var labelSpeed = '<?php /* translators: %s: playback rate, e.g. 1.5x. */ echo esc_js( __( 'Playback speed: %s', 'themeasy-lite' ) ); ?>';

      // Function replacement: a '$&' in the value must never be re-expanded.
      var fillLabel = function ( template, value ) {
        return String( template ).replace( '%s', function () {
          return value;
        });
      };
    #>

    <div class="{{ wrapperClassStr }}" {{{ Themeasy.htmlAttributes( wrapperAtts ) }}}>

      <audio {{{ Themeasy.htmlAttributes( nativeAtts ) }}}></audio>

      <div class="tms-audio-player__ui" role="group" aria-label="<?php echo esc_attr__( 'Audio player', 'themeasy-lite' ); ?>">

        <# if ( renderCover ) { #>
          <div class="tms-audio-player__cover">
            <span class="tms-audio-player__cover-fallback" aria-hidden="true"></span>
            <# if ( first.cover !== '' ) { #>
              <img class="tms-audio-player__cover-img" src="{{ first.cover }}" alt="" loading="lazy" decoding="async" />
            <# } else { #>
              <img class="tms-audio-player__cover-img" src="" alt="" hidden loading="lazy" decoding="async" />
            <# } #>
          </div><!-- /.tms-audio-player__cover -->
        <# } #>

        <div class="tms-audio-player__head">
          <# if ( showIndicator ) { #>
            <span class="tms-audio-player__head-eq tms-audio-player__eq" aria-hidden="true">
              <span class="tms-audio-player__eq-bar"></span>
              <span class="tms-audio-player__eq-bar"></span>
              <span class="tms-audio-player__eq-bar"></span>
              <span class="tms-audio-player__eq-bar"></span>
            </span>
          <# } #>
          <span class="tms-audio-player__titles">
            <span class="tms-audio-player__title">{{ titleLabel }}</span>
            <# if ( first.artist !== '' ) { #>
              <span class="tms-audio-player__artist">{{ first.artist }}</span>
            <# } #>
          </span>
        </div><!-- /.tms-audio-player__head -->

        <div class="tms-audio-player__timeline">
          <span class="tms-audio-player__time tms-audio-player__time--current">{{ currentLabel }}</span>
          <input
            type="range"
            class="tms-audio-player__scrubber"
            min="0"
            max="1000"
            step="1"
            value="{{ progress * 10 }}"
            aria-label="<?php echo esc_attr__( 'Seek', 'themeasy-lite' ); ?>"
            aria-valuetext="{{ currentLabel }}"
          />
          <span class="tms-audio-player__time tms-audio-player__time--total">{{ totalLabel }}</span>
        </div><!-- /.tms-audio-player__timeline -->

        <div class="tms-audio-player__transport">

          <# if ( hasMultiple ) { #>
            <button
              type="button"
              class="tms-audio-player__button tms-audio-player__button--secondary tms-audio-player__button--prev"
              aria-label="<?php echo esc_attr__( 'Previous track', 'themeasy-lite' ); ?>"
            >
              <?php echo wp_kses( $this->get_icon_markup( 'prev' ), themeasy_get_icon_kses_tags() ); ?>
            </button>
          <# } #>

          <# if ( showSkip ) { #>
            <button
              type="button"
              class="tms-audio-player__button tms-audio-player__button--secondary tms-audio-player__button--rewind"
              aria-label="<?php echo esc_attr__( 'Rewind 15 seconds', 'themeasy-lite' ); ?>"
            >
              <?php echo wp_kses( $this->get_icon_markup( 'rewind' ), themeasy_get_icon_kses_tags() ); ?>
              <span class="tms-audio-player__skip-amount" aria-hidden="true">15</span>
            </button>
          <# } #>

          <button
            type="button"
            class="tms-audio-player__button tms-audio-player__button--play"
            aria-label="{{ fillLabel( labelPlay, titleLabel ) }}"
            data-label-play="{{ labelPlay }}"
            data-label-pause="{{ labelPause }}"
          >
            <?php echo wp_kses( $this->get_icon_markup( 'play' ), themeasy_get_icon_kses_tags() ); ?>
            <?php echo wp_kses( $this->get_icon_markup( 'pause' ), themeasy_get_icon_kses_tags() ); ?>
          </button>

          <# if ( showSkip ) { #>
            <button
              type="button"
              class="tms-audio-player__button tms-audio-player__button--secondary tms-audio-player__button--forward"
              aria-label="<?php echo esc_attr__( 'Forward 15 seconds', 'themeasy-lite' ); ?>"
            >
              <?php echo wp_kses( $this->get_icon_markup( 'forward' ), themeasy_get_icon_kses_tags() ); ?>
              <span class="tms-audio-player__skip-amount" aria-hidden="true">15</span>
            </button>
          <# } #>

          <# if ( hasMultiple ) { #>
            <button
              type="button"
              class="tms-audio-player__button tms-audio-player__button--secondary tms-audio-player__button--next"
              aria-label="<?php echo esc_attr__( 'Next track', 'themeasy-lite' ); ?>"
            >
              <?php echo wp_kses( $this->get_icon_markup( 'next' ), themeasy_get_icon_kses_tags() ); ?>
            </button>
          <# } #>

          <span class="tms-audio-player__extras">
            <# if ( showSpeed ) { #>
              <button
                type="button"
                class="tms-audio-player__button tms-audio-player__button--secondary tms-audio-player__button--speed"
                aria-label="{{ fillLabel( labelSpeed, '1×' ) }}"
                data-label-speed="{{ labelSpeed }}"
              >
                <span class="tms-audio-player__speed-label" aria-hidden="true">1×</span>
              </button>
            <# } #>

            <button
              type="button"
              class="tms-audio-player__button tms-audio-player__button--secondary tms-audio-player__button--mute"
              aria-label="<?php echo esc_attr__( 'Mute', 'themeasy-lite' ); ?>"
              data-label-mute="<?php echo esc_attr__( 'Mute', 'themeasy-lite' ); ?>"
              data-label-unmute="<?php echo esc_attr__( 'Unmute', 'themeasy-lite' ); ?>"
            >
              <?php echo wp_kses( $this->get_icon_markup( 'volume' ), themeasy_get_icon_kses_tags() ); ?>
              <?php echo wp_kses( $this->get_icon_markup( 'muted' ), themeasy_get_icon_kses_tags() ); ?>
            </button>
          </span><!-- /.tms-audio-player__extras -->

        </div><!-- /.tms-audio-player__transport -->

      </div><!-- /.tms-audio-player__ui -->

      <# if ( renderPlaylist ) { #>
        <ol class="tms-audio-player__playlist">
          <# _.each( tracks, function ( track, position ) {
            var titleKey = view.getRepeaterSettingKey( 'track_title', 'tracks', track.index );
            view.addRenderAttribute( titleKey, 'class', 'tms-audio-player__item-title' );
            view.addInlineEditingAttributes( titleKey, 'none' );

            var itemClasses = [ 'tms-audio-player__item' ];

            if ( position === 0 ) {
              itemClasses.push( 'tms-audio-player__item--active' );
            }

            var itemTitle = track.title !== '' ? track.title : untitled;

            var linkAtts = {
              'class': 'tms-audio-player__item-link',
              'data-src': track.src,
              'data-title': itemTitle,
              'data-artist': track.artist,
              'data-cover': track.cover,
              'data-duration': track.duration
            };

            if ( track.src !== '' ) {
              linkAtts['href'] = track.src;
            }
          #>
            <li class="{{ itemClasses.join( ' ' ) }}">
              <a {{{ Themeasy.htmlAttributes( linkAtts ) }}}>
                <# if ( playlistNumbers ) { #>
                  <span class="tms-audio-player__item-index" aria-hidden="true">{{ position < 9 ? '0' + ( position + 1 ) : String( position + 1 ) }}</span>
                <# } #>
                <# if ( showIndicator ) { #>
                  <span class="tms-audio-player__item-eq tms-audio-player__eq" aria-hidden="true">
                    <span class="tms-audio-player__eq-bar"></span>
                    <span class="tms-audio-player__eq-bar"></span>
                    <span class="tms-audio-player__eq-bar"></span>
                    <span class="tms-audio-player__eq-bar"></span>
                  </span>
                <# } #>
                <span {{{ view.getRenderAttributeString( titleKey ) }}}>{{ itemTitle }}</span>
                <span class="tms-audio-player__item-duration">{{ track.duration }}</span>
              </a>
            </li>
          <# }); #>
        </ol><!-- /.tms-audio-player__playlist -->
      <# } #>

    </div><!-- /.tms-audio-player -->

    <?php
  }
}
