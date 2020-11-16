(function(){
    // 百度地图API功能
    var map = new BMap.Map("allmap");    // 创建Map实例
    map.centerAndZoom(new BMap.Point(lgt*1,lat*1), 17);  // 初始化地图,设置中心点坐标和地图级别
    var top_left_control = new BMap.ScaleControl({anchor: BMAP_ANCHOR_TOP_LEFT});// 左上角，添加比例尺
    var top_left_navigation = new BMap.NavigationControl();  //左上角，添加默认缩放平移控件
//添加地图类型控件
    map.addControl(new BMap.MapTypeControl({
        mapTypes:[
            BMAP_NORMAL_MAP,
            BMAP_HYBRID_MAP,
        ]}));
    map.addControl(top_left_control);
    map.addControl(top_left_navigation);
    var icon = new BMap.Icon(themes+'/images/marker_red_sprite.png', new BMap.Size(65, 32), {
        anchor: new BMap.Size(10, 30)
    });
    var mkr =new BMap.Marker(new BMap.Point(lgt,lat), {
        icon: icon
    });

    map.addOverlay(mkr);
    map.enableScrollWheelZoom(true);     //开启鼠标滚轮缩放
})();
//带天数的倒计时
function checkTime(i){ //将0-9的数字前面加上0，例1变为01
    if(i<10) {
        i = "0" + i;
    }
    return i;
}
//show_time();
function show_time(){
    var time_now = new Date(); // 获取当前时间
    time_now = time_now.getTime();
    var time_distance = time_end - time_now; // 结束时间减去当前时间
    var int_day, int_hour, int_minute, int_second;
    if(time_distance >= 0){
        // 天时分秒换算
        int_day = Math.floor(time_distance/86400000);
        time_distance -= int_day * 86400000;
        int_hour = Math.floor(time_distance/3600000);
        time_distance -= int_hour * 3600000;
        int_minute = Math.floor(time_distance/60000);
        time_distance -= int_minute * 60000;
        int_second = Math.floor(time_distance/1000);
        int_day =checkTime(int_day);
        int_hour =checkTime(int_hour);
        int_minute =checkTime(int_minute);
        int_second =checkTime(int_second);
        $('.countdown .time').html("<b><span>"+int_day+"</span><span>"+int_hour+"</span><span>"+int_minute+"</span><span>"+int_second+"</span></b>");
        setTimeout("show_time()",1000);
    }else{
        $('.countdown .time').html("<b><span>-</span><em>天</em><span>-</span><em>时</em><span>-</span><em>分</em><span>-</span><em>秒</em></b>");
    }
}

//最近报名的团友
var marquee={
    iNow:0,
    init:function(){
        if($('.signlist ul li').size()>10){
            this.auto();
        }
    },
    move:function(){
        $('.signlist ul').stop().animate({'margin-top':-$('.signlist ul li').height()*this.iNow},400);
    },
    auto:function(){
        var _this=this;
        setInterval(function(){
            _this.iNow++;
            if(_this.iNow>$('.signlist ul li').size()-9){
                _this.iNow=0;
            }
            _this.move();
        },3000);
    }
};
marquee.init();
//更多品牌
function moreBrand(){
    var num=36;
    var iLen=$('#brand li').size();
    $('#more').click(function(){
        if(num>=iLen){
            return false;
        }
        num+=36;
        for(var i=0;i<num;i++){
            $('#brand li').eq(i).removeClass('none');
        }
    });
}
moreBrand();