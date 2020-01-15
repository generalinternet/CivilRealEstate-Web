<?php
/**
 * Description of AbstractTimeIntervalFormView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    4.0.0
 */
abstract class AbstractTimeIntervalFormView extends MainWindowView {
    
    /** @var GI_Form */
    protected $form;
    /** @var AbstractTimeInterval */
    protected $timeInterval;
    protected $scheduleContacts = false;
    protected $multipleContactAssigning = false;
    
    public function __construct(GI_Form $form, AbstractTimeInterval $timeInterval = NULL) {
        parent::__construct();
        $this->form = $form;
        $this->timeInterval = $timeInterval;
        $this->addJS('framework/modules/Schedule/' . MODULE_SCHEDULE_VER . '/resources/schedule.js');
        
        $this->setWindowTitle('<span class="inline_block">Time Interval</span>');
    }
    
    /**
     * @param boolean $addWrap
     * @return \AbstractTimeIntervalFormView
     */
    public function setAddWrap($addWrap){
        $this->addWrap = $addWrap;
        if (!$this->addWrap) {
            $this->addOuterWrap = false;
        }
        return $this;
    }
    
    public function buildForm() {
        $this->buildFormBody();
        $this->buildFormFooter();
    }
    
    protected function buildFormBody($classNames = NULL) {
        $this->form->addHTML('<div class="form_body ' . $classNames . '">');
        $this->addFields();
        $this->form->addHTML('</div>');
    }
    
    protected function addFields() {
        $this->addDateSection();
        $this->addTimeRange();
        $this->addScheduleContactsField();
        $this->addRecurringOptionsField();
    }
    
    protected function addRecurringOptionsField($overWriteSettings = array()){
        $specificDate = $this->timeInterval->getSpecificDate();
        if($specificDate){
            $options = $this->timeInterval->getRecurringOptions();
            if(isset($options['single'])){
                $options['single'] = 'No <i class="primary sml_text">Just ' . GI_Time::formatDateForDisplay($specificDate) . '</i>';
            }
            $fieldSettings = GI_Form::overWriteSettings(array(
                'displayName' => 'Edit Recurring',
                'options' => $options,
                'formElementClass' => 'list_options',
                'value' => 'single',
                'fieldClass' => 'radio_toggler',
                'stayOn' => true
            ), $overWriteSettings);

            $this->form->addField('edit_what', 'radio', $fieldSettings);
        }
    }
    
    protected function addDateSection(){
        $specificDate = $this->timeInterval->getSpecificDate();
        $specificChoice = false;
        if($specificDate){
            $specificChoice = true;
        }
        if($specificChoice){
            $this->addSpecificInfoSection();
            $this->form->addHTML('<div class="hide_on_load">');
        }
        $this->addSingleOrMultiField();
        $this->addDateRange();
        $this->addWeekDaySelector();
        if($specificChoice){
            $this->form->addHTML('</div>');
        }
    }
    
    protected function addSpecificInfoSection(){
        $specificDate = $this->timeInterval->getSpecificDate();
        if($specificDate){
            $this->form->addHTML('<h3>' . GI_Time::formatDateForDisplay($specificDate) . '</h3>');
        }
    }
    
    protected function addDateRange(){
        $this->form->addHTML('<div class="flex_row no_pad form_element">')
                ->addHTML('<div class="flex_col">');
        $this->addStartDateField();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addEndDateField();
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }
    
    protected function addTimeRange(){
        $this->form->addHTML('<div class="flex_row no_pad form_element">')
                ->addHTML('<div class="flex_col">');
        $this->addAllDayField();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addStartTimeField();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addEndTimeField();
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }
    
    protected function addSingleOrMultiField($overWriteSettings = array()){        
        $value = $this->timeInterval->getProperty('single_day');
        if(!$this->timeInterval->getId()){
            $value = 1;
        }
        $fieldSettings = GI_Form::overWriteSettings(array(
            'showLabel' => false,
            'value' => $value,
            'required' => true,
            'options' => array(
                1 => 'Single Day',
                0 => 'Multi Day'
            ),
            'fieldClass' => 'radio_toggler'
        ), $overWriteSettings);
        
        $this->form->addField('single_day', 'radio', $fieldSettings);
    }
    
