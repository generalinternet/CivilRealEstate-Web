<?php
/**
 * Description of AbstractContactEventFormView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    3.0.1
 */
class AbstractContactEventFormView extends GI_View {
    
    /**
     *
     * @var GI_Form 
     */
    protected $form;
    /**
     * @var AbstractContactEvent
     */
    protected $contactEvent;
    /** @var AbstractGI_Uploader */
    protected $uploader = NULL;
    protected $formTitle = '';
    protected $formBuilt = false;
    protected $viewBuilt = false;
    
    public function __construct(GI_Form $form, AbstractContactEvent $contactEvent) {
        parent::__construct();
        $this->form = $form;
        $this->contactEvent = $contactEvent;
        if($contactEvent->getProperty('id')){
            $this->formTitle = 'Edit ' . $contactEvent->getViewTitle(false);
        } else {
            $this->formTitle = 'Add ' . $contactEvent->getViewTitle(false);
        }
    }
    
    public function buildForm() {
        if (!$this->formBuilt) {
            $this->form->addHTML('<h1>' . $this->formTitle . '</h1>');
            $this->form->addHTML('<div class="columns thirds top_align">');
                $this->form->addHTML('<div class="column">');
                    $this->addTypeField();
                    $this->addUploaderField();
                $this->form->addHTML('</div>');
                $this->form->addHTML('<div class="column two_thirds">');
                    $this->addDateTimeFields();
                    $this->addTitleNotesField();
                $this->form->addHTML('</div>');
            $this->form->addHTML('</div>');
            $this->formBuilt = true;
        }
        
    }

    public function buildView($fullView = true){
        if (!$this->formBuilt) {
            $this->buildForm();
        }
        
        if ($fullView) {
            $this->addHTML($this->form->getForm());
        } else {
            $this->form->setBtnText('');
            $this->addHTML($this->form->getForm('', false));
        }
        $this->viewBuilt = true;
    }
    
    public function beforeReturningView() {
        if(!$this->viewBuilt){
            $this->buildView();
        }
    }
    
    public function setUploader(AbstractGI_Uploader $uploader){
        $this->uploader = $uploader;
        return $this;
    }
    
    /**
     * Add type dropdown menu
     */
    protected function addTypeField($overWriteSettings = array()) {
        $fieldSettings = GI_Form::overWriteSettings(array(
                    'displayName' => 'Event Type',
                    'options' => ContactEventFactory::getTypesArray(),
                    'value' => $this->contactEvent->getTypeRef(),
                    'required' => true,
                    'fieldClass' => 'toggler',
                    'formElementClass' => 'autofocus_off',
                    'hideNull' => true,
                        ), $overWriteSettings);

        if ($this->contactEvent->getProperty('id')) {
            $fieldSettings['formElementClass'] = 'autofocus_off';
        }
        $this->form->addField('type', 'dropdown', $fieldSettings);
    }

    /**
     * Add uploader
     */
    protected function addUploaderField(){
        if($this->uploader){
            $this->form->addHTML('<h3 class="content_block_title">Attached Files</h3>');
            $this->form->addHTML($this->uploader->getHTMLView());
        }
    }
    
    /**
     * Add start/end datetime field
     */
    protected function addDateTimeFields(){
        $this->form->addHTML('<div class="columns halves">');
            $this->form->addHTML('<div class="column">');
                $this->addStartDateField();
            $this->form->addHTML('</div>');
            $this->form->addHTML('<div class="column">');
                $this->addEndDateField();
            $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
        
        $this->form->addHTML('<div class="columns halves">');
            $this->form->addHTML('<div class="column">');
                $this->addStartTimeField();
            $this->form->addHTML('</div>');
            $this->form->addHTML('<div class="column">');
                $this->addEndTimeField();
            $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
    }
    
    protected function addStartDateField($overWriteSettings = array()){
        $dateObj = new DateTime();
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'Start Date',
            'placeHolder' => 'ex. ' . $dateObj->format('Y-m-d'),
            'value' => $this->contactEvent->getStartDate(),
            'fieldClass' => 'field_start_date',
        ), $overWriteSettings);
        $this->form->addField('start_date', 'date', $fieldSettings);
    }
    
    protected function addStartTimeField($overWriteSettings = array()){
        $dateObj = new DateTime();
        $stepMinute = 15;
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'Start Time',
            'placeHolder' => 'ex. ' . $dateObj->format('h:'.$stepMinute.' a'),
            'value' => $this->contactEvent->getStartTime(),
            'fieldClass' => 'field_start_time',
            'stepMinute' => $stepMinute,
        ), $overWriteSettings);
        $this->form->addField('start_time', 'time', $fieldSettings);
    }
    
    protected function addEndDateField($overWriteSettings = array()) {
        $dateObj = new DateTime();
        $this->form->addHTML('<div class="toggler_element form_element" data-group="type" data-element="'.ContactEventFactory::getMulpleDatesTypeString().'">');
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'End Date',
            'placeHolder' => 'ex. ' . $dateObj->format('Y-m-d'),
            'value' => $this->contactEvent->getEndDate(),
            'minDateFromField' => 'start_date',
            'fieldClass' => 'field_end_date',
        ), $overWriteSettings);
        
        $this->form->addField('end_date', 'date', $fieldSettings);
        $this->form->addHTML('</div>');
    }
    
    protected function addEndTimeField($overWriteSettings = array()){
        $dateObj = new DateTime();
        $stepMinute = 15;
        $this->form->addHTML('<div class="toggler_element form_element" data-group="type" data-element="'.ContactEventFactory::getMulpleTimesTypeString().'">');
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'End Time',
            'placeHolder' => 'ex. ' . $dateObj->format('h:'.$stepMinute.' a'),
            'value' => $this->contactEvent->getEndTime(),
            'fieldClass' => 'field_end_time',
            'stepMinute' => $stepMinute,
        ), $overWriteSettings);
        $this->form->addField('end_time', 'time', $fieldSettings);
        $this->form->addHTML('</div>');
    }
    
    /**
     * Add Title and Notes fields
     */
    protected function addTitleNotesField(){
        $this->form->addField('title', 'text', array(
            'displayName' => 'Title',
            'placeHolder' => 'Event Title',
            'value' => $this->contactEvent->getProperty('title'),
            'fieldClass' => 'field_title',
        ));
        $this->form->addField('notes', 'textarea', array(
            'displayName' => 'Notes',
            'placeHolder' => 'Describe the event',
            'value' => $this->contactEvent->getProperty('notes'),
        ));
    }
}
