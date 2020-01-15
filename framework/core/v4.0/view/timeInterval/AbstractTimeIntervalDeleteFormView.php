<?php
/**
 * Description of AbstractTimeIntervalDeleteFormView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    2.0.0
 */
abstract class AbstractTimeIntervalDeleteFormView extends AbstractDeleteFormView { 
    
    /** @var AbstractTimeInterval */
    protected $model;
    
    protected function addFields(){
        parent::addFields();
        $specificDate = $this->model->getSpecificDate();
        if($specificDate){
            $options = $this->model->getRecurringOptions();
            $this->form->addField('delete_what', 'radio', array(
                'displayName' => 'What are you trying to delete?',
                'options' => $options,
                'formElementClass' => 'list_options',
                'value' => 'single',
                'fieldClass' => 'radio_toggler',
                'stayOn' => true
            ));
        }
    }
    
    protected function addMessage() {
        $this->form->addHTML('<p class="radio_toggler_element" data-group="delete_what" data-element="all">' . $this->getMessage() . '</p>');
        $specificDate = $this->model->getSpecificDate();
        if($specificDate){
            $this->form->addHTML('<p class="radio_toggler_element" data-group="delete_what" data-element="single">Are you sure you want to delete <b>' . GI_Time::formatDateForDisplay($specificDate) . '</b>? This cannot be undone.</p>');
            $this->form->addHTML('<p class="radio_toggler_element" data-group="delete_what" data-element="future">Are you sure you want to delete all future events from <b>' . GI_Time::formatFromDateToDate($specificDate, $this->model->getEndDate()) . '</b>? This cannot be undone.</p>');
        }
    }
    
}
