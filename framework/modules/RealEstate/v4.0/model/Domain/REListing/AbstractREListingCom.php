<?php

abstract class AbstractREListingCom extends AbstractREListing {
    
    protected $propertyTypeTagTypeRef = 're_com_prop_type';


    public function getViewTitle($plural = true) {
        $title = 'Commercial ';
        $title .= parent::getViewTitle($plural);
        return $title;
    }
    
    public function handleFormSubmission(GI_Form $form){
        if($form->wasSubmitted() && $form->validate()){
            $fieldArray = array(
                'business_type_major',
                'num_of_units'
            ); 
            
            foreach($fieldArray as $field){
                $value = filter_input(INPUT_POST, $field);
                $this->setProperty('re_listing_com.' . $field, $value);
            }
            
            return parent::handleFormSubmission($form);
        }
        
        return false;
    }
    
    public function handleModifyFormSubmission(GI_Form $form){
        if($form->wasSubmitted() && $form->validate()){
            $fieldArray = array(
                
            ); 
            
            foreach($fieldArray as $field){
                $value = filter_input(INPUT_POST, $field);
                $this->setProperty('re_listing_com.' . $field, $value);
            }
            
            return parent::handleFormSubmission($form);
        }
        
        return false;
    }
}
