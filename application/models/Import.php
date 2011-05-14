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
class Application_Model_Import
{
    /**
     * constants defining error messages
     */
    const IMPORT_FILE_NOT_FOUND     = 1;
    const IMPORT_FILE_WRONG_FORMAT  = 2;
    const IMPORT_FILE_SUCCESS       = 3;

    /**
     * patterns for regular expressions
     */
    const PATTERN_GROUPS            = '/^\[group[\s]*([a-z0-9-]*)\]$/i';
    const PATTERN_REPOSITORY        = '/^\[repo[\s]*([a-z0-9-]*)\]$/i';
    const PATTERN_MEMBERS           = '/^members[\s]*=([a-z0-9-\.\@\s]*)$/i';
    const PATTERN_OWNER             = '/^owner[\s]*=([a-z0-9-\.\@\s]*)$/i';
    const PATTERN_DESCRIPTION       = '/^description[\s]*=([a-z0-9- "]*)$/i';
    const PATTERN_REPO_IN_GROUP     = '/^(readonly|writable)[\s]*=([a-z0-9-\s]*)$/i';

    /**
     * constants for storing data
     */
    const DATA_KEY_GROUPS            = 'GROUPS';
    const DATA_KEY_REPOS             = 'REPOS';
    const DATA_KEY_USERS             = 'USERS';
    const DATA_KEY_MAPPING           = 'REPOS_IN_GROUPS';
    const DATA_KEY_ERRORS            = 'ERRORS';

    /**
     * @var string
     */
    protected $_file = null;

    /**
     * @param array
     */
    protected $_data = null;

    /**
     * @param array
     */
    protected $_stats = null;

    /**
     * setting file for import
     *
     * @param string $file
     * @return Application_Model_Import
     */
    public function setFile ($file)
    {
        if (!empty($file) && file_exists($file)) {
            $this->_file = $file;
        }
        return $this;
    }

    /**
     * returning file for import
     *
     * if first param is true, file with path is returned. If first param is
     * false, only the filename is returned.
     *
     * @param bool $fullyQualified
     * @return string
     */
    public function getFile($fullyQualified = true)
    {
        if (empty($this->_file)) {
            return null;
        }

        if (!$fullyQualified) {
            return basename($this->_file);
        }

        return $this->_file;
    }

    /**
     * import of file
     */
    public function import ()
    {
        $this->_readConfigFile();

        $errors = array();

        if (!$this->_addGroupsToDb()) {
            $errors[] = 'Es trat ein Fehler beim Speichern der Gruppen auf, bitte prüfen Sie, ob alle Daten korrekt übernommen wurden.';
        }

        if (!$this->_addUsersToDb()) {
            $errors[] = 'Es trat ein Fehler beim Speichern der Benutzer auf, bitte prüfen Sie, ob alle Daten korrekt übernommen wurden.';
        }

        if (!$this->_addReposToDb()) {
            $errors[] = 'Es trat ein Fehler beim Speichern der Repositories auf, bitte prüfen Sie, ob alle Daten korrekt übernommen wurden.';
        }

        if (!$this->_addMappingRepoGroupUserToDb()) {
            $errors[] = 'Es trat ein Fehler beim Verknüpfen der Benutzer, Berechtigungen und Repositories auf, bitte prüfen Sie, ob alle Daten korrekt übernommen wurden.';
        }

        $returnErrors = array();
        $importErrors = $this->_getData(self::DATA_KEY_ERRORS);
        if (!empty($importErrors)) {
            if (!is_array($importErrors)) {
                $importErrors = array ($importErrors);
            }

            $returnErrors = $importErrors;
        }

        if (!empty($errors)) {
            if (!is_array($errors)) {
                $errors = array ($errors);
            }

            $returnErrors = array_merge($returnErrors, $errors);
        }
        return $returnErrors;
    }

    /**
     * getting ids of imported users
     *
     * @return array
     */
    public function getImportedUsers ()
    {
        return $this->_getData(self::DATA_KEY_USERS);
    }

