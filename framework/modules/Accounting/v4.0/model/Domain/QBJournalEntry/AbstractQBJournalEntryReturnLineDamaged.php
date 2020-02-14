<?php

/**
 * Description of AbstractQBJournalEntryReturnLineDamaged
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    3.0.0
 */
abstract class AbstractQBJournalEntryReturnLineDamaged extends AbstractQBJournalEntry {

    /** @var AbstractOrderReturnLineDamaged */
    protected $returnLine = NULL;

    /** @return AbstractOrderReturnLineDamaged */
    public function getReturnLine() {
        if (empty($this->returnLine)) {
            $this->returnLine = OrderReturnLineFactory::getModelById($this->getProperty('item_id'));
        }
        return $this->returnLine;
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
            'qty' => array(
                'header_title' => 'Qty',
                'method_name' => 'getQty',
            ),
            'expense_type' => array(
                'header_title' => 'Expense Type',
                'method_name' => 'getCategoryTitle',
            ),
            'amount' => array(
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
            'return_number' => array(
                'header_title' => 'Return #',
                'method_name' => 'getReturnNumber',
            ),
            'return_date' => array(
                'header_title' => 'Damage Recorded on Date',
                'method_name' => 'getReturnDate',
                'method_attributes' => array(false),
            ),
            'returned_by_user_name' => array(
                'header_title' => 'Return Entered By',
                'method_name' => 'getReturnedByUserName',
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
        $orderReturnLine = $this->getReturnLine();
        if (!empty($orderReturnLine)) {
            return $orderReturnLine->getInvItemSKU();
        }
        return NULL;
    }

    public function getNameAndPackConfigContentsString($includeContainerTypeTitle = false) {
        $orderReturnLine = $this->getReturnLine();
        if (!empty($orderReturnLine)) {
            return $orderReturnLine->getNameAndPackConfigContentsString($includeContainerTypeTitle);
        }
        return NULL;
    }

    public function getQty() {
        $orderReturnLine = $this->getReturnLine();
        if (!empty($orderReturnLine)) {
            return $orderReturnLine->getProperty('qty');
        }
        return NULL;
    }
    
    public function getOrderNumber() {
        $orderReturnLine = $this->getReturnLine();
        if (!empty($orderReturnLine)) {
            return $orderReturnLine->getOrderNumber();
        }
    }

    public function getReturnNumber() {
        $orderReturnLine = $this->getReturnLine();
        if (!empty($orderReturnLine)) {
            return $orderReturnLine->getReturnNumber();
        }
    }

    public function getReturnDate() {
        $orderReturnLine = $this->getReturnLine();
        if (!empty($orderReturnLine)) {
            return $orderReturnLine->getReturnDate();
        }
    }

    public function getReturnedByUserName() {
        $orderReturnLine = $this->getReturnLine();
        if (!empty($orderReturnLine)) {
            return $orderReturnLine->getReturnedByUserName();
        }
    }
}
