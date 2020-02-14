<?php
/**
 * Description of AbstractExpenseItemGroupFactory
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractExpenseItemGroupFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'expense_item_group';
    protected static $models = array();

    /**
     * @param string $typeRef
     * @param boolean $inProgress
     * @return AbstractExpenseItemGroup
     */
    public static function buildNewModel($typeRef = '', $inProgress = false) {
        $model = parent::buildNewModel($typeRef);
        if ($inProgress) {
            $model->setProperty('in_progress', 1);
        }
        return $model;
    }

    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return AbstractExpenseItemGroup
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new ExpenseItemGroup($map);
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
    
    /**
     * 
     * @param integer $id
     * @param boolean $force
     * @return AbstractExpenseItemGroup
     */
    public static function getModelById($id, $force = false) {
        return parent::getModelById($id, $force);
    }
    
}
