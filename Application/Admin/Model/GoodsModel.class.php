<?php
namespace Admin\Model;

/**
 * 商品服务类
 */
class GoodsModel extends BaseModel
{

    /**
     * 获取商品信息
     */
    public function get()
    {
        $id = (int) I('id', 0);
        $sql = "select g.goodsImg,g.isSale,g.upTime,g.goodsId,g.saleCount,g.goodsSn,g.goodsName,g.goodsThums,s.shopId,g.marketPrice,g.shopPrice,
	 	g.isAdminRecom,g.createTime,g.gameId,s.shopName,ga.gameName from __PREFIX__goods as g left join __PREFIX__shops as s
	 	on g.shopId=s.shopId  left join  __PREFIX__game as ga on ga.id=g.gameId  where g.goodsId=$id  order by g.goodsId desc";
        $info = M()->query($sql);
        foreach ($info as $k => $v) {
            
            $version = M('goods_versions  as gv')->where(array(
                'gv.goodsId' => $v['goodsId']
            ))
            ->join('oto_versions as v on gv.versionsId=v.id')
            ->field('v.vName,gv.attrPrice')
            ->order('gv.attrPrice ASC')
            ->select();
            $info[$k]['lowPrice'] = $version[0]['attrPrice'];
            // 最低折扣
            $info[$k]['zhekou'] = sprintf('%0.1f', $version[0]['attrPrice'] / $v['shopPrice'] * 10);
            
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
                $info[$k]['versions'] = $vName;
            }
        }
        return $info[0];
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
            $version = M('goods_versions  as gv')->where(array(
                'gv.goodsId' => $v['goodsId']
            ))
                ->join('oto_versions as v on gv.versionsId=v.id')
                ->field('v.vName,gv.attrPrice')
                ->order('gv.attrPrice ASC')
                ->select();
            $info['root'][$k]['lowPrice'] = $version[0]['attrPrice'];
            // 最低折扣
            $info['root'][$k]['zhekou'] = sprintf('%0.1f', $version[0]['attrPrice'] / $v['shopPrice'] * 10);
            $vName = '';
            foreach ($version as $kk => $vv) {
                $vName .= $vv['vName'] . ',';
            }
            $vName = trim($vName, ',');
            $info['root'][$k]['versions'] = $vName;
        }
        
        return $info;
    }

    /**
     * 分页列表[获取已审核列表]
     * 二次开发
     * 编写者 魏永就
     * 新增参数 $msg，如果$msg有值，则queryByPage 是导出数据调用的
     */
    public function queryByPage($msg='',$type='',$start='',$end='')
    {
        $m = M('goods');
        $scope=I('scope');
        $shopName = I('shopName');
        $goodsSn = I('goodsSn');
        $starDay = I('starDay');
        $endDay = I('endDay');
        $gameName = I('gameName');
        $vName = I('vName');
        $goodsName = I('goodsName');
        
        $where = " where g.goodsFlag=1 and g.goodsStatus=1";
        
        //1首充 ，2代充，3会首，4会代充
        if($scope==1){
            $where.=" and g.scopeId=1 and g.goodsType=0 ";
        }else if($scope==2){
            $where.=" and g.scopeId=2 and g.goodsType=0  ";
        }else if($scope==3){
            $where.=" and g.scopeId=1 and g.goodsType=1  ";
        }else if($scope==4){
            $where.=" and g.scopeId=2 and g.goodsType=1  ";
        }
        
        
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
        
        if($vName){
            $where.=" and vv.vName like '%$vName%' ";
        }
        
        /**
         * @author peng	
         * @date 2017-01
         * @descreption 平台商品
         */
         $is_pingtai=I('is_pt');
         if($is_pingtai){
            $where.=" and g.shopId=0";
            if(I('member_rank')){
                $where.=" and g.member_rank=".I('member_rank');
            }
            if($_GET['isSale']!='') {
                $where.=" and g.isSale=".I('isSale');
            }
         }else{
            $where.=" and g.shopId>0";
         }
         
        
        $sql = "select g.isMiao,g.goodsThums,g.goodsImg,g.scopeId,g.goodsType,g.isHot,g.isSale,g.upTime,g.goodsId,g.saleCount,g.goodsSn,g.goodsName,g.goodsThums,s.shopId,g.marketPrice,g.shopPrice,vv.vName,
            g.isAdminRecom,g.createTime,g.gameId,s.shopName,ga.gameName,g.member_rank from __PREFIX__goods as g left join __PREFIX__shops as s
            on g.shopId=s.shopId  left join  __PREFIX__game as ga on ga.id=g.gameId  left join __PREFIX__goods_versions as gv on gv.goodsId=g.goodsId  left join __PREFIX__versions as vv on gv.versionsId=vv.id   $where  order by g.goodsId desc";

        if($msg)
        {
            if($type=='')
                die('导出数据时间类型未选择，请刷新重试');
            $where .= " and UNIX_TIMESTAMP(g.{$type}) >= $start and UNIX_TIMESTAMP(g.{$type}) <= $end ";
            $sql = "select g.isMiao,g.goodsThums,g.goodsImg,g.scopeId,g.goodsType,g.isHot,g.isSale,g.upTime,g.goodsId,g.saleCount,g.goodsSn,g.goodsName,g.goodsThums,s.shopId,g.marketPrice,g.shopPrice,vv.vName,
            g.isAdminRecom,g.createTime,g.gameId,s.shopName,ga.gameName from __PREFIX__goods as g left join __PREFIX__shops as s
            on g.shopId=s.shopId  left join  __PREFIX__game as ga on ga.id=g.gameId  left join __PREFIX__goods_versions as gv on gv.goodsId=g.goodsId  left join __PREFIX__versions as vv on gv.versionsId=vv.id   $where  order by g.goodsId desc";
            $info = $m->query($sql);
            $info['root'] = $info;
        }else{
            $info = $m->pageQuery($sql);
        }
        foreach ($info['root'] as $k => $v) {
            
            //普通 商品
            if($v['goodsType']==0){
                if($v['scopeId']==1){
                    //首充
                    $info['root'][$k]['scopeType']='首充号';
                }else{
                 //代充   
                    $info['root'][$k]['scopeType']='首充号代充';
                }
            }else{
                //会员商品
                if($v['scopeId']==1){
                    //首充
                    $info['root'][$k]['scopeType']='会员首充号';
                }else{
                    //代充
                    $info['root'][$k]['scopeType']='会员首充号代充';
                }
            }
            
            $version = M('goods_versions  as gv')->where(array(
                'gv.goodsId' => $v['goodsId']
            ))
                ->join('oto_versions as v on gv.versionsId=v.id')
                ->field('v.vName,gv.attrPrice')
                ->order('gv.attrPrice ASC')
                ->select();
            $info['root'][$k]['lowPrice'] = $version[0]['attrPrice'];
            // 最低折扣
            $info['root'][$k]['zhekou'] = sprintf('%0.1f', $version[0]['attrPrice'] / $v['shopPrice'] * 10);
            $vName = '';
            foreach ($version as $kk => $vv) {
                $vName .= $vv['vName'] . ',';
            }
            $vName = trim($vName, ',');
            $info['root'][$k]['versions'] = $vName;
        }
        
        
        
        return $info;
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
     * 修改上下架状态
     */
    public function changeGoodsSaleStatus()
    {
        $rd = array(
            'status' => - 1
        );
        $m = M('goods');
        $id = (int) I('id', 0);
        
        if ($status == 1) {
            $m->upTime = date('Y-m-d H:i:s');
        }
        /**
         * @author peng	
         * @date 2017-01
         * @descreption 如果是平台商品则首代充绑定在一起
         */
        $data=['isSale'=>(int) I('status', 0)];
        $goods_info=$m->find($id);
        if($goods_info['shopId']==0){
            #上架是有限制的
            if($data['isSale']==1 && $m->where([
            'member_rank'=>$goods_info['member_rank'],
            'isSale'=>1
            ])->find()){
                return ['status'=>0,'info'=>C('rank_name_arr')[$goods_info['member_rank']].'的商品已经有上架,'];
            }
            $re=$m->where(['goodsName'=>$goods_info['goodsName']])->save($data);    
            
        }else{
            $rs = $m->where(['goodsId'=>$id])->save($data);
        }
        
        
        if (false !== $rs) {
            $rd['status'] = 1;
        }
        return $rd;
    }

    /**
     * 修改热门状态
     */
    public function changeGoodsHotStatus()
    {
        $rd = array(
            'status' => - 1
        );
        $m = M('goods');
        $id = (int) I('id', 0);
        $status = (int) I('status', 0);
        $m->isHot = $status;
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
    public function changeUpDownStatus()
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
     * 批量修改热门
     */
    public function changeHotStatus()
    {
        $rd = array(
            'status' => - 1
        );
        $m = M('goods');
        $id = I('id', 0);
        $status = (int) I('status', 0);
        $m->isHot = $status;
        $rs = $m->where('goodsId in(' . $id . ")")->save();
        if (false !== $rs) {
            $rd['status'] = 1;
        }
        return $rd;
    }

    /**
     * 修改为推荐
     */
    public function changeRecomStatus()
    {
        $rd = array(
            'status' => - 1
        );
        $m = M('goods');
        $id = I('id', 0);
        $status = (int) I('status', 0);
        $m->isAdminRecom = $status;
        $rs = $m->where('goodsId in(' . $id . ")")->save();
        if (false !== $rs) {
            $rd['status'] = 1;
        }
        return $rd;
    }

    /**
     * 修改为秒充
     */
    public function changeMiaoStatus()
    {
        $rd = array(
            'status' => - 1
        );
        $m = M('goods');
        $id = I('id', 0);
        $status = (int) I('status', 0);
        $m->isMiao = $status;
        $rs = $m->where('goodsId in(' . $id . ")")->save();
        if (false !== $rs) {
            $rd['status'] = 1;
        }
        return $rd;
    }

    /**
     * 批量修改为推荐
     */
    public function changeAdminRecomStatus()
    {
        $rd = array(
            'status' => - 1
        );
        $m = M('goods');
        $id = I('id', 0);
        $status = (int) I('status', 0);
        $m->isAdminRecom = $status;
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
}
;
?>