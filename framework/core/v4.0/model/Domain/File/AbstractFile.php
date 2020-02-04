<?php
/**
 * Description of AbstractFile
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.3
 */
abstract class AbstractFile extends GI_Model {
    
    public static $validImageExtensions = array(
        'jpg',
        'jpeg',
        'png',
        'gif'
    );
    
    public static $validPreviewExtensions = array(
//        'jpg',
//        'jpeg',
//        'png',
//        'gif',
        'pdf'
    );
    
    //@todo add bmp back when there is a way to create bmp thumbs
    protected static $fileTypeClasses = array(
        'jpg' => 'image',
        'gif' => 'image',
        'png' => 'image',
        'bmp' => 'image',
        'jpeg' => 'image',
        'tif' => 'image',
        'zip' => 'compressed',
        'gtar' => 'compressed',
        'tar' => 'compressed',
        'avi' => 'video',
        'wmv' => 'video',
        'mov' => 'video',
        'qt' => 'video',
        'mp4' => 'video',
        'mp3' => 'audio',
        'mpeg' => 'audio',
        'swf' => 'video',
        'wav' => 'audio',
        'ogg' => 'video',
        'ogv' => 'video',
        'doc' => 'text',
        'docx' => 'text',
        'pdf' => 'pdf',
        'ppt' => 'ppt',
        'xls' => 'text',
        'xlsx' => 'text',
        'xml' => 'text',
        'csv' => 'text',
        'txt' => 'text',
        'ttf' => 'text',
        'eot' => 'text',
        'otf' => 'text',
        'dfont' => 'text',
        'ai' => 'ai',
        'eps' => 'image',
        'indd' => 'indd',
        'ps' => 'psd',
        'psd' => 'psd',
        'fla' => 'fla',
        'ico' => 'image',
        'css' => 'code',
        'html' => 'code',
        'htm' => 'code',
        'js' => 'code',
        'php' => 'code',
        'ae' => 'ae'
    );
    
    protected static $mimeTypes = array(
        'image/bmp' => 'bmp',
        'image/cis-cod' => 'cod',
        'image/gif' => 'gif',
        'image/ief' => 'ief',
        'image/jpeg' => 'jpg',
        'image/pipeg' => 'jfif',
        'image/tiff' => 'tif',
        'image/x-cmu-raster' => 'ras',
        'image/x-cmx' => 'cmx',
        'image/x-icon' => 'ico',
        'image/x-portable-anymap' => 'pnm',
        'image/x-portable-bitmap' => 'pbm',
        'image/x-portable-graymap' => 'pgm',
        'image/x-portable-pixmap' => '.ppm',
        'image/x-rgb' => 'rgb',
        'image/x-xbitmap' => 'xbm',
        'image/x-xpixmap' => 'xpm',
        'image/x-xwindowdump' => 'xwd',
        'image/png' => 'png',
        'image/x-jps' => 'jps',
        'image/x-freehand' => 'fh'
    );
    
    public static function getExtensionFromMimeType($mimeType){
        $lowerMimeType = strtolower($mimeType);
        if(isset(static::$mimeTypes[$lowerMimeType])){
            return static::$mimeTypes[$lowerMimeType];
        }
        
        return NULL;
    }
    
    public static function getMimeTypeFromExtension($imgExt){
        $lowerImgExt = strtolower($imgExt);
        $mimeType = array_search($imgExt, static::$mimeTypes);
        return $mimeType;
    }
    
    public static function isValidImageExtension($imgExt){
        $lowerImgExt = strtolower($imgExt);
        if(in_array($lowerImgExt, static::$validImageExtensions)){
            return true;
        }
        return false;
    }

