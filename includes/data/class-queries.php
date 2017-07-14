<?php

/* * ******************************************************************
 * Version 3.5
 * Modified: 05-06-2017
 * Copyright 2017 Accentio. All rights reserved.
 * License: None
 * By: Michel Jongbloed
 * ****************************************************************** */

// Prevent direct access
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WPPFM_Queries' ) ) :

	/**
	 * The WPPFM_Queries Class contains the database queries
	 * 
	 * @class WPPFM_Queries
	 * @version dev
	 * @category class
	 */
	class WPPFM_Queries {

		/**
		 * @var class reference
		 */
		private $_wpdb;
		
		/**
		 * @var string placeholder containing the wp table prefix
		 */
		private $_table_prefix;

		/**
		 * WPPFM_Queries Constructor
		 */
		public function __construct() {

			// get global wordpress database functions
			global $wpdb;

			// assign the global wpdb to a varable
			$this->_wpdb = &$wpdb;

			// assign the wp table prefix to a variable
			$this->_table_prefix = $this->_wpdb->prefix;
		}

		public function make_list_of_active_feeds() {
			$main_table		 = $this->_table_prefix . 'feedmanager_product_feed';
			$status_table	 = $this->_table_prefix . 'feedmanager_feed_status';

			return $this->_wpdb->get_results( "SELECT p.product_feed_id, p.title, p.url, p.updated, p.products, s.status AS status, s.color AS color "
			. "FROM $main_table AS p "
			. " INNER JOIN $status_table AS s on p.status_id = s.status_id" );
		}
		
		public function get_feed_row( $feed_id ) {
			$main_table		 = $this->_table_prefix . 'feedmanager_product_feed';
			return $this->_wpdb->get_row( "SELECT * FROM $main_table WHERE product_feed_id = $feed_id" );
		}

		/**
		 * Get a list of all existing countries
		 * 
		 * @return results of the query
		 */
		public function read_countries() {
			$main_table = $this->_table_prefix . 'feedmanager_country';
			return $this->_wpdb->get_results( "SELECT name_short, name FROM $main_table ORDER BY name", ARRAY_A );
		}
		
		public function get_feedmanager_channel_table() {
			$main_table = $this->_table_prefix . 'feedmanager_channel';
			return $this->_wpdb->get_results( "SELECT * FROM $main_table", ARRAY_A );
		}
		
		public function get_feedmanager_product_feed_table() {
			$main_table = $this->_table_prefix . 'feedmanager_product_feed';
			return $this->_wpdb->get_results( "SELECT * FROM $main_table", ARRAY_A );
		}
		
		public function get_feedmanager_product_feedmeta_table() {
			$main_table = $this->_table_prefix . 'feedmanager_product_feedmeta';
			return $this->_wpdb->get_results( "SELECT * FROM $main_table", ARRAY_A );
		}

		public function read_channels() {
			$google = array( 'channel_id'=>'1', 'name'=>'Google Merchant', 'short'=>'google' );
			return array( $google ); //NOG NADENKEN => Over hoe ik het zo kan aanpassen dat Google automatisch wordt geselecteerd!
		}

		public function register_a_channel( $channel_short_name, $channel_id, $channel_name ) {
			$main_table = $this->_table_prefix . 'feedmanager_channel';
			return $this->_wpdb->query( "INSERT INTO $main_table (channel_id, name, short) VALUES
				( $channel_id, '$channel_name', '$channel_short_name' )" );
		}

		public function get_channel_id( $channel_short_name ) {
			$main_table = $this->_table_prefix . 'feedmanager_channel';
			return $this->_wpdb->get_var( "SELECT channel_id FROM $main_table WHERE short = '$channel_short_name'" );
		}

		public function get_channel_short_name_from_db( $channel_id ) {
			if ( $channel_id !== 'undefined' ) { // make sure the selected channel is installed
				$main_table = $this->_table_prefix . 'feedmanager_channel';
				return $this->_wpdb->get_var( "SELECT short FROM $main_table WHERE channel_id = $channel_id" );
			} else { return false; }
		}

		public function remove_channel( $channel_id ) {
			$main_table = $this->_table_prefix . 'feedmanager_channel';
			return $this->_wpdb->delete( $main_table, array( 'channel_id' => $channel_id ) );
		}

		public function read_active_schedule_data() {
			$main_table = $this->_table_prefix . 'feedmanager_product_feed';
			return $this->_wpdb->get_results( "SELECT product_feed_id, updated, schedule FROM $main_table WHERE status_id=1", ARRAY_A );
		}
		
		public function read_failed_feeds() {
			$main_table = $this->_table_prefix . 'feedmanager_product_feed';
			return $this->_wpdb->get_results( "SELECT product_feed_id, updated, schedule FROM $main_table WHERE status_id=4 OR status_id=3", ARRAY_A );
		}

		public function read_sources() {
			$main_table = $this->_table_prefix . 'feedmanager_source';
			return $this->_wpdb->get_results( "SELECT source_id, name FROM $main_table ORDER BY name", ARRAY_A );
		}

		public function get_feeds_from_specific_channel( $channel_id ) {
			$main_table = $this->_table_prefix . 'feedmanager_product_feed';
			return $this->_wpdb->get_results( "SELECT product_feed_id FROM $main_table WHERE channel_id = $channel_id", ARRAY_A );
		}

		public function get_meta_parents( $feed_id ) {
			$main_table = $this->_table_prefix . 'posts';
			return $this->_wpdb->get_results( "SELECT ID FROM $main_table WHERE post_parent = $feed_id", ARRAY_A );
		}

		public function read_feed( $feed_id ) {
			$main_table		 = $this->_table_prefix . 'feedmanager_product_feed';
			$countries_table = $this->_table_prefix . 'feedmanager_country';
			$channel_table	 = $this->_table_prefix . 'feedmanager_channel';

			$result = $this->_wpdb->get_results( "SELECT p.product_feed_id, p.source_id AS source, p.title, p.feed_title, p.feed_description, p.main_category, "
			. "p.url, p.include_variations, p.is_aggregator, p.status_id, p.updated, p.schedule, c.name_short "
			. "AS country, m.channel_id AS channel, p.status_id "
			. "FROM $main_table AS p "
			. "INNER JOIN $countries_table AS c ON p.country_id = c.country_id "
			. "INNER JOIN $channel_table AS m ON p.channel_id = m.channel_id "
			. "WHERE p.product_feed_id = $feed_id", ARRAY_A );

			$category_mapping = $this->read_category_mapping( $feed_id );

			if ( isset( $category_mapping[ 0 ][ 'meta_value' ] ) && $category_mapping[ 0 ][ 'meta_value' ] !== '' ) {
				$result[ 0 ][ 'category_mapping' ] = $category_mapping[ 0 ][ 'meta_value' ];
			} else {
				$result[ 0 ][ 'category_mapping' ] = '';
			}

			return $result;
		}
		
		public function read_category_mapping( $feed_id ) {
			$meta_table		 = $this->_table_prefix . 'feedmanager_product_feedmeta';
			return $this->_wpdb->get_results( "SELECT meta_value FROM $meta_table WHERE product_feed_id = $feed_id AND meta_key = 'category_mapping'", ARRAY_A );
		}

		/**
		 * Returns the post data from published products with id between offset and offset+limit
		 * 
		 * @param string $column_string
		 * @param string $category_string
		 * @param int $offset
		 * @param int $limit
		 * @return array
		 */
		public function read_post_data( $column_string, $category_string, $offset, $limit ) {
			$main_table					 = $this->_table_prefix . 'posts';
			$term_relationships_table	 = $this->_table_prefix . 'term_relationships';
			$term_taxonomy_table		 = $this->_table_prefix . 'term_taxonomy';
			$selecting_columns			 = $column_string ? ', ' . $column_string : '';

			return $this->_wpdb->get_results( "SELECT DISTINCT ID $selecting_columns 
				FROM $main_table 
				LEFT JOIN $term_relationships_table ON ($main_table.ID = $term_relationships_table.object_id) 
				LEFT JOIN $term_taxonomy_table ON ($term_relationships_table.term_taxonomy_id = $term_taxonomy_table.term_taxonomy_id)
				WHERE $main_table.post_type = 'product' AND $main_table.post_status = 'publish'
				AND ID >= $offset AND ID < $limit
				AND $term_taxonomy_table.term_id IN ($category_string)
				ORDER BY ID" );
		}

		/**
		 * returns the lowest product id belonging to one or more categories
		 * 
		 * @param string $category_string
		 * @return string
		 */
		public function get_lowest_product_id( $category_string ) {
			$main_table					= $this->_table_prefix . 'posts';
			$term_relationships_table	= $this->_table_prefix . 'term_relationships';
			$term_taxonomy_table		= $this->_table_prefix . 'term_taxonomy';

			$result = $this->_wpdb->get_results( "SELECT MIN(ID) FROM $main_table 
				LEFT JOIN $term_relationships_table ON ($main_table.ID = $term_relationships_table.object_id) 
				LEFT JOIN $term_taxonomy_table ON ($term_relationships_table.term_taxonomy_id = $term_taxonomy_table.term_taxonomy_id)
				WHERE $main_table.post_type = 'product' AND $main_table.post_status = 'publish'
				AND $term_taxonomy_table.term_id IN ($category_string)"
				, ARRAY_A );

			return $result ? $result[ 0 ][ 'MIN(ID)' ] : 0;
		}
		

		/**
		 * returns the highest product id belonging to one or more categories
		 * 
		 * @param string $category_string
		 * @return string
		 */
		public function get_highest_product_id( $category_string ) {
			$main_table					= $this->_table_prefix . 'posts';
			$term_relationships_table	= $this->_table_prefix . 'term_relationships';
			$term_taxonomy_table		= $this->_table_prefix . 'term_taxonomy';

			$result = $this->_wpdb->get_results( "SELECT MAX(ID) FROM $main_table 
				LEFT JOIN $term_relationships_table ON ($main_table.ID = $term_relationships_table.object_id) 
				LEFT JOIN $term_taxonomy_table ON ($term_relationships_table.term_taxonomy_id = $term_taxonomy_table.term_taxonomy_id)
				WHERE $main_table.post_type = 'product' AND $main_table.post_status = 'publish'
				AND $term_taxonomy_table.term_id IN ($category_string)"
				, ARRAY_A );

			return $result ? $result[ 0 ][ 'MAX(ID)' ] : 0;
		}

		public function read_meta_data( $post_id, $record_ids, $meta_columns ) {
			$options_table	 = $this->_table_prefix . 'options';
			$data = array();

			foreach ( $meta_columns as $column ) {
				foreach( $record_ids as $rec_id ) {
					
					$value = get_post_meta( $rec_id, $column, true );
					
					if ( $value ) {
						array_push( $data, $this->make_meta_object( $column, $value, $rec_id ) );
					} else {
						$alt_val = maybe_unserialize( get_post_meta( $rec_id, '_product_attributes', true ) );
						$col_name = str_replace( ' ', '-', strtolower($column));
						if ( $alt_val && isset( $alt_val[$col_name] ) ) {
							array_push( $data, $this->make_meta_object( $column, $alt_val[$col_name]['value'], $rec_id ) );
						} elseif ( $alt_val && is_array( $alt_val )) {
							foreach( $alt_val as $v ) {
								if ( $v['name'] === $column ) { array_push( $data, $this->make_meta_object( $column, $v['value'], $rec_id ) ); } 
							}
						}
					}
				}
			}
			
			$main_url = $this->_wpdb->get_var( "SELECT option_value FROM $options_table WHERE option_name = 'siteurl'" );
			$this->polish_data( $data, $post_id, $main_url );

			return $data;
		}
		
		private function make_meta_object( $key, $value, $id ) {
			$obj = new stdClass();
			$obj->meta_key = $key;
			$obj->meta_value = $value;
			$obj->post_id = $id;
			
			return $obj;
		}

		private function polish_data( &$data, $main_post_id, $site_url ) {

			foreach ( $data as $row ) {

				// make sure the _wp_attached_file data contains a valide url
				if ( $row->meta_key === '_wp_attached_file' ) {
					$row->meta_value = $this->get_post_thumbnail_url( $main_post_id, 'large' );

					// if the _wp_attached_file data is not a valid url than add the url data
					if ( !filter_var( $row->meta_value, FILTER_VALIDATE_URL ) ) {
						$row->meta_value = $site_url . '/wp-content/uploads/' . $row->meta_value;
					}
				}
				
				// convert the time stamp format to a usable date time format for the feed
				if ( $row->meta_key === '_sale_price_dates_from' || $row->meta_key === '_sale_price_dates_to' ) {
					$row->meta_value = convert_price_date_to_feed_format( $row->meta_value ) ;
				}
			}
		}

		public function delete_feed( $feed_id ) {
			$main_table = $this->_table_prefix . 'feedmanager_product_feed';
			return $this->_wpdb->delete( $main_table, array( 'product_feed_id' => $feed_id ) );
		}

		public function delete_meta( $feed_id ) {
			$main_table = $this->_table_prefix . 'feedmanager_product_feedmeta';
			return $this->_wpdb->delete( $main_table, array( 'product_feed_id' => $feed_id ) );
		}

		public function read_metadata( $feed_id ) {

			if ( $feed_id ) {
				$main_table = $this->_table_prefix . 'feedmanager_product_feedmeta';
				return $this->_wpdb->get_results( "SELECT * FROM $main_table WHERE product_feed_id = $feed_id AND meta_key != 'category_mapping' AND meta_key != 'product_filter_query' ORDER BY meta_id", ARRAY_A );
			} else { return false; }
		}
		
		public function get_product_filter_query( $feed_id ) {

			if ( $feed_id ) {
				$main_table = $this->_table_prefix . 'feedmanager_product_feedmeta';
				return $this->_wpdb->get_results( "SELECT meta_value FROM $main_table WHERE product_feed_id = $feed_id AND meta_key = 'product_filter_query'", ARRAY_A );
			} else {
				wppfm_write_log_file( "Function get_filter_query returned false on feed $feed_id" );
				return false;
			}
		}

		public function get_columns_from_post_table() {
			$main_table = $this->_table_prefix . 'posts';
			return $this->_wpdb->get_results( "SHOW COLUMNS FROM $main_table" );
		}

		public function get_custom_product_attributes() {
			$main_table = $this->_table_prefix . 'woocommerce_attribute_taxonomies';
			return $this->_wpdb->get_results( "SELECT attribute_name, attribute_label FROM $main_table" );
		}

		public function get_custom_product_fields() {
			$keywords_array = explode( ', ', get_option( 'wppfm_third_party_attribute_keywords' ) );
			$main_table	 = $this->_wpdb->postmeta;
			
			$query_string = "SELECT DISTINCT meta_key FROM $main_table WHERE meta_key NOT BETWEEN '_' AND '_z'";
			
			foreach( $keywords_array as $keyword ) {
				$query_string .= " OR meta_key LIKE '$keyword'";
			}
			
			$query_string .= " ORDER BY meta_key";

			return $this->_wpdb->get_col( $query_string );
		}
		
		public function get_own_variable_product_attributes( $variable_id ) {
			$keywords = get_option( 'wppfm_third_party_attribute_keywords', '_wpmr_%, _cpf_%, _unit%' );
			$wpmr_attributes = array();
			
			if ( $keywords ) {
				$keywords_array = explode( ', ', $keywords );
				$main_table	 = $this->_wpdb->postmeta;
				$query_where_string = count( $keywords_array ) > 0 ? "WHERE (meta_key LIKE '$keywords_array[0]'" : '';

				for( $i = 1; $i < count( $keywords_array ); $i++ ) {
					$query_where_string .= " OR meta_key LIKE '$keywords_array[$i]'";
				}

				$query_where_string .= count( $keywords_array ) > 0 ? ") AND " : '';

				foreach( $this->_wpdb->get_results( "SELECT meta_key, meta_value FROM $main_table $query_where_string (post_id = $variable_id)" ) as $key => $row ) {
					$wpmr_attributes[$row->meta_key] = $row->meta_value;
				}
			}
			
			return $wpmr_attributes;
		}
		
		public function get_all_product_attributes() {
			$main_table	 = $this->_wpdb->postmeta;
			return $this->_wpdb->get_results( "SELECT DISTINCT meta_value FROM $main_table WHERE meta_key = '_product_attributes'" );
		}

		public function get_current_feed_status( $feed_id ) {
			$main_table = $this->_table_prefix . 'feedmanager_product_feed';
			return $this->_wpdb->get_results( "SELECT status_id FROM $main_table WHERE product_feed_id = $feed_id" );
		}

		public function get_country_id( $country_code ) {
			$main_table = $this->_table_prefix . 'feedmanager_country';
			return $this->_wpdb->get_row( "SELECT country_id FROM $main_table WHERE name_short = '$country_code'" );
		}

		public function get_status_id( $status ) {
			$main_table = $this->_table_prefix . 'feedmanager_feed_status';
			return $this->_wpdb->get_row( "SELECT status_id FROM $main_table WHERE status = '$status'" );
		}

		public function update_current_feed_status( $feed_id, $new_status ) {
			$main_table = $this->_table_prefix . 'feedmanager_product_feed';
			return $this->_wpdb->update( $main_table, array( 'status_id' => $new_status ), array( 'product_feed_id' => $feed_id ) );
		}

		public function set_nr_feed_products( $feed_id, $nr_products ) {
			$main_table = $this->_table_prefix . 'feedmanager_product_feed';
			return $this->_wpdb->update( $main_table, array( 'products' => $nr_products ), array( 'product_feed_id' => $feed_id ) );
		}
		
		public function get_nr_feed_products( $feed_id ) {
			$main_table = $this->_table_prefix . 'feedmanager_product_feed';
			return $this->_wpdb->get_row( "SELECT products FROM $main_table WHERE product_feed_id = '$feed_id'" );
		}

		/**
		 * Updates a new feed in the product_feed table.
		 * 
		 * @since 1.0.0
		 * 
		 * @param (int) $feed_id
		 * @param (int) $channel_id
		 * @param (int) $country_id
		 * @param (int) $source_id
		 * @param (string) $title
		 * @param (string) $feed_title			// @since 1.8.0
		 * @param (string) $feed_description	// @since 1.8.0
		 * @param (string) $url
		 * @param (int) $status
		 * @return (int) nr of affected rows
		 */
		public function update_feed( $feed_id, $channel_id, $country_id, $source_id, $title, $feed_title, $feed_description, $main_category, $incl_variations, $is_aggregator,
							   $url, $status, $schedule ) {
			
			$main_table = $this->_table_prefix . 'feedmanager_product_feed';

			$result = $this->_wpdb->update( $main_table, array(
				'channel_id'			=> $channel_id,
				'include_variations'	=> $incl_variations,
				'is_aggregator'			=> $is_aggregator,
				'country_id'			=> $country_id,
				'source_id'				=> $source_id,
				'title'					=> $title,
				'feed_title'			=> $feed_title,
				'feed_description'		=> $feed_description,
				'main_category'			=> $main_category,
				'url'					=> $url,
				'status_id'				=> $status,
				'schedule'				=> $schedule,
				'updated'				=> date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
			), array( 'product_feed_id' => $feed_id ), array(
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
				'%s',
			), array( '%d' ) );

			return $result;
		}

		public function update_feed_update_data( $feed_id, $feed_url ) {
			$main_table = $this->_table_prefix . 'feedmanager_product_feed';
			return $this->_wpdb->update( $main_table, array( 
				'updated' => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ), 
				'url' => $feed_url ), 
			array( 'product_feed_id' => $feed_id ), 
			array( '%s', '%s' ), 
			array( '%d' ) );
		}

		public function get_file_url_from_feed( $feed_id ) {
			$main_table = $this->_table_prefix . 'feedmanager_product_feed';
			$result = $this->_wpdb->get_row( "SELECT url FROM $main_table WHERE product_feed_id = $feed_id", ARRAY_A );
			return $result[ 'url' ];
		}

		public function update_feed_file_status( $feed_id, $status ) {
			$main_table = $this->_table_prefix . 'feedmanager_product_feed';
			return $this->_wpdb->update( $main_table, array( 'status_id' => $status ), array( 'product_feed_id' => $feed_id ), array( '%d' ), array( '%d' ) );
		}

		public function update_meta_data( $feed_id, $meta_data ) {

			// TODO: Onderstaande proces is nog niet echt efficient. In plaats van het standaard verwijderen
			// van elke bestaande regel met het geselecteerde id nummer, zou ik eerst moeten kijken of
			// deze regel wel is gewijzigd. Indien een regel niet is gewijzigd zou ik hem ook niet moeten
			// verwijderen.

			$main_table = $this->_table_prefix . 'feedmanager_product_feedmeta';

			// first remove all meta data belonging to this feed except the product_filter_query
			//$this->_wpdb->delete( $main_data, array( 'product_feed_id' => $feed_id ) ); // could not use it because it can't work with !=
			$this->_wpdb->query( "DELETE FROM $main_table WHERE product_feed_id = $feed_id AND meta_key != 'product_filter_query'" );

			$counter = 0;

			for ( $i = 0; $i < count( $meta_data ); $i ++ ) {
				if ( !empty( $meta_data[ $i ]->value ) && $meta_data[ $i ]->value !== '{}' ) {
					$result = $this->_wpdb->insert( $main_table, array(
						'product_feed_id'	 => $feed_id,
						'meta_key'			 => $meta_data[ $i ]->key,
						'meta_value'		 => $meta_data[ $i ]->value
					), array(
						'%d',
						'%s',
						'%s'
					) );

					$counter += $result;
				}
			}

			return $counter;
		}
		
		public function store_feed_filter( $feed_id, $filter ) {
			$main_table = $this->_table_prefix . 'feedmanager_product_feedmeta';
			
			if ( $filter ) {
				
				$exists = $this->_wpdb->get_results( "SELECT meta_id FROM $main_table WHERE product_feed_id = $feed_id AND meta_key = 'product_filter_query'" );
				
				if ( $exists ) {				
					$this->_wpdb->update( $main_table, array( 'meta_value' => $filter ), array( 'product_feed_id' => $feed_id, 'meta_key' => 'product_filter_query' ), array( '%s' ), array( '%d', '%s' ) );
				} else {
					$this->_wpdb->insert( $main_table, array( 'product_feed_id' => $feed_id, 'meta_key' => 'product_filter_query', 'meta_value' => $filter ), array( '%d', '%s', '%s' ) );
				}
			} else {
				$this->_wpdb->query( "DELETE FROM $main_table WHERE product_feed_id = $feed_id AND meta_key = 'product_filter_query'" );
			}
		}

		public function insert_meta_data( $feed_id, $meta_data, $category_mapping ) {
			$main_table = $this->_table_prefix . 'feedmanager_product_feedmeta';

			$counter = 0;

			for ( $i = 0; $i < count( $meta_data ); $i ++ ) {
				
				$result = $this->_wpdb->insert( $main_table, array(
						'product_feed_id'	 => $feed_id,
						'meta_key'			 => $meta_data[ $i ]['meta_key'],
						'meta_value'		 => $meta_data[ $i ]['meta_value']
					), array(
						'%d',
						'%s',
						'%s'
					) );

				$counter += $result;
			}

			$counter += $this->_wpdb->insert( $main_table, array(
					'product_feed_id'	 => $feed_id,
					'meta_key'			 => 'category_mapping',
					'meta_value'		 => $category_mapping[0]['meta_value']
				), array(
					'%d',
					'%s',
					'%s'
				) );

			return $counter;
		}
		
		public function title_exists( $feed_title ) {
			$main_table = $this->_table_prefix . 'feedmanager_product_feed';
			$count = $this->_wpdb->get_var( "SELECT COUNT(*) FROM $main_table WHERE title = '$feed_title'" );
			return $count > 0 ? true : false;
		}

		/**
		 * Inserts a new feed in the product_feed table and returns its new id.
		 * 
		 * @since 1.0.0
		 * 
		 * @param int $channel_id
		 * @param int $country_id
		 * @param int $source_id
		 * @param string $title
		 * @param string $feed_title		// @since 1.8.0
		 * @param string $feed_description	// @since 1.8.0
		 * @param int $incl_variations
		 * @param int $is_aggregator
		 * @param string $url
		 * @param string $status
		 * @param string $schedule
		 * @return integer containing the id of the new feed
		 */
		public function insert_feed( $channel_id, $country_id, $source_id, $title, $feed_title, $feed_description, $main_category, $incl_variations, $is_aggregator, $url,
							   $status, $schedule ) {

			$main_table = $this->_table_prefix . 'feedmanager_product_feed';

			$this->_wpdb->insert( $main_table, array(
				'channel_id'			=> $channel_id,
				'include_variations'	=> $incl_variations,
				'is_aggregator'			=> $is_aggregator,
				'country_id'			=> $country_id,
				'source_id'				=> $source_id,
				'title'					=> $title,
				'feed_title'			=> $feed_title,
				'feed_description'		=> $feed_description,
				'main_category'			=> $main_category,
				'url'					=> $url,
				'status_id'				=> $status,
				'schedule'				=> $schedule,
				'updated'				=> date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
				'products'				=> 0
			), array(
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
				'%s',
				'%d'
			) );

			return $this->_wpdb->insert_id;
		}
		
		/**
		 * Reads the data from all plugin related tables ands stores the data in a sql like string
		 * The return string contains a timestamp, the database version, the option settings and the table content
		 * 
		 * @since 1.7.0
		 * @return string sql like string with the backup data
		 */
		public function read_full_backup_data() {
			$main_table = $this->_table_prefix . 'feedmanager_product_feed';
			$meta_table = $this->_table_prefix . 'feedmanager_product_feedmeta';
			$channel_table = $this->_table_prefix . 'feedmanager_channel';
			
			$main_table_content = $this->make_table_backup_string( $this->_wpdb->get_results( "SELECT * FROM $main_table", ARRAY_N ) );
			$meta_table_content = $this->make_table_backup_string( $this->_wpdb->get_results( "SELECT * FROM $meta_table", ARRAY_N ) );
			$channel_table_content = $this->make_table_backup_string( $this->_wpdb->get_results( "SELECT * FROM $channel_table", ARRAY_N ) );

			$db_version = get_option( 'wppfm_db_version' );
			$ftp_passive = get_option( 'wppfm_ftp_passive', "true" );
			$auto_fix = get_option( 'wppfm_auto_feed_fix', "true" );
			$sep_string = '# backup string for database ->';
			$time_stamp = current_time( 'timestamp' );
			
			$table_content = "$time_stamp#$db_version#$ftp_passive#$auto_fix";
			$table_content .= "$sep_string $main_table # <- # $main_table_content ";
			$table_content .= "$sep_string $meta_table # <- # $meta_table_content ";
			$table_content .= "$sep_string $channel_table # <- # $channel_table_content";
			
			return $table_content;
		}

		/**
		 * Restores the data in the database tables
		 * 
		 * @since 1.7.0
		 * @param array $table_queries
		 */
		public function restore_backup_data( $table_queries ) {
			$product_feed_table_data = explode( PHP_EOL, $table_queries[0][1] );
			$product_feedmeta_table_data = explode( PHP_EOL, $table_queries[1][1] );
			$channel_table_data = explode( PHP_EOL, $table_queries[2][1] );
			
			$main_table = $this->_table_prefix . 'feedmanager_product_feed';
			$meta_table = $this->_table_prefix . 'feedmanager_product_feedmeta';
			$channel_table = $this->_table_prefix . 'feedmanager_channel';

			$this->_wpdb->query( "TRUNCATE TABLE $main_table" );
			$this->_wpdb->query( "TRUNCATE TABLE $meta_table" );
			$this->_wpdb->query( "TRUNCATE TABLE $channel_table" );
			
			foreach( $product_feed_table_data as $table_data ) {
				$product_feed_data = explode( "\t", $table_data );

				if( 16 === count( $product_feed_data ) ) {
					$this->_wpdb->replace( $main_table, array(
						'product_feed_id' => $product_feed_data[0],
						'channel_id' => $product_feed_data[1],
						'is_aggregator' => $product_feed_data[2],
						'include_variations' => $product_feed_data[3],
						'country_id' => $product_feed_data[4],
						'source_id' => $product_feed_data[5],
						'title' => $product_feed_data[6],
						'feed_title' => $product_feed_data[7],
						'feed_description' => $product_feed_data[8],
						'main_category' => $product_feed_data[9],
						'url' => $product_feed_data[10],
						'status_id' => $product_feed_data[11],
						'updated' => $product_feed_data[12],
						'schedule' => $product_feed_data[13],
						'products' => 0,
						'timestamp' => $product_feed_data[15]
					), array(
						'%d',
						'%d',
						'%d',
						'%d',
						'%d',
						'%d',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%d',
						'%s',
						'%s',
						'%d',
						'%s'
					) );
				}
			}
			
			foreach( $product_feedmeta_table_data as $table_metadata ) {
				$product_feed_metadata = explode( "\t", $table_metadata );

				if( 4 === count( $product_feed_metadata ) ) {
					$this->_wpdb->replace( $meta_table, array(
						'meta_id' => $product_feed_metadata[0],
						'product_feed_id' => $product_feed_metadata[1],
						'meta_key' => $product_feed_metadata[2],
						'meta_value' => $product_feed_metadata[3]
					), array(
						'%d',
						'%d',
						'%s',
						'%s'
					) );
				}
			}
			
			foreach( $channel_table_data as $table_channeldata ) {
				$channel_data = explode( "\t", $table_channeldata );

				if( 3 === count( $channel_data ) ) {
					$this->_wpdb->replace( $channel_table, array(
						'channel_id' => $channel_data[0],
						'name' => $channel_data[1],
						'short' => $channel_data[2]
					), array(
						'%d',
						'%s',
						'%s'
					) );
				}
			}
		}

		/**
		 * Returns a tab separated string with the query results
		 */
		private function make_table_backup_string( $query_result ) {
			$string = '';
			foreach( $query_result as $row ) { $string .= implode( "\t", $row )."\r\n"; }
			return $string;
		}
	}
	

     // End of WPPFM_Queries class

endif;