<?php
/**
 * Created by PhpStorm.
 * User: @ 若雨
 * Date: 2018/9/22
 * Time: 19:42
 */

namespace app\user\controller;

use app\index\controller\BaseController;
use app\admin\model\User;
use app\admin\model\Cart as CartModel;
use think\facade\Request;
use app\admin\Model\Cart;
use app\admin\model\Goods;
use app\admin\model\UserAddress;
use app\admin\model\Order as OrderModel;
use Endroid\QrCode\QrCode;
use think\Db;

class Order extends BaseController
{
    public function initialize(){
        parent::initialize(); // TODO: Change the autogenerated stub
        $this->isLogin();
    }
    public function all(){
        // 获取用户的收获地址
        $userAddress = User::getUserAddress(session('user.id'));
        // 从购物车中获取商品
        $data = CartModel::getCart();
        $userCart = $data['userCart'];
        $sum_price = $data['sum_price'];
        $is = count($userCart);
        $is_add = count($userAddress);
        if($is == 0){
            return $this->redirect('/');
        }
        $this->assign([
            'userCart'      =>   $userCart,
            'sum_price'     =>   $sum_price,
            'is'            =>   $is,
            'userAddress'   =>  $userAddress,
            'is_add'       =>  $is_add
        ]);
        return $this->fetch();
    }

    // 生成订单
    /*
     * 1、接收到前台传来的收获地址ID、从数据库中找出对于的地址详情、并转换为json数据
     * 2、生产订单号
     * 3、从购物车库拿去对应的数据
     * 4、整合好数据入库
     * 5、清空购物车
     *
     * **/
    public function order(){
        if(Request::isAjax()){
            $data = Request::post();
            if(!$data){
                $state = [
                    'code'  =>  -5,
                    'msg'   =>  '请选择一个收获地址！'
                ];
                return json($state);
            }
            //判断是否有收获地址
            $userAddress = User::getUserAddress(session('user.id'))->count();
            if(!$userAddress){
                $state = [
                    'code'  =>  -1,
                    'msg'   =>  '请添加收获地址后再尝试下单'
                ];
                return json($state);
            }
            // 从数据库中拿去加入购物车的商品
            $user_id = session('user.id');
            $userCart = Cart::where(['u_id'=>$user_id])->select();

            // 判断是否有库存
            foreach ($userCart as $item){
                $kc = Goods::getGoodsStock($item['g_id']);
                if($kc < $item['number']  || $kc ===0 || $item['number'] == 0){
                    $state = [
                        'code'  =>  -5,
                        'msg'   =>  '某件商品库存量不足、请检查后重新尝试下单'
                    ];
                    return json($state);
                }
            }
            if(!$userCart->count()){
                $state = [
                    'code'  =>  -2,
                    'msg'   =>  '下单失败'
                ];
                return json($state);
            }
            foreach ($userCart as $item){
                $item['goods'] = Goods::getGoodsInfo($item['g_id'])->toArray();
            }

            // 订单号
            $order['order_id'] = get_order();
            // 获取用户收获地址
            $order['address'] = json_encode(UserAddress::get($data['addressID']));
            // 获取商品总价
            $sum_price = 0;
            foreach ($userCart as $item){
                $sum_price += $item['number'] * $item['goods']['shop_price'];
            }
            $order['payment'] = $sum_price;
            $order['user_id'] = session('user.id');
            $order['goods_item'] = json_encode($userCart);
            $order['close_time'] = time() + 1800;
            $info = OrderModel::create($order);
            if($info){
                Cart::where(['u_id'=>$user_id])->delete();
                $state = [
                    'code'    =>  1,
                    'orderId' =>  $info['order_id']
                ];
                return json($state);
            }

        }
    }
    public function showOrder($id){
        $order = OrderModel::where(['order_id'=>intval($id)])->where(['status'=>1])->find();
       if(!$order){
           return $this->redirect('/');
       }
        $this->assign([
           'order'  =>  $order
        ]);
        return $this->fetch('show-order');
    }
    //生成微信支付二维码
    public function wxewm($orderCode = 151515){
        $payPlus = PAY_PLUS.'pay/wxpay/';
        include ($payPlus.'index2.php');
        $qrCode = new QRcode();
        $obj = new \WeiXinPay2();
        $order = OrderModel::where(['id'=>$orderCode])->where(['status'=>1])->find();
        if(!$order){
            return $this->redirect('/');
        }
        $qrurl = $obj->getQrUrl($orderCode);
        //2.生成二维码
        $qrCode->setText($qrurl)
            ->setSize(300)//大小
            ->setLabelFontPath(PAY_PLUS.'/vendor/endroid\qrcode\assets\noto_sans.otf')
            ->setErrorCorrectionLevel('high')
            ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
            ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))

            ->setLabelFontSize(16);
        header('Content-Type: '.$qrCode->getContentType());
        echo $qrCode->writeString();
        exit;
    }

    public function testingOrder(){
        $order_id = Request::post('order_id');
        return json(OrderModel::where(['id'=>$order_id])->value('status'));
    }
    public function ico(){
        return $this->fetch();
    }

    // 验证订单是否失效
    public function invalidOrder(){
        $orderCode = Request::post('order_code');
        $status = OrderModel::where(['id'=>$orderCode])->value('status');
       return $status;
    }

    // 取消订单
    public function cancelOrder(){
        $order_id = Request::post('order_id');
        $order = OrderModel::where(['order_id'=>$order_id])->find();
        $goods_item = json_decode($order['goods_item']);
        foreach ($goods_item as $item){
            $stock = Goods::where(['id'=>$item->g_id])->value('stock');
            $stock += $item->number;
            Db::table('tp_goods')->where(['id'=>$item->g_id])->update(['stock'=>$stock]);
        }
        // 修改订单状态
        Db::table('tp_order')->where(['id'=>$order['id']])->update(['status'=>6]);
        return json(1);
    }

    // 订单详情
    public function show_Order($id){
        $order = OrderModel::where(['order_id'=>$id])->find();
        $this->assign([
            'order' =>  $order
        ]);
       return  $this->fetch('info-order');
    }
}