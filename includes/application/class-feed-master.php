<?php

/* * ******************************************************************
 * Version 8.4
 * Modified: 02-06-2017
 * Copyright 2017 Accentio. All rights reserved.
 * License: None
 * By: Michel Jongbloed
 * ****************************************************************** */

// Prevent direct access
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WPPFM_Feed_Master_Class' ) ) :

	/**
	 * The WPPFM_Feed_Master class contains the general feed functions and can be used to extend
	 * feed classes that generate feeds for a specific feed shop
	 * 
	 * 
	 * 
	 * @class WPPFM_Feed_Master_Class
	 * @version dev
	 */
	class WPPFM_Feed_Master_Class extends WPPFM_Async_Request {

		/* --------------------------------------------------------------------------------------------------*
		 * Attributes
		 * -------------------------------------------------------------------------------------------------- */

		/**
		 * @var object placeholder containing the main feed data
		 */
		protected $_feed;

		/**
		 * @var int
		 */
		protected $_product_counter;
		
		/**
		 * @var string action request identifier
		 */
		protected $action = 'activate_feed_update';

		/**
		 * @var class reference
		 */
		protected $data_class;
		
		/**
		 * @var class reference
		 */
		protected $feed_class;
		
		/**
		 * @var array with ints
		 */
		protected $_ids_in_feed;
		
		protected $_multipl = 3.3;
		
		/**
		 * WP_Feed_Master_Class Constructor
		 */
		public function __construct() {
			parent::__construct();
			
			$this->data_class = new WPPFM_Data_Class();
		}

		/* --------------------------------------------------------------------------------------------------*
		 * Public functions
		 * -------------------------------------------------------------------------------------------------- */

		/**
		 * Starts the feed update process
		 * 
		 * @access public
		 * @param object $feed_data
		 * @param bool $silent (default = false)
		 */
		public function update_feed_file( $feed_data, $silent = false ) {
			
			// store the feed data localy
			$this->_feed = $feed_data ? $feed_data : null;

			// some channels do not use channels and leave the main category empty which causes issues
			if ( function_exists( 'channel_uses_category' ) && !channel_uses_category( $this->_feed->channel ) ) {
				$this->_feed->mainCategory = 'No Category Required';
			}
			
			$this->feed_class = new WPPFM_Google_Feed_Class();
			
			$result = $this->handle();
			
			if ( !$silent && true !== $result ) echo $result;
		}

		/* --------------------------------------------------------------------------------------------------*
		 * Protected functions
		 * -------------------------------------------------------------------------------------------------- */

		/**
		 * starts the file update process as an async request
		 */
		protected function handle() {
			$current_feed_status = $this->data_class->get_feed_status( $this->_feed->feedId ); // store the feed status before starting the update process

			$this->data_class->set_nr_of_feed_products( $this->_feed->feedId, '0' ); // 0 products
			$this->data_class->update_feed_status( $this->_feed->feedId , 4 ); // set status to "Processing"
			if ( '4' === $this->_feed->status ) { $this->_feed->status = '2'; } // in case the previous status was already on processing
			
			// prepair the folder structure to support saving feed files
			if ( !file_exists( WPPFM_FEEDS_DIR ) ) { WPPFM_Folders_Class::make_feed_support_folder(); }
			
			// set the required variables
			$file_extention = function_exists( 'get_file_type' ) ? get_file_type( $this->_feed->channel ) : 'xml';
			$file_name = $this->_feed->title;
			$feed_name = $file_name . '.' . $file_extention;
			$feed_path = $this->get_file_path( $feed_name );
			
			// write the feed file
			$result = $this->generate_feed_file( $feed_path, $file_extention ); // starts the generate_file_text function with the channel specific parameters

			// register the update in the database
			if ( $result ) { 
				$this->register_feed_update( $feed_name, $current_feed_status ); 
			} else {
				echo wppfm_show_wp_error( __( "There were problems with the feed generation. Please try again. If the issue persists, please issue a support ticket at wpmarketingrobot.com ", 'wp-product-feed-manager' ) );
			}
			
			//echo "<script type='text/javascript'>wppfm_resetFeedList();</script>"; // call the javascript reset feed list function
			
			return $result;
		}

		/**
		 * converts feed data to a text string that then is stored in the feed
		 * 
		 * @return string
		 */
		protected function generate_feed_file( $feed_path, $file_extention ) {

			// gets category name and description name for the selected channel
			// the channel_file_text_data function exists in the wppfm-channel-functions file that is only available for the full plugin version
			$channel_text_details = function_exists( 'channel_file_text_data' ) ? channel_file_text_data( $this->_feed->channel ) : 
				array( 'category_name' => 'google_product_category', 'description_name' => 'description' );
			
			/* -- GET ALL THE REQUIRED PRE DATA -- */
			$pre_data_array = $this->get_required_pre_data();
			
			/* -- GENERATE THE FEED -- */
			return $this->generate_feed( $pre_data_array, $channel_text_details, $feed_path, $file_extention );
		}
		
		/**
		 * register the update in the database
		 * 
		 * @param string $feed_name
		 * @param string $set_status
		 */
		protected function register_feed_update( $feed_name, $set_status = null ) {
			
			// register the update and update the feed Last Change time
			$this->data_class->update_feed_data( $this->_feed->feedId, $this->get_file_url( $feed_name ) );

			$actual_status = $set_status ? $set_status : $this->data_class->get_feed_status( $this->_feed->feedId );
			$old_status = $this->_feed->status;

			if ( $actual_status !== '3' ) { // no errors
				$this->data_class->update_feed_status( $this->_feed->feedId, $old_status ); // put feed on status hold if no errors are reported
			}
		}

		/**
		 * header text, override this function in the class-feed.pho if required for a channel specific header
		 * 
		 * @param string $title
		 * @return string
		 */
		protected function header( $title ) { return '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0"><products>'; }

		/**
		 * footer text, override if required for a channel specific footer
		 * 
		 * @return string
		 */
		protected function footer() { return '</products></rss>'; }
		
		protected function add_xml_sub_tags( &$product ) { return $product;	}

		/**
		 * converts an ordinary xml string into a CDATA string
		 * 
		 * @param string $string
		 * @return string
		 */
		protected function convert_to_character_data_string( $string ) { return "<![CDATA[ $string ]]>"; }
		
		// TODO: This function has to be removed as soon as every user has upgraded to 1.6
		protected function data_string( $string ) { return "<![CDATA[ $string ]]>";	}

		// ALERT! has a javascript equivalent in channel-functions.js called setAttributeStatus();
		/**
		 * sets the activity status of a specific attribute to true or false depending on its level
		 * 
		 * @param type $field_level
		 * @param type $field_value
		 * @return boolean
		 */
		protected function set_attribute_status( $field_level, $field_value ) {
			if ( $field_level > 0 && $field_level < 3 ) { return true; }
			$clean_field_value = trim( $field_value );
			if ( !empty( $clean_field_value ) ) { return true; }
			return false;
		}
		

		/* --------------------------------------------------------------------------------------------------*
		 * Private functions
		 * -------------------------------------------------------------------------------------------------- */

		/**
		 * actually generates the feed
		 * 
		 * @param array $pre_data
		 * @param array $channel_details
		 * @param string $feed_path
		 * @param type $file_extention
		 * @return string
		 */
		private function generate_feed( $pre_data, $channel_details, $feed_path, $file_extention ) {
			if ( is_writable( WPPFM_FEEDS_DIR ) ) {
				$feed = fopen( $feed_path, "w" );
			} else {
				return sprintf( __( "1430 - %s is not a writable folder. Make sure you have admin rights to this folder.", 'wp-product-feed-manager' ), WPPFM_FEEDS_DIR );
			}
				
			if ( $feed !== false ) {
				if ( $this->_feed->categoryMapping ) {
					$queries_class = new WPPFM_Queries();
					$prep_meta_class = new WPPFM_Feed_Value_Editors_Class();
					$feed_processor_class = new WPPFM_Feed_Processor_Class();
					$data_class = new WPPFM_Data_Class();

					if( $this->_feed->channel === '1' && !empty( $this->_feed->feedTitle ) ) {
						fwrite( $feed, $this->feed_class->header( $this->_feed->feedTitle, $this->_feed->feedDescription ) );
					} elseif ( $file_extention === 'xml' || $this->_feed->channel === '17' ) {
						fwrite( $feed, $this->feed_class->header( $this->_feed->title ) );
					}

					$selected_categories = $this->make_category_selection_string( json_decode( $this->_feed->categoryMapping ) );

					$post_columns_query_string = $pre_data['database_fields']['post_column_string'] ? substr( $pre_data['database_fields']['post_column_string'], 0, -2 ) : '';

					$query_batch_size = 1000;
					$time_limit = 30;
					
					$done = false;
					$offset = $queries_class->get_lowest_product_id( $selected_categories );
					$last_fetched_post_id = '';
					$highest_post_id = $queries_class->get_highest_product_id( $selected_categories );
					$this->_product_counter = 0;
					$this->_ids_in_feed = array();
		            $cat_val = $time_limit * $this->_multipl;
					$valid_lic = get_option( 'wppfm_lic_status' ) !== 'valid' ? true : false;
					
					while( !$done ) {
						set_time_limit( $time_limit );
						
						if ( $last_fetched_post_id > $highest_post_id ) { $done = true; }
					
						$products = $queries_class->read_post_data( $post_columns_query_string, $selected_categories, $offset, $offset+$query_batch_size );
						
						$nr_products = count( $products );

						if ( $nr_products > 0 ) {
							
							foreach ( $products as $product ) {
								
								// make sure no doubles are registered
								if ( in_array( $product->ID, $this->_ids_in_feed ) ) { break; }
								
								// parent ids are required to get the main data from product variations
								$meta_parent_ids = $this->get_meta_parent_ids( $product->ID );

								array_unshift( $meta_parent_ids, $product->ID ); // combine the product id with the parent ids

								$meta_data = $queries_class->read_meta_data( $product->ID, $meta_parent_ids, 
									$pre_data['database_fields']['meta_fields'] );
				
								foreach ( $meta_data as $meta ) {
									$meta_value = $prep_meta_class->prep_meta_values( $meta );

									if ( array_key_exists( $meta->meta_key, $product ) ) {
										$meta_key = $meta->meta_key;

										if ( $product->$meta_key === '' ) {
											$product = (object) array_merge( (array) $product, array( $meta->meta_key => $meta_value ) );
										}
									} else {
										$product = (object) array_merge( (array) $product, array( $meta->meta_key => $meta_value ) );
									}
								}

								foreach ( $pre_data['database_fields']['active_custom_fields'] as $field ) {
									$product->{$field} = $this->get_custom_field_data( $product->ID, $field );
								}

								foreach ( $pre_data['database_fields']['third_party_custom_fields'] as $third_party_field ) {
									$product->{$third_party_field} = $this->get_third_party_custom_field_data( $product->ID, $third_party_field );
								}
								
								$this->add_procedural_data( $product, $pre_data['column_names'] );

								$product_data = $feed_processor_class->generate_product_data( $product, $this->_feed->includeVariations,
									$pre_data['active_fields'], $this->_feed->attributes, $pre_data['filters'], $pre_data['field_relations'],
									$channel_details['category_name'], $this->_feed->mainCategory, json_decode( $this->_feed->categoryMapping ), 'wppf', $file_extention);
								
								foreach ( $product_data as $data ) {
									$product_text = '';

									$product_text .= $this->generate_feed_text( $data, $channel_details, $file_extention, $pre_data['active_fields'] );

									fwrite( $feed, $product_text );
	
									$this->_product_counter++;
								}
								
								if ( $this->_product_counter > $cat_val ) { 
									$done = true;
									break; 
								}
								
								array_push( $this->_ids_in_feed, $product->ID );
								$last_fetched_post_id = $product->ID; // know where to start in the next read_post_data query
							}

							$offset = $last_fetched_post_id + 1;
						} else {
							$last_fetched_post_id += $query_batch_size;
							$offset = $last_fetched_post_id;
						}
					}
					
					// add a footer to the xml feed
					if ( $file_extention === 'xml' ) { fwrite( $feed, $this->feed_class->footer() ); }
					
				} else {
					// user has not selected any category
					fwrite( $feed, '<item>No Categories selected! Please select at least one Shop Category from the Category Mapping.</item>' );
					return __( "You have not selected any category in the Category Mapping field so there where no items to add to the feed", 'wp-product-feed-manager' );
				}
				
				fclose( $feed );

				$data_class->set_nr_of_feed_products( $this->_feed->feedId, $this->_product_counter );
				
				// restore the origional timeout setting
				$stored_timeout = ini_get( 'max_execution_time' );
				if( $stored_timeout ) { set_time_limit( $stored_timeout ); }
				
				return sprintf( __( 'Updated feed %s.%s successfully. The feed contains %d products.', 'wp-product-feed-manager' ), $this->_feed->title, $file_extention, $this->_product_counter );
			} else {
				
				wppfm_write_log_file( "User could not access the $feed_path file because he misses the correct rights to acces the " . WPPFM_FEEDS_DIR . " folder." );
				return sprintf( __( '1431 - Could not access the %s file. If you have the feed file open please close it and make sure you have admin rights to the %s folder.', 'wp-product-feed-manager' ). $feed_path, WPPFM_FEEDS_DIR );
			}
		}

		/**
		 * convert the feed data of a single product into xml or csv text depending on the channel
		 * 
		 * @param array $data
		 * @param array $channel_details
		 * @param string $file_extension
		 * @param array $active_fields
		 * @return string
		 */
		private function generate_feed_text( $data, $channel_details, $file_extension, $active_fields ) {
			switch ( $file_extension ) {
				case 'xml':
	 				return $this->feed_class->convert_data_to_xml( $data, $channel_details['category_name'], $channel_details['description_name'], $this->_feed->channel );

				case 'txt':
					$first_row = $this->_product_counter > 0 ? false : true;
					return $this->feed_class->convert_data_to_txt( $data, $first_row );
				
				case 'csv':
					$csv_sep = get_correct_csv_separator( $this->_feed->channel );
					return $this->convert_data_to_csv( $data, $active_fields, $csv_sep );
			}
		}

		/**
		 * makes an xml string of one product including its variations
		 * 
		 * @param array $data
		 * @param string $category_name
		 * @param string $description_name
		 * @return string
		 */
		public function convert_data_to_xml( $data, $category_name, $description_name, $channel ) {
			return $data ? $this->make_xml_string_row( $data, $category_name, $description_name, $channel ) : '';
		}

		/**
		 * makes an csv string of one product including its variations
		 * 
		 * @param array $data
		 * @return string
		 */
		private function convert_data_to_csv( $data, $active_fields, $csv_separator ) {
			if ( $data ) {
				// the first row in a csv file should contain the index, the following rows the data
				return $this->_product_counter > 0 ? $this->make_comma_separated_string_from_data_array( $data, $active_fields, $csv_separator ) :
					$this->make_csv_header_string( $active_fields, $csv_separator );
			} else { return ''; }
		}
		
		public function convert_data_to_txt( $data, $first_row ) {
			if ( $data ) {
				// the first row in a txt file should contain the index, the following rows the data
				$txt_data = !$first_row ? $data : array_keys( $data );
				return $this->make_tab_delimited_string_from_data_array( $txt_data );
			} else { return ''; }
		}
		
		/**
		 * takes one row data and converts it to a tab delimited string
		 * 
		 * @param array $row_data
		 * @return string
		 */
		protected function make_tab_delimited_string_from_data_array( $row_data ) {
			$row_string = '';
			
			foreach ( $row_data as $row_item ) {
				$a_row_item = !is_array( $row_item ) ? preg_replace( "/\r|\n/", "", $row_item ) : implode( ', ', $row_item );
				$clean_row_item = strip_tags( $a_row_item );
				$row_string .= $clean_row_item . "\t";
			}

			$row = trim( $row_string ); // removes the tab at the end of the line
			
			return $row . "\r\n";
		}

		/**
		 * takes one row data and converts it to a comma separated string
		 * 
		 * @param object $row_data
		 * @param array $active_fields
		 * @return string
		 */
		protected function make_comma_separated_string_from_data_array( $row_data, $active_fields, $separator = ',' ) {
			$row_string = '';
			
			foreach ( $active_fields as $row_item ) {
				if( array_key_exists( $row_item, $row_data ) ) {
					$clean_row_item = !is_array( $row_data[$row_item] ) ? preg_replace( "/\r|\n/", "", $row_data[$row_item] ) : implode( ', ', $row_data[$row_item] );
				} else {
					$clean_row_item = '';
				}
				
//				$clean_row_item = array_key_exists( $row_item, $row_data ) ? preg_replace( "/\r|\n/", "", $row_data[$row_item] ) : "";
				$no_double_quote_item = str_replace( '"', "'", $clean_row_item );
				$row_string .= '"'.$no_double_quote_item.'"' . $separator;
			}

			$row = rtrim( $row_string, $separator ); // removes the comma at the end of the line
			
			return $row . "\r\n";
		}

		/**
		 * makes the header string for a csv file
		 * 
		 * @param array $active_fields
		 * @return string
		 */
		protected function make_csv_header_string( $active_fields, $separator ) {
			$header = implode( $separator, $active_fields );
			return $header . "\r\n";
		}

		/**
		 * makes an xml string for one product
		 * 
		 * @param array $product
		 * @param string $category_name
		 * @param string $description_name
		 * @return string
		 */
		protected function make_xml_string_row( $product, $category_name, $description_name, $channel ) {
			$product_node = function_exists( 'product_node_name' ) ? product_node_name( $channel ) : 'item';
			$node_pre_tag = function_exists( 'get_node_pretag' ) ? get_node_pretag( $channel ) : 'g:';
//			$this->feed_class->add_xml_sub_tags( $product );
			$this->add_xml_sub_tags( $product );
			$xml_string = "<$product_node>";

			// for each product value item
			foreach ( $product as $key => $value ) {
				if ( !is_array( $value ) ) {
					$xml_string .= $this->make_xml_string( $key, $value, $category_name, $description_name, $node_pre_tag, $channel );
				} else {
					$xml_string .= $this->make_array_string( $key, $value, $node_pre_tag, $channel );
				}
			}

			$xml_string .= "</$product_node>";

			return $xml_string;
		}

		/**
		 * make an array of product element strings
		 * 
		 * @param string $key
		 * @param string $value
		 * @param string $google_node_pre_tag
		 * @return string
		 */
		private function make_array_string( $key, $value, $google_node_pre_tag, $channel ) {
			$xml_strings = '';

			for ( $i = 0; $i < count( $value ); $i++ ) {
				$xml_key = $key === 'Extra_Afbeeldingen' ? 'Extra_Image_' . ( $i + 1 ) : $key; // required for Beslist.nl
				$xml_strings .= $this->make_xml_string( $xml_key, $value[ $i ], '', '', $google_node_pre_tag, $channel );
			}

			return $xml_strings;
		}

		/**
		 * makes a single product item string for xml use
		 * 
		 * @param string $key
		 * @param string $value
		 * @param string $category_name
		 * @param string $description_name
		 * @param string $google_node_pre_tag
		 * @return string
		 */
		private function make_xml_string( $key, $value, $category_name, $description_name, $google_node_pre_tag, $channel ) {
			$xml_string = '';
			
//			$xml_value = ! in_array( $key, $this->feed_class->keys_that_have_sub_tags() ) ? $this->convert_to_xml_value( $value ) : $value;
			$xml_value = ! in_array( $key, $this->keys_that_have_sub_tags() ) ? $this->convert_to_xml_value( $value ) : $value;
			
			if ( substr( $xml_value, 0, 5 ) === '!sub:' ) {
				$sub_array = explode( "|", $xml_value );
				$sa = $sub_array[0];
				$st = explode( ":", $sa );
				$sub_tag = $st[1];
				$xml_value = "<$google_node_pre_tag$sub_tag>$sub_array[1]</$google_node_pre_tag$sub_tag>";
			}

			// LET OP!! Meer keys in de datastring zetten!!!
			if ( $key === $category_name || $key === $description_name || $key === 'title' ) { // put the category and description in a ![CDATA[...]] bracket
				$xml_value = $this->convert_to_character_data_string( $xml_value );
			}
			
			if ( $key !== '' ) {
				// as of October 2016 google removed the need for a g: suffix only for title and link. Facebook still requires it
				if ( $key === 'title' || $key === 'link' ) { $google_node_pre_tag = $channel === '1' ? '' : $google_node_pre_tag; }

				$not_allowed_characters	 = array( ' ', '-' );
				$key					 = str_replace( $not_allowed_characters, '_', $key );

				$xml_string = "<$google_node_pre_tag$key>$xml_value</$google_node_pre_tag$key>";
			}

			return $xml_string;
		}
		
		/**
		 * return an empty array
		 * 
		 * @return array
		 */
		protected function keys_that_have_sub_tags() { return array(); }

		/**
		 * replaces certain characters to get a valid xml value
		 * 
		 * @param string $value_string
		 * @return string
		 */
		private function convert_to_xml_value( $value_string ) {
			$string_without_tags = strip_tags( $value_string );
			$prep_string = str_replace( array( '&amp;', '&lt;', '&gt;', '&apos;', '&quot;', '&nbsp;' ), array( '&', '<', '>', '\'', '"', 'nbsp;' ), $string_without_tags );
			$clean_xml_string = str_replace( array( '&', '<', '>', '\'', '"', 'nbsp;', '`' ), array( '&amp;', '&lt;', '&gt;', '&apos;', '&quot;', '&nbsp;', '' ), $prep_string );

			return $clean_xml_string;
		}
		
		/**
		 * gather all the information required to get the feed data
		 * 
		 * @return array
		 */
		private function get_required_pre_data() {
			
			// get the feed query string if the user has added to filter out specific products from the feed (Paid version only)
			$feed_filter = $this->data_class->get_filter_query( $this->_feed->feedId );
			
			// get an array with all the field names that are required to make the feed (including the source fields, fields for the queries and fields for static data)
			$required_column_names = $this->get_column_names_required_for_feed( $this->_feed->attributes, $feed_filter );

			// get the relations between the column names (input) and feed fields (output) eg _sku is linked to id, title is linked to post_title
			$field_relation_table = $this->get_channel_to_woocommerce_field_relations( $this->_feed->attributes );

			// get the fields that are active and have to go into the feed
			$active_fields = $this->get_active_fields( $this->_feed->attributes );
			
			$database_fields = $this->get_database_fields( $required_column_names );
			
			return array(
				'filters'			=> $feed_filter,
				'column_names'		=> $required_column_names,
				'field_relations'	=> $field_relation_table,
				'active_fields'		=> $active_fields,
				'database_fields'	=> $database_fields
			);
		}
		
		/**
		 * adds data to the product that require a procedure to get
		 * 
		 * @param object $product
		 * @param array $active_field_names
		 */
		private function add_procedural_data( &$product, $active_field_names ) {
			if ( in_array( 'permalink', $active_field_names ) ) {
				$product->permalink = get_permalink( $product->ID );
			}

			if ( in_array( 'attachment_url', $active_field_names ) ) {
				$product->attachment_url = wp_get_attachment_url( get_post_thumbnail_id( $product->ID ) );
			}

			if ( in_array( 'product_cat', $active_field_names ) ) {
				$product->product_cat = WPPFM_Categories_Class::get_shop_categories( $product->ID );
			}

			if ( in_array( 'product_cat_string', $active_field_names ) ) {
				$product->product_cat_string = WPPFM_Categories_Class::make_shop_category_string( $product->ID );
			}

			if ( in_array( 'last_update', $active_field_names ) ) {
				$product->last_update = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
			}

			if ( in_array( '_wp_attachement_metadata', $active_field_names ) ) {
				$product->_wp_attachement_metadata = $this->get_product_image_galery( $product->ID );
				//$product->_wp_attachement_metadata = wp_get_attachment_url( get_post_thumbnail_id( $product->is_type( 'variation' ) ? $product->variation_id : $product->id ) );
			}

			if ( in_array( 'product_tags', $active_field_names ) ) {
				$product->product_tags = $this->get_product_tags( $product->ID );
			}

			if ( in_array( 'wc_currency', $active_field_names ) ) {
				$product->wc_currency = get_woocommerce_currency();
			}

			if ( in_array( 'item_group_id', $active_field_names ) ) {
				$prdct = wc_get_product( $product->ID );
				if( $prdct ) { $product->item_group_id = $prdct->is_type( 'variable' ) || $prdct->is_type( 'variation' ) ? 'GID' . $product->ID : ''; }

// 070417
//				if ( $prdct instanceof WC_Product_Simple || $prdct instanceof WC_Product_Variable ) {
//					$product->item_group_id = $prdct->is_type( 'variable' ) || $prdct->is_type( 'variation' ) ? 'GID' . $product->ID : '';
//				} else {
//					$product->item_group_id = '';
//				}
			}

			if ( in_array( 'shipping_class', $active_field_names ) ) {
				$prdct = wc_get_product( $product->ID );
				if( $prdct ) { $product->shipping_class = $prdct->get_shipping_class(); }

				// 070417
//				if ( $prdct instanceof WC_Product_Simple || $prdct instanceof WC_Product_Variable ) {
//					$product->shipping_class = $prdct->get_shipping_class(); 
//				}
			}
		}
		
		/**
		 * gather all required column names from the database
		 * 
		 * @param array $active_field_names
		 * @return array
		 */
		private function get_database_fields( $active_field_names ) {

			$queries_class = new WPPFM_Queries();
			
			$post_fields			 = array();
			$meta_fields			 = array();
			$custom_fields			 = array();
			$active_custom_fields	 = array();
			$active_third_party_custom_fields = array();
			$post_columns_string	 = '';

			$colums_in_post_table	 = $queries_class->get_columns_from_post_table(); // get all post table column names
			$all_custom_columns		 = $queries_class->get_custom_product_attributes(); // get all custom name labels
			$third_party_custom_fields = $this->data_class->get_third_party_custom_fields();
			
			// convert the query results to an array with only the name labels
			foreach ( $colums_in_post_table as $column ) {
				array_push( $post_fields, $column->Field );
			} // $post_fields containing the required names from the post table
			foreach ( $all_custom_columns as $custom ) {
				array_push( $custom_fields, $custom->attribute_name );
			} // $custom_fields containing the custom names
			// filter the post columns, the meta columns and the custom columns to only those that are actually in use
			
			foreach ( $active_field_names as $column ) {

				if ( in_array( $column, $post_fields ) && $column !== 'ID' ) { // because ID is always required, it's excluded here and hardcoded in the query
					$post_columns_string .= $column . ', '; // here a string is required to push in the query
				} elseif ( in_array( $column, $custom_fields ) ) {
					array_push( $active_custom_fields, $column );
				} elseif ( in_array( $column, $third_party_custom_fields ) ) {
					array_push( $active_third_party_custom_fields, $column );
				} else {
					array_push( $meta_fields, $column );
				}
			}
			
			return array(
				'post_column_string' => $post_columns_string,
				'meta_fields' => $meta_fields,
				'active_custom_fields' => $active_custom_fields,
				'third_party_custom_fields' => $third_party_custom_fields
			);
		}

		/**
		 * returns the column names from the database that are required to get the data necessary to make the feed
		 * 
		 * @param array $attributes
		 * @param object $feed_filter_object
		 * @return array
		 */
		private function get_column_names_required_for_feed( $attributes, $feed_filter_object ) {
			$support_class = new WPPFM_Feed_Support_Class();
			
			$fields = array();
			$filter_columns = $support_class->get_column_names_from_feed_filter_array( $feed_filter_object);

			foreach ( $attributes as $attribute ) {
				if ( $attribute->fieldName !== 'category_mapping' ) {
					$column_names = $this->get_db_column_name_from_attribute( $attribute );
					foreach ( $column_names as $name ) { if ( !empty( $name ) ) { array_push( $fields, $name ); } }
				}
			}
			
			$result = array_unique( array_merge( $fields, $filter_columns ) ); // remove doubles
			
			if ( empty( $result ) ) { wppfm_write_log_file( "Function get_column_names_required_for_feed returned zero columns" ); }

			return array_merge( $result ); // and resort the result before returning
		}

		/**
		 * returns all active column names that are stored in the feed attributes
		 * 
		 * @param array $attribute
		 * @return array
		 */
		private function get_db_column_name_from_attribute( $attribute ) {

			$column_names = array();

			if ( property_exists( $attribute, 'isActive' ) && $attribute->isActive ) { // only select the active attributes

				// source columns
				if ( ! empty( $attribute->value ) ) {

					$source_columns		 = $this->get_source_columns_from_attribute_value( $attribute->value );
					$condition_columns	 = $this->get_condition_columns_from_attribute_value( $attribute->value );
					$query_columns		 = $this->get_queries_columns_from_attribute_value( $attribute->value );

					// TODO: Volgens mij kan de eerste $column_names array wel uit de array_merge
					$column_names = array_merge( $column_names, $source_columns, $condition_columns, $query_columns );
				}

				// advised sources
				if ( ! empty( $attribute->advisedSource ) 
					&& strpos( $attribute->advisedSource, 'Fill with a static value' ) === false 
					&& strpos( $attribute->advisedSource, 'Use the settings in the Merchant Center' ) === false ) {

					// add the relevant advised sources
					array_push( $column_names, $attribute->advisedSource );
				} elseif ( property_exists ( $attribute, 'advisedSource' ) 
					&& strpos( $attribute->advisedSource, 'Use the settings in the Merchant Center' ) !== false ) {
					
					array_push( $column_names, 'woo_shipping' );
				}
			}

			return $column_names;
		}

		/**
		 * retrieve the selected catagories from the categoryMapping object
		 * 
		 * @param object $category_mapping
		 * @return string
		 */
		private function make_category_selection_string( $category_mapping ) {

			$category_selection_string = '';

			foreach ( $category_mapping as $category ) {
				$category_selection_string .= $category->shopCategoryId . ', ';
			}

			return $category_selection_string ? substr( $category_selection_string, 0, -2 ) : '';
		}

		/**
		 * get an array with the relations between the woocommerce fields and the channel fields
		 * 
		 * @param array $attributes
		 * @return array
		 */
		private function get_channel_to_woocommerce_field_relations( $attributes ) {

			$relations = array();

			foreach ( $attributes as $attribute ) {

				// get the source name except for the category_mapping field
				if ( $attribute->fieldName !== 'category_mapping' ) {
					$source = $this->get_source_from_attribute( $attribute );
				}

				if ( ! empty( $source ) ) {

					// correct googles product category source
					if ( $attribute->fieldName === 'google_product_category' ) {
						$source = 'google_product_category';
					}

					// correct googles identifier exists source
					if ( $attribute->fieldName === 'identifier_exists' ) {
						$source = 'identifier_exists';
					}

					// fill the relations array
					$a = array( 'field' => $attribute->fieldName, 'db' => $source );
					array_push( $relations, $a );
				}
			}

			if ( empty( $relations ) ) { wppfm_write_log_file( "Function get_channel_to_woocommerce_field_relations returned zero relations." ); }

			return $relations;
		}

		/**
		 * extract the source name from the attribute string
		 * 
		 * @param string $attribute
		 * @return string
		 */
		private function get_source_from_attribute( $attribute ) {

			$source = '';

			$value_source = property_exists( $attribute, 'value' ) ? $this->get_source_from_attribute_value( $attribute->value ) : '';

			if ( ! empty( $value_source ) ) {
				$source = $value_source;
			} elseif ( array_key_exists( 'advisedSource', $attribute ) && $attribute->advisedSource !== '' ) {
				$source = $attribute->advisedSource;
			} else {
				$source = $attribute->fieldName;
			}

			return $source;
		}

		/**
		 * extract the source value from the attribute string
		 * 
		 * @param string $value
		 * @return string
		 */
		private function get_source_from_attribute_value( $value ) {

			$source = '';

			if ( $value ) {

				$value_string = $this->get_source_string( $value );

				$value_object = json_decode( $value_string );

				if ( is_object( $value_object ) && property_exists( $value_object, 'source' ) ) { $source = $value_object->source; }
			}

			return $source;
		}

		/**
		 * get the value
		 * 
		 * @param string $value_string
		 * @return string
		 */
		private function get_source_string( $value_string ) {

			$source_string = '';

			if ( ! empty( $value_string ) ) {

				$value_object = json_decode( $value_string );

				if ( property_exists( $value_object, 'm' ) && property_exists( $value_object->m[ 0 ], 's' ) ) {
					$source_string = json_encode( $value_object->m[ 0 ]->s );
				}
			}

			return $source_string;
		}

		/**
		 * extract the active fields from the attributes
		 * 
		 * @param array $attributes
		 * @return array
		 */
		private function get_active_fields( $attributes ) {

			$active_fields = array();

			foreach ( $attributes as $attribute ) {

				if ( $attribute->isActive && $attribute->fieldName !== 'category_mapping' ) {

					$push = false;

					if ( $attribute->fieldLevel === '1' ) {

						$push = true;
					} else {

						$value_object = property_exists( $attribute, 'value' ) ? json_decode( $attribute->value ) : new stdClass();

						if ( !empty( $attribute->value ) && property_exists( $value_object, 'm' ) && key_exists( 's', $value_object->m[ 0 ] ) ) {
							$push = true;
						} elseif ( !empty( $attribute->advisedSource ) ) {
							$push = true;
						} elseif ( !empty( $attribute->value ) && property_exists( $value_object, 't' ) ) {
							$push = true;
						} elseif ( !empty( $attribute->value ) && property_exists( $value_object, 'v' ) ) {
							$push = true;
						}
					}

					if ( $push === true ) { array_push( $active_fields, $attribute->fieldName ); }
				}
			}

			if ( empty( $active_fields ) ) { wppfm_write_log_file( "Function get_active_fields returned zero fields." ); }

			return $active_fields;
		}
		
		/**
		 * returns the url to the feed file including feed name and extension
		 * 
		 * @param string $feed_name
		 * @return string
		 */
		private function get_file_url( $feed_name ) {
			
//			$upload_dir = wp_upload_dir();
//
//			// wp_upload_dir does not work with https
//			if ( is_ssl() ) {
//				$upload_folder = str_replace( 'http://', 'https://', $upload_dir['baseurl'] );
//			} else {
//				$upload_folder = $upload_dir['baseurl'];
//			}
			
			// previous to plugin version 1.3.0 feeds where stored in the plugins but after that version they are stored in the upload folder
			if( file_exists( WP_PLUGIN_DIR . '/wp-product-feed-manager-support/feeds/' . $feed_name ) ) {
				
				return plugins_url() . '/wp-product-feed-manager-support/feeds/' . $feed_name;
			} elseif( file_exists( WPPFM_FEEDS_DIR . '/' . $feed_name ) ) {
				
				//$upload_dir = wp_upload_dir();
				return WPPFM_UPLOADS_URL . '/wppfm-feeds/' . $feed_name;
			} else { // as of version 1.5.0 all spaces in new filenames are replaced by a dash
				
				$forbitten_name_chars = array( ' ', '<', '>', ':', '?', ',' ); // characters that are not allowed in a feed file name
				//$upload_dir = wp_upload_dir();
				return WPPFM_UPLOADS_URL . '/wppfm-feeds/' . str_replace( $forbitten_name_chars, '-', $feed_name);
			}
		}
		
		/**
		 * returns the path to the feed file including feed name and extension
		 * 
		 * @param string $feed_name
		 * @return string
		 */
		private function get_file_path( $feed_name ) {

			// previous to plugin version 1.3.0 feeds where stored in the plugins but after that version they are stored in the upload folder
			if( file_exists( WP_PLUGIN_DIR . '/wp-product-feed-manager-support/feeds/' . $feed_name ) ) {
				return WP_PLUGIN_DIR . '/wp-product-feed-manager-support/feeds/' . $feed_name;
			} elseif( file_exists( WPPFM_FEEDS_DIR . '/' . $feed_name ) ) {
				return WPPFM_FEEDS_DIR . '/' . $feed_name;
			} else { // as of version 1.5.0 all spaces in new filenames are replaced by a dash
				$forbitten_name_chars = array( ' ', '<', '>', ':', '?', ',' ); // characters that are not allowed in a feed file name
				return WPPFM_FEEDS_DIR . '/' . str_replace( $forbitten_name_chars, '-', $feed_name);
			}
		}

		/**
		 * return an array with source column names from an attribute string
		 * 
		 * @param string $value_string
		 * @return array
		 */
		private function get_source_columns_from_attribute_value( $value_string ) {

			$source_columns = array();

			$value_object = json_decode( $value_string );

			if ( property_exists( $value_object, 'm' ) ) {

				foreach ( $value_object->m as $source ) {

					// TODO: Volgens mij kan ik de volgende "if" loops nog verder combineren
					if ( property_exists( $source, 's' ) ) {

						if ( property_exists( $source->s, 'source' ) ) {

							if ( $source->s->source !== 'combined' ) {
								array_push( $source_columns, $source->s->source );
							} else {

								if ( property_exists( $source->s, 'f' ) ) {
									$source_columns = array_merge( $source_columns, $this->get_combined_sources_from_combined_string( $source->s->f ) );
								}
							}
						}
					}
				}
			}

			return $source_columns;
		}

		/**
		 * split the combined string into single combination items
		 * 
		 * @param string $combined_string
		 * @return array
		 */
		private function get_combined_sources_from_combined_string( $combined_string ) {

			$result					 = array();
			$combined_string_array	 = explode( '|', $combined_string );

			array_push( $result, $combined_string_array[ 0 ] );

			for ( $i = 1; $i < count( $combined_string_array ); $i++ ) {

				$a = explode( '#', $combined_string_array[ $i ] );
				array_push( $result, $a[ 1 ] );
			}

			return $result;
		}

		/**
		 * return an array with condition column names from an attribute string
		 * 
		 * @param string $value_string
		 * @return array
		 */
		private function get_condition_columns_from_attribute_value( $value_string ) {

			$condition_columns = array();

			$value_object = json_decode( $value_string );

			if ( property_exists( $value_object, 'm' ) ) {

				foreach ( $value_object->m as $source ) {

					if ( property_exists( $source, 'c' ) ) {

						for ( $i = 0; $i < count( $source->c ); $i++ ) {
							array_push( $condition_columns, $this->get_column_names_from_condition_string( $source->c[ $i ]->{$i + 1} ) );
						}
					}
				}
			}

			return $condition_columns;
		}

		/**
		 * extract the column name from the condition string
		 * 
		 * @param string $condition_string
		 * @return array
		 */
		private function get_column_names_from_condition_string( $condition_string ) {

			$condition_string_array = explode( '#', $condition_string );

			return $condition_string_array[ 1 ];
		}

		/**
		 * return an array with query column names from an attribute string
		 * 
		 * @param type $value_string
		 * @return array
		 */
		private function get_queries_columns_from_attribute_value( $value_string ) {

			$query_columns = array();

			$value_object = json_decode( $value_string );

			if ( property_exists( $value_object, 'v' ) ) {

				foreach ( $value_object->v as $changed_value ) {

					if ( property_exists( $changed_value, 'q' ) ) {

						for ( $i = 0; $i < count( $changed_value->q ); $i++ ) {
							array_push( $query_columns, $this->get_column_names_from_query_string( $changed_value->q[ $i ]->{$i + 1} ) );
						}
					}
				}
			}

			return $query_columns;
		}

		/**
		 * extract the column name from the query string
		 * 
		 * @param string $query_string
		 * @return array
		 */
		private function get_column_names_from_query_string( $query_string ) {

			$condition_string_array = explode( '#', $query_string );

			return $condition_string_array[ 1 ];
		}

		/**
		 * get formal woocommerce custom fields data
		 * 
		 * @param string $id
		 * @param string $field
		 * @return string
		 */
		private function get_custom_field_data( $id, $field ) {

			$custom_string	 = '';
			$taxonomy		 = 'pa_' . $field;
			$custom_values	 = get_the_terms( $id, $taxonomy );

			if ( $custom_values ) {
				foreach ( $custom_values as $custom_value ) { $custom_string .= $custom_value->name . ', ';	}
			}

			return $custom_string ? substr( $custom_string, 0, -2 ) : '';
		}

		/**
		 * get additional images
		 * 
		 * @param string $post_id
		 * @return array
		 */
		private function get_product_image_galery( $post_id ) {

			$image_urls		 = array();
			$images			 = 1;
			$max_nr_images	 = 10;
//			$args			 = array(
//				'post_type'		 => 'attachment',
//				'numberposts'	 => -1,
//				'post_status'	 => 'any',
//				'post_parent'	 => $post_id,
//				'exclude'		 => get_post_thumbnail_id( $post_id )
//			);
//
//			$attachments = get_posts( $args );
//
//			if ( $attachments ) {
//				foreach ( $attachments as $attachment ) {
//
//					array_push( $image_urls, $attachment->guid );
//					$images++;
//
//					if ( $images > $max_nr_images ) { break; }
//				}
//			}

			$prdct = wc_get_product( $post_id );
			$attachment_ids = $prdct->get_gallery_attachment_ids();
			
			foreach( $attachment_ids as $attachment ) {
				
				$image_link = wp_get_attachment_url( $attachment );
				array_push( $image_urls, $image_link );
				$images++;

				if ( $images > $max_nr_images ) { break; }
			}

			return $image_urls;
		}

		private function get_product_tags( $id ) {

			$product_tags_string = '';
			$product_tag_values	 = get_the_terms( $id, 'product_tag' );
			$post_tag_values	 = get_the_tags( $id );

			if ( $product_tag_values ) {
				foreach ( $product_tag_values as $product_tag ) {

					$product_tags_string .= $product_tag->name . ', ';
				}
			}

			if ( $post_tag_values ) {
				foreach ( $post_tag_values as $post_tag ) {

					$product_tags_string .= $post_tag->name . ', ';
				}
			}

			return $product_tags_string ? substr( $product_tags_string, 0, -2 ) : '';
		}

		private function get_meta_parent_ids( $feed_id ) {

			$queries_class = new WPPFM_Queries();

			$query_result	 = $queries_class->get_meta_parents( $feed_id );
			$ids			 = array();

			foreach ( $query_result as $result ) {

				array_push( $ids, $result[ 'ID' ] );
			}

			return $ids;
		}
		
		/**
		 * get third party custom field data
		 * 
		 * @param string $feed_id
		 * @param string $field
		 * @return string
		 */
		private function get_third_party_custom_field_data( $feed_id, $field ) {
			
			$result = '';
			
			// YITH Brands plugin
			if ( $field === get_option( 'yith_wcbr_brands_label' ) ) { // YITH Brands plugin active
			
				if ( has_term( '', 'yith_product_brand', $feed_id ) ) {
					$product_brand = get_the_terms( $feed_id, 'yith_product_brand' );

					foreach ( $product_brand as $brand ) { $result .= $brand->name . ', ';	}
				}
			}
			
			// WooCommerce Brands plugin
			if ( in_array( 'woocommerce-brands/woocommerce-brands.php', apply_filters( 'active_plugins', 
				get_option( 'active_plugins' ) ) ) ) { 
				
				if ( has_term( '', 'product_brand', $feed_id ) ) {
					$product_brand = get_the_terms( $feed_id, 'product_brand' );

					foreach ( $product_brand as $brand ) { $result .= $brand->name . ', '; }
				}
			}
			
			return $result ? substr( $result, 0, -2 ) : '';
		}
		
	}

	
	
     // end of WPPFM_Feed_Master_Class

endif;