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
class MBit_View_Helper_Theme extends Zend_View_Helper_Abstract
{
    /**
     * @var MBit_Theme
     */
    protected $_themeModel = null;

    /**
     * view helper constructor
     *
     * @return MBit_View_Helper_Theme
     */
    public function theme()
    {
        $this->_loadTheme();
        return $this;
    }

    /**
     * getting theme base url
     *
     * @param string $path
     */
    public function getUrl($path = '')
    {
        return $this->view->baseUrl('/themes/' . $this->_themeModel->getTheme() . $path);
    }

    /**
     * loading themeing model
     */
    protected function _loadTheme()
    {
        if ($this->_themeModel === null) {
            $this->_themeModel = MBit_Theme::getInstance();
        }
    }
}