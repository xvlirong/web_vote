<?php

namespace Backend\Controller;

class ActivityController extends CommonController
{
    /**
     * 添加活动
     */
    public function index()
    {
        $this->display();
    }

    /**
     * 活动列表
     */
    public function act_list()
    {
        $act_list = M("rv_act")->select();
        $this->assign('list',$act_list);

        $this->display();
    }


    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 活动修改
     */
    public function update_act()
    {
        $id = I('id');
        $info = M("rv_act")->where(array('id' => $id))->find();
        $this->assign('info', $info);

        $this->display();
    }

    /**
     * 处理修改活动信息
     */
    public function saveAct()
    {
        $id = I('id');
        $data['act_name'] = I('act_name');
        $data['start_time'] = strtotime(I('start_time'));
        $data['end_time'] = strtotime(I('end_time'));
        $data['act_state'] = I('act_state');

        $res = M("rv_act")->where(array('id'=>$id))->save($data);

        if($res){
            echo "<script>alert('修改成功'); location.replace(document.referrer);</script>";
        } else {
            echo "<script>alert('修改失败'); location.replace(document.referrer);</script>";
        }

    }

    /**
     * 处理活动添加
     */
    public function addAct()
    {
        $data['act_name'] = I('act_name');
        $data['start_time'] = strtotime(I('start_time'));
        $data['end_time'] = strtotime(I('end_time'));
        $data['add_time'] = time();
        $data['up_time'] = time();

        $res = M("rv_act")->add($data);

        if($res){
            echo "<script>alert('添加成功'); location.replace(document.referrer);</script>";
        } else {
            echo "<script>alert('添加失败'); location.replace(document.referrer);</script>";
        }
    }

    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 参选企业列表
     */
    public function company_list()
    {
        $id = I('id');
        $this->assign('act_id',$id);

        $list = M("act_company")->where(array('act_id'=>$id))->field('id,company_name,company_logo,add_time,company_state,tp_num')->select();
        $this->assign('list',$list);

        $this->display('company_list');
    }

    public function add_company()
    {
        $act_id = I('act_id');
        $this->assign('act_id',$act_id);

        $this->display();
    }

    /**
     * 处理添加公司
     */
    public function addCompany()
    {
        $data['act_id'] = I('act_id');
        $data['company_name'] = I('company_name');
        $img = uploadImg('company_logo');
        $data['company_logo'] = $img['company_logo']['savename'];
        $data['company_intro'] = I('company_intro');
        $data['add_time'] = time();
        $res = M("act_company")->add($data);
        if($res){
            $this->success('添加成功',U('company_list',array('id'=>$data['act_id'])));
        } else {
            echo "<script>alert('添加失败'); location.replace(document.referrer);</script>";
        }
    }


}

?>