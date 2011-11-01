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
	 * A stack of inputs
	 * 
	 * @var array
	 */
	protected $input_stack = array();
	
	/**
	* A stack of the names of the inputs being
	* filtered right now.
	*
	* @var array
	*/
	protected $name_stack = array();
	
	/**
	 * Index into the stack of inputs to
	 * get the current value
	 * 
	 * @var int
	 */
	protected $stack_index = -1;
	
	/**
	 * Filter the inputs to the outputs.
	 * 
	 * This function isn't recursive by itself, but some of the
	 * filter operation functions on this class will call this
	 * function for nested filtering.
	 * 
	 * @param array $inputs
	 * @param array $filters
	 * @param array $outputs
	 */
	protected function recursive_filter(array $inputs, array $filters, array &$outputs)
	{
		$this->input_stack[$this->stack_index] = $inputs;
		
		foreach ($filters as $var_name => $operations)
		{
			array_push($this->name_stack, $var_name);
			$outputs[$var_name] = isset($inputs[$var_name]) ? $inputs[$var_name] : null;
			
			foreach ($operations as $operation_name => $operation)
			{
				$filter = array_shift($operation);
				array_unshift($operation, &$outputs[$var_name]);
				$success = $this->dynamic_call('operation_' . $filter, $operation);
			}
			
			array_pop($this->name_stack);
		}
		
		unset($this->input_stack[$this->stack_index--]);
		
		// TODO
		return true;
	}
	
	/**
	 * Run the filters on the inputs
	 * 
	 * @param array $inputs
	 * @param array $filters
	 * @return array Filtered inputs
	 */
	public function run(array $inputs, array $filters)
	{
		$outputs = array();
		$this->recursive_filter($inputs, $filters, $outputs);
		return $outputs;
	}
	
	// ------------------------------------------------------------------------
	// Filter Operations
	// ------------------------------------------------------------------------
	
	// --------------------------------
	// Filtering subarrays
	// --------------------------------
	
	/**
	 * Run the same set of filters on each element of an array
	 * 
	 * @param array $input
	 * @param array $filters
	 * @return bool
	 */
	protected function operation_subarray_each(&$input, array $filters)
	{
		$keyed_filters = array();
		foreach ($input as $key => $value)
		{
			$keyed_filters[$key] = $filters;
		}
		
		return $this->recursive_filter($input, $keyed_filters, $input);
	}
	
	/**
	 * Run a filter on a subarray
	 * 
	 * @param array $input
	 * @param array $filters
	 * @return bool
	 */
	protected function operation_subarray_filter(&$input, array $filters)
	{
		return $this->recursive_filter($input, $filters, $input);
	}
	
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
	 * @param string $input
	 * @return bool
	 */
	protected function operation_validate_alpha(&$input)
	{
		return ctype_alpha($input);
	}
	
	/**
	 * Check to see if the input is all alphanumeric characters
	 * 
	 * @param string $input
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
	 * @param string $input
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
		return $input === null && isset($this->input_stack[$this->stack_index][$this->name_stack[$this->stack_index]]);
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
		return $input !== null && isset($this->input_stack[$this->stack_index][$this->name_stack[$this->stack_index]]);
	}
	
	/**
	 * Make sure the value of the input is between 2 values.
	 * 
	 * @param number $input
	 * @param number $min If null, no min value will be checked
	 * @param number $max If null, no max value will be checked
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
	 * @param string $input
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
	 * Check to make sure an input is an object
	 * 
	 * @param mixed $input
	 * @return bool
	 */
	protected function operation_validate_type_object(&$input)
	{
		return is_object($input);
	}
	
	/**
	 * Check to make sure the input is of type $classname
	 * 
	 * @param object $input
	 * @param string $classname
	 * @return bool
	 */
	protected function operation_validate_object_class(&$input, $classname)
	{
		return get_class($input) === $classname;
	}
	
	/**
	 * Check to make sure the input has $classname as its base class or one
	 * of its parent classes.
	 * 
	 * @see is_a()
	 * @param object $input
	 * @param string $classname
	 * @return bool
	 */
	protected function operation_validate_object_is_a(&$input, $classname)
	{
		return is_a($input, $classname);
	}
	
	/**
	 * Check to see if an object has $classname as one of its parent classes
	 * 
	 * @see is_subclass_of()
	 * @param object $input
	 * @param string $classname
	 * @return bool
	 */
	protected function operation_validate_object_subclass_of(&$input, $classname)
	{
		return is_subclass_of($input, $classname);
	}
	
	/**
	 * Validate the format of a float
	 * 
	 * @param string $input
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
	 * @see strptime()
	 * @param string $input
	 * @param array $formats strftime string formats to allow
	 * @return bool
	 */
	protected function operation_validate_datetime(&$input, array $formats)
	{
		foreach ($formats as $format)
		{
			$date = strptime($input, $format);
			if ($date !== false)
			{
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Validate an IP address
	 * 
	 * @param string $input
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
	 * @param string $input
	 * @return bool
	 */
	protected function operation_validate_hostname(&$input)
	{
		return preg_match('/^(?:[a-z0-9][-a-z0-9]*\.)*(?:[a-z0-9][-a-z0-9]{0,62})\.(?:(?:[a-z]{2}\.)?[a-z]{2,4}|museum|travel)$/', $input);
	}
	
	/**
	 * Validate an email address
	 * 
	 * @param string $input
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
	 * @param array $input
	 * @return bool
	 */
	protected function operation_validate_file_upload_success(&$input)
	{
		foreach ((array) $input['errors'] as $error)
		{
			if ($error !== UPLOAD_ERR_OK)
			{
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Validate an uploaded file's extension. If more than one file
	 * is passed in (using the $_FILES array format), then all
	 * of the files will be checked.
	 * 
	 * @param array $input
	 * @param array $extensions
	 * @return bool
	 */
	protected function operation_validate_file_extension(&$input, $extensions)
	{
		foreach ((array) $input['name'] as $name)
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
	
	/**
	 * Validate an uploaded file's size. If more than one file
	 * is passed in (using the $_FILES array format), then all
	 * of the files will be checked.
	 * 
	 * @param array $input
	 * @param int $min If null, no min value will be checked
	 * @param int $max If null, no max value will be checked
	 * @param bool $inclusive True to pass validation when the file size is equal to the min or max
	 * @return bool
	 */
	protected function operation_validate_file_size(&$input, $min = null, $max = null, $inclusive = true)
	{
		foreach ((array) $input['size'] as $size)
		{
			if (!(($min !== null && (($inclusive && $size >= $min) || (!$inclusive && $size > $min)))
			   && ($max !== null && (($inclusive && $size <= $max) || (!$inclusive && $size < $max)))))
			{
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Validate the number of files uploaded under a certain name.
	 * 
	 * @param array $input
	 * @param int $min If null, no min value will be checked
	 * @param int $max If null, no max value will be checked
	 * @param bool $inclusive True to pass validation when the file size is equal to the min or max
	 * @return bool
	 */
	protected function operation_validate_file_count_between(&$input, $min = null, $max = null, $inclusive = true)
	{
		$size = count((array) $input['size']);
		return ($min !== null && (($inclusive && $size >= $min) || (!$inclusive && $size > $min)))
		    && ($max !== null && (($inclusive && $size <= $max) || (!$inclusive && $size < $max)));
	}
	
	/**
	 * Check to see if the length of a string is between a set of values
	 * 
	 * @param string $input
	 * @param int $min If null, no min value will be checked
	 * @param int $max If null, no max value will be checked
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
	 * @param string $input
	 * @return bool
	 */
	protected function operation_validate_uuid(&$input)
	{
		return preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $input);
	}
	
	/**
	 * Check to make sure this field is identical (===) to another field
	 * 
	 * @param mixed $input
	 * @param string $other_field_name
	 * @return bool
	 */
	protected function operation_validate_identical_field(&$input, $other_field_name)
	{
		return (!isset($this->input_stack[$this->stack_index][$this->name_stack[$this->stack_index]]) && !isset($this->input_stack[$this->stack_index][$other_field_name]))
		    || ($input === $this->input_stack[$this->stack_index][$other_field_name]);
	}
	
	/**
	 * Check to make sure this field is not identical (!==) to another field
	 * 
	 * @param mixed $input
	 * @param string $other_field_name
	 * @return bool
	 */
	protected function operation_validate_not_identical_field(&$input, $other_field_name)
	{
		return (isset($this->input_stack[$this->stack_index][$this->name_stack[$this->stack_index]]) !== isset($this->input_stack[$this->stack_index][$other_field_name]))
		    || ($input !== $this->input_stack[$this->stack_index][$other_field_name]);
	}
	
	/**
	 * Validate a base64 encoded string
	 * 
	 * @param string $input
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
	 * @param array $options
	 * @return bool
	 */
	protected function operation_validate_filter_var(&$input, $filter, $options = null)
	{
		return filter_var($input, $filter, $options);
	}
	
	/**
	 * Check to see if a key exists on an array
	 * 
	 * @param array $input
	 * @param int|string $key
	 * @return bool
	 */
	protected function operation_validate_array_key_exists(&$input, $key)
	{
		return isset($input[$key]);
	}
	
	/**
	 * Validate an array is associative (keys aren't 0-based through length - 1)
	 * 
	 * @param array $input
	 * @return bool
	 */
	protected function operation_validate_array_is_assoc(&$input)
	{
		if (empty($input))
		{
			return true;
		}
		
		$i = 0;
		foreach ($input as $key => $val)
		{
			if ($key !== $i++)
			{
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Validate an array is indexed (keys are 0-based through length - 1)
	 * 
	 * @param array $input
	 * @return bool
	 */
	protected function operation_validate_array_is_indexed(&$input)
	{
		if (empty($input))
		{
			return true;
		}
		
		$i = 0;
		foreach ($input as $key => $val)
		{
			if ($key !== $i++)
			{
				return false;
			}
		}
		
		return true;
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
	 * @param float $input
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
	 * @param float $input
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
	 * @param float $input
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
	// Conversion
	// --------------------------------
	
	/**
	 * Convert a date to a timestamp using the first
	 * strptime formats that match
	 * 
	 * @see strptime()
	 * @param string $input
	 * @param array $formats strftime string formats to allow
	 * @return bool
	 */
	protected function operation_convert_date_to_timestamp(&$input, array $formats)
	{
		foreach ($formats as $format)
		{
			$parsed = strptime($input, $format);
			if ($parsed !== false)
			{
				$input = mktime($parsed['tm_hour'], $parsed['tm_min'], $parsed['tm_sec'], 1 + $parsed['tm_mon'], $parsed['tm_mday'], 1900 + $parsed['tm_year']);
				return true;
			}
		}
		
		return false;
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
		if ($input === null && !isset($this->input_stack[$this->stack_index][$this->name_stack[$this->stack_index]]))
		{
			$input = $value;
		}
		
		return true;
	}
	
	// --------------------------------
	// Custom Functions
	// --------------------------------
	
	/**
	 * Call a callback with the input as a parameter
	 * 
	 * @param mixed $input
	 * @param callback $callback
	 * @param array $parameters
	 * @return bool
	 */
	protected function operation_callback(&$input, $callback, array $parameters = array())
	{
		array_unshift($parameters, &$input);
		return call_user_func_array($callback, $parameters);
	}
	
	/**
	 * Make sure the return value of a callback equals (==) a value
	 * 
	 * @param mixed $input
	 * @param mixed $value
	 * @param callback $callback
	 * @param array $parameters
	 * @return bool
	 */
	protected function operation_callback_equals(&$input, $value, $callback, array $parameters = array())
	{
		array_unshift($parameters, &$input);
		return $value == call_user_func_array($callback, $parameters);
	}
	
	/**
	 * Make sure the return value of a callback doesn't equal (!=) a value
	 * 
	 * @param mixed $input
	 * @param mixed $value
	 * @param callback $callback
	 * @param array $parameters
	 * @return bool
	 */
	protected function operation_callback_not_equals(&$input, $value, $callback, array $parameters = array())
	{
		array_unshift($parameters, &$input);
		return $value != call_user_func_array($callback, $parameters);
	}
	
	/**
	 * Make sure the return value of a callback is identical (===) to a value
	 * 
	 * @param mixed $input
	 * @param mixed $value
	 * @param callback $callback
	 * @param array $parameters
	 * @return bool
	 */
	protected function operation_callback_identical(&$input, $value, $callback, array $parameters = array())
	{
		array_unshift($parameters, &$input);
		return $value === call_user_func_array($callback, $parameters);
	}
	
	/**
	 * Make sure the return value of a callback is not identical (!==) to a value
	 * 
	 * @param mixed $input
	 * @param mixed $value
	 * @param callback $callback
	 * @param array $parameters
	 * @return bool
	 */
	protected function operation_callback_not_identical(&$input, $value, $callback, array $parameters = array())
	{
		array_unshift($parameters, &$input);
		return $value !== call_user_func_array($callback, $parameters);
	}
	
	/**
	 * Call a function on an object
	 * 
	 * @param object $input
	 * @param string $function
	 * @param array $parameters
	 * @return bool
	 */
	protected function operation_object_call(&$input, $function, array $parameters = array())
	{
		if (is_a($input, 'PhractalObject'))
		{
			return $input->dynamic_call($function, $parameters);
		}
		else
		{
			return call_user_func_array(array($input, $function), $parameters);
		}
	}
	
	/**
	 * Make sure the output of a function call on an object
	 * equals (==) a value
	 * 
	 * @param object $input
	 * @param mixed $value
	 * @param string $function
	 * @param array $parameters
	 * @return bool
	 */
	protected function operation_object_call_equals(&$input, $value, $function, array $parameters = array())
	{
		if (is_a($input, 'PhractalObject'))
		{
			$ret = $input->dynamic_call($function, $parameters);
		}
		else
		{
			$ret = call_user_func_array(array($input, $function), $parameters);
		}
		
		return $ret == $value;
	}
	
	/**
	 * Make sure the output of a function call on an object
	 * doesn't equal (!=) a value
	 * 
	 * @param object $input
	 * @param mixed $value
	 * @param string $function
	 * @param array $parameters
	 * @return bool
	 */
	protected function operation_object_call_not_equals(&$input, $value, $function, array $parameters = array())
	{
		if (is_a($input, 'PhractalObject'))
		{
			$ret = $input->dynamic_call($function, $parameters);
		}
		else
		{
			$ret = call_user_func_array(array($input, $function), $parameters);
		}
		
		return $ret != $value;
	}
	
	/**
	 * Make sure the output of a function call on an object
	 * is identical (===) to a value
	 * 
	 * @param object $input
	 * @param mixed $value
	 * @param string $function
	 * @param array $parameters
	 * @return bool
	 */
	protected function operation_object_call_identical(&$input, $value, $function, array $parameters = array())
	{
		if (is_a($input, 'PhractalObject'))
		{
			$ret = $input->dynamic_call($function, $parameters);
		}
		else
		{
			$ret = call_user_func_array(array($input, $function), $parameters);
		}
		
		return $ret === $value;
	}
	
	/**
	 * Make sure the output of a function call on an object
	 * is not identical (!==) to a value
	 * 
	 * @param object $input
	 * @param mixed $value
	 * @param string $function
	 * @param array $parameters
	 * @return bool
	 */
	protected function operation_object_call_not_identical(&$input, $value, $function, array $parameters = array())
	{
		if (is_a($input, 'PhractalObject'))
		{
			$ret = $input->dynamic_call($function, $parameters);
		}
		else
		{
			$ret = call_user_func_array(array($input, $function), $parameters);
		}
		
		return $ret !== $value;
	}
}
