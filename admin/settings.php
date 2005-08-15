<?php

/**
 * @file
 * @brief Konfigurationsvariablen
 * @author Hinrich Donner
 * @version 1
 * @ingroup admin
 */

/**
 * @brief Konfigurationsvariablen
 * @author Hinrich Donner
 * @version 1
 * @ingroup admin
 */
class TAdminModuleSettings extends TAbstractAdminModule
{
    /**
     * @brief Menüeinträge zurückgeben
     * @return array
     */
    public function getMenuItems()
    {
        if (!$this->_system->admin->radminsuper)
            return array();

        $result = array(array('sort'        => 'sys-cfg',
                              'op'          => 'configure',
                              'title'       => _("Einstellungen"),
                              'text'        => _("Generelle Einstellungen und Konfiguration für Ihre Web-Site."),
                              'image'       => 'settings.gif'));
        return $result;
    }

    /**
     * @brief Prüfen, ob die angefragte Funktion ausgeführt werden kann
     * @param string $op Die Funktion
     * @return bool
     */
    public function canHandleFunction($op)
    {
        if (!$this->_system->admin->radminsuper)
            return false;

        if (false !== strpos('configure,config-save', $op))
            return true;
        return false;
    }

    /**
     * @brief Modul ausführen
     * @param string $op Die Funktion
     */
    public function run($op = '')
    {
        switch ($op)
        {
            case 'configure':
                $this->_displayForm();
                break;

            case 'config-save':
                break;
        }
    }

    /**
     * @brief Formular anzeigen
     */
    protected function _displayForm()
    {
        $this->header(_("Einstellungen ändern"));
        title(_("Einstellungen ändern"));
        title(_("Allgemeine Einstellungen"));
        openTable();
        echo "<table class=\"input\">\n"
            . "<tr class=\"input\">\n"
            . "<th class=\"input\">" . _("Name der Web-Site") . ":</th>\n"
            . "<td class=\"input\"><input type=\"text\" name=\"xsitename\" value=\"{$this->_system->config->sitename}\" size=\"40\" /></td>\n"
            . "<td class=\"input\"><small>" . _("Geben Sie hier den Namen Ihrer Internet-Präsenz an.") . "</td>\n"
            . "</tr>\n"
            . "<tr class=\"input\">\n"
            . "<th class=\"input\">" . _("Slogan/Untertitel") . ":</th>\n"
            . "<td class=\"input\"><input type=\"text\" name=\"xslogan\" value=\"{$this->_system->config->slogan}\" size=\"40\" /></td>\n"
            . "<td class=\"input\"><small>" . _("Geben Sie hier eine kurze Information zur Web-Site an.") . "</td>\n"
            . "</tr>\n"
            . "<tr class=\"input\">\n"
            . "<th class=\"input\">" . _("Start der Web-Site") . ":</th>\n"
            . "<td class=\"input\"><input type=\"text\" name=\"xstartdate\" value=\"{$this->_system->config->startdate}\" size=\"40\" /></td>\n"
            . "<td class=\"input\"><small>" . _("Wann wurde die Web-Site online geschaltet?") . "</td>\n"
            . "</tr>\n"
            . "<tr class=\"input\">\n"
            . "<th class=\"input\">" . _("Email des Webmaster") . ":</th>\n"
            . "<td class=\"input\"><input type=\"text\" name=\"xadminmail\" value=\"{$this->_system->config->adminmail}\" size=\"40\" /></td>\n"
            . "<td class=\"input\"><small>" . _("An diese Emailadresse werden Mitteilungen des Systems versandt.") . "</td>\n"
            . "</tr>\n"
            . "</table>\n";


//$site_logo = "logo.gif";
//$slogan = "PHP Portal System";
//$startdate = "January 2006";
//$adminmail = "hd@phportals.de";
//$anonpost = 0;
//$Default_Theme = "PHPortalS";
//$foot1 = "Copyright";
//$foot2 = "Ich Du Sie";
//$foot3 = "Er und ich";
//$foot4 = "This web site was made with <a href=\"http://phpnuke.org\">PHP-Nuke</a>, a web portal system written in PHP. PHP-Nuke is Free Software released under the <a href=\"http://www.gnu.org\">GNU/GPL license</a>.";
//$commentlimit = 4096;
//$anonymous = "Anonymous";
//$minpass = 5;
//$pollcomm = 1;
//$articlecomm = 1;
//$verbose = 1;


        closeTable();


        $this->footer();
    }
}



?>
