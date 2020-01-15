<?php

class ContentInvestmentRealestateFormView extends ContentInvestmentFormView{
    
    public function buildFormGuts() {
        parent::buildFormGuts();
        
        $this->addRealEstateDetailFormSection();

        $this->addShareStructureFormSection();

        $this->addSalientDetailsFormSection();

    }

    protected function addRealEstateDetailFormSection(){
        $this->form->addHTML('<hr>');
        
        $this->form->addHTML('<div class="auto_columns halves custom_fields">');
            $this->form->addHTML('<div class="auto_column">');
                $this->form->addHTML('<div class="auto_columns thirds">');
                $this->addPropertyTypeField();
                $this->addPurchasePriceField();
                $this->addNewValueField();
                $this->addNetGainField();
                $this->form->addHTML('</div>');

                $this->form->addHTML('<div class="auto_columns halves">');
                $this->addBuildingTypeField();
                $this->addNumberOfUnitsField();
                $this->addRealestateInvestmentTypeField();
                $this->addRealestateInvestmentTermField();
                $this->form->addHTML('</div>');
                
                $this->form->addHTML('<div class="auto_columns thirds">');
                $this->addAnnualReturnField();
                $this->addEquityRequiredField();
                $this->addMinimumInvestmentField();
                $this->form->addHTML('</div>');
                
            $this->form->addHTML('</div>');
            
            $this->form->addHTML('<div class="auto_column">');
                $this->addAddressField();
            $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
    }
    
    public function addPropertyTypeField(){
        $this->form->addField($this->content->getFieldName('property_type'), 'dropdown', array(
            'displayName'=>'Category',
            'options'=>ContentFactory::$OPITIONS_PROPERTY_TYPE,
            'value' => $this->content->getProperty('content_investment_realestate.property_type'),
        ));
    }
    
    public function addBuildingTypeField(){
        $this->form->addField($this->content->getFieldName('building_type'), 'text', array(
            'displayName' => 'Building Type',
            'placeHolder' => 'i.e. 4-Plex or 16 Unit',
            'value' => $this->content->getProperty('content_investment_realestate.building_type'),
            'required' => true, 
        ));
    }
    
    public function addNumberOfUnitsField(){
        $this->form->addField($this->content->getFieldName('number_of_units'), 'text', array(
            'displayName' => 'Number of Units',
            'placeHolder' => 'i.e. 16',
            'value' => $this->content->getProperty('content_investment_realestate.number_of_units'),
        ));
    }

    public function addRealestateInvestmentTypeField(){
        $this->form->addField($this->content->getFieldName('realestate_invest_type'), 'dropdown', array(
            'displayName'=>'Investment Type',
            'options'=>ContentFactory::$OPITIONS_REALESTATE_INVEST_TYPE,
            'value' => $this->content->getProperty('content_investment_realestate.realestate_invest_type'),
        ));
    }

    public function addRealestateInvestmentTermField(){
        $this->form->addField($this->content->getFieldName('investment_term'), 'text', array(
            'displayName'=>'Investment Term',
            'placeHolder' => 'i.e. 3-5 years',
            'value' => $this->content->getProperty('content_investment_realestate.investment_term'),
        ));
    }

    public function addPurchasePriceField(){
        $this->form->addField($this->content->getFieldName('purchase_price'), 'money', array(
            'displayName' => 'Purchase Price',
            'placeHolder' => 'i.e. 3000000',
            'value' => $this->content->getProperty('content_investment_realestate.purchase_price'),
        ));
    }
    
    public function addNewValueField(){
        $this->form->addField($this->content->getFieldName('new_value'), 'money', array(
            'displayName' => 'New Value',
            'placeHolder' => 'i.e. 4000000',
            'value' => $this->content->getProperty('content_investment_realestate.new_value'),
        ));
    }
    
    public function addNetGainField(){
        $this->form->addField($this->content->getFieldName('net_gain'), 'money', array(
            'displayName' => 'Net Gain',
            'placeHolder' => 'i.e. 1000000',
            'value' => $this->content->getProperty('content_investment_realestate.net_gain'),
        ));
    }

    public function addAnnualReturnField(){
        $this->form->addField($this->content->getFieldName('annual_return'), 'text', array(
            'displayName' => 'Investment ROI',
            'placeHolder' => 'i.e. 20%',
            'value' => $this->content->getProperty('content_investment_realestate.annual_return'),
        ));
    }
    
    public function addEquityRequiredField(){
        $this->form->addField($this->content->getFieldName('equity_required'), 'money', array(
            'displayName' => 'Total equity required',
            'placeHolder' => 'i.e. 3000000',
            'value' => $this->content->getProperty('content_investment_realestate.equity_required'),
        ));
    }
    
    public function addMinimumInvestmentField(){
        $this->form->addField($this->content->getFieldName('minimum_investment'), 'money', array(
            'displayName' => 'Minimum investment',
            'placeHolder' => 'i.e. 100000',
            'value' => $this->content->getProperty('content_investment_realestate.minimum_investment'),
        ));
    }
    
    function addAddressField() {
        $addrContactInfo = $this->content->getAddrContactInfo();
        $addrContactInfoFormView = $addrContactInfo->getFormView($this->form);
        $addrContactInfoFormView->hideTypeField(true);
//        $addrContactInfoFormView->setFieldRequired('addr_country', false);
//        $addrContactInfoFormView->setFieldRequired('addr_region', false);
        $this->form->addHTML('<fieldset class="label_legend">'); 
            $this->form->addHTML('<legend class="main">Address</legend>');
            $addrContactInfoFormView->buildForm();
        $this->form->addHTML('</fieldset>');
    }
    
