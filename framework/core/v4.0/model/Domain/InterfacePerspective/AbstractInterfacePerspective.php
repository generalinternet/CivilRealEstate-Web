<?php
/**
 * Description of AbstractInterfacePerspective
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractInterfacePerspective extends GI_Model {
    
    public function addRole(AbstractRole $role) {
        $search = new GI_DataSearch('role_has_interface_perspective');
        $search->filter('role_id', $role->getId())
                ->filter('interface_perspective_id', $this->getId());
        $results = $search->select();
        if (!empty($results)) {
            return true;
        }
        $softDeletedSearch = new GI_DataSearch('role_has_interface_perspective');
        $softDeletedSearch->filter('status', 0)
                ->filter('role_id', $role->getId())
                ->filter('interface_perspective_id', $this->getId());
        $softDeletedResults = $softDeletedSearch->select();
        if (!empty($softDeletedResults)) {
            $softDeletedDAO = $softDeletedResults[0];
            $softDeletedDAO->setProperty('status', 1);
            if ($softDeletedDAO->save()) {
                return true;
            }
        }
        $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
        $newLink = new $defaultDAOClass('role_has_interface_perspective');
        $newLink->setProperty('role_id', $role->getId());
        $newLink->setProperty('interface_perspective_id', $this->getId());
        if ($newLink->save()) {
            return true;
        }
        return false;
    }

    public function removeRole(AbstractRole $role) {
        $search = new GI_DataSearch('role_has_interface_perspective');
        $search->filter('role_id', $role->getId())
                ->filter('interface_perspective_id', $this->getId());
        $results = $search->select();
        if (empty($results)) {
            return true;
        }
        foreach ($results as $resultDAO) {
            $resultDAO->setProperty('status', 0);
            if (!$resultDAO->save()) {
                return false;
            }
        }
        return true;
    }

}
