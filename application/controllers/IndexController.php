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
        $contextSwitch->addActionContext('loadRepositories', 'json')
                      ->initContext();
    }

    /**
     * showing messages
     */
    public function indexAction()
    {
        if (!Zend_Registry::isRegistered('admin_user')) {
            $layout = Zend_Layout::getMvcInstance();
            $layout->setLayout('plain');
        }
        $this->view->messages = $this->_helper->FlashMessenger->getMessages();
        $this->_helper->FlashMessenger->clearMessages();
    }

    public function loadRepositoriesAction()
    {
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        if ($contextSwitch->getCurrentContext() !== 'json') {
            $this->getHelper('Redirector')->gotoSimple('index');
        }
    }
}
