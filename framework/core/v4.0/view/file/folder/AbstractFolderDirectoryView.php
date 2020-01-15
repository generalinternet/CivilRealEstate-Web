<?php
/**
 * Description of AbstractFolderDirectoryView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    2.0.0
 */
abstract class AbstractFolderDirectoryView extends GI_View {
    
    /** @var AbstractFolder */
    protected $folder = NULL;
    protected $addWrap = true;
    protected $startOpen = false;
    protected $recursive = false;
    protected $uploaderName = NULL;
    protected $containerId = NULL;
    protected $folderHeaderClass = '';
    protected $folderContentsOpen = false;
    
    public function __construct(AbstractFolder $folder) {
        parent::__construct();
        $this->folder = $folder;
    }
    
    public function setAddWrap($addWrap){
        $this->addWrap = $addWrap;
        return $this;
    }
    
    public function setStartOpen($startOpen){
        $this->startOpen = $startOpen;
        return $this;
    }
    
    public function setRecursive($recursive){
        $this->recursive = $recursive;
        return $this;
    }
    
    public function setUploaderName($uploaderName){
        $this->uploaderName = $uploaderName;
        return $this;
    }
    
    public function setContainerId($containerId){
        $this->containerId = $containerId;
        return $this;
    }
    
    public function setFolderHeaderClass($folderHeaderClass){
        $this->folderHeaderClass = $folderHeaderClass;
        return $this;
    }
    
    public function setFolderContentsOpen($folderContentsOpen){
        $this->folderContentsOpen = $folderContentsOpen;
        return $this;
    }
    
    protected function openViewWrap(){
        if($this->addWrap){
            $this->addHTML('<div class="content_padding">');
        }
        return $this;
    }
    
    protected function closeViewWrap(){
        if($this->addWrap){
            $this->addHTML('</div>');
        }
        return $this;
    }
    
    public function buildView() {
        $this->openViewWrap();
        $this->addDirectoryRow($this->folder, $this->startOpen, $this->recursive);
        $this->closeViewWrap();
    }
    
    protected function addDirectoryRow(AbstractFolder $folder, $open = true, $recursive = false){
        $this->addHTML('<div class="folder_row" data-folder-id="' . $folder->getId() . '"');
        $uploader = false;
        if($this->uploaderName){
            $uploader = true;
            $this->addHTML('data-uploader-name="' . $this->uploaderName. '" ');
        }
        if($this->containerId){
            $this->addHTML('data-container-id="' . $this->containerId . '" ');
        }
        $this->addHTML('>');
        
        $openDirClass = 'hide_on_load';
        $iconClass = 'arrow_right';
        if(FolderFactory::getSubFolderCount($folder)){
            $openDirClass = '';
        }
        if($open){
            $iconClass = 'arrow_down';
        }
        $folderHeaderClass = $this->folderHeaderClass;
        if($uploader){
            $folderHeaderClass .= ' open_folder';
        }
        if($this->folderContentsOpen && $folder->getId() == $this->folder->getId()){
            $folderHeaderClass .= ' open';
        }
        $iconView = new IconView($iconClass);
        $iconView->setIconWrapClass('open_directory '.$openDirClass);
        $iconView->setIconClass('border primary');
        $this->addHTML($iconView->getHTMLView());
        $this->addHTML('<span class="folder_header ' . $folderHeaderClass . '">');
            $this->addHTML('<span class="folder_icon"></span>')
                    ->addHTML('<span class="folder_title">' . $folder->getTitle(). '</span>');
            $this->addDirectoryBtns($folder);
        $this->addHTML('</span>');
        $this->addSubDirectories($open, $recursive);
        
        $this->addHTML('</div>');
    }
    
    protected function addSubDirectories($open = true, $recursive = false){
        $class = '';
        if($open){
            $class = 'open';
        }
        $this->addHTML('<div class="sub_directory_wrap ' . $class . '">');
        if($open){
            $openSubDir = false;
            if($recursive){
                $openSubDir = true;
            }
            $subFolders = $this->folder->getSubFolders();
            foreach($subFolders as $subFolder){
                $this->addDirectoryRow($subFolder, $openSubDir, $recursive);
            }
        }
        $this->addHTML('</div>');
    }
    
    protected function addDirectoryBtns(AbstractFolder $folder){
        $showBtns = false;
        $deleteBtn = '';
        $editBtn = '';
        $addBtn = '';
        if($folder->isDeleteable()){
            $showBtns = true;
            $deleteURL = $folder->getDeleteURL();
            $iconView = new IconView('trash');
            $deleteBtn = '<a href="' . $deleteURL . '" class="custom_btn open_modal_form" title="Delete Folder">'.$iconView->getHTMLView().'</a>';
        }
        if($folder->isEditable()){
            $showBtns = true;
            $editURL = $folder->getEditURL();
            $iconView = new IconView('pencil');
            $editBtn = '<a href="' . $editURL . '" class="custom_btn open_modal_form" title="Edit Folder">'.$iconView->getHTMLView().'</a>';
        }
        $subfolder = FolderFactory::buildNewModel();
        $subfolder->setParentFolder($folder);
        if($subfolder->isAddable()){
            $showBtns = true;
            $addURL = $subfolder->getAddURL();
            $iconView = new IconView('add');
            $addBtn = '<a href="' . $addURL . '" class="custom_btn open_modal_form" title="Add Subfolder">'.$iconView->getHTMLView().'</a>';
        }
        if($showBtns){
            $this->addHTML('<span class="folder_actions">');
                $this->addHTML($deleteBtn);
                $this->addHTML($editBtn);
                $this->addHTML($addBtn);
            $this->addHTML('</span>');
        }
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}
