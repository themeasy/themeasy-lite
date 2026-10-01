<?php
/**
 * Themeasy Library — local (theme-bundled) template source.
 *
 * Perpetual themes ship a local, theme-scoped template library: Elementor JSON
 * files bundled inside the active theme (business-context §3, Path B — sections
 * and inner pages extracted from that theme's demos). This class scans the
 * theme's /library directory and merges those templates into the in-editor
 * panel alongside the cloud catalog. Local templates are never license-gated
 * and never touch the network: the local library must keep working
 * indefinitely and offline, with nothing to revoke — when the backstage is
 * unreachable, the panel degrades to the local set instead of an error.
 *
 * Directory layout (inside the parent theme):
 *   library/{group-slug}/{category-slug}/{template-slug}.json — grouped
 *     template ({group} = a top-level parent category such as blocks/pages;
 *     the panel renders groups as tabs and matches a group filter locally)
 *   library/{category-slug}/{template-slug}.json  — categorized template
 *   library/{template-slug}.json                  — uncategorized template
 *   library/.../{template-slug}.{png|jpg|jpeg|webp} — optional card cover,
 *     same basename as the JSON it illustrates.
 *
 * Each JSON file is an Elementor export envelope
 * ({ version, title, type, content, page_settings }); the editor's copy/paste
 * shape ({ type: "elementor", siteurl, elements }) and a bare _elementor_data
 * array are also accepted and normalized into the envelope. The card title
 * comes from the envelope's `title` when present, else it is humanized from
 * the file name. Files larger than MAX_JSON_BYTES are ignored.
 *
 * Ships in BOTH builds (no premium marker), like the rest of the Library
 * panel: whether local templates exist is decided by the active theme, not by
 * the plan.
 *
 * @package Themeasy\Elementor
 * @since 1.0.0
 */

namespace Themeasy\Elementor;

defined( 'ABSPATH' ) || exit;

/**
 * Scans, lists and serves the theme-bundled template JSON files.
 */
class Library_Local {
  /**
   * Transient holding the scanned card index (invalidated by the signature).
   * The v2 suffix invalidates pre-hierarchy caches whose items lack `group`.
   *
   * @var string
   */
  private const CACHE_KEY = 'themeasy_lib_local_index_v2';

  /**
   * Upper size bound for a template JSON (mirrors the backstage upload cap).
   *
   * @var int
   */
  private const MAX_JSON_BYTES = 2097152; // 2 MB.

  /**
   * Cover sidecar extensions, probed in order.
   *
   * @var string[]
   */
  private const COVER_EXTENSIONS = ['png', 'jpg', 'jpeg', 'webp'];

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
   * Absolute path of the local template directory (parent theme /library).
   *
   * @return string
   */
  public function dir(): string {
    $dir = trailingslashit( get_template_directory() ) . 'library';

    /**
     * Filter the local (theme-bundled) template library directory.
     *
     * @param string $dir Absolute path to the directory holding the JSON files.
     */
    $dir = (string) apply_filters( 'themeasy/library_local_dir', $dir );

    return untrailingslashit( wp_normalize_path( $dir ) );
  }

