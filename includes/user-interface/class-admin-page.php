<?php

/* * ******************************************************************
 * Version 1.0
 * Modified: 29-08-2015
 * Copyright 2015 Accentio. All rights reserved.
 * License: None
 * By: Michel Jongbloed
 * ****************************************************************** */

// Prevent direct access
if ( !defined( 'ABSPATH' ) ) {
	echo 'Hi!  I\'m just a plugin, there\'s not much I can do when called directly.';
	exit;
}

if ( !class_exists( 'WPPFM_Admin_Page' ) ) :

	/**
	 * 
	 */
	class WPPFM_Admin_Page {

		protected $spinner_gif;

		/**
		 * Class constructor
		 */
		protected function __construct() {

			// link to the spinner gif file
			$this->spinner_gif = MYPLUGIN_PLUGIN_URL . '/images/ajax-loader.gif';
		}

		/**
		 * Returns a string containing the standard header for an admin page.
		 * 
		 * @return string
		 */
		protected function admin_page_header( $header_text = "WP Product Feed Manager" ) {

			global $wpdb;

			return
			'
         <div class="wrap">
         <div class="feed-spinner" id="feed-spinner" style="display:none;">
            <img id="img-spinner" src="' . $this->spinner_gif . '" alt="Loading" />
         </div>
         <div class="data" id="wp-product-feed-manager-data" style="display:none;"><div id="wp-plugin-url">' . WPPFM_UPLOADS_URL . '</div><div id="wp-table-prefix">' . $wpdb->prefix . '</div></div>
         <div class="main-wrapper header-wrapper" id="header-wrapper">
         <div class="header-text"><h1>' . $header_text . '</h1></div>
         <div class="sub-header-text"><h3>' . __( 'Manage your feeds with ease', 'wp-product-feed-manager' ) . '</h3></div>
         <div class="logo"></div>
         </div>
      ';
		}

		/**
		 * Returns a string containing the standard footer for an admin page.
		 * 
		 * @return string
		 */
		protected function admin_page_footer() {

			return
			'
         <div class="main-wrapper footer-wrapper" id="footer-wrapper">
         <div class="links-wrapper" id="footer-links"><a href="' . EDD_SL_STORE_URL . '" target="_blank">About Us</a> 
		 | <a href="' . EDD_SL_STORE_URL . '/support/" target="_blank">Contact Us</a> 
		 | <a href="' . EDD_SL_STORE_URL . '/terms/" target="_blank">Terms and Conditions</a></div>
         </div></div>
      ';
		}

		protected function message_field( $alert = '' ) {
			$display_alert = !empty( $alert ) ? 'block' : 'none';
		
			return
			'
         <div class="message-field notice notice-error" id="error-message" style="display:none;"></div>
         <div class="message-field notice notice-success" id="success-message" style="display:none;"></div>
         <div class="message-field notice notice-warning" id="disposible-warning-message" style="display:' . $display_alert . ';"><p>' . $alert . '</p>
		<button type="button" id="disposible-notice-button" class="notice-dismiss"></button>
		</div>
      ';
		}

	}

	

     // end of WPPFM_Admin_Page class

endif;