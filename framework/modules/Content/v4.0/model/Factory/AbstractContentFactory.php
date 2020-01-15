<?php
/**
 * Description of AbstractContentFactory
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.1
 */
abstract class AbstractContentFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'content';
    protected static $models = array();
    protected static $optionsArray = NULL;
    protected static $indexableTypeRefs = array(
        'content',
        'page',
        'page_post',
        'page_df'
    );
    
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'text':
                $model = new ContentText($map);
                break;
            case 'text_code':
                $model = new ContentTextCode($map);
                break;
            case 'text_wysiwyg':
                $model = new ContentTextWYSIWYG($map);
                break;
            case 'text_video':
                $model = new ContentTextVideo($map);
                break;
            case 'page':
                $model = new ContentPage($map);
                break;
            case 'page_post':
                $model = new ContentPagePost($map);
                break;
            case 'page_df':
                $model = new ContentPageDF($map);
                break;
            case 'file_col':
                $model = new ContentFileCol($map);
                break;
            case 'file_col_slider':
                $model = new ContentFileColSlider($map);
                break;
            case 'file_col_gallery':
                $model = new ContentFileColGallery($map);
                break;
            case 'file_col_image':
                $model = new ContentFileColImage($map);
                break;
            case 'base':
                $model = new ContentBase($map);
                break;
            case 'dynamic_form':
                $model = new ContentDF($map);
                break;
            case 'content_ref':
                $model = new ContentRef($map);
                break;
            case 'content':
            default:
                $model = new Content($map);
                break;
        }
        return static::setFactoryClassName($model);
    }
    
    public static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            case 'content':
                $typeRefs = array('content');
                break;
            case 'text':
                $typeRefs = array('text', 'text');
                break;
            case 'text_code':
                $typeRefs = array('text', 'text_code');
                break;
            case 'text_wysiwyg':
                $typeRefs = array('text', 'text_wysiwyg');
                break;
            case 'text_video':
                $typeRefs = array('text', 'text_video');
                break;
            case 'page':
                $typeRefs = array('page', 'page');
                break;
            case 'page_post':
                $typeRefs = array('page', 'page_post', 'page_post');
                break;
            case 'file_col':
                $typeRefs = array('file_col', 'file_col');
                break;
            case 'file_col_slider':
                $typeRefs = array('file_col', 'file_col_slider');
                break;
            case 'file_col_gallery':
                $typeRefs = array('file_col', 'file_col_gallery');
                break;
            case 'file_col_image':
                $typeRefs = array('file_col', 'file_col_image');
                break;
            //forms module
            case 'base':
                $typeRefs = array('base');
                break;
            case 'dynamic_form':
                $typeRefs = array('dynamic_form');
                break;
            case 'page_df':
                $typeRefs = array('page', 'page_df', 'page_df');
                break;
            case 'content_ref':
                $typeRefs = array('content_ref');
                break;
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    
    /**
     * 
     * @param string $typeRef
     * @return Content
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * 
     * @param string $contentRef
     * @return Content
     */
    public static function getModelByRef($contentRef){
        $contentResult = ContentFactory::search()
                ->filter('ref', $contentRef)
                ->select();
        
        if($contentResult){
            return $contentResult[0];
        }
        
        return NULL;
    }
    
    /**
     * @param AbstractTag $tag
     * @return AbstractContent[]
     */
    public static function getByTag(AbstractTag $tag){
        $tagId = $tag->getId();
        $search = static::search();
        $contentTable = $search->prefixTableName('content');
        $ilttJoin = $search->createJoin('item_link_to_tag', 'item_id', $contentTable, 'id', 'ILTT');
        $ilttJoin->filter('ILTT.tag_id', $tagId);
        $search->groupBy('id')
                ->orderBY('ILTT.pos','ASC');
        $content = $search->select();
        return $content;
    }
    
    public static function getIndexableTypeRefs(){
        $typeRefs = array();
        foreach(static::$indexableTypeRefs as $typeRef){
            $sample = static::buildNewModel($typeRef);
            if(!$sample || !$sample->isIndexViewable()){
                continue;
            }
            $typeTitle = $sample->getViewTitle();
            $typeRefs[$typeRef] = $typeTitle;
        }
        return $typeRefs;
    }
    
    /**
     * @param AbstractContent $content
     * @param string $className
     * @return AbstractContent
     */
    public static function getParentContentOfClass(AbstractContent $content, $className){
        $parentContent = $content->getParentContent();
        if($parentContent){
            if(is_a($parentContent, $className)){
                return $parentContent;
}
            return static::getParentContentOfClass($parentContent, $className);
        }
        return NULL;
    }
    
}