  /**
   * Merge the local templates into a proxied /templates listing result.
   *
   * Local items are prepended on page 1 and counted into `total` on every page
   * (so the footer count is stable while paging). When the backstage is
   * unreachable, the local set is served alone — the offline guarantee of the
   * theme-bundled library.
   *
   * @param array<string,mixed>      $result Internal proxy result envelope.
   * @param array<string,string|int> $query  Sanitized listing query.
   * @return array<string,mixed>
   */
  public function merge_templates( array $result, array $query ): array {
    $tag = isset( $query['tag'] ) ? (string) $query['tag'] : '';

    // Local templates carry no tags, so an active tag filter matches none.
    $local = ( '' === $tag )
      ? $this->items(
        isset( $query['category'] ) ? (string) $query['category'] : '',
        isset( $query['search'] ) ? (string) $query['search'] : ''
      )
      : [];

    if ( empty( $local ) ) {
      return $result;
    }

    $page = isset( $query['page'] ) ? max( 1, (int) $query['page'] ) : 1;

    if ( $result['ok'] && is_array( $result['data'] ) ) {
      $data = $result['data'];
      $data['items'] = ( isset( $data['items'] ) && is_array( $data['items'] ) ) ? $data['items'] : [];

      // The synced snapshot also lives in the cloud catalog (published `pro`
      // for subscribers), so on this theme's own site the same template can
      // arrive from both sources — the local copy wins and the cloud twin is
      // dropped. `total` is deliberately NOT adjusted: the drop count varies
      // per page and a stable footer count beats exactness by a few.
      $keys = [];
      foreach ( $local as $item ) {
        $keys[$this->dedupe_key( $item )] = true;
      }

      $kept = [];
      foreach ( $data['items'] as $item ) {
        if ( is_array( $item ) && isset( $keys[$this->dedupe_key( $item )] ) ) {
          continue;
        }

        $kept[] = $item;
      }

      $data['items'] = $kept;

      if ( 1 === $page ) {
        $data['items'] = array_merge( $local, $data['items'] );
      }

      $data['total'] = ( isset( $data['total'] ) ? (int) $data['total'] : 0 ) + count( $local );
      $result['data'] = $data;

      return $result;
    }

    // Backstage unreachable: the theme-bundled library still works on its own.
    return [
      'ok' => true,
      'code' => '',
      'message' => '',
      'data' => [
        'items' => $local,
        'total' => count( $local ),
        'page' => 1,
        'per_page' => count( $local ),
        'pages' => 1,
      ],
    ];
  }

  /**
   * Merge the local template categories into a proxied /categories result.
   *
   * Counts are added onto remote categories sharing the slug; local-only
   * categories are appended. On a remote failure the local set is served alone
   * so the sidebar never collapses to "All categories" while local items show.
   *
   * Local terms carry the additive `parent` field (the item's group dir, or
   * null) and each group is emitted as a root term of its own — offline, the
   * panel can still derive its tabs from the local set alone. On a slug match
   * the remote term wins (only the count is added), so remote parent data is
   * never overwritten by the humanized local guess.
   *
   * @param array<string,mixed> $result Internal proxy result envelope.
   * @return array<string,mixed>
   */
  public function merge_categories( array $result ): array {
    $local = [];

    foreach ( $this->templates() as $item ) {
      if ( !is_array( $item['category'] ) ) {
        continue;
      }

      $slug = $item['category']['slug'];
      $group = ( isset( $item['group'] ) && is_string( $item['group'] ) ) ? $item['group'] : '';

      if ( !isset( $local[$slug] ) ) {
        $local[$slug] = [
          'slug' => $slug,
          'name' => $item['category']['name'],
          'count' => 0,
          'parent' => ( '' !== $group ) ? $group : null,
        ];
      }

      $local[$slug]['count']++;

      // The group itself must exist in the merged list (direct count 0, like
      // the backstage emits it) so the tab bar can build from local data.
      if ( '' !== $group && !isset( $local[$group] ) ) {
        $local[$group] = [
          'slug' => $group,
          'name' => ucwords( str_replace( '-', ' ', $group ) ),
          'count' => 0,
          'parent' => null,
        ];
      }
    }

    if ( empty( $local ) ) {
      return $result;
    }

    $remote = ( $result['ok'] && is_array( $result['data'] ) ) ? $result['data'] : [];
    $merged = [];

    foreach ( $remote as $term ) {
      if ( !is_array( $term ) || empty( $term['slug'] ) ) {
        continue;
      }

      $slug = (string) $term['slug'];

      if ( isset( $local[$slug] ) ) {
        $term['count'] = (int) ( $term['count'] ?? 0 ) + $local[$slug]['count'];
        unset( $local[$slug] );
      }

      $merged[] = $term;
    }

    foreach ( $local as $term ) {
      $merged[] = $term;
    }

    return ['ok' => true, 'code' => '', 'message' => '', 'data' => array_values( $merged )];
  }

