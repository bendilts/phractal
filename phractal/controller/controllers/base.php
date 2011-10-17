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
	protected $request;
	protected $response;
	
	public function __construct($request, $response)
	{
		parent::__construct();
		
		$this->request = $request;
		$this->response = $response;
	}
	
	public function run()
	{
		$this->response->set_body('Controller::run NOT IMPLEMENTED' . "\n");
	}
}
