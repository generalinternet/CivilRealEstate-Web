<?php

require_once 'framework/core/' . FRMWK_CORE_VER . '/controller/AbstractTestController.php';

use QuickBooksOnline\API\DataService\DataService;

class testController extends AbstractTestController {

    public function actionGetGraphData($attributes) {
        $type = 'donut';
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
        }

        $graphId = 'dynamic_' . $type . '_graph';
        $jqueryCallbackAction = 'new Morris.Donut({
            element: "' . $graphId . '",
            data: [
                {value: 100, label: "Apples" },
                {value: 200, label: "Bananas" },
                {value: 300, label: "Cranberry" }
            ],
            colors: ["#3f95ff","#347ad1","#4c8ee0"],
            resize: true,
            formatter: function(x, data){
                return "$"+numberWithCommas(data.value.toFixed(2));
            }
        });
        
setTimeout(function(){
$("#' . $graphId . '").show();
}, 5000);';
        //@todo test type switches
        $returnArray = GI_Controller::getReturnArray();
        $returnArray['mainContent'] = '<div id="' . $graphId . '" class="hide_on_load"></div>';
        $returnArray['jqueryCallbackAction'] = $jqueryCallbackAction;
        return $returnArray;
    }

    public function actionTabception($attributes){
        $view = new AdminEchoView();
        $tabURLAttrs = array(
            'controller' => 'test',
            'action' => 'tabContent'
        );
        $tabAURLAttrs = $tabURLAttrs;
        $tabAURLAttrs['tabRef'] = 'A';
        $tabBURLAttrs = $tabURLAttrs;
        $tabBURLAttrs['tabRef'] = 'B';
        $tabCURLAttrs = $tabURLAttrs;
        $tabCURLAttrs['tabRef'] = 'C';
        $tabDURLAttrs = $tabURLAttrs;
        $tabDURLAttrs['tabRef'] = 'D';
        
        $tabs = array();
        $tabA = new GenericTabView('Tab A', GI_URLUtils::buildURL($tabAURLAttrs), true);
        $tabs[] = $tabA;
        $tabB = new GenericTabView('Tab B', GI_URLUtils::buildURL($tabBURLAttrs), true);
        $tabs[] = $tabB;
        $tabC = new GenericTabView('Tab C', GI_URLUtils::buildURL($tabCURLAttrs), true);
        $tabs[] = $tabC;
        $tabD = new GenericTabView('Tab D', GI_URLUtils::buildURL($tabDURLAttrs), true);
        $tabs[] = $tabD;
        
        $tabWrap = new GenericTabWrapView($tabs);
        $tabWrap->setTabWrapId('alpha');
        $view->addHTMLOverride('<div class="content_padding">');
        $view->addHTMLOverride($tabWrap->getHTMLView());
        $view->addHTMLOverride('</div>');
        
        $returnArray = $this->getReturnArray($view);
        return $returnArray;
    }
    
    public function actionTabContent($attributes){
        $view = new AdminEchoView();
        $tabRef = 'A';
        if(isset($attributes['tabRef'])){
            $tabRef = $attributes['tabRef'];
        }
        $view->addMainTitle('Tab ' . $tabRef);
        if($tabRef != 'B'){
            $view->addHTMLOverride('<p>this is just some content about tab ' . $tabRef . '</p>');
        } else {
            $tabRefs = array(
                'Apple',
                'Banana',
                'Orange',
                'Pear',
                'Grape',
                'Plum',
                'Peach',
                'Watermelon'
            );
            $tabs = array();
            foreach($tabRefs as $i => $tabRef){
                $tabURLAttrs = array(
                    'controller' => 'test',
                    'action' => 'tabContent',
                    'tabRef' => $tabRef
                );
                $tab = new GenericTabView('Tab ' . $tabRef, GI_URLUtils::buildURL($tabURLAttrs), true);
                if($i == 2){
                    $tab->setCurrent(true);
                }
                $tabs[] = $tab;
            }
            
            $tabWrap = new GenericTabWrapView($tabs);
            $tabWrap->setTabWrapId('fruit');
            $view->addHTMLOverride($tabWrap->getHTMLView());
        }
        
        $returnArray = $this->getReturnArray($view);
        return $returnArray;
    }
    
    //Example of using TransactionList to get data about imported payments from QBO
    public function actionQB() {
        $dataService = QBConnection::getInstance();
        $reportService = new \QuickBooksOnline\API\ReportService\ReportService($dataService->getServiceContext());
        $reportService->setStartDate("2019-01-01");
        $reportService->setEndDate("2019-02-28");
        $reportService->setTransactionType('BillPaymentCheck');
        try {
            $transactionList = $reportService->executeReport("TransactionList");
            print_r('<pre>');
            var_dump($transactionList);
        } catch (Exception $ex) {
            print_r($ex->getMessage());
        }
        
        die();
    }
    
    public function actionQBInvoice($attributes) {
        $dataService = QBConnection::getInstance();
        $query = "Select * from Invoice where id='636'";
        try {
           $results = $dataService->Query($query);
           print_r('<pre>');
           var_dump($results);
        } catch (Exception $ex) {
            print_r($ex->getMessage());
        }
        die();
    } 

    public function actionCreateInvHistoryBaseline($attributes) {
        if (!DEV_MODE) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $warehouses = ContactFactory::search()
                ->filterByTypeRef('warehouse')
                ->select();

        $packConfigs = InvPackConfigFactory::search()
                ->select();
        foreach ($warehouses as $warehouse) {
            foreach ($packConfigs as $packConfig) {
                if (!empty($warehouse)) {
                    InvHistLineFactory::createSystemStockCountAdjustment($packConfig, $warehouse);
                }
            }
        }
        print_r('DONE.');
        die();
    }
    
    public function actionClearCache($attributes) {
        if (!(DEV_MODE)) {
            GI_URLUtils::redirectToAccessDenied();
        }
        if (apcu_clear_cache()) {
            print_r('CLEAR!');
        } else {
            print_r('NOT CLEAR!');
        }
        die();
    }
    
