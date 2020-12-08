<?php
namespace Invitation\Controller;
use Think\Controller;
class IndexController extends BaseController {
    public function index(){
        $maps['sms_offer_record.update_time'] = array('EQ',0);
        $invite_state = I('invite_state');
        $title = I('title');
        $map_order = array('sms_offer_record.add_time'=>'desc');
        if($invite_state===''&&$title===''){
        }else{
            if($title===''){
                if($invite_state == 1){
                    $maps['sms_offer_record.update_time'] = array("GT",0);
                    $map_order = array('sms_offer_record.update_time'=>'desc');
                }else{
                    $maps['sms_offer_record.update_time'] = array("EQ",0);
                }

            }elseif ($invite_state === ''){
                $maps['sms_user_data.phone'] = array("like","%$title%");
            }else{
                if($invite_state == 1){
                    $maps['sms_offer_record.update_time'] = array("GT",0);
                    $map_order = array('sms_offer_record.update_time'=>'desc');
                }else{
                    $maps['sms_offer_record.update_time'] = array("EQ",0);
                }
                $maps['sms_user_data.phone'] = array("like","%$title%");
            }
        }
        $user_id = session('sale_our_saleId');
        $maps['sale_id'] = array('EQ',$user_id);

        $admin_arr = array(14,15,16,17,18);
        if(in_array($user_id,$admin_arr)){
            $maps['area'] = array('LIKE',"%西安%");
        }
        $list = M("sms_user_data")
            ->join("left join sms_offer_record on sms_user_data.id=sms_offer_record.pid")
            ->where($maps)
            ->order($map_order)
            ->field('sms_user_data.id,phone,sms_offer_record.add_time,sms_offer_record.update_time')
            ->select();

        $this->assign('state',$invite_state);
        $this->assign('title',$title);
        $this->assign('list',$list);

        $this->display();
    }

    public function invite_info()
    {
        $id = I('id');
        $info = M("sms_user_data")
            ->join("left join sms_offer_record on sms_user_data.id=sms_offer_record.pid")
            ->where(array('sms_user_data.id'=>$id))
            ->field('sms_user_data.*,sms_offer_record.add_time,sms_offer_record.id as new_id,client_level,buy_time,car_type,budget,mark')
            ->find();
        $this->assign('info',$info);

        $this->display();
    }

    public function handleInfo()
    {
        $id = I('id');
        $data['client_level'] = I('client_level');
        $data['buy_time'] = I('buy_time');
        $data['car_type'] = I('car_type');
        $data['budget'] = I('budget');
        $data['mark'] = I('mark');
        $data['update_time'] = time();
        $res = M("sms_offer_record")->where(array('id'=>$id))->save($data);
        if($res){
            $msg['code'] = 1;
            $msg['url'] = U('Index/index');
            $msg['msg'] = '提交成功';
        }else{
            $msg['code'] = 0;
            $msg['msg'] = '处理失败';
        }
        $this->ajaxReturn($msg);

    }
}