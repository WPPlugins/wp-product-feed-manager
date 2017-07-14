/*!
 * feed-html.js v1.8
 * Part of the WP Product Feed Manager
 * Copyright 2017, Michel Jongbloed
 *
 */

"use strict";

function wppfm_staticInputField( rowId, level, combinationLevel, value ) {

    return '<input type="text" name="static-input-field" id="static-input-field-' + rowId + '-' + level + '-' + combinationLevel
        + '" class="static-input-field" value="' + value + '" onchange="wppfm_staticValueChanged(' 
        + rowId + ', ' + level + ', ' + combinationLevel + ')">';
}

function wppfm_feedStaticValueSelector( fieldName, rowId, sourceLevel, level, value, channel ) {

    var restrictedFields = wppfm_restrictedStaticFields( channel, fieldName );

    if ( restrictedFields.length > 0 ) {

        return wppfm_displayCorrectStaticField( rowId, sourceLevel, level, channel, fieldName, value );
    } else {

        return wppfm_staticInputField( rowId, sourceLevel, level, value );
    }
}

function wppfm_staticInputSelect( rowId, level, combinationLevel, options, selected ) {

    var htmlCode = '<div class="static-value-control" id="static-value-control-' + rowId + '-' + level + '-' + combinationLevel + '">';

    htmlCode += '<select class="static-select-control input-select" id="static-condition-input-' + rowId + '-' + level + '-' + combinationLevel
        + '" onchange="wppfm_staticValueChanged(' + rowId + ', ' + level + ', ' + combinationLevel + ')">';

    for ( var i = 0; i < options.length; i++ ) {

        if ( options[i] !== selected ) {

            htmlCode += '<option value="' + options[i] + '">' + options[i].replace( '_', ' ' ) + '</option>';
        } else {

            htmlCode += '<option value="' + options[i] + '" selected>' + options[i].replace( '_', ' ' ) + '</option>';
        }
    }

    htmlCode += '</select></div>';

    return htmlCode;
}

function wppfm_advisedSourceSelector( rowId, sourceCountr, advisedSource ) {
    
    return '<div class="advised-source">' + advisedSource + wppfm_editSourceSelector( rowId, sourceCountr ) + '</div>';
}

function wppfm_editSourceSelector( rowId, sourceCountr ) {
    
    return ' (<a class="edit-output wppfm-btn wppfm-btn-small" href="javascript:void(0)" onclick="wppfm_editOutput('
        + rowId + ', ' + sourceCountr + ')">edit</a>)';
}

function wppfm_forAllProductsCondition( rowId, level, isVisible ) {
    
    var other = level > 0 ? 'other ' : '';

    return '<div class="colw col40w allproducts" id="condition-col-' + rowId + '-' + level + '" style="display:' + isVisible + '">'
        + ' for all ' + other + 'products '
        + '(<a class="edit-prod-query wppfm-btn wppfm-btn-small" href="javascript:void(0)" id="edit-prod-query-' + rowId + '-' 
        + level + '" onclick="wppfm_addCondition(' + rowId + ', ' + level + ', 0, \'\')">'
        + 'edit</a>)'
        + '</div>';
}

function wppfm_editValueSpan( rowId, sourceLevel, valueEditorLevel, displayStyle ) {

    return '<div class="edit-value-control" id="value-editor-input-query-add-span-' + rowId + '-' + sourceLevel + '-' + valueEditorLevel + '" style="display:' + displayStyle + '"><p>'
        + '(<a class="edit-prod-query wppfm-btn wppfm-btn-small" href="javascript:void(0)" id="edit-row-value-' + rowId + '-' + sourceLevel + '-' + valueEditorLevel + '" '
        + 'onclick="wppfm_addRowValueEditor(' + rowId + ', ' + sourceLevel + ', ' + valueEditorLevel +  ', \'\')">'
        + 'edit values</a>)'
        + '</p></div>';
}

