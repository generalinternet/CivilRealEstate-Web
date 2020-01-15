<?php
//for error handling in the system (to display warnings and notices within layout, instead of above it
function GI_ErrorHandler($errNo, $errStr, $errFile, $errLine){
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
    if (!FORCE_ERRORS_ON && !(error_reporting() & $errNo)) {
        // This error code is not included in error_reporting, so let it fall
        // through to the standard PHP error handler
        return false;
    }
    
    $error = GI_ErrorFactory::buildError($errNo, $errStr, $errFile, $errLine);
    GI_ErrorFactory::addError($error);
    return true;
}
set_error_handler('GI_ErrorHandler');
