<?php
/**
 * Description of AbstractLabourRateFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.3
 */
abstract class AbstractLabourRateFormView extends GI_View {
    
    /**
     * @var GI_Form 
     */
    protected $form;
    /**
     * @var AbstractLabourRate
     */
    protected $labourRate;
    
    public function __construct(GI_Form $form, AbstractLabourRate $labourRate) {
        parent::__construct();
        $this->form = $form;
        $this->labourRate = $labourRate;
        $this->addSiteTitle('Labour Rates');
        $typeTitle = $this->labourRate->getViewTitle();
        if($typeTitle != 'Labour Rates'){
            $this->addSiteTitle($typeTitle);
        }
        if(empty($this->labourRate->getProperty('id'))){
            $this->addSiteTitle('Add');
        } else {
            $this->addSiteTitle($this->labourRate->getTitle());
            $this->addSiteTitle('Edit');
        }
    }
    
    protected function addFormTitle(){
        if(empty($this->labourRate->getProperty('id'))){
            $this->form->addHTML('<h1>Add ' . $this->labourRate->getTypeTitle() . '</h1>');
        } else {
            $this->form->addHTML('<h1>Edit ' . $this->labourRate->getTypeTitle() . '</h1>');
        }
    }
    
    protected function addFields(){
        $this->form->addField('title', 'text', array(
            'displayName' => 'Title',
            'placeHolder' => 'Title',
            'required' => true,
            'description' => 'Labour title.',
            'value' => $this->labourRate->getTitle()
        ));
        
        $this->form->addField('wage', 'money', array(
            'displayName' => 'Wage',
            'placeHolder' => 'Wage',
            'description' => 'Wage per hour.',
            'value' => $this->labourRate->getWage()
        ));
        
        $this->form->addField('rate', 'money', array(
            'displayName' => 'Rate',
            'placeHolder' => 'Rate',
            'description' => 'Billing rate per hour.',
            'value' => $this->labourRate->getRate()
        ));

        if ($this->labourRate->showCurrency()) {
            if (ProjectConfig::getHasMultipleCurrencies()) {
                $this->form->addField('currency_id', 'dropdown', array(
                    'displayName' => 'Currency',
                    'options' => CurrencyFactory::getOptionsArray('name'),
                    'value' => $this->labourRate->getProperty('currency_id')
                ));
            } else {
                $this->form->addDefaultCurrencyField($this->labourRate->getProperty('currency_id'), 'currency_id');
            }
        }
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
