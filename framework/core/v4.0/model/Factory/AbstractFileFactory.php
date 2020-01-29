<?php
/**
 * Description of AbstractFileFactory
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractFileFactory extends GI_ModelFactory {
    
    protected static $primaryDAOTableName = 'file';
    protected static $models = array();
    

    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return File
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'signature':
                $model = new FileSignature($map);
                break;
            case 'proj_mstone_sov':
                $model = new FileProjectMilestoneSOV($map);
                break;
            default:
                $model = new File($map);
                break;
        }
        return static::setFactoryClassName($model);
    }

    /**
     * @param type $typeRef - can be empty string
     * @return array
     */
    protected static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            case 'signature':
                $typeRefs = array('signature');
                break;
            case 'proj_mstone_sov':
                $typeRefs = array('proj_mstone_sov');
                break;
            default:
                $typeRefs = array('file');
                break;
        }
        return $typeRefs;
    }
    
    /**
     * @param string $typeRef
     * @return File
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param type $id - the id of the model
     * @param type $force - Whether or not you want to force the system to update the model, or to use available model from object pool
     * @return File
     */
    public static function getModelById($id, $force = false){ 
        return parent::getModelById($id, $force);
    }
    
    /**
     * @param string $uploaderName
     * @return File[]
     */
    public static function getTempFilesByUploaderName($uploaderName, $idsAsKey = false){
        $user = Login::getUser();
        $tempFolderId = FolderFactory::verifyTempFolder($user);
        
        $fileTableName = dbConfig::getDbPrefix() . 'file';
        $tempFiles = FileFactory::search()
                ->join('folder_link_to_file', 'file_id', $fileTableName, 'id','fltf')
                ->filter('uploader', $uploaderName)
                ->filter('fltf.folder_id', $tempFolderId)
                ->groupBy('fltf.file_id')
                ->select($idsAsKey);
        return $tempFiles;
    }
    
    /**
     * 
     * @param array $fileIdsFromForm
     * @param boolean $idsAsKey
     * @return File[]
     */
    public static function getFilesByIdsFromForm($fileIdsFromForm = array(), $idsAsKey = false){
        $files = array();
        
        foreach($fileIdsFromForm as $fileId){
            $file = static::getModelById($fileId);
            if($file){
                if($idsAsKey){
                    $files[$fileId] = $file;
                } else {
                    $files[] = $file;
                }
            }
        }
        
        return $files;
    }
    
    /**
     * @param string $signatureField
     * @param GI_Model $model
     * @param boolean $removeIfEmpty
     * @return boolean
     */
    public static function saveSignatureToModel($signatureField, GI_Model $model, $removeIfEmpty = false){
        $signature = filter_input(INPUT_POST, $signatureField);
        $signPrintName = filter_input(INPUT_POST, $signatureField . '_print_name');
        if($signature){
            $unaltered = filter_input(INPUT_POST, $signatureField . '_unaltered');
            $signatureFile = static::getSignatureFromModel($signatureField, $model);
            if($unaltered){
                if($signatureFile){
                    $curPrintName = $signatureFile->getProperty('file_signature.print_name');
                    if($curPrintName != $signPrintName){
                        $signatureFile->setProperty('file_signature.print_name', $signPrintName);
                        $signatureFile->setProperty('file_signature.ip_addr', Login::getIPAddr());
                        if(!$signatureFile->save()){
                            return false;
                        }
                    }
                }
                return true;
            }
            $signImgData = filter_input(INPUT_POST, $signatureField . '_img_data');
            $signImgType = filter_input(INPUT_POST, $signatureField . '_img_type');
            $signatureFolder = $model->getSubFolderByRef('signatures', array(
                'title' => 'Signatures'
            ));
            
            if(!$signatureFile){
                $signatureFile = static::buildNewModel('signature');
                $signatureFile->setProperty('uploader', $signatureField);
                $signatureFile->setProperty('attached', 1);
                $signatureFile->setProperty('system', 1);
            }
            
            list($mimeType, $encoding) = explode(';', $signImgType, 2);
            
            if($encoding != 'base64'){
                return false;
            }
            
            $decodedData = base64_decode($signImgData);
            $signImg = imagecreatefromstring($decodedData);
            if(!$signImg){
                return false;
            }
            imagealphablending($signImg, false);
            imagesavealpha($signImg, true);
            
            $signPath = 'tempData/user/' . Login::getUserId(true) . '/';
            File::createTempDataFolders($signPath);
            
            $signFileName = $signatureField;
            
            switch ($mimeType) {
                case 'image/jpeg':
                    $signFileName .= '.jpg';
                    $signFilePath = $signPath . $signFileName;
                    imagejpeg($signImg, $signFilePath, 100);
                    break;
                case 'image/png':
                    $signFileName .= '.png';
                    $signFilePath = $signPath . $signFileName;
                    imagepng($signImg, $signFilePath, 0);
                    break;
                case 'image/gif':
                    $signFileName .= '.gif';
                    $signFilePath = $signPath . $signFileName;
                    imagegif($signImg, $signFilePath);
                    break;
            }
            imagedestroy($signImg);
            $signFilePath = trim($signFilePath);
            $signS3Key = File::generateAWSKeyBase() . $signFileName; 
            $s3Bucket = ProjectConfig::getAWSBucket();
            if(!File::addFileToS3($signFilePath, $s3Bucket, $signS3Key)){
                return false;
            }
            $signatureFile->setProperty('display_name', $signFileName);
            $signatureFile->setProperty('filename', $signFileName);
            $signatureFile->setProperty('file_size', filesize($signFilePath));
            $signatureFile->setProperty('aws_s3_bucket', $s3Bucket);
            $signatureFile->setProperty('aws_s3_key', $signS3Key);
            $signatureFile->setProperty('aws_region', ProjectConfig::getAWSRegion());
            $signatureFile->setProperty('file_signature.print_name', $signPrintName);
            $signatureFile->setProperty('file_signature.ip_addr', Login::getIPAddr());
            if($signatureFile->save()){
                if(FolderFactory::linkFileToFolder($signatureFile, $signatureFolder)){
                    File::deleteTempDir();
                    return true;
                }
            }
        } elseif($removeIfEmpty){
            return static::removeSignatureFromModel($signatureField, $model);
        }
        return false;
    }
    
    public static function removeSignatureFromModel($signatureField, GI_Model $model){
        $signatureFile = static::getSignatureFromModel($signatureField, $model);
        if($signatureFile){
            $signatureFile->softDelete();
        }
        return true;
    }
    
    /**
     * @param string $signatureField
     * @param GI_Model $model
     * @return AbstractFile
     */
    public static function getSignatureFromModel($signatureField, GI_Model $model){
        $modelId = $model->getProperty('id');
        if($modelId){
            $signatureFolder = $model->getSubFolderByRef('signatures', array(
                'title' => 'Signatures'
            ));
            if($signatureFolder){
                $fileTable = static::getDbPrefix() . 'file';

                $signatureFileResult = static::search()
                        ->join('folder_link_to_file', 'file_id', $fileTable, 'id', 'FL')
                        ->filter('FL.folder_id', $signatureFolder->getProperty('id'))
                        ->filter('uploader', $signatureField)
                        ->filter('attached', 1)
                        ->filter('system', 1)
                        ->select();
                if($signatureFileResult){
                    return $signatureFileResult[0];
                }
            }
        }
        return NULL;
    }

    public static function getFileByFolderAndFilename(AbstractFolder $folder, $filename) {
        $fileTableName = static::getDbPrefix() . 'file';
        $search = static::search();
        $search->join('folder_link_to_file', 'file_id', $fileTableName, 'id', 'FLTF');
        $search->filter('FLTF.folder_id', $folder->getId())
                ->filter('filename', $filename);
        $results = $search->select();
        if (!empty($results)) {
            return $results[0];
        }
        return NULL;
    }

}
