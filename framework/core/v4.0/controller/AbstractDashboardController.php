<?php

abstract class AbstractDashboardController extends GI_Controller {

    public function actionIndex($attributes) {
        if (!Permission::verifyByRef('view_dashboard')) {
            if (Permission::verifyByRef('view_inventory_index')) {
                GI_URLUtils::redirect(array(
                    'controller' => 'inventory',
                    'action' => 'index'
                ));
            }
            GI_URLUtils::redirect(array(
                'controller' => 'contact',
                'action' => 'catIndex',
                'type' => 'internal'
            ));
        }
        $widgets = WidgetService::getDashboardWidgets();
        $view = new DashboardIndexView($widgets);
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['breadcrumbs'] = array(
            array(
                'link' => '.',
                'label' => 'Dashboard'
            )
        );
        return $returnArray;
    }

    public function actionGetWidgetContent($attributes) {
        if (!isset($attributes['ref'])) {
            return array('mainContent' => '');
        }
        $widget = WidgetService::getDashboardWidget($attributes['ref']);
        if (empty($widget)) {
            return array('mainContent' => '');
        }
        $returnArray = array();
        $returnArray['mainContent'] = $widget->getBodyContentHTMLView();
        $dynamicJS = $widget->getDynamicJS();
        if (!empty($dynamicJS)) {
            $returnArray['jqueryCallbackAction'] = $dynamicJS;
        }
        return $returnArray;
    }
    
}
