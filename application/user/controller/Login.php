<?php
/**
 * 用户注册与登陆
 * User: @ 若雨
 * Date: 2018/9/18
 * Time: 20:23
 */

namespace app\user\controller;


use think\Controller;
use think\facade\Request;
use app\admin\model\User;
use think\facade\Session;
class Login extends Controller
{
    public function register(){
        return $this->fetch();
    }
    public function _return($info){
        if($info){
            return json([
                'errorCode'=>1
            ]);
        }else{
            return json([
                'errorCode'=>0
            ]);
        }
    }
    // 处理登陆
    public function HandleRegister(){
        if(Request::isAjax()){
            $data = Request::post();
            // 定义用户名正则
            if($data['upwd'] !== $data['upwd_2']){
                $state = [
                    'error' => 0,
                    'msg'=> '两次输入的密码不匹配'
                ];
                return json($state);
            }
            // 验证用户名是否被注册
            if(User::seeUserName($data['uname'])){
                $state = [
                    'error' => 0,
                    'msg'=> '用户名已注册'
                ];
                return json($state);
            }
            // 验证邮箱是否被注册
            if(User::seeUserEmail($data['email'])){
                $state = [
                    'error' => 0,
                    'msg'=> '邮箱已注册'
                ];
                return json($state);
            }

            // 入库操作
            $datas['username'] = $data['uname'];
            $datas['password'] = md5($data['upwd']);
            $datas['email'] = $data['email'];
            $datas['reg_time'] = time();
            $info = User::create($datas);
            if($info){
                $state = [
                    'error' => 1,
                    'msg'=> '注册成功'
                ];
                return json($state);
            }else{
                $state = [
                    'error' => 1,
                    'msg'=> '注册失败'
                ];
                return json($state);
            }
        }
        return 0;
    }

    // 登陆
    public function login(){
        return $this->fetch();
    }

    public function HandleLogin(){
        // 处理登陆
        if(Request::isAjax()){
            $data = Request::post();
            $user = User::loinUser($data['uname'],$data['upwd']);
            // 判断用户名是否注册
            if(!User::seeUserName($data['uname'])){
                $state = [
                    'error' => 0,
                    'msg'=> '用户名没有注册'
                ];
                return json($state);
            }

            // 判断密码是否正确
            if(!$user){
                $state = [
                    'error' => 0,
                    'msg'=> '密码错误'
                ];
                return json($state);
            }
            // 登陆成功
            session('user',$user);  // 写入session
            $state = [
                'error' => 1,
                'msg'=> '登陆成功'
            ];
            return json($state);
        }
    }

    // 退出登陆
    public function logout(){
        // 清除session
        Session::clear();
        $this->redirect('/');
    }
}