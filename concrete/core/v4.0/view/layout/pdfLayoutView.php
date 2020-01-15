<?php

class PDFLayoutView extends GI_View {
    
    protected $billAddr = 'CAN';
    protected $billAddrName = NULL;
    protected $billAddrStreet = NULL;
    protected $billAddrCity = NULL;
    protected $billAddrRegion = NULL;
    protected $billAddrCountry = NULL;
    protected $billAddrCode = NULL;
    protected $billPhone = NULL;
    protected $billFax = NULL;
    protected $billEmail = NULL;
    protected $showPrintDate = false;
    protected $showPrintTime = false;

    public function __construct(){
        
    }
    
    public function buildView(){
        
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
    public function getPDFView($pdf = NULL){
        return parent::getHTMLView();
    }
    
    function getBillAddrName() {
        return $this->billAddrName;
    }

    function getBillAddrStreet() {
        return $this->billAddrStreet;
    }

    function getBillAddrCity() {
        return $this->billAddrCity;
    }

    function getBillAddrRegion() {
        return $this->billAddrRegion;
    }

    function getBillAddrCountry() {
        return $this->billAddrCountry;
    }

    function getBillAddrCode() {
        return $this->billAddrCode;
    }
    
    function getBillPhone() {
        return $this->billPhone;
    }

    function getBillFax() {
        return $this->billFax;
    }

    function getBillEmail() {
        return $this->billEmail;
    }

    function setBillAddr($billAddr) {
        $this->billAddr = $billAddr;
    }

    function setBillAddrName($billAddrName) {
        $this->billAddrName = $billAddrName;
    }

    function setBillAddrStreet($billAddrStreet) {
        $this->billAddrStreet = $billAddrStreet;
    }

    function setBillAddrCity($billAddrCity) {
        $this->billAddrCity = $billAddrCity;
    }

    function setBillAddrRegion($billAddrRegion) {
        $this->billAddrRegion = $billAddrRegion;
    }

    function setBillAddrCountry($billAddrCountry) {
        $this->billAddrCountry = $billAddrCountry;
    }

    function setBillAddrCode($billAddrCode) {
        $this->billAddrCode = $billAddrCode;
    }
    
    function setBillPhone($billPhone) {
        $this->billPhone = $billPhone;
    }

    function setBillFax($billFax) {
        $this->billFax = $billFax;
    }

    function setBillEmail($billEmail) {
        $this->billEmail = $billEmail;
    }
    
    public function getCustomBillAddrString(){
        $addrString = '';
        
        $billAddrName = $this->getBillAddrName();
        $billAddrStreet = $this->getBillAddrStreet();
        $billAddrCity = $this->getBillAddrCity();
        $billAddrRegion = $this->getBillAddrRegion();
        $billAddrCountry = $this->getBillAddrCountry();
        $billAddrCode = $this->getBillAddrCode();
        
        if(!empty($billAddrName)){
            $addrString .= $billAddrName;
        }
        if(!empty($addrString)){
            $addrString .= '<br/>';
        }
        if(!empty($billAddrStreet)){
            $addrString .= $billAddrStreet;
        }
        if(!empty($addrString)){
            $addrString .= '<br/>';
        }
        if(!empty($billAddrCity)){
            $addrString .= $billAddrCity;
        }
        if(!empty($billAddrRegion)){
            if(!empty($billAddrCity)){
                $addrString .= ' ';
            }
            $addrString .= $billAddrRegion;
        }
        if(!empty($billAddrCountry)){
            if(!empty($billAddrRegion)){
                $addrString .= ', ';
            }
            $addrString .= $billAddrCountry;
        }
        if(!empty($billAddrCode)){
            $addrString .= ' ' . $billAddrCode;
        }
        return $addrString;
    }
    
    public function getBillAddrString(){
        $addrString = '';
        if(!empty(BILL_ADDR_NAME)){
            $addrString .= BILL_ADDR_NAME;
        }
        if(!empty($addrString)){
            $addrString .= '<br/>';
        }
        if(!empty(BILL_ADDR_STREET)){
            $addrString .= BILL_ADDR_STREET;
        }
        if(!empty($addrString)){
            $addrString .= '<br/>';
        }
        if(!empty(BILL_ADDR_CITY)){
            $addrString .= BILL_ADDR_CITY;
        }
        if(!empty(BILL_ADDR_REGION)){
            if(!empty(BILL_ADDR_CITY)){
                $addrString .= ' ';
            }
            $addrString .= BILL_ADDR_REGION;
        }
        if(!empty(BILL_ADDR_COUNTRY)){
            if(!empty(BILL_ADDR_REGION)){
                $addrString .= ', ';
            }
            $addrString .= BILL_ADDR_COUNTRY;
        }
        if(!empty(BILL_ADDR_CODE)){
            $addrString .= ' ' . BILL_ADDR_CODE;
        }
        return $addrString;
    }
    
    public function getBillAddr2String(){
        $addrString = '';
        if(!empty(BILL_ADDR_NAME_2)){
            $addrString .= BILL_ADDR_NAME_2;
        }
        if(!empty($addrString)){
            $addrString .= '<br/>';
        }
        if(!empty(BILL_ADDR_STREET_2)){
            $addrString .= BILL_ADDR_STREET_2;
        }
        if(!empty($addrString)){
            $addrString .= '<br/>';
        }
        if(!empty(BILL_ADDR_CITY_2)){
            $addrString .= BILL_ADDR_CITY_2;
        }
        if(!empty(BILL_ADDR_REGION_2)){
            if(!empty(BILL_ADDR_CITY_2)){
                $addrString .= ' ';
            }
            $addrString .= BILL_ADDR_REGION_2;
        }
        if(!empty(BILL_ADDR_COUNTRY_2)){
            if(!empty(BILL_ADDR_REGION_2)){
                $addrString .= ', ';
            }
            $addrString .= BILL_ADDR_COUNTRY_2;
        }
        if(!empty(BILL_ADDR_CODE_2)){
            $addrString .= ' ' . BILL_ADDR_CODE_2;
        }
        return $addrString;
    }
    
    public function getHTMLHeader($pdf = NULL){
        $header = '<table class="header"><tr>';
        $header .= '<td class="first_col">';
        $header .= '<img src="resources/media/pdf_logo.png" height="40px"/>';
            if($this->billAddr == 'CAN'){
                $header .= '<table class="contact_info no_border"><tr>';
                $addrString = $this->getBillAddrString();
                if(!empty($addrString)){
                    $header .= '<td>' . $addrString . '</td>';
                }
                $header .= '<td>Phone ' . SITE_PHONE;
                    if(!empty(SITE_FAX)){
                        $header .= '<br/>Fax ' . SITE_FAX;
                    }
                    $header .= '<br/>' . SITE_EMAIL;
                $header .= '</td></tr></table>';
            } elseif($this->billAddr == 'custom') {
                $header .= '<table class="contact_info no_border"><tr>';
                $addrString = $this->getCustomBillAddrString();
                if(!empty($addrString)){
                    $header .= '<td>' . $addrString . '</td>';
                }
                $phone = $this->getBillPhone();
                $fax = $this->getBillFax();
                $email = $this->getBillEmail();
                
                $header .= '<td>';
                if(!empty($phone)){
                    $header .= 'Phone ' . $phone;
                }
                if(!empty($fax)){
                    if(!empty($phone)){
                        $header .= '<br/>';
                    }
                    $header .= 'Fax ' . $fax;
                }
                if(!empty($email)){
                    if(!empty($phone) || !empty($fax)){
                        $header .= '<br/>';
                    }
                    $header .= $email;
                }
                $header .= '</td></tr></table>';
            } else {
                $header .= '<table class="contact_info no_border"><tr>';
                $addrString = $this->getBillAddr2String();
                if(!empty($addrString)){
                    $header .= '<td>' . $addrString . '</td>';
                }
                $header .= '<td>Phone ' . SITE_PHONE_2;
                    if(!empty(SITE_FAX_2)){
                        $header .= '<br/>Fax ' . SITE_FAX_2;
                    }
                    $header .= '<br/>' . SITE_EMAIL_2;
                $header .= '</td></tr></table>';
            }
        $header .= '</td>';
        $header .= '<td class="second_col">' . $this->getRightHeaderContent() . '</td>';
        $header .= '</tr></table>';
        return $header;
    }
    
    public function getRightHeaderContent(){
        return '<h1>' . $this->outputTypeLabel . '</h1>';
    }
    
    public function getHTMLFooter($pdf = NULL){
        $footer = '<table class="footer"><tr>';
        //@todo somehow detect page count...
        $footer .= '<td class="page_number">Page {PAGENO} of {nbpg}</td>';
        $outputTypeLabel = '';
        if(isset($this->outputTypeLabel) && !empty($this->outputTypeLabel)){
            $outputTypeLabel = ' &nbsp;<b class="blue caps">'.$this->outputTypeLabel.'</b>';
        }
        if($this->showPrintTime){
            $footer .= '<td class="print_date">'.date('M jS, Y - g:i a').$outputTypeLabel.'</td>';
        } else if($this->showPrintDate){
            $footer .= '<td class="print_date">'.date('M jS, Y').$outputTypeLabel.'</td>';
        } else {
            $footer .= '<td class="print_date">'.$outputTypeLabel.'</td>';
        }
        $footer .= '</tr></table>';
        return $footer;
    }
    
}
