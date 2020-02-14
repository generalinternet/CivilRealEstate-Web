<?php
/**
 * Description of AbstractAccReportQBCustomerBalance
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
use QuickBooksOnline\API\ReportService\ReportName;

abstract class AbstractAccReportQBCustomerBalance extends AbstractAccReportQB {

    public function getTitle() {
        return 'Customer Balance Summary';
    }

    protected function getQBReportName() {
        return ReportName::CUSTOMERBALANCE;
    }
    
    public function getDescription() {
        return 'Shows each customer’s total open balances.';
    }
    
    public function getColour() {
        return 'E70400';
    }

    public function getInitials() {
        return 'CB';
    }

    public function isViewable() {
        if ($this->overridePermissionCheck || Permission::verifyByRef('view_customer_balance_report')) {
            return true;
        }
        return false;
    }

}
