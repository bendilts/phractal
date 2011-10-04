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
define('RUNTIME', isset($_SERVER['argc']) ? 'cli' : 'web');

// ------------------------------------------------------------------------

// require phractal core
require_once(PATH_PHRACTAL . '/config/bootstrap.php');
$phractal = Phractal::get_instance();

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
$config->load_file('config');
$phractal->set_config($config);

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
$phractal->set_logger($logger);

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
$phractal->set_loader($loader);

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
$phractal->set_error_handler($error_handler);

// ------------------------------------

/**
 * Inflector
 * 
 * Replace this class with a subclass of PhractalInflector
 * to customize inflecting
 */
$inflector = new PhractalInflector();
$phractal->set_inflector($inflector);

// ------------------------------------

/**
 * Benchmark
 * 
 * Replace this class with a subclass of PhractalBenchmark
 * to customize benchmarking
 */
$benchmark = new PhractalBenchmark();
$phractal->set_benchmark($benchmark);
