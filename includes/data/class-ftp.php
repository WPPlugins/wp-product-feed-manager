<?php

/* * ******************************************************************
 * Version 1.2
 * Modified: 17-06-2016
 * Copyright 2017 Accentio. All rights reserved.
 * License: None
 * By: Michel Jongbloed
 * ****************************************************************** */

// Prevent direct access
if ( !defined( 'ABSPATH' ) ) {
	echo 'Hi!  I\'m just a plugin, there\'s not much I can do when called directly.';
	exit;
}

if ( !class_exists( 'WPPFM_FTP_Class' ) ) :

	/**
	 * The WPPFM_FTP_Class handles all ftp actions
	 * 
	 * @class WPPFM_FTP_Class
	 * @version 1.2
	 * @category class
	 */
	class WPPFM_FTP_Class {

		// @private storage for queries class
		private $_ftp_server;
		private $_ftp_username;
		private $_ftp_pw;

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->_ftp_server	 = 'ftp.wpmarketingrobot.com';
			$this->_ftp_username = 'wppfmchannelupdates@wpmarketingrobot.com';
			$this->_ftp_pw		 = '88GF47Xtb9Qb';
		}

		public function get_channel_source_files( $channel, $code ) {
			
			if ( !function_exists( 'ftp_connect' ) ) {
				echo wppfm_show_wp_error( __( 'Your PHP settings do currently not support ftp functionality. 
					Please see http://php.net/manual/en/ftp.installation.php for more information about enabling ftp support.', 'wp-product-feed-manager' ) );
				return false;
			}

			$ftp_server	 = $this->_ftp_server;
			$ftp_conn	 = ftp_connect( $ftp_server ) or die( 'Could not connect to the ftp server.' );

			if ( !file_exists( WPPFM_CHANNEL_DATA_DIR ) ) { WPPFM_Folders_Class::make_channels_support_folder(); }

			$login = ftp_login( $ftp_conn, $this->_ftp_username, $this->_ftp_pw );
			
			if ( !$login ) {
				
				echo wppfm_show_wp_error( __('Failed to login on the ftp server.', 'wp-product-feed-manager') );
				
				$lic_status = get_option( 'wppfm_lic_status' );
				$lic_key = get_option( 'wppfm_lic_key' );
				
				$error_message = "Failed to login on the ftp server with license status=$lic_status and key=$lic_key";
				
				wppfm_write_log_file($error_message);
				
				return false;
			} else {

				$local_file	 = WPPFM_CHANNEL_DATA_DIR . '/' . $channel . '.zip';
				$server_file = $code . '.zip';
				$ftp_pasive_option = get_option( 'wppfm_ftp_passive', true );
				
				if ( $ftp_pasive_option === true || $ftp_pasive_option === 'true' ) {
					ftp_pasv( $ftp_conn, true ); // put the server in passive mode
				}

				if ( !ftp_get( $ftp_conn, $local_file, $server_file, FTP_BINARY ) ) {
					echo wppfm_show_wp_error( sprintf( __('Could not download the update file. Please try again. 
						Also make sure you have read/write permissions for the %s folder on your server. 
						If this message persists, please report the issue to michel@wpmarketingrobot.com 
						and include the error.log file from the plugin folder.', 'wp-product-feed-manager'), WPPFM_CHANNEL_DATA_DIR ) );
				
					$lic_status = get_option( 'wppfm_lic_status' );
					$lic_key = get_option( 'wppfm_lic_key' );

					$error_message = "Could not download the requested $channel update file with a license status=$lic_status and using key=$lic_key";

					wppfm_write_log_file($error_message);

					return false;
				}
			}

			ftp_close( $ftp_conn );
			
			return true;
		}

	}

	

	// end of WPPFM_FTP_Class

endif;
