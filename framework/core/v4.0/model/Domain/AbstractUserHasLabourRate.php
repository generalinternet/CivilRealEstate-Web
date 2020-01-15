<?php
/**
 * Description of AbstractUserHasLabourRate
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.1
 */
abstract class AbstractUserHasLabourRate extends GI_Model {
    
    protected $fieldSuffix = NULL;
    /**
     * @var AbstractLabourRate
     */
    protected $labourRate = NULL;
    
    public function getViewTitle($plural = true) {
        $title = 'User Labour Rate';
        if($plural){
            $title .= 's';
        }
        return $title;
    }
    
    /**
     * @return AbstractLabourRate
     */
    public function getLabourRate(){
        if(is_null($this->labourRate)){
            $labourRateId = $this->getProperty('labour_rate_id');
            $labourRate = LabourRateFactory::getModelById($labourRateId);
            if (!empty($labourRate)) {
                $this->labourRate = $labourRate;
            }
        }
        return $this->labourRate;
    }
    
    public function getTitle(){
        $title = $this->getProperty('title');
        if(empty($title)){
            $labourRate = $this->getLabourRate();
            if($labourRate){
                $title = $labourRate->getTitle();
            }
        }
        return $title;
    }
    
    public function getWage($formatForDisplay = false, $showCurrency = false){
        $labourRate = $this->getLabourRate();
        $wage = $this->getProperty('wage');
        if(empty($wage)){
            if($labourRate){
                $wage = $labourRate->getWage();
            }
        }
        if($formatForDisplay && $labourRate){
            return $labourRate->formatAmountForDisplay($wage, $showCurrency);
        }
        return $wage;
    }
    
    public function getRate($formatForDisplay = false, $showCurrency = false){
        $labourRate = $this->getLabourRate();
        $rate = $this->getProperty('rate');
        if(empty($rate)){
            if($labourRate){
                $rate = $labourRate->getRate();
            }
        }
        if($formatForDisplay && $labourRate){
            return $labourRate->formatAmountForDisplay($rate, $showCurrency);
        }
        return $rate;
    }
    
    /**
     * @param \GI_Form $form
     * @param boolean $buildForm
     * @return \UserHasLabourRateFormView
     */
    public function getFormView(\GI_Form $form, $buildForm = true) {
        $formView = new UserHasLabourRateFormView($form, $this, false);
        if($buildForm){
            $formView->buildForm();
        }
        return $formView;
    }
    
    public function handleFormSubmission(\GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            if(!$form->fieldErrorCount()){
                
                $labourRateId = filter_input(INPUT_POST, 'labour_rate_id');
                $this->setProperty('labour_rate_id', $labourRateId);
                
                $useLabourRateSettings = filter_input(INPUT_POST, 'use_labour_rate_settings');
                
                $title = '';
                $wage = NULL;
                $rate = NULL;
                
                if(!$useLabourRateSettings){
                    $title = filter_input(INPUT_POST, 'title');
                    $wage = filter_input(INPUT_POST, 'wage');
                    $rate = filter_input(INPUT_POST, 'rate');
                }
                
                $this->setProperty('title', $title);
                
                $this->setProperty('wage', $wage);
                
                $this->setProperty('rate', $rate);
                
                if($this->save()){
                    return true;
                }
            }
        }
        return false;
    }
    
}
