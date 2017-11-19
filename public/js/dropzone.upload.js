$(function () {
    Dropzone.autoDiscover = false;
    var myDropzone = new Dropzone("#myDropzone", {
        previewTemplate: '<div class="dz-preview dz-file-preview"><div class="dz-image"><img data-dz-thumbnail=""></div><div class="dz-details"></div><div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress=""></span></div><div class="dz-error-message"><span data-dz-errormessage=""></span></div><div class="dz-success-mark"><span class="fa-stack fa-lg bigger-150"><i class="fa fa-circle fa-stack-2x white"></i><i class="fa fa-check fa-stack-1x fa-inverse green"></i></span></div><div class="dz-error-mark"><svg width="54px" height="54px" viewBox="0 0 54 54" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns"><title>Error</title><defs></defs><g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" sketch:type="MSPage"><g id="Check-+-Oval-2" sketch:type="MSLayerGroup" stroke="#747474" stroke-opacity="0.198794158" fill="#FFFFFF" fill-opacity="0.816519475"><path d="M32.6568542,29 L38.3106978,23.3461564 C39.8771021,21.7797521 39.8758057,19.2483887 38.3137085,17.6862915 C36.7547899,16.1273729 34.2176035,16.1255422 32.6538436,17.6893022 L27,23.3431458 L21.3461564,17.6893022 C19.7823965,16.1255422 17.2452101,16.1273729 15.6862915,17.6862915 C14.1241943,19.2483887 14.1228979,21.7797521 15.6893022,23.3461564 L21.3431458,29 L15.6893022,34.6538436 C14.1228979,36.2202479 14.1241943,38.7516113 15.6862915,40.3137085 C17.2452101,41.8726271 19.7823965,41.8744578 21.3461564,40.3106978 L27,34.6568542 L32.6538436,40.3106978 C34.2176035,41.8744578 36.7547899,41.8726271 38.3137085,40.3137085 C39.8758057,38.7516113 39.8771021,36.2202479 38.3106978,34.6538436 L32.6568542,29 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z" id="Oval-2" sketch:type="MSShapeGroup"></path></g></g></svg><!--<span class="fa-stack fa-lg bigger-150"><i class="fa fa-circle fa-stack-2x white"></i><i class="fa fa-remove fa-stack-1x fa-inverse red"></i></span>--></div><br><!--<a class="dz-remove" href="javascript:undefined;" data-dz-remove=""><i class="ace-icon fa fa-trash-o bigger-120 red">删除</i></a>--></div>',
        url: uploader,
        method: 'post',
        paramName: 'Filedata',
        thumbnailHeight: 120,
        thumbnailWidth: 120,
        autoProcessQueue: true,
        addRemoveLinks:true,
        dictRemoveFile : "删除",
        maxFilesize: 8,
        dictFileTooBig:'请上传小于8MB的图片',
        dictFallbackMessage:'您的浏览器不支持此上传插件',
        acceptedFiles: '.jpeg,.jpg,.png,.gif',
        dictInvalidFileType:'不支持此图片类型的上传',
        dictCancelUpload:'取消上传',
        dictCancelUploadConfirmation:'确定取消上传吗？',
        clickable: true,
        dictDefaultMessage: '<span class="bigger-150 bolder"><i class="ace-icon fa fa-caret-right red"></i></span> 点击上传图片\
            <span class="smaller-80 grey">(或拖拽) </span> <br /> \
            <i class="upload-icon ace-icon fa fa-cloud-upload blue fa-3x"></i>'
        ,
        thumbnail: function (file, dataUrl) {
            if (file.previewElement) {
                $(file.previewElement).removeClass("dz-file-preview");
                var images = $(file.previewElement).find("[data-dz-thumbnail]").each(function () {
                    var thumbnailElement = this;
                    thumbnailElement.alt = file.name;
                    thumbnailElement.src = dataUrl;
                });
                setTimeout(function () {
                    $(file.previewElement).addClass("dz-image-preview");
                }, 1);
            }
        },
        sending: function(file, xhr, formData) {
            formData.append("filesize", file.size);
        },
        success: function (file, response, e) {
            var res = JSON.parse(response);
            if (!res.errormsg){
                $(file.previewTemplate).children('.dz-image').append('<input type="hidden" name="image_ids[]" value="'+res.image_id+'">');
            }
            else if (res.errormsg) {
                $(file.previewTemplate).children('.dz-error-mark').css('opacity', '1');
            }
        }
    });

    if(image_info){
        var images = JSON.parse(image_info);
        $.each(images, function (i) {
            var mockFile = { name: images[i].filename };
            myDropzone.emit("addedfile", mockFile);
            myDropzone.files.push(mockFile);
            myDropzone.emit("thumbnail", mockFile, '/uploadfiles/'+images[i].image_path+'?'+Math.random(10000, 99999));
            $(mockFile.previewTemplate).children('.dz-image').find('img').css('width', 120);
            $(mockFile.previewTemplate).children('.dz-image').find('img').css('height', 120);
            $(mockFile.previewTemplate).children('.dz-image').append('<input type="hidden" name="image_ids[]" value="'+images[i].id+'">');
            myDropzone.emit("complete", mockFile);
        });
    }
});

