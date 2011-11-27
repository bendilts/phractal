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
 * Encrypt Component
 *
 * Encrypts and Decrypts things.
 */
class PhractalEncryptComponent extends PhractalBaseComponent
{
	/**
	 * Encryption configurations
	 * 
	 * @var array
	 */
	protected $configs;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->configs = PhractalApp::get_instance()->get_config()->get('encryption');
	}
	
	/**
	 * Use an encryption configuration (specified in the config file)
	 * to encrypt a string.
	 * 
	 * @param mixed $value
	 * @param string $config_name
	 * @return string
	 */
	public function encrypt($value, $config_name = 'default')
	{
		return $this->encrypt_with_config($value, $this->configs[$config_name]);
	}
	
	/**
	 * Use an encryption configuration (specified in the config file)
	 * to decrypt a string
	 * 
	 * @param string $encrypted
	 * @param string $config_name
	 * @return mixed
	 */
	public function decrypt($encrypted, $config_name = 'default')
	{
		return $this->decrypt_with_config($encrypted, $this->configs[$config_name]);
	}
	
	/**
	 * Encrypt a value by using an encryption config, but change the key and salt
	 * 
	 * @param mixed $value
	 * @param string $key
	 * @param string $salt
	 * @param string $config_name
	 * @return string
	 */
	public function encrypt_alter_config($value, $key, $salt, $config_name = 'default')
	{
		return $this->encrypt_with_config($value, array_merge(
			$this->configs[$config_name],
			array(
				'key'  => $key,
				'salt' => $salt,
			)
		));
	}
	
	/**
	 * Decrypt a value by using an encryption config, but change the key.
	 * 
	 * @param string $encrypted
	 * @param string $key
	 * @param string $salt
	 * @param string $config_name
	 * @return mixed
	 */
	public function decrypt_alter_config($encrypted, $key, $salt, $config_name = 'default')
	{
		return $this->decrypt_with_config($encrypted, array_merge(
			$this->configs[$config_name],
			array(
				'key'  => $key,
				'salt' => $salt,
			)
		));
	}
	
	/**
	 * Encrypt a value
	 * 
	 * @param mixed $value
	 * @param array $config
	 * @return string
	 */
	public function encrypt_with_config($value, $config)
	{
		$encrypted = mcrypt_encrypt($config['cipher'],
		                            $config['key'],
		                            $config['salt'] . ($config['serialize'] ? serialize($value) : $value),
		                            $config['mode'],
		                            $config['iv']);
		
		return $config['base64'] ? base64_encode($encrypted) : $encrypted;
	}
	
	/**
	 * Decrypt an encrypted string
	 * 
	 * @param string $encrypted
	 * @param array $config
	 * @return mixed
	 */
	public function decrypt_with_config($encrypted, $config)
	{
		// the trim is necessary because mcrypt_decrypt can leave
		// extra whitespace on the end of the decrypted string.
		$decrypted = substr(rtrim(mcrypt_decrypt($config['cipher'],
		                                         $config['key'],
		                                         $config['base64'] ? base64_decode($encrypted) : $encrypted,
		                                         $config['mode'],
		                                         $config['iv'])),
		                    strlen($config['salt']));
		
		return $config['serialize'] ? unserialize($decrypted) : $decrypted;
	}
}
