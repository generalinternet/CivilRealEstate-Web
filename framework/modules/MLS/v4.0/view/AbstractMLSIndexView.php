<?php

/**
 * Description of AbstractMLSIndexView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractMLSIndexView extends ListWindowView {
    
    /** @var AbstractMLSListing[] */
    protected $models = array();
    /** @var AbstractMLSListing */
    protected $sampleModel = NULL;

    public function __construct($models, AbstractUITableView $uiTableView, AbstractMLSListing $sampleModel, GI_SearchView $searchView = NULL) {
        parent::__construct($models, $uiTableView, $sampleModel, $searchView);
        $this->addSiteTitle($sampleModel->getViewTitle(true));
        $typeTitle = $this->sampleModel->getTypeTitle();
        $windowIcon = 'real_estate';
        $this->addSiteTitle($typeTitle);
        $this->setWindowIcon($windowIcon);
        $this->setListItemTitle($this->sampleModel->getViewTitle());
    }
    
    protected function addWindowTitle(){
        $title = $this->sampleModel->getViewTitle();
        $this->setWindowTitle($title);
        parent::addWindowTitle();
        return $this;
    }
    
    protected function addWindowBtns() {
//        $this->addAddBtn();
    }
    
//    protected function addAddBtn(){
//        if ($this->sampleModel->isAddable()) {
//            //$addTitle = $this->sampleModel->getViewTitle(false);
//            $addTitle = 'Listing';
//            $addURL = $this->sampleModel->getAddURL();
//            $this->addHTML('<a href="' . $addURL . '" title="' . $addTitle . '" class="custom_btn" ><span class="icon_wrap"><span class="icon primary add"></span></span><span class="btn_text">' . $addTitle . '</span></a>');
//        }
//    }
    
    protected function addViewBodyContent() {
        $this->openPaddingWrap();

        if($this->searchView && !$this->searchView->getUseShadowBox()){
            $this->addHTML($this->searchView->getHTMLView());
            $this->addHTML('<div id="mls_list_re_mod">');
            $this->addHTML('</div>');
        }

        $this->addTypeSelector();
        
        $this->addTable();
        
        $this->closePaddingWrap();
    }
}
