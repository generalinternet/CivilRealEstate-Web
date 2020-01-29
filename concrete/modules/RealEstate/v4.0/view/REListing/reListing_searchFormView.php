<?php

class REListingSearchFormView extends AbstractREListingSearchFormView{
    
    protected $addLocationField = true;
    protected $addPriceFields = true;
    protected $addPropertyTypeField = true;
    protected $addBedroomField = true;
    protected $addBathroomField = true;
    protected $addLandSizeField = true;
    protected $addYearBuiltField = true;
    protected $listingTypeRef = 'res';
    
    
    public function setAddLocationField($addLocationField) {
        $this->addLocationField = $addLocationField;
        return $this;
    }
    
    public function setAddPriceFields($addPriceFields) {
        $this->addPriceFields = $addPriceFields;
        return $this;
    }
    
    public function setAddPropertyTypeField($addPropertyTypeField) {
        $this->addPropertyTypeField = $addPropertyTypeField;
        return $this;
    }
    
    public function setAddBedroomField($addBedroomField) {
        $this->addBedroomField = $addBedroomField;
        return $this;
    }
    
    public function setAddBathroomField($addBathroomField) {
        $this->addBathroomField = $addBathroomField;
        return $this;
    }
    
    public function setAddLandSizeField($addLandSizeField) {
        $this->addLandSizeField = $addLandSizeField;
        return $this;
    }
    
    public function setAddYearBuiltField($addYearBuiltField) {
        $this->addYearBuiltField = $addYearBuiltField;
        return $this;
    }
    
    public function setListingTypeRef($listingTypeRef) {
        $this->listingTypeRef = $listingTypeRef;
        return $this;
    }
    
    protected function buildForm() {
        $this->form->addHTML('<div class="search-form-content">');
            $this->addCloseBtn();
            $this->form->addHTML('<div class="search-title">Find a Home</div>');
            $this->form->addHTML('<div class="basic-fields inline_fields">');
                $this->buildLocationField();
                $this->buildPriceFields();
                $this->addInlineBtns();
            $this->form->addHTML('</div>');
            $this->buildAdvancedFields();
        $this->form->addHTML('</div><!--.search-form-content-->');
    }
    
    /**
     * Search by location: area, city or MSL number
     */
    protected function buildLocationField() {
        if ($this->addLocationField) {
            $this->form->addHTML('<div class="inline_cell field_cell location_cell">');
            //@todo autocomplete
//            $acLocationURL = GI_URLUtils::buildURL(array(
//                'controller' => 'autocomplete',
//                'action' => 'mlsLocation',
//                'ajax' => 1
//            ));
//
//            $this->form->addField('search_location', 'autocomplete', array(
//                'displayName' => 'Search by Location',
//                'placeHolder' => 'Enter Area, City or MLS Number',
//                'autocompURL' => $acLocationURL,
//                'autocompMinLength' => 3,
//                'autocompMultiple' => true,
//                'value' => $this->getQueryValue('city')
//            ));
            $this->form->addField('search_location', 'text', array(
                'displayName'=> 'Location',
                'placeHolder' => 'Enter Area, City or MLS Number',
                'value' => $this->getQueryValue('location')
            ));
            $this->form->addHTML('</div>');
        }
    }
    
    protected function buildPriceFields() {
        if ($this->addPriceFields) {
            $this->form->addHTML('<div class="inline_cell field_cell price_cell form_element_group right_cell">');
                $this->form->addField('search_price_min', 'money', array(
                    'displayName'=> 'Min Price',
                    'placeHolder' => 'Min Price',
                    'value' => $this->getQueryValue('price_min')
                ));
                $this->form->addField('search_price_max', 'money', array(
                    'displayName'=> 'Max Price',
                    'placeHolder' => 'Max Price',
                    'value' => $this->getQueryValue('price_max')
                ));
            $this->form->addHTML('</div>');
        }
    }
    
    protected function addBtns(){
        $this->form->addHTML('<div class="form_bottom_btns">');
            $this->form->addHTML('<span class="other_btn gray reset_btn form_btn"><span class="icon_wrap"><span class="icon reply"></span></span><span class="btn_text">Reset</span></span>');
            $this->form->addHTML('<span class="submit_btn form_btn"><span class="icon_wrap"><span class="icon search"></span></span><span class="btn_text">Search</span></span>');
            
        $this->form->addHTML('</div>');
    }
    
    protected function addInlineBtns(){
        $this->form->addHTML('<div class="inline_cell btns_cell">');
        $this->addSubmitBtn();
        $this->addFilterBtn();
        $this->form->addHTML('</div>');
    }
    
    protected function addCloseBtn(){
        $this->form->addHTML('<span class="close_btn"><img src="resources/media/icons/cross.svg" class="icon close_icon" alt="close icon" title="Close"/></span>');
    }
    
    protected function addSubmitBtn(){
        $this->form->addHTML('<span class="submit_btn"><img src="resources/media/icons/search_grey.svg" class="icon search_icon" alt="search icon" title="Search"/></span>');
    }
    
    protected function addFilterBtn(){
        $this->form->addHTML('<span class="other_btn filter_btn"><img src="resources/media/icons/filter.svg" class="icon filter_icon" alt="fitler icon" title="Filter"/></span>');
    }
    
