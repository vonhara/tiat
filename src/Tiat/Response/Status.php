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
	 * 1xx: Informational - Request received, continuing process
	 * 2xx: Success - The action was successfully received, understood, and accepted
	 * 3xx: Redirection - Further action must be taken in order to complete the request
	 * 4xx: Client Error - The request contains bad syntax or cannot be fulfilled
	 * 5xx: Server Error - The server failed to fulfill an apparently valid request
	 *
	 * @param    int    $code
	 *
	 * @return  string
	 */
	final public static function getResponse(int $code = 0) : string {
		if($code > 0):
			// Return message and if does not exists then return $code
			return match ( $code ) {
				// RFC7231
				100 => 'Continue',
				
				// RFC7231
				101 => 'Switching Protocols',
				
				// RFC2518
				102 => 'Processing',
				
				// RFC8297
				103 => 'Early Hints',
				
				// RFC7231: Standard response for successful HTTP requests.
				200 => 'OK',
				
				// RFC7231: The request has been fulfilled and resulted in a new resource being created.
				201 => 'Created',
				
				// RFC7231: The request has been accepted for processing, but the processing has not been completed.
				202 => 'Accepted',
				
				// RFC7231: The server successfully processed the request, but is returning information that may be from another source.
				203 => 'Non-Authoritative Information',
				
				// RFC7231: The server successfully processed the request, but is not returning any content. Usually with DELETE
				204 => 'No Content',
				
				// RFC7231
				205 => 'Reset Content',
				
				// RFC7233
				206 => 'Partial Content',
				
				// RFC4918
				207 => 'Multi-Status',
				
				// RFC3229
				208 => 'Already Reported',
				
				// RFC7231
				300 => 'Multiple Choices',
				
				// RFC7231: This and all future requests should be directed to the given URI.
				301 => 'Moved Permanently',
				
				// RFC7231
				302 => 'Found',
				
				// RFC7231
				303 => 'See Other',
				
				// RFC7232: Indicates that the resource has not been modified since the version specified by the request headers If-Modified-Since or If-None-Match
				304 => 'Not Modified',
				
				// RFC7231
				305 => 'Use Proxy',
				
				// RFC7231: In this case, the request should be repeated with another URI; however, future requests should still use the original URI
				307 => 'Temporary Redirect',
				
				// RFC7538: The request, and all future requests should be repeated using another URI
				308 => 'Permanent Redirect',
				
				// RFC7231: The server cannot or will not process the request due to something that is perceived to be a client error
				400 => 'Bad Request',
				
				// Similar to 403 Forbidden, but specifically for use when authentication is required and has failed or has not yet been provided
				401 => 'Unauthorized',
				
				// RFC7231: The original intention was that this code might be used as part of some form of digital cash or micropayment scheme
				402 => 'Payment Required',
				
				//RFC7231:  The request was a valid request, but the server is refusing to respond to it
				403 => 'Forbidden',
				
				// RFC7231: The requested resource could not be found but may be available again in the future
				404 => 'Not Found',
				
				// RFC7231
				405 => 'Method Not Allowed',
				
				// RFC7231
				406 => 'Not Acceptable',
				
				// RFC7235
				407 => 'Proxy Authentication Required',
				
				// RFC7231
				408 => 'Request Timeout',
				
				// RFC7231
				409 => 'Conflict',
				
				// RFC7231_ Indicates that the resource requested is no longer available and will not be available again
				410 => 'Gone',
				
				// RFC7231
				411 => 'Length Required',
				
				// RFC7232
				412 => 'Precondition Failed',
				
				// RFC7231
				413 => 'Payload Too Large',
				
				// RFC7231
				414 => 'URI Too Long',
				
				// RFC7231: The server is refusing to service the request because the entity of the request is in a format not supported by the requested resource for the requested method.
				415 => 'Unsupported Media Type',
				
				// RFC7233
				416 => 'Range Not Satisfiable',
				
				// RFC7231
				417 => 'Expectation Failed',
				
				// RFC7540
				421 => 'Misdirected Request',
				
				// RFC7450
				422 => 'Unprocessable Entity',
				
				// RFC4918
				423 => 'Locked',
				
				// RFC4918
				424 => 'Failed Dependency',
				
				// RFC8470
				425 => 'Too Early',
				
				// RFC7231
				426 => 'Upgrade Required',
				
				// RFC6585
				428 => 'Precondition Required',
				
				// RFC6585
				429 => 'Too Many Requests',
				
				// RFC6585
				431 => 'Request Header Fields Too Large',
				
				// RFC7725
				451 => 'Unavailable For Legal Reasons',
				
				// RFC7231
				500 => 'Internal Server Error',
				
				// RFC7231
				501 => 'Not Implemented',
				
				// RFC7231
				502 => 'Bad Gateway',
				
				// RFC7231
				503 => 'Service Unavailable',
				
				// RFC7231
				504 => 'Gateway Timeout',
				
				// RFC7231
				505 => 'HTTP Version Not Supported',
				
				// RFC2295
				506 => 'Variant Also Negotiates',
				
				// RFC4918
				507 => 'Insufficient Storage',
				
				// RFC5842
				508 => 'Loop Detected',
				
				// RFC2774
				510 => 'Not Extended',
				
				// RFC6585
				511 => 'Network Authentication Required',
				
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
				
				// Default is the code which we don't understand
				default => (string)$code
			};
		endif;
		
		//
		return (string)$code;
	}
}
