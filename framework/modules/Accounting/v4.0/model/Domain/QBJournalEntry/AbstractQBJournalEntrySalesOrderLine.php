<?php

/**
 * Description of AbstractQBJournalEntrySalesOrderLine
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    3.0.0
 */
 abstract class AbstractQBJournalEntrySalesOrderLine extends AbstractQBJournalEntry {
     
     /** @var AbstractOrderLineSales */
     protected $salesOrderLine = NULL;
     
     /** @return AbstractOrderLineSales */
     public function getSalesOrderLine() {
        if (empty($this->salesOrderLine)) {
            $this->salesOrderLine = OrderLineFactory::getModelById($this->getProperty('item_id'));
        }
        return $this->salesOrderLine;
    }

    public static function getCSVExportUITableCols() {
        $tableColArrays = array(
            'sku' => array(
                'header_title' => 'SKU',
                'method_name' => 'getInvItemSKU',
            ),
            'name' => array(
                'header_title' => 'Item: Item Package',
                'method_name' => 'getNameAndPackConfigContentsString',
                'method_attributes'=>array(true),
            ),
            'qty_exported' => array(
                'header_title' => 'Qty',
                'method_name' => 'getQuickbooksExportedQty',
            ),
            'expense_type'=>array(
                'header_title'=>'Expense Type',
                'method_name'=>'getCategoryTitle',
            ),
            'cogs_exported' => array(
                'header_title' => 'Net Amount',
                'method_attributes' => 'amount',
            ),
            'currency' => array(
                'header_title' => 'Currency',
                'method_name' => 'getCurrencyName',
            ),
            'so_number' => array(
                'header_title' => 'Sales Order #',
                'method_name' => 'getOrderNumber',
            ),
            'shipment_number' => array(
                'header_title' => 'Shipment #',
                'method_name' => 'getShipmentNumber',
            ),
            'shipped_by_user_name' => array(
                'header_title' => 'Shipped By',
                'method_name' => 'getShippedByUserName',
            ),
            'invoice_number_exported' => array(
                'header_title' => 'Invoice #',
                'method_name' => 'getInvoiceNumber',
                'method_attributes' => array(false),
            ), 
                );


        $UITableCols = array();
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        $parentUITableCols = parent::getCSVExportUITableCols();
        if (!empty($parentUITableCols)) {
            return array_merge($UITableCols, $parentUITableCols);
        }
        return $UITableCols;
    }
    
    public function getInvItemSKU() {
        $salesOrderLine = $this->getSalesOrderLine();
        if (!empty($salesOrderLine)) {
            return $salesOrderLine->getInvItemSKU();
        }
        return '';
    }

    public function getNameAndPackConfigContentsString($includeContainerTypeTitle = false) {
        $salesOrderLine = $this->getSalesOrderLine();
        if (!empty($salesOrderLine)) {
            return $salesOrderLine->getNameAndPackConfigContentsString($includeContainerTypeTitle);
        }
        return '';
    }

    public function getQuickbooksExportedQty() {
        $salesOrderLine = $this->getSalesOrderLine();
        if (!empty($salesOrderLine)) {
            return $salesOrderLine->getQuickbooksExportedQty();
        }
        return '';
    }

    public function getOrderNumber() {
        $salesOrderLine = $this->getSalesOrderLine();
        if (!empty($salesOrderLine)) {
            return $salesOrderLine->getOrderNumber();
        }
        return '';
    }

    public function getShipmentNumber() {
        $salesOrderLine = $this->getSalesOrderLine();
        if (!empty($salesOrderLine)) {
            return $salesOrderLine->getShipmentNumber();
        }
        return '';
    }

    public function getShippedByUserName() {
        $salesOrderLine = $this->getSalesOrderLine();
        if (!empty($salesOrderLine)) {
            return $salesOrderLine->getShippedByUserName();
        }
        return '';
    }

    public function getInvoiceNumber() {
        $salesOrderLine = $this->getSalesOrderLine();
        if (!empty($salesOrderLine)) {
            return $salesOrderLine->getInvoiceNumber();
        }
        return '';
    }

}
