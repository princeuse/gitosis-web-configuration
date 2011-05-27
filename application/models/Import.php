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
    const IMPORT_OK                     = 0;
    const IMPORT_FILE_NOT_FOUND         = 1;
    const IMPORT_FILE_WRONG_FORMAT      = 2;
    const IMPORT_NO_DATA                = 3;
    const IMPORT_NO_GROUPS              = 4;
    const IMPORT_NO_REPOS               = 5;
    const IMPORT_NO_USERS               = 6;

    /**
     * constant for session storage
     */
    const SESSION_NAMESPACE             = 'gitosis_import_config';

    /**
     * @var string
     */
    protected $_file = null;

    /**
     * @var array
     */
    protected $_config = null;

    /**
     * @var array
     */
    protected $_users = null;

    /**
     * @var array
     */
    protected $_groups = null;

    /**
     * @var array
     */
    protected $_repositories = null;

    /**
     * @var array
     */
    protected $_permissions = null;

    /**
     * setting file for import
     *
     * @param string $file
     * @return Application_Model_Import
     */
    public function setFile($file)
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
     *
     * @return int
     */
    public function import()
    {
        $flag = $this->_readConfigFile();
        if ($flag !== self::IMPORT_OK) {
            return $flag;
        }

        $flag = $this->_parseConfig();
        return $flag;
    }

    /**
     * getting imported users
     *
     * @return array
     */
    public function getUsers()
    {
        if (empty($this->_users)) {
            $this->loadFromSession();
        }
        return $this->_users;
    }

    /**
     * getting imported repositories
     *
     * @return array
     */
    public function getRepositories()
    {
        if (empty($this->_repositories)) {
            $this->loadFromSession();
        }
        return $this->_repositories;
    }

    /**
     * getting imported groups
     *
     * @return array
     */
    public function getGroups()
    {
        if (empty($this->_groups)) {
            $this->loadFromSession();
        }
        return $this->_groups;
    }

    /**
     * getting imported permissions
     *
     * @return array
     */
    public function getPermissions()
    {
        if (empty($this->_permissions)) {
            $this->loadFromSession();
        }
        Zend_Debug::dump($this->_permissions, '$this->_permissions', true);
        return $this->_permissions;
    }

    /**
     * storing class variables to session
     */
    public function saveToSession()
    {
        $session = new Zend_Session_Namespace(self::SESSION_NAMESPACE);
        foreach (get_class_vars(get_class($this)) as $name => $value) {
            $session->{$name} = serialize($this->{$name});
        }
    }

    /**
     * loading from session
     */
    public function loadFromSession()
    {
        $session = new Zend_Session_Namespace(self::SESSION_NAMESPACE);
        foreach (get_class_vars(get_class($this)) as $name => $value) {
            $this->{$name} = unserialize($session->{$name});
        }
    }

    /**
     * unsetting all session data
     */
    public function removeFromSession()
    {
        $session = new Zend_Session_Namespace(self::SESSION_NAMESPACE);
        $session->unsetAll();
    }

    /**
     * getting error message to given error id
     *
     * @param int $errorId
     * @return string
     */
    public function getErrorMessage($errorId)
    {
        switch ($errorId) {
            case self::IMPORT_FILE_NOT_FOUND:
                return 'Die Import-Datei wurde nicht gefunden';

            case self::IMPORT_FILE_WRONG_FORMAT:
                return 'Das Format der Import-Datei ist fehlerhaft.';

            case self::IMPORT_NO_DATA:
                return 'Die importierte Datei enthielt keine Daten.';

            case self::IMPORT_NO_GROUPS:
                return 'Die importierte Datei enthielt keine Gruppen.';

            case self::IMPORT_NO_REPOS:
                return 'Die importierte Datei enthielt keine Repositories.';

            case self::IMPORT_NO_USERS:
                return 'Die importierte Datei enthielt keine Benutzer.';

            case self::IMPORT_OK:
                return 'Der Import war erfolgreich.';

            default:
                Zend_Registry::get('Zend_Log')->error('Beim Import einer Konfiguration wurde die nicht bekannte Fehler-ID "' . $errorId . '" übergeben');
                return 'Es trat ein unbekannter Fehler auf.';
        }
    }

    /**
     * reading import file
     *
     * this function returns one of the constants declared to identify status
     * of import
     *
     * @return int
     */
    protected function _readConfigFile()
    {

        if (empty($this->_file) || !file_exists($this->_file)) {
            return self::IMPORT_FILE_NOT_FOUND;
        }

        $gitosisConf = parse_ini_file($this->_file, true);
        if (!empty($gitosisConf) && array_key_exists('gitosis', $gitosisConf)) {
            unset($gitosisConf['gitosis']);
            $this->_config = $gitosisConf;
            return self::IMPORT_OK;
        }
        return self::IMPORT_FILE_WRONG_FORMAT;
    }

    /**
     * parsing configuration
     *
     * this function returns one of the constants declared to identify status
     * of import
     *
     * @return int
     */
    protected function _parseConfig()
    {
        foreach ($this->_config as $section => $values) {
            if (strpos($section, 'group') === 0) {
                $group = trim(str_replace('group', '', $section));
                if (empty($group)) {
                    continue;
                }

                if (!array_key_exists('members', $values)) {
                    continue;
                }
                $users = explode(' ', $values['members']);
                foreach ($users as $user) {
                    $user = trim((string) $user);
                    if (!empty($user)) {
                        $this->_users[$user] = array();
                        $this->_groups[$group][] = $user;
                    }
                }

                if (array_key_exists('writable', $values)) {
                    $this->_permissions[] = array(
                        'group' => $group,
                        'repos' => explode(' ', $values['writable']),
                        'write' => true
                    );
                }

                if (array_key_exists('readonly', $values)) {
                    $this->_permissions[] = array(
                        'group' => $group,
                        'repos' => explode(' ', $values['readonly']),
                        'write' => false
                    );
                }
            } elseif (strpos($section, 'repo') === 0) {
                $repo = trim(str_replace('repo', '', $section));
                if (empty($repo)) {
                    continue;
                }

                $description = null;
                $owner = null;

                if (array_key_exists('description', $values)) {
                    $description = trim((string) $values['description']);
                    if (empty($description)) {
                        $description = null;
                    }
                }

                if (array_key_exists('owner', $values)) {
                    $owner = trim((string) $values['owner']);
                    if (empty($owner)) {
                        $owner = null;
                    }
                }

                $this->_repositories[$repo] = array (
                    'description'   => $description,
                    'owner'         => $owner
                );
            }
        }

        if (empty($this->_groups) && empty($this->_repositories) && empty($this->_users)) {
            return self::IMPORT_NO_DATA;
        } elseif (empty($this->_groups)) {
            return self::IMPORT_NO_GROUPS;
        } elseif (empty($this->_repositories)) {
            return self::IMPORT_NO_REPOS;
        } elseif (empty($this->_users)) {
            return self::IMPORT_NO_USERS;
        }
        return self::IMPORT_OK;
    }
}