    /**
     * Resizes image by thumbnail width x thumbnail height
     * 
     * @param int $tWidth thumbnail width
     * @param int $tHeight thumbnail height
     * @param string $imagePath Aws\S3 file path including file name
     * @return string resized image's file path including file name 
     */
    public static function resizeImage($tWidth, $tHeight, $imagePath) {
        $thumbWidth = $tWidth;
        $thumbHeight = $tHeight;
        //RESIZES AN IMAGE AND KEEPS THE ASPECT RATIO
        /* set the width to 0 to resize only on the height and vise versa */
        list($oWidth, $oHeight) = getimagesize($imagePath);
        //currently ignoring exif_read_data errors because of a PHP bug (throwing warning)
        //$exif = @exif_read_data($imagePath);
        if (function_exists('exif_read_data') && @exif_imagetype($imagePath) == 2) { //image/jpeg
            $exif = @exif_read_data($imagePath);
        } else {
            $exif = array();
        }

        $rotateDeg = NULL;
        if (!empty($exif['Orientation'])) {
            switch ($exif['Orientation']) {
                case 3:
                    $rotateDeg = 180;
                    break;
                case 6:
                    $rotateDeg = -90;
                    $tmpOWidth = $oWidth;
                    $oWidth = $oHeight;
                    $oHeight = $tmpOWidth;
                    break;
                case 8:
                    $rotateDeg = 90;
                    $tmpOWidth = $oWidth;
                    $oWidth = $oHeight;
                    $oHeight = $tmpOWidth;
                    break;
            } 
        }
        $aspectRatio = $oWidth / $oHeight;

        if ($aspectRatio < 1 || $tWidth == 0) {
            $imgOrientation = 'portrait';
            if ($tWidth > $tHeight * $aspectRatio) {
                $tHeight = $tWidth / $aspectRatio;
            } else {
                $tWidth = $tHeight * $aspectRatio;
            }
        } elseif ($aspectRatio > 1 || $tHeight == 0) {
            $imgOrientation = 'landscape';
            if ($tHeight > $tWidth / $aspectRatio) {
                $tWidth = $tHeight * $aspectRatio;
            } else {
                $tHeight = $tWidth / $aspectRatio;
            }
        } elseif ($aspectRatio == 1) {
            $imgOrientation = 'square';
            if ($tHeight > $tWidth) {
                $tWidth = $tHeight;
            } else {
                $tHeight = $tWidth;
            }
        }
        $imgInfo = finfo_open(FILEINFO_MIME_TYPE);
        $imgMime = finfo_file($imgInfo, $imagePath);
        $newImagePath = static::generateS3KeyForThumbnail($thumbWidth, $thumbHeight, $imagePath);
        switch ($imgMime) {
            case 'image/jpeg':
                $newImg = imagecreatefromjpeg($imagePath);
                if(!is_null($rotateDeg)){
                    $newImg = imagerotate($newImg, $rotateDeg, 0);
                }
                break;
            case 'image/png':
                $newImg = imagecreatefrompng($imagePath);
                if(!is_null($rotateDeg)){
                    $newImg = imagerotate($newImg, $rotateDeg, 0);
                }
                imagesavealpha($newImg, false);
                break;
            case 'image/gif':
                $newImg = imagecreatefromgif($imagePath);
                if(!is_null($rotateDeg)){
                    $newImg = imagerotate($newImg, $rotateDeg, 0);
                }
                break;
        }
        $finalImg = imagecreatetruecolor($tWidth, $tHeight);
        imagealphablending($finalImg, false);
        imagesavealpha($finalImg, true);
        imagecopyresampled($finalImg, $newImg, 0, 0, 0, 0, $tWidth, $tHeight, $oWidth, $oHeight);
        switch ($imgMime) {
            case 'image/jpeg':
                imagejpeg($finalImg, $newImagePath, 100);
                break;
            case 'image/png':
                imagepng($finalImg, $newImagePath, 0);
                break;
            case 'image/gif':
                imagegif($finalImg, $newImagePath);
                break;
        }
        imagedestroy($newImg);
        imagedestroy($finalImg);
        $newImagePath = trim($newImagePath);
        return $newImagePath;
    }

    /**
     * Gets file path out of file path + file name string
     * 
     * @param string $pathAndFilename file path + file name string
     * @return string file path without file name
     */
    public static function getPathFromPathAndFilename($pathAndFilename) {
        $fileInfo = pathinfo($pathAndFilename);
        $dir = '';
        if(!empty($fileInfo['dirname'])){
            $dir = $fileInfo['dirname'] . '/';
        }
        return $dir;
    }
    
    /**
     * Generates AWS key base
     *
     * @return string AWS key base
     */
    public static function generateAWSKeyBase() {
        $keyBase = Login::getUserId(true) . '/' . date('Y/m_M/d/');
        return $keyBase;
    }
    
