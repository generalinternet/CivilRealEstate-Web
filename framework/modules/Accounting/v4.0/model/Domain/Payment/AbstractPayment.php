<?php
/**
 * Description of AbstractPayment
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractPayment extends GI_FormRowableModel {

    protected $groupPayment = NULL;
    protected $tableWrapId = 'group_payment_table';
    protected static $searchFormId = 'group_payment_search';

    /** @return string */
    public function getTableWrapId() {
        return $this->tableWrapId;
    }

    /** @return string */
    public static function getSearchFormId() {
        return static::$searchFormId;
    }

    public function getIndexTitle() {
        return 'Payments';
    }
    
    public function getCreditIndexTitle() {
        return 'Credits';
    }

    public function getGroupPayment() {
        if (empty($this->groupPayment)) {
            $groupPaymentId = $this->getProperty('payment.group_payment_id');
            $groupPayment = GroupPaymentFactory::getModelById($groupPaymentId);
            $this->groupPayment = $groupPayment;
        }
        return $this->groupPayment;
    }
    
    public function getGroupPaymentViewURL() {
        $groupPayment = $this->groupPayment;
         if (!empty($groupPayment)) {
             return GI_URLUtils::buildURL(array(
                 'controller'=>'accounting',
                 'action'=>'viewPayment',
                 'id'=>$groupPayment->getProperty('id')
             ));
         }
         return '';
    }
    
    public function save() {
        $groupPayment = $this->getGroupPayment();
        $applicableDate = $this->getProperty('applicable_date');
        if (empty($applicableDate)) {
            $applicableDate = $groupPayment->getProperty('applicable_date');
            if (empty($applicableDate)) {
                $inception = $this->getProperty('inception');
                if (empty($inception)) {
                    $applicableDate = GI_Time::getDate();
                }
            }
            $this->setProperty('applicable_date', $applicableDate);
        }
        if (parent::save()) {
            return $groupPayment->save();
        }
        return false;
    }
    
    public function getContactRelationDisplayHTML(AbstractGroupPayment $groupPayment = NULL) {
        if (empty($groupPayment)) {
            $groupPayment = $this->getGroupPayment();
        }
        $contact = $groupPayment->getContact();
        $contactName = $contact->getName();
        $contactViewURL = $contact->getViewURL();
        return GI_View::getContentBlockTitle('Contact') . '<p class="content_block"><a href="' . $contactViewURL . '">' . $contactName . '</a></p>';
    }

    public static function getUITableCols() {
        $tableColArrays = array(
            array(
                'header_title' => 'Date',
                'method_name' => 'getDate',
                'method_attributes' => 'true',
            ),
        );
        $UITableCols = array();
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UITableCols;
    }

    public static function getTableViewUITableCols() {
        $tableColArrays = array(
            array(
                'header_title' => 'Date',
                'method_name' => 'getDate',
                'method_attributes' => 'true',
            ),
            array(
                'header_title' => 'Type',
                'method_name'=> 'getPaymentType',
            ),
            array(
                'header_title'=>'Transaction #',
                'method_name'=>'getTransactionNumber',
                'cell_url_method_name'=>'getGroupPaymentViewURL'
            ),
            array(
                'header_title' => 'Amount',
                'method_name' => 'getAmount',
                'method_attributes' => 'true, true'
            ),
        );
        $UITableCols = array();
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UITableCols;
    }

    public function getDate($formatForDisplay = false, $formatForExport = false) {
        $date = $this->getProperty('date');
        if ($formatForDisplay) {
            $date = GI_Time::formatDateForDisplay($date);
        } else if ($formatForExport) {
            $dateTime = new DateTime($date . ' 00:00:00');
            $date = $dateTime->format('m/d/Y');
        }
        return $date;
    }

    public function getAmount($formatForDisplay = false, $showCurrency = false) {
        $amount = $this->getProperty('amount');
        if ($formatForDisplay) {
            return $this->formatAmountForDisplay($amount, $showCurrency);
        }
        return $amount;
    }

    public function getAppliedToName() {
        return '';
    }

    public function getAppliedToURL() {
        return NULL;
    }

    protected function formatAmountForDisplay($amount, $showCurrency = false) {
        $currency = $this->getCurrency();
        if (empty($currency)) {
            return $amount;
        }
        $total = $currency->getProperty('symbol') . GI_StringUtils::formatMoney($amount);
        if ($showCurrency && !GI_CSV::csvExporting()) {
            $total .= ' (' . $currency->getProperty('name') . ')';
        }
        return $total;
    }

    public function getCurrency() {
        $groupPayment = $this->getGroupPayment();
        if (!empty($groupPayment)) {
            $currency = $groupPayment->getCurrency();
            return $currency;
        }
    }
    
    public function getCurrencyName() {
        $currency = $this->getCurrency();
        if (!empty($currency)) {
            return $currency->getProperty('name');
        }
        return '';
    }
    
    public function getLinkedExpenseOrIncome() {
        return NULL;
    }

    public function void($saveGroupPayment = true) {
        $this->setProperty('void', 1);
        if (!$this->save()) {
            return false;
        }
        if ($saveGroupPayment) {
            $groupPayment = $this->getGroupPayment();
            if (!$groupPayment->save()) {
                return false;
            }
        }
        $linkedModel = $this->getLinkedExpenseOrIncome();
        if (!empty($linkedModel)) {
            if (!$linkedModel->save()) {
                return false;
            }
        }
        return true;
    }

    public function cancel($saveGroupPayment = true) {
        $this->setProperty('cancelled', 1);
        if (!$this->save()) {
            return false;
        }
        if ($saveGroupPayment) {
            $groupPayment = $this->getGroupPayment();
            if (!$groupPayment->save()) {
                return false;
            }
        }
        $linkedModel = $this->getLinkedExpenseOrIncome();
        if (!empty($linkedModel)) {
            if (!$linkedModel->save()) {
                return false;
            }
        }
        return true;
    }
    
    public function getPaymentType() {
        $groupPayment = $this->getGroupPayment();
        if (!empty($groupPayment)) {
            return $groupPayment->getTypeTitle();
        }
        return '';
    }
    
    public function getTransactionNumber() {
        $groupPayment = $this->getGroupPayment();
        if (!empty($groupPayment)) {
            return $groupPayment->getProperty('transaction_number');
        }
        return '';
    }

    public function getIsVoid() {
        $void = $this->getProperty('void');
        if (!empty($void)) {
            return true;
        }
    }

    public function isVoid() {
        return $this->getIsVoid();
    }

    public function getIsCancelled() {
        $cancelled = $this->getProperty('cancelled');
        if (!empty($cancelled)) {
            return true;
        }
    }

    public function isCancelled(){
        return $this->getIsCancelled();
    }

    public function getIsVoidOrCancelled() {
        if ($this->getIsCancelled()) {
            return true;
        }
        if ($this->getIsVoid()) {
            return true;
        }
        return false;
    }

    public function getIsVoidable() {
        if ($this->getIsVoidOrCancelled()) {
            return false;
        }
        if (!$this->getIsLocked()) {
            return true;
        }
        return false;
    }

    public function getIsCancellable() {
        if ($this->getIsVoidOrCancelled()) {
            return false;
        }
        if (!$this->getIsLocked()) {
            return true;
        }
        return false;
    }

    public function getIsLocked() {
        return true;
    }
    
    /**
     * @deprecated use $groupPayment->getFormView instead
     * @param GI_Form $form
     * @param AbstractGroupPayment $groupPayment
     * @return \AccountingGroupPaymentFormView
     */
    public function getGroupPaymentFormView(GI_Form $form, AbstractGroupPayment $groupPayment) {
        $view = new AccountingGroupPaymentFormView($form, $groupPayment, $this);
        $uploader = $groupPayment->getUploader($form);
        $view->setUploader($uploader);
        return $view;
    }
    
    public function getGroupPaymentFormTitle($plural = true) {
        $title = 'Payment';
        if ($plural) {
            $title .= 's';
        }
        return $title;
    }
    
    public function getGroupPaymentFormPaymentTypeTitle() {
        return '';
    }

    public function getGroupPaymentFormContactFieldDisplayName() {
        return 'Contact';
    }

    public function getFormView(\GI_Form $form) {
        return NULL;
    }

    protected function setPropertiesFromForm(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            $amount = filter_input(INPUT_POST, $this->getFieldName('amount'));
            if (empty($amount)) {
                return NULL;
            }
            $date = filter_input(INPUT_POST, $this->getFieldName('date'));
            $this->setProperty('amount', $amount);
            $this->setProperty('date', $date);
            $this->setProperty('applicable_date', $date);
        }
        return $this;
    }

    public function handleFormSubmission(\GI_Form $form, AbstractGroupPayment $groupPayment) {
        if ($form->wasSubmitted() && $form->validate()) {
            $this->setProperty('group_payment_id', $groupPayment->getProperty('id'));
            if (!empty($this->setPropertiesFromForm($form))) {
                if ($this->save()) {
                    return true;
                }
            } else {
                return true;
            }
        }
        return false;
    }
    
    public function getGroupPaymentBreadcrumbs(AbstractGroupPayment $groupPayment, $verb = '') {
        return array();
    }
    
     /**
     * @param AbstractGroupPayment[] $groupPayments
     * @param AbstractUITableView $uiTableView
     * @param AbstractPayment $samplePayment
     * @param GI_SearchView $searchView
     * @return \AbstractGroupPaymentIndexView
     */
    public static function getIndexView($groupPayments = NULL, AbstractUITableView $uiTableView = NULL, AbstractPayment $samplePayment = NULL, GI_SearchView $searchView = NULL){
        $view = new AccountingGroupPaymentIndexView($groupPayments, $uiTableView, $samplePayment, $searchView);
        $view->setTabView(true);
        return $view;
    }
    
    /**
     * @param AbstractGroupPayment[] $groupPayments
     * @param AbstractUITableView $uiTableView
     * @param AbstractPayment $samplePayment
     * @param GI_SearchView $searchView
     * 
     */
    public static function getCreditIndexView($groupPayments = NULL, AbstractUITableView $uiTableView = NULL, AbstractPayment $samplePayment = NULL, GI_SearchView $searchView = NULL) {
        $view = new AccountingCreditIndexView($groupPayments, $uiTableView, $samplePayment, $searchView);
        $view->setTabView(true);
        return $view;
    }
    
    public static function getImportedPaymentsIndexView($groupPayments = NULL, AbstractUITableView $uiTableView = NULL, AbstractPayment $samplePayment = NULL, GI_SearchView $searchView = NULL) {
        $view = new GroupPaymentImportedIndexView($groupPayments, $uiTableView, $samplePayment, $searchView);
        $view->setTabView(true);
        return $view;
    }
    
        /**
     * @param GI_Form $form
     * @param array $searchValues
     * @param string $type
     * @return \AbstractGroupPaymentSearchFormView
     */
    protected static function buildSearchFormView(GI_Form $form, AbstractGroupPayment $groupPayment, $searchValues = NULL, $type = NULL){
        return $groupPayment->getSearchFormView($form, $searchValues, $type);
    }

    /**
     * @param GI_Form $form
     * @param GI_DataSearch $dataSearch
     * @return \AbstractGroupPaymentSearchFormView
     */
    protected static function getSearchFormView(GI_Form $form, AbstractGroupPayment $groupPayment, GI_DataSearch $dataSearch = NULL, $type = NULL) {
        $searchValues = array();
        if ($dataSearch) {
            $searchValues = $dataSearch->getSearchValues();
        }
        $searchValues['queryId'] = $dataSearch->getQueryId();
        $searchView = static::buildSearchFormView($form, $groupPayment, $searchValues, $type);
        return $searchView;
    }
    
    /**
     * @param GI_DataSearch $dataSearch
     * @param GI_Form $form
     * @return boolean
     */
    protected static function filterSearchForm(GI_DataSearch $dataSearch, GI_Form $form = NULL) {
        $transactionNumber = $dataSearch->getSearchValue('transaction_number');
        if (!empty($transactionNumber)) {
            static::addTransactionNumberFilterToDataSearch($transactionNumber, $dataSearch);
        }
        $contactId = $dataSearch->getSearchValue('contact_id');
        if(!empty($contactId)){
            static::addContactIdFilterToDataSearch($contactId, $dataSearch);
        }
        
        $currencyId = $dataSearch->getSearchValue('currency_id');
        if(!empty($currencyId) && $currencyId != 'NULL'){
            static::addCurrencyIdFilterToDataSearch($currencyId, $dataSearch);
        }
        
        $startDate = $dataSearch->getSearchValue('start_date');
        if(!empty($startDate)){
            static::addStartDateFilterToDataSearch($startDate, $dataSearch);
        }
        
        $endDate = $dataSearch->getSearchValue('end_date');
        if(!empty($endDate)){
            static::addEndDateFilterToDataSearch($endDate, $dataSearch);
        }
//        
        if(!is_null($form) && $form->wasSubmitted() && $form->validate()){
            $transactionNumber = filter_input(INPUT_POST, 'search_trans_number');
            $dataSearch->setSearchValue('transaction_number', $transactionNumber);
            
            $title = filter_input(INPUT_POST, 'search_title');
            $dataSearch->setSearchValue('title', $title);
            
            $contactId = filter_input(INPUT_POST, 'search_contact_id');
            $dataSearch->setSearchValue('contact_id', $contactId);
            
            $currencyId = filter_input(INPUT_POST, 'search_currency_id');
            $dataSearch->setSearchValue('currency_id', $currencyId);
            
            $startDate = filter_input(INPUT_POST, 'search_start_date');
            $dataSearch->setSearchValue('start_date', $startDate);
            
            $endDate = filter_input(INPUT_POST, 'search_end_date');
            $dataSearch->setSearchValue('end_date', $endDate);
        }
        return true;
    }
    
    /**
     * @param GI_DataSearch $dataSearch
     * @param string $type
     * @param array $redirectArray
     * @return AbstractGroupPaymentSearchFormView
     */
    public static function getSearchForm(GI_DataSearch $dataSearch, AbstractGroupPayment $groupPayment, $type = NULL, $redirectArray = array()){
        $form = new GI_Form(static::getSearchFormId());
        $searchView = static::getSearchFormView($form, $groupPayment, $dataSearch, $type);
        
        static::filterSearchForm($dataSearch, $form);
        
        if($form->wasSubmitted() && $form->validate()){
            $queryId = $dataSearch->getQueryId();
            
            if(empty($redirectArray)){
                $redirectArray = array(
                    'controller' => 'accounting',
                    'action' => 'paymentsIndex',
                );
                
                if(!empty($type)){
                    $redirectArray['type'] = $type;
                }
            }
            
            $redirectArray['queryId'] = $queryId;
            if(GI_URLUtils::getAttribute('ajax')){
                $redirectArray['ajax'] = 1;
            }
            
            GI_URLUtils::redirect($redirectArray);
        }
        return $searchView;
    }
    
    public static function addTransactionNumberFilterToDataSearch($transactionNumber, GI_DataSearch $dataSearch) {
        $dataSearch->filterLike('transaction_number', '%' . $transactionNumber . '%');
    }
    
    public static function addContactIdFilterToDataSearch($contactId, GI_DataSearch $dataSearch){
        $dataSearch->filter('contact_id', $contactId);
    }

    public static function addCurrencyIdFilterToDataSearch($currencyId, GI_DataSearch $dataSearch) {
        $dataSearch->filter('currency_id', $currencyId);
    }

    public static function addStartDateFilterToDataSearch($startDate, GI_DataSearch $dataSearch) {
        $dataSearch->filterGreaterOrEqualTo('date', $startDate);
    }

    public static function addEndDateFilterToDataSearch($endDate, GI_DataSearch $dataSearch) {
        $dataSearch->filterLessOrEqualTo('date', $endDate);
    }

    public function getExportUITableCols() {
        $tableColArrays = array(
            array(
                'header_title' => 'Payment Number',
                'method_name' => 'getPaymentNumber'
            ),
        );
        $UITableCols = array();
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UITableCols;
    }
    
    public function getPaymentNumber() {
        $groupPayment = $this->getGroupPayment();
        if (empty($groupPayment)) {
            return '';
        }
        return $groupPayment->getProperty('transaction_number');
    }
    
    public function getGroupPaymentTotal() {
        $groupPayment = $this->getGroupPayment();
        if (!empty($groupPayment)) {
            return $groupPayment->getProperty('amount');
        }
        return NULL;
    }
    
    public function getGroupPaymentMemo() {
        $groupPayment = $this->getGroupPayment();
        if (!empty($groupPayment)) {
            return $groupPayment->getProperty('memo');
        }
        return '';
    }
    
    public function getInvoice() {
        return NULL;
    }
    
    public function isIndexViewable() {
        return true;
    }
    
    public function getIsAddable() {
        if (Permission::verifyByRef('add_payments')) {
            return true;
        }
        return false;
    }

    /**
     * Used by export functions
     * @return float
     */
    public function getNonCreditAmount() {
        $groupPayment = $this->getGroupPayment();
        return $groupPayment->getNonCreditAmount($this);
    }

    /**
     * Used by export functions
     * @return float
     */
    public function getCreditAmount() {
        $groupPayment = $this->getGroupPayment();
        return $groupPayment->getCreditAmount($this);
    }
    
    public function getGroupPaymentContactLabel() {
        return 'Contact';
    }

}
