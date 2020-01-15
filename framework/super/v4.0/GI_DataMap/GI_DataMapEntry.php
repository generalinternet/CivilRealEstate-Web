<?php
/**
 * Description of GI_DataMapEntry
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.1
 */
abstract class GI_DataMapEntry extends GI_Object {
    
    /** @return GI_DAO */
    protected $dao = NULL;
    protected $daoIsRoot = false;
    protected $columnType = NULL;
    protected $columnNotNullStatus = true;
    
    public function setDAO(GI_DAO $dao) {
        $this->dao = $dao;
    }
    
    /** @return GI_DAO */
    public function getDAO() {
        return $this->dao;
    }
    
    public function setUsedState($isUsed) {
        $dao = $this->getDAO();
        if (!empty($dao)) {
            $dao->setUsedState($isUsed);
        }
    }
    
    public function getUsedState() {
        $dao = $this->getDAO();
        if (!empty($dao)) {
            return $dao->getUsedState();
        }
        return NULL;
    }
    
    public function setDAOisRoot($isRoot) {
        $this->daoIsRoot = $isRoot;
    }
    
    public function getDAOisRoot() {
        return $this->daoIsRoot;
    }
    
    public function setColumnType($columnType) {
        $this->columnType = $columnType;
    }
    
    public function getColumnType() {
        return $this->columnType;
    }
    
    public function setColumnNotNullStatus($notNullStatus) {
        $this->columnNotNullStatus = $notNullStatus;
    }
    
    public function getColumnNotNullStatus() {
        return $this->columnNotNullStatus;
    }
    
    public function __clone(){
        $this->dao = clone $this->dao;
    }
    
}