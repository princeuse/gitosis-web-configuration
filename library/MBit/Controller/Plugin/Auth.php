<?php
/**
 * MB-it Gitosis Web Configuration
 *
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *
 *  - Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *
 *  - Neither the name MB-it nor the names of its contributors may be used to
 *    endorse or promote products derived from this software without specific
 *    prior written permission.
 *
 * @copyright Copyright (c) 2011-2020 MB-it (http://www.mb-it.com)
 * @author    Marc Becker <m.becker@mb-it.com>
 * @category  MB-it
 * @package   Lib
 */

/**
 * @category MB-it
 * @package  Lib
 */
class MBit_Controller_Plugin_Auth extends Zend_Controller_Plugin_Abstract
{

    /**
     * This controller plugin checks if the user tries to get to a secured page. If
     * he is not logged in, it redirects to login page.
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $accessGranted = false;
        if ($request->getModuleName() == 'default' && $request->getControllerName() == 'index' && $request->getActionName() == 'index') {
            $accessGranted = true;
        }

        if ($request->getModuleName() == 'default' && $request->getControllerName() == 'auth'  && $request->getActionName() == 'login') {
            $accessGranted = true;
        }

        if ($request->getModuleName() == 'default' && $request->getControllerName() == 'license'  && $request->getActionName() == 'index') {
            $accessGranted = true;
        }

        if ($request->getModuleName() == 'default' && $request->getControllerName() == 'error'  && $request->getActionName() == 'error') {
            $accessGranted = true;
        }

        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $identity = $auth->getIdentity();
            $userModel = new Application_Model_Admin_User();
            $userModel->load($identity->{'admin_id'});
            Zend_Registry::set('admin_user', $userModel);
        }

        if (!$accessGranted && !$auth->hasIdentity()) {
            $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
            $redirector->direct('login', 'auth', 'default', array());
        }
    }
}
