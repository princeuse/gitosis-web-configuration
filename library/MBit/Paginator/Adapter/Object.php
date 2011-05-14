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
class MBit_Paginator_Adapter_Object extends Zend_Paginator_Adapter_DbTableSelect
{
    /**
     * @var string
     */
    protected $_objectName = null;

    public function setObjectName($name)
    {
        if (!empty($name)) {
            if (is_object($name)) {
                $this->_objectName = get_class($name);
            } else {
                $this->_objectName = trim((string) $name);
            }
        }
        return $this;
    }

    public function getObjectName()
    {
        return $this->_objectName;
    }

    public function getItems($offset, $itemCountPerPage)
    {
        $returnValue = array();
        $counter = 0;

        $items = parent::getItems($offset, $itemCountPerPage);
        foreach ($items as $item) {
            $returnValue[$counter] = new $this->_objectName;
            $returnValue[$counter]->setData($item);

            $counter++;
        }
        return $returnValue;
    }
}

?>
