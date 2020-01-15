<?php
/**
 * Description of AbstractGI_UploaderFactory
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractGI_UploaderFactory {
    
    /**
     * @param string $uploaderName
     * @param string $type
     * @return AbstractGI_Uploader
     */
    public static function buildUploader($uploaderName, $type = ''){
        $uploader = new GI_Uploader($uploaderName);
        if(!empty($type)){
            $uploader->setMimeTypes($type);
        }
        return $uploader;
    }
    
    /**
     * @param string $uploaderName
     * @return AbstractGI_Uploader
     */
    public static function buildImageUploader($uploaderName){
        return static::buildUploader($uploaderName, 'imgs');
    }
    
    /**
     * @param string $uploaderName
     * @param string $type
     * @return AbstractGI_UploaderBrowser
     */
    public static function buildFileBrowser($uploaderName, $type = ''){
        $browser = new GI_UploaderBrowser($uploaderName);
        if(!empty($type)){
            $browser->setMimeTypes($type);
        }
        return $browser;
    }
    
}
