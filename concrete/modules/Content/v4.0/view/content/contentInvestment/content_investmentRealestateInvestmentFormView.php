<?php

class ContentInvestmentRealestateInvestmentFormView extends ContentInvestmentRealestateFormView{

    protected function addRealEstateDetailFormSection(){
        $this->form->addHTML('<hr>');
        
        $this->form->addHTML('<div class="auto_columns halves custom_fields">');
            $this->form->addHTML('<div class="auto_column">');
            $this->addPropertyTypeField();
            $this->form->addHTML('</div>');
            
            $this->form->addHTML('<div class="auto_column">');
                $this->addAddressField();
            $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
    }

    protected function addShareStructureFormSection(){
        return;
    }

    protected function addSalientDetailsFormSection(){
        return;
    }
}