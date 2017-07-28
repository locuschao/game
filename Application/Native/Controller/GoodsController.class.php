<?php
namespace Native\Controller;

use Think\Controller;
use Think\Model;

class GoodsController extends BaseController
{

    public function _initialize()
    {
        @header('Content-type: text/html;charset=UTF-8');
    }

    public function index()
    {
        $id = I('goodsId');
        if (! $id) {
            $this->returnJson(array());
        }
        $field = "g.gameId,g.applyTo,g.goodsName,g.goodsImg,g.goodsThums,g.shopId,g.shopPrice,g.marketPrice,g.goodsSpec,g.goodsType,g.scopeId,g.isMiao,s.shopName,s.shopImg";
        $goodsInfo = M('goods')->where(array(
            'g.goodsId' => $id
        ))
            ->field($field)
            ->join('as g left join  oto_shops as s on s.shopId=g.shopId')
            ->find();
        $goodsInfo['catName'] = '首充号';
        if ($goodsInfo['scopeId'] == 1) {
            $goodsInfo['catName'] = '首充号';
        } else 
            if ($goodsInfo['scopeId'] == 2) {
                $goodsInfo['catName'] = '首充号代充';
            } 
        $sales = M('order_goods')->where(array(
            'o.orderStatus' => array(
                'gt',
                0
            ),
            'o.shopId' => $goodsInfo['shopId']
        ))
            ->join('as g join oto_orders as o on o.orderId=g.orderId')
            ->SUM('goodsNums');
        
        $sales = $sales > 0 ? $sales : 0;
        $goodsAttr = M('goods_versions as gv')->where(array(
            'gv.goodsId' => $id
        ))
            ->field('gv.*,v.vName')
            ->join('oto_versions as v on v.id=gv.versionsId')
            ->select();
        
        if (! empty($goodsAttr)) {
            foreach ($goodsAttr as $k => $v) {
                $goodsAttr[$k]['zhekou'] = sprintf('%.1f', ($v['attrPrice'] / $goodsInfo['marketPrice']) * 10);
            }
        }
        
        $gameName = M('game')->where(array(
            'id' => $goodsInfo['gameId']
        ))->getField('gameName');
        $shopScope = M('shops')->where(array(
            'shopId' => $goodsInfo['shopId']
        ))->getField('scope');
        $fanwei = '';
        $temp = explode(',', $shopScope);
        foreach ($temp as $v) {
            if ($v == 1) {
                $fanwei .= '首充号,';
            } else 
                if ($v == 2) {
                    $fanwei .= '首充号代充,';
                } 
        }
        $fanwei = trim($fanwei, ',');
        $arr = array();
        $arr['fanwei'] = $fanwei;
        $arr['goodsInfo'] = $goodsInfo;
        $arr['sales'] = $sales;
        $arr['gameName'] = $gameName;
        $arr['goodsAttr'] = $goodsAttr;
        $this->returnJson($arr);
    }

    public function goodsSearch()
    {
        $page = I('page', 0, 'intval');
        $key = I('key');
        $where['g.goodsFlag'] = 1;
        $where['g.goodsStatus'] = 1;
        $where['g.isSale'] = 1;
        if ($key) {
            $where['ga.gameName'] = array(
                'like',
                "%$key%"
            );
        }
        $field = "g.*,ga.gameName,ga.gameIco";
        $goodsList = M('goods as g')->join('oto_game as ga on ga.id=g.gameId')
            ->field($field)
            ->where($where)
            ->group('g.gameId')
            ->order('goodsId DESC')
            ->limit($page * 30, 30)
            ->select();
        foreach ($goodsList as $k => $v) {
            $where['gameId'] = $v['gameId'];
            $type = M('goods as g')->join('oto_game as ga on ga.id=g.gameId')
                ->field('scopeId')
                ->where($where)
                ->group('g.scopeId')
                ->order('goodsId DESC')
                ->limit($page * 30, 30)
                ->select();
            foreach ($type as $kk => $vv) {
                if (! empty($vv['scopeId']) && $vv['scopeId'] > 0) {
                    if ($vv['scopeId'] == 1) {
                        $type[$kk]['type'] = '首充号';
                    } else 
                        if ($vv['scopeId'] == 2) {
                            $type[$kk]['type'] = '首充号代充';
                        }
                } else {
                    unset($type[$kk]);
                }
                $goodsList[$k]['type'] = $type;
            }
        }
        $this->returnJson($goodsList);
    }
    
