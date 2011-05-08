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
class GroupController extends MBit_Controller_Action
{
    /**
     * initialising controller
     *
     * setting the model and initialise json-action
     */
    public function init()
    {
        $this->_model = new Application_Model_Db_Gitosis_Groups();

        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        $contextSwitch->addActionContext('ajax', 'json')
                      ->initContext();
    }

    /**
     * list of all users
     */
    public function addAction ()
    {
        $id = $this->_getParam('id');
        if (empty($id)) {
            $this->_redirectToList();
        }

        $group = $this->_model->getById($id);

        $model  = new Application_Model_Db_Gitosis_Users();
        $select = $model->select(Zend_Db_Table::SELECT_WITH_FROM_PART);

        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pager.phtml');
        $paginator = Zend_Paginator::factory($select);
        $paginator->setDefaultItemCountPerPage(20);
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));

        $this->view->pager   = $paginator;
        $this->view->group   = $group['gitosis_group_name'];
        $this->view->groupId = $group['gitosis_group_id'];
    }

    /**
     * adding/removing users from/to group
     */
    public function ajaxAction ()
    {
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        if ($contextSwitch->getCurrentContext() !== 'json')
        {
            $this->getHelper('Redirector')->gotoSimple('add');
        }

        $action  = $this->_getParam('operation');
        $userId  = $this->_getParam('userId');
        $groupId = $this->_getParam('groupId');

        $message = '';
        $model = new Application_Model_Db_Gitosis_UsersGroups();
        if ($action == 'add') {
            $flag = $model->addUserToGroup($groupId, $userId);
            if ($flag) {
                $message = 'Der Benutzer wurde erfolgreich der Gruppe hinzugefügt';
            } else {
                $message = 'Der Benutzer konnte nicht zur Gruppe hinzugefügt werden';
            }
        } elseif ($action == 'remove') {
            $flag = $model->removeUserFromGroup($groupId, $userId);
            if ($flag) {
                $message = 'Der Benutzer wurde erfolgreich aus der Gruppe entfernt';
            } else {
                $message = 'Der Benutzer konnte nicht aus der Gruppe entfernt werden';
            }
        } else {
            $message = 'Es wurde eine nicht bekannte Aktion "' . $action . '" verwendet';
        }

        $this->view->message = $message;
    }

    /**
     * getting form for editing or creating a group
     *
     * @return Zend_Form
     */
    protected function _getForm()
    {
        $form = new Zend_Form();
        $form->setAttrib('accept-charset', 'UTF-8')
             ->setDecorators(array('FormElements', 'Form'));

        $paramId = intval($this->_getParam('id'));
        if ($paramId > 0) {
            $id = new Zend_Form_Element_Hidden('id');
            $id->setDecorators(self::$clearDecorator)
               ->setValue($paramId);

            $form->addElement($id);
        }

        $name = new Zend_Form_Element_Text('gitosis_group_name');
        $name->setDecorators(self::$paragraphDecorator)
             ->setFilters(array('StripTags', 'StringTrim'))
             ->setLabel('Name:')
             ->setAllowEmpty(false)
             ->setRequired(true)
             ->addValidator(
                 'NotEmpty',
                 true,
                 array(
                    'messages' => array (
                        Zend_Validate_NotEmpty::IS_EMPTY => 'Es muss ein Name angegeben werden'
                    )
                )
             )
             ->addValidator(
                 'StringLength',
                 true,
                 array(
                    5,
                    200,
                    'messages' => array (
                        Zend_Validate_StringLength::TOO_LONG  => 'Der Name darf maximal 200 Zeichen lang sein',
                        Zend_Validate_StringLength::TOO_SHORT => 'Der Name muss mindestens 5 Zeichen lang sein',
                    )
                )
             )
             ->addValidator(
                 'Regex',
                 true,
                 array(
                    '/^[a-z-]+$/i',
                    'messages' => array (
                        Zend_Validate_Regex::NOT_MATCH => 'Der Name darf nur alphabetische Zeichen (a-z) und Bindestriche enthalten'
                    )
                )
             );
        $form->addElement($name);

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setDecorators(self::$clearDecorator)
               ->setLabel('Speichern');
        $form->addElement($submit);

        $reset = new Zend_Form_Element_Reset('reset');
        $reset->setDecorators(self::$clearDecorator)
              ->setLabel('zurück setzen');
        $form->addElement($reset);

        return $form;
    }

    /**
     * getting the url to list action
     *
     * @return string
     */
    protected function _getListUrl()
    {
        return $this->view->url(
            array (
                'action'     => 'list',
                'controller' => 'group'
            ),
            null,
            true
        );
    }
}
