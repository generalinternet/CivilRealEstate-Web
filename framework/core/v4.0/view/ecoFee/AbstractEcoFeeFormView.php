<?php

abstract class AbstractEcoFeeFormView extends GI_FormRowView {

    protected $seqNumFieldName = 'eco_fee_seq_nums';
    protected $modelFieldPrefix = 'eco_fee';
    protected $ecoFee;
    protected $pricingUnitOptions = NULL;
    protected $byType = false;
    protected $byContainerType = false;
    
    protected $acceptableContainerTypeRefs = array(
        'bottle',
        'pail',
        'drum',
        'barrel',
        'keg',
        'item',
        'tube',
        'ecobox'
    );

    public function __construct(\GI_Form $form, AbstractEcoFee $ecoFee) {
        parent::__construct($form);
        $this->form = $form;
        $this->ecoFee = $ecoFee;
        $singleAppliesToArray = $ecoFee->getAppliesTo(true, true, true, 1);
        if (!empty($singleAppliesToArray)) {
            $singleAppliesTo = $singleAppliesToArray[0];
            if (!empty($singleAppliesTo->getProperty('inv_item_id'))) {
                $this->byType = false;
                $this->byContainerType = false;
            }  else if (!empty($singleAppliesTo->getProperty('inv_container_type_ref'))) {
                $this->byType = false;
                $this->byContainerType = true;
            } else {
                $this->byType = true;
                $this->byContainerType = false;
            }
        }
    }

    protected function getModelId() {
        return $this->ecoFee->getProperty('id');
    }

    protected function getModelTypeRef() {
        return $this->ecoFee->getTypeRef();
    }

    public function getFieldName($fieldName) {
        return $this->ecoFee->getFieldName($fieldName);
    }

    public function getFieldSuffix() {
        return $this->ecoFee->getFieldSuffix();
    }
    
    protected function getPricingUnitOptions() {
        if (empty($this->pricingUnitOptions)) {
            $this->pricingUnitOptions = PricingUnitFactory::getOptionsArray(false, true);
        }
        return $this->pricingUnitOptions;
    }

    public function buildForm() {
        $this->openFormRowWrap();
        $this->form->addHTML('<h4>'.$this->ecoFee->getTypeTitle().'</h4>');
        $this->addRequiredInfo();
        $this->addRemoveBtnWrap();
        $this->form->addHTML('<div class="form_row_fields">');
        $this->addFields();
        $this->form->addHTML('</div>');
        $this->form->addHTML('<hr class="no_marg" />');
        $this->closeFormRowWrap();
    }

    protected function addFields() {
        $this->addTypeField();
        $this->addTopRowFields();
        $this->addBottomRowFields();
    }

