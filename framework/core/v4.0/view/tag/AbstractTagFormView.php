<?php
/**
 * Description of AbstractTagFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractTagFormView extends MainWindowView {
    
    /** @var GI_Form */
    protected $form;
    /** @var AbstractTag */
    protected $tag;
    protected $formBuilt = false;
    
    public function __construct(GI_Form $form, AbstractTag $tag) {
        parent::__construct();
        $this->form = $form;
        $this->tag = $tag;
        $this->setListBarURL($tag->getListBarURL());
        $action = 'Edit';
        if (empty($this->tag->getId())) {
            $action = 'Add';
        }
        $this->setWindowTitle($action . ' ' . $this->tag->getViewTitle());
    }

    public function buildForm() {
        if (!$this->formBuilt) {
            $this->buildFormHeader();
            $this->buildFormBody();
            $this->buildFormFooter();
            $this->formBuilt = true;
        }
    }

    protected function buildFormHeader() {
        
    }

    protected function buildFormBody() {
        $this->addTitleField();
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addColourField();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->addPositionField();
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
        $this->addParentsField();
    }

    protected function addTitleField() {
        $this->form->addField('title', 'text', array(
            'value' => $this->tag->getProperty('title'),
            'displayName'=>'Title',
            'required' => true
        ));
    }

    protected function addColourField() {
        $this->form->addField('colour', 'colour', array(
            'value' => $this->tag->getProperty('tag.colour')
        ));
    }
    
    protected function addPositionField() {
        $this->form->addField('position', 'integer_pos', array(
            'value'=>$this->tag->getProperty('pos'),
            'displayName'=>'Position',
        ));
    }
    
    protected function addParentsField() {
        $url = GI_URLUtils::buildURL(array(
            'controller' => 'tag',
            'action' => 'autocompTag',
            'ajax' => 1,
            'type' => $this->tag->getTypeRef(),
            'valueColumn' => 'id',
            'autocompField' => 'p_tag_ids',
            'notIds' => $this->tag->getId()
        ));
        $this->form->addField('p_tag_ids', 'autocomplete', array(
            'value' => $this->tag->getPTagString(),
            'displayName' => 'Parent Tags',
            'autocompURL' => $url,
            'autocompMultiple' => true
        ));
    }

    protected function buildFormFooter() {
        $this->form->addHTML('<span class="submit_btn" data-form-id="' . $this->form->getFormId() . '" tabindex="0">Submit</span>');
    }
    
    public function addViewBodyContent() {
        $this->openPaddingWrap();
        $this->buildForm();
        $this->addHTML($this->form->getForm());
        $this->closePaddingWrap();
    }
    
}
