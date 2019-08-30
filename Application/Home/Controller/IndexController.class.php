<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        $id = I('id');
        $end_date = '2019/9/10 12:00:00';
        $end_date = getDateToMesc($end_date);
        $now_date = msectime();
        $this->assign('now_date',$now_date);
        $this->assign('end_date',$end_date);
        $this->display();
    }
}