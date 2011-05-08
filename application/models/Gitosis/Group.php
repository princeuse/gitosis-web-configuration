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
class Application_Model_Gitosis_Group
{
    /**
     * @var int
     */
    protected $_id = null;

    /**
     * @var string
     */
    protected $_name = null;

    /**
     * @var array
     */
    protected $_originData = null;

    /**
     * loading group
     *
     * @param int $id
     * @return Application_Model_Gitosis_Group
     * @throws InvalidArgumentException
     */
    public function load ($id = null)
    {
        if (!empty($id) && intval($id) > 0) {
            $this->_id = $id;
        }

        if (empty($this->_id)) {
            throw new InvalidArgumentException('group id has to be set or given via parameter');
        }

        $this->_loadGroup();
        return $this;
    }

    /**
     * storing group to database
     *
     * @return Application_Model_Gitosis_Group
     */
    public function save ()
    {
        $this->_saveGroup();

        return $this;
    }

    /**
     * getting id
     *
     * @return int
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * getting name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * setting id
     *
     * @param int $id
     * @return Application_Model_Gitosis_Group
     */
    public function setId($id)
    {
        if (intval($id) > 0) {
            $this->_id = $id;
        }
        return $this;
    }

    /**
     * setting name
     *
     * @param string $name
     * @return Application_Model_Gitosis_Group
     */
    public function setName($name)
    {
        if (is_string($name) && !empty($string)) {
            $this->_name = trim($name);
        }
        return $this;
    }

    /**
     * getting group from database
     *
     * @throws UnexpectedValueException
     */
    protected function _loadGroup()
    {
        if (empty($this->_id)) {
            throw new UnexpectedValueException('no group id given, can\'t load group');
        }

        $groupModel = new Application_Model_Db_Gitosis_Groups();
        $groupData  = $groupModel->getById($this->_id);

        if (!empty($groupData)) {
            $this->_originData = $groupData;

            foreach ($groupData as $fieldName => $fieldValue) {
                switch ($fieldName) {
                    case 'gitosis_group_id':
                        $this->_id = intval($fieldValue);
                        break;

                    case 'gitosis_group_name':
                        $this->setName($fieldValue);
                        break;

                    default:
                        Zend_Registry::get('Zend_Log')->warn('field "' . $fieldName . '" ignored while loading group');
                        break;
                }
            }
        }
    }

    /**
     * saving user
     */
    protected function _saveGroup()
    {
        $groupModel = new Application_Model_Db_Gitosis_Groups();

        $dbData = array('gitosis_group_name' => $this->getName());

        if (!$this->_id) {
            $primId = $groupModel->insert($dbData);
            if ($primId) {
                $this->_id = $primId;
            }
        } else {
            foreach ($this->_originData as $fieldName => $fieldValue) {
                if ($dbData[$fieldName] == $fieldValue) {
                    unset($dbData[$fieldName]);
                }
            }

            if (!empty($dbData)) {
                $groupModel->update(
                    $dbData,
                    array('gitosis_group_id = ?' => $this->_id)
                );
            }
        }
    }
}