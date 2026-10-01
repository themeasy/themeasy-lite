<?php
/**
 * Themeasy class autoloader.
 *
 * Registers an SPL autoloader for the `Themeasy\` namespace so class resolution
 * no longer depends on the order of the manual require_once chains: a class
 * referenced before (or without) its explicit require is still resolved here,
 * removing the load-order fragility of a pure require-based bootstrap.
 *
 * The class map is built lazily — only the first time a `Themeasy\` class is
 * requested that was not already loaded — by tokenizing the plugin's PHP sources.
 * In normal operation the loaders still require everything up front, so the map
 * is never built and there is no scanning cost; the autoloader is the safety net
 * that makes the require chain non-load-bearing.
 *
 * Keep it that way: building the map reads and tokenizes every scanned file, which
 * costs 100ms+ per request on a real site. A module with lazy classes must resolve
 * them through its own autoloader registered with `$prepend = true` (see
 * Variation_Swatches), or require them explicitly, so this map stays unbuilt.
 *
 * Excluded from the scan: the vendored Freemius SDK (freemius/) and both Elementor
 * widget trees (includes/elementor/widgets/ and its Pro sibling), which the
 * Elementor loader discovers and gates through its own scanner.
 *
 * @package Themeasy\Core
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

spl_autoload_register(
  static function ( $class ) {
    if ( 0 !== strpos( $class, 'Themeasy\\' ) ) {
      return;
    }

    static $map = null;

    if ( null === $map ) {
      $map = themeasy_build_classmap( TMS_PATH );
    }

    if ( isset( $map[$class] ) && is_readable( $map[$class] ) ) {
      require_once $map[$class];
    }
  }
);

/**
 * Build a fully-qualified-class-name => absolute-file-path map by tokenizing the
 * plugin's PHP sources.
 *
 * @param string $base_path Plugin root path (TMS_PATH, trailing slash).
 * @return array<string, string>
 */
function themeasy_build_classmap( string $base_path ): array {
  $map = [];
  $dirs = [
    'core',
    'admin',
    'admin__premium_only',
    'includes/elementor',
    'includes/woocommerce__premium_only',
    'includes/contact-form-7__premium_only',
    'modules__premium_only',
  ];

  foreach ( $dirs as $dir ) {
    $root = $base_path . $dir;

    if ( !is_dir( $root ) ) {
      continue;
    }

    $iterator = new RecursiveIteratorIterator(
      new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ),
      RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ( $iterator as $file ) {
      if ( !$file->isFile() || 'php' !== strtolower( $file->getExtension() ) ) {
        continue;
      }

      // Both Elementor widget trees (Free and Pro) have their own scanner; never
      // autoload from them. The pattern covers any suffix on the tree root.
      if ( preg_match( '#/includes/elementor/widgets[^/]*/#', wp_normalize_path( $file->getPathname() ) ) ) {
        continue;
      }

      $fqcn = themeasy_extract_fqcn( $file->getPathname() );

      if ( '' !== $fqcn && !isset( $map[$fqcn] ) ) {
        $map[$fqcn] = $file->getPathname();
      }
    }
  }

  return $map;
}

/**
 * Extract the first declared type (class/interface/trait) as a fully-qualified
 * name from a PHP file, using the tokenizer so comments and `::class` references
 * are not mistaken for declarations.
 *
 * @param string $file Absolute file path.
 * @return string FQCN, or '' when the file declares no top-level type.
 */
function themeasy_extract_fqcn( string $file ): string {
  $code = file_get_contents( $file );

  if ( false === $code ) {
    return '';
  }

  $tokens = token_get_all( $code );
  $count = count( $tokens );
  $namespace = '';

  for ( $i = 0; $i < $count; $i++ ) {
    $token = $tokens[$i];

    if ( !is_array( $token ) ) {
      continue;
    }

    if ( T_NAMESPACE === $token[0] ) {
      $namespace = '';
      $ns_token_types = themeasy_namespace_token_types();

      for ( $j = $i + 1; $j < $count; $j++ ) {
        $part = $tokens[$j];

        if ( ';' === $part || '{' === $part ) {
          break;
        }

        if ( is_array( $part ) && in_array( $part[0], $ns_token_types, true ) ) {
          $namespace .= $part[1];
        }
      }

      continue;
    }

    if ( in_array( $token[0], [T_CLASS, T_INTERFACE, T_TRAIT], true ) ) {
      // The type name is the next non-whitespace, non-comment token; if it is not
      // a plain string (anonymous class, or a `::class` reference), skip it.
      for ( $j = $i + 1; $j < $count; $j++ ) {
        $next = $tokens[$j];

        if ( is_array( $next ) && in_array( $next[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true ) ) {
          continue;
        }

        if ( is_array( $next ) && T_STRING === $next[0] ) {
          return '' !== $namespace ? $namespace . '\\' . $next[1] : $next[1];
        }

        break;
      }
    }
  }

  return '';
}

/**
 * Token types that make up a namespace name across PHP versions.
 *
 * @return int[]
 */
function themeasy_namespace_token_types(): array {
  $types = [T_STRING, T_NS_SEPARATOR];

  // PHP 8.0+ emits namespaced names as single tokens.
  if ( defined( 'T_NAME_QUALIFIED' ) ) {
    $types[] = T_NAME_QUALIFIED;
  }

  if ( defined( 'T_NAME_FULLY_QUALIFIED' ) ) {
    $types[] = T_NAME_FULLY_QUALIFIED;
  }

  return $types;
}
