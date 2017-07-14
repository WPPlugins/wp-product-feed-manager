<?php
/**
 * WP Product Feed Manager Includes functions
 *
 * Functions for including the required functions
 *
 * @author 		Michel Jongbloed
 * @category 	Includes
 * @package 	Main
 * @version     1.2
 */

// Prevent direct access
if (!defined('ABSPATH')) exit;

/**
 * Includes all required classes
 * 
 * @since 1.0.0
 */
function include_classes() {
    if (WP_DEBUG) { require_once ( 'setup/class-prepare-taxonomy.php' ); }

	if (!class_exists('WPPFM_Schedules')) { require_once ( __DIR__ . '/application/class-scheduled-tasks.php' ); }

    if (!class_exists('WPPFM_Async_Request')) { require_once ( __DIR__ . '/application/class-async-request.php' ); }

    if (!class_exists('WPPFM_Background_Process')) { require_once ( __DIR__ . '/application/class-background-process.php' ); }

	if (!class_exists('WPPFM_Feed_Master_Class')) { require_once ( __DIR__ . '/application/class-feed-master.php' ); }

	if (!class_exists('WPPFM_Queries')) { require_once ( __DIR__ . '/data/class-queries.php' ); }

	if (!class_exists('WPPFM_File_Class')) { require_once ( __DIR__ . '/data/class-file.php' ); }

	if (!class_exists('WPPFM_Channel')) { require_once ( __DIR__ . '/data/class-channels.php' ); }

    if (!class_exists('WPPFM_Variations_Class')) { require_once ( __DIR__ . '/data/class-variations.php' ); }

	if (!class_exists('WPPFM_Data_Class')) { require_once ( __DIR__ . '/data/class-data.php' ); }

    if (!class_exists('WPPFM_Categories_Class')) { require_once ( __DIR__ . '/data/class-categories.php' ); }

    if (!class_exists('WPPFM_Feed_Support_Class')) { require_once ( __DIR__ . '/application/class-feed-support.php' ); }

    if (!class_exists('WPPFM_Feed_Processor_Class')) { require_once ( __DIR__ . '/application/class-feed-processor.php' ); }

    if (!class_exists('WPPFM_Feed_Value_Editors_Class')) { require_once ( __DIR__ . '/application/class-feed-value-editors.php' ); }
	
    if (!class_exists('WPPFM_Admin_Page')) { require_once ( __DIR__ . '/user-interface/class-admin-page.php' ); }

    if (!class_exists('WPPFM_Main_Admin_Page')) { require_once ( __DIR__ . '/user-interface/class-main-admin-page.php' ); }

    if (!class_exists('WPPFM_List_Table')) { require_once ( __DIR__ . '/user-interface/class-list-table.php' ); }

    if (!class_exists('WPPFM_Ajax_Calls')) { require_once ( __DIR__ . '/data/class-ajax-calls.php' ); }

    if (!class_exists('WPPFM_Add_Feed_Page')) { require_once ( __DIR__ . '/user-interface/class-add-feed-page.php' ); }

    if (!class_exists('WPPFM_Feed_Form')) { require_once ( __DIR__ . '/user-interface/class-feed-form.php' ); }

    if (!class_exists('WPPFM_Add_Options_Page')) { require_once ( __DIR__ . '/user-interface/class-add-options-page.php' ); }

    if (!class_exists('WPPFM_Options_Form')) { require_once ( __DIR__ . '/user-interface/class-options-form.php' ); }

    if (!class_exists('WPPFM_Feed_Form_Control')) { require_once ( __DIR__ . '/user-interface/class-feed-form-controls.php' ); }

    if (!class_exists('WPPFM_Register_Scripts')) { require_once ( __DIR__ . '/class-register-scripts.php' ); }

    if (!class_exists('WPPFM_Db_Management')) { require_once ( __DIR__ . '/data/class-db-management.php' ); }

    if (!class_exists('WPPFM_Database')) { require_once ( __DIR__ . '/setup/class-wp-db-management.php' ); }

    if (!class_exists('WPPFM_Ajax_Data_Class')) { require_once ( __DIR__ . '/data/class-ajax-data.php' ); }

    if (!class_exists('WPPFM_Ajax_File_Class')) { require_once ( __DIR__ . '/data/class-ajax-file.php' ); }

    if (!class_exists('WPPFM_Ajax_Cache_Class')) { require_once ( __DIR__ . '/data/class-ajax-cache.php' ); }

    if (!class_exists('WPPFM_FTP_Class')) { require_once ( __DIR__ . '/data/class-ftp.php' ); }

    if (!class_exists('WPPFM_Feed_Queries_Class')) { require_once ( __DIR__ . '/application/class-feed-queries.php' ); }

    if (!class_exists('WPPFM_Feed_Value_Editors_Class')) { require_once ( __DIR__ . '/application/class-feed-value-editors.php' ); }

    if (!class_exists('WPPFM_Feed_Object')) { require_once ( __DIR__ . '/application/class-feed-object.php' ); }

    if (!class_exists('WPPFM_Feed_Processor_Class')) { require_once ( __DIR__ . '/application/class-feed-processor.php' ); }

    if (!class_exists('WPPFM_Folders_Class')) { require_once ( __DIR__ . '/setup/class-folders.php' ); }
}

/**
 * Includes all required channel classes
 * 
 * @since 1.0.0
 * 
 * @global type $wpdb
 */
function include_channels() {
	if ( !class_exists( 'WPPFM_Google_Feed_Class' ) ) {
		require_once ( __DIR__ . '/application/google/class-feed.php' );
	}
}