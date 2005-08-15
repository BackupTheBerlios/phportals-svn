<?php

/**
 * @file
 * @brief Exceptions
 * @ingroup err
 * @author Hinrich Donner
 * @version 1
 */

/**
 * @brief Exception: Fehlende Datei
 * @ingroup err
 * @author Hinrich Donner
 * @version 1
 */
class EFileNotFound extends Exception
{
    /**
     * @brief Der Dateiname
     * @var string
     */
    protected $filename;

    /**
     * @brief Konsturktor
     * @param string $filename Der Dateiname
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
        $message = sprintf(_("File '%s' not found!"), $filename);
        parent::__construct($message);
    }

    /**
     * @brief Dateiname
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }
}

// $Log$
//
?>
