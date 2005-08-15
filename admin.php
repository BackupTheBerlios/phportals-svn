<?php
// $Id$

/**
 * @file
 * @brief Administration
 * @author Hinrich Donner
 * @version 1
 * @ingroup kernel
 */

require_once 'mainfile.php';

//get_lang('admin');

/**
 * @brief Abstrakte Basis für ein Admin-Modul
 * @author Hinrich Donner
 * @version 1
 * @ingroup api
 *
 * Jedes Modul @b muss von dieser Klasse abgeleitet sein.  Die abstrakten Methoden müssen
 * entsprechend überschrieben werden.
 */
abstract class TAbstractAdminModule extends TAbstractModule
{
    /**
     * @brief Eigentümer
     * @var TAdminPAnel
     */
    public $owner = null;

    /**
     * @brief Konstruktor
     */
    public function __construct()
    {
        parent::__construct();

        // Globale Variablen initialisieren
        //
        $GLOBALS['adminpage']   = 0;
    }

    /**
     * @brief Seitenkopf ausgeben
     * @param string $title Der Seitentitel
     */
    public function header($title = '')
    {
        if (empty($title))
            $title = _("Administration");
        $this->owner->header($title);
    }

    /**
     * @brief Seitenfuß ausgeben
     */
    public function footer()
    {
        $this->owner->footer();
    }

    /**
     * @brief Unterstützte Menüeinträge zurückgeben
     * @return array
     */
    abstract public function getMenuItems();

}

/**
 * @brief Admin-Wrapper
 * @author Hinrich Donner
 * @version 1
 * @ingroup kernel
 *
 * Der AdminPanel übernimmt das Laden und Aufrufen der administrativen Module.
 */
class TAdminPanel extends TAbstractModule
{
    /**
     * @brief Liste der verfügbaren Admin-Module
     * @var array
     */
    protected $_modules = array();

    /**
     * @brief Konstruktor
     */
    public function __construct()
    {
        parent::__construct();

        // Globale Variablen initialisieren
        //
        $GLOBALS['adminpage']   = 0;

        // Adminmodule lesen
        //
        $dir = new DirectoryIterator('admin');
        foreach ($dir as $file)
        {
            if ($file->isDir() || $file->isDot() || $file->isLink() || !$file->isReadable()
                    || !preg_match("/.*\.php$/i", $file->getFilename()) )
                continue;

            // Datei einbinden
            //
            $aid = $this->_system->admin->aid;
            $prefix = $this->_system->config->prefix;
            $user_prefix = $this->_system->config->user_prefix;
            require_once $file->getPathname();

            // Instanzen erzeugen
            //
            $classname = 'TAdminModule' . preg_replace("/(\.php)$/i", '', ucfirst($file->getFilename()));

            if (!class_exists($classname))
                continue;

            $module = new $classname();
            $module->owner = $this;
            $this->_modules[$file->getFilename()] = $module;
        }
        ksort($this->_modules);

        // Ereignis-Methoden binden
        //
        $this->afterHeader = 'afterHeader';
    }

    /**
     * @brief Unterstützte Menüeinträge liefern
     * @return array
     */
    public function getMenuItems()
    {
        $result = array(array('sort'        => '____',
                              'op'          => '',
                              'title'       => _("Übersicht"),
                              'text'        => _("Alle Optionen für die Administration auf einen Blick."),
                              'image'       => 'main.gif'),
                        array('sort'        => 'zzzz',
                              'op'          => 'logout',
                              'title'       => _("Abmelden"),
                              'text'        => _("Beendet die Sitzung als Administrator."),
                              'image'       => 'logout.gif'));
        return $result;
    }

    /**
     * @brief Sortiert die Menüeinträge
     * @param array $a
     * @param array $b
     * @return int
     * @attention @b usort() enthält die Dokumentation
     */
    public function _sortMenu($a, $b)
    {
        return strcmp($a['sort'], $b['sort']);
    }

