<?php
/**
 * WordPress-Plugin geocoding location maps
 *
 * PHP version 5.2
 *
 * @category   PHP
 * @package    WordPress
 * @subpackage Geocoder
 * @author     Ralf Albert <me@neun12.de>
 * @license    GPLv3 http://www.gnu.org/licenses/gpl-3.0.txt
 * @version    2.0
 * @link       http://wordpress.com
 */

class GeoCoder_Frontend extends GeoCoder
{

	/**
	 * Icons for the map
	 * @var	array	$gmap_icon
	 */
	public static $gmap_icon = array(

			'icon_url'		=> 'http://google.webassist.com/google/markers/flag/poppy.png', // original:  http://maps.google.com/mapfiles/marker.png (20x34)
			'icon_size'		=> array( 'width' => 31, 'height' => 35 ),
			'icon_origin'	=> array( 'x' => 0, 'y' => 0 ),
			'icon_anchor'	=> array( 'x' => 0, 'y' => 35 ),

			'shadow_url'	=> 'http://google.webassist.com/google/markers/flag/shadow.png', // original: http://maps.google.com/mapfiles/shadow50.png (37x34)
			'shadow_size'	=> array( 'width' => 31, 'height' => 35 ),
			'shadow_origin'	=> array( 'x' => 0, 'y' => 0 ),
			'shadow_anchor'	=> array( 'x' => 0, 'y' => 35 ),

	);

	/**
	 * Constructor
	*/
	public function __construct(){

		add_shortcode( 'glink',	array( &$this, 'glink_shortcode' ) );
		add_shortcode( 'gmap',	array( &$this, 'gmap_shortcode' ) );

		if( FALSE === self::get_options( 'static_maps') )
			add_action( 'init', array( __CLASS__, 'register_scripts' ), 1 );

		self::$gmap_icon = apply_filters( 'geocoder_gmap_icon', self::$gmap_icon );

	}

	/**
	 * Register and enqueue javascripts
	 */
	public static function register_scripts(){

		$apikey = self::get_options( 'apikey' );

		if( ! empty( $apikey ) )
			$gmaps_api_url = add_query_arg( 'key', $apikey, self::GOOGLE_MAPS_API_URL );
		else
			$gmaps_api_url = self::GOOGLE_MAPS_API_URL;

		wp_enqueue_script(
		'gmaps_api',
		$gmaps_api_url,
		FALSE,
		FALSE,
		TRUE
		);

		$script_ext = ( TRUE === WP_DEBUG ) ? '-dev.js' : '-min.js';

		wp_enqueue_script(
		'geco_frontend_script',
		plugins_url( 'js/frontend_script' . $script_ext, self::$file ),
		array( 'jquery', 'gmaps_api' ),
		FALSE,
		TRUE
		);


		/*
		 * This stylesheet MUST be included!
		* @see: http://stackoverflow.com/questions/7471830/google-maps-api-v3-weird-ui-display-glitches-with-screenshot
		*/
		wp_enqueue_style(
		'geco_frontend_css',
		plugins_url( 'css/frontend_style.css', self::$file ),
		false,
		false,
		'screen'
				);

		// ajaxurl for frontend ajax calls
		$data = array(
				'ajaxurl'			=> admin_url( 'admin-ajax.php' ),
				'nonce'				=> wp_create_nonce( self::NONCE_AJAX ),
				'gmap_icon'			=> json_encode( self::$gmap_icon ),
				'readmore'			=> __( 'Read More', self::LANG ),
		);

		wp_localize_script( 'geco_frontend_script', 'GeoCoder', $data );

	}

