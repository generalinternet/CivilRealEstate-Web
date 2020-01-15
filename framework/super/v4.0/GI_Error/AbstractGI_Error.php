<?php

abstract class AbstractGI_Error{
    
    protected $errNo = NULL;
    protected $errStr = NULL;
    protected $errFile = NULL;
    protected $errLine = NULL;
    
    public function __construct($errNo, $errStr = NULL, $errFile = NULL, $errLine = NULL){
        $this->errNo = $errNo;
        $this->errStr = $errStr;
        $this->errFile = $errFile;
        $this->errLine = $errLine;
    }
    
    public function getErrStr() {
        return $this->errStr;
    }

    public function getErrFile() {
        return $this->errFile;
    }

    public function getErrLine() {
        return $this->errLine;
    }

    public function setErrStr($errStr) {
        $this->errStr = $errStr;
        return $this;
    }

    public function setErrFile($errFile) {
        $this->errFile = $errFile;
        return $this;
    }

    public function setErrLine($errLine) {
        $this->errLine = $errLine;
        return $this;
    }
    
    public static function getErrorType($errNo){
        switch($errNo){
            case E_WARNING:
            case E_USER_WARNING:
                return 'Warning';
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
                return 'Notice';
                break;
            case E_ERROR:
                return 'Fatal Error';
                break;
            case E_STRICT:
                return 'Strict';
                break;
            case E_RECOVERABLE_ERROR:
                return 'Recoverable Error';
                break;
            case E_DEPRECATED:
                return 'Deprecated';
                break;
            default:
                return 'Error';
                break;
        }
        /*
        2 = warning (non fatal run-time errors) E_WARNING
        * 8 = notice (found something that might be an error) E_NOTICE
        * 256 = user_error (fatal error using "trigger_error") E_USER_ERROR
        * 512 = user_warning (non fatal error using "trigger_error") E_USER_WARNING
        * 1024 = user_notice (notice using "trigger_error") E_USER_NOTICE
        * 2048 = strict E_STRICT
        * 4096 = recoverable_error (catchable fatal error) E_RECOVERABLE_ERROR
        * 8191 = all (all errors) E_DEPRECATED
         * 
         */
    }
    
    public function getError(){
        $errorMsg = '<div class="gi_error">';
        $errorMsg .= '<span class="close_gi_error"><span class="icon eks"></span></span>';
        $errorMsg .= '<div class="error_type">' . static::getErrorType($this->errNo) . '</div>';
        $errorMsg .= '<div class="error_msg">' . $this->errStr . '</div>';
        $errorMsg .= '<div class="error_loc">File <span class="error_file">' . $this->errFile . '</span> Line <span class="error_line">' . $this->errLine . '</span></div>';
        $errorMsg .= '</div>';
        return $errorMsg;
    }
    
}
