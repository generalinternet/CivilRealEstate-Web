<?php
/**
 * Description of AbstractContactCatFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.2
 */
class AbstractContactCatFormView extends GI_View {
    
    /**
     *
     * @var GI_Form 
     */
    protected $form;
    protected $contactCat;
    protected $formBuilt = false;
    protected $viewBuilt = false;
    
    public function __construct(GI_Form $form, AbstractContactCat $contactCat) {
        parent::__construct();
        $this->form = $form;
        $this->contactCat = $contactCat;
    }
    
    public function buildForm() {
        if (!$this->formBuilt) {
            $this->form->addHTML('<div class="contact_cat" id="contact_cat_form_' . $this->contactCat->getTypeRef() . '">');
            $this->buildFormHeader();
            $this->buildFormBody();
            $this->buildFormFooter();
            $this->form->addHTML('</div>');
            $this->formBuilt = true;
        }
    }
    
    protected function buildFormHeader() {
        
    }
    
    protected function buildFormBody() {
        $tagRootType = $this->contactCat->getTypeRef();
        $tagOptionsArray = TagFactory::getTagOptionsArrayByTypeRef($tagRootType);
        //Show only if there are contact tags for other system that doesn't need contact tags
        if (!empty($tagOptionsArray)) {
            $this->form->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
            $this->addContactTagsField();
                $this->form->addHTML('</div>')
                    ->addHTML('<div class="flex_col">')
                    ->addHTML('</div>')
                    ->addHTML('<div class="flex_col">');
                $this->form->addHTML('</div>')
                        ->addHTML('</div>');
        }
    }
    
    protected function buildFormFooter() {
        
    }
    
    public function buildView($fullView = true){
        $this->buildForm();
        if ($fullView) {
            $this->addHTML($this->form->getForm());
        } else {
            $this->form->setBtnText('');
            $this->addHTML($this->form->getForm('', false));
        }
        
        $this->viewBuilt = true;
    }
    
    public function beforeReturningView() {
        if(!$this->viewBuilt){
            $this->buildView();
        }
    }
    public function addContactTagsField($overWriteSettings = array()) {
        $tagRootType = $this->contactCat->getTypeRef();
        $typeTitle = $this->contactCat->getTypeTitle();
        $contact = $this->contactCat->getContact();
        $tagValues = '';
        if (!empty($contact)) {
            $tagValues = implode(',', $contact->getContactTagIdArray($this->form));
        }
        $tagsURL = GI_URLUtils::buildUrl(array(
            'controller' => 'autocomplete',
            'action' => 'tag',
            'valueColumn' => 'id',
            'type' => $tagRootType,
            'autocompField' => 'contact_tag_ids',
            'ajax' => 1
        ), false, true);
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => $typeTitle.' Tags',
            'placeHolder' => $typeTitle.' Tags',
            'autocompURL' => $tagsURL,
            'autocompMinLength' => 0,
            'autocompMultiple' => true,
            'value' => $tagValues,
        ), $overWriteSettings);

        $this->form->addField('contact_tag_ids', 'autocomplete', $fieldSettings);
    }
}
