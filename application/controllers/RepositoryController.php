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
class RepositoryController extends MBit_Controller_Crud
{

    /**
     * initialise json action(s)
     */
    public function init()
    {
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        $contextSwitch->addActionContext('set-permissions', 'json')
                      ->initContext();
    }

    /**
     * listing all groups and checkboxes to grant read- or write access
     */
    public function permissionAction()
    {
        $id = $this->_getParam('id');
        if (empty($id)) {
            $this->_redirectToList();
        }

        $repo = new Application_Model_Gitosis_Repository();
        $repo->load($id);

        $group = new Application_Model_Gitosis_Group();

        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pager.phtml');
        $paginator = Zend_Paginator::factory(
                        $group->getPaginatorSelect(), 'Object', array('MBit_Paginator_Adapter_' => 'MBit/Paginator/Adapter')
        );
        $paginator->getAdapter()->setObjectName('Application_Model_Gitosis_Group');
        $paginator->setDefaultItemCountPerPage(20);
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));

        $this->view->pager  = $paginator;
        $this->view->repo   = $repo;
    }

    public function setPermissionsAction()
    {
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        if ($contextSwitch->getCurrentContext() !== 'json') {
            $this->getHelper('Redirector')->gotoSimple('permission');
        }

        $flag         = false;
        $repositoryId = $this->_getParam('repoId');
        $groupId      = $this->_getParam('groupId');
        $action       = $this->_getParam('operation');

        if (intval($repositoryId) > 0 && intval($groupId) > 0 && !empty($action)) {
            $repository = new Application_Model_Gitosis_Repository();
            $repository->load($repositoryId);

            if ($repository->getId()) {
                if ($action == 'add') {
                    $permission = ($this->_getParam('permission') == 'write' ? true : false);
                    $repository->addGroup($groupId, $permission);
                } else {
                    $repository->removeGroup($groupId);
                }

                $flag = $repository->save();
            }
        }

        $message = 'Der Datensatzes konnte nicht gespeichert werden';
        if ($flag) {
            $message = 'Die Speicherung des Datensatzes war erfolgreich';
        }

        $this->view->message = $message;
    }

    /**
     * getting form for editing or creating a repository
     *
     * @return Application_Form_Repository
     */
    protected function _getForm()
    {
        if (empty($this->_form)) {
            $this->_form = new Application_Form_Repository();
        }
        return $this->_form;
    }

    /**
     * getting model for data of repository
     *
     * @return Application_Model_Gitosis_Repository
     */
    protected function _getModel()
    {
        if (empty($this->_model)) {
            $this->_model = new Application_Model_Gitosis_Repository();
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
                'action'     => 'list',
                'controller' => 'repository'
            ),
            null,
            true
        );
    }
}
