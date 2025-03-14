<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class GarantiBBVAPaymentHandlerModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        // Deny direct browser access
        if (
            !isset($_SERVER['HTTP_REFERER'])
            || strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) === false
        ) { 
            header('HTTP/1.0 404 Not Found');
            exit;
        }

        $api = \Eticsoft\Sanalpospro\InternalApi::getInstance()->setModule($this->module)->run();
        header('Content-Type: application/json');
        die(json_encode($api->response));
    }
}
