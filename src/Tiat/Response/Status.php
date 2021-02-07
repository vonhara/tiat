<?php
/**
 * Tiat Platform
 * LICENSE
 * This framework is using MIT licence
 *
 * @category     Platform
 * @package      Response
 * @author       Jan Theon <jan@jantheon.com>
 * @copyright    Copyright (c) 2005-2021 Jan Theon (All rights reserved)
 * @license      MIT. See also the license.txt
 */

/**
 * Bootstrap package for Tiat Platform
 *
 * @category     Platform
 * @package      Response
 * @copyright    Copyright (c) 2005-2021 Jan Theon
 * @license      MIT. See also the license.txt
 */
namespace Tiat\Response;

//
use function filter_var;
use function header;
use function is_numeric;

/**
 * Class Status
 *
 * @package Tiat\Response
 */
class Status {

	/**
	 * Set official HTTP status code
	 *
	 * @param    integer        $code        Official HTTP status code number
	 * @param    string|null    $redirect    Redirect to address (if needed)
	 * @param    boolean        $exit        Stop execution
	 */
	final public static function setResponse(int $code, string $redirect = NULL, bool $exit = FALSE) {
		if(is_numeric($code) && $code > 0):
			// Get status text by code
			if(! empty($text = self::getResponse($code))):
				// Modify text
				$header = 'HTTP/1.1 ' . ((int)$code) . ' ' . $text;

				// Print header
				header($header);
			endif;

			// Redirect
			if(! empty($redirect) && filter_var($redirect, FILTER_VALIDATE_URL)):
				header('Location: ' . $redirect);
			endif;

			if($exit):
				exit;
			endif;
		endif;
	}

	/**
	 * Return status text by code
	 *
	 * @param    int    $code
	 *
	 * @return  string
	 */
	final public static function getResponse(int $code = 0) : string {
		if($code > 0):
			switch($code):
				// Standard response for successful HTTP requests.
				case "200":
					return 'OK';

				// The request has been fulfilled and resulted in a new resource being created.
				case "201":
					return 'Created';

				// The request has been accepted for processing, but the processing has not been completed.
				case "202":
					return 'Accepted';

				// The server successfully processed the request, but is returning information that may be from another source.
				case "203":
					return 'Non-Authoritative Information';

				// The server successfully processed the request, but is not returning any content. Usually with DELETE
				case "204":
					return 'No Content';

				// This and all future requests should be directed to the given URI.
				case "301":
					return 'Moved Permanently';

				// Indicates that the resource has not been modified since the version specified by the request headers If-Modified-Since or If-None-Match
				case "304":
					return 'Not Modified';

				// In this case, the request should be repeated with another URI; however, future requests should still use the original URI
				case "307":
					return 'Temporary Redirect';

				// The request, and all future requests should be repeated using another URI
				case "308":
					return 'Permanent Redirect';

				// The server cannot or will not process the request due to something that is perceived to be a client error
				case "400":
					return 'Bad Request';

				// Similar to 403 Forbidden, but specifically for use when authentication is required and has failed or has not yet been provided
				case "401":
					return 'Unauthorized';

				// The original intention was that this code might be used as part of some form of digital cash or micropayment scheme
				case "402":
					return 'Payment Required';

				// The request was a valid request, but the server is refusing to respond to it
				case "403":
					return 'Forbidden';

				// The requested resource could not be found but may be available again in the future
				case "404":
					return 'Not Found';

				case "409":
					return 'Conflict';

				// Indicates that the resource requested is no longer available and will not be available again
				case "410":
					return 'Gone';

				// The server is refusing to service the request because the entity of the request is in a format not supported by the requested resource for the requested method.
				case "415":
					return 'Unsupported Media Type';

				case "500":
					return 'Internal Server Error';

				case "502":
					return 'Bad Gateway';

				case "503":
					return 'Service Unavailable';

				case "504":
					return 'Gateway Timeout';

				// The 520 error is used as a "catch-all response when the origin server returns something unexpected"
				// Listing connection resets, large headers, and empty or invalid responses as common triggers.
				case "520":
					return 'Unknown Error';

				// The origin server has refused the connection from frontend
				case "521":
					return 'Web Server Is Down';

				// Could not negotiate a TCP handshake with the origin server
				case "522":
					return 'Connection Timed Out';

				// Could not reach the origin server; for example, if the DNS records for the origin server are incorrect
				case "523":
					return 'Origin Is Unreachable';

				// System was able to complete a TCP connection to the origin server, but did not receive a timely HTTP response
				case "524":
					return 'A Timeout Occurred';
			endswitch;
		endif;

		return '';
	}
}
