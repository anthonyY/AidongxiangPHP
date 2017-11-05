var lock = false;
function statusSwitch(id) {
	if (lock) {
		return false;
	}
	lock = true;
	var dom = $("#statusSwitch" + id);
	var status = dom.attr("status");
	var set_status = status == 1 ? 2 : 1;
	if (typeof(statusSwitchUrl) != "undefined" && statusSwitchUrl) {
		$.post(statusSwitchUrl, {id: id, status: set_status}, function (data) {
		    if(data.code != 200){
		    	alert(data.message);return;
			}
			if (status == 1) {
				dom.attr("status", set_status);
				dom.val("启用");
				dom.addClass("btn-success");
				dom.removeClass("btn-warning");
				$("#statusDesc" + id).text("禁用");
			}
			else {
				dom.attr("status", set_status);
				dom.val("禁用");
				dom.removeClass("btn-success");
				dom.addClass("btn-warning");
				$("#statusDesc" + id).text("启用");
			}
			lock = false;
		}, 'json');
	}
}