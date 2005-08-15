<?php

/**
 * @file
 * @brief Funktionen
 * @ingroup api
 * @author Hinrich Donner
 * @version 1
 */

/**
 * @brief Das Basisverzeichnis
 * @return string Relatives Verzeichnis der Installation zum Server-Root
 * @sa dlServer::Base()
 * @ingroup api
 */
function path()
{
    $result = dirname($_SERVER['SCRIPT_NAME']);
    if ($result == '/')
        return '/';
    return $result . '/';
}

/**
 * @brief Säubert die Argumente
 * @ingroup kernel
 * @version 1
 */
function cleanArgv()
{
    static $search  = array('|</?\s*script.*?>|si',
                            '|</?\s*frame.*?>|si',
                            '|</?\s*object.*?>|si',
                            '|</?\s*img.*?>|si',
                            '|</?\s*meta.*?>|si',
                            '|</?\s*applet.*?>|si',
                            '|</?\s*link.*?>|si',
                            '|</?\s*iframe.*?>|si',
                            '|\([^>]*"?[^)]*\)|si',
                            '|style\s*=\s*"[^"]*"|si');
    static $replace = array('');

    foreach ($_REQUEST as $name => $var)
    {
        // Clean var
        if (get_magic_quotes_gpc())
            stripslashes($var);

        // Einige CSS entfernen
        $var = preg_replace($search, $replace, $var);

        // Add to result array
        $_REQUEST[$name] = $var;
    }
}

/**
 * @brief Anzahl der übergebenen Argumente
 * @return int Die Anzahl
 * @sa argv()
 * @ingroup api
 */
function argc()
{
    static $result;

    if (isset($result))
        return $result;

    $result = count($_REQUEST);
    return $result;
}

/**
 * @brief Ein übergebenes Argument lesen
 * @param string $name Der Name
 * @param mixed $default Rückgabewert, wenn das Argument nicht existiert
 * @return string Der Wert
 * @sa argc()
 * @ingroup api
 */
function argv($name, $default = null)
{
    if ((argc() == 0) || !array_key_exists($name, $_REQUEST))
        return $default;
    if (get_magic_quotes_gpc())
        return stripslashes($_REQUEST[$name]);
    else
        return $_REQUEST[$name];
}

/**
 * @brief Variable für die Verwendung als Dateinamen vorbereiten
 * @param string $value Der Wert, der als Dateiname fungieren soll
 * @return string Der angepasste Wert
 * @ingroup api
 */
function toFilename($value)
{
    static $search  = array('!\.\./!si', // .. (directory traversal)
                            '!^.*://!si', // .*:// (start of URL)
                            '!/!si',     // Forward slash (directory traversal)
                            '!\\\\!si'); // Backslash (directory traversal)

    static $replace = array('',
                            '',
                            '_',
                            '_');

    // Seperatoren u.ä. aussortieren
    //
    $value = preg_replace($search, $replace, $value);
    if (!get_magic_quotes_gpc())
        $value = addslashes($value);
    return $value;
}

/**
 * @brief Testet, ob die Web-Site unter https läuft.
 * @return bool @b TRUE oder @b FALSE
 * @ingroup api
 */
function ssl()
{
    static $secure;

    if (isset($secure))
        return $secure;

    // IIS sets HTTPS=off
    //
    if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] != 'off'))
        $secure = true;
    else
        $secure = false;
    return $secure;
}

/**
 * @brief Liefert die Basis-URL.
 * @param bool $withpath Wenn @b TRUE wird der Pfad ebenfalls zurückgegeben
 * @return string Basis-URL
 * @see path()
 * @ingroup api
 */
function url($withpath = true)
{
    static $result;

    // Da die Variable als Index verwendet wird, muss der Inhalt immer konsistent sein.
    //
    if ($withpath)
        $withpath = 'yes';
    else
        $withpath = 'no';

    // Altes Ergebnis nehmen, wenn möglich
    //
    if (isset($result) && array_key_exists($withpath, $result))
        return $result[$withpath];

    // Dummy erzeugen
    //
    $result = array();

    // Server-Namen holen
    //
    if (empty($_SERVER['SERVER_NAME']))
        $server = getenv('HTTP_HOST');
    else
        $server = $_SERVER['SERVER_NAME'];

    // Protokoll ermitteln
    //
    if (ssl())
        $proto = 'https://';
    else
        $proto = 'http://';

    $result['no']   = $proto . $server;
    $result['yes']  = $proto . $server . path();
    if (substr($result['yes'], strlen($result['yes']) - 1, 1) == '/')
        $result['yes'] = substr($result['yes'], 0, strlen($result['yes']) - 1);

    return $result[$withpath];
}

/**
 * @brief Location
 *
 * Diese Funktion führt einen HTTP/1.0 korrekten Wechsel der Location aus.
 * Entgegen vielen anders lautenden Behauptungen und vor allem
 * Gepflogenheiten MUSS eine ABSOLUTE Adresse angegeben werden. Genau das
 * macht diese Funktion. Darüber hinaus startet sie im Anschluss eine
 * HTML-Ausgabe, die versucht, die neue Adresse mittels Meta-Refresh
 * anzuspringen. Dadurch wird sichergestellt, dass auch diejenigen Browser,
 * die Location nicht unterstützen, am Ziel ankommen.
 *
 * @param string $url Die neue Adresse (relativ oder absolut)
 * @ingroup api
 */
function location($url = '')
{
    if (empty($url))
        $url = url();
    elseif ((substr($url, 0, 5) != 'http:') && (substr($url, 0, 6) != 'https:'))
    {
        if (substr($url, 0, 1) == '/')
            $url = url(false).$url;
        else
            $url = url().'/'.$url;
    }

    // Write down session information
    //
    session_write_close();

    $url = str_replace('&amp;', '&', $url);

    // Jump to the location, if possible
    //
    if (!headers_sent());
        Header("Location: {$url}");

    // Make an output for source
    //
    $target = str_replace('&', '&amp;', "{$url}");
    echo "<html>\n<head>\n<title>"._("Weiterleiten")."</title>\n"
        ."<meta http-equiv=\"refresh\" content=\"0; URL={$target}\">\n"
        ."</head>\n<body>\n<center><h1>"._("Sie werden weitergeleitet")."</h1>\n"
        ."<p>"._("Wenn Ihr Browser Sie nicht weiterleitet,")
        ." <a href=\"{$target}\">"._("klicken Sie bitte hier")."</a></p>\n"
        ."<p>"._("Die Zieladresse lautet").":<br><br><b>{$target}</b></p>\n"
        ."<p><small>"._("PHP Portal System")."</small></p>\n"
        ."</body>\n</html>\n";
    exit;
}

// $Log$
//
?>
