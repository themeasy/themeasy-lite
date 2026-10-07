<?php
/**
 * Themeasy Library — server-side proxy to the backstage catalog.
 *
 * The in-editor panel never talks to cms.themeasy.co directly: the catalog is a
 * cross-origin host. So the browser calls these same-origin admin-ajax
 * endpoints (nonce + capability checked) and this class proxies to the frozen
 * `themeasy-lib/v1` REST server-to-server. It also shields the editor from CORS
 * and adds a thin cache layer (fresh TTL + a day-long last-good fallback) over
 * the public list endpoints, retrying transient upstream failures once.
 *
 * Theme-bundled templates (Library_Local) are merged into the listing/categories
 * responses and served by the insert endpoint from local JSON, with no
 * proxying: the local library works offline by design (business-context, Path B).
 *
 * The backstage serves a `free` template to anyone and answers 403 for a `pro`
 * one.
 *
 * @package Themeasy\Elementor
 * @since 1.0.0
 */

namespace Themeasy\Elementor;

defined( 'ABSPATH' ) || exit;

/**
 * Wires the admin-ajax endpoints behind the Themeasy Library editor panel.
 */
class Library_Ajax {
  /**
   * Shared nonce action for every Library endpoint.
   *
   * @var string
   */
  public const NONCE = 'themeasy_library';

  /**
   * Capability required to browse/insert (the Elementor editing baseline).
   *
   * @var string
   */
  private const CAPABILITY = 'edit_posts';

  /**
   * Frozen REST namespace on the backstage (cms.themeasy.co).
   *
   * @var string
   */
  private const REST_NAMESPACE = 'themeasy-lib/v1';

  /**
   * Default backstage origin when nothing overrides it.
   *
   * @var string
   */
  private const DEFAULT_ORIGIN = 'https://cms.themeasy.co';

  /**
   * Whitelisted read-only resources -> backstage REST paths.
   *
   * A fixed map is the allowlist: the browser names a resource, never a raw path,
   * so it can never coerce the proxy into hitting an arbitrary URL.
   *
   * @var array<string,string>
   */
  private const RESOURCES = [
    'templates' => '/templates',
    'categories' => '/categories',
    'tags' => '/tags',
    'stats' => '/stats',
  ];

  /**
   * Cache TTL per resource (seconds). Stable lists live longer than the query-
   * dependent template listing.
   *
   * @var array<string,int>
   */
  private const CACHE_TTL = [
    'templates' => 5 * MINUTE_IN_SECONDS,
    'categories' => HOUR_IN_SECONDS,
    'tags' => HOUR_IN_SECONDS,
    'stats' => 15 * MINUTE_IN_SECONDS,
  ];

  /**
   * Singleton instance.
   *
   * @var self|null
   */
  private static ?self $instance = null;

