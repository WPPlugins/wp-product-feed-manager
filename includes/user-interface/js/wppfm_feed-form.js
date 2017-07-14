/*!
 * feed-form.js v3.4
 * Part of the WP Product Feed Manager
 * Copyright 2017, Michel Jongbloed
 *
 */

"use strict";

var $jq = jQuery.noConflict();

var _mandatoryFields = [ ];
var _highlyRecommendedFields = [ ];
var _recommendedFields = [ ];
var _undefinedRecommendedOutputs = [ ];
var _definedRecommendedOutputs = [ ];
var _optionalFields = [ ];
var _undefinedOptionalOutputs = [ ];
var _definedOptionalOutputs = [ ];
var _undefinedCustomOutputs = [ ];
var _customFields = [ ];
var _inputFields = [ ];
var _feedHolder;


/**
 * Gets triggered when one of the main inputs on the edit feed page has changed. This function starts a new feed when all
 * main inputs are given or updates the existing feed if required, based on the changed main input
 * 
 * @param {boolean} categoryChanged
 * @returns {nothing}
 */
function wppfm_mainInputChanged( categoryChanged ) {

	var channel = $jq( '#merchants' ).val();
	var feedId = _feedHolder['feedId'];
	
	wppfm_reactOnChannelInputChanged( channel, feedId, categoryChanged );
}

function wppfm_file_name_changed() {
	_feedHolder['title'] = $jq( '#file-name' ).val();
}

function wppfm_freeCategoryChanged( type, id ) {

	if ( type === 'default' ) { // default category selection changed

		if ( id ) {

			if ( id > 0 ) {
				_feedHolder.setCustomCategory( undefined, $jq( '#free-category-text-input' ).val() );
			} else {
				wppfm_mainInputChanged( true );
			}
		} else {
			// starts the new feed and stores the input as a 
			wppfm_mainInputChanged( true );
		}

	} else { // category mapping selection changed
		//wppfm_setChildCategories( id, $jq( '#feed-category-' + id + ' :input' ).val() ); // TODO: would be better if I could reuse this function
		_feedHolder.changeCustomFeedCategoryMap( id, $jq( '#feed-category-' + id + ' :input' ).val() );
	}
}

/**
 * Generates a new feed and opens the feed table
 * 
 * @returns {nothing}
 */
function wppfm_constructNewFeed() {

	// get all the data from the input fields
	var fileName = $jq( '#file-name' ).val();
	var source = parseInt( $jq( '#sources' ).val() );
	var mainCategory = $jq( '#lvl_0 option:selected' ).text();
	var categoryMapping = [ ];
	var channel = $jq( '#merchants' ).val();
	var variations = $jq( '#variations' ).is( ':checked' ) ? 1 : 0;
	var aggregator = $jq( '#aggregator' ).is( ':checked' ) ? 1 : 0;
	var country = $jq( '#countries' ).val();
	var feedTitle = $jq( '#google-feed-title-selector' ).val();
	var feedDescription = $jq( '#google-feed-description-selector' ).val();
	var daysInterval = $jq( '#days-interval' ).val() !== '' ? $jq( '#days-interval' ).val() : '1';
	var hours = $jq( 'update-schedule-hours' ).val() !== '' ? $jq( 'update-schedule-hours' ).val() : '00';
	var minutes = $jq( 'update-schedule-minutes' ).val() !== '' ? $jq( 'update-schedule-minutes' ).val() : '00';
	var frequency = $jq( 'update-schedule-frequency' ).val() !== '' ? $jq( 'update-schedule-frequency' ).val() : '1';
	var feedFilter = [ ];
	var status = 2;
	var channel_feed_type = wppfm_getChannelFeedType( channel );

	// make the url to the feed file
	var url = $jq( '#wp-plugin-url' ).text() + '/wppfm-feeds/' + fileName + '.' + channel_feed_type;
	var updates = daysInterval + ':' + hours + ':' + minutes + ':' + frequency;

	wppfm_setScheduleSelector( daysInterval, frequency );
	
	// make a new feed object
	_feedHolder = new Feed( -1, fileName, variations, aggregator, parseInt( channel ), mainCategory, categoryMapping, url, source, country, feedTitle, feedDescription, updates, feedFilter, status );
}

function wppfm_finishOrUpdateFeedPage( categoryChanged ) {

	wppfm_show_feed_spinner();

	var channel = $jq( '#merchants' ).val().toString();

	// make sure the data is correct
	_feedHolder['title'] = $jq( '#file-name' ).val();
	_feedHolder['channel'] = $jq( '#merchants' ).val();
	_feedHolder['includeVariations'] = $jq( '#variations' ).is( ':checked' ) ? '1' : '0';
	_feedHolder['isAggregator'] = $jq( '#aggregator' ).is( ':checked' ) ? '1' : '0';
	_feedHolder['feedTitle'] = $jq( '#google-feed-title-selector' ).val();
	_feedHolder['feedDescription'] = $jq( '#google-feed-description-selector' ).val();

	// get the output fields that can be used with the selected channel
	wppfm_outputFields( -1, channel, function ( outputs ) {

		_feedHolder.setUpdateSchedule( $jq( '#days-interval' ).val(), $jq( '#update-schedule-hours' ).val(), 
			$jq( '#update-schedule-minutes' ).val(), $jq( '#update-schedule-frequency' ).val() );

		// add the attributes
		wppfm_addFeedAttributes( outputs, channel, 1 );

		wppfm_customSourceFields( _feedHolder['dataSource'], function ( customFields ) {
			
			_feedHolder['country'] = $jq( '#countries' ).val();
			
			wppfm_fillSourcesList( customFields );

			wppfm_mainFeedFilters( _feedHolder['feedId'], function ( feedFilters ) {

				// get the master feed filter
				var mainFeedFilter = feedFilters !== 1 ? feedFilters : null;
				_feedHolder.setFeedFilter( mainFeedFilter );

				// set the correct level of the attributes
				_feedHolder = wppfm_setOutputAttributeLevels( channel, _feedHolder, _feedHolder['country'] );

				wppfm_makeFeedFilterWrapper( _feedHolder['feedId'], _feedHolder['feedFilter'] );

				if ( categoryChanged ) {

					_feedHolder['mainCategory'] = $jq( '#lvl_0' ).val() ? $jq( '#lvl_0' ).val() : $jq( '#free-category-text-input' ).val();
					_feedHolder.setMainCategory( 'lvl_0', $jq( '#lvl_0' ).val(), channel );
				}

				// draws the fields table on the form
				wppfm_makeFieldsTable();

				if ( _feedHolder !== 0 ) {

					var isNew = _feedHolder['feedId'] === -1 ? true : false;
					wppfm_fillFeedFields( isNew, categoryChanged );
				}

				// TODO: somewhere between the initialization of channel in _feedHolder the channel id is changed
				// to an integer in stead of the required string. For now I just reset the variable to
				// a string again, but I need to figure out why this is happening. Before the wppfm_addFeedAttributes
				// it is still a string. When it's an int, the static menus of a new feed will not work anymore.
				_feedHolder['channel'] = channel;

				wppfm_hide_feed_spinner();
			} );
		} );
	} );
}

function wppfm_editExistingFeed( feedId ) {

	wppfm_show_feed_spinner();

	// read the feed data from the database
	wppfm_getFeedObject( feedId, function ( feedObject ) {

		// put the data in the _feed object
		_feedHolder = feedObject;

		var channel = _feedHolder['channel'];
		var categoryString = _feedHolder['mainCategory'];
		var mainCategory = categoryString.indexOf( ' > ' ) > -1 ? categoryString.substring( 0, categoryString.indexOf( ' > ' ) ) : categoryString;
		
		wppfm_fillCategoryVariables( channel, mainCategory, 0); // make sure the category values are set correctly
		
		// get all possible output fields from the database
		wppfm_outputFields( _feedHolder['feedId'], channel, function ( outputs ) {

			// set the found output fields as attributes in the feed object
			wppfm_addFeedAttributes( outputs, channel, 1 );

			wppfm_customSourceFields( _feedHolder['dataSource'], function ( customFields ) {

				wppfm_fillSourcesList( customFields );
				
				wppfm_mainFeedFilters( feedId, function ( feedFilters ) {

					// get the master feed filter
					var mainFeedFilter = feedFilters !== 1 ? feedFilters : null;
					_feedHolder.setFeedFilter( mainFeedFilter );

					// set the correct level of the attributes
					_feedHolder = wppfm_setOutputAttributeLevels( channel, _feedHolder, _feedHolder['country'] );

					wppfm_makeFeedFilterWrapper( _feedHolder['feedId'], _feedHolder['feedFilter'] );

					// draws the fields table on the form
					wppfm_makeFieldsTable();

					if ( _feedHolder !== 0 ) wppfm_fillFeedFields( false, false );

					if ( _feedHolder['categoryMapping'] ) wppfm_setCategoryMap( _feedHolder['categoryMapping'] );

					// enable the Generate and Save buttons and the target country selection
					wppfm_enableFeedActionButtons();

					$jq( '#countries' ).prop( 'disabled', false );

					// set the default categories select fields in the background
					wppfm_fillDefaultCategorySelectors();

					// set the identifier_exists layout
					wppfm_setIdentifierExistsDependancies();

					wppfm_hide_feed_spinner();
				} );
			} );
		} );
	} );
}

function wppfm_fillSourcesList( customFields ) {

	_inputFields = wppfm_woocommerceSourceOptions();
	wppfm_addCustomFieldsToInputFields( _inputFields, customFields );
	_inputFields.sort( function(a,b){ return (''+a.label).toUpperCase() < (''+b.label).toUpperCase() ? -1 : 1; });
}

function wppfm_saveUpdateSchedule() {
	
	// get the values
	var days = $jq( '#days-interval' ).val();
	var hours = $jq( '#update-schedule-hours' ).val();
	var mins = $jq( '#update-schedule-minutes' ).val();
	var freq = $jq( '#update-schedule-frequency' ).val();

	// change the form selector if required
	if ( days !== '1' ) { freq = '1'; }
	
	if ( freq === 1 ) { days = '1'; }
	
	wppfm_setScheduleSelector( days, freq );

	// store the selection in the feed
	_feedHolder.setUpdateSchedule( days, hours, mins, freq );
}

function wppfm_setScheduleSelector( days, freq ) {

	// change the form selector if required
	if ( days === '1' ) {
		$jq( '#wppfm-update-frequency-wrapper' ).show();
	} else {
		$jq( '#wppfm-update-frequency-wrapper' ).hide();
	}
	
	if ( freq > 1 ) {
		$jq( '#wppfm-update-day-wrapper' ).hide();
		$jq( '#wppfm-update-every-day-wrapper' ).show();
	} else {
		$jq( '#wppfm-update-day-wrapper' ).show();
		$jq( '#wppfm-update-every-day-wrapper' ).hide();
	}
}

function wppfm_addCustomFieldsToInputFields( inputFields, customFields ) {

	if ( customFields !== '0' ) {
		for ( var i = 0; i < customFields.length; i++ ) {

			var field = { value: customFields[i].attribute_name, label: customFields[i].attribute_label, prop: 'custom' };
			inputFields.push( field );
		}
	}
}

/**
 * Gets the correct categories from the category file and fills the category selector with them
 * 
 * @returns {nothing}
 */
