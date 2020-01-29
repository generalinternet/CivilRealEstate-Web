<?php

class GI_LogFile {
    
    protected $fileName;
    protected $logPath;
    protected $path = NULL;
    protected $logFilePath = NULL;
    protected $overWrite = false;
    protected $addToExisting = false;
    protected static $logExporting = false;
    
    public function __construct($fileName) {
        $slashPos = strrpos($fileName, '/');
        $this->path = ltrim(substr($fileName, 0, $slashPos), '/');
        
        if($slashPos !== false){
            $fileName = substr($fileName, $slashPos + 1);
        }
        
        $fileNameParts = explode('.', $fileName);
        if(end($fileNameParts) !== 'txt'){
            $fileName = $fileName .= '.txt';
        }
        $this->fileName = $fileName;
    }
    
    public static function addToDebugLog($content = '', $lineBreak = true){
        if(defined('DEBUG_LOG_MODE') && DEBUG_LOG_MODE){
            if(!defined('DEBUG_LOG_NAME')){
                $dateTime = GI_Time::getDateTime();
                $logFileName = GI_Sanitize::filename('debug_log_' . $dateTime);
                define('DEBUG_LOG_NAME', $logFileName);
                $debugLog = new GI_LogFile($logFileName);
                $debugLog->addContent('###### NEW DEBUG LOG ######');
                $debugLog->addContent('Controller: ' . GI_URLUtils::getAttribute('controller'));
                $debugLog->addContent('Action: ' . GI_URLUtils::getAttribute('action'));
                $debugLog->addContent('Attributes: ' . print_r(GI_URLUtils::getAttributes(), true));
            }
            $debugLog = new GI_LogFile(DEBUG_LOG_NAME);
            $debugLog->setAddToExisting(true);
            $debugLog->addContent($content, $lineBreak);
        }
    }
    
    public static function setLogExporting($logExporting){
        static::$logExporting = $logExporting;
    }
    
    public static function logExporting(){
        return static::$logExporting;
    }
    
    public function setOverWrite($overWrite){
        $this->overWrite = $overWrite;
        return $this;
    }
    
    public function setAddToExisting($addToExisting){
        $this->addToExisting = $addToExisting;
        return $this;
    }
    
    public function makeLogFile(){
        $logPath = $this->getLogPath();
        $logFilePath = $logPath . '/' . $this->fileName;
        
        if($this->overWrite && !$this->addToExisting){
            if(file_exists($logFilePath)){
                unlink($logFilePath);
            }
        } elseif(!$this->addToExisting){
            $dotPos = strrpos($this->fileName, '.');
            if ($dotPos) {
                $name = substr($this->fileName, 0, $dotPos);
                $ext = substr($this->fileName, $dotPos);
            } else {
                $name = $this->fileName;
                $ext = '.txt';
            }
            $newPath = $logPath . '/' . $this->fileName;
            
            $newName = $this->fileName;
            $counter = 0;
            while (file_exists($newPath)) {
                $newName = $name . '_' . $counter . $ext;
                $newPath = $logPath . '/' . $newName;
                $counter++;
            }
            $this->fileName = $newName;
            $logFilePath = $newPath;
        }
        
        touch($logFilePath);
        chmod($logFilePath, 0777);
        
        $this->logFilePath = $logFilePath;
        
        return $logFilePath;
    }
    
    public function getLogPath(){
        if(is_null($this->logPath)){
            $userId = Login::getUserId();
            if(empty($userId)){
                $userId = 0;
            }
            $localPath = 'tempData/logs/user/' . Login::getUserId();
            if(!empty($this->path)){
                $localPath .= '/' . $this->path;
            }
            File::createTempDataFolders($localPath);
            $this->logPath = $localPath;
        }
        return $this->logPath;
    }
    
    public function getLogFilePath(){
        if(is_null($this->logFilePath)){
            $this->makeLogFile();
        }
        return $this->logFilePath;
    }
    
    public function addContent($content, $lineBreak = true){
        $finalContent = '[' . GI_Time::getDateTime() . '] ' . $content;
        if($lineBreak){
            $finalContent = $finalContent . "\n";
        }
        $logFilePath = $this->getLogFilePath();
        file_put_contents($logFilePath, $finalContent, FILE_APPEND);
        return $this;
    }
    
}
