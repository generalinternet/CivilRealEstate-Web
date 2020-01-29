<?php
/**
 * Description of AbstractFolder
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    4.0.1
 */
abstract class AbstractFolder extends GI_Model {

    /** @var AbstractFolder */
    protected $parentFolder = NULL;
    
    public function getTitle(){
        return $this->getProperty('title');
    }
    
    /**
     * @param AbstractFolder $folder
     * @return $this
     */
    public function setParentFolder(AbstractFolder $folder){
        $this->parentFolder = $folder;
        return $this;
    }

    /** @return AbstractFolder[] */
    public function getParentFolder(){
        if(is_null($this->parentFolder)){
            $this->parentFolder = FolderFactory::getParentFolder($this);
        }
        return $this->parentFolder;
    }
    
    public function addSubfolder(AbstractFolder $folder) {
        return FolderFactory::linkFolderToFolder($this, $folder);
    }
    
    /**
     * @param GI_Form $form
     * @return AbstractFolderFormView
     */
    public function getFormView(GI_Form $form) {
        return new FolderFormView($form, $this);
    }
    
    /**
     * @param GI_Form $form
     * @return boolean
     */
    public function setPropertiesFromForm(GI_Form $form){
        $title = filter_input(INPUT_POST, 'title');
        $this->setProperty('title', $title);
        return true;
    }
    
    /**
     * @param GI_Form $form
     * @return boolean
     */
    public function handleFormSubmission(GI_Form $form) {
        if ($form->wasSubmitted() && $this->validateForm($form)) {
            $this->setPropertiesFromForm($form);
            if (!$this->save()) {
                return false;
            }
            
            return true;
        }
        return false;
    }
    
    /**
     * Logical delete: sets status 0
     * 
     * @return boolean
     */
    public function softDelete() {
          $subFolders = FolderFactory::getSubFolders($this);
          if (!empty($subFolders)) {
              foreach ($subFolders as $subFolder) {
                  $unlinkResult = FolderFactory::unlinkFolderFromFolder($this, $subFolder);
                  if (!$unlinkResult) {
                      return false;
                  }
                //TODO - if this is the only link the child folder has to any
                //other folders, delete it as well, and all of its files.
                //Do this recursively for entire file system "below" this folder
              }
          }
          $parentFolders = FolderFactory::getParentFolders($this);
          if (!empty($parentFolders)) {
              foreach ($parentFolders as $parentFolder) {
                  $unlinkResult = FolderFactory::unlinkFolderFromFolder($parentFolder, $this);
                  if (!$unlinkResult) {
                      return false;
                  }
              }
          }
          
          $files = FolderFactory::getFiles($this);
          if (!empty($files)) {
              foreach ($files as $file) {
                  $unlinkResult = FolderFactory::unlinkFolderAndFile($this, $file);
                  if (!$unlinkResult) {
                      return false;
                  }
            //TODO if this is the file's only link to a folder, then
            //soft delete the file as well, and remove it from S3
            //(override File->softDelete())
              }
          }

        return parent::softDelete();
    }

    /**
     * Gets an array of file item views
     * 
     * @return GI_View[] an array of $targetController's file item views
     */
    public function getFolderContentsItemViews($targetController) {
        $files = FolderFactory::getFiles($this);
        $itemViews = array();
        if (!empty($files)) {
            foreach ($files as $file) {
                $itemView = $file->getView();
                $itemViews[] = $itemView;
            }
        }
        return $itemViews;
    }
    
    /**
     * @return AbstractFile[]
     */
    public function getFiles($idsAsKey = false) {
        return FolderFactory::getFiles($this, $idsAsKey);
    }
    
    /**
     * @return Folder[]
     */
    public function getSubFolders(){
        return FolderFactory::getSubFolders($this);
    }
    
    public function getSubFolderCount(){
        return FolderFactory::getSubFolderCount($this);
    }


    public function save() {
        if($this->getProperty('system') && empty($this->getProperty('ref'))){
            $ref = GI_Sanitize::ref($this->getProperty('title'));
            if($ref){
                $this->setProperty('ref', $ref);
            }
        }
        if(empty($this->getProperty('user_id'))){
            $userId = Login::getUserId();
            $this->setProperty('user_id', $userId);
        }
        if(parent::save()){
            if(!empty($this->parentFolder) && !empty($this->parentFolder->getId())){
                return FolderFactory::linkFolderToFolder($this->parentFolder, $this);
            }
            return true;
        }
        return false;
    }
    
    public function getProfilePictureFolder(){
        if($this->getProperty('is_root')){
            $ppFolder = FolderFactory::getSubFolderByRef($this, 'profile_pictures');
            if(!$ppFolder){
                $ppFolder = FolderFactory::buildNewModel();
                $ppFolder->setProperty('is_root', 0);
                $ppFolder->setProperty('user_root', 0);
                $ppFolder->setProperty('system', 1);
                $ppFolder->setProperty('title', 'Profile Pictures');
                $ppFolder->setProperty('ref', 'profile_pictures');
                $ppFolder->setProperty('user_id', $this->getProperty('user_id'));
                if ($ppFolder->save()) {
                    $linkResult = FolderFactory::linkFolderToFolder($this, $ppFolder);
                    if (!$linkResult) {
                        return false;
                    }
                }
            }
            return $ppFolder;
        }
        return NULL;
    }
    
    public function getIsAddable() {
        if(Permission::verifyByRef('add_folders')){
            return true;
        }
        return false;
    }
    
    public function getIsViewable() {
        if($this->getProperty('uid') == Login::getUserId() || Permission::verifyByRef('view_folders')){
            return true;
        }
        return false;
    }
    
    public function getIsEditable() {
        if($this->getProperty('uid') == Login::getUserId() || Permission::verifyByRef('edit_folders')){
            return true;
        }
        return false;
    }
    
    public function isSystemFolder(){
        if($this->getProperty('system')){
            return true;
        }
        return false;
    }
    
    public function getIsDeleteable(){
        if(!$this->isSystemFolder() && ($this->getProperty('uid') == Login::getUserId() || Permission::verifyByRef('delete_folders'))){
            return true;
        }
        return false;
    }
    
    public function getDeleteURL(){
        $attrs = array(
            'controller' => 'file',
            'action' => 'deleteFolder',
            'id' => $this->getId()
        );
        return GI_URLUtils::buildURL($attrs);
    }
    
    public function getEditURL(){
        $attrs = array(
            'controller' => 'file',
            'action' => 'editFolder',
            'id' => $this->getId()
        );
        return GI_URLUtils::buildURL($attrs);
    }
    
    public function getAddURL(){
        $attrs = array(
            'controller' => 'file',
            'action' => 'addFolder'
        );
        if($this->parentFolder){
            $attrs['parentFolderId'] = $this->parentFolder->getId();
        }
        return GI_URLUtils::buildURL($attrs);
    }
    
    public function getViewURLAttrs() {
        $attrs = array(
            'controller' => 'file',
            'action' => 'viewFolder',
            'id' => $this->getId()
        );
        return $attrs;
    }

    public function getSpecificTitle() {
        return $this->getTitle();
    }
    
    /**
     * @return \AbstractFolderDirectoryView
     */
    public function getDirectoryView(){
        $directoryView = new FolderDirectoryView($this);
        return $directoryView;
    }
    
    public function getFile($filename) {
        return FileFactory::getFileByFolderAndFilename($this, $filename);
    }
    
}
