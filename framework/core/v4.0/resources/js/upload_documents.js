$('.upload_doc.open').slideDown();

function changeUploader(newUploader){
    cur_upload_container = newUploader;
    gi_pluploader.setOption({
        "browse_button" : cur_upload_container.find(".browse_computer")[0],
        "drop_element" : cur_upload_container.find(".dropzone")[0],
        "filters" : {
            mime_types: window["mime_types_" + cur_upload_container.data("mime-types")]
        }
    });
}

$(document).on('click', '.upload_docs_menu li', function(){
    if(!$(this).is('.open')){
        if(gi_pluploader.state == plupload.STOPPED){
            var docTypeRef = $(this).data('doc-type-ref');
            $('.upload_docs_menu li').removeClass('open');
            $(this).addClass('open');
            $('.upload_doc').slideUp().removeClass('open');
            var openDoc = $('.upload_doc[data-doc-type-ref="' + docTypeRef + '"]');
            openDoc.slideDown().addClass('open');
            changeUploader(openDoc.find('.uploader_container'));
        } else {
            alert('Please wait for your upload to finish.');
        }
    }
});
