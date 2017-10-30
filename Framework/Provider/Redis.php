<?php
namespace Framework\Provider;
class Redis{
    private static $redis;
    /*
    * @ Redis Base ²¿·Ö
    */
    public static function getRedis(){
        if(!is_object(self::$redis)){
            self::$redis = new \Framework\Lib\MyRedis();
        }
        return self::$redis;
    }
}
?>