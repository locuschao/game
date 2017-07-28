<?php
namespace Admin\Model;

/**
 * 积分上传模型类
 */
class IntegralModel extends BaseModel
{

    /**
     * 新增积分商品
     */
    public function insert($obj)
    {
        $IntegralModel = M('IntegralGoods');
        $data['goodsName'] = I('goodsName');
        $data['goodsContent'] = I('goodsContent');
        $data['goodsDesc'] = I('description');
        $data['price'] = I('goodsPrice');
        $data['goodsFlag'] = 1;
        $data['createTime'] = time();
        $data['goodsImg'] = $obj['goodsImg'];
        $data['goodsThumbs'] = $obj['goodsThumbs'];
        $goodsId = $IntegralModel->add($data);
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
        $m = M('IntegralGoods');
        $sql = "select * from oto_integral_goods where goodsFlag=1 order by goodsId desc";
        return $m->pageQuery($sql);
    }

    /**
     * 获取积分商品详情
     */
    public function get($id)
    {
        $m = M('IntegralGoods');
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
        $IntegralModel = M('IntegralGoods');
        $data['goodsName'] = I('goodsName');
        $data['goodsContent'] = I('goodsContent');
        $data['goodsDesc'] = I('description');
        $data['price'] = I('goodsPrice');
        $data['goodsFlag'] = 1;
        if ($obj['goodsImg']) {
            $data['goodsImg'] = $obj['goodsImg'];
            $data['goodsThumbs'] = $obj['goodsThumbs'];
        }
        $rsg = $IntegralModel->where('goodsId=' . $id)->save($data);
        $GallerysModel = M('GoodsGallerys');
        $arr['shopId'] = 0;
        $arr['goodsId'] = $id;
        if ($obj['path2'] != '') {
            $path2 = I('path2');
            $arr['goodsImg'] = $obj['path2'];
            $arr['goodsThumbs'] = $obj['path2_thumb'];
            if ($path2) {
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
}
;
?>