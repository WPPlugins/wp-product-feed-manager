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

if ( !class_exists( 'WPPFM_List_Table' ) ) :

	/**
	 * Provides a base class for making standard list tables
	 */
	class WPPFM_List_Table {

		private $_column_titles = array();
		private $_table_id;
		private $_table_id_string;

		/**
		 * Constructor
		 */
		public function __construct() {

			$this->_table_id = '';

			$this->_table_id_string = '';
		}

		/**
		 * Sets the column titles
		 * 
		 * @param array of strings containing the column titles
		 */
		public function set_column_titles( $titles ) {

			if ( !empty( $titles ) ) {

				$this->_column_titles = $titles;
			}
		}

		public function set_table_id( $id ) {

			if ( $id !== $this->_table_id ) {

				$this->_table_id			 = $id;
				$this->_table_id_string	 = ' id="' . $id . '"';
			}
		}

		public function display() {

			echo '<table class="wp-list-table tablepress widefat fixed posts" id="feedlisttable">';

			echo $this->table_header();

			echo $this->table_footer();

			echo $this->table_body();

			echo '</table>';
		}

		private function table_header() {
			
			$html = '<thead><tr>';

			foreach ( $this->_column_titles as $title ) {

				$html .= '<th>' . __( $title ) . '</th>';
			}

			$html .= '</tr></thead>';

			return $html;
		}

		private function table_footer() {

			$html = '<tfoot><tr>';

			foreach ( $this->_column_titles as $title ) {

				$html .= '<th>' . __( $title ) . '</th>';
			}

			$html .= '</tr></tfoot>';

			return $html;
		}

		private function table_body() {

			$html = '<tbody' . $this->_table_id_string . '></tbody>';

			return $html;
		}

	}

	

    

     // end of WPPFM_List_Table class

endif;