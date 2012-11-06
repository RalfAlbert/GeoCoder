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

class Geocoder_Options_Page extends GeoCoder_Admin
{
	/**
	 * Hook for the menupage
	 * @var	string
	 */
	protected $pagehook = '';

	/**
	 * Constructor
	 * Add action for inserting the options page
	 */
	public function __construct(){

		add_action( 'admin_menu', array( &$this, 'add_options_page' ) );

	}

	/**
	 * Add a option page to the admin menu
	 */
	public function add_options_page(){

		$this->pagehook = add_options_page(
			__( 'Geocoder', self::LANG ),
			'Geocoder',
			'manage_options',
			'geocoder',
			array( &$this, 'options_page' )
		);

		add_action( 'load-' . $this->pagehook, array( &$this, 'register_styles' ) );
		add_action( 'load-' . $this->pagehook, array( &$this, 'admin_add_help_tab' ) );

	}

	/**
	 * Optionspage output
	 */
	public function options_page(){

		if( isset( $_REQUEST['option'] ) && 'update' === $_REQUEST['option'] )
			$this->do_update_data();

		$opts = self::get_options( 'home' );
		$checked = ' checked="checked"';

		$field_vals = array(

			'title'				=> 'GeoCoder',
			'home'				=> __( 'Home', self::LANG ),
			'home_desc'			=> __( 'Setup yout homeplace and geolocation', self::LANG ),

			'key_title'			=> __( 'Api-Key', self::LANG ),
			'key_desc'			=> __( 'Please refer to the help tab in the upper right', self::LANG ),
			'key_label'			=> __( 'Google-Api-Key', self::LANG ),
			'key_text'			=> esc_html( __( 'Get your key here', self::LANG ) ),
			'key_link'			=> esc_url( self::GOOGLE_API_KEY_URL ),

			'rss_title'			=> __( 'Options for RSS-Feed', self::LANG ),
			'rss_desc'			=> __( 'GeoCoder can add geocoding information to the feeds in various formats. Select the format for your needings.', self::LANG ),
			'rss_geo'			=> 'Geo',
			'rss_geo_checked'	=> ( ! empty( $opts['rss_geo'] ) ) ? $checked : '',
			'rss_geo_hint'		=>	__( '<strong>Attention!</strong>', self::LANG ) .
									__( ' Your feed will not be longer valid if the geo-namespace is used!', self::LANG ),
			'rss_icbm'			=> 'ICBM',
			'rss_icbm_checked'	=> ( ! empty( $opts['rss_icbm'] ) ) ? $checked : '',
			'rss_geourl'		=> 'GeoURL',
			'rss_geourl_checked'=> ( ! empty( $opts['rss_geourl'] ) ) ? $checked : '',

			'map_options'		=> __( 'Map Options', self::LANG ),
			'map_desc'			=> __( 'Setup the default values for displaying maps.', self::LANG ),

			'map_small'			=> __( 'Small', self::LANG ),
			'map_medium'		=> __( 'Medium', self::LANG ),
			'map_large'			=> __( 'Large', self::LANG ),

			'size_desc'			=> __( 'Sizes (width x height)', self::LANG ),

			'type_zoom_title'	=> __( 'Default Type & Zoom', self::LANG ),
			'map_type_desc'		=> __( 'Maptype', self::LANG ),
			'map_zoom_desc'		=> __( 'Zoom', self::LANG ),

			'def_mapsize_title'	=> __( 'Default Size', self::LANG ),
			'def_mapsize_desc'	=> __( 'Mapsize', self::LANG ),

			'static_maps_title'	=> __( 'Static Maps', self::LANG ),
			'static_maps_desc'	=> __( 'Use static maps', self::LANG ),
			'static_maps_checked'	=> ( ! empty( $opts['static_maps'] ) ) ? $checked : '',

			'btn_update'		=> __( 'Save', self::LANG ),
		);

		$values = array_merge( $field_vals, $this->get_shared_form_fields( 'home' ) );

		if( $mapsizes = self::get_options( 'mapsizes' ) ){

			foreach( self::get_options( 'mapsizes' ) as $name => $size ){
				$sizes = explode( 'x', $size );
				$values["map_{$name}_x_val"] = $sizes[0];
				$values["map_{$name}_y_val"] = $sizes[1];
			}

		}


		$values['def_mapsize_options'] = '';
		$sel_ = self::get_options( 'def_mapsize' );

		if( $def_mapsizes = self::get_options( 'def_mapsizes' ) ){

			foreach( $def_mapsizes as $type => $size ){
				$selected = ( $sel_ == $type ) ?
				' selected="selected"' : '';

				$size_name = ucfirst( $type );

				$values['def_mapsize_options'] .= "<option value='{$type}'{$selected}>{$size_name}</option>";
			}

		}

		$values['map_type_options'] = '';
		$sel_ = self::get_options( 'def_maptype' );

		foreach( self::get_options( 'def_maptypes' ) as $type ){
			$selected = ( $sel_ == $type ) ?
				' selected="selected"' : '';

			$values['map_type_options'] .= "<option value='{$type}'{$selected}>{$type}</option>";
		}


		$values['map_zoom_options'] = '';
		$sel_ = self::get_options( 'def_mapzoom' );
		for( $i = 21; $i >= 0; $i-- ){
			$selected = ( $sel_ == $i ) ?
				' selected="selected"' : '';

			$values['map_zoom_options'] .= "<option value='{$i}'{$selected}>{$i}</option>";
		}

		wp_enqueue_style( 'geco_backend_style' );

		wp_enqueue_script( 'geco_backend_script' );

		self::$view->view( 'options_page', $values );

	}