	/**
	 * Ajaxcallback to get the lonlat-data from all posts with a map
	 * @use	filter	int		geocoder_excerpt_num_words	Set the nukmber of words for the excerpt-length in gmap infowindows. Default is 55 words
	 * @use	filter	string	geocoder_excerpt_ends_with	Set the text/string that will append to the excerpt. Default is &hellip;
	 */
	public static function get_ajax_multimarker(){

		$nonce = filter_input( INPUT_POST, '_ajax_nonce', FILTER_SANITIZE_STRING );

		if( ! wp_verify_nonce( $nonce, self::NONCE_AJAX ) )
			die( '0' );

		global $wpdb, $post;

		$post_ids = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '%s';", self::META_KEY ), 0);

		$data = array();

		// setup values for shorten text
		$excerpt_num_words	= apply_filters( 'geocoder_excerpt_num_words', 55 );
		$excerpt_ends_with	= apply_filters( 'geocoder_excerpt_ends_with', '&hellip;' );

		foreach( $post_ids as $key => $post_id ){

			if( 'publish' === get_post_status( $post_id ) ){

				$post_data = get_post_meta( $post_id, self::META_KEY, TRUE );

				if( ! empty( $post_data['lat'] ) && ! empty( $post_data['lon'] ) ){

					$post = get_post( $post_id );

					$content = ( ! empty( $post->post_excerpt ) ) ? $post->post_excerpt : $post->post_content;

					$content = self::shorten_text( $content, $excerpt_num_words, $excerpt_ends_with );

					if( ! $content )
						$content = '';

					$dkey = chr( 65+$key );

					$data[$dkey] = array(
							'lat' 	=> $post_data['lat'],
							'lng' 	=> $post_data['lon'],
							'title' => get_the_title( $post_id ),
							'text'	=> $content,
							'link'	=> get_permalink( $post_id )
					);
				}
			}

		}

		die( json_encode( $data ) );

	}

	/**
	 * Callback for glink shortcode
	 * @param	array	$atts		Attributes from shrortcode
	 * @param	string	$content	Content from shortcode
	 * @return	string				String with link to GoogleMaps
	 */
	public function glink_shortcode( $atts, $content = NULL ){

		global $post;

		$geodata = self::get_options( $post->ID );

		// maybe no geodata are available for this post
		if( FALSE !== ( $nogeo = $this->check_no_geodata( $geodata ) ) )
			return $nogeo;

		// setup defaults
		$atts =
		shortcode_atts(
				array(
						'text'		=> 'GoogleMaps',
						'zoom'		=> self::get_options( 'def_mapzoom' ),
						'maptype'	=> 'street',
				),
				$atts
		);


		// get the urlencoded address (if available)
		$atts['addr'] = $this->get_urlencoded_address( $geodata );

		if( ! empty( $atts['addr'] ) )
			$atts['addr'] = '&amp;q=' . $atts['addr'];


		// get the urlencoded geo-location (if available)
		$atts['latlon'] = $this->get_urlencoded_latlon( $geodata );

		$switch = sprintf( '%b%b', empty( $atts['addr'] ), empty( $atts['latlon'] ) );

		switch( $switch ){

			// empty latlon
			case '01':
				$atts['latlon'] = '';
				break;

				// empty address
			case '10':
				$atts['latlon']	= '&amp;q=' . $atts['latlon'];
				$atts['addr']	= '';
				break;

				// address and latlon NOT empty
			case '00':
				$atts['latlon'] = '&amp;ll=' . $atts['latlon'];
				break;

				// both empty -> error
			case '11':
			default:
				return __( '[<strong>GeoCoder Error</strong>: Unable to convert address and/or geo-location]', self::LANG );
				break;
		}

		// prepare link-text for output
		$atts['text'] = esc_html( $atts['text'] );


		// convert the maptype from cleartext to url-param
		$atts['maptype'] = $this->get_urlencoded_maptype( $atts['maptype'] );


		// get the blog-language
		$atts['language'] = $this->get_language_att();


		//$base_url = 'https://maps.google.de/maps?';
		$base_url = rtrim( self::GOOGLE_MAPS_LINK_URL, '?' ) . '?';

		$atts['href'] = add_query_arg(
				array(
						'f'		=> 'q',
						'hl'	=> $atts['language'],
						't'		=> $atts['maptype'],
						'z'		=> $atts['zoom'],
						'om'	=> 1,
						'ie'	=> 'UTF8',

				),
				$base_url
		);

		$atts['href'] .= $atts['addr'] . $atts['latlon'];


		return self::$view->get_view( 'glink', $atts );


	}

