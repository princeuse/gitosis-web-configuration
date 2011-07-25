<?php
/**
 * All rights reserved.
 * 
 * The use and redistribution of this software, either compiled or uncompiled,
 * with or without modifications are permitted provided that the following
 * conditions are met:
 * 
 * - Redistributions of compiled or uncompiled source must contain the above
 *   copyright notice, this list of the conditions and the following disclaimer
 * 
 * - All advertising materials mentioning features or use of this software must
 *   display the following acknowledgement: "This product includes software
 *   developed by MBit (http://www.mb-it.com)."
 * 
 * - The name MBit or the website http://www.mb-it.com may not be used to
 *   endorse or promote products derived from this software without specific
 *   prior written permission.
 * 
 * This software is provided by MB-it without any express or implied warranties.
 * MB-it is under no condition liable for the functional capability of this
 * software for a certain purpose or the general usability. MB-it is under no
 * condition liable for any direct or indirect damages resulting from the use of
 * the software. Liability and Claims for damages of any kind are excluded.
 *
 * @copyright Copyright 2011 MB-it (http://www.mb-it.com)
 * @author    mbecker
 * @category  MB-it
 * @package   MODULE
 */
error_reporting(E_ALL | E_STRICT);

define('APPLICATION_ENV', 'unit-testing');
define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

set_include_path(
    implode(
        PATH_SEPARATOR,
        array (
            '.',
            realpath(APPLICATION_PATH . '/../library'),
            get_include_path()
        )
    )
);

require_once 'Zend/Loader/Autoloader.php';
require_once 'Zend/Application.php';

$loader = Zend_Loader_Autoloader::getInstance();

Zend_Session::$_unitTestEnabled = true;

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

$application->bootstrap();
Zend_Registry::set('app', $application);