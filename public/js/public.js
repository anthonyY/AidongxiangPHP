function show_confirm(url) {
	var r = confirm("确定删除？");
	if (r == true) {
		window.location.href = url;
	} else {
	}
}

function show_confirm_message(url,message) {
	var r = confirm(message);
	if (r == true) {
		window.location.href = url;
	} else {
	}
}


function show_confirm_stop(url) {
	var r = confirm("确定停用？");
	if (r == true) {
		window.location.href = url;
	} else {
	}
}

function show_confirm_start(url) {
	var r = confirm("确定启用？");
	if (r == true) {
		window.location.href = url;
	} else {
	}
}

function show_confirm_pass(url) {
	var r = confirm("您确定认证通过此用户资料吗？");
	if (r == true) {
		window.location.href = url;
	} else {
	}
}

function show_confirm_refused(url) {
	var r = confirm("您确定拒绝通过此用户认证资料吗？");
	if (r == true) {
		window.location.href = url;
	} else {
	}
}

function show_confirm_cancel(url) {
	var r = confirm("您确定要撤消吗？");
	if (r == true) {
		window.location.href = url;
	} else {
	}
}

function show_confirm_payment() {
	var r = confirm("您确定要支付吗？");
	if (r == true) {
		return true;
	} else {
		
	}
}

function show_confirm_inquiry(url) {
	var r = confirm("您确定要选择该报价吗？");
	if (r == true) {
		return true;
	} else {
		
	}
}

function show_confirm_inquiry_2(url) {
	var r = confirm("当前车主未安排车辆，您确定要选择该报价吗？");
	if (r == true) {
		return true;
	} else {
		
	}
}
function show_confirm_right(url) {
	var r = confirm("您确定要对此订单维权吗？");
	if (r == true) {
		window.location.href = url;
	} else {
	}
}

function show_confirm_deleteInquiry(url) {
	var r = confirm("您确定要撤销该报价吗？");
	if (r == true) {
		window.location.href = url;
	} else {
		
	}
}
function show_confirm_rights(url) {
	var r = confirm("您确定要结束维权并退款吗？");
	if (r == true) {
		window.location.href = url;
	} else {
		
	}
}


function show_confirm_support(url) {
	var r = confirm("您确定支持此投诉吗？");
	if (r == true) {
		window.location.href = url;
	} else {
		
	}
}

function show_confirm_rejected(url) {
	var r = confirm("您确定支持此投诉吗？");
	if (r == true) {
		window.location.href = url;
	} else {
		
	}
}

function show_confirm_achieve(url) {
	var r = confirm("您确定帮助此用户实现愿望吗？");
	if (r == true) {
		window.location.href = url;
	} else {
		
	}
}

function show_confirm_pass_financial(url){
	var r = confirm("您确定通过此财务信息吗？");
	if (r == true) {
		window.location.href = url;
	} else {
		
	}
}

function show_confirm_rejected_financial(url){
	var r = confirm("您确定拒绝通过此财务信息吗？");
	if (r == true) {
		window.location.href = url;
	} else {
		
	}
}

function show_confirm_reset(url){
	var r = confirm("您确定重置该用户状态吗？");
	if (r == true) {
		window.location.href = url;
	} else {
		
	}
}

function show_confirm_update_status(url){
	var r = confirm("您确定要完成此任务吗？");
	if (r == true) {
		window.location.href = url;
	} else {
		
	}
}


function show_confirm_hide(url){
	var r = confirm("您确定要隐藏该任务吗？");
	if (r == true) {
		window.location.href = url;
	} else {
		
	}
}

function show_confirm_enable(url){
	var r = confirm("您确定要启用该任务吗？");
	if (r == true) {
		window.location.href = url;
	} else {
		
	}
}
//验证手机号码  mobile 手机号码  obj 提示信息对象

function checkMobile(mobile, obj) {
	var regu = /^13[0-9]{1}[0-9]{8}$|15[0-9]{9}$|18[0-9]{9}$/;
	if (mobile) {
		if (!regu.test(mobile)) {
			obj.text('请输入正确的手机号码！');
			return false;
		} else {
			return true;
		}
	} else {
		return false;
	}
}

//验证营业执照号码

function checkCharterNumber(charternumber, obj) {

	if (charternumber.length > 12) {
		obj.text('*营业执照号码不能大于12位！');
	} else {
		obj.text('');
	}

}

/*function show_confirm_approve()
 {
 var r=confirm("您确定要提交此车辆资料认证吗？认证期间该车辆不可用。");
 if (r==true)
 { 
 window.location.href = url;
 }
 else
 {}
 }*/

var gHuitouche = {
	"id" : 0
};

