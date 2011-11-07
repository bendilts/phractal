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
 * Thrown when the response has been locked and an attempt to change anything
 * about the response is made.
 */
class PhractalResponseComponentLockedException extends PhractalException {}

// ------------------------------------------------------------------------

/**
 * Thrown when a bad code is used when unlocking the response.
 */
class PhractalResponseComponentBadUnlockCodeException extends PhractalException {}

// ------------------------------------------------------------------------

/**
 * Thrown when an unknown HTTP status code is given without a message.
 */
class PhractalResponseComponentUnknownHTTPStatusMessageException extends PhractalNameException {}

// ------------------------------------------------------------------------

/**
 * Response Component
 *
 * Contains response headers and data for a request
 */
class PhractalResponseComponent extends PhractalBaseComponent
{
	/**
	 * Common HTTP status codes
	 * 
	 * @var int
	 */
	const HTTP_STATUS_CONTINUE                              =   100;
	const HTTP_STATUS_OK                                    =   200;
	const HTTP_STATUS_CREATED                               =   201;
	const HTTP_STATUS_ACCEPTED                              =   202;
	const HTTP_STATUS_NO_CONTENT                            =   204;
	const HTTP_STATUS_RESET_CONTENT                         =   205;
	const HTTP_STATUS_PARTIAL_CONTENT                       =   206;
	const HTTP_STATUS_MULTIPLE_CHOICES                      =   300;
	const HTTP_STATUS_MOVED_PERMANENTLY                     =   301;
	const HTTP_STATUS_FOUND                                 =   302;
	const HTTP_STATUS_SEE_OTHER                             =   303;
	const HTTP_STATUS_NOT_MODIFIED                          =   304;
	const HTTP_STATUS_USE_PROXY                             =   305;
	const HTTP_STATUS_TEMPORARY_REDIRECT                    =   307;
	const HTTP_STATUS_BAD_REQUEST                           =   400;
	const HTTP_STATUS_UNAUTHORIZED                          =   401;
	const HTTP_STATUS_PAYMENT_REQUIRED                      =   402;
	const HTTP_STATUS_FORBIDDEN                             =   403;
	const HTTP_STATUS_NOT_FOUND                             =   404;
	const HTTP_STATUS_METHOD_NOT_ALLOWED                    =   405;
	const HTTP_STATUS_PROXY_AUTHENTICATION_REQUIRED         =   407;
	const HTTP_STATUS_INTERNAL_SERVER_ERROR                 =   500;
	const HTTP_STATUS_NOT_IMPLEMENTED                       =   501;
	const HTTP_STATUS_BAD_GATEWAY                           =   502;
	const HTTP_STATUS_SERVICE_UNAVAILABLE                   =   503;
	const HTTP_STATUS_GATEWAY_TIMEOUT                       =   504;
	
	/**
	 * Status messages for common HTTP status codes
	 * 
	 * @var array
	 */
	protected static $http_status_messages = array(
		self::HTTP_STATUS_CONTINUE                          => 'Continue',
		self::HTTP_STATUS_OK                                => 'OK',
		self::HTTP_STATUS_CREATED                           => 'Created',
		self::HTTP_STATUS_ACCEPTED                          => 'Accepted',
		self::HTTP_STATUS_NO_CONTENT                        => 'No Content',
		self::HTTP_STATUS_RESET_CONTENT                     => 'Reset Content',
		self::HTTP_STATUS_PARTIAL_CONTENT                   => 'Partial Content',
		self::HTTP_STATUS_MULTIPLE_CHOICES                  => 'Multiple Choices',
		self::HTTP_STATUS_MOVED_PERMANENTLY                 => 'Moved Permanently',
		self::HTTP_STATUS_FOUND                             => 'Found',
		self::HTTP_STATUS_SEE_OTHER                         => 'See Other',
		self::HTTP_STATUS_NOT_MODIFIED                      => 'Not Modified',
		self::HTTP_STATUS_USE_PROXY                         => 'Use Proxy',
		self::HTTP_STATUS_TEMPORARY_REDIRECT                => 'Temporary Redirect',
		self::HTTP_STATUS_BAD_REQUEST                       => 'Bad Request',
		self::HTTP_STATUS_UNAUTHORIZED                      => 'Unauthorized',
		self::HTTP_STATUS_PAYMENT_REQUIRED                  => 'Payment Required',
		self::HTTP_STATUS_FORBIDDEN                         => 'Forbidden',
		self::HTTP_STATUS_NOT_FOUND                         => 'Not Found',
		self::HTTP_STATUS_METHOD_NOT_ALLOWED                => 'Method Not Allowed',
		self::HTTP_STATUS_PROXY_AUTHENTICATION_REQUIRED     => 'Proxy Authentication Required',
		self::HTTP_STATUS_INTERNAL_SERVER_ERROR             => 'Internal Server Error',
		self::HTTP_STATUS_NOT_IMPLEMENTED                   => 'Not Implemented',
		self::HTTP_STATUS_BAD_GATEWAY                       => 'Bad Gateway',
		self::HTTP_STATUS_SERVICE_UNAVAILABLE               => 'Service Unavailable',
		self::HTTP_STATUS_GATEWAY_TIMEOUT                   => 'Gateway Time-out',
	);
	
