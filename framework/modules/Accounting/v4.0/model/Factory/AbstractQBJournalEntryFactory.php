<?php

/**
 * Description of AbstractQBJournalEntryFactory
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    3.0.0
 */
abstract class AbstractQBJournalEntryFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'qb_journal_entry';
    protected static $models = array();
    protected static $modelsByQBId = array();

    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'sales_order_line':
                $model = new QBJournalEntrySalesOrderLine($map);
                break;
            case 'return_line_returned':
                $model = new QBJournalEntryReturnLineReturned($map);
                break;
            case 'return_line_damaged':
                $model = new QBJournalEntryReturnLineDamaged($map);
                break;
            case 'inv_adjustment_waste':
                $model = new QBJournalEntryInvAdjustmentWaste($map);
                break;
            default:
            case 'entry':
                $model = new QBJournalEntry($map);
                break;
        }
        return static::setFactoryClassName($model);
    }

    public static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            case 'sales_order_line':
                $typeRefs = array('sales_order_line');
                break;
            case 'return_line_returned':
                $typeRefs = array('return_line_returned');
                break;
            case 'return_line_damaged':
                $typeRefs = array('return_line_damaged');
                break;
            case 'inv_adjustment_waste':
                $typeRefs = array('inv_adjustment_waste');
                break;
            case 'entry':
                $typeRefs = array('entry');
                break;
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    
    public static function getModelArrayByLinkedModel(GI_Model $model) {
        $search = static::search();
        $search->filter('table_name', $model->getTableName())
                ->filter('item_id', $model->getId())
                ->orderBy('id', 'ASC');
        return $search->select();
    }

}