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
class Application_Form_Config extends MBit_Form
{
    /**
     * creating the form
     */
    public function init()
    {
        $model = new Application_Model_Config();
        $elements = $model->getConfigElements();
        foreach ($elements as $element) {
            $type = $element->getType();
            switch($type) {
                case 'text':
                    $this->addElement($this->_getTextElement($element));
                    break;

                case 'varchar':
                    $this->addElement($this->_getVarcharElement($element));
                    break;

                case 'integer':
                    $this->addElement($this->_getIntElement($element));
                    break;

                case 'float':
                    $this->addElement($this->_getFloatElement($element));
                    break;
            }
        }

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setDecorators($this->_elementDecoratorClear)
               ->setLabel('Speichern');
        $this->addElement($submit);

        $reset = new Zend_Form_Element_Reset('reset');
        $reset->setDecorators($this->_elementDecoratorClear)
              ->setLabel('zurück setzen');
        $this->addElement($reset);
    }

    /**
     * @param Application_Model_Config_Element $configElement
     * @return Zend_Form_Element_Text
     */
    protected function _getVarcharElement(Application_Model_Config_Element $configElement)
    {
        $formElement = new Zend_Form_Element_Text($configElement->getCode());
        $formElement->setDecorators($this->_elementDecoratorParagraph)
                    ->setFilters($this->_standardFilters)
                    ->setLabel($configElement->getLabel());

        if ($configElement->isRequired()) {
            $formElement->setAllowEmpty(false)
                        ->setRequired(true)
                        ->addValidator(
                            'NotEmpty',
                            true,
                                array(
                                    'messages' => array (
                                        Zend_Validate_NotEmpty::IS_EMPTY => $configElement->getLabel() . ' darf nicht leer sein'
                                )
                            )
                        );
        }
        $formElement->addValidator(
                            'StringLength',
                            true,
                            array(
                            1,
                            250,
                                'messages' => array (
                                    Zend_Validate_StringLength::TOO_LONG  => $configElement->getLabel() . ' darf maximal 250 Zeichen lang sein',
                            )
                        )
                    );
        $formElement->setValue($configElement->getValue());

        return $formElement;
    }

    /**
     * @param Application_Model_Config_Element $configElement
     * @return Zend_Form_Element_Textarea
     */
    protected function _getTextElement(Application_Model_Config_Element $configElement)
    {
        $formElement = new Zend_Form_Element_Textarea($configElement->getCode());
        $formElement->setDecorators($this->_elementDecoratorParagraph)
                    ->setFilters($this->_standardFilters)
                    ->setLabel($configElement->getLabel())
                    ->setAttrib('cols', 80)
                    ->setAttrib('rows', 10);

        if ($configElement->isRequired()) {
            $formElement->setAllowEmpty(false)
                        ->setRequired(true)
                        ->addValidator(
                            'NotEmpty',
                            true,
                            array(
                                'messages' => array(
                                    Zend_Validate_NotEmpty::IS_EMPTY => $configElement->getLabel() . ' darf nicht leer sein'
                                )
                            )
                        );
        }
        $formElement->setValue($configElement->getValue());

        return $formElement;
    }

    /**
     * @param Application_Model_Config_Element $configElement
     * @return Zend_Form_Element_Text
     */
    protected function _getIntElement(Application_Model_Config_Element $configElement)
    {
        $formElement = new Zend_Form_Element_Text($configElement->getCode());
        $formElement->setDecorators($this->_elementDecoratorParagraph)
                    ->setFilters($this->_standardFilters)
                    ->setLabel($configElement->getLabel());

        if ($configElement->isRequired()) {
            $formElement->setAllowEmpty(false)
                        ->setRequired(true)
                        ->addValidator(
                            'NotEmpty',
                            true,
                                array(
                                    'messages' => array (
                                        Zend_Validate_NotEmpty::IS_EMPTY => $configElement->getLabel() . ' darf nicht leer sein'
                                )
                            )
                        );
        }

        $formElement->addValidator(
                            'Int',
                            true,
                            array(
                                'messages' => array (
                                    Zend_Validate_Int::NOT_INT => $configElement->getLabel() . ' muss eine ganze Zahl sein'
                            )
                        )
                    );
        $formElement->setValue($configElement->getValue());

        return $formElement;
    }

    /**
     * @param Application_Model_Config_Element $configElement
     * @return Zend_Form_Element_Text
     */
    protected function _getFloatElement(Application_Model_Config_Element $configElement)
    {
        $formElement = new Zend_Form_Element_Text($configElement->getCode());
        $formElement->setDecorators($this->_elementDecoratorParagraph)
                    ->setFilters($this->_standardFilters)
                    ->setLabel($configElement->getLabel());

        if ($configElement->isRequired()) {
            $formElement->setAllowEmpty(false)
                        ->setRequired(true)
                        ->addValidator(
                            'NotEmpty',
                            true,
                                array(
                                    'messages' => array (
                                        Zend_Validate_NotEmpty::IS_EMPTY => $configElement->getLabel() . ' darf nicht leer sein'
                                )
                            )
                        );
        }

        $formElement->addValidator(
                            'Float',
                            true,
                            array(
                                'messages' => array (
                                    Zend_Validate_Float::NOT_FLOAT => $configElement->getLabel() . ' muss eine Gleitkommazahl sein'
                            )
                        )
                    );
        $formElement->setValue($configElement->getValue());
        
        return $formElement;
    }
}
