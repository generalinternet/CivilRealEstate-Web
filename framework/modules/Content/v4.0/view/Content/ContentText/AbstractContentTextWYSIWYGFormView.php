<?php
/**
 * Description of GI_Model
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractContentTextWYSIWYGFormView extends AbstractContentFormView{
    
    protected $wygBtnHTML = true;
    protected $wygBtnUndo = true;
    protected $wygBtnFormat = true;
    protected $wygBtnBold = true;
    protected $wygBtnItalic = true;
    protected $wygBtnUnderline = true;
    protected $wygBtnStrike = true;
    protected $wygBtnSuperscript = false;
    protected $wygBtnSubscript = false;
    protected $wygBtnLink = true;
    protected $wygBtnJustify = false;
    protected $wygBtnLists = true;
    protected $wygBtnRule = false;
    protected $wygBtnCode = false;
    protected $wygBtnTable = false;
    protected $wygBtnUnformat = true;
    protected $wygBtnFullscreen = true;
    
    /**
     * Override buildFormGuts
     */
    public function buildFormGuts() {
        $this->form->addField($this->content->getFieldName('type_ref'), 'hidden', array(
            'value' => $this->content->getTypeRef()
        ));
        
        $this->form->addField($this->content->getFieldName('title'), 'hidden', array(
            'value' => $this->content->getProperty('title')
        ));
        
        $this->form->addField($this->content->getFieldName('ref'), 'hidden', array(
            'value' => $this->content->getProperty('ref')
        ));
                
        $this->form->addField($this->content->getFieldName('content'), 'wysiwyg', array(
            'displayName' => 'Content',
            'placeHolder' => 'Content',
            'value' => $this->content->getProperty('content_text.content'),
            'wygBtnHTML' => $this->wygBtnHTML,
            'wygBtnUndo' => $this->wygBtnUndo,
            'wygBtnFormat' => $this->wygBtnFormat,
            'wygBtnBold' => $this->wygBtnBold,
            'wygBtnItalic' => $this->wygBtnItalic,
            'wygBtnUnderline' => $this->wygBtnUnderline,
            'wygBtnStrike' => $this->wygBtnStrike,
            'wygBtnSuperscript' => $this->wygBtnSuperscript,
            'wygBtnSubscript' => $this->wygBtnSubscript,
            'wygBtnLink' => $this->wygBtnLink,
            'wygBtnJustify' => $this->wygBtnJustify,
            'wygBtnLists' => $this->wygBtnLists,
            'wygBtnRule' => $this->wygBtnRule,
            'wygBtnCode' => $this->wygBtnCode,
            'wygBtnTable' => $this->wygBtnTable,
            'wygBtnUnformat' => $this->wygBtnUnformat,
            'wygBtnFullscreen' => $this->wygBtnFullscreen
        ));
    }
}
