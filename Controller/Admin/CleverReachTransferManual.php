<?php

namespace TopConcepts\CleverReach\Controller\Admin;


use OxidEsales\Eshop\Application\Controller\Admin\AdminListController;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\Str;
use OxidEsales\Eshop\Core\UtilsView;
use TopConcepts\CleverReach\Core\CleverReachTransfer;
use TopConcepts\CleverReach\Core\Exception\GroupNotFoundException;

/**
 * Admin functions for data transfer
 */
class CleverReachTransferManual extends AdminListController
{
    /**
     * Name of the template file
     *
     * @var string
     */
    protected $_sThisTemplate = 'tc_cleverreach_transfer_manual.tpl';

    /**
     * Wie viele Datensätze sollen pro Aufruf übertragen werden
     *
     * @var int
     */
    protected $limit = 1000;

    /**
     * Values for template meta refresh url
     *
     * @var array
     */
    protected $metaRefreshValues = [
        'full'      => null,
        'iReceiver' => null,
        'function'  => null,
        'transfer'  => null,
        'refresh'   => null,
        'iStart'    => null,
        'end'       => null,
        'offset'    => null,
    ];

    /**
     * Holds a tc specific error messages as string
     *
     * @var string
     */
    protected $tcError = null;

    /**
     * Holds general exception messages as string
     *
     * @var string
     */
    protected $error = null;

    /**
     * Setzt Template Variablen für den Start der Übertragung
     *
     * @return bool
     */
    public function transfer_start()
    {
        $config = Registry::getConfig();

        $config->saveShopConfVar(
            'bool',
            'tc_cleverreach_with_orders',
            (boolean)Registry::get(Request::class)->getRequestEscapedParameter('tc_cleverreach_with_orders')
        );

        $transfer = oxNew(CleverReachTransfer::class);
        $transfer->setShopId($config->getShopId());

        // Daten sind fehlerhaft
        if (!$transfer->transferPossible()) {
            $lang          = Registry::getLang();
            $msg           = $lang->translateString('TC_CLEVERREACH_ERROR_NO_KEY');
            $this->tcError = $msg;

            return false;
        }

        // add full flag, to check for full list export
        $full = (int)Registry::get(Request::class)->getRequestEscapedParameter('tc_cleverreach_fulllist');

        $this->metaRefreshValues['full']      = $full;
        $this->metaRefreshValues['iReceiver'] = "???";
        $this->metaRefreshValues['function']  = 'transfer';
        $this->metaRefreshValues['transfer']  = 'user';
        $this->metaRefreshValues['refresh']   = 0;
        $this->metaRefreshValues['iStart']    = 0;
    }

    /**
     * Setzt Template Variablen für den Start der Übertragung
     *
     * @return bool
     */
    public function transfer_start_csv()
    {
        $config     = Registry::getConfig();
        $exportPath = Registry::get(Request::class)->getRequestEscapedParameter('tc_cleverreach_exportpath');

        $config->saveShopConfVar('str', 'tc_cleverreach_exportpath', $exportPath);

        $str = Str::getStr();

        // add an slash to end of path
        if ($str->strlen($exportPath) > 0 && $str->substr($exportPath, -1) !== '/') {
            $exportPath .= '/';
        }

        if (realpath(getShopBasePath() . $exportPath) === false) {
            $target  = getShopBasePath() . $exportPath;
            $langKey = Registry::getLang()->translateString('TC_CLEVERREACH_ERROR_PATHINVALID');
            $langKey = sprintf($langKey, $target);
            Registry::get(UtilsView::class)->addErrorToDisplay($langKey);
        }

        $config->saveShopConfVar('str', 'tc_cleverreach_exportpath', $exportPath);

        $config->saveShopConfVar(
            'bool',
            'tc_cleverreach_with_orders',
            (boolean)Registry::get(Request::class)->getRequestEscapedParameter('tc_cleverreach_with_orders')
        );

        $transfer = oxNew(CleverReachTransfer::class, null, 'CSV');
        $transfer->setTransferType('CSV');
        $transfer->setShopId($config->getShopId());

        // add full flag, to check for full list export
        $full = (int)Registry::get(Request::class)->getRequestEscapedParameter('tc_cleverreach_fulllist');

        $this->metaRefreshValues['full']      = $full;
        $this->metaRefreshValues['iReceiver'] = "???";
        $this->metaRefreshValues['function']  = 'transfer_csv';
        $this->metaRefreshValues['transfer']  = 'user';
        $this->metaRefreshValues['refresh']   = 0;
        $this->metaRefreshValues['iStart']    = 0;
    }


