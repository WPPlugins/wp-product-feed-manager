/*!
 * data.js v4.0
 * Part of the WP Product Feed Manager
 * Copyright 2016, Michel Jongbloed
 *
 */

"use strict";

/**
 * Returns an array with all possible condition options
 * 
 * @return An array with possible condition options
 */
function wppfm_queryOptionsEng() {

	return [ 'includes', 'does not include', 'is equal to', 'is not equal to', 'is empty', 'is not empty', 'starts with',
		'does not start with', 'ends with', 'does not end with', 'is greater than', 'is greater or equal to', 'is smaller than',
		'is smaller or equal to', 'is between' ];
}

// obsolete 290117
//function wppfm_googleRestrictedStaticValueLabels() {
//
//	return [ 'condition', 'availability', 'identifier_exists', 'gender', 'age_group', 'size_type',
//		'size_system', 'is_bundle', 'adult', 'energy_efficiency_class' ];
//}

function wppfm_changeValuesOptions() {

	return [ 'change nothing', 'overwrite', 'replace', 'remove', 'add prefix', 'add suffix', 'recalculate', 'convert to child-element' ];
}

function wppfm_changeValuesRecalculateOptions() {

	return [ 'add', 'substract', 'multiply', 'divide' ];
}

function wppfm_woocommerceSourceOptions() {

	return [
		{ value: '_backorders', label: 'Allow Backorders', prop: 'meta' },
		{ value: '_button_text', label: 'Button Text', prop: 'meta' },
		//{value:'', label:'Cross-Sells', prop:'meta'},
		{ value: '_height', label: 'Dimensions Height', prop: 'meta' },
		{ value: '_length', label: 'Dimensions Length', prop: 'meta' },
		{ value: '_width', label: 'Dimensions Width', prop: 'meta' },
		{ value: '_downloadable', label: 'Downloadable', prop: 'meta' },
		//{value:'', label:'Enable Reviews', prop:'meta'},
		{ value: 'attachment_url', label: 'Featured Image', prop: 'main' }, // in the end this item will be handled procedural
		//{value:'', label:'Grouping', prop:'meta'},
		{ value: 'item_group_id', label: 'Item Group Id', prop: 'main' },
		{ value: '_wp_attachement_metadata', label: 'Image Library', prop: 'main' },
		{ value: '_manage_stock', label: 'Manage Stock?', prop: 'meta' },
		{ value: '_max_variation_price', label: 'Max Variation Price', prop: 'meta' },
		{ value: '_max_variation_regular_price', label: 'Max Variation Regular Price', prop: 'meta' },
		{ value: '_max_variation_sale_price', label: 'Max Variation Sale Price', prop: 'meta' },
		{ value: 'menu_order', label: 'Menu Order', prop: 'meta' },
		{ value: '_min_variation_price', label: 'Min Variation Price', prop: 'meta' },
		{ value: '_min_variation_regular_price', label: 'Min Variation Regular Price', prop: 'meta' },
		{ value: '_min_variation_sale_price', label: 'Min Variation Sale Price', prop: 'meta' },
		{ value: 'post_author', label: 'Post Author', prop: 'post' },
		{ value: 'post_date', label: 'Post Date', prop: 'post' },
		{ value: 'post_date_gmt', label: 'Post Date GMT', prop: 'post' },
		{ value: 'ID', label: 'Post ID', prop: 'post' },
		{ value: 'post_modified', label: 'Post Modified', prop: 'post' },
		{ value: 'post_modified_gmt', label: 'Post Modified GMT', prop: 'post' },
		{ value: 'product_cat_string', label: 'Product Category String', prop: 'main' },
		{ value: 'post_content', label: 'Product Description', prop: 'post' },
		{ value: 'post_excerpt', label: 'Product Short Description', prop: 'post' },
		{ value: 'product_tags', label: 'Product Tags', prop: 'meta' },
		{ value: 'post_title', label: 'Product Title', prop: 'post' },
		//{value:'', label:'Product Type', prop:'meta'},
		{ value: 'permalink', label: 'Permalink', prop: 'post' },
		//{value:'', label:'Purchase Note', prop:'meta'},
		{ value: '_regular_price', label: 'Regular Price', prop: 'meta' },
		{ value: '_sale_price', label: 'Sale Price', prop: 'meta' },
		{ value: '_sale_price_dates_from', label: 'Sale Price Dates From', prop: 'meta' },
		{ value: '_sale_price_dates_to', label: 'Sale Price Dates To', prop: 'meta' },
		{ value: 'product_cat', label: 'Selected Product Categories', prop: 'main' },
		{ value: 'fixed_shipping_price', label:'Fixed Shipping Price', prop:'main'},
		{ value: 'shipping_class', label:'Shipping Class', prop:'main'},
		{ value: '_sku', label: 'SKU', prop: 'meta' },
		{ value: '_sold_individually', label: 'Sold Individually', prop: 'meta' },
		{ value: '_stock', label: 'Stock Qty', prop: 'meta' },
		{ value: '_stock_status', label: 'Stock Status', prop: 'meta' },
		//{value:'', label:'Tax Status', prop:'meta'},
		//{value:'', label:'Tax Class', prop:'meta'},
		//{value:'', label:'Up-Sells', prop:'meta'},
		{ value: '_virtual', label: 'Virtual', prop: 'meta' },
		{ value: '_weight', label: 'Weight', prop: 'meta' },
		{ value: 'wc_currency', label: 'WooCommerce Currency', prop: 'main' },
		{ value: 'last_update', label: 'Last Feed Update', prop: 'main' }
	];
}

function wppfm_sourceOptionsConverter( optionValue ) {
	
	var list = wppfm_woocommerceSourceOptions();
	
	for ( var key in list ) {
		
		if ( list[key]['value'] === optionValue ) {
			return list[key]['label'];
			break;
		}
	}
}