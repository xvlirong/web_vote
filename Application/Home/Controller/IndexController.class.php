<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        echo phpinfo();die;
        echo M("rv_users")->getField('username');
    }
}