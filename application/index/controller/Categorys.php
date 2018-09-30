<?php
/**
 * 分类
 * User: @ 若雨
 * Date: 2018/9/17
 * Time: 16:52
 */

namespace app\index\controller;

use app\admin\model\Category;
use app\admin\model\Goods;

class Categorys extends BaseController
{
    public function index($id){
        // 获取顶级分类
        $classification = Category::getGoodsCategoryInfoDingji($id);
        $goods = Goods::getCategoryGoods($id);
        $top_level= $classification['dj'];
        unset($classification['dj']);
        $count = $goods->count();
        $this->assign([
            'classification'   =>    $classification  ,
            'top_level'        =>    $top_level,
            'id'               =>    $id,
            'goods'            =>    $goods,
            'count'           =>    $count
        ]);
        return $this->fetch();
    }

}