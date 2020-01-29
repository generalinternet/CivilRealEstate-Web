<?php
/**
 * Description of AbstractContactQBDetailView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.0
 */

abstract class AbstractContactQBDetailView extends GI_View {
    
    protected $contactQB;
    
    public function __construct(AbstractContactQB $contactQB) {
        parent::__construct();
        $this->contactQB = $contactQB;
    }
    
    protected function buildView() {
        $this->openViewWrap();
        $this->buildViewHeader();
        $this->buildViewBody();
        $this->buildViewFooter();
        $this->closeViewWrap();
    }
    
    protected function openViewWrap() {
        $this->addHeaderIcons();
        $this->addHTML('<div id="qb_info_content">');
    }
    
    protected function closeViewWrap() {
        $this->addHTML('</div>');
    }
    
    protected function addHeaderIcons() {
        $this->addHTML('<span id="qb_info_section_expand" class="icon_wrap"><span class="icon arrow_down border"></span></span>');
        $this->addHTML('<span id="qb_info_section_close" class="icon_wrap circle close_qb_section"><span class="icon eks"></span></span>');
    }
    
    protected function buildViewHeader() {
        $this->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
        $this->addHTML('<h1 class="main_title">');
        $this->addHTML($this->contactQB->getViewTitle(false) . ' Information');
        $this->addHTML('</h1>');
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
      //  $this->addImportExportDates();
        $this->addHTML('</div>')
                ->addHTML('</div>');
        $this->addHTML('<br />');
    }

    protected function buildViewBody($classNames = '') {
        $this->addHTML('<div class="flex_row '.$classNames.'">')
                    ->addHTML('<div class="flex_col">');
        $this->buildLeftBodyColumn();
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->buildRightBodyColumn();
        $this->addHTML('</div>')
                ->addHTML('</div>');
    }
    
    protected function buildLeftBodyColumn() {
        $this->addHTML('<div class="bg_section_box left_box">');
        $this->addIndividualName();
        $this->addCompanyNames();
        $this->addDisplayName();
        $this->addBillingAddress();
        $this->addHTML('</div>');
    }
    
    protected function addIndividualName() {
        $name = $this->contactQB->getIndividualName();
        if (empty($name)) {
            $name = '--';
        }
        $this->addHTML('<div class="h_content_wrap">');
        $this->addContentBlock($name, 'Name');
        $this->addHTML('</div>');
    }
    
    protected function addCompanyNames() {
        $this->addCompanyName();
     //   $this->addPrintOnChequeName();
    }

    protected function addCompanyName() {
        $company = $this->contactQB->getProperty('company');
        if (empty($company)) {
            $company = '--';
        }
        $this->addHTML('<div class="h_content_wrap">');
        $this->addContentBlock($company, 'Company');
        $this->addHTML('</div>');
    }

    protected function addDisplayName() {
        $displayName = $this->contactQB->getProperty('display_name');
        if (empty($displayName)) {
            $displayName = '--';
        }
        $this->addHTML('<div class="h_content_wrap">');
        $this->addContentBlock($displayName, 'Display Name As');
        $this->addHTML('</div>');
    }

    protected function addDBAName() {
        $dbaName = $this->contactQB->getProperty('company_dba');
        if (!empty($dbaName)) {
            $this->addHTML('<div class="h_content_wrap">');
            $this->addContentBlock($dbaName, 'Doing Business As');
            $this->addHTML('</div>');
        }
    }
    
    protected function addPrintOnChequeName() {
        $printOnChequeName = $this->contactQB->getProperty('print_on_cheque_name');
        if (!empty($printOnChequeName)) {
            $this->addHTML('<div class="h_content_wrap">');
            $this->addContentBlock($printOnChequeName, 'Print on Cheque as');
            $this->addHTML('</div>');
        }
    }

