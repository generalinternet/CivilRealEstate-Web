<?php

/**
 * Description of AbstractTagIndexView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractTagIndexView extends ListWindowView {

    /** @var AbstractTag[] */
    protected $models;
    /** @var AbstractTag */
    protected $sampleModel;

    public function __construct($models, AbstractUITableView $uiTableView, AbstractTag $sampleModel, GI_SearchView $searchView = NULL) {
        parent::__construct($models, $uiTableView, $sampleModel, $searchView);
        $this->addSiteTitle($sampleModel->getViewTitle());
        $this->setWindowTitle($this->sampleModel->getViewTitle());
        $this->setWindowIcon('gear');
        $this->setListItemTitle($this->sampleModel->getViewTitle());
    }
    
    protected function addAddBtn(){
        if ($this->sampleModel->isAddable()) {
            $addTitle = $this->sampleModel->getViewTitle(false);
            $addURLArray = array(
                'controller' => 'tag',
                'action' => 'add',
                'type'=> $this->sampleModel->getTypeRef(),
                'refresh'=>1,
            );
            $addURL = GI_URLUtils::buildURL($addURLArray);
            $this->addHTML('<a href="' . $addURL . '" title="' . $addTitle . '" class="custom_btn open_modal_form" ><span class="icon_wrap"><span class="icon primary add"></span></span><span class="btn_text">' . $addTitle . '</span></a>');
        }
    }
    
    protected function addWindowBtns() {
        $this->addAddBtn();
    }
    
}
