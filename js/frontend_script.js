/**
 * JavaScript for GeoCoder
 * Depending on jQuery and Google Maps API v3
 *
 *  @author Ralf Albert
 *  @version 1.0
 */

var GeoCoderFrontend, GeoCoder, gm, list;

if( 'undefined' === jQuery || 'undefined' === google ){
	var google = null;
	var jQuery = null;
}

if( null !== ( jQuery && google ) ){
	jQuery( document ).ready( function($){ GeoCoderFrontend.init(); } );
}


( function( $ ){

	GeoCoderFrontend = {

		/**
		 * Initialize the GeoCoderMapObject
		 *
		 * @param	none
		 * @return	void
		 */
		init: function(){

			gm = this;
			list = null;

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
		 * Add one or more marker(s) to the map
		 * @param	object	map		GoogleMap object
		 * @param	object	latlng	LatLong-object
		 * @param	object	element	DOM element
		 */
		addMarker: function( map, latlng, element ){

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

			gm.setMarkers( map, markers );

		},

		/**
		 * Add marker(s) to the map
		 * @param	object	map			Googloe map object
		 * @param	object	locations	Object with data for location & infowindow
		 */
		setMarkers: function( map, locations ){

			var icon = $.parseJSON( GeoCoder.gmap_icon );

			var image = new google.maps.MarkerImage(
					icon.icon_url,
					new google.maps.Size( icon.icon_size.width, icon.icon_size.height ),
					new google.maps.Point( icon.icon_origin.x, icon.icon_origin.y ),
					new google.maps.Point( icon.icon_anchor.x, icon.icon_anchor.y )
			);

			var shadow = new google.maps.MarkerImage(
					icon.shadow_url,
					new google.maps.Size( icon.shadow_size.width, icon.shadow_size.height ),
					new google.maps.Point( icon.shadow_origin.x, icon.shadow_origin.y ),
					new google.maps.Point( icon.shadow_anchor.x, icon.shadow_anchor.y )
			);

			// the clickabel area, defined by the origin and width/height of the icon
			var shape = {
				coord: [
						icon.icon_origin.x, icon.icon_origin.y,
						icon.icon_origin.x, icon.icon_size.height,
						icon.icon_size.width, icon.icon_size.height,
						icon.icon_size.width , icon.icon_origin.y
						],
				type: 'poly'
			};

			for( var i in locations ){

				if( 'undefinde' !== typeof locations[i] ) {

					var data = locations[i];
					var latlng = new google.maps.LatLng( data.lat, data.lng );

					var marker = new google.maps.Marker({
						position: latlng,
						map: map,
						shadow: shadow,
						icon: image,
						shape: shape,
						title: data.title
						//zIndex: i
					});

					if( '' !== data.title ){

						var contentString =
							'<div id="content">'+
							'<div id="siteNotice">'+
							'</div>'+
							'<h1 id="firstHeading" class="firstHeading">'+data.title+'</h1>'+
							'<div id="bodyContent">'+
							'<p>'+data.text+'</p>'+
							'<p><a href="'+data.link+'">'+GeoCoder.readmore+'</a>'+
							'</div>'+
							'</div>';

							var infowindow = new google.maps.InfoWindow({
								content: contentString
							});

							gm.addInfoWindow( map, marker, infowindow );
					} // end if data.title

				} // end typeof

			} // end for in locations
		},

		addInfoWindow: function( map, marker, infowindow ){

			google.maps.event.addListener( marker, 'click', function() {
				infowindow.open( map, marker );
			});

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
		geocodeLatlng: function( element ){

			var latlng = gm.getLatlngObject( element );

			if( null !== latlng ){

				var map = new google.maps.Map( document.getElementById( gm.getID( element ) ), gm.getMapOptions( element, latlng ) );

				gm.addMarker( map, latlng, element );

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

			var geocoder = new google.maps.Geocoder();

			geocoder.geocode(
					{ 'address': gm.getAddress( element ) },

					function( results, status ){

						if( status === google.maps.GeocoderStatus.OK ){

							var latlng = { lat: results[0].geometry.location.Xa, lng: results[0].geometry.location.Ya };
							var map = new google.maps.Map( document.getElementById( gm.getID( element ) ), gm.getMapOptions( element, latlng ) );

							gm.addMarker( map, latlng, element );

						} else {
							gm.showError( element, status );
						}

					});
		},

		ajaxMultiMarker: function(){

			jQuery.ajax({
				type:		'POST',
				url:		GeoCoder.ajaxurl,
				data:		{'action': 'geco_multimarker', '_ajax_nonce': GeoCoder.nonce, 'async': false },
				success:	function( result ){ gm.list = result; },
				async:		false

			});

		}

	};

})( jQuery );
