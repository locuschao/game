<?php
namespace Home\Model;

/**
 * 购物车服务类
 */
class CartModel extends BaseModel
{

    protected $tableName = 'goods';

    /**
     * 添加[正常]商品到购物车
     */
    public function addToCart()
    {
        // 判断一下该商品是否正常出售
        $isGroup = (int) I("isGroup", 0);
        $isSeckill = (int) I("isSeckill", 0);
        $id = I("goodsAttrId");
        $goodsInfo = self::getGoodsInfo((int) I("goodsId"), I("goodsAttrId"), $isGroup, $isSeckill);
        // return $goodsInfo['shopPrice'];
        if ($goodsInfo['goodsId'] == '')
            return array();
        $goodsInfo["cnt"] = ((int) I("gcount") > 0) ? (int) I("gcount") : 1;
        $goodsInfo["ischk"] = 1;
        $cartgoods = array();
        $totalMoney = 0;
        $WST_CART = session("WST_CART");
        // 如果购物车为空则放入购物车中
        if (empty($WST_CART)) {
            $shopcat = array();
            $goodsAttrId = '';
            $price = 0.0;
            foreach ($goodsInfo as $k => $v) {
                if (is_numeric($k)) {
                    $goodsAttrId .= $goodsInfo[$k]['goodsAttrId'] . '_';
                    $price += $goodsInfo[$k]['shopPrice'];
                }
            }
            // 如果是团购商品则直接显示团购价
            
            if ($price != 0 && $isGroup == 0 && $isSeckill == 0) {
                $goodsInfo['shopPrice'] = $price;
            }
            
            $goodsAttrId = trim($goodsAttrId, '_');
            // 设置默认值为0
            if ($goodsAttrId == '') {
                $goodsAttrId = '0';
            }
            $shopcat[$goodsInfo["goodsId"] . "_" . $goodsAttrId . "_" . $isSeckill . "_" . $isGroup] = $goodsInfo;
            $totalMoney += $goodsInfo["cnt"] * $goodsInfo["shopPrice"];
            $cartgoods[] = $goodsInfo;
            
            session("WST_CART", $shopcat);
        } else {
            // 如果购物车不为空则要看下该商品是否原来就存在了。
            $shopcat = $WST_CART;
            $goodsAttrId = '';
            // session商品键加入属性id
            foreach ($goodsInfo as $k => $v) {
                if (is_numeric($k)) {
                    $goodsAttrId .= $goodsInfo[$k]['goodsAttrId'] . '_';
                }
            }
            $goodsAttrId = trim($goodsAttrId, '_');
            // 设置默认值为0
            if ($goodsAttrId == '') {
                $goodsAttrId = '0';
            }
            // $a=($goodsAttrId!='0');
            // return $a;
            
            // 如果已经存在则要把数量相加
            if (! empty($shopcat[$goodsInfo['goodsId'] . "_" . $goodsAttrId . "_" . $isSeckill . "_" . $isGroup])) {
                // 如果是秒杀商品，购物车该商品的数量不得大于该（商品限购的数量 - 该用户已购数量(已下单) - 购物车该商品已购数量（未下单））
                if ($isSeckill) {
                    $tempCnt = $goodsInfo['cnt'];
                    // 返回 商品限购的数量 - 该用户已购数量(已下单) - 购物车该商品已购数量（未下单）的值
                    $tempSk = $this->getLimitSkGoodsNums($goodsInfo['goodsId'], $goodsAttrId, $isSeckill, $isGroup, $WST_CART);
                    // 如果购买数量大于限购数量，则购买数量默认等于限购数量
                    if ($tempCnt > $tempSk) {
                        $tempCnt = $tempSk;
                    }
                    $goodsInfo["cnt"] = $WST_CART[$goodsInfo["goodsId"] . "_" . $goodsAttrId . "_" . $isSeckill . "_" . $isGroup]["cnt"] + $tempCnt;
                } else {
                    $goodsInfo["cnt"] = $WST_CART[$goodsInfo["goodsId"] . "_" . $goodsAttrId . "_" . $isSeckill . "_" . $isGroup]["cnt"] + $goodsInfo["cnt"];
                }
            } elseif ($isSeckill && $goodsAttrId) {
                // 新添加入购物车的 秒杀带属性的商品，则要进行判断是否已经达到限购上限
                $tempCnt = $goodsInfo["cnt"];
                $tempSk = $this->getLimitSkGoodsNums($goodsInfo['goodsId'], $goodsAttrId, $isSeckill, $isGroup, $WST_CART);
                if ($tempCnt > $tempSk) {
                    $tempCnt = $tempSk;
                }
                $goodsInfo["cnt"] = $tempCnt;
            }
            if ($goodsInfo['cnt'] > 0) {
                $shopcat[$goodsInfo["goodsId"] . "_" . $goodsAttrId . "_" . $isSeckill . "_" . $isGroup] = $goodsInfo;
            }
            
            // 重新把购物车内的数据拿到外边
            foreach ($shopcat as $key => $cgoods) {
                $totalMoney += $cgoods["cnt"] * $cgoods["shopPrice"];
                $cartgoods[] = $cgoods;
            }
            session("WST_CART", $shopcat);
            return $shopcat;
        }
        $cartInfo = array();
        $cartInfo["cartgoods"] = $cartgoods;
        $cartInfo["totalMoney"] = 100;
        return $id;
    }
    // 秒杀商品加入购物车之前得到该秒杀商品允许增加的最大数量 05092
    public function getLimitSkGoodsNums($goodsInfoGoodsId, $goodsAttrId, $isSeckill, $isGroup, $WST_CART)
    {
        $m = M('GoodsSeckill');
        $tempSk = $m->where('goodsId=' . $goodsInfoGoodsId)->find();
        // 如果存在用户，得出该用户已购数量，-----> 购物车限购数量=该商品限购数量-用户已购数量（已下单）-购物车已购数量（未下单）
        $USER = session('WST_USER');
        $userId = $USER['userId'];
        if ($userId) {
            $orderArr = M('OrderGoods')->where('goodsId=' . $goodsInfoGoodsId . ' AND isSeckillGood=1 ')
                ->field('orderId,goodsNums')
                ->order('orderId asc')
                ->select();
            // print_r($orderArr);
            $USERARR = array();
            foreach ($orderArr as $k => $v) {
                $USERARR[] = M('Orders')->where('orderId=' . $v['orderId'])
                    ->field('userId')
                    ->find();
            }
            
            foreach ($USERARR as $k => $v) {
                $goodsNums = 0;
                if ($v['userId'] == $userId) {
                    $goodsNums += $orderArr[$k]['goodsNums'];
                }
                $tempSk['seckillMaxCount'] = $tempSk['seckillMaxCount'] - $goodsNums;
            }
            // return $tempSk['seckillMaxCount'];
            // 购物车已购数量
            $cartNum = 0;
            
            foreach ($WST_CART as $k => $v) {
                if (preg_match('/' . $goodsInfoGoodsId . "_" . '.+' . "_" . $isSeckill . "_" . $isGroup . '/', $k)) {
                    $cartNum += $v['cnt'];
                }
            }
            
            //
            $tempSk['seckillMaxCount'] = $tempSk['seckillMaxCount'] - $cartNum;
            
            // 和库存进行比较
            if (! ! $goodsAttrId) {
                $attrArr = explode('_', $goodsAttrId);
                $m = M('GoodsAttributes');
                // 得到属性库存最小的
                foreach ($attrArr as $k => $v) {
                    $attrTemp = $m->where('goodsId=' . $goodsInfoGoodsId . ' AND id=' . $v)
                        ->field('skAttrStock,id')
                        ->find();
                    if ($k == 0) {
                        $minAttrStock = $attrTemp['skAttrStock'];
                        $minAttrId = $attrTemp['id'];
                    } else {
                        if ($attrTemp['skAttrStock'] < $minAttrStock) {
                            $minAttrStock = $attrTemp['skAttrStock'];
                            $minAttrId = $attrTemp['id'];
                        }
                    }
                }
                $attrStockNum = 0;
                foreach ($WST_CART as $k => $v) {
                    if (preg_match('/' . $goodsInfoGoodsId . "_" . '.+' . "_" . $isSeckill . "_" . $isGroup . '/', $k)) {
                        if (preg_match('/.+?' . $minAttrId . '.+?/', $k)) {
                            $attrStockNum += $v['cnt'];
                        }
                    }
                }
                $minAttrStock = $minAttrStock - $attrStockNum;
                
                if ($tempSk['seckillMaxCount'] > $minAttrStock)
                    $tempSk['seckillMaxCount'] = $minAttrStock;
            } else {
                $g = M('GoodsSeckill');
                $good = $g->where('goodsId=' . $goodsInfoGoodsId)
                    ->field('seckillStock')
                    ->find();
                $goodStockNum = 0;
                foreach ($WST_CART as $k => $v) {
                    if (preg_match('/' . $goodsInfoGoodsId . "_" . '.+' . "_" . $isSeckill . "_" . $isGroup . '/', $k)) {
                        $goodStockNum += $v['cnt'];
                    }
                }
                $good['seckillStock'] = $good['seckillStock'] - $goodStockNum;
                if ($tempSk['seckillMaxCount'] > $good['seckillStock'])
                    $tempSk['seckillMaxCount'] = $good['seckillStock'];
            }
        }
        return $tempSk['seckillMaxCount'];
    }
    // 在购物车更改秒杀商品数量时，得到该秒杀商品允许购买的最大数量
    public function getCartLimitSkGoodsNums($goodsInfoGoodsId, $goodsInfoGoodsAttrId, $isSeckill, $isGroup, $WST_CART)
    {
        $m = M('GoodsSeckill');
        $tempSk = $m->where('goodsId=' . $goodsInfoGoodsId)->find();
        // 如果存在用户，得出该用户已购数量，-----> 购物车限购数量=该商品限购数量-用户已购数量（已下单）-购物车已购数量（未下单）
        $USER = session('WST_USER');
        $userId = $USER['userId'];
        if ($userId) {
            $orderArr = M('OrderGoods')->where('goodsId=' . $goodsInfoGoodsId . ' AND isSeckillGood=1 ')
                ->field('orderId,goodsNums')
                ->order('orderId asc')
                ->select();
            // print_r($orderArr);
            $USERARR = array();
            foreach ($orderArr as $k => $v) {
                $USERARR[] = M('Orders')->where('orderId=' . $v['orderId'])
                    ->field('userId')
                    ->find();
            }
            
            foreach ($USERARR as $k => $v) {
                $goodsNums = 0;
                if ($v['userId'] == $userId) {
                    $goodsNums += $orderArr[$k]['goodsNums'];
                }
                $tempSk['seckillMaxCount'] = $tempSk['seckillMaxCount'] - $goodsNums;
            }
            // return $tempSk['seckillMaxCount'];
            // 购物车已购数量
            $cartNum = 0;
            
            foreach ($WST_CART as $k => $v) {
                if (preg_match('/' . $goodsInfoGoodsId . "_" . '.+' . "_" . $isSeckill . "_" . $isGroup . '/', $k)) {
                    $cartNum += $v['cnt'];
                }
                // 不包括指定的秒杀商品的数量
                if (preg_match('/' . $goodsInfoGoodsId . "_" . $goodsInfoGoodsAttrId . "_" . $isSeckill . "_" . $isGroup . '/', $k)) {
                    $cartNum -= $v['cnt'];
                }
            }
            
            //
            $tempSk['seckillMaxCount'] = $tempSk['seckillMaxCount'] - $cartNum;
            // return $tempSk['seckillMaxCount'];
        }
        return $tempSk['seckillMaxCount'];
    }

