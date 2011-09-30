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
 * Thrown when the error handler is double registered or double unregistered.
 */
class PhractalErrorHandlerRegistrationException extends PhractalException {}

// ------------------------------------------------------------------------

/**
 * Error Handler
 *
 * This class can handle exceptions and errors. It is the
 * default error and exception handler for Phractal, but
 * can be overridden.
 */
class PhractalErrorHandler extends PhractalObject
{
	/**
	 * When true, this object should contain the registered
	 * error and exception handler. At the very least,
	 * they are on the error/exception handling stack.
	 * @var bool
	 */
	protected $registered = false;
	
	/**
	 * Register this class's error and exception handlers
	 * 
	 * @throws PhractalErrorHandlerRegistrationException
	 */
	public function register()
	{
		if ($this->registered)
		{
			throw new PhractalErrorHandlerRegistrationException();
		}
		
		set_error_handler(array($this, 'handle_error'));
		set_exception_handler(array($this, 'handle_exception'));
		$this->registered = true;
	}
	
	/**
	 * Restore the previous error and exception handlers.
	 * 
	 * This function will succeed whether or not the current
	 * error handlers belong to this class. There is no
	 * validation on which handlers are currently registered.
	 * 
	 * @throws PhractalErrorHandlerRegistrationException
	 */
	public function unregister()
	{
		if (!$this->registered)
		{
			throw new PhractalErrorHandlerRegistrationException();
		}
		
		restore_error_handler();
		restore_exception_handler();
		$this->registered = false;
	}
	
	/**
	 * Handle an error.
	 * 
	 * @param int $errno Contains the level of the error raised.
	 * @param string $errstr Contains the error message.
	 * @param string $errfile Contains the filename that the error was raised in.
	 * @param int $errline Contains the line number the error was raised at.
	 * @param array $errcontext Points to the active symbol table at the point the error occurred.
	 *                          In other words, errcontext will contain an array of every variable
	 *                          that existed in the scope the error was triggered in. User error
	 *                          handler must not modify error context.
	 * @return bool If false is returned, the normal error handler continues.
	 */
	public function handle_error($errno, $errstr, $errfile, $errline, array $errcontext)
	{
		var_dump($errstr);
		die();
	}
	
	/**
	 * Handle an exception
	 * 
	 * PHP 5 dictates that execution is stopped after an uncaught
	 * exception is thrown (this function doesn't count as "catching"
	 * the exception).
	 * 
	 * @param Exception $exception Exception that was thrown
	 */
	public function handle_exception(Exception $exception)
	{
		var_dump($exception->getMessage());
		die();
	}
}
