/*!
 * channel-functions.js v1.4
 * Part of the WP Product Feed Manager
 * Copyright 2016, Michel Jongbloed
 *
 */

"use strict";


var $jq = jQuery.noConflict();

/**
 * shows the correct input fields on the feed form depending on the choosen channel
 * 
 * @param {string} channel
 * @param {bool} isNew
 * @returns {nothing}
 */
// WPPFM_CHANNEL_RELATED
function wppfm_showChannelInputs( channel, isNew ) {
  
	var fName = {
		'1':'switchToGoogleFeedFormMainInputs',
		'2':'switchToBingFeedFormMainInputs',
		'3':'switchToBeslisFeedFormMainInputs',
		'4':'switchToPricegrabberFeedFormMainInputs',
		'5':'switchToShoppingFeedFormMainInputs',
		'6':'switchToAmazonFeedFormMainInputs',
		'7':'switchToConnexityFeedFormMainInputs',
		'9':'switchToNextagFeedFormMainInputs',
		'10':'switchToKieskeurigFeedFormMainInputs',
		'11':'switchToVergelijkFeedFormMainInputs',
		'12':'switchToKoopjespakkerFeedFormMainInputs',
		'13':'switchToAvantLinkFeedFormMainInputs',
		'14':'switchToZboziFeedFormMainInputs',
		'15':'switchToComconFeedFormMainInputs',
		'16':'switchToFacebookFeedFormMainInputs',
		'17':'switchToBolFeedFormMainInputs',
		'18':'switchToAdtractionFeedFormMainInputs',
		'998':'switchToMarketingrobotCsvFeedFormMainInputs',
		'999':'switchToMarketingrobotFeedFormMainInputs'
	};

	// call the correct function
	if ( fName.hasOwnProperty( channel ) ) {
		window[fName[channel]](isNew, channel);
	}

	// standard for all channels
    $jq( '#update-schedule-row' ).show();
	$jq( '#add-product-variations-row' ).show();
	
	if ( ( $jq( '#lvl_0' ).val() === null && $jq( '#selected-categories' ).html() === '' ) || $jq( '#countries' ).val() === 0 ) {
		wppfm_show_or_hide_category_map( channel );
	} else {
	    $jq( '#category-map' ).show();
	}
}

/**
 * depending on channel show or hide the category map
 * 
 * @param {string} channel
 * @returns nothing
 */
function wppfm_show_or_hide_category_map( channel ) {
	
	switch( channel ) {
		
		case '15': // Commerce Connector
		case '17': // Bol.com
		case '18': // Adtraction
			$jq( '#category-map' ).show();
			break;
			
		default:
			$jq( '#category-map' ).hide();
			break;
	}	
}

/**
 * calls the correct channel function after the user has changed a main input field on the feed form
 * 
 * @param {string} channel
 * @param {string} feedId
 * @param {bool} categoryChanged
 * @returns {nothing}
 */
function wppfm_reactOnChannelInputChanged( channel, feedId, categoryChanged ) {

	var fName = {
		'1':'googleInputChanged',
		'2':'bingInputChanged',
		'3':'beslisInputChanged',
		'4':'pricegrabberInputChanged',
		'5':'shoppingInputChanged',
		'6':'amazonInputChanged',
		'7':'connexityInputChanged',
		'9':'nextagInputChanged',
		'10':'kieskeurigInputChanged',
		'11':'vergelijkInputChanged',
		'12':'koopjespakkerInputChanged',
		'13':'avantlinkInputChanged',
		'14':'zboziInputChanged',
		'15':'comconInputChanged',
		'16':'facebookInputChanged',
		'17':'bolInputChanged',
		'18':'adtractionInputChanged',
		'998':'marketingrobotCsvInputChanged',
		'999':'marketingrobotInputChanged'
	};	

	// call the correct function
	if ( fName.hasOwnProperty( channel ) ) {
		window[fName[channel]](feedId, categoryChanged);
	}
}

/**
 * Returns txt of xml depending on the feed type that needs to be made
 * 
 * @param {string} channel
 * @returns {string}
 */
