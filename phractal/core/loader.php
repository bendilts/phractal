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
		static $suffix_map = array(
			'Controller' => 'controller/controllers',
			'Component'  => 'controller/components',
			'Driver'     => 'model/drivers',
			'Model'      => 'model/models',
			'Record'     => 'model/records',
			'Manager'    => 'model/managers',
			'Helper'     => 'view/helpers',
			'Renderer'   => 'view/renderers',
		);
		
		$core = substr($classname, 0, 8) === 'Phractal';
		$base = $core ? PATH_PHRACTAL : PATH_APP;
		
		if ($core)
		{
			$classname = substr($classname, 8);
		}
		
		foreach ($suffix_map as $suffix => $path)
		{
			$suffix_length = strlen($suffix);
			if (substr($classname, -$suffix_length) === $suffix)
			{
				// TODO: This doesn't account for subclasses like MysqlDriver, ApcCacheComponent, etc.
				$classname = substr($classname, 0, strlen($classname) - $suffix_length);
				$underscored = Phractal::get_instance()->get_inflector()->underscore($classname);
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
