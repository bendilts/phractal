<?php if (!defined('PATH_WEBROOT')) { exit('no access'); }
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
 * Constant for checking whether phractal has been loaded.
 * @var bool
 */
define('PHRACTAL', true);

// ------------------------------------------------------------------------

require_once(PATH_PHRACTAL . '/core/object.php');
require_once(PATH_PHRACTAL . '/core/exception.php');
require_once(PATH_PHRACTAL . '/core/config.php');
require_once(PATH_PHRACTAL . '/core/inflector.php');
require_once(PATH_PHRACTAL . '/core/registry.php');
require_once(PATH_PHRACTAL . '/core/error_handler.php');
require_once(PATH_PHRACTAL . '/core/loader.php');
require_once(PATH_PHRACTAL . '/core/benchmark.php');
require_once(PATH_PHRACTAL . '/core/logger.php');
require_once(PATH_PHRACTAL . '/core/dispatcher.php');
require_once(PATH_PHRACTAL . '/core/phractal.php');
