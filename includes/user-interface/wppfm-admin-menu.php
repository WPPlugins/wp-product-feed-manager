<?php
/**
 * WP Product Feed Manager Admin Menu functions
 *
 * Functions for handling admin pages
 *
 * @since 1.0.0
 * 
 * @author 		Michel Jongbloed
 * @category 	Menus
 * @package 	User-interface
 * @version     1.2
 */

// Prevent direct access
if (!defined('ABSPATH')) exit;

/**
 * Add the feed manager menu in the Admin page
 */
function wppfm_add_feed_manager_menu( $channel_updated = false ) {

	// defines the feed manager menu
	add_menu_page(
		__( 'WP Feed Manager', 'wp-product-feed-manager' ), 
		__( 'Feed Manager', 'wp-product-feed-manager' ), 
		'manage_options', 'wp-product-feed-manager', 
		'wppfm_main_admin_page', 
		esc_url( MYPLUGIN_PLUGIN_URL . '/images/app-rss-plus-xml-icon.png' ) 
	);

	add_submenu_page(
		'wp-product-feed-manager',
		__( 'Add Feed', 'wp-product-feed-manager' ), 
		__('Add Feed', 'wp-product-feed-manager' ), 
		'manage_options', 
		'wp-product-feed-manager-add-new-feed', 
		'wppfm_add_feed_page' 
	);

	// add the settings 
	add_submenu_page(
	'wp-product-feed-manager', __( 'Settings', 'wp-product-feed-manager' ),  __( 'Settings', 'wp-product-feed-manager' ), 'manage_options', 'wppfm-options-page', 'wppfm_options_page' );

	if( !wppfm_check_backup_status() ) {
		echo wppfm_show_wp_warning( __( "Due to the latest update your Feed Manager backups are no longer valid! 
			Please open the Feed Manager Settings page, remove all your current backups and make a new one.", 'wp-product-feed-manager' ), true );
	}
	
// Obsolete 170217
//	add_options_page(
//	__( 'WP Feed Manage Options', 'wp-product-feed-manager' ), __( 'Feed Manager', 'wp-product-feed-manager' ), 'manage_options', 'wppfm_options_page', 'wppfm_options_page' );
}

add_action( 'admin_menu', 'wppfm_add_feed_manager_menu' );

/**
 * starts the main admin page
 */
function wppfm_main_admin_page() {
	$start = new WPPFM_Main_Admin_Page ();
	// now let's get things going
	$start->show();
}

function wppfm_add_feed_page() {
	$add_new_feed_page = new WPPFM_Add_Feed_Page ();
	$add_new_feed_page->show();
}

/**
 * options page
 */
function wppfm_options_page() {
	$add_options_page = new WPPFM_Add_Options_Page ();
	$add_options_page->show();
}