    /**
     * Gets AWS image URL resized by thumbnail width x thumbnail height
     * 
     * @param int $tWidth thumbnail width
     * @param int $tHeight thumbnail height
     * @param string $s3Bucket Aws\S3 file bucket
     * @param string $s3Key Aws\S3 key
     * @return string Aws image URL. NULL if image doesn't exist
     */
    public static function getAWSImageThumbURL($tWidth, $tHeight, $s3Bucket, $s3Key) {
        $s3Client = S3Connection::getInstance();
        
        $thumbKey = static::generateS3KeyForThumbnail($tWidth, $tHeight, $s3Key);
        $exists = $s3Client->doesObjectExist($s3Bucket, $thumbKey);
        if ($exists) {
            $cmd = $s3Client->getCommand('GetObject', [
                'Bucket' => $s3Bucket,
                'Key' => $thumbKey
            ]);
            $request = $s3Client->createPresignedRequest($cmd, '+20 minutes');
            $presignedUrl = (string) $request->getUri();
            return $presignedUrl;
        } else {
            return NULL;
        }
    }

    protected static function generateAWSImageThumbSuffix($tWidth, $tHeight) {
        $suffix = $tWidth . $tHeight;
        return $suffix;
    }
    
    /**
     * Gets file extension from Aws\S3 key
     * 
     * @param string $s3Key Aws\S3 key
     * @return string file extension
     */
    public static function getFileExtensionFromAWSS3Key($s3Key) {
        $fileParts = pathinfo($s3Key);
        $ext = $fileParts['extension'];
        return $ext;
    }
    
    /**
     * Generates image's Aws\S3 key resized by thumbnail width x thumbnail height
     * 
     * @param int $tWidth thumbnail width
     * @param int $tHeight thumbnail height
     * @param string $sourceS3Key original image's Aws\S3 key
     * @return string resized image's Aws\S3 key
     */
    public static function generateS3KeyForThumbnail($tWidth, $tHeight, $sourceS3Key) {
        $suffix = static::generateAWSImageThumbSuffix($tWidth, $tHeight);
        
        $fileInfo = pathinfo($sourceS3Key);
        $dir = $fileInfo['dirname'];
        $file = $fileInfo['filename'];
        $ext = $fileInfo['extension'];
        $thumbKey = '';
        if(!empty($dir)){
            $thumbKey .= $dir . '/';
        }
        $thumbKey .= $file . $suffix . '.' . $ext;
        return trim($thumbKey);
    }

    /**
     * Adds file to Aws\S3
     * 
     * @param string $path source file's path
     * @param string $s3Bucket Aws\S3 file bucket
     * @param string $s3Key Aws\S3 key
     * @return boolean true/false: success/failure
     */
    public static function addFileToS3($path, $s3Bucket, $s3Key) {
        $s3Client = S3Connection::getInstance();
        try {
            $params = array(
                'Bucket' => $s3Bucket,
                'Key' => $s3Key,
                'SourceFile' => $path,
                'ACL' => 'authenticated-read'
            );
            
            $mimeType = mime_content_type($path);
            if(!empty($mimeType)){
                $params['ContentType'] = $mimeType;
            }
            $params['ContentDisposition'] = 'inline';
            $result = $s3Client->putObject($params);
        } catch (Exception $ex) {
            return false;
        }
        if ($result['ObjectURL']) {
            return true;
        }
        return false;
    }
    
    public static function saveToS3($path, $s3Path){
        $s3Bucket = ProjectConfig::getAWSBucket();
        if(static::addFileToS3($path, $s3Bucket, $s3Path)){
            return static::getS3URL($s3Path);
        }
        return NULL;
    }
    
    public static function getS3URL($s3Path) {
        $s3Client = S3Connection::getInstance();
        $s3Bucket = ProjectConfig::getAWSBucket();
        $exists = $s3Client->doesObjectExist($s3Bucket, $s3Path);
        if ($exists) {
            $cmd = $s3Client->getCommand('GetObject', [
                'Bucket' => $s3Bucket,
                'Key' => $s3Path
            ]);
            $request = $s3Client->createPresignedRequest($cmd, '+20 minutes');
            $presignedUrl = (string) $request->getUri();
        } else {
            $presignedUrl = NULL;
        }
        return $presignedUrl;
    }
    
