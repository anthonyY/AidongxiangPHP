$(function () {

    var start ={
        dateCell:"#start_time",
        format:"YYYY-MM-DD hh:mm:ss",
        //isinitVal:true, //默认值
        festival:true,
        ishmsVal:false,
        isTime:true, //isClear:false,
        maxDate:"2099-12-31 23:59:59",
        minDate:"1970-01-01 00:00:00",
        choosefun: function(datas){
            end.minDate = datas;
        }
    };
    var end ={
        dateCell:"#end_time",
        format:"YYYY-MM-DD hh:mm:ss",
        festival:true,
        ishmsVal:false,
        isTime:true, //isClear:false,
        maxDate:"2099-12-31 23:59:59",
        minDate:"1970-01-01 00:00:00",
        choosefun: function(datas){
            start.maxDate = datas;
        }
    };
    jeDate(start);
    jeDate(end);
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


