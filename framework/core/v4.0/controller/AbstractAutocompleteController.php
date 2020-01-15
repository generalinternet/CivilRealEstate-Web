<?php
/**
 * Description of AbstractAutocompleteController
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractAutocompleteController extends GI_Controller {

    /**
     * @param array $attributes
     * @return array
     * @deprecated since version 3.0.0 - use TagController->actionAutocompTag()
     */
    public function actionTag($attributes){
        $tagController = new TagController();
        return $tagController->actionAutocompTag($attributes);
    }

    /**
     * @param array $attributes
     * @return array
     * @deprecated since version 3.0.0 - use ContactController->actionAutocompContact()
     */
    public function actionContact($attributes){
        $contactController = new ContactController();
        return $contactController->actionAutocompContact($attributes);
    }

    public function actionTerms($attributes){

        if ((!isset($attributes['ajax']) || !$attributes['ajax'] == 1)){
            $returnArray = GI_Controller::getReturnArray();
            return $returnArray;
        }

        if(isset($attributes['curVal'])){
            $curVal = $attributes['curVal'];
            $curVals = explode(',', $curVal);

            $finalLabel = array();
            $finalValue = array();
            $finalResult = array();

            foreach($curVals as $termsId){
                $contactTerms = ContactTermsFactory::getModelById($termsId);
                if($contactTerms){
                    $name = $contactTerms->getProperty('terms');

                    $finalLabel[] = $name;
                    $finalValue[] = $termsId;
                    $finalResult[] = '<span class="result_text">'.$name.'</span>';
                }
            }
            $results = array(
                'label' => $finalLabel,
                'value' => $finalValue,
                'autoResult' => $finalResult
            );
            return $results;
        } else {
            if(isset($_REQUEST['term'])){
                $term = $_REQUEST['term'];
            } else {
                $term = '';
            }

            $search = ContactTermsFactory::search()
                    ->setItemsPerPage(ProjectConfig::getAutocompleteItemLimit())
                    ->filterLike('terms', '%' . $term . '%')
                    ->orderByLikeScore('terms', $term);
            $pageNumber = 1;
            if(isset($attributes['pageNumber'])){
                $pageNumber = (int) $attributes['pageNumber'];
                $search->setPageNumber($pageNumber);
            }
            $contactTermsResult = $search->select();

            $results = array();

            foreach($contactTermsResult as $contactTerms){
                $name = $contactTerms->getProperty('terms');

                $contactTermsInfo = array(
                    'label' => $name,
                    'value' => $contactTerms->getId(),
                    'autoResult' => '<span class="result_text">' . $this->markTerm($term, $name) . '</span>'
                );

                $results[] = $contactTermsInfo;

            }

            $itemsPerPage = $search->getItemsPerPage();
            $count = $search->getCount();
            $this->addAutocompNavToResults($results, $count, $itemsPerPage, $pageNumber);

            return $results;
        }
    }

    public function actionInventoryItem($attributes){

        if ((!isset($attributes['ajax']) || !$attributes['ajax'] == 1)){
            $returnArray = GI_Controller::getReturnArray();
            return $returnArray;
        }

        if(isset($attributes['curVal'])){
            $curVal = $attributes['curVal'];
            $curVals = explode(',', $curVal);

            $finalLabel = array();
            $finalValue = array();
            $finalResult = array();

            foreach($curVals as $invItemId){
                $invItem = InvItemFactory::getModelById($invItemId);
                if($invItem){
                    $name = $invItem->getProperty('name');

                    $finalLabel[] = $name;
                    $finalValue[] = $invItemId;
                    $finalResult[] = '<span class="result_text">'.$name.'</span>';
                }
            }
            $results = array(
                'label' => $finalLabel,
                'value' => $finalValue,
                'autoResult' => $finalResult
            );
            return $results;
        } else {
            if(isset($_REQUEST['term'])){
                $term = $_REQUEST['term'];
            } else {
                $term = '';
            }

            $invItemSearch = InvItemFactory::search()
                    ->setItemsPerPage(ProjectConfig::getAutocompleteItemLimit())
                    ->filterLike('name', '%' . $term . '%')
                    ->orderByLikeScore('name', $term);

            $invItems = $invItemSearch->select();

            $results = array();

            foreach($invItems as $invItem){
                /* @var $invItem InvItem */
                $name = $invItem->getProperty('name');

                $invItemInfo = array(
                    'label' => $name,
                    'value' => $invItem->getId(),
                    'autoResult' => '<span class="result_text"><i class="float_right">'.$invItem->getTypeTitle().'</i>' . $this->markTerm($term, $name) . '</span>'
                );
                $invItemInfo['icon'] = $invItem->getTypeIcon();
                $invItemInfo['desc'] = $invItem->getProperty('description');
                $invItemInfo['dims'] = $invItem->getDimensionString();
                $invItemInfo['container_type_id'] = $invItem->getProperty('default_inv_container_type_id');
                //@todo get real per case value
                $invItemInfo['per_container'] = $invItem->getProperty('default_units_per_container');
                $results[] = $invItemInfo;

            }

            $this->addMoreResult($invItemSearch, $results);

            return $results;
        }
    }

    /**
     * @param array $attributes
     * @return array
     * @deprecated since version 3.0.0 - use UserController->actionAutocompUser()
     */
    public function actionUser($attributes){
        $userController = new UserController();
        return $userController->actionAutocompUser($attributes);
    }

    /**
     * @param array $attributes
     * @return array
     * @deprecated since version 3.0.0 - use PermissionController->actionAutocompPermission()
     */
    public function actionPermission($attributes){
        $permissionController = new PermissionController();
        return $permissionController->actionAutocompPermission($attributes);
    }

    public function actionMLSCity($attributes){
        if ((!isset($attributes['ajax']) || !$attributes['ajax'] == 1)){
            $returnArray = GI_Controller::getReturnArray();
            return $returnArray;
        }

        if(isset($attributes['curVal'])){

            $curVal = $attributes['curVal'];
            $curVals = explode(',', $curVal);

            $finalLabel = array();
            $finalValue = array();
            $finalResult = array();

            foreach($curVals as $cityId){
                $city = MLSCityFactory::getModelById($cityId);
                if($city){
                    $name = $city->getTitle();

                    $finalLabel[] = $name;
                    $finalValue[] = $cityId;
                    $finalResult[] = '<span class="result_text">'.$name.'</span>';
                }
            }
            $results = array(
                'label' => $finalLabel,
                'value' => $finalValue,
                'autoResult' => $finalResult
            );
            return $results;
        } else {
            if(isset($_REQUEST['term'])){
                $term = $_REQUEST['term'];
            } else {
                $term = '';
            }

            $citySearch = MLSCityFactory::search()
                    ->setItemsPerPage(ProjectConfig::getAutocompleteItemLimit());

            if(!empty($term)){
                $citySearch->filterTermsLike('title', $term)
                        ->orderByLikeScore('title', $term);
            }

            $cities = $citySearch->select();

            $results = array();

            foreach($cities as $city){
                /*@var $city iMLSCity*/
                $name = $city->getTitle();

                $cityInfo = array(
                    'label' => $name,
                    'value' => $city->getId(),
                    'autoResult' => '<span class="result_text">'.$this->markTerm($term, $name).'</span>'
                );

                $results[] = $cityInfo;
            }

            $this->addMoreResult($citySearch, $results);

            return $results;
        }
    }

    public function actionLabourRate($attributes){
        if ((!isset($attributes['ajax']) || !$attributes['ajax'] == 1)){
            $returnArray = GI_Controller::getReturnArray();
            return $returnArray;
        }

        if(isset($attributes['curVal'])){

            $curVal = $attributes['curVal'];
            $curVals = explode(',', $curVal);

            $finalLabel = array();
            $finalValue = array();
            $finalResult = array();

            foreach($curVals as $labourRateId){
                $labourRate = LabourRateFactory::getModelById($labourRateId);
                if($labourRate){
                    $name = $labourRate->getTitle();

                    $finalLabel[] = $name;
                    $finalValue[] = $labourRateId;
                    $finalResult[] = '<span class="result_text">'.$name.'</span>';
                }
            }
            $results = array(
                'label' => $finalLabel,
                'value' => $finalValue,
                'autoResult' => $finalResult
            );
            return $results;
        } else {
            if(isset($_REQUEST['term'])){
                $term = $_REQUEST['term'];
            } else {
                $term = '';
            }

            $labourRateSearch = LabourRateFactory::search()
                    ->setItemsPerPage(ProjectConfig::getAutocompleteItemLimit());

            if(!empty($term)){
                $labourRateSearch->filterTermsLike('title', $term)
                        ->orderByLikeScore('title', $term);
            }

            $labourRates = $labourRateSearch->select();

            $results = array();

            foreach($labourRates as $labourRate){
                /*@var $labourRate AbstractLabourRate*/
                $name = $labourRate->getTitle();
                if(Permission::verifyByRef('view_labour_rate_rates')){
                    $rate = $labourRate->getRate(true);
                }
                if(Permission::verifyByRef('view_labour_rate_wages')){
                    $wage = $labourRate->getWage(true);
                }

                $wageRateString = '';
                if(isset($rate) || isset($wage)){
                    $wageRateString = '<i class="float_right right_align">';
                        if(isset($wage)){
                            $wageRateString .= 'Wage: ' . $wage;
                        }
                        if(isset($rate) && isset($wage)){
                            $wageRateString .= '<br/>';
                        }
                        if(isset($rate)){
                            $wageRateString .= 'Rate: ' . $rate;
                        }
                    $wageRateString .= '</i>';
                }

                $labourRateInfo = array(
                    'label' => $name,
                    'value' => $labourRate->getId(),
                    'autoResult' => '<span class="result_text">' . $wageRateString . $this->markTerm($term, $name) . '</span>'
                );

                $results[] = $labourRateInfo;
            }

            $this->addMoreResult($labourRateSearch, $results);

            if(isset($attributes['autocompField'])){
                $addLabourRateURLProps = array(
                    'controller' => 'labourRate',
                    'action' => 'add',
                    'ajax' => 1
                );

                $addLabourRateTitle = 'Add Labour Rate';
                if(!empty($term)){
                    $addLabourRateTitle = 'Add ' . $term;
                    $addLabourRateURLProps['title'] = $term;
                }

                $addLabourRateURL = GI_URLUtils::buildURL($addLabourRateURLProps, false, true);

                $autocompField = $attributes['autocompField'];

                $results[] = array(
                    'preventDefault' => 1,
                    'jqueryAction' => 'giModalOpenAjaxContent("' . $addLabourRateURL . '","",function(){ $("#gi_modal").data("autocomplete-field","' . $autocompField . '"); });',
                    'liClass' => 'custom_btn',
                    'hoverTitle' => 'Add Labour Rate',
                    'autoResult' => '<span class="icon_wrap"><span class="icon add"></span></span><span class="btn_text">' . $addLabourRateTitle . '</span>'
                );
            }
            return $results;
        }
    }

    public function actionUserHasLabourRate($attributes){
        if ((!isset($attributes['ajax']) || !$attributes['ajax'] == 1)){
            $returnArray = GI_Controller::getReturnArray();
            return $returnArray;
        }

        if(isset($attributes['curVal'])){

            $curVal = $attributes['curVal'];
            $curVals = explode(',', $curVal);

            $finalLabel = array();
            $finalValue = array();
            $finalResult = array();

            foreach($curVals as $userHasLabourRateId){
                $userHasLabourRate = UserHasLabourRateFactory::getModelById($userHasLabourRateId);
                if($userHasLabourRate){
                    $name = $userHasLabourRate->getTitle();

                    $finalLabel[] = $name;
                    $finalValue[] = $userHasLabourRateId;
                    $finalResult[] = '<span class="result_text">' . $name . '</span>';
                }
            }
            $results = array(
                'label' => $finalLabel,
                'value' => $finalValue,
                'autoResult' => $finalResult
            );
            return $results;
        } else {
            if(isset($_REQUEST['term'])){
                $term = $_REQUEST['term'];
            } else {
                $term = '';
            }

            $userId = '';
            if(isset($attributes['userId'])){
                $userId = $attributes['userId'];
            }

            $userHasLabourRateTable = UserHasLabourRateFactory::getDbPrefix() . 'user_has_labour_rate';

            $userHasLabourRateSearch = UserHasLabourRateFactory::search()
                    ->innerJoin('labour_rate', 'id', $userHasLabourRateTable, 'labour_rate_id', 'LR')
                    ->setItemsPerPage(ProjectConfig::getAutocompleteItemLimit());

            if(!empty($term)){
                $userHasLabourRateSearch->filterGroup()
                        ->filterTermsLike('title', $term)
                        ->orIf()
                        ->filterTermsLike('LR.title', $term)
                        ->closeGroup()
                        ->andIf()
                        ->orderByLikeScore('title', $term);
            }

            if(!empty($userId)){
                $userHasLabourRateSearch->filter('user_id', $userId);
            }

            $userHasLabourRates = $userHasLabourRateSearch->select();

            $results = array();

            foreach($userHasLabourRates as $userHasLabourRate){
                /*@var $userHasLabourRate AbstractUserHasLabourRate*/
                $name = $userHasLabourRate->getTitle();
                if(Permission::verifyByRef('view_labour_rate_rates')){
                    $rate = $userHasLabourRate->getRate(true);
                }
                if(Permission::verifyByRef('view_labour_rate_wages')){
                    $wage = $userHasLabourRate->getWage(true);
                }

                $wageRateString = '';
                if(isset($rate) || isset($wage)){
                    $wageRateString = '<i class="float_right right_align">';
                        if(isset($wage)){
                            $wageRateString .= 'Wage: ' . $wage;
                        }
                        if(isset($rate) && isset($wage)){
                            $wageRateString .= '<br/>';
                        }
                        if(isset($rate)){
                            $wageRateString .= 'Rate: ' . $rate;
                        }
                    $wageRateString .= '</i>';
                }

                $userHasLabourRateInfo = array(
                    'label' => $name,
                    'value' => $userHasLabourRate->getId(),
                    'autoResult' => '<span class="result_text">' . $wageRateString . $this->markTerm($term, $name) . '</span>'
                );

                $results[] = $userHasLabourRateInfo;
            }

            $this->addMoreResult($userHasLabourRateSearch, $results);

            if(isset($attributes['autocompField']) && !empty($userId)){
                $addLabourRateURLProps = array(
                    'controller' => 'labourRate',
                    'action' => 'addUserHasLabourRate',
                    'userId' => $userId,
                    'ajax' => 1
                );

                $addLabourRateTitle = 'Add Labour Rate';
                if(!empty($userId)){
                    $user = UserFactory::getModelById($userId);
                    if($user){
                        $addLabourRateTitle .= ' to ' . $user->getFullName();
                    }
                }

                $addLabourRateURL = GI_URLUtils::buildURL($addLabourRateURLProps, false, true);

                $autocompField = $attributes['autocompField'];

                $results[] = array(
                    'preventDefault' => 1,
                    'jqueryAction' => 'giModalOpenAjaxContent("' . $addLabourRateURL . '","",function(){ $("#gi_modal").data("autocomplete-field","' . $autocompField . '"); });',
                    'liClass' => 'custom_btn',
                    'hoverTitle' => 'Add Labour Rate',
                    'autoResult' => '<span class="icon_wrap"><span class="icon add"></span></span><span class="btn_text">' . $addLabourRateTitle . '</span>'
                );
            }

            return $results;
        }
    }

    protected function addMoreResult(GI_DataSearch $dataSearch, &$results){
        $itemsPerPage = $dataSearch->getItemsPerPage();
        $count = $dataSearch->getCount();
        if (!empty($itemsPerPage) && $count > $itemsPerPage) {
            $results[] = array(
                'preventDefault' => 1,
                'liClass' => 'more_results',
                'autoResult' => '&hellip;'
            );
        }
    }

    protected function markTerm($term, $result) {
        if (!empty($term)) {
            return preg_replace('/' . $term . '/i', "<mark>\$0</mark>", $result);
        }
        return $result;
    }

    public function actionBill($attributes) {
        if ((!isset($attributes['ajax']) || !$attributes['ajax'] == 1)){
            $returnArray = GI_Controller::getReturnArray();
            return $returnArray;
        }
        if(isset($attributes['curVal'])){
            $curVal = $attributes['curVal'];
            $curVals = explode(',', $curVal);
            $finalLabel = array();
            $finalValue = array();
            $finalResult = array();
            $finalTotal = array();
            $finalBalance = array();

            foreach ($curVals as $billId) {
                $bill = BillFactory::getModelById($billId);
                if ($bill) {
                    $billNumber = $bill->getProperty('bill_number');
                    $sortableBalance = $bill->getSortableBalance(false);
                    $sortableTotal = $bill->getSortableTotal(false);
                    $billDate = $bill->getDate(true);
                    $labelExtras = $billDate . ' Total: $' . GI_StringUtils::formatMoney($sortableTotal) . ', Balance: $' . GI_StringUtils::formatMoney($sortableBalance);
                    $finalLabel[] = $billNumber . ' - ' . $labelExtras;
                    $finalValue[] = $billId;
                    $finalResult[] = '<span class="result_text">' . $billNumber . '</span>';
                }
            }
            $results = array(
                'label' => $finalLabel,
                'value' => $finalValue,
                'autoResult' => $finalResult,
                'total'=>$finalTotal,
                'balance'=>$finalBalance
            );
            return $results;
        } else {
            if(isset($_REQUEST['term'])){
                $term = $_REQUEST['term'];
            } else {
                $term = '';
            }
            if (!isset($attributes['currencyId']) || !isset($attributes['contactId'])) {
                return array();
            }
            $currencyId = $attributes['currencyId'];
            $contactId = $attributes['contactId'];
            $withBalanceOnly = false;
            if (isset($attributes['withBalanceOnly'])) {
                if ($attributes['withBalanceOnly'] == '1') {
                    $withBalanceOnly = true;
                }
            }
            $billTableName = dbConfig::getDbPrefix() . 'bill';
            
            $billSearch = BillFactory::search()
                    ->join('item_link_to_expense', 'item_id', $billTableName, 'id', 'ilte')
                    ->filter('ilte.table_name', 'bill')
                    ->join('expense', 'id', 'ilte', 'expense_id', 'ex')
                    ->filter('ex.void', 0)
                    ->filter('ex.cancelled', 0)
                    ->setItemsPerPage(ProjectConfig::getAutocompleteItemLimit())
                    ->andIf()
                    ->filterGroup()
                    ->filterLike('bill_number', '%' . $term . '%')
                    ->orIf()
                    ->filterLike('ex.sortable_total', '%' . $term . '%')
                    ->closeGroup()
                    ->andIf()
                    ->filter('contact_id', $contactId)
                    ->filter('currency_id', $currencyId);
            if ($withBalanceOnly) {
                $billSearch->filterNULL('ex.paid_in_full');
            }
            $billSearch->orderByLikeScore('ex.sortable_total', $term)
                    ->orderByLikeScore('bill_number', $term);
            $bills = $billSearch->select();
            $results = array();
            foreach ($bills as $bill) {
                $billNumber = $bill->getProperty('bill_number');
                $sortableBalance = $bill->getSortableBalance(false);
                $sortableTotal = $bill->getSortableTotal(false);
                $billDate = $bill->getDate(true);
                $labelExtras = $billDate . ' Total: $' . GI_StringUtils::formatMoney($sortableTotal) . ', Balance: $' . GI_StringUtils::formatMoney($sortableBalance);
                $billInfo = array(
                    'label' => $billNumber . ' - ' . $labelExtras,
                    'value' => $bill->getId(),
                    'autoResult' => '<span class="result_text">' . $this->markTerm($term, $billNumber) . ' - ' . $labelExtras . '</span>',
                    'total'=>$sortableTotal,
                    'balance' => $sortableBalance,
                );

                $results[] = $billInfo;
            }
            $this->addMoreResult($billSearch, $results);
            return $results;
        }
    }

    public function actionInvoice($attributes) {
        if ((!isset($attributes['ajax']) || !$attributes['ajax'] == 1)) {
            $returnArray = GI_Controller::getReturnArray();
            return $returnArray;
        }
        if (isset($attributes['curVal'])) {
            $curVal = $attributes['curVal'];
            $curVals = explode(',', $curVal);
            $finalLabel = array();
            $finalValue = array();
            $finalResult = array();
            $finalDate = array();
            $finalTotal = array();
            $finalBalance = array();
            foreach ($curVals as $invoiceId) {
                $invoice = InvoiceFactory::getModelById($invoiceId);
                if ($invoice) {
                    $invoiceNumber = $invoice->getProperty('invoice_number');
                    $sortableTotal = $invoice->getSortableTotalFromIncome(false);
                    $sortableBalance = $invoice->getSortableBalanceFromIncome(false);
                    $invoiceDate = $invoice->getDate(true);
                    $labelExtras = $invoiceDate . ' Total: $' . GI_StringUtils::formatMoney($sortableTotal) . ', Balance: $' . GI_StringUtils::formatMoney($sortableBalance);
                    $finalLabel[] = $invoiceNumber . ' - ' . $labelExtras;
                    $finalValue[] = $invoiceId;
                    $finalResult[] = '<span class="result_text">' . $invoiceNumber . '</span>';
                    $finalDate[] = $invoiceDate;
                    $finalTotal[] = $sortableTotal;
                    $finalBalance[] = $sortableBalance;
                }
            }
            $results = array(
                'label' => $finalLabel,
                'value' => $finalValue,
                'autoResult' => $finalResult,
                'date'=>$finalDate,
                'total'=>$finalTotal,
                'balance'=>$finalBalance,
            );
            return $results;
        } else {
            if (isset($_REQUEST['term'])) {
                $term = $_REQUEST['term'];
            } else {
                $term = '';
            }
            if (!isset($attributes['currencyId']) || !isset($attributes['contactId'])) {
                return array();
            }
            $currencyId = $attributes['currencyId'];
            $contactId = $attributes['contactId'];
            $invoiceTableName = dbConfig::getDbPrefix() . 'invoice';

            $invoiceSearch = InvoiceFactory::search()
                    ->join('item_link_to_income', 'item_id', $invoiceTableName, 'id', 'ilti')
                    ->filter('ilti.table_name', 'invoice')
                   ->join('income', 'id', 'ilti', 'income_id', 'inv')
                    ->filter('inv.void', 0)
                    ->filter('inv.cancelled', 0)
                    ->setItemsPerPage(ProjectConfig::getAutocompleteItemLimit())
                    ->andIf()
                    ->filterGroup()
                    ->filterLike('invoice_number', '%' . $term . '%')
                    ->orIf()
                    ->filterLike('inv.sortable_total', '%' . $term . '%')
                    ->closeGroup()
                    ->andIf()
                    ->filter('contact_id', $contactId)
                    ->filter('currency_id', $currencyId)
                    ->orderByLikeScore('inv.sortable_total', $term)
                    ->orderByLikeScore('invoice_number', $term);
            $invoices = $invoiceSearch->select();
            $results = array();
            foreach ($invoices as $invoice) {
                $invoiceNumber = $invoice->getProperty('invoice_number');
                $invoiceDate = $invoice->getDate(true);
                $sortableTotal = $invoice->getSortableTotalFromIncome(false);
                $sortableBalance = $invoice->getSortableBalanceFromIncome(false);
                $labelExtras = $invoiceDate . ' Total: $' . GI_StringUtils::formatMoney($sortableTotal) . ', Balance: $' . GI_StringUtils::formatMoney($sortableBalance);
                $invoiceInfo = array(
                    'label' => $invoiceNumber . ' - ' . $labelExtras,
                    'value' => $invoice->getId(),
                    'autoResult' => '<span class="result_text">' . $this->markTerm($term, $invoiceNumber) . ' - ' . $labelExtras . '</span>',
                    'date'=>$invoiceDate,
                    'total'=>$sortableTotal,
                    'balance' => $sortableBalance,
                );

                $results[] = $invoiceInfo;
            }
            $this->addMoreResult($invoiceSearch, $results);
            return $results;
        }
    }

    public function actionGroupPayment($attributes) {
        $returnArray = GI_Controller::getReturnArray();
        if ((!isset($attributes['ajax']) || !$attributes['ajax'] == 1)) {
            return $returnArray;
        }

        if (isset($attributes['curVal'])) {
            $curVal = $attributes['curVal'];
            $curVals = explode(',', $curVal);
            $finalLabel = array();
            $finalValue = array();
            $finalResult = array();

            foreach ($curVals as $groupPaymentId) {
                $groupPayment = GroupPaymentFactory::getModelById($groupPaymentId);
                if ($groupPayment) {
                    $transactionNumber = $groupPayment->getProperty('transaction_number');
                    $finalLabel[] = $transactionNumber;
                    $finalValue[] = $groupPaymentId;
                    $finalResult[] = '<span class="result_text">' . $transactionNumber . '</span>';
                }
            }
            $results = array(
                'label' => $finalLabel,
                'value' => $finalValue,
                'autoResult' => $finalResult
            );
            return $results;
        } else {
            if (!isset($attributes['type']) || !isset($attributes['contactId']) || !isset($attributes['currencyId'])) {
                return $returnArray;
            }
            $type = $attributes['type'];
            $contactId = $attributes['contactId'];
            $currencyId = $attributes['currencyId'];
            if (isset($_REQUEST['term'])) {
                $term = $_REQUEST['term'];
            } else {
                $term = '';
            }
            $groupPaymentSearch = GroupPaymentFactory::search()
                    ->setItemsPerPage(ProjectConfig::getAutocompleteItemLimit())
                    ->filterLike('transaction_number', '%' . $term . '%')
                    ->filter('contact_id', $contactId)
                    ->filter('currency_id', $currencyId)
                    ->filter('default_payment_type_ref', $type)
                    ->filter('void', 0)
                    ->filter('cancelled', 0)
                    ->orderByLikeScore('transaction_number', $term);
            $groupPayments = $groupPaymentSearch->select();
            $results = array();
            if (!empty($groupPayments)) {
                foreach ($groupPayments as $groupPayment) {
                    $transactionNumber = $groupPayment->getProperty('transaction_number');
                    $date = $groupPayment->getProperty('date');
                    $amount = $groupPayment->getProperty('amount');
                    $sortableBalance = $groupPayment->getProperty('sortable_balance');
                    $labelExtras = GI_Time::formatDateForDisplay($date) . ' Total: $' . GI_StringUtils::formatMoney($amount) . ' Balance: $' . GI_StringUtils::formatMoney($sortableBalance);
                    $groupPaymentInfo = array(
                        'label'=>$transactionNumber . ' - ' . $labelExtras,
                        'value'=>$groupPayment->getProperty('id'),
                        'autoResult'=>'<span class="result_text">' . $this->markTerm($term, $transactionNumber) . ' - ' . $labelExtras . '</span>',
                    );
                    
                    $results[] = $groupPaymentInfo;
                }
            }
            $this->addMoreResult($groupPaymentSearch, $results);
            return $results;
        }
    }
    
    public function actionPricingUnit($attributes){
        if ((!isset($attributes['ajax']) || !$attributes['ajax'] == 1)){
            $returnArray = GI_Controller::getReturnArray();
            return $returnArray;
        }

        if(isset($attributes['curVal'])){

            $curVal = $attributes['curVal'];
            $curVals = explode(',', $curVal);

            $finalLabel = array();
            $finalValue = array();
            $finalResult = array();

            foreach($curVals as $pricingUnitId){
                $pricingUnit = PricingUnitFactory::getModelById($pricingUnitId);
                if($pricingUnit){
                    $abbr = $pricingUnit->getAbbr();
                    $name = $abbr . ' - ' . $pricingUnit->getTitle(true);

                    $finalLabel[] = $name;
                    $finalValue[] = $pricingUnitId;
                    $finalResult[] = '<span class="result_text">' . $name . '</span>';
                }
            }
            $results = array(
                'label' => $finalLabel,
                'value' => $finalValue,
                'autoResult' => $finalResult
            );
            return $results;
        } else {
            if(isset($_REQUEST['term'])){
                $term = $_REQUEST['term'];
            } else {
                $term = '';
            }

            $pricingUnitSearch = PricingUnitFactory::searchActive();
                    //->setItemsPerPage(ProjectConfig::getAutocompleteItemLimit());

            if(!empty($term)){
                $pricingUnitSearch->filterTermsLike('title,ref', $term)
                        ->orderByLikeScore('title,ref', $term);
            }

            $pricingUnits = $pricingUnitSearch->orderBy('pos', 'ASC')
                    ->select();

            $results = array();

            foreach($pricingUnits as $pricingUnit){
                /*@var $pricingUnit AbstractPricingUnit*/
                $abbr = $pricingUnit->getAbbr();
                $name = $abbr . ' - ' . $pricingUnit->getTitle(true);

                $pricingUnitInfo = array(
                    'label' => $name,
                    'value' => $pricingUnit->getId(),
                    'autoResult' => '<span class="result_text">' . $this->markTerm($term, $name) . '</span>'
                );

                $results[] = $pricingUnitInfo;
            }

            $this->addMoreResult($pricingUnitSearch, $results);
            /*
            if(isset($attributes['autocompField'])){
                $addLabourRateURLProps = array(
                    'controller' => 'labourRate',
                    'action' => 'add',
                    'ajax' => 1
                );

                $addLabourRateTitle = 'Add Labour Rate';
                if(!empty($term)){
                    $addLabourRateTitle = 'Add ' . $term;
                    $addLabourRateURLProps['title'] = $term;
                }

                $addLabourRateURL = GI_URLUtils::buildURL($addLabourRateURLProps, false, true);

                $autocompField = $attributes['autocompField'];

                $results[] = array(
                    'preventDefault' => 1,
                    'jqueryAction' => 'giModalOpenAjaxContent("' . $addLabourRateURL . '","",function(){ $("#gi_modal").data("autocomplete-field","' . $autocompField . '"); });',
                    'liClass' => 'custom_btn',
                    'hoverTitle' => 'Add Labour Rate',
                    'autoResult' => '<span class="icon_wrap"><span class="icon add"></span></span><span class="btn_text">' . $addLabourRateTitle . '</span>'
                );
            }
             */
            return $results;
        }
    }

}
