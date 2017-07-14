/*!
 * wppfm_support.js v3.2
 * Part of the WP Product Feed Manager
 * Copyright 2017, Michel Jongbloed
 *
 */

"use strict";

var $jq = jQuery.noConflict();

function wppfm_activateFeedCategory( id ) {
	if( id !== 'wppfm_all_categories_selected' ) {
		wppfm_activateFeedCategorySelector( id );

		var children = $jq( '#feed-selector-' + id ).attr( "data-children" ) ? JSON.parse( $jq( '#feed-selector-' + id ).attr( "data-children" ) ) : [ ];

		for ( var i = 0; i < children.length; i++ ) { wppfm_activateFeedCategorySelector( children[i] ); }
	} else {
		
		var allIds = $jq('tbody#wppfm-category-mapping-body').children('tr');
		
		for ( var i = 0; i < allIds.length; i++ ) { wppfm_activateFeedCategorySelector( $jq( allIds[i] ).children('th').children('input').val() ); }
	}
}

function wppfm_activateFeedCategorySelector( id ) {

	// some channels use your own shop's categories
	var usesOwnCategories = wppfm_channelUsesOwnCategories( _feedHolder['channel'] );
	var feedCategoryText = usesOwnCategories ? 'shopCategory' : 'default';
	// activate the category in the feedHolder
	_feedHolder.activateCategory( id, usesOwnCategories );

	// get the children of this selector if any
	var children = $jq( '#feed-selector-' + id ).attr( "data-children" ) ? JSON.parse( $jq( '#feed-selector-' + id ).attr( "data-children" ) ) : [ ];

	if ( $jq( '#feed-category-' + id ).html() === '' ) {
		$jq( '#feed-category-' + id ).html( wppfm_mapToDefaultCategoryElement( id, feedCategoryText ) );
	}

	$jq( '#feed-selector-' + id ).prop( 'checked', true );

	for ( var i = 0; i < children.length; i++ ) { wppfm_activateFeedCategorySelector( children[i] ); }
}

function wppfm_deactivateFeedCategory( id ) {

	if( id !== 'wppfm_all_categories_selected' ) {
		wppfm_deactivateFeedCategorySelector( id, true );

		var children = $jq( '#feed-selector-' + id ).attr( "data-children" ) ? JSON.parse( $jq( '#feed-selector-' + id ).attr( "data-children" ) ) : [ ];

		for ( var i = 0; i < children.length; i++ ) { wppfm_deactivateFeedCategorySelector( children[i], false ); }
	} else {
		
		var allIds = $jq('tbody#wppfm-category-mapping-body').children('tr');
		
		for ( var i = 0; i < allIds.length; i++ ) { wppfm_deactivateFeedCategorySelector( $jq( allIds[i] ).children('th').children('input').val() ); }
	}
}

function wppfm_deactivateFeedCategorySelector( id, parent ) {

	_feedHolder.deactivateCategory( id );

	$jq( '#feed-category-' + id ).html( '' );
	$jq( '#category-selector-catmap-' + id ).hide();

	$jq( '#feed-selector-' + id ).prop( 'checked', false );

	if ( !parent ) {
		var children = $jq( '#feed-selector-' + id ).attr( "data-children" ) ? JSON.parse( $jq( '#feed-selector-' + id ).attr( "data-children" ) ) : [ ];
		for ( var i = 0; i < children.length; i++ ) { wppfm_deactivateFeedCategorySelector( children[i], false ); }
	}
}

/**
 * Shows and hides the category sub level selectors depending on the selected level
 * 
 * @param {int} currentLevelId
 * @returns {nothing}
 */
function wppfm_hideSubs( currentLevelId ) {

	// identify the level from the level id
	var level = currentLevelId.match( /(\d+)$/ )[0];
	var idString = currentLevelId.substring( 0, currentLevelId.length - level.length );

	// only show sub fields that are at or before the selected level. Hide the rest
	for ( var i = 7; i > level; i-- ) {
		$jq( '#' + idString + i ).css( 'display', 'none' );
		$jq( '#' + idString + i ).empty();
	}
}

function wppfm_show_feed_spinner() {
	$jq( '#feed-spinner' ).show();
	$jq( 'body' ).css( 'cursor', 'wait' );
	$jq( '#wppfm-generate-feed-button-top' ).attr( 'disabled', true );
	$jq( '#wppfm-generate-feed-button-bottom' ).attr( 'disabled', true );
	$jq( '#wppfm-save-feed-button-top' ).attr( 'disabled', true );
	$jq( '#wppfm-save-feed-button-bottom' ).attr( 'disabled', true );
}

function wppfm_hide_feed_spinner() {
	$jq( '#feed-spinner' ).hide();
	$jq( 'body' ).css( 'cursor', 'default' );
	$jq( '#wppfm-generate-feed-button-top' ).attr( 'disabled', false );
	$jq( '#wppfm-generate-feed-button-bottom' ).attr( 'disabled', false );
	$jq( '#wppfm-save-feed-button-top' ).attr( 'disabled', false );
	$jq( '#wppfm-save-feed-button-bottom' ).attr( 'disabled', false );
}

function wppfm_enableFeedActionButtons() {
	// enable the Generate and Save button
	$jq( '#wppfm-generate-feed-button-top' ).prop( 'disabled', false );
	$jq( '#wppfm-generate-feed-button-bottom' ).prop( 'disabled', false );
	$jq( '#wppfm-save-feed-button-top' ).prop( 'disabled', false );
	$jq( '#wppfm-save-feed-button-bottom' ).prop( 'disabled', false );
}

function disableFeedActionButtons() {
	// keep the Generate and Save buttons disabled
	$jq( '#wppfm-generate-feed-button-top' ).prop( 'disabled', true );
	$jq( '#wppfm-generate-feed-button-bottom' ).prop( 'disabled', true );
	$jq( '#wppfm-save-feed-button-top' ).prop( 'disabled', true );
	$jq( '#wppfm-save-feed-button-bottom' ).prop( 'disabled', true );
}

function wppfm_show_error_message( message ) {
	$jq( '#error-message' ).empty();
	$jq( '#error-message' ).append( '<p>' + message + '</p>' );
	$jq( '#error-message' ).show();
}

function wppfm_show_success_message( message ) {
	$jq( '#success-message' ).empty();
	$jq( '#success-message' ).append( '<p>' + message + '</p>' );
	$jq( '#success-message' ).show();
}
