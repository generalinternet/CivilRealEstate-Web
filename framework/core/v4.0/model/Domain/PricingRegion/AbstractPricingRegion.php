<?php

abstract class AbstractPricingRegion extends GI_Model {

    public function getTitle() {
        return $this->getProperty('title');
    }
    
    public function getRef(){
        return $this->getProperty('ref');
    }

    public function getViewURLAttrs() {
        return array(
            'controller' => 'admin',
            'action' => 'viewPricingRegion',
            'id' => $this->getProperty('id')
        );
    }

    public function getEditURL() {
        return GI_URLUtils::buildURL(array(
                    'controller' => 'admin',
                    'action' => 'editPricingRegion',
                    'id' => $this->getProperty('id')
        ));
    }

    public function getDetailView() {
        return new PricingRegionDetailView($this);
    }

    public function getFormView(GI_Form $form) {
        return new PricingRegionFormView($form, $this);
    }

    public static function getUITableCols() {
        $tableColArrays = array(
            array(
                'header_title' => 'Title',
                'method_name' => 'getTitle',
                'cell_url_method_name' => 'getViewURL',
            ),
        );
        $UITableCols = array();
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UITableCols;
    }
    
    public static function getUIRolodexCols() {
        $tableColArrays = array(
            //Title
            array(
                'method_name' => 'getTitle',
            ),
        );
        $UIRolodexCols = array();
        foreach ($tableColArrays as $tableColArray) {
            $UIRolodexCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UIRolodexCols;
    }
    
    public function handleFormSubmission(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            if (!$this->handleFormFields())  {
                return false;
            }
            if (!$this->save()) {
                return false;
            }
            if (!$this->handleCountriesAndRegionsFields()) {
                return false;
            }
            return true;
        }
        return false;
    }
    
    protected function handleFormFields() {
        $title = filter_input(INPUT_POST, 'title');
        $ref = GI_StringUtils::formatStringForRef($title);
        
        $this->setProperty('title', $title);
        $this->setProperty('ref', $ref);
        return true;
    }
    
    protected function handleCountriesAndRegionsFields() {
        if (!$this->handleCountriesField()) {
            return false;
        }
        if (!$this->handleRegionsField()) {
            return false;
        }
        return true;
    }

