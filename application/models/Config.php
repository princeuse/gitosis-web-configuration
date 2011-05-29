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
 * @package   Models
 */

/**
 * @category MB-it
 * @package  Models
 */
class Application_Model_Config
{
    /**
     * defining possible configuration strings
     */
    const CONFIG_DATA_GITOSIS_ADMIN_REPO    = 'gitosis_admin_repo';
    const CONFIG_DATA_GITOSIS_ADMIN_SSH_KEY = 'gitosis_admin_key';

    /**
     * allowed keys for element config
     *
     * @var array
     */
    protected $_elementKeys = null;

    /**
     * @var array
     */
    protected $_elements = null;

    /**
     * @var bool
     */
    protected $_hasChanges = false;

    /**
     * constructor
     *
     * loading configuration from database
     */
    public function __construct ()
    {
        $refl = new ReflectionClass('Application_Model_Config');
        $this->_elementKeys = $refl->getConstants();

        $this->_elements = array();
        $this->_loadConfig();
    }

    /**
     * destructor
     *
     * storing changed configuration to database
     */
    public function __destruct ()
    {
        $this->_writeConfig();
    }

    /**
     * getting config element
     *
     * @param string $key
     * @return Application_Model_Config_Element
     */
    public function getConfigElement($key)
    {
        if (array_key_exists($key, $this->_elements)) {
            return $this->_elements[$key];
        } elseif (in_array($key, $this->_elementKeys)) {
            $this->_elements[$key] = new Application_Model_Config_Element();
            $this->_elements[$key]->load($key);
            return $this->_elements[$key];
        }
        return null;
    }

    /**
     * getting all config elements
     *
     * @return array
     */
    public function getConfigElements()
    {
        return $this->_elements;
    }

    /**
     * setting config element
     *
     * @param string|array|Application_Model_Config_Element $configElement
     * @return Application_Model_Config
     */
    public function setElement($configElement)
    {
        if ($configElement instanceof Application_Model_Config_Element) {
            $code = $configElement->getCode();
            if (in_array($code, $this->_elementKeys)) {
                $this->_hasChanges = true;
                $this->_elements[$code] = $configElement;
            }
        } elseif (is_array($configElement) && array_key_exists('code', $configElement) && in_array($configElement['code'], $this->_elementKeys)) {
            $code = trim((string) $configElement['code']);
            $this->_elements[$code] = new Application_Model_Config_Element();
            $this->_elements[$code]->setData($configElement);
            $this->_hasChanges = true;
        } else {
            $code = trim((string) $configElement);
            if (!empty($code)) {
                $this->_elements[$code] = new Application_Model_Config_Element();
                $this->_elements[$code]->load($code);
                $this->_hasChanges = true;
            }
        }
        return $this;
    }

    /**
     * setting config elements
     *
     * @param array $data
     * @return Application_Model_Config
     */
    public function setFormData($data)
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException('$data has to be an array');
        }

        foreach ($data as $key => $value) {
            $configElement = array(
                'code'  => $key,
                'value' => $value
            );
            $this->setElement($configElement);
        }
    }

    /**
     * loading configuration out of database
     */
    protected function _loadConfig ()
    {
        $model = new Application_Model_Db_Config();
        $data  = $model->getCodes();

        if (empty($data)) {
            return;
        }

        foreach ($data as $row) {
            $code = trim((string) $row['config_code']);
            $this->_elements[$code] = new Application_Model_Config_Element();
            $this->_elements[$code]->load($code);
        }
    }

    /**
     * writing configuration to database
     */
    protected function _writeConfig ()
    {
        if (empty($this->_elements) || !$this->_hasChanges) {
            return;
        }

        foreach ($this->_elements as $element) {
            $element->save();
        }
    }
}