<?php

/* * ******************************************************************
 * Version 1.4
 * Modified: 13-05-2017
 * Copyright 2017 Accentio. All rights reserved.
 * License: None
 * By: Michel Jongbloed
 * ****************************************************************** */

// Prevent direct access
if ( !defined( 'ABSPATH' ) ) {
	echo 'Hi!  I\'m just a plugin, there\'s not much I can do when called directly.';
	exit;
}


if ( !class_exists( 'WPPFM_Feed_Value_Editors_Class' ) ) :

	/**
	 * The WPPFM_Feed_Queries_Class class contains the value editor functions
	 * 
	 * @class WPPFM_Feed_Value_Editors_Class
	 * @version dev
	 */
	class WPPFM_Feed_Value_Editors_Class {

		public function overwrite_value( $condition ) {

			return $condition[ 2 ];
		}

		public function replace_value( $condition, $current_value ) {

			return str_replace( $condition[ 2 ], $condition[ 3 ], $current_value );
		}
		
		public function convert_to_element( $element_name, $current_value ) {
			
			return "!sub:$element_name[2]|$current_value";
		}

		public function remove_value( $condition, $current_value ) {

			return str_replace( $condition[ 2 ], '', $current_value );
		}

		public function add_prefix_value( $condition, $current_value ) {

			return $condition[ 2 ] . $current_value;
		}

		public function add_suffix_value( $condition, $current_value ) {

			return $current_value . $condition[ 2 ];
		}

		public function recalculate_value( $condition, $current_value, $combination_string, $combined_data_elements ) {
			
			if ( ! $combination_string ) {
				
				$values = $this->make_recalculate_inputs($current_value, $condition[3]);
				$calculated_value = $this->recalculate( $condition[ 2 ], floatval( $values['main_val'] ), floatval( $values['sub_val'] ) );
				return $this->is_money_value($current_value) ? prep_money_values( $calculated_value ) : $calculated_value;
				
			} else {
				
				if ( count( $combined_data_elements ) > 1 ) {
				
					$combined_string_values = array();

					foreach( $combined_data_elements as $element ) {

						$values = $this->make_recalculate_inputs($element, $condition[3]);

						$reg_match = '/[0-9.,]/'; // only numbers and decimals

						$calculated_value = preg_match( $reg_match, $values['main_val'] ) && preg_match( $reg_match, $values['sub_val'] ) ? 
							$this->recalculate( $condition[ 2 ], floatval( $values['main_val'] ), floatval( $values['sub_val'] ) ) : $values['main_val'];

						$end_value = $this->is_money_value( $element ) ? prep_money_values( $calculated_value ) : $calculated_value;

						array_push( $combined_string_values, $end_value );
					}

					return $this->make_combined_result_string( $combined_string_values, $combination_string );
				} else {
					
					return '';
				}
			}
		}
		
		private function make_combined_result_string( $values, $combination_string ) {
			
			$separators = $this->combination_separators();
			$result_string = $values[0];

			$combinations = explode( '|', $combination_string );

			for ( $i = 1; $i < count( $combinations ); $i++ ) {

				$sep = explode( '#', $combinations[$i] );
				$result_string .= $separators[(int)$sep[0]];
				$result_string .= $values[$i];
			}

			return $result_string;
		}
		
		public function combination_separators() {
			
			return array( '', ' ', ', ','.', '; ', ':', '-', '/', '\\' ); // should correspond with wppfm_getCombinedSeparatorList()
		}
		
		private function make_recalculate_inputs( $current_value, $current_sub_value ) {
			if ( ! preg_match( '/[a-zA-Z]/', $current_value ) )  { // only remove the commas if the current value has no letters
				$main_value = $this->numberformat_parse( $current_value);
			} else {
				$main_value = $current_value;
			}

			$sub_value = $this->numberformat_parse( $current_sub_value );
			
			return array( 'main_val' => $main_value, 'sub_val' => $sub_value );
		}

		public function prep_meta_values( $meta_data ) {

			$result = $meta_data->meta_value;

// 130517
//			$special_price_keys = array(
//				'_max_variation_price',
//				'_max_variation_regular_price',
//				'_max_variation_sale_price',
//				'_min_variation_price',
//				'_min_variation_regular_price',
//				'_min_variation_sale_price',
//				'_regular_price',
//				'_sale_price' );

			if ( meta_key_is_money( $meta_data->meta_key ) ) { $result = prep_money_values( $result ); }

			return is_string( $result ) ? trim( $result ) : $result;
		}
		
		public function is_money_value( $value ) {

			$number_decimals = absint( get_option( 'woocommerce_price_num_decimals', 2 ) );
			$decimal_point = get_option( 'woocommerce_price_decimal_sep' );

			$check = strripos( (string)$value, $decimal_point, -1 );
			
			return $check === $number_decimals ? true : false;
		}

		private function recalculate( $math, $main_value, $sub_value ) {
			$result = 0;

			if ( is_numeric( $main_value ) && is_numeric( $sub_value )) {

				switch ( $math ) {

					case 'add':

						$result = $main_value + $sub_value;
						break;

					case 'substract':

						$result = $main_value - $sub_value;
						break;

					case 'multiply':

						$result = $main_value * $sub_value;
						break;

					case 'divide':

						$result = $sub_value !== 0 ? $main_value / $sub_value : 0;
						break;
				}
			}

			return $result;
		}
		
		/**
		 * Converts any number string to a string with a number that has no thousands separator 
		 * and a period as decimal separator
		 * 
		 * @param string $number_string
		 * @return string
		 */
		private function numberformat_parse( $number_string ) {
			$decimal_point = get_option( 'woocommerce_price_decimal_sep' );
			$thousand_separator = get_option( 'woocommerce_price_thousand_sep' );
			
			$no_thousands_sep = str_replace( $thousand_separator, '', $number_string );
			return $decimal_point !== '.' ? str_replace( $decimal_point, '.', $no_thousands_sep ) : $no_thousands_sep;
		}

	}

	
    
	// End of WPPFM_Feed_Value_Editors_Class class

endif;