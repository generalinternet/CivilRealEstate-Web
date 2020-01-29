<?php
/**
 * Description of AbstractGI_Uploader
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.4
 */
abstract class AbstractGI_Uploader extends GI_View{
    
    protected $uploaderName = NULL;
    protected $mimeTypes = 'all';
    /** @var AbstractFolder */
    protected $targetFolder = NULL;
    /** @var AbstractDocument */
    protected $targetDocument = NULL;
    protected $containerId = 'uploader';
    protected $scriptString = '';
    protected $uploadType = 'basic';
    protected $filesLabel = 'Files';
    protected $addBrowseButton = true;
    protected $browseLabel = 'Upload File';
    protected $browseIconClass = 'icon upload';
    protected $browseClass = '';
    protected $browseFolderLabel = 'Link File';
    protected $saveOrderLabel = 'Save Order';
    protected $required = false;
    protected $enabled = true;
    protected $fileView = 'thumbnail';
    protected $fileLimit = 0;
    protected $dropzone = true;
    protected $downloadZip = false;
    protected $downloadZipIncludeFolders = true;
    protected $areFilesDeleteable = true;
    protected $areFilesRenamable = true;
    /** @var GI_Form */
    protected $form = NULL;
    protected $scriptBuilt = false;
    protected $showBtns = true;
    protected $disabledMsg = NULL;
    
    public function __construct($uploaderName) {
        $this->setUploaderName($uploaderName);
        $this->saveUploader($this);
        $this->setContainerId($this->getUploaderName() . '_container');
        parent::__construct();
        if(!Login::isLoggedIn()){
            $this->setEnabled(false);
            $loginLink = GI_URLUtils::buildURL(array(
                'controller' => 'login',
                'action' => 'index',
            ));
            $this->setDisabledMsg('Please log in to upload files. <a href="' . $loginLink . '" title="Log In">Log In</a>');
        }
    }
    
    public function setUploaderName($uploaderName){
        $cleanUploaderName = GI_Sanitize::ref($uploaderName);
        $this->uploaderName = $cleanUploaderName;
        return $this;
    }
    
    public function getUploaderName(){
        return $this->uploaderName;
    }
    
    public function setMimeTypes($mimeTypes){
        $this->mimeTypes = $mimeTypes;
        return $this;
    }
    
    public function getMimeTypes(){
        return $this->mimeTypes;
    }
    
    public function setTargetFolder(AbstractFolder $targetFolder = NULL){
        $this->targetFolder = $targetFolder;
        return $this;
    }
    
    public function getTargetFolder(){
        return $this->targetFolder;
    }
    
    public function getTargetFolderId(){
        $folder = $this->targetFolder;
        if($folder){
            return $folder->getProperty('id');
        }
        return NULL;
    }
    
    public function setTargetDocument(AbstractDocument $targetDocument = NULL){
        $this->targetDocument = $targetDocument;
        return $this;
    }
    
    public function getTargetDocumentId(){
        $document = $this->targetDocument;
        if($document){
            return $document->getProperty('id');
        }
        return NULL;
    }
    
    public function setContainerId($containerId){
        $this->containerId = $containerId;
        return $this;
    }
    
    public function getContainerId(){
        return $this->containerId;
    }
    
    public function getUploadURL(){
        $urlProps = array(
            'uploaderName' => $this->getUploaderName(),
            'targetFolderId' => $this->getTargetFolderId(),
            'targetDocId' => $this->getTargetDocumentId(),
            'fileView' => $this->getFileView(),
            'attach' => 1
        );
        
        if($this->form){
            $urlProps['attach'] = 0;
        }
        
        $uploadURL = '';
        foreach($urlProps as $prop => $val){
            $uploadURL .= '&' . $prop . '=' . $val;
        }
        return $uploadURL;
    }
    
