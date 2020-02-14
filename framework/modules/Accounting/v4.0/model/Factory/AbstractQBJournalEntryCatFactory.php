<?php

/**
 * Description of AbstractQBJournalEntryCatFactory
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    3.0.0
 */
abstract class AbstractQBJournalEntryCatFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'qb_journal_entry_cat';
    protected static $models = array();
    protected static $modelsByRef = array();
    protected static $modelsByPOLineTypeRef = array();

    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new QBJournalEntryCat($map);
                break;
        }
        return static::setFactoryClassName($model);
    }

    public static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    
    public static function getModelByRef($ref) {
        if (!isset(static::$modelsByRef[$ref])) {
            $search = static::search();
            $search->filter('ref', $ref)
                    ->orderBy('id', 'ASC');
            $results = $search->select();
            if (empty($results)) {
                return NULL;
            }
            static::$modelsByRef[$ref] = $results[0];
        }
        return static::$modelsByRef[$ref];
    }
    
    public static function getModelByPOLineTypeRef($poLineTypeRef) {
        if (!isset(static::$modelsByPOLineTypeRef[$poLineTypeRef])) {
            $search = static::search();
            $search->filter('po_line_type_ref', $poLineTypeRef)
                    ->orderBy('id', 'ASC');
            $results = $search->select();
            if (empty($results)) {
                return NULL;
            }
            static::$modelsByPOLineTypeRef[$poLineTypeRef] = $results[0];
        }
        return static::$modelsByPOLineTypeRef[$poLineTypeRef];
    }

}