//    public function actionReport($attributes) {
//        $startDate = new DateTime('2018-06-01');
//        $endDate = new DateTime('2019-05-31');
//        $report = AccReportFactory::buildReportObject('order_values', $startDate, $endDate);
//        //$report->buildReport();
//        $report->testDates();
//        die();
//        
//    }
//    
//    public function actionWidget($attributes) {
//        $widget = WidgetService::getDashboardWidget('ytd_profit_loss');
//        $widget->setUseBodyPlaceholder(false);
//        $returnArray = GI_Controller::getReturnArray($widget);
//        return $returnArray;
//    }

    public function actionRole($attributes) {
        $keyPrefix = '';
        if (DEV_MODE) {
            $keyPrefix = ProjectConfig::getProjectBase();
        }
        if (apcu_exists($keyPrefix . '_role_perms')) {
            $allRolePermsBefore = apcu_fetch($keyPrefix . '_role_perms');
        } else {
            $allRolePermsBefore = array();
        }
        $role = RoleFactory::getModelById(1); //Super Admin
        
        if (!$role->clearCachedPermissions()) {
            print_r('CANNOT CLEAR CACHE<br>');
        }
        if (apcu_exists($keyPrefix . '_role_perms')) {
            $allRolePermsAfter = apcu_fetch($keyPrefix . '_role_perms');
        } else {
            $allRolePermsAfter = array();
        }
        
        
        print_r('<pre>');
        var_dump($allRolePermsBefore);
        print_r('*************<br>');
        var_dump($allRolePermsAfter);
        die();
    }

    public function actionUser($attributes) {
        $keyPrefix = '';
        if (DEV_MODE) {
            $keyPrefix = ProjectConfig::getProjectBase();
        }
        if (apcu_exists($keyPrefix . '_user_perms')) {
            $allUserPermsBefore = apcu_fetch($keyPrefix . '_user_perms');
        } else {
            $allUserPermsBefore = array();
        }
        $user = UserFactory::getModelById(4); //Nobody Special

        if (!$user->clearCachedPermissions()) {
            print_r('CANNOT CLEAR CACHE<br>');
        }
        if (apcu_exists($keyPrefix . '_user_perms')) {
            $allUserPermsAfter = apcu_fetch($keyPrefix . '_user_perms');
        } else {
            $allUserPermsAfter = array();
        }


        print_r('<pre>');
        var_dump($allUserPermsBefore);
        print_r('*************<br>');
        var_dump($allUserPermsAfter);
        die();
    }
    
    public function actionImport($attributes) {
        $dataService = QBConnection::getInstance();
        $query = "SELECT * from Customer StartPosition 1 MaxResults 1000";
        try {
           $results = $dataService->Query($query);
           print_r('<pre>');
           var_dump($results);
        } catch (Exception $ex) {
            print_r($ex->getMessage());
        }
        die();
    }
    
    public function actionMstone($attributes) {
        $invoiceLine = InvoiceLineFactory::getModelById(36);
        $uiTableCols = InvoiceProjectMstone::getInvoiceTableUITableCols('milestone');
        $uiTableView = new InvoiceProjectMstoneTableView(array($invoiceLine), $uiTableCols);
        $singleLineHTML = $uiTableView->buildSingleRow($invoiceLine);
        $preHTML = '<table class="ui_table"><tbody>';
        $postHTML = '</tbody></table>';
        $html = $preHTML . $singleLineHTML . $postHTML;
        echo $html;
        
        die();

    }
    
    public function actionCharity($attibutes){
        if(!DEV_MODE){
            exit('oops');
        }
        $apiKey = CHARITY_NAVIGATOR_API_KEY;
        $appId = CHARITY_NAVIGATOR_APP_ID;
        
        $baseURL = 'https://api.data.charitynavigator.org/v2';
        
        $endPoint = 'Organizations';
        
        $pageNumber = 1;
        if(isset($attibutes['pageNumber'])){
            $pageNumber = $attibutes['pageNumber'];
        }
        
        //organization params
        $queryParams = array(
            'app_id' => $appId,
            'app_key' => $apiKey,
            'pageSize' => 1000, //between 1 and 1000
            'pageNum' => $pageNumber,
            'search' => '', //whitespace separated terms (searches all text properties by default)
//            'searchType' => 'DEFAULT', //DEFAULT or NAME_ONLY (to search only name property)
            'rated' => 1, //whether to return only rated or unrated charities
//            'categoryID' => '', //id of a category
//            'causeID' => '', //id of a cause
//            'state' => '', //2 letter state code
//            'city' => '',
//            'zip' => '',
//            'minRating' => 0, //integer between 0 and 4
//            'maxRating' => 4, //integer between 0 and 4
            'scopeOfWork' => 'ALL', //ALL, REGIONAL, NATIONAL, OR INTERNATIONAL
            'sort' => 'NAME:ASC' //NAME, RATING, RELEVANCE            
        );
        
        $finalURL = sprintf("%s?%s", $baseURL . '/' . $endPoint, http_build_query($queryParams));
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $finalURL);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        if(!$result){
            die('Connection Failure');
        }
        curl_close($curl);
        $data = json_decode($result);
        
        $updater = new DBDirectUpdater('charity');
        $categoryUpdater = new DBDirectUpdater('charity_category');
        $causeUpdater = new DBDirectUpdater('charity_cause');
        
