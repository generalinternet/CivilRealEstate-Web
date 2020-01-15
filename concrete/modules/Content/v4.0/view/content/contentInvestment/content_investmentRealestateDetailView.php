<?php

class ContentInvestmentRealestateDetailView extends ContentInvestmentDetailView{
    
    /** @var ContentInvestmentRealestate  */
    protected $content;
    
    protected function buildInvestmentDetailSection(){
        parent::buildInvestmentDetailSection();


        $this->buildRealEstateDetailSection();
        
        $this->buildOtherFieldsSection();
    }

    protected function buildRealEstateDetailSection(){
        $this->addHTML('<div class="content_group">');
        $this->addHTML('<h2 class="content_group_title">Real Estate Details</h2>');
        $this->addHTML('<div class="auto_columns thirds">');       
        $this->addContentBlockWithWrap($this->content->getPropertyTypeTitle(), 'Category');
        $this->addContentBlockWithWrap($this->content->getDisplayPurchasePrice(), 'Purchase Price');
        $this->addContentBlockWithWrap($this->content->getDisplayNewValue(), 'New Value');
        $this->addContentBlockWithWrap($this->content->getDisplayNetGain(), 'Net Gain');
        
        $this->addContentBlockWithWrap($this->content->getProperty('content_investment_realestate.building_type'), 'Building Type');
        $this->addContentBlockWithWrap($this->content->getProperty('content_investment_realestate.number_of_units'), 'Number Of Units');
        $this->addContentBlockWithWrap($this->content->getRealestateInvestTypeTitle(), 'Investment Type');
        
        $this->addContentBlockWithWrap($this->content->getProperty('content_investment_realestate.annual_return'), 'Annual Return');
        $this->addContentBlockWithWrap($this->content->getDisplayEquityRequired(), 'Total equity required');
        $this->addContentBlockWithWrap($this->content->getDisplayMinimumInvestment(), 'Minimum investment');

        $this->addContentBlockWithWrap($this->content->getAddrString(), 'Address');
        
        $this->addHTML('</div>');
        $this->addHTML('</div>');
    }

    protected function buildOtherFieldsSection(){
        $this->addHTML('<div class="content_group">');
        $this->addHTML('<h2 class="content_group_title">Other Details</h2>');
        $this->addHTML('<div class="auto_columns thirds">');  
        $otherProperties = [
            [
                'title' => 'Investment Term',
                'function' => '',
                'ref' => 'investment_term'
            ],
            [
                'title' => 'Purchase Price of Building',
                'function' => 'getDisplayPurchasePriceOfBuilding',
                'ref' => 'purchase_price_of_building'
            ],
            [
                'title' => 'Purchase Price per door',
                'function' => 'getDisplayPurchasePricePerDoor',
                'ref' => ''
            ],
            [
                'title' => 'Suite Mix Amount',
                'function' => '',
                'ref' => 'suite_mix_amount'
            ],
            [
                'title' => 'Suite Mix Description',
                'function' => '',
                'ref' => 'suite_mix_description'
            ],
            [
                'title' => 'Shares Available',
                'function' => '',
                'ref' => 'shares_available'
            ],
            [
                'title' => 'Cost Per Share',
                'function' => 'getDisplayCostPerShare',
                'ref' => 'cost_per_share'
            ],
            [
                'title' => 'Exit Strategy',
                'function' => '',
                'ref' => 'exit_strategy'
            ],
            [
                'title' => 'Property Description',
                'function' => '',
                'ref' => 'property_description'
            ],
            [
                'title' => 'Individually Stratified',
                'function' => '',
                'ref' => 'individually_stratified_units'
            ],
            [
                'title' => 'Legal Description',
                'function' => '',
                'ref' => 'legal_description'
            ],
            [
                'title' => 'Lot Size',
                'function' => '',
                'ref' => 'lot_size'
            ],
            [
                'title' => 'Property Tax',
                'function' => 'getPropertyTaxValue',
                'ref' => 'property_tax'
            ],
            [
                'title' => 'Zoning Description',
                'function' => '',
                'ref' => 'zoning_description'
            ],
            [
                'title' => 'Zoning Slot',
                'function' => '',
                'ref' => 'zoning_slot'
            ],
            [
                'title' => 'Year Built',
                'function' => '',
                'ref' => 'year_built'
            ],
            [
                'title' => 'Net Rentable Area',
                'function' => '',
                'ref' => 'net_rentable_area'
            ],
            [
                'title' => 'Parking',
                'function' => '',
                'ref' => 'parking'
            ],
        ];

        foreach($otherProperties as $realEstateProp){
            if (!empty($realEstateProp['function'])){
                $value = $this->content->{$realEstateProp['function']}();
            } else {
                $value = $this->content->getProperty('content_investment_realestate.'.$realEstateProp['ref']);
            }
            $this->addContentBlockWithWrap($value, $realEstateProp['title']);
        }
        $this->addHTML('</div>');
        $this->addHTML('</div>');
    }
}
