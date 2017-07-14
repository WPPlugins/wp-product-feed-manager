/*!
 * ajaxdatahandling.js v2.1
 * Part of the WP Product Feed Manager
 * Copyright 2017, Michel Jongbloed
 *
 */

"use strict";

var MyAjax;

function wppfm_getFeedList( callback ) {

	jQuery.post(
		MyAjax.ajaxurl,
		{
			action: 'myajax-get-list-of-feeds',
			postFeedsListNonce: MyAjax.postFeedsListNonce

		}, function ( response ) {

		callback( wppfm_validateResponse( response ) );
	} );
}

function wppfm_getBackupsList( callback ) {

	jQuery.post(
		MyAjax.ajaxurl,
		{
			action: 'myajax-get-list-of-backups',
			postBackupListNonce: MyAjax.postBackupListNonce

		}, function ( response ) {

		callback( wppfm_validateResponse( response ) );
	} );
}

function wppfm_getSettingsOptions( callback ) {

	jQuery.post(
		MyAjax.ajaxurl,
		{
			action: 'myajax-get-settings-options',
			postSetupOptionsNonce: MyAjax.postSetupOptionsNonce

		}, function ( response ) {

		callback( wppfm_validateResponse( response ) );
	} );
}

/**
 * Reads and returns all possible output fields from the selected merchant
 * 
 * @param {int} feedId
 * @param {int} channelId
 * @param callback
 * @returns list with output fields
 */
function wppfm_getOutputFields( feedId, channelId, callback ) {

	jQuery.post(
		MyAjax.ajaxurl,
		{
			action: 'myajax-get-output-fields',
			feedId: feedId,
			channelId: channelId,
			outputFieldsNonce: MyAjax.outputFieldsNonce

		}, function ( response ) {

		callback( wppfm_validateResponse( response ) );
	} );
}

/**
 * Reads and returns all possible source fields from the selected source
 * 
 * @param {int} sourceId
 * @param callback
 * @returns list with input fields
 */
function wppfm_getSourceFields( sourceId, callback ) {

	jQuery.post(
		MyAjax.ajaxurl,
		{
			action: 'myajax-get-input-fields',
			sourceId: sourceId,
			inputFieldsNonce: MyAjax.inputFieldsNonce

		}, function ( response ) {

		callback( wppfm_validateResponse( response ) );
	} );
}

function wppfm_getMainFeedFilters( feedId, callback ) {

	jQuery.post(
		MyAjax.ajaxurl,
		{
			action: 'myajax-get-main-feed-filters',
			feedId: feedId,
			inputFeedFiltersNonce: MyAjax.inputFeedFiltersNonce

		}, function ( response ) {

		callback( wppfm_validateResponse( response ) );
	} );
}

function wppfm_getNextCategories( channelId, requestedLevel, parentCategory, language, callback ) {

	jQuery.post(
		MyAjax.ajaxurl,
		{
			action: 'myajax-get-next-categories',
			channelId: channelId,
			requestedLevel: requestedLevel,
			parentCategory: parentCategory,
			fileLanguage: language,
			nextCategoryNonce: MyAjax.nextCategoryNonce

		}, function ( response ) {
			
		response = response.trim();

		if ( response.substr( response.length - 1 ) === '0' ) {
			response = response.substring( 0, response.length - 1 );
		}

		callback( wppfm_validateResponse( response ) );
	} );
}

function wppfm_getCategoryListsFromString( channelId, mainCategoriesString, language, callback ) {

	jQuery.post(
		MyAjax.ajaxurl,
		{
			action: 'myajax-get-category-lists',
			channelId: channelId,
			mainCategories: mainCategoriesString,
			fileLanguage: language,
			categoryListsNonce: MyAjax.categoryListsNonce

		}, function ( response ) {

		callback( wppfm_validateResponse( response ) );
	} );
}

