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
 * Thrown when no match can be found for a request.
 */
class PhractalRouterComponentNoMatchException extends PhractalException {}

// ------------------------------------------------------------------------

/**
 * Thrown when the request has not been matched yet, but a function
 * requires it.
 */
class PhractalRouterComponentNotMatchedException extends PhractalException {}

// ------------------------------------------------------------------------

/**
 * Thrown when the request has not been matched yet, but a function
 * requires it.
 */
class PhractalRouterComponentNamedRouteNotFoundException extends PhractalNameException {}

// ------------------------------------------------------------------------

/**
 * Router Component
 *
 * Determines the controller and action that should handle
 * a request.
 */
class PhractalRouterComponent extends PhractalBaseComponent
{
	/**
	 * The request object
	 * 
	 * @var PhractalRequestComponent
	 */
	protected $request;
	
	/**
	 * The route that was matched
	 * 
	 * @var string
	 */
	protected $matched_route;
	
	/**
	 * Constructor
	 * 
	 * @param PhractalRequestComponent $request
	 */
	public function __construct(PhractalRequestComponent $request)
	{
		parent::__construct();
		
		$this->request = $request;
	}
	
	/**
	 * Force the router to match a route by name
	 * 
	 * @param string $route_name
	 * @throws PhractalRouterComponentNamedRouteNotFoundException
	 */
	public function force_match_by_name($route_name)
	{
		$this->matched_route = null;
		
		$config = PhractalApp::get_instance()->get_config();
		$routes = $config->get('route.table');
		if (!isset($routes[$route_name]))
		{
			throw new PhractalRouterComponentNamedRouteNotFoundException($route_name);
		}
		
		$route = $routes[$route_name];
		
		$request_method    = $this->request->get_method();
		$request_extension = $this->request->get_extension();
		$request_path      = $this->request->get_path();
		$named_vars = array();
		
		// override the extension if not present
		if ($request_extension === null && isset($route['no_extension']))
		{
			$route_extension = $route['no_extension'];
		}
		else
		{
			$route_extension = $request_extension;
		}
		
		PhractalApp::get_instance()->get_logger()->core_debug('Force matched route ' . $route_name);
		$this->matched_route = $route;
		$this->request->set_matched_route($route, $route_name);
		$this->request->set_extension($route_extension);
		$this->request->set_force_matched(true);
		
		// add other named params
		if (isset($route['extra_named']))
		{
			$named_vars = array_merge($route['extra_named'], $named_vars);
		}
		
		// set router variables on the request object
		$this->request->set_router_array($named_vars);
	}
	
