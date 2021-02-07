<?php
/**
 * Tiat Platform
 * LICENSE
 * This framework is using MIT licence
 *
 * @category           Platform
 * @package            Core
 * @author             Jan Theon <jan@jantheon.com>
 * @copyright          Copyright (c) 2005-2020 Jan Theon (All rights reserved)
 * @license            MIT. See also the license.txt
 */

/**
 * Bootstrap package for Tiat Platform
 *
 * @category           Platform
 * @package            Core
 * @copyright          Copyright (c) 2005-2020 Jan Theon
 * @license            MIT. See also the license.txt
 */

namespace Tiat;

//
use Error;

use function define;
use function defined;
use function file_exists;
use function getenv;
use function strtolower;
use function strtoupper;
use function version_compare;

// Check PHP Version requirements
if(version_compare(PHP_VERSION, '8.0.0') < 0):
	echo 'PHP version is too old. You need at least version 8.0.0';
	exit;
endif;

// Set base path
// Sometimes framework has linked (ln -s...) from other base/root dir so this has been cut from $_SERVER vars
$path1 = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR;
$path2 = (dirname($_SERVER['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR);

//
if($path1 !== $path2):
	// Use resolved path
	if(! defined('PATH_ROOT')):
		define('PATH_ROOT', $path2);
	endif;
else:
	if(! defined('PATH_ROOT')):
		define('PATH_ROOT', $path2);
	endif;
endif;

// Remove vars
unset($path1, $path2);

// Set RUNMODE
if(! empty(getenv('APPS_RUNMODE'))):
	define('APPS_RUNMODE', strtoupper(getenv('APPS_RUNMODE')));
else:
	define('APPS_RUNMODE', strtoupper('production'));
endif;

// Test framework index.php
if(! file_exists($framework =
	                 PATH_ROOT . ucfirst(strtolower(getenv('PLATFORM_DEFAULT') ?? 'Tiat')) . DIRECTORY_SEPARATOR .
	                 'index.php')):
	if(! file_exists($framework = PATH_ROOT . ucfirst(strtolower('tiat')) . DIRECTORY_SEPARATOR . 'index.php')):
		throw new Error('No framework exists');
	endif;
endif;

// Load framework
require_once $framework;
