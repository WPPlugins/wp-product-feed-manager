<?php

/* * ******************************************************************
 * Version 4.1
 * Modified: 27-04-2017
 * Copyright 2017 Accentio. All rights reserved.
 * License: None
 * By: Michel Jongbloed
 * ****************************************************************** */

// Prevent direct access
if ( !defined( 'ABSPATH' ) ) {
	echo 'Hi!  I\'m just a plugin, there\'s not much I can do when called directly.';
	exit;
}

if ( !class_exists( 'WPPFM_Categories_Class' ) ) :

	/**
	 * The WPPFM_Categories_Class contains all functions for working with post categories
	 * 
	 * @class WPPFM_Categories_Class
	 * @version 1.0
	 * @category class
	 */
	class WPPFM_Categories_Class {

		public static function make_shop_category_string( $product_id ) {

			$args	 = array( 'taxonomy' => 'product_cat', );
			$cats	 = wp_get_post_terms( $product_id, 'product_cat', $args );

			$result = array();

			if ( count( $cats ) === 0 ) { return ''; }

			$cat_string = function ($id) use (&$result, &$cat_string) {
				$term = get_term_by( 'id', $id, 'product_cat', 'ARRAY_A' );

				if ( $term[ 'parent' ] ) { $cat_string( $term[ 'parent' ] ); }

				$result[] = $term[ 'name' ];
			};

			$cat_string( $cats[ 0 ]->term_id );

			//return implode( ' &gt; ', $result );
			return implode( ' > ', $result );
		}

		public static function get_shop_categories( $post_id, $separator = ', ' ) {

			$return_string = '';

			$args	 = array( 'taxonomy' => 'product_cat', );
			$cats	 = wp_get_post_terms( $post_id, 'product_cat', $args );

			foreach ( $cats as $cat ) { $return_string .= $cat->name . $separator; }

			return rtrim( $return_string, $separator );
		}

		/**
		 * Returns the product category that is selected as primary (only when Yoast plugin is installed)
		 * 
		 * @param string $id
		 * @return WP_Term Object
		 */
		public static function get_yoast_primary_cat( $id ) {

			if ( !is_plugin_active( 'wordpress-seo/wp-seo.php' ) && !is_plugin_active_for_network( 'wordpress-seo/wp-seo.php' )
				&& !is_plugin_active( 'wordpress-seo-premium/wp-seo-premium.php' ) && !is_plugin_active_for_network( 'wordpress-seo-premium/wp-seo-premium.php' )  ) {
				return false; // return false if yoast plugin is inactive
			}

			$primary_cat_id = get_post_meta( $id,'_yoast_wpseo_primary_product_cat', true );

			if( $primary_cat_id ){
				$product_cat[0] = get_term( $primary_cat_id, 'product_cat' );
				if( isset( $product_cat[0]->term_id ) ) { return $product_cat; }
			} else { return false; }		
		}

// obsolete 080117
//		public static function make_shop_category_string_from_selected_category( $product_categories, $category_id,
//																		   $category_string, $separator = '/' ) {
//			
//			$category = self::select_category_by_id( $product_categories, $category_id );
//
//			if ( $category ) {
//
//				$return_string = $category->name . $separator . $category_string;
//			} else {
//
//				$return_string = '';
//				wppfm_write_log_file( "Could not find a parent category for $category_string" );
//			}
//
//			$return_string = $category ? $category->name . $separator . $category_string : '';
//
//			if ( $category && $category->parent !== 0 && self::select_category_by_id( $product_categories, $category->parent ) ) {
//
//				$return_string = self::make_shop_category_string_from_selected_category( $product_categories, $category->parent, $return_string, $separator );
//			}
//
//			return rtrim( $return_string, $separator );
//		}

		// TODO: Check if this function can replace, or be replaced by the get_shop_categories or make_shop_category_string_from_selected_category functions
		public static function get_shop_categories_list() {

			$args = array(
				'hide_empty'	 => 0,
				'taxonomy'		 => 'product_cat',
				'hierarchical'	 => 1,
				'orderby'		 => 'name',
				'order'			 => 'ASC',
				'child_of'		 => 0
			);

			return self::get_cat_hierchy( 0, $args );
		}

		private static function get_cat_hierchy( $parent, $args ) {

			$cats	 = get_categories( $args );
			$ret	 = new stdClass;

			foreach ( $cats as $cat ) {

				if ( $cat->parent == $parent ) {
					$id					 = $cat->cat_ID;
					$ret->$id			 = $cat;
					$ret->$id->children	 = self::get_cat_hierchy( $id, $args );
				}
			}

			return $ret;
		}

// obsolete 280117
//		private static function select_category_by_id( $product_categories, $category_id ) {
//
//			foreach ( $product_categories as $category ) {
//
//				if ( $category->term_id === $category_id ) {
//					return $category;
//				}
//			}
//		}
	}

	
    // end of WPPFM_Categories_Class

endif;