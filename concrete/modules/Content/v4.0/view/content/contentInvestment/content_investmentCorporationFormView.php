<?php

class ContentInvestmentCorporationFormView extends ContentInvestmentFormView{
    
    protected $logoImageUploader = NULL;
    
    public function setLogoImageUploader(AbstractGI_Uploader $logoImageUploader = NULL){
        $this->logoImageUploader = $logoImageUploader;
        return $this;
    }
    
    public function buildFormGuts() {
        parent::buildFormGuts();
        
        $this->form->addHTML('<hr>');
        
        $this->form->addHTML('<div class="auto_columns quarters custom_fields">');
            $this->addCategoryField();
            $this->addInvestorsField();
            $this->addEquityField();
            $this->addIndustryField();
            $this->addLocationField();
            $this->addCurrencyField();
            $this->addEmployeesField();
            $this->addIncorporationTypeField();
            $this->addFoundDateField();
            $this->addWebsiteField();
        $this->form->addHTML('</div>'); 
        $this->form->addHTML('<hr>');  
        $this->form->addHTML('<div class="auto_columns halves custom_fields">');
            $this->form->addHTML('<div class="auto_column">');
            $this->addSloganField();
            $this->addSummaryField();
            $this->form->addHTML('</div>');
            $this->form->addHTML('<div class="auto_column">');
            $this->addLogoImageUploader();
            $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
        

    }

    public function addCategoryField(){
        $this->form->addField($this->content->getFieldName('corporation_category'), 'dropdown', array(
            'displayName' => 'Category',
            'options' => ContentFactory::$OPITIONS_CORPORATION_CATEGORY,
            'value' => $this->content->getProperty('content_investment_corporation.corporation_category'),
        ));
    }

    public function addInvestorsField(){
        $this->form->addField($this->content->getFieldName('investors'), 'text', array(
            'displayName' => 'Investors',
            'placeHolder' => 'i.e. 3',
            'value' => $this->content->getProperty('content_investment_corporation.investors'),
        ));
    }
    
    public function addEquityField(){
        $this->form->addField($this->content->getFieldName('equity'), 'text', array(
            'displayName' => 'Equity',
            'placeHolder' => 'i.e. 20%',
            'value' => $this->content->getProperty('content_investment_corporation.equity'),
        ));
    }
    
    public function addIndustryField(){
        $this->form->addField($this->content->getFieldName('industry'), 'text', array(
            'displayName' => 'Industry',
            'placeHolder' => 'i.e. Food and Beverage',
            'value' => $this->content->getProperty('content_investment_corporation.industry'),
        ));
    }

    public function addLocationField(){
        $this->form->addField($this->content->getFieldName('location'), 'text', array(
            'displayName' => 'Location',
            'placeHolder' => 'i.e. Vancouver, BC, Canada',
            'value' => $this->content->getProperty('content_investment_corporation.location'),
        ));
    }
    
    public function addCurrencyField(){
        $this->form->addField($this->content->getFieldName('currency'), 'text', array(
            'displayName' => 'Currency',
            'placeHolder' => 'i.e. CAD',
            'value' => $this->content->getProperty('content_investment_corporation.currency'),
        ));
    }
    
    public function addEmployeesField(){
        $this->form->addField($this->content->getFieldName('employees'), 'text', array(
            'displayName' => 'Employees',
            'placeHolder' => 'i.e. 10',
            'value' => $this->content->getProperty('content_investment_corporation.employees'),
        ));
    }
    
    public function addIncorporationTypeField(){
        $this->form->addField($this->content->getFieldName('incorporation_type'), 'text', array(
            'displayName' => 'Incorporation Type',
            'placeHolder' => 'i.e. Not Incorporated',
            'value' => $this->content->getProperty('content_investment_corporation.incorporation_type'),
        ));
    }
    
    public function addFoundDateField(){
        $this->form->addField($this->content->getFieldName('found_date'), 'date', array(
            'displayName' => 'Found Date',
            'value' => $this->content->getProperty('content_investment_corporation.found_date'),
        ));
    }

    public function addWebsiteField(){
        $this->form->addField($this->content->getFieldName('website'), 'text', array(
            'displayName' => 'Website',
            'placeHolder' => 'i.e. civilrealestate.ca',
            'value' => $this->content->getProperty('content_investment_corporation.website'),
        ));
    }
    
    public function addSloganField(){
        $this->form->addField($this->content->getFieldName('slogan'), 'text', array(
            'displayName' => 'Slogan',
            'value' => $this->content->getProperty('content_investment_corporation.slogan'),
        ));
    }
    public function addSummaryField(){
        $this->form->addField($this->content->getFieldName('summary'), 'textarea', array(
            'displayName' => 'Company Summary',
            'value' => $this->content->getProperty('content_investment_corporation.summary'),
        ));
    }
    
    public function addLogoImageUploader(){
        if($this->logoImageUploader){
            $this->form->addHTML($this->logoImageUploader->getHTMLView());
        }
    }
}
