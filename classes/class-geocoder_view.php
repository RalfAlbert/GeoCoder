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

class GeoCoder_View extends WP_Formatter_V2
{

	/**
	 * Path to templates
	 * @var string
	 */
	public $template_dir = '';

	/**
	 * Template prefix
	 * @var string
	 */
	public $temp_prefix = 'template-';

	/**
	 * Template suffix
	 * @var string
	 */
	public $temp_suffix = '.tpl';

	/**
	 * Cache for templates
	 * @var array
	 */
	public $templates = array();

	/**
	 * Flag if templates should be stored in class-var
	 * @var bool
	 */
	public $save_template_internal = FALSE;

	/**
	 * Constructor
	 * @param	string	$template_dir	Directory with templates
	 */
	public function __construct( $template_dir ){

		$this->template_dir = rtrim( $template_dir, '/' );

		$this->read_template_dir();

	}

	/**
	 * Read the available templates in template-dir
	 */
	protected function read_template_dir(){

		$dir = sprintf(
			'%s/%s',
			$this->template_dir,
			'template-*.tpl'
		);

		foreach( glob( $dir ) as $tpl ){

			$basename = str_replace( $this->temp_prefix, '', basename( $tpl, $this->temp_suffix ) );
			$this->templates[$basename] = TRUE === $this->save_template_internal ?
				file_get_contents( $tpl ) :
				$tpl;

		}

	}

	/**
	 * Check if a requested template is available
	 * @param	string	$template	Template name
	 */
	public function isset_template( $template ){

		return isset( $this->templates[$template] );

	}

	/**
	 * Returns a template
	 * @param	string	$template	Name of the requested template
	 * @return	string	anonymous	Template as string
	 */
	public function get_template( $template ){

		if( ! isset( $this->templates[$template] ) )
			return FALSE;

		return ( is_file( $this->templates[$template] ) ) ?
			file_get_contents( $this->templates[$template] ) :
			$this->templates[$template];

	}

	/**
	 * Returns the template formatted with values
	 * @param	string	$template	Name of the template
	 * @param	array	$values		Array with values
	 * @return	string	anonymous	The formatted template
	 */
	public function get_view( $template, $values ){

		if( ! isset( $this->templates[$template] ) || empty( $values ) )
			return FALSE;

		return $this->sprintf( $this->get_template( $template ), $values );

	}

	/**
	 * Print a template formatted with values
	 * @param	string	$template	Name of the template
	 * @param	array	$values		Array with values
	 * @return	void	anonymous	Result will be printed
	 */
	public function view( $template, $values ){

		echo $this->get_view( $template, $values );

	}

}