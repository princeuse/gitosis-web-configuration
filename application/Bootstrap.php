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
 * @package   Core
 */

/**
 * @category MB-it
 * @package  Core
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    /**
     * constants defining the environment
     */
    const ENV_PROD  = 'production';
    const ENV_STAG  = 'staging';
    const ENV_DEV   = 'development';

    /**
     * initialise view
     *
     * @return Zend_View
     */
    protected function _initView()
    {
        $this->bootstrap('autoload');
        $themeModel = MBit_Theme::getInstance();
        if (!$themeModel->setTheme($this->getOption('gitosis_theme'))) {
            $themeModel->setTheme('mb-it');
        }

        $layout = Zend_Layout::startMvc()
            ->setLayout('layout')
            ->setLayoutPath($themeModel->getLayoutPath())
            ->setContentKey('content');

        $view = new Zend_View();
        $view->doctype('XHTML1_STRICT');
        $view->setEncoding('UTF-8');
        $view->setBasePath($themeModel->getViewPath());
        $view->setScriptPath($themeModel->getViewScriptPath());
        $view->addScriptPath($themeModel->getPartialPath());

        $view->addHelperPath(
            realpath(
                    APPLICATION_PATH . DIRECTORY_SEPARATOR .
                    '..'             . DIRECTORY_SEPARATOR .
                    'library'        . DIRECTORY_SEPARATOR .
                    'MBit'           . DIRECTORY_SEPARATOR .
                    'View'           . DIRECTORY_SEPARATOR .
                    'Helper'),
            'MBit_View_Helper_'
        );

        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
        $viewRenderer->setView($view);

        return $view;
    }

    /**
     * loading navigation
     *
     * @return Zend_Navigation
     */
    protected function _initNavigation()
    {
        $navConfig  = realpath(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'configs') . DIRECTORY_SEPARATOR . 'navigation.ini';
        $iniConfig  = new Zend_Config_Ini($navConfig);
        $navigation = new Zend_Navigation($iniConfig);

        $view = $this->getResource('view');
        $view->navigation($navigation);

        return $navigation;
    }

    /**
     * Controller-Plugins in Front-Controller registrieren
     *
     * @return Zend_Controller_Front
     */
    protected function _initControllerPlugins ()
    {
        $this->bootstrap('autoload');
        $this->bootstrap('frontController');

        /* @var $front Zend_Controller_Front */
        $front = $this->getResource('frontController');

        if (PHP_SAPI !== 'cli') {
            $front->registerPlugin(new MBit_Controller_Plugin_Auth(), 1);
        }

        return $front;
    }

    /**
     * setting timezone and language
     *
     * @return Zend_Locale
     */
    protected function _initLocale()
    {
        if (date_default_timezone_get() !== "Europe/Berlin"
            && function_exists('date_default_timezone_set')
        ) {
            date_default_timezone_set('Europe/Berlin');
        }

        $lang = 'de_DE';
        $locale = new Zend_Locale();
        $locale->setLocale($lang);

        $this->bootstrap('registry');
        $registry = $this->getResource('registry');

        $registry->set('Zend_Locale', $locale);

        return $locale;
    }

    /**
     * registering namespaces
     *
     * @return Zend_Loader_Autoload
     */
    protected function _initAutoload()
    {

        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->registerNamespace('MBit_');

        return $autoloader;
    }

    /**
     * initialise registry
     *
     * @return Zend_Registry
     */
    protected function _initRegistry()
    {
        $registry = Zend_Registry::getInstance();
        $registry->setFlags(ArrayObject::ARRAY_AS_PROPS);

        return $registry;
    }

    /**
     * initialise database
     *
     * @return Zend_Db_Adapter_Pdo_Mysql
     */
    protected function _initDatabase()
    {
        $this->bootstrap('db');
        $resource = $this->getPluginResource('db');

        $this->bootstrap('registry');

        /* @var $db Zend_Db_Adapter_Pdo_Mysql */
        $db = $resource->getDbAdapter();
        $db->query("SET NAMES 'utf8'");

        /* @var $registry Zend_Registry */
        $registry = $this->getResource('registry');
        $registry->db = $db;

        return $db;
    }

    /**
     * initialise logging
     *
     * In productive environment the logger just writes warnings and errors. In
     * development and staging environment all messages are logged and firebug
     * logger is added.
     *
     * @return Zend_Log
     */
    protected function _initLogger()
    {
        $logger = new Zend_Log();

        $this->bootstrap('locale');
        $date = new Zend_Date();
        $now = $date->get(
            Zend_Date::YEAR     . '-' .
            Zend_Date::MONTH    . '-' .
            Zend_Date::DAY      . ' ' .
            Zend_Date::TIME_SHORT
        );

        $formatStream = new Zend_Log_Formatter_Simple($now . " %priorityName%\t %message%" . PHP_EOL);
        $filterWarn = new Zend_Log_Filter_Priority(Zend_Log::WARN);

        $logPath = implode(DIRECTORY_SEPARATOR, array(APPLICATION_PATH, '..', 'var', 'log'));
        if (!file_exists($logPath) || !is_dir($logPath)) {
            mkdir($logPath, 0775, true);
        }
        $logPath = realpath($logPath);

        $streamWriter = new Zend_Log_Writer_Stream($logPath . DIRECTORY_SEPARATOR . 'application.log');
        $streamWriter->setFormatter($formatStream);

        if ($this->getEnvironment() == Bootstrap::ENV_PROD) {
            $streamWriter->addFilter($filterWarn);
        }

        $logger->addWriter($streamWriter);

        if ($this->getEnvironment() == Bootstrap::ENV_DEV) {
            $firebugWriter = new Zend_Log_Writer_Firebug();
            $logger->addWriter($firebugWriter);
        }

        $this->bootstrap('registry');
        $registry = Zend_Registry::getInstance();
        $registry->Zend_Log = $logger;

        return $logger;
    }

    /**
     * initialise audit log
     *
     * audit log is storing information about changes in configuration. When commiting and pushing informations this
     * log entries are added as comments.
     *
     * @return Zend_Log
     */
    protected function _initAuditLog()
    {

        $dbAdapter = $this->getResource('database');

        $columnMapping = array(
            'audit_log_message'     => 'message',
            'audit_log_timestamp'   => 'timestamp'
        );

        $logWriter = new Zend_Log_Writer_Db($dbAdapter, 'audit_log', $columnMapping);
        $logger = new Zend_Log($logWriter);

        $this->bootstrap('registry');
        $registry = Zend_Registry::getInstance();
        $registry->Audit_Log = $logger;

        return $logger;
    }

}