function wppfm_fillDefaultCategorySelectors( ) {

	var mainCategoriesString = _feedHolder['mainCategory'];
	var channel = _feedHolder['channel'];
	var language = wppfm_channelCountryCode( channel );

	wppfm_getCategoryListsFromString( channel, mainCategoriesString, language, function ( categories ) {

		var lists = JSON.parse( categories );

		if ( lists && lists.length > 0 && mainCategoriesString !== undefined ) {

			var categoriesArray = mainCategoriesString.split( ' > ' );

			for ( var i = 0; i < lists.length; i++ ) {

				$jq( '#lvl_' + i ).append( wppfm_categorySelectCntrl( lists[i] ) );

				var element = document.getElementById( 'lvl_' + i );
				element.value = categoriesArray[i];
			}
		} else {

			$jq( '#lvl_0' ).prop( 'disabled', false );
		}
	} );
}

function wppfm_google_feed_title_changed() {
	_feedHolder['feedTitle'] = $jq( '#google-feed-title-selector' ).val();
}

function wppfm_google_feed_description_changed() {
	_feedHolder['feedDescription'] = $jq( '#google-feed-description-selector' ).val();
}

function wppfm_setCategoryMap( mapping ) {

	var map = JSON.parse( mapping );

	for ( var i = 0; i < map.length; i++ ) {

		var categoryId = map[i].shopCategoryId;
		var mapString = '';

		switch ( map[i].feedCategories ) {

			case 'wp_mainCategory':
				mapString = wppfm_mapToDefaultCategoryElement( categoryId, 'default' );
				break;

			case 'wp_ownCategory':
				mapString = wppfm_mapToDefaultCategoryElement( categoryId, 'shopCategory' );
				break;

			default:
				mapString = wppfm_mapToCategoryElement( categoryId, map[i].feedCategories );
				break;
		}

		$jq( '#feed-selector-' + categoryId ).prop( 'checked', true );
		$jq( '#feed-category-' + categoryId ).html( mapString );
	}
}

function wppfm_generateAndSaveFeed() {

	wppfm_show_feed_spinner();
	
// 190517
//	_feedHolder['mainCategory'] = _feedHolder['channel'] !== '15' ? _feedHolder['mainCategory'] : 'no category required'; // Commerce Connector uses no channels
	_feedHolder['mainCategory'] = !wppfm_channelUsesOwnCategories( _feedHolder['channel'] ) ? _feedHolder['mainCategory'] : 'no category required';

	// save the feed data to the database
	wppfm_saveFeedToDb( _feedHolder, function ( dbResult ) {

		var newFeed = _feedHolder['feedId'] === -1 ? true : false;

		// the wppfm_saveFeedToDb returns the entered feed id
		if ( dbResult === 0 ) {

			console.log( 'Saving the data to the data base has failed!' );
			wppfm_show_error_message( 'Saving the data to the data base has failed! Please try again.' );
		} else {

			// insert the feed id in the _feed
			_feedHolder['feedId'] = dbResult;

			if ( newFeed ) {

				// reset the url to implement the feed id so the user can reset the form if he wants
				var currentUrl = window.location.href;
				window.location.href = currentUrl + '&id=' + _feedHolder['feedId'];
			}
		}

		// convert the data to xml or csv and save the code to a feed file
		wppfm_updateFeedFile( _feedHolder, function ( xmlResult ) {

			if ( xmlResult !== '1' && xmlResult !== 1 && ! xmlResult.includes( 'successfully' ) ) {

				wppfm_show_error_message( "Generating the xml file has failed! Return code = " + xmlResult + "." );

				wppfm_hide_feed_spinner();
			} else {

				if ( ! newFeed ) {

					wppfm_hide_feed_spinner();
				}
				
				if ( xmlResult.includes( 'successfully' ) ) {

					wppfm_show_success_message( xmlResult );
				}
			}
		} );
	} );
}

function wppfm_saveFeed() {

	wppfm_show_feed_spinner();

	var newFeed = _feedHolder['feedId'] === -1 ? true : false;
	
	_feedHolder['mainCategory'] = !wppfm_channelUsesOwnCategories( _feedHolder['channel'] ) ? _feedHolder['mainCategory'] : 'no category required';

	if ( newFeed ) _feedHolder['url'] = 'No feed generated';

	// save the feed data to the database
	wppfm_saveFeedToDb( _feedHolder, function ( dbResult ) {

		// the wppfm_saveFeedToDb returns the entered feed id
		if ( dbResult === 0 ) {
			console.log( 'Saving the data to the data base has failed!' );
			wppfm_show_error_message( 'Saving the data to the data base has failed! Please try again.' );
		} else {

			// insert the feed id in the _feed
			_feedHolder['feedId'] = dbResult;

			if ( newFeed ) {
				// reset the url to implement the feed id so the user can reset the form if he wants
				var currentUrl = window.location.href;
				window.location.href = currentUrl + '&id=' + _feedHolder['feedId'];
			}
		}

		wppfm_hide_feed_spinner();
	} );
}

/**
 * Gets the next category list and fills the selector with it
 * 
 * @param {string} currentLevelId
 * @returns {nothing}
 */
function wppfm_nextCategory( currentLevelId ) {

	var nextLevelId = wppfm_incrementLast( currentLevelId );
	var nextLevel = nextLevelId.match( /(\d+)$/ )[0]; // get the number on the end of the nextLevelId
	var selectedCategory = $jq( '#' + currentLevelId ).val();
	var channel = _feedHolder['channel'] ? _feedHolder['channel'].toString() : $jq( '#merchants' ).val();
	var language = wppfm_channelCountryCode( channel );

	if ( nextLevel > 1 ) {
		wppfm_show_feed_spinner();
	}

	// show the correct sub level selectors, hide the others
	wppfm_hideSubs( currentLevelId );
	
	// fill the special filter variables
	wppfm_fillCategoryVariables( channel, selectedCategory, currentLevelId );

	// get the next level selector
	wppfm_getNextCategories( channel, nextLevel, selectedCategory, language, function ( categories ) {

		var list = JSON.parse( categories );

		if ( list.length > 0 ) {

			$jq( '#' + nextLevelId ).append( wppfm_categorySelectCntrl( list ) );
			$jq( '#' + nextLevelId ).show();
		}

		if ( currentLevelId.indexOf( 'catmap' ) > -1 ) { // the selection is from the category map

			wppfm_setChildCategories( currentLevelId, selectedCategory );
		} else { // the selection is from the default category

			_feedHolder.setMainCategory( currentLevelId, selectedCategory, channel );

			if ( _feedHolder['attributes'].length > 0 && _feedHolder['attributes'][3]['value'] !== undefined && _feedHolder['attributes'][3]['value'] !== '' ) {

				$jq( '#category-source-string' ).html( JSON.parse( _feedHolder['attributes'][3]['value'] ).t );
			} else {

				$jq( '#category-source-string' ).html( _feedHolder['mainCategory'] );
			}
		}

		if ( nextLevel > 1 ) {
			wppfm_hide_feed_spinner();
		}
	} );
}

function wppfm_setChildCategories( categorySelectorId, selectedCategory ) {

	var selectedLevel = categorySelectorId.match( /(\d+)$/ )[0]; // next level
	var sc = categorySelectorId.replace( '_' + selectedLevel, '' );
	var shopCategoryId = sc.match( /(\d+)$/ )[0];

	var children = $jq( '#feed-selector-' + shopCategoryId ).attr( "data-children" ) ? JSON.parse( $jq( '#feed-selector-' + shopCategoryId ).attr( "data-children" ) ) : [ ];

	for ( var i = 0; i < children.length; i++ ) {

		wppfm_setChildrenToParentCategory( children[i], selectedLevel, selectedCategory );
	}

	_feedHolder.mapCategory( categorySelectorId, selectedCategory );
}

function wppfm_variation_selection_changed() {
	alert( "The option to add product variations to the feed is not available in the free version. Unlock this option by upgrading to the Premium plugin. For more information goto http://www.wpmarketingrobot.com/." );
	_feedHolder.changeIncludeVariations( false );
	$jq( '#variations' ).prop( 'checked', false );
}

function wppfm_aggregatorChanged() {
	
	console.log(_feedHolder.attributes);

	if ( $jq( '#aggregator' ).is(":checked") ) {

		_feedHolder.changeAggregator( true );
		_feedHolder.attributes[8]['fieldLevel'] = "1";
	} else {

		_feedHolder.changeAggregator( false );
		_feedHolder.attributes[8]['fieldLevel'] = "0";
		_feedHolder.deactivateAttribute( '8' );
	}
}

function wppfm_setChildrenToParentCategory( childId, level, parentCategory ) {

	var currentCategory = $jq( '#category-text-span-' + childId ).text();
	var newCategoryString = wppfm_addNewItemToCategoryString( level, currentCategory, parentCategory, ' > ' );
	var categorySelectorId = 'catmap-' + childId + '_' + level;

	$jq( '#category-text-span-' + childId ).text( newCategoryString );

	var children = $jq( '#feed-selector-' + childId ).attr( "data-children" ) ? JSON.parse( $jq( '#feed-selector-' + childId ).attr( "data-children" ) ) : [ ];

	for ( var i = 0; i < children.length; i++ ) {

		wppfm_setChildrenToParentCategory( children[i], level, parentCategory );
	}

	_feedHolder.mapCategory( categorySelectorId, parentCategory );
}

function wppfm_fillFeedFields( isNew, categoryChanged ) {

	// if the category attribute has a value
	if ( _feedHolder['mainCategory'] && isNew === false ) {

		// and display the category in the Default Category input field unless only the category has been changed
		if ( !categoryChanged ) {

			// make the category string
			var categoryString = _feedHolder['mainCategory'] +
				' (<a class="edit-categories wppfm-btn wppfm-btn-small" href="javascript:void(0)" ' +
				'id="categories" onclick="wppfm_editCategories()">edit</a>)';

			$jq( '#lvl_0' ).hide();
			$jq( '#selected-categories' ).html( categoryString );
			$jq( '#selected-merchant' ).html( $jq( "#merchants option[value='" + _feedHolder['channel'] + "']" ).text() );
			$jq( '#merchants' ).hide();
		}
	} else {

		$jq( '#lvl_0' ).css( 'display', 'initial' );
	}

	var schedule = _feedHolder['updateSchedule'].split( ':' );
	
	if ( ! isNew ) { 
		wppfm_showChannelInputs( _feedHolder['channel'], isNew ); 
	} else {
		$jq( '#category-map' ).show();
	}
	
	$jq( '#file-name' ).val( _feedHolder['title'] );
	$jq( '#merchants' ).val( _feedHolder['channel'] );
	$jq( '#variations' ).prop( 'checked', _feedHolder['includeVariations'] > 0 ? true : false );
	$jq( '#aggregator' ).prop( 'checked', _feedHolder['isAggregator'] > 0 ? true : false );
	$jq( '#countries' ).val( _feedHolder['country'] );
	$jq( '#google-feed-title-selector' ).val( _feedHolder['feedTitle'] );
	$jq( '#google-feed-description-selector' ).val( _feedHolder['feedDescription'] );
	$jq( '#days-interval' ).val( schedule[0] );

	// get the link to the update schedule selectors
	var hrsSelector = document.getElementById( 'update-schedule-hours' );
	var mntsSelector = document.getElementById( 'update-schedule-minutes' );
	var freqSelector = document.getElementById( 'update-schedule-frequency' );
	
	// set the values of the update schedule selectors
	hrsSelector.value = schedule[1];
	mntsSelector.value = schedule[2];
	freqSelector.value = schedule[3] ? schedule[3] : '1'; // standard setting is once a day
	
	// set the layout of the update schedule selectors
	wppfm_setScheduleSelector( schedule[0], schedule[3] );
}

