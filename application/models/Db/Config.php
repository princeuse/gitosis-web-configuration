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
class Application_Model_Db_Config extends Zend_Db_Table_Abstract
{
    /**
     * @var string
     */
    protected $_name = 'gitosis_web_configuration';

    /**
     * @var string
     */
    protected $_primary = 'config_id';

    /**
     * storing configuration to database
     *
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function save ($key, $value)
    {
        $key   = (string) $key;
        $value = (string) $value;

        if (empty($key) || empty($value)) {
            return false;
        }

        $row = $this->fetchRow(array(
           'config_name = ?'    => $key,
        ));

        if (empty($row)) {
            return (bool) $this->insert(
                array(
                    'config_name'   => $key,
                    'config_value'  => $value
                )
            );
        } elseif ($row->{'config_value'} !== $value) {
            return (bool) $this->update(
                array(
                    'config_value'  => $value
                ),
                array (
                    'config_name = ?' => $key
                )
            );
        }
        return false;
    }

}
