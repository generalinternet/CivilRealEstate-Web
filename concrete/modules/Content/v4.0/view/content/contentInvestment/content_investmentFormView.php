<?php

class ContentInvestmentFormView extends ContentFormView{
    
    protected $featuredImageUploader = NULL;
    protected $bannerImageUploader = NULL;
    protected $downloadThumbImageUploader = NULL;
    
    public function setFeaturedImageUploader(AbstractGI_Uploader $featuredImageUploader = NULL){
        $this->featuredImageUploader = $featuredImageUploader;
        return $this;
    }
    
    public function setBannerImageUploader(AbstractGI_Uploader $bannerImageUploader = NULL){
        $this->bannerImageUploader = $bannerImageUploader;
        return $this;
    }

    public function setDownloadThumbImageUploader(AbstractGI_Uploader $downloadThumbImageUploader = NULL){
        $this->downloadThumbImageUploader = $downloadThumbImageUploader;
        return $this;
    }
    
    public function buildFormGuts() {
        parent::buildFormGuts();
        
        $this->form->addHTML('<hr>');
        
        $this->form->addHTML('<div class="custom_fields">');
            $this->form->addHTML('<div class="auto_columns thirds">');
            $this->addFeaturedImageUploader();
            // $this->addBannerImageUploader();
            $this->addDownloadThumbImageUploader();
            $this->form->addHTML('</div>');
            $this->form->addHTML('<hr>');
            $this->form->addHTML('<div class="auto_columns">');
            $this->addFeaturedYoutubeVideoField();
            $this->addFeaturedListingField();
            $this->form->addHTML('</div>');
            $this->form->addHTML('</div>');
        
        $this->form->addHTML('<div class="auto_columns quarters custom_fields">');
        $this->addInvestStatusField();
        $this->addFundsRateField();
        $this->addTargetAmtField();
        $this->addInvestedAmtField();
        $this->addDueDateField();
        if($this->content->getTypeRef() == ContentInvestmentCorporation::$TYPE_OPPORTUNITY_REF){
            $this->addExpectedReturnsField();
        }
        $this->form->addHTML('</div>');
    }
    
    public function addFeaturedImageUploader(){
        if($this->featuredImageUploader){
            $this->form->addHTML($this->featuredImageUploader->getHTMLView());
        }
    }
    
    public function addBannerImageUploader(){
        if($this->bannerImageUploader){
            $this->form->addHTML($this->bannerImageUploader->getHTMLView());
        }
    }

    public function addDownloadThumbImageUploader(){
        if($this->downloadThumbImageUploader){
            $this->form->addHTML($this->downloadThumbImageUploader->getHTMLView());
        }
    }
    
    public function addFeaturedYoutubeVideoField(){
        $this->form->addField($this->content->getFieldName('featured_youtube_video_url'), 'text', array(
            'displayName' => 'Featured Youtube Video URL',
            'placeHolder' => 'i.e. https://youtu.be/DAVtm9uTolw',
            'value' => $this->content->getProperty('content_investment.featured_youtube_video_url'),
        ));
    }

    public function addFeaturedListingField(){
        $this->form->addField($this->content->getFieldName('is_featured_investment'), 'checkbox', array(
            'displayName' => 'Is featured listing ?',
            'options' => array(
                '1' => 'Yes',
            ),
            'value' => $this->content->getProperty('content_investment.is_featured_investment'),
        ));
    }
    
    public function addInvestStatusField(){
        $this->form->addField($this->content->getFieldName('invest_status'), 'dropdown', array(
            'displayName' => 'Project Status',
            'options'=> ContentFactory::$OPITIONS_INVEST_STATUS,
            'value' => $this->content->getProperty('content_investment.invest_status'),
            'required' => true,
        ));
    }
    
    public function addTargetAmtField(){
        $this->form->addField($this->content->getFieldName('target_amt'), 'money', array(
            'displayName' => 'Target Amount',
            'placeHolder' => 'i.e. 3000000',
            'value' => $this->content->getProperty('content_investment.target_amt'),
        ));
    }
    
    public function addInvestedAmtField(){
        $this->form->addField($this->content->getFieldName('invested_amt'), 'money', array(
            'displayName' => 'Invested Amount',
            'placeHolder' => 'i.e. 3000000',
            'value' => $this->content->getProperty('content_investment.invested_amt'),
        ));
    }
    
    public function addDueDateField(){
        $date = new DateTime();
        $date->add(new DateInterval('P1M'));
        $placeholderDate = $date->format('Y-m-d');
        $this->form->addField($this->content->getFieldName('due_date'), 'date', array(
            'displayName' => 'Due Date',
            'value' => $this->content->getProperty('content_investment.due_date'),
            'placeHolder' => 'i.e. '.$placeholderDate,
            'required' => true,
        ));
    }

    public function addExpectedReturnsField(){
        $this->form->addField($this->content->getFieldName('expected_returns'), 'text', array(
            'displayName' => 'Expected Returns',
            // 'placeHolder' => 'i.e. ',
            'value' => $this->content->getProperty('content_investment.expected_returns'),
        ));
    }
    
    public function addFundsRateField(){
        $this->form->addField($this->content->getFieldName('funds_rate'), 'integer', array(
            'displayName' => 'Funded (%)',
            'placeHolder' => 'i.e. 30',
            'value' => $this->content->getProperty('content_investment.funds_rate'),
        ));
    }
}
