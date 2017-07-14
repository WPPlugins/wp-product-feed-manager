/*!
 * metadatahandling.js v1.1
 * Part of the WP Product Feed Manager
 * Copyright 2017, Michel Jongbloed
 *
 */

"use strict";
var $jq = jQuery.noConflict();

function wppfm_storeSourceValue( level, currentMetaValue, type, valueToStore ) {

	console.log( 'level:', level );
	console.log( 'currentMetaValue:', currentMetaValue );
	console.log( 'type:', type );
	console.log( 'valueToStore:', valueToStore );
	
	var newValue = '';
	var o = currentMetaValue ? JSON.parse( currentMetaValue ) : { };
	
	if ( o && 't' in o ) { return currentMetaValue; } // do not change the meta value if it contains the main category

	if ( valueToStore && type !== 'clear' ) {

		var t = { }, s = { };

		s[type] = valueToStore;

		t.s = s;

		if ( o.hasOwnProperty( 'm' ) ) {

			if ( !o.m[level] ) {

				o.m[level] = t;
			} else {

				o.m[level].s = s;
			}
		} else {
			var m = [ ];

			m.push( t );
			o.m = m;
		}
	} else if ( type === 'clear' ) {

		if ( o.hasOwnProperty( 'm' ) ) {

			if ( o.m.length > 1 ) {

				if ( !o.m[level].c ) {

					o.m.splice( level, 1 );
				} else {

					delete o.m[level].s;
				}
			} else {

				delete o.m;
			}
		} else {

			o = { };
		}
	}

	newValue = typeof ( o ) && !$jq.isEmptyObject( o ) ? JSON.stringify( o ) : '';

	console.log( 'result:', newValue );
	return newValue;
}

function wppfm_storeConditionValue( sourceLevel, conditionLevel, currentMetaValue, newCondition ) {

	console.log( 'sourceLevel:', sourceLevel );
	console.log( 'conditionLevel:', conditionLevel );
	console.log( 'currentMetaValue:', currentMetaValue );
	console.log( 'newCondition:', newCondition );

	var newValue = '';
	var o = currentMetaValue ? JSON.parse( currentMetaValue ) : { };
	var conditionPos = conditionLevel - 1;
	var t = { };
	var q = { };

	if ( newCondition ) {

		if ( !o.hasOwnProperty( 'm' ) ) {

			var m = [ ];

			m.push( t );
			o.m = m;
		}

		if ( o.m[sourceLevel] && o.m[sourceLevel].hasOwnProperty( 'c' ) ) {

			q[conditionLevel] = newCondition;

			if ( o.m[sourceLevel].c.hasOwnProperty( conditionPos ) ) {

				o.m[sourceLevel].c[conditionPos] = q;
			} else {

				o.m[sourceLevel].c.push( q );
			}
		} else {

			var c = [ ];

			q[conditionLevel] = newCondition;

			c.push( q );

			t.c = c;

			o.m[sourceLevel].c = c;
		}
	}

	newValue = typeof ( o ) && !$jq.isEmptyObject( o ) ? JSON.stringify( o ) : '';

	console.log( 'result:', newValue );
	return newValue;
}

function wppfm_storeCombinedValue( sourceLevel, currentMetaValue, valueToStore ) {

	console.log( 'sourceLevel:', sourceLevel );
	console.log( 'currentMetaValue:', currentMetaValue );
	console.log( 'valueToStore:', valueToStore );

	var newValue = '';
	var o = currentMetaValue ? JSON.parse( currentMetaValue ) : { };

	if ( valueToStore ) {

		var m = [ ], t = { }, s = { };

		if ( !o.hasOwnProperty( 'm' ) ) {

			m.push( t );
			o.m = m;
		}

		if ( !o.m[sourceLevel].hasOwnProperty( 's' ) ) {

			s['source'] = 'combined';
			o.m[sourceLevel].s = s;
		}

		o.m[sourceLevel].s.f = valueToStore;
	}

	newValue = typeof ( o ) && !$jq.isEmptyObject( o ) ? JSON.stringify( o ) : '';

	console.log( 'result:', newValue );

	return newValue;
}