//        echo '<pre>';
//        print_r($data);
//        echo '</pre>';
//        exit();
        foreach($data as $charity){
            $ein = $charity->ein;
            if(empty($ein)){
                continue;
            }
            $props = array(
                'name' => $charity->charityName,
                'cn_url' => $charity->charityNavigatorURL,
                'website' => $charity->websiteURL,
                'tag_line' => $charity->tagLine,
                'mission' => $charity->mission
            );
            if(isset($charity->mailingAddress) && !empty($charity->mailingAddress)){
                $props['addr_street'] = $charity->mailingAddress->streetAddress1;
                $props['addr_street_two'] = $charity->mailingAddress->streetAddress2;
                $props['addr_city'] = $charity->mailingAddress->city;
                $props['addr_region'] = $charity->mailingAddress->stateOrProvince;
                $props['addr_code'] = $charity->mailingAddress->postalCode;
                $props['addr_country'] = $charity->mailingAddress->country;
            }
            if(isset($charity->category) && !empty($charity->category)){
                $cnCatId = $charity->category->categoryID;
                $categoryProps = array(
                    'name' => $charity->category->categoryName,
                    'cn_url' => $charity->category->charityNavigatorURL,
                    'cn_image' => $charity->category->image,
                );
                
                $categoryId = $categoryUpdater->save($categoryProps, $cnCatId, 'cn_id');
                
                if(!empty($categoryId)){
                    $props['charity_category_id'] = $categoryId;
                }
            }
            if(isset($charity->cause) && !empty($charity->cause)){
                $cnCauseId = $charity->cause->causeID;
                $causeProps = array(
                    'name' => $charity->cause->causeName,
                    'cn_url' => $charity->cause->charityNavigatorURL,
                    'cn_image' => $charity->cause->image,
                );
                
                $causeId = $causeUpdater->save($causeProps, $cnCauseId, 'cn_id');
                
                if(!empty($causeId)){
                    $props['charity_cause_id'] = $causeId;
                }
            }
            if(isset($charity->currentRating) && !empty($charity->currentRating)){
                $props['rating'] = $charity->currentRating->rating;
            }
            
            $updater->save($props, $ein, 'ein');
        }
        exit('done');
    }
    
}
