<?php
/**
 * Description of AbstractLabourRate
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.2
 */
abstract class AbstractLabourRate extends GI_Model {
    
    protected $fieldSuffix = NULL;
    
    /**
     * @var Currency
     */
    protected $currency = NULL;
    
    protected $showCurrency = false;


    public function getViewTitle($plural = true) {
        $title = 'Labour Rate';
        if($plural){
            $title .= 's';
        }
        return $title;
    }
    
    public function getTitle(){
        return $this->getProperty('title');
    }
    
    public function showCurrency(){
        return $this->showCurrency;
    }
    
    /**
     * @return AbstractCurrency
     */
    public function getCurrency() {
        if (empty($this->currency)) {
            $currencyId = $this->getProperty('currency_id');
            $currency = CurrencyFactory::getModelById($currencyId);
            if (!empty($currency)) {
                $this->currency = $currency;
            }
        }
        return $this->currency;
    }
    
    /**
     * @return string
     */
    public function getCurrencyRef(){
        $currency = $this->getCurrency();
        if(!empty($currency)){
            return $currency->getProperty('ref');
        }
        return NULL;
    }
    
    /**
     * @return string
     */
    public function getCurrencyName(){
        $currency = $this->getCurrency();
        if(!empty($currency)){
            return $currency->getProperty('name');
        }
        return NULL;
    }
    
    /**
     * @param string $currencyRef
     * @return AbstractLabourRate
     */
    public function setCurrencyRef($currencyRef) {
        $this->currency = NULL;
        $currency = CurrencyFactory::getModelByRef($currencyRef);
        if($currency){
            $this->currency = $currency;
            $this->setProperty('currency_id', $currency->getProperty('id'));
        }
        return $this;
    }
    
    /**
     * @param boolean $formatForDisplay
     * @param boolean $showCurrency
     * @return string
     */
    public function getWage($formatForDisplay = false, $showCurrency = false){
        $wage = $this->getProperty('wage');
        if ($formatForDisplay) {
            return $this->formatAmountForDisplay($wage, $showCurrency);
        }
        return $wage;
    }
    
    /**
     * @param boolean $formatForDisplay
     * @param boolean $showCurrency
     * @return string
     */
    public function getRate($formatForDisplay = false, $showCurrency = false){
        $rate = $this->getProperty('rate');
        if ($formatForDisplay) {
            return $this->formatAmountForDisplay($rate, $showCurrency);
        }
        return $rate;
    }
    
    public function formatAmountForDisplay($amount, $showCurrency = false){
        $currency = $this->getCurrency();
        $total = '';
        if($currency){
            $total = $currency->getProperty('symbol');
        }
        $total .= GI_StringUtils::formatMoney($amount);
        if($currency && $showCurrency && !GI_CSV::csvExporting()){
            $total .= ' (' . $currency->getProperty('name') . ')';
        }
        return $total;
    }
    
    /**
     * @param \GI_Form $form
     * @param boolean $buildForm
     * @return \LabourRateFormView
     */
    public function getFormView(\GI_Form $form, $buildForm = true) {
        $formView = new LabourRateFormView($form, $this, false);
        if($buildForm){
            $formView->buildForm();
        }
        return $formView;
    }
    
    public function handleFormSubmission(\GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            if(!$form->fieldErrorCount()){
                $title = filter_input(INPUT_POST, 'title');
                $this->setProperty('title', $title);
                
                $wage = filter_input(INPUT_POST, 'wage');
                $this->setProperty('wage', $wage);
                
                $rate = filter_input(INPUT_POST, 'rate');
                $this->setProperty('rate', $rate);
                
                $currencyId = filter_input(INPUT_POST, 'currency_id');
                if(empty($currencyId)){
                    $this->setCurrencyRef('cad');
                } else {
                    $this->setProperty('currency_id', $currencyId);
                }
                
                if($this->save()){
                    return true;
                }
            }
        }
        return false;
    }
    
}
