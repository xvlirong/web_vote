(function(){
    // �ٶȵ�ͼAPI����
    var map = new BMap.Map("allmap");    // ����Mapʵ��
    map.centerAndZoom(new BMap.Point(lgt,lat), 16);  // ��ʼ����ͼ,�������ĵ�����͵�ͼ����
    var top_left_control = new BMap.ScaleControl({anchor: BMAP_ANCHOR_TOP_LEFT});// ���Ͻǣ���ӱ�����
    var top_left_navigation = new BMap.NavigationControl();  //���Ͻǣ����Ĭ������ƽ�ƿؼ�
//��ӵ�ͼ���Ϳؼ�
    map.addControl(new BMap.MapTypeControl({
        mapTypes:[
            BMAP_NORMAL_MAP,
            BMAP_HYBRID_MAP,
        ]}));
    map.addControl(top_left_control);
    map.addControl(top_left_navigation);
    var icon = new BMap.Icon(themes+'/Fckunshan/images/icon.png', new BMap.Size(65, 32), {
        anchor: new BMap.Size(10, 30)
    });
    var mkr =new BMap.Marker(new BMap.Point(lgt,lat), {
        icon: icon
    });

    map.addOverlay(mkr);
    map.enableScrollWheelZoom(true);     //��������������
})();
//�������ĵ���ʱ
function checkTime(i){ //��0-9������ǰ�����0����1��Ϊ01
    if(i<10) {
        i = "0" + i;
    }
    return i;
}
function show_time(){
    var time_now = new Date(); // ��ȡ��ǰʱ��
    time_now = time_now.getTime();
    var time_distance = time_end-time_now; // ����ʱ���ȥ��ǰʱ��
    var int_day, int_hour, int_minute, int_second;
    if(time_distance >= 0){
        // ��ʱ���뻻��
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

//����Ʒ��
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
        $('#brand ul').animate({'margin-left':-1197*iNow},300);
    }
};
brand.init();

//�������������
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