<?php

/**
 * APPLICATION
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
 * @package   MODULE
 */

/**
 * @category MB-it
 * @package  MODULE
 */
class Application_Model_Mail extends Zend_Mail
{
    /**
     * initialize mail class
     *
     * @param string $charset
     */
    public function __construct($charset = 'utf-8')
    {
        parent::__construct($charset);
        $this->_initFromConfig();
    }

    /**
     * get and use config data
     */
    protected function _initFromConfig()
    {
        $frontController = Zend_Controller_Front::getInstance();
        $options = $frontController->getParam('bootstrap')
                                   ->getApplication()
                                   ->getOptions();

        if (!array_key_exists('mail', $options)) {
            return;
        }
        $mailOptions = $options['mail'];
        unset($options);

        $senderMail = $mailOptions['sender']['from'];
        $senderName = $mailOptions['sender']['name'];
        if (!empty($senderMail)) {
            $this->setFrom($senderMail, $senderName);
        }

        $sendViaSmtp = ($mailOptions['sendViaSmtp'] == 1 ? true : false);
        if ($sendViaSmtp) {
            if (!array_key_exists('host', $mailOptions['smtp'])) {
                throw new MBit_Exception('for sending mails via smtp, a host has to be configured');
            }

            $host = trim((string) $mailOptions['smtp']['host']);
            unset($mailOptions['smtp']['host']);

            $smtpOptions = $mailOptions['smtp'];
            $transport = new Zend_Mail_Transport_Smtp($host, $smtpOptions);
            $this->setDefaultTransport($transport);
        }
    }
}