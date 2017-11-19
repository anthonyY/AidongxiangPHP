$(function () {
    //日期无时间格式
    var start_date = {
        dateCell:"#start_date",
        format:"YYYY-MM-DD",
        festival:true,
        ishmsVal:false,
        isTime:false,
        maxDate:"2099-12-31",
        minDate:"1970-01-01",
        choosefun:function (datas) {
            end_date.minDate = datas;
        }
    };
    var end_date = {
        dateCell:"#end_date",
        format:"YYYY-MM-DD",
        festival:true,
        ishmsVal:false,
        isTime:false, //isClear:false,
        maxDate:"2099-12-31",
        minDate:"1970-01-01",
        choosefun: function(datas){
            start_date.maxDate = datas;
        }
    };
    jeDate(start_date);
    jeDate(end_date);
});

function dateValidate() {
    var start_time = $('#start_time').val();
    var end_time = $('#end_time').val();
    if (!start_time || !end_time){
        showMessage('开始时间或结束时间不能为空', 0);
        return false;
    }
    start_time = new Date(start_time).getTime();
    end_time = new Date(end_time).getTime();
    if (start_time > end_time){
        showMessage('开始时间不能大于结束时间', 0);
        return false;
    }
    return true;
}


