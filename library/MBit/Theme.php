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
class MBit_Theme
{
    /**
     * @var MBit_Theme
     */
    static protected $_instance = null;

    /**
     * name of the used theme
     * @var string
     */
    protected $_theme = null;

    /**
     * base path for theme
     * @var string
     */
    protected $_basePath = null;

    /**
     * layout path for theme
     * @var string
     */
    protected $_layoutPath = null;

    /**
     * view path for theme
     * @var string
     */
    protected $_viewPath = null;

    /**
     * partial path for theme
     * @var string
     */
    protected $_partialPath = null;

    /**
     * Getting instance of this class
     *
     * The parameter is the name of the theme.
     *
     * @param string $theme
     * @return MBit_Theme
     */
    static public function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Setting theme
     *
     * Setting given theme and resetting paths.
     *
     * @param string $theme
     * @return bool
     */
    public function setTheme($theme)
    {
        if ($this->_themeExists($theme)) {
            $this->_theme = (string) $theme;
            $this->_resetPaths();
            return true;
        }
        return false;
    }

    /**
     * getting theme
     *
     * @return string
     */
    public function getTheme()
    {
        return $this->_theme;
    }

    /**
     * getting base path of theme
     *
     * @return string
     */
    public function getBasePath()
    {
        if ($this->_basePath === null) {
            $this->_basePath = realpath($this->_getThemesPath() . DIRECTORY_SEPARATOR . $this->_theme);
        }
        return $this->_basePath;
    }

    /**
     * getting layout path of theme
     *
     * @return string
     */
    public function getLayoutPath()
    {
        if ($this->_layoutPath === null) {
            $this->_layoutPath = realpath($this->getBasePath() . DIRECTORY_SEPARATOR . 'layouts');
        }
        return $this->_layoutPath;
    }

    /**
     * getting partials path of theme
     *
     * @return string
     */
    public function getPartialPath()
    {
        if ($this->_partialPath === null) {
            $this->_partialPath = realpath($this->getLayoutPath() . DIRECTORY_SEPARATOR . 'partials');
        }
        return $this->_partialPath;
    }

    /**
     * getting view path of theme
     *
     * @return string
     */
    public function getViewPath()
    {
        if ($this->_viewPath === null) {
            $this->_viewPath = realpath($this->getBasePath() . DIRECTORY_SEPARATOR . 'views');
        }
        return $this->_viewPath;
    }

    /**
     * getting view script path of theme
     *
     * @return string
     */
    public function getViewScriptPath()
    {
        return realpath($this->getViewPath() . DIRECTORY_SEPARATOR . 'scripts');
    }

    /**
     * Constructor
     *
     * @param string $theme
     */
    private function __construct()
    { }

    private function __clone()
    { }

    /**
     * Checking if theme path exists
     *
     * @param string $theme
     * @return bool
     */
    protected function _themeExists($theme)
    {
        $theme = (string) $theme;
        if (is_dir($this->_getThemesPath() . DIRECTORY_SEPARATOR . $theme)) {
            return true;
        }
        return false;
    }

    /**
     * Getting the path where all themes are stored
     *
     * @return string
     */
    protected function _getThemesPath()
    {
        $pathElements = array(
            dirname(__FILE__),
            '..',
            '..',
            'public',
            'themes'
        );
        return realpath(implode(DIRECTORY_SEPARATOR, $pathElements));
    }

    /**
     * Resetting paths
     */
    protected function _resetPaths()
    {
        $this->_basePath    = null;
        $this->_layoutPath  = null;
        $this->_partialPath = null;
        $this->_viewPath    = null;
    }
}
