<?php
namespace Eticsoft\Sanalpospro;

class EticConfig
{
    public static function get($key)
    {
        return \Configuration::get($key);
    }

    public static function set($key, $value)
    {
        return \Configuration::updateValue($key, $value);
    }
}