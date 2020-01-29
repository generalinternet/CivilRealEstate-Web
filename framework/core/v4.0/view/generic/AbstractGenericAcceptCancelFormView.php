<?php
/**
 * Description of AbstractGenericAcceptDenyFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.0
 */
abstract class AbstractGenericAcceptCancelFormView extends GI_View {
    
    protected $form;
    protected $formBuilt = false;
    protected $modalHeader = '';
    protected $header = '';
    protected $message = '';
    protected $submitButtonLabel = 'Submit';
    protected $cancelButtonLabel = 'Cancel';
    protected $cancelButtonURL = NULL;
    protected $cancelButtonClass = 'close_gi_modal';
    protected $cancelButtonOtherAttrs = '';
    
    public function __construct(GI_Form $form) {
        parent::__construct();
        $this->form = $form;
    }
    
    public function setHeaderText($headerText) {
        $this->header = $headerText;
        return $this;
    }
    
    public function setModalHeader($modalHeader){
        $this->modalHeader = $modalHeader;
        return $this;
    }
    
    public function setMessageText($messageText) {
        $this->message = $messageText;
        return $this;
    }
    
    public function setSubmitButtonLabel($submitButtonLabel) {
        $this->submitButtonLabel = $submitButtonLabel;
        return $this;
    }
    
    public function setCancelButtonLabel($cancelButtonLabel) {
        $this->cancelButtonLabel = $cancelButtonLabel;
        return $this;
    }
    
    public function setCancelButtonClass($cancelButtonClass){
        $this->cancelButtonClass = $cancelButtonClass;
        return $this;
    }
    
    public function setCancelButtonURL($cancelButtonURL){
        $this->cancelButtonURL = $cancelButtonURL;
        return $this;
    }
    
    public function setCancelButtonOtherAttrs($otherAttrs){
        $this->cancelButtonOtherAttrs = $otherAttrs;
        return $this;
    }
    
    public function buildForm() {
        if (!$this->formBuilt) {
            $this->form->addHTML('<div class="center_btns wrap_btns">');
            $this->addSubmitBtn();
            $this->addCancelBtn();
            $this->form->addHTML('</div>');
            $this->formBuilt = true;
        }
    }
    
    public function addSubmitBtn(){
        $this->form->addHTML('<span class="submit_btn">'.$this->submitButtonLabel.'</span>');
    }
    
    public function addCancelBtn(){
        $dataURLAttr = '';
        if($this->cancelButtonURL){
            $dataURLAttr = 'data-url="' . $this->cancelButtonURL . '"';
        }
        $this->form->addHTML('<span class="other_btn gray ' . $this->cancelButtonClass . '" ' . $dataURLAttr . ' ' . $this->cancelButtonOtherAttrs . '>'.$this->cancelButtonLabel.'</span>');
    }
    
    public function beforeReturningView() {
        if($this->modalHeader){
            $this->addMainTitle($this->modalHeader);
        }
        $this->buildForm();
        $this->addHTML('<div class="content_padding">');
        $this->addMainTitle($this->header);
        $this->addHTML('<p>' . $this->message . '</p>');
        $this->addHTML($this->form->getForm());
        $this->addHTML('</div>');
    }
}