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

class GeoCoder
{
	/**
	 *
	 * Table name
	 * @var string
	 */
	const TABLE_NAME = 'geocoder';

	/**
	 *
	 * Key for options
	 * @var string
	 */
	const OPTION_KEY = 'geocoder';

	/**
	 *
	 * Key for post meta
	 * @var string
	 */
	const META_KEY = 'geocoder';

	/**
	 *
	 * Values for creating nonces
	 * @var string
	 */
	const NONCE_NAME = 'geocoder_nonce';
	const NONCE_SAVE = 'geocoder_save_data';
	const NONCE_AJAX = 'geocoder_ajax_getdata';

	/**
	 *
	 * Plugin version
	 * @var string
	 */
	const VERSION = '2.0';

	/**
	 *
	 * Textdomain
	 * @var string
	 */
	const LANG = 'geocoder';

	/**
	 * Constants for GoogleMpas
	 * @var	string	various	Various API-links
	 */
	const GOOGLE_API_KEY_URL	= 'https://developers.google.com/maps/documentation/javascript/tutorial#api_key';
	const GOOGLE_MAPS_API_URL	= 'https://maps.google.com/maps/api/js?sensor=false';
	const GOOGLE_STATIC_MAP_URL	= 'http://maps.google.com/maps/api/staticmap?';
	const GOOGLE_MAPS_LINK_URL	= 'https://maps.google.com/maps?';

	/**
	 * Absolute path
	 * @var	string	$abspath
	 */
	protected static $abspath	= '';

	/**
	 * Basename of index.php
	 * @var	string	$file
	 */
	protected static $file		= '';

	/**
	 * Template engine
	 * @var	object	$view
	 */
	protected static $view = NULL;

	/**
	 * Default options
	 * @var	array	$options
	 */
	protected static $options =
		array(
			'do_rss'		=> FALSE,
			'rss_geo'		=> FALSE,
			'rss_icbm'		=> FALSE,
			'rss_geourl'	=> FALSE,
			'apikey'		=> '',
			'static_maps'	=> FALSE,

			'def_mapsize'		=> 'medium',
			// default (basic) mapsizes available after plugin activation
			'def_mapsizes'	=>
				array(
					'small'		=> '300x200',
					'medium'	=> '400x200',
					'large'		=> '500x300'
				),
			// array for customized mapsizes
			'mapsizes'		=>
				array(
					'small'		=> '300x200',
					'medium'	=> '400x200',
					'large'		=> '500x300'
				),

			'def_mapformat'		=> 'png',
			'def_mapformats'	=> array( 'png', 'jpg', 'gif' ),

			'def_maptype'		=> 'roadmap',
			'def_maptypes'		=> array( 'roadmap', 'satellite', 'terrain', 'hybrid' ),

			'def_mapzoom'		=> 15
		);

	/**
	 * GitHub Update Routine
	 * @var	object
	 */
	public static $updater = NULL;

	/**
	 *
	 * Constructor, initialize and start the plugin
	 */
	public static function plugin_construct( $abspath = '' ){

		self::$abspath	= $abspath;
		self::$file		= sprintf( '%s/index.php', $abspath );

		self::$view = new GeoCoder_View( self::$abspath . '/templates' );

		if( TRUE == self::get_options( 'do_rss' ) ){

			$rss_class = 'GeoCoder_RSS';
			add_filter( 'rss2_item',	array( &$rss_class, 'insert_geotags' ) );
			add_filter( 'rdf_item',		array( &$rss_class, 'insert_geotags' ) );
			add_filter( 'atom_entry',	array( &$rss_class, 'insert_geotags' ) );

			add_filter( 'rss2_ns',	array( &$rss_class, 'insert_namespaces' ) );
			add_filter( 'atom_ns',	array( &$rss_class, 'insert_namespaces' ) );
			add_filter( 'rdf_ns',	array( &$rss_class, 'insert_namespaces' ) );

		}

		load_plugin_textdomain(
			self::LANG,
			FALSE,
			self::$abspath . '/languages/'
		);

		// add ajax callbacks HERE. Adding ajax-callbacks in another place is to late. Damm WP
		self::add_ajax_callbacks();

		// switching between backend and frontend
		if( is_admin() ){

			self::update();

			new GeoCoder_Admin();

		} else {

			new GeoCoder_Frontend();

		}

	}

