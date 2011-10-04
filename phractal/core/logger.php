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
 * Thrown when a logger group is requested but has not been registered.
 */
class PhractalLoggerGroupNotRegisteredException extends PhractalNameException {}

// ------------------------------------------------------------------------

/**
 * Thrown when a logger group is registered multiple times
 */
class PhractalLoggerGroupAlreadyRegisteredException extends PhractalNameException {}

// ------------------------------------------------------------------------

/**
 * Thrown when headers were already sent, so the logger cannot send more
 */
class PhractalLoggerHeadersSentException extends PhractalException {}

// ------------------------------------------------------------------------

/**
 * Logger Class
 *
 * Logs errors and debug information in requests.
 */
class PhractalLogger extends PhractalObject
{
	/**
	 * All the logging levels combined
	 *
	 * @var int
	 */
	const LEVEL_ALL       = 0x1111111111111111;

	/**
	 * Critical log level.
	 *
	 * Logs of this level need immediate attention.
	 *
	 * @var int
	 */
	const LEVEL_CRITICAL  = 0x0000000000000001;

	/**
	 * Error log level.
	 *
	 * Logs of this level represent an error that occurred
	 * in the system. They are breaking the current system,
	 * but not as badly as the critical logs.
	 *
	 * @var int
	 */
	const LEVEL_ERROR     = 0x0000000000000010;

	/**
	 * Warning log level.
	 *
	 * Logs of this level can lead to errors and criticals
	 * if not examined in some reasonable amount of time.
	 *
	 * @var int
	 */
	const LEVEL_WARNING   = 0x0000000000000100;

	/**
	 * Notice log level.
	 *
	 * Logs of this level will probably not lead to any
	 * damage to the system, but are good to know.
	 *
	 * @var int
	 */
	const LEVEL_NOTICE    = 0x0000000000001000;

	/**
	 * Debug log level.
	 *
	 * Logs of this level are good for debugging problems,
	 * but will probably not make sense in a production
	 * environment.
	 *
	 * @var int
	 */
	const LEVEL_DEBUG     = 0x0000000000010000;

	/**
	 * Info log level.
	 *
	 * Logs of this level are good for informing system
	 * administrators what is going on in the system.
	 *
	 * @var int
	 */
	const LEVEL_INFO      = 0x0000000000100000;
	
	/**
	 * Bench log level
	 * 
	 * Logs of this level contain benchmarking information.
	 * 
	 * @var int
	 */
	const LEVEL_BENCHMARK = 0x0000000001000000;

	/**
	 * Names of the levels for output
	 *
	 * @var array
	 */
	static $level_names = array(
		self::LEVEL_CRITICAL  => 'Critical',
		self::LEVEL_ERROR     => 'Error',
		self::LEVEL_WARNING   => 'Warning',
		self::LEVEL_NOTICE    => 'Notice',
		self::LEVEL_DEBUG     => 'Debug',
		self::LEVEL_INFO      => 'Info',
		self::LEVEL_BENCHMARK => 'Benchmark',
	);

	/**
	 * All the log entries
	 *
	 * @var array
	 */
	protected $logs = array();

	/**
	 * Log groups.
	 *
	 * Each group has a level bitmask for filtering,
	 * and a destination
	 *
	 * @var string
	 */
	protected $groups = array(
		'file'   => array(),
		'header' => array(),
	);

	/**
	 * Print an entry all pretty-like.
	 *
	 * @param array $entry
	 * @return string
	 */
	protected function format_entry($entry)
	{
		return '[' . date('Y-m-d H:i:s', $entry['time']) . '] ' . self::$level_names[$entry['level']] . ': ' . $entry['message'];
	}

	/**
	 * Create a log entry with the level and message specified.
	 *
	 * If the message isn't a string, var_export will be used
	 * to stringify it.
	 *
	 * @param int $level
	 * @param mixed $message
	 */
	protected function log($level, $message)
	{
		$this->logs[] = array(
			'time'    => time(),
			'message' => $message,
			'level'   => $level,
		);
	}

	/**
	 * Destructor
	 */
	public function __destruct()
	{
		parent::__destruct();
		$this->write_logs_to_file();
	}

	/**
	 * Write all logs to their group files
	 *
	 * This function is private because it happens
	 * automatically on __destruct.
	 */
	protected function write_logs_to_file()
	{
		foreach ($this->groups['file'] as $name => $level)
		{
			$formatted = array();
			foreach ($this->logs as $entry)
			{
				if ($entry['level'] & $level)
				{
					$formatted[] = $this->format_entry($entry);
				}
			}
				
			if (!empty($formatted))
			{
				// TODO: Use config to determine path
				$filename = PATH_APP . '/tmp/logs/' . $name . '.log';

				// suppress errors here, because what would we do? log an error?
				@file_put_contents($filename, "---\n" . implode("\n", $formatted) . "\n", FILE_APPEND);
			}
		}
	}

