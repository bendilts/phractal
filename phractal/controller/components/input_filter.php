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

class PhractalInputFilterComponentValidationException extends PhractalException {}

// ------------------------------------------------------------------------

/**
 * Input Filter Component
 *
 * Operates on inputs to clean them up. Can perform text
 * manipulation, casting, validation, and more.
 */
class PhractalInputFilterComponent extends PhractalBaseComponent
{
	/**
	 * Inputs sent to the run function
	 * 
	 * @var array
	 */
	protected $inputs;
	
	/**
	 * Filters sent to the run function
	 * 
	 * @var array
	 */
	protected $filters;
	
	/**
	 * The name of the input being filtered right now
	 * 
	 * @var string
	 */
	protected $current_input_name;
	
	/**
	 * Run the filters on the inputs
	 * 
	 * @param array $inputs
	 * @param array $filters
	 * @return array Filtered inputs
	 */
	public function run(array $inputs, array $filters)
	{
		$this->inputs = &$inputs;
		$this->filters = &$filters;
		
		$outputs = array();
		
		foreach ($filters as $var_name => $operations)
		{
			$this->current_input_name = $var_name;
			$outputs[$var_name] = isset($inputs[$var_name]) ? $inputs[$var_name] : null;
			
			foreach ($operations as $operation_name => $operation)
			{
				$filter = array_shift($operation);
				$function = 'operation_' . $filter;
				
				switch(count($operation))
				{
					case 0:
						$this->{$function}($outputs[$var_name]);
						break;
					case 1:
						$this->{$function}($outputs[$var_name], $operation[0]);
						break;
					case 2:
						$this->{$function}($outputs[$var_name], $operation[0], $operation[1]);
						break;
					case 3:
						$this->{$function}($outputs[$var_name], $operation[0], $operation[1], $operation[2]);
						break;
					case 4:
						$this->{$function}($outputs[$var_name], $operation[0], $operation[1], $operation[2], $operation[3]);
						break;
					case 5:
						$this->{$function}($outputs[$var_name], $operation[0], $operation[1], $operation[2], $operation[3], $operation[4]);
						break;
				}
			}
		}
		
		return $outputs;
	}
	
	// ------------------------------------------------------------------------
	// Filter Operations
	// ------------------------------------------------------------------------
	
	// --------------------------------
	// Casting
	// --------------------------------
	
	/**
	 * Cast the input to an integer
	 * 
	 * @param mixed $input
	 * @return bool
	 */
	protected function operation_cast_int(&$input)
	{
		$input = (int) $input;
		return true;
	}
	
	/**
	 * Cast the input to a floating point number
	 * 
	 * @param mixed $input
	 * @return bool
	 */
	protected function operation_cast_float(&$input)
	{
		$input = (float) $input;
		return true;
	}
	
	/**
	 * Cast the input to a boolean
	 * 
	 * @param mixed $input
	 * @return bool
	 */
	protected function operation_cast_bool(&$input)
	{
		$input = (bool) $input;
		return true;
	}
	
	/**
	 * Cast the input to an array
	 * 
	 * @param mixed $input
	 * @return bool
	 */
	protected function operation_cast_array(&$input)
	{
		$input = (array) $input;
		return true;
	}
	
	// --------------------------------
	// Validation
	// --------------------------------
	
	/**
	 * Check to see if the input is all alpha characters
	 * 
	 * @param mixed $input
	 * @return bool
	 */
	protected function operation_validate_alpha(&$input)
	{
		return ctype_alpha($input);
	}
	
	/**
	 * Check to see if the input is all alphanumeric characters
	 * 
	 * @param mixed $input
	 * @return bool
	 */
	protected function operation_validate_alpha_numeric(&$input)
	{
		return ctype_alnum($input);
	}
	
	/**
	 * Check to see if the input is all numeric characters
	 * 
	 * @param string $input
	 * @return bool
	 */
	protected function operation_validate_numeric(&$input)
	{
		return preg_match('/^\\d*$/', $input);
	}
	
	/**
	 * Check to see if the input matches a perl regular expression
	 * 
	 * @see preg_match()
	 * @param mixed $input
	 * @param string $regex
	 * @return bool
	 */
	protected function operation_validate_regex(&$input, $regex)
	{
		return preg_match($regex, $input);
	}
	
