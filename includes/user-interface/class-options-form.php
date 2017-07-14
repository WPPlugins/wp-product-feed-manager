<?php

/* * ******************************************************************
 * Version 2.2
 * Modified: 25-05-2017
 * Copyright 2017 Accentio. All rights reserved.
 * License: None
 * By: Michel Jongbloed
 * ****************************************************************** */

// Prevent direct access
if (!defined( 'ABSPATH' )) exit;

if ( !class_exists( 'WPPFM_Options_Form' ) ) :

	/**
	 * The WPPFM_Options_form class contains option form functions
	 * 
	 * @since		1.5.0
	 * 
	 * @class		WPPFM_Options_Form
	 * @version		2.2
	 * @category	Class
	 * @author		Michel Jongbloed
	 */
	class WPPFM_Options_Form {

		/* --------------------------------------------------------------------------------------------------*
		 * Public functions
		 * -------------------------------------------------------------------------------------------------- */

		/**
		 * Generates the main part of the Settings page
		 * 
		 * @since 1.5.0
		 * 
		 * @return string The html code for the mail part of the Settings page
		 */
		public function display() {
			$html_code = '<table class="form-table"><tbody>';
			$html_code .= $this->settings();
			$html_code .= '</tbody></table>';

			echo $html_code;
		}

		/* --------------------------------------------------------------------------------------------------*
		 * Private functions
		 * -------------------------------------------------------------------------------------------------- */
		
		/**
		 * Generates html code for the Setting page
		 * 
		 * @since 1.5.0
		 * @since 1.7.0 Added the backups table
		 * @since 1.8.0 Added the third party attributes text field
		 */
		private function settings() {
			$html_code = '';
			
			// ftp option is only required for the full version
			if ( EDD_SL_ITEM_NAME === 'Woocommerce Product Feed Manager' ) {
				$passive_option = get_option( 'wppfm_ftp_passive', true );
				$passive_checked = true === $passive_option || $passive_option === 'true' ? ' checked ' : '';
			
				$html_code .= '<tr valign="top" class="">';
				$html_code .= '<th scope="row" class="titledesc">' . __( 'FTP Passive Mode', 'wp-product-feed-manager' ) . '</th>';
				$html_code .= '<td class="forminp forminp-checkbox">';
				$html_code .= '<fieldset>';
				$html_code .= '<legend class="screen-reader-text"><span>' . __( 'FTP Passive Mode', 'wp-product-feed-manager' ) . '</span></legend>';
				$html_code .= '<label for="wppfm_ftp_passive_mode">';
				$html_code .= '<input name="wppfm_ftp_passive_mode" id="wppfm_ftp_passive_mode" type="checkbox" class="" value="1"';
				$html_code .= $passive_checked . '> ' . __( 'Use FTP Passive Mode when downloading channels (default on). Change this setting when you have trouble downloading a new channel.', 'wp-product-feed-manager') . '</label></fieldset>';
				$html_code .= '</td></tr>';
			}

			$auto_fix_feed_option = get_option( 'wppfm_auto_feed_fix', true );
			$checked = true === $auto_fix_feed_option || $auto_fix_feed_option === 'true' ? ' checked ' : '';

			$html_code .= '<tr valign="top" class="">';
			$html_code .= '<th scope="row" class="titledesc">' . __( 'Auto Feed Fix', 'wp-product-feed-manager' ) . '</th>';
			$html_code .= '<td class="forminp forminp-checkbox">';
			$html_code .= '<fieldset>';
			$html_code .= '<legend class="screen-reader-text"><span>' . __( 'Auto Feed Fix', 'wp-product-feed-manager' ) . '</span></legend>';
			$html_code .= '<label for="wppfm_auto_feed_fix_mode">';
			$html_code .= '<input name="wppfm_auto_feed_fix_mode" id="wppfm_auto_feed_fix_mode" type="checkbox" class="" value="1"';
			$html_code .= $checked . '> ' . __( 'Automatically fix feeds that are failed (default on). Change this setting if a feed keeps failing.', 'wp-product-feed-manager') . '</label></fieldset>';
			$html_code .= '</td></tr>';
			
			$third_party_attribute_keywords = get_option( 'wppfm_third_party_attribute_keywords', '_wpmr_%, _cpf_%, _unit%' );

			$html_code .= '<tr valign="top" class="">';
			$html_code .= '<th scope="row" class="titledesc">' . __( 'Third Party Attributes', 'wp-product-feed-manager' ) . '</th>';
			$html_code .= '<td class="forminp forminp-checkbox">';
			$html_code .= '<fieldset>';
			$html_code .= '<legend class="screen-reader-text"><span>' . __( 'Auto Feed Fix', 'wp-product-feed-manager' ) . '</span></legend>';
			$html_code .= '<label for="wppfm_third_party_attr_keys">';
			$html_code .= '<input name="wppfm_third_party_attr_keys" id="wppfm_third_party_attr_keys" type="text" class="" value="' . $third_party_attribute_keywords . '"> ';
			$html_code .= __( 'Enter comma separated keywords and wildcards to use third party attributes.', 'wp-product-feed-manager') . '</label></fieldset>';
			$html_code .= '</td></tr>';
			
			$html_code .= '<tr valign="top" class="">';
			$html_code .= '<th scope="row" class="titledesc">' . __( 'Backups', 'wp-product-feed-manager' ) . '</th>';
			$html_code .= '<td>';
			
			$html_code .= '<p>Available backups</p>';
			$html_code .= '<table id="wppfm-backups" class="wp-list-table smallfat fixed posts"';
			$html_code .= '<thead>';
			$html_code .= '<tr><th scope="col" class="wppfm-backup-filename">' . __( 'File name', 'wp-product-feed-manager' ) . '</th>';
			$html_code .= '<th scope="col" class="wppfm-backup-date">' . __( 'Backup date', 'wp-product-feed-manager' ) . '</th>';
			$html_code .= '<th scope="col">' . __( 'Actions', 'wp-product-feed-manager' ) . '</th></tr>';
			$html_code .= '</thead>';
			$html_code .= '<tbody id="wppfm-backups-list"></tbody>';
			$html_code .= '</table>';
			$html_code .= '<p>';
			$html_code .= '<span class="button-secondary" id="wppfm_prepare_backup">' . __( 'Add new backup', 'wp-product-feed-manager' ) . '</span>';
			$html_code .= '</p>';
			$html_code .= '</td></tr>';
			$html_code .= '<tr style="display:none;" id="wppfm_backup-wrapper"><th>&nbsp</th><td>';
			$html_code .= '<input type="text" class="regular-text" id="wppfm_backup-file-name" placeholder="Enter a file name">';
			$html_code .= '<span class="button-secondary" id="wppfm_make_backup" disabled>' . __( 'Backup current feeds', 'wp-product-feed-manager' ) . '</span>';
			$html_code .= '<span class="button-secondary" id="wppfm_cancel_backup">' . __( 'Cancel backup', 'wp-product-feed-manager' ) . '</span>';
			
			$html_code .= '</td></tr>';
			
			return $html_code;
		}
	}
	
endif;