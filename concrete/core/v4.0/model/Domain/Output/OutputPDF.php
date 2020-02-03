<?php

class OutputPDF extends Mpdf\Mpdf{
    
    protected $curMGT = 42;
    protected $curMGB = 20;
    protected $curMGL = 10;
    protected $curMGR = 10;
    
    public function __construct(array $config = []) {
        $defaultConfig = array(
            'mode' => 'utf-8',
            'format' => 'Letter',
            'default_font_size' => 0,
            'default_font' => '',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 26,
            'margin_bottom' => 26,
            'margin_header' => 10,
            'margin_footer' => 10,
            'orientation' => 'P',
        );

        foreach ($defaultConfig as $key => $val) {
            if (!isset($config[$key])) {
                $config[$key] = $val;
            }
        }
        parent::__construct($config);
        $stylesheet = file_get_contents('resources/css/pdf.css');
        $this->WriteHTML($stylesheet,1);
    }
    
    public function addHTMLFromView(PDFLayoutView $view){
        $this->setViewHeader($view);
        $this->setViewFooter($view);
        
        $body = $view->getPDFView($this);
        if($body){
            $this->WriteHTML($body);
        }
    }
    
    public function setEmptyHeader(){
        $this->SetMargins(10, 10, 10);
        $this->SetHTMLHeader('');
    }
    
    public function setEmptyFooter(){
        $this->SetHTMLFooter('');
    }
    
    public function setViewHeader(PDFLayoutView $view){
        $this->SetMargins($this->curMGL, $this->curMGR, $this->curMGT);
        $header = $view->getHTMLHeader($this);
        if($header){
            $this->SetHTMLHeader($header);
        }
    }
    
    public function setViewFooter(PDFLayoutView $view){
        $footer = $view->getHTMLFooter($this);
        if ($footer) {
            $this->SetHTMLFooter($footer);
        }
    }

    public function setCurMGT($curMGT) {
        $this->curMGT = $curMGT;
    }

}