// WPPFM_CHANNEL_RELATED
function wppfm_getChannelFeedType( channel ) {
    
    switch( channel ) {
            
        case '2': // bing
        case '4': // pricegrabber
        case '6': // amazon
        case '7': // connexity
        case '9': // nextag
        case '12': // koopjespakker.nl
            return 'txt';
            break;
			
		case '15': // Commerce Connector
		case '17': // Bol.com
		case '998': // Custom CSV Feed.com
			return 'csv'; 
            
        default:
            return 'xml';
            break;
    }
}

/**
 * returns the correct country code for the channel specific category text file
 * 
 * @param {string} channel
 * @returns {String}
 */
function wppfm_channelCountryCode( channel ) {

	var language = 'en-US';

	// WPPFM_CHANNEL_RELATED
	switch ( channel ) {

		case '3': // Beslist
		case '10': // Kieskeurig
		case '11': // Vergelijk
		case '12': // Koopjespakker
		case '17': // Bol.com
			language = 'nl-NL';
			break;
			
		case '14': // Zbozi
			language = 'cs-CZ';
			break;
	}
	
	return language;
}

/**
 * Returns true if the specified channel does not have its own categories but uses the users shop
 * categories instead
 * 
 * @param {string} channel
 * @returns {Boolean} true when this channel uses categories from the shop
 */
// WPPFM_CHANNEL_RELATED
function wppfm_channelUsesOwnCategories( channel ) {

    // only add the channel when it uses the shop categories in stead of specific channel categories
    switch ( channel ) {
        
        case '10': // kieskeurig.nl
		case '15': // Commerce Connector
		case '17': // Bol.com
		case '18': // Adtraction
            return true;
            
        default:
            return false;
    }
}

/**
 * If required for that channel, this function activates the correct function that will prepare the global category 
 * variables in the channel specific javascript file. Does nothing when not required for the channel
 * 
 * @param {string} channel
 * @param {string} selectedCategory
 * @param {string} currentLevelId
 * @returns {nothing}
 */
function wppfm_fillCategoryVariables( channel, selectedCategory, currentLevelId ) {

	var fName = {
		'1':'fillGoogleCategoryVariables',
		'4':'fillPricegrabberCategoryVariables',
		'5':'fillShoppingCategoryVariables',
		'6':'fillAmazonCategoryVariables',
		'7':'fillConnexityCategoryVariables',
		'9':'fillNextagCategoryVariables',
		'13':'fillAvantLinkCategoryVariables',
		'14':'fillZboziCategoryVariables'
	};

	// call the correct function
	if ( fName.hasOwnProperty( channel ) ) {
		// call the correct switch  main form inputs function
		window[fName[channel]](selectedCategory, currentLevelId);
	}
}

/**
 * Some fields require specific allowed inputs. This function gets the correct options for given field
 * 
 * @param {string} id
 * @param {string} level
 * $param (string) combinationLevel
 * @param {string} channel
 * @param {string} fieldName
 * @param {string} selected
 * @returns {String} containing the allowed options
 */
function wppfm_displayCorrectStaticField( id, level, combinationLevel, channel, fieldName, selected ) {

	var html = '';
	var options = wppfm_restrictedStaticFields( channel, fieldName );

	if ( options !== undefined ) {

		if ( options.length === 0 ) {

			// show the standard text type input field
			html = wppfm_staticInputField( id, level, combinationLevel, selected );
		} else {

			// show the standard selector with the correct allowed options
			html = wppfm_staticInputSelect( id, level, combinationLevel, options, selected );
		}
	}

	return html;
}

/**
 * Gets the advised input fields
 * 
 * @param {string} channel
 * @param {string} source Currently not in use
 * @returns {array} array containing the advised inputs
 */
function wppfm_getAdvisedInputs( channel ) {

	var fName = {
		'1':'woocommerceToGoogleFields',
		'2':'woocommerceToBingFields',
		'3':'woocommerceToBeslisFields',
		'4':'woocommerceToPricegrabberFields',
		'5':'woocommerceToShoppingFields',
		'6':'woocommerceToAmazonFields',
		'7':'woocommerceToConnexityFields',
		'9':'woocommerceToNextagFields',
		'10':'woocommerceToKieskeurigFields',
		'11':'woocommerceToVergelijkFields',
		'12':'woocommerceToKoopjespakkerFields',
		'13':'woocommerceToAvantLinkFields',
		'14':'woocommerceToZboziFields',
		'15':'woocommerceToComconFields',
		'16':'woocommerceToFacebookFields',
		'17':'woocommerceToBolFields',
		'18':'woocommerceToAdtractionFields'
	};

	if ( fName.hasOwnProperty( channel ) ) {
		// call the correct function
		return window[fName[channel]]();
	} else {
		
		return new Array();
	}
}

