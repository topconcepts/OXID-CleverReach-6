<?php

use OxidEsales\Eshop\Application\Controller\NewsletterController;
use OxidEsales\Eshop\Application\Model\NewsSubscribed;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\User;
use TopConcepts\CleverReach\Controller\Admin\CleverReachConfig;
use TopConcepts\CleverReach\Controller\Admin\CleverReachFrames;
use TopConcepts\CleverReach\Controller\Admin\CleverReachList;
use TopConcepts\CleverReach\Controller\Admin\CleverReachManualCsv;
use TopConcepts\CleverReach\Controller\Admin\CleverReachTransferManual;
use TopConcepts\CleverReach\Controller\CleverReachNewsletterController;
use TopConcepts\CleverReach\Controller\CleverReachOAuthController;
use TopConcepts\CleverReach\Controller\CleverReachProdsearchController;
use TopConcepts\CleverReach\Model\CleverReachNewsSubscribed;
use TopConcepts\CleverReach\Model\CleverReachOrder;
use TopConcepts\CleverReach\Model\CleverReachUser;

$sMetadataVersion = '2.0';

$aModule = [
    'id'          => 'tccleverreach',
    'title'       => [
        'de' => 'Offizieller CleverReach® Connector',
        'en' => 'Official CleverReach® Connector',
    ],
    'description' => 'Dieses Modul ermöglicht es unkompliziert Kundendaten und Bestellungen nach CleverReach® zu übertragen.',
    'thumbnail'   => 'tc_logo.jpg',
    'version'     => '4.1.0',
    'author'      => 'top concepts GmbH',
    'email'       => 'support@topconcepts.com',
    'url'         => 'https://www.topconcepts.de',
    'controllers' => [
        'CleverReachFrames'               => CleverReachFrames::class,
        'CleverReachList'                 => CleverReachList::class,
        'CleverReachConfig'               => CleverReachConfig::class,
        'CleverReachManualCsv'            => CleverReachManualCsv::class,
        'CleverReachTransferManual'       => CleverReachTransferManual::class,
        'CleverReachOAuthController'      => CleverReachOAuthController::class,
        'CleverReachProdsearchController' => CleverReachProdsearchController::class,
    ],
    'extend'      => [
        NewsletterController::class => CleverReachNewsletterController::class,
        NewsSubscribed::class       => CleverReachNewsSubscribed::class,
        Order::class                => CleverReachOrder::class,
        User::class                 => CleverReachUser::class,
    ],
    'templates'   => [
        // Admin
        'tc_cleverreach_config.tpl'          => 'tc/tccleverreach/views/admin/tpl/tc_cleverreach_config.tpl',
        'tc_cleverreach_transfer_manual.tpl' => 'tc/tccleverreach/views/admin/tpl/tc_cleverreach_transfer_manual.tpl',
        'tc_cleverreach_frames.tpl'          => 'tc/tccleverreach/views/admin/tpl/tc_cleverreach_frames.tpl',
        'tc_cleverreach_list.tpl'            => 'tc/tccleverreach/views/admin/tpl/tc_cleverreach_list.tpl',
        'tc_cleverreach_manual_csv.tpl'      => 'tc/tccleverreach/views/admin/tpl/tc_cleverreach_manual_csv.tpl',
        'tc_cleverreach_oauth.tpl'           => 'tc/tccleverreach/views/admin/tpl/tc_cleverreach_oauth.tpl',
    ],
    'events'      => [
        'onActivate'   => '\TopConcepts\CleverReach\Core\CleverReachModuleHandler::onActivate',
        'onDeactivate' => '\TopConcepts\CleverReach\Core\CleverReachModuleHandler::onDeactivate',
    ],
];
