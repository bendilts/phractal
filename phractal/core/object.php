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
 * phractal Object Class
 *
 * The root object class for the entire phractal framework. The most important
 * functions of this class are the magic functions like the constructor and
 * destructor. If these functions didn't exist, then parent::__construct (or
 * similar parent::__destruct) calls would fail from child functions.
 */
abstract class PhractalObject
{
	/**
	 * Class Constructor
	 */
	public function __construct()
	{
		// intentionally blank
	}
	
	/**
	 * Class Destructor
	 */
	public function __destruct()
	{
		// intentionally blank
	}
}
