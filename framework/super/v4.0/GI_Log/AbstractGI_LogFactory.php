<?php

class AbstractGI_LogFactory {
    
    /** @var GI_Log[] */
    protected static $logObjs = array();
    
    public static function startLog($logName = 'main'){
        if(!isset($_SESSION['log'])){
            $_SESSION['log'] = array();
        }
        if(!isset($_SESSION['log'][$logName])){
            $_SESSION['log'][$logName] = array();
        }
        return true;
    }
    
    public static function addToLog($value, $logName = 'main'){
        static::startLog($logName);
        $_SESSION['log'][$logName][] = array(
            'data' => $value,
            'time' => GI_Time::getDateTime()
        );
        $log = static::getLog($logName);
        $log->setLogData($_SESSION['log'][$logName]);
        return true;
    }
    
    public static function getLog($logName = 'main'){
        if(!isset(static::$logObjs[$logName])){
            $logData = static::getLogData($logName);
            static::$logObjs[$logName] = new GI_Log($logName, $logData);
        }
        return static::$logObjs[$logName];
    }
    
    public static function getLogs(){
        $logs = array();
        if(isset($_SESSION['log'])){
            foreach($_SESSION['log'] as $logName => $log){
                $logs[] = static::getLog($logName);
            }
        }
        return $logs;
    }
    
    public static function getLogData($logName = 'main'){
        static::startLog($logName);
        return $_SESSION['log'][$logName];
    }
    
    public static function dumpLogData($logName = 'main'){
        if(isset($_SESSION['log'][$logName])){
            unset($_SESSION['log'][$logName]);
        }
        if(isset(static::$logObjs[$logName])){
            unset(static::$logObjs[$logName]);
        }
        return true;
    }
    
    public static function dumpAllLogData(){
        if(isset($_SESSION['log'])){
            foreach($_SESSION['log'] as $logName => $log){
                static::dumpLog($logName);
            }
        }
        return true;
    }
    
}
