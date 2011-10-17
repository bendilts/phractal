<?php if (!isset($_SERVER['REMOTE_ADDR'])) { exit('no access'); }
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

/**
 * Script start time.
 * 
 * DON'T CHANGE THIS VALUE
 * 
 * @var float
 */
define('START_TIME', microtime(true));

/**
 * The method of framework entrance.
 * 
 * DON'T CHANGE THIS VALUE
 * 
 * @var string
 */
define('ENTRANCE', 'index');

// ------------------------------------------------------------------------

require_once(dirname(__FILE__) . '/bootstrap.php');

// ------------------------------------------------------------------------

$request = Phractal::get_loader()->instantiate('Request', 'Component', array($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']));
$response = Phractal::get_dispatcher()->dispatch($request);
$response->send_to_client();

