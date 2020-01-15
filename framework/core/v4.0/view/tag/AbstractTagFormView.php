<?php
/**
 * Description of AbstractTagFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.1
 */
abstract class AbstractTagFormView extends GI_View {
    
    /**
     * @var GI_Form
     */
    protected $form;
    /**
     * @var AbstractTag
     */
    protected $tag;
    protected $formBuilt = false;
    
    public function __construct(GI_Form $form, AbstractTag $tag) {
        parent::__construct();
        $this->form = $form;
        $this->tag = $tag;
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
        $action = 'Edit';
        if (empty($this->tag->getProperty('id'))) {
            $action = 'Add';
        }
        $this->form->addHTML('<h1>'.$action.' '.$this->tag->getViewTitle().'</h1>');
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

    protected function buildFormFooter() {
        $this->form->addHTML('<span class="submit_btn" data-form-id="' . $this->form->getFormId() . '">Submit</span>');
    }

    protected function openViewWrap() {
        $this->addHTML('<div class="content_padding">');
    }
    
    protected function closeViewWrap(){
        $this->addHTML('</div>');
    }
    
    public function buildView() {
        $this->openViewWrap();
        $this->buildForm();
        $this->addHTML($this->form->getForm());
        $this->closeViewWrap();
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}
