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
 * Dispatcher Class
 *
 * Gathers inputs, determines route, calls controller,
 * and returns output.
 */
class PhractalDispatcher extends PhractalObject
{
	/**
	 * Puts all of the super globals in the request variables.
	 * Unsets the super globals so that the applications MUST
	 * use the request instance.
	 * 
	 * @param PhractalRequestComponent $request
	 */
	protected function grab_super_globals(PhractalRequestComponent $request)
	{
		$request->set_get_array($_GET);
		$request->set_post_array($_POST);
		$request->set_env_array($_ENV);
		$request->set_cookie_array($_COOKIE);
		$request->set_server_array($_SERVER);
		
		if (RUNTIME === 'web' && isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0)
		{
			$raw = file_get_contents('php://input');
			$request->set_raw($raw);
		}
		
		unset($_GET);
		unset($_POST);
		unset($_ENV);
		unset($_COOKIE);
		unset($_SERVER);
	}
	
	/**
	 * Dispatch a request
	 * 
	 * @param PhractalRequestComponent $request
	 */
	public function dispatch(PhractalRequestComponent $request)
	{
		$loader = Phractal::get_loader();
		$logger = Phractal::get_logger();
		$config = Phractal::get_config();
		
		Phractal::push_context();
		
		$initial_request = Phractal::num_contexts() === 1;
		if ($initial_request)
		{
			$this->grab_super_globals($request);
		}
		
		$request->set_client_initiated($initial_request);
		$router = $loader->instantiate('Router', 'Component', array($request));
		$response = $loader->instantiate('Response', 'Component', array($request));
		
		$is_404 = false;
		$is_500 = false;
		$unlock_code = null;
		
		if ($config->get('site.maintenance'))
		{
			try
			{
				$route_maintenance_name = $config->get('route.site.maintenance.name');
				$router->force_match_by_name($route_maintenance_name);
				
				$unlock_code = $request->lock();
				
				$controller = $loader->instantiate($router->get_controller(), 'Controller', array($request, $response));
				$controller->run();
			}
			catch (Exception $e)
			{
				$is_500 = true;
			}
		}
		else
		{
			try
			{
				$router->match();
			}
			catch (PhractalRouterComponentNoMatchException $e)
			{
				$is_404 = true;
			}
			
			try
			{
				if ($is_404)
				{
					$route404_name = $config->get('route.error.404.name');
					$router->force_match_by_name($route404_name);
				}
				
				$unlock_code = $request->lock();
				
				$controller = $loader->instantiate($router->get_controller(), 'Controller', array($request, $response));
				$controller->run();
			}
			catch (Exception $e)
			{
				if ($is_404)
				{
					$logger->error('A 404 error was found, but an internal error occurred.');
				}
				
				$is_500 = true;
			}
		}
		
		if ($is_500)
		{
			try
			{
				if ($unlock_code !== null)
				{
					$request->unlock($unlock_code);
					$unlock_code = null;
				}
				
				$route500_name = $config->get('route.error.500.name');
				$router->force_match_by_name($route500_name);
				
				$unlock_code = $request->lock();
				
				$controller = $loader->instantiate($router->get_controller(), 'Controller', array($request, $response));
				$controller->run();
			}
			catch (Exception $e)
			{
				$logger->error('A 500 error was found, but an internal error occurred.');
			}
		}
		
		Phractal::pop_context();
		
		return $response;
	}
}
