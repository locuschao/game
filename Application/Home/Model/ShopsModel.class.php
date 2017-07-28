<?php
namespace Home\Model;

/**
 * 店铺服务类
 */
class ShopsModel extends BaseModel
{

    /**
     * 查询登录关键字
     */
    public function checkLoginKey($val, $id = 0)
    {
        $sql = " (loginName ='%s' or userPhone ='%s' or userEmail='%s') ";
        $keyArr = array(
            $val,
            $val,
            $val
        );
        if ($id > 0)
            $sql .= " and userId!=" . $id;
        $m = M('users');
        $rs = $m->where($sql, $keyArr)->count();
        if ($rs == 0)
            return 1;
        return 0;
    }

    /**
     * 商家登录验证
     */
    public function login()
    {
        $rd = array(
            'status' => - 1
        );
        $m = M('users');
        $users = $m->where('(loginName="' . I('loginName') . '" or userPhone="' . I('loginName') . '" or userEmail="' . I('loginName') . '") and userFlag=1 and userStatus=1')->find();
        if ($users['loginPwd'] == md5(I('loginPwd') . $users['loginSecret']) && $users['userType'] >= 1) {
            // 加载商家信息
            $s = M('shops');
            $shops = $s->where('userId=' . $users['userId'] . " and shopFlag=1")->find();
            $shops["serviceEndTime"] = str_replace('.5', ':30', $shops["serviceEndTime"]);
            $shops["serviceEndTime"] = str_replace('.0', ':00', $shops["serviceEndTime"]);
            $shops["serviceStartTime"] = str_replace('.5', ':30', $shops["serviceStartTime"]);
            $shops["serviceStartTime"] = str_replace('.0', ':00', $shops["serviceStartTime"]);
            $users = array_merge($shops, $users);
            $rd['shop'] = $users;
            $rd['status'] = 1;
            $m->lastTime = date('Y-m-d H:i:s');
            $m->lastIP = get_client_ip();
            $m->where(' userId=' . $shops['userId'])->save();
            // 记录登录日志
            $data = array();
            $data["userId"] = $shops['userId'];
            $data["loginTime"] = date('Y-m-d H:i:s');
            $data["loginIp"] = get_client_ip();
            M('log_user_logins')->add($data);
        }
        return $rd;
    }

    /**
     * 加载商家信息
     */
    public function loadShopInfo($userId)
    {
        $shops = $this->queryRow('select s.*,u.userType,u.userPhone from __PREFIX__shops s,__PREFIX__users u where s.userId=u.userId and u.userId=' . $userId);
        $shops["serviceEndTime"] = str_replace('.5', ':30', $shops["serviceEndTime"]);
        $shops["serviceEndTime"] = str_replace('.0', ':00', $shops["serviceEndTime"]);
        $shops["serviceStartTime"] = str_replace('.5', ':30', $shops["serviceStartTime"]);
        $shops["serviceStartTime"] = str_replace('.0', ':00', $shops["serviceStartTime"]);
        return $shops;
    }

