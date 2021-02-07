<?php
/**
 * Tiat Platform
 * LICENSE
 * This framework is using MIT licence
 *
 * @category     Platform
 * @package      Uri
 * @author       Jan Theon <jan@jantheon.com>
 * @copyright    Copyright (c) 2005-2021 Jan Theon (All rights reserved)
 * @license      MIT. See also the license.txt
 */

/**
 * Bootstrap package for Tiat Platform
 *
 * @category     Platform
 * @package      Uri
 * @copyright    Copyright (c) 2005-2021 Jan Theon
 * @license      MIT. See also the license.txt
 */
namespace Tiat\Uri;

//
use JetBrains\PhpStorm\Pure;
use Tiat\Tools\Network;

use function explode;
use function is_scalar;
use function parse_str;
use function strpos;
use function substr;

/**
 * Class Base
 *
 * @package Tiat\Uri
 */
class Base {

	// Declare constants
	public const SCHEME_HTTP  = 'http';
	public const SCHEME_HTTPS = 'https';

	//
	private string $_requestUri;

	/**
	 * @param    null|string    $uri
	 *
	 * @return  array
	 */
	final public function factory(string $uri = NULL) : array {
		return $this->getUriArray($uri);
	}

	/**
	 * @param    null|string    $uri
	 *
	 * @return array
	 */
	final public function getUriArray(string $uri = NULL) : array {
		// URL encode
		if(! empty($result = explode('/', $this->getUri($uri)))):
			foreach($result as $key => &$val):
				$val = urldecode($val);
			endforeach;
		endif;

		return $result;
	}

	/**
	 * Get URI
	 *
	 * @param    null|string    $uri
	 *
	 * @return string
	 */
	final public function getUri(string $uri = NULL) : string {
		//
		$uri = $uri ?? $this->getRequestUri();

		//
		if($uri[0] === DIRECTORY_SEPARATOR):
			$uri = substr($uri, 1);
		endif;

		//
		if(str_contains($uri, '?')):
			$uri = substr($uri, 0, strpos($uri, '?'));
		endif;

		return $uri;
	}

	/**
	 * Get Request URI
	 *
	 * @param    null|string    $uri
	 *
	 * @return string
	 */
	final public function getRequestUri(string $uri = NULL) : string {
		if(empty($this->_requestUri)):
			$this->_setRequestUri($uri);
		endif;

		return $this->_requestUri;
	}

	/**
	 * @param    null|string    $requestUri
	 *
	 * @return $this
	 */
	final protected function _setRequestUri(string $requestUri = NULL) {
		if($requestUri === NULL):
			// Check this first so IIS will catch
			if(isset($_SERVER['HTTP_X_REWRITE_URL'])):
				$requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
			elseif(isset($_SERVER['IIS_WasUrlRewritten'], $_SERVER['UNENCODED_URL']) &&
			       $_SERVER['IIS_WasUrlRewritten'] === '1' && $_SERVER['UNENCODED_URL'] !== ''):
				// IIS7 with URL Rewrite: make sure we get the unencoded url (double slash problem)
				$requestUri = $_SERVER['UNENCODED_URL'];
			elseif(isset($_SERVER['REQUEST_URI'])):
				//
				$requestUri = $_SERVER['REQUEST_URI'];

				// HTTP proxy reqs setup request uri with scheme and host [and port] + the url path, only use url path
				$schemeAndHttpHost = $this->getScheme() . '://' . $this->getHttpHost();

				//
				if(str_starts_with($requestUri, $schemeAndHttpHost)):
					$requestUri = substr($requestUri, strlen($schemeAndHttpHost));
				endif;
			elseif(isset($_SERVER['ORIG_PATH_INFO'])):
				// PHP as CGI & older IIS
				$requestUri = $_SERVER['ORIG_PATH_INFO'];

				if(! empty($_SERVER['QUERY_STRING'])):
					$requestUri .= '?' . $_SERVER['QUERY_STRING'];
				endif;
			else:
				return $this;
			endif;
		elseif(! is_string($requestUri)):
			return $this;
		else:
			// Set GET items, if available
			if(FALSE !== ($pos = strpos($requestUri, '?'))):
				// Get key => value pairs and set $_GET
				$query = substr($requestUri, $pos + 1);
				parse_str($query, $vars);
				$this->getQuery($vars);
			endif;
		endif;

		//
		$this->_requestUri = $requestUri;

		return $this;
	}

