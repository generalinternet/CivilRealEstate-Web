<?php

class GI_Device {
    
    protected static $detect = NULL;

    protected static function getDetect(){
        if(is_null(static::$detect)){
            static::$detect = new Mobile_Detect;
        }
        return static::$detect;
    }

    public static function isMobile(){
        $detect = static::getDetect();
        return $detect->isMobile();
    }
    
    public static function isTablet(){
        $detect = static::getDetect();
        return $detect->isTablet();
    }
    
    public static function isDesktop(){
        $detect = static::getDetect();
        if(!$detect->isMobile() && !$detect->isTablet()){
            return true;
        } else {
            return false;
        }
    }
    
    public static function is($key, $userAgent = null, $httpHeaders = null){
        $detect = static::getDetect();
        return $detect->is($key, $userAgent, $httpHeaders);
    }
    
}
