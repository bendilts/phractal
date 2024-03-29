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
 * Thrown when the loader is double registered or double unregistered.
 */
class PhractalLoaderRegistrationException extends PhractalException {}

// ------------------------------------------------------------------------

/**
 * Thrown when a file cannot be loaded.
 */
class PhractalLoaderCannotLoadFileException extends PhractalNameException {}

// ------------------------------------------------------------------------

/**
 * Thrown when a class cannot be instantiated.
 */
class PhractalLoaderNoSuchClassException extends PhractalNameException {}

// ------------------------------------------------------------------------

/**
 * Loader Class
 *
 * Including an autoload registration, this class is in charge
 * of loading all classes (excluding phractal core classes).
 */
class PhractalLoader extends PhractalObject
{
	/**
	 * When true, this object is one of the registered
	 * autoloaders in the PHP runtime.
	 * @var bool
	 */
	protected $registered = false;
	
	/**
	 * Mapping of Classname suffix to directories where the class might be found.
	 * 
	 * Each key is the classname suffix, and each value is the directory where the
	 * class might be found. Each directory is relative to the phractal or app
	 * directory (since they have identical subdirectories).
	 * 
	 * @var array
	 */
	protected $classname_suffix_map;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->classname_suffix_map = array(
			'Controller'     => 'controller/controllers',
			'Component'      => 'controller/components',
			'Driver'         => 'model/drivers',
			'Manager'        => 'model/managers',
			'Migration'      => 'model/migrations',
			'Model'          => 'model/models',
			'Record'         => 'model/records',
			'Helper'         => 'view/helpers',
			'View'           => 'view/views',
		);
	}
	
	/**
	 * Registers the autoload function on this class.
	 * 
	 * @throws PhractalLoaderRegistrationException
	 */
	public function register()
	{
		if ($this->registered)
		{
			throw new PhractalLoaderRegistrationException();
		}
		
		spl_autoload_register(array($this, 'autoload'), false, false);
		$this->registered = true;
	}
	
	/**
	 * Unregister the autoload function on this class.
	 * 
	 * @throws PhractalLoaderRegistrationException
	 */
	public function unregister()
	{
		if (!$this->registered)
		{
			throw new PhractalLoaderRegistrationException();
		}
		
		spl_autoload_unregister(array($this, 'autoload'));
		$this->registered = false;
	}
	
	/**
	 * Instantiate a class by name and type.
	 * 
	 * This class will prefer the classes from the application, but will
	 * also check the phractal core if the application does not have a
	 * class by that name.
	 * 
	 * The name is the name of the class, minus the type. For example,
	 * to load the UserController class, use this call:
	 * 
	 * instantiate('User', 'Controller');
	 * 
	 * If the constructor has arguments, those can be passed in as
	 * the argument in the last position (optional). Up to 5
	 * constructor arguments can be used.
	 * 
	 * instantiate('User', 'Controller', array('firstarg', 'secondarg'));
	 * 
	 * @param string $name
	 * @param string $type
	 * @param array $constructor_args
	 * @return PhractalObject
	 * @throws PhractalLoaderNoSuchClassException
	 */
	public function instantiate($name, $type, array $constructor_args = array())
	{
		$num_constructor_args = count($constructor_args);
		
		$app_name = $name . $type;
		$names = array($app_name);
		
		if (strpos($app_name, 'Phractal') !== 0)
		{
			$names[] = 'Phractal' . $app_name;
		}
		
		foreach ($names as $classname)
		{
			try
			{
				return call_constructor($classname, $constructor_args);
			}
			catch (Exception $e) {}
		}
		
		throw new PhractalLoaderNoSuchClassException($app_name);
	}
	
	/**
	 * Autoload a class based on name.
	 * 
	 * This function is the subject of spl_autoload_register. It does
	 * not work for:
	 *     - Phractal Core Classes
	 *     - Migration Classes
	 *     - Third Party Classes
	 * 
	 * @param string $classname Name of the class to load
	 * @throws PhractalLoaderCannotLoadFileException
	 */
	public function autoload($classname)
	{
		// short circuit an already loaded class. this can occur
		// when this function is called directly and when this
		// function is only one of the registered autoload
		// functions
		if (class_exists($classname)) { return; }
		
		PhractalApp::get_instance()->get_logger()->core_debug('Autoload ' . $classname);
		
		$core = substr($classname, 0, 8) === 'Phractal';
		$base = $core ? PATH_PHRACTAL : PATH_APP;
		
		if ($core)
		{
			$classname = substr($classname, 8);
		}
		
		foreach ($this->classname_suffix_map as $suffix => $path)
		{
			$suffix_length = strlen($suffix);
			if (substr($classname, -$suffix_length) === $suffix)
			{
				$classname = substr($classname, 0, strlen($classname) - $suffix_length);
				$underscored = PhractalApp::get_instance()->get_inflector()->underscore($classname);
				$filename = $base . '/' . $path . '/' . $underscored . '.php';
				
				if (!file_exists($filename) || !is_file($filename) || !is_readable($filename))
				{
					throw new PhractalLoaderCannotLoadFileException($filename);
				}
				
				require_once($filename);
				break;
			}
		}
	}
}
