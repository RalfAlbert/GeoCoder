<?php
/**
 * 
 * WP-Autoloader
 * 
 * Easy to use autoloader
 * 
 * To overwrite $prefix, an empty string have to be passed to the autoloader
 * 
 * @author Ralf Albert (neun12@googlemail.com)
 * @version 0.3.1
 */

/*
 * Usage
 * =====
 * 
 * $autoloader = new WP_Autoloader( [$config] );
 * 
 * $config is optional
 * $config could be array or object
 * 
 * $config should look like this:
 * 
 * $config = new stdClass();
 * $config->abspath			= __FILE__;
 * $config->include_pathes	= array( '/lib', '/classes' );
 * $config->extension		= '.php';
 * $config->prefixes		= array( 'class-' );
 *  
 *  OR
 *  
 * $config = array(  
 * 		'abspath'			=> __FILE__,
 * 		'include_pathes'	=> array( 'models', 'views' ),
 * 		'extension'			=> '.php',
 * 		'prefixes' 			=> array( 'model-', 'view-' ),
 * );
 * 
 */

if( ! class_exists( 'WP_Autoloader_V2' ) ){

class WP_Autoloader_V2
{
	/**
	 * 
	 * Absolute path to include directory
	 * @var string
	 */
	public static $abspath = FALSE;
	
	/**
	 * 
	 * Relative path(es) to include directory
	 * @var array
	 */
	public static $include_pathes = array();
	
	/**
	 * 
	 * Extension for files
	 * @var string
	 */
	public static $extension = '.php';
	
	/**
	 * 
	 * Prefix for files
	 * @var array
	 */
	public static $prefixes = array();

	/**
	 * 
	 * Constructor for one-step-autoloading
	 * 
	 * @param array|object $config
	 */
	public function __construct( $config = NULL ){

		//check for usage with older versions
		$config = self::convert_older_versions_config( $config );
		
		self::init( $config );
		
	}
	
	public static function init( $config = NULL ){

		if( NULL === $config )
			$config = array();
						
		// casting object to array for array_merge
		$config = (array) $config;

		$defaults = array(
			'include_pathes'	=> self::$include_pathes,
			'extension'			=> self::$extension,
			'prefixes'			=> self::$prefixes 
		);

		extract( array_merge( $defaults, $config ) );
		
		if( isset( $abspath ) && is_string( $abspath ) );
			self::set_abspath( $abspath );
			
		self::autoloader_init( $include_pathes, $prefixes, $extension );
		
	}
	
	/**
	 * 
	 * Initialize the spl_autoloader
	 * Overwrite class-vars, set&sanitize include-path, checks if include-path was already set
	 * 
	 * @param array $include_path
	 * @param array $prefix
	 * @param string $extension
	 */
	public static function autoloader_init( $include_pathes = array(), $prefixes = array(), $extension = FALSE ){

		if( FALSE === self::$abspath )
			self::$abspath = dirname( __FILE__ );
		
		if( ! is_array( $include_pathes ) )
			$include_pathes = (array) $include_pathes;
			
		if( ! empty( $include_pathes ) )
			self::set_includepathes( $include_pathes );
			
		if( ! is_array( $prefixes ) )
			$prefixes = (array) $prefixes;
			
		if( ! empty( $prefixes ) )
			self::$prefixes = $prefixes;

		if( FALSE !== $extension )
			self::$extension = $extension;
  		
		/*
		 * From php.net/spl_autoload (http://de3.php.net/manual/de/function.spl-autoload.php)
		 * 
		 * 1. Add your class dir to include path
		 * 2. You can use this trick to make autoloader look for commonly used "My-class.php" type filenames
		 * 3. Use default autoload implementation or self defined autoloader 
		 */

		foreach( self::$include_pathes as $includepath ){

			$path = self::$abspath . DIRECTORY_SEPARATOR . $includepath;

			// check if the path have already been added to include_path
			$pathes = explode( PATH_SEPARATOR, get_include_path() );

			if( ! in_array( $path, $pathes ) )				
				// set our path at the first position. require, include, __autoload etc. start searching in the first path
				// with our custom path at the first, PHP does not have to search in all other pathes for our classes
				set_include_path( $path . PATH_SEPARATOR . get_include_path() );
			
		}

		self::register_extensions( self::$extension );

		spl_autoload_register( array( __CLASS__, 'autoload' ) );

	}
		
	/**
	 * 
	 * Callback for spl_autoload_register
	 * 
	 * @param string $class_name
	 */
	private static function autoload( $class_name ){

		// if a class-prefix is set, add it to the class-name		
		if( ! empty( self::$prefixes ) ){

			foreach( self::$prefixes as $prefix ){
				
				$test_class_name = $prefix . $class_name;
				
				try {
					spl_autoload( $test_class_name );
				} catch ( Exception $e ) {
					throw new WordPress_Exception( 'Class ' . $class_name . ' not found' );
				}
						
			}
				
		} else {
				try {
					spl_autoload( $class_name );
				} catch ( Exception $e ) {
					throw new WordPress_Exception( 'Class ' . $class_name . ' not found' );
				}
		}
		
	}
	
	/**
	 * 
	 * Set the absolute path to file
	 * 
	 * @param string $abspath
	 * @return bool True on success (abspath is an file or directory), false on fail
	 */
	public static function set_abspath( $abspath = FALSE ){
		
		if( FALSE != $abspath && is_string( $abspath ) ){
			
			if( is_file( $abspath ) )	
				self::$abspath = dirname( $abspath );

			elseif( is_dir( $abspath) )
				self::$abspath = $abspath;

			else
				return FALSE;

		}
		
		return TRUE;
		 
	}
	
	/**
	 * 
	 * Sanitize and add the include pathes to PHPs include-path
	 * 
	 * @param array $pathes
	 */
	public static function set_includepathes( $pathes = array() ){
		
		if( empty( $pathes ) )
			return FALSE;

		$sanitized_pathes = array();
		
		foreach( $pathes as $path ){

			if( is_string( $path ) ){
				
				// strip slashes and backslashes at the start and end of the string /classes/ -> classes; /lib/classes/ -> lib/classes
				$path = preg_replace( "#^[/|\\\]|[/|\\\]$#", '', $path );
				
				// replace slashes and backslashes with the OS specific directory seperator
				$path = preg_replace( "#[/|\\\]#", DIRECTORY_SEPARATOR, $path );

				if( is_dir( $path ) || is_dir( self::$abspath . '/' . $path ) )
					array_push( $sanitized_pathes, $path );

			}	
		}

		self::$include_pathes = $sanitized_pathes;
		
		unset( $sanitized_pathes );
		
	}

	/**
	 * 
	 * Register one or more extension for autoloading
	 * @param string|array $extensions
	 */
	public static function register_extensions( $extensions = NULL ){
		
		if( ! is_string( $extensions ) && ! is_array( $extensions ) )
			return FALSE;
		
		if( ! is_array( $extensions ) && is_string( $extensions ) )
			$extensions = (array) $extensions;
			
		if( empty( $extensions ) )
			$extensions = &self::$extension;
			
		foreach( $extensions as $extension )
			spl_autoload_extensions( $extension );
			
		return TRUE;
		
	}
	
	/**
	 * 
	 * Removes a single path from PHPs include-path and from the internal
	 * list of include-pathes
	 * 
	 * @param string $search_path Path to be removed from include-pathes
	 */
	public static function remove_includepath( $search_path ){
		
		$old_path = get_include_path();
		
		$pattern = sprintf(
			'/%s%s?/',
			str_replace( "\\", "\\\\", $search_path ),
			PATH_SEPARATOR
		);
				
		$new_path = preg_replace( $pattern, '', $old_path );
		
		set_include_path( $new_path );
		
		foreach( self::$include_pathes as $key => $path ){
			
			if( $path === $search_path )
				unset( self::$include_pathes[$key] );
				
		}
		
	}
	
	/**
	 * 
	 * Unregister the registered autoload-function and removes the added pathes from PHPs
	 * include-path
	 * 
	 */
	public static function reset(){
		
		spl_autoload_unregister( array( __CLASS__, 'autoload' ) );
		
		foreach( self::$include_pathes as $path )
			self::remove_includepath( $path );
		
	}
	
	public static function convert_older_versions_config( $config = array() ){
		
		if( empty( $config ) )
			return $config;
			
		if( isset( $config['include_path'] ) ){
			$config['include_pathes'] = (array) $config['include_path'];
			//trigger_error( 'Param include_path is deprecated. Use include_pathes instead', E_USER_NOTICE );
		}
			
		if( isset( $config['prefix'] ) ){
			$config['prefixes'] = (array) $config['prefix'];
			trigger_error( 'Param ptrefix is deprecated. Use prefixes instead', E_USER_NOTICE );
		}

		return $config;
	}
}

}