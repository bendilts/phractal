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
 * Thrown when trying to set a config value outside of the
 * load_* functions
 */
class PhractalConfigNotLoadingException extends PhractalException {}

// ------------------------------------------------------------------------

/**
 * Thrown when nothing has been loaded, and access is attempted.
 */
class PhractalConfigNothingLoadedException extends PhractalException {}

// ------------------------------------------------------------------------

/**
 * Thrown when a file cannot be loaded.
 */
class PhractalConfigCannotLoadFileException extends PhractalNameException {}

// ------------------------------------------------------------------------

/**
 * Thrown when a config is not set, but is accessed.
 */
class PhractalConfigNoValueFoundException extends PhractalNameException {}

// ------------------------------------------------------------------------

/**
 * Config Class
 *
 * Handles configuration.
 */
class PhractalConfig extends PhractalObject
{
	/**
	 * Configuration stack. Each entry contains
	 * a set of configuration values.
	 * 
	 * @var array
	 */
	protected $stack = array();
	
	/**
	 * Index of the current config stack frame
	 * 
	 * @var int
	 */
	protected $stack_index = -1;
	
	/**
	 * True when the configuration is being loaded
	 * 
	 * @var bool
	 */
	protected $loading = false;
	
	/**
	 * Push a new config entry on the stack
	 */
	protected function push($config = array())
	{
		array_push($this->stack, $config);
		$this->stack_index++;
	}
	
	/**
	 * Pop the most recent config entry from the stack
	 * 
	 * @throws PhractalConfigNothingLoadedException
	 */
	protected function pop()
	{
		$this->ensure_config_loaded();
		array_pop($this->stack);
		$this->stack_index--;
	}
	
	/**
	 * Make sure at least one config entry is loaded
	 * on the config stack. If not, throw an exception.
	 * 
	 * @throws PhractalConfigNothingLoadedException
	 */
	protected function ensure_config_loaded()
	{
		if ($this->stack_index === -1)
		{
			throw new PhractalConfigNothingLoadedException();
		}
	}
	
	/**
	 * Load a configuration file, creating a new entry
	 * on the config stack
	 * 
	 * @param string $filename Either an absolute path or a path relative to app/config. No .php extension
	 * @throws PhractalConfigCannotLoadFileException
	 */
	public function load_file($filename)
	{
		$token = Phractal::get_benchmark()->start('config', basename($filename));
		
		$this->push();
		$filename = $filename . '.php';
		
		if ($filename[0] !== '/')
		{
			$filename = PATH_APP . '/config/' . $filename;
		}
		
		if (!file_exists($filename) || !is_file($filename) || !is_readable($filename))
		{
			throw new PhractalConfigCannotLoadFileException($filename);
		}
		
		// makes config files look nicer ($config->set instead of $this->set)
		$config = $this;
		
		$this->loading = true;
		require($filename);
		$this->loading = false;
		
		Phractal::get_benchmark()->stop($token);
	}
	
	/**
	 * Load a set of values from an array
	 * 
	 * @param array $values
	 */
	public function load_array($values)
	{
		$this->push($values);
	}
	
	/**
	 * Unload the most recently loaded configuration file.
	 * 
	 * @throws PhractalConfigNothingLoadedException
	 */
	public function unload_last()
	{
		$this->pop();
	}
	
	/**
	 * Check to see if a variable has been set.
	 * 
	 * @param string $name
	 * @return bool
	 */
	public function check($name)
	{
		for ($i = $this->stack_index; $i >= 0; $i--)
		{
			if (isset($this->stack[$name]))
			{
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Get the value of a configuration parameter.
	 * If multiple files have been loaded with multiple
	 * values for the same configuration parameter,
	 * then the most recently loaded file takes precedence.
	 * 
	 * If no value is found in any stack frame, the default
	 * will be returned. If the default is null, an exception
	 * will be thrown
	 * 
	 * @param string $name
	 * @param mixed $default If the variable isn't set, return this value instead.
	 * @return mixed
	 * @throws PhractalConfigNoValueFoundException
	 * @throws PhractalConfigNothingLoadedException
	 */
	public function get($name, $default = null)
	{
		$this->ensure_config_loaded();
		
		for ($i = $this->stack_index; $i >= 0; $i--)
		{
			$config = $this->stack[$i];
			if (isset($config[$name]))
			{
				return $config[$name];
			}
		}
		
		if ($default !== null)
		{
			return $default;
		}
		
		throw new PhractalConfigNoValueFoundException($name);
	}
	
	/**
	 * Set a configuration parameter in the current
	 * stack reference.
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @throws PhractalConfigNotLoadingException
	 * @throws PhractalConfigNothingLoadedException
	 */
	public function set($name, $value)
	{
		if (!$this->loading) { throw new PhractalConfigNotLoadingException(); }
		$this->ensure_config_loaded();
		
		$this->stack[$this->stack_index][$name] = $value;
	}
}
