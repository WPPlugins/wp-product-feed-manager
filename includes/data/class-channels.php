<?php

/* * ******************************************************************
 * Version 1.2
 * Modified: 24-03-2017
 * Copyright 2017 Accentio. All rights reserved.
 * License: None
 * By: Michel Jongbloed
 * ****************************************************************** */

// Prevent direct access
if ( !defined( 'ABSPATH' ) ) {
	echo 'Hi!  I\'m just a plugin, there\'s not much I can do when called directly.';
	exit;
}

if ( !class_exists( 'WPPFM_Channel' ) ) :

	/**
	 * The WPPFM_Channel class contains the functions that make the required for working with channels
	 * 
	 * @class WPPFM_Channel
	 * @version dev
	 */
	class WPPFM_Channel {
		/* --------------------------------------------------------------------------------------------------*
		 * Attributes
		 * -------------------------------------------------------------------------------------------------- */

		private $_channels;

		/**
		 * WPPFM_Tables
		 * 
		 * @access public
		 */
		public function __construct() {

			// WPPFM_CHANNEL_RELATED
			$this->_channels = array( 
			
				new Channel( '0', 'usersetup', 'Free User Setup' ),
				new Channel( '1', 'google', 'Google Merchant Centre' ),
				new Channel( '2', 'bing', 'Bing Merchant Centre' ),
				new Channel( '3', 'beslis', 'Beslis.nl'),
				new Channel( '4', 'pricegrabber', 'PriceGrabber'),
				new Channel( '5', 'shopping', 'Shopping.com (eBay)'),
				new Channel( '6', 'amazon', 'Amazon product ads'),
				new Channel( '7', 'connexity', 'Connexity'),
				new Channel( '8', 'become', 'Become'), // Become is overgenomen door Connexity, https://merchants.become.com/DataFeedSpecification.html linkt ook naar Connexity
				new Channel( '9', 'nextag', 'Nextag'),
				new Channel( '10', 'kieskeurig', 'Kieskeurig.nl'),
				new Channel( '11', 'vergelijk', 'Vergelijk.nl'),
				new Channel( '12', 'koopjespakker', 'Koopjespakker.nl'),
				new Channel( '13', 'avantlink', 'AvantLink'),
				new Channel( '14', 'zbozi', 'Zbozi'),
				new Channel( '15', 'comcon', 'Commerce Connector' ),
				new Channel( '16', 'facebook', 'Facebook' ),
				new Channel( '17', 'bol', 'Bol.com' ),
				new Channel( '18', 'adtraction', 'Adtraction' ),
				new Channel( '998', 'marketingrobot_csv', 'Custom CSV Export' ),
				new Channel( '999', 'marketingrobot', 'Custom XML Export')
			);
		}
		
		public function get_active_channel_details( $channel_name ) {
			
			foreach( $this->_channels as $channel ) {
				
				if ( $channel->channel_short === $channel_name ) {
					
					return $channel;
				}
			}
		}
		
		public function get_channel_short_name( $channel_id ) {
			
			foreach( $this->_channels as $channel ) {
				
				if ( $channel->channel_id === $channel_id ) {
					
					return $channel->channel_short;
				}
			}
		}
		
		public function get_channel_name( $channel_id ) {
			
			foreach( $this->_channels as $channel ) {
				
				if ( $channel->channel_id === $channel_id ) {
					
					return $channel->channel_name;
				}
			}
		}
		
		public function get_installed_channel_names() {
			
			$file_class = new WPPFM_File_Class();

			return $file_class->get_installed_channels_from_file();
		}

		public function remove( $channel, $nonce ) {

			if ( wp_verify_nonce( $nonce, 'delete-channel-nonce' ) ) {
				
				$this->remove_channel( $channel );
			}
		}
		
		public function update( $channel, $code, $nonce ) {

			if ( wp_verify_nonce( $nonce, 'update-channel-nonce' ) ) {
				
				$this->update_channel( $channel, $code );
			}
		}
		
		public function install( $channel, $code, $nonce ) {

			if ( wp_verify_nonce( $nonce, 'install-channel-nonce' ) ) {
				
				$this->install_channel( $channel, $code );
			}
		}
		
		public function get_channels_from_server() {
			
			$url = trailingslashit( EDD_SL_STORE_URL ) . 'wpmr/channels/channels.php';
			$registered_to = 'localhost/winkeltje/';
			
			$response = wp_remote_post( 
				$url,
				array(
					'body' => array(
						'unique-site-id'	=> trim( get_option( 'wppfm_lic_key' ) ),
						'site'				=> $registered_to
					)
				));
			
			return $response;
		}
		
		public function get_number_of_updates_from_server( $channel_updated ) {
			
			if ( date( 'Ymd' ) === get_option( 'wppfm_channel_update_check_date' ) ) {

				if ( $channel_updated ) { decrease_updatable_channels(); }

				return get_option( 'wppfm_channels_to_update' );
			} else {
			
				$response = $this->get_channels_from_server();

				if ( ! is_wp_error( $response ) ) {

					$available_channels = json_decode( $response['body'] );

					if ( $available_channels ) {

						$installed_channels_names = $this->get_installed_channel_names();

						$this->add_status_data_to_available_channels( $available_channels, $installed_channels_names, false );

						$stored_count = $this->count_updatable_channels( $available_channels );

						$count = $channel_updated ? $stored_count-- : $stored_count;
						update_option( 'wppfm_channels_to_update', $count > 0 ? $count : 0 );
						update_option( 'wppfm_channel_update_check_date', date( 'Ymd' ) );

						return $count;
					}
				}
			}

			return 0;
		}
		
		public function add_status_data_to_available_channels( &$available_channels, $installed_channels, $updated ) {
			
			for ( $i = 0; $i < count( $available_channels ); $i++ ) {
				
				if ( in_array( $available_channels[$i]->short_name, $installed_channels ) ) {
					
					$available_channels[$i]->status = 'installed';

					$available_channels[$i]->installed_version = $available_channels[$i]->short_name === $updated ? $available_channels[$i]->version 
						: $this->get_channel_file_version( $available_channels[$i]->short_name, 0 );
				} else {
					
					$available_channels[$i]->status = 'not installed';
					$available_channels[$i]->installed_version = '0';
				}
			}
		}
		
		private function get_channel_file_version( $channel_name, $rerun_counter ) {

			if ( $rerun_counter < 3 ) {

				if ( class_exists( 'WPPFM_' . ucfirst( $channel_name ) . '_Feed_Class' ) ) {

					$class_var = 'WPPFM_' . ucfirst( $channel_name ) . '_Feed_Class';

					$channel_class = new $class_var();

					return $channel_class->get_version();
				} else {

					// reset the registered channels in the channel table
					$db_class = new WPPFM_Database();
					$db_class->reset_channel_registration();

					include_channels(); // include the channel classess

					return $this->get_channel_file_version( $channel_name, $rerun_counter++ );
				}
			} else {
				
				if ( stripos( $this->_uri, '/wp-admin/admin.php?page=' . MYPLUGIN_PLUGIN_NAME ) ) {

					echo wppfm_show_wp_error( __( "Channel " . $channel_name . " is not installed correctly. Please try to Deactivate and then Activate the Feed Manager Plugin in your Plugins page.", 'wp-product-feed-manager' ) );
					wppfm_write_log_file( "Error: Channel " . $channel_name . " is not installed correctly." );
				}
				
				return 'unknown';
			}
		}
		
		private function count_updatable_channels( $channel_data ) {
			
			$counter = 0;
			
			foreach( $channel_data as $channel ) {

				if ( $channel->status === 'installed' && ( $channel->version > $channel->installed_version ) ) {
					
					$counter++;
				}
			}
			
			return $counter;
		}
		
		private function update_channel( $channel, $code ) {
			
			$file_class = new WPPFM_File_Class();
			$ftp_class = new WPPFM_FTP_Class();
			
			// remove the out dated channel source files from the server
			$file_class->delete_channel_source_files( $channel );
			
			$get_result = $ftp_class->get_channel_source_files( $channel, $code );

			// get the update files from wpmarketingrobot.com
			if ( false !== $get_result ) {
			
				// unzip the file
				$file_class->unzip_channel_file( $channel );
				
				// register the update
				decrease_updatable_channels();
			}
		}
		
		private function remove_channel( $channel ) {
			
			$data_class = new WPPFM_Data_Class();
			$file_class = new WPPFM_File_Class();
			
			// get the channel id that needs to be removed
			$channel_id = $data_class->get_channel_id_from_short_name( $channel );

			if ( $channel_id ) { // confirm channel is installed

				// remove channel related feed files
				$file_class->delete_channel_feed_files( $channel_id );

				// remove any channel related feed data and feed meta
				$data_class->delete_channel_feeds( $channel_id );

				// remove the channel from the 6 table
				$data_class->delete_channel( $channel_id );

				// remove the channel source files from the server
				$file_class->delete_channel_source_files( $channel );
			}
		}
		
		private function install_channel( $channel_name, $code ) {
			
			$ftp_class = new WPPFM_FTP_Class();
			$file_class = new WPPFM_File_Class();
			$data_class = new WPPFM_Data_Class();
			
			if ( plugin_version_supports_channel( $channel_name ) ) {

				$get_result = $ftp_class->get_channel_source_files( $channel_name, $code );

				// get the update files from wpmarketingrobot.com
				if ( false !== $get_result ) {

					// unzip the file
					$file_class->unzip_channel_file( $channel_name );

					// register the new channel
					$channel_details = $this->get_active_channel_details( $channel_name );

					$data_class->register_channel( $channel_name, $channel_details );
				} else {

					wppfm_write_log_file( "Could not get the $channel_name channel file from the server. Get_result message is $get_result." );
				}
			} else {
				
				echo wppfm_show_wp_warning( __( "Channel $channel_name is not supported by your current plugin version. Please update your plugin to the latest version and try uploading this channel again.", 'wp-product-feed-manager' ) );
			}
		}
	}

     // end of WPPFM_Channel class
	
	class Channel {
		
		public $channel_id;
		public $channel_short;
		public $channel_name;

		public function __construct( $id, $short, $name ) {
			
			$this->channel_id = $id;
			$this->channel_short = $short;
			$this->channel_name = $name;
		}
	}

	// end of Channel class
endif;