function wppfm_addFeedSourceRow( rowId, sourceLevel, sourceRowsData, channel, removable ) {

    var borderStyleClass = sourceLevel > 0 ? ' dotted-top-line' : '';
    var showEditValuesControl = 'initial';
    var deleteValueControl = removable ? wppfm_removeOutputCntrl( rowId, sourceRowsData.fieldName ) : '';

    if ( sourceRowsData.customCondition  ) { // no edit value control for the Category item
        
        showEditValuesControl = 'none';
    }

    // source wrapper
    var htmlCode = '<div class="feed-source-row" id="source-' + rowId + '-' + sourceLevel + '">';

    // first column wrapper
    htmlCode += '<div class="add-to-feed-column colw col20w">';

    // first column (add to feed column)
    htmlCode += sourceLevel === 0 ? '<span class="output-field-label">' + sourceRowsData.fieldName + '</span>' + deleteValueControl : '&nbsp;';
    
    htmlCode += '</div>';

    // the source data and queries wrapper
    htmlCode += '<div class="source-data-column colw col80w' + borderStyleClass + '" id="source-data-' + rowId + '-' + sourceLevel + '">';

    htmlCode += wppfm_addSourceDataAndQueriesColumn( sourceLevel, sourceRowsData );

    // close the source data and queries wrapper
    htmlCode += '</div>';

    if ( sourceLevel === 0 && sourceRowsData.changeValues.length === 0 ) {
        
        htmlCode += wppfm_editValueSpan( sourceRowsData.rowId, sourceLevel, 0, showEditValuesControl );
    } else {

// Aanzetten om fout 73 verder te onderzoeken
//        console.log(JSON.stringify(sourceRowsData.changeValues));
//        console.log(sourceRowsData.changeValues.length);
//        console.log(sourceLevel);
        
//        for ( var i = 0; i < sourceRowsData.changeValues.length; i++ ) {
//
//            if ( sourceRowsData.changeValues[sourceLevel] ) {
//
//                // add the change value editor fields
//                htmlCode += wppfm_valueEditor( sourceRowsData.rowId, sourceLevel, i, sourceRowsData.changeValues );
//            }
//        }
    }

    // close the source wrapper
    htmlCode += '</div>';

    return htmlCode;
}

function wppfm_removeOutputCntrl( rowId, fieldName ) {
    
    var htmlCode = ' (';
    htmlCode += '<a class="remove-output wppfm-btn wppfm-btn-small" href="javascript:void(0)" id="' + rowId + '" onclick="wppfm_removeRow(' + rowId + ', \'' + fieldName + '\')">remove</a>';
    htmlCode += ') ';
    
    return htmlCode;
}

function wppfm_conditionQueryCntrl( id, sourceLevel, conditionLevel, subConditionLevel, identifier, onChangeFunction, selectedValue ) {

    var queryOptions = wppfm_queryOptionsEng();
    var queryLevelString = subConditionLevel !== -1 ? '-' + subConditionLevel : '';
    var queryLevelFunctionString = subConditionLevel !== -1 ? ', ' + subConditionLevel : '';

    var htmlCode = '<select class="select-control condition-query-select" id="' + identifier + '-'
        + id + '-' + sourceLevel + '-' + conditionLevel + queryLevelString + '" onchange="' + onChangeFunction + '(' + id + ', ' + sourceLevel + ', ' + conditionLevel + queryLevelFunctionString + ')"> ';

    for ( var i = 0; i < queryOptions.length; i++ ) {

        htmlCode += parseInt( selectedValue ) !== i ? '<option value = "' + i + '">' + queryOptions[i] + '</option>'
            : '<option value = "' + i + '" selected>' + queryOptions[i] + '</option>';
    }

    htmlCode += '</select>';

    return htmlCode;
}

