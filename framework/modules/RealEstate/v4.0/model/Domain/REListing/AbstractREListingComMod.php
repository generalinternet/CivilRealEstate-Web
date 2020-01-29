<?php

abstract class AbstractREListingComMod extends AbstractREListingCom {
    
    public function getFormView(\GI_Form $form, $buildForm = true) {
        $formView = new REModFormView($form, $this);
        $uploader = $this->getUploader($form);
        $formView->setUploader($uploader);
        //MLS Listing sample model to search
        $mlsListing = $this->getMLSListing();
        if (empty($mlsListing)) {
            $mlsListing = MLSListingFactory::buildNewModel('com');
        }
        $formView->setMLSListing($mlsListing);
        
        if($buildForm){
            $formView->buildForm();
        }
        return $formView;
    }
    
    public function getAddURLAttributes(){
        $urlAttributes = array(
            'controller' => 're',
            'action' => 'indexMLS',
            'type' => $this->getTypeRef(),
//            'search' => 1,
//            'targetId' => 'main_window',
        );
        return $urlAttributes;
    }
}