	/**
	 * Check to make sure the input is not empty as defined
	 * by the empty() function.
	 * 
	 * @see empty()
	 * @param mixed $input
	 * @return bool
	 */
	protected function operation_validate_not_empty(&$input)
	{
		return !empty($input);
	}
	
	/**
	 * Check to make sure the input is empty as defined
	 * by the empty() function
	 * 
	 * @see empty()
	 * @param mixed $input
	 * @return bool
	 */
	protected function operation_validate_empty(&$input)
	{
		return empty($input);
	}
	
	/**
	 * Check to make sure the input is not set in the list of
	 * variables passed to the run function
	 * 
	 * @see isset()
	 * @param mixed $input
	 * @return bool
	 */
	protected function operation_validate_not_set(&$input)
	{
		return $input === null && isset($this->inputs[$this->current_input_name]);
	}
	
	/**
	 * Check to make sure the input is set in the list of
	 * variables passed to the run function
	 * 
	 * @see isset()
	 * @param mixed $input
	 * @return bool
	 */
	protected function operation_validate_isset(&$input)
	{
		return $input !== null && isset($this->inputs[$this->current_input_name]);
	}
	
	/**
	 * Make sure the value of the input is between 2 values.
	 * 
	 * @param mixed $input
	 * @param mixed $min If null, no min value will be checked
	 * @param mixed $max If null, no max value will be checked
	 * @param bool $inclusive True to pass validation when the input is equal to the min or max
	 * @return bool
	 */
	protected function operation_validate_between(&$input, $min = null, $max = null, $inclusive = true)
	{
		return ($min !== null && (($inclusive && $input >= $min) || (!$inclusive && $input > $min)))
		    && ($max !== null && (($inclusive && $input <= $max) || (!$inclusive && $input < $max)));
	}
	
	/**
	 * Validate a credit card by using the luhn algorithm.
	 * 
	 * @param mixed $input
	 * @return bool
	 */
	protected function operation_validate_credit_card(&$input)
	{
		$sum = 0;
		$length = strlen($input);
		
		for ($position = 1 - ($length % 2); $position < $length; $position += 2)
		{
			$sum += $input[$position];
		}
		
		for ($position = ($length % 2); $position < $length; $position += 2)
		{
			$number = $input[$position] * 2;
			$sum += ($number < 10) ? $number : $number - 9;
		}
		
		return ($sum % 10 === 0);
	}
	
	/**
	 * Check to see if an input is equal (==) to a value
	 * 
	 * @param mixed $input
	 * @param mixed $value
	 * @return bool
	 */
	protected function operation_validate_equal(&$input, $value)
	{
		return $input == $value;
	}
	
	/**
	 * Check to see if an input is not equal (!=) to a value
	 * 
	 * @param mixed $input
	 * @param mixed $value
	 * @return bool
	 */
	protected function operation_validate_not_equal(&$input, $value)
	{
		return $input != $value;
	}
	
	/**
	 * Check to see if an input is identical (===) to a value
	 * 
	 * @param mixed $input
	 * @param mixed $value
	 * @return bool
	 */
	protected function operation_validate_identical(&$input, $value)
	{
		return $input === $value;
	}
	
	/**
	 * Check to see if an input is not identical (!==) to a value
	 * 
	 * @param mixed $input
	 * @param mixed $value
	 * @return bool
	 */
	protected function operation_validate_not_identical(&$input, $value)
	{
		return $input !== $value;
	}
	
	/**
	 * Check to make sure an input is an array
	 * 
	 * @param mixed $input
	 * @return bool
	 */
	protected function operation_validate_type_array(&$input)
	{
		return is_array($input);
	}
	
	/**
	 * Check to make sure an input is an int
	 * 
	 * @param mixed $input
	 * @return bool
	 */
	protected function operation_validate_type_int(&$input)
	{
		return is_int($input);
	}
	
	/**
	 * Check to make sure an input is a float
	 * 
	 * @param mixed $input
	 * @return bool
	 */
	protected function operation_validate_type_float(&$input)
	{
		return is_float($input);
	}
	
