<?php
/**
 * Description of AbstractPricingRegionIndexView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractPricingRegionIndexView extends ListWindowView {
    
    /** @var AbstractPricingRegion[] */
    protected $models = array();
    /** @var AbstractPricingRegion */
    protected $sampleModel = NULL;
    
    public function __construct($models, AbstractUITableView $uiTableView, AbstractPricingRegion $sampleModel, GI_SearchView $searchView = NULL) {
        parent::__construct($models, $uiTableView, $sampleModel, $searchView);
        $this->addSiteTitle('Pricing Regions');
        $this->setWindowTitle('Pricing Regions');
        $this->setWindowIcon('gear');
        $this->setListItemTitle($sampleModel->getViewTitle());
        $this->setAddListBtns(false);
    }
    
    protected function addAddBtn(){
        if (Permission::verifyByRef('add_pricing_regions')) {
            $addURL = GI_URLUtils::buildURL(array(
                'controller'=>'admin',
                'action'=>'addPricingRegion'
            ));
            $this->addHTML('<a href="' . $addURL . '" title="add pricing region" class="custom_btn" ><span class="icon_wrap"><span class="icon primary add"></span></span><span class="btn_text">Add Pricing Region</span></a>');
        }
    }
    
    protected function addWindowBtns() {
        $this->addAddBtn();
    }
    
}