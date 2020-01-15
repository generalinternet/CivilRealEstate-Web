<?php
/**
 * Description of AbstractPermissionIndexView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractPermissionIndexView extends ListWindowView {

    /** @var AbstractPermission[] */
    protected $models = array();
    /** @var AbstractPermission */
    protected $sampleModel = NULL;
    
    public function __construct($models, AbstractUITableView $uiTableView, AbstractPermission $sampleModel, GI_SearchView $searchView = NULL) {
        parent::__construct($models, $uiTableView, $sampleModel, $searchView);
        $this->addSiteTitle('Permissions');
        $this->setWindowTitle('Permissions');
        $this->setWindowIcon('gear');
        $this->setListItemTitle($sampleModel->getViewTitle());
    }
    
    protected function addAddBtn(){
        if(Permission::verifyByRef('add_permissions')){
            $addURL = GI_URLUtils::buildURL(array(
                'controller' => 'permission',
                'action' => 'add'
            ));
            $this->addHTML('<a href="' . $addURL . '" title="Add Permission" class="custom_btn" >' . GI_StringUtils::getIcon('add') . '<span class="btn_text"></span></a>');
        }
    }
    
    protected function addWindowBtns() {
        $this->addAddBtn();
    }

}
