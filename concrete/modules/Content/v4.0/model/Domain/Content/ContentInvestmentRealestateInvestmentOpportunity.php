<?php

class ContentInvestmentRealestateInvestmentOpportunity extends ContentInvestmentRealestate {
    
    public function getView() {
        $contentView = new ContentInvestmentRealEstateInvestmentDetailView($this);
        return $contentView;
    }
    protected function buildNewFormView(\GI_Form $form){
        return new ContentInvestmentRealEstateInvestmentFormView($form, $this, false);
    }
}