	/**
	 * Write all logs to the browser using HTTP headers
	 *
	 * @throws PhractalLoggerHeadersSentException
	 */
	public function write_logs_to_browser()
	{
		if (headers_sent())
		{
			throw new PhractalLoggerHeadersSentException();
		}
		
		if (RUNTIME === 'cli')
		{
			return;
		}

		foreach ($this->groups['header'] as $name => $level)
		{
			$i = 0;
			foreach ($this->logs as $entry)
			{
				if ($entry['level'] & $level)
				{
					header('log-' . $name . '-' . $i++ . ': ' . $this->format_entry($entry));
				}
			}
		}
	}
	
	/**
	 * Register a logging group of a certain type
	 *
	 * @param string $name Name of the group
	 * @param int $level_bitmask Bitmask of all log levels to monitor
	 * @throws PhractalLoggerGroupAlreadyRegisteredException
	 */
	protected function register_group($type, $name, $level_bitmask)
	{
		if (isset($this->groups[$type][$name]))
		{
			throw new PhractalLoggerGroupAlreadyRegisteredException($name);
		}
		
		$this->groups[$type][$name] = $level_bitmask;
	}
	
	/**
	 * Unregister a logging group of a certain type
	 *
	 * @param string $name
	 * @throws PhractalLoggerGroupNotRegisteredException
	 */
	public function unregister_group($type, $name)
	{
		if (!isset($this->groups[$type][$name]))
		{
			throw new PhractalLoggerGroupNotRegisteredException($name);
		}
		
		unset($this->groups[$type][$name]);
	}
	
	/**
	 * Register a file logging group
	 *
	 * @param string $name Name of the group
	 * @param int $level_bitmask Bitmask of all log levels to monitor
	 * @throws PhractalLoggerGroupAlreadyRegisteredException
	 */
	public function register_file($name, $level_bitmask)
	{
		$this->register_group('file', $name, $level_bitmask);
	}

	/**
	 * Unregister a file logging group
	 *
	 * @param string $name
	 * @throws PhractalLoggerGroupNotRegisteredException
	 */
	public function unregister_file($name)
	{
		$this->unregister_group('file', $name);
	}

	/**
	 * Register a header logging group
	 *
	 * @param string $name Name of the group
	 * @param int $level_bitmask Bitmask of all log levels to monitor
	 * @throws PhractalLoggerGroupAlreadyRegisteredException
	 */
	public function register_header($name, $level_bitmask)
	{
		$this->register_group('header', $name, $level_bitmask);
	}

	/**
	 * Unregister a header logging group
	 *
	 * @param string $name
	 * @throws PhractalLoggerGroupNotRegisteredException
	 */
	public function unregister_header($name)
	{
		$this->unregister_group('header', $name);
	}

	/**
	 * Get all the logs by a level bitmask
	 *
	 * @param int $level_bitmask
	 * @return array
	 */
	public function get_logs_by_level($level_bitmask)
	{
		$logs = array();
		foreach ($this->logs as $entry)
		{
			if ($entry['level'] & $level_bitmask)
			{
				$logs[] = $entry;
			}
		}
		return $logs;
	}

	/**
	 * Log a critical message
	 *
	 * @param mixed $message
	 */
	public function critical($message)
	{
		$this->log(self::LEVEL_CRITICAL, $message);
	}

	/**
	 * Log an error message
	 *
	 * @param mixed $message
	 */
	public function error($message)
	{
		$this->log(self::LEVEL_ERROR, $message);
	}

	/**
	 * Log a warning message
	 *
	 * @param mixed $message
	 */
	public function warning($message)
	{
		$this->log(self::LEVEL_WARNING, $message);
	}

	/**
	 * Log a notice message
	 *
	 * @param mixed $message
	 */
	public function notice($message)
	{
		$this->log(self::LEVEL_NOTICE, $message);
	}

	/**
	 * Log a debug message
	 *
	 * @param mixed $message
	 */
	public function debug($message)
	{
		$this->log(self::LEVEL_DEBUG, $message);
	}

	/**
	 * Log an informational message
	 *
	 * @param mixed $message
	 */
	public function info($message)
	{
		$this->log(self::LEVEL_INFO, $message);
	}
	
	/**
	 * Log a benchmark message
	 * 
	 * @param string $message
	 */
	public function benchmark($message)
	{
		$this->log(self::LEVEL_BENCHMARK, $message);
	}
}
