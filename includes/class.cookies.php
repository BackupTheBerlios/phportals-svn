<?php

/**
 * @file
 * @brief Cookies
 * @ingroup api
 * @author Hinrich Donner
 * @version 1
 */

/**
 * @brief Cookies
 * @ingroup api
 * @author Hinrich Donner
 * @version 1
 */
class TCookies implements Iterator
{
    /**
     * @brief Domain für das Cookie
     * @var string
     */
    protected $host;

    /**
     * @brief Konstruktor
     */
    public function __construct()
    {
        if (strcmp(substr($_SERVER['SERVER_NAME'], 0, 3), 'www'))
            $this->host = '.' . $_SERVER['SERVER_NAME'];
        else
            $this->host = substr($_SERVER['SERVER_NAME'], 4);
    }

    /**
     * @brief Prüft, ob eine Variable existiert
     * @param string $name Der NAme der Variablen
     * @return bool
     * @sa TCookies::get(), TCookies::__get(), TCookies::delete()
     */
    public function exists($name)
    {
        if (!empty($name) && array_key_exists($name, $_COOKIE))
            return true;
        return false;
    }

    /**
     * @brief Variable lesen
     * @param string $name Der Name der Variablen
     * @param mixed $default Ein Vorgabewert
     * @return string
     *
     * Diese Methode list eine Variable aus den Cookies.  Wenn die
     * gewünschte Variable nicht existiert, wird der Vorgabewert
     * zurückgegeben.
     *
     * @sa TCookies::__get(), TCookies::exists()
     */
    public function get($name, $default = null)
    {
        if ($this->exists($name))
            return $_COOKIE[$name];
        return $default;
    }

    /**
     * @brief Variable lesen (Eigenschaft)
     * @param string $name Der Name der Variablen
     * @return string
     * @sa TCookies::get()
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @brief Variable setzen
     * @param string $name Der Name der Variablen
     * @param mixed $value Der Wert
     * @param int $expire Verfallzeit in Sekunden (Vorgabe: 100 Tage)
     * @sa TCookies::__set()
     */
    public function set($name, $value = null, $expire = 8640000)
    {
        if (null === $value)
        {
            $this->delete($name);
            return;
        }
        $_COOKIE[$name] = $value;
        if (headers_sent())
            return;
        setcookie($name,
                  (string) $value,
                  time() + $expire,
                  path(),
                  $this->host,
                  ssl());
    }

    /**
     * @brief Variable setzen
     * @param string $name Der Name der Variablen
     * @param mixed $value Der Wert
     * @sa TCookies::set()
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * @brief Löschen einer Session-Variablen
     * @param string $name Der Name
     */
    public function delete($name)
    {
        if (!$this->exists($name))
            return;
        unset($_COOKIE[$name]);
        setcookie($name);
    }

    /**
     * @brief Aktuelle Variable (Iterator)
     * @return array
     */
    public function current()
    {
        return current($_COOKIE);
    }

    /**
     * @brief Name der aktuellen Variablen (Iterator)
     * @return string
     */
    public function key()
    {
        return key($_COOKIE);
    }

    /**
     * @brief Nächste Variable (Iterator)
     * @return array
     */
    public function next()
    {
        return next($_COOKIE);
    }

    /**
     * @brief Erste Variable (Iterator)
     * @return array
     */
    public function rewind()
    {
        return reset($_COOKIE);
    }

    /**
     * @brief Gültigkeit der aktuellen Meldung (Iterator)
     * @return array
     */
    public function valid()
    {
        return (bool) ($this->key());
    }
}

// $Log$
//
?>
