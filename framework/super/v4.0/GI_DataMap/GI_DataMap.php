<?php
/**
 * Description of GI_DataMap
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.5
 */
abstract class GI_DataMap extends GI_Object {
    
    protected $map = array();
    /** @var GI_DAO[] */
    protected $daoArray;
    protected $dataMapEntrySample;
    protected $typeRef;
    protected $typeTitle = '';
    protected $actualTypeModel = NULL;
    protected $baseTypeModelArray = array();
    protected $primaryTableName = NULL;
    protected $tableNames = array();
    protected $hardDeleteAllowedTableNames = array('login');
    
    public function __construct($daoArray, GI_DataMapEntry $dataMapEntrySample, $typeRef = NULL) {
        $this->daoArray = $daoArray;
        $this->dataMapEntrySample = $dataMapEntrySample;
        $this->typeRef = $typeRef;
        $this->buildMap();
    }
    
    public function __clone() {
        $newMap = array();
        $clones = array();
        foreach($this->map as $key => $object){
            $keyArray = explode('.', $key);
            if(!isset($clones[$keyArray[0]])){
                $clone = clone $object;
                $clones[$keyArray[0]] = $clone;
            } else {
                $clone = $clones[$keyArray[0]];
            }
            $newMap[$key] = $clone;
        }
        $this->map = $newMap;
    }
    
    public function getTypeRef() {
        return $this->typeRef;
    }
    
    public function getTypeTitle() {
        return $this->typeTitle;
    }
    
    public function getPrimaryTableName() {
        return $this->primaryTableName;
    }
    
    public function getTypeModel($typeRef = NULL) {
        if (empty($typeRef)) {
            if (!empty($this->actualTypeModel)) {
                return $this->actualTypeModel;
            } 
        } else {
            if (isset($this->baseTypeModelArray[$typeRef])) {
                return $this->baseTypeModelArray[$typeRef];
            }
        }
        return NULL;
    }
    
    /** @return GI_DAO */
    public function getRootDAO(){
        if(isset($this->daoArray[0])){
            return $this->daoArray[0];
        }
        return NULL;
    }
    
    public function getTableNames(){
        return $this->tableNames;
    }
    
    protected function buildMap() {
        $dataMapEntryClass = get_class($this->dataMapEntrySample);
        $lastIndex = sizeof($this->daoArray) - 1;
        foreach ($this->daoArray as $index => $dao) {
            if ($index == 0) {
                $isRoot = true;
            } else {
                $isRoot = false;
            }
            $typeDAO = $dao->getTypeDAO();
            if (!empty($typeDAO)) {
                $typeTitle = $typeDAO->getProperty('title');
                if (!empty($typeTitle)) {
                    $this->typeTitle = $typeTitle;
                }
                $typeTableName = $typeDAO->getTableName();
                $baseTypeModel = TypeModelFactory::getBaseTypeModel($typeTableName);
                if (!empty($baseTypeModel)) {
                    $baseTypeRef = $baseTypeModel->getProperty('ref');
                    if (!isset($this->baseTypeModelArray[$baseTypeRef])) {
                       $this->baseTypeModelArray[$baseTypeRef] = TypeModelFactory::buildModelWithTypeDAO($typeDAO);
                    }
                }
            }
            $tableName = $dao->getTableName();
            if (is_null($this->primaryTableName)) {
                $this->primaryTableName = $tableName;
            }
            $this->tableNames[] = $tableName;
            $used = $dao->getUsedState();
            $properties = $dao->getProperties();
            foreach ($properties as $key => $value) {
                $dataMapEntry = new $dataMapEntryClass();
                $dataMapEntry->setDAO($dao);
                $dataMapEntry->setUsedState($used);
                $dataMapEntry->setDAOisRoot($isRoot);
                $dataMapEntry->setColumnType($dao->getColType($key));
                $dataMapEntry->setColumnNotNullStatus($dao->getColNotNullStatus($key));
                $this->map[$tableName . '.' . $key] = $dataMapEntry;
            }
            if ($index == $lastIndex) {
                if (!empty($typeDAO)) {
                    $typeModel = TypeModelFactory::buildModelWithTypeDAO($typeDAO);
                    $this->actualTypeModel = $typeModel;
                }
            }
        }
    }

    public function getProperty($key, $original = false) {
        $keyArray = explode('.', $key);
        if (count($keyArray) == 1) {
            $key = $this->primaryTableName . '.' . $key;
        }
        if (isset($this->map[$key])) {
            $dao = $this->map[$key]->getDAO();
            $propertyKey = $keyArray[count($keyArray) - 1];
            $property = $dao->getProperty($propertyKey, $original);
            return $property;
        }
        return NULL;
    }
    
    public function getProperties(){
        $properties = array();
        foreach($this->map as $property => $dataMapEntry){
            /*@var $dataMapEntry GI_DataMapEntry*/
            $dao = $dataMapEntry->getDAO();
            $keyArray = explode('.', $property);
            $propertyKey = $keyArray[count($keyArray) - 1];
            $properties[$property] = $dao->getProperty($propertyKey);
        }
        return $properties;
    }

    public function setProperty($key, $value) {
        $keyArray = explode('.', $key);
        if (sizeof($keyArray) == 1) {
            $key = $this->primaryTableName . '.' . $key;
        }
        if (isset($this->map[$key])) {
            $dataMapEntry = $this->map[$key];
            $dao = $dataMapEntry->getDAO();
            $propertyKey = $keyArray[sizeof($keyArray) - 1];
            $dao->setProperty($propertyKey, $value);
            return true;
        }
        return false;
    }
    
