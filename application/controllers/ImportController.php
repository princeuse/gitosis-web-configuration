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
class ImportController extends Zend_Controller_Action
{
    /**
     * namespace for storing import data
     */
    const SESSION_NAMESPACE = 'gitosis_config_import';

    /**
     * importing existing gitosis configuration
     */
    public function indexAction ()
    {
        $form = $this->_getImportForm();

        if ($this->getRequest()->isPost() &&
            $form->isValid($this->getRequest()->getPost())) {

                $returnFlag = array();

                $uploadedData = $form->getValues();
                $fullFilePath = $form->getElement('gitosis_conf')->getFileName();
                $fullFilePath = realpath($fullFilePath);

                if (file_exists($fullFilePath)) {
                    $importModel = new Application_Model_Import();
                    $importModel->setFile($fullFilePath);
                    $returnFlag = $importModel->import();

                    $importedUserIds = $importModel->getImportedUsers();
                    if (!empty($importedUserIds)) {

                        if (!is_array($importedUserIds)) {
                            $importedUserIds = array($importedUserIds);
                        }

                        $session = new Zend_Session_Namespace(self::SESSION_NAMESPACE);
                        $session->userIds = $importedUserIds;
                    }
                }

                if (!empty($returnFlag)) {
                    foreach ($returnFlag as $error) {
                        $this->_helper->FlashMessenger->addMessage($error);
                    }
                } else {
                    $this->_helper->FlashMessenger->addMessage('Der Import der Konfiguration war erfolgreich.');
                }

                unlink($fullFilePath);

                $this->_redirect(
                    $this->view->url(
                        array (
                            'controller' => 'import',
                            'action'     => 'edit'
                        )
                    ),
                    array (
                        'prependBase' => false
                    )
                );
        }

        $this->view->form = $form;
    }

    /**
     * adding ssh-key and name to imported users
     */
    public function editAction ()
    {
        $session = new Zend_Session_Namespace(self::SESSION_NAMESPACE);
        if (!isset($session->userIds)) {
            $this->_redirect(
                $this->view->url(
                    array (
                        'controller' => 'index',
                        'action'     => 'index'
                    )
                ),
                array (
                    'prependBase' => false
                )
            );
        }

        $userIds = $session->userIds;
        $form = $this->_getSshKeyForm(array_keys($userIds));

        if ($this->getRequest()->isPost()
            && $form->isValid($this->getRequest()->getPost())) {

                $userModel = new Application_Model_Db_Gitosis_Users();
                $success = true;

                $postData = $form->getValues();
                $userData = array();

                foreach ($postData as $fieldName => $fieldValue) {

                    if (preg_match('/^gitosis_user_name_/', $fieldName)) {
                        $userId = intval(str_replace('gitosis_user_name_', '', $fieldName));

                        if (!empty($userId) && $userId > 0) {

                            $userData['gitosis_user_name'] = trim($fieldValue);

                            $filePath = $form->getElement('gitosis_user_ssh_key_' . $userId)->getFileName();
                            $sshKey   = trim(file_get_contents($filePath));
                            unlink($filePath);
                            $userData['gitosis_user_ssh_key'] = $sshKey;
                            unset($sshKey, $filePath);

                            $rowsUpdated = $userModel->update($userData, 'gitosis_user_id = ' . $userId);
                            if ($rowsUpdated <= 0) {
                                $success = false;
                            }

                            $userData = array();
                        }
                    }
                }

                if ($success) {
                    $this->_helper->FlashMessenger->addMessage('Der Import der Konfiguration war erfolgreich.');
                } else {
                    $this->_helper->FlashMessenger->addMessage('Es traten Fehler beim Import der Konfiguration auf.');
                }

                $this->_redirect(
                    $this->view->url(
                        array (
                            'controller' => 'index',
                            'action'     => 'index'
                        )
                    ),
                    array (
                        'prependBase' => false
                    )
                );
        }

        $this->view->form = $form;
    }

