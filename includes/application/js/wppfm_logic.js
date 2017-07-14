/*!
 * logic.js v1.2
 * Part of the WP Product Feed Manager
 * Copyright 2016, Michel Jongbloed
 *
 */

"use strict";

var $jq = jQuery.noConflict();
var _feedHolder;

function wppfm_editCategories() {

		if ( !wppfm_isCustomChannel( _feedHolder['channel'] ) ) {

		var currentCategories = _feedHolder['mainCategory'].split( ' > ' );

		$jq( '#selected-categories' ).hide();
		$jq( '#lvl_0' ).prop( 'disabled', false );

		for ( var i = 0; i < currentCategories.length; i++ ) {

			$jq( '#lvl_' + i ).show();
		}
	} else {

		// as the user selected a free format, just show a text input control
		$jq( '#category-selector-lvl' ).html( wppfm_freeCategoryInputCntrl( 'default', _feedHolder['feedId'], _feedHolder['mainCategory'] ) );
		$jq( '#category-selector-lvl' ).prop( 'disabled', false );
	}
}

function wppfm_generateFeed() {

	if ( $jq( '#file-name' ).val() !== '' ) {

		if ( _feedHolder['categoryMapping'] && _feedHolder['categoryMapping'].length > 0 ) {

			wppfm_generateAndSaveFeed();
		} else {

			var userInput = confirm( 'You\'ve not selected a Shop Category in the Category Mapping Table. With no Shop Category selected, your feed will be empty.\n\n Are you sure you still want to save this feed?' );

			if ( userInput === true ) {

				wppfm_generateAndSaveFeed();
			}
		}
	} else {
		jQuery( '#alert-message' ).html( '<p>A file name is required!</p>' );
		jQuery( '#success-message' ).show();
	}
}

function wppfm_saveFeedData() {

	if ( $jq( '#file-name' ).val() !== '' ) {
		
		wppfm_saveFeed();

	} else {
		jQuery( '#alert-message' ).html( '<p>A file name is required!</p>' );
		jQuery( '#success-message' ).show();
	}
}

function getCombinedValue( rowId, sourceLevel ) {

	var c = 1;
	var combinedValue = '';
	var oldValue = _feedHolder.getCombinedOutputValue( rowId, sourceLevel );
	
	while ( $jq( '#combined-input-field-cntrl-' + rowId + '-' + sourceLevel + '-' + c ).val() ) {
		
		var idString =  rowId + '-' + sourceLevel + '-' + c;
		
		var selectedValue = $jq( '#combined-input-field-cntrl-' + idString ).val();
		
		combinedValue += c > 1 ? $jq( '#combined-separator-cntrl-' + idString ).val() + '#' : '' ;
		
		if ( selectedValue !== 'static' ) {
			
			combinedValue += selectedValue !== 'select' ? selectedValue + '|' : '';
		} else if ( $jq( '#static-input-field-' + idString ).val() ) {
			
			combinedValue += selectedValue + '#' + $jq( '#static-input-field-' + idString ).val() + '|';
		} else {

			combinedValue = oldValue + '|';
			break; // if one of the static input fields is still empty, return the old value
		}
		
		c++;
	}
	
	combinedValue = combinedValue.substring( 0, combinedValue.length - 1 ); // remove the last |
	
	return c > 1 ? combinedValue : false; // need at least two fields to be valid
}

function wppfm_staticValueChanged( id, level, combinationLevel ) {
	
	if ( combinationLevel > 0 ) { // the static field resides in a combination source
		
		wppfm_changedCombinedOutput( id, level, combinationLevel );
	} else {
		// store the change in the feed
		wppfm_setStaticValue( id, level, combinationLevel );

		// when the identifier_exists static value has changed, the level of a few attributes should be changed
		if ( id === 34 ) {
			wppfm_setIdentifierExistsDependancies();
		}
	}
}

function wppfm_changedOutputSelection( level ) {

	if ( $jq( '#output-field-cntrl-' + level ).val() !== 'no-value' ) {

		wppfm_activateOptionalFieldRow( level, $jq( '#output-field-cntrl-' + level ).val() );
	}
}

function wppfm_hasExtraSourceRow( nrOfSources, value ) {

	if ( value.length > 0 ) {

		return value[nrOfSources - 1].hasOwnProperty( 'c' ) ? true : false;
	} else {

		return false;
	}
}