  /**
   * Identity of a card item for local-vs-cloud deduplication.
   *
   * Category slug + template slug — the sync writes the CMS slugs verbatim,
   * so a snapshot file and its cloud twin always share both. Matching on the
   * pair (not the slug alone) keeps an unrelated same-slug template in another
   * category visible.
   *
   * @param array<string,mixed> $item Card item (local or remote shape).
   * @return string
   */
  private function dedupe_key( array $item ): string {
    $category = ( isset( $item['category'] ) && is_array( $item['category'] ) && isset( $item['category']['slug'] ) )
      ? (string) $item['category']['slug']
      : '';

    return $category . '/' . ( isset( $item['slug'] ) ? (string) $item['slug'] : '' );
  }

  /**
   * The local card items matching the given filters.
   *
   * A category filter matches the item's own category slug OR its group dir —
   * the panel's group tabs send the parent slug, which the backstage expands
   * to children server-side (tax_query include_children); this is the local
   * mirror of that expansion.
   *
   * @param string $category Category or group slug filter ('' = all).
   * @param string $search   Search term ('' = all).
   * @return array<int,array<string,mixed>>
   */
  public function items( string $category = '', string $search = '' ): array {
    $items = [];
    $needle = strtolower( trim( $search ) );

    foreach ( $this->templates() as $item ) {
      if ( '' !== $category ) {
        $slug = is_array( $item['category'] ) ? $item['category']['slug'] : '';
        $group = ( isset( $item['group'] ) && is_string( $item['group'] ) ) ? $item['group'] : '';

        if ( $slug !== $category && $group !== $category ) {
          continue;
        }
      }

      if ( '' !== $needle ) {
        $haystack = strtolower( $item['title'] . ' ' . $item['slug'] );

        if ( false === strpos( $haystack, $needle ) ) {
          continue;
        }
      }

      $items[] = $item;
    }

    return $items;
  }

  /**
   * The full Elementor export envelope for one local template.
   *
   * The key is validated against the scan index — the client never names a
   * path, so the lookup cannot traverse outside the library directory.
   *
   * @param string $key Local template key ({group}/{category}/{slug},
   *                    {category}/{slug} or {slug}).
   * @return array<string,mixed>|null Null when the key or file is invalid.
   */
  public function envelope( string $key ): ?array {
    $key = $this->sanitize_key( $key );

    if ( '' === $key ) {
      return null;
    }

    $index = $this->index();

    if ( !isset( $index[$key] ) ) {
      return null;
    }

    $decoded = $this->read_json( $index[$key]['path'] );

    if ( !is_array( $decoded ) ) {
      return null;
    }

    // Full Elementor export envelope: pass through with a normalized content list.
    if ( isset( $decoded['content'] ) && is_array( $decoded['content'] ) ) {
      $decoded['content'] = array_values( $decoded['content'] );

      return $decoded;
    }

    // Editor copy/paste shape ({ type: "elementor", siteurl, elements }): same
    // element models as the export envelope, different wrapper — normalize it.
    if ( isset( $decoded['elements'] ) && is_array( $decoded['elements'] ) ) {
      return [
        'version' => '0.4',
        'title' => $index[$key]['slug'],
        'type' => 'section',
        'content' => array_values( $decoded['elements'] ),
        'page_settings' => [],
      ];
    }

    // Bare _elementor_data array: wrap it into the envelope shape.
    if ( isset( $decoded[0] ) ) {
      return [
        'version' => '0.4',
        'title' => $index[$key]['slug'],
        'type' => 'section',
        'content' => array_values( $decoded ),
        'page_settings' => [],
      ];
    }

    return null;
  }

