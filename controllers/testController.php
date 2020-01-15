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
    
}
