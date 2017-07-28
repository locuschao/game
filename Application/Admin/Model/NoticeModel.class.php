<?php
namespace Admin\Model;

/**
 * 公告类
 */
class NoticeModel extends BaseModel
{

    /**
     * 获取商品信息
     */
    public function get()
    {
        $id = (int) I('id', 0);
        $info = M('Notice')->where(array(
            'id' => $id
        ))->find();
        return $info;
    }

    public function edit()
    {
        $id = I('id', 0, intval);
        $db = M('notice');
        if ($id > 0) {
            $A = $db->save($_POST);
        } else {
            $_POST['createTime'] = date('Y-m-d H:i:s');
            $db->create();
            $A = $db->add();
        }
        if ($A != false) {
            return array(
                'status' => 0
            );
        } else {
            return array(
                'status' => - 1
            );
        }
    }

    /**
     * 分页列表[获取待审核列表]
     */
    public function queryPenddingByPage()
    {
        $m = M('goods');
        
        $shopName = I('shopName');
        $goodsSn = I('goodsSn');
        $starDay = I('starDay');
        $endDay = I('endDay');
        $gameName = I('gameName');
        $vName = I('vName');
        $goodsName = I('goodsName');
        
        $where = " where g.goodsFlag=1 and g.goodsStatus=0";
        if ($goodsName) {
            $where .= " and g.goodsName like '%$goodsName%'";
        }
        if ($goodsSn) {
            $where .= " and g.goodsSn='$goodsSn' ";
        }
        
        if ($starDay && $endDay) {
            $where .= " and g.createTime between '$starDay' and '$endDay' ";
        } else 
            if ($starDay && ! $endDay) {
                $where .= " and g.createTime > '$starDay' ";
            } else 
                if (! $starDay && $endDay) {
                    $where .= " and g.createTime < '$endDay' ";
                }
        
        if ($shopName) {
            $where .= " and s.shopName like '%$shopName%' ";
        }
        if ($gameName) {
            $where .= " and ga.gameName like '%$gameName%' ";
        }
        
        $sql = "select g.goodsImg, g.isSale,g.upTime,g.goodsId,g.saleCount,g.goodsSn,g.goodsName,g.goodsThums,s.shopId,g.marketPrice,g.shopPrice,
            g.isAdminRecom,g.createTime,g.gameId,s.shopName,ga.gameName from __PREFIX__goods as g left join __PREFIX__shops as s
            on g.shopId=s.shopId  left join  __PREFIX__game as ga on ga.id=g.gameId  $where  order by g.goodsId desc";
        $info = $m->pageQuery($sql);
        foreach ($info['root'] as $k => $v) {
            $info['root'][$k]['zhekou'] = sprintf('%0.1f', $v['shopPrice'] / $v['marketPrice'] * 10);
            $version = M('game_versions  as gv')->where(array(
                'gv.gid' => $v['gameId']
            ))
                ->join('oto_versions as v on gv.vid=v.id')
                ->field('v.vName')
                ->select();
            if (is_array($version)) {
                $vName = '';
                foreach ($version as $kk => $vv) {
                    $vName .= $vv['vName'] . ',';
                }
                $vName = trim($vName, ',');
                $info['root'][$k]['versions'] = $vName;
            }
        }
        
        return $info;
    }

    /**
     * 分页列表[获取已审核列表]
     */
    public function queryByPage()
    {
        $m = M('notice');
        $title = I('title');
        $where = "";
        if ($title) {
            $where .= "  where  title like '%$title%' ";
        }
        $sql = "select * from __PREFIX__notice $where order by id desc";
        $info = $m->pageQuery($sql);
        
        return $info;
    }

    public function gameList()
    {
        return M('game')->select();
    }

    public function versionsList()
    {
        return M('versions')->select();
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
        $m = M('goods');
        $id = (int) I('id', 0);
        $m->goodsStatus = (int) I('status', 0);
        $rs = $m->where('goodsId=' . $id)->save();
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
     * 修改商品状态
     */
    public function changeGoodsSaleStatus()
    {
        $rd = array(
            'status' => - 1
        );
        $m = M('goods');
        $id = (int) I('id', 0);
        $status = (int) I('status', 0);
        $m->isSale = $status;
        if ($status == 1) {
            $m->upTime = date('Y-m-d H:i:s');
        }
        $rs = $m->where('goodsId=' . $id)->save();
        if (false !== $rs) {
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

    /**
     * 批量修改上架时间及状态
     */
    public function changeBestStatus()
    {
        $rd = array(
            'status' => - 1
        );
        $m = M('goods');
        $id = I('id', 0);
        $status = (int) I('status', 0);
        $m->isSale = $status;
        if ($status == 1) {
            $m->upTime = date('Y-m-d H:i:s');
        }
        $rs = $m->where('goodsId in(' . $id . ")")->save();
        if (false !== $rs) {
            $rd['status'] = 1;
        }
        return $rd;
    }

    /**
     * 批量通过审核
     */
    public function changeBethGoodsStatus()
    {
        $rd = array(
            'status' => - 1
        );
        $m = M('goods');
        $id = I('id', 0);
        $m->goodsStatus = (int) I('status', 0);
        $rs = $m->where('goodsId in(' . $id . ")")->save();
        if (false !== $rs) {
            $rd['status'] = 1;
        }
        return $rd;
    }

    /**
     * 删除
     */
    public function del()
    {
        $id = (int) I('id');
        $rd = array(
            'status' => - 1
        );
        $m = M('Notice');
        // 下架所有商品
        $rs = $m->where(array(
            'id' => $id
        ))->delete();
        if (false !== $rs) {
            $rd['status'] = 1;
        }
        return $rd;
    }
}
;
?>