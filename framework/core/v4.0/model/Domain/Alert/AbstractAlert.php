<?php
/**
 * Description of AbstractAlert
 * 
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractAlert extends GI_Object {
    
    protected $colour = 'gray';
    protected $message = '';
    protected $id = NULL;
    
    public function __construct($message = NULL, $colour = NULL) {
        if(!empty($message)){
            $this->setMessage($message);
        }
        if(!empty($colour)){
            $this->setColour($colour);
        }
    }
    
    public function setColour($colour){
        $this->colour = $colour;
        return $this;
    }
    
    public function setMessage($message){
        $this->message = $message;
        return $this;
    }
    
    public function setId($id){
        $this->id = $id;
        return $this;
    }
    
    public function getColour(){
        return $this->colour;
    }
    
    public function getMessage(){
        return $this->message;
    }
    
    public function getId(){
        if(is_null($this->id)){
            $this->generateUniqueId();
        }
        return $this->id;
    }
    
    public function generateUniqueId(){
        $this->id = AlertService::getNextAlertId();
        return $this->id;
    }
    
    protected function getCloseBtn(){
        $btn = '<span class="close_alert">' . GI_StringUtils::getIcon('eks', false, 'white') . '</span>';
        return $btn;
    }
    
    public function getAlertHTML(){
        $html = '<div class="alert_message ' . $this->getColour() . '" data-alert-id="' . $this->getId() . '">';
        $html .= $this->getCloseBtn();
        $message = $this->getMessage();
        if (strpos($message, '<p') === false) {
            $message = GI_StringUtils::surroundWithTag($message, 'p');
        }
        $html .= $message;
        $html .= '</div>';
        AlertService::removeAlert($this->getId());
        return $html;
    }
    
}
