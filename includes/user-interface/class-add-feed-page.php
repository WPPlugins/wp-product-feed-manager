<?php

/* * ******************************************************************
 * Version 1.1
 * Modified: 30-04-2017
 * Copyright 2017 Accentio. All rights reserved.
 * License: None
 * By: Michel Jongbloed
 * ****************************************************************** */

// Prevent direct access
if ( !defined( 'ABSPATH' ) ) {
	echo 'Hi!  I\'m just a plugin, there\'s not much I can do when called directly.';
	exit;
}

if ( !class_exists( 'WPPFM_Add_Feed_Page' ) ) :

	class WPPFM_Add_Feed_Page extends WPPFM_Admin_Page {

		private $_feed_form;

		public function __construct() {

			parent::__construct();
			
			// update the database if required
			$db_management = new WPPFM_Database();
			$db_management->verify_db_version();

			$this->prepare_feed_form();
		}

		public function show() {

			echo $this->admin_page_header();

			echo $this->message_field();

			echo $this->main_page_body_top();

			echo $this->main_admin_buttons();

			echo $this->admin_page_footer();
		}

		private function prepare_feed_form() {

			$this->_feed_form = new WPPFM_Feed_Form ();
		}

		private function main_page_body_top() {

			$this->_feed_form->display();
		}

		private function main_admin_buttons() {

			$html_code = '<div class="button-wrapper" id="page-bottom-buttons"><input class="button-primary" type="button" ' .
			'onclick="parent.location=\'admin.php?page=wp-product-feed-manager\'" name="new" value="' .
			__( 'Feeds List', 'wp-product-feed-manager' ) . '" id="add-new-feed-button" /></div>';

			return $html_code;
		}

	}

	

     // end of WPPFM_Add_Feed_Page class

endif;