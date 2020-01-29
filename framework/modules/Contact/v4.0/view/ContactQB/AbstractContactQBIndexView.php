<?php
/**
 * Description of AbstractContactQBIndexView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.0
 */
abstract class AbstractContactQBIndexView extends GI_View {
    
    /** @var AbstractContactQB[] */
    protected $contactQBs;
    protected $uiTableView;
    protected $sampleContactQB;
    protected $searchView = NULL;
    protected $tabbedView = false;

    public function __construct($contactQBs, AbstractUITableView $uiTableView, AbstractContactQB $sampleContactQB, GI_SearchView $searchView = NULL) {
        parent::__construct();
        $this->contactQBs = $contactQBs;
        $this->uiTableView = $uiTableView;
        $this->sampleContactQB = $sampleContactQB;
        $this->searchView = $searchView;
        $title = $this->sampleContactQB->getViewTitle();
        $this->addSiteTitle($title);
    }
    
    /**
     * @param boolean $tabbedView
     */
    public function setTabView($tabbedView = true) {
        $this->tabbedView = $tabbedView;
    }

    protected function openViewWrap() {
        if (!$this->tabbedView) {
            $this->addHTML('<div class="content_padding">');
        }
        return $this;
    }

    protected function closeViewWrap() {
        if (!$this->tabbedView) {
            $this->addHTML('</div>');
        }
        return $this;
    }
    
    protected function addSearchBtn(){
        $title = $this->sampleContactQB->getViewTitle();
        if($this->searchView){
            if ($this->searchView->getUseShadowBox()) {
                $searchURL = $this->searchView->getShadowBoxURL();
                $this->addHTML('<a href="' . $searchURL . '" title="Search ' . $title . '" class="custom_btn gray open_modal_form" data-modal-class="large_sized shadow_box_modal">' . GI_StringUtils::getIcon('search', true, 'white') . '<span class="btn_text">Search</span></a>');
            } else {
                $searchBtnClass = 'open';
                $queryId = $this->searchView->getQueryId();
                if(!empty($queryId)){
                    $searchBtnClass = '';
                }
                
                $this->addHTML('<span title="Search ' . $title . '" class="custom_btn gray open_search_box ' . $searchBtnClass . '" data-box="' . $this->searchView->getBoxId() . '" >' . GI_StringUtils::getIcon('search', true, 'white') . '<span class="btn_text">Search</span></span>');
            }
        }
    }
    
    protected function addImportButton() {
        $url = GI_URLUtils::buildURL(array(
            'controller'=>'contact',
            'action'=>'importQBContacts',
            'type'=>$this->sampleContactQB->getTypeRef(),
        ));
        $this->addHTML('<a href="' . $url . '" title="Import New" class="custom_btn open_modal_form">' . GI_StringUtils::getIcon('import') . '<span class="btn_text">Import New</span></a>');
    }
    
    protected function addBtns(){
        $rightBtnClass = 'absolute';
        $this->addHTML('<div class="right_btns ' . $rightBtnClass . '">');
        $this->addSearchBtn();
        $this->addImportButton();
        $this->addHTML('</div>');
    }
    
    protected function addHeaderTitle(){
        $title = $this->sampleContactQB->getViewTitle();
        if ($this->tabbedView) {
            $this->addHTML('<h2>' . $title . '</h2>');
        } else {
            $this->addMainTitle($title);
        }
    }

    protected function buildView() {
        $this->openViewWrap();

        if($this->searchView && !$this->searchView->getUseShadowBox()){
            $this->addHTML($this->searchView->getHTMLView());
        }
        
        $this->addBtns();
        
        $this->addHeaderTitle();
        
        if ($this->uiTableView) {
            $this->addHTML($this->uiTableView->getHTMLView());
        } else {
            $this->addHTML('<p class="no_model_message">No ' . strtolower($this->sampleContactQB->getViewTitle()) . ' found.</p>');
        }
        $this->closeViewWrap();
    }

    public function beforeReturningView() {
        $this->buildView();
    }

}