    /**
     * 游客开店
     */
    public function addByVisitor()
    {
        $rd = array(
            'status' => - 1
        );
        // 检测账号是否存在
        $hasLoginName = self::checkLoginKey(I("loginName"));
        if ($hasLoginName == 0) {
            $rd = array(
                'status' => - 2
            );
            return $rd;
        }
        $hasUserPhone = self::checkLoginKey(I("userPhone"));
        if ($hasUserPhone == 0) {
            $rd = array(
                'status' => - 7
            );
            return $rd;
        }
        // 新注册账号
        $data = array();
        $data["loginName"] = I("loginName");
        $data["loginSecret"] = rand(1000, 9999);
        $data["loginPwd"] = md5(I('loginPwd') . $data['loginSecret']);
        $data["userName"] = I("userName");
        $data["userPhone"] = I("userPhone");
        // 店铺资料
        $sdata = array();
        $sdata["areaId1"] = (int) I("areaId1");
        $sdata["areaId2"] = (int) I("areaId2");
        $sdata["areaId3"] = (int) I("areaId3");
        $sdata["goodsCatId1"] = (int) I("goodsCatId1");
        $sdata["shopName"] = I("shopName");
        $sdata["shopCompany"] = I("shopCompany");
        $sdata["shopImg"] = I("shopImg");
        $sdata["shopAddress"] = I("shopAddress");
        $sdata["bankId"] = (int) I("bankId");
        $sdata["bankNo"] = I("bankNo");
        $sdata["latitude"] = I("latitude");
        $sdata["longitude"] = I("longitude");
        $sdata["mapLevel"] = (int) I("mapLevel", 13);
        $sdata["isInvoice"] = (int) I("isInvoice", 0);
        $sdata["serviceStartTime"] = I("serviceStartTime");
        $sdata["serviceEndTime"] = I("serviceEndTime");
        $sdata["shopTel"] = I("shopTel");
        if ($this->checkEmpty($data, true) && $this->checkEmpty($sdata, true)) {
            $data["userStatus"] = 1;
            $data["userType"] = 0;
            $data["userFlag"] = 1;
            $data["createTime"] = date('Y-m-d H:i:s');
            $m = M('users');
            $userId = $m->add($data);
            if (false !== $userId) {
                $sdata["userId"] = $userId;
                $sdata['deliveryStartMoney'] = (float) I('deliveryStartMoney');
                $sdata["deliveryFreeMoney"] = (float) I("deliveryFreeMoney", 0);
                $sdata["deliveryMoney"] = (float) I("deliveryMoney", 0);
                $sdata["avgeCostMoney"] = (float) I("avgeCostMoney", 0);
                $sdata["invoiceRemarks"] = I("invoiceRemarks");
                $sdata["qqNo"] = I("qqNo");
                $sdata["shopStatus"] = 0;
                $sdata["shopAtive"] = (int) I("shopAtive", 1);
                $sdata["shopFlag"] = 1;
                $sdata["createTime"] = date('Y-m-d H:i:s');
                
                $m = M('shops');
                $shopId = $m->add($sdata);
                if (false !== $shopId) {
                    $rd['status'] = 1;
                    $rd['userId'] = $userId;
                    // 增加商家评分记录
                    $data = array();
                    $data['shopId'] = $shopId;
                    $m = M('shop_scores');
                    $m->add($data);
                    // 建立店铺和社区的关系
                    $relateArea = I('relateAreaId');
                    $relateCommunity = I('relateCommunityId');
                    if ($relateArea != '') {
                        $m = M('shops_communitys');
                        $relateAreas = explode(',', $relateArea);
                        foreach ($relateAreas as $v) {
                            if ($v == '' || $v == '0')
                                continue;
                            $tmp = array();
                            $tmp['shopId'] = $shopId;
                            $tmp['areaId1'] = (int) I("areaId1");
                            $tmp['areaId2'] = (int) I("areaId2");
                            $tmp['areaId3'] = $v;
                            $tmp['communityId'] = 0;
                            $ra = $m->add($tmp);
                        }
                    }
                    if ($relateCommunity != '') {
                        $m = M('communitys');
                        $lc = $m->where('communityFlag=1 and (communityId in(0,' . $relateCommunity . ") or areaId3 in(0," . $relateArea . "))")->select();
                        if (count($lc) > 0) {
                            $m = M('shops_communitys');
                            foreach ($lc as $key => $v) {
                                $tmp = array();
                                $tmp['shopId'] = $shopId;
                                $tmp['areaId1'] = $v['areaId1'];
                                $tmp['areaId2'] = $v['areaId2'];
                                $tmp['areaId3'] = $v['areaId3'];
                                $tmp['communityId'] = $v['communityId'];
                                $ra = $m->add($tmp);
                            }
                        }
                    }
                    
                    // 记录登录日志
                    $data = array();
                    $data["userId"] = $userId;
                    $data["loginTime"] = date('Y-m-d H:i:s');
                    $data["loginIp"] = get_client_ip();
                    M('log_user_logins')->add($data);
                }
            }
        }
        
        return $rd;
    }

