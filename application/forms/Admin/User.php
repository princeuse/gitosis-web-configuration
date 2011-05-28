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
class Application_Form_Admin_User extends MBit_Form
{
    /**
     * creating the form
     */
    public function init()
    {
        $id = new Zend_Form_Element_Hidden('id');
        $id->setDecorators($this->_elementDecoratorNoTags);
        $this->addElement($id);

        $name = new Zend_Form_Element_Text('login');
        $name->setDecorators($this->_elementDecoratorParagraph)
             ->setFilters($this->_standardFilters)
             ->setLabel('Login')
             ->setAllowEmpty(false)
             ->setRequired(true)
             ->addValidator(
                 'NotEmpty',
                 true,
                 array(
                    'messages' => array (
                        Zend_Validate_NotEmpty::IS_EMPTY => 'Es muss ein Login-Name angegeben werden'
                    )
                )
             )
             ->addValidator(
                 'Alnum',
                 true,
                 array(
                    true,
                    'messages' => array (
                        Zend_Validate_Alnum::NOT_ALNUM => 'Der Login-Name darf nur alphanumerische Zeichen enthalten'
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
                        Zend_Validate_StringLength::TOO_LONG  => 'Der Login-Name darf maximal 200 Zeichen lang sein',
                        Zend_Validate_StringLength::TOO_SHORT => 'Der Login-Name muss mindestens 5 Zeichen lang sein',
                    )
                )
             );
        $this->addElement($name);

        $email = new Zend_Form_Element_Text('email');
        $email->setDecorators($this->_elementDecoratorParagraph)
              ->setFilters($this->_standardFilters)
              ->setLabel('E-Mail')
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
        $this->addElement($email);

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setDecorators($this->_elementDecoratorClear)
               ->setLabel('Speichern');
        $this->addElement($submit);

        $reset = new Zend_Form_Element_Reset('reset');
        $reset->setDecorators($this->_elementDecoratorClear)
              ->setLabel('zurück setzen');
        $this->addElement($reset);
    }
}