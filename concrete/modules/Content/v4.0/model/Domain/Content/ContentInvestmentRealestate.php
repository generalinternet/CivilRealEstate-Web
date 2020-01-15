<?php

class ContentInvestmentRealestate extends ContentInvestment {
    /**
     * @var AbstractContactInfo 
     */
    protected $addrContactInfo;
    
    public function getWindowIcon() {
        return 'building';
    }
    
    public function getAvatarHTML(){
        return GI_StringUtils::getSVGIcon('building');
    }
    
    public function getViewTitle($plural = true) {
        $typeModel = $this->getTypeModel();
        $title = $typeModel->getProperty('title');
        return $title;
    }
    
    public function getView() {
        $contentView = new ContentInvestmentRealEstateDetailView($this);
        return $contentView;
    }
    
    protected function buildNewFormView(\GI_Form $form){
        return new ContentInvestmentRealEstateFormView($form, $this, false);
    }
    
    public function getAddrContactInfo() {
        if (empty($this->addrContactInfo)) {
            $addrId = $this->getProperty('content_investment_realestate.addr_id');
            if (!empty($addrId)) {
                $this->addrContactInfo = ContactInfoFactory::getModelById($addrId);
            } else {
                $this->addrContactInfo = ContactInfoFactory::buildNewModel('address');
            }
        }
        return $this->addrContactInfo;
    }
    
    protected function setPropertiesFromForm(GI_Form $form){
       
        parent::setPropertiesFromForm($form);
        
        $propertyType = filter_input(INPUT_POST,  $this->getFieldName('property_type'));
        $this->setProperty('content_investment_realestate.property_type', $propertyType);
        
        $buildingType = filter_input(INPUT_POST,  $this->getFieldName('building_type'));
        $this->setProperty('content_investment_realestate.building_type', $buildingType);
        
        $numberOfUnits = filter_input(INPUT_POST,  $this->getFieldName('number_of_units'));
        $this->setProperty('content_investment_realestate.number_of_units', $numberOfUnits);
        
        $realestateInvestType = filter_input(INPUT_POST,  $this->getFieldName('realestate_invest_type'));
        $this->setProperty('content_investment_realestate.realestate_invest_type', $realestateInvestType);

        $realestateInvestTerm = filter_input(INPUT_POST,  $this->getFieldName('investment_term'));
        $this->setProperty('content_investment_realestate.investment_term', $realestateInvestTerm);

        $purchasePrice = filter_input(INPUT_POST,  $this->getFieldName('purchase_price'));
        $this->setProperty('content_investment_realestate.purchase_price', $purchasePrice);
        
        $newValue = filter_input(INPUT_POST,  $this->getFieldName('new_value'));
        $this->setProperty('content_investment_realestate.new_value', $newValue);
        
        $netGain = filter_input(INPUT_POST,  $this->getFieldName('net_gain'));
        $this->setProperty('content_investment_realestate.net_gain', $netGain);
        
        $annualReturn = filter_input(INPUT_POST,  $this->getFieldName('annual_return'));
        $this->setProperty('content_investment_realestate.annual_return', $annualReturn);
        
        $equityRequired = filter_input(INPUT_POST,  $this->getFieldName('equity_required'));
        $this->setProperty('content_investment_realestate.equity_required', $equityRequired);
        
        $minimumInvestment = filter_input(INPUT_POST,  $this->getFieldName('minimum_investment'));
        $this->setProperty('content_investment_realestate.minimum_investment', $minimumInvestment);

        $addressContactInfo = $this->getAddrContactInfo();
           
        if (!empty($addressContactInfo)) {
            if (!$addressContactInfo->handleFormSubmission($form)) {
                return false;
            }
            if (!empty($addressContactInfo->getProperty('id'))) {
                $addressContactInfoId = $addressContactInfo->getProperty('id');
                $this->setProperty('content_investment_realestate.addr_id', $addressContactInfoId);
            }
        }

        // other fields
        $otherFormItems = array(
            'purchase_price_of_building',
            'suite_mix_amount',
            'suite_mix_description',
            'shares_available',
            'cost_per_share',
            'exit_strategy',
            'property_description',
            'individually_stratified_units',
            'legal_description',
            'lot_size',
            'property_tax',
            'zoning_description',
            'zoning_slot',
            'year_built',
            'net_rentable_area',
            'parking'
        );
        foreach($otherFormItems as $formItem){
            $formItemValue = filter_input(INPUT_POST, $this->getFieldName($formItem));
            $this->setProperty('content_investment_realestate.'.$formItem, $formItemValue);
        }

        return true;
    }
    
    public function getDisplayPurchasePrice() {
        $purchasePrice = $this->getProperty('content_investment_realestate.purchase_price');
        return ContentFactory::formatMoney($purchasePrice);
    }

