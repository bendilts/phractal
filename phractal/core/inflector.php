<?php if (!defined('PHRACTAL')) { exit('no access'); }
/**
 * phractal
 *
 * A framework for PHP 5 dedicated to high availability and scaling.
 *
 * @author		Matthew Barlocker
 * @copyright	Copyright (c) 2011, Matthew Barlocker
 * @license		Proprietary, All Rights Reserved
 * @link		https://github.com/mbarlocker/phractal
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Inflector Class
 *
 * Manipulates strings to match conventions. For example,
 * can change PhractalInflector to phractal_inflector.php,
 * and vice versa.
 */
class PhractalInflector extends PhractalObject
{
	/**
	 * Change a string into a camel cased word (ie myClassName)
	 * 
	 * @param string $string
	 * @return string
	 */
	public function camel($string)
	{
		return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
	}
	
	/**
	 * Change a string into an upper, camel cased word (ie MyClassName)
	 * 
	 * @param string $string
	 * @return string
	 */
	public function camel_upper_first($string)
	{
		$new = $this->camel($string);
		return empty($new) ? '' : strtoupper($new[0]) . substr($new, 1);
	}
	
	/**
	 * Change a string into an underscored word (ie my_class_name)
	 * 
	 * @param string $string
	 * @return string
	 */
	public function underscore($string)
	{
		return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $string));
	}
}
