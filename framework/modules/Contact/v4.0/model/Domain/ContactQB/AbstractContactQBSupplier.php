<?php
/**
 * Description of AbstractContactQBSupplier
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    3.0.1
 */
abstract class AbstractContactQBSupplier extends AbstractContactQB {

    protected $tableWrapId = 'contact_qb_supplier_table';
    protected static $searchFormId = 'contact_qb_supplier_search';
    
    protected static $compatibleContactCatTypeRefs = array(
        'vendor'
    );

    public function getViewTitle($plural = true) {
        $title = 'Quickbooks Supplier';
        if ($plural) {
            $title .= 's';
        }
        return $title;
    }

    public function getAPITableName() {
        return 'Vendor';
    }

    protected function setPropertiesFromQBObject($qbObject) {
        if (!parent::setPropertiesFromQBObject($qbObject)) {
            return false;
        }
        return true;
    }

    public function isSupplier() {
        return true;
    }

    public function exportToQB() {
        if (!Permission::verifyByRef('export_contacts_to_quickbooks')) {
            return false;
        }
        $properties = $this->getQuickbooksExportPropertiesArray();
        if (empty($properties)) {
            return false;
        }
        $dataService = QBConnection::getInstance();
        if (empty($dataService)) {
            return false;
        }
        $qbId = $this->getQuickbooksId();

        try {
            if (empty($qbId)) {
                $resourceObject = QuickBooksOnline\API\Facades\Vendor::create($properties);
                $resultingObject = $dataService->Add($resourceObject);
            } else {
                $resourceObject = $dataService->FindById('Vendor', $qbId);
                $properties['Id'] = $qbId;
                $properties['sparse'] = true;
                $resourceObject = QuickBooksOnline\API\Facades\Vendor::update($resourceObject, $properties);
                $resultingObject = $dataService->Update($resourceObject);
            }
            $error = $dataService->getLastError();
            if (!$error) {
                $updatedQBId = $resultingObject->Id;
                if (empty($qbId)) {
                    $this->setProperty('qb_id', $updatedQBId);
                }
                $this->setProperty('display_name', $resultingObject->DisplayName);
                $this->setProperty('print_on_cheque_name', $resultingObject->PrintOnCheckName);
                if (!($this->save() && $this->updateContactsAfterImportOrExport())) {
                    return false;
                }
                return true;
            } else {
                if (empty($this->getProperty('qb_id'))) {
                    $this->softDelete();
                }
                GI_URLUtils::redirectToQBError($error);
            }
        } catch (Exception $ex) {
            if (empty($this->getProperty('qb_id'))) {
                $this->softDelete();
            }
            GI_URLUtils::redirectToError(6000, $ex->getMessage());
        }
        return false;
    }

    public function getQuickbooksObject() {
        if (empty($this->quickbooksObject)) {
            $quickbooksId = $this->getQuickbooksId();
            $dataService = QBConnection::getInstance();
            if (!empty($quickbooksId) && !empty($dataService)) {
                $apiQuery = "SELECT * from Vendor";
                $apiQuery .= " where id='" . $quickbooksId . "'";
                $customerArray = $dataService->Query($apiQuery);
                if (!empty($customerArray)) {
                    $this->quickbooksObject = $customerArray[0];
                }
            }
        }
        return $this->quickbooksObject;
    }

}
