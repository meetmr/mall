<?php
/**
 *  订单管理
 * User: @ 若雨
 * Date: 2018/9/28
 * Time: 14:16
 */

namespace app\admin\controller;

use app\admin\model\Order as OrderModel;
use think\Db;
use think\facade\Request;
use app\admin\model\Logistics;

class Order extends BaseController
{
    public function info($status = 0){
        if($status == 0){
            $orders = OrderModel::order('id desc')->paginate(15);
        }else{
            $orders = OrderModel::order('id desc')->where(['status'=>$status])->paginate(15);
        }

        $this->assign([
            'orderss'   =>  $orders
        ]);
        return $this->fetch();
    }
    // 订单详情
    public function orderInfo($id){
        $order = OrderModel::where(['id'=>$id])->find();
        $this->assign([
            'order'   =>  $order
        ]);
        return $this->fetch('info-order');
    }
    public function updateState($id){
        $id = intval($id);
        $wlInfo = Logistics::where(['o_id'=>$id])->find();
        $count = count($wlInfo);
        if(!$wlInfo){
            $wlInfo['name'] = '';
            $wlInfo['order_number'] = '';
        }
        $state = OrderModel::where(['id'=>$id])->value('status');
        $this->assign([
            'state'      =>  $state,
            'id'         =>  $id,
            'wlInfo'     =>  $wlInfo,
            'count'     => $count
        ]);
        return $this->fetch('update-state');
    }
    // 1、更改订单状态
    public function sOrder(){
        if(Request::isAjax()){
            $data = Request::post();
            $info = OrderModel::update(['status'=>$data['s']],['id'=>$data['id']]);
            if($info){
                return json(1);
            }else{
                return json(0);
            }
        }
    }
    // 2、更改订单状态
    public function xOrder(){
        if(Request::isAjax()){
            $data = Request::post();
            $info = OrderModel::update(['status'=>$data['s']],['id'=>$data['id']]);
            if($info){
                if($data['count'] == 0){
                    Logistics::create(['o_id'=>$data['id'],'name'=>$data['gs'],'order_number'=>$data['ddhx']]);
                }
                return json(1);
            }else{
                return json(0);
            }
        }
    }
    public function aa(){
       dd(getWuLiuInfo('yuantong','801722946991791469'));
    }
}