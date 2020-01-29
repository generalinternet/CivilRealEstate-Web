<?php
/**
 * Description of AbstractFolderFactory
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractFolderFactory extends GI_ModelFactory {
    
    protected static $primaryDAOTableName = 'folder';
    protected static $models = array();

    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return Folder
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new Folder($map);
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
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    
    /**
     * @param string $typeRef
     * @return Folder
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param type $id - the id of the model
     * @param type $force - Whether or not you want to force the system to update the model, or to use available model from object pool
     * @return Folder
     */
    public static function getModelById($id, $force = false){ 
        return parent::getModelById($id, $force);
    }

    public static function linkFolderToFolder(AbstractFolder $parentFolder, AbstractFolder $childFolder) {
        $parentFolderId = $parentFolder->getProperty('id');
        $childFolderId = $childFolder->getProperty('id');
        $defualtDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
        $existingFolderLinkArray = $defualtDAOClass::getByProperties('folder_link_to_folder', array(
                    'p_folder_id' => $parentFolderId,
                    'c_folder_id' => $childFolderId
        ));
        if (!$existingFolderLinkArray) {
            $softDeletedFolderLinkArray = $defualtDAOClass::getByProperties('folder_link_to_folder', array(
                        'p_folder_id' => $parentFolderId,
                        'c_folder_id' => $childFolderId
                            ), 'client', 0);
            if ($softDeletedFolderLinkArray) {
                $softDeletedFolderLink = $softDeletedFolderLinkArray[0];
                $softDeletedFolderLink->setProperty('status', 1);
                if (!$softDeletedFolderLink->save()) {
                    return false;
                }
            } else {
                $folderLinkToFolder = new $defualtDAOClass('folder_link_to_folder');
                $folderLinkToFolder->setProperty('p_folder_id', $parentFolderId);
                $folderLinkToFolder->setProperty('c_folder_id', $childFolderId);
                if (!$folderLinkToFolder->save()) {
                    return false;
                }
            }
        }
        return true;
    }
    
    public static function linkFileToFolder(AbstractFile $file, AbstractFolder $folder, $pos = 0, $description = '') {
        $defualtDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
        $fileId = $file->getProperty('id');
        $folderId = $folder->getProperty('id');
        $existingLinks = $defualtDAOClass::getByProperties('folder_link_to_file', array(
            'folder_id' => $folderId,
            'file_id' => $fileId
        ));
        if (!empty($existingLinks)) {
            $link = $existingLinks[0];
            $saveLink = false;
            if($link->getProperty('position') != $pos){
                $link->setProperty('position', $pos);
                $saveLink = true;
            }
            
            if($link->getProperty('description') != $description){
                $link->setProperty('description', $description);
                $saveLink = true;
            }
            
            if($saveLink){
                return $link->save();
            }
            
            return true;
        }
        
        $newLink = new $defualtDAOClass('folder_link_to_file');
        $newLink->setProperty('folder_id', $folderId);
        $newLink->setProperty('file_id', $fileId);
        $newLink->setProperty('position', $pos);
        $newLink->setProperty('description', $description);
        if (!$newLink->save()) {
            return false;
        }
        return true;
    }
    
    public static function unlinkFileFromFolder(AbstractFile $file, AbstractFolder $folder) {
        $defualtDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
        $fileId = $file->getProperty('id');
        $folderId = $folder->getProperty('id');
        $existingLinks = $defualtDAOClass::getByProperties('folder_link_to_file', array(
            'folder_id' => $folderId,
            'file_id' => $fileId
        ));
        if (!empty($existingLinks)) {
            foreach ($existingLinks as $link) {
                if (!$link->softDelete()) {
                    return false;
                }
            }
        }
        
        $deleteFile = true;
        $otherFolderLinks = $defualtDAOClass::getByProperties('folder_link_to_file', array(
            'file_id' => $fileId
        ));
        
        if(!empty($otherFolderLinks)){
            $deleteFile = false;
        }
        
        $otherItemLinks = $defualtDAOClass::getByProperties('item_link_to_file', array(
            'file_id' => $fileId
        ));
        
        if(!empty($otherItemLinks)){
            $deleteFile = false;
        }
        
        if($deleteFile){
            $file->softDelete();
        }
        
        return true;
    }

    public static function linkFoldersToFolder(AbstractFolder $parentFolder, array $childFolders) {
        foreach ($childFolders as $childFolder) {
            if (!static::linkFolderToFolder($parentFolder, $childFolder)) {
                return false;
            }
        }
        return true;
    }

    public static function linkFolderToFolders(array $parentFolders, AbstractFolder $childFolder) {
        foreach ($parentFolders as $parentFolder) {
            if (!static::linkFolderToFolder($parentFolder, $childFolder)) {
                return false;
            }
        }
        return true;
    }
    
    public static function unlinkFoldersFromFolder(AbstractFolder $parentFolder, array $childFolders) {
        foreach ($childFolders as $childFolder) {
            if (!static::unlinkFolderFromFolder($parentFolder, $childFolder)) {
                return false;
            }
        }
        return true;
    }

    public static function unlinkFolderFromFolders(array $parentFolders, AbstractFolder $childFolder) {
        foreach ($parentFolders as $parentFolder) {
            if (!static::unlinkFolderFromFolder($parentFolder, $childFolder)) {
                return false;
            }
        }
        return true;
    }

    public static function unlinkFolderFromFolder(AbstractFolder $parentFolder, AbstractFolder $childFolder) {
        $parentFolderId = $parentFolder->getProperty('id');
        $childFolderId = $childFolder->getProperty('id');
        $defualtDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
        $folderLinkArray = $defualtDAOClass::getByProperties('folder_link_to_folder', array(
            'p_folder_id' => $parentFolderId,
            'c_folder_id' => $childFolderId
        ));
        if (!empty($folderLinkArray)) {
            foreach ($folderLinkArray as $folderLink) {
                if (!$folderLink->softDelete()) {
                    return false;
                }
            }
        }
        return true;
    }

    public static function getFolderByItemIdAndTableName($itemId, $tableName, $createNew = false, $newFolderProperties = array()) {
        if(!empty($itemId)){
            $defualtDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
            $itemLinkToFolderArray = $defualtDAOClass::getByProperties('item_link_to_folder', array(
                'table_name'=>$tableName,
                'item_id'=>$itemId
            ));
            if (!empty($itemLinkToFolderArray)) {
                $itemLinkToFolderDAO = $itemLinkToFolderArray[0];
                $folderId = $itemLinkToFolderDAO->getProperty('folder_id');
                //$folder = Folder::getById($folderId);
                $folder = static::getModelById($folderId);
                return $folder;
            } else {
                if ($createNew) {
                    //$folder = new Folder();
                    $folder = static::buildNewModel();
                    $folder->setProperty('is_root', 0);
                    $folder->setProperty('user_root', 0);
                    $folder->setProperty('system', 1);
                    foreach($newFolderProperties as $prop => $val){
                        $folder->setProperty($prop, $val);
                    }
                    if (!$folder->save()) {
                        GI_URLUtils::redirectToError(1000);
                    }
                    $itemLinkToFolderDAO = new $defualtDAOClass('item_link_to_folder');
                    $itemLinkToFolderDAO->setProperty('table_name', $tableName);
                    $itemLinkToFolderDAO->setProperty('item_id', $itemId);
                    $itemLinkToFolderDAO->setProperty('folder_id', $folder->getProperty('id'));
                    if (!$itemLinkToFolderDAO->save()) {
                        GI_URLUtils::redirectToError(1000);
                    }
                    return $folder;
                } else {
                    return NULL;
                }
            }
        }
        return NULL;
    }
    
    public static function unlinkFoldersByLinkId($linkId, $uid = NULL) {
        $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
        $folderLinkSearchArray = array(
            'id'=>$linkId
        );
        if (!empty($uid)) {
            $folderLinkSearchArray['uid'] = $uid;
        }
        $folderLinkArray = $defaultDAOClass('folder_link_to_folder', $folderLinkSearchArray);
        if (!empty($folderLinkArray)) {
            foreach ($folderLinkArray as $folderLink) {
                if (!$folderLink->softDelete()) {
                    return false;
                }
            }
        }
        return true;
    }


    public static function reLinkFoldersByLinkId($linkId) {
        $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
        $folderLinkToFolderDAO = $defaultDAOClass::getById('folder_link_to_folder', $linkId, 'client', 0);
        if (!empty($folderLinkToFolderDAO)) {
            //determine if parent folders are not recycled
            $pFolderId = $folderLinkToFolderDAO->getProperty('p_folder_id');
            $pFolderLinkArray = $defaultDAOClass::getByProperties('folder_link_to_folder', array(
                        'c_folder_id' => $pFolderId
            ));
            if (!empty($pFolderLinkArray)) {
                $pFolderLink = $pFolderLinkArray[0];
                $pFolderLinkId = $pFolderLink->getProperty('id');
            } else {
                $pFolderLinkId = NULL;
            }
            if (!empty($pFolderLinkId) && static::isFolderLinkedToRootFolder($pFolderLinkId)) {
//                        //folder's parents not recycled
                $folderLinkToFolderDAO->setProperty('status', 1);
                if ($folderLinkToFolderDAO->save()) {
                    return true;
                }
            } else {
//                        //folder's parents have been recycled
//                        //set folder's link p_folder_id to old root folder
                $folderId = $folderLinkToFolderDAO->getProperty('c_folder_id');
                $rootFolderId = static::findFolderRootFolderId($folderId);
                $folderLinkToFolderDAO->setProperty('p_folder_id', $rootFolderId);
                $folderLinkToFolderDAO->setProperty('status', 1);
                if ($folderLinkToFolderDAO->save()) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function unlinkFolderAndFileByLinkId($linkId, $uid = NULL) {
        $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
        $folderFileLinkSearchArray = array(
            'id' => $linkId
        );
        if (!empty($uid)) {
            $folderFileLinkSearchArray['uid'] = $uid;
        }
        $folderFileLinkArray = $defaultDAOClass('folder_link_to_file', $folderFileLinkSearchArray);
        if (!empty($folderFileLinkArray)) {
            foreach ($folderFileLinkArray as $folderFileLink) {
                if (!$folderFileLink->softDelete()) {
                    return false;
                }
            }
        }
        return true;
    }

    public static function relinkFolderAndFileByLinkId($linkId) {
        $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
       // $foldLinkToFile = Folder_Link_To_File::getById($linkId, 'client', 0);
        $foldLinkToFile = $defaultDAOClass::getById('folder_link_to_file',$linkId, 'client', 0);
        if ($foldLinkToFile) {
            //TODO determine if parent folders are not recycled
            $foldLinkToFileFoldId = $foldLinkToFile->getProperty('folder_id');
//            $folderLinkToFolderArray = Folder_Link_To_Folder::getByProperties(array(
//                        'c_folder_id' => $foldLinkToFileFoldId
//            ));
            $folderLinkToFolderArray = $defaultDAOClass::getByProperties('folder_link_to_folder', array(
              'c_folder_id'=>$foldLinkToFileFoldId
                    ));
            if ($folderLinkToFolderArray) {
                $folderLinkToFolder = $folderLinkToFolderArray[0];
                $folderLinkToFolderId = $folderLinkToFolder->getProperty('id');
            } else {
                $folderLinkToFolderId = NULL;
            }

            //if (!is_null($folderLinkToFolderId) && $this->isFolderLinkedToRootFolder($folderLinkToFolderId)) {
            if (!is_null($folderLinkToFolderId) && static::isFolderLinkedToRootFolder($folderLinkToFolderId)) {
                //File's parents not recycled
                $foldLinkToFile->setProperty('status', 1);
                if ($foldLinkToFile->save()) {
                    return true;
                }
            } else {
                //file's parents have been recycled
                //set file's link to folder p_folder_id to old root folder
                //$rootFolderId = $this->findFolderRootFolderId($foldLinkToFileFoldId);
                $rootFolderId = static::findFolderRootFolderId($foldLinkToFileFoldId);
                $foldLinkToFile->setProperty('folder_id', $rootFolderId);
                $foldLinkToFile->setProperty('status', 1);
                if ($foldLinkToFile->save()) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function isFolderLinkedToRootFolder($folderLinkToFolderId) {
        $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
        $folderLinkToFolder = $defaultDAOClass::getById('folder_link_to_folder', $folderLinkToFolderId);
        if (!empty($folderLinkToFolder)) {
            $pFolderId = $folderLinkToFolder->getProperty('p_folder_id');
            $pFolder = static::getModelById($pFolderId);
            $isRoot = $pFolder->getProperty('is_root');
            if ($isRoot == 1) {
                return true;
            } else {
                $topFolderLinkToFolderArray = $defaultDAOClass::getByProperties('folder_link_to_folder', array(
                    'c_folder_id'=>$pFolderId
                ));
                if ($topFolderLinkToFolderArray) {
                    $topFolderLinkToFolder = $topFolderLinkToFolderArray[0];
                    $topFolderLinkToFolderId = $topFolderLinkToFolder->getProperty('id');
                    return $this->isFolderLinkedToRootFolder($topFolderLinkToFolderId);
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    public static function findFolderRootFolderId($folderId) {
        $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
        //$folder = Folder::getById($folderId);
        $folder = static::getModelById($folderId);
        $isRoot = $folder->getProperty('is_root');
        if ((int) $isRoot == 1) {
            return $folderId;
        } else {
//            $folderLinkToFolderArray = Folder_Link_To_Folder::getByProperties(array(
//                        'c_folder_id' => $folderId
//            ));
            $folderLinkToFolderArray = $defaultDAOClass::getByProperties('folder_link_to_folder', array(
               'c_folder_id'=>$folderId 
            ));
            if ($folderLinkToFolderArray) {
                $parentFolderLink = $folderLinkToFolderArray[0];
                $parentFolderId = $parentFolderLink->getProperty('p_folder_id');
                return $this->findFolderRootFolderId($parentFolderId);
            } else {
//                $inactiveFolderLinkToFolderArray = Folder_Link_To_Folder::getByProperties(array(
//                            'c_folder_id' => $folderId
//                                ), 'client', 0);
                $inactiveFolderLinkToFolderArray = $defaultDAOClass::getByProperties(array(
                    'c_folder_id'=>$folderId
                ), 'client', 0);
                $parentFolderLink = $inactiveFolderLinkToFolderArray[0];
                $parentFolderId = $parentFolderLink->getProperty('p_folder_id');
                return $this->findFolderRootFolderId($parentFolderId);
            }
        }
    }

    public static function findTopFolderSharedWithUser($folderId, $userId, $highestFolderId) {
        //$folder = Folder::getById($folderId);
        $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
        $folder = static::getModelById($folderId);
        $isRoot = $folder->getProperty('is_root');
        $folderLinkToUserArray = $defaultDAOClass::getByProperties('folder_link_to_user', array(
            'user_id'=>$userId,
            'folder_id'=>$folderId
        ));
        if ($isRoot) {
            if ($folderLinkToUserArray) {
                $highestFolderId = $folderId;
            }
            return $highestFolderId;
        }
        $folderLinkToFolderArray = $defaultDAOClass::getByProperties('folder_link_to_folder', array(
            'c_folder_id'=>$folderId
        ));
        if ($folderLinkToFolderArray) {
            $pFolderId = $folderLinkToFolderArray[0]->getProperty('p_folder_id');
            $pFolderLinkToUserArray = $defaultDAOClass::getByProperties('folder_link_to_user',array(
                'user_id'=>$userId,
                'folder_id'=>$pFolderId
            ));
            if ($pFolderLinkToUserArray) {
                return static::findTopFolderSharedWithUser($pFolderId, $userId, $pFolderId);
            } else {
                return static::findTopFolderSharedWithUser($pFolderId, $userId, $highestFolderId);
            }
        } else {
            return $highestFolderId;
        }
    }

    public static function isUserLinkedToFolder($folderId, $userId) {
        $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
        //$folder = Folder::getById($folderId);
        $folder = static::getModelById($folderId);
        $folderUid = $folder->getProperty('uid');
        if ($folderUid == $userId) {
            return true;
        }
//        $folderLinkWithUserArray = Folder_Link_To_User::getByProperties(array(
//                    'user_id' => $userId,
//                    'folder_id' => $folderId
//        ));
        $folderLinkWithUserArray = $defaultDAOClass::getByProperties('folder_link_to_user',array(
            'user_id'=>$userId,
            'folder_id'=>$folderId
        ));
        if ($folderLinkWithUserArray) {
            return true;
        }
        // $topLinkFolderId = $this->findTopFolderSharedWithUser($folderId, $userId, $folderId);
        $topLinkFolderId = static::findTopFolderSharedWithUser($folderId, $userId, $folderId);
        if ($topLinkFolderId != $folderId) {
            return true;
        }
        return false;
    }
    
    public static function moveFolderToFolder(AbstractFolder $folder, AbstractFolder $targetFolder) {
        $cFolderId = $folder->getProperty('id');
        $pFolderId = $targetFolder->getProperty('id');
        $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
        $folderLinkToFolderArray = $defaultDAOClass::getByProperties('folder_link_to_folder', array(
            'c_folder_id'=>$cFolderId,
            'p_folder_id'=>$pFolderId,
        ));
        if (!empty($folderLinkToFolderArray)) {
            return true;
        }
        $existingFolderLinkArray = $defaultDAOClass::getByProperties('folder_link_to_folder', array(
            'c_folder_id'=>$cFolderId
        ));
        if (!empty($existingFolderLinkArray)) {
            $existingFolderLink = $existingFolderLinkArray[0];
            $existingFolderLink->setProperty('p_folder_id', $pFolderId);
            if ($existingFolderLink->save()) {
                return true;
            }
        } else {
            return static::linkFolderToFolder($targetFolder, $folder);
        }
        return false;
    }
    
    public static function  moveFileToFolder(AbstractFile $file, AbstractFolder $targetFolder) {
        $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
        $fileId = $file->getProperty('id');
        $folderId = $targetFolder->getProperty('id');
        $folderLinkToFileArray = $defaultDAOClass::getByProperties('folder_link_to_file', array(
            'folder_id'=>$folderId,
            'file_id'=>$fileId
        ));
        if (!empty($folderLinkToFileArray)) {
            return true;
        }
        $existingFolderLinkArray = $defaultDAOClass::getByProperties('folder_link_to_file', array(
            'file_id'=>$fileId
        ));
        if (!empty($existingFolderLinkArray)) {
            $existingFolderLink = $existingFolderLinkArray[0];
            $existingFolderLink->setProperty('folder_id', $folderId);
            return $existingFolderLink->save();
        } else {
            return static::linkFileToFolder($file, $targetFolder);
        }
    }

    public static function deleteTree($rootFolderId) {
        //find all the files in the folder and delete them
        //$folder = Folder::getById($rootFolderId);
        $folder = static::getModelById($rootFolderId);
        $folder->setProperty('status', 0);
        $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
        if ($folder->save()) {
//            $folderLinkToFilesArray = Folder_Link_To_File::getByProperties(array(
//                        'folder_id' => $rootFolderId
//            ));
            $folderLinkToFilesArray = $defaultDAOClass::getByProperties('folder_link_to_file', array(
                'folder_id'=>$rootFolderId
            ));
            if ($folderLinkToFilesArray) {
                foreach ($folderLinkToFilesArray as $fltf) {
                    $fileId = $fltf->getProperty('file_id');
                    $this->deleteFile($fileId);
                }
            }
            //find all the subfolders and delete the links to them
//            $folderLinkToFoldersArray = Folder_Link_To_Folder::getByProperties(array(
//                        'p_folder_id' => $rootFolderId
//            ));
            $folderLinkToFoldersArray = $defaultDAOClass::getByProperties('folder_link_to_folder', array(
                'p_folder_id'=>$rootFolderId
            ));
            if ($folderLinkToFoldersArray) {
                foreach ($folderLinkToFoldersArray as $foldltf) {
                    $cFolderId = $foldltf->getProperty('c_folder_id');
                    $foldltf->setProperty('status', 0);
                    if ($foldltf->save()) {
                        return static::deleteTree($cFolderId);
                    } else {
                        return false;
                    }
                }
            } else {
                return true;
            }
        }
    }
    
    /**
     * @param AbstractFolder $folder
     * @param string $ref
     * @param boolean $system
     * @param boolean $root
     * @return AbstractFolder
     */
    public static function getSubFolderByRef(AbstractFolder $folder, $ref = NULL, $system = false, $root = false){
        $folderId = $folder->getProperty('id');
        $folderTableName = dbConfig::getDbPrefix(). 'folder';
        $folderSearch = static::search()
                ->join('folder_link_to_folder', 'c_folder_id', $folderTableName, 'id', 'fltf')
                ->filter('fltf.p_folder_id', $folderId)
                ->filter('ref', $ref);
        
        if($system){
            $folderSearch->filter('system', 1);
        }
        
        if($root){
            $folderSearch->filter('is_root', 1);
        }
        
        $folders = $folderSearch->select();
        if($folders){
            $folder = $folders[0];
            return $folder;
        }
        return NULL;
    }
    
    /**
     * @param AbstractFolder $folder
     * @return AbstractFolder[]
     */
    public static function getSubFolders(AbstractFolder $folder) {
        $folderId = $folder->getProperty('id');
        $folderTableName = dbConfig::getDbPrefix(). 'folder';
        $folders = static::search()
                ->join('folder_link_to_folder', 'c_folder_id', $folderTableName, 'id', 'fltf')
                ->filter('fltf.p_folder_id', $folderId)
                ->select();
        return $folders;
    }
    
    /**
     * @param AbstractFolder $folder
     * @return AbstractFolder[]
     */
    public static function getSubFolderCount(AbstractFolder $folder) {
        $folderId = $folder->getProperty('id');
        $folderTableName = dbConfig::getDbPrefix(). 'folder';
        $folders = static::search()
                ->join('folder_link_to_folder', 'c_folder_id', $folderTableName, 'id', 'fltf')
                ->filter('fltf.p_folder_id', $folderId)
                ->count();
        return $folders;
    }
    
    /**
     * @param AbstractFolder $folder
     * @return AbstractFolder[]
     */
    public static function getParentFolders(AbstractFolder $folder) {
        $folderId = $folder->getProperty('id');
        $search = static::search();
        $folderTableName = $search->prefixTableName('folder');
        $folders = $search->join('folder_link_to_folder', 'p_folder_id', $folderTableName, 'id', 'fltf')
                ->filter('fltf.c_folder_id', $folderId)
                ->select();
        return $folders;
    }
    
    /**
     * @param AbstractFolder $folder
     * @return AbstractFolder
     */
    public static function getParentFolder(AbstractFolder $folder){
        $folderId = $folder->getProperty('id');
        $search = static::search();
        $folderTableName = $search->prefixTableName('folder');
        $result = $search->join('folder_link_to_folder', 'p_folder_id', $folderTableName, 'id', 'fltf')
                ->filter('fltf.c_folder_id', $folderId)
                ->setItemsPerPage(1)
                ->select();
        if($result){
            return $result[0];
        }
        return NULL;
    }
    
    /**
     * @param AbstractFolder $folder
     * @return AbstractFile[]
     */
    public static function getFiles(AbstractFolder $folder, $idsAsKey = false) {
        $folderId = $folder->getProperty('id');
        $fileTablename = dbConfig::getDbPrefix() . 'file';
        $files = FileFactory::search()
                ->join('folder_link_to_file', 'file_id', $fileTablename, 'id', 'fltf')
                ->filter('fltf.folder_id', $folderId)
                ->orderBy('fltf.position', 'ASC')
                ->orderBy('last_mod', 'DESC')
                ->select($idsAsKey);
        return $files;
    }

    //TODO - change to use UserFactory once it becomes available
    public static function linkFolderAndUser($user, AbstractFolder $folder) {
        $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');   
        $userId = $user->getProperty('id');
        $folderId = $folder->getProperty('id');
        $existingLinkArray = $defaultDAOClass::getByProperties('folder_link_to_user', array(
            'user_id'=>$userId,
            'folder_id'=>$folderId
        ));
        if (!empty($existingLinkArray)) {
            return true;
        }
        $inactiveFolderLinkArray = $defaultDAOClass::getByProperties('folder_link_to_user', array(
            'user_id'=>$userId,
            'folder_id'=>$folderId,
            'status'=>0
        ));
        if (!empty($inactiveFolderLinkArray)) {
            $inactiveLink = $inactiveFolderLinkArray[0];
            $inactiveLink->setProperty('status', 1);
            if ($inactiveLink->save()) {
                return true;
            }
        }
        $newLink = new $defaultDAOClass('folder_link_to_user');
        $newLink->setProperty('folder_id', $folderId);
        $newLink->setProperty('user_id', $userId);
        if ($newLink->save()) {
            return true;
        }
        return false;
    }
    
    public static function getFoldersByFile(AbstractFile $file) {
        $fileId = $file->getProperty('id');
        $folderTableName = dbConfig::getDbPrefix() . 'folder';
        $folders = static::search()
                ->join('folder_link_to_file', 'folder_id', $folderTableName, 'id', 'fltf')
                ->filter('fltf.file_id', $fileId)
                ->select();
        return $folders;
    }
    
    public static function getFoldersByLinkedModel(GI_Model $model) {
        $tableName = $model->getTableName();
        $itemId = $model->getProperty('id');
        $folderTableName = dbConfig::getDbPrefix() . 'folder';
        $folders = static::search()
                ->join('item_link_to_folder', 'folder_id', $folderTableName, 'id', 'iltf')
                ->filter('iltf.table_name', $tableName)
                ->filter('iltf.item_id', $itemId)
                ->groupBy('iltf.folder_id')
                ->select();
        return $folders;
    }
    
    public static function linkModelToFolder(GI_Model $model, AbstractFolder $folder) {
        $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass'); 
        $itemId = $model->getProperty('id');
        $tableName = $model->getTableName();
        $folderId = $folder->getProperty('id');
        $existingLinks = $defaultDAOClass::getByProperties('item_link_to_folder', array(
            'table_name'=>$tableName,
            'folder_id'=>$folderId,
            'item_id'=>$itemId
        ));
        if (!empty($existingLinks)) {
            return true;
        }
        $softDeletedLinks = $defaultDAOClass::getByProperties('item_link_to_folder', array(
            'table_name'=>$tableName,
            'folder_id'=>$folderId,
            'item_id'=>$itemId,
            'status'=>0
        ));
        
        if (!empty($softDeletedLinks)) {
            $softDeletedLink = $softDeletedLinks[0];
            $softDeletedLink->setProperty('status', 1);
            if ($softDeletedLink->save()) {
                return true;
            }
        }
        $newLink = new $defaultDAOClass('item_link_to_folder');
        $newLink->setProperty('table_name', $tableName);
        $newLink->setProperty('folder_id', $folderId);
        $newLink->setProperty('item_id', $itemId);
        if ($newLink->save()) {
            return true;
        }
        return false;
    }

    public static function unlinkModelFromFolder(GI_Model $model, AbstractFolder $folder) {
        $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
        $itemId = $model->getProperty('id');
        $tableName = $model->getTableName();
        $folderId = $folder->getProperty('id');
        $existingLinks = $defaultDAOClass::getByProperties('item_link_to_folder', array(
                    'table_name' => $tableName,
                    'folder_id' => $folderId,
                    'item_id' => $itemId
        ));
        if (empty($existingLinks)) {
            return true;
        }
        foreach ($existingLinks as $existingLink) {
            if (!$existingLink->softDelete()) {
                return false;
            }
        }
        return true;
    }

    public static function determineFolderLinkedTableName(AbstractFolder $folder) {
        $folderId = $folder->getProperty('id');
        $tableName = '';
        $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
        $itemLinkToFolderArray = $defaultDAOClass::getByProperties('item_link_to_folder', array(
                    'folder_id' => $folderId
        ));
        if (!empty($itemLinkToFolderArray)) {
            $itemLink = $itemLinkToFolderArray[0];
            $tableName = $itemLink->getProperty('table_name');
        }
        return $tableName;
    }

    public static function verifyUserRootFolder(AbstractUser $user = NULL) {
        $currentUserId = Login::getUserId();
        $rootUser = UserFactory::getRootUser();
        $rootUserId = $rootUser->getProperty('id');
        if (empty($user)) {
            $userId = $currentUserId;
        } else {
            $userId = $user->getProperty('id');
        }
        if ($userId == $currentUserId) {
            $value = SessionService::getValue(array(
                'folders',
                'my_files'
            ));
            if (!empty($value) && $value === '1') {
                return true;
            }
        } else if ($userId == $rootUserId) {
            $value = SessionService::getValue(array(
                'folders',
                'my_files_root_user'
            ));
            if (!empty($value) && $value === '1') {
                return true;
            }
        }
        $rootFolderArray = static::search()
                ->filter('uid', $userId)
                ->filter('user_root', 1)
                ->select();
        if (!$rootFolderArray) {
            $rootFolder = static::buildNewModel();
            $rootFolder->setProperty('is_root', 1);
            $rootFolder->setProperty('user_root', 1);
            $rootFolder->setProperty('system', 1);
            $rootFolder->setProperty('title', 'my files');
            $rootFolder->setProperty('ref', 'my_files');
            $rootFolder->setProperty('user_id', $userId);
            if ($rootFolder->save()) {
                if ($userId == $currentUserId) {
                    SessionService::setValue(array(
                        'folders',
                        'my_files'
                    ), '1');
                } else if ($userId == $rootUserId) {
                    SessionService::setValue(array(
                        'folders',
                        'my_files_root_user'
                    ), '1');
                }
                return true;
            }
        } else {
            if ($userId == $currentUserId) {
                SessionService::setValue(array(
                    'folders',
                    'my_files'
                        ), '1');
            } else if ($userId == $rootUserId) {
                SessionService::setValue(array(
                    'folders',
                    'my_files_root_user'
                        ), '1');
            }
            return true;
        }
        return false;
    }

    public static function verifyUserProfilePicturesFolder(AbstractUser $user = NULL) {
        $currentUserId = Login::getUserId();
        if (empty($user)) {
            $userId = $currentUserId;
        } else {
            $userId = $user->getProperty('id');
        }
        if ($userId == $currentUserId) {
            $value = SessionService::getValue(array(
                'folders',
                'profile_pictures'
            ));
            if (!empty($value) && $value === '1') {
                return true;
            }
        }
        $rootFolderArray = static::search()
                ->filter('uid', $userId)
                ->filter('is_root', 1)
                ->filter('user_root', 1)
                ->select();
        if ($rootFolderArray) {
            $rootFolder = $rootFolderArray[0];
        } else {
            if ($userId == $currentUserId) {
                SessionService::setValue(array(
                    'folders',
                    'my_files'
                        ), '0');
                SessionService::setValue(array(
                    'folders',
                    'profile_pictures'
                        ), '0');
            }
            return false;
        }
        $ppFolder = $rootFolder->getProfilePictureFolder();
        if($ppFolder){
            if ($userId == $currentUserId) {
                SessionService::setValue(array(
                    'folders',
                    'profile_pictures'
                ), '1');
            }
            return true;
        }
        
        return false;
    }

    public static function verifyTempFolder(AbstractUser $user = NULL) {
        if (empty($user)) {
            $rootUser = UserFactory::getRootUser();
            $rootUserId = $rootUser->getProperty('id');
            $userId = $rootUserId;
        } else {
            $userId = $user->getProperty('id');
        }
        $userSessionKey = 'user_' . $userId;
        $tempFolderId = SessionService::getValue(array(
            $userSessionKey,
            'temp_folder_id'
        ));
        if (!empty($tempFolderId)) {
            return $tempFolderId;
        }
        $rootFolderArray = static::search()
                ->filter('uid', $userId)
                ->filter('user_root', 1)
                ->select();
        if ($rootFolderArray) {
            $rootFolder = $rootFolderArray[0];
            $subFolders = static::getSubFolders($rootFolder);

            if (!empty($subFolders)) {
                foreach ($subFolders as $subFolder) {
                    $subFolderTitle = $subFolder->getProperty('title');
                    if ($subFolderTitle === 'temp') {
                        $subFolderId = $subFolder->getProperty('id');
                        SessionService::setValue(array(
                            $userSessionKey,
                            'temp_folder_id'
                        ), $subFolderId);
                        return $subFolderId;
                    }
                }
            }
            $newFolder = static::buildNewModel();
            $newFolder->setProperty('is_root', '0');
            $newFolder->setProperty('user_root', '0');
            $newFolder->setProperty('system', '1');
            $newFolder->setProperty('title', 'temp');
            $newFolder->setProperty('ref', 'temp');
            if ($newFolder->save()) {
                $linkResult = static::linkFolderToFolder($rootFolder, $newFolder);
                if ($linkResult) {
                    $newFolderId = $newFolder->getProperty('id');
                    SessionService::setValue(array(
                        $userSessionKey,
                        'temp_folder_id'
                    ), $newFolderId);
                    return $newFolderId;
                }
            }
        }
        return NULL;
    }

    public static function getUserRootFolder(AbstractUser $user) {
        $userId = $user->getProperty('id');
        $folders = static::search()
                ->filter('user_id', $userId)
                ->filter('user_root', 1)
                ->filter('is_root', 1)
                ->select();
        if (!empty($folders)) {
            $folder = $folders[0];
            if(empty($folder->getProperty('ref'))){
                $folder->save();
            }
            return $folder;
        } else {
            $folder = static::buildNewModel();
            $folder->setProperty('user_id', $userId);
            $folder->setProperty('user_root', 1);
            $folder->setProperty('is_root', 1);
            $folder->setProperty('title', 'my files');
            $folder->setProperty('ref', 'my_files');
            if($folder->save()){
                return $folder;
            }
        }
        return NULL;
    }
    
    public static function putUploadedFilesInTargetFolder(GI_Uploader $uploader){
        $remainingFiles = $uploader->getFiles(true, true);
        $uploadedFileIds = $uploader->getFileIdsFromForm();
        
        $targetFolder = $uploader->getTargetFolder();
        if(!$targetFolder){
            return false;
        }
        $user = Login::getUser();
        $tempFolderId = static::verifyTempFolder($user);
        $tempFolder = static::getModelById($tempFolderId);
        $pos = 0;
        if($uploadedFileIds){
            foreach($uploadedFileIds as $fileId){
                if(!isset($remainingFiles[$fileId])){
                    //missing file
                    return false;
                }
                $file = $remainingFiles[$fileId];
                unset($remainingFiles[$fileId]);

                if(!static::linkFileToFolder($file, $targetFolder, $pos)){
                    return false;
                }
                $pos++;

                static::unlinkFileFromFolder($file, $tempFolder);

                $file->setProperty('attached', 1);
                if(!$file->save()){
                    return false;
                }
            }
        }
        
        if($remainingFiles){
            foreach($remainingFiles as $remainingFile){
                static::unlinkFileFromFolder($remainingFile, $tempFolder);
                static::unlinkFileFromFolder($remainingFile, $targetFolder);
            }
        }
        return true;
    }
    
}
