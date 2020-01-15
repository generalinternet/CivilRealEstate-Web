<?php
/**
 * Description of AbstractContentInContent
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.1
 */
abstract class AbstractContentInContent extends GI_Model {
    
    /**
     * @var Content 
     */
    protected $parentContent = NULL;
    /**
     * @var Content 
     */
    protected $childContent = NULL;
    
    public function softDelete() {
        $otherParents = ContentInContentFactory::search()
                ->filter('c_content_id', $this->getProperty('c_content_id'))
                ->filterNotEqualTo('id', $this->getProperty('id'))
                ->count();
        if(!$otherParents){
            $childContent = $this->getChildContent();
            $childContent->softDelete();
        }
        return parent::softDelete();
    }
    
    /**
     * 
     * @return Content
     */
    public function getParentContent(){
        if(is_null($this->parentContent)){
            $this->parentContent = ContentFactory::getModelById($this->getProperty('p_content_id'));
        }
        
        return $this->parentContent;
    }
    
    /**
     * 
     * @return Content
     */
    public function getChildContent(){
        if(is_null($this->childContent)){
            $this->childContent = ContentFactory::getModelById($this->getProperty('c_content_id'));
        }
        
        return $this->childContent;
    }
    
}