    /**
     * 获取商品信息0509
     */
    public function getGoodsInfo($goodsId, $goodsAttrId = 0, $isGroup, $isSeckill)
    {
        $sql = "SELECT g.attrCatId,g.goodsId,g.isGroup,g.goodsSn,g.goodsName,g.goodsThums,g.shopId,g.marketPrice,g.shopPrice,g.goodsStock,g.bookQuantity,g.isBook,sp.shopName
				FROM __PREFIX__goods g ,__PREFIX__shops sp WHERE g.shopId=sp.shopId AND goodsFlag=1 and isSale=1 and goodsStatus=1 and g.goodsId = $goodsId";
        $goodslist = $this->query($sql);
        // 如果选择的是团购商品
        if ($isGroup == 1) {
            $goodsGroupModel = M('GoodsGroup');
            $group = $goodsGroupModel->where("goodsId = $goodsId and goodsGroupStatus = 1 and groupStatus = 1")->find();
            // 加入团购信息
            $goodslist[0] = array_merge($goodslist[0], $group);
            // 把商品价变为团购价
            $goodslist[0]['shopPrice'] = $goodslist[0]['groupPrice'];
        }
        // 如果选择的是秒杀商品
        if ($isSeckill == 1) {
            $goodsGroupModel = M('GoodsSeckill');
            $group = $goodsGroupModel->where("goodsId = $goodsId")->find();
            // 加入团购信息
            $goodslist[0] = array_merge($goodslist[0], $group);
            // 如果商品有价格属性的话则获取其价格属性
            if (! empty($goodslist) && $goodslist[0]['attrCatId'] > 0) {
                $price = 0;
                $sql = "select ga.id,ga.skAttrPrice,ga.skAttrStock,a.attrName,ga.attrVal,ga.attrId from __PREFIX__attributes a,__PREFIX__goods_attributes ga
						where a.attrId=ga.attrId and a.catId=" . $goodslist[0]['attrCatId'] . " and a.isPriceAttr=1
						and ga.goodsId=" . $goodslist[0]['goodsId'] . " and id in(" . $goodsAttrId . ") order by ga.id asc";
                $priceAttrs = $this->query($sql);
                if (! empty($priceAttrs)) {
                    foreach ($priceAttrs as $k => $v) {
                        $goodslist[0][$k]['attrId'] = $v['attrId'];
                        $goodslist[0][$k]['goodsAttrId'] = $v['id'];
                        $goodslist[0][$k]['attrName'] = $v['attrName'];
                        $goodslist[0][$k]['attrVal'] = $v['attrVal'];
                        $goodslist[0][$k]['shopPrice'] = $v['skAttrPrice'];
                        $goodslist[0][$k]['goodsStock'] = $v['skAttrStock'];
                        $price += $v['skAttrPrice'];
                    }
                }
            }
            // 把商品价变为团购价
            if ($price != 0) {
                $goodslist[0]['shopPrice'] = $price;
            } else {
                $goodslist[0]['shopPrice'] = $goodslist[0]['seckillPrice'];
            }
        } else {
            // 如果商品有价格属性的话则获取其价格属性
            if (! empty($goodslist) && $goodslist[0]['attrCatId'] > 0) {
                $sql = "select ga.id,ga.attrPrice,ga.attrStock,a.attrName,ga.attrVal,ga.attrId from __PREFIX__attributes a,__PREFIX__goods_attributes ga
			        where a.attrId=ga.attrId and a.catId=" . $goodslist[0]['attrCatId'] . " and a.isPriceAttr=1
			        and ga.goodsId=" . $goodslist[0]['goodsId'] . " and id in(" . $goodsAttrId . ") order by ga.id asc";
                $priceAttrs = $this->query($sql);
                if (! empty($priceAttrs)) {
                    foreach ($priceAttrs as $k => $v) {
                        $goodslist[0][$k]['attrId'] = $v['attrId'];
                        $goodslist[0][$k]['goodsAttrId'] = $v['id'];
                        $goodslist[0][$k]['attrName'] = $v['attrName'];
                        $goodslist[0][$k]['attrVal'] = $v['attrVal'];
                        $goodslist[0][$k]['shopPrice'] = $v['attrPrice'];
                        $goodslist[0][$k]['goodsStock'] = $v['attrStock'];
                    }
                }
            }
        }
        
        return $goodslist[0];
    }

