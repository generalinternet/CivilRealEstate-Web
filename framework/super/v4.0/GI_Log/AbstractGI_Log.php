<?php

class AbstractGI_Log {
    
    protected $logName = NULL;
    protected $logData = NULL;
    
    public function __construct($logName, $logData) {
        $this->setLogName($logName);
        $this->setLogData($logData);
    }
    
    /**
     * @param string $logName
     * @return \AbstractGI_Log
     */
    public function setLogName($logName){
        $this->logName = $logName;
        return $this;
    }
    
    /** @return string */
    public function getLogName(){
        return $this->logName;
    }
    
    /**
     * @param array $logData
     * @return \AbstractGI_Log
     */
    public function setLogData($logData){
        $this->logData = $logData;
        return $this;
    }
    
    /**
     * @param mixed $value
     * @return \AbstractGI_Log
     */
    public function addToLog($value){
        $logName = $this->getLogName();
        GI_LogFactory::addToLog($value, $logName);
        return $this;
    }
    
    public function getLogRowHead(){
        $row = '';
        $row .= '<span class="flex_row flex_head">';
        $row .= '<span class="flex_col sml">Key</span>';
        $row .= '<span class="flex_col med">Time</span>';
        $row .= '<span class="flex_col">Data</span>';
        $row .= '</span>';
        return $row;
    }
    
    public function getLogRows(){
        $logString = '';
        foreach($this->logData as $key => $data){
            $logString .= $this->getLogRow($key, $data);
        }
        return $logString;
    }
    
    public function getLogRow($key, $data){
        $logTime = '--';
        if(isset($data['time'])){
            $logTime = GI_Time::formatDateTimeForDisplay($data['time']);
        }
        $logValue = $data;
        if(isset($data['data'])){
            $logValue = $data['data'];
        }
        $row = '';
        $row .= '<span class="flex_row">';
        $row .= '<span class="flex_col sml">' . $key . '</span>';
        $row .= '<span class="flex_col med">' . $logTime . '</span>';
        $row .= '<span class="flex_col">' . print_r($logValue, true) . '</span>';
        $row .= '</span>';
        return $row;
    }
    
    public function dumpLog(){
        $logName = $this->getLogName();
        return GI_LogFactory::dumpLogData($logName);
    }
    
}