  /**
   * All local templates as card items, cached until the directory changes.
   *
   * @return array<int,array<string,mixed>>
   */
  private function templates(): array {
    $index = $this->index();

    if ( empty( $index ) ) {
      return [];
    }

    $sig = $this->signature( $index );
    $cached = get_transient( self::CACHE_KEY );

    if ( is_array( $cached ) && ( $cached['sig'] ?? '' ) === $sig && is_array( $cached['items'] ?? null ) ) {
      return $cached['items'];
    }

    $items = [];

    foreach ( $index as $key => $entry ) {
      $items[] = $this->build_item( $key, $entry );
    }

    $items = array_values( array_filter( $items ) );

    set_transient( self::CACHE_KEY, ['sig' => $sig, 'items' => $items], DAY_IN_SECONDS );

    return $items;
  }

  /**
   * Scan the library directory into a key => { path, group, category, slug }
   * index.
   *
   * Three depths coexist: root JSONs (uncategorized), one dir level (category,
   * no group) and two dir levels (group/category — the tabbed layout written
   * by sync-library.mjs). A dir name is a category for its direct JSONs and a
   * group for its subdirs' JSONs, so mixed layouts stay deterministic.
   *
   * @return array<string,array{path:string,group:string,category:string,slug:string}>
   */
  private function index(): array {
    $dir = $this->dir();

    if ( '' === $dir || !is_dir( $dir ) ) {
      return [];
    }

    $files = [];

    foreach ( (array) glob( $dir . '/*.json' ) as $path ) {
      if ( is_string( $path ) ) {
        $files[] = ['path' => wp_normalize_path( $path ), 'group' => '', 'category' => ''];
      }
    }

    foreach ( (array) glob( $dir . '/*', GLOB_ONLYDIR ) as $subdir ) {
      if ( !is_string( $subdir ) ) {
        continue;
      }

      $category = sanitize_title( basename( $subdir ) );

      if ( '' === $category ) {
        continue;
      }

      foreach ( (array) glob( $subdir . '/*.json' ) as $path ) {
        if ( is_string( $path ) ) {
          $files[] = ['path' => wp_normalize_path( $path ), 'group' => '', 'category' => $category];
        }
      }

      foreach ( (array) glob( $subdir . '/*', GLOB_ONLYDIR ) as $subsubdir ) {
        if ( !is_string( $subsubdir ) ) {
          continue;
        }

        $child = sanitize_title( basename( $subsubdir ) );

        if ( '' === $child ) {
          continue;
        }

        foreach ( (array) glob( $subsubdir . '/*.json' ) as $path ) {
          if ( is_string( $path ) ) {
            $files[] = ['path' => wp_normalize_path( $path ), 'group' => $category, 'category' => $child];
          }
        }
      }
    }

    $index = [];

    foreach ( $files as $file ) {
      $slug = sanitize_title( basename( $file['path'], '.json' ) );

      if ( '' === $slug ) {
        continue;
      }

      $key = implode( '/', array_filter( [$file['group'], $file['category'], $slug], 'strlen' ) );

      // First file wins on a duplicate key (same slug twice in one category).
      if ( isset( $index[$key] ) ) {
        continue;
      }

      $index[$key] = [
        'path' => $file['path'],
        'group' => $file['group'],
        'category' => $file['category'],
        'slug' => $slug,
      ];
    }

    return $index;
  }

