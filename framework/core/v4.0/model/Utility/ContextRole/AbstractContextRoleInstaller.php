<?php
/**
 * Description of AbstractContextRoleTemplateInstaller
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractContextRoleInstaller {

    protected static $projectContextRoles = array(
        array(
            'title' => 'Project Manager',
            'ref' => 'project_manager',
            'system' => 1,
            'pos'=>10,
        ),
    );
    
    protected function installContextRole($data, $appliesToTableName) {
        $search = ContextRoleFactory::search();
        $search->filter('table_name', $appliesToTableName)
                ->filterNull('item_id')
                ->filter('ref', $data['ref']);
        $results = $search->select();
        if (!empty($results)) {
            $template = $results[0];
        } else {
            $template = ContextRoleFactory::buildNewModel();
            $template->setProperty('table_name', $appliesToTableName);
            foreach ($data as $colKey => $value) {
                $template->setProperty($colKey, $value);
            }
            if (!$template->save()) {
                return false;
            }
        }
        return true;
    }

    public function installProjectContextRoles() {
        if (!dbConnection::isModuleInstalled('project')) {
            return true;
        }
        $roles = static::$projectContextRoles;
        if (!empty($roles)) {
            foreach ($roles as $data) {
                if ($this->installContextRole($data, 'project')) {
                    //TODO - record success
                } else {
                    //TODO - record failure
                }
            }
        }
        return true;
    }

}
