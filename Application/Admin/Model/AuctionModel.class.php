<?php
namespace Admin\Model;

/**
 * 商品服务类
 */
class AuctionModel extends BaseModel
{

    /**
     * 获取商品信息
     */
    public function get()
    {
        $m = M('goods');
        $id = (int) I('id', 0);
        $goods = $m->where("goodsId=" . $id)->find();
        // 相册
        $m = M('goods_gallerys');
        $goods['gallery'] = $m->where('goodsId=' . $id)->select();
        // 属性
        if ($goods['attrCatId'] > 0) {
            $sql = "select catName from __PREFIX__attribute_cats where catId=" . $goods['attrCatId'];
            $rs = $this->query($sql);
            $goods['attrCatName'] = $rs[0]['catName'];
            
            // 获取规格属性
            $sql = "select ga.attrVal,ga.attrPrice,ga.attrStock,a.attrId,a.attrName,a.isPriceAttr,ga.isRecomm
			            ,ga.isRecomm from __PREFIX__attributes a
			            left join __PREFIX__goods_attributes ga on ga.attrId=a.attrId and ga.goodsId=" . $id . " where
						a.attrFlag=1 and a.catId=" . $goods['attrCatId'] . " and a.shopId=" . $goods['shopId'];
            $sql .= " order by ga.id asc";
            $attrRs = $m->query($sql);
            if (! empty($attrRs)) {
                $priceAttr = array();
                $attrs = array();
                foreach ($attrRs as $key => $v) {
                    if ($v['isPriceAttr'] == 1) {
                        $goods['priceAttrName'] = $v['attrName'];
                        $priceAttr[] = $v;
                    } else {
                        $v['attrContent'] = $v['attrVal'];
                        $attrs[] = $v;
                    }
                }
                $goods['priceAttrs'] = $priceAttr;
                $goods['attrs'] = $attrs;
            }
        }
        $goodsGroupModel = M('GoodsAuction');
        $group = $goodsGroupModel->where('goodsId =' . $goods['goodsId'])->find();
        $group['auctionStartTime'] = date('Y-m-d H:i:s', $group['auctionStartTime']);
        $group['auctionEndTime'] = date('Y-m-d H:i:s', $group['auctionEndTime']);
        return array_merge($goods, $group);
    }

    /**
     * 分页列表[获取待审核列表]
     */
    public function queryPenddingByPage()
    {
        $m = M('goods');
        $shopName = I('shopName');
        $goodsName = I('goodsName');
        $areaId1 = (int) I('areaId1', 0);
        $areaId2 = (int) I('areaId2', 0);
        $goodsCatId1 = (int) I('goodsCatId1', 0);
        $goodsCatId2 = (int) I('goodsCatId2', 0);
        $goodsCatId3 = (int) I('goodsCatId3', 0);
        $sql = "select g.*,gc.catName,sc.catName shopCatName,p.shopName,act.goodsAuctionStatus from __PREFIX__goods g
	 	      left join __PREFIX__goods_cats gc on g.goodsCatId3=gc.catId
	 	      left join __PREFIX__goods_auction act on act.goodsId=g.goodsId
	 	      left join __PREFIX__shops_cats sc on sc.catId=g.shopCatId2,__PREFIX__shops p
	 	      where  p.shopId=g.shopId and g.isAuction = 1 and act.goodsAuctionStatus<>1 ";
        if ($areaId1 > 0)
            $sql .= " and p.areaId1=" . $areaId1;
        if ($areaId2 > 0)
            $sql .= " and p.areaId2=" . $areaId2;
        if ($goodsCatId1 > 0)
            $sql .= " and g.goodsCatId1=" . $goodsCatId1;
        if ($goodsCatId2 > 0)
            $sql .= " and g.goodsCatId2=" . $goodsCatId2;
        if ($goodsCatId3 > 0)
            $sql .= " and g.goodsCatId3=" . $goodsCatId3;
        if ($shopName != '')
            $sql .= " and (p.shopName like '%" . $shopName . "%' or p.shopSn like '%" . $shopName . "%')";
        if ($goodsName != '')
            $sql .= " and (g.goodsName like '%" . $goodsName . "%' or g.goodsSn like '%" . $goodsName . "%')";
        $sql .= "  order by act.goodsAuctionStatus asc";
        $goodsGroup = $m->pageQuery($sql);
        // print_r($goodsGroup);
        foreach ($goodsGroup['root'] as $k => $v) {
            $goodsGroupModel = M('GoodsAuction');
            $group = $goodsGroupModel->where('goodsId =' . $v['goodsId'])->find();
            $group['auctionStartTime'] = date('Y-m-d H:i:s', $group['auctionStartTime']);
            $group['auctionEndTime'] = date('Y-m-d H:i:s', $group['auctionEndTime']);
            
            $goodsGroup['root'][$k] = array_merge($goodsGroup['root'][$k], $group);
        }
        
        return $goodsGroup;
    }

