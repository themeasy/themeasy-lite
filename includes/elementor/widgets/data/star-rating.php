<?php
/**
 * Themeasy Elementor Widget: Star Rating
 *
 * A compact, accessible rating display: a row of icons with a precise
 * per-star fractional fill (whole, half, or any decimal), an optional title,
 * the numeric value, and a free-form caption (e.g. "based on 124 reviews").
 *
 * The fill is pixel-accurate because each star layers the identical icon twice
 * (an empty base + a width-clipped fill overlay) at the same size and position,
 * so clipping reveals an exact fraction regardless of the icon's glyph width.
 * Works with both font icons and inline SVGs (solid via fill, outline via
 * stroke). Pure CSS + PHP — no JavaScript and no vendor library.
 *
 * @package Themeasy
 * @since 1.0.0
 */

namespace Themeasy\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

/**
 * Responsible for registering controls and rendering the widget.
 */
class StarRating extends Widget_Base {
  public function get_name() {
    return 'themeasy-star-rating';
  }

  public function get_title() {
    return esc_html__( 'Star Rating', 'themeasy-lite' );
  }

  public function get_icon() {
    return 'eicon-rating';
  }

  public function get_custom_help_url() {
    return 'https://hub.themeasy.co/docs';
  }

  public function get_categories() {
    return ['themeasy_data_category', 'themeasy_content_category'];
  }

  public function get_keywords() {
    return ['themeasy', 'rating', 'stars', 'star', 'review', 'score', 'testimonial'];
  }

