<?php

require_once 'resources/SpreadsheetReader.php';

abstract class AbstractImportController extends GI_Controller {
    
    public function actionContactTerms($attributes) {
        if (!Permission::verifyByRef('import_data')) {
            GI_URLUtils::redirect(array(
                'controller'=>'dashboard',
                'action'=>'index'
            ));
        }
        $form = new GI_Form('import_terms');
        $otherFormData = array('title'=>'Contact Terms');
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
                'terms' => 1,
                'days' => 2,
            );
            $rowCount = $data->rowcount(0);
            for ($currentRow = 2; $currentRow < $rowCount + 1; $currentRow++) {
                $terms = $data->val($currentRow, $index['terms']);
                $days = $data->val($currentRow, $index['days']);
                $existingTerms = ContactTermsFactory::search()
                        ->filter('terms', $terms)
                        ->filter('days', $days)
                        ->select();
                if (empty($existingTerms)) {
                    $contactTerm = ContactTermsFactory::buildNewModel();
                    $contactTerm->setProperty('terms', $terms);
                    $contactTerm->setProperty('days', $days);
                    if (!$contactTerm->save()) {
                        return false;
                    }
                }
            }
            $newUrlArray = array(
                'controller' => 'dashboard',
                'action' => 'index',
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

    public function actionImportPayments($attributes) {
        if (!isset($attributes['type'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $paymentType = $attributes['type'];
        $samplePayment = PaymentFactory::buildNewModel($paymentType);
        if (empty($samplePayment)) {
            GI_URLUtils::redirectToError(2000);
        }
        if (!Permission::verifyByRef('import_data')) {
            GI_URLUtils::redirect(array(
                'controller' => 'dashboard',
                'action' => 'index'
            ));
        }
        $form = new GI_Form('import_payments');
        $otherFormData = array('title' => 'Payments');
        $view = new ImportPaymentsFileFormView($form, $otherFormData);
        $returnArray = GI_Controller::getReturnArray();
        $success = 0;
        $newUrl = NULL;
        if ($form->wasSubmitted()) {
            try {
                $excelFile = $_FILES['import_file']['tmp_name'];
                if (empty($excelFile)) {
                    $form->addFieldError('import_file', 'no_file', 'You must select a file');
                }
                $data = new \PHPExcelReader\SpreadsheetReader($excelFile);
            } catch (Exception $ex) {
                return GI_URLUtils::redirectToError();
            }
            $importer = new PaymentImporter();
            $results = $importer->parseImportData($data);
            if (empty($results)) {
                GI_URLUtils::redirectToError();
            }
            $importKey = $importer->storeResultsInSession($results);
            if (empty($importKey)) {
                GI_URLUtils::redirectToError();
            }
            $newUrlAttributes = array(
                'controller' => 'import',
                'action' => 'confirmImportedPayments',
                'key' => $importKey,
                'type'=>$paymentType,
            );
            if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
                $newUrl = GI_URLUtils::buildURL($newUrlAttributes);
                $success = 1;
            } else {
                GI_URLUtils::redirect($newUrlAttributes);
            }
        }
        
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if (!empty($newUrl)) {
            $returnArray['newUrl'] = $newUrl;
        }
        return $returnArray;
    }

    public function actionConfirmImportedPayments($attributes) {
        if (!isset($attributes['key']) || !isset($attributes['type'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $key = $attributes['key'];
        $paymentType = $attributes['type'];
        $samplePayment = PaymentFactory::buildNewModel($paymentType);

        $results = SessionService::getValue($key);
        if (empty($results) || empty($samplePayment)) {
            GI_URLUtils::redirectToError(2000);
        }
        
//        if (empty($results)) {
//            GI_URLUtils::redirectToError();
//        }
        $groupPayments = array();

        $cId = ProjectConfig::getDefaultCurrencyId();
        $defaultCurrency = CurrencyFactory::getModelById($cId);

        foreach ($results as $rowNumber => $resultArray) {
            $dateString = $resultArray['date'];
            $total = floatval($resultArray['total']);
            $payeeString = $resultArray['payee'];
            $memo = $resultArray['memo'];

            $groupPayment = GroupPaymentFactory::buildNewModel('imported');
            $groupPayment->setProperty('date', $dateString);
            $groupPayment->setProperty('amount', $total);
            $groupPayment->setProperty('sortable_balance', $total);
            $groupPayment->setProperty('currency_id', $defaultCurrency->getProperty('id'));
            $groupPayment->setProperty('void', 0);
            $groupPayment->setProperty('cancelled', 0);
            $groupPayment->setProperty('default_payment_type_ref', $paymentType);
            $groupPayment->setProperty('memo', $memo);
            $groupPayment->setPayeeString($payeeString);
            $groupPayments[$rowNumber] = $groupPayment;
        }

        $importer = new PaymentImporter();
        $form = new GI_Form('import_payments');
        $view = $importer->getImportPaymentsFormView($form, $groupPayments);
        $view->buildForm();
        if ($importer->handleImportPaymentsFormSubmission($form, $groupPayments)) {

            SessionService::unsetValue($key);
            //TODO - if user chose to continue, store group payment ids in string and forward to action to map in accounting controller
            //else, redirect as is
            
            GI_URLUtils::redirect(array(
                'controller' => 'accounting',
                'action' => 'accountsPayable',
                'tab' => 'imported_payments'
            ));
        }
        $returnArray = GI_Controller::getReturnArray($view);
        return $returnArray;
    }
    
    /**
     * @deprecated
     * @param type $attributes
     * @return int
     */
    public function actionUsers($attributes){
        if (!Permission::verifyByRef('import_data')) {
            GI_URLUtils::redirect(array(
                'controller' => 'dashboard',
                'action' => 'index'
            ));
        }
        $form = new GI_Form('import_users');
        $otherFormData = array('title' => 'Users');
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
                'email' => 2,
                'pass' => 3,
                'salt' => 4,
                'first_name' => 5,
                'last_name' => 6,
                'role_ref' => 7,
                'franchise_id' => 8,
                'internal' => 9
            );
            $successfulImports = array();
            $duplicateImports = array();
            $failedImports = array();
            $rowCount = $data->rowcount(0);
            for ($currentRow = 2; $currentRow < $rowCount + 1; $currentRow++) {
                $type = $data->val($currentRow, $index['type']);
                
                $email = trim($data->val($currentRow, $index['email']));
                $pass = trim($data->val($currentRow, $index['pass']));
                $salt = trim($data->val($currentRow, $index['salt']));
                $firstName = trim($data->val($currentRow, $index['first_name']));
                $lastName = trim($data->val($currentRow, $index['last_name']));
                $roleRef = trim($data->val($currentRow, $index['role_ref']));
                $franchiseId = trim($data->val($currentRow, $index['franchise_id']));
                $internal = trim($data->val($currentRow, $index['internal']));
                
                $label = 'ROW #' . $currentRow . ' ' . trim($firstName . ' ' . $lastName);
                
                if(empty($email)){
                    $failedImports[] = $label . ' - (email missing)';
                    continue;
                }
                
                $label .= ' (' . $email . ')';
                
                $existingUserResult = UserFactory::search()
                        ->filter('email', $email)
                        ->select();
                
                if($existingUserResult){
                    $duplicateImports[] = $label;
                    continue;
                }
                
                if(empty($firstName)){
                    $failedImports[] = $label . ' - (first name missing)';
                    continue;
                }
                
                $newUser = UserFactory::buildNewModel($type);
                
                $newUser->setProperty('email', $email);
                if(!empty($pass) && !empty($salt)){
                    $newUser->setProperty('pass', $pass);
                    $newUser->setProperty('salt', $salt);
                } else {
                    $password = $pass;
                    if(empty($password)){
                        $password = 'Password1!';
                    }
                    $salt = $newUser->generateSalt();
                    $newUser->setProperty('salt', $salt);
                    $pass = $newUser->generateSaltyPass($password, $salt);
                    $newUser->setProperty('pass', $pass);
                }
                
                $newUser->setProperty('first_name', $firstName);
                $newUser->setProperty('last_name', $lastName);
                $newUser->setProperty('franchise_id', $franchiseId);
                $newUser->setProperty('language', 'english');
                
                $role = RoleFactory::getRoleBySystemTitle($roleRef);
                if(!$role){
                    $failedImports[] = $label . ' - (cannot find role ' . $roleRef . ')';
                    continue;
                }
                
                if(!$newUser->save()){
                    $failedImports[] = $label . ' - (could not save user)';
                    continue;
                }
                
                if(!RoleFactory::linkRoleToUser($role, $newUser)){
                    $failedImports[] = $label . ' - (could not link user to role)';
                    continue;
                }
                
                $newUser->saveUserAsContact($internal);
                
                $successfulImports[] = $label;
                
            }
            $newUrlArray = array(
                'controller' => 'import',
                'action' => 'afterUsersImport',
            );
            
            SessionService::setValue('user_import_report', array(
                'success' => $successfulImports,
                'fail' => $failedImports,
                'duplicate' => $duplicateImports
            ));
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

    public function actionAfterUsersImport($attributes) {
//        if (isset($_SESSION['user_import_report'])) {
//            $reportArray = $_SESSION['user_import_report'];
//            unset($_SESSION['user_import_report']);
//        } else {
//            GI_URLUtils::redirect(array(
//                'controller' => 'user',
//                'action' => 'index'
//            ));
//        }
        $reportArray = SessionService::getValue('user_import_report');
        SessionService::unsetValue('user_import_report');
        if (empty($reportArray)) {
            GI_URLUtils::redirect(array(
                'controller' => 'user',
                'action' => 'index'
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
