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
 * Thrown when a variable is not found and no default is specified
 */
class PhractalRequestComponentVariableNotFoundException extends PhractalNameException {}

// ------------------------------------------------------------------------

/**
 * Thrown when the request has been locked and an attempt to change anything
 * about the request is made.
 */
class PhractalRequestComponentLockedException extends PhractalException {}

// ------------------------------------------------------------------------

/**
 * Request Component
 *
 * Contains all request parameters. This includes POST,
 * GET, PUT, DELETE, FILES, COOKIE, and SERVER.
 */
class PhractalRequestComponent extends PhractalBaseComponent
{
	/**
	 * True when this request has been locked
	 * 
	 * @var bool
	 */
	protected $locked = false;
	
	/**
	 * Request method
	 * 
	 * This can have any string value, but will commonly have
	 * GET, POST, DELETE, PUT, etc. It will help determine the
	 * route to use.
	 * 
	 * @var string
	 */
	protected $method;
	
	/**
	 * Request URI
	 * 
	 * @var string
	 */
	protected $uri;
	
	/**
	 * GET variables
	 * 
	 * @var array
	 */
	protected $get = array();
	
	/**
	 * POST variables
	 * 
	 * @var array
	 */
	protected $post = array();
	
	/**
	 * PUT variables
	 * 
	 * @var array
	 */
	protected $put = array();
	
	/**
	 * DELETE variables
	 * 
	 * @var array
	 */
	protected $delete = array();
	
	/**
	 * ENV variables
	 * 
	 * @var array
	 */
	protected $env = array();
	
	/**
	 * COOKIE variables
	 * 
	 * @var array
	 */
	protected $cookie = array();
	
	/**
	 * SERVER variables
	 * 
	 * @var array
	 */
	protected $server = array();
	
	/**
	 * FILES variables
	 * 
	 * @var array
	 */
	protected $files = array();
	
	/**
	 * Constructor
	 * 
	 * @param string $method Request Method
	 * @param string $uri Request URI
	 */
	public function __construct($method, $uri)
	{
		parent::__construct();
		
		$this->method = strtoupper($method);
		$this->uri = $uri;
	}
	
	/**
	 * Throws an exception if the request is locked
	 * 
	 * @throws PhractalRequestComponentLockedException
	 */
	protected function throw_exception_if_locked()
	{
		if ($this->locked)
		{
			throw new PhractalRequestComponentLockedException();
		}
	}
	
	/**
	 * Lock the request.
	 * 
	 * After being locked, the request cannot be modified.
	 * All subsequent attempts to change the request will
	 * throw an exception
	 */
	public function lock()
	{
		$this->locked = true;
	}
	
	/**
	 * Get the Request Method
	 * 
	 * @return string
	 */
	public function get_method()
	{
		return $this->method;
	}
	
	/**
	 * Get the Request URI
	 * 
	 * @return string
	 */
	public function get_uri()
	{
		return $this->uri;
	}
	
	/**
	 * Replace all SERVER vars with the ones from a request, and then
	 * modify the relevant ones to indicate the current request.
	 * 
	 * For example, REQUEST_URI would be changed to the
	 * current URI, REQUEST_METHOD would be changed as
	 * well.
	 * 
	 * @param PhractalRequestComponent $request
	 */
	public function replace_server_updated(PhractalRequestComponent $request)
	{
		$this->server = $request->server;
		
		if (RUNTIME === 'cli')
		{
			$this->server['argv'][1] = $this->method;
			$this->server['argv'][2] = $this->uri;
		}
		elseif (RUNTIME === 'web')
		{
			$this->server['REQUEST_METHOD'] = $this->method;
			$this->server['REQUEST_URI'] = $this->uri;
		}
	}
	
	/**
	 * Replace all SERVER vars with the ones from a request
	 * 
	 * @param PhractalRequestComponent $request
	 */
	public function replace_env(PhractalRequestComponent $request)
	{
		$this->env = $request->env;
	}
	
