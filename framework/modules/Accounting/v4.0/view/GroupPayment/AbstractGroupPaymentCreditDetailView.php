<?php
/**
 * Description of AbstractGroupPaymentCreditDetailView  
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractGroupPaymentCreditDetailView extends AbstractGroupPaymentDetailView {

    protected function addPaymentDate() {
        $paymentDate = $this->groupPayment->getProperty('date');
        $this->addContentBlock($paymentDate, 'Issue Date');
    }

    protected function addUploaderSection() {
//        if (!empty($this->uploader)) {
//            $this->addHTML($this->uploader->getHTMLView());
//        }
    }

    protected function addPrintButton() {
        if ($this->groupPayment->isPrintable()) {
            $printURL = GI_URLUtils::buildURL(array(
                        'controller' => 'accounting',
                        'action' => 'printCreditNote',
                        'id' => $this->groupPayment->getProperty('id')
            ));
            $this->addHTML('<a href="' . $printURL . '" class="custom_btn non_ajax_link" title="Print Credit Note"><span class="icon_wrap"><span class="icon primary print"></span></span><span class="btn_text">Print</span></a>');
        }
    }

    protected function addButtons() {
        $this->addHTML('<div class="right_btns ajax_link_wrap">');
        if ($this->groupPayment->getIsVoidable()) {
            $voidURL = $this->groupPayment->getVoidURL();
            $this->addHTML('<a href="' . $voidURL . '" title="Void Payment" class="custom_btn open_modal_form" ><span class="icon_wrap"><span class="icon primary void"></span></span><span class="btn_text">Void</span></a>');
        }
        if ($this->groupPayment->getIsEditable()) {
            $editURL = $this->groupPayment->getEditURL();
            $this->addHTML('<a href="' . $editURL . '" title="Edit Payment" class="custom_btn" ><span class="icon_wrap"><span class="icon primary pencil"></span></span><span class="btn_text">Edit</span></a>');
        }
        $this->addPrintButton();
        $this->addHTML('</div>');
    }

}
