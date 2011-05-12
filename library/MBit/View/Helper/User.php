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
 * @package   Lib
 */

/**
 * @category MB-it
 * @package  Lib
 */
class MBit_View_Helper_User extends Zend_View_Helper_Abstract
{
    /**
     * constructor
     *
     * @return MBit_View_Helper_User
     */
    public function user()
    {
        return $this;
    }

    /**
     * getting user name by id
     *
     * @param int $id
     * @return string
     */
    public function getById($id)
    {
        $model = new Application_Model_Db_Gitosis_Users();
        $userRow = $model->getById($id);
        if (empty($userRow)) {
            return "unbekannt";
        }
        return $this->_escape($userRow['gitosis_user_name']);
    }

    /**
     * checking if user exists in group
     * @param int $groupId
     * @param int $userId
     * @return bool
     */
    public function isInGroup ($groupId, $userId)
    {
        $model = new Application_Model_Db_Gitosis_UsersGroups();
        return $model->userIsInGroup($groupId, $userId);
    }

    /**
     * remove not allowed chars
     *
     * {@see Zend_View_Abstract::escape()}
     *
     * @param string $text
     * @return string
     */
    protected function _escape($text)
    {
        $text = (string) $text;

        $view = Zend_Layout::getMvcInstance()->getView();
        return $view->escape($text);
    }
}
