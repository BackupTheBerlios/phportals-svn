<?php

/**
 * @file
 * @brief Abstract Database Layer
 * @ingroup api
 * @author Hinrich Donner
 * @version 1
 */

// Eltern einbinden
//
require_once 'includes/class.database.php';

class TMySQLResult extends TDatabaseResult
{
    /**
     * @brief Anzahl der Zeilen in der Ergebnismenge
     * @return int
     */
    public function count()
    {
        return mysql_num_rows($this->resource);
    }

    /**
     * @brief Aktuelle Zeilennummer in der Ergebnismenge
     * @return int
     */
    public function key()
    {
        return $this->pointer;
    }

    /**
     * @brief Aktelle Zeile aus der Ergebnismenge
     * @return array
     */
    public function current()
    {
        // Ergebnis holen
        //
        if (false === ($result = mysql_fetch_assoc($this->resource)))
            return null;

        // Zeiger zur�cksetzen
        //
        mysql_data_seek($this->resource, $this->pointer);

        // Zur�ck
        //
        return $result;
    }

    /**
     * @brief N�chste Zeile der Ergebnismenge
     * @return array
     */
    public function next()
    {
        // Ergebnis holen
        //
        if (false === ($result = mysql_fetch_assoc($this->resource)))
            return null;

        // Zeiger berichtigen
        //
        $this->pointer ++;

        // Zur�ck
        //
        return $result;
    }

    /**
     * @brief Zur ersten Zeile in der Ergebnismenge springen
     * @return array
     */
    public function rewind()
    {
        // Zum Anfang springen
        //
        $this->pointer = 0;
        mysql_data_seek($this->resource, 0);
        return $this->current();
    }

    /**
     * @brief G�ltigkeit des aktuellen Ergebnisses pr�fen
     * @return bool
     */
    public function valid()
    {
        if (!is_array($this->current()))
            return false;
        return true;
    }

    /**
     * @brief Speicher freigeben
     */
    public function close()
    {
        mysql_free_result($this->resource);
    }
}

/**
 * @brief Datenbank-Layer: MySQL
 * @author Hinrich Donner
 * @version 1
 */
class TMySQL extends TDatabase
{
    /**
     * @brief Konstruktor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @brief Datenbankverbindung erstellen
     * @param string $host Der SQL-Server oder die IP
     * @param string $name Der berechtigte Benutzer
     * @param string $passwd Das dazugeh�rige Kennwort
     * @sa TMySQL::close()
     */
    public function connect($host, $name, $passwd)
    {
        if (false === ($this->resource = mysql_connect($host,
                                                       $name,
                                                       rawurlencode($passwd),
                                                       false,
                                                       MYSQL_CLIENT_COMPRESS)))
        {
            $this->resource = null;
            throw new EDatabase(mysql_error(), mysql_errno());
        }
    }

    /**
     * @brief Datenbankverbindung schlie�en
     * @sa TMySQL::connect()
     */
    public function close()
    {
        if ($this->resource === null)
            return;
        mysql_close($this->resource);
        $this->resource = null;
        $this->name = '';
    }

    /**
     * @brief Letzte Fehlermeldung
     * @return string
     * @sa TMySQL::errno()
     */
    public function error()
    {
        return mysql_error();
    }

    /**
     * @brief Letzter Fehlercode
     * @return int
     * @sa TMySQL::error()
     */
    public function errno()
    {
        return mysql_errno();
    }

    /**
     * @brief String zum Schreiben vorbereiten
     * @param int|string $value Der Wert
     * @return string
     */
    public function qStr($value)
    {
        if ($this->resource)
            return mysql_real_escape_string($value, $this->resource);
        else
            return mysql_escape_string($value);
    }

    /**
     * @brief Datenbank ausw�hlen
     * @param string $name Der Name der Datenbank
     */
    public function select($name)
    {
        if (!mysql_select_db($name, $this->resource))
            throw new EDatabase(mysql_error(), mysql_errno());
        parent::select($name);
    }

    /**
     * @brief SQL-Anweisung ausf�hren
     * @param string $sql Die Anweisung
     * @return mixed
     */
    public function _execute($sql)
    {
        $result = mysql_query($sql, $this->resource);
        return $result;
    }

    /**
     * @brief SQL-Abfrage ausf�hren
     * @param string $sql Die Anweisung
     * @return TDatabaseResult
     */
    public function query($sql)
    {
        if (false === ($result = $this->execute($sql)))
            throw new EDatabase(mysql_error(), mysql_errno());
        return new TMySQLResult($result);
    }

    /**
     * @brief SQL-Abfrage ausf�hren (Ergebnismenge begrenzen)
     * @param string $sql Die Anweisung
     * @param int $offset Relativer Anfang
     * @param int $limit Maximale Anzahl der Zeilen
     * @return TDatabaseResult
     */
    public function queryLimit($sql, $offset = 0, $limit = -1)
    {
        if ($limit != -1)
            $sql = $sql . sprintf(" LIMIT %u, %u", $offset, $limit);
        else
            $sql = $sql . sprintf(" LIMIT %u", $offset);
        return $this->query($sql);
    }

    /**
     * @brief SQL-Abfrage ausf�hren und nur die erste Zeile zur�ckgeben
     * @param string $sql Die Anweisung
     * @return array
     */
    public function queryRow($sql)
    {
        if (false === ($result = $this->execute($sql)))
            throw new EDatabase(mysql_error(), mysql_errno());
        $row = mysql_fetch_array($result);
        mysql_free_result($result);
        return $row;
    }
}

// $Log$
//
?>
