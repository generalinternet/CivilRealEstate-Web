<?php
/**
 * Description of AbstractContentTextCode
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.1
 */
abstract class AbstractContentTextCode extends AbstractContentText{
    
    /**
     * @return \ContentTextCodeDetailView
     */
    public function getView() {
        $contentView = new ContentTextCodeDetailView($this);
        return $contentView;
    }
    
    public function getViewTitle($plural = true) {
        $title = 'Code';
        return $title;
    }
    
    public function getTitleTag() {
        return 'h6';
    }
    
    /**
     * @param \GI_Form $form
     * @param boolean $buildForm
     * @return \ContentTextCodeFormView
     */
    public function getFormView(\GI_Form $form, $buildForm = true) {
        $contentFormView = new ContentTextCodeFormView($form, $this, $buildForm);
        return $contentFormView;
    }
    
    public function handleFormSubmission(\GI_Form $form) {
        if(parent::handleFormSubmission($form)){
            $language = filter_input(INPUT_POST, $this->getFieldName('language'));
            $this->setProperty('content_text_code.language', $language);
            
            $startingLine = filter_input(INPUT_POST, $this->getFieldName('starting_line'));
            $this->setProperty('content_text_code.starting_line', $startingLine);
            
            $highlightLines = filter_input(INPUT_POST, $this->getFieldName('highlight_lines'));
            $this->setProperty('content_text_code.highlight_lines', $highlightLines);
            
            if($this->save()){
                return true;
            }
        }
        return false;
    }
    
    public function getHighlightLines(){
        $highlightLines = $this->getProperty('content_text_code.highlight_lines');
        $highlightArray = explode(',', $highlightLines);
        $lineNumbers = array();
        foreach($highlightArray as $lineNumber){
            if (strpos($lineNumber, '-') !== false) {
                $numberToNumber = explode('-', $lineNumber);
                for($i=trim($numberToNumber[0]); $i<=trim($numberToNumber[1]); $i++){
                    $lineNumbers[] = $i;
                }
            } else {
                $lineNumbers[] = $lineNumber;
            }
        }
        return $lineNumbers;
    }
    
}
