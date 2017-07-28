<?php
namespace Game\Controller;

use Think\Controller;
use Think\Model;

class CouponController extends BaseController
{

    public function _initialize()
    {
        parent::isLogin();
    }
    // 领取优惠卷
    public function getCoupon()
    {
        $time = time();
        $uid = session('oto_userId');
        $sql = "SELECT y.id,y.deal_cate_id,s.shopName,y.deal_cate_id,y.good_id,y.supplier_id,y.shop_cat_id,y.brand_id,y.name,y.youhui_scope,y.good_id,y.supplier_id,y.begin_time,y.end_time,y.youhui_type,y.total_fee,y.breaks_menoy,y.deal_cate_id,y.expire_day,y.city_id FROM `oto_youhui` as y  left join oto_shops as s on y.supplier_id=s.shopId where $time between begin_time and end_time  and y.id not in(select youhui_id from oto_youhui_user_link where user_id=$uid)";
        $mayReceive = M()->query($sql);
        $codition = '';
        foreach ($mayReceive as $k => $v) {
            switch ($v['youhui_scope']) {
                case 1:
                    $codition = '全部商品';
                    break;
                case 2:
                    $codition = $v['shopName'];
                    break;
                case 3:
                    $codition = $v['shopName'] . '部分商品';
                    break;
                case 4:
                    $codition = $v['shopName'] . '指定品牌';
                    break;
                case 5:
                    $codition = M('goods_cats')->where(array(
                        'catId' => $v['deal_cate_id']
                    ))->getField('catName');
                    break;
            }
            $mayReceive[$k]['condition'] = $codition;
        }
        $this->mayReceive = $mayReceive;
        $this->display();
    }
    // 领取优惠卷处理
    public function getCouponHandel()
    {
        if (! session('oto_userId')) {
            $this->ajaxReturn(array(
                'status' => - 1
            ));
            return;
        }
        
        if (IS_AJAX) {
            $id = I('id');
            $uinfo = M('youhui_user_link')->where(array(
                'youhui_id' => $id
            ))->find();
            $couponInfo = M('youhui')->where(array(
                'id' => $id
            ))->find();
            if ($uinfo) {
                if ($uinfo['todayget'] >= $couponInfo['user_limit']) {
                    $this->ajaxReturn(array(
                        'status' => - 3
                    ));
                    return;
                }
            }
            if ($couponInfo['begin_time'] < time() && $couponInfo['end_time'] > time()) {
                if ($uinfo) {
                    $A = M('youhui_user_link')->where(array(
                        'youhui_id' => $id
                    ))->setInc('surplus', 1);
                    $B = M('youhui_user_link')->where(array(
                        'youhui_id' => $id
                    ))->setInc('todayget', 1);
                    if ($A && $B) {
                        $this->ajaxReturn(array(
                            'status' => 0
                        ));
                    } else {
                        $this->ajaxReturn(array(
                            'status' => - 1
                        ));
                    }
                } else {
                    $data['youhui_id'] = $id;
                    $data['user_id'] = session('oto_userId');
                    $data['surplus'] = 1;
                    $data['get_time'] = time();
                    $res = M('youhui_user_link')->add($data);
                    if ($res) {
                        $this->ajaxReturn(array(
                            'status' => 0
                        ));
                    } else {
                        $this->ajaxReturn(array(
                            'status' => - 1
                        ));
                    }
                }
            } else {
                $this->ajaxReturn(array(
                    'status' => - 1
                ));
            }
        }
    }
    
