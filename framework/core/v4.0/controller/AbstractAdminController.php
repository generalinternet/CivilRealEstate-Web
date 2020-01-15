<?php

abstract class AbstractAdminController extends GI_Controller {

    public function actionIndex($attributes) {
        $view = new AdminIndexView();
        $returnArray = GI_Controller::getReturnArray($view);
        return $returnArray;
    }

    public function actionEditCurrencies($attributes) {
        $form = new GI_Form('edit_currencies');
        $currencies = CurrencyFactory::getAll();
        $view = new AdminEditCurrencyFormView($form, $currencies);
        if ($form->wasSubmitted() && $form->validate()) {
            foreach ($currencies as $currency) {
                if (!$currency->handleFormSubmission($form)) {
                    GI_URLUtils::redirectToError(1000);
                }
            }
            GI_URLUtils::redirect(array(
                'controller' => 'dashboard',
                'action' => 'index'
            ));
        }
        $returnArray = GI_Controller::getReturnArray($view);
        return $returnArray;
    }

    public function actionPricingRegionIndex($attributes) {
        if (!Permission::verifyByRef('view_pricing_regions')) {
            GI_URLUtils::redirectToAccessDenied();
        }
        
        if (isset($attributes['targetId'])) {
            $targetId = $attributes['targetId'];
        } else {
            $targetId = 'list_bar';
            GI_URLUtils::setAttribute('targetId', 'list_bar');
        }
        
        $pageBarLinkArray = array(
            'controller' => 'admin',
            'action' => 'pricingRegionIndex'
        );
        
        $pricingRegionSearch = PricingRegionFactory::search();
        $pricingRegions = $pricingRegionSearch->select();
        $pageBar = $pricingRegionSearch->getPageBar($pageBarLinkArray);
        $samplePricingRegion = PricingRegionFactory::buildNewModel();
        if ($targetId == 'list_bar') {
            //Tile style view
            $uiTableCols = $samplePricingRegion->getUIRolodexCols();
            $uiTableView = new UIRolodexView($pricingRegions, $uiTableCols, $pageBar);
            $uiTableView->setLoadMore(true);
            $uiTableView->setShowPageBar(false);
            if(isset($attributes['curId']) && $attributes['curId'] != ''){
                $uiTableView->setCurId($attributes['curId']);
            }
        } else {
            //List style view
            $uiTableCols = $samplePricingRegion->getUITableCols();
            $uiTableView = new UITableView($pricingRegions, $uiTableCols);
        }
        $view = new PricingRegionIndexView($pricingRegions, $uiTableView, $samplePricingRegion);
        $returnArray = GI_Controller::getReturnArray($view);
        $breadcrumbs = array(
            array(
                'label' => 'Admin',
                'link' => ''
            ),
            array(
                'label' => 'Pricing Regions',
                'link' => GI_URLUtils::buildURL($attributes)
            ),
        );
        $returnArray['breadcrumbs'] = $breadcrumbs;
        return $returnArray;
    }

    public function actionViewPricingRegion($attributes) {
        if (!Permission::verifyByRef('view_pricing_regions')) {
            GI_URLUtils::redirectToAccessDenied();
        }
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $pricingRegion = PricingRegionFactory::getModelById($attributes['id']);
        if (empty($pricingRegion)) {
            GI_URLUtils::redirectToError(2000);
        }
        $view = $pricingRegion->getDetailView();
        $returnArray = GI_Controller::getReturnArray($view);
        $breadcrumbs = array(
            array(
                'label' => 'Admin',
                'link' => ''
            ),
            array(
                'label' => 'Pricing Regions',
                'link' => GI_URLUtils::buildURL(array(
                    'controller' => 'admin',
                    'action' => 'pricingRegionIndex',
                ))
            ),
            array(
                'label' => $pricingRegion->getTitle(),
                'link' => $pricingRegion->getViewURL(),
            ),
        );
        $returnArray['breadcrumbs'] = $breadcrumbs;
        return $returnArray;
    }

