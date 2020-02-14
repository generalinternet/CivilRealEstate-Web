<?php

class RESearchFormView extends AbstractRESearchFormView{
    protected $useShadowBox = false;
    protected $useBasicSearch = false;
    protected $hideAdvancedSearch = false;
    protected $useAjax = false;

    public function getShadowBoxURL(){
        if(is_null($this->searchAttributes)){
            $curAttributes = GI_URLUtils::getAttributes();
        } else {
            $curAttributes = $this->searchAttributes;
        }
        $curAttributes['search'] = 1;
        if(isset($this->queryValues['queryId'])){
            $curAttributes['queryId'] = $this->queryValues['queryId'];
        }
        return GI_URLUtils::buildURL($curAttributes, false, true);
    }

    protected function buildForm(){
        $this->form->addHTML('<div class="relisting-search">');
        $this->addFavouritesField();
        $this->addPriceField();
        $this->addPropertyTypeField();
        $this->addAreaField();
        $this->addPropertyStatusField();
        $this->addDatePostedField();
        $this->addFeaturesField();
        $this->form->addHTML('</div>');
    }

    protected function addFavouritesField(){
        $options = array(
            'only_favourites' => 'Show Only Favourites'
        );
        $this->form->addField('favourites', 'checkbox', array(
            'class' => 'form__input form__input_type_checkbox',
            'displayName' => "Favourites",
            'options'=> $options
        ));
    }
    
    protected function addPriceField(){
        $this->addRangeField('Price (in CAD)', 'search_price');
    }

    protected function addPropertyTypeField(){
        $options = array(
            'detached_home' => 'Detached Home',
            'apartment_condo' => 'Apartment / Condo',
            'estate_farm' => 'Estate / Farm',
            'commercial' => 'Commercial',
            'land' => 'Land'
        );
        $this->form->addField('search_property_type', 'checkbox', array(
            'class' => 'form__input form__input_type_checkbox',
            'displayName' => "Property Type",
            'options'=> $options,
            'value' => $this->getQueryValue('property_type')
        ));
    }

    protected function addAreaField(){
        $this->addRangeField('Area (in sqft)', 'search_area');
    }

    protected function addPropertyStatusField(){
        $options = array(
            'ready_to_move_in' => 'Ready to Move In',
            'under_construction' => 'Under Construction',
        );
        $this->form->addField('property_status', 'checkbox', array(
            'class' => 'form__input form__input_type_checkbox',
            'displayName' => "Property Status",
            'options'=> $options
        ));
    }

    protected function addDatePostedField(){
        $options = array(
            'last_four_weeks' => 'Last Four Weeks',
            'last_three_weeks' => 'Last Three Weeks',
            'last_two_weeks' => 'Last Two Weeks',
            'last_week' => 'Last Week',
        );
        $this->form->addField('date_posted', 'dropdown', array(
            'class' => 'form__input form__input_type_dropdown',
            'displayName' => "Date Posted",
            'options'=> $options
        ));
    }

    protected function addFeaturesField(){
        $options = array(
            'covered_parking' => 'Covered Parking',
            'private_outdoor_area' => 'Private Outdoor Area',
            'Air Conditioning' => 'Air Conditioning',
            'balcony' => 'Balcony',
            'enclosed_yard' => 'Enclosed Yard',
            'attached_garage' => 'Attached Garage',
            'sauna' => 'Sauna',
            'steam_room' => 'Steam Room',
            'view' => 'View',
            'storage' => 'Storage',
            'fireplace' => 'Fireplace',
            'en_suite_laundry' => 'En Suite Laundry',
            'jacuzzi' => 'Jacuzzi',
            'common_room' => 'Common Room',
            'finished_basement' => 'Finished Basement',
            'high_end_appliances' => 'High End Appliances',
            'guest_suite' => 'Guest Suite',
            'penthouse' => 'Penthouse',
        );
        $this->form->addField('features', 'checkbox', array(
            'class' => 'form__input form__input_type_checkbox',
            'displayName' => "Features",
            'options'=> $options,
        ));
    }

    protected function addRangeField($title, $name){
        $this->form->addHTML('<div class="form__input form__input_type_range">');
            $this->form->addHTML('<label class="main">'.$title.'</label>');
            $this->form->addHTML('<div class="field_content">');
                $this->form->addHTML('<div class="form__input-wrap">');
                    $this->form->addField($name.'_min', 'dropdown', array(
                        'class' => 'form__input form__input_type_dropdown',
                        'placeHolder' => 'Min',
                        'options' => array(
                            '100' => 100,
                            '200' => 200,
                            '300' => 300,
                            '400' => 400,
                        )
                    ));
                    $this->form->addField($name.'_max', 'dropdown', array(
                        'class' => 'form__input form__input_type_dropdown',
                        'placeHolder' => 'Max',
                        'options' => array(
                            '100' => 100,
                            '200' => 200,
                            '300' => 300,
                            '400' => 400,
                        )
                    ));
                $this->form->addHTML('</div>');
            $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
    }

    protected function buildView()
    {
        $this->addHTML($this->form->getForm('Search'));
    }
}
