<?php
/**
 * Description of AbstractGI_UploaderBrowser
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.1
 */
abstract class AbstractGI_UploaderBrowser extends AbstractGI_Uploader {
    
    public function buildView() {
        $this->setFilesLabel(NULL);
        $this->addHTML('<div class="folder_directory">')
                ->addHTML('<div class="folders">');
        $this->addHTML('<h3>Folders</h3>');
            $this->addHTML('<div class="directory_wrap">');
                $directoryView = $this->targetFolder->getDirectoryView();
                $directoryView->setAddWrap(false);
                $directoryView->setFolderContentsOpen(true);
                $directoryView->setStartOpen(true);
                $directoryView->setUploaderName($this->getUploaderName());
                $directoryView->setContainerId($this->getContainerId());
                $this->addHTML($directoryView->getHTMLView());
            $this->addHTML('</div>');
        $this->addHTML('</div>')
                ->addHTML('<div class="files">');
        $this->addHTML('<h3>Files</h3>');
        parent::buildView();
        $this->addHTML('</div>')
                ->addHTML('</div>');
    }
    
}
