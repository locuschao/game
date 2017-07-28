<?php
namespace Admin\Model;

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
            $sql .= " and userId!=" . (int) $id;
        $m = M('users');
        $rs = $m->where($sql, $keyArr)->count();
        if ($rs == 0)
            return 1;
        return 0;
    }

    /**
     * 新增
     */
    public function insert()
    {
        $rd = array(
            'status' => - 1
        );
        // 先建立账号
        $hasLoginName = self::checkLoginKey(I("loginName"));
        $hasUserPhone = self::checkLoginKey(I("userPhone"));
        if ($hasLoginName == 0 || $hasUserPhone == 0) {
            $rd = array(
                'status' => - 2
            );
            return $rd;
        }
        // 用户资料
        $data = array();
        $data["loginName"] = I("loginName");
        $data["loginSecret"] = rand(1000, 9999);
        $data["loginPwd"] = md5(I('loginPwd') . $data['loginSecret']);
        $data["userName"] = I("userName");
        $data["userPhone"] = I("userPhone");
        // 店铺资料
        $sdata = array();
        $sdata["shopSn"] = I("shopSn");
        $sdata["goodsCatId1"] = (int) I("goodsCatId1");
        $sdata["shopName"] = I("shopName");
        $sdata["shopCompany"] = I("shopCompany");
        $sdata["shopImg"] = I("shopImg");
        $sdata["scope"] = I("scope", 0);
        $sdata["shopAddress"] = I("shopAddress");
        $sdata["bankId"] = (int) I("bankId");
        $sdata["bankNo"] = I("bankNo");
        $sdata["agentStatus"] = I("agentStatus", 0);
        $sdata["isAdminRecom"] = I("isAdminRecom", 0);
        $sdata["serviceStartTime"] = I("serviceStartTime");
        $sdata["serviceEndTime"] = I("serviceEndTime");
        $sdata["shopTel"] = I("shopTel");
        if ($this->checkEmpty($data, true) && $this->checkEmpty($sdata, true)) {
            $data["userStatus"] = (int) I("userStatus", 1);
            $data["userType"] = (int) I("userType", 1);
            $data["userEmail"] = I("userEmail");
            $data["userQQ"] = I("userQQ");
            $data["userScore"] = I("userScore", 0);
            $data["userTotalScore"] = I("userTotalScore", 0);
            $data["userFlag"] = 1;
            $data["createTime"] = date('Y-m-d H:i:s');
            $m = M('users');
            $userId = $m->add($data);
            if (false !== $userId) {
                $sdata["userId"] = $userId;
                $sdata["isSelf"] = (int) I("isSelf", 0);
                if ($sdata["isSelf"] == 1) {
                    $sdata["deliveryType"] = 1;
                } else {
                    $sdata["deliveryType"] = 0;
                }
                $sdata["deliveryStartMoney"] = I("deliveryStartMoney", 0);
                $sdata["agentStatus"] = I("agentStatus");
                $sdata["deliveryCostTime"] = I("deliveryCostTime", 0);
                $sdata["agentStatus"] = I("agentStatus");
                $sdata["deliveryFreeMoney"] = I("deliveryFreeMoney", 0);
                $sdata["deliveryMoney"] = I("deliveryMoney", 0);
                $data["isAdminRecom"] = I("isAdminRecom", 0);
                $sdata["avgeCostMoney"] = I("avgeCostMoney", 0);
                $sdata["shopStatus"] = (int) I("shopStatus", 1);
                $sdata["shopAtive"] = (int) I("shopAtive", 1);
                $sdata["shopFlag"] = 1;
                $sdata["createTime"] = date('Y-m-d H:i:s');
                $sdata['qqNo'] = I('qqNo');
                $m = M('shops');
                $shopId = $m->add($sdata);
                if (false !== $shopId) {
                 /*    // 默认添加首页充号和首充号代充分类
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
                    } */
                    
                    // 添加默认价格属性分类
                    
                    // 添加属性
                   /*  $isCatid = M('attribute_cats')->where(array(
                        'shopId' => $shopId
                    ))->find();
                    if (! $isCatid) {
                        $data = array();
                        $data['catName'] = '游戏版本';
                        $data["shopId"] = $shopId;
                        $data['catFlag'] = 1;
                        $data['createTime'] = date('Y-m-d H:i:s');
                        $catId = M('attribute_cats')->add($data);
                    }
                    
                    // 二级
                    $isAttrid = M('attributes')->where(array(
                        'shopId' => $shopId
                    ))->find();
                    if (! $isAttrid) {
                        $data = array();
                        $data['shopId'] = $shopId;
                        $data['catId'] = $catId;
                        $data['attrName'] = '游戏版本';
                        $data['isPriceAttr'] = 1;
                        $data['attrType'] = 0;
                        $data['attrContent'] = '';
                        $data['attrSort'] = 0;
                        $data['attrFlag'] = 1;
                        $data['createTime'] = date('Y-m-d H:i:s');
                        $attrId = M('attributes')->add($data);
                    } */
                    
                    $rd['status'] = 1;
                    // 增加商家评分记录
                    $data = array();
                    $data['shopId'] = $shopId;
                    $m = M('shop_scores');
                    $m->add($data);
                }
            }
        }
        
        return $rd;
    }

    /**
     * 修改
     */
    public function edit()
    {
        $rd = array(
            'status' => - 1
        );
        $shopId = (int) I('id', 0);
        if ($shopId == 0)
            return rd;
        $m = M('shops');
        // 获取店铺资料
        $shops = $m->where("shopId=" . $shopId)->find();
        // 检测手机号码是否存在
        if (I("userPhone") != '') {
            $hasUserPhone = self::checkLoginKey(I("userPhone"), $shops['userId']);
            if ($hasUserPhone == 0) {
                $rd = array(
                    'status' => - 2
                );
                return $rd;
            }
        }
        $data = array();
        $data["shopSn"] = I("shopSn");
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
        $data["bankId"] = I("bankId");
        $data["scope"] = I("scope", 0);
        $data["bankNo"] = I("bankNo");
        $data["agentStatus"] = I("agentStatus", 0);
        $data["serviceStartTime"] = I("serviceStartTime");
        $data["serviceEndTime"] = I("serviceEndTime");
        $data["shopStatus"] = (int) I("shopStatus", 0);
        $data["shopAtive"] = (int) I("shopAtive", 1);
        $data["shopTel"] = I("shopTel");
        $data["isAdminRecom"] = I("isAdminRecom", 0);
        $data["agentStatus"] = I("agentStatus");
        if ($this->checkEmpty($data, true)) {
            $data['qqNo'] = I('qqNo');
            $rs = $m->where("shopId=" . $shopId)->save($data);
            if (false !== $rs) {
                $shopMessage = '';
                // 如果[已通过的店铺]被改为未审核的话也要停止了该店铺的商品
                if ($shops['shopStatus'] != $data['shopStatus']) {
                    if ($data['shopStatus'] != 1) {
                        $sql = "update __PREFIX__goods set isSale=0,goodsStatus=0 where shopId=" . $shopId;
                        $m->execute($sql);
                        $shopMessage = "您的店铺状态已被改为“未审核”状态，如有疑问请与商场管理员联系。";
                    }
                    if ($shops['shopStatus'] != 1 && $data['shopStatus'] == 1) {
                        $shopMessage = "您的店铺状态已被改为“已审核”状态，您可以出售自己的商品啦~";
                    }
                    $yj_data = array(
                        'msgType' => 0,
                        'sendUserId' => session('WST_STAFF.staffId'),
                        'receiveUserId' => $shops['userId'],
                        'msgContent' => $shopMessage,
                        'createTime' => date('Y-m-d H:i:s'),
                        'msgStatus' => 0,
                        'msgFlag' => 1
                    );
                    M('messages')->add($yj_data);
                }
                // 检查用户类型
                $m = M('users');
                $userType = $m->where('userId=' . $shops['userId'])->getField('userType');
                
                // 保存用户资料
                $data = array();
                $data["userName"] = I("userName");
                $data["userPhone"] = I("userPhone");
                
                // 如果是普通用户则提升为店铺会员
                if ($userType == 0) {
                    $data["userType"] = 1;
                }
                $urs = $m->where("userId=" . $shops['userId'])->save($data);
                $rd['status'] = 1;
            }
        }
        
        return $rd;
    }

    /**
     * 获取指定对象
     */
    public function get()
    {
        $m = M('shops');
        $rs = $m->where("shopId=" . (int) I('id'))->find();
        $m = M('users');
        $us = $m->where("userId=" . $rs['userId'])->find();
        $rs['userName'] = $us['userName'];
        $rs['userPhone'] = $us['userPhone'];
        // 获取店铺社区关系
        $m = M('shops_communitys');
        $rc = $m->where('shopId=' . (int) I('id'))->select();
        $relateArea = array();
        $relateCommunity = array();
        if (count($rc) > 0) {
            foreach ($rc as $v) {
                if ($v['communityId'] == 0 && ! in_array($v['areaId3'], $relateArea))
                    $relateArea[] = $v['areaId3'];
                if (! in_array($v['communityId'], $relateCommunity))
                    $relateCommunity[] = $v['communityId'];
            }
        }
        $rs['relateArea'] = implode(',', $relateArea);
        $rs['relateCommunity'] = implode(',', $relateCommunity);
        return $rs;
    }

    public function setSort()
    {
        $shopId = I('shopId', 0, intval);
        $sort = I('sort', 0, intval);
        if (! $shopId || $sort < 0) {
            return array(
                'status' => - 1
            );
        }
        $rs = M('shops')->where(array(
            'shopId' => $shopId
        ))->setField('sort', $sort);
        if ($rs !== false) {
            return array(
                'status' => 0
            );
        } else {
            return array(
                'status' => - 1
            );
        }
    }

    public function setHot()
    {
        $shopId = I('shopId', 0, intval);
        $val = I('val', 0, intval);
        if (! $shopId) {
            return array(
                'status' => - 1
            );
        }
        $rs = M('shops')->where(array(
            'shopId' => $shopId
        ))->setField('isAdminRecom', $val);
        if ($rs !== false) {
            return array(
                'status' => 0
            );
        } else {
            return array(
                'status' => - 1
            );
        }
    }

    public function getHotShop()
    {
        $m = M('shops');
        $id = I('shopId', 0);
        $shopName = I('shopName');
        $isAdminRecom = I('isAdminRecom', 1);
        $where = "where shopFlag=1 ";
        if ($id) {
            $where .= " and shopID=$id ";
        } else {
            if ($shopName) {
                $where .= " and shopName like '%$shopName%'";
            }
            if (! empty($isAdminRecom)) {
                $isAdminRecom = $isAdminRecom == 1 ? $isAdminRecom : 0;
                $where .= " and isAdminRecom=$isAdminRecom ";
            }
        }
        $sql = "select * from __PREFIX__shops $where order by sort ASC";
        $rs = $m->pageQuery($sql);
        return $rs;
    }

    /**
     * 停止或者拒绝店铺
     */
    public function reject()
    {
        $rd = array(
            'status' => - 1
        );
        $shopId = I('id', 0);
        if ($shopId == 0)
            return rd;
        $m = M('shops');
        // 获取店铺资料
        $shops = $m->where("shopId=" . $shopId)->find();
        $data = array();
        $data['shopStatus'] = (int) I('shopStatus', - 1);
        $data['statusRemarks'] = I('statusRemarks');
        if ($this->checkEmpty($data, true)) {
            $rs = $m->where("shopId=" . $shopId)->save($data);
            if (false !== $rs) {
                // 如果[已通过的店铺]被改为停止或者拒绝的话也要停止了该店铺的商品
                if ($shops['shopStatus'] != $data['shopStatus']) {
                    $shopMessage = '';
                    if ($data['shopStatus'] != 1) {
                        $sql = "update __PREFIX__goods set isSale=0,goodsStatus=0 where shopId=" . $shopId;
                        $m->execute($sql);
                        if ($data['shopStatus'] == 0) {
                            $shopMessage = "您的店铺状态已被改为“未审核”状态，如有疑问请与商场管理员联系。";
                        } else {
                            $shopMessage = I('statusRemarks');
                        }
                    }
                    $yj_data = array(
                        'msgType' => 0,
                        'sendUserId' => session('WST_STAFF.staffId'),
                        'receiveUserId' => $shops['userId'],
                        'msgContent' => I('statusRemarks'),
                        'createTime' => date('Y-m-d H:i:s'),
                        'msgStatus' => 0,
                        'msgFlag' => 1
                    );
                    M('messages')->add($yj_data);
                }
                $rd['status'] = 1;
            }
        }
        return $rd;
    }

    /**
     * 分页列表
     */
    public function queryByPage()
    {
        $m = M('shops');
        $sql = "select serviceStartTime,scope,serviceEndTime,shopStatus,shopId,shopSn,shopName,u.userName,shopAtive,shopStatus from __PREFIX__shops s,__PREFIX__users u  
	 	     where  s.userId=u.userId and shopStatus=1 and shopFlag=1 ";
        if (I('shopName') != '')
            $sql .= " and shopName like '%" . I('shopName') . "%'";
        $sql .= " order by shopId desc";
        $info = $m->pageQuery($sql);
        foreach ($info['root'] as $k => $v) {
            $temp = explode(',', $v['scope']);
            $str = '';
            foreach ($temp as $vv) {
                if ($vv == 1) {
                    $str .= '首充号' . ',';
                }
                if ($vv == 2) {
                    $str .= '首充号代充' . ',';
                }
                if ($vv == 3) {
                    $str .= '会员首充号' . ',';
                }
                if ($vv == 4) {
                    $str .= '会员首充号代充' . ',';
                }
            }
            $str = rtrim($str, ',');
            $info['root'][$k]['scopeName'] = $str;
        }
        return $info;
    }

    /**
     * 分页列表[待审核列表]
     */
    public function queryPeddingByPage()
    {
        $m = M('shops');
        $sql = "select serviceStartTime,scope,serviceEndTime,shopStatus,shopId,shopSn,shopName,u.userName,shopAtive,shopStatus from __PREFIX__shops s,__PREFIX__users u 
	 	     where s.userId=u.userId and shopStatus<=0 and shopFlag=1";
        if (I('shopName') != '')
            $sql .= " and shopName like '%" . I('shopName') . "%'";
        if (I('shopStatus', - 999) != - 999)
            $sql .= " and shopStatus =" . (int) I('shopStatus');
        $sql .= " order by shopId desc";
        $info = $m->pageQuery($sql);
        foreach ($info['root'] as $k => $v) {
            $temp = explode(',', $v['scope']);
            $str = '';
            foreach ($temp as $vv) {
                if ($vv == 1) {
                    $str .= '首充号' . ',';
                }
                if ($vv == 2) {
                    $str .= '首充号代充' . ',';
                }
                if ($vv == 3) {
                    $str .= '首充号分销' . ',';
                }
                if ($vv == 4) {
                    $str .= '自主充值' . ',';
                }
            }
            $str = rtrim($str, ',');
            $info['root'][$k]['scopeName'] = $str;
        }
        return $info;
    }

    /**
     * 获取列表
     */
    public function queryByList()
    {
        $m = M('shops');
        $sql = "select * from __PREFIX__shops order by shopId desc";
        $rs = $m->find($sql);
    }

    /**
     * 删除
     */
    public function del()
    {
        $shopId = (int) I('id');
        $rd = array(
            'status' => - 1
        );
        $m = M('shops');
        // 下架所有商品
        $sql = "update __PREFIX__goods set isSale=0,goodsStatus=-1 where shopId=" . $shopId;
        $m->execute($sql);
        $sql = "select userId from __PREFIX__shops where shopId=" . $shopId;
        $shop = $this->queryRow($sql);
        // 删除登录账号
        $sql = "update __PREFIX__users set userFlag=-1 where userId=" . $shop['userId'];
        $m->execute($sql);
        // 标记店铺删除状态
        $data = array();
        $data["shopFlag"] = - 1;
        $data["shopStatus"] = - 2;
        $rs = $m->where("shopId=" . $shopId)->save($data);
        if (false !== $rs) {
            $rd['status'] = 1;
        }
        return $rd;
    }

    /**
     * 获取待审核的店铺数量
     */
    public function queryPenddingShopsNum()
    {
        $rd = array(
            'status' => - 1
        );
        $m = M('goods');
        $sql = "select count(*) counts from __PREFIX__shops where shopStatus=0 and shopFlag=1";
        $rs = $this->query($sql);
        $rd['num'] = $rs[0]['counts'];
        return $rd;
    }
}
;
?>