	/**
	 * Check to make sure an input is a bool
	 * 
	 * @param mixed $input
	 * @return bool
	 */
	protected function operation_validate_type_bool(&$input)
	{
		return is_bool($input);
	}
	
	/**
	 * Check to make sure an input is a string
	 * 
	 * @param string $input
	 * @return bool
	 */
	protected function operation_validate_type_string(&$input)
	{
		return is_string($input);
	}
	
	/**
	 * Check to make sure an input is a file upload
	 * 
	 * @param mixed $input
	 * @return bool
	 */
	protected function operation_validate_type_file(&$input)
	{
		return is_array($input) && isset($input['size']) && isset($input['name']) && isset($input['type']) && isset($input['tmp_name']) && isset($input['error']);
	}
	
	/**
	 * Validate the format of a float
	 * 
	 * @param mixed $input
	 * @param int $min_precision Null for no min precision
	 * @param int $max_precision Null for no max precision
	 * @return bool
	 */
	protected function operation_validate_float(&$input, $min_precision = null, $max_precision = null)
	{
		$precision = ($min_precision === null ? '0' : $min_precision) . ','
		           . ($max_precision === null ? ''  : $max_precision);
		
		return is_float($input) || preg_match('/^[-+]?\d*\\.\d{' . $precision . '}$/', $input);
	}
	
	/**
	 * Check to see if the input contains a date in the format passed in.
	 * 
	 * @param mixed $input
	 * @param mixed $strptime_format String for a single format, or an array of strings to allow multiple.
	 * @return bool
	 */
	protected function operation_validate_datetime(&$input, $strptime_format)
	{
		if (is_array($strptime_format))
		{
			foreach ($strptime_format as $format)
			{
				$date = strptime($input, $format);
				if ($date === false)
				{
					return false;
				}
			}
			
			return true;
		}
		
		$date = strptime($input, $strptime_format);
		return $date !== false;
	}
	
	/**
	 * Validate an IP address
	 * 
	 * @param mixed $input
	 * @param string $type all|ipv4|ipv6
	 * @return bool
	 */
	protected function operation_validate_ip(&$input, $type = 'all')
	{
		$flags = array();
		if ($type === 'all' || $type === 'ipv4')
		{
			$flags[] = FILTER_FLAG_IPV4;
		}
		if ($type === 'all' || $type === 'ipv6')
		{
			$flags[] = FILTER_FLAG_IPV6;
		}
		return false !== filter_var($input, FILTER_VALIDATE_IP, array('flags' => $flags));
	}
	
	/**
	 * Validate a hostname
	 * 
	 * @param mixed $input
	 * @return bool
	 */
	protected function operation_validate_hostname(&$input)
	{
		return preg_match('/^(?:[a-z0-9][-a-z0-9]*\.)*(?:[a-z0-9][-a-z0-9]{0,62})\.(?:(?:[a-z]{2}\.)?[a-z]{2,4}|museum|travel)$/', $input);
	}
	
	/**
	 * Validate an email address
	 * 
	 * @param mixed $input
	 * @return bool
	 */
	protected function operation_validate_email(&$input)
	{
		return false !== filter_var($input, FILTER_VALIDATE_EMAIL);
	}
	
	/**
	 * Validate a successful file upload. If more than one file
	 * is passed in (using the $_FILES array format), then all
	 * of the files will be checked.
	 * 
	 * @param mixed $input
	 * @return bool
	 */
	protected function operation_validate_file_upload_success(&$input)
	{
		if (is_array($input['error']))
		{
			foreach ($input['error'] as $error)
			{
				if ($error !== UPLOAD_ERR_OK)
				{
					return false;
				}
			}
		}
		
		return $error === UPLOAD_ERR_OK;
	}
	
	/**
	 * Validate an uploaded file's extension. If more than one file
	 * is passed in (using the $_FILES array format), then all
	 * of the files will be checked.
	 * 
	 * @param mixed $input
	 * @param array $extensions
	 * @return bool
	 */
	protected function operation_validate_file_extension(&$input, $extensions)
	{
		if (is_array($input['name']))
		{
			foreach ($input['name'] as $name)
			{
				$index = strrpos($name, '.');
				if ($index === false)
				{
					return false;
				}
				
				$ext = substr($name, $index + 1);
				if (!in_array($ext, $extensions, true))
				{
					return false;
				}
			}
			
			return true;
		}
		
		$index = strrpos($input['name'], '.');
		if ($index === false)
		{
			return false;
		}
		
		$ext = substr($input['name'], $index + 1);
		return in_array($ext, $extensions, true);
	}
	
