<?php
/**
 * Description of AbstractContactProfileIndexView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.1.0
 */
abstract class AbstractContactProfileIndexView extends ListWindowView {
    
    /** @var AbstractContact[] */
    protected $models = array();
    /** @var AbstractContactCat */
    protected $sampleModel = NULL;
    protected $catType = 'client'; //Default catType

    public function __construct($models, AbstractUITableView $uiTableView, AbstractContactCat $sampleModel, GI_SearchView $searchView = NULL) {
        parent::__construct($models, $uiTableView, $sampleModel, $searchView);
        $this->addSiteTitle('Contacts');
        $typeTitle = $this->sampleModel->getTypeTitle();
        $viewTitle = $this->sampleModel->getViewTitle();
        $typeRef = $this->sampleModel->getTypeRef();
        $windowIcon = 'contacts';
        if (!empty($typeRef) && $typeTitle != 'Category') {
            $this->addSiteTitle($viewTitle);
            $this->catType = $typeRef;
            $windowIcon = $typeRef;
        }

        $this->setWindowTitle($viewTitle);
        $this->setWindowIcon($windowIcon);
        $this->setListItemTitle($viewTitle);
        
        //Add class to the right btn in order to manage style for smaller screens
        if ($this->sampleModel->isAddable()) {
            $this->setViewHeaderClass('multiple_header_btns');
        }
    }
    
    public function setListTitle($listTitle){
        $this->setWindowTitle($listTitle);
        return $this;
    }
    
    protected function addAddIndBtn(){
        if ($this->sampleModel->isAddable()) {
            $addIndURL = GI_URLUtils::buildURL(array(
                'controller'=>'contact',
                'action'=>'add',
                'type'=>'ind',
                'catType'=>$this->catType,
            ));
            $this->addHTML('<a href="' . $addIndURL . '" title="Add Individuals" class="custom_btn">'.GI_StringUtils::getIcon('add').'<span class="btn_text">Individual</span></a>');
        }
    }
    
    protected function addAddBtn(){
        if ($this->sampleModel->isAddable()) {
            $addOrgURL = GI_URLUtils::buildURL(array(
                'controller'=>'contactprofile',
                'action'=>'add',
                'type'=>$this->catType,
            ));
            $title = $this->sampleModel->getViewTitle(false);
            $this->addHTML('<a href="' . $addOrgURL . '" title="Add '.$title.'" class="custom_btn">'.GI_StringUtils::getIcon('add').'</a>');
        }

    }
    
    
    
    protected function addWindowBtns() {
        $this->addAddBtn();
    }
    
    protected function addTypeSelector() {

    }
}
