<?php
/**
 * WP Product Feed Manager Administrative functions
 *
 * Functions for Administrative actions
 *
 * @author 		Michel Jongbloed
 * @category 	Cron
 * @package 	Application
 * @version     2.1
 */

// Prevent direct access
if (!defined('ABSPATH')) exit;

/**
 * Converts a string containing a date-time stamp as stored in the meta data to a date time string
 * that can be used in a feed file
 * 
 * @since 1.1.0
 * 
 * @param string	$datestamp	The timestamp that needs to be converted to a string that can be stored in a feed file
 * 
 * @return string	A string containing the time or an empty string if the $datestamp is empty
 */
function convert_price_date_to_feed_format( $datestamp ) {
	if ( $datestamp ) {
		// register the date
		$feed_string = date( 'Y-m-d', $datestamp );

		// if set, add the time
		if ( date( 'H', $datestamp ) !== '00' || date( 'i', $datestamp ) !== '00' || date( 's', $datestamp ) !== '00' ) {
			$feed_string .= 'T' . date( 'H:i:s', $datestamp );
		}
		
		return $feed_string;
	} else {
		return '';
	}
}

/**
 * After a channel has been updated this function decreases the 'wppfm_channels_to_update' option with one
 * 
 * @since 1.4.1
 */
function decrease_updatable_channels() {
	$old = get_option( 'wppfm_channels_to_update' );

	if ( $old > 0 ) { update_option( 'wppfm_channels_to_update', $old - 1 ); } 
	else { update_option( 'wppfm_channels_to_update', 0 ); }
}

/**
 * Checks if a specific source key is a money related key or not
 * 
 * @since 1.1.0
 * 
 * @param string	$key	The source key to be checked
 * @return boolean	True if the source key is money related, false if not
 */
function meta_key_is_money( $key ) {
	// money keys
	$special_price_keys = array(
		'_max_variation_price',
		'_max_variation_regular_price',
		'_max_variation_sale_price',
		'_min_variation_price',
		'_min_variation_regular_price',
		'_min_variation_sale_price',
		'_regular_price',
		'_sale_price' );

	return in_array( $key, $special_price_keys ) ? true : false;
}
		
/**
 * Takes a value and formats it to a money value using the WooCommerce thousands separator, decimal separator and number of decimals values
 * 
 * @since 1.1.0
 * 
 * @param string	$money_value	The money value to be formated
 * @return string	A formated money value
 */
function prep_money_values( $money_value ) {
	$mon_val = is_float( $money_value ) ? $money_value : floatval( $money_value );
	$number_decimals = absint( get_option( 'woocommerce_price_num_decimals', 2 ) );
	$decimal_point = get_option( 'woocommerce_price_decimal_sep' );
	$thousand_separator = get_option( 'woocommerce_price_thousand_sep' );

	return number_format( $mon_val, $number_decimals, $decimal_point, $thousand_separator );
}

/**
 * Checks if there are invalid backups
 * 
 * @since 1.8.0
 * 
 * @return boolean true if there are no backups or these backups are current
 */
function wppfm_check_backup_status() {
	if ( !WPPFM_Db_Management::invalid_backup_exist() ) return true;
}