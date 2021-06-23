$(function(){
    //点击右边箭头icon时候
    $(".selectBox .fa,.imitationSelect1 ").on("click",function(event){
        $(this).parent().next().toggle();//ul弹窗展开
        if($(".fa").hasClass("fa-caret-down")){
            $(".fa").removeClass("fa-caret-down").addClass("fa-caret-up")//点击input选择适合，小图标动态切换
        }else{
            $(".fa").addClass("fa-caret-down").removeClass("fa-caret-up")//点击input选择适合，小图标动态切换
        }
        if (event.stopPropagation) {
            // 针对 Mozilla 和 Opera
            event.stopPropagation();
        }else if (window.event) {
            // 针对 IE
            window.event.cancelBubble = true;
        }
    });

    //
    $(".selectUl li").click(function(event){
        event=event||window.event;
        $(this).addClass("actived_li").siblings().removeClass("actived_li");//点击当前的添加。actived_li这个类；其他的移除这个类名
        var oliName = $(this).attr("oliName");//定义一个name属性，获取点击的元素属性赋值到当前，方便动态化传值
        var oliId = $(this).attr("oliId");//定义一个id属性，获取点击的元素属性赋值到当前，方便数据交互传值
        $("#club_p").html(oliName); //把当前点击的name赋值到显示的input的val里面
        $(this).parent().prev().children().attr("oliName",oliName);//把当前点击的oliName赋值到显示的input的oliName里面
        $(this).parent().prev().children().attr("oliId",oliId);//把当前点击的oliId赋值到显示的input的oliId里面
        if(oliName == 0){
            $("#club_name").show();
            $("#club_p").hide();
            $("#club_name").focus();
        }

    });

    $("#club_name").blur(function () {
        var oliName = $("#club_name").val();
        if(oliName == ''){
            oliName = '请选择您的俱乐部'
        }
        $("#club_p").html(oliName);
        $("#club_name").hide();
        $("#club_p").show();
    });
    //点击任意地方隐藏下拉
    $(document).click(function(event){
        event=event||window.event;
        $(".inputCase .fa").removeClass("fa-caret-up").addClass("fa-caret-down")//当点隐藏ul弹窗时候，把小图标恢复原状
        $(".selectUl").hide();//当点击空白处，隐藏ul弹窗
    });

});

function alertBox(message){
    $('#mask').show();
    $('#message').show();
    $('#message').html(message);
    setTimeout(function(){
        $('#mask').hide();
        $('#message').hide();
    },1500);
}

$("#sign_btn").click(function () {
    var reg=/\s+/;
    var club_name = $("#club_p").html();
    var username = $("#username").val();
    var phone = $("#userphone").val();
    var verify_code = $("#verify_code").val();
    if(!username || !phone || !verify_code || reg.test(username) || reg.test(phone) || reg.test(verify_code) || club_name == '请选择您的俱乐部') {
        alertBox('必填项不能为空');
        return false;
    }
    var text=/[\u4e00-\u9fa5]{1,5}/;
    if(!text.test(username)) {
        alertBox('姓名格式错误');
        return false;
    }
    var num = /^1[3-9]\d{9}$/;
    if(!num.test(phone)) {
        alertBox('电话格式错误');
        return false;
    }
    var code = /^[0-9]*$/;
    if(!code.test(verify_code)) {
        alertBox('验证码格式错误');
        return false;
    }
    var data = {
        act_id:id,
        source:source,
        url_refer:url_refer,
        username: username,
        //objective:objective||0,
        userphone:phone,
        club_name:club_name,
        //url:url,
        verify_code:verify_code
    };
    $.post(use_url,data,function(res){
        res = eval('(' + res + ')');//由JSON字符串转换为JSON对象

        if(res.code == '200'){
            //alertBox(res.data);
            $('#username').val('');
            $('#phone').val('');
            location.href = base_url + "rider_success/id/"+id+ "/source/"+source+"/";
            //$('.signlist .list ul li').last().remove();
            //var str='<li><span class="username">'+username.substring(0, 1)+'xx</span><span class="userphone">'+phone.substring(0,3)+"****"+phone.substring(7)+'</span><span class="usercar">'+modelValue.split(',')[0]+'</span><span class="usertime">1分钟以前</span></li>';
            //$(str).insertBefore($('.signlist .list ul li').first());
        }else{
            alertBox(res.data);
        }
    });
});