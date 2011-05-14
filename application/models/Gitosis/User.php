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
class Application_Model_Gitosis_User implements MBit_Model_CrudInterface
{
    /**
     * array containing group ids
     *
     * @var array
     */
    protected $_groups = null;

    /**
     * @var int
     */
    protected $_id = null;

    /**
     * @var string
     */
    protected $_name = null;

    /**
     * @var string
     */
    protected $_mail = null;

    /**
     * @var string
     */
    protected $_sshKey = null;

    /**
     * containing origin data
     *
     * @var array
     */
    protected $_originData = null;

    /**
     * loading a user
     *
     * @param int $id
     * @return Application_Model_Gitosis_User
     * @throws InvalidArgumentException
     */
    public function load ($id = null)
    {
        if (!empty($id) && intval($id) > 0) {
            $this->_id = $id;
        }

        if (empty($this->_id)) {
            throw new InvalidArgumentException('user id has to be set or given via parameter');
        }

        $this->_loadUser();
        $this->_loadGroups();

        return $this;
    }

    /**
     * storing user and groups to database
     *
     * @return bool
     */
    public function save()
    {
        $saveUser = $this->_saveUser();
        $this->_saveGroups();

        return $saveUser;
    }

    /**
     * deleting a user
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id = null)
    {
        if (!empty($id) && intval($id) > 0) {
            $this->_id = $id;
        }

        if (empty($this->_id)) {
            throw new InvalidArgumentException('user id has to be set or given via parameter, skipping remove');
        }

        $model = new Application_Model_Db_Gitosis_Users();
        return $model->deleteItem($this->_id);
    }

    /**
     * getting user id
     *
     * @return int
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * getting user name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * getting ssh key of user
     *
     * @return string
     */
    public function getSshKey()
    {
        return $this->_sshKey;
    }

    /**
     * getting user mailadress
     *
     * @return int
     */
    public function getMailAdress()
    {
        return $this->_mail;
    }

    /**
     * getting one user group
     *
     * @return Application_Model_Gitosis_Group
     */
    public function getGroup($groupId)
    {
        if (!is_array($this->_groups) || !in_array($groupId, $this->_groups)) {
                return null;
        }
        $model = new Application_Model_Gitosis_Group();
        $model->load($groupId);

        return $model;
    }

    /**
     * getting all user groups
     *
     * @return array
     */
    public function getGroups()
    {
        if (!is_array($this->_groups)) {
                return null;
        }

        $returnValue = null;
        $counter = 0;
        foreach ($this->_groups as $groupId) {
            $returnValue[$counter] = $this->getGroup($groupId);
            $counter++;
        }
        return $returnValue;
    }

    /**
     * setting user id
     *
     * @param int $id
     * @return Application_Model_Gitosis_User
     */
    public function setId($id)
    {
        if (intval($id) > 0) {
            $this->_id = $id;
        }
        return $this;
    }

    /**
     * setting user name
     *
     * @param string $name
     * @return Application_Model_Gitosis_User
     */
    public function setName($name)
    {
        if (is_string($name) && !empty($name)) {
            $this->_name = trim($name);
        }
        return $this;
    }

    /**
     * setting ssh key
     *
     * @param string $sshKey
     * @return Application_Model_Gitosis_User
     */
    public function setSshKey($sshKey)
    {
        if (is_string($sshKey) && !empty($sshKey)) {
            $this->_sshKey = trim($sshKey);
        }
        return $this;
    }

    /**
     * setting mail address
     *
     * @param string $mail
     * @return Application_Model_Gitosis_User
     */
    public function setMailAdress($mail)
    {
        if (is_string($mail) && !empty($mail)) {
            $this->_mail = trim($mail);
        }
        return $this;
    }

