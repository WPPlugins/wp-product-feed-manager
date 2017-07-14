<?php

/* * ******************************************************************
 * Version 3.2
 * Modified: 21-05-2017
 * Copyright 2017 Accentio. All rights reserved.
 * License: None
 * By: Michel Jongbloed
 * ****************************************************************** */

// Prevent direct access
if ( !defined( 'ABSPATH' ) ) exit;


if ( !class_exists( 'WPPFM_Schedules' ) ) :

	/**
	 * The WPPFM_Schedules class contains the functions that perform scheduled tasks like updating the active feeds
	 * 
	 * @class		WPPFM_Schedules
	 * @version		3.1
	 * @category	Class
	 * @author		Michel Jongbloed
	 */
	class WPPFM_Schedules {

		/* --------------------------------------------------------------------------------------------------*
		 * Public functions
		 * -------------------------------------------------------------------------------------------------- */

		/**
		 * Initiates the automatic feed updates
		 * 
		 * @param bool $silent
		 */
		public function update_active_feeds( $silent = false ) {
			$data_class = new WPPFM_Data_Class();

			$current_timestamp = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
			$active_feeds_schedules = $data_class->get_schedule_data();
			$failed_feeds = $data_class->get_failed_feeds();

			// update scheduled feeds
			foreach ( $active_feeds_schedules as $schedule ) {
				$update_time = $this->new_activation_time( $schedule[ 'updated' ], $schedule[ 'schedule' ] );
				
				// $feed_data should be in the same format as the data that comes from the javascript _feedHolder
				// the function get_feed_data fetches the data from the database and converts it to the same
				// format as the _feedHolder has.
				$feed_data = $data_class->get_feed_data( $schedule[ 'product_feed_id' ] );

				// activate the feed update when the update time is reached
				if ( $update_time < $current_timestamp )
					$this->auto_update_feed( $feed_data, $silent );
			}
			
			// update previously failed feeds
			if ( "true" === get_option( 'wppfm_auto_feed_fix' ) ) {
				foreach ( $failed_feeds as $failed_feed ) {
					// $feed_data should be in the same format as the data that comes from the javascript _feedHolder
					// the function get_feed_data fetches the data from the database and converts it to the same
					// format as the _feedHolder has.
					$feed_data = $data_class->get_feed_data( $failed_feed[ 'product_feed_id' ] );

					$this->auto_update_feed( $feed_data, $silent);
				}
			}
		}

		/* --------------------------------------------------------------------------------------------------*
		 * Private functions
		 * -------------------------------------------------------------------------------------------------- */

		/**
		 * Returns the time at which the feed should be updated
		 * 
		 * @return string Containing the time in Y-m-d H:i:s format
		 */
		private function new_activation_time( $last_update, $update_frequency ) {
			$update_split = explode( ':', $update_frequency );

			$days = $update_split[0] <= 1 ? 0 : $update_split[0];
			$hrs = $update_split[1];
			$min = $update_split[2];
			$freq = $update_split[3];
			
			if ( $freq < 2 ) { // update only once a day, every $update_split[0] days
				$update_date = date_add( date_create( $last_update ), date_interval_create_from_date_string( $days . ' days' ) );
				return date_format( $update_date, 'Y-m-d' ) . ' ' . $hrs . ':' . $min . ':00';
			} else { // update more than once a day
				$update_hrs = $this->get_update_hours( $freq );
				$update_date = date_add( date_create( $last_update ), date_interval_create_from_date_string( $update_hrs . ' hours' ) );
				return date_format( $update_date, 'Y-m-d H:i' ) . ':00';
			}
		}
		
		/**
		 * Performs the auto feed update
		 */
		private function auto_update_feed( $feed_data, $silent ) {
			$feed_master_class = new WPPFM_Feed_Master_Class( $feed_data );
			$feed_master_class->update_feed_file( $feed_data, $silent );
		}
		
		/**
		 * Returns the daily update options
		 * 
		 * @return int Hours difference between updates
		 */
		private function get_update_hours( $selection ) {
			switch( $selection ) {
				case '2':
					return 12;
				case '4':
					return 6;
				case '6':
					return 4;
				case '8':
					return 3;
				case '12':
					return 2;
				case '24':
					return 1;
				default:
					return 24;
			}
		}

	}

     // end of WPPFM_Schedules class

endif;