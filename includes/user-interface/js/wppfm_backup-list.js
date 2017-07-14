/*!
 * wppfm_backup-list.js v1.0
 * Part of the WP Product Feed Manager
 * Copyright 2017, Michel Jongbloed
 *
 */

"use strict";

var $jq = jQuery.noConflict();

function wppfm_resetBackupsList() {

    var backupListData = null;
    var listHtml = '';

    wppfm_getBackupsList( function ( list ) {

        if ( list !== "0" ) {

            backupListData = JSON.parse( list );

            // convert the data to html code
            listHtml = wppfm_backupsTable( backupListData );
        }
        else {
            listHtml = wppfm_emptyBackupsTable();
        }

        $jq( '#wppfm-backups-list' ).empty(); // first clear the feedlist

        $jq( '#wppfm-backups-list' ).append( listHtml );
    } );
}

/**
 * Restores the options on the settings page
 */
function wppfm_resetOptionSettings() {
	
	wppfm_getSettingsOptions( function ( optionsString ) {
		
		if ( optionsString ) {

			var options = JSON.parse( optionsString );
			
			$jq( '#wppfm_ftp_passive_mode' ).prop( "checked", options[0] === "true" ? true : false );
			$jq( '#wppfm_auto_feed_fix_mode' ).prop( "checked", options[1] === "true" ? true : false );
		}
	} );
}

function wppfm_backupsTable( list ) {

    var htmlCode = '';

    for ( var i = 0; i < list.length; i++ ) {

		var backup = list[i].split( '&&' );
		var fileName = backup[0];
		var fileDate = backup[1];

        htmlCode += '<tr id="feed-row"';
        if ( i % 2 === 0 ) { htmlCode += ' class="alternate"'; } // alternate background color per row
        htmlCode += '>';
		htmlCode += '<td id="file-name">' + fileName + '</td>';
		htmlCode += '<td id="file-date">' + fileDate + '</td>';
		htmlCode += '<td id="actions"><strong><a href="javascript:void(0);" onclick="wppfm_deleteBackupFile(\'' + fileName + '\')">Delete </a>';
        htmlCode += '| <a href="javascript:void(0);" onclick="wppfm_restoreBackupFile(\'' + fileName + '\')">Restore </a>';
        htmlCode += '| <a href="javascript:void(0);" onclick="wppfm_duplicateBackupFile(\'' + fileName + '\')">Duplicate </a></strong></td>';
		htmlCode += '</tr>';
    }

    return htmlCode;
}

function wppfm_emptyBackupsTable() {

    var htmlCode = '';

    htmlCode += '<tr>';
    htmlCode += '<td colspan = 4>No backup data found</td>';
    htmlCode += '</tr>';

    return htmlCode;
}

/**
 * Document ready actions
 */
jQuery( document ).ready( function () {

    // fill the backups list
    wppfm_resetBackupsList();
} );
