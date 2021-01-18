<?php
namespace SaleMS\Controller;
use Think\Controller;
use Think\Upload;
class DmController extends CommonController {

    /**
     * 上传数据
     */
    public function into_enroll()
    {
        $this->display();

    }

    /**
     * 处理上传数据
     */
    public function uploadExcel()
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
        }else{// 上传成功
            $excelData = $this->getExcelData('./Public/upload/excel/'.$info['excel']['savename'], $active,$info['excel']['ext']);
            $list = array();
            for ($i = 1; $i<count($excelData); $i++){
                $list[$i-1]['name'] = $excelData[$i][1];
                $list[$i-1]['phone'] = $excelData[$i][2];
                $list[$i-1]['area'] = $excelData[$i][3];
                $list[$i-1]['source'] = $excelData[$i][4];
                $list[$i-1]['upload_time'] = time();
            }
            array_merge($list);
            $goods_list = $list;
            $res = M("sms_user_data")->addAll($goods_list);
            if($res){
                echo "<script>alert('处理成功'); location.replace(document.referrer);</script>";
            }else{
                echo "<script>alert('处理失败'); location.replace(document.referrer);</script>";
            }

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
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 数据分配
     */
    public function data_dtb()
    {

        $sale_list = M("sms_user")->where(array('role_id'=>'2'))->select();
        $this->assign('sale_list',$sale_list);
        $sel_name = trim(I('sel_name'));
        $sel_phone = trim(I('sel_phone'));
        if($sel_name===''&&$sel_phone===''){
        }else{
            if($sel_name===''){
                $maps['phone'] = array("EQ",$sel_phone);
                $map_str = '&sel_phone='.$sel_phone;
            }elseif($sel_phone === ''){
                $maps['name'] = array("EQ",$sel_name);
                $map_str = '&sel_name='.$sel_name;
            }else{
                $maps['phone'] = array("EQ",$sel_phone);
                $maps['name'] = array("EQ",$sel_name);
                $map_str = '&sel_phone='.$sel_phone.'&sel_name='.$sel_name;
            }
        }

        $maps['belong_state'] = array('EQ',0);
        $count = M("sms_user_data")->where($maps)->count();
        $Page = new \Extend\Page($count,23);// 实例化分页类 传入总f记录数和每页显示的记录数(25)
        $show = $Page->show($map_str);// 分页显示输出
        $list = M("sms_user_data")
            ->where($maps)
            ->limit($Page->firstRow.','.$Page->listRows)
            ->order(array("upload_time"=>'desc'))
            ->select();

        $this->assign('count',$count);
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->assign('sel_name',$sel_name);
        $this->assign('sel_phone',$sel_phone);
        $this->display();
    }

    /**
     * 处理数据分配
     */
    public function fp_user()
    {
        $user_list = I("user_list");
        if($user_list == ''){
            echo "<script>alert('至少选择一人'); location.replace(document.referrer);</script>";die;
        }
        $sale_id = I('sale_id');
        if($sale_id == ''){
            echo "<script>alert('未选择销售人员'); location.replace(document.referrer);</script>";die;
        }

        $code = $this->addUserBelong($sale_id,$user_list);
        if($code == 1){
            echo "<script>alert('处理成功'); location.replace(document.referrer);</script>";die;
        }else{
            echo "<script>alert('处理失败'); location.replace(document.referrer);</script>";die;
        }

    }

    /**
     * @param $sale_id
     * @param $user_list
     * @return int
     * 
     */
    public function addUserBelong($sale_id,$user_list)
    {
        foreach ($user_list as $v) {
            $arr1 = array();
            $arr1['pid'] = $v;
            $arr1['sale_id'] =$sale_id;
            $arr1['add_time'] = time();
            $labelArr[] = $arr1;
        }
        $maps['id'] = array("IN",$user_list);
        $res = M('sms_offer_record')->addAll($labelArr);
        if($res){
            M("sms_user_data")->where($maps)->save(array('belong_state'=>1));
           return 1;
        }else{
            return 0;
        }

    }


    public function invite_info()
    {
        $admin_id = session('sale_our_adminId');
        $admin_info = M("sms_user")->where(array('id'=>$admin_id))->find();
        if($admin_info['role_id']==3){
            $sale_list = M("sms_user")->where(array('role_id'=>'2','area'=>$admin_info['area']))->select();
        }else{
            $sale_list = M("sms_user")->where(array('role_id'=>'2'))->select();
        }

        $this->assign('sale_list',$sale_list);

        $sale_id = I('sale_id');
        $invite_state = I('invite_state');
        if($sale_id===''&&$invite_state===''){
        }else{
            if($sale_id===''){
                if($invite_state == 1){
                    $maps['sms_offer_record.update_time'] = array("GT",0);
                }else{
                    $maps['sms_offer_record.update_time'] = array("EQ",0);
                }

                $map_str = '&invite_state='.$invite_state;
            }elseif ($invite_state === ''){
                $maps['sms_offer_record.sale_id'] = array("EQ",$sale_id);
                $map_str = '&sale_id='.$sale_id;
            }else{
                if($invite_state == 1){
                    $maps['sms_offer_record.update_time'] = array("GT",0);
                }else{
                    $maps['sms_offer_record.update_time'] = array("EQ",0);
                }
                $maps['sms_offer_record.sale_id'] = array("EQ",$sale_id);
                $map_str = '&invite_state='.$invite_state.'&sale_id='.$sale_id;
            }
        }
        $maps['belong_state'] = array("EQ",1);
        $count = M("sms_offer_record")->where($maps)->count();
        $Page = new \Extend\Page($count,50);// 实例化分页类 传入总f记录数和每页显示的记录数(25)
        $show = $Page->show($map_str);// 分页显示输出
        $list = M("sms_offer_record")
            ->join("left join sms_user_data on sms_offer_record.pid=sms_user_data.id")
            ->join("left join sms_user on sms_offer_record.sale_id=sms_user.id")
            ->where($maps)
            ->limit($Page->firstRow.','.$Page->listRows)
            ->order(array("update_time"=>'desc','sms_offer_record.id'=>'desc'))
            ->field('sms_offer_record.*,sms_user.user_name,sms_user_data.name,sms_user_data.phone,sms_user_data.area')
            ->select();

        $this->assign('sale_id',$sale_id);
        $this->assign('invite_state',$invite_state);
        $this->assign('count',$count);
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->display();

    }


    public function invite_count()
    {
        $last_time = strtotime(date("Y-m-d",strtotime("-1 day")));
        $last_end_time = strtotime(date("Y-m-d",time()));
        //昨日邀约数据
        $admin_id = session('sale_our_adminId');
        $admin_info = M("sms_user")->where(array('id'=>$admin_id))->find();
        if($admin_info['role_id']==3){
            $last_list = M("sms_invite_count")->where(array('record_date'=>$last_time,'area'=>'张家港'))->select();
        }else{
            $last_list = M("sms_invite_count")->where(array('record_date'=>$last_time))->select();
        }
        $last_fir = M("sms_invite_count")
            ->where(array('record_date'=>$last_time))
            ->field('sum(yy_num) as num,sale_name')
            ->find();
        $week_time = strtotime(date("Y-m-d",strtotime("-1 week")));
        $maps['record_date'] = array("BETWEEN",array($week_time,$last_end_time));
        if($admin_info['role_id']==3){
            $maps['area'] = array("EQ",'张家港');
        }
        //七日邀约数据
        $week_list = M("sms_invite_count")->where($maps)->select();
        //七日邀约冠军
        $week_fir = M("sms_invite_count")
            ->where($maps)
            ->field('sum(yy_num) as num,sale_name')
            ->group('sale_name')
            ->order('num desc')
            ->find();
        //七日邀约总数
        $week_all_num = 0;
        for($i=0; $i<count($week_list);$i++){
            $week_all_num = $week_all_num+$week_list[$i]['yy_num'];
        }
        //七日A类总和
        $new_maps['update_time'] = array("BETWEEN",array($week_time,$last_end_time));
        $new_maps['client_level'] = array("EQ",'A');
        $a_num = M('sms_offer_record')
            ->where($new_maps)
            ->count();

       $this->assign('a_num',$a_num);
       $this->assign('last_fir',$last_fir);
       $this->assign('week_fir',$week_fir);
       $this->assign('week_all_num',$week_all_num);
       $this->assign('last_list',$last_list);
       $this->assign('week_list',$week_list);
       $this->display();

    }

    public function handleLastArr($last_time,$last_end_time)
    {

        $maps['sms_offer_record.add_time'] = array("BETWEEN",array($last_time,$last_end_time));
        $list_fp = M("sms_offer_record")
            ->join("left join sms_user on sms_offer_record.sale_id=sms_user.id")
            ->where($maps)
            ->field('count(*) AS num,user_name,sale_id')
            ->group('sale_id')
            ->select();
        $maps1['update_time'] = array("GT",0);
        $list_yy = M("sms_offer_record")
            ->join("left join sms_user on sms_offer_record.sale_id=sms_user.id")
            ->where($maps1)
            ->field('count(*) AS num,user_name,sale_id')
            ->group('sale_id')
            ->select();
        for($i = 0;$i<count($list_fp);$i++){
            $list_fp[$i]['yy_num'] = 0;
            for($j=0;$j<count($list_yy);$j++){
                if($list_fp[$i]['sale_id'] == $list_yy[$j]['sale_id']){
                    $list_fp[$i]['yy_num'] = $list_yy[$j]['num'];
                }
            }
            $list_fp[$i]['wyy_num'] = $list_fp[$i]['num']-$list_fp[$i]['yy_num'];
            $list_fp[$i]['count_date'] = date("Y-m-d",time());
        }
        return $list_fp;
    }

}