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
class Application_Model_Admin_User implements MBit_Model_CrudInterface
{

    /**
     * @static string
     */
    const COOKIE_NAME = 'gitosis_web_configuration_login';

    /**
     * @var int
     */
    protected $_id = null;

    /**
     * @var string
     */
    protected $_login = null;

    /**
     * @var string
     */
    protected $_email = null;

    /**
     * @var string
     */
    protected $_password = null;

    /**
     * @var string
     */
    protected $_cookie = null;

    /**
     * @var array
     */
    protected $_originData = null;

    /**
     * login the user in via credentials
     *
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function loginWithCredentials($username, $password)
    {
        // Auth-Adapter
        $adapter = new Zend_Auth_Adapter_DbTable(Zend_Db_Table::getDefaultAdapter());
        $adapter->setTableName('admin_users')
                ->setIdentityColumn('admin_login')
                ->setCredentialColumn('admin_password')
                ->setCredentialTreatment('MD5(?)')
                ->setIdentity($username)
                ->setCredential($password);

        $auth = Zend_Auth::getInstance();
        $result = $auth->authenticate($adapter);

        if ($result->isValid()) {

            $storage = $auth->getStorage();
            $storage->write(
                $adapter->getResultRowObject(array('admin_id', 'admin_login'))
            );

            $userObject = $adapter->getResultRowObject('admin_id');
            $userId = $userObject->{'admin_id'};

            $this->load($userId);
            $this->setCookie(substr(md5(uniqid(rand(), true)), 0, 200));
            $this->save();

            $registry = Zend_Registry::getInstance();
            $registry->set('admin_user', $this);
            return true;
        }
        return false;
    }

    /**
     * login the user in via cookie
     *
     * @param string $cookie
     * @return bool
     */
    public function loginWithCookie($cookie)
    {
        $cookie = trim((string) $cookie);
        if (empty($cookie)) {
            return false;
        }

        $dataArray = explode(';', $cookie);
        if (!count($dataArray) == 2) {
            return false;
        }

        $this->setLogin(trim((string) $dataArray[0]));
        $this->setCookie(trim((string) $dataArray[1]));

        // Auth-Adapter
        $adapter = new Zend_Auth_Adapter_DbTable(Zend_Db_Table::getDefaultAdapter());
        $adapter->setTableName('admin_users')
                ->setIdentityColumn('admin_login')
                ->setCredentialColumn('admin_cookie')
                ->setIdentity($this->getLogin())
                ->setCredential($this->getCookie());

        $auth = Zend_Auth::getInstance();
        $result = $auth->authenticate($adapter);
        if ($result->isValid()) {

            $storage = $auth->getStorage();
            $storage->write(
                $adapter->getResultRowObject(array('admin_id', 'admin_login'))
            );

            $userObject = $adapter->getResultRowObject('admin_id');
            $userId = $userObject->{'admin_id'};
            $this->load($userId);

            $registry = Zend_Registry::getInstance();
            $registry->set('admin_user', $this);
            return true;
        }
        return false;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->_login;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->_email;
    }

    /**
     * @return string
     */
    public function getCookie()
    {
        return $this->_cookie;
    }

    /**
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->_password;
    }

    /**
     * generating user password
     *
     * @param int $length
     * @return string
     */
    public function generateNewPassword($length = 8)
    {
        $passwordChars = array_merge(range('0', '9'), range('a', 'z'), range('A', 'Z'));

        mt_srand((double)microtime()*1000000);
        for ($i = 1; $i <= (count($passwordChars)*2); $i++) {
            $swap = mt_rand(0,count($passwordChars)-1);
            $tmp = $passwordChars[$swap];
            $passwordChars[$swap] = $passwordChars[0];
            $passwordChars[0] = $tmp;
        }
        $password = substr(implode('',$passwordChars),0,$length);
        $this->setPassword($password);

        return $password;
    }

    /**
     * @param int $id
     * @return Application_Model_Admin_User
     */
    public function setId($id)
    {
        if (intval($id) > 0) {
            $this->_id = $id;
        }
        return $this;
    }

