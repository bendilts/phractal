<?php if (!isset($_SERVER['argc'])) { exit('no access'); }
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
 * The method of framework entrance.
 * 
 * DON'T CHANGE THIS VALUE
 * 
 * @var string
 */
define('ENTRANCE', 'cron');

// ------------------------------------------------------------------------

if ($_SERVER['argc'] !== 3)
{
	echo 'Usage: php ' . __FILE__ . ' <request method> <request uri>' . PHP_EOL;
	exit -1;
}

// ------------------------------------------------------------------------

require_once(dirname(__FILE__) . '/bootstrap.php');

// ------------------------------------------------------------------------

$request = PhractalApp::get_instance()->get_loader()->instantiate('Request', 'Component', array($_SERVER['argv'][1], $_SERVER['argv'][2]));
main($request);