function wppfm_categorySelectCntrl( categories ) {

	var htmlCode = '<option value="0">Select a sub-category</option>';

	for ( var i = 0; i < categories.length; i++ ) {

		htmlCode += '<option value="' + categories[i] + '">' + categories[i] + '</option>';
	}

	return htmlCode;
}

/**
 * Edits the mapping of the feed categories to the shop categories
 * 
 * @param {string} id
 * @returns {nothing}
 */
function wppfm_editCategoryMapping( id ) {

	var channel = _feedHolder['channel'];
	var language = wppfm_channelCountryCode( channel );

	if ( !wppfm_isCustomChannel( channel ) ) {

		wppfm_getCategoryListsFromString( channel, "", language, function ( categories ) {

			var list = JSON.parse( categories )[0];

			if ( list && list.length > 0 ) {

				$jq( '#catmap-' + id + '_0' ).append( wppfm_categorySelectCntrl( list ) );
				$jq( '#catmap-' + id + '_0' ).prop( 'disabled', false );
			} else {

				$jq( '#catmap-' + id + '_0' ).prop( 'disabled', false );
			}

			$jq( '#feed-category-' + id ).html( "" );
			$jq( '#category-selector-catmap-' + id ).show();
		} );
	} else {

//        $jq( '#feed-category-' + id ).html( wppfm_freeCategoryInputCntrl( 'mapping', 'lvl_' + id, _feedHolder['mainCategory'] ) );
		$jq( '#feed-category-' + id ).html( wppfm_freeCategoryInputCntrl( 'mapping', id, _feedHolder['mainCategory'] ) );
		$jq( '#category-selector-catmap-' + id ).hide();
	}
}

function wppfm_activateOptionalFieldRow( level, name ) {

	var attributeId = _feedHolder.getAttributeIdByName( name );

	// register the new optional field as an active input
	_feedHolder.activateAttribute( attributeId );

	if ( _feedHolder['attributes'][attributeId]['advisedSource'] ) { // when the selected input has an advised value, 
																	 // save the advised value in the attributes to make sure the selection is saved
		_feedHolder.setSourceValue( attributeId, level, _feedHolder['attributes'][attributeId]['advisedSource'] );
	}

	// get the html code for the new source row that needs to be added to the form
	var code = wppfm_fieldRow( _feedHolder['attributes'][attributeId], true );

	// reset the lists that contain the selected and non selected output fields
	var ind = _undefinedRecommendedOutputs.indexOf( name );
	if ( ind > -1 ) {
		_undefinedRecommendedOutputs.splice( ind, 1 );
	}
	_definedRecommendedOutputs.push( _feedHolder['attributes'][attributeId]['fieldName'] );
	_definedRecommendedOutputs.sort();

	// reset the output selector
	$jq( "#output-field-cntrl-" + level ).empty();
	$jq( "#output-field-cntrl-" + level ).html( wppfm_outputFieldCntrl( level ) );

	if ( level === 3 ) {

		$jq( '#new-recommended-row' ).append( code );
		$jq( '#new-recommended-row' ).show();
	} else if ( level === 4 ) {

		$jq( '#new-optional-row' ).append( code );
	}
}

function wppfm_deactivateOptionalFieldRow( rowId, level, name ) {

	_feedHolder.deactivateAttribute( rowId );

	$jq( "#output-field-cntrl-" + level ).append( "<option value='" + name + "'>" + name + "</option>" );

	$jq( '#row-' + rowId ).html( '' );

	$jq( '#new-recommended-row' ).hide();

}

function wppfm_activateCustomFieldRow( fieldName ) {

	if ( !_feedHolder.checkIfCustomNameExists( fieldName ) ) { // prevent doubles

		var attributeId = _feedHolder.getAttributeIdByName( fieldName );

		// register the new custom field
		_feedHolder.addAttribute( attributeId, fieldName, '', undefined, "5", true, 0, 0, 0, 0 );

		$jq( '#new-custom-row' ).append( wppfm_fieldRow( _feedHolder['attributes'][attributeId], true ) );
		$jq( '#new-custom-row' ).show();
	} else {

		alert( 'You already have a field ' + fieldName + ' defined!' );
	}

	console.log( _feedHolder['attributes'] );
	// clear the input field
	$jq( '#custom-output-title-input' ).val( '' );
}

function wppfm_setStaticValue( attributeId, conditionLevel, combinationLevel ) {

	console.log(attributeId);
	console.log(conditionLevel);
	console.log(combinationLevel);
	console.log(_feedHolder['attributes'][attributeId]['value']);

	var staticValue = $jq( '#static-input-field-' + attributeId + '-' + conditionLevel + '-' + combinationLevel ).val();

	if ( staticValue === undefined ) {

		staticValue = $jq( '#static-condition-input-' + attributeId + '-' + conditionLevel + '-' + combinationLevel + ' option:selected' ).text();
	}

	// store the changed static value in the feed
	_feedHolder.setStaticAttributeValue( attributeId, conditionLevel, combinationLevel, staticValue );
}

function wppfm_setIdentifierExistsDependancies() {

	var staticValue = $jq( '#static-input-field-34-0' ).val();

	if ( staticValue === undefined ) {

		staticValue = $jq( '#static-condition-input-34-0 option:selected' ).text();
	}

	switch ( staticValue ) {

		case 'true':
			wppfm_resetOutputField( 12, 1, true );
			wppfm_resetOutputField( 13, 1, true );
			break;

		case 'false':
			wppfm_resetOutputField( 12, 3, false );
			wppfm_resetOutputField( 13, 3, false );
			break;

		default:
			break;
	}
}

function wppfm_resetOutputField( fieldId, level, isActive ) {

	var oldLevel = _feedHolder['attributes'][fieldId]['fieldLevel'];

	// set the attribute status
	_feedHolder['attributes'][fieldId]['fieldLevel'] = level;
	_feedHolder['attributes'][fieldId]['isActive'] = isActive;

	//show or hide the output field
	var outputField = wppfm_fieldRow( _feedHolder['attributes'][fieldId], false );
	var fieldName = _feedHolder['attributes'][fieldId]['fieldName'];

	if ( isActive ) {

		$jq( '#required-field-table' ).append( outputField );
		$jq( "#output-field-cntrl-" + oldLevel + " option[value='" + fieldName + "']" ).remove();
		$jq( "#recommended-field-table #row-" + fieldId ).remove();

	} else {

		$jq( '#row-' + fieldId ).replaceWith( '' );
		$jq( '#output-field-cntrl-' + level ).append( '<option value="' + fieldName + '">' + fieldName + '</option>' );
	}
}

/**
 * Returns the html code for a single field table row
 * 
 * @param {string array} attributeData
 * @param {boolean} removable
 * @returns {String} containing the html
 */
function wppfm_fieldRow( attributeData, removable ) {

	var rowId = attributeData['rowId'];
	var sourceRowsData = _feedHolder.getSourceObject( rowId );
	var nrOfSources = countSources( sourceRowsData.mapping );

	// add an extra row when there is a condition in the last source row and an advised source but no user set source
	if ( sourceRowsData.advisedSource && wppfm_hasExtraSourceRow( nrOfSources, sourceRowsData.mapping ) ) {
		nrOfSources++;
	}

	// row wrapper
	var htmlCode = '<div class="field-table-row-wrapper" id="row-' + rowId + '">';

	for ( var i = 0; i < nrOfSources; i++ ) {

		htmlCode += wppfm_addFeedSourceRow( rowId, i, sourceRowsData, _feedHolder.channel, removable );
	}

	for ( var i = 0; i < sourceRowsData.changeValues.length; i++ ) {

		if ( sourceRowsData.changeValues[0] ) {

			// add the change value editor fields
			htmlCode += wppfm_valueEditor( sourceRowsData.rowId, i, i, sourceRowsData.changeValues );
		}
	}

	// end the row and start a new one
	htmlCode += wppfm_endrow( rowId );
	htmlCode += '</div>';

	return htmlCode;
}

function wppfm_addSourceDataAndQueriesColumn( sourceLevel, sourceRowsData ) {

	var htmlCode = '';
	var levelString = sourceRowsData.rowId + '-' + sourceLevel;
	var sourceValue = wppfm_getMappingSourceValue( sourceRowsData.mapping, sourceLevel );
	var combinedValue = wppfm_getMappingCombinedValue( sourceRowsData.mapping, sourceLevel );
	var staticValue = wppfm_getMappingStaticValue( sourceRowsData.mapping, sourceLevel );
	var sourceIsCategory = sourceRowsData.customCondition ? true : false;
	var sourceMappingHasSourceData = sourceValue || combinedValue || staticValue ? true : false;
	var channel = _feedHolder['channel'].toString();
	var conditions = wppfm_getMappingConditions( sourceRowsData.mapping, sourceLevel );
	var conditionsCounter = wppfm_countObjectItems( conditions );

	// wrap the source controls
	htmlCode += '<div class="source-selector colw col30w" id="source-select-' + levelString + '">';

//    if ( ( channel !== '0' && channel !== '999' && sourceRowsData.rowId === 3 ) ) || sourceIsCategory === true ) { // row 3 is the standard category row
	if ( sourceIsCategory ) {
		htmlCode += wppfm_categorySource();
	} else if ( ( sourceRowsData.advisedSource && !sourceMappingHasSourceData && sourceRowsData.advisedSource !== 'Fill with a static value' )
		|| sourceRowsData.advisedSource === sourceValue ) {

		// remove underscore where applicable
		//var advisedSourceWithoutUnderscore = sourceRowsData.advisedSource.charAt(0) === '_' ? sourceRowsData.advisedSource.substr(1) : sourceRowsData.advisedSource;
		var advisedSourceLabel = $jq.grep( _inputFields, function ( e ) {
			return e.value === sourceRowsData.advisedSource;
		} );
		advisedSourceLabel = advisedSourceLabel[0] ? advisedSourceLabel[0].label : sourceRowsData.advisedSource;

		htmlCode += wppfm_advisedSourceSelector( sourceRowsData.rowId, sourceLevel, advisedSourceLabel,
			channel, sourceRowsData.mapping );
	} else if ( ( !sourceRowsData.advisedSource && !sourceValue )
		|| sourceMappingHasSourceData
		|| sourceRowsData.advisedSource === 'Fill with a static value' ) {

		htmlCode += wppfm_inputFieldCntrl( sourceRowsData.rowId, sourceLevel, sourceValue, staticValue, sourceRowsData.advisedSource, combinedValue, wppfm_isCustomChannel( channel ) );

		if ( staticValue ) {

			htmlCode += wppfm_feedStaticValueSelector( sourceRowsData.fieldName, sourceRowsData.rowId, sourceLevel, 0,
				staticValue, channel );
		}

		if ( combinedValue ) {

			htmlCode += wppfm_combinedField( sourceRowsData.rowId, sourceLevel, 0, combinedValue, false, channel );
		}
	}

	// end the source control wrapper
	htmlCode += '</div>';

	htmlCode += '<div class="condition-selector colw" id="condition-data-' + levelString + '">';

	// and now fill the condition controls
//    if ( ( sourceRowsData.rowId !== 3 || channel === '0' || channel === '999' ) && ! conditions  ) { // row 3 is the standard category row
	if ( !sourceIsCategory && !conditions ) {

		htmlCode += '<div class="condition-wrapper" id="condition-' + levelString + '-0">';
		htmlCode += wppfm_forAllProductsCondition( sourceRowsData.rowId, sourceLevel, 'initial' );
		htmlCode += '</div>';

		//htmlCode += forAllProductsChangeValuesSelector( sourceRowsData.rowId, sourceLevel, 'initial' );
	} else if ( conditions ) {

		for ( var i = 0; i < conditionsCounter; i++ ) {

			htmlCode += wppfm_conditionSelectorCode( sourceRowsData.rowId, sourceLevel, i, conditionsCounter, conditions[i] );
		}
	}

	htmlCode += '</div>';

	return htmlCode;
}