    /**
     * 获取购物车信息 05092
     */
    public function getCartInfo()
    {
        $mgoods = D('Home/Goods');
        $totalMoney = 0;
        $shopcart = session("WST_CART") ? session("WST_CART") : array();
        $cartgoods = array();
        foreach ($shopcart as $goodsId => $cgoods) {
            $temp = explode('_', $goodsId);
            // 获取团购标志
            $isGroup = $temp[count($temp) - 1];
            $isSeckill = $temp[count($temp) - 2];
            // 去除末尾团购标志
            array_pop($temp);
            // 去除末尾秒杀标志
            array_pop($temp);
            $goodsAttrId = array();
            foreach ($temp as $k => $v) {
                if ($k == 0) {
                    $goodsId = (int) $v;
                } else {
                    $goodsAttrId[] = (int) $v;
                }
            }
            $goodsAttrId = implode(',', $goodsAttrId);
            $sql = "SELECT  g.goodsThums,g.goodsId,g.shopPrice,g.isBook,g.goodsName,g.shopId,g.goodsStock,g.shopPrice,shop.shopName,shop.qqNo,shop.deliveryType,shop.shopAtive,
					shop.shopTel,shop.shopAddress,shop.deliveryTime,shop.isInvoice, shop.deliveryStartMoney,
					shop.deliveryFreeMoney,shop.deliveryMoney ,g.goodsSn,shop.serviceStartTime,shop.serviceEndTime
					FROM __PREFIX__goods g, __PREFIX__shops shop
					WHERE g.goodsId = $goodsId AND g.shopId = shop.shopId AND g.goodsFlag = 1 and g.isSale=1 and g.goodsStatus=1 ";
            $goods = self::queryRow($sql);
            if ($goods["isBook"] == 1) {
                $goods["goodsStock"] = $goods["goodsStock"] + $goods["bookQuantity"];
            }
            // 如果是团购商品
            if ($isGroup) {
                $goodsGroupModel = M('GoodsGroup');
                $group = $goodsGroupModel->where("goodsId = $goodsId and goodsGroupStatus = 1 and groupStatus = 1")->find();
                // 加入团购信息
                $goods = array_merge($goods, $group);
                // 把商品价变为团购价
                $goods['shopPrice'] = $goods['groupPrice'];
                $goods["goodsStock"] = $goods['groupMaxCount'];
            }
            // 如果是秒杀商品
            if ($isSeckill) {
                $goodsGroupModel = M('GoodsSeckill');
                $group = $goodsGroupModel->where("goodsId = $goodsId")->find();
                // 加入秒杀信息
                $goods = array_merge($goods, $group);
                $goods["goodsStock"] = $group['seckillStock'];
                $goods["shopPrice"] = $group['seckillPrice'];
                // 把商品价变为秒杀价
                // 如果商品有价格属性的话则获取其价格属性
                if (! empty($goods) && $cgoods['attrCatId'] > 0) {
                    $sql = "select ga.id,ga.skAttrPrice,ga.skAttrStock,a.attrName,ga.attrVal,ga.attrId from __PREFIX__attributes a,__PREFIX__goods_attributes ga
			             where a.attrId=ga.attrId and a.catId=" . $cgoods['attrCatId'] . " and a.isPriceAttr=1
			             and ga.goodsId=" . $goodsId . " and id in(" . $goodsAttrId . ") order by ga.id asc";
                    $priceAttrs = $this->query($sql);
                    if (! empty($priceAttrs)) {
                        foreach ($priceAttrs as $k => $v) {
                            $goods[$k]['shopPrice'] = $v['skAttrPrice'];
                            $goods[$k]['goodsAttrId'] = $v['id'];
                            $goods[$k]['attrName'] = $v['attrName'];
                            $goods[$k]['attrVal'] = $v['attrVal'];
                            $goods[$k]['goodsStock'] = $v['skAttrStock'];
                        }
                    }
                }
                $price = 0;
                foreach ($goods as $k => $v) {
                    if (is_numeric($k)) {
                        if ($k == 0)
                            $minStock = $v["goodsStock"];
                        $price += $v["shopPrice"];
                        if ($minStock > $v['goodsStock'])
                            $minStock = $v['goodsStock'];
                    }
                }
                if ($price != 0 && $isSeckill == 1) {
                    $goods['shopPrice'] = $price;
                    $goods['goodsStock'] = $minStock;
                } else {
                    $goods['shopPrice'] = $goods['seckillPrice'];
                }
            } else {
                if (! empty($goods) && $cgoods['attrCatId'] > 0) {
                    $sql = "select ga.id,ga.attrPrice,ga.attrStock,a.attrName,ga.attrVal,ga.attrId from __PREFIX__attributes a,__PREFIX__goods_attributes ga
			             where a.attrId=ga.attrId and a.catId=" . $cgoods['attrCatId'] . " and a.isPriceAttr=1
			             and ga.goodsId=" . $goodsId . " and id in(" . $goodsAttrId . ") order by ga.id asc";
                    $priceAttrs = $this->query($sql);
                    if (! empty($priceAttrs)) {
                        foreach ($priceAttrs as $k => $v) {
                            $goods[$k]['goodsAttrId'] = $v['id'];
                            $goods[$k]['attrName'] = $v['attrName'];
                            $goods[$k]['attrVal'] = $v['attrVal'];
                            $goods[$k]['shopPrice'] = $v['attrPrice'];
                            $goods[$k]['goodsStock'] = $v['attrStock'];
                        }
                    }
                }
            }
            
            // 如果商品有价格属性的话则获取其价格属性
            
            $goods["goodsAttrId"] = str_replace(',', '_', $goodsAttrId);
            $goods["cnt"] = $cgoods["cnt"];
            $goods["ischk"] = $cgoods["ischk"];
            $goods['isGroup'] = $isGroup;
            $goods['isSeckill'] = $isSeckill;
            $totalCnt += $cgoods["cnt"];
            $price = 0;
            foreach ($goods as $k => $v) {
                if (is_numeric($k)) {
                    $price += $v["shopPrice"];
                }
            }
            if ($price != 0 && $isGroup != 1 && $isSeckill != 1) {
                $shopPrice = $price;
            } else {
                $shopPrice = $goods["shopPrice"];
            }
            if ($startTime < $goods["startTime"]) {
                $startTime = $goods["startTime"];
            }
            if ($endTime > $goods["endTime"]) {
                $endTime = $goods["endTime"];
            }
            $cartgoods[$goods["shopId"]]["shopgoods"][] = $goods;
            $cartgoods[$goods["shopId"]]["deliveryFreeMoney"] = $goods["deliveryFreeMoney"]; // 店铺免运费最低金额
            $cartgoods[$goods["shopId"]]["deliveryMoney"] = $goods["deliveryMoney"]; // 店铺配送费
            $cartgoods[$goods["shopId"]]["deliveryStartMoney"] = $goods["deliveryStartMoney"]; // 店铺配送费
            $cartgoods[$goods["shopId"]]["totalCnt"] = $cartgoods[$goods["shopId"]]["totalCnt"] + $cgoods["cnt"];
            $cartgoods[$goods["shopId"]]["totalMoney"] = $cartgoods[$goods["shopId"]]["totalMoney"] + ($goods["cnt"] * $shopPrice);
            $totalMoney += $goods["cnt"] * $shopPrice;
        }
        /*
         * 暂时没发现有什么用
         * foreach($catgoods as $key=> $cshop){
         * if($cshop["totalMoney"]<$cshop["deliveryFreeMoney"]){
         * $totalMoney = $totalMoney + $cshop["deliveryMoney"];
         * }
         * }
         */
        $cartInfo = array();
        $cartInfo["totalMoney"] = $totalMoney;
        $cartInfo["cartgoods"] = $cartgoods;
        return $cartInfo;
    }

