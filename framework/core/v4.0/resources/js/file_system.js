/*
file_system.js v2.0.0
*/
function createGIUploader(uploaderName, containerId, awsURL, awsKey, awsPolicy, awsSignature, keyBase, multipartParams){
    if(jQuery.type(multipartParams) != 'object'){
        //old aws authentication
        multipartParams = {
            "acl": "authenticated-read",
            "Content-Type": "",
            "Content-Disposition" : 'inline',
            "AWSAccessKeyId" : awsKey,
            "policy": awsPolicy,
            "signature": awsSignature
        };
    }
    var container = $('#' + containerId);
    window[uploaderName] = null;
    //create uploader variable
    window[uploaderName] = new plupload.Uploader({
        //uploader settings
        runtimes : 'html5,flash,silverlight',
        browse_button: container.find('.browse_computer')[0],
        drop_element: container.find('.dropzone')[0],
        url : awsURL,
        flash_swf_url : 'resources/external/js/pluploader/Moxie.swf',
        silverlight_xap_url : 'resources/external/js/pluploader/Moxie.xap',
        filters : {
            max_file_size : '2gb',
            mime_types: window['mime_types_' + container.data('mime-types')]
        },
        file_data_name: 'file',
        multipart: true,
        multipart_params: multipartParams,
        verifyfilecount : function(){
            var fileLimit = container.data('file-limit');
            var curCount = container.find('.files_area .file_wrap').length;
            var browseBtn = container.find('.browse_computer');
            if(fileLimit == 0 || curCount < fileLimit){
                browseBtn.removeClass('limit_reached');
                window[uploaderName].disableBrowse(false);
                return true;
            } else {
                browseBtn.addClass('limit_reached');
                window[uploaderName].disableBrowse(true);
                return false;
            }
        },
                
        //open init function
        init: {
            //post init
            PostInit: function(up) {
                container.find('.uploading_files').html('');
                up.settings.verifyfilecount();
            },

            //after file is uploaded
            FileUploaded : function(up, file, object) {
                var fileInplup = file.inplup;
                var uploadURL = '';
                if(fileInplup != undefined){
                    uploadURL = fileInplup.data('upload-url');
                }
                var fullURL = 'index.php?controller=file&action=saveUploadData&ajax=1&displayName=' + file.name + '&key=' + up.settings.multipart_params.key + '&size=' + file.size + uploadURL;
                jQuery.post(fullURL, function(data){
                    //var parsedData = JSON.parse(data);
                    var uploaderType = '';
                    if(fileInplup != undefined){
                        uploaderType = fileInplup.data('upload-type');
                    }
                    if (uploaderType == 'basic') {
                        fileInplup.find('.files_area').prepend(data.content);
                        $('#uploading_file_' + file.id).remove();
                    }
                    $('body').trigger('fileUploaded', {
                        fileId: data.fileId,
                        uploaderName: uploaderName
                    });
                    container.trigger('fileUploaded', {
                        fileId: data.fileId
                    });
                });
            },

            //before upload
            BeforeUpload: function(up, file) {
                up.settings.multipart_params.Filename = file.name;
                var awsFileName = keyBase + jQuery.now() + '/' + file.name;
                up.settings.multipart_params.key = awsFileName;
                
                up.settings.multipart_params["Content-Type"] = file.type;
                up.settings.multipart_params["Content-Disposition"] = 'inline';
                up.settings.verifyfilecount();
            },

            //files are removed
            FilesRemoved: function(up, files){
                up.settings.verifyfilecount();
            },

            //files are added
            FilesAdded: function(up, files) {
                plupload.each(files, function(file) {
                    if(up.settings.verifyfilecount()){
                    file.inplup = container;
                        var shortFileName = file.name;
                        var fileNameLen = shortFileName.length;
                        if(fileNameLen>14){
                            shortFileName = shortFileName.substr(0, 14);
                        }
                        file.inplup.find('.files_area').append('<div id="uploading_file_' + file.id + '" class="file_wrap uploading"><div class="file_thumb uploading" title="' + file.name + '" ><span class="cancel_file" data-file-id="' + file.id + '"></span><span class="ext percentage"><span class="ext_icon"></span><span class="ext_title">0%</span></span><span class="filename">' + shortFileName + '</span><span class="corner"></span><span class="percentage_bar"></span></div></div>');
                        container.trigger('fileAdded', {
                            fileId: file.id
                        });
                    } else {
                        var fileLimit = container.data('file-limit');
                        giModalConfirm('Uploader Error', 'File limit of ' + fileLimit + ' reached.');
                        up.removeFile(file);
                    }
                });
                up.start();
                var dropzone = container.find('.dropzone');
                if(dropzone.length){
                    dropzone.removeClass('over');
                    dropzone.removeClass('incoming');
                }
            },

            //as files are uploading
            UploadProgress: function(up, file) {
                $('#uploading_file_' + file.id + ' .percentage .ext_title').html(file.percent + '%');
                $('#uploading_file_' + file.id + ' .percentage_bar').css({width: file.percent + '%'});
            },

            //if there's an error
            Error: function(up, err) {
                giModalConfirm('Uploader Error ' + err.code, err.message);
                console.log(err);
            }

        }
    });
    window[uploaderName].init();
    makeFilesSortable();
}

