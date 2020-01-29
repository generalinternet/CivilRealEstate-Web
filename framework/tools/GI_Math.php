<?php
/**
 * Description of GI_Math
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version 2.0.5
 */
class GI_Math {

    /**
     * Divides $dividend as evenly as possible among an array of $divisor # of elements,
     * to 2 decimal places
     * @param Float $dividend - Rounded to 2 decimal places.
     * @param Integer $divisor
     * @param Float[] $values - An array with $divisor #of values, each rounded to 2 decimal places
     * @return 
     */
    public static function divideMoneyWithoutLoss($dividend, $divisor, &$values = array()) {
        if ($divisor == 0) {
            return;
        }
        
        $valuesAreNegative = false;
        
        if($dividend < 0){
            $dividend *= -1;
            $valuesAreNegative = true;
        }
        
        self::divideMoneyWithoutLossRecursive($dividend, $divisor, $values);
        $count = count($values);
        if ($count > 0) {
            for ($i = 0; $i < $count; $i++) {
                $newValue = round($values[$i], 2);
                if($valuesAreNegative){
                    $newValue *= -1;
                }
                $values[$i] = $newValue;
            }
        }
        
        return;
    }

    protected static function divideMoneyWithoutLossRecursive($dividend, $divisor, &$values = array()) {
        if ($divisor == 0) {
            return;
        }
        $remainder = fmod($dividend, $divisor);
        $remainder = round($remainder, 2);
        if ($remainder == 0) {
            $value = $dividend / $divisor;
        } else {
            if ($remainder == $dividend) {
                $dividendCents = $dividend * 100; 
                $count = round(fmod($dividendCents, $divisor));
                $dividend = ($dividendCents - $count) / 100 ;
                $value = $dividend / $divisor;
                if ($count > 0) {
                   for ($j = 0; $j < $count; $j++) {
                        if (!isset($values[$j])) {
                            $values[$j] = 0;
                        }
                        $values[$j] += 0.01;
                    }
                }
            } else {
                $evenTotal = $dividend - $remainder;
                $value = $evenTotal / $divisor;
                self::divideMoneyWithoutLossRecursive($remainder, $divisor, $values);
            }
        }
        for ($i=0;$i<$divisor;$i++) {
            if(!isset($values[$i])){
                $values[$i] = 0;
            }
            $values[$i] += $value;
        }
        return;
    }

    public static function redistributeByProportionWithoutLoss($values, $newSum) {
        if (empty($values)) {
            return array();
        }
        $numOfValues = count($values);
        if ($newSum == 0) {
            $zeroArray = array();
            for ($k = 0; $k < $numOfValues; $k++) {
                $zeroArray[$k] = 0;
                return $zeroArray;
            }
        }
        $sum = 0;
        foreach ($values as $value) {
            $sum += $value;
        }
        $remainingProportion = 1;
        $proportions = array();

        for ($i = 0; $i < $numOfValues; $i++) {
            $value = $values[$i];
            if ($i != $numOfValues - 1) {
                $proportion = (float) ($value / $sum);
                $remainingProportion -= $proportion;
                $proportions[$i] = $proportion;
            } else {
                $proportions[$i] = $remainingProportion;
                $remainingProportion = 0;
            }
        }
        $newValues = array();
        $remainingSum = $newSum;
        for ($j=0;$j<$numOfValues;$j++) {
            $proportion = $proportions[$j];
            $newValue = (float) floor(($newSum * $proportion) * 100);
            $newValue = $newValue / 100;
            $newValues[$j] = $newValue;
            $remainingSum -= $newValue;
        }
        $remainingSum = round($remainingSum, 2);
        $index = 0;
        while ($remainingSum > 0) {
            $newValues[$index] += 0.01;
            $remainingSum -= 0.01;
            $index++;
            if ($index == $numOfValues) {
                $index = 0;
            }
        }
        return $newValues;
    }

    public static function calculateTaxBalancingQuantities($totalQty, $perUnitAmount, $taxRate) {
        $perUnitTaxAmount = number_format(($perUnitAmount * $taxRate), 4);
        $lastTwoDigits = substr($perUnitTaxAmount, -2);

        $normalAmount = round($perUnitAmount * $taxRate, 2);
        $upAmount = 0;
        $downAmount = 0;
        $roundUpQty = 0;
        $roundDownQty = 0;
        if (empty($lastTwoDigits) || $totalQty == 1) {
            $normalQty = $totalQty;
        } else {
            $normalQty = 0;
            $roundUpRatio = round(($lastTwoDigits / 100), 2);
            $roundUpQty = round($totalQty * $roundUpRatio);
            $roundDownQty = $totalQty - $roundUpQty;
            $upAmount = GI_Math::round_up(($perUnitAmount * $taxRate), 3);
            $downAmount = GI_Math::round_down(($perUnitAmount * $taxRate), 3);
        }
        return array(
            'up_qty' => $roundUpQty,
            'up_amount' => $upAmount,
            'down_qty' => $roundDownQty,
            'down_amount' => $downAmount,
            'normal_qty' => $normalQty,
            'normal_amount' => $normalAmount
        );
    }

    public static function preciseRound($number, $precision = 2){
        $eNumber = (float) ($number . 'e' . $precision);
        $roundedENumber = round($eNumber);
        $finalNumber = (float) ($roundedENumber . 'e-' . $precision);
        return $finalNumber;
    }
    
    public static function round_up($number, $precision = 3) {
        $fig = (int) str_pad('1', $precision, '0');
        return (ceil($number * $fig) / $fig);
    }

    public static function round_down($number, $precision = 3) {
        $fig = (int) str_pad('1', $precision, '0');
        return (floor($number * $fig) / $fig);
    }

    public static function floatEquals($floatA, $floatB, $epsilon = 0.000001) {
        if (abs($floatA - $floatB) < $epsilon) {
            return true;
        }
        return false;
    }
    
    /**
     * @param float $float
     * @return float
     */
    public static function defaultRound($float){
        $precision = ProjectConfig::getDefaultRoundPrecision();
        $roundedFloat = round($float, $precision);
        return (float) $roundedFloat;
    }

    public static function mergeAndAddArrays($array1, $array2) {
        $sums = array();
        foreach (array_keys($array1 + $array2) as $key) {
            $sums[$key] = (isset($array1[$key]) ? $array1[$key] : 0) + (isset($array2[$key]) ? $array2[$key] : 0);
        }
        return $sums;
    }
    
    public static function defaultStockRound($float) {
        return static::round_down($float, ProjectConfig::getStockUnitPrecision());
    }

}
