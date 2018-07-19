<?php

namespace TopConcepts\CleverReach\Controller;


use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\EshopCommunity\Application\Model\User;

/**
 * Extends newsletter
 * Remove users from cleverreach if they deactivate
 * newsletters in shop
 * @author top concepts
 */
class CleverReachNewsletterController extends CleverReachNewsletterController_parent
{

    /**
     * Determine user by email address and flag him as single optin user
     * @return null
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    public function singleoptin()
    {
        $email = Registry::get(Request::class)->getRequestEscapedParameter('email');
        $uid   = false;

        if ($email != "") {
            $uid = DatabaseProvider::getDb()->getOne('SELECT oxid FROM oxuser WHERE oxusername=?', [trim($email)]);
        }
        $uid = $this->singleoptinUserSave($uid, $email);

        if ($uid != false) {
            $this->singleoptinIfUid($uid);
        }
        //Avoid e-mail sending
        Registry::getSession()->setVariable("blDBOptInMailAlreadyDone", true);

        parent::addme();
        $this->_iNewsletterStatus = 5;
    }

    /**
     * Injects User-Id into POST or GET parameter
     *
     * @param string $uid
     */
    protected function singleoptinIfUid($uid)
    {
        //Inject the uid to the GET or POST
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST['uid'] = $uid;
        } else if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET') {
            $_GET['uid'] = $uid;
        }
    }

    /**
     * If mail does not long belong to a user, create a new one
     *
     * @param string $uid
     * @param string $email
     *
     * @return string
     */
    protected function singleoptinUserSave($uid, $email)
    {
        if (!$uid && $email != "") {
            $oUser                     = oxNew('oxuser');
            $oUser->oxuser__oxusername = new Field($email, Field::T_RAW);
            $oUser->oxuser__oxactive   = new Field(1, Field::T_RAW);
            $oUser->oxuser__oxrights   = new Field('user', Field::T_RAW);
            $oUser->oxuser__oxshopid   = new Field(Registry::getConfig()->getShopId(), Field::T_RAW);
            $oUser->oxuser__oxfname    = new Field("", Field::T_RAW);
            $oUser->oxuser__oxlname    = new Field("", Field::T_RAW);
            $blUserLoaded              = $oUser->save();
            if ($blUserLoaded) {
                $uid = $oUser->oxuser__oxid->value;
            }
        }

        return $uid;
    }

    /**
     * Loads user and removes him from newsletter group.
     *
     * @return null
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    public function removeme()
    {
        $email = Registry::get(Request::class)->getRequestEscapedParameter('email');
        $uid   = Registry::get(Request::class)->getRequestEscapedParameter('uid');
        if ($email != "") {
            $uid = $this->removemeIfEmail($email);
        } else if ($uid != "") {
            //Search DatabaseProvider for the email
            $query = 'SELECT oxusername FROM oxuser WHERE oxid=?';
            $email = DatabaseProvider::getDb()->getOne($query, [trim($uid)]);
        }

        if (!empty($email) && !empty($uid)) {
            $oUser = oxNew(User::class);
            $oUser->load($uid);
            $oUser->getNewsSubscription();
            if($oUser->getNewsSubscription() && $oUser->getNewsSubscription()->oxnewssubscribed__oxid->value){

                $db = DatabaseProvider::getDb();
                $db->execute(
                    '
                      REPLACE INTO
                          tc_cleverreach_news
                      SET
                          newsid =?,
                          userid =?,
                          tc_cleverreach_last_edit = now(),
                          shopid =?,
                          tc_cleverreach_last_transfer="0000-00-00 00:00:00"
              ',
                    [
                        $oUser->getNewsSubscription()->oxnewssubscribed__oxid->value,
                        $uid,
                        Registry::getConfig()->getShopId(),
                    ]
                );
            }
        }

        parent::removeme();
    }

    /**
     * Get User-Id by email and inject it as POST/GET parameter
     *
     * @param string $email
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @return false|mixed|string
     */
    protected function removemeIfEmail($email)
    {
        //Search DatabaseProvider for the user
        $query = 'SELECT oxid FROM oxuser WHERE oxusername=?';
        $uid   = DatabaseProvider::getDb()->getOne($query, [trim($email)]);

        if ($uid != "") {
            //Inject the uid to the GET or POST
            if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
                $_POST['uid'] = $uid;
            } else if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET') {
                $_GET['uid'] = $uid;
            }
            return $uid;
        }

        return Registry::get(Request::class)->getRequestEscapedParameter('uid');
    }
}