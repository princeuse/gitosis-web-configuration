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
 * CRUD for database data
 *
 * @category MB-it
 * @package  Lib
 */
abstract class MBit_Db_Table_Abstract extends Zend_Db_Table_Abstract
{
    /**
     * creating data
     *
     * @param array $data
     * @return bool
     */
    public function createItem ($data)
    {
        $rowsCreated = $this->insert($data);
        if ($rowsCreated > 0) {
            return true;
        }
        return false;
    }

    /**
     * deleting data
     *
     * @param int $id
     * @return bool
     */
    public function deleteItem ($id)
    {
        $rowsDeleted = $this->delete($this->_getPrimaryKeyColumnName() . '=' . $id);
        if ($rowsDeleted == 1) {
            return true;
        }
        return false;
    }

    /**
     * update data
     *
     * @param  int   $id
     * @param  array $data
     * @return bool
     */
    public function editItem ($id, $data)
    {
        if ($id > 0 && !empty($data)) {
            $rowsUpdated = $this->update(
                $data,
                $this->_getPrimaryKeyColumnName() . '=' . $id
            );

            if ($rowsUpdated == 1) {
                return true;
            }
        }
        return false;
    }

    /**
     * getting data by id
     *
     * @param int $id
     * @return array
     */
    public function getById ($id)
    {
        $row = $this->fetchRow($this->_getPrimaryKeyColumnName() . '= ' . $id);
        if (empty($row)) {
            return null;
        }
        return $row->toArray();
    }

    /**
     * getting name of primary key
     *
     * @return string
     */
    protected function _getPrimaryKeyColumnName ()
    {
        $primary = $this->info(Zend_Db_Table::PRIMARY);
        if (is_array($primary)) {
            return $primary[1];
        }
        return $primary;
    }

    /**
     * getting id of dataset by field
     *
     * if no dataset is found, false will be returned, otherwise the id.
     *
     * @param string $name
     * @return int
     */
    protected function _getByField($fieldName, $value)
    {
        $row = $this->fetchRow(array($fieldName . ' = ?' => $value));

        if (empty($row) || intval($row->{$this->_getPrimaryKeyColumnName()}) <= 0) {
            return false;
        }
        return intval($row->{$this->_getPrimaryKeyColumnName()});
    }
}