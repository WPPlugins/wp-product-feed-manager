/*!
 * wppfm_settings-form.js v3.1
 * Part of the WP Product Feed Manager
 * Copyright 2017, Michel Jongbloed
 *
 */

"use strict";

var $jq = jQuery.noConflict();

function wppfm_ftp_mode_changed() {
	wppfm_update_ftp_passive_mode( $jq( '#wppfm_ftp_passive_mode' ).is( ':checked' ), function( response ) { console.log( response ); } );
}

function wppfm_auto_feed_fix_changed() {
	wppfm_auto_feed_fix_mode( $jq( '#wppfm_auto_feed_fix_mode' ).is( ':checked' ), function( response ) { console.log( response ); } );	
}

function wppfm_third_party_attributes_changed() {
	wppfm_change_third_party_attribute_keywords( $jq( '#wppfm_third_party_attr_keys' ).val(), function( response ) { console.log( response ); } );
}

function wppfm_backup() {
	
	if ( $jq( '#wppfm_backup-file-name' ).val() !== '' ) {
		
		$jq( '#wppfm_backup-wrapper' ).hide();

		wppfm_initiateBackup( $jq( '#wppfm_backup-file-name' ).val(), function( response ) { 
			wppfm_resetBackupsList();
			
			if ( response !== '1' ) {

				wppfm_show_error_message( response );
			}
		} );
	} else {
		
		alert( "First enter a file name for the backup file." );
	}
}

function wppfm_deleteBackupFile( fileName ) {

	var userInput = confirm( "Please confirm you want to delete backup " + fileName + "." );

	if ( userInput === true ) {
		wppfm_deleteBackup( fileName, function( response ) {
	
			wppfm_show_success_message( fileName + " removed." );

			wppfm_resetBackupsList();
			console.log( response ); 
		} );
	}
}

function wppfm_restoreBackupFile( fileName ) {

	var userInput = confirm( "Are you sure you want to restore backup " + fileName + "? This will overwrite the current Feed Manager data in the database!" );

	if ( userInput === true ) {

		wppfm_restoreBackup( fileName, function( response ) { 

			if ( response === '1' ) {
				wppfm_show_success_message( fileName + ' restored' );
				wppfm_resetOptionSettings();
			} else {
				wppfm_show_error_message( response );
			}
		} );
	}
}

function wppfm_duplicateBackupFile( fileName ) {
	
	wppfm_duplicateBackup( fileName, function( response ) { 
		
		if ( response === '1' ) { 
			wppfm_show_success_message( fileName + ' duplicated' ); 
		} else {
			wppfm_show_error_message( response );
		}
		wppfm_resetBackupsList();
	} );
}

/**
 * hook the document actions
 */
$jq( document ).ready( function () {

	// set up the event listeners
	wppfm_listen();
} );