function wppfm_changedCustomOutputTitle() {

	var title = $jq( '#custom-output-title-input' ).val();

	if ( title ) { wppfm_activateCustomFieldRow( title ); }
}

function wppfm_deleteSpecificFeed( id, title ) {

	var userInput = confirm( "Please confirm you want to delete feed " + title + "." );

	if ( userInput === true ) {
		wppfm_deleteFeed( id, title );
		console.log( "File " + title + " removed from server.");
	}
}

function wppfm_alertRemoveChannel() {

	var userInput = confirm( "Please confirm you want to remove this channel! Removing this channel will also remove all its feed files." );

	if ( userInput !== true ) { return false; }
}

function wppfm_valueOptionChanged( rowId, sourceLevel, valueEditorLevel ) {

	var type = $jq( '#value-options-' + rowId + '-' + sourceLevel + '-' + valueEditorLevel ).val();

	//var selectorCode = wppfm_getCorrectValueSelector( rowId, sourceLevel, valueEditorLevel, type, '', '' );
	var selectorCode = wppfm_getCorrectValueSelector( rowId, sourceLevel, 0, type, '', '' );

	$jq( '#value-editor-input-span-' + rowId + '-' + sourceLevel + '-' + valueEditorLevel ).html( selectorCode );
}

function wppfm_getCorrectValueSelector( rowId, sourceLevel, valueEditorLevel, type, value, endValue ) {
	var selectorCode = "";

	// TODO: the type is now based on the value and on the text. Should be only value as this makes it
	// easier to work with different languages
	switch ( type ) {

		case '0':
		case 'change nothing':
			wppfm_valueInputOptionsChanged( rowId, sourceLevel, valueEditorLevel ); // save the value in meta as there is no input field required
			selectorCode = '';
			break;

		case '1':
		case 'overwrite':
			selectorCode = wppfm_valueOptionsSingleInput( rowId, sourceLevel, valueEditorLevel, value );
			break;

		case '2':
		case 'replace':
			selectorCode = wppfm_valueOptionsReplaceInput( rowId, sourceLevel, valueEditorLevel, value, endValue );
			break;

		case '3':
		case 'remove':
		case '4':
		case 'add prefix':
		case '5':
		case 'add suffix':
			selectorCode = wppfm_valueOptionsSingleInputValue( rowId, sourceLevel, valueEditorLevel, value );
			break;

		case '6':
		case 'recalculate':
			selectorCode = wppfm_valueOptionsRecalculate( rowId, sourceLevel, valueEditorLevel, value, endValue );
			break;

		case '7':
		case 'convert to child-element':
			selectorCode = wppfm_valueOptionsElementInput( rowId, sourceLevel, valueEditorLevel, value );
			break;

		default:
			selectorCode = wppfm_valueOptionsSingleInput( rowId, sourceLevel, valueEditorLevel, value );
			break;
	}

	return selectorCode;
}

function wppfm_deactivateFeed( id ) {

	wppfm_switchFeedStatus( id, function ( result ) {

		if ( result === "1" ) {
			wppfm_updateFeedRowStatus( id, 1 );
		} else {
			wppfm_updateFeedRowStatus( id, 2 );
		}
	} );
}

function wppfm_duplicateFeed( id, feedName ) {
	
	wppfm_duplicateExistingFeed( id, function ( result ) {
		
		if ( result ) { wppfm_show_success_message( 'Added a copy of feed "' + feedName + '" to the list.' ); }
	} );
}

function wppfm_viewFeed( url ) { window.open( url ); }

function wppfm_addRowValueEditor( rowId, sourceLevel, valueEditorLevel, values ) {

	// add the change values controls
	$jq( '#end-row-id-' + rowId ).remove();
	$jq( '#row-' + rowId ).append( wppfm_valueEditor( rowId, sourceLevel, valueEditorLevel, values ) + wppfm_endrow( rowId ) );

	// and remove the edit values control
	$jq( '#value-editor-input-query-add-span-' + rowId + '-' + sourceLevel + '-' + valueEditorLevel ).remove();
}

/**
 * wppfm_getFeedObject gets the data from an existing feed and stores it in a Feed object
 * 
 * @param {type} feedId
 * @param {type} callback
 * @returns {undefined}
 */