	/**
	 * Callback for gmap shortcode
	 * @param	array	$atts		Attributes from shortcode
	 * @param	string	$content	Content from shortcode
	 * @return	string				String to include a GoogleMap
	 */
	public function gmap_shortcode( $atts, $content = NULL ){

		$this->save_extra_args( $atts );

		$this->setup_attributes_defaults( $atts );

		$this->sanitize_gmap_attributes( $atts );

		// return the last recognized error
		if( ! empty( $atts['error'] ) )
			return $atts['error'];

		$this->setup_query_args( $atts );

		return ( TRUE === $atts['static'] ) ?
		self::$view->get_view( 'gmap_static', $atts ) :
		self::$view->get_view( 'gmap_dynamic', $this->get_dynamic_mapdata( $atts ) );


	}

	/**
	 * Store options without an argument in attribute-array
	 * @param	array	$atts	Attributes from shortcode
	 */
	protected function save_extra_args( &$atts ){

			// save all extra-arguments without a value
		if( ! empty( $atts ) ){

			$allowed_extra_args = array( 'generalmap', 'static' );

			foreach( $atts as $key => $value ){

				$value = strtolower( trim( $value, ' ,;/' ) );

				if( is_int( $key ) ){

					// if arguments seperated by comma, split them into single arguments
					$arguments = explode( ',', $value );

					if( ! is_array( $arguments ) )
						$arguments = (array) $arguments;

					foreach( $arguments as $arg ){

						if( in_array( $arg, $allowed_extra_args ) ){
							$atts[ strtolower( $arg ) ] = TRUE;
							unset( $atts[$key] );
						}

					}

				}

			}
		}

	}

	/**
	 * Setup the defaults for the shortcode attributes
	 * @param	array	$atts	Attributes from shortcode
	 */
	protected function setup_attributes_defaults( &$atts ){

		$atts =
		shortcode_atts(
				array(
						'staticapi'		=> self::GOOGLE_STATIC_MAP_URL,
						'dynamicapi'	=> self::GOOGLE_MAPS_API_URL,
						'static'		=> self::get_options( 'static_maps' ),
						'text'			=> 'GoogleMaps',
						'center'		=> '',
						'zoom'			=> self::get_options( 'def_mapzoom' ),
						'size'			=> '',
						'width'			=> '',
						'height'		=> '',
						'format'		=> self::get_options( 'def_mapformat' ),
						'maptype'		=> self::get_options( 'def_maptype' ),
						'generalmap'	=> FALSE,
				),
				$atts
		);


		// clear errors
		$atts['error'] = '';

	}

	/**
	 * Sanitizing the shortcode attributes
	 * @param	array	$atts	Shortcode attributes
	 */
	protected function sanitize_gmap_attributes( &$atts ){

		global $post;

		$atts['geodata'] = self::get_options( $post->ID );

		if( FALSE !== ( $nogeo = $this->check_no_geodata( $atts['geodata'] ) ) && empty( $atts['center'] ) ){

			$atts['error'] = $nogeo;

			return;
		}

		// push center-attribute to geodata to prefer a given center over given adress/latlon
		if( ! empty( $atts['center'] ) )
			$atts['geodata']['center'] = $atts['center'];

		// get the location (address and/or latlon)
		$atts['center']	= $this->get_urlencoded_location_data( $atts['geodata'] );

		if( empty( $atts['center'] ) ){

			$atts['error'] = __( '[<strong>GeoCoder Error</strong>: Unable to convert address and/or geo-location]', self::LANG );

			return;
		}

		// convert old maptypes (numeric) to new maptypes (string)
		if( in_array( $atts['maptype'], range( 1, 4 ) ) ){

			$mt  = self::get_options( 'def_maptypes' );
			$atts['maptype'] = $mt[ (int) $atts['maptype'] - 1 ];
			unset( $mt );

		}

		// maptype (roadmap, satellite, hybrid, terrain)
		$atts['maptype'] = strtolower( $atts['maptype'] );
		$atts['maptype'] = ( in_array( $atts['maptype'], self::get_options( 'def_maptypes' ) ) ) ?
		$atts['maptype'] : 'roadmap';

		// image-format (png, gif, jpg, ...)
		$atts['format'] = strtolower( $atts['format'] );
		$atts['format'] = ( in_array( $atts['format'], self::get_options( 'def_mapformats' ) ) ) ?
		$atts['format'] : 'png';

		// get the blog-language
		$atts['language'] = $this->get_language_att();

		// zoom...
		$atts['zoom'] = intval( $atts['zoom'] );

		// get named sizes
		$sizes = self::get_options( 'mapsizes' );

		// get the size (width and height) from shortcode-attributes
		if( empty( $atts['size'] ) && ( ! empty( $atts['width'] ) && ! empty( $atts['height'] ) ) )
			$atts['size'] = "{$atts['width']}x{$atts['height']}";
		// select default named mapsize if no size-value is set
		elseif( empty( $atts['size'] ) && empty( $atts['width'] ) && empty( $atts['height'] ) )
		$atts['size'] = self::get_options( 'def_mapsize' );

		else
			$atts['size'] = strtolower( $atts['size'] );

		// select named size
		if( in_array( $atts['size'], array_keys( $sizes ) ) )
			$atts['img_size'] = $sizes[$atts['size']];
		else
			$atts['img_size'] = $atts['size'];

		// set dimensions
		$dims = explode( 'x', $atts['img_size'] );
		$atts['img_x'] = $dims[0];
		$atts['img_y'] = $dims[1];

		// positioning the marker
		$atts['markerpos'] = '%7C' . $this->get_urlencoded_location_data( $atts['geodata'] );

	}