    public function getDisplayPurchasePriceOfBuilding() {
        $purchasePrice = $this->getProperty('content_investment_realestate.purchase_price_of_building');
        return ContentFactory::formatMoney($purchasePrice);
    }

    public function getPropertyTaxValue() {
        $taxValue = $this->getProperty('content_investment_realestate.property_tax');
        return ContentFactory::formatMoney($taxValue);
    }

    public function getDisplayPurchasePricePerDoor() {
        $buildingPrice = $this->getProperty('content_investment_realestate.purchase_price_of_building');
        $suiteMixAmount = $this->getProperty('content_investment_realestate.suite_mix_amount');

        if(
            empty($buildingPrice) ||
            is_nan($buildingPrice) ||
            empty($suiteMixAmount) ||
            is_nan($suiteMixAmount)
        ){
            return NULL;
        }
        return ContentFactory::formatMoney($buildingPrice/$suiteMixAmount);
    }

    public function getDisplayCostPerShare() {
        $purchasePrice = $this->getProperty('content_investment_realestate.cost_per_share');
        return ContentFactory::formatMoney($purchasePrice);
    }
    
    public function getDisplayNewValue() {
        $newValue = $this->getProperty('content_investment_realestate.new_value');
        return ContentFactory::formatMoney($newValue);
    }
    
    public function getDisplayNetGain() {
        $netGain = $this->getProperty('content_investment_realestate.net_gain');
        return ContentFactory::formatMoney($netGain);
    }
    
    public function getSubtitle() {
        return $this->getLocation();
    }
    
    public function getLocation() {
        $address = $this->getAddrContactInfo();
        if (!empty($address)) {
            $locationText = '';
            $city = $address->getProperty('contact_info_address.addr_city');
            if (!empty($city)) {
                $locationText .= $city;
            }
            $includeCountry = true;
            $region = $address->getRegion($includeCountry);
            if (!empty($region)) {
                if (!empty($locationText)) {
                    $locationText .= ', ';
                }
                $locationText .= $region;
            }
            return $locationText;
        }
        return '';
    }
    
    public function getPreviewBockHTML() {
        $dataArr = array(
            [
                'name' => 'Purchase',
                'value' => $this->getDisplayPurchasePrice()
            ],
            [
                'name' => 'New Value',
                'value' => $this->getDisplayNewValue()
            ],
            [
                'name' => 'Net Gain',
                'value' => $this->getDisplayNetGain()
            ],
        );
        return $this->getPreviewBlocks($dataArr, 'investment__preview-wrap_theme_primary');
    }

    public function getInvestmentItemDetailBlockHTML() {
        $html = '';

        if(Login::isLoggedIn()){
            $summaryInfo = [
                [
                    'name' => 'Property Type',
                    'value' => $this->getPropertyTypeTitle(),
                ],
                [
                    'name' => 'Project Status',
                    'value' => $this->getInvestmentStatusTitle(),
                ],
                [
                    'name' => 'Target Amount',
                    'value' => $this->getDisplayTargetAmt(),
                ],
                [
                    'name' => 'Invested Amount',
                    'value' => $this->getDisplayInvestedAmt(),
                ],
                [
                    'name' => 'Funded',
                    'value' => $this->getDisplayFundsRate(),
                ],
                [
                    'name' => 'Due Date',
                    'value' => $this->getDisplayDueDate(),
                ],
                [
                    'name' => 'Minimum Investment',
                    'value' => $this->getDisplayMinimumInvestment(),
                ],
            ];
            $html .= '<div class="row investment__detail-info-row">';
                $html .= '<div class="col-xs-12"><h3 class="investment__general-info-title">Summary</h3></div>';
                foreach($summaryInfo as $infoItem){
                    if(empty($infoItem['value']) && $infoItem['value'] !== 0){
                        continue;
                    }
                    $html .= '<div class="col-xs-12">';
                    $html .= '<span class="investment__general-info-item-name">'.$infoItem['name'].'</span>';
                    $html .= '<span class="investment__general-info-item-value">'.$infoItem['value'].'</span>';
                    $html .= '</div>';
                }
            $html .= '</div>';
        } else {
            $html .= $this->getUnregisterPlaceholder();
        }

        $html .= $this->getSharesStructureInfo();

        $html .= $this->getSalientDetailsInfo();

        return $html;
    }
    
    public function getPropertyTypeTitle() {
        $propertyType = $this->getProperty('content_investment_realestate.property_type');
        if (!empty($propertyType) && array_key_exists($propertyType, ContentFactory::$OPITIONS_PROPERTY_TYPE)) {
            return ContentFactory::$OPITIONS_PROPERTY_TYPE[$propertyType];
        }
        return '';
    }
    
