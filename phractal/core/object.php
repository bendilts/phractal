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
 * Thrown when too many arguments are passed in to the class constructor function
 * on this class.
 */
class PhractalObjectNewObjectTooManyArgsException extends PhractalNameException {}

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
	
	/**
	 * Call a function with a variable number of parameters. This is a fast
	 * replacement for call_user_func_array
	 * 
	 * @param string $function
	 * @param array $parameters Default is no parameters
	 * @return mixed Whatever the function returns
	 */
	protected function dynamic_call($function, array $parameters = array())
	{
		switch (count($parameters))
		{
			case 0:
				return $this->{$function}();
			case 1:
				return $this->{$function}($parameters[0]);
			case 2:
				return $this->{$function}($parameters[0], $parameters[1]);
			case 3:
				return $this->{$function}($parameters[0], $parameters[1], $parameters[2]);
			case 4:
				return $this->{$function}($parameters[0], $parameters[1], $parameters[2], $parameters[3]);
			case 5:
				return $this->{$function}($parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4]);
			case 6:
				return $this->{$function}($parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4], $parameters[5]);
			case 7:
				return $this->{$function}($parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4], $parameters[5], $parameters[6]);
			case 8:
				return $this->{$function}($parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4], $parameters[5], $parameters[6], $parameters[7]);
			case 9:
				return $this->{$function}($parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4], $parameters[5], $parameters[6], $parameters[7], $parameters[8]);
			case 10:
				return $this->{$function}($parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4], $parameters[5], $parameters[6], $parameters[7], $parameters[8], $parameters[9]);
			default:
				return call_user_func_array(array($this, $function), $parameters);
		}
	}
	
	/**
	 * Create a new class with a variable number of arguments
	 * 
	 * @param string $class
	 * @param array $parameters
	 * @return mixed New object of type $class
	 * @throws PhractalObjectNewObjectTooManyArgsException
	 */
	protected function dynamic_new($class, array $parameters = array())
	{
		switch (count($paramaters))
		{
			case 0:
				return new $class();
			case 1:
				return new $class($parameters[0]);
			case 2:
				return new $class($parameters[0], $parameters[1]);
			case 3:
				return new $class($parameters[0], $parameters[1], $parameters[2]);
			case 4:
				return new $class($parameters[0], $parameters[1], $parameters[2], $parameters[3]);
			case 5:
				return new $class($parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4]);
			case 6:
				return new $class($parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4], $parameters[5]);
			case 7:
				return new $class($parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4], $parameters[5], $parameters[6]);
			case 8:
				return new $class($parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4], $parameters[5], $parameters[6], $parameters[7]);
			case 9:
				return new $class($parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4], $parameters[5], $parameters[6], $parameters[7], $parameters[8]);
			case 10:
				return new $class($parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4], $parameters[5], $parameters[6], $parameters[7], $parameters[8], $parameters[9]);
			default:
				throw new PhractalObjectNewObjectTooManyArgsException($class);
		}
	}
}
