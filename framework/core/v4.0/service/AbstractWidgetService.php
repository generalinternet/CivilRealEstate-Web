<?php
/**
 * Description of AbstractWidgetService
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractWidgetService extends GI_Service {
    
    //master list of all
    protected static $dashboardWidgetRefs = array(
        'recent_activity_table',
        'po_table',
        'receiving_table',
        'so_table',
        'shipping_table',
        //'con_event_hist_table',
        'con_client_event_hist_table',
        'ytd_sales',
        'ytd_profit_loss',
        'active_users_table',
        'purchase_order_values_table',
        'sales_order_values_table',
        'my_sales_table',
        'sales_by_salesperson_table',
        'ap_ar_table',
    ); 
    protected static $dashboardWidgets = array();
    protected static $dashboardIndexWidgetColumnCount = 3; //Max 6
    protected static $dashboardWidgetMaxTableRows = 5;
    protected static $defaultChartColours = array(
        '3f95ff',
        '347ad1',
        '4c8ee0',
        '27487c',
        '1a64db',
        '9abcf4',
    );

    public static function getDashboardWidgets() {
        foreach (static::$dashboardWidgetRefs as $ref) {
            static::buildDashboadWidget($ref);
        }
        return static::$dashboardWidgets;
    }
    
    public static function getDashboardWidget($ref) {
        if (!isset(static::$dashboardWidgets[$ref])) {
            static::buildDashboadWidget($ref);
        }
        if (isset(static::$dashboardWidgets[$ref])) {
            return static::$dashboardWidgets[$ref];
        }
        return NULL;
    }
    
    public static function getDashboardIndexWidgetColumnCount() {
        $numOfCols = static::$dashboardIndexWidgetColumnCount;
        if ($numOfCols > 6) {
            $numOfCols = 6;
        }
        return $numOfCols;
    }
    
    public static function getDashboardWidgetMaxTableRows() {
        return static::$dashboardWidgetMaxTableRows;
    }
    
    public static function getDefaultChartColours() {
        return static::$defaultChartColours;
    }

    protected static function buildDashboadWidget($ref) {
        if (isset(static::$dashboardWidgets[$ref])) {
            return static::$dashboardWidgets[$ref];
        }
        $widget = NULL;
        switch ($ref) {
            case 'po_table':
                $widget = new DashboardPOTableWidgetView($ref);
                break;
            case 'receiving_table':
                $widget = new DashboardReceivingTableWidgetView($ref);
                break;
            case 'shipping_table':
                $widget = new DashboardShippingTableWidgetView($ref);
                break;
            case'so_table':
                $widget = new DashboardSOTableWidgetView($ref);
                break;
            case 'con_event_hist_table':
                $widget = new DashboardContactEventHistoryTableWidgetView($ref);
                break;
            case 'con_client_event_hist_table':
                $widget = new DashboardContactClientEventHistoryTableWidgetView($ref);
                break;
            case 'ytd_sales':
                $widget = new DashboardYTDSalesChartWidgetView($ref);
                $widget->setColours(static::getDefaultChartColours());
                break;
            case 'ytd_profit_loss':
                $widget = new DashboardYTDProfitLossChartWidgetView($ref);
                $widget->setColours(static::getDefaultChartColours());
                break;
            case 'active_users_table':
                $widget = new DashboardActiveUsersTableWidgetView($ref);
                break;
            case 'recent_activity_table':
                $widget = new DashboardRecentActivityTableWidgetView($ref);
                break;
            case 'purchase_order_values_table':
                $widget = new DashboardPurchaseOrderValuesTableWidgetView($ref);
                break;
            case 'sales_order_values_table':
                $widget = new DashboardSalesOrderValuesTableWidgetView($ref);
                break;
            case 'my_sales_table':
                $widget = new DashboardMySalesTableWidgetView($ref);
                break;
            case'sales_by_salesperson_table':
                $widget = new DashboardSalesBySalespersonTableWidgetView($ref);
                break;
            case'ap_ar_table':
                $widget = new DashboardAPARTableWidgetView($ref);
                break;
            default:
                break;
        }


        if (!empty($widget) && $widget->isViewable()) {
            static::$dashboardWidgets[$ref] = $widget;
        }
        return $widget;
    }
    
}