    /**
     * adding user to group
     *
     * @param int|Application_Model_Gitosis_Group $group
     * @return Application_Model_Gitosis_User
     * @throws InvalidArgumentException
     */
    public function addGroup ($group)
    {
        if ($group instanceof Application_Model_Gitosis_Group) {
            $groupId = $group->getId();
        } elseif (intval($group) > 0) {
            $groupId = intval($group);
        } else {
            $groupId = -1;
        }

        if ($groupId <= 0) {
            throw new InvalidArgumentException('no group id given');
        }

        if (!is_array($this->_groups) || !in_array($groupId, $this->_groups)) {
                $this->_groups[] = $groupId;
                sort($this->_groups);
        }
        return $this;
    }

    /**
     * adding user to multiple groups
     *
     * the array can contain group ids or group objects
     *
     * @param array $groupIds
     * @return Application_Model_Gitosis_User
     */
    public function addGroups($groups)
    {
        if (!is_array($groups)) {
            $groups = array($groups);
        }

        foreach ($groups as $group) {
            try {
                $this->addGroup($group);
            } catch (InvalidArgumentException $e) {
                continue;
            }
        }

        return $this;
    }

    /**
     * removing user from a group
     *
     * @param int|Application_Model_Gitosis_Group $group
     * @return Application_Model_Gitosis_User
     * @throws InvalidArgumentException
     */
    public function removeGroup ($group)
    {
        if ($group instanceof Application_Model_Gitosis_Group) {
            $groupId = $group->getId();
        } elseif (intval($group) > 0) {
            $groupId = intval($group);
        } else {
            $groupId = -1;
        }

        if ($groupId <= 0) {
            throw new InvalidArgumentException('no group id given');
        }

        if (is_array($this->_groups) && in_array($groupId, $this->_groups)) {
            foreach ($this->_groups as $key => $value) {
                if ($groupId == $value) {
                    unset ($this->_groups[$key]);
                }
            }
        }

        return $this;
    }

    /**
     * removing user from groups
     *
     * the array can contain Application_Model_Gitosis_Group or group ids
     *
     * @param array $groups
     * @return Application_Model_Gitosis_User
     */
    public function removeGroups ($groups)
    {
        if (!is_array($groups)) {
            $groups = array($groups);
        }

        foreach ($groups as $group) {
            try {
                $this->removeGroup($group);
            } catch (InvalidArgumentException $e) {
                continue;
            }
        }

        return $this;
    }

    /**
     * return select statement for paginator
     *
     * @return Zend_Db_Table_Select
     */
    public function getPaginatorSelect()
    {
        $userModel = new Application_Model_Db_Gitosis_Users();
        $select = $userModel->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
        $select->columns(
            array(
                new Zend_Db_Expr('gitosis_user_id as id'),
                new Zend_Db_Expr('gitosis_user_name as name'),
                new Zend_Db_Expr('gitosis_user_email as email'),
                new Zend_Db_Expr('gitosis_user_ssh_key as ssh_key'),
            )
        );
        return $select;
    }

    /**
     * getting all data
     *
     * @return array
     */
    public function getData()
    {
        return array (
            'id'        => $this->getId(),
            'name'      => $this->getName(),
            'email'     => $this->getMailAdress(),
            'ssh_key'   => $this->getSshKey()
        );
    }

    /**
     * setting multiple data
     *
     * @param array $data
     */
    public function setData($data)
    {
        if (empty($data)) {
            return;
        }

        foreach ($data   as $key => $value) {
            switch($key) {
                case 'name':
                    $this->setName($value);
                    break;

                case 'email':
                    $this->setMailAdress($value);
                    break;

                case 'id':
                    $this->setId($value);
                    break;

                case 'ssh_key':
                    $this->setSshKey($value);
                    break;

                case 'groups':
                    if (is_array($value)) {
                        $this->addGroups($value);
                    } else {
                        $this->addGroup($value);
                    }
            }
        }
    }

