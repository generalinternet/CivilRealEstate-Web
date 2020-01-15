<?php

class ContentInvestmentCorporationDetailView extends ContentInvestmentDetailView{
    
    /** @var ContentInvestmentCorporation  */
    protected $content;
    
    protected function addInvestmentDetailFields(){

        $this->addContentBlockWithWrap($this->content->getCorporationCategoryTitle(), 'Category');

        parent::addInvestmentDetailFields();
        if($this->content->getTypeRef() == ContentInvestmentCorporation::$TYPE_OPPORTUNITY_REF){
            $this->addContentBlockWithWrap($this->content->getExpectedReturns(), 'Expected Return');
        }

        $this->addContentBlockWithWrap($this->content->getProperty('content_investment_corporation.investors'), 'Investors');
        $this->addContentBlockWithWrap($this->content->getProperty('content_investment_corporation.equity'), 'Equity');
        $this->addContentBlockWithWrap($this->content->getProperty('content_investment_corporation.industry'), 'Industry');
        
        $this->addContentBlockWithWrap($this->content->getProperty('content_investment_corporation.location'), 'Location');
        $this->addContentBlockWithWrap($this->content->getProperty('content_investment_corporation.currency'), 'Currency');
        $this->addContentBlockWithWrap($this->content->getProperty('content_investment_corporation.employees'), 'Employees');
        $this->addContentBlockWithWrap($this->content->getProperty('content_investment_corporation.incorporation_type'), 'Incorporation Type');
        $this->addContentBlockWithWrap($this->content->getProperty('content_investment_corporation.found_date'), 'Found Date');
        $this->addContentBlockWithWrap($this->content->getProperty('content_investment_corporation.website'), 'Website');
        
        $this->addHTML('</div>');
        $this->addHTML('<hr>');  
        $this->addHTML('<div class="auto_columns halves custom_fields">');
            $this->addHTML('<div class="auto_column">');
            $this->addContentBlockWithWrap($this->content->getProperty('content_investment_corporation.slogan'), 'Slogan');
            $this->addContentBlockWithWrap($this->content->getDisplaySummary(), 'Company Summary');
            $this->addHTML('</div>');
            $this->addHTML('<div class="auto_column">');
            
            $logoImageFolder = $this->content->getLogoImageFolder();
            if($logoImageFolder){
                $this->addHTML('<div class="content_block_wrap">');
                    $this->addHTML('<h3 class="content_block_title">Logo Image</h3>');
                    $this->addImageFileViews($logoImageFolder);
                $this->addHTML('</div>');
            }
        
            $this->addHTML('</div>');
    }
    
    
}
