<?php

class AbstractFileController extends GI_Controller {
    
    //AJAX
    public function actionSaveUploadData($attributes) {
        if ((!isset($attributes['ajax']) || $attributes['ajax'] != 1) || !isset($attributes['key']) || !isset($attributes['size']) || !isset($attributes['displayName']) || !isset($attributes['uploaderName'])) {
            return array('content' => NULL);
        }
        
        $targetFolderId = NULL;
        if(isset($attributes['targetFolderId'])){
            $targetFolderId = $attributes['targetFolderId'];
        }
        
        $targetDocId = NULL;
        if(isset($attributes['targetDocId'])){
            $targetDocId = $attributes['targetDocId'];
        }
        
        $uploaderName = $attributes['uploaderName'];
        
        $fileView = NULL;
        if(isset($attributes['fileView'])){
            $fileView = $attributes['fileView'];
        }
        
        $file = FileFactory::buildNewModel();
        $file->setProperty('aws_s3_bucket', ProjectConfig::getAWSBucket());
        $file->setProperty('aws_region', ProjectConfig::getAWSRegion());
        $key = $attributes['key'];
        $fileSize = $attributes['size'];
        $displayName = $attributes['displayName'];
        $file->setProperty('aws_s3_key', $key);
        $file->setProperty('display_name', $displayName);
        $file->setProperty('filename', $displayName);
        $file->setProperty('file_size', $fileSize);
        $saveInTemp = false;
        if(isset($attributes['attach']) && $attributes['attach']){
            $file->setProperty('attached', 1);
        } else {
            $file->setProperty('attached', 0);
            $saveInTemp = true;
        }
        $file->setProperty('uploader', $uploaderName);
        if ($file->save()) {
            if($saveInTemp || empty($targetFolderId)){
                $user = Login::getUser();
                $folderId = FolderFactory::verifyTempFolder($user);
            } else {
                $folderId = $targetFolderId;
            }
            
            $pos = 0;
            if(isset($attributes['pos'])){
                $pos = $attributes['pos'];
            }
            
            $fileId = $file->getProperty('id');
            if (!empty($targetDocId)) {
                $document = DocumentFactory::getModelById($targetDocId);
                $document->setProperty('file_id', $fileId);
                if (!$document->save()) {
                    return array('content' => 0);
                }
            }
            $folder = FolderFactory::getModelById($folderId);
            $linkResult = FolderFactory::linkFileToFolder($file, $folder, $pos);
            if (!$linkResult) {
                return array('content' => 0);
            }
            
            $uploader = NULL;
            if($saveInTemp){
                $uploader = GI_UploaderFactory::buildUploader($uploaderName);
            }
            
            $fileView = $file->getView($fileView, $uploader);
            return array(
                'content' => $fileView->getHTMLView(),
                'fileId' => $fileId
            );
        } else {
            return array('content' => 0);
        }
    }
    
    public function actionDeleteFolderLink($attributes) {
        if ((!isset($attributes['ajax']) || !$attributes['ajax'] == 1) || !isset($attributes['fileId']) || !isset($attributes['folderId'])) {
            return array('content' => NULL);
        }
        $fileId = $attributes['fileId'];
        $folderId = $attributes['folderId'];
        if (isset($attributes['documentId'])) {
            $documentId = $attributes['documentId'];
            $document = DocumentFactory::getModelById($documentId);
            if (!empty($document)) {
                $document->setProperty('document.file_id', '');
                if (!$document->save()) {
                    return array('content' => 0);
                }
            }
        }
        $folder = FolderFactory::getModelById($folderId);
        $file = FileFactory::getModelById($fileId);
        $unlinkResult = FolderFactory::unlinkFileFromFolder($file, $folder);
        if (!$unlinkResult) {
            return array('content' => 0);
        }
        return array('content' => 1);
    }

    //AJAX
    public function actionRecycle($attributes) {
        $result = array('content' => 'false');
        if (isset($attributes['linkId'])) {
            $linkId = $attributes['linkId'];
        } else {
            $linkId = NULL;
        }

        if (!is_null($linkId) && isset($attributes['type'])) {
            $type = $attributes['type'];
            if ($type === 'folder') {
//                //allow user to remove a folder they previously added to a folder
                $unlinkResult = FolderFactory::unlinkFoldersByLinkId($linkId, Login::getUserId());
            } else if ($type === 'file') {
                $unlinkResult = FolderFactory::unlinkFolderAndFileByLinkId($linkId, Login::getUserId());
            }
            if (!$unlinkResult) {
                $result['content'] = 0;
            } else {
                $result['content'] = 1;
            }
        }
        return $result;
    }

