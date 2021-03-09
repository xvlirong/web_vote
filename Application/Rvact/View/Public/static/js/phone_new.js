(function(){
    // 百度地图API功能
    var map = new BMap.Map("allmaps");    // 创建Map实例
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
    map.enableScrollWheelZoom(false);     //开启鼠标滚轮缩放
})();

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
            if(_this.iNow>$('.signlist ul li').size()-1){
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