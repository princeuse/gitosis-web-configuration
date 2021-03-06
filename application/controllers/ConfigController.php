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
class ConfigController extends Zend_Controller_Action
{

    /**
     * showing general configuration
     */
    public function indexAction ()
    {
        $form = new Application_Form_Config();

        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            $model = new Application_Model_Config();
            $model->setFormData(
                $form->getValidValues($this->getRequest()->getPost())
            );
        }

        $this->view->form = $form;
    }
}