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
	 * @param array $config
	 * @see PhractalCacheComponent::__construct
	 */
	public function __construct($config)
	{
		parent::__construct($config);
		
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
	public function increment($key, $step = 1)
	{
		return $this->memcached->increment($key, $step);
	}
	
	/**
	 * @see PhractalCacheComponent::decrement()
	 */
	public function decrement($key, $step = 1)
	{
		return $this->memcached->decrement($key, $step);
	}
	
	/**
	 * @see PhractalCacheComponent::prepend()
	 */
	public function prepend($key, $contents, $ttl = null, $default = '')
	{
		return $this->memcached->prepend($key, $contents);
	}
	
	/**
	 * @see PhractalCacheComponent::append()
	 */
	public function append($key, $contents, $ttl = null, $default = '')
	{
		return $this->memcached->append($key, $contents);
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
