<?php

require_once 'framework/core/' . FRMWK_CORE_VER . '/controller/AbstractImportController.php';

class ImportController extends AbstractImportController {

    public function actionTmpContacts($attributes){
        if(!DEV_MODE){
            die('not available');
        }
        if (!Permission::verifyByRef('import_data')) {
            GI_URLUtils::redirect(array(
                'controller' => 'dashboard',
                'action' => 'index'
            ));
        }
        $form = new GI_Form('import_contact');
        $otherFormData = array('title' => 'Tmp Contacts');
        $view = new ImportFileFormView($form, $otherFormData);
        $returnArray = GI_Controller::getReturnArray();
        $success = 0;
        if ($form->wasSubmitted()) {
            try {
                $excelFile = $_FILES['import_file']['tmp_name'];
                if (empty($excelFile)) {
                    $form->addFieldError('import_file', 'no_file', 'You must select a file');
                }
                $data = new \PHPExcelReader\SpreadsheetReader($excelFile);
            } catch (Exception $ex) {
                return NULL;
            }

            $index = array(
                'type' => 1,
                'company' => 2,
                'dba' => 3,
                'currency' => 4,
                'addr_street' => 5,
                'addr_city' => 6,
                'addr_region' => 7,
                'addr_country' => 8,
                'addr_code' => 9,
                'phone' => 10,
                'fax' => 11,
                'cell' => 12,
                'email' => 13,
                'first_name' => 14,
                'last_name' => 15,
                'location' => 16,
                'notes' => 17
            );
            $successfulImports = array();
            $duplicateImports = array();
            $failedImports = array();
            $rowCount = $data->rowcount(0);
            for ($currentRow = 2; $currentRow < $rowCount + 1; $currentRow++) {
                $type = $data->val($currentRow, $index['type']);
                $duplicate = false;
                $oldOrg = NULL;
                $oldInd = NULL;
                $contactLabel = '[' . $currentRow . '] ';
                $company = $data->val($currentRow, $index['company']);
                $dba =  $data->val($currentRow, $index['dba']);
                
                if(!empty($company)){
                    $contactLabel .= 'Organization ' . $company;
                    $existingContactOrgSearch = ContactFactory::search()
                            ->filterByTypeRef('org')
                            ->filter('org.title', $company);
                    
                    $existingContactOrgs = $existingContactOrgSearch->select();
                    if(!empty($existingContactOrgs)){
                        $oldOrg = $existingContactOrgs[0];
                        $duplicate = true;
                    }
                }
                
                $firstName = $data->val($currentRow, $index['first_name']);
                $lastName = $data->val($currentRow, $index['last_name']);
                
                if(!empty($firstName) && !empty($lastName)){
                    $contactLabel .= 'Individual ' . trim($firstName . ' ' . $lastName);
                    $existingContactIndSearch = ContactFactory::search()
                            ->filterByTypeRef('ind')
                            ->filter('ind.first_name', $firstName)
                            ->filter('ind.last_name', $lastName);
                    
                    $existingContactInds = $existingContactIndSearch->select();
                    if(!empty($existingContactInds)){
                        $oldInd = $existingContactInds[0];
                        $duplicate = true;
                    } else {
                        $duplicate = false;
                    }
                }
                
                $location = $data->val($currentRow, $index['location']);
                
                if($type == 'loc' && !empty($location)){
                    $contactLabel .= 'Location ' . $location;
                    $existingContactLocSearch = ContactFactory::search()
                            ->filterByTypeRef('loc')
                            ->filter('loc.name', $location);
                    
                    $existingContactLocs = $existingContactLocSearch->select();
                    if(!empty($existingContactLocs)){
                        $duplicate = true;
                    } else {
                        $duplicate = false;
                    }
                }
                
                if($duplicate){
                    $duplicateImports[] = $contactLabel;
                    continue;
                }
                
                $addrStreet = $data->val($currentRow, $index['addr_street']);
                $addrCity = $data->val($currentRow, $index['addr_city']);
                $addrRegion = $data->val($currentRow, $index['addr_region']);
                $addrCountry = $data->val($currentRow, $index['addr_country']);
                $addrCode = $data->val($currentRow, $index['addr_code']);
                
                $phone = $data->val($currentRow, $index['phone']);
                $fax = $data->val($currentRow, $index['fax']);
                $cell = $data->val($currentRow, $index['cell']);
                
                $email = $data->val($currentRow, $index['email']);
                
                $currencyRef = $data->val($currentRow, $index['currency']);
                
                if (!empty($currencyRef)) {
                    $currency = CurrencyFactory::getModelByRef(strtolower($currencyRef));
                    if (empty($currency)) {
                        $failedImports[] = $contactLabel . ' - (currency missing : ' . $currencyRef . ')';
                        continue;
                    }
                }
                
                $notes = $data->val($currentRow, $index['notes']);
                
                $newOrg = NULL;
                $newInd = NULL;
                $newLoc = NULL;
                
                if(!empty($company) && !$oldOrg){
                    $newOrg = ContactFactory::buildNewModel('org');
                    $newOrg->getColour(); //sets a random colour
                    $newOrg->setProperty('default_currency_id', $currency->getId());
                    $newOrg->setProperty('contact_org.title', $company);
                    $newOrg->setProperty('contact_org.doing_bus_as', $dba);
                    if(!$newOrg->save()){
                        $failedImports[] = $contactLabel . ' (contact)';
                        continue;
                    }
                }
                
                if(!empty($firstName) && !empty($lastName) && !$oldInd){
                    $newInd = ContactFactory::buildNewModel('ind');
                    $newInd->getColour(); //sets a random colour
                    $newInd->setProperty('default_currency_id', $currency->getId());
                    $newInd->setProperty('contact_ind.first_name', $firstName);
                    $newInd->setProperty('contact_ind.last_name', $lastName);
                    if(!$newInd->save()){
                        $failedImports[] = $contactLabel . ' (contact)';
                        continue;
                    }
                }
                
                if(!empty($location)){
                    $newLoc = ContactFactory::buildNewModel('loc');
                    $newLoc->getColour(); //sets a random colour
                    $newLoc->setProperty('contact_loc.name', $location);
                    if(!$newLoc->save()){
                        $failedImports[] = $contactLabel . ' (contact)';
                        continue;
                    }
                }
                
                if($type != 'loc'){
                    $contactCat = ContactCatFactory::buildNewModel($type);
                    if($newOrg){
                        $contactCat->setProperty('contact_id', $newOrg->getId());
                        if(!$contactCat->save()){
                            $failedImports[] = $contactLabel . ' (org contact cat)';
                        }
                    }
                    if($newInd){
                        $indContactCat = clone $contactCat;
                        $indContactCat->setProperty('contact_id', $newInd->getId());
                        if(!$indContactCat->save()){
                            $failedImports[] = $contactLabel . ' (ind contact cat)';
                        }
                    }
                }
                
                if($newOrg && $newInd){
                    if(!ContactFactory::linkContactAndContact($newOrg, $newInd)){
                        $failedImports[] = $contactLabel . ' (linking new org to new ind)';
                    }
                }
                
                if($oldOrg && $newInd){
                    if(!ContactFactory::linkContactAndContact($oldOrg, $newInd)){
                        $failedImports[] = $contactLabel . ' (linking old org to new ind)';
                    }
                }
                
                if($newOrg && $newLoc){
                    if(!ContactFactory::linkContactAndContact($newOrg, $newLoc)){
                        $failedImports[] = $contactLabel . ' (linking new org to new loc)';
                    }
                }
                
                if($oldOrg && $newLoc){
                    if(!ContactFactory::linkContactAndContact($oldOrg, $newLoc)){
                        $failedImports[] = $contactLabel . ' (linking old org to new loc)';
                    }
                }
                
                if(!empty($addrRegion)){
                    $infoAddr = ContactInfoFactory::buildNewModel('address');
                    $infoAddr->setProperty('contact_info_address.addr_street', $addrStreet);
                    $infoAddr->setProperty('contact_info_address.addr_city', $addrCity);
                    $infoAddr->setProperty('contact_info_address.addr_region', $addrRegion);
                    $infoAddr->setProperty('contact_info_address.addr_country', $addrCountry);
                    $infoAddr->setProperty('contact_info_address.addr_code', $addrCode);
                    if($newOrg){
                        $infoAddr->setProperty('contact_id', $newOrg->getId());
                        $infoAddr->save();
                        if(!$infoAddr->save()){
                            $failedImports[] = $contactLabel . ' (org addr)';
                        }
                    }
                    if($newInd){
                        $indInfoAddr = clone $infoAddr;
                        $indInfoAddr->setProperty('contact_id', $newInd->getId());
                        if(!$indInfoAddr->save()){
                            $failedImports[] = $contactLabel . ' (ind addr)';
                        }
                    }
                    if($newLoc){
                        $locInfoAddr = clone $infoAddr;
                        $locInfoAddr->setProperty('contact_id', $newLoc->getId());
                        if(!$locInfoAddr->save()){
                            $failedImports[] = $contactLabel . ' (loc addr)';
                        }
                    }
                }
                
                if(!empty($phone)){
                    $infoPhone = ContactInfoFactory::buildNewModel('phone_num');
                    $infoPhone->setProperty('contact_info_phone_num.phone', $phone);
                    if($newOrg){
                        $infoPhone->setProperty('contact_id', $newOrg->getId());
                    } elseif($newInd){
                        $infoPhone->setProperty('contact_id', $newInd->getId());
                    }
                    if(!$infoPhone->save()){
                        $failedImports[] = $contactLabel . ' (phone)';
                    }
                }
                
                if(!empty($fax)){
                    $infoFax = ContactInfoFactory::buildNewModel('fax_num');
                    $infoFax->setProperty('contact_info_phone_num.phone', $fax);
                    if($newOrg){
                        $infoFax->setProperty('contact_id', $newOrg->getId());
                    } elseif($newInd){
                        $infoFax->setProperty('contact_id', $newInd->getId());
                    }
                    if(!$infoFax->save()){
                        $failedImports[] = $contactLabel . ' (fax)';
                    }
                }
                
                if(!empty($cell)){
                    $infoCell = ContactInfoFactory::buildNewModel('mobile_phone_num');
                    $infoCell->setProperty('contact_info_phone_num.phone', $cell);
                    if($newInd){
                        $infoCell->setProperty('contact_id', $newInd->getId());
                    } elseif($newOrg){
                        $infoCell->setProperty('contact_id', $newOrg->getId());
                    }
                    if(!$infoCell->save()){
                        $failedImports[] = $contactLabel . ' (cell)';
                    }
                }
                
                if(!empty($email)){
                    $infoEmail = ContactInfoFactory::buildNewModel('email_address');
                    $infoEmail->setProperty('contact_info_email_addr.email_address', $email);
                    if($newOrg){
                        $infoEmail->setProperty('contact_id', $newOrg->getId());
                    } elseif($newInd){
                        $infoEmail->setProperty('contact_id', $newInd->getId());
                    }
                    if(!$infoEmail->save()){
                        $failedImports[] = $contactLabel . ' (email)';
                    }
                }
                
                if (!empty($notes)) {
                    if($newOrg){
                        if(!$newOrg->addPrivateNote($notes)){
                            $failedImports[] = $contactLabel . ' (notes)';
                        }
                    } elseif($newInd){
                        if(!$newInd->addPrivateNote($notes)){
                            $failedImports[] = $contactLabel . ' (notes)';
                        }
                    } elseif($newLoc){
                        if(!$newLoc->addPrivateNote($notes)){
                            $failedImports[] = $contactLabel . ' (notes)';
                        }
                    }
                }
                
                $successfulImports[] = $contactLabel;
                
            }
            $newUrlArray = array(
                'controller' => 'import',
                'action' => 'afterCustomersImport',
            );
            $_SESSION['customer_import_report'] = array(
                'success' => $successfulImports,
                'fail' => $failedImports,
                'duplicate' => $duplicateImports
            );
            if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
                $newUrlArray['ajax'] = 1;
                $newUrl = GI_URLUtils::buildLinkURL($newUrlArray);
                $success = 1;
                $returnArray['newUrl'] = $newUrl;
            } else {
                GI_URLUtils::redirect($newUrlArray);
            }
        }
        
