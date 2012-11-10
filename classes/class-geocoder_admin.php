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

class GeoCoder_Admin extends GeoCoder
{
	/**
	 * Constructor
	 * - Create an instance of the options-page class
	 * - Create an instance of the edit-form class
	 */
	public function __construct(){

		new GeoCoder_Options_Page();
		new GeoCoder_Edit_Form();

	}

	/**
	 * Register and enqueueing stylesheets
	 */
	public function register_styles(){

		wp_register_style(
		'geco_backend_style',
		plugins_url( 'css/backend_style.css', self::$file ),
		FALSE,
		FALSE,
		'screen'
				);

	}

	/**
	 * Register and enqueueing scripts
	 */
	public function register_scripts(){

		$apikey = self::get_options( 'apikey' );

		if( ! empty( $apikey ) )
			$gmaps_api_url = add_query_arg( 'key', $apikey, self::GOOGLE_MAPS_API_URL );
		else
			$gmaps_api_url = self::GOOGLE_MAPS_API_URL;

		wp_register_script(
		'gmaps_api',
		$gmaps_api_url,
		FALSE,
		FALSE,
		TRUE
		);

		$script_ext = ( TRUE === WP_DEBUG ) ? '-dev.js' : '-min.js';

		wp_register_script(
		'geco_backend_script',
		plugins_url( 'js/backend_script' . $script_ext, self::$file ),
		array( 'jquery', 'gmaps_api' ),
		FALSE,
		TRUE
		);

		$strings = array(
				'please_wait'	=> __( 'Please wait...', self::LANG ),
				'gmaps_err'		=> __( 'Geocode was not successful for the following reason: ', self::LANG ),
				'error_no_data'	=> __( 'No data to convert. All fields are empty!', self::LANG ),
		);

		wp_localize_script( 'geco_backend_script', 'geocoderl10n', $strings );
	}

	/**
	 * Check nonces
	 */
	protected function check_nonce(){

		$nonce = isset( $_REQUEST[self::NONCE_NAME] ) ?
		filter_var( $_REQUEST[self::NONCE_NAME], FILTER_SANITIZE_STRING ) : '';

		return wp_verify_nonce( $nonce, self::NONCE_SAVE );

	}

	/**
	 * Fields used either in post-screen and in options-screen
	 *
	 * @param string $mode
	 */
	protected function get_shared_form_fields( $mode ){

		if( empty( $mode ) )
			$mode = 'home';

		$opts = self::get_options( $mode );

		return array(

				'nonce_field'	=> wp_nonce_field( self::NONCE_SAVE, self::NONCE_NAME ),

				'zip'			=> __( 'ZIP', self::LANG ),
				'city'			=> __( 'City', self::LANG ),
				'street'		=> __( 'Street', self::LANG ),
				'zip_val'		=> ! empty( $opts['zip'] ) ? esc_attr( $opts['zip'] ) : '',
				'city_val'		=> ! empty( $opts['city'] ) ? esc_attr( $opts['city'] ) : '',
				'street_val'	=> ! empty( $opts['street'] ) ? esc_attr( $opts['street'] ) : '',

				'lon'			=> __( 'Longitude', self::LANG ),
				'lat'			=> __( 'Latitude', self::LANG ),
				'lon_val'		=> ! empty( $opts['lon'] ) ? esc_attr( $opts['lon'] ) : '',
				'lat_val'		=> ! empty( $opts['lat'] ) ? esc_attr( $opts['lat'] ) : '',

				'key_val'		=> ! empty( $opts['apikey'] ) ? esc_attr( $opts['apikey'] ) : '',

		);

	}

}