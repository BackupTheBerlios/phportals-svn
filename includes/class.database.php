<?php

/**
 * @file
 * @brief Abstract Database Layer
 * @ingroup api
 * @author Hinrich Donner
 * @version 1
 */

/**
 * @brief Exception: Datenbank
 * @ingroup err
 * @author Hinrich Donner
 * @version 1
 */
class EDatabase extends Exception
{
}

abstract class TDatabaseResult implements Iterator
{
    protected $resource = null;

    protected $pointer = 0;

    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    /**
     * @brief Speicher freigeben
     */
    abstract public function close();

}

/**
 * @brief Datenbank-Layer (abstrakte Basis)
 * @author Hinrich Donner
 * @version 1
 */
abstract class TDatabase
{
    /**
     * @brief Der Name der aktuellen Datenbank
     * @var string
     */
    protected $name = '';

    /**
     * @brief PHP-Resource
     * @var resource
     */
    protected $resource = null;

    /**
     * @brief Konstruktor
     */
    public function __construct()
    {
    }

    /**
     * @brief Destruktor
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * @brief Datenbankverbindung erstellen
     * @param string $host Der SQL-Server oder die IP
     * @param string $username Der berechtigte Benutzer
     * @param string $passwd Das dazugehörige Kennwort
     */
    abstract public function connect($host, $name, $passwd);

    /**
     * @brief Datenbankverbindung schließen
     */
    abstract public function close();

    /**
     * @brief Letzte Fehlermeldung
     * @return string
     * @sa TDatabase::errno()
     */
    abstract public function error();

    /**
     * @brief String zum Schreiben vorbereiten
     * @param int|string $value Der Wert
     * @return string
     */
    abstract public function qStr($value);

    /**
     * @brief Letzter Fehlercode
     * @return int
     * @sa TDatabase::error()
     */
    abstract public function errno();

    /**
     * @brief Datenbank auswählen
     * @param string $name Der Name der Datenbank
     */
    public function select($name)
    {
        $this->name = $name;
    }

    /**
     * @brief SQL-Anweisung ausführen
     * @param string $sql Die Anweisung
     * @return mixed
     */
    abstract public function _execute($sql);

    /**
     * @brief SQL-Anweisung ausführen
     * @param string $sql Die Anweisung
     * @return mixed
     * @todo Fehler-Log einbinden
     */
    public function execute($sql)
    {
        $result = $this->_execute($sql);
        if ($this->errno() == 0)
            return $result;
        return $result;
    }

    /**
     * @brief SQL-Abfrage ausführen
     * @param string $sql Die Anweisung
     * @return TDatabaseResult
     */
    abstract public function query($sql);

    /**
     * @brief SQL-Abfrage ausführen (Ergebnismenge begrenzen)
     * @param string $sql Die Anweisung
     * @param int $offset Relativer Anfang
     * @param int $limit Maximale Anzahl der Zeilen
     * @return TDatabaseResult
     */
    abstract public function queryLimit($sql, $offset = 0, $limit = -1);

    /**
     * @brief SQL-Abfrage ausführen und nur die erste Zeile zurückgeben
     * @param string $sql Die Anweisung
     * @return array
     */
    abstract public function queryRow($sql);

    /**
     * @brief SQL-Abfrage ausführen und nur das erste Element der ersten Zeile zurückgeben
     * @param string $sql Die Anweisung
     * @return array
     */
    public function queryOne($sql)
    {
        if (false === ($row = $this->queryRow($sql)))
            return $false;
        return $row[0];
    }

    /**
     * @brief Resource-Handle zurückgeben
     * @return resource
     */
    public function getHandle()
    {
        return $this->resource;
    }
}

// $Log$
//
?>
