<?php
/**
 * Description of AbstractUserIndexView
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    4.0.0
 */
abstract class AbstractUserIndexView extends ListWindowView {
    
    /** @var AbstractUser[] */
    protected $models = array();
    /** @var AbstractUser */
    protected $sampleModel = NULL;
    
    public function __construct($models, AbstractUITableView $uiTableView, AbstractUser $sampleModel, GI_SearchView $searchView = NULL) {
        parent::__construct($models, $uiTableView, $sampleModel, $searchView);
        $this->addSiteTitle('Users');
        $this->setWindowTitle('Users');
        $this->setWindowIcon('users');
        $this->setListItemTitle($sampleModel->getViewTitle());
    }
    
    protected function addWindowBtns() {
        $this->addAddBtn();
    }
    
    protected function addAddBtn(){
        if(Permission::verifyByRef('add_users')){
            $addURL = GI_URLUtils::buildURL(array(
                'controller' => 'user',
                'action' => 'add'
            ));
            $addTitle = Lang::getString('add_user');
            $this->addHTML('<a href="' . $addURL . '" title="' . $addTitle . '" class="custom_btn" >' . GI_StringUtils::getIcon('add') . '<span class="btn_text"></span></a>');
        }
    }
    
}
