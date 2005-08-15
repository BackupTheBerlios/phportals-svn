<?php

/**
 * @file
 * @brief Aktueller Administrator
 * @ingroup api
 * @author Hinrich Donner
 * @version 1
 */

/**
 * @brief Exception: Administrator allgemein
 * @ingroup err
 * @author Hinrich Donner
 * @version 1
 */
class EAdmin extends Exception
{
}

/**
 * @brief Aktueller Administrator
 * @author Hinrich Donner
 * @version 1
 */
class TAdmin
{
    /**
     * @brief Konfigurationsvariablen
     * @var array
     */
    protected $_vars = array();

    /**
     * @brief System-Container
     * @var TSystem
     */
    protected $_system;

    /**
     * @brief Constructor
     * @var TSystem $system Der System-Container
     */
    public function __construct(TSystem $system)
    {
        $this->_system = $system;
        if (!$this->_system->cookies->exists('admin'))
            return;
        $admin = $this->_system->cookies->admin;
        if (empty($admin))
            return;

        list ($aid,
              $pwd,
              $rest) = explode(':', base64_decode($admin));

        $sql = sprintf("SELECT * FROM `%s_authors` WHERE `aid`='%s' AND `pwd`='%s'",
                       $this->_system->config->prefix,
                       $this->_system->db->qStr(substr(trim($aid), 0, 25)),
                       $this->_system->db->qStr(substr(trim($pwd), 0, 40)));
        if (false == ($row = $this->_system->db->queryRow($sql)))
            return;

        $this->_vars = $row;
    }

    /**
     * @brief Lesen einer Konfigurationsvariablen
     * @param string $name Der Name der Variablen
     * @param mixed $default Ein Vorgabewert
     * @sa TConfig::__get()
     */
    public function get($name, $default = null)
    {
        if (array_key_exists($name, $this->_vars))
            return $this->_vars[$name];
        return $default;
    }

    /**
     * @brief Lesen einer Konfigurationsvariablen
     * @param string $name Der Name der Variablen
     * @sa TConfig::get()
     */
    public function __get($name)
    {
        switch ($name)
        {
            case 'aid':
            case 'name':
            case 'email':
            case 'url':
            case 'pwd':
                if (array_key_exists($name, $this->_vars))
                    $result = $this->_vars[$name];
                else
                    $result = '';
                break;

            case 'loggedin':
                if (array_key_exists('aid', $this->_vars))
                    $result = true;
                else
                    $result = false;
                break;

            default:
                if (!strncmp('radmin', $name, 6))
                {
                if (array_key_exists($name, $this->_vars))
                    $result = (bool) $this->_vars[$name];
                else
                    $result = false;
                }
                else
                    $result = null;
        }
        return $result;
    }

    /**
     * @brief Überprüfen eines Logins
     * @param string $aid Die Author-ID
     * @param string $pwd Das Klartextkennwort
     * @return bool
     *
     * Überprüft @b aid und @b pwd auf Ihre Gültigkeit.  In jedem Fall werden die
     * internen Werte, sofern vorhanden, gelöscht.
     */
    public function validateLogin($aid, $pwd)
    {
        $this->_vars = array();
        if (empty($aid) || empty($pwd))
            return false;
        $sql = sprintf("SELECT * FROM `%s_authors` WHERE `aid`='%s' AND `pwd`='%s'",
                       $this->_system->config->prefix,
                       $this->_system->db->qStr(substr($aid, 0, 25)),
                       $this->_system->db->qStr(substr(md5($pwd), 0, 40)));
        if (false === ($row = $this->_system->db->queryRow($sql)))
            return false;
        $this->_vars = $row;
        return true;
    }
}

// $Log$
//
?>
