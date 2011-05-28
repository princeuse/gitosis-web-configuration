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
 * @package   Models
 */

/**
 * @category MB-it
 * @package  Models
 */
class Application_Model_Mail_Password extends Application_Model_Mail
{
    /**
     * this flag indicates, whether the password was forgotten
     *
     * @var bool
     */
    protected $_forgotPassword = false;

    /**
     * @var Application_Model_Admin_User
     */
    protected $_user = null;

    /**
     * @param bool $flag
     * @return Entity_Mail_Password
     */
    public function setPasswordAsForgotten($flag = true)
    {
        $this->_forgotPassword = (bool) $flag;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPasswordForgotten()
    {
        return $this->_forgotPassword;
    }

    /**
     * @param Application_Model_Admin_User $user
     * @return Application_Model_Mail_Password
     */
    public function setUser(Application_Model_Admin_User $user)
    {
        $this->_user = $user;
        return $this;
    }

    /**
     * @param string $receiverMail
     * @param string $receiverName
     * @param Zend_Mail_Transport_Abstract $transport
     * @return Application_Model_Mail_Password
     */
    public function sendPassword()
    {
        if (empty($this->_user)) {
            throw new MBit_Exception('user model has to be set for sending password mails');
        }
        $this->addTo($this->_user->getEmail());
        if ($this->isPasswordForgotten()) {
            $this->setSubject('Gitosis-Webconfiguration: Passwort vergessen');
        } else {
            $this->setSubject('Gitosis-Webconfiguration: dein Zugang wurde angelegt');
        }
        $this->setBodyHtml($this->_getMailText());
        
        return parent::send();
    }

    /**
     * E-Mailtext erstellen
     *
     * @return string
     */
    protected function _getMailText()
    {
        $viewPath = MBit_Theme::getInstance()->getMailTemplatePath() . DIRECTORY_SEPARATOR;
        $view = new Zend_View();
        $view->setScriptPath($viewPath);

        $view->login = $this->_user->getLogin();
        $view->password = $this->_user->generateNewPassword();

        $html = null;
        if ($this->isPasswordForgotten()) {
            $html = $view->render('password-forgotten.phtml');
        } else {
            $html = $view->render('password.phtml');
        }
        return $html;
    }
}