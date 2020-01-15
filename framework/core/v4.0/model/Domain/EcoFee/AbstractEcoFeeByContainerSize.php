<?php

abstract class AbstractEcoFeeByContainerSize extends AbstractEcoFee {

    public function getFormView(GI_Form $form) {
        return new EcoFeeByContainerSizeFormView($form, $this);
    }

    public function getRateString() {
        $rateUnit = PricingUnitFactory::getModelById($this->getProperty('rate_unit'));
        if (empty($rateUnit)) {
            return '';
        }
        $rate = $this->getProperty('rate_per_unit');
        $rateUnitTitle = $rateUnit->getProperty('title');
        return '$' . $rate . '/' . $rateUnitTitle . ' of Container Size';
    }

    public function getMinimumThresholdString() {
        $minUnit = PricingUnitFactory::getModelById($this->getProperty('min_unit'));
        if (empty($minUnit)) {
            return '--';
        }
        $qty = $this->getProperty('min_qty');
        return $qty . ' ' . $minUnit->getProperty('title') . ' Container';
    }

    public function getMaximumThresholdString() {
        $maxUnit = PricingUnitFactory::getModelById($this->getProperty('max_unit'));
        if (empty($maxUnit)) {
            return '--';
        }
        $qty = $this->getProperty('max_qty');
        return $qty . ' ' . $maxUnit->getProperty('title') . ' Container';
    }

}
