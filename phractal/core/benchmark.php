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
 * Thrown when a token doesn't exist
 */
class PhractalBenchmarkBadTokenException extends PhractalNameException {}

// ------------------------------------------------------------------------

/**
 * Thrown when a token has already been stopped
 */
class PhractalBenchmarkTokenAlreadyStoppedException extends PhractalNameException {}

// ------------------------------------------------------------------------

/**
 * Benchmark Class
 *
 * Tracks resource usage during runtime.
 */
class PhractalBenchmark extends PhractalObject
{
	/**
	 * All of the benchmark entries organized by group and name
	 * 
	 * @var array
	 */
	protected $groups = array();
	
	/**
	 * All of the benchmark entries, keyed by token
	 * 
	 * @var array
	 */
	protected $tokens = array();
	
	/**
	 * The amount of time the script has taken up until the
	 * reporting of metrics. This is used in the time percentage
	 * calculation.
	 * 
	 * @var float
	 */
	protected $script_percent_time;
	
	/**
	 * Add an entry to the benchmark array
	 * 
	 * @param string $group
	 * @param string $name
	 * @param array $stats Contains time and memory keys
	 * @return int Token used for stop function
	 */
	protected function add_entry($group, $name, $stats)
	{
		static $token_count = 0;
		$token = $token_count++;
		
		$entry = array(
			'token' => $token,
			'start' => $stats,
			'stop'  => false,
		);
		
		$this->tokens[$token] = &$entry;
		$this->groups[$group][$name][] = &$entry;
		
		return $token;
	}
	
	/**
	 * Create a completed benchmark as if it had been begun at
	 * the script start.
	 * 
	 * @param string $group
	 * @param string $name
	 */
	public function mark_from_script_start($group, $name)
	{
		$token = $this->add_entry($group, $name, array(
			'time'   => START_TIME,
			'memory' => START_MEMORY,
		));
		
		$this->stop($token);
	}
	
	/**
	 * Start a benchmark test.
	 * 
	 * @param string $group
	 * @param string $name
	 * @return int Token to pass into the stop function
	 */
	public function start($group, $name)
	{
		return $this->add_entry($group, $name, array(
			'time'   => microtime(true),
			'memory' => memory_get_usage(true),
		));
	}
	
	/**
	 * Stop a benchmark test.
	 * 
	 * @param int $token The token from the start method
	 * @throws PhractalBenchmarkBadTokenException
	 * @throws PhractalBenchmarkTokenAlreadyStoppedException
	 */
	public function stop($token)
	{
		if (!isset($this->tokens[$token]))
		{
			throw new PhractalBenchmarkBadTokenException($token);
		}
		
		$entry = &$this->tokens[$token];
		if ($entry['stop'] !== false)
		{
			throw new PhractalBenchmarkTokenAlreadyStoppedException($token);
		}
		
		$entry['stop'] = array(
			'time'   => microtime(true),
			'memory' => memory_get_usage(true),
		);
	}
	
	/**
	 * Get group stats for all groups with finished tests.
	 * 
	 * @return array
	 */
	public function all_group_stats()
	{
		return $this->stat_groups(array_keys($this->groups));
	}
	
	/**
	 * Get stats for all finished tests.
	 * 
	 * @return array
	 */
	public function all_stats()
	{
		$stats = array();
		foreach ($this->groups as $group => $names)
		{
			foreach ($names as $name => $entries)
			{
				$stat = $this->stat($group, $name);
				if ($stat !== false)
				{
					$stats[$group][$name] = $stat;
				}
			}
		}
		return $stats;
	}
	
	/**
	 * Get group stats for all specified groups with finished tests.
	 * 
	 * Bad group names are ignored.
	 * 
	 * @param array $groups
	 * @return array
	 */
	public function stat_groups(array $groups)
	{
		$stats = array();
		foreach ($groups as $group)
		{
			$stat = $this->stat_group($group);
			if ($stat !== false)
			{
				$stats[$group] = $stat;
			}
		}
		return $stats;
	}
	
	/**
	 * Get grouped stats for a particular group name.
	 * A single array is returned with stats about
	 * min, max, average, and total time ane memory
	 * usage.
	 * 
	 * Bad group names are ignored.
	 * 
	 * @param string $group
	 * @return array
	 */
	public function stat_group($group)
	{
		$min_memory = 0.0;
		$max_memory = 0.0;
		$avg_memory = 0.0;
		$all_memory = 0.0;
		
		$min_time = 0.0;
		$max_time = 0.0;
		$avg_time = 0.0;
		$all_time = 0.0;
		
		$count = 0;
		
		if (isset($this->groups[$group]))
		{
			foreach ($this->groups[$group] as $name => $entries)
			{
				foreach ($entries as $entry)
				{
					if ($entry['stop'] === false) { continue; }
					
					$entry_memory = $entry['stop']['memory'] - $entry['start']['memory'];
					$entry_time   = $entry['stop']['time']   - $entry['start']['time'];
					
					if ($count === 0)
					{
						$min_memory = $max_memory = $entry_memory;
						$min_time   = $max_time   = $entry_time;
					}
					else
					{
						if ($entry_memory < $min_memory)
						{
							$min_memory = $entry_memory;
						}
						if ($entry_memory > $max_memory)
						{
							$max_memory = $entry_memory;
						}
						if ($entry_time < $min_time)
						{
							$min_time = $entry_time;
						}
						if ($entry_time > $max_time)
						{
							$max_time = $entry_time;
						}
					}
					
					$all_memory += $entry_memory;
					$all_time   += $entry_time;
					$count++;
				}
			}
			
			if ($count > 0)
			{
				$avg_memory = $all_memory / $count;
				$avg_time   = $all_time   / $count;
			}
		}
		
		if ($count === 0)
		{
			return false;
		}
		
		return array(
			'memory' => array(
				'min' => $min_memory,
				'max' => $max_memory,
				'avg' => $avg_memory,
				'all' => $all_memory,
			),
			'time' => array(
				'min' => $min_time,
				'max' => $max_time,
				'avg' => $avg_time,
				'all' => $all_time,
			),
			'count' => $count,
		);
	}
	
