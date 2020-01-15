<?php
/**
 * Description of AbstractRoleGroupIndexView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractRoleGroupIndexView extends ListWindowView {
    
    /** @var AbstractRoleGroup[] */
    protected $models = array();
    /** @var AbstractRoleGroup */
    protected $sampleModel = NULL;

    public function __construct($models, AbstractUITableView $uiTableView, AbstractRoleGroup $sampleModel, GI_SearchView $searchView = NULL) {
        parent::__construct($models, $uiTableView, $sampleModel, $searchView);
        $this->addSiteTitle('Role Groups');
        $this->setWindowTitle($this->sampleModel->getViewTitle());
        $this->setWindowIcon('gear');
        $this->setAddListBtns(false);
    }
    
    protected function addAddBtn(){
        if($this->sampleModel->isAddable()){
            $addTitle = Lang::getString('add_role_group');
            $addURLArray = array(
                'controller' => 'role',
                'action' => 'addGroup'
            );
            $addURL = GI_URLUtils::buildURL($addURLArray);
            $this->addHTML('<a href="' . $addURL . '" title="' . $addTitle . '" class="custom_btn" >' . GI_StringUtils::getIcon('add') . '<span class="btn_text">' . $addTitle . '</span></a>');
        }
    }
    
    protected function addWindowBtns() {
        $this->addAddBtn();
    }

}
