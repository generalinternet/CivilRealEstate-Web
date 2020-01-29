<?php
/**
 * Description of AbstractSuspensionFormView
 *
 * @author General Internet
 * @copyright  2020 General Internet
 * @version    4.0.0
 */

abstract class AbstractSuspensionFormView extends MainWindowView {

    protected $suspension;
    protected $form;

    protected $formBuilt = false;

    public function __construct(GI_Form $form, AbstractSuspension $suspension) {
        parent::__construct();
        $this->form = $form;
        $this->suspension = $suspension;
        if (empty($suspension->getId())) {
            $title = 'Add ';
        } else {
            $title = 'Edit ';
        }
        $title .= ' Suspension';
        $this->setWindowTitle($title);
    }

    protected function addViewBodyContent() {
        $this->buildForm();
        $this->addHTML($this->form->getForm(''));
    }

    public function buildForm() {
        if (!$this->formBuilt) {
            $this->buildFormHeader();
            $this->buildFormBody();
            $this->buildFormFooter();
            $this->formBuilt = true;
        }
    }

    protected function buildFormHeader() {
        
    }

    protected function buildFormBody() {
        $this->addTypeField();
        $this->addStartDateField();
        $this->addEndDateField();
        $this->addNotesField();
    }
    
    protected function addTypeField() {
        $options = SuspensionFactory::getTypesArray();
        $this->form->addField('suspenson_type_ref', 'dropdown', array(
            'options'=>$options,
            'value'=>$this->suspension->getTypeRef(),
            'hideNull'=>true,
            'displayName'=>'Suspension Type'
        ));
    }
    
    protected function addStartDateField() {
        $value = $this->suspension->getProperty('start_date_time');
        if (empty($value)) {
            $value = GI_Time::getDateTime();
        }
        $this->form->addField('start_date_time', 'datetime', array(
            'displayName' => 'Start',
            'value' => $value,
            'required' => true,
            'fieldClass'=>'autofocus_off'
        ));
    }

    protected function addEndDateField() {
        $this->form->addField('end_date_time', 'datetime', array(
            'displayName' => 'End',
            'value' => $this->suspension->getProperty('end_date_time'),
            'required' => false,
            'fieldClass'=>'autofocus_off'
        ));
    }
    
    protected function addNotesField() {
        $this->form->addField('notes', 'textarea', array(
            'displayName'=>'Reason for Suspension',
            'value'=>$this->suspension->getProperty('notes'),
            'required'=>true,
        ));
    }

    protected function buildFormFooter() {
        $this->addButtons();
    }

    protected function addButtons() {
        $this->form->addHTML('<div class="center_btns wrap_btns">');
        $this->addSubmitBtn();
        $this->addCancelBtn();
        $this->form->addHTML('</div>');
    }

    public function addSubmitBtn() {
        $this->form->addHTML('<span class="submit_btn">Save</span>');
    }

    public function addCancelBtn() {
        $this->form->addHTML('<span class="other_btn gray">Cancel</span>');
    }

}
