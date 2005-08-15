<?php

/**
 * @file
 * @brief Session
 * @ingroup api
 * @author Hinrich Donner
 * @version 1
 */

/**
 * @brief Fehler der Session-Instanz
 * @ingroup err
 * @author Hinrich Donner
 * @version 1
 */
class ESession extends Exception
{
}

/**
 * @brief Session
 * @ingroup api
 * @author Hinrich Donner
 * @version 1
 */
class TSession implements Iterator
{
    /**
     * @brief Konstruktor
     */
    public function __construct()
    {
        if (!isset($_SESSION) || !is_array($_SESSION))
            throw new ESession(_("Starten Sie zunächst die Session"));
    }

    /**
     * @brief Prüft, ob eine Variable existiert
     * @param string $name Der NAme der Variablen
     * @return bool
     * @sa TSession::get(), TSession::__get(), TSession::delete()
     */
    public function exists($name)
    {
        if (!empty($name) && array_key_exists($name, $_SESSION))
            return true;
        return false;
    }

    /**
     * @brief Variable lesen
     * @param string $name Der Name der Variablen
     * @param mixed $default Ein Vorgabewert
     * @return mixed
     *
     * Diese Methode list eine Variable aus der Session.  Wenn die
     * gewünschte Variable nicht existiert, wird der Vorgabewert
     * zurückgegeben.
     *
     * @sa TSession::__get(), TSession::exists()
     */
    public function get($name, $default = null)
    {
        if ($this->exists($name))
            return $_SESSION[$name];
        return $default;
    }

    /**
     * @brief Variable lesen (Eigenschaft)
     * @param string $name Der Name der Variablen
     * @return mixed
     * @sa TSession::get()
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @brief Variable setzen
     * @param string $name Der Name der Variablen
     * @param mixed $value Der Wert
     * @sa TSession::__set()
     */
    public function set($name, $value = null)
    {
        if (null === $value)
            $this->delete($name);
        else
            $_SESSION[$name] = $value;
    }

    /**
     * @brief Variable setzen
     * @param string $name Der Name der Variablen
     * @param mixed $value Der Wert
     * @sa TSession::set()
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
        unset($_SESSION[$name]);
    }

    /**
     * @brief Aktuelle Variable (Iterator)
     * @return array
     */
    public function current()
    {
        return current($_SESSION);
    }

    /**
     * @brief Name der aktuellen Variablen (Iterator)
     * @return string
     */
    public function key()
    {
        return key($_SESSION);
    }

    /**
     * @brief Nächste Variable (Iterator)
     * @return array
     */
    public function next()
    {
        return next($_SESSION);
    }

    /**
     * @brief Erste Variable (Iterator)
     * @return array
     */
    public function rewind()
    {
        return reset($_SESSION);
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
