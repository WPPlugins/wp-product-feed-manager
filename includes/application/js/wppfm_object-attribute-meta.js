/*!
 * object-attribute-meta.js v1.1
 * Part of the WP Product Feed Manager
 * Copyright 2016, Michel Jongbloed
 *
 */

"use strict";

function wppfm_attributeMeta( key, value ) {

    this.key = key;
    this.value = value;
}

function countSources( mappingData ) {
    
    return mappingData.length > 0 ? mappingData.length : 1;
}

function wppfm_getMappingSourceValue( mapping, sourceCounter ) {
    
    if ( mapping && mapping.length > 0 ) {

        if (  mapping[sourceCounter] && 's' in mapping[sourceCounter] && 'source' in mapping[sourceCounter].s ) {

            // do not return a combined value
            return mapping[sourceCounter].s['source'] !== 'combined' ? mapping[sourceCounter].s['source'] : null;
        } else {
            
            return null;
        }
    } else {
        
        return null; // mapping seems to be empty
    }
}

function wppfm_getMappingCombinedValue( mapping, sourceCounter ) {
    
    if ( mapping && mapping.length > 0 ) {

        if ( mapping[sourceCounter] && 's' in mapping[sourceCounter] && 'source' in mapping[sourceCounter].s
            && mapping[sourceCounter].s['source'] === 'combined' ) {

            return mapping[sourceCounter].s['f'];
        } else {
            
            return null;
        }
    } else {
        
        return null; // mapping seems to be empty
    }
}

function wppfm_getMappingStaticValue( mapping, sourceCounter ) {
    
    if ( mapping && mapping.length > 0 ) {

        if ( mapping[sourceCounter] && 's' in mapping[sourceCounter] && 'static' in mapping[sourceCounter].s) {

            return mapping[sourceCounter].s['static'];
        } else {
            
            return null;
        }
    } else {
        
        return null; // mapping seems to be empty
    }
}

function wppfm_getMappingConditions( mapping, sourceCounter ) {
    
    if ( mapping && mapping.length > 0 ) {

        if ( mapping[sourceCounter] && 'c' in mapping[sourceCounter] ) {
            
            return mapping[sourceCounter].c;
        }
    } else {
        
        return null;
    }
}