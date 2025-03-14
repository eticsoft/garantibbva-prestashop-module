<?php
namespace Eticsoft\Sanalpospro;

class EticTools {
    /**
     * return Prestashop native getvalue function with a fallback
    */ 
    public static function postVal($key, $default = null) {
        return \Tools::getValue($key, $default);
    }
    /**
     * return Prestashop native getvalue function with a fallback
    */
    public static function getVal($key, $default = null) {
        return \Tools::getValue($key, $default);
    }

}