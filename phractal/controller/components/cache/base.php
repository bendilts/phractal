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
 * Thrown when a config name is not found in the cache.configs configuration.
 */
class PhractalBaseCacheComponentConfigNotFoundException extends PhractalNameException {}

// ------------------------------------------------------------------------

/**
 * Thrown when a key cannot be retrieved because it doesn't exist.
 */
class PhractalBaseCacheComponentKeyNotFoundException extends PhractalNameException {}

// ------------------------------------------------------------------------

/**
 * Thrown when a key cannot be added because it already exists.
 */
class PhractalBaseCacheComponentKeyAlreadyExistsException extends PhractalNameException {}

// ------------------------------------------------------------------------

/**
 * Base Cache Component
 *
 * Parent class for all caching components
 */
abstract class PhractalBaseCacheComponent extends PhractalBaseComponent
{
	/**
	 * The configuration array associated with this instance.
	 * 
	 * @var array
	 */
	protected $config;
	
	/**
	 * Constructor
	 * 
	 * @param string|array $config Either the name of the config (in cache.configs array) or the configuration itself.
	 * @throws InvalidArgumentException
	 */
	public function __construct($config)
	{
		parent::__construct();
		
		if (is_string($connection))
		{
			$all = PhractalApp::get_instance()->get_config()->get('cache.configs', array());
			
			if (!isset($all[$config]))
			{
				throw new PhractalBaseCacheComponentConfigNotFoundException($config);
			}
			
			$this->config = $all[$config];
		}
		elseif (is_array($config))
		{
			$this->config = $config;
		}
		else
		{
			throw new InvalidArgumentException('Cache constructor accepts a cache config name or a configuration array.');
		}
	}
	
	/**
	 * Flush all elements in the cache
	 * 
	 * @return bool Success
	 */
	abstract public function flush();
	
	/**
	 * Increment the numeric value of a cache entry
	 * 
	 * @param string $key
	 * @param int $step
	 * @param int $expires
	 * @param int $default
	 * @return int The new value of the entry (after incrementing)
	 */
	abstract public function increment($key, $step = 1, $expires = null, $default = 0);
	
	/**
	 * Decrement the numeric value of a cache entry
	 * 
	 * @param string $key
	 * @param int $step
	 * @param int $expires
	 * @param int $default
	 * @return int The new value of the entry (after decrementing)
	 */
	abstract public function decrement($key, $step = 1, $expires = null, $default = 0);
	
	/**
	 * Prepend a string value to a string cache entry
	 * 
	 * @param string $key
	 * @param string $contents
	 * @param int $expires
	 * @param string $default
	 * @return bool Success
	 */
	abstract public function prepend($key, $contents, $expires = null, $default = '');
	
	/**
	 * Append a string value to a string cache entry
	 * 
	 * @param string $key
	 * @param string $contents
	 * @param int $expires
	 * @param string $default
	 * @return bool Success
	 */
	abstract public function append($key, $contents, $expires = null, $default = '');
	
	/**
	 * Delete an entry from the cache
	 * 
	 * @param string $key
	 * @return bool Success
	 */
	abstract public function delete($key);
	
	/**
	 * Add an entry to the cache if it doesn't already exist.
	 * 
	 * @param string $key
	 * @param mixed $value
	 * @param int$expires
	 * @return bool Success
	 * @throws PhractalBaseCacheComponentKeyAlreadyExistsException
	 */
	abstract public function add($key, $value, $expires = null);
	
	/**
	 * Replace an entry in the cache if it exists.
	 * 
	 * @param string $key
	 * @param mixed $value
	 * @param int $expires
	 * @return bool Success
	 * @throws PhractalBaseCacheComponentKeyNotFoundException
	 */
	abstract public function replace($key, $value, $expires = null);
	
	/**
	 * Set the value of a cache entry
	 * 
	 * @param string $key
	 * @param mixed $value
	 * @param int $expires
	 * @return bool Success
	 */
	abstract public function set($key, $value, $expires = null);
	
	/**
	 * Get the value of a cache entry
	 * 
	 * If the key doesn't exist, then $default will be returned.
	 * If $default is null, then an exception will be thrown.
	 * 
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 * @throws PhractalBaseCacheComponentKeyNotFoundException
	 */
	abstract public function get($key, $default = null);
}