function wppfm_valueEditor( rowId, sourceLevel, valueEditorLevel, valueObject ) {
    
    var valueArray = wppfm_valueStringToValueObject( valueObject[sourceLevel] );
    var queryDisplay = valueObject[valueEditorLevel] && valueObject[valueEditorLevel].q ? 'none' : 'initial';
    var value = wppfm_countObjectItems( valueArray ) > 0 ? valueArray : wppfm_makeCleanValueObject();
    var valueSelector = 'and change values ';
    var html = '<div class="change-source-value-wrapper" id="edit-value-span-' + rowId + '-' + sourceLevel + '-0">';
	var removeValueEditorSelector = sourceLevel === 0 ? ' (<a class="remove-value-editor-query wppfm-btn wppfm-btn-small" href="javascript:void(0)" id="remove-value-editor-query-' + rowId + '-' + sourceLevel
     + '-' + valueEditorLevel + '" onclick="wppfm_removeValueEditor(' + rowId + ', ' + sourceLevel + ', ' + valueEditorLevel + ')">remove value editor</a>)' : '';

    if ( sourceLevel > 0 ) {

// 170516
//        valueSelector = value.preCondition === 'and'
//            ? '<select id="value-options-pre-selector-' + rowId + '-' + sourceLevel + '-0"><option>or</option><option selected>and</option></select>'
//            : '<select id="value-options-pre-selector-' + rowId + '-' + sourceLevel + '-0"><option selected>or</option><option>and</option></select>';
		valueSelector = 'and ';
    }

    html += valueSelector;
    html += wppfm_changeValueCntrl( rowId, sourceLevel, valueEditorLevel, value.condition );
    html += '<span id="value-editor-input-span-' + rowId + '-' + sourceLevel + '-0">';
//    html += wppfm_getCorrectValueSelector( rowId, sourceLevel, valueEditorLevel, value.condition, value.value, value.endValue );
    html += wppfm_getCorrectValueSelector( rowId, sourceLevel, 0, value.condition, value.value, value.endValue );
    html += '</span>';
    html += '<span id="value-editor-selectors-' + rowId + '-' + sourceLevel + '-' + valueEditorLevel + '">';
    html += wppfm_forAllProductsAtChangeValuesSelector( rowId, sourceLevel, valueEditorLevel, queryDisplay );
    html += '<span id="value-editor-input-query-remove-span-' + rowId + '-' + sourceLevel + '-' + valueEditorLevel + '">';
    html += removeValueEditorSelector;
    html += '</span>';
//    html += wppfm_editValueSpan( rowId, level, displayType );
    html += '</span>';
    html += '<span id="value-editor-queries-' + rowId + '-' + sourceLevel + '-0">';

    if ( valueObject[valueEditorLevel] && valueObject[valueEditorLevel].q ) {
            
        for ( var i = 1; i < valueObject[valueEditorLevel].q.length + 1; i++ ) {

            var queryArray = wppfm_convertQueryStringToQueryObject( valueObject[valueEditorLevel].q[i - 1][i] );

            var lastValue = i >= valueObject[valueEditorLevel].q.length ? true : false;

            html += wppfm_ifValueQuerySelector( rowId, sourceLevel, i, queryArray, lastValue );
        }
    }

    html += '</span></div>';

    return html;
}

function wppfm_endrow( rowId ) {
	
	return '<div class="end-row" id="end-row-id-' + rowId + '">&nbsp;</div>';
}

function wppfm_forAllProductsAtChangeValuesSelector( rowId, sourceLevel, valueEditorLevel, displayStatus ) {
    
    var other = sourceLevel > 0 ? 'other ' : '';

    return '<div class="colw col30w allproducts" id="value-editor-input-query-span-' + rowId + '-' + sourceLevel + '-0" style="display:' + displayStatus + ';float:right;">'
        + ' for all ' + other + 'products'
        + ' (<a class="edit-value-editor-query wppfm-btn wppfm-btn-small" href="javascript:void(0)" id="edit-value-editor-query-' + rowId + '-' + sourceLevel + '-' + valueEditorLevel
        + '" onclick="wppfm_addValueEditorQuery(' + rowId + ', ' + sourceLevel + ', 0)">edit</a>)'
        + '</div>';
}

function wppfm_valueOptionsSingleInput( rowId, sourceLevel, valueEditorLevel, value ) {

    return ' to' + wppfm_valueOptionsSingleInputValue( rowId, sourceLevel, valueEditorLevel, value );
}

function wppfm_valueOptionsElementInput( rowId, sourceLevel, valueEditorLevel, value ) {

    return ' with element name' + wppfm_valueOptionsSingleInputValue( rowId, sourceLevel, valueEditorLevel, value );
}

function wppfm_valueOptionsSingleInputValue( rowId, sourceLevel, valueEditorLevel, value ) {

    return ' <input type="text" onchange="wppfm_valueInputOptionsChanged(' + rowId + ', ' + sourceLevel 
        + ', ' + valueEditorLevel + ')" id="value-options-input-' + rowId + '-' + sourceLevel + '-' + valueEditorLevel + '" value="' + value + '">';
}