  public function get_style_depends() {
    return ['themeasy-star-rating'];
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
    // Content section: Rating
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'star_rating_content_section',
        [
          'label' => esc_html__( 'Rating', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $this->add_control(
        'rating',
        [
          'label' => esc_html__( 'Rating Value', 'themeasy-lite' ),
          'type' => Controls_Manager::NUMBER,
          'min' => 0,
          'max' => 10,
          'step' => 0.1,
          'default' => 4.5,
          'description' => esc_html__( 'Supports decimals for partial stars (e.g. 4.5).', 'themeasy-lite' ),
        ]
      );

      $this->add_control(
        'max_stars',
        [
          'label' => esc_html__( 'Total Stars', 'themeasy-lite' ),
          'type' => Controls_Manager::NUMBER,
          'min' => 1,
          'max' => 10,
          'step' => 1,
          'default' => 5,
        ]
      );

      $this->add_control(
        'star_icon',
        [
          'label' => esc_html__( 'Star Icon', 'themeasy-lite' ),
          'type' => Controls_Manager::ICONS,
          'skin' => 'inline',
          'label_block' => false,
          'default' => [
            'value' => 'fas fa-star',
            'library' => 'fa-solid',
          ],
        ]
      );

      $this->add_control(
        'title',
        [
          'label' => esc_html__( 'Title', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'separator' => 'before',
          'default' => '',
          'placeholder' => esc_html__( 'e.g. Customer Rating', 'themeasy-lite' ),
        ]
      );

      $this->add_control(
        'show_value',
        [
          'label' => esc_html__( 'Show Rating Number', 'themeasy-lite' ),
          'type' => Controls_Manager::SWITCHER,
          'label_on' => esc_html__( 'Yes', 'themeasy-lite' ),
          'label_off' => esc_html__( 'No', 'themeasy-lite' ),
          'return_value' => 'yes',
          'default' => 'yes',
        ]
      );

      $this->add_control(
        'count_text',
        [
          'label' => esc_html__( 'Caption', 'themeasy-lite' ),
          'type' => Controls_Manager::TEXT,
          'default' => '',
          'placeholder' => esc_html__( 'e.g. based on 124 reviews', 'themeasy-lite' ),
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Content section: Layout
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'star_rating_layout_section',
        [
          'label' => esc_html__( 'Layout', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_CONTENT,
        ]
      );

      $this->add_responsive_control(
        'alignment',
        [
          'label' => esc_html__( 'Alignment', 'themeasy-lite' ),
          'type' => Controls_Manager::CHOOSE,
          'options' => [
            'left' => [
              'title' => esc_html__( 'Left', 'themeasy-lite' ),
              'icon' => 'eicon-text-align-left',
            ],
            'center' => [
              'title' => esc_html__( 'Center', 'themeasy-lite' ),
              'icon' => 'eicon-text-align-center',
            ],
            'right' => [
              'title' => esc_html__( 'Right', 'themeasy-lite' ),
              'icon' => 'eicon-text-align-right',
            ],
          ],
          'default' => 'left',
          'toggle' => false,
          'selectors' => [
            '{{WRAPPER}} .tms-star-rating' => 'align-items: {{VALUE}};',
            '{{WRAPPER}} .tms-star-rating__row' => 'justify-content: {{VALUE}};',
          ],
          'selectors_dictionary' => [
            'left' => 'flex-start',
            'center' => 'center',
            'right' => 'flex-end',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Stars
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'star_rating_stars_styles_section',
        [
          'label' => esc_html__( 'Stars', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_responsive_control(
        'stars_size',
        [
          'label' => esc_html__( 'Size', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'em', 'rem'],
          'range' => [
            'px' => [
              'min' => 8,
              'max' => 96,
              'step' => 1,
            ],
            'em' => [
              'min' => 0.5,
              'max' => 6,
              'step' => 0.1,
            ],
          ],
          'default' => [
            'unit' => 'px',
            'size' => 24,
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-star-rating__stars' => 'font-size: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_responsive_control(
        'stars_gap',
        [
          'label' => esc_html__( 'Spacing', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'em'],
          'range' => [
            'px' => [
              'min' => 0,
              'max' => 30,
              'step' => 1,
            ],
            'em' => [
              'min' => 0,
              'max' => 2,
              'step' => 0.05,
            ],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-star-rating__stars' => 'gap: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      // No defaults on these color controls: the CSS carries the defaults
      // (empty = the --tms-border-color token so it adapts to dark mode; fill =
      // a warm amber, since no design token maps to "rating gold"). The control
      // only emits a rule once the user picks a value.
      $this->add_control(
        'fill_color',
        [
          'label' => esc_html__( 'Fill Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-star-rating__star-fill' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_control(
        'empty_color',
        [
          'label' => esc_html__( 'Empty Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-star-rating__star-bg' => 'color: {{VALUE}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Title
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'star_rating_title_styles_section',
        [
          'label' => esc_html__( 'Title', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
          'condition' => [
            'title!' => '',
          ],
        ]
      );

      $this->add_control(
        'title_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-star-rating__title' => 'color: {{VALUE}};',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'title_typography',
          'selector' => '{{WRAPPER}} .tms-star-rating__title',
        ]
      );

      $this->add_responsive_control(
        'title_spacing',
        [
          'label' => esc_html__( 'Spacing', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px'],
          'range' => [
            'px' => [
              'min' => 0,
              'max' => 40,
              'step' => 1,
            ],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-star-rating__title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

    $this->end_controls_section();

    // ------------------------------------------------------------------------
    // Style section: Value & Caption
    // ------------------------------------------------------------------------
    $this->start_controls_section(
      'star_rating_text_styles_section',
        [
          'label' => esc_html__( 'Value & Caption', 'themeasy-lite' ),
          'tab' => Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_responsive_control(
        'text_gap',
        [
          'label' => esc_html__( 'Gap', 'themeasy-lite' ),
          'description' => esc_html__( 'Space between the stars, the value and the caption.', 'themeasy-lite' ),
          'type' => Controls_Manager::SLIDER,
          'size_units' => ['px', 'em'],
          'range' => [
            'px' => [
              'min' => 0,
              'max' => 40,
              'step' => 1,
            ],
          ],
          'selectors' => [
            '{{WRAPPER}} .tms-star-rating__row' => 'gap: {{SIZE}}{{UNIT}};',
          ],
        ]
      );

      $this->add_control(
        'value_heading',
        [
          'label' => esc_html__( 'Rating Number', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
          'condition' => [
            'show_value' => 'yes',
          ],
        ]
      );

      $this->add_control(
        'value_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-star-rating__value' => 'color: {{VALUE}};',
          ],
          'condition' => [
            'show_value' => 'yes',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'value_typography',
          'selector' => '{{WRAPPER}} .tms-star-rating__value',
          'condition' => [
            'show_value' => 'yes',
          ],
        ]
      );

      $this->add_control(
        'count_heading',
        [
          'label' => esc_html__( 'Caption', 'themeasy-lite' ),
          'type' => Controls_Manager::HEADING,
          'separator' => 'before',
          'condition' => [
            'count_text!' => '',
          ],
        ]
      );

      $this->add_control(
        'count_color',
        [
          'label' => esc_html__( 'Color', 'themeasy-lite' ),
          'type' => Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .tms-star-rating__count' => 'color: {{VALUE}};',
          ],
          'condition' => [
            'count_text!' => '',
          ],
        ]
      );

      $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
          'name' => 'count_typography',
          'selector' => '{{WRAPPER}} .tms-star-rating__count',
          'condition' => [
            'count_text!' => '',
          ],
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
    $max_stars = max( 1, absint( $settings['max_stars'] ?? 5 ) );
    $rating = isset( $settings['rating'] ) ? (float) $settings['rating'] : 0.0;
    $rating = max( 0.0, min( $rating, (float) $max_stars ) );

    $icon = is_array( $settings['star_icon'] ?? null ) ? $settings['star_icon'] : [];
    $title = $settings['title'] ?? '';
    $show_value = ( $settings['show_value'] ?? '' ) === 'yes';
    $count_text = $settings['count_text'] ?? '';

    // Render the icon once and reuse it for the base + fill layer of every star.
    // Fall back to a solid star through the same helper if the control is empty,
    // so there is no hand-written icon markup to keep escaped or in sync.
    $icon_setting = !empty( $icon['value'] ) ? $icon : ['value' => 'fas fa-star', 'library' => 'fa-solid'];
    $icon_html = themeasy_render_icon_html(
      $icon_setting,
      ['class' => 'tms-star-rating__icon', 'aria-hidden' => 'true']
    );

    // Trim a trailing ".0" so a whole rating reads "4", a partial one "4.5".
    $rating_display = ( floor( $rating ) === $rating )
      ? (string) (int) $rating
      : (string) round( $rating, 1 );

    /* translators: 1: rating value (e.g. 4.5), 2: maximum number of stars (e.g. 5). */
    $aria_label = sprintf( __( 'Rating: %1$s out of %2$s', 'themeasy-lite' ), $rating_display, $max_stars );

    // ------------------------------------------------------------------------
    // Wrapper classes + attributes.
    // ------------------------------------------------------------------------
    $wrapper_classes = ['tms-star-rating'];
    $wrapper_atts = [];

    $wrapper_classes_output = implode( ' ', array_filter( $wrapper_classes ) );

    // ------------------------------------------------------------------------
    // Render attributes.
    // ------------------------------------------------------------------------
    $this->add_render_attribute( 'title', 'class', 'tms-star-rating__title' );
    $this->add_inline_editing_attributes( 'title', 'none' );

    $this->add_render_attribute( 'count', 'class', 'tms-star-rating__count' );
    $this->add_inline_editing_attributes( 'count', 'none' );

    // ------------------------------------------------------------------------
    // Output.
    // ------------------------------------------------------------------------
    ?>
    <div class="<?php echo esc_attr( $wrapper_classes_output ); ?>"<?php themeasy_print_html_attributes( $wrapper_atts ); ?>>

      <?php if ( $title ) : ?>
        <span <?php $this->print_render_attribute_string( 'title' ); ?>><?php echo esc_html( $title ); ?></span>
      <?php endif; ?>

      <div class="tms-star-rating__row">
        <div class="tms-star-rating__stars" role="img" aria-label="<?php echo esc_attr( $aria_label ); ?>">

          <?php for ( $i = 0; $i < $max_stars; $i++ ) : ?>
            <?php
            // Fraction of THIS star that is filled (1 = full, 0.5 = half, 0 = empty).
            $fill = max( 0.0, min( 1.0, $rating - $i ) );
            $pct = round( $fill * 100, 2 );
            ?>
            <span class="tms-star-rating__star">
              <span class="tms-star-rating__star-bg"><?php echo wp_kses( $icon_html, themeasy_get_icon_kses_tags() ); ?></span>
              <span class="tms-star-rating__star-fill" style="width: <?php echo esc_attr( (string) $pct ); ?>%;"><?php echo wp_kses( $icon_html, themeasy_get_icon_kses_tags() ); ?></span>
            </span>
          <?php endfor; ?>

        </div>

        <?php if ( $show_value ) : ?>
          <span class="tms-star-rating__value"><?php echo esc_html( $rating_display ); ?></span>
        <?php endif; ?>

        <?php if ( $count_text ) : ?>
          <span <?php $this->print_render_attribute_string( 'count' ); ?>><?php echo esc_html( $count_text ); ?></span>
        <?php endif; ?>
      </div>

    </div><!-- /.tms-star-rating -->
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
      // Settings.
      // ------------------------------------------------------------------------
      var maxStars = Math.max( 1, parseInt( settings.max_stars, 10 ) || 5 );
      var rating   = parseFloat( settings.rating ) || 0;
      rating = Math.max( 0, Math.min( rating, maxStars ) );

      var title     = settings.title || '';
      var showValue = settings.show_value === 'yes';
      var countText = settings.count_text || '';

      var iconMarkup = ( window.Themeasy && window.Themeasy.renderIconMarkup )
        ? window.Themeasy.renderIconMarkup(
            view,
            settings.star_icon,
            { value: 'fas fa-star', library: 'fa-solid' },
            { 'class': 'tms-star-rating__icon', 'aria-hidden': 'true' }
          )
        : '<i class="tms-star-rating__icon fas fa-star" aria-hidden="true"></i>';

      var ratingDisplay = ( Math.floor( rating ) === rating )
        ? String( rating )
        : String( Math.round( rating * 10 ) / 10 );

      var ariaLabelTemplate = '<?php
        /* translators: 1: rating value (e.g. 4.5), 2: maximum number of stars (e.g. 5). */
        echo esc_js( __( 'Rating: %1$s out of %2$s', 'themeasy-lite' ) );
      ?>';
      var ariaLabel = ariaLabelTemplate.replace( '%1$s', ratingDisplay ).replace( '%2$s', maxStars );

      // ------------------------------------------------------------------------
      // Wrapper classes + attributes.
      // ------------------------------------------------------------------------
      var wrapperClasses = [ 'tms-star-rating' ];
      var wrapperAtts    = {};

      var wrapperClassStr = wrapperClasses.filter( Boolean ).join( ' ' );

      // ------------------------------------------------------------------------
      // Render attributes.
      // ------------------------------------------------------------------------
      view.addRenderAttribute( 'title', 'class', 'tms-star-rating__title' );
      view.addInlineEditingAttributes( 'title', 'none' );

      view.addRenderAttribute( 'count', 'class', 'tms-star-rating__count' );
      view.addInlineEditingAttributes( 'count', 'none' );
    #>

    <div class="{{ wrapperClassStr }}" {{{ Themeasy.htmlAttributes( wrapperAtts ) }}}>

      <# if ( title ) { #>
        <span {{{ view.getRenderAttributeString( 'title' ) }}}>{{ title }}</span>
      <# } #>

      <div class="tms-star-rating__row">
        <div class="tms-star-rating__stars" role="img" aria-label="{{ ariaLabel }}">

          <# for ( var i = 0; i < maxStars; i++ ) {
            var fill = Math.max( 0, Math.min( 1, rating - i ) );
            var pct  = Math.round( fill * 10000 ) / 100;
          #>
            <span class="tms-star-rating__star">
              <span class="tms-star-rating__star-bg">{{{ iconMarkup }}}</span>
              <span class="tms-star-rating__star-fill" style="width: {{ pct }}%;">{{{ iconMarkup }}}</span>
            </span>
          <# } #>

        </div>

        <# if ( showValue ) { #>
          <span class="tms-star-rating__value">{{ ratingDisplay }}</span>
        <# } #>

        <# if ( countText ) { #>
          <span {{{ view.getRenderAttributeString( 'count' ) }}}>{{ countText }}</span>
        <# } #>
      </div>

    </div><!-- /.tms-star-rating -->
    <?php
  }
}