	/**
	 * Replace all COOKIE values with the ones from the request
	 * 
	 * @param PhractalRequestComponent $request
	 */
	public function replace_cookie(PhractalRequestComponent $request)
	{
		$this->cookie = $request->cookie;
	}
	
	/**
	 * Get a REQUEST variable as it would normally be gotten
	 * from the $_REQUEST super global.
	 * 
	 * The same precedence is used here as would be used
	 * in the $_REQUEST super global
	 * 
	 * If the variable isn't found, then $default will be returned.
	 * If $default is null, an exception will be thrown.
	 * 
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 * @throws PhractalRequestComponentVariableNotFoundException
	 */
	public function get_request($name, $default = null)
	{
		$lookup = array(
			'G' => &$this->get,
			'P' => &$this->post,
			'C' => &$this->cookie,
			'S' => &$this->server,
			'E' => &$this->env,
		);
		
		$order = ini_get('request_order');
		$length = strlen($order);
		for ($i = 0; $i < $length; $i++)
		{
			$char = $order[$i];
			$array = &$lookup[$char];
			if (isset($array[$name]))
			{
				return $array[$name];
			}
		}
		
		if ($default !== null)
		{
			return $default;
		}
		
		throw new PhractalRequestComponentVariableNotFoundException('REQUEST::' . $name);
	}
	
	/**
	 * Set a GET variable
	 * 
	 * @param string $name
	 * @param mixed $value
	 */
	public function set_get($name, $value)
	{
		$this->throw_exception_if_locked();
		$this->get[$name] = $value;
	}
	
	/**
	 * Set an array of GET variables
	 * 
	 * This will not replace all the existing GET values, but will
	 * overwrite any that were already set.
	 * 
	 * @param array $array
	 * @throws PhractalRequestComponentLockedException
	 */
	public function set_get_array(array $array)
	{
		$this->throw_exception_if_locked();
		$this->get = array_merge($this->get, $array);
	}
	
	/**
	 * Delete a GET variable
	 * 
	 * @param string $name
	 */
	public function del_get($name)
	{
		$this->throw_exception_if_locked();
		unset($this->get[$name]);
	}
	
	/**
	 * Get a GET variable
	 * 
	 * If the variable isn't found, $default will be returned.
	 * If $default is null, an exception will be thrown.
	 * 
	 * @param string $name
	 * @param mixed $default
	 * @throws PhractalRequestComponentVariableNotFoundException
	 */
	public function get_get($name, $default = null)
	{
		if (isset($this->get[$name]))
		{
			return $this->get[$name];
		}
		elseif ($default !== null)
		{
			return $default;
		}
		else
		{
			throw new PhractalRequestComponentVariableNotFoundException('GET::' . $name);
		}
	}
	
	/**
	 * Get all of the GET variables in an associative array.
	 * 
	 * @return array
	 */
	public function get_all_get()
	{
		return $this->get;
	}
	
	/**
	 * Check to see if a GET variable exists
	 * 
	 * @param string $name
	 * @return bool
	 */
	public function check_get($name)
	{
		return isset($this->get[$name]);
	}
	
	/**
	 * Set a POST variable
	 * 
	 * @param string $name
	 * @param mixed $value
	 */
	public function set_post($name, $value)
	{
		$this->throw_exception_if_locked();
		$this->post[$name] = $value;
	}
	
	/**
	 * Set an array of POST variables
	 * 
	 * This will not replace all the existing POST values, but will
	 * overwrite any that were already set.
	 * 
	 * @param array $array
	 * @throws PhractalRequestComponentLockedException
	 */
	public function set_post_array(array $array)
	{
		$this->throw_exception_if_locked();
		$this->post = array_merge($this->post, $array);
	}
	
	/**
	 * Delete a POST variable
	 * 
	 * @param string $name
	 */
	public function del_post($name)
	{
		$this->throw_exception_if_locked();
		unset($this->post[$name]);
	}
	
