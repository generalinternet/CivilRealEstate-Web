<?php
$curIncludePath = get_include_path();
set_include_path($curIncludePath . '/' . FRMWK_CORE_VER);

require_once 'controller/AbstractFileController.php';

//Abstract GI_Uploader
require_once 'model/Utility/Uploader/AbstractGI_Uploader.php';
require_once 'model/Utility/Uploader/AbstractGI_UploaderBrowser.php';
require_once 'model/Utility/Uploader/AbstractGI_UploaderFactory.php';

//Abstract Domains
require_once 'model/Domain/File/AbstractFile.php';
require_once 'model/Domain/File/AbstractFileSignature.php';
require_once 'model/Domain/Folder/AbstractFolder.php';

//Abstract Factories
require_once 'model/Factory/AbstractFileFactory.php';
require_once 'model/Factory/AbstractFolderFactory.php';

//Abstract Views
require_once 'view/file/AbstractFileEditFormView.php';
require_once 'view/file/AbstractFileIndexView.php';
    //file views
require_once 'view/file/file/AbstractFileThumbnailView.php';
require_once 'view/file/file/AbstractFileSmallThumbnailView.php';
require_once 'view/file/file/AbstractFileAvatarView.php';
require_once 'view/file/file/AbstractFileAvatarPlaceholderView.php';
require_once 'view/file/file/AbstractFileSizedView.php';
    //folder views
require_once 'view/file/folder/AbstractFileFolderThumbnailView.php';
require_once 'view/file/folder/AbstractFolderFormView.php';
require_once 'view/file/folder/AbstractFolderDirectoryView.php';

//Concrete
set_include_path('concrete/core/' . FRMWK_SUPER_VER);
//GI_Uploader
require_once 'model/Utility/Uploader/GI_Uploader.php';
require_once 'model/Utility/Uploader/GI_UploaderBrowser.php';
require_once 'model/Utility/Uploader/GI_UploaderFactory.php';

//Concrete Domains
require_once 'model/Domain/Core/File/File.php';
require_once 'model/Domain/Core/File/FileSignature.php';
require_once 'model/Domain/Core/Folder/Folder.php';

//Concrete Factories
require_once 'model/Factory/FileFactory.php';
require_once 'model/Factory/FolderFactory.php';

//Concrete Views
require_once 'view/file/file_editFormView.php';
require_once 'view/file/file_indexView.php';
    //file views
require_once 'view/file/file/file_thumbnailView.php';
require_once 'view/file/file/file_smallThumbnailView.php';
require_once 'view/file/file/file_avatarView.php';
require_once 'view/file/file/file_avatarPlaceholderView.php';
require_once 'view/file/file/file_sizedView.php';
    //folder views
require_once 'view/file/folder/file_folderThumbnailView.php';
require_once 'view/file/folder/folder_formView.php';
require_once 'view/file/folder/folder_directoryView.php';

set_include_path($curIncludePath);
