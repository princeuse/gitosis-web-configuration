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
class Application_Model_Db_Gitosis_Repositories extends MBit_Db_Table_Abstract
{
    /**
     * @var string
     */
    protected $_name = 'gitosis_repositories';

    /**
     * @var string
     */
    protected $_primary = 'gitosis_repository_id';

    /**
     * getting id of dataset
     *
     * if no dataset is found, false will be returned, otherwise the id.
     *
     * @param string $name
     * @return int
     */
    public function getByName ($name)
    {
        return $this->_getByField('gitosis_repository_name', $name);
    }
}
