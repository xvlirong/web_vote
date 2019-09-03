<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends BaseController {
    public function index(){
        $id = I('act_id',1);
        //列表
        $list = M("act_company")
            ->where(array('act_id'=>$id,'company_state'=>1))
            ->field('id,company_name,company_logo,tp_num')
            ->select();
        $this->assign('list',$list);

        $vote_state = $this->checkVoteState();
        $this->assign('vote_state',$vote_state);
        //公共信息
        $base_info = $this->getBaseInfo($id);
        $this->assign('end_date',$base_info['end_date']);
        $this->assign('now_date',$base_info['now_date']);
        $this->assign('base_info',$base_info);
        $this->display();
    }

    public function checkVoteState()
    {
        $data['uid'] = $this->userid;
        $res1 = M("rv_users")->where($data)->getField('tel_phone');
        if($res1 == ''){
            $state = 3;  //1可投票 2已投票 3 未完善信息
        }else{
            $data['batch'] = date("Y-m-d",time());
            $exist = M("votes_record")->where($data)->find();
            if($exist){
                $state = 2;
            }else{
                $state = 1;
            }
        }
        return $state;

    }

    public function detail()
    {
        $act_id = I('act_id',1);
        $id = I('id');
        $list = M("company_banner")->where(array('pid'=>$id))->select();
        $this->assign('list',$list);

        $info = M("act_company")->where(array('id'=>$id))->find();
        $this->assign('info',$info);

        $vote_state = $this->checkVoteState();
        $this->assign('vote_state',$vote_state);
        //公共信息
        $base_info = $this->getBaseInfo($act_id);
        $this->assign('end_date',$base_info['end_date']);
        $this->assign('now_date',$base_info['now_date']);
        $this->assign('base_info',$base_info);

        $this->display();

    }

    /**
     * @param $id
     * @return mixed
     * @throws \think\Exception
     * 基础信息
     */
    public function getBaseInfo($id)
    {
        //pv自增
        M("rv_act")->where(array('id'=>$id))->setInc('pv_num');
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

    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 处理用户手机号接口
     */
    public function handleUserInfo()
    {
        $phone = I('phone');
        $exist = M("rv_users")->where(array('tel_phone'=>$phone))->find();
        if($exist){
            $res_info['code'] = 0;
            $res_info['msg'] = '该手机号已被注册';
        }else{
            $code = I('code');
            $maps['phone'] = array("EQ",$phone);
            $maps['code'] = array("EQ",$code);
            $maps['end_time'] = array("GT",time());
            $info = M("msg_code_log")->where($maps)->find();
            if($info){
                $uid = $this->userid;
                $res = M("rv_users")->where(array('id'=>$uid))->save(array('tel_phone'=>$phone));
                if($res){
                    $res_info['code'] = 1;
                    $res_info['msg'] = '处理成功';
                }else{
                    $res_info['code'] = 3;
                    $res_info['msg'] = '处理失败';
                }
            }else{
                $res_info['code'] = 2;
                $res_info['msg'] = '验证码错误';
            }
        }

        $this->ajaxReturn($res_info);
    }

    /**
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     * 处理投票接口
     */
    public function handleVotes()
    {
        $act_id = 1;
        $id = I('id');
        $data['uid'] = $this->userid;
        $data['batch'] = date("Y-m-d",time());
        $exist = M("votes_record")->where($data)->find();
        if($exist){
            $res_info['code'] = 0;
            $res_info['msg'] = '今日已投';
        }else{
            M()->startTrans();
            $data['pid'] = $id;
            $data['act_id'] = $act_id;
            $data['add_time'] = time();
            $res1 = M("votes_record")->add($data);
            $res2 = M("act_company")->where(array('id'=>$id))->setInc('tp_num');
            if($res1 && $res2){
                M()->commit();
                $res_info['code'] = 1;
                $res_info['msg'] = '投票成功';
            }else{
                M()->rollback();
                $res_info['code'] = 2;
                $res_info['msg'] = '处理失败';
            }
        }

        $this->ajaxReturn($res_info);
    }


}