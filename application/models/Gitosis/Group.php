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
class Application_Model_Gitosis_Group implements MBit_Model_CrudInterface
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
     * this array containts ids of related users
     *
     * @var array
     */
    protected $_users = null;

    /**
     * this array containts ids of related repositories
     *
     * @var array
     */
    protected $_repositories = null;

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
    public function load($id = null)
    {
        if (!empty($id) && intval($id) > 0) {
            $this->_id = $id;
        }

        if (empty($this->_id)) {
            throw new InvalidArgumentException('group id has to be set or given via parameter');
        }

        $this->_loadGroup();
        $this->_loadRepositories();
        $this->_loadUsers();

        return $this;
    }

    /**
     * loading group by name
     *
     * @param string $name
     * @return Application_Model_Gitosis_Group
     * @throws InvalidArgumentException
     */
    public function loadByName($name)
    {
        $name = trim((string) $name);
        if (empty($name)) {
            throw new InvalidArgumentException('group name has to be set or given via parameter');
        }

        $dbModel = new Application_Model_Db_Gitosis_Groups();
        $groupId = $dbModel->getByName($name);

        if (intval($groupId) > 0) {
            $this->_id = $groupId;
            $this->_loadGroup();
            $this->_loadRepositories();
            $this->_loadUsers();
        }

        return $this;
    }

    /**
     * storing group to database
     *
     * @return bool
     */
    public function save()
    {
        $returnFlag = $this->_saveGroup();
        if ($returnFlag) {
            $this->_saveRepositories();
            $this->_saveUsers();
        }
        return $returnFlag;
    }

    /**
     * getting select statement for paginator
     *
     * @return Zend_Db_Table_Select
     */
    public function getPaginatorSelect()
    {
        $groupModel = new Application_Model_Db_Gitosis_Groups();
        $select = $groupModel->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
        $select->columns(
            array(
                new Zend_Db_Expr('gitosis_group_id as id'),
                new Zend_Db_Expr('gitosis_group_name as name'),
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
                    $this->setId($value);
                    break;

                case 'name':
                    $this->setName($value);
                    break;

                case 'user':
                case 'users':
                    if (is_array($value)) {
                        $this->addUsers($value);
                    } else {
                        $this->addUser($value);
                    }
                    break;

                case 'repository':
                case 'repositories':
                    if (is_array($value)) {
                        $this->addRepositories($value);
                    } else {
                        $this->addRepository($value);
                    }
                    break;
            }
        }
    }

    /**
     * getting all group data
     *
     * @return array
     */
    public function getData()
    {
        return array(
            'id'    => $this->getId(),
            'name'  => $this->getName()
        );
    }

    /**
     * deleting the group
     */
    public function delete($id = null)
    {
        if (!empty($id) && intval($id) > 0) {
            $this->_id = $id;
        }

        if (empty($this->_id)) {
            throw new InvalidArgumentException('no group id set, skipping removing of group');
        }

        $groupModel = new Application_Model_Db_Gitosis_Groups();
        return $groupModel->deleteItem($this->_id);
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
     * getting all users related to this group
     *
     * the return value is an array that contains objects of type
     * {@see Application_Model_Gitosis_User}
     *
     * @return array
     */
    public function getUsers()
    {
        if (empty($this->_users) || !is_array($this->_users)) {
            return null;
        }

        $returnValue = array();
        $counter = 0;
        foreach ($this->_users as $userId) {
            $returnValue[$counter] = $this->getUser($userId);
            $counter++;
        }

        return $returnValue;
    }

    /**
     * getting specific user
     *
     * @param int $userId
     * @return Application_Model_Gitosis_User
     */
    public function getUser($userId)
    {
        if (empty($this->_users) || !in_array($userId, $this->_users)) {
            return null;
        }

        $userModel = new Application_Model_Gitosis_User();
        $userModel->load($userId);

        return $userModel;
    }

    /**
     * getting specific repository
     *
     * @param int $repositoryId
     * @return Application_Model_Gitosis_Repository
     */
    public function getRepository($repositoryId)
    {
        if (empty($this->_repositories) || !array_key_exists($repositoryId, $this->_repositories)) {
            return null;
        }

        $repoModel = new Application_Model_Gitosis_Repository();
        $repoModel->load($repositoryId);

        return $repoModel;
    }

    /**
     * getting all related repositories
     *
     * the returned array contains objects of type
     * {@see Application_Model_Gitosis_Repository}.
     *
     * @return array
     */
    public function getRepositories()
    {
        if (empty($this->_repositories) || !is_array($this->_repositories)) {
            return null;
        }

        $repoIds = array_keys($this->_repositories);

        $returnValue = array();
        $counter = 0;
        foreach ($repoIds as $repo) {
            $returnValue[$counter] = $this->getRepository($repo);
            $counter++;
        }

        return $returnValue;
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
        if (is_string($name) && !empty($name)) {
            $this->_name = trim($name);
        }
        return $this;
    }

    /**
     * adding multiple users to group
     *
     * {@see addUser()}
     *
     * @param array $users
     * @return Application_Model_Gitosis_Group
     */
    public function addUsers($users)
    {
        if (!is_array($users)) {
            $users = array($users);
        }

        foreach ($users as $user) {
            $this->addUser($user);
        }

        return $this;
    }

    /**
     * adding user to group
     *
     * The given parameter can be an instance of the model
     * {@see Application_Model_Gitosis_User}, the mail address of the user or
     * the id of the user.
     *
     * @param Application_Model_Gitosis_User|int $user
     * @return Application_Model_Gitosis_Group
     */
    public function addUser($user)
    {
        $userId = $this->_getUserId($user);
        if (intval($userId) > 0) {
            $this->_users[] = $userId;
            sort($this->_users);
        }
        return $this;
    }

    /**
     * removing users from this group
     *
     * The first parameter has to be an array containing user ids or objects of
     * type {@see Application_Model_Gitosis_User}.
     *
     * @param array $user
     * @return Application_Model_Gitosis_Group
     */
    public function removeUsers($users)
    {
        if (!is_array($users)) {
            $users = array($users);
        }

        foreach ($users as $user) {
            $this->removeUser($user);
        }
        return $this;
    }

    /**
     * removing a user from this group
     *
     * @param int|Application_Model_Gitosis_User $user
     * @return Application_Model_Gitosis_Group
     */
    public function removeUser($user)
    {
        $userId = $this->_getUserId($user);
        if (intval($userId) > 0 && is_array($this->_users) && in_array($userId, $this->_users)) {
            foreach ($this->_users as $key => $value) {
                if ($value == $userId) {
                    unset($this->_users[$key]);
                }
            }
        }
        return $this;
    }

    /**
     * adding a repository to this group
     *
     * @param int|Application_Model_Gitosis_Repository $repository
     * @param bool $isWriteable
     * @return Application_Model_Gitosis_Group
     */
    public function addRepository($repository, $isWriteable = false)
    {
        $repoId = $this->_getRepositoryId($repository);
        if (intval($repoId) > 0) {
            $this->_repositories[$repoId] = (bool) $isWriteable;
        }
        return $this;
    }

    /**
     * adding repositories to this group
     *
     * The first parameter has to be an array containing repository ids or
     * objects of type {@see Application_Model_Gitosis_Repository} as the index
     * and the value has to be TRUE or FALSE (declaring if readonly).
     *
     * @param array $repository
     * @return Application_Model_Gitosis_Group
     */
    public function addRepositories($repositories)
    {
        if (!is_array($repositories)) {
            $repositories = array($repositories);
        }

        foreach ($repositories as $repository => $isWriteable) {
            $this->addRepository($repository, $isWriteable);
        }
        return $this;
    }

    /**
     * removing a repository from this group
     *
     * @param int|Application_Model_Gitosis_Repository $repository
     * @return Application_Model_Gitosis_Group
     */
    public function removeRepository($repository)
    {
        $repoId = $this->_getRepositoryId($repository);
        if (intval($repoId) > 0 && is_array($this->_repositories) && array_key_exists($repoId, $this->_repositories)) {
            unset($this->_repositories[$repoId]);
        }
        return $this;
    }

    /**
     * removing repositories from this group
     *
     * The first parameter has to be an array containing repository ids or
     * objects of type {@see Application_Model_Gitosis_Repository}.
     *
     * @param array $repository
     * @return Application_Model_Gitosis_Group
     */
    public function removeRepositories($repositories)
    {
        if (!is_array($repositories)) {
            $repositories = array($repositories);
        }

        foreach ($repositories as $repository) {
            $this->removeRepository($repository);
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
            throw new UnexpectedValueException('no group id given, cannot load group');
        }

        $groupModel = new Application_Model_Db_Gitosis_Groups();
        $groupData  = $groupModel->getById($this->_id);

        if (!empty($groupData)) {
            $this->_originData = $groupData;

            foreach ($groupData as $fieldName => $fieldValue) {
                switch ($fieldName) {
                    case 'gitosis_group_id':
                        $this->setId($fieldValue);
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
     * loading users
     *
     * @throws UnexpectedValueException
     */
    protected function _loadUsers()
    {
        $users = $this->_getDbUserIds();
        if (!empty($users)) {
            $this->_users = $users;
            sort($this->_users);
        }
    }

    /**
     * loading repositories
     *
     * @throws UnexpectedValueException
     */
    protected function _loadRepositories()
    {
        $this->_repositories = $this->_getDbRepositoryIds();
        if (empty($this->_repositories)) {
            $this->_repositories = null;
        }
    }

    /**
     * saving group
     */
    protected function _saveGroup()
    {
        $groupModel = new Application_Model_Db_Gitosis_Groups();
        $dbData = array('gitosis_group_name' => $this->getName());

        if (!$this->_id) {
            $primId = $groupModel->insert($dbData);
            if ($primId) {
                $this->_id = $primId;
                return true;
            }
        } else {
            foreach ($this->_originData as $fieldName => $fieldValue) {
                if ($dbData[$fieldName] == $fieldValue) {
                    unset($dbData[$fieldName]);
                }
            }

            if (empty($dbData)) {
                return true;
            } else {
                return (bool) $groupModel->update(
                    $dbData,
                    array('gitosis_group_id = ?' => $this->_id)
                );
            }
        }
        return false;
    }

    /**
     * saving repository changes
     */
    protected function _saveRepositories()
    {
        $groupId = $this->getId();
        if (empty($groupId)) {
            throw new UnexpectedValueException('no group id given, cannot save repositories');
        }

        if (!is_array($this->_repositories)) {
            $actualRepositories  = array();
        } else {
            $actualRepositories  = array_keys($this->_repositories);
        }
        $currentRepositories = array_keys($this->_getDbRepositoryIds());

        $addedRepos    = array_diff($actualRepositories, $currentRepositories);
        $removedRepos  = array_diff($currentRepositories, $actualRepositories);

        $groupRepoModel = new Application_Model_Db_Gitosis_GroupRights();
        if (!empty($removedRepos)) {
            foreach ($removedRepos as $repoId) {
                $groupRepoModel->deleteRepoGroupRelation($repoId, $groupId);
            }
            unset($repoId);
        }

        if (!empty($addedRepos)) {
            foreach ($addedRepos as $repoId) {
                $groupRepoModel->addRepoGroupRelation($repoId, $groupId, $this->_repositories[$repoId]);
            }
        }
    }

    /**
     * saving user changes
     */
    protected function _saveUsers()
    {
        $groupId = $this->getId();
        if (empty($groupId)) {
            throw new UnexpectedValueException('no group id given, cannot save users');
        }

        $actualUsers  = (is_array($this->_users) ? $this->_users : array());
        $currentUsers = $this->_getDbUserIds();

        $addedUsers   = array_diff($actualUsers, $currentUsers);
        $removedUsers = array_diff($currentUsers, $actualUsers);

        $groupUserModel = new Application_Model_Db_Gitosis_UsersGroups();
        if (!empty($removedUsers)) {
            foreach ($removedUsers as $userId) {
                $groupUserModel->removeUserFromGroup($groupId, $userId);
            }
            unset($userId);
        }

        if (!empty($addedUsers)) {
            foreach ($addedUsers as $userId) {
                $groupUserModel->addUserToGroup($groupId, $userId);
            }
        }
    }

    /**
     * getting the user id
     *
     * The given parameter can be an instance of {@see Application_Model_Gitosis_User}
     * or the user id.
     *
     * @return int
     */
    protected function _getUserId($user)
    {
        if ($user instanceof Application_Model_Gitosis_User) {
            $userId = $user->getId();
        } elseif (intval($user) > 0) {
            $userId = intval($user);
        } else {
            $dbModel = new Application_Model_Db_Gitosis_Users();
            $userId = $dbModel->getByEmail($user);
            if (!$userId) {
                $userId = -1;
            }
        }

        return $userId;
    }

    /**
     * getting the repository id
     *
     * The given parameter can be an instance of
     * {@see Application_Model_Gitosis_Repository} or the repository id.
     *
     * @return int
     */
    protected function _getRepositoryId($repo)
    {
        if ($repo instanceof Application_Model_Gitosis_Repository) {
            $repoId = $user->getId();
        } elseif (intval($repo) > 0) {
            $repoId = intval($repo);
        } else {
            $repoId = -1;
        }

        return $repoId;
    }

    /**
     * getting all repository ids out of database
     *
     * @return array
     */
    protected function _getDbRepositoryIds()
    {
        $repoIds = array();

        $groupId = $this->getId();
        if (empty($groupId)) {
            return null;
        }

        $groupRepoModel = new Application_Model_Db_Gitosis_GroupRights();
        $dbRows = $groupRepoModel->getRepositories($groupId);
        if (!empty($dbRows)) {
            foreach ($dbRows as $dbRow) {
                $repoIds[$dbRow['gitosis_repository_id']] = ($dbRow['is_writeable'] == 1 ? true : false);
            }
        }
        return $repoIds;
    }

    /**
     * getting all user ids out of database
     *
     * @return array
     */
    protected function _getDbUserIds()
    {
        $userIds = array();

        $groupId = $this->getId();
        if (empty($groupId)) {
            return array();
        }

        $groupUserModel = new Application_Model_Db_Gitosis_UsersGroups();
        $dbRows = $groupUserModel->getGroupUsers($groupId);
        if (!empty($dbRows)) {
            foreach ($dbRows as $row) {
                $userIds[] = $row['gitosis_user_id'];
            }
        }
        return $userIds;
    }
}