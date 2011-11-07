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
	
	/**
	 * Call a function with a variable number of parameters on this object.
	 * 
	 * @param string $function
	 * @param array $parameters Default is no parameters
	 * @return mixed Whatever the function returns
	 */
	protected function dynamic_call($function, array $parameters = array())
	{
		// we can't just call call_user_func_array_optimized here because that
		// won't allow us to call private/protected methods
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
				return call_user_func_array(array(&$this, $function), $parameters);
		}
	}
}