//文件上传方法
function ajaxFileUpload(id) {
	gHuitouche.id = id; // 重要

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
		url : url,
		secureuri : false,
		fileElementId : 'img' + id,
		dataType : 'json',
		success : function(data, status) {

			if (typeof (data.error) != 'undefined') {
				if (data.error != '') {
					alert(data.error);
				} else {
					$("#img_" + id).attr('src', data.path);
					$("#img_id_" + id).val(data.imgid);
					if (ajaxUpdate) {
						ajaxUpdate();
					}
				}
			}
		},
		error : function(data, status, e) {
			alert(e);
		}
	})

	return false;
}




//身份证号合法性验证 
//支持15位和18位身份证号
//支持地址编码、出生日期、校验位验证
function IdentityCodeValid(code, obj) {
	var city = {
		11 : "北京",
		12 : "天津",
		13 : "河北",
		14 : "山西",
		15 : "内蒙古",
		21 : "辽宁",
		22 : "吉林",
		23 : "黑龙江 ",
		31 : "上海",
		32 : "江苏",
		33 : "浙江",
		34 : "安徽",
		35 : "福建",
		36 : "江西",
		37 : "山东",
		41 : "河南",
		42 : "湖北 ",
		43 : "湖南",
		44 : "广东",
		45 : "广西",
		46 : "海南",
		50 : "重庆",
		51 : "四川",
		52 : "贵州",
		53 : "云南",
		54 : "西藏 ",
		61 : "陕西",
		62 : "甘肃",
		63 : "青海",
		64 : "宁夏",
		65 : "新疆",
		71 : "台湾",
		81 : "香港",
		82 : "澳门",
		91 : "国外 "
	};
	var tip = "";
	var pass = true;

	if (!code
			|| !/^\d{6}(18|19|20)?\d{2}(0[1-9]|1[12])(0[1-9]|[12]\d|3[01])\d{3}(\d|X)$/i
					.test(code)) {
		tip = "身份证号格式错误";
		pass = false;
	}

	else if (!city[code.substr(0, 2)]) {
		tip = "地址编码错误";
		pass = false;
	} else {
		//18位身份证需要验证最后一位校验位
		if (code.length == 18) {
			code = code.split('');
			//∑(ai×Wi)(mod 11)
			//加权因子
			var factor = [ 7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2 ];
			//校验位
			var parity = [ 1, 0, 'X', 9, 8, 7, 6, 5, 4, 3, 2 ];
			var sum = 0;
			var ai = 0;
			var wi = 0;
			for ( var i = 0; i < 17; i++) {
				ai = code[i];
				wi = factor[i];
				sum += ai * wi;
			}
			var last = parity[sum % 11];
			if (parity[sum % 11] != code[17]) {
				tip = "校验位错误";
				pass = false;
			}
		}
	}
	if (!pass)
		obj.text(tip);
	return pass;
}

if(typeof(checkStrLenArrayByName) !== "function") {
	function checkStrLenArrayByName(Names) {
		var result = true;
		for(one in Names) {
			temp = checkStrLen( $("input[name='"+Names[one][0]+"']") , Names[one][1] , Names[one][2] , Names[one][3] );
			if(temp == false) {
				result = false;
			}
		}
		return result;
	}
}

if(typeof(checkStrLenThis) !== "function") {
	function checkStrLenThis(dom , max , min , button) {
		return checkStrLen($(dom),max,min,button);
	}
}

if(typeof(checkStrLen) !== "function") {
	function checkStrLen(dom , max , min , button)
	{
		var jq_dom = dom;
		var val_len = jq_dom.val().length;
		var submit_dom = button ? ( $("#"+button).length ? $("#"+button) : $("."+button) ) : $("input[type='submit']");
		var message = jq_dom.parent().find(".message");
		
		/* 如果最大限制与最小限制相同，提示信息转变 */
		if(max == min && max != null) {
			if(val_len != max) {
				if(submit_dom.length) {
					submit_dom.attr("disabled","disabled");
				}
				content = "字数长度必须是"+max+"个";
				if(message.length) {
					message.html(content);
				}else{
					jq_dom.parent().append("<span class='message'>"+content+"</span>");
				}
				return false;
			}
		}else{
			/* 有限制最大长度，而且超出最大长度 */
			if(max && val_len > max){
				if(submit_dom.length) {
					submit_dom.attr("disabled","disabled");
				}
				content = "字数不能超出"+max+"个";
				if(message.length) {
					message.html(content);
				}else{
					jq_dom.parent().append("<span class='message'>"+content+"</span>");
				}
				return false;
			}
			/* 有限制最小长度，而且不足最小长度 */
			if(min && val_len < min){
				if(submit_dom.length) {
					submit_dom.attr("disabled","disabled");
				}
				content = "字数不能小于"+min+"个";
				if(message.length) {
					message.html(content);
				}else{
					jq_dom.parent().append("<span class='message'>"+content+"</span>");
				}
				return false;
			}
		}
		/* 没问题，清除限制与清楚提示 */
		if(submit_dom.length) {
			submit_dom.removeAttr("disabled");
		}
		if(message.length) {
			message.html("");
		}
		return true;
	}
}
