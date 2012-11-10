/**
 * JavaScript for GeoCoder
 * Depending on jQuery and Google Maps API v3
 *
 *  @author Ralf Albert
 *  @version 1.1
 */

var GeoCoderFrontend, GeoCoder, gm;

if( 'undefined' === jQuery || 'undefined' === google ){
	var google = null;
	var jQuery = null;
}

if( null !== ( jQuery && google ) ){
	jQuery( document ).ready( function($){ GeoCoderFrontend.init(); } );
}


( function( $ ){

	GeoCoderFrontend = {

		list:	null,
		image:	null,
		shadow:	null,
		shape:	null,

		/**
		 * Initialize the GeoCoderMapObject
		 *
		 * @param	none
		 * @return	void
		 */
		init: function(){

			gm		= this;
			gm.list	= null;

			var icon = $.parseJSON( GeoCoder.gmap_icon );

			gm.image = new google.maps.MarkerImage(
					icon.icon_url,
					new google.maps.Size( icon.icon_size.width, icon.icon_size.height ),
					new google.maps.Point( icon.icon_origin.x, icon.icon_origin.y ),
					new google.maps.Point( icon.icon_anchor.x, icon.icon_anchor.y )
			);

			gm.shadow = new google.maps.MarkerImage(
					icon.shadow_url,
					new google.maps.Size( icon.shadow_size.width, icon.shadow_size.height ),
					new google.maps.Point( icon.shadow_origin.x, icon.shadow_origin.y ),
					new google.maps.Point( icon.shadow_anchor.x, icon.shadow_anchor.y )
			);

			// the clickabel area, defined by the origin and width/height of the icon
			gm.shape = {
				coord: [
						icon.icon_origin.x, icon.icon_origin.y,
						icon.icon_origin.x, icon.icon_size.height,
						icon.icon_size.width, icon.icon_size.height,
						icon.icon_size.width , icon.icon_origin.y
						],
				type: 'poly'
			};


			$( '.geco_dynamic_map' ).each(
					function(){

						var elem = $( this );

						if( '' !== gm.getLatlng( elem ) ){
							gm.geocodeLatlng( elem );
						} else {
							gm.geocodeAddress( elem );
						}

					}
				);

		},

		/**
		 * Display an error-message in the given DOM element
		 *
		 * @param	object	element		DOM element
		 * @param	string	message		Message to display
		 */
		showError: function( element, message ){

			if( typeof message === 'undefined' ){
				message = 'No error message was given';
			}

			$( element ).html( '<p>GoogleMaps failed with the following reason:<br><strong>' + message + '</strong></p>' );

		},

		/**
		 * Returns the ID of the given element
		 *
		 * @param	object	element		DOM element
		 * @returns	string	anonymous	ID of the given DOM object
		 */
		getID: function( element ){

			var id = $( element ).attr( 'ID' );

			if( '' !== id ){
				return id;
			} else {
				gm.showError( element, 'Element has no ID' );
			}

		},

		/**
		 * Returns the stored address
		 *
		 * @param	object	element		DOM element
		 * @returns	string	anonymous	Stored address or empty string if no address is stored
		 */
		getAddress: function( element ){

			return $( element ).attr( 'data-addr' );

		},

		/**
		 * Returns stored latitude and longitude as string
		 *
		 * @param	object	element		DOM element
		 * @returns	string	anonymous	String with comma seperated latitude and longitude or empty string if no data is stored
		 */
		getLatlng: function ( element){

			return element.attr( 'data-latlng' );

		},

		/**
		 * Returns the latitude and longitude as object
		 *
		 * @use		getLatlng()
		 * @param	object	element		DOM element with latitude/longitude
		 * @returns	object	anonymous	Object with latitude/longitude. Null on error
		 */
		getLatlngObject: function( element ){

			var latlngStr = gm.getLatlng( element ).split( ",", 2 );

			return ( latlngStr.length < 2 ) ?
				null : { lat: parseFloat( latlngStr[0] ), lng: parseFloat( latlngStr[1] ) };

		},

		/**
		 * Returns object for displaying the map based on stored data
		 *
		 * @param	object	element		DOM element with stored data
		 * @param	object	latlng		Object with latitude and longitude
		 * @returns	object	anonymous	Object with stored data
		 */
		getMapOptions: function( element, latlng ){

			if( typeof latlng !== 'object' ){
				gm.showError( element, 'LatLng object is not type of object' );
			}

			var data = $( element );

			var zoom = parseInt( data.attr( 'data-zoom' ), 10 );

			if( typeof zoom === 'undefined' ){
				zoom = 16;
			}

			var maptype = data.attr( 'data-maptype' ).toUpperCase();

				if( false === ( maptype in google.maps.MapTypeId ) ){
					maptype = 'ROADMAP';
				}

			return {
						zoom:				zoom,
						center:				new google.maps.LatLng( latlng.lat, latlng.lng ),
						mapTypeId:			google.maps.MapTypeId[maptype],

						panControl:			true,
						zoomControl:		true,
						mapTypeControl:		true,
						scaleControl:		true,
						streetViewControl:	false,
						overviewMapControl:	true
/*
						mapTypeControlOptions: {
							style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
						}
*/
			};

		},

		/**
		 * Determines if a map is a general map
		 * @param	object	element		DOM element
		 * @return	bool	anonymous	True if the map is a general map, false if not
		 */
		isGeneralMap: function( element ){

			var flag =  $( element ).attr( 'data-generalmap' );

			return ( 1 === parseInt( flag, 10 ) );

		},

		/**
		 * Create and display a map based on latitude and longitude
		 *
		 * @uses	getMapOptions()
		 * @param	object	element		DOM element which keeps the data for the map and display the map
		 */
		geocodeLatlng: function( element, latlng ){

			if( 'undefined' === typeof latlng ){

				latlng = gm.getLatlngObject( element );

			}

			if( null !== latlng ){

				google.maps.event.addDomListener( window, 'load', function() {

					var map = new google.maps.Map( document.getElementById( gm.getID( element ) ), gm.getMapOptions( element, latlng ) );

					var infoWindow = new google.maps.InfoWindow();

					var onMarkerClick = function() {

						var marker = this;
						var contentString = gm.getContentString( marker.data );

						infoWindow.setContent( contentString );
						infoWindow.open(map, marker);

					};

					// add auto close
					google.maps.event.addListener(map, 'click', function() {
						infoWindow.close();
					});

					// get markers
					var markers = gm.getMarkers( latlng, element );

					// markers with event listeners
					for( var i in markers ){

						if( 'undefined' !== typeof markers[i] ){

							var marker = gm.getMarker( map, markers[i] );

							google.maps.event.addListener( marker, 'click', onMarkerClick );

						}
					}

				});

			} else {

				gm.showError( element, 'Map is not a general map and can not extract latitude/longitude from stored data' );

			}

		},


		/**
		 * Create and display a map based on stored address. Geocodes the address first
		 *
		 * @uses	getMapOptions()
		 * @param	object	element		DOM element which keeps the stored data and display the map
		 */
		geocodeAddress: function( element ){

			var address = gm.getAddress( element );

			if( '' !== address ){

				var geocoder = new google.maps.Geocoder();

				geocoder.geocode(
					{ 'address': address },

					function( results, status ){

						if( status === google.maps.GeocoderStatus.OK ){
							var latlng = { lat: results[0].geometry.location.Ya, lng: results[0].geometry.location.Za };
							gm.geocodeLatlng( element, latlng );
						} else {
							gm.showError( element, status );
						}

					}
				);
			}

		},

		/**
		 * Get LatLng-Coord via ajax from WordPress
		 */
		ajaxMultiMarker: function(){

			jQuery.ajax({
				type:		'POST',
				url:		GeoCoder.ajaxurl,
				data:		{'action': 'geco_multimarker', '_ajax_nonce': GeoCoder.nonce, 'async': false },
				success:	function( result ){ gm.list = result; },
				async:		false

			});

		},

		/**
		 * Add one or more marker(s) to the map
		 * @param	object	map		GoogleMap object
		 * @param	object	latlng	LatLong-object
		 * @param	object	element	DOM element
		 */
		getMarkers: function( latlng, element ){

			var markers;

			if( true === gm.isGeneralMap( element ) ){

				gm.ajaxMultiMarker();

				markers = $.parseJSON( gm.list );

			} else {

				markers			= { 'A': latlng };
				markers.A.title	= '';
				markers.A.text	= '';
				markers.A.link	= '';

			}

			return markers;

		},

		/**
		 * Create a single map marker
		 * @param	object	map		Map object
		 * @param	object	data	Marker data
		 */
		getMarker: function( map, data ){

			var marker = new google.maps.Marker({
				map:		map,
				position:	new google.maps.LatLng( data.lat, data.lng ),
				shadow:		gm.shadow,
				icon:		gm.image,
				shape:		gm.shape
			});

			if( '' !== data.title ){

				marker.title = data.title;

			}

			marker.data = data;

			return marker;

		},

		/**
		 * Return content string for infowindow
		 * @param	object	data	Data object
		 */
		getContentString: function( data ){

			var contentString = '';

			if( 'undefinde' !== typeof data ) {

				if( '' !== data.title ){

					contentString = '<div id="content">'+
						'<div id="siteNotice">'+
						'</div>'+
						'<h1 id="firstHeading" class="firstHeading">'+data.title+'</h1>'+
						'<div id="bodyContent">'+
						'<p>'+data.text+'</p>'+
						'<p><a href="'+data.link+'">'+GeoCoder.readmore+'</a>'+
						'</div>'+
						'</div>';


				} // end if data.title

			} // end typeof

			return contentString;

		}

	};

})( jQuery );