    protected function buildAdvancedFields() {
        $this->form->addHTML('<div class="advanced-fields inline_fields">');
            $this->buildPropertyTypeField();
            $this->buildLandSizeField();
            $this->buildBedroomField();
            $this->buildBathroomField();
            $this->buildYearBuiltField();
        $this->form->addHTML('</div>');
    }
    
    protected function buildPropertyTypeField() {
        if ($this->addPropertyTypeField) {
            $this->form->addHTML('<div class="inline_cell field_cell property_type_cell">');

            $tagTypeRef = 'mls_dwelling';
            if($this->listingTypeRef == 'com'){
                $tagTypeRef = 'mls_com_prop_type';
            }
            TagFactory::setDBType(MLSListingFactory::getDBType());
            $propertyTagArray = TagFactory::search()
                    ->filterTypeByRef('tag', $tagTypeRef)
                    ->select();
            TagFactory::resetDBType();

            $propertyTypeOptions = array();
            foreach($propertyTagArray as $tag){
                $propertyTypeOptions[$tag->getProperty('id')] = $tag->getProperty('title');
            }

            $this->form->addField('search_property_type', 'dropdown', array(
                'displayName'=> 'Dwelling Type',
                'options' => $propertyTypeOptions,
                'value' => $this->getQueryValue('property_type')
            ));
            $this->form->addHTML('</div>');
        }
    }
    protected function buildLandSizeField() {
        if ($this->addLandSizeField) {
            $this->form->addHTML('<div class="inline_cell field_cell land_size_cell">');

            $this->form->addField('search_floor_area', 'dropdown',array(
                    'displayName' => 'Floor Space',
                    'options' => array(
                        ',1000' => 'under 1000 sq. ft',
                        '1000,2000' => '1000 to 2000 sq. ft',
                        '2000,3000' => '2000 to 3000 sq. ft',
                        '3000,5000' => '3000 to 5000 sq. ft',
                        '5000,10000' => '5000 to 10000 sq. ft',
                        '10000' => 'over 10000 sq. ft'
                    ),
                    'value' => $this->getQueryValue('floor_area'),
                ));
            $this->form->addHTML('</div>');
        }
    }
    
    
    protected function buildBedroomField() {
        if ($this->addBedroomField) {
            $this->form->addHTML('<div class="inline_cell field_cell bedroom_cell">');

            $this->form->addField('search_bedrooms', 'dropdown',array(
                    'displayName' => 'Bedrooms',
                    'options' => array(
                        '1' => '1 Bedroom',
                        '1,+' => '1+ Bedrooms',
                        '2' => '2 Bedrooms',
                        '2,+' => '2+ Bedrooms',
                        '3' => '3 Bedrooms',
                        '3,+' => '3+ Bedrooms',
                        '4' => '4 Bedrooms',
                        '4,+' => '4+ Bedrooms',
                        '5' => '5 Bedrooms',
                        '5,+' => '5+ Bedrooms',
                    ),
                    'value' => $this->getQueryValue('bedrooms'),
                ));
            $this->form->addHTML('</div>');
        }
    }
    
    protected function buildBathroomField() {
        if ($this->addBathroomField) {
            $this->form->addHTML('<div class="inline_cell field_cell bathroom_cell">');

            $this->form->addField('search_bathrooms', 'dropdown',array(
                    'displayName' => 'Bathrooms',
                    'options' => array(
                        '1' => '1 Bathroom',
                        '1,+' => '1+ Bathrooms',
                        '2' => '2 Bathrooms',
                        '2,+' => '2+ Bathrooms',
                        '3' => '3 Bathrooms',
                        '3,+' => '3+ Bathrooms',
                        '4' => '4 Bathrooms',
                        '4,+' => '4+ Bathrooms',
                        '5' => '5 Bathrooms',
                        '5,+' => '5+ Bathrooms',
                    ),
                    'value' => $this->getQueryValue('bathrooms'),
                ));
            $this->form->addHTML('</div>');
        }
    }
    
    protected function buildYearBuiltField() {
        if ($this->addYearBuiltField) {
            $this->form->addHTML('<div class="inline_cell field_cell year_built_cell">');
            /*ex:
            array(
                '2016' => 'over 2015',
                '2011,2015' => '2011 - 2015',
                '2006,2010' => '2006 - 2010',
                '2001,2005' => '2001 - 2005',
                '1996,2000' => '1996 - 2000',
                '1991,1995' => '1991 - 1995',
                '1986,1990' => '1986 - 1990',
                ',1985' => 'under 1986'
            )*/
            $curYear = date("Y");
            $step = 5;
            $cnt = 6;
            //Get the first year ending with 5 or 10
            $firstStartYear = $curYear - ($curYear % 5);
            $yearOptions[$firstStartYear + 1] = 'over ' . $firstStartYear;
            for ($i=0; $i < $cnt; $i++) {
                $startYear = $firstStartYear - $step*($i+1) + 1;
                $endYear = $firstStartYear - $step*$i;
                $yearOptions[$startYear . ',' . $endYear] = $startYear . ' - ' . $endYear;
            }
            $endYear = $firstStartYear - $step*$cnt + 1;
            $yearOptions[',' . ($endYear - 1)] = 'under ' . $endYear;
            $this->form->addField('search_year', 'dropdown',array(
                    'displayName' => 'Year Built',
                    'options' => $yearOptions,
                    'value' => $this->getQueryValue('year'),
                ));
            $this->form->addHTML('</div>');
        }
    }
}
