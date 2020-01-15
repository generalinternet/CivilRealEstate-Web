<?php
/**
 * Description of AbstractGI_FormItem
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.1
 */
abstract class AbstractGI_FormItem {
    
    protected $html = '';
    
    public function __construct($startingHTML = '') {
        $this->setHTML($startingHTML);
    }
    
    public function setHTML($html){
        $this->html = $html;
        return $this;
    }
    
    public function addHTML($html){
        $this->html .= $html;
        return $this;
    }
    
    protected function beforeGetHTML(){
        
    }
    
    public function getHTML(){
        $this->beforeGetHTML();
        return $this->html;
    }
    
}
