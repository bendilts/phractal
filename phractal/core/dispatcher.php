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
		Phractal::push_context();
		
		$initial_request = Phractal::num_contexts() === 1;
		if ($initial_request)
		{
			$this->grab_super_globals($request);
		}
		
		$router = $loader->instantiate('Router', 'Component', array($request));
		$router->match();
		$request->lock();
		$controller = $loader->instantiate($router->get_controller(), 'Controller', array($request));
		
		Phractal::set_in_current_context('request', $request);
		Phractal::set_in_current_context('router', $router);
		Phractal::set_in_current_context('controller', $controller);
		
		$controller->run();
		
		Phractal::pop_context();
	}
}
