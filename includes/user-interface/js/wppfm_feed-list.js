/*!
 * feed-list.js v1.1
 * Part of the WP Product Feed Manager
 * Copyright 2017, Michel Jongbloed
 *
 */

"use strict";

var $jq = jQuery.noConflict();

function wppfm_fillFeedList() {

    var feedListData = null;
    var listHtml = '';

    wppfm_getFeedList( function ( list ) {

        if ( list !== "0" ) {

            feedListData = JSON.parse( list );

            // convert the data to html code
            listHtml = wppfm_feedListTable( feedListData );
        }
        else {
            listHtml = wppfm_emptyListTable();
        }

        $jq( '#wppfm-feed-list' ).empty(); // first clear the feedlist

        $jq( '#wppfm-feed-list' ).append( listHtml );
    } );
}

function appendCategoryLists( channelId, language, isNew ) {

    if ( isNew ) {

        wppfm_getCategoryListsFromString( channelId, '', language, function ( categories ) {

            var list = JSON.parse( categories )[0];

            if ( list && list.length > 0 ) {

                $jq( '#lvl_0' ).html( wppfm_categorySelectCntrl( list ) );
                $jq( '#lvl_0' ).prop( 'disabled', false );
            } else {

                // as the user selected a free format, just show a text input control
                $jq( '#category-selector-lvl' ).html( wppfm_freeCategoryInputCntrl( 'default', '0', false ) );
                $jq( '#category-selector-lvl' ).prop( 'disabled', false );
            }
        } );
    }
}

function wppfm_resetFeedList() {

    wppfm_fillFeedList();
}

function wppfm_feedListTable( list ) {

    var htmlCode = '';

    for ( var i = 0; i < list.length; i++ ) {

        var status = list [ i ] [ 'status' ];
        var changeStatus = "Activate";
        var feedId = list [ i ] ['product_feed_id'];
        var feedUrl = list [ i ] ['url'];
        var feedReady = status !== 'Not ready' ? true : false;
        var nrProducts = feedReady ? list [ i ] ['products'] : 'Still processing, please wait a few seconds and then reload this page';
		var fileName = feedUrl.lastIndexOf( '/' ) > 0 ? feedUrl.slice( feedUrl.lastIndexOf( '/' ) - feedUrl.length + 1 ) : list [ i ] [ 'title' ];
		var fileExists = feedUrl === 'No feed generated' ? false : true;
		
        if ( status === "OK" ) { changeStatus = "Deactivate"; }

        htmlCode += '<tr id="feed-row"';

        if ( i % 2 === 0 ) { htmlCode += ' class="alternate"'; } // alternate background color per row

        htmlCode += '>';
        htmlCode += '<td id="title">' + list [ i ] ['title'] + '</td>';
        htmlCode += '<td id="url">' + feedUrl + '</td>';
        htmlCode += '<td id="updated">' + list [ i ] ['updated'] + '</td>';
        htmlCode += '<td id="products">' + nrProducts + '</td>';
        htmlCode += '<td id="feed-status-' + feedId + '" value="' + status + '" style="color: ' + list [ i ] [ 'color' ] + '"><strong>';
        htmlCode += feedReady ? status : 'Processing';
        htmlCode += '</strong></td>';
        
        if ( feedReady ) {
            
            htmlCode += '<td id="actions"><strong><a href="javascript:void(0);" onclick="parent.location=\'admin.php?page=wp-product-feed-manager-add-new-feed&id=' + feedId + '\'">Edit </a>';
            htmlCode += fileExists ? '| <a href="javascript:void(0);" onclick="wppfm_viewFeed(\'' + feedUrl + '\')">View </a>' : '';
            htmlCode += '| <a href="javascript:void(0);" onclick="wppfm_deleteSpecificFeed(' + feedId + ', \'' + fileName + '\')">Delete </a>';
            htmlCode += fileExists ? '| <a href="javascript:void(0);" onclick="wppfm_deactivateFeed(' + feedId + ')" id="feed-status-switch-' + feedId + '">' + changeStatus + ' </a>' : '';
            htmlCode += '| <a href="javascript:void(0);" onclick="wppfm_duplicateFeed(' + feedId + ', \'' + list [ i ] ['title'] + '\')">Duplicate </a></strong></td>';
        } else {
            
            htmlCode += '<td id="actions"><strong>';
            htmlCode += '<a href="javascript:void(0);" onclick="parent.location=\'admin.php?page=wp-product-feed-manager-add-new-feed&id=' + feedId + '\'">Edit </a>';
            htmlCode += '| <a href="javascript:void(0);" onclick="wppfm_deleteSpecificFeed(' + feedId + ', \'' + fileName + '\')"> Delete</a>';
            htmlCode += '</strong></td>';
        }
    }

    return htmlCode;
}

function wppfm_emptyListTable() {

    var htmlCode = '';

    htmlCode += '<tr>';
    htmlCode += '<td colspan = 4>No data found</td>';
    htmlCode += '</tr>';

    return htmlCode;
}

function wppfm_updateFeedRowStatus( feedId, status ) {

    if ( status === 1 ) {
        $jq( '#feed-status-' + feedId ).html( '<strong>OK</strong>' );
        $jq( '#feed-status-' + feedId ).css( 'color', '#54C754' );
        $jq( '#feed-status-switch-' + feedId ).html( 'Deactivate' );
    }
    else {
        $jq( '#feed-status-' + feedId ).html( '<strong>On hold</strong>' );
        $jq( '#feed-status-' + feedId ).css( 'color', '#0074A2' );
        $jq( '#feed-status-switch-' + feedId ).html( 'Activate' );
    }
}

/**
 * Document ready actions
 */
jQuery( document ).ready( function () {

    // fill the items on the main admin page
    wppfm_resetFeedList();
} );
