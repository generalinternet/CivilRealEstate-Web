<?php

class ContentInvestmentListItemDetailView extends GI_View{
    
    protected $investment;

    public function __construct(ContentInvestment $investment) {
        parent::__construct();
        $this->investment = $investment;  
    }
    
    protected function openViewWrap() {
        $this->addHTML('<div class="investment investment_theme_list-item">');
        return $this;
    }

    protected function closeViewWrap() {
        $this->addHTML('</div>');
        return $this;
    }
    public function buildView() {
        $this->openViewWrap();
        $this->addHTML('<div class="investment__header-title-wrap">');
        $this->addTypeTitleBlock();
        $this->addInvestmentTitleBlock();
        $this->addHTML('</div>');
        $this->addFeaturedMediaBlock();
        $this->addInvestmentPreviewBlock();
        $this->addViewDetailButtonBlock();
        $this->closeViewWrap();
    }
    
    public function addFeaturedMediaBlock() {
        $this->addHTML('<div class="investment__featured-media">');
            $this->addHTML($this->investment->getFeaturedMediaBlockHTML());
        $this->addHTML('</div>');
    }
    
    public function addInvestmentPreviewBlock() {
        $this->addHTML('<div class="investment__detail-wrap investment__detail-wrap_has-icon">');
            $this->addHTML('<span class="investment__icon investment__icon_type_question"></span>');
            $this->addHTML($this->getInvestmentPreviewBlockHTML());
        $this->addHTML('</div>');
    }
    
    public function getInvestmentPreviewBlockHTML() {

        // $html = '<a href="'.$this->investment->getPublicDetailViewURL().'">';
        $html = '<div class="investment__detail-body">';
        $html .= $this->investment->getPreviewBockHTML();
        $html .= $this->investment->getStatusDetailBockHTML();
        $html .= '</div><!--.detail-body-->';
        // $html .= "</a>";
        return $html;
    }

    public function addTypeTitleBlock(){
        $html = $this->investment->getTypeHeaderBlock();
        $this->addHTML($html);
    }

    public function addInvestmentTitleBlock(){
        $html = $this->investment->getTitleBockHTML();
        $this->addHTML($html);
    }
    
    public function addViewDetailButtonBlock(){
        $html = '<div class="investment__button-wrap">';
        $html .= '<a class="investment__button button button_theme_primary button_has-icon" href="'.$this->investment->getPublicDetailViewURL().'" title="Click to view details">VIEW DETAILS <span class="button__icon button__icon_color_dark"></span></a>';
        $html .= '</div>';
        $this->addHTML($html);
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
}
