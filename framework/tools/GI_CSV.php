<?php

class GI_CSV {
    
    protected $fileName;
    protected $csvPath;
    protected $path = NULL;
    protected $csvFilePath = NULL;
    protected $overWrite = false;
    protected $addToExisting = false;
    /**
     * @var UITableCol[]
     */
    protected $uiTableCols = array();
    protected static $csvExporting = false;
    
    public function __construct($fileName) {
        $slashPos = strrpos($fileName, '/');
        $this->path = ltrim(substr($fileName, 0, $slashPos), '/');
        
        if($slashPos !== false){
            $fileName = substr($fileName, $slashPos + 1);
        }
        
        $fileNameParts = explode('.', $fileName);
        if(end($fileNameParts) !== 'csv'){
            $fileName = $fileName .= '.csv';
        }
        $this->fileName = $fileName;
    }
    
    public static function setCSVExporting($csvExporting){
        static::$csvExporting = $csvExporting;
    }
    
    public static function csvExporting(){
        return static::$csvExporting;
    }
    
    public function setOverWrite($overWrite){
        $this->overWrite = $overWrite;
        return $this;
    }
    
    public function setAddToExisting($addToExisting){
        $this->addToExisting = $addToExisting;
        return $this;
    }
    
    public function makeCSVFile(){
        $csvPath = $this->getCSVPath();
        $csvFilePath = $csvPath . '/' . $this->fileName;
        
        if($this->overWrite && !$this->addToExisting){
            if(file_exists($csvFilePath)){
                unlink($csvFilePath);
            }
        } elseif(!$this->addToExisting){
            $dotPos = strrpos($this->fileName, '.');
            if ($dotPos) {
                $name = substr($this->fileName, 0, $dotPos);
                $ext = substr($this->fileName, $dotPos);
            } else {
                $name = $this->fileName;
                $ext = '.csv';
            }
            $newPath = $csvPath . '/' . $this->fileName;
            
            $newName = $this->fileName;
            $counter = 0;
            while (file_exists($newPath)) {
                $newName = $name . '_' . $counter . $ext;
                $newPath = $csvPath . '/' . $newName;
                $counter++;
            }
            $this->fileName = $newName;
            $csvFilePath = $newPath;
        }
        
        touch($csvFilePath);
        chmod($csvFilePath, 0777);
        
        $this->csvFilePath = $csvFilePath;
        
        return $csvFilePath;
    }
    
    public function getCSVFileName(){
        return $this->fileName;
    }
    
    public function getCSVPath(){
        if(is_null($this->csvPath)){
            $userId = Login::getUserId();
            if(empty($userId)){
                $userId = 0;
            }
            $localPath = 'tempData/user/' . Login::getUserId();
            if(!empty($this->path)){
                $localPath .= '/' . $this->path;
            }
            File::createTempDataFolders($localPath);
            $this->csvPath = $localPath;
        }
        return $this->csvPath;
    }
    
    public function getCSVFilePath(){
        if(is_null($this->csvFilePath)){
            $this->makeCSVFile();
        }
        return $this->csvFilePath;
    }
    
    public function addContent($content, $lineBreak = true){
        if($lineBreak){
            $content = $content . "\n";
        }
        $csvFilePath = $this->getCSVFilePath();
        file_put_contents($csvFilePath, $content, FILE_APPEND);
        return $this;
    }
    
    /**
     * @param UITableCol[] $uiTableCols
     * @return \GI_CSV
     */
    public function setUITableCols($uiTableCols, $addHeader = false){
        $this->uiTableCols = $uiTableCols;
        if($addHeader){
            $this->addHeader();
        }
        return $this;
    }
    
    protected function formatCell($cellContent){
        return '"' . str_replace('"', '""', $cellContent) . '"';
    }
    
    /**
     * Adds a header line into the CSV file using the set UITableCols
     * 
     * @return \GI_CSV
     */
    public function addHeader() {
        $headerTitles = array();
        foreach ($this->uiTableCols as $uiTableCol) {
            $headerTitles[] = $this->formatCell($uiTableCol->getHeaderTitle());
        }
        $this->addContent(implode(',', $headerTitles));
        return $this;
    }
    
    public function addModelRow(GI_Model $model){
        static::setCSVExporting(true);
        $rowContent = array();
        foreach ($this->uiTableCols as $tableCol) {
            $rowContent[] = $this->formatCell($this->getModelCell($model, $tableCol));
        }
        static::setCSVExporting(false);
        $this->addContent(implode(',', $rowContent));
        return $this;
    }
    
    /**
     * @param GI_Model[] $models
     * @return \GI_CSV
     */
    public function addModelRows($models){
        foreach($models as $model){
            $this->addModelRow($model);
        }
        return $this;
    }
    
    protected function getModelCell(GI_Model $model, UITableCol $uiTableCol) {
        $methodName = $uiTableCol->getMethodName();
        $methodAttributes = $uiTableCol->getMethodAttributes();
        if (!empty($methodAttributes)) {
            $val = call_user_func_array(array(
                $model,
                $methodName
            ), $methodAttributes);
        } else {
            $val = $model->$methodName();
        }
        
        return $val;
    }
    
    /**
     * Adds a header line into the CSV file with titles provided by an array
     * 
     * @param array $headerTitles
     * @return \GI_CSV
     */
    public function addHeaderRow($headerTitles = array()){
        return $this->addRow($headerTitles);
    }
    
    /**
     * Adds a line into the CSV file with the content provided in an array
     * 
     * @param array $rowContent
     * @return \GI_CSV
     */
    public function addRow($rowContent = array()){
        $formattedContent = array();
        foreach ($rowContent as $cellContent) {
            $formattedContent[] = $this->formatCell($cellContent);
        }
        $this->addContent(implode(',', $formattedContent));
        return $this;
    }
    
}
