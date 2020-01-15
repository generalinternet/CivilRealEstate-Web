<?php

class ContentInvestmentRealestateInvestmentDetailView extends ContentInvestmentRealestateDetailView{
    protected function buildRealEstateDetailSection(){
        $this->addHTML('<div class="content_group">');
        $this->addHTML('<h2 class="content_group_title">Real Estate Details</h2>');
        $this->addHTML('<div class="auto_columns halves">');       
        $this->addContentBlockWithWrap($this->content->getPropertyTypeTitle(), 'Category');
        $this->addContentBlockWithWrap($this->content->getAddrString(), 'Address');        
        $this->addHTML('</div>');
        $this->addHTML('</div>');
    }

    protected function buildOtherFieldsSection(){
        return;
    }
}