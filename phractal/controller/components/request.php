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
 * Thrown when a bad code is used when unlocking the request.
 */
class PhractalRequestComponentBadUnlockCodeException extends PhractalException {}

// ------------------------------------------------------------------------

/**
 * Request Component
 *
 * Contains all request parameters. This includes POST,
 * GET, FILES, COOKIE, SERVER, etc.
 */
class PhractalRequestComponent extends PhractalBaseComponent
{
	/**
	 * Variable source constants
	 * 
	 * @var string
	 */
	const VARIABLES_POST     = 'P';
	const VARIABLES_GET      = 'G';
	const VARIABLES_COOKIE   = 'C';
	const VARIABLES_FILES    = 'F';
	const VARIABLES_ENV      = 'E';
	const VARIABLES_SERVER   = 'S';
	const VARIABLES_ROUTER   = 'R';
	
	/**
	 * True when the client initiated this request. False when
	 * this is an internal request
	 */
	protected $client_initiated;
	
	/**
	 * True when this request has been locked
	 * 
	 * @var mixed Null when not locked, int otherwise
	 */
	protected $locked = null;
	
	/**
	 * True when the request was force matched to some other
	 * route than it matched. 404 errors are good examples.
	 * 
	 * @var bool
	 */
	protected $force_matched = false;
	
	/**
	 * Request method
	 * 
	 * This can have any string value, but will commonly have
	 * GET, POST, etc. It will help determine the
	 * route to use.
	 * 
	 * @var string
	 */
	protected $method;
	
	/**
	 * The query string.
	 * 
	 * This is part of the uri after the '?'
	 * 
	 * @var string
	 */
	protected $query;
	
	/**
	 * The query path.
	 * 
	 * This is part of the uri leading up to the
	 * extension or query string.
	 * 
	 * @var string
	 */
	protected $path;
	
	/**
	 * The query extension
	 * 
	 * This is part of the uri.
	 * 
	 * @var string
	 */
	protected $extension;
	
	/**
	 * The matched route information
	 * 
	 * @var array
	 */
	protected $matched_route;
	
	/**
	 * The name of the route that was matched
	 * 
	 * @var string
	 */
	protected $matched_route_name;
	
	/**
	 * Order and use of variables
	 * 
	 * @var string
	 */
	protected $default_variables_order = 'RSECFPG';
	
	/**
	 * IP Address where the request came from
	 * 
	 * @var string
	 */
	protected $ip = null;
	
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
	 * ROUTER variables
	 * 
	 * The matched route can have variables associated
	 * with it. Those variables are put here.
	 * 
	 * @var array
	 */
	protected $router = array();
	
	/**
	 * RAW input, from the body of the request.
	 * 
	 * @var string
	 */
	protected $raw;
	
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
		
		$config = PhractalApp::get_instance()->get_config();
		$base = $config->get('route.base');
		$base_length = strlen($base);
		if (substr($uri, 0, $base_length) === $base)
		{
			$uri = substr($uri, $base_length);
		}
		
		if (empty($uri) || $uri[0] !== '/')
		{
			$uri = '/' . $uri;
		}
		
		$query_start = strpos($uri, '?');
		if ($query_start !== false)
		{
			$this->query = substr($uri, $query_start + 1);
			$uri = substr($uri, 0, $query_start);
		}
		
		$last_slash = strpos($uri, '/');
		$period = strpos($uri, '.', $last_slash);
		if ($period !== false)
		{
			$this->extension = substr($uri, $period + 1);
			$uri = substr($uri, 0, $period);
		}
		
