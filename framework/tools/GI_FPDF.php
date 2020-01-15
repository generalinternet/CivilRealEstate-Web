<?php

class GI_FPDF extends FPDI{
    
    protected $lastColumnWidth;
    protected $lastx;
    
    function __construct($orientation='P', $unit='pt', $size='letter'){
        return parent::__construct($orientation, $unit, $size);
    }
    
    public function SetHexTextColor($hex) {
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        $this->SetTextColor($r, $g, $b);
    }
    
    protected function getColumnWidth($key){
        if(is_array($this->lastColumnWidth)){
            $columnWidth = $this->lastColumnWidth[$key];
        } else {
            if(empty($this->lastColumnWidth)){
                $columnWidth = $this->LineWidth;
            } else {
                $columnWidth = $this->lastColumnWidth;
            }
        }
        return $columnWidth;
    }
    
    public function AddTableHeader($header, $columnWidths = NULL){
        
        if(!empty($columnWidths)){
            $this->lastColumnWidth = $columnWidths;
        }
        
        $this->AddTableRow($header);
    }
    
    public function AddTableRow($data){
        $this->lastx = $this->x;
        foreach($data as $key => $column){
            $columnWidth = $this->getColumnWidth($key);
            $this->Cell($columnWidth,$this->FontSize,$column);
        }
        $this->RowBr();
    }
    
    public function RowBr($height = NULL){
        $this->x = $this->lastx;
        if($height===null){
            $this->y += $this->lasth;
        } else {
            $this->y += $height;
        }
    }

}
