<?php

namespace TopConcepts\CleverReach\Core;


use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use TopConcepts\CleverReach\Controller\Admin\CleverReachConfig;
use TopConcepts\CleverReach\Core\Exception\CleverReachException;

/**
 * Creates a csv file from a given tc_cleverreach_collection
 */
class CleverReachCsv
{

    /**
     * Pfad zur CSV Datei
     *
     * @var string
     */
    public $filePath = 'tc_cleverreach_export';

    /**
     * Suffix der CSV Datei
     *
     * @var string
     */
    public $fileSuffix;

    /**
     * CSV Kopfzeile
     *
     * @var array
     */
    protected $csvHead = array();

    /**
     * Anzahl der durchgeführten Dateneinträge
     *
     * @var int
     */
    private $entrys = 0;

    /**
     * CSV Datei
     *
     * @var resource
     */
    private $fileHandler;

    /**
     * Aktive Datei
     *
     * @var string
     */
    private $activeFile;

    /**
     * Seperator
     *
     * @var string
     */
    private $delimiter = ';';

    /**
     * Instance object
     *
     * @var CleverReachCsv
     */
    private static $instance = null;

    /**
     * Instance getter of singleton
     *
     * @return CleverReachCsv
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = oxNew(self::class);
        }

        return self::$instance;
    }

    /**
     * Erstellt den CSV Kopf
     *
     * @param array $header
     */
    public function setCSVHead($header)
    {
        $this->csvHead = $header;
    }

    /**
     * Führt alle Aktionen zum erstellen einer CSV Datei durch
     *
     * @param CleverReachCollection $collection
     *
     * @return array
     */
    public function createCSVFile(CleverReachCollection $collection)
    {
        $iterator = $collection->getFilteredIterator();
        $iterator->rewind();

        $this->start(array_keys($iterator->current()));

        foreach ($iterator as $element) {
            fputcsv($this->fileHandler, $element, $this->delimiter);
        }

        $this->stop();

        foreach ($collection as $key => $element) {
            $collection->addTransferred($key);
        }
        $collection->setTransfer();

        return array();
    }

    /**
     * Öffnet die CSV Datei und schreibt die Kopfzeile
     *
     * @param string $header
     * @throws CleverReachException
     */
    protected function start($header)
    {
        // force creation of current specified csv dir
        if ($this->hasFileDir() === false) {
            $this->createFileDir();
        }

        $filePath = $this->getFilePath();
        $offset   = Registry::get(Request::class)->getRequestEscapedParameter('offset');

        if ($offset === '') {
            @unlink($filePath);
        }

        if (file_exists($filePath) === false) {

            if (
                $this->entrys === 0 ||
                $filePath !== $this->activeFile
            ) {
                $this->entrys = 0;
                $this->setCSVHead($header);

                $this->fileHandler = @fopen($filePath, 'w');
                $this->fileWritable();
                $this->activeFile = $filePath;

                fputcsv($this->fileHandler, $this->csvHead, $this->delimiter);

                return;
            }
        }

        $this->fileHandler = @fopen($filePath, 'a');
        $this->fileWritable();
    }

    /**
     * Schließt die CSV Datei
     */
    protected function stop()
    {
        fclose($this->fileHandler);
        exec('chmod 664 ' . $this->getFilePath());

        $this->entrys++;
    }

    /**
     * Ist Datei beschreibbar
     *
     * @throws CleverReachException
     */
    protected function fileWritable()
    {

        if ($this->fileHandler === false) {
            $lang = Registry::getLang();
            $msg  = $lang->translateString('TC_CLEVERREACH_ERROR_NO_PATH2') . ': ' . $this->getFilePath();
            throw new CleverReachException($msg, 31);
        }
    }

    /**
     * Setzt den Pfad zur CSV Datei (ohne Dateiendung!)
     *
     * @param string $filePath
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * Setzt den Suffix der CSV Datei
     *
     * @param string $suffix
     */
    public function setFileSuffix($suffix)
    {
        $this->fileSuffix = $suffix;
    }

    /**
     * Liefert den Pfad zur CSV Datei
     *
     * @return string
     * @throws CleverReachException
     */
    public function getFilePath()
    {

        $filePath = Registry::getConfig()->getConfigParam(CleverReachConfig::SETTING_EXPORT_PATH);
        if ($filePath === null) {
            throw new CleverReachException('CSV Pfad ist nicht angegeben', 32);
        }

        return getShopBasePath() . $filePath . $this->filePath . '_' . $this->getFileSuffix() . '.csv';
    }

    /**
     * Liefert den Suffix der CSV Datei
     *
     * @return string
     */
    public function getFileSuffix()
    {
        return $this->fileSuffix;
    }

    /**
     * Check if csv file dir exists
     *
     * @return bool
     */
    public function hasFileDir()
    {
        $filePath = Registry::getConfig()->getConfigParam(CleverReachConfig::SETTING_EXPORT_PATH);
        if (file_exists(getShopBasePath() . $filePath) === true) {
            return true;
        }

        return false;

    }

    /**
     * Create a new csv file dir
     *
     * @return void
     * @throws CleverReachException
     */
    public function createFileDir()
    {

        $filePath = Registry::getConfig()->getConfigParam(CleverReachConfig::SETTING_EXPORT_PATH);
        $filePath = getShopBasePath() . $filePath;

        @mkdir($filePath);
        $msg = Registry::getLang()->translateString('TC_CLEVERREACH_ERROR_NO_PATH2') . ': ' . $filePath;
        if (file_exists($filePath) === false) {
            throw new CleverReachException($msg);
        }
    }
}
