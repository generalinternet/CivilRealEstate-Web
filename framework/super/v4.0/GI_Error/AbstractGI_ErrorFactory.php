<?php

abstract class AbstractGI_ErrorFactory{
    
    /**
     * @var GI_Error[] 
     */
    protected static $errors = array();
    
    public static function addError(GI_Error $error){
        static::$errors[] = $error;
    }
    
    /**
     * @return GI_Error[]
     */
    public static function getErrors(){
        return static::$errors;
    }
    
    public static function getErrorCount(){
        return count(static::$errors);
    }
    
    public static function getErrorString(){
        $errorString = '';
        foreach(static::$errors as $error){
            $errorString .= $error->getError();
        }
        return $errorString;
    }
    
    /**
     * @param int $errNo
     * @return GI_Error
     */
    public static function buildError($errNo, $errStr = NULL, $errFile = NULL, $errLine = NULL){
        /*
        * Error Numbers
        * 2 = warning (non fatal run-time errors) E_WARNING
        * 8 = notice (found something that might be an error) E_NOTICE
        * 256 = user_error (fatal error using "trigger_error") E_USER_ERROR
        * 512 = user_warning (non fatal error using "trigger_error") E_USER_WARNING
        * 1024 = user_notice (notice using "trigger_error") E_USER_NOTICE
        * 2048 = strict E_STRICT
        * 4096 = recoverable_error (catchable fatal error) E_RECOVERABLE_ERROR
        * 8191 = all (all errors) E_DEPRECATED
        */
        $error = new GI_Error($errNo, $errStr, $errFile, $errLine);
        return $error;
    }
    
}