    /**
     * @brief Nach der Ausgabe des Headers (Ereignismethode)
     */
    public function afterHeader()
    {
        $menus = $this->getMenuItems();
        foreach ($this->_modules as $module)
            $menus = array_merge($menus, $module->getMenuItems());

        usort($menus, array('TAdminPanel', '_sortMenu'));
        $op = trim(argv('op', ''));

        title(_("Übersicht"));
        openTable();
        echo "<ul class=\"adminmenu\">\n";
        foreach ($menus as $menu)
        {
            if (strcmp($op, $menu['op']))
                echo "<li class=\"adminmenu\">\n"
                    . "<a href=\"admin.php?op=$menu[op]\" title=\"$menu[text]\">"
                    . "<img src=\"admin/images/$menu[image]\" alt=\"$menu[title]\" title=\"$menu[text]\" />"
                    . "</a><span class=\"adminmenu\"><br />"
                    . "<a href=\"admin.php?op=$menu[op]\" title=\"$menu[text]\">$menu[title]</a></span>"
                    . "</li>\n";
            else
                echo "<li class=\"adminmenu\">\n"
                    . "<img src=\"admin/images/$menu[image]\" alt=\"$menu[title]\" title=\"$menu[text]\" />"
                    . "<span class=\"adminmenu\"><br />"
                    . "<em>$menu[title]</em></span>"
                    . "</li>\n";
        }
        echo "</ul>\n"
            . "<br style=\"clear: both;\">\n";
        closeTable();
    }

    /**
     * @brief Prüfen, ob die angefragte Funktion ausgeführt werden kann
     * @param string $op Die Funktion
     * @return bool
     */
    public function canHandleFunction($op)
    {
        return true;
    }

    /**
     * @brief Prüft, ob überhaupt Administratoren eingetragen sind
     * @return bool
     */
    protected function _hasAdmin()
    {
        static $result;

        if (isset($result))
            return $result;

        $sql = sprintf("SELECT COUNT(*) FROM `%s_authors`", $this->_system->config->prefix);
        $count = $this->_system->db->queryOne($sql);
        $result = (bool) ($count > 0);
        return $result;
    }

    /**
     * @brief Formular für den ersten aller Administratoren
     */
     protected function _createFirstForm()
     {
        // Kein Admin-Menü
        //
        $this->afterHeader = null;

        $this->header(_("Super-Administrator anlegen"));
        title($this->_system->config->sitename . ': ' . _("Super-Administrator anlegen"));
        OpenTable();
        echo "<p class=\"content\">"
            . _("Derzeit existiert kein Super-Administrator. Bitte füllen Sie das nachfolgende Formular aus, um einen Super-Administrator anzulegen.")
            . "</p>\n"
            . "<p class=\"content\">"
            . _("Sie müssen das Kennwort für den Datenbankbenutzer angeben. Diese Eingabe dient der Überprüfung der Rechtmäßigkeit für die Neuanlage des Super-Administrators.")
            . "</p>"
            . "<form action=\"admin.php\" method=\"post\" name=\"firstadmin\" id=\"firstadmin\">"
            . "<input type=\"hidden\" name=\"op\" value=\"create_first\" />\n"
            . "<table>\n"
            . "<tr>\n"
            . "<td><label for=\"name\">" . _("Login-Name") . ":</label></td>\n"
            . "<td><input type=\"text\" name=\"name\" size=\"30\" maxlength=\"25\" id=\"name\" /></td>\n"
            . "</tr>\n"
            . "<tr>\n"
            . "<td><label for=\"rname\">" . _("Echter Name") . ":</label></td>\n"
            . "<td><input type=\"text\" name=\"rname\" size=\"30\" maxlength=\"50\" id=\"rname\" /></td>\n"
            . "</tr>\n"
            . "<tr>\n"
            . "<td><label for=\"url\">" . _("Homepage") . ":</label></td>\n"
            . "<td><input type=\"text\" name=\"url\" size=\"30\" maxlength=\"255\" value=\"http://\" /></td>\n"
            . "</tr>\n"
            . "<tr>\n"
            . "<td><label for=\"email\">" . _("Emailadresse") . ":</label></td>\n"
            . "<td><input type=\"text\" name=\"email\" size=\"30\" maxlength=\"255\" /></td>\n"
            . "</tr>\n"
            . "<tr>\n"
            . "<td><label for=\"pwd\">" . _("Gewünschtes Kennwort") . ":</label></td>\n"
            . "<td><input type=\"password\" name=\"pwd\" size=\"11\" maxlength=\"20\" /></td>\n"
            . "</tr>"
            . "<tr>\n"
            . "<td><label for=\"pwd2\">" . _("Kennwortwiederholung") . ":</label></td>\n"
            . "<td><input type=\"password\" name=\"pwd2\" size=\"11\" maxlength=\"20\" /></td>\n"
            . "</tr>"
            . "<tr>\n"
            . "<td><label for=\"dbpwd\">" . _("Kennwort der Datenbank") . ":</label></td>\n"
            . "<td><input type=\"password\" name=\"dbpwd\" size=\"11\" maxlength=\"40\" /></td>\n"
            . "</tr>"
            . "<tr>\n"
            . "<td><label for=\"usercopy\">" . _("Auch Benutzer anlegen") ."</label></td>\n"
            . "<td><input type=\"checkbox\" name=\"usercopy\" value=\"1\" checked /></td>\n"
            . "</tr>"
            . "<tr>\n"
            . "<td colspan=\"2\" style=\"text-align: center;\">"
            . "<input type=\"button\" value=\""._("Abbrechen")."\" onClick=\"self.location.href='index.php'\" />\n"
            . "<input type=\"submit\" value=\""._("Super-Administrator anlegen")."\" />\n"
            . "</td>\n"
            . "</tr>"
            . "</table>\n"
            . "</form>\n"
            . "<script type=\"text/javascript\" language=\"javascript\"><!--\n"
            . "  document.firstadmin.name.focus();\n"
            . "--></script>\n";
        CloseTable();
        $this->footer();
        exit;
    }

