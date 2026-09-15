<?php
/**
 * Uninstall handler.
 *
 * Removes the per-user preference and the migration flag. Deactivating the
 * plugin leaves both alone — only deleting it clears them.
 *
 * @package Dark_Mode_For_WP_Dashboard
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die();
}

delete_metadata( 'user', 0, 'dark_mode_preference', '', true );
delete_metadata( 'user', 0, 'dark_mode_dashboard', '', true );

delete_option( 'dark_mode_migration_done' );
delete_site_option( 'dark_mode_migration_done' );
