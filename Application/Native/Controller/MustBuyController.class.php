<?php
namespace Native\Controller;

use Think\Controller;
use Think\Model;

class MustBuyController extends BaseController
{

    public function index()
    {
        $parentCatid = I('parentId', 334);
        // $parentCatid=334;//超市
        // 二级分类
        $sonCat = M('goods_cats')->where(array(
            'parentId' => $parentCatid,
            'catFlag' => 1
        ))
            ->field('catId,catName')
            ->order('catSort ASC')
            ->select();
        $this->assign('cat', $sonCat);
        // 精品推荐
        $baseMap['goodsCatId1'] = $parentCatid;
        $baseMap['goodsStatus'] = 1;
        $baseMap['isGroup'] = 0;
        $baseMap['isBest'] = 1;
        $baseMap['goodsFlag'] = 1;
        $baseMap['goodsStock'] = array(
            'gt',
            0
        );
        $field = "goodsId,goodsName,marketPrice,shopPrice,goodsStock,goodsThums";
        $goods = M('goods')->where($baseMap)
            ->field($field)
            ->limit(3)
            ->select();
        // 默认第一个三级显示
        unset($baseMap['isBest']);
        $db = M('goods_cats');
        $arr = array();
        foreach ($sonCat as $k => $v) {
            $arr[$v['catId']] = M('goods_cats')->where(array(
                'parentId' => $v['catId'],
                'catFlag' => 1
            ))
                ->field('catId,catName')
                ->order('catSort ASC')
                ->select();
        }
        $baseMap['goodsCatId2'] = $sonCat[0]['catId'];
        $goodsHot = M('goods')->where($baseMap)
            ->field($field)
            ->limit(8)
            ->select();
        $this->cateName = M('goods_cats')->where(array(
            'catId' => $parentCatid
        ))->getField('catName');
        $this->assign('third', $arr);
        $this->assign('baseGoods', $goods);
        $this->assign('hotGoods', $goodsHot);
        $this->display();
    }
    
    // 超市
    public function supermarket()
    {
        $parentCatid = I('parentId', 334);
        // $parentCatid=334;//超市
        // 二级分类
        $sonCat = M('goods_cats')->where(array(
            'parentId' => $parentCatid,
            'catFlag' => 1
        ))
            ->field('catId,catName')
            ->order('catSort ASC')
            ->select();
        $this->assign('cat', $sonCat);
        // 精品推荐
        $baseMap['goodsCatId1'] = $parentCatid;
        $baseMap['goodsStatus'] = 1;
        $baseMap['isGroup'] = 0;
        $baseMap['isBest'] = 1;
        $baseMap['goodsFlag'] = 1;
        $baseMap['goodsStock'] = array(
            'gt',
            0
        );
        $field = "goodsId,goodsName,marketPrice,shopPrice,goodsStock,goodsThums";
        $goods = M('goods')->where($baseMap)
            ->field($field)
            ->limit(3)
            ->select();
        // 默认第一个三级显示
        unset($baseMap['isBest']);
        $secondCat = M('goods_cats')->where(array(
            'parentId' => $sonCat[0]['catId'],
            'catFlag' => 1
        ))
            ->field('catId,catName')
            ->order('catSort ASC')
            ->select();
        $baseMap['goodsCatId2'] = $sonCat[0]['catId'];
        $goodsHot = M('goods')->where($baseMap)
            ->field($field)
            ->limit(8)
            ->select();
        
        $this->cateName = M('goods_cats')->where(array(
            'catId' => $parentCatid
        ))->getField('catName');
        $this->assign('secondCat', $secondCat);
        $this->assign('baseGoods', $goods);
        $this->assign('hotGoods', $goodsHot);
        $this->display();
    }
    // 加载三级菜单
    public function thirdCat()
    {
        if (IS_AJAX) {
            $secondCatId = I('id');
            $secondCat = M('goods_cats')->where(array(
                'parentId' => $secondCatId,
                'catFlag' => 1
            ))
                ->field('catId,catName')
                ->order('catSort ASC')
                ->select();
            // 默认加载第一个分类的内容
            $baseMap['goodsCatId2'] = $secondCatId;
            $baseMap['goodsStatus'] = 1;
            $baseMap['isGroup'] = 0;
            $baseMap['isBest'] = 1;
            $baseMap['goodsFlag'] = 1;
            $baseMap['goodsStock'] = array(
                'gt',
                0
            );
            $field = "goodsId,goodsName,marketPrice,shopPrice,goodsStock,goodsThums";
            $goods = M('goods')->where($baseMap)
                ->field($field)
                ->limit(8)
                ->select();
            foreach ($goods as $k => $v) {
                $goods[$k]['url'] = U('goodsDetail', array(
                    'id' => $v['goodsId']
                ));
            }
            $res = array();
            $res['goods'] = $goods;
            $res['cat'] = $secondCat;
            $this->ajaxReturn($res);
        }
    }
    