    /**
     * reading import file and calling setters for storing data of groups,
     * users, repositories aso.
     * 
     * @return type
     */
    protected function _readConfigFile ()
    {

        if (empty($this->_file) || !file_exists($this->_file)) {
            return self::IMPORT_FILE_NOT_FOUND;
        }

        $groups = array();
        $users  = array();
        $repos  = array();
        $userToGroups  = array();

        $belongsTo = null;
        $errors    = array();

        $isFirst = true;
        $fileHandle = fopen($this->_file, 'r');
        while (false !== ($line = fgets($fileHandle))) {
            $line = trim($line);

            if ($isFirst && $line !== '[gitosis]') {
                return self::IMPORT_FILE_WRONG_FORMAT;
            } elseif ($isFirst && $line == '[gitosis]') {
                $isFirst = false;
                continue;
            } elseif (empty($line)) {
                continue;
            }

            if (preg_match(self::PATTERN_GROUPS, $line)) {

                $group = $this->_getGroup($line);
                if (empty($group)) {
                    continue;
                }
                $belongsTo = $group;
                $groups[] = $group;
                continue;

            } elseif (preg_match(self::PATTERN_REPOSITORY, $line)) {

                $repo = $this->_getRepository($line);
                if (empty($repo)) {
                    continue;
                }
                $belongsTo    = $repo;
                $repos[$repo] = array('desc' => '', 'owner' => '');
                continue;

            } elseif (preg_match(self::PATTERN_MEMBERS, $line)) {

                $members = $this->_getMembers($line);
                if (empty($belongsTo) && !empty($members)) {
                    $errors[] = 'Setzen der Mitglieder "'
                              . implode(', ', $members)
                              . '" gescheitert, da keine zugehörige Gruppe gefunden wurde.';
                } elseif (empty($members)) {
                    continue;
                }

                $users = array_merge($users, $members);
                $userToGroups[$belongsTo]['users'] = $members;
                array_unique($users);
                continue;

            } elseif (preg_match(self::PATTERN_REPO_IN_GROUP, $line)) {

                $reposInGroup = $this->_getRepoInGroup($line);
                if (empty($belongsTo) && !empty($reposInGroup)) {
                    $errors[] = 'Setzen der Repositories "'
                              . implode(', ', $reposInGroup['repos'])
                              . '" gescheitert, da keine zugehörige Gruppe gefunden wurde.';
                } elseif (empty($reposInGroup)) {
                    continue;
                }
                $userToGroups[$belongsTo]['rights'] = $reposInGroup;
                continue;

            } elseif (preg_match(self::PATTERN_DESCRIPTION, $line)) {

                $desc = $this->_getRepositoryDescription($line);
                if (empty($belongsTo) && !empty($desc)) {
                    $errors[] = 'Setzen der Beschreibung "'
                              . $desc
                              . '" gescheitert, da kein zugehöriges Repository gefunden wurde.';
                } elseif (empty($desc)) {
                    continue;
                }

                $repos[$belongsTo]['desc'] = $desc;
                continue;

            } elseif (preg_match(self::PATTERN_OWNER, $line)) {

                $owner = $this->_getOwner($line);
                if (empty($belongsTo) && !empty($owner)) {
                    $errors[] = 'Setzen des Besitzers "'
                              . $owner
                              . '" gescheitert, da kein zugehöriges Repository gefunden wurde.';
                } elseif (empty($owner)) {
                    continue;
                }

                $repos[$belongsTo]['owner'] = $owner;
                continue;
            }
        }

        $this->_setData(self::DATA_KEY_GROUPS, $groups);
        $this->_setData(self::DATA_KEY_MAPPING, $userToGroups);
        $this->_setData(self::DATA_KEY_REPOS, $repos);
        $this->_setData(self::DATA_KEY_USERS, $users);

        if (!empty($errors)) {
            $this->_setData(self::DATA_KEY_ERRORS, $errors);
        }

        return self::IMPORT_FILE_SUCCESS;
    }

    /**
     * extracting group name out of config line
     *
     * @param string $configLine
     * @return string
     */
    protected function _getGroup($configLine)
    {
        return $this->_getMatch(self::PATTERN_GROUPS,$configLine);
    }

    /**
     * extracting repository name out of config line
     *
     * @param string $configLine
     * @return string
     */
    protected function _getRepository($configLine)
    {
        return $this->_getMatch(self::PATTERN_REPOSITORY,$configLine);
    }