        $returnArray['mainContent'] = $view->getHTMLView();
        $returnArray['success'] = $success;
        return $returnArray;
    }
    
    public function actionAfterCustomersImport($attributes) {
        if (isset($_SESSION['customer_import_report'])) {
            $reportArray = $_SESSION['customer_import_report'];
            unset($_SESSION['customer_import_report']);
        } else {
            GI_URLUtils::redirect(array(
                'controller' => 'contact',
                'action' => 'catIndex',
                'type' => 'client'
            ));
        }
        $successfulImports = $reportArray['success'];
        $failedImports = $reportArray['fail'];
        $duplicateImports = $reportArray['duplicate'];
        $view = new AdminEchoView();
        $view->echoThis('SUCCESSFUL IMPORTS: ' . count($successfulImports));
        $view->echoThis('DUPLICATE IMPORTS: ' . count($duplicateImports));
        if (!empty($duplicateImports)) {
            foreach ($duplicateImports as $duplicateImport) {
                $view->echoThis($duplicateImport);
            }
        }
        $view->echoThis('FAILED IMPORTS: ' . count($failedImports));
        if (!empty($failedImports)) {
            foreach ($failedImports as $failedImport) {
                $view->echoThis($failedImport);
            }
        }
        $returnArray = $this->getReturnArray($view);
        return $returnArray;
    }
    
}
