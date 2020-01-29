<?php
/**
 * Description of AbstractTableColumn
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    4.0.0
 */
class AbstractTableColumn extends GI_Model {
    
    /** @var AbstractTable */
    protected $table = NULL;
    
    public function getLabel(){
        $label = $this->getProperty('label');
        return $label;
    }
    
    public function getColumnName(){
        $columnName = $this->getProperty('column_name');
        return $columnName;
    }
    
    public function alterTable(){
        $rootDAO = $this->map->getRootDAO();
        if(is_a($rootDAO, 'AbstractTableColumnDAO')){
            return $rootDAO->alterTable();
        }
        return false;
    }
    
    /** @return AbstractTable */
    public function getTable(){
        if(is_null($this->table)){
            $this->table = TableFactory::getModelById($this->getProperty('table_id'));
        }
        return $this->table;
    }
    
}