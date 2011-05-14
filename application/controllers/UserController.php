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
 * @package   Controller
 */

/**
 * @category MB-it
 * @package  Controller
 */
class UserController extends MBit_Controller_Crud
{

    /**
     * initialise json action(s)
     */
    public function init()
    {
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        $contextSwitch->addActionContext('ajax', 'json')
                      ->initContext();
    }

    /**
     * listing all groups
     *
     * if no user id is given, this action redirects back to list
     */
    public function groupAction()
    {
        $id = $this->_getParam('id');
        if (empty($id)) {
            $this->_redirectToList();
        }

        $user = new Application_Model_Gitosis_User();
        $user->load($id);

        $group = new Application_Model_Gitosis_Group();

        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pager.phtml');
        $paginator = Zend_Paginator::factory(
                        $group->getPaginatorSelect(), 'Object', array('MBit_Paginator_Adapter_' => 'MBit/Paginator/Adapter')
        );
        $paginator->getAdapter()->setObjectName('Application_Model_Gitosis_Group');
        $paginator->setDefaultItemCountPerPage(20);
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));

        $this->view->pager = $paginator;
        $this->view->user = $user->getName();
        $this->view->userId = $user->getId();
    }

    /**
     * adding/removing groups of a user
     *
     * call of this action is only allowed by ajax requests, all other requests
     * are redirected to group listing
     */
    public function ajaxAction()
    {
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        if ($contextSwitch->getCurrentContext() !== 'json') {
            $this->getHelper('Redirector')->gotoSimple('group');
        }

        $action  = $this->_getParam('operation');
        $userId  = $this->_getParam('userId');
        $groupId = $this->_getParam('groupId');

        $message = '';
        $user = new Application_Model_Gitosis_User();
        $user->load($userId);

        if (!$user->getId()) {
            $message = 'Der Benutzer existiert nicht';
        } elseif (!empty($action) && ($action == 'add' || $action == 'remove')) {

            if ($action == 'add') {
                $user->addGroup($groupId);
            } elseif ($action == 'remove') {
                $user->removeGroup($groupId);
            }

            if ($user->save()) {
                $message = "Die Änderung des Datensatzes war erfolgreich";
            } else {
                $message = "Es trat ein Fehler beim Ändern des Datensatzes auf";
            }
        } else {
            $message = 'Es wurde eine nicht bekannte Aktion "' . $action . '" verwendet';
        }
        $this->view->message = $message;
    }

    /**
     * form for editing/creating users
     *
     * @return Zend_Form
     */
    protected function _getForm()
    {
        if (empty($this->_form)) {
            $this->_form = new Application_Form_User();
        }
        return $this->_form;
    }

    /**
     * user model
     *
     * @return Application_Model_Gitosis_User
     */
    protected function _getModel()
    {
        if (empty($this->_model)) {
            $this->_model = new Application_Model_Gitosis_User();
        }
        return $this->_model;
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
                'controller' => 'user'
            ),
            null,
            true
        );
    }

}
