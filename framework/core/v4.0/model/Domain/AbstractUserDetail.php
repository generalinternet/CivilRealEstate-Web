<?php
/**
 * Description of AbstractUserDetail
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.1
 */
abstract class AbstractUserDetail extends GI_Model {
    
    /**
     * @var AbstractUser
     */
    protected $user = NULL;
    
    public function handleFormSubmission(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            return $this;
        }
        return NULL;
    }
    
    /**
     * @return AbstractUser
     */
    public function getUser(){
        if(is_null($this->user)){
            $this->user = UserFactory::getModelById($this->getProperty('user_id'));
        }
        return $this->user;
    }
    
}