	/**
	 * Add a help tab to the options page
	 */
	public function admin_add_help_tab(){

		$screen = get_current_screen();

		if ( $screen->id != $this->pagehook )
			return;

		$lang = get_locale();
		$fileformat = '%s/languages/help_%s.html';
		$filename = sprintf( $fileformat, self::$abspath, $lang );

		if( ! file_exists( $filename ) )
			$filename = sprintf( $fileformat, self::$abspath, 'en-EN' );

		$help_content = file_get_contents($filename);


		// Add my_help_tab if current screen is My Admin Page
		$screen->add_help_tab(
			array(
				'id'	=> 'plugin_help_tab',
				'title'	=> 'GeoCoder',
				'content'	=> $help_content
		    )
		);

	}

	/**
	 * Updateting/saving the data from options page
	 * @return	boolean		anonymous	Always return true
	 */
	protected function do_update_data(){

		if( FALSE == $this->check_nonce() )
			return;

		$filters = array(
			'rss_geo'		=> FILTER_VALIDATE_BOOLEAN,
			'rss_icbm'		=> FILTER_VALIDATE_BOOLEAN,
			'rss_geourl'	=> FILTER_VALIDATE_BOOLEAN,
			'static_maps'	=> FILTER_VALIDATE_BOOLEAN,
			'apikey'		=> FILTER_SANITIZE_STRING,
			'map_small_x'	=> FILTER_SANITIZE_NUMBER_INT,
			'map_small_y'	=> FILTER_SANITIZE_NUMBER_INT,
			'map_medium_x'	=> FILTER_SANITIZE_NUMBER_INT,
			'map_medium_y'	=> FILTER_SANITIZE_NUMBER_INT,
			'map_large_x'	=> FILTER_SANITIZE_NUMBER_INT,
			'map_large_y'	=> FILTER_SANITIZE_NUMBER_INT,
			'def_mapsize'	=> FILTER_SANITIZE_STRING,
			'def_maptype'	=> FILTER_SANITIZE_STRING,
			'def_mapzoom'	=> FILTER_SANITIZE_NUMBER_INT
		);

		// preset the dull checkboxes. if a checkbox is NOT checked, than NO value will be set in the array
		$cb_defaults = array(
			'rss_geo'		=> FALSE,
			'rss_icbm'		=> FALSE,
			'rss_geourl'	=> FALSE,
			'static_maps'	=> FALSE
		);

		$_REQUEST['geco'] = array_merge( $cb_defaults, $_REQUEST['geco'] );

		$options = array_merge( self::$options, $_REQUEST['geco'] );
		$options = filter_var_array( $_REQUEST['geco'], $filters );

		$def_sizes = array();
		foreach( self::get_options( 'def_mapsizes' ) as $name => $size ){

			if( ! empty( $options["map_{$name}_x"] ) && ! empty( $options["map_{$name}_y"] ) )
				$def_sizes[$name] = $options["map_{$name}_x"] . 'x' . $options["map_{$name}_y"];
			else
				$def_sizes[$name] = $size;

			unset( $options["map_{$name}_x"], $options["map_{$name}_y"] );

		}

		$options['mapsizes'] = $def_sizes;

		$options['do_rss'] = FALSE;
		foreach( $cb_defaults as $key => $cb )
			if( TRUE === $options[$key] )
				$options['do_rss'] = TRUE;

		self::set_options( 'home', $options );

		printf( '<div class="updated fade">%s</div>', __( 'Options updated', self::LANG ) );

		return TRUE;
	}

}