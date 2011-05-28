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
class GroupController extends MBit_Controller_Crud
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
     * list of all users
     *
     * if no group id is given this action redirects back to group list.
     */
    public function userAction()
    {
        $id = $this->_getParam('id');
        if (empty($id)) {
            $this->_redirectToList();
        }

        $group = new Application_Model_Gitosis_Group();
        $group->load($id);

        $user = new Application_Model_Gitosis_User();

        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pager.phtml');
        $paginator = Zend_Paginator::factory(
                        $user->getPaginatorSelect(),
                        'Object',
                        array('MBit_Paginator_Adapter_' => 'MBit/Paginator/Adapter')
        );
        $paginator->getAdapter()->setObjectName('Application_Model_Gitosis_User');
        $paginator->setDefaultItemCountPerPage(20);
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));

        $this->view->pager = $paginator;
        $this->view->group = $group->getName();
        $this->view->groupId = $group->getId();
    }

    /**
     * adding/removing users from/to group
     *
     * calling this action is only allowed via ajax requests. All other requests
     * are redirected to user list.
     */
    public function ajaxAction()
    {
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        if ($contextSwitch->getCurrentContext() !== 'json') {
            $this->getHelper('Redirector')->gotoSimple('user');
        }

        $action  = $this->_getParam('operation');
        $userId  = $this->_getParam('userId');
        $groupId = $this->_getParam('groupId');

        $message = '';
        $group = new Application_Model_Gitosis_Group();
        $group->load($groupId);
        if (!empty($action) && ($action == 'remove' || $action == 'add')) {
            if ($action == 'add') {
                $group->addUser($userId);
            } elseif ($action == 'remove') {
                $group->removeUser($userId);
            }
            $flag = $group->save();
            if ($flag) {
                $message = 'Die Änderung wurde erfolgreich gespeichert.';
            } else {
                $message = 'Die Änderung konnte nicht gespeichert werden.';
            }
        } else {
            $message = 'Es wurde eine nicht bekannte Aktion "' . $action . '" verwendet';
        }

        $this->view->message = $message;
    }

    /**
     * getting form for editing or creating a group
     *
     * @return Application_Form_Group
     */
    protected function _getForm()
    {
        if (empty($this->_form)) {
            $this->_form = new Application_Form_Group();
        }
        return $this->_form;
    }

    /**
     * getting model for data of group
     *
     * @return Application_Model_Gitosis_Group
     */
    protected function _getModel()
    {
        if (empty($this->_model)) {
            $this->_model = new Application_Model_Gitosis_Group();
        }
        return $this->_model;
    }

    /**
     * getting the url to list action
     *
     * @return string
     */
    protected function _getListUrl()
    {
        return $this->view->url(
            array(
                'action' => 'list',
                'controller' => 'group'
            ),
            null,
            true
        );
    }

}