    // 检查库存
    public function stock()
    {
        // 判断用户代充，如果是代充，则跳转到以前的店铺去
        // 检查库存
        if (IS_AJAX) {
            $attrid = I('attrid', 0);
            $goosdId = I('goodsId', 0);
            $info = M('goods')->where(array(
                'goodsId' => $goosdId
            ))
                ->field('scopeId,shopId,gameId')
                ->find();
            /*
             * if($info['scopeId']==2){
             * $buyShopId=M('order_goods as og')->where(array('og.goodsId'=>$goosdId,'og.gid'=>$info['gameId'],'o.userId'=>session('oto_userId')))
             * ->join('oto_orders as o on o.orderId=og.orderId')
             * ->getField('o.shopId');
             * if($buyShopId){
             * if($buyShopId!=$info['shopId']){
             * $this->ajaxReturn(array('status'=>0,'num'=>0,'shopId'=>$buyShopId));
             * }
             * }else{
             * $this->ajaxReturn(array('status'=>-1,'num'=>0,'shopId'=>$buyShopId));
             * }
             * }
             */
            $stock = 0;
            if ($attrid > 0) {
                $stock = M('goods_versions')->where(array(
                    'id' => $attrid
                ))->getField('attrStock');
            } else {
                $stock = M('goods')->where(array(
                    'goodsId' => $goosdId
                ))->getField('goodsStock');
            }
            $this->ajaxReturn(array(
                'num' => $stock
            ));
        }
    }

    public function goodsType()
    {
        $key = I('key');
        $letter = I('letter');
        $type = I('type');
        $page = I('page', 0);
        if ($letter && $letter != 'Hot') {
            $PY = <<<Eof
	    ELT(INTERVAL(CONV(HEX(left(CONVERT(gameName USING gbk),1)),16,10),
0xB0A1,0xB0C5,0xB2C1,0xB4EE,0xB6EA,0xB7A2,0xB8C1,0xB9FE,0xBBF7,
0xBFA6,0xC0AC,0xC2E8,0xC4C3,0xC5B6,0xC5BE,0xC6DA,0xC8BB,0xC8F6,
0xCBFA,0xCDDA,0xCEF4,0xD1B9,0xD4D1),
'A','B','C','D','E','F','G','H','J','K','L','M','N','O','P',
'Q','R','S','T','W','X','Y','Z') 
Eof;
        }
        
        $where = "where g.isDel=0 and go.goodsType=0 ";
        if ($key) {
            $where .= " and g.gameName like '%$key%' ";
        }
        
        if ($letter == 'Hot') {
            $where .= " and  g.isHot=1 ";
        }
        
        if ($PY) {
            $where .= " and $PY='$letter' ";
        }
        /*
         * if($name){
         * $where.=" and c.catName='$name'";
         * }
         */
        $order = 'order by g.id DESC';
        
        if ($letter == 'Hot') {
            $order = 'order by g.isHot DESC';
        }
        
        $scope = '';
        $join = '';
        switch ($type) {
            case 'sc':
                $join .= " join __PREFIX__goods  as go on go.gameId=g.id and go.scopeId=1";
                break;
            case 'dc':
                $join .= " join __PREFIX__goods  as go on go.gameId=g.id and go.scopeId=2";
                break;
            default:
                $join .= " join __PREFIX__goods  as go on go.gameId=g.id and go.scopeId=1";
        }
        
        $limit = $page * 30;
        $sql = "select g.* from __PREFIX__game as g   $join  $where  group by go.gameId  $order limit  $limit,30";
        $goodsInfo = M()->query($sql);
        $this->returnJson($goodsInfo);
    }