    public function actionAddPricingRegion($attributes) {
        if (!Permission::verifyByRef('add_pricing_regions')) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $pricingRegion = PricingRegionFactory::buildNewModel();
        $form = new GI_Form('add_pricing_region');
        $view = $pricingRegion->getFormView($form);
        $view->buildForm();
        if ($pricingRegion->handleFormSubmission($form)) {
            GI_URLUtils::redirect(array(
                'controller' => 'admin',
                'action' => 'viewPricingRegion',
                'id' => $pricingRegion->getProperty('id'),
            ));
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $breadcrumbs = array(
            array(
                'label' => 'Admin',
                'link' => ''
            ),
            array(
                'label' => 'Pricing Regions',
                'link' => GI_URLUtils::buildURL(array(
                    'controller' => 'admin',
                    'action' => 'pricingRegionIndex',
                ))
            ),
            array(
                'label' => 'Add Pricing Region',
                'link' => GI_URLUtils::buildURL(array(
                    'controller' => 'admin',
                    'action' => 'addPricingRegion',
                    'id' => $pricingRegion->getProperty('id')
                )),
            ),
        );
        $returnArray['breadcrumbs'] = $breadcrumbs;
        return $returnArray;
    }

    public function actionEditPricingRegion($attributes) {
        if (!Permission::verifyByRef('edit_pricing_regions')) {
            GI_URLUtils::redirectToAccessDenied();
        }
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $pricingRegion = PricingRegionFactory::getModelById($attributes['id']);
        if (empty($pricingRegion)) {
            GI_URLUtils::redirectToError(2000);
        }
        $form = new GI_Form('edit_pricing_region');
        $view = $pricingRegion->getFormView($form);
        $view->buildForm();
        if ($pricingRegion->handleFormSubmission($form)) {
            GI_URLUtils::redirect(array(
                'controller' => 'admin',
                'action' => 'viewPricingRegion',
                'id' => $pricingRegion->getProperty('id'),
            ));
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $breadcrumbs = array(
            array(
                'label' => 'Admin',
                'link' => ''
            ),
            array(
                'label' => 'Pricing Regions',
                'link' => GI_URLUtils::buildURL(array(
                    'controller' => 'admin',
                    'action' => 'pricingRegionIndex',
                ))
            ),
            array(
                'label' => $pricingRegion->getTitle(),
                'link' => $pricingRegion->getViewURL(),
            ),
            array(
                'label' => 'Edit Pricing Region',
                'link' => GI_URLUtils::buildURL(array(
                    'controller' => 'admin',
                    'action' => 'editPricingRegion',
                    'id' => $pricingRegion->getProperty('id')
                )),
            ),
        );
        $returnArray['breadcrumbs'] = $breadcrumbs;
        return $returnArray;
    }
    
    public function actionEcoFeeIndex($attributes) {
        $sampleRegion = RegionFactory::buildNewModel();
        if (empty($sampleRegion) || !$sampleRegion->getIsEcoFeeIndexViewable()) {
            GI_URLUtils::redirectToAccessDenied();
        }
        if (!isset($attributes['id'])) {
            $regionCode = ProjectConfig::getDefaultRegionCode();
            $countryCode = ProjectConfig::getDefaultCountryCode();
        } else {
            $region = RegionFactory::getModelById($attributes['id']);
            $regionCode = $region->getProperty('region_code');
            $countryCode = $region->getProperty('country_code');
        }
       
        if (isset($attributes['cc'])) {
            $countryCodesString = $attributes['cc'];
        } else {
            $countryCodesString = ProjectConfig::getDefaultEcoFeeCountryCodes();
        }
        $countryCodesArray = explode(',', $countryCodesString);
        $search = RegionFactory::search()
                ->filterGroup();
        if (!empty($countryCodesArray)) {
            foreach ($countryCodesArray as $cc) {
                $search->filter('country_code', $cc)
                        ->orIf();
            }
        }
        $search->closeGroup()
                ->andIf();
        $regions = $search->select();
        if (empty($regions)) {
            GI_URLUtils::redirectToError();
        }
        if (in_array($countryCode, $countryCodesArray)) {
             $currentTabKey = $countryCode . '_' . $regionCode;
        } else {
            $firstRegion = $regions[0];
            $currentTabKey = $firstRegion->getProperty('country_code') . '_' . $firstRegion->getProperty('region_code');
        }
        $view = new RegionEcoFeeIndexView($regions);
        $view->setCurrentTab($currentTabKey);
        LogService::logActivity(GI_URLUtils::buildURL($attributes), $sampleRegion->getEcoFeeIndexTitle(), 'visible', 'view');
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['breadcrumbs'] = array(
            array(
                'label'=>'Admin',
                'link'=>'',
                ),
            array(
                'label'=>$regions[0]->getEcoFeeIndexTitle(),
                'link'=>  GI_URLUtils::buildURL($attributes),
            ),
        );
        return $returnArray;
    }

    public function actionEditEcoFees($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError();
        }
        $regionId = $attributes['id'];
        $region = RegionFactory::getModelById($regionId);
        if (!$region->getIsEcoFeesEditable()) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $form = new GI_Form('eco_fees');
        $view = $region->getEcoFeeFormView($form);
        $view->buildForm();
        if ($region->handleEcoFeeFormSubmission($form)) {
            $newUrlAttributes = array(
                'controller' => 'admin',
                'action' => 'ecoFeeIndex',
                'id' => $regionId
            );
            LogService::logActivity(GI_URLUtils::buildURL($newUrlAttributes), $region->getEcoFeeIndexTitle() . ': ' . $region->getProperty('region_name') , 'pencil', 'edit');
            LogService::setIgnoreNextLogView(true);
            GI_URLUtils::redirect($newUrlAttributes);
        }
        $returnArray = GI_Controller::getReturnArray($view);
        return $returnArray;
    }

    public function actionAddEcoFeeRow($attributes) {
        $returnArray = GI_Controller::getReturnArray();
        if (!isset($attributes['ajax']) || $attributes['ajax'] != 1 || !isset($attributes['seq'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $seq = $attributes['seq'];
        $typeRef = 'eco_fee';
        if (isset($attributes['typeRef']) && $attributes['typeRef']) {
            $typeRef = $attributes['typeRef'];
        }
        $ecoFee = EcoFeeFactory::buildNewModel($typeRef);
        if (empty($ecoFee)) {
            return $returnArray;
        }
        $region = NULL;
        if (isset($attributes['regionId'])) {
            $region = RegionFactory::getModelById($attributes['regionId']);
        } 
        if (empty($region)) {
            $region = RegionFactory::getModelByCodes(ProjectConfig::getDefaultCountryCode(), ProjectConfig::getDefaultRegionCode());
        }
        $ecoFee->setFieldSuffix($seq);
        $tempForm = new GI_Form('temp_form');
        $formView = $ecoFee->getFormView($tempForm);
        $formView->setFullView(false);
        $formView->buildForm();
        return array(
            'formRow' => $formView->getHTMLView()
        );
    }

    public function actionViewEcoFees($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError();
        }
        $regionId = $attributes['id'];
        $region = RegionFactory::getModelById($regionId);
        if (empty($region)) {
            GI_URLUtils::redirectToError();
        }
        $view = $region->getEcoFeeDetailView();
        if (isset($attributes['tabbed']) && $attributes['tabbed'] == 1) {
            $view->setIsTabbed(true);
        }
        $returnArray = GI_Controller::getReturnArray($view);
        return $returnArray;
    }
    
    public function actionViewLogs($attributes){
        $view = new AdminEchoView();
        $logs = GI_LogFactory::getLogs();
        if($logs){
            foreach($logs as $log){
                $view->addString('<h2>' . $log->getLogName() . '</h2>');
                $view->addString('<div class="flex_table">');
                $view->addString($log->getLogRowHead());
                $view->addString($log->getLogRows());
                $view->addString('</div>');
                $log->dumpLog();
            }
        } else {
            $view->addString('<p>No logged data.</p>');
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['breadcrumbs'] = array(
            array(
                'link' => GI_URLUtils::buildURL($attributes),
                'label' => 'Logs'
            )
        );
        return $returnArray;
    }

    public function actionQBSettingsIndex($attributes) {
        if (!Permission::verifyByRef('view_qb_settings')) {
            GI_URLUtils::redirectToAccessDenied();
        }
        if (isset($attributes['tab'])) {
            $currentTabKey = $attributes['tab'];
        } else {
            $currentTabKey = 'general';
        }
        $view = new QBSettingsIndexView();
        $sampleRegion = RegionFactory::buildNewModel();
        if (!empty($sampleRegion) && !$sampleRegion->getIsQBSettingsViewable()) {
            $view->setShowRegionalTab(false);
            if ($currentTabKey == 'regional') {
                $currentTabKey = 'general';
            }
        }
        $view->setCurrentTab($currentTabKey);
        LogService::logActivity(GI_URLUtils::buildURL($attributes), 'Quickbooks Settings', 'visible', 'view');
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['breadcrumbs'] = array(
            array(
                'label' => 'Admin',
                'link' => '',
            ),
            array(
                'label' => 'Quickbooks Settings',
                'link' => GI_URLUtils::buildURL($attributes),
            ),
        );
        return $returnArray;
    }



}