	/**
	 * Validate an uploaded file's size. If more than one file
	 * is passed in (using the $_FILES array format), then all
	 * of the files will be checked.
	 * 
	 * @param mixed $input
	 * @param mixed $min If null, no min value will be checked
	 * @param mixed $max If null, no max value will be checked
	 * @param bool $inclusive True to pass validation when the file size is equal to the min or max
	 * @return bool
	 */
	protected function operation_validate_file_size(&$input, $min = null, $max = null, $inclusive = true)
	{
		if (is_array($input['size']))
		{
			foreach ($input['size'] as $size)
			{
				if (!(($min !== null && (($inclusive && $size >= $min) || (!$inclusive && $size > $min)))
				   && ($max !== null && (($inclusive && $size <= $max) || (!$inclusive && $size < $max)))))
				{
					return false;
				}
			}
			
			return true;
		}
		
		$size = $input['size'];
		return ($min !== null && (($inclusive && $size >= $min) || (!$inclusive && $size > $min)))
		    && ($max !== null && (($inclusive && $size <= $max) || (!$inclusive && $size < $max)));
	}
	
	/**
	 * Validate the number of files uploaded under a certain name.
	 * 
	 * @param mixed $input
	 * @param mixed $min If null, no min value will be checked
	 * @param mixed $max If null, no max value will be checked
	 * @param bool $inclusive True to pass validation when the file size is equal to the min or max
	 * @return bool
	 */
	protected function operation_validate_file_count_between(&$input, $min = null, $max = null, $inclusive = true)
	{
		$size = is_array($input['size']) ? count($input['size']) : 1;
		return ($min !== null && (($inclusive && $size >= $min) || (!$inclusive && $size > $min)))
		    && ($max !== null && (($inclusive && $size <= $max) || (!$inclusive && $size < $max)));
	}
	
	/**
	 * Check to see if the length of a string is between a set of values
	 * 
	 * @param string $input
	 * @param mixed $min If null, no min value will be checked
	 * @param mixed $max If null, no max value will be checked
	 * @param bool $inclusive True to pass validation when the length is equal to the min or max
	 * @return bool
	 */
	protected function operation_validate_strlength_between(&$input, $min = null, $max = null, $inclusive = true)
	{
		$length = strlen($input);
		return ($min !== null && (($inclusive && $length >= $min) || (!$inclusive && $length > $min)))
		    && ($max !== null && (($inclusive && $length <= $max) || (!$inclusive && $length < $max)));
	}
	
	/**
	 * Validate a phone number
	 * 
	 * @param string $input
	 * @return bool
	 */
	protected function operation_validate_phone(&$input)
	{
		// includes all NANPA members. see http://en.wikipedia.org/wiki/North_American_Numbering_Plan#List_of_NANPA_countries_and_territories
		return preg_match('/^(?:\+?1)?[-. ]?\\(?[2-9][0-8][0-9]\\)?[-. ]?[2-9][0-9]{2}[-. ]?[0-9]{4}$/', $input);
	}
	
	/**
	 * Validate a postal code
	 * 
	 * @param string $input
	 * @param string $country 2 char iso code
	 * @return bool
	 */
	protected function operation_validate_postal(&$input, $country = 'all')
	{
		$regex = null;
		
		switch ($country)
		{
			case 'uk':
				$regex  = '/\\A\\b[A-Z]{1,2}[0-9][A-Z0-9]? [0-9][ABD-HJLNP-UW-Z]{2}\\b\\z/i';
				break;
			case 'ca':
				$regex  = '/\\A\\b[ABCEGHJKLMNPRSTVXY][0-9][A-Z] [0-9][A-Z][0-9]\\b\\z/i';
				break;
			case 'it':
			case 'de':
				$regex  = '/^[0-9]{5}$/i';
				break;
			case 'be':
				$regex  = '/^[1-9]{1}[0-9]{3}$/i';
				break;
			case 'us':
				$regex  = '/\\A\\b[0-9]{5}(?:-[0-9]{4})?\\b\\z/i';
				break;
		}
		
		return $regex === null || preg_match($regex, input);
	}
	
