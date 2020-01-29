<?php
/**
 * Description of AbstractCoreController
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
class AbstractCoreController extends GI_Controller {
    
    public function actionConvert($attributes){
        if(!isset($attributes['val']) || !isset($attributes['unitType']) || !isset($attributes['curUnit']) || !isset($attributes['targetUnit'])){
            GI_URLUtils::redirectToError(2000);
        }
        
        $val = (float) $attributes['val'];
        $curUnit = $attributes['curUnit'];
        $targetUnit = $attributes['targetUnit'];
        $success = 0;
        $result = 0;
        switch($attributes['unitType']){
            case 'length':
                $result = GI_Measurement::convertValToNewLengthUnits($val, $curUnit, $targetUnit);
                $success = 1;
                break;
            case 'volume':
                $result = GI_Measurement::convertValToNewVolumeUnits($val, $curUnit, $targetUnit);
                $success = 1;
                break;
        }
        
        $returnArray = array(
            'converted' => $result,
            'success' => $success
        );
        return $returnArray;
    }

    public function actionAutocompContextRole($attributes) {
        if ((!isset($attributes['ajax']) || !$attributes['ajax'] == 1 || !isset($attributes['tableName']))) {
            $returnArray = GI_Controller::getReturnArray();
            return $returnArray;
        }

        if (isset($attributes['curVal'])) {
            $curVal = $attributes['curVal'];
            $curVals = explode(',', $curVal);

            $results = array(
                'label' => array(),
                'value' => array(),
                'autoResult' => array()
            );
            foreach($curVals as $contextRoleId){
                $contextRole = ContextRoleFactory::getModelById($contextRoleId);
                if($contextRole){
                    $acResult = $contextRole->getAutocompResult();

                    foreach($acResult as $key => $val){
                        if(!isset($results[$key])){
                            $results[$key] = array();
                        }
                        $results[$key][] = $val;
                    }
                }
            }
            return $results;
        } else {
            if(isset($_REQUEST['term'])){
                $term = $_REQUEST['term'];
            } else {
                $term = '';
            }

            $search = ContextRoleFactory::search()
                    ->setItemsPerPage(ProjectConfig::getAutocompleteItemLimit());
            $joinTableName = ContextRoleFactory::getDbPrefix() . 'context_role';
            $termCols = array(
                $joinTableName.'.title',
            );
            $search->filterTermsLike($termCols, $term)
                    ->orderByLikeScore($termCols, $term);
            
            $search->filter('table_name', $attributes['tableName']);
            if (isset($attributes['itemId'])) {
                $join = $search->createJoin('context_role', 'source_context_role_id', $joinTableName, 'id', 'SRC2', 'left');
                $join->filter('SRC2.table_name', $attributes['tableName'])
                        ->filter('SRC2.item_id', $attributes['itemId']);
                $search->filterGroup()
                        ->filterGroup()
                        ->filter('item_id', $attributes['itemId'])
                        ->closeGroup()
                        ->orIf()
                        ->filterGroup()
                        ->andIf()
                        ->filterNullOr('SRC2.status')
                        ->filterNull('item_id')
                        ->closeGroup()
                        ->closeGroup()
                        ->andIf();
            } else {
                $search->filterNull('item_id');
            }
            
            $search->groupBy('id');

            $contextRoles = $search->select();

            $results = array();

            foreach($contextRoles as $contextRole){
                /* @var $contextRole AbstractContextRole */
                $contextRoleInfo = $contextRole->getAutocompResult($term);
                $results[] = $contextRoleInfo;
            }
            //@todo move a lot of the autocomplete functions into helper class
            $itemsPerPage = $search->getItemsPerPage();
            $count = $search->getCount();
            if (!empty($itemsPerPage) && $count > $itemsPerPage) {
                $results[] = array(
                    'preventDefault' => 1,
                    'liClass' => 'more_results',
                    'autoResult' => '&hellip;'
                );
            }

            return $results;
        }
    }
    
}
