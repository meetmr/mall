<?php
namespace app\admin\controller;

class Index extends BaseController
{
    protected $beforeActionList = [
        'isLogin',
    ];

    //后台主页视图
    public function index()
    {
        return $this->fetch();
    }
    public function welcome(){
        return $this->fetch();
    }
}
