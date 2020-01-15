<?php
/**
 * Description of GenericTypeModel
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.1
 */
class GenericTypeModel extends GI_Object {
    
    protected $dao;
    
    public function __construct(GI_DAO $typeDAO) {
        $this->dao = $typeDAO;
    }
    
    public function getProperty($key) {
        return $this->dao->getProperty($key);
    }
    
    public function getTableName() {
        return $this->dao->getTableName();
    }
    
    public function getId(){
        return $this->getProperty('id');
    }
    
}
