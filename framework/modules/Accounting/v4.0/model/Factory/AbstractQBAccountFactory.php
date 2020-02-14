<?php

/**
 * Description of AbstractQBAccountFactory
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    3.0.1
 */
abstract class AbstractQBAccountFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'qb_account';
    protected static $models = array();
    protected static $modelsByQBId = array();

    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return AbstractQBAccount
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new QBAccount($map);
                break;
        }
        return static::setFactoryClassName($model);
    }

    public static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            case 'account':
                $typeRefs = array('account');
                break;
            case 'asset':
                $typeRefs = array('asset');
                break;
            case 'equity':
                $typeRefs = array('equity');
                break;
            case 'expense':
                $typeRefs = array('expense');
                break;
            case 'liability':
                $typeRefs = array('liability');
                break;
            case 'revenue':
                $typeRefs = array('revenue');
                break;
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    
    /**
     * @param string $typeRef
     * @return AbstractQBAccount
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param integer $id - the id of the model
     * @param boolean $force - Whether or not you want to force the system to update the model, or to use available model from object pool
     * @return AbstractQBAccount
     */
    public static function getModelById($id, $force = false) {
        return parent::getModelById($id, $force);
    }

    /**
     * 
     * @param Integer $qbId
     * @return AbstractQBAccount
     */
    public static function getModelByQBId($qbId) {
        $franchiseId = QBConnection::getFranchiseId();
        if (isset(static::$modelsByQBId[$franchiseId][$qbId])) {
            return static::$modelsByQBId[$franchiseId][$qbId];
        }
        $search = static::search();
        $search->filter('qb_id', $qbId);
        if (ProjectConfig::getIsFranchisedSystem()) {
            $search->ignoreFranchise('qb_account');
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
    
    public static function getAccountOptionsArray($includeOnlyTypeRefs = array(), $mustIncludeItemWithQBId = NULL) {
        $franchiseId = QBConnection::getFranchiseId();
        $optionsArray = array();
        $dataArray = array();
        if (apcu_exists('qb_accounts_' . $franchiseId)) {
            $dataArray = apcu_fetch('qb_accounts_' . $franchiseId);
        }
        if (empty($dataArray)) {
            $dataArray = static::updateDBDataFromQB();
        }
        if (!empty($dataArray)) {
            apcu_store('qb_accounts_' . $franchiseId, $dataArray, ProjectConfig::getApcuTTL());
        }
        foreach ($dataArray as $accountTypeRef => $accountTypeOptions) {
            $merge = true;
            if (!empty($includeOnlyTypeRefs)) {
                if (!in_array($accountTypeRef, $includeOnlyTypeRefs)) {
                    $merge = false;
                }
            }
            if ($merge) {
                $optionsArray = $optionsArray + $accountTypeOptions;
            }
        }
        if (!empty($mustIncludeItemWithQBId && !isset($optionsArray[$mustIncludeItemWithQBId]))) {
            $requiredModel = static::getModelByQBId($mustIncludeItemWithQBId);
            if (!empty($requiredModel)) {
                $optionsArray[$requiredModel->getProperty('qb_id')] = $requiredModel->getNumberAndName();
            }
        }
        return $optionsArray;
    }

    public static function refreshCachedDataFromDB() {
        $franchiseId = QBConnection::getFranchiseId();
        if (apcu_exists('qb_accounts_' . $franchiseId)) {
            if (!apcu_delete('qb_accounts_' . $franchiseId)) {
                return false;
            }
        }
        $options = static::getQBAccountOptionsDataFromDB();
        if (apcu_store('qb_accounts_' . $franchiseId, $options, ProjectConfig::getApcuTTL())) {
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
        $query = "Select * from Account StartPosition 0 MaxResults 1000";
        try {
            $results = $dataService->Query($query);
            $error = $dataService->getLastError();
            if (!empty($error)) {
                GI_URLUtils::redirectToQBError($error);
            }
        } catch (Exception $ex) {
            GI_URLUtils::redirectToError(6000, $ex->getMessage());
        }
        $typesByTitle = array();
        if (!empty($results)) {
            foreach ($results as $account) {
                $qbId = $account->Id;
                $typeTitle = $account->Classification;
                $accountModel = static::getModelByQBId($qbId);
                if (empty($accountModel)) {
                    if (isset($typesByTitle[$typeTitle])) {
                        $typeModel = $typesByTitle[$typeTitle];
                    } else {
                        $typeModelSearch = new GI_DataSearch('qb_account_type');
                        $typeModelSearch->filter('title', $typeTitle);
                        $typeModelResults = $typeModelSearch->select();
                        if (empty($typeModelResults)) {
                            continue;
                        }
                        $typeModel = $typeModelResults[0];
                        $typesByTitle[$typeTitle] = $typeModel;
                    }
                    $accountModel = QBAccountFactory::buildNewModel($typeModel->getProperty('ref'));
                    $accountModel->setProperty('bos_active', 0); //Not Visible by default
                }
                if (empty($accountModel)) {
                    continue;
                }
                if (!$accountModel->updateFromQBData($account)) {
                    return false;
                }
                if (!empty($accountModel->getId()) && !empty($accountModel->getProperty('qb_active')) && !empty($accountModel->getProperty('bos_active'))) {
                    $accountTypeRef = $accountModel->getTypeRef();
                    if (!isset($optionsData[$accountTypeRef])) {
                        $optionsData[$accountTypeRef] = array();
                    }
                    $optionsData[$accountTypeRef][$accountModel->getProperty('qb_id')] = $accountModel->getNumberAndName();
                }
            }
        }

        return $optionsData;
    }

    protected static function getQBAccountOptionsDataFromDB() {
        $options = array();
        $search = static::search();
        $search->filter('qb_active', 1)
                ->filter('bos_active', 1);
        if (ProjectConfig::getIsFranchisedSystem()) {
            $search->ignoreFranchise('qb_account');
            QBConnection::addFranchiseFilterToDataSearch($search);
        }
        $models = $search->select();
        if (!empty($models)) {
            foreach ($models as $model) {
                if (!isset($options[$model->getTypeRef()])) {
                    $options[$model->getTypeRef()] = array();
                }
                $qbId = $model->getProperty('qb_id');
                $options[$model->getTypeRef()][$qbId] = $model->getNumberAndName();
            }
        }
        return $options;
    }

}