	/**
	 * Request for which this response is being
	 * created.
	 *
	 * @var PhractalRequestComponent
	 */
	protected $request;

	/**
	 * Code used to lock/unlock this response
	 *
	 * @var int
	 */
	protected $locked;
	
	/**
	 * HTTP Status Code
	 * 
	 * @var int
	 */
	protected $http_status_code;
	
	/**
	 * Message for HTTP status code
	 * 
	 * @var string
	 */
	protected $http_status_message;
	
	/**
	 * HTTP Headers
	 * 
	 * @var array
	 */
	protected $headers = array();
	
	/**
	 * Body of the response
	 * 
	 * @var string
	 */
	protected $body;
	
	/**
	 * True when the response has been sent to the client
	 * 
	 * @var bool
	 */
	protected $sent;
	
	/**
	 * Constructor
	 *
	 * @param PhractalRequestComponent $request
	 */
	public function __construct(PhractalRequestComponent $request)
	{
		parent::__construct();

		$this->request = $request;
		$this->reset();
	}

	/**
	 * Get the request
	 *
	 * @return PhractalRequestComponent
	 */
	public function get_request()
	{
		return $this->request;
	}

	/**
	 * Locks the response against changes
	 *
	 * @return int Unlock code
	 * @throws PhractalResponseComponentLockedException
	 */
	public function lock()
	{
		if ($this->locked !== null)
		{
			throw new PhractalResponseComponentLockedException();
		}

		$this->locked = rand(100, 999);
		return $this->locked;
	}

	/**
	 * Unlock the response from changes
	 *
	 * @param int $code Code received from lock function
	 * @throws PhractalResponseComponentBadUnlockCodeException
	 */
	public function unlock($code)
	{
		if ($code !== $this->locked)
		{
			throw new PhractalResponseComponentBadUnlockCodeException();
		}

		$this->locked = null;
	}
	
	/**
	 * Reset the response
	 * 
	 * @throws PhractalResponseComponentLockedException
	 */
	public function reset()
	{
		if ($this->locked !== null)
		{
			throw new PhractalResponseComponentLockedException();
		}
		
		$this->http_status_code = self::HTTP_STATUS_OK;
		$this->http_status_message = self::$http_status_messages[self::HTTP_STATUS_OK];
		$this->headers = array();
		$this->body = '';
		$this->sent = false;
	}
	
	/**
	 * Set the HTTP Status Code and Message
	 * 
	 * @param int $status
	 * @param string $message Required if status code is not a constant on this class
	 * @throws PhractalResponseComponentLockedException
	 * @throws PhractalResponseComponentUnknownHTTPStatusMessageException
	 */
	public function set_http_status($status, $message = null)
	{
		if ($this->locked !== null)
		{
			throw new PhractalResponseComponentLockedException();
		}
		
		if (isset(self::$http_status_messages[$status]))
		{
			$this->http_status_message = self::$http_status_messages[$status];
		}
		elseif ($message !== null)
		{
			$this->http_status_message = $message;
		}
		else
		{
			throw new PhractalResponseComponentUnknownHTTPStatusMessageException($status);
		}
		
		$this->http_status_code = $status;
	}
	
