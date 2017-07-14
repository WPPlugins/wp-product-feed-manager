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


if ( !class_exists( 'WPPFM_Feed_Object' ) ) :

	/**
	 * The WPPFM_Feed_Object class is an object that contains the data required to make a feed
	 * 
	 * 
	 * 
	 * @class WPPFM_Feed_Object
	 * @version dev
	 */
	class WPPFM_Feed_Object {

		public $feed_row;
		public $data;

	}

	

     // end of WPPFM_Feed_Object class

endif;	