function wppfm_addCombinedField( id, sourceLevel, combinedLevel ) {

	var allFilled = true;

	for ( var i = 0; i < combinedLevel; i++ ) {

		// do all fields have a selected value?
		if ( $jq( '#combined-input-field-cntrl-' + id + '-' + sourceLevel + '-' + combinedLevel ).val() === 'select' ) {

			allFilled = false;
			i = combinedLevel;
		}

		if ( $jq( '#combined-input-field-cntrl-' + id + '-' + sourceLevel + '-' + combinedLevel ).val() === 'static'
			&& $jq( '#static-input-field-' + id + '-' + sourceLevel + '-' + combinedLevel ).val() === '' ) {

			allFilled = false;
			i = combinedLevel;
		}
	}

	if ( allFilled ) {

		console.log(_feedHolder['attributes'][id]);
//		var combinedValueObject = JSON.parse( _feedHolder['attributes'][id]['value'] );
		var combinedValueObject = _feedHolder['attributes'][id]['value'] ? JSON.parse( _feedHolder['attributes'][id]['value'] ) : { };
		var combinedValuePart = 'm' in combinedValueObject ? combinedValueObject.m : { };
		var combinedValue = wppfm_getMappingCombinedValue( combinedValuePart, sourceLevel );
		var manualAdd = combinedLevel > 0 ? true : false;

		$jq( '#source-select-' + id + '-' + sourceLevel ).append( wppfm_combinedField( id, sourceLevel, combinedLevel, combinedValue, manualAdd ) );

		$jq( '#add-combined-field-' + id + '-' + sourceLevel-- ).hide();
	} else {

		alert( "Make sure to select all source fields before adding a new one!" );
	}
}

function wppfm_combinedField( rowId, sourceLevel, combinedLevel, fields, manualAdd ) {

	$jq( '#combined-wrapper-' + rowId + '-' + sourceLevel ).remove(); // reset the combined wrapper field

	var fieldsArray = fields ? fields.split( '|' ) : [ ];
	var fieldsLength = fields ? fieldsArray.length : 2; // if no field data is given, then show two empty input selectors

	if ( manualAdd ) {
		fieldsLength++;
	}

	var htmlCode = '<div class="combined-wrapper" id="combined-wrapper-' + rowId + '-' + sourceLevel + '">';

	// loop through the field items in the fields string
	for ( var i = 0; i < fieldsLength; i++ ) {

		var str = fieldsArray[i];
		var ind = str ? str.indexOf( '#' ) : 0;

		if ( str ) {

			var fieldSplit = str.substr( 0, ind ) !== 'static' ? [ str.substr( 0, ind ), str.substr( ind + 1 ) ]
				: [ '0', fieldsArray[i] ];
		} else {

			var fieldSplit = [ ];
		}

		htmlCode += wppfm_combinedCntrl( rowId, sourceLevel, i + 1, fieldsLength, 'delete', fieldSplit, _feedHolder['channel'] );
	}

	htmlCode += '</div>';

	return htmlCode;
}

function wppfm_combinedCntrl( rowId, sourceLevel, combinationLevel, nrQueries, type, valueArray, channel ) {

	var htmlCode = '';
	var fieldName = _feedHolder['attributes'][rowId]['fieldName'];

	htmlCode += '<span class="combined-field-row" id="combined-field-row-' + rowId + '-' + sourceLevel + '-' + combinationLevel + '">';

	if ( combinationLevel > 1 ) {

		htmlCode += wppfm_combinedSeparatorCntrl( rowId, sourceLevel, combinationLevel, valueArray[0] );
	}

	// draw the control
	htmlCode += wppfm_combinedInputFieldCntrl( rowId, sourceLevel, combinationLevel, valueArray[1], fieldName, channel );

	if ( type === 'initialize' ) {

		// add an extra control
		htmlCode += '<span class="combined-field-row" id="combined-field-row">';
		htmlCode += wppfm_combinedSeparatorCntrl( rowId, sourceLevel, combinationLevel, valueArray[0] );
		htmlCode += wppfm_combinedInputFieldCntrl( rowId, sourceLevel, 2, '', fieldName, channel );
//        htmlCode += '<span id="static-input-combined-field-' + rowId + '-' + sourceLevel + '-2"></span>';
		htmlCode += '<div class="static-value-control" id="static-value-control-' + rowId + '-' + sourceLevel + '-2"></div>';
	}

	if ( combinationLevel > 2 ) {

		// draw the "remove" button
		htmlCode += '<span id="remove-combined-field-' + rowId + '-' + sourceLevel + '-' + combinationLevel + '" style="display:initial">';
		htmlCode += '(<a class="remove-combined-field wppfm-btn wppfm-btn-small" href="javascript:void(0)"';
		htmlCode += ' onclick="wppfm_removeCombinedField(' + rowId + ', ' + sourceLevel + ', ' + combinationLevel + ')">remove</a>)</span>';
	}

	if ( combinationLevel === 2 || combinationLevel >= nrQueries ) {

		if ( combinationLevel === 1 ) {
			combinationLevel++;
		}

		// draw the "add" button
		if ( combinationLevel >= nrQueries ) {
			htmlCode += '<span id="add-combined-field-' + rowId + '-' + sourceLevel + '-' + combinationLevel + '" style="display:initial">';
			htmlCode += '(<a class="add-combined-field wppfm-btn wppfm-btn-small" href="javascript:void(0)"';
			htmlCode += ' onclick="wppfm_addCombinedField(' + rowId + ', ' + sourceLevel + ', ' + combinationLevel + ')">add</a>)</span>';
		}
	}

	htmlCode += '</span>';

	return htmlCode;
}

// TODO: this function looks like the wppfm_ifValueQuerySelector() function. Maybe I could combine these two?
function wppfm_ifConditionSelector( rowId, sourceLevel, conditionLevel, numberOfQueries, queryObject ) {

	var htmlCode = '';

	var query = wppfm_isEmptyQueryObject( queryObject ) ? wppfm_makeCleanQueryObject() : queryObject;

	var conditionSelector = 'if ';

	if ( conditionLevel > 1 ) {

		conditionSelector = query.preCondition === '1'
			? '<select id="sub-condition-' + rowId + '-' + sourceLevel + '-' + conditionLevel + '" onchange="wppfm_storeCondition(' + rowId + ', ' + sourceLevel + ', ' + conditionLevel + ')"><option value="2">or</option><option value="1" selected>and</option></select>'
			: '<select id="sub-condition-' + rowId + '-' + sourceLevel + '-' + conditionLevel + '" onchange="wppfm_storeCondition(' + rowId + ', ' + sourceLevel + ', ' + conditionLevel + ')"><option value="2" selected>or</option><option value="1">and</option></select>';
	}

	var storeConditionFunctionString = 'wppfm_storeCondition(' + rowId + ', ' + sourceLevel + ', ' + conditionLevel + ')';

	htmlCode += '<div class="condition-wrapper" id="condition-' + rowId + '-' + sourceLevel + '-' + conditionLevel + '">';
	htmlCode += conditionSelector;
	htmlCode += wppfm_conditionFieldCntrl( rowId, sourceLevel, conditionLevel, -1, 'input-field-cntrl', query.source, storeConditionFunctionString );
	htmlCode += wppfm_conditionQueryCntrl( rowId, sourceLevel, conditionLevel, -1, 'query-condition', 'wppfm_queryConditionChanged', query.condition );

	htmlCode += '<input type="text" onchange="wppfm_storeCondition(' + rowId + ', ' + sourceLevel + ', ' + conditionLevel + ')" name="condition-value" id="condition-value-';
	htmlCode += rowId + '-' + sourceLevel + '-' + conditionLevel + '" value="' + query.value + '"';

	if ( queryObject.condition !== '4' && queryObject.condition !== '5' ) {

		htmlCode += ' style="display:initial">';
	} else {

		htmlCode += ' style="display:none;">';
	}

	if ( queryObject.condition !== '14' ) {

		htmlCode += '<span id="condition-and-value-' + rowId + '-' + sourceLevel + '-' + conditionLevel
			+ '" style="display:none;"> and <input type="text" name="condition-and-value" onchange="wppfm_storeCondition('
			+ rowId + ', ' + sourceLevel + ', ' + conditionLevel + ')" id="condition-and-value-input-' + rowId + '-' + sourceLevel + '-' + conditionLevel + '"></span>';
	} else {

		htmlCode += '<span id="condition-and-value-' + rowId + '-' + sourceLevel + '-' + conditionLevel
			+ '" style="display:initial"> and <input type="text" name="condition-and-value" onchange="wppfm_storeCondition('
			+ rowId + ', ' + sourceLevel + ', ' + conditionLevel + ')" id="condition-and-value-input-' + rowId + '-' + sourceLevel + '-' + conditionLevel + '" value="' + queryObject.endValue + '"></span>';
	}

	htmlCode += '(<a class="remove-edit-condition wppfm-btn wppfm-btn-small" href="javascript:void(0)" id="' + rowId;
	htmlCode += '" onclick="wppfm_removeCondition(' + rowId + ', ' + sourceLevel + ', ' + ( conditionLevel - 1 ) + ')">remove</a>)';

	if ( conditionLevel >= numberOfQueries ) {

		htmlCode += '<span id="add-edit-condition-' + rowId + '-' + sourceLevel + '-' + conditionLevel + '" style="display:initial"> (<a class="add-edit-condition wppfm-btn wppfm-btn-small" href="javascript:void(0)';
		htmlCode += '" onclick="wppfm_addCondition(' + rowId + ', ' + sourceLevel + ', ' + conditionLevel + ', \'\')">add</a>)</span>';
	}

	htmlCode += '</div>';

	return htmlCode;
}

function wppfm_orConditionSelector( rowId, inputsObject ) {

	var inputSplit = inputsObject.split( '#' );
	var staticField = '';
	var html = '';

	if ( inputSplit[0] === 'static' ) {

		staticField = wppfm_displayCorrectStaticField( rowId, 1, 0, _feedHolder['channel'], _feedHolder['attributes'][rowId]['fieldName'], inputSplit[1] );
		inputsObject = 'static';
	}

	html += 'or ';
	html += wppfm_alternativeInputFieldCntrl( rowId, inputsObject );
	html += '<span id="alternative-static-input-' + rowId + '">' + staticField + '</span>';
	html += ' for all other products ';
	html += '(<a class="edit-prod-source wppfm-btn wppfm-btn-small" href="javascript:void(0)" id="edit-prod-source-' + rowId + '" onclick="addSource(' + rowId + ', 0, \'\')">';
	html += 'edit</a>)';

	return html;
}

