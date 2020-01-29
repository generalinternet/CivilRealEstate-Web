<?php
/**
 * Description of AbstractChangeFranchiseFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractFranchiseChangeFormView extends GI_View{
    
    /** @var GI_Form */
    protected $form = NULL;
    /** @var AbstractContactOrgFranchise */
    protected $franchise = NULL;
    protected $franchiseOptions = array();
    
    public function __construct(GI_Form $form, AbstractContactOrgFranchise $franchise = NULL) {
        $this->form = $form;
        $this->franchise = $franchise;
        parent::__construct();
    }
    
    /**
     * @param string[] $options
     */
    public function setFranchiseOptions($options) {
        $this->franchiseOptions = $options;
    }
    
    public function buildForm() {
        $this->buildFormHeader();
        $this->buildFormBody();
        $this->buildFormFooter();
    }
    
    protected function buildFormHeader() {
        $this->form->addHTML('<h1>Act As</h1>');
    }
    
    protected function buildFormBody() {
        $this->addFranchiseField();
    }
    
    protected function buildFormFooter() {
        $this->form->addHTML('<span class="submit_btn" title="Save" tabindex="0">Submit</span>');
    }
    
    protected function addFranchiseField(){
        $val = NULL;
        if (!empty($this->franchise)) {
            $val = $this->franchise->getId();
        }
        $fieldOptions = array(
            'displayName' => 'Franchise',
            'placeHolder' => 'start typing...',
            'value' => $val,
            'options'=>$this->franchiseOptions,
            'formElementClass' => 'autofocus_off'
        );
        if(!Permission::verifyByRef('super_admin')){
            $fieldOptions['hideNull'] = true;
        }
        $this->form->addField('franchise_id', 'dropdown', $fieldOptions);
    }
    
    protected function openViewWrap(){
        $this->addHTML('<div class="content_padding">');
        return $this;
    }
    
    protected function closeViewWrap(){
        $this->addHTML('</div>');
        return $this;
    }
    
    public function buildView() {
        $this->openViewWrap();
        $this->addHTML($this->form->getForm());
        $this->closeViewWrap();
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}
