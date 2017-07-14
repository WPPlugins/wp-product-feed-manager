/*!
 * google-source.js v4.1
 * Part of the WP Product Feed Manager
 * Copyright 2017, Michel Jongbloed
 *
 */

"use strict";

var $jq = jQuery.noConflict();
var _googleClothingAndAccessories = false;
var _googleNeedsProductCat = false;
var _googleRequiresBrand = true;

// ALERT! This function is equivalent for the woocommerce_to_feed_fields() function in class-data.php
function woocommerceToGoogleFields() {
    var fields = {
        'id': '_sku',
        'title': 'post_title',
        'google_product_category': 'category',
        'description': 'post_content',
        'link': 'permalink',
        'image_link': 'attachment_url',
        'additional_image_link': '_wp_attachement_metadata',
        'price': '_regular_price',
        'sale_price': '_sale_price',
        'sale_price_effective_date': '_sale_price_dates_from',
		'item_group_id': 'item_group_id',
        
        // In accordance with the Google Feed Specifications update of september 2015
        'tax': 'Use the settings in the Merchant Center',
        'shipping': 'Use the settings in the Merchant Center'
    };

    return fields;
}

// ALERT! This function is equivalent for the set_google_output_attribute_levels() function in class-data.php
function setGoogleOutputAttributeLevels( feedHolder, targetCountry ) {
    for ( var i = 0; i < feedHolder['attributes'].length; i++ ) {

        if ( feedHolder['attributes'][i]['fieldLevel'] === '0' ) {

            switch ( feedHolder['attributes'][i]['fieldName'] ) {

				case 'google_product_category':

					if ( _googleNeedsProductCat === true ) {
                        feedHolder['attributes'][i]['fieldLevel'] = '1';
                    }
                    else {
                        feedHolder['attributes'][i]['fieldLevel'] = '4';
                    }
					
					break;
				                
                case 'is_bundle':
				case 'multipack':

                    if ( $jq.inArray( targetCountry, googleSpecialProductCountries() ) < 0 ) {
                        feedHolder['attributes'][i]['fieldLevel'] = '4';
                    }
                    else {
                        feedHolder['attributes'][i]['fieldLevel'] = '1';
                    }

                    break;

                case 'brand':

                    if ( _googleRequiresBrand === true ) {
                        feedHolder['attributes'][i]['fieldLevel'] = '1';
                    }
                    else {
                        feedHolder['attributes'][i]['fieldLevel'] = '4';
                    }

                    break;
				
                case 'item_group_id':

					if ( $jq.inArray( targetCountry, googleSpecialClothingGroupCountries() ) > -1 ) {
						feedHolder['attributes'][i]['fieldLevel'] = '1';
					}
					else {
						feedHolder['attributes'][i]['fieldLevel'] = '4';
					}

                    break;

                case 'gender':
                case 'age_group':
                case 'color':
                case 'size':

                    if ( $jq.inArray( targetCountry, googleSpecialClothingGroupCountries() ) > -1 && _googleClothingAndAccessories === true ) {
                        feedHolder['attributes'][i]['fieldLevel'] = '1';
                    }
                    else {
                        feedHolder['attributes'][i]['fieldLevel'] = "4";
                    }

                    break;

                case 'tax':
                
                    // In accordance with the Google Feed Specifications update of september 2015
                    if ( targetCountry === 'US' ) {
                        
                        feedHolder['attributes'][i]['fieldLevel'] = '1';
                    } else {
                        
                        feedHolder['attributes'][i]['fieldLevel'] = '4';
                    }
                    
                    break;
                    
                case 'shipping':
                
                    // In accordance with the Google Feed Specifications update of september 2015
                    if ( $jq.inArray( targetCountry, googleSpecialShippingCountries() ) > -1 ) {
                        
                        feedHolder['attributes'][i]['fieldLevel'] = '1';
                    } else {
                        
                        feedHolder['attributes'][i]['fieldLevel'] = '4';
                    }
                    
                    break;

                default:
                    break;

            }

            // set the attribute to active if it's a recommended or highly recommended attribute, or if it has a value
            feedHolder['attributes'][i]['isActive'] = setAttributeStatus( parseInt( feedHolder['attributes'][i]['fieldLevel'] ), feedHolder['attributes'][i]['value'] );
        }
    }

    return feedHolder;
}