function wppfm_valueOptionsReplaceInput( rowId, sourceLevel, valueEditorLevel, value, endValue ) {

    console.log(value);

    return '<input type="text" onchange="wppfm_valueInputOptionsChanged(' + rowId + ', ' + sourceLevel + ', ' 
        + valueEditorLevel + ' )" id="value-options-input-' + rowId + '-' + sourceLevel + '-' + valueEditorLevel
        + '" value="' + value + '"> with <input type="text" onchange="wppfm_valueInputOptionsChanged('
        + rowId + ', ' + sourceLevel + ', ' + valueEditorLevel + ')" id="value-options-input-with-' + rowId + '-' 
        + sourceLevel + '-' + valueEditorLevel + '" value="' + endValue + '">';
}

function wppfm_valueOptionsRecalculate( rowId, sourceLevel, valueEditorLevel, selectedValue, value ) {

    var valueOptions = wppfm_changeValuesRecalculateOptions();

    var htmlCode = '<select class="select-value-options" id="value-options-recalculate-options-' + rowId + '-' + sourceLevel + '-0">';

    for ( var i = 0; i < valueOptions.length; i++ ) {

        htmlCode += valueOptions[i] !== selectedValue ? '<option value = "' + i + '">' + valueOptions[i] + '</option>'
            : '<option value = "' + i + '" selected>' + valueOptions[i] + '</option>';
    }

    htmlCode += '</select>';
    htmlCode += ' <input type="text" onchange="wppfm_valueInputOptionsChanged(' + rowId + ', ' + sourceLevel + ', ' + valueEditorLevel + ')" id="value-options-input-'
        + rowId + '-' + sourceLevel + '-' + valueEditorLevel + '" value="' + value + '">';

    return htmlCode;
}

function wppfm_changeValueCntrl( rowId, conditionLevel, valueEditorLevel, selectedValue ) {

    var valueOptions = wppfm_changeValuesOptions();

    var htmlCode = '<select class="select-value-options" id="value-options-'
        + rowId + '-' + conditionLevel + '-0" onchange="wppfm_valueOptionChanged(' + rowId + ', ' + conditionLevel + ', 0)"> ';

    for ( var i = 0; i < valueOptions.length; i++ ) {

        htmlCode += valueOptions[i] !== selectedValue ? '<option value = "' + i + '">' + valueOptions[i] + '</option>'
            : '<option value = "' + i + '" selected>' + valueOptions[i] + '</option>';
    }

    htmlCode += '</select>';

    return htmlCode;
}

function wppfm_mapToDefaultCategoryElement( categoryId, category ) {

    var categoryText = '';
    var editable = '';
    
    switch ( category ) {
        
        case 'default':
            categoryText = 'Map to Default Category';
            break;
            
        case 'shopCategory':
            categoryText = 'Use Shop Category';
            break;
            
        default:
            categoryText = category;
            break;
    }
    
    if ( category !== 'shopCategory' ) {
        
        editable = ' (<a class="edit-feed-mapping wppfm-btn wppfm-btn-small" '
       + 'href="javascript:void(0)" data-id="' + categoryId + '" id="edit-feed-mapping-' + categoryId
       + '" onclick="wppfm_editCategoryMapping(' + categoryId + ')">edit</a>)';
    }
    
    return '<div class="feed-category-map-to-default" id="feed-category-map-to-default-' + categoryId 
       + '" style="display:initial"><span id="category-text-span-' + categoryId + '">' + categoryText 
       + '</span>' + editable + '</div>';
}

function wppfm_mapToCategoryElement( categoryId, categoryString ) {
    
    return '<div class="feed-category-map" id="feed-category-map-' + categoryId 
       + '" style="display:initial"><span id="category-text-span-' + categoryId + '">' + categoryString 
       + '</span> (<a class="edit-feed-mapping wppfm-btn wppfm-btn-small" '
       + 'href="javascript:void(0)" data-id="' + categoryId + '" id="edit-feed-mapping-' + categoryId
       + '" onclick="wppfm_editCategoryMapping(' + categoryId + ')">edit</a>)</div>';
}