    /**
     * getting import form
     *
     * @return Zend_Form
     */
    protected function _getImportForm ()
    {
        $form = new Zend_Form();
        $form->setAttrib('accept-charset', 'UTF-8')
             ->setDecorators(array('FormElements', 'Form'));

        $fileUploadDestination = implode(
            DIRECTORY_SEPARATOR,
            array(
                APPLICATION_PATH,
                '..',
                'var',
                'tmp'
            )
        );
        if (!is_dir($fileUploadDestination)) {
            mkdir($fileUploadDestination, 0775, true);
        }

        $fileUpload = new Zend_Form_Element_File('gitosis_conf');
        $fileUpload->setDecorators(array(
                        'File',
                        'Label',
                        'Errors',
                        array('Description', array('tag' => 'span')),
                        array('HtmlTag', array('tag' => 'p'))
                    ))
                    ->setLabel('Gitosis Konfiguration:')
                    ->setFilters(array('StripTags', 'StringTrim'))
                    ->setAllowEmpty(false)
                    ->setRequired(true)
                    ->setDestination($fileUploadDestination)
                    ->addValidator('Count', true, 1)
                    ->addValidator('Extension', true, 'conf');

        $validatorUpload = $fileUpload->getValidator('File_Upload');
        $validatorUpload->setMessages(
            array (
                Zend_Validate_File_Upload::ATTACK           => 'Es trat ein Systemfehler auf, die Datei konnte %value% nicht hochgeladen werden.',
                Zend_Validate_File_Upload::CANT_WRITE       => 'Es trat ein Systemfehler auf, die Datei konnte %value% nicht hochgeladen werden.',
                Zend_Validate_File_Upload::EXTENSION        => 'Es trat ein Systemfehler auf, die Datei konnte %value% nicht hochgeladen werden.',
                Zend_Validate_File_Upload::FILE_NOT_FOUND   => 'Es wurde keine Datei zum Hochladen ausgewählt.',
                Zend_Validate_File_Upload::FORM_SIZE        => 'Die Datei %value% überschreitet die zulässige Dateigröße.',
                Zend_Validate_File_Upload::INI_SIZE         => 'Die Datei %value% überschreitet die zulässige Dateigröße.',
                Zend_Validate_File_Upload::NO_FILE          => 'Es wurde keine Datei hochgeladen.',
                Zend_Validate_File_Upload::NO_TMP_DIR       => 'Es trat ein Systemfehler auf, die Datei konnte %value% nicht hochgeladen werden.',
                Zend_Validate_File_Upload::PARTIAL          => 'Es trat ein Übertragungsfehler auf. Die Datei %value% wurde nur teilweise hochgeladen.',
                Zend_Validate_File_Upload::UNKNOWN          => 'Es trat ein Systemfehler auf, die Datei konnte %value% nicht hochgeladen werden.',
            )
        );

        $validatorCount = $fileUpload->getValidator('File_Count');
        $validatorCount->setMessages(
            array(
                Zend_Validate_File_Count::TOO_MANY =>
                    "Es ist nur das Hochladen einer Datei erlaubt"
            )
        );
        $validatorExtension = $fileUpload->getValidator('File_Extension');
        $validatorExtension->setMessages(
            array(
                Zend_Validate_File_Extension::FALSE_EXTENSION =>
                    'Die Datei muss auf .conf enden.'
            )
        );
        $form->addElement($fileUpload);

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setDecorators(array( 'ViewHelper', 'Label', 'Errors'))
               ->setLabel('Speichern');
        $form->addElement($submit);

        return $form;
    }

