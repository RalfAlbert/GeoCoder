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
 * @version    2.1.0
 * @link       http://wordpress.com
 */

/**
 * Plugin Name:	Geocoder 2.0
 * Plugin URI:	http://yoda.neun12.de
 * Description:	This plugin adds maps to posts based on location-codes or adresses and geotag them. You will need an <a href="https://developers.google.com/maps/signup">API-Key from Google</a> to work with this plugin.
 * Version: 	2.1
 * Author: 		Ralf Albert
 * Author URI: 	http://yoda.neun12.de
 * Text Domain:	geocoder
 * Domain Path:	languages
 * Network:
 * License:		GPLv3
 */


! defined( 'ABSPATH' ) and die( "I'm sorry, Dave. I'm afraid I can't do that" );

if( ! class_exists( 'Plugin_Initialisation' ) )
	require_once dirname( __FILE__ ) . '/lib/v2/class-plugin_initialisation.php';

Plugin_Initialisation::init_plugin(
__FILE__,
array( 'GeoCoder','plugin_construct' )
);
