<?php
/**
 * Description of AbstractContactOrgFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.7
 */
abstract class AbstractContactOrgFormView extends AbstractContactFormView {
    
    /**
     * @var ContactOrg
     */
    protected $contact;
    
    public function buildFormBody() {
        if($this->ajax){
            $this->buildAjaxForm();
            return;
        }
//        $this->form->addHTML('<div class="columns thirds">')
//                ->addHTML('<div class="column">');
//        $this->addNameField();
//        $this->form->addHTML('</div>')
//                ->addHTML('<div class="column">');
//        $this->addDoingBusAsField();
//        $this->form->addHTML('</div>')
//                ->addHTML('<div class="column">');
//        $this->form->addHTML('<div class="columns thirds">')
//                ->addHTML('<div class="column">');
//        $this->addColourField();
//        $this->form->addHTML('</div>')
//                ->addHTML('<div class="column two_thirds">');
//        $this->addDefaultCurrencyField();
//        $this->form->addHTML('</div>')
//                ->addHTML('</div>');
//                
//        $this->form->addHTML('</div>')
//                ->addHTML('</div>');
        
        
        
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
                $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addNameField();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->addDoingBusAsField();
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<div class="columns fifths">')
                ->addHTML('<div class="column">');
        $this->addColourField();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column two_fifths">');
        $this->addDefaultCurrencyField();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column two_fifths">');     
        $this->addSubCategoryTagField();
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
        
        
        $this->form->addHTML('<div id="contact_category_form_wrap">');
            $this->buildContactCatForm();
        $this->form->addHTML('</div>');
       
        $this->form->addHTML('<br />');
        $this->form->addHTML('<br />');
    }
    
    protected function buildAjaxForm(){
        $this->addNameFields();
        $this->addDefaultCurrencyField();
        $this->form->addHTML('<hr/>');
        $this->form->addHTML('<div id="contact_category_form_wrap">');
        $this->buildContactCatForm();
        $this->form->addHTML('</div>');
        $this->form->addHTML('<hr>');
    }

    protected function addNameField() {
        $title = $this->contact->getRealName();
        if (empty($title)) {
            $title = $this->startTitle;
        }

        $this->form->addField('title', 'text', array(
            'displayName' => 'Name',
            'placeHolder' => '',
            'value' => $title,
            'required' => true
        ));
    }

    protected function addDoingBusAsField() {
        $this->form->addField('doing_bus_as', 'text', array(
            'displayName' => 'Doing Business As',
            'placeHolder' => '',
            'value' => $this->contact->getProperty('contact_org.doing_bus_as'),
        ));
    }

    protected function addNameFields() {
        $this->form->addHTML('<div class="columns fifths">')
                ->addHTML('<div class="column four_fifths">');
        $this->addNameField();
        $this->addDoingBusAsField();
        $this->form->addHTML('</div>');
        $this->form->addHTML('<div class="column">');
        $this->addColourField();
        $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
    }



    protected function addFormFields() {
        if (!$this->ajax) {
            $this->addContactInfoForms();

            $this->addUploaders();

            $this->addTagForm();
        }
        $this->addSubmitBtn();
    }
}