    /**
     * @param string $login
     * @return Application_Model_Admin_User
     */
    public function setLogin($login)
    {
        $login = trim((string) $login);
        if (!empty($login)) {
            $this->_login = $login;
        }
        return $this;
    }

    /**
     * @param string $email
     * @return Application_Model_Admin_User
     */
    public function setEmail($email)
    {
        $email = trim((string) $email);
        if (!empty($email)) {
            $this->_email = $email;
        }
        return $this;
    }

    /**
     * @param string $cookie
     * @return Application_Model_Admin_User
     */
    public function setCookie($cookie)
    {
        $cookie = trim((string) $cookie);
        if (!empty($cookie)) {
            $this->_cookie = $cookie;
        }
        return $this;
    }

    /**
     * @return Application_Model_Admin_User
     */
    public function unsetCookie()
    {
        $this->_cookie = null;
        return $this;
    }

    /**
     * @param string $password
     * @return Application_Model_Admin_User
     */
    public function setPassword($password)
    {
        $password = trim((string) $password);
        if (!empty($password)) {
            $this->_password = md5($password);
        }
        return $this;
    }

    /**
     * loading data
     * @param int $id
     * @return Application_Model_Admin_User
     */
    public function load($id = null)
    {
        if (intval($id) > 0) {
            $this->setId($id);
        }

        if ($this->getId() > 0) {

            $userModel = new Application_Model_Db_Users();
            $rows = $userModel->find($this->getId());

            if ($rows->count() == 1) {

                $data = $rows->current()->toArray();
                $this->_originData = $data;

                foreach ($data as $key => $value) {
                    switch ($key) {
                        case 'admin_id':
                            $this->setId($value);
                            break;

                        case 'admin_login':
                            $this->setLogin($value);
                            break;

                        case 'admin_email':
                            $this->setEmail($value);
                            break;

                        case 'admin_password':
                            $this->_password = $value;
                            break;

                        case 'admin_cookie':
                            $this->setCookie($value);
                            break;
                    }
                }

            } else {
                $this->_id = null;
            }
        }
        return $this;
    }

    /**
     * deleteing the admin user
     *
     * @param int $id
     * @return bool
     */
    public function delete($id = null)
    {
        if (!empty($id)) {
            $this->setId($id);
        }

        if (empty($this->_id)) {
            return false;
        }

        $model = new Application_Model_Db_Users();
        return $model->deleteItem($this->getId());
    }

    /**
     * getting admin user data as array
     *
     * @return array
     */
    public function getData()
    {
        return array(
            'id'        => $this->getId(),
            'login'     => $this->getLogin(),
            'email'     => $this->getEmail()
        );
    }

    public function getPaginatorSelect()
    {
        $user = new Application_Model_Db_Users();
        $select = $user->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
        $select->columns(
            array(
                new Zend_Db_Expr('admin_id as id'),
                new Zend_Db_Expr('admin_login as login'),
                new Zend_Db_Expr('admin_email as email')
            )
        );
        return $select;
    }

    /**
     * @param array $data
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

                case 'login':
                    $this->setLogin($value);
                    break;

                case 'password':
                    $this->setPassword($value);
                    break;

                case 'email':
                    $this->setEmail($value);
                    break;
            }
        }
    }

    /**
     * saving user changes
     */
    public function save()
    {
        $userModel = new Application_Model_Db_Users();

        $dbData = array(
            'admin_login'       => $this->getLogin(),
            'admin_password'    => $this->getPassword(),
            'admin_email'       => $this->getEmail(),
            'admin_cookie'      => $this->getCookie()
        );

        if (!($this->getId())) {
            $primId = $userModel->insert($dbData);
            if ($primId) {
                $this->_id = $primId;
                return true;
            }
        } else {
            foreach ($this->_originData as $fieldName => $fieldValue) {
                if (array_key_exists($fieldName, $dbData) &&
                    $dbData[$fieldName] == $fieldValue) {
                        unset($dbData[$fieldName]);
                }
            }

            if (empty($dbData)) {
                return true;
            } else {
                return (bool) $userModel->update(
                    $dbData,
                    array('admin_id = ?' => $this->getId())
                );
            }
        }
        return false;
    }
}