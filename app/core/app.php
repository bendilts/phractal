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
 * App Class
 *
 * The only singleton in the project. Manages request contexts
 * and factories.
 */
class App extends PhractalApp
{
	/**
	 * @see PhractalApp::create_global_context()
	 */
	protected function create_global_context()
	{
		$this->global_context = new PhractalContext();
	}
	
	/**
	 * Register the singleton instance
	 * 
	 * @return App
	 */
	public static function register()
	{
		$app = new App();
		self::register_app_singleton($app);
		
		return $app;
	}
}
