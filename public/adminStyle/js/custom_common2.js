var lock = false;
function statusSwitch(id) {
//	alert(1);return;
	if (lock) {
		return false;
	}
	lock = true;
	var dom = $("#statusSwitch" + id);
	var status = dom.attr("status");
	var set_status = status == 1 ? 2 : 1;
	if (typeof(statusSwitchUrl) != "undefined" && statusSwitchUrl) {
		$.post(statusSwitchUrl, {id: id, status: set_status}, function (data) {
		    if(data.status != 0){
		    	alert(data.msg);return;
			}
			if (status == 1) {
				dom.attr("status", set_status);
				dom.val("上架");
				dom.addClass("btn-success");
				dom.removeClass("btn-warning");
				$("#statusDesc" + id).text("已下架");
			}
			else {
				dom.attr("status", set_status);
				dom.val("下架");
				dom.removeClass("btn-success");
				dom.addClass("btn-warning");
				$("#statusDesc" + id).text("正常");
			}
			lock = false;
		}, 'json');
	}
}