function wppfm_ifValueQuerySelector( rowId, sourceLevel, queryLevel, queryObject, lastValue ) {

	var htmlCode = '';
	var query = wppfm_isEmptyQueryObject( queryObject ) ? wppfm_makeCleanQueryObject() : queryObject;
	var querySelector = 'if ';
	var queryValueConditionChangedFunctionString = 'wppfm_queryValueConditionChanged(' + rowId + ', ' + sourceLevel + ', ' + queryLevel + ', 0)';

	if ( queryLevel > 1 ) {

		querySelector = query.preCondition === '1'
			? '<select id="value-options-sub-query-' + rowId + '-' + sourceLevel + '-' + queryLevel + '" onchange="wppfm_storeValueCondition(' + rowId + ', ' + sourceLevel + ', ' + queryLevel + ')"><option value="2">or</option><option value="1" selected>and</option></select>'
			: '<select id="value-options-sub-query-' + rowId + '-' + sourceLevel + '-' + queryLevel + '" onchange="wppfm_storeValueCondition(' + rowId + ', ' + sourceLevel + ', ' + queryLevel + ')"><option value="2" selected>or</option><option value="1">and</option></select>';
	}

	htmlCode += '<div class="value-options-query-selector" id="value-options-condition-' + rowId + '-' + sourceLevel + '-' + queryLevel + '">';
	htmlCode += querySelector;
	htmlCode += wppfm_conditionFieldCntrl( rowId, sourceLevel, queryLevel, -1, 'value-options-input-field-cntrl', query.source, queryValueConditionChangedFunctionString );
	htmlCode += wppfm_conditionQueryCntrl( rowId, sourceLevel, queryLevel, 0, 'value-query-condition', 'wppfm_queryValueConditionChanged', query.condition );

	if ( queryObject.condition !== '4' && queryObject.condition !== '5' ) {
		htmlCode += '<input type="text" onchange="wppfm_storeValueCondition(' + rowId + ', ' + sourceLevel + ', ' + queryLevel + ')" name="value-options-condition-value" id="value-options-condition-value-';
		htmlCode += rowId + '-' + sourceLevel + '-' + queryLevel + '" value="' + query.value + '" style="display:initial">';
	}

	if ( queryObject.condition !== '14' ) {

		htmlCode += '<span id="value-options-condition-and-value-' + rowId + '-' + sourceLevel + '-' + queryLevel + '" style="display:none;"> and ';
		htmlCode += '<input id="value-options-condition-and-value-input-' + rowId + '-' + sourceLevel + '-' + queryLevel + '" ';
		htmlCode += 'type="text" onchange="wppfm_storeValueCondition(' + rowId + ', ' + sourceLevel + ', ' + queryLevel + ')" name="value-condition-and-value"></span>';
	} else {

		htmlCode += '<span id="value-options-condition-and-value-' + rowId + '-' + sourceLevel + '-' + queryLevel + '" style="display:initial"> and ';
		htmlCode += '<input id="value-options-condition-and-value-input-' + rowId + '-' + sourceLevel + '-' + queryLevel + '" ';
		htmlCode += 'type="text" onchange="wppfm_storeValueCondition(' + rowId + ', ' + sourceLevel + ', ' + queryLevel + ')" name="value-condition-and-value" value="' + queryObject.endValue + '"></span>';
	}

	htmlCode += '(<a class="remove-edit-condition wppfm-btn wppfm-btn-small" href="javascript:void(0)" id="' + rowId;
	htmlCode += '" onclick="wppfm_removeValueQuery(' + rowId + ', ' + sourceLevel + ', ' + queryLevel + ')">remove</a>)';

	if ( lastValue ) {

		htmlCode += '<span id="value-options-add-edit-condition-' + rowId + '-' + sourceLevel + '-' + queryLevel + '" style="display:initial">(<a class="add-edit-condition wppfm-btn wppfm-btn-small" href="javascript:void(0)';
		htmlCode += '" onclick="wppfm_addValueQuery(' + rowId + ', ' + sourceLevel + ', ' + queryLevel + ', \'\')">add</a>)</span>';
	}

	htmlCode += '</div>';

	return htmlCode;
}

function wppfm_addRequiredFieldRow() {
	
	
}

function wppfm_addOptionalFieldRow( level ) {

	var htmlCode = '';
	var addRow = false;

	if ( level === 3 ) {

		addRow = _undefinedRecommendedOutputs.length > 0 ? true : false;
	} else if ( level === 4 ) {

		addRow = _undefinedOptionalOutputs.length > 0 ? true : false;
	} else if ( level === 5 ) {

		//addRow = _undefinedCustomOutputs.length > 0 ? true : false;
		addRow = true;
	}

	if ( addRow === true ) {

		if ( level === 3 ) {
			htmlCode += '<div class="field-table-row-wrapper" id="new-recommended-row" style="display:none;"></div>';
		} else if ( level === 4 ) {
			htmlCode += '<div class="field-table-row-wrapper" id="new-optional-row"></div>';
		} else if ( level === 5 ) {
			htmlCode += '<div class="field-table-row-wrapper" id="new-custom-row"></div>';
		}

		htmlCode += '<div class="field-table-row-top">';
		htmlCode += '<div class="col2w" id="output-select-' + level;

		htmlCode += level !== 5 ? '" onchange="wppfm_changedOutputSelection(' + level + ')">' + wppfm_outputFieldCntrl( level ) :
			'">' + wppfm_customOutputFieldCntrl();

		htmlCode += '</div></div>';
	}

	return htmlCode;
}

/**
 * Is called when the user wants to edit an advised source. Changes the advised output string to a source selector
 * 
 * @param {string} rowId string containing the id of the feed row
 * @param {string} sourceLevel string containing the source counter
 * @returns {nothing}
 */
function wppfm_editOutput( rowId, sourceLevel ) {

	var htmlCode = '';

	htmlCode += wppfm_inputFieldCntrl( rowId, sourceLevel, '', '', _feedHolder['attributes'][rowId]['advisedSource'], '', false );
	htmlCode += '<div class="field-table-row-combined-selection" id="combined-selectors-' + rowId + '" style="display:none;"></div>';

	$jq( '#source-select-' + rowId + '-' + sourceLevel ).html( htmlCode );
}

function wppfm_removeCondition( rowId, sourceLevel, conditionLevel ) {

	// remove the selected query
	_feedHolder.removeValueConditionValue( rowId, sourceLevel, conditionLevel );

	var queries = _feedHolder.getAttributesQueriesObject( rowId, sourceLevel );

	var nrQueries = wppfm_countObjectItems( queries );

	// remove the old condition-wrappers
	for ( var t = 1; t < nrQueries + 2; t++ ) {

		$jq( '#condition-' + rowId + '-' + sourceLevel + '-' + t ).remove();
//        $jq( '#dummy-source-' + rowId + '-' + sourceLevel + '-' + t ).remove();
	}

	// update the form
	if ( nrQueries > 0 ) {

		for ( var i = 0; i < nrQueries; i++ ) {

			wppfm_conditionCode( rowId, sourceLevel, i, queries[i], nrQueries );
		}

		$jq( '#edit-conditions-' + rowId ).append( wppfm_orSelectorCode( rowId, '' ) );
	} else {

		var forAllProductsCode = '<div class="condition-wrapper" id="condition-' + rowId + '-' + sourceLevel + '-0">'
			+ wppfm_forAllProductsCondition( rowId, sourceLevel, 'initial' ) + '</div>';

		//$jq( forAllProductsCode ).insertAfter( '#source-select-' + rowId + '-' + sourceLevel );
		$jq( '#condition-data-' + rowId + '-' + sourceLevel ).html( forAllProductsCode );

		// also remove the for all other products row
		$jq( '#source-' + rowId + '-' + ( sourceLevel + 1 ) ).remove();
	}
}

function wppfm_removeValueQuery( rowId, sourceLevel, queryLevel ) {

	// remove the selected query
	_feedHolder.removeValueQueryValue( rowId, sourceLevel, queryLevel );

	var queries = _feedHolder.getValueQueryValue( rowId, sourceLevel );

	var nrQueries = wppfm_countObjectItems( queries );

	// when the value is fully empty after removing the condition, deactivate the attribute
	if ( !_feedHolder['attributes'][rowId]['value'] ) {

		_feedHolder.deactivateAttribute( rowId );
	}

	// clean the old queries html
	$jq( '#value-editor-queries-' + rowId + '-' + sourceLevel + '-0' ).empty();
	$jq( '#edit-conditions-' + rowId ).empty();

	console.log( nrQueries );

	// remove the condition and update the form
	if ( nrQueries > 0 ) {

		for ( var i = 0; i < nrQueries; i++ ) {

			wppfm_addValueQuery( rowId, sourceLevel, i, queries[i] );
		}

		$jq( '#edit-conditions-' + rowId ).append( wppfm_orSelectorCode( rowId, '' ) );
	} else {

		$jq( '#value-editor-input-query-span-' + rowId + '-' + sourceLevel + '-0' ).show();

		// as there are no queries, the other change value needs to be removed from the meta
		_feedHolder.removeEditValueValue( rowId, ( sourceLevel + 1 ), 0 );

		// also remove the "for all other products" change value line
		$jq( '#edit-value-span-' + rowId + '-' + ( sourceLevel + 1 ) + '-0' ).remove();
	}
}

function wppfm_removeRow( rowId, fieldName ) {

	var level = '0';
	var recInd = _definedRecommendedOutputs.indexOf( fieldName );
	var optInd = _definedOptionalOutputs.indexOf( fieldName );

	// deactivate the attribute
	_feedHolder.deactivateAttribute( rowId );

	// remove the source row from the form
	$jq( '#row-' + rowId ).remove();

	// reset the lists that contain the selected and non selected output fields
	if ( recInd > -1 ) {
		_definedRecommendedOutputs.splice( recInd, 1 );
		level = 3;
		_undefinedRecommendedOutputs.push( fieldName );
		_undefinedRecommendedOutputs.sort();
	}

	if ( optInd > -1 ) {
		_definedOptionalOutputs.splice( optInd, 1 );
		level = 4;
		_undefinedOptionalOutputs.push( fieldName );
		_undefinedOptionalOutputs.sort();
	}

	$jq( "#output-field-cntrl-" + level ).empty();
	$jq( "#output-field-cntrl-" + level ).html( wppfm_outputFieldCntrl( level ) );
}

/**
 * Adds the condition controls to the attribute mapping rows
 * 
 * @param {int} rowId
 * @param {int} sourceLevel
 * @param {int} conditionLevel
 * @param {string} query
 * @returns {nothing}
 */
