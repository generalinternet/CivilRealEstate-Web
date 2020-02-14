<?php
/**
 * Description of AbstractGI_Form
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractGI_Form {
    
    /**
     * @var GI_FormItem[]
     */
    protected $formItemObjs = array();
    
    /***LEGACY***/
    
    protected $formErrors = array();
    protected $fieldErrors = array();
    protected $formItems = array();
    protected $fieldNames = array();
    protected $arrayFieldKeys = array();
    protected $formID = '';
    protected $formClass = array();
    protected $displayFormErrors = true;
    protected $fileField = false;
    protected $btnText = 'Submit';
    protected $botValidation = false;
    protected $formAction = '';
    protected $formTarget = NULL;
    
    function __construct($formID){
        if(!empty($formID)){
            $this->formID = $formID; 
            if($this->isValid($formID,'clean')){
                $this->formID = $formID;                
            } else {
                $this->addFormError('Form ID "<mark>'.$formID.'</mark>" is not a clean ID.');
            }
        } else {
            $this->addFormError('Unique form ID required.');
        }
    }
    
    public function removeAllFormItems(){
        $this->formItems = array();
        $this->fieldNames = array();
        $this->arrayFieldKeys = array();
        $this->fileField = false;
        $this->botValidation = false;
    }
    
    public function removeAllErrors(){
        $this->formErrors = array();
        $this->fieldErrors = array();
    }
    
    public function setBotValidation($botValidation){
        $this->botValidation = $botValidation;
        return $this;
    }
    
    public function getFormId(){
        return $this->formID;
    }
    
    public function addFormClass($class) {
        if (!in_array($class, $this->formClass)) {
            $this->formClass[] = $class;
        }
    }
    
    public function setFormAction($formAction){
        $this->formAction = $formAction;
        return $this;
    }
    
    public function setFormTarget($formTarget){
        $this->formTarget = $formTarget;
        return $this;
    }
    
    public function addFormError($errorText, $addInfo=array()){
        $newError = array('errorText'=>$errorText);
        foreach($addInfo as $key => $val){
            if($key != 'errorText'){
                $newError[$key] = $val;
            }
        }
        $this->formErrors[] = $newError;
        return true;
    }
    
    protected function getFormError($key){
        if(isset($this->formErrors[$key]) && !empty($this->formErrors[$key]['errorText'])){
            $html = '<li>'.$this->formErrors[$key]['errorText'].'</li>';
            return $html;
        } else {
            return false;
        }
    }
    
    public function setBtnText($btnText){
        $this->btnText = $btnText;
    }
    
    public function setDisplayFormErrors($displayErrors){
        $this->displayFormErrors = $displayErrors;
    }
    
    protected function getFormErrors(){
        if($this->displayFormErrors && !empty($this->formErrors)){
            $html = '
            <div class="form_errors_wrap">
                <div class="form_errors">
                    <ul>
            ';
            foreach($this->formErrors as $key => $info){
                $getError = $this->getFormError($key);
                if($getError){
                    $html .= $getError;
                }
            }
            $html .= '
                    </ul>
                </div>
            </div>';
            return $html;
        }
    }
    
    /**
     * 
     * @param string $name
     * @param string $error Error type
     * @param string $errorText Error text (shows under field)
     * @return \GI_Form
     */
    public function addFieldError($name, $error, $errorText=''){
        $errorInfo = array(
            'error'=>$error
        );
        if(!empty($errorText)){
            $errorInfo['errorText'] = $errorText;
        }
        $this->fieldErrors[$name] = $errorInfo;
        return $this;
    }
    
    public function getFieldError($name){
        if(isset($this->fieldErrors[$name]['errorText'])){
            return $this->fieldErrors[$name]['errorText'];
        }
        return NULL;
    }
    
    public function getFieldErrors(){
        return $this->fieldErrors;
    }
    
    public function fieldErrorCount(){
        return count($this->fieldErrors);
    }
    
    /**
     * @param string $name
     * @return \GI_Form
     */
    public function startFieldset($name, $settings = array()){
        $settings['name'] = $name;
        $itemInfo = array(
            'type' => 'fieldset',
            'settings' => $settings
        );
        $this->formItems[] = $itemInfo;
        return $this;
    }
    
    /**
     * @return \GI_Form
     */
    public function endFieldset(){
        $itemInfo = array(
            'type'=>'endFieldset',
            'settings' => array()
        );
        $this->formItems[] = $itemInfo;
        return $this;
    }
    
    /**
     * @param string $stepTitle
     * @return \GI_Form
     */
    public function startStep($stepTitle, $stepRef = NULL){
        if(is_null($stepRef)){
            $stepRef = GI_Sanitize::ref($stepTitle);
        }
        $itemInfo = array(
            'type' => 'step',
            'settings' => array(
                'title' => $stepTitle,
                'ref' => $stepRef
            )
        );
        $this->formItems[] = $itemInfo;
        return $this;
    }
    
    /**
     * @return \GI_Form
     */
    public function endStep(){
        $itemInfo = array(
            'type' => 'endStep',
            'settings' => array()
        );
        $this->formItems[] = $itemInfo;
        return $this;
    }
    
    /**
     * @param boolean $wizardStyle
     * @return \GI_Form
     */
    public function startStepWrap($wizardStyle = true, $startStep = NULL){
        $itemInfo = array(
            'type' => 'stepWrap',
            'settings' => array(
                'wizardStyle' => $wizardStyle,
                'startStep' => $startStep
            )
        );
        $this->formItems[] = $itemInfo;
        return $this;
    }
    
    /**
     * @return \GI_Form
     */
    public function endStepWrap(){
        $itemInfo = array(
            'type' => 'endStepWrap',
            'settings' => array()
        );
        $this->formItems[] = $itemInfo;
        return $this;
    }
    
    /**
     * @param string $html
     * @return \GI_Form
     */
    public function addHTML($html){
        $itemInfo = array(
            'type' => 'content',
            'settings'=>array(
                'content' => $html
            )            
        );
        $this->formItems[] = $itemInfo;
        return $this;
    }    
    
    /**
     * @param string $html
     * @return \GI_Form
     * @deprecated
     */
    public function addContent($html){
        return $this->addHTML($html);
    }
    
    /**
     * 
     * @param string $name field name (for $_POST[$name])
     * @param string $type field type
     * @param array $settings array of field settings
     * @return \GI_Form
     */
    public function addField($name, $type = NULL, $settings = array()){
        if($name == 'recaptcha' && is_null($type)){
            $type = 'recaptcha';
        }
        if(isset($settings['name'])){
            unset($settings['name']);
        }
        $settings['name'] = $name;
        if(isset($settings['type'])){
            unset($settings['type']);
        }
        $settings['type'] = $type;
        if(in_array($name, $this->fieldNames)){
            if($type != 'radio' && strpos($name, '[]') === false){
                $this->addFormError('There is more than one field with the name "<mark>'.$name.'</mark>"');
            }
        } else {
            $this->fieldNames[] = $name;
        }
        if($type=='file'){
            $this->fileField = true;
        }
        $itemInfo = array(
            'type' => 'field',
            'settings' => $settings
        );
        $this->formItems[] = $itemInfo;
        return $this;
    }
    
    public function wasSubmitted(){
        if(!empty($this->formID)){
            if(filter_input(INPUT_POST, $this->formID)) {
                return true;
            } else {
                return false;
            }
        } else {
            $this->addFormError('No unique form ID provided to check if form was submitted.');
            return false;
        }
    }
    
    public function isValid($string, $match){
        switch($match){
            case 'clean':
                $regex = '/^[a-z][a-zA-Z0-9]*(?:_[a-zA-Z0-9]+)*$/';
                break;
            default:
                $regex = $match;
                break;
        }
        if(!preg_match($regex,$string)){
            return false;
        } else {
            return true;
        }
    }
    
    public static function getDataAttrString($dataArray = array()){
        $string = '';
        foreach($dataArray as $dataAttr => $dataVal){
            if($dataAttr == 'optionClass'){
                $string .= ' class';
            } else {
                $string .= ' data';
                $dataAttrChunks = preg_split('/(?=[A-Z])/',$dataAttr);
                foreach($dataAttrChunks as $chunk){
                    $string .= '-' . $chunk;
                }
            }
            $string .= '="' . $dataVal . '"';
        }
        return $string;
    }
    
    public function setArrayFieldStartingKey($name, $startingKey = 0){
        $cleanName = preg_replace('/\[(.*)\]/', '', $name);
        if(!isset($this->arrayFieldKeys[$cleanName])){
            $this->arrayFieldKeys[$cleanName] = $startingKey;
        }
    }
    
    public function getField($name, $type = NULL, $settings = array()){
        if($name == 'recaptcha' && is_null($type)){
            $type = 'recaptcha';
        }
        
        $inputName = $name;
        $cleanName = preg_replace('/\[(.*)\]/', '', $name);
        $idNameSuffix = $cleanName;
        $postArray = false;
        if (strpos($name, '[') !== false) {
            $postArray = true;
            if(!isset($this->arrayFieldKeys[$cleanName])){
                $this->arrayFieldKeys[$cleanName] = 0;
            }
            $postArrayKey = $this->arrayFieldKeys[$cleanName];
            $idNameSuffix .= '_' . $postArrayKey;
            $this->arrayFieldKeys[$cleanName]++;
        }
        
        $errorRefName = $idNameSuffix;
        
        $settingDefaults = array(
            'displayName' => ucwords(str_replace('_', ' ', $cleanName)),
            'value' => '',
            'placeHolder' => '',
            'required' => false,
            'autoComplete' => true,
            'autoFocus' => false,
            'disabled' => false,
            'maxLength' => 255,
            'readOnly' => false,
            'fieldID' => 'field_' . $idNameSuffix,
            'fieldClass' => 'gi_field_' . $type,
            'formElementID' => 'felm_' . $idNameSuffix,
            'options' => array(),
            'optionGroups' => array(),
            'optionData' => array(),
            'disabledOptions' => array(),
            'disabledOptionGroups' => array(),
            'clearValue' => false,
            'useProvidedValueOnClear' => false,
            'class' => '',
            'labelClass' => 'main',
            'formElementClass' => 'form_element',
            'fieldContentClass' => 'field_content',
            'fieldDescriptionClass' => 'field_description',
            'showLabel' => true,
            'description' => '',
            'showDescription' => true,
            'showError' => true,
            'hideDescOnError' => true,
            'preHTML' => '',
            'postHTML' => '',
            'maxVal' => NULL,
            'minVal' => NULL,
            'fieldDataAttrs' => array(),
            'formElementDataAttrs' => array(),
            'tabIndex' => NULL,
            'hiddenDescTitle' => NULL,
            'hiddenDesc' => NULL
        );
        $forceReadOnly = false;
        if($type=='radio'){
            $settingDefaults['stayOn'] = false;
        }
        if($type=='date' || $type=='event' || $type=='datetime' || $type=='reminder'){
            $settingDefaults['dateFormat'] = 'yy-mm-dd';
            $settingDefaults['phpDateFormat'] = 'Y-m-d';
            $settingDefaults['changeMonth'] = true;
            $settingDefaults['changeYear'] = true;
            $settingDefaults['yearRange'] = 'c-10:c+10';
            $settingDefaults['minDate'] = 'null';
            $settingDefaults['minDateFromField'] = '';
            $settingDefaults['maxDate'] = 'null';
            $settingDefaults['maxDateFromField'] = '';
            //$settingDefaults['readOnly'] = true;
            $settingDefaults['defaultDate'] = 'null';
            $settingDefaults['firstDay'] = 1;
            //$forceReadOnly = true;
        }
        if($type=='time' || $type=='alarm' || $type=='datetime' || $type=='reminder'){
            $settingDefaults['timeFormat'] = 'h:mm tt';
            $settingDefaults['phpTimeFormat'] = 'g:i a';
            $settingDefaults['stepMinute'] = 1;
            $settingDefaults['minuteGrid'] = 0;
            $settingDefaults['controlType'] = 'select'; //slider
            //$settingDefaults['readOnly'] = true;
            //$forceReadOnly = true;
        }
        if($type=='autocomplete'){
            $settingDefaults['autocompMinLength'] = 1;
            $settingDefaults['autocompValue'] = '';
            $settingDefaults['autocompDuplicates'] = false;
            $settingDefaults['autocompMultiple'] = false;
            $settingDefaults['autocompRemFull'] = true;
            $settingDefaults['autocompURL'] = '';
            $settingDefaults['autocompAppendTo'] = 'self';
            $settingDefaults['autocompLimit'] = 0;
            $settingDefaults['autocompHiddenFieldClass'] = '';
        }
        if($type == 'textarea' || $type == 'wysiwyg') {
            $settingDefaults['maxLength'] = '';
        }
        if($type == 'checkbox' || $type == 'radio' || $type == 'onoff'){
            $settingDefaults['labelBeforeBox'] = false;
            $settingDefaults['minSelections'] = NULL;
            $settingDefaults['maxSelections'] = NULL;
            $settingDefaults['tabIndex'] = 0;
        }
        if($type == 'wysiwyg'){
            $settingDefaults['wygBtnHTML'] = true;
            $settingDefaults['wygBtnUndo'] = true;
            $settingDefaults['wygBtnFormat'] = true;
            $settingDefaults['wygBtnBold'] = true;
            $settingDefaults['wygBtnItalic'] = true;
            $settingDefaults['wygBtnUnderline'] = true;
            $settingDefaults['wygBtnStrike'] = true;
            $settingDefaults['wygBtnSuperscript'] = false;
            $settingDefaults['wygBtnSubscript'] = false;
            $settingDefaults['wygBtnLink'] = true;
            $settingDefaults['wygBtnJustify'] = false;
            $settingDefaults['wygBtnLists'] = true;
            $settingDefaults['wygBtnRule'] = false;
            $settingDefaults['wygBtnCode'] = false;
            $settingDefaults['wygBtnTable'] = false;
            $settingDefaults['wygBtnUnformat'] = true;
            $settingDefaults['wygBtnFullscreen'] = true;
        }
        if($type == 'dropdown') {
            $settingDefaults['hideNull'] = false;
            $settingDefaults['nullText'] = 'SELECT';
            $settingDefaults['nullValue'] = 'NULL';
            $settingDefaults['htmlOptions'] = false;
        } elseif($type == 'select'){
            $settingDefaults['hideNull'] = false;
            $settingDefaults['nullText'] = 'SELECT';
            $settingDefaults['nullValue'] = '';
            $settingDefaults['htmlOptions'] = false;
        }
        if($type == 'money' || $type == 'money_rate'){
            $settingDefaults['currency'] = 'cad';
        }
        if($type == 'tag'){
            $settingDefaults['tagLimit'] = 0;
            $settingDefaults['autocompURL'] = '';
            $settingDefaults['autocompMinLength'] = 1;
            $settingDefaults['autocompAppendTo'] = 'self';
        }
        
        if($type == 'onoff'){
            $settingDefaults['onoffValue'] = 1;
            $settingDefaults['onoffStyleAsCheckbox'] = false;
            $settingDefaults['onoffShowLabels'] = false;
            $settingDefaults['onoffOnLabel'] = 'On';
            $settingDefaults['onoffOffLabel'] = 'Off';
        }
        if($type == 'signature'){
            $settingDefaults['signatureRewriteLabel'] = 'Rewrite';
            $settingDefaults['signatureCheckboxClass'] = 'gi_field_onoff';
            $settingDefaults['signatureCheckboxWrapClass'] = 'form_element';
            $settingDefaults['signatureCheckboxLabel'] = 'Signed and confirmed.';
            $settingDefaults['signatureBtnClass'] = 'other_btn';
            $settingDefaults['signatureImgDataValue'] = '';
            $settingDefaults['signatureImgTypeValue'] = '';
            $settingDefaults['signatureFile'] = NULL;
            $settingDefaults['signaturePrintNameClass'] = 'form_element';
            $settingDefaults['signaturePrintName'] = true;
            $settingDefaults['signaturePrintNameRequired'] = false;
            $settingDefaults['signaturePrintNameLabel'] = 'Print Name';
        }
        if($type == 'colour' || $type == 'color'){
            $settingDefaults['pickerClass'] = '';
            $settingDefaults['hideKeyboard'] = true;
            $settingDefaults['pickerLayout'] = 'popup'; //block
            $settingDefaults['pickerSliders'] = '';
            /*
            stting = whsvrgbap
            w - color wheel
            h - hue slider
            s - saturation slider
            v - value slider
            r - red slider
            g - green slider
            b - blue slider
            a - alpha slider
            p - color preview
            */
            $settingDefaults['pickerSnap'] = true;
        }
        
        if($type=='recaptcha'){
            $settingDefaults['showLabel'] = false;
        }
        if($type == 'otp'){
            $settingDefaults['required'] = true;
        }
        foreach($settings as $settingName => $settingValue){
            ${$settingName} = $settingValue;
        }
        foreach($settingDefaults as $settingName => $defaultValue){
            if(!isset(${$settingName})) ${$settingName} = $defaultValue;
        }
        
        if($formElementClass != 'form_element'){
            if (strpos($formElementClass, 'form_element') === false) {
                $formElementClass = 'form_element '.$formElementClass;
            }
        }
        
        if($fieldContentClass != 'field_content'){
            if (strpos($fieldContentClass, 'field_content') === false) {
                $fieldContentClass = 'field_content '.$fieldContentClass;
            }
        }
        
        if($fieldDescriptionClass != 'field_description'){
            if (strpos($fieldDescriptionClass, 'field_description') === false) {
                $fieldDescriptionClass = 'field_description '.$fieldDescriptionClass;
            }
        }
        
        if($fieldClass != 'gi_field_' . $type){
            if (strpos($fieldClass, 'gi_field_' . $type) === false) {
                $fieldClass = 'gi_field_' . $type . ' ' . $fieldClass;
            }
        }
        
        $formElementClass .= ' '.$class;
        if(isset($this->fieldErrors[$errorRefName])){
            $formElementClass .= ' error';
            $fieldClass .= ' error';
        }
        
        if($this->wasSubmitted()){
            $formElementClass .= ' submitted';
            $fieldClass .= ' submitted';
        }
        
        if($required){
            $labelClass .= ' required';
            $formElementClass .= ' required';
            $fieldClass .= ' required';
        }
        
        if($type == 'signature'){
            if(isset($signatureCheckboxWrapClass) && $signatureCheckboxWrapClass != 'form_element'){
                if (strpos($signatureCheckboxWrapClass, 'form_element') === false) {
                    $signatureCheckboxWrapClass = 'form_element ' . $signatureCheckboxWrapClass;
                }
            }
            
            if($signaturePrintName){
                if(isset($signaturePrintNameClass) && $signaturePrintNameClass != 'form_element'){
                    if (strpos($signaturePrintNameClass, 'form_element') === false) {
                        $signaturePrintNameClass = 'form_element ' . $signaturePrintNameClass;
                    }
                }
                
                $printNameFieldName = $inputName . '_print_name';
                if(isset($this->fieldErrors[$printNameFieldName])){
                    $signaturePrintNameClass .= ' error';
                }
            }
        }
        
        if((!isset($value) || isset($_POST[$cleanName])) && !$clearValue){
            if($this->wasSubmitted()){
                if(is_array($_POST[$cleanName])){
                    $postValue = filter_input(INPUT_POST, $cleanName, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
                    $value = $postValue;
                    if($postArray){
                        $value = NULL;
                        if(isset($postValue[$postArrayKey])){
                            $value = $postValue[$postArrayKey];
                        }
                    }
                } else {
                    $postValue = filter_input(INPUT_POST, $cleanName);
                    $value = htmlentities($postValue, ENT_QUOTES);
                }
            }
        } else {
            if($this->wasSubmitted() && !$useProvidedValueOnClear){
                $value = NULL;
            } elseif(is_string($value)){            
                $value = htmlentities($value, ENT_QUOTES);
            }
        }
        if($readOnly && (!$forceReadOnly || (isset($settings['readOnly']) && $settings['readOnly']))) $fieldClass .= ' read_only';
        $autoFocus ? $autoFocusAttr = 'autofocus="autofocus"' : $autoFocusAttr = '';
        $disabledAttr = '';
        if($disabled){
            $formElementClass .= ' disabled';
            $disabledAttr = 'disabled="disabled"';
        }
        $readOnlyAttr = '';
        if($readOnly){
            $formElementClass .= ' read_only';
            $readOnlyAttr = 'readonly="readonly"';
        }
        if($autoComplete){
            $autoCompleteAttr = 'autocomplete="on"';
        } else {
            $autoCompleteAttr = 'autocomplete="off"';
        }
        if(!empty($inputAutoCompleteVal)){
            $autoCompleteAttr = 'autocomplete="' . $inputAutoCompleteVal . '"';
        }
        !empty($placeHolder) ? $placeHolderAttr = 'placeholder="'.htmlentities($placeHolder).'"' : $placeHolderAttr = '';
        !empty($maxLength) ? $maxLengthAttr = 'maxlength="'.$maxLength.'"' : $maxLengthAttr = '';        
        
        $maxValAttr = '';
        if(!is_null($maxVal)){
            $maxValAttr = 'data-max-val="' . $maxVal . '"';
        }
        
        $minValAttr = '';
        if(!is_null($minVal)){
            $minValAttr = 'data-min-val="' . $minVal . '"';
        }
        
        $tabIndexAttr = '';
        if(!is_null($tabIndex)){
            $tabIndexAttr = 'tabindex="' . $tabIndex . '"';
        }
        
        $fieldDataAttrString = '';
        if(!empty($fieldDataAttrs)){
            foreach($fieldDataAttrs as $dataAttr => $dataVal){
                $fieldDataAttrString .= ' data-' . $dataAttr . '="' . $dataVal . '"';
            }
        }
        
        switch($type){
            case 'tag':
                $finalLabel = '<label for="'.$fieldID.'" class="'.$labelClass.'">'.$displayName.'</label>';
                $autocompURLAttr = '';
                if(!empty($autocompAppendTo)){
                    $autocompAppendToAttr = 'data-append-to="'.$autocompAppendTo.'"';
                } else {
                    $autocompAppendToAttr = '';
                }
                if($autocompURL){
                    $autocompURLAttr = 'data-url="'.$autocompURL.'"';
                }
                $finalContent = '<input type="text" name="'.$inputName.'" id="'.$fieldID.'" value="'.$value.'" class="tagit_field '.$fieldClass.'" '.$placeHolderAttr.' data-tag-limit="'.$tagLimit.'" '.$autoCompleteAttr.' '.$autoFocusAttr.' '.$readOnlyAttr.' '.$disabledAttr.'  '.$autocompURLAttr.' '.$autocompAppendToAttr.' data-min-length="'.$autocompMinLength.'" ' . $tabIndexAttr . ' ' . $fieldDataAttrString . ' />';
                break;
            case 'autocomplete':
                $autoName = $cleanName . '_autocomp';
                $autoCleanName = $autoName;
                $autoResultsID = 'acresults_' . $cleanName;
                $autoListID = 'aclist_' . $cleanName;
                $autoID = $autoName;
                if($postArray){
                    $autoCleanName = $autoName;
                    $autoID = $autoName . '_' . $postArrayKey;
                    $autoName = $cleanName . '_autocomp[]';
                    $autoResultsID = 'acresults_' . $cleanName . '_' . $postArrayKey;
                    $autoListID = 'aclist_' . $cleanName . '_' . $postArrayKey;
                }
                
                if($this->wasSubmitted()){
                    $autoVal = htmlentities(filter_input(INPUT_POST, $autoName));
                    if(isset($_POST[$autoCleanName]) && is_array($_POST[$autoCleanName])){
                        $autoPostVal = filter_input(INPUT_POST, $autoCleanName, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
                        $autoVal = $autoPostVal;
                        if($postArray){
                            $autoVal = $autoPostVal[$postArrayKey];
                        }
                    } else {
                        $autoPostVal = filter_input(INPUT_POST, $autoCleanName);
                        $autoVal = htmlentities($autoPostVal, ENT_QUOTES);
                    }
                } else {
                    $autoVal = $autocompValue;
                }
                if(!$autocompDuplicates && $autocompMultiple){
                    $valArray = explode(',',$value);
                    $value = implode(',',array_unique($valArray));
                }
                
                $autocompRemFull ? $autocompRemFullVal = 'true' : $autocompRemFullVal = 'false';
                $autocompMultiple ? $autocompMultipleVal = 'true' : $autocompMultipleVal = 'false';
                $autocompDuplicates ? $autocompDuplicatesVal = 'true' : $autocompDuplicatesVal = 'false';
                
                $autocompAppendToAttr = '';
                if(!empty($autocompAppendTo)){
                    $autocompAppendToAttr = 'data-append-to="'.$autocompAppendTo.'"';
                }
                
                $postArrayKeyAttr = '';
                if($postArray){
                    $postArrayKeyAttr = 'data-post-array-key="' . $postArrayKey . '"';
                }
                
                $finalLabel = '<label for="'.$autoID.'" class="'.$labelClass.'">'.$displayName.'</label>';
                
                $autocompDropdown = '';
                if($autocompMinLength == 0){
                    $autocompDropdown = '<span class="autocomp_dropdown"></span>';
                    $fieldClass .= ' autocomp_dropdown_field';
                }
                
                $autocompSearchIcon = '<span class="autocomp_search_icon">' . GI_StringUtils::getSVGIcon('search', '1em', '1em') . '</span>';
                
                $finalContent = '<span class="autocomp_field_wrap">' . $autocompDropdown . $autocompSearchIcon . '<input type="text" class="'.$fieldClass.'" name="'.$autoName.'" id="'.$autoID.'" value="'.$autoVal.'" '.$maxLengthAttr.' '.$placeHolderAttr.' '.$autoCompleteAttr.' '.$autoFocusAttr.' '.$disabledAttr.' '.$readOnlyAttr.'
                data-url="'.$autocompURL.'" data-rem-full="'.$autocompRemFullVal.'" data-multiple="'.$autocompMultipleVal.'" data-duplicates="'.$autocompDuplicatesVal.'" '.$autocompAppendToAttr.' data-min-length="'.$autocompMinLength.'"  data-limit="'.$autocompLimit.'" ' . $tabIndexAttr . ' ' . $fieldDataAttrString . ' /></span>';
                $finalContent .= '<div id="'.$autoResultsID.'" class="ac_results"></div>';
                if($autocompMultiple) $finalContent .= '<ul id="'.$autoListID.'" class="ac_list"></ul>';
                $finalContent .= '<input type="hidden" name="' . $inputName . '" id="'.$fieldID.'" value="'.$value.'" class="autocomp ' . $autocompHiddenFieldClass . '" ' . $postArrayKeyAttr . ' />';
                break;
            case 'alarm':
            case 'time':
                $finalLabel = '<label for="'.$fieldID.'" class="'.$labelClass.'">'.$displayName.'</label>';
                $value = GI_Time::formatDateTimeForDisplay($value, $phpTimeFormat);
                $finalContent = '<input type="text" name="' . $inputName . '" id="'.$fieldID.'" value="'.$value.'" class="time_field '.$fieldClass.'" '.$maxLengthAttr.' '.$readOnlyAttr.' '.$placeHolderAttr.' data-time-format="'.$timeFormat.'" data-minute-grid="'.$minuteGrid.'" data-step-minute="'.$stepMinute.'" data-control-type="'.$controlType.'" '.$autoCompleteAttr.' '.$autoFocusAttr.' '.$disabledAttr.' ' . $tabIndexAttr . ' ' . $fieldDataAttrString . ' />';
                break;
            case 'date':
            case 'event':
                $changeMonth ? $changeMonthVal = 'true' : $changeMonthVal = 'false';
                $changeYear ? $changeYearVal = 'true' : $changeYearVal = 'false';
                $minDateFromFieldAttr = '';
                if(!empty($minDateFromField)){
                    $minDateFromFieldAttr = 'data-min-date-from="' . $minDateFromField . '"';
                }
                $maxDateFromFieldAttr = '';
                if(!empty($maxDateFromField)){
                    $maxDateFromFieldAttr = 'data-max-date-from="' . $maxDateFromField . '"';
                }
                $finalLabel = '<label for="'.$fieldID.'" class="'.$labelClass.'">'.$displayName.'</label>';
                $value = GI_Time::formatDateTimeForDisplay($value, $phpDateFormat);
                $finalContent = '<input type="text" name="' . $inputName . '" id="'.$fieldID.'" value="'.$value.'" class="date_field '.$fieldClass.'" '.$maxLengthAttr.' '.$readOnlyAttr.' '.$placeHolderAttr.' data-date-format="'.$dateFormat.'" data-min-date="'.$minDate.'" ' . $minDateFromFieldAttr . ' data-max-date="'.$maxDate.'" ' . $maxDateFromFieldAttr . ' data-default-date="'.$defaultDate.'" data-change-month="'.$changeMonthVal.'" data-change-year="'.$changeYearVal.'" data-year-range="'.$yearRange.'" '.$autoCompleteAttr.' '.$autoFocusAttr.' '.$disabledAttr.' data-first-day="' . $firstDay . '" ' . $tabIndexAttr . ' ' . $fieldDataAttrString . ' />';
                break;
            case 'datetime':
            case 'reminder':
                $changeMonth ? $changeMonthVal = 'true' : $changeMonthVal = 'false';
                $changeYear ? $changeYearVal = 'true' : $changeYearVal = 'false';
                $minDateFromFieldAttr = '';
                if(!empty($minDateFromField)){
                    $minDateFromFieldAttr = 'data-min-date-from="' . $minDateFromField . '"';
                }
                $maxDateFromFieldAttr = '';
                if(!empty($maxDateFromField)){
                    $maxDateFromFieldAttr = 'data-max-date-from="' . $maxDateFromField . '"';
                }
                $finalLabel = '<label for="'.$fieldID.'" class="'.$labelClass.'">'.$displayName.'</label>';
                $value = GI_Time::formatDateTimeForDisplay($value, $phpDateFormat .' '. $phpTimeFormat);
                $finalContent = '<input type="text" name="' . $inputName . '" id="'.$fieldID.'" value="'.$value.'" class="date_time_field '.$fieldClass.'" '.$maxLengthAttr.' '.$readOnlyAttr.' '.$placeHolderAttr.' data-date-format="'.$dateFormat.'" data-min-date="'.$minDate.'" ' . $minDateFromFieldAttr . ' data-max-date="'.$maxDate.'" ' . $maxDateFromFieldAttr . ' data-default-date="'.$defaultDate.'" data-change-month="'.$changeMonthVal.'" data-change-year="'.$changeYearVal.'" data-year-range="'.$yearRange.'" data-time-format="'.$timeFormat.'" data-minute-grid="'.$minuteGrid.'" data-step-minute="'.$stepMinute.'" data-control-type="'.$controlType.'" '.$autoCompleteAttr.' '.$autoFocusAttr.' '.$disabledAttr.' data-first-day="' . $firstDay . '" ' . $tabIndexAttr . ' ' . $fieldDataAttrString . ' />';
                break;
            case 'checkbox':
            case 'radio':
                if(!is_null($minSelections)){
                    $formElementDataAttrs['min-selections'] = $minSelections;
                }
                if(!is_null($maxSelections)){
                    $formElementDataAttrs['max-selections'] = $maxSelections;
                }
                $finalLabel = '<label class="'.$labelClass.'">'.$displayName.'</label>';
                if($type=='checkbox'){
                    $boxInputName = $inputName."[]";
                    if(!is_array($value)){
                        $value = explode(',', $value);
                    }
                } else {
                    $boxInputName = $inputName;
                }
                $finalContent = '<div class="options_wrap">';
                if($type=='radio' && $stayOn){
                    $fieldClass .= ' stay_on';
                }
                if(isset($optionGroups) && !empty($optionGroups)){
                    if(is_array($optionGroups)){
                        foreach($optionGroups as $optGroup=>$groupOpts){
                            in_array($optGroup,$disabledOptionGroups) ? $groupDis = 'disabled' : $groupDis = '';
                            $finalContent .= '<div class="option_group '.$groupDis.'" ><div class="option_group_title">'.$optGroup.'</div>';
                            if(is_array($groupOpts)){
                                foreach($groupOpts as $optVal=>$optLabel){
                                    $optDisClass = '';
                                    if(in_array($optVal,$disabledOptions)){
                                        $optDis = 'disabled="disabled"';
                                        $optDisClass = 'disabled';
                                    } else {
                                        $optDis ='';
                                    }
                                    if($groupDis=='disabled'){
                                        $optDis = 'disabled="disabled"';
                                    }
                                    $optChk = '';
                                    if(is_array($value) && in_array($optVal,$value)){
                                        $optChk = 'checked="checked"';
                                    } elseif($value==$optVal) {
                                        $optChk = 'checked="checked"';
                                        if($type == 'radio'){
                                            $optChk .= ' data-prev-val="true"';
                                        }
                                    }
                                    $finalContent .= '<label class="'.$optDisClass.'" ' . $tabIndexAttr . '>';
                                    $boxInputHTML = '<input type="'.$type.'" name="'.$boxInputName.'" value="'.$optVal.'" '.$optDis.' '.$optChk.' class="'.$fieldClass.'" ' . $fieldDataAttrString . ' />';
                                    $boxLabelHTML = '';
                                    if(!empty($optLabel)){
                                        $boxLabelHTML = '<span class="'.$type.'_label">'.$optLabel.'</span>';
                                    }
                                    if($labelBeforeBox){
                                        $finalContent .= $boxLabelHTML . $boxInputHTML;
                                    } else {
                                        $finalContent .= $boxInputHTML . $boxLabelHTML;
                                    }
                                    $finalContent .= '</label>';
                                                      
                                }
                            } 
                            $finalContent .= '</div>';
                        }
                    } 
                } else {
                    if(is_array($options)){
                        if(!empty($options)){
                            foreach($options as $optVal=>$optLabel){
                                $optDisClass = '';
                                if(in_array($optVal,$disabledOptions)){
                                    $optDis = 'disabled="disabled"';
                                    $optDisClass = 'disabled';
                                } else {
                                    $optDis ='';
                                }
                                $optChk = '';
                                if(is_array($value) && in_array($optVal,$value)){
                                    $optChk = 'checked="checked"';
                                } elseif($value==$optVal) {
                                    $optChk = 'checked="checked"';
                                    if($type == 'radio'){
                                        $optChk .= ' data-prev-val="true"';
                                    }
                                }
                                $finalContent .= '<label class="'.$optDisClass.'" ' . $tabIndexAttr . '>';
                                $boxInputHTML = '<input type="'.$type.'" name="'.$boxInputName.'" value="'.$optVal.'" '.$optDis.' '.$optChk.' class="'.$fieldClass.'" ' . $fieldDataAttrString . ' />';
                                $boxLabelHTML = '';
                                if(!empty($optLabel)){
                                    $boxLabelHTML = '<span class="'.$type.'_label">'.$optLabel.'</span>';
                                }
                                if($labelBeforeBox){
                                    $finalContent .= $boxLabelHTML . $boxInputHTML;
                                } else {
                                    $finalContent .= $boxInputHTML . $boxLabelHTML;
                                }
                                $finalContent .= '</label>';
                            }
                        } 
                    } 
                }
                $finalContent .= '</div>';
                break;
            case 'decimal':
                $finalLabel = '<label for="'.$fieldID.'" class="'.$labelClass.'">'.$displayName.'</label>';
                $finalContent = '<input type="text" name="' . $inputName . '" id="'.$fieldID.'" value="'.$value.'" class="decimal_field '.$fieldClass.'" '.$maxLengthAttr.' '.$readOnlyAttr.' '.$placeHolderAttr.' '.$autoCompleteAttr.' '.$autoFocusAttr.' '.$disabledAttr.' ' . $minValAttr . ' ' . $maxValAttr . ' ' . $tabIndexAttr . ' ' . $fieldDataAttrString . ' />';
                break;
            case 'dropdown':
            case 'select':
                $finalLabel = '<label for="'.$fieldID.'" class="'.$labelClass.'">'.$displayName.'</label>';
                $selMult = '';
                $selName = $inputName;
                if($type=='select'){
                    $selMult = 'multiple="multiple"';
                    $selName = $inputName.'[]';
                    $fieldClass .= ' multi_select';
                }
                $finalContent = '<span class="'.$type.'_field_wrap"><select name="'.$selName.'" id="'.$fieldID.'" '.$selMult.' '.$disabledAttr.' class="'.$fieldClass.'" ' . $tabIndexAttr . ' ' . $fieldDataAttrString . '>
                ';
                if(!$hideNull && (!$readOnly || $value == $nullValue || empty($value))){
                    $nullOptRawLabel = '';
                    if($htmlOptions){
                        $nullOptRawLabel = 'data-raw-label="' . htmlspecialchars ($nullText) . '"';
                    }
                    $finalContent .= '<option value="' . $nullValue . '" class="null_option" ' . $nullOptRawLabel . '>' . $nullText . '</option>';
                }
                if(isset($optionGroups) && !empty($optionGroups)){
                    if(is_array($optionGroups)){
                        foreach($optionGroups as $optGroup=>$groupOpts){
                            in_array($optGroup,$disabledOptionGroups) ? $groupDis = 'disabled="disabled"' : $groupDis = '';
                            if(!$readOnly || array_key_exists($value, $groupOpts)) $finalContent .= '<optgroup label="'.$optGroup.'" '.$groupDis.'>';
                            if(is_array($groupOpts)){
                                foreach($groupOpts as $optVal=>$optLabel){
                                    in_array($optVal,$disabledOptions) ? $optDis = 'disabled="disabled"' : $optDis ='';
                                    if(is_array($value)){
                                        in_array($optVal,$value) ? $optSel = 'selected="selected"' : $optSel = '';
                                    } else {
                                        $value==$optVal ? $optSel = 'selected="selected"' : $optSel = '';
                                    }                                    
                                    if(!$readOnly || $value==$optVal) {
                                        $optRawLabel = '';
                                        if($htmlOptions){
                                            $optRawLabel = 'data-raw-label="' . htmlspecialchars ($optLabel) . '"';
                                        }
                                        $optDataString = '';
                                        if(isset($optionData[$optVal])){
                                            $optData = $optionData[$optVal];
                                            $optDataString = static::getDataAttrString($optData);
                                        }
                                        $finalContent .= '<option value="'.$optVal.'" '.$optDis.' '.$optSel.' ' . $optRawLabel . ' ' . $optDataString . '>'.$optLabel.'</option>';
                                    }
                                }
                            } 
                            if(!$readOnly || array_key_exists($value, $groupOpts)) $finalContent .= '</optgroup>';
                        }
                    } 
                } else {
                    if(is_array($options)){
                        if(!empty($options)){
                            foreach($options as $optVal=>$optLabel){
                                in_array($optVal,$disabledOptions) ? $optDis = 'disabled="disabled"' : $optDis ='';
                                if(is_array($value)){
                                    in_array($optVal,$value) ? $optSel = 'selected="selected"' : $optSel = '';
                                } else {
                                    $value==$optVal ? $optSel = 'selected="selected"' : $optSel = '';
                                } 
                                $optRawLabel = '';
                                if($htmlOptions){
                                    $optRawLabel = 'data-raw-label="' . htmlspecialchars ($optLabel) . '"';
                                }
                                if(!$readOnly || $value==$optVal){
                                    $optDataString = '';
                                    if(isset($optionData[$optVal])){
                                        $optData = $optionData[$optVal];
                                        $optDataString = static::getDataAttrString($optData);
                                    }
                                    $finalContent .= '<option value="'.$optVal.'" '.$optDis.' '.$optSel.' ' . $optRawLabel . ' ' . $optDataString . '>'.$optLabel.'</option>';
                                }
                            }
                        } 
                    } 
                }
                $finalContent .= '</select></span>';
                break;
            case 'email':
                $finalLabel = '<label for="'.$fieldID.'" class="'.$labelClass.'">'.$displayName.'</label>';
                $finalContent = '<input type="email" name="' . $inputName . '" id="'.$fieldID.'" value="'.$value.'" '.$maxLengthAttr.' '.$readOnlyAttr.' '.$placeHolderAttr.' '.$autoCompleteAttr.' '.$autoFocusAttr.' '.$disabledAttr.' class="'.$fieldClass.'" ' . $tabIndexAttr . ' ' . $fieldDataAttrString . ' />';
                break;
            case 'hidden':
                $finalLabel = '';
                $finalContent = '<input type="hidden" name="' . $inputName . '" id="'.$fieldID.'" value="'.$value.'" class="'.$fieldClass.'" ' . $fieldDataAttrString . ' />';
                break;
            case 'id':
            case 'integer_pos':
                $finalLabel = '<label for="'.$fieldID.'" class="'.$labelClass.'">'.$displayName.'</label>';
                $finalContent = '<input type="text" name="' . $inputName . '" id="'.$fieldID.'" value="'.$value.'" class="pos_int_field '.$fieldClass.'" '.$maxLengthAttr.' '.$readOnlyAttr.' '.$placeHolderAttr.' '.$autoCompleteAttr.' '.$autoFocusAttr.' '.$disabledAttr.' ' . $minValAttr . ' ' . $maxValAttr . ' ' . $tabIndexAttr . ' ' . $fieldDataAttrString . ' />';
                break;
            case 'integer':
            case 'integer_large':
                $finalLabel = '<label for="'.$fieldID.'" class="'.$labelClass.'">'.$displayName.'</label>';
                $finalContent = '<input type="text" name="' . $inputName . '" id="'.$fieldID.'" value="'.$value.'" class="int_field '.$fieldClass.'" '.$maxLengthAttr.' '.$readOnlyAttr.' '.$placeHolderAttr.' '.$autoCompleteAttr.' '.$autoFocusAttr.' '.$disabledAttr.' ' . $minValAttr . ' ' . $maxValAttr . ' ' . $tabIndexAttr . ' ' . $fieldDataAttrString . ' />';
                break;
            case 'money':
            case 'money_rate':
                $allowedCurrencies = array(
                    'gbp',
                    'generic',
                    'yen',
                    'eur',
                    'usd',
                    'cad'
                );
                $currency = strtolower($currency);
                $currency_class = '';
                if($type == 'money_rate'){
                    $fieldClass .= ' money_rate_field';
                } else {
                    $fieldClass .= ' money_field';
                }
                if(!empty($currency) && in_array($currency,$allowedCurrencies)) $currency_class = 'currency_'.$currency;
                $finalLabel = '<label for="'.$fieldID.'" class="'.$labelClass.'">'.$displayName.'</label>';
                $finalContent = '<span class="money_field_wrap '.$currency_class.'"><input type="text" name="' . $inputName . '" id="'.$fieldID.'" value="'.$value.'" class="'.$fieldClass.'" '.$maxLengthAttr.' '.$readOnlyAttr.' '.$placeHolderAttr.' '.$autoCompleteAttr.' '.$autoFocusAttr.' '.$disabledAttr.' ' . $minValAttr . ' ' . $maxValAttr . ' ' . $tabIndexAttr . ' ' . $fieldDataAttrString . ' /></span>';
                break;
            case 'onoff':
                if($onoffStyleAsCheckbox){
                    $showLabel = false;
                    $formElementClass .= ' list_options';
                }
                if($onoffShowLabels){
                    $fieldClass .= ' slide_toggle';
                }
                $finalLabel = '<label for="'.$fieldID.'" class="'.$labelClass.'">'.$displayName.'</label>';
                if(is_array($value) && in_array($onoffValue, $value)){
                    $optChk = 'checked="checked"';
                } elseif($value == $onoffValue) {
                    $optChk = 'checked="checked"';
                } else {
                    $optChk = '';
                }
                
                $finalContent = '<div class="options_wrap">';
                $finalContent .= '<label ' . $tabIndexAttr . '>';
                $boxInputHTML = '<input type="checkbox" name="' . $inputName . '" id="'.$fieldID.'" value="' . $onoffValue . '" '.$optChk.' '.$readOnlyAttr.' '.$disabledAttr.' class="'.$fieldClass.'" ' . $fieldDataAttrString . ' />';
                
                if($onoffShowLabels){
                    $boxInputHTML .= '<span class="off_label">' . $onoffOffLabel . '</span><span class="on_label">' . $onoffOnLabel . '</span>';
                }
                
                $boxLabelHTML = '<span class="'.$type.'_label checkbox_label">'.$displayName.'</span>';
                if($onoffStyleAsCheckbox){
                    if($labelBeforeBox){
                        $finalContent .= $boxLabelHTML . $boxInputHTML;
                    } else {
                        $finalContent .= $boxInputHTML . $boxLabelHTML;
                    }
                } else {
                    $finalContent .= $boxInputHTML;
                }
                $finalContent .= '</label>';
                $finalContent .= '</div>';
                
                break;
            case 'password':
                $finalLabel = '<label for="'.$fieldID.'" class="'.$labelClass.'">'.$displayName.'</label>';
                $finalContent = '<input type="password" name="' . $inputName . '" id="'.$fieldID.'" value="'.$value.'" '.$maxLengthAttr.' '.$readOnlyAttr.' '.$placeHolderAttr.' '.$autoCompleteAttr.' '.$autoFocusAttr.' '.$disabledAttr.' class="'.$fieldClass.'" ' . $tabIndexAttr . ' ' . $fieldDataAttrString . ' />';
                break;
            case 'percentage':
                $finalLabel = '<label for="'.$fieldID.'" class="'.$labelClass.'">'.$displayName.'</label>';
                $finalContent = '<span class="percentage_field_wrap"><input type="text" name="' . $inputName . '" id="'.$fieldID.'" value="'.$value.'" class="percentage_field '.$fieldClass.'" '.$maxLengthAttr.' '.$readOnlyAttr.' '.$placeHolderAttr.' '.$autoCompleteAttr.' '.$autoFocusAttr.' '.$disabledAttr.' ' . $minValAttr . ' ' . $maxValAttr . ' ' . $tabIndexAttr . ' ' . $fieldDataAttrString . ' /></span>';
                break;
            case 'phone':
                $finalLabel = '<label for="'.$fieldID.'" class="'.$labelClass.'">'.$displayName.'</label>';
                $finalContent = '<input type="text" name="' . $inputName . '" id="'.$fieldID.'" value="'.$value.'" class="phone_field '.$fieldClass.'" '.$maxLengthAttr.' '.$readOnlyAttr.' '.$placeHolderAttr.' '.$autoCompleteAttr.' '.$autoFocusAttr.' '.$disabledAttr.' ' . $tabIndexAttr . ' ' . $fieldDataAttrString . ' />';
                break;
            case 'text':
            default:
                $finalLabel = '<label for="'.$fieldID.'" class="'.$labelClass.'">'.$displayName.'</label>';
                $finalContent = '<input type="text" name="' . $inputName . '" id="'.$fieldID.'" value="'.$value.'" '.$maxLengthAttr.' '.$readOnlyAttr.' '.$placeHolderAttr.' '.$autoCompleteAttr.' '.$autoFocusAttr.' '.$disabledAttr.' class="'.$fieldClass.'" ' . $tabIndexAttr . ' ' . $fieldDataAttrString . ' />';
                break;
            case 'textarea':
                $finalLabel = '<label for="'.$fieldID.'" class="'.$labelClass.'">'.$displayName.'</label>';
                $finalContent = '<textarea name="' . $inputName . '" id="'.$fieldID.'" '.$maxLengthAttr.' '.$readOnlyAttr.' '.$placeHolderAttr.' '.$autoCompleteAttr.' '.$autoFocusAttr.' '.$disabledAttr.' class="'.$fieldClass.'" ' . $tabIndexAttr . ' ' . $fieldDataAttrString . ' >'.$value.'</textarea>';
                break;
            case 'wysiwyg':
                $finalLabel = '<label for="'.$fieldID.'" class="'.$labelClass.'">'.$displayName.'</label>';
                $wygBtnData = '';
                if($wygBtnHTML){
                    $wygBtnData .= 'data-wyg-html="true" ';
                }
                if($wygBtnUndo){
                    $wygBtnData .= 'data-wyg-undo="true" ';
                }
                if($wygBtnFormat){
                    $wygBtnData .= 'data-wyg-format="true" ';
                }
                if($wygBtnBold){
                    $wygBtnData .= 'data-wyg-bold="true" ';
                }
                if($wygBtnItalic){
                    $wygBtnData .= 'data-wyg-italic="true" ';
                }
                if($wygBtnUnderline){
                    $wygBtnData .= 'data-wyg-underline="true" ';
                }
                if($wygBtnStrike){
                    $wygBtnData .= 'data-wyg-strike="true" ';
                }
                if($wygBtnSubscript){
                    $wygBtnData .= 'data-wyg-subscript="true" ';
                }
                if($wygBtnSuperscript){
                    $wygBtnData .= 'data-wyg-superscript="true" ';
                }
                if($wygBtnLink){
                    $wygBtnData .= 'data-wyg-link="true" ';
                }
                if($wygBtnJustify){
                    $wygBtnData .= 'data-wyg-justify="true" ';
                }
                if($wygBtnLists){
                    $wygBtnData .= 'data-wyg-lists="true" ';
                }
                if($wygBtnRule){
                    $wygBtnData .= 'data-wyg-rule="true" ';
                }
                if($wygBtnCode){
                    $wygBtnData .= 'data-wyg-code="true" ';
                }
                if($wygBtnTable){
                    $wygBtnData .= 'data-wyg-table="true" ';
                }
                if($wygBtnUnformat){
                    $wygBtnData .= 'data-wyg-unformat="true" ';
                }
                if($wygBtnFullscreen){
                    $wygBtnData .= 'data-wyg-fullscreen="true" ';
                }
                $finalContent = '<textarea name="' . $inputName . '" id="'.$fieldID.'" '.$maxLengthAttr.' '.$readOnlyAttr.' '.$placeHolderAttr.' '.$autoCompleteAttr.' '.$autoFocusAttr.' '.$disabledAttr.' class="wysiwyg autosizeOff ' . $fieldClass . '" ' . $wygBtnData . ' ' . $fieldDataAttrString . ' >'.$value.'</textarea>';
                break;
            case 'url':
                $finalLabel = '<label for="'.$fieldID.'" class="'.$labelClass.'">'.$displayName.'</label>';
                $finalContent = '<input type="url" name="' . $inputName . '" id="'.$fieldID.'" value="'.$value.'" '.$maxLengthAttr.' '.$readOnlyAttr.' '.$placeHolderAttr.' '.$autoCompleteAttr.' '.$autoFocusAttr.' '.$disabledAttr.' class="'.$fieldClass.'" ' . $tabIndexAttr . ' ' . $fieldDataAttrString . ' />';
                break;
            case 'file':
                $finalLabel = '<label for="'.$fieldID.'" class="'.$labelClass.'">'.$displayName.'</label>';
                $finalContent = '<input type="file" name="' . $inputName . '" id="'.$fieldID.'" value="'.$value.'" '.$readOnlyAttr.' '.$placeHolderAttr.' '.$disabledAttr.' class="'.$fieldClass.'" ' . $tabIndexAttr . ' ' . $fieldDataAttrString . ' />';
                break;
            case 'signature':
                $imgPath = NULL;
                $unalteredName = $inputName . '_unaltered';
                $unaltered = filter_input(INPUT_POST, $unalteredName);
                $printNameFieldName = $inputName . '_print_name';
                $printNameVal = filter_input(INPUT_POST, $printNameFieldName);
                /* @var $signatureFile AbstractFile */
                if((!$this->wasSubmitted() || $unaltered) && !is_null($signatureFile) && is_a($signatureFile, 'AbstractFile') && $signatureFile->isImage()){
                    $printNameVal = $signatureFile->getProperty('file_signature.print_name');
                    $imgPath = $signatureFile->getFileURL();
                    /*
                    $imgExt = $signatureFile->getExtension();
                    $imgMimeType = File::getMimeTypeFromExtension($imgExt);
                    $signatureImgTypeValue = $imgMimeType . ';base64';
                    $imgData = file_get_contents($imgPath);
                    $signatureImgDataValue = base64_encode($imgData);
                     */
                    $value = 1;
                }
                $imgDataName = $inputName . '_img_data';
                if($this->wasSubmitted()){
                    $imgDataVal = htmlentities(filter_input(INPUT_POST, $imgDataName));
                } else {
                    $imgDataVal = $signatureImgDataValue;
                }
                $imgTypeName = $inputName . '_img_type';
                if($this->wasSubmitted()){
                    $imgTypeVal = htmlentities(filter_input(INPUT_POST, $imgTypeName));
                } else {
                    $imgTypeVal = $signatureImgTypeValue;
                }
                $finalLabel = '<label for="'.$fieldID.'" class="'.$labelClass.'">'.$displayName.'</label>';
                $finalContent = '<div class="jsignature_wrap">';
                $finalContent .= '<div class="jsignature_signarea"></div>';
                $finalContent .= '<div class="jsignature_imgarea"></div>';
                $finalContent .= '<textarea class="jsignature_imgdata" name="' . $imgDataName . '" data-img-path="' . $imgPath . '">' . $imgDataVal . '</textarea>';
                if(!empty($imgPath)){
                    $finalContent .= '<input class="jsignature_unaltered" value="1" type="hidden" name="' . $unalteredName . '"/>';
                }
                $finalContent .= '<input class="jsignature_imgtype" name="' . $imgTypeName . '" value="' . $imgTypeVal . '">';
                $finalContent .= '<div class="' . $signatureBtnClass . ' btn_jsignature_rewrite">' . $signatureRewriteLabel . '</div>';
                $finalLabel = '<label for="'.$fieldID.'" class="'.$labelClass.'">'.$displayName.'</label>';
                if($value == 1){
                    $optChk = 'checked="checked"';
                } else {
                    $optChk = '';
                }
                if($readOnly){
                    $signatureCheckboxClass .= ' read_only';
                    $signaturePrintNameClass .= ' read_only';
                }
                
                if($signaturePrintName){
                    if($signaturePrintNameRequired){
                        $signaturePrintNameLabel .= '*';
                    }
                    $finalContent .= '<br/>';
                    $finalContent .= '<div class="' . $signaturePrintNameClass . '">';
                    $printNameInputClass = '';
                    if(!$this->getFieldError($printNameFieldName)){
                        $printNameInputClass = 'ignore_error_style';
                    }
                    $finalContent .= '<input class="' . $printNameInputClass . ' print_name" type="text" value="' . $printNameVal . '" name="' . $printNameFieldName . '" placeHolder="' . $signaturePrintNameLabel . '"/>';
                    $finalContent .= '</div>';
                }
                
                $finalContent .= '<div class="' . $signatureCheckboxWrapClass . '">';
                $finalContent .= '<div class="options_wrap">
                <label><input type="checkbox" name="' . $inputName . '" id="'.$fieldID.'" value="1" '.$optChk.' '.$readOnlyAttr.' '.$disabledAttr.' class="'.$signatureCheckboxClass.'"/><span class="checkbox_label">' . $signatureCheckboxLabel . '</span></label>
                </div>
                ';
                $finalContent .= '</div>';
                
                
                $finalContent .= '</div>';
                break;
            case 'recaptcha':
                GI_View::setRecaptchaUsed(true);
                $reCapKey = ProjectConfig::getReCaptchaKey();
                if(empty($reCapKey)){
                    $this->addFormError('<mark>RE_CAPTCHA_KEY</mark> is not defined.');
                }
                $reCapSecret = ProjectConfig::getReCaptchaSecret();
                if(empty($reCapSecret)){
                    $this->addFormError('<mark>RE_CAPTCHA_SECRET</mark> is not defined.');
                }
                $finalLabel = '<label for="'.$fieldID.'" class="'.$labelClass.'">'.$displayName.'</label>';
                $finalContent = '<div class="g-recaptcha" data-sitekey="' . $reCapKey . '"></div>';
                break;
            case 'colour':
            case 'color':
                $finalLabel = '<label for="'.$fieldID.'" class="'.$labelClass.'">' . $displayName . '</label>';
                
                $colourOptions = '';
                if(!empty($pickerClass)){
                    $colourOptions .= 'data-wcp-cssClass="' . $pickerClass . '" ';
                }
                
                $colourOptions .= 'data-wcp-hideKeyboard="' . $hideKeyboard . '" ';
                $colourOptions .= 'data-wcp-layout="' . $pickerLayout . '" ';
                if(!empty($pickerSliders)){
                    $colourOptions .= 'data-wcp-sliders="' . $pickerSliders . '" ';
                }
                $colourOptions .= 'data-wcp-snap="' . $pickerSnap . '" ';
                
                $finalContent = '<input type="text" name="' . $inputName . '" id="' . $fieldID . '" value="' . $value . '" class="colour_picker_field ' . $fieldClass . '" ' . $maxLengthAttr . ' '.$readOnlyAttr . ' '.$placeHolderAttr . ' ' . $autoCompleteAttr . ' ' . $autoFocusAttr . ' ' . $disabledAttr . ' ' . $colourOptions . ' ' . $tabIndexAttr . ' ' . $fieldDataAttrString . ' />';
                break;
            case 'otp':
                $finalLabel = '<label for="'.$fieldID.'" class="'.$labelClass.'">'.$displayName.'</label>';
                $finalContent = '<input type="text" name="' . $inputName . '" id="'.$fieldID.'" value="'.$value.'" class="otp_field '.$fieldClass.'" '.$maxLengthAttr.' '.$readOnlyAttr.' '.$placeHolderAttr.' '.$autoCompleteAttr.' '.$autoFocusAttr.' '.$disabledAttr.' ' . $tabIndexAttr . ' ' . $fieldDataAttrString . ' />';
                break;
        }
        if($type=='hidden'){
            if($showError && isset($this->fieldErrors[$errorRefName]) && !empty($this->fieldErrors[$errorRefName]['errorText'])){
                $field = '<div class="'.$formElementClass.'">'
                        . $finalContent
                        . '</div>'
                        . '<div class="field_error">' . $this->fieldErrors[$errorRefName]['errorText'] . '</div>';
            } else {
                $field = $finalContent;
            }
        } else {
            $formElementDataAttrString = '';
            if(!empty($formElementDataAttrs)){
                foreach($formElementDataAttrs as $dataAttr => $dataVal){
                    $formElementDataAttrString .= ' data-' . $dataAttr . '="' . $dataVal . '"';
                }
            }
            $field = '<div class="'.$formElementClass.' ' . $type . '_element" id="'.$formElementID.'" ' . $formElementDataAttrString . '>';
            if($showLabel && !empty($finalLabel)){
                $field .= $finalLabel;
            }
            if(!empty($preHTML) || !empty($postHTML)){
                $fieldContentClass .= ' flex_row';
            }
            $hiddenDescString = '';
            if(!empty($hiddenDesc) || !empty($hiddenDescTitle)){
                $fieldContentClass .= ' has_hidden_desc';
                $hiddenDescString .= '<span class="show_hidden_desc">';
                    $hiddenDescString .= GI_StringUtils::getSVGIcon('question', '16px', '16px');
                $hiddenDescString .= '</span>';
                $hiddenDescString .= '<div class="hidden_desc_wrap">';
                    $hiddenDescString .= '<span class="hide_hidden_desc">' . GI_StringUtils::getSVGIcon('close', '16px', '16px') . '</span>';
                    if(!empty($hiddenDescTitle)){
                        $hiddenDescString .= '<span class="hidden_desc_title">' . $hiddenDescTitle . '</span>';
                    }
                    if(!empty($hiddenDesc)){
                        $hiddenDescString .= '<span class="hidden_desc">' . $hiddenDesc . '</span>';
                    }
                $hiddenDescString .= '</div>';
            }
            
            $field .= '<div class="'.$fieldContentClass.'">';
                if(!empty($preHTML)){
                    $field .= '<span class="pre_field_html">' . $preHTML . '</span>';
                }
                if(!empty($hiddenDesc) || !empty($hiddenDescTitle)){
                    $field .= '<span class="show_hidden_desc">';
                        $field .= GI_StringUtils::getSVGIcon('question', '16px', '16px');
                    $field .= '</span>';
                    $field .= '<div class="hidden_desc_wrap">';
                        $field .= '<span class="hide_hidden_desc">' . GI_StringUtils::getSVGIcon('close', '16px', '16px') . '</span>';
                        if(!empty($hiddenDescTitle)){
                            $field .= '<span class="hidden_desc_title">' . $hiddenDescTitle . '</span>';
                        }
                        if(!empty($hiddenDesc)){
                            $field .= '<span class="hidden_desc">' . $hiddenDesc . '</span>';
                        }
                    $field .= '</div>';
                }
                $field .= $finalContent;
                if(!empty($postHTML)){
                    $field .= '<span class="post_field_html">' . $postHTML . '</span>';
                }
            if($showDescription && !empty($description) && ($hideDescOnError && !isset($this->fieldErrors[$errorRefName]) || !$hideDescOnError || $hideDescOnError && empty($this->fieldErrors[$errorRefName]['errorText']))){ 
                $field .= '<div class="'.$fieldDescriptionClass.'">'.$description.'</div>';
            }
            $field .= '</div>';
            if($showError && isset($this->fieldErrors[$errorRefName]) && !empty($this->fieldErrors[$errorRefName]['errorText'])){
                $field .= '<div class="field_error">'.$this->fieldErrors[$errorRefName]['errorText'].'</div>';
            }
            $field .= '</div>';
        }
        return $field;
    }
    public function getForm($btnText = '', $makeForm = true, $method = 'post', $formAction = ''){
        if($makeForm){
            $this->addField($this->formID,'hidden',array(
                'value'=>true
            ));
        }
        if(empty($btnText) && !is_null($btnText)){
            $btnText = $this->btnText;
        }
        $enctype = '';
        if($this->fileField) $enctype = 'enctype="multipart/form-data"';
        if(empty($formAction)){
            $formAction = $this->formAction;
        }
        $formActionAttr = '';
        if(!empty($formAction)){
            $formActionAttr = 'action="' . $formAction . '"';
        }
        $formTargetAttr = '';
        if(!empty($this->formTarget)){
            $formTargetAttr = 'target="' . $this->formTarget . '"';
        }
        $form = '';
        if($makeForm){
            $formClass = '';
            if (!empty($this->formClass)) {
                $formClass = 'class="' . implode(' ', $this->formClass) . '"';
            }
            $form = '<form id="' . $this->formID . '" method="' . $method . '" ' . $formActionAttr . ' ' . $enctype . ' ' . $formClass . ' ' . $formTargetAttr . '>';
        }
        if($this->botValidation){
            $form .= $this->getField('email', 'email', array(
                'displayName' => 'Email',
                'autoComplete' => false,
                'formElementClass' => 'bval'
            ));
        }
        $fieldsetOpen = false;
        $stepOpen = false;
        $stepWrapOpen = false;
        foreach($this->formItems as $itemInfo){
            $itemType = $itemInfo['type'];
            $itemSettings = $itemInfo['settings'];
            switch($itemType){
                case 'field':
                    $fieldName = $itemSettings['name'];
                    unset($itemSettings['name']);
                    $fieldType = $itemSettings['type'];
                    unset($itemSettings['type']);
                    $form .= $this->getField($fieldName, $fieldType, $itemSettings);
                    break;
                case 'fieldset':
                    $fieldsetOpen = true;
                    $fieldsetName = $itemSettings['name'];
                    $fieldsetClass = '';
                    if(isset($itemSettings['class'])){
                        $fieldsetClass = $itemSettings['class'];
                    }
                    $form .= '<fieldset class="' . $fieldsetClass . '"><legend>' . $fieldsetName . '</legend>';
                    break;
                case 'endFieldset':
                    $fieldsetOpen = false;
                    $form .= '</fieldset>';
                    break;
                case 'step':
                    $stepOpen = true;
                    $stepTitle = $itemSettings['title'];
                    $stepRef = $itemSettings['ref'];
                    $form .= '<div class="wiz_step" data-ref="' . $stepRef . '" id="' . $this->formID . '_' . $stepRef . '"><h3 class="wiz_step_title">' . $stepTitle . '</h3>';
                    break;
                case 'endStep':
                    $stepOpen = false;
                    $form .= '</div>';
                    break;
                case 'stepWrap':
                    $stepWrapOpen = true;
                    $wizardStyle = $itemSettings['wizardStyle'];
                    $stepWrapClass = '';
                    $wizardStyleAttr = 0;
                    if($wizardStyle){
                        $stepWrapClass .= 'wizard';
                        $wizardStyleAttr = 1;
                    }
                    $startStep = $itemSettings['startStep'];
                    $startStepAttr = '';
                    if(!empty($startStep)){
                        $startStepAttr = 'data-start-step="' . $startStep . '"';
                    }
                    $form .= '<div class="wiz_step_wrap ' . $stepWrapClass . '" data-wizard-style="' . $wizardStyleAttr . '" ' . $startStepAttr . ' >';
                    break;
                case 'endStepWrap':
                    $stepWrapOpen = false;
                    $form .= '</div>';
                    break;
                default:
                    $form .= $itemSettings['content'];
                    break;
            }
        }
        if($fieldsetOpen) $form .= '</fieldset>';
        if($stepOpen) $form .= '</div>';
        if($stepWrapOpen) $form .= '</div>';
        if(!empty($btnText)) $form .= '<input type="submit" value="'.$btnText.'" formnovalidate="formnovalidate" />';
        if($makeForm) $form .= '</form>';
        $errors = $this->getFormErrors();
        $form = $errors.$form;
        return $form;
    }
    public function validate(){
        if($this->botValidation){
            $email = filter_input(INPUT_POST, 'email');
            if(!empty($email)){
                $this->addFieldError('email', 'bot_detected', 'There was a problem with your form submission.');
                return false;
            }
        }
        
        $tmpArrayFieldKeys = array();
        
        foreach($this->formItems as $itemInfo){
            $itemType = $itemInfo['type'];
            $itemSettings = $itemInfo['settings'];
            if($itemType=='field'){
                $fieldType = $itemSettings['type'];
                $fieldName = $itemSettings['name'];
                $cleanName = preg_replace('/\[(.*)\]/', '', $fieldName);
                $errorRefName = $cleanName;
                $postArray = false;
                if (strpos($fieldName, '[') !== false) {
                    $postArray = true;
                    if(!isset($tmpArrayFieldKeys[$cleanName])){
                        $tmpArrayFieldKeys[$cleanName] = 0;
                    }
                    $postArrayKey = $tmpArrayFieldKeys[$cleanName];
                    $tmpArrayFieldKeys[$cleanName]++;
                    $errorRefName = $cleanName . '_' . $postArrayKey;
                }
                
                $fieldValue = '';
                if(isset($_POST[$cleanName])){
                    if(is_array($_POST[$cleanName])){
                        $postValue = filter_input(INPUT_POST, $cleanName, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
                        $fieldValue = $postValue;
                        if($postArray){
                            $fieldValue = NULL;
                            if(isset($postValue[$postArrayKey])){
                                $fieldValue = $postValue[$postArrayKey];
                            }
                        }
                    } else {
                        $postValue = filter_input(INPUT_POST, $cleanName);
                        $fieldValue = $postValue;
                    }
                }
                
                if($fieldType=='file') $fieldValue = $_FILES[$cleanName]['name'];
                $required = false;
                if(isset($itemSettings['required']) && $itemSettings['required']){
                    $required = true;
                }
                $disabled = false;
                if(isset($itemSettings['disabled']) && $itemSettings['disabled']){
                    $disabled = true;
                }
                switch($fieldType){                    
                    case 'alarm':
                    case 'time':
                    case 'date':
                    case 'event':
                    case 'datetime':
                    case 'reminder':
                        if(empty($fieldValue) && $fieldValue!=="0" && $required && !$disabled){
                            $this->addFieldError($errorRefName,'required','Required field.');
                        } elseif(!empty($fieldValue)) {
                            try {
                                new DateTime($fieldValue);
                            } catch (Exception $ex){
                                $this->addFieldError($errorRefName,'invalid','Invalid '.$fieldType.' format.');
                            }
                        }
                        break;                       
                    case 'hidden':                        
                    case 'password':                        
                    case 'phone':
                    case 'text':
                    case 'textarea':
                    case 'wysiwyg':
                    case 'url':
                    case 'radio':
                    case 'select':
                    case 'tag':
                        if(empty($fieldValue) && $fieldValue!=="0" && $required && !$disabled) $this->addFieldError($errorRefName,'required','Required field.');
                        break;
                    case 'checkbox':
                        if(empty($fieldValue) && $fieldValue!=="0" && $required && !$disabled){
                            $this->addFieldError($errorRefName,'required','Required field.');
                        } elseif((isset($itemSettings['maxSelections']) && $itemSettings['maxSelections'] > 0) || (isset($itemSettings['minSelections']) && $itemSettings['minSelections'] > 0)){
                            if(isset($itemSettings['maxSelections'])){
                                $maxSelections = $itemSettings['maxSelections'];
                                if(count($fieldValue) > $maxSelections){
                                    $selTerm = 'selections';
                                    if($maxSelections == 1){
                                        $selTerm = 'selection';
                                    }
                                    $this->addFieldError($errorRefName,'invalid','A maximum of <b>' . $maxSelections . '</b> ' . $selTerm . ' can be made.');
                                }
                            }
                            
                            if(isset($itemSettings['minSelections'])){
                                $minSelections = $itemSettings['minSelections'];
                                if(count($fieldValue) < $minSelections){
                                    $selTerm = 'selections';
                                    if($minSelections == 1){
                                        $selTerm = 'selection';
                                    }
                                    $this->addFieldError($errorRefName,'invalid','A minimum of <b>' . $minSelections . '</b> ' . $selTerm . ' must be made.');
                                }
                            }
                        }
                        break;
                    case 'autocomplete':
                        if($required && !$disabled){
                            if(isset($itemSettings['autocompRemFull']) && !$itemSettings['autocompRemFull']){
                                $autoCleanName = $cleanName . '_autocomp';
                                if(is_array($_POST[$autoCleanName])){
                                    $autoPostVal = filter_input(INPUT_POST, $autoCleanName, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
                                    $fieldAutoCompValue = $autoPostVal;
                                    if($postArray){
                                        $fieldAutoCompValue = $autoPostVal[$postArrayKey];
                                    }
                                } else {
                                    $autoPostVal = filter_input(INPUT_POST, $autoCleanName);
                                    $fieldAutoCompValue = $autoPostVal;
                                }
                                
                                if(empty($fieldValue) && $fieldValue!=="0" && empty($fieldAutoCompValue) && $fieldAutoCompValue!=="0") $this->addFieldError($errorRefName,'required','Required field.');
                            } else {
                                if(empty($fieldValue) && $fieldValue!=="0") $this->addFieldError($errorRefName,'required','Required field.');
                            }
                        }
                        break;
                    case 'id':
                    case 'integer_pos':
                        if(empty($fieldValue) && $fieldValue!=="0" && $required && !$disabled){
                            $this->addFieldError($errorRefName,'required','Required field.');
                        } elseif(($fieldValue!=="0" && (!is_numeric($fieldValue) || $fieldValue<0 || ($fieldValue/(int)$fieldValue)!=1)) && !empty($fieldValue)){
                            $this->addFieldError($errorRefName,'invalid','Must be a positive integer or zero.');
                        }
                        break;
                    case 'integer':
                    case 'integer_large':
                        if(empty($fieldValue) && $fieldValue!=="0" && $required && !$disabled){
                            $this->addFieldError($errorRefName,'required','Required field.');
                        } elseif(!empty($fieldValue) && (!is_numeric($fieldValue) || ($fieldValue/(int)$fieldValue)!=1)){
                            $this->addFieldError($errorRefName,'invalid','Must be an integer.');
                        }
                        break;
                    case 'decimal':
                    case 'money':
                    case 'money_rate':
                    case 'percentage':
                        if(empty($fieldValue) && $fieldValue!=="0" && $required && !$disabled){
                            $this->addFieldError($errorRefName,'required','Required field.');
                        } elseif(!is_numeric($fieldValue) && !empty($fieldValue)){
                            $this->addFieldError($errorRefName,'invalid','Must be numeric.');
                        }
                        break;
                    case 'dropdown':
                        if((empty($fieldValue) || $fieldValue=='NULL') && $required && !$disabled) $this->addFieldError($errorRefName,'required','Required field.');
                        break;   
                    case 'onoff':
                    case 'signature':
                        if(!$fieldValue && $required && !$disabled) $this->addFieldError($errorRefName,'required','Required field.');
                        
                        if(isset($itemSettings['signaturePrintNameRequired']) && $itemSettings['signaturePrintNameRequired']){
                            $printNameFieldName = $cleanName . '_print_name';
                            $printNameVal = filter_input(INPUT_POST, $printNameFieldName);
                            if(empty($printNameVal)){
                                $this->addFieldError($printNameFieldName,'required','Required field.');
                            }
                        }
                        break;
                    case 'email':
                        if(empty($fieldValue) && $required && !$disabled){
                            $this->addFieldError($errorRefName,'required','Required field.');
                        } elseif(!empty($fieldValue)) {
                            if(!preg_match("/^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,})$/", $fieldValue)){
                                $this->addFieldError($errorRefName,'invalid','Must be a valid email address.');
                            }
                        }
                        break;
                    case 'file':
                        if(empty($fieldValue) && $required && !$disabled){
                            $this->addFieldError($errorRefName,'required','Required field.');
                        } elseif($required && !$disabled) {
                            $allowedTypes = $itemSettings['fileTypes'];
                            $fileName = basename($_FILES[$cleanName]['name']);
                            $fileType = substr($fileName,strrpos($fileName, '.') + 1);
                            if(!in_array($fileType,$allowedTypes)) $this->addFieldError($errorRefName,'invalid','Invalid file type.');
                        }
                        break;
                    case 'recaptcha':
                        $reCaptcha = filter_input(INPUT_POST, 'g-recaptcha-response');
                        if(empty($reCaptcha)){
                            $this->addFieldError($errorRefName, 'required', 'Please confirm you are not a robot.');
                        } else {
                            $curl = curl_init();
                            curl_setopt($curl, CURLOPT_POST, 1);
                            curl_setopt($curl, CURLOPT_POSTFIELDS, array(
                                'secret' => ProjectConfig::getReCaptchaSecret(),
                                'response' => $reCaptcha
                            ));

                            curl_setopt($curl, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
                            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

                            $robotResonse = curl_exec($curl);

                            curl_close($curl);
                            $robotData = json_decode($robotResonse, true);
                            
                            if(!$robotData['success']){
                                $captchaErrors = array(
                                    'missing-input-secret' => 'The secret parameter is missing.',
                                    'invalid-input-secret' => 'The secret parameter is invalid or malformed.',
                                    'missing-input-response' => 'The response parameter is missing.',
                                    'invalid-input-response' => 'The response parameter is invalid or malformed.',
                                    'bad-request' => 'The request is invalid or malformed.'
                                );
                                $errorPosted = false;
                                if(isset($robotData['error-codes'])){
                                    foreach($robotData['error-codes'] as $errorCode){
                                        if(isset($captchaErrors[$errorCode])){
                                            $errorPosted = true;
                                            $this->addFieldError($errorRefName, 'required', $captchaErrors[$errorCode]);
                                        }
                                    }
                                }
                                if(!$errorPosted){
                                    $this->addFieldError($errorRefName, 'required', 'There was an error with the captcha.');
                                }
                            }
                        }
                        break;
                    case 'otp':
                        if(empty($fieldValue) && $required && !$disabled){
                            $this->addFieldError($errorRefName,'required','Required field.');
                        } elseif(!empty($fieldValue)) {
                            $otpValidValue = SessionService::getValue('otp_' . $this->getFormId());
                            if(strtolower($fieldValue) !== strtolower($otpValidValue)){
                                $this->addFieldError($errorRefName,'invalid','Invalid code.');
                            }
                        }
                        break;
                }
            }
        }
        if($this->fieldErrorCount()){
            return false;
        } else {
            return true;
        }
    }
    
    public function display($btnText = 'Submit', $makeForm = true, $method = 'post', $action = ''){
        echo $this->getForm($btnText, $makeForm, $method, $action);
        return true;
    }
    
    public function addDefaultCurrencyDropdownOrField($value, $fieldName = 'currency_id', $overWriteSettings = array(), $columnKey = 'id'){
        if (ProjectConfig::getHasMultipleCurrencies()) {
            if(empty($value)){
                $defaultRef =  ProjectConfig::getDefaultCurrencyRef();
                if($columnKey == 'ref'){
                    $value = $defaultRef;
                } else {
                    $defaultCur = CurrencyFactory::getModelByRef($defaultRef);
                    if($defaultCur){
                        $value = $defaultCur->getProperty($columnKey);
                    }
                }
            }
            $fieldSettings = static::overWriteSettings(array(
                'options' => CurrencyFactory::getOptionsArray('name'),
                'displayName' => 'Currency',
                'required' => true,
                'hideNull' => true,
                'value' => $value
            ), $overWriteSettings);

            $this->addField($fieldName, 'dropdown', $fieldSettings);
        } else {
            $this->addDefaultCurrencyField($value, $fieldName, $columnKey);
        }
    }
    
    public function addDefaultCurrencyField($value, $fieldName = 'currency_id', $columnKey = 'id') {
        if(empty($value)){
            $defaultRef =  ProjectConfig::getDefaultCurrencyRef();
            if($columnKey == 'ref'){
                $value = $defaultRef;
            } else {
                $defaultCur = CurrencyFactory::getModelByRef($defaultRef);
                if($defaultCur){
                    $value = $defaultCur->getProperty($columnKey);
                }
            }
        }
        $this->addField($fieldName, 'hidden', array(
            'value' => $value
        ));
    }
    
    /**
     * @deprecated - TODO - remove
     * @param AbstractTaxRegion[] $taxRegions
     * @param array() $overWriteSettings
     */
    public function addTaxSelectorField($taxRegions = array(), $fieldName = 'taxes', $overWriteSettings = array()){
        $taxString = 'No taxes';
        $taxRegionIds = array();
        if(!empty($taxRegions)){
            $taxTitles = array();
            foreach ($taxRegions as $taxRegion) {
                $taxRegionIds[] = $taxRegion->getId();
                $taxTitles[] = $taxRegion->getTaxTitle();
            }
            $taxString = implode(', ', $taxTitles);
        }
        
        $regionCode = ProjectConfig::getDefaultRegionCode();
        if(isset($overWriteSettings['regionCode']) && !empty($overWriteSettings['regionCode'])){
            $regionCode = $overWriteSettings['regionCode'];
        }
        $countryCode = ProjectConfig::getDefaultCountryCode();
        if(isset($overWriteSettings['countryCode']) && !empty($overWriteSettings['countryCode'])){
            $countryCode = $overWriteSettings['countryCode'];
        }
        $taxRegionOptions = TaxRegionFactory::getTaxRegionOptionsByCodes($countryCode, $regionCode);
        
        $fieldSettings = static::overWriteSettings(array(
            'displayName' => 'Taxes',
            'showLabel' => false,
            'options' => $taxRegionOptions,
            'value' => $taxRegionIds,
            'formElementClass' => 'list_options',
            'fieldClass' => ''
        ), $overWriteSettings);
        
        $fieldTitle = $fieldSettings['displayName'];
        $fieldSettings['fieldClass'] .= ' update_tax_modal_preview';
        $fieldSettings['formElementClass'] .= ' tax_options';
        if($this->wasSubmitted()){
            $fieldSettings['formElementClass'] .= ' submitted';
        }
        
        $this->addHTML('<div class="form_element show_content_in_modal">');
            $this->addHTML('<label class="main">' . $fieldTitle . '</label>');
            $this->addHTML('<div class="modal_content">');
                $this->addHTML('<h3 class="main_head">' . $fieldTitle . '</h3>');
                $this->addHTML('<div class="content_padding">');
                $this->addField($fieldName, 'checkbox', $fieldSettings);
                $this->addHTML('<div class="wrap_btns"><span class="other_btn close_gi_modal gray">Close</span></div>');
                $this->addHTML('</div>');
            $this->addHTML('</div>');
            $this->addHTML('<div class="modal_preview inline_block" title="Edit ' . $fieldTitle . '">');
                $this->addHTML('<span class="inline_block tax_string">' . $taxString . '</span>');
                $this->addHTML(' <span class="icon edit gray inline_block"></span>');
            $this->addHTML('</div>');
        $this->addHTML('</div>');
    }
    
    public static function overWriteSettings($defaultFieldSettings, $fieldSettings = array()){
        foreach($fieldSettings as $setting => $value){
            $defaultFieldSettings[$setting] = $value;
        }
        return $defaultFieldSettings;
    }

    public function addTaxField($fieldName = 'tax_code_qb_id', $overWriteSettings = array(), $date = NULL, $ratesType = 'sales') {
        if (QBTaxCodeFactory::getTaxingUsesQBAst()) {
            $fieldSettings = static::overWriteSettings(array(
                        'displayName' => 'Tax?',
                        'fieldClass' => 'tax_code_check',
                            ), $overWriteSettings);

            $this->addField($fieldName, 'onoff', $fieldSettings);
            return;
        }
        $options = QBTaxCodeFactory::getOptionsArray($date);
        $optionData = array();
        if (!empty($options)) {
            foreach ($options as $qbId => $taxCodeName) {
                $taxRatesData = QBTaxRateFactory::getRatesDataFromTaxCodeData($qbId, $date, $ratesType);
                $taxRateIdsString = '';
                $taxRateIds = array();
                if (!empty($taxRatesData)) {
                    foreach ($taxRatesData as $taxRateId => $taxRateArray) {
                        $taxRateIds[] = $taxRateId;
                    }
                    $taxRateIdsString = implode(',', $taxRateIds);
                }
                $optionData[$qbId] = array(
                    'rates' => $taxRateIdsString,
                );
            }
        }
        $defaultSettings = array(
            'displayName' => 'Tax Code',
            'options' => $options,
            'optionData' => $optionData,
            'fieldClass' => 'tax_code_select',
            'required'=>true,
        );
        $settings = static::overWriteSettings($defaultSettings, $overWriteSettings);
        $this->addField($fieldName, 'dropdown', $settings);
    }

    public function addSubtotalRow() {
        $this->addHTML('<div class="flex_row subtotal_row">')
                ->addHTML('<div class="flex_col head subtotal_name_col">');
        $this->addHTML('Subtotal');
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col subtotal_value_col">');
        $this->addHTML('$0.00');
        $this->addHTML('</div>')
                ->addHTML('</div>');
    }
    
    public function addTaxTotalRows($date = NULL, $type = 'sales') {
        $rates = QBTaxRateFactory::getQBRatesDataFromTaxCodesData($type, $date);
        if (!empty($rates)) {
            foreach ($rates as $rateId=>$rateArray) {
                $this->addHTML('<div class="flex_row tax_total_row" data-rate-id="'.$rateId.'" data-rate="'.$rateArray['rate'].'">')
                        ->addHTML('<div class="flex_col head tax_name_col">');
                $this->addHTML($rateArray['description']);
                $this->addHTML('</div>')
                        ->addHTML('<div class="flex_col tax_total_col">');
                $this->addHTML('$0.00');
                $this->addHTML('</div>')
                        ->addHTML('</div>');
            }
        }
    }

    public function addTotalRow() {
        $this->addHTML('<div class="flex_row total_row">')
                ->addHTML('<div class="flex_col head total_name_col">');
        $this->addHTML('Total');
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col total_value_col">');
        $this->addHTML('$0.00');
        $this->addHTML('</div>')
                ->addHTML('</div>');
    }
    
    public function addDefaultTaxCodeIdField($value, $fieldName = 'default_tax_code_id', $overWriteSettings = array()) {
        $fieldSettings = array(
            'value'=>$value,
            'fieldClass'=>'default_tax_code_id'
        );
        $settings = static::overWriteSettings($fieldSettings, $overWriteSettings);
        $this->addField($fieldName, 'hidden', $settings);
    }
    
    public function addOTP($otpEmailField = '', $otpPhoneField = '', $readyToSend = false, $fieldName = 'otp', $overWriteSettings = array(), $overWriteMsgTypeSettings = array()){
        if(!$this->wasSubmitted()){
            SessionService::setValue(array('otp_' . $this->getFormId()), NULL);
            SessionService::setValue(array('otp_' . $this->getFormId() . '_sent_to'), NULL);
        }
        $otpTerm = 'OTP (One Time PIN)';
        $emailOptEnabled = false;
        $phoneOptEnabled = false;
        $useUserInfo = false;
        $emailValue = NULL;
        $phoneValue = NULL;
        
        $otpMsgType = 'email';
        
        if(empty($otpEmailField) && empty($otpPhoneField)){
            $useUserInfo = true;
            $user = Login::getUser();
            if($user){
                $phoneValue = $user->getMobileNumber();
                if($phoneValue){
                    $phoneOptEnabled = true;
                    $otpMsgType = 'phone';
                }
                $emailValue = $user->getEmailAddress();
                if($emailValue){
                    $emailOptEnabled = true;
                    $otpMsgType = 'email';
                }
            }
        } else {
            if($otpPhoneField){
                $phoneOptEnabled = true;
                $otpMsgType = 'phone';
            }
            if($otpEmailField){
                $emailOptEnabled = true;
                $otpMsgType = 'email';
            }
        }
        
        if($phoneOptEnabled && !ProjectConfig::isTwilioEnabled()){
            $phoneOptEnabled = false;
        }
        
        if($emailOptEnabled && $phoneOptEnabled){
            $this->openHideDuringOTP($fieldName);
            $otpMsgTypeName = $fieldName . '_msg_type';
            $msgTypeFieldSettings = array(
                'displayName' => 'How would you like to receive your ' . $otpTerm,
                'options' => array(
                    'email' => 'Email',
                    'phone' => 'Text Message'
                ),
                'value' => $otpMsgType,
                'fieldClass' => 'stay_on',
                'required' => true,
                'hiddenDescTitle' => 'What is this?',
                'hiddenDesc' => 'We need to send you a ' . $otpTerm . ' to verify that you’re real and not a bot.'
            );
            $msgTypeSettings = static::overWriteSettings($msgTypeFieldSettings, $overWriteMsgTypeSettings);
            $this->addField($otpMsgTypeName, 'radio', $msgTypeSettings);
            $this->closeHideDuringOTP($fieldName);
        }
        
        $this->openShowDuringOTP($fieldName);
            $this->addHTML('If you’d like to change how you receive your ' . $otpTerm .', <a href="" class="toggle_otp">click here</a>.');
        $this->closeShowDuringOTP($fieldName);
        $togglerValue = 0;
        if($this->wasSubmitted() && $this->validate() && $readyToSend){
            if(isset($otpMsgTypeName)){
                $otpMsgType = filter_input(INPUT_POST, $otpMsgTypeName);
            }
            if(!$useUserInfo){
                $emailValue = filter_input(INPUT_POST, $otpEmailField);
                if($otpMsgType == 'email' && empty($emailValue)){
                    $this->addFieldError($otpEmailField, 'required', 'Required.');
                }
                $phoneValue = filter_input(INPUT_POST, $otpPhoneField);
                if($otpMsgType == 'phone' && empty($phoneValue)){
                    $this->addFieldError($otpPhoneField, 'required', 'Required.');
                }
            }
            if(!$this->fieldErrorCount()){
                $togglerValue = 1;
               
                $sendOTP = false;
                //TODO - REMOVE
                // $generatedOTP = NULL;
//                if(isset($_SESSION['otp_' . $this->getFormId()])){
//                    $generatedOTP = $_SESSION['otp_' . $this->getFormId()];
//                }
                $generatedOTP = SessionService::getValue('otp_' . $this->getFormId());
                if(empty($generatedOTP)){
                    $length = 6;
                    $strict = true;
                    $lowercase = true;
                    $uppercase = false;
                    $numbers = true;
                    $special = false;
                    $limitNumbers = 4;
                    $generatedOTP = GI_StringUtils::generateRandomString($length, $strict, $lowercase, $uppercase, $numbers, $special, $limitNumbers);
                  //  $_SESSION['otp_' . $this->getFormId()] = $generatedOTP;
                    SessionService::setValue(array('otp_' . $this->getFormId()), $generatedOTP);
                    $sendOTP = true;
                }
                $lastSentTo = SessionService::getValue('otp_' . $this->getFormId() . '_sent_to');
                
                $otpResendField = $fieldName . '_resend';
                $this->addField($otpResendField, 'hidden', array(
                    'value' => 0,
                    'clearValue' => true
                ));
                
                $resend = filter_input(INPUT_POST, $otpResendField);
                if($resend){
                    $sendOTP = true;
                }
                $targetContactInfo = NULL;
                switch($otpMsgType){
                    case 'phone':
                        $msgTerm = 'texted';
                        if($sendOTP || $lastSentTo !== $phoneValue){
                            $targetContactInfo = $phoneValue;
                            $message = 'Hello, your ' . $otpTerm . ' is ' . $generatedOTP;
                            $sms = new GI_SMS(GI_SMS::formatNumberE164($phoneValue), $message);
                            if($sms->sendMessage()) {
                                SessionService::setValue('otp_' . $this->getFormId() . '_sent_to', $phoneValue);
                            }
                        }
                        break;
                    case 'email':
                        $msgTerm = 'emailed';
                        if($sendOTP || $lastSentTo !== $emailValue){
                            $targetContactInfo = $emailValue;
                            $emailView = new GenericEmailView();
                            $emailView->addParagraph('Hello,<br/>Your ' . $otpTerm . ' is:<br/><b>' . $generatedOTP . '</b>');
                            $giEmail = new GI_Email();
                            $giEmail->addMandrillTag('otp');
                            $giEmail->addTo($emailValue, $emailValue)
                                    ->setFrom(ProjectConfig::getServerEmailAddr(), ProjectConfig::getServerEmailName())
                                    ->setSubject('Your ' . $otpTerm)
                                    ->useEmailView($emailView);
                            if($giEmail->send()){
                             //   $_SESSION['otp_' . $this->getFormId() . '_sent_to'] = $emailValue;
                                SessionService::setValue('otp_' . $this->getFormId() . '_sent_to', $emailValue);
                            }
                        }
                        break;
                }
                
                if(DEV_MODE || ProjectConfig::getBypassLiveOTP()){
                    $otpAlert = new Alert('Your ' . $otpTerm . ' is <b>' . $generatedOTP . '</b>', 'green');
                    AlertService::addAlert($otpAlert);
                }

                if(empty($targetContactInfo)){
                    $targetContactInfo = $lastSentTo;
                }
                if(empty($targetContactInfo)){
                    $targetContactInfo = '[error]';
                }
                $this->openShowDuringOTP($fieldName);
                $this->addHTML('<div class="alert_message green"><p>Your ' . $otpTerm . ' has been ' . $msgTerm . ' to <b>' . $targetContactInfo . '</b>, enter it below.</p><p class="sml_text">Didn’t receive one? <span class="submit_btn" data-field-name="' . $otpResendField . '" data-field-value="1" tabindex="0">Click here</span> to re-send one.</p></div>');
            
                $otpCheckName = $fieldName . '_check';
                
                $otpCheckVal = filter_input(INPUT_POST, $otpCheckName);
                $required = false;
                if(!is_null($otpCheckVal)){
                    $required = true;
                } else {
                    $this->addFieldError($fieldName . '_prevent', 'invalid', 'Error added to force OTP validation');
                }
                $fieldSettings = array(
                    'displayName' => $otpTerm,
                    'required' => $required,
                    'formElementClass' => 'fake_required',
                    'inputAutoCompleteVal' => 'one-time-code'
                );
                $settings = static::overWriteSettings($fieldSettings, $overWriteSettings);
                $this->addField($fieldName, 'otp', $settings);
                if(!is_null($otpCheckVal)){
                    $this->validate();
                }
                $this->addField($otpCheckName, 'hidden', array(
                    'value' => 1
                ));
                $this->closeShowDuringOTP($fieldName);
                
                $this->validate();
            }
        }
        
        $this->addField($fieldName . '_toggler', 'onoff', array(
            'formElementClass' => 'hide_on_load otp_toggler',
            'fieldClass' => 'checkbox_toggler',
            'value' => $togglerValue,
            'clearValue' => true,
            'useProvidedValueOnClear' => true
        ));
        return $this;
    }
    
    public function openHideDuringOTP($fieldName = 'otp'){
        $togglerGroup = $fieldName . '_toggler';
        $this->addHTML('<div class="checkbox_toggler_element form_element" data-group="' . $togglerGroup . '" data-element="NULL">');
        return $this;
    }
    
    public function closeHideDuringOTP($fieldName = 'otp'){
        $this->addHTML('</div>');
        return $this;
    }
    
    public function openShowDuringOTP($fieldName = 'otp'){
        $togglerGroup = $fieldName . '_toggler';
        $this->addHTML('<div class="checkbox_toggler_element form_element" data-group="' . $togglerGroup . '" data-element="1">');
        return $this;
    }
    
    public function closeShowDuringOTP($fieldName = 'otp'){
        $this->addHTML('</div>');
        return $this;
    }
    
}
