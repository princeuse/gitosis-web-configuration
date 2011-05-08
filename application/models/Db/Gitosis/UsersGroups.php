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
class Application_Model_Db_Gitosis_UsersGroups extends MBit_Db_Table_Abstract
{
    /**
     * @var string
     */
    protected $_name = 'gitosis_user_groups';

    /**
     * @var string
     */
    protected $_primary = 'gitosis_user_group_id';

    /**
     * adding a user to a group
     *
     * @param int $groupId
     * @param int $userId
     * @return bool
     */
    public function addUserToGroup($groupId, $userId)
    {
        $groupId = intval($groupId);
        $userId  = intval($userId);
        if (empty($userId) || empty($groupId)) {
            return false;
        }

        if ($this->userIsInGroup($groupId, $userId)) {
            return true;
        }

        $primKey = $this->insert(
            array(
                'gitosis_group_id' => $groupId,
                'gitosis_user_id'  => $userId
            )
        );

        if ($primKey > 0) {
            return true;
        }
        return false;
    }

    /**
     * removing a user from a group
     *
     * @param int $groupId
     * @param int $userId
     * @return bool
     */
    public function removeUserFromGroup($groupId, $userId)
    {
        $groupId = intval($groupId);
        $userId  = intval($userId);
        if (empty($userId) || empty($groupId)) {
            return false;
        }

        if (!$this->userIsInGroup($groupId, $userId)) {
            return true;
        }

        $rowsDeleted = $this->delete(array('gitosis_group_id = ?' => $groupId, 'gitosis_user_id = ?' => $userId));
        if ($rowsDeleted == 1) {
            return true;
        }
        return false;
    }

    /**
     * checking if user exists in group
     *
     * @param int $groupId
     * @param int $userId
     * @return bool
     */
    public function userIsInGroup($groupId, $userId)
    {
        $rows = $this->fetchAll(array('gitosis_group_id = ?' => $groupId, 'gitosis_user_id = ?' => $userId));
        if ($rows->count() > 0) {
            return true;
        }
        return false;
    }
}