function wppfm_addCondition( rowId, sourceLevel, conditionLevel, query ) {

	// show the condition controls
	var condition = wppfm_conditionCode( rowId, sourceLevel, conditionLevel, query, 0 );

	if ( condition ) {
		// and if its the first condition level
		if ( conditionLevel === 0 ) {

			// add a "for all other products" row
			$jq( wppfm_addFeedSourceRow( rowId, sourceLevel + 1, _feedHolder.getSourceObject( rowId ) ) ).insertAfter( '#source-' + rowId + '-' + sourceLevel, false );
		}

		// if this input has an advised value, than store this advised value in the meta data
		if ( _feedHolder['attributes'][rowId]['advisedSource'] ) {

			_feedHolder.setSourceValue( rowId, ( sourceLevel + 1 ), _feedHolder['attributes'][rowId]['advisedSource'] );
		}
	}
}

function wppfm_conditionCode( rowId, sourceLevel, conditionLevel, query, nrQueries ) {

	if ( query || wppfm_source_is_filled( rowId, sourceLevel, conditionLevel ) ) {

		// get the data from the selection fields (only used when query is not empty)
		var queryCondition = $jq( '#query-condition-' + rowId + '-' + sourceLevel + '-' + conditionLevel ).val();
		var inputField = $jq( '#input-field-cntrl-' + rowId + '-' + sourceLevel + '-' + conditionLevel ).val();
		var conditionValue = $jq( '#condition-value-' + rowId + '-' + sourceLevel + '-' + conditionLevel ).val();
		var wait = false;

		if ( queryCondition !== "4" && queryCondition !== "5" && queryCondition !== "14" ) {

			if ( inputField === 'select' || conditionValue === '' ) {

				wait = true;
			}
		} else if ( queryCondition === 4 || queryCondition === 5 ) {

			if ( inputField === 'select' ) {

				wait = true;
			}
		}

		if ( wait === false || conditionLevel === 0 ) {

			_feedHolder.incrNrQueries( rowId );

			var queryObject = wppfm_queryStringToQueryObject( query );

			if ( conditionLevel === 0 ) {

				//$jq( '#condition-col-' + id + '-' + sourceLevel ).remove();
				$jq( '#condition-' + rowId + '-' + sourceLevel + '-0' ).remove();
			}

			// replaces the "for all products" with a new empty condition row
//            $jq( '#source-data-' + id + '-' + sourceLevel ).append( wppfm_ifConditionSelector( id, sourceLevel, conditionLevel + 1, nrQueries, queryObject ) );
			$jq( '#condition-data-' + rowId + '-' + sourceLevel ).append( wppfm_ifConditionSelector( rowId, sourceLevel, conditionLevel + 1, nrQueries, queryObject ) );

			if ( conditionLevel > 0 ) {

				// remove the "add" button from the previous condition so only the last condition has an "add" button
				$jq( '#add-edit-condition-' + rowId + '-' + sourceLevel + '-' + conditionLevel ).remove();
			}

			return true;
		} else {

			alert( 'Please fill in the current condition before adding a new one!' );

			return false;
		}
	} else {

		alert( 'Please select a source field first before you select the conditions.' );

		return false;
	}
}

function wppfm_addValueQuery( rowId, sourceLevel, queryLevel, query ) {

	if ( wppfm_source_is_filled( rowId, sourceLevel, queryLevel ) ) {

		// get the data from the selection fields (only used when query is not empty)
		var queryCondition = $jq( '#value-query-condition-' + rowId + '-' + sourceLevel + '-' + queryLevel + '-0' ).val();
		var inputField = $jq( '#value-options-input-field-cntrl-' + rowId + '-' + sourceLevel + '-' + queryLevel ).val();
		var conditionValue = $jq( '#value-options-condition-value-' + rowId + '-' + sourceLevel + '-' + queryLevel ).val();
		var wait = false;

		if ( queryCondition !== "4" && queryCondition !== "5" && queryCondition !== "14" ) {

			if ( inputField === 'select' || conditionValue === '' ) {

				wait = true;
			}
		} else if ( queryCondition === 4 || queryCondition === 5 ) {

			if ( inputField === 'select' ) {

				wait = true;
			}
		}

		if ( wait === false || sourceLevel === 0 ) {

			//_feed.incrNrQueries( id );

			var queryArray = wppfm_queryStringToQueryObject( query );

			$jq( '#value-options-add-edit-condition-' + rowId + '-' + sourceLevel + '-' + queryLevel ).hide(); // hides the add button on this condition level
			$jq( '#value-editor-queries-' + rowId + '-' + sourceLevel + '-0' ).append( wppfm_ifValueQuerySelector( rowId, sourceLevel, queryLevel + 1, queryArray, true ) );
		} else {

			alert( 'Please fill in the current condition before adding a new one!' );
		}
	} else {

		alert( 'Please select a source field first before you select the conditions.' );
	}
}

function wppfm_conditionSelectorCode( id, sourceLevel, level, nrQueries, query ) {

	var queryArray = wppfm_queryStringToQueryObject( query );

	return wppfm_ifConditionSelector( id, sourceLevel, level + 1, nrQueries, queryArray );
}

function wppfm_orSelectorCode( id, alternativeInputs ) {

	var alternative = '';

	if ( alternativeInputs ) {

		for ( var key in alternativeInputs ) {

			alternative = alternativeInputs[key];
		}
	}

	return '<div class="or-selector" id="or-selector-' + id + '">' + wppfm_orConditionSelector( id, alternative ) + '</div>';
}

function wppfm_showEditValueQuery( rowId, sourceLevel, queryLevel, query ) {

	console.log( rowId );
	console.log( sourceLevel );
	console.log( queryLevel );
	console.log( query );

	var emptyQueryObject = { };

	// add a query selector
	$jq( '#value-editor-queries-' + rowId + '-' + sourceLevel + '-' + queryLevel ).html( wppfm_ifValueQuerySelector( rowId, sourceLevel, queryLevel + 1, emptyQueryObject, query ) );

	// hide the 'for all products' text
	$jq( '#value-editor-input-query-span-' + rowId + '-' + sourceLevel + '-0' ).hide();

	if ( queryLevel === 0 && query ) {

		// if it's the first query for this change value, then also add a "for all other products" row
		wppfm_addRowValueEditor( rowId, sourceLevel + 1, 0, '' );
	}
}

/**
 * Gets called when the user selects a source from the source selector
 * 
 * @param {string} rowId
 * @param {string} sourceLevel
 * @param {string} advisedSource
 * @returns {nothing}
 */
function wppfm_changedOutput( rowId, sourceLevel, advisedSource ) {

	var selectedValue = $jq( '#input-field-cntrl-' + rowId + '-' + sourceLevel ).val();
	var combinationLevel = 0;

	switch ( selectedValue ) {

		case 'advised':

			if ( rowId !== 3 ) {
	
				var htmlCode = '';

				if ( advisedSource !== 'Use the settings in the Merchant Center' ) {

					var label = wppfm_sourceOptionsConverter( advisedSource );

					// reset the row to display an advised input row
					htmlCode = '<div class="advised-source">' + label + wppfm_editSourceSelector( rowId, sourceLevel ) + '</div>'; // <span id="static-input-' + id + '"></span>';
				} else {
					
					htmlCode = '<div class="advised-source">Use the settings in the Merchant Center ' + wppfm_editSourceSelector( rowId, sourceLevel ) + '</div>';
				}

				$jq( '#source-select-' + rowId + '-' + sourceLevel ).html( htmlCode );

				// clear the attribute value
				_feedHolder.clearSourceValue( rowId, sourceLevel );
				_feedHolder.deactivateAttribute( rowId );
			} else {

				var htmlCode = '<span id="category-source-string">' + _feedHolder.mainCategory
					+ '</span>' + wppfm_editSourceSelector( rowId, sourceLevel );

				_feedHolder.setCategoryValue( rowId, _feedHolder.mainCategory );
				$jq( '#source-select-' + rowId + '-' + sourceLevel ).html( htmlCode );
			}

			break;

		case 'static':

			$jq( '#source-select-' + rowId + '-' + sourceLevel ).append( wppfm_displayCorrectStaticField( rowId, sourceLevel, 0, _feedHolder['channel'], _feedHolder['attributes'][rowId]['fieldName'], '' ) );
			$jq( '#combined-wrapper-' + rowId + '-' + sourceLevel ).remove();

			var staticValue = $jq( '#static-input-field-' + rowId + '-' + sourceLevel + '-' + combinationLevel ).val() ? $jq( '#static-input-field-' + rowId + '-' + sourceLevel + '-' + combinationLevel ).val() :
				$jq( '#static-condition-input-' + rowId + '-' + sourceLevel + '-' + combinationLevel + ' option:selected' ).text();

			// set the attribute value in the feed on the standard value of the input field for when the user leaves it there
			_feedHolder.setStaticAttributeValue( rowId, sourceLevel, combinationLevel, staticValue );

			if ( staticValue || sourceLevel > 0 ) {
				_feedHolder.activateAttribute( rowId );
			} else {
				_feedHolder.deactivateAttribute( rowId );
			}

			break;

		case 'select':

			// reset the source value
			_feedHolder.clearSourceValue( rowId, sourceLevel );
			if ( sourceLevel === '0' ) _feedHolder.deactivateAttribute( rowId );

			$jq( '#static-input-field-' + rowId + '-' + sourceLevel + '-' + combinationLevel ).remove();
			$jq( '#static-condition-input-' + rowId + '-' + sourceLevel + '-' + combinationLevel ).remove();
			$jq( '#combined-wrapper-' + rowId + '-' + sourceLevel ).remove();

			wppfm_clearStaticField( rowId );

			break;

		case 'combined':

			_feedHolder.setSourceValue( rowId, sourceLevel, selectedValue );
			if ( sourceLevel === '0' ) _feedHolder.deactivateAttribute( rowId );

			$jq( '#static-input-field-' + rowId + '-' + sourceLevel + '-' + combinationLevel ).remove();
			$jq( '#static-condition-input-' + rowId + '-' + sourceLevel + '-' + combinationLevel ).remove();

			wppfm_addCombinedField( rowId, sourceLevel, 0 );

			break;

		case 'category_mapping':

			_feedHolder.setCustomCategory( rowId, _feedHolder.mainCategory );          // Deze optie geeft een foutmelding!!!!!, maar zou wel de goede optie moeten zijn!!!!
//            _feedHolder.setCategoryValue( rowId, _feedHolder.mainCategory );    // nieuwe feed maken, aantal outputs invoeren, een van de outputs aan de category map koppelen
			// feed wordt nu wel gemaakt, maar de value van dit attribute wordt geen "t" waarde, maar blijft een
			// een 'm' met 's' waarde

			$jq( '#static-input-field-' + rowId + '-' + sourceLevel + '-' + combinationLevel ).remove();
			$jq( '#static-condition-input-' + rowId + '-' + sourceLevel + '-' + combinationLevel ).remove();
			$jq( '#combined-wrapper-' + rowId + '-' + sourceLevel ).remove();

			// remove the condition and edit values options
			$jq( '#condition-' + rowId + '-' + sourceLevel + '-0' ).remove();
			$jq( '#value-editor-input-query-add-span-' + rowId + '-' + sourceLevel + '-0' ).hide();

			// display the standard category text message
			$jq( '#source-select-' + rowId + '-' + sourceLevel ).html( wppfm_categorySource() );

			break;

		default:

			$jq( '#static-input-' + rowId ).html( '' );

			if ( selectedValue === advisedSource && sourceLevel === '0' && $jq( '#input-field-cntrl-' + rowId + '-' + sourceLevel + '-0' ).val() === 'select' ) {

				_feedHolder.clearSourceValue( rowId, sourceLevel );
			} else {

				_feedHolder.setSourceValue( rowId, sourceLevel, selectedValue );
			}

			_feedHolder.activateAttribute( rowId );
			_feedHolder['attributes'][rowId]['isActive'] = true;

			$jq( '#static-input-field-' + rowId + '-' + sourceLevel + '-' + combinationLevel ).remove();
			$jq( '#static-condition-input-' + rowId + '-' + sourceLevel + '-' + combinationLevel ).remove();
			$jq( '#combined-wrapper-' + rowId + '-' + sourceLevel ).remove();

			break;
	}
}