    /**
     * 检测购物车中商品库存05092
     */
    public function checkCatGoodsStock()
    {
        $shopcart = session("WST_CART") ? session("WST_CART") : array();
        $mgoods = D('Home/Goods');
        $cartgoods = array();
        foreach ($shopcart as $key => $cgoods) {
            $temp = explode('_', $key);
            $goodsId = (int) $temp[0];
            $goodsAttrId = (int) $temp[1];
            $isGroup = (int) $temp[count($temp) - 1];
            $isSeckill = (int) $temp[count($temp) - 2];
            $obj = array();
            $obj["goodsId"] = $goodsId;
            $obj["goodsAttrId"] = $goodsAttrId;
            $obj["isGroup"] = $isGroup;
            $obj["isSeckill"] = $isSeckill;
            $goods = $mgoods->getGoodsStock($obj);
            if ($goods["isBook"] == 1) {
                $goods["goodsStock"] = $goods["goodsStock"] + $goods["bookQuantity"];
            }
            $goods["cnt"] = $cgoods["cnt"];
            $goods["stockStatus"] = ($goods["goodsStock"] >= $goods["cnt"]) ? 1 : 0;
            $cartgoods[] = $goods;
        }
        
        return $cartgoods;
    }

    /**
     * 删除购物车中的商品
     */
    public function delCartGoods()
    {
        if (I("goodsAttrId")) {
            $goodsKey = (int) I("goodsId") . "_" . I("goodsAttrId") . "_" . (int) I("isSeckill") . "_" . (int) I("isGroup");
        } else {
            $goodsKey = (int) I("goodsId") . "_0_" . (int) I("isSeckill") . "_" . (int) I("isGroup");
            // $goodsKey = (int)I("goodsId")."__0_1"; qiuck_links.js中的removeCartGoods函数只传了goodsid；
        }
        $shopcart = session("WST_CART") ? session("WST_CART") : array();
        session("WST_CART", null);
        $newShopcat = array();
        foreach ($shopcart as $key => $cgoods) {
            if ($goodsKey != $key) {
                $newShopcat[$key] = $cgoods;
            }
        }
        session("WST_CART", $newShopcat);
        return 1;
    }

