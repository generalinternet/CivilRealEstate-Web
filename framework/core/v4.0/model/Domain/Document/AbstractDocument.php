<?php

/**
 * Description of AbstractDocument
 * 
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    2.0
 */
abstract class AbstractDocument extends GI_Model {

    protected $folder = NULL;
    protected $file = NULL;

    public function __construct(GI_DataMap $map) {
        parent::__construct($map);
    }

    /**
     * 
     * @return File
     */
    public function getFile() {
        if (is_null($this->file)) {
            //$this->file = File::getById($this->getProperty('file_id'));
            $this->file = FileFactory::getModelById($this->getProperty('file_id'));
        }

        return $this->file;
    }

    public function getFolderProperties() {
        $folderProperties = parent::getFolderProperties();
        $folderProperties['title'] = $this->getViewTitle(false);
        return $folderProperties;
    }

    public function getViewTitle($plural = true) {
        $title = 'Document';
        if ($plural) {
            $title .= 's';
        }
        return $title;
    }

}