    /**
     * form for adding ssh-keys and user names
     *
     * @param array $userIds
     * @return Zend_Form
     */
    protected function _getSshKeyForm ($userIds)
    {
        $form = new Zend_Form();
        $form->setAttrib('accept-charset', 'UTF-8')
             ->setDecorators(array('FormElements', 'Form'));

        $fileUploadDestination = implode(
            DIRECTORY_SEPARATOR,
            array(
                APPLICATION_PATH,
                '..',
                'var',
                'tmp'
            )
        );

        $counter = 0;
        $model = new Application_Model_Db_Gitosis_Users();
        foreach ($userIds as $userId) {

            $user = $model->getById($userId);
            if (empty($user)) {
                continue;
            }

            $fileUpload[$counter] = new Zend_Form_Element_File('gitosis_user_ssh_key_' . $userId);
            $fileUpload[$counter]->setDecorators(array(
                                    'File',
                                    'Label',
                                    'Errors',
                                    array('Description', array('tag' => 'span')),
                                    array('HtmlTag', array('tag' => 'p'))
                                ))
                                ->setLabel('SSH-Schlüssel:')
                                ->setFilters(array('StripTags', 'StringTrim'))
                                ->setAllowEmpty(false)
                                ->setRequired(true)
                                ->setDestination($fileUploadDestination)
                                ->addValidator('Count', true, 1)
                                ->addValidator('Extension', true, 'pub');

            $validatorUpload = $fileUpload[$counter]->getValidator('File_Upload');
            $validatorUpload->setMessages(
                array (
                    Zend_Validate_File_Upload::ATTACK           => 'Es trat ein Systemfehler auf, die Datei konnte %value% nicht hochgeladen werden.',
                    Zend_Validate_File_Upload::CANT_WRITE       => 'Es trat ein Systemfehler auf, die Datei konnte %value% nicht hochgeladen werden.',
                    Zend_Validate_File_Upload::EXTENSION        => 'Es trat ein Systemfehler auf, die Datei konnte %value% nicht hochgeladen werden.',
                    Zend_Validate_File_Upload::FILE_NOT_FOUND   => 'Es wurde keine Datei zum Hochladen ausgewählt.',
                    Zend_Validate_File_Upload::FORM_SIZE        => 'Die Datei %value% überschreitet die zulässige Dateigröße.',
                    Zend_Validate_File_Upload::INI_SIZE         => 'Die Datei %value% überschreitet die zulässige Dateigröße.',
                    Zend_Validate_File_Upload::NO_FILE          => 'Es wurde keine Datei hochgeladen.',
                    Zend_Validate_File_Upload::NO_TMP_DIR       => 'Es trat ein Systemfehler auf, die Datei konnte %value% nicht hochgeladen werden.',
                    Zend_Validate_File_Upload::PARTIAL          => 'Es trat ein Übertragungsfehler auf. Die Datei %value% wurde nur teilweise hochgeladen.',
                    Zend_Validate_File_Upload::UNKNOWN          => 'Es trat ein Systemfehler auf, die Datei konnte %value% nicht hochgeladen werden.',
                )
            );

            $validatorCount = $fileUpload[$counter]->getValidator('File_Count');
            $validatorCount->setMessages(
                array(
                    Zend_Validate_File_Count::TOO_MANY =>
                        "Es ist nur das Hochladen einer Datei erlaubt"
                )
            );
            $validatorExtension = $fileUpload[$counter]->getValidator('File_Extension');
            $validatorExtension->setMessages(
                array(
                    Zend_Validate_File_Extension::FALSE_EXTENSION =>
                        'Die Datei muss auf .pub enden.'
                )
            );

            $nameField[$counter] = new Zend_Form_Element_Text('gitosis_user_name_' . $userId);
            $nameField[$counter]->setDecorators(
                                    array (
                                        'ViewHelper',
                                        'Label',
                                        'Errors',
                                        array('Description', array('tag' => 'span')),
                                        array('HtmlTag', array('tag' => 'p'))
                                    )
                                )
                                ->setFilters(array('StripTags', 'StringTrim'))
                                ->setLabel('Name:')
                                ->setAllowEmpty(false)
                                ->setRequired(true)
                                ->addValidator(
                                    'NotEmpty',
                                    true,
                                    array(
                                       'messages' => array (
                                           Zend_Validate_NotEmpty::IS_EMPTY => 'Es muss der Name angegeben werden'
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

            $dgName = 'dg_' . $counter;
            $form->addDisplayGroup(
                array(
                    $fileUpload[$counter],
                    $nameField[$counter]
                ),
                $dgName
            );

            $dg[$counter] = $form->getDisplayGroup($dgName);
            $dg[$counter]->setLegend($user['gitosis_user_email'])
                         ->setDecorators(array('FormElements','Fieldset'));

            $counter++;
        }

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setDecorators(array( 'ViewHelper', 'Label', 'Errors'))
               ->setLabel('Speichern');
        $form->addElement($submit);

        return $form;
    }
}