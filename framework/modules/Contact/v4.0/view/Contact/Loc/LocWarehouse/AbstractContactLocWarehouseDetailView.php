<?php

/**
 * Description of AbstractContactLocWarehouseDetailView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.1.0
 */
abstract class AbstractContactLocWarehouseDetailView extends AbstractContactLocDetailView {
    
    protected function addButtonsSection() {
        $this->addHTML('<div class="right_btns">');
        $this->addInventoryBtn();
        $this->addDeleteBtn();
        $this->addEditBtn();
        $this->addHTML('</div>');
    }
    
    protected function addSidebarGeneralInfoBtns(){
        $this->addInventoryBtn();
        parent::addSidebarGeneralInfoBtns();
    }
    
    protected function getGeneralBtnOptions() {
        $btnOptionsArray = parent::getGeneralBtnOptions();
        $this->addInventoryBtnOptions($btnOptionsArray);
        return $btnOptionsArray;
    }
    
    protected function addInventoryBtnOptions(&$btnOptionsArray){
        if(dbConnection::isModuleInstalled('inventory')){
            $linkURL = GI_URLUtils::buildURL(array(
                'controller' => 'inventory',
                'action' => 'inventoryIndex',
                'locId' => $this->contact->getId()
            ));
            $linkType = 'edit';
            $linkTitle = 'Inventory Items';
            $linkIcon = 'box';
            $btnOptionsArray[] = array(
                'type' => $linkType,
                'title' => $linkTitle,
                'icon' => $linkIcon,
                'link' => $linkURL,
            );
        }
    }
    
    public function addInventoryBtn(){
        if(dbConnection::isModuleInstalled('inventory')){
            $locCarriesStockURL = GI_URLUtils::buildURL(array(
                'controller' => 'inventory',
                'action' => 'inventoryIndex',
                'locId' => $this->contact->getId()
            ));
//            $this->addHTML('<a href="' . $locCarriesStockURL . '" title="Edit Inventory Items" class="custom_btn"><span class="icon_wrap"><span class="icon box"></span></span><span class="btn_text">Inventory Items</span></a>');
            
            $linkURLText = 'Inventory Items';
            $linkIcon = 'box';
            $btnText = $this->getSidebarMenuTextWithSVGIcon($linkIcon, $linkURLText);
            $this->addHTML('<a href="' . $locCarriesStockURL . '" class="sidbar_btn" title="'.$linkURLText.'">'.$btnText.'</a>');
        }
    }
    
}
