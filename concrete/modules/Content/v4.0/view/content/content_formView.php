<?php

class ContentFormView extends AbstractContentFormView{
    public function addTitleField($overWriteSettings = array()){
        if (isset($overWriteSettings['fieldClass'])) {
            $fieldClass = $overWriteSettings['fieldClass'];
        } else {
            $fieldClass = '';
        }
        
        $overWriteSettings['fieldClass'] = $fieldClass.' sanitize_ref';
        parent::addTitleField($overWriteSettings);
    }
    
    public function addRefField($overWriteSettings = array()){
        if (isset($overWriteSettings['fieldClass'])) {
            $fieldClass = $overWriteSettings['fieldClass'];
        } else {
            $fieldClass = '';
        }
        
        $overWriteSettings['fieldClass'] = $fieldClass.' target_ref';
        parent::addRefField($overWriteSettings);
    }
}
