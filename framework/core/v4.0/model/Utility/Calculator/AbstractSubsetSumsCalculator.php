<?php
/**
 * Description of AbstractSubsetSumsCalculator
 *
 * A tool for calculating the largest possible sum of numbers in a given set, that is less than or equal to a given number, as well
 * as determining which numbers comprise the solution set. The set of numbers must in the domain of positive real numbers.
 * 
 * ex. Values = {10, 4, 3, 7, 2}    TargetSum = 11  -> Max Sum = 11, Subset = {7, 4}
 * 
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    2.0.3
 */
abstract class AbstractSubsetSumsCalculator {
    
    protected $setOfNumbers;
    protected $valuesChanged = false;
    protected $targetSum;
    protected $m = array();
    protected $removeDecimalMultiplier = 100; //allow for 2 decimal places
    
    public function __construct($setOfNumbers = array(), $targetSum = 0) {
        $this->setOfNumbers = $setOfNumbers;
        $this->targetSum = $targetSum;
    }
    
    public function setTargetSum($targetSum) {
        $this->targetSum = $targetSum;
    }
    
    public function setSetOfNumbers($setOfNumbers) {
        $this->setOfNumbers = $setOfNumbers;
        $this->valuesChanged = true;
    }
    
    public function setRemoveDecimalMultiplier($removeDecimalMultiplier = 100) {
        if ($removeDecimalMultiplier > 0 && $removeDecimalMultiplier <= 100) {
            $this->removeDecimalMultiplier = $removeDecimalMultiplier;
        }
    }

    public function calculateSums() {
        if (empty($this->setOfNumbers)) {
            return NULL;
        }
        if ($this->valuesChanged || empty($this->m)) {
            sort($this->setOfNumbers);
            if ($this->removeDecimalMultiplier == 100) {
                ini_set('memory_limit', '512M');
            } else if ($this->removeDecimalMultiplier == 10) {
                ini_set('memory_limit', '256M');
            }

            $numberOfElements = count($this->setOfNumbers);
            $divisor = $this->removeDecimalMultiplier;
            $targetSum = $this->targetSum * $this->removeDecimalMultiplier;
       //     $targetSum = round($targetSum);
            for ($i = 0; $i < $numberOfElements; $i++) {
                $this->m[$i] = array();
                for ($j = 0; $j < $targetSum + 1; $j++) {
                    if ($j == 0) {
                        $this->m[$i][$j] = 0;
                    } else {
                        $value = round($this->setOfNumbers[$i] * $divisor);
                        if ($j < $value) {
                            if ($i == 0) {
                                $this->m[$i][$j] = 0;
                            } else {
                                $this->m[$i][$j] = $this->m[$i - 1][$j];
                            }
                        } else {
                            if ($i == 0) {
                                $lastValue = 0;
                            } else {
                                $lastValue = $this->m[$i - 1][$j - $value];
                            }
                            if (($lastValue + $value) <= $targetSum) {
                                $this->m[$i][$j] = $lastValue + $value;
                            } else {
                                $this->m[$i][$j] = max(array($lastValue, $value));
                            }
                        }
                    }
                }
            }
        }

        return $this->getMaxSum($targetSum / $this->removeDecimalMultiplier);
    }

    public function getMaxSum($targetSum = NULL) {
        if (empty($targetSum)) {
            $targetSum = $this->targetSum;
        }
        $targetSum = $targetSum * $this->removeDecimalMultiplier;
     //   $targetSum = round($targetSum);
        $rowMemoryKey = $this->getMaxSumRowMemoryKey($targetSum);
        return $this->getValueFromMemory($rowMemoryKey, $targetSum);
    }
    
    protected function getMaxSumRowMemoryKey($targetSum = NULL) {
        if (empty($targetSum)) {
            $targetSum = $this->targetSum;
        }
        $numberOfElements = count($this->setOfNumbers);
        $max = 0;
        $key = 0;
        for ($n = $numberOfElements - 1; $n > -1; $n--) {
            $val = $this->getValueFromMemory($n, $targetSum);
            if ($val > $max) {
                $max = $val;
                $key = $n;
            }
        }
        return $key;
    }

    public function getSubset($maxSum = NULL) {
        if (empty($this->m)) {
            return array();
        }
        if (empty($maxSum) || $maxSum > $this->targetSum) {
            $maxSum = $this->targetSum;
        }
        $maxSum = ($maxSum * $this->removeDecimalMultiplier);
        $maxSum = round($maxSum);
        if (empty($this->m)) {
            return array();
        }
        $j = $maxSum;
        $i = $this->getMaxSumRowMemoryKey($maxSum);

        $sums = array();
        while ($j > 0) {
            if ($i < 0 || $j < 0) {
                break;
            }
            $value =  ($this->setOfNumbers[$i] * $this->removeDecimalMultiplier);
            $mValue =  $this->m[$i][$j];

            if (GI_Math::floatEquals($mValue, $value)) {
                $sums[] = $value / $this->removeDecimalMultiplier;
                $j = 0;
            } else if ($mValue < $value) {
                $i--;
            } else {
                $sums[] = $value / $this->removeDecimalMultiplier;
                $j -= ($this->setOfNumbers[$i] * $this->removeDecimalMultiplier);
                $i--;
            }
        }
        return $sums;
    }

    public function getValueFromMemory($row, $col) {
        $value = $this->m[$row][$col];
        return $value / $this->removeDecimalMultiplier;
    }
    
    
    public function printMemory() {
        for ($i= (count($this->setOfNumbers) -1); $i > -1; $i--) {
            for ($j=0;$j<(($this->targetSum+1) * $this->removeDecimalMultiplier); $j++) {
                print_r($this->getValueFromMemory($i, $j) . ' | ');
            }
            print_r('<br>');
        }
    }

    public function printCol($key) {
        for ($i = count($this->setOfNumbers) - 1; $i > -1; $i--) {
            print_r($this->getValueFromMemory($i, $key));
            print_r('<br>');
        }
    }

}