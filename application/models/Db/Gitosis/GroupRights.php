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
 * @package   DB-Models
 */

/**
 * @category MB-it
 * @package  DB-Models
 */
class Application_Model_Db_Gitosis_GroupRights extends MBit_Db_Table_Abstract
{
    /**
     * @var string
     */
    protected $_name = 'gitosis_group_rights';

    /**
     * @var string
     */
    protected $_primary = 'gitosis_group_right_id';

    /**
     * checking if there is a permision for this group to this repository
     *
     * @param int $groupId
     * @param int $repoId
     * @return bool
     */
    public function exists($groupId, $repoId)
    {
        $row = $this->fetchRow(
            array(
                'gitosis_group_id = ?'      => $groupId,
                'gitosis_repository_id = ?' => $repoId,
            )
        );

        if (!empty($row)) {
            return true;
        }
        return false;
    }

    /**
     * checking if permision for this group to this repository is set
     *
     * @param int $groupId
     * @param int $repoId
     * @param true $isWriteable
     * @return bool
     */
    public function hasRights($groupId, $repoId, $isWriteable)
    {
        $row = $this->fetchRow(
            array(
                'gitosis_group_id = ?'      => $groupId,
                'gitosis_repository_id = ?' => $repoId,
                'is_writeable = ?'          => ($isWriteable ? 1 : 0)
            )
        );

        if (!empty($row)) {
            return true;
        }
        return false;
    }

    /**
     * getting all repositories for a group
     *
     * @param int $groupId
     * @return array
     */
    public function getRepositories($groupId)
    {
        $rows = $this->fetchAll(array('gitosis_group_id = ?' => $groupId));
        if ($rows->count() <= 0) {
            return null;
        }
        return $rows->toArray();
    }

    /**
     * getting all groups for a repository
     *
     * @param int $repoId
     * @return array
     */
    public function getGroups($repoId)
    {
        $rows = $this->fetchAll(array('gitosis_repository_id = ?' => $repoId));
        if ($rows->count() <= 0) {
            return null;
        }
        return $rows->toArray();
    }

    /**
     * removing relation between repository and group
     *
     * @param int $repoId
     * @param int $groupId
     * @return bool
     */
    public function deleteRepoGroupRelation($repoId, $groupId)
    {
        $flag = $this->delete(
            array(
                'gitois_group_id = ?'       => $groupId,
                'gitois_repository_id = ?'  => $repoId
            )
        );

        return (bool) $flag;
    }

    /**
     * adding relation between repository and group
     *
     * @param int $repoId
     * @param int $groupId
     * @param bool $isWriteable
     * @return bool
     */
    public function addRepoGroupRelation($repoId, $groupId, $isWriteable)
    {

        $isWriteable = ($isWriteable ? 1 : 0);

        $select = $this->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
        $select->where(
            array(
                'gitois_group_id = ?'       => $groupId,
                'gitois_repository_id = ?'  => $repoId
            )
        );

        $row = $this->fetchRow($select);
        if (empty($row)) {
            $row = $this->createRow();
        }

        $row->{'gitosis_repository_id'} = $repoId;
        $row->{'gitosis_group_id'} = $groupId;
        $row->{'is_writeable'} = $isWriteable;

        return (bool) $row->save();
    }
}
