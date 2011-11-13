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
 * Controller Base Class
 *
 * Handles the flow of logic from request to response.
 */
abstract class PhractalBaseController extends PhractalObject
{
	/**
	 * The request object
	 * 
	 * @var PhractalRequestComponent
	 */
	protected $request;
	
	/**
	 * The response object
	 * 
	 * @var PhractalResponseComponent
	 */
	protected $response;
	
	/**
	 * The view
	 * 
	 * @var BaseView
	 */
	protected $view;
	
	/**
	 * Set to true to stop running the action
	 * 
	 * @var bool
	 */
	protected $run_stop = false;
	
	/**
	 * Constructor
	 * 
	 * @param PhractalRequestComponent $request
	 * @param PhractalResponseComponent $response
	 */
	public function __construct(PhractalRequestComponent $request, PhractalResponseComponent $response)
	{
		parent::__construct();
		
		$app = PhractalApp::get_instance();
		
		$this->request = $request;
		$this->response = $response;
		$this->view = $app->get_loader()->instantiate('Base', 'View');
		
		$route = $request->get_matched_route();
		$inflector = $app->get_inflector();
		
		// add the initial template web/<extension>/<controller>/<action>
		$this->view->unshift_template('web/' . $request->get_extension() . '/' . $inflector->underscore($route['controller']) . '/' . $inflector->underscore($route['action']));
	}
	
	/**
	 * Main function in the controller class. Calls the requested action,
	 * renders the view, and updates the response.
	 */
	public function run()
	{
		$this->before_action();
		if ($this->run_stop) { return; }
		
		$route = $this->request->get_matched_route();
		$this->dynamic_call($route['action']);
		if ($this->run_stop) { return; }
		
		$this->after_action();
		if ($this->run_stop) { return; }
		
		//
		// it is too late to stop rendering after the action has been performed.
		//
		
		$this->before_view();
		
		$body = $this->view->render();
		$this->response->set_body($body);
		
		$this->after_run();
	}
	
	/**
	 * Callback before the action is called
	 * 
	 * Authorization, HTTPS check, and HTTP method can all be validated
	 * and handled here.
	 */
	protected function before_action() {}
	
	/**
	 * Callback after the action is called
	 * 
	 * This is the last chance to stop view rendering.
	 */
	protected function after_action() {}
	
	/**
	 * Callback before the view is rendered
	 */
	protected function before_view() {}
	
	/**
	 * Callback after the run function is finished
	 */
	protected function after_run() {}
}
