<?php
namespace Game\Controller;

use Think\Controller;
use Think\Model;

class GoodsController extends Controller
{

    public function _initialize()
    {
        @header('Content-type: text/html;charset=UTF-8');
    }

    public function index()
    {
        $id = I('id');
        if (! $id) {
            $url = U('Index/index', '', '', 0);
            $this->redirect($url, '', 0, '');
            exit();
        }
        $field = "g.*,s.shopName,s.shopImg";
        $goodsInfo = M('goods')->where(array(
            'g.goodsId' => $id
        ))
            ->field($field)
            ->join('as g left join  oto_shops as s on s.shopId=g.shopId')
            ->find();
        /**
         * @author peng
         * @date 2016
         * @descreption 查询相关商品的代充相应版本的价格,并入到 $goodsAttr中
         */
         $dai_versions_info=M('goods_versions')
         ->field('versionsId,min(round(attrPrice/shopPrice,2)) min_zhekou')
         ->join('gv left join '.C('DB_PREFIX').'goods g on gv.goodsId=g.goodsId')
         ->where([
         'g.shopId'=>$goodsInfo['shopId'],
         'gameId'=>$goodsInfo['gameId'],
         'scopeId'=>2,
         'isSale'=>1,
         'goodsStatus'=>1,//审核通过了的
         'goodsFlag'=>1//删除状态正常的
         ])
         ->group('versionsId')
         ->select();
         
         foreach($dai_versions_info as $k=>$row){
            $dc_price_assoc[$row['versionsId']]=$row['min_zhekou'];
         }
         $this->dc_price_assoc=$dc_price_assoc;
            
        $goodsInfo['catName'] = '首充号';
        if ($goodsInfo['scopeId'] == 1) {
            if($goodsInfo['goodsType']==0){
                $goodsInfo['catName'] = '首充号';
            }else{
                $goodsInfo['catName'] = '会员首充';
            }

        } else 
            if ($goodsInfo['scopeId'] == 2) {
                if($goodsInfo['goodsType']==0){
                  $goodsInfo['catName'] = '首充号代充';
                }else{
                    $goodsInfo['catName'] = '会员首代';
                }
            } 
        $this->sales = M('order_goods')->where(array(
            'o.orderStatus' => array(
                'in',
                '3,-6,-8'
            ),
            'o.shopId' => $goodsInfo['shopId']
        ))
            ->join('as g join oto_orders as o on o.orderId=g.orderId')
            ->SUM('goodsNums');
        
        $goodsAttr = M('goods_versions as gv')->where(array(
            'gv.goodsId' => $id
        ))
            ->field('gv.*,v.vName')
            ->join('oto_versions as v on v.id=gv.versionsId')
            ->select();
        #算出各个版本的不同等级会员所享有的价格
        D('Game/Goods')->getRightPrice($goodsAttr);
        $this->goodsAttr = $goodsAttr;
        
        
        $this->gameName = M('game')->where(array(
            'id' => $goodsInfo['gameId']
        ))->getField('gameName');
        
        $this->downLoadUrl=M('game')->where(array(
            'id' => $goodsInfo['gameId']
        ))->getField('downLoadUrl');
        
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
        $this->fanwei = $fanwei;
        $this->goodsInfo = $goodsInfo;
        
        #查出会员等级 add by peng
//        $this->rank=M('users')->find($_SESSION['oto_mall']['oto_userInfo']['userId'])['rank'];
        $this->rank=M('users')->find(session('oto_userId'))['rank'];                //魏永就  直接获取session中的oto_userId
        $this->display();
    }