    public static function doesS3FileExist($s3Path){
        $s3Client = S3Connection::getInstance();
        $s3Bucket = ProjectConfig::getAWSBucket();
        $exists = $s3Client->doesObjectExist($s3Bucket, $s3Path);
        if ($exists) {
            return true;
        }
        return false;
    }
    
    /**
     * Removes file from Aws\S3
     * 
     * @param string $s3Bucket Aws\S3 file bucket
     * @param string $s3Key Aws\S3 key
     * @return boolean true/false: success/failure
     */
    public static function removeFileFromS3($s3Bucket, $s3Key) {
        $s3Client = S3Connection::getInstance();
        try {
            $result = $s3Client->deleteObject(array(
                'Bucket' => $s3Bucket,
                'Key' => $s3Key
            ));
        } catch (Exception $ex) {
            return false;
        }
        try {
            //deleteObject is idempotent, so verfiy file was removed before returning
            $verify = $s3Client->doesObjectExist($s3Bucket, $s3Key);
            if ($verify == false) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $ex) {
            //TODO = add Logging
            return false;
        }
    }
    
    public static function removeFromS3($s3Path){
        $s3Bucket = ProjectConfig::getAWSBucket();
        return static::removeFileFromS3($s3Bucket, $s3Path);
    }
    
    /**
     * Saves a file from Aws\S3 to local  
     * 
     * @return string saved local file's path including file name
     */
    public function saveToTemp(){
        $s3Bucket = $this->getProperty('aws_s3_bucket');
        $s3Key = $this->getProperty('aws_s3_key');
        $localURL = static::saveFileFromS3($s3Bucket, $s3Key);
        
        $mimeType = mime_content_type($localURL);
        if($mimeType == 'application/pdf'){
            $tmpPDFName = str_replace('.pdf', '_tmp.pdf', $localURL);
            rename($localURL, $tmpPDFName);
            
            $cmd = 'gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile="' . $localURL . '" "' . $tmpPDFName . '"';
            
            $result = shell_exec($cmd);
            if(file_exists($localURL)){
                unlink($tmpPDFName);
            } else {
                //command failed so rename back to original
                rename($tmpPDFName, $localURL);
            }
        }
        
        return $localURL;
    }

    /**
     * Saves file from Aws\S3
     * 
     * @param string $s3Bucket Aws\S3 file bucket
     * @param string $s3Key Aws\S3 key
     * @return string saved local file's path including file name
     */
    public static function saveFileFromS3($s3Bucket, $s3Key, $path = '') {
        $date = new DateTime();
        $dateFolders = $date->format('Y/m_M/d/');
        if (empty($path)) {
            $path = 'tempData/user/' . Login::getUserId(true) . '/' . $dateFolders;
        }
        File::createTempDataFolders($path);
        $s3Client = S3Connection::getInstance();
        if(empty($s3Client)){
            return NULL;
        }
        try {
            $result = $s3Client->getObject(array(
                'Bucket' => $s3Bucket,
                'Key' => $s3Key,
            ));
        } catch(Exception $ex) {
            //@todo result failed
            return NULL;
        }
        $filename = static::getFilenameFromAwsS3Key($s3Key);
        file_put_contents($path . $filename, (string) $result['Body']);
        chmod($path . $filename, 0777);
        return $path . $filename;
    }

    /**
     * Gets file name from Aws\S3 key
     * 
     * @param string $s3Key Aws\S3 key
     * @return string file name
     */
    public static function getFilenameFromAwsS3Key($s3Key) {
        $fileInfo = pathinfo($s3Key);
        $base = $fileInfo['basename'];
        return $base;
    }

    /**
     * Creates physical temporary directory and set access permission if it deosn't exist 
     * 
     * @param string $path temporary directory
     */
    public static function createTempDataFolders($path) {
        $folders = explode('/', $path);
        $currentDir = '';
        foreach($folders as $folder){
            if(empty($currentDir)){
                $currentDir = $folder;
            } else {
                $currentDir .= '/' . $folder;
            }
            
            if (!is_dir($currentDir)) {
                mkdir($currentDir);
                chmod($currentDir, 0777);
            }
        }
    }

