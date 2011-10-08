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
 * @package   Models
 */

/**
 * @copyright Copyright 2011 MB-it (http://www.mb-it.com)
 * @author    mbecker
 * @category  MB-it
 * @package   Models
 */
class Application_Model_Audit
{
    /**
     * @var Application_Model_Audit
     */
    static protected $_instance = null;

    /**
     * @var Zend_Log
     */
    protected $_auditLog = null;

    /**
     * Getting instance of this class
     *
     * @return Application_Model_Audit
     */
    static public function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Logging audit string
     *
     * @param string $message
     * @return Application_Model_Audit
     */
    public function log($message)
    {
        $message = trim((string) $message);
        if (!empty($message)) {
            $this->_auditLog->info($message);
        }
        return $this;
    }

     /**
     * Constructor
     */
    private function __construct()
    {
        $this->_auditLog = Zend_Registry::get('Audit_Log');
    }

    /**
     * Disable cloning of class
     *
     * @return void
     */
    private function __clone()
    { }
}