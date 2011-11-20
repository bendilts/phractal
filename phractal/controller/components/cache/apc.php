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
 * Apc Cache Component
 *
 * Connects to the local APC cache.
 */
class PhractalApcCacheComponent extends PhractalBaseCacheComponent
{
	/**
	 * Check to see if apc is currently enabled
	 * 
	 * @return bool
	 */
	protected function apc_enabled()
	{
		return (RUNTIME === 'web' && ini_get('apc.enabled')) ||
		       (RUNTIME === 'cli' && ini_get('apc.enable_cli'));
	}
	
	/**
	 * @see PhractalBaseCacheComponent::flush()
	 */
	public function flush()
	{
		return apc_clear_cache('user');
	}
	
	/**
	 * @see PhractalBaseCacheComponent::increment()
	 */
	public function increment($key, $step = 1, $ttl = null, $default = 0)
	{
		$val = apc_inc($this->config['prefix'] . $key, $step);
		if ($val === false)
		{
			$val = $default + step;
			if (!apc_add($this->config['prefix'] . $key,
			            $val,
			            $ttl === null ? $this->config['ttl'] : $ttl))
			{
				return false;
			}
		}
		
		return $val;
	}
	
	/**
	 * This function is NOT atomic!
	 * 
	 * @see PhractalBaseCacheComponent::prepend()
	 */
	public function prepend($key, $contents, $ttl = null, $default = '')
	{
		PhractalApp::get_instance()->get_logger()->warning('Apc::prepend is NOT atomic. You probably ought to not be using this function.');
		
		$key = $this->config['prepend'] . $key;
		$value = apc_fetch($key);
		
		return apc_store($key,
		                 $contents . ($value === false ? $default : $value),
		                 $ttl === null ? $this->config['ttl'] : $ttl);
	}
	
	/**
	 * This function is NOT atomic!
	 * 
	 * @see PhractalBaseCacheComponent::append()
	 */
	public function append($key, $contents, $ttl = null, $default = '')
	{
		PhractalApp::get_instance()->get_logger()->warning('Apc::append is NOT atomic. You probably ought to not be using this function.');
		
		$key = $this->config['prepend'] . $key;
		$value = apc_fetch($key);
		
		return apc_store($key,
		                 ($value === false ? $default : $value) . $contents,
		                 $ttl === null ? $this->config['ttl'] : $ttl);
	}
	
	/**
	 * @see PhractalBaseCacheComponent::delete()
	 */
	public function delete($key)
	{
		return apc_delete($this->config['prepend'] . $key);
	}
	
	/**
	 * @see PhractalBaseCacheComponent::add()
	 */
	public function add($key, $value, $ttl = null)
	{
		$added = apc_add($this->config['prepend'] . $key,
		                 $value,
		                 $ttl === null ? $this->config['prepend'] : $ttl);
		
		if (!$added && $this->apc_enabled())
		{
			throw new PhractalBaseCacheComponentKeyAlreadyExistsException($key);
		}
		
		return $added;
	}
	
	/**
	 * @see PhractalBaseCacheComponent::replace()
	 */
	public function replace($key, $value, $ttl = null)
	{
		PhractalApp::get_instance()->get_logger()->warning('Apc::replace is NOT atomic. You probably ought to not be using this function.');
		
		if (apc_exists($this->config['prefix'] . $key))
		{
			throw new PhractalBaseCacheComponentKeyAlreadyExistsException($key);
		}
		
		return apc_store($this->config['prefix'] . $key,
		                 $value,
		                 $ttl === null ? $this->config['ttl'] : $ttl);
	}
	
	/**
	 * @see PhractalBaseCacheComponent::set()
	 */
	public function set($key, $value, $ttl = null)
	{
		return apc_store($this->config['prefix'] . $key,
		                 $value,
		                 $ttl === null ? $this->config['ttl'] : $ttl);
	}
	
	/**
	 * @see PhractalBaseCacheComponent::get()
	 */
	public function get($key, $default = null)
	{
		$value = apc_fetch($this->config['prefix'] . $key);
		
		if ($value === false)
		{
			if ($default === null)
			{
				throw new PhractalBaseCacheComponentKeyNotFoundException($key);
			}
			
			return $default;
		}
		
		return $value;
	}
	
	/**
	 * @see PhractalBaseCacheComponent::exists()
	 */
	public function exists($key)
	{
		return apc_exists($this->config['prefix'] . $key);
	}
}