    public function memberGoods()
    {
        $key = I('key');
        $letter = I('letter');
        $type = I('type');
        $page = I('page', 0, 'intval');
        
        if ($letter && $letter != 'Hot') {
            $PY = <<<Eof
	    ELT(INTERVAL(CONV(HEX(left(CONVERT(gameName USING gbk),1)),16,10),
0xB0A1,0xB0C5,0xB2C1,0xB4EE,0xB6EA,0xB7A2,0xB8C1,0xB9FE,0xBBF7,
0xBFA6,0xC0AC,0xC2E8,0xC4C3,0xC5B6,0xC5BE,0xC6DA,0xC8BB,0xC8F6,
0xCBFA,0xCDDA,0xCEF4,0xD1B9,0xD4D1),
'A','B','C','D','E','F','G','H','J','K','L','M','N','O','P',
'Q','R','S','T','W','X','Y','Z') 
Eof;
        }
        
        $where = "where g.isDel=0 and go.goodsType=1 ";
        if ($key) {
            $where .= " and g.gameName like '%$key%' ";
        }
        
        if ($letter == 'Hot') {
            $where .= " and  g.isHot=1 ";
        }
        
        if ($PY) {
            $where .= " and $PY='$letter' ";
        }
        /*
         * if($name){
         * $where.=" and c.catName='$name'";
         * }
         */
        $order = 'order by g.id DESC';
        
        if ($letter == 'Hot') {
            $order = 'order by g.isHot DESC';
        }
        
        $scope = '';
        $join = '';
        switch ($type) {
            case 'sc':
                $join .= " join __PREFIX__goods  as go on go.gameId=g.id and go.scopeId=1";
                break;
            case 'dc':
                $join .= " join __PREFIX__goods  as go on go.gameId=g.id and go.scopeId=2";
                break;
            default:
                $join .= " join __PREFIX__goods  as go on go.gameId=g.id and go.scopeId=1";
        }
        
        $limit = $page * 30;
        $sql = "select g.* from __PREFIX__game as g   $join  $where  group by go.gameId  $order limit  $limit,30";
        $goodsInfo = M()->query($sql);
        
        $this->returnJson($goodsInfo);
    }
    
    // 游戏信息
    public function gameName()
    {
        $gameId = I('gameId', 0, 'intval');
        $gameName = M('game')->where(array(
            'id' => $gameId
        ))->getField('gameName');
        $this->returnJson($gameName);
    }
    
    // 搜索结果点击后跳转到列表页
    public function goodsList()
    {
        $gameId = I('gameId', 0);
        $type = I('gameScope', '', 'intval') ? I('gameScope') : 1;
        $page = I('page', 0);
        if ($type == 1) {
            $type = 1; // 普通 首充号
            $where['scopeId'] = 1;
        } else 
            if ($type == 2) {
                $type = 2; // 普通代充号
                $where['scopeId'] = 2;
            } else 
                if ($type == 3) {
                    unset($where['scopeId']);
                    $type = 3; // 会员 商品
                }
        $field = "g.goodsType,g.goodsName,g.goodsId,g.goodsSpec,g.marketPrice,g.shopPrice,g.scopeId,g.isMiao ,(
		SELECT
			attrPrice
		FROM
			oto_goods_versions AS gv
		WHERE
			gv.goodsId = g.goodsId
		ORDER BY
			attrPrice ASC
		LIMIT 1
	) AS attrPrice  ";
        $where['g.goodsFlag'] = 1;
        $where['g.goodsStatus'] = 1;
        $where['g.isSale'] = 1;
        $where['g.gameId'] = $gameId;
        if ($type == 3) {
            $where['g.goodsType'] = 1; // 会员商品
        } else {
            $where['g.goodsType'] = 0; // 普通 商品
        }
        $where['isAdminRecom'] = 1; // 平台推荐的才可以在此页显示
        $moreInf = M('goods as g')->field($field)
            ->where($where)
            ->limit($page * 30, 30)
            ->order('g.shopPrice ASC')
            ->having(' attrPrice >0 ')
            ->select();
        
