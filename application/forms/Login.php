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
 * @package   Forms
 */

/**
 * @category MB-it
 * @package  Forms
 */
class Application_Form_Login extends MBit_Form
{
    /**
     * creating the form
     */
    public function init()
    {
        $name = new Zend_Form_Element_Text('login');
        $name->setDecorators($this->_elementDecoratorParagraphNoExt)
             ->setFilters($this->_standardFilters)
             ->setLabel('Login')
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
                        Zend_Validate_Alnum::NOT_ALNUM => 'Der Benutzername darf nur alphanumerische Zeichen enthalten'
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
                        Zend_Validate_StringLength::TOO_LONG  => 'Der Benutzername darf maximal 200 Zeichen lang sein',
                        Zend_Validate_StringLength::TOO_SHORT => 'Der Benutzername muss mindestens 5 Zeichen lang sein',
                    )
                )
             );
        $this->addElement($name);

        $password = new Zend_Form_Element_Password('password');
        $password->setDecorators($this->_elementDecoratorParagraphNoExt)
                 ->setFilters($this->_standardFilters)
                 ->setLabel('Passwort')
                 ->setAllowEmpty(false)
                 ->setRequired(true)
                 ->addValidator(
                     'NotEmpty',
                     true,
                     array(
                        'messages' => array (
                            Zend_Validate_NotEmpty::IS_EMPTY => 'Es muss eine Passwort angegeben werden'
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
                            Zend_Validate_StringLength::TOO_LONG  => 'Das Passwort darf maximal 200 Zeichen lang sein',
                            Zend_Validate_StringLength::TOO_SHORT => 'Das Passwort muss mindestens 5 Zeichen lang sein',
                        )
                    )
                 );
        $this->addElement($password);

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setDecorators($this->_elementDecoratorClear)
               ->setLabel('Login');
        $this->addElement($submit);
    }
}