  /**
   * Build one card item (frozen list-item shape + source/key/group extras).
   *
   * `group` (the parent dir of a two-level layout, null otherwise) mirrors the
   * backstage's category hierarchy: items() matches it when the panel filters
   * by a group tab. Like source/key it rides along the frozen shape; the modal
   * ignores it on the card itself.
   *
   * @param string                                                       $key   Local template key.
   * @param array{path:string,group:string,category:string,slug:string}  $entry Index entry.
   * @return array<string,mixed>|null Null when the file is unreadable/oversized.
   */
  private function build_item( string $key, array $entry ): ?array {
    $decoded = $this->read_json( $entry['path'] );

    if ( null === $decoded ) {
      return null;
    }

    $title = '';

    if ( !empty( $decoded['title'] ) && is_string( $decoded['title'] ) ) {
      // Decode entities from pre-fix snapshots (the CMS once exported
      // kses-encoded titles like "Brass &amp; Blade"); the card renders via
      // textContent, so a leftover entity would show literally.
      $title = wp_specialchars_decode( sanitize_text_field( $decoded['title'] ), ENT_QUOTES );
    }

    if ( '' === $title ) {
      $title = ucwords( str_replace( ['-', '_'], ' ', $entry['slug'] ) );
    }

    return [
      'id' => 0,
      'source' => 'theme',
      'key' => $key,
      'group' => ( '' !== $entry['group'] ) ? $entry['group'] : null,
      'title' => $title,
      'slug' => $entry['slug'],
      'category' => ( '' !== $entry['category'] )
        ? [
          'slug' => $entry['category'],
          'name' => ucwords( str_replace( '-', ' ', $entry['category'] ) ),
        ]
        : null,
      'tags' => [],
      'tier' => 'free',
      'cover' => $this->cover_url( $entry['path'] ),
      'preview_url' => null,
      'url' => null,
      'updated' => gmdate( 'c', (int) filemtime( $entry['path'] ) ),
    ];
  }

  /**
   * Public URL of the cover sidecar image next to a template JSON, if any.
   *
   * @param string $json_path Absolute template JSON path.
   * @return string|null
   */
  private function cover_url( string $json_path ): ?string {
    $base = substr( $json_path, 0, -5 ); // Strip the ".json" suffix.

    foreach ( self::COVER_EXTENSIONS as $ext ) {
      $file = $base . '.' . $ext;

      if ( is_file( $file ) ) {
        return $this->path_to_theme_uri( $file );
      }
    }

    return null;
  }

  /**
   * Map an absolute path inside the parent theme to its public URI.
   *
   * @param string $path Absolute file path.
   * @return string|null Null when the path is outside the theme (no known URL).
   */
  private function path_to_theme_uri( string $path ): ?string {
    $theme_dir = untrailingslashit( wp_normalize_path( get_template_directory() ) );
    $path = wp_normalize_path( $path );

    if ( 0 !== strpos( $path, trailingslashit( $theme_dir ) ) ) {
      return null;
    }

    return get_template_directory_uri() . substr( $path, strlen( $theme_dir ) );
  }

  /**
   * Change signature of the scanned files (theme + path|mtime list).
   *
   * @param array<string,array{path:string,category:string,slug:string}> $index Scan index.
   * @return string
   */
  private function signature( array $index ): string {
    $parts = [get_template()];

    foreach ( $index as $entry ) {
      $parts[] = $entry['path'] . '|' . (string) filemtime( $entry['path'] );
    }

    return md5( implode( ';', $parts ) );
  }

  /**
   * Read and decode a template JSON file, bounded by MAX_JSON_BYTES.
   *
   * @param string $path Absolute file path.
   * @return array<mixed>|null
   */
  private function read_json( string $path ): ?array {
    if ( !is_file( $path ) || !is_readable( $path ) ) {
      return null;
    }

    $size = filesize( $path );

    if ( false === $size || $size < 2 || $size > self::MAX_JSON_BYTES ) {
      return null;
    }

    $raw = file_get_contents( $path ); // phpcs:ignore WordPressVIPMinimum.Performance.FetchingRemoteData.FileGetContentsUnknown -- local theme file.

    if ( false === $raw ) {
      return null;
    }

    $decoded = json_decode( $raw, true );

    return is_array( $decoded ) ? $decoded : null;
  }

  /**
   * Allowlist a client-supplied local template key.
   *
   * Keys are built from sanitize_title() segments, so the accepted charset is
   * lowercase alphanumerics + dashes, with at most two path separators
   * ({group}/{category}/{slug} at the deepest).
   *
   * @param string $key Raw key.
   * @return string Empty string when the key is malformed.
   */
  private function sanitize_key( string $key ): string {
    $key = trim( $key );

    return preg_match( '#^[a-z0-9_-]+(/[a-z0-9_-]+){0,2}$#', $key ) ? $key : '';
  }
}
