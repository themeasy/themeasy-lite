<?php
/**
 * Themeasy Elementor Widget: Team Member
 *
 * Profile card for team/staff with portrait, name, role, short bio and a
 * repeatable social-links row. Ships three skins:
 *   - classic    (portrait on top, content below)
 *   - horizontal (portrait on the left, content on the right)
 *   - overlay    (portrait fills the card, content reveals on hover)
 *
 * @package Themeasy
 * @since 1.0.0
 */

namespace Themeasy\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Control_Media;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Utils;
use Elementor\Widget_Base;

/**
 * Responsible for registering controls and rendering the Team Member widget.
 */
class TeamMember extends Widget_Base {
  public function get_name() {
    return 'themeasy-team-member';
  }

  public function get_title() {
    return esc_html__( 'Team Member', 'themeasy-lite' );
  }

  public function get_icon() {
    return 'eicon-person';
  }

  public function get_custom_help_url() {
    return 'https://themeasy.co/help-center';
  }

  public function get_categories() {
    return ['themeasy_blocks_category', 'themeasy_content_category'];
  }

  public function get_keywords() {
    return ['themeasy', 'team', 'member', 'person', 'staff', 'profile', 'about', 'author'];
  }

  public function get_style_depends() {
    return ['themeasy-shared', 'themeasy-team-member'];
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
    // Content section: Content
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'team_member_content_section',
        [
          'label' => esc_html__( 'Content', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $this->add_control(
        'member_name',
        [
          'label' => esc_html__( 'Name', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'Jane Doe', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'Leave blank to hide', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
        ]
      );

      $this->add_control(
        'member_name_tag',
        [
          'label' => esc_html__( 'Name Tag', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'h3',
          'options' => [
            'h1' => 'H1',
            'h2' => 'H2',
            'h3' => 'H3',
            'h4' => 'H4',
            'h5' => 'H5',
            'h6' => 'H6',
            'div' => 'DIV',
          ],
        ]
      );

      $this->add_control(
        'member_role',
        [
          'label' => esc_html__( 'Role / Position', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'Creative Director', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'Leave blank to hide', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
        ]
      );

      $this->add_control(
        'member_bio',
        [
          'label' => esc_html__( 'Bio', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::TEXTAREA,
          'rows' => 4,
          'default' => esc_html__(
            'Short biography or a few words describing the team member\'s role and expertise.',
            'themeasy-lite'
          ),
          'placeholder' => esc_html__( 'Leave blank to hide', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Image
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'team_member_image_section',
        [
          'label' => esc_html__( 'Image', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $this->add_control(
        'image',
        [
          'label' => esc_html__( 'Portrait', 'themeasy-lite' ),
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
          'exclude' => ['custom'],
          'include' => [],
          'default' => 'medium_large',
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
    // Content section: Socials
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'team_member_socials_section',
        [
          'label' => esc_html__( 'Socials', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $this->add_control(
        'show_socials',
        [
          'label' => esc_html__( 'Show Socials', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Show', 'themeasy-lite' ),
          'label_off' => esc_html__( 'Hide', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
        ]
      );

      $repeater = new Repeater();

      $repeater->add_control(
        'social_label',
        [
          'label' => esc_html__( 'Label', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'default' => esc_html__( 'Social Link', 'themeasy-lite' ),
          'placeholder' => esc_html__( 'Accessible label (e.g. Twitter)', 'themeasy-lite' ),
          'dynamic' => ['active' => true],
        ]
      );

      $repeater->add_control(
        'social_icon',
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
        'social_link',
        [
          'label' => esc_html__( 'Link', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::URL,
          'placeholder' => esc_html__( 'https://your-link.com', 'themeasy-lite' ),
          'options' => ['url', 'is_external', 'nofollow'],
          'default' => [
            'url' => '#',
            'is_external' => true,
            'nofollow' => false,
          ],
          'dynamic' => ['active' => true],
        ]
      );

      $this->add_control(
        'socials',
        [
          'label' => esc_html__( 'Social Links', 'themeasy-lite' ),
          'type' => Controls_Manager::REPEATER,
          'fields' => $repeater->get_controls(),
          'title_field' => '{{{ social_label }}}',
          'default' => [
            [
              'social_label' => esc_html__( 'Instagram', 'themeasy-lite' ),
              'social_icon' => ['value' => 'ty-feather-instagram', 'library' => 'ty-feather'],
              'social_link' => ['url' => '#your-link', 'is_external' => true, 'nofollow' => false],
            ],
            [
              'social_label' => esc_html__( 'X', 'themeasy-lite' ),
              'social_icon' => ['value' => 'ty-feather-x-twitter', 'library' => 'ty-feather'],
              'social_link' => ['url' => '#your-link', 'is_external' => true, 'nofollow' => false],
            ],
            [
              'social_label' => esc_html__( 'LinkedIn', 'themeasy-lite' ),
              'social_icon' => ['value' => 'ty-feather-linkedin', 'library' => 'ty-feather'],
              'social_link' => ['url' => '#your-link', 'is_external' => true, 'nofollow' => false],
            ],
          ],
          'condition' => [
            'show_socials' => 'yes',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Card Link
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'team_member_link_section',
        [
          'label' => esc_html__( 'Card Link', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $this->add_control(
        'link_type',
        [
          'label' => esc_html__( 'Link Type', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => '',
          'options' => [
            '' => esc_html__( 'None', 'themeasy-lite' ),
            'name' => esc_html__( 'Name Only', 'themeasy-lite' ),
            'box' => esc_html__( 'Whole Card', 'themeasy-lite' ),
          ],
          'description' => esc_html__(
            'Optionally wrap the member name or the entire card with a link (e.g. to a profile page).',
            'themeasy-lite'
          ),
        ]
      );

      $this->add_control(
        'link',
        [
          'label' => esc_html__( 'Link', 'themeasy-lite' ),
          'label_block' => true,
          'type' => Controls_Manager::URL,
          'placeholder' => esc_html__( 'https://your-link.com', 'themeasy-lite' ),
          'options' => ['url', 'is_external', 'nofollow'],
          'default' => [
            'url' => '',
            'is_external' => false,
            'nofollow' => false,
          ],
          'condition' => [
            'link_type!' => '',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Layout
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'team_member_layout_section',
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
          'default' => 'classic',
          'options' => [
            'classic' => esc_html__( 'Classic (Image on Top)', 'themeasy-lite' ),
            'horizontal' => esc_html__( 'Horizontal (Image on Side)', 'themeasy-lite' ),
            'overlay' => esc_html__( 'Overlay (Reveal on Hover)', 'themeasy-lite' ),
          ],
        ]
      );

      $this->add_control(
        'image_position',
        [
          'label' => esc_html__( 'Image Position', 'themeasy-lite' ),
          'type' => Controls_Manager::CHOOSE,
          'options' => [
            'left' => [
              'title' => esc_html__( 'Left', 'themeasy-lite' ),
              'icon' => 'eicon-h-align-left',
            ],
            'right' => [
              'title' => esc_html__( 'Right', 'themeasy-lite' ),
              'icon' => 'eicon-h-align-right',
            ],
          ],
          'default' => 'left',
          'toggle' => false,
          'condition' => [
            'skin' => 'horizontal',
          ],
        ]
      );

      $this->add_control(
        'overlay_reveal',
        [
          'label' => esc_html__( 'Reveal Effect', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'slide-up',
          'options' => [
            'slide-up' => esc_html__( 'Slide Up', 'themeasy-lite' ),
            'fade' => esc_html__( 'Fade In', 'themeasy-lite' ),
            'always' => esc_html__( 'Always Visible', 'themeasy-lite' ),
          ],
          'condition' => [
            'skin' => 'overlay',
          ],
        ]
      );

      $this->add_responsive_control(
        'content_alignment',
        [
          'label' => esc_html__( 'Content Alignment', 'themeasy-lite' ),
          'type' => Controls_Manager::CHOOSE,
          'options' => [
            'start' => [
              'title' => esc_html__( 'Left', 'themeasy-lite' ),
              'icon' => 'eicon-text-align-left',
            ],
            'center' => [
              'title' => esc_html__( 'Center', 'themeasy-lite' ),
              'icon' => 'eicon-text-align-center',
            ],
            'end' => [
              'title' => esc_html__( 'Right', 'themeasy-lite' ),
              'icon' => 'eicon-text-align-right',
            ],
          ],
          'default' => 'center',
          'selectors' => [
            '{{WRAPPER}} .tms-team-member' => '--tms-team-member-align: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'content_gap',
        [
          'label' => esc_html__( 'Content Spacing', 'themeasy-lite' ),
          'description' => esc_html__(
            'Vertical gap between the name, role, bio and socials.',
            'themeasy-lite'
          ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem'],
          'range' => [
            'px' => ['min' => 0, 'max' => 80, 'step' => 1],
            'rem' => ['min' => 0, 'max' => 6, 'step' => 0.1],
          ],
          'default' => ['unit' => 'px', 'size' => 16],
          'selectors' => [
            '{{WRAPPER}} .tms-team-member__body' => 'gap: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'content_padding',
        [
          'label' => esc_html__( 'Content Padding', 'themeasy-lite' ),
          'description' => esc_html__(
            'Inner padding of the text block. Set the bottom to 0 on transparent cards so they align flush with the section.',
            'themeasy-lite'
          ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', 'em'],
          'default' => [
            'top' => 20, 'right' => 20, 'bottom' => 20, 'left' => 20, 'unit' => 'px', 'isLinked' => true,
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-team-member__body'
              => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
          'condition' => [
            'skin!' => 'overlay',
          ],
        ]
      );

      $this->add_responsive_control(
        'box_min_height',
        [
          'label' => esc_html__( 'Minimum Height', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'vh', 'rem'],
          'range' => [
            'px' => ['min' => 120, 'max' => 900, 'step' => 1],
            'vh' => ['min' => 10, 'max' => 100, 'step' => 1],
            'rem' => ['min' => 8, 'max' => 60, 'step' => 0.5],
          ],
          'default' => ['unit' => 'px', 'size' => 420],
          'selectors' => [
            '{{WRAPPER}} .tms-team-member' => 'min-height: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'skin' => 'overlay',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Box
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'team_member_box_styles_section',
        [
          'label' => esc_html__( 'Box', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_control(
        'box_background_color',
        [
          'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-team-member' => 'background-color: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'box_max_width',
        [
          'label' => esc_html__( 'Max Width', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', '%', 'rem'],
          'range' => [
            'px' => ['min' => 200, 'max' => 1200, 'step' => 1],
            '%' => ['min' => 10, 'max' => 100, 'step' => 1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-team-member' => 'max-width: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'box_padding',
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', 'em'],
          'selectors' => [
            '{{WRAPPER}} .tms-team-member'
              => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'box_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', '%'],
          'selectors' => [
            '{{WRAPPER}} .tms-team-member'
              => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Border::get_type(),
        [
          'name' => 'box_border',
          'selector' => '{{WRAPPER}} .tms-team-member',
        ]
      );

      $this->add_group_control(
        Group_Control_Box_Shadow::get_type(),
        [
          'name' => 'box_shadow',
          'label' => esc_html__( 'Box Shadow', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-team-member',
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Image
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'team_member_image_styles_section',
        [
          'label' => esc_html__( 'Image', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_responsive_control(
        'image_width',
        [
          'label' => esc_html__( 'Width', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', '%', 'rem'],
          'range' => [
            'px' => ['min' => 50, 'max' => 800, 'step' => 1],
            '%' => ['min' => 10, 'max' => 100, 'step' => 1],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-team-member__media' => 'width: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'skin' => 'classic',
          ],
          'description' => esc_html__(
            'Available on the Classic skin. Use "Column Width" below for the Horizontal skin.',
            'themeasy-lite'
          ),
        ]
      );

      $this->add_responsive_control(
        'image_height',
        [
          'label' => esc_html__( 'Height', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', '%', 'rem'],
          'range' => [
            'px' => ['min' => 50, 'max' => 900, 'step' => 1],
            '%' => ['min' => 10, 'max' => 100, 'step' => 1],
          ],
          'default' => ['unit' => 'px', 'size' => 280],
          'selectors' => [
            '{{WRAPPER}} .tms-team-member__media' => 'height: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'image_object_fit',
        [
          'label' => esc_html__( 'Object Fit', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'cover',
          'options' => [
            'cover' => esc_html__( 'Cover', 'themeasy-lite' ),
            'contain' => esc_html__( 'Contain', 'themeasy-lite' ),
            'fill' => esc_html__( 'Fill', 'themeasy-lite' ),
            'scale-down' => esc_html__( 'Scale Down', 'themeasy-lite' ),
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-team-member__image' => 'object-fit: {{VALUE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'image_object_position',
        [
          'label' => esc_html__( 'Object Position', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'center center',
          'options' => [
            'center center' => esc_html__( 'Center', 'themeasy-lite' ),
            'top center' => esc_html__( 'Top', 'themeasy-lite' ),
            'bottom center' => esc_html__( 'Bottom', 'themeasy-lite' ),
            'center left' => esc_html__( 'Left', 'themeasy-lite' ),
            'center right' => esc_html__( 'Right', 'themeasy-lite' ),
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-team-member__image' => 'object-position: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'image_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', '%'],
          'selectors' => [
            '{{WRAPPER}} .tms-team-member__media'
              => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Css_Filter::get_type(),
        [
          'name' => 'image_css_filters',
          'selector' => '{{WRAPPER}} .tms-team-member__image',
        ]
      );

      $this->add_control(
        'image_hover_zoom',
        [
          'label' => esc_html__( 'Zoom on Hover', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['u'],
          'range' => ['u' => ['min' => 1, 'max' => 1.4, 'step' => 0.01]],
          'default' => ['unit' => 'u', 'size' => 1.05],
          'selectors' => [
            '{{WRAPPER}} .tms-team-member' => '--tms-team-member-image-zoom: {{SIZE}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'horizontal_image_width',
        [
          'label' => esc_html__( 'Column Width (Horizontal Skin)', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['%', 'px'],
          'range' => [
            '%' => ['min' => 20, 'max' => 80, 'step' => 1],
            'px' => ['min' => 120, 'max' => 600, 'step' => 1],
          ],
          'default' => ['unit' => '%', 'size' => 45],
          'selectors' => [
            '{{WRAPPER}} .tms-team-member--skin-horizontal' => '--tms-team-member-media-col: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'skin' => 'horizontal',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Overlay
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'team_member_overlay_styles_section',
        [
          'label' => esc_html__( 'Overlay', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'skin' => 'overlay',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Background::get_type(),
        [
          'name' => 'overlay_background',
          'label' => esc_html__( 'Background', 'themeasy-lite' ),
          'types' => ['classic', 'gradient'],
          'selector' => '{{WRAPPER}} .tms-team-member--skin-overlay .tms-team-member__overlay',
        ]
      );

      $this->add_responsive_control(
        'overlay_padding',
        [
          'label' => esc_html__( 'Content Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', 'em'],
          'default' => ['top' => 24, 'right' => 24, 'bottom' => 24, 'left' => 24, 'unit' => 'px'],
          'selectors' => [
            '{{WRAPPER}} .tms-team-member--skin-overlay .tms-team-member__body'
              => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'overlay_transition_duration',
        [
          'label' => esc_html__( 'Transition Duration', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['s'],
          'range' => ['s' => ['min' => 0.1, 'max' => 2, 'step' => 0.1]],
          'default' => ['unit' => 's', 'size' => 0.45],
          'selectors' => [
            '{{WRAPPER}} .tms-team-member' => '--tms-team-member-duration: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Content
    // ------------------------------------------------------------------------
    $this->register_content_style_controls();

    // ------------------------------------------------------------------------
    // Style section: Socials
    // ------------------------------------------------------------------------
    $this->register_socials_style_controls();
  }

  /**
   * Register content typography/colors (name, role, bio).
   *
   * @return void
   */
  private function register_content_style_controls(): void {
    $this->start_controls_section(
      'team_member_content_styles_section',
        [
          'label' => esc_html__( 'Content', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      // Name.
      $this->add_control(
        'name_heading',
        [
          'label' => esc_html__( 'Name', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
        ]
      );

      $this->add_control(
        'name_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-team-member__name,
             {{WRAPPER}} .tms-team-member__name a' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'name_hover_color',
        [
          'label' => esc_html__( 'Hover Color (when linked)', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-team-member__name a:hover,
             {{WRAPPER}} .tms-team-member__name a:focus-visible' => 'color: {{VALUE}};',
          ],
          'condition' => [
            'link_type' => ['name', 'box'],
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'name_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-team-member__name',
        ]
      );

      // Role.
      $this->add_control(
        'role_heading',
        [
          'label' => esc_html__( 'Role', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'role_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-team-member__role' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Background::get_type(),
        [
          'name' => 'role_background',
          'label' => esc_html__( 'Background', 'themeasy-lite' ),
          'types' => ['classic', 'gradient'],
          'selector' => '{{WRAPPER}} .tms-team-member__role',
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'role_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-team-member__role',
        ]
      );

      $this->add_responsive_control(
        'role_padding',
        [
          'label' => esc_html__( 'Padding', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', 'em'],
          'selectors' => [
            '{{WRAPPER}} .tms-team-member__role'
              => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
          'description' => esc_html__(
            'Space around the text — needed for a background or border to read as a badge.',
            'themeasy-lite'
          ),
        ]
      );

      $this->add_control(
        'role_border_radius',
        [
          'label' => esc_html__( 'Border Radius', 'themeasy-lite' ),
          'type' => Controls_Manager::DIMENSIONS,
          'size_units' => ['px', 'rem', '%'],
          'selectors' => [
            '{{WRAPPER}} .tms-team-member__role'
              => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Border::get_type(),
        [
          'name' => 'role_border',
          'selector' => '{{WRAPPER}} .tms-team-member__role',
        ]
      );

      // Bio.
      $this->add_control(
        'bio_heading',
        [
          'label' => esc_html__( 'Bio', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
        ]
      );

      $this->add_control(
        'bio_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-team-member__bio' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'bio_typography',
          'label' => esc_html__( 'Typography', 'themeasy-lite' ),
          'selector' => '{{WRAPPER}} .tms-team-member__bio',
        ]
      );

      $this->add_responsive_control(
        'bio_line_clamp',
        [
          'label' => esc_html__( 'Line Clamp', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['u'],
          'range' => ['u' => ['min' => 0, 'max' => 8, 'step' => 1]],
          'default' => ['unit' => 'u', 'size' => 0],
          'mobile_default' => ['unit' => 'u', 'size' => 0],
          'selectors' => [
            '{{WRAPPER}} .tms-team-member__bio'
              => '--tms-line-clamp: {{SIZE}}; display: -webkit-box; -webkit-box-orient: vertical; overflow: hidden; -webkit-line-clamp: var(--tms-line-clamp);',
          ],
          'description' => esc_html__( 'Set to 0 to disable line clamp (the phone default).', 'themeasy-lite' ),
        ]
      );

    $this->end_controls_section();
  }

  /**
   * Register socials style controls (normal/hover colors, size, shape).
   *
   * @return void
   */
  private function register_socials_style_controls(): void {
    $this->start_controls_section(
      'team_member_socials_styles_section',
        [
          'label' => esc_html__( 'Socials', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'show_socials' => 'yes',
          ],
        ]
      );

      // Colors (normal/hover).
      $this->start_controls_tabs( 'socials_color_tabs' );

        $this->start_controls_tab(
          'socials_normal_tab',
          [
            'label' => esc_html__( 'Normal', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'socials_icon_color',
            [
              'label' => esc_html__( 'Icon Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-team-member__social-link,
                 {{WRAPPER}} .tms-team-member__social-link svg' => 'color: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            'socials_icon_fill_color',
            [
              'label' => esc_html__( 'Icon Fill Color', 'themeasy-lite' ),
              'description' => esc_html__( 'Applies to SVG icons only.', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-team-member__social-link svg' => 'fill: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            'socials_icon_stroke_color',
            [
              'label' => esc_html__( 'Icon Stroke Color', 'themeasy-lite' ),
              'description' => esc_html__( 'Applies to SVG icons only.', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-team-member__social-link svg' => 'stroke: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            'socials_bg_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-team-member__social-link' => 'background-color: {{VALUE}};',
              ],
              'condition' => [
                'socials_shape!' => 'plain',
              ],
            ]
          );

          $this->add_control(
            'socials_border_color',
            [
              'label' => esc_html__( 'Border Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-team-member__social-link' => 'border-color: {{VALUE}};',
              ],
              'condition' => [
                'socials_shape!' => 'plain',
              ],
            ]
          );

        $this->end_controls_tab();

        $this->start_controls_tab(
          'socials_hover_tab',
          [
            'label' => esc_html__( 'Hover', 'themeasy-lite' ),
          ]
        );

          $this->add_control(
            'socials_hover_icon_color',
            [
              'label' => esc_html__( 'Icon Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-team-member__social-link:hover,
                 {{WRAPPER}} .tms-team-member__social-link:hover svg,
                 {{WRAPPER}} .tms-team-member__social-link:focus-visible,
                 {{WRAPPER}} .tms-team-member__social-link:focus-visible svg' => 'color: {{VALUE}};',
              ],
            ]
          );

          $this->add_control(
            'socials_hover_bg_color',
            [
              'label' => esc_html__( 'Background Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-team-member__social-link:hover,
                 {{WRAPPER}} .tms-team-member__social-link:focus-visible' => 'background-color: {{VALUE}};',
              ],
              'condition' => [
                'socials_shape!' => 'plain',
              ],
            ]
          );

          $this->add_control(
            'socials_hover_border_color',
            [
              'label' => esc_html__( 'Border Color', 'themeasy-lite' ),
              'type' => Controls_Manager::COLOR,
              'selectors' => [
                '{{WRAPPER}} .tms-team-member__social-link:hover,
                 {{WRAPPER}} .tms-team-member__social-link:focus-visible' => 'border-color: {{VALUE}};',
              ],
              'condition' => [
                'socials_shape!' => 'plain',
              ],
            ]
          );

        $this->end_controls_tab();

      $this->end_controls_tabs();

      // Shape & alignment.
      $this->add_control(
        'socials_shape',
        [
          'label' => esc_html__( 'Shape', 'themeasy-lite' ),
          'type' => Controls_Manager::SELECT,
          'default' => 'circle',
          'separator' => 'before',
          'options' => [
            'plain' => esc_html__( 'Plain', 'themeasy-lite' ),
            'square' => esc_html__( 'Square', 'themeasy-lite' ),
            'rounded' => esc_html__( 'Rounded', 'themeasy-lite' ),
            'circle' => esc_html__( 'Circle', 'themeasy-lite' ),
          ],
        ]
      );

      $this->add_responsive_control(
        'socials_align',
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
          'default' => 'center',
          'selectors' => [
            '{{WRAPPER}} .tms-team-member__socials' => 'justify-content: {{VALUE}};',
          ],
        ]
      );

      // Sizing.
      $this->add_responsive_control(
        'socials_size',
        [
          'label' => esc_html__( 'Icon Size', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'separator' => 'before',
          'size_units' => ['px', 'em', 'rem'],
          'range' => [
            'px' => ['min' => 8, 'max' => 80, 'step' => 1],
            'em' => ['min' => 0.5, 'max' => 4, 'step' => 0.1],
            'rem' => ['min' => 0.5, 'max' => 4, 'step' => 0.1],
          ],
          'default' => ['unit' => 'px', 'size' => 16],
          'selectors' => [
            '{{WRAPPER}} .tms-team-member__social-link .tms-team-member__social-icon'
              => 'font-size: {{SIZE}}{{UNIT}};',
            '{{WRAPPER}} .tms-team-member__social-link .tms-team-member__social-icon svg'
              => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'socials_box_size',
        [
          'label' => esc_html__( 'Box Size', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'em', 'rem'],
          'range' => [
            'px' => ['min' => 16, 'max' => 120, 'step' => 1],
            'em' => ['min' => 1, 'max' => 5, 'step' => 0.1],
            'rem' => ['min' => 1, 'max' => 5, 'step' => 0.1],
          ],
          'default' => ['unit' => 'px', 'size' => 36],
          'selectors' => [
            '{{WRAPPER}} .tms-team-member__social-link' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'socials_shape!' => 'plain',
          ],
        ]
      );

      $this->add_responsive_control(
        'socials_gap',
        [
          'label' => esc_html__( 'Items Gap', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'rem'],
          'range' => [
            'px' => ['min' => 0, 'max' => 40, 'step' => 1],
            'rem' => ['min' => 0, 'max' => 3, 'step' => 0.1],
          ],
          'default' => ['unit' => 'px', 'size' => 8],
          'selectors' => [
            '{{WRAPPER}} .tms-team-member__socials' => 'gap: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'socials_icon_thickness',
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
            '{{WRAPPER}} .tms-team-member__social-link svg,
             {{WRAPPER}} .tms-team-member__social-link svg *' => 'stroke-width: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      // Border.
      $this->add_control(
        'socials_border_width',
        [
          'label' => esc_html__( 'Border Width', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => ['px' => ['min' => 0, 'max' => 6, 'step' => 1]],
          'selectors' => [
            '{{WRAPPER}} .tms-team-member__social-link'
              => 'border-width: {{SIZE}}{{UNIT}}; border-style: solid;',
          ],
          'separator' => 'before',
          'condition' => [
            'socials_shape!' => 'plain',
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
    $skin = $settings['skin'] ?? 'classic';
    $image_position = $settings['image_position'] ?? 'left';
    $overlay_reveal = $settings['overlay_reveal'] ?? 'slide-up';
    $name = $settings['member_name'] ?? '';
    $role = $settings['member_role'] ?? '';
    $bio = $settings['member_bio'] ?? '';
    $socials_enabled = ( $settings['show_socials'] ?? 'yes' ) === 'yes';
    $socials = $settings['socials'] ?? [];
    $shape = $settings['socials_shape'] ?? 'circle';
    $link_type = $settings['link_type'] ?? '';
    $link = $settings['link'] ?? [];
    $has_link = !empty( $link['url'] );

    $name_tag = themeasy_sanitize_heading_tag(
      $settings['member_name_tag'] ?? 'h3',
      'h3',
      ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div']
    );

    $image = $settings['image'] ?? [];
    $image_id = absint( $image['id'] ?? 0 );
    $image_url = $image['url'] ?? '';
    $image_alt = Control_Media::get_image_alt( $image );
    $image_size = $settings['image_resolution_size'] ?? 'medium_large';
    $image_prio = $settings['image_loading_priority'] ?? 'lazy';

    // ------------------------------------------------------------------------
    // Wrapper classes + attributes.
    // ------------------------------------------------------------------------
    $wrapper_classes = ['tms-team-member', 'tms-team-member--skin-' . $skin];

    if ( 'horizontal' === $skin ) {
      $wrapper_classes[] = 'tms-team-member--image-' . $image_position;
    }

    if ( 'overlay' === $skin ) {
      $wrapper_classes[] = 'tms-team-member--reveal-' . $overlay_reveal;
    }

    if ( $socials_enabled ) {
      $wrapper_classes[] = 'tms-team-member--shape-' . $shape;
    }

    $wrapper_classes_output = implode( ' ', array_filter( $wrapper_classes ) );

    // ------------------------------------------------------------------------
    // Render attributes.
    // ------------------------------------------------------------------------
    if ( 'name' === $link_type && $has_link ) {
      $this->add_link_attributes( 'name_link', $link );
      themeasy_add_external_link_rel( $this, 'name_link', $link );
    }

    if ( 'box' === $link_type && $has_link ) {
      $this->add_link_attributes( 'box_link', $link );
      themeasy_add_external_link_rel( $this, 'box_link', $link );
    }

    $this->add_render_attribute( 'member_name', 'class', 'tms-team-member__name' );
    $this->add_inline_editing_attributes( 'member_name', 'none' );

    $this->add_render_attribute( 'member_role', 'class', 'tms-team-member__role' );
    $this->add_inline_editing_attributes( 'member_role', 'none' );

    $this->add_render_attribute( 'member_bio', 'class', 'tms-team-member__bio' );
    $this->add_inline_editing_attributes( 'member_bio', 'advanced' );

    // ------------------------------------------------------------------------
    // Output.
    // ------------------------------------------------------------------------
    ?>
    <article class="<?php echo esc_attr( $wrapper_classes_output ); ?>">

      <?php if ( !empty( $image_url ) ) : ?>
        <div class="tms-team-member__media">
          <?php
          themeasy_render_attachment_image(
            $image_id,
            $image_size,
            $image_url,
            $image_alt,
            'tms-team-member__image',
            [],
            $image_prio
          );
          ?>
        </div>
      <?php endif; ?>

      <?php if ( 'overlay' === $skin ) : ?>
        <span class="tms-team-member__overlay" aria-hidden="true"></span>
      <?php endif; ?>

      <div class="tms-team-member__body">

        <?php if ( $name ) : ?>
          <<?php echo tag_escape( $name_tag ); ?> <?php $this->print_render_attribute_string( 'member_name' ); ?>>
            <?php if ( 'name' === $link_type && $has_link ) : ?>
              <a <?php $this->print_render_attribute_string( 'name_link' ); ?>>
                <?php echo esc_html( $name ); ?>
              </a>
            <?php else : ?>
              <?php echo esc_html( $name ); ?>
            <?php endif; ?>
          </<?php echo tag_escape( $name_tag ); ?>>
        <?php endif; ?>

        <?php if ( $role ) : ?>
          <div <?php $this->print_render_attribute_string( 'member_role' ); ?>>
            <?php echo esc_html( $role ); ?>
          </div>
        <?php endif; ?>

        <?php if ( $bio ) : ?>
          <div <?php $this->print_render_attribute_string( 'member_bio' ); ?>>
            <?php echo wp_kses_post( $bio ); ?>
          </div>
        <?php endif; ?>

        <?php if ( $socials_enabled && !empty( $socials ) ) : ?>
          <ul class="tms-team-member__socials">

            <?php foreach ( $socials as $index => $item ) :
              $social_label = $item['social_label'] ?? '';
              $social_icon = $item['social_icon'] ?? [];
              $social_link = $item['social_link'] ?? [];

              if ( empty( $social_icon['value'] ) && empty( $social_label ) ) {
                continue;
              }

              $icon_html = themeasy_render_icon_html(
                is_array( $social_icon ) ? $social_icon : [],
                ['class' => 'tms-team-member__social-icon', 'aria-hidden' => 'true']
              );

              $social_link_key = 'social_link_' . $index;

              if ( !empty( $social_link['url'] ) ) {
                $this->add_link_attributes( $social_link_key, $social_link );
                themeasy_add_external_link_rel( $this, $social_link_key, $social_link );
              } else {
                $this->add_render_attribute( $social_link_key, 'href', '#' );
              }

              $this->add_render_attribute( $social_link_key, 'class', 'tms-team-member__social-link' );

              if ( '' !== $social_label ) {
                $this->add_render_attribute( $social_link_key, 'aria-label', $social_label );
              }

              $label_key = $this->get_repeater_setting_key( 'social_label', 'socials', $index );
              $this->add_render_attribute( $label_key, 'class', 'tms-team-member__social-label screen-reader-text' );
              $this->add_inline_editing_attributes( $label_key, 'none' );
              ?>

              <li class="tms-team-member__social-item">
                <a <?php $this->print_render_attribute_string( $social_link_key ); ?>>
                  <?php echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>
                  <?php if ( '' !== $social_label ) : ?>
                    <span <?php $this->print_render_attribute_string( $label_key ); ?>>
                      <?php echo esc_html( $social_label ); ?>
                    </span>
                  <?php endif; ?>
                </a>
              </li>

            <?php endforeach; ?>

          </ul>
        <?php endif; ?>

      </div><!-- /.tms-team-member__body -->

      <?php if ( 'box' === $link_type && $has_link ) : ?>
        <?php $cover_label = $name ? $name : esc_html__( 'Open profile', 'themeasy-lite' ); ?>
        <a <?php $this->print_render_attribute_string( 'box_link' ); ?>
           class="tms-team-member__cover-link"
           aria-label="<?php echo esc_attr( $cover_label ); ?>"></a>
      <?php endif; ?>

    </article><!-- /.tms-team-member -->
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
      var skin           = settings.skin || 'classic';
      var imagePosition  = settings.image_position || 'left';
      var overlayReveal  = settings.overlay_reveal || 'slide-up';
      var name           = settings.member_name || '';
      var role           = settings.member_role || '';
      var bio            = sanitizeInline( settings.member_bio || '', 'rich' );
      var socialsEnabled = settings.show_socials === 'yes';
      var socials        = settings.socials || [];
      var shape          = settings.socials_shape || 'circle';
      var linkType       = settings.link_type || '';
      var link           = settings.link || {};

      // Scheme guard for every href/src below. The regex lives in exactly one
      // place; the fallback fails closed rather than duplicating it (a bare ^
      // anchor misses the C0 controls browsers strip before resolving a scheme).
      var safeUrl = ( window.Themeasy && window.Themeasy.safeUrl )
        ? window.Themeasy.safeUrl
        : function () { return ''; };

      // A blocked scheme yields '' — the name then renders as plain text and the
      // box cover link is dropped, exactly as when no link is set.
      var linkHref       = safeUrl( link.url || '' );
      var hasLink        = !! linkHref;

      var nameTag = settings.member_name_tag || 'h3';
      var allowedTags = [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div' ];
      if ( allowedTags.indexOf( nameTag ) === -1 ) { nameTag = 'h3'; }

      var imageObj = settings.image || {};
      var imageUrl = safeUrl( imageObj.url || '' );
      var imageAlt = imageObj.alt || '';

      // ------------------------------------------------------------------------
      // Wrapper classes + attributes.
      // ------------------------------------------------------------------------
      var wrapperClasses = [ 'tms-team-member', 'tms-team-member--skin-' + skin ];

      if ( skin === 'horizontal' ) {
        wrapperClasses.push( 'tms-team-member--image-' + imagePosition );
      }

      if ( skin === 'overlay' ) {
        wrapperClasses.push( 'tms-team-member--reveal-' + overlayReveal );
      }

      if ( socialsEnabled ) {
        wrapperClasses.push( 'tms-team-member--shape-' + shape );
      }

      var wrapperClassStr = wrapperClasses.filter( Boolean ).join( ' ' );

      // ------------------------------------------------------------------------
      // Render attributes.
      // ------------------------------------------------------------------------
      view.addRenderAttribute( 'member_name', 'class', 'tms-team-member__name' );
      view.addInlineEditingAttributes( 'member_name', 'none' );

      view.addRenderAttribute( 'member_role', 'class', 'tms-team-member__role' );
      view.addInlineEditingAttributes( 'member_role', 'none' );

      view.addRenderAttribute( 'member_bio', 'class', 'tms-team-member__bio' );
      view.addInlineEditingAttributes( 'member_bio', 'advanced' );

      var coverLabelFallback = <?php echo wp_json_encode( __( 'Open profile', 'themeasy-lite' ) ); ?>;
    #>

    <article class="{{ wrapperClassStr }}">

      <# if ( imageUrl ) { #>
        <div class="tms-team-member__media">
          <img src="{{ imageUrl }}" alt="{{ imageAlt }}" class="tms-team-member__image" loading="lazy" />
        </div>
      <# } #>

      <# if ( skin === 'overlay' ) { #>
        <span class="tms-team-member__overlay" aria-hidden="true"></span>
      <# } #>

      <div class="tms-team-member__body">

        <# if ( name ) { #>
          <{{ nameTag }} {{{ view.getRenderAttributeString( 'member_name' ) }}}>
            <# if ( linkType === 'name' && hasLink ) { #>
              <a href="{{ linkHref }}">{{ name }}</a>
            <# } else { #>
              {{ name }}
            <# } #>
          </{{ nameTag }}>
        <# } #>

        <# if ( role ) { #>
          <div {{{ view.getRenderAttributeString( 'member_role' ) }}}>{{ role }}</div>
        <# } #>

        <# if ( bio ) { #>
          <div {{{ view.getRenderAttributeString( 'member_bio' ) }}}>{{{ bio }}}</div>
        <# } #>

        <# if ( socialsEnabled && socials.length ) { #>
          <ul class="tms-team-member__socials">
            <#
              _.each( socials, function( item, index ) {
                var socialLabel = item.social_label || '';
                var socialIcon  = item.social_icon || {};
                var socialObj   = item.social_link || {};

                // A blocked scheme yields '' — fall back to render()'s inert
                // href="#" rather than emitting the hostile value.
                var socialHref  = safeUrl( socialObj.url || '' ) || '#';

                if ( ( ! socialIcon.value ) && ( ! socialLabel ) ) { return; }

                var socialIconMarkup = ( window.Themeasy && window.Themeasy.renderIconMarkup )
                  ? window.Themeasy.renderIconMarkup(
                      view, socialIcon, null,
                      { 'class': 'tms-team-member__social-icon', 'aria-hidden': 'true' }
                    )
                  : '';

                var labelKey = view.getRepeaterSettingKey( 'social_label', 'socials', index );
                view.addRenderAttribute( labelKey, 'class', 'tms-team-member__social-label screen-reader-text' );
                view.addInlineEditingAttributes( labelKey, 'none' );
            #>
              <li class="tms-team-member__social-item">
                <a href="{{ socialHref }}"
                   class="tms-team-member__social-link"
                   <# if ( socialLabel ) { #>aria-label="{{ socialLabel }}"<# } #>>
                  <# if ( socialIconMarkup ) { #>{{{ socialIconMarkup }}}<# } #>
                  <# if ( socialLabel ) { #>
                    <span {{{ view.getRenderAttributeString( labelKey ) }}}>{{ socialLabel }}</span>
                  <# } #>
                </a>
              </li>
            <# } ); #>
          </ul>
        <# } #>

      </div><!-- /.tms-team-member__body -->

      <# if ( linkType === 'box' && hasLink ) {
        var coverLabel = name || coverLabelFallback;
      #>
        <a href="{{ linkHref }}"
           class="tms-team-member__cover-link"
           aria-label="{{ coverLabel }}"></a>
      <# } #>

    </article><!-- /.tms-team-member -->

    <?php
  }
}