	/**
	 * Get stats for a particular group / name pair.
	 * A single array is returned with stats about
	 * min, max, average, and total time ane memory
	 * usage.
	 * 
	 * Bad groups/names are ignored.
	 * 
	 * @param string $group
	 * @param string $name
	 * @return array
	 */
	public function stat($group, $name)
	{
		$min_memory = 0.0;
		$max_memory = 0.0;
		$avg_memory = 0.0;
		$all_memory = 0.0;
		
		$min_time = 0.0;
		$max_time = 0.0;
		$avg_time = 0.0;
		$all_time = 0.0;
		
		$count = 0;
		
		if (isset($this->groups[$group]) && isset($this->groups[$group][$name]))
		{
			foreach ($this->groups[$group][$name] as $entry)
			{
				if ($entry['stop'] === false) { continue; }
				
				$entry_memory = $entry['stop']['memory'] - $entry['start']['memory'];
				$entry_time   = $entry['stop']['time']   - $entry['start']['time'];
				
				if ($count === 0)
				{
					$min_memory = $max_memory = $entry_memory;
					$min_time   = $max_time   = $entry_time;
				}
				else
				{
					if ($entry_memory < $min_memory)
					{
						$min_memory = $entry_memory;
					}
					if ($entry_memory > $max_memory)
					{
						$max_memory = $entry_memory;
					}
					if ($entry_time < $min_time)
					{
						$min_time = $entry_time;
					}
					if ($entry_time > $max_time)
					{
						$max_time = $entry_time;
					}
				}
				
				$all_memory += $entry_memory;
				$all_time   += $entry_time;
				$count++;
			}
		}
		
		if ($count === 0)
		{
			return false;
		}
		
		return array(
			'memory' => array(
				'min' => $min_memory,
				'max' => $max_memory,
				'avg' => $avg_memory,
				'all' => $all_memory,
			),
			'time' => array(
				'min' => $min_time,
				'max' => $max_time,
				'avg' => $avg_time,
				'all' => $all_time,
			),
			'count' => $count,
		);
	}
	
	/**
	 * Log grouped benchmark stats for all groups.
	 * The logs will be PhractalLogger::LEVEL_BENCHMARK level logs.
	 */
	public function log_all_groups()
	{
		$this->log_groups(array_keys($this->groups));
	}
	
	/**
	 * Log stats for all group/name pairs.
	 * The logs will be PhractalLogger::LEVEL_BENCHMARK level logs.
	 */
	public function log_all()
	{
		// mark the script total time
		$this->script_percent_time = (microtime(true) - START_TIME) / 100;
		
		foreach ($this->groups as $group => $names)
		{
			foreach ($names as $name => $entries)
			{
				$this->log($group, $name);
			}
		}
	}
	
	/**
	 * Log grouped benchmark stats for all named groups.
	 * The logs will be PhractalLogger::LEVEL_BENCHMARK level logs.
	 * 
	 * Bad group names are ignored.
	 * 
	 * @param array $groups
	 */
	public function log_groups(array $groups)
	{
		// mark the script total time
		$this->script_percent_time = (microtime(true) - START_TIME) / 100;
		
		foreach ($groups as $group)
		{
			$this->log_group($group);
		}
	}
	
	/**
	 * Format a stat for the log file.
	 * 
	 * @param array $stat Output from $this->stat* functions
	 * @param string $title Name of the test to put in the logfile.
	 * @return string
	 */
	protected function format_for_log($stat, $title)
	{
		$message = $title . ' (' . $stat['count'] . ')'
		         . ' {Time ' . sprintf('%.3f', $stat['time']['all'] / $this->script_percent_time) . '% min='   . sprintf('%.7f', $stat['time']['min'])   . ' max=' . sprintf('%.7f', $stat['time']['max'])   . ' avg=' . sprintf('%.7f', $stat['time']['avg'])   . ' all=' . sprintf('%.7f', $stat['time']['all'])   . '}'
		         . ' {Memory min=' . sprintf('%d',   $stat['memory']['min']) . ' max=' . sprintf('%d',   $stat['memory']['max']) . ' avg=' . sprintf('%d',   $stat['memory']['avg']) . ' all=' . sprintf('%d',   $stat['memory']['all']) . '}';
		
		return $message;
	}
	
	/**
	 * Log grouped stats for the named group.
	 * The logs will be PhractalLogger::LEVEL_BENCHMARK level logs.
	 * 
	 * Bad group names are ignored.
	 * 
	 * @param string $group
	 */
	public function log_group($group)
	{
		$stat = $this->stat_group($group);
		if ($stat !== false)
		{
			$message = $this->format_for_log($stat, $group);
			PhractalApp::get_instance()->get_logger()->benchmark($message);
		}
	}
	
	/**
	 * Log stats for the name/group pair
	 * The logs will be PhractalLogger::LEVEL_BENCHMARK level logs.
	 * 
	 * Bad group names are ignored.
	 * 
	 * @param string $group
	 */
	public function log($group, $name)
	{
		$stat = $this->stat($group, $name);
		if ($stat !== false)
		{
			$message = $this->format_for_log($stat, $group . '->' . $name);
			PhractalApp::get_instance()->get_logger()->benchmark($message);
		}
	}
}
