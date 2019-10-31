//带天数的倒计时
function checkTime(i){ //将0-9的数字前面加上0，例1变为01
    if(i<10) {
        i = "0" + i;
    }
    return i;
}
function show_time(html,html2){
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
        $('.countdown .time').html(html);
        setTimeout("show_time()",1000);
    }else{
        $('.countdown .time').html(html2);
    }
}
show_time();