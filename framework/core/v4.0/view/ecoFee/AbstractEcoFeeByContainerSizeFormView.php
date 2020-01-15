<?php

abstract class AbstractEcoFeeByContainerSizeFormView extends AbstractEcoFeeFormView {
    
    protected $volumeOnlyPricingUnitOptions = NULL;
    
    protected function getVolumeOnlyPricingUnitOptions() {
        if (empty($this->volumeOnlyPricingUnitOptions)) {
            $this->volumeOnlyPricingUnitOptions = PricingUnitFactory::getOptionsArray(false, true, false, 'volume');
        }
        return $this->volumeOnlyPricingUnitOptions;
    }

    protected function addRateField() {
        $this->form->addHTML('<div class="columns fifths">')
                ->addHTML('<div class="column two_fifths">');
        $this->form->addField($this->getFieldName('rate_per_unit'), 'decimal', array(
            'displayName' => 'Rate($)/C. Size',
            'value' => $this->ecoFee->getProperty('rate_per_unit'),
            'required' => true,
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column three_fifths">');
        $this->form->addField($this->getFieldName('rate_unit'), 'dropdown', array(
            'options' => $this->getVolumeOnlyPricingUnitOptions(),
            'value' => $this->ecoFee->getProperty('rate_unit'),
            'displayName' => 'C. Size Unit',
            'required' => true,
        ));
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addMinThresholdField() {
        $this->form->addHTML('<div class="columns fifths">')
                ->addHTML('<div class="column two_fifths">');
        $this->form->addField($this->getFieldName('min_qty'), 'decimal', array(
            'value' => $this->ecoFee->getProperty('min_qty'),
            'displayName' => 'Min. C. Size'
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column three_fifths">');
        $this->form->addField($this->getFieldName('min_unit'), 'dropdown', array(
            'value' => $this->ecoFee->getProperty('min_unit'),
            'displayName' => 'Min. C. Size Unit',
            'options' => $this->getVolumeOnlyPricingUnitOptions(),
        ));
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addMaxThresholdField() {
        $this->form->addHTML('<div class="columns fifths">')
                ->addHTML('<div class="column two_fifths">');
        $this->form->addField($this->getFieldName('max_qty'), 'decimal', array(
            'value' => $this->ecoFee->getProperty('max_qty'),
            'displayName' => 'Max. C. Size'
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column three_fifths">');
        $this->form->addField($this->getFieldName('max_unit'), 'dropdown', array(
            'value' => $this->ecoFee->getProperty('max_unit'),
            'displayName' => 'Max. C. Size Unit',
            'options' => $this->getVolumeOnlyPricingUnitOptions(),
        ));
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

}
