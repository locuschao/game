<?php
namespace Home\Widget;
use Think\Controller;

class GoodsWidget extends Controller {
	/**
	 * 查看商品历史记录
	 */
	public function getViewGoods(){
		$m = D('Home/Goods');
		$goodslist = $m->getViewGoods();
		$this->assign("goodslist",$goodslist);
		$this->display("widget/view_history");
	}
	
	/**
	 * 热销排名
	 */
	public function getHotGoods($shopId){
		$m = D('Home/Goods');
		$hotgoods = $m->getHotGoods($shopId);
		$this->assign("hotgoods",$hotgoods);
		$this->display("widget/hot_goods");
	}
	
}