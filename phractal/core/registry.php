<?php if (!defined('PHRACTAL')) { exit(-1); }
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
 * Registry Class
 *
 * Manages references to widely scoped objects which would otherwise
 * be singletons or create and throw-away objects which don't
 * save state (like an inflector).
 */
class PhractalRegistry extends PhractalObject
{
	/**
	 * Array of objects that have been registered
	 * @type array
	 */
	protected $objects = array();
	
	/**
	 * Check to see if an object has been registered
	 * 
	 * @param string $name Name of the object (typically the class name)
	 * @return bool True if the object is registered
	 */
	public function check($name)
	{
		return isset($this->objects[$name]);
	}
	
	/**
	 * Get an object from the registry
	 * 
	 * @param string $name Name of the object (typically the class name)
	 * @param mixed $classname If the object isn't found, and the classname is set, a new object will
	 *                         be created, registered, and returned
	 * @return mixed The registered object, or null if not found
	 */
	public function get($name, $classname = false)
	{
		$object = null;
		
		if (isset($this->objects[$name]))
		{
			$object = $this->objects[$name];
		}
		elseif ($classname !== false)
		{
			$object = new $classname();
			$this->objects[$name] = $object;
		}
		
		return $object;
	}
	
	/**
	 * Set an object in the registry. This class does not enforce
	 * that the $object be an object, it could be a string, int, etc.
	 * 
	 * @param string $name Name of the object (typically the class name)
	 * @param mixed $object Object to save in the registry (null to unregister)
	 */
	public function set($name, $object)
	{
		if (is_null($object))
		{
			unset($this->objects[$name]);
		}
		else
		{
			$this->objects[$name] = $object;
		}
	}
}
