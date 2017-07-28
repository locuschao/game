<?php
namespace Game\Controller;

use Think\Controller;
use Think\Model;
use Game\Controller\BaseController;

class SecKillController extends BaseController
{

    public function index()
    {
        // 20：00-->0:00 时间段秒杀
        $starTime = strtotime(date('Y-m-d') . '20:00:00');
        $endTime = strtotime(date('Y-m-d') . '23:59:59');
        $this->oneStarTime = $starTime;
        $this->ondEndTime = $endTime;
        $field = "k.shopId,k.goodsId,k.seckillPrice,k.seckillMaxCount,s.goodsName,s.goodsThums,s.marketPrice,s.shopPrice,k.goodsStock";
        $map['k.goodsSeckillStatus'] = 1;
        $map['k.seckillStatus'] = 1;
        $map['s.goodsStatus'] = 1;
        $map['s.goodsFlag'] = 1;
        $map['s.isSeckill'] = 1;
        $map['k.seckillStartTime'] = array(
            'between',
            "$starTime,$endTime"
        );
        $map['k.seckillEndTime'] = array(
            'between',
            "$starTime,$endTime"
        );
        $db = M('goods_seckill');
        $info = $db->join('as k left join oto_goods as s on s.goodsId=k.goodsId')
            ->where($map)
            ->select();
        if (time() > $starTime && time() < $endTime) {
            foreach ($info as $k => $v) {
                $couMap['oto_orders.orderStatus'] = array(
                    'egt',
                    0
                );
                $couMap['oto_orders.createTime'] = array(
                    'between',
                    "$starTime,$endTime"
                );
                $count = M('order_goods')->join('oto_orders on oto_orders.orderId=oto_order_goods.orderId')
                    ->where($couMap)
                    ->sum('oto_order_goods.goodsNums');
                $stock = $v['goodsStock'] + $count;
                $info[$k]['percent'] = round($count / $stock);
                $info[$k]['status'] = 1;
            }
        } else {
            foreach ($info as $k => $v) {
                $couMap['oto_orders.orderStatus'] = array(
                    'egt',
                    0
                );
                $couMap['oto_orders.createTime'] = array(
                    'between',
                    "$starTime,$endTime"
                );
                $count = M('order_goods')->join('oto_orders on oto_orders.orderId=oto_order_goods.orderId')
                    ->where($couMap)
                    ->sum('oto_order_goods.goodsNums');
                $stock = $v['goodsStock'] + $count;
                $info[$k]['percent'] = round($count / $stock);
                $info[$k]['status'] = 0;
            }
        }
        $this->one = $info;
        
        // 00:00->08:00时间段秒杀
        $starTime = strtotime(date('Y-m-d') . '00:00:00');
        $endTime = strtotime(date('Y-m-d') . '08:00:00');
        $this->twoStarTime = $starTime;
        $this->twoEndTime = $endTime;
        $map['k.seckillStartTime'] = array(
            'between',
            "$starTime,$endTime"
        );
        $map['k.seckillEndTime'] = array(
            'between',
            "$starTime,$endTime"
        );
        $info = $db->join('as k left join oto_goods as s on s.goodsId=k.goodsId')
            ->where($map)
            ->select();
        if (time() > $starTime && time() < $endTime) {
            foreach ($info as $k => $v) {
                $couMap['oto_orders.orderStatus'] = array(
                    'egt',
                    0
                );
                $couMap['oto_orders.createTime'] = array(
                    'between',
                    "$starTime,$endTime"
                );
                $count = M('order_goods')->join('oto_orders on oto_orders.orderId=oto_order_goods.orderId')
                    ->where($couMap)
                    ->sum('oto_order_goods.goodsNums');
                $stock = $v['goodsStock'] + $count;
                $info[$k]['percent'] = round($count / $stock);
                $info[$k]['status'] = 1;
            }
        } else {
            foreach ($info as $k => $v) {
                $couMap['oto_orders.orderStatus'] = array(
                    'egt',
                    0
                );
                $couMap['oto_orders.createTime'] = array(
                    'between',
                    "$starTime,$endTime"
                );
                $count = M('order_goods')->join('oto_orders on oto_orders.orderId=oto_order_goods.orderId')
                    ->where($couMap)
                    ->sum('oto_order_goods.goodsNums');
                $stock = $v['goodsStock'] + $count;
                $info[$k]['percent'] = round($count / $stock);
                $info[$k]['status'] = 0;
            }
        }
        $this->two = $info;
        
        // 08:00->12:00时间段秒杀
        $starTime = strtotime(date('Y-m-d') . '08:00:00');
        $endTime = strtotime(date('Y-m-d') . '12:00:00');
        $this->threeStarTime = $starTime;
        $this->threeEndTime = $endTime;
        $map['k.seckillStartTime'] = array(
            'between',
            "$starTime,$endTime"
        );
        $map['k.seckillEndTime'] = array(
            'between',
            "$starTime,$endTime"
        );
        $info = $db->join('as k left join oto_goods as s on s.goodsId=k.goodsId')
            ->where($map)
            ->select();
        if (time() > $starTime && time() < $endTime) {
            foreach ($info as $k => $v) {
                $couMap['oto_orders.orderStatus'] = array(
                    'egt',
                    0
                );
                $couMap['oto_orders.createTime'] = array(
                    'between',
                    "$starTime,$endTime"
                );
                $count = M('order_goods')->join('oto_orders on oto_orders.orderId=oto_order_goods.orderId')
                    ->where($couMap)
                    ->sum('oto_order_goods.goodsNums');
                $stock = $v['goodsStock'] + $count;
                $info[$k]['percent'] = round($count / $stock);
                $info[$k]['status'] = 1;
            }
        } else {
            foreach ($info as $k => $v) {
                $couMap['oto_orders.orderStatus'] = array(
                    'egt',
                    0
                );
                $couMap['oto_orders.createTime'] = array(
                    'between',
                    "$starTime,$endTime"
                );
                $count = M('order_goods')->join('oto_orders on oto_orders.orderId=oto_order_goods.orderId')
                    ->where($couMap)
                    ->sum('oto_order_goods.goodsNums');
                $stock = $v['goodsStock'] + $count;
                $info[$k]['percent'] = round($count / $stock);
                $info[$k]['status'] = 0;
            }
        }
        $this->three = $info;
        
        // 12:00->16:00时间段秒杀
        $starTime = strtotime(date('Y-m-d') . '12:00:00');
        $endTime = strtotime(date('Y-m-d') . '16:00:00');
        $this->fourStarTime = $starTime;
        $this->fourEndTime = $endTime;
        $map['k.seckillStartTime'] = array(
            'between',
            "$starTime,$endTime"
        );
        $map['k.seckillEndTime'] = array(
            'between',
            "$starTime,$endTime"
        );
        $info = $db->join('as k left join oto_goods as s on s.goodsId=k.goodsId')
            ->where($map)
            ->select();
        $info = $db->join('as k left join oto_goods as s on s.goodsId=k.goodsId')
            ->where($map)
            ->select();
        if (time() > $starTime && time() < $endTime) {
            foreach ($info as $k => $v) {
                $couMap['oto_orders.orderStatus'] = array(
                    'egt',
                    0
                );
                $couMap['oto_orders.createTime'] = array(
                    'between',
                    "$starTime,$endTime"
                );
                $count = M('order_goods')->join('oto_orders on oto_orders.orderId=oto_order_goods.orderId')
                    ->where($couMap)
                    ->sum('oto_order_goods.goodsNums');
                $stock = $v['goodsStock'] + $count;
                $info[$k]['percent'] = round($count / $stock);
                $info[$k]['status'] = 1;
            }
        } else {
            foreach ($info as $k => $v) {
                $couMap['oto_orders.orderStatus'] = array(
                    'egt',
                    0
                );
                $couMap['oto_orders.createTime'] = array(
                    'between',
                    "$starTime,$endTime"
                );
                $count = M('order_goods')->join('oto_orders on oto_orders.orderId=oto_order_goods.orderId')
                    ->where($couMap)
                    ->sum('oto_order_goods.goodsNums');
                $stock = $v['goodsStock'] + $count;
                $info[$k]['percent'] = round($count / $stock);
                $info[$k]['status'] = 0;
            }
        }
        $this->four = $info;
        
        // 16:00->20:00时间段秒杀
        $starTime = strtotime(date('Y-m-d') . '16:00:00');
        $endTime = strtotime(date('Y-m-d') . '20:00:00');
        
        $this->fiveStarTime = $starTime;
        $this->fiveEndTime = $endTime;
        $map['k.seckillStartTime'] = array(
            'between',
            "$starTime,$endTime"
        );
        $map['k.seckillEndTime'] = array(
            'between',
            "$starTime,$endTime"
        );
        $info = $db->join('as k left join oto_goods as s on s.goodsId=k.goodsId')
            ->where($map)
            ->select();
        if (time() > $starTime && time() < $endTime) {
            foreach ($info as $k => $v) {
                $couMap['oto_orders.orderStatus'] = array(
                    'egt',
                    0
                );
                $couMap['oto_orders.createTime'] = array(
                    'between',
                    "$starTime,$endTime"
                );
                $count = M('order_goods')->join('oto_orders on oto_orders.orderId=oto_order_goods.orderId')
                    ->where($couMap)
                    ->sum('oto_order_goods.goodsNums');
                $stock = $v['goodsStock'] + $count;
                $info[$k]['percent'] = round($count / $stock);
                $info[$k]['status'] = 1;
            }
        } else {
            foreach ($info as $k => $v) {
                $couMap['oto_orders.orderStatus'] = array(
                    'egt',
                    0
                );
                $couMap['oto_orders.createTime'] = array(
                    'between',
                    "$starTime,$endTime"
                );
                $count = M('order_goods')->join('oto_orders on oto_orders.orderId=oto_order_goods.orderId')
                    ->where($couMap)
                    ->sum('oto_order_goods.goodsNums');
                $stock = $v['goodsStock'] + $count;
                $info[$k]['percent'] = round($count / $stock);
                $info[$k]['status'] = 0;
            }
        }
        $this->five = $info;
        
        $this->display();
    }
}