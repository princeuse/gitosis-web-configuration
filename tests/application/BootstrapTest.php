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
 * @package   UnitTesting
 */

/**
 * @copyright Copyright 2011 MB-it (http://www.mb-it.com)
 * @author    mbecker
 * @category  MB-it
 * @package   UnitTesting
 */
class BootstrapTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Zend_Application
     */
    protected $_application = null;

    /**
     * @var array
     */
    protected $_resources = array(
        'view'              => 'Zend_View',
        'navigation'        => 'Zend_Navigation',
        'ControllerPlugins' => 'Zend_Controller_Front',
        'locale'            => 'Zend_Locale',
        'autoload'          => 'Zend_Loader_Autoloader',
        'registry'          => 'Zend_Registry',
        'database'          => 'Zend_Db_Adapter_Pdo_Mysql',
        'logger'            => 'Zend_Log',
        'AuditLog'          => 'Zend_Log',
    );
    
    /**
     * getting application out of bootstrap
     */
    public function setUp()
    {
        $this->_application = Zend_Registry::get('app');
        return parent::setUp();
    }

    /**
     * @test
     */
    public function checkAllResourcesExist ()
    {
        foreach ($this->_resources as $name => $class) {
            $this->assertTrue($this->_application->getBootstrap()->hasResource($name), $name);
        }
    }

    /**
     * @depends checkAllResourcesExist
     * @test
     */
    public function checkAllResourcesType ()
    {
        foreach ($this->_resources as $name => $class) {
            $this->assertInstanceOf($class, $this->_application->getBootstrap()->getResource($name));
        }
    }

    /**
     * @depends checkAllResourcesType
     * @test
     */
    public function viewHasHelperPaths ()
    {
        $helperPaths = $this->_application->getBootstrap()->getResource('view')->getHelperPaths();

        $this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $helperPaths);
        $this->assertArrayHasKey('MBit_View_Helper_', $helperPaths);
    }
}

