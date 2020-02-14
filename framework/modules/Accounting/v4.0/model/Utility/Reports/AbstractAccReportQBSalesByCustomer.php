<?php
/**
 * Description of AbstractAccReportQBSalesByCustomer
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
use QuickBooksOnline\API\ReportService\ReportName;

abstract class AbstractAccReportQBSalesByCustomer extends AbstractAccReportQB {

    public function getTitle() {
        return 'Sales by Customer';
    }

    protected function getQBReportName() {
        return ReportName::CUSTOMERSALES;
    }
    
    public function getDescription() {
        return 'Shows total sales for each customer so you can see which ones generate the most revenue for you.';
    }
    
    public function getColour() {
        return '0087E7';
    }

    public function getInitials() {
        return 'SC';
    }

    public function isViewable() {
        if ($this->overridePermissionCheck || Permission::verifyByRef('view_sales_by_customer_report')) {
            return true;
        }
        return false;
    }

}
