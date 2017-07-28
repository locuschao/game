<?php
namespace Admin\Model;

/**
 * 积分上传模型类
 */
class IntegralGoodsModel extends BaseModel
{

    /**
     * 新增积分商品分类
     */
    public function catsInsert()
    {
        $rd = array(
            'status' => - 1
        );
        $data = array();
        $data["catName"] = I("catName");
        if ($this->checkEmpty($data, true)) {
            $data["isShow"] = (int) I("isShow", 0);
            $data["catSort"] = (int) I("catSort", 0);
            $data["catFlag"] = 1;
            ;
            $m = M('IntegralCats');
            $rs = $m->add($data);
            if ($rs) {
                $rd['status'] = 1;
            }
        }
        return $rd;
    }

    /**
     * 修改商品分类
     */
    public function catsEdit()
    {
        $rd = array(
            'status' => - 1
        );
        $id = (int) I("id", 0);
        $data = array();
        $data["catName"] = I("catName");
        if ($this->checkEmpty($data)) {
            $data["isShow"] = (int) I("isShow", 0);
            $data["catSort"] = (int) I("catSort", 0);
            $m = M('IntegralCats');
            $rs = $m->where("catFlag=1 and catId=" . (int) I('id'))->save($data);
            if (false !== $rs) {
                
                $rd['status'] = 1;
            }
        }
        return $rd;
    }

    /**
     * 获取树形分类
     */
    public function getCatAndChild()
    {
        $m = M('IntegralCats');
        $sql = "select * from __PREFIX__integral_cats where catFlag=1 order by catSort asc";
        $rs = $m->query($sql);
        return $rs;
    }

    /**
     * 修改商品分类名称
     */
    public function editName()
    {
        $rd = array(
            'status' => - 1
        );
        $id = (int) I("id", 0);
        $data = array();
        $data["catName"] = I("catName");
        if ($this->checkEmpty($data)) {
            $m = M('IntegralCats');
            $rs = $m->where("catFlag=1 and catId=" . (int) I('id'))->save($data);
            if (false !== $rs) {
                $rd['status'] = 1;
            }
        }
        return $rd;
    }

    /**
     * 获取分类详情
     */
    public function catsGet($id)
    {
        $m = M('IntegralCats');
        return $m->where("catId=" . (int) $id)->find();
    }

    /**
     * 删除商品分类
     */
    public function catsDel()
    {
        $rd = array(
            'status' => - 1
        );
        $id = (int) I('id');
        $m = M('IntegralCats');
        // 把相关的商品下架了
        $sql = "update __PREFIX__integral_goods set isSale=0 where shopCatId1 = {$id}";
        $m->execute($sql);
        $sql = "update __PREFIX__integral_goods set isSale=0 where goodsCatId2 = {$id}";
        $m->execute($sql);
        $sql = "update __PREFIX__integral_goods set isSale=0 where goodsCatId3 = {$id}";
        $m->execute($sql);
        // 设置商品分类为删除状态
        $m->catFlag = - 1;
        $rs = $m->where(" catId = {$id}")->save();
        if (false !== $rs) {
            $rd['status'] = 1;
        }
        return $rd;
    }

    /**
     * 显示分类是否显示/隐藏商品分类
     */
    public function editiIsShow()
    {
        $rd = array(
            'status' => - 1
        );
        if (I('id', 0) == 0)
            return $rd;
        $isShow = (int) I('isShow');
        $id = (int) I('id');
        $m = M('IntegralCats');
        if ($isShow != 1) {
            // 把相关的商品下架了
            $sql = "update __PREFIX__goods set isSale=0 where shopCatId1 = {$id}";
            $m->execute($sql);
            $sql = "update __PREFIX__goods set isSale=0 where goodsCatId2 = {$id}";
            $m->execute($sql);
            $sql = "update __PREFIX__goods set isSale=0 where goodsCatId3 = {$id}";
            $m->execute($sql);
        }
        $m->isShow = ($isShow == 1) ? 1 : 0;
        $rs = $m->where("catId = {$id}")->save();
        if (false !== $rs) {
            $rd['status'] = 1;
        }
        return $rd;
    }

