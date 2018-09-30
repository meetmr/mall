<?php
namespace app\index\controller;

use app\admin\model\Goods;
class Index extends BaseController
{
    public function index()
    {
        // 获取人气热销商品
        $hotSaleGoods = self::getHotSale(20);
        //  获取新品尝鲜商品
        $newProductGoods = self::getNewProductGoods(20);
        //  获取为你推荐商品
        $recommendGoods = self::getRecommendGoods(20);
        $this->assign([
            'hotSaleGoods'       =>  $hotSaleGoods,      //    人气热销商品
            'newProductGoods'    =>  $newProductGoods,   //    人气新品尝鲜商品
            'recommendGoods'    =>  $recommendGoods      //    为你推荐商品
        ]);
        return $this->fetch();
    }

//    获取最新商品
    public static function getNewGoods(){
        return (Goods::getNewGoods());
    }

    // 获取指定数目商品
    public static function getGoods($count){
       return Goods::getGoods($count);
    }

    // 获取人气热销商品
    public static function getHotSale($count){
        return Goods::getHotSale($count);
    }

    // 获取新品尝鲜商品
    public static function getNewProductGoods($count){
        return Goods::getNewProductGoods($count);
    }
    // 获取为你推荐商品
    public static function getRecommendGoods($count){
        return Goods::getRecommendGoods($count);
    }
}