	/**
	 * Get the HTTP status code
	 * 
	 * @return int
	 */
	public function get_http_status_code()
	{
		return $this->http_status_code;
	}
	
	/**
	 * Get the HTTP status message
	 * 
	 * @return string
	 */
	public function get_http_status_message()
	{
		return $this->http_status_message;
	}
	
	/**
	 * Get all the HTTP headers
	 *
	 * @return array
	 */
	public function get_all_headers()
	{
		$headers = array('HTTP/1.0 ' . $this->http_status_code . ' ' . $this->http_status_message);
		
		foreach ($this->headers as $name => $values)
		{
			foreach ($values as $value)
			{
				$headers[] = $name . ': ' . $value;
			}
		}
		
		return $headers;
	}
	
	/**
	 * Add a header to the response
	 * 
	 * @see header()
	 * @param string $header
	 * @param bool $replace
	 * @throws PhractalResponseComponentLockedException
	 */
	public function add_header($header, $replace = true)
	{
		if ($this->locked !== null)
		{
			throw new PhractalResponseComponentLockedException();
		}
		
		if (strtolower(substr($header, 0, 4)) === 'http')
		{
			$parts = explode(' ', $header, 3);
			$this->set_http_status((int) $parts[1], $parts[2]);
		}
		else
		{
			list($name, $value) = array_map('trim', explode(':', $header, 2));
			
			if (isset($this->headers[$name]))
			{
				if ($replace)
				{
					$this->headers[$name] = array($value);
				}
				else
				{
					$this->headers[$name][] = $value;
				}
			}
			else
			{
				$this->headers[$name] = array($value);
			}
			
			// add 302 status if 201 or 3XX status not already present for redirects
			if (strtolower(substr($header, 0, 9)) === 'location:' && !($this->http_status_code === 201 || ($this->http_status_code >= 300 && $this->http_status_code < 400)))
			{
				$this->set_http_status(self::HTTP_STATUS_FOUND);
			}
		}
	}
	
	/**
	 * Clear all headers EXCEPT for the status header (ie. HTTP/1.0 200 OK)
	 * 
	 * @throws PhractalResponseComponentLockedException
	 */
	public function clear_headers()
	{
		if ($this->locked !== null)
		{
			throw new PhractalResponseComponentLockedException();
		}
		
		$this->headers = array();
	}
	
	/**
	 * Set the body of the response
	 * 
	 * @param string $body
	 * @throws PhractalResponseComponentLockedException
	 */
	public function set_body($body)
	{
		if ($this->locked !== null)
		{
			throw new PhractalResponseComponentLockedException();
		}
		
		$this->body = $body;
	}
	
	/**
	 * Append data to the body
	 * 
	 * @param string $body
	 * @throws PhractalResponseComponentLockedException
	 */
	public function append_to_body($body)
	{
		if ($this->locked !== null)
		{
			throw new PhractalResponseComponentLockedException();
		}
		
		$this->body .= $body;
	}
	
	/**
	 * Get the response body
	 * 
	 * @return string
	 */
	public function get_body()
	{
		return $this->body;
	}
	
	/**
	 * Set whether this response was sent to the client.
	 * 
	 * This function does NOT throw an exception when
	 * the response has been locked.
	 * 
	 * @param bool $sent
	 */
	public function set_sent()
	{
		$this->sent = true;
	}
	
	/**
	 * Get whether this response has been sent to the client or not
	 * 
	 * @return bool
	 */
	public function get_sent()
	{
		return $this->sent;
	}
	
	/**
	 * Send the response to the client
	 * 
	 * @param bool $force
	 */
	public function send_to_client($force = false)
	{
		// only send to the client if this response was for a client initiated
		// request and not sent yet. Also, allow a $force override
		if ($force || (!$this->sent && $this->request->get_client_initiated()))
		{
			$this->sent = true;
			
			foreach ($this->get_all_headers() as $header)
			{
				header($header, false);
			}
			
			echo $this->body;
		}
	}
}