function wppfm_updateFeedToDb( feed, metaData, callback ) {

	jQuery.post(
		MyAjax.ajaxurl,
		{
			action: 'myajax-update-feed-data',
			feedId: feed['feedId'],
			channelId: feed['channel'],
			includeVariations: feed['includeVariations'],
			isAggregator: feed['isAggregator'],
			countryId: feed['country'],
			sourceId: feed['dataSource'],
			title: feed['title'],
			feedTitle: feed['feedTitle'],
			feedDescription: feed['feedDescription'],
			defaultCategory: feed['mainCategory'],
			url: feed['url'],
			status: feed['status'],
			schedule: feed['updateSchedule'],
			feedFilter: feed['feedFilter'] ? feed['feedFilter'][0]['meta_value'] : '',
			metaData: JSON.stringify( metaData ),
			updateFeedDataNonce: MyAjax.updateFeedDataNonce
		}, function ( response ) {

		callback( wppfm_validateResponse( response ) );
	} );
}

function wppfm_updateFeedFile( feed, callback ) {

	var strt = new Date();
	console.log( "Feed update started at " + strt.getHours() + ":" + strt.getMinutes() + ":" + strt.getSeconds() );

	jQuery.post(
		MyAjax.ajaxurl,
		{
			action: 'myajax-update-feed-file',
			dataType: 'text',
			feedData: JSON.stringify( feed ),
			updateFeedFileNonce: MyAjax.updateFeedFileNonce

		}, function ( response ) {

		var ended = new Date();
		var runtime = ( ended - strt ) / 1000;
		console.log( "Feed update ended at " + ended.getHours() + ":" + ended.getMinutes() + ":" + ended.getSeconds() + " (runtime " + runtime + " seconds)." );

		callback( wppfm_validateResponse( response ) );
	} );
}

function wppfm_getFeedData( feedId, callback ) {

	jQuery.post(
		MyAjax.ajaxurl,
		{
			action: 'myajax-get-feed-data',
			sourceId: feedId,
			feedDataNonce: MyAjax.feedDataNonce

		}, function ( response ) {

		callback( wppfm_validateResponse( response ) );
	} );
}

function wppfm_switchFeedStatus( feedId, callback ) {

	jQuery.post(
		MyAjax.ajaxurl,
		{
			action: 'myajax-switch-feed-status',
			feedId: feedId,
			switchFeedStatusNonce: MyAjax.switchFeedStatusNonce

		}, function ( response ) {

		callback( wppfm_validateResponse( response ) );
	} );
}

function wppfm_duplicateExistingFeed( feedId, callback ) {

	jQuery.post(
		MyAjax.ajaxurl,
		{
			action: 'myajax-duplicate-existing-feed',
			feedId: feedId,
			duplicateFeedNonce: MyAjax.duplicateFeedNonce

		}, function ( response ) {

		if ( response.trim() ) { wppfm_resetFeedList(); }
		
		callback( wppfm_validateResponse( response ) );
	} );
}

function wppfm_logMessageOnServer( message, fileName, callback ) {

	jQuery.post(
		MyAjax.ajaxurl,
		{
			action: 'myajax-log-message',
			messageList: message,
			fileName: fileName,
			logMessageNonce: MyAjax.logMessageNonce

		}, function ( result ) {

		callback( result.trim() );
	} );
}

function wppfm_update_ftp_passive_mode( selection, callback ) {

	jQuery.post(
		MyAjax.ajaxurl,
		{
			action: 'myajax-update-ftp-mode-selection',
			ftp_selection: selection,
			updateFeedDataNonce: MyAjax.setFTPModeNonce
			
		}, function ( response ) {

		callback( response.trim() );
	} );
}

function wppfm_auto_feed_fix_mode( selection, callback ) {

	jQuery.post(
		MyAjax.ajaxurl,
		{
			action: 'myajax-auto-feed-fix-mode-selection',
			fix_selection: selection,
			updateAutoFeedFixNonce: MyAjax.setAutoFeedFixNonce
			
		}, function ( response ) {

		callback( response.trim() );
	} );
}

function wppfm_change_third_party_attribute_keywords( keywords, callback ) {
	
	jQuery.post(
		MyAjax.ajaxurl,
	{
		action: 'myajax-third-party-attribute-keywords',
		keywords: keywords,
		thirdPartyKeywordsNonce: MyAjax.setThirdPartyKeywordsNonce
		
	}, function( response ) {
	
		callback( response.trim() );
	} );
}

/**
 * Takes the response of an ajax call and checks if it's ok. When not, it will display the error and return
 * an empty list.
 * 
 * @param {type} response
 * @returns {String}
 */