	/**
	 * Get a POST variable
	 * 
	 * If the variable isn't found, $default will be returned.
	 * If $default is null, an exception will be thrown.
	 * 
	 * @param string $name
	 * @param mixed $default
	 * @throws PhractalRequestComponentVariableNotFoundException
	 */
	public function get_post($name, $default = null)
	{
		if (isset($this->post[$name]))
		{
			return $this->post[$name];
		}
		elseif ($default !== null)
		{
			return $default;
		}
		else
		{
			throw new PhractalRequestComponentVariableNotFoundException('POST::' . $name);
		}
	}
	
	/**
	 * Get all of the POST variables in an associative array.
	 * 
	 * @return array
	 */
	public function get_all_post()
	{
		return $this->post;
	}
	
	/**
	 * Check to see if a POST variable exists
	 * 
	 * @param string $name
	 * @return bool
	 */
	public function check_post($name)
	{
		return isset($this->post[$name]);
	}
	
	/**
	 * Set a PUT variable
	 * 
	 * @param string $name
	 * @param mixed $value
	 */
	public function set_put($name, $value)
	{
		$this->throw_exception_if_locked();
		$this->put[$name] = $value;
	}
	
	/**
	 * Set an array of PUT variables
	 * 
	 * This will not replace all the existing PUT values, but will
	 * overwrite any that were already set.
	 * 
	 * @param array $array
	 * @throws PhractalRequestComponentLockedException
	 */
	public function set_put_array(array $array)
	{
		$this->throw_exception_if_locked();
		$this->put = array_merge($this->put, $array);
	}
	
	/**
	 * Delete a PUT variable
	 * 
	 * @param string $name
	 */
	public function del_put($name)
	{
		$this->throw_exception_if_locked();
		unset($this->put[$name]);
	}
	
	/**
	 * Get a PUT variable
	 * 
	 * If the variable isn't found, $default will be returned.
	 * If $default is null, an exception will be thrown.
	 * 
	 * @param string $name
	 * @param mixed $default
	 * @throws PhractalRequestComponentVariableNotFoundException
	 */
	public function get_put($name, $default = null)
	{
		if (isset($this->put[$name]))
		{
			return $this->put[$name];
		}
		elseif ($default !== null)
		{
			return $default;
		}
		else
		{
			throw new PhractalRequestComponentVariableNotFoundException('PUT::' . $name);
		}
	}
	
	/**
	 * Get all of the PUT variables in an associative array.
	 * 
	 * @return array
	 */
	public function get_all_put()
	{
		return $this->put;
	}
	
	/**
	 * Check to see if a PUT variable exists
	 * 
	 * @param string $name
	 * @return bool
	 */
	public function check_put($name)
	{
		return isset($this->put[$name]);
	}
	
	/**
	 * Set a DELETE variable
	 * 
	 * @param string $name
	 * @param mixed $value
	 */
	public function set_delete($name, $value)
	{
		$this->throw_exception_if_locked();
		$this->delete[$name] = $value;
	}
	
	/**
	 * Set an array of DELETE variables
	 * 
	 * This will not replace all the existing DELETE values, but will
	 * overwrite any that were already set.
	 * 
	 * @param array $array
	 * @throws PhractalRequestComponentLockedException
	 */
	public function set_delete_array(array $array)
	{
		$this->throw_exception_if_locked();
		$this->delete = array_merge($this->delete, $array);
	}
	
	/**
	 * Delete a DELETE variable
	 * 
	 * @param string $name
	 */
	public function del_delete($name)
	{
		$this->throw_exception_if_locked();
		unset($this->delete[$name]);
	}
	
	/**
	 * Get a DELETE variable
	 * 
	 * If the variable isn't found, $default will be returned.
	 * If $default is null, an exception will be thrown.
	 * 
	 * @param string $name
	 * @param mixed $default
	 * @throws PhractalRequestComponentVariableNotFoundException
	 */
	public function get_delete($name, $default = null)
	{
		if (isset($this->delete[$name]))
		{
			return $this->delete[$name];
		}
		elseif ($default !== null)
		{
			return $default;
		}
		else
		{
			throw new PhractalRequestComponentVariableNotFoundException('DELETE::' . $name);
		}
	}
	
