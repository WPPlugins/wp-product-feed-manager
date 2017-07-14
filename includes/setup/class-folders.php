<?php

/* * ******************************************************************
 * Version 1.1
 * Modified: 07-08-2016
 * Copyright 2016 Accentio. All rights reserved.
 * License: None
 * By: Michel Jongbloed
 * ****************************************************************** */

// Prevent direct access
if ( !defined( 'ABSPATH' ) ) {
	echo 'Hi!  I\'m just a plugin, there\'s not much I can do when called directly.';
	exit;
}

if ( ! class_exists( 'WPPFM_Folders_Class' ) ) :

	/**
	 * The WPPFM_Folders_Class makes or removes folders
	 * 
	 * @class WPPFM_Folders_Class
	 * @version 1.1
	 * @category class
	 */
	class WPPFM_Folders_Class {

		public static function make_feed_support_folder() {
			if ( !file_exists( WPPFM_FEEDS_DIR ) ) { self::make_wppfm_dir( WPPFM_FEEDS_DIR ); }
		}

		public static function make_channels_support_folder() {
			if ( !file_exists( WPPFM_CHANNEL_DATA_DIR ) ) { self::make_wppfm_dir( WPPFM_CHANNEL_DATA_DIR ); }
		}
		
		public static function make_backup_folder() {
			if ( !file_exists( WPPFM_BACKUP_DIR ) ) { self::make_wppfm_dir( WPPFM_BACKUP_DIR ); }
		}
		
		public static function make_wppfm_dir( $dir ) {
			//$oldmask = umask( 0 ); 
			wp_mkdir_p( $dir );
			//umask( $oldmask );
		}
		
		public static function update_wppfm_channel_dir() {
			$old_channel_folder = WP_PLUGIN_DIR . '/wp-product-feed-manager-support/channels';
			
			if ( file_exists( $old_channel_folder ) ) {
				if ( file_exists( WPPFM_CHANNEL_DATA_DIR ) ) { // if channels folder already exists, remove it to prevent the rename function from failing
					self::delete_folder( WPPFM_CHANNEL_DATA_DIR ); 
				}  

				if ( !self::copy_folder( $old_channel_folder, WPPFM_CHANNEL_DATA_DIR ) ) { return false; }
				
				if ( !self::delete_folder( $old_channel_folder ) ) {
					require_once ( WP_PLUGIN_DIR . '/wp-product-feed-manager/includes/user-interface/wppfm-messaging.php' );
					echo wppfm_show_wp_warning( __( 'Unable to remove the "' . $old_channel_folder . '" folder. This folder is not required any more. Please try removing this folder manually using ftp software or an equivalent methode.', 'wp-product-feed-manager' ) );
				}
			}
		}

		/**
		 * Deletes a directory and all its content
		 * @param string $folder_name
		 * @return boolean true when the directory has been deleted
		 */
		public static function delete_folder( $folder_name ) {
			if ( is_dir( $folder_name ) ) {

				$dir_handle = opendir( $folder_name );

				if ( !$dir_handle ) { return false; }

				while ( $file = readdir( $dir_handle ) ) {
					if ( $file != "." && $file != ".." ) {
						if ( !is_dir( $folder_name . "/" . $file ) ) { 
							unlink( $folder_name . "/" . $file );
						} else {
							self::delete_folder( $folder_name . '/' . $file );
						}
					}
				}

				closedir( $dir_handle );
				rmdir( $folder_name );

				return true;
			} else {
				return false;
			}
		}
		
		public static function copy_folder( $source_folder, $target_folder ) {
			$result = true;
			$dir = opendir( $source_folder ); 

			self::make_wppfm_dir( $target_folder ); 

			while( false !== ( $file = readdir( $dir ) ) ) { 
				
				if ( ! $result ) { break; }

				if ( ( $file != '.' ) && ( $file != '..' ) ) {
					if ( is_dir( $source_folder . '/' . $file ) ) {
						self::copy_folder( $source_folder . '/' . $file,$target_folder . '/' . $file );
					} else {
						$result = copy( $source_folder . '/' . $file,$target_folder . '/' . $file );
					} 
				} 
			} 

			closedir( $dir );
			
			return $result;
		}
		
		public static function folder_is_empty( $folder ) {
			
			if ( !is_readable( $folder ) ) { return NULL; }
			
			$handle = opendir( $folder );
			
			while ( false !== ( $entry = readdir( $handle ) ) ) {
				
				if ( $entry != "." && $entry != ".." ) {
					return false;
				}
			}
			
			return true;
		}

	}

	

	// end of WPPFM_Folders_Class

endif;	
