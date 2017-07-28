<?php
namespace Native\Controller;

use Think\Controller;
use Think\Model;

class ShopController extends BaseController
{
    
    
    
    //付款时店铺营业时间
    public function officeHours(){
    
        $goodsId=I('goodsId',0);
        
        $payId=I('orderId',0);
    
        $orderType=M('orders_payid')->where(array('id'=>$payId))->getField('type');
    
         if($goodsId){
             $shopId=M('goods')->where(array('goodsId'=>$goodsId))->getField('shopId');
             $shopInfo=M('shops')->where(array('shopId'=>$shopId))->find();
             
             if(!$shopId||!$shopInfo){
                 $this->returnJson(array('status'=>-2,'msg'=>'非法数据请求'));
             }
             date_default_timezone_set('PRC');
             //判断营业时间
             $currentH=date("G");
             $fen=date('i');
             if($fen>=30&&$fen<60){
                 $currentH=$currentH.'.5';
             }
             if($currentH>=$shopInfo['serviceStartTime']&&$currentH<=$shopInfo['serviceEndTime']){
                 $this->returnJson(array('status'=>0,'msg'=>'店铺营业中'));
             }else{
                 $starTime=$shopInfo['serviceStartTime'];
                 $endTime=$shopInfo['serviceEndTime'];
                 $this->returnJson(array('status'=>-1,'msg'=>"店铺营业时间是{$starTime} 至 {$endTime}"));
             }
             exit();
         }
        
        //订单
        if($orderType==1){
    
            $orderId=M('orders_payid')->where(array('pid'=>$payId))->getField('orderId');
            $shopId=M('orders')->where(array('orderId'=>$orderId))->getField('shopId');
            $shopInfo=M('shops')->where(array('shopId'=>$shopId))->find();
    
            if(!$orderId||!$shopId||!$shopInfo){
                $this->returnJson(array('status'=>-2,'msg'=>'非法数据请求'));
            }
    
            date_default_timezone_set('PRC');
            //判断营业时间
            $currentH=date("G");
            $fen=date('i');
            if($fen>=30&&$fen<60){
                $currentH=$currentH.'.5';
            }
    
            if($currentH>=$shopInfo['serviceStartTime']&&$currentH<=$shopInfo['serviceEndTime']){
                $this->returnJson(array('status'=>0,'msg'=>'店铺营业中'));
            }else{
                $starTime=$shopInfo['serviceStartTime'];
                $endTime=$shopInfo['serviceEndTime'];
                $this->returnJson(array('status'=>-1,'msg'=>"店铺营业时间是{$starTime} 至 {$endTime}"));
            }
        }else{
            //充值的订单不用判断 时间
            $this->returnJson(array('status'=>0,'msg'=>'订单充值'));
        }
    
    }
    
    // 店铺搜索
    public function searchShop()
    {
        $key = trim(I('key'));
        $page=I('page',0);
        $H = date('H');
        $where = "where shopFlag=1 and shopAtive=1 and  $H between serviceStartTime and serviceEndTime";
        if (! empty($key)) {
            $where .= " and shopName like '%$key%'";
        }
        $start=$page*20;
        $sql = "select oto_shops.shopId,oto_shops.shopImg,oto_shops.shopName,oto_shops.scope,(select Sum(g.goodsNums) from oto_order_goods as g left join oto_orders as o on g.orderId=o.orderId and  o.orderStatus>0 where o.shopId=oto_shops.shopId ) as sales from oto_shops $where order by  sales DESC limit  $start,20 ";
        $shopList = M()->query($sql);
       if($shopList){
           foreach ($shopList as $k => $v) {
               $shopList[$k]['sales']=$v['sales']>0?$v['sales']:0;
               $type = '';
               $temp = explode(',', $v['scope']);
               if (in_array('1', $temp)) {
                   $type .= '首充号,';
               }
               if (in_array('2', $temp)) {
                   $type .= '首充号代充,';
               }
               $shopList[$k]['scope'] = $type;
           }
       }
     
        $this->returnJson($shopList);
    }

