<?php
namespace Game\Controller;

use Think\Controller;
use Think\Model;

class IndexController extends Controller
{

    public function _initialize()
    {
        @header('Content-type: text/html;charset=UTF-8');
    }

    public function index()
    {
        /**
         * @author peng
         * @date 2017-02
         * @descreption 
         */
         if($_GET['referer'] && $turnback_uri=_getcookie('ref')){
            header('location:'.$turnback_uri);
            _setcookie('ref',null);
         }
     
        // 商品推荐店铺
        $this->goodsShop = M('shops')->where(array(
            'isAdminRecom' => 1,
            'shopFlag' => 1,
            'shopAtive' => 1
        ))
            ->limit(4)
            ->order('shopId DESC')
            ->select();
        $this->scope = explode(',', $this->goodsShop[0]['businessScope']);
        // 首页热门推荐商品
        $indexGoods = M('game')->where(array(
            'isHot' => 1
        ))
            ->limit(20)
            ->order('id desc')
            ->select();
        // 判断此游戏有没有所包含的类型有哪些
        $gameId = $indexGoods[0]['id'];
        $this->shouType = M('goods')->where(array(
            'gameId' => $gameId,
            'scopeId' => 1
        ))->find(); // 判断 是否有首充号
        $this->daiType = M('goods')->where(array(
            'gameId' => $gameId,
            'scopeId' => 2
        ))->find(); // 判断 是否有首充号代充
        
        $this->indexGoods = $indexGoods;
//        de($indexGoods);
        $today = date('Y-m-d');
        // 首页轮播图
        $this->scrollImg = M('ads')->where(array(
            'adPositionId' => - 1,
            'adStartDate' => array(
                'elt',
                $today
            ),
            'adEndDate' => array(
                'egt',
                $today
            )
        ))->select();
        // 用户个人信息
        $this->userInfo = M('users')->where(array(
            'userId' => session('oto_userId')
        ))->find();
        
        if (session('oto_userId')) {
            // 待支付
            $waitPay = A('Game/Orders');
            $this->waitPay = $waitPay->waitPay();
            // 待发货
            $waitDeliver = A('Game/Orders');
            $this->waitDeliver = $waitDeliver->waitDeliver();
            // 已完成
            $orderFinsh = A('Game/Orders');
            $this->orderFinsh = $orderFinsh->orderFinsh();
            
            // 发货
            $fahuo = A('Game/Orders');
            $this->fahuo = $fahuo->fahuo();
            
            // 已完成
            $orders = A('Game/Orders');
            $data = $orders->orders();
            $notReadCount = M('orders')->where('isRead=0 and userId='.session('oto_userId'))->count();
            $this->assign('notReadCount',$notReadCount);
            $this->orders = $data;
        }
        
        $bottom = M('sys_configs')->where(array(
            'fieldCode' => 'mallFooter'
        ))->getField('fieldValue');
        $this->bottom = html_entity_decode($bottom);
        
        $this->notice = $this->notice();
        
        $this->display();
    }

    public function notice()
    {
        $page = I('page', 0);
        $limit = 5;
        $star = $page * $limit;
        $info = M('notice')->order('id desc')
            ->limit($star, $limit)
            ->select();
        if (IS_AJAX) {
            $this->ajaxReturn($info);
        } else {
            return $info;
        }
    }

    public function noticeDetail()
    {
        $id = I('id', 1);
        $this->info = M('notice')->where(array(
            'id' => $id
        ))->find();
        $this->display();
    }
}
