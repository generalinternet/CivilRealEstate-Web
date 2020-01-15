<?php
/**
 * Description of AbstractListWindowView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractListWindowView extends WindowView {
    
    protected $listBarClass = 'loaded';
    protected $addListBtns = true;
    protected $listBtnsClass = '';
    protected $viewWrapId = 'list_window_view_wrap';
    /** @var GI_SearchView */
    protected $searchView = NULL;
    protected $listItemTitle = NULL;
    /** @var GI_Model[] */
    protected $models = array();
    /** @var GI_Model */
    protected $sampleModel = NULL;
    /** @var AbstractUITableView */
    protected $uiTableView = NULL;
    
    public function __construct($models, AbstractUITableView $uiTableView, GI_Model $sampleModel, GI_SearchView $searchView = NULL) {
        parent::__construct();
        $this->setListBarURL(GI_URLUtils::buildURL(GI_URLUtils::getAttributes()));
        $this->setModels($models);
        $this->setUITableView($uiTableView);
        $this->setSampleModel($sampleModel);
        if ($searchView) {
            $this->setSearchView($searchView);
        }
        if(!is_a($uiTableView, 'AbstractUIRolodexView')){
            //Not in the list_bar 
            $this->addOuterWrap = false;
        }
    }
    
    public function getUITableView() {
        return $this->uiTableView;
    }
    
    public function getModels(){
        return $this->models;
    }
    
    public function getSearchView() {
        return $this->searchView;
    }

    public function getSampleModel() {
        return $this->sampleModel;
    }
    
    public function setAddListBtns($addListBtns){
        $this->addListBtns = $addListBtns;
        return $this;
    }
    
    public function setModels($models){
        $this->models = $models;
        return $this;
    }

    public function setSearchView(GI_SearchView $searchView) {
        $this->searchView = $searchView;
        return $this;
    }

    public function setSampleModel(GI_Model $sampleModel) {
        $this->sampleModel = $sampleModel;
        return $this;
    }

    public function setUITableView(AbstractUITableView $uiTableView) {
        $this->uiTableView = $uiTableView;
        return $this;
    }

    protected function openOuterWrap(){
        if(!$this->addOuterWrap){
            return $this;
        }
        $this->addHTML('<div id="list_content">');
        return $this;
    }
    
    protected function closeOuterWrap(){
        if(!$this->addOuterWrap){
            return $this;
        }
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function buildViewHeader(){
        parent::buildViewHeader();
        $this->buildListBtns();
        return $this;
    }
    
    protected function openListBtnsWrap(){
        if(!$this->addListBtns){
            return $this;
        }
        $this->addHTML('<div class="list_btns ' . $this->getListBtnsClass() . '">');
        return $this;
    }

    protected function closeListBtnsWrap(){
        if(!$this->addListBtns){
            return $this;
        }
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function getListBtnsClass(){
        return $this->listBtnsClass;
    }
    
    protected function buildListBtns(){
        if(!$this->addListBtns || !$this->addOuterWrap){
            return $this;
        }
        $this->openListBtnsWrap();
            $this->addListBtns();
        $this->closeListBtnsWrap();
        return $this;
    }
    
    protected function addListBtns(){
        $this->addSearchBtn();
        return $this;
    }
    
    protected function getListItemTitle(){
        return $this->listItemTitle;
    }
    
    protected function setListItemTitle($listItemTitle){
        $this->listItemTitle = $listItemTitle;
        return $this;
    }
    
    protected function addSearchBtn(){
        $title = $this->getListItemTitle();
        $searchTerm = Lang::getString('search');
        if($this->searchView){
            if ($this->searchView->getUseShadowBox()) {
                $searchURL = $this->searchView->getShadowBoxURL();
                $this->addHTML('<a href="' . $searchURL . '" title="' . $searchTerm . ' ' . $title . '" class="custom_btn gray open_modal_form" data-modal-class="large_sized shadow_box_modal">' . GI_StringUtils::getIcon('search', true, 'gray') . '<span class="btn_text">' . $searchTerm . '</span></a>');
            } else {
                $this->addHTML('<span title="' . $searchTerm . ' ' . $title . '" class="custom_btn gray open_search_box" data-box="' . $this->searchView->getBoxId() . '" >' . GI_StringUtils::getIcon('search', true, 'gray') . '<span class="btn_text">' . $searchTerm . '</span></span>');
            }
        }
        return $this;
    }
    
    protected function addTypeSelector(){
        return $this;
    }
    
    protected function addTable(){
        $this->openTableWrap();
            $this->addHTML($this->uiTableView->getHTMLView());
        $this->closeTableWrap();
        return $this;
    }
    
    protected function openTableWrap($class = ''){
        if(!$this->addOuterWrap){
            return $this;
        }
        $this->addHTML('<div id="list_table_wrap" class="'.$class.'">');
        return $this;
    }
    
    protected function closeTableWrap(){
        if(!$this->addOuterWrap){
            return $this;
        }
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function addViewBodyContent() {
        $this->openPaddingWrap();

        if($this->searchView && !$this->searchView->getUseShadowBox()){
            $this->addHTML($this->searchView->getHTMLView());
        }

        $this->addTypeSelector();
        
        $this->addTable();
        
        $this->closePaddingWrap();
    }
    
}
