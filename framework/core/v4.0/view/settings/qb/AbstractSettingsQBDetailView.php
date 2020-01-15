<?php

/**
 * Description of AbstractSettingsQBDetailView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    2.0.0
 */
abstract class AbstractSettingsQBDetailView extends AbstractSettingsDetailView {

    protected function buildViewHeader() {
        $this->addButtons();
        $this->addHTML('<h1>General</h1>');
    }

    protected function buildViewBody() {
        $this->addHTML('<hr />');
        $this->addInventoryDefaultsSection();
        $this->addHTML('<hr />');
        $this->addPOAcDefaultsSection();
        $this->addPODefaultTaxSection();
        $this->addHTML('<hr />');
        $this->addSOAcDefaultsSection();
    }

    protected function addInventoryDefaultsSection() {
        $updateToolURL = GI_URLUtils::buildURL(array(
            'controller'=>'accounting',
            'action'=>'updateMultiInvItemQBSettings',
        ));
        $this->addHTML('<div class="right_btns">');
        $this->addHTML('<a href="' . $updateToolURL . '" title="Update Existing Items" class="custom_btn" ><span class="icon_wrap"><span class="icon primary wrench"></span></span><span class="btn_text">Update Existing Items</span></a>');
        $this->addHTML('</div>');
        $this->addHTML('<h2>Inventory Item Defaults</h2>');
        $this->addHTML('<p>'.$this->settings->getDescription('inventory_main').'</p>');
        $this->addHeaderRow();
        $this->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
        $this->addHTML('<h4>Inventory Asset Account</h4>');
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $invAssetAccount = QBAccountFactory::getModelByQBId($this->settings->getProperty('settings_qb.inv_asset_acct_qb_id'));
        if (empty($invAssetAccount)) {
            $invAssetAccountName = 'Not Set';
        } else {
            $invAssetAccountName = $invAssetAccount->getNumberAndName();
        }
        $this->addHTML('<p>' . $invAssetAccountName . '</p>');
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addHTML('<p>'.$this->settings->getDescription('inv_asset').'</p>');
        $this->addHTML('</div>')
                ->addHTML('</div>');
        $this->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
        $this->addHTML('<h4>COGS Account</h4>');
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $cogsAccount = QBAccountFactory::getModelByQBId($this->settings->getProperty('settings_qb.inv_cogs_acct_qb_id'));
        if (empty($cogsAccount)) {
            $cogsAccountName = 'Not Set';
        } else {
            $cogsAccountName = $cogsAccount->getNumberAndName();
        }
        $this->addHTML('<p>' . $cogsAccountName . '</p>');
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addHTML('<p>'.$this->settings->getDescription('inv_cogs').'</p>');
        $this->addHTML('</div>')
                ->addHTML('</div>');
        $this->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
        $this->addHTML('<h4>Inventory Waste Account</h4>');
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $wasteAccount = QBAccountFactory::getModelByQBId($this->settings->getProperty('settings_qb.inv_wst_acct_qb_id'));
        if (empty($wasteAccount)) {
            $wasteAccountName = 'Not Set';
        } else {
            $wasteAccountName = $wasteAccount->getNumberAndName();
        }
        $this->addHTML('<p>' . $wasteAccountName . '</p>');
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addHTML('<p>'.$this->settings->getDescription('inv_waste').'</p>');
        $this->addHTML('</div>')
                ->addHTML('</div>');

        $this->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
        $this->addHTML('<h4>Sales Product/Service</h4>');
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $salesProduct = QBProductFactory::getModelByQBId($this->settings->getProperty('settings_qb.inv_sales_prod_qb_id'));
        if (empty($salesProduct)) {
            $salesProdName = 'Not Set';
        } else {
            $salesProdName = $salesProduct->getName();
        }
        
        $this->addHTML('<p>' . $salesProdName . '</p>');
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addHTML('<p>' . $this->settings->getDescription('inv_prod') . '</p>');
        $this->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addPOAcDefaultsSection() {
        $this->addHTML('<h2>Purchase Order Additional Costs - Default Accounts</h2>');
        $this->addHTML('<p>'.$this->settings->getDescription('po_ac_main').'</p>');
        $this->addHeaderRow();

        $this->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
        $this->addHTML('<h4>Shipping - Default COGS Account</h4>');
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $invAssetAccount = QBAccountFactory::getModelByQBId($this->settings->getProperty('settings_qb.ac_pur_ship_cogs_qb_id'));
        if (empty($invAssetAccount)) {
            $invAssetAccountName = 'Not Set';
        } else {
            $invAssetAccountName = $invAssetAccount->getNumberAndName();
        }
        $this->addHTML('<p>' . $invAssetAccountName . '</p>');
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addHTML('<p>' . $this->settings->getDescription('po_ship') . '</p>');
        $this->addHTML('</div>')
                ->addHTML('</div>');

        $this->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
        $this->addHTML('<h4>Other - Default COGS Account</h4>');
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $cogsAccount = QBAccountFactory::getModelByQBId($this->settings->getProperty('settings_qb.ac_pur_othr_cogs_qb_id'));
        if (empty($cogsAccount)) {
            $cogsAccountName = 'Not Set';
        } else {
            $cogsAccountName = $cogsAccount->getNumberAndName();
        }
        $this->addHTML('<p>' . $cogsAccountName . '</p>');
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addHTML('<p>' . $this->settings->getDescription('po_other') . '</p>');
        $this->addHTML('</div>')
                ->addHTML('</div>');
    }
    
    protected function addPODefaultTaxSection() {
        if (QBTaxCodeFactory::getTaxingUsesQBAst()) {
            $this->addPODefaultTaxASTSection();
        } else {
            $this->addPODefaultTaxNOAstSection();
        }
    }
    
    protected function addPODefaultTaxASTSection() {
        $this->addHTML('<hr />');
        $this->addHTML('<h2>Purchase Order Lines</h2>');
        $this->addHeaderRow();
        $this->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
        $this->addHTML('<h4>Taxable Status</h4>');
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $qbId = $this->settings->getProperty('settings_qb.ast_po_line_def_val');
        if (empty($qbId)) {
            $val = 'OFF';
        } else {
            $val = 'ON';
        }
        $this->addHTML('<p>' . $val . '</p>');
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addHTML('<p>' . $this->settings->getDescription('po_def_tax') . '</p>');
        $this->addHTML('</div>')
                ->addHTML('</div>');
    }
    
    protected function addPODefaultTaxNOAstSection() {
        //Not Implemented - region based
    }

    protected function addSOAcDefaultsSection() {
        $this->addHTML('<h2>Sales Order Additional Costs - Default Products/Services</h2>');
        $this->addHTML('<p>'.$this->settings->getDescription('so_ac_main').'</p>');
        $this->addHeaderRow();
        $this->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
        $this->addHTML('<h4>Shipping - Default Product/Service</h4>');
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $shippingProduct = QBProductFactory::getModelByQBId($this->settings->getProperty('settings_qb.ac_sal_ship_prod_qb_id'));
        if (empty($shippingProduct)) {
            $shippingProdName = 'Not Set';
        } else {
            $shippingProdName = $shippingProduct->getName();
        }
        $this->addHTML('<p>' . $shippingProdName . '</p>');
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addHTML('<p>' . $this->settings->getDescription('so_ship') . '</p>');
        $this->addHTML('</div>')
                ->addHTML('</div>');

        $this->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
        $this->addHTML('<h4>Other - Default Product/Service</h4>');
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $otherProduct = QBProductFactory::getModelByQBId($this->settings->getProperty('settings_qb.ac_sal_othr_prod_qb_id'));
        if (empty($otherProduct)) {
            $otherProdName = 'Not Set';
        } else {
            $otherProdName = $otherProduct->getName();
        }
        $this->addHTML('<p>' . $otherProdName . '</p>');
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addHTML('<p>' . $this->settings->getDescription('so_other') . '</p>');
        $this->addHTML('</div>')
                ->addHTML('</div>');
    }
    
    protected function addHeaderRow() {
        $this->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
        $this->addHTML('<h3>Setting</h3>');
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addHTML('<h3>Value</h3>');
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addHTML('<h3>Description</h3>');
        $this->addHTML('</div>')
                ->addHTML('</div>');
        $this->addHTML('<br />');
    }

    protected function addButtons() {
        $this->addHTML('<div class="right_btns">');
        if ($this->settings->isEditable()) {
            $this->addEditButton();
        }
        $this->addHTML('</div>');
    }
    
    protected function addEditButton() {
        $editURL = GI_URLUtils::buildURL(array(
            'controller'=>'accounting',
            'action'=>'editQBSettings'
        ));
        $this->addHTML('<a href="' . $editURL . '" title="Edit Settings" class="custom_btn" ><span class="icon_wrap"><span class="icon primary pencil"></span></span><span class="btn_text">Edit</span></a>');
    }
    
    protected function buildView() {
        $this->buildViewHeader();
        $this->buildViewBody();
        $this->buildViewFooter();
    }

}