function removeFolderLink(file, fileId, folderId){
    var uploaderContainer = file.closest('.uploader_container');
    var targetDocId = uploaderContainer.data('target-doc-id');
    var documentIdAttr = '';
    if(targetDocId != undefined){
        documentIdAttr = '&documentId=' + targetDocId;
    }
    jQuery.post('index.php?controller=file&action=deleteFolderLink&fileId=' + fileId + '&folderId=' + folderId + documentIdAttr + '&ajax=1', function (data) {
        //var parsedData = JSON.parse(data);
        if(data.content){
            file.remove();
            var uploaderName = uploaderContainer.data('uploader-name');
            $('body').trigger('fileRemoved', {
                fileId: fileId,
                uploaderName: uploaderName
            });
            uploaderContainer.trigger('fileRemoved', {
                fileId: data.fileId
            });
            window[uploaderName].settings.verifyfilecount();
        } else {
            console.log(data);
            alert('An error occurred when deleting file.');
        }
    });
}

$(document).on('click', '.extra_browse_files', function(e){
    e.preventDefault();
    var containerId = $(this).data('container-id');
    var container = $('#' + containerId);
    if(container.length){
        var browseBtn = container.find('.browse_computer')[0];
        browseBtn.click();
    }
});

$(document).on('click','.remove_file',function(e){
    e.preventDefault();
    var uploaderContainer = $(this).closest('.uploader_container');
    var uploaderName = uploaderContainer.data('uploader-name');
    var fileId = $(this).data('file-id');
    var file = uploaderContainer.find('#file_'+fileId);
    var folderId = $(this).closest('.uploader_container').data('target-folder-id');
    var inForm = $(this).closest('form').length;
    giModalConfirm('Delete File?', 'Are you sure you want to delete this file?', 'Yes', function(){
        if(!inForm){
            removeFolderLink(file, fileId, folderId);
        } else {
            file.remove();
            $('body').trigger('fileRemoved', {
                fileId: fileId,
                uploaderName: uploaderName
            });
            uploaderContainer.trigger('fileRemoved', {
                fileId: fileId
            });
            window[uploaderName].settings.verifyfilecount();
        }
    }, 'No');
});

$(document).on('click', '.edit_file', function(e){
    e.preventDefault();
    giModalOpenForm($(this), e, 'medium_sized');
});

function verifyDraggingFile(dragObject){
    var dt = dragObject.originalEvent.dataTransfer;
    if(dt.types != null && (dt.types.indexOf ? dt.types.indexOf('Files') != -1 : dt.types.contains('application/x-moz-file'))) {
        return true;
    } else {
        return false;
    }
}

