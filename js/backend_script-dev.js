/**
 * JavaScript for GeoCoder
 * Depending on jQuery and Google Maps API v3
 *
 * @author Ralf Albert
 * @version 1.0
 */

var GeoCoderBackend, gcb, geocoderl10n, pagenow;

if( 'undefined' === jQuery || 'undefined' === google ){
	var google = null;
	var jQuery = null;
}

if( null !== ( jQuery && google ) ){
	jQuery( document ).ready( function($){ GeoCoderBackend.init(); } );
}


( function( $ ){

	GeoCoderBackend = {

			/**
			 * available html-fields
			 */
			fields: {
					msg:	$( '#geco_msg' ),
					lng:	$( '#geco_lon' ),
					lat:	$( '#geco_lat' ),
					zip:	$( '#geco_zip' ),
					city:	$( '#geco_city' ),
					street:	$( '#geco_street' )

			},

			geocoder: new google.maps.Geocoder(),

			/**
			 * Initialize the process and register click-events
			 *
			 */
			init: function(){

				gcb = this;

				$( '#geco_convert' ).click( function(){ gcb.switchConverter(); } );

				$( '#geco_clear_address' ).click( function(){
					gcb.fields.zip.val( '' );
					gcb.fields.city.val( '' );
					gcb.fields.street.val( '' );
				});

				$( '#geco_clear_latlng' ).click( function(){
					gcb.fields.lat.val( '' );
					gcb.fields.lng.val( '' );
				});


			},

			/**
			 * Switch between converting address-to-latlng and latlng-to-address
			 *
			 * @returns	null	Returns null if no data to convert available
			 */
			switchConverter: function(){

				var e = '';
				for( var f in gcb.fields ){
					if( 'undefined' !== typeof gcb.fields[f] ){
						e = e + gcb.fields[f].val();
					}
				}

				if( e.length < 1 ){
					gcb.fields.msg.text( geocoderl10n.error_no_data );
					return null;
				}

				var addr_data = {
						zip:	gcb.fields.zip.val(),
						city:	gcb.fields.city.val(),
						street:	gcb.fields.street.val()
				};

				var latlng_data = {
						lat:	gcb.fields.lat.val(),
						lng:	gcb.fields.lng.val()
				};

				if( ('' === latlng_data.lat) | ('' === latlng_data.lng) ){
					gcb.convertAddr( addr_data );
				} else {
					gcb.convertLatlng( latlng_data );
				}


			},

			/**
			 * Convert latitude and longitude to an address
			 *
			 * @param	object	latlng_data	Object with latitude and longitude
			 */
			convertLatlng: function( latlng_data ){

				gcb.fields.msg.text( geocoderl10n.please_wait );

				var latlng = new google.maps.LatLng(
						parseFloat( latlng_data.lat ),
						parseFloat( latlng_data.lng )
				);

				gcb.geocoder.geocode(
					{'latLng': latlng },

					function( results, status ){

						if( status === google.maps.GeocoderStatus.OK ) {

							var data = results[0].formatted_address.split( ",", 3 );
							var zipcity = $.trim( data[1] ).split( " " );

							gcb.fields.street.val( data[0] );
							gcb.fields.zip.val( zipcity[0] );
							gcb.fields.city.val( zipcity[1] );

							gcb.fields.msg.text( '' );

						}
						else {
							gcb.fields.msg.text( geocoderl10n.gmaps_err + status );
						}
				});

			},

			/**
			 * Convert an address to latitude and longitude
			 *
			 * @param	object	addr_data	Object with address-data
			 */
			convertAddr: function( addr_data ){

				var address = addr_data.street + ',' + addr_data.zip + ' ' + addr_data.city;

				gcb.geocoder.geocode(
					{ 'address': address },

					function( results, status ){

						if( status === google.maps.GeocoderStatus.OK ){

							var data = results[0].formatted_address.split( ",", 3 );
							var zipcity = $.trim( data[1] ).split( " " );

							gcb.fields.street.val( data[0] );
							gcb.fields.zip.val( zipcity[0] );
							gcb.fields.city.val( zipcity[1] );

							gcb.fields.lat.val( results[0].geometry.location.Ya );
							gcb.fields.lng.val( results[0].geometry.location.Za );

							if( 'settings_page_geocoder' === pagenow ){
								gcb.fields.msg.text( geocoderl10n.save_data_msg );} else {gcb.fields.msg.text( '' );
							}

						} else {
							gcb.fields.msg.text( geocoderl10n.gmaps_err + status );
						}
				});

			}

	};

} )( jQuery );