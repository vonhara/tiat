<?php
/**
 * Tiat Platform
 * LICENSE
 * This framework is using MIT licence
 *
 * @category     Platform
 * @package      Tools
 * @author       Jan Theon <jan@jantheon.com>
 * @copyright    Copyright (c) 2005-2021 Jan Theon (All rights reserved)
 * @license      MIT. See also the license.txt
 */

/**
 * Bootstrap package for Tiat Platform
 *
 * @category     Platform
 * @package      Tools
 * @copyright    Copyright (c) 2005-2021 Jan Theon
 * @license      MIT. See also the license.txt
 */
namespace Tiat\Tools;

//
use JetBrains\PhpStorm\Pure;

use function filter_var;
use function gmp_init;
use function gmp_strval;
use function implode;
use function ip2long;
use function long2ip;
use function str_pad;
use function strlen;
use function trim;

/**
 * Class Network
 *
 * @package Tiat\Tools
 */
class Network {

	/**
	 * Detect client IP, return IP address
	 *
	 * @param    bool    $checkProxy
	 *
	 * @return string
	 */
	final public function detectClient(bool $checkProxy = TRUE) : string {
		if($checkProxy && ! is_null($this->getServer('HTTP_CLIENT_IP'))):
			$ip = $this->getServer('HTTP_CLIENT_IP');
		elseif($checkProxy && ! is_null($this->getServer('HTTP_X_FORWARDED_FOR'))):
			$ip = $this->getServer('HTTP_X_FORWARDED_FOR');
		else:
			$ip = $this->getServer('REMOTE_ADDR');
		endif;

		return $ip;
	}

	/**
	 * Retrieve a member of the $_SERVER superglobals
	 * If no $key is passed, returns the entire $_SERVER array.
	 *
	 * @param    null|string    $key
	 * @param    null           $default    Default value to use if key not found
	 *
	 * @return null|array|mixed
	 */
	final public function getServer(string $key = NULL, $default = NULL) {
		if(NULL === $key):
			return $_SERVER;
		endif;

		return $_SERVER[$key] ?? $default;
	}

	/**
	 * Convert IP to integer. IP can be IPv4 or IPv6
	 *
	 * @param    string    $ip
	 * @param    bool      $private    Allow private/reserved IP addresses
	 * @param    bool      $string     Return as the string, not an array
	 *
	 * @return null|array|string
	 */
	final public function convertToInteger(string $ip, bool $private = TRUE, bool $string = FALSE) : array|string|null {
		if($private || (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE))):
			// Private networks allowed? If not then add 'FILTER_FLAG_NO_PRIV_RANGE'
			if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)):
				$result = $this->convert4($ip);
			elseif(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)):
				$result = $this->convert6($ip);
			endif;

			if($string && isset($result) && is_array($result)):
				return implode('', $result);
			else:
				return $result ?? NULL;
			endif;
		endif;

		return NULL;
	}

	/**
	 * Convert IPv4 to integer
	 *
	 * @param    null|string    $ip
	 *
	 * @return null|array
	 */
	final public function convert4(string $ip = NULL) : ?array {
		if($ip !== NULL):
			return [ip2long($ip)];
		endif;

		return NULL;
	}

	/**
	 * Convert IPv6 to 2 INT64 blocks
	 *
	 * @param    null|string    $ip
	 *
	 * @return null|array
	 */
	final public function convert6(string $ip = NULL) : ?array {
		if($ip !== NULL):
			if(strlen($ip) !== 39):
				$ip = $this->ipv6Expand($ip);
			endif;

			$ip      = str_replace(':', '', $ip);
			$part[0] = gmp_strval('0x' . substr($ip, 0, 16));
			$part[1] = gmp_strval('0x' . substr($ip, 16));

			return [$part[0], $part[1]];
		endif;

		return NULL;
	}

	/**
	 * Return the expanded IPv6 in correct format with leading zeros
	 *
	 * @param    string    $ip
	 *
	 * @return null|string
	 */
	#[Pure] final public function ipv6Expand(string $ip) {
		if($ip !== NULL):
			$ip     = trim($ip);
			$length = strlen($ip);

			if($length > 0 && $length <= 39):
				$array = explode(':', $ip);
				foreach($array as &$val):
					$val = str_pad($val, 4, '0', STR_PAD_LEFT);
				endforeach;
			endif;

			return implode(':', $array);
		endif;

		return NULL;
	}

	/**
	 * Validate IP address
	 *
	 * @param    string     $ip
	 * @param    boolean    $private    Allow private networks (default: true)
	 *
	 * @return  boolean
	 */
	final public function validate(string $ip, bool $private = TRUE) : bool {
		if(! $result = $this->valid4($ip, $private)):
			$result = $this->valid6($ip, $private);
		endif;

		return $result;
	}

	/**
	 * Validate IPv4. If $private is TRUE then private addresses also accepted
	 *
	 * @param    string     $ip
	 * @param    boolean    $private
	 *
	 * @return  boolean
	 */
	final public function valid4(string $ip, bool $private = TRUE) : bool {
		if($ip):
			if($private):
				return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
			else:
				return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE);
			endif;
		endif;

		return FALSE;
	}

	/**
	 * Validate IPv6
	 *
	 * @param    string     $ip
	 * @param    boolean    $private
	 *
	 * @return  boolean
	 */
	#[Pure] final public function valid6(string $ip, bool $private = TRUE) : bool {
		if($ip):
			if($private):
				return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
			else:
				return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE);
			endif;
		endif;

		return FALSE;
	}

	/**
	 * Return integer to IPv4
	 *
	 * @param    int    $value
	 *
	 * @return  string|NULL
	 */
	final public function address4(int $value) {
		if($value !== NULL):
			return long2ip($value);
		endif;

		return NULL;
	}

	/**
	 * Convert INT64 blocks to valid IPv6 address
	 *
	 * @param    null    $first
	 * @param    null    $second
	 *
	 * @return null|string
	 */
	final public function address6($first = NULL, $second = NULL) : ?string {
		if($first !== NULL && $second !== NULL):
			//
			$p1      = $this->_address6($first);
			$p2      = $this->_address6($second);
			$address = $p1 . $p2;
			$counter = 0;
			$result  = "";

			//
			while(1):
				if($counter > 0):
					$result .= ':';
				endif;

				$result .= substr($address, $counter * 4, 4);
				$counter++;

				if($counter >= 8):
					break;
				endif;
			endwhile;

			// Return valid IPv6 address
			return inet_ntop(inet_pton($result));
		endif;

		return NULL;
	}

	/**
	 * @param    string    $val
	 *
	 * @return  string
	 */
	#[Pure] private function _address6(string $val = NULL) : string {
		return str_pad(gmp_strval(gmp_init($val), 16), 16, '0', STR_PAD_LEFT);
	}
}
