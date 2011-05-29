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
class AdminController extends MBit_Controller_Crud
{

    public function sendPasswordAction()
    {
        $userId = $this->_getParam('id');
        if (intval($userId) > 0) {
            $userModel = new Application_Model_Admin_User();
            $userModel->load($userId);
            if ($userModel->getId()) {
                $mail = new Application_Model_Mail_Password();
                $mail->setUser($userModel)
                     ->setPasswordAsForgotten(true);

                $mail->send();
                if ($userModel->save()) {
                    $message = "Das Passwort wurde erfolgreich zurück gesetzt und versendet";
                } else {
                    $message = "Das Passwort konnte nicht zurück gesetzt und versendet werden";
                }

                $this->_helper->FlashMessenger->addMessage($message);
            }
        }
        $this->_redirectToList();
    }

    /**
     * sending new password via mail after creation of a new user
     */
    protected function _beforeCreateSave()
    {
        $mail = new Application_Model_Mail_Password();
        $mail->setUser($this->_model);
        $mail->setPasswordAsForgotten(false);
        $mail->send();
    }

    /**
     * @return Application_Form_Admin_User
     */
    protected function _getForm()
    {
        if (empty($this->_form)) {
            $this->_form = new Application_Form_Admin_User();
        }
        return $this->_form;
    }

    /**
     * getting url to list action
     *
     * @return string
     */
    protected function _getListUrl()
    {
        return $this->view->url(
            array(
                'action'     => 'list',
                'controller' => 'admin'
            ),
            null,
            true
        );
    }

    /**
     * @return Application_Model_Admin_User
     */
    protected function _getModel()
    {
        if (empty($this->_model)) {
            $this->_model = new Application_Model_Admin_User();
        }
        return $this->_model;
    }
}