$(document).on('dragover','body',function(e) { 
    if(verifyDraggingFile(e)) {
        $('.dropzone').addClass('incoming');
    }
});

$(document).on('dragleave','body',function() { 
    $('.dropzone').removeClass('incoming');
});

$(document).on('dragover','.dropzone',function(e) {    
    if(verifyDraggingFile(e)) {
        $(this).addClass('over');
    }
});

$(document).on('dragleave','.dropzone',function() {
    $(this).removeClass('over');
});

$(document).on('click','.cancel_file',function(){
    var fileId = $(this).data('file-id');
    var uploaderContainer = $(this).closest('.uploader_container');
    var uploaderName = uploaderContainer.data('uploader-name');
    window[uploaderName].removeFile(window[uploaderName].getFile(fileId));
    var file = $(this).closest('.file_wrap');
    file.remove();
});

function makeFilesSortable(){
    $('.files_area').sortable({
        handle: '.corner',
        items: '> .file_wrap',
        placeholder: 'file_placeholder',
        opacity: 0.5,
        tolerance: 'pointer',
        stop: function(){
            if(!$(this).closest('form').length){
                var uploaderContainer = $(this).closest('.uploader_container');
                var saveOrderBtn = uploaderContainer.find('.save_order');
                saveOrderBtn.addClass('allow_save');
            }
        }
    });
}

$(function(){
    makeFilesSortable();
});

$(document).on('click', '.save_order:not(.disabled)', function(){
    var saveOrderBtn = $(this);
    var uploaderContainer = saveOrderBtn.closest('.uploader_container');
    var filesArea = uploaderContainer.find('.files_area');
    var folderId = uploaderContainer.data('target-folder-id');
    var position = 0;
    filesArea.find('.file_wrap').each(function(){
        var fileId = $(this).data('id');
        jQuery.post('index.php?controller=file&action=positionFolderLink&fileId=' + fileId + '&folderId=' + folderId + '&position=' + position + '&ajax=1', function (data) {
            //var parsedData = JSON.parse(data);
            if(data.content){
                saveOrderBtn.removeClass('allow_save');
            } else {
                alert('An error occurred when sorting file.');
            }
        });
        position++;
    });
});

function replaceFileIconView(fileId, iconView){
    giModalClose();
    $('#file_' + fileId).replaceWith(iconView);
    makeFilesSortable();
}

function replaceDir(dir){
    var folderId = dir.data('folder-id');
    var subDir = dir.find('.sub_directory_wrap:first');
    elmStartLoading(dir);
    var uploaderName = dir.data('uploader-name');
    var containerId = dir.data('container-id');
    var subDirURL = 'index.php?controller=file&action=getDirectoryView&folderId=' + folderId;
    if(uploaderName != undefined && uploaderName != ''){
        subDirURL += '&uploaderName=' + uploaderName;
    }
    if(containerId != undefined && containerId != ''){
        subDirURL += '&containerId=' + containerId;
    }
    jQuery.post(subDirURL + '&ajax=1', function (data) {
        dir.replaceWith(data.mainContent);
        newContentLoaded();
    });
}

function replaceDirByFolderId(folderId){
    $('.folder_row[data-folder-id="' + folderId + '"]').each(function(){
        replaceDir($(this));
    });
}

