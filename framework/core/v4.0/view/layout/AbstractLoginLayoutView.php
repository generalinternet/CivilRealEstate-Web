<?php
/**
 * Description of AbstractLoginLayoutView
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    2.0.1
 */
abstract class AbstractLoginLayoutView extends AbstractLayoutView {

    protected $addFullscreenToggle = false;
    
    protected function addDefaultJS(){
        parent::addDefaultJS();
        $this->addJS('framework/core/' . FRMWK_CORE_VER. '/resources/js/forms.js');
        $this->addJS('framework/core/' . FRMWK_CORE_VER. '/resources/js/layout.js');
    }
    
    public function display() {
        $this->addHeader()
                ->openContentWrapDiv()
//                ->addMenuBtn()
//                ->addMenu()
                ->addLogo()
                ->openLoginBoxDiv()
                ->addHTML($this->getMainContent())
                ->closeLoginBoxDiv()
                ->closeContentWrapDiv()
                ->addFooter();
        echo $this->html;
    }
    
    protected function openMenuWrapDiv($class = ''){
        return parent::openMenuWrapDiv('login_size ' . $class);
    }
    
//    protected function addMenuContent(){
//        $loginURL = GI_URLUtils::buildURL(array(
//            'controller' => 'login',
//            'action' => 'index'
//        ));
//        $this->addHTML('<ul><li><a href="' . $loginURL . '" title="Log In">Log In</a></li></ul>');
//        return $this;
//    }
    
    protected function openLoginBoxDiv(){
        $this->addHTML('<div id="login_box" class="content_padding">');
        return $this;
    }
    
    protected function closeLoginBoxDiv(){
        $this->addHTML('</div>');
        return $this;
    }

}