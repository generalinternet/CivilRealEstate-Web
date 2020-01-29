<?php

class AbstractGI_LogFactory {
    
    /** @var GI_Log[] */
    protected static $logObjs = array();
    
    public static function startLog($logName = 'main'){
        SessionService::setValue(array(
            'log',
            $logName,
        ), array());
        return true;
    }
    
    public static function addToLog($value, $logName = 'main'){
        static::startLog($logName);
        $logData = SessionService::getValue(array(
            'log',
            $logName
        ));
        if (empty($logData)) {
            $logData = array();
        }
        $logData[] = array(
            'data' => $value,
            'time' => GI_Time::getDateTime()
        );
        $log = static::getLog($logName);
        $log->setLogData($logData);
        
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
        $sessionLogs = SessionService::getValue('log');
        if (!empty($sessionLogs)) {
            foreach ($sessionLogs as $logName => $log) {
                $logs[] = static::getLog($logName);
            }
        }
        return $logs;
    }
    
    public static function getLogData($logName = 'main'){
        static::startLog($logName);
        return SessionService::getValue(array(
            'log',
            $logName,
        ));
    }
    
    public static function dumpLogData($logName = 'main'){
        SessionService::unsetValue(array(
            'log',
            $logName,
        ));
        if(isset(static::$logObjs[$logName])){
            unset(static::$logObjs[$logName]);
        }
        return true;
    }
    
    public static function dumpAllLogData(){
        $sessionLog = SessionService::getValue('log');
        if (!empty($sessionLog)) {
            foreach ($sessionLog as $logName => $log) {
                static::dumpLog($logName);
            }
        }
        return true;
    }
    
}
