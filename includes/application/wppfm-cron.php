<?php
/**
 * WP Product Feed Manager Cron functions
 *
 * Functions for handling cron requests
 *
 * @author 		Michel Jongbloed
 * @category 	Cron
 * @package 	Application
 * @version     2.0
 */

// Prevent direct access
if (!defined('ABSPATH')) exit;

/**
 * Activates the feed update schedules
 * 
 * @param none
 * @return nothing
 */
function wppfm_update_feeds() {
	// include the required wordpress files
	require_once ( ABSPATH . 'wp-load.php' );
	require_once ( ABSPATH . 'wp-admin/includes/admin.php' );
	require_once ( ABSPATH . 'wp-admin/includes/file.php' ); // required for using the file system
	
	// include all product feed manager files
	require_once ( __DIR__ . '/../wppfm-wpincludes.php' );
	require_once ( __DIR__ . '/../data/wppfm-admin-functions.php' );
	require_once ( __DIR__ . '/../user-interface/wppfm-messaging.php' );	

	// WooCommerce needs to be installed and active
	if ( !is_plugin_active( 'woocommerce/woocommerce.php' ) && !is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) {
		wppfm_write_log_file( 'Tried to start the auto update process but failed because WooCommerce is not installed.' );
		exit;
	}

	WC_Post_types::register_taxonomies(); // make sure the woocommerce taxonomies are loaded
	WC_Post_types::register_post_types(); // make sure the woocommerce post types are loaded

	// include all required classes
	include_classes();
	include_channels();

	// update the database if required
	$db_management = new WPPFM_Database();
	$db_management->verify_db_version();
	
	// start updating the active feeds
	$wppfm_schedules = new WPPFM_Schedules();
	$wppfm_schedules->update_active_feeds( true );
}