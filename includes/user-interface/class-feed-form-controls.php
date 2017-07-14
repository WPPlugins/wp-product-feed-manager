<?php

/* * ******************************************************************
 * Version 1.2
 * Modified: 03-06-2017
 * Copyright 2017 Accentio. All rights reserved.
 * License: None
 * By: Michel Jongbloed
 * ****************************************************************** */

// Prevent direct access
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WPPFM_Feed_Form_Control' ) ) :

	/**
	 * A class that contains all controls for the feed form
	 */
	class WPPFM_Feed_Form_Control {
	
		public static function category_selector( $identifier, $id, $start_visible ) {
			$display = $start_visible ? 'initial' : 'none';
			$ident = $id !== '-1' ? $identifier . '-' . $id : $identifier;
			
			$html_code = '<div id="category-selector-' . $ident . '" style="display:' . $display . '">';
			$html_code .= '<div id="selected-categories"></div><select class="cat_select" id="' . $ident . '_0" disabled></select>';
			
			for ( $i = 1; $i < 8; $i++ ) { $html_code .= '<select class="cat_select" id="' . $ident . '_' . $i . '" style="display:none;"></select>'; }
			
			$html_code .= '<div>';
			
			return $html_code;
		}
		
		public static function feed_name_selector() {
			return '<input type="text" name="file-name" id="file-name" />';
		}
		
		public static function source_selector() {
			$data_class = new WPPFM_Data_Class ();
			$sources = $data_class->get_sources();

			if ( !empty( $sources ) ) {
				$html_code = '<select id="sources">';

				$html_code .= '<option value="0">' . __( 'Select your product source', 'wp-product-feed-manager' ) . '</option>';

				if ( count( $sources ) > 1 ) {
					foreach ( $sources as $source ) {
						$html_code .= '<option value="' . $source[ 'source_id' ] . '">' . $source[ 'name' ] . '</option>';
					}
				} else {
					$html_code .= '<option value="' . $sources[ 0 ][ 'source_id' ] . '" selected>' . $sources[ 0 ][ 'name' ] . '</option>';
				}
			
				$html_code .= '</select>';
			}

			return $html_code;
		}
		
		public static function channel_selector() {
			$data_class = new WPPFM_Data_Class ();
			$channels = $data_class->get_channels();

			if ( !empty( $channels ) ) {
				$html_code = '<div id="selected-merchant"></div>';
				$html_code .= '<select id="merchants" style="display:initial">';

				$html_code .= '<option value="0">' . __( '-- Select your merchant --', 'wp-product-feed-manager' ) . '</option>';

				foreach ( $channels as $channel ) { $html_code .= '<option value="' . $channel[ 'channel_id' ] . '">' . $channel[ 'name' ] . '</option>'; }
			
				$html_code .= '</select>';
			} else {
				$html_code = 'Sorry, but you first need to install a channel before you can add a feed. Open the Manage Channels page and install at least one channel.';
			}

			return $html_code;
		}
		
		public static function country_selector() {
			$data_class = new WPPFM_Data_Class ();
			$countries = $data_class->get_countries();

			if ( !empty( $countries ) ) {
				$html_code = '<select id="countries" disabled>';
				$html_code .= '<option value="0">' . __( '-- Select your target country --', 'wp-product-feed-manager' ) . '</option>';

				foreach ( $countries as $country ) { $html_code .= '<option value="' . $country[ 'name_short' ] . '">' . $country[ 'name' ] . '</option>'; }
			
				$html_code .= '</select>';
			}

			return $html_code;
		}
		
		public static function schedule_selector() {
			$html_code = '<span id="wppfm-update-day-wrapper" style="display:initial">' . __( 'Every ', 'wp-product-feed-manager' );
			$html_code .= '<input type="text" class="small-text" name="days-interval" id="days-interval" value="1" style="width:30px;" /> ' . __( 'day(s) at', 'wp-product-feed-manager' ) . '</span>';
			$html_code .= '<span id="wppfm-update-every-day-wrapper" style="display:none">' . __( 'Every day at', 'wp-product-feed-manager' ) . '</span>';
			$html_code .= ' <select id="update-schedule-hours" style="width:50px;">' . self::hour_list() . '</select>';
			$html_code .= '<select id="update-schedule-minutes" style="width:50px;">' . self::minutes_list() . '</select>';
			$html_code .= '<span id="wppfm-update-frequency-wrapper" style="display:initial">';
			$html_code .= __( ' for ', 'wp-product-feed-manager' );
			$html_code .= '<select id="update-schedule-frequency" style="width:50px;">' . self::frequency_list() . '</select>';
			$html_code .= __( ' time(s) a day', 'wp-product-feed-manager' );
			$html_code .= '</span>';
			
			return $html_code;
		}
		
		public static function category_mapping_table() {
			$shop_categories = WPPFM_Categories_Class::get_shop_categories_list();

			$html_code = '<section id="category-map" style="display:none;">';
			$html_code .= '<div id="category-mapping-header" class="header"><h3>' . __( 'Category Mapping', 'wp-product-feed-manager' ) . ':</h3></div>';
			$html_code .= '<table class="fm-category-mapping-table widefat" cellspacing="0" id="category-mapping-table">';
			$html_code .= '<thead class="fm-category-mapping-titels"><tr>';

			$html_code .= '<td id="shop-category-selector" class="manage-column column-cb check-column">';
			$html_code .= '<label class="screen-reader-text" for="categories-select-all">Select All</label>';
			$html_code .= '<input id="categories-select-all" type="checkbox"';
			$html_code .= '</td>';

			$html_code .= '<th scope="row" class="manage-column column-name col30w">' . __( 'Shop Category', 'wp-product-feed-manager' ) . '</th>';
			$html_code .= '<th scope="row" class="manage-column column-name col55w">' . __( 'Feed Category', 'wp-product-feed-manager' ) . '</th>';
			$html_code .= '<th scope="row" class="manage-column column-name col10w">' . __( 'Products', 'wp-product-feed-manager' ) . '</th>';
			$html_code .= '</tr></thead>';

			$html_code .= '<tbody id="wppfm-category-mapping-body">';
			$html_code .= self::category_rows( $shop_categories, 0 );
			$html_code .= '</tbody>';
			$html_code .= '</table>';

			$html_code .= '</section>';

			return $html_code;
		}
		
		public static function aggregation_selector() {
			return '<input type="checkbox" name="aggregator-selector" id="aggregator">';
		}
		
		public static function product_variation_selector() {
			return '<input type="checkbox" name="product-variations-selector" id="variations">';
		}
		
		public static function google_feed_title_selector() {
			return '<input type="text" name="google-feed-title-selector" id="google-feed-title-selector" placeholder="uses File Name if left empty..." />';
		}
		
		public static function google_feed_description_selector() {
			return '<input type="text" name="google-feed-description-selector" id="google-feed-description-selector" placeholder="uses File Name if left empty..." />';
		}
		
		public static function main_product_filter_wrapper() {
			$html_code = '<section class="main-product-filter-wrapper" style="display:none;">';
			$html_code .= '<div class="product-filter-condition-wrapper">';
			$html_code .= '</div>';
			$html_code .= '</section>';
			
			return $html_code;
		}

		private static function category_rows( $categories, $level ) {
			$html_code		 = '';
			$level_indicator = '';

			for ( $i = 0; $i < $level; $i++ ) { $level_indicator .= '— '; }
			
			if ( $categories ) {
				foreach ( $categories as $category ) {
					$category_children = self::get_sub_categories( $category );

					$html_code .= self::category_row_code( $category, $category_children, $level_indicator );

					if ( $category->children && count( (array) $category->children ) > 0 ) { $html_code .= self::category_rows( $category->children, $level + 1 ); }
				}
			} else {
				$html_code .= 'No shop categories found.';
			}

			return $html_code;
		}

		private static function category_row_code( $category, $category_children, $level_indicator ) {
			$html_code = '<tr id="category-' . $category->term_id . '"><th class="check-column" scope="row" id="shop-category-selector">';
			$html_code .= '<input class="category-mapping-selector" data-children="' . $category_children . '" id="feed-selector-' . $category->term_id;
			$html_code .= '" type="checkbox" value="' . $category->term_id . '" title="Select ' . $category->name . '">';
			$html_code .= '</th><td id="shop-category" class="col30w">';
			$html_code .= $level_indicator . $category->name;
			$html_code .= '</td><td class="field-header col55w"><div id="feed-category-' . $category->term_id . '"></div>';
			$html_code .= WPPFM_Feed_Form_Control::category_selector( "catmap", $category->term_id, false ) . '</td>';
			$html_code .= '<td class="category-count col10w">' . $category->category_count . '</td></tr>';

			return $html_code;
		}

		private static function get_sub_categories( $category ) {
			$arrayString = '';

			if ( $category->children && count( (array) $category->children ) ) {
				$arrayString .= '[';

				foreach ( $category->children as $child ) { $arrayString .= $child->term_id . ', ';	}

				$arrayString = substr( $arrayString, 0, -2 );
				$arrayString .= ']';
			}

			return $arrayString;
		}

		private static function hour_list() {
			$html_code = '<option value="00">00</option>';
			$html_code .= '<option value="01">01</option>';
			$html_code .= '<option value="02">02</option>';
			$html_code .= '<option value="03">03</option>';
			$html_code .= '<option value="04">04</option>';
			$html_code .= '<option value="05">05</option>';
			$html_code .= '<option value="06">06</option>';
			$html_code .= '<option value="07">07</option>';
			$html_code .= '<option value="08">08</option>';
			$html_code .= '<option value="09">09</option>';

			for ( $i = 10; $i < 24; $i ++ ) { $html_code .= '<option value="' . $i . '">' . $i . '</option>'; }

			return $html_code;
		}

		private static function minutes_list() {
			$html_code = '<option value="00">00</option>';
			$html_code .= '<option value="01">01</option>';
			$html_code .= '<option value="02">02</option>';
			$html_code .= '<option value="03">03</option>';
			$html_code .= '<option value="04">04</option>';
			$html_code .= '<option value="05">05</option>';
			$html_code .= '<option value="06">06</option>';
			$html_code .= '<option value="07">07</option>';
			$html_code .= '<option value="08">08</option>';
			$html_code .= '<option value="09">09</option>';

			for ( $i = 10; $i < 60; $i ++ ) { $html_code .= '<option value="' . $i . '">' . $i . '</option>'; }

			return $html_code;
		}
		
		private static function frequency_list() {
			$html_code = '<option value="1">1</option>';
			$html_code .= '<option value="2">2</option>';
			$html_code .= '<option value="4">4</option>';
			$html_code .= '<option value="6">6</option>';
			$html_code .= '<option value="8">8</option>';
			$html_code .= '<option value="12">12</option>';
			$html_code .= '<option value="24">24</option>';

			return $html_code;
		}
	}
	
    
// end of WPPFM_Feed_Form_Control class

endif;
	