function wppfm_validateResponse( response ) {
	
	response = response.trim(); // remove php ajax response white spaces

	// when the response contains no error message
	if ( response.indexOf( "<div id='error'>" ) < 0 && response.indexOf( "<b>Fatal error</b>" ) < 0
		&& response.indexOf( "<b>Notice</b>" ) < 0 && response.indexOf( "<b>Warning</b>" ) < 0
		&& response.indexOf( "<b>Catchable fatal error</b>" ) < 0 && response.indexOf( '<div id="error">' ) < 0 ) {

		if ( response.indexOf( "[]" ) < 0 ) {

			if ( response !== '' ) {

				return( response );
			} else {

				return ( '1' );
			}
		} else { // if it has an error message

			// return an empty list
			return( '0' );
		}
	} else {

		wppfm_show_error_message( response.replace( '[]', '' ) );
		wppfm_hide_feed_spinner();
		
		wppfm_logMessageOnServer( response, 'error', function ( result ) {

			// return an empty list
			return( '0' );
		} );
	}
}

/**
 * Deletes a specific feed file
 * 
 * This function first removes the file from the server and than from the feed database.
 * After that it will refresh the Feed List.
 * 
 * @param {int} id
 * @param {string} feedTitle
 * @returns nothing
 */
function wppfm_deleteFeed( id, feedTitle ) {

	// clear old messages
	jQuery( '#feed-list-message' ).empty();

	// remove the file
	wppfm_removeFeedFile( function () {

		jQuery( '#feed-spinner' ).show();

		// delete the file entry in the database
		wppfm_deleteFeedFromDb( id, function ( response ) {

			jQuery( '#feed-spinner' ).show();
			
			response = response.trim();

			if ( response === '1' ) {

				// reset the feed list
				wppfm_resetFeedList();
				
				wppfm_show_success_message( 'Removed file "' + feedTitle + '" from the server.' );

				jQuery( '#feed-spinner' ).hide();
			} else {

				// report the result to the user
				jQuery( '#feed-list-message' ).append( response );
				jQuery( '#feed-spinner' ).hide();
			}
		}, id );

	}, feedTitle );
}

function wppfm_removeFeedFile( callback, feedTitle ) {

	jQuery.post(
		MyAjax.ajaxurl,
		{
			action: 'myajax-delete-feed-file',
			fileTitle: feedTitle,
			deleteFeedNonce: MyAjax.deleteFeedNonce

		}, function ( response ) {

		callback( wppfm_validateResponse( response ) );
	} );
}

function wppfm_deleteFeedFromDb( feedId, callback ) {

	jQuery.post(
		MyAjax.ajaxurl,
		{
			action: 'myajax-delete-feed',
			feedId: feedId,
			deleteFeedNonce: MyAjax.deleteFeedNonce

		}, function ( response ) {

		callback( wppfm_validateResponse( response ) );
	} );
}

function wppfm_initiateBackup( fileName, callback ) {

	jQuery.post(
		MyAjax.ajaxurl,
		{
			action: 'myajax-backup-current-data',
			fileName: fileName,
			backupNonce: MyAjax.backupNonce
			
		}, function ( response ) {

		callback( wppfm_validateResponse( response ) );
	} );
}

function wppfm_deleteBackup( fileName, callback ) {

	jQuery.post(
		MyAjax.ajaxurl,
		{
			action: 'myajax-delete-backup-file',
			fileName: fileName,
			deleteBackupNonce: MyAjax.deleteBackupNonce
			
		}, function ( response ) {

		callback( wppfm_validateResponse( response ) );
	} );
}

function wppfm_restoreBackup( fileName, callback ) {

	jQuery.post(
		MyAjax.ajaxurl,
		{
			action: 'myajax-restore-backup-file',
			fileName: fileName,
			restoreBackupNonce: MyAjax.restoreBackupNonce
			
		}, function ( response ) {

		callback( wppfm_validateResponse( response ) );
	} );
}

function wppfm_duplicateBackup( fileName, callback ) {

	jQuery.post(
		MyAjax.ajaxurl,
		{
			action: 'myajax-duplicate-backup-file',
			fileName: fileName,
			duplicateBackupNonce: MyAjax.duplicateBackupNonce
			
		}, function ( response ) {

		callback( wppfm_validateResponse( response ) );
	} );
}