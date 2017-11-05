<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>WebService接口PHP_Demo</title>
<script type="text/javascript" src="./js/jquery-1.4.2.min.js" /></script>
<style type="text/css">
input,label{cursor:pointer;}
</style>

<script language="javascript">
var gmethod = 0;
var gurl = 'DealPage.php';
var mot = 0;
var rptt = 0;
var nType = 0;
var submitt = 0;
$(document).ready(function(){
	$("input[name='type']").click(function(){
		gmethod = this.value;
	});
	
	$("#clearSnddata").click(function(){	
		$('#f1ret').val('');
	});
	
	$("#clearSefSnddata").click(function(){	
		$('#f6ret').val('');
	});
	
	$("#clearModata").click(function(){	
		$('#f2ret').val('');
	});	
	
	$("#clearMoAndRptdata").click(function(){	
		$('#f5ret').val('');
	});
	
	$("#clearRptdata").click(function(){	
		$('#f3ret').val('');
	});
	
	$("#clearMoneydata").click(function(){	
		$('#f4ret').val('');
	});
	
	$("#submit").click(function(){
		var obj = this;
		obj.disabled = true;		
		$.ajax({
		url:gurl,
		data:{type:$(obj).attr('ntype'),port:$('#extendnum').val() , self:nType, flownum:$('#flownum').val(), method:gmethod, phones:$.trim($('#phones').val()), msg:$('#msg').val(), r:Math.random()},
		error:function(){AddText('f1ret', '页面无法访问');obj.disabled = false;},
		success:function(data){ AddText('f1ret', data); obj.disabled = false; }
		});
	});
	
	$("#submitSef").click(function(){
		var obj = this;
		obj.disabled = true;		
		$.ajax({
		url:gurl,
		data:{type:$(obj).attr('ntype'), self:nType, method:gmethod, sefmsg:$('#sefmsg').val(), r:Math.random()},
		error:function(){AddText('f6ret', '页面无法访问');obj.disabled = false;},
		success:function(data){ AddText('f6ret', data); obj.disabled = false; }
		});
	});
	
	
	$('#autosubmit').click(function(){
		if ('自动发送' == this.value)
		{
			this.value = '关闭发送';
			$('#submit').trigger('click');
			submitt = setInterval(function(){
				$('#msg').val('自动发送随机数2');// + Math.random().toString());
				$('#submit').trigger('click');
				}, 10);
		}
		else
		{
			this.value = '自动发送';
			clearTimeout(submitt);
		}
	});
	
	
	$("input[name='get']").click(function(){
		this.disabled = true;
		OnSubmit(this);
	});
	
	
	$('#msg').bind('keyup',function(){
		var len = strlen($(this).val());
		$('#msglens').text(len);
	});
	
	$('#phones').bind('keyup',function(){		
		var pcount = 0;
		var phones = $.trim($(this).val());
		if (phones != '')
		{
			var parr = phones.split(',');
			pcount = parr.length;
			if (pcount == 101)
				alert('手机号码超出100个');
		}
		$('#pcount').text(pcount);
	});
	
	$('#selfsend').click(function(){
		if (this.checked)
			nType = 4;
		else
			nType = 0;
	});	
}); 
 
function OnSubmit(obj)
{
	var o = $(obj).parent();
	var txtobj = $(o).children('textarea');
	$.ajax({
		url:gurl,
		data:{type:$(obj).attr('ntype'), method:gmethod, r:Math.random()},
		error:function(){AddText($(txtobj).attr('id'), '页面无法访问');obj.disabled = false;},
		success:function(data){			
			AddText($(txtobj).attr('id'), data);
			obj.disabled = false;
			}
		});
};

function isChinese(str)
{
   var lst = /[u00-uFF]/;       
   return !lst.test(str);      
};

function strlen(str) 
{
	var strlength = 0;
	for (var i=0; i < str.length; ++i)
	{
		if (isChinese(str.charAt(i)) == true)
			strlength = strlength + 1;
		else
			strlength = strlength + 1;
	}
	return strlength;
};

function AddText(id, txt)
{
	var str = $('#' + id).val();
	if (str.length == 1000)
		str = '';	
	txt = txt.replace(/;/g, '\r\n');
	str += txt + '\r\n';
	$('#' + id).val(str);
	document.getElementById(id).scrollTop = document.getElementById(id).scrollHeight;
	if (strlen(txt) > 20)
	{
		var txtarr = txt.split('\r\n');
		var spanobj = $('#' + id).parent().children('span');
		$(spanobj).text($(spanobj).text() * 1 + txtarr.length);
	}
};

