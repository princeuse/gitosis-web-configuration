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
 * This controller plugin checks if the user tries to get to a secured page. If
 * he is not logged in, it redirects to login page.
 *
 * @category MB-it
 * @package  Lib
 */
class MBit_Controller_Plugin_Auth extends Zend_Controller_Plugin_Abstract
{

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $auth = Zend_Auth::getInstance();

        if (!$auth->hasIdentity() &&
            !( $request->getModuleName()     == "default" &&
               $request->getControllerName() == "auth" &&
               $request->getActionName()     == "login") )
        {
            $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
            $redirector->direct('login', 'auth', 'default', array());
        }
        else {
            if (!( $request->getModuleName()     == "default" &&
                   $request->getControllerName() == "auth" &&
                   $request->getActionName()     == "login")) {
                $cookieValue = $this->getRequest()->getCookie('netz98_login');
                $dataArray = explode(';', $cookieValue);
                $user = Entity_User::getByCookie($dataArray[0], $dataArray[1]);
                $registry = Zend_Registry::getInstance();
                $registry->set('User', $user);
            }
        }
    }

}