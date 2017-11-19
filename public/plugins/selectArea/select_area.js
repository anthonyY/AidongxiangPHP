/**
 * Created by Administrator on 2017/8/3 0003.
 * By hjj
 */
$(function () {
    //默认绑定省
    ProviceBind();
    //绑定事件
    $("#province").change( function () {
        var proId = $('#province').val();
        if(proId==820000){
            AreaBind();
        }else{
            CityBind();
        }
    });
});
function ProviceBind() {
    //清空下拉数据
    $("#province").html("");
    var str = "<option>全部省份</option>";
    $.ajax({
        type: "POST",
        url: "<?php echo $this->url('platform-store',['action'=>'getAreaInfo'])?>",
        data: { "parentId": 1,"deep":1,"type":1},
        dataType: "JSON",
        async: false,
        success: function (data) {
            //console.log(typeof(data));return false;
            //从服务器获取数据进行绑定
            $.each(data, function (i, item) {
                str += "<option value=" + item.id + ">" + item.name + "</option>";
            })
            //将数据添加到省份这个下拉框里面
            $('#province').append(str);
        },
        error: function (res) {
            console.log(res);
        }
    });
}
function CityBind() {
    var provice = $("#province").val();
    //判断省份这个下拉框选中的值是否为空
    if (provice == "") {
        return;
    }
    $("#selectCity").html("");
    var str = '<select class="form-control" onchange="AreaBind()" name="city" id="city"><option>全部城市</option>';
    $.ajax({
        type: "POST",
        url: "<?php echo $this->url('platform-store',['action'=>'getAreaInfo'])?>",
        data: { "parentId": provice,"deep":2,"type":1 },
        dataType: "JSON",
        async: false,
        success: function (data) {
            //从服务器获取数据进行绑定
            if(data.total!=0){
                $.each(data, function (i, item) {
                    str += "<option value=" + item.id + ">" + item.name + "</option>";
                })
                str += '</select>';
                //将数据添加到省份这个下拉框里面
                $("#selectCity").append(str);
            }else{
                $('#selectArea').html('');
            }
        },
        error: function (res) {
            alert('error');
            console.log(res);
        }
    });
}
function AreaBind() {
    var province = $('#province').val();
    var city = $("#city").val();
    if(province==820000){
        $("#selectCity").html("");
        city = province;
    }
    //判断市这个下拉框选中的值是否为空
    if (city == "") {
        return;
    }
    $("#selectArea").html("");
    var str = '<select class="form-control" name="community" id="community"><option>全部社区</option>';
    //将市的ID拿到数据库进行查询，查询出他的下级进行绑定
    $.ajax({
        type: "POST",
        url: "<?php echo $this->url('platform-store',['action'=>'getAreaInfo'])?>",
        data: { "parentId": city, "deep": 4,"type":1 },
        dataType: "JSON",
        async: false,
        success: function (data) {
            //从服务器获取数据进行绑定
            if(data.total!=0){
                $.each(data, function (i, item) {
                    str += "<option value=" + item.id + ">" + item.name + "</option>";
                })
                str += '</select>';
                //将数据添加到省份这个下拉框里面
                $("#selectArea").append(str);
            }
        },
        error: function () { alert("Error"); }
    });
}