    // 优惠卷处理
    public function coupon()
    {
        // 有多少优惠卷待使用
        $time = time();
        $field = "y.deal_cate_id,u.surplus,s.shopName,y.deal_cate_id,y.good_id,y.supplier_id,y.shop_cat_id,y.brand_id,y.name,y.youhui_scope,y.good_id,y.supplier_id,y.begin_time,y.end_time,y.youhui_type,y.total_fee,y.breaks_menoy,y.deal_cate_id,y.expire_day,y.city_id";
        $notUse = M('youhui_user_link')->field($field)
            ->where(array(
            'u.user_id' => session('oto_userId')
        ))
            ->join("as u  join oto_youhui as y on u.youhui_id=y.id and  $time between begin_time and end_time left join oto_shops as s on s.shopId=y.supplier_id")
            ->select();
        foreach ($notUse as $k => $v) {
            switch ($v['youhui_scope']) {
                case 1:
                    $codition = '全部商品';
                    break;
                case 2:
                    $codition = $v['shopName'];
                    break;
                case 3:
                    $codition = $v['shopName'] . '部分商品';
                    break;
                case 4:
                    $codition = $v['shopName'] . '指定品牌';
                    break;
                case 5:
                    $codition = M('goods_cats')->where(array(
                        'catId' => $v['deal_cate_id']
                    ))->getField('catName');
                    break;
            }
            $notUse[$k]['condition'] = $codition;
        }
        $this->notUse = $notUse;
        // 总共有多少张可领取的
        $time = time();
        $uid = session('oto_userId');
        $sql = "SELECT y.deal_cate_id,s.shopName,y.deal_cate_id,y.good_id,y.supplier_id,y.shop_cat_id,y.brand_id,y.name,y.youhui_scope,y.good_id,y.supplier_id,y.begin_time,y.end_time,y.youhui_type,y.total_fee,y.breaks_menoy,y.deal_cate_id,y.expire_day,y.city_id FROM `oto_youhui` as y  left join oto_shops as s on y.supplier_id=s.shopId where $time between begin_time and end_time  and y.id not in(select youhui_id from oto_youhui_user_link where user_id=$uid)";
        $mayReceive = M()->query($sql);
        $this->mayReceive = count($mayReceive);
        
        // 过期没使用
        $field = "y.deal_cate_id,u.surplus,s.shopName,y.deal_cate_id,y.good_id,y.supplier_id,y.shop_cat_id,y.brand_id,y.name,y.youhui_scope,y.good_id,y.supplier_id,y.begin_time,y.end_time,y.youhui_type,y.total_fee,y.breaks_menoy,y.deal_cate_id,y.expire_day,y.city_id";
        $expireNoUse = M('youhui_user_link')->field($field)
            ->join("as u  join oto_youhui as y on u.youhui_id=y.id and  $time not between begin_time and end_time left join oto_shops as s on s.shopId=y.supplier_id where y.id not in(select youhui_id from oto_youhui_use_record where userId=50) and u.user_id= $uid")
            ->select();
        foreach ($expireNoUse as $k => $v) {
            switch ($v['youhui_scope']) {
                case 1:
                    $codition = '全部商品';
                    break;
                case 2:
                    $codition = $v['shopName'];
                    break;
                case 3:
                    $codition = $v['shopName'] . '部分商品';
                    break;
                case 4:
                    $codition = $v['shopName'] . '指定品牌';
                    break;
                case 5:
                    $codition = M('goods_cats')->where(array(
                        'catId' => $v['deal_cate_id']
                    ))->getField('catName');
                    break;
            }
            $expireNoUse[$k]['condition'] = $codition;
        }
        $this->expireNoUse = $expireNoUse;
        
        // 已经使用
        $existsSql = "SELECT y.deal_cate_id, s.shopName, y.deal_cate_id, y.good_id, y.supplier_id, y.shop_cat_id, y.brand_id, y. NAME, y.youhui_scope, y.good_id, y.supplier_id, y.begin_time, y.end_time, y.youhui_type, y.total_fee, y.breaks_menoy, y.deal_cate_id, y.expire_day, y.city_id FROM `oto_youhui_use_record` AS u JOIN oto_youhui AS y ON u.youhui_id = y.id LEFT JOIN oto_shops AS s ON s.shopId = y.supplier_id WHERE (u.userId = $uid)";
        $exisUse = M()->query($existsSql);
        foreach ($exisUse as $k => $v) {
            switch ($v['youhui_scope']) {
                case 1:
                    $codition = '全部商品';
                    break;
                case 2:
                    $codition = $v['shopName'];
                    break;
                case 3:
                    $codition = $v['shopName'] . '部分商品';
                    break;
                case 4:
                    $codition = $v['shopName'] . '指定品牌';
                    break;
                case 5:
                    $codition = M('goods_cats')->where(array(
                        'catId' => $v['deal_cate_id']
                    ))->getField('catName');
                    break;
            }
            $exisUse[$k]['condition'] = $codition;
        }
        $this->exisUse = $exisUse;
        
        $this->display();
    }
}