function toggleOpenDir(dir){
    var folderId = dir.data('folder-id');
    var subDir = dir.find('.sub_directory_wrap:first');
    var icon = dir.find('.open_directory:first .icon');
    if(subDir.is('.open')){
        icon.removeClass('arrow_down');
        icon.addClass('arrow_right');
        subDir.slideUp(500, function(){
            subDir.removeClass('open');
        });
    } else {
        if(subDir.html() == ''){
            elmStartLoading(subDir);
            var uploaderName = dir.data('uploader-name');
            var containerId = dir.data('container-id');
            var subDirURL = 'index.php?controller=file&action=getDirectoryView&folderId=' + folderId;
            if(uploaderName != undefined && uploaderName != ''){
                subDirURL += '&uploaderName=' + uploaderName;
            }
            if(containerId != undefined && containerId != ''){
                subDirURL += '&containerId=' + containerId;
            }
            jQuery.post(subDirURL + '&ajax=1', function (data) {
                dir.replaceWith(data.mainContent);
                newContentLoaded();
            });
        }
        subDir.slideDown(500);
        subDir.addClass('open');
        icon.removeClass('arrow_right');
        icon.addClass('arrow_down');
    }
}

$(document).on('click', '.open_directory', function(){
    var dir = $(this).closest('.folder_row');
    toggleOpenDir(dir);
});

$(document).on('click', '.open_folder', function () {
    var folderBtn = $(this);
    folderBtn.closest('.folder_directory').find('.open_folder.open').removeClass('open');
    var folderRow = $(this).closest('.folder_row');
    var folderId = folderRow.data('folder-id');
    var uploaderName = folderRow.data('uploader-name');
    var containerId = folderRow.data('container-id');
    var container = $('#' + containerId);
    var mimeTypes = container.data('mime-types');
    elmStartLoading(container);
    jQuery.post('index.php?controller=file&action=getFolderFilesArea&folderId=' + folderId + '&uploaderName=' + uploaderName + '&containerId=' + containerId + '&mimeTypes=' + mimeTypes + '&ajax=1', function (data) {
        folderBtn.addClass('open');
        container.replaceWith(data.mainContent);
        if(data.jqueryAction) {
            eval(data.jqueryAction);
        }
        newContentLoaded();
    });
});

function getFileThumbnails(){
    $('.get_file_thumbnail').each(function(){
        let fileId = $(this).data('file-id');
        let placeholder = $('<div class="file_wrap"><div class="file_thumb" ><span class="corner"></span><span class="loading_bar"></span></div></div>');
        elmStartLoading(placeholder.find('.loading_bar'));
        $(this).replaceWith(placeholder);
        
        let url = 'index.php?controller=file&action=getFileThumbnail&fileId=' + fileId + '&ajax=1';
        jQuery.post(url, function (data) {
            if(data.content != undefined){
                let fileThumb = $(data.content);
                placeholder.replaceWith(fileThumb);
                fileThumb.trigger('fileThumbnailLoaded');
            } else {
                console.log(data);
            }
        });
    });
}

function getAvatarThumbnails(){
    $('.get_avatar_thumbnail').each(function(){
        let userId = $(this).data('user-id');
        let socketUserId = $(this).data('socket-user-id');
        let placeholder = $('<div class="avatar_wrap"><span class="loading_bar"></span></div>');
        elmStartLoading(placeholder.find('.loading_bar'));
        $(this).replaceWith(placeholder);
        
        let url = 'index.php?controller=file&action=getAvatarThumbnail&ajax=1';
        if(userId !== undefined && userId !== null && userId !== ''){
            url += '&userId=' + userId;
        }
        if(socketUserId !== undefined && socketUserId !== null && socketUserId !== ''){
            url += '&socketUserId=' + socketUserId;
        }
        if($(this).data('width') != undefined){
            url += '&width=' + $(this).data('width');
        }
        if($(this).data('height') != undefined){
            url += '&height=' + $(this).data('height');
        }
        jQuery.post(url, function (data) {
            if(data.content != undefined){
                let avatarThumb = $(data.content);
                placeholder.replaceWith(avatarThumb);
                avatarThumb.trigger('avatarThumbnailLoaded');
            } else {
                console.log(data);
            }
        });
    });
}

$(document).on('bindActionsToNewContent',function(){
    getFileThumbnails();
    getAvatarThumbnails();
});
