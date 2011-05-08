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
class Application_Model_Gitosis_User
{
    /**
     * array containing group objects
     * {@see Application_Model_Gitosis_Group}
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
        return $this;
    }

    /**
     * storing user and groups to database
     *
     * @return bool
     */
    public function save ()
    {
        $saveUser = $this->_saveUser();
        $this->_saveGroups();

        return $saveUser;
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
        if (!is_array($this->_groups) ||
            !array_key_exists($groupId, $this->_groups)) {
                return null;
        }
        return $this->_groups[$groupId];
    }

    /**
     * getting all user groups
     *
     * @return array
     */
    public function getGroups()
    {
        return $this->_groups;
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
     * @param int $groupId
     * @return Application_Model_Gitosis_User
     * @throws InvalidArgumentException
     */
    public function addGroup ($groupId)
    {
        $groupId = intval($groupId);
        if ($groupId <= 0) {
            throw new InvalidArgumentException('no group id given');
        }

        if (!array_key_exists($groupId, $this->_groups)) {
            $groupModel = new Application_Model_Gitosis_Group();
            $groupModel->load($groupId);

            if ($groupModel->getId()) {
                $this->_groups[$groupId] = $groupModel;
            }
        }
        return $this;
    }

    /**
     * adding user to multiple groups
     *
     * @param array $groupIds
     * @return Application_Model_Gitosis_User
     */
    public function addGroups($groupIds)
    {
        if (!is_array($groupIds)) {
            $groupIds = array($groupIds);
        }

        foreach ($groupIds as $groupId) {
            try {
                $this->addGroup($groupId);
            } catch (InvalidArgumentException $e) {
                continue;
            }
        }

        return $this;
    }

    /**
     * removing user from a group
     *
     * @param int $groupId
     * @return Application_Model_Gitosis_User
     * @throws InvalidArgumentException
     */
    public function removeGroup ($groupId)
    {
        $groupId = intval($groupId);
        if ($groupId <= 0) {
            throw new InvalidArgumentException('no group id given');
        }

        if (array_key_exists($groupId, $this->_groups)) {
            unset ($this->_groups[$groupId]);
        }

        return $this;
    }

    /**
     * removing user from groups
     *
     * @param array $groupIds
     * @return Application_Model_Gitosis_User
     */
    public function removeGroups ($groupIsd)
    {
        if (!is_array($groupIds)) {
            $groupIds = array($groupIds);
        }

        foreach ($groupIds as $groupId) {
            try {
                $this->removeGroup($groupId);
            } catch (InvalidArgumentException $e) {
                continue;
            }
        }

        return $this;
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

            try {
                $this->_loadGroups();
            } catch (UnexpectedValueException $e) {
                Zend_Registry::get('Zend_Log')->error('error while loading user groups: ' . $e->getMessage());
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
        if (empty($this->_groups)) {
            $userId = $this->getId();

            if (empty($userId)) {
                throw new UnexpectedValueException('no user id given');
            }

            $groups = $this->_getGroupIds($userId);
            if (empty($groups)) {
                $this->_groups = null;
            } else {
                foreach ($groups as $groupId) {
                    $this->_groups[$groupId] = new Application_Model_Gitosis_Group();
                    $this->_groups[$groupId]->load($groupId);
                    if (!$this->_groups[$groupId]->getId()) {
                        unset($this->_groups[$groupId]);
                    }
                }
            }
        }
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
        } else {
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
        if (empty($this->_id)) {
            throw new InvalidArgumentException('no user id set');
        }

        if (!empty($this->_groups)) {
            $groupIds = array_keys($this->_groups);
            sort($groupIds);
        } else {
            $groupIds = array();
        }
        $currentGroups = $this->_getGroupIds($this->_id);
        sort($currentGroups);

        $addedGroups    = array_diff($groupIds, $currentGroups);
        $removedGroups  = array_diff($currentGroups, $groupIds);

        $groupModel = new Application_Model_Db_Gitosis_UsersGroups();
        if (!empty($addedGroups)) {
            foreach ($addedGroups as $groupId) {
                $groupModel->addUserToGroup($groupId, $this->_id);
            }
        }

        if (!empty($removedGroups)) {
            foreach ($removedGroups as $groupId) {
                $groupModel->removeUserFromGroup($groupId, $this->_id);
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