    protected function addStartDateField($overWriteSettings = array()){
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'Start Date',
            'value' => $this->timeInterval->getStartDate(),
            'required' => true,
            'fieldClass' => 'autofocus_off'
        ), $overWriteSettings);
        $this->form->addField('start_date', 'date', $fieldSettings);
    }

    protected function addEndDateField($overWriteSettings = array()) {
        $this->form->addHTML('<div class="radio_toggler_element form_element" data-group="single_day" data-element="0">');
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'End Date',
            'value' => $this->timeInterval->getEndDate(),
            'minDateFromField' => 'start_date'
        ), $overWriteSettings);
        
        $this->form->addField('end_date', 'date', $fieldSettings);
        $this->form->addHTML('</div>');
    }
    
    protected function addWeekDaySelector($overWriteSettings = array()){
        $this->form->addHTML('<div class="radio_toggler_element form_element" data-group="single_day" data-element="0">');
        $fieldSettings = GI_Form::overWriteSettings(array(
            'showLabel' => false,
            'value' => $this->timeInterval->getDaysArray(),
            'options' => array(
                'mon' => 'M',
                'tue' => 'T',
                'wed' => 'W',
                'thu' => 'T',
                'fri' => 'F',
                'sat' => 'S',
                'sun' => 'S',
            ),
            'labelBeforeBox' => true,
            'formElementClass' => 'block_labels center_align'
        ), $overWriteSettings);
        
        $this->form->addField('week_days', 'checkbox', $fieldSettings);
        $this->form->addHTML('</div>');
    }
    
    protected function addAllDayField($overWriteSettings = array()){
        $value = $this->timeInterval->getProperty('all_day');
        if(!$this->timeInterval->getId()){
            $value = 0;
        }
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'All Day?',
            'value' => $value,
            'fieldClass' => 'checkbox_toggler'
        ), $overWriteSettings);
        
        $this->form->addField('all_day', 'onoff', $fieldSettings);
    }
    
    protected function addStartTimeField($overWriteSettings = array()){
        $this->form->addHTML('<div class="checkbox_toggler_element form_element" data-group="all_day" data-element="undefined">');
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'Start Time',
            'value' => $this->timeInterval->getStartTime(),
            'stepMinute' => 15,
            'fieldClass' => 'autofocus_off'
        ), $overWriteSettings);
        $this->form->addField('start_time', 'time', $fieldSettings);
        $this->form->addHTML('</div>');
    }

    protected function addEndTimeField($overWriteSettings = array()) {
        $this->form->addHTML('<div class="checkbox_toggler_element form_element" data-group="all_day" data-element="undefined">');
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'End Time',
            'value' => $this->timeInterval->getEndTime(),
            'stepMinute' => 15,
            'fieldClass' => 'autofocus_off'
        ), $overWriteSettings);
        
        $this->form->addField('end_time', 'time', $fieldSettings);
        $this->form->addHTML('</div>');
    }
    
    protected function addScheduleContactsField($overWriteSettings = array(), $overWriteAutocompProps = array()) {
        if(!$this->scheduleContacts || !dbConnection::isModuleInstalled('contact')){
            return NULL;
        }
        $autocompProps = array(
            'controller' => 'contact',
            'action' => 'autocompContact',
            'type' => 'ind',
            'ajax' => 1,
//            'autocompField' => 'contact_ids',
//            'addIndType' => 'ind',
        );
        foreach ($overWriteAutocompProps as $prop => $val) {
            $autocompProps[$prop] = $val;
        }
        $autocompURL = GI_URLUtils::buildURL($autocompProps);
        $contacts = $this->timeInterval->getScheduledContacts();
        $contactIds = array();
        foreach($contacts as $contact){
            $contactIds[] = $contact->getId();
        }
        $contactIdString = implode(',', $contactIds);
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'Assign',
            'placeHolder' => 'start typing...',
            'autocompURL' => $autocompURL,
            'autocompMultiple' => $this->multipleContactAssigning,
            'value' => $contactIdString,
        ), $overWriteSettings);

        $this->form->addField('contact_ids', 'autocomplete', $fieldSettings);
    }
    
    protected function addBtns() {
        $this->form->addHTML('<span class="custom_btns">');
        $this->addSubmitBtn();
        $this->form->addHTML('</span>');
    }
    
    protected function addSubmitBtn() {
        $this->form->addHTML('<span class="submit_btn">Save</span>');
    }
    
    protected function buildFormFooter() {
        $this->addBtns();
    }
    
    protected function addViewBodyContent(){
        $this->addHTML($this->form->getForm());
    }
}
