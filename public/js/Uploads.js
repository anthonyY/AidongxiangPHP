//flash图片上传
$(document).ready(function(){
	$("#index_img").uploadify({
		'swf': swf,
		'uploader': uploader,
		'buttonText' : '请选择图片上传',
		'auto':true,
		'multi': true,//是否多文件上传
		'fileTypeExts'	: '*.gif; *.jpg; *.png; *.flv; *.jpeg;',	//允许上传的文件后缀
		'fileSizeLimit'	: '2MB',					//上传文件的大小限制，单位为B, KB, MB, 或 GB
		'onUploadSuccess' : function(file, data, response) {
		 getResult_index(data,0); //获得img的ID
		}
	});
	$("#description_image_ids").uploadify({
		'swf': swf,
		'uploader': uploader,
		'buttonText' : '请选择图片上传',
		'auto':true,
		'multi': true,//是否多文件上传
		'fileTypeExts'	: '*.gif; *.jpg; *.png; *.flv; *.jpeg;',	//允许上传的文件后缀
		'fileSizeLimit'	: '2MB',					//上传文件的大小限制，单位为B, KB, MB, 或 GB
		'onUploadSuccess' : function(file, data, response) {
		 getResult_index(data,1); //获得img的ID
		}
	});
});
//添加时候的图片删除
	function getResult_index(img_url,a){
		var img_url = img_url.split(',');
		var type = a==0 ? "img_div" : "description_img_div"; 
		var img_id = a==0 ? "image_ids[]" : "description_image_ids[]";
		$("#"+type).append('<div id="'+type+img_url[1]+'" class="outdiv"></div>');
		$("#"+type+img_url[1]).append("<img src="+img_url[0]+" onmouseover='del1(this)'  width = '280' height = '210'>");
		
		$("#"+type+img_url[1]).append("<input value="+img_url[1]+" name='"+img_id+"' type='hidden'>");
		$("#"+type+img_url[1]).append("<div id='near_img_"+type+img_url[1]+"' onmouseout='del3(this)' onclick='del2(\""+img_url[1]+"\",\""+type+"\")' class='innerdiv'>删除</div>");
		}
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