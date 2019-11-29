(function () {
    function alertBox(message){
        $('#mask').show();
        $('#message').show();
        $('#message').html(message);
        setTimeout(function(){
            $('#mask').hide();
            $('#message').hide();
        },1500);
    }
    //表单提交
    $('#submit').click(function(){
        var objective = $('input[name="objective"]:checked').val();
        var reg=/\s+/;
        var num=/\d{11}/;
        var text=/[\u4e00-\u9fa5]{2,5}/;
        var username = $('#username').val();
        var phone = $('#phone').val();
        var modelValue ='';
        $('input[name="model"]:checked').each(function(){
            modelValue+=$(this).val()+',';
        });
        modelValue=modelValue.substr(0,modelValue.length-1);
        if(!username || !phone || !modelValue|| reg.test(username) || reg.test(phone) || reg.test(modelValue)) {
            alertBox('必填项不能为空');
            return false;
        }
        if(!text.test(username)) {
            alertBox('姓名格式错误');
            return false;
        }
        if(!num.test(phone)) {
            alertBox('电话格式错误');
            return false;
        }
        var data = {
            act_id:id,
            source:source,
            sign_url:all_url,
            username: username,
            //objective:objective||0,
            userphone:phone,
            source_type:type,
            //url:url,
            car_type:modelValue
        };
        $.post(use_url,data,function(res){
            res = eval('(' + res + ')');//由JSON字符串转换为JSON对象

            if(res.code == '200'){
                //alertBox(res.data);
                $('#username').val('');
                $('#phone').val('');
                location.href = base_url + "signSuccess/id/"+id+ "/source/"+source+"/";
                //$('.signlist .list ul li').last().remove();
                //var str='<li><span class="username">'+username.substring(0, 1)+'xx</span><span class="userphone">'+phone.substring(0,3)+"****"+phone.substring(7)+'</span><span class="usercar">'+modelValue.split(',')[0]+'</span><span class="usertime">1分钟以前</span></li>';
                //$(str).insertBefore($('.signlist .list ul li').first());
            }else{
                alertBox(res.data);
            }
        });
    });
})();