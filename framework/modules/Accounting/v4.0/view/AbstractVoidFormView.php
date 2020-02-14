<?php
/**
 * Description of AbstractVoidFormView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractVoidFormView extends GI_View {
    
    protected $form;
    protected $model;
    protected $voidError = false;
    protected $formBuilt = false;

    public function __construct($form, GI_Model $model) {
        parent::__construct();
        $this->form = $form;
        $this->model = $model;
    }

    public function buildForm() {
        if (!$this->formBuilt) {
            $this->form->addHTML('<h1>Void ' . $this->model->getSpecificTitle() . '</h1>');
            if ($this->voidError) {
                $this->form->addHTML('<div class="center_btns wrap_btns"><span class="other_btn gray close_gi_modal" tabindex="0" >Cancel</span></div>');
            } else {
                $this->form->addHTML('<p>Are you sure you want to void this <b>' . $this->model->getSpecificTitle() . '</b>?</p>');
                $this->form->addField('void_notes', 'textarea', array(
                    'displayName'=>'Notes'
                ));
                $this->form->addHTML('<div class="center_btns wrap_btns"><span class="submit_btn" tabindex="0" >Void</span><span class="other_btn gray close_gi_modal" tabindex="0" >Cancel</span></div>');
            }
            $this->formBuilt = true;
        }
    }

    protected function openViewWrap() {
        $this->addHTML('<div class="content_padding">');
        return $this;
    }

    protected function closeViewWrap() {
        $this->addHTML('</div>');
        return $this;
    }

    protected function buildView() {
        $this->openViewWrap();
        $this->buildForm();
        $this->addHTML($this->form->getForm());
        $this->closeViewWrap();
    }
    
    public function setVoidError($error){
        $this->voidError = true;
        $this->form->addHTML('<p class="error">'.$error.'</p>');
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }

}
