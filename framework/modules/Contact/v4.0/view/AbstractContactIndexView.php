<?php
/**
 * Description of AbstractContactIndexView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.1
 */
abstract class AbstractContactIndexView extends ListWindowView {
    /** @var AbstractContact[] */
    protected $models = array();
    /** @var AbstractContact */
    protected $sampleModel = NULL;
    
    public function __construct($models, AbstractUITableView $uiTableView, AbstractContact $sampleModel, GI_SearchView $searchView = NULL) {
        parent::__construct($models, $uiTableView, $sampleModel, $searchView);
        $this->addSiteTitle('Contacts');
        $typeTitle = $this->sampleModel->getTypeTitle();
        $typeRef = $this->sampleModel->getTypeRef();
        $windowIcon = $this->sampleModel->getIcon();
        $this->setWindowTitle($this->sampleModel->getViewTitle());
        $this->setWindowIcon($windowIcon);
        $this->setListItemTitle($this->sampleModel->getViewTitle());
    }
    
    protected function addAddBtn(){
        $addURL = GI_URLUtils::buildURL(array(
            'controller'=>'contact',
            'action'=>'add',
            'type'=>$this->sampleModel->getTypeRef()
        ));
        $addTitle = $this->sampleModel->getViewTitle(false);
        
        if ($this->sampleModel->getAllowAddOnIndex()) {
            if($this->sampleModel->isAddable()){
                $this->addHTML('<a href="' . $addURL . '" title="' . $addTitle . '" class="custom_btn" >'.GI_StringUtils::getIcon('add').'<span class="btn_text">' . $addTitle . '</span></a>');
            }
        }
    }
    
    protected function addWindowBtns() {
        $this->addAddBtn();
    }
}
