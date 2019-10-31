
//合作品牌
var brand={
    iNow:0,
    init:function(){
        this.renderPoint();
    },
    renderPoint:function(){
        var iLen=$('#brand li').size();
        console.log(iLen);
        var str='';
        for(var i=0;i<iLen;i++){
            str+='<span></span>';
        }
        var oUl=$('<div id="brandNum">'+str+'</div>');
        $('#brand').append(oUl);
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
        $('#brand ul').animate({'margin-left':-1210*iNow},300);
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