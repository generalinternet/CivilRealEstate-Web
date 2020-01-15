<?php
/**
 * Description of AbstractContentTextCodeFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.1
 */
abstract class AbstractContentTextCodeFormView extends AbstractContentTextFormView {
    
    protected $showStyleFields = false;
    protected $showTagField = false;
    
    public function buildFormGuts() {
        AbstractContentFormView::buildFormGuts();
        $this->form->addHTML('<div class="columns fifths">');
            $this->form->addField($this->content->getFieldName('language'), 'dropdown', array(
                'displayName' => 'Language',
                'optionGroups' => CodeLangDefinitions::getCodeLanguages(),
                'formElementClass' => 'column',
                'required' => true,
                'value' => $this->content->getProperty('content_text_code.language')
            ));
            
            $startingLine = $this->content->getProperty('content_text_code.starting_line');
            if(empty($startingLine)){
                $startingLine = 1;
            }
            
            $this->form->addField($this->content->getFieldName('starting_line'), 'integer_pos', array(
                'displayName' => 'Starting Line Number',
                'value' => $startingLine,
                'required' => true,
                'formElementClass' => 'column'
            ));
            $this->form->addField($this->content->getFieldName('highlight_lines'), 'text', array(
                'displayName' => 'Lines to highlight',
                'placeHolder' => 'ex. "2, 3, 4, 6-15"',
                'value' => $this->content->getProperty('content_text_code.highlight_lines'),
                'formElementClass' => 'column'
            ));
        $this->form->addHTML('</div>');
        $this->form->addField($this->content->getFieldName('content'), 'textarea', array(
            'displayName' => 'Code',
            'placeHolder' => 'Enter code here...',
            'required' => true,
            'value' => $this->content->getProperty('content_text.content')
        ));
    }
    
}
