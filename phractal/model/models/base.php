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
 * Model Base Class
 *
 * Presents an api to controllers and components to perform
 * atomic operations on the persistence layer. Every single
 * function needs to be atomic, or this layer is broken.
 */
abstract class PhractalBaseModel extends PhractalObject
{
}