    /**
     * extracting members of a group out of config line
     *
     * @param string $configLine
     * @return array
     */
    protected function _getMembers($configLine)
    {
        $members = $this->_getMatch(self::PATTERN_MEMBERS, $configLine);
        if (!empty($members)) {
            $members = explode(' ', $members);
        }

        if (empty($members)) {
            $members = array();
        }
        return $members;
    }

    /**
     * extracting repositories in a group out of config line
     *
     * @param string $configLine
     * @return array
     */
    protected function _getRepoInGroup($configLine)
    {
        $matches = array();

        $reposInGroup = array();
        $isWriteable  = false;

        preg_match(self::PATTERN_REPO_IN_GROUP, $configLine, $matches);
        if (count($matches) !== 3) {
            return null;
        }

        if ($matches[1] == 'writable') {
            $isWriteable = true;
        }

        $reposInGroup = explode(' ', trim($matches[2]));

        if (empty($reposInGroup)) {
            return null;
        }

        return array(
            'isWriteable' => intval($isWriteable),
            'repos'       => $reposInGroup
        );
    }

    /**
     * extracting owner of a repository out of config line
     *
     * @param string $configLine
     * @return string
     */
    protected function _getOwner($configLine)
    {
        return $this->_getMatch(self::PATTERN_OWNER, $configLine);
    }

    /**
     * extracting description of a repository out of config line
     *
     * @param string $configLine
     * @return string
     */
    protected function _getRepositoryDescription($configLine)
    {
        $description = $this->_getMatch(self::PATTERN_DESCRIPTION, $configLine);
        if (empty($description)) {
            return null;
        }

        return str_replace(array('"'),array(''),$description);
    }

    /**
     * storing groups to db
     *
     * @return bool
     */
    protected function _addGroupsToDb ()
    {
        $groups = $this->_getData(self::DATA_KEY_GROUPS);
        if (empty($groups)) {
            return false;
        }

        $groups       = array_unique($groups);
        $groupWithIds = array();
        $model  = new Application_Model_Db_Gitosis_Groups();
        foreach ($groups as $group) {

            $id = $model->getByName($group);
            if (!$id) {
                $id = $model->insert(array('gitosis_group_name' => $group));
            }

            if (intval($id) > 0) {
                $groupWithIds[$id] = $group;
            }
        }

        $this->_setData(self::DATA_KEY_GROUPS, $groupWithIds);
        return true;
    }

    /**
     * storing users to db
     *
     * @return bool
     */
    protected function _addUsersToDb ()
    {
        $users = $this->_getData(self::DATA_KEY_USERS);
        if (empty($users)) {
            return false;
        }

        $users        = array_unique($users);
        $usersWithIds = array();
        $model  = new Application_Model_Db_Gitosis_Users();
        foreach ($users as $user) {

            $id = $model->getByEmail($user);
            if (!$id) {
                $id = $model->insert(
                    array(
                        'gitosis_user_email'    => $user,
                        'gitosis_user_ssh_key'  => 'none',
                        'gitosis_user_name'     => 'none'
                    )
                );
            }

            if (intval($id) > 0) {
                $usersWithIds[$id] = $user;
            }
        }

        $this->_setData(self::DATA_KEY_USERS, $usersWithIds);
        return true;
    }

    /**
     * storing repositories to db
     *
     * @return bool
     */
    protected function _addReposToDb()
    {
        $reposWithId = array();
        $repos = $this->_getData(self::DATA_KEY_REPOS);
        if (empty($repos)) {
            return false;
        }

        $modelUsers = new Application_Model_Db_Gitosis_Users();
        $modelRepos = new Application_Model_Db_Gitosis_Repositories();

        foreach ($repos as $repo => $data) {

            $repoId = $modelRepos->getByName($repo);
            if (!($repoId === false)) {
                continue;
            }

            $userId = $modelUsers->getByEmail($data['owner']);
            if ($userId === false) {
                $userId = null;
            }

            $description = $data['desc'];
            if (empty($description)) {
                $description = null;
            }

            $dbData = array(
                'gitosis_repository_owner_id'       => $userId,
                'gitosis_repository_name'           => $repo,
                'gitosis_repository_description'    => $description
            );

            $repoId = $modelRepos->insert($dbData);
            if ($repoId > 0) {
                $reposWithId[$repoId] = $repo;
            }
        }

        $this->_setData(self::DATA_KEY_REPOS, $reposWithId);
        return true;
    }

