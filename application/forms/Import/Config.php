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
class Application_Form_Import_Config extends Application_Form_Import_ImportAbstract
{

    /**
     * creating the form
     */
    public function init()
    {
        $fileUploadDestination = $this->_getFileUploadDestination();

        $fileUpload = new Zend_Form_Element_File('gitosis_conf');
        $fileUpload->setDecorators($this->_elementDecoratorFile)
                    ->setLabel('Gitosis Konfiguration:')
                    ->setFilters(array('StripTags', 'StringTrim'))
                    ->setAllowEmpty(false)
                    ->setRequired(true)
                    ->setDestination($fileUploadDestination)
                    ->addValidator('Count', true, 1)
                    ->addValidator('Extension', true, 'conf');

        $validatorUpload = $fileUpload->getValidator('File_Upload');
        $validatorUpload->setMessages(
            array (
                Zend_Validate_File_Upload::ATTACK           => 'Es trat ein Systemfehler auf, die Datei konnte %value% nicht hochgeladen werden.',
                Zend_Validate_File_Upload::CANT_WRITE       => 'Es trat ein Systemfehler auf, die Datei konnte %value% nicht hochgeladen werden.',
                Zend_Validate_File_Upload::EXTENSION        => 'Es trat ein Systemfehler auf, die Datei konnte %value% nicht hochgeladen werden.',
                Zend_Validate_File_Upload::FILE_NOT_FOUND   => 'Es wurde keine Datei zum Hochladen ausgewählt.',
                Zend_Validate_File_Upload::FORM_SIZE        => 'Die Datei %value% überschreitet die zulässige Dateigröße.',
                Zend_Validate_File_Upload::INI_SIZE         => 'Die Datei %value% überschreitet die zulässige Dateigröße.',
                Zend_Validate_File_Upload::NO_FILE          => 'Es wurde keine Datei hochgeladen.',
                Zend_Validate_File_Upload::NO_TMP_DIR       => 'Es trat ein Systemfehler auf, die Datei konnte %value% nicht hochgeladen werden.',
                Zend_Validate_File_Upload::PARTIAL          => 'Es trat ein Übertragungsfehler auf. Die Datei %value% wurde nur teilweise hochgeladen.',
                Zend_Validate_File_Upload::UNKNOWN          => 'Es trat ein Systemfehler auf, die Datei konnte %value% nicht hochgeladen werden.',
            )
        );

        $validatorCount = $fileUpload->getValidator('File_Count');
        $validatorCount->setMessages(
            array(
                Zend_Validate_File_Count::TOO_MANY =>
                    "Es ist nur das Hochladen einer Datei erlaubt"
            )
        );
        $validatorExtension = $fileUpload->getValidator('File_Extension');
        $validatorExtension->setMessages(
            array(
                Zend_Validate_File_Extension::FALSE_EXTENSION =>
                    'Die Datei muss auf .conf enden.'
            )
        );
        $this->addElement($fileUpload);

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setDecorators(array( 'ViewHelper', 'Label', 'Errors'))
               ->setLabel('Speichern');
        $this->addElement($submit);
    }
}