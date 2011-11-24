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
class PhractalMemcacheCacheComponent extends PhractalBaseCacheComponent
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
	 * @see PhractalBaseCacheComponent::__construct
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
	 * @see PhractalBaseCacheComponent::flush()
	 */
	public function flush()
	{
		return $this->memcache->flush();
	}
	
	/**
	 * @see PhractalBaseCacheComponent::increment()
	 */
	public function increment($key, $step = 1, $ttl = null, $default = 0)
	{
		$val = $this->memcache->increment($this->config['prefix'] . $key, $step);
		if ($val === false)
		{
			$val = $default + $step;
			if (!$this->memcache->add($this->config['prefix'] . $key,
			                          $val,
			                          0,
			                          $ttl === null ? $this->config['ttl'] : $ttl))
			{
				return false;
			}
		}
		
		return $val;
	}
	
	/**
	 * @see PhractalBaseCacheComponent::prepend()
	 */
	public function prepend($key, $contents, $ttl = null, $default = '')
	{
		trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' is NOT atomic. You probably ought to not be using this function.', E_USER_WARNING);
		
		$value = $this->memcache->get($this->config['prefix'] . $key);
		return $this->memcache->set($this->config['prefix'] . $key,
		                            $contents . ($value === false ? $default : $value),
		                            0,
		                            $ttl === null ? $this->config['ttl'] : $ttl);
	}
	
	/**
	 * @see PhractalBaseCacheComponent::append()
	 */
	public function append($key, $contents, $ttl = null, $default = '')
	{
		trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' is NOT atomic. You probably ought to not be using this function.', E_USER_WARNING);
		
		$value = $this->memcache->get($this->config['prefix'] . $key);
		return $this->memcache->set($this->config['prefix'] . $key,
		                            ($value === false ? $default : $value) . $contents,
		                            0,
		                            $ttl === null ? $this->config['ttl'] : $ttl);
	}
	
	/**
	 * @see PhractalBaseCacheComponent::delete()
	 */
	public function delete($key)
	{
		return $this->memcache->delete($this->config['prefix'] . $key);
	}
	
	/**
	 * @see PhractalBaseCacheComponent::add()
	 */
	public function add($key, $value, $ttl = null)
	{
		return $this->memcache->add($this->config['prefix'] . $key,
		                            $value,
		                            0,
		                            $ttl === null ? $this->config['ttl'] : $ttl);
	}
	
	/**
	 * @see PhractalBaseCacheComponent::replace()
	 */
	public function replace($key, $value, $ttl = null)
	{
		return $this->memcache->replace($this->config['prefix'] . $key,
		                                $value,
		                                0,
		                                $ttl === null ? $this->config['ttl'] : $ttl);
	}
	
	/**
	 * @see PhractalBaseCacheComponent::set()
	 */
	public function set($key, $value, $ttl = null)
	{
		return $this->memcache->set($this->config['prefix'] . $key,
		                            $value,
		                            0,
		                            $ttl === null ? $this->config['ttl'] : $ttl);
	}
	
	/**
	 * @see PhractalBaseCacheComponent::get()
	 */
	public function get($key, $default = null)
	{
		$value = $this->memcache->get($this->config['prefix'] . $key);
		
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
		return false !== $this->memcache->get($this->config['prefix'] . $key);
	}
}
