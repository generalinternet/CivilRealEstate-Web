<?php
/**
 * Description of AbstractContactEventSearchFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.1
 */
abstract class AbstractContactEventSearchFormView extends GI_SearchView {
    
    public function __construct(\GI_Form $form, $queryValues = array()) {
        $this->setBoxId('contact_event_search_box');
        parent::__construct($form, $queryValues);
    }
    
    protected function buildForm() {
        $this->openFormWrap();
        $this->addContactsField();
        $this->addUsersField();
        $this->addContentTextField();
        $this->addTypeField();
        $this->addDateFields();
        $this->closeFormWrap();
    }
    
    protected function openFormWrap() {
        $this->form->addHTML('<div class="auto_columns thirds">');
    }
    
    protected function closeFormWrap() {
        $this->form->addHTML('</div>');
    }
    protected function addContactsField() {
        $contactAutocompURL = GI_URLUtils::buildURL(array(
            'controller' => 'contact',
            'action' => 'autocompContact',
            'type' => 'org,ind',
            'ajax' => 1,
        ));
        $this->form->addField('search_contact_ids', 'autocomplete', array(
            'displayName' => 'Search by Contact(s)',
            'placeHolder' => 'start typing...',
            'autocompURL' => $contactAutocompURL,
            'value' => $this->getQueryValue('contact_ids'),
            'autocompMultiple' => true,
        ));
    }
    
    protected function addUsersField() {
        $userAutocompURL = GI_URLUtils::buildURL(array(
            'controller' => 'user',
            'action' => 'autocompUser',
            'ajax' => 1,
        ));
        $this->form->addField('search_user_ids', 'autocomplete', array(
            'displayName' => 'Search by User(s)',
            'placeHolder' => 'start typing...',
            'autocompURL' => $userAutocompURL,
            'value' => $this->getQueryValue('user_ids'),
            'autocompMultiple'=>true,
        ));
    }
    
    protected function addDateFields() {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addStartDateField();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->addEndDateField();
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }
    
    protected function addStartDateField() {
        $this->form->addField('search_start_date', 'date', array(
            'displayName' => 'Date From',
            'placeHolder' => 'start date',
            'value' => $this->getQueryValue('start_date'),
            'fieldClass' => 'field_start_date',
        ));
    }

    protected function addEndDateField() {
        $this->form->addField('search_end_date', 'date', array(
            'displayName' => 'To',
            'placeHolder' => 'end date',
            'value' => $this->getQueryValue('end_date'),
            'minDateFromField' => 'search_start_date',
            'fieldClass' => 'field_end_date',
        ));
    }
    
    protected function addContentTextField() {
        $this->form->addField('search_content_text', 'text', array(
            'displayName' => 'Search by Content',
            'placeHolder' => 'type text',
            'value' => $this->getQueryValue('content_text'),
        ));
    }
    
    protected function addTypeField($overWriteSettings = array()){
        $fieldSettings = GI_Form::overWriteSettings(array(
                'displayName' => 'Event Type',
                'options'=> ContactEventFactory::getTypesArray(),
                'value' => $this->getQueryValue('event_type'),
            ), $overWriteSettings);
        $this->form->addField('search_event_type', 'dropdown',$fieldSettings);
    }
}