	/**
	 * Process the request
	 * 
	 * Determines the controller, action, and
	 * anything else needed to route the request.
	 * Also sets the route-defined variables
	 * on the request object.
	 * 
	 * The first route that matches will be used.
	 * 
	 * @throws PhractalRouterComponentNoMatchException
	 */
	public function match()
	{
		$this->matched_route = null;
		
		$request_method    = $this->request->get_method();
		$request_extension = $this->request->get_extension();
		$request_path      = $this->request->get_path();
		
		// get all path parts
		$request_uri_parts = array_merge(array_filter(explode('/', $request_path)));
		$request_uri_part_count = count($request_uri_parts);
		
		// loop through each route looking for a match
		$config = PhractalApp::get_instance()->get_config();
		$routes = $config->get('route.table');
		$matched_route = null;
		foreach ($routes as $route_name => $route)
		{
			// check request method
			if (isset($route['methods']) && !in_array($request_method, $route['methods'], true))
			{
				continue;
			}
			
			// check runtime
			if (isset($route['runtimes']) && !in_array(RUNTIME, $route['runtimes'], true))
			{
				continue;
			}
			
			// get the extension of the request, supposing this route matches
			if ($request_extension === null && isset($route['no_extension']))
			{
				$route_extension = $route['no_extension'];
			}
			else
			{
				$route_extension = $request_extension;
			}
			
			// check the extension
			if (isset($route['extensions']) && !in_array($route_extension, $route['extensions'], true))
			{
				continue;
			}
			
			// check uri part count matches
			$route_uri_parts = array_merge(array_filter(explode('/', $route['path'])));
			$route_uri_part_count = count($route_uri_parts);
			if ($route_uri_part_count !== $request_uri_part_count)
			{
				continue;
			}
			
			// check all parts are the same, pull out variables
			$named_vars = array();
			for ($part_index = 0; $part_index < $request_uri_part_count; $part_index++)
			{
				$route_part = $route_uri_parts[$part_index];
				$request_part = $request_uri_parts[$part_index];
				
				if (strpos($route_part, '{') === false || strpos($route_part, '}') === false)
				{
					if ($route_part !== $request_part)
					{
						// jump to the next route
						continue 2;
					}
				}
				else
				{
					$request_part_length = strlen($request_part);
					$request_part_index = 0;
					
					$current_var_name  = null;
					$current_var_value = null;
					
					$route_part_length = strlen($route_part);
					$route_part_index = 0;
					$route_part_char = $route_part[$route_part_index];
					
					if ($route_part_char === '{')
					{
						$current_var_name = '';
						$current_var_value = '';
						$route_part_index++;
						$route_part_char = $route_part[$route_part_index];
						while ($route_part_char !== '}' && $route_part_index < $route_part_length)
						{
							$current_var_name .= $route_part_char;
							$route_part_index++;
							$route_part_char = $route_part[$route_part_index];
						}
						
						if ($route_part_char !== '}')
						{
							// jump to the next route
							continue 2;
						}
						
						$route_part_index++;
						$route_part_char = $route_part_index < $route_part_length ? $route_part[$route_part_index] : 'cant match this';
					}
					
					for ( ; $request_part_index < $request_part_length; $request_part_index++)
					{
						$request_part_char = $request_part[$request_part_index];
						
						// check to see if the current var is finished parsing
						if ($route_part_char === $request_part_char)
						{
							$route_part_index++;
							$route_part_char = $route_part_index < $route_part_length ? $route_part[$route_part_index] : 'cant match this';
							
							if ($current_var_name !== null)
							{
								$named_vars[$current_var_name] = $current_var_value;
								
								$current_var_name  = null;
								$current_var_value = null;
							}
							
							if ($route_part_char === '{')
							{
								$current_var_name = '';
								$current_var_value = '';
								$route_part_index++;
								$route_part_char = $route_part[$route_part_index];
								while ($route_part_char !== '}' && $route_part_index < $route_part_length)
								{
									$current_var_name .= $route_part_char;
									$route_part_index++;
									$route_part_char = $route_part[$route_part_index];
								}
								
								if ($route_part_char !== '}')
								{
									// jump to the next route
									continue 3;
								}
								
								$route_part_index++;
								$route_part_char = $route_part_index < $route_part_length ? $route_part[$route_part_index] : 'cant match this';
							}
						}
						elseif ($current_var_name !== null)
						{
							$current_var_value .= $request_part_char;
						}
						else
						{
							// jump to the next route
							continue 3;
						}
					}
					
					if ($route_part_index !== $route_part_length)
					{
						// jump to the next route
						continue 2;
					}
					
					if ($current_var_name !== null)
					{
						$named_vars[$current_var_name] = $current_var_value;
					}
				}
			}
			
			// validate named params
			if (isset($route['regex']))
			{
				foreach ($route['regex'] as $name => $regex)
				{
					if (!isset($named_vars[$name]) || !preg_match($regex, $named_vars[$name]))
					{
						// jump to the next route
						continue 2;
					}
				}
			}
			
			// MATCH FOUND!
			PhractalApp::get_instance()->get_logger()->core_debug('Matched route ' . $route_name);
			$this->matched_route = $route;
			$this->request->set_matched_route($route, $route_name);
			$this->request->set_extension($route_extension);
			$this->request->set_force_matched(false);
			
			// add other named params
			if (isset($route['extra_named']))
			{
				$named_vars = array_merge($route['extra_named'], $named_vars);
			}
			
			// set router variables on the request object
			$this->request->set_router_array($named_vars);
			
			// return after the first match
			break;
		}
		
		if ($this->matched_route === null)
		{
			throw new PhractalRouterComponentNoMatchException();
		}
	}
	
	/**
	 * Get the class name of the controller that should be used
	 * to process the request.
	 * 
	 * @return string
	 * @throws PhractalRouterComponentNotMatchedException
	 */
	public function get_controller()
	{
		if ($this->matched_route === null)
		{
			throw new PhractalRouterComponentNotMatchedException();
		}
		
		return $this->matched_route['controller'];
	}
	
	/**
	 * Get the name of the action to call on the controller.
	 * 
	 * @return string
	 * @throws PhractalRouterComponentNotMatchedException
	 */
	public function get_action()
	{
		if ($this->matched_route === null)
		{
			throw new PhractalRouterComponentNotMatchedException();
		}
		
		return $this->matched_route['action'];
	}
	
	/**
	 * Returns true when a route has been matched
	 * 
	 * @return string
	 */
	public function matched()
	{
		return $this->matched_route !== null;
	}
}
