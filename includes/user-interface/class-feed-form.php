<?php

/* * ******************************************************************
 * Version 2.0
 * Modified: 24-12-2015
 * Copyright 2015 Accentio. All rights reserved.
 * License: None
 * By: Michel Jongbloed
 * ****************************************************************** */

// Prevent direct access
if ( !defined( 'ABSPATH' ) ) {
	echo 'Hi!  I\'m just a plugin, there\'s not much I can do when called directly.';
	exit;
}


if ( !class_exists( 'WPPFM_Feed_Form' ) ) :

	/**
	 * Provides a base class for making standard list tables
	 */
	class WPPFM_Feed_Form {

		/**
		 * Constructor
		 */
		public function __construct() {

		}

		public function display() {

			$html_code = '<div class="meta-box-sortables ui-sortable>';
			$html_code .= '<h3>' . __( 'Basic Feed Options', 'wp-product-feed-manager' ) . '</h3>';

			echo $this->tabs();

			echo $this->add_or_edit_feed_page_code();

			$html_code .= '</div>';

			echo $html_code;
		}

		private function tabs() {

			$html_code = '<h2 class="nav-tab-wrapper">';
			$html_code .= '<a href="admin.php?page=wp-product-feed-manager" class="nav-tab">' . __( 'Feeds List', 'wp-product-feed-manager' ) . '</a>';
			$html_code .= '<a href="admin.php?page=wp-product-feed-manager-add-new-feed\" class="nav-tab nav-tab-active">' . __( 'Add or Edit Feed', 'wp-product-feed-manager' ) . '</a>';
			$html_code .= '</h2>';

			return $html_code;
		}

		private function add_or_edit_feed_page_code() {

			$html_code = '<table class="feed-main-input-table form-table">';
			$html_code .= '<tbody id="feed-data">';
			$html_code .= '<tr><th id="main-feed-input-label"><label for="file-name">' . __( 'File Name', 'wp-product-feed-manager' ) . '</label> :</th>';
			$html_code .= '<td>' . WPPFM_Feed_Form_Control::feed_name_selector() . '</td></tr>';
			$html_code .= '<tr style="display:none;"><th id="main-feed-input-label"><label for="source-list">' . __( 'Products source', 'wp-product-feed-manager' ) . '</label> :</th>'; // hidden until we support more sources
			$html_code .= '<td>' . WPPFM_Feed_Form_Control::source_selector() . '</td></tr>';
			$html_code .= '<tr><th id="main-feed-input-label"><label for="merchant-list">' . __( 'Channel', 'wp-product-feed-manager' ) . '</label> :</th>';
			$html_code .= '<td>' . WPPFM_Feed_Form_Control::channel_selector() . '</td></tr>';
			$html_code .= '<tr id="country-list-row" style="display:none;"><th id="main-feed-input-label"><label for="country-list">' . __( 'Target Country', 'wp-product-feed-manager' ) . '</label> :</th>';
			$html_code .= '<td>' . WPPFM_Feed_Form_Control::country_selector() . '</td></tr>';
			$html_code .= '<tr id="category-list-row" style="display:none;"><th id="main-feed-input-label"><label for="categories-list">' . __( 'Default Category', 'wp-product-feed-manager' ) . '</label> :</th>';
			$html_code .= '<td>' . WPPFM_Feed_Form_Control::category_selector( "lvl", "-1", true ) . '</td></tr>';
			$html_code .= '<tr id="aggregator-selector-row" style="display:none;"><th id="main-feed-input-label"><label for="aggregator-selector">' . __( 'Aggregator Shop', 'wp-product-feed-manager' ) . '</label> :</th>';
			$html_code .= '<td>' . WPPFM_Feed_Form_Control::aggregation_selector() . '</td></tr>';
			$html_code .= '<tr id="add-product-variations-row" style="display:none;"><th id="main-feed-input-label"><label for="product-variations-selector">' . __( 'Include Product Variations', 'wp-product-feed-manager' ) . '</label> :</th>';
			$html_code .= '<td>' . WPPFM_Feed_Form_Control::product_variation_selector() . '</td></tr>';
			$html_code .= '<tr id="google-feed-title-row" style="display:none;"><th id="main-feed-input-label"><label for="google-feed-title-selector">' . __( 'Feed Title', 'wp-product-feed-manager' ) . '</label> :</th>';
			$html_code .= '<td>' . WPPFM_Feed_Form_Control::google_feed_title_selector() . '</td></tr>';
			$html_code .= '<tr id="google-feed-description-row" style="display:none;"><th id="main-feed-input-label"><label for="google-feed-description-selector">' . __( 'Feed Description', 'wp-product-feed-manager' ) . '</label> :</th>';
			$html_code .= '<td>' . WPPFM_Feed_Form_Control::google_feed_description_selector() . '</td></tr>';
			$html_code .= '<tr id="update-schedule-row" style="display:none;"><th id="main-feed-input-label"><label for="update-schedule">' . __( 'Update Schedule', 'wp-product-feed-manager' ) . '</label> :</th>';
			$html_code .= '<td>' . WPPFM_Feed_Form_Control::schedule_selector() . '</td></tr>';
			$html_code .= '</tbody>';
			$html_code .= '</table>';
			$html_code .= WPPFM_Feed_Form_Control::category_mapping_table();
			$html_code .= WPPFM_Feed_Form_Control::main_product_filter_wrapper();
			$html_code .= '<section class=master-feed-filter-wrapper></section>';
			$html_code .= '<div class="button-wrapper" id="page-center-buttons">';
			$html_code .= '<input class="button-primary" type="button" name="generate-top" value="' . __( 'Save & Generate Feed', 'wp-product-feed-manager' ) . '" id="wppfm-generate-feed-button-top" disabled />';
			$html_code .= '<input class="button-primary" type="button" name="save-top" value="' . __( 'Save Feed', 'wp-product-feed-manager' ) . '" id="wppfm-save-feed-button-top" disabled />';

			$html_code .= '</div>';
			$html_code .= '<div class="widget-content" id="fields-form" style="display:none;">';

			$html_code .= '<section id="attribute-map">';
			$html_code .= '<div class="header" id="fields-form-header"><h3>' . __( 'Attribute Mapping', 'wp-product-feed-manager' ) . ' :</h3></div>';
			$html_code .= '<div id="required-fields" style="display:initial;"><legend class="field-level"><h4>' . __( 'Required', 'wp-product-feed-manager' ) . ':</h4></legend>';
			$html_code .= $this->fieldFormTableTitles();
			$html_code .= '<div class="field-table" id="required-field-table"></div></div>';
			$html_code .= '<div id="highly-recommended-fields" style="display:none;"><legend class="field-level"><h4>' . __( 'Highly recommended', 'wp-product-feed-manager' ) . ':</h4></legend>';
			$html_code .= $this->fieldFormTableTitles();
			$html_code .= '<div class="field-table" id="highly-recommended-field-table"></div></div>';
			$html_code .= '<div id="recommended-fields" style="display:none;"><legend class="field-level"><h4>' . __( 'Recommended', 'wp-product-feed-manager' ) . ':</h4></legend>';
			$html_code .= $this->fieldFormTableTitles();
			$html_code .= '<div class="field-table" id="recommended-field-table"></div></div>';
			$html_code .= '<div id="optional-fields" style="display:initial;"><legend class="field-level"><h4>' . __( 'Optional', 'wp-product-feed-manager' ) . ':</h4></legend>';
			$html_code .= $this->fieldFormTableTitles();
			$html_code .= '<div class="field-table" id="optional-field-table"></div></div>';
			$html_code .= '<div id="custom-fields" style="display:initial;"><legend class="field-level"><h4>' . __( 'Custom attributes', 'wp-product-feed-manager' ) . ':</h4></legend>';
			$html_code .= $this->fieldFormTableTitles();
			$html_code .= '<div class="field-table" id="custom-field-table"></div></div>';
			$html_code .= '</section>';

			$html_code .= '<div class="button-wrapper" id="page-center-buttons">';
			$html_code .= '<input class="button-primary" type="button" name="generate-bottom" value="' . __( 'Save & Generate Feed', 'wp-product-feed-manager' ) . '" id="wppfm-generate-feed-button-bottom" disabled />';
			$html_code .= '<input class="button-primary" type="button" name="save-bottom" value="' . __( 'Save Feed', 'wp-product-feed-manager' ) . '" id="wppfm-save-feed-button-bottom" disabled />';
			$html_code .= '</div>';
			$html_code .= '</div>';

			return $html_code;
		}

		private function fieldFormTableTitles() {

			$html_code = '<div class="field-header-wrapper">';
			$html_code .= '<div class="field-header col20w">' . __( 'Add to feed', 'wp-product-feed-manager' ) . '</div>';
			$html_code .= '<div class="field-header col30w">' . __( 'From WooCommerce source', 'wp-product-feed-manager' ) . '</div>';
			$html_code .= '<div class="field-header col40w">' . __( 'Condition', 'wp-product-feed-manager' ) . '</div>';
			$html_code .= '<div class="end-row">&nbsp</div></div>';

			return $html_code;
		}

	}

	

	
    
// end of WPPFM_Feed_Form class

endif;
