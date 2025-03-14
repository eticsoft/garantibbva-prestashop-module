<?php
namespace Eticsoft\Sanalpospro;

class EticContext
{
    public static function get($key)
    {   
        return \Context::getContext()->$key;
    }
}