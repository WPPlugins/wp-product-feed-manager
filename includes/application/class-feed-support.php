<?php

/* * ******************************************************************
 * Version 1.1
 * Modified: 31-03-2017
 * Copyright 2017 Accentio. All rights reserved.
 * License: None
 * By: Michel Jongbloed
 * ****************************************************************** */

// Prevent direct access
if ( !defined( 'ABSPATH' ) ) {
	echo 'Hi!  I\'m just a plugin, there\'s not much I can do when called directly.';
	exit;
}


if ( !class_exists( 'WPPFM_Feed_Support_Class' ) ) :

	/**
	 * The WPPFM_Feed_Support_Class class contains the support functions for processing the form data to feed data
	 * 
	 * @class WPPFM_Feed_Support_Class
	 * @version dev
	 */
	class WPPFM_Feed_Support_Class {

		public function get_query_string_from_query_object( $query_object ) {

			// TODO: There's probably a better way to do this!
			foreach ( $query_object as $value ) {

				return $value;
			}
		}

		public function find_relation( $feed_name, $relations_table ) {

			$result = '';

			foreach ( $relations_table as $relation ) {

				if ( $relation[ 'field' ] === $feed_name ) {

					$result = $relation[ 'db' ];
					break;
				}
			}

			return $result;
		}
		
		public function category_is_selected( $term_id, $category_mapping ) {

			for ( $i = 0; $i < count($category_mapping); $i++ ) {
				
				if ( (string)$term_id === $category_mapping[$i]->shopCategoryId ) {
					
					return $i;
				}
			}
			
			return false;
		}

		public function check_query_result_on_specific_row( $query_split, $product_data ) {

			$queries_class = new WPPFM_Feed_Queries_Class;
			$current_data = key_exists( $query_split[ 1 ], $product_data) ? $product_data[$query_split[ 1 ]] : '';

			if ( is_array( $current_data) ) { // A user had this once where he had an attribute that only showed "Array()"  as value
				
				$product_id = key_exists( 'ID', $product_data ) ? $product_data[ 'ID' ] : 'unknown';
				$product_title = key_exists( 'post_title', $product_data ) ? $product_data[ 'post_title' ] : 'unknown';

				$error_message = "There is something wrong with the '" . $query_split[ 1 ] . "' attribute of product '$product_title' with id $product_id. It seems to be of a wrong type.";
				
				wppfm_write_log_file( $error_message, 'debug' );
				
				$current_data = $current_data[0];
			}

			$result = true;

			switch ( $query_split[ 2 ] ) {

				case 0:
					$result = $queries_class->includes_query( $query_split, $current_data );
					break;

				case 1:
					$result = $queries_class->does_not_include_query( $query_split, $current_data );
					break;

				case 2:
					$result = $queries_class->is_equal_to_query( $query_split, $current_data );
					break;

				case 3:
					$result = $queries_class->is_not_equal_to_query( $query_split, $current_data );
					break;

				case 4:
					$result = $queries_class->is_empty( $current_data );
					break;

				case 5:
					$result = $queries_class->is_not_empty_query( $current_data );
					break;

				case 6:
					$result = $queries_class->starts_with_query( $query_split, $current_data );
					break;

				case 7:
					$result = $queries_class->does_not_start_with_query( $query_split, $current_data );
					break;

				case 8:
					$result = $queries_class->ends_with_query( $query_split, $current_data );
					break;

				case 9:
					$result = $queries_class->does_not_end_with_query( $query_split, $current_data );
					break;

				case 10:
					$result = $queries_class->is_greater_than_query( $query_split, $current_data );
					break;

				case 11:
					$result = $queries_class->is_greater_or_equal_to_query( $query_split, $current_data );
					break;

				case 12:
					$result = $queries_class->is_smaller_than_query( $query_split, $current_data );
					break;

				case 13:
					$result = $queries_class->is_smaller_or_equal_to_query( $query_split, $current_data );
					break;

				case 14:
					$result = $queries_class->is_between_query( $query_split, $current_data );
					break;

				default:
					break;
			}

			return $result;
		}

		public function edit_value( $current_value, $edit_string, $combination_string, $combined_data_elements ) {
			$value_editors = new WPPFM_Feed_Value_Editors_Class;

			$query_split = explode( '#', $edit_string );

			switch ( $query_split[ 1 ] ) {
				
				case 'change nothing':
					
					$result = $current_value;
					break;

				case 'overwrite':

					$result = $value_editors->overwrite_value( $query_split );
					break;

				case 'replace':

					$result = $value_editors->replace_value( $query_split, $current_value );
					break;

				case 'remove':

					$result = $value_editors->remove_value( $query_split, $current_value );
					break;

				case 'add prefix':

					$result = $value_editors->add_prefix_value( $query_split, $current_value );
					break;

				case 'add suffix':

					$result = $value_editors->add_suffix_value( $query_split, $current_value );
					break;

				case 'recalculate':

					$result = $value_editors->recalculate_value( $query_split, $current_value, $combination_string, $combined_data_elements );
					break;
				
				case 'convert to child-element':
					
					$result = $value_editors->convert_to_element( $query_split, $current_value );
					break;
				
				default:
					
					$result = false;
					break;
			}

			return $result;
		}
		
		public function get_column_names_from_feed_filter_array( $feed_filter_array ) {
			
			$empty_array = array();
			$filters = $feed_filter_array ? json_decode($feed_filter_array[0]['meta_value']) : $empty_array;
			$column_names = array();
			
			foreach ( $filters as $filter ) {
				
				$query_string = $this->get_query_string_from_query_object( $filter );
				$query_parts = explode('#', $query_string);
				
				array_push( $column_names, $query_parts[1] );
			}
			
			return $column_names;
		}
		
		/**
		 * makes a unique feed for a copy of an existing feed
		 * 
		 * @param string $current_feed_name
		 * @return string
		 */
		public function next_unique_feed_name( $current_feed_name ) {

			$queries_class = new WPPFM_Queries();
			
			$title_end = explode( '_', $current_feed_name );
			$end_nr = end( $title_end );
			
			if ( count( $title_end ) > 1 && is_numeric( $end_nr ) ) {
				
				$new_title = substr_replace( $current_feed_name, ( $end_nr + 1 ), -strlen( $end_nr ) );
			} else {
				
				$new_title = $current_feed_name . '_1';
				$end_nr = '1';
			}

			// increase the end number of the title already exists
			while ( $queries_class->title_exists( $new_title ) ) {

				$new_title = substr_replace( $new_title, ( $end_nr + 1 ), -strlen( $end_nr ) );
				$end_nr++;
			}
			
			return $new_title;
		}
		
	}

     // end of WPPFM_Feed_Support_Class

endif;	