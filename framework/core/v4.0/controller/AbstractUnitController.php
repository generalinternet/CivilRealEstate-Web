<?php

class AbstractUnitController extends GI_Controller {

    public function actionConvertDistance($attributes) {
        if (!isset($attributes['ajax']) || $attributes['ajax'] != 1 || !isset($attributes['tUnitRef']) || !isset($attributes['unitRef']) || !isset($attributes['length'])) {
            return NULL;
        }
        $tUnitRef = $attributes['tUnitRef'];
        $unitRef = $attributes['unitRef'];
        $length = $attributes['length'];
        if ($tUnitRef === $unitRef) {
            $val = $length;
        } else {
            $val = GI_Measurement::convertValToNewLengthUnits($length, $unitRef, $tUnitRef);
        }
        $returnArray = GI_Controller::getReturnArray();
        $returnArray['data'] = $val;
        return $returnArray;
    }

    public function actionConvertAreaToSQFT($attributes) {
        if (!isset($attributes['ajax']) || $attributes['ajax'] != 1 || !isset($attributes['unitRef']) || !isset($attributes['length']) || !isset($attributes['width'])) {
            return NULL;
        }
        $unitRef = $attributes['unitRef'];
        $length = $attributes['length'];
        $width = $attributes['width'];
        if ($unitRef == 'ft') {
            $val = $length * $width;
        } else {
            $lengthInFt = GI_Measurement::convertValToNewLengthUnits($length, $unitRef, 'ft');
            $widthInFt = GI_Measurement::convertValToNewLengthUnits($width, $unitRef, 'ft');
            $val = $lengthInFt * $widthInFt;
        }
        $returnArray = GI_Controller::getReturnArray();
        $returnArray['data'] = $val;
        return $returnArray;
    }

    public function actionConvertAreaToSQM($attributes) {
        if (!isset($attributes['ajax']) || $attributes['ajax'] != 1 || !isset($attributes['unitRef']) || !isset($attributes['length']) || !isset($attributes['width'])) {
            return NULL;
        }
        $unitRef = $attributes['unitRef'];
        $length = $attributes['length'];
        $width = $attributes['width'];
        if ($unitRef == 'm') {
            $val = $length * $width;
        } else {
            $lengthInM = GI_Measurement::convertValToNewLengthUnits($length, $unitRef, 'm');
            $widthInM = GI_Measurement::convertValToNewLengthUnits($width, $unitRef, 'm');
            $val = $lengthInM * $widthInM;
        }
        $returnArray = GI_Controller::getReturnArray();
        $returnArray['data'] = $val;
        return $returnArray;
    }
    
    public function actionGetUnitTitle($attributes){
        if (!isset($attributes['ajax']) || $attributes['ajax'] != 1) {
            return NULL;
        }
        
        if(isset($attributes['unitRef'])){
            $unit = PricingUnitFactory::getModelByRef($attributes['unitRef']);
        } elseif(isset($attributes['unitId'])){
            $unit = PricingUnitFactory::getModelById($attributes['unitId']);
        }
        
        $returnArray = GI_Controller::getReturnArray();
        if (!empty($unit)) {
            $returnArray['title'] = $unit->getProperty('title');
            $returnArray['pluralTitle'] = $unit->getProperty('pl_title');
        }
        
        return $returnArray;
    }
    
    public function actionGetPricingUnitByMeasurmentUnitRef($attributes){
        if (!isset($attributes['ajax']) || $attributes['ajax'] != 1) {
            return NULL;
        }
        $returnArray = GI_Controller::getReturnArray();
        
        if(isset($attributes['mUnitRef'])){
            $unitRef = GI_Measurement::getSquareUnitRef($attributes['mUnitRef']);
            if($unitRef){
                $unit = PricingUnitFactory::getModelByRef($unitRef);
                if($unit){
                    $returnArray['unitId'] = $unit->getProperty('id');
                    $returnArray['title'] = $unit->getProperty('title');
                    $returnArray['pluralTitle'] = $unit->getProperty('pl_title');
                }
            }
        }
        
        return $returnArray;
    }
    
    public function actionConvertAreaTo($attributes){
        if (!isset($attributes['ajax']) || $attributes['ajax'] != 1 || !isset($attributes['mUnitRef']) || !isset($attributes['height']) || !isset($attributes['width']) || !isset($attributes['pUnitId'])) {
            return NULL;
        }
        
        $mUnitRef = $attributes['mUnitRef'];
        $pUnitId = $attributes['pUnitId'];
        $pUnit = PricingUnitFactory::getModelById($pUnitId);
        if(!$pUnit){
            return NULL;
        }
        $pUnitRef = $pUnit->getProperty('ref');
        //ex. if it's sqft we need the pUnit to be in ft
        $pUnitBaseRef = GI_Measurement::getBaseUnitRefByUnitRef($pUnitRef);
        $height = (float) $attributes['height'];
        $width = (float) $attributes['width'];
        
        if($pUnitBaseRef != $mUnitRef){
            $height = GI_Measurement::convertValToNewLengthUnits($height, $mUnitRef, $pUnitBaseRef);
            $width = GI_Measurement::convertValToNewLengthUnits($width, $mUnitRef, $pUnitBaseRef);
        }
        
        $area = $height * $width;
        
        $returnArray = GI_Controller::getReturnArray();
        $returnArray['data'] = $area;
        /*
        
        if ($mUnitRef == 'm') {
            $val = $height * $width;
        } else {
            $heightInM = GI_Measurement::convertValToNewLengthUnits($height, $unitRef, 'm');
            $widthInM = GI_Measurement::convertValToNewLengthUnits($width, $unitRef, 'm');
            $val = $heightInM * $widthInM;
        }
        $returnArray = GI_Controller::getReturnArray();
        $returnArray['data'] = $val;
         * 
         */
        return $returnArray;
    }
    
    public function actionGetSmallestUnitCount($attributes){
        if (!isset($attributes['ajax']) || $attributes['ajax'] != 1 || !(isset($attributes['pUnitId']) && isset($attributes['pUnitRef'])) || !isset($attributes['pUnitCount'])) {
            return NULL;
        }
        
        $pUnitCount = $attributes['pUnitCount'];
        $pricingUnit = NULL;
        if(isset($attributes['pUnitId'])){
            $pricingUnit = PricingUnitFactory::getModelById($attributes['pUnitId']);
        } else {
            $pricingUnit = PricingUnitFactory::getModelByRef($attributes['pUnitRef']);
        }
        if(!$pricingUnit){
            return NULL;
        }
        /*@var $newPricingUnit AbstractPricingUnit*/
        $newPricingUnit = NULL;
        
        $newCount = GI_Measurement::getSmallestUnitCount($pUnitCount, $pricingUnit, $newPricingUnit);
        $returnArray = array(
            'count' => $newCount,
            'unitLabel' => $newPricingUnit->getTitle(),
            'plUnitLabel' => $newPricingUnit->getTitle(true),
            'unitRef' => $newPricingUnit->getAbbr(false)
        );
        return $returnArray;
    }

}