    protected function addTopRowFields() {
        $this->form->addHTML('<div class="columns thirds">')
                ->addHTML('<div class="column">');
        $this->addNameField();
        $this->form->addHTML('</div>')
         ->addHTML('<div class="column">');
        $this->addRateField();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->addAppliesToSelectorField();
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addBottomRowFields() {
        $this->form->addHTML('<div class="columns thirds">')
                ->addHTML('<div class="column">');
        $this->addMinThresholdField();
        $this->form->addHTML('</div>')
        ->addHTML('<div class="column">');
        $this->addMaxThresholdField();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->addAppliesToFields();
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addNameField() {
        $this->form->addField($this->getFieldName('name'), 'text', array(
            'displayName' => 'Name',
            'required' => true,
            'value' => $this->ecoFee->getProperty('name'),
        ));
    }

    protected function addTypeField() {
        $this->form->addField($this->getFieldName('type'), 'hidden', array(
            'value' => $this->ecoFee->getTypeRef(),
        ));
    }

    protected function addRateField() {
        $this->form->addHTML('<div class="columns fifths">')
                ->addHTML('<div class="column two_fifths">');
        $this->form->addField($this->getFieldName('rate_per_unit'), 'decimal', array(
            'displayName'=>'Rate($)/Unit',
            'value'=>$this->ecoFee->getProperty('rate_per_unit'),
            'required'=>true,
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column three_fifths">');
        $this->form->addField($this->getFieldName('rate_unit'), 'dropdown', array(
            'options'=>$this->getPricingUnitOptions(),
            'value'=>$this->ecoFee->getProperty('rate_unit'),
            'displayName'=>'Rate Unit',
            'required'=>true,
        ));
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }
    
    protected function addMinThresholdField() {
        $this->form->addHTML('<div class="columns fifths">')
                ->addHTML('<div class="column two_fifths">');
        $this->form->addField($this->getFieldName('min_qty'), 'decimal', array(
            'value'=>$this->ecoFee->getProperty('min_qty'),
            'displayName'=>'Min. Qty'
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column three_fifths">');
        $this->form->addField($this->getFieldName('min_unit'), 'dropdown', array(
            'value'=>$this->ecoFee->getProperty('min_unit'),
            'displayName'=>'Min. Unit',
            'options'=>$this->getPricingUnitOptions(),
        ));
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addMaxThresholdField() {
        $this->form->addHTML('<div class="columns fifths">')
                ->addHTML('<div class="column two_fifths">');
        $this->form->addField($this->getFieldName('max_qty'), 'decimal', array(
            'value' => $this->ecoFee->getProperty('max_qty'),
            'displayName' => 'Max. Qty'
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column three_fifths">');
        $this->form->addField($this->getFieldName('max_unit'), 'dropdown', array(
            'value' => $this->ecoFee->getProperty('max_unit'),
            'displayName' => 'Max. Unit',
            'options' => $this->getPricingUnitOptions(),
        ));
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addAppliesToSelectorField() {
        if (!dbConnection::isModuleInstalled('inventory')) {
            return true;
        }
        if ($this->byType) {
            $value = 'type';
        } else if ($this->byContainerType) {
            $value = 'container_type';
        } else {
            $value = 'name';
        }
        $fieldName = $this->getFieldName('applies_to_selector');
        $this->form->addField($fieldName, 'radio', array(
            'options' => array(
                'type' => 'Type',
                'container_type'=>'Container Type',
                'name' => 'Name'
            ),
            'displayName' => 'Applies To Item(s) by',
            'value' => $value,
            'stayOn' => true,
            'fieldClass' => 'radio_toggler',
            'required' => true,
        ));
    }

    protected function addAppliesToFields() {
        if (!dbConnection::isModuleInstalled('inventory')) {
            return true;
        }
        $selectorFieldName = $this->getFieldName('applies_to_selector');

        $this->form->addHTML('<div class="radio_toggler_element form_element" data-group="' . $selectorFieldName . '" data-element="type">');

        $this->form->addField($this->getFieldName('applies_to_inv_item_type'), 'select', array(
            'options' => InvItemFactory::getTypesArray(),
            'value'=>$this->ecoFee->getAppliesToInvItemTypeRefArray(),
            'displayName'=>'Applies To Item Type(s)',
            'formElementClass'=>'fake_required'

        ));
        $this->form->addHTML('</div>');

        $this->form->addHTML('<div class="radio_toggler_element form_element" data-group="' . $selectorFieldName . '" data-element="container_type">');

        $containerOptions = $this->filterContainerTypeOptions(InvContainerFactory::getTypesArray());
        $this->form->addField($this->getFieldName('applies_to_inv_container_type'), 'select', array(
            'options' => $containerOptions,
            'value' => $this->ecoFee->getAppliesToInvContainerTypeRefArray(),
            'displayName' => 'Applies To Container Type(s)',
            'formElementClass' => 'fake_required'
        ));

        $this->form->addHTML('</div>');

        $fieldName = $this->getFieldName('applies_to_inv_item_name');
        $acURL = GI_URLUtils::buildURL(array(
                    'controller' => 'inventory',
                    'action' => 'autocompInvItem',
                    'autocompField' => $fieldName,
                    'ajax' => 1
                        ), false, true);

        $this->form->addHTML('<div class="radio_toggler_element form_element" data-group="'.$selectorFieldName.'" data-element="name">');
        $this->form->addField($fieldName, 'autocomplete', array(
            'displayName' => 'Applies To Item Name(s)',
            'placeHolder' => 'SELECT',
            'autocompURL' => $acURL,
            'autocompMinLength' => 0,
            'autocompMultiple' => true,
            'value' => $this->ecoFee->getAppliesToInvItemIdString(),
            'formElementClass'=>'fake_required'
        ));
        $this->form->addHTML('</div>');
    }

    public function filterContainerTypeOptions($options) {
        $finalOptions = array();
        foreach ($options as $key => $value) {
            if (in_array($key, $this->acceptableContainerTypeRefs)) {
                $finalOptions[$key] = $value;
            }
        }
        return $finalOptions;
    }

}
