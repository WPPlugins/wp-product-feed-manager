<?php

/* * ******************************************************************
 * Version 1.0
 * Modified: 29-08-2015
 * Copyright 2015 Accentio. All rights reserved.
 * License: None
 * By: Michel Jongbloed
 * ****************************************************************** */

// Prevent direct access
if ( !defined( 'ABSPATH' ) ) {
	echo 'Hi!  I\'m just a plugin, there\'s not much I can do when called directly.';
	exit;
}

if ( !class_exists( 'WPPFM_Prepare_Taxonomy_Class' ) ) :

	/**
	 * The WPPFM_Prepare_Taxonomy_Class contains functions that can convert taxonomy files provided by channel providers
	 * to the format that WP Product Feed Manager requires as taxonomy files.
	 * 
	 * @class WPPFM_Prepare_Taxonomy_Class
	 * @version dev
	 */
	class WPPFM_Prepare_Taxonomy_Class {

		public function remove_merchant_rates_from_pricegrabber_category_file() {

			$path	 = WPPFM_CHANNEL_DATA_DIR . "/pricegrabber/taxonomy.en-US.txt";
			$rpath	 = WPPFM_CHANNEL_DATA_DIR . "/pricegrabber/taxonomy_new.en-US.txt";

			$fhr = fopen( $path, 'r' );
			$fhw = fopen( $rpath, 'w' );

			while ( !feof( $fhr ) ) {

				$line = fgets( $fhr );

				if ( strpos( $line, ',$' ) !== false ) {
					$tline	 = $line ? substr( $line, 0, strpos( $line, ',$' ) ) : '';
					$newline = $tline . "\r\n";
				} else {
					$newline = $line;
				}

				fputs( $fhw, $newline );
			}
		}

		public function prepare_amazone_category_file() {

			$path	 = WPPFM_CHANNEL_DATA_DIR . "/amazon/taxonomy.en-US.txt";
			$rpath	 = WPPFM_CHANNEL_DATA_DIR . "/amazon/taxonomy_new.en-US.txt";

			$fhr = fopen( $path, 'r' );
			$fhw = fopen( $rpath, 'w' );

			while ( !feof( $fhr ) ) {

				$line = fgets( $fhr );

				if ( strpos( $line, '/' ) !== false ) {
					$newline = str_replace( '/', ' > ', $line );
				} else {
					$newline = $line;
				}

				fputs( $fhw, $newline );
			}
		}

		public function prepare_vergelijk_category_file() {

			$path	 = WPPFM_CHANNEL_DATA_DIR . "/vergelijk/taxonomy.nl-NL.txt";
			$rpath	 = WPPFM_CHANNEL_DATA_DIR . "/vergelijk/taxonomy_new.nl-NL.txt";

			$fhr = fopen( $path, 'r' );
			$fhw = fopen( $rpath, 'w' );

			while ( !feof( $fhr ) ) {

				$line = fgets( $fhr );

				$removed_tabs = str_replace( "\t", '|', $line );

				$explode = explode( '|', $removed_tabs );

				$category = $explode[ 1 ];

				str_replace( '"', "", $category );

				$newline = $category . ' > ' . $explode[ 3 ] . ' > ' . $explode[ 5 ];

				fputs( $fhw, $newline );
			}

			// now remove the doubles
			$l		 = file( $rpath );
			$lines	 = array_unique( $l );
			file_put_contents( $rpath, $lines );
		}

		public function convert_kieskeurig_category_file() {

			$path	 = WPPFM_CHANNEL_DATA_DIR . "/kieskeurig/taxonomy.nl-NL.txt";
			$rpath	 = WPPFM_CHANNEL_DATA_DIR . "/kieskeurig/taxonomy_new.nl-NL.txt";

			$fhr = fopen( $path, 'r' );
			$fhw = fopen( $rpath, 'w' );

			$r_1 = '';
			$r_2 = '';

			$cat_1	 = '';
			$cat_2	 = '';
			$cat_3	 = '';

			$c = 0;

			while ( !feof( $fhr ) ) {

				$line = fgets( $fhr );

				if ( $c < 2 ) { // remove the first two lines
					$newline = '';
					$c++;
				} else {

					$line_cats = explode( "\t", $line );

					if ( $line_cats[ 0 ] !== '' ) {

						$r_1 = $line_cats[ 0 ];

						$newline = '';
					} elseif ( $line_cats[ 2 ] !== '' ) {

						$r_2 = $line_cats[ 2 ];

						$newline = '';
					} elseif ( $line_cats[ 5 ] !== '' ) {

						$cat_1	 = $line_cats[ 0 ] === '' ? $r_1 : $line_cats[ 0 ];
						$cat_2	 = $line_cats[ 2 ] === '' ? $r_2 : $line_cats[ 2 ];
						$cat_3	 = $line_cats[ 5 ];

						$newline = $cat_1 . ' > ' . $cat_2 . ' > ' . $cat_3 . "\r\n";
					}
				}

				fputs( $fhw, $newline );
			}
		}

		public function prepare_beslis_category_file() {

			$path	 = WPPFM_CHANNEL_DATA_DIR . "/beslis/category_overview.xml";
			$rpath	 = WPPFM_CHANNEL_DATA_DIR . "/beslis/taxonomy-new.nl-NL.txt";

			$fhw = fopen( $rpath, 'w' );

			$xml = simplexml_load_file( $path );

			foreach ( $xml->categories->maincat as $main_cat ) {

				$newline = (string) $main_cat[ 'name' ][ 0 ];

				fputs( $fhw, $newline . "\r\n" );

				foreach ( $main_cat as $level_1 ) {

					$level_1_line = $newline . ' > ' . (string) $level_1[ 'name' ][ 0 ];

					fputs( $fhw, $level_1_line . "\r\n" );

					foreach ( $level_1 as $level_2 ) {

						$level_2_line = $level_1_line . ' > ' . (string) $level_2[ 'name' ][ 0 ];

						fputs( $fhw, $level_2_line . "\r\n" );
					}
				}
			}
		}

		public function prepare_nextag_category_file() {

			$path	 = WPPFM_CHANNEL_DATA_DIR . "/nextag/taxonomy.en-US.txt";
			$rpath	 = WPPFM_CHANNEL_DATA_DIR . "/nextag/taxonomy_new.en-US.txt";

			$fhr = fopen( $path, 'r' );
			$fhw = fopen( $rpath, 'w' );

			while ( !feof( $fhr ) ) {

				$line = fgets( $fhr );

				if ( strpos( $line, '/' ) !== false ) {

					$newline = str_replace( '/', '>', $line );
				} else {

					$newline = $line;
				}

				fputs( $fhw, trim( $newline ) . "\r\n" );
			}
		}

		public function prepare_koopjespakker_category_file() {

			$path	 = WPPFM_CHANNEL_DATA_DIR . "/koopjespakker/taxonomy.nl-NL.txt";
			$rpath	 = WPPFM_CHANNEL_DATA_DIR . "/koopjespakker/taxonomy_new.nl-NL.txt";

			$fhr = fopen( $path, 'r' );
			$fhw = fopen( $rpath, 'w' );

			while ( !feof( $fhr ) ) {

				$line = fgets( $fhr );

				if ( strpos( $line, 'concat' ) === false ) {
					
				}

				$newline_1	 = str_replace( '/', '>', $line );
				$newline_2	 = str_replace( '|', '>', $newline_1 );

				$newline = trim( $newline_2 );

				if ( $newline !== '' && strpos( $line, 'concat' ) === false && strpos( $line, 'Options' ) === false ) {
					fputs( $fhw, $newline . "\r\n" );
				}
			}
		}

	}

	

     // end of WPPFM_Prepare_Taxonomy_Class

endif;
