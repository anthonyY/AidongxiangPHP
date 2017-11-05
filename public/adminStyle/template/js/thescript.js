/**
 * Created by Administrator on 2016/6/1.
 */
var myObject = {
    popups:function(mes){
        var m = $(".panel-body").width();
        var n = $(".panel-body").height();
        var i = $(".modal-content").width();
        var j = $(".modal-content").height();
        var left = (m/3)+(i/2);
        var top  = (n/1.5)+(j/2);
        $(".panel-body").css("position","relative");
        $(".modal-content").css("position","absolute");
        $(".modal-content").css("left",left+"px");
        $(".modal-content").css("top",top+"px");
        $(".modal-body").html(mes);
    },
    show:function(m){
        $(m).css("display","block");
    },
    hide:function(n){
        $(n).css("display","none");
    },
    ajax:function(url, data,callback){
        $.ajax({
            url:url,
            data:data,
            type:'post',
            cache:false,
            dataType:'json',
            success:callback,
            error : function() {
                alert('400:未知错误');
            }
        });
    },
    redirect:function(url){
        window.location.href=url;
    },
    setTime:function(url, time){
        setTimeout("myObject.redirect('"+url+"')", time);
    },
    redirection:function(time){
        setTimeout("myObject.reload()", time);
    },
    reload:function(){
        window.location.reload();
    },
    history:function(){
        window.location.href=history.go(-1);
    },
    getValue:function(inputName){
        return $.trim($(":input[name='"+inputName+"']").val());
    },
    popups1:function(big,small,inner,mes){
        var m = $(big).width();
        var n = $(big).height();
        var i = $(small).width();
        var j = $(small).height();
        var left = (m/3)+(i/2);
        var top  = (n/1.5)+(j/2);
        $(big).css("position","relative");
        $(small).css("position","absolute");
        $(small).css("left",left+"px");
        $(small).css("top",top+"px");
        $(inner).html(mes);
    },

}

/**
 * @type {{id: number}}
 */
var gHuitouche = {
    "id" : 0
};

/**
 * upload
 * @param id
 * @param type
 * @returns {boolean}
 */
function ajaxFileUpload(id,type) {
    gHuitouche.id = id;

    $("#loading" + id).ajaxStart(function() {
        if (id == gHuitouche.id) {
            $(this).next().hide();
            $(this).show();

        }
    }).ajaxComplete(function() {
        if (id == gHuitouche.id) {
            $(this).next().show();
            $(this).hide();
        }
    });

    $.ajaxFileUpload({
        url : type == 1 ? url_two : uploader,
        secureuri : false,
        fileElementId : 'img' + id,
        dataType : 'json',
        success : function(data, status) {
            console.log(status);
            if (status == 'success') {
                    $("#img_" + id ).attr('src', data.path);
                    $("#img_path_" + id).val(data.path1);
                    $("#img_id_" + id).val(data.imgid);
            }
            else
            {
                alert(status);
            }
        },
        error : function(data, status, e) {
            alert(e);
        }
    })

    return false;
}

/**
 * upload
 * @param id
 * @param type
 * @returns {boolean}
 */
function ajaxFileUpload2(dom) {
	var id = $(dom).attr('id');
    $.ajaxFileUpload({
        url : uploader,
        secureuri : false,
        fileElementId : id,
        dataType : 'json',
        success : function(data, status) {
            console.log(status);
            if (status == 'success') {
            	console.log($("#div" + id));
            	$("#div" + id).find(".img-path").val(data.path1);
            	$("#div" + id).find(".img-img").attr('src', data.path);
            }
            else
            {
                alert(status);
            }
        },
        error : function(data, status, e) {
            alert(e);
        }
    })

    return false;
}
