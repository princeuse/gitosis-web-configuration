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
class RepositoryController extends MBit_Controller_Action
{
    /**
     * initialising controller
     */
    public function init()
    {
        $this->_model = new Application_Model_Db_Gitosis_Repositories();
    }

    /**
     * form for editing/creating a repository
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

        $name = new Zend_Form_Element_Text('gitosis_repository_name');
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

        $owner = new Zend_Form_Element_Select('gitosis_repository_owner_id');
        $owner->setDecorators(self::$paragraphDecorator)
              ->setFilters(array('StripTags', 'StringTrim'))
              ->setLabel('Besitzer:')
              ->setRequired(true)
              ->addValidator(
                 'NotEmpty',
                 true,
                 array(
                    'messages' => array (
                        Zend_Validate_NotEmpty::IS_EMPTY => 'Es muss ein Besitzer angegeben werden'
                    )
                )
             );

        $userModel = new Application_Model_Db_Gitosis_Users();
        $rows = $userModel->fetchAll();
        if ($rows->count() <= 0) {
            $this->_helper->FlashMessenger->addMessage('Es müssen zuerst Benutzer im System erfasst werden, bevor Repositories erstellt werden können.');
            $createUserUrl = $this->view->url(
                array (
                    'action'     => 'create',
                    'controller' => 'user'
                ),
                null,
                true
            );
            $this->_redirect($createUserUrl, array('prependBase' => false));
        } else {
            $owner->addMultiOption('', 'Bitte wählen...');
            foreach ($rows as $row) {
                $owner->addMultiOption($row->{'gitosis_user_id'}, $row->{'gitosis_user_name'});
            }
        }
        $form->addElement($owner);

        $description = new Zend_Form_Element_Textarea('gitosis_repository_description');
        $description->setDecorators(self::$paragraphDecorator)
               ->setFilters(array('StripTags', 'StringTrim'))
               ->setLabel('Beschreibung:')
               ->setAttrib('cols', 100)
               ->setAttrib('rows', 10)
               ->setAllowEmpty(true);
        $form->addElement($description);

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
                'controller' => 'repository'
            ),
            null,
            true
        );
    }
}
