<?php
/**
 * Tiat Platform
 * LICENSE
 * This framework is using MIT licence
 *
 * @category     Platform
 * @package      Core
 * @author       Jan Theon <jan@jantheon.com>
 * @copyright    Copyright (c) 2005-2021 Jan Theon (All rights reserved)
 * @license      MIT. See also the license.txt
 */

/**
 * Bootstrap package for Tiat Platform
 *
 * @category     Platform
 * @package      Core
 * @copyright    Copyright (c) 2005-2021 Jan Theon
 * @license      MIT. See also the license.txt
 */
namespace Tiat;

//
use function file_exists;

// Construct Loader
$filename = PATH_ROOT . 'Tiat' . DIRECTORY_SEPARATOR . 'Loader.php';
if(file_exists($filename)):
	require_once $filename;
	
	//
	$loader = new Loader();
	$loader->run(PATH_ROOT ?? DIRECTORY_SEPARATOR);
	$loader->boot();
else:
	echo 'Loader does not exists';
	exit;
endif;