	/**
	 * Get all of the DELETE variables in an associative array.
	 * 
	 * @return array
	 */
	public function get_all_delete()
	{
		return $this->delete;
	}
	
	/**
	 * Check to see if a DELETE variable exists
	 * 
	 * @param string $name
	 * @return bool
	 */
	public function check_delete($name)
	{
		return isset($this->delete[$name]);
	}
	
	/**
	 * Set a COOKIE variable
	 * 
	 * @param string $name
	 * @param mixed $value
	 */
	public function set_cookie($name, $value)
	{
		$this->throw_exception_if_locked();
		$this->cookie[$name] = $value;
	}
	
	/**
	 * Set an array of COOKIE variables
	 * 
	 * This will not replace all the existing COOKIE values, but will
	 * overwrite any that were already set.
	 * 
	 * @param array $array
	 * @throws PhractalRequestComponentLockedException
	 */
	public function set_cookie_array(array $array)
	{
		$this->throw_exception_if_locked();
		$this->cookie = array_merge($this->cookie, $array);
	}
	
	/**
	 * Delete a COOKIE variable
	 * 
	 * @param string $name
	 */
	public function del_cookie($name)
	{
		$this->throw_exception_if_locked();
		unset($this->cookie[$name]);
	}
	
	/**
	 * Get a COOKIE variable
	 * 
	 * If the variable isn't found, $default will be returned.
	 * If $default is null, an exception will be thrown.
	 * 
	 * @param string $name
	 * @param mixed $default
	 * @throws PhractalRequestComponentVariableNotFoundException
	 */
	public function get_cookie($name, $default = null)
	{
		if (isset($this->cookie[$name]))
		{
			return $this->cookie[$name];
		}
		elseif ($default !== null)
		{
			return $default;
		}
		else
		{
			throw new PhractalRequestComponentVariableNotFoundException('COOKIE::' . $name);
		}
	}
	
	/**
	 * Get all of the COOKIE variables in an associative array.
	 * 
	 * @return array
	 */
	public function get_all_cookie()
	{
		return $this->cookie;
	}
	
	/**
	 * Check to see if a COOKIE variable exists
	 * 
	 * @param string $name
	 * @return bool
	 */
	public function check_cookie($name)
	{
		return isset($this->cookie[$name]);
	}
	
	/**
	 * Set a SERVER variable
	 * 
	 * @param string $name
	 * @param mixed $value
	 */
	public function set_server($name, $value)
	{
		$this->throw_exception_if_locked();
		$this->server[$name] = $value;
	}
	
	/**
	 * Set an array of SERVER variables
	 * 
	 * This will not replace all the existing SERVER values, but will
	 * overwrite any that were already set.
	 * 
	 * @param array $array
	 * @throws PhractalRequestComponentLockedException
	 */
	public function set_server_array(array $array)
	{
		$this->throw_exception_if_locked();
		$this->server = array_merge($this->server, $array);
	}
	
	/**
	 * Delete a SERVER variable
	 * 
	 * @param string $name
	 */
	public function del_server($name)
	{
		$this->throw_exception_if_locked();
		unset($this->server[$name]);
	}
	
	/**
	 * Get a SERVER variable
	 * 
	 * If the variable isn't found, $default will be returned.
	 * If $default is null, an exception will be thrown.
	 * 
	 * @param string $name
	 * @param mixed $default
	 * @throws PhractalRequestComponentVariableNotFoundException
	 */
	public function get_server($name, $default = null)
	{
		if (isset($this->server[$name]))
		{
			return $this->server[$name];
		}
		elseif ($default !== null)
		{
			return $default;
		}
		else
		{
			throw new PhractalRequestComponentVariableNotFoundException('SERVER::' . $name);
		}
	}
	
