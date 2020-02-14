<?php

/**
 * Description of AbstractQBProductFactory
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    3.0.1
 */
abstract class AbstractQBProductFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'qb_product';
    protected static $models = array();
    protected static $modelsByQBId = array();

    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return AbstractQBProduct
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new QBProduct($map);
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
     * @param string $typeRef
     * @return AbstractQBProduct
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param integer $id - the id of the model
     * @param boolean $force - Whether or not you want to force the system to update the model, or to use available model from object pool
     * @return AbstractQBProduct
     */
    public static function getModelById($id, $force = false) {
        return parent::getModelById($id, $force);
    }

    /**
     * 
     * @param Integer $qbId
     * @return AbstractQBProduct
     */
    public static function getModelByQBId($qbId) {
        $franchiseId = QBConnection::getFranchiseId();
        if (isset(static::$modelsByQBId[$franchiseId][$qbId])) {
            return static::$modelsByQBId[$franchiseId][$qbId];
        }
        $search = static::search();
        $search->filter('qb_id', $qbId);
        if (ProjectConfig::getIsFranchisedSystem()) {
            $search->ignoreFranchise('qb_product');
            QBConnection::addFranchiseFilterToDataSearch($search);
        }
        $results = $search->orderBy('id')
                ->select();
        if (!empty($results)) {
            $model = $results[0];
            if (!isset(static::$modelsByQBId[$franchiseId])) {
                static::$modelsByQBId[$franchiseId] = array();
            }
            static::$modelsByQBId[$franchiseId][$qbId] = $model;
            return $model;
        }
        return NULL;
    }

    public static function getProductOptionsArray($mustIncludeItemWithQBId = NULL) {
        $franchiseId = QBConnection::getFranchiseId();
        $dataArray = array();
        if (apcu_exists('qb_products_' . $franchiseId)) {
            $dataArray = apcu_fetch('qb_products_' . $franchiseId);
        }
        if (empty($dataArray)) {
            $dataArray = static::updateDBDataFromQB();
        }
        if (!empty($dataArray)) {
            apcu_store('qb_products_' . $franchiseId, $dataArray, ProjectConfig::getApcuTTL());
        }
        if (!empty($mustIncludeItemWithQBId && !isset($dataArray[$mustIncludeItemWithQBId]))) {
            $requiredModel = static::getModelByQBId($mustIncludeItemWithQBId);
            if (!empty($requiredModel)) {
                $dataArray[$requiredModel->getProperty('qb_id')] = $requiredModel->getName();
            }
        }
        return $dataArray;
    }

    public static function refreshCachedDataFromDB() {
        $franchiseId = QBConnection::getFranchiseId();
        if (apcu_exists('qb_products_' . $franchiseId)) {
            if (!apcu_delete('qb_products_' . $franchiseId)) {
                return false;
            }
        }
        $options = static::getQBProductOptionsDataFromDB();
        if (apcu_store('qb_products_' . $franchiseId, $options, ProjectConfig::getApcuTTL())) {
            return true;
        }
        return false;
    }

    public static function updateDBDataFromQB() {
        $optionsData = array();
        $dataService = QBConnection::getInstance();
        if (empty($dataService)) {
            return NULL;
        }
        $nonInventoryQuery = "Select * from Item WHERE Type = 'NonInventory'";
        try {
            $nonInventoryResults = $dataService->Query($nonInventoryQuery);
            $error = $dataService->getLastError();
            if (!empty($error)) {
                GI_URLUtils::redirectToQBError($error);
            }
        } catch (Exception $ex) {
            GI_URLUtils::redirectToError(6000, $ex->getMessage());
        }
        if (!empty($nonInventoryResults)) {
            foreach ($nonInventoryResults as $product) {
                $qbId = $product->Id;
                $productModel = static::getModelByQBId($qbId);
                if (empty($productModel)) {
                    $productModel = static::buildNewModel();
                    $productModel->setProperty('bos_active', 0);
                }
                if (empty($productModel)) {
                    continue;
                }
                if (!$productModel->updateFromQBData($product)) {
                    return false;
                }
                if (!empty($productModel->getProperty('qb_active')) && !empty($productModel->getProperty('bos_active'))) {
                    $optionsData[$productModel->getProperty('qb_id')] = $productModel->getName();
                }
            }
        }
        
        $servicesQuery = "Select * from Item WHERE Type = 'Service'";
        try {
            $servicesResults = $dataService->Query($servicesQuery);
            $error = $dataService->getLastError();
            if (!empty($error)) {
                GI_URLUtils::redirectToQBError($error);
            }
        } catch (Exception $ex) {
            GI_URLUtils::redirectToError(6000, $ex->getMessage());
        }

        if (!empty($servicesResults)) {
            foreach ($servicesResults as $service) {
                $qbId = $service->Id;
                $serviceModel = static::getModelByQBId($qbId);
                if (empty($serviceModel)) {
                    $serviceModel = static::buildNewModel();
                    $serviceModel->setProperty('bos_active', 0);
                }
                if (empty($serviceModel)) {
                    continue;
                }
                if (!$serviceModel->updateFromQBData($service)) {
                    return false;
                }
                if (!empty($serviceModel->getProperty('qb_active')) && !empty($serviceModel->getProperty('bos_active'))) {
                    $optionsData[$serviceModel->getProperty('qb_id')] = $serviceModel->getName();
                }
            }
        }

        return $optionsData;
    }

    protected static function getQBProductOptionsDataFromDB() {
        $options = array();
        $search = static::search();
        $search->filter('qb_active', 1)
                ->filter('bos_active', 1);
        if (ProjectConfig::getIsFranchisedSystem()) {
            $search->ignoreFranchise('qb_product');
            QBConnection::addFranchiseFilterToDataSearch($search);
        }
        $models = $search->select();
        if (!empty($models)) {
            foreach ($models as $model) {
                $qbId = $model->getProperty('qb_id');
                $options[$qbId] = $model->getName();
            }
        }
        return $options;
    }

}