    /**
     * 会员注册开通店铺
     */
    public function addByUser($userId)
    {
        $rd = array(
            'status' => - 1
        );
        // 新注册账号
        $data = array();
        $data["userName"] = I("userName");
        if ($this->checkEmpty($data, true)) {
            $m = M('users');
            $rs = $m->where('userId=' . $userId)->save($data);
            if (false !== $rs) {
                $data = array();
                $data["userId"] = $userId;
                $data["areaId1"] = (int) I("areaId1");
                $data["areaId2"] = (int) I("areaId2");
                $data["areaId3"] = (int) I("areaId3");
                $data["goodsCatId1"] = (int) I("goodsCatId1");
                $data["isSelf"] = (int) I("isSelf", 0);
                if ($data["isSelf"] == 1) {
                    $data["deliveryType"] = 1;
                } else {
                    $data["deliveryType"] = 0;
                }
                $data["shopName"] = I("shopName");
                $data["shopCompany"] = I("shopCompany");
                $data["shopImg"] = I("shopImg");
                $data["shopAddress"] = I("shopAddress");
                $data['deliveryStartMoney'] = (float) I('deliveryStartMoney');
                $data["deliveryFreeMoney"] = (float) I("deliveryFreeMoney", 0);
                $data["deliveryMoney"] = (float) I("deliveryMoney", 0);
                $data["avgeCostMoney"] = (float) I("avgeCostMoney", 0);
                $data["bankId"] = (int) I("bankId");
                $data["bankNo"] = I("bankNo");
                $data["isInvoice"] = (int) I("isInvoice", 0);
                $data["serviceStartTime"] = I("serviceStartTime");
                $data["serviceEndTime"] = I("serviceEndTime");
                $data["shopStatus"] = 0;
                $data["shopAtive"] = (int) I("shopAtive", 1);
                $data["shopFlag"] = 1;
                $data["latitude"] = I("latitude");
                $data["longitude"] = I("longitude");
                $data["mapLevel"] = (int) I("mapLevel", 13);
                $data["createTime"] = date('Y-m-d H:i:s');
                $data["shopTel"] = I("shopTel");
                if ($this->checkEmpty($data, true)) {
                    $data["qqNo"] = I("qqNo");
                    $data["invoiceRemarks"] = I("invoiceRemarks");
                    $m = M('shops');
                    $shopId = $m->add($data);
                    if (false !== $shopId) {
                        
                        // 默认添加首页充号和首充号代充分类
                        $existsShouchong = M('shops_cats')->where(array(
                            'catFlag' => 1,
                            'catName' => '首充号',
                            'shopId' => $shopId
                        ))->find();
                        if (! $existsShouchong) {
                            $shouchong['shopId'] = $shopId;
                            $shouchong['parentId'] = 0;
                            $shouchong['isShow'] = 1;
                            $shouchong['catName'] = '首充号';
                            $shouchong['catSort'] = 0;
                            M('shops_cats')->add($shouchong);
                        }
                        $existsDaichong = M('shops_cats')->where(array(
                            'catFlag' => 1,
                            'catName' => '首充号代充',
                            'shopId' => $shopId
                        ))->find();
                        if (! $existsShouchong) {
                            $shouchong['shopId'] = $shopId;
                            $shouchong['parentId'] = 0;
                            $shouchong['isShow'] = 1;
                            $shouchong['catName'] = '首充号代充';
                            $shouchong['catSort'] = 0;
                            M('shops_cats')->add($shouchong);
                        }
                        
                        $existsFenxiao = M('shops_cats')->where(array(
                            'catFlag' => 1,
                            'catName' => '首充号分销',
                            'shopId' => $shopId
                        ))->find();
                        if (! $existsFenxiao) {
                            $shouchong['shopId'] = $shopId;
                            $shouchong['parentId'] = 0;
                            $shouchong['isShow'] = 1;
                            $shouchong['catName'] = '首充号分销';
                            $shouchong['catSort'] = 0;
                            M('shops_cats')->add($shouchong);
                        }
                        $existsZhichong = M('shops_cats')->where(array(
                            'catFlag' => 1,
                            'catName' => '自主充值',
                            'shopId' => $shopId
                        ))->find();
                        if (! $existsZhichong) {
                            $shouchong['shopId'] = $shopId;
                            $shouchong['parentId'] = 0;
                            $shouchong['isShow'] = 1;
                            $shouchong['catName'] = '自主充值';
                            $shouchong['catSort'] = 0;
                            M('shops_cats')->add($shouchong);
                        }
                        
                        $rd['status'] = 1;
                        // 增加商家评分记录
                        $data = array();
                        $data['shopId'] = $shopId;
                        $m = M('shop_scores');
                        $m->add($data);
                    }
                }
            }
        }
        
        return $rd;
    }

