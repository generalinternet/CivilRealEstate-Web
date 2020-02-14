<?php

/**
 * Description of AbstractQBJournalEntryInvAdjustmentWaste
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    3.0.0
 */
abstract class AbstractQBJournalEntryInvAdjustmentWaste extends AbstractQBJournalEntry {

    /** @var AbstractInvAdjustmentWaste */
    protected $invAdjustment = NULL;

    public function getInvAdjustment() {
        if (empty($this->invAdjustment)) {
            $this->invAdjustment = InvAdjustmentFactory::getModelById($this->getProperty('item_id'));
        }
        return $this->invAdjustment;
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
                'method_name' => 'getCurrencyName'
            ),
            'adjustment_date' => array(
                'header_title' => 'Date Entered',
                'method_name' => 'getAdjustmentDate',
                'method_attributes' => array(false),
            ),
            'entered_by_user_name' => array(
                'header_title' => 'Entered By',
                'method_name' => 'getEnteredByUserName',
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
        $invAdjustment = $this->getInvAdjustment();
        if (!empty($invAdjustment)) {
            return $invAdjustment->getInvItemSKU();
        }
        return '';
    }

    public function getNameAndPackConfigContentsString() {
        $invAdjustment = $this->getInvAdjustment();
        if (!empty($invAdjustment)) {
            return $invAdjustment->getNameAndPackConfigContentsString();
        }
        return '';
    }

    public function getQty() {
        $invAdjustment = $this->getInvAdjustment();
        if (!empty($invAdjustment)) {
            return $invAdjustment->getProperty('adjustment');
        }
        return '';
    }

    public function getAdjustmentDate($formatForDisplay = false) {
        $invAdjustment = $this->getInvAdjustment();
        if (!empty($invAdjustment)) {
            return $invAdjustment->getAdjustmentDate($formatForDisplay);
        }
        return '';
    }

    public function getEnteredByUserName() {
        $invAdjustment = $this->getInvAdjustment();
        if (!empty($invAdjustment)) {
            return $invAdjustment->getEnteredByUserName();
        }
        return '';
    }

}
