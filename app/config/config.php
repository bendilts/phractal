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
 * The recommended way to fill this, and all other, configuration file
 * is to use PHP's autoprepend. The autoprepend file should exist outside
 * of the code checkout. Because of it's sensitive nature (usernames,
 * hostnames, passwords, etc), it should never be committed to any source
 * code control like subversion or git.
 * 
 * You should not use the prepend for any non-sensitive files, like
 * routes.
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

// ------------------------------------------------------------------------
// COMMON CONFIGURATION
// ------------------------------------------------------------------------

/**
 * Site Maintenance
 * 
 * Set to true to show the maintenance route to all
 * requesters.
 * 
 * @var bool
 */
$config->set('site.maintenance', false);

/**
 * Encryption Configuration
 * 
 * Each configuration is named (the key). The Encrypt class takes
 * a configuration name as a parameter, so the names are important.
 * 
 * Each configuration contains these keys:
 *   @param string key The encryption key. This can be any length.
 *   @param string salt Salt to prepend the encrypted value with.
 *   @param string iv Initialization vector. This will be a somewhat
 *                    random string, but the string length is determined
 *                    by the cipher and mode being used. Use this code to
 *                    get a random string of the correct length whenever
 *                    the cipher or mode are changed.
 *                    <code>
 *                    $iv = mcrypt_create_iv(mcrypt_get_iv_size(<CIPHER>, <MODE>), MCRYPT_RAND);
 *                    </code>
 *   @param string cipher One of the MCRYPT_* constants
 *   @param string mode One of the MCRYPT_MODE_* constants
 *   @param bool base64 True to base64 encode/decode on encrypt/decrypt.
 *   @param bool serialize True to serialize values before encryption and
 *                         unserialize values after decryption. This is nice
 *                         for objects or arrays.
 * 
 * @var array
 */
$config->set('encryption', array(
	'default' => array(
		'key' => 'CHANGE ME!!! this should be different in dev and production',
		'salt' => 'CHANGE ME!!! this should be different in dev and production',
		'iv' => '32 characters...................',
		'cipher' => MCRYPT_RIJNDAEL_256,
		'mode' => MCRYPT_MODE_CBC,
		'base64' => true,
		'serialize' => true,
	),
	'double-encrypt' => array(
		'key' => 'CHANGE ME!!! this should be different in dev and production',
		'salt' => 'CHANGE ME!!! this should be different in dev and production',
		'iv' => 'only 8 c',
		'cipher' => MCRYPT_3DES,
		'mode' => MCRYPT_MODE_OFB,
		'base64' => false,
		'serialize' => true,
	),
));

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

/**
 * Extension -> Content Type Mapping
 * 
 * Mapping of route extensions (json, html, etc) to content
 * types.
 * 
 * If no mapping exists for an allowed extension, an exception
 * will be thrown.
 * 
 * @var array
 */
$config->set('map.extension.content-type', array(
	'js'    => 'text/javascript',
	'json'  => 'application/json',
	'css'   => 'text/css',
	'html'  => 'text/html',
	'txt'   => 'text/plain',
	'csv'   => 'text/plain',
	'xhtml' => 'application/xhtml',
	'xml'   => 'application/xml',
	'rss'   => 'application/rss+xml',
	'atom'  => 'application/atom+xml',
	'amf'   => 'application/x-amf',
	'pdf'   => 'application/pdf',
	'zip'   => 'application/x-zip',
	'tar'   => 'application/x-tar',
));

/**
 * Accepted proxy servers
 * 
 * HTTP clients (browsers and others) can spoof the
 * X-Forwarded-For to steal sessions and cookies that
 * are IP specific.
 * 
 * This value can be an array of server IP addresses,
 * which will be accepted as valid proxy servers to get
 * the correct client IP address.
 * 
 * This value can also be a boolean. If the value is true,
 * all proxies will be accepted all the time (very insecure).
 * If the value is false, no proxies will ever be accepted.
 * 
 * If you are using HTTPS without an SSL enabled load
 * balancer, false is a good choice.
 * 
 * @var array|boolean
 */
$config->set('proxy.accept', array(
	'1.1.1.1',
	'2.2.2.2',
));

// ------------------------------------------------------------------------
// CACHING CONFIGURATION
// ------------------------------------------------------------------------

/**
 * Caching configurations
 * 
 * Each key is the name of a cache configuration. Each value
 * is the details of that configuration. The details for each
 * configuration will depend on the engine being used.
 * 
 * All configurations will take the following parameters:
 *     @param string engine Engine to use. This will correspond exactly to the classname of the engine
 *                          being used. To use the MemcachedCacheComponent, the engine will be 'Memcached' (case
 *                          sensitive).
 *     @param int ttl       Default Time to Live, in seconds, for all keys.
 *     @param string prefix Prefix to prepend to all keys.
 * 
 * Memcached engine:
 *     @param array servers List of servers to connect to. Each server is an array of host, port, and weight.
 * 
 * Apc engine:
 *     (no extra params)
 * 
 * @var array
 */
$config->set('cache.configs', array(
	'cache1' => array(
		'engine' => 'Apc',
		'ttl'    => 1800,
		'prefix' => 'myapp',
	),
	'cache2' => array(
		'engine'  => 'Memcached',
		'ttl'     => 1800,
		'prefix'  => 'myapp',
		'servers' => array(
			array('memcache1.mydomain.com', 11211, 1),
			array('memcache2.mydomain.com', 11211, 1),
		),
	),
));