    public function getShopInfo()
    {
        $id = I('shopId');
        if (! $id) {
            return;
        }
        $shopInfo = M('shops')->where(array(
            'shopId' => $id
        ))->find();
        $shopInfo['serviceStartTime'] = str_replace('.', ':', $shopInfo['serviceStartTime']) . '0';
        $shopInfo['serviceEndTime'] = str_replace('.', ':', $shopInfo['serviceEndTime']) . '0';
        $this->shopInfo = $shopInfo;
        $sql = "select SUM(g.goodsNums) as sales from oto_order_goods as g  join oto_orders as o on o.orderId=g.orderId left join oto_shops as s on s.shopId=o.shopId where s.shopId=$id and o.orderStatus>0";
        $res = M()->query($sql);
        $sales = $res[0]['sales'];
        $temp = explode(',', $shopInfo['scope']);
        foreach ($temp as $v) {
            if ($v == 1) {
                $fanwei .= '首充号,';
            } else 
                if ($v == 2) {
                    $fanwei .= '首充号代充,';
                } else 
                    if ($v == 3) {
                        $fanwei .= '会员首充号,';
                    } else 
                        if ($v == 4) {
                            $fanwei .= '会员首充号代充,';
                        }
        }
        $fanwei = trim($fanwei, ',');
        $shopInfo['fanwei'] = $fanwei;
        $shopInfo['salesNum'] = $sales;
        $this->returnJson($shopInfo);
    }
    
    // 店铺首充号
    public function getShouChong()
    {
        $id = I('shopId');
        if (! $id) {
            return;
        }
        $shouChongSql = "
            SELECT
	g.goodsName,g.goodsId,g.goodsThums,g.shopPrice,g.isMiao,g.scopeId,g.goodsSpec,(
		SELECT
			attrPrice
		FROM
			oto_goods_versions AS gv
		WHERE
			gv.goodsId = g.goodsId
		ORDER BY
			attrPrice ASC
		LIMIT 1
	) AS attrPrice
FROM
	__PREFIX__goods as g
WHERE
	(g.goodsFlag = 1)
AND (g.goodsStatus = 1)
AND (g.isSale = 1)
AND (g.shopId = $id)
AND (g.scopeId = 1)
AND (g.goodsType= 0)
 HAVING  attrPrice> 0
ORDER BY
	shopPrice ASC
LIMIT 30
            ";
        $shouChong = M()->query($shouChongSql);
        $this->returnJson($shouChong);
    }
    
    // 店铺代充号
    public function getDaiChong()
    {
        $id = I('shopId');
        if (! $id) {
            return;
        }
        $daiChongSql = "
            SELECT
	g.goodsName,g.goodsId,g.goodsThums,g.shopPrice,g.isMiao,g.scopeId,g.goodsSpec,(
		SELECT
			attrPrice
		FROM
			oto_goods_versions AS gv
		WHERE
			gv.goodsId = g.goodsId
		ORDER BY
			attrPrice ASC
		LIMIT 1
	) AS attrPrice
FROM
	__PREFIX__goods as g
WHERE
	(g.goodsFlag = 1)
AND (g.goodsStatus = 1)
AND (g.isSale = 1)
AND (g.shopId = $id)
AND (g.scopeId = 2)
AND (g.goodsType= 0)
HAVING  attrPrice> 0
ORDER BY
	shopPrice ASC
LIMIT 30
            ";
        
        $daiChong = M()->query($daiChongSql);
        $this->returnJson($daiChong);
    }
    // 店铺页
    public function personShop()
    {
        $id = I('id');
        $shopInfo = M('shops')->where(array(
            'shopId' => $id
        ))->find();
        $shopInfo['serviceStartTime'] = str_replace('.', ':', $shopInfo['serviceStartTime']) . '0';
        $shopInfo['serviceEndTime'] = str_replace('.', ':', $shopInfo['serviceEndTime']) . '0';
        $this->shopInfo = $shopInfo;
        $sql = "select SUM(g.goodsNums) as sales from oto_order_goods as g  join oto_orders as o on o.orderId=g.orderId left join oto_shops as s on s.shopId=o.shopId where s.shopId=$id and o.orderStatus>0";
        $res = M()->query($sql);
        $this->sales = $res[0]['sales'];
        $temp = explode(',', $shopInfo['scope']);
        foreach ($temp as $v) {
            if ($v == 1) {
                $fanwei .= '首充号,';
            } else 
                if ($v == 2) {
                    $fanwei .= '首充号代充,';
                } else 
                    if ($v == 3) {
                        $fanwei .= '首充号分销,';
                    } else 
                        if ($v == 4) {
                            $fanwei .= '自主充值,';
                        }
        }
        $fanwei = trim($fanwei, ',');
        $this->fanwei = $fanwei;
        
        $shouChongSql = "
            SELECT
	g.goodsName,g.goodsId,g.goodsThums,g.shopPrice,g.isMiao,(
		SELECT
			attrPrice
		FROM
			oto_goods_versions AS gv
		WHERE
			gv.goodsId = g.goodsId
		ORDER BY
			attrPrice ASC
		LIMIT 1
	) AS attrPrice 
FROM
	__PREFIX__goods as g
WHERE
	(g.goodsFlag = 1)
AND (g.goodsStatus = 1)
AND (g.isSale = 1)
AND (g.shopId = 11)
AND (g.scopeId = 1)
AND (g.goodsType= 0) 
 HAVING  attrPrice> 0 
ORDER BY
	shopPrice ASC
LIMIT 30
            ";
        $this->shouChong = M()->query($shouChongSql);
        
        $daiChongSql = "
            SELECT
	g.*,(
		SELECT
			attrPrice
		FROM
			oto_goods_versions AS gv
		WHERE
			gv.goodsId = g.goodsId
		ORDER BY
			attrPrice ASC
		LIMIT 1
	) AS attrPrice
FROM
	__PREFIX__goods as g
WHERE
	(g.goodsFlag = 1)
AND (g.goodsStatus = 1)
AND (g.isSale = 1)
AND (g.shopId = 11)
AND (g.scopeId = 2)
AND (g.goodsType= 0) 
HAVING  attrPrice> 0
ORDER BY
	shopPrice ASC
LIMIT 30
            ";
        
        $this->daiChong = M()->query($daiChongSql);
        $this->display();
    }
    
