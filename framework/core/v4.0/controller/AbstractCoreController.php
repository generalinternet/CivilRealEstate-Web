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
    
}
