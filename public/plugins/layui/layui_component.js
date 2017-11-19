/**
 * Created by Administrator on 2017/7/20 0020.
 */
layui.use(['layer', 'form', 'layedit'], function(){
    var layer = layui.layer
        , form = layui.form
    ;
});

//弹出层
function showMessage(msg, type){
    layer.msg(msg, {
        icon: type,
        time: 2000, //2秒关闭（如果不配置，默认是3秒）
        offset:'50px'
    }, function(){
        //do something
    });
}

//加载层
function showLoad(type=2){
    return layer.load(type, {time: 10*1000, offset:'120px'});
}

//加载层永久
//加载层
function showLoadNoTime(type=2){
    return layer.load(type, {time: 300*1000,offset:'200px'});
}

//确认层
function confirm_handle(msg ,handle='', url='', obj={}){
    layer.confirm(msg, {icon: 3, title:'提示', offset:'100px'}, function(index){
        layer.close(index);
        if(url){
            window.location.href = url;
        }
        else if (obj){
            handle(obj);
        }
        else{
            handle();
        }
    });
}

