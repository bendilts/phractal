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
 * Thrown when an object is requested, but not found in the registry
 */
class PhractalRegistryObjectNotFoundException extends PhractalException
{
	/**
	 * Name of the object that wasn't found
	 * 
	 * @var string
	 */
	protected $name;
	
	/**
	 * Constructor
	 * @param string $name
	 */
	public function __construct($name)
	{
		parent::__construct();
		$this->name = $name;
	}
	
	/**
	 * Get the name of the object
	 * 
	 * @return string
	 */
	public function get_name()
	{
		return $this->name;
	}
}

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
	 * @var array
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
	 * Get an object from the registry.
	 * 
	 * If the object isn't found, then a new object with the specified classname
	 * will be created, registered, and returned. If the classname is null,
	 * an exception will be thrown.
	 * 
	 * @param string $name Name of the object (typically the class name)
	 * @param bool $create If true, and the object isn't registered, a new
	 *                     object will be created with classname $name. This
	 *                     new object will be registered and returned.
	 * @return mixed The registered object
	 * @throws PhractalRegistryObjectNotFoundException
	 */
	public function get($name, $create = false)
	{
		if (isset($this->objects[$name]))
		{
			return $this->objects[$name];
		}
		
		if ($create)
		{
			$object = new $name();
			$this->objects[$name] = $object;
			return $object;
		}
		
		throw new PhractalRegistryObjectNotFoundException($name);
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
		$this->objects[$name] = $object;
	}
	
	/**
	 * Deletes an object from the registry.
	 * 
	 * Deleting objects that don't exist has no effect.
	 * 
	 * @param string $name
	 */
	public function del($name)
	{
		unset($this->objects[$name]);
	}
}
