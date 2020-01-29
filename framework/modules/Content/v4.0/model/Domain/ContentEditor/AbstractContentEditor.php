<?php
/**
 * Description of AbstractContentEditor
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractContentEditor extends GI_Model {
    
    /** @var AbstractContent  */
    protected $content = NULL;
    /** @var AbstractUser */
    protected $editor = NULL;
    
    /**
     * @param AbstractContent $content
     * @return $this
     */
    public function setContent(AbstractContent $content){
        $this->content = $content;
        $this->setProperty('content_id', $content->getId());
        return $this;
    }
    
    /**
     * @param AbstractUser $editor
     * @return $this
     */
    public function setEditor(AbstractUser $editor){
        $this->editor = $editor;
        $this->setProperty('user_id', $editor->getId());
        return $this;
    }
    
    /** @return AbstractContent */
    public function getContent(){
        if(is_null($this->content)){
            $this->content = ContentFactory::getModelById($this->getProperty('content_id'));
        }
        
        return $this->content;
    }
    
    /** @return AbstractUser */
    public function getEditor(){
        if(is_null($this->editor)){
            $this->editor = UserFactory::getModelById($this->getProperty('user_id'));
        }
        return $this->editor;
    }
    
}