    /**
     * storing user to group mapping and group to repo mapping
     *
     * @return bool
     */
    protected function _addMappingRepoGroupUserToDb ()
    {
        $data = $this->_getData(self::DATA_KEY_MAPPING);
        if (empty($data)) {
            return false;
        }

        foreach ($data as $group => $mapping) {
            if (!$this->_mapUsersToGroup($group, $mapping['users']) ||
                !$this->_mapGroupsToRepos($group, $mapping['rights'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * add users to group
     *
     * @param  int   $group
     * @param  array $users
     * @return bool
     */
    protected function _mapUsersToGroup ($group, $users)
    {
        if (empty($group) || empty($users)) {
            return false;
        }

        $modelGroup     = new Application_Model_Db_Gitosis_Groups();
        $modelUser      = new Application_Model_Db_Gitosis_Users();
        $modelUserGroup = new Application_Model_Db_Gitosis_UsersGroups();

        $groupId = $modelGroup->getByName($group);
        if ($groupId <= 0) {
            return false;
        }

        foreach ($users as $user) {
            $userId = $modelUser->getByEmail($user);
            if ($userId > 0) {
                $modelUserGroup->addUserToGroup($groupId, $userId);
            }
        }

        return true;
    }

    /**
     * map groups to repos and set rights
     *
     * @param  int   $group
     * @param  array $rights
     * @return bool
     */
    protected function _mapGroupsToRepos($group, $rights)
    {
        if (empty($group) || empty($rights)) {
            return false;
        }

        if (!is_array($rights)) {
            return false;
        }

        $modelGroup       = new Application_Model_Db_Gitosis_Groups();
        $modelRepo        = new Application_Model_Db_Gitosis_Repositories();
        $modelGroupRights = new Application_Model_Db_Gitosis_GroupRights();

        $groupId = $modelGroup->getByName($group);
        if ($groupId <= 0) {
            return false;
        }

        $isWriteable = intval($rights['isWriteable']);
        $repos       = $rights['repos'];

        foreach ($repos as $repo) {

            $repoId = $modelRepo->getByName($repo);
            if ($repoId > 0) {

                if ($modelGroupRights->exists($groupId, $repoId) &&
                    $modelGroupRights->hasRights($groupId, $repoId, $isWriteable)) {
                } elseif ($modelGroupRights->exists($groupId, $repoId) &&
                          !$modelGroupRights->hasRights($groupId, $repoId, $isWriteable)) {

                    $rightsId = $modelGroupRights->update(
                        array ('is_writeable'          => $isWriteable),
                        array(
                            'gitosis_group_id = ?'      => $groupId,
                            'gitosis_repository_id = ?' => $repoId,
                        )
                    );
                } else {
                    $rightsId = $modelGroupRights->insert(
                        array(
                            'gitosis_group_id'      => $groupId,
                            'gitosis_repository_id' => $repoId,
                            'is_writeable'          => $isWriteable
                        )
                    );
                }
            }

        }

        return true;
    }

    /**
     * getting preg match
     *
     * @param string $pattern
     * @param string $configLine
     * @return string
     */
    protected function _getMatch($pattern, $configLine, $matchCount = 2)
    {
        $matches = array();
        preg_match($pattern, $configLine, $matches);
        if (count($matches) !== $matchCount) {
            return null;
        }
        return trim($matches[$matchCount-1]);
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    protected function _setData($key, $value)
    {

        if (!empty($key)) {

            if (empty($this->_data)) {
                $this->_data = array();
            }

            if (empty($value)) {
                try {
                    unset($this->_data[$key]);
                } catch (Exception $e) { }
            } else {
                $this->_data[$key] = $value;
            }
        }
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function _getData($key)
    {
        if (is_array($this->_data) && array_key_exists($key, $this->_data)) {
            return $this->_data[$key];
        }
        return null;
    }
}