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
     * initialise json action(s)
     */
    public function init()
    {
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        $contextSwitch->addActionContext('user-add', 'json')
                      ->initContext();
    }

    /**
     * uploading gitosis configuration
     */
    public function indexAction ()
    {
        $form = new Application_Form_Import_Config();

        if ($this->getRequest()->isPost() &&
            $form->isValid($this->getRequest()->getPost())) {

                $message = 'Es trat ein Fehler beim Hochladen der Datei auf.';

                $uploadedData = $form->getValues();
                $fullFilePath = $form->getElement('gitosis_conf')->getFileName();
                $fullFilePath = realpath($fullFilePath);

                if (file_exists($fullFilePath)) {
                    $importModel = new Application_Model_Import();
                    $importModel->setFile($fullFilePath);
                    $returnFlag = $importModel->import();

                    if ($returnFlag === Application_Model_Import::IMPORT_OK) {
                        $importModel->saveToSession();
                        $message = 'Der Import der Konfiguration war erfolgreich.';
                        $redirectTarget = array (
                            'controller' => 'import',
                            'action'     => 'edit'
                        );
                    } else {
                        $message = $importModel->getErrorMessage($returnFlag);
                        $redirectTarget = array (
                            'controller' => 'index',
                            'action'     => 'index'
                        );
                    }
                }

                $this->_helper->FlashMessenger->addMessage($message);
                unlink($fullFilePath);

                $this->_redirect(
                    $this->view->url($redirectTarget),
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
        $importModel = new Application_Model_Import();
        $users = $importModel->getUsers();
        $this->view->users = array_keys($users);
    }

    /**
     * storing groups, repositories and permissions
     */
    public function saveAction()
    {
        $importModel = new Application_Model_Import();

        $groups = $importModel->getGroups();
        if (!empty($groups)) {
            foreach ($groups as $group => $members) {
                $groupModel = new Application_Model_Gitosis_Group();
                $groupModel->setName($group);
                $groupModel->addUsers($members);
                try {
                    $groupModel->save();
                } catch (Exception $e) {
                    continue;
                }
            }
        }
        unset($groupModel);

        $repos = $importModel->getRepositories();
        if (!empty($repos)) {
            foreach ($repos as $repo => $data) {
                $repoModel = new Application_Model_Gitosis_Repository();
                try {
                    $repoModel->setName($repo);

                    if (array_key_exists('description', $data) && !empty($data['description'])) {
                        $repoModel->setDescription($data['description']);
                    }

                    if (array_key_exists('owner', $data) && !empty($data['owner'])) {
                        $repoModel->setOwner($data['owner']);
                    }

                    $repoModel->save();
                    unset($repoModel);
                } catch (Exception $e) {
                    continue;
                }
            }
        }

        $permissions = $importModel->getPermissions();
        if (!empty($permissions)) {
            foreach($permissions as $data) {
                $groupModel = new Application_Model_Gitosis_Group();
                $groupModel->loadByName($data['group']);

                $isWriteable = $data['write'];
                foreach ($data['repos'] as $repo) {
                    $groupModel->addRepository($repo, $isWriteable);
                }
                $groupModel->save();
                unset($groupModel);
            }
        }
        $this->_redirect('/');
    }

    /**
     * storing users via ajax
     */
    public function userAddAction()
    {
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        if ($contextSwitch->getCurrentContext() !== 'json') {
            $this->getHelper('Redirector')->gotoSimple('index');
        }

        $username = $this->_getParam('username');
        $email    = $this->_getParam('email');
        $sshKey   = $this->_getParam('sshkey');

        $userModel = new Application_Model_Gitosis_User();
        $userModel->setMailAdress($email)
                  ->setName($username)
                  ->setSshKey($sshKey);

        if ($userModel->save()) {
            $this->view->message = 'success';
        } else {
            $this->view->message = 'error';
        }

    }
}