		$this->path = $uri;
	}
	
	/**
	 * Throws an exception if the request is locked
	 * 
	 * @throws PhractalRequestComponentLockedException
	 */
	protected function throw_exception_if_locked()
	{
		if ($this->locked !== null)
		{
			throw new PhractalRequestComponentLockedException();
		}
	}
	
	/**
	 * Return true when the client initiated this request.
	 * 
	 * @return bool
	 */
	public function get_client_initiated()
	{
		return $this->client_initiated;
	}
	
	/**
	 * Return true when the system initiated this request
	 * as a dependency of another request
	 * 
	 * @return bool
	 */
	public function get_system_initiated()
	{
		return !$this->client_initiated;
	}
	
	/**
	 * Set whether this request is client initiated.
	 * 
	 * @param bool $client_initiated
	 * @throws PhractalRequestComponentLockedException
	 */
	public function set_client_initiated($client_initiated)
	{
		$this->throw_exception_if_locked();
		$this->client_initiated = $client_initiated;
	}
	
	/**
	 * Lock the request.
	 * 
	 * @return int Unlock code
	 * @throws PhractalRequestComponentLockedException
	 */
	public function lock()
	{
		$this->throw_exception_if_locked();
		$this->locked = rand(100, 999);
		return $this->locked;
	}
	
	/**
	 * Unlock the request using the unlock code from the lock() function
	 * 
	 * @param int $code
	 * @throws PhractalRequestComponentBadUnlockCodeException
	 */
	public function unlock($code)
	{
		if ($this->locked !== $code)
		{
			throw new PhractalRequestComponentBadUnlockCodeException();
		}
		
		$this->locked = null;
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
		$uri = $this->path;
		
		if ($this->extension !== null)
		{
			$uri .= '.' . $this->extension;
		}
		
		if ($this->query !== null)
		{
			$uri .= '?' . $this->query;
		}
		
		return $uri;
	}
	
	/**
	 * Get the extension, or null if there isn't one.
	 * 
	 * @return string
	 */
	public function get_extension()
	{
		return $this->extension;
	}
	
	/**
	 * Get the matched route
	 * 
	 * @return array
	 */
	public function get_matched_route()
	{
		return $this->matched_route;
	}
	
	/**
	 * Get the name of the matched route
	 * 
	 * @return string
	 */
	public function get_matched_route_name()
	{
		return $this->matched_route_name;
	}
	
	/**
	 * Set the matched route
	 * 
	 * @param array $route
	 * @param string $matched_route_name
	 * @throws PhractalRequestComponentLockedException
	 */
	public function set_matched_route(array $matched_route, $matched_route_name)
	{
		$this->throw_exception_if_locked();
		$this->matched_route = $matched_route;
		$this->matched_route_name = $matched_route_name;
	}
	
	/**
	 * Change the request extension.
	 * 
	 * @param string $extension
	 * @throws PhractalRequestComponentLockedException
	 */
	public function set_extension($extension)
	{
		$this->throw_exception_if_locked();
		$this->extension = $extension;
	}
	
	/**
	 * Get the query string
	 * 
	 * @return string
	 */
	public function get_query()
	{
		return $this->query;
	}
	
	/**
	 * Get the query path
	 * 
	 * @return string
	 */
	public function get_path()
	{
		return $this->path;
	}
	
	/**
	 * Returns true when the route was a force match,
	 * like a 404 error
	 *
	 * @return bool
	 */
	public function get_force_matched()
	{
		return $this->force_matched;
	}
	
	/**
	 * Set whether the request was force matched
	 * 
	 * @param bool $force_matched
	 * @throws PhractalRequestComponentLockedException
	 */
	public function set_force_matched($force_matched)
	{
		$this->throw_exception_if_locked();
		$this->force_matched = $force_matched;
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
	 * Get the client's IP address.
	 * 
	 * Proxy / load balancer IP addresses are taken into
	 * consideration along with the proxy.* configuration
	 * parameters.
	 * 
	 * @return string Null if none found
	 */
	public function get_ip()
	{
		if ($this->ip === null)
		{
			$proxies = PhractalApp::get_instance()->get_config()->get('proxy.accept', false);
			
			$forwarded_for = $this->get_server('HTTP_X_FORWARDED_FOR', false);
			$remote_addr   = $this->get_server('REMOTE_ADDR', false);
			$client_ip     = $this->get_server('CLIENT_IP', false);
			
			if ($forwarded_for && $proxies === true)
			{
				$this->ip = $forwarded_for;
			}
			elseif ($forwarded_for && $remote_addr && in_array($remote_addr, (array) $proxies, true))
			{
				$this->ip = $forwarded_for;
			}
			elseif ($remote_addr)
			{
				$this->ip = $remote_addr;
			}
			elseif ($client_ip)
			{
				$this->ip = $client_ip;
			}
			elseif (RUNTIME === 'cli')
			{
				$this->ip = '127.0.0.1';
			}
			else
			{
				$this->ip = false;
			}
			
			if ($this->ip && strpos($this->ip, ',') !== false)
			{
				$this->ip = trim(end(explode(',', $this->ip)));
			}
		}
		
		return $this->ip;
	}
	
	/**
	 * Return whether this request is over HTTPS or not.
	 * 
	 * @return bool
	 */
	public function is_ssl()
	{
		$https = $this->get_server('HTTPS', false);
		return $https && $https !== 'off';
	}
	
	/**
	 * Return whether this request is from an AJAX call or not.
	 * 
	 * @return bool
	 */
	public function is_ajax()
	{
		return $this->get_server('HTTP_X_REQUESTED_WITH', false) === 'XMLHttpRequest';
	}
	
	/**
	 * Set the default variables order.
	 * 
	 * This will set the default ordering of variables
	 * returned from get_all_variables when no ordering
	 * is specified.
	 * 
	 * To get the GET and POST variables, use 'GP'. If
	 * you want the POST variables to take precedence
	 * over the GET variables (should any naming conflicts
	 * occur), then use 'PG'.
	 * 
	 * @param string $order
	 */
	public function set_default_variables_order($order)
	{
		$this->default_variables_order = $order;
	}
	
	/**
	 * Get the default order of variables being used
	 * 
	 * @return string
	 */
	public function get_default_variables_order()
	{
		return $this->default_variables_order;
	}
	
	/**
	 * Get all the input variables as an array with
	 * the key being the self::VARIABLES_* constant
	 * that represents them
	 * 
	 * @return array
	 */
	protected function get_variables_by_input()
	{
		return array(
			self::VARIABLES_GET    => $this->get,
			self::VARIABLES_POST   => $this->post,
			self::VARIABLES_COOKIE => $this->cookie,
			self::VARIABLES_SERVER => $this->server,
			self::VARIABLES_ENV    => $this->env,
			self::VARIABLES_FILES  => $this->files,
			self::VARIABLES_ROUTER => $this->router,
		);
	}
	
	/**
	 * Get a variable from the inputs using the order
	 * specified. If no order is specified, use the
	 * default_variables_order defined on this object.
	 * 
	 * If the variable isn't found, then $default will be returned.
	 * If $default is null, an exception will be thrown.
	 * 
	 * @param string $name
	 * @param string $order
	 * @param mixed $default
	 * @return mixed
	 * @throws PhractalRequestComponentVariableNotFoundException
	 */
	public function get_variable($name, $order = null, $default = null)
	{
		if ($order === null)
		{
			$order = $this->default_variables_order;
		}
		
		$lookup = $this->get_variables_by_input();
		
		$length = strlen($order);
		for ($i = 0; $i < $length; $i++)
		{
			$char = $order[$i];
			$array = $lookup[$char];
			if (isset($array[$name]))
			{
				return $array[$name];
			}
		}
		
		if ($default !== null)
		{
			return $default;
		}
		
		throw new PhractalRequestComponentVariableNotFoundException($order . '::' . $name);
	}
	
	/**
	 * Get all variables using the order specified.
	 * If no order is specified
	 * 
	 * @param string $order
	 */
	public function get_all_variables($order = null)
	{
		if ($order === null)
		{
			$order = $this->default_variables_order;
		}
		
		$lookup = $this->get_variables_by_input();
		
		$all = array();
		$length = strlen($order);
		for ($i = 0; $i < $length; $i++)
		{
			$char = $order[$i];
			$all = array_merge($array, $all);
		}
		
		return $all;
	}
	
	/**
	 * Get a REQUEST variable as it would normally be gotten
	 * from the $_REQUEST super global.
	 * 
	 * The same precedence is used here as would be used
	 * in the $_REQUEST super global.
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
		return $this->get_variable(ini_get('request_order'), $name, $default);
	}
	
	/**
	 * Get all the REQUEST variables. This is equivalent to
	 * accessing the $_REQUEST super global.
	 * 
	 * The same precedence is used here as would be used
	 * in the $_REQUEST super global.
	 * 
	 * @return array
	 */
	public function get_all_request()
	{
		return $this->get_all_variables(ini_get('request_order'));
	}
	
	/**
	 * Set a GET variable
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @throws PhractalRequestComponentLockedException
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
	 * @throws PhractalRequestComponentLockedException
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
	 * @throws PhractalRequestComponentLockedException
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
	 * @throws PhractalRequestComponentLockedException
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
	 * Set a COOKIE variable
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @throws PhractalRequestComponentLockedException
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
	 * @throws PhractalRequestComponentLockedException
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
	 * @throws PhractalRequestComponentLockedException
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
	 * @throws PhractalRequestComponentLockedException
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
	 * @throws PhractalRequestComponentLockedException
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
	 * @throws PhractalRequestComponentLockedException
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
	 * @throws PhractalRequestComponentLockedException
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
	 * @throws PhractalRequestComponentLockedException
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
	
	/**
	 * Set a ROUTER variable
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @throws PhractalRequestComponentLockedException
	 */
	public function set_router($name, $value)
	{
		$this->throw_exception_if_locked();
		$this->router[$name] = $value;
	}
	
	/**
	 * Set an array of ROUTER variables
	 * 
	 * This will not replace all the existing ROUTER values, but will
	 * overwrite any that were already set.
	 * 
	 * @param array $array
	 * @throws PhractalRequestComponentLockedException
	 */
	public function set_router_array(array $array)
	{
		$this->throw_exception_if_locked();
		$this->router = array_merge($this->router, $array);
	}
	
	/**
	 * Delete a ROUTER variable
	 * 
	 * @param string $name
	 * @throws PhractalRequestComponentLockedException
	 */
	public function del_router($name)
	{
		$this->throw_exception_if_locked();
		unset($this->router[$name]);
	}
	
	/**
	 * Get a ROUTER variable
	 * 
	 * If the variable isn't found, $default will be returned.
	 * If $default is null, an exception will be thrown.
	 * 
	 * @param string $name
	 * @param mixed $default
	 * @throws PhractalRequestComponentVariableNotFoundException
	 */
	public function get_router($name, $default = null)
	{
		if (isset($this->router[$name]))
		{
			return $this->router[$name];
		}
		elseif ($default !== null)
		{
			return $default;
		}
		else
		{
			throw new PhractalRequestComponentVariableNotFoundException('ROUTER::' . $name);
		}
	}
	
	/**
	 * Get all of the ROUTER variables in an associative array.
	 * 
	 * @return array
	 */
	public function get_all_router()
	{
		return $this->router;
	}
	
	/**
	 * Check to see if a ROUTER variable exists
	 * 
	 * @param string $name
	 * @return bool
	 */
	public function check_router($name)
	{
		return isset($this->router[$name]);
	}
	
	/**
	 * Set the raw data input
	 * 
	 * @param string $value
	 * @throws PhractalRequestComponentLockedException
	 */
	public function set_raw($value)
	{
		$this->throw_exception_if_locked();
		$this->raw = $value;
	}
	
	/**
	 * Get the raw data
	 * 
	 * @return string
	 */
	public function get_raw()
	{
		return $this->raw;
	}
	
	/**
	 * Delete the raw data
	 * 
	 * @throws PhractalRequestComponentLockedException
	 */
	public function del_raw()
	{
		$this->throw_exception_if_locked();
		$this->raw = null;
	}
	
	/**
	 * Check to see if raw data exists.
	 * 
	 * @return bool
	 */
	public function check_raw()
	{
		return $this->raw !== null;
	}
}
