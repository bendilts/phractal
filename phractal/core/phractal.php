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
 * Thrown when a context is required, but none exist.
 */
class PhractalNoContextException extends PhractalException {}

// ------------------------------------------------------------------------

/**
 * Phractal Class
 *
 * Manages references to all objects in the current request. This
 * is the only singleton class.
 */
final class Phractal
{
	/**
	 * A stack of contexts and variables within contexts
	 * @var array
	 */
	private static $contexts = array();
	
	/**
	 * The number of contexts on the stack
	 * @var int
	 */
	private static $context_index = -1;
	
	/**
	 * The global context variables.
	 * @var array
	 */
	private static $global = array(
		'loader'    => null,
		'error'     => null,
		'inflector' => null,
		'logger'    => null,
		'benchmark' => null,
		'config'    => null,
		'dispatch'  => null,
	);
	
	/**
	 * Get the number of contexts in existence.
	 * 
	 * @return int
	 */
	public static function num_contexts()
	{
		return self::$context_index + 1;
	}
	
	/**
	 * Push a new context on the stack
	 */
	public static function push_context()
	{
		self::$context_index++;
		array_push(self::$contexts, array());
	}
	
	/**
	 * Pop the current context off the stack
	 * 
	 * @throws PhractalNoContextException
	 */
	public static function pop_context()
	{
		if (self::$context_index === -1)
		{
			throw new PhractalNoContextException();
		}
		
		self::$context_index--;
		array_pop(self::$contexts);
	}
	
	/**
	 * Get the class registry from the current context
	 * 
	 * @return PhractalRegistry
	 * @throws PhractalNoContextException
	 */
	public static function get_registry()
	{
		if (self::$context_index === -1)
		{
			throw new PhractalNoContextException();
		}
		
		return self::$contexts[self::$context_index]['registry'];
	}
	
	/**
	 * Get the Loader object
	 * 
	 * @return PhractalLoader
	 */
	public static function get_loader()
	{
		return self::$global['loader'];
	}
	
	/**
	 * Set the Loader object. Unregisters any previously set
	 * loader objects
	 * 
	 * @param PhractalLoader $loader
	 */
	public static function set_loader(PhractalLoader $loader)
	{
		$current_loader = self::get_loader();
		if ($current_loader !== null)
		{
			$current_loader->unregister();
		}
		
		self::$global['loader'] = $loader;
		$loader->register();
	}
	
	/**
	 * Get the ErrorHandler object
	 * 
	 * @return PhractalErrorHandler
	 */
	public static function get_error_handler()
	{
		return self::$global['error'];
	}
	
	/**
	 * Set the ErrorHandler object. Unregisters any previously set
	 * error handler objects
	 * 
	 * @param PhractalErrorHandler $handler
	 */
	public static function set_error_handler(PhractalErrorHandler $handler)
	{
		$current_handler = self::get_error_handler();
		if ($current_handler !== null)
		{
			$current_handler->unregister();
		}
		
		self::$global['error'] = $handler;
		$handler->register();
	}
	
	/**
	 * Get the Inflector object
	 * 
	 * @return PhractalInflector
	 */
	public static function get_inflector()
	{
		return self::$global['inflector'];
	}
	
	/**
	 * Set the Inflector object.
	 * 
	 * @param PhractalInflector $inflector
	 */
	public static function set_inflector(PhractalInflector $inflector)
	{
		self::$global['inflector'] = $inflector;
	}
	
	/**
	 * Get the Benchmark object
	 * 
	 * @return PhractalBenchmark
	 */
	public static function get_benchmark()
	{
		return self::$global['benchmark'];
	}
	
	/**
	 * Set the Benchmark object.
	 * 
	 * @param PhractalBenchmark $benchmark
	 */
	public static function set_benchmark(PhractalBenchmark $benchmark)
	{
		self::$global['benchmark'] = $benchmark;
	}
	
	/**
	 * Get the Logger object
	 * 
	 * @return PhractalLogger
	 */
	public static function get_logger()
	{
		return self::$global['logger'];
	}
	
	/**
	 * Set the Logger object.
	 * 
	 * @param PhractalLogger $logger
	 */
	public static function set_logger(PhractalLogger $logger)
	{
		self::$global['logger'] = $logger;
	}
	
	/**
	 * Get the Config object
	 * 
	 * @return PhractalConfig
	 */
	public static function get_config()
	{
		return self::$global['config'];
	}
	
	/**
	 * Set the Config object.
	 * 
	 * @param PhractalConfig $config
	 */
	public static function set_config(PhractalConfig $config)
	{
		self::$global['config'] = $config;
	}
	
	/**
	 * Get the Dispatcher object
	 * 
	 * @return PhractalDispatcher
	 */
	public static function get_dispatcher()
	{
		return self::$global['dispatch'];
	}
	
	/**
	 * Set the Dispatcher object.
	 * 
	 * @param PhractalDispatcher $config
	 */
	public static function set_dispatcher(PhractalDispatcher $dispatcher)
	{
		self::$global['dispatch'] = $dispatcher;
	}
}
