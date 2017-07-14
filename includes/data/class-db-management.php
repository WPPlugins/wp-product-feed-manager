<?php

/* * ******************************************************************
 * Version 1.1
 * Modified: 09-06-2017
 * Copyright 2017 Accentio. All rights reserved.
 * License: None
 * By: Michel Jongbloed
 * ****************************************************************** */

// Prevent direct access
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WPPFM_Db_Management' ) ) :

	/**
	 * The WPPFM_Db_Management Class contains several static database management functions
	 * 
	 * @class WPPFM_Db_Management
	 * @version 1.1
	 */
	class WPPFM_Db_Management {

		public static function table_exists( $table_name ) {

			global $wpdb;

			if ( $wpdb->get_var( "SHOW TABLES LIKE '" . $table_name . "'" ) === $table_name ) {
				return true;
			} else {
				return false;
			}
		}
		
		/**
		 * makes a copy of a selected feed
		 * 
		 * @param string $feed_id
		 * @return boolean
		 */
		public static function duplicate_feed( $feed_id ) {

			$queries_class = new WPPFM_Queries();
			$support_class = new WPPFM_Feed_Support_Class();
			
			// get the feed data
			$feed_data = $queries_class->get_feed_row( $feed_id  );
			
			// get the meta data
			$meta_data = $queries_class->read_metadata( $feed_id );
			
			// get the category mapping
			$category_mapping = $queries_class->read_category_mapping( $feed_id );

			// generate a new unique feed name
			$feed_data->title = $support_class->next_unique_feed_name( $feed_data->title );
			$feed_data->url = 'No feed generated';
			
			// store a copy of the new feed
			$new_feed_id = $queries_class->insert_feed( $feed_data->channel_id, $feed_data->country_id, 
				$feed_data->source_id, $feed_data->title, $feed_data->feed_title, $feed_data->feed_description, $feed_data->main_category, $feed_data->include_variations, 
				$feed_data->is_aggregator, $feed_data->url, $feed_data->status_id, $feed_data->schedule );
			
			$result = $new_feed_id > 0 ? $queries_class->insert_meta_data( $new_feed_id, $meta_data, $category_mapping ) : false;
			
			return $result;
		}
		
		/**
		 * Backups all plugin related data from the database to a file
		 * 
		 * @since 1.7.2
		 * 
		 * @param string $backup_file_name
		 * @return boolean
		 */
		public static function backup_database_tables( $backup_file_name ) {
			$queries_class = new WPPFM_Queries();
			$file_class = new WPPFM_File_Class();

			$backup_file = WPPFM_BACKUP_DIR . '/' . $backup_file_name . '.sql';
			$backup_path = str_replace( "\\", "/", $backup_file );
			
			// prepair the folder structure to support saving backup files
			if ( !file_exists( WPPFM_BACKUP_DIR ) ) { WPPFM_Folders_Class::make_backup_folder(); }

			if ( !file_exists( $backup_path ) ) {
				$backup_file_text = $queries_class->read_full_backup_data();
				return $file_class->write_full_backup_file( $backup_path, $backup_file_text );
			} else {
				echo wppfm_show_wp_warning( __( "A backup file with the selected name already exists. Please choose another name or delete the existing file first.", 'wp-product-feed-manager' ) );
				return false;
			}
		}
		
		/**
		 * Checks the existing backup files for non compliant versions
		 * 
		 * @since 1.8.0
		 * 
		 * @return boolean true if a non compliant backup file exists
		 */
		public static function invalid_backup_exist() {
			
			if( !file_exists( WPPFM_BACKUP_DIR ) ) return false;
			
			$files = glob( WPPFM_BACKUP_DIR . '/*.{sql}', GLOB_BRACE );
			if( count( $files ) === 0 ) return false;
			
			foreach( $files as $file ) {
				$backup_string = file_get_contents( $file );
				
				// get the db version
				$backup_version_string = ltrim( substr( $backup_string, stripos( $backup_string, '#' ) ), '#' );
				$backup_db_version = substr( $backup_version_string, 0, strpos( $backup_version_string, '#' ) );

				if( $backup_db_version < get_option( 'wppfm_db_version' ) ) return true;
			}
			
			return false;
		}
		
		/**
		 * Restores the data from a backup file
		 * 
		 * @since 1.7.2
		 * 
		 * @param string name of the backup file
		 * @return boolean if restored successfully, string when not
		 */
		public static function restore_backup( $backup_file_name ) {
			$queries_class = new WPPFM_Queries();
			
			$backup_file = WPPFM_BACKUP_DIR . '/' . $backup_file_name;
			$backup_path = str_replace( "\\", "/", $backup_file );
			
			$current_db_version = get_option( 'wppfm_db_version' );

			if ( file_exists( $backup_path ) ) {
				
				$table_queries = [];
				$backup_string = file_get_contents( $backup_file );
				
				// remove the date string
				$backup_string = substr( $backup_string, stripos( $backup_string, '#' ) );
				
				// get the db version
				$backup_db_version = ltrim( $backup_string, '#' );
				$backup_db_version = substr( $backup_db_version, 0, strpos( $backup_db_version, '#' ) );

				if( $backup_db_version < $current_db_version ) {
					return __( "The backup file is of an older version of the database and can not be restored as it is not compatible with the current database.", 'wp-product-feed-manager' );
				}
				
				// remove the version
				$backup_string = self::remove_left_data_part( $backup_string );
				
				// reset the ftp passive setting
				$ftp_passive_setting = ltrim( $backup_string, '#' );
				$ftp_passive_setting = substr( $ftp_passive_setting, 0, strpos( $ftp_passive_setting, '#' ) );
				update_option( 'wppfm_ftp_passive', $ftp_passive_setting );
				
				// remove the ftp passive setting
				$backup_string = self::remove_left_data_part( $backup_string );
				
				// reset the auto feed fix setting
				$auto_feed_fix_setting = ltrim( $backup_string, '#' );
				$auto_feed_fix_setting = substr( $auto_feed_fix_setting, 0, strpos( $auto_feed_fix_setting, '#' ) );
				update_option( 'wppfm_auto_feed_fix', $auto_feed_fix_setting );
				
				// remove the auto feed fix setting
				$backup_string = self::remove_left_data_part( $backup_string );

				// split the string in table specific rows
				$table_strings = explode( "# backup string for database -> ", $backup_string );
				
				foreach( $table_strings as $string ) {
					$table_name = substr( $string, 0, stripos( $string, '#' ) );
					$query_string = substr( $string, stripos( $string, '#' ), strlen( $string ) );
					array_push( $table_queries, [trim($table_name), ltrim($query_string, "# <- # ")] );
				}
				
				// remove the first (empty) element
				array_shift( $table_queries );
				
				return $queries_class->restore_backup_data( $table_queries );
			} else {
				return __( "A backup file with the selected name does not exists.", 'wp-product-feed-manager' );
			}
		}
		
		/**
		 * Deletes an existing backup file
		 * 
		 * @since 1.7.2
		 * 
		 * @param string name of the backup file to be deleted
		 */
		public static function delete_backup_file( $backup_file_name ) {

			$backup_file = WPPFM_BACKUP_DIR . '/' . $backup_file_name;
			
			// only return results when the user is an admin with manage options
			if ( is_admin() ) {
				echo file_exists( $backup_file ) ? unlink( $backup_file ) : wppfm_show_wp_error( __( "Could not find file $backup_file.", 'wp-product-feed-manager' ) );
			} else {
				echo wppfm_show_wp_error( __( 'Error deleting the feed. You do not have the correct authorities to delete the file.', 'wp-product-feed-manager' ) );
			}
		}
		
		/**
		 * Duplicate an existing backup file
		 * 
		 * @since 1.7.2
		 * 
		 * @param string name of the backup file to be duplicated
		 */
		public static function duplicate_backup_file( $backup_file_name ) {
			
			$support_class = new WPPFM_Feed_Support_Class();
			
			$backup_file_name_without_extention = rtrim( $backup_file_name, '.sql' );
			$new_backup_file_title = $support_class->next_unique_feed_name( $backup_file_name_without_extention );
			$new_backup_file_name = $new_backup_file_title . '.sql';
			
			if ( !copy( WPPFM_BACKUP_DIR . '/' . $backup_file_name, WPPFM_BACKUP_DIR . '/'. $new_backup_file_name ) ) {
				echo __( "Failed to make a copy of $backup_file_name.", 'wp-product-feed-manager' );
			} else {
				echo true;
			}
		}
		
		private static function remove_left_data_part( $data_string ) {
			$ds = ltrim( $data_string, '#' );
			return substr( $ds, stripos( $ds, '#' ) );
		}
		
		private static function get_backupfile_version_number( $file ) {
			
		}
	}

	

endif;
