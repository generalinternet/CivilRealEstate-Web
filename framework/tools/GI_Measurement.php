<?php
/**
 * Description of GI_Measurement
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.1
 */
class GI_Measurement {

    protected static $lengthUnits = array(
        'metric' => array(
            'mm' => 'Millimetres',
            'cm' => 'Centimetres',
            'm' => 'Metres',
            'km' => 'Kilometres',
        ),
        'imperial' => array(
            'in' => 'Inches',
            'ft' => 'Feet',
            'yd' => 'Yards',
            'mi' => 'Miles'
        )
    );
    
    protected static $squareUnitRefs = array(
        'mm' => 'sqmm',
        'cm' => 'sqcm',
        'm' => 'sqm',
        'km' => 'sqkm',
        'in' => 'sqin',
        'ft' => 'sqft',
        'yd' => 'sqy',
        'mi' => 'sqmi'
    );
    
    protected static $volumeUnits = array(
        'metric' => array(
            'l'=>'Litres',
            'ml'=>'Millilitres'
        ),
        'imperial' => array(
            'gl' => 'Gallon',
            'qt' => 'Quarts',
            'pt'=>'Pints',
            'floz'=>'Fluid Ounces'
        ),
    );
    
    protected static $breakdownUnits = array(
        'cpc' => array(
            'unit' => 'pc',
            'factor' => 100
        ),
        'cft' => array(
            'unit' => 'ft',
            'factor' => 100
        ),
        'mpc' => array(
            'unit' => 'pc',
            'factor' => 1000
        ),
        'mft' => array(
            'unit' => 'ft',
            'factor' => 1000
        ),
        'msqft' => array(
            'unit' => 'sqft',
            'factor' => 1000
        ),
        'mcf' => array(
            'unit' => 'mcf',
            'factor' => 1000
        )
    );

    protected function __construct() {
        
    }

    protected function __clone() {
        
    }

    public static function getLengthUnits() {
        return array_merge(static::$lengthUnits['metric'], static::$lengthUnits['imperial']);
    }
    
    public static function getVolumeUnits() {
        return array_merge(static::$volumeUnits['metric'], static::$volumeUnits['imperial']);
    }
    
    public static function getBaseUnitRefByUnitRef($unitRef){
        if(isset(static::$squareUnitRefs[$unitRef])){
            return $unitRef;
        } elseif(in_array($unitRef, static::$squareUnitRefs)){
            return array_search($unitRef, static::$squareUnitRefs);
        }
        return NULL;
    }

    public static function getLengthUnitByRef($unitRef) {
        $units = static::getLengthUnits();
        if (isset($units[$unitRef])) {
            return $units[$unitRef];
        }
        return NULL;
    }

    public static function getVolumeUnitByRef($unitRef) {
        $units = static::getVolumeUnits();
        if (isset($units[$unitRef])) {
            return $units[$unitRef];
        }
        return NULL;
    }
    
    public static function convertValToNewLengthUnits($val, $currentUnitsRef, $targetUnitsRef) {
        $valInBaseUnits = static::getValInBaseUnits($val, $currentUnitsRef);
        if (in_array($currentUnitsRef, array_keys(static::$lengthUnits['metric']))) {
            $currentUnitsType = 'metric';
        } else {
            $currentUnitsType = 'imperial';
        }
        if (in_array($targetUnitsRef, array_keys(static::$lengthUnits['metric']))) {
            $targetUnitsType = 'metric';
        } else {
            $targetUnitsType = 'imperial';
        }
        if ($currentUnitsType === 'metric' && $targetUnitsType === 'imperial') {
            $newVal = static::getInchesFromMM($valInBaseUnits);
            return static::convertValToNewLengthUnit($newVal, 'in', $targetUnitsRef);
        } else if ($currentUnitsType === 'imperial' && $targetUnitsType === 'metric') {
            $newVal = static::getMMFromInches($valInBaseUnits);
            return static::convertValToNewLengthUnit($newVal, 'mm', $targetUnitsRef);
        } else {
            if ($targetUnitsType === 'metric') {
                return static::convertValToNewLengthUnit($valInBaseUnits, 'mm', $targetUnitsRef);
            } else {
                return static::convertValToNewLengthUnit($valInBaseUnits, 'in', $targetUnitsRef);
            }
        }
        return NULL;
    }

