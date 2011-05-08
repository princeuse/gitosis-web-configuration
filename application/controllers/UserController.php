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
class UserController extends MBit_Controller_Action
{
    /**
     * initialising controller
     *
     * setting the model and initialise json-action
     */
    public function init()
    {
        $this->_model = new Application_Model_Db_Gitosis_Users();

        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        $contextSwitch->addActionContext('ajax', 'json')
                      ->initContext();
    }

    /**
     * listing all groups
     */
    public function groupAction ()
    {
        $id = $this->_getParam('id');
        if (empty($id)) {
            $this->_redirectToList();
        }

        $user = new Application_Model_Gitosis_User();
        $user->load($id);

        $model  = new Application_Model_Db_Gitosis_Groups();
        $select = $model->select(Zend_Db_Table::SELECT_WITH_FROM_PART);

        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pager.phtml');
        $paginator = Zend_Paginator::factory($select);
        $paginator->setDefaultItemCountPerPage(20);
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));

        $this->view->pager   = $paginator;
        $this->view->user    = $user->getName();
        $this->view->userId  = $user->getId();
    }

    /**
     * adding/removing groups
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
        $user = new Application_Model_Gitosis_User();
        $user->load($userId);

        if (!$user->getId()) {
            $message = 'Der Benutzer existiert nicht';
        } elseif (!empty($action) &&
                  ($action == 'add') || $action == 'remove') {

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

        $name = new Zend_Form_Element_Text('gitosis_user_name');
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
                        Zend_Validate_NotEmpty::IS_EMPTY => 'Es muss ein Benutzername angegeben werden'
                    )
                )
             )
             ->addValidator(
                 'Alnum',
                 true,
                 array(
                    true,
                    'messages' => array (
                        Zend_Validate_Alnum::NOT_ALNUM => 'Der Name darf nur alphabetische Zeichen und Ziffern enthalten'
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
             );
        $form->addElement($name);

        $email = new Zend_Form_Element_Text('gitosis_user_email');
        $email->setDecorators(self::$paragraphDecorator)
              ->setFilters(array('StripTags', 'StringTrim'))
              ->setLabel('E-Mail:')
              ->setAllowEmpty(false)
              ->setRequired(true)
              ->addValidator(
                  'NotEmpty',
                  true,
                  array(
                     'messages' => array (
                         Zend_Validate_NotEmpty::IS_EMPTY => 'Es muss eine E-Mailadresse angegeben werden'
                     )
                 )
              )
              ->addValidator(
                  'StringLength',
                  true,
                  array(
                     10,
                     200,
                     'messages' => array (
                         Zend_Validate_StringLength::TOO_LONG  => 'Die E-Mailadresse darf maximal 200 Zeichen lang sein',
                         Zend_Validate_StringLength::TOO_SHORT => 'Die E-Mailadresse muss mindestens 10 Zeichen lang sein',
                     )
                 )
              )
              ->addValidator(
                  'EmailAddress',
                  true,
                  array(
                     'messages' => array (
                         Zend_Validate_EmailAddress::INVALID_FORMAT =>
                            'Die E-Mailadresse besitzt ein ungültiges Format',
                         Zend_Validate_EmailAddress::INVALID_HOSTNAME =>
                            'Die E-Mailadresse enthält einen ungültigen Domainanteil',
                         Zend_Validate_EmailAddress::INVALID_LOCAL_PART =>
                            'Die E-Mailadresse enthält einen ungültigen, lokalen Anteil',
                     )
                 )
              );
        $form->addElement($email);

        $sshKey = new Zend_Form_Element_Textarea('gitosis_user_ssh_key');
        $sshKey->setDecorators(self::$paragraphDecorator)
               ->setFilters(array('StripTags', 'StringTrim'))
               ->setLabel('SSH-Schlüssel:')
               ->setAttrib('cols', 100)
               ->setAttrib('rows', 10)
               ->setAllowEmpty(false)
               ->setRequired(true)
               ->addValidator(
                  'NotEmpty',
                  true,
                  array(
                     'messages' => array (
                         Zend_Validate_NotEmpty::IS_EMPTY => 'Es muss eine SSH-Schlüssel angegeben werden'
                     )
                 )
              );
        $form->addElement($sshKey);

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
     * getting url to list action
     *
     * @return string
     */
    protected function _getListUrl()
    {
        return $this->view->url(
            array (
                'action'     => 'list',
                'controller' => 'user'
            ),
            null,
            true
        );
    }
}
