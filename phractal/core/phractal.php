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
 * Thrown when multiple instances of the Phractal singleton
 * are created.
 */
class PhractalMultipleInstancesException extends PhractalException {}

// ------------------------------------------------------------------------

/**
 * Phractal Class
 *
 * Manages references to all objects in the current request. This
 * is the only singleton class.
 */
final class Phractal extends PhractalObject
{
	/**
	 * A stack of contexts and variables within contexts
	 * @var array
	 */
	private $contexts = array();
	
	/**
	 * The number of contexts on the stack
	 * @var int
	 */
	private $context_index = -1;
	
	/**
	 * The global context variables.
	 * @var array
	 */
	private $global = array(
		'loader'    => null,
		'error'     => null,
		'inflector' => null,
		'logger'    => null,
		'benchmark' => null,
		'config'    => null,
		'dispatch'  => null,
	);
	
	/**
	 * Constructor
	 * 
	 * This class is a singleton. Use Phractal::get_instance instead.
	 * 
	 * @throws PhractalMultipleInstancesException
	 */
	public function __construct()
	{
		parent::__construct();
		
		static $instance_count = 0;
		if (++$instance_count !== 1)
		{
			throw new PhractalMultipleInstancesException();
		}
	}
	
	/**
	 * Push a new context on the stack
	 */
	private function push_context()
	{
		$this->context_index++;
		array_push($this->contexts, array(
			'registry' => new PhractalRegistry(),
		));
	}
	
	/**
	 * Pop the current context off the stack
	 * 
	 * @throws PhractalNoContextException
	 */
	private function pop_context()
	{
		$this->ensure_context_exists();
		$this->context_index--;
		array_pop($this->contexts);
	}
	
	/**
	 * Make sure a context exists, or throw an exception
	 * 
	 * @throws PhractalNoContextException
	 */
	private function ensure_context_exists()
	{
		if ($this->context_index === -1)
		{
			throw new PhractalNoContextException();
		}
	}
	
	/**
	 * Get a variable from the current context by name
	 * 
	 * @param string $name
	 * @return mixed
	 * @throws PhractalNoContextException
	 */
	private function get_from_current_context($name)
	{
		$this->ensure_context_exists();
		return $this->contexts[$this->context_index][$name];
	}
	
	/**
	 * Get a variable from the global context by name
	 * 
	 * @param string $name
	 * @return mixed
	 */
	private function get_from_global_context($name)
	{
		return $this->global[$name];
	}
	
	/**
	 * Get the class registry from the current context
	 * 
	 * @return PhractalRegistry
	 * @throws PhractalNoContextException
	 */
	public function get_registry()
	{
		return $this->get_from_current_context('registry');
	}
	
	/**
	 * Get the Loader object
	 * 
	 * @return PhractalLoader
	 */
	public function get_loader()
	{
		return $this->get_from_global_context('loader');
	}
	
	/**
	 * Set the Loader object. Unregisters any previously set
	 * loader objects
	 * 
	 * @param PhractalLoader $loader
	 */
	public function set_loader(PhractalLoader $loader)
	{
		$current_loader = $this->get_loader();
		if (!is_null($current_loader))
		{
			$current_loader->unregister();
		}
		
		$this->global['loader'] = $loader;
		$loader->register();
	}
	
	/**
	 * Get the ErrorHandler object
	 * 
	 * @return PhractalErrorHandler
	 */
	public function get_error_handler()
	{
		return $this->get_from_global_context('error');
	}
	
	/**
	 * Set the ErrorHandler object. Unregisters any previously set
	 * error handler objects
	 * 
	 * @param PhractalErrorHandler $handler
	 */
	public function set_error_handler(PhractalErrorHandler $handler)
	{
		$current_handler = $this->get_error_handler();
		if (!is_null($current_handler))
		{
			$current_handler->unregister();
		}
		
		$this->global['error'] = $handler;
		$handler->register();
	}
	
	/**
	 * Get the Inflector object
	 * 
	 * @return PhractalInflector
	 */
	public function get_inflector()
	{
		return $this->get_from_global_context('inflector');
	}
	
	/**
	 * Set the Inflector object.
	 * 
	 * @param PhractalInflector $inflector
	 */
	public function set_inflector(PhractalInflector $inflector)
	{
		$this->global['inflector'] = $inflector;
	}
	
	/**
	 * Get the Benchmark object
	 * 
	 * @return PhractalBenchmark
	 */
	public function get_benchmark()
	{
		return $this->get_from_global_context('benchmark');
	}
	
	/**
	 * Set the Benchmark object.
	 * 
	 * @param PhractalBenchmark $benchmark
	 */
	public function set_benchmark(PhractalBenchmark $benchmark)
	{
		$this->global['benchmark'] = $benchmark;
	}
	
	/**
	 * Get the Logger object
	 * 
	 * @return PhractalLogger
	 */
	public function get_logger()
	{
		return $this->get_from_global_context('logger');
	}
	
	/**
	 * Set the Logger object.
	 * 
	 * @param PhractalLogger $logger
	 */
	public function set_logger(PhractalLogger $logger)
	{
		$this->global['logger'] = $logger;
	}
	
	/**
	 * Get the Config object
	 * 
	 * @return PhractalConfig
	 */
	public function get_config()
	{
		return $this->get_from_global_context('config');
	}
	
	/**
	 * Set the Config object.
	 * 
	 * @param PhractalConfig $config
	 */
	public function set_config(PhractalConfig $config)
	{
		$this->global['config'] = $config;
	}
	
	/**
	 * Get the Dispatcher object
	 * 
	 * @return PhractalDispatcher
	 */
	public function get_dispatcher()
	{
		return $this->get_from_global_context('dispatch');
	}
	
	/**
	 * Set the Dispatcher object.
	 * 
	 * @param PhractalDispatcher $config
	 */
	public function set_dispatcher(PhractalDispatcher $dispatcher)
	{
		$this->global['dispatch'] = $dispatcher;
	}
	
	/**
	 * Get the phractal instance
	 * 
	 * @return Phractal
	 */
	public static function &get_instance()
	{
		static $instance = null;
		if (!$instance)
		{
			$instance = new Phractal();
		}
		return $instance;
	}
}
