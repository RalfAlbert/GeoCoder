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

class GeoCoder_RSS extends GeoCoder
{

	/**
	 * Insert geotags into the feeds
	 */
	public static function insert_geotags(){

		global $post;

		$geodata = self::get_options( $post->ID );

		if( TRUE === self::get_options( 'rss_geo' ) ){

			if( ! empty( $geodata['lat'] ) && ! empty( $geodata['lon'] ) )
				echo "\n\t\t<georss:point>{$geodata['lat']} {$geodata['lon']}</georss:point>";

			if( ! empty( $geodata['zip'] ) && ! empty( $geodata['city'] ) ){
				echo "\n\t\t<georss:featureTypeTag>city</georss:featureTypeTag>";
				echo "\n\t\t<georss:featureName>{$geodata['zip']} {$geodata['city']}</georss:featureName>\n";
			}

		}

		if(
			TRUE === self::get_options( 'rss_icbm' ) &&
			( ! empty( $geodata['lat'] ) && ! empty( $geodata['lon'] ) )
		) {
			echo "\n\t\t<icbm:latitude>{$geodata['lat']}</icbm:latitude>";
			echo "\n\t\t<icbm:longitude>{$geodata['lon']}</icbm:longitude>\n";
		}


		if(
			TRUE === self::get_options( 'rss_geourl' ) &&
			( ! empty( $geodata['lat'] ) && ! empty( $geodata['lon'] ) )
		) {
			echo "\n\t\t<geourl:latitude>{$geodata['lat']}</geourl:latitude>";
			echo "\n\t\t<geourl:longitude>{$geodata['lon']}</geourl:longitude>\n";

		}

	}

	/**
	 * Insert namespaces into the feeds
	 */
	public static function insert_namespaces(){

	   // Add geo
	    if( TRUE === self::get_options( 'rss_geo' ) )
	        //echo "\txmlns:geo=\"http://www.w3.org/2003/01/geo/wgs84_pos#\"\n";
	        echo "\txmlns:georss=\"http://www.georss.org/georss\"\n";

	   // Add icbm
	    if( TRUE === self::get_options( 'rss_icbm' ) )
	        echo "\txmlns:icbm=\"http://postneo.com/icbm\"\n";


	   // Add geourl
	    if( TRUE === self::get_options( 'rss_geourl' ) )
	    	echo "\txmlns:geourl=\"http://geourl.org/rss/module/\"\n";

	}

}