function wppfm_getFeedObject( feedId, callback ) {

	wppfm_getFeedData( feedId, function ( feedData ) {

		if ( feedData !== "0" ) {

			var data = JSON.parse( feedData )[0];
			
			var newFeedObject = new Feed( data['product_feed_id'], data['title'], 
				data['include_variations'], data['is_aggregator'], String( data['channel'] ), data['main_category'],
				data['category_mapping'], data['url'], data['source'], data['country'], data['feed_title'], 
				data['feed_description'], data['schedule'], '', data['status_id'] );

			callback( newFeedObject );
		} else {
			callback( 0 );
		}
	} );
}

function wppfm_addValueEditorQuery( rowId, sourceLevel, conditionLevel ) {

	if ( wppfm_change_value_is_filled( rowId, sourceLevel, conditionLevel ) ) {
		
		if ( wppfm_query_is_filled( rowId, ( sourceLevel - 1 ), 1 ) ) {
			
			wppfm_showEditValueQuery( rowId, sourceLevel, conditionLevel, true );
		} else {
			alert( 'Add at least one query in the previous change value row before adding a new row.' );
		}
	} else { alert( 'Please first fill in a change value option before adding a query to it.' ); }
}

function wppfm_queryStringToQueryObject( queryString ) {

	var queryObject = { };

	if ( queryString ) {
		for ( var key in queryString ) { queryObject = wppfm_convertQueryStringToQueryObject( queryString[key] ); }
	}

	return queryObject;
}

function wppfm_valueStringToValueObject( valueString ) {

	var valueObject = { };

	if ( valueString ) {

		for ( var key in valueString ) {

			// do not process the query part of the string
			if ( key !== 'q' ) {

				valueObject = wppfm_convertValueStringToValueObject( valueString[key] );
			}
		}
	}

	return valueObject;
}

function wppfm_convertQueryStringToQueryObject( queryString ) {

	var queryObject = { };

	var stringSplit = queryString.split( '#' );

	if ( stringSplit[0] === '1' || stringSplit[0] === '2' ) {

		queryObject.preCondition = stringSplit[0];
	} else {

		queryObject.preCondition = '0';
	}

	queryObject.source = stringSplit[1];
	queryObject.condition = stringSplit[2];
	queryObject.value = stringSplit[3] ? stringSplit[3] : '';
	queryObject.endValue = stringSplit[5] ? stringSplit[5] : '';

	return queryObject;
}

function wppfm_resortObject( object ) {

	var result = [ ];
	var i = 1;

	// re-sort the conditions
	for ( var element in object ) {

		var o = { };

		for ( var key in object[element] ) {

			if ( key !== 'q' ) { // exclude q as key
				o[i] = object[element][key];

				result.push( o );
			} else {

				result[i - 1].q = object[element][key];
			}
		}

		i++;
	}

	// don't return an empty {} string
	return i > 1 ? result : '';
}

function wppfm_convertValueStringToValueObject( valueString ) {

	var valueObject = { };
	var valueSplit = valueString.split( '#' );

	valueObject.preCondition = valueSplit[0];
	valueObject.condition = valueSplit[1];
	valueObject.value = valueSplit[2];
	valueObject.endValue = valueSplit[3] ? valueSplit[3] : '';

	return valueObject;
}

function wppfm_makeCleanQueryObject() {

	var queryObject = { };

	queryObject.preCondition = 'if';
	queryObject.source = 'select';
	queryObject.condition = '';
	queryObject.value = '';
	queryObject.endValue = '';

	return queryObject;
}

function wppfm_makeCleanValueObject() {

	var valueObject = { };

	valueObject.preCondition = 'change';
	valueObject.condition = 'overwrite';
	valueObject.value = '';
	valueObject.endValue = '';

	return valueObject;
}

function wppfm_addNewItemToCategoryString( level, oldString, newValue, separator ) {

	var categoryLevel = oldString.split( separator ).length;

	if ( level === '0' ) {

		return newValue;
	} else {

		if ( categoryLevel <= level ) {

			return oldString + separator + newValue;
		} else {

			var pos = 0;

			for ( var i = 0; i < level; i++ ) {

				pos = oldString.indexOf( separator, pos + 1 );

				var oldPart = oldString.substring( 0, pos );

				return oldPart + separator + newValue;
			}
		}
	}
}