    /**
     * 修改
     */
    public function edit($shopId)
    {
        $rd = array(
            'status' => - 1
        );
        if ($shopId == 0)
            return $rd;
        $m = M('shops');
        // 加载商店信息
        $shops = $m->where('shopId=' . $shopId)->find();
        $data = array();
        $data["shopName"] = I("shopName");
        //$data["agentStatus"] = I("agentStatus", 0);//分销需要到平台申请开通，不允许随意开启或关闭
        $data["shopImg"] = I("shopImg");
        $data["serviceStartTime"] = I("serviceStartTime");
        $data["serviceEndTime"] = I("serviceEndTime");
        $data["bankId"] = (int) I("bankId");
        $data["scope"] = I("scope", 0);
        $data["bankNo"] = I("bankNo");
        if ($this->checkEmpty($data, true)) {
            $rs = $m->where("shopId=" . $shopId)->save($data);
            if (false !== $rs) {
                S('WST_CACHE_RECOMM_SHOP_' . $shops['areaId2'], null);
                $USER = session('WST_USER');
                $data["serviceEndTime"] = str_replace('.5', ':30', $data["serviceEndTime"]);
                $data["serviceStartTime"] = str_replace('.5', ':30', $data["serviceStartTime"]);
                session('WST_USER', array_merge($USER, $data));
                $rd['status'] = 1;
                // 修改用户资料
                $m = M('users');
                $data = array();
                $data[userName] = I("userName");
                $m->where("userId=" . $shops['userId'])->save($data);
            }
        }
        return $rd;
    }

    /**
     * 修改店铺设置
     */
    public function editShopCfg($shopId)
    {
        $mc = M('shop_configs');
        // 加载商店信息
        $shopcg = $mc->where('shopId=' . $shopId)->find();
        
        $scdata = array();
        $scdata["shopId"] = $shopId;
        $scdata["shopTitle"] = I("shopTitle");
        $scdata["shopKeywords"] = I("shopKeywords");
        $scdata["shopBanner"] = I("shopBanner");
        $scdata["shopDesc"] = I("shopDesc");
        $scdata["shopAds"] = I("shopAds");
        $scdata["shopAdsUrl"] = I("shopAdsUrl");
        if ($shopcg["configId"] > 0) {
            $rs = $mc->where("shopId=" . $shopId)->save($scdata);
        } else {
            $mc->add($scdata);
        }
        S('WST_CACHE_RECOMM_SHOP_' . $shopcg['areaId2'], null);
        $rd['status'] = 1;
        return $rd;
    }

    /**
     * 获取指定对象
     */
    public function get($id)
    {
        $m = M('shops');
        $rs = $m->where("shopId=" . (int) $id)->find();
        $m = M('users');
        $us = $m->where("userId=" . $rs['userId'])->find();
        $rs['userName'] = $us['userName'];
        $rs['userPhone'] = $us['userPhone'];
        return $rs;
    }

    /**
     * 获取指定对象
     */
    public function getShopCfg($id)
    {
        $mc = M('shop_configs');
        $rs = $mc->where("shopId=" . $id)->find();
        $shopAds = array();
        if ($rs["shopAds"] != '') {
            $shopAdsImg = explode('#@#', $rs["shopAds"]);
            $shopAdsUrl = explode('#@#', $rs["shopAdsUrl"]);
            for ($i = 0; $i < count($shopAdsImg); $i ++) {
                $adsImg = $shopAdsImg[$i];
                $shopAds[$i]["adImg"] = $adsImg;
                $imgpaths = explode('.', $adsImg);
                $shopAds[$i]["adImg_thumb"] = $imgpaths[0] . "_thumb." . $imgpaths[1];
                $shopAds[$i]["adUrl"] = $shopAdsUrl[$i];
            }
        }
        $rs['shopAds'] = $shopAds;
        
        return $rs;
    }

