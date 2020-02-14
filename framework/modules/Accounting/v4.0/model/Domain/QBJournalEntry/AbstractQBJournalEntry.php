<?php

/**
 * Description of AbstractQBJournalEntry
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.2
 */
abstract class AbstractQBJournalEntry extends GI_Model {
    
    protected $currency = NULL;
    protected $creditAccount = NULL;
    protected $debitAccount = NULL;
    protected $category = NULL;
    protected $deleteLiveJEOnDelete = true;


    public function getCurrency() {
        if (empty($this->currency)) {
            $this->currency = CurrencyFactory::getModelById($this->getProperty('currency_id'));
        }
        return $this->currency;
    }
    
    public function getCurrencyName() {
        $currency = $this->getCurrency();
        if (!empty($currency)) {
            return $currency->getProperty('name');
        }
        return '';
    }
    
    public function getCategory() {
        if (empty($this->category)) {
            $this->category = QBJournalEntryCatFactory::getModelById($this->getProperty('qb_journal_entry_cat_id'));
        }
        return $this->category;
    }
    
    public function getCategoryTitle() {
        $category = $this->getCategory();
        if (!empty($category)) {
            return $category->getProperty('title');
        }
        return '';
    }
    
    public function setDeleteLiveJEOnDelete($deleteLive) {
        $this->deleteLiveJEOnDelete = $deleteLive;
    }

    public function exportToQB() {
        $dataService = QBConnection::getInstance();
        if (empty($dataService)) {
            return false;
        }
        $currency = $this->getCurrency();
        $defaultCurrency = CurrencyFactory::getDefaultCurrency();
        $exRate = CurrencyFactory::determineConversionRate($currency, $defaultCurrency);
        if (empty($exRate)) {
            $exRate = 1;
        }
        $value = $this->getProperty('amount');
        if ($value < 0) {
            $value = $this->reverseNegativeValue();
        }

        $memo = $this->getProperty('memo');
            $properties = array(
                'CurrencyRef' => array(
                    'value' => $currency->getProperty('name')
                ),
                'ExchangeRate' => $exRate,
                'TxnDate'=>$this->getProperty('date'),
                'Line' => array(
                    array(
                        'DetailType' => 'JournalEntryLineDetail',
                        'Description' => $memo,
                        'Amount' => $value,
                        'JournalEntryLineDetail' => array(
                            'PostingType' => 'Credit',
                            'AccountRef' => array(
                                'value' => $this->getProperty('cred_acct_qb_id')
                            ),
                        ),
                    ),
                    array(
                        'DetailType' => 'JournalEntryLineDetail',
                        'Description' => $memo,
                        'Amount' => $value,
                        'JournalEntryLineDetail' => array(
                            'PostingType' => 'Debit',
                            'AccountRef' => array(
                                'value' => $this->getProperty('debit_acct_qb_id')
                        ),
                    ),
                ),
            ),
        );
        try {
            $qbJournalEntry = QuickBooksOnline\API\Facades\JournalEntry::create($properties);
            $resultingObject = $dataService->Add($qbJournalEntry);
            $error = $dataService->getLastError();
            if (!empty($error)) {
                return false;
            }
        } catch (Exception $ex) {
            return false;
        }
        $qbId = $resultingObject->Id;
        $this->setProperty('qb_id', $qbId);

        if (!$this->save()) {
            return false;
        }
        return true;
    }

    public function updateInQB() {
        $dataService = QBConnection::getInstance();
        if (empty($dataService)) {
            return false;
        }
        $syncToken = $this->getSyncTokenFromQB();
        if (is_null($syncToken)) {
            return false;
        }

        $value = $this->getProperty('amount');
        if ($value < 0) {
            $value = $this->reverseNegativeValue();
        }
        $memo = $this->getProperty('memo');
        $currency = $this->getCurrency();
        $properties = array(
            'Id' => $this->getProperty('qb_id'),
            'SyncToken' => $syncToken,
            'sparse' => true,
            'CurrencyRef' => array(
                'value' => $currency->getProperty('name')
            ),
            'Line' => array(
                array(
                    'DetailType' => 'JournalEntryLineDetail',
                    'Description' => $memo,
                    'Amount' => $value,
                    'JournalEntryLineDetail' => array(
                        'PostingType' => 'Credit',
                        'AccountRef' => array(
                            'value' => $this->getProperty('cred_acct_qb_id')
                        ),
                    ),
                ),
                array(
                    'DetailType' => 'JournalEntryLineDetail',
                    'Description' => $memo,
                    'Amount' => $value,
                    'JournalEntryLineDetail' => array(
                        'PostingType' => 'Debit',
                        'AccountRef' => array(
                            'value' => $this->getProperty('debit_acct_qb_id')
                        ),
                    ),
                ),
            ),
        );

        try {
            $qbJournalEntry = QuickBooksOnline\API\Facades\JournalEntry::create($properties);
            $resultingObject = $dataService->Update($qbJournalEntry);
            $error = $dataService->getLastError();
            if (!empty($error)) {
                return false;
            }
        } catch (Exception $ex) {
            return false;
        }

        $qbId = $resultingObject->Id;
        $this->setProperty('qb_id', $qbId);

        if (!$this->save()) {
            return false;
        }
        return true;
    }

