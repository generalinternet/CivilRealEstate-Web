<?php
/**
 * Description of AbstractPricingUnitVolume
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.0
 */
abstract class AbstractPricingUnitVolume extends AbstractPricingUnit {

    public function convertQtyToThis($currentQty, AbstractPricingUnit $currentPricingUnit) {
        if (!($currentPricingUnit->getTypeModel()->getProperty('id') == $this->getTypeModel()->getProperty('id'))) {
            return NULL;
    }
        $convertedVal = GI_Measurement::convertValToNewVolumeUnits($currentQty, $currentPricingUnit->getProperty('ref'), $this->getProperty('ref'));
        return $convertedVal;
    }

}
