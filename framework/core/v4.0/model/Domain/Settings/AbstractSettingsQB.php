<?php
/**
 * Description of AbstractSettingsQB
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    2.0.0
 */
abstract class AbstractSettingsQB extends AbstractSettings {

    public function getDetailView() {
        return new SettingsQBDetailView($this);
    }

    public function getFormView(GI_Form $form) {
        return new SettingsQBFormView($form, $this);
    }

    protected function getIsEditable() {
        if (Permission::verifyByRef('edit_qb_settings')) {
            return true;
        }
        return false;
    }

    public function setPropertiesFromForm(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            $invAssetAcctQBId = filter_input(INPUT_POST, 'inv_asset_acct_qb_id');
            $invCOGSAcctQBId = filter_input(INPUT_POST, 'inv_cogs_acct_qb_id');
            $invWstAcctQBId = filter_input(INPUT_POST, 'inv_wst_acct_qb_id');
            $invSalesProdQBId = filter_input(INPUT_POST, 'inv_sales_prod_qb_id');
            
            $acPurShipCOGSQBId = filter_input(INPUT_POST, 'ac_pur_ship_cogs_qb_id');
            $acPurOtherCOGSQBId = filter_input(INPUT_POST, 'ac_pur_othr_cogs_qb_id');
            $acSalesShipProdQBId = filter_input(INPUT_POST, 'ac_sal_ship_prod_qb_id');
            $acSalesOtherProdQBId = filter_input(INPUT_POST, 'ac_sal_othr_prod_qb_id');
            
            $poDefaultTaxCodeId = filter_input(INPUT_POST, 'po_default_tax_qb_id');
            if (empty($poDefaultTaxCodeId)) {
                $poDefaultTaxCodeId = NULL;
            }
            
            $this->setProperty('settings_qb.inv_asset_acct_qb_id', $invAssetAcctQBId);
            $this->setProperty('settings_qb.inv_cogs_acct_qb_id', $invCOGSAcctQBId);
            $this->setProperty('settings_qb.inv_wst_acct_qb_id', $invWstAcctQBId);
            $this->setProperty('settings_qb.inv_sales_prod_qb_id', $invSalesProdQBId);
            
            $this->setProperty('settings_qb.ac_pur_ship_cogs_qb_id', $acPurShipCOGSQBId);
            $this->setProperty('settings_qb.ac_pur_othr_cogs_qb_id', $acPurOtherCOGSQBId);
            $this->setProperty('settings_qb.ac_sal_ship_prod_qb_id', $acSalesShipProdQBId);
            $this->setProperty('settings_qb.ac_sal_othr_prod_qb_id', $acSalesOtherProdQBId);
            
            $this->setProperty('settings_qb.ast_po_line_def_val', $poDefaultTaxCodeId);
            return parent::setPropertiesFromForm($form);
        }
        return false;
    }

    public function getDescription($key = 'inv_asset') {
        switch ($key) {
            case 'general_main':
                return '';
            case 'inventory_main':
                return 'When a <b>new</b> Inventory Item is defined, these settings are used to pre-select the values of dropdown menus on the Accounting step of the form. Changing these values will not update existing Inventory Item definitions. Also, in cases where the corresponding settings have not been defined on a specific Inventory Item, the system will use these settings in their place.';
            case 'inv_asset':
                return "Pre-populates the 'QB Account' dropdown menu on Bill lines created from Purchase Order lines.";
            case 'inv_cogs':
                return "Pre-populates the 'Debit/Credit Account' dropdown menus when adjustments for Item Stock expenses are exported to Quickbooks.";
            case 'inv_waste':
                return "Pre-populates the 'Debit/Credit Account' dropdown menus when adjustments for Wasted/Damaged Stock expenses are exported to Quickbooks.";
            case 'inv_prod':
                return "Pre-populates the 'QB Product/Service' dropdown menu on Invoice line items created from Sales Order lines.";
            case 'po_ac_main':
                return "When adjustments stemming from additional cost expenses on Purchase Orders are exported to Quickbooks, these settings are used to pre-select the values of dropdown menus on the form.";
            case 'po_ship':
                return "Pre-populates the 'Debit/Credit Account' dropdown menus when adjustments for Shipping expenses are exported to Quickbooks.";
            case 'po_other':
                return "Pre-populates the 'Debit/Credit Account' dropdown menus when adjustments for Other expenses are exported to Quickbooks.";
            case 'po_def_tax':
                return 'Determines whether or not line items on a Purchase Order are pre-selected as taxable.';
            case 'so_ac_main':
                return 'These settings are used to pre-select the values of dropdown menus for each Invoice line item that is created from a Sales Order additional cost line.';
            case 'so_ship':
                return "Pre-populates the 'QB Product/Service' dropdown menu on Invoice line items created from Shipping Sales Order additional cost lines.";
            case 'so_other':
                return "Pre-populates the 'QB Product/Service' dropdown menu on Invoice line items created from Other Sales Order additional cost lines.";
            default:
                return '';
        }
    }
    
}