    /**
     * 获取店铺信息
     */
    public function getShopInfo($oshopId = 0)
    {
        $m = M('shops');
        $shopId = (int) I("shopId");
        $shopId = ($shopId == 0) ? $oshopId : $shopId;
        $rs = $m->where("shopStatus=1 and shopId=" . $shopId)->find();
        if (empty($rs))
            return array();
        $mc = M('shop_configs');
        $spc = $mc->where("shopId=" . $shopId)->find();
        $shopAds = array();
        if ($spc["shopAds"] != '') {
            $shopAdsImg = explode('#@#', $spc["shopAds"]);
            $shopAdsUrl = explode('#@#', $spc["shopAdsUrl"]);
            for ($i = 0; $i < count($shopAdsImg); $i ++) {
                $adsImg = $shopAdsImg[$i];
                $shopAds[$i]["adImg"] = $adsImg;
                $imgpaths = explode('.', $adsImg);
                $shopAds[$i]["adImg_thumb"] = $imgpaths[0] . "_thumb." . $imgpaths[1];
                $shopAds[$i]["adUrl"] = $shopAdsUrl[$i];
            }
        }
        $rs['shopAds'] = $shopAds;
        $rs['shopTitle'] = $spc["shopTitle"];
        $rs['shopDesc'] = $spc["shopDesc"];
        $rs['shopKeywords'] = $spc["shopKeywords"];
        $rs['shopBanner'] = $spc["shopBanner"];
        
        // 热销排名
        $sql = "SELECT g.saleCount, g.shopId , g.goodsId , g.goodsName,g.goodsImg, g.goodsThums,g.shopPrice,g.marketPrice, g.goodsSn 
						FROM __PREFIX__goods g 
						WHERE g.goodsFlag = 1 AND g.isAdminBest = 1 AND g.isSale = 1 AND g.goodsStatus = 1 AND g.shopId = $shopId
						ORDER by g.saleCount desc limit 5";
        $hotgoods = $this->query($sql);
        $rs["hotgoods"] = $hotgoods;
        
        return $rs;
    }