    protected function handleCountriesField() {
        $existingCountryRefs = $this->getCountryRefs(true);
        $submittedCountryRefs = filter_input(INPUT_POST, 'country_refs', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        foreach ($submittedCountryRefs as $submittedCountryRef) {
            $index = array_search($submittedCountryRef, $existingCountryRefs);
            if ($index !== false) {
                unset($existingCountryRefs[$index]);
            } else {
                $buildNewModel = false;
                $softDeletedSearch = PricingRegionInclFactory::search()
                        ->filter('status', 0)
                        ->filter('pricing_region_id', $this->getProperty('id'))
                        ->filter('country_code', $submittedCountryRef);
                $softDeletedArray = $softDeletedSearch->select();
                if (!empty($softDeletedArray)) {
                    $softDeletedModel = $softDeletedArray[0];
                    $softDeletedModel->setProperty('status', 1);
                    if (!$softDeletedModel->save()) {
                        $buildNewModel = true;
                    }
                } else {
                    $buildNewModel = true;
                }
                if ($buildNewModel) {
                    $newInclusion = PricingRegionInclFactory::buildNewModel();
                    $newInclusion->setProperty('pricing_region_id', $this->getProperty('id'));
                    $newInclusion->setProperty('country_code', $submittedCountryRef);
                    if (!$newInclusion->save()) {
                        return false;
                    }
                }
            }
        }
        foreach ($existingCountryRefs as $countryRefToRemove) {
            $search = PricingRegionInclFactory::search()
                    ->filter('pricing_region_id', $this->getProperty('id'))
                    ->filter('country_code', $countryRefToRemove)
                    ->filterNull('region_code');
            $inclusionsToRemove = $search->select();
            if (!empty($inclusionsToRemove)) {
                foreach ($inclusionsToRemove as $inclusionToRemove) {
                    if (!$inclusionToRemove->softDelete()) {
                        return false;
                    }
                }
            }
        }
        return true;
    }
    
    
    public function getCountryRefs($nullRegionCode = false) {
        $search = PricingRegionInclFactory::search()
                ->filter('pricing_region_id', $this->getProperty('id'))
                ->groupBy('country_code');
        if ($nullRegionCode === true) {
            $search->filterNull('region_code');
        }
        $pricingRegionInclArray = $search->select();
        $results = array();
        if (!empty($pricingRegionInclArray)) {
            foreach($pricingRegionInclArray as $pricingRegionIncl) {
                $results[] = $pricingRegionIncl->getProperty('country_code');
            }
        }
        return $results;
    }
    
    public function getRegionRefs() {
        $search = PricingRegionInclFactory::search()
                ->filter('pricing_region_id', $this->getProperty('id'))
                ->filterNotNull('region_code');
        $pricingRegionInclArray = $search->select();
        $results = array();
        if (!empty($pricingRegionInclArray)) {
            foreach ($pricingRegionInclArray as $pricingRegionIncl) {
                $results[] = $pricingRegionIncl->getProperty('country_code') . '_' . $pricingRegionIncl->getProperty('region_code');
            }
        }
        return $results;
    }

    public function handleRegionsField() {
        $existingRegionsRefs = $this->getRegionRefs();
        $submittedRegionsRefs = filter_input(INPUT_POST, 'region_refs', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $submittedCountryRefs = filter_input(INPUT_POST, 'country_refs', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        foreach ($submittedRegionsRefs as $submittedRegionRef) {
            $refArray = $this->separateCountryAndRegionRefs($submittedRegionRef);
            $countryRef = $refArray[0];
            $regionRef = $refArray[1];
            if (in_array($countryRef, $submittedCountryRefs)) {
                $index = array_search($submittedRegionRef, $existingRegionsRefs);
                if ($index !== false) {
                    unset($existingRegionsRefs[$index]);
                } else {
                    $softDeletedSearch = PricingRegionInclFactory::search()
                            ->filter('pricing_region_id', $this->getProperty('id'))
                            ->filter('region_code', $regionRef)
                            ->filter('country_code', $countryRef)
                            ->filter('status', 0);
                    $softDeletedArray = $softDeletedSearch->select();
                    $buildNewModel = false;
                    if (!empty($softDeletedArray)) {
                        $softDeletedModel = $softDeletedArray[0];
                        $softDeletedModel->setProperty('status', 1);
                        if (!$softDeletedModel->save()) {
                            $buildNewModel = true;
                        }
                    } else {
                        $buildNewModel = true;
                    }
                    if ($buildNewModel) {
                        $newInclusion = PricingRegionInclFactory::buildNewModel();
                        $newInclusion->setProperty('region_code', $regionRef);
                        $newInclusion->setProperty('country_code', $countryRef);
                        $newInclusion->setProperty('pricing_region_id', $this->getProperty('id'));
                        if (!$newInclusion->save()) {
                            return false;
                        }
                    }
                    $entireCountryInclusionSearch = PricingRegionInclFactory::search()
                            ->filter('pricing_region_id', $this->getProperty('id'))
                            ->filter('country_code', $countryRef)
                            ->filterNULL('region_code');
                    $entireCountryInclusions = $entireCountryInclusionSearch->select();
                    if (!empty($entireCountryInclusions)) {
                        foreach ($entireCountryInclusions as $entireCountryInclusion) {
                            if (!$entireCountryInclusion->softDelete()) {
                                return false;
                            }
                        }
                    }
                }
            }
        }
        foreach ($existingRegionsRefs as $regionRefToRemove) {
            $separatedRefArray = $this->separateCountryAndRegionRefs($regionRefToRemove);
            $countryRef = $separatedRefArray[0];
            $regionRef = $separatedRefArray[1];
            $search = PricingRegionInclFactory::search()
                    ->filter('pricing_region_id', $this->getProperty('id'))
                    ->filter('region_code', $regionRef)
                    ->filter('country_code', $countryRef);
            $modelsToRemove = $search->select();
            if (!empty($modelsToRemove)) {
                foreach ($modelsToRemove as $modelToRemove) {
                    if (!$modelToRemove->softDelete()) {
                        return false;
                    }
                }
            }
        }
        return true;
    }
    
    protected function separateCountryAndRegionRefs($countryAndRegionRefString) {
        $array = explode('_', $countryAndRegionRefString);
        return $array;
    }
    
    public function getIncludedCountriesAndRegionsNamesArray() {
        return GeoDefinitions::getCountryAndRegionNameArrayByPricingRegion($this);
    }

    public function getListBarURLAttrs(){
        $attrs = array(
            'controller' => 'admin',
            'action' => 'pricingRegionIndex',
            'curId' => $this->getId()
        );
        $typeRef = $this->getTypeRef();
        if($typeRef){
            $attrs['type'] = $typeRef;
        }
        return $attrs;
    }
}