    /**
     * Startet den Datentransfer und stetzt View Variablen
     *
     * @return bool|void
     */
    public function transfer()
    {
        $transfer = oxNew(CleverReachTransfer::class);

        $request  = Registry::get(Request::class);
        $complete = (boolean)$request->getRequestEscapedParameter('full');
        $offset   = (int)$request->getRequestEscapedParameter('offset');

        $transfer->setOffset($offset);

        try {
            list($count, $transferResult) = $transfer->run($this->getLimit(), $complete);
        } catch (GroupNotFoundException $e) {
            $this->error                              = $e->getMessage();
            $this->_aViewData['blShowListResetPopUp'] = true;

            return;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();

            return;
        }

        if (is_array($transferResult) === true && (count($transferResult) === 0 || $transferResult[0] === false)) {
            $this->metaRefreshValues['end'] = true;
            // Transfer fertig
            $this->transferComplete();

            return;
        }

        // Daten sind fehlerhaft
        if ($transferResult === false) {
            $lang          = Registry::getLang();
            $msg           = $lang->translateString('TC_CLEVERREACH_ERROR_NO_KEY');
            $this->tcError = $msg;

            return false;

            // Alle Daten übertragen
        } elseif ($transferResult === true) {
            $this->metaRefreshValues['end'] = true;
            // Transfer fertig
            $this->transferComplete();

            return;
            // Anzeige ? Nutzer|Bestellungen
        } elseif (is_array($transferResult) === true) {
            $transferType = 'user';
            $iReceiver    = $count;

            // add full flag, to check for full list export
            $full                                 = (int)$request->getRequestEscapedParameter('full');
            $this->metaRefreshValues['full']      = $full;
            $this->metaRefreshValues['function']  = 'transfer';
            $this->metaRefreshValues['transfer']  = $transferType;
            $this->metaRefreshValues['iReceiver'] = $iReceiver;
            $this->metaRefreshValues['refresh']   = 0;
            $this->metaRefreshValues['offset']    = $this->getLimit() + (int)$request->getRequestEscapedParameter('offset');
        }
    }

    /**
     * Startet den Datentransfer und stetzt View Variablen
     *
     * @return bool|void
     */
    public function transfer_csv()
    {
        $transfer = oxNew(CleverReachTransfer::class, null, 'CSV');
        $transfer->setTransferType('CSV');

        $request  = Registry::get(Request::class);
        $complete = (boolean)$request->getRequestEscapedParameter('full');
        $offset   = (int)$request->getRequestEscapedParameter('offset');

        $transfer->setOffset($offset);

        try {
            list($count, $transferResult) = $transfer->run($this->getLimit(), $complete);
        } catch (\Exception $e) {
            $this->error = $e->getMessage();

            return;
        }

        // Daten sind fehlerhaft
        if ($transferResult === false) {
            $lang          = Registry::getLang();
            $msg           = $lang->translateString('TC_CLEVERREACH_ERROR_NO_KEY');
            $this->tcError = $msg;

            return false;

            // Alle Daten übertragen
        } elseif ($transferResult === true) {
            $this->metaRefreshValues['end'] = true;
            // Transfer fertig
            $this->transferComplete();

            return;

            // Anzeige ? Nutzer|Bestellungen
        } elseif (is_array($transferResult) === true) {

            $transferType = 'user';
            $iReceiver    = $count;

            // add full flag, to check for full list export
            $full                                 = (int)$request->getRequestParameter('full');
            $this->metaRefreshValues['full']      = $full;
            $this->metaRefreshValues['function']  = 'transfer_csv';
            $this->metaRefreshValues['transfer']  = $transferType;
            $this->metaRefreshValues['iReceiver'] = $iReceiver;
            $this->metaRefreshValues['refresh']   = 0;
            $this->metaRefreshValues['offset']    = $this->getLimit() + (int)$request->getRequestEscapedParameter('offset');
        }
    }

    /**
     * Gibt die Anzahl der Datenübertragungen zurück
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Returns starting count
     *
     * @return integer
     */
    public function getStart()
    {
        return $this->metaRefreshValues['iStart'];
    }

    /**
     * Returns the function fnc name
     *
     * @return string
     */
    public function getFunction()
    {
        return $this->metaRefreshValues['function'];
    }

    /**
     * Returns receiver count
     *
     * @return integer
     */
    public function getReceiver()
    {
        return $this->metaRefreshValues['iReceiver'];
    }

    /**
     * Returns the transfer type
     *
     * @return mixed
     */
    public function getTransfer()
    {
        return $this->metaRefreshValues['transfer'];
    }

    /**
     * Getter for shop id
     *
     * @return string
     */
    public function getShopId()
    {
        return $this->metaRefreshValues['shopid'];
    }

    /**
     * Getter for full option check
     *
     * @return boolean
     */
    public function getFull()
    {
        return $this->metaRefreshValues['full'];
    }

    /**
     * Getter for offset
     *
     * @return integer
     */
    public function getOffset()
    {
        return $this->metaRefreshValues['offset'];
    }

    /**
     * Getter for refresh status
     *
     * @return integer
     */
    public function getRefresh()
    {
        return $this->metaRefreshValues['refresh'];
    }

    /**
     * Getter for refresh status
     *
     * @return integer
     */
    public function getEnd()
    {
        return $this->metaRefreshValues['end'];
    }

    /**
     * Getter for oxid internal exceptions
     *
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Getter for top concepts exceptions
     *
     * @return string
     */
    public function getTcError()
    {
        return $this->tcError;
    }

    /**
     *
     */
    public function resetList()
    {
        Registry::getConfig()->saveShopConfVar('str', 'tcCleverReachListId', null);
    }

    /**
     * Transfer fertig
     */
    public function transferComplete()
    {
        Registry::getConfig()->saveShopConfVar('int', 'tc_cleverreach_last_transfer', date('Y-m-d H:i:s'));
    }
}