    /**
     * 统计附近的商铺
     */
    public function getDistrictsShops($obj)
    {
        $m = M('areas');
        $areaId3 = (int) $obj["areaId3"];
        $shopName = $obj["shopName"];
        $keyWords = I("keyWords");
        $deliveryStartMoney = $obj["deliveryStartMoney"];
        if ($deliveryStartMoney != - 1) {
            $deliverys = explode("-", $deliveryStartMoney);
            $deliveryStart = intval($deliverys[0]);
            $deliveryEnd = intval($deliverys[1]);
        }
        
        $deliveryMoney = $obj["deliveryMoney"];
        if ($deliveryMoney != - 1) {
            $mdeliverys = explode("-", $deliveryMoney);
            $mdeliveryStart = intval($mdeliverys[0]);
            $mdeliveryEnd = intval($mdeliverys[1]);
        }
        
        $shopAtive = (int) $obj["shopAtive"];
        
        $words = array();
        $words1 = array();
        $words2 = array();
        if ($keyWords != "") {
            $keyWords = urldecode($keyWords);
            $words1 = explode(" ", $keyWords);
        }
        
        $words = array();
        if ($shopName != "") {
            $keyWords = urldecode($shopName);
            $words2 = explode(" ", $keyWords);
        }
        $words = array_merge($words1, $words2);
        $dsplist = array();
        $sql = "SELECT communityId,communityName from __PREFIX__communitys WHERE communityFlag=1 AND isShow = 1 AND areaId3=" . $areaId3;
        $ctlist = $this->query($sql);
        $ctsplist = array();
        for ($k = 0; $k < count($ctlist); $k ++) {
            $community = $ctlist[$k];
            $communityId = $community["communityId"];
            $sql = "SELECT count(*) as spcnt from __PREFIX__shops_communitys sc,__PREFIX__shops sp WHERE sp.shopStatus = 1 AND sp.shopFlag = 1 AND sc.shopId = sp.shopId AND communityId=" . $communityId;
            
            if (! empty($words)) {
                $sarr = array();
                foreach ($words as $key => $word) {
                    if ($word != "") {
                        $sarr[] = "sp.shopName LIKE '%$word%'";
                    }
                }
                $sql .= " AND (" . implode(" or ", $sarr) . ")";
            }
            /*
             * if($keyWords!="" && $shopName!=""){
             * $sql .= " AND (sp.shopName like '%$keyWords%' OR shopName like '%$shopName%')";
             * }else{
             * if($keyWords!=""){
             * $sql .= " AND sp.shopName like '%$keyWords%'";
             * }
             * if($shopName!=""){
             * $sql .= " AND sp.shopName like '%$shopName%'";
             * }
             * }
             */
            if ($deliveryStart != "" && $deliveryStart >= 0) {
                $sql .= " AND deliveryStartMoney >= $deliveryStart";
            }
            if ($deliveryEnd != "" && $deliveryEnd > 0) {
                $sql .= " AND deliveryStartMoney < $deliveryEnd";
            }
            
            if ($mdeliveryStart != "" && $mdeliveryStart >= 0) {
                $sql .= " AND deliveryMoney >= $mdeliveryStart";
            }
            if ($mdeliveryEnd != "" && $mdeliveryEnd > 0) {
                $sql .= " AND deliveryMoney < $mdeliveryEnd";
            }
            
            if ($shopAtive != "" && $shopAtive >= 0) {
                $sql .= " AND shopAtive = $shopAtive";
            }
            $splist = $this->query($sql);
            $spcnt = $splist[0]["spcnt"];
            $community["spcnt"] = $spcnt;
            if ($spcnt > 0)
                $ctsplist[] = $community;
        }
        $districts["ctlist"] = $ctsplist;
        if (count($ctsplist) > 0)
            $dsplist[] = $districts;
        return $dsplist;
    }

    /**
     * 统计附近的商铺
     */
    public function getShopByCommunitys($obj)
    {
        $communityId = (int) $obj["communityId"];
        $shopName = $obj["shopName"];
        $keyWords = I("keyWords");
        $pcurr = (int) I("curr");
        $deliveryStartMoney = $obj["deliveryStartMoney"];
        if ($deliveryStartMoney != - 1) {
            $deliverys = explode("-", $deliveryStartMoney);
            $deliveryStart = intval($deliverys[0]);
            $deliveryEnd = intval($deliverys[1]);
        }
        
        $deliveryMoney = $obj["deliveryMoney"];
        if ($deliveryMoney != - 1) {
            $mdeliverys = explode("-", $deliveryMoney);
            $mdeliveryStart = intval($mdeliverys[0]);
            $mdeliveryEnd = intval($mdeliverys[1]);
        }
        
        $words = array();
        $words1 = array();
        $words2 = array();
        if ($keyWords != "") {
            $keyWords = urldecode($keyWords);
            $words1 = explode(" ", $keyWords);
        }
        
        $words = array();
        if ($shopName != "") {
            $keyWords = urldecode($shopName);
            $words2 = explode(" ", $keyWords);
        }
        $words = array_merge($words1, $words2);
        
        $shopAtive = $obj["shopAtive"];
        $dsplist = array();
        $sql = "SELECT sp.shopId,sp.shopName,sp.shopAddress,sp.deliveryStartMoney,sp.shopAtive,sp.deliveryMoney,sp.shopImg,sp.deliveryCostTime,sp.deliveryFreeMoney
		   ,sp.avgeCostMoney from __PREFIX__shops_communitys sc,__PREFIX__shops sp WHERE sp.shopStatus = 1 AND sp.shopFlag = 1 AND sc.shopId = sp.shopId AND sc.communityId=" . $communityId;
        
        /*
         * if($keyWords!="" && $shopName!=""){
         * $sql .= " AND (sp.shopName like '%$keyWords%' OR shopName like '%$shopName%')";
         * }else{
         * if($keyWords!=""){
         * $sql .= " AND sp.shopName like '%$keyWords%'";
         * }
         * if($shopName!=""){
         * $sql .= " AND sp.shopName like '%$shopName%'";
         * }
         * }
         */
        
        if (! empty($words)) {
            $sarr = array();
            foreach ($words as $key => $word) {
                if ($word != "") {
                    $sarr[] = "sp.shopName LIKE '%$word%'";
                }
            }
            $sql .= " AND (" . implode(" or ", $sarr) . ")";
        }
        
        if ($deliveryStart != "" && $deliveryStart >= 0) {
            $sql .= " AND deliveryStartMoney >= $deliveryStart";
        }
        if ($deliveryEnd != "" && $deliveryEnd > 0) {
            $sql .= " AND deliveryStartMoney < $deliveryEnd";
        }
        
        if ($mdeliveryStart != "" && $mdeliveryStart >= 0) {
            $sql .= " AND deliveryMoney >= $mdeliveryStart";
        }
        if ($mdeliveryEnd != "" && $mdeliveryEnd > 0) {
            $sql .= " AND deliveryMoney < $mdeliveryEnd";
        }
        
        if ($shopAtive != "" && $shopAtive >= 0) {
            $sql .= " AND shopAtive = $shopAtive";
        }
        $dslist = $this->pageQuery($sql, $pcurr);
        
        return $dslist;
    }