function wppfm_changedAlternativeSource( rowId ) {

	var level = 1; // Werkt op dit moment alleen nog maar met één alternatieve bron. Als later "for all products" wordt aangepast, dan ook dit aanpassen

	var selectedValue = $jq( '#alternative-input-field-cntrl-' + rowId ).val();

	if ( selectedValue === 'static' ) {

		var htmlCode = wppfm_displayCorrectStaticField( rowId, _feedHolder['channel'], level, _feedHolder['attributes'][rowId]['fieldName'], '' );

		$jq( '#alternative-static-input-' + rowId ).html( htmlCode );

	} else {

		$jq( '#alternative-static-input-' + rowId ).empty();

		if ( selectedValue !== 'select' && selectedValue !== 'empty' ) {

			_feedHolder.setAlternativeSourceValue( rowId, level, selectedValue );
		} else {

			_feedHolder.removeAlternativeSourceValue( rowId, level );
		}
	}
}

function wppfm_changedCombinationSeparator( rowId, sourveLevel, combinationLevel ) {

	wppfm_changedCombinedOutput( rowId, sourveLevel, combinationLevel );
}

function wppfm_changedCombinedOutput( rowId, sourceLevel, combinationLevel ) {

	var selectedValue = $jq( '#combined-input-field-cntrl-' + rowId + '-' + sourceLevel + '-' + combinationLevel ).val();
	var combinedOutput = '';

	switch ( selectedValue ) {

		case 'static':

			if ( !$jq( '#static-input-field-' + rowId + '-' + sourceLevel + '-' + combinationLevel ).val() ) {

				var htmlCode = wppfm_displayCorrectStaticField( rowId, sourceLevel, combinationLevel, _feedHolder['channel'], _feedHolder['attributes'][rowId]['fieldName'], '' );
				//$jq( '#static-input-combined-field-' + rowId + '-' + sourceLevel + '-' + combinationLevel ).html( htmlCode );
				$jq( '#static-value-control-' + rowId + '-' + sourceLevel + '-' + combinationLevel ).html( htmlCode );
			}

			break;

		default:
			//$jq( '#static-input-combined-field-' + rowId + '-' + sourceLevel + '-' + combinationLevel ).empty();
			$jq( '#static-value-control-' + rowId + '-' + sourceLevel + '-' + combinationLevel ).empty();

			break;
	}

	combinedOutput = getCombinedValue( rowId, sourceLevel );

	if ( combinedOutput ) {
		
		_feedHolder.setCombinedOutputValue( rowId, sourceLevel, combinedOutput );
		_feedHolder.activateAttribute( rowId );
//            _feedHolder['attributes'][rowId]['isActive'] = true;
	} else {

		_feedHolder.removeCombinedOutputValue( rowId, sourceLevel, combinationLevel );
		if ( sourceLevel === '0' ) _feedHolder.deactivateAttribute( rowId ); // only deactivate when when on the first (0) level
	}
}

function wppfm_removeCombinedField( id, sourceLevel, combinedLevel ) {

	_feedHolder.removeCombinedOutputValue( id, sourceLevel, combinedLevel );

	var combinedValueObject = JSON.parse( _feedHolder['attributes'][id]['value'] );
	var combinedValuePart = 'm' in combinedValueObject ? combinedValueObject.m : { };
	var combinedValue = wppfm_getMappingCombinedValue( combinedValuePart, sourceLevel );

	$jq( '#source-select-' + id + '-' + sourceLevel ).append( wppfm_combinedField( id, sourceLevel, combinedLevel, combinedValue, false ) );
}

function wppfm_clearStaticField( id ) {

	$jq( '#static-input-' + id ).html( '' );
}

function wppfm_storeValueCondition( rowId, conditionLevel, queryLevel ) {

	var source = $jq( '#value-options-input-field-cntrl-' + rowId + '-' + conditionLevel + '-' + queryLevel ).val();

	if ( source !== 'select' ) {

		var doStore;

		// get the data required to build the query string
		var subCondition = queryLevel > 1 ? $jq( '#value-options-sub-query-' + rowId + '-' + conditionLevel + '-' + queryLevel + ' option:selected' ).val() : '0';
		var condition = $jq( '#value-query-condition-' + rowId + '-' + conditionLevel + '-' + queryLevel + '-0 option:selected' ).val();
		var value = condition !== '4' && condition !== '5' ? $jq( '#value-options-condition-value-' + rowId + '-' + conditionLevel + '-' + queryLevel ).val() : '';
		var betweenValue = condition === '14' ? $jq( '#value-options-condition-and-value-input-' + rowId + '-' + conditionLevel + '-' + queryLevel ).val() : '';

		if ( condition === '4' || condition === '5' ) {

			doStore = true;
		} else if ( condition === '14' ) {

			doStore = value && betweenValue ? true : false;
		} else {

			doStore = value ? true : false;
		}

		if ( doStore === true ) {

			var subConditionString = subCondition !== '' ? subCondition + '#' : '0#';

			// build the query string
			var query = subConditionString + source + "#" + condition;

			if ( value ) {
				query += "#" + value;
			}

			if ( betweenValue ) {
				query += "#0#" + betweenValue;
			}

			// store the query string in the feed object
			_feedHolder.addValueQueryValue( rowId, conditionLevel, queryLevel, query );
		}
	} else {

		console.log( "Query " + rowId + "-" + conditionLevel + "-" + queryLevel + " removed!" );

		_feedHolder.removeValueQueryValue( rowId, conditionLevel, queryLevel );
	}
}

function wppfm_storeCondition( rowId, sourceLevel, conditionLevel ) {

	var source = $jq( '#input-field-cntrl-' + rowId + '-' + sourceLevel + '-' + conditionLevel ).val();
	var result = false;

	if ( wppfm_validSourceSelected( rowId, sourceLevel ) ) {

		// only store the condition if the user has selected a condition option
		if ( source !== 'select' ) {

			var doStore;

			// get the data from the input fields
			var subCondition = conditionLevel > 1 ? $jq( '#sub-condition-' + rowId + '-' + sourceLevel + '-' + conditionLevel + ' option:selected' ).val() : '0';
			var condition = $jq( '#query-condition-' + rowId + '-' + sourceLevel + '-' + conditionLevel + ' option:selected' ).val();
			var value = condition !== '4' && condition !== '5' ? $jq( '#condition-value-' + rowId + '-' + sourceLevel + '-' + conditionLevel ).val() : '';
			var betweenValue = condition === '14' ? $jq( '#condition-and-value-input-' + rowId + '-' + sourceLevel + '-' + conditionLevel ).val() : '';

			if ( condition === '4' || condition === '5' ) {

				doStore = true;
			} else if ( condition === '14' ) {

				doStore = value && betweenValue ? true : false;
			} else {

				doStore = value ? true : false;
			}

			// only store the data when it is complete
			if ( doStore === true ) {

				var subConditionString = subCondition !== '' ? subCondition + '#' : '0#';

				// build the query string
				var query = subConditionString + source + "#" + condition;

				if ( value ) {
					query += "#" + value;
				}

				if ( betweenValue ) {
					query += "#0#" + betweenValue;
				}

				// store the query string in the feed object
				_feedHolder.addConditionValue( rowId, query, sourceLevel, conditionLevel );

				result = true;
			}
		} else {

			_feedHolder.removeValueConditionValue( rowId, sourceLevel, conditionLevel );
		}
	} else {

		$jq( '#input-field-cntrl-' + rowId + '-' + sourceLevel + '-1' ).prop( 'selectedIndex', 0 );
		$jq( '#condition-value-' + rowId + '-' + sourceLevel + '-1' ).val( '' );

		alert( 'Please select a valid source before adding a condition to that source.' );
	}

	return result;
}

function wppfm_validSourceSelected( rowId, sourceLevel ) {

	var mainSelection = $jq( '#input-field-cntrl-' + rowId + '-' + sourceLevel ).val();

	switch ( mainSelection ) {

		case 'select':
			return false;

		case 'static':

			// TODO: uitzoeken waarom ik de ene keer een static-condition-input en de ander keer een static-input-field als id gebruik
			if ( $jq( '#static-condition-input-' + rowId + '-' + sourceLevel + '-0' ).val() ) {
				return $jq( '#static-condition-input-' + rowId + '-' + sourceLevel + '-0' ).val() ? true : false;
			} else if ( $jq( '#static-input-field-' + rowId + '-' + sourceLevel + '-0' ).val() ) {
				return $jq( '#static-input-field-' + rowId + '-' + sourceLevel + '-0' ).val() ? true : false;
			} else {
				return false;
			}

		case 'combined':
			return $jq( '#combined-input-field-cntrl-' + rowId + '-' + sourceLevel + '-2' ).val() !== 'select' ? true : false;

		default:
			return true;
	}
}

function wppfm_valueInputOptionsChanged( rowId, sourceLevel, valueEditorLevel ) {

	var option = $jq( '#value-options-' + rowId + '-' + sourceLevel + '-' + valueEditorLevel + ' option:selected' ).text();
	var value = $jq( '#value-options-input-' + rowId + '-' + sourceLevel + '-' + valueEditorLevel ).val();
	var store = '';
	//170516 var pre = sourceLevel > 0 ? $jq( '#value-options-pre-selector-' + rowId + '-' + sourceLevel + '-0 option:selected' ).text() : 'change';
	var pre = sourceLevel > 0 ? 'and' : 'change';

	if ( option !== 'replace' && option !== 'recalculate' ) {

		store = pre + '#' + option + '#';
		store += option === 'change nothing' ? 'blank' : value;

		console.log( rowId );
		console.log( sourceLevel );
		console.log( valueEditorLevel );
		console.log( option );
		console.log( value );
		console.log( store );

		_feedHolder.addChangeValue( rowId, sourceLevel, valueEditorLevel, store );
	} else if ( option === 'replace' ) {

		var withValue = $jq( '#value-options-input-with-' + rowId + '-' + sourceLevel + '-' + valueEditorLevel ).val();

		if ( value && withValue ) {

			store = pre + '#' + option + '#' + value + '#' + withValue;
			_feedHolder.addChangeValue( rowId, sourceLevel, valueEditorLevel, store );
		}

	} else if ( option === 'recalculate' ) {

		var calculation = $jq( '#value-options-recalculate-options-' + rowId + '-' + sourceLevel + '-' + valueEditorLevel + ' option:selected' ).text();

		if ( value ) {

			store = pre + '#' + option + '#' + calculation + "#" + value;
			_feedHolder.addChangeValue( rowId, sourceLevel, valueEditorLevel, store );
		}
	}
}