/**
 * Sets the attributes to the correct levels depending on several variables
 * 
 * @param {int} channel
 * @param {object} feedHolder
 * @param {string} country
 * @returns {object} feed holder with the correct attribute levels
 */
// ALERT has a relation with the set_output_attribute_levels() function in the class-data.php file
function wppfm_setOutputAttributeLevels( channel, feedHolder, country ) {

//	var fName = {
//		'1':'setGoogleOutputAttributeLevels',
//		'2':'setBingOutputAttributeLevels',
//		'3':'setBeslisOutputAttributeLevels',
//		'4':'setPricegrabberOutputAttributeLevels',
//		'5':'setShoppingOutputAttributeLevels',
//		'6':'setAmazonOutputAttributeLevels',
//		'7':'setConnexityOutputAttributeLevels',
//		'9':'setNextagOutputAttributeLevels',
//		'10':'setKieskeurigOutputAttributeLevels',
//		'11':'setVergelijkOutputAttributeLevels',
//		'13':'setAvantLinkOutputAttributeLevels',
//		'14':'setZboziOutputAttributeLevels',
//		'999':'setMarketingrobotOutputAttributeLevels'
//	};
//
//	if ( fName.hasOwnProperty( channel ) ) {
//		// call the correct switch  main form inputs function
//		return window[fName[channel]]();
//	} else {
//		
//		return feedHolder;
//	}

	switch ( channel ) {

		case '1':
			return setGoogleOutputAttributeLevels( feedHolder, country );
			break;

		case '2':
			return setBingOutputAttributeLevels( feedHolder );
			break;

		case '3':
			return setBeslisOutputAttributeLevels( feedHolder );
			break;

		case '4':
			return setPricegrabberOutputAttributeLevels( feedHolder );
			break;

		case '5':
			return setShoppingOutputAttributeLevels( feedHolder );
			break;

		case '6':
			return setAmazonOutputAttributeLevels( feedHolder );
			break;

		case '7':
			return setConnexityOutputAttributeLevels( feedHolder );
			break;

		case '9':
			return setNextagOutputAttributeLevels( feedHolder );
			break;

		case '10':
			return setKieskeurigOutputAttributeLevels( feedHolder );
			break;

		case '11':
			return setVergelijkOutputAttributeLevels( feedHolder );
			break;

		case '13':
			return setAvantLinkOutputAttributeLevels( feedHolder, country );
			break;

		case '14':
			return setZboziOutputAttributeLevels( feedHolder, country );
			break;

		case '998':
			return setMarketingrobotCsvOutputAttributeLevels( feedHolder );
			break;

		case '999':
			return setMarketingrobotOutputAttributeLevels( feedHolder );
			break;

		default:
			return feedHolder;
			break;
	}
}

/**
 * returns an array with the channel specific fields with restricted input options
 * 
 * @param {string} channel
 * @param {string} fieldName
 * @returns {Array}
 */
function wppfm_restrictedStaticFields( channel, fieldName ) {

	var fName = {
		'1':'googleStaticFieldOptions',
		'2':'bingStaticFieldOptions',
		'3':'beslisStaticFieldOptions',
		'4':'pricegrabberStaticFieldOptions',
		'5':'shoppingStaticFieldOptions',
		'6':'amazonStaticFieldOptions',
		'7':'connexityStaticFieldOptions',
		'9':'nextagStaticFieldOptions',
		'10':'kieskeurigStaticFieldOptions',
		'11':'vergelijkStaticFieldOptions',
		'12':'koopjespakkerStaticFieldOptions',
		'13':'avantlinkStaticFieldOptions',
		'14':'zboziStaticFieldOptions',
		'15':'comconStaticFieldOptions',
		'16':'facebookStaticFieldOptions',
		'17':'bolStaticFieldOptions',
		'18':'adtractionStaticFieldOptions'
	};

	if ( fName.hasOwnProperty( channel ) ) {
		// call the correct function
		return window[fName[channel]](fieldName);
	} else {
		
		return new Array();
	}
}

