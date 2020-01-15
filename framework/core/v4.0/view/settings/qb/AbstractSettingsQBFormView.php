<?php
/**
 * Description of AbstractSettingsQBFormView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    2.0.0
 */
abstract class AbstractSettingsQBFormView extends AbstractSettingsFormView {

    protected function buildFormHeader() {
        $this->form->addHTML('<h1>Quickbooks Settings - General</h1>');
    }

    protected function buildFormBody() {
        $this->form->addHTML('<hr />');
        $this->addInventoryDefaultsSection();
        $this->addPODefaultsSection();
        $this->addSODefaultsSection();
    }

    protected function addInventoryDefaultsSection() {
        $this->form->addHTML('<h2>Inventory Item Defaults</h2>');
        $this->form->addHTML('<p>'.$this->settings->getDescription('inventory_main').'</p>');
        $this->addHeaderRow();
        $this->addInvAssetSection();
        $this->addInvCOGSSection();
        $this->addInvWasteSection();
        $this->addInvProdSection();
        $this->form->addHTML('<hr />');
    }

    protected function addInvAssetSection() {
        $this->form->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
        $this->form->addHTML('<h4>Inventory Asset Account</h4>');
        $this->form->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addInvAssetField();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->form->addHTML('<p>' . $this->settings->getDescription('inv_asset') . '</p>');
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addInvAssetField() {
        $tempModel = InvItemQBDefaultFactory::buildNewModel('inv_asset');
        if (!empty($tempModel)) {
            $value = $this->settings->getProperty('settings_qb.inv_asset_acct_qb_id');
            $accountTypes = $tempModel->getQBAccountTypes();
            $options = QBAccountFactory::getAccountOptionsArray($accountTypes, $value);
            $this->form->addField('inv_asset_acct_qb_id', 'dropdown', array(
                'value' => $value,
                'options' => $options,
                'displayName' => '',
                'hideNull' => true,
            ));
        }
    }

    protected function addInvCOGSSection() {
        $this->form->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
        $this->form->addHTML('<h4>COGS Account</h4>');
        $this->form->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addInvCOGSField();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->form->addHTML('<p>' . $this->settings->getDescription('inv_cogs') . '</p>');
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addInvCOGSField() {
        $tempModel = InvItemQBDefaultFactory::buildNewModel('cogs');
        if (!empty($tempModel)) {
            $value = $this->settings->getProperty('settings_qb.inv_cogs_acct_qb_id');
            $accountTypes = $tempModel->getQBAccountTypes();
            $options = QBAccountFactory::getAccountOptionsArray($accountTypes, $value);
            $this->form->addField('inv_cogs_acct_qb_id', 'dropdown', array(
                'value' => $value,
                'options' => $options,
                'displayName' => '',
                'hideNull' => true,
            ));
        }
    }

    protected function addInvWasteSection() {
        $this->form->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
        $this->form->addHTML('<h4>Inventory Waste Account</h4>');
        $this->form->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addInvWasteField();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->form->addHTML('<p>' . $this->settings->getDescription('inv_waste') . '</p>');
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addInvWasteField() {
        $tempModel = InvItemQBDefaultFactory::buildNewModel('waste');
        if (!empty($tempModel)) {
            $value = $this->settings->getProperty('settings_qb.inv_wst_acct_qb_id');
            $accountTypes = $tempModel->getQBAccountTypes();
            $options = QBAccountFactory::getAccountOptionsArray($accountTypes, $value);
            $this->form->addField('inv_wst_acct_qb_id', 'dropdown', array(
                'value' => $value,
                'options' => $options,
                'displayName' => '',
                'hideNull' => true,
            ));
        }
    }

    protected function addInvProdSection() {
        $this->form->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
        $this->form->addHTML('<h4>Sales Product/Service</h4>');
        $this->form->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addInvProdField();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->form->addHTML('<p>' . $this->settings->getDescription('inv_prod') . '</p>');
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addInvProdField() {
        $tempModel = InvItemQBDefaultFactory::buildNewModel('sales');
        if (!empty($tempModel)) {
            $value = $this->settings->getProperty('settings_qb.inv_sales_prod_qb_id');
            $options = QBProductFactory::getProductOptionsArray($value);
            $this->form->addField('inv_sales_prod_qb_id', 'dropdown', array(
                'value' => $value,
                'options' => $options,
                'displayName' => '',
                'hideNull' => true,
            ));
        }
    }
    
    protected function addPODefaultsSection() {
        $this->form->addHTML('<h2>Purchase Order Additional Costs</h2>');
        $this->form->addHTML('<p>'.$this->settings->getDescription('po_ac_main').'</p>');
        $this->addHeaderRow();
        $this->addPOAcShippingSection();
        $this->addPOAcOtherSection();
        $this->form->addHTML('<hr />');
        if (QBTaxCodeFactory::getTaxingUsesQBAst()) {
            $this->form->addHTML('<h2>Purchase Order Lines</h2>');
            $this->addPODefaultTaxSection();
            $this->form->addHTML('<hr />');
        }
    }

    protected function addPOAcShippingSection() {
        $this->form->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
        $this->form->addHTML('<h4>Shipping - Default COGS Account</h4>');
        $this->form->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addPOAcShippingField();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->form->addHTML('<p>' . $this->settings->getDescription('po_ship') . '</p>');
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addPOAcShippingField() {
        $tempModel = InvItemQBDefaultFactory::buildNewModel('cogs');
        if (!empty($tempModel)) {
            $value = $this->settings->getProperty('settings_qb.ac_pur_ship_cogs_qb_id');
            $accountTypes = $tempModel->getQBAccountTypes();
            $options = QBAccountFactory::getAccountOptionsArray($accountTypes, $value);
            $this->form->addField('ac_pur_ship_cogs_qb_id', 'dropdown', array(
                'value' => $value,
                'options' => $options,
                'displayName' => '',
                'hideNull' => true,
            ));
        }
    }

    protected function addPOAcOtherSection() {
        $this->form->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
        $this->form->addHTML('<h4>Other - Default COGS Account</h4>');
        $this->form->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addPOAcOtherField();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->form->addHTML('<p>' . $this->settings->getDescription('po_other') . '</p>');
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addPOAcOtherField() {
        $tempModel = InvItemQBDefaultFactory::buildNewModel('cogs');
        if (!empty($tempModel)) {
            $value = $this->settings->getProperty('settings_qb.ac_pur_othr_cogs_qb_id');
            $accountTypes = $tempModel->getQBAccountTypes();
            $options = QBAccountFactory::getAccountOptionsArray($accountTypes, $value);
            $this->form->addField('ac_pur_othr_cogs_qb_id', 'dropdown', array(
                'value' => $value,
                'options' => $options,
                'displayName' => '',
                'hideNull' => true,
            ));
        }
    }
    
    protected function addPODefaultTaxSection() {
                $this->form->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
        $this->form->addHTML('<h4>Taxable Status</h4>');
        $this->form->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addPODefaultTaxField();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->form->addHTML('<p>' . $this->settings->getDescription('po_def_tax') . '</p>');
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }
    
    protected function addPODefaultTaxField() {
        if (QBTaxCodeFactory::getTaxingUsesQBAst()) {
            $this->addPODefaultTaxASTField();
        } else {
            $this->addPODefaultTaxNotASTField();
        }
    }
    
    protected function addPODefaultTaxASTField() {
        $this->form->addField('po_default_tax_qb_id', 'onoff', array(
            'value'=>$this->settings->getProperty('settings_qb.ast_po_line_def_val'),
            'displayName' => 'Tax?',
        ));
    }
    
    protected function addPODefaultTaxNotASTField() {
        $this->form->addField('po_default_tax_qb_id', 'hidden', array(
            'value'=>NULL,
        )); 
    }

    protected function addSODefaultsSection() {
        $this->form->addHTML('<h2>Sales Order Additional Costs</h2>');
        $this->form->addHTML('<p>'.$this->settings->getDescription('so_ac_main').'</p>');
        $this->addHeaderRow();
        $this->addSOAcShippingSection();
        $this->addSOAcOtherSection();
    }

    protected function addSOAcShippingSection() {
        $this->form->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
        $this->form->addHTML('<h4>Shipping - Default Product/Service</h4>');
        $this->form->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addSOAcShippingField();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->form->addHTML('<p>' . $this->settings->getDescription('so_ship') . '</p>');
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addSOAcShippingField() {
        $tempModel = InvItemQBDefaultFactory::buildNewModel('sales');
        if (!empty($tempModel)) {
            $value = $this->settings->getProperty('settings_qb.ac_sal_ship_prod_qb_id');
            $options = QBProductFactory::getProductOptionsArray($value);
            $this->form->addField('ac_sal_ship_prod_qb_id', 'dropdown', array(
                'value' => $value,
                'options' => $options,
                'displayName' => '',
                'hideNull' => true,
            ));
        }
    }

    protected function addSOAcOtherSection() {
        $this->form->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
        $this->form->addHTML('<h4>Other - Default Product/Service</h4>');
        $this->form->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addSOAcOtherField();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->form->addHTML('<p>' . $this->settings->getDescription('so_other') . '</p>');
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addSOAcOtherField() {
        $tempModel = InvItemQBDefaultFactory::buildNewModel('sales');
        if (!empty($tempModel)) {
            $value = $this->settings->getProperty('settings_qb.ac_sal_othr_prod_qb_id');
            $options = QBProductFactory::getProductOptionsArray($value);
            $this->form->addField('ac_sal_othr_prod_qb_id', 'dropdown', array(
                'value' => $value,
                'options' => $options,
                'displayName' => '',
                'hideNull' => true,
            ));
        }
    }

    protected function addHeaderRow() {
        $this->form->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
        $this->form->addHTML('<h3>Setting</h3>');
        $this->form->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->form->addHTML('<h3>Value</h3>');
        $this->form->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->form->addHTML('<h3>Description</h3>');
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
        $this->form->addHTML('<br />');
    }

}