function wppfm_removeValueEditor( rowId, sourceLevel, valueEditorLevel ) {

	_feedHolder.removeEditValueValue( rowId, sourceLevel, valueEditorLevel );

	var values = _feedHolder.getAttributesValueObject( rowId );
	var nrValues = wppfm_countObjectItems( values );

	// when the value is fully empty after removing the condition, deactivate the attribute
	if ( !_feedHolder['attributes'][rowId]['value'] ) {

		_feedHolder.deactivateAttribute( rowId );
	}

	$jq( '#edit-value-span-' + rowId + '-' + sourceLevel + '-0' ).remove();

	if ( nrValues > 0 ) {

		for ( var i = 1; i < nrValues; i++ ) {

			wppfm_addRowValueEditor( rowId, sourceLevel, i, values[i] );
		}
	} else {

		$jq( '#value-editor-' + rowId + '-' + sourceLevel + '-' + valueEditorLevel ).remove();
		$jq( '#source-' + rowId + '-' + sourceLevel ).append( wppfm_editValueSpan( rowId, sourceLevel, valueEditorLevel, 'initial' ) );
	}
}

function wppfm_makeFieldsTable() {

	var channel = _feedHolder.channel.toString(); // TODO: Channel is not always a string

	// reset the fields
	wppfm_resetFields();

	for ( var i = 0; i < _feedHolder['attributes'].length; i++ ) {

		switch ( _feedHolder['attributes'][i]['fieldLevel'] ) {

			case '1':
				_mandatoryFields += wppfm_fieldRow( _feedHolder['attributes'][i], false );
				break;

			case '2':
				_highlyRecommendedFields += wppfm_fieldRow( _feedHolder['attributes'][i], false );
				break;

			case '3':
				if ( _feedHolder['attributes'][i]['isActive'] ) {
					_recommendedFields += wppfm_fieldRow( _feedHolder['attributes'][i], true );
					_definedRecommendedOutputs.push( _feedHolder['attributes'][i]['fieldName'] );
				} else {
					_undefinedRecommendedOutputs.push( _feedHolder['attributes'][i]['fieldName'] );
				}
				break;

			case '4':
				if ( _feedHolder['attributes'][i]['isActive'] ) {
					_optionalFields += wppfm_fieldRow( _feedHolder['attributes'][i], true );
					_definedOptionalOutputs.push( _feedHolder['attributes'][i]['fieldName'] );
				} else {
					_undefinedOptionalOutputs.push( _feedHolder['attributes'][i]['fieldName'] );
				}
				break;

			case '5':
				if ( _feedHolder['attributes'][i]['isActive'] ) {
					_customFields += wppfm_fieldRow( _feedHolder['attributes'][i], true );
				} else {
					_undefinedCustomOutputs.push( _feedHolder['attributes'][i]['fieldName'] );
				}
				break;

			default:
				break;
		}
	}

	_definedRecommendedOutputs.sort();
	_undefinedRecommendedOutputs.sort();

	_recommendedFields += wppfm_addOptionalFieldRow( 3 );
	_optionalFields += wppfm_addOptionalFieldRow( 4 );

	if ( wppfm_isCustomChannel( channel ) ) { // this means the user has selected a free format feed
		_customFields += wppfm_addOptionalFieldRow( 5 );
	}

	if ( _mandatoryFields.length > 0 ) {
		$jq( '#required-field-table' ).html( _mandatoryFields );
		$jq( '#required-fields' ).show();
	} else {
		$jq( '#required-fields' ).hide();
	}

	if ( _highlyRecommendedFields.length > 0 ) {
		$jq( '#highly-recommended-field-table' ).html( _highlyRecommendedFields );
		$jq( '#highly-recommended-fields' ).show();
	} else {
		$jq( '#highly-recommended-fields' ).hide();
	}

	if ( _recommendedFields.length > 0 ) {
		$jq( '#recommended-field-table' ).html( _recommendedFields );
		$jq( '#recommended-fields' ).show();
	} else {
		$jq( '#recommended-fields' ).hide();
	}

	if ( _optionalFields.length > 0 ) {
		$jq( '#optional-field-table' ).html( _optionalFields );
		$jq( '#optional-fields' ).show();
	} else {
		$jq( '#optional-fields' ).hide();
	}

	if ( _customFields.length > 0 ) {
		$jq( '#custom-field-table' ).html( _customFields );
		$jq( '#custom-fields' ).show();
	} else {
		$jq( '#custom-fields' ).hide();
	}

	$jq( '#fields-form' ).show( 300 );
}

function wppfm_resetFields() {

	_mandatoryFields = [ ];
	_highlyRecommendedFields = [ ];
	_recommendedFields = [ ];
	_undefinedRecommendedOutputs = [ ];
	_optionalFields = [ ];
	_undefinedOptionalOutputs = [ ];
	_customFields = [ ];
	_undefinedCustomOutputs = [ ];
}

function wppfm_queryConditionChanged( id, sourceLevel, conditionLevel ) {

	// TODO: wppfm_queryConditionChanged en wppfm_queryValueConditionChanged en andere functies die aan de conditions zijn
	// gerelateerd kunnen volgens mij samengevoegd worden door in de query-condition niveaus toch een unieke subQuery
	// nummer toe te voegen (bijvoorbeeld een lettercombinatie) waardoor ze van de value-query-condition niveaus
	// kunnen worden gescheiden. Daarna is het combineren van beide condition opties mogelijk.

	// get the selected query option
	var value = $jq( '#query-condition-' + id + '-' + sourceLevel + '-' + conditionLevel ).val();

	// if the "is empty" or "is not empty" condition is selected
	if ( value === "4" || value === "5" ) {

		$jq( '#condition-value-' + id + '-' + sourceLevel + '-' + conditionLevel ).hide();
	} else {

		$jq( '#condition-value-' + id + '-' + sourceLevel + '-' + conditionLevel ).show();
	}

	// if the "is between" condition is selected
	if ( value === "14" ) {

		$jq( '#condition-value-' + id + '-' + sourceLevel + '-' + conditionLevel ).show();
		$jq( '#condition-and-value-' + id + '-' + sourceLevel + '-' + conditionLevel ).show();
	} else {

		$jq( '#condition-and-value-' + id + '-' + sourceLevel + '-' + conditionLevel ).hide();
	}

	var conditionStored = wppfm_storeCondition( id, sourceLevel, conditionLevel );

	if ( conditionStored && !$jq( '#source-' + id + '-' + ( sourceLevel + 1 ) ).length ) {

		// add a new source row to the fieldRow
		$jq( wppfm_addFeedSourceRow( id, sourceLevel + 1, _feedHolder.getSourceObject( id ) ) ).insertAfter( '#source-' + id + '-' + sourceLevel, false );
	}
}

function wppfm_queryValueConditionChanged( rowId, sourceLevel, queryLevel, querySublevel ) {

	var value = $jq( '#value-query-condition-' + rowId + '-' + sourceLevel + '-' + queryLevel + '-' + querySublevel ).val();

	if ( value === "4" || value === "5" ) {

		$jq( '#value-options-condition-value-' + rowId + '-' + sourceLevel + '-' + queryLevel ).hide();
	} else {

		$jq( '#value-options-condition-value-' + rowId + '-' + sourceLevel + '-' + queryLevel ).show();
	}

	if ( value === "14" ) {

		$jq( '#value-options-condition-value-' + rowId + '-' + sourceLevel + '-' + queryLevel ).show();
		$jq( '#value-options-condition-and-value-' + rowId + '-' + sourceLevel + '-' + queryLevel ).show();
	} else {

		$jq( '#value-options-condition-and-value-' + rowId + '-' + sourceLevel + '-' + queryLevel ).hide();
	}

	wppfm_storeValueCondition( rowId, sourceLevel, queryLevel );
}

function wppfm_decrease_channel_updates_counter() {
	
	var oldValue = $jq( '.update-count' ).text();
	var newValue = oldValue > 1 ? oldValue - 1 : '';
	$jq( '.update-count' ).html( newValue );
}

function wppfm_getOutputFieldsList( level ) {

	var htmlCode = '';
	var list = [ ];

	switch ( level ) {

		case 3:
			list = _undefinedRecommendedOutputs;
			break;

		case 4:
			list = _undefinedOptionalOutputs;
			break;

		default:
			break;
	}

	for ( var i = 0; i < list.length; i++ ) {

		htmlCode += '<option value="' + list[i] + '">' + list[i] + '</option>';
	}

	return htmlCode;
}

function wppfm_fixedSourcesList( selectedValue ) {

	var htmlCode = '';
	var selectStatus = '';

	for ( var i = 0; i < _inputFields.length; i++ ) {

		selectStatus = selectedValue === _inputFields[i].value ? ' selected' : '';

		htmlCode += '<option value = "' + _inputFields[i].value + '" itemprop="' + _inputFields[i].prop + '" ' + selectStatus + '>' + _inputFields[i].label + '</option>';
	}

	return htmlCode;
}

function wppfm_hideFeedFormMainInputs() {

	//$jq( '#country-list-row' ).hide();
	$jq( '#category-list-row' ).hide();
	$jq( '#aggregator-selector-row' ).hide();
	$jq( '#add-product-variations-row' ).hide();
}

function wppfm_editFeedFilter( ) {
	alert( "The Advanced Filter option is not available in the free version. Unlock the Advanced Filter option by upgrading to the Premium plugin. For more information goto http://www.wpmarketingrobot.com/." );
}

function wppfm_makeFeedFilterWrapper( feedId, filter ) {
	var	htmlCode = 'All products from the selected Shop Categories will be included in the feed';

	htmlCode += '<span id="filter-edit-text" style="display:initial;"> (<a class="edit-feed-filter wppfm-btn wppfm-btn-small" href="javascript:void(0)" id="edit-feed-filters-' + feedId;
	htmlCode += '" onclick="wppfm_editFeedFilter()">edit</a>)</span>';
	
	$jq( '.product-filter-condition-wrapper' ).html( htmlCode );
	$jq( '.main-product-filter-wrapper' ).show();
}

function wppfm_getCombinedSeparatorList( selectedValue ) {

	// ALERT These options have to be the same as in the make_combined_result_string() array
	var separatorOptions = { "0": "-- No separator --", "1": "space", "2": "comma", "3": "point", "4": "semicolon", "5": "colon", "6": "dash", "7": "slash", "8": "backslash" };

	var htmlCode = '';

	for ( var field in separatorOptions ) {

		if ( field !== selectedValue ) {

			htmlCode += '<option value="' + field + '">' + separatorOptions[field] + '</option>';
		} else {

			htmlCode += '<option value="' + field + '" selected>' + separatorOptions[field] + '</option>';

		}
	}

	return htmlCode;
}

function updateFeedFormAfterInputChanged( feedId, categoryChanged ) {
	
	// enable the Generate and Save button
	wppfm_enableFeedActionButtons();
	wppfm_finishOrUpdateFeedPage( categoryChanged );

	// make a new feed object if it has not been already
	if ( feedId === undefined || feedId < 1 ) { wppfm_constructNewFeed(); }
}

/**
 * hook the document actions
 */
$jq( document ).ready( function () {
	var feedId = wppfm_getUrlVariable( "id" );
	_feedHolder = new Feed();
	if ( feedId !== "" ) { wppfm_editExistingFeed( feedId ); }

	// set up the event listeners
	wppfm_listen();
} );
