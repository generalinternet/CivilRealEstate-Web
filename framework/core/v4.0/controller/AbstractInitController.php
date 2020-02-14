<?php

abstract class AbstractInitController extends GI_Controller {
    
    protected $initActions = array(
        array(
            'controller' => 'init',
            'action' => 'BOSContact',
        ),
        array(
            'controller' => 'init',
            'action' => 'events',
        ),
        array(
            'controller' => 'init',
            'action' => 'contextRoles',
        ),
        array(
            'controller' => 'init',
            'action' => 'locationTags',
        )
    );
    
    public function addInitAction($controller, $action, $otherAttrs = array()){
        $attrs = array_merge(array(
            'controller' => $controller,
            'action' => $action
        ), $otherAttrs);
        $this->initActions[] = $attrs;
        return $this;
    }
    
    public function actionEvents($attributes) {
        $eventInstaller = new EventInstaller();
        $eventInstaller->installProjectEvents();
        $eventInstaller->installPurchaseOrderEvents();
        $eventInstaller->installSalesOrderEvents();
        $eventInstaller->installQnAEvents();
        $eventInstaller->installPaymentEvents();
        //TODO - alert w/ success/fail messages
        print_r('Done Events');
        die();
    }

    public function actionContextRoles($attributes) {
        $installer = new ContextRoleInstaller();
        $installer->installProjectContextRoles();

        //TODO - alert w/ success/fail messages
        print_r('Done Context Roles');
        die();
    }
    
    public function actionLocationTags($attributes) {
        $installer = new LocationTagInstaller();
        if (empty($installer)) {
            die('error - installer not found');
        }
        $installer->installTags();
        
        //Report
        //New Tags
        //New Links
        //Severed Links
        //Removed Tags
        //Failures
        
        die('Done Location Tags');
    }
    
    public function actionBOSContact($attributes) {
        $superAdminRole = RoleFactory::getRoleBySystemTitle('super_admin');
        if (empty($superAdminRole)) {
            print_r('ERROR - Super Admin Role Not Found.');
            die();
        }
        $users = UserFactory::getUsersByRole($superAdminRole);
        if (empty($users)) {
            print_r('ERROR - User with Super Admin Role Not Found.');
            die();
        }
        $user = $users[0];
        
        $indSearch = ContactFactory::search();
        $indSearch->filterByTypeRef('ind')
                ->filter('source_user_id', $user->getId());
        $indResults = $indSearch->select();
        if (!empty($indResults)) {
            $ind = $indResults[0];
        } else {
            $ind = ContactFactory::buildNewModel('ind');
            $ind->setProperty('source_user_id', $user->getProperty('id'));
            $ind->setProperty('pending', 0);
            $ind->setProperty('profile_complete', 1);
            $ind->setProperty('contact_ind.first_name', 'Super');
            $ind->setProperty('contact_ind.last_name', 'Admin');
            if (!$ind->save()) {
                print_r('ERROR - Could Not Save Super Admin Contact Ind');
                die();
            }
        }
        $orgSearch = ContactFactory::search();
        $orgSearch->filterByTypeRef('org')
                ->filter('org.title', 'General Internet');
        $orgResults = $orgSearch->select();

        if (!empty($orgResults)) {
            $org = $orgResults[0];
        } else {
            $org = ContactFactory::buildNewModel('org');
            $org->setProperty('display_name', 'General Internet');
            $org->setProperty('profile_complete', 1);
            $org->setProperty('contact_org.title', 'General Internet');
        }
        if (empty($org->getId()) || empty($org->getProperty('contact_org.primary_individual_id'))) {
            $org->setProperty('contact_org.primary_individual_id', $ind->getId());
            if (!$org->save()) {
                print_r('ERROR - Could Not Save BOS Admin Contact Org');
                die();
            }
        }
        if (!ContactRelationshipFactory::establishRelationship($org, $ind)) {
            print_r('ERROR - Could Not Save Relationship between Super Admin Contact Ind and BOS Admin Contact Org');
            die();
        }
        
        //contact cat (internal)
        $catSearch = ContactCatFactory::search();
        $catSearch->filterByTypeRef('internal')
                ->filter('contact_id', $org->getId());
        $catResult = $catSearch->select();
        if (!empty($catResult)) {
            $cat = $catResult[0];
        } else {
            $cat = ContactCatFactory::buildNewModel('internal');
            $cat->setProperty('contact_id', $org->getId());
            if (!$cat->save()) {
                print_r('ERROR - Could Not Save Contact Cat');
                die();
            }
        }
        
        print_r('Done Admin Contacts<br>');
    }
    
    public function actionRun($attributes){
        echo '<pre>';
        echo '<h2>Initializing</h2>';
        if(empty($this->initActions)){
            return 'No actions to complete';
        }
        foreach($this->initActions as $initAction){
            $path = GI_URLUtils::buildURL($initAction, true);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$path);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);          
            curl_close($ch);
            echo '<b>' . $path . '</b>';
            echo '<br/>';
            print_r($result);
            echo '<br/>';
            echo '<br/>';
        }
        echo '</pre>';
        exit();
    }

}