    protected function addBillingAddress() {
        $billingAddress = $this->contactQB->getBillingAddress();
        if (!empty($billingAddress)) {
            $this->addHTML('<div class="h_content_wrap">');
            $this->addContentBlock($billingAddress, 'Billing Address');
            $this->addHTML('</div>');
        }
    }
    
    protected function buildRightBodyColumn() {
        $this->addHTML('<div class="bg_section_box right_box">');
        $this->addEmailAddress();
        $this->addPhoneNumbers();
        $this->addCurrency();
        $this->addHTML('</div>');
    }
    
    protected function addEmailAddress() {
        $emailAddress = $this->contactQB->getProperty('email');
        if (!empty($emailAddress)) {
            $this->addHTML('<div class="h_content_wrap">');
            $this->addContentBlock($emailAddress, 'Email');
            $this->addHTML('</div>');
        }
    }
    
    protected function addPhoneNumbers() {
        $this->addPrimaryPhoneNumber();
        $this->addMobilePhoneNumber();
        $this->addFaxNumber();
        $this->addOtherPhoneNumber();
    }
    
    protected function addPrimaryPhoneNumber() {
        $primaryPhone = $this->contactQB->getProperty('primary_phone');
        if (!empty($primaryPhone)) {
            $this->addHTML('<div class="h_content_wrap">');
            $this->addContentBlock($primaryPhone, 'Phone');
            $this->addHTML('</div>');
        }
    }
    
    protected function addMobilePhoneNumber() {
        $mobileNumber = $this->contactQB->getProperty('mobile');
        if (!empty($mobileNumber)) {
            $this->addHTML('<div class="h_content_wrap">');
            $this->addContentBlock($mobileNumber, 'Mobile');
            $this->addHTML('</div>');
        }
    }
    
    protected function addFaxNumber() {
        $fax = $this->contactQB->getProperty('fax');
        if (!empty($fax)) {
            $this->addHTML('<div class="h_content_wrap">');
            $this->addContentBlock($fax, 'Fax');
            $this->addHTML('</div>');
        }
    }
    
    protected function addOtherPhoneNumber() {
        $otherPhone = $this->contactQB->getProperty('alternate_phone');
        if (!empty($otherPhone)) {
            $this->addHTML('<div class="h_content_wrap">');
            $this->addContentBlock($otherPhone, 'Other');
            $this->addHTML('</div>');
        }
    }

    protected function addImportExportDates() {
        $importText = '';
        $importDate = $this->contactQB->getProperty('qb_import_date');
        if (!empty($importDate)) {
            $importText .= GI_Time::formatDateForDisplay($importDate);
        }
        if (!empty($this->contactQB->getProperty('import_required'))) {
            $importText .= ' <strong>** Import Required</strong>';
        }
        if (empty($importText)) {
            $importText = '--';
        }
        $exportText = '';
        $exportDate = $this->contactQB->getProperty('qb_export_date');
        if (!empty($exportDate)) {
            $exportText .= GI_Time::formatDateForDisplay($exportDate);
        }
        if (!empty($this->contactQB->getProperty('export_required'))) {
            $exportText .= ' <strong>** Export Required</strong>';
        }
        if (empty($exportText)) {
            $exportText = '--';
        }
        $this->addHTML('<div class="bg_section_box left_box">');
        $this->addHTML('<div class="h_content_wrap">');
        $this->addContentBlock($importText, 'Import Date');
        $this->addContentBlock($exportText, 'Export Date');
        $this->addHTML('</div>');
        $this->addHTML('</div>');
    }

    protected function addCurrency() {
        $currencyRef = $this->contactQB->getProperty('currency_ref');
        if (!empty($currencyRef)) {
            $this->addHTML('<div class="h_content_wrap">');
            $this->addContentBlock($currencyRef, 'Currency');
            $this->addHTML('</div>');
        }
    }

    protected function buildViewFooter() {
        
    }

    public function beforeReturningView() {
        $this->buildView();
    }
    
}