	/**
	 * Get PATH from uri (everything between server name & '?')
	 *
	 * @param    null|string    $uri
	 *
	 * @return  string
	 */
	final public function getPath(string $uri = NULL) : string {
		$u = $this->getRequestUri($uri);

		if(str_contains($u, '?')):
			$path = substr($u, 0, strpos($u, '?'));
		else:
			$path = $u;
		endif;

		return $path ?? '';
	}

	/**
	 * Get query params in array
	 *
	 * @param    null|string    $uri
	 *
	 * @return array
	 */
	final public function getQueryArray(string $uri = NULL) : array {
		$query = $this->getQuery($uri);

		// Parse to key/value
		parse_str($query, $result);

		// Return validated array
		if(count($resultset = $this->_validateQuery($result))):
			return $resultset;
		endif;

		return [];
	}

	/**
	 * Get query string (everything after '?')
	 *
	 * @param    null|string    $uri
	 *
	 * @return  string
	 */
	final public function getQuery(string $uri = NULL) : string {
		if($uri === NULL):
			$uri = $this->getRequestUri($uri);
			$uri = substr($uri, strpos($uri, '?') + 1);
		endif;

		return substr($uri, strpos($uri, '?'));
	}

	/**
	 * @param    array    $array
	 *
	 * @return  array
	 */
	private function _validateQuery(array $array = []) : array {
		if(! empty($array)):
			foreach($array as $key => $val):
				$k = $this->_validate($key);

				if(is_array($val)):
					foreach($val as $val2):
						$v[] = $this->_validate($val2);
					endforeach;
				else:
					$v = $this->_validate($val);
				endif;

				// Do not allow null values
				if(! empty($k) && ! empty($v)):
					$result[$k] = $v;
				endif;

				unset($k, $v);
			endforeach;

			if(isset($result)):
				return $result;
			endif;
		endif;

		return [];
	}

	/**
	 * Check query values
	 *
	 * @param    scalar    $value
	 *
	 * @return  string|boolean
	 */
	#[Pure] private function _validate($value = NULL) {
		if(is_scalar($value) && ! empty($value)):
			return (string)$value;
		endif;

		return FALSE;
	}

	/**
	 * Get the request URI scheme
	 *
	 * @return string
	 */
	#[Pure] final public function getScheme() : string {
		return ($this->getServer('HTTPS') === 'on') ? self::SCHEME_HTTPS : self::SCHEME_HTTP;
	}

	/**
	 * Retrieve a member of the $_SERVER superglobal
	 * If no $key is passed, returns the entire $_SERVER array.
	 *
	 * @param    string    $key
	 * @param    mixed     $default    Default value to use if key not found
	 *
	 * @return  mixed        Returns null if key does not exist
	 */
	final public function getServer($key = NULL, $default = NULL) : mixed {
		if(NULL === $key):
			return $_SERVER;
		endif;

		//
		return $_SERVER[$key] ?? $default;
	}

	/**
	 * Get the HTTP host.
	 * "Host" ":" host [ ":" port ]
	 * Note the HTTP Host header is not the same as the URI host.
	 * It includes the port while the URI host doesn't.
	 *
	 * @return string
	 */
	#[Pure] final public function getHttpHost() : string {
		$host = $this->getServer('HTTP_HOST');

		if(! empty($host)):
			return $host;
		endif;

		$scheme = $this->getScheme();
		$name   = $this->getServer('SERVER_NAME');
		$port   = $this->getServer('SERVER_PORT');

		if(NULL === $name):
			return '';
		elseif(($scheme === self::SCHEME_HTTP && $port === 80) || ($scheme === self::SCHEME_HTTPS && $port === 443)):
			return $name;
		else:
			return $name . ':' . $port;
		endif;
	}

	/**
	 * @param    null|string    $host
	 *
	 * @return string
	 */
	public function getDomainTld(string $host = NULL) : string {
		// This will work ONLY with TOP-LEVEL domains
		// So example www.your-domain.co.uk will return UK (not co.uk)
		// Working with gTLD + sTLD + ccTLD
		if(! (new Network())->valid4($host, TRUE)):
			if(! empty($tmp = array_reverse(explode('.', $host)))):
				return $tmp[0] ?? '';
			endif;
		endif;

		return '';
	}
}