    public static function convertValToNewVolumeUnits($val, $currentUnitsRef, $targetUnitsRef) {
        $valInBaseUnits = static::getValInBaseUnits($val, $currentUnitsRef);
        if (in_array($currentUnitsRef, array_keys(static::$volumeUnits['metric']))) {
            $currentUnitsType = 'metric';
        } else {
            $currentUnitsType = 'imperial';
        }
        if (in_array($targetUnitsRef, array_keys(static::$volumeUnits['metric']))) {
            $targetUnitsType = 'metric';
        } else {
            $targetUnitsType = 'imperial';
        }
        if ($currentUnitsType === 'metric' && $targetUnitsType === 'imperial') {
            $newVal = static::getFlOzFromML($valInBaseUnits);
            return static::convertValToNewVolumeUnit($newVal, 'floz', $targetUnitsRef);
        } else if ($currentUnitsType === 'imperial' && $targetUnitsType === 'metric') {
            $newVal = static::getMLFromFLOz($valInBaseUnits);
            return static::convertValToNewVolumeUnit($newVal, 'ml', $targetUnitsRef);
        } else {
            if ($targetUnitsType === 'metric') {
                return static::convertValToNewVolumeUnit($valInBaseUnits, 'ml', $targetUnitsRef);
            } else {
                return static::convertValToNewVolumeUnit($valInBaseUnits, 'floz', $targetUnitsRef);
            }
        }
        return NULL;
    }

    protected static function getValInBaseUnits($val, $unitRef) {
        switch ($unitRef) {
            case 'mm':
                return $val;
            case 'cm':
                return static::getValInBaseUnits(($val * 10), 'mm');
            case 'm':
                 return static::getValInBaseUnits(($val * 100) , 'cm');
            case 'km':
                return static::getValInBaseUnits(($val * 1000), 'm');
            case 'in':
                 return $val;
            case 'ft':
                 return static::getValInBaseUnits(($val * 12), 'in');
            case 'yd':
                 return static::getValInBaseUnits(($val * 3), 'ft');
            case 'mi':
                 return static::getValInBaseUnits(($val * 1760), 'yd');
            case 'ml':
                return $val;
            case 'l':
                return static::getValInBaseUnits(($val * 1000), 'ml');
            case 'floz':
                return $val;
            case 'pt':
                return static::getValInBaseUnits(($val * 16), 'floz');
            case 'qt':
                return static::getValInBaseUnits(($val * 2), 'pt');
            case 'gl':
                return static::getValInBaseUnits(($val * 4), 'qt');
        }
    }
    
    protected static function getInchesFromMM($val) {
        return $val / 25.4;
    }

    protected static function getMMFromInches($val) {
        return $val * 25.4;
    }
    
    protected static function getFlOzFromML($val) {
        return $val / 29.5735;
    }
    
    protected static function getMLFromFLOz($val) {
        return $val * 29.5735;
    }

    protected static function convertValToNewLengthUnit($val, $unitRef, $targetUnitRef) {
        if ($unitRef === $targetUnitRef) {
            return $val;
        }
        switch ($unitRef) {
            case 'mm':
                return static::convertValToNewLengthUnit($val / 10, 'cm', $targetUnitRef);
            case 'cm':
                return static::convertValToNewLengthUnit($val / 100, 'm', $targetUnitRef);
            case 'm':
                return static::convertValToNewLengthUnit($val / 1000, 'km', $targetUnitRef);
            case 'km':
                return $val;
            case 'in':
                return static::convertValToNewLengthUnit($val / 12, 'ft', $targetUnitRef);
            case 'ft':
                return static::convertValToNewLengthUnit($val / 3, 'yd', $targetUnitRef);
            case 'yd':
                return static::convertValToNewLengthUnit($val / 1760, 'mi', $targetUnitRef);
            case 'mi':
                return $val;
        }
    }

