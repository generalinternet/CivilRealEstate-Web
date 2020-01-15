<?php

class StaticConverterView extends GI_View {
    
    /** @var GI_Form */
    protected $form = NULL;
    protected $lengthUnits = array();
    protected $volumeUnits = array();

    public function __construct(GI_Form $form = NULL) {
        $this->addJS('framework/core/' . FRMWK_CORE_VER. '/resources/js/converter.js');
        parent::__construct(); 
        $this->form = $form;
        $this->form = new GI_Form('convert_form');
        $this->lengthUnits = GI_Measurement::getLengthUnits();
        $this->volumeUnits = GI_Measurement::getVolumeUnits();
        $this->buildForm();
        $this->addSiteTitle('Convert');
    }
    
    public function buildForm(){
        $this->form->addHTML('<div class="columns halves">');
            $this->form->addHTML('<div class="column">');
                $this->form->addHTML('<div class="blend_form_elements">');
                    $this->form->addField('from','decimal',array(
                        'fieldClass' => 'convert_from_val'
                    ));
                    $this->addUnitField('from_unit', 'convert_from_unit');
                $this->form->addHTML('</div>');
            $this->form->addHTML('</div>');
            $this->form->addHTML('<div class="column blend_form_elements">');
                $this->form->addHTML('<div class="blend_form_elements">');
                    $this->form->addField('to','decimal',array(
                        'fieldClass' => 'convert_to_val'
                    ));
                    $this->addUnitField('to_unit', 'convert_to_unit');
                $this->form->addHTML('</div>');
            $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
    }
    
    protected function addUnitField($fieldName, $fieldClass = ''){
        $optionGroups = array();
        $optionGroups['Length'] = $this->lengthUnits;
        $optionGroups['Volume'] = $this->volumeUnits;
        $this->form->addField($fieldName, 'dropdown', array(
            'optionGroups' => $optionGroups,
            'hideNull' => true,
            'fieldClass' => $fieldClass
        ));
    }
    
    protected function buildView(){
        $this->openViewWrap();
        $this->addMainTitle('Converter');
        $this->addHTML($this->form->getForm());
        $this->closeViewWrap();
    }
    
    protected function openViewWrap(){
        $this->addHTML('<div class="content_padding">');
        return $this;
    }
    
    protected function closeViewWrap(){
        $this->addHTML('</div>');
        return $this;
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}
