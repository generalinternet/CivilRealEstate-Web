<?php
/**
 * Description of AbstractNoteIndexView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractNoteIndexView extends GI_View {

    /** @var Note[] */
    protected $notes;
    /** @var UITableView */
    protected $uiTableView;
    /** @var Note */
    protected $sampleNote;
    /** @var GI_SearchView */
    protected $searchView = NULL;

    public function __construct($notes, AbstractUITableView $uiTableView, AbstractNote $sampleNote, GI_SearchView $searchView = NULL) {
        parent::__construct();
        $this->notes = $notes;
        $this->uiTableView = $uiTableView;
        $this->sampleNote = $sampleNote;
        $this->searchView = $searchView;
        $this->addSiteTitle('Notes');
        $typeTitle = $this->sampleNote->getViewTitle();
        $this->addSiteTitle($typeTitle);
    }

    protected function openViewWrap(){
        $this->addHTML('<div class="content_padding">');
        return $this;
    }
    
    protected function closeViewWrap(){
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function addSearchBtn(){
        $title = $this->sampleNote->getViewTitle();
        
        $this->addHTML('<span title="Search ' . $title . '" class="custom_btn gray open_search_box" data-box="' . $this->searchView->getBoxId() . '" ><span class="icon_wrap"><span class="icon search"></span></span><span class="btn_text">Search</span></span>');
    }
    
    protected function addAddBtn(){
        if($this->sampleNote->isAddable()){
            $addTitle = $this->sampleNote->getViewTitle(false);
            $addURLArray = array(
                'controller' => 'note',
                'action' => 'add'
            );
            $typeRef = $this->sampleNote->getTypeRef();
            if(!empty($typeRef)){
                $addURLArray['type'] = $typeRef;
            }
            $addURL = GI_URLUtils::buildURL($addURLArray);
            $this->addHTML('<a href="' . $addURL . '" title="' . $addTitle . '" class="custom_btn" ><span class="icon_wrap"><span class="icon add"></span></span><span class="btn_text">' . $addTitle . '</span></a>');
        }
    }
    
    protected function addHeaderTitle($headerClass = ''){
        $typeTitle = $this->sampleNote->getViewTitle();
        $typeRef = $this->sampleNote->getTypeRef();
        if($typeRef != 'note'){
            $this->addHTML('<h2 class="main_head ' . $headerClass . '">Notes - ' . $typeTitle . '</h2>');
        } else {
            $this->addHTML('<h2 class="main_head  ' . $headerClass . '">Notes</h2>');
        }
    }
    
    protected function addTable(){
        if (count($this->notes) > 0) {
            $this->addHTML($this->uiTableView->getHTMLView());
        } else {
            $typeRef = $this->sampleNote->getTypeRef();
            $objTitle = 'notes';
            if($typeRef != 'note'){
                $objTitle = strtolower($this->sampleNote->getViewTitle(true));
            }
            $this->addHTML('<p>No ' . $objTitle . ' found.</p>');
        }
    }
    
    protected function buildView() {
        $this->openViewWrap();
        
        if($this->searchView){
            $this->addHTML($this->searchView->getHTMLView());
        }
        
        $this->addHTML('<div class="right_btns">');
            $this->addSearchBtn();
            $this->addAddBtn();
        $this->addHTML('</div>');
        
        $this->addHeaderTitle();
        
        $this->addTable();
        
        $this->closeViewWrap();
    }

    public function beforeReturningView() {
        $this->buildView();
    }

}