//function wppfm_categorySource( rowId, sourceValue ) {
function wppfm_categorySource() {

    return '<span id="category-source-string">Defined by the Category Mapping Table.</span>';
}

function wppfm_freeCategoryInputCntrl( type, id, value ) {
    
    var valueString = value ? ' value="' + value + '"' : '';
    
    return '<input type="text" name="free-category" class="free-category-text-input custom-category-' 
        + type + '" id="free-category-text-input" onchange="wppfm_freeCategoryChanged(\'' 
        + type + '\', \'' + id + '\')"' + valueString + '>';
}

function wppfm_inputFieldCntrl( rowId, sourceLevel, sourceValue, staticValue, advisedSource, combinedValue, isCustom ) {
	
    var hasAdvisedValueHtml = advisedSource ? '<option value="advised" itemprop="basic">Use advised source</option>' : '';
    var staticSelectedHtml = staticValue ? ' selected' : '';
    var prefix = sourceLevel > 0 ? 'or ' : '';
    var hasCombinedOptionHtml = !combinedValue ? '<option value="combined" itemprop="basic">Combine source fields</option>'
       : '<option value="combined" selected>Combine source fields</option>';
    var customCategoryMapping = isCustom ? '<option value="category_mapping" itemprop="basic">Category Mapping</option>' : '';

    return '<div class="select-control">' + prefix + '<select class="select-control input-select" id="input-field-cntrl-' + rowId + '-' + sourceLevel
        + '" onchange="wppfm_changedOutput(' + rowId + ', ' + sourceLevel + ', \'' + advisedSource + '\')"> '
        + '<option value="select" itemprop="basic">-- Select a source field --</option>'
        + hasAdvisedValueHtml
        + '<option value="static" itemprop="basic"'
        + staticSelectedHtml
        + '>Fill with a static value</option>'
        + customCategoryMapping
        + hasCombinedOptionHtml
        + wppfm_fixedSourcesList( sourceValue ) + '</select></div>';
}

function wppfm_combinedInputFieldCntrl( rowId, sourceLevel, combinedLevel, selectedValue, fieldName, channel ) {

	var isStatic = selectedValue && selectedValue.startsWith( 'static#' );
    var staticSelectedHtml = isStatic ? ' selected' : '';
	var staticInputHtml = isStatic ? wppfm_feedStaticValueSelector( fieldName, rowId, sourceLevel, combinedLevel, selectedValue.substring(7), channel ) : '';

    return '<select class="select-control input-select align-left" id="combined-input-field-cntrl-' + rowId + '-' + sourceLevel + '-' + combinedLevel
        + '" onchange="wppfm_changedCombinedOutput(' + rowId + ', ' + sourceLevel + ', ' + combinedLevel + ')"> '
        + '<option value="select" itemprop="basic">-- Select a source field --</option>'
        + '<option value="static" itemprop="basic"'
        + staticSelectedHtml
        + '>Fill with a static value</option>'
        + wppfm_fixedSourcesList( selectedValue ) + '</select>'
		+ '<div class="static-value-control" id="static-value-control-' + rowId + '-' + sourceLevel + '-' + combinedLevel + '">'
		+ staticInputHtml
		+ '</div>';
}

function wppfm_combinedSeparatorCntrl( rowId, sourceLevel, combinedLevel, selectedValue ) {

    return '<select class="select-control input-select align-left" id="combined-separator-cntrl-' + rowId + '-' + sourceLevel + '-' + combinedLevel
        + '" onchange="wppfm_changedCombinationSeparator(' + rowId + ', ' + sourceLevel + ', ' + combinedLevel + ')"> '
        + wppfm_getCombinedSeparatorList( selectedValue )
        + '</select>';
}

function wppfm_alternativeInputFieldCntrl( id, selectedValue ) {

    var selectedValueHtml = selectedValue === 'static' ? ' selected' : '';

    return '<select class="select-control alternative-input-select" id="alternative-input-field-cntrl-' + id
        + '" onchange="wppfm_changedAlternativeSource(' + id + ')"> '
        + '<option value="select">-- Select a source field --</option>'
        + '<option value="empty">-- an empty field --</option>'
        + '<option value="static"'
        + selectedValueHtml
        + '>Fill with a static value</option>'
        + wppfm_fixedSourcesList( selectedValue ) + '</select>';
}

