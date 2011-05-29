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
class Application_Model_Config_Element
{
    /**
     * unique code identifying config element
     *
     * @var string
     */
    protected $_code = null;

    /**
     * @var string
     */
    protected $_label = null;

    /**
     * @var string
     */
    protected $_type = null;

    /**
     * @var string
     */
    protected $_value = null;

    /**
     * @var string
     */
    protected $_isRequired = false;

    /**
     * @var array
     */
    protected $_originData = null;

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->_label;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return $this->_isRequired;
    }

    /**
     * @param string $code
     * @return Application_Model_Config_Element
     */
    public function setCode($code)
    {
        $code = trim((string) $code);
        if (!empty($code)) {
            $this->_code = $code;
        }
        return $this;
    }

    /**
     * @param string $label
     * @return Application_Model_Config_Element
     */
    public function setLabel($label)
    {
        $label = trim((string) $label);
        if (!empty($label)) {
            $this->_label = $label;
        }
        return $this;
    }

    /**
     * @param string $type
     * @return Application_Model_Config_Element
     */
    public function setType($type)
    {
        $type = trim((string) $type);
        if (!empty($type)) {
            $this->_type = $type;
        }
        return $this;
    }

    /**
     * @param string $value
     * @return Application_Model_Config_Element
     */
    public function setValue($value)
    {
        $value = trim((string) $value);
        if (!empty($value)) {
            $this->_value = $value;
        }
        return $this;
    }

    /**
     * @param bool $isRequired
     * @return Application_Model_Config_Element
     */
    public function setAsRequired($isRequired = true)
    {
        $this->_isRequired = (bool) $isRequired;
        return $this;
    }

    /**
     * @param array $data
     * @return Application_Model_Config_Element
     */
    public function setData($data)
    {
        if (array_key_exists('code', $data)) {
            $code = $data['code'];
            unset($data['code']);

            $this->load($code);
            $this->_setData($data);
        }
        return $this;
    }

    /**
     * loading element by given or set code
     *
     * @param string $code
     * @return Application_Model_Config_Element
     */
    public function load($code = null)
    {
        $this->setCode($code);
        if (($this->getCode() === null)) {
            throw new MBit_Exception('loading config element without code is not possible');
        }

        $dbModel = new Application_Model_Db_Config();
        $data = $dbModel->getByCode($this->getCode());
        if (!empty($data)) {
            $this->_originData = $data;
            $this->_setData($data);
        }
        return $this;
    }

    /**
     * saving config element
     *
     * @return bool
     */
    public function save()
    {
        $dbModel = new Application_Model_Db_Config();
        $row = $dbModel->fetchRow(array('config_code = ?' => $this->getCode()));
        if (empty($row)) {
            $row = $dbModel->createRow();
        }

        $row->{'config_code'}        = $this->getCode();
        $row->{'config_label'}       = $this->getLabel();
        $row->{'config_type'}        = $this->getType();
        $row->{'config_value'}       = $this->getValue();
        $row->{'config_is_required'} = ($this->isRequired() ? 1 : 0);

        return (bool) $row->save();
    }

    /**
     * @param array $data
     */
    protected function _setData($data)
    {
        foreach ($data as $key => $value) {
            switch ($key) {
                case 'config_id':
                case 'id':
                    break;

                case 'config_label':
                case 'label':
                    $this->setLabel($value);
                    break;

                case 'config_type':
                case 'type':
                    $this->setType($value);
                    break;

                case 'config_value':
                case 'value':
                    $this->setValue($value);
                    break;

                case 'config_is_required':
                case 'isRequired':
                case 'is_required':
                    $this->setAsRequired($value);
                    break;

                default:
                    $logger = Zend_Registry::get('Zend_Log');
                    $logger->log(__CLASS__ . ' | no setter found for field ' . $key, Zend_Log::INFO);
            }
        }
    }
}
