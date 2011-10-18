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
 * Script start time.
 * 
 * DON'T CHANGE THIS VALUE
 * 
 * @var float
 */
define('START_TIME', microtime(true));

/**
 * Script start memory
 * 
 * DON'T CHANGE THIS VALUE
 * 
 * @var int
 */
define('START_MEMORY', memory_get_usage(true));

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

/**
 * The PHP runtime being used.
 * @var string cli|web
 */
define('RUNTIME', defined('STDIN') ? 'cli' : 'web');

// ------------------------------------------------------------------------

require_once(PATH_PHRACTAL . '/config/bootstrap.php');

// ------------------------------------

/**
 * Benchmark
 * 
 * Replace this class with a subclass of PhractalBenchmark
 * to customize benchmarking
 */
$benchmark = new PhractalBenchmark();
Phractal::set_benchmark($benchmark);

// ------------------------------------

/**
 * Inflector
 * 
 * Replace this class with a subclass of PhractalInflector
 * to customize inflecting
 */
$inflector = new PhractalInflector();
Phractal::set_inflector($inflector);

// ------------------------------------

/**
 * Config
 * 
 * Replace this class with a subclass of PhractalConfig
 * to customize configuration management.
 * 
 * Config files can be stacked on top of eachother. When
 * a config variable is requested, the config class will
 * go through each element in the stack and return the
 * first instance it finds.
 * 
 * <example>
 * // app/config/config.php
 * $config->set('myvar', 1);
 * 
 * // app/config/staging.php
 * $config->set('myvar', 2);
 * 
 * // app_webroot/bootstrap.php (THIS FILE)
 * $config->load_file('config');
 * $config->load_file('staging');
 * 
 * // later in execution
 * $config->get('myvar') === 2
 * </example>
 */
$config = new PhractalConfig();
$config->load_file(PATH_PHRACTAL . '/config/config');
$config->load_file('config');
Phractal::set_config($config);

// ------------------------------------

/**
 * Logger
 * 
 * Replace this class with a subclass of PhractalLogger
 * to customize logging.
 * 
 * The PhractalLogger can send logs to file as well as HTTP
 * headers for web runtimes. HTTP headers will be skipped
 * in cli runtimes.
 * 
 * To log to a file, use $logger->register_file and pass in
 * the name of the file and the log levels that should go
 * into that file.
 * 
 * To send logs to the screen, use $logger->register_screen and
 * pass in the name of the group and the log levels that should
 * be sent to the screen.
 * 
 * To log to the HTTP client using headers, use
 * $logger->register_header and pass in the name of the header
 * and the log levels that should be sent.
 */
$logger = new PhractalLogger();
$logger->register_file('error',       PhractalLogger::LEVEL_CRITICAL | PhractalLogger::LEVEL_ERROR);
$logger->register_file('warning',     PhractalLogger::LEVEL_NOTICE   | PhractalLogger::LEVEL_WARNING);
$logger->register_file('trace',       PhractalLogger::LEVEL_DEBUG    | PhractalLogger::LEVEL_INFO);
$logger->register_file('benchmark',   PhractalLogger::LEVEL_BENCHMARK);
$logger->register_header('benchmark', PhractalLogger::LEVEL_ALL);
$logger->register_screen('onscreen',  PhractalLogger::LEVEL_CRITICAL | PhractalLogger::LEVEL_ERROR);
Phractal::set_logger($logger);

// ------------------------------------

/**
 * Loader
 * 
 * Replace this class with a subclass of PhractalLoader
 * to customize autoloading.
 * 
 * The loader registered here will be used to autoload
 * missing classes.
 */
$loader = new PhractalLoader();
Phractal::set_loader($loader);

// ------------------------------------

/**
 * ErrorHandler
 * 
 * Replace this class with a subclass of PhractalErrorHandler
 * to customize error handling
 * 
 * The error handler registered here will be used to
 * catch errors and exceptions from the PHP runtime.
 */
$error_handler = new PhractalErrorHandler();
Phractal::set_error_handler($error_handler);

// ------------------------------------

/**
 * Dispatcher
 * 
 * Replace this class with a subclass of PhractalDispatcher
 * to customize dispatching
 */
$dispatcher = new PhractalDispatcher();
Phractal::set_dispatcher($dispatcher);

// ------------------------------------------------------------------------

/**
 * Main function
 * 
 * Each entry point should define their own request, and then
 * pass it in to this function.
 * 
 * @param PhractalRequestComponent $request
 */
function main($request)
{
	$benchmark  = Phractal::get_benchmark();
	$logger     = Phractal::get_logger();
	$dispatcher = Phractal::get_dispatcher();
	
	$benchmark->mark_from_script_start('global', 'startup');
	
	$main_benchmark = $benchmark->start('global', 'main');
	$response = $dispatcher->dispatch($request);
	$benchmark->stop($main_benchmark);
	
	// you probably only need 1 of these
	$benchmark->log_all();
	//$benchmark->log_all_groups();
	
	$logger->write_logs_to_browser();
	$response->send_to_client();
}
