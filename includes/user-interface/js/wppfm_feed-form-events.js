/*!
 * feed-form-events.js v1.2
 * Part of the WP Product Feed Manager
 * Copyright 2017, Michel Jongbloed
 *
 */

"use strict";

var $jq = jQuery.noConflict();

function wppfm_listen() {

    // monitor the four main feed settings and react when they change
    $jq( '#file-name' ).focusout( function () {

        if ( $jq( '#file-name' ).val() !== '' ) {
            $jq( '#countries' ).prop( 'disabled', false );
            $jq( '#lvl_0' ).prop( 'disabled', false );
			wppfm_file_name_changed();
        } else {
            $jq( '#countries' ).prop( 'disabled', true );
            $jq( '#lvl_0' ).prop( 'disabled', true );
        }
    } );

    $jq( '#file-name' ).keyup( function () {

        if ( $jq( '#file-name' ).val() !== '' ) {
            $jq( '#countries' ).prop( 'disabled', false );
            $jq( '#lvl_0' ).prop( 'disabled', false );
        } else {
            $jq( '#countries' ).prop( 'disabled', true );
            $jq( '#lvl_0' ).prop( 'disabled', true );
        }
    } );

    $jq( '#countries' ).change( function () {
        if ( $jq( '#countries' ).val() !== '0' ) { $jq( '#lvl_0' ).prop( 'disabled', false ); }

        wppfm_mainInputChanged( false );
    } );
	
	$jq( '#google-feed-title-selector' ).change( function() { wppfm_google_feed_title_changed(); } );
	
	$jq( '#google-feed-description-selector' ).change( function() { wppfm_google_feed_description_changed(); } );
    
    $jq( '#merchants' ).change( function () {

        if ( $jq( '#merchants' ).val() !== '0' ) {
            wppfm_showChannelInputs( $jq( '#merchants' ).val(), true );
            wppfm_mainInputChanged( false );
        } else {
            wppfm_hideFeedFormMainInputs();
        }
    } );
	
	$jq( '#variations' ).change( function () { wppfm_variation_selection_changed(); } );
    
    $jq( '#aggregator' ).change( function() {
		wppfm_aggregatorChanged();
		wppfm_makeFieldsTable(); // reset the attribute mapping
    } );

    $jq( '#lvl_0' ).change( function () { wppfm_mainInputChanged( true ); } );

    $jq( '.cat_select' ).change( function () { wppfm_nextCategory( this.id ); } );
    
    $jq( '#wppfm-generate-feed-button-top' ).click( function () { wppfm_generateFeed(); } );

    $jq( '#wppfm-generate-feed-button-bottom' ).click( function () { wppfm_generateFeed(); } );
	
	$jq( '#wppfm-save-feed-button-top' ).click( function() { wppfm_saveFeedData(); } );

	$jq( '#wppfm-save-feed-button-bottom' ).click( function() { wppfm_saveFeedData(); } );

    $jq( '#days-interval' ).change( function () { wppfm_saveUpdateSchedule(); } );

    $jq( '#update-schedule-hours' ).change( function () { wppfm_saveUpdateSchedule(); } );

    $jq( '#update-schedule-minutes' ).change( function () { wppfm_saveUpdateSchedule(); } );
    
	$jq( '#update-schedule-frequency' ).change( function () { wppfm_saveUpdateSchedule(); } );
	
	$jq( '#wppfm_ftp_passive_mode' ).change( function () { wppfm_ftp_mode_changed(); } );
	
	$jq( '#wppfm_auto_feed_fix_mode' ).change( function () { wppfm_auto_feed_fix_changed(); } );
	
	$jq( '#wppfm_third_party_attr_keys' ).focusout( function() { wppfm_third_party_attributes_changed(); } );
    
    $jq( '.category-mapping-selector' ).change( function() {
        if ( $jq(this).is(":checked") ) { wppfm_activateFeedCategory( $jq(this).val() ); } 
		else { wppfm_deactivateFeedCategory( $jq(this).val() ); }
    } );
	
	$jq( '#categories-select-all' ).change( function() {
        if ( $jq(this).is(":checked") ) { wppfm_activateFeedCategory( 'wppfm_all_categories_selected' ); } 
		else { wppfm_deactivateFeedCategory( 'wppfm_all_categories_selected' ); }
	} );
    
    $jq( 'input#accept_eula' ).change( function() {
        if ( $jq(this).is(":checked") ) { $jq( 'input#wppfm_license_activate' ).prop( 'disabled', false ); } 
		else { $jq( 'input#wppfm_license_activate' ).prop( 'disabled', true ); }
    } );

    //$jq( '.edit-output' ).click( function () { wppfm_editOutput( this.id ); } ); TODO: Hier nog verder naar zoeken. De this.id zou de id van de link op moeten pakken.
	
	$jq( '#wppfm_prepare_backup' ).click( function() { 
		$jq( '#wppfm_backup-file-name' ).val( '' );
		$jq( '#wppfm_backup-wrapper' ).show();
	} );
	
	$jq( '#wppfm_make_backup' ).click( function() { wppfm_backup(); } );
	
	$jq( '#wppfm_cancel_backup' ).click( function() { $jq( '#wppfm_backup-wrapper' ).hide(); } );
	
	$jq( '#wppfm_backup-file-name' ).keyup( function() {
		if ( $jq( '#wppfm_backup-file-name' ).val !== '' ) { $jq( '#wppfm_make_backup' ).attr( 'disabled', false ); }
	} );
	
    $jq( '.notice-dismiss' ).click( function () { console.log( "Disposed-Clicked" ); } );
}