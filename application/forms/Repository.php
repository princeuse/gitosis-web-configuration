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
class Application_Form_Repository extends MBit_Form
{

    /**
     * creating the form
     */
    public function init()
    {
        $id = new Zend_Form_Element_Hidden('id');
        $id->setDecorators($this->_elementDecoratorClear);
        $this->addElement($id);

        $name = new Zend_Form_Element_Text('name');
        $name->setDecorators($this->_elementDecoratorParagraph)
             ->setFilters($this->_standardFilters)
             ->setLabel('Name')
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
        $this->addElement($name);

        $owner = new Zend_Form_Element_Select('owner');
        $owner->setDecorators($this->_elementDecoratorParagraph)
              ->setFilters($this->_standardFilters)
              ->setLabel('Besitzer')
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

        $owner->addMultiOption('', 'Bitte wählen...');

        $userModel = new Application_Model_Db_Gitosis_Users();
        $rows = $userModel->fetchAll();
        if ($rows->count() > 0) {
            foreach ($rows as $row) {
                $owner->addMultiOption($row->{'gitosis_user_id'}, $row->{'gitosis_user_name'});
            }
        }
        $this->addElement($owner);

        $description = new Zend_Form_Element_Textarea('description');
        $description->setDecorators($this->_elementDecoratorParagraph)
               ->setFilters($this->_standardFilters)
               ->setLabel('Beschreibung')
               ->setAttrib('cols', 80)
               ->setAttrib('rows', 10)
               ->setAllowEmpty(true);
        $this->addElement($description);

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
