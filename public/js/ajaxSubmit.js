
function ajaxSubmit (e) {
    e.preventDefault();
    var form = e.target;
    if (!validateData(form)){
        return;
    }
    var index = showLoad();
    $(form).find('button[type="submit"]').attr('type', 'button');
    $.ajax({
        url:form.action,
        type:'post',
        data:$(form).serializeArray(),
        dataType:'json',
        success: function (rs) {
            if(rs.status == 0) {
                layer.close(index);
                showMessage(rs.error_msg, 0);
                $(form).find('button[type="button"]').attr('type', 'submit');
                return;
            }
            if(rs.status == 1) {
                layer.close(index);
                showMessage(rs.msg, 1);
            }
            if(rs.redirect) {
                setTimeout(function () {
                    location.href = rs.redirect;
                }, 1000);
            }
        },
        error: function () {
            layer.close(index);
            $(form).find('button[type="button"]').attr('type', 'submit');
            showMessage('服务器请求错误', 2);
        }
    });
    /*$.post(form.action, $(form).serializeArray(), function(rs) {

        if(rs.status == 0) {
            showMessage(rs.error_msg, 2);
            return;
        }
        if(rs.status == 1) {
            showMessage(rs.msg, 1);
        }
        if(rs.redirect) {
            setTimeout(function () {
                location.href = rs.redirect;
            }, 1000);
        }
    }, 'json');*/
}

function ajaxForm(e){
    e.preventDefault();
    var index = showLoad();
    var form = e.target;
    $(form).find('button[type="submit"]').attr('type', 'button');
    $.ajax({
        url:form.action,
        type:'post',
        data:$(form).serializeArray(),
        dataType:'json',
        success: function (rs) {
            if(rs.s) {
                layer.close(index);
                showMessage(rs.d, 0);
                $(form).find('button[type="button"]').attr('type', 'submit');
                return;
            }else{
                layer.close(index);
                if (rs.d){
                    showMessage(rs.d, 1);
                }
            }

            if(rs.url) {
                setTimeout(function () {
                    location.href = rs.url;
                }, 700);
            }
        },
        error: function () {
            layer.close(index);
            $(form).find('button[type="button"]').attr('type', 'submit');
            showMessage('服务器请求错误', 2);
        }
    });
}

function ajaxFormPc(e){
    e.preventDefault();
    var index = showLoad();
    var form = e.target;
    $(form).find('#submit').attr('type', 'button');
    $.ajax({
        url:form.action,
        type:'post',
        data:$(form).serializeArray(),
        dataType:'json',
        success: function (rs) {
            if(rs.status == 0) {
                layer.close(index);
                showMessage(rs.error_msg, 0);
                $(form).find('#submit').attr('type', 'submit');
                return;
            }
            if(rs.status == 1) {
                layer.close(index);
                if (rs.msg){
                    showMessage(rs.msg, 1);
                }
            }
            if(rs.redirect) {
                setTimeout(function () {
                    location.href = rs.redirect;
                }, 700);
            }
        },
        error: function () {
            layer.close(index);
            $(form).find('button[type="button"]').attr('type', 'submit');
            showMessage('服务器请求错误', 2);
        }
    });
}

function ajaxRecommend(e){
    e.preventDefault();
    var index = showLoad();
    var form = e.target;
    $(form).find('button[type="submit"]').attr('type', 'button');
    $.ajax({
        url:form.action,
        type:'post',
        data:$(form).serializeArray(),
        dataType:'json',
        success: function (rs) {
            if(rs.status == 0) {
                layer.close(index);
                showMessage(rs.error_msg, 0);
                $(form).find('button[type="button"]').attr('type', 'submit');
                return;
            }
            if(rs.status == 1) {
                layer.close(index);
                if (rs.msg){
                    showMessage(rs.msg, 1);
                    $(form).find('button[type="button"]').attr('type', 'submit');
                }
            }
            if(rs.redirect) {
                setTimeout(function () {
                    location.href = rs.redirect;
                }, 700);
            }
        },
        error: function () {
            layer.close(index);
            $(form).find('button[type="button"]').attr('type', 'submit');
            showMessage('服务器请求错误', 2);
        }
    });
}

