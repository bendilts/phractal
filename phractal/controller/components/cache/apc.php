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
	 * True when APC is enabled
	 * 
	 * @var bool
	 */
	protected $apc_enabled;
	
	/**
	 * Constructor
	 * 
	 * @param mixed $config
	 * @see PhractalBaseCacheComponent::__construct
	 */
	public function __construct($config)
	{
		parent::__construct($config);
		
		$this->apc_enabled = function_exists('apc_clear_cache') &&
		                     ((RUNTIME === 'web' && ini_get('apc.enabled')) ||
		                      (RUNTIME === 'cli' && ini_get('apc.enable_cli')));
	}
	
	/**
	 * @see PhractalBaseCacheComponent::flush()
	 */
	public function flush()
	{
		return $this->apc_enabled && apc_clear_cache('user');
	}
	
	/**
	 * @see PhractalBaseCacheComponent::increment()
	 */
	public function increment($key, $step = 1, $ttl = null, $default = 0)
	{
		if (!$this->apc_enabled)
		{
			return false;
		}
		
		$val = apc_inc($this->config['prefix'] . $key, $step);
		if ($val === false)
		{
			$val = $default + $step;
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
		trigger_error('Apc::prepend is NOT atomic. You probably ought to not be using this function.', E_WARNING);
		
		if (!$this->apc_enabled)
		{
			return false;
		}
		
		$value = apc_fetch($this->config['prefix'] . $key);
		return apc_store($this->config['prefix'] . $key,
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
		trigger_error('Apc::append is NOT atomic. You probably ought to not be using this function.', E_WARNING);
		
		if (!$this->apc_enabled)
		{
			return false;
		}
		
		$value = apc_fetch($this->config['prefix'] . $key);
		return apc_store($this->config['prefix'] . $key,
		                 ($value === false ? $default : $value) . $contents,
		                 $ttl === null ? $this->config['ttl'] : $ttl);
	}
	
	/**
	 * @see PhractalBaseCacheComponent::delete()
	 */
	public function delete($key)
	{
		return $this->apc_enabled && apc_delete($this->config['prefix'] . $key);
	}
	
	/**
	 * @see PhractalBaseCacheComponent::add()
	 */
	public function add($key, $value, $ttl = null)
	{
		if (!$this->apc_enabled)
		{
			return false;
		}
		
		$added = apc_add($this->config['prefix'] . $key,
		                 $value,
		                 $ttl === null ? $this->config['ttl'] : $ttl);
		
		if (!$added)
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
		trigger_error('Apc::replace is NOT atomic. You probably ought to not be using this function.', E_WARNING);
		
		if (!$this->apc_enabled)
		{
			return false;
		}
		
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
		return $this->apc_enabled && apc_store($this->config['prefix'] . $key,
		                                       $value,
		                                       $ttl === null ? $this->config['ttl'] : $ttl);
	}
	
	/**
	 * @see PhractalBaseCacheComponent::get()
	 */
	public function get($key, $default = null)
	{
		$value = $this->apc_enabled ? apc_fetch($this->config['prefix'] . $key) : false;
		
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
		return $this->apc_enabled && apc_exists($this->config['prefix'] . $key);
	}
}
