<?php
/**
 * Description of AbstractDeletedDetailView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    4.0.0
 */
abstract class AbstractDeletedDetailView extends MainWindowView {
    
    /** @var GI_Model */
    protected $model;
    protected $message = '';

    public function __construct(GI_Model $model) {
        parent::__construct();
        $this->model = $model;
        $this->addSiteTitle($this->model->getSpecificTitle());
        $this->addSiteTitle('Deleted');
        $this->setWindowTitle($this->model->getSpecificTitle() . ' <span class="sub_status red">Deleted</span>');
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
    
    public function addViewBodyContent() {
        $this->openPaddingWrap();
        $this->addMessage();
        $this->closePaddingWrap();
    }

}
