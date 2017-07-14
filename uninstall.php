<?php

/* * ******************************************************************
 * Version 3.2
 * Modified: 25-05-2017
 * Copyright 2017 Accentio. All rights reserved.
 * License: None
 * By: Michel Jongbloed
 * 
 * Runs on uninstall command from wp_product_feed_manager plugin
 * ****************************************************************** */

// Prevent direct access
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

/**
 * @var link to global wordpress database functions
 */
global $wpdb;

$upload_dir = wp_upload_dir();

if ( !class_exists( 'WPPFM_Folders_Class' ) ) {
	require_once ( __DIR__ . '/includes/setup/class-folders.php' );
}

// stop the scheduled feed update actions
wp_clear_scheduled_hook( 'wppfm_feed_update_schedule' );

// remove the support folders
WPPFM_Folders_Class::delete_folder( WP_PLUGIN_DIR . '/wp-product-feed-manager-support/feeds' );
WPPFM_Folders_Class::delete_folder( WP_PLUGIN_DIR . '/wp-product-feed-manager-support' );
WPPFM_Folders_Class::delete_folder( $upload_dir['basedir'] . '/wppfm-channels' );
WPPFM_Folders_Class::delete_folder( $upload_dir['basedir'] . '/wppfm-feeds' );

$tables = array( $wpdb->prefix . 'feedmanager_country', $wpdb->prefix . 'feedmanager_feed_status',
	$wpdb->prefix . 'feedmanager_field_categories',	$wpdb->prefix . 'feedmanager_channel', 
	$wpdb->prefix . 'feedmanager_product_feed', $wpdb->prefix . 'feedmanager_product_feedmeta',
	$wpdb->prefix . 'feedmanager_source', $wpdb->prefix . 'feedmanager_errors' );

// remove the feedmanager tables
foreach ( $tables as $table ) {
	$wpdb->query( "DROP TABLE IF EXISTS $table" );
}

// unregister the plugin
unregister_plugin();

/**
 * Deletes a file
 * 
 * @param string $filename
 * @param string $dirname
 * @return nothing
 */
function remove_file_from_directory( $file, $dirname ) {
	if ( !is_dir( $dirname . "/" . $file )) {
		unlink( $dirname . "/" . $file );
	} else {
		WPPFM_Folders_Class::delete_folder( $dirname . '/' . $file );
	}
}

/**
 * Removes the registration info from the database
 * 
 * @return nothing
 */
function unregister_plugin() {
	// retrieve the license from the database
	$license = get_option( 'wppfm_lic_key' );
	
	delete_option( 'wppfm_db_version' );
	delete_option( 'wppfm_lic_status' );
	delete_option( 'wppfm_lic_status_date' );
	delete_option( 'wppfm_lic_key' );
	delete_option( 'wppfm_channel_update_check_date' );
	delete_option( 'wppfm_channels_to_update' );
	delete_option( 'wppfm_ftp_passive' );
	delete_option( 'wppfm_auto_feed_fix' );
	delete_option( 'wppfm_third_party_attribute_keywords' );

	if ( $license ) { // if the plugin is a licensed version then deactivate it on the license server
		// data to send in our API request
		$api_params = array( 
			'edd_action'=> 'deactivate_license', 
			'license'   => $license, 
			'item_name' => urlencode( 'Woocommerce Google Feed Manager' ), // the name of our product in EDD
			'url'       => home_url()
		);

		// Call the custom API.
		wp_remote_post( 'http://www.wpmarketingrobot.com/', array(
			'timeout'   => 15,
			'sslverify' => false,
			'body'      => $api_params
		) );
	}
}