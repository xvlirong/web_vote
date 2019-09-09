<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends BaseController {
    public function index(){
        $id = I('act_id',1);
        $key = I('key','');

        $list = $this->getTpList($key,$id);
        $this->assign('list',$list);
        $this->assign('key',$key);

        $vote_state = $this->checkVoteState();
        $this->assign('vote_state',$vote_state);

        $batch = date("Ymd",time());
        $record_id = M("votes_record")->where(array('uid'=>$this->userid,'act_id'=>$id,'batch'=>$batch))->getField('pid');
        $this->assign('record_id',$record_id);

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
        $res1 = M("rv_users")->where(array('id'=>$data['uid']))->getField('tel_phone');
        if($res1 == ''){
            $state = 3;  //1可投票 2已投票 3 未完善信息
        }else{
            $data['batch'] = date("Ymd",time());
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
        cookie('share_pid',$id);
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
        $act_info = M("rv_act")->where(array('id'=>$act_id))->find();
        if($act_info['act_state'] == 0 || time()>$act_info['end_time']){
            $res_info['code'] = 3;
            $res_info['msg'] = '活动已结束';
        }else{
            $id = I('id');
            $data['uid'] = $this->userid;
            $data['batch'] = date("Ymd",time());
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
        }

        $this->ajaxReturn($res_info);
    }

    /**
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 排行榜
     */
    public function rank_list()
    {
        $id = I('act_id',1);

        $list = M("act_company")
            ->where(array('act_id'=>$id))
            ->order(array('tp_num'=>'desc','id'=>'asc'))
            ->field('id,company_name,company_logo,tp_num')
            ->select();
        $this->assign('list',$list);

        //公共信息
        $base_info = $this->getBaseInfo($id);
        $this->assign('end_date',$base_info['end_date']);
        $this->assign('now_date',$base_info['now_date']);
        $this->assign('base_info',$base_info);
        $this->display();
    }

    public function act_rules()
    {
        $id = I('act_id',1);

        $info = M("rv_act")->where(array('id'=>$id))->find();
        $this->assign('info',$info);

        //公共信息
        $base_info = $this->getBaseInfo($id);
        $this->assign('end_date',$base_info['end_date']);
        $this->assign('now_date',$base_info['now_date']);
        $this->assign('base_info',$base_info);
        $this->display();
    }

    /**
     * 分享
     */
    public function tp_js_api(){
        $url=htmlspecialchars_decode(trim(I('url')));
        $jssdk=A("Jssdk");
        $signPackage = $jssdk->GetSignPackage($url);
        $img_url = HTTP_TYPE."votes.rvtimes.cn/Public/img/tp_fx.jpg";

        $share_data['title'] = '房车时代网2019"优秀服务商"评选"';
        $share_data['desc'] = '快来帮你喜欢的企业投上宝贵的1票！';
        $share_data['link']=HTTP_TYPE."votes.rvtimes.cn/index/index/since/1";
        $share_data['imgUrl']=$img_url;

        if($signPackage){
            $data['result']="success";
            $data['js_data']=$signPackage;
            $data['share_data']=$share_data;
        }else{
            $data['result']="error";
            $data['js_data']='';
            $data['share_data']='';
        }
        $this->ajaxReturn($data);
    }

    /**
     * 分享
     */
    public function tp_detail_api(){
        $url=htmlspecialchars_decode(trim(I('url')));
        $jssdk=A("Jssdk");
        $signPackage = $jssdk->GetSignPackage($url);
        $pid = cookie('share_pid');
        $info = M("act_company")->where(array('id'=>$pid))->field('company_name,company_logo')->find();
        $img_url = HTTP_TYPE."votes.rvtimes.cn/Public/upload/company_logo/".$info['company_logo'];
        $con = '我正在为'.$info['company_name'].'投票，你也来帮忙吧';
        $share_data['title'] = '房车时代网2019"优秀服务商"评选';
        $share_data['desc'] = $con;
        $share_data['link']=HTTP_TYPE."votes.rvtimes.cn/index/detail/since/1/id/".$pid;
        $share_data['imgUrl']=$img_url;

        if($signPackage){
            $data['result']="success";
            $data['js_data']=$signPackage;
            $data['share_data']=$share_data;
        }else{
            $data['result']="error";
            $data['js_data']='';
            $data['share_data']='';
        }
        $this->ajaxReturn($data);
    }

    public function getTpList($key,$id)
    {
        $maps['act_id'] = array("EQ",$id);
        $maps['company_state'] = array("EQ",1);
        if($key != ''){
            if(is_numeric($key)){
                $maps['sort'] = array("EQ",$key);
            }else{
                $maps['company_name'] = array("LIKE","%$key%");
            }
        }

        //列表
        $list = M("act_company")
            ->where($maps)
            ->field('id,company_name,company_logo,tp_num,sort')
            ->select();
        return $list;
    }


}