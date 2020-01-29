<?php

abstract class AbstractREListingRes extends AbstractREListing {
    
    public function getViewTitle($plural = true) {
        $title = 'Residential ';
        $title .= parent::getViewTitle($plural);
        return $title;
    }
    
    public function handleFormSubmission(GI_Form $form){
        if($form->wasSubmitted() && $form->validate()){
            $fieldArray = array(
                'features',
                'site_influences',
                'gross_taxes',
                'tax_year',
                'floor_area_finished',
                'floor_area_total',
                'full_baths',
                'half_baths',
                'total_baths',
                'total_bedrooms',
            ); 
            
            foreach($fieldArray as $field){
                $value = filter_input(INPUT_POST, $field);
                $this->setProperty('re_listing_res.' . $field, $value);
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
                $this->setProperty('re_listing_res.' . $field, $value);
            }
            
            return parent::handleFormSubmission($form);
        }
        
        return false;
    }
}
