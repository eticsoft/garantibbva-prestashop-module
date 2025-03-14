<?php
/**
 * @author    Eticsoft
 * @copyright 2024 Eticsoft
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
} 
class AdminGarantiBBVAIapiController extends ModuleAdminController 
{ 
    public function init()
    {
        parent::init();
    }

    public function display()
    {
        $api = \Eticsoft\Sanalpospro\InternalApi::getInstance()->run();
        header('Content-Type: application/json');
        die(json_encode($api->response));
    }
}