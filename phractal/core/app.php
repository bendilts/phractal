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
 * Thrown when a key is not found in the current context.
 */
class PhractalAppKeyNotFoundInCurrentContextException extends PhractalNameException {}

// ------------------------------------------------------------------------

/**
 * Phractal App Class
 *
 * Manages references to all objects in the current request. This
 * is the only singleton class.
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
	 * The global context variables.
	 * @var array
	 */
	protected $global = array(
		'loader'    => null,
		'error'     => null,
		'inflector' => null,
		'logger'    => null,
		'benchmark' => null,
		'config'    => null,
		'dispatch'  => null,
	);
	
	/**
	 * Singleton instance
	 * 
	 * @var PhractalApp
	 */
	protected static $instance = null;
	
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
		array_push($this->contexts, array());
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
	 * Get an object from the current context by key.
	 * 
	 * If the key doesn't exist, use the loader to
	 * instantiate a new object, save, and return it.
	 * 
	 * @param string $key Key to use to get/set in the current context
	 * @param string $name Basename of the class (@see PhractalLoader::instantiate)
	 * @param string $type Type of the class (@see PhractalLoader::instantiate)
	 * @param array $constructor_args Constructor args (@see PhractalLoader::instantiate)
	 * @return mixed
	 * @throws PhractalLoaderNoSuchClassException
	 */
	public function get_or_instantiate_in_current_context($key, $classname, $type, array $constructor_args = array())
	{
		if (isset($this->contexts[$this->context_index][$key]))
		{
			return $this->contexts[$this->context_index][$key];
		}
		
		return self::get_loader()->instantiate($classname, $type, $constructor_args);
	}
	
	/**
	 * Get an object from the current context by key.
	 * 
	 * @param string $key
	 * @return mixed
	 * @throws PhractalAppKeyNotFoundInCurrentContextException
	 */
	public function get_in_current_context($key)
	{
		if ($this->context_index === -1)
		{
			throw new PhractalAppNoContextException();
		}
		
		if (isset($this->contexts[$this->context_index][$key]))
		{
			return $this->contexts[$this->context_index][$key];
		}
		
		throw new PhractalAppKeyNotFoundInCurrentContextException($key);
	}
	
	/**
	 * Set an object in the current context by key
	 * 
	 * @param string $key
	 * @param string $value
	 * @throws PhractalAppNoContextException
	 */
	public function set_in_current_context($key, $value)
	{
		if ($this->context_index === -1)
		{
			throw new PhractalAppNoContextException();
		}
		
		$this->contexts[$this->context_index][$key] = $value;
	}
	
	/**
	 * Check to see if a key exists in the current context
	 * 
	 * @param string $key
	 * @return bool
	 * @throws PhractalAppNoContextException
	 */
	public function check_in_current_context($key)
	{
		if ($this->context_index === -1)
		{
			throw new PhractalAppNoContextException();
		}
		
		return isset($this->contexts[$this->context_index][$key]);
	}
	
	/**
	 * Delete an object from the current context.
	 * 
	 * Ignores missing keys.
	 * 
	 * @param string $key
	 * @throws PhractalAppNoContextException
	 */
	public function delete_from_current_context($key)
	{
		if ($this->context_index === -1)
		{
			throw new PhractalAppNoContextException();
		}
		
		unset($this->contexts[$this->context_index][$key]);
	}
	
	/**
	 * Get the class registry from the current context
	 * 
	 * @return PhractalRegistry
	 * @throws PhractalAppNoContextException
	 */
	public function get_registry()
	{
		if ($this->context_index === -1)
		{
			throw new PhractalAppNoContextException();
		}
		
		return $this->contexts[$this->context_index]['registry'];
	}
	
	/**
	 * Get the Loader object
	 * 
	 * @return PhractalLoader
	 */
	public function get_loader()
	{
		return $this->global['loader'];
	}
	
	/**
	 * Set the Loader object. Unregisters any previously set
	 * loader objects
	 * 
	 * @param PhractalLoader $loader
	 */
	public function set_loader(PhractalLoader $loader)
	{
		$current_loader = self::get_loader();
		if ($current_loader !== null)
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
		return $this->global['error'];
	}
	
	/**
	 * Set the ErrorHandler object. Unregisters any previously set
	 * error handler objects
	 * 
	 * @param PhractalErrorHandler $handler
	 */
	public function set_error_handler(PhractalErrorHandler $handler)
	{
		$current_handler = self::get_error_handler();
		if ($current_handler !== null)
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
		return $this->global['inflector'];
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
		return $this->global['benchmark'];
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
		return $this->global['logger'];
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
		return $this->global['config'];
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
		return $this->global['dispatch'];
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
