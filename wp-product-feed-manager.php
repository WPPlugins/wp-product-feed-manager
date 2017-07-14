<?php

/**
 * Plugin Name: WP Product Feed Manager
 * Plugin URI: http://www.wpmarketingrobot.com
 * Description: An easy to use WordPress plugin that generates and submits your product feeds to merchant centres.
 * Version: 1.6.1
 * Modified: 16-05-2017
 * Author: Michel Jongbloed
 * Author URI: http://www.wpmarketingrobot.com
 * Requires at least: 4.6
 * Tested up to: 4.8
 *
 * Text Domain: wp-product-feed-manager
 * Domain Path: /languages/
 */

// Prevent direct access
if ( !defined( 'ABSPATH' ) ) {
	echo 'Hi!  I\'m just a plugin, there\'s not much I can do when called directly.';
	exit;
}

if ( !class_exists( 'WP_Product_Feed_Manager' ) ) :

	/**
	 * The Main WP_Product_Feed_Manager Class
	 * 
	 * @class WP_Product_Feed_Manager
	 * @version 1.6.1
	 */
	final class WP_Product_Feed_Manager {
		/* --------------------------------------------------------------------------------------------------*
		 * Attributes
		 * -------------------------------------------------------------------------------------------------- */

		/**
		 * @var string containing the version number of the plugin
		 */
		public $version = '1.6.1';

		/**
		 * @var string countaining the authors name
		 */
		public $author = 'Michel Jongbloed';

		/**
		 * @var WP_Product_Feed_Manager single instance
		 */
		private static $instance = null;

		/**
		 * Returns the Singleton instance of this class
		 * 
		 * @static
		 * @access public
		 * @return WP_Product_Feed_Manager Main instance
		 */
		public static function get_instance() {

			if ( is_null( self::$instance ) ) { self::$instance = new self(); }
			
			return self::$instance;
		}

		/**
		 * Cloning is not allowed.
		 * @since 4.2.4
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cloning is not allowed', 'wp-product-feed-manager' ), '4.2.4' );
		}

		/**
		 * Unserializing instances of this class is not allowed.
		 * @since 4.2.4
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is not allowed', 'wp-product-feed-manager' ), '4.2.4' );
		}

		/**
		 * WP_Product_Feed_Manager Constructor
		 */
		private function __construct() {
			// set the constants to be used in this plugin
			$this->define_constants();

			// hooks
			$this->hooks();
			
			// temporary function that moves the support folders from the plugin folder to the upload folder (as of version 1.2.3)
			$this->update_support_folders();

			// includes
			$this->includes();
			
			// register my version
			add_option( 'myplugin_version', MYPLUGIN_VERSION_NUM );
			
			// register my schedule
			add_action( 'wppfm_feed_update_schedule', array( $this, 'activate_feed_update_schedules' ) );
		}

		/* --------------------------------------------------------------------------------------------------*
		 * Private functions
		 * -------------------------------------------------------------------------------------------------- */

		/**
		 * Defines a few important constants
		 */
		private function define_constants() {
			// Store the name of the plugin
			if ( !defined( 'MYPLUGIN_PLUGIN_NAME' ) ) { define( 'MYPLUGIN_PLUGIN_NAME', trim( dirname( plugin_basename( __FILE__ ) ), '/' ) ); }

			// Store the directory of the plugin
			if ( !defined( 'MYPLUGIN_PLUGIN_DIR' ) ) { define( 'MYPLUGIN_PLUGIN_DIR', plugin_dir_path( __FILE__ ) ); }

			// Store the url of the plugin
			if ( !defined( 'MYPLUGIN_PLUGIN_URL' ) ) { define( 'MYPLUGIN_PLUGIN_URL', plugins_url() . '/' . MYPLUGIN_PLUGIN_NAME );	}

			// Store the version of my plugin
			if ( !defined( 'MYPLUGIN_VERSION_NUM' ) ) { define( 'MYPLUGIN_VERSION_NUM', $this->version ); }

			// Store the url to wpmarketingrobot.com
			if ( !defined( 'EDD_SL_STORE_URL' ) ) { define( 'EDD_SL_STORE_URL', 'http://www.wpmarketingrobot.com/' ); }

			// Store the plugin title
			if ( !defined( 'EDD_SL_ITEM_NAME' ) ) { define( 'EDD_SL_ITEM_NAME', 'WP Product Feed Manager' ); }
			
			// Store the base uploads folder, should also work in a multisite environment
			if ( !defined( 'WPPFM_UPLOADS_DIR' ) ) {
				$wp_upload_dir = wp_upload_dir();
				$upload_dir = is_multisite() && defined( 'UPLOADS' ) ? UPLOADS : $wp_upload_dir['basedir'];
				
				if ( !file_exists( $upload_dir ) && !is_dir( $upload_dir ) ) {
					define( 'WPPFM_UPLOADS_DIR', $wp_upload_dir['basedir'] );
				} else {
					define( 'WPPFM_UPLOADS_DIR', $upload_dir );
				}
			}
			
			if( !defined( 'WPPFM_UPLOADS_URL' ) ) {
				$wp_upload_dir = wp_upload_dir();
				
				// correct baseurl for https if required
				if ( is_ssl() ) {
					$url = str_replace( 'http://', 'https://', $wp_upload_dir['baseurl'] );
				} else {
					$url = $wp_upload_dir['baseurl'];
				}
				
				define( 'WPPFM_UPLOADS_URL', $url );
			}

			// store the folder that contains the channels data
			if ( !defined( 'WPPFM_CHANNEL_DATA_DIR' ) ) {
				define( 'WPPFM_CHANNEL_DATA_DIR', MYPLUGIN_PLUGIN_DIR . 'includes/application' );
			}

			// store the folder that contains the backup files
			if ( !defined( 'WPPFM_BACKUP_DIR' ) ) { define( 'WPPFM_BACKUP_DIR', WPPFM_UPLOADS_DIR . '/wppfm-backups' ); }

			// store the folder that contains the feeds
			if ( !defined( 'WPPFM_FEEDS_DIR' ) ) { define( 'WPPFM_FEEDS_DIR', WPPFM_UPLOADS_DIR . '/wppfm-feeds' );	}
		}

		/**
		 * Sets the hooks
		 */
		private function hooks() {
			// registeres the activation, deactivation and uninstall hooks
			register_activation_hook( __FILE__, array( &$this, 'on_activation' ) );
			register_deactivation_hook( __FILE__, array( &$this, 'on_deactivation' ) );
		}

		/**
		 * Includes the required files
		 */
		private function includes() {
			// include the wordpress pluggable.php file on forehand to prevent a "Call to undefined function wp_get_current_user()" error
			require_once( ABSPATH . 'wp-includes/pluggable.php' );

			if ( is_admin() ) {
				// include the admin menu and the includes file
				require_once ( 'includes/user-interface/wppfm-admin-menu.php' );
				require_once ( 'includes/data/wppfm-admin-functions.php' );
				require_once ( 'includes/user-interface/wppfm-messaging.php' );
				require_once ( 'includes/wppfm-wpincludes.php' );

				// include all required classes
				include_classes();
				include_channels();
			} else {
				require_once ( 'includes/user-interface/wppfm-messaging.php' );
				wppfm_show_wp_warning( __( 'You have insufficient rights to use ' . EDD_SL_ITEM_NAME, 'wp-product-feed-manager' ) );
			}
		}
		
		/**
		 * Temporary function to move the support folders from the plugins folder to the uploads folder
		 * This folder switch is required converting from a < 1.2.3 version
		 */
		private function update_support_folders() {
			$old_feed_folder = WP_PLUGIN_DIR . '/wp-product-feed-manager-support/feeds';
			$old_support_folder = WP_PLUGIN_DIR . '/wp-product-feed-manager-support';
			
			if ( !class_exists( 'WPPFM_Folders_Class' ) ) { require_once ( __DIR__ . '/includes/setup/class-folders.php' );	}
			
			// if the old channel folder still exists then make the new folder structure and move the old channel files to the new structure
			if ( file_exists( WP_PLUGIN_DIR . '/wp-product-feed-manager-support/channels' ) ) {
				if ( !class_exists( 'WPPFM_Queries' ) ) { require_once ( __DIR__ . '/includes/data/class-queries.php' ); }

				if ( !class_exists( 'WPPFM_File_Class' ) ) { require_once ( __DIR__ . '/includes/data/class-file.php' ); }

				if ( !class_exists( 'WPPFM_Channel' ) ) { require_once ( __DIR__ . '/includes/data/class-channels.php' ); }

				if ( !class_exists( 'WPPFM_Data_Class' ) ) { require_once ( __DIR__ . '/includes/data/class-data.php' ); }

				if ( !class_exists( 'WPPFM_Database' ) ) { require_once ( __DIR__ . '/includes/setup/class-wp-db-management.php' );	}
				
				// make the new folder structure
				WPPFM_Folders_Class::make_feed_support_folder();
				
				// if required move existing channel files to the new folders
				WPPFM_Folders_Class::update_wppfm_channel_dir();
				
				// reset the registered channels in the channel table
				$db_class = new WPPFM_Database();
				$db_class->reset_channel_registration();
			}
			
			if ( WPPFM_Folders_Class::folder_is_empty( $old_feed_folder ) ) { WPPFM_Folders_Class::delete_folder( $old_feed_folder ); }
			
			if ( WPPFM_Folders_Class::folder_is_empty( $old_support_folder ) ) { WPPFM_Folders_Class::delete_folder( $old_support_folder );	}
		}

		/* --------------------------------------------------------------------------------------------------*
		 * Public functions
		 * -------------------------------------------------------------------------------------------------- */

		public function activate_feed_update_schedules() {
			// temporary function that moves the support folders from the plugin folder to the upload folder (as of version 1.2.3)
			$this->update_support_folders();

			require_once ( __DIR__ . '/includes/application/wppfm-cron.php' );
			
			wppfm_update_feeds();
		}

		/**
		 * Performs the required actions on activation of the plugin
		 * 
		 * @param none
		 * @return nothing
		 */
		public function on_activation() {
			// add the required tables to the database
			$wppfm_database = new WPPFM_Database();
			$wppfm_database->make();

			wp_schedule_event( time(), 'hourly', 'wppfm_feed_update_schedule' );
		}

		/**
		 * Performs the required actions on deactivation of the plugin
		 * 
		 * @param none
		 * @return nothing
		 */
		public function on_deactivation() {
			// stop the scheduled feed update actions
			wp_clear_scheduled_hook( 'wppfm_feed_update_schedule' );
		}

	}

	// end of WP_Product_Feed_Manager class

endif;

WP_Product_Feed_Manager::get_instance();