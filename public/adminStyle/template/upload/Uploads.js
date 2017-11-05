//flash图片上传
$(document).ready(function(){
	$("#index_img").uploadify({
		'swf': swf,
		'uploader': uploader,
		'buttonText' : '请选择图片上传',
		'auto':true,
		'multi': true,//是否多文件上传
		'fileTypeExts'	: '*.gif; *.jpg; *.png; *.flv',	//允许上传的文件后缀
		'fileSizeLimit'	: '2MB',					//上传文件的大小限制，单位为B, KB, MB, 或 GB
		'onUploadSuccess' : function(file, data, response) {
		 getResult_index(data); //获得img的ID
		}
	});
});
//添加时候的图片删除
	function getResult_index(img_url){
		// var img_url = img_url.split(',');
		var img_url = jQuery.parseJSON(img_url);
		$("#image-c").attr("src",img_url.path);
		$("#img_div").append('<div id="img_div'+img_url.imgid+'" class="outdiv"></div>');
		$("#img_div"+img_url.imgid).append("<img src="+img_url.path+" onmouseover='del1(this)'  width = '280' height = '210'>");
		$("#img_div"+img_url.imgid).append("<input value="+img_url.imgid+" name='image_ids[]' type='hidden'>");
		$("#img_div"+img_url.imgid).append("<div id='near_img"+img_url.imgid+"' onmouseout='del3(this)' onclick='del2(\""+img_url.imgid+"\")' class='innerdiv'>删除</div>");
		}
		function del1(me){
			var i= $(me).parent().attr("id");
			$("#"+i+" .innerdiv").css("top","-111px");
		}
		function del2(data){
			$("#divTxt input[value='"+data+"']").remove();
			$("#img_div"+data).remove();
		}
		function del3(me){
			$(me).css("top","111px");
		}