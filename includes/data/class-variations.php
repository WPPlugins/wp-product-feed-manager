<?php

/* * ******************************************************************
 * Version 1.4
 * Modified: 26-05-2017
 * Copyright 2017 Accentio. All rights reserved.
 * License: None
 * By: Michel Jongbloed
 * ****************************************************************** */

// Prevent direct access
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WPPFM_Variations_Class' ) ) :

	/**
	 * The WPPFM_Variations Class contains the functions required to work with product variations
	 * 
	 * @class WPPFM_Variations
	 * @version dev
	 * @category class
	 */
	class WPPFM_Variations_Class {

		/**
		 * Takes the product data and fills its items with the correct variation data
		 * 
		 * @param type $product_data
		 * @param type $woocommerce_variation_data
		 * @param type $wpmr_variation_data
		 */
		public static function fill_product_data_with_variation_data( &$product_data, $woocommerce_variation_data, $wpmr_variation_data ) {
			
			$permalink = array_key_exists( 'permalink' , $product_data ) ? $product_data['permalink'] : ''; // some channels don't require permalinks
			$conversions = self::variation_conversion_table( $woocommerce_variation_data, $permalink );
			$third_party_attribute_keywords = explode( ', ', get_option( 'wppfm_third_party_attribute_keywords', '_wpmr_%, _cpf_%, _unit%' ) );
			
			foreach( $product_data as $key => $field_value ) {
				if ( in_array( $key, array_keys($conversions) ) && $field_value !== $conversions[ $key ] && $conversions[ $key ] ) {
					$product_data[$key] = $conversions[ $key ];
				}
				
				if ( array_key_exists( $key, $woocommerce_variation_data['attributes'] ) && $woocommerce_variation_data['attributes'][$key] ) {
					$product_data[$key] = $woocommerce_variation_data['attributes'][$key];
				}
				
				// process the wpmr variation data
				if ( $wpmr_variation_data && array_key_exists( $key, $wpmr_variation_data ) ) {
					$product_data[$key] = $wpmr_variation_data[$key];
				} else {
					foreach( $third_party_attribute_keywords as $keyword ) {
						$search_str = str_replace( '%', '*', $keyword ); // change sql wildcard % to norman wildcard *
						if ( fnmatch( $search_str, $key ) ) $product_data[$key] = '';
					}
				}
			}
		}
		
		private static function variation_conversion_table( $variation_data, $main_permalink ) {

			$variable_product = new WC_Product_Variation( $variation_data[ 'variation_id' ] );
			$permalink_value = self::variation_permalink_value_string( $main_permalink, $variable_product->get_variation_attributes() );

// 120517: The call to $variable_product functions is the prefered way to access variables but does not work on all servers. See issue #204
// TODO: When there is time, maybe investigate why this is not working on one website
//			return array(
//				'ID'						=> (string)$variation_data['variation_id'],
//				'_downloadable'				=> $variable_product->get_downloadable(),
//				'_virtual'					=> $variable_product->get_virtual(),
//				'_manage_stock'				=> $variable_product->get_manage_stock(),
//				'_stock'					=> $variable_product->get_stock_quantity(),
//				'_backorders'				=> $variable_product->get_backorders(),
//				'_stock_status'				=> $variable_product->get_stock_status(),
//				'_sku'						=> $variable_product->get_sku(),
//				'_weight'					=> $variable_product->get_weight(),
//				'_length'					=> $variable_product->get_length(),
//				'_width'					=> $variable_product->get_width(),
//				'_height'					=> $variable_product->get_height(),
//				'post_content'				=> $variable_product->get_description(),
//				'_regular_price'			=> prep_money_values( $variable_product->get_regular_price() ),
//				'_sale_price'				=> prep_money_values( $variable_product->get_sale_price() ),
//				'_sale_price_dates_from'	=> convert_price_date_to_feed_format( $variable_product->sale_price_dates_from ),
//				'_sale_price_dates_to'		=> convert_price_date_to_feed_format( $variable_product->sale_price_dates_to ),
//				'attachment_url'			=> wp_get_attachment_url( get_post_thumbnail_id( $variation_data[ 'variation_id' ] ) ),
//				'permalink'					=> $main_permalink . $permalink_value

			return array(
				'ID'						=> (string)$variation_data['variation_id'],
				'_downloadable'				=> $variable_product->downloadable,
				'_virtual'					=> $variable_product->virtual,
				'_manage_stock'				=> $variable_product->manage_stock,
				'_stock'					=> $variable_product->stock,
				'_backorders'				=> $variable_product->backorders,
				'_stock_status'				=> $variable_product->stock_status,
				'_sku'						=> $variable_product->sku,
				'_weight'					=> $variable_product->weight,
				'_length'					=> $variable_product->length,
				'_width'					=> $variable_product->width,
				'_height'					=> $variable_product->height,
				'post_content'				=> $variable_product->variation_description,
				'_regular_price'			=> prep_money_values( $variable_product->regular_price ),
				'_sale_price'				=> prep_money_values( $variable_product->sale_price ),
				'_sale_price_dates_from'	=> convert_price_date_to_feed_format( $variable_product->sale_price_dates_from ),
				'_sale_price_dates_to'		=> convert_price_date_to_feed_format( $variable_product->sale_price_dates_to ),
				'attachment_url'			=> wp_get_attachment_url( get_post_thumbnail_id( $variation_data[ 'variation_id' ] ) ),
				'permalink'					=> $main_permalink . $permalink_value
			);
		}
		
		private static function variation_permalink_value_string( $main_permalink, $attributes ) {
			
			$string = stripos( $main_permalink, '?' ) ? '&' : '?';
			
			foreach( $attributes as $attribute_name => $attribute_value ) {
				
//				$clean_attr_name = str_replace( 'attribute_pa_', '', $attribute_name );
				$clean_attr_name = str_replace( ' ', '+', $attribute_name );
				$string .= strtolower( $clean_attr_name ) . '=' . $attribute_value . '&';
			}
			
			return rtrim( $string, '&' );
		}

	}

	
     // End of WPPFM_Variations_Class

endif;	