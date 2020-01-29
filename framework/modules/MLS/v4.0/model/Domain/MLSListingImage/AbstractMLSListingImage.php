<?php
/**
 * Description of AbstractMLSListingImage
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractMLSListingImage extends GI_Model {
    
    public function save() {
        if(RETS_MODIFY_ROWS){
            return parent::save();
        }
        return false;
    }
    
    public function softDelete() {
        if(RETS_MODIFY_ROWS){
            $file = $this->getFile();
            if(parent::softDelete()){
                if($file){
                    $file->softDelete();
                }
                return true;
            }
        }
        return false;
    }
    
    /**
     * @var AbstractMLSListing 
     */
    protected $mlsListing = NULL;
    
    /**
     * @var iFile
     */
    protected $file = NULL;
    
    /**
     * @return AbstractMLSListing
     */
    public function getMLSListing(){
        if(is_null($this->mlsListing)){
            $this->mlsListing = MLSListingFactory::getModelById($this->getProperty('mls_listing_id'));
        }
        
        return $this->mlsListing;
    }
    
    /**
     * @return iFile
     */
    public function getFile(){
        if(is_null($this->file)){
            $fileId = $this->getProperty('file_id');
            if($fileId){
                FileFactory::setDBType(MLSListingImageFactory::getDBType());
                $this->file = FileFactory::getModelById($fileId);
                FileFactory::getDBType();
            }
        }
        
        return $this->file;
    }
    
    public function getImageURL() {
        $imgSrc = $this->getProperty('img_src');
        if(!empty($imgSrc)){
            return $imgSrc;
        }
        
        $file = $this->getFile();
        if($file){
            return $file->getFileURL();
        }
        return NULL;
    }
    
    public function getDesc() {
        return $this->getProperty('description');
    }
    
    public function getSubDesc() {
        return $this->getProperty('sub_description');
    }
    
}
