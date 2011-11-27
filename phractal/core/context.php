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
 * Thrown when an object is retrieved but doesn't exist.
 */
class PhractalContextKeyNotFoundException extends PhractalNameException {}

// ------------------------------------------------------------------------

/**
 * Phractal Context Class
 *
 * Manages references to objects by key.
 */
class PhractalContext extends PhractalObject
{
	/**
	 * List of objects in the context
	 * 
	 * @var array
	 */
	protected $objects = array();
	
	/**
	 * Get an object by key.
	 * 
	 * If the object doesn't exist, $default will be returned.
	 * If $default is null, throw an exception.
	 * 
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 * @throws PhractalContextKeyNotFoundException
	 */
	public function get($key, $default = null)
	{
		if (!isset($this->objects[$key]))
		{
			if ($default === null)
			{
				throw new PhractalContextKeyNotFoundException($key);
			}
			
			return $default;
		}
		
		return $this->objects[$key];
	}
	
	/**
	 * Get an object from the context. If the object
	 * doesn't exist, call the closure to get it.
	 * 
	 * @param string $key
	 * @param function $closure
	 */
	public function ensure_with_closure($key, $closure)
	{
		if (!isset($this->objects[$key]))
		{
			$this->objects[$key] = $closure();
		}
		
		return $this->objects[$key];
	}
	
	/**
	 * Set an object
	 * 
	 * @param string $key
	 * @param mixed $value
	 */
	public function set($key, $value)
	{
		$this->objects[$key] = $value;
	}
	
	/**
	 * Delete an object
	 * 
	 * @param string $key
	 */
	public function delete($key)
	{
		unset($this->objects[$key]);
	}
	
	/**
	 * Remove all objects from the context
	 */
	public function clear()
	{
		$this->objects = array();
	}
	
	/**
	 * Check existence of an object
	 * 
	 * @param string $key
	 * @return bool
	 */
	public function exists($key)
	{
		return isset($this->objects[$key]);
	}
}
