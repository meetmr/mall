<?php
/**
 * 商品分类模型模型
 * User: @ 若雨
 * Date: 2018/9/11
 * Time: 15:30
 */

namespace app\admin\model;

use catetree\Catetree;

class Category extends BaseModel
{
      public static function getInfoCategory(){
          $cate_list = self::order('sort asc')->select();
          $cate = new Catetree();
          $cateList = $cate->catetree($cate_list);
          return $cateList;
    }
    // 传入分类id、获取商品分类名称
   public static function getGoodsCategoryName($c_id){
          return self::where('id','=',$c_id)->value('cate_name');
    }

    // 生成商品分类
    public static function getCategory(){
          return self::where('pid','=',0)->field('id,cate_name')->select();
    }

    // 生成产品分类二级
    public static function getCategoryErJi(){
        $goods =  self::where('pid','=',0)->field('id,cate_name')->select();
        foreach ($goods as $good){
            $good['erji'] = self::where('pid','=',$good['id'])->field('id,cate_name')->select();
        }
        return $goods;
    }
    // 传入分类id、获取商品分类名称
    public static function getGoodsCategoryInfo($c_id){
        return self::where('id','=',$c_id)->field('id,cate_name')->find();
    }

    // 传入商品id 获取顶级ID下面的所有分类
    public static function getGoodsCategoryInfoDingji($c_id){
        $p_id =  self::where('id','=',$c_id)->value('pid');
        if($p_id == 0){
            $p_id = $c_id;
        }
        $dj =  self::where('id','=',$p_id)->field('id,cate_name')->find();
        $cate =  self::where('pid','=',$p_id)->field('id,cate_name')->select();
        $cate['dj'] = $dj;
        return $cate;
    }


}