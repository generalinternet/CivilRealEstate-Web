<?php

class RESearchFormView extends AbstractRESearchFormView{
    protected $useShadowBox = false;
    protected $useBasicSearch = false;
    protected $hideAdvancedSearch = false;
    protected $useAjax = false;
    protected $form = null;

    protected function buildForm(){
        $this->form->addHTML('<div class="relisting-search">');
        $this->addFavouritesField();
        $this->addBedroomsField();
        $this->addBathroomsField();
        $this->addPriceField();
        $this->addPropertyTypeField();
        $this->addAreaField();
        // $this->addPropertyStatusField();
        $this->addDatePostedField();
        // $this->addFeaturesField();
        $this->form->addHTML('</div>');
    }

    protected function addFavouritesField(){
        $options = array(
            'only_favourites' => 'Show Only Favourites'
        );
        $this->form->addHTML('<div class="relisting-search__field">');
            $this->form->addHTML('<h3 class="relisting-search__field-label">Favourites</h3>');
            $this->form->addHTML('<div class="relisting-search__field-wrap">');
                $this->form->addField('favourites', 'checkbox', array(
                    'class' => 'form__input form__input_type_checkbox',
                    'options'=> $options,
                    'value' => $this->getQueryValue('favourite')
                ));
                $this->addApplyButton();
            $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
    }
    
    protected function addPriceField(){
        $options = array(
            '00' => '0',
            25000 => '25,000',
            50000 => '50,000',
            75000 => '75,000',
            100000 => '100,000',
            125000 => '125,000',
            150000 => '150,000',
            175000 => '175,000',
            200000 => '200,000',
            225000 => '225,000',
            250000 => '250,000',
            275000 => '275,000',
            300000 => '300,000',
            325000 => '325,000',
            350000 => '350,000',
            375000 => '375,000',
            400000 => '400,000',
            425000 => '425,000',
            450000 => '450,000',
            475000 => '475,000',
            500000 => '500,000',
            550000 => '550,000',
            600000 => '600,000',
            650000 => '650,000',
            700000 => '700,000',
            750000 => '750,000',
            800000 => '800,000',
            850000 => '850,000',
            900000 => '900,000',
            1000000 => '1,000,000',
            1500000 => '1,500,000',
            2000000 => '2,000,000',
            2500000 => '2,500,000',
            3000000 => '3,000,000',
            3500000 => '3,500,000',
            4000000 => '4,000,000',
            4500000 => '4,500,000',
            5000000 => '5,000,000',
            5500000 => '5,500,000',
            6000000 => '6,000,000',
            6500000 => '6,500,000',
            7000000 => '7,000,000',
            8000000 => '8,000,000',
            9000000 => '9,000,000',
            10000000 => '10,000,000',
            15000000 => '15,000,000',
            20000000 => '20,000,000'
        );
        $maxOptions = array(
            'NULL' => 'Unlimited',
            25000 => '25,000',
            50000 => '50,000',
            75000 => '75,000',
            100000 => '100,000',
            125000 => '125,000',
            150000 => '150,000',
            175000 => '175,000',
            200000 => '200,000',
            225000 => '225,000',
            250000 => '250,000',
            275000 => '275,000',
            300000 => '300,000',
            325000 => '325,000',
            350000 => '350,000',
            375000 => '375,000',
            400000 => '400,000',
            425000 => '425,000',
            450000 => '450,000',
            475000 => '475,000',
            500000 => '500,000',
            550000 => '550,000',
            600000 => '600,000',
            650000 => '650,000',
            700000 => '700,000',
            750000 => '750,000',
            800000 => '800,000',
            850000 => '850,000',
            900000 => '900,000',
            1000000 => '1,000,000',
            1500000 => '1,500,000',
            2000000 => '2,000,000',
            2500000 => '2,500,000',
            3000000 => '3,000,000',
            3500000 => '3,500,000',
            4000000 => '4,000,000',
            4500000 => '4,500,000',
            5000000 => '5,000,000',
            5500000 => '5,500,000',
            6000000 => '6,000,000',
            6500000 => '6,500,000',
            7000000 => '7,000,000',
            8000000 => '8,000,000',
            9000000 => '9,000,000',
            10000000 => '10,000,000',
            15000000 => '15,000,000',
            20000000 => '20,000,000'
        );
        $this->form->addHTML('<div class="relisting-search__field">');
            $this->form->addHTML('<h3 class="relisting-search__field-label">Price (in CAD)</h3>');
            $this->form->addHTML('<div class="relisting-search__field-wrap">');
                $this->addRangeField('price', $options, $maxOptions);
            $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
    }

    protected function addBedroomsField(){
        $leftOptions = array(
            '00' => '0',
            1 => '1',
            2 => '2',
            3 => '3',
            4 => '4',
        );
        $rightOptions = array(
            1 => '1',
            2 => '2',
            3 => '3',
            4 => '4',
            '5+' => '5+',
        );
        $this->form->addHTML('<div class="relisting-search__field">');
            $this->form->addHTML('<h3 class="relisting-search__field-label">Bedrooms</h3>');
            $this->form->addHTML('<div class="relisting-search__field-wrap">');
                $this->addRangeField('bedroom', $leftOptions, $rightOptions);
            $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
    }

    protected function addBathroomsField(){
        $leftOptions = array(
            '00' => '0',
            1 => '1',
            2 => '2',
            3 => '3',
            4 => '4',
        );
        $rightOptions = array(
            1 => '1',
            2 => '2',
            3 => '3',
            4 => '4',
            '5+' => '5+',
        );
        $this->form->addHTML('<div class="relisting-search__field">');
            $this->form->addHTML('<h3 class="relisting-search__field-label">Bathrooms</h3>');
            $this->form->addHTML('<div class="relisting-search__field-wrap">');
                $this->addRangeField('bathroom', $leftOptions, $rightOptions);
            $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
    }

