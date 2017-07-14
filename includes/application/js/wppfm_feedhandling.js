/*!
 * feedhandling.js v1.1
 * Part of the WP Product Feed Manager
 * Copyright 2016, Michel Jongbloed
 *
 */

"use strict";

var _feedHolder;

function wppfm_outputFields( feedId, channel, callback ) {

    wppfm_getOutputFields( feedId, channel, function ( fields ) {

        if ( fields !== "0" ) {

            callback( JSON.parse( fields ) );
        } else {

            callback( [ ] ); // free feed format selected
        }
    } );
}

function wppfm_customSourceFields( source, callback ) {

    wppfm_getSourceFields( source, function ( fields ) {

        callback( fields && fields !== '0' ? JSON.parse( fields ) : '0' );
    } );
}

function wppfm_mainFeedFilters( feedId, callback ) {
	
	wppfm_getMainFeedFilters( feedId, function ( filters ) {
	
		callback( filters !== '0' ? JSON.parse( filters ) : null );
	} );
}

/**
 * Fills the attributes of the current _feed object with data from the outputs var
 * 
 * @param {array containg output strings} outputs
 * @param {int} channel id
 * @param {int} source id
 * @returns {nothing}
 */
function wppfm_addFeedAttributes( outputs, channel, source ) {

    var inputs = wppfm_getAdvisedInputs( channel );
    var i = 0;

    _feedHolder.clearAllAttributes();

    for ( var field in outputs ) {

        var outputTitle = outputs[field]['field_label'];
        var activity = true;

        if ( parseInt( outputs[field]['category_id'] ) > 2 && outputs[field]['value'] === '' ) {
            activity = false;
        } else if ( outputs[field]['category_id'] === "0" ) {
            activity = false;
        } else if ( parseInt( outputs[field]['category_id'] ) > 2 && outputs[field]['value'] === undefined ) {
            activity = false;
        }
		
		wppfm_setChannelRelatedPresets( outputs[field], channel );

        _feedHolder.addAttribute( i, outputTitle, inputs[outputTitle], outputs[field]['value'], outputs[field]['category_id'], activity, 0, 0, 0 );

        i++;
    }
}

// 160416 - obsolete
//function clearCondition( conditions, level ) {
//
//    var result = new Object();
//    var i = 1;
//
//    // remove the correct condition
//    delete conditions[level];
//
//    // re-sort the conditions
//    for ( var condition in conditions ) {
//
//        result[i] = conditions[condition];
//
//        i++;
//    }
//
//    return result;
//}

function wppfm_saveFeedToDb( feed, callback ) {

    // store the feed in a local variable
    _feedHolder = feed;

    var metaToStore = wppfm_filterActiveMetaData( _feedHolder['attributes'], _feedHolder['categoryMapping'] );

    wppfm_updateFeedToDb( _feedHolder, metaToStore, function ( response ) {

        callback( response.trim() );
    } );
}

function wppfm_filterActiveMetaData( metaData, categoryMapping ) {

    // make a storage place to store the changed attributes
    var activeMeta = [ ];

    for ( var i = 0; i < metaData.length; i++ ) {

        // if the advised source is not equal to the advised inputs, the user has selected his own input so this needs to be stored
        if ( metaData[i]['value'] !== undefined && metaData[i]['value'] !== "" && metaData[i]['isActive'] === true ) {

            // store a 
            activeMeta.push( new wppfm_attributeMeta( metaData[i]['fieldName'], metaData[i]['value'] ) );
        }
    }

    // also store the category mapping as meta data
    if ( categoryMapping.length > 0 ) {

        activeMeta.push( new wppfm_attributeMeta( 'category_mapping', categoryMapping ) );
    }

    return activeMeta;
}