    /**
     * Deletes physical directory and files 
     * 
     * @param string $path directory to be deleted
     */
    public static function deleteDir($path) {
        if (!DEV_MODE) {
            if (!is_dir($path)) {
                throw new InvalidArgumentException($path . ' must be a directory');
            }
            if (substr($path, strlen($path) - 1, 1) != '/') {
                $path .= '/';
            }
            $files = glob($path . '*', GLOB_MARK);
            foreach ($files as $file) {
                if (is_dir($file)) {
                    static::deleteDir($file);
                } else {
                    unlink($file);
                }
            }
            rmdir($path);
        }
    }
    
    public function isImage(){
        $validImageExtensions = File::$validImageExtensions;
        $s3Key = $this->getProperty('aws_s3_key');
        $fileExtension = strtolower(File::getFileExtensionFromAWSS3Key($s3Key));
        $isImage = in_array($fileExtension, $validImageExtensions);
        return $isImage;
    }
    
    /**
     * Gets resized image's Aws\S3 URL
     * 
     * @param int $width resized width
     * @param int $height resized height
     * @return string resized image's Aws\S3 URL. NULL if a File object is not an image
     */
    public function getResizedImage($width, $height){
        $s3Key = $this->getProperty('aws_s3_key');
        $isImage = $this->isImage();
        
        if ($isImage) {
            $s3Bucket = $this->getProperty('aws_s3_bucket');
            $imageLink = File::getAWSImageThumbURL($width, $height, $s3Bucket, $s3Key);
            if (empty($imageLink)) {
                $path = File::saveFileFromS3($s3Bucket, $s3Key);
                $imagePath = File::resizeImage($width, $height, $path);
                $imageS3Key = File::generateS3KeyForThumbnail($width, $height, $s3Key);
                File::addFileToS3($imagePath, $s3Bucket, $imageS3Key);
                $imageLink = File::getAWSImageThumbURL($width, $height, $s3Bucket, $s3Key);
                static::deleteTempDir();
            }
        } else {
            //not an image
            $imageLink = NULL;
        }
        
        return $imageLink;
    }
    
    /**
     * Deletes physical temporary directory('tempData/user/' . Login::getUserId() . '/') and files 
     */
    public static function deleteTempDir(){
        if(!DEV_MODE){
            File::deleteDir('tempData/user/' . Login::getUserId(true) . '/');
        }
    }
    
    /**
     * Gets File object's URL 
     * 
     * @return string URL
     */
    public function getFileURL(){
        $fileURL = $this->getFileS3URL();
        return $fileURL;
    }

    protected $overrideURL = NULL;
    public function setOverrideURL($overrideURL){
        $this->overrideURL = $overrideURL;
        return $this;
    }
    
    /**
     * Gets File object's URL in case the file is in Aws\S3
     * 
     * @return string URL, NULL if it doesn't exist
     */
    public function getFileS3URL() {
        if(!empty($this->overrideURL)){
            return $this->overrideURL;
        }
        $s3Client = S3Connection::getInstance();
        if (empty($s3Client)) {
            return NULL;
        }
        $s3Bucket = $this->getProperty('aws_s3_bucket');
        $s3Key = $this->getProperty('aws_s3_key');
        if(empty($s3Bucket) || empty($s3Key)){
            return NULL;
        }
        $exists = $s3Client->doesObjectExist($s3Bucket, $s3Key);
        if ($exists) {
            $cmd = $s3Client->getCommand('GetObject', [
                'Bucket' => $s3Bucket,
                'Key' => $s3Key
            ]);
            $request = $s3Client->createPresignedRequest($cmd, '+20 minutes');
            $presignedUrl = (string) $request->getUri();
        } else {
            $presignedUrl = NULL;
        }
        return $presignedUrl;
    }
    
    /**
     * Gets file's CSS class by extension
     * 
     * @param string $extension Extension of file
     * @return string CSS class for thumbnail styling of file type
     */
    public static function getFileTypeClass($extension) {
        $ext = strtolower($extension);
        if (isset(static::$fileTypeClasses[$ext])) {
            return static::$fileTypeClasses[$ext];
        } else {
            return 'other';
        }
    }
    
    public function getPredefinedResizedImage($type){
        switch ($type) {
            case 'thumbnail':
                $width = 88;
                $height = 70;
                break;
            case 'small':
                $width = 39;
                $height = 30;
                break;
            case 'avatar':
                $width = 34;
                $height = 34;
                break;
            case 'avatar_small':
                $width = 30;
                $height = 30;
                break;
            case 'avatar_large':
                $width = 100;
                $height = 100;
                break;
        }
        
        return $this->getResizedImage($width, $height);
    }
    