//ajax提交购物卡
function ajaxSubmitShoppingCard(e){
    e.preventDefault();
    var index = showLoadNoTime();
    var form = e.target;
    $(form).find('button[type="submit"]').attr('type', 'button');
    $.ajax({
        url:form.action,
        type:'post',
        data:$(form).serializeArray(),
        dataType:'json',
        success: function (rs) {
            if(rs.status == 0) {
                layer.close(index);
                showMessage(rs.error_msg, 0);
                $(form).find('button[type="button"]').attr('type', 'submit');
                return;
            }
            if(rs.status == 1) {
                layer.close(index);
                if (rs.msg){
                    showMessage(rs.msg, 1);
                }
            }
            if(rs.redirect) {
                setTimeout(function () {
                    location.href = rs.redirect;
                }, 700);
            }
        },
        error: function () {
            layer.close(index);
            $(form).find('button[type="button"]').attr('type', 'submit');
            showMessage('服务器请求错误', 2);
        }
    });
}

//提交之间验证规格型号数据
function validateData(obj){
    //验证规格信息
    var spec_class = $(obj).find('.spec');
    if (spec_class.length){
        var spec = new Array();
        var model = new Array();
        $(obj).find('.spec').each(function (i) {
            var flag = true;
            var parent = $(this).parents('tr');
            var class_num = parent.attr('class');
            model = [];
            parent.parent().find('.'+class_num+' .model').each(function (j) {
                model[j] = $.trim($(this).val());
            });
            if (isRepeat(model)){
                showMessage('同一规格下的型号不能重名', 0);
                throw new Error('同一规格下的型号名称重复');
            }
            spec[i] = $.trim($(this).val());
        });
        if (isRepeat(spec)){
            showMessage('规格名称不能重复', 0);
            return false;
        }
        var flag = 1;
        $(obj).find('.price').each(function () {
            if (!/^([1-9][0-9]{0,7}(\.[0-9]{1,2})?|0\.[0-9]{1,2})$/.test($.trim($(this).val()))){
                showMessage('售价最多8位并且保留两位小数', 0);
                flag = 0;
                return false;
            }
        });
         if(flag === 0){
             return false;
         }
        $(obj).find('.original_price').each(function () {
            if (!/^([1-9][0-9]{0,7}(\.[0-9]{1,2})?|0\.[0-9]{1,2})$/.test($.trim($(this).val()))){
                showMessage('原价最多8位并且保留两位小数', 0);
                flag = 0;
                return false;
            }
            var price = $(this).parent().prev().find('.price').val();
            if (parseFloat(price) > parseFloat($.trim($(this).val()))){
                showMessage('原价必须大于售价', 0);
                flag = 0;
                return false;
            }
        });
        if(flag === 0){
            return false;
        }
        $(obj).find('.cost_price').each(function () {
            if (!/^([1-9][0-9]{0,7}(\.[0-9]{1,2})?|0\.[0-9]{1,2})$/.test($.trim($(this).val()))){
                showMessage('成本价最多8位并且保留两位小数', 0);
                flag = 0;
                return false;
            }
            var original_price = $(this).parent().prev().find('.original_price').val();
            if (parseFloat(original_price) < parseFloat($.trim($(this).val()))){
                showMessage('原价必须大于成本价', 0);
                flag = 0;
                return false;
            }
        });
        if(flag === 0){
            return false;
        }
        $(obj).find('.stock').each(function () {
            if (!/^([1-9]\d{0,7}|0)$/.test($.trim($(this).val()))){
                showMessage('库存必须是为大于或等于0的数字（最多8位）', 0);
                flag = 0;
                return false;
            }
        });
        if(flag === 0){
            return false;
        }
        $(obj).find('.bar').each(function () {
            if (!/^\d*\w*$/.test($.trim($(this).val()))){
                showMessage('条形码必须是字母或数字', 0);
                flag = 0;
                return false;
            }
        });
        if(flag === 0){
            return false;
        }
    }

    return true;
}

//检查数组内是否有重复的值
function isRepeat(arr){

    var hash = {};

    for(var i in arr) {

        if(hash[arr[i]])

            return true;

        hash[arr[i]] = true;

    }

    return false;

}


function ajaxFormForPc(e){
    e.preventDefault();
    var index = showLoad();
    var form = e.target;
    $(form).find('button[type="submit"]').attr('type', 'button');
    $.ajax({
        url:form.action,
        type:'post',
        data:$(form).serializeArray(),
        dataType:'json',
        success: function (rs) {
            if(rs.status == 0) {
                layer.close(index);
                showMessage(rs.error_msg, 0);
                $(form).find('button[type="button"]').attr('type', 'submit');
                return;
            }
            if(rs.status == 1) {
                layer.close(index);
                if (rs.msg){
                    showMessage(rs.msg, 1);
                }
            }
            if(rs.redirect) {
                setTimeout(function () {
                    location.href = rs.redirect;
                }, 700);
            }
        },
        error: function () {
            layer.close(index);
            $(form).find('button[type="button"]').attr('type', 'submit');
            showMessage('服务器请求错误', 2);
        }
    });
}