    /**
     * 统计店铺信息
     */
    public function getShopDetails($obj)
    {
        $shopId = (int) $obj["shopId"];
        $dsplist = array();
        $sql = "SELECT totalScore,totalScore ,
				       goodsScore,goodsUsers,
				       serviceScore,serviceUsers,
					   timeScore,timeUsers
				FROM __PREFIX__shop_scores WHERE shopId = $shopId";
        $scores = $this->queryRow($sql);
        $data = array();
        $data["goodsScore"] = $scores["goodsUsers"] ? round($scores["goodsScore"] / $scores["goodsUsers"]) : 0;
        $data["timeScore"] = $scores["timeUsers"] ? round($scores["timeScore"] / $scores["timeUsers"]) : 0;
        $data["serviceScore"] = $scores["serviceUsers"] ? round($scores["serviceScore"] / $scores["serviceUsers"]) : 0;
        // 待审核商品
        $sql = "SELECT count(*) cnt FROM __PREFIX__goods WHERE goodsStatus = 0 and goodsFlag=1 and isSale=1 and shopId = $shopId";
        $goods = $this->queryRow($sql);
        $data["waitGoodsCnt"] = $goods["cnt"];
        // 仓库中商品
        $sql = "SELECT count(*) cnt FROM __PREFIX__goods WHERE isSale = 0 and goodsFlag=1 AND shopId = $shopId";
        $goods = $this->queryRow($sql);
        $data["waitSaleGoodsCnt"] = $goods["cnt"];
        // 出售中的商品
        $sql = "SELECT count(*) cnt FROM __PREFIX__goods WHERE isSale = 1 AND goodsStatus = 1 and goodsFlag=1 AND shopId = $shopId";
        $goods = $this->queryRow($sql);
        $data["onSaleGoodsCnt"] = $goods["cnt"];
        // 买家留言
        $sql = "SELECT count(*) cnt FROM __PREFIX__goods_appraises WHERE shopId = $shopId";
        $appraises = $this->queryRow($sql);
        $data["appraisesCnt"] = $appraises["cnt"];
        // 待受理订单
        $sql = "SELECT count(*) cnt FROM __PREFIX__orders WHERE shopId = $shopId AND orderStatus = 0 and orderFlag=1";
        $orders = $this->queryRow($sql);
        $data["waitHandleOrderCnt"] = $orders["cnt"];
        // 待发货订单
        $sql = "SELECT count(*) cnt FROM __PREFIX__orders WHERE shopId = $shopId AND orderStatus in (1,2) and orderFlag=1";
        $orders = $this->queryRow($sql);
        $data["waitSendOrderCnt"] = $orders["cnt"];
        
        // 待结束
        $sql = "SELECT count(*) cnt FROM __PREFIX__orders WHERE shopId = $shopId AND orderStatus in (3,-3) and orderFlag=1";
        $appOrders = $this->queryRow($sql);
        $data["appraisesOrderCnt"] = $appOrders["cnt"];
        
        // 周订单量
        $wdate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 7, date("Y")));
        $sql = "SELECT count(*) cnt, sum(totalMoney) totalMoney FROM __PREFIX__orders WHERE shopId = $shopId AND createTime >='$wdate' and orderFlag=1 and orderStatus>=0 ";
        $orders = $this->queryRow($sql);
        $data["weekOrderCnt"] = $orders["cnt"];
        $data["weekOrderMoney"] = $orders["totalMoney"] ? $orders["totalMoney"] : 0;
        
        // 一个月订单量
        $mdate = date("Y-m-d", mktime(0, 0, 0, date("m") - 1, date("d"), date("Y")));
        $sql = "SELECT count(*) cnt, sum(totalMoney) totalMoney FROM __PREFIX__orders WHERE shopId = $shopId AND createTime >='$mdate' and orderFlag=1 and orderStatus>=0 ";
        $orders = $this->queryRow($sql);
        $data["monthOrderCnt"] = $orders["cnt"];
        $data["monthOrderMoney"] = $orders["totalMoney"] ? $orders["totalMoney"] : 0;
        return $data;
    }

    /**
     * 获取店铺评分
     */
    public function getShopScores($obj)
    {
        $shopId = (int) $obj["shopId"];
        $sql = "SELECT totalScore,totalScore ,goodsScore,goodsUsers,serviceScore,serviceUsers,timeScore,timeUsers
				FROM __PREFIX__shop_scores WHERE shopId = $shopId";
        $scores = $this->queryRow($sql);
        $data = array();
        $goodsScore = $scores["goodsUsers"] ? sprintf('%.1f', $scores["goodsScore"] / $scores["goodsUsers"]) : 0;
        $timeScore = $scores["timeUsers"] ? sprintf('%.1f', $scores["timeScore"] / $scores["timeUsers"]) : 0;
        $serviceScore = $scores["serviceUsers"] ? sprintf('%.1f', $scores["serviceScore"] / $scores["serviceUsers"]) : 0;
        $data["goodsScore"] = $goodsScore;
        $data["timeScore"] = $timeScore;
        $data["serviceScore"] = $serviceScore;
        return $data;
    }

    /**
     * 检查用户是否正在申请开店
     */
    public function checkOpenShopStatus($userId)
    {
        $m = M('shops');
        return $m->where('userId=' . $userId . " and shopFlag=1 ")->getField('shopStatus', true);
    }

    /**
     * 获取自营店铺
     */
    public function getSelfShop($areaId2)
    {
        $m = M('shops');
        $sql = "select * from __PREFIX__shops where areaId2=" . $areaId2 . " and isSelf=1 and shopFlag=1 and shopStatus = 1";
        $shop = $this->queryRow($sql);
        return $shop;
    }

    /**
     * 检测自营店铺ID
     */
    function checkSelfShopId($areaId2)
    {
        $m = M('shops');
        $sql = "select shopId from __PREFIX__shops where areaId2=" . $areaId2 . " and isSelf=1 and shopFlag=1 and shopStatus = 1";
        $shop = $this->queryRow($sql);
        return (int) $shop['shopId'];
    }

    /**
     * 获取店铺搜索提示列表
     * 
     * @return \Think\mixed
     */
    public function getKeyList($areaId2)
    {
        $keywords = I("keywords");
        $sql = "select DISTINCT shopName as searchKey from __PREFIX__shops where areaId2=" . $areaId2 . " and shopFlag=1 and shopStatus = 1 and shopName like '%$keywords%' Order by shopName asc limit 10";
        $rs = $this->query($sql);
        return $rs;
    }
}