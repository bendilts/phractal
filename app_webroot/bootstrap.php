<?php if (!defined('ENTRANCE')) { exit('no access'); }
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
 * Set the environment to run in. This environment can be anything you choose.
 * Examples are:
 * 
 * - production
 * - development
 * - staging
 * - testing
 * 
 * @var string
 */
define('ENVIRONMENT', 'production');

/**
 * Absolute path to the webroot of the application.
 * Does NOT contain a trailing slash.
 * @var string
 */
define('PATH_WEBROOT', dirname(__FILE__));

/**
 * Absolute path to the application directory.
 * Does NOT contain a trailing slash.
 * @var string
 */
define('PATH_APP', dirname(dirname(__FILE__)) . '/app');

/**
 * Absolute path to the phractal framework directory.
 * Does NOT contain a trailing slash.
 * @var string
 */
define('PATH_PHRACTAL', dirname(dirname(__FILE__)) . '/phractal');

// ------------------------------------------------------------------------

// require phractal core
require_once(PATH_PHRACTAL . '/config/bootstrap.php');

// set global context items (autoloader, error handler, logger, etc)
$phractal = Phractal::get_instance();

$config = new PhractalConfig();
$config->load_file('config');
$phractal->set_config($config);

$loader = new PhractalLoader();
$phractal->set_loader($loader);

$error_handler = new PhractalErrorHandler();
$phractal->set_error_handler($error_handler);

$inflector = new PhractalInflector();
$phractal->set_inflector($inflector);

$benchmark = new PhractalBenchmark();
$phractal->set_benchmark($benchmark);

$logger = new PhractalLogger();
$phractal->set_logger($logger);