    /**
     * 新增积分商品
     */
    public function insert($obj)
    {
        $data['goodsCatId'] = I('goodsCatId');
        $data['goodsSn'] = I('goodsSn');
        $data['goodsName'] = I('goodsName');
        $data['goodsDesc'] = I('description');
        $data['marketPrice'] = I('marketPrice');
        $data['shopPrice'] = I('shopPrice');
        $data['goodsStock'] = I('goodsStock');
        $data['goodsImg'] = $obj['goodsImg'];
        $data['goodsThums'] = $obj['goodsThums'];
        $data['goodsContent'] = I('goodsContent');
        $data['goodsFlag'] = 1;
        $data['isSale'] = (int) I('isSale', 0);
        $data['createTime'] = time();
        $m = M('Integral');
        $goodsId = $m->data($data)->add();
        $GallerysModel = M('GoodsGallerys');
        $arr['goodsId'] = $goodsId;
        $arr['shopId'] = 0;
        if ($obj['path2'] != '') {
            $arr['goodsImg'] = $obj['path2'];
            $arr['goodsThumbs'] = $obj['path2_thumb'];
            $rs = $GallerysModel->add($arr);
        }
        if ($obj['path3'] != '') {
            $arr['goodsImg'] = $obj['path3'];
            $arr['goodsThumbs'] = $obj['path3_thumb'];
            $rs = $GallerysModel->add($arr);
        }
        if ($goodsId) {
            return 1;
        }
    }

    /**
     * 分页列表查看积分商品
     */
    public function queryByPage()
    {
        $m = M('Integral');
        $goodsName = I('goodsName');
        $goodsCatId = (int) I('goodsCat', 0);
        $sql = "select * from __PREFIX__integral where goodsFlag=1";
        if ($goodsName != '')
            $sql .= " and goodsName like '%" . $goodsName . "%'";
        if ($goodsCatId > 0)
            $sql .= " and goodsCatId =" . $goodsCatId;
        $sql .= ' order by goodsId asc';
        $goods = $m->pageQuery($sql);
        return $goods;
    }

    /**
     * 获取积分商品详情
     */
    public function get($id)
    {
        $m = M('Integral');
        $goods = $m->where('goodsId=' . $id)->find();
        $m = M('GoodsGallerys');
        $goods['gallerys'] = $m->where('goodsId=' . $id)->select();
        return $goods;
    }

    /**
     * 修改积分商品
     */
    public function edit($obj, $id)
    {
        $m = M('Integral');
        $data['goodsCatId'] = I('goodsCatId');
        $data['goodsSn'] = I('goodsSn');
        $data['goodsName'] = I('goodsName');
        $data['goodsDesc'] = I('description');
        $data['marketPrice'] = I('marketPrice');
        $data['shopPrice'] = I('shopPrice');
        $data['goodsStock'] = I('goodsStock');
        $data['goodsContent'] = I('goodsContent');
        if ($obj['goodsImg']) {
            $data['goodsImg'] = $obj['goodsImg'];
            $data['goodsThums'] = $obj['goodsThums'];
        }
        $rsg = $m->where('goodsId=' . $id)
            ->data($data)
            ->save();
        $GallerysModel = M('GoodsGallerys');
        $arr['shopId'] = 0;
        $arr['goodsId'] = $id;
        if ($obj['path2'] != '') {
            $path2 = I('path2');
            $arr['goodsImg'] = $obj['path2'];
            $arr['goodsThumbs'] = $obj['path2_thumb'];
            if ($path2 != false) {
                $rsgl2 = $GallerysModel->where('id=' . $path2)->save($arr);
            } else {
                $rsgl2 = $GallerysModel->add($arr);
            }
        }
        if ($obj['path3'] != '') {
            $path3 = I('path3');
            $arr['goodsImg'] = $obj['path3'];
            $arr['goodsThumbs'] = $obj['path3_thumb'];
            if ($path3) {
                $rsgl3 = $GallerysModel->where('id=' . $path3)->save($arr);
            } else {
                $rsgl3 = $GallerysModel->add($arr);
            }
        }
        if ($rsg || $rsgl2 || $rsgl3) {
            return 1;
        }
    }

    /**
     * 上架、下架积分商品
     */
    public function editiIsSale()
    {
        $rd = array(
            'status' => - 1
        );
        if (I('id', 0) == 0)
            return $rd;
        $isSale = (int) I('isSale');
        $id = (int) I('id');
        $m = M('Integral');
        $m->isSale = ($isSale == 1) ? 1 : 0;
        $rs = $m->where("goodsId = {$id}")->save();
        if (false !== $rs) {
            $rd['status'] = 1;
        }
        return $rd;
    }
}
;
?>