    public function putUploadedFilesInTargetFolder() {
        return FolderFactory::putUploadedFilesInTargetFolder($this);
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
    protected function addScript($script){
        $this->scriptString .= $script;
        return $this;
    }
    
    public function getScript(){
        if(!Login::isLoggedIn()){
            return NULL;
        }
        $this->buildV4Script();
        return $this->scriptString;
    }
    
    public function setUploadType($uploadType){
        $this->uploadType = $uploadType;
        return $this;
    }
    
    public function getUploadType(){
        return $this->uploadType;
    }
    
    public function setFilesLabel($filesLabel) {
        $this->filesLabel = $filesLabel;
        return $this;
    }

    public function setRequired($required) {
        $this->required = $required;
        return $this;
    }

    public function setBrowseLabel($browseLabel) {
        $this->browseLabel = $browseLabel;
        return $this;
    }
    
    public function setBrowseFolderLabel($browseFolderLabel) {
        $this->browseFolderLabel = $browseFolderLabel;
        return $this;
    }
    
    public function setSaveOrderLabel($saveOrderLabel) {
        $this->saveOrderLabel = $saveOrderLabel;
        return $this;
    }

    public function setEnabled($enabled) {
        $this->enabled = $enabled;
        return $this;
    }
    
    public function setDisabledMsg($disabledMsg){
        $this->disabledMsg = $disabledMsg;
        return $this;
    }
    
    public function setFileView($fileView){
        $this->fileView = $fileView;
        return $this;
    }
    
    public function getFileView(){
        return $this->fileView;
    }
    
    public function setFileLimit($fileLimit){
        $this->fileLimit = $fileLimit;
        return $this;
    }
    
    public function setDropzone($dropzone){
        $this->dropzone = $dropzone;
        return $this;
    }
    
    public function setShowBtns($showBtns){
        $this->showBtns = $showBtns;
        return $this;
    }
    
    public function setDownloadZip($downloadZip, $includeFolders = true){
        $this->downloadZip = $downloadZip;
        $this->downloadZipIncludeFolders = $includeFolders;
        return $this;
    }
    
    public function setForm(GI_Form $form){
        $this->form = $form;
        return $this;
    }
    
    /**
     * @deprecated since version 3.0.2
     */
    protected function buildScript(){
        $awsBucket = ProjectConfig::getAWSBucket();
        $policy = base64_encode(json_encode(array(
            // ISO 8601 - date('c'); generates uncompatible date, so better do it manually
            'expiration' => date('Y-m-d\TH:i:s.000\Z', strtotime('+1 day')),
            'conditions' => array(
                array('bucket' => $awsBucket),
                array('acl' => 'authenticated-read'),
                array('starts-with', '$key', ''),
                array('starts-with', '$Content-Type', ''),
                array('starts-with', '$Content-Disposition', ''),
                array('starts-with', '$name', ''),
                array('starts-with', '$Filename', ''),
            )
        )));
        $signature = base64_encode(hash_hmac('sha1', $policy, ProjectConfig::getAWSSecret(), true));
        $keyBase = File::generateAWSKeyBase();
        
        $uploaderName = $this->getUploaderName();
        $containerId = $this->getContainerId();
        $this->addScript('createGIUploader("' . $uploaderName . '", "' . $containerId . '", "' . ProjectConfig::getAWSURL() . '", "' . ProjectConfig::getAWSKey() . '", "' . $policy . '", "' . $signature . '", "' . $keyBase . '", "' . $awsBucket . '");');
    }
    
    protected function buildV4Script(){
        if($this->scriptBuilt){
            return true;
        }
        $dateObj = new DateTime();
        //not sure if it needs to be in GMT
        GI_Time::getGMTTimezone($dateObj);
        $shortDate = $dateObj->format('Ymd');
        $isoDate = $dateObj->format('Ymd\THis\Z');
        $dateObj->add(new DateInterval('P1D')); //1 day expiration
        $expDate = $dateObj->format('Y-m-d\TG:i:s\Z');
        
        $awsBucket = ProjectConfig::getAWSBucket();
        $awsKey = ProjectConfig::getAWSKey();
        $awsSecret = ProjectConfig::getAWSSecret();
        $awsRegion = ProjectConfig::getAWSRegion();
        
        $xAmzCred = $awsKey . '/' . $shortDate . '/' . $awsRegion . '/s3/aws4_request';
        $xAmzAlgo = 'AWS4-HMAC-SHA256';
        
        $policy = utf8_encode(json_encode(array(
            'expiration' => $expDate,
            'conditions' => array(
                array('acl' => 'authenticated-read'),  
                array('bucket' => $awsBucket), 
                array('starts-with', '$key', ''),
                array('starts-with', '$name', ''), 
                array('starts-with', '$Filename', ''),
                array('starts-with', '$Content-Type', ''),
                array('starts-with', '$Content-Disposition', ''),
                array('x-amz-credential' => $xAmzCred),
                array('x-amz-algorithm' => $xAmzAlgo),
                array('X-amz-date' => $isoDate)
        )))); 
        
        //Signature calculation (AWS Signature Version 4)   
        //For more info http://docs.aws.amazon.com/AmazonS3/latest/API/sig-v4-authenticating-requests.html  
        $kDate = hash_hmac('sha256', $shortDate, 'AWS4' . $awsSecret, true);
        $kRegion = hash_hmac('sha256', $awsRegion, $kDate, true);
        $kService = hash_hmac('sha256', 's3', $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);
        $signature = hash_hmac('sha256', base64_encode($policy), $kSigning);
        $base64Policy = base64_encode($policy);
        
        $keyBase = File::generateAWSKeyBase();
        
        $uploaderName = $this->getUploaderName();
        $containerId = $this->getContainerId();
        $multiPartParams = array(
            'acl' => 'authenticated-read',
            'X-Amz-Credential' => $xAmzCred,
            'X-Amz-Algorithm' => $xAmzAlgo,
            'X-Amz-Date' => $isoDate,
            'policy' => $base64Policy,
            'X-Amz-Signature' => $signature
        );
        $this->addScript('createGIUploader("' . $uploaderName . '", "' . $containerId . '", "' . ProjectConfig::getAWSURL() . '", "' . ProjectConfig::getAWSKey() . '", "' . $base64Policy . '", "' . $signature . '", "' . $keyBase . '", ' . json_encode($multiPartParams) . ');');
        $this->scriptBuilt = true;
    }
    
    protected function buildView(){
        $formElementClass = 'form_element';

        $this->addHTML('<div id="' . $this->getContainerId() . '" class="' . $formElementClass . ' uploader_container" data-upload-url="' . $this->getUploadURL() . '" data-upload-type="' . $this->getUploadType() . '" data-target-folder-id="' . $this->getTargetFolderId() . '" data-mime-types="' . $this->getMimeTypes() . '" data-uploader-name="' . $this->getUploaderName() . '" data-file-limit="' . $this->fileLimit . '">');
        
        $this->addLabel();
        
        $this->addBtns();

        $this->addFilesArea();

        $this->addVerifyMessage();

        $this->addHTML('</div>');
    }
    
    protected function addLabel(){
        if(empty($this->filesLabel)){
            return NULL;
        }
        $labelClass = 'main';
        if ($this->required) {
            $labelClass .= ' required';
        }
        $this->addHTML('<label class="' . $labelClass . '">' . $this->filesLabel . '</label>');
    }
    
    protected function addBtns(){
        if(!$this->showBtns){
            return;
        }
        $this->addHTML('<div class="wrap_btns">');
            $this->addBrowseBtn();
            //$this->addLinkBtn();
        $this->addSortBtn();
        if ($this->downloadZip) {
            $this->addDownloadZipBtn();
        }
        $this->addHTML('</div>');
    }

    protected function addBrowseBtn() {
        if ($this->addBrowseButton) {
            $browseClass = 'browse_computer';
            if (!$this->enabled) {
                $browseClass .= ' disabled';
            }
            if(!empty($this->browseClass)){
                $browseClass .= ' ' . $this->browseClass;
            }
            $string = '<span class="' . $browseClass . '"><span class="icon_wrap"><span class="' . $this->browseIconClass . '"></span></span>';
            if (!empty($this->browseLabel)) {
                $string .= '<span class="btn_text">' . $this->browseLabel . '</span>';
            }
            $string .= '</span>';
            $this->addHTML($string);
        }
    }

    protected function addLinkBtn(){
        $browseClass = 'browse_folder';
        if (!$this->enabled) {
            $browseClass .= ' disabled';
        }
        $this->addHTML('<span class="' . $browseClass . ' other_btn icon_btn"><span class="icon link"></span><span class="btn_text">' . $this->browseFolderLabel . '</span></span>');
    }
    
    protected function addSortBtn(){
        $saveOrderClass = 'save_order';
        if (!$this->enabled) {
            $saveOrderClass .= ' disabled';
        }
        $this->addHTML('<span class="' . $saveOrderClass . ' other_btn">' . $this->saveOrderLabel . '</span>');
    }
    
    protected function addDownloadZipBtn(){
        if($this->targetFolder && $this->downloadZip){
            $folderId = $this->targetFolder->getProperty('id');
            $downloadZipProps = array(
                'controller' => 'file',
                'action' => 'downloadZip',
                'folderId' => $folderId
            );
            if(!$this->downloadZipIncludeFolders){
                $downloadZipProps['includeFolders'] = 0;
            }
            $downloadZipURL = GI_URLUtils::buildURL($downloadZipProps);
            $this->addHTML('<a href="' . $downloadZipURL . '" title="Download Zip" class="custom_btn download_zip" target="_blank"><span class="icon_wrap"><span class="icon primary download"></span></span></a>');
        }
    }
    
    protected function addDropZone(){
        $this->addHTML($this->getDropZone());
    }
    
    protected function getDropZone(){
        $html = '';
        if($this->dropzone){
            $dropzoneClass = 'dropzone';
            if (!$this->enabled) {
                $dropzoneClass .= ' disabled';
            }
            $html = '<div class="' . $dropzoneClass . '"><p>Files Here</p></div>';
        }
        return $html;
    }
    
    /**
     * @return File[]
     */
    public function getFiles($idsAsKey = false, $getExisting = false){
        $fileIdsFromForm = $this->getFileIdsFromForm();
        if(!$getExisting && !empty($fileIdsFromForm)){
            $uploaderFiles = FileFactory::getFilesByIdsFromForm($fileIdsFromForm, $idsAsKey);
        } else {
            if(!empty($this->form)){
                $tempFiles = FileFactory::getTempFilesByUploaderName($this->getUploaderName(), $idsAsKey);
            }

            if(!isset($tempFiles) || empty($tempFiles)){
                $tempFiles = array();
            }

            if(!empty($this->targetFolder)){
                $files = $this->targetFolder->getFiles($idsAsKey);
            }

            if(!isset($files) || empty($files)){
                $files = array();
            }

            $uploaderFiles = $tempFiles + $files;
        }
        return $uploaderFiles;
    }
    
    public function getFileIdsFromForm(){
        if($this->form && $this->form->wasSubmitted()){
            $fileIds = filter_input(INPUT_POST, $this->getUploaderName() . '_files', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            return $fileIds;
        }
        return NULL;
    }
    
    protected function addFilesArea(){
        $this->addHTML($this->getFilesArea());
    }
    
    public function getFilesArea(){
        $filesAreaClass = 'files_area';
        if (!empty($this->fileView)) {
            $filesAreaClass .= ' ' . $this->fileView . '_area';
        }
        
        if (!$this->enabled) {
            $filesAreaClass .= ' disabled';
        }
        
        if(!$this->areFilesDeleteable){
            $filesAreaClass .= ' delete_disabled';
        }
        
        if(!$this->areFilesRenamable){
            $filesAreaClass .= ' edit_disabled';
        }
        
       $html = '<div class="' . $filesAreaClass . '">';
        $idsAsKey = false;
        if(!empty($this->form)){
            $idsAsKey = true;
        }
        $files = $this->getFiles($idsAsKey);
        if(!empty($files)){
            foreach($files as $file){
                if(!empty($this->form)){
                    $fileView = $file->getView($this->getFileView(), $this);
                } else {
                    $fileView = $file->getView($this->getFileView());
                }
                if($fileView){
                    $html .= $fileView->getHTMLView();
                }
            }
        }
        
        $this->addDropZone();

        $html .= '</div>';
        return $html;
    }
    
    protected function addVerifyMessage(){
        if ($this->enabled) {
            $this->addHTML('<div class="uploading_files">');
            $this->addHTML('<p>Your browser doesn\'t have HTML5, Silverlight or Flash support.</p>');
            $this->addHTML('</div>');
        } elseif(!empty($this->disabledMsg)){
            $this->addHTML('<p>' . $this->disabledMsg . '</p>');
        }
    }

    public function setAreFilesDeleteable($areFilesDeleteable) {
        $this->areFilesDeleteable = $areFilesDeleteable;
        return $this;
    }
    
    public function setAreFilesRenamable($areFilesRenamable) {
        $this->areFilesRenamable = $areFilesRenamable;
        return $this;
    }
    
    public function setBrowseIconClass($browseIconClass) {
        $this->browseIconClass = $browseIconClass;
    }
    
    public function setBrowseClass($browseClass) {
        $this->browseClass = $browseClass;
    }
    
    public function setAddBrowseButton($addBrowseButton = true) {
        $this->addBrowseButton = $addBrowseButton;
    }
    
}
