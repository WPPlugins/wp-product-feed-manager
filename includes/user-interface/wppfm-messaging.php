<?php
/**
 * WP Product Feed Manager Messaging functions
 *
 * Functions for handling messages
 *
 * @author 		Michel Jongbloed
 * @category 	Messages
 * @package 	User-interface
 * @version     2.2
 */

// Prevent direct access
if (!defined('ABSPATH')) exit;

function wppfm_show_wp_error( $message, $dismissible = false, $permanent_dismissible = false ) {
	return wppfm_show_wp_message( $message, 'error', $dismissible, $permanent_dismissible );
}

function wppfm_show_wp_warning( $message, $dismissible = false, $permanent_dismissible = false ) {
	return wppfm_show_wp_message( $message, 'warning', $dismissible, $permanent_dismissible );
}

function wppfm_show_wp_success( $message, $dismissible = false, $permanent_dismissible = false ) {
	return wppfm_show_wp_message( $message, 'success', $dismissible, $permanent_dismissible );
}

function wppfm_show_wp_info( $message, $dismissible = false, $permanent_dismissible = false ) {
	return wppfm_show_wp_message( $message, 'info', $dismissible, $permanent_dismissible );
}

function wppfm_show_wp_message( $message, $type, $dismissible, $permanent_dismissible ) {
	//$dism = $dismissible && !$permanent_dismissible ? ' is-dismissible' : '';
	$dism = $dismissible ? ' is-dismissible' : '';
	$perm_dism = $permanent_dismissible ? ' id="disposible-warning-message"' : '';
	$dismiss_button = $permanent_dismissible ? '<button type="button" id="disposible-notice-button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>' : '';
	
	return '<div' . $perm_dism . ' class="notice notice-' . $type . $dism . '"><p>'  . $message . '</p>' . $dismiss_button . '</div>';
}

/**
 * enables writing log files in the plugin folder
 * 
 * @since 1.5.1
 * 
 * @param string $error_message
 * @param string $filename
 */
function wppfm_write_log_file( $error_message, $filename = 'error' ) {
	$file = fopen( MYPLUGIN_PLUGIN_DIR . $filename . '.log', "a");

	if ( $file ) {
		if ( is_null( $error_message ) || is_string( $error_message ) || is_int( $error_message ) || is_bool( $error_message ) || is_float( $error_message ) ) {
			$message_line = $error_message;
		} elseif ( is_array( $error_message ) || is_object( $error_message ) ) {
			$message_line = json_encode( $error_message );
		} else {
			$message_line = "ERROR! Could not write messages of type " . gettype( $error_message ) ;
		}

		fwrite( $file, date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ) . ' - ' . ucfirst($filename) . ' Message: ' . $message_line . PHP_EOL );
		
		fclose($file);
	} else {
		wppfm_show_wp_error( __( "There was an error but I was unable to store the error message in the log file. The message was $error_message", 'wp-product-feed-manager' ) );
	}
}

/**
 * allows safe debugging on a operational server of a user
 */
class MJ {

	static function log( $var ) {

		?>
		<style>
			.mj_debug { word-wrap: break-word; white-space: pre; text-align: left; position: relative; 
					   background-color: rgba(0, 0, 0, 0.8); font-size: 11px; color: #a1a1a1; margin: 10px; 
					   padding: 10px; margin: 0 auto; width: 80%; overflow: auto; -moz-box-shadow:0 10px 40px rgba(0, 0, 0, 0.75); 
					   -webkit-box-shadow:0 10px 40px rgba(0, 0, 0, 0.75); -moz-border-radius: 5px; -webkit-border-radius: 5px; text-shadow: none; }
		</style>
		<br /><pre class="mj_debug">

		<?php
		if ( is_null( $var ) || is_string($var) || is_int( $var ) || is_bool($var) || is_float( $var ) ) :
			var_dump( $var );

		else :
			print_r( $var );

		endif;

		echo '</pre><br />';
	}
	
	static function log_channel_table() {
		$queries_class = new WPPFM_Queries();
		self::log( $queries_class->get_feedmanager_channel_table() );
	}
	
	static function log_product_feed_table() {
		$queries_class = new WPPFM_Queries();
		self::log( $queries_class->get_feedmanager_product_feed_table() );
	}
	
	static function log_product_feedmeta_table() {
		$queries_class = new WPPFM_Queries();
		self::log( $queries_class->get_feedmanager_product_feedmeta_table() );
	}
}