	/**
	 * Setup the query
	 * @param	array	$atts	Shortcode attributes
	 */
	protected function setup_query_args( &$atts ){
		//TODO: language exceptions for some languages
		/*
		 'en-AU': 'ENGLISH (AUSTRALIAN)',
		'en-GB': 'ENGLISH (GREAT BRITAIN)',
		'pt-BR': 'PORTUGUESE (BRAZIL)',
		'pt-PT': 'PORTUGUESE (PORTUGAL)',
		'zh-CN': 'CHINESE (SIMPLIFIED)',
		'zh-TW': 'CHINESE (TRADITIONAL)'
		*/
		$query_args = array(
				'sensor'	=> 'false',
				'center'	=> $atts['center'],
				'maptype'	=> $atts['maptype'],
				'zoom'		=> $atts['zoom'],
				'format'	=> $atts['format'],
				'language'	=> $atts['language'],
				'size'		=> $atts['img_size'],
				'language'	=> $this->get_language_att(),
				'markers'	=> 'label:A' . $atts['markerpos']
		);

		$atts['imgurl'] = add_query_arg( $query_args, $atts['staticapi'] );

	}

	/**
	 * Prepare the data for dynamic maps
	 * @param	array	$atts		Shortcode attributes
	 * @return	array	$mapdata	Prepared data for dynamic maps
	 */
	protected function get_dynamic_mapdata( $atts = array() ){

		$mapdata				= array();

		// create a unique id for the html-element
		$mapdata['id']			= sprintf( '%03d', rand( 0,999 ) );

		$mapdata['img_x']		= $atts['img_x'];
		$mapdata['img_y']		= $atts['img_y'];

		$mapdata['zoom']		= $atts['zoom'];
		$mapdata['maptype']		= $atts['maptype'];
		$mapdata['generalmap']	= (int) $atts['generalmap']; //( isset( $atts['generalmap'] ) && TRUE === $atts['generalmap'] ) ? $this->get_generalmap_ids() : '';

		$mapdata['latlng']		= empty( $atts['geodata']['center'] ) ? $this->get_urlencoded_latlon( $atts['geodata'] ) : $this->get_center_formated( $atts['geodata']['center'] );
		$mapdata['addr']		= empty( $atts['geodata']['center'] ) ? $this->get_urlencoded_address( $atts['geodata'] ) : '';

		//$mapdata['script']		= $this->get_gmap_scriptblock( $mapdata );

		return $mapdata;

	}