function wppfm_storeValueChange( sourceLevel, valueEditorLevel, valueToStore, action, currentMetaValue ) {

	console.log( 'sourceLevel:', sourceLevel );
	console.log( 'valueEditorLevel:', valueEditorLevel );
	console.log( 'valueToStore:', valueToStore );
	console.log( 'action:', action );
	console.log( 'currentMetaValue:', currentMetaValue );

	var newValue = '';
	var o = currentMetaValue ? JSON.parse( currentMetaValue ) : { };
	var t = { };

	if ( valueToStore && action === 'add' ) {

		t[sourceLevel + 1] = valueToStore;

		if ( o.hasOwnProperty( 'v' ) ) {

			if ( o.v[sourceLevel] && o.v[sourceLevel].hasOwnProperty( 'q' ) ) {

				o.v[sourceLevel][valueEditorLevel + 1] = valueToStore;
			} else {

				o.v[sourceLevel] = t;
			}
		} else {

			var v = [ ];

			v.push( t );
			o.v = v;
		}
	} else if ( action === 'clear' ) {

		if ( 'v' in o ) {

			if ( o.v.length > 1 ) {

				if ( !o.v[sourceLevel].q ) {

					o.v.splice( sourceLevel, 1 );
				} else {

					delete o.v[sourceLevel].s;
				}
			} else {

				delete o.v;
			}
		} else {

			o = { };
		}
	}

	newValue = typeof ( o ) && !$jq.isEmptyObject( o ) ? JSON.stringify( o ) : '';

	console.log( 'result:', newValue );

	return newValue;
}

function wppfm_removeCombinedValue( sourceLevel, combinedLevel, currentMetaValue ) {

	console.log( 'sourceLevel:', sourceLevel );
	console.log( 'combinedLevel:', combinedLevel );
	console.log( 'currentMetaValue:', currentMetaValue );

	var newValue = '';
	var o = currentMetaValue ? JSON.parse( currentMetaValue ) : { };

	if ( o.hasOwnProperty( 'm' ) && o.m[sourceLevel].hasOwnProperty( 's' ) && o.m[sourceLevel].s.hasOwnProperty( 'f' ) ) {

		if ( o.m[sourceLevel].s.f ) {

			var combinedValues = o.m[sourceLevel].s.f.split( '|' );

			combinedValues.splice( combinedLevel - 1, 1 );

			o.m[sourceLevel].s.f = wppfm_makeCombinedValuesStringFromArray( combinedValues );
		}
	}

	newValue = typeof ( o ) && !$jq.isEmptyObject( o ) ? JSON.stringify( o ) : '';

	console.log( 'result:', newValue );

	return newValue;
}

function wppfm_removeConditionValue( sourceLevel, conditionLevel, currentMetaValue ) {

	console.log( 'sourceLevel:', sourceLevel );
	console.log( 'conditionLevel:', conditionLevel );
	console.log( 'currentMetaValue:', currentMetaValue );

	var newValue = '';
	var o = currentMetaValue ? JSON.parse( currentMetaValue ) : { };

	if ( o && o.hasOwnProperty( 'm' ) && o.m[sourceLevel] && o.m[sourceLevel].hasOwnProperty( 'c' ) ) {

		if ( o.m[sourceLevel].c[conditionLevel] ) {

			if ( o.m[sourceLevel].c.length > 1 ) {

				// remove the correct condition
				o.m[sourceLevel].c.splice( conditionLevel, 1 );

				// and resort the remaining conditions
				o.m[sourceLevel].c = wppfm_resortObject( o.m[sourceLevel].c );
			} else {

				// check what is left in the mapping part
				var coi = wppfm_countObjectItems( o.m );

				if ( coi > 2 ) {

					o.m.splice( [ sourceLevel ], 1 );
				} else {

					if ( coi < 1 ) {

						if ( o.hasOwnProperty( 'v' ) ) {

							// when the object also has a v element only remove the m element
							delete o.m[sourceLevel];
						} else {

							// but when the object only has an m element then empty the object
							o = { };
						}
					} else {

						if ( o.m[sourceLevel].hasOwnProperty( 's' ) ) {

							// remove the condition
							delete o.m[sourceLevel].c;

							// and remove the source that was selected as the condition would be met
							o.m.splice( [ sourceLevel + 1 ], 1 );
						} else {

							if ( o.hasOwnProperty( 'v' ) ) {

								// when the object also has a v element only remove the m element
								delete o.m[sourceLevel];
							} else {

								// but when the object only has an m elemen then empty the object
								o = { };
							}
						}
					}
				}
			}
		}
	} else if ( o && o.hasOwnProperty( 'm' ) && o.m[sourceLevel] && o.m[sourceLevel].hasOwnProperty( 's' ) ) {

		var coi = wppfm_countObjectItems( o.m );

		if ( coi > 1 ) {

			o.m.splice( [ sourceLevel ], 1 );
		} else {

			if ( o.hasOwnProperty( 'v' ) ) {

				// when the object also has a v element only remove the m element
				delete o.m[sourceLevel];
			} else {

				// but when the object only has an m elemen then empty the object
				o = { };
			}
		}
	}

	newValue = typeof ( o ) && !$jq.isEmptyObject( o ) ? JSON.stringify( o ) : '';

	console.log( 'result:', newValue );

	return newValue;
}

