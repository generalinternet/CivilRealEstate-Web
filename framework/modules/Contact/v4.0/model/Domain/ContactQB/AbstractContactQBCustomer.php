<?php
/**
 * Description of AbstractContactQBCustomer
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    3.0.1
 */
abstract class AbstractContactQBCustomer extends AbstractContactQB {

    protected $tableWrapId = 'contact_qb_customer_table';
    protected static $searchFormId = 'contact_qb_customer_search';
    
    protected static $compatibleContactCatTypeRefs = array(
        'client'
    );

    public function getViewTitle($plural = true) {
        $title = 'Quickbooks Customer';
        if ($plural) {
            $title .= 's';
        }
        return $title;
    }

    public function getAPITableName() {
        return 'Customer';
    }

    protected function setPropertiesFromQBObject($qbObject) {
        if (!parent::setPropertiesFromQBObject($qbObject)) {
            return false;
        }
        $shipAddrObject = $qbObject->ShipAddr;
        if (!empty($shipAddrObject)) {
            $this->setProperty('contact_qb_customer.ship_addr_id', $shipAddrObject->Id);
            $this->setProperty('contact_qb_customer.ship_addr_line_1', $shipAddrObject->Line1);
            $this->setProperty('contact_qb_customer.ship_addr_line_2', $shipAddrObject->Line2);
            $this->setProperty('contact_qb_customer.ship_addr_line_3', $shipAddrObject->Line3);
            $this->setProperty('contact_qb_customer.ship_addr_line_4', $shipAddrObject->Line4);
            $this->setProperty('contact_qb_customer.ship_addr_line_5', $shipAddrObject->Line5);
            $this->setProperty('contact_qb_customer.ship_addr_city', $shipAddrObject->City);
            $this->setProperty('contact_qb_customer.ship_addr_country', $shipAddrObject->Country);
            $this->setProperty('contact_qb_customer.ship_addr_region', $shipAddrObject->CountrySubDivisionCode);
            $this->setProperty('contact_qb_customer.ship_addr_postal_code', $shipAddrObject->PostalCode);
        }
        $balance = $qbObject->Balance;
        $this->setProperty('contact_qb_customer.balance', $balance);
        $this->setProperty('contact_qb_customer.bal_update_reqd', 0);
        $defaultTaxCodeRef = $qbObject->DefaultTaxCodeRef;
        $this->setProperty('contact_qb_customer.default_tax_code_qb_id', $defaultTaxCodeRef);
        $taxExemptionReasonId = $qbObject->TaxExemptionReasonId;
        $this->setProperty('contact_qb_customer.tax_exempt_reason_qb_id', $taxExemptionReasonId);
        
        return true;
    }

    public function getDetailView() {
        return new ContactQBCustomerDetailView($this);
    }

    public function getShippingAddress($addHTMLLineBreaks = true) {
        $lines = array();
        $line1 = $this->getProperty('contact_qb_customer.ship_addr_line_1');
        if (!empty($line1)) {
            $lines[] = $line1 . ' ';
        }
        $line2 = $this->getProperty('contact_qb_customer.ship_addr_line_2');
        if (!empty($line2)) {
            $lines[] = $line2 . ' ';
        }
        $line3 = $this->getProperty('contact_qb_customer.ship_addr_line_3');
        if (!empty($line3)) {
            $lines[] = $line3 . ' ';
        }
        $line4 = $this->getProperty('contact_qb_customer.ship_addr_line_4');
        if (!empty($line4)) {
            $lines[] = $line4 . ' ';
        }
        $line5 = $this->getProperty('contact_qb_customer.ship_addr_line_5');
        if (!empty($line5)) {
            $lines[] = $line5 . ' ';
        }
        $line6 = '';
        $city = $this->getProperty('contact_qb_customer.ship_addr_city');
        if (!empty($city)) {
            $line6 .= $city . ', ';
        }
        $region = $this->getProperty('contact_qb_customer.ship_addr_region');
        if (!empty($region)) {
            $line6 .= $region . ' ';
        }
        $postalCode = $this->getProperty('contact_qb_customer.ship_addr_postal_code');
        if (!empty($postalCode)) {
            $line6 .= $postalCode;
        }
        if (!empty($line6)) {
            $lines[] = $line6;
        }
        $country = $this->getProperty('contact_qb_customer.ship_addr_country');
        if (!empty($country)) {
            $lines[] = $country;
        }
        if ($addHTMLLineBreaks) {
            $address = '';
            foreach ($lines as $line) {
                $address .= $line . '<br />';
            }
        } else {
            $address = implode(' ', $lines);
        }
        return $address;
    }

