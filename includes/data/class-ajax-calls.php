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

if ( !class_exists( 'WPPFM_Ajax_Calls' ) ) :

	/**
	 * The WPPFM_Ajax_Calls class contains all basic functions for all ajax calls
	 * 
	 * @class WPPFM_Ajax_Calls
	 * @version dev
	 */
	class WPPFM_Ajax_Calls {

		public $_queries;
		public $_files;

		public function __construct() {
			
		}

		protected function safe_ajax_call( $nonce, $registerd_nonce_name ) {

			// check the nonce
			if ( !wp_verify_nonce( $nonce, $registerd_nonce_name ) ) {
				die( __( 'You are not allowed to do this!', 'wp-product-feed-manager' ) );
			}

			// only return results when the user is an admin with manage options
			if ( is_admin() ) {

				// output the response
				return true;
			} else {
				return false;
			}
		}

	}

	 // end of WPPFM_Ajax_Calls class

endif;