    /**
     * @brief Erstmalig einen Administrator anmelden
     * @param string $name Login-Name
     * @param string $rname Realname
     * @param string $pwd Kennwort
     * @param string $pwd2 Kennwortwiederholung
     * @param string $dbpwd Datenbankkennwort
     * @param string $url Homepage
     * @param string $email Emailadresse
     * @param bool $usercopy Gleichnamigen Benutzer erzeugen
     */
    protected function _createFirst($name, $rname, $pwd, $pwd2, $dbpwd, $url, $email, $usercopy)
    {
        // Eingaben prüfen
        //
        if (empty($name))
            return new TSessionMessages(_("Sie müssen einen Login-Namen angeben."), 'error');
        if (empty($pwd))
            return new TSessionMessages(_("Sie müssen ein Kennwort angeben."), 'error');
        if (strcmp($pwd, $pwd2))
            return new TSessionMessages(_("Die Kennwörter stimmen nicht überein."), 'error');
        if (strcmp($dbpwd, $this->_system->config->dbpass))
            return new TSessionMessages(_("Das Kennwort der Datenbank ist falsch."), 'error');

        if (strcmp('http://', $url))
            $url = '';
        if (empty($rname))
            $name2 = $name;
        else
            $name2 = $rname;

        $md5pwd = md5($pwd);

        $sql = sprintf("INSERT INTO `%s_authors` (`aid`, `name`, `pwd`, `url`, `email`, `radminsuper`) VALUES ('%s', '%s', '%s', '%s', '%s', '1')",
                       $this->_system->config->prefix,
                       $this->_system->db->qStr(substr($name, 0, 25)),
                       $this->_system->db->qStr(substr($name2, 0, 50)),
                       $this->_system->db->qStr($md5pwd),
                       $this->_system->db->qStr(substr($url, 0, 255)),
                       $this->_system->db->qStr(substr($email, 0, 255)));
        if (false === $this->_system->db->execute($sql) && $this->_system->config->verbose)
            new TSessionMessages($this->_system->db->error());

        // User-Record auch anlegen
        //
        if ($usercopy)
        {
            $sql = sprintf("INSERT INTO `%s_users`
                            (`uname`,
                             `name`,
                             `pass`,
                             `url`,
                             `email`,
                             `user_avatar`,
                             `user_regdate`)
                            VALUES ('%s',
                                    '%s',
                                    '%s',
                                    '%s',
                                    '%s',
                                    'blank.gif',
                                    '%u')",
                           $this->_system->config->user_prefix,
                           $this->_system->db->qStr(substr($name, 0, 25)),
                           $this->_system->db->qStr(substr($rname, 0, 60)),
                           $this->_system->db->qStr($md5pwd),
                           $this->_system->db->qStr(substr($url, 0, 255)),
                           $this->_system->db->qStr(substr($email, 0, 255)),
                           time());
            if (false === $this->_system->db->execute($sql) && $this->_system->config->verbose)
                new TSessionMessages($this->_system->db->error());
            new TSessionMessages(sprintf(_("Der Administrator '%s' wurde erstellt."), $name) . ' '
                                    . _("Bitte melden Sie sich an."));
        }
        location('admin.php');
    }