	/**
	 * Check to see if the input is in an array of values
	 * 
	 * @see in_array()
	 * @param mixed $input
	 * @param array $array
	 * @param bool $strict
	 * @return bool
	 */
	protected function operation_validate_in_array(&$input, $array, $strict = false)
	{
		return in_array($input, $array, $strict);
	}
	
	/**
	 * Validate a UUID
	 * 
	 * @param mixed $input
	 * @return bool
	 */
	protected function operation_validate_uuid(&$input)
	{
		return preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $input);
	}
	
	/**
	 * Check to make sure this field is identical to another field
	 * 
	 * @param mixed $input
	 * @param string $other_field_name
	 * @return bool
	 */
	protected function operation_validate_identical_field(&$input, $other_field_name)
	{
		return (!isset($this->inputs[$this->current_input_name]) && !isset($this->inputs[$other_field_name]))
		    || ($input === $this->inputs[$other_field_name]);
	}
	
	/**
	 * Validate a base64 encoded string
	 * 
	 * @param mixed $input
	 * @return bool
	 */
	protected function operation_validate_base64(&$input)
	{
		return preg_match('/^[a-z0-9+\/]*[=]{0,2}$/', $input);
	}
	
	/**
	 * Use filter_var to validate an input
	 * 
	 * @see filter_var()
	 * @param mixed $input
	 * @param int $filter
	 * @param mixed $options
	 * @return bool
	 */
	protected function operation_validate_filter_var(&$input, $filter, $options = null)
	{
		return filter_var($input, $filter, $options);
	}
	
	// --------------------------------
	// Manipulation
	// --------------------------------
	
	/**
	 * Trim an input
	 * 
	 * @see trim()
	 * @param string $input
	 * @param string $chrlist
	 * @return bool
	 */
	protected function operation_manipulate_trim(&$input, $chrlist = null)
	{
		$input = trim($input, $chrlist);
		return true;
	}
	
	/**
	 * Left trim an input
	 * 
	 * @see ltrim()
	 * @param string $input
	 * @param string $chrlist
	 * @return bool
	 */
	protected function operation_manipulate_ltrim(&$input, $chrlist = null)
	{
		$input = ltrim($input, $chrlist);
		return true;
	}
	
	/**
	 * Right trim an input
	 * 
	 * @see rtrim()
	 * @param string $input
	 * @param string $chrlist
	 * @return bool
	 */
	protected function operation_manipulate_rtrim(&$input, $chrlist = null)
	{
		$input = rtrim($input, $chrlist);
		return true;
	}
	
	/**
	 * Pad an input
	 * 
	 * @see str_pad()
	 * @param string $input
	 * @param int $length
	 * @param string $string
	 * @param int $type
	 * @return bool
	 */
	protected function operation_manipulate_str_pad(&$input, $length, $string = ' ', $type = STR_PAD_RIGHT)
	{
		$input = str_pad($input, $length, $string, $type);
		return true;
	}
	
	/**
	 * Perform str_replace on an input
	 * 
	 * @see str_replace()
	 * @param string $input
	 * @param mixed $search
	 * @param mixed $replace
	 * @param int $count
	 * @return bool
	 */
	protected function operation_manipulate_str_replace(&$input, $search, $replace, $count = null)
	{
		$input = str_replace($search, $replace, $input, $count);
		return true;
	}
	
	/**
	 * Explode an input
	 * 
	 * @see explode()
	 * @param string $input
	 * @param string $delimiter
	 * @param int $limit
	 * @return bool
	 */
	protected function operation_manipulate_explode(&$input, $delimiter, $limit = null)
	{
		$input = explode($delimiter, $input, $limit);
		return true;
	}
	
	/**
	 * Implode an input
	 * 
	 * @see implode()
	 * @param array $input
	 * @param string $delimiter
	 * @return bool
	 */
	protected function operation_manipulate_implode(&$input, $delimiter)
	{
		$input = implode($delimiter, $input);
		return true;
	}
	
	/**
	 * Change all characters to uppercase
	 * 
	 * @see strtoupper()
	 * @param string $input
	 * @return bool
	 */
	protected function operation_manipulate_strtoupper(&$input)
	{
		$input = strtoupper($input);
		return true;
	}
	
	/**
	 * Change all characters to lowercase
	 * 
	 * @see strtolower()
	 * @param string $input
	 * @return bool
	 */
	protected function operation_manipulate_strtolower(&$input)
	{
		$input = strtolower($input);
		return true;
	}
	
	/**
	 * Change the first letter of every word to
	 * uppercase
	 * 
	 * @see ucwords()
	 * @param string $input
	 * @return bool
	 */
	protected function operation_manipulate_ucwords(&$input)
	{
		$input = ucwords($input);
		return true;
	}
	
	/**
	 * Change the first letter of every sentence to
	 * uppercase
	 * 
	 * @see ucfirst()
	 * @param string $input
	 * @return bool
	 */
	protected function operation_manipulate_ucfirst(&$input)
	{
		$input = ucfirst($input);
		return true;
	}
	
	/**
	 * Strip HTML tags from an input
	 * 
	 * @see strip_tags()
	 * @param string $input
	 * @param array $allowable_tags
	 * @return bool
	 */
	protected function operation_manipulate_strip_tags(&$input, $allowable_tags = null)
	{
		strip_tags($input, $allowable_tags);
		return true;
	}
	
	/**
	 * Strip slashes from an input
	 * 
	 * @see stripslashes()
	 * @param string $input
	 * @return bool
	 */
	protected function operation_manipulate_stripslashes(&$input)
	{
		$input = stripslashes($input);
		return true;
	}
	
	/**
	 * Ensure a number is within a range by setting
	 * it to the min or the max when it is outside
	 * the range.
	 * 
	 * @param number $input
	 * @param number $min If null, no min is checked
	 * @param number $max If null, no max is checked
	 * @return bool
	 */
	protected function operation_manipulate_range(&$input, $min = null, $max = null)
	{
		if ($min !== null && $input < $min)
		{
			$input = $min;
		}
		elseif ($max !== null && $input > $max)
		{
			$input = $max;
		}
		
		return true;
	}
	
	/**
	 * Round an input
	 * 
	 * @see round()
	 * @param mixed $input
	 * @param int $precision
	 * @return bool
	 */
	protected function operation_manipulate_round(&$input, $precision = 0)
	{
		$input = round($input, $precision);
		
		return true;
	}
	
	/**
	 * Ceil an input
	 * 
	 * @param mixed $input
	 * @param int $precision
	 * @return bool
	 */
	protected function operation_manipulate_ceil(&$input, $precision = 0)
	{
		if ($precision === 0)
		{
			$input = ceil($input);
		}
		else
		{
			$mult = 10 * $precision;
			$input = ceil($input * $mult) / $mult;
		}
		
		return true;
	}
	
	/**
	 * Floor an input
	 * 
	 * @param mixed $input
	 * @param int $precision
	 * @return bool
	 */
	protected function operation_manipulate_floor(&$input, $precision = 0)
	{
		if ($precision === 0)
		{
			$input = floor($input);
		}
		else
		{
			$mult = 10 * $precision;
			$input = floor($input * $mult) / $mult;
		}
		
		return true;
	}
	
	// --------------------------------
	// Setting
	// --------------------------------
	
	/**
	 * Set the input if it is empty
	 * 
	 * @see empty()
	 * @param mixed $input
	 * @param mixed $value
	 * @return bool
	 */
	protected function operation_default_empty(&$input, $value)
	{
		if (empty($input))
		{
			$input = $value;
		}
		
		return true;
	}
	
	/**
	 * Set an input to a value if the input isn't set
	 * 
	 * @param mixed $input
	 * @param mixed $value
	 * @return bool
	 */
	protected function operation_default_not_set(&$input, $value)
	{
		if ($input === null && !isset($this->inputs[$this->current_input_name]))
		{
			$input = $value;
		}
		
		return true;
	}
}
