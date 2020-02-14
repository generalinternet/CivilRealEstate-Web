<?php
/**
 * Description of AbstractCreditCardDetailView
 *
 * @author General Internet
 * @copyright  2020 General Internet
 * @version    4.0.0
 */

abstract class AbstractCreditCardDetailView extends MainWindowView {
    
    protected $creditCardDataArray = array();
    protected $isDefault = false;
    
    public function __construct($creditCardDataArray) {
        parent::__construct();
        $this->creditCardDataArray = $creditCardDataArray;
    }
    
    public function setIsDefault($isDefault){
        $this->isDefault = $isDefault;
        return $this;
    }
    
    protected function addViewBodyContent() {
        $this->openCreditCardWrap();
            $this->openCreditCard();
                $this->addBrand();
                $this->addLastFour();
                $this->addExpiry();
            $this->closeCreditCard();
        $this->closeCreditCardWrap();
    }
    
    protected function openCreditCardWrap(){
        $class = '';
        if($this->isDefault){
            $class = 'default';
        }
        $this->addHTML('<div class="credit_card_wrap ' . $class . '">');
        return $this;
    }
    protected function openCreditCard(){
        $this->addHTML('<div class="credit_card">');
        return $this;
    }
    protected function closeCreditCardWrap(){
        $this->addHTML('</div>');
        return $this;
    }
    protected function closeCreditCard(){
        $this->addHTML('</div>');
        return $this;
    }

    protected function addBrand() {
        if (isset($this->creditCardDataArray['brand'])) {
            $brand = $this->creditCardDataArray['brand'];
        } else {
            $brand = '';
        }
        $this->addHTML('<span class="cc_brand">');
        $this->addBrandLogo($brand);
//        $this->addHTML('<span class="cc_brand_name">' . $brand . '</span>');
        $this->addHTML('</span>');
    }
    protected function addBrandLogo($brand){
        switch(strtolower($brand)){
            case 'visa':
                $logoRef = 'pf-visa';
                break;
            case 'mastercard':
                $logoRef = 'pf-mastercard';
                break;
            case 'amex':
                $logoRef = 'pf-american-express';
                break;
            case 'discover':
                $logoRef = 'pf-discover';
                break;
            case 'diners':
                $logoRef = 'pf-diners';
                break;
            case 'jcb':
                $logoRef = 'pf-jcb';
                break;
            default:
                $logoRef = 'pf-credit-card';
                break;
        }
        $this->addHTML('<span class="cc_brand_logo" title="' . $brand . '">');
        $this->addHTML('<i class="pf ' . $logoRef . '"></i>');
        $this->addHTML('</span>');
    }

    protected function addLastFour() {
        if (isset($this->creditCardDataArray['last_four'])) {
            $lastFour = $this->creditCardDataArray['last_four'];
        } else {
            $lastFour = '';
        }
        $this->addHTML('<span class="cc_last_four">');
        $this->addHTML('Ending in <b>' . $lastFour . '</b>');
        $this->addHTML('</span>');
    }

    protected function addExpiry() {
        if (isset($this->creditCardDataArray['exp_month'])) {
            $expMonth = $this->creditCardDataArray['exp_month'];
        } else {
            $expMonth = '';
        }
        if (isset($this->creditCardDataArray['exp_year'])) {
            $expYear = $this->creditCardDataArray['exp_year'];
        } else {
            $expYear = '';
        }
        $expiry = $expMonth . '/' . $expYear;
        $this->addHTML('<span class="cc_exp">');
        $this->addHTML('Expiring in <b>' .$expiry . '</b>');
        $this->addHTML('</span>');
    }

}
