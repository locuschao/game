<?php
namespace Game\Controller;

use Think\Controller;
use Think\Model;

class FavoritesController extends BaseController
{

    public function _initialize()
    {
        parent::isLogin();
        @header('Content-type: text/html;charset=UTF-8');
    }

    public function index()
    {
        $map['f.userId'] = session('oto_userId');
        $map['f.favoriteType'] = 0;
        $map['g.goodsStatus'] = 1;
        $map['g.goodsFlag'] = 1;
        $this->fav = M('favorites')->where($map)
            ->join('as f left join oto_goods as g on f.targetId=g.goodsId')
            ->field('goodsThums,goodsName,shopPrice,goodsId')
            ->select();
        $this->display();
    }
}