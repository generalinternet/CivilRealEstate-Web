<?php

/**
 * Description of AbstractGroupPaymentImportedIndexView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractGroupPaymentImportedIndexView extends AbstractGroupPaymentIndexView {

    protected function addViewHeader() {
        $this->addButtonsAndSearchView();
        $this->addHTML('<h1>Imported Payments</h1>');
    }

    protected function addButtons($searchBtnClass = 'open') {
        $this->addHTML('<div class="right_btns">');
        $importURL = GI_URLUtils::buildURL(array(
                    'controller' => 'import',
                    'action' => 'importPayments',
                    'type' => $this->samplePayment->getTypeRef(),
        ));
        $this->addHTML('<a href="' . $importURL . '" title="Import Payments" class="custom_btn open_modal_form" ><span class="icon_wrap"><span class="icon upload"></span></span><span class="btn_text">Import Payments</span></a>');
        if ($this->searchView) {
            $this->addHTML('<span title="Search Payments" class="custom_btn gray open_search_box ' . $searchBtnClass . '" data-box="' . $this->searchView->getBoxId() . '" ><span class="icon_wrap"><span class="icon search"></span></span><span class="btn_text">Search</span></span>');
        }
        $this->addHTML('</div>');
    }

}