    // 店铺分类
    public function shopCate()
    {
        $id = I('shopId');
        $goodsType=I('goodsType');
        $shopInfo = M('shops')->where(array(
            'shopId' => $id
        ))->find();
        $temp = explode(',', $shopInfo['scope']);
        $firstCateArr = array();
        $scopeId = 1;
        $secondCate = array();
        foreach ($temp as $k => $v) {
            if ($v == 1) {
                $scopeId = 1;
                $firstCateArr[] = '首充号';
                $fanwei .= '首充号,';
            } else 
                if ($v == 2) {
                    $scopeId = 2;
                    $firstCateArr[] = '首充号代充';
                    $fanwei .= '首充号代充,';
                } /* else 
                    if ($v == 3) {
                        $scopeId = 3;
                        $firstCateArr[] = '会员首充号';
                        $fanwei .= '会员首充号,';
                    } else 
                        if ($v == 4) {
                            $scopeId = 4;
                            $firstCateArr[] = '会员首充号代充';
                            $fanwei .= '会员首充号代充,';
                        } */
            $sql = <<<Eof
select
ga.gameName,ga.id,ELT(INTERVAL(CONV(HEX(left(CONVERT(gameName USING gbk),1)),16,10),
0xB0A1,0xB0C5,0xB2C1,0xB4EE,0xB6EA,0xB7A2,0xB8C1,0xB9FE,0xBBF7,
0xBFA6,0xC0AC,0xC2E8,0xC4C3,0xC5B6,0xC5BE,0xC6DA,0xC8BB,0xC8F6,
0xCBFA,0xCDDA,0xCEF4,0xD1B9,0xD4D1),
'A','B','C','D','E','F','G','H','J','K','L','M','N','O','P',
'Q','R','S','T','W','X','Y','Z') as PY  from oto_game as ga join oto_goods as go on ga.id=go.gameId join oto_shops  as s on s.shopId=go.shopId where go.scopeId=$scopeId and go.shopId=$id and go.goodsType=0  group by go.gameId  order by PY ASC
Eof;
            $res = M()->query($sql);
            $secondCate[$k] = $res;
        }
        $arr = array();
        $arr['first'] = $firstCateArr;
        $arr['second'] = $secondCate;
        $this->returnJson($arr);
    }

