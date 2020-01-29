<?php

abstract class AbstractGenericPasswordRuleView extends GI_View{
    
    protected $fieldName = '';
    protected $confFieldName = '';
    protected $minLength = NULL;
    protected $showCannotBeSame = false;
    protected $forceUpper = false;
    protected $forceLower = false;
    protected $forceSymbol = false;
    protected $forceNum = false;
    protected $noWhiteSpace = false;
    /** GI_Form **/
    protected $form = NULL;
    protected $doNotValidate = false;
    
    public function __construct(GI_Form $form = NULL) {
        parent::__construct();
        $this->form = $form;
    }
            
    function getFieldName() {
        return $this->fieldName;
    }

    function getConfFieldName() {
        return $this->confFieldName;
    }

    function getMinLength() {
        return $this->minLength;
    }

    function getShowCannotBeSame() {
        return $this->showCannotBeSame;
    }

    function getForceUpper() {
        return $this->forceUpper;
    }

    function getForceLower() {
        return $this->forceLower;
    }

    function getForceSymbol() {
        return $this->forceSymbol;
    }

    function getForceNum() {
        return $this->forceNum;
    }

    function getNoWhiteSpace() {
        return $this->noWhiteSpace;
    }
    
    function getDoNotValidate(){
        return $this->doNotValidate;
    }

    function setFieldName($fieldName) {
        $this->fieldName = $fieldName;
        return $this;
    }

    function setConfFieldName($confFieldName) {
        $this->confFieldName = $confFieldName;
        return $this;
    }

    function setMinLength($minLength) {
        $this->minLength = $minLength;
        return $this;
    }

    function setShowCannotBeSame($showCannotBeSame) {
        $this->showCannotBeSame = $showCannotBeSame;
        return $this;
    }

    function setForceUpper($forceUpper) {
        $this->forceUpper = $forceUpper;
        return $this;
    }

    function setForceLower($forceLower) {
        $this->forceLower = $forceLower;
        return $this;
    }

    function setForceSymbol($forceSymbol) {
        $this->forceSymbol = $forceSymbol;
        return $this;
    }

    function setForceNum($forceNum) {
        $this->forceNum = $forceNum;
        return $this;
    }

    function setNoWhiteSpace($noWhiteSpace) {
        $this->noWhiteSpace = $noWhiteSpace;
        return $this;
    }
    
    function setDoNotValidate($doNotValidate){
        $this->doNotValidate = $doNotValidate;
        return $this;
    }
    
    function validatePassword(&$reason = '', &$confReason = ''){
        if($this->getDoNotValidate()){
            return;
        }
        $fieldName = $this->getFieldName();
        $password = filter_input(INPUT_POST, $fieldName);
        
        $badPass = false;
        $badConfPass = false;
        $minLength = $this->getMinLength();
        if(strlen($password) < $minLength){
            if(!empty($reason)){
                $reason .= '<br/>';
            }
            $reason .= 'Must be at least ' . $minLength . ' characters.';
            $badPass = true;
        }
        
        $forceUpper = $this->getForceUpper();
        if($forceUpper && !GI_StringUtils::stringContainsUppercase($password)){
            if(!empty($reason)){
                $reason .= '<br/>';
            }
            $reason .= 'Must contain at least 1 uppercase letter.';
            $badPass = true;
        }
        
        $forceLower = $this->getForceLower();
        if($forceLower && !GI_StringUtils::stringContainsLowercase($password)){
            if(!empty($reason)){
                $reason .= '<br/>';
            }
            $reason .= 'Must contain at least 1 lowercase letter.';
            $badPass = true;
        }
        
        $forceSymbol = $this->getForceSymbol();
        if($forceSymbol && !GI_StringUtils::stringContainsSymbol($password)){
            if(!empty($reason)){
                $reason .= '<br/>';
            }
            $reason .= 'Must contain at least 1 symbol. (ex. #,@,!,?)';
            $badPass = true;
        }
        
        $forceNum = $this->getForceNum();
        if($forceNum && !GI_StringUtils::stringContainsNumber($password)){
            if(!empty($reason)){
                $reason .= '<br/>';
            }
            $reason .= 'Must contain at least 1 number.';
            $badPass = true;
        }
        
        $noWhiteSpace = $this->getNoWhiteSpace();
        if($noWhiteSpace && GI_StringUtils::stringContainsWhiteSpace($password)){
            if(!empty($reason)){
                $reason .= '<br/>';
            }
            $reason .= 'Cannot contain any whitespace.';
            $badPass = true;
        }
        
        $confFieldName = $this->getConfFieldName();
        if($confFieldName){
            $confPassword = filter_input(INPUT_POST, $confFieldName);
            if($password !== $confPassword){
                $confReason .= 'Must be re-entered exactly the same twice.';
                $badConfPass = true;
            }
        }
        
        if($this->form && $this->form->wasSubmitted()){
            if($badPass){
                $this->form->addFieldError($fieldName, 'invalid', $reason);
            }
            if($badConfPass){
                $this->form->addFieldError($confFieldName, 'invalid', $confReason);
            }
        }
        
        if($badPass || $badConfPass){
            return false;
        }
        return true;
    }
    
    protected function buildView(){
        $fieldName = $this->getFieldName();
        $confFieldName = $this->getConfFieldName();
        
        $this->addHTML('<div class="validate_pass" ');
        if(!empty($fieldName)){
            $this->addHTML('data-field="' . $fieldName . '" ');
        }
        if(!empty($confFieldName)){
            $this->addHTML('data-conf-field="' . $confFieldName . '"');
        }
        $this->addHTML('>');
        $this->addHTML('<ul class="sml_text pass_check">');
        
        $showCannotBeSame = $this->getShowCannotBeSame();
        if($showCannotBeSame){
            $this->addHTML('<li>Cannot be the same as your current password.</li>');
        }
        
        $minLength = $this->getMinLength();
        if($minLength > 1){
            $this->addHTML('<li data-rule="length" data-val="' . $minLength . '">Must be at least ' . $minLength . ' characters long.</li>');
        }
        
        $forceUpper = $this->getForceUpper();
        if($forceUpper){
            $this->addHTML('<li data-rule="upper" data-val="1">Must contain at least 1 uppercase letter.</li>');
        }
        
        $forceLower = $this->getForceLower();
        if($forceLower){
            $this->addHTML('<li data-rule="lower" data-val="1">Must contain at least 1 lowercase letter.</li>');
        }
        
        $forceSymbol = $this->getForceSymbol();
        if($forceSymbol){
            $this->addHTML('<li data-rule="symbol" data-val="1">Must contain at least 1 symbol. (ex. #,@,!,?)</li>');
        }
        
        $forceNum = $this->getForceNum();
        if($forceNum){
            $this->addHTML('<li data-rule="number" data-val="1">Must contain at least 1 number.</li>');
        }
        
        $noWhiteSpace = $this->getNoWhiteSpace();
        if($noWhiteSpace){
            $this->addHTML('<li data-rule="whitespace" data-val="0">Cannot contain any whitespace.</li>');
        }
        
        if(!empty($confFieldName)){
            $this->addHTML('<li data-rule="match" data-val="1">Must be entered exactly the same twice.</li>');
        }
        $this->addHTML('</ul>');
        $this->addHTML('</div>');
    }
    
    public function beforeReturningView() {
        if($this->form && $this->form->wasSubmitted()){
            $this->validatePassword();
        }
        $this->buildView();
    }
    
}
