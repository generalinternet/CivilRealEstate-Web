<?php
/**
 * Description of AbstractPaymentExpense
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractPaymentExpense extends AbstractPayment {

    protected $expense = NULL;
    protected $bill = NULL;
    protected $tableWrapId = 'group_payment_expense_table';
    protected static $searchFormId = 'group_payment_expense_search';

    public function getContactRelationDisplayHTML(AbstractGroupPayment $groupPayment = NULL) {
        if (empty($groupPayment)) {
            $groupPayment = $this->getGroupPayment();
        }
        $contact = $groupPayment->getContact();
        $contactName = $contact->getName();
        $contactViewURL = $contact->getViewURL();
        return GI_View::getContentBlockTitle('Payment To') . '<p class="content_block"><a href="' . $contactViewURL . '">' . $contactName . '</a></p>';
    }

    public function getExpense() {
        if (empty($this->expense)) {
            $expenseId = $this->getProperty('payment_expense.expense_id');
            $expense = ExpenseFactory::getModelById($expenseId);
            $this->expense = $expense;
        }
        return $this->expense;
    }

    public function getBill() {
        if (empty($this->bill)) {
            $expense = $this->getExpense();
            if (!empty($expense)) {
                $this->bill = $expense->getBill();
            }
        }
        return $this->bill;
    }

    public function getLinkedExpenseOrIncome() {
        return $this->getExpense();
    }

    public static function getUITableCols() {
        $tableColArrays = array(
            //Date
            array(
                'header_title' => 'Bill',
                'method_name' => 'getAppliedToBillNumber',
                'cell_url_method_name' => 'getAppliedToBillURL',
            ),
            //Amount
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

    public function getAppliedToBillNumber() {
        $bill = $this->getBill();
        if (!empty($bill)) {
            return $bill->getProperty('bill_number');
        }
        return '';
    }

    public function getAppliedToBillURL() {
        $bill = $this->getBill();
        if (!empty($bill)) {
            return GI_URLUtils::buildURL(array(
                'controller'=>'billing',
                'action' => 'view',
                        'id' => $bill->getProperty('id')
            ));
        }
        return '';
    }

    public function getGroupPaymentFormTitle($plural = true) {
        return 'Bill ' . parent::getGroupPaymentFormTitle($plural);
    }

    public function getGroupPaymentFormContactFieldDisplayName() {
        return 'To ' . parent::getGroupPaymentFormContactFieldDisplayName();
    }

    protected function setPropertiesFromForm(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            $billId = filter_input(INPUT_POST, $this->getFieldName('bill_id'));
            $bill = BillFactory::getModelById($billId);
            $expenseArray = ExpenseFactory::getExpenseArrayFromLinkedModel($bill);
            if (!empty($expenseArray)) {
                $expense = $expenseArray[0];
                $this->setProperty('payment_expense.expense_id', $expense->getProperty('id'));
            }
        }
        return parent::setPropertiesFromForm($form);
    }

    public function save() {
        if (parent::save()) {
            $expense = $this->getExpense();
            if (!empty($expense)) {
                if (!$expense->save()) {
                    return false;
                }
                $bill = $expense->getBill();
                if (!empty($bill)) {
                    return $bill->save();
                }
            }
            return true;
        }

        return false;
    }

    public function getFormView(\GI_Form $form) {
        return new AccountingPaymentExpenseFormView($form, $this);
    }

    public function getGroupPaymentFormPaymentTypeTitle($plural = true) {
        $title = 'Bill';
        if ($plural) {
            $title .='(s)';
        }
        return $title;
    }
    
    public function softDelete() {
        $expense = $this->getExpense();
        if (parent::softDelete()) {
            if ($expense->save()) {
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
            'label' => 'Expenses',
            'link' => ''
        );
        $breadcrumbs[] = array(
            'label' => $groupPayment->getIndexTitle(true),
            'link' => GI_URLUtils::buildURL(array(
                'controller' => 'accounting',
                'action' => 'accountsPayable',
                'tab' => $groupPayment->getARTabRef(),
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
            'label' => $label,
            'link' => GI_URLUtils::buildURL(array(
                'controller' => 'accounting',
                'action' => 'viewPayment',
                'id' => $groupPayment->getProperty('id')
            )),
        );
        return $breadcrumbs;
    }

    public function getExportUITableCols() {
        $tableColArrays = array(
            array(
                'header_title' => 'Payment To',
                'method_name' => 'getPaymentToName'
            ),
            array(
                'header_title' => 'Payment Type',
                'method_name' => 'getPaymentType'
            ),
            array(
                'header_title' => 'Payment Date',
                'method_name' => 'getDate',
                'method_attributes' => array(
                    false,
                    true
                ),
            ),
            array(
                'header_title' => 'Currency',
                'method_name' => 'getCurrencyName',
            ),
            array(
                'header_title' => 'Payment Total',
                'method_name' => 'getGroupPaymentTotal'
            ),
            array(
                'header_title' => 'Applied Amount',
                'method_name' => 'getAmount',
                'method_attributes' => array(
                    false,
                    false,
                ),
            ),
            //Applied Date
            array(
                'header_title'=>'Applied Date',
                'method_attributes'=>'applicable_date'
            ),
            array(
                'header_title' => 'Memo',
                'method_name' => 'getGroupPaymentMemo'
            ),
            array(
                'header_title' => 'Bill Number',
                'method_name' => 'getBillNumber'
            ),
        );
        $UITableCols = parent::getExportUITableCols();
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UITableCols;
    }

    public function getPaymentToName() {
        $groupPayment = $this->getGroupPayment();
        if (!empty($groupPayment)) {
            return $groupPayment->getContactName();
        }
        return '';
    }

    public function getBillNumber() {
        $bill = $this->getBill();
        if (!empty($bill)) {
            return $bill->getProperty('bill_number');
        }
        return '';
    }

    public function getGroupPaymentContactLabel() {
        return 'Payment To';
    }

}
