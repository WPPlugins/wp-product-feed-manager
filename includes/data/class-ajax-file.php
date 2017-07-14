<?php

/* * ******************************************************************
 * Version 2.1
 * Modified: 25-05-2017
 * Copyright 2017 Accentio. All rights reserved.
 * License: None
 * By: Michel Jongbloed
 * ****************************************************************** */

// Prevent direct access
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WPPFM_Ajax_File_Class' ) ) :

	/**
	 * The WPPFM_Ajax_File_Class contains all functions for file manipulation ajax calls
	 * 
	 * @class		WPPFM_Ajax_File_Class
	 * @version		2.1
	 * @category	Class
	 * @author		Michel Jongbloed
	 */
	class WPPFM_Ajax_File_Class extends WPPFM_Ajax_Calls {

		/**
		 * Class constructor
		 */
		public function __construct() {

			parent::__construct();

			// hooks
			add_action( 'wp_ajax_myajax-get-next-categories', array( $this, 'myajax_read_next_categories' ) );
			add_action( 'wp_ajax_myajax-get-category-lists', array( $this, 'myajax_read_category_lists' ) );
			add_action( 'wp_ajax_myajax-delete-feed-file', array( $this, 'myajax_delete_feed_file' ) );
			add_action( 'wp_ajax_myajax-update-feed-file', array( $this, 'myajax_update_feed_file' ) );
			add_action( 'wp_ajax_myajax-log-message', array( $this, 'myajax_log_message' ) );
			add_action( 'wp_ajax_myajax-update-ftp-mode-selection', array( $this, 'myajax_update_ftp_mode_selection' ) );
			add_action( 'wp_ajax_myajax-auto-feed-fix-mode-selection', array( $this, 'myajax_auto_feed_fix_mode_selection' ) );
			add_action( 'wp_ajax_myajax-third-party-attribute-keywords', array( $this, 'myajax_set_third_party_attribute_keywords' ) );
		}

		/* --------------------------------------------------------------------------------------------------*
		 * Public functions
		 * -------------------------------------------------------------------------------------------------- */

		/**
		 * Returns the sub-categories from a selected category
		 */
		public function myajax_read_next_categories() {

			// make sure this call is legal
			if ( $this->safe_ajax_call( filter_input( INPUT_POST, 'nextCategoryNonce' ), 'myajax-next-category-nonce' ) ) {
				$file_class = new WPPFM_File_Class();

				$channel_id		 = filter_input( INPUT_POST, 'channelId' );
				$requested_level = filter_input( INPUT_POST, 'requestedLevel' );
				$parent_category = filter_input( INPUT_POST, 'parentCategory' );
				$file_language	 = filter_input( INPUT_POST, 'fileLanguage' );
				$categories = $file_class->get_categories_for_list( $channel_id, $requested_level, $parent_category, $file_language );

				if ( !is_array( $categories ) ) {
					if ( substr( $categories, -1 ) === "0" ) {
						chop( $categories, '0' );
					}
				}

				echo json_encode( $categories );
			}

			// IMPORTANT: don't forget to exit
			exit;
		}

		/**
		 * 
		 */
		public function myajax_read_category_lists() {

			// make sure this call is legal
			if ( $this->safe_ajax_call( filter_input( INPUT_POST, 'categoryListsNonce' ), 'myajax-category-lists-nonce' ) ) {
				$file_class = new WPPFM_File_Class();

				$channel_id				 = filter_input( INPUT_POST, 'channelId' );
				$main_categories_string	 = filter_input( INPUT_POST, 'mainCategories' );
				$file_language			 = filter_input( INPUT_POST, 'fileLanguage' );
				$categories_array = explode( '>', $main_categories_string );
				$categories = array();

				for ( $i = 0; $i < count( $categories_array ); $i ++ ) {
					$parent_category = $i > 0 ? $categories_array[ $i - 1 ] : '';
					$c = $file_class->get_categories_for_list( $channel_id, $i, $parent_category, $file_language );
					array_push( $categories, $c );
				}

				echo json_encode( $categories );
			}

			// IMPORTANT: don't forget to exit
			exit;
		}

		/**
		 * Delete a specific feed file
		 */
		public function myajax_delete_feed_file() {

			// make sure this call is legal
			if ( $this->safe_ajax_call( filter_input( INPUT_POST, 'deleteFeedNonce' ), 'myajax-delete-feed-nonce' ) ) {
				$file_name = filter_input( INPUT_POST, 'fileTitle' );
				
				if ( file_exists( WP_PLUGIN_DIR . '/wp-product-feed-manager-support/feeds/' . $file_name ) ) {
					$file = WP_PLUGIN_DIR . '/wp-product-feed-manager-support/feeds/' . $file_name;
				} else {
					$file = WPPFM_FEEDS_DIR . '/' . $file_name;
				}

				// only return results when the user is an admin with manage options
				if ( is_admin() ) {
					echo file_exists( $file ) ? unlink( $file ) : wppfm_show_wp_error( __( "Could not find file $file.", 'wp-product-feed-manager' ) );
				} else {
					echo wppfm_show_wp_error( __( 'Error deleting the feed. You do not have the correct authorities to delete the file.', 'wp-product-feed-manager' ) );
				}
			}

			// IMPORTANT: don't forget to exit
			exit;
		}

		/**
		 * This function fetches the posted data and triggers the update of the feed file on the server.
		 */
		public function myajax_update_feed_file() {

			// make sure this call is legal
			if ( $this->safe_ajax_call( filter_input( INPUT_POST, 'updateFeedFileNonce' ), 'myajax-update-feed-file-nonce' ) ) {
				$feed_master_class = new WPPFM_Feed_Master_Class();

				// fetch the data from $_POST
				$feed_received	 = filter_input( INPUT_POST, 'feedData' );
				$feed_data		 = json_decode( $feed_received );

				// only return results when the user is an admin with manage options
				if ( is_admin() ) {
					$feed_master_class->update_feed_file( $feed_data, false );
				} else {
					echo wppfm_show_wp_error( __( 'Error writing the feed. You do not have the correct authorities to write the file.', 'wp-product-feed-manager' ) );
				}
			}

			// IMPORTANT: don't forget to exit
			exit;
		}

		/**
		 * Logs a message from a javascript call to the server
		 */
		public function myajax_log_message() {

			// make sure this call is legal
			if ( $this->safe_ajax_call( filter_input( INPUT_POST, 'logMessageNonce' ), 'myajax-log-message-nonce' ) ) {
				// fetch the data from $_POST
				$message		 = filter_input( INPUT_POST, 'messageList' );
				$fileName		 = filter_input( INPUT_POST, 'fileName' );
				$text_message	 = strip_tags( $message );

				// only return results when the user is an admin with manage options
				if ( is_admin() ) {
					echo wppfm_write_log_file( $text_message, $fileName );
				} else {
					echo wppfm_show_wp_error( __( 'Error writing the feed. You do not have the correct authorities to write the file.', 'wp-product-feed-manager' ) );
				}
			}

			// IMPORTANT: don't forget to exit
			exit;
		}

		/**
		 * Changes the FTP Passive Mode setting from the Settings page
		 * 
		 * @since 1.7.0
		 */
		public function myajax_update_ftp_mode_selection() {
			
			// make sure this call is legal
			if ( $this->safe_ajax_call( filter_input( INPUT_POST, 'updateFeedDataNonce' ), 'myajax-ftp-mode-nonce' ) ) {
				$selection = filter_input( INPUT_POST, 'ftp_selection' );
				update_option( 'wppfm_ftp_passive', $selection );
				
				echo get_option( 'wppfm_ftp_passive' );
			}

			// IMPORTANT: don't forget to exit
			exit;
		}

		/**
		 * Changes the Auto Feed Fix setting from the Settings page
		 * 
		 * @since 1.7.0
		 */
		public function myajax_auto_feed_fix_mode_selection() {
			
			// make sure this call is legal
			if ( $this->safe_ajax_call( filter_input( INPUT_POST, 'updateAutoFeedFixNonce' ), 'myajax-auto-feed-fix-nonce' ) ) {
				$selection = filter_input( INPUT_POST, 'fix_selection' );
				update_option( 'wppfm_auto_feed_fix', $selection );
				
				echo get_option( 'wppfm_auto_feed_fix' );
			}

			// IMPORTANT: don't forget to exit
			exit;
		}
		
		/**
		 * Stores the third party attribute keywords
		 * 
		 * @since 1.8.0
		 */
		public function myajax_set_third_party_attribute_keywords() {
			
			// make sure this call is legal
			if ( $this->safe_ajax_call( filter_input( INPUT_POST, 'thirdPartyKeywordsNonce' ), 'myajax-set-third-party-keywords-nonce' ) ) {
				$keywords = filter_input( INPUT_POST, 'keywords' );
				update_option( 'wppfm_third_party_attribute_keywords', $keywords );
				
				echo get_option( 'wppfm_third_party_attribute_keywords' );
			}

			// IMPORTANT: don't forget to exit
			exit;
		}

	}

	// End of WPPFM_Ajax_File_Class

endif;

$myajaxfileclass = new WPPFM_Ajax_File_Class();