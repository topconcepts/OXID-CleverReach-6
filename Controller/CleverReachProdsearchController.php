<?php

namespace TopConcepts\CleverReach\Controller;


use OxidEsales\Eshop\Application\Controller\FrontendController;
use TopConcepts\CleverReach\Core\Prodsearch\Handler;

/**
 * Entry point for cleverreach product search. This is accessed
 * by cleverreach backend.
 *
 * @Class CleverreachProdsearchController
 */
class CleverReachProdsearchController extends FrontendController
{
    /**
     * Handle request and output json
     *
     * @return null|void
     */
    public function render()
    {
        $handler = oxNew(Handler::class);
        header('Content-Type: application/json');
        $output = $handler->handle();
        echo $output;
        exit;
    }
}
