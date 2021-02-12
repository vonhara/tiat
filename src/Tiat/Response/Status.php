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
				$header = 'HTTP/1.1 ' . ( (int)$code ) . ' ' . $text;
				
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
			// Return message and if does not exists then return $code
			return match ( $code ) {
				// Standard response for successful HTTP requests.
				200 => 'OK',
				
				// The request has been fulfilled and resulted in a new resource being created.
				201 => 'Created',
				
				// The request has been accepted for processing, but the processing has not been completed.
				202 => 'Accepted',
				
				// The server successfully processed the request, but is returning information that may be from another source.
				203 => 'Non-Authoritative Information',
				
				// The server successfully processed the request, but is not returning any content. Usually with DELETE
				204 => 'No Content',
				
				// This and all future requests should be directed to the given URI.
				301 => 'Moved Permanently',
				
				// Indicates that the resource has not been modified since the version specified by the request headers If-Modified-Since or If-None-Match
				304 => 'Not Modified',
				
				// In this case, the request should be repeated with another URI; however, future requests should still use the original URI
				307 => 'Temporary Redirect',
				
				// The request, and all future requests should be repeated using another URI
				308 => 'Permanent Redirect',
				
				/**
				 *
				 */
				
				// The server cannot or will not process the request due to something that is perceived to be a client error
				400 => 'Bad Request',
				
				// Similar to 403 Forbidden, but specifically for use when authentication is required and has failed or has not yet been provided
				401 => 'Unauthorized',
				
				// The original intention was that this code might be used as part of some form of digital cash or micropayment scheme
				402 => 'Payment Required',
				
				// The request was a valid request, but the server is refusing to respond to it
				403 => 'Forbidden',
				
				// The requested resource could not be found but may be available again in the future
				404 => 'Not Found',
				
				409 => 'Conflict',
				
				// Indicates that the resource requested is no longer available and will not be available again
				410 => 'Gone',
				
				// The server is refusing to service the request because the entity of the request is in a format not supported by the requested resource for the requested method.
				415 => 'Unsupported Media Type',
				
				500 => 'Internal Server Error',
				
				502 => 'Bad Gateway',
				
				503 => 'Service Unavailable',
				
				504 => 'Gateway Timeout',
				
				// The 520 error is used as a "catch-all response when the origin server returns something unexpected"
				// Listing connection resets, large headers, and empty or invalid responses as common triggers.
				520 => 'Unknown Error',
				
				// The origin server has refused the connection from frontend
				512 => 'Web Server Is Down',
				
				// Could not negotiate a TCP handshake with the origin server
				522 => 'Connection Timed Out',
				
				// Could not reach the origin server; for example, if the DNS records for the origin server are incorrect
				523 => 'Origin Is Unreachable',
				
				// System was able to complete a TCP connection to the origin server, but did not receive a timely HTTP response
				524 => 'A Timeout Occurred',
				
				// Default is the code
				default => (string)$code
			};
		endif;
		
		return (string)$code;
	}
}
