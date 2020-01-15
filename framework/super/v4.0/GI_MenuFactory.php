<?php

class GI_MenuFactory{
    
    public static function getMenuView($type){
        $type = ucfirst(strtolower($type));
        $menuViewClass = $type.'MenuView';
        if(class_exists($menuViewClass)){
            $menuView = new $menuViewClass();
            return $menuView;
        }
        return NULL;
    }
}