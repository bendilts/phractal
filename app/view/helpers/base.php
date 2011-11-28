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
 * Base Helper
 *
 * App specific parent for all helpers.
 * 
 * Helpers are used in view templates to make creating templates
 * easier and for extracting more logic from them.
 */
abstract class BaseHelper extends PhractalBaseHelper
{
}
