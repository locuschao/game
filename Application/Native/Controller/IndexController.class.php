<?php
namespace Native\Controller;

use Think\Controller;
use Think\Model;

class IndexController extends BaseController
{

    public function _initialize()
    {
        @header('Content-type: text/html;charset=UTF-8');
    }
    
    // 首页轮播图
    public function loopImg()
    {
        $today = date('Y-m-d');
        $field = "adFile,adURL,appUrl";
        $scrollImg = M('ads')->field($field)
            ->where(array(
            'adPositionId' => - 1,
            'adStartDate' => array(
                'elt',
                $today
            ),
            'adEndDate' => array(
                'egt',
                $today
            )
        ))
            ->select();
        print_r(json_encode($scrollImg));
        return;
    }

    public function testAutoKey()
    {
        $str = 'abcdef';
        $dstr = 'a4f7OAEXoTTuxqlMH3OMDnurHcRkF1I2od6qKJaEB8g2DMc';
        echo authcode($str, 'ENCODE'); // 加密
        echo '<br/>';
        echo authcode($dstr, 'DECODE'); // 解密
    }
    
    // 首页热门游戏
    public function hotGame()
    {
        // 首页热门推荐商品
        $indexGoods = M('game')->where(array(
            'isHot' => 1
        ))
            ->limit(20)
            ->order('id desc')
            ->select();
        // 判断此游戏有没有所包含的类型有哪些
        $gameId = $indexGoods[0]['id'];
        $shouType = M('goods')->where(array(
            'gameId' => $gameId,
            'scopeId' => 1
        ))->find(); // 判断 是否有首充号
        $daiType = M('goods')->where(array(
            'gameId' => $gameId,
            'scopeId' => 2
        ))->find(); // 判断 是否有首充号代充
        $indexGoods[0]['shouChong'] = $shouType ? 1 : 0;
        $indexGoods[0]['daiChong'] = $daiType ? 1 : 0;
        print_r(json_encode($indexGoods));
        return;
    }

    public function hotShop()
    {
        $goodsShop = M('shops')->where(array(
            'isAdminRecom' => 1,
            'shopFlag' => 1,
            'shopAtive' => 1
        ))
            ->limit(20)
            ->order('shopId DESC')
            ->select();
        print_r(json_encode($goodsShop));
    }
    
    // 订单列表
    public function orderList()
    {
        $page = I('page', 0);
        
        $userId = I('userId');
        $userId = authCode($userId);
        
        if(!$userId){
            $this->returnJson(array());
        }
        
        $orderStatus = I('orderStatus',0,'intval');
        $limit = 20;
        $info = array();
        switch ($orderStatus) {
            case 0:
                // 已完成
                $orders = A('Native/Orders');
                $info = $orders->orders($page, $limit, $userId);
                break;
            // 待付款
            case 1:
                $waitPay = A('Native/Orders');
                $info = $waitPay->waitPay($page, $limit, $userId);
                break;
            case 2: // 待发货
                $waitDeliver = A('Native/Orders');
                $info = $waitDeliver->waitDeliver($page, $limit, $userId);
                break;
            case 3:
                // 已发货
                $fahuo = A('Native/Orders');
                $info = $fahuo->fahuo($page, $limit, $userId);
                break;
            case 4:
                // 已完成
                $orderFinsh = A('Native/Orders');
                $info = $orderFinsh->orderFinsh($page, $limit, $userId);
                break;
        }
        foreach ($info as $k => $v) {
            if (isset($v['paytime'])) {
                $info[$k]['paytime'] = date('Y-m-d H:i:s', $v['paytime']);
            }
        }
        if (! empty($info)) {
            $info[0]['Flag'] = $orderStatus;
        }
        $this->returnJson($info);
    }

    public function index()
    {
        // 商品推荐店铺
        
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
            $this->orders = $orders->orders();
        }
        
        $this->notice = $this->notice();
        
        $this->display();
    }
    
    // 用户信息
    public function getUserInfo()
    {
        $userId = I('userId');
        $userId = authCode($userId);
        $field = "userPhone,userPhoto,userMoney,isAgent";
        $userInfo = M('users')->where(array(
            'userId' => $userId
        ))
            ->field($field)
            ->find();
        $this->returnJson($userInfo);
    }
    
    // 首页询问版权
    public function buttom()
    {
        $bottom = M('sys_configs')->where(array(
            'fieldCode' => 'mallFooter'
        ))->getField('fieldValue');
        $this->ajaxReturn(html_entity_decode($bottom));
    }

    public function notice()
    {
        $page = I('page', 0);
        $limit = 5;
        $star = $page * $limit;
        $info = M('notice')->order('id desc')
            ->field('id,title')
            ->limit($star, $limit)
            ->select();
        print_r(json_encode($info));
    }

    public function noticeDetail()
    {
        $id = I('id', 1);
        $info = M('notice')->where(array(
            'id' => $id
        ))->find();
        $info['content'] = html_entity_decode($info['content']);
        $info['content'] = preg_replace_callback("|(\d*[.]?\d*px)|", "pxToem", $info['content']);
        $server = $_SERVER['SERVER_NAME'];
        $info['content'] = str_replace('src="/Upload/image/', 'src="http://' . $server . '/Upload/image/', $info['content']);
        $this->returnJson($info);
    }
}
