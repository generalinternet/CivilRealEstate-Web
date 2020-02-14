<?php
/**
 * Description of AbstractAccReportFactory
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.1
 */
abstract class AbstractAccReportFactory {

    protected static $reportTypes = array(
      //  'overview',
        'profit_and_loss',
        'ar_aging_summary',
        'customer_balance',
        'sales_by_sku',
        'sales_by_customer',
        'sales_by_salesperson',
        'ap_aging_summary',
        'inv_cogs_sales',
        'order_values',
        'sales_by_region',
        'sales_comparison',
    );
    
    /**
     * @param String $type
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @return AbstractAccReport
     */
    public static function buildReportObject($type, DateTime $startDate, DateTime $endDate, $overridePermissionCheck = false) {
        $report = NULL;
        switch ($type) {
            case 'overview':
                $report = new AccReportOverview($type, $startDate, $endDate);
                break;
            case 'profit_and_loss';
                $report = new AccReportQBProfitAndLoss($type, $startDate, $endDate);
                break;
            case 'ar_aging_summary':
                $report = new AccReportQBArAgingSummary($type, $startDate, $endDate);
                break;
            case 'customer_balance':
                $report = new AccReportQBCustomerBalance($type, $startDate, $endDate);
                break;
            case 'sales_by_sku':
                $report = new AccReportSalesBySKU($type, $startDate, $endDate);
                break;
            case 'sales_by_customer':
                $report = new AccReportQBSalesByCustomer($type, $startDate, $endDate);
                break;
            case 'sales_by_salesperson':
                $report = new AccReportSalesBySalesperson($type, $startDate, $endDate);
                break;
            case 'ap_aging_summary':
                $report = new AccReportQBApAgingSummary($type, $startDate, $endDate);
                break;
            case 'inv_cogs_sales':
                $report = new AccReportInvCogsSales($type, $startDate, $endDate);
                break;
            case 'order_values':
                $report = new AccReportOrderValues($type, $startDate, $endDate);
                break;
            case 'sales_by_region':
                $report = new AccReportSalesByRegion($type, $startDate, $endDate);
                break;
            case 'sales_comparison':
                $report = new AccReportSalesComparison($type, $startDate, $endDate);
                break;
            default:
                $report = NULL;
                break;
        }
        if (!empty($report)) {
            $report->setOverridePermissionCheck($overridePermissionCheck);
            if (!$report->isViewable()) {
                $report = NULL;
            }
        }

        return $report;
    }

    /**
     * @return String[]
     */
    public static function getTypesArray() {
        $types = array();
        foreach (static::$reportTypes as $reportType) {
            if (Permission::verifyByRef('view_' . $reportType . '_report')) {
                $types[] = $reportType;
            } 
        }
        return $types;
    }
    
    /**
     * @return AbstractAccReport[] One report of each type
     */
    public static function buildReportsArray(DateTime $startDate, DateTime $endDate) {
        $reports = array();
       // $types = static::$reportTypes;
        $types = static::getTypesArray();
        if (!empty($types)) {
            foreach ($types as $type) {
                $reports[$type] = static::buildReportObject($type, $startDate, $endDate);
            }
        }
        return $reports;
    }
    
    

}