	/**
	 * Get all of the SERVER variables in an associative array.
	 * 
	 * @return array
	 */
	public function get_all_server()
	{
		return $this->server;
	}
	
	/**
	 * Check to see if a SERVER variable exists
	 * 
	 * @param string $name
	 * @return bool
	 */
	public function check_server($name)
	{
		return isset($this->server[$name]);
	}
	
	/**
	 * Set a ENV variable
	 * 
	 * @param string $name
	 * @param mixed $value
	 */
	public function set_env($name, $value)
	{
		$this->throw_exception_if_locked();
		$this->env[$name] = $value;
	}
	
	/**
	 * Set an array of ENV variables
	 * 
	 * This will not replace all the existing ENV values, but will
	 * overwrite any that were already set.
	 * 
	 * @param array $array
	 * @throws PhractalRequestComponentLockedException
	 */
	public function set_env_array(array $array)
	{
		$this->throw_exception_if_locked();
		$this->env = array_merge($this->env, $array);
	}
	
	/**
	 * Delete a ENV variable
	 * 
	 * @param string $name
	 */
	public function del_env($name)
	{
		$this->throw_exception_if_locked();
		unset($this->env[$name]);
	}
	
	/**
	 * Get a ENV variable
	 * 
	 * If the variable isn't found, $default will be returned.
	 * If $default is null, an exception will be thrown.
	 * 
	 * @param string $name
	 * @param mixed $default
	 * @throws PhractalRequestComponentVariableNotFoundException
	 */
	public function get_env($name, $default = null)
	{
		if (isset($this->env[$name]))
		{
			return $this->env[$name];
		}
		elseif ($default !== null)
		{
			return $default;
		}
		else
		{
			throw new PhractalRequestComponentVariableNotFoundException('ENV::' . $name);
		}
	}
	
	/**
	 * Get all of the ENV variables in an associative array.
	 * 
	 * @return array
	 */
	public function get_all_env()
	{
		return $this->env;
	}
	
	/**
	 * Check to see if a ENV variable exists
	 * 
	 * @param string $name
	 * @return bool
	 */
	public function check_env($name)
	{
		return isset($this->env[$name]);
	}
	
	/**
	 * Set a FILES variable
	 * 
	 * @param string $name
	 * @param mixed $value
	 */
	public function set_files($name, $value)
	{
		$this->throw_exception_if_locked();
		$this->files[$name] = $value;
	}
	
	/**
	 * Set an array of FILES variables
	 * 
	 * This will not replace all the existing FILES values, but will
	 * overwrite any that were already set.
	 * 
	 * @param array $array
	 * @throws PhractalRequestComponentLockedException
	 */
	public function set_files_array(array $array)
	{
		$this->throw_exception_if_locked();
		$this->files = array_merge($this->files, $array);
	}
	
	/**
	 * Delete a FILES variable
	 * 
	 * @param string $name
	 */
	public function del_files($name)
	{
		$this->throw_exception_if_locked();
		unset($this->files[$name]);
	}
	
	/**
	 * Get a FILES variable
	 * 
	 * If the variable isn't found, $default will be returned.
	 * If $default is null, an exception will be thrown.
	 * 
	 * @param string $name
	 * @param mixed $default
	 * @throws PhractalRequestComponentVariableNotFoundException
	 */
	public function get_files($name, $default = null)
	{
		if (isset($this->files[$name]))
		{
			return $this->files[$name];
		}
		elseif ($default !== null)
		{
			return $default;
		}
		else
		{
			throw new PhractalRequestComponentVariableNotFoundException('FILES::' . $name);
		}
	}
	
	/**
	 * Get all of the FILES variables in an associative array.
	 * 
	 * @return array
	 */
	public function get_all_files()
	{
		return $this->files;
	}
	
	/**
	 * Check to see if a FILES variable exists
	 * 
	 * @param string $name
	 * @return bool
	 */
	public function check_files($name)
	{
		return isset($this->files[$name]);
	}
}
