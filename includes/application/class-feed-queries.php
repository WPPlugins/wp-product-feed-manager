<?php

/* * ******************************************************************
 * Version 2.0
 * Modified: 06-02-2017
 * Copyright 2017 Accentio. All rights reserved.
 * License: None
 * By: Michel Jongbloed
 * ****************************************************************** */

// Prevent direct access
if ( !defined( 'ABSPATH' ) ) {
	echo 'Hi!  I\'m just a plugin, there\'s not much I can do when called directly.';
	exit;
}


if ( !class_exists( 'WPPFM_Feed_Queries_Class' ) ) :

	/**
	 * The WPPFM_Feed_Queries_Class class contains the query functions
	 * 
	 * @class WPPFM_Feed_Queries_Class
	 * @version dev
	 */
	class WPPFM_Feed_Queries_Class {

		public function includes_query( $query, $value ) {

			return strpos( strtolower( $value ), strtolower( trim( $query[ 3 ] ) ) ) !== false ? false : true;
		}

		public function does_not_include_query( $query, $value ) {

			return strpos( strtolower( $value ), strtolower( trim( $query[ 3 ] ) ) ) === false ? false : true;
		}

		public function is_equal_to_query( $query, $value ) {

			return strtolower( $value ) === strtolower( trim( $query[ 3 ] ) ) ? false : true;
		}

		public function is_not_equal_to_query( $query, $value ) {

			return strtolower( $value ) !== strtolower( trim( $query[ 3 ] ) ) ? false : true;
		}

		public function is_empty( $value ) {

			$trimValue = trim( $value );
			return empty( $trimValue ) ? false : true;
		}

		public function is_not_empty_query( $value ) {

			$trimValue = trim( $value );
			return !empty( $trimValue ) ? false : true;
		}

		public function starts_with_query( $query, $value ) {

			if ( !empty( $value ) && strrpos( strtolower( $value ), strtolower( trim( $query[ 3 ] ) ), -strlen( $value ) ) !== false ) {

				return false;
			} else {

				return true;
			}
		}

		public function does_not_start_with_query( $query, $value ) {

			if ( empty( $value ) || strrpos( strtolower( $value ), strtolower( trim( $query[ 3 ] ) ), -strlen( $value ) ) === false ) {

				return false;
			} else {

				return true;
			}
		}

		public function ends_with_query( $query, $value ) {

			$search_string = trim( $query[ 3 ] );

			if ( !empty( $value ) && ( $temp = strlen( $value ) - strlen( $search_string ) ) >= 0 && strpos( $value, $search_string, $temp ) !== false ) {

				return false;
			} else {

				return true;
			}
		}

		public function does_not_end_with_query( $query, $value ) {

			$search_string = trim( $query[ 3 ] );

			if ( !empty( $value ) && ( $temp = strlen( $value ) - strlen( $search_string ) ) >= 0 && strpos( $value, $search_string, $temp ) !== false ) {

				return true;
			} else {

				return false;
			}
		}

		public function is_greater_than_query( $query, $value ) {

			$data_nr		 = $this->convert_to_us_notation( trim( $value ) );
			$condition_nr	 = $this->convert_to_us_notation( trim( $query[ 3 ] ) );

			if ( is_numeric( $data_nr ) && is_numeric( $condition_nr ) ) {

				return (float) $data_nr > (float) $condition_nr ? false : true;
			} else {
				
				return false;
			}
		}
		
		public function is_greater_or_equal_to_query( $query, $value ) {

			$data_nr		 = $this->convert_to_us_notation( trim( $value ) );
			$condition_nr	 = $this->convert_to_us_notation( trim( $query[ 3 ] ) );

			if ( is_numeric( $data_nr ) && is_numeric( trim( $condition_nr ) ) ) {

				return (float) $data_nr >= (float) $condition_nr ? false : true;
			} else {

				return false;
			}
		}

		public function is_smaller_than_query( $query, $value ) {

			$data_nr		 = $this->convert_to_us_notation( trim( $value ) );
			$condition_nr	 = $this->convert_to_us_notation( trim( $query[ 3 ] ) );
			
			if ( is_numeric( $data_nr ) && is_numeric( $condition_nr ) ) {

				return (float) $data_nr < (float) $condition_nr ? false : true;
			} else {

				return false;
			}
		}

		public function is_smaller_or_equal_to_query( $query, $value ) {

			$data_nr		 = $this->convert_to_us_notation( trim( $value ) );
			$condition_nr	 = $this->convert_to_us_notation( trim( $query[ 3 ] ) );

			if ( is_numeric( $data_nr ) && is_numeric( $condition_nr ) ) {

				return (float) $data_nr <= (float) $condition_nr ? false : true;
			} else {

				return false;
			}
		}

		public function is_between_query( $query, $value ) {

			$data_nr			 = $this->convert_to_us_notation( trim( $value ) );
			$condition_nr_low	 = $this->convert_to_us_notation( trim( $query[ 3 ] ) );
			$condition_nr_high	 = $this->convert_to_us_notation( trim( $query[ 5 ] ) );

			if ( is_numeric( $data_nr ) && is_numeric( $condition_nr_low ) && is_numeric( $condition_nr_high ) ) {

				if ( (float) $data_nr > (float) $condition_nr_low && (float) $data_nr < (float) $condition_nr_high ) {

					return false;
				} else {

					return true;
				}
			} else {

				return false;
			}
		}
		
		private function convert_to_us_notation( $current_value ) {
			$decimal_sep = get_option( 'woocommerce_price_decimal_sep' );
			$thousands_sep = get_option( 'woocommerce_price_thousand_sep' );
			
			if ( !preg_match( '/[a-zA-Z]/', $current_value ) )  { // only remove the commas if the current value has no letters
				$no_thousands_sep = str_replace( $thousands_sep, '', $current_value);
				return $decimal_sep === ',' ? str_replace( ',', '.', $no_thousands_sep) : $no_thousands_sep;
			} else {
				return $current_value;
			}
		}
	}

	
	
    // end of WPPFM_Feed_Queries_Class

endif;