    protected function addPropertyTypeField(){
        $mlsTypeArr = MLSListingFactory::getTypesArray();
        $reTypeArr = REListingFactory::getTypesArray();

        $typeArr = array_unique(array_merge($mlsTypeArr, $reTypeArr));

        $this->form->addHTML('<div class="relisting-search__field">');
            $this->form->addHTML('<h3 class="relisting-search__field-label">Property Type</h3>');
            $this->form->addHTML('<div class="relisting-search__field-wrap">');
                $this->form->addField('property_type', 'checkbox', array(
                    'class' => 'form__input form__input_type_checkbox',
                    'options'=> $typeArr,
                    'value' => $this->getQueryValue('property_type')
                ));
                $this->addApplyButton();
            $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
    }

    protected function addAreaField(){
        $options = array(
            '00' => '0',
            1000 => '1000',
            2000 => '2000',
            3000 => '3000',
            4000 => '4000',
            5000 => '5000',
        );
        $maxOptions = array(
            1000 => '1000',
            2000 => '2000',
            3000 => '3000',
            4000 => '4000',
            5000 => '5000',
            '5000+' => '5000 +',
        );
        $this->form->addHTML('<div class="relisting-search__field">');
            $this->form->addHTML('<h3 class="relisting-search__field-label">Area (in sqft)</h3>');
            $this->form->addHTML('<div class="relisting-search__field-wrap">');
                $this->addRangeField('area', $options, $maxOptions);
            $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
    }

    protected function addPropertyStatusField(){
        $options = array(
            'ready_to_move_in' => 'Ready to Move In',
            'under_construction' => 'Under Construction',
        );
        $this->form->addHTML('<div class="relisting-search__field">');
            $this->form->addHTML('<h3 class="relisting-search__field-label">Property Status</h3>');
            $this->form->addHTML('<div class="relisting-search__field-wrap">');
                $this->form->addField('property_status', 'checkbox', array(
                    'class' => 'form__input form__input_type_checkbox',
                    'options'=> $options,
                    'value' => $this->getQueryValue('property_status')
                ));
                $this->addApplyButton();
            $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
    }

    protected function addDatePostedField(){
        $options = array(
            'older_than_1_month' => 'Older Than 1 Month',
            'last_four_weeks' => 'Last Four Weeks',
            'last_three_weeks' => 'Last Three Weeks',
            'last_two_weeks' => 'Last Two Weeks',
            'last_week' => 'Last Week',
        );
        $this->form->addHTML('<div class="relisting-search__field">');
            $this->form->addHTML('<h3 class="relisting-search__field-label">Date Posted</h3>');
            $this->form->addHTML('<div class="relisting-search__field-wrap">');
                $this->form->addField('date_posted', 'dropdown', array(
                    'class' => 'form__input form__input_type_dropdown',
                    'options'=> $options,
                    'value' => $this->getQueryValue('date_posted')
                ));
                $this->addApplyButton();
            $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
    }

    protected function addFeaturesField(){
        $options = array(
            'Covered Parking' => 'Covered Parking',
            'Private Outdoor Area' => 'Private Outdoor Area',
            'Air Conditioning' => 'Air Conditioning',
            'Balcony' => 'Balcony',
            'Enclosed Yard' => 'Enclosed Yard',
            'Attached Garage' => 'Attached Garage',
            'Sauna' => 'Sauna',
            'Steam Room' => 'Steam Room',
            'View' => 'View',
            'Storage' => 'Storage',
            'Fireplace' => 'Fireplace',
            'En Suite Laundry' => 'En Suite Laundry',
            'Jacuzzi' => 'Jacuzzi',
            'Common Room' => 'Common Room',
            'Finished Basement' => 'Finished Basement',
            'High End Appliances' => 'High End Appliances',
            'Guest Suite' => 'Guest Suite',
            'Penthouse' => 'Penthouse',
        );
        $this->form->addHTML('<div class="relisting-search__field">');
            $this->form->addHTML('<h3 class="relisting-search__field-label">Features</h3>');
            $this->form->addHTML('<div class="relisting-search__field-wrap">');
                $this->form->addField('features', 'checkbox', array(
                    'class' => 'form__input form__input_type_checkbox',
                    'options'=> $options,
                    'value' => $this->getQueryValue('features')
                ));
                $this->addApplyButton();
            $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
    }

    protected function addRangeField($name, $minOptions, $maxOptions){
        $this->form->addHTML('<div class="form__input form__input_type_range">');
            $this->form->addHTML('<div class="field_content">');
                $this->form->addHTML('<div class="form__input-wrap">');
                    $minName = $name.'_min';
                    $this->form->addField($minName, 'dropdown', array(
                        'class' => 'form__input form__input_type_dropdown',
                        'nullText' => 'Min',
                        'options' => $minOptions,
                        'value' => $this->getQueryValue($minName)
                    ));
                    $minName = $name.'_max';
                    $this->form->addField($minName, 'dropdown', array(
                        'class' => 'form__input form__input_type_dropdown',
                        'nullText' => 'Max',
                        'options' => $maxOptions,
                        'value' => $this->getQueryValue($minName)
                    ));
                $this->form->addHTML('</div>');
            $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
        $this->addApplyButton();
    }

    protected function addApplyButton(){
        $this->form->addHTML('<div class="text-right">');
        $this->form->addHTML('<span class="button button_theme_primary submit_btn">Apply</span>');
        $this->form->addHTML('</div>');
    }

    protected function buildView()
    {
        $this->addHTML($this->form->getForm('Search'));
    }

    public function beforeReturningView(){
        $this->addHTML($this->form->getForm('Search'));
    }

}
