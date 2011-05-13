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
 * CRUD controller
 *
 * @category MB-it
 * @package  Lib
 */
abstract class MBit_Controller_Crud extends Zend_Controller_Action
{
    /**
     * @var MBit_Model_CrudInterface
     */
    protected $_model = null;

    /**
     * @var MBit_Form
     */
    protected $_form = null;

    /**
     * redirecting to list action
     */
    public function indexAction()
    {
        $this->_redirectToList();
    }

    /**
     * listing data
     */
    public function listAction ()
    {
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pager.phtml');

        $this->_initModel();
        $select = $this->_model->getPaginatorSelect();

        $paginator = Zend_Paginator::factory($select);
        $paginator->setDefaultItemCountPerPage(20);
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));

        $this->view->messages = $this->_helper->FlashMessenger->getMessages();
        $this->view->pager    = $paginator;

        $this->_helper->FlashMessenger->clearMessages();
    }

    /**
     * editing data
     */
    public function editAction ()
    {
        $id = $this->_getParam('id');
        if (empty($id)) {
            $this->_redirectToList();
        }

        $this->_initForm();
        $this->_initModel();

        $preData = $this->_model->getById($id);
        $this->_form->populate($preData);

        if ($this->getRequest()->isPost()
            && $this->_form->isValid($this->getRequest()->getPost())
        ) {
            $data = $this->_form->getValidValues($this->getRequest()->getPost());

            $id = null;
            if (array_key_exists('id', $data)) {
                $id = $data['id'];
                unset($data['id']);
            }

            $successFlag = $this->_model->editItem($id, $data);
            $message = "Der Datensatz konnte nicht gespeichert werden";
            if ($successFlag) {
                $message = "Der Datensatz wurde erfolgreich gespeichert";
            }

            $this->_helper->FlashMessenger->addMessage($message);
            $this->_redirectToList();
        }

        $this->view->form = $this->_form;
    }

    /**
     * creating data
     */
    public function createAction ()
    {
        $form = $this->_form;

        if ($this->getRequest()->isPost()
            && $form->isValid($this->getRequest()->getPost())
        ) {
            $data = $form->getValidValues($this->getRequest()->getPost());
            if (array_key_exists('id', $data)) {
                unset($data['id']);
            }

            $successFlag = $this->_model->createItem($data);
            $message = "Der Datensatz konnte nicht erstellt werden";
            if ($successFlag) {
                $message = "Der Datensatz wurde erfolgreich erstellt";
            }

            $this->_helper->FlashMessenger->addMessage($message);
            $this->_redirectToList();
        }

        $this->view->form     = $form;
        $this->view->messages = $this->_helper->FlashMessenger->getMessages();

        $this->_helper->FlashMessenger->clearMessages();
    }

    /**
     * deleting data
     */
    public function deleteAction ()
    {
        $message = "Der Datensatz konnte nicht gelöscht werden";
        $id = $this->_getParam('id');
        if (!empty($id)) {
            $this->_model->deleteItem($id);
            $message = "Der Datensatz wurde erfolgreich gelöscht";
        }

        $this->_helper->FlashMessenger->addMessage($message);
        $this->_redirectToList();
    }

    /**
     * redirect to list action
     */
    protected function _redirectToList ()
    {
        $this->_redirect($this->_getListUrl(), array('prependBase' => false));
    }

    /**
     * initialising model
     */
    protected function _initModel()
    {
        $this->_getModel();
        if (!$this->_model instanceof MBit_Model_CrudInterface) {
            throw new MBit_Exception('Models that are used by the MBit_Controller_Crud have to implement MBit_Model_CrudInterface');
        }
    }

    /**
     * initialising form
     */
    protected function _initForm()
    {
        $this->_getForm();
        if (!$this->_form instanceof Zend_Form) {
            throw new MBit_Exception('Forms that are used by the MBit_Controller_Crud have to extends Zend_Form');
        }
    }

    /**
     * @return Zend_Form
     */
    abstract protected function _getForm();

    /**
     * @return MBit_Model_CrudInterface
     */
    abstract protected function _getModel();

    /**
     * Auslesen der URL zur Liste der Datensätze
     *
     * @return string
     */
    abstract protected function _getListUrl();
}
