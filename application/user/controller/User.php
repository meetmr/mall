<?php
/**
 *  用户控制器
 * User: @ 若雨PhpSt
 * Date: 2018/9/18
 * Time: 20:22
 */

namespace app\user\controller;
use app\index\controller\BaseController;
use app\admin\model\City as CityModel;
use think\facade\Request;
use app\admin\model\UserAddress;
use app\admin\model\Logistics;
use app\admin\model\Order;
class User extends BaseController
{
    public function initialize(){
        parent::initialize(); // TODO: Change the autogenerated stub
        $this->isLogin();
    }

    // 展示个人中心
    public function member(){
        $order = $this->getOrder();
        $count = $order->count();
        $this->assign([
            'title'    =>  '订单管理',
            'left'     =>    'order',
            'order'    =>   $order,
            'count'   =>   $count
        ]);
        return $this->fetch();
    }

    // 收获地址
    public function address(){
        $address = UserAddress::where(['user_id'=>session('user.id')])->select();
        $count = $address->count();
        $this->assign([
            'address'   =>  $address,
            'count'     =>  $count
        ]);
        $this->assign([
            'title'    =>  '收货地址',
            'left'    =>    'address'
        ]);
        return $this->fetch();
    }
    // 添加收货地址
    public function addAddress(){
        $list_city = CityModel::getCitylist();
        $this->assign('list_city',$list_city);
        $this->assign([
            'title'    =>  '添加-收货地址',
            'left'    =>    'address'
        ]);
        return $this->fetch('add_address');
    }
    public function getlevelcity(){
        $region_id = input('post.region_id');
        $listlevelcity = CityModel::getCitylist($region_id);
        return json($listlevelcity);
    }
    // 收获地址入库
    public function cheAddress(){
        $data = Request::post();
        $data['city'] = CityModel::getCityName($data['city']);
        $data['country'] = CityModel::getCityName($data['country']);
        $data['detail'] = CityModel::getCityName($data['detail']);
        $data['user_id'] = session('user.id');
        $info = UserAddress::create($data);
        if($info){
            $state = [
                'code' => 1,
                'msg'=> '添加成功'
            ];
            return json($state);
        }
    }
    //删除收获地址
    public function deleteAddress(){
        $id = Request::post('id');
        $info = UserAddress::where(['id'=>$id])->delete();
        if($info){
            $state = [
                'code' => 1,
                'msg'=> '添加成功'
            ];
            return json($state);
        }
    }

    public function logisticsInfo(){
        $id = intval(input('get.id'));
        $logistics = Logistics::where(['o_id'=>$id])->find();
        $wlinfo = json_decode(getWuLiuInfo($logistics['name'],$logistics['order_number']));
        if($wlinfo->status == 400){
            return json([
                'msg'   =>  '参数错误'
            ]);
        }
      $data = $wlinfo->data;
     $status = Order::where(['id'=>$id])->value('status');
      $this->assign([
          'wlinfo'  =>  $wlinfo,
          'data'    =>  $data,
          'id'      =>  $id,
          'status'  =>  $status
      ]);
        return $this->fetch('wl');
    }
    // 确定收获

    public function harvest(){
        if(Request::isAjax()){
            $o_id = Request::post('id');
            $info = Order::update(['status'=>5],['id'=>$o_id]);
            if($info){
                return json(1);
            }else{
                return json(0);

            }
        }
    }
}