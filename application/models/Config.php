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
    const CONFIG_DATA_GITOSIS_ADMIN = 'gitosis_admin_path';

    /**
     * @var Application_Model_Db_Config
     */
    protected $_model = null;

    /**
     * @var array
     */
    protected $_data = null;

    /**
     * constructor
     *
     * loading configuration from database
     */
    public function __construct ()
    {
        $this->_data = array();
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
     * getting config data
     *
     * @param string $key
     */
    public function getData($key)
    {
        if (array_key_exists($key, $this->_data)) {
            return $this->_data[$key]['value'];
        }
        return null;
    }

    /**
     * setting config data
     *
     * @param string $key
     * @param string $value
     * @return Application_Model_Config
     */
    public function setData($key, $value)
    {
        $key   = (string) $key;
        $value = (string) $value;

        if (empty($key) || empty($value)) {
            return $this;
        }

        if (!array_key_exists($key, $this->_data)) {
            $this->_data[$key] = array(
                'hasChanged' => true,
                'value'      => $value
            );
        } elseif ($this->_data[$key]['value'] !== $value) {
            $this->_data[$key] = array(
                'hasChanged' => true,
                'value'      => $value
            );
        }

        return $this;
    }

    /**
     * loading configuration out of database
     */
    protected function _loadConfig ()
    {
        $model = $this->_getModel();
        $data  = $model->fetchAll();

        if (empty($data)) {
            return;
        }

        foreach ($data as $configPair) {

            $key   = trim((string) $configPair->{'config_name'});
            $value = trim((string) $configPair->{'config_value'});

            $this->_data[$key] = array(
                'hasChanged' => false,
                'value'      => $value
            );
        }
    }

    /**
     * writing configuration to database
     */
    protected function _writeConfig ()
    {
        if (empty($this->_data)) {
            return;
        }

        $model = $this->_getModel();
        foreach ($this->_data as $key => $valueData) {
            if ($valueData['hasChanged']) {
                $model->save($key, $valueData['value']);
            }
        }
    }

    /**
     * getting database model
     *
     * @return Application_Model_Db_Config
     */
    protected function _getModel()
    {
        if ($this->_model === null) {
            $this->_model = new Application_Model_Db_Config();
        }
        return $this->_model;
    }
}