<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\facade\Route;
// 分类
Route::get('category/:id','index/categorys/index',['ext'=>'html|htm'],['id'=>'\d{1,5}']);
// 栏目
Route::get('column/:id','index/Column/index',['ext'=>'html|htm'],['id'=>'\d{1,5}']);

//商品
Route::get('goods/:id','index/Goods/index',['ext'=>'html|htm'],['id'=>'\d{1,5}']);

// 搜索
Route::get('search','index/Goods/search',['ext'=>'html|htm']);

// 用户注册
Route::get('reg','user/Login/register',['ext'=>'html|htm']);
// 用户登陆
Route::get('login','user/Login/login',['ext'=>'html|htm']);


// 用户管理

Route::get('user/member','user/user/member',['ext'=>'html|htm']);
// 显示购物车
Route::get('user/add_address','user/user/addAddress',['ext'=>'html|htm']);
Route::get('user/address','user/user/Address',['ext'=>'html|htm']);

// 购物车
Route::get('cart','user/cart/info',['ext'=>'html|htm']);

// 下单
Route::get('all','user/order/all',['ext'=>'html|htm']);
Route::get('show_order/:id','user/order/showOrder',['ext'=>'html|htm']);

// 订单详情

Route::get('showOrder/:id','user/order/show_Order',['ext'=>'html|htm']);

Route::post('now','index/goods/immediately',['ext'=>'html|htm']);

// 管理员登陆
Route::get('admin/login','admin/login/login',['ext'=>'html|htm']);
Route::get('admin/lou','admin/login/outLogin',['ext'=>'html|htm']);