    public function goodsSearch()
    {
        $page = I('page', 0);
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
                        if($vv['goodsType']==1){
                            $type[$kk]['type'] = '会员首充';
                        }
                    } else 
                        if ($vv['scopeId'] == 2) {
                            $type[$kk]['type'] = '首充号代充';
                            if($vv['goodsType']==1){
                                $type[$kk]['type'] = '会员首代';
                            }
                        } 
                } else {
                    unset($type[$kk]);
                }
                $goodsList[$k]['type'] = $type;
            }
        }
        if (IS_AJAX) {
            $this->ajaxReturn($goodsList);
        } else {
            $this->goodsList = $goodsList;
            $this->display();
        }
    }
    
    // 检查库存
    public function stock()
    {
        // 判断用户代充，如果是代充，则跳转到以前的店铺去
        // 检查库存
       
        if (IS_AJAX) {
            $attrid = I('attrid', 0);
            $goosdId = I('goodsId', 0);
            $info = M('goods')->where(array(        //查找 表 goods
                'goodsId' => $goosdId
            ))
                ->field('scopeId,shopId,gameId')
                ->find();
            
            $shopInfo=M('shops')->where(array('shopId'=>$info['shopId']))->find();     //查找 shops
            
            if(!$shopInfo||!$info){
                $this->ajaxReturn(array('status'=>-2,'msg'=>'非法数据请求'));
            }
            
            date_default_timezone_set('PRC');
            //判断营业时间
            $currentH=date("G");
            $fen=date('i');
            if($fen>=30&&$fen<60){
                $currentH=$currentH.'.5';
            }
            if($currentH>=$shopInfo['serviceStartTime']&&$currentH<=$shopInfo['serviceEndTime']){
                $this->ajaxReturn(array('status'=>0,'msg'=>'店铺营业中'));
            }else{
                $starTime=$shopInfo['serviceStartTime'];
                $endTime=$shopInfo['serviceEndTime'];
                $this->ajaxReturn(array('status'=>-1,'msg'=>"店铺营业时间是{$starTime} 至 {$endTime}"));
            }
            
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
            /*
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
            */
            
            
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
        //$where = "where g.isDel=0 and go.goodsType=0"; // and g.goodsType=1普通 商品
        /**
         * @author peng
         * @date 2016
         * @remark 加上商品是未删除状态而且是超管推荐的
         */
        $where = "where g.isDel=0 and go.goodsType=0 and goodsFlag=1 and isAdminRecom=1"; // and g.goodsType=1普通 商品
        if ($key) {
            $where .= " and g.gameName like '%$key%' ";
        }
        
        if ($letter == 'Hot') {
            $where .= " and  g.isHot=1 ";
        }
        
        
        if ($PY) {
            $pinyin .= " and $PY='$letter' ";
            
            $english=" and gameName like '$letter%'  ";
            
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
        
        //搜索中文
        $pinyin=$where.$pinyin;

        //搜索字母开头
        $english=$where.$english;
        
        $sql = "select g.* from __PREFIX__game as g   $join  $pinyin  group by go.gameId  $order limit  $limit,30";
        $goodsInfo = M()->query($sql);
//        $goodsInfo = M();
//        de($goodsInfo);
//        de($goodsInfo);
        
       
        /**
         * @author peng
         * @copyright 2016
         * @remark 修复bug,当用关键字搜索时候会出现重复商品
         */
        if($letter!='' && $letter != 'Hot'){
            $en = "select g.* from __PREFIX__game as g   $join  $english  group by go.gameId  $order limit  $limit,30";
        
            $englishGame=M()->query($en);

        }
        
        if($goodsInfo){
            if($englishGame){
                $goodsInfo=array_merge($goodsInfo,$englishGame);
            }
        }else{
            $goodsInfo=$englishGame;
        }
        
        if (IS_AJAX) {
            
            $this->ajaxReturn($goodsInfo);
        } else {
            
            $this->goodsInfo = $goodsInfo;
            $this->display();
        }
    }

    public function memberGoods()
    {
        #$this->error('暂未开放，敬请期待');
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
        
        //$where = "where g.isDel=0 and go.goodsType=1"; // and g.goodsType=1会员 商品
        /**
         * @author peng
         * @date 2016
         * @remark 加上商品是未删除状态而且是超管推荐。
         */
        $where = "where g.isDel=0 and go.goodsType=1 and go.goodsFlag=1 and isAdminRecom=1"; // and g.goodsType=1会员 商品
        if ($key) {
            $where .= " and g.gameName like '%$key%' ";
        }
        
        if ($letter == 'Hot') {
            $where .= " and  g.isHot=1 ";
        }
        
        if ($PY) {
             $pinyin .= " and $PY='$letter' ";
            
            $english=" and gameName like '$letter%'  ";
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
       
        //搜索中文
        $pinyin=$where.$pinyin;
        
        //搜索字母开头
        $english=$where.$english;
        $sql = "select g.* from __PREFIX__game as g   $join  $pinyin  group by go.gameId  $order limit  $limit,30";
        $goodsInfo = M()->query($sql);
        /**
         * @author peng
         * @copyright 2016
         * @remark 修复重复商品bug
         */
        if($letter && $letter != 'Hot'){
            $en = "select g.* from __PREFIX__game as g   $join  $english  group by go.gameId  $order limit  $limit,30";
        
            $englishGame=M()->query($en);
        }
        
        
        if($goodsInfo){
            if($englishGame){
                $goodsInfo=array_merge($goodsInfo,$englishGame);
            }
        }else{
            $goodsInfo=$englishGame;
        }
        
        
        if (IS_AJAX) {
            $this->ajaxReturn($goodsInfo);
        } else {
            $this->goodsInfo = $goodsInfo;
            $this->display();
        }
    }
    
    // 搜索结果点击后跳转到列表页-》普通 商品
    public function goodsList()
    {
        $gameId = I('gameId');
        $type = I('type') ? I('type') : 1;
        if ($type == '首充号') {
            $type = 1;
        } else 
            if ($type == '首充号代充') {
                $type = 2;
            } else 
                if ($type == '会员商品') {
                    $type = 3;
                } else 
                    if ($type == '自主充值') {
                        $type = 4;
                    }
        $gameName = M('game')->where(array(
            'id' => $gameId
        ))->getField('gameName');
        $this->assign('gameName', $gameName);
        $page = I('page', 0);
        $field = "g.goodsName,g.goodsType,g.goodsId,g.goodsSpec,g.marketPrice,g.shopPrice,g.scopeId,g.isMiao ,(
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
            $where['g.goodsType'] = 1;
        } else {
            $where['g.goodsType'] = 0;
        }
        $where['scopeId'] = 1;
        $where['isAdminRecom'] = 1; // 平台推荐的才可以在此页显示
        $this->shouChongInfo = M('goods as g')->field($field)
            ->where($where)
            ->limit($page * 30, 30)
            ->having(' attrPrice >0 ')
            ->order('g.shopPrice ASC')
            ->select();
        $where['scopeId'] = 2;
        $this->daiChongInfo = M('goods as g')->field($field)
            ->where($where)
            ->limit($page * 30, 30)
            ->having(' attrPrice >0 ')
            ->order('g.shopPrice ASC')
            ->select();
        unset($where['scopeId']);
        $where['g.goodsType'] = 1;
        $this->fenxiaoChongInfo = M('goods as g')->field($field)
            ->where($where)
            ->limit($page * 30, 30)
            ->having(' attrPrice >0 ')
            ->order('g.shopPrice ASC')
            ->select();
            
        
        /*
         * $where['scopeId']=4;
         * $this->zhizhuChongInfo=M('goods as g')->field($field)->where($where)->limit($page*30,30)->order('g.shopPrice ASC')->select();
         */
        if (IS_AJAX) {
            if ($type == 3) {
                $where['g.goodsType'] = 1;
            } else {
                $where['scopeId'] = $type;
                $where['g.goodsType'] = 0;
            }
            $moreInf = M('goods as g')->field($field)
                ->where($where)
                ->limit($page * 30, 30)
                ->order('g.shopPrice ASC')
                ->having(' attrPrice >0 ')
                ->select();
            $this->ajaxReturn($moreInf);
        } else {
            $this->display();
        }
    }
    
    
    
    //会员商品列表
    public function memberGoodsList()
    {
        $gameId = I('gameId');
        $type = I('type') ? I('type') : 1;
        if ($type == '首充号') {
            $type = 1;
        } else
            if ($type == '首充号代充') {
                $type = 2;
            } 
                $gameName = M('game')->where(array(
                    'id' => $gameId
                ))->getField('gameName');
                $this->assign('gameName', $gameName);
                $page = I('page', 0);
                $field = "g.goodsName,g.goodsId,g.goodsSpec,g.marketPrice,g.shopPrice,g.scopeId,g.isMiao ,(
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
                $where['g.goodsType'] = 1;
                $where['scopeId'] = 1;
                $where['isAdminRecom'] = 1; // 平台推荐的才可以在此页显示
                $this->shouChongInfo = M('goods as g')->field($field)
                ->where($where)
                ->limit($page * 30, 30)
                ->having(' attrPrice >0 ')
                ->order('g.shopPrice ASC')
                ->select();
                $where['scopeId'] = 2;
                $this->daiChongInfo = M('goods as g')->field($field)
                ->where($where)
                ->limit($page * 30, 30)
                ->having(' attrPrice >0 ')
                ->order('g.shopPrice ASC')
                ->select();
                unset($where['scopeId']);
                $where['g.goodsType'] = 1;
                $this->fenxiaoChongInfo = M('goods as g')->field($field)
                ->where($where)
                ->limit($page * 30, 30)
                ->having(' attrPrice >0 ')
                ->order('g.shopPrice ASC')
                ->select();
                /*
                 * $where['scopeId']=4;
                 * $this->zhizhuChongInfo=M('goods as g')->field($field)->where($where)->limit($page*30,30)->order('g.shopPrice ASC')->select();
                */
                if (IS_AJAX) {
                    $moreInf = M('goods as g')->field($field)
                    ->where($where)
                    ->limit($page * 30, 30)
                    ->order('g.shopPrice ASC')
                    ->having(' attrPrice >0 ')
                    ->select();
                    $this->ajaxReturn($moreInf);
                } else {
                    $this->display();
                }
    }
    
    
    // 检测通过后的店铺首充号代充
    public function validateGoodsList()
    {
        $arr = session('validateGoods');
        
        if (! $arr) {
            $this->redirect(U('Game/Index/index', '', '', '', ''));
            exit();
        }
        $this->gameName = M('game')->where(array(
            'id' => $arr['gameId']
        ))->getField('gameName');
        $where = array();
        $where['g.goodsFlag'] = 1;
        $where['g.isSale'] = 1;
        $where['g.goodsStatus'] = 1;
        /**
         * @author peng
         * @date 2017-02
         * @descreption 如果是TT版本的初次验证，则显示所有的店铺 
         * 或者乐8的初次验证,则显示相应代理的所有店铺
         */
        if($arr['shopId']!='') {
            if(is_array($arr['shopId'])){
                $where['g.shopId'] = ['in',$arr['shopId']];
            } else {
                $where['g.shopId'] = $arr['shopId'];
            }
            
        }
        
        $where['g.gameId'] = $arr['gameId'];
        
        $where['g.scopeId'] = 1; // 首充
        $where['g.goodsType'] = 0;
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
        $this->shouChong = M('goods as g')->field($field)
            ->where($where)
            ->having(' attrPrice >0 ')
            ->order('g.shopPrice ASC')
            ->select();
        $where['g.goodsType'] = 0;
        $where['g.scopeId'] = 4; // 自充
        $this->zhiChong = M('goods as g')->field($field)
            ->where($where)
            ->having(' attrPrice >0 ')
            ->order('g.shopPrice ASC')
            ->select();
        
        // 会员商品
        $where['g.goodsType'] = 1;
        // 会员首充
        $where['g.scopeId'] = 1;
        $memberShouChong = M('goods as g')->field($field)
            ->where($where)
            ->having(' attrPrice >0 ')
            ->order('g.shopPrice ASC')
            ->select();
        
        // 会员 代充
        $where['g.scopeId'] = 2;
        $versionsId = $arr['vid'];
        $where['gv.versionsId'] = $versionsId;
        $memberdaiChong = M('goods as g')->where($where)
            ->field('g.*,gv.attrPrice')
            ->order('g.shopPrice ASC')
            ->join("oto_goods_versions as gv on gv.goodsId=g.goodsId and gv.versionsId=$versionsId")
            ->group('g.goodsId')
            ->select();
       // $this->fenxiao = array_merge($memberShouChong, $memberdaiChong);
        $this->fenxiao= $memberdaiChong;
        
        // 代充
        $where['g.goodsType'] = 0;//普通 商品
        $where['g.scopeId'] = 2;
        $versionsId = $arr['vid'];
        $where['gv.versionsId'] = $versionsId;
        $this->daiChong = M('goods as g')->where($where)
            ->field('g.*,gv.attrPrice')
            ->order('g.shopPrice ASC')
            ->join("oto_goods_versions as gv on gv.goodsId=g.goodsId and gv.versionsId=$versionsId")
            ->group('g.goodsId')
            ->select();
        
        //查询此游戏此版本是否是购买过会员商品的,如果是的话,优先显示会员的
        /*
        [vid] => 34
        [account] => 13533362394
        [gameId] => 10
        [shopId] => 16
        */
        $this->assign('arr',$arr);
        
        // echo M('goods as g')->_sql();
        $this->display();
    }

    public function validateGoodsDetail()
    {
        $id = I('id');
        $arr = session('validateGoods');
        if (! $arr) {
            $this->redirect(U('Game/Index/index', '', '', '', ''));
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
            if($goodsInfo['goodsType']==1){
                $goodsInfo['catName'] = '会员首充';
            }
        } else 
            if ($goodsInfo['scopeId'] == 2) {
                $goodsInfo['catName'] = '首充号代充';
                if($goodsInfo['goodsType']==1){
                    $goodsInfo['catName'] = '会员首代';
                }
            }
        
        $this->sales = D('Game/Goods')->getShopNum($goodsInfo['shopId']);
        
        $versionsId = $arr['vid'];
        
        $goodsAttr = M('goods_versions as gv')->where(array(
            'gv.goodsId' => $id,
            'gv.versionsId' => $versionsId
        ))
            ->field('gv.*,v.vName')
            ->join('oto_versions as v on v.id=gv.versionsId')
            ->select();
            
         #算出各个版本的不同等级会员所享有的价格
        D('Game/Goods')->getRightPrice($goodsAttr);   
        
        $this->goodsAttr = $goodsAttr;
        
        $this->gameName = M('game')->where(array(
            'id' => $goodsInfo['gameId']
        ))->getField('gameName');
        
        $this->downLoadUrl=M('game')->where(array(
            'id' => $goodsInfo['gameId']
        ))->getField('downLoadUrl');
        
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
        $this->fanwei = $fanwei;
        $this->goodsInfo = $goodsInfo;
        
        #查出会员等级 add by peng
        $this->rank=M('users')->find($_SESSION['oto_mall']['oto_userInfo']['userId'])['rank'];
        
        $this->display();
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
                    'status' => - 2
                ));
            }

            $shopInfo=M('shops')->where(array('shopId'=>$info['shopId']))->find();
            
            if(!$shopInfo){
                $this->ajaxReturn(array('status'=>-3,'msg'=>'非法数据请求'));
            }
            
            date_default_timezone_set('PRC');
            //判断营业时间
            $currentH=date("G");
            $fen=date('i');
            if($fen>=30&&$fen<60){
                $currentH=$currentH.'.5';
            }
            if($currentH>=$shopInfo['serviceStartTime']&&$currentH<=$shopInfo['serviceEndTime']){
                $this->ajaxReturn(array('status'=>0,'msg'=>'店铺营业中'));
            }else{
                $starTime=$shopInfo['serviceStartTime'];
                $endTime=$shopInfo['serviceEndTime'];
                $this->ajaxReturn(array('status'=>-4,'msg'=>"店铺营业时间是{$starTime} 至 {$endTime}"));
            }
            
            
            
        /*     $stock = 0;
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
            )); */
        }
    }
}