    public function getRealestateInvestTypeTitle() {
        $realestateInvestType = $this->getProperty('content_investment_realestate.realestate_invest_type');
        if (!empty($realestateInvestType) && array_key_exists($realestateInvestType, ContentFactory::$OPITIONS_REALESTATE_INVEST_TYPE)) {
            return ContentFactory::$OPITIONS_REALESTATE_INVEST_TYPE[$realestateInvestType];
        }
        return '';
    }
    
    public function getDisplayEquityRequired() {
        $equityRequired = $this->getProperty('content_investment_realestate.equity_required');
        return ContentFactory::formatMoney($equityRequired);
    }
    
    public function getDisplayMinimumInvestment() {
        $minimumInvestment = $this->getProperty('content_investment_realestate.minimum_investment');
        return ContentFactory::formatMoney($minimumInvestment);
    }

    
    public function getDetailPreviewBlockHTML(){
        $infoArr = [
            [
                'name' => 'Building Type',
                'value' => $this->getProperty('content_investment_realestate.building_type'),
            ],
            [
                'name' => 'Number Of Units',
                'value' => $this->getProperty('content_investment_realestate.number_of_units'),
            ],
            [
                'name' => 'Investment Type',
                'value' => $this->getRealestateInvestTypeTitle(),
            ],
            [
                'name' => 'Investor Type',
                'value' => '',
            ],
            [
                'name' => 'Projected Annual Return',
                'value' => $this->getProperty('content_investment_realestate.annual_return'),
            ],
            [
                'name' => 'Total Equity Required',
                'value' => $this->getDisplayEquityRequired(),
            ],
            [
                'name' => 'Deadline',
                'value' => $this->getDisplayDueDate(),
            ],
            [
                'name' => 'Investment Term',
                'value' => $this->getProperty('content_investment_realestate.investment_term'), 
            ],
        ];
        $chunkCount = ceil(count($infoArr) / 2);
        $infoArrs = array_chunk($infoArr, $chunkCount);
        $html = '<div class="investment__detail-info-row investment__detail-info-row_type_hightlight">';
            $html .= '<div class="row">';
            foreach($infoArrs as $infoArr){
                $html .= '<div class="col-xs-12 col-md-6">';
                foreach($infoArr as $infoItem){
                    if(empty($infoItem['value']) && $infoItem['value'] !== 0){
                        continue;
                    }
                    $html .= '<div>';
                        $html .= '<span class="investment__general-info-item-name">'.$infoItem['name'].'</span>';
                        $html .= '<span class="investment__general-info-item-value">'.$infoItem['value'].'</span>';
                    $html .= '</div>';
                }
                $html .= '</div>';
            }
            $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    protected function getSharesStructureInfo(){
        $html = '';

        if(!Login::isLoggedIn()){
            return $this->getUnregisterPlaceholder('Share Structure');
        }

        $subValueTagOpen = '<span class="investment__general-info-item-sub-value">';
        $subValueTagClose = '</span>';

        $perDoorPriceText = '';
        $perDoorPrice = $this->getDisplayPurchasePricePerDoor();
        if(!empty($perDoorPrice)){
            $perDoorPriceText =  $subValueTagOpen. $perDoorPrice. ' per door' . $subValueTagClose;
        }

        $sharesAvailable = $this->getProperty('content_investment_realestate.shares_available');
        if(!empty($sharesAvailable)){
            $sharesAvailable .= ' Share';
            if($sharesAvailable > 1){
                $sharesAvailable .= 's';
            }
            $sharesAvailable .= ' Available';
            $sharesAvailable = $subValueTagOpen . $sharesAvailable . $subValueTagClose;
        }

        $summaryInfo = [
            [
                'name' => 'Purchase Price of Building',
                'value' => $this->getDisplayPurchasePriceOfBuilding() . $perDoorPriceText,
            ],
            [
                'name' => 'Suite Mix',
                'value' => $this->getFullSuiteMixTextValue(),
            ],
            [
                'name' => 'Total Equity Required',
                'value' => $this->getDisplayEquityRequired() . $sharesAvailable,
            ],
            [
                'name' => 'Cost Per Share',
                'value' => $this->getDisplayCostPerShare(),
            ],
            [
                'name' => 'Projected Annual Return',
                'value' => $this->getProperty('content_investment_realestate.annual_return'),
            ],
            [
                'name' => 'Exit Strategy',
                'value' => $this->getProperty('content_investment_realestate.exit_strategy'),
            ],
        ];
        $html .= '<div class="row investment__detail-info-row">';
            $html .= '<div class="col-xs-12"><h3 class="investment__general-info-title">Share Structure</h3></div>';
            foreach($summaryInfo as $infoItem){
                if(empty($infoItem['value']) && $infoItem['value'] !== 0){
                    continue;
                }
                $html .= '<div class="col-xs-12">';
                $html .= '<span class="investment__general-info-item-name">'.$infoItem['name'].'</span>';
                $html .= '<span class="investment__general-info-item-value">'.$infoItem['value'].'</span>';
                $html .= '</div>';
            }
        $html .= '</div>';

        return $html;

    }

    protected function getSalientDetailsInfo(){
        $html = '';

        if(!Login::isLoggedIn()){
            return $this->getUnregisterPlaceholder('Salient Details');
        }

        $subValueTagOpen = '<span class="investment__general-info-item-sub-value">';
        $subValueTagClose = '</span>';

        $individualStratifiedUnit = $this->getProperty('content_investment_realestate.individually_stratified_units');
        if(!empty($individualStratifiedUnit)){
            $individualStratifiedUnit .= ' individually stratified unit';

            if($individualStratifiedUnit > 1){
                $individualStratifiedUnit .= 's';
            }

            $individualStratifiedUnit = $subValueTagOpen . $individualStratifiedUnit . $subValueTagClose;
        }

        $lotSize = $this->getProperty('content_investment_realestate.annual_return');
        if(!empty($lotSize)){
            $lotSize = $lotSize . ' square feet';
        }

        $netRentableArea = $this->getProperty('content_investment_realestate.net_rentable_area');
        if(!empty($netRentableArea)){
            $netRentableArea = $netRentableArea . ' square feet';
        }

        $summaryInfo = [
            [
                'name' => 'Property Description',
                'value' => $this->getProperty('content_investment_realestate.property_description') . $individualStratifiedUnit,
            ],
            [
                'name' => 'Legal Description',
                'value' => $this->getProperty('content_investment_realestate.legal_description'),
            ],
            [
                'name' => 'Lot Size',
                'value' => $lotSize,
            ],
            [
                'name' => 'Property Tax',
                'value' => $this->getPropertyTaxValue(),
            ],
            [
                'name' => 'Zoning Description',
                'value' => $this->getProperty('content_investment_realestate.zoning_description'),
            ],
            [
                'name' => 'Year Built',
                'value' => $this->getProperty('content_investment_realestate.year_built'),
            ],
            [
                'name' => 'Suite Mix',
                'value' => $this->getFullSuiteMixTextValue(),
            ],
            [
                'name' => 'Net Rentable Area',
                'value' => $netRentableArea,
            ],
            [
                'name' => 'Parking',
                'value' => $this->getProperty('content_investment_realestate.parking'),
            ],
        ];

        $html .= '<div class="row investment__detail-info-row">';
            $html .= '<div class="col-xs-12"><h3 class="investment__general-info-title">Salient Details</h3></div>';
            foreach($summaryInfo as $infoItem){
                if(empty($infoItem['value']) && $infoItem['value'] !== 0){
                    continue;
                }
                $html .= '<div class="col-xs-12">';
                $html .= '<span class="investment__general-info-item-name">'.$infoItem['name'].'</span>';
                $html .= '<span class="investment__general-info-item-value">'.$infoItem['value'].'</span>';
                $html .= '</div>';
            }
        $html .= '</div>';

        return $html;

    }

    protected function getFullSuiteMixTextValue(){
        $subValueTagOpen = '<span class="investment__general-info-item-sub-value">';
        $subValueTagClose = '</span>';

        $suiteMixAmount = $this->getProperty('content_investment_realestate.suite_mix_amount');
        if(!empty($suiteMixAmount)){
            $suiteMixAmount .= ' Suite';
            if($suiteMixAmount > 1){
                $suiteMixAmount .= 's';
            }
        }

        $suiteMixDescription = $this->getProperty('content_investment_realestate.suite_mix_description');
        if(!empty($suiteMixDescription)){
            $suiteMixDescription = $subValueTagOpen. $suiteMixDescription . $subValueTagClose;
        }

        return $suiteMixAmount . $suiteMixDescription;
    }

    public function getCategoryRef(){
        return $this->getPropertyTypeTitle();
    }
    public function getCategoryColor(){
        $realestateCategory = $this->getProperty('content_investment_realestate.property_type');
        if(empty($realestateCategory)){
            return ContentFactory::$INVESTMENT_CATEGORY_COLOR[$this->getTypeRef()];
        }
        return ContentFactory::$INVESTMENT_CATEGORY_COLOR[$realestateCategory];
    }

    public function getAddrString(){
        $contactInfo = $this->getAddrContactInfo();
        if(empty($contactInfo)){
            return '';
        }
        return $contactInfo->getAddressString();
    }
}
