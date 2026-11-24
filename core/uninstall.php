<?php
/**
 * Uninstall cleanup: what the plugin stored to run, removed when it is deleted.
 *
 * The SDK registers the WordPress uninstall hook when the plugin is deactivated
 * and fires its after_uninstall action from it, unless the other edition of the
 * pair is still active; an uninstall.php file would make WordPress skip that
 * hook. core/licensing.php hooks themeasy_uninstall_cleanup() there.
 *
 * Only the plumbing goes (owner decision, backlog #475): caches, locks, queues,
 * scheduled events and generated files. The settings and the content stay, so a
 * reinstall finds the site as it was.
 *
 * Not namespaced, like core/licensing.php: the SDK calls it by name.
 *
 * @package Themeasy\Core
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Remove the plugin's runtime state from every site of the install.
 *
 * @return void
 */
function themeasy_uninstall_cleanup(): void {
  if ( !is_multisite() ) {
    themeasy_uninstall_site();

    return;
  }

  foreach ( get_sites( ['fields' => 'ids', 'number' => 0] ) as $site_id ) {
    switch_to_blog( (int) $site_id );
    themeasy_uninstall_site();
    restore_current_blog();
  }
}

/**
 * Remove the plugin's runtime state from the current site.
 *
 * @return void
 */
function themeasy_uninstall_site(): void {
  global $wpdb;

  // Every transient of the plugin is a cache, a lock or a one-time notice, and
  // some carry a version or a hash in their name: by prefix, as
  // themeasy_flush_editor_svg_library_cache() does. A persistent object cache
  // keeps them out of wp_options, and there they expire on their own.
  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
  $wpdb->query(
    $wpdb->prepare(
      "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
      $wpdb->esc_like( '_transient_themeasy_' ) . '%',
      $wpdb->esc_like( '_transient_timeout_themeasy_' ) . '%'
    )
  );

  /**
   * Fires on each site when the plugin is deleted, after its transients are
   * gone: the place for the rest of what the plugin stored to run.
   */
  do_action( 'themeasy/uninstall' );
}
