<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends BaseController {
    public function index(){
        $id = I('id',1);
        //pv自增
        M("rv_act")->where(array('id'=>$id))->setInc('pv_num');

        //公共信息
        $base_info = $this->getBaseInfo($id);
        $this->assign('end_date',$base_info['end_date']);
        $this->assign('now_date',$base_info['now_date']);
        $this->assign('base_info',$base_info);
        $this->display();
    }

    public function detail()
    {
        echo 'xiangqing';
    }

    /**
     * @param $id
     * @return mixed
     * @throws \think\Exception
     * 基础信息
     */
    public function getBaseInfo($id)
    {
        //活动信息
        $act_info = M("rv_act")->where(array('id'=>$id))->field('act_name,end_time')->find();

        //结束时间
        $end_date = date("Y/m/d H:i:s",$act_info['end_time']);
        $info['end_date'] = getDateToMesc($end_date);
        //当前时间
        $info['now_date'] = msectime();

        $maps = array('act_id'=>$id,'company_state'=>1);
        //参赛数
        $info['entries_num'] = M("act_company")->where($maps)->count();

        //投票数
        $info['vote_num'] = M("act_company")->where($maps)->sum('tp_num');

        //访问量
        $info['pv_num'] = M("rv_act")->where(array('id'=>$id))->getField('pv_num');
        return $info;
    }


}