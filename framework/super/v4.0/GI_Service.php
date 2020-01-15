<?php
/**
 * Description of GI_Service
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    2.0.0
 */

abstract class GI_Service {
    
    /**
     * 
     * @param GI_Model[] $models
     */
    public static function saveModels($models) {
        $savedModels = array();
        if (!empty($models)) {
            foreach ($models as $model) {
                if ($model->save()) {
                    $savedModels[] = $model;
                }
            }
            
        }
        return $savedModels;
    }
    
}