<?php
/**
 * Description of AbstractLoginRegisterView
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    2.0.0
 */
class AbstractLoginRegisterView extends GI_View {
    
    /**
     * @var GI_Form
     */
    protected $form;
    /**
     * @var User
     */
    protected $user = NULL;
    protected $addWrapper = false;

    public function __construct(GI_Form $form, AbstractUser $user) {
        $this->form = $form;
        $this->user = $user;
        parent::__construct();
        $this->buildForm();
    }
    
    public function setAddWrapper($addWrapper){
        $this->addWrapper = $addWrapper;
        return $this;
    }

    protected function buildForm() {
        $formView = $this->user->getFormView($this->form, $this->user);
        $formView->setRegisterForm(true);
        $formView->buildForm();
        $this->form->addHTML('<span class="submit_btn">' . Lang::getString('register') . '</span>');
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
        if($this->addWrapper){
            $this->openViewWrap()
                    ->addHTML('<h1>Register</h1>');
        }
        $this->addHTML($this->form->getForm());
        if($this->addWrapper){
            $this->closeViewWrap();
        }
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }

}
