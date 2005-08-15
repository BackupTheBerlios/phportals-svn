<?php

/**
 * @file
 * @brief Session basierte Meldungen
 * @ingroup api
 * @author Hinrich Donner
 * @version 1
 */

/**
 * @brief Session basierte Meldungen
 * @ingroup api
 * @author Hinrich Donner
 * @version 1
 */
class TSessionMessages implements Iterator
{
    /**
     * @brief Konstruktor
     * @param string $message Eine Meldung
     * @param string $type Der Meldungstyp (@a status oder @b error)
     */
    public function __construct($message = '', $type = 'status')
    {
        if (!isset($_SESSION['session_messages']) || !is_array($_SESSION['session_messages']))
            $_SESSION['session_messages'] = array();
        if (empty($message))
            return;
        $this->set($message);
    }

    /**
     * @brief Eine Meldung setzen
     * @param string $message Die Meldung
     */
    public function set($message, $type = 'status')
    {
        $_SESSION['session_messages'][] = array('time'      => time(),
                                                'type'      => $type,
                                                'message'   => $message);
    }

    /**
     * @brief Meldung als Eigenschaft schreiben
     * @param string $type Nachrichtentyp (@a status oder @a error)
     * @param string $message Die Nachricht
     */
    public function __set($type, $message)
    {
        if (false !== strpos('error,status', $type))
            $this->set($message, $type);
    }

    /**
     * @brief Alle Meldungen löschen
     */
    public function delete()
    {
        $_SESSION['session_messages'] = array();
    }

    /**
     * @brief Aktuelle Meldung (Iterator)
     * @return array
     */
    public function current()
    {
        return current($_SESSION['session_messages']);
    }

    /**
     * @brief Index der aktuellen Meldung (Iterator)
     * @return int
     */
    public function key()
    {
        return key($_SESSION['session_messages']);
    }

    /**
     * @brief Nächste Meldung (Iterator)
     * @return array
     */
    public function next()
    {
        return next($_SESSION['session_messages']);
    }

    /**
     * @brief Erste Meldung (Iterator)
     * @return array
     */
    public function rewind()
    {
        return reset($_SESSION['session_messages']);
    }

    /**
     * @brief Gültigkeit der aktuellen Meldung (Iterator)
     * @return array
     */
    public function valid()
    {
        return (bool) is_array($this->current());
    }
}

// $Log$
//
?>
