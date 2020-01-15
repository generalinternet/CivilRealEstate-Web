<?php
/**
 * Description of AbstractRuleGroupSalesOrder
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    2.0.0
 */
abstract class AbstractRuleGroupSalesOrder extends AbstractRuleGroup {

    protected static $subjectPropertyOptions = array(
        'subtotal'=>array(
            'title'=>'Order Subtotal',
            'method_name'=>'getSalesOrderSubtotal'
        ),
        'client_outstanding_balance'=>array(
            'title'=>'Client Outstanding Invoice Balance',
            'method_name'=>'getClientOutstandingInvoiceBalance'
        ),
        'stock_required_to_fill_order'=>array(
            'title'=>'Stock Required To Fill Order',
            'method_name'=>'getStockRequiredForOrderQuantities'
        ),
        'available_stock_in_inventory'=>array(
            'title'=>'Available Stock in Inventory',
            'method_name'=>'getStockAvailableQuantities',
        ),
    );

    /**
     * @return AbstractOrderSales
     */
    public function getSubjectModel() {
        return $this->subjectModel;
    }

    /**
     * @param AbstractOrderSales $model
     */
    public function setSubjectModel(GI_Model $model) {
        $this->subjectModel = $model;
    }
    
    public function getSalesOrderSubtotal() {
        $salesOrder = $this->getSubjectModel();
        if (empty($salesOrder)) {
            return NULL;
        }
        return $salesOrder->getSubtotal();
    }
    
    public function getClientOutstandingInvoiceBalance() {
        $salesOrder = $this->getSubjectModel();
        if (empty($salesOrder)) {
            return NULL;
        }
        $client = $salesOrder->getContact();
        if (empty($client)) {
            return NULL;
        }
        return $client->getOutstandingFinalizedInvoiceBalance();
    }

    public function getStockRequiredForOrderQuantities() {
        $salesOrder = $this->getSubjectModel();
        if (empty($salesOrder)) {
            return NULL;
        }
        $array = array();
        $lines = $salesOrder->getLines();
        if (!empty($lines)) {
            foreach ($lines as $line) {
                $array[$line->getProperty('id')] = $line->getQty();
            }
        }
        return $array;
    }

    public function getStockAvailableQuantities() {
        $salesOrder = $this->getSubjectModel();
        if (empty($salesOrder)) {
            return NULL;
        }
        $array = array();
        $lines = $salesOrder->getLines();
        if (!empty($lines)) {
            foreach ($lines as $line) {
                $array[$line->getProperty('id')] = InvStockFactory::getPotentialStockForLineCount($line, $line->getPackageConfig(), true);
            }
        }
        return $array;
    }

}
