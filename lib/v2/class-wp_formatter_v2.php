<?php
/**
 * Simple Template Engine
 *
 * PHP version 5.3
 *
 * @category   PHP
 * @package    Template_Engine
 * @author     Ralf Albert <me@neun12.de>
 * @license    GPLv3 http://www.gnu.org/licenses/gpl-3.0.txt
 * @version    0.1.2
 * @link       http://neun12.de
 */

/**
 *
 * WP_Formtatter
 *
 * WP_Formatter is a class which provide a simple template engine. It uses the printf/sprintf-syntax to format
 * values.
 *
 * Usage:
 * $values = array(
 * 	'key_1'	=> 'value_1',
 * 	'key_2'	=> 'value_2',
 * );
 *
 * $format = 'key_1 have value %key_1%. key_2 have value %key_2%. %key_1% and %key_2%';
 * WP_Formatter::printf( $format, $values );
 *
 * $values = new stdClass();
 * $values->key_1 = 'value 1';
 * $values->key_2 = 'value 2';
 * $format = '>key_1< belongs to key_1, >key_2< belongs to key_2';
 *
 * WP_Formatter::set_delimiter( '<', '>' );
 * $e = WP_Formatter::sprintf( $format, $values );
 *
 * $values = array(
 * 	'number_one'	=> 3,
 *  'number_two'	=> 5,
 *  );
 *
 * $format	 = 'Number One filled to 5 positions: %number_one[05d]%';
 * $format	.= ' and Number Two as hex (upper chars) filled to 6 positions: %number_two[06X]%';
 * WP_Formatter::printf( $format, $values );
 *
 *
 * WP_Formatter::printf( string $format, array|object $values );
 * WP_Formatter::sprintf( string $format, array|object $values );
 * WP_Formatter::set_delimiter( string $start_delimiter, string $end_delimiter );
 *
 * Debugging
 * =========
 * WP_Formatter::debugging( 'auto'[default]|'on'|'off'|TRUE|FALSE );
 * If the debugging-mode is turned on, placeholders will be printed if their matching value is not set.
 * If debugging-mode is turned off, all placeholder without a matching value will be surpressed.
 *
 * The auto-mode will set the debugging-mode depending on WordPress' debugging-mode (WP_DEBUG)
 *
 * @author Ralf Albert
 * @version 1.1.2
 * @see http://php.net/manual/function.sprintf.php
 */
// TODO: Switch from static to object
class WP_Formatter_V2
{
	/**
	 *
	 * Starting delimiter
	 * @var string
	 */
	public static $start_delimiter	= '%';

	/**
	 *
	 * Ending delimiter
	 * @var string
	 */
	public static $end_delimiter	= '%';

	/**
	 *
	 * Flag for debugging mode
	 * @var bool
	 */
	public static $debug = TRUE;

	/**
	 *
	 * The current debugging mode in clear text (auto|on|off)
	 * @var string
	 */
	public static $debug_mode = 'auto';

	/**
	 *
	 * Replacing values in a format-string
	 * @param string $format
	 * @param array|object $values
	 * @return string|bool	Returns the formated string or FALSE on failure
	 */
	public static function sprintf( $format = '', $values = NULL ){
		/*
		 * Checking arguments
		*/
		if( empty( $format ) || NULL == $values )
			return FALSE;

		if( ! is_string( $format ) )
			$format = new WP_Error( 'formatter_error', '<p>Format must be a string. ' . ucfirst( gettype( $format ) ) . ' given.</p>' );

		if( ! is_array( $values ) && ! is_object( $values ) )
			$format = new WP_Error( 'formatter_error', '<p>Values must be type of array or object. ' . ucfirst( gettype( $values ) ) . ' given.</p>' );

		/*
		 * Do the replacement
		*/
		if( is_wp_error( $format ) )
			return $format->get_error_message( 'formatter_error' );

		foreach( $values as $key => $value ){

			if( is_array( $value ) || is_object( $value ) )
				continue;

			$matches	= array();
			$search_key	= sprintf( '%s%s%s', self::$start_delimiter, $key, self::$end_delimiter );
			$pattern	= sprintf( '/%%%s\[(.*)\]%%/iU', $key );

			// search for the values in format-string. find %key% or %key[format]%
			preg_match_all( $pattern, $format, $matches );

			// the '[format]' part was not found. replace only the key with the value
			if( empty( $matches[1] ) )
				$format = str_replace( $search_key, $value, $format );

			// one or more keys with a '[format]' part was found.
			// walk over the formats and replace the key with a formated value
			else
				foreach( $matches[1] as $match ){

					$replace = sprintf( '%' . $match, $value );
					$search = sprintf( '%s%s[%s]%s', self::$start_delimiter, $key, $match, self::$end_delimiter );
					$format = str_replace( $search, $replace, $format );

				}

		}

		if( FALSE === self::$debug )
			preg_replace( $pattern, '', $format );

		// return the formatted string
		return $format;

	}

	/**
	 *
	 * Print a formated string
	 * @param string $format
	 * @param array|object $values
	 * @uses Formatter::sprintf()
	 * @return void
	 */
	public static function printf( $format, $values ){

		echo self::sprintf( $format, $values );

	}

	/**
	 *
	 * Set the start- and end-delimiter
	 * @param string $start
	 * @param string $end
	 */
	public static function set_delimiter( $start = '%', $end = '%' ){

		self::$start_delimiter	= $start;
		self::$end_delimiter	= $end;

	}

	/**
	 *
	 * Setup the debugging mode
	 * TRUE|FALSE turns debugging mode on/off
	 * auto turns debugging mode on/off depending on WordPress' debugging mode (default)
	 * An empty value returns the state of the current debuggin mode
	 *
	 * @param	bool|string $mode Bool or string to turn debugging mode on/off
	 * @return	bool		Status of the current debugging mode
	 */
	public static function debugging( $mode = NULL ){

		if( ! is_bool( $mode) && ! is_string( $mode ) )
			return NULL;

		if( is_string( $mode ) && in_array( strtolower( $mode), array( 'auto', 'on', 'off') ) ){

			switch( strtolower( $mode ) ) {

				case 'auto':
					self::$debug = defined( 'WP_DEBUG' ) ? WP_DEBUG : FALSE;
				break;

				case 'on':
					self::$debug = TRUE;
				break;

				case 'off':
					self::$debug = FALSE;
				break;

			}

			self::$debug_mode = $mode;

		} else {

			self::$debug = $mode;
			self::$debug_mode = TRUE === self::$debug ? 'on' : 'off';

		}


		return self::$debug;

	}

	/**
	 *
	 * Returns the current debugging mode in clear text
	 *
	 * @return	string The current debugging mode: auto|on|off
	 */
	public static function get_debugmode(){

		return self::$debug_mode;

	}
}
