<?php

namespace Admin\Controller;
use Extend\Page;
use Think\Controller;
use Think\Upload;
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
        $act_list = M("activity")->order(array('id'=>'desc'))->select();
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
        $list = M("brand_library")->order(array('sort'=>'asc'))->select();
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
        $Page = new \Extend\Page($count,100);
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
            $data[$k]['area'] = $goods_info['mobile_province'].'+'.$goods_info['mobile_area'];
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
            if($field == 'area'){
                $headArr[]='地区';
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

    public function count_sign_info()
    {
        $id = I('id');

        $this->assign('id',$id);

        $this->display();
    }

    public function count_arrival_info()
    {
        $id = I('id');

        $this->assign('id',$id);

        $this->display();
    }


    //到场统计
    public function countArrUser()
    {
        $id = I('id');
        $list = M("act_registration")
            ->where(array('act_id'=>$id,'arrival_status'=>2))
            ->group('mobile_province')
            ->field('mobile_province,count(id) as num')
            ->order('num desc')
            ->select();
        for($i=0;$i<count($list);$i++){
            if($list[$i]['mobile_province'] == null){
                $list[$i]['mobile_province'] = ' ';
            }
            $str['province'][] = $list[$i]['mobile_province'];

            $str['num'][]= ($list[$i]['num']);
        }


        echo json_encode($str);
    }


    //报名统计
    public function countUser()
    {
        $id = I('id');
        $list = M("act_registration")
            ->where(array('act_id'=>$id,'arrival_status'=>0))
            ->group('mobile_province')
            ->field('mobile_province,count(id) as num')
            ->order('num desc')
            ->select();
        for($i=0;$i<count($list);$i++){
            if($list[$i]['mobile_province'] == null){
                $list[$i]['mobile_province'] = ' ';
            }
            $str['province'][] = $list[$i]['mobile_province'];

            $str['num'][]=intval($list[$i]['num']);
        }


        echo json_encode($str);
    }

    public function countSignUserPie()
    {
        $id = I('id');
        $all_num = M("act_registration")->where(array('act_id'=>$id))->count();

        $list = M("act_registration")
            ->where(array('act_id'=>$id,'arrival_status'=>0))
            ->group('mobile_province')
            ->field('mobile_province,count(id) as num')
            ->order('num desc')
            ->select();
        for($i=0;$i<count($list);$i++){
            if($list[$i]['mobile_province'] == null){
                $list[$i]['mobile_province'] = ' ';
            }
            $str['list'][$i]['name'] = $list[$i]['mobile_province'];
            $str['list'][$i]['y'] =  floatval(sprintf("%.2f", $list[$i]['num']/$all_num*100));

        }


       echo json_encode($str);
    }

    public function countSourceUserPie()
    {
        $id = I('id');
        $all_num = M("act_registration")->where(array('act_id'=>$id))->count();

        $list = M("act_registration")
            ->where(array('act_id'=>$id,'arrival_status'=>0))
            ->group('source_title')
            ->field('source_title,count(id) as num')
            ->order('num desc')
            ->select();
        for($i=0;$i<count($list);$i++){
            if($list[$i]['source_title'] == null){
                $list[$i]['source_title'] = ' ';
            }
            $str['list'][$i]['name'] = $list[$i]['source_title'];
            $str['list'][$i]['y'] =  floatval(sprintf("%.2f", $list[$i]['num']/$all_num*100));

        }


        echo json_encode($str);
    }

    public function countArrUserPie()
    {
        $id = I('id');
        $all_num = M("act_registration")->where(array('act_id'=>$id))->count();

        $list = M("act_registration")
            ->where(array('act_id'=>$id,'arrival_status'=>2))
            ->group('mobile_province')
            ->field('mobile_province,count(id) as num')
            ->order('num desc')
            ->select();
        for($i=0;$i<count($list);$i++){
            if($list[$i]['mobile_province'] == null){
                $list[$i]['mobile_province'] = ' ';
            }
            $str['list'][$i]['name'] = $list[$i]['mobile_province'];
            $str['list'][$i]['y'] =  floatval(sprintf("%.2f", $list[$i]['num']/$all_num*100));

        }


        echo json_encode($str);
    }

    public function countSourceArrPie()
    {
        $id = I('id');
        $all_num = M("act_registration")->where(array('act_id'=>$id))->count();

        $list = M("act_registration")
            ->where(array('act_id'=>$id,'arrival_status'=>2))
            ->group('source_title')
            ->field('source_title,count(id) as num')
            ->order('num desc')
            ->select();
        for($i=0;$i<count($list);$i++){
            if($list[$i]['source_title'] == null){
                $list[$i]['source_title'] = ' ';
            }
            $str['list'][$i]['name'] = $list[$i]['source_title'];
            $str['list'][$i]['y'] =  floatval(sprintf("%.2f", $list[$i]['num']/$all_num*100));

        }


        echo json_encode($str);
    }

    public function into_enroll()
    {
        $this->display();

    }

    public function uploadExcel()
    {
        set_time_limit(0);
        $config  = C('UPLOAD_CONFIG');
        $config['exts'] = array("xls","xlsx");
        $config['savePath'] = 'upload/excel/';
        $upload = new Upload($config);
        // 上传文件
        $active = I('active');
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
        }else{// 上传成功
            $excelData = $this->getExcelData('./Public/upload/excel/'.$info['excel']['savename'], $active,$info['excel']['ext']);
            $list = array();
            for ($i = 1; $i<count($excelData); $i++){
                $list[$i]['name'] = $excelData[$i][1];
                $list[$i]['phone'] = $excelData[$i][2];
                $list[$i]['area'] = $this->getMobileInfo($excelData[$i][2]);
            }
           array_merge($list);
           $goods_list = $list;

            $data = array();
            foreach ($goods_list as $k=>$goods_info){
                $data[$k]['name'] = $goods_info['name'];
                $data[$k]['phone'] = $goods_info['phone'];
                $data[$k]['area'] = $goods_info['area'];
            }
            foreach ($data as $field=>$v){
                if($field == 'name'){
                    $headArr[]='姓名';
                }

                if($field == 'phone'){
                    $headArr[]='手机号';
                }
                if($field == 'area'){
                    $headArr[]='地区';
                }

            }
            $filename="地区转换";


            $this->getExcel($filename,$headArr,$data);


        }


    }

    /**
     * 获取Excel数据
     * @param $file_name
     * @param string $exts
     * @return array
     */
    private function getExcelData($file_name,$active , $exts = 'xlsx')
    {
        set_time_limit(0);
        ini_set("memory_limit", "1024M");
        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能inport导入
        //创建PHPExcel对象，注意，不能少了\
        vendor("PHPExcel.PHPExcel");
        //创建PHPExcel对象，注意，不能少了\
        $PHPExcel=new \PHPExcel();

        if($exts=="xls")
        {
            import("Org.Util.PHPExcel.Reader.Excel5");
            $PHPReader=new \PHPExcel_Reader_Excel5();
        }
        else if($exts=="xlsx")
        {
            import("Org.Util.PHPExcel.Reader.Excel2007");
            $PHPReader=new \PHPExcel_Reader_Excel2007();
        }
        //载入文件
        $PHPExcel=$PHPReader->load($file_name);
        //获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
        $currentSheet=$PHPExcel->getSheet(0);

        //获取总列数
        $allColumn=$currentSheet->getHighestColumn();

        //获取总行数
        $allRow=$currentSheet->getHighestDataRow();


        $excelData = array();
        //循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始
        for($currentRow=1;$currentRow<=$allRow;$currentRow++){
            //从哪列开始，A表示第一列
            $i = 0;
            for($currentColumn='A';$currentColumn<=$allColumn;$currentColumn++){
                //数据坐标
                $address=$currentColumn.$currentRow;
                //读取到的数据，保存到数组$arr中
                $rowArr = $currentSheet-> getCell($address)-> getValue();
                $excelData[$currentRow - 1][++$i] = $rowArr;
            }
        }
        $count = count($excelData);
        for ($i = 0; $i < $count; $i++) {
            $str = implode('',$excelData[$i]);
            if ($str == '' || $str == null) {
                unset($excelData[$i]);
            } else {
                $excelData[$i];
            }
        }

        $excelData = array_values($excelData);
        return $excelData;
    }

    /**
     * 手机号地区查询接口
     * @param $mobile
     * @return mixed
     */
    public function getMobileInfo($mobile)
    {
        if (!preg_match("/^1[34578]\d{9}$/", $mobile)) {
            //return '请输入正确手机号码！';
            $info['prov'] = '未知';
            $info['city'] = '未知';
        }else{
            $phone_json = file_get_contents('http://sp0.baidu.com/8aQDcjqpAAV3otqbppnN2DJv/api.php?query={'.$mobile.'}&resource_id=6004&ie=utf8&oe=utf8&format=json');
            $phone_array = json_decode($phone_json,true);
            $phone_info = array();
            $phone_info['mobile'] = $mobile;
            $phone_info['type'] = $phone_array['data'][0]['type'];
            $phone_info['location'] = $phone_array['data'][0]['prov'].$phone_array['data'][0]['city'];
            $prov = $phone_array['data'][0]['prov'];
            $city = $phone_array['data'][0]['city'];
            if($prov == ''){
                return $city;
            }else{
                return $prov."+".$city;

            }
        }
    }

    /**
     * 定时计划列表
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function plan_info(){
        $list = M("act_plan")
            ->join("left join activity on act_plan.pid=activity.id")
            ->field('act_plan.*,activity.title,end_time')
            ->order('act_plan.id desc')
            ->select();
        $this->assign('list',$list);
        $this->display();
    }

    /**
     * 定时计划任务修改
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function save_plan()
    {
        $id = I('id');
        $map['end_time'] = array("GT",time());
        $act_list = M("activity")->where($map)->field('id,title')->select();
        $this->assign('act_list',$act_list);

        $info = M("act_plan")->where(array('id'=>$id))->find();
        $this->assign('info',$info);

        $this->display();
    }

    /**
     * 定时计划任务修改
     */
    public function savePlan()
    {
        $id = I('id');
        $data['plan_name'] = I('plan_name');
        $data['start_num'] = I('start_num');
        $data['end_num'] = I('end_num');
        $data['state'] = I('state');

        $res = M("act_plan")->where(array('id'=>$id))->save($data);
        if($res){
            echo "<script>alert('处理成功'); location.replace(document.referrer);</script>";
        } else {
            echo "<script>alert('处理失败'); location.replace(document.referrer);</script>";
        }

    }

    /**
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * 删除定时计划
     */
    public function del_plan()
    {
        $id = I('id');
        $res = M("act_plan")->where(array('id'=>$id))->delete();
        if($res){
            echo "<script>alert('处理成功'); location.replace(document.referrer);</script>";
        } else {
            echo "<script>alert('处理失败'); location.replace(document.referrer);</script>";
        }
    }

    /**
     * 新增计划任务
     */
    public function add_plan()
    {
        $now_time = time();
        //活动列表
        $maps['end_time'] = array("GT",$now_time);
        $act_list = M("activity")->where($maps)->field('id,title')->select();
        $this->assign('act_list',$act_list);

        $this->display();

    }

    public function addPlan()
    {
        $data['pid'] = I('pid');
        $data['plan_name'] = I('plan_name');
        $data['start_num'] = I('start_num');
        $data['end_num'] = I('end_num');
        $res = M("act_plan")->add($data);

        if($res){
            echo "<script>alert('处理成功'); location.replace(document.referrer);</script>";
        } else {
            echo "<script>alert('处理失败'); location.replace(document.referrer);</script>";
        }
    }

    public function count_area()
    {
        $this->display();
    }

    public function handleCountArea()
    {
        $config  = C('UPLOAD_CONFIG');
        $config['exts'] = array("xls","xlsx");
        $config['savePath'] = 'upload/excel/';
        $upload = new Upload($config);
        // 上传文件
        $active = I('active');
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
        }else {// 上传成功
            $excelDatas = $this->getExcelData('./Public/upload/excel/' . $info['excel']['savename'], $active, $info['excel']['ext']);
            $excelData = array_column($excelDatas,1);
            for ($i = 1; $i<count($excelData); $i++){
              preg_replace('# #','',$excelData[$i]);
              $arr = explode('+',$excelData[$i]);
              $province[] = $arr[0];
            }
            $area_data = array_count_values($excelData);
            $province_data = array_count_values($province);

            $all_num = count($excelData);
            $a = 0;
            //省份统计处理
            foreach ($province_data as $key=>$v){
               $new_province [$a]['省份'] = $key;
               $new_province [$a]['数量'] = $v;

               $ratio = substr(bcdiv($v,$all_num,4),0,6)*100;
               $new_province [$a]['省份占比'] = $ratio;
               $a++;
            }

            $sort_pro = array_column($new_province,'数量');

            array_multisort($sort_pro, SORT_DESC, $new_province);
            $new_province = array_merge($new_province);
            //地区统计处理
            foreach ($area_data as $key=>$v){
                $new_area [$a]['地区'] = $key;
                $new_area [$a]['地区数量'] = $v;

                $ratio = substr(bcdiv($v,$all_num,4),0,6)*100;
                $new_area [$a]['地区占比'] = $ratio;
                $a++;
            }
            $sort_area = array_column($new_area,'地区数量');
            array_multisort($sort_area, SORT_DESC, $new_area);
            $new_area = array_merge($new_area);

            $pro_num = count($new_province);
            $area_num = count($new_area);
            if($area_num>50){
                $area_num = 50;
            }
            for($i=0; $i<$area_num;$i++){
              $inum = $i+1;
              if($inum>$pro_num){
                  $list[] = $new_area[$i];
              }else{
                  $list[] = array_merge($new_area[$i],$new_province[$i]);
              }
            }
            $data = array();
            foreach ($list as $k=>$goods_info){
                $data[$k]['province'] = $goods_info['省份'];
                $data[$k]['ratio'] = $goods_info['省份占比'];
                $data[$k]['area'] = $goods_info['地区'];
                $data[$k]['area_ratio'] = $goods_info['地区占比'];
            }
            foreach ($data as $field=>$v){
                if($field == 'province'){
                    $headArr[]='省份';
                }

                if($field == 'ratio'){
                    $headArr[]='省份占比(%)';
                }
                if($field == 'area'){
                    $headArr[]='地区';
                }
                if($field == 'area_ratio'){
                    $headArr[]='地区占比(%)';
                }

            }
            $filename="地区统计";


            $this->getExcel($filename,$headArr,$data);
        }
    }

    public function upload_user()
    {
        //活动列表
        $act_list = M("activity")->order('id desc')->field('id,title')->select();
        $this->assign('act_list',$act_list);

        $this->display();
    }

    public function uploadUserExcel()
    {
        $act_id = I('pid');
        set_time_limit(0);
        $config  = C('UPLOAD_CONFIG');
        $config['exts'] = array("xls","xlsx");
        $config['savePath'] = 'upload/excel/';
        $upload = new Upload($config);
        // 上传文件
        $active = I('active');
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
        }else{// 上传成功
            $excelData = $this->getExcelData('./Public/upload/excel/'.$info['excel']['savename'], $active,$info['excel']['ext']);
            $list = array();
            for ($i = 1; $i<count($excelData); $i++){
                $list[$i]['username'] = $excelData[$i][1];
                $list[$i]['userphone'] = $excelData[$i][2];
                preg_replace('# #','',$excelData[$i][3]);
                $arr = explode('+',$excelData[$i][3]);
                $list[$i]['mobile_province'] = $arr[0];
                $list[$i]['mobile_area'] = $arr[1];
                $list[$i]['source_title'] = $excelData[$i][4];
                $add_time = $excelData[$i][5];
                $list[$i]['add_time'] = strtotime("$add_time");
                $list[$i]['act_id'] = $act_id;
            }
            $new_list = array_merge($list);
            M('act_registration')->addAll($new_list);
        }

    }

    public function saveSignState()
    {
        $pid = I('pid');
        //所有到店用户
        $all_sign = M("act_registration")->where(array('arrival_status'=>1,'act_id'=>$pid))->field('id,userphone')->select();
        $use_sign = array_column($all_sign,'userphone');

        //所有报名用户

        $all_user = M("act_registration")->where(array('arrival_status'=>0,'act_id'=>$pid))->field('id,userphone')->select();
        $use_user = array_column($all_sign,'userphone');
        $result = array_intersect($use_sign, $use_user);
        // 重新索引
        $result = array_values($result);

        $use_phone = implode(',',$result);

        $maps['userphone'] = array("IN",$use_phone);
        $maps['arrival_status'] = array("EQ",0);
        $maps['act_id'] = array("EQ",$pid);

        $res =  M("act_registration")->where($maps)->save(array('arrival_status'=>2));
        if($res){
            echo 1;
        }



    }


}

?>