    public function getExtension(){
        $fileBasename = $this->getProperty('filename');
        $fileParts = pathinfo($fileBasename);
        $ext = $fileParts['extension'];
        $fileName = $fileParts['filename'];
        return $ext;
    }
    
    public function getDisplayName($withExtension = false){
        $fileBasename = $this->getProperty('display_name');
        if($withExtension){
            return $fileBasename;
        } else {
            $fileParts = pathinfo($fileBasename);
            $ext = $fileParts['extension'];
            $fileName = $fileParts['filename'];
            return $fileName;
        }
    }
    
    public function getAltTag(){
        $altTag = $this->getProperty('alt_tag');
        if(empty($altTag)){
            $altTag = $this->getTitleTag();
        }
        return $altTag;
    }
    
    public function getTitleTag(){
        $titleTag = $this->getProperty('title_tag');
        if(empty($titleTag)){
            $titleTag = $this->getProperty('display_name');
        }
        return $titleTag;
    }
    
    /**
     * 
     * @param string $type
     * @param AbstractGI_Uploader $uploaderName
     * @return type
     */
    public function getView($type = 'thumbnail', AbstractGI_Uploader $uploader = NULL){
        switch($type){
            case 'avatar':
                $view = $this->getAvatarView();
                break;
            case 'small_thumbnail':
                $view = $this->getSmallThumbnailView($uploader);
                break;
            case 'thumbnail':
            default:
                $view = $this->getThumbnailView($uploader);
                break;
        }
        return $view;
    }
    
    public function getThumbnailView(AbstractGI_Uploader $uploader = NULL){
        $view = new FileThumbnailView($this);
        $view->setUploader($uploader);
        return $view;
    }
    
    public function getSmallThumbnailView(AbstractGI_Uploader $uploader = NULL){
        $view = new FileSmallThumbnailView($this);
        $view->setUploader($uploader);
        return $view;
    }
    
    public function getAvatarView(){
        $view = new FileAvatarView($this);
        return $view;
    }
    
    /**
     * @param int $width
     * @param int $height
     * @return \AbstractFileSizedView
     */
    public function getSizedView($width = NULL, $height = NULL){
        $view = new FileSizedView($this);
        if(!is_null($width) && !is_null($height)){
            $view->setDimensions($width, $height);
        }
        return $view;
    }
    
    public static function getNewFileName($path, $curFileName){
        $fileParts = pathinfo($curFileName);
        $ext = $fileParts['extension'];
        $name = GI_Sanitize::filename($fileParts['filename']);
        $cleanFileName = $name . '.' . $ext;
        
        $newPath = $path . '/' . $cleanFileName;
        $newName = $cleanFileName;
        $counter = 0;
        while (file_exists($newPath)) {
            $newName = $name . '_' . $counter . '.' . $ext;
            $newPath = $path . '/' . $newName;
            $counter++;
         }

        return $newName;
    }
    
    /**
     * Get sized view with keeping w/h ratio
     * @param type $resizedWidth
     * @param type $resizedHeight
     * @return \FileSizedView
     */
    public function getSizedViewKeepRatio($resizedWidth = NULL, $resizedHeight = NULL){
        $view = new FileSizedView($this);
        if ($resizedWidth != NULL || $resizedHeight != NULL) {
            // Get original image's size to calculate ratio
            $s3Key = $this->getProperty('aws_s3_key');
            $s3Bucket = $this->getProperty('aws_s3_bucket');
            $imagePath = File::saveFileFromS3($s3Bucket, $s3Key);

            if($imagePath){
                list($oWidth, $oHeight) = getimagesize($imagePath);

                $aspectRatio = $oWidth / $oHeight;

                if ($resizedWidth == NULL && $resizedHeight != NULL) {
                    $resizedWidth = $resizedHeight * $aspectRatio;
                } else if ($resizedWidth != NULL && $resizedHeight == NULL) {
                    $resizedHeight = $resizedWidth / $aspectRatio;
                }
            }
            $view->setDimensions($resizedWidth, $resizedHeight);
        }

        return $view;
    }
    
}
