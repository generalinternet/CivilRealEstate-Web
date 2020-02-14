<?php

/**
 * Description of AbstractAccountingController
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractAccountingController extends GI_Controller {

    public function actionPreApplyPaymentToInvoices($attributes) {
        if (!isset($attributes['invoiceIds']) || !isset($attributes['contactId']) || !isset($attributes['type'])) {
            GI_URLUtils::redirectToError(2000);
        }
        if (!isset($attributes['currencyRef'])) {
            $currencyRef = 'usd';
        } else {
            $currencyRef = $attributes['currencyRef'];
        }
        $type = $attributes['type'];
        $contactId = $attributes['contactId'];
        $contact = ContactFactory::getModelById($contactId);
        $currency = CurrencyFactory::getModelByRef($currencyRef);
        $form = new GI_Form('pre_apply_gp');
        $view = new AccountingPreApplyGroupPaymentFormView($form, $type, $currency, $contact);
        $view->buildForm();
        $success = 0;
        $newUrl = NULL;
        if ($form->wasSubmitted() && $form->validate()) {
            $continue = true;
            $newOrOld = filter_input(INPUT_POST, 'new_or_old');
            if ($newOrOld === 'old') {
                $groupPaymentId = filter_input(INPUT_POST, 'group_payment_id');
                if (empty($groupPaymentId)) {
                    $form->addFieldError('group_payment_id', 'error', 'You must select a payment');
                    $continue = false;
                } else {
                    $attributes['groupPaymentId'] = $groupPaymentId;
                }
            }
            if ($continue) {
                $attributes['action'] = 'applyPaymentToInvoices';
                if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
                    unset($attributes['ajax']);
                    $newUrl = GI_URLUtils::buildURL($attributes);
                    $success = 1;
                } else {
                    GI_URLUtils::redirect($attributes);
                }
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if (!empty($newUrl)) {
            $returnArray['newUrl'] = $newUrl;
        }
        return $returnArray;
    }

    public function actionPreApplyPaymentToBills($attributes) {
        if (!isset($attributes['billIds']) || !isset($attributes['contactId']) || !isset($attributes['type'])) {
            GI_URLUtils::redirectToError(2000);
        }
        if (!isset($attributes['currencyRef'])) {
            $currencyRef = 'usd';
        } else {
            $currencyRef = $attributes['currencyRef'];
        }
        $type = $attributes['type'];
        $contactId = $attributes['contactId'];
        $contact = ContactFactory::getModelById($contactId);
        $currency = CurrencyFactory::getModelByRef($currencyRef);
        $form = new GI_Form('pre_apply_gp');
        $view = new AccountingPreApplyGroupPaymentFormView($form, $type, $currency, $contact);
        $view->buildForm();
        $success = 0;
        $newUrl = NULL;
        if ($form->wasSubmitted() && $form->validate()) {
            $continue = true;
            $newOrOld = filter_input(INPUT_POST, 'new_or_old');
            if ($newOrOld === 'old') {
                $groupPaymentId = filter_input(INPUT_POST, 'group_payment_id');
                if (empty($groupPaymentId)) {
                    $form->addFieldError('group_payment_id', 'error', 'You must select a payment');
                    $continue = false;
                } else {
                    $attributes['groupPaymentId'] = $groupPaymentId;
                }
            }
            if ($continue) {
                $attributes['action'] = 'applyPaymentToBills';
                if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
                    unset($attributes['ajax']);
                    $newUrl = GI_URLUtils::buildURL($attributes);
                    $success = 1;
                } else {
                    GI_URLUtils::redirect($attributes);
                }
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if (!empty($newUrl)) {
            $returnArray['newUrl'] = $newUrl;
        }
        return $returnArray;
    }

    public function actionApplyPaymentToInvoices($attributes) {
        if (!isset($attributes['invoiceIds'])) {
            GI_URLUtils::redirectToError(2000);
        }
        if (!isset($attributes['queryId'])) {
            $queryId = 0;
        } else {
            $queryId = $attributes['queryId'];
        }
        if (!isset($attributes['currencyRef'])) {
            $currencyRef = 'usd';
        } else {
            $currencyRef = $attributes['currencyRef'];
        }
        $currency = CurrencyFactory::getModelByRef($currencyRef);
        $currencyId = $currency->getProperty('id');
        if (isset($attributes['contactId'])) {
            $contactId = $attributes['contactId'];
        } else {
            $contactId = NULL;
        }
        $groupPayment = NULL;
        if (isset($attributes['groupPaymentId'])) {
            $groupPayment = GroupPaymentFactory::getModelById($attributes['groupPaymentId']);
        }
        if (empty($groupPayment)) {
            $groupPayment = GroupPaymentFactory::buildNewModel('cheque');
            $groupPayment->setProperty('currency_id', $currencyId);
            if (!empty($contactId)) {
                $groupPayment->setProperty('contact_id', $contactId);
            }
        }
        $invoiceIdArray = explode(',', $attributes['invoiceIds']);
        $invoices = array();
        $invoiceTableName = InvoiceFactory::getDbPrefix() . 'invoice';
        foreach ($invoiceIdArray as $invoiceId) {
            $invoiceSearch = InvoiceFactory::search()
                    ->join('item_link_to_income', 'item_id', $invoiceTableName, 'id', 'ilti')
                    ->filter('ilti.table_name', 'invoice')
                    ->filter('ilti.item_id', $invoiceId)
                    ->join('income', 'id', 'ilti', 'income_id', 'inc')
                    ->filter('inc.void', 0)
                    ->filter('inc.cancelled', 0);
            $invoiceSearch->filter('id', $invoiceId)
                    ->filter('currency_id', $currencyId);
            $invoicesArray = $invoiceSearch->select();
            if (!empty($invoicesArray)) {
                $invoices[] = $invoicesArray[0];
            }
        }
        if (empty($invoices)) {
            GI_URLUtils::redirectToError(2000);
        }
        $form = new GI_Form('apply_payment_to_invoices');
        $view = new AccountingApplyGroupPaymentToInvoicesFormView($form, $groupPayment, $invoices);
        if (sizeof($invoiceIdArray) > 1) {
            $newUrlArray = array(
                'controller' => 'accounting',
                'action' => 'accountsReceivable',
                'tab' => 'invoices',
                'queryId' => $queryId
            );
        } else {
            $newUrlArray = array(
                'controller' => 'invoice',
                'action' => 'view',
                'id' => $invoices[0]->getProperty('id')
            );
        }
        $view->setCancelURL(GI_URLUtils::buildURL($newUrlArray));
        $success = 0;
        $newUrl = NULL;
        $view->buildForm();
        if ($form->wasSubmitted() && $form->validate()) {
            $targetGroupPaymentType = filter_input(INPUT_POST, 'group_payment_type_ref');
            if ($groupPayment->getTypeRef() !== $targetGroupPaymentType) {
                if (empty($groupPayment->getProperty('id'))) {
                    $groupPayment = GroupPaymentFactory::buildNewModel($targetGroupPaymentType);
                } else {
                    $groupPayment = GroupPaymentFactory::changeModelType($groupPayment, $targetGroupPaymentType);
                }
            }
            $updatedGroupPayment = $groupPayment->handleApplyPaymentToInvoicesFormSubmission($form, $invoices);
            if (!empty($updatedGroupPayment)) {
                if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
                    $newUrl = GI_URLUtils::buildURL($newUrlArray);
                    $success = 1;
                } else {
                    GI_URLUtils::redirect($newUrlArray);
                }
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if (!empty($newUrl)) {
            $returnArray['newUrl'] = $newUrl;
        }
        $breadcrumbs = $invoices[0]->getARBreadcrumbs();
        if (count($invoices) == 1) {
            $breadcrumbs[] = array(
                'label'=>$invoices[0]->getTypeTitle() . ' #' . $invoices[0]->getInvoiceNumber(true),
                'link'=>$invoices[0]->getViewURL(),
            );
        }
        $currentURL = GI_URLUtils::buildURL($attributes);
        $breadcrumbs[] = array(
            'label' => 'Apply Payment to Invoice',
            'link' => $currentURL,
        );
        $returnArray['breadcrumbs'] = $breadcrumbs;
        return $returnArray;
    }

    public function actionApplyPaymentToBills($attributes) {
        if (!isset($attributes['billIds'])) {
            GI_URLUtils::redirectToError(2000);
        }
        if (!isset($attributes['queryId'])) {
            $queryId = 0;
        } else {
            $queryId = $attributes['queryId'];
        }
        if (!isset($attributes['currencyRef'])) {
            $currencyRef = 'usd';
        } else {
            $currencyRef = $attributes['currencyRef'];
        }
        $currency = CurrencyFactory::getModelByRef($currencyRef);
        $currencyId = $currency->getProperty('id');
        if (isset($attributes['contactId'])) {
            $contactId = $attributes['contactId'];
        } else {
            $contactId = NULL;
        }
        $groupPayment = NULL;
        if (isset($attributes['groupPaymentId'])) {
            $groupPayment = GroupPaymentFactory::getModelById($attributes['groupPaymentId']);
        }
        if (empty($groupPayment)) {
            $groupPayment = GroupPaymentFactory::buildNewModel('cheque');
            $groupPayment->setProperty('currency_id', $currencyId);
            if (!empty($contactId)) {
                $groupPayment->setProperty('contact_id', $contactId);
            }
        }
        $billIdArray = explode(',', $attributes['billIds']);
        $bills = array();
        $billTableName = BillFactory::getDbPrefix() . 'bill';
        foreach ($billIdArray as $billId) {
            $billSearch = BillFactory::search();
            $billSearch->filter('id', $billId)
                    ->filter('currency_id', $currencyId)
                    ->join('item_link_to_expense', 'item_id', $billTableName, 'id', 'ilte')
                    ->filter('ilte.table_name', 'bill')
                    ->filter('ilte.item_id', $billId)
                    ->join('expense', 'id', 'ilte', 'expense_id', 'exp')
                    ->filter('exp.void', 0)
                    ->filter('exp.cancelled', 0);
            $billsArray = $billSearch->select();
            if (!empty($billsArray)) {
                $bills[] = $billsArray[0];
            }
        }
        if (empty($bills)) {
            GI_URLUtils::redirectToError(2000);
        }
        $form = new GI_Form('apply_payment_to_bills');
        $view = new AccountingApplyGroupPaymentToBillsFormView($form, $groupPayment, $bills);
        if (sizeof($billIdArray) > 1) {
            $newUrlArray = array(
                'controller' => 'accounting',
                'action' => 'accountsPayable',
                'tab' => 'bills',
                'queryId' => $queryId
            );
        } else {
            $newUrlArray = array(
                'controller' => 'billing',
                'action' => 'view',
                'id' => $bills[0]->getProperty('id')
            );
        }
        $view->setCancelURL(GI_URLUtils::buildURL($newUrlArray));
        $success = 0;
        $newUrl = NULL;
        $view->buildForm();
        if ($form->wasSubmitted() && $form->validate()) {
            $targetGroupPaymentType = filter_input(INPUT_POST, 'group_payment_type_ref');
            if ($groupPayment->getTypeRef() !== $targetGroupPaymentType) {
                if (empty($groupPayment->getProperty('id'))) {
                    $groupPayment = GroupPaymentFactory::buildNewModel($targetGroupPaymentType);
                } else {
                    $groupPayment = GroupPaymentFactory::changeModelType($groupPayment, $targetGroupPaymentType);
                }
            }
            $updatedGroupPayment = $groupPayment->handleApplyPaymentToBillsFormSubmission($form, $bills);
            if (!empty($updatedGroupPayment)) {
//                if (sizeof($billIdArray) > 1) {
//                    $newUrlArray = array(
//                        'controller' => 'accounting',
//                        'action' => 'accountsPayable',
//                        'tab'=>'bills',
//                        'queryId' => $queryId
//                    );
//                } else {
//                    $newUrlArray = array(
//                        'controller'=>'billing',
//                        'action'=>'view',
//                        'id'=>$bills[0]->getProperty('id')
//                    );
//                }
                if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
                    $newUrl = GI_URLUtils::buildURL($newUrlArray);
                    $success = 1;
                } else {
                    GI_URLUtils::redirect($newUrlArray);
                }
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if (!empty($newUrl)) {
            $returnArray['newUrl'] = $newUrl;
        }
        $breadcrumbs = $bills[0]->getAPBreadcrumbs();
        if (count($bills) == 1) {
            $breadcrumbs[] = array(
                'label'=>$bills[0]->getTypeTitle() . ' #' . $bills[0]->getBillNumber(),
                'link'=>$bills[0]->getViewURL(),
            );
        }
        $currentURL = GI_URLUtils::buildURL($attributes);
        $breadcrumbs[] = array(
            'label' => 'Apply Payment to Bill',
            'link' => $currentURL,
        );
        $returnArray['breadcrumbs'] = $breadcrumbs;
        return $returnArray;
    }

    //TODO - refactor this, credits index, and importedPaymentsIndex into the same action
    public function actionPaymentsIndex($attributes) {
        if (!isset($attributes['type'])) {
            $type = 'payment';
        } else {
            $type = $attributes['type'];
        }
        if (isset($attributes['pageNumber'])) {
            $pageNumber = $attributes['pageNumber'];
        } else {
            $pageNumber = 1;
        }
        if (isset($attributes['queryId'])) {
            $queryId = $attributes['queryId'];
        } else {
            $queryId = NULL;
        }
        $typeModel = TypeModelFactory::getTypeModelByRef($type, 'payment_type');
        if (empty($typeModel)) {
            GI_URLUtils::redirectToError(4000);
        }
        $typeRef = $typeModel->getProperty('ref');
        $samplePayment = PaymentFactory::buildNewModel($type);
        if (!$samplePayment->isIndexViewable()) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $creditType = TypeModelFactory::getTypeModelByRef('credit', 'group_payment_type');
        $importedType = TypeModelFactory::getTypeModelByRef('imported', 'group_payment_type');
        $groupPaymentSearch = GroupPaymentFactory::search()
                ->filterNotEqualTo('group_payment_type_id', $creditType->getProperty('id'))
                ->filterNotEqualTo('group_payment_type_id', $importedType->getProperty('id'))
                ->filter('default_payment_type_ref', $typeRef)
                ->orderBy('void', 'ASC')
                ->orderBy('date', 'DESC');

        $groupPaymentSearch
                ->groupBy('id')
                ->setPageNumber($pageNumber)
                ->setItemsPerPage(ProjectConfig::getUITableItemsPerPage())
                ->setQueryId($queryId);

        $samplePaymentClass = get_class($samplePayment);
        $sampleGroupPayment = GroupPaymentFactory::buildNewModel('cheque');
        $sampleGroupPaymentClass = get_class($sampleGroupPayment);
        $searchView = $samplePaymentClass::getSearchForm($groupPaymentSearch, $sampleGroupPayment, $type);
        $searchView->setUseAjax(true);
        $groupPayments = $groupPaymentSearch->select();
        $pageBar = $groupPaymentSearch->getPageBar(array(
            'controller' => 'accounting',
            'action' => 'paymentsIndex',
            'type' => $type
        ));
        $pageBar->setUseAjax(true);
        $uiTableCols = $sampleGroupPaymentClass::getUITableCols();
        $uiTableView = new UITableView($groupPayments, $uiTableCols, $pageBar);
        $uiTableView->setLoadMore(false);
        $uiTableView->setTableWrapId($sampleGroupPayment->getTableWrapId());
        $searchView->setTargetElementId($sampleGroupPayment->getTableWrapId());
        if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
            $returnArray = array();
            if (isset($attributes['tabbed']) && $attributes['tabbed'] == 1) {
                $view = $samplePaymentClass::getIndexView($groupPayments, $uiTableView, $samplePayment, $searchView);
                $returnArray = GI_Controller::getReturnArray($view);
            } else if (isset($attributes['onlyRows']) && $attributes['onlyRows'] == 1) {
                $returnArray['uiTableRows'] = $uiTableView->getRows();
            } else {
                $returnArray['uiTable'] = $uiTableView->getHTMLView();
            }
        } else {
            $view = $samplePaymentClass::getIndexView($groupPayments, $uiTableView, $samplePayment, $searchView);
            $returnArray = GI_Controller::getReturnArray($view);
            if (!empty($groupPayments)) {
                $exampleGroupPayment = $groupPayments[0];
            } else {
                $exampleGroupPayment = GroupPaymentFactory::buildNewModel('cheque');
            }
            $breadcrumbs = $exampleGroupPayment->getBreadcrumbs();
            $indexURL = GI_URLUtils::buildURL(array(
                        'controller' => 'accounting',
                        'action' => 'paymentsIndex',
                        'type' => $type
            ));
            $breadcrumbs[] = array(
                'label' => 'Payments - ' . $samplePayment->getTypeTitle(),
                'link' => $indexURL,
            );
            $returnArray['breadcrumbs'] = $breadcrumbs;
        }
        return $returnArray;
    }

    public function actionCreditsIndex($attributes) {
        if (!Permission::verifyByRef('view_credits_index')) {
            GI_URLUtils::redirectToAccessDenied();
        }
        if (!isset($attributes['type'])) {
            $type = 'payment';
        } else {
            $type = $attributes['type'];
        }
        if (isset($attributes['pageNumber'])) {
            $pageNumber = $attributes['pageNumber'];
        } else {
            $pageNumber = 1;
        }
        if (isset($attributes['queryId'])) {
            $queryId = $attributes['queryId'];
        } else {
            $queryId = NULL;
        }
        $typeModel = TypeModelFactory::getTypeModelByRef($type, 'payment_type');
        if (empty($typeModel)) {
            GI_URLUtils::redirectToError(4000);
        }
        $typeRef = $typeModel->getProperty('ref');
        $samplePayment = PaymentFactory::buildNewModel($type);
        $groupPaymentSearch = GroupPaymentFactory::search()
                ->filterByTypeRef('credit')
                ->filter('default_payment_type_ref', $typeRef)
                ->orderBy('void', 'ASC')
                ->orderBy('date', 'DESC');

        $groupPaymentSearch->groupBy('group_payment.id')
                ->setPageNumber($pageNumber)
                ->setItemsPerPage(ProjectConfig::getUITableItemsPerPage())
                ->setQueryId($queryId);

        $samplePaymentClass = get_class($samplePayment);
        $sampleGroupPayment = GroupPaymentFactory::buildNewModel('credit');
        $sampleGroupPaymentClass = get_class($sampleGroupPayment);
        $sampleGroupPayment->addCustomFiltersToDataSearch($groupPaymentSearch);
        $searchView = $samplePaymentClass::getSearchForm($groupPaymentSearch, $sampleGroupPayment,$type, array(
            'controller'=>'accounting',
            'action'=>'creditsIndex',
            'type'=>$type
        ));
        $searchView->setUseAjax(true);
        $groupPayments = $groupPaymentSearch->select();
        $pageBar = $groupPaymentSearch->getPageBar(array(
            'controller' => 'accounting',
            'action' => 'creditsIndex',
            'type' => $type
        ));
        $pageBar->setUseAjax(true);
        $uiTableCols = $sampleGroupPaymentClass::getUITableCols();
        $uiTableView = new UITableView($groupPayments, $uiTableCols, $pageBar);
        $uiTableView->setLoadMore(false);
        $uiTableView->setTableWrapId($sampleGroupPayment->getTableWrapId());
        $searchView->setTargetElementId($sampleGroupPayment->getTableWrapId());
        if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
            $returnArray = array();
            if (isset($attributes['tabbed']) && $attributes['tabbed'] == 1) {
                $view = $samplePaymentClass::getCreditIndexView($groupPayments, $uiTableView, $samplePayment, $searchView);
                $returnArray = GI_Controller::getReturnArray($view);
            } else if (isset($attributes['onlyRows']) && $attributes['onlyRows'] == 1) {
                $returnArray['uiTableRows'] = $uiTableView->getRows();
            } else {
                $returnArray['uiTable'] = $uiTableView->getHTMLView();
            }
        } else {
            $view = $samplePaymentClass::getCreditIndexView($groupPayments, $uiTableView, $samplePayment, $searchView);
            $returnArray = GI_Controller::getReturnArray($view);
            if (!empty($groupPayments)) {
                $exampleGroupPayment = $groupPayments[0];
            } else {
                $exampleGroupPayment = GroupPaymentFactory::buildNewModel('credit');
            }
            $breadcrumbs = $exampleGroupPayment->getBreadcrumbs();
            $indexURL = GI_URLUtils::buildURL(array(
                        'controller' => 'accounting',
                        'action' => 'creditIndex',
                        'type' => $type
            ));
            $breadcrumbs[] = array(
                'label' => 'Credits - ' . $samplePayment->getTypeTitle(),
                'link' => $indexURL,
            );
            $returnArray['breadcrumbs'] = $breadcrumbs;
        }
        return $returnArray;
    }

    public function actionImportedPaymentsIndex($attributes) {
        if (!isset($attributes['type'])) {
            $type = 'payment';
        } else {
            $type = $attributes['type'];
        }
        if (isset($attributes['pageNumber'])) {
            $pageNumber = $attributes['pageNumber'];
        } else {
            $pageNumber = 1;
        }
        if (isset($attributes['queryId'])) {
            $queryId = $attributes['queryId'];
        } else {
            $queryId = NULL;
        }
        $typeModel = TypeModelFactory::getTypeModelByRef($type, 'payment_type');
        if (empty($typeModel)) {
            GI_URLUtils::redirectToError(4000);
        }
        $typeRef = $typeModel->getProperty('ref');
        $samplePayment = PaymentFactory::buildNewModel($type);
        if (!$samplePayment->isIndexViewable()) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $groupPaymentSearch = GroupPaymentFactory::search()
                ->filterByTypeRef('imported')
                ->filter('default_payment_type_ref', $typeRef)
                ->orderBy('void', 'ASC')
                ->orderBy('date', 'DESC');

        $groupPaymentSearch
                ->groupBy('id')
                ->setPageNumber($pageNumber)
                ->setItemsPerPage(ProjectConfig::getUITableItemsPerPage())
                ->setQueryId($queryId);

        $samplePaymentClass = get_class($samplePayment);
        $sampleGroupPayment = GroupPaymentFactory::buildNewModel('imported');
        $sampleGroupPaymentClass = get_class($sampleGroupPayment);
        $searchView = $samplePaymentClass::getSearchForm($groupPaymentSearch, $sampleGroupPayment, $type, array(
            'controller'=>'accounting',
            'action'=>'importedPaymentsIndex',
            'type'=>$type
        ));
        $searchView->setUseAjax(true);
        $groupPayments = $groupPaymentSearch->select();
        $pageBar = $groupPaymentSearch->getPageBar(array(
            'controller' => 'accounting',
            'action' => 'importedPaymentsIndex',
            'type' => $type
        ));
        $pageBar->setUseAjax(true);
        $uiTableCols = $sampleGroupPaymentClass::getUITableCols();
        $uiTableView = new UITableView($groupPayments, $uiTableCols, $pageBar);
        $uiTableView->setLoadMore(false);
        $uiTableView->setTableWrapId($sampleGroupPayment->getTableWrapId());
        $searchView->setTargetElementId($sampleGroupPayment->getTableWrapId());
        if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
            $returnArray = array();
            if (isset($attributes['tabbed']) && $attributes['tabbed'] == 1) {
                $view = $samplePaymentClass::getImportedPaymentsIndexView($groupPayments, $uiTableView, $samplePayment, $searchView);
                $returnArray = GI_Controller::getReturnArray($view);
            } else if (isset($attributes['onlyRows']) && $attributes['onlyRows'] == 1) {
                $returnArray['uiTableRows'] = $uiTableView->getRows();
            } else {
                $returnArray['uiTable'] = $uiTableView->getHTMLView();
            }
        } else {

            $view = $samplePaymentClass::getImportedPaymentsIndexView($groupPayments, $uiTableView, $samplePayment, $searchView);
            $returnArray = GI_Controller::getReturnArray($view);
            if (!empty($groupPayments)) {
                $exampleGroupPayment = $groupPayments[0];
            } else {
                $exampleGroupPayment = GroupPaymentFactory::buildNewModel('cheque');
            }
            $breadcrumbs = $exampleGroupPayment->getBreadcrumbs();
            $indexURL = GI_URLUtils::buildURL(array(
                        'controller' => 'accounting',
                        'action' => 'importedPaymentsIndex',
                        'type' => $type
            ));
            $breadcrumbs[] = array(
                'label' => 'Imported Payments',
                'link' => $indexURL,
            );
            $returnArray['breadcrumbs'] = $breadcrumbs;
        }
        return $returnArray;
    }



    public function actionViewPayment($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $id = $attributes['id'];
        $groupPayment = GroupPaymentFactory::getModelById($id);
        if (!$groupPayment->isViewable()) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $view = $groupPayment->getDetailView();
        $breadcrumbs = array();
        $samplePayment = PaymentFactory::buildNewModel($groupPayment->getProperty('default_payment_type_ref'));
        if (!empty($samplePayment)) {
            $breadcrumbs = $samplePayment->getGroupPaymentBreadcrumbs($groupPayment, '');
            $view->setContactLabel($samplePayment->getGroupPaymentContactLabel());
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['breadcrumbs'] = $breadcrumbs;
        return $returnArray;
    }

    public function actionAddPayment($attributes) {
        if (!isset($attributes['type'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $type = $attributes['type']; //payment type - expense or income
        $gpLocked = false;
        if (isset($attributes['gp'])) {
            $gpType = $attributes['gp'];
            $gpLocked = true;
        } else {
            $gpType = 'cheque';
        }
        $groupPayment = GroupPaymentFactory::buildNewModel($gpType);
        if (!$groupPayment->isAddable()) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $defaultCurrencyId = ProjectConfig::getDefaultCurrencyId();
        $groupPayment->setProperty('currency_id', $defaultCurrencyId);
        $examplePayment = PaymentFactory::buildNewModel($type);
        $form = new GI_Form('add_group_payment');
        $view = $groupPayment->getFormView($form, $examplePayment);
        $view->setGPTypeLocked($gpLocked);
        $payments = $groupPayment->getPayments($form);
        if (!$form->wasSubmitted()) {
            for ($i=0;$i<count($payments);$i++) {
                $payments[$i]->setFieldSuffix($i);
            }
        }
        $view->setPayments($payments);
        $view->buildForm();
        if ($form->wasSubmitted() && $form->validate()) {
            $updatedGroupPayment = $groupPayment->handleFormSubmission($form, $examplePayment);
            if (!empty($updatedGroupPayment)) {
                GI_URLUtils::redirect(array(
                    'controller' => 'accounting',
                    'action' => 'viewPayment',
                    'id' => $updatedGroupPayment->getProperty('id')
                ));
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $breadcrumbs = $examplePayment->getGroupPaymentBreadcrumbs($groupPayment, 'Add');
//        $breadcrumbs[] = array(
//            'label'=>'Add Payment',
//            'link'=>  GI_URLUtils::buildURL($attributes)
//        );
        $returnArray['breadcrumbs'] = $breadcrumbs;
        return $returnArray;
    }

    public function actionEditPayment($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $id = $attributes['id'];
        $groupPayment = GroupPaymentFactory::getModelById($id);
        if (empty($groupPayment) || !$groupPayment->isEditable()) {
            GI_URLUtils::redirectToError(2000);
        }
        $type = $groupPayment->getProperty('default_payment_type_ref');
        $examplePayment = PaymentFactory::buildNewModel($type);
        $form = new GI_Form('edit_group_payment');
        //$view = $examplePayment->getGroupPaymentFormView($form, $groupPayment);
        $view = $groupPayment->getFormView($form, $examplePayment);
        $payments = $groupPayment->getPayments($form);
        if (!$form->wasSubmitted()) {
            for ($i = 0; $i < count($payments); $i++) {
                $payments[$i]->setFieldSuffix($i);
            }
        }
        if ($form->wasSubmitted() && $form->validate()) {
            $updatedGroupPayment = $groupPayment->handleFormSubmission($form, $examplePayment);
            if (!empty($updatedGroupPayment)) {
                GI_URLUtils::redirect(array(
                    'controller' => 'accounting',
                    'action' => 'viewPayment',
                    'id' => $updatedGroupPayment->getProperty('id')
                ));
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $breadcrumbs = $examplePayment->getGroupPaymentBreadcrumbs($groupPayment, 'Edit');
//        $breadcrumbs[] = array(
//            'label'=>'#' . $groupPayment->getProperty('transaction_number'),
//            'link'=>  GI_URLUtils::buildURL(array(
//                'controller'=>'accounting',
//                'action'=>'viewPayment',
//                'id'=>$id,
//            )),
//        );
        $breadcrumbs[] = array(
            'label'=>'Edit Payment',
            'link'=>  GI_URLUtils::buildURL($attributes)
        );
        $returnArray['breadcrumbs'] = $breadcrumbs;
        return $returnArray;
    }

    /**
     * @deprecated
     * @param type $attributes
     * @return type
     */
    public function actionLockReport($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $id = $attributes['id'];
        $report = AccountingReportFactory::getModelById($id);
        $form = new GI_Form('void_payment');
        $view = new AccountingLockFormView($form, $report);
        $success = 0;
        $newUrl = NULL;
        if ($form->wasSubmitted() && $form->validate()) {
            if ($report->lock()) {
                $newUrlArray = array(
                    'controller' => 'admin',
                    'action' => 'reports',
                    'reportId' => $id
                );
                if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
                    $newUrl = GI_URLUtils::buildURL($newUrlArray);
                    $success = 1;
                } else {
                    GI_URLUtils::redirect($newUrlArray);
                }
            } else {
                $view->setLockError('You cannot lock this ' . $report->getSpecificTitle());
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        $returnArray['newUrl'] = $newUrl;
        return $returnArray;
    }

    public function actionAddPaymentLine($attributes) {
        $returnArray = GI_Controller::getReturnArray();
        if (!isset($attributes['ajax']) || $attributes['ajax'] != 1 || !isset($attributes['seq'])) {
            GI_URLUtils::redirectToError(2000);
        }
        if (!isset($attributes['typeRef'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $typeRef = $attributes['typeRef'];
        $seq = $attributes['seq'];
        $payment = PaymentFactory::buildNewModel($typeRef);
        if (empty($payment)) {
            return $returnArray;
        }
        $payment->setFieldSuffix($seq);
        $tempForm = new GI_Form('temp_form');
        $formView = $payment->getFormView($tempForm);
        $formView->setFullView(false);
        $formView->buildForm();
        return array(
            'formRow' => $formView->getHTMLView()
        );
    }

    public function actionAccountsPayable($attributes) {
        //TODO - check permissions
        $view = new AccountingAccountsPayableIndexView();
        if (dbConnection::isModuleInstalled('order')) {
            $view->setShowBillsTab(false);
        }
        if (isset($attributes['tab'])) {
            $currentTab = $attributes['tab'];
            $view->setCurrentTab($currentTab);
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $breadcrumbs = array();
        $breadcrumbs[] = array(
            'label' => 'Accounting',
            'link' => '',
        );
        $breadcrumbs[] = array(
            'label' => 'Expenses',
            'link' => GI_URLUtils::buildURL(array(
                'controller' => 'accounting',
                'action' => 'accountsPayable'
            )),
        );
        $returnArray['breadcrumbs'] = $breadcrumbs;
        return $returnArray;
    }

    public function actionAccountsReceivable($attributes) {
        $view = new AccountingAccountsReceivableIndexView();
        if (isset($attributes['tab'])) {
            $currentTab = $attributes['tab'];
            $view->setCurrentTab($currentTab);
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $breadcrumbs = array();
        $breadcrumbs[] = array(
            'label' => 'Accounting',
            'link' => '',
        );
        $breadcrumbs[] = array(
            'label' => 'Sales',
            'link' => GI_URLUtils::buildURL(array(
                'controller' => 'accounting',
                'action' => 'accountsReceivable'
            )),
        );
        $returnArray['breadcrumbs'] = $breadcrumbs;
        return $returnArray;
    }

    public function actionVoidGroupPayment($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $id = $attributes['id'];
        $groupPayment = GroupPaymentFactory::getModelById($id);
        if (empty($groupPayment) || !$groupPayment->getIsVoidable()) {
            GI_URLUtils::redirectToError(2000);
        }
        $form = new GI_Form('void_group_payment');
        $view = new VoidFormView($form, $groupPayment);
        $view->buildForm();
        $success = 0;
        $newUrl = NULL;
        if ($form->wasSubmitted() && $form->validate()) {
            $voidNotes = filter_input(INPUT_POST, 'void_notes');
            if ($groupPayment->void($voidNotes)) {
                $newUrlAttributes = array(
                    'controller' => 'accounting',
                    'action' => 'viewPayment',
                    'id' => $id
                );
                if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
                    $newUrl = GI_URLUtils::buildURL($newUrlAttributes);
                    $success = 1;
                } else {
                    GI_URLUtils::redirect($newUrlAttributes);
                }
            } else {
                $view->setVoidError('This Payment cannot be voided.');
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if (!empty($newUrl)) {
            $returnArray['newUrl'] = $newUrl;
        }
        return $returnArray;
    }

    public function actionExportExpenses($attributes) {
        if (!Permission::verifyByRef('export_expenses')) {
            GI_URLUtils::redirectToAccessDenied();
        }
        if (isset($attributes['queryId'])) {
            $queryId = $attributes['queryId'];
        } else {
            $queryId = NULL;
        }
        $pageNumber = 1;
        if (isset($attributes['pageNumber'])) {
            $pageNumber = $attributes['pageNumber'];
        }
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
        } else {
            $type = 'bill';
        }
        $itemsPerPage = ProjectConfig::getUITableItemsPerPage();
        $expenseSearch = ExpenseFactory::search()
                ->filter('void', 0)
                ->filter('cancelled', 0)
                ->setPageNumber($pageNumber)
                ->setItemsPerPage($itemsPerPage)
                ->setQueryId($queryId);
        $sampleExpense = ExpenseFactory::buildNewModel($type);
        $expenses = $expenseSearch->orderBy('date', 'DESC')
                ->orderBy('inception', 'DESC')
                ->select();

        $date = GI_Time::getDate();
        $fileName = 'Expense_Report_' . $date;
        $taxRateQBIds = ExpenseFactory::getAllTaxRateQBIdsFromExpenses();
        $uiTableCols = $sampleExpense->getExportUITableCols($taxRateQBIds);
        $csv = new GI_CSV(GI_Sanitize::filename($fileName));
        $csv->setOverWrite(true);
        $addHeader = true;
        if ($pageNumber > 1) {
            $csv->setAddToExisting(true);
            $addHeader = false;
        }
        $csv->setUITableCols($uiTableCols, $addHeader);
        $csv->addModelRows($expenses);
        GI_CSV::setCSVExporting(false);
        $csvFile = $csv->getCSVFilePath();
        $total = $expenseSearch->getCount();
        $totalPages = ceil($total / $itemsPerPage);
        if (isset($attributes['timeTrackerId'])) {
            $timeTrackerId = $attributes['timeTrackerId'];
        } else {
            $timeTrackerId = GI_StringUtils::generateRandomString(8, false, true, true, true, false);
        }
        $nextPageNumber = $pageNumber + 1;
        $nextURL = NULL;
        if ($nextPageNumber <= $totalPages) {
            $nextPageURL = array(
                'controller' => 'accounting',
                'action' => 'exportExpenses',
                'pageNumber' => $nextPageNumber,
                'timeTrackerId' => $timeTrackerId,
            );
            if (!empty($queryId)) {
                $nextPageURL['queryId'] = $queryId;
            }
            $nextURL = GI_URLUtils::buildURL($nextPageURL);
        }
        if ($totalPages <= 0) {
            $totalPages = 1;
        }
        $percentage = $pageNumber / $totalPages * 100;
        $view = new GenericProgressBarView();
        $view->setPercentage($percentage);
        $view->setTimeTrackerId($timeTrackerId);
        $view->setProgressBarTitle('Exporting Expense Report');
        $view->setProgressDesc('Preparing Expense Report.');
        if (!empty($nextURL)) {
            $view->setNextURL($nextURL);
        } else {
            $view->setProgressForward(false);
            $view->addHTML('<p>Your Expense Report is ready to <a href="' . $csvFile . '" target="_blank" title="Download Expense Report">download</a>.</p>');
        }
        $returnArray = GI_Controller::getReturnArray($view);
        return $returnArray;
    }

    public function actionExportIncomes($attributes) {
        if (!Permission::verifyByRef('export_incomes')) {
            GI_URLUtils::redirectToAccessDenied();
        }
        if (isset($attributes['queryId'])) {
            $queryId = $attributes['queryId'];
        } else {
            $queryId = NULL;
        }
        $pageNumber = 1;
        if (isset($attributes['pageNumber'])) {
            $pageNumber = $attributes['pageNumber'];
        }
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
        } else {
            $type = 'invoice';
        }
        $itemsPerPage = ProjectConfig::getUITableItemsPerPage();
        $incomeSearch = IncomeFactory::search()
                ->filter('void', 0)
                ->filter('cancelled', 0)
                ->setPageNumber($pageNumber)
                ->setItemsPerPage($itemsPerPage)
                ->setQueryId($queryId);
        $sampleIncome = IncomeFactory::buildNewModel($type);
        $incomes = $incomeSearch->orderBy('date', 'DESC')
                ->orderBy('inception', 'DESC')
                ->select();

        $date = GI_Time::getDate();
        $fileName = 'Income_Report_' . $date;
        $uiTableCols = $sampleIncome->getExportUITableCols();
        $csv = new GI_CSV(GI_Sanitize::filename($fileName));
        $csv->setOverWrite(true);
        $addHeader = true;
        if ($pageNumber > 1) {
            $csv->setAddToExisting(true);
            $addHeader = false;
        }
        $csv->setUITableCols($uiTableCols, $addHeader);
        $csv->addModelRows($incomes);
        GI_CSV::setCSVExporting(false);
        $csvFile = $csv->getCSVFilePath();
        $total = $incomeSearch->getCount();
        $totalPages = ceil($total / $itemsPerPage);
        if (isset($attributes['timeTrackerId'])) {
            $timeTrackerId = $attributes['timeTrackerId'];
        } else {
            $timeTrackerId = GI_StringUtils::generateRandomString(8, false, true, true, true, false);
        }
        $nextPageNumber = $pageNumber + 1;
        $nextURL = NULL;
        if ($nextPageNumber <= $totalPages) {
            $nextPageURL = array(
                'controller' => 'accounting',
                'action' => 'exportIncomes',
                'pageNumber' => $nextPageNumber,
                'timeTrackerId' => $timeTrackerId,
            );
            if (!empty($queryId)) {
                $nextPageURL['queryId'] = $queryId;
            }
            $nextURL = GI_URLUtils::buildURL($nextPageURL);
        }
        if ($totalPages <= 0) {
            $totalPages = 1;
        }
        $percentage = $pageNumber / $totalPages * 100;
        $view = new GenericProgressBarView();
        $view->setPercentage($percentage);
        $view->setTimeTrackerId($timeTrackerId);
        $view->setProgressBarTitle('Exporting Income Report');
        $view->setProgressDesc('Preparing Income Report.');
        if (!empty($nextURL)) {
            $view->setNextURL($nextURL);
        } else {
            $view->setProgressForward(false);
            $view->addHTML('<p>Your Income Report is ready to <a href="' . $csvFile . '" target="_blank" title="Download Income Report">download</a>.</p>');
        }
        $returnArray = GI_Controller::getReturnArray($view);
        return $returnArray;
    }

    public function actionExportPayments($attributes) {
        if (!(Permission::verifyByRef('export_income_payments') || Permission::verifyByRef('export_expense_payments'))) {
            GI_URLUtils::redirectToAccessDenied();
        }
        if (isset($attributes['type'])) {
            $typeRef = $attributes['type'];
        } else {
            $typeRef = 'payment';
        }
        if (isset($attributes['queryId'])) {
            $queryId = $attributes['queryId'];
        } else {
            $queryId = NULL;
        }
        $pageNumber = 1;
        if (isset($attributes['pageNumber'])) {
            $pageNumber = $attributes['pageNumber'];
        }
        $unappliedPayments = array();
        if ($pageNumber == 1) {
            $groupPaymentTableName = GroupPaymentFactory::getDbPrefix() . 'group_payment';
            $unappliedGroupPaymentSearch = GroupPaymentFactory::search()
                    ->join('payment', 'group_payment_id', $groupPaymentTableName, 'id', 'PAY', 'left')
                    ->filterNull('PAY.status')
                    ->filter('void', 0)
                    ->filter('cancelled', 0)
                    ->groupBy('id');
            $unappliedGroupPayments = $unappliedGroupPaymentSearch->select();
            if (!empty($unappliedGroupPayments)) {
                foreach ($unappliedGroupPayments as $unappliedGroupPayment) {
                   $unappliedPayment = PaymentFactory::createPaymentFromGroupPaymentBalance($typeRef, $unappliedGroupPayment);
                   if (!empty($unappliedPayment)) {
                       $unappliedPayments[] = $unappliedPayment;
                   }
               }
           }
        }
        $itemsPerPage = ProjectConfig::getUITableItemsPerPage();
        $paymentSearch = PaymentFactory::search()
                ->filterByTypeRef($typeRef)
                ->setPageNumber($pageNumber)
                ->setItemsPerPage($itemsPerPage)
                ->setQueryId($queryId);
        $samplePayment = PaymentFactory::buildNewModel($typeRef);
        $payments = $paymentSearch->orderBy('date', 'DESC')
                ->orderBy('inception', 'DESC')
                ->select();
        foreach ($payments as $key=>$payment) {
            if (!empty($payment->getProperty('id'))) {
                $groupPaymentId = $payment->getProperty('group_payment_id');
                $otherPaymentsSearch = PaymentFactory::search()
                        ->filterByTypeRef($typeRef)
                        ->filter('group_payment_id', $groupPaymentId)
                        ->orderBy('inception', 'DESC')
                        ->setItemsPerPage(1);
                $otherPaymentArray = $otherPaymentsSearch->select();
                if (!empty($otherPaymentArray)) {
                    $otherPayment = $otherPaymentArray[0];
                    if ($otherPayment->getProperty('id') == $payment->getProperty('id')) {
                        //this payment is the most recent
                        $groupPayment = $payment->getGroupPayment();
                        if (!empty($groupPayment)) {
                            $blankPayment = PaymentFactory::createPaymentFromGroupPaymentBalance($typeRef, $groupPayment);
                            if (!empty($blankPayment)) {
                                array_splice($payments, $key+1, 0, array($blankPayment));
                            }
                        }
                    }
                }
            }
        }
        $date = GI_Time::getDate();
        $typeTitle = $samplePayment->getTypeTitle();
        $fileName = $typeTitle . '_Payment_Report_' . $date;
        $uiTableCols = $samplePayment->getExportUITableCols();
        $csv = new GI_CSV(GI_Sanitize::filename($fileName));
        $csv->setOverWrite(true);
        $addHeader = true;
        if ($pageNumber > 1) {
            $csv->setAddToExisting(true);
            $addHeader = false;
        }
        $csv->setUITableCols($uiTableCols, $addHeader);
        $csv->addModelRows(array_merge($unappliedPayments, $payments));
        GI_CSV::setCSVExporting(false);
        $csvFile = $csv->getCSVFilePath();
        $total = $paymentSearch->getCount();
        $totalPages = ceil($total / $itemsPerPage);
        if (isset($attributes['timeTrackerId'])) {
            $timeTrackerId = $attributes['timeTrackerId'];
        } else {
            $timeTrackerId = GI_StringUtils::generateRandomString(8, false, true, true, true, false);
        }
        $nextPageNumber = $pageNumber + 1;
        $nextURL = NULL;
        if ($nextPageNumber <= $totalPages) {
            $nextPageURL = array(
                'controller' => 'accounting',
                'action' => 'exportPayments',
                'pageNumber' => $nextPageNumber,
                'type' => $typeRef,
                'timeTrackerId' => $timeTrackerId,
            );
            if (!empty($queryId)) {
                $nextPageURL['queryId'] = $queryId;
            }
            $nextURL = GI_URLUtils::buildURL($nextPageURL);
        }
        if ($totalPages <= 0) {
            $totalPages = 1;
        }
        $percentage = $pageNumber / $totalPages * 100;
        $view = new GenericProgressBarView();
        $view->setPercentage($percentage);
        $view->setTimeTrackerId($timeTrackerId);
        $view->setProgressBarTitle('Exporting ' . $typeTitle . ' Payment Report');
        $view->setProgressDesc('Preparing ' . $typeTitle . ' Payment Report.');
        if (!empty($nextURL)) {
            $view->setNextURL($nextURL);
        } else {
            $view->setProgressForward(false);
            $view->addHTML('<p>Your ' . $typeTitle . ' Payment Report is ready to <a href="' . $csvFile . '" target="_blank" title="Download ' . $typeTitle . 'Payment Report">download</a>.</p>');
        }
        $returnArray = GI_Controller::getReturnArray($view);
        return $returnArray;
    }

    public function actionExportARInvoices($attributes) {
        if (!Permission::verifyByRef('export_ar_invoices')) {
            GI_URLUtils::redirectToAccessDenied();
       }
        if (isset($attributes['queryId'])) {
            $queryId = $attributes['queryId'];
        } else {
            $queryId = NULL;
        }
        $pageNumber = 1;
        if (isset($attributes['pageNumber'])) {
            $pageNumber = $attributes['pageNumber'];
        }
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
        } else {
            $type = 'inv';
        }
        $itemsPerPage = ProjectConfig::getUITableItemsPerPage();

        $invoiceTableName = InvoiceFactory::getDbPrefix() . 'invoice';

        $invoiceSearch = InvoiceFactory::search();

        $itemJoin = $invoiceSearch->createJoin('item_link_to_income', 'item_id', $invoiceTableName, 'id', 'ILTI');
        $itemJoin->filter('ILTI.table_name', 'invoice');

        $invoiceSearch->join('income', 'id', 'ILTI', 'income_id', 'INCOME');
        $invoiceSearch->filterNull('INCOME.paid_in_full')
                ->filter('INCOME.void', 0)
                ->filter('INCOME.cancelled', 0);

        if (isset($attributes['cat'])) {
            $cat = $attributes['cat'];
        } else {
            $cat = 'due_date';
        }
        $invoiceSearch->orderBy($cat, 'ASC');

        $invoiceSearch->setPageNumber($pageNumber)
                ->setItemsPerPage($itemsPerPage)
                ->setQueryId($queryId);
        $invoices = $invoiceSearch->select();
        $total = $invoiceSearch->getCount();
        $totalPages = ceil($total / $itemsPerPage);
        if (isset($attributes['timeTrackerId'])) {
            $timeTrackerId = $attributes['timeTrackerId'];
        } else {
            $timeTrackerId = GI_StringUtils::generateRandomString(8, false, true, true, true, false);
        }
        $sampleInvoice = InvoiceFactory::buildNewModel($type);
        $date = GI_Time::getDate();
        $fileName = 'AR_Invoice_Report_' . $date;
        $uiTableCols = $sampleInvoice->getARExportUITableCols();
        $csv = new InvoiceARCSV(GI_Sanitize::filename($fileName));
        if ($pageNumber == 1) {
            $csv->resetSessionVariables();
        }
        if ($pageNumber == $totalPages) {
            $csv->setIsLastPage(true);
        }
        $csv->setOverWrite(true);
        $csv->setDayRangeBreaks(ProjectConfig::getDefaultARInvoiceExportDayRangeBreaks());
        if ($cat == 'date') {
            $csv->setUseInvoiceDate(true);
        }
        $addHeader = true;
        if ($pageNumber > 1) {
            $csv->setAddToExisting(true);
            $addHeader = false;
        }
        $csv->setUITableCols($uiTableCols, $addHeader);
        $csv->addModelRows($invoices);
        GI_CSV::setCSVExporting(false);
        $csvFile = $csv->getCSVFilePath();

        $nextPageNumber = $pageNumber + 1;
        $nextURL = NULL;
        if ($nextPageNumber <= $totalPages) {
            $nextPageURL = array(
                'controller' => 'accounting',
                'action' => 'exportARInvoices',
                'pageNumber' => $nextPageNumber,
                'timeTrackerId' => $timeTrackerId
            );
            if (!empty($queryId)) {
                $nextPageURL['queryId'] = $queryId;
            }
            $nextURL = GI_URLUtils::buildURL($nextPageURL);
        }
        if ($totalPages <= 0) {
            $totalPages = 1;
        }
        $percentage = $pageNumber / $totalPages * 100;
        $view = new GenericProgressBarView();
        $view->setPercentage($percentage);
        $view->setTimeTrackerId($timeTrackerId);
        $view->setProgressBarTitle('Exporting A/R Invoice Report');
        $view->setProgressDesc('Preparing A/R Invoice Report.');
        if (!empty($nextURL)) {
            $view->setNextURL($nextURL);
        } else {
            $view->setProgressForward(false);
            $view->addHTML('<p>Your A/R Invoice Report is ready to <a href="' . $csvFile . '" target="_blank" title="Download Income Report">download</a>.</p>');
        }
        $returnArray = GI_Controller::getReturnArray($view);
        return $returnArray;
    }

    public function actionExport($attributes) {
        $atLeastOnePermission = false;
        $exportPermissions = array(
            'export_incomes' => false,
            'export_income_payments' => false,
            'export_expenses' => false,
            'export_expense_payments' => false,
            'export_ar_invoices' => false,
        );
        foreach ($exportPermissions as $key => $value) {
            $newValue = Permission::verifyByRef($key);
            if ($newValue) {
                $atLeastOnePermission = true;
            }
            $exportPermissions[$key] = $newValue;
        }
        if (!$atLeastOnePermission) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $form = new GI_Form('export');
        $view = new AccountingExportFormView($form);
        $fiscalYearOptions = GI_Time::getFiscalYearOptionsArray(ProjectConfig::getSystemLiveDate());
        $view->setFiscalYearOptions($fiscalYearOptions);
        $exportTypeOptions = array();
        if ($exportPermissions['export_incomes']) {
            $exportTypeOptions['income'] = 'Incomes';
        }
        if (!ProjectConfig::getIsQuickbooksIntegrated() && $exportPermissions['export_income_payments']) {
            $exportTypeOptions['income_payments'] = 'Income Payments';
        }
        if ($exportPermissions['export_expenses']) {
            $exportTypeOptions['expense'] = 'Expenses';
        }
        if (!ProjectConfig::getIsQuickbooksIntegrated() && $exportPermissions['export_expense_payments']) {
            $exportTypeOptions['expense_payments'] = 'Expense Payments';
        }
        if (!ProjectConfig::getIsQuickbooksIntegrated() && $exportPermissions['export_ar_invoices']) {
            $exportTypeOptions['ar_invoices'] = 'A/R Invoices';
        }
        $view->setExportTypeOptions($exportTypeOptions);
        $view->buildForm();
        if ($form->wasSubmitted() && $form->validate()) {
           $exportType = filter_input(INPUT_POST, 'export_type');
//           $fiscalYear = filter_input(INPUT_POST, 'fiscal_year');
//           $fiscalYearArray = explode('_', $fiscalYear);
//           $startYear = $fiscalYearArray[0];
//           $endYear = $fiscalYearArray[1];
//           $fiscalYearStartMonthAndDay = ProjectConfig::getFiscalYearStartMonthAndDay();
//           $startDateTime = new DateTime($startYear . '-' . $fiscalYearStartMonthAndDay . ' 00:00:00');
//           $endDateTime = new DateTime($endYear . '-' . $fiscalYearStartMonthAndDay . ' 00:00:00');
//           $endDateTime->modify("-1 days");
           $type = NULL;
           $redirectAttributes = array();
           switch ($exportType) {
               case 'income':
                   $action = 'exportIncomes';
                    break;
                case 'income_payments':
                    $action = 'exportPayments';
                    $type = 'income';
                    break;
                case 'expense':
                    $action = 'exportExpenses';
                    break;
                case 'expense_payments':
                    $action = 'exportPayments';
                    $type = 'expense';
                    break;
               case 'ar_invoices':
                   $action = 'exportARInvoices';
                   $exportARInvoicesCat = filter_input(INPUT_POST, 'export_ar_invoices_cat');
                   if (!empty($exportARInvoicesCat)) {
                       $redirectAttributes['cat'] = $exportARInvoicesCat;
                   } else {
                       $redirectAttributes['cat'] = 'due_date';
                   }
                    break;
            }
            $redirectAttributes['controller'] = 'accounting';
            $redirectAttributes['action'] = $action;

            if (!empty($type)) {
                $redirectAttributes['type'] = $type;
            }
            GI_URLUtils::redirect($redirectAttributes);
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $breadcrumbs = array(
            array(
                'label' => 'Accounting',
                'link' => ''
            ),
            array(
                'label' => 'Exports',
                'link' => GI_URLUtils::buildURL($attributes)
            ),
        );
        $returnArray['breadcrumbs'] = $breadcrumbs;
        return $returnArray;
    }

    public function actionGetCurrencyExchangeRate($attributes) {
        if (!isset($attributes['sId']) || !isset($attributes['tId']) || (!isset($attributes['ajax']) && ($attributes['ajax'] == 1))) {
            GI_URLUtils::redirectToError();
        }
        $sourceCurrency = CurrencyFactory::getModelById($attributes['sId']);
        $targetCurrency = CurrencyFactory::getModelById($attributes['tId']);
        if (empty($sourceCurrency) || empty($targetCurrency)) {
            GI_URLUtils::redirectToError();
        }
        if ($sourceCurrency->getProperty('id') === $targetCurrency->getProperty('id')) {
            return array(
                'rate' => 1
            );
        }
        $exchangeRate = CurrencyFactory::determineConversionRate($sourceCurrency, $targetCurrency);
        return array(
            'rate' => $exchangeRate
        );
    }

   
    public function actionGetRegionData($attributes){
        $regionId = NULL;
        $regionCode = NULL;
        $countryCode = NULL;
        if (isset($attributes['regionId']) && !empty($attributes['regionId'])){
            $regionId = $attributes['regionId'];
            $region = RegionFactory::getModelById($regionId);
            if($region){
                $regionCode = $region->getRegionCode();
                $countryCode = $region->getCountryCode();
            }
        } else {
            if (isset($attributes['regionCode']) && !empty($attributes['regionCode'])) {
                $regionCode = GeoDefinitions::cleanRegionCode($attributes['regionCode']);
            }
            if (isset($attributes['countryCode']) && !empty($attributes['countryCode'])) {
                $countryCode = $attributes['countryCode'];
            }
            if (!empty($countryCode) && !empty($regionCode)) {
                $region = RegionFactory::getModelByCodes($countryCode, $regionCode);
                if (!empty($region)) {
                    $regionId = $region->getId();
                }
            }
        }
        return array(
            'regionId' => $regionId,
            'regionCode' => $regionCode,
            'countryCode' => $countryCode
        );
    }

    public function actionPrintCreditNote($attributes) {
        if (!isset($attributes['id']) || empty($attributes['id'])) {
            return NULL;
        }
        $id = $attributes['id'];
        $groupPayment = GroupPaymentFactory::getModelById($id);
        if ($groupPayment->isPrintable()) {
            $groupPayment->printOutput();
        }
    }

    public function actionPaymentAccountIndex($attributes) {
        if (isset($attributes['pageNumber'])) {
            $pageNumber = $attributes['pageNumber'];
        } else {
            $pageNumber = 1;
        }
        if (isset($attributes['queryId'])) {
            $queryId = $attributes['queryId'];
        } else {
            $queryId = NULL;
        }
        $type = 'account';
        $samplePaymentAccount = PaymentAccountFactory::buildNewModel($type);
        if (!$samplePaymentAccount->isIndexViewable()) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $paymentAccountSearch = PaymentAccountFactory::search();
       //         ->filter('default_payment_type_ref', $type);

        $paymentAccountSearch
                ->groupBy('id')
                ->setPageNumber($pageNumber)
                ->setItemsPerPage(ProjectConfig::getUITableItemsPerPage())
                ->setQueryId($queryId);

        $samplePaymentAccountClass = get_class($samplePaymentAccount);

      //  $searchView = $samplePaymentAccountClass::getSearchForm($paymentAccountSearch, $samplePaymentAccount, $type);
      //  $searchView->setUseAjax(true);
        $searchView = NULL;
        $paymentAccounts = $paymentAccountSearch->select();
        $pageBar = $paymentAccountSearch->getPageBar(array(
            'controller' => 'accounting',
            'action' => 'paymentAccountIndex',
        ));
        $pageBar->setUseAjax(true);
        $uiTableCols = $samplePaymentAccountClass::getUITableCols();
        $uiTableView = new UITableView($paymentAccounts, $uiTableCols, $pageBar);
        $uiTableView->setLoadMore(false);
        $uiTableView->setTableWrapId($samplePaymentAccount->getTableWrapId());
       // $searchView->setTargetElementId($sampleGroupPayment->getTableWrapId());
        if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
            $returnArray = array();
            if (isset($attributes['tabbed']) && $attributes['tabbed'] == 1) {
                $view = $samplePaymentAccountClass::getIndexView($paymentAccounts, $uiTableView, $samplePaymentAccount, $searchView);
                $returnArray = GI_Controller::getReturnArray($view);
            } else if (isset($attributes['onlyRows']) && $attributes['onlyRows'] == 1) {
                $returnArray['uiTableRows'] = $uiTableView->getRows();
            } else {
                $returnArray['uiTable'] = $uiTableView->getHTMLView();
            }
        } else {
            $view = $samplePaymentAccountClass::getIndexView($paymentAccounts, $uiTableView, $samplePaymentAccount, $searchView);
            $returnArray = GI_Controller::getReturnArray($view);
            if (!empty($paymentAccounts)) {
                $exampleGroupPayment = $paymentAccounts[0];
            } else {
                $exampleGroupPayment = GroupPaymentFactory::buildNewModel('cheque');
            }
            $breadcrumbs = $exampleGroupPayment->getBreadcrumbs();
            $indexURL = GI_URLUtils::buildURL(array(
                        'controller' => 'accounting',
                        'action' => 'paymentAccountIndex',
            ));
            $breadcrumbs[] = array(
                'label' =>$samplePaymentAccount->getIndexTitle(),
                'link' => $indexURL,
            );
            $returnArray['breadcrumbs'] = $breadcrumbs;
        }
        return $returnArray;
    }

    public function actionAddPaymentAccount($attributes) {
        $paymentAccount = PaymentAccountFactory::buildNewModel('account');
        if (!$paymentAccount->isAddable()) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $defaultCurrencyId = ProjectConfig::getDefaultCurrencyId();
        $paymentAccount->setProperty('currency_id', $defaultCurrencyId);
        $form = new GI_Form('add_payment_account');
        $view = $paymentAccount->getFormView($form);
        $view->buildForm();
        $success = 0;
        $newUrl = NULL;
        if ($paymentAccount->handleFormSubmission($form)) {
            $newUrlAttributes = array(
                'controller' => 'accounting',
                'action' => 'paymentAccountIndex',
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

    public function actionEditPaymentAccount($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $id = $attributes['id'];
        $paymentAccount = PaymentAccountFactory::getModelById($id);
        if (empty($paymentAccount)) {
            GI_URLUtils::redirectToError(2000);
        }
        if (!$paymentAccount->isEditable()) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $form = new GI_Form('edit_payment_account');
        $view = $paymentAccount->getFormView($form);
        $view->buildForm();
        $success = 0;
        $newUrl = NULL;
        if ($paymentAccount->handleFormSubmission($form)) {
            $newUrlAttributes = array(
                'controller' => 'accounting',
                'action' => 'paymentAccountIndex',
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

    public function actionDeletePaymentAccount($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $id = $attributes['id'];
        $paymentAccount = PaymentAccountFactory::getModelById($id);
        if (empty($paymentAccount) || !$paymentAccount->isDeleteable()) {
            GI_URLUtils::redirectToError(2000);
        }
        $form = new GI_Form('delete_payment_account');
        $view = new GenericAcceptCancelFormView($form);
        $view->setSubmitButtonLabel('Delete Account');
        $view->setModalHeader('QB Settings');
        $view->setHeaderText('Delete Account');
        $view->setMessageText('Are you sure you wish to delete ' . $paymentAccount->getProperty('name') . '?');
        $view->buildForm();
        $success = 0;
        $newUrl = NULL;
        if ($form->wasSubmitted() && $form->validate()) {
            if ($paymentAccount->softDelete()) {
                $newUrlAttributes = array(
                    'controller' => 'accounting',
                    'action' => 'paymentAccountIndex',
                );
                if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
                    $newUrl = GI_URLUtils::buildURL($newUrlAttributes);
                    $success = 1;
                } else {
                    GI_URLUtils::redirect($newUrlAttributes);
                }
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if (!empty($newUrl)) {
            $returnArray['newUrl'] = $newUrl;
        }
        return $returnArray;
    }

    public function actionHandleQBOAuth2($attributes) {
        $socketUserId = Login::getSocketUserId();
        $authorizationCode = filter_input(INPUT_GET, 'code');
        $realmId = filter_input(INPUT_GET, 'realmId');
        $franchiseId = filter_input(INPUT_GET, 'state');
        if ($franchiseId == 'none') {
            $franchiseId = 0;
        }
        
        $loginHelper = new \QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper(ProjectConfig::getQBClientId(), ProjectConfig::getQBClientSecret(), ProjectConfig::getQBRedirectURL(), ProjectConfig::getQBScope());
        $token = $loginHelper->exchangeAuthorizationCodeForToken($authorizationCode, $realmId);
        $tokenKey = $token->getAccessToken();
        $refreshTokenKey = $token->getRefreshToken();
        $tokenKeys = array(
            'token_key' => $tokenKey,
            'refresh_token_key' => $refreshTokenKey,
            'realm_id' => $realmId,
        );
        apcu_store('qb_token_keys_' . $franchiseId, $tokenKeys);

        $qbSettingsSearch = SettingsFactory::search();
        $qbSettingsSearch->filterByTypeRef('qb')
                ->filter('ref', 'quickbooks')
                ->ignoreFranchise('settings')
                ->filter('settings_qb.realm_id', $realmId);
        $qbSettingsResult = $qbSettingsSearch->select();
        if (!empty($qbSettingsResult)) {
            $qbSettings = $qbSettingsResult[0];
        } else {
            $qbSettings = SettingsFactory::buildNewModel('qb');
            $qbSettings->setProperty('ref', 'quickbooks');
            $qbSettings->setProperty('title', 'Quickbooks');
            $qbSettings->setProperty('settings_qb.cust_data_req_update', 0);
            $qbSettings->setProperty('settings_qb.realm_id', $realmId);
            $autoSalesTax = 0;
            if (QBTaxCodeFactory::getTaxingUsesQBAst($franchiseId)) {
                $autoSalesTax = 1;
            }
            $qbSettings->setProperty('settings_qb.auto_sales_tax', $autoSalesTax);
            if (!empty($franchiseId)) {
                $qbSettings->setProperty('franchise_id', $franchiseId);
            }
        }
        $qbSettings->setProperty('settings_qb.token_key', $tokenKey);
        $qbSettings->setProperty('settings_qb.refresh_token_key', $refreshTokenKey);
        $qbSettings->save();

        GI_URLUtils::redirect(array(
            'controller' => 'accounting',
            'action' => 'connectedToQB',
            'socketUserId' => $socketUserId
        ));
    }

    public function actionConnectedToQB($attributes) {
        if(isset($attributes['socketUserId'])){
            Notification::qbConnected($attributes['socketUserId']);
            die('Successfully connected to QuickBooks, you may now close this window.');
        }
        $view = new AdminEchoView();
        $view->echoThis('Successfully connected to QB');

        $returnArray = GI_Controller::getReturnArray($view);
        return $returnArray;
    }

    public function actionGetQBButton($attributes){
        $qbBtn = QBConnection::getConnectToQuickbooksButton();
        $returnArray = GI_Controller::getReturnArray();
        $returnArray['qbBtn'] = $qbBtn;
        return $returnArray;
    }

    /**
     * @deprecated
     * @param type $attributes
     * @return type
     */
    public function actionDisconnectFromQB($attributes){
        SessionService::unsetValue('qb_token');
        QBConnection::removeInstance();
        $qbBtn = QBConnection::getConnectToQuickbooksButton();
        $returnArray = GI_Controller::getReturnArray();
        $returnArray['success'] = 1;
        $returnArray['qbBtn'] = $qbBtn;
        return $returnArray;
    }

    public function actionExportAdjustmentsIndex($attributes) {
        if (!Permission::verifyByRef('export_adjustments_to_quickbooks')) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $searchForm = new GI_Form('export_adjustments_search');
        $searchStart = NULL;
        $searchEnd = NULL;

        if ($searchForm->wasSubmitted()) {
            $searchStart = filter_input(INPUT_POST, 'search_start');
            $searchEnd = filter_input(INPUT_POST, 'search_end');
        } else {
            $fiscalDates = GI_Time::getFiscalYearStartAndEndDates();
            if($fiscalDates['start']){
                $searchStartObj = $fiscalDates['start'];
                $searchStart = GI_Time::formatDateTime($searchStartObj, 'date');
            }
            
            if($fiscalDates['end']){
                $searchEndObj = $fiscalDates['end'];
                $searchEnd = GI_Time::formatDateTime($searchEndObj, 'date');
            }
        }
        
        $form = new GI_Form('export_adjustments');
        $form->setFormAction(GI_URLUtils::buildURL(array(
            'controller'=>'accounting',
            'action'=>'exportAdjustmentsToQuickbooks'
        )));
        
        $view = new ExportAdjustmentsToQuickbooksIndexView($form, $searchForm);
        
        if(isset($attributes['tabIndex'])){
            $view->setCurrentTabIndex($attributes['tabIndex']);
        }
        
        $view->setSearchStart($searchStart);
        $view->setSearchEnd($searchEnd);
        if (isset($attributes['tab'])) {
            $currentTab = $attributes['tab'];
            $view->setCurrentTab($currentTab);
        }
        $view->buildForm();
        $logURLAttributes = array(
            'controller'=>'accounting',
            'action'=>'exportAdjustmentsIndex'
        );
        LogService::logActivity(GI_URLUtils::buildURL($logURLAttributes), 'Export Adjustments to Quickbooks', 'visible', 'view');
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['breadcrumbs'] = array(
            array(
                'label'=>'Accounting',
                'link'=>'',
            ),
            array(
                'label'=>'Adjustments',
                'link'=>  GI_URLUtils::buildURL($attributes),
            ),
        );
        return $returnArray;
    }

    public function actionGetQuickbooksAdjustmentsExportIndexContent($attributes) {
        if (!isset($attributes['ajax']) || $attributes['ajax'] != 1) {
            return array();
        }
        $type = NULL;
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
        }
        $exported = NULL;
        if (isset($attributes['exported'])) {
            $exported = $attributes['exported'];
        }
        
        $excluded = NULL;
        if (isset($attributes['excluded'])) {
            $excluded = $attributes['excluded'];
        }
        $firstLoad = true;
        $pageNumber = 1;
        if (isset($attributes['pageNumber'])) {
            $pageNumber = $attributes['pageNumber'];
            $firstLoad = false;
        }
        
        $searchStart = NULL;
        if (isset($attributes['searchStart'])) {
            $searchStart = $attributes['searchStart'];
        }

        $searchEnd = NULL;
        if (isset($attributes['searchEnd'])) {
            $searchEnd = $attributes['searchEnd'];
        }

        if(isset($attributes['queryId'])){
            $queryId = $attributes['queryId'];
        } else {
            $queryId = NULL;
        }
        $curTabKey = 'not_yet_exported';
        if (!is_null($excluded)) {
            if (!empty($excluded)) {
                $curTabKey = 'excluded';
            }
        }
        if (!is_null($exported)) {
            if (!empty($exported)) {
                $curTabKey = 'exported';
            }
        }
        $cogsUITableView = NULL;
        $returnedUITableView = NULL;
        $damagedUITableView = NULL;
        $wasteUITableView = NULL;
        
        SessionService::setValue('blarp', 'humpe');

        if (empty($type) || $type == 'cogs') {
            $sampleOrderLine = OrderLineFactory::buildNewModel('sales');
            if (!empty($sampleOrderLine)) {
                $salesOrderLineSearch = $sampleOrderLine->getQuickbooksExportDataSearch($exported, $excluded, $searchStart, $searchEnd, $queryId);
                $salesOrderLineSearch->setPageNumber($pageNumber)
                        ->setItemsPerPage(ProjectConfig::getUITableItemsPerPage());
                $orderLineClass = get_class($sampleOrderLine);
                $orderLines = $salesOrderLineSearch->groupBy('id')
                        ->orderBy('SHIPTI.start_date_time')
                        ->select();
                $cogsPageBar = $salesOrderLineSearch->getPageBar(array(
                    'controller' => 'accounting',
                    'action' => 'getQuickbooksAdjustmentsExportIndexContent',
                    'excluded' => $excluded,
                    'exported' => $exported,
                    'type' => 'cogs'
                ));
                $cogsPageBar->setUseAjax(true);
                $uiTableCols = $orderLineClass::getQuickbooksExportUITableCols($curTabKey);
                $cogsUITableView = new UITableView($orderLines, $uiTableCols, $cogsPageBar);
            }
        }
          if ($type == 'returned') {     
            $sampleInvAdjustment = OrderReturnLineFactory::buildNewModel('returned');
            $orderReturnLineSearch = $sampleInvAdjustment->getQuickbooksExportDataSearch($exported, $excluded, $searchStart, $searchEnd, $queryId);
            $orderReturnLineSearch->setPageNumber($pageNumber)
                    ->setItemsPerPage(ProjectConfig::getUITableItemsPerPage());

            $invAdjustmentClass = get_class($sampleInvAdjustment);
            $invAdjustments = $orderReturnLineSearch->groupBy('id')
                    ->orderBy('RETURN.date')
                    ->select();

            $returnedPageBar = $orderReturnLineSearch->getPageBar(array(
                'controller' => 'accounting',
                'action' => 'getQuickbooksAdjustmentsExportIndexContent',
                'excluded' => $excluded,
                'exported' => $exported,
                'type'=>'returned'
            ));
            $returnedPageBar->setUseAjax(true);
            $uiTableCols = $invAdjustmentClass::getQuickbooksExportUITableCols($curTabKey);
            $returnedUITableView = new UITableView($invAdjustments, $uiTableCols, $returnedPageBar);
        }
        if ($type == 'damaged') {
            $sampleInvAdjustment = OrderReturnLineFactory::buildNewModel('damaged');
            $orderReturnLineSearch = $sampleInvAdjustment->getQuickbooksExportDataSearch($exported, $excluded, $searchStart, $searchEnd, $queryId);
            $orderReturnLineSearch->setPageNumber($pageNumber)
                    ->setItemsPerPage(ProjectConfig::getUITableItemsPerPage());

            $invAdjustmentClass = get_class($sampleInvAdjustment);
            $invAdjustments = $orderReturnLineSearch->groupBy('id')
                    ->orderBy('RETURN.date')
                    ->select();
            
            $returnedPageBar = $orderReturnLineSearch->getPageBar(array(
                'controller' => 'accounting',
                'action' => 'getQuickbooksAdjustmentsExportIndexContent',
                'excluded' => $excluded,
                'exported' => $exported,
                'type' => 'damaged'
            ));
            $returnedPageBar->setUseAjax(true);
            $uiTableCols = $invAdjustmentClass::getQuickbooksExportUITableCols($curTabKey);
            $damagedUITableView = new UITableView($invAdjustments, $uiTableCols, $returnedPageBar);
        }
        if ($type == 'waste') {
            $sampleInvAdjustment = InvAdjustmentFactory::buildNewModel('waste');
            $invAdjustmentSearch = $sampleInvAdjustment->getQuickbooksExportDataSearch($exported, $excluded, $searchStart, $searchEnd, $queryId);
            $invAdjustmentSearch->setPageNumber($pageNumber)
                    ->setItemsPerPage(ProjectConfig::getUITableItemsPerPage());

            $invAdjustmentClass = get_class($sampleInvAdjustment);
            $invAdjustments = $invAdjustmentSearch->groupBy('id')
                    ->orderBy('inception')
                    ->select();

            $returnedPageBar = $invAdjustmentSearch->getPageBar(array(
                'controller' => 'accounting',
                'action' => 'getQuickbooksAdjustmentsExportIndexContent',
                'excluded' => $excluded,
                'exported' => $exported,
                'type' => 'waste'
            ));
            $returnedPageBar->setUseAjax(true);
            $uiTableCols = $invAdjustmentClass::getQuickbooksExportUITableCols($curTabKey);
            $wasteUITableView = new UITableView($invAdjustments, $uiTableCols, $returnedPageBar);
        }

        if (empty($type)) {
            $view = new AccountingExportAdjustmentsToQuickbooksContentView();
            $view->setCurTabKey($curTabKey);
            if (!empty($searchStart)) {
                $view->setSearchStart($searchStart);
            }
            if (!empty($searchEnd)) {
                $view->setSearchEnd($searchEnd);
            }
            if (!empty($cogsUITableView)) {
                $view->setCogsUITable($cogsUITableView);
            }
            
            $returnedURLAttributes = array(
                        'controller' => 'accounting',
                        'action' => 'getQuickbooksAdjustmentsExportIndexContent',
                        'excluded' => $excluded,
                        'exported' => $exported,
                        'type' => 'returned',
            );
            if (!empty($searchStart)) {
                $returnedURLAttributes['searchStart'] = $searchStart;
            }
            if (!empty($searchEnd)) {
                $returnedURLAttributes['searchEnd'] = $searchEnd;
            }
            $returnedURL = GI_URLUtils::buildURL($returnedURLAttributes);
            $view->setReturnedURL($returnedURL);
            
            $damagedURLAttributes = array(
                        'controller' => 'accounting',
                        'action' => 'getQuickbooksAdjustmentsExportIndexContent',
                        'excluded' => $excluded,
                        'exported' => $exported,
                        'type' => 'damaged',
            );
            if (!empty($searchStart)) {
                $damagedURLAttributes['searchStart'] = $searchStart;
            }
            if (!empty($searchEnd)) {
                $damagedURLAttributes['searchEnd'] = $searchEnd;
            }
            $damagedURL = GI_URLUtils::buildURL($damagedURLAttributes);
            $view->setDamagedURL($damagedURL);
            
            $wasteURLAttributes = array(
                        'controller' => 'accounting',
                        'action' => 'getQuickbooksAdjustmentsExportIndexContent',
                        'excluded' => $excluded,
                        'exported' => $exported,
                        'type' => 'waste',
            );
            if (!empty($searchStart)) {
                $wasteURLAttributes['searchStart'] = $searchStart;
            }
            if (!empty($searchEnd)) {
                $wasteURLAttributes['searchEnd'] = $searchEnd;
            }
            $wasteURL = GI_URLUtils::buildURL($wasteURLAttributes);
            $view->setWasteURL($wasteURL);
            $returnArray = GI_Controller::getReturnArray($view);
        } else {
            $returnArray = array();
            switch ($type) {
                case 'returned':
                    if (!empty($returnedUITableView)) {
                        if ($firstLoad) {
                            $returnArray['mainContent'] = $returnedUITableView->getHTMLView();
                        } else {
                            $returnArray['uiTable'] = $returnedUITableView->getHTMLView();
                        }
                    }
                    break;
                case 'damaged':
                    if (!empty($damagedUITableView)) {
                        if ($firstLoad) {
                            $returnArray['mainContent'] = $damagedUITableView->getHTMLView();
                        } else {
                            $returnArray['uiTable'] = $damagedUITableView->getHTMLView();
                        }
                    }
                    break;
                case 'waste':
                    if (!empty($wasteUITableView)) {
                        if ($firstLoad) {
                            $returnArray['mainContent'] = $wasteUITableView->getHTMLView();
                        } else {
                            $returnArray['uiTable'] = $wasteUITableView->getHTMLView();
                        }
                    }
                    break;
                case 'cogs':
                default:
                    if (!empty($cogsUITableView)) {
                        if ($firstLoad) {
                            $returnArray['mainContent'] = $cogsUITableView->getHTMLView();
                        } else {
                            $returnArray['uiTable'] = $cogsUITableView->getHTMLView();
                        }
                    }
                    break;
            }
        }
        return $returnArray;
    }

    public function actionPreExportAdjustmentsToQuickbooks($attributes) {
        $franchiseId = QBConnection::getFranchiseId();
        $salesOrderLineIds = filter_input(INPUT_POST, 'sales_order_lines', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $returnLineReturnedIds = filter_input(INPUT_POST, 'order_return_returned_lines', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $damagedLineReturnIds = filter_input(INPUT_POST, 'order_return_damaged_lines', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $invAdjustmentWasteIds = filter_input(INPUT_POST, 'inv_adjustment_waste_lines', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $count = count($salesOrderLineIds);
        $count += count($returnLineReturnedIds);
        $count += count($damagedLineReturnIds);
        $count += count($invAdjustmentWasteIds);
        $exportAdjForm = new GI_Form('export_adjustments');
        if ($exportAdjForm->wasSubmitted()) {
            SessionService::setValue(array(
                'exports',
                $franchiseId,
                'so_line_ids'
                    ), $salesOrderLineIds);

            SessionService::setValue(array(
                'exports',
                $franchiseId,
                'order_return_returned_line_ids',
                    ), $returnLineReturnedIds);

            SessionService::setValue(array(
                'exports',
                $franchiseId,
                'order_return_damaged_line_ids'
                    ), $damagedLineReturnIds);

            SessionService::setValue(array(
                'exports',
                $franchiseId,
                'inv_adjustment_waste_ids'
                    ), $invAdjustmentWasteIds);
            SessionService::setValue(array(
                'exports',
                $franchiseId,
                'count'
                    ), $count);
            SessionService::setValue(array(
                'exports',
                $franchiseId,
                'total_count',
                    ), $count);
        }
        $form = new GI_Form('confirm_export');
        $view = new AccountingExportAdjustmentsToQuickbooksConfirmView($form);
        $view->setCount($count);
        $view->buildForm();
        $success = 0;
        $newUrl = '';
        $jQueryAction = '';
        if ($form->wasSubmitted() && $form->validate()) {
            $newUrlAttributes = array(
                'controller'=>'accounting',
                'action'=>'exportAdjustmentsToQuickbooks',
                'ajax'=>1,
            );
            if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
                $success = 1;
                $url = GI_URLUtils::buildURL($newUrlAttributes, false, true);
                $jQueryAction = 'giModalOpenAjaxContent("'.$url.'");';
                $newUrl = '';
            } else {
                GI_URLUtils::redirect($newUrlAttributes);
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if (!empty($newUrl)) {
            $returnArray['newUrl'] = $newUrl;
        }
        $returnArray['jqueryAction'] = $jQueryAction;
        return $returnArray;
    }

    public function actionExportAdjustmentsToQuickbooks($attributes) {
        if (!Permission::verifyByRef('export_adjustments_to_quickbooks')) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $franchiseId = QBConnection::getFranchiseId();
        $currentModelIds = array();
        $currentKey = '';
        $soLineIds = SessionService::getValue(array(
            'exports',
                    $franchiseId,
                    'so_line_ids'
        ));
        if (!empty($soLineIds)) {
            $currentModelIds = $soLineIds;
            $model = OrderLineFactory::getModelById($currentModelIds[0]);
            $currentKey = 'so_line_ids';
        }
        if (empty($currentModelIds)) {
            $returnedLineIds = SessionService::getValue(array(
                        'exports',
                        $franchiseId,
                        'order_return_returned_line_ids',
            ));
            if (!empty($returnedLineIds)) {
                $currentModelIds = $returnedLineIds;
                $model = OrderReturnLineFactory::getModelById($currentModelIds[0]);
                $currentKey = 'order_return_returned_line_ids';
            }
        }
        if (empty($currentModelIds)) {
            $damagedLineIds = SessionService::getValue(array(
                        'exports',
                        $franchiseId,
                        'order_return_damaged_line_ids',
            ));
            if (!empty($damagedLineIds)) {
                $currentModelIds = $damagedLineIds;
                $model = OrderReturnLineFactory::getModelById($currentModelIds[0]);
                $currentKey = 'order_return_damaged_line_ids';
            }
        }
        if (empty($currentModelIds)) {
            $wasteAdjustmentIds = SessionService::getValue(array(
                        'exports',
                        $franchiseId,
                        'inv_adjustment_waste_ids'
            ));
            if (!empty($wasteAdjustmentIds)) {
                $currentModelIds = $wasteAdjustmentIds;
                $model = InvAdjustmentFactory::getModelById($currentModelIds[0]);
                $currentKey = 'inv_adjustment_waste_ids';
            }
        }


        if (empty($currentModelIds)) {
            SessionService::unsetValue(array(
                'exports',
                $franchiseId,
            ));
            return array(
                'success'=>1,
                'jqueryAction'=>'giModalClose();',
                'newUrl'=>'refresh',
            );
        }
        $form = new GI_Form('export_adjustment_' . $currentKey . '_' . $currentModelIds[0]);
        $view = $model->getExportQBAdjustmentFormView($form);
        $remainingCount = SessionService::getValue(array(
            'exports',
            $franchiseId,
            'count'
        ));
        if (!is_null($remainingCount)) {
            $remainingCount = (int) $remainingCount;
            $remainingCount -= 1;
        }
        $view->setRemainingCount($remainingCount);
        
        $totalCount = SessionService::getValue(array(
            'exports',
            $franchiseId,
            'total_count'
        ));
        if (!is_null($totalCount)) {
            $totalCount = (int) $totalCount;
        }
        $view->setTotalCount($totalCount);
        
        $view->buildForm();
        $success = 0;
        $newUrl = '';
        if ($model->handleQBExportFormSubmission($form)) {
            if (!empty($currentModelIds)) {
                unset($currentModelIds[0]);
                $currentModelIds = array_values($currentModelIds);
            }
            if (empty($currentModelIds)) {
                SessionService::unsetValue(array(
                    'exports',
                    $franchiseId,
                    $currentKey,
                ));
            } else {
                SessionService::setValue(array(
                    'exports',
                    $franchiseId,
                    $currentKey,
                        ), $currentModelIds);
            }
            SessionService::setValue(array(
                'exports',
                $franchiseId,
                'count'
            ), $remainingCount);
            return $this->actionExportAdjustmentsToQuickbooks($attributes);
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        $returnArray['newUrl'] = $newUrl;
        $returnArray['modalClass'] = 'full_sized qb_modal';
        return $returnArray;
    }

    public function actionExcludeAdjustmentFromQuickbooksExport($attributes) {
        if (!isset($attributes['type']) || !isset($attributes['id'])) {
            GI_URLUtils::redirectToError();
        }
        $type = $attributes['type'];
        $id = $attributes['id'];
        switch ($type) {
            case 'sales_order_line':
                $model = OrderLineFactory::getModelById($id);
                break;
            case 'returned':
            case 'damaged':
                $model = OrderReturnLineFactory::getModelById($id);
                break;
            case 'waste':
                $model = InvAdjustmentFactory::getModelById($id);
                break;
            default:
                $model = NULL;
                break;
        }
        if (empty($model)) {
            GI_URLUtils::redirectToError();
        }
        $form = new GI_Form('confirm_exclude');
        $view = new GenericAcceptCancelFormView($form);
        $view->setModalHeader('QB Settings');
        $view->setHeaderText('Exclude'); //TODO - more specific
        $view->setMessageText('Are you sure you wish to exclude this?'); //TODO - more specific
        $view->setSubmitButtonLabel('Exclude');
        $view->buildForm();
        $success = 0;
        $newUrl = NULL;
        if ($form->wasSubmitted() && $form->validate()) {
            if ($model->excludeFromQuickbooksExport()) {
                $newUrlAttributes = array(
                    'controller' => 'accounting',
                    'action' => 'exportAdjustmentsIndex',
                    'tab' => 'excluded'
                );
                if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
                    $newUrl = GI_URLUtils::buildURL($newUrlAttributes);
                    $success = 1;
                } else {
                    GI_URLUtils::redirect($newUrlAttributes);
                }
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if (!empty($newUrl)) {
            $returnArray['newUrl'] = $newUrl;
        }
        return $returnArray;
    }

    public function actionUnExcludeAdjustmentFromQuickbooksExport($attributes) {
        if (!isset($attributes['type']) || !isset($attributes['id'])) {
            GI_URLUtils::redirectToError();
        }
        $type = $attributes['type'];
        $id = $attributes['id'];
        switch ($type) {
            case 'sales_order_line':
                $model = OrderLineFactory::getModelById($id);
                break;
            case 'returned':
            case 'damaged':
                $model = OrderReturnLineFactory::getModelById($id);
                break;
            case 'waste':
                $model = InvAdjustmentFactory::getModelById($id);
                break;
            default:
                $model = NULL;
                break;
        }
        if (empty($model)) {
            GI_URLUtils::redirectToError();
        }
        $form = new GI_Form('confirm_un_exclude');
        $view = new GenericAcceptCancelFormView($form);
        $view->setModalHeader('QB Settings');
        $view->setHeaderText('Unexclude'); //TODO - more specific
        $view->setMessageText('Are you sure you wish to unexclude this?'); //TODO - more specific
        $view->setSubmitButtonLabel('Unexclude');
        $view->buildForm();
        $success = 0;
        $newUrl = NULL;
        if ($form->wasSubmitted() && $form->validate()) {
            if ($model->unExcludeFromQuickbooksExport()) {
                $newUrlAttributes = array(
                    'controller' => 'accounting',
                    'action' => 'exportAdjustmentsIndex',
                    'tab' => 'not_yet_exported'
                );
                if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
                    $newUrl = GI_URLUtils::buildURL($newUrlAttributes);
                    $success = 1;
                } else {
                    GI_URLUtils::redirect($newUrlAttributes);
            }
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if (!empty($newUrl)) {
            $returnArray['newUrl'] = $newUrl;
        }
        return $returnArray;
    }

    public function actionExportAdjustmentsExportedToQuickbooks($attributes) {
        if (isset($attributes['queryId'])) {
            $queryId = $attributes['queryId'];
        } else {
            $queryId = NULL;
        }
        $pageNumber = 1;
        if (isset($attributes['pageNumber'])) {
            $pageNumber = $attributes['pageNumber'];
        }
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
        } else {
            $type = 'sales_order_lines'; //returned, damage, waste
        }
        $searchStart = NULL;
        if (isset($attributes['searchStart'])) {
            $searchStart = $attributes['searchStart'];
        }

        $searchEnd = NULL;
        if (isset($attributes['searchEnd'])) {
            $searchEnd = $attributes['searchEnd'];
        }
        $itemsPerPage = ProjectConfig::getUITableItemsPerPage();
        $models = NULL;
        $date = GI_Time::getDate();
        $reportTitle = 'Quickbooks Adjustments Report';
        $fileName = 'QB_Exported_Adjustments_Report_' . $date;
        $sampleJournalEntry = NULL;
        $uiTableCols = NULL;
        switch ($type) {
            case 'returned':
                $sampleJournalEntry = QBJournalEntryFactory::buildNewModel('return_line_returned');
                $sampleModel = OrderReturnLineFactory::buildNewModel('returned');
                $modelSearch = $sampleModel->getQuickbooksExportDataSearch(1, 0, $searchStart, $searchEnd, $queryId);
                $modelSearch->setPageNumber($pageNumber)
                        ->setItemsPerPage($itemsPerPage)
                        ->groupBy('id');
                $models = $modelSearch->orderBy('RETURN.date')
                        ->select();

                $reportTitle = 'Returned Stock Adjustments Report';
                $fileName = 'returned_stock_adjustments_' . $date;
                break;
            case 'damaged':
                $sampleJournalEntry = QBJournalEntryFactory::buildNewModel('return_line_damaged');
                $sampleModel = OrderReturnLineFactory::buildNewModel('damaged');
                $modelSearch = $sampleModel->getQuickbooksExportDataSearch(1, 0, $searchStart, $searchEnd, $queryId);
                $modelSearch->setPageNumber($pageNumber)
                        ->setItemsPerPage($itemsPerPage)
                        ->groupBy('id');
                $models = $modelSearch->orderBy('RETURN.date')
                        ->select();

                $reportTitle = 'Damaged Sold Stock Adjustments Report';
                $fileName = 'damaged_sold_stock_adjustments_' . $date;
                break;
            case 'waste':
                $sampleJournalEntry = QBJournalEntryFactory::buildNewModel('inv_adjustment_waste');
                $sampleModel = InvAdjustmentFactory::buildNewModel('waste');
                $modelSearch = $sampleModel->getQuickbooksExportDataSearch(1, 0, $searchStart, $searchEnd, $queryId);
                $modelSearch->setPageNumber($pageNumber)
                        ->setItemsPerPage($itemsPerPage)
                        ->groupBy('id');
                $models = $modelSearch->orderBy('inception')
                        ->select();
                $reportTitle = 'Damaged/Wasted Unsold Stock Adjustments Report';
                $fileName = 'damaged_wasted_unsold_stock_adjustments_' . $date;
                break;
            case 'sales_order_lines':
            default:
                $sampleJournalEntry = QBJournalEntryFactory::buildNewModel('sales_order_line');
                $sampleModel = OrderLineFactory::buildNewModel('sales');
                $modelSearch = $sampleModel->getQuickbooksExportDataSearch(1,0, $searchStart, $searchEnd, $queryId);
                $modelSearch->setPageNumber($pageNumber)
                        ->setItemsPerPage($itemsPerPage)
                        ->groupBy('id');
                $models = $modelSearch->orderBy('SHIPTI.start_date_time')
                        ->select();

                $reportTitle = 'Sold Stock Adjustments Report';
                $fileName = 'sold_stock_adjustments_' . $date;
                break;
        }
        if (!empty($sampleJournalEntry)) {
            $uiTableCols = $sampleJournalEntry->getCSVExportUITableCols();
        }
        $csv = new GI_CSV(GI_Sanitize::filename($fileName));
        $csv->setOverWrite(true);
        $addHeader = true;
        if ($pageNumber > 1) {
            $csv->setAddToExisting(true);
            $addHeader = false;
        }
        $csv->setUITableCols($uiTableCols, $addHeader);
        foreach ($models as $model) {
            $exportedJournalEntries = $model->getExportedQBJournalEntries();
            if (!empty($exportedJournalEntries)) {
                $csv->addModelRows($exportedJournalEntries);
            }
        }
        GI_CSV::setCSVExporting(false);
        $csvFile = $csv->getCSVFilePath();
        $total = $modelSearch->getCount();
        $totalPages = ceil($total / $itemsPerPage);
        if (isset($attributes['timeTrackerId'])) {
            $timeTrackerId = $attributes['timeTrackerId'];
        } else {
            $timeTrackerId = GI_StringUtils::generateRandomString(8, false, true, true, true, false);
        }
        $nextPageNumber = $pageNumber + 1;
        $nextURL = NULL;
        if ($nextPageNumber <= $totalPages) {
            $nextPageURL = array(
                'controller' => 'accounting',
                'action' => 'ExportAdjustmentsExportedToQuickbooks',
                'type'=>$type,
                'pageNumber' => $nextPageNumber,
                'timeTrackerId' => $timeTrackerId,
            );
            if (!empty($queryId)) {
                $nextPageURL['queryId'] = $queryId;
            }
            if (!empty($searchStart)) {
                $nextPageURL['searchStart'] = $searchStart;
            }
            if (!empty($searchEnd)) {
                $nextPageURL['searchEnd'] = $searchEnd;
            }
            $nextURL = GI_URLUtils::buildURL($nextPageURL);
        }
        if ($totalPages <= 0) {
            $totalPages = 1;
        }
        $percentage = $pageNumber / $totalPages * 100;
        $view = new GenericProgressBarView();
        $view->setPercentage($percentage);
        $view->setTimeTrackerId($timeTrackerId);
        $view->setProgressBarTitle('Exporting ' . $reportTitle);
        $view->setProgressDesc('Preparing ' . $reportTitle);
        if (!empty($nextURL)) {
            $view->setNextURL($nextURL);
        } else {
            $view->setProgressForward(false);
            $view->addHTML('<p>Your '.$reportTitle.' is ready to <a href="' . $csvFile . '" target="_blank" title="Download '.$reportTitle.'">download</a>.</p>');
        }
        $returnArray = GI_Controller::getReturnArray($view);
        return $returnArray;
    }

    public function actionClearQBExportData($attributes) {
        if (!DEV_MODE) {
            GI_URLUtils::redirectToAccessDenied();
        }
        //invoices
        $invoices = InvoiceFactory::search()
                ->filterNotNull('quickbooks_id')
                ->select();
        if (!empty($invoices)) {
            foreach ($invoices as $invoice) {
                $invoice->setProperty('quickbooks_id', NULL);
                $invoice->setProperty('quickbooks_export_count', 0);
                $invoice->setProperty('quickbooks_export_date', NULL);
                $invoice->setProperty('qb_re_export_required', 0);
                if (!$invoice->save()) {
                    print_r('Invoice ' . $invoice->getId() . ' did not save.<br/>');
                }
            }
        }
        //bills
        $bills = BillFactory::search()
                ->filterNotNull('quickbooks_id')
                ->select();
        if (!empty($bills)) {
            foreach ($bills as $bill) {
                $bill->setProperty('quickbooks_id', NULL);
                $bill->setProperty('quickbooks_export_count', 0);
                $bill->setProperty('quickbooks_export_date', NULL);
                $bill->setProperty('qb_re_export_required', 0);
                if (!$bill->save()) {
                    print_r('Bill ' . $bill->getId() . ' did not save.<br/>');
                }
            }
        }
        //Contacts
        $contacts = ContactFactory::search()
                ->filterNotNull('contact_qb_id')
                ->select();
        if (!empty($contacts)) {
            foreach ($contacts as $contact) {
                $contact->setProperty('contact_qb_id', NULL);
                if (!$contact->save()) {
                    print_r('Contact ' . $contact->getId() . ' did not save.<br/>');
                }
            }
        }
        //Contact QBs
        $contactQBs = ContactQBFactory::search()
                ->select();
        if (!empty($contactQBs)) {
            foreach ($contactQBs as $contactQB) {
                if (!$contactQB->softDelete()) {
                    print_r('Contact QB ' . $contactQB->getId() . ' did not save<br/>');
                }
            }
        }
        //Contact Infos
        $contactInfos = ContactInfoFactory::search()
                ->filter('qb_linked', 1)
                ->select();
        if (!empty($contactInfos)) {
            foreach ($contactInfos as $contactInfo) {
                $contactInfo->setProperty('qb_linked', 0);
                if (!$contactInfo->save()) {
                    print_r('Contact Info ' . $contactInfo->getId() . ' did not save<br/>');
                }
            }
        }

        //order line sales
        $orderLineSales = OrderLineFactory::search()
                ->filterByTypeRef('sales')
                ->filterNotNull('sales.quickbooks_exported_qty')
                ->select();
        if (!empty($orderLineSales)) {
            foreach ($orderLineSales as $orderLineSale) {
                $orderLineSale->setProperty('order_line_sales.quickbooks_exported_qty', NULL);
                $orderLineSale->setProperty('order_line_sales.quickbooks_exported_cogs_summary', NULL);
                $orderLineSale->setProperty('order_line_sales.qb_re_export_required', 0);
                if (!$orderLineSale->save()) {
                    print_r('Order Line Sales ' . $orderLineSale->getId() . ' did not save.<br/>');
                }
            }
        }
        //order return line
        $orderReturnLines = OrderReturnLineFactory::search()
                ->filterNotNull('quickbooks_export_date')
                ->select();
        if (!empty($orderReturnLines)) {
            foreach ($orderReturnLines as $orderReturnLine) {
                $orderReturnLine->setProperty('quickbooks_export_date', NULL);
                $orderReturnLine->setProperty('quickbooks_exported_amount_summary', NULL);
                $orderReturnLine->setProperty('qb_re_export_required', NULL);
                if (!$orderReturnLine->save()) {
                    print_r('Return Line ' . $orderReturnLine->getId() . ' did not save.<br/>');
                }
            }
        }
        //inv adjustment waste
        $invAdjustmentWastes = InvAdjustmentFactory::search()
                ->filterByTypeRef('waste')
                ->filterNotNull('waste.quickbooks_export_date')
                ->select();
        if (!empty($invAdjustmentWastes)) {
            foreach ($invAdjustmentWastes as $invAdjustmentWaste) {
                $invAdjustmentWaste->setProperty('inv_adjustment_waste.quickbooks_export_date', NULL);
                $invAdjustmentWaste->setProperty('inv_adjustment_waste.quickbooks_exported_amount_summary', '');
                $invAdjustmentWaste->setProperty('inv_adjustment_waste.qb_re_export_required', NULL);
                if (!$invAdjustmentWaste->save()) {
                    print_r('Inv Adjustment Waste ' . $invAdjustmentWaste->getId() . ' did not save.<br/>');
                }
            }
        }
        
        print_r('All Done!');
        die();
    }

    public function actionReports($attributes) {
        $form = new GI_Form('reports');
        $dates = GI_Time::getFiscalYearStartAndEndDates(); //DateTime Objects
        $reports = AccReportFactory::buildReportsArray($dates['start'], $dates['end']);
        $selectedType = NULL;
        if (isset($attributes['type'])) {
            $selectedType = $attributes['type'];
        }
        $view = new AccReportsFormView($form, $reports, $selectedType);
        $view->buildForm();
        if ($form->wasSubmitted() && $form->validate()) {
            $typeArray = filter_input(INPUT_POST, 'report_type', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            $formValid = true;
            if (empty($typeArray)) {
                $formValid = false;
                $form->addFieldError('report_type_error', 'error', 'You must select a type of report.');
            }
            $fiscalYear = filter_input(INPUT_POST, 'fiscal_year');
            $timePeriod = filter_input(INPUT_POST, 'time_period');
            $startDate = filter_input(INPUT_POST, 'start_date');
            $endDate = filter_input(INPUT_POST, 'end_date');
            
            if (!(($fiscalYear !== 'NULL' && $timePeriod !== 'NULL') || (!empty($startDate) && !empty($endDate)))) {
                $formValid = false;
                if ($fiscalYear != 'NULL' && $timePeriod == 'NULL') {
                    $form->addFieldError('time_period', 'error', 'You must select a time period');
                } else if ($fiscalYear == 'NULL' && $timePeriod != 'NULL') {
                    $form->addFieldError('fiscal_year', 'error', 'You must select a fiscal year');
                } else if (!empty($startDate) && empty($endDate)) {
                    $form->addFieldError('end_date', 'error', 'You must select an end date');
                } else if (empty($startDate) && !empty($endDate)) {
                    $form->addFieldError('start_date', 'error', 'You must select a start date');
                }
            }
            if ($formValid) {
                $type = $typeArray[0];
                $start = NULL;
                $end = NULL;
                if (!empty($startDate) && !empty($endDate)) {
                    $start = $startDate;
                    $end = $endDate;
                } else {
                    $fyYearArray = explode('_', $fiscalYear);
                    if (count($fyYearArray) > 1) {
                        $startYear = $fyYearArray[0];
                        $endYear = $fyYearArray[1];
                    } else {
                        $startYear = $fyYearArray[0];
                        $endYear = $fyYearArray[0];
                    }
                    $fiscalStartDate = new DateTime($startYear . '-' . ProjectConfig::getFiscalYearStartMonthAndDay());
                    $tempEndDate = new DateTime($startYear . '-' . ProjectConfig::getFiscalYearStartMonthAndDay());
                    $tempEndDate->modify('-1 day');
                    $fiscalEndDate = new DateTime($endYear . '-' . $tempEndDate->format('m-d'));
                    $dates = GI_Time::getDatesByFiscalYearAndReportingPeriod($fiscalStartDate, $fiscalEndDate, $timePeriod);
                    $start = $dates['start'];
                    $end = $dates['end'];
                }
                GI_URLUtils::redirect(array(
                    'controller' => 'accounting',
                    'action' => 'viewReport',
                    'type'=>$type,
                    'start'=>$start,
                    'end'=>$end,
                ));
            }
        }
        $breadcrumbs = array();
        $breadcrumbs[] = array(
            'label'=>'Accounting',
            'link'=>'',
        );
        $breadcrumbs[] = array(
            'label'=>'Reports',
            'link'=> GI_URLUtils::buildURL($attributes),
        );
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['breadcrumbs'] = $breadcrumbs;
        return $returnArray;
    }

    public function actionViewReport($attributes) {
        if (!isset($attributes['type']) || !isset($attributes['start']) || !isset($attributes['end'])) {
            GI_URLUtils::redirectToError();
        }
        $activeType = $attributes['type'];
        $start = new DateTime($attributes['start']);
        $end = new DateTime($attributes['end']);
        $reports = AccReportFactory::buildReportsArray($start, $end);
        $form = new GI_Form('acc_report');
        $view = new AccReportsView($reports, $activeType, $start, $end, $form);
        $returnArray = GI_Controller::getReturnArray($view);
        return $returnArray;
    }

    public function actionGetReport($attributes) {
        if (isset($attributes['type']) && isset($attributes['start']) && isset($attributes['end'])) {
            $type = $attributes['type'];
            $startDate = new DateTime ($attributes['start']);
            $endDate = new DateTime($attributes['end']);
            $report = AccReportFactory::buildReportObject($type, $startDate, $endDate);
            if (!empty($report)) {
                if ($report->buildReport()) {
                    $view = $report->getDetailView();
                    if (!empty($view)) {
                        $returnArray = GI_Controller::getReturnArray($view);
                        $dynamicJS = $view->getDynamicJS();
                        if (!empty($dynamicJS)) {
                            $returnArray['jqueryCallbackAction'] = $dynamicJS;
                        }
                        return $returnArray;
                    }
                }
            }
        }
        $returnArray = GI_Controller::getReturnArray();
        $returnArray['mainContent'] = '';
        return $returnArray;
    }
    
    public function actionExportReportCSV($attributes) {
        if (!isset($attributes['type']) || !isset($attributes['start']) || !isset($attributes['end'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $type = $attributes['type'];
        $startDate = new DateTime($attributes['start']);
        $endDate = new DateTime($attributes['end']);
        $report = AccReportFactory::buildReportObject($type, $startDate, $endDate);
        if (empty($report) || !$report->buildReport()) {
            GI_URLUtils::redirectToError(2000);
        }
        $csvFile = $report->getCSVFile();
        if (empty($csvFile)) {
            return NULL;
        }
        $percentage = 100;
        $view = new GenericProgressBarView();
        $view->setPercentage($percentage);
        $view->setProgressBarTitle('Exporting CSV');
        $view->setProgressDesc('CSV Expense Report.');
        $view->setProgressForward(false);
        $view->addHTML('<p>Your CSV is ready to <a href="' . $csvFile . '" target="_blank" title="Download Expense Report">download</a>.</p>');
        $returnArray = GI_Controller::getReturnArray($view);
        $breadcrumbs = array();
        $breadcrumbs[] = array(
            'label'=>'Accounting',
            'link'=>''
        );
        $breadcrumbs[] = array(
            'label'=>'Reports',
            'link'=> GI_URLUtils::buildURL(array(
                'controller'=>'accounting',
                'action'=>'reports'
            ))
        );
        $breadcrumbs[] = array(
            'label'=>$report->getTitle(),
            'link'=> GI_URLUtils::buildURL(array(
                'controller'=>'accounting',
                'action'=>'viewReport',
                'type'=>$type,
                'start'=>$startDate->format('Y-m-d'),
                'end'=>$endDate->format('Y-m-d'),
            ))
        );
        $breadcrumbs[] = array(
          'label'=>'Export CSV',
            'link'=> GI_URLUtils::buildURL($attributes),
        );
        $returnArray['breadcrumbs'] = $breadcrumbs;
        return $returnArray;
    }

    public function actionQBWebhooks($attributes) {
        //Return HTTP response
        ob_start();
        echo 'OK';
        $size = ob_get_length();
        header("Content-Encoding: none");
        header("Content-Length: {$size}");
        header("Connection: close");
        ob_end_flush();
        ob_flush();
        flush();
        if (session_id()) {
            session_write_close();
        }
        //These instructions are executed after the HTTP request has been returned\
        
        $webhooksToken = ProjectConfig::getQBWebhooksToken();
        $isVerified = false;
        $payloadData = array();
        if (!DEV_MODE && isset($_SERVER['HTTP_INTUIT_SIGNATURE']) && !empty($_SERVER['HTTP_INTUIT_SIGNATURE'])) {
            $payload = file_get_contents("php://input");
            if (GI_StringUtils::isValidJSON($payload)) {
                $payloadHash = hash_hmac('sha256', $payload, $webhooksToken);
                $singatureHash = bin2hex(base64_decode($_SERVER['HTTP_INTUIT_SIGNATURE']));
                if ($payloadHash == $singatureHash) {
                    $isVerified = true;
                }
            }
        }
        if ($isVerified) {
            $payloadData = json_decode($payload, true);
        } else {
            die();
        }
        foreach ($payloadData['eventNotifications'] as $eventNotification) {
            $realmId = $eventNotification['realmId'];
            $qbSettingsSearch = SettingsFactory::search();
            $qbSettingsSearch->filterByTypeRef('qb')
                    ->filter('qb.realm_id', $realmId);
            $results = $qbSettingsSearch->select();
            if (empty($results)) {
                continue;
            }
            $qbSettings = $results[0];
            $franchiseId = $qbSettings->getProperty('franchise_id');
            if (!empty($franchiseId)) {
                $franchiseSearch = ContactFactory::search();
                $franchiseSearch->ignoreFranchise('contact')
                        ->filter('id', $franchiseId);
                $franchiseResult = $franchiseSearch->select();
                if (ProjectConfig::getIsFranchisedSystem() && empty($franchiseResult)) {
                    continue;
                }
                $franchise = $franchiseResult[0];
                Login::setCurrentFranchise($franchise);
            }
            $dataService = QBConnection::getInstance();
            $settings = QBConnection::getQBSettingsModel();
            
            foreach ($eventNotification['dataChangeEvent']['entities'] as $entity) {
                $name = $entity['name'];
                $id = $entity['id'];
                $operation = $entity['operation'];
                switch ($name) {
                    case 'Customer':
                        if (!empty($id) && $operation == 'Update') {
                            $contactQB = ContactQBFactory::getModelByQBId($id);
                            if (!empty($contactQB)) {
                                $contactQB->importFromQB();
//                                $contactQB->setProperty('import_required', 1);
//                                $contactQB->save();
                            }
                        }
                        break;
                    case 'Invoice':
                        if (!empty($id)) {
                            if (empty($dataService)) {
                                if (!empty($settings)) {
                                    $settings->setProperty('settings_qb.cust_data_req_update', 1);
                                    $settings->save();
                                }
                            } else {
                                $query = "select * from Invoice where id='" . $id . "'";
                                $invoiceArray = $dataService->Query($query);
                                if (!empty($invoiceArray)) {
                                    $qbInvoice = $invoiceArray[0];
                                    $customerId = $qbInvoice->CustomerRef;
                                    if (!empty($customerId)) {
                                        $contactQBCustomer = ContactQBFactory::getModelByQBId($customerId);
                                        if (!empty($contactQBCustomer)) {
                                            $customerQBObject = $contactQBCustomer->getQuickbooksObject();
                                            if (!empty($customerQBObject)) {
                                                $contactQBCustomer->updateFromQB($customerQBObject);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        break;
                    case 'Payment':
                        if (!empty($id)) {
                            if (empty($dataService)) {
                                if (!empty($settings)) {
                                    $settings->setProperty('settings_qb.cust_data_req_update', 1);
                                    $settings->save();
                                }
                            } else {
                                $query = "select * from Payment where id='" . $id . "'";
                                $paymentArray = $dataService->Query($query);
                                if (!empty($paymentArray)) {
                                    $payment = $paymentArray[0];
                                    $customerId = $payment->CustomerRef;
                                    if (!empty($customerId)) {
                                        $contactQBCustomer = ContactQBFactory::getModelByQBId($customerId);
                                        if (!empty($contactQBCustomer)) {
                                            $customerQBObject = $contactQBCustomer->getQuickbooksObject();
                                            if (!empty($customerQBObject)) {
                                                $contactQBCustomer->updateFromQB($customerQBObject);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        break;
                    default:
                        //Do nothing
                        break;
                }
            }
        }
        die();
    }
    
    public function actionUpdateQBCustomerBalances($attributes) {
       $dataService = QBConnection::getInstance();
       if (!isset($attributes['ajax']) || $attributes['ajax'] != 1 || !Permission::verifyByRef('connect_to_quickbooks') || empty($dataService)) {
           return array(
               'result'=>'-1'
           );
       }
       $settingsSearch = SettingsFactory::search();
       $settingsSearch->filterByTypeRef('qb');
       $settingsArray = $settingsSearch->select();
       if (empty($settingsArray)) {
           return array(
                'result' => 0
            );
        }
        $qbSettings = $settingsArray[0];
        if (!empty($qbSettings->getProperty('settings_qb.cust_data_req_update'))) {
            $search = ContactQBFactory::search()
                    ->filterByTypeRef('customer');
            $models = $search->select();
            if (!empty($models)) {
                foreach ($models as $contactQB) {
                    $qbObject = $contactQB->getQuickbooksObject();
                    if (!empty($qbObject)) {
                        $contactQB->updateFromQB($qbObject);
                    }
                }
            }
            $qbSettings->setProperty('settings_qb.cust_data_req_update', 0);
            $qbSettings->save();
        }

        return array(
            'result' => 0
        );
    }

    public function actionDetermineTaxCodeQBId($attributes) {
        if (!(isset($attributes['ajax']) && $attributes['ajax']) == 1) {
            GI_URLUtils::redirectToError();
        }

        $type = 'sales';
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
        }

        $contactId = NULL;
        if (isset($attributes['contactId'])) {
            $contactId = $attributes['contactId'];
        }
        
        $countryCode = NULL;
        $regionCode = NULL;
        if (isset($attributes['regionId']) && !empty($attributes['regionId']) && $attributes['regionId'] != 'NULL') {
            $region = RegionFactory::getModelById($attributes['regionId']);
            $regionCode = $region->getRegionCode();
            $countryCode = $region->getCountryCode();
        }
        
        $taxCodeId = QBTaxCodeFactory::determineTaxCodeQBId($type, $regionCode, $countryCode, $contactId);
        
        return array(
            'tax_code_id' => $taxCodeId
        );
    }
    
    public function actionQBAccountsIndex($attributes) {
        //TODO - permission check
        $sampleAccount = QBAccountFactory::buildNewModel('account');
        if (isset($attributes['pageNumber'])) {
            $pageNumber = $attributes['pageNumber'];
        } else {
            $pageNumber = 1;
        }

        if (isset($attributes['queryId'])) {
            $queryId = $attributes['queryId'];
        } else {
            $queryId = NULL;
        }

        $search = QBAccountFactory::search();

        QBConnection::addFranchiseFilterToDataSearch($search);
        $search->setPageNumber($pageNumber)
                ->setItemsPerPage(ProjectConfig::getUITableItemsPerPage())
                ->setQueryId($queryId)
                ->filter('qb_active', 1)
                ->orderBy('qb_account_type_id', 'ASC');

        $tempRedirectArray = array( //For testing
            'controller'=>'accounting',
            'action'=>'qbAccountsIndex'
        );
        $searchView = $sampleAccount->getSearchForm($search, $tempRedirectArray);

        $pageBarLinkProps = array(
            'controller' => 'accounting',
            'action' => 'QBAccountsIndex',
        );
        
        $actionResult = ActionResultFactory::buildActionResult();
        $actionResult->setSearchView($searchView)
                ->setSampleModel($sampleAccount)
                ->setUseAjax(true);
        if(!GI_URLUtils::getAttribute('search')){
            $accounts = $search->select();
            $pageBar = $search->getPageBar($pageBarLinkProps);
            $uiTableCols = $sampleAccount->getUITableCols();
            $uiTableView = new UITableView($accounts, $uiTableCols, $pageBar);
            $view = $sampleAccount->getIndexView($accounts, $uiTableView, $sampleAccount, $searchView);
            if(GI_URLUtils::isAJAX()){
                $view->setAddWrap(false);
            }
            $actionResult->setView($view)
                    ->setPageBar($pageBar)
                    ->setUITableView($uiTableView);
        }
        
        $returnArray = $actionResult->getIndexReturnArray();
        
        return $returnArray;
    }
    
    public function actionSwitchQBAccountActiveStatus($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError();
        }
        $account = QBAccountFactory::getModelById($attributes['id']);
        if (empty($account)) {
            GI_URLUtils::redirectToError();
        }
        if (empty($account->getProperty('bos_active'))) {
            $active = false;
            $messageText = 'Are you sure you wish to make <b>' . $account->getNumberAndName() . '</b> visible in dropdown menus?';
            $submitButtonLabel = 'Yes, make it visible';
        } else {
            $active = true;
            $messageText = 'Are you sure you wish to make <b>' . $account->getNumberAndName() . '</b> hidden in dropdown menus?';
            $submitButtonLabel = 'Yes, make it hidden';
        }
        $form = new GI_Form('switch');
        $view = new GenericAcceptCancelFormView($form);
        $view->setModalHeader('QB Settings');
        $view->setHeaderText('Change Dropdown Status');
        $view->setMessageText($messageText);
        $view->setSubmitButtonLabel($submitButtonLabel);
        $view->buildForm();
        $success = 0;
        $newUrl = NULL;
        if ($form->wasSubmitted() && $form->validate()) {
            if ($active) {
                $account->setProperty('bos_active', 0);
            } else {
                $account->setProperty('bos_active', 1);
            }
            if ($account->save() && QBAccountFactory::refreshCachedDataFromDB()) {
                $newUrlAttributes = array(
                    'controller' => 'admin',
                    'action' => 'qbSettingsIndex',
                    'tab' => 'accounts',
                );
                if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
                    $newUrl = 'refresh';
                    $success = 1;
                } else {
                    GI_URLUtils::redirect($newUrlAttributes);
                }
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if (!empty($newUrl)) {
            $returnArray['newUrl'] = $newUrl;
        }
        return $returnArray;
    }

    public function actionSwitchQBProductActiveStatus($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError();
        }
        $product = QBProductFactory::getModelById($attributes['id']);
        if (empty($product)) {
            GI_URLUtils::redirectToError();
        }
        if (empty($product->getProperty('bos_active'))) {
            $active = false;
            $messageText = 'Are you sure you wish to make <b>' . $product->getName() . '</b> visible in dropdown menus?';
            $submitButtonLabel = 'Yes, make it visible';
        } else {
            $active = true;
            $messageText = 'Are you sure you wish to make <b>' . $product->getName() . '</b> hidden in dropdown menus?';
            $submitButtonLabel = 'Yes, make it hidden';
        }
        $form = new GI_Form('switch');
        $view = new GenericAcceptCancelFormView($form);
        $view->setModalHeader('QB Settings');
        $view->setHeaderText('Change Dropdown Status');
        $view->setMessageText($messageText);
        $view->setSubmitButtonLabel($submitButtonLabel);
        $view->buildForm();
        $success = 0;
        $newUrl = NULL;
        if ($form->wasSubmitted() && $form->validate()) {
            if ($active) {
                $product->setProperty('bos_active', 0);
            } else {
                $product->setProperty('bos_active', 1);
            }
            if ($product->save() && QBProductFactory::refreshCachedDataFromDB()) {
                $newUrlAttributes = array(
                    'controller' => 'admin',
                    'action' => 'qbSettingsIndex',
                    'tab' => 'products',
                );
                if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
                    $newUrl = 'refresh';
                    $success = 1;
                } else {
                    GI_URLUtils::redirect($newUrlAttributes);
                }
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if (!empty($newUrl)) {
            $returnArray['newUrl'] = $newUrl;
        }
        return $returnArray;
    }
    
    public function actionImportQBAccounts($attributes) {
        if (!Permission::verifyByRef('edit_qb_settings')) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $form = new GI_Form('import_qb_accounts');
        $view = new GenericAcceptCancelFormView($form);
        $view->setModalHeader('QB Settings');
        $view->setHeaderText('Import Accounts List from Quickbooks');
        $view->setMessageText('Are you sure you wish to import the list of accounts from Quickbooks?');
        $view->setSubmitButtonLabel('Yes, import the list');
        $view->buildForm();
        $success = 0;
        $newUrl = NULL;
        if ($form->wasSubmitted() && $form->validate()) {
            $updatedData = QBAccountFactory::updateDBDataFromQB();
            if (empty($updatedData) || (!empty($updatedData) && QBAccountFactory::refreshCachedDataFromDB())) {
                $newUrlAttributes = array(
                    'controller' => 'admin',
                    'action' => 'qbSettingsIndex',
                    'tab' => 'accounts',
                );
                if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
                    $newUrl = 'refresh';
                    $success = 1;
                } else {
                    GI_URLUtils::redirect($newUrlAttributes);
                }
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if (!empty($newUrl)) {
            $returnArray['newUrl'] = $newUrl;
        }
        return $returnArray;
    }

    public function actionImportQBProducts($attributes) {
        if (!Permission::verifyByRef('edit_qb_settings')) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $form = new GI_Form('import_qb_products');
        $view = new GenericAcceptCancelFormView($form);
        $view->setModalHeader('QB Settings');
        $view->setHeaderText('Import Products/Services List from Quickbooks');
        $view->setMessageText('Are you sure you wish to import the list of products/services from Quickbooks?');
        $view->setSubmitButtonLabel('Yes, import the list');
        $view->buildForm();
        $success = 0;
        $newUrl = NULL;
        if ($form->wasSubmitted() && $form->validate()) {
            $updatedData = QBProductFactory::updateDBDataFromQB();
            if (empty($updatedData) || (!empty($updatedData) && QBProductFactory::refreshCachedDataFromDB())) {
                $newUrlAttributes = array(
                    'controller' => 'admin',
                    'action' => 'qbSettingsIndex',
                    'tab' => 'products',
                );
                if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
                    $newUrl = 'refresh';
                    $success = 1;
                } else {
                    GI_URLUtils::redirect($newUrlAttributes);
                }
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if (!empty($newUrl)) {
            $returnArray['newUrl'] = $newUrl;
        }
        return $returnArray;
    }

    public function actionQBProductsIndex($attributes) {
                //TODO - permission check
        $sampleProduct = QBProductFactory::buildNewModel();
        if (isset($attributes['pageNumber'])) {
            $pageNumber = $attributes['pageNumber'];
        } else {
            $pageNumber = 1;
        }

        if (isset($attributes['queryId'])) {
            $queryId = $attributes['queryId'];
        } else {
            $queryId = NULL;
        }

        $search = QBProductFactory::search();

        QBConnection::addFranchiseFilterToDataSearch($search);
        $search->setPageNumber($pageNumber)
                ->setItemsPerPage(ProjectConfig::getUITableItemsPerPage())
                ->setQueryId($queryId)
                ->filter('qb_active', 1)
                ->orderBy('income_acct_qb_id', 'ASC');

        $tempRedirectArray = array(//For testing
            'controller' => 'accounting',
            'action' => 'qbProductsIndex'
        );
        $searchView = $sampleProduct->getSearchForm($search, $tempRedirectArray);

        $pageBarLinkProps = array(
            'controller' => 'accounting',
            'action' => 'QBProductsIndex',
        );
        
        $actionResult = ActionResultFactory::buildActionResult();
        $actionResult->setSearchView($searchView)
                ->setSampleModel($sampleProduct)
                ->setUseAjax(true);
        if(!GI_URLUtils::getAttribute('search')){
            $products = $search->select();
            $pageBar = $search->getPageBar($pageBarLinkProps);
            $uiTableCols = $sampleProduct->getUITableCols();
            $uiTableView = new UITableView($products, $uiTableCols, $pageBar);
            $view = $sampleProduct->getIndexView($products, $uiTableView, $sampleProduct, $searchView);
            if(GI_URLUtils::isAJAX()){
                $view->setAddWrap(false);
            }
            $actionResult->setView($view)
                    ->setPageBar($pageBar)
                    ->setUITableView($uiTableView);
        }
        
        $returnArray = $actionResult->getIndexReturnArray();
        
        return $returnArray;
    }
    
    public function actionViewQBSettings($attributes) {
        $settings = QBConnection::getQBSettingsModel();
        if (empty($settings)) {
            GI_URLUtils::redirectToError();
        }
        $view = $settings->getDetailView();
        $returnArray = GI_Controller::getReturnArray($view);
        return $returnArray;
    }

    public function actionEditQBSettings($attributes) {
        if (!Permission::verifyByRef('edit_qb_settings')) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $settings = QBConnection::getQBSettingsModel();
        if (empty($settings)) {
            GI_URLUtils::redirectToError();
        }
        $form = new GI_Form('qb_settings');
        $view = $settings->getFormView($form);
        $view->buildForm();
        $success = 0;
        $newUrl = NULL;
        $jQueryAction = '';
        if ($settings->handleFormSubmission($form)) {
            
            $newUrlAttributes = array(
                'controller' => 'admin',
                'action' => 'qbSettingsIndex',
                'tab'=>'general'
            );
            LogService::logActivity(GI_URLUtils::buildURL($newUrlAttributes), 'Quickbooks Settings', 'pencil', 'edit');
            LogService::setIgnoreNextLogView(true);
            if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
                //$newUrl = GI_URLUtils::buildURL($newUrlAttributes);
                $jQueryAction = 'giModalClose();';
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
        if (!empty($jQueryAction)) {
            $returnArray['jqueryAction'] = $jQueryAction;
        }
        $breadcrumbs = array();
        $breadcrumbs[] = array(
            'label'=>'Admin',
            'link'=>'',
        );
        $breadcrumbs[] = array(
            'label' => 'Quickbooks',
            'link' => ''
        );
        $breadcrumbs[] = array(
            'label' => 'Settings',
            'link' => GI_URLUtils::buildURL(array(
                'controller' => 'admin',
                'action' => 'qbSettingsIndex',
                'tab' => 'general'
            )),
        );
        $breadcrumbs[] = array(
            'label' => 'Edit Quickbooks Settings - General',
            'link' => GI_URLUtils::buildURL($attributes),
        );
        $returnArray['breadcrumbs'] = $breadcrumbs;
        return $returnArray;
    }

    public function actionRegionalQBSettingsIndex($attributes) {
        $regions = RegionFactory::getAll();
        $view = new RegionQBSettingsIndexView($regions);
        $view->setCurrentTab(ProjectConfig::getDefaultCountryCode() . '_' . ProjectConfig::getDefaultRegionCode());
        $returnArray = GI_Controller::getReturnArray($view);
        return $returnArray;
    }

    public function actionViewRegionQBSettings($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError();
        }
        $regionId = $attributes['id'];
        $region = RegionFactory::getModelById($regionId);
        if (empty($region)) {
            GI_URLUtils::redirectToError();
        }
        $view = $region->getQBSettingsDetailView(); 
        if (isset($attributes['tabbed']) && $attributes['tabbed'] == 1) {
            $view->setIsTabbed(true);
        }
        $returnArray = GI_Controller::getReturnArray($view);
        return $returnArray;
    }

    public function actionEditRegionQBSettings($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError();
        }
        $regionId = $attributes['id'];
        $region = RegionFactory::getModelById($regionId);
        if (empty($region)) {
            GI_URLUtils::redirectToError();
        }
        if (!$region->getIsQBSettingsEditable()) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $form = new GI_Form('edit_region_qb_settings');
        $view = $region->getQBSettingsFormView($form);
        $view->buildForm();
        $success = 0;
        $jQueryAction = NULL;
        $newUrl = NULL;
        if ($region->handleQBSettingsFormSubmission($form)) {
            $newUrlAttributes = array(
                'controller' => 'admin',
                'action' => 'qbSettingsIndex',
                'tab' => 'regional'
            );
            if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
                $success = 1;
                $newUrl = GI_URLUtils::buildURL($newUrlAttributes);
            } else {
                GI_URLUtils::redirect($newUrlAttributes);
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        if (!empty($newUrl)) {
            $returnArray['newUrl'] = $newUrl;
        }
        $returnArray['success'] = $success;
        $returnArray['jqueryAction'] = $jQueryAction;
        return $returnArray;
    }

    public function actionUpdateMultiInvItemQBSettings($attributes) {
        if (!Permission::verifyByRef('edit_qb_settings')) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $form = new GI_Form('update_inv_qb_settings');
        $sampleInvItem = InvItemFactory::buildNewModel('item');
        $view = new AccountingUpdateMultiInvItemQBSettingsFormView($form, $sampleInvItem);
        $view->buildForm();
        $success = 0;
        $jQueryAction = NULL;
        if ($form->wasSubmitted() && $form->validate()) {
            $brandIds = filter_input(INPUT_POST, 'brand_ids', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            $invItemTypeRefs = filter_input(INPUT_POST, 'inv_item_type_refs', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            $search = InvItemFactory::search();
            if (isset($brandIds[0]) && empty($brandIds[0])) {
                unset($brandIds[0]);
                $brandIds = array_values($brandIds);
            }
            if (!empty($brandIds)) {
                $search->filterGroup();
                $brandIdCount = count($brandIds);
                for ($i = 0; $i < $brandIdCount; $i++) {
                    $brandId = $brandIds[$i];
                    if (!empty($brandId)) {
                        $search->filter('inv_item_brand_id', $brandId);
                        if ($i != $brandIdCount - 1) {
                            $search->orIf();
                        }
                    }
                }
                $search->closeGroup()
                        ->andIf();
            }
            if (isset($invItemTypeRefs[0]) && empty($invItemTypeRefs[0])) {
                unset($invItemTypeRefs[0]);
                $invItemTypeRefs = array_values($invItemTypeRefs);
            }
            if (!empty($invItemTypeRefs)) {
                $typeRefCount = count($invItemTypeRefs);
                $search->filterGroup();
                for ($j = 0; $j < $typeRefCount; $j++) {
                    $search->filterByTypeRef($invItemTypeRefs[$j], false);
                    if ($j != $typeRefCount - 1) {
                        $search->orIf();
                    }
                }
                $search->closeGroup()
                        ->andIf();
            }

            $invItems = $search->select();
            $defaults = array();
      
            $invAssetQBId = filter_input(INPUT_POST, 'inv_item_qb_default_inv_asset');
            $cogsQBId = filter_input(INPUT_POST, 'inv_item_qb_default_cogs');
            $wasteQBId = filter_input(INPUT_POST, 'inv_item_qb_default_waste');
            $salesQBId = filter_input(INPUT_POST, 'inv_item_qb_default_sales');
            
            $invAssetDefault = InvItemQBDefaultFactory::buildNewModel('inv_asset');
            $invAssetDefault->setProperty('qb_id', $invAssetQBId);
            $defaults[] = $invAssetDefault;
            $cogsDefault = InvItemQBDefaultFactory::buildNewModel('cogs');
            $cogsDefault->setProperty('qb_id', $cogsQBId);
            $defaults[] = $cogsDefault;
            $wasteDefault = InvItemQBDefaultFactory::buildNewModel('waste');
            $wasteDefault->setProperty('qb_id', $wasteQBId);
            $defaults[] = $wasteDefault;
            $salesDefault = InvItemQBDefaultFactory::buildNewModel('sales');
            $salesDefault->setProperty('qb_id', $salesQBId);
            $defaults[] = $salesDefault;

            if (InvItemQBDefaultFactory::updateMultipleInvItemDefaults($invItems, $defaults)) {
                $newUrlAttributes = array(
                    'controller' => 'admin',
                    'action' => 'qbSettingsIndex',
                    'tab' => 'general'
                );
                if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
                    $jQueryAction = 'giModalClose();';
                    $success = 1;
                } else {
                    GI_URLUtils::redirect($newUrlAttributes);
                }
            } else {
                GI_URLUtils::redirectToError();
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if (!empty($jQueryAction)) {
            $returnArray['jqueryAction'] = $jQueryAction;
        }
        return $returnArray;
    }

}
