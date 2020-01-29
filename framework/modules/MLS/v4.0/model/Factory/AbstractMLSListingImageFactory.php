<?php
/**
 * Description of AbstractMLSListingImageFactory
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractMLSListingImageFactory extends GI_ModelFactory {

    protected static $dbType = 'rets';
    protected static $primaryDAOTableName = 'mls_listing_image';
    protected static $models = array();
    
    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return AbstractMLSListingImage
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new MLSListingImage($map);
                break;
        }
        return static::setFactoryClassName($model);
    }
    
    /**
     * @param type $typeRef
     * @return array
     */
    protected static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    
    /**
     * @param string $typeRef
     * @return AbstractMLSListingImage
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param AbstractMLSListing $mlsListing
     * @return boolean
     */
    public static function updateForMLSListing(AbstractMLSListing $mlsListing){
        $storeImages = $mlsListing->storeImages();
        $existingImages = $mlsListing->getImages();
        $deleteImages = array();
        foreach($existingImages as $existingImage){
            if($storeImages){
                $deleteImages[$existingImage->getProperty('id')] = $existingImage;
            } else {
                $deleteImages[$existingImage->getImageURL()] = $existingImage;
            }
        }
        
        $retsType = $mlsListing->getRetsType();
        $listingId = $mlsListing->getProperty('listing_id');
        $mlsListingId = $mlsListing->getProperty('id');
        $location = 1;
        if($storeImages){
            $location = 0;
        }
        $imageObjs = GI_RETS::getObjectsByResourceId($retsType, $listingId, 'Photo', 'Property', $location);
        $deleteTempDir = false;
        
        if($imageObjs){
            foreach($imageObjs as $imageObj){
                /*@var $imageObj PHRETS\Models\Object*/

                $size = $imageObj->getSize();
                $desc = $imageObj->getContentDescription();
                $subDesc = $imageObj->getContentSubDescription();
                $objectId = $imageObj->getObjectId();

                if(!$storeImages){
                    $imgLocation = $imageObj->getLocation();

                    if(isset($deleteImages[$imgLocation])){
                        $mlsListingImage = $deleteImages[$imgLocation];
                        unset($deleteImages[$imgLocation]);
                    } elseif(!empty($imgLocation)) {
                        $mlsListingImage = static::buildNewModel();
                        $mlsListingImage->setProperty('mls_listing_id', $mlsListingId);
                        $mlsListingImage->setProperty('img_src', $imgLocation);
                    } else {
                        continue;
                    }
                } else {
                    $matchingImageResults = static::search()
                            ->filter('mls_listing_id', $mlsListingId)
                            ->filter('pos', $objectId)
                            ->filter('content_size', $size)
                            ->select();
                    if($matchingImageResults){
                        $mlsListingImage = $matchingImageResults[0];
                    } else {
                        $contentType = $imageObj->getContentType();
                        $fileExt = File::getExtensionFromMimeType($contentType);
                        if(empty($fileExt) || !File::isValidImageExtension($fileExt)){
                            continue;
                        }
                        $fileName = $listingId . '_' . $objectId . '.' . $fileExt;
                        $localPath = 'listingImages/' . $listingId . '/';
                        File::createTempDataFolders('tempData/' . $localPath);
                        $deleteTempDir = true;
                        if(!empty($fileName)){
                            $filePath = 'tempData/' . $localPath . $fileName;
                            $localImage = fopen($filePath, 'w');
                            if(!fwrite($localImage, $imageObj->getContent())){
                                fclose($localImage);
                                return false;
                            }
                            fclose($localImage);
                            $s3Path = 'mlsListings/' . $localPath . $fileName;
                            if(File::saveToS3($filePath, $s3Path)){
                                $fileResults = FileFactory::search()
                                        ->setDBType(static::getDBType())
                                        ->filter('aws_s3_key', $s3Path)
                                        ->select();

                                if($fileResults){
                                    $file = $fileResults[0];
                                } else {
                                    FileFactory::setDBType(static::getDBType());
                                    $file = FileFactory::buildNewModel();
                                    FileFactory::resetDBType();
                                }

                                $file->setProperty('filename', $fileName);
                                $file->setProperty('file_size', $size);
                                $file->setProperty('aws_s3_bucket', ProjectConfig::getAWSBucket());
                                $file->setProperty('aws_region', ProjectConfig::getAWSRegion());
                                $file->setProperty('aws_s3_key', $s3Path);
                                $file->setProperty('display_name', $fileName);
                                $file->setProperty('attached', 1);
                                $file->setProperty('system', 1);
                                $fileDesc = $desc;
                                if(empty($fileDesc)){
                                    $fileDesc = $subDesc;
                                }
                                $file->setProperty('description', $fileDesc);
                                $file->save();

                                $fileId = $file->getProperty('id');

                                $mlsImageResults = static::search()
                                        ->filter('mls_listing_id', $mlsListingId)
                                        ->filter('file_id', $fileId)
                                        ->select();
                                if($mlsImageResults){
                                    $mlsListingImage = $mlsImageResults[0];
                                } else {
                                    $mlsListingImage = static::buildNewModel();
                                    $mlsListingImage->setProperty('mls_listing_id', $mlsListingId);
                                    $mlsListingImage->setProperty('file_id', $fileId);
                                }
                            }
                        }
                    }
                    $existingImageId = $mlsListingImage->getProperty('id');
                    if(isset($deleteImages[$existingImageId])){
                        unset($deleteImages[$existingImageId]);
                    }
                }
                $mlsListingImage->setProperty('content_size', $size);
                $mlsListingImage->setProperty('description', $desc);
                $mlsListingImage->setProperty('sub_description', $subDesc);
                $mlsListingImage->setProperty('pos', $objectId);
                $mlsListingImage->save();
            }
        }
        
        foreach($deleteImages as $deleteImage){
            $deleteImage->softDelete();
        }
        
        if($deleteTempDir){
            File::deleteDir('tempData/listingImages/' . $listingId);
        }
        
        return true;
    }
    
}
