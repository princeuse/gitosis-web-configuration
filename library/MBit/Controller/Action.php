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
abstract class MBit_Controller_Action extends Zend_Controller_Action
{
    /**
     * form decorators (paragraph)
     *
     * @var array
     */
    protected static $paragraphDecorator = array(
        'ViewHelper',
    	'Label',
    	'Errors',
    	array('Description', array('tag' => 'span')),
    	array('HtmlTag', array('tag' => 'p'))
    );

    /**
     * form decorators (no embrassing tag)
     *
     * @var array
     */
    protected static $clearDecorator = array(
        'ViewHelper',
    	'Label',
    	'Errors'
    );

    /**
     * @var MBit_Db_Table_Abstract
     */
    protected $_model = null;

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

        $select = $this->_model->select(Zend_Db_Table::SELECT_WITH_FROM_PART);

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
        $form = $this->_getForm();

        $preData = $this->_model->getById($this->_getParam('id'));
        $form->populate($preData);

        if ($this->getRequest()->isPost()
            && $form->isValid($this->getRequest()->getPost())
        ) {
            $data = $form->getValidValues($this->getRequest()->getPost());

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

        $this->view->form = $form;
    }

    /**
     * creating data
     */
    public function createAction ()
    {
        $form = $this->_getForm();

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
     * @return Zend_Form
     */
    abstract protected function _getForm();

    /**
     * Auslesen der URL zur Liste der Datensätze
     *
     * @return string
     */
    abstract protected function _getListUrl();
}
