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

$request = Phractal::get_loader()->instantiate('Request', 'Component', array($_SERVER['argv'][1], $_SERVER['argv'][2]));
$response = Phractal::get_dispatcher()->dispatch($request);
$response->send_to_client();

