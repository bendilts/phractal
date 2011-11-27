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
class PhractalAppNoContextException extends PhractalException {}

// ------------------------------------------------------------------------

/**
 * Phractal App Class
 *
 * The only singleton class in the project.
 */
class PhractalApp extends PhractalObject
{
	/**
	 * A stack of contexts and variables within contexts
	 * @var array
	 */
	protected $contexts = array();
	
	/**
	 * The number of contexts on the stack
	 * @var int
	 */
	protected $context_index = -1;
	
	/**
	 * The global context (doesn't change between
	 * push/pop calls).
	 * 
	 * @var PhractalContext
	 */
	protected $global_context;
	
	/**
	 * Singleton instance
	 * 
	 * @var PhractalApp
	 */
	protected static $instance = null;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->global_context = new PhractalContext();
	}
	
	/**
	 * Get the number of contexts in existence.
	 * 
	 * @return int
	 */
	public function num_contexts()
	{
		return $this->context_index + 1;
	}
	
	/**
	 * Push a new context on the stack
	 */
	public function push_context()
	{
		$this->context_index++;
		array_push($this->contexts, new PhractalContext());
	}
	
	/**
	 * Pop the current context off the stack
	 * 
	 * @throws PhractalAppNoContextException
	 */
	public function pop_context()
	{
		if ($this->context_index === -1)
		{
			throw new PhractalAppNoContextException();
		}
		
		$this->context_index--;
		array_pop($this->contexts);
	}
	
	/**
	 * Get the current context
	 * 
	 * @return PhractalContext
	 */
	public function get_context()
	{
		return $this->contexts[$this->context_index];
	}
	
	/**
	 * Get the Loader object
	 * 
	 * @return PhractalLoader
	 */
	public function get_loader()
	{
		return $this->global_context->get('loader');
	}
	
	/**
	 * Set the Loader object. Unregisters any previously set
	 * loader objects
	 * 
	 * @param PhractalLoader $loader
	 */
	public function set_loader(PhractalLoader $loader)
	{
		$current_loader = $this->global_context->get('loader', false);
		if ($current_loader !== false)
		{
			$current_loader->unregister();
		}
		
		$this->global_context->set('loader', $loader);
		$loader->register();
	}
	
	/**
	 * Get the ErrorHandler object
	 * 
	 * @return PhractalErrorHandler
	 */
	public function get_error_handler()
	{
		return $this->global_context->get('error');
	}
	
	/**
	 * Set the ErrorHandler object. Unregisters any previously set
	 * error handler objects
	 * 
	 * @param PhractalErrorHandler $handler
	 */
	public function set_error_handler(PhractalErrorHandler $handler)
	{
		$current_handler = $this->global_context->get('error', false);
		if ($current_handler !== false)
		{
			$current_handler->unregister();
		}
		
		$this->global_context->set('error', $handler);
		$handler->register();
	}
	
	/**
	 * Get the Inflector object
	 * 
	 * @return PhractalInflector
	 */
	public function get_inflector()
	{
		return $this->global_context->get('inflector');
	}
	
	/**
	 * Set the Inflector object.
	 * 
	 * @param PhractalInflector $inflector
	 */
	public function set_inflector(PhractalInflector $inflector)
	{
		$this->global_context->set('inflector', $inflector);
	}
	
	/**
	 * Get the Benchmark object
	 * 
	 * @return PhractalBenchmark
	 */
	public function get_benchmark()
	{
		return $this->global_context->get('benchmark');
	}
	
	/**
	 * Set the Benchmark object.
	 * 
	 * @param PhractalBenchmark $benchmark
	 */
	public function set_benchmark(PhractalBenchmark $benchmark)
	{
		$this->global_context->set('benchmark', $benchmark);
	}
	
	/**
	 * Get the Logger object
	 * 
	 * @return PhractalLogger
	 */
	public function get_logger()
	{
		return $this->global_context->get('logger');
	}
	
	/**
	 * Set the Logger object.
	 * 
	 * @param PhractalLogger $logger
	 */
	public function set_logger(PhractalLogger $logger)
	{
		$this->global_context->set('logger', $logger);
	}
	
	/**
	 * Get the Config object
	 * 
	 * @return PhractalConfig
	 */
	public function get_config()
	{
		return $this->global_context->get('config');
	}
	
	/**
	 * Set the Config object.
	 * 
	 * @param PhractalConfig $config
	 */
	public function set_config(PhractalConfig $config)
	{
		$this->global_context->set('config', $config);
	}
	
	/**
	 * Get the Dispatcher object
	 * 
	 * @return PhractalDispatcher
	 */
	public function get_dispatcher()
	{
		return $this->global_context->get('dispatch');
	}
	
	/**
	 * Set the Dispatcher object.
	 * 
	 * @param PhractalDispatcher $config
	 */
	public function set_dispatcher(PhractalDispatcher $dispatcher)
	{
		$this->global_context->set('dispatch', $dispatcher);
	}
	
	/**
	 * Get the singleton instance
	 * 
	 * @return PhractalApp
	 */
	public static function get_instance()
	{
		if (!self::$instance)
		{
			self::$instance = new PhractalApp();
		}
		
		return self::$instance;
	}
}