function wppfm_outputFieldCntrl( level ) {

    var outputLevelHtml = level === 3 ? '<option value="no-value">-- Add recommended output --</option>' : '<option value="no-value">-- Add optional output --</option>';

    return '<select class="select-control input-select" id="output-field-cntrl-' + level + '"> '
        + outputLevelHtml
        + wppfm_getOutputFieldsList( level )
        + '</select>';
}

function wppfm_customOutputFieldCntrl() {
    
    return '<input type="text" name="custom-output-title" id="custom-output-title-input" placeholder="Enter an output title" onfocusout="wppfm_changedCustomOutputTitle()">';
}

function wppfm_conditionFieldCntrl( id, sourceLevel, conditionLevel, subConditionLevel, identifier, selectedValue, onChange ) {

    var subConditionLevelString = subConditionLevel !== -1 ? '-' + subConditionLevel : '';
    var emptyOption = identifier === 'or-field-cntrl' ? '<option value="empty">-- Empty field --</option>' : '';
    var onChangeFunction = onChange ? ' onchange="' + onChange + '"' : '';

    return '<select class="select-control input-select" id="' + identifier + '-' + id + '-' + sourceLevel + '-' + conditionLevel + subConditionLevelString + '"' + onChangeFunction + '> '
        + '<option value="select">-- Select a source field --</option>'
        + emptyOption
        + wppfm_fixedSourcesList( selectedValue )
        + '</select>';
}

function wppfm_filterPreCntrl( feedId, filterLevel, selectedValue ) {

	var preString = '<select id="filter-pre-control-' + feedId + '-' + filterLevel  +  '" onchange="wppfm_filterChanged(' + feedId + ', ' + filterLevel + ')">';

	if ( filterLevel > 1 ) {
	
		return selectedValue === '1'
			? preString + '<option value="2">or</option><option value="1" selected>and</option></select>'
			: preString + '<option value="2" selected>or</option><option value="1">and</option></select>';
	} else {
		
		return '';
	}
	
}

function wppfm_filterSourceCntrl( feedId, filterLevel, selectedValue ) {
	
	return '<select class="select-control input-select" id="filter-source-control-' + feedId + '-' + filterLevel + '" onchange="wppfm_filterChanged(' + feedId + ', ' + filterLevel + ')">'
        + '<option value="select">-- Select a source field --</option>'
		+ wppfm_fixedSourcesList( selectedValue )
		+ '</select>';
}

function wppfm_filterOptionsCntrl( feedId, filterLevel, selectedValue ) {

    var filterOptions = wppfm_queryOptionsEng();

    var htmlCode = '<select class="select-control condition-query-select" id="filter-options-control-' + feedId + '-' + filterLevel;
	htmlCode += '" onchange="wppfm_filterChanged(' + feedId + ', ' + filterLevel + ')">';

    for ( var i = 0; i < filterOptions.length; i++ ) {

        htmlCode += parseInt( selectedValue ) !== i ? '<option value = "' + i + '">' + filterOptions[i] + '</option>'
            : '<option value = "' + i + '" selected>' + filterOptions[i] + '</option>';
    }

    htmlCode += '</select>';

    return htmlCode;
}

function wppfm_filterInputCntrl( feedId, filterLevel, inputLevel, value) {
	
	var identString = feedId + '-' + filterLevel + '-' + inputLevel;
	var andString = inputLevel > 1 ? ' and ' : '';
	var s = inputLevel === 1 ? 1 : 3;
	
	if ( inputLevel > 1 ) {
		
		var v = value && value.includes( '#' ) ? value.split( '#' )[s] : '';
	} else {
		
		var v = value ? value : '';
	}

	var style = inputLevel > 1 && !v ? 'style ="display:none"' : 'style ="display:initial"';
	
	return '<span id="filter-input-span-' +  identString + '"' + style + '>' + andString + '<input type="text" name="filter-value" id="filter-input-control-' + identString
		+ '" onchange="wppfm_filterChanged(' + feedId + ', ' + filterLevel + ', ' + inputLevel  + ')" value="' + v + '"></span>';
}