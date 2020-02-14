<?php
/**
 * Description of AbstractPaymentIncome
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractPaymentIncome extends AbstractPayment {
    
    protected $income = NULL;
    protected $invoice = NULL;
    protected $tableWrapId = 'group_payment_income_table';
    protected static $searchFormId = 'group_payment_income_search';

    
    public function getContactRelationDisplayHTML(AbstractGroupPayment $groupPayment = NULL) {
        if (empty($groupPayment)) {
            $groupPayment = $this->getGroupPayment();
        }
        $contact = $groupPayment->getContact();
        $contactName = $contact->getName();
        $contactViewURL = $contact->getViewURL();
        return GI_View::getContentBlockTitle('Payment From') . '<p class="content_block"><a href="' . $contactViewURL . '">' . $contactName . '</a></p>';
    }

    public function getIncome() {
        if (empty($this->income)) {
            $incomeId = $this->getProperty('payment_income.income_id');
            $income = IncomeFactory::getModelById($incomeId);
            $this->income = $income;
        }
        return $this->income;
    }

    public function getInvoice() {
        if (empty($this->invoice)) {
            $income = $this->getIncome();
            if (!empty($income)) {
                $this->invoice = InvoiceFactory::getInvoiceByIncome($income);
            }
        }
        return $this->invoice;
    }
    
    public function getInvoiceNumber() {
        $invoice = $this->getInvoice();
        if (!empty($invoice)) {
            return $invoice->getProperty('invoice_number');
        }
        return '';
    }
    
    public function getInvoiceDate() {
        $invoice = $this->getInvoice();
        if (!empty($invoice)) {
            return $invoice->getProperty('date');
        }
        return '';
    }
    
    public function getInvoiceFinalizedDate() {
        $invoice = $this->getInvoice();
        if (!empty($invoice)) {
            $dateTimeString = $invoice->getProperty('finalized_date');
            if (!empty($dateTimeString)) {
                $dateTime = new DateTime($dateTimeString);
                return $dateTime->format('Y-m-d');
            }
        }
        return '';
    }

    public function getAppliedToName() {
        $invoice = $this->getInvoice();
        if (!empty($invoice)) {
            return 'Invoice #' . $invoice->getProperty('invoice_number');
        }
        return '';
    }

    public function getLinkedExpenseOrIncome() {
        return $this->getIncome();
    }

    public static function getUITableCols() {
        $tableColArrays = array(
            array(
                'header_title' => 'Invoice',
                'method_name' => 'getAppliedToName',
                'cell_url_method_name' => 'getAppliedToURL',
            ),
            array(
                'header_title' => 'Amount',
                'method_name' => 'getAmount',
                'method_attributes' => 'true, true'
            ),
        );
        $UITableCols = parent::getUITableCols();
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UITableCols;
    }

    public function getAppliedToURL() {
        $income = $this->getIncome();
        $invoice = InvoiceFactory::getInvoiceByIncome($income);
        if (!empty($invoice)) {
            return $invoice->getViewURL();
        }
        return NULL;
    }

    public function getGroupPaymentFormTitle($plural = true) {
        return 'Invoice ' . parent::getGroupPaymentFormTitle($plural);
    }

    public function getGroupPaymentFormContactFieldDisplayName() {
        return 'From ' . parent::getGroupPaymentFormContactFieldDisplayName();
    }

    protected function setPropertiesFromForm(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            $invoiceId = filter_input(INPUT_POST, $this->getFieldName('invoice_id'));
            $invoice = InvoiceFactory::getModelById($invoiceId);
            $incomeArray = IncomeFactory::getIncomeArrayFromLinkedModel($invoice);
            if (!empty($incomeArray)) {
                $income = $incomeArray[0];
                $this->setProperty('payment_income.income_id', $income->getProperty('id'));
            }
        }
        return parent::setPropertiesFromForm($form);
    }

    public function save() {
        if (parent::save()) {
            $income = $this->getIncome();
            if (!empty($income)) {
                if (!$income->save()) {
                    return false;
                }
                $invoice = $this->getInvoice();
                if (!empty($invoice) && !$invoice->save()) {
                    return false;
                }
                return true;
            }
        }
        return false;
    }

    public function getGroupPaymentFormPaymentTypeTitle($plural = true) {
        $title = 'Invoice';
        if ($plural) {
            $title .= '(s)';
        }
        return $title;
    }

    public function getFormView(\GI_Form $form) {
        return new AccountingPaymentIncomeFormView($form, $this);
    }
    
    public function softDelete() {
        $income = $this->getIncome();
        if (parent::softDelete()) {
            if ($income->save()) {
                $invoice = InvoiceFactory::getInvoiceByIncome($income);
                if (!empty($invoice) && !$invoice->save()) {
                    return false;
                }
                return true;
            }
        }
        return false;
    }

    public function getGroupPaymentBreadcrumbs(AbstractGroupPayment $groupPayment, $verb = '') {
        $breadcrumbs = array();
        $breadcrumbs[] = array(
            'label' => 'Accounting',
            'link' => ''
        );
        $breadcrumbs[] = array(
            'label' => 'Sales',
            'link' => ''
        );
        $breadcrumbs[] = array(
            'label' => $groupPayment->getIndexTitle(true),
            'link' => GI_URLUtils::buildURL(array(
                'controller' => 'accounting',
                'action' => 'accountsReceivable',
                'tab' => $groupPayment->getARTabRef()
            ))
        );
        $indexTitle = $groupPayment->getIndexTitle();
        $transNumber = $groupPayment->getProperty('transaction_number');
        if (empty($verb)) {
            $label = $indexTitle . ' #' . $transNumber;
        } else {
            $label = $verb . ' ' . $indexTitle;
        }
        $breadcrumbs[] = array(
            'label'=>$label,
            'link'=>  GI_URLUtils::buildURL(array(
                'controller'=>'accounting',
                'action'=>'viewPayment',
                'id'=>$groupPayment->getProperty('id')
            )),
        );
        return $breadcrumbs;
    }

    public function getExportUITableCols() {
        $tableColArrays = array(
            //Payment From
            array(
                'header_title' => 'Payment From',
                'method_name' => 'getPaymentFromName'
            ),
            //Payment Type
            array(
                'header_title' => 'Payment Type',
                'method_name' => 'getPaymentType'
            ),
            //Payment Date
            array(
                'header_title' => 'Payment Date',
                'method_name' => 'getDate',
                'method_attributes' => array(
                    false,
                    true
                ),
            ),
//            //Currency
            array(
                'header_title' => 'Currency',
                'method_name' => 'getCurrencyName',
            ),
            //Payment Total
            array(
                'header_title' => 'Payment/Credit Total',
                'method_name' => 'getGroupPaymentTotal'
            ),
            array(
                'header_title' => 'Applied Payment Amount',
                'method_name' => 'getNonCreditAmount',
            ),
            array(
                'header_title' => 'Applied Credit Amount',
                'method_name' => 'getCreditAmount',
            ),
            //Applied Date
            array(
                'header_title'=>'Applied Date',
                'method_attributes'=>'applicable_date'
            ),
            //Invoice Number
            array(
                'header_title' => 'Invoice Number',
                'method_name' => 'getInvoiceNumber'
            ),
            //Invoice Date
            array(
                'header_title' => 'Invoice Date',
                'method_name' => 'getInvoiceDate'
            ),
            //Invoice Finalized Date
            array(
                'header_title' => 'Invoice Finalized Date',
                'method_name' => 'getInvoiceFinalizedDate'
            ),
            //Memo
            array(
                'header_title' => 'Memo',
                'method_name' => 'getGroupPaymentMemo'
            ),
        );
        $UITableCols = parent::getExportUITableCols();
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UITableCols;
    }

    public function getPaymentFromName() {
        $groupPayment = $this->getGroupPayment();
        if (!empty($groupPayment)) {
            return $groupPayment->getContactName();
        }
        return '';
    }

    public function isIndexViewable() {
        if (!Permission::verifyByRef('view_invoice_payments_index')) {
            return false;
        }
        return true;
    }

    public function getGroupPaymentContactLabel() {
        return 'Payment From';
    }

}
