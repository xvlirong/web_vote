//�������ĵ���ʱ
function checkTime(i){ //��0-9������ǰ�����0����1��Ϊ01
    if(i<10) {
        i = "0" + i;
    }
    return i;
}
function show_time(html,html2){
    var time_now = new Date(); // ��ȡ��ǰʱ��
    time_now = time_now.getTime();
    var time_distance = time_end - time_now; // ����ʱ���ȥ��ǰʱ��
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
        $('.countdown .time').html(html);
        setTimeout("show_time()",1000);
    }else{
        $('.countdown .time').html(html2);
    }
}
show_time();