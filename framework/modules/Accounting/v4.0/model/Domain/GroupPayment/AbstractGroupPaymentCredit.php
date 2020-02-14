<?php

/**
 * Description of AbstractGroupPaymentCredit
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractGroupPaymentCredit extends AbstractGroupPayment {

    protected $tableWrapId = 'group_payment_credit_table';
    protected static $searchFormId = 'group_payment_credit_search';
    protected static $initNumber = '528491';
    protected static $prependChars = 'CR';

    public function getFormView(GI_Form $form, AbstractPayment $payment) {
        $view = new AccountingGroupPaymentCreditFormView($form, $this, $payment);
        $uploader = $this->getUploader($form);
        $view->setUploader($uploader);
        return $view;
    }

    public function handleFormSubmission(GI_Form $form, AbstractPayment $examplePayment) {
        if (parent::handleFormSubmission($form, $examplePayment)) {
            if (empty($this->getProperty('transaction_number'))) {
                $this->setNewTransactionNumber();
                if (!$this->save()) {
                    return NULL;
                }
            }
            return $this;
        }
        return NULL;
    }

    protected function setNewTransactionNumber() {
        $initNumber = (int) static::$initNumber;
        $id = (int) $this->getProperty('id');
        if (empty($id)) {
            return false;
        }
        $newNumber = $initNumber + $id;
        $prependChars = static::$prependChars;
        $uniqueNumber = $prependChars . $newNumber;
        $this->setProperty('transaction_number', $uniqueNumber);
        return true;
    }

    public function getDetailView() {
        $view = new AccountingGroupPaymentCreditDetailView($this);
        $uploader = $this->getUploader();
        $view->setUploader($uploader);
        return $view;
    }

    public function getIndexTitle($plural = false) {
        $title = 'Credit';
        if ($plural) {
            $title .= 's';
        }
        return $title;
    }

    public function getAPtabRef() {
        return 'credits';
    }

    public function getARTabRef() {
        return 'credits';
    }

    public function getSearchFormView(GI_Form $form, $searchValues = NULL, $type = NULL) {
        return new GroupPaymentCreditSearchFormView($form, $searchValues, $type);
    }

    public function printOutput() {
        $pdf = new OutputPDF();
        $outputView = new AccountingGroupPaymentCreditOutputView($this);
        $pdf->addHTMLFromView($outputView);
        $filename = $this->getProperty('transaction_number') . '_' . $this->getProperty('date') . '.pdf';
        $pdf->Output($filename, 'I');
    }

    public function addCustomFiltersToDataSearch(GI_DataSearch $dataSearch) {
        if (!Permission::verifyByRef('view_credits')) {
            $dataSearch = $this->addViewPermissionFilterToDataSearch($dataSearch);
        }
        return $dataSearch;
    }

    protected function addViewPermissionFilterToDataSearch(GI_DataSearch $dataSearch) {
        $dataSearch->filter('uid', Login::getUserId());
        return $dataSearch;
    }

    protected function getIsViewable() {
        if (!Permission::verifyByRef('view_credits')) {
            $search = GroupPaymentFactory::search()
                    ->filter('id', $this->getProperty('id'));
            $search = $this->addViewPermissionFilterToDataSearch($search);
            $array = $search->select();
            if (empty($array)) {
                return false;
            }
        }
        return true;
    }

    public function isIndexViewable() {
        if (!Permission::verifyByRef('view_credits_index')) {
            return false;
        }
        return false;
    }

    public function getIsAddable() {
        if (!Permission::verifyByRef('add_credits')) {
            return false;
        }
        return true;
    }

    public function getIsEditable() {
        if ($this->getIsVoidOrCancelled() || $this->getIsLocked()) {
            return false;
        }
        if (!Permission::verifyByRef('edit_payments')) {
            $search = GroupPaymentFactory::search()
                    ->filter('id', $this->getProperty('id'));
            $search = $this->addViewPermissionFilterToDataSearch($search);
            if (empty($search->select())) {
                return false;
            }
        }
        return true;
    }

    public function isPrintable() {
        if (Permission::verifyByRef('print_credits')) {
            return true;
        }
        return false;
    }
    
    public function getNonCreditAmount(AbstractPayment $payment) {
        return NULL;
    }
    
    public function getCreditAmount(AbstractPayment $payment) {
        return $payment->getProperty('amount');
    }
}