    // 加载三级分类下的内容
    // 加载三级菜单
    public function thirdCatGoodsInfo()
    {
        if (IS_AJAX) {
            $page = I('page');
            $catid = I('id');
            // 默认加载第一个分类的内容
            $baseMap['goodsCatId3'] = $catid;
            $baseMap['goodsStatus'] = 1;
            $baseMap['isGroup'] = 0;
            $baseMap['isBest'] = 1;
            $baseMap['goodsFlag'] = 1;
            $baseMap['goodsStock'] = array(
                'gt',
                0
            );
            $field = "goodsId,goodsName,marketPrice,shopPrice,goodsStock,goodsThums";
            $limit = 8;
            $start = $page * $limit;
            $goods = M('goods')->where($baseMap)
                ->field($field)
                ->limit($start, $limit)
                ->select();
            foreach ($goods as $k => $v) {
                $goods[$k]['url'] = U('goodsDetail', array(
                    'id' => $v['goodsId']
                ));
            }
            $this->ajaxReturn($goods);
        }
    }
    // 加载更多
    public function moreSupermarket()
    {
        $parentCatid = I('parentId', 334);
        // $parentCatid=334;//超市
        $page = I('page');
        // 精品推荐
        $catid2 = I('catid2');
        $catid3 = I('catid3');
        if ($catid3) {
            $baseMap['goodsCatId3'] = $catid3;
        } else 
            if ($catid2) {
                $baseMap['goodsCatId2'] = $catid2;
            } else {
                $baseMap['goodsCatId1'] = $parentCatid;
            }
        $baseMap['goodsStatus'] = 1;
        $baseMap['isGroup'] = 0;
        // $baseMap['isBest']=1;
        $baseMap['goodsFlag'] = 1;
        $baseMap['goodsStock'] = array(
            'gt',
            0
        );
        $step = 8;
        $limit = $step * $page;
        $field = "goodsId,goodsName,marketPrice,shopPrice,goodsStock,goodsThums";
        $goods = M('goods')->where($baseMap)
            ->field($field)
            ->limit($limit, $step)
            ->select();
        foreach ($goods as $k => $v) {
            $goods[$k]['url'] = U('goodsDetail', array(
                'id' => $v['goodsId']
            ));
        }
        $this->ajaxReturn($goods);
    }
    // 加载更多
    public function switchMoreSupermarket()
    {
        // $parentCatid=334;//超市
        $parentCatid = I('parentId', 334);
        $page = I('page');
        // 精品推荐
        $baseMap['goodsCatId1'] = $parentCatid;
        $baseMap['goodsStatus'] = 1;
        $baseMap['isGroup'] = 0;
        $baseMap['isBest'] = 1;
        $baseMap['goodsFlag'] = 1;
        $baseMap['goodsStock'] = array(
            'gt',
            0
        );
        $step = 3;
        $limit = $step * $page;
        $field = "goodsId,goodsName,marketPrice,shopPrice,goodsStock,goodsThums";
        $goods = M('goods')->where($baseMap)
            ->field($field)
            ->limit($limit, $step)
            ->select();
        foreach ($goods as $k => $v) {
            $goods[$k]['url'] = U('goodsDetail', array(
                'id' => $v['goodsId']
            ));
        }
        $this->ajaxReturn($goods);
    }
}