    /**
     * @brief Login-Formular
     */
    protected function _loginForm()
    {
        $this->afterHeader = null;
        $this->header(_("Administrator System Anmeldung"));
        title(_("Administrator System Anmeldung"));
        openTable();
        echo "<form action=\"admin.php\" method=\"post\" name=\"adminlogin\" id=\"adminlogin\">"
            . "<table>"
            . "<tr>\n"
            . "<td><label for=\"aid\">" . _("Administrator ID") .":</label></td>\n"
            . "<td><input type=\"text\" name=\"aid\" size=\"20\" maxlength=\"20\" name=\"aid\" id=\"aid\" /></td>\n"
            . "</tr>\n"
            . "<tr>\n"
            . "<td><label for=\"pwd\">" . _("Kennwort") . "</label></td>\n"
            . "<td><input type=\"password\" name=\"pwd\" size=\"20\" maxlength=\"20\" /></td>\n"
            . "</tr>\n"
            . "<tr>\n"
            . "<td>"
            . "<input type=\"hidden\" name=\"op\" value=\"login\" />"
            . "<input type=\"button\" value=\""._("Abbrechen")."\" onClick=\"self.location.href='index.php'\" />\n"
            . "<input type=\"submit\" value=\"" . _("Anmelden") . "\" />"
            . "</td>\n"
            . "</tr>\n"
            . "</table>\n"
            . "</form>\n"
            . "<script type=\"text/javascript\" language=\"javascript\"><!--\n"
            . "  document.adminlogin.aid.focus();\n"
            . "--></script>\n";
        closeTable();
        $this->footer();
        exit;
    }

    /**
     * @brief Anmeldevorgang
     * @param string $aid Der Admin-Login-Name
     * @param string $pwd Das Kennwort
     */
    protected function _login($aid, $pwd)
    {
        if (empty($aid) || empty($pwd))
        {
            // Kennung oder KEnnwort fehlt
            //
            new TSessionMessages(_("Die Administratorkennung oder das Kennwort fehlen oder sind fehlerhaft."));
            location('admin.php');
        }
        $sql = sprintf("SELECT `pwd` FROM `%s_authors` WHERE `aid`='%s'",
                       $this->_system->config->prefix,
                       $this->_system->db->qStr(substr($aid, 0, 25)));
        $dbpwd = $this->_system->db->queryOne($sql);
        if (strcmp($dbpwd, md5($pwd)))
        {
            // Kennwörter stimmen nicht überein
            //
            new TSessionMessages(_("Die Administratorkennung oder das Kennwort fehlen oder sind fehlerhaft."));
            location('admin.php');
        }

        // Alles Ok, Cookie setzen
        //
        $admin = sprintf('%s:%s:', substr($aid, 0, 25), md5($pwd));
        if (!strncmp('www.', $_SERVER['SERVER_NAME'], 4))
            $url = substr($_SERVER['SERVER_NAME'], 4);
        else
            $url = '.' . $_SERVER['SERVER_NAME'];
        setcookie('admin',
                  base64_encode($admin),
                  $this->_system->config->get('admin_cookie_lifetime', 0));
        new TSessionMessages(_("Sie sind jetzt als Administrator angemeldet."));
        location('admin.php');
    }

    /**
     * @brief Modul ausführen
     * @param string $op Die Funktion
     */
    public function run($op = '')
    {
        $op = trim(argv('op', ''));

        // Zuerst prüfen, ob überhaupt ein Admin existiert
        //
        if (!$this->_hasAdmin())
        {
            if (!strcmp('create_first', $op))
                $this->_createFirst(trim(argv('name', '')),
                                   trim(argv('rname', '')),
                                   trim(argv('pwd', '')),
                                   trim(argv('pwd2', '')),
                                   trim(argv('dbpwd', '')),
                                   trim(argv('url', '')),
                                   trim(argv('email', '')),
                                   argv('usercopy', true));
            else
                $this->_createFirstForm();
        }

        // Login prüfen
        //
        if (!$this->_system->admin->loggedin)
        {
            if (!strcmp('login', $op))
                $this->_login(trim(argv('aid', '')),
                              trim(argv('pwd', '')));
            else
                $this->_loginForm();
        }


        if (empty($op))
        {
            // Hauptseite
            //
            $this->header(_("Administrationsübersicht"));

            // mal sehen, was hier hin könnte

            // Fuß
            //
            $this->footer();
        }
        elseif (!strcmp('logout', $op))
            $this->doLogout();
        else
        {
            // Anderes modul
            //
            foreach ($this->_modules as $module)
                if ($module->canHandleFunction($op))
                    return $module->run($op);

            // Fehlender Handler oder Zugriff verweigert
            //
            new TSessionMessages(_("Die gewünschte Funktion existiert nicht, oder Sie sind nicht berechtigt, diese auszuführen."), 'error');
            location('admin.php');
        }
    }

    /**
     * @brief Den Administrator abmelden
     */
    public function doLogout()
    {
        setcookie('admin');
        new TSessionMessages(_("Sie sind jetzt als Administrator abgemeldet."));
        location('index.php');
    }
}

$admin_panel = new TAdminPanel();
$admin_panel->run();

// $Log$
//
?>
