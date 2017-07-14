<?php

/* * ******************************************************************
 * Version 4.4
 * Modified: 05-06-2017
 * Copyright 2017 Accentio. All rights reserved.
 * License: None
 * By: Michel Jongbloed
 * ****************************************************************** */

// Prevent direct access
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WPPFM_Google_Feed_Class' ) ) :

	/**
	 * The WPPFM_Google_Feed class contains the general feed functions and can be used to extend
	 * feed classes that generate feeds for a specific feed shop
	 * 
	 * @class WPPFM_Google_Feed_Class
	 * @version 4.4
	 */
	class WPPFM_Google_Feed_Class extends WPPFM_Feed_Master_Class {

		private $_version = '4.4';

		/**
		 * Class constructor
		 */
		public function __construct() {
			parent::__construct();
		}
		
		public function get_version() { return $this->_version; }

		public function get_file_text() { return $this->generate_file_text( '1', 'google_product_category', 'description', 'xml' ); }
		
		public function woocommerce_to_feed_fields() {
			$fields = new stdClass();

			// ALERT! Any changes made to this object also need to be done to the woocommerceToGoogleFields() function in the google-source.js file
			$fields->id							 = '_sku';
			$fields->title						 = 'post_title';
			$fields->google_product_category	 = 'category';
			$fields->description				 = 'post_content';
			$fields->link						 = 'permalink';
			$fields->image_link					 = 'attachment_url';
			$fields->additional_image_link		 = '_wp_attachement_metadata';
			$fields->price						 = '_regular_price';
			$fields->identifier_exists			 = 'Fill with a static value';
			$fields->sale_price					 = '_sale_price';
			$fields->sale_price_effective_date	 = '_sale_price_dates_from';
			$fields->item_group_id				 = 'item_group_id';
			
            // In accordance with the Google Feed Specifications update of september 2015
			$fields->tax						 = 'Use the settings in the Merchant Center';
			$fields->shipping					 = 'Use the settings in the Merchant Center';

			return $fields;
		}
		
		// overrides the set_feed_output_attribute_levels function in WPPFM_Feed_Master_Class
		// ALERT! This function is equivalent for the setGoogleOutputAttributeLevels() function in google-source.js
		public function set_feed_output_attribute_levels( &$main_data ) {
			$country = $main_data->country;
			
			for ( $i = 0; $i < count( $main_data->attributes ); $i++ ) {
				if ( $main_data->attributes[ $i ]->fieldLevel === '0' ) {
					switch ( $main_data->attributes[ $i ]->fieldName ) {
						case 'google_product_category':
							$main_data->attributes[ $i ]->fieldLevel = $this->google_needs_product_cat( $main_data->mainCategory ) === true ? 1 : 4;
							break;

						case 'is_bundle':
						case 'multipack':
							$main_data->attributes[ $i ]->fieldLevel = in_array( $country, $this->special_product_countries() ) ? 1 : 4;
							break;

						case 'brand':
							$main_data->attributes[ $i ]->fieldLevel = $this->google_requires_brand( $main_data->mainCategory ) === true ? 1 : 4;
							break;

						case 'item_group_id':
							$main_data->attributes[ $i ]->fieldLevel = in_array( $country, $this->special_clothing_group_countries() ) ? 1 : 4;
							break;

						case 'gender':
						case 'age_group':
						case 'color':
						case 'size':
							if ( in_array( $country, $this->special_clothing_group_countries() ) 
								&& $this->google_clothing_and_accessories( $main_data->mainCategory ) === true ) {
								$main_data->attributes[ $i ]->fieldLevel = 1;
							} else {
								$main_data->attributes[ $i ]->fieldLevel = 4;
							}

							break;
							
						case 'tax':
		                    // In accordance with the Google Feed Specifications update of september 2015
							$main_data->attributes[ $i ]->fieldLevel = $country === 'US' ? 1 : 4;
							break;
							
						case 'shipping':
		                    // In accordance with the Google Feed Specifications update of september 2015
							$main_data->attributes[ $i ]->fieldLevel = in_array( $country, $this->special_shipping_countries() ) ? 1 : 4;
							break;

						default:
							break;
					}

					$main_data->attributes[ $i ]->isActive = 
						$this->set_attribute_status( (int) $main_data->attributes[ $i ]->fieldLevel, 
						$main_data->attributes[ $i ]->value );
				}
			}
		}
		
		public function keys_that_have_sub_tags() { return array( 'installment', 'loyalty_points', 'shipping', 'tax' ); }
		
		public function add_xml_sub_tags( &$product ) {
			$google_sub_tag_keys = array(
				'installment-month',
				'installment-amount',
				'loyalty_points-name',
				'loyalty_points-pointsValue',
				'loyalty_points-ratio',
				'shipping-country',
				'shipping-region',
				'shipping-service',
				'shipping-price',
				'tax-country',
				'tax-region',
				'tax-rate',
				'tax-tax_ship'
			);
			
			if ( count( array_intersect_key( $product, array_flip( $google_sub_tag_keys ) ) ) > 0 ) {
				foreach ( $google_sub_tag_keys as $key ) {
					if ( array_key_exists( $key, $product ) ) {
						$split = explode( '-', $key );
					
						$string = array_key_exists( $split[0], $product ) ? $product[$split[0]] : '';
						
						$value = $product[$key];
						
						$string .= '<g:' . $split[1] . '>' . $value . '</g:' . $split[1] . '>';
					
						unset( $product[$key] );
						
						$product[$split[0]] = $string;
					}
				}
			}
			
			return $product;
		}

		// ALERT! This function is equivalent to the googleSpecialClothingGroupCountries() function in google-source.js
		private function special_clothing_group_countries() {
			return array( 'US', 'GB', 'DE', 'FR', 'JP', 'BR' ); // Brazil added based on the new Feed Specifications from september 2015
		}
		
		// ALERT! This function is equivalent to the googleSpecialShippingCountries() function in google-source.js
		private function special_shipping_countries() {
			return array( 'US', 'GB', 'DE', 'AU', 'FR', 'CH', 'CZ', 'NL', 'IT', 'ES', 'JP' );
		}
		
		// ALERT! This function is equivalent to the googleSpecialProductCountries() function in google-source.js
		private function special_product_countries() {
			return array( 'US', 'GB', 'DE', 'AU', 'FR', 'CH', 'CZ', 'NL', 'IT', 'ES', 'JP', 'BR' );
		}

		private function google_clothing_and_accessories( $category ) {
			return stristr( $category, 'Apparel & Accessories' ) !== false ? true : false;
		}
		
		private function google_needs_product_cat( $category ) {
			return stristr( $category, 'Apparel & Accessories' ) !== false 
				|| stristr( $category, 'Media' ) !== false 
				|| stristr( $category, 'Software' ) !== false ? true : false;
		}
		
		private function google_requires_brand( $category ) {
			return stristr( $category, 'Media' ) === false ? true : false;
		}
		
		protected function header( $title, $description = '' ) {
			// the check for convert_to_data_string function can be remove when all users have switched to plugin version 1.6 or higher
			$title_string = method_exists( $this, 'data_string' ) ? $this->data_string( $title ) 
				: $this->convert_to_character_data_string( $title );
			$home_link = method_exists( $this, 'data_string' ) ? $this->data_string( get_option( 'home' ) ) 
				: $this->convert_to_character_data_string( get_option( 'home' ) );
			$descr = '' !== $description ? $description : $title;
			$description_string = method_exists( $this, 'data_string' ) ? $this->data_string( $descr ) 
				: $this->convert_to_character_data_string( $descr );
			
			return '<?xml version="1.0"?>
					<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">
					<channel>
					<title>' . $title_string . '</title>
					<link>' . $home_link . '</link>
					<description>' . $description_string . '</description>';
		}
		
		protected function footer() { return '</channel></rss>'; }
	}
	
    // end of WPPFM_Google_Feed_Class

endif;
