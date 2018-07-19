<?php
/**
 * Cronjob for sending user data to cleverreach
 */

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;
use TopConcepts\CleverReach\Controller\Admin\CleverReachConfig;
use TopConcepts\CleverReach\Core\CleverReachTransfer;
use TopConcepts\CleverReach\Core\Exception\CleverReachException;
use TopConcepts\JobInstance\Core\Exception\JobInstanceException;
use TopConcepts\JobInstance\Core\JobInstance;
use TopConcepts\CleverReach\Controller\CleverReachOAuthController;

set_time_limit(0);
error_reporting(E_ALL ^ E_NOTICE);

function tc_getShopBasePath()
{
    return __DIR__ . '/../../../../';
}

/**
 * @param $bCSV
 * @param $transferType
 * @return void
 */
function handleScriptArguments(&$bCSV, &$transferType)
{
    if ((is_array($_SERVER['argv']))) {
        $bCSV = in_array('csv', $_SERVER['argv']);
    }

    $transferType = 'CSV';
    if ($bCSV == false) {
        $bCSV = null;
        $transferType = 'API';
    }
}

function setOrderOption($shopId=null)
{
    if ((is_array($_SERVER['argv']))) {
        oxRegistry::getConfig()->saveShopConfVar(
            'bool',
            CleverReachConfig::SETTING_ORDER_FLAG,
            in_array('orders', $_SERVER['argv']),
            $shopId
        );
    }
}

try {
    if (!file_exists(tc_getShopBasePath() . 'index.php')) {
        throw new \Exception('Dateipfad ist falsch', 30);
    }

    require_once tc_getShopBasePath() . 'bootstrap.php';

    // tc_jobinstance
    try {
        /* @var $instance JobInstance */
        $instance = oxNew(JobInstance::class, 'tc_cleverreach');
        $instance->initialize();
    } catch (JobInstanceException $e) {
        throw $e;
    } catch (Exception $e) {
        throw new StandardException('tc jobinstance ist nicht richtig installiert:' . $e->getMessage(), 40);
    }

    $bCSV = null;
    $transferType = null;
    handleScriptArguments($bCSV, $transferType);

    try {
        $notConnectedShops = [];
        /* @var $transfer CleverReachTransfer */
        $transfer = oxNew(CleverReachTransfer::class, null, $transferType, true);
        foreach ($transfer->restByShop as $shopId => $shop)
        {
            if($shop->authMode == false)
            {
                $notConnectedShops[] = $shopId;
            }
        }
    } catch (Exception $e) {
        throw new StandardException('tc CleverReach ist nicht richtig installiert:' . $e->getMessage(), 41);
    }

    if ($transfer->hasDataResetParameter())
        exit;

    if (!$bCSV && $transfer->transferPossible() === false)
        throw new CleverReachException('API Key oder Listen ID fehlt', 42);

    $transferResult = array();
    $i              = 0;

    $shops = DatabaseProvider::getDb()->getCol('SELECT oxid FROM oxshops WHERE oxactive = ?', array(1));

    foreach ($shops as $shopId) {
        $counter = 0;
        $hasInfo = false;
        $list = Registry::getConfig()->getShopConfVar('tcCleverReachListId', $shopId);
        if($bCSV || (!empty($list) && CleverReachOAuthController::tc_isOAuthTokenValid($shopId))) {
            if(!in_array($shopId, $notConnectedShops)){
                echo "\n\nShopId: ".$shopId."\n";
            }
            $transfer->setShopId($shopId);
            setOrderOption($shopId);
            do {
                $i++;
                $transferResult[$i] = $transfer->run(1000, false);
                if(is_array($transferResult[$i][1])){
                    $hasInfo = true;
                    if (!$bCSV) {
                        foreach ($transferResult[$i][1] as $value) {
                            echo "Id: ".$value->id.", ".$value->status."\n";
                        }
                    }
                }
                sleep(2);
                if ($bCSV && $transferResult[$i][0] > 0) {
                    $counter += $transferResult[$i][0];
                }
            } while ($transferResult[$i][0] !== 0 && $transferResult[$i][1] !== true);
            if ($bCSV && $counter > 0) {
                echo $counter." Empfänger exportiert\n";
            }
            if(!$hasInfo && !in_array($shopId, $notConnectedShops)){
                echo "\nEs wurden keine Daten aktualisiert.\n";
            }
        } else {
            echo "\n\nShopId: ".$shopId."\n";
            echo "Mit keiner Empfängerliste verbunden\n";
        }
    }
    unset($transferResult[$i]);

    echo "\nExport erfolgreich abgeschlossen.\n";


} catch (Exception $e) {
    echo "--- Error ---\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";

    // Für Entwickler
    if ((is_array($_SERVER['argv']) && in_array('debug', $_SERVER['argv']) === true)) {
        echo "File: " . $e->getFile() . "\n";
        echo "Line: " . $e->getLine() . "\n";
        echo "Trace: " . $e->getTraceAsString() . "\n";
    }
    echo "\n";
}
