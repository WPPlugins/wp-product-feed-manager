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

if ( !class_exists( 'WPPFM_Ajax_Cache_Class' ) ) :

	/**
	 * The WPPFM_Ajax_Cache_Class contains all functions for cache manipulation ajax calls
	 * 
	 * @class WPPFM_Ajax_Cache_Class
	 * @version dev
	 */
	class WPPFM_Ajax_Cache_Class {

		private $_queries;

		/**
		 * Class constructor
		 */
		public function __construct() {

			$this->_queries = new WPPFM_Queries ();

			// hooks
		}

	}

	// End of WPPFM_Ajax_Cache_Class

endif;

$myajaxfileclass = new WPPFM_Ajax_Cache_Class();
