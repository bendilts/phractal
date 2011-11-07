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
 * Thrown when too many arguments are passed in to the class constructor function.
 */
class PhractalCallConstructorFunctionTooManyArgsException extends PhractalNameException {}

// ------------------------------------------------------------------------

/**
 * @see call_user_func_array()
 * @param callback $callback
 * @param array $parameters
 * @return mixed
 */
function call_user_func_array_optimized($callback, $parameters = array())
{
	if (is_array($callback))
	{
		$object = $callback[0];
		$function = $callback[1];
		
		if (is_object($object))
		{
			switch (count($parameters))
			{
				case 0:
					return $object->{$function}();
				case 1:
					return $object->{$function}($parameters[0]);
				case 2:
					return $object->{$function}($parameters[0], $parameters[1]);
				case 3:
					return $object->{$function}($parameters[0], $parameters[1], $parameters[2]);
				case 4:
					return $object->{$function}($parameters[0], $parameters[1], $parameters[2], $parameters[3]);
				case 5:
					return $object->{$function}($parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4]);
				case 6:
					return $object->{$function}($parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4], $parameters[5]);
				case 7:
					return $object->{$function}($parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4], $parameters[5], $parameters[6]);
				case 8:
					return $object->{$function}($parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4], $parameters[5], $parameters[6], $parameters[7]);
				case 9:
					return $object->{$function}($parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4], $parameters[5], $parameters[6], $parameters[7], $parameters[8]);
				case 10:
					return $object->{$function}($parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4], $parameters[5], $parameters[6], $parameters[7], $parameters[8], $parameters[9]);
			}
		}
	}
	
	return call_user_func_array($callback, $parameters);
}

// ------------------------------------------------------------------------

/**
 * This function is like call_user_func_array, except it is used
 * only for constructing objects.
 * 
 * @param string $class
 * @param array $parameters
 * @return object
 * @throws PhractalCallConstructorFunctionTooManyArgsException
 */
function call_constructor($class, $parameters = array())
{
	switch (count($parameters))
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
			throw new PhractalCallConstructorFunctionTooManyArgsException($class);
	}
}