</script>
</head>
<body>
请求方式:<input type="radio" id="type1" name="type" value="0" checked="checked"/><label for="type1">SOAP</label>
<input type="radio" id="type2" name="type" value="1" /><label for="type2">POST</label>
<input type="radio" id="type3" name="type" value="2" /><label for="type3">GET</label><hr /><br/>

<form name="form1" id="form1" method="post" action="" >
<div><font style=font-size:18px color="#FF0000"><strong>短信息发送接口（相同内容群发，可自定义流水号）:</strong></font><br/>
手机号码(多个号码以<font color="#FF0000">英文半角逗号</font>分隔,<font color="#FF0000">请输入有效号码!!!</font>)&nbsp;
<br/>当前号码个数(<label id="pcount">1</label>):<br/>
<textarea id="phones" name="phones" cols="60" rows="5">13143515985</textarea><br/>
信息内容&nbsp;(<label id="msglens">9</label>):<br />
<textarea id="msg" name="msg" cols="60" rows="5">感谢您配合我们的测试</textarea>&lt;短连接&gt;测试信息<br/>
扩展子号:<input type="text" id="extendnum" name="extendnum" value="*"/>
&nbsp;流水号:<input type="text" id="flownum" name="flownum" value="0"/><br />
<input type="button" id="submit" name="submit" ntype="0" value="发送信息">
<input type="button" id="clearSnddata" name="clearSnddata" ntype="99" value="清空回应数据">
<br />
返回信息:<br/><textarea id="f1ret" name="f1ret" cols="85" rows="5" readonly="true"></textarea>
</div></form><br/><br/>

<font style=font-size:18px color="#FF0000"><strong>短信息发送接口（不同内容群发，可自定义不同流水号，自定义不同扩展子号）:</strong></font><br/>待发信息内容(<font color="#FF0000">请按照文档格式组包!!!</font>)<br/>
<div><form name="form6" id="form6" method="post" action="" >
<textarea id="sefmsg" name="sefmsg" cols="60" rows="5">457894132578945|41|13800138000|xOO6wyy7ttOtyrnTwyE=</textarea><br/>
<input type="button" id="submitSef" name="submitSef" ntype="5" value="发送信息">
<input type="button" id="clearSefSnddata" name="clearSefSnddata" ntype="99" value="清空回应数据"><br />
返回信息:<br/><textarea id="f6ret" name="f6ret" cols="85" rows="5" readonly="true"></textarea>
</div></form><br/><br/>

<font style=font-size:18px color="#FF0000"><strong>获取上行:</strong></font><br/>
<form name="form2" id="form2" method="post" action="" >
<div><input type="button" id="getmo" name="get" ntype="1" value="获取上行" >
<input type="button" id="clearModata" name="clearModata" ntype="100" value="清空回应数据">
返回信息:&nbsp;信上行标志0,日期,时间,上行源号码,上行目标通道号,*,*,上行信息内容
<br/><textarea id="f2ret" name="f2ret" cols="85" rows="5" readonly="true"></textarea></div></form><br/><br/>

<font style=font-size:18px color="#FF0000"><strong>获取状态报告:</strong></font><br/>
<form name="form2" id="form2" method="post" action="" >
<div><input type="button" id="getrpt" name="get" ntype="2" value="获取状态报告">
<input type="button" id="clearRptdata" name="clearRptdata" ntype="103" value="清空回应数据">
返回信息:&nbsp;状态报告标志1,日期,时间,信息编号,通道号,手机号,MsgId,*,状态值,错误原因
<br/><textarea id="f3ret" name="f3ret" cols="85" rows="5" readonly="true"></textarea>
</div></form><br/><br/>

<font style=font-size:18px color="#FF0000"><strong>查询余额:</strong></font><br/>
<form name="form4" id="form3" method="post" action="" >
<div><input type="button" id="getmoney" name="get" ntype="3" value="查询余额">
<input type="button" id="clearMoneydata" name="clearMoneydata" ntype="105" value="清空回应数据">
<br />
返回信息:<br/><textarea id="f4ret" name="f4ret" cols="85" rows="5" readonly="true"></textarea>
</div></form><br/><br/>

</body>
</html>
