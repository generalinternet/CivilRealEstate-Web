<?php
/**
 * Description of AbstractPaymentAccount
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractPaymentAccount extends GI_Model {

    protected $tableWrapId = 'payment_account_table';
    protected static $searchFormId = 'payment_account_search';
    protected $currency = NULL;

    /** @return string */
    public function getTableWrapId() {
        return $this->tableWrapId;
    }

    /** @return string */
    public static function getSearchFormId() {
        return static::$searchFormId;
    }

    public static function getIndexView($paymentAccounts, AbstractUITableView $uiTableView, AbstractPaymentAccount $samplePaymentAccount, GI_SearchView $searchView = NULL) {
        return new PaymentAccountIndexView($paymentAccounts, $uiTableView, $samplePaymentAccount, $searchView);
    }

    public function isIndexViewable() {
        //TODO - permission check
        return true;
    }
    
    public function getIndexTitle() {
        return 'Accounts';
    }
    
    public function getFormView(GI_Form $form) {
        return new PaymentAccountFormView($form, $this);
    }
    
    public function handleFormSubmission(GI_Form $form) {
        if ($form->wasSubmitted() && $this->validateForm($form)) {
            $targetTypeRef = filter_input(INPUT_POST, 'type');
            if ($targetTypeRef !== $this->getTypeRef()) {
                if (empty($this->getProperty('id'))) {
                    $updatedModel = PaymentAccountFactory::buildNewModel($targetTypeRef);
                } else {
                    $updatedModel = PaymentAccountFactory::changeModelType($this, $targetTypeRef);
                }
                return $updatedModel->handleFormSubmission($form);
            }
            $this->setPropertiesFromForm($form);
            if (!$this->save()) {
                return false;
            }
            return true;
        }
        return false;
    }
    
    protected function setPropertiesFromForm(GI_Form $form) {
        if ($form->wasSubmitted() && $this->validateForm($form)) {
            $name = filter_input(INPUT_POST, 'name');
            $currencyId = filter_input(INPUT_POST, 'currency_id');
            $this->setProperty('name', $name);
            $this->setProperty('currency_id', $currencyId);
        }
    }
    
    public function validateForm(\GI_Form $form) {
        if ($this->formValidated) {
            return true;
        }
        if ($form->wasSubmitted() && $form->validate()) {
            
            $this->formValidated = true;
        }
        return $this->formValidated;
    }
    
    public function getIsAddable() {
        //TODO - add permission check
        return true;
    }
    
    public function getIsEditable() {
       //TODO - add permission check
        return true;
    }

    public function getIsDeleteable() {
        //TODO - add permission check
        return true;
    }

    public static function getUITableCols() {
        $tableColArrays = array(
            array(
                'header_title' => 'Name',
                'method_attributes'=>'name'
            ),
            array(
                'header_title' => 'Type',
                'method_name' => 'getTypeTitle',
            ),
            array(
                'header_title' => 'Currency',
                'method_name' => 'getCurrencyName',
            ),
            array(
                'header_title'=>'',
                'method_name'=>'getButtonsHTML'
            ),
        );
        $UITableCols = array();
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UITableCols;
    }
    
    public function getCurrency() {
        if (empty($this->currency)) {
            $this->currency = CurrencyFactory::getModelById($this->getProperty('currency_id'));
        }
        return $this->currency;
    }
    
    public function getCurrencyName() {
        $currency = $this->getCurrency();
        if (!empty($currency)) {
            return $currency->getProperty('name');
        }
        return '';
    }

    public function getButtonsHTML() {
        $html = '<span>';
        if ($this->isEditable()) {
            $editURL = GI_URLUtils::buildURL(array(
                        'controller' => 'accounting',
                        'action' => 'editPaymentAccount',
                        'id' => $this->getId()
            ));
            $html .= '<a href="' . $editURL . '" title="Edit" class="custom_btn open_modal_form" ><span class="icon_wrap"><span class="icon edit"></span></span><span class="btn_text">Edit</span></a> ';
        }
        if ($this->isDeleteable()) {
            $deleteURL = GI_URLUtils::buildURL(array(
                        'controller' => 'accounting',
                        'action' => 'deletePaymentAccount',
                        'id' => $this->getId()
            ));
            $html .= '<a href="' . $deleteURL . '" title="Delete" class="custom_btn open_modal_form" ><span class="icon_wrap"><span class="icon trash"></span></span><span class="btn_text">Delete</span></a> ';
        }
        $html .= '</span>';
        return $html;
    }

}
