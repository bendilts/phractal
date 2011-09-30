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
 * Phractal Exception
 *
 * This class is the parent class for all exceptions within the
 * phractal framework.
 */
abstract class PhractalException extends Exception
{
}

/**
 * Phractal Name Exception
 * 
 * This class is the parent class for all exceptions that
 * have a single named thing that threw the exception.
 */
abstract class PhractalNameException extends PhractalException
{
	/**
	 * Name of the thing that threw the exception.
	 * @var string
	 */
	protected $name;
	
	/**
	 * Constructor
	 * 
	 * @param string $name Name of the thing that threw the exception
	 */
	public function __construct($name)
	{
		parent::__construct();
		$this->name = $name;
	}
	
	/**
	 * Get the name of the thing that threw the exception
	 * 
	 * @return string
	 */
	public function get_name()
	{
		return $this->name;
	}
}
