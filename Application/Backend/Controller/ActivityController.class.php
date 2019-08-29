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

    public function saveAct()
    {
        $id = I('id');
        $data['act_name'] = I('act_name');
        $data['start_time'] = I('start_time');
        $data['end_time'] = I('end_time');
        $data['act_state'] = I('end_time');

    }

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


}

?>