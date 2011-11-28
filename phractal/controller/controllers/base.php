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
 * Thrown when an extension is found in a route that isn't mapped to a content type
 */
class PhractalBaseControllerNoExtensionMappingException extends PhractalNameException {}

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
	 * @throws PhractalBaseControllerNoExtensionMappingException
	 */
	public function __construct(PhractalRequestComponent $request, PhractalResponseComponent $response)
	{
		parent::__construct();
		
		$app = PhractalApp::get_instance();
		
		$route = $request->get_matched_route();
		$inflector = $app->get_inflector();
		
		$this->request = $request;
		$this->response = $response;
		
		$this->view = $app->get_loader()->instantiate('Base', 'View', array(
			'web/' . $request->get_extension() . '/' . $inflector->underscore($route['controller']) . '/' . $inflector->underscore($route['action']),
		));
		
		$this->set_content_type_from_extension($request->get_extension());
	}
	
	/**
	 * Set the content type on the response based on the
	 * extension passed in.
	 * 
	 * The mapping of extension -> content type can be
	 * overridden by the configuration variable named
	 * 'map.extension.content-type'
	 * 
	 * @param string $extension
	 * @throws PhractalBaseControllerNoExtensionMappingException
	 */
	protected function set_content_type_from_extension($extension)
	{
		$extensions = PhractalApp::get_instance()->get_config()->get('map.extension.content-type', array());
		
		if (!isset($extensions[$extension]))
		{
			throw new PhractalBaseControllerNoExtensionMappingException($extension);
		}
		
		$this->response->add_header_name_value('Content-Type', $extensions[$extension]);
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
	 * 
	 * This function will NEVER contain anything useful in
	 * PhractalBaseController. It is NOT necessary to call
	 * parent::before_action() in your override.
	 */
	protected function before_action() {}
	
	/**
	 * Callback after the action is called
	 * 
	 * This function will NEVER contain anything useful in
	 * PhractalBaseController. It is NOT necessary to call
	 * parent::after_action() in your override.
	 * 
	 * This is the last chance to stop view rendering.
	 */
	protected function after_action() {}
	
	/**
	 * Callback before the view is rendered
	 * 
	 * This function will NEVER contain anything useful in
	 * PhractalBaseController. It is NOT necessary to call
	 * parent::before_view() in your override.
	 */
	protected function before_view() {}
	
	/**
	 * Callback after the run function is finished
	 * 
	 * This function will NEVER contain anything useful in
	 * PhractalBaseController. It is NOT necessary to call
	 * parent::after_run() in your override.
	 */
	protected function after_run() {}
}