    /**
     * 分页列表[获取已审核列表]
     */
    public function queryByPage()
    {
        $m = M('goods');
        $shopName = I('shopName');
        $goodsName = I('goodsName');
        $areaId1 = (int) I('areaId1', 0);
        $areaId2 = (int) I('areaId2', 0);
        $goodsCatId1 = (int) I('goodsCatId1', 0);
        $goodsCatId2 = (int) I('goodsCatId2', 0);
        $goodsCatId3 = (int) I('goodsCatId3', 0);
        $sql = "select g.*,gc.catName,sc.catName shopCatName,p.shopName,act.auctionStatus from __PREFIX__goods g
	 	      left join __PREFIX__goods_cats gc on g.goodsCatId3=gc.catId
	 	      left join __PREFIX__goods_auction act on act.goodsId=g.goodsId
	 	      left join __PREFIX__shops_cats sc on sc.catId=g.shopCatId2,__PREFIX__shops p
	 	      where  p.shopId=g.shopId and g.isAuction = 1 and act.goodsAuctionStatus=1 ";
        if ($areaId1 > 0)
            $sql .= " and p.areaId1=" . $areaId1;
        if ($areaId2 > 0)
            $sql .= " and p.areaId2=" . $areaId2;
        if ($goodsCatId1 > 0)
            $sql .= " and g.goodsCatId1=" . $goodsCatId1;
        if ($goodsCatId2 > 0)
            $sql .= " and g.goodsCatId2=" . $goodsCatId2;
        if ($goodsCatId3 > 0)
            $sql .= " and g.goodsCatId3=" . $goodsCatId3;
        if ($shopName != '')
            $sql .= " and (p.shopName like '%" . $shopName . "%' or p.shopSn like '%" . $shopName . "%')";
        if ($goodsName != '')
            $sql .= " and (g.goodsName like '%" . $goodsName . "%' or g.goodsSn like '%" . $goodsName . "%')";
        $sql .= "  order by act.auctionStatus asc";
        $goodsGroup = $m->pageQuery($sql); // 注意数据的筛选必须全包含在$sql内
                                           // 判断团购商品状态
                                           // print_r($goodsGroup['root']);
                                           
        // echo $goodsGroup['total'];
        foreach ($goodsGroup['root'] as $k => $v) {
            $goodsGroupModel = M('GoodsAuction');
            $group = $goodsGroupModel->where('goodsId =' . $v['goodsId'])->find();
            $group['auctionStartTime'] = date('Y-m-d H:i:s', $group['auctionStartTime']);
            $group['auctionEndTime'] = date('Y-m-d H:i:s', $group['auctionEndTime']);
            
            $goodsGroup['root'][$k] = array_merge($goodsGroup['root'][$k], $group);
        }
        // echo $goodsGroup['total'].'--';
        // print_r($goodsGroup['root']);
        return $goodsGroup;
    }

    /**
     * 获取列表
     */
    public function queryByList()
    {
        $m = M('goods');
        $sql = "select * from __PREFIX__goods order by goodsId desc";
        return $m->find($sql);
    }

    /**
     * 修改商品状态
     */
    public function changeGoodsStatus()
    {
        $rd = array(
            'status' => - 1
        );
        $m = M('GoodsAuction');
        $id = (int) I('id', 0);
        $data['goodsAuctionStatus'] = (int) I('status', 0);
        $temp['auctionStartTime'] = strtotime(I('auctionStartTime'));
        $temp['auctionEndTime'] = strtotime(I('auctionEndTime'));
        // 0表示未开始，1表示开始中，2表示结束
        if ($temp['auctionStartTime'] && $temp['auctionEndTime']) {
            if ($temp['auctionStartTime'] < time()) {
                if ($temp['auctionEndTime'] < time()) {
                    $data['auctionStatus'] = 2;
                } else {
                    $data['auctionStatus'] = 1;
                }
            } else {
                $data['auctionStatus'] = 0;
            }
        }
        $data['notPassReason'] = '';
        
        $rs = $m->where('id=' . $id)->save($data);
        if (false !== $rs) {
            $sql = "select goodsName,userId from __PREFIX__goods g,__PREFIX__shops s where g.shopId=s.shopId and g.goodsId=" . $id;
            $goods = $this->query($sql);
            $msg = "";
            if (I('status', 0) == 0) {
                $msg = "商品[" . $goods[0]['goodsName'] . "]已被商城下架";
            } else {
                $msg = "商品[" . $goods[0]['goodsName'] . "]已通过审核";
            }
            $yj_data = array(
                'msgType' => 0,
                'sendUserId' => session('WST_STAFF.staffId'),
                'receiveUserId' => $goods[0]['userId'],
                'msgContent' => $msg,
                'createTime' => date('Y-m-d H:i:s'),
                'msgStatus' => 0,
                'msgFlag' => 1
            );
            M('messages')->add($yj_data);
            $rd['status'] = 1;
        }
        return $rd;
    }

    /**
     * 获取待审核的商品数量
     */
    public function queryPenddingGoodsNum()
    {
        $rd = array(
            'status' => - 1
        );
        $m = M('goods');
        $sql = "select count(*) counts from __PREFIX__goods where goodsStatus=0 and goodsFlag=1";
        $rs = $this->query($sql);
        $rd['num'] = $rs[0]['counts'];
        return $rd;
    }
    // 拍卖 审核 不通过
    public function auctionNotPass()
    {
        $id = (int) I('auctionId');
        $content = I('rejectionRemarks');
        $m = M('GoodsAuction');
        $data['notPassReason'] = $content;
        $data['goodsAuctionStatus'] = 2;
        $num = $m->where('id=' . $id)->save($data);
        if ($num) {
            return 1;
        } else {
            return 2;
        }
    }
    
    // //卖家放弃拍卖的申请 转移到home模块
    // public function giveUpAuction(){
    //
    // $id = (int)I('auctionId');
    // $m=M('GoodsAuction');
    // $winNum=$m->where('goodsId='.$id)->getField('auctionWinNum');
    // $num=$m->where('goodsId='.$id)->delete();
    // $g=M('goods');
    // $num2=$g->where('goodsId='.$id)->save(array('isAuction'=>'0'));
    // $g->where('goodsId='.$id)->setInc('goodsStock',$winNum);
    // if(!!$num && !!$num2){
    // return 1;
    // }else{
    // return 2;
    // }
    // }
}
;
?>