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

/**
 * The recommended way to fill this, and all other, configuration file
 * is to use PHP's autoprepend. The autoprepend file should exist outside
 * of the code checkout. Because of it's sensitive nature (usernames,
 * hostnames, passwords, etc), it should never be committed to any source
 * code control like subversion or git.
 * 
 * The reason why this is recommended is because it ensures configuration
 * stays on the machine and doesn't change during releases. It also ensures
 * that nobody overwrites the production configuration values.
 * 
 * <example>
 * // php.ini
 * auto_prepend_file = /path/to/prepend.php
 * 
 * // vhost.conf (instead of php.ini)
 * php_value auto_prepend_file /path/to/prepend.php
 * 
 * // /path/to/prepend.php
 * $autoprepend['myvalue'] = 'whatever';
 * 
 * // config.php (THIS FILE)
 * global $autoprepend;
 * $config->set('myvalue', $autoprepend['myvalue']);
 * </example>
 * 
 * @link http://www.php.net/auto_prepend_file
 */

/**
 * Log file path
 * 
 * All logs will be placed in this directory. The directory
 * will not be created, nor will it have its permissions
 * changed by Phractal. You must create it and set its
 * permissions before running.
 * 
 * This value does NOT have a trailing slash.
 * 
 * @var string
 */
$config->set('log.file.path', PATH_APP . '/tmp/logs');

/**
 * Log file extension
 * 
 * All logs created by the logger will have this extension.
 * 
 * This value does NOT contain the period separating the
 * log file name and the log file extension. It contains
 * only the extension (ie 'log').
 * 
 * @var string
 */
$config->set('log.file.extension', 'log');

/**
 * Environment
 * 
 * This is not currently used by the Phractal core, but
 * is a common configuration directive. This is good
 * to have for your own application's code. It can have
 * any value, but suggested ones are 'sandbox', 'production',
 * 'staging', 'testing', 'development', etc.
 * 
 * @var string
 */
$config->set('environment', 'development');
