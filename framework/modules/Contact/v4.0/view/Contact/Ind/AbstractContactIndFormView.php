<?php
/**
 * Description of AbstractContactIndFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.4
 */
abstract class AbstractContactIndFormView extends AbstractContactFormView {
    
    /**
     * @var ContactInd
     */
    protected $contact;

    protected function addFormFields(){
        if(!$this->ajax){
            $this->addContactInfoForms();

            $this->addUploaders();
            
            $this->addTagForm();
            
        }
        $this->addSubmitBtn();
    }

    public function buildFormBody() {
        if ($this->ajax) {
            $this->buildAjaxForm();
            return;
        }
        $this->form->addHTML('<div class="columns halves">');
        $this->form->addHTML('<div class="column">');
        $this->addNameFields();
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addDefaultCurrencyField();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->addSubCategoryTagField();
        $this->form->addHTML('</div>')
                ->addHTML('</div>');

        $this->form->addHTML('</div>');
        $this->form->addHTML('<div class="column">');
        $this->addUserFieldsSection();
        $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');

        $this->form->addHTML('<div id="contact_category_form_wrap">');
        $this->buildContactCatForm();
        $this->form->addHTML('</div>');

        $this->form->addHTML('<br />');
    }

    protected function buildAjaxForm() {
        $this->addNameFields();
        $this->addUserFieldsSection();
        $this->addDefaultCurrencyField();
        $this->form->addHTML('<hr>');
        $this->form->addHTML('<div id="contact_category_form_wrap">');
        $this->buildContactCatForm();
        $this->form->addHTML('</div>');
        $this->form->addHTML('<hr>');
    }
    
    protected function addNameFields(){
        $firstName = $this->contact->getProperty('contact_ind.first_name');
        $lastName = $this->contact->getProperty('contact_ind.last_name');
        if(empty($firstName) && !empty($this->startTitle)){
            $spacePos = strrpos($this->startTitle, ' ');
            $firstName = $this->startTitle;
            if($spacePos !== false){
                $firstName = substr($this->startTitle, 0, $spacePos);
                $lastName = substr($this->startTitle, $spacePos + 1);
            }
        }
        $this->form->addHTML('<div class="columns fifths">')
                ->addHTML('<div class="column two_fifths">');
        $this->form->addField('first_name', 'text', array(
            'displayName' => Lang::getString('first_name'),
            'placeHolder' => Lang::getString('first_name'),
            'value' => $firstName,
            'required' => true
        ));
        $this->form->addHTML('</div><div class="column two_fifths">');
        $this->form->addField('last_name', 'text', array(
            'displayName' => Lang::getString('last_name'),
            'placeHolder' => Lang::getString('last_name'),
            'value' => $lastName,
        ));
        $this->form->addHTML('</div>');
        $this->form->addHTML('<div class="column">');
            $this->addColourField();
        $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
        
    }

}
