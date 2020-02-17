<?php

class RESearchFormView extends GI_View{
    protected $useShadowBox = false;
    protected $useBasicSearch = false;
    protected $hideAdvancedSearch = false;
    protected $useAjax = false;
    protected $form = null;

    public function __construct(GI_Form $form = NULL)
    {
        if(empty($form)){
            $form = new GI_Form('real_estate_search');
        }
        $this->form = $form;
        $this->buildForm();
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
        $this->addRangeField('Price (in CAD)', 'price');
    }

    protected function addPropertyTypeField(){
        $typeArr = MLSListingFactory::getTypesArray();
        $this->form->addField('property_type', 'checkbox', array(
            'class' => 'form__input form__input_type_checkbox',
            'displayName' => "Property Type",
            'options'=> $typeArr,
        ));
    }

    protected function addAreaField(){
        $this->addRangeField('Area (in sqft)', 'area');
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
                            '100000' => '100,000',
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

    public function beforeReturningView(){
        $this->addHTML($this->form->getForm('Search'));
    }

}