  /**
   * Returns the singleton instance.
   *
   * @return self
   */
  public static function instance(): self {
    if ( null === self::$instance ) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  /**
   * Register the AJAX actions. Logged-in only — there is no nopriv surface.
   *
   * @return void
   */
  public function init(): void {
    add_action( 'wp_ajax_themeasy_library_browse', [$this, 'handle_browse'] );
    add_action( 'wp_ajax_themeasy_library_insert', [$this, 'handle_insert'] );
  }

  /**
   * Proxy a read-only catalog request (templates/categories/tags/stats).
   *
   * @return void
   */
  public function handle_browse(): void {
    $this->guard();

    $resource = isset( $_POST['resource'] ) ? sanitize_key( wp_unslash( $_POST['resource'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- guard() verifies the nonce.

    if ( !isset( self::RESOURCES[$resource] ) ) {
      wp_send_json_error(
        [
          'code' => 'resource',
          'message' => esc_html__( 'Unknown library resource.', 'themeasy-lite' ),
        ],
        400
      );
    }

    $query = ( 'templates' === $resource ) ? $this->read_templates_query() : [];
    $result = $this->fetch_cached( $resource, self::RESOURCES[$resource], $query );

    // Theme-bundled templates (local JSON, zero cloud dependency) ride along
    // the remote catalog — and keep the panel working when the backstage is
    // unreachable (the local library's offline guarantee).
    if ( 'templates' === $resource ) {
      $result = Library_Local::instance()->merge_templates( $result, $query );
    } elseif ( 'categories' === $resource ) {
      $result = Library_Local::instance()->merge_categories( $result );
    }

    if ( !$result['ok'] ) {
      // Generic public code — the upstream HTTP status stays server-side so the
      // response leaks no backstage topology.
      wp_send_json_error(
        [
          'code' => 'unavailable',
          'message' => $result['message'],
        ],
        502
      );
    }

    wp_send_json_success( $result['data'] );
  }

  /**
   * Fetch a template's Elementor export payload for one-click insertion.
   *
   * The backstage returns 200 for a `free` template and 403 for a `pro` one,
   * and this proxy relays the verdict. The payload is never cached.
   *
   * @return void
   */
  public function handle_insert(): void {
    $this->guard();

    // Theme-bundled template: served straight from the theme's local JSON,
    // never proxied (the local library ships with the theme and works offline).
    $source = isset( $_POST['source'] ) ? sanitize_key( wp_unslash( $_POST['source'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- guard() verifies the nonce.

    if ( 'theme' === $source ) {
      $this->send_local_template();
      return;
    }

    $id = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- guard() verifies the nonce.

    if ( $id < 1 ) {
      wp_send_json_error(
        [
          'code' => 'id',
          'message' => esc_html__( 'Invalid template.', 'themeasy-lite' ),
        ],
        400
      );
    }

    $result = $this->request( '/templates/' . $id . '/content' );

    if ( 403 === $result['status'] ) {
      wp_send_json_error(
        [
          'code' => 'forbidden',
          'message' => esc_html__( 'This template requires a Pro or Agency subscription.', 'themeasy-lite' ),
        ],
        403
      );
    }

    if ( !$result['ok'] || !is_array( $result['data'] ) ) {
      wp_send_json_error(
        [
          'code' => 'content',
          'message' => esc_html__( 'The template could not be loaded. Please try again.', 'themeasy-lite' ),
        ],
        502
      );
    }

    wp_send_json_success( $result['data'] );
  }

  /**
   * Serve a theme-bundled template envelope for insertion.
   *
   * The key is resolved against Library_Local's own scan index, so the client
   * can never address a file outside the theme's library directory.
   *
   * @return void
   */
  private function send_local_template(): void {
    $key = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- guard() verifies the nonce.
    $envelope = Library_Local::instance()->envelope( $key );

    if ( null === $envelope ) {
      // 'not_found', not 'content': a missing/malformed local file is
      // deterministic, so the client must not burn a retry on it.
      wp_send_json_error(
        [
          'code' => 'not_found',
          'message' => esc_html__( 'The template could not be loaded. Please try again.', 'themeasy-lite' ),
        ],
        404
      );
    }

    wp_send_json_success( $envelope );
  }

  /**
   * Verify the request method, nonce and capability, or terminate with JSON.
   *
   * @return void
   */
  private function guard(): void {
    if ( empty( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== strtoupper( (string) $_SERVER['REQUEST_METHOD'] ) ) {
      wp_send_json_error(
        [
          'code' => 'method',
          'message' => esc_html__( 'Invalid request method.', 'themeasy-lite' ),
        ],
        405
      );
    }

    if ( !check_ajax_referer( self::NONCE, 'nonce', false ) ) {
      wp_send_json_error(
        [
          'code' => 'nonce',
          'message' => esc_html__( 'Your session has expired. Please reload the editor.', 'themeasy-lite' ),
        ],
        403
      );
    }

    if ( !current_user_can( self::CAPABILITY ) ) {
      wp_send_json_error(
        [
          'code' => 'cap',
          'message' => esc_html__( 'You are not allowed to do this.', 'themeasy-lite' ),
        ],
        403
      );
    }
  }

  /**
   * Read and sanitize the templates listing query (filters + pagination).
   *
   * Only the frozen contract's parameters are forwarded; everything else is
   * dropped so the proxy cannot be used to smuggle arbitrary query strings.
   *
   * @return array<string,string|int>
   */
  private function read_templates_query(): array {
    // phpcs:disable WordPress.Security.NonceVerification.Missing -- guard() verified the nonce before this runs.
    $query = [];

    $category = isset( $_POST['category'] ) ? sanitize_title( wp_unslash( $_POST['category'] ) ) : '';
    if ( '' !== $category ) {
      $query['category'] = $category;
    }

    $tag = isset( $_POST['tag'] ) ? sanitize_title( wp_unslash( $_POST['tag'] ) ) : '';
    if ( '' !== $tag ) {
      $query['tag'] = $tag;
    }

    $search = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';
    if ( '' !== $search ) {
      $query['search'] = $search;
    }

    $query['page'] = isset( $_POST['page'] ) ? max( 1, absint( $_POST['page'] ) ) : 1;
    $query['per_page'] = isset( $_POST['per_page'] ) ? min( 48, max( 1, absint( $_POST['per_page'] ) ) ) : 24;
    // phpcs:enable WordPress.Security.NonceVerification.Missing

    return $query;
  }

  /**
   * Fetch a resource through a short-lived transient cache.
   *
   * Read-only list endpoints are public and identical for every site, so caching
   * them keeps the editor snappy and spares the backstage. The fresh cache is
   * bypassed under WP_DEBUG so local catalog edits show up immediately.
   *
   * Every success also refreshes a day-long "last good" copy, served when the
   * upstream fails: a slightly stale listing beats a Retry wall, and single
   * upstream hiccups stop reaching the user at all.
   *
   * @param string                    $resource Resource key (cache TTL selector).
   * @param string                    $path     Backstage REST path.
   * @param array<string,string|int>  $query    Query parameters.
   * @return array{ok:bool,code:string,message:string,data:mixed}
   */
  private function fetch_cached( string $resource, string $path, array $query ): array {
    $debug = defined( 'WP_DEBUG' ) && WP_DEBUG;
    $cache_key = 'themeasy_lib_' . md5( $resource . '|' . self::origin() . '|' . wp_json_encode( $query ) );
    $stale_key = $cache_key . '_stale';

    if ( !$debug ) {
      $cached = get_transient( $cache_key );
      if ( is_array( $cached ) ) {
        return ['ok' => true, 'code' => '', 'message' => '', 'data' => $cached];
      }
    }

    $result = $this->request( $path, $query );

    if ( $result['ok'] ) {
      if ( !$debug ) {
        $ttl = self::CACHE_TTL[$resource] ?? 5 * MINUTE_IN_SECONDS;
        set_transient( $cache_key, $result['data'], $ttl );
      }

      // The last-good copy is kept even under WP_DEBUG — it only ever serves
      // when the upstream fails, so it cannot mask fresh catalog edits.
      set_transient( $stale_key, $result['data'], DAY_IN_SECONDS );

      return $result;
    }

    $stale = get_transient( $stale_key );
    if ( is_array( $stale ) ) {
      return ['ok' => true, 'code' => '', 'message' => '', 'data' => $stale];
    }

    return $result;
  }

  /**
   * Perform a server-to-server GET against the backstage REST.
   *
   * @param string                   $path  REST path under the frozen namespace.
   * @param array<string,string|int> $query Query parameters.
   * @return array{ok:bool,status:int,code:string,message:string,data:mixed}
   */
  private function request( string $path, array $query = [] ): array {
    $url = self::rest_base() . $path;
    if ( !empty( $query ) ) {
      // add_query_arg url-encodes values itself (urlencode_deep), so the array
      // is passed raw — pre-encoding here would double-encode (e.g. a space would
      // arrive as %2520). All values are already sanitized upstream.
      $url = add_query_arg( $query, $url );
    }

    $headers = ['Accept' => 'application/json'];

    $args = [
      'timeout' => 12,
      'headers' => $headers,
    ];

    $response = wp_remote_get( $url, $args );

    // One transparent retry on a transport failure or 5xx: these GETs are
    // idempotent, and absorbing a single upstream hiccup here is what keeps the
    // modal from bubbling every blip to the user as a Retry prompt. The short
    // pause gives a momentary blip time to clear without doubling load
    // back-to-back on a struggling upstream.
    if ( is_wp_error( $response ) || (int) wp_remote_retrieve_response_code( $response ) >= 500 ) {
      usleep( 250000 );
      $response = wp_remote_get( $url, $args );
    }

    if ( is_wp_error( $response ) ) {
      return [
        'ok' => false,
        'status' => 0,
        'code' => 'transport',
        'message' => esc_html__( 'Could not reach the template library. Please try again.', 'themeasy-lite' ),
        'data' => null,
      ];
    }

    $status = (int) wp_remote_retrieve_response_code( $response );
    $data = json_decode( wp_remote_retrieve_body( $response ), true );

    if ( 200 !== $status || null === $data ) {
      return [
        'ok' => false,
        'status' => $status,
        'code' => 'http_' . $status,
        'message' => esc_html__( 'The template library returned an unexpected response.', 'themeasy-lite' ),
        'data' => $data,
      ];
    }

    return ['ok' => true, 'status' => 200, 'code' => '', 'message' => '', 'data' => $data];
  }

  /**
   * Full base URL of the backstage REST namespace.
   *
   * @return string
   */
  public static function rest_base(): string {
    return self::origin() . '/wp-json/' . self::REST_NAMESPACE;
  }

  /**
   * Resolve the backstage origin (cms.themeasy.co), validated.
   *
   * Order: THEMEASY_LIBRARY_ORIGIN constant -> legacy THEMEASY_LIBRARY_URL ->
   * themeasy/library_url filter -> default. Production accepts only HTTPS on a
   * themeasy.co host (the inserted JSON lands in the user's document, so the
   * source must be trusted). A WP_DEBUG escape hatch allows a plain-host
   * http(s) override for a LocalWP cms during dev.
   *
   * THEMEASY_LIBRARY_ORIGIN is the canonical override name: the legacy
   * THEMEASY_LIBRARY_URL collides with the backstage plugin's own plugin-dir
   * constant when both plugins share a site (this dev install and the cms
   * itself), which silently neutralized the documented override. The legacy
   * name is still read for back-compat; a plugin-dir URL never survives the
   * validation below, so the collision stays harmless.
   *
   * @return string
   */
  public static function origin(): string {
    $base = self::DEFAULT_ORIGIN;

    if ( defined( 'THEMEASY_LIBRARY_ORIGIN' ) && is_string( THEMEASY_LIBRARY_ORIGIN ) && '' !== THEMEASY_LIBRARY_ORIGIN ) {
      $base = THEMEASY_LIBRARY_ORIGIN;
    } elseif ( defined( 'THEMEASY_LIBRARY_URL' ) && is_string( THEMEASY_LIBRARY_URL ) && '' !== THEMEASY_LIBRARY_URL ) {
      $base = THEMEASY_LIBRARY_URL;
    }

    /**
     * Filter the Themeasy Library (backstage) origin. Must resolve to HTTPS on a
     * themeasy.co host in production; under WP_DEBUG a plain http(s) host is
     * allowed for local development.
     *
     * @param string $base Backstage origin URL.
     */
    $base = (string) apply_filters( 'themeasy/library_url', $base );
    $base = untrailingslashit( esc_url_raw( $base ) );

    if ( preg_match( '#^https://([a-z0-9-]+\.)*themeasy\.co$#i', $base ) ) {
      return $base;
    }

    if ( defined( 'WP_DEBUG' ) && WP_DEBUG && preg_match( '#^https?://[a-z0-9.\-]+(:\d+)?$#i', $base ) ) {
      return $base;
    }

    return self::DEFAULT_ORIGIN;
  }
}