    protected function addShareStructureFormSection(){        
        $this->form->addHTML('<hr>');

        $formItems = array(
            [
                'type' => 'money',
                'ref' => 'purchase_price_of_building',
                'settings' => array(
                    'displayName' => 'Purchase Price of Building',
                    'placeHolder' => 'i.e. 4000000',
                    'value' => $this->content->getProperty('content_investment_realestate.purchase_price_of_building'),
                )
            ],
            [
                'type' => 'integer',
                'ref' => 'suite_mix_amount',
                'settings' => array(
                    'displayName' => 'Suite Mix Amount',
                    'placeHolder' => 'i.e. 4',
                    'value' => $this->content->getProperty('content_investment_realestate.suite_mix_amount'),
                )
            ],
            [
                'type' => 'text',
                'ref' => 'suite_mix_description',
                'settings' => array(
                    'displayName' => 'Suite Mix Description',
                    'placeHolder' => 'i.e. (19) 1-Bed  ·  (20) 2-Bed',
                    'value' => $this->content->getProperty('content_investment_realestate.suite_mix_description'),
                )
            ],
            [
                'type' => 'integer',
                'ref' => 'shares_available',
                'settings' => array(
                    'displayName' => 'Shares Available',
                    'placeHolder' => 'i.e. 10',
                    'value' => $this->content->getProperty('content_investment_realestate.shares_available'),
                )
            ],
            [
                'type' => 'money',
                'ref' => 'cost_per_share',
                'settings' => array(
                    'displayName' => 'Cost Per Share',
                    'placeHolder' => 'i.e. 300000',
                    'value' => $this->content->getProperty('content_investment_realestate.cost_per_share'),
                )
            ],
            [
                'type' => 'dropdown',
                'ref' => 'exit_strategy',
                'settings' => array(
                    'displayName' => 'Exit Strategy',
                    'options' => ContentFactory::$OPITIONS_REALESTATE_INVEST_EXIT_STRATEGY,
                    'value' => $this->content->getProperty('content_investment_realestate.exit_strategy'),
                )
            ],
        );

        $this->form->addHTML('<div class="auto_columns thirds">');
        foreach($formItems as $formItem){
            $this->form->addField($this->content->getFieldName($formItem['ref']), $formItem['type'], $formItem['settings']);
        }
        $this->form->addHTML('</div>');
    }

    protected function addSalientDetailsFormSection(){
        $this->form->addHTML('<hr>');

        $formItems = array(
            [
                'type' => 'text',
                'ref' => 'property_description',
                'settings' => array(
                    'displayName' => 'Property Description',
                    'placeHolder' => 'i.e. 4 Story walk up with elevator',
                    'value' => $this->content->getProperty('content_investment_realestate.property_description'),
                )
            ],
            [
                'type' => 'integer',
                'ref' => 'individually_stratified_units',
                'settings' => array(
                    'displayName' => 'Individually Stratified Units',
                    'placeHolder' => 'i.e. 39',
                    'value' => $this->content->getProperty('content_investment_realestate.individually_stratified_units'),
                )
            ],
            [
                'type' => 'text',
                'ref' => 'legal_description',
                'settings' => array(
                    'displayName' => 'Legal Description',
                    'placeHolder' => 'i.e. Lot 1, Land District 36, Township...',
                    'value' => $this->content->getProperty('content_investment_realestate.legal_description'),
                )
            ],
            [
                'type' => 'integer',
                'ref' => 'lot_size',
                'settings' => array(
                    'displayName' => 'Lot Size (sqft)',
                    'placeHolder' => 'i.e. 43660',
                    'value' => $this->content->getProperty('content_investment_realestate.lot_size'),
                )
            ],
            [
                'type' => 'money',
                'ref' => 'property_tax',
                'settings' => array(
                    'displayName' => 'Property Tax',
                    'placeHolder' => 'i.e. 32345',
                    'value' => $this->content->getProperty('content_investment_realestate.property_tax'),
                )
            ],
            [
                'type' => 'dropdown',
                'ref' => 'zoning_description',
                'settings' => array(
                    'displayName' => 'Zoning Description',
                    'options' => ContentFactory::$OPTION_REALESTATE_ZONING_DESCIPTIONS,
                    'value' => $this->content->getProperty('content_investment_realestate.zoning_description'),
                )
            ],
            [
                'type' => 'integer',
                'ref' => 'zoning_slot',
                'settings' => array(
                    'displayName' => 'Zoning Slot',
                    'placeHolder' => 'i.e. 45',
                    'value' => $this->content->getProperty('content_investment_realestate.zoning_slot'),
                )
            ],
            [
                'type' => 'dropdown',
                'ref' => 'year_built',
                'settings' => array(
                    'displayName' => 'Year Built',
                    'options' => ContentFactory::getYearBuiltOptions(),
                    'value' => $this->content->getProperty('content_investment_realestate.year_built'),
                )
            ],
            [
                'type' => 'integer',
                'ref' => 'net_rentable_area',
                'settings' => array(
                    'displayName' => 'Net Rentable Area (sqft)',
                    'placeHolder' => 'i.e. 32699',
                    'value' => $this->content->getProperty('content_investment_realestate.net_rentable_area'),
                )
            ],
            [
                'type' => 'text',
                'ref' => 'parking',
                'settings' => array(
                    'displayName' => 'Parking',
                    'placeHolder' => 'i.e. 50 Stalls secured ...',
                    'value' => $this->content->getProperty('content_investment_realestate.parking'),
                )
            ],
        );

        $this->form->addHTML('<div class="auto_columns quarters">');
        foreach($formItems as $formItem){
            $this->form->addField($this->content->getFieldName($formItem['ref']), $formItem['type'], $formItem['settings']);
        }
        $this->form->addHTML('</div>');
    }
}
