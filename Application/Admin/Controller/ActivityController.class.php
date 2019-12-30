<?php

namespace Admin\Controller;
use Extend\Page;
use Think\Controller;
class ActivityController extends CommonController
{
    /**
     * 添加活动
     */
    public function index()
    {
        $brand_list = M("brand_library")->select();
        $this->assign('brand_list',$brand_list);

        $tpl_list = M("act_template")->order("id desc")->select();
        $this->assign('tpl_list',$tpl_list);

        $this->display();
    }

    /**
     * 活动列表
     */
    public function act_list()
    {
        $act_list = M("activity")->order(array('start_time'=>'desc','id'=>'desc'))->select();
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
     * 处理活动添加
     */
    public function addAct()
    {
        $data = $_POST;

        $data['start_time'] = strtotime($data['start_time']);
        $data['end_time'] = strtotime($data['end_time']);
        //图片上传
        $pc_banner = $_FILES['banner'];
        $data['banner'] = $this->uploadImgs($pc_banner,'banner');
        //移动端图片
        $mobile_banner =  $_FILES['banner_mobile'];
        $data['banner_mobile'] = $this->uploadImgs($mobile_banner,'banner');
        $data['add_time'] = time();
        //微信图片
        $wx_img = $_FILES['wx_img'];
        $data['wx_img'] = $this->uploadImgs($wx_img,'wx_img');

        $res = M("activity")->add($data);

        if($res){
            $brand_list = I("brand_list");
            $this->addBrandLabel($res,$brand_list);
         $res1 = $this->handleAddSceneImg($res);
            if($res1){
                echo "<script>alert('添加成功'); location.replace(document.referrer);</script>";
            } else {
                echo "<script>alert('添加失败'); location.replace(document.referrer);</script>";
            }
        }else{
            echo "<script>alert('处理失败，请稍后再试'); location.replace(document.referrer);</script>";
        }
    }

    public function handleAddSceneImg($act_id)
    {
        $pc_img = $_FILES['pc_img'];
        if($_FILES['pc_img']['name'] != ''){
            $pc_img= $this->uploadImgs($pc_img,'scene');
            if($pc_img){
                $img_arr['act_id'] = $act_id;
                $img_arr['img_type'] = 1;
                $img_arr['img_url'] = $pc_img;
                $img_arr['add_time'] = time();
                $res1 = M("scene_img")->add($img_arr);
                if($res1){
                    $state = 1;
                }else{
                    $state = 0;
                }
            }else{
                $state = 1;
            }
        }else{
            $state = 1;
        }
        $mobile_img = $_FILES['mobile_img'];

        if($_FILES['mobile_img']['name'] != ''){
            $mobile_img= $this->uploadImgs($mobile_img,'scene');
            if($mobile_img){
                $img_arr['act_id'] = $act_id;
                $img_arr['img_type'] = 2;
                $img_arr['img_url'] = $mobile_img;
                $img_arr['add_time'] = time();
                $res2 = M("scene_img")->add($img_arr);
                if($res2){
                    $state = 1;
                }else{
                    $state = 0;
                }
            }else{
                $state = 1;
            }
        }else{
            $state = 1;
        }


        if($state == 1){
            return true;
        }else{
            return false;
        }

    }

    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 品牌库列表
     */
    public function brand_list()
    {
        $list = M("brand_library")->order(array('id'=>'desc'))->select();
        $this->assign('list',$list);

        $this->display();
    }

    /**
     * 品牌添加
     */
    public function add_brand()
    {
        $this->display();
    }

    /**
     * 处理品牌添加
     */
    public function addBrand()
    {
        $data['brand_name'] = I('brand_name');
        $file = $_FILES['brand_logo'];
        $data['brand_logo'] = $this->uploadImgs($file,'brand');
        $file1 = $_FILES['zh_logo'];
        $data['zh_logo'] = $this->uploadImgs($file1,'brand');
        $data['sort'] = I('sort');
        $data['add_time'] = time();
        $res = M("brand_library")->add($data);
        if($res){
            $this->success('添加成功',U('brand_list'));
        } else {
            echo "<script>alert('添加失败'); location.replace(document.referrer);</script>";
        }
    }

    public function save_act()
    {
        //基础信息
        $id = I('id');
        $brand_list = M("brand_library")->order('sort asc')->select();
        $this->assign( 'brand_list',$brand_list);

        $tpl_list = M("act_template")->order("id desc")->select();
        $this->assign('tpl_list',$tpl_list);

        $info = M("activity")->where(array('id'=>$id))->find();
        $this->assign('info',$info);

        //现场照片
        $pc_scene = M("scene_img")->where(array('act_id'=>$id,'img_type'=>1))->order('id desc')->find();
        $this->assign('pc_scene',$pc_scene);
        $mobile_scene = M("scene_img")->where(array('act_id'=>$id,'img_type'=>2))->select();
        $this->assign('mobile_scene',$mobile_scene);

        //品牌库
        $brand_label = M("brand_library")
            ->join("left join act_brand on brand_library.id=act_brand.brand_id")
            ->where(array('act_brand.act_id'=>$id))
            ->order(array('brand_library.sort'=>'asc'))
            ->field('brand_library.*')
            ->select();
        $this->assign('brand_label',array_column($brand_label,'id'));



        $this->display();
    }

    public function template()
    {

        $list = M("act_template")->order('id desc')->select();
        $this->assign('list',$list);

        $this->display();
    }

    public function addTemplate()
    {
        $data = $_POST;
        $file = $_FILES['preview_img'];
        $data['preview_img'] = $this->uploadImgs($file,'img');
        $data['add_time'] = time();
        $res = M("act_template")->add($data);
        if($res){
            $this->success('添加成功',U('template'));
        } else {
            echo "<script>alert('添加失败'); location.replace(document.referrer);</script>";
        }
    }

    public function addBrandLabel($pid,$personal_label)
    {
        foreach ($personal_label as $v) {
            $arr1 = array();
            $arr1['act_id'] = $pid;
            $arr1['add_time'] = time();
            $arr1['brand_id'] = $v;
            $labelArr[] = $arr1;
        }
        M('act_brand')->addAll($labelArr);

    }

    public function addBrandClass()
    {
        $id = I('pid');
        $brand_id = I('label_id');
        $exist = M("act_brand")->where(array('act_id'=>$id,'brand_id'=>$brand_id))->find();
        if($exist){
            M("act_brand")->where(array('act_id'=>$id,'brand_id'=>$brand_id))->delete();
        }else{
            M("act_brand")->data(array('act_id'=>$id,'brand_id'=>$brand_id,'add_time'=>time()))->add();
        }
    }

    public function delScene()
    {
        $id = I('id');
        M("scene_img")->where(array('id'=>$id))->delete();
    }

    public function saveAct()
    {
        $data = $_POST;

        $data['start_time'] = strtotime($data['start_time']);
        $data['end_time'] = strtotime($data['end_time']);
        //图片上传
        $pc_banner = $_FILES['banner'];
        if($_FILES['banner']['name'] != ''){
            $data['banner'] = $this->uploadImgs($pc_banner,'banner');
        }

        //移动端图片
        $mobile_banner =  $_FILES['banner_mobile'];
        if($_FILES['banner_mobile']['name'] != ''){
            $data['banner_mobile'] = $this->uploadImgs($mobile_banner,'banner');
        }
        $data['add_time'] = time();
        //微信图片
        $wx_img = $_FILES['wx_img'];
        if($_FILES['wx_img']['name'] != ''){
            $data['wx_img'] = $this->uploadImgs($wx_img,'wx_img');
        }
        $this->handleAddSceneImg($data['id']);
        $res = M("activity")->save($data);
        if($res){
            echo "<script>alert('处理成功'); location.replace(document.referrer);</script>";
        } else {
            echo "<script>alert('处理失败'); location.replace(document.referrer);</script>";
        }

    }

    public function entry_info()
    {
        $id = I('id');
        $this->assign('id',$id);

        $start_time = strtotime(I('start_time',0));
        //echo $start_time;
        $this->assign('start_time',$start_time);
        $end_time = strtotime(I('end_time',0));
        $this->assign('end_time',$end_time);
        if($start_time>0){
            $end_time = $end_time+86400;
            $map['add_time'] = array('BETWEEN',array($start_time,$end_time));
        }
        $map['act_id'] = array('EQ',$id);

        $list = M("act_registration")->where($map)->order('id desc')->select();
        $all_num = count($list);
        $count = count($list);
        $Page = new \Extend\Page($count,50);
        $show = $Page->show();// 分页显示输出
        $list = M("act_registration")
            ->where($map)
            ->limit($Page->firstRow.','.$Page->listRows)
            ->order('id desc')
            ->select();
        $this->assign('all_num',$all_num);

        $today_time = strtotime(date("Y-m-d",time()));
       // echo $end_time;die;
        $maps['add_time'] = array("GT",$today_time);
        $maps['act_id'] = array("EQ",$id);

        $today_num = M("act_registration")->where($maps)->count();
        $this->assign('today_num',$today_num);

        $this->assign('list',$list);
        $this->assign('id',$id);
        $this->assign('page',$show);

        $this->display();
    }


    private  function getExcel($fileName,$headArr,$data){
        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能inport导入
        $date = date("Y_m_d",time());
        $fileName .= "_{$date}.xls";

        //创建PHPExcel对象，注意，不能少了\
        vendor("PHPExcel.PHPExcel");

        $objPHPExcel = new \PHPExcel();
        $objProps = $objPHPExcel->getProperties();

        //设置表头
        $key = ord("A");
        //print_r($headArr);exit;
        foreach($headArr as $v){
            $colum = chr($key);
            $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
            $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
            $key += 1;
        }

        $column = 2;
        $objActSheet = $objPHPExcel->getActiveSheet();

        //print_r($data);exit;
        foreach($data as $key => $rows){ //行写入
            $span = ord("A");
            foreach($rows as $keyName=>$value){// 列写入
                $j = chr($span);
                if($value==1){
                    $value='是';
                }elseif($value=='0'){
                    $value='否';
                }
                $objActSheet->setCellValue($j.$column, $value);
                $span++;
            }
            $column++;
        }

        $fileName = iconv("utf-8", "gb2312", $fileName);
        //重命名表
        //$objPHPExcel->getActiveSheet()->setTitle('test');
        //设置活动单指数到第一个表,所以Excel打开这是第一个表
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output'); //文件通过浏览器下载
        exit;
    }

    public function exam_export()
    {
        $id = I('id');
        $start_time = strtotime(I('start_time',0));
        $end_time = strtotime(I('end_time',0));
        if($start_time>0){
            $end_time = $end_time+86400;
            $map['add_time'] = array('BETWEEN',array($start_time,$end_time));
        }
        $map['act_id'] = array('EQ',$id);
        $goods_list = M('act_registration')
            ->where($map)
            ->order('add_time desc')
            ->select();
        $data = array();
        foreach ($goods_list as $k=>$goods_info){
            $data[$k]['username'] = $goods_info['username'];
            $data[$k]['userphone'] = $goods_info['userphone'];
            $data[$k]['car_type'] = $goods_info['car_type'];
            $data[$k]['source_title'] = $goods_info['source_title'];
            $data[$k]['source'] = $goods_info['source'];
            $data[$k]['source_type'] = $goods_info['source_type'];
            $data[$k]['add_time'] = date("Y-m-d H:i:s",$goods_info['add_time']);
        }
        //print_r($goods_list);
        //print_r($data);exit;

        foreach ($data as $field=>$v){


            if($field == 'username'){
                $headArr[]='姓名';
            }

            if($field == 'userphone'){
                $headArr[]='手机号';
            }
            if($field == 'car_type'){
                $headArr[]='意向车型';
            }
            if($field == 'source_title'){
                $headArr[]='来源';
            }
            if($field == 'source'){
                $headArr[]='来源标识';
            }
            if($field == 'source_type'){
                $headArr[]='来源类型';
            }
            if($field == 'add_time'){
                $headArr[]='报名时间';
            }
        }
        $filename="报名信息";


        $this->getExcel($filename,$headArr,$data);
    }

    public function source_list()
    {
        $list = M('source')->select();
        $this->assign('list',$list);

        $this->display();
    }

    public function add_source()
    {
        $this->display();
    }

    public function addSource()
    {
        $data = $_POST;

        $data['add_time'] = time();

        $res = M('source')->add($data);
        if($res){
            echo "<script>alert('处理成功'); location.replace(document.referrer);</script>";
        } else {
            echo "<script>alert('处理失败'); location.replace(document.referrer);</script>";
        }
    }

    public function del_source()
    {
        $id = I('id');

        $res = M("source")->where(array('id'=>$id))->delete();

        if($res){
            echo "<script>alert('处理成功'); location.replace(document.referrer);</script>";
        } else {
            echo "<script>alert('处理失败'); location.replace(document.referrer);</script>";
        }
    }

    public function save_source()
    {
        $id = I('id');

        $info = M("source")->where(array('id'=>$id))->find();
        $this->assign('info',$info);

        $this->display();
    }

    public function saveSource()
    {
        $data['source_title'] = I('source_title');
        $data['source_id'] = I('source_id');
        $id = I('id');

        $res = M("source")->where(array('id'=>$id))->save($data);
        if($res){
            echo "<script>alert('处理成功'); location.replace(document.referrer);</script>";
        } else {
            echo "<script>alert('处理失败'); location.replace(document.referrer);</script>";
        }
    }


    public function update_brand()
    {
        $id = I('id');

        $info = M("brand_library")->where(array('id'=>$id))->find();
        $this->assign('info',$info);

        $this->display();

    }
    public function saveBrand()
    {
        $id = I('id');
        $data['brand_name'] = I('brand_name');
        if($_FILES['brand_logo']['name'] != ''){
            $file = $_FILES['brand_logo'];
            $data['brand_logo'] = $this->uploadImgs($file,'brand');
        }
        if($_FILES['zh_logo']['name'] != ''){
            $file1 = $_FILES['zh_logo'];
            $data['zh_logo'] = $this->uploadImgs($file1,'brand');
        }
        $data['sort'] = I('sort');
        $res = M("brand_library")->where(array('id'=>$id))->save($data);
        if($res){
            $this->success('处理成功',U('brand_list'));
        } else {
            echo "<script>alert('处理失败'); location.replace(document.referrer);</script>";
        }
    }

    /**
     * 删除品牌
     */
    public function del_brand()
    {
        $id = I('id');

        $res = M("brand_library")->where(array('id'=>$id))->delete();
        if($res){
            echo "<script>alert('处理成功'); location.replace(document.referrer);</script>";
        } else {
            echo "<script>alert('处理失败'); location.replace(document.referrer);</script>";
        }
    }

    public function sign_info()
    {
        $list = M("sign_info")->order('add_time desc')->select();
        $this->assign('list',$list);

        $this->display();
    }




}

?>