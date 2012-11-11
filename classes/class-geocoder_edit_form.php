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

class GeoCoder_Edit_Form extends GeoCoder_Admin
{

	/**
	 * Array where the metabox can appear
	 * @var array
	 */
	public $pages = array();

	/**
	 * Constructor
	 * - Add hook for meta-box
	 * - Add hook for saving data
	 * - Add styles & sscripts to admin-header
	*/
	public function __construct(){

		add_action( 'add_meta_boxes',	array( &$this, 'add_meta_boxes' ) );
		add_action( 'save_post',		array( &$this, 'save_post_data' ) );

		$this->pages = apply_filters( 'geocoder_metabox_where', array( 'post', 'page' ) );

		foreach( $this->pages as $page ){

			add_action( sprintf( 'admin_print_styles-%s.php', $page ),		array( &$this, 'register_styles' ) );
			add_action( sprintf( 'admin_print_styles-%s-new.php', $page ),	array( &$this, 'register_styles' ) );

			add_action( sprintf( 'admin_print_scripts-%s.php', $page ),		array( &$this, 'register_scripts' ) );
			add_action( sprintf( 'admin_print_scripts-%s-new.php', $page ),	array( &$this, 'register_scripts' ) );

		}


	}

	/**
	 * Add the meta-box
	 */
	public function add_meta_boxes(){

		foreach( $this->pages as $page )
			add_meta_box(
					'geocoder',
					__( 'GeoCoder', self::LANG),
					array( &$this, 'edit_form' ),
					$page,
					'advanced',
					'default'
			);

	}

	/**
	 * Print the meta-box
	 */
	public function edit_form(){

		global $post;

		$field_vals = array(

				'btn_convert'	=> __( 'Convert Data', self::LANG ),
				'btn_clear'		=> '<img src="images/no.png" />',
				'apikey'		=> self::get_options( 'apikey' )

		);

		$values = array_merge( $field_vals, $this->get_shared_form_fields( $post->ID ) );

		wp_enqueue_style( 'geco_backend_style' );
		wp_enqueue_script( 'geco_backend_script' );

		self::$view->view( 'edit_form', $values );

	}

	/**
	 * Saving the data from meta-box
	 * @param	object	$post		Post-object
	 * @return	bool	anonymous	Always return true
	 */
	public function save_post_data( $post ){

		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post;

		if( FALSE == $this->check_nonce() )
			return;

		global $post;

		$lonlat_filter = array(
				'filter'	=> FILTER_SANITIZE_NUMBER_FLOAT,
				'flags'		=> FILTER_FLAG_ALLOW_FRACTION
		);

		$filters = array(
				'lon'		=> $lonlat_filter,
				'lat'		=> $lonlat_filter,
				'zip'		=> FILTER_SANITIZE_STRING,
				'city'		=> FILTER_SANITIZE_STRING,
				'street'	=> FILTER_SANITIZE_STRING,
		);

		$options = array_merge( self::$options, $_REQUEST['geco'] );
		$options = filter_var_array( $_REQUEST['geco'], $filters );

		self::set_options( $post->ID, $options );

		return TRUE;

	}

}
