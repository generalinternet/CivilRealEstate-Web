<?php

abstract class GI_LoadMoreBtn extends GI_View {
    
    protected $linkArray;
    protected $itemsPerPage;
    protected $totalCount;
    protected $currentPage;
    protected $label = 'Load More';
    protected $cssClass = 'load_more_btn';
    protected $step = 1;
    protected $reverseLoadBtns = false;
    
    public function __construct($linkArray, $itemsPerPage, $totalCount, $currentPage = 1) {
        parent::__construct();
        $this->setLinkArray($linkArray);
        $this->setItemsPerPage($itemsPerPage);
        $this->setTotalCount($totalCount);
        $this->setCurrentPage($currentPage);
    }
    
    public function setLinkArray($linkArray){
        $this->linkArray = $linkArray;
        if(!isset($this->linkArray['queryId'])){
            $this->linkArray['queryId'] = 0;
        }
    }
    
    public function setItemsPerPage($itemsPerPage){
        $this->itemsPerPage = $itemsPerPage;
        if(empty($this->itemsPerPage) && !empty($this->totalCount)){
            $this->itemsPerPage = $this->totalCount;
        }
        return $this;
    }
    
    public function setTotalCount($totalCount){
        $this->totalCount = $totalCount;
        if(empty($this->itemsPerPage)){
            $this->setItemsPerPage($this->totalCount);
        }
        return $this;
    }
    
    public function setCurrentPage($currentPage){
        $this->currentPage = $currentPage;
        return $this;
    }
    
    public function setLabel($label){
        $this->label = $label;
        return $this;
    }
    
    public function setReverseLoadBtns($reverseLoadBtns) {
        $this->reverseLoadBtns = $reverseLoadBtns;
        return $this;
    }
    
    protected function getPageToLoad() {
        return $this->currentPage + $this->step;
    }
    
    protected function getPageCount() {
        return ceil($this->totalCount / $this->itemsPerPage);
    }
    
    protected function hasPageToLoad() {
        $pageCount = $this->getPageCount();
        return ($this->currentPage < $pageCount);
    }
    
    public function buildBtn(){
        $pageCount = $this->getPageCount();
        if ($this->hasPageToLoad()) {
            $nextPage = $this->getPageToLoad();
            $loadMoreURLProps = $this->linkArray;
            if(isset($loadMoreURLProps['pageNumber'])){
                unset($loadMoreURLProps['pageNumber']);
            }
            $reverseLoadBtns = 0;
            if ($this->reverseLoadBtns) {
                $reverseLoadBtns = 1;
            }
            $loadMoreURL = GI_URLUtils::buildURL($loadMoreURLProps, false, true);
            $this->addHTML('<div class="load_more_btn_wrap">');
            $this->addHTML('<a class="'.$this->cssClass.'" href="' . $loadMoreURL . '" data-page="' . $this->currentPage . '" data-next-page="' . $nextPage . '" data-page-count="' . $pageCount . '" data-step="' . $this->step . '"  data-reverse="'.$reverseLoadBtns.'">');
                $this->addHTML('<span class="btn_text">' . $this->label . '</span>');
            $this->addHTML('</a>');
            $this->addHTML('</div>');
        }
    }
    
    public function beforeReturningView() {
        $this->buildBtn();
    }
    
}
