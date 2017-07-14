/*!
 * feed-list.js v1.0
 * Part of the WP Product Feed Manager
 * Copyright 2016, Michel Jongbloed
 *
 */

"use strict";

var $jq = jQuery.noConflict();

function wppfm_source_is_filled( rowId, sourceLevel, conditionLevel ) {
	
	if ( $jq( '#input-field-cntrl-' + rowId + '-' + sourceLevel + '-' + conditionLevel ).val() !== 'select' ) {
		
		return true;
	} else {
		
		 return false;
	}
}

function wppfm_change_value_is_filled( rowId, sourceLevel, conditionLevel ) {
	
	var result = false;
	var changeSelectorValue = $jq( '#value-options-' + rowId + '-' + sourceLevel + '-' + conditionLevel ).val();
	var changeOptionsValue = $jq( '#value-options-input-' + rowId + '-' + sourceLevel + '-' + conditionLevel ).val();
	
	if ( changeOptionsValue ) {
		
		if ( changeSelectorValue === '2' ) { // replace
			
			result = $jq( '#value-options-input-with-' + rowId + '-' + sourceLevel + '-' + conditionLevel ).val() ? true : false;
		} else {
		
			result = true;
		}
	}
	
	return result;
}

function wppfm_query_is_filled( rowId, sourceLevel, queryLevel ) {
	
	var identString = rowId + '-' + sourceLevel + '-' + queryLevel;
	var result = false;
	var querySourceSelectorValue = $jq( '#value-options-input-field-cntrl-' + identString ).val();
	var querySelectorValue = $jq( '#value-query-condition-' + identString + '-0' ).val();
	var queryValue = $jq( '#value-options-condition-value-' + identString ).val();
	
	if ( sourceLevel < 0 ) { return true; } // there is no previous query so accept
	
	if ( querySourceSelectorValue !== 'select' ) {

		if ( querySelectorValue !== '4' && querySelectorValue !== '5' ) {
			
			if ( querySelectorValue === '14' ) {
				
				result = $jq( '#value-options-condition-and-value-input-' + identString ).val() && queryValue ? true : false;
			} else {
				
				result = queryValue ? true : false;
			}
		} else {
			
			result = true;
		}
	}
	
	return result;
}

function wppfm_feedFilterIsFilled( feedId, filterLevel ) {
	
	var identifierString = feedId + '-' + filterLevel;
	var result = false;
	var querySourceSelectorValue = $jq( '#filter-source-control-' + identifierString ).val();
	var querySelectorValue = $jq( '#filter-options-control-' + identifierString ).val();
	var queryValue = $jq( '#filter-input-control-' + identifierString + '-1' ).val();
	
	if ( filterLevel < 0 ) { return true; } // there is no previous filter so accept
	
	if ( querySourceSelectorValue !== 'select' ) {

		if ( querySelectorValue !== '4' && querySelectorValue !== '5' ) {
			
			if ( querySelectorValue === '14' ) {
				
				result = $jq( '#filter-input-control-' + identifierString + '-2' ).val() && queryValue ? true : false;
			} else {
				
				result = queryValue ? true : false;
			}
		} else {
			
			result = true;
		}
	}
	
	return result;
}