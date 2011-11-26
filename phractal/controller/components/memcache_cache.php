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
 * Memcache Cache Component
 *
 * Connects to a set of servers running memcached.
 */
class PhractalMemcacheCacheComponent extends PhractalCacheComponent
{
	/**
	 * Memcache instance
	 * 
	 * @var Memcache
	 */
	protected $memcache;
	
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
		
		$this->memcache = new Memcache();
		foreach ($this->config['servers'] as $server)
		{
			$this->memcache->addServer(/* host */    $server[0],
			                           /* port */    $server[1],
			                           /* persist */ true,
			                           /* weight */  $server[2]);
		}
	}
	
	/**
	 * Return the Memcache connection
	 * 
	 * @return Memcache
	 */
	public function connection()
	{
		return $this->memcache;
	}
	
	/**
	 * @see PhractalCacheComponent::flush()
	 */
	public function flush()
	{
		return $this->memcache->flush();
	}
	
	/**
	 * @see PhractalCacheComponent::increment()
	 */
	public function increment($key, $step = 1)
	{
		return $this->memcache->increment($this->config['prefix'] . $key, $step);
	}
	
	/**
	 * @see PhractalCacheComponent::decrement()
	 */
	public function decrement($key, $step = 1)
	{
		return $this->memcache->decrement($this->config['prefix'] . $key, $step);
	}
	
	/**
	 * @see PhractalCacheComponent::prepend()
	 */
	public function prepend($key, $contents)
	{
		trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' is NOT atomic. You probably ought to not be using this function.', E_USER_WARNING);
		
		$value = $this->memcache->get($this->config['prefix'] . $key);
		return $value !== false && $this->memcache->set($this->config['prefix'] . $key,
		                                                $contents . $value,
		                                                0,
		                                                $ttl === null ? $this->config['ttl'] : $ttl);
	}
	
	/**
	 * @see PhractalCacheComponent::append()
	 */
	public function append($key, $contents)
	{
		trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' is NOT atomic. You probably ought to not be using this function.', E_USER_WARNING);
		
		$value = $this->memcache->get($this->config['prefix'] . $key);
		return $value !== false && $this->memcache->set($this->config['prefix'] . $key,
		                                                $value . $contents,
		                                                0,
		                                                $ttl === null ? $this->config['ttl'] : $ttl);
	}
	
	/**
	 * @see PhractalCacheComponent::delete()
	 */
	public function delete($key)
	{
		return $this->memcache->delete($this->config['prefix'] . $key);
	}
	
	/**
	 * @see PhractalCacheComponent::add()
	 */
	public function add($key, $value, $ttl = null)
	{
		return $this->memcache->add($this->config['prefix'] . $key,
		                            $value,
		                            0,
		                            $ttl === null ? $this->config['ttl'] : $ttl);
	}
	
	/**
	 * @see PhractalCacheComponent::replace()
	 */
	public function replace($key, $value, $ttl = null)
	{
		return $this->memcache->replace($this->config['prefix'] . $key,
		                                $value,
		                                0,
		                                $ttl === null ? $this->config['ttl'] : $ttl);
	}
	
	/**
	 * @see PhractalCacheComponent::set()
	 */
	public function set($key, $value, $ttl = null)
	{
		return $this->memcache->set($this->config['prefix'] . $key,
		                            $value,
		                            0,
		                            $ttl === null ? $this->config['ttl'] : $ttl);
	}
	
	/**
	 * @see PhractalCacheComponent::get()
	 */
	public function get($key, $default = null)
	{
		$value = $this->memcache->get($this->config['prefix'] . $key);
		
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
		return false !== $this->memcache->get($this->config['prefix'] . $key);
	}
}
