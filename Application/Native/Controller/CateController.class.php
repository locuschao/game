<?php
namespace Native\Controller;

use Think\Controller;
use Think\Model;

class CateController extends Controller
{

    public function cate()
    {
        $catid = I('id');
        $page = I('page', 0);
        $key = I('key');
        if ($key) {
            $map['goodsName'] = array(
                'like',
                "%" . $key . '%'
            );
        }
        $map['isSale'] = 1;
        $map['goodsStock'] = array(
            'gt',
            0
        );
        $map['goodsFlag'] = 1;
        $map['goodsStatus'] = 1;
        $map['goodsCatId3'] = $catid;
        $field = "goodsId,goodsName,goodsThums,shopId,shopPrice,marketPrice";
        $step = 20;
        $limit = $step * $page;
        $this->goodsList = M('goods')->where($map)
            ->field($field)
            ->order('goodsId DESC')
            ->limit($limit, $step)
            ->select();
        $this->cateName = M('goods_cats')->where(array(
            'catId' => $catid
        ))->getField('catName');
        if (IS_AJAX) {
            $this->ajaxReturn($this->goodsList);
        } else {
            $this->display();
        }
    }
}