// ------------------------------------------------------------------------
// ROUTING CONFIGURATION
// ------------------------------------------------------------------------

/**
 * Basepath of routes
 * 
 * The basepath of the route is whatever URI is required
 * to get to the webroot of this phractal installation.
 * 
 * If your DocumentRoot is at /var/www/html, and this
 * installation is located at /var/www/html/some/other/folders,
 * then this value should be /some/other/folders, assuming
 * that index.php and cron.php are located in the 'folders'
 * folder.
 * 
 * @var string
 */
$config->set('route.base', '/');

/**
 * Specifies a route name to use when a 404 error occurs.
 * 
 * The route specified here should not contain uri parameters, validation,
 * or valid extensions. It must accept all extensions and be as flexible as
 * possible, as it will accept all 404 errors.
 * 
 * This route must have the no_extension parameter in the routing table,
 * because some urls won't have routes, but they will need to be redirected
 * here anyway.
 * 
 * @var string
 */
$config->set('route.error.404.name', 'error404');

/**
 * Specifies a route name to use when a 500 error occurs.
 * 
 * The route specified here should not contain uri parameters, validation,
 * or valid extensions. It must accept all extensions and be as flexible as
 * possible, as it will accept all 500 errors.
 * 
 * This route must have the no_extension parameter in the routing table,
 * because some urls won't have routes, but they will need to be redirected
 * here anyway.
 * 
 * @var string
 */
$config->set('route.error.500.name', 'error500');

/**
 * Specifies a route name to use when site.maintenance config is true.
 * 
 * The route specified here should not contain uri parameters, validation,
 * or valid extensions. It must accept all extensions and be as flexible as
 * possible, as it will accept all requests when the site is in maintenance
 * mode.
 * 
 * This route must have the no_extension parameter in the routing table,
 * because some urls won't have routes, but they will need to be redirected
 * here anyway.
 * 
 * @var string
 */
$config->set('route.site.maintenance.name', 'sitedown');

/**
 * Routing Table
 * 
 * This value must be an array. The keys for this routing table must be the names of the
 * associated routes. The values must be associative arrays with the following keys:
 * 
 *   - string  path          (required) Request path that matches this route (no extension, no query string).
 *                           The path must begin with a '/'. It can contain static parts as well as
 *                           named variables. A named variable is used like this:
 *                           /blogs/{userid}-{blogid}/{pagenum}
 *                           In this example, there are 3 named variables: userid, blogid, and pagenum.
 *                           /blogs/matthew-barlocker-17/5 does NOT match this example route.
 *                           These will be set in the request object after routing.
 *   
 *   - string  controller    (required) Classname of the controller (without 'Controller') to use
 *                           when processing the route.
 *   
 *   - string  action        (required) Name of the method on the controller to use when processing
 *                           the route.
 *   
 *   - array   extensions    (optional) Array of allowed extensions. If absent, all extensions
 *                           will be allowed.
 *   
 *   - array   methods       (optional) Array of methods that can match the route. HTTP methods
 *                           (GET, POST, etc) are going to be used most commonly, but all strings
 *                           are allowed. This enables routes to be restricted to cron jobs or
 *                           internal requests. If not specified, all methods will be allowed.
 *   
 *   - string  no_extension  (optional) If no extension is given, use this one. If no extension is
 *                           provided, and no_extension == null, then the route will not
 *                           be matched.
 *   
 *   - array   runtimes      (optional) Array of runtimes to allow access to. If not defined, all
 *                           runtimes will be allowed access.
 *   
 *   - array   extra_named   (optional) Extra named variables to be added to the request object.
 *   
 *   - array   regex         (optional) Regular expression validations to be applied to all named
 *                           variables found in the url. If any regex doesn't match, the route
 *                           will not match.
 * 
 * @var array
 */
$config->set('route.table', array(
	'home' => array(
		'path'         => '/',
		'methods'      => array('GET'),
		'no_extension' => 'html',
		'controller'   => 'Home',
		'action'       => 'index',
		'extensions'   => array('html', 'htm'),
	),
	'maintenance' => array(
		'path'         => '/cron/maintenance',
		'methods'      => array('CRON'),
		'runtimes'     => array('cli'),
		'controller'   => 'Maintenance',
		'action'       => 'doit',
	),
	'admin.sidebar' => array(
		'path'         => '/admin/sidebar',
		'methods'      => array('INTERNAL'),
		'controller'   => 'Admin',
		'action'       => 'sidebar',
		'no_extension' => 'html',
		'extensions'   => array('html'),
		'extra_named'  => array(
			'var1' => 'abc',
			'var2' => 'def',
		),
	),
	'user.profile' => array(
		'path'         => '/users/{userid}-{abc}/profile/{profiletype}',
		'methods'      => array('GET', 'POST'),
		'controller'   => 'UserProfile',
		'action'       => 'get_user_profile',
		'extensions'   => array('json', 'xml'),
		'regex'        => array(
			'userid'      => '/^\d+$/',
			'profiletype' => '/^account|password|history|picture$/',
		),
	),
	'error404' => array(
		'path'         => '/errors/404',
		'controller'   => 'Error',
		'action'       => 'error404',
		'no_extension' => 'html',
	),
	'error500' => array(
		'path'         => '/errors/500',
		'controller'   => 'Error',
		'action'       => 'error500',
		'no_extension' => 'html',
	),
	'sitedown' => array(
		'path'         => '/sitedown',
		'controller'   => 'Error',
		'action'       => 'sitedown',
		'no_extension' => 'html',
	),
));
