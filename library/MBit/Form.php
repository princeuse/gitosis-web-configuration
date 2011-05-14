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
 * @package   Lib
 */

/**
 * @category MB-it
 * @package  Lib
 */
abstract class MBit_Form extends Zend_Form
{

    /**
     * decorator for elements (paragraph)
     *
     * @var array
     */
    protected $_elementDecoratorParagraph = array(
        'ViewHelper',
    	'LabelExt',
    	'Errors',
    	array('HtmlTag', array ('tag' => 'p'))
    );

    /**
     * decorator for elements (no embracing tags)
     *
     * @var array
     */
    protected $_elementDecoratorNoTags = array(
        'ViewHelper',
    	'LabelExt',
    	'Errors',
    );

    /**
     * decorator for groups (fieldset)
     *
     * @var array
     */
    protected $_groupDecoratorDefault = array(
        'FormElements',
    	'Fieldset'
    );

    /**
     * decorator for elements (clear)
     *
     * @var array
     */
    protected $_elementDecoratorClear = array(
		'ViewHelper'
    );

    /**
     * default filter for form elements
     *
     * @var array
     */
    protected $_standardFilters = array(
        'StringTrim',
        'StripTags'
    );

    /**
     * constructor
     *
     * intialize mbit decorator
     *
     * @param array $options
     */
    public function __construct($options = null)
    {

        parent::__construct($options);

        $this->addElementPrefixPath('MBit_Form_Decorator',
                                    'MBit/Form/Decorators',
                                    'decorator');

        $this->setAttrib('accept-charset', 'UTF-8');
        $this->setDecorators(array(
            'FormElements',
            'Form'
        ));
    }

}