function wppfm_removeQueryValue( sourceLevel, conditionLevel, currentMetaValue ) {

	console.log( 'sourceLevel:', sourceLevel );
	console.log( 'conditionLevel:', conditionLevel );
	console.log( 'currentMetaValue:', currentMetaValue );

	var newValue = '';
	var o = currentMetaValue ? JSON.parse( currentMetaValue ) : { };

	if ( o && o.hasOwnProperty( 'v' ) && o.v[sourceLevel] && o.v[sourceLevel].hasOwnProperty( 'q' ) ) {

		if ( o.v[sourceLevel].q[conditionLevel - 1] ) {

			if ( o.v[sourceLevel].q.length > 1 ) {

				// remove the correct condition
				o.v[sourceLevel].q.splice( ( conditionLevel - 1 ), 1 );

				// and resort the remaining conditions
				o.v[sourceLevel].q = wppfm_resortObject( o.v[sourceLevel].q );
			} else {

				// check what is left in the mapping part
				var coi = wppfm_countObjectItems( o.v );

				if ( coi > 2 ) {

					o.v.splice( [ sourceLevel ], 1 );
				} else {

					if ( coi < 1 ) {

						if ( o.hasOwnProperty( 'm' ) ) {

							// when the object also has an m element only remove the v element
							delete o.v[sourceLevel];
						} else {

							// but when the object only has a v elemen then empty the object
							o = { };
						}
					} else {

						if ( o.v[sourceLevel].hasOwnProperty( '1' ) ) {

							// remove the condition
							delete o.v[sourceLevel].q;
						} else {

							if ( o.hasOwnProperty( 'm' ) ) {

								// when the object also has an m element only remove the v element
								delete o.v[sourceLevel];
							} else {

								// but when the object only has an m elemen then empty the object
								o = { };
							}
						}
					}
				}
			}
		}
	}

	newValue = typeof ( o ) && !$jq.isEmptyObject( o ) ? JSON.stringify( o ) : '';

	console.log( 'result:', newValue );

	return newValue;
}

function wppfm_removeEditValuesValue( sourceLevel, valueEditorLevel, currentMetaValue ) {

	console.log( 'sourceLevel:', sourceLevel );
	console.log( 'valueEditorLevel:', valueEditorLevel );
	console.log( 'currentMetaValue:', currentMetaValue );

	var newValue = '';
	var o = currentMetaValue ? JSON.parse( currentMetaValue ) : { };

	var newValue = '';
	var o = currentMetaValue ? JSON.parse( currentMetaValue ) : { };

	if ( 'v' in o ) {

		if ( o.v.length > 1 ) {

			o.v.splice( sourceLevel, 1 );
		} else {

			delete o.v;
		}
	} else {

		o = { };
	}

	newValue = typeof ( o ) && !$jq.isEmptyObject( o ) ? JSON.stringify( o ) : '';

	console.log( 'result:', newValue );

	return newValue;

}