function setGooglePresets( field ) {
    switch ( field ) {
        
        case 'condition':
            return '{"m":[{"s":{"static":"new"}}]}';
            break;
            
        case 'availability':
            return '{"m":[{"s":{"static":"in stock"},"c":[{"1":"0#_stock_status#0#instock"}]},{"s":{"static":"out of stock"}}]}';
            break;
            
        case 'identifier_exists':
            return '{"m":[{"s":{"static":"yes"}}]}';
            break;
            
        case 'adult':
            return '{"m":[{"s":{"static":"no"}}]}';
            break;
			
		case 'price':
			return '{"m":[{"s":{"source":"combined","f":"_regular_price|1#wc_currency"}}]}';
			break;
            
        default:
            break;
    }
}

function fillGoogleCategoryVariables( selectedCategory, currentLevel ) {
    switch ( currentLevel ) {
        case 'lvl_0':
        case 'lvl_1':
            _googleClothingAndAccessories = false;
            _googleNeedsProductCat = false;
			_googleRequiresBrand = true;
            break;
    }

    switch ( selectedCategory ) {
        case 'Clothing':
            _googleClothingAndAccessories = true;
            _googleNeedsProductCat = true;
			_googleRequiresBrand = true;
            break;

        case 'Software':
        case 'Apparel & Accessories':
            _googleClothingAndAccessories = true;
            _googleNeedsProductCat = true;
			_googleRequiresBrand = true;
            break;
			
		case 'Media':
            _googleClothingAndAccessories = false;
            _googleNeedsProductCat = true;
			_googleRequiresBrand = false;
			break;

        default:
            break;
    }
}

function googleStaticFieldOptions( fieldName ) {
    switch ( fieldName ) {
        case 'condition':
            var options = new Array( 'new', 'used', 'refurbished' );
            break;

        case 'availability':
            var options = new Array( 'in stock', 'out of stock', 'preorder' );
            break;

        case 'identifier_exists':
            var options = new Array( 'yes', 'no' );
            break;

        case 'gender':
            var options = new Array( 'unisex', 'male', 'female' );
            break;

        case 'age_group':
            var options = new Array( 'adult', 'newborn', 'infant', 'toddler', 'kids' );
            break;

        case 'size_type':
            var options = new Array( 'regular', 'petite', 'plus', 'big and tall', 'maternity' );
            break;

        case 'size_system':
            var options = new Array( 'EU', 'US', 'UK', 'DE', 'FR', 'JP', 'CN', 'IT', 'BR', 'MEX', 'AU' );
            break;

        case 'is_bundle':
            var options = new Array( 'yes', 'no' );
            break;

        case 'adult':
            var options = new Array( 'yes', 'no' );
            break;

        case 'energy_efficiency_class':
            var options = new Array( 'A', 'A+', 'A++', 'A+++', 'B', 'C', 'D', 'E', 'F', 'G' );
            break;

        case 'excluded_destination':
            var options = new Array( 'Shopping', 'DisplayAds' );
            break;

        default:
            var options = new Array();
            break;
    }
    
    return options;
}

function switchToGoogleFeedFormMainInputs( isNew, channel ) {
    var language = "en-US";
                
    $jq( '#country-list-row' ).show();
    $jq( '#category-list-row' ).show();
	$jq( '#google-feed-title-row' ).show();
	$jq( '#google-feed-description-row' ).show();
    //$jq( '#category-map' ).show();
    $jq( '#aggregator-selector-row' ).hide();

    appendCategoryLists( parseInt( channel ), language, isNew );
}

function googleInputChanged( feedId, categoryChanged ) {
    var fileName = $jq( '#file-name' ).val();
    var selectedCountry = $jq( '#countries' ).val();
    var selectedMainCategory = $jq( '#lvl_0' ).val();
    
    // enable or disable the correct buttons for the google channel
    if ( fileName && selectedCountry !== '0' && selectedMainCategory && selectedMainCategory !== '0' ) {
        updateFeedFormAfterInputChanged( feedId, categoryChanged );
    } else {
        // keep the Generate and Save buttons disabled
        disableFeedActionButtons();
    }
}

// ALERT! This function is equivalent to the special_clothing_group_countries() function in class-feed.php in the google channels folder
function googleSpecialClothingGroupCountries() {
    return [ 'US', 'GB', 'DE', 'FR', 'JP', 'BR' ]; // Brazil added based on the new Feed Specifications from september 2015
}

// ALERT! This function is equivalent to the special_shipping_countries() function in class-feed.php in the google channels folder
function googleSpecialShippingCountries() {
    return [ 'US', 'GB', 'DE', 'AU', 'FR', 'CH', 'CZ', 'NL', 'IT', 'ES', 'JP' ];
}

// ALERT! This function is equivalent to the special_product_countries() function in class-feed.php in the google channels folder
function googleSpecialProductCountries() {
    return [ 'US', 'GB', 'DE', 'AU', 'FR', 'CH', 'CZ', 'NL', 'IT', 'ES', 'JP', 'BR' ];
}