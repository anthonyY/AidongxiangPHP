$(function () {
    $('#res_img').css('margin-top', '20px');
    $("#one_img_upload").uploadifive({
        'uploadScript': uploader,
        'buttonText' : '请选择图片上传',
        'auto':true,
        'multi': false,//是否多文件上传
        'width': 120,
        'fileTypeExts'	: '*.gif; *.jpg; *.png; *.flv',	//允许上传的文件后缀
        'fileSizeLimit'	: '8MB',					//上传文件的大小限制，单位为B, KB, MB, 或 GB
        'onUploadComplete' : function(file, data) {
            setTimeout(function () {
                $('#uploadifive-one_img_upload-queue').hide();
            }, 2000);
            data = JSON.parse(data);
            getResult_one_img(data); //获得img的ID
        }
    });
    //添加时候的图片删除
    function getResult_one_img(img_url){
        var type = "res_img";
        var img_id = "image_id";
        $('#'+type).html('');
        $("#"+type).append('<div id="img_url" class="outdiv"></div>');
        $("#img_url").append("<img src="+img_url.path+" onmouseover='del1(this)'  width = '280' height = '210'>");
        $("#img_url").append("<input value="+img_url.image_id+" name='"+img_id+"' type='hidden'>");
        $("#img_url").append("<div id='near_img' onmouseout='del3(this)' onclick='$(\"#"+type+"\").html(\"\")' class='innerdiv'>删除</div>");
    }
});


function del1(me){
    var i= $(me).parent().attr("id");
    $("#"+i+" .innerdiv").css("top","-212px");
}
function del2(data,a){
    if(!a){a='img_div';}
    $("#divTxt input[value='"+data+"']").remove();
    $("#"+a+data).remove();
}
function del3(me){
    $(me).css("top","210px");
}