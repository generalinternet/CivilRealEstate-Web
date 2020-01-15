<?php
/**
 * Description of GI_FormRowableTaxableModel
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class GI_FormRowableTaxableModel extends GI_FormRowableModel {
    
    public function getQty() {
        return 0;
    }
    
    public function getTaxCodeName() {
        $taxCodeQBId = $this->getTaxCodeQBId();
        if (!empty($taxCodeQBId)) {
            $taxCodes = QBTaxCodeFactory::getOptionsArray();
            if (!empty($taxCodes) && isset($taxCodes[$taxCodeQBId])) {
                return $taxCodes[$taxCodeQBId];
            }
        }
        
        return NULL;
    }

    /**
     * @param Boolean $forDisplay
     * @return Float The subtotal for this bill line
     * @return String Formatted money value
     */
    public function getSubTotal($forDisplay = false, $showCurrency = false) {
        return NULL;
    }

    /**
     *
     * @return []
     */
    public function getTaxTotals($forDisplay = false, $date = NULL, $type= 'sales') {
        $taxTotals = array();
        $taxCodeQBId = $this->getTaxCodeQBId();
        $subtotal = $this->getSubTotal();
        if (!empty($subtotal) && !empty($taxCodeQBId)) {
            $taxTotals = QBTaxCodeFactory::getQBTaxTotals($taxCodeQBId, $subtotal, $date, $type);
        }
        if (!empty($taxTotals) && $forDisplay) {
            foreach ($taxTotals as $taxRateId=>$total) {
                $displayTotal = GI_StringUtils::formatMoney($total);
                $taxTotals[$taxRateId] = '$' . $displayTotal;
            }
        }
        return $taxTotals;
    }

/**
     * 
     * @param type $forDisplay
     * @param type $date
     * @param type $type
     * @return type
     */
    public function getTotal($forDisplay = false, $date = NULL, $type = 'sales') {
        $total = 0;
        $subtotal = $this->getSubTotal();
        if (!empty($subtotal)) {
            $total += $subtotal;
            $taxTotals = $this->getTaxTotals(false, $date, $type);
            if (!empty($taxTotals)) {
                foreach ($taxTotals as $taxTotalArray) {
                    $total += $taxTotalArray['amount'];
                }
            }
        }
        if ($forDisplay) {
            $total = '$' . GI_StringUtils::formatMoney($total);
        }
        return $total;
    }

}
