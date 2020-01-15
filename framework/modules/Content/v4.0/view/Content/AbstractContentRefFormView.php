<?php
/**
 * Description of AbstractContentRefFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.1
 */
abstract class AbstractContentRefFormView extends AbstractContentFormView {
    
    public function addFormHeader(){
        
    }
    
    public function buildFormGuts() {  
        $this->addHiddenTypeRefField();   
        $this->addRefContentIdField();
        $refContent = $this->content->getReferencedContent();
        if($refContent){
            $refView = $refContent->getView();
            $refView->setReferenceOnly(true);
            $refView->buildView();
            $this->form->addHTML($refView->getHTMLView());
        }
    }
    
    public function addRefContentIdField($overWriteSettings = array()){
        $fieldSettings = GI_Form::overWriteSettings(array(
            'value' => $this->content->getProperty('content_ref.ref_content_id'),
        ), $overWriteSettings);
        $fieldType = 'hidden';
        if(isset($overWriteSettings['fieldType'])){
            $fieldType = $overWriteSettings['fieldType'];
        }
        $this->form->addField($this->content->getFieldName('ref_content_id'), $fieldType, $fieldSettings);
    }
    
}
