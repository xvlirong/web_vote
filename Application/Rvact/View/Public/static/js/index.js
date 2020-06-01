(function(){
    // 百度地图API功能
    var map = new BMap.Map("allmap");    // 创建Map实例
    map.centerAndZoom(new BMap.Point(lgt,lat), 16);  // 初始化地图,设置中心点坐标和地图级别
//添加地图类型控件
    map.addControl(new BMap.MapTypeControl({
        mapTypes:[
            BMAP_NORMAL_MAP,
            BMAP_HYBRID_MAP,
        ]}));
    //var new_point = new BMap.Point(lgt,lat);
    //var marker = new BMap.Marker(new_point);

    var icon = new BMap.Icon(themes+'/images/marker_red_sprite.png', new BMap.Size(65, 32), {
        anchor: new BMap.Size(10, 30)
    });
    var mkr =new BMap.Marker(new BMap.Point(lgt,lat), {
        icon: icon
    });
    map.addOverlay(mkr);
    //marker.setAnimation(BMAP_ANIMATION_BOUNCE); //跳动的动画
    map.enableScrollWheelZoom(true);     //开启鼠标滚轮缩放
})();
//带天数的倒计时
function checkTime(i){ //将0-9的数字前面加上0，例1变为01
    if(i<10) {
        i = "0" + i;
    }
    return i;
}
function show_time(){
    var time_now = new Date(); // 获取当前时间
    time_now = time_now.getTime();
    var time_distance = time_end-time_now; // 结束时间减去当前时间
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
        $('.countdown .time').html("<span>"+int_day+"</span><span>"+int_hour+"</span><span>"+int_minute+"</span><span>"+int_second+"</span>");
        setTimeout("show_time()",1000);
    }else{
        $('.countdown .time').html("<span>-</span><span>-</span><span>-</span><span>-</span>");
    }
}
show_time();

//合作品牌
var brand={
    iNow:0,
    init:function(){
        this.renderPoint();
    },
    renderPoint:function(){
        var iLen=$('#brand li').size();
        var str='';
        for(var i=0;i<iLen;i++){
            str+='<span></span>';
        }
        var oUl=$('<div id="brandNum">'+str+'</div>');
        $('#brand .con').append(oUl);
        $('#brandNum span').eq(0).addClass('act');
        this.pointHandler();
    },
    pointHandler:function(){
        var _this=this;
        $(document).delegate('#brandNum span','click',function(){
            $(this).addClass('act').siblings().removeClass('act');
            _this.iNow=$(this).index();
            _this.move(_this.iNow);
            var height=$('#brand ul li').eq(_this.iNow).height();
            $('#brand .list').css({'height':height});
        })
    },
    move:function(iNow){
        if(iNow==2){
            $("#wrap").height(3400)
        }else{
            $("#wrap").height(3840)
        }
        $('#brand ul').animate({'margin-left':-1240*iNow},300);
    }
};
brand.init();

//最近报名的团友
var marquee={
      iNow:0,
      init:function(){
            if($('.signlist ul li').size()>16){
                this.auto();
            }
      },
      move:function(){
            $('.signlist ul').stop().animate({'margin-top':-30*this.iNow},400);
      },
      auto:function(){
            var _this=this;
            setInterval(function(){
                _this.iNow++;
                if(_this.iNow>$('.signlist ul li').size()-8){
                    _this.iNow=0;
                }
                _this.move();
            },3000);
      }
};
marquee.init();