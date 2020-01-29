<?php
/**
 * Description of AbstractContactQBCustomerDetailView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.0
 */
abstract class AbstractContactQBCustomerDetailView extends AbstractContactQBDetailView {

    protected function buildLeftBodyColumn() {
        $this->addHTML('<div class="bg_section_box left_box">');
        $this->addIndividualName();
        $this->addCompanyNames();
        $this->addDisplayName();
        $this->addBillingAddress();
        $this->addShippingAddress();
        $this->addHTML('</div>');
    }

    protected function buildRightBodyColumn() {
        $this->addHTML('<div class="bg_section_box right_box">');
        $this->addEmailAddress();
        $this->addPhoneNumbers();
        $this->addCurrency();
        $this->addDefaultTaxCode();
        $this->addOutstandingBalance();
        $this->addHTML('</div>');
    }

    protected function addShippingAddress() {
        $shippingAddress = $this->contactQB->getShippingAddress();
        if (!empty($shippingAddress)) {
            $this->addHTML('<div class="h_content_wrap">');
            $this->addContentBlock($shippingAddress, 'Shipping Address');
            $this->addHTML('</div>');
        }
    }

    protected function addOutstandingBalance() {
        $balance = $this->contactQB->getProperty('contact_qb_customer.balance');
        if (!empty($balance)) {
            $this->addHTML('<div class="h_content_wrap">');
            $this->addContentBlock('$'. GI_StringUtils::formatMoney($balance), 'Balance Outstanding');
            $this->addHTML('</div>');
        }
    }

    protected function addDefaultTaxCode() {
        $defaultTaxCodeQBId = $this->contactQB->getProperty('contact_qb_customer.default_tax_code_qb_id');
        if (!empty($defaultTaxCodeQBId)) {
            $optionsArray = QBTaxCodeFactory::getOptionsArray();
            if (!empty($optionsArray) && isset($optionsArray[$defaultTaxCodeQBId])) {
                $this->addHTML('<div class="h_content_wrap">');
                $this->addContentBlock($optionsArray[$defaultTaxCodeQBId], 'Default Tax Code');
                $this->addHTML('</div>');
            }
        }
    }

}
