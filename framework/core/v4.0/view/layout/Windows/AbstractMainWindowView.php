<?php
/**
 * Description of AbstractMainWindowView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractMainWindowView extends WindowView {
    
    protected $viewWrapId = 'main_window_view_wrap';
    protected $mainContentClass = '';
    
    public function getMainContentClass(){
        return $this->mainContentClass;
    }

    public function setMainContentClass($class){
        $this->mainContentClass = $class;
        return $this;
    }
    
    protected function openOuterWrap(){
        if(!$this->addOuterWrap){
            return $this;
        }
        $this->addHTML('<div id="main_content"');
        if(!empty($this->mainContentClass)){
            $this->addHTML(' class="'.$this->mainContentClass.'"');
        }
        $this->addHTML('>');
        return $this;
    }
    
    protected function closeOuterWrap(){
        if(!$this->addOuterWrap){
            return $this;
        }
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function buildViewBody() {
        if(!empty($this->viewBodyBarHTML)){
            $this->buildViewBodyBar();
        }
        return parent::buildViewBody();
    }
    
}
