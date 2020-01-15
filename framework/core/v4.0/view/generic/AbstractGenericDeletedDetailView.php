<?php
/**
 * Description of AbstractDeletedDetailView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    2.0.0
 */
abstract class AbstractDeletedDetailView extends GI_View {
    
    /** @var GI_Model */
    protected $model;
    protected $message = '';

    public function __construct(GI_Model $model) {
        parent::__construct();
        $this->model = $model;
        $this->addSiteTitle($this->model->getSpecificTitle());
        $this->addSiteTitle('Deleted');
    }
    
    protected function addMessage(){
        $this->addHTML('<p>' . $this->getMessage() . '</p>');
    }
    
    protected function getMessage(){
        $message = $this->message;
        if(empty($message)){
            $message = '<b>' . $this->model->getSpecificTitle() . '</b> has been deleted and is no longer accessible.';
        }
        return $message;
    }
    
    public function setMessage($message){
        $this->message = $message;
        return true;
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
        $this->addMainTitle($this->model->getSpecificTitle() . ' <span class="sub_status red">Deleted</span>');
        $this->addMessage();
        $this->closeViewWrap();
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }

}
