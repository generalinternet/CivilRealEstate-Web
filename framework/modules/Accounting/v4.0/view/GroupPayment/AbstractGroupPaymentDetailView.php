<?php

/**
 * Description of AbstractGroupPaymentDetailView  
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractGroupPaymentDetailView extends GI_View {

    protected $groupPayment;
    protected $uploader = NULL;
    protected $contactLabel = 'Contact';

    public function __construct(AbstractGroupPayment $groupPayment) {
        parent::__construct();
        $this->groupPayment = $groupPayment;
    }

    public function setUploader(AbstractGI_Uploader $uploader = NULL) {
        $this->uploader = $uploader;
        return $this;
    }
    
    public function setContactLabel($contactLabel) {
        $this->contactLabel = $contactLabel;
    }

    protected function openViewWrap() {
        $this->addHTML('<div class="content_padding">');
        return $this;
    }

    protected function closeViewWrap() {
        $this->addHTML('</div>');
        return $this;
    }

    protected function buildView() {
        $this->openViewWrap();
        $this->addButtons();
        $this->addViewHeader();
        $this->addHTML('<div class="columns halves">');
        $this->addHTML('<div class="column">');
        $this->addRemovedBySection();
        $this->addPaymentDate();
        $this->addContactName();
        $this->addType();
        $this->addTransactionNumber();
        $this->addCurrency();
        $this->addAmount();
        $this->addBalance();
        $this->addMemoSection();
        $this->addUploaderSection();
        $this->addHTML('</div>');
        $this->addHTML('<div class="column">');
        $this->addPaymentsSection();
        $this->addHTML('</div>');
        $this->addHTML('</div>');
        $this->closeViewWrap();
    }

    protected function addViewHeader() {
        $paymentTypeTitle = $this->groupPayment->getTypeTitle();
        $transactionNumber = $this->groupPayment->getTransactionNumber();
        $this->addHTML('<h1>' . $paymentTypeTitle . ' #' . $transactionNumber . '</h1>');
    }

    protected function addPaymentDate() {
        $paymentDate = $this->groupPayment->getProperty('date');
        $this->addContentBlock($paymentDate, 'Payment Date');
    }

    protected function addType() {
        $paymentType = $this->groupPayment->getTypeTitle();
        $this->addContentBlock($paymentType, 'Type');
    }
    
    protected function addContactName() {
        $contact = $this->groupPayment->getContact();
        if (!empty($contact)) {
            $viewURL = $contact->getViewURL();
            $contactName = $contact->getName();
            $this->addContentBlock('<a href="'.$viewURL.'">'.$contactName.'</a>', $this->contactLabel);
        }
        
    }

    protected function addCurrency() {
        $currency = $this->groupPayment->getCurrency();
        $this->addContentBlock($currency->getProperty('name'), 'Currency');
    }

    protected function addAmount() {
        $paymentAmount = $this->groupPayment->getAmount(true, true);
        $this->addContentBlock($paymentAmount, 'Amount');
    }

    protected function addBalance() {
        $balance = $this->groupPayment->getBalance(true, true);
        $this->addContentBlock($balance, 'Balance');
    }

    protected function addTransactionNumber() {
        $transactionNumber = $this->groupPayment->getTransactionNumber();
        if (!empty($transactionNumber)) {
            $this->addContentBlock($transactionNumber, 'Transaction Number');
        }
    }

    protected function addPaymentsSection() {
        $payments = $this->groupPayment->getPayments();
        if (!empty($payments)) {
            $this->addContentBlockTitle('Applied To');
            $examplePaymentClass = get_class($payments[0]);
            $paymentsUITableCols = $examplePaymentClass::getUITableCols();
            $uiTableView = new PaymentTableView($payments, $paymentsUITableCols);
            $this->addHTML($uiTableView->getHTMLView());
        }
    }

    protected function addButtons() {
        $this->addHTML('<div class="right_btns">');
        if ($this->groupPayment->getIsVoidable()) {
            $voidURL = $this->groupPayment->getVoidURL();
            $this->addHTML('<a href="' . $voidURL . '" title="Void Payment" class="custom_btn open_modal_form" ><span class="icon_wrap"><span class="icon void"></span></span><span class="btn_text">Void</span></a>');
        }
        if ($this->groupPayment->getIsEditable()) {
            $editURL = $this->groupPayment->getEditURL();
            $this->addHTML('<a href="' . $editURL . '" title="Edit Payment" class="custom_btn" ><span class="icon_wrap"><span class="icon pencil"></span></span><span class="btn_text">Edit</span></a>');
        }

        $this->addHTML('</div>');
    }

    protected function addRemovedBySection() {
        $isVoid = $this->groupPayment->getIsVoid();
        if ($isVoid) {
            $removedByUser = $this->groupPayment->getRemovedBy();
            $removedNotes = $this->groupPayment->getRemovedNotes();
            $removedDate = $this->groupPayment->getRemovedDate();
            if (!empty($removedByUser)) {
                $this->addContentBlock($removedByUser->getFullName(), 'Voided By');
            }
            if (!empty($removedDate)) {
                $this->addContentBlock(GI_Time::formatDateForDisplay($removedDate), 'Voided On');
            }
            if (!empty($removedNotes)) {
                $this->addContentBlock($removedNotes, 'Void Notes');
            }
        }
    }

    protected function addMemoSection() {
        $memo = $this->groupPayment->getProperty('memo');
        $this->addContentBlock($memo, 'Memo');
    }

    protected function addUploaderSection() {
        if (!empty($this->uploader)) {
            $this->addHTML($this->uploader->getHTMLView());
        }
    }

    public function beforeReturningView() {
        $this->buildView();
    }

}
