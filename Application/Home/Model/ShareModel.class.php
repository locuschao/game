<?php
namespace Home\Model;

/**
 * 商品晒单类
 */
class ShareModel extends BaseModel
{

    /**
     * 增加晒单分享
     */
    public function addShare($obj, $path)
    {
        $m = M('Share');
        $data = array();
        $data['goodsId'] = $obj['goodsId'];
        $data['shopId'] = $obj['shopId'];
        $data['orderId'] = $obj['orderId'];
        $data['userId'] = $obj['userId'];
        $data['shareTitle'] = I('shareTitle');
        $data['shareContent'] = I('shareContent');
        $data['shareTime'] = date('Y-m-d H:i:s');
        $data['shareImg1'] = $obj['path1'];
        $data['shareImg2'] = $obj['path2'];
        $data['shareImg3'] = $obj['path3'];
        $data['isBest'] = 1;
        $data['remark'] = 1;
        if (empty($data['shareImg1']) && empty($data['shareImg2']) && empty($data['shareImg3'])) {
            return 0;
        }
        $rs = $m->add($data);
        // 清除上一次分享晒单时的session
        unset($_SESSION['path1']);
        unset($_SESSION['path2']);
        unset($_SESSION['path3']);
        return 1;
    }

    /**
     * 商家商品晒单分页列表
     */
    public function queryByPage($shopId)
    {
        $m = M('share');
        $shopCatId1 = (int) I('shopCatId1', 0);
        $shopCatId2 = (int) I('shopCatId2', 0);
        $goodsName = I('goodsName');
        $pcurr = (int) I("pcurr", 0);
        $sql = "select gp.*,g.goodsName,g.goodsThums,u.loginName from 
	 	           __PREFIX__share gp ,__PREFIX__goods g, __PREFIX__users u
	 	           where gp.userId=u.userId and gp.goodsId=g.goodsId and gp.shopId=" . $shopId . "";
        if ($shopCatId1 > 0)
            $sql .= " and shopCatId1=" . $shopCatId1;
        if ($shopCatId2 > 0)
            $sql .= " and shopCatId2=" . $shopCatId2;
        if ($goodsName != '')
            $sql .= " and (goodsName like '%" . $goodsName . "%' or goodsSn like '%" . $goodsName . "%')";
        $sql .= " order by id desc";
        $pages = $this->pageQuery($sql, $pcurr);
        return $pages;
    }

    /**
     * 商家审核晒单
     */
    public function changeShareShow()
    {
        $m = M('Share');
        $id = (int) I('id');
        $isShow = (int) I('isShow', 0);
        $rs = $m->where('id = ' . $id)->setField('isShow', $isShow);
        return $rs;
    }
}