<?php

/**
 * @file
 * @brief Abstrakte Modulbasis
 * @author Hinrich Donner
 * @version 1
 * @ingroup api
 */

/**
 * @brief Abstrakte Basis für ein Modul
 * @author Hinrich Donner
 * @version 1
 * @ingroup api
 *
 * Jedes Modul @b muss von dieser Klasse abgeleitet sein.  Die abstrakten Methoden müssen
 * entsprechend überschrieben werden.
 */
abstract class TAbstractModule
{
    /**
     * @brief System-Container
     * @var TSystem
     */
    protected $_system;

    /**
     * @brief Methode, die vor der Ausgabe des Seitenkopfes aufzurufen ist
     * @var string
     */
    public $beforeHeader = null;

    /**
     * @brief Methode, die nach der Ausgabe des Seitenkopfes aufzurufen ist
     * @var string
     */
    public $afterHeader = null;

    /**
     * @brief Methode, die vor der Ausgabe des Seitenfusses aufzurufen ist
     * @var string
     */
    public $beforeFooter = null;

    /**
     * @brief Methode, die nach der Ausgabe des Seitenfusses aufzurufen ist
     * @var string
     */
    public $afterFooter = null;

    /**
     * @brief Konstruktor
     */
    public function __construct()
    {
        $this->_system = TSystem::instance();

        // Globale Variablen initialisieren
        //
        $GLOBALS['home']        = 0;
        $GLOBALS['index']       = 0;
    }

    /**
     * @brief Eigenschaften lesen (Magic)
     * @param string $name Der Name der Eigenschaft
     * @return mixed
     * @sa TAbstractModule::__set()
     */
    public function __get($name)
    {
        switch ($name)
        {
            case 'home':
                $result = $GLOBALS['home'];
                break;

            case 'index':
                $result = $GLOBALS['index'];
                break;

            default:
                $result = null;
        }
        return $result;
    }

    /**
     * @brief Eigenschaften setzen (Magic)
     * @param string $name Der Name der Eigenschaft
     * @param mixed $value Der Wert
     * @sa TAbstractModule::__get()
     */
    public function __set($name, $value)
    {
        switch ($name)
        {
            case 'home':
                $GLOBALS['home'] = (int) $value;
                break;

            case 'index':
                $GLOBALS['index'] = (int) $value;
                break;
        }
        return $result;
    }

    /**
     * @brief Seitenkopf ausgeben
     * @param string $title Der Seitentitel
     * @sa TAbstractModule::Footer()
     */
    public function header($title = '')
    {
        global $pagetitle;

        if (empty($title))
            $pagetitle = $this->config->sitename . ' - ' . $this->config->slogan;
        else
            $pagetitle = $this->config->sitename . ' - ' . $title;

        if ((null !== $this->beforeHeader) && method_exists($this, $this->beforeHeader))
        {
            $method = $this->beforeHeader;
            $this->$method();
        }

        include 'header.php';

        if ((null !== $this->afterHeader) && method_exists($this, $this->afterHeader))
        {
            $method = $this->afterHeader;
            $this->$method();
        }
    }

    /**
     * @brief Seitenfuß ausgeben
     * @sa TAbstractModule::Header()
     */
    public function footer()
    {
        if ((null !== $this->beforeFooter) && method_exists($this, $this->beforeFooter))
        {
            $method = $this->beforeFooter;
            $this->$method();
        }

        include 'footer.php';

        if ((null !== $this->afterFooter) && method_exists($this, $this->afterFooter))
        {
            $method = $this->afterFooter;
            $this->$method();
        }
    }

    /**
     * @brief Prüfen, ob die angefragte Funktion ausgeführt werden kann
     * @param string $op Die Funktion
     * @return bool
     */
    abstract public function canHandleFunction($op);

    /**
     * @brief Modul ausführen
     * @param string $op Die Funktion
     */
    abstract public function run($op = '');
}

?>
