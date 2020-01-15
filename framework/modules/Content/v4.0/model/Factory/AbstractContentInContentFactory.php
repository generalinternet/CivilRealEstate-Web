<?php
/**
 * Description of AbstractContentInContentFactory
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.1
 */
abstract class AbstractContentInContentFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'content_in_content';
    protected static $models = array();
    
    protected static function buildModelByTypeRef($typeRef, $map) {
        $model = new ContentInContent($map);
        return static::setFactoryClassName($model);
    }
    
    public static function getTypeRefArrayFromTypeRef($typeRef) {
        return array();
    }
    
    /**
     * 
     * @param string $typeRef
     * @return ContentInContent
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param AbstractContent $pContent
     * @param AbstractContent $cContent
     * @return AbstractContentInContent
     */
    public static function getByParentAndChild(AbstractContent $pContent, AbstractContent $cContent){
        $search = static::search()
                ->filter('p_content_id', $pContent->getId())
                ->filter('c_content_id', $cContent->getId());
        $result = $search->select();
        if($result){
            return $result[0];
        }
        return NULL;
    }
    
    public static function insertIntoParent(AbstractContent $cContent, AbstractContent $pContent, $pos = NULL){
        $link = static::getByParentAndChild($pContent, $cContent);
        if($link && is_null($pos)){
            return true;
        }
        
        $postSiblings = NULL;
        if(is_null($pos)){
            $maxPostSiblingSearch = static::search()
                    ->filter('p_content_id', $pContent->getId())
                    ->orderBy('pos', 'DESC')
                    ->setItemsPerPage(1);
            $maxPostSiblingResult = $maxPostSiblingSearch->select();
            if($maxPostSiblingResult){
                $maxPostSibling = $maxPostSiblingResult[0];
                $maxPostSiblingPos = $maxPostSibling->getProperty('pos');
                $pos = $maxPostSiblingPos+1;
            }
        } else {
            $postSiblingSearch = static::search()
                    ->filter('p_content_id', $pContent->getId())
                    ->filterGreaterOrEqualTo('pos', $pos);
            $postSiblings = $postSiblingSearch->select();
        }
        
        if(!$link){
            $link = static::buildNewModel();
            $link->setProperty('p_content_id',$pContent->getId());
            $link->setProperty('c_content_id',$cContent->getId());
        }
        
        $link->setProperty('pos', $pos);
        $link->save();
        
        if($postSiblings){
            foreach($postSiblings as $postSibling){
                $curSiblingPos = $postSibling->getProperty('pos');
                $newSiblingPos = $curSiblingPos+1;
                $postSibling->setProperty('pos', $newSiblingPos);
                $postSibling->save();
            }
        }
        return true;
    }
    
    public static function removeFromParent(AbstractContent $cContent, AbstractContent $pContent, $deleteLink = true){
        $link = static::getByParentAndChild($pContent, $cContent);
        if(!$link){
            return true;
        }
        
        $postSiblingSearch = static::search()
                ->filter('p_content_id', $pContent->getId())
                ->filterGreaterOrEqualTo('pos', $link->getProperty('pos'));
        $postSiblings = $postSiblingSearch->select();
        
        if($deleteLink){
            if(!$link->softDelete()){
                return false;
            }
        }
        
        if($postSiblings){
            foreach($postSiblings as $postSibling){
                $curSiblingPos = $postSibling->getProperty('pos');
                $newSiblingPos = $curSiblingPos-1;
                $postSibling->setProperty('pos', $newSiblingPos);
                $postSibling->save();
            }
        }
        return true;
    }
    
}
