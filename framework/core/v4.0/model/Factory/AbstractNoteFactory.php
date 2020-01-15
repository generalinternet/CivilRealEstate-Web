<?php
/**
 * Description of AbstractNoteFactory
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractNoteFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'note';
    protected static $models = array();
    
    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return Note
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'system':
                $model = new NoteSystem($map);
                break;
            case 'private':
                $model = new NotePrivate($map);
                break;
            default:
                $model = new Note($map);
                break;
        }
        return static::setFactoryClassName($model);
    }
    
    /**
     * @param string $typeRef
     * @return array
     */
    public static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            case 'system':
                $typeRefs = array('system');
                break;
            case 'private':
                $typeRefs = array('private');
                break;
            case 'note':
                $typeRefs = array('note');
                break;
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    
    /**
     * @param string $typeRef
     * @return Note
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param string $id
     * @param boolean $force
     * @return Note
     */
    public static function getModelById($id, $force = false){
        return parent::getModelById($id, $force);
    }
    
    public static function linkNoteAndModel(AbstractNote $note, GI_Model $model) {
        $tableName = $model->getTableName();
        $itemId = $model->getProperty('id');
        $noteId = $note->getProperty('id');
        $existingSearch = new GI_DataSearch('item_link_to_note');
        $existingSearch->filter('table_name', $tableName)
                ->filter('item_id', $itemId)
                ->filter('note_id', $noteId);
        $existingLinks = $existingSearch->select();
        if (!empty($existingLinks)) {
            return true;
        }
        $softDeletedSearch = new GI_DataSearch('item_link_to_note');
        $softDeletedSearch->filter('status', '0')
                ->filter('table_name', $tableName)
                ->filter('item_id', $itemId)
                ->filter('note_id', $noteId);
        $softDeletedLinks = $softDeletedSearch->select();
        if (!empty($softDeletedLinks)) {
            $softDeletedLink = $softDeletedLinks[0];
            $softDeletedLink->setProperty('status', 1);
            if ($softDeletedLink->save()) {
                return true;
            }
        }
        $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
        $newLink = new $defaultDAOClass('item_link_to_note');
        $newLink->setProperty('table_name', $tableName);
        $newLink->setProperty('item_id', $itemId);
        $newLink->setProperty('note_id', $noteId);
        if ($newLink->save()) {
            return true;
        }
        return false;
    }
    
    public static function getNotesLinkedToModel(GI_Model $model, $typeRef = NULL, $pageNumber = 1, $itemsPerPage = 3) {
        $noteTableName = static::getDbPrefix() . 'note';
        $noteSearch = static::search();
        $noteJoin = $noteSearch->createJoin('item_link_to_note', 'note_id', $noteTableName, 'id', 'NOTELINK');
        $noteJoin->filter('NOTELINK.table_name', $model->getTableName());
        $noteSearch->filter('NOTELINK.item_id', $model->getProperty('id'));
        if (!empty($typeRef)) {
            $noteSearch->filterByTypeRef($typeRef);
        }
        $noteSearch->orderBy('inception', 'DESC');
        $noteSearch->setPageNumber($pageNumber);
        $noteSearch->setItemsPerPage($itemsPerPage);
        return $noteSearch->select();
    }
    
}
