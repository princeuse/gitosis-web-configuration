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
abstract class Application_Form_Import_ImportAbstract extends MBit_Form
{
    /**
     * path to folder for storing file uploads
     * 
     * @return string
     */
    protected function _getFileUploadDestination()
    {
        $fileUploadDestination = implode(
            DIRECTORY_SEPARATOR,
            array(
                APPLICATION_PATH,
                '..',
                'var',
                'tmp'
            )
        );

        if (!is_dir($fileUploadDestination)) {
            mkdir($fileUploadDestination, 0775, true);
        }

        return $fileUploadDestination;
    }
}