    protected static function convertValToNewVolumeUnit($val, $unitRef, $targetUnitRef) {
        if ($unitRef === $targetUnitRef) {
            return $val;
        }
        switch ($unitRef) {
            case 'ml':
                return static::convertValToNewVolumeUnit($val / 1000, 'l', $targetUnitRef);
            case 'l':
                return $val;
            case 'floz':
                return static::convertValToNewVolumeUnit($val / 16, 'pt', $targetUnitRef);
            case 'pt':
                return static::convertValToNewVolumeUnit($val / 2, 'qt', $targetUnitRef);
            case 'qt':
                return static::convertValToNewVolumeUnit($val / 4, 'gl', $targetUnitRef);
            case 'gl':
                return $val;
        }
    }

    public static function getSquareUnitRef($unitRef) {
        if (isset(static::$squareUnitRefs[$unitRef])) {
            return static::$squareUnitRefs[$unitRef];
        }
        return NULL;
    }

    public static function isQtyLessThan($qty, AbstractPricingUnit $pricingUnit, $compareQty, AbstractPricingUnit $comparePricingUnit, $compareForEquality = false) {
        $convertedQty = $comparePricingUnit->convertQtyToThis($qty, $pricingUnit);
        if (empty($convertedQty)) {
            return NULL;
        }
        if ($convertedQty < $compareQty) {
            return true;
        }
        if ($compareForEquality && GI_Math::floatEquals($convertedQty, $compareQty)) {
            return true;
        }
        return false;
    }

    public static function isQtyGreaterThan($qty, AbstractPricingUnit $pricingUnit, $compareQty, AbstractPricingUnit $comparePricingUnit, $compareForEquality = false) {
        $convertedQty = $comparePricingUnit->convertQtyToThis($qty, $pricingUnit);
        if (empty($convertedQty)) {
            return NULL;
        }
        if ($convertedQty > $compareQty) {
            return true;
        }
        if ($compareForEquality && GI_Math::floatEquals($convertedQty, $compareQty)) {
            return true;
        }
        return false;
    }

    public static function isQtyEqualTo($qty, AbstractPricingUnit $pricingUnit, $compareQty, AbstractPricingUnit $comparePricingUnit) {
        $convertedQty = $comparePricingUnit->convertQtyToThis($qty, $pricingUnit);
        if (empty($convertedQty)) {
            return NULL;
        }
        if (GI_Math::floatEquals($convertedQty, $compareQty)) {
            return true;
        }
        return false;
    }
    
    public static function getBreakdownData($ref){
        if(isset(static::$breakdownUnits[$ref])){
            return static::$breakdownUnits[$ref];
        }
        return NULL;
    }
    
    /**
     * @param float $qty
     * @param AbstractPricingUnit $pricingUnit
     * @param AbstractPricingUnit $newPricingUnit
     * @return float
     */
    public static function getSmallestUnitCount($qty, AbstractPricingUnit $pricingUnit, AbstractPricingUnit &$newPricingUnit = NULL){
        $ref = $pricingUnit->getAbbr(false);
        $finalCount = $qty;
        $breakdownData = static::getBreakdownData($ref);
        $newRef = $ref;
        $newPricingUnit = $pricingUnit;
        if($breakdownData){
            $newRef = $breakdownData['unit'];
            $newPricingUnit = PricingUnitFactory::getModelByRef($newRef);
            $factor = (float) $breakdownData['factor'];
            $finalCount = (float) $qty * $factor;
        }
        
        return (float) $finalCount;
    }

}
