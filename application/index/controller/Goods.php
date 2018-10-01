<?php
/**
 *  商品控制器
 * User: @ 若雨
 * Date: 2018/9/18
 * Time: 14:23
 */

namespace app\index\controller;

use app\admin\model\Goods as GoodesModel;
use think\Db;
use think\facade\Request;
use app\admin\model\Category;
use app\admin\model\User;
use app\admin\model\Order as OrderModel;
use app\admin\model\UserAddress;
class Goods extends BaseController
{
    public function index($id){
        // 获取商品详细信息
        $goodsInfo = GoodesModel::getGoodsInfo($id);
        // 获取
        $this->assign([
            'goodsInfo' =>  $goodsInfo,
        ]);
        return $this->fetch();
    }

    // 传入商品id获取商品库存
    public function getGoodsStock(){
       $goods_id = Request::post('goods_id');
       return json(GoodesModel::getGoodsStock($goods_id));
    }

    // 搜索
    public function search($key=''){
        $goodsInfo = GoodesModel::getKeyGoodsInfo($key);
        $count = $goodsInfo->count();
        $id = 0;
        $this->assign([
            'goods'     =>  $goodsInfo,
            'count'     =>  $count,
            'id'        =>  $id,
            'title'     =>  $key
        ]);
        return $this->fetch();
    }

    // 立即购买
    public function immediately(){
        $data = Request::post();
        // 检查是否超过库存量
        $kc = GoodesModel::getGoodsStock($data['g_id']);
        if($kc<$data['quantity']){
            $this->assign([
                'msg'=>'库存不足',
                'good_id'   =>  $data['g_id']
            ]);
           return $this->fetch('ju');
        }
        // 获取用户的收获地址
        $userAddress = User::getUserAddress(session('user.id'));
        $goods = GoodesModel::getGoodsInfo($data['g_id']);
        $sum_price = 0;
        $sum_price += $data['quantity'] *$goods['shop_price'];
        $is = count($goods);
        $is_add = count($userAddress);
        if($is == 0){
            return $this->redirect('/');
        }
        $this->assign([
            'goods'        =>    $goods,
            'sum_price'     =>   $sum_price,
            'is'            =>   $is,
            'userAddress'   =>  $userAddress,
            'is_add'        =>  $is_add,
            'num'           =>$data['quantity']
        ]);
        return $this->fetch('all');
    }
    public function order(){
        $data = Request::post();
        //判断是否有收获地址
        $userAddress = User::getUserAddress(session('user.id'))->count();
        if(!$userAddress){
            $state = [
                'code'  =>  -1,
                'msg'   =>  '请添加收获地址后再尝试下单'
            ];
            return json($state);
        }
        $kc = GoodesModel::getGoodsStock($data['goods_id']);
        if($kc<$data['num']){
            $state = [
                'code'  =>  -5,
                'msg'   =>  '库存量不足'
            ];
            return json($state);
        }
        $goods = GoodesModel::getGoodsInfo($data['goods_id']);
        $userCart = [];
        $userCart[]['u_id'] = session('user.id');
        $userCart[0]['g_id'] = $data['goods_id'];
        $userCart[0]['number'] = $data['num'];
        $userCart[0]['goods'] = $goods->toArray();
        // 订单号
        $order['order_id'] = get_order();
        // 获取用户收获地址
        $order['address'] = json_encode(UserAddress::get($data['addressID']));
        // 获取商品总价
        $sum_price = 0;
        $sum_price += $data['num'] * $goods['shop_price'];

        $order['payment'] = $sum_price;
        $order['user_id'] = session('user.id');
        $order['goods_item'] = json_encode($userCart);
        $order['close_time'] = time() + 10;
        $info = Db::table('tp_order')->insertGetId($order);
        if($info){
            $orderId = Db::table('tp_order')->where(['id'=>$info])->value('order_id');
            // 减少库存量
            $kc = GoodesModel::getGoodsStock($data['goods_id']);
            $kc = $kc - $data['num'];
            Db::table('tp_goods')->where(['id'=>$data['goods_id']])->update(['stock'=>$kc]);
            $state = [
                'code'    =>  1,
                'orderId' => $orderId
            ];
            return json($state);
        }
    }
}