<?php

/**
 * APPLICATION
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
 * @package   MODULE
 */

/**
 * @category MB-it
 * @package  MODULE
 */
class AuthController extends Zend_Controller_Action
{
    /**
     * user login
     */
    public function loginAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('plain');

        $cookie = $this->getRequest()->getCookie(Application_Model_Admin_User::COOKIE_NAME);

        $userModel = new Application_Model_Admin_User();
        if ($userModel->loginWithCookie($cookie)) {
            $this->_setCookie($userModel->getLogin(), $userModel->getCookie());
            $this->_redirect('/');
        }

        $form = new Application_Form_Login();
        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            if ($userModel->loginWithCredentials($this->_getParam('login'), $this->_getParam('password'))) {
                $this->_setCookie($userModel->getLogin(), $userModel->getCookie());
                $this->_redirect('/');
            }
        }

        $this->view->form = $form;
    }

    /**
     * user logout
     */
    public function logoutAction()
    {
        $registry = Zend_Registry::getInstance();
        try {
            $user = $registry->get('admin_user');

            $expire = time() - 31556926;
            $this->_setCookie($user->getLogin(), $user->getCookie(), $expire);
            $user->unsetCookie();
            $user->save();
        } catch (Exception $e) {
            $log = Zend_Registry::get('Zend_Log');
            $log->log($e->getMessage(), Zend_Log::ERR);
        }

        try {
            Zend_Auth::getInstance()->clearIdentity();
        } catch (Exception $e) {
            $log = Zend_Registry::get('Zend_Log');
            $log->log($e->getMessage(), Zend_Log::ERR);
        }

        try {
            $iterator = Zend_Session::getIterator();
            foreach ($iterator as $namespace) {
                Zend_Session::namespaceUnset($namespace);
            }
            Zend_Session::destroy(true);

        } catch (Exception $e) {
            $log = Zend_Registry::get('Zend_Log');
            $log->log($e->getMessage(), Zend_Log::ERR);
        }
        $this->_helper->redirector('login', 'auth', 'default');
    }

    /**
     * set cookie after sucessful login
     *
     * @param string $login
     * @param string $cookieHash
     * @param int $expire
     */
    protected function _setCookie($login, $cookieHash, $expire = null)
    {
        if ($expire == null) {
            $expire = time() + 31556926;
        }

        // Cookie-Daten
        $cookieName         = Application_Model_Admin_User::COOKIE_NAME;
        $cookieId           = $cookieHash;
        $cookieDomain       = 'http://' . $_SERVER['HTTP_HOST'];
        $cookieExpireTime   = $expire;

        $cookie = new Zend_Http_Cookie(
                $cookieName,
                $login . ';' . $cookieId,
                $cookieDomain,
                $cookieExpireTime,
                '/'
        );

        setcookie($cookie->getName(), $cookie->getValue(), $cookie->getExpiryTime(), $cookie->getPath());
    }
}