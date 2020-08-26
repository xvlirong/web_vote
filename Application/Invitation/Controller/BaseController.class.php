<?php
namespace Invitation\Controller;
use Think\Controller;
class BaseController extends Controller
{
    //用户id
    public $userid;

    public function __construct()
    {
        parent::__construct();

        if (!session('sale_our_saleId')) {
            $cookie_adminid = cookie('sale_id');
            if($cookie_adminid){
                session('sale_our_saleId',$cookie_adminid);
                return;
            }else{
                $url = U('Login/login');
                echo "<script>window.top.location.href='{$url}';</script>";
                die;
            }
        }

    }
}