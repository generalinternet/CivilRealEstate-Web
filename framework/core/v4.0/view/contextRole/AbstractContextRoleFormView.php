<?php
/**
 * Description of AbstractContextRoleFormView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */

abstract class AbstractContextRoleFormView extends GI_View {
    
    protected $form = NULL;
    protected $contextRole = NULL;
    protected $formBuilt = false;
    protected $contextTitle = 'Context';
    
    public function __construct(GI_Form $form, AbstractContextRole $contextRole) {
        parent::__construct();
        $this->form = $form;
        $this->contextRole = $contextRole;
    }
    
    public function setContextTitle($title) {
        $this->contextTitle = $title;
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
        if (empty($this->contextRole->getId())) {
            $action = 'Add';
        } else {
            $action = 'Edit';
        }
        $this->form->addHTML('<h1>' . $action . ' ' . $this->contextTitle . ' Role</h1>');
    }
    
    protected function buildFormBody() {
        $this->addTitleField();
        $this->addUsersField();
    }

    protected function addTitleField() {
        $this->form->addField('title', 'text', array(
            'value' => $this->contextRole->getProperty('title'),
            'required' => true,
            'displayName' => 'Title'
        ));
    }

    protected function addUsersField() {
        $value = $this->contextRole->getUserIdsString(true);
        $fieldName = 'user_ids';
        $acURLAttrs = array(
            'controller' => 'user',
            'action' => 'autocompUser',
            'autocompField' => $fieldName,
            'ajax' => 1
        );
        $acURL = GI_URLUtils::buildURL($acURLAttrs, false, true);
        $this->form->addField($fieldName, 'autocomplete', array(
            'displayName' => 'User Name(s)',
            'placeHolder' => 'SELECT',
            'autocompURL' => $acURL,
            'autocompMinLength' => 0,
            'autocompMultiple' => true,
            'value' => $value,
            'fieldClass' => 'autofocus_off',
        ));
    }

    protected function buildFormFooter() {
        $this->form->addHTML('<div class="center_btns wrap_btns">');
        $this->addSubmitBtn();
        $this->addCancelBtn();
        $this->form->addHTML('</div>');
    }

    public function addSubmitBtn() {
        $this->form->addHTML('<span class="submit_btn">Save</span>');
    }

    public function addCancelBtn() {
        $this->form->addHTML('<span class="other_btn gray">Cancel</span>');
    }
    
    protected function buildView() {
        $this->openViewWrap();
        $this->buildForm();
        $this->addHTML($this->form->getForm(''));
        $this->closeViewWrap();
    }
    
    protected function openViewWrap() {
        $this->addHTML('<div class="content_padding">');
    }
    
    protected function closeViewWrap() {
        $this->addHTML('</div>');
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}