        if (! empty($moreInf)) {
            $moreInf[0]['Flag'] = $type;
        }
        $this->returnJson($moreInf);
    }
    
    
    // 会员搜索结果点击后跳转到列表页
    public function memberGoodsList()
    {
        $gameId = I('gameId', 0);
        $type = I('gameScope', '', 'intval') ? I('gameScope') : 1;
        $page = I('page', 0);
        if ($type == 1) {
            $type = 1; // 普通 首充号
            $where['scopeId'] = 1;
        } else
            if ($type == 2) {
                $type = 2; // 普通代充号
                $where['scopeId'] = 2;
            }
            $field = "g.goodsType,g.goodsName,g.goodsId,g.goodsSpec,g.marketPrice,g.shopPrice,g.scopeId,g.isMiao ,(
		SELECT
			attrPrice
		FROM
			oto_goods_versions AS gv
		WHERE
			gv.goodsId = g.goodsId
		ORDER BY
			attrPrice ASC
		LIMIT 1
	) AS attrPrice  ";
            $where['g.goodsFlag'] = 1;
            $where['g.goodsStatus'] = 1;
            $where['g.isSale'] = 1;
            $where['g.gameId'] = $gameId;
            $where['g.goodsType'] = 1; // 会员商品
            $where['isAdminRecom'] = 1; // 平台推荐的才可以在此页显示
            $moreInf = M('goods as g')->field($field)
            ->where($where)
            ->limit($page * 30, 30)
            ->order('g.shopPrice ASC')
            ->having(' attrPrice >0 ')
            ->select();
            if (! empty($moreInf)) {
                $moreInf[0]['Flag'] = $type;
            }
            $this->returnJson($moreInf);
    }
    
    
    // 检测通过后的店铺首充号代充
    public function validateGoodsList()
    {
        $gameId = I('gameId', 0);
        $type = I('gameScope', '', 'intval') ? I('gameScope') : 1;
        $page = I('page', 0);
        $shopId = I('shopId', 0, 'intval');
        $vid = I('vid', 0, 'intval');
        if ($type == 1) {
            $type = 1; // 普通 首充号
            $where['scopeId'] = 1;
        } else 
            if ($type == 2) {
                $type = 2; // 普通代充号
                $where['scopeId'] = 2;
                $where['gv.versionsId']=$vid;
            } else 
                if ($type == 3) {
                    unset($where['scopeId']);
                    $where['gv.versionsId']=$vid;
                    $type = 3; // 会员 商品
                }
        if (! $type || ! $gameId || ! $shopId) {
            $this->returnJson(array(
                'msg' => '数据异常',
                'status' => - 1
            ));
            return;
        }
        $where = array();
        $where['g.goodsFlag'] = 1;
        $where['g.isSale'] = 1;
        $where['g.goodsStatus'] = 1;
        $where['g.shopId'] = $shopId;
        $where['g.gameId'] = $gameId;
        $where['g.scopeId'] = $type;
        if ($type == 3) {
            unset($where['g.scopeId']);
            $where['g.goodsType'] = 1; // 会员商品
        } else {
            $where['g.goodsType'] = 0; // 普通 商品
        }
        
        $field = "g.goodsType,g.goodsName,g.goodsId,g.goodsSpec,g.marketPrice,g.shopPrice,g.scopeId,g.isMiao,(
		SELECT
			attrPrice
		FROM
			oto_goods_versions AS gv
		WHERE
			gv.goodsId = g.goodsId and gv.versionsId=$vid 
		ORDER BY
			attrPrice ASC
		LIMIT 1
	) AS attrPrice ";
        $moreInf = M('goods as g')->field($field)
            ->limit($page * 30, 30)
            ->where($where)
            ->having(' attrPrice >0 ')
            ->order('g.shopPrice ASC')
            ->select();
        if (! empty($moreInf)) {
            $moreInf[0]['Flag'] = $type;
        }
        $this->returnJson($moreInf);
    }

    public function validateGoodsDetail()
    {
        $id = I('goodsId', 0, 'intval');
        $versionsId = I('vid', 0, 'intval');
        if (! $id || ! $versionsId) {
            $this->returnJson(array());
            exit();
        }
        $field = "g.*,s.shopName,s.shopImg";
        $goodsInfo = M('goods')->where(array(
            'g.goodsId' => $id
        ))
            ->field($field)
            ->join('as g left join  oto_shops as s on s.shopId=g.shopId')
            ->find();
        
        $goodsInfo['catName'] = '首充号';
        if ($goodsInfo['scopeId'] == 1) {
            $goodsInfo['catName'] = '首充号';
        } else 
            if ($goodsInfo['scopeId'] == 2) {
                $goodsInfo['catName'] = '首充号代充';
            }
        
        $sales = M('order_goods')->where(array(
            'o.orderStatus' => array(
                'gt',
                0
            ),
            'o.shopId' => $goodsInfo['shopId']
        ))
            ->join('as g join oto_orders as o on o.orderId=g.orderId')
            ->SUM('goodsNums');
        $sales = $sales > 0 ? $sales : 0;
        
        $goodsAttr = M('goods_versions as gv')->where(array(
            'gv.goodsId' => $id,
            'gv.versionsId' => $versionsId
        ))
            ->field('gv.*,v.vName')
            ->join('oto_versions as v on v.id=gv.versionsId')
            ->select();
        
        if (! empty($goodsAttr)) {
            foreach ($goodsAttr as $k => $v) {
                $goodsAttr[$k]['zhekou'] = sprintf('%.1f', ($v['attrPrice'] / $goodsInfo['marketPrice']) * 10);
            }
        }
        
        $gameName = M('game')->where(array(
            'id' => $goodsInfo['gameId']
        ))->getField('gameName');
        $shopScope = M('shops')->where(array(
            'shopId' => $goodsInfo['shopId']
        ))->getField('scope');
        $fanwei = '';
        $temp = explode(',', $shopScope);
        foreach ($temp as $v) {
            if ($v == 1) {
                $fanwei .= '首充号,';
            } else 
                if ($v == 2) {
                    $fanwei .= '首充号代充,';
                }
        }
        $fanwei = trim($fanwei, ',');
        $arr = array();
        $arr['fanwei'] = $fanwei;
        $arr['goodsInfo'] = $goodsInfo;
        $arr['sales'] = $sales;
        $arr['gameName'] = $gameName;
        $arr['goodsAttr'] = $goodsAttr;
        $this->returnJson($arr);
    }
    
    // 检查代充库存及验证是否可充
    public function validateStock()
    {
        // 检查库存
        $id = I('id');
        $arr = session('validateGoods');
        if (! $arr) {
            $this->ajaxReturn(array(
                'num' => 0,
                'mes' => '数据异常',
                'status' => - 1
            ));
        }
        
        if (IS_AJAX) {
            $attrid = I('attrid', 0);
            $goosdId = I('goodsId', 0);
            $versions = $arr['vid'];
            $info = M('goods as g')->where(array(
                'g.goodsId' => $goosdId,
                'g.gameId' => $arr['gameId'],
                'gv.versionsId' => $versions
            ))
                ->join("oto_goods_versions as gv on gv.goodsId=g.goodsId")
                ->field('g.scopeId,g.shopId,g.gameId')
                ->find();
            if (! $info) {
                $this->ajaxReturn(array(
                    'num' => 0,
                    'mes' => '数据异常，请退出重试',
                    'status' => - 1
                ));
            }
            $stock = 0;
            if ($attrid > 0) {
                $stock = M('goods_versions')->where(array(
                    'id' => $attrid
                ))->getField('attrStock');
            } else {
                $stock = M('goods')->where(array(
                    'goodsId' => $goosdId
                ))->getField('goodsStock');
            }
            $this->ajaxReturn(array(
                'num' => $stock,
                'status' => 0
            ));
        }
    }
}
