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
 * Memcached Cache Component
 *
 * Connects to a set of servers running memcached.
 */
class PhractalMemcachedCacheComponent extends PhractalCacheComponent
{
	/**
	 * Memcached instance
	 * 
	 * @var Memcached
	 */
	protected $memcached;
	
	/**
	 * Constructor
	 * 
	 * @param string|array $config
	 * @see PhractalCacheComponent::__construct
	 */
	public function __construct($config)
	{
		parent::__construct($config);
		
		$this->config = array_merge(array(
			'servers' => array(),
		), $this->config);
		
		$this->memcached = new Memcached();
		$this->memcached->addServers($this->config['servers']);
		$this->memcached->setOption(Memcached::OPT_PREFIX_KEY, $this->config['prefix']);
		$this->memcached->setOption(Memcached::OPT_COMPRESSION, false);
	}
	
	/**
	 * Get the Memcached connection
	 * 
	 * @return Memcached
	 */
	public function connection()
	{
		return $this->memcached;
	}
	
	/**
	 * @see PhractalCacheComponent::flush()
	 */
	public function flush()
	{
		return $this->memcached->flush();
	}
	
	/**
	 * @see PhractalCacheComponent::increment()
	 */
	public function increment($key, $step = 1, $ttl = null, $default = 0)
	{
		$val = $this->memcached->increment($key, $step);
		if ($val === false && $this->memcached->getResultCode() === Memcached::RES_NOTFOUND)
		{
			$val = $default + $step;
			if (!$this->memcached->add($key,
			                           $val,
			                           $ttl === null ? $this->config['ttl'] : $ttl))
			{
				return false;
			}
		}
		
		return $val;
	}
	
	/**
	 * @see PhractalCacheComponent::decrement()
	 */
	public function decrement($key, $step = 1, $ttl = null, $default = 0)
	{
		$val = $this->memcached->decrement($key, $step);
		if ($val === false && $this->memcached->getResultCode() === Memcached::RES_NOTFOUND)
		{
			$val = $default - $step;
			if (!$this->memcached->add($key,
			                           $val,
			                           $ttl === null ? $this->config['ttl'] : $ttl))
			{
				return false;
			}
		}
		
		return $val;
	}
	
	/**
	 * @see PhractalCacheComponent::prepend()
	 */
	public function prepend($key, $contents, $ttl = null, $default = '')
	{
		$success = $this->memcached->prepend($key, $contents);
		if (!$success && $this->memcached->getResultCode() === Memcached::RES_NOTFOUND)
		{
			$success = $this->memcached->add($key,
			                                 $contents . $default,
			                                 $ttl === null ? $this->config['ttl'] : $ttl);
		}
		
		return $success;
	}
	
	/**
	 * @see PhractalCacheComponent::append()
	 */
	public function append($key, $contents, $ttl = null, $default = '')
	{
		$success = $this->memcached->append($key, $contents);
		if (!$success && $this->memcached->getResultCode() === Memcached::RES_NOTFOUND)
		{
			$success = $this->memcached->add($key,
			                                 $default . $contents,
			                                 $ttl === null ? $this->config['ttl'] : $ttl);
		}
		
		return $success;
	}
	
	/**
	 * @see PhractalCacheComponent::delete()
	 */
	public function delete($key)
	{
		return $this->memcached->delete($key);
	}
	
	/**
	 * @see PhractalCacheComponent::add()
	 */
	public function add($key, $value, $ttl = null)
	{
		return $this->memcached->add($key,
		                             $value,
		                             $ttl === null ? $this->config['ttl'] : $ttl);
	}
	
	/**
	 * @see PhractalCacheComponent::replace()
	 */
	public function replace($key, $value, $ttl = null)
	{
		return $this->memcached->replace($key,
		                                 $value,
		                                 $ttl === null ? $this->config['ttl'] : $ttl);
	}
	
	/**
	 * @see PhractalCacheComponent::set()
	 */
	public function set($key, $value, $ttl = null)
	{
		return $this->memcached->set($key,
		                             $value,
		                             $ttl === null ? $this->config['ttl'] : $ttl);
	}
	
	/**
	 * @see PhractalCacheComponent::get()
	 */
	public function get($key, $default = null)
	{
		$value = $this->memcached->get($key);
		
		if ($value === false)
		{
			if ($default === null)
			{
				throw new PhractalCacheComponentKeyNotFoundException($key);
			}
			
			return $default;
		}
		
		return $value;
	}
	
	/**
	 * @see PhractalCacheComponent::exists()
	 */
	public function exists($key)
	{
		return false !== $this->memcached->get($key);
	}
}