    protected function reverseNegativeValue() {
        $value = $this->getProperty('amount');
        if ($value >= 0) {
            return $value;
        }
        $origCreditAccountQBId = $this->getProperty('cred_acct_qb_id');
        $origDebitAccountQBId = $this->getProperty('debit_acct_qb_id');
        $newValue = abs($value);
        $this->setProperty('cred_acct_qb_id', $origDebitAccountQBId);
        $this->setProperty('debit_acct_qb_id', $origCreditAccountQBId);
        $this->setProperty('amount', $newValue);
        return $newValue;
    }

    protected function getSyncTokenFromQB() {
        $dataService = QBConnection::getInstance();
        if (empty($dataService)) {
            return NULL;
        }
        $query = "Select SyncToken from journalentry where Id='" . $this->getProperty('qb_id') . "'";
        try {
            $results = $dataService->Query($query);
            $error = $dataService->getLastError();
            if (!empty($error)) {
                return false;
            }
            $qbObj = $results[0];
            return $qbObj->SyncToken;
        } catch (Exception $ex) {
            return false;
        }
        return NULL;
    }

    public static function getCSVExportUITableCols() {
        $tableColArrays = array(
            'exported_date' => array(
                'header_title' => 'Exported Date',
                'method_name' => 'getExportedDate',
                'method_attributes'=>array(true),
            ),
            'credit_account' => array(
                'header_title' => 'Credit Account',
                'method_name' => 'getCreditAccountName',
            ),
            'debit_account' => array(
                'header_title' => 'Debit Account',
                'method_name' => 'getDebitAccountName',
            ),
            'je_date' => array(
                'header_title' => 'Journal Entry Date',
                'method_name' => 'getJournalEntryDate',
                'method_attributes'=>array(true),
            ),
            'memo' => array(
                'header_title' => 'Memo',
                'method_name' => 'getMemo',
            ),
        );
        $UITableCols = array();
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UITableCols;
    }
    
    public function getExportedDate($formatForDisplay = false) {
        $date = $this->getProperty('inception');
        if ($formatForDisplay) {
            $date = GI_Time::formatDateForDisplay($date);
        }
        return $date;
    }
    
    public function getJournalEntryDate($formatForDisplay = false) {
        $date = $this->getProperty('date');
        if ($formatForDisplay) {
            $date = GI_Time::formatDateForDisplay($date);
        }
        return $date;
    }
    
    public function getCreditAccountName() {
        $creditAccount = $this->getQBCreditAccount();
        if (!empty($creditAccount)) {
            return $creditAccount->getProperty('name');
        }
        return '';
    }
    
    public function getDebitAccountName() {
        $debitAccount = $this->getQBDebitAccount();
        if (!empty($debitAccount)) {
            return $debitAccount->getProperty('name');
        }
        return '';
    }

    public function getMemo() {
        return $this->getProperty('memo');
    }

    protected function getQBCreditAccount() {
        if (empty($this->creditAccount)) {
            $this->creditAccount = QBAccountFactory::getModelByQBId($this->getProperty('cred_acct_qb_id'));
        }
        return $this->creditAccount;
    }

    protected function getQBDebitAccount() {
        if (empty($this->debitAccount)) {
            $this->debitAccount = QBAccountFactory::getModelByQBId($this->getProperty('debit_acct_qb_id'));
        }
        return $this->debitAccount;
    }
    
    public function softDelete() {
        if ($this->deleteLiveJEOnDelete) {
            $qbConnection = QBConnection::getInstance();
            if (empty($qbConnection)) {
                return false;
            }
            try {
                $je = QuickBooksOnline\API\Facades\JournalEntry::create(array(
                            "Id" => $this->getProperty('qb_id'),
                            "SyncToken" => 0
                ));
                $resultObj = $qbConnection->Delete($je);
            } catch (Exception $ex) {
                return false; //TODO - error message
            }

            $error = $qbConnection->getLastError();
            if (!$error) {
                return parent::softDelete();
            }
        } else {
            return parent::softDelete();
        }

        return false;
    }

}
