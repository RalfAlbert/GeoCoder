<?php
/**
 * WordPress class to initialize a plugin
 *
 * PHP version 5.2
 *
 * @category   PHP
 * @package    WordPress
 * @subpackage GeoCoder
 * @author     Ralf Albert <me@neun12.de>
 * @license    GPLv3 http://www.gnu.org/licenses/gpl-3.0.txt
 * @version    0.1
 * @link       http://wordpress.com
 */

class Plugin_Initialisation
{
	/**
	 * 
	 * Caching for abspath
	 * @var string
	 */
	protected static $abspath = '';
	
	/**
	 * 
	 * Cache for calling file
	 * @var string
	 */
	protected static  $file = '';
	
	/**
	 * 
	 * Cache for callback to start the plugin after initialsation
	 * @var string|array
	 */
	protected static $function = FALSE;
	
	/**
	 * 
	 * Registering the plugin on plugins_loaded and starting the plugin on WordPress startup
	 * @internal hooked by plugins_loaded
	 */
	public static function init_plugin( $abspath = __FILE__, $function = FALSE ){

		self::$abspath = self::sanitize_abspath( $abspath );
		
		if( FALSE !== $function )
			self::$function = $function;

		self::init_autoloader( $abspath );
		
		self::init_plugin_install( $abspath );
		
		if( 1 > did_action( 'plugins_loaded' ) ){

			add_action(
				'plugins_loaded',
				array( __CLASS__, 'init_plugin' )
			);
			
			return TRUE;
			
		} else {
	
			call_user_func( self::$function, self::$abspath );
			
		}
		
		return NULL;
				
	}

	/**
	 * 
	 * Loads and initialize the autoloader
	 */
	public static function init_autoloader( $abspath = __FILE__ ){
		
		$abspath = self::sanitize_abspath( $abspath );

		if( ! class_exists( 'WP_Autoloader_V2' ) )
			require_once $abspath . '/lib/v2/class-wp_autoloader_v2.php';
		
		$config = array(  
				'abspath'			=> $abspath,
				'include_pathes'	=> array( 'lib', 'classes' ),
				'extensions'		=> '.php',
				'prefixes' 			=> array( 'class-', ),
		);
		
		WP_Autoloader_V2::init( $config );
		
	}

	/**
	 * 
	 * Register the activation-, deactivation- and uninstall-hook
	 */
	public static function init_plugin_install( $abspath ){

		self::init_autoloader( $abspath );
		
		register_activation_hook(	self::$file, array( 'GeoCoder_Installer', 'activate' ) );
		register_deactivation_hook(	self::$file, array( 'GeoCoder_Installer', 'deactivate' ) );
		register_uninstall_hook(	self::$file, array( 'GeoCoder_Installer', 'uninstall' ) );
		
	}

	/**
	 * 
	 * Check if $abspath is a string and a directory. If not, cast to string and/or set to dirname( __FILE__ )
	 * @param	string	$abspath	Absolute path to sanitize
	 * @return	string	$abspath	Sanitized absolute path/chached abspath
	 */
	protected static function sanitize_abspath( $abspath ){
		
		if( '' != self::$abspath )
			return self::$abspath;
		
		if( ! is_string( $abspath ) )
			$abspath = (string) $abspath;

		if( is_file( $abspath ) ){
			// save filename with path for later use
			self::$file = $abspath;
			$abspath = dirname( $abspath );
		}

		if( ! is_dir( $abspath ) )
			$abspath = dirname( __FILE__ );

		self::$abspath = $abspath;
			
		return $abspath;
		
	}
}