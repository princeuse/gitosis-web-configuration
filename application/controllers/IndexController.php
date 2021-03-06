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
class IndexController extends Zend_Controller_Action
{

    /**
     * initialise json action(s)
     */
    public function init()
    {
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        $contextSwitch->addActionContext('load-repositories', 'json')
                      ->initContext();
    }

    /**
     * showing messages
     */
    public function indexAction()
    {
        $this->view->messages = $this->_helper->FlashMessenger->getMessages();
        $this->_helper->FlashMessenger->clearMessages();
    }

    public function loadRepositoriesAction()
    {
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        if ($contextSwitch->getCurrentContext() !== 'json') {
            $this->getHelper('Redirector')->gotoSimple('index');
        }

        $layout = Zend_Layout::getMvcInstance();
        $layout->disableLayout();

        $config = new Application_Model_Config();
        $host   = $config->getConfigElement(Application_Model_Config::CONFIG_DATA_GITOSIS_ADMIN_URL)->getValue();
        $user   = $config->getConfigElement(Application_Model_Config::CONFIG_DATA_GITOSIS_ADMIN_USER)->getValue();
        if (!empty($host) && !empty($user) ) {
            $gitosisUrl = $user . '@' . $host;
        } else {
            $gitosisUrl = '';
        }

        $email = $this->_getParam('email');
        $repos = array();
        if (!empty($email)) {
            $userModel = new Application_Model_Gitosis_User();
            try {
                $userModel->loadByMail($email);
            } catch (Exception $e) {
            }
            if ($userModel->getId()) {
                $groups = $userModel->getGroups();
                if (!empty($groups)) {
                    foreach ($groups as $group) {
                        $groupRepositories = $group->getRepositories();
                        if (!empty($groupRepositories)) {
                            foreach ($groupRepositories as $groupRepository) {
                                $repos[] = $repoData = array(
                                    'repo'  => $groupRepository->getName(),
                                    'write' => ($groupRepository->getGroupRight($group) == Application_Model_Gitosis_Repository::REPO_RIGHTS_WRITEABLE ? true : false),
                                    'url'   => $gitosisUrl . ':' . $groupRepository->getName(),
                                    'desc'  => $groupRepository->getDescription()
                                );
                            }
                        }
                    }
                }
            }
        }
        $this->view->repositories = $repos;
    }
}
