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

class GeoCoder_Installer extends GeoCoder
{

	/**
	 *
	 * WordPress database connection
	 * @var object
	 */
	protected static $db = NULL;

	/**
	 *
	 * Table name with WordPress table prefix
	 * @var string
	 */
	protected static $tablename = '';

	/**
	 * Stuff to do on plugin activation
	 * - Check the required versions
	 * - Check if it is an update from an older version
	 */
	public static function activate(){

		self::do_init();

		WP_Environment_Check_V2::init( array( 'wp' => '3.4', 'php' => '5.2' ) );

		if ( TRUE == self::need_update() )
			self::do_update();
		else
			self::do_install();

	}

	/**
	 * Stuff to do on deactivation
	 * - Delete the options from database
	 */
	public static function deactivate(){

		delete_option( self::OPTION_KEY );

	}

	/**
	 * Stuff to do on uninstall
	 * - Delete options from database (if not already done)
	 * - Delete entries from wp-meta
	 */
	public static function uninstall(){

		delete_option( self::OPTION_KEY );

		self::$db->query( self::$db->prepare( "DELETE FROM {self::$db->postmeta} WHERE meta_key = '%s';", self::META_KEY ) );

	}

	/**
	 * Checks if it is an update from an older version
	 * @return	boolean		anonymous	True if it is an update, else false
	 */
	protected static function need_update(){

		$res = self::$db->get_col( self::$db->prepare( "SHOW TABLES LIKE '%s'", self::$tablename ) );

		return in_array( self::$tablename, $res );

	}

	/**
	 * Initialize the plugin
	 * - Create a reference to the wp-db
	 * - Copy tablename to class-var
	 */
	protected function do_init(){

		global $wpdb;

		self::$db		 = &$wpdb;
		self::$tablename = $wpdb->prefix . self::TABLE_NAME;

		return;

	}

	/**
	 * Will be executed if an update is required
	 * - Convert old options to new options
	 * - Copy old data to wp-meta
	 * - Delete old extra table
	 */
	protected static function do_update(){

		$old_option_keys = array(
			'geco_lon'			=> 'home_lon',
			'geco_lat'			=> 'home_lat',
			'geco_plz'			=> 'home_zip',
			'geco_ort'			=> 'home_city',
			'geco_str'			=> 'home_street',
			'geco_RSS_geo'		=> 'rss_geo',
			'geco_RSS_icbm'		=> 'rss_icbm',
			'geco_RSS_geourl'	=> 'rss_geourl',
			'geco_apikey'		=> 'apikey'
		);

		$new_options = array();

		foreach ( $old_option_keys as $old_key => $new_key ){

			$new_options[$new_key] = get_option( $old_key );

			delete_option( $old_key );
		}

		$new_options['plugin_version'] = self::VERSION;

		set_option( self::OPTION_KEY, $new_options );

		// copy old data to post meta and delete the extra table
		$old_data = self::$db->get_results( self::$db->prepare( "SELECT * FROM %s;", self::$tablename ) );

		foreach ( $old_data as $entry ){

			$post_id = &$entry->post_id;

			$data = array(
				'lon' => $entry->lon,
				'lat' => $entry->lat,
				'plz' => $entry->zip,
				'ort' => $entry->city,
				'str' => $entry->street
			);

			update_post_meta( $post_id, self::META_KEY, $data );

		}

		$drop_table = self::$db->query( self::$db->prepare( "DROP TABLE IF EXISTS %s;", self::$tablename ) );

		unset(
			$old_option_keys, $old_key, $old_data, $old_data_sql,
			$new_options, $new_key, $entry,
			$drop_table_sql, $drop_table
		);

		return;

	}

	/**
	 * Will be executed if it is not an update
	 * - Add options to database
	 */
	protected static function do_install(){

		add_option( self::OPTION_KEY, self::$options );

	}

}