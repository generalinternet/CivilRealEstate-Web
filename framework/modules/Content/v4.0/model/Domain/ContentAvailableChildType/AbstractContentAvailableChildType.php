<?php
/**
 * Description of AbstractContentAvailableChildType
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.1
 */
abstract class AbstractContentAvailableChildType extends GI_Model {
    
    protected $emptyParentContent = NULL;
    protected $emptyChildContent = NULL;
    
    /**
     * @return \Content[]
     */
    public function getEmptyParentContent() {
        if(is_null($this->emptyParentContent)){
            $pContentRef = $this->getProperty('p_content_ref');
            $this->emptyParentContent = ContentFactory::buildNewModel($pContentRef);
        }
        
        return $this->emptyParentContent;
    }
    
    /**
     * @return \Content[]
     */
    public function getEmptyChildContent() {
        if(is_null($this->emptyChildContent)){
            $cContentRef = $this->getProperty('c_content_ref');
            $this->emptyChildContent = ContentFactory::buildNewModel($cContentRef);
        }
        
        return $this->emptyChildContent;
    }
    
}
