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
 * Checks the health of the phractal installation
 * and configuration
 */
class HealthController extends BaseController
{
	/**
	 * Check the health of the PHP environment
	 */
	public function check_environment()
	{
		// Check PHP version
		$this->view->set('php version', PHP_VERSION);
		$this->view->set('php version min', version_compare(PHP_VERSION, '5.3.0', '>'));
		
		// Ensure tmp exists and is writable
		$tmp = PATH_APP . '/tmp';
		$this->view->set('tmp dir exists', is_dir($tmp));
		$this->view->set('tmp dir writable', is_writable($tmp));
		$this->view->set('tmp dir readable', is_readable($tmp));
		$this->view->set('tmp dir executable', is_executable($tmp));
	}
	
	/**
	 * Check the existence and health of PHP extensions
	 */
	public function check_extensions()
	{
		// Ensure Memcached is loaded
		$this->view->set('memcached loaded', class_exists('Memcached'));
		
		// Ensure APC is loaded
		$this->view->set('apc loaded', function_exists('apc_add'));
		$this->view->set('apc enabled', ini_get('apc.enabled') === '1');
		$this->view->set('apc cli enabled', ini_get('apc.cli_enabled') === '1');
		
		// Ensure mcrypt is loaded
		$this->view->set('mcrypt loaded', function_exists('mcrypt_encrypt'));
	}
	
	/**
	 * Check all the caches for connectivity and configuration validity
	 */
	public function check_caches()
	{
		$configs = PhractalApp::get_instance()->get_config()->get('cache.configs');
		$caches = array();
		
		// make sure all keys are present for each configuration
		foreach ($configs as $name => $config)
		{
			$caches[$name] = array();
			
			if (!is_array($config) || !isset($config['engine']))
			{
				$caches[$name]['config'] = false;
				continue;
			}
			
			$rules = null;
			switch ($config['engine'])
			{
				case 'Apc':
					$rules = array(
						'ttl' => array(
							array('validate_isset'),
							array('validate_numeric'),
							array('validate_between', 0, 2592000),
						),
						'prefix' => array(
							array('validate_isset'),
							array('validate_type_string'),
						),
					);
					break;
					
				case 'Memcached':
					$rules = array(
						'ttl' => array(
							array('validate_isset'),
							array('validate_numeric'),
							array('validate_between', 0, 2592000),
						),
						'prefix' => array(
							array('validate_isset'),
							array('validate_type_string'),
						),
						'servers' => array(
							array('validate_isset'),
							array('validate_type_array'),
							array('validate_array_is_indexed'),
							array('subarray_each', array(
								0 => array(
									array('validate_isset'),
									array('validate_type_string'),
									array('validate_strlen_between', 1),
								),
								1 => array(
									array('validate_isset'),
									array('validate_numeric'),
									array('validate_between', 0, 65535),
								),
								2 => array(
									array('validate_isset'),
									array('validate_numeric'),
									array('validate_between', 0),
								),
							)),
						),
					);
					break;
			}
			
			$valid = false;
			if ($rules !== null)
			{
				$filter = PhractalApp::get_instance()->get_loader()->instantiate('InputFilter', 'Component', array($config));
				try
				{
					$filter->run($rules);
					$valid = true;
				}
				catch (PhractalInputFilterComponentFilterException $e) {var_dump($e->get_errors());}
			}
			
			$caches[$name]['config'] = $valid;
		}
		
		// connect to all caches and write a test variable
		foreach ($configs as $name => $config)
		{
			$cache = PhractalApp::get_instance()->cache_factory($name);
			$valid = $cache->set('phractal', 'phractal', 30) && $cache->get('phractal', false) === 'phractal';
			$caches[$name]['connection'] = $valid;
		}
		
		$this->view->set('caches', $caches);
	}
	
	/**
	 * Check to make sure all configured encryption algorithms are working 
	 */
	public function check_encryption()
	{
		// make sure all encryption configs work
		$encrypt = PhractalApp::get_instance()->get_loader()->instantiate('Encrypt', 'Component');
		$configs = PhractalApp::get_instance()->get_config()->get('encryption');
		$checks = array();
		
		foreach ($configs as $name => $config)
		{
			$start = 'phractal';
			$encrypted = $encrypt->encrypt($start, $name);
			$decrypted = $encrypt->decrypt($encrypted, $name);
			$checks[$name] = $start === $decrypted && $start !== $encrypted;
		}
		
		$this->view->set('keys', $checks);
	}
}
