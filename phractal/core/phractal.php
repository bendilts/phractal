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
	private $num_contexts = 0;
	
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
	);
	
	/**
	 * Constructor
	 * 
	 * This class is a singleton. Use Phractal::get_instance instead.
	 */
	public function __construct()
	{
		$this->push_context();
	}
	
	/**
	 * Destructor
	 */
	public function __destruct()
	{
		$this->pop_context();
	}
	
	/**
	 * Push a new context on the stack
	 */
	private function push_context()
	{
		$this->num_contexts++;
		array_push($this->contexts, array(
			'registry' => new PhractalRegistry(),
		));
	}
	
	/**
	 * Pop the current context off the stack
	 */
	private function pop_context()
	{
		$this->num_contexts--;
		array_pop($this->contexts);
	}
	
	/**
	 * Get a variable from the current context by name
	 * 
	 * @param string $name
	 * @return mixed
	 */
	private function get_from_current_context($name)
	{
		return $this->contexts[$this->num_contexts - 1][$name];
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
	 * @param PhractalBenchmark $inflector
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
	 * @param PhractalLogger $inflector
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
