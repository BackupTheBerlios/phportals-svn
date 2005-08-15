<?php

/**
 * @file
 * @brief Konfiguration
 * @ingroup api
 * @author Hinrich Donner
 * @version 1
 */

/**
 * @brief Exception: Konfiguration allgemein
 * @ingroup err
 * @author Hinrich Donner
 * @version 1
 */
class EConfig extends Exception
{
}

/**
 * @brief Konfigurationsvariablen
 * @author Hinrich Donner
 * @version 1
 * @sa TApplication::$config
 *
 * Eine Instanz dieser Klasse befindet sich in der Eigenschaft @a $config der
 * Klasse @a TApplication.
 */
class TConfig implements Iterator
{
    /**
     * @brief Konfigurationsvariablen
     * @var array
     */
    protected $_vars = array();

    /**
     * @brief Konfigurationsvariablen laden
     * @param string $filename Der Dateiname
     */
    public function load($filename)
    {
        // Existiert die Datei?
        //
        if (!file_exists($filename))
            throw new EFileNotFound($filename);

        // Laden
        //
        include $filename;

        // Variablen übernehmen
        //
        unset($filename);
        $this->_vars = get_defined_vars();
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
        return $this->get($name);
    }

    /**
     * @brief Alle Namen der Konfigurationsvariablen ermitteln
     * @return array
     * @sa TConfig::all()
     */
    public function keys()
    {
        return array_keys($this->_vars);
    }

    /**
     * @brief Alle Werte ermitteln
     * @return array
     * @sa TConfig::keys()
     */
    public function all()
    {
        return $this->_vars;
    }

    /**
     * @brief Aktuelle Konfigurationsvariable (Iterator)
     * @return mixed
     */
    public function current()
    {
        return current($this->_vars);
    }

    /**
     * @brief Name der aktuellen Konfigurationsvariablen (Iterator)
     * @return string
     */
    public function key()
    {
        return key($this->_vars);
    }

    /**
     * @brief Erste Konfigurationsvariable (Iterator)
     * @return mixed
     */
    public function rewind()
    {
        return reset($this->_vars);
    }

    /**
     * @brief Nächstes Konfigurationsvariable (Iterator)
     * @return mixed
     */
    public function next()
    {
        return next($this->_vars);
    }

    /**
     * @brief Gültigkeit der aktuellen Konfigurationsvariablen (Iterator)
     * @return mixed
     */
    public function valid()
    {
        return (bool) ($this->current());
    }
}

// $Log$
//
?>
