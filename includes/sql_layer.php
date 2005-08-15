<?php

/************************************************************************/
/* PHP-NUKE: Web Portal System                                          */
/* ===========================                                          */
/*                                                                      */
/* Copyright (c) 2002 by Francisco Burzi (fbc@mandrakesoft.com)         */
/* http://phpnuke.org                                                   */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/************************************************************************/

if (eregi("sql_layer.php",$PHP_SELF)) {
    Header("Location: ../index.php");
    die();
}

/* $dbtype = "MySQL"; */
/* $dbtype = "mSQL"; */
/* $dbtype = "PostgreSQL"; */
/* $dbtype = "PostgreSQL_local";// When postmaster start without "-i" option. */
/* $dbtype = "ODBC"; */
/* $dbtype = "ODBC_Adabas"; */
/* $dbtype = "Interbase"; */
/* $dbtype = "Sybase"; */

/*
 * sql_connect($host, $user, $password, $db)
 * returns the connection ID
 */


class ResultSet {
    var $result;
    var $total_rows;
    var $fetched_rows;

    function set_result( $res ) {
        $this->result = $res;
    }

    function get_result() {
        return $this->result;
    }

    function set_total_rows( $rows ) {
        $this->total_rows = $rows;
    }

    function get_total_rows() {
        return $this->total_rows;
    }

    function set_fetched_rows( $rows ) {
        $this->fetched_rows = $rows;
    }

    function get_fetched_rows() {
        return $this->fetched_rows;
    }

    function increment_fetched_rows() {
        $this->fetched_rows = $this->fetched_rows + 1;
    }
}

/**
 * @brief Datenbankverbindung herstellen
 * @param string $host SQL-Server
 * @param string $user Benutzer
 * @param string $password Kennwort
 * @param string $db Datenbank
 * @return resource
 * @deprecated
 * @ingroup nuke
 * @sa TDatabase::connect()
 */
function sql_connect($host, $user, $password, $db)
{
    global $system;
    return $system->db->getHandle();
}

/**
 * @brief Datenbankverbindung beenden
 * @deprecated
 * @ingroup nuke
 * @sa TDatabase::close()
 */
function sql_logout()
{
    global $system;
    $system->db->close();
}


/**
 * @brief SQL-Anweisung ausführen
 * @deprecated
 * @ingroup nuke
 * @sa TDatabase::execute()
 */
function sql_query($query)
{
    global $system;
    return $system->db->execute($query);
}

/**
 * @brief Anzahl der Zeilen in der Ergebnismenge
 * @param resource $res Die Ergebnismenge
 * @return int
 * @deprecated
 * @ingroup nuke
 */
function sql_num_rows($res)
{
    $rows=mysql_num_rows($res);
    return $rows;
}

/**
 * @brief Eine Zeile der Ergebnismenge
 * @param resource $res Die Ergebnismenge
 * @return array
 * @deprecated
 * @ingroup nuke
 */
function sql_fetch_row($res)
{
    $row = mysql_fetch_row($res);
    return $row;
}

/**
 * @brief Eine Zeile der Ergebnismenge
 * @param resource $res Die Ergebnismenge
 * @return array
 * @deprecated
 * @ingroup nuke
 */
function sql_fetch_array($res)
{
    $row = array();
    $row = mysql_fetch_array($res);
    return $row;
}

/**
 * @brief Eine Zeile der Ergebnismenge
 * @param resource $res Die Ergebnismenge
 * @return object
 * @deprecated
 * @ingroup nuke
 */
function sql_fetch_object($res)
{
    $row = mysql_fetch_object($res);
    if($row)
        return $row;
    else
        return false;
}

/**
 * @brief Ergebnismenge löschen
 * @param resource $res Die Ergebnismenge
 * @deprecated
 * @ingroup nuke
 */
function sql_free_result($res)
{
    mysql_free_result($res);
}

?>
