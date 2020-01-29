<?php
/**
 * Description of AbstractUserHasLabourRateFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.2
 */
abstract class AbstractUserHasLabourRateFormView extends GI_View {
    
    /**
     * @var GI_Form 
     */
    protected $form;
    /**
     * @var AbstractUserHasLabourRate
     */
    protected $userHasLabourRate;
    
    public function __construct(GI_Form $form, AbstractUserHasLabourRate $userHasLabourRate) {
        parent::__construct();
        $this->form = $form;
        $this->userHasLabourRate = $userHasLabourRate;
        $this->addSiteTitle('User Labour Rates');
        $typeTitle = $this->userHasLabourRate->getViewTitle();
        if($typeTitle != 'User Labour Rates'){
            $this->addSiteTitle($typeTitle);
        }
        if(empty($this->userHasLabourRate->getProperty('id'))){
            $this->addSiteTitle('Add');
        } else {
            $this->addSiteTitle($this->userHasLabourRate->getTitle());
            $this->addSiteTitle('Edit');
        }
    }
    
    protected function addFormTitle(){
        if(empty($this->userHasLabourRate->getProperty('id'))){
            $this->form->addHTML('<h1>Add ' . $this->userHasLabourRate->getViewTitle(false) . '</h1>');
        } else {
            $this->form->addHTML('<h1>Edit ' . $this->userHasLabourRate->getViewTitle(false) . '</h1>');
        }
    }
    
    protected function addFields(){
        $labourRateURL = GI_URLUtils::buildURL(array(
            'controller' => 'autocomplete',
            'action' => 'labourRate',
            'ajax' => 1
        ));
        $this->form->addField('labour_rate_id', 'autocomplete', array(
            'displayName' => 'Labour Rate',
            'placeHolder' => 'Select a labour rate...',
            'autocompURL' => $labourRateURL,
            'autocompMinLength' => 0,
            'required' => true,
            'value' => $this->userHasLabourRate->getProperty('labour_rate_id')
        ));
        
        $this->form->addField('use_labour_rate_settings', 'radio', array(
            'displayName' => 'Use Labour Rate Settings',
            'options' => array(
                1 => 'Yes',
                0 => 'No'
            ),
            'value' => 1,
            'stayOn' => true,
            'fieldClass' => 'radio_toggler'
        ));
        
        $this->form->addHTML('<div class="radio_toggler_element form_element" data-group="use_labour_rate_settings" data-element="0">');
        
        $this->form->addField('title', 'text', array(
            'displayName' => 'Title',
            'placeHolder' => 'Title',
            'description' => 'Labour title.',
            'value' => $this->userHasLabourRate->getTitle()
        ));
        
        $this->form->addField('wage', 'money', array(
            'displayName' => 'Wage',
            'placeHolder' => 'Wage',
            'description' => 'Wage per hour.',
            'value' => $this->userHasLabourRate->getWage()
        ));
        
        $this->form->addField('rate', 'money', array(
            'displayName' => 'Rate',
            'placeHolder' => 'Rate',
            'description' => 'Billing rate per hour.',
            'value' => $this->userHasLabourRate->getRate()
        ));
        
        $this->form->addHTML('</div>');
    }
    
    protected function addSubmitBtn(){
        $this->form->addHTML('<span class="submit_btn" title="Submit" tabindex="0">Submit</span>');
    }
    
    public function buildForm(){
        $this->addFormTitle();
        
        $this->addFields();
        
        $this->addSubmitBtn();
    }
    
    protected function openViewWrap(){
        $this->addHTML('<div class="content_padding">');
        return $this;
    }
    
    protected function closeViewWrap(){
        $this->addHTML('</div>');
        return $this;
    }
    
    public function buildView(){
        $this->openViewWrap();
            $this->addHTML($this->form->getForm());
        $this->closeViewWrap();
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}
