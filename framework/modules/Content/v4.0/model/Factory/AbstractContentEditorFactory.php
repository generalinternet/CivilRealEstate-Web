<?php
/**
 * Description of AbstractContentEditorFactory
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractContentEditorFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'content_editor';
    protected static $models = array();
    
    protected static function buildModelByTypeRef($typeRef, $map) {
        $model = new ContentEditor($map);
        return static::setFactoryClassName($model);
    }
    
    public static function getTypeRefArrayFromTypeRef($typeRef) {
        return array();
    }
    
    /**
     * 
     * @param string $typeRef
     * @return AbstractContentEditor
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param AbstractContent $content
     * @param AbstractUser $editor
     * @param boolean $ignoreStatus
     * @return AbstractContentEditor
     */
    public static function getByContentAndEditor(AbstractContent $content, AbstractUser $editor, $ignoreStatus = false){
        $search = static::search()
                ->filter('content_id', $content->getId())
                ->filter('user_id', $editor->getId());
        if($ignoreStatus){
            $search->setAutoStatus(false);
        }
        $result = $search->select();
        if($result){
            return $result[0];
        }
        return NULL;
    }
    
    /**
     * @param AbstractUser $editor
     * @param AbstractContent $content
     * @return boolean
     */
    public static function addEditorToContent(AbstractUser $editor, AbstractContent $content, $andNotify = true){
        $contentEditor = static::getByContentAndEditor($content, $editor, true);
        if($contentEditor){
            if(!$contentEditor->getProperty('status')){
                $contentEditor->setProperty('status', 1);
                return $contentEditor->save();
            }
            return true;
        }
        
        $newContentEditor = static::buildNewModel();
        $newContentEditor->setContent($content);
        $newContentEditor->setEditor($editor);
        
        if($newContentEditor->save()){
            if($andNotify){
                Notification::notifyUser($editor, 'New Form Assigned', $content->getViewURLAttrs(), 'You have been assigned a new "' . $content->getTitle() . '" to edit.');
            }
            return true;
        }
        return false;
    }
    
    /**
     * @param AbstractUser $editor
     * @param AbstractContent $content
     * @return boolean
     */
    public static function removeEditorFromContent(AbstractUser $editor, AbstractContent $content){
        $contentEditor = static::getByContentAndEditor($content, $editor);
        if(!$contentEditor){
            return true;
        }
        return $contentEditor->softDelete();
    }
    
    /**
     * @param AbstractContent $content
     * @return AbstractContentEditor[]
     */
    public static function getContentEditors(AbstractContent $content){
        $search = static::search()
                ->filter('content_id', $content->getId());
        $result = $search->select();
        return $result;
    }
    
    /**
     * @param AbstractUser[] $editors
     * @param AbstractContent $content
     * @return boolean
     */
    public static function adjustEditorsForContent($editors, AbstractContent $content, $andNotify = true){
        $contentEditors = static::getContentEditors($content);
        $existingEditors = array();
        foreach($contentEditors as $contentEditor){
            $existingEditors[$contentEditor->getProperty('user_id')] = $contentEditor;
        }
        
        foreach($editors as $editor){
            if(isset($existingEditors[$editor->getId()])){
                unset($existingEditors[$editor->getId()]);
            } else {
                if(!static::addEditorToContent($editor, $content, $andNotify)){
                    return false;
                }
            }
        }
        
        if(!empty($existingEditors)){
            foreach($existingEditors as $existingEditor){
                $deleteEditor = $existingEditor->getEditor();
                if(!static::removeEditorFromContent($deleteEditor, $content)){
                    return false;
                }
            }
        }
        return true;
    }
    
}