    public function isCustomer() {
        return true;
    }

    protected function createContactInfosFromData(AbstractContact $contact) {
        if (parent::createContactInfosFromData($contact)) {
            $shippingAddress = $this->createShippingAddressContactInfoFromData();
            if (!empty($shippingAddress)) {
                $shippingAddress->setProperty('contact_id', $contact->getProperty('id'));
                $shippingAddress->setProperty('qb_linked', 1);
                if (!$shippingAddress->save()) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * 
     * @param type $typeRef
     * @return AbstractContactInfoAddress
     */
    public function createShippingAddressContactInfoFromData($typeRef = 'shipping_address', AbstractContactInfoAddress $contactInfo = NULL) {
        if (empty($contactInfo)) {
            $contactInfo = ContactInfoFactory::buildNewModel($typeRef);
        } else {
            $contactInfo = ContactInfoFactory::changeModelType($contactInfo, $typeRef);
        }
        if (empty($contactInfo)) {
            return NULL;
        }
        $countryCode = 'CAN';
        $regionCode = GeoDefinitions::determineDefaultRegionCode($countryCode);

        $countryString = $this->getProperty('contact_qb_customer.ship_addr_country');
        $countryName = GeoDefinitions::getCountryNameFromCode(strtoupper($countryString));
        if (!empty($countryName)) {
            $countryCode = strtoupper($countryString);
        } else {
            $countryCodeFromName = GeoDefinitions::getCountryCodeFromName($countryString);
            if (!empty($countryCodeFromName)) {
                $countryCode = $countryCodeFromName;
            }
        }
        $regionString = $this->getProperty('contact_qb_customer.ship_addr_region');
        $regionName = GeoDefinitions::getRegionNameFromCode($countryCode, $regionString, true);
        if (!empty($regionName)) {
            $regionCode = $regionString;
        } else {
            $regionCodeFromName = GeoDefinitions::getRegionCodeFromName($countryCode, $regionString, true);
            if (!empty($regionCodeFromName)) {
                $regionCode = $regionCodeFromName;
            } else {
                $regionCode = $regionString;
            }
        }
//        if (empty($countryCode) || empty($regionCode)) {
//            return NULL;
//        }
        if (!empty($countryCode) && ($countryCode === 'CAN' || $countryCode === 'USA') && empty($regionCode)) {
            return NULL;
        }
        $contactInfo->setProperty('contact_info_address.addr_country', $countryCode);
        $contactInfo->setProperty('contact_info_address.addr_region', $regionCode);

        $line1 = $this->getProperty('contact_qb_customer.ship_addr_line_1');
    //    if (!empty($line1)) {
            $contactInfo->setProperty('contact_info_address.addr_street', $line1);
     //   }
        $streetTwo = '';
        $line2 = $this->getProperty('contact_qb_customer.ship_addr_line_2');
        if (!empty($line2)) {
            $streetTwo .= $line2 . ' ';
        }
        $line3 = $this->getProperty('contact_qb_customer.ship_addr_line_3');
        if (!empty($line3)) {
            $streetTwo .= $line3 . ' ';
        }
        $line4 = $this->getProperty('contact_qb_customer.ship_addr_line_4');
        if (!empty($line4)) {
            $streetTwo .= $line4 . ' ';
        }
        $line5 = $this->getProperty('contact_qb_customer.ship_addr_line_5');
        if (!empty($line5)) {
            $streetTwo .= $line5 . ' ';
        }
     //   if (!empty($streetTwo)) {
            $contactInfo->setProperty('contact_info_address.addr_street_two', $streetTwo);
     //   }
        $city = $this->getProperty('contact_qb_customer.ship_addr_city');
        $contactInfo->setProperty('contact_info_address.addr_city', $city);

        $postalCode = $this->getProperty('contact_qb_customer.ship_addr_postal_code');
        $contactInfo->setProperty('contact_info_address.addr_code', $postalCode);
        return $contactInfo;
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
                $resourceObject = QuickBooksOnline\API\Facades\Customer::create($properties);
                $resultingObject = $dataService->Add($resourceObject);
            } else {
                $resourceObject = $dataService->FindById('Customer', $qbId);
                $properties['Id'] = $qbId;
                $properties['sparse'] = true;
                $resourceObject = QuickBooksOnline\API\Facades\Customer::update($resourceObject, $properties);
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
                $this->setProperty('contact_qb_customer.balance', $resultingObject->Balance);
                $this->setProperty('contact_qb_customer.default_tax_code_qb_id', $resultingObject->DefaultTaxCodeRef);
                $this->setProperty('fully_qualified_name', $resultingObject->FullyQualifiedName);
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

    public function getQuickbooksExportPropertiesArray() {
        $properties = parent::getQuickbooksExportPropertiesArray();
        $properties['ShipAddr'] = array(
            'Line1' => $this->getProperty('contact_qb_customer.ship_addr_line_1'),
            'Line2' => $this->getProperty('contact_qb_customer.ship_addr_line_2'),
            'City' => $this->getProperty('contact_qb_customer.ship_addr_city'),
            'CountrySubDivisionCode' => $this->getProperty('contact_qb_customer.ship_addr_region'),
            'Country' => $this->getProperty('contact_qb_customer.ship_addr_country'),
            'PostalCode' => $this->getProperty('contact_qb_customer.ship_addr_postal_code'),
        );
        $properties['Taxable'] = true;
        $parentQbId = $this->getProperty('parent_qb_id');
        if (!empty($parentQbId)) {
            $properties['ParentRef'] = $parentQbId;
            $properties['Job'] = true;
        } else {
            $properties['Job'] = false;
        }
        return $properties;
    }

    public function getQuickbooksObject() {
        if (empty($this->quickbooksObject)) {
            $quickbooksId = $this->getQuickbooksId();
            $dataService = QBConnection::getInstance();
            if (!empty($quickbooksId) && !empty($dataService)) {
                $apiQuery = "SELECT * from Customer";
                $apiQuery .= " where id='" . $quickbooksId . "'";
                $customerArray = $dataService->Query($apiQuery);
                if (!empty($customerArray)) {
                    $this->quickbooksObject = $customerArray[0];
                }
            }
        }
        return $this->quickbooksObject;
    }

    public function getOutstandingInvoiceBalance() {
        return $this->getProperty('contact_qb_customer.balance');
    }

    public function requiresBalanceUpdate() {
        if (!empty($this->getProperty('contact_qb_customer.bal_update_reqd'))) {
            return true;
        }
        return false;
    }

    public function getQuickbooksDefaultTaxCodeRef() {
        $taxCodeRef = $this->getProperty('contact_qb_customer.default_tax_code_qb_id');
        if (!empty($taxCodeRef)) {
            return $taxCodeRef;
        }
        return parent::getQuickbooksDefaultTaxCodeRef();
    }

    public function getIsTaxExempt() {
        if (!empty($this->getProperty('contact_qb_customer.tax_exempt_reason_qb_id'))) {
            return true;
        }
        return false;
    }

}