    public function goodsList()
    {
        $id = I('shopId');
        $page = I('page', 0);
        $orderby = I('orderBy') ? I('orderBy') : 'NEW';
        $scondCate = I('scondCate', 0);
        $firstCate = I('firstCate', 0);
        $goodsType = I('goodsType', 0);
        $firstCateID = $firstCate;
        
        $shopInfo = M('shops')->where(array(
            'shopId' => $id
        ))->find();
        $temp = explode(',', $shopInfo['scope']);
        $firstCateArr = array();
        $scopeId = 1;
        $secondCate = array();
        foreach ($temp as $k => $v) {
            if ($v == 1) {
                $scopeId = 1;
                $firstCateArr[] = '首充号';
                $fanwei .= '首充号,';
            } else 
                if ($v == 2) {
                    $scopeId = 2;
                    $firstCateArr[] = '首充号代充';
                    $fanwei .= '首充号代充,';
                }
            $sql = <<<Eof
select
ga.gameName,ga.id,ELT(INTERVAL(CONV(HEX(left(CONVERT(gameName USING gbk),1)),16,10),
0xB0A1,0xB0C5,0xB2C1,0xB4EE,0xB6EA,0xB7A2,0xB8C1,0xB9FE,0xBBF7,
0xBFA6,0xC0AC,0xC2E8,0xC4C3,0xC5B6,0xC5BE,0xC6DA,0xC8BB,0xC8F6,
0xCBFA,0xCDDA,0xCEF4,0xD1B9,0xD4D1),
'A','B','C','D','E','F','G','H','J','K','L','M','N','O','P',
'Q','R','S','T','W','X','Y','Z') as PY  from oto_game as ga join oto_goods as go on ga.id=go.gameId join oto_shops  as s on s.shopId=go.shopId where go.scopeId=$scopeId and go.shopId=$id and go.goodsType=0  group by go.gameId  order by PY ASC
Eof;
            $res = M()->query($sql);
            $secondCate[$k] = $res;
        }
        
        $fanwei = trim($fanwei, ',');
        $this->fanwei = $fanwei;
        // 一级分类
        $this->firstCate = $firstCateArr;
        // 二级分类
        $this->secondCate = $secondCate;
        // 店铺信息
        $this->shopInfo = $shopInfo;
        
        $where['g.goodsFlag'] = 1;
        $where['g.isSale'] = 1;
        $where['g.goodsStatus'] = 1;
        $where['g.shopId'] = $id;
        $where['g.goodsType'] = $goodsType;
        $where['g.scopeId'] = $firstCateID + 1;
        if ($scondCate) {
            $where['gameId'] = $scondCate;
        } else {
            unset($where['g.scopeId']);
        }
        if ($orderby) {
            switch ($orderby) {
                case 'NEW':
                    $order = "goodsId DESC";
                    break;
                case 'DESC':
                    $order = "attrPrice DESC";
                    break;
                case 'ASC':
                    $order = "attrPrice ASC";
                    break;
            }
        }
        
        $field = "g.scopeId,g.goodsName,g.goodsId,g.goodsThums,g.shopPrice,g.isMiao,g.goodsType,g.goodsSpec,(
		SELECT
			attrPrice
		FROM
			oto_goods_versions AS gv
		WHERE
			gv.goodsId = g.goodsId
		ORDER BY
			attrPrice ASC
		LIMIT 1
	) AS attrPrice ";
        $goodsInfo = M('goods as g')->field($field)
            ->where($where)
            ->order($order)
            ->limit($page * 20, 20)
            ->having(' attrPrice >0 ')
            ->select();
        foreach ($goodsInfo as $k => $v) {
            if ($v['scopeId'] == 1) {
                $goodsInfo[$k]['type'] = '首充号';
            } else 
                if ($v['scopeId'] == 2) {
                    $goodsInfo[$k]['type'] = '首充号代充';
                }
        }
        $this->returnJson($goodsInfo);
    }

    public function memberGoodsList()
    {
        $id = I('id');
        
        $page = I('page', 0);
        
        $orderby = I('orderby') ? I('orderby') : 'NEW';
        
        $scondCate = I('scondCate', 0);
        
        $firstCate = I('firstCate', 0);
        $firstCateID = $firstCate;
        
        $shopInfo = M('shops')->where(array(
            'shopId' => $id
        ))->find();
        $temp = explode(',', $shopInfo['scope']);
        $firstCateArr = array();
        $scopeId = 1;
        $secondCate = array();
        foreach ($temp as $k => $v) {
            if ($v == 1) {
                $scopeId = 1;
                $firstCateArr[] = '首充号';
                $fanwei .= '首充号,';
            } else 
                if ($v == 2) {
                    $scopeId = 2;
                    $firstCateArr[] = '首充号代充';
                    $fanwei .= '首充号代充,';
                } else 
                    if ($v == 3) {
                        $scopeId = 3;
                        $firstCateArr[] = '首充号分销';
                        $fanwei .= '首充号分销,';
                    } else 
                        if ($v == 4) {
                            $scopeId = 4;
                            $firstCateArr[] = '自主充值';
                            $fanwei .= '自主充值,';
                        }
            $sql = <<<Eof
select
ga.gameName,ga.id,ELT(INTERVAL(CONV(HEX(left(CONVERT(gameName USING gbk),1)),16,10),
0xB0A1,0xB0C5,0xB2C1,0xB4EE,0xB6EA,0xB7A2,0xB8C1,0xB9FE,0xBBF7,
0xBFA6,0xC0AC,0xC2E8,0xC4C3,0xC5B6,0xC5BE,0xC6DA,0xC8BB,0xC8F6,
0xCBFA,0xCDDA,0xCEF4,0xD1B9,0xD4D1),
'A','B','C','D','E','F','G','H','J','K','L','M','N','O','P',
'Q','R','S','T','W','X','Y','Z') as PY  from oto_game as ga join oto_goods as go on ga.id=go.gameId join oto_shops  as s on s.shopId=go.shopId where go.scopeId=$scopeId and go.shopId=$id and go.goodsType=1  group by go.gameId  order by PY ASC
Eof;
            $res = M()->query($sql);
            $secondCate[$k] = $res;
        }
        
        $fanwei = trim($fanwei, ',');
        $this->fanwei = $fanwei;
        // 一级分类
        $this->firstCate = $firstCateArr;
        // 二级分类
        $this->secondCate = $secondCate;
        // 店铺信息
        $this->shopInfo = $shopInfo;
        
        $where['g.goodsFlag'] = 1;
        $where['g.isSale'] = 1;
        $where['g.goodsStatus'] = 1;
        $where['g.shopId'] = $id;
        $where['g.goodsType'] = 1;
        $where['g.scopeId'] = $firstCateID + 1;
        if ($scondCate) {
            $where['gameId'] = $scondCate;
        } else {
            unset($where['g.scopeId']);
        }
        if ($orderby) {
            switch ($orderby) {
                case 'NEW':
                    $order = "goodsId DESC";
                    break;
                case 'DESC':
                    $order = "shopPrice DESC";
                    break;
                case 'ASC':
                    $order = "shopPrice ASC";
                    break;
            }
        }
        $field = "g.*,(
		SELECT
			attrPrice
		FROM
			oto_goods_versions AS gv
		WHERE
			gv.goodsId = g.goodsId
		ORDER BY
			attrPrice ASC
		LIMIT 1
	) AS attrPrice ";
        $goodsInfo = M('goods as g')->field($field)
            ->where($where)
            ->order($order)
            ->limit($page * 20, 20)
            ->having(' attrPrice >0 ')
            ->select();
        foreach ($goodsInfo as $k => $v) {
            if ($v['scopeId'] == 1) {
                $goodsInfo[$k]['type'] = '首充号';
            } else 
                if ($v['scopeId'] == 2) {
                    $goodsInfo[$k]['type'] = '首充号代充';
                } else 
                    if ($v['scopeId'] == 3) {
                        $goodsInfo[$k]['type'] = '首充号分销';
                    } else 
                        if ($v['scopeId'] == 4) {
                            $goodsInfo[$k]['type'] = '自主充值';
                        } else {
                            $goodsInfo[$k]['type'] = '其它';
                        }
        }
        $this->goodsInfo = $goodsInfo;
        if (IS_AJAX) {
            $this->ajaxReturn($goodsInfo);
        } else {
            $this->display();
        }
    }

    public function shop()
    {
        $id = I('get.id', 0, intval);
        $shopInfo = M('shops')->where(array(
            'shopId' => $id,
            'shopFlag' => 1,
            'shopAtive' => 1,
            'shopStatus' => 1
        ))->find();
        $this->shopInfo = $shopInfo;
        
        $shopCate = M('shops_cats')->where(array(
            'shopId' => $id,
            'catFlag' => 1
        ))->select();
        $cate = $this->unlimitedForLayer($shopCate, 0);
        $this->cate = $cate;
        // p($cate);return;
        $cid = I('get.cid');
        $map['shopId'] = $id;
        $map['goodsStatus'] = 1;
        $map['goodsFlag'] = 1;
        if (! empty($cid)) {
            $map['shopCatId2'] = $cid;
        }
        $goods = M('goods')->where($map)->select();
        $this->goods = $goods;
        $this->display();
    }

    public function unlimitedForLayer($cate, $name = 'child', $pid = 0)
    {
        $arr = array();
        $name = 'child';
        foreach ($cate as $v) {
            if ($v['parentId'] == $pid) {
                $v[$name] = self::unlimitedForLayer($cate, $name, $v['catId']);
                $arr[] = $v;
            }
        }
        return $arr;
    }
}