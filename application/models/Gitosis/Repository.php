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
class Application_Model_Gitosis_Repository implements MBit_Model_CrudInterface
{
    const REPO_RIGHTS_NOTHING   = 1;
    const REPO_RIGHTS_READONLY  = 2;
    const REPO_RIGHTS_WRITEABLE = 3;

    /**
     * repository id
     *
     * @var int
     */
    protected $_id = null;

    /**
     * Owner of the repository
     *
     * @var Application_Model_Gitosis_User
     */
    protected $_owner = null;

    /**
     * name of the repository
     *
     * @var string
     */
    protected $_name = null;

    /**
     * description
     *
     * @var string
     */
    protected $_description = null;

    /**
     * IDs of groups
     *
     * @var array
     */
    protected $_groups = null;

    /**
     * origin data from db to detect updates made to this repository
     *
     * @var array
     */
    protected $_originData = null;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @return Application_Model_Gitosis_User
     */
    public function getOwner()
    {
        return $this->_owner;
    }

    /**
     * @return int
     */
    public function getOwnerId()
    {
        if ($this->_owner instanceof Application_Model_Gitosis_User) {
            return $this->_owner->getId();
        }
        return 0;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * setting id
     *
     * @param int $id
     * @return Application_Model_Gitosis_Repository
     */
    public function setId($id)
    {
        if (intval($id) > 0) {
            $this->_id = intval($id);
        }
        return $this;
    }

    /**
     * setting name
     *
     * @param string $name
     * @return Application_Model_Gitosis_Repository
     */
    public function setName($name)
    {
        $name = trim((string) $name);
        if (!empty($name)) {
            $this->_name = $name;
        }
        return $this;
    }

    /**
     * setting description
     *
     * @param string $description
     * @return Application_Model_Gitosis_Repository
     */
    public function setDescription($description)
    {
        $description = trim((string) $description);
        if (!empty($description)) {
            $this->_description = $description;
        }
        return $this;
    }

    /**
     * setting owner
     *
     * the user can be identified by id, email or directly by
     * {@see Application_Model_Gitosis_User}.
     *
     * @param mixed $user
     * @return Application_Model_Gitosis_Repository
     */
    public function setOwner($user)
    {
        if ($user instanceof Application_Model_Gitosis_User) {
            if($user->getId()) {
                $this->_owner = $user;
            }
        } else {

            $owner = new Application_Model_Gitosis_User();
            if (intval($user) > 0) {
                $userId = intval($user);
                $owner->load($userId);
            } else {
                $owner->loadByMail($user);
            }

            if ($owner->getId()) {
                $this->_owner = $owner;
            }
        }
        return $this;
    }

    /**
     * adding a group to this repo
     *
     * @param int|Application_Model_Gitosis_Group $group
     * @param bool $isWriteable
     * @return Application_Model_Gitosis_Repository
     */
    public function addGroup($group, $isWriteable)
    {
        $isWriteable = (bool) $isWriteable;
        $groupId     = $this->_getGroupId($group);
        if ($groupId > 0) {
            $this->_groups[$groupId] = $isWriteable;
        }
        return $this;
    }

    /**
     * adding multiple groups to this repo
     *
     * The given array has to contain group ids or group model as index and the
     * writeable flag as value.
     *
     * @param array $groups
     * @return Application_Model_Gitosis_Repository
     */
    public function addGroups($groups)
    {
        if (is_array($groups)) {
            foreach ($groups as $group => $isWriteable) {
                $this->addGroup($group, $isWriteable);
            }
        }
        return $this;
    }

    /**
     * removing a group from this repo
     *
     * @param int|Application_Model_Gitosis_Group $group
     * @return Application_Model_Gitosis_Repository
     */
    public function removeGroup($group)
    {
        $groupId = $this->_getGroupId($group);
        if ($groupId > 0 && is_array($this->_groups) && array_key_exists($groupId, $this->_groups)) {
            unset($this->_groups[$groupId]);
        }
        return $this;
    }

    /**
     * removing multiple groups from this repo
     *
     * The given array has to contain group ids or group models.
     *
     * @param array $groups
     * @return Application_Model_Gitosis_Repository
     */
    public function removeGroups($groups)
    {
        if (is_array($groups)) {
            foreach ($groups as $group) {
                $this->removeGroup($group);
            }
        }
        return $this;
    }

    /**
     * return the access right of the group to this repository
     *
     * {@see REPO_RIGHTS_WRITEABLE}, {@see REPO_RIGHTS_READONLY},
     * {@see REPO_RIGHTS_NOTHING}
     *
     * @param int|Application_Model_Gitosis_Group $group
     * @return int
     */
    public function getGroupRight($group)
    {
        $groupId = $this->_getGroupId($group);
        if (is_array($this->_groups) && array_key_exists($groupId, $this->_groups)) {
            if ($this->_groups[$groupId]) {
                return self::REPO_RIGHTS_WRITEABLE;
            }
            return self::REPO_RIGHTS_READONLY;
        }
        return self::REPO_RIGHTS_NOTHING;
    }

    /**
     * loading data from database
     *
     * @param int $id
     * @return Application_Model_Gitosis_Repository
     */
    public function load($id = null)
    {
        if (!empty($id) && intval($id) > 0) {
            $this->setId($id);
        }

        $repoId = $this->getId();
        if (empty($repoId)) {
            $this->_id = null;
        } else {
            $this->_loadRepository($repoId);
            $this->_loadGroups();
        }

        return $this;
    }

    /**
     * getting select statement for paginator
     *
     * @return Zend_Db_Table_Select
     */
    public function getPaginatorSelect()
    {
        $repoModel = new Application_Model_Db_Gitosis_Repositories();
        $select = $repoModel->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
        $select->columns(
            array (
                new Zend_Db_Expr('gitosis_repository_id as id'),
                new Zend_Db_Expr('gitosis_repository_owner_id as owner_id'),
                new Zend_Db_Expr('gitosis_repository_name as name'),
                new Zend_Db_Expr('gitosis_repository_description as description'),
            )
        );

        return $select;
    }

    /**
     * setting data
     *
     * @param array
     */
    public function setData($data)
    {
        if (empty($data)) {
            return;
        }

        foreach ($data as $key => $value) {
            switch ($key) {
                case 'id':
                case 'gitosis_repository_id':
                    $this->setId($value);
                    break;

                case 'name':
                case 'gitosis_repository_name':
                    $this->setName($value);
                    break;

                case 'description':
                case 'gitosis_repository_description':
                    $this->setDescription($value);
                    break;

                case 'owner':
                case 'gitosis_repository_owner_id':
                    $this->setOwner($value);
                    break;
            }
        }
    }

    /**
     * getting all data in array
     *
     * @return array
     */
    public function getData()
    {
        return array(
            'id'            => $this->getId(),
            'name'          => $this->getName(),
            'description'   => $this->getDescription(),
            'owner'         => $this->getOwnerId()
        );
    }

    /**
     * storing or updating dataset
     *
     * @return bool
     */
    public function save()
    {
        if ($this->_saveRepository() && $this->_saveGroups()) {
            return true;
        }
        return false;
    }

    /**
     * deleting the dataset
     */
    public function delete($id = null)
    {
        if (!empty($id) && intval($id) > 0) {
            $this->setId($id);
        }

        $repoId = $this->getId();
        if (empty($repoId)) {
            return false;
        }

        Application_Model_Audit::getInstance()->log('deleted repository ' . $this->getName());
        $repoModel = new Application_Model_Db_Gitosis_Repositories();
        if ($repoModel->deleteItem($repoId)) {
            return true;
        }
        return false;
    }

    /**
     * loading repository data out of database
     *
     * @param int $id
     */
    protected function _loadRepository($id)
    {
        $repoModel = new Application_Model_Db_Gitosis_Repositories();
        $repo = $repoModel->getById($id);
        if (!empty($repo)) {
            $this->_originData = $repo;
            $this->setData($repo);
        }
    }

    /**
     * loading group data out of database
     */
    protected function _loadGroups()
    {
        $groups = $this->_getCurrentGroupIds();
        if (!empty($groups)) {
            $this->_groups = $groups;
        }
    }

    /**
     * storing repository in database
     *
     * @return bool
     */
    protected function _saveRepository()
    {
        $dbData = array(
            'gitosis_repository_owner_id'    => $this->getOwnerId(),
            'gitosis_repository_name'        => $this->getName(),
            'gitosis_repository_description' => $this->getDescription()
        );

        $repoId = $this->getId();
        $repoModel = new Application_Model_Db_Gitosis_Repositories();
        if (empty($repoId)) {
            $primId = $repoModel->insert($dbData);
            if ($primId > 0) {
                Application_Model_Audit::getInstance()->log('added repository ' . $this->getName());
                $this->_id = intval($primId);
                return true;
            }
        } elseif (is_array($this->_originData)) {
            foreach ($this->_originData as $fieldName => $fieldValue) {

                if ($fieldName == 'gitosis_repository_id') {
                    continue;
                }

                if ($fieldValue == $dbData[$fieldName]) {
                    unset($dbData[$fieldName]);
                }
            }

            if (empty($dbData)) {
                return true;
            } else {
                Application_Model_Audit::getInstance()->log('updated repository ' . $this->getName());
                return (bool) $repoModel->update($dbData, array('gitosis_repository_id = ?' => $repoId));
            }
        }
        return false;
    }

    /**
     * storing groups in database
     */
    protected function _saveGroups ()
    {
        $repoId = $this->getId();
        if (!empty($repoId)) {
            $currentGroups = array_keys($this->_getCurrentGroupIds());
            $actGroups     = (is_array($this->_groups) ? array_keys($this->_groups) : array());

            $addedGroups    = array_diff($actGroups, $currentGroups);
            $removedGroups  = array_diff($currentGroups, $actGroups);

            $groupRepoModel = new Application_Model_Db_Gitosis_GroupRights();
            $returnFlag = true;
            if (!empty($addedGroups)) {
                foreach ($addedGroups as $groupId) {
                    $added = $groupRepoModel->addRepoGroupRelation($repoId, $groupId, $this->_groups[$groupId]);
                    Application_Model_Audit::getInstance()->log('added group with id "' . $groupId . '" to repository ' . $this->getName());
                    if ($returnFlag && !$added) {
                        $returnFlag = false;
                    }

                }
            }

            if (!empty($removedGroups)) {
                foreach ($removedGroups as $groupId) {
                    Application_Model_Audit::getInstance()->log('removed group with id "' . $groupId . '" from repository ' . $this->getName());
                    $removed = $groupRepoModel->deleteRepoGroupRelation($repoId, $groupId);
                    if ($returnFlag && !$removed) {
                        $returnFlag = false;
                    }
                }
            }
            return $returnFlag;
        }
        return false;
    }

    /**
     * getting group id
     *
     * @param int|Application_Model_Gitosis_Group $group
     * @return int
     */
    protected function _getGroupId($group)
    {
        if ($group instanceof Application_Model_Gitosis_Group) {
            $groupId = $group->getId();
        } elseif (intval($group) > 0) {
            $groupId = intval($group);
        } else {
            $groupModel = new Application_Model_Db_Gitosis_Groups();
            $groupId = $groupModel->getByName($group);
        }

        if (intval($groupId) <= 0) {
            $groupId = -1;
        }

        return $groupId;
    }

    /**
     * getting current group ids from database
     *
     * @return array
     */
    protected function _getCurrentGroupIds()
    {
        $repoId   = $this->getId();
        $groupIds = array();


        if (!empty($repoId)) {
            $model = new Application_Model_Db_Gitosis_GroupRights();
            $dbRows = $model->getGroups($repoId);
            if (!empty($dbRows)) {
                foreach ($dbRows as $row) {
                    $groupId = intval($row['gitosis_group_id']);
                    $isWriteable = intval($row['is_writeable']);
                    $groupIds[$groupId] = (bool) $isWriteable;
                }
            }
        }
        return $groupIds;
    }
}