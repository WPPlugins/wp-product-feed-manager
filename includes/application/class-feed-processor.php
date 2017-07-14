<?php

/* * ******************************************************************
 * Version 5.3
 * Modified: 03-06-2017
 * Copyright 2017 Accentio. All rights reserved.
 * License: None
 * By: Michel Jongbloed
 * ****************************************************************** */

// Prevent direct access
if ( !defined( 'ABSPATH' ) ) {
	echo 'Hi!  I\'m just a plugin, there\'s not much I can do when called directly.';
	exit;
}


if ( !class_exists( 'WPPFM_Feed_Processor_Class' ) ) :

	/**
	 * The WPPFM_Feed_Processor_Class class contains the feed functions that process the form data to feed data
	 * 
	 * @class WPPFM_Feed_Processor_Class
	 * @version dev
	 */
	class WPPFM_Feed_Processor_Class {

		protected $_product_counter;
		protected $_product_filtered;
		protected $_support_class;
		protected $_ids_in_feed;
		protected $_nr_thats_selected;

		public function __construct() {

			$this->_support_class = new WPPFM_Feed_Support_Class();
			$this->_ids_in_feed = array();
		}
		
		public function generate_product_data( $product, $include_variations, $active_fields, $meta_data, $feed_filter,
			$field_relation_table, $main_category_field_title, $main_category, $category_mapping, $fm_lim, $file_extention ) {

			$data_class = new WPPFM_Data_Class ();

			$feed_products_array = array();
			$fm_lim .= 'm_lic_s';
			
			// get the woocommerce specific product data
			$prdct = wc_get_product( $product->ID );
			
			// make sure its a valid woocommerce product
			if ( $prdct instanceof WC_Product_Simple || $prdct instanceof WC_Product_Variable 
				|| $prdct instanceof WC_Product_External || $prdct instanceof WC_Product_Grouped ) {

				// include variations when applicable
				if ( $include_variations === '1' && $prdct !== null && $prdct->is_type( 'variable' ) ) {

					$vars = $prdct->get_available_variations(); // get the woocommerce variations data

					foreach ($vars as $var) {

						if ( $var[ 'variation_is_active' ] && ! in_array( $var[ 'variation_id' ], $this->_ids_in_feed ) ) {

							$wpmr_variation_attributes = $data_class->get_own_variation_data( $var[ 'variation_id' ] );

							$feed_product_object = $this->process_product( $product, $var, $wpmr_variation_attributes, $active_fields, 
								$meta_data, $feed_filter, $field_relation_table, $main_category_field_title, $main_category, $category_mapping, $file_extention );

							if ( $feed_product_object ) {

								$reg_id = $var['variation_id'] ? $var['variation_id'] : $prdct->id; 

								// and push the feed_object in the feed_data_array
								array_push( $feed_products_array, $feed_product_object );
								array_push( $this->_ids_in_feed, $reg_id );
							}
						}
					}

				} elseif ( $prdct && ! $prdct->is_type( 'variation' ) ) { // do not include the variations of this product

					// prevent doubles in the feed
					if ( ! in_array( $product->ID, $this->_ids_in_feed ) ) {

						$feed_product_object = $this->process_product( $product, null, null, $active_fields, $meta_data, $feed_filter, 
							$field_relation_table, $main_category_field_title, $main_category, $category_mapping, $file_extention );

						if ( $feed_product_object ) {
							// register this product as handled
							array_push( $this->_ids_in_feed, $product->ID );
							array_push( $feed_products_array, $feed_product_object );
						}
					}
				}
			}

			return $feed_products_array;
		}

		/**
		 * Returns an array containing the data required to make the xml text.
		 * 
		 * This array contains one sub-array for each product and the sub-array contains a key->value combination
		 * where the key is the title of the product data that should be placed in the feed, and the data contains
		 * the data belonging to that key.
		 * 
		 * The output of this function grabs the input data and calculates the output based on filters, static data,
		 * adviced data, edit values, alternative sources and combined sources that the user has set in the feed form.
		 * 
		 * @param {int} $feed_id
		 * @param {array} $active_fields contains all active fields
		 * @param {array} $meta_data
		 * @param {array} $field_relation_table contains the relation between a feed item title and its database column
		 * @param {array} $data
		 * @param {string} $main_category_field_title each channel uses an other title to store the category. This parm contains that title
		 * @param {string} $main_category
		 * @param {array} $category_mapping
		 * @return array with the data required to make the xml text
		 */
// obsolete as of version 1.6.0
//		public function generate_feed_data( $feed_id, $include_variations, $active_fields, $meta_data, $field_relation_table, $feed_filter, $product,
//									  $main_category_field_title, $main_category, $category_mapping ) {
//
//			$data_class = new WPPFM_Data_Class ();
//
//			// the feed_data_array is the placeholder for the complete feed data containing all the rows with fields
//			// that should be placed in the xml text
//			$feed_products_array	 = array();
//			$feed_product_object	 = array();
//			$this->_product_counter	 = 0;
//			$data_class->set_nr_of_feed_products( $feed_id, -1 );
//
//			// process each product
//			foreach ( $data as $product ) {
//
//				$prdct = wc_get_product($product[ 'ID' ]);
//
//				// include variations
//				if ( $include_variations === '1' && $prdct->product_type === 'variable' ) {
//
//					$vars = $prdct->get_available_variations(); // get the woocommerce variations data
//					
//					foreach ($vars as $var) {
//
//						if ( $var[ 'variation_is_active' ] && ! in_array( $var[ 'variation_id' ], $this->_ids_in_feed ) ) {
//							
//							$wpmr_variation_attributes = $data_class->get_own_variation_data( $var[ 'variation_id' ] );
//
//							$feed_product_object = $this->process_product( $product, $var, $wpmr_variation_attributes, $active_fields, $meta_data, $feed_filter, $field_relation_table, $main_category_field_title, $main_category, $category_mapping );
//							
//							if ( $feed_product_object ) {
//								
//								$reg_id = $var['variation_id'] ? $var['variation_id'] : $prdct->id; 
//								
//								// and push the feed_object in the feed_data_array
//								array_push( $feed_products_array, $feed_product_object );
//								array_push( $this->_ids_in_feed, $reg_id );
//							}
//						}
//					}
//				} elseif ( $prdct->product_type !== 'variation' ) { // do not include the variations of this product
//					
//					$var = null;
//					
//					// prevent doubles in the feed
//					if ( ! in_array( $product['ID'], $this->_ids_in_feed ) ) {
//						
//						$wpmr_variation_attributes = null;
//
//						$feed_product_object = $this->process_product( $product, $var, $wpmr_variation_attributes, $active_fields, $meta_data, $feed_filter, $field_relation_table, $main_category_field_title, $main_category, $category_mapping );
//						
//						if ( $feed_product_object ) {
//							// and push the feed_object in the feed_data_array
//							array_push( $feed_products_array, $feed_product_object );
//							array_push( $this->_ids_in_feed, $product['ID'] );
//						}
//					}
//				}
//
//			}
//
//			$data_class->set_nr_of_feed_products( $feed_id, $this->_product_counter );
//
//			return $feed_products_array;
//		}

		/**
		 * Processes a data row
		 * 
		 * Returns an key=>value array with all fields and data of one specific product
		 */
		private function process_product( $product_object, $product_variation_data, $wpmr_variation_data, $active_fields, $meta_data, $feed_filter, $field_relation_table,
									$main_category_feed_title, $main_category, $category_mapping, $file_extention ) {
			
			// the product variable is the placeholder for one row in the feed_data_array
			$product = array();
			$product_data = (array)$product_object;
			$product_parent_id = $product_data['ID'];
			
			if ( $product_variation_data || $wpmr_variation_data ) {
				// get correct variation data
				WPPFM_Variations_Class::fill_product_data_with_variation_data( $product_data, $product_variation_data, $wpmr_variation_data );
			}
			
			$row_category	 = $this->get_mapped_category( $product_parent_id, $main_category, $category_mapping );
			$row_filtered	 = $this->is_product_filtered( $feed_filter, $product_data );
			
			// only process the product if its category has been selected to be included in the feed and is not filtered out
			if ( $row_category && ! $row_filtered ) { // $row_category is false when the shop category for this row has not been selected
				
				// for each row loop through each field
				foreach ( $active_fields as $field ) {
					
					$field_meta_data = $this->get_meta_data_from_specific_field( $field, $meta_data );

					// get the field data based on the user settings
					$feed_object = $this->process_product_field( $product_data, $field_meta_data, $field_relation_table, $main_category_feed_title, $row_category );
					
					$key = key( $feed_object );

					// for an xml file only add fields that contain data
					if ( !empty( $feed_object[ $key ] ) || $file_extention !== 'xml' ) {
						$product[ $key ] = $feed_object[ $key ];
					}
				}

				// count the products that are active for the feed
				$this->_product_counter++;
			} elseif ( $row_filtered ) {
				$this->_product_filtered++;
				$product = false;
			} elseif ( ! $row_category ) {
				echo wppfm_show_wp_error( sprintf( __( "Could not identify the correct categorymap for the product with ID: %s! 
					Please check the category settings of this product.", 'wp-product-feed-manager' ), $product_data['ID'] ) );
				$product = false;
			}

			return $product;
		}

		private function is_product_filtered( $feed_filter_strings, $product_data ) {
			
			if ( $feed_filter_strings ) {
				
				return $this->filter_result( json_decode( $feed_filter_strings[0]['meta_value'] ), $product_data ) ? true : false;
			} else {
				
				return false;
			}
		}

		private function get_meta_data_from_specific_field( $field, $meta_data ) {

			$i = 0;

			while ( true ) {

				if ( $meta_data[ $i ]->fieldName !== $field ) {
					$i++;
					if ( $i>1000 ) { break; }
				} else {
					return $meta_data[ $i ];
				}
			}

			return false;
		}

		/**
		 * Generate the value of a field based on what the user has selected in filters, combined data, static data eg.
		 * 
		 * Returns an key=>value array of a specific product field where the key contains the field name and the value the field value
		 */
		private function process_product_field( $product_data, $field_meta_data, $field_relation_table,
										  $main_category_feed_title, $row_category ) {
			
			$product_object[ $field_meta_data->fieldName ] = $this->get_correct_field_value( $field_meta_data, $product_data, $field_relation_table, $main_category_feed_title, $row_category );
			
			return $product_object;
		}

		/**
		 * Processes a single field of a single product in the feed
		 * 
		 */
		private function get_correct_field_value( $field_meta_data, $product_data, $field_relation_table,
													 $main_category_feed_title, $row_category ) {
			
			$end_row_value = '';
			$this->_nr_thats_selected = 0;

			// do not process category strings, but only fields that are requested
			if ( key_exists( 'fieldName', $field_meta_data ) && $field_meta_data->fieldName !== $main_category_feed_title 
				&& $this->meta_data_contains_category_data( $field_meta_data ) === false ) {

				$value_object = key_exists( 'value', $field_meta_data ) && $field_meta_data->value !== '' ? json_decode( $field_meta_data->value ) : new stdClass();

				if ( key_exists( 'value', $field_meta_data ) && $field_meta_data->value !== '' && key_exists( 'm', $value_object ) ) { // seems to be something we need to work on
					$advised_source = key_exists( 'advisedSource', $field_meta_data ) ? $field_meta_data->advisedSource : '';

					// get the end value depending on the filter settings
					$end_row_value = $this->get_correct_end_row_value( $value_object->m, $product_data, $advised_source );
					
				} else { // no queries, edit valies or alternative sources for this field
					
					if ( property_exists( $field_meta_data, 'advisedSource' ) && $field_meta_data->advisedSource !== '' ) {

						$db_title = $field_meta_data->advisedSource;
					} else {

						$source_title	 = key_exists( 'fieldName', $field_meta_data ) ? $field_meta_data->fieldName : '';
						$db_title		 = $this->_support_class->find_relation( $source_title, $field_relation_table );
					}

					$end_row_value = array_key_exists( $db_title, $product_data ) ? $product_data[ $db_title ] : '';
				}

				// change value if requested
				if ( key_exists( 'value', $field_meta_data ) && $field_meta_data->value !== '' && key_exists( 'v', $value_object ) ) {
					
					$pos = $this->_nr_thats_selected;

					if ( key_exists( 'm', $value_object ) && key_exists( 's', $value_object->m[$pos] ) ) {
						$combination_string = key_exists( 'f', $value_object->m[$pos]->s ) ? $value_object->m[$pos]->s->f : false;
						$is_money = key_exists( 'source', $value_object->m[$pos]->s ) ?	meta_key_is_money( $value_object->m[$pos]->s->source ) : false;
					} else {
						$combination_string = false;
						$is_money = false;
					}

					$row_value = $this->get_edited_end_row_value( $value_object->v, $end_row_value, $product_data, $combination_string );
					$end_row_value = !$is_money ? $row_value : prep_money_values( $row_value );
				}
			} else {
				$end_row_value = $row_category;
			}

			return $end_row_value;
		}
		
		private function meta_data_contains_category_data( $meta_data ) {
			if( !key_exists( 'value', $meta_data ) || empty( $meta_data->value ) ) return false;
			
			$meta_obj = json_decode( $meta_data->value );
			return property_exists( $meta_obj, 't' ) ? true : false;
		}

		private function get_correct_end_row_value( $value, $product_data, $advised_source ) {

			$end_row_value = '';
			
			// De meta data is als volgt opgeslagen:
			// 
			// {
			//    "m":[{"s":{"source":"combined","f":"_min_variation_price|1#static#GBP"},
			//    "c":[{"1":"0#_min_variation_price#5"}]},{"s":{"source":"_regular_price"}}], 
			//    "v":[{"1":"change#recalculate#multiply#0,9"}]
			// }
			// 
			// Wat nu nog fout gaat is dat als het filter waar is, de combined value niet goed gaat.
			
			// Hier gaat iets nog niet goed met de telling van de _nr_thats_selected variabele

			foreach ( $value as $filter ) {
				
				$val = json_encode($filter);

				if ( $this->get_filter_status( $filter, $product_data ) === true && $end_row_value === '' ) {

					$end_row_value = $this->get_row_source_data( $filter, $product_data, $advised_source );
					break;
				} else {
					
					$this->_nr_thats_selected++;
				}
			}

			// not found a condition that was correct so lets take the "for all other products" data to fetch the correct row_value
			if ( $end_row_value === '' ) {

				$end_row_value = $this->get_row_source_data( end( $value ), $product_data, $advised_source );
			}

			return $end_row_value;
		}

		private function get_row_source_data( $filter, $product_data, $advised_source ) {

			$row_source_data = '';

			if ( key_exists( 's', $filter ) ) {

				if ( key_exists( 'static', $filter->s ) ) {

					$row_source_data = $filter->s->static;
				} elseif ( key_exists( 'source', $filter->s ) ) {

					if ( $filter->s->source !== 'combined' ) {

						$row_source_data = array_key_exists( $filter->s->source, $product_data ) ? $product_data[ $filter->s->source ] : '';
					} else {

						$row_source_data = $this->generate_combined_string( $filter->s->f, $product_data );
					}
				}
			} else {

				// return the advised source data
				if ( $advised_source !== '' ) {

					$row_source_data = array_key_exists( $advised_source, $product_data ) ? $product_data[ $advised_source ] : '';
				}
			}

			return $row_source_data;
		}

		private function get_filter_status( $filter, $product_data ) {

			if ( key_exists( 'c', $filter ) ) {

				// check if the query is true for this field
				return $this->filter_result( $filter->c, $product_data );
			} else {

				// apperently there is no condition so the result is always true
				return true;
			}
		}

		private function get_edited_end_row_value( $change_parameters, $origional_output, $product_data, $combination_string ) {
			
			$result_is_filtered = false;
			$y = 0;

			for ( $i = 0; $i < ( count( $change_parameters ) - 1 ); $i++ ) {

				if ( key_exists( 'q', $change_parameters[ $i ] ) ) {

					$filter_result = $this->filter_result( $change_parameters[ $i ]->q, $product_data );

					if ( $filter_result === true ) {

						$combined_data_elements = $combination_string ? $this->get_combined_elements( $product_data, $combination_string ) : '';
						$final_output = $this->_support_class->edit_value( $origional_output, $change_parameters[ $i ]->{$i + 1}, $combination_string, $combined_data_elements );

						$result_is_filtered = true;
					}
				}

				$y++;
			}
			
			if ( $result_is_filtered === false ) {
				$combined_data_elements = $combination_string ? $this->get_combined_elements( $product_data, $combination_string ) : '';
				$final_output = $this->_support_class->edit_value( $origional_output, $change_parameters[ $y ]->{$y + 1}, $combination_string, $combined_data_elements );
			}

			return $final_output;
		}
		
		private function get_combined_elements( $product_data, $combination_string ) {
			
			$result = array();
			$found_all_data = true;
			
			$combination_elements = explode( '|', $combination_string );
			
			if ( false === strpos( $combination_elements[0], 'static#' ) ) {
				if ( array_key_exists( $combination_elements[0], $product_data ) ) {
					array_push( $result, $product_data[$combination_elements[0]] );
				} else {
					$found_all_data = false;
				}
			} else {
				$element = explode( '#', $combination_elements[0] );
				array_push( $result, $element[1] );
			}
			
			for ( $i = 1; $i <= count($combination_elements) - 1; $i++ ) {
				
				$pos = strpos( $combination_elements[$i], '#');
				$selector = substr( $combination_elements[$i], ($pos !== false ? $pos + 1 : 0 ));
				
				if ( substr( $selector, 0, 7 ) === 'static#' ) {

					$selector = explode( '#', $selector );
					array_push( $result, $selector[1] );
				} elseif ( array_key_exists( $selector, $product_data ) ) {

					array_push( $result, $product_data[$selector] );
				} else {

					//array_push( $result, $selector );
					$found_all_data = false;
				}
			}
			
			return $found_all_data ? $result : '';
		}

		private function get_mapped_category( $id, $main_category, $category_mapping ) {

			$yoast_primary_category = WPPFM_Categories_Class::get_yoast_primary_cat( $id );
			$yoast_cat_is_selected = $yoast_primary_category ? $this->_support_class->category_is_selected( $yoast_primary_category[0]->term_id, $category_mapping ) : false;
			
			$product_categories	= $yoast_primary_category && false !== $yoast_cat_is_selected ? $yoast_primary_category :
				wp_get_post_terms( $id, 'product_cat', array( 'taxonomy' => 'product_cat' ) ); // get the categories from a specific product in the shop
			
			if ( $product_categories && ! is_wp_error( $product_categories ) ) {

				// loop through each category
				foreach ( $product_categories as $category ) {

					// check if this category is selected in the category mapping
					$shop_category_id = $this->_support_class->category_is_selected( $category->term_id, $category_mapping );
					
					// only add this product when at least one of the categories is selected in the category mapping
					if ( $shop_category_id !== false ) {
			
// 010517
//						if ( $yoast_cat_is_selected ) {
//							$prim_cat_name = $yoast_primary_category[0]->name;
//							$curr_cat_name = $category->name;
//							$prim_cat_id = $yoast_primary_category[0]->term_id;
//							$feed_cats = $category_mapping[ $shop_category_id ]->feedCategories;
//							$debug_string = "Of product $id the primary category is $prim_cat_name ($prim_cat_id) and should be equal to the current product_category ($curr_cat_name). The cat_is_selected output is $yoast_cat_is_selected and the feed categories are $feed_cats";
//						}

						switch ( $category_mapping[ $shop_category_id ]->feedCategories ) {

							case 'wp_mainCategory':
								return $main_category;

							case 'wp_ownCategory':
								return WPPFM_Categories_Class::get_shop_categories($id, ' > ');
								
								// 080117
								//return WPPFM_Categories_Class::make_shop_category_string_from_selected_category( $product_categories, $category->term_id, '' );

							default:
								return $category_mapping[ $shop_category_id ]->feedCategories;
						}
					}
				}
			} else {
				
				return false;
			}
		}

		private function generate_combined_string( $combined_sources, $row ) {
			
			$source_selectors_array = explode( '|', $combined_sources ); //split the combined source string in an array containing every single source
			$values_class = new WPPFM_Feed_Value_Editors_Class();
			$separators = $values_class->combination_separators(); // array with all possible separators
			
			// if one of the row results is an array, the final output needs to be an array
			$result_is_array = $this->check_if_any_source_has_array_data( $source_selectors_array, $row );
			$result = $result_is_array ? array() : '';
			
			if ( ! $result_is_array ) {

				$result = $this->make_combined_string( $source_selectors_array, $separators, $row, false );
			} else {
				
				for( $i = 0; $i < count( $result_is_array ); $i++ ) {

					$combined_string = $this->make_combined_string( $source_selectors_array, $separators, $row, $i );
					array_push( $result, $combined_string );				
				}
			}
			
			return $result;
		}
		
		private function make_combined_string( $source_selectors_array, $separators, $row, $array_pos ) {

			$combined_string = '';
			
			foreach ( $source_selectors_array as $source ) {
				
				$split_source = explode( '#', $source );
				
				// get the separator
				$separators_id = count( $split_source ) > 1 && $split_source[ 0 ] !== 'static' ? $split_source[ 0 ] : 0;
				$sep = $separators[ $separators_id ];
				
				$data_key = count( $split_source ) > 1 && $split_source[ 0 ] !== 'static' ? $split_source[ 1 ] : $split_source[ 0 ] ;

				if ( ( array_key_exists( $data_key, $row ) && $row[ $data_key ] ) || $data_key === 'static' ) {

					if ( $data_key !== 'static' && ! is_array( $row[ $data_key ] ) ) { // not static and no array

						$combined_string .= $sep;
						$combined_string .= $data_key !== 'static' ? $row[ $data_key ] : $split_source[ 2 ];
					} elseif ( $data_key === 'static' ) { // static inputs
						
						$static_string = count( $split_source ) > 2 ? $split_source[ 2 ] : $split_source[ 1 ];
						$combined_string .= $sep . $static_string;
					} else { // array inputs
						
						$input_array = $row[ $data_key ][$array_pos];
						$combined_string .= $sep . $input_array;
					}
				}
			}
			
			return $combined_string;
		}

		/**
		 * Distracts the keys from the $sources string (separated by a #) and looks if any of these keys
		 * are linked to an array in the $data_row
		 * 
		 * @param string $sources
		 * @param array $data_row
		 * @return false or an array from the data_row
		 */
		private function check_if_any_source_has_array_data( $sources, $data_row ) {
			
			foreach( $sources as $source ) {
				
				$split_source = explode( '#', $source );
				
				if ( count( $split_source ) > 1 && $split_source[1] === 'static' ) {
					$last_key = 'static';
				} elseif ( $split_source[0] === 'static' ) {
					$last_key = 'static';
				} else {
					$last_key = array_pop( $split_source );
				}
				
				if( array_key_exists($last_key, $data_row) && is_array( $data_row[ $last_key ] ) ) { return $data_row[ $last_key ]; } 
			}
			
			return false;
		}
		
		private function filter_result( $conditions, $product_data ) {

			$query_results = array();
			
			// run each query on the data
			foreach ( $conditions as $condition ) {

				$condition_string = $this->_support_class->get_query_string_from_query_object( $condition );

				$query_split = explode( '#', $condition_string );

				$row_result = $this->_support_class->check_query_result_on_specific_row( $query_split, $product_data ) === true ? 'false' : 'true';
				
				array_push( $query_results, $query_split[ 0 ] . '#' . $row_result );
			}

			// return the final filter result, based on the specific results
			return $this->connect_query_results( $query_results );
		}

		/**
		 * Recieves an array with condition results and generates a single end result based on the "and" or "or"
		 * connetion between the conditions
		 * 
		 * @param array with $results
		 * @return boolean
		 */
		private function connect_query_results( $results ) {

			$and_results = array();
			$end_result	 = true;
			$or_results	 = array();

			if ( count( $results ) > 0 ) {

				foreach ( $results as $query_result ) {

					$result_split = explode( '#', $query_result );

					if ( $result_split[ 0 ] === '2' ) {

						array_push( $or_results, $and_results ); // store the current "and" result for processing as "or" result

						$and_results = array(); // clear the "and" array
					}

					$and_result = $result_split[ 1 ]; // === 'false' ? 'false' : 'true';

					array_push( $and_results, $and_result );
				}

				if ( count( $and_results ) > 0 ) {

					array_push( $or_results, $and_results );
				}

				if ( count( $or_results ) > 0 ) {

					$end_result = false;

					foreach ( $or_results as $or_result ) {

						$a = true;

						foreach ( $or_result as $and_array ) {

							if ( $and_array === 'false' ) {
								$a = false;
							}
						}

						if ( $a ) {
							$end_result = true;
						}
					}
				} else { // no "or" results found
					$end_result = false;
				}
			} else {

				$end_result = false;
			}

			return $end_result;
		}

	}

	

	

    // end of WPPFM_Feed_Processor_Class

endif;	