    //AJAX
    public function actionUnrecycle($attributes) {
        $result = array('content' => 0);
        if (isset($attributes['linkId'])) {
            $linkId = $attributes['linkId'];
        } else {
            $linkId = NULL;
        }
        if (!is_null($linkId) && isset($attributes['type'])) {
            $type = $attributes['type'];
            if ($type === 'folder') {
                $relinkResult = FolderFactory::reLinkFoldersByLinkId($linkId);
            } else if ($type === 'file') {
                $relinkResult = FolderFactory::relinkFolderAndFileByLinkId($linkId);
            }
            if ($relinkResult) {
                $result['content'] = 1;
            } else {
                $result['content'] = 0;
            }
        }
        return $result;
    }

//AJAX
    public function actionCreateFolder($attribtues) {
        if (!isset($attribtues['ajax']) || !$attribtues['ajax'] == 1) {
            return array('content' => NULL);
        }
        //TODO: Change the source of folderId and newFolderName to POST after AJAX jQuery stuff completed
        if (isset($attribtues['folderId']) && isset($attribtues['newFolderName'])) {
            $pFolderId = $attribtues['folderId'];
            $newFolderName = $attribtues['newFolderName'];

            //$newFolder = new Folder();
            $newFolder = FolderFactory::buildNewModel();
            $newFolder->setProperty('title', $newFolderName);
            $newFolder->setProperty('is_root', 0);
            $newFolder->setProperty('user_root', 0);

            if ($newFolder->save()) {
//                $cFolderId = $newFolder->getProperty('id');
//                $fltf = new Folder_Link_To_Folder();
//                $fltf->setProperty('p_folder_id', $pFolderId);
//                $fltf->setProperty('c_folder_id', $cFolderId);
                $pFolder = FolderFactory::getModelById($pFolderId);
                $linkResult = FolderFactory::linkFolderToFolder($pFolder, $newFolder);
                if ($linkResult) {
                    //   $listItemView = $this->createListItemView($newFolder, $fltf);
                    return array('content' => 1);
                } else {
                    $newFolder->softDelete();
                    return array('content' => 0);
                }
            }
        } else {
            return array('content' => 0);
        }
    }

    //AJAX
    public function actionShareFolder($attributes) {
        if ((!isset($attributes['ajax']) || !$attributes['ajax'] == 1) || !isset($attributes['folderId']) || !isset($attributes['userId'])) {
            return array('content' => NULL);
        }
        $targetUserId = $attributes['userId'];
        $folderId = $attributes['folderId'];

        //verify that the user sharing the folder is the owner
        //$folder = Folder::getById($folderId);
        $folder = FolderFactory::getModelById($folderId);
        if ($folder) {
            $uid = $folder->getProperty('uid');
            if ((int) $uid != (int) Login::getUserId()) {
                return array('content' => 0);
            }
            $user = UserFactory::getModelById($targetUserId);
            if (!empty($user) && FolderFactory::linkFolderAndUser($user, $folder)) {
                return array('content'=>1);
            }
        }
        return array('content'=>0);
    }

