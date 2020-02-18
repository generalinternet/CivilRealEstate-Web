<?php

require_once 'framework/core/' . FRMWK_CORE_VER . '/controller/AbstractAutocompleteController.php';

class AutocompleteController extends AbstractAutocompleteController {
    
    public function actionSearchCharity($attributes){
        if ((!isset($attributes['ajax']) || !$attributes['ajax'] == 1)){
            $returnArray = GI_Controller::getReturnArray();
            return $returnArray;
        }

        if(isset($attributes['curVal'])){
            $curVal = $attributes['curVal'];
            $curVals = explode(',', $curVal);

            $finalLabel = array();
            $finalValue = array();
            $finalResult = array();

            foreach($curVals as $charityItemId){
                $charityItem = CharityFactory::getModelById($charityItemId);
                if($charityItem){
                    $name = $charityItem->getProperty('name');

                    $finalLabel[] = $name;
                    $finalValue[] = $charityItemId;
                    $finalResult[] = '<span class="result_text">'.$name.'</span>';
                }
            }
            $results = array(
                'label' => $finalLabel,
                'value' => $finalValue,
                'autoResult' => $finalResult
            );
            return $results;
        } else {
            if(isset($_REQUEST['term'])){
                $term = $_REQUEST['term'];
            } else {
                $term = '';
            }

            $charityItemSearch = CharityFactory::search()
                    ->setItemsPerPage(ProjectConfig::getAutocompleteItemLimit())
                    ->filterLike('name', '%' . $term . '%')
                    ->orderByLikeScore('name', $term);

            $charityItems = $charityItemSearch->select();

            $results = array();

            foreach($charityItems as $charityItem){
                /* @var $charityItem InvItem */
                $name = $charityItem->getProperty('name');

                $charityItemInfo = array(
                    'label' => $name,
                    'value' => $name,
                    'autoResult' => '<span class="result_text"><i class="float_right">'.$name.'</i>' . $this->markTerm($term, $name) . '</span>'
                );
                //@todo get real per case value
                $results[] = $charityItemInfo;

            }

            $this->addMoreResult($charityItemSearch, $results);

            return $results;
        }
    }

}