    /**
     * getting user from database
     *
     * @throws UnexpectedValueException
     */
    protected function _loadUser()
    {
        if (empty($this->_id)) {
            throw new UnexpectedValueException('no user id given, can\'t load user');
        }

        $userModel = new Application_Model_Db_Gitosis_Users();
        $userData  = $userModel->getById($this->_id);

        if (!empty($userData)) {
            $this->_originData = $userData;

            foreach ($userData as $fieldName => $fieldValue) {
                switch ($fieldName) {
                    case 'gitosis_user_id':
                        $this->_id = intval($fieldValue);
                        break;

                    case 'gitosis_user_name':
                        $this->setName($fieldValue);
                        break;

                    case 'gitosis_user_email':
                        $this->setMailAdress($fieldValue);
                        break;

                    case 'gitosis_user_ssh_key':
                        $this->setSshKey($fieldValue);
                        break;

                    default:
                        Zend_Registry::get('Zend_Log')->warn('field "' . $fieldName . '" ignored while loading user');
                        break;
                }
            }
        } else {
            $this->_id = null;
        }
    }

    /**
     * getting groups from database
     *
     * @throws UnexpectedValueException
     */
    protected function _loadGroups()
    {
        $userId = $this->getId();
        if (empty($userId)) {
            throw new UnexpectedValueException('no user id given');
        }

        $this->_groups = $this->_getGroupIds($userId);
    }

    /**
     * saving user
     *
     * @return bool
     */
    protected function _saveUser()
    {
        $userModel = new Application_Model_Db_Gitosis_Users();

        $dbData = array(
            'gitosis_user_name'     => $this->getName(),
            'gitosis_user_email'    => $this->getMailAdress(),
            'gitosis_user_ssh_key'  => $this->getSshKey()
        );

        if (!$this->_id) {
            $primId = $userModel->insert($dbData);
            if ($primId) {
                $this->_id = $primId;
                return true;
            } else {
                return false;
            }
        } elseif (is_array($this->_originData)) {
            foreach ($this->_originData as $fieldName => $fieldValue) {
                if (array_key_exists($fieldName, $dbData) && $dbData[$fieldName] == $fieldValue) {
                    unset($dbData[$fieldName]);
                }
            }

            if (empty($dbData)) {
                return true;
            } else {
                $rows = $userModel->update(
                    $dbData,
                    array('gitosis_user_id = ?' => $this->_id)
                );

                if ($rows == 1) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * storing user to group relations
     *
     * @throws InvalidArgumentException
     */
    protected function _saveGroups()
    {
        $userId = $this->_id;
        if (empty($userId)) {
            throw new InvalidArgumentException('no user id set');
        }

        if (!empty($this->_groups)) {
            $groupIds = $this->_groups;
        } else {
            $groupIds = array();
        }
        sort($groupIds);

        $currentGroups = $this->_getGroupIds($userId);
        if (empty($currentGroups)) {
            $currentGroups = array();
        }
        sort($currentGroups);

        $addedGroups    = array_diff($groupIds, $currentGroups);
        $removedGroups  = array_diff($currentGroups, $groupIds);

        $groupModel = new Application_Model_Db_Gitosis_UsersGroups();
        if (!empty($addedGroups)) {
            foreach ($addedGroups as $groupId) {
                $groupModel->addUserToGroup($groupId, $userId);
            }
        }

        if (!empty($removedGroups)) {
            foreach ($removedGroups as $groupId) {
                $groupModel->removeUserFromGroup($groupId, $userId);
            }
        }
    }

    /**
     * getting all group ids related to this user
     *
     * @return array
     */
    protected function _getGroupIds ($userId)
    {
        $model = new Application_Model_Db_Gitosis_UsersGroups();
        $groups = $model->getUserGroups($userId);

        if (!empty($groups)) {
            $groupIds = array();

            foreach ($groups as $group) {
                $groupIds[] = $group['gitosis_group_id'];
            }
        }
        return (empty($groupIds) ? null : $groupIds);
    }
}