<?php

class TAdminModuleOptimize extends TAbstractAdminModule
{

    public function getMenuItems()
    {
        if (!$this->_system->admin->radminsuper)
            return array();

        $result = array(array('sort'        => 'sys-optimize',
                              'op'          => 'optimize',
                              'title'       => _("Optimieren"),
                              'text'        => _("Optimiert die Datenbank."),
                              'image'       => 'optimize.gif'));
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

        if (!strcmp('optimize', $op))
            return true;
        return false;
    }

    /**
     * @brief Modul ausführen
     * @param string $op Die Funktion
     */
    public function run($op = '')
    {
        $this->header(_("Optimierung der Datenbank"));
        title(_("Optimierung der Datenbank"));
        openTable();

        echo "<h1 class=\"title\">" . sprintf(_("Datenbank: %s"),
                                              "<strong>" . $this->_system->config->dbname . "</strong>")
            . "</h1>\n"
            . "<table class=\"list\">\n"
            . "<tr class=\"title\">\n"
            . "<th class=\"list\">" . _("Name") . "</th>\n"
            . "<th class=\"list\">" . _("Typ") . "</th>\n"
            . "<th class=\"list\">" . _("Einträge/Größe") . "</th>\n"
            . "<th class=\"list\">" . _("Überhang") . "</th>\n"
            . "<th class=\"list\">" . _("Status") . "</th>\n"
            . "</tr>\n";

        $_even = false;
        $dbr = $this->_system->db->query("SHOW TABLE STATUS FROM ". $this->_system->config->dbname);
        foreach ($dbr as $row)
        {
            $even = ($_even ? 'even' : 'odd');
            $_even = !$_even;

            $tot_data       = $row['Data_length'];
            $tot_idx        = $row['Index_length'];
            $total          = ($row['Data_length'] + $row['Index_length']) / 1024;
            $gain           = $row['Data_free'] / 1024;
            $sql            = 'OPTIMIZE TABLE '.$row['Name'];

            // Optimieren, wenn nötig
            //
            if (!empty($gain))
                $this->_system->db->execute($sql);

            echo "<tr class=\"list-$even\">\n"
                . "<td class=\"list-$even\">$row[Name]</td>\n"
                . "<td class=\"list-$even\">$row[Type]</td>\n"
                . "<td class=\"list-$even\">$row[Rows]/" . sprintf("%0.2f KB", $total/1024) . "</td>\n"
                . "<td class=\"list-$even\">" . (empty($gain) ? '<em>'._("keiner").'</em>' : '<strong>'.sprintf("%0.3f", $gain).'</strong>') . "</td>\n"
                . "<td class=\"list-$even\">" . (empty($gain)
                            ? "<img src=\"images/reject.gif\" alt=\"" . _("Nicht optimiert") . "\" title=\"" . _("Nicht optimiert") . "\" />"
                            : "<img src=\"images/approve.gif\" alt=\"" . _("Optimiert") . "\" title=\"" . _("Optimiert") . "\" />")
                . "</td>\n"
                . "</tr>\n";

        }

        echo "</table>\n";
        closeTable();
        $this->footer();

    }
}

?>
