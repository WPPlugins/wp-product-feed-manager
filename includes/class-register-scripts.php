<?php

/* * ******************************************************************
 * Version 4.2
 * Modified: 11-05-2017
 * Copyright 2017 Accentio. All rights reserved.
 * License: None
 * By: Michel Jongbloed
 * ****************************************************************** */

// Prevent direct access
if ( !defined( 'ABSPATH' ) ) {
	echo 'Hi!  I\'m just a plugin, there\'s not much I can do when called directly.';
	exit;
}

if ( !class_exists( 'WPPFM_Register_Scripts' ) ) :

	/**
	 * The WPPFM_Register_Scripts registeres the required ajax and css scripts
	 * 
	 * @class WPPFM_Register_Scripts
	 * @version 4.2
	 */
	class WPPFM_Register_Scripts {

		// @private storage for queries class
		private $_uri;
		// @private storage of scripts version
		private $_version_stamp;
		// @private register minified scripts
		private $_js_min;

		/* --------------------------------------------------------------------------------------------------*
		 * Constructor
		 * -------------------------------------------------------------------------------------------------- */

		public function __construct() {

			$this->_uri = $_SERVER[ 'REQUEST_URI' ];

			$premium_version_nr		 = EDD_SL_ITEM_NAME === 'WP Product Feed Manager' ? 'fr-' : 'pr-'; // prefix for version stamp depending on premium or free version
			$action_level			 = 2; // for future use
			$this->_version_stamp	 = WP_DEBUG ? time() : $premium_version_nr . MYPLUGIN_VERSION_NUM;
			$this->_js_min			 = WP_DEBUG ? '' : '.min';

			// add hooks
			add_action( 'admin_enqueue_scripts', array( $this, 'wppfm_register_required_scripts_and_nonces' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'wppfm_register_required_style_sheets' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'wppfm_unregister_required_style_sheets' ), 90 );
			add_action( 'wp_print_scripts', array( $this, 'wppfm_unregister_required_scripts' ), 100 );

			if ( $action_level === 1 ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'wppfm_register_level_one_scripts' ) );
				add_action( 'wp_print_scripts', array( $this, 'wppfm_unregister_level_one_scripts' ), 101 );
			} elseif ( $action_level === 2 ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'wppfm_register_level_two_scripts' ) );
				add_action( 'wp_print_scripts', array( $this, 'wppfm_unregister_level_two_scripts' ), 101 );
			}
		}

		/**
		 * Registeres all required java scripts and generates the nonces
		 * 
		 * @param none
		 * @return nothing
		 */
		public function wppfm_register_required_scripts_and_nonces() {

			// embed the javascript file that makes the Ajax requests
			wp_enqueue_script( 'wppfm_business-logic-script', esc_url( MYPLUGIN_PLUGIN_URL . '/includes/application/js/wppfm_logic' . $this->_js_min . '.js' ), array( 'jquery' ), $this->_version_stamp );
			wp_enqueue_script( 'wppfm_data-handling-script', esc_url( MYPLUGIN_PLUGIN_URL . '/includes/data/js/wppfm_ajaxdatahandling' . $this->_js_min . '.js' ), array( 'jquery' ), $this->_version_stamp );
			wp_enqueue_script( 'wppfm_data-script', esc_url( MYPLUGIN_PLUGIN_URL . '/includes/data/js/wppfm_data' . $this->_js_min . '.js' ), array( 'jquery' ), $this->_version_stamp );
			wp_enqueue_script( 'wppfm_event-listener-script', esc_url( MYPLUGIN_PLUGIN_URL . '/includes/user-interface/js/wppfm_feed-form-events' . $this->_js_min . '.js' ), array( 'jquery' ), $this->_version_stamp );
			wp_enqueue_script( 'wppfm_feed-form-script', esc_url( MYPLUGIN_PLUGIN_URL . '/includes/user-interface/js/wppfm_feed-form' . $this->_js_min . '.js' ), array( 'jquery' ), $this->_version_stamp );
//			wp_enqueue_script( 'wppfm_setting-form-script', esc_url( MYPLUGIN_PLUGIN_URL . '/includes/user-interface/js/wppfm_setting-form' . $this->_js_min . '.js' ), array( 'jquery' ), $this->_version_stamp );
			wp_enqueue_script( 'wppfm_form-support-script', esc_url( MYPLUGIN_PLUGIN_URL . '/includes/user-interface/js/wppfm_support' . $this->_js_min . '.js' ), array( 'jquery' ), $this->_version_stamp );
			wp_enqueue_script( 'wppfm_verify-inputs-script', esc_url( MYPLUGIN_PLUGIN_URL . '/includes/user-interface/js/wppfm_verify-inputs' . $this->_js_min . '.js' ), array( 'jquery' ), $this->_version_stamp );
			wp_enqueue_script( 'wppfm_feed-handling-script', esc_url( MYPLUGIN_PLUGIN_URL . '/includes/application/js/wppfm_feedhandling' . $this->_js_min . '.js' ), array( 'jquery' ), $this->_version_stamp );
			wp_enqueue_script( 'wppfm_feed-html', esc_url( MYPLUGIN_PLUGIN_URL . '/includes/user-interface/js/wppfm_feed-html' . $this->_js_min . '.js' ), array( 'jquery' ), $this->_version_stamp );
			wp_enqueue_script( 'wppfm_feed-list-script', esc_url( MYPLUGIN_PLUGIN_URL . '/includes/user-interface/js/wppfm_feed-list' . $this->_js_min . '.js' ), array( 'jquery' ), $this->_version_stamp );
			wp_enqueue_script( 'wppfm_feed-meta-script', esc_url( MYPLUGIN_PLUGIN_URL . '/includes/application/js/wppfm_object-attribute-meta' . $this->_js_min . '.js' ), array( 'jquery' ), $this->_version_stamp );
			wp_enqueue_script( 'wppfm_feed-objects-script', esc_url( MYPLUGIN_PLUGIN_URL . '/includes/application/js/wppfm_object-feed' . $this->_js_min . '.js' ), array( 'jquery' ), $this->_version_stamp );
			wp_enqueue_script( 'wppfm_general-functions-script', esc_url( MYPLUGIN_PLUGIN_URL . '/includes/application/js/wppfm_general-functions' . $this->_js_min . '.js' ), array( 'jquery' ), $this->_version_stamp );
			wp_enqueue_script( 'wppfm_object-handling-script', esc_url( MYPLUGIN_PLUGIN_URL . '/includes/data/js/wppfm_metadatahandling' . $this->_js_min . '.js' ), array( 'jquery' ), $this->_version_stamp );
			
			// make a unique nonce for all Ajax requests
			wp_localize_script( 'wppfm_data-handling-script', 'MyAjax', array(
				// URL to wp-admin/admin-ajax.php to process the request
				'ajaxurl'				 => admin_url( 'admin-ajax.php' ),
				// generate the nonces
				'categoryListsNonce'	 => wp_create_nonce( 'myajax-category-lists-nonce' ),
				'deleteFeedNonce'		 => wp_create_nonce( 'myajax-delete-feed-nonce' ),
				'feedDataNonce'			 => wp_create_nonce( 'myajax-feed-data-nonce' ),
				'inputFieldsNonce'		 => wp_create_nonce( 'myajax-input-fields-nonce' ),
				'inputFeedFiltersNonce'	 => wp_create_nonce( 'myajax-feed-filters-nonce' ),
				'logMessageNonce'		 => wp_create_nonce( 'myajax-log-message-nonce' ),
				'nextCategoryNonce'		 => wp_create_nonce( 'myajax-next-category-nonce' ),
				'outputFieldsNonce'		 => wp_create_nonce( 'myajax-output-fields-nonce' ),
				'postFeedsListNonce'	 => wp_create_nonce( 'myajax-post-feeds-list-nonce' ),
				'switchFeedStatusNonce'	 => wp_create_nonce( 'myajax-switch-feed-status-nonce' ),
				'duplicateFeedNonce'	 => wp_create_nonce( 'myajax-duplicate-existing-feed-nonce' ),
				'updateFeedDataNonce'	 => wp_create_nonce( 'myajax-update-feed-data-nonce' ),
				'updateAutoFeedFixNonce' => wp_create_nonce( 'myajax-set-auto-feed-fix-nonce' ),
				'updateFeedFileNonce'	 => wp_create_nonce( 'myajax-update-feed-file-nonce' )
			));
		}

		public function wppfm_register_level_one_scripts() {

			$data				 = new WPPFM_Data_Class;
			$installed_channels	 = $data->get_channels();

// 260317
//			$upload_dir = wp_upload_dir();
//
//			// wp_upload_dir does not work with https
//			if ( substr( MYPLUGIN_PLUGIN_URL, 0, 5 ) === 'https' ) {
//				$upload_folder = str_replace( 'http://', 'https://', $upload_dir['baseurl'] );
//			} else {
//				$upload_folder = $upload_dir['baseurl'];
//			}

			wp_enqueue_script( 'wppfm_channel-functions-script', esc_url( MYPLUGIN_PLUGIN_URL . '/includes/application/js/wppfm_channel-functions' . $this->_js_min . '.js' ), array( 'jquery' ), $this->_version_stamp );

			foreach ( $installed_channels as $channel ) {

				wp_enqueue_script( 'wppfm_' . $channel[ 'short' ] . '-source-script', esc_url( WPPFM_UPLOADS_URL . '/wppfm-channels/' . $channel[ 'short' ] . '/wppfm_' . $channel[ 'short' ] . '-source.js' ), array( 'jquery' ), $this->_version_stamp );
			}
		}

		public function wppfm_unregister_required_scripts() {

			if ( stripos( $this->_uri, '/wp-admin/admin.php?page=' . MYPLUGIN_PLUGIN_NAME ) === false ) {

				wp_dequeue_script( 'wppfm_object-handling-script' );
				wp_dequeue_script( 'wppfm_general-functions-script' );
				wp_dequeue_script( 'wppfm_feed-objects-script' );
				wp_dequeue_script( 'wppfm_feed-meta-script' );
				wp_dequeue_script( 'wppfm_feed-list-script' );
				wp_dequeue_script( 'wppfm_feed-html' );
				wp_dequeue_script( 'wppfm_feed-handling-script' );
				wp_dequeue_script( 'wppfm_verify-inputs-script' );
				wp_dequeue_script( 'wppfm_setting-form-script' );
				wp_dequeue_script( 'wppfm_feed-form-script' );
				wp_dequeue_script( 'wppfm_form-support-script' );
				wp_dequeue_script( 'wppfm_event-listener-script' );
				wp_dequeue_script( 'wppfm_data-script' );
				wp_dequeue_script( 'wppfm_data-handling-script' );
				wp_dequeue_script( 'wppfm_business-logic-script' );
			}
		}

		public function wppfm_unregister_level_one_scripts() {

			if ( stripos( $this->_uri, '/wp-admin/admin.php?page=' . MYPLUGIN_PLUGIN_NAME ) === false ) {

				$data				 = new WPPFM_Data_Class;
				$installed_channels	 = $data->get_channels();

				wp_dequeue_script( 'wppfm_channel-functions-script' );

				foreach ( $installed_channels as $channel ) { wp_dequeue_script( 'wppfm_' . $channel[ 'short' ] . '-source-script' ); }
			}
		}

		public function wppfm_register_level_two_scripts() {

			wp_enqueue_script( 'wppfm_channel-functions-script', esc_url( MYPLUGIN_PLUGIN_URL . '/includes/application/js/wppfm_channel-functions.js' ), 
				array( 'jquery' ), $this->_version_stamp );

			wp_enqueue_script( 'wppfm_google-source-script', esc_url( MYPLUGIN_PLUGIN_URL . '/includes/application/google/wppfm_google-source.js' ), 
				array( 'jquery' ), $this->_version_stamp );
		}

		public function wppfm_unregister_level_two_scripts() {

			if ( stripos( $this->_uri, '/wp-admin/admin.php?page=' . MYPLUGIN_PLUGIN_NAME ) === false ) {

				wp_dequeue_script( 'wppfm_channel-functions-script' );

				wp_dequeue_script( 'wppfm_google-source-script' );
			}
		}

		/**
		 * Registers all required style sheets
		 */
		public function wppfm_register_required_style_sheets() {

			wp_register_style( 'wp-product-feed-manager', esc_url( MYPLUGIN_PLUGIN_URL . '/css/wppfm_admin-page' . $this->_js_min . '.css' ),
				'', $this->_version_stamp, 'screen' );
			wp_enqueue_style( 'wp-product-feed-manager' );
		}

		/**
		 * Unregister all registered style sheets
		 */
		public function wppfm_unregister_required_style_sheets() {

			if ( stripos( $this->_uri, '/wp-admin/admin.php?page=' . MYPLUGIN_PLUGIN_NAME ) === false ) {

				//wp_dequeue_style( 'wppfm_wp-product-feed-manager-stylesheet' );
				//wp_deregister_style( 'wppfm_wp-product-feed-manager-stylesheet' );
				wp_dequeue_style( 'wp-product-feed-manager' );
				wp_deregister_style( 'wp-product-feed-manager' );
			}
		}

	}

	// End of WPPFM_Register_Scripts class

endif;

$myajaxregistrationclass = new WPPFM_Register_Scripts();