<?php
/**
 * 管理员登陆
 * User: @ 若雨
 * Date: 2018/10/7
 * Time: 11:28
 */

namespace app\admin\controller;

use app\admin\model\Admin;
use think\Controller;
use think\facade\Request;
use think\facade\Session;

class Login extends Controller
{
    public function login(){
        return $this->fetch();
    }
    public function handleLogin(){
        if(Request::isAjax()){
            $data = Request::post();
            $info = Admin::where(['username'=>$data['username']])->where(['password'=>$data['password']])->find();
            if(!$info){
                return json([
                    'code'  => 0,
                    'msg'   => '用户或者密码错误'
                ]);
            }
            session('admin',$info);
            return json([
                'code'  => 1,
            ]);
        }
    }

    // 退出登陆
    public function outLogin(){
        session('admin',null);
        Session::clear();
        $this->redirect('admin/login/login');
    }
}