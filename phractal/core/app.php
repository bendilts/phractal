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
 * Thrown when no app singleton has been registered.
 */
class PhractalAppNoAppSingletonRegisteredException extends PhractalException {}

// ------------------------------------------------------------------------

/**
 * Thrown when the app singleton is registered more than once.
 */
class PhractalAppSingletonAlreadyRegisteredException extends PhractalException {}

// ------------------------------------------------------------------------

/**
 * Phractal App Class
 *
 * Parent class for the App singleton
 */
abstract class PhractalApp extends PhractalObject
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
		
		$this->create_global_context();
	}
	
	/**
	 * Create the global context.
	 * 
	 * This function is called by the PhractalApp::__construct function.
	 */
	abstract protected function create_global_context();
	
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
	 * Create a new cache object from the name of the config
	 * that will be passed to it.
	 * 
	 * @param string $config_name
	 * @return PhractalCacheComponent
	 */
	public function cache_factory($config_name)
	{
		return $this->contexts[$this->context_index]->ensure_with_closure('Cache.' . $config_name, function() use ($config_name) {
			$configs = PhractalApp::get_instance()->get_config()->get('cache.configs');
			
			if (!isset($configs[$config_name]))
			{
				return null;
			}
			
			$cache_config = $configs[$config_name];
			return PhractalApp::get_instance()->get_loader()->instantiate($cache_config['engine'] . 'Cache', 'Component', array($cache_config));
		});
	}
	
	/**
	 * Get the singleton instance
	 * 
	 * @return PhractalApp
	 * @throws PhractalAppNoAppSingletonException
	 */
	public static function get_instance()
	{
		if (self::$instance === null)
		{
			throw new PhractalAppNoAppSingletonException();
		}
		
		return self::$instance;
	}
	
	/**
	 * Register the singleton instance
	 * 
	 * @param PhractalApp $instance
	 * @throws PhractalAppSingletonAlreadyRegisteredException
	 */
	protected static function register_app_singleton(PhractalApp $instance)
	{
		if (self::$instance !== null)
		{
			throw new PhractalAppSingletonAlreadyRegisteredException();
		}
		
		self::$instance = $instance;
	}
}
