<?php

/* * ******************************************************************
 * Version 1.3
 * Modified: 05-06-2017
 * Copyright 2017 Accentio. All rights reserved.
 * License: None
 * By: Michel Jongbloed
 * ****************************************************************** */

// Prevent direct access
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WPPFM_Ajax_Data_Class' ) ) :

	/**
	 * The WPPFM_Ajax_Main_Data contains all functions for database manipulation ajax calls
	 * 
	 * @class WPPFM_Ajax_Data_Class
	 * @version dev
	 */
	class WPPFM_Ajax_Data_Class extends WPPFM_Ajax_Calls {

		/**
		 * Class constructor
		 */
		public function __construct() {

			parent::__construct();

			$this->_queries	 = new WPPFM_Queries ();
			$this->_files	 = new WPPFM_File_Class();

			// hooks
			add_action( 'wp_ajax_myajax-get-list-of-feeds', array( $this, 'myajax_get_list_of_feeds' ) );
			add_action( 'wp_ajax_myajax-get-list-of-backups', array( $this, 'myajax_get_list_of_backups' ) );
			add_action( 'wp_ajax_myajax-get-settings-options', array( $this, 'myajax_get_settings_options' ) );
			add_action( 'wp_ajax_myajax-get-output-fields', array( $this, 'myajax_get_output_fields' ) );
			add_action( 'wp_ajax_myajax-get-input-fields', array( $this, 'myajax_get_input_fields' ) );
			add_action( 'wp_ajax_myajax-get-feed-data', array( $this, 'myajax_get_feed_data' ) );
			add_action( 'wp_ajax_myajax-get-main-feed-filters', array( $this, 'myajax_get_feed_filters' ) );
			add_action( 'wp_ajax_myajax-switch-feed-status', array( $this, 'myajax_switch_feed_status_between_hold_and_ok' ) );
			add_action( 'wp_ajax_myajax-duplicate-existing-feed', array( $this, 'myajax_duplicate_feed_data' ) );
			add_action( 'wp_ajax_myajax-update-feed-data', array( $this, 'myajax_update_feed_data' ) );
			add_action( 'wp_ajax_myajax-delete-feed', array( $this, 'myajax_delete_feed' ) );
			add_action( 'wp_ajax_myajax-backup-current-data', array( $this, 'myajax_backup_current_data' ) );
			add_action( 'wp_ajax_myajax-delete-backup-file', array( $this, 'myajax_delete_backup_file' ) );
			add_action( 'wp_ajax_myajax-restore-backup-file', array( $this, 'myajax_restore_backup_file' ) );
			add_action( 'wp_ajax_myajax-duplicate-backup-file', array( $this, 'myajax_duplicate_backup_file' ) );
		}

		/**
		 * Returns a list of all active feeds to an ajax caller
		 */
		public function myajax_get_list_of_feeds() {

			if ( $this->safe_ajax_call( filter_input( INPUT_POST, 'postFeedsListNonce' ), 'myajax-post-feeds-list-nonce' ) ) {
				echo json_encode( $this->_queries->make_list_of_active_feeds() );
			}

			// IMPORTANT: don't forget to exit
			exit;
		}
		
		/**
		 * Returns a list of backups the user has made
		 */
		public function myajax_get_list_of_backups() {

			if ( $this->safe_ajax_call( filter_input( INPUT_POST, 'postBackupListNonce' ), 'myajax-backups-list-nonce' ) ) {

				echo json_encode( $this->_files->make_list_of_active_backups() );
			}

			// IMPORTANT: don't forget to exit
			exit;
		}
		
		public function myajax_get_settings_options() {

			if ( $this->safe_ajax_call( filter_input( INPUT_POST, 'postSetupOptionsNonce' ), 'myajax-setting-options-nonce' ) ) {
				$options = [ get_option( 'wppfm_ftp_passive' ), get_option( 'wppfm_auto_feed_fix' ) ];
				echo json_encode( $options );
			}

			// IMPORTANT: don't forget to exit
			exit;
		}

		/**
		 * Retrieves the output fields that are specific for a given merchant and
		 * also adds stored meta data to the output fields
		 * 
		 * @access public (ajax triggered)
		 * @return json encoded object with output fields
		 */
		public function myajax_get_output_fields() {

			// check if the call is safe
			if ( $this->safe_ajax_call( filter_input( INPUT_POST, 'outputFieldsNonce' ), 'myajax-output-fields-nonce' ) ) {

				// get the posted inputs
				$channel_id	 = filter_input( INPUT_POST, 'channelId' );
				$feed_id	 = filter_input( INPUT_POST, 'feedId' );
				$channel = $this->_queries->get_channel_short_name_from_db( $channel_id );
				$is_custom = function_exists( 'channel_is_custom_channel' ) ? channel_is_custom_channel( $channel_id ) : false;
				
				if ( ! $is_custom ) {
					
					// read the output fields
					$outputs = $this->_files->get_output_fields_for_specific_channel( $channel );

					// if the feed is a stored feed, look for meta data to add (a feed an id of -1 is a new feed that not yet has been saved)
					if ( $feed_id >= 0 ) {

						// add meta data to the feeds output fields
						$this->data_class	 = new WPPFM_Data_Class();
						$outputs			 = $this->data_class->fill_output_fields_with_metadata( $feed_id, $outputs );
					}
				} else {

					$this->data_class	 = new WPPFM_Data_Class();
					$outputs			 = $this->data_class->get_custom_fields_with_metadata( $feed_id );
				}

				echo json_encode( $outputs );
			}

			// IMPORTANT: don't forget to exit
			exit;
		}

		public function myajax_get_input_fields() {

			if ( $this->safe_ajax_call( filter_input( INPUT_POST, 'inputFieldsNonce' ), 'myajax-input-fields-nonce' ) ) {

				$source_id = filter_input( INPUT_POST, 'sourceId' );

				switch ( $source_id ) {

					case '1':
						$data_class = new WPPFM_Data_Class();
						
						$custom_product_attributes = $this->_queries->get_custom_product_attributes();
						$custom_product_fields = $this->_queries->get_custom_product_fields();
						$product_attributes = $this->_queries->get_all_product_attributes();
						$third_party_custom_fields = $data_class->get_third_party_custom_fields();
						
						echo json_encode( $this->combine_custom_attributes_and_feeds( $custom_product_attributes, 
							$custom_product_fields, $product_attributes, $third_party_custom_fields ) );
						break;

					default:
	
						$status = get_option( 'wppfm_lic_status' );

						if ( $status === 'valid' ) { // error message for paid versions
							
							echo "<div id='error'>Could not add custom fields because I could not define the channel. If not already done add the correct channel in the Manage Channels page. Also try to deactivate and then activate the plugin.</div>";
							
							$error_message = "Could not define the channel in a valid Premium plugin version. Feed id = $source_id";
							wppfm_write_log_file($error_message);
						} else { // error message for free version
							
							echo "<div id='error'>Could not define the channel. Try to deactivate and then activate the plugin. If that doesn't work remove the plugin through the Wordpress Plugins page and than reinstall and activate it again.</div>";

							$error_message = "Could not define the channel in a free plugin version. Feed id = $source_id";
							wppfm_write_log_file($error_message);
						}

						break;
				}
			}

			// IMPORTANT: don't forget to exit
			exit;
		}
		
		public function myajax_get_feed_filters() {
			
			if ( $this->safe_ajax_call( filter_input( INPUT_POST, 'inputFeedFiltersNonce' ), 'myajax-feed-filters-nonce' ) ) {

				$feed_id = filter_input( INPUT_POST, 'feedId' );

				$data_class = new WPPFM_Data_Class();
				$filters = $data_class->get_filter_query( $feed_id );
				
				echo $filters ? json_encode( $filters ) : '0';
			}

			// IMPORTANT: don't forget to exit
			exit;
		}

		public function myajax_get_feed_data() {

			if ( $this->safe_ajax_call( filter_input( INPUT_POST, 'feedDataNonce' ), 'myajax-feed-data-nonce' ) ) {

				$feed_id = filter_input( INPUT_POST, 'sourceId' );

				$feed_data = $this->_queries->read_feed( $feed_id );

				echo json_encode( $feed_data );
			}

			// IMPORTANT: don't forget to exit
			exit;
		}

		public function myajax_update_feed_data() {

			if ( $this->safe_ajax_call( filter_input( INPUT_POST, 'updateFeedDataNonce' ), 'myajax-update-feed-data-nonce' ) ) {

				$data_class = new WPPFM_Data_Class();

				// get the posted feed data
				$feed_id			= filter_input( INPUT_POST, 'feedId' );
				$channel_id			= filter_input( INPUT_POST, 'channelId' );
				$is_aggregator		= filter_input( INPUT_POST, 'isAggregator' );
				$incl_variations	= filter_input( INPUT_POST, 'includeVariations' );
				$country_code		= filter_input( INPUT_POST, 'countryId' );
				$source_id			= filter_input( INPUT_POST, 'sourceId' );
				$title				= filter_input( INPUT_POST, 'title' );
				$feed_title			= filter_input( INPUT_POST, 'feedTitle' );			// @since 1.8.0
				$feed_description	= filter_input( INPUT_POST, 'feedDescription' );	// @since 1.8.0
				$main_category		= filter_input( INPUT_POST, 'defaultCategory' );
				$url				= filter_input( INPUT_POST, 'url' );
				$status				= filter_input( INPUT_POST, 'status' );
				$feed_filter		= filter_input( INPUT_POST, 'feedFilter' );
				$schedule			= filter_input( INPUT_POST, 'schedule' );
				$m_data				= filter_input( INPUT_POST, 'metaData' );

				$country_id = $data_class->get_country_id_from_short_code( $country_code )->country_id;

				//$status_id	 = $data_class->get_status_id_from_status( $status )->status_id;
				// get the posted meta data
				$meta_data = json_decode( $m_data );

				// insert or update the feed
				if ( $feed_id < 0 ) {
					$resulting_feed_id	 = $this->_queries->insert_feed( $channel_id, $country_id, $source_id, $title, $feed_title, $feed_description, $main_category, $incl_variations, $is_aggregator, $url, $status, $schedule );
					$response			 = $resulting_feed_id;
				} else {
					$update_result	 = $this->_queries->update_feed( $feed_id, $channel_id, $country_id, $source_id, $title, $feed_title, $feed_description, $main_category, $incl_variations, $is_aggregator, $url, $status, $schedule );
					$response		 = $update_result ? $feed_id : 0;
				}

				$actual_feed_id = $feed_id < 0 ? $resulting_feed_id : $feed_id;

				if ( count( $meta_data ) > 0 ) {
					$this->_queries->update_meta_data( $actual_feed_id, $meta_data );
				}
				
				$this->_queries->store_feed_filter( $actual_feed_id, $feed_filter );

				echo $response;
			}

			exit;
		}

		public function myajax_switch_feed_status_between_hold_and_ok() {

			if ( $this->safe_ajax_call( filter_input( INPUT_POST, 'switchFeedStatusNonce' ), 'myajax-switch-feed-status-nonce' ) ) {

				$feed_id = filter_input( INPUT_POST, 'feedId' );

				$feed_status = $this->_queries->get_current_feed_status( $feed_id );
				$current_status = $feed_status[ 0 ]->status_id;

				$new_status = $current_status === "1" ? 2 : 1;

				$result = $this->_queries->update_current_feed_status( $feed_id, $new_status );

				echo (false === $result) ? $current_status : $new_status;
			}

			// IMPORTANT: don't forget to exit
			exit;
		}
		
		public function myajax_duplicate_feed_data() {

			if ( $this->safe_ajax_call( filter_input( INPUT_POST, 'duplicateFeedNonce' ), 'myajax-duplicate-existing-feed-nonce' ) ) {

				$feed_id = filter_input( INPUT_POST, 'feedId' );
				
				echo WPPFM_Db_Management::duplicate_feed( $feed_id );
			}

			// IMPORTANT: don't forget to exit
			exit;
		}

		public function myajax_delete_feed() {

			if ( $this->safe_ajax_call( filter_input( INPUT_POST, 'deleteFeedNonce' ), 'myajax-delete-feed-nonce' ) ) {

				$feed_id = filter_input( INPUT_POST, 'feedId' );

				// only return results when the user is an admin with manage options
				if ( is_admin() ) {

					$this->_queries->delete_meta( $feed_id );

					echo $this->_queries->delete_feed( $feed_id );
				}
			}

			// IMPORTANT: don't forget to exit
			exit;
		}
		
		public function myajax_backup_current_data() {
			
			if ( $this->safe_ajax_call( filter_input( INPUT_POST, 'backupNonce' ), 'myajax-backup-nonce' ) ) {

				// only take action when the user is an admin with manage options
				if ( is_admin() ) {

					$backup_file_name = filter_input( INPUT_POST, 'fileName' );
	
					echo WPPFM_Db_Management::backup_database_tables( $backup_file_name );
				}
			}
			
			// IMPORTANT: don't forget to exit
			exit;
		}
		
		public function myajax_delete_backup_file() {
			
			if ( $this->safe_ajax_call( filter_input( INPUT_POST, 'deleteBackupNonce' ), 'myajax-delete-backup-nonce' ) ) {

				// only take action when the user is an admin with manage options
				if ( is_admin() ) {

					$backup_file_name = filter_input( INPUT_POST, 'fileName' );
	
					echo WPPFM_Db_Management::delete_backup_file( $backup_file_name );
				}
			}
			
			// IMPORTANT: don't forget to exit
			exit;
		}
		
		public function myajax_restore_backup_file() {
			
			if ( $this->safe_ajax_call( filter_input( INPUT_POST, 'restoreBackupNonce' ), 'myajax-restore-backup-nonce' ) ) {

				// only take action when the user is an admin with manage options
				if ( is_admin() ) {
					$backup_file_name = filter_input( INPUT_POST, 'fileName' );
					echo WPPFM_Db_Management::restore_backup( $backup_file_name );
				}
			}
			
			// IMPORTANT: don't forget to exit
			exit;
		}
		
		public function myajax_duplicate_backup_file() {
			
			if ( $this->safe_ajax_call( filter_input( INPUT_POST, 'duplicateBackupNonce' ), 'myajax-duplicate-backup-nonce' ) ) {

				// only take action when the user is an admin with manage options
				if ( is_admin() ) {

					$backup_file_name = filter_input( INPUT_POST, 'fileName' );
	
					echo WPPFM_Db_Management::duplicate_backup_file( $backup_file_name );
				}
			}
			
			// IMPORTANT: don't forget to exit
			exit;
		}

		private function combine_custom_attributes_and_feeds( $attributes, $feeds, $product_attributes, $third_party_fields ) {
			$keywords = explode( ', ', get_option( 'wppfm_third_party_attribute_keywords', '_wpmr_%, _cpf_%, _unit%' ) );
			$prev_dup_array = array(); // used to prevent doubles
			
			foreach ( $feeds as $feed ) {
				$clean_feed = $feed;
				$obj = new stdClass();
				
				foreach( $keywords as $keyword ) {
					$clean_feed = str_replace( trim( $keyword, '%' ), '', $clean_feed );
				}
				
				$obj->attribute_name = $feed;
				$obj->attribute_label = $clean_feed;
				
				array_push( $attributes, $obj );
				array_push( $prev_dup_array, $obj->attribute_label );
			}

			foreach ($product_attributes as $attribute_string){
				$attribute_object = maybe_unserialize( $attribute_string->meta_value );

				if( $attribute_object && ( is_object( $attribute_object ) || is_array( $attribute_object ) ) ) {

					foreach ( $attribute_object as $attribute ) {
						if ( !in_array( $attribute['name'], $prev_dup_array ) ) {
							$obj = new stdClass();
							$obj->attribute_name = $attribute['name'];
							$obj->attribute_label = $attribute['name'];

							array_push( $attributes, $obj );
							array_push( $prev_dup_array, $attribute['name'] );
						}
					}
				} else {
					if ( $attribute_object ) { wppfm_write_log_file( $attribute_object , 'debug' ); }
				}
			}
			
			foreach ( $third_party_fields as $field_label ) {

				if ( !in_array( $field_label, $prev_dup_array ) ) {
					$obj = new stdClass();
					$obj->attribute_name = $field_label;
					$obj->attribute_label = $field_label;

					array_push( $attributes, $obj );
					array_push( $prev_dup_array, $field_label );
				}
			}
			
			return $attributes;
		}
	}

	// end of WPPFM_Ajax_Data_Class

endif;

$myajaxdataclass = new WPPFM_Ajax_Data_Class();
