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
interface MBit_Model_CrudInterface
{
    /**
     * getting select statement for paginator
     *
     * @return Zend_Db_Table_Select
     */
    public function getPaginatorSelect();

    /**
     * setting data
     *
     * @param array
     */
    public function setData($data);

    /**
     * getting all data in array
     *
     * @return array
     */
    public function getData();

    /**
     * storing or updating dataset
     */
    public function save();

    /**
     * deleting the dataset
     */
    public function delete();
}