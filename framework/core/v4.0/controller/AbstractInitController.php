<?php

abstract class AbstractInitController extends GI_Controller {
    
    public function actionEvents($attributes) {
        $eventInstaller = new EventInstaller();
        $eventInstaller->installProjectEvents();
        $eventInstaller->installPurchaseOrderEvents();
        $eventInstaller->installSalesOrderEvents();
        $eventInstaller->installQnAEvents();
        //TODO - alert w/ success/fail messages
        print_r('DONE');
        die();
    }

    public function actionContextRoles($attributes) {
        $installer = new ContextRoleInstaller();
        $installer->installProjectContextRoles();

        //TODO - alert w/ success/fail messages
        print_r('DONE');
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
        
        die('Complete');
    }

}