	/**
	 * Returns urlencoded address or, if not available, urlencoded geolocation
	 * @param array $geodata
	 * @return string $location_urlencoded
	 */
	protected function get_urlencoded_location_data( $geodata = array() ){

		$location = '';

		// if the center-attribute is set, use this in priority
		if( isset( $geodata['center'] ) && ! empty( $geodata['center'] ) ){

			$location = $this->get_center_formated( $geodata['center'] );

			if( FALSE !== $location )
				return $location;

		}

		// first try to get an address, than try to get geolocation, than quit with error
		$location = $this->get_urlencoded_address( $geodata );
		if( empty( $location ) )
			$location = $this->get_urlencoded_latlon( $geodata );

		return $location;

	}

	/**
	 * Urlencode an address
	 * @param array $geodata
	 * @return string $addr_urlencoded
	 */
	protected function get_urlencoded_address( $geodata = array() ){

		$street = ! empty( $geodata['street'] ) ? urlencode( $geodata['street'] ) . ',+' : '';
		$zip	= ! empty( $geodata['zip'] ) ? urlencode( $geodata['zip'] ) . '+' : '';
		$city	= ! empty( $geodata['city'] ) ? urlencode( $geodata['city'] ) : '';

		return sprintf( '%s%s%s', $street, $zip, $city );

	}

	/**
	 * Urlencode latitude and longitude
	 * @param array $geodata
	 * @return string $latlon_urlencoded
	 */
	protected function get_urlencoded_latlon( $geodata = array() ){

		if( ! empty( $geodata['lat'] ) && ! empty( $geodata['lon'] ) )
			return sprintf( '%f,%f', $geodata['lat'], $geodata['lon'] );
		else
			return '';

	}

	/**
	 * Converts a cleartext maptype into url-param
	 * @param string $cleartext
	 * @return string $maptype_urlencoded
	 */
	protected function get_urlencoded_maptype( $cleartext = '' ){

		if( '' == $cleartext )
			return '';

		/*
		 * maptype
		* &t=
		* h = satelit
		* m = street
		* f = GoogleEarth
		*/

		$types = array(
				'geo'			=> 'h',
				'map'			=> 'm',
				'street'		=> 'm',
				'google-earth'	=> 'f',
				'satellite'		=> 'k',
				'roadmap'		=> 'm',
				'hybrid'		=> 'h',
				'terrain'		=> 'f'

		);

		$mt = 'm';

		foreach( array_keys( $types ) as $key ){

			$l = levenshtein( $cleartext, $key );
			$s = similar_text( $cleartext, $key );
			if( 0 == $s )
				$s = 1;

			if( ($l/$s) < 1 )
				$mt = $types[$key];
		}

		return $mt;

	}

	/**
	 *
	 * Returns the short-version of blog-language (de_DE -> de; en_EN -> en)
	 * @return string $language
	 */
	protected function get_language_att(){

		// fetch the blog-language and setup the url-param
		$lang = explode( '-', get_bloginfo( 'language' ) );
		return ( empty( $lang[0] ) ? 'en' : $lang[0] );

	}

	/**
	 * Returns a commaseperated list with post-IDs of all post which have a map
	 * @return	string	anonymous	Comma seperated list with post-IDs
	 */
	protected function get_generalmap_ids(){

		global $wpdb;

		return implode( ',', $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '%s';", self::META_KEY ), 0 ) );

	}

	/**
	 * Checks if some geodata (adress and/or longitude-latitude) was given
	 * @param	array		$geodata
	 * @return	string|bool	Error message if no geodata are present or FALSE if there are some geodata
	 */
	protected function check_no_geodata( $geodata = array() ){

		$values = '';

		foreach( array_values( (array) $geodata ) as $val )
			$values .= $val;

		return ( empty( $values ) ) ?
		__( '[<strong>GeoCoder Error</strong>: No geodata available for this post]', self::LANG ) : FALSE;

	}

	/**
	 * Checks if a center-attribute (string) have the right syntax (latitude-comma-longituc
	 * @param	string	$center		Comma seperated latitude and longitude as string
	 * @return	string|bool			Well formated latlon-string or false on error
	 */
	protected function get_center_formated( $center = '' ){

		$data = explode( ',', $center );

		if( sizeof( $data ) < 2 )
			return FALSE;

		return sprintf( '%f,%f', $data[0], $data[1] );

	}

}