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
	 * Format an error or exception as a log message
	 * 
	 * @param Exception $exception True if an exception, false if an error
	 * @param int $errno Contains the level of the error raised.
	 * @param string $errstr Contains the error message.
	 * @param string $errfile Contains the filename that the error was raised in.
	 * @param int $errline Contains the line number the error was raised at.
	 * @param array $errcontext Points to the active symbol table at the point the error occurred.
	 *                          In other words, errcontext will contain an array of every variable
	 *                          that existed in the scope the error was triggered in. User error
	 *                          handler must not modify error context.
	 */
	protected function format_log_message(Exception $exception = null, $errno, $errstr, $errfile, $errline, array $errcontext)
	{
		static $titles = array(
			E_COMPILE_ERROR     => 'Compiler Error',
			E_COMPILE_WARNING   => 'Compiler Warning',
			E_CORE_ERROR        => 'Core Error',
			E_CORE_WARNING      => 'Core Warning',
			E_DEPRECATED        => 'Deprecated',
			E_ERROR             => 'Error',
			E_NOTICE            => 'Notice',
			E_PARSE             => 'Parse',
			E_RECOVERABLE_ERROR => 'Recoverable Error',
			E_USER_DEPRECATED   => 'User Deprecated',
			E_USER_ERROR        => 'User Error',
			E_USER_NOTICE       => 'User Notice',
			E_USER_WARNING      => 'User Warning',
			E_WARNING           => 'Warning',
		);
		
		$title = $exception === null ?
		         $titles[$errno] :
		         'Uncaught ' . get_class($exception);
		return $title . ' "' . $errstr . '" on line ' . $errline . ' of ' . $errfile;
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
		$logger = Phractal::get_logger();
		if ($logger)
		{
			$message = $this->format_log_message(null, $errno, $errstr, $errfile, $errline, $errcontext);
			
			switch ($errno)
			{
				case E_COMPILE_ERROR:
				case E_CORE_ERROR:
				case E_PARSE:
					$logger->critical($message);
					break;
				
				case E_ERROR:
				case E_RECOVERABLE_ERROR:
				case E_USER_ERROR:
					$logger->error($message);
					break;
				
				case E_COMPILE_WARNING:
				case E_CORE_WARNING:
				case E_USER_WARNING:
				case E_WARNING:
					$logger->warning($message);
					break;
				
				case E_DEPRECATED:
				case E_NOTICE:
				case E_USER_DEPRECATED:
				case E_USER_NOTICE:
					$logger->notice($message);
					break;
			}
		}
		
		$live = $errno & (E_COMPILE_WARNING | E_CORE_WARNING | E_USER_WARNING | E_WARNING | E_DEPRECATED | E_NOTICE | E_USER_DEPRECATED | E_USER_NOTICE);
		if (!$live)
		{
			die();
		}
		
		return false;
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
		$logger = Phractal::get_logger();
		if ($logger)
		{
			$message = $this->format_log_message($exception, $exception->getCode(), $exception->getMessage(), $exception->getFile(), $exception->getLine(), $exception->getTrace());
			$logger->critical($message);
		}
		
		// PHP5 dies anyway, whether I call it here explicitly or not.
		// I call it explicity here as a way to draw attention to it.
		die();
	}
}
