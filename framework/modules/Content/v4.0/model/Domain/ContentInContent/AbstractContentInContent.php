<?php
/**
 * Description of AbstractContentInContent
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.1
 */
abstract class AbstractContentInContent extends GI_Model {
    
    /** @var AbstractContent */
    protected $parentContent = NULL;
    /** @var AbstractContent */
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
    
    /** @return AbstractContent */
    public function getParentContent(){
        if(is_null($this->parentContent)){
            $this->parentContent = ContentFactory::getModelById($this->getProperty('p_content_id'));
        }
        
        return $this->parentContent;
    }
    
    /** @return AbstractContent */
    public function getChildContent(){
        if(is_null($this->childContent)){
            $this->childContent = ContentFactory::getModelById($this->getProperty('c_content_id'));
        }
        
        return $this->childContent;
    }
    
    public function isOnlyChild(){
        $search = ContentInContentFactory::search()
                ->filter('p_content_id', $this->getProperty('p_content_id'))
                ->filterNotEqualTo('c_content_id', $this->getProperty('c_content_id'));
        $siblingCount = $search->count();
        
        if(!empty($siblingCount)){
            return false;
        }
        return true;
    }
    
}