/**
 * set a preset condition, other than the advised input, for fields for a specific channel (eg. condition = static field with 'new' selected
 * 
 * @param {array} outputsField
 * @param {string} channel
 * @returns {array}
 */
function wppfm_setChannelRelatedPresets( outputsField, channel ) {

	// WPPFM_CHANNEL_RELATED
	switch ( channel ) {

		case '1': // Google

			if ( outputsField['field_label'] === 'condition' || outputsField['field_label'] === 'availability'
				|| outputsField['field_label'] === 'identifier_exists' || outputsField['field_label'] === 'adult'
				|| outputsField['field_label'] === 'price' ) {

				// only switch to the 'preset' value if no user value is set
				if ( !outputsField['value'] ) {

					outputsField['value'] = setGooglePresets( outputsField['field_label'] );
				}
			}
			break;

		case '2': // Bing

			if ( outputsField['field_label'] === 'seller_name' ) {

				// only switch to the 'preset' value if no user value is set
				if ( !outputsField['value'] ) {

					outputsField['value'] = setBingPresets( outputsField['field_label'] );
				}
			}
			break;

		case '3': // Beslist

			if ( outputsField['field_label'] === 'Conditie' || outputsField['field_label'] === 'Levertijd' ) {

				// only switch to the 'preset' value if no user value is set
				if ( !outputsField['value'] ) {

					outputsField['value'] = setBeslisPresets( outputsField['field_label'] );
				}
			}
			break;

		case '13': // Avant Link

			if ( outputsField['field_label'] === 'condition' || outputsField['field_label'] === 'availability'
				|| outputsField['field_label'] === 'identifier_exists' ) {

				// only switch to the 'preset' value if no user value is set
				if ( !outputsField['value'] ) {

					outputsField['value'] = setAvantLinkPresets( outputsField['field_label'] );
				}
			}
			break;

		case '14': // Zbozi

			if ( outputsField['field_label'] === 'EROTIC' || outputsField['field_label'] === 'VISIBILITY' ) {

				// only switch to the 'preset' value if no user value is set
				if ( !outputsField['value'] ) {

					outputsField['value'] = setZboziPresets( outputsField['field_label'] );
				}
			}
			break;

		case '15': // Commerce Connector

			if ( outputsField['field_label'] === 'Delivery time' ) {

				// only switch to the 'preset' value if no user value is set
				if ( !outputsField['value'] ) {

					outputsField['value'] = setComconPresets( outputsField['field_label'] );
				}
			}
			break;

		case '16': // Facebook

			if ( outputsField['field_label'] === 'condition' || outputsField['field_label'] === 'availability'
				|| outputsField['field_label'] === 'price' ) {

				// only switch to the 'preset' value if no user value is set
				if ( !outputsField['value'] ) {

					outputsField['value'] = setFacebookPresets( outputsField['field_label'] );
				}
			}
			break;

		case '17': // Bol.com

			if ( outputsField['field_label'] === 'Condition' || outputsField['field_label'] === 'Deliverycode' ) {

				// only switch to the 'preset' value if no user value is set
				if ( !outputsField['value'] ) {

					outputsField['value'] = setBolPresets( outputsField['field_label'] );
				}
			}
			break;

		case '18': // Adtraction

			if ( outputsField['field_label'] === 'instock' ) {

				// only switch to the 'preset' value if no user value is set
				if ( !outputsField['value'] ) {

					outputsField['value'] = setAdtractionPresets( outputsField['field_label'] );
				}
			}
			break;

		default:
			break;
	}
}

/**
 * returns if a channel is a custom feed channel
 * 
 * @param {string} channel
 * @returns {Boolean}
 */
function wppfm_isCustomChannel( channel ) {
	
	switch( channel ) {
		
		case '998': // Custom CSV Feed
		case '999': // Custom XML Feed
			return true;
			
		default:
			return false;
	}
}

// ALERT! has a php equivalent in class-feed-master.php called set_attribute_status();
function setAttributeStatus( fieldLevel, fieldValue ) {

    if ( fieldLevel > 0 && fieldLevel < 3 ) { return true; }
    
    if ( fieldValue ) { return true; }
    
    return false;
}