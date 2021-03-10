$(document).ready(function(){
    $.ajax({
        type:"post",
        url:fx_url,
        data:"url="+encodeURIComponent(location.href.split('#')[0]),
        dataType:"json",
        success:function(data){
            if ( data.result == "success" ) {
                var appid = data.js_data.appId,
                    timestamp = data.js_data.timestamp,
                    nonceStr = data.js_data.nonceStr,
                    signature = data.js_data.signature;
                wx.config({
                    debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
                    appId: appid, // 必填，公众号的唯一标识
                    timestamp: timestamp, // 必填，生成签名的时间戳
                    nonceStr: nonceStr, // 必填，生成签名的随机串
                    signature: signature,// 必填，签名，见附录1
                    jsApiList: ['onMenuShareAppMessage','onMenuShareTimeline'] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
                });
                wx.ready(function(){
                    //分享到朋友圈
                    wx.onMenuShareTimeline({
                        title: data.share_data.title, // 分享标题
                        link: data.share_data.link, // 分享链接
                        imgUrl: data.share_data.imgUrl, // 分享图标
                        success: function () {
                            console.log("分享成功！")
                        }
                    });
                    //分享给朋友
                    wx.onMenuShareAppMessage({
                        title: data.share_data.title, // 分享标题
                        desc: data.share_data.desc, // 分享描述
                        link: data.share_data.link, // 分享链接
                        imgUrl: data.share_data.imgUrl, // 分享图标
                        success: function () {
                            console.log("分享成功！")
                        }
                    });

                });
            }
        }
    });
})
//});
/*
 * 微信分享JS end
 */

