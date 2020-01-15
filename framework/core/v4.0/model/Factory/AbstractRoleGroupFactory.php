<?php

abstract class AbstractRoleGroupFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'role_rank';
    protected static $models = array();
    protected static $modelsSystemTitleKey = array();

    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new RoleGroup($map);
                break;
        }
        return static::setFactoryClassName($model);
    }

    protected static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    
    public static function getRoleGroupByRole(AbstractRole $role) {
        $roleRankId = $role->getProperty('role_rank');
        $roleRank = static::getModelById($roleRankId);
        return $roleRank;
    }
    
    public static function getRoleGroupBySystemTitle($systemTitle){
        if(!isset(static::$modelsSystemTitleKey[$systemTitle])){
            $roleGroups = static::search()
                    ->filter('system_title', $systemTitle)
                    ->select();
            if($roleGroups){
                static::$modelsSystemTitleKey[$systemTitle] = $roleGroups[0];
            } else {
                static::$modelsSystemTitleKey[$systemTitle] = NULL;
            }
        }
        return static::$modelsSystemTitleKey[$systemTitle];
    }
    
    /** @return GI_DataSearch */
    public static function search() {
        $dataSearch = parent::search();
        $dataSearch->setSortAscending(true);
        return $dataSearch;
    }
    
    public static function searchRestricted(){
        $search = static::search();
        $userHighestRoleGroupRank = RoleGroup::getUserHighestRoleGroupRank();
        $search->filterLessOrEqualTo('rank', $userHighestRoleGroupRank);
        return $search;
    }

}