    //AJAX
    public function actionEdit($attributes) {
        if (!isset($attributes['id']) || empty($attributes['id'])) {
            return array('content' => 0);
        }
        
        $fileId = $attributes['id'];
        $file = FileFactory::getModelById($fileId);
        if (empty($file)) {
            GI_URLUtils::redirectToError(4001);
        }
        $form = new GI_Form('edit_file');
        $view = new FileEditFormView($form, $file);
        $success = 0;
        if ($form->wasSubmitted() && $form->validate()) {
            $newName = filter_input(INPUT_POST, 'new_name');
            $curExtension = $file->getExtension();
            $newNameWithExt = $newName . '.' . $curExtension;
            $file->setProperty('display_name', $newNameWithExt);
            
            $file->setPropertyIfPostIsset('title_tag', 'title_tag');
            $file->setPropertyIfPostIsset('alt_tag', 'alt_tag');
            $file->setPropertyIfPostIsset('description', 'description');
            if($file->save()){
                if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
                    $success = 1;
                    $uploader = NULL;
                    if (isset($attributes['uploader']) && !empty($attributes['uploader'])) {
                        $uploader = GI_UploaderFactory::buildUploader($attributes['uploader']);
                    }
                    $iconView = $file->getView('thumbnail', $uploader);
                }
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if(isset($iconView)){
            $returnArray['jqueryAction'] = 'replaceFileIconView(' . $fileId . ', "' . addslashes($iconView->getHTMLView()) . '");';
        }
        return $returnArray;
    }
    
    //AJAX
    public function actionPositionFolderLink($attributes) {
        if ((!isset($attributes['ajax']) || !$attributes['ajax'] == 1) || !isset($attributes['fileId']) || !isset($attributes['folderId']) || !isset($attributes['position'])) {
            return array('content' => NULL);
        }
        $fileId = $attributes['fileId'];
        $folderId = $attributes['folderId'];
        $position = $attributes['position'];
        
        $defualtDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
        $existingLinks = $defualtDAOClass::getByProperties('folder_link_to_file', array(
            'folder_id' => $folderId,
            'file_id' => $fileId
        ));
        if($existingLinks){
            $existingLink = $existingLinks[0];
            if($existingLink->getProperty('position') != $position){
                $existingLink->setProperty('position', $position);
                if (!$existingLink->save()) {
                    return array('content' => 0);
                }
            }
        }
        return array('content' => 1);
    }
    

    //AJAX
    public function actionMove($attributes) {
        if ((!isset($attributes['ajax']) || $attributes['ajax'] != 1) || !isset($attributes['type']) || (!isset($attributes['id']) && !isset($attributes['ids'])) || !isset($attributes['targetId'])) {
            return array('content' => 0);
        }
        $targetId = $attributes['targetId'];
        if(isset($attributes['id'])){
            $ids = array($attributes['id']);
        } elseif($attributes['ids']){
            $ids = explode(',', $attributes['ids']);
        }
        $type = $attributes['type'];
        $userId = Login::getUserId();
        //verify that user can move file/folder into target dir
        $perm = FolderFactory::isUserLinkedToFolder($targetId, $userId);
        $targetFolder = FolderFactory::getModelById($targetId);
        $moveResult = false;
        if ($type === 'file') {
            if ($perm) {
                $allFilesMoved = true;
                foreach($ids as $id){
                    $file = FileFactory::getModelById($id);
                    if(!FolderFactory::moveFileToFolder($file, $targetFolder)){
                        $allFilesMoved = false;
                    }
                }
                if($allFilesMoved){
                    $moveResult = true;
                }
            }
        } else if ($type === 'folder') {
            if ($perm) {
                $allFoldersMoved = true;
                foreach($ids as $id){
                    $folder = FolderFactory::getModelById($id);
                    if(!FolderFactory::moveFolderToFolder($folder, $targetFolder)){
                        $allFoldersMoved = false;
                    }
                }
                if($allFoldersMoved){
                    $moveResult = true;
                }
            }
        }
        if ($moveResult) {
            return array('content' => 1);
        } else {
            return array('content' => 0);
        }
    }

    //AJAX
    public function actionGetFolderContentsHTML($attributes) {
        if ((!isset($attributes['ajax']) || $attributes['ajax'] != 1) || !isset($attributes['targetController']) || !isset($attributes['folderId'])) {
            return array('content' => 0);
        }
        $folderId = $attributes['folderId'];
        $folder = FolderFactory::getModelById($folderId);
        $targetController = $attributes['targetController'];
        $itemViews = $folder->getFolderContentsItemViews($targetController);
        $htmlItemViews = '';
        foreach ($itemViews as $itemView) {
            $htmlItemViews .= $itemView->getHTMLView();
        }
        return array('content' => $htmlItemViews);
    }

    //AJAX
    public function actionDelete($attributes, $deleteProperties = array()) {
        if ((!isset($attributes['ajax']) || $attributes['ajax'] != 1) || !isset($attributes['type']) || !isset($attributes['id'])) {
            return array('content' => 0);
        } else if (!Permission::verifyByRef('delete_files')) {
            return array('content' => 0);
        }
        $type = $attributes['type'];
        $id = $attributes['id'];
        if ($type === 'file') {
            if ($this->deleteFile($id)) {
                return array('content' => 1);
            } else {
                return array('content' => 0);
            }
        } else if ($type === 'folder') {
          //  if ($this->deleteTree($id)) {
             if (FolderFactory::deleteTree($id)) {
                return array('content' => 1);
            } else {
                return array('content' => 0);
            }
        }
    }

    protected function deleteFile($fileId) {
        $fileArray = FileFactory::search()
                ->filter('uid', Login::getUserId())
                ->filter('id', $fileId)
                ->select();
        if (!empty($fileArray)) {
            $file = $fileArray[0];
            $s3Bucket = $file->getProperty('aws_s3_bucket');
            $s3Key = $file->getProperty('aws_s3_key');
            $result = File::removeFileFromS3($s3Bucket, $s3Key);

            if ($result) {
                $fileId = $file->getProperty('id');
                $file->setProperty('status', 0);
                //check if the link to the folder needs to be removed
                $folders = FolderFactory::getFoldersByFile($file);
                if (!empty($folders)) {
                    foreach ($folders as $folder) {
                        $unlinkResult = FolderFactory::unlinkFileFromFolder($file, $folder);
                        if (!$unlinkResult) {
                            return false;
                        }
                    }
                }
                if ($file->save()) {
                    //remove thumbnails for image files
                    $fileExtension = File::getFileExtensionFromAWSS3Key($s3Key);
                    $validImageExtensions = SystemConfig::getValidImageExtensions();
                    if (isset($validImageExtensions[$fileExtension]) && $validImageExtensions[$fileExtension] == true) {
                        $thumbnailSizes = SystemConfig::getThumbnailSizes();
                        foreach ($thumbnailSizes as $thumbnailSize) {
                            $width = $thumbnailSize['w'];
                            $height = $thumbnailSize['h'];
                            $thumbS3Key = File::generateS3KeyForThumbnail($width, $height, $s3Key);
                            File::removeFileFromS3($s3Bucket, $thumbS3Key);
                        }
                    }
                    $documentsArray = DocumentFactory::search()
                            ->filter('document.file_id', $fileId)
                            ->select();
                    if (!empty($documentsArray)) {
                        foreach ($documentsArray as $document) {
                            $document->setProperty('document.file_id', 'NULL');
                            if (!$document->save()) {
                                return false;
                            }
                        }
                    }
                    return true;
                }
            }
        }
        return false;
    }
    
    //@todo move some of this to a factory
    public function actionDownloadZip($attributes){
        if (!isset($attributes['folderId']) || empty($attributes['folderId'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $folderId = $attributes['folderId'];
        $folder = FolderFactory::getModelById($folderId);
        if(!$folder){
            GI_URLUtils::redirectToError(4001);
        }
        
        $zip = new ZipArchive();
        $zipPath = 'tempData/user/' . Login::getUserId() . '/zips/';
        $zipName = File::getNewFileName($zipPath, $folder->getProperty('title') . '.zip');
        File::createTempDataFolders($zipPath);
        $zipFilePath = $zipPath . $zipName;
        if($zip->open($zipFilePath, ZipArchive::CREATE) !== true){
            GI_URLUtils::redirectToError(1000);
        }
        
        $includeFolders = true;
        if (isset($attributes['includeFolders']) && $attributes['includeFolders'] == 0){
            $includeFolders = false;
        }
        
        $this->addFolderContentsToZip($zip, $folder, $includeFolders);
        $zip->close();
        if(file_exists($zipFilePath)){
            File::deleteTempDir();
            Header('Location: ' . $zipFilePath);
            die();
        } else {
            GI_URLUtils::redirectToError(1010);
        }
    }
    
    //@todo move this to a factory
    protected function addFolderContentsToZip(ZipArchive $zip, Folder $folder, $includeFolders = true, $curPath = ''){
        if($includeFolders){
            $subFolders = $folder->getSubFolders();

            if($subFolders){
                foreach($subFolders as $subFolder){
                    $addPath = GI_Sanitize::filename($subFolder->getProperty('title')) . '/';
                    $this->addFolderContentsToZip($zip, $subFolder, $includeFolders, $curPath . $addPath);
                }
            }
        }
        
        $files = $folder->getFiles();
        if($files){
            foreach($files as $file){
                $localFile = $file->saveToTemp();
                $zip->addFile($localFile, $curPath . GI_Sanitize::filename($file->getProperty('display_name')));
            }
        }
        
        return true;
    }
    
    public function actionAddFolder($attributes){
        $folder = FolderFactory::buildNewModel();
        $parentFolder = NULL;
        if (isset($attributes['parentFolderId'])){
            $parentFolderId = $attributes['parentFolderId'];
            $parentFolder = FolderFactory::getModelById($parentFolderId);
            $folder->setParentFolder($parentFolder);
        }
        
        if (empty($folder) || !$folder->isAddable()) {
            GI_URLUtils::redirectToError(2000);
        }
        
        $form = new GI_Form('add_folder');

        $ajax = false;
        if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
            $ajax = true;
        }
        
        $view = $folder->getFormView($form);
        $view->buildForm();

        $success = 0;
        $newURL = NULL;
        $jqueryAction = NULL;
        if(!isset($attributes['noSave']) || empty($attributes['noSave'])){
            if ($folder->handleFormSubmission($form)) {
                if ($ajax) {
//                    $newURL = 'refresh';
                    $reloadFolderId = $folder->getId();
                    if(!empty($parentFolderId)){
                        $reloadFolderId = $parentFolderId;
                    }
                    $jqueryAction = 'giModalClose(); replaceDirByFolderId(' . $reloadFolderId . ');';
                    $success = 1;
                } else {
                    $attrs = $folder->getViewURLAttrs();
                    if(!empty($parentFolder)){
                        $attrs = $parentFolder->getViewURLAttrs();
                    }
                    GI_URLUtils::redirect($attrs);
                }
            }
        }
        $returnArray = static::getReturnArray($view);
        $returnArray['breadcrumbs'] = $folder->getBreadcrumbs();
        $returnArray['breadcrumbs'][] = array(
            'label' => 'Add',
            'link' => GI_URLUtils::buildURL($attributes)
        );
        $returnArray['success'] = $success;
        if (!empty($newURL)) {
            $returnArray['newUrl'] = $newURL;
        }
        if(!empty($jqueryAction)){
            $returnArray['jqueryAction'] = $jqueryAction;
        }
        return $returnArray;
    }
    
    public function actionEditFolder($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $id = $attributes['id'];
        $folder = FolderFactory::getModelById($id);
        if (empty($folder) || !$folder->isEditable()) {
            GI_URLUtils::redirectToError(2000);
        }
        
        $form = new GI_Form('edit_folder');
        
        $ajax = false;
        if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
            $ajax = true;
        }
        
        $view = $folder->getFormView($form);
        $view->buildForm();
        
        $success = 0;
        $newURL = NULL;
        $jqueryAction = NULL;
        if(!isset($attributes['noSave']) || empty($attributes['noSave'])){
            if ($folder->handleFormSubmission($form)) {
                if ($ajax) {
//                    $newURL = 'refresh';
                    $jqueryAction = 'giModalClose(); replaceDirByFolderId(' . $folder->getId() . ');';
                    $success = 1;
                } else {
                    //Forward to the detail page
                    $attrs = $folder->getViewURLAttrs();
                    GI_URLUtils::redirect($attrs);
                }
            }
        }
        $returnArray = static::getReturnArray($view);
        $returnArray['breadcrumbs'] = $folder->getBreadcrumbs();
        $returnArray['breadcrumbs'][] = array(
            'label' => 'Edit',
            'link' => GI_URLUtils::buildURL($attributes)
        );
        $returnArray['success'] = $success;
        if (!empty($newURL)) {
            $returnArray['newUrl'] = $newURL;
        }
        if(!empty($jqueryAction)){
            $returnArray['jqueryAction'] = $jqueryAction;
        }
        return $returnArray;
    }
    
    public function actionDeleteFolder($attributes){
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $id = $attributes['id'];
        $folder = FolderFactory::getModelById($id);
        if (empty($folder)) {
            GI_URLUtils::redirectToError(4001);
        }
        
        if(!$folder->isDeleteable()){
            GI_URLUtils::redirectToAccessDenied();
        }
        
        $redirectProps = array(
            'controller' => 'dashboard',
            'action' => 'index'
        );
        
        $deleteProperties = array(
            'factoryClassName' => 'FolderFactory',
            'redirectOnSuccess' => $redirectProps,
            'refresh' => 1
        );
        
        $parentFolder = $folder->getParentFolder();
        if($parentFolder){
            $deleteProperties['jqueryAction'] = 'giModalClose(); replaceDirByFolderId(' . $parentFolder->getId() . ');';
        }
        
        return parent::actionDelete($attributes, $deleteProperties);
    }
    
    public function actionGetFolderFilesArea($attributes){
        if (!isset($attributes['folderId']) || !isset($attributes['uploaderName'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $uploaderName = $attributes['uploaderName'];
        $folderId = $attributes['folderId'];
        $folder = FolderFactory::getModelById($folderId);
        if (empty($folder)) {
            GI_URLUtils::redirectToError(4001);
        }
        $mimeTypes = 'basic';
        if(isset($attributes['mimeTypes'])){
            $mimeTypes = $attributes['mimeTypes'];
        }
        $uploader = GI_UploaderFactory::buildUploader($uploaderName, $mimeTypes);
        $uploader->setFilesLabel(NULL);
        $uploader->setTargetFolder($folder);
        if(isset($attributes['containerId'])){
            $uploader->setContainerId($attributes['containerId']);
        }
        $returnArray = static::getReturnArray($uploader);
        $returnArray['jqueryAction'] = $uploader->getScript();
        return $returnArray;
    }
    
    public function actionGetDirectoryView($attributes){
        if (!isset($attributes['folderId'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $folderId = $attributes['folderId'];
        $folder = FolderFactory::getModelById($folderId);
        if (empty($folder)) {
            GI_URLUtils::redirectToError(4001);
        }
        $view = $folder->getDirectoryView();
        $view->setStartOpen(true);
        if(isset($attributes['uploaderName'])){
            $view->setUploaderName($attributes['uploaderName']);
        }
        if(isset($attributes['containerId'])){
            $view->setContainerId($attributes['containerId']);
        }
        if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
            $view->setAddWrap(false);
        }
        $returnArray = static::getReturnArray($view);
        return $returnArray;
    }
    
    public function actionViewFolder($attributes){
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $folderId = $attributes['id'];
        $folder = FolderFactory::getModelById($folderId);
        if (empty($folder)) {
            GI_URLUtils::redirectToError(4001);
        }
        $view = GI_UploaderFactory::buildFileBrowser('folder_' . $folderId);
        $view->setTargetFolder($folder);
        $returnArray = static::getReturnArray($view);
        return $returnArray;
    }
    
    public function actionGetFileThumbnail($attributes) {
        $missingFile = FileFactory::buildNewModel();
        $missingFileView = new FileThumbnailView($missingFile);
        if ((!isset($attributes['ajax']) || $attributes['ajax'] != 1) || !isset($attributes['fileId'])) {
            return array('content' => $missingFileView->getHTMLView());
        }
        
        $fileViewType = 'thumbnail';
        if(isset($attributes['fileViewType'])){
            $fileViewType = $attributes['fileViewType'];
        }
        
        $fileId = $attributes['fileId'];
        
        $file = FileFactory::getModelById($fileId);
        if(!$file){
            return array('content' => $missingFileView->getHTMLView());
        }
            
        $fileView = $file->getView($fileViewType);
        $fileView->setIsDeleteable(false);
        $fileView->setIsRenamable(false);
        return array(
            'content' => $fileView->getHTMLView()
        );
    }
    
    public function actionGetAvatarThumbnail($attributes){
        if(!isset($attributes['userId']) && !isset($attributes['socketUserId'])){
            GI_URLUtils::redirectToError(2000);
        }
        $user = NULL;
        if(isset($attributes['userId'])){
            $userId = $attributes['userId'];
            $user = UserFactory::getModelById($userId);
        } elseif(isset($attributes['socketUserId'])){
            $socketUserId = $attributes['socketUserId'];
            $user = UserFactory::getBySocketUserId($socketUserId);
        }
        
        $width = NULL;
        $height = NULL;
        if(isset($attributes['width'])){
            $width = $attributes['width'];
        }
        if(isset($attributes['height'])){
            $height = $attributes['height'];
        }
        
        if(empty($user)){
            $user = UserFactory::buildNewModel();
        }
        
        $avatarHTML = $user->getUserAvatarHTML($width, $height);
        
        return array(
            'content' => $avatarHTML
        );
    }
    
}
