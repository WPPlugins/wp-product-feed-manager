/*!
 * general-functions.js v1.1
 * Part of the WP Product Feed Manager
 * Copyright 2016, Michel Jongbloed
 *
 */

"use strict";

/**
 * Finds the index of an object with a specific value in an array of objects
 * 
 * @param {array} theArray with objects
 * @param {string} searchTerm
 * @param {string} arrayProperty
 * @returns {int} the index of the object or false of its not in the array
 */
function wppfm_arrayObjectIndexOf( theArray, searchTerm, arrayProperty ) {
    
    for ( var i = 0, len = theArray.length; i < len; i++ ) {

        if ( theArray[ i ][ arrayProperty ] === searchTerm ) {
            
            return i; // return the index
        }
    }

    return -1; // return false if object could not be found
}

/**
 * Get a specific variable from the current url
 * 
 * @param {string} key
 * @returns {String|getUrlVariable.param}
 */
function wppfm_getUrlVariable( key ) {

    var result = "";
    var url = window.location.search.substring( 1 );
    var params = url.split( "&" );

    for ( var i = 0; i < params.length; i++ ) {
        var param = params[i].split( "=" );

        if ( param[0] === key ) {
            result = param[1];
        }
    }

    return result;
}

/**
 * Counts the number of items in an object
 * 
 * @param {object} object
 * @returns {int} number of items in object
 */
function wppfm_countObjectItems( object ) {

    var count = 0;

    for ( var k in object ) {

        if ( object.hasOwnProperty( k ) ) {

            count++;
        }
    }

    return count;
}

/**
 * Returns true if the object is empty
 * 
 * @param {object} object
 * @returns {Boolean} true if object is empty
 */
// TODO: Er bestaat ook een jQuery.isEmptyObject() functie. In hoeverre is die bruikbaar ter vervanging van onderstaande functie?
function wppfm_isEmptyQueryObject( object ) {

    // null and undefined are "empty"
    if ( object === null ) {
        return true;
    }

    // Assume if it has a length property with a non-zero value
    // that that property is correct.
    if ( object.length > 0 )
        return false;
    if ( object.length === 0 )
        return true;

    // Otherwise, does it have any properties of its own?
    // Note that this doesn't handle
    // toString and valueOf enumeration bugs in IE < 9
    for ( var key in object ) {
        //if ( hasOwnProperty.call( object, key ) )
        if ( object.hasOwnProperty( key ) )
            return false;
    }

    return true;
}

/**
 * Takes a string with a number at the end and increments the number
 * 
 * @param {String} v
 * @returns {String} with incremented number
 */
function wppfm_incrementLast( v ) {
    
    return v.replace( /[0-9]+(?!.*[0-9])/, parseInt( v.match( /[0-9]+(?!.*[0-9])/ ), 10 ) + 1 );
}