// 110416 obsolete
//function storeCustomCategoryValue( newCategory ) {
//    
//    console.log( newCategory );
//    
//    var newValue = '';
//    
//    var o = { };
//    
//    o.t = newCategory;
//    
//    newValue = typeof( o ) && ! $jq.isEmptyObject( o ) ? JSON.stringify( o ) : '';
//    
//    console.log( newValue );
//    return newValue;
//}

function wppfm_storeQueryValue( sourceLevel, queryLevel, currentMetaValue, newQuery ) {

	console.log( 'sourceLevel:', sourceLevel );
	console.log( 'queryLevel:', queryLevel );
	console.log( 'currentMetaValue:', currentMetaValue );
	console.log( 'newQuery:', newQuery );

	var newValue = '';
	var o = currentMetaValue ? JSON.parse( currentMetaValue ) : { };
	var conditionPos = queryLevel - 1;
	var t = { };
	var vq = { };

	if ( newQuery ) {

		if ( !o.hasOwnProperty( 'v' ) ) {

			var v = [ ];

			v.push( t );
			o.v = v;
		}

		if ( o.v[sourceLevel] && o.v[sourceLevel].hasOwnProperty( 'q' ) ) {

			vq[queryLevel] = newQuery;

			if ( o.v[sourceLevel].q.hasOwnProperty( conditionPos ) ) {

				o.v[sourceLevel].q[conditionPos] = vq;
			} else {

				o.v[sourceLevel].q.push( vq );
			}
		} else {

			var q = [ ];

			vq[queryLevel] = newQuery;

			q.push( vq );

			t.q = q;

			o.v[sourceLevel].q = q;
		}
	}

	newValue = typeof ( o ) && !$jq.isEmptyObject( o ) ? JSON.stringify( o ) : '';

	console.log( 'result:', newValue );
	return newValue;
}

function wppfm_makeCombinedValuesStringFromArray( combinedValuesArray ) {

	var combinedValuesString = '';

	for ( var i = 0; i < combinedValuesArray.length; i++ ) {

		combinedValuesString += combinedValuesArray[i];
		combinedValuesString += i < ( combinedValuesArray.length - 1 ) ? '|' : '';
	}

	return combinedValuesString;
}

function changeFeedFilterValue( workValue, newValues, changedFilterLevel ) {

	console.log( 'workValue:', workValue );
	console.log( 'newValues:', newValues );
	console.log( 'changedFilterLevel:', changedFilterLevel );

	if ( workValue ) {
		var filterObject = JSON.parse( workValue[0]['meta_value'] );
	} else {
		var filterObject = [ ];
		
		workValue = [ ]; // build a new empty workValue
		var m = { 'meta_value' : '' };
		workValue.push(m);
	}
	
	var nrFilters = filterObject.length;

	var newValueString = newValues[0] + '#' + newValues[1] + '#' + newValues[2];
	newValueString += newValues[2] !== '4' && newValues[2] !== '5' ? '#' + newValues[3] : '';

	if ( changedFilterLevel <= nrFilters ) {
		filterObject[changedFilterLevel - 1][changedFilterLevel] = newValueString;
	} else {

		var n = { };

		n[changedFilterLevel] = newValueString;
		//filterObject.push( n );
		filterObject[changedFilterLevel - 1] = n;
	}

	workValue[0]['meta_value'] = JSON.stringify( filterObject );

	console.log( 'result:', JSON.stringify( workValue ) );

	return workValue;
}

function removeFeedFilterLevel( workValue, levelToRemove ) {

	console.log( 'workValue:', workValue );
	console.log( 'levelToRemove:', levelToRemove );

	var filterObject = workValue ? JSON.parse( workValue[0]['meta_value'] ) : { };
	var returnFilterObject = [ ];
	var i = 0;

	for ( var key in filterObject ) {
		
		if ( parseInt( key ) !== levelToRemove - 1 ) {

			var v = { };
			v[i+1] = filterObject[key][parseInt(key)+1];
			
			returnFilterObject[i] = v;

			i++;
		}
	}

	if ( returnFilterObject.length > 0 ) {
		workValue[0]['meta_value'] = JSON.stringify( returnFilterObject );
		console.log( 'result:', JSON.stringify( workValue ) );
	} else {
		workValue = '';
		console.log( 'result:', workValue );
	}

	return workValue;
}