	/**
	 * Fetch updates from GitHub instead of WordPress.org
	 */
	protected function update(){

		$config = array(

				// required data
				'file'		=> self::$file,
				'user'		=> 'RalfAlbert',
				'repo'		=> 'GeoCoder',

				// optional data
				'requires'	=> '3.0',
				'tested'	=> '3.4',

		);

		self::$updater = new WP_GitHub_Updater( $config );

	}

	/**
	 * Wrapper for WP's get_option()
	 * @param	string|int	$mode		If $mode is a string, get_options() try to get the option. If $mode is a integer, it try to get the post_meta from the gven post-ID
	 * @return	mixed		$options	The option, post-meta or false on failure
	 */
	protected static function get_options( $mode ){

		$mode = strtolower( (string) $mode );
		$post_id = 0;

		if( ! key_exists( $mode, self::$options ) && 'home' !== $mode )
			$post_id = intval( $mode, 10 );

		$filter		= array( 'plugin_version', 'sensor' );

		$options	= 0 == $post_id ?
			get_option( self::OPTION_KEY ) :
			get_post_meta( $post_id, self::META_KEY, TRUE );

		// if a single option is requested and if it is set, return the single option directly
		if( key_exists( $mode, self::$options ) )
			return isset( $options[$mode] ) ? $options[$mode] : FALSE;

		// unset not needed values
		foreach( $filter as $key )
			if( isset( $options[$key] ) )
				unset( $options[$key] );

		return $options;

	}

	/**
	 * Wrapper for WP's update_option(). Set an option if $mode is a string. Or update the post-meta if $mode is an integer
	 * @param	string|int	$mode			Update/set an option if $mode is a string. Or update/set post-meta if $mode is an integer
	 * @param	mixed		$new_options	The new option/post-meta to be update/set
	 */
	protected static function set_options( $mode, $new_options ){

		if( ! is_string( $mode ) )
			$post_id = intval( $mode );

		// if a single option should be changed
		if( key_exists( $mode, self::$options ) )
			$new_options = array( $mode => $new_options );

		$old_options = is_string( $mode ) ?
			get_option( self::OPTION_KEY ) :
			(array) get_post_meta( $post_id, self::META_KEY, TRUE );

		$new_options = array_merge( $old_options, $new_options );

		is_string( $mode ) ?
			update_option( self::OPTION_KEY, $new_options ) :
			update_post_meta( $post_id, self::META_KEY, $new_options );

	}

	/**
	 *
	 * Shorten a given text to x words and paste dots at the end
	 * The function will REMOVE shortcodes and tags!
	 * @param	string	$text		Text to be shorten
	 * @param	integer	$num_words	(optional; default: 55) Number of words the text will be shorten to
	 * @param	string	$more		(optional; default: &hellip; ) Text/string to display at the end of the shorten text
	 */
	protected static function shorten_text( $text, $num_words = 55, $more = '&hellip;' ) {

		// remove HTML tags (do not remove breaks)
		$text = wp_strip_all_tags( $text, FALSE );

		// remove shortcodes etc.
		$text = preg_replace( '#\[.+\]#ius', '', $text );

		// split text into words. use space and line-ends as seperator
		$words_array = preg_split( "/[\n\r\t ]+/", $text, $num_words + 1, PREG_SPLIT_NO_EMPTY );

		if( count( $words_array ) > $num_words ){

			array_pop( $words_array );
			$text = implode( ' ', $words_array );
			$text = $text . $more;

		} else {

			$text = implode( ' ', $words_array );

		}

		return $text;
	}

	/**
	 * Adding ajax-callback functions with the WP-API
	 */
	protected static function add_ajax_callbacks(){

		add_action( 'wp_ajax_geco_multimarker',			array( 'GeoCoder_Frontend', 'get_ajax_multimarker' ), 99, 0 );
		add_action( 'wp_ajax_nopriv_geco_multimarker',	array( 'GeoCoder_Frontend', 'get_ajax_multimarker' ), 99, 0 );

	}

}