    /**
     * 修改购物车中的商品数量
     */
    public function changeCartGoodsnum()
    {
        $flag = true;
        if (I("goodsAttrId")) {
            $goodsKey = (int) I("goodsId") . "_" . I("goodsAttrId") . "_" . (int) I("isSeckill") . "_" . (int) I("isGroup");
        } else {
            $goodsKey = (int) I("goodsId") . "_0_" . (int) I("isSeckill") . "_" . (int) I("isGroup");
        }
        $num = abs((int) I("num"));
        // 如果是秒杀商品 限制数量=该商品限购数量-当前用户已购数量-购物车该商品的的数量
        if ((int) I("isSeckill")) {
            $WST_CART = session("WST_CART");
            $skGoodMaxNum = $this->getCartLimitSkGoodsNums((int) I('goodsId'), I("goodsAttrId"), (int) I('isSeckill'), (int) I('isGroup'), $WST_CART);
            if ($num > $skGoodMaxNum) {
                $num = $skGoodMaxNum;
                $flag = false;
            }
        }
        
        $ischk = (int) I("ischk", 0);
        $shopcart = session("WST_CART") ? session("WST_CART") : array();
        session("WST_CART", null);
        $newShopcart = array();
        foreach ($shopcart as $key => $cgoods) {
            $cartgoods = $shopcart[$key];
            if ($goodsKey == $key) {
                $cartgoods["cnt"] = $num;
                $cartgoods["ischk"] = $ischk;
            }
            $newShopcart[$key] = $cartgoods;
        }
        
        session("WST_CART", $newShopcart);
        if ($flag) {
            return 1;
        } else {
            return 2;
        }
    }
}