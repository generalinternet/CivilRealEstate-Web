<?php

class StaticErrorView extends GI_View {
    
    protected $controller = '';
    protected $action = '';
    protected $attributes = array();
    protected $errorType = '';
    protected $otherData = array();
    protected $addWrap = true;

    public function __construct($controller, $action, $attributes, $errorType = NULL, $otherData = array()) {
        parent::__construct();
        $this->controller = $controller;
        $this->action = $action;
        $this->attributes = $attributes;
        $this->errorType = $errorType;
        $this->otherData = $otherData;        
        $this->addSiteTitle('Page Not Found');
        $errorCode = GI_URLUtils::getAttribute('errorCode');
        if($errorType == 'error_code' && !empty($errorCode)){
            $this->addSiteTitle($errorCode);
        }
        if (GI_URLUtils::isAJAX() || !ApplicationConfig::isPublic()) {
            $this->setAddWrap(false);
        }
    }
    
    public function setAddWrap($addWrap){
        $this->addWrap = $addWrap;
        return $this;
    }
    
    public function buildView() {
        $this->openViewWrap();

        $this->addHTML('<div class="error-text">');
        $this->addHTML('<p>We were unable to perform the requested action.</p>');
        $this->addHTML('<p>If you need further assistance, please contact your friendly General Internet tech support team.</p>');
        $this->addHTML('<hr/>');
        
        switch($this->errorType){
            case 'controller':                
                $this->addHTML('<p>Controller <b>'.$this->controller.'</b> cannot be found.</p>');
                break;
            case 'action':
                $this->addHTML('<p>Action <b>'.$this->action.'</b> in controller <b>'.$this->controller.'</b> cannot be found.</p>');
                break;
            case 'exception':
                if(isset($this->otherData['exception'])){
                    $exception = $this->otherData['exception'];
                } else {
                    $exception = 'No exception provided.';
                }
                if(!empty($this->controller) && !empty($this->action)){
                    $this->addHTML('<p>Exception caught in action <b>'.$this->action.'</b> of the controller <b>'.$this->controller.'</b>.</p>');
                }
                $this->addHTML('<p><pre>'.$exception.'</pre></p>');
                break;
            case 'error_code':
                // $showErrorCodes = false;
                // if(isset($this->attributes['errorCode']) && !empty($this->attributes['errorCode'])){
                //     $showErrorCodes = false;
                //     $this->addHTML('<p><b>'.$this->attributes['errorCode'].':</b> '.Lang::getError($this->attributes['errorCode']).'</p>');
                // }
                // if(isset($this->attributes['errorMsg']) && !empty($this->attributes['errorMsg'])){
                //     $showErrorCodes = false;
                //     $this->addHTML('<p>'.$this->attributes['errorMsg'].'</p>');
                // } elseif(isset($this->attributes['errorMessage']) && !empty($this->attributes['errorMessage'])){
                //     $showErrorCodes = false;
                //     $this->addHTML('<p>'.$this->attributes['errorMessage'].'</p>');
                // }
                
                // if($showErrorCodes){
                //     $this->addErrorCodeList();
                // }
                
                // if(isset($this->attributes['returnURL']) && !empty($this->attributes['returnURL'])){
                //     $this->addHTML('<a class="ccs_btn" href="'.$this->attributes['returnURL'].'" title="Back"><span class="icon_wrap"><span class="icon arrow_left gray"></span></span><span class="btn_text">Back</span></a>');
                // }
                // break;
            default:
                $this->addHTML('<p>We can’t seem to find the page you were looking for.</p>');
                break;
        }

        $this->addHTML('</div>');
        $this->closeViewWrap();
    }
    
    protected function addErrorCodeList(){
        $errorCodes = Lang::getErrorCodes();
        $this->addHTML('<table class="simple_table">');
        $this->addHTML('<tr>')
                ->addHTML('<th>Code</th>')
                ->addHTML('<th>Title</th>')
                ->addHTML('</tr>');
        foreach($errorCodes as $errorCode => $errorTitle){
            $this->addHTML('<tr>')
                    ->addHTML('<td><b>' . $errorCode . '</b></td>')
                    ->addHTML('<td>' . $errorTitle . '</td>')
                    ->addHTML('</tr>');
        }
        $this->addHTML('</table>');
    }

    protected function openViewWrap() {
        if ($this->addWrap) {
            $this->addHTML('<section class="section section_type_error banner banner_size_normal banner_page_home banner_fixed">');
            $this->addHTML('<div class="container">');
            $this->addHTML('<div class="row">');
            $this->addHTML('<div class="col-xs-12 form-col">');
        }
        
        $this->addHTML('<h1 class="section__title section__title_color_white">Oops! Something went wrong</h1>');

        return $this;
    }

    protected function closeViewWrap() {
        if ($this->addWrap) {
            $this->addHTML('</div><!--.col-->');
            $this->addHTML('</div><!--.row-->');
            $this->addHTML('</div><!--.container-->'); 
            $this->addHTML('</section>');
        }

        return $this;
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
}
