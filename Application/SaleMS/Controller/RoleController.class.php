<?php
namespace SaleMS\Controller;
use Think\Controller;
class RoleController extends CommonController {

    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 角色列表
     */
    public function user_list()
    {
        $list = M("sms_user")
            ->join("left join sms_role on sms_user.role_id=sms_role.id")
            ->field('sms_user.*,sms_role.role_name')
            ->order('sms_user.add_time desc')
            ->select();
        $this->assign('list',$list);
        $this->display();
    }

    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 新增角色
     */
    public function add_user()
    {
        $role_list = M("sms_role")->select();
        $this->assign('role_list',$role_list);
        $this->display();
    }

    /**
     * 处理新增角色
     */
    public function addUser()
    {
        $data['user_name'] = I('user_name');
        $data['tel_phone'] = I('tel_phone');
        $data['role_id'] = I('role_id');
        $data['area'] = I('area');
        $data['add_time'] = time();

        $res = M("sms_user")->add($data);
        if($res){
            $this->success('添加成功',U('user_list'));
        } else {
            echo "<script>alert('添加失败'); location.replace(document.referrer);</script>";
        }
    }

    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 修改角色信息
     */
    public function update_role()
    {
        $id = I('id');
        $info = M("sms_user")->where(array('id'=>$id))->find();
        $this->assign('info',$info);

        $role_list = M("sms_role")->select();
        $this->assign('role_list',$role_list);

        $this->display();
    }

    /**
     * 处理修改角色信息
     */
    public function saveUser()
    {
        $id = I('id');
        $data['user_name'] = I('user_name');
        $data['tel_phone'] = I('tel_phone');
        $data['role_id'] = I('role_id');
        $data['area'] = I('area');
        $res = M("sms_user")->where(array('id'=>$id))->save($data);
        echo "<script>alert('处理成功'); location.replace(document.referrer);</script>";
    }

    public function delUser()
    {
        $id = I('id');

        $res = M("sms_user")->where(array('id'=>$id))->delete();
        if($res){
            echo "<script>alert('处理成功'); location.replace(document.referrer);</script>";
        }else{
            echo "<script>alert('处理失败'); location.replace(document.referrer);</script>";
        }
    }
}