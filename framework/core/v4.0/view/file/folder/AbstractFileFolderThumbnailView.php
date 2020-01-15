<?php

abstract class AbstractFileFolderThumbnailView extends GI_View {

    protected $folderModel;
    protected $rootDirectory;
    protected $otherData;

    public function __construct($folderModel, $rootDirectory = false, $otherData = array()) {
        parent::__construct();
        $this->folderModel = $folderModel;
        $this->rootDirectory = $rootDirectory;
        $this->otherData = $otherData;
        $this->buildView();
    }

    protected function buildView() {
        if (isset($this->otherData['subFolderCount'])) {
            $subFolderCount = $this->otherData['subFolderCount'];
        } else {
            $subFolderCount = 0;
        }
        $folderId = $this->folderModel->getProperty('id');
        $folderTitle = $this->otherData['folderTitle'];
        if ($this->rootDirectory) {
            $folderIconClass = 'folder_big';
            $folderClass = 'root';
        } else {
            $folderIconClass = 'folder_sml';
            $folderClass = 'sub';
        }
        $this->addContent('<div class="folder ' . $folderClass . '" id="folder_' . $folderId . '" data-id="' . $folderId . '">');
        if ($subFolderCount > 0 && !$this->rootDirectory) {
            $this->addContent('<span class="expand_folder"><span class="icon arrow_red_right"></span></span>');
        }
        $this->addContent('<span class="open_folder"><span class="icon ' . $folderIconClass . '"></span><span class="folder_title">' . $folderTitle . '</span></span>');
        if (!$this->rootDirectory) {
            $this->addContent('<div class="sub_directory"></div>');
        }
        $this->addContent('</div>');
    }

}
