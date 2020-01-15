<?php
/**
 * Description of GI_TypeModelFactory
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.1
 */
abstract class GI_TypeModelFactory extends GI_Object {

    public static function getModelById($typeTableName, $id, $dbType = 'client') {
        $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
        $defaultTypeModelClass = static::getStaticPropertyValueFromChild('defaultTypeModelClass');
        $dao = $defaultDAOClass::getById($typeTableName, $id, $dbType);
        if (!empty($dao)) {
            $typeModel = new $defaultTypeModelClass($dao);
            return $typeModel;
        }
        return NULL;
    }

    public static function getTypeModelByRef($typeRef, $typeTableName, $dbType = 'client') {
        $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
        $defaultTypeModelClass = static::getStaticPropertyValueFromChild('defaultTypeModelClass');
        $daoArray = $defaultDAOClass::getByProperties($typeTableName, array(
            'ref'=>$typeRef
        ), $dbType);
        if (!empty($daoArray)) {
            $dao = $daoArray[0];
            $typeModel = new $defaultTypeModelClass($dao);
            return $typeModel;
        }
        return NULL;
    }
    
    public static function buildModelWithTypeDAO($typeDAO) {
        $defaultTypeModelClass = static::getStaticPropertyValueFromChild('defaultTypeModelClass');
        return new $defaultTypeModelClass($typeDAO);
    }
    
    public static function getBaseTypeModel($typeTableName) {
        $dataSearch = new GI_DataSearch($typeTableName);
        $dataSearch->orderBy('id', 'ASC');
        $typeDAOArray = $dataSearch->select();
        if (!empty($typeDAOArray)) {
            $typeDAO = $typeDAOArray[0];
            return static::buildModelWithTypeDAO($typeDAO);
        }
        return NULL;
    }

}