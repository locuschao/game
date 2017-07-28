<?php
/**
 * @author peng	
 * @date 2016-12-31
 * @descreption 会员等级商品
 */
namespace Game\Controller;
use Think\Controller;

class UserGoodsLevelController extends Controller{
    /**
     * @author peng	
     * @date 2017-01-12
     * @descreption 商品等级首页
     */
    public function index(){
        
        $this->assign('goods_list',M('goods')
        ->where([
        'shopId'=>0,
        'isSale'=>1
        ])
        ->order('member_rank')
        ->select());
    	$this->display();
    }
    /**
     * @author peng	
     * @date 2017-01-12
     * @descreption 商品详情
     */
    public function goodsDetail(){
    	$this->display();
    }
    
    /**
     * @author peng	
     * @date 2017-01-12
     * @descreption 我的代金券
     */
    public function myVoucher(){
    	$this->display();
    }
    
    
}