    public function getTypeProperty($key) {
        if (!empty($this->actualTypeModel)) {
            $value = $this->actualTypeModel->getProperty($key);
            if (!empty($value)) {
                return $value;
            }
        }
        return NULL;
    }

    public function save() {
      //  return $this->insert(); //For legacy systems only
        $lastDAO = NULL;
        $parentId = NULL;
        foreach ($this->map as $colKey => $dataMapEntry) {
            $currentDAO = $dataMapEntry->getDAO();
            if (is_null($lastDAO)) {
                $usedState = $dataMapEntry->getUsedState();
                if (empty($currentDAO->getProperty('id')) || $usedState) {
                    if (!$currentDAO->save()) {
                        return false;
                    }
                    $dataMapEntry->setUsedState(false);
                }
                $parentId = $currentDAO->getProperty('id');
                $lastDAO = $currentDAO;
            } else {
                if (!($currentDAO == $lastDAO)) {
                    $currentDAO->setProperty('parent_id', $parentId);
                    $usedState = $dataMapEntry->getUsedState();
                    if (empty($currentDAO->getProperty('id')) || $usedState) {
                        if (!$currentDAO->save()) {
                            return false;
                        }
                        $dataMapEntry->setUsedState(false);
                    }

                    $lastDAO = $currentDAO;
                    $parentId = $currentDAO->getProperty('id');
                }
            }
            $pendingLinkDAO = $currentDAO->getPendingLinkDAO();
            if (!is_null($pendingLinkDAO)) {
                $tableName = $currentDAO->getTableName();
                $tableColKey = $tableName . '_id';
                $currentDAOId = $currentDAO->getProperty('id');
                $pendingLinkDAO->setProperty($tableColKey, $currentDAOId);
                if (!$pendingLinkDAO->save()) {
                    return false;
                }
                $currentDAO->setPendingLinkDAO(NULL);
            }
        }
        return true;
    }

    /**
     * @deprecated
     * @return boolean
     */
    protected function insert() {
        $lastDAO = NULL;
        $parentId = NULL;
        foreach ($this->map as $colKey => $dataMapEntry) {
            $currentDAO = $dataMapEntry->getDAO();
            if (is_null($lastDAO)) {
                if (!$currentDAO->save()) {
                    return false;
                }
                $parentId = $currentDAO->getProperty('id');
                $lastDAO = $currentDAO;
            } else {
                if (!($currentDAO == $lastDAO)) {
                    $currentDAO->setProperty('parent_id', $parentId);
                    if (!$currentDAO->save()) {
                        return false;
                    }
                    $lastDAO = $currentDAO;
                    $parentId = $currentDAO->getProperty('id');
                }
            }
            $pendingLinkDAO = $currentDAO->getPendingLinkDAO();
            if (!is_null($pendingLinkDAO)) {
                $tableName = $currentDAO->getTableName();
                $tableColKey = $tableName . '_id';
                $currentDAOId = $currentDAO->getProperty('id');
                $pendingLinkDAO->setProperty($tableColKey, $currentDAOId);
                if (!$pendingLinkDAO->save()) {
                    return false;
                }
                $currentDAO->setPendingLinkDAO(NULL);
            }
        }
        return true;
    }

    public function softDelete() {
        foreach ($this->map as $mapEntry) {
            $dao = $mapEntry->getDAO();
            $daoStatus = $dao->getProperty('status');
            if (!empty($daoStatus)) {
                if (!$dao->softDelete()) {
                    return false;
                }
            }
        }
        return true;
    }
    
    public function unSoftDelete() {
        foreach ($this->map as $mapEntry) {
            $dao = $mapEntry->getDAO();
            $daoStatus = $dao->getProperty('status');
            if (empty($daoStatus)) {
                $dao->setProperty('status', 1);
                if (!$dao->save()) {
                    return false;
                }
            }
        }
        return true;
    }
    
    public function delete() {
        if (in_array($this->primaryTableName, $this->hardDeleteAllowedTableNames)) {
            foreach ($this->map as $dataMapEntry) {
                $dao = $dataMapEntry->getDAO();
                $daoId = $dao->getProperty('id');
                if (!empty($daoId)) {
                    if (!$dao->delete()) {
                        return false;
                    }
                }
            }
            return true;
        }
        return false;
    }

    public function getPropertyColType($key) {
        $keyArray = explode('.', $key);
        if (sizeof($keyArray) == 1) {
            $key = $this->primaryTableName . '.' . $key;
        }
        if (isset($this->map[$key])) {
            $dataMapEntry = $this->map[$key];
            $colType = $dataMapEntry->getColumnType();
            return $colType;
        }
        return NULL;
    }

    public function getPropertyColNotNullStatus($key) {
        $keyArray = explode('.', $key);
        if (sizeof($keyArray) == 1) {
            $key = $this->primaryTableName . '.' . $key;
        }
        if (isset($this->map[$key])) {
            $dataMapEntry = $this->map[$key];
            $colNullStatus = $dataMapEntry->getColumnNotNullStatus();
            return $colNullStatus;
        }
        return NULL;
    }
    
    public function getKeyNames() {
        return array_keys($this->map);
    }

    public function getUsedState($key) {
        if (isset($this->map[$key])) {
            $dataMapEntry = $this->map[$key];
            if (!empty($dataMapEntry)) {
                return $dataMapEntry->getUsedState();
            }
        }

        return NULL;
    }

    public function setUsedState($key, $usedState) {
        if (isset($this->map[$key])) {
            $dataMapEntry = $this->map[$key];
            if (!empty($dataMapEntry)) {
                $dataMapEntry->setUsedState($usedState);
                return true;
            }
        }
        return false;
    }

}