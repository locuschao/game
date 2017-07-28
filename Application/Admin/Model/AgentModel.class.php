<?php
namespace Admin\Model;

/**
 * 分销类
 */
class AgentModel extends BaseModel
{

    public function __construct()
    {
        parent::__construct();
        $info = M('agentset')->find();
        
        $this->info = $info;
    }

    public function historyOrderqueryByPage()
    {
        $m = M('goods');
        $shopName = I('shopName');
        $orderNo = I('orderNo');
        /*
         * $areaId1 = (int)I('areaId1',0);
         * $areaId2 = (int)I('areaId2',0);
         * $areaId3 = (int)I('areaId3',0);
         * $orderStatus = (int)I('orderStatus',-9999);
         * $sql = "select o.orderId,o.orderNo,o.totalMoney,o.orderStatus,o.deliverMoney,o.payType,o.createTime,s.shopName,o.userName from __PREFIX__orders o
         * left join __PREFIX__shops s on o.shopId=s.shopId where o.isAgent=1 ";
         * if($areaId1>0)$sql.=" and s.areaId1=".$areaId1;
         * if($areaId2>0)$sql.=" and s.areaId2=".$areaId2;
         * if($areaId3>0)$sql.=" and s.areaId3=".$areaId3;
         * if($shopName!='')$sql.=" and (s.shopName like '%".$shopName."%' or s.shopSn like '%".$shopName."%')";
         * if($orderNo!='')$sql.=" and o.orderNo like '%".$orderNo."%' ";
         * if($orderStatus!=-9999 && $orderStatus!=-100)$sql.=" and o.orderStatus=".$orderStatus;
         * if($orderStatus==-100)$sql.=" and o.orderStatus in(-6,-7)";
         * $sql.=" order by orderId desc";
         */
        
        $where = '';
        if ($orderNo) {
            $where .= " and o.orderNo=$orderNo ";
        }
        
        if ($shopName) {
            $where .= " and s.shopName like '%$shopName%' ";
        }
        
        $sql = "select o.signTime,o.orderType,o.payType,o.needPay,o.orderId,o.orderNo,og.goodsName,ga.gameName,v.vName,o.createTime,s.shopName,o.orderStatus,og.goodsThums
        from __PREFIX__orders as o left join __PREFIX__shops as s on o.shopId=s.shopId left join __PREFIX__order_goods as og
        on og.orderId=o.orderId  left join __PREFIX__game as ga on ga.id=og.gid
        left join __PREFIX__versions as v on v.id=og.vid where  o.isAgent=1  $where   GROUP BY o.orderId   order by o.orderId desc ";
        
        $page = $m->pageQuery($sql);
        
        foreach ($page['root'] as $k => $v) {
            if ($v['orderType'] == 1) {
                $page['root'][$k]['scope'] = '首充号';
            } else 
                if ($v['orderType'] == 2) {
                    $page['root'][$k]['scope'] = '首充号代充';
                } else 
                    if ($v['orderType'] == 3) {
                        $page['root'][$k]['scope'] = '首充号分销';
                    } else 
                        if ($v['orderType'] == 4) {
                            $page['root'][$k]['scope'] = '自主充值';
                        }
        }
        
        return $page;
    }

    public function historyOrderViewGetDetail()
    {
        $m = M('orders');
        $id = (int) I('id', 0);
        $orderId = $id;
        $data = array();
        $where = " where o.orderId = $orderId ";
        
        $sql = "select o.orderType,o.userAddress,o.roleName,o.profession,o.userPhone,o.orderNo,o.qq,og.goodsNums,o.userName,o.orderStatus,o.needPay,o.orderId,o.orderNo,og.goodsName,ga.gameName,v.vName,o.createTime,s.shopName,o.orderStatus,og.goodsThums
		from __PREFIX__orders as o left join __PREFIX__shops as s on o.shopId=s.shopId left join __PREFIX__order_goods as og
		on og.orderId=o.orderId  left join __PREFIX__game as ga on ga.id=og.gid
		left join __PREFIX__versions as v on v.id=og.vid $where order by orderId desc ";
        
        $order = $this->queryRow($sql);
        
        if ($order['orderType'] == 1 || $order['orderType'] == 2) {
            $data['fahuo'] = M('fahuo')->where(array(
                'orderId' => $orderId
            ))->select();
        }
        
        $data["order"] = $order;
        $sql = "SELECT * FROM __PREFIX__log_orders WHERE orderId = $orderId ";
        $logs = $this->query($sql);
        $data["logs"] = $logs;

        /* 16-12-23
         * 魏永就
         * 搜索获取佣金的用户
         */
        $sql = "select d.agentPrice,d.gain_price,d.level,d.goodsNums,d.bili,u.userPhone from __PREFIX__distribution_log as d left join __PREFIX__users as u on d.uid = u.userId where d.orderId = $orderId order by d.uid";
        $agentArr = $this->query($sql);
//        de($agentArr);
        $data['agentMoney'] = $agentArr;
        return $data;
    }

    /**
     * ***********************agent_user_module_star****************************
     */
    public function usersQueryByPage()
    {
        $m = M('users');
        $map = array();
        $sql = "select * from __PREFIX__users where userFlag=1 ";
        if (I('loginName') != '')
            $sql .= " and loginName LIKE '%" . I('loginName') . "%'";
        if (I('userPhone') != '')
            $sql .= " and userPhone LIKE '%" . I('userPhone') . "%'";
        if (I('userType', - 1) != - 1)
            $sql .= " and userType=" . I('userType', - 1);
        $sql .= "  order by userId desc";
        $rs = $m->pageQuery($sql);
        // 计算等级
        $userModel = M('users')->field('userId,partnerId')->select();
        if (count($rs) > 0) {
            $m = M('user_ranks');
            $urs = $m->select();
            foreach ($rs['root'] as $key => $v) {
                foreach ($urs as $rkey => $rv) {
                    if ($v['userTotalScore'] >= $rv['startScore'] && $v['userTotalScore'] < $rv['endScore']) {
                        $rs['root'][$key]['userRank'] = $rv['rankName'];
                    }
                }
            }
        }
        foreach ($rs['root'] as $key2 => $v2) {
            $rs['root'][$key2]['fansCount'] = $this->getAgentNum($userModel,$v2['userId']);
        }
        return $rs;
    }

    /**
     * @author 魏永就
     * @date 17-1-21
     * @description 获取用下级的会员
     */
    public function getAgentNum($data,$parentId)
    {
        $count = 0;
        foreach ($data as $key => $value)
        {
            if($value['partnerId'] == $parentId)
                $count++;
        }
        return $count;
    }

    /*
     * 二次开发
     * 编写者 魏永就
     *  找出要导出Excel表格的数据
     */
    public function dataSelect($start,$end)
    {
        $xlsModel = D('users');
        $userModel = M('users')->field(true)->select();
        $sql = "select userId,loginName,userName,partnerId,agentTotalPrice,agentBalance,agentWaitPrice,agentPayPrice,createTime ,userStatus,userPhone from __PREFIX__users where userFlag=1 and UNIX_TIMESTAMP(createTime) >= $start
              and UNIX_TIMESTAMP(createTime) <= $end order  by userId desc";
        $xlsData = $xlsModel->query($sql);
        foreach ($xlsData as $key2 => $v2) {
            if($v2['userStatus'] == 0)
                $xlsData[$key2]['userStatus'] = '停用';
            else
                $xlsData[$key2]['userStatus'] = '启用';
            $temp = $this->getMenuTreeDownAll($userModel, $v2['userId'], $v2['userId'], $this->info['agentLevel']);
            $xlsData[$key2]['fansCount'] = count($temp[$v2['userId']]);
            unset($temp);
        }
        return $xlsData;
    }

    private function getMenuTreeDownAll($arrCat, $parent_id = 0, $agentId = 0, $agentLevel = 0, $level = 0)
    {
        static $arrTree = array(); // 使用static代替global
        if (empty($arrCat)) {
            return FALSE;
        }
        $level ++;
        foreach ($arrCat as $key => $value) {
            // if($value['partnerId'] == $parent_id)
            if ($value['partnerId'] == $parent_id) {
                $value['level'] = $level;
                $arrTree[$agentId][] = $value;
                unset($arrCat[$key]); // 注销当前节点数据，减少已无用的遍历
                if ($level < $agentLevel || $agentLevel == 0) {
                    self::getMenuTreeDownAll($arrCat, $value['userId'], $agentId, $agentLevel, $level);
                }
            }
        }
        
        return $arrTree;
    }

    public function usersQueryByList()
    {
        $m = M('users');
        $sql = "select * from __PREFIX__users order by userId desc";
        $rs = $m->find($sql);
        return $rs;
    }

    public function usersGet()
    {
        $m = M('users');
        return $m->where("userId=" . (int) I('id'))->find();
    }

    public function usersToEdit()
    {
        $rd = array(
            'status' => - 1
        );
        $id = (int) I('id', 0);
        
        $m = M('users');
        $data = array();
        
        if ($this->checkEmpty($data, true)) {
            
            $data["partnerId"] = (int) I("partnerId");
            $data["agentTotalPrice"] = (float) I("agentTotalPrice");
            $data["agentBalance"] = (float) I("agentBalance");
            $data["agentWaitPrice"] = (float) I("agentWaitPrice");
            $data["agentPayPrice"] = (float) I("agentPayPrice");
            $rs = $m->where(array(
                'userId' => $id
            ))->save($data);
            if (false !== $rs) {
                $rd['status'] = 1;
            }
        }
        return $rd;
    }

    public function usersMoreResult()
    {
        $id = (int) I('get.id');
        if (! ($id > 0))
            return false;
        $tempData = M('users')->select();
        
        $data = $this->getMenuTreeDown($tempData, $id, 0, 1);   //魏永就     用户的会员只有下级
        $data = $this->checkUserMoreData($data);
        
        return $data;
    }

    private function checkUserMoreData($data)
    {
        foreach ($data as $key => $value) {
            if (! $value['userName']) {
                $data[$key]['userName'] = '未填写';
            }
            if (! $value['userPhone']) {
                $data[$key]['userPhone'] = '未填写';
            }
        }
        
        return $data;
    }

    public function getMenuTreeDown($arrCat, $parent_id = 0, $level = 0, $agentLevel = 0)
    {
        static $arrTree = array(); // 使用static代替global
        if (empty($arrCat)) {
            return FALSE;
        }
        $level ++;
        foreach ($arrCat as $key => $value) {
            // if($value['partnerId'] == $parent_id)
            if ($value['partnerId'] == $parent_id) {
                $value['level'] = $level;
                $arrTree[] = $value;
                unset($arrCat[$key]); // 注销当前节点数据，减少已无用的遍历
                if ($level < $agentLevel) {
                    self::getMenuTreeDown($arrCat, $value['userId'], $level, $agentLevel);
                }
            }
        }
        
        return $arrTree;
    }

    /**
     * ***********************agent_user_module_end****************************
     */
    
    /**
     * ***********************agent_order_log_module_star****************************
     */
    // 分销设置信息
    public function orderAgentSetInfo()
    {
        $info = M('agentset')->find();
        // echo M()->getLastSql();
        return $info;
    }
    
    // 计算相应商品佣金
    public function orderCountPrice($orderId)
    {
        $sql = "
SELECT
o.orderNo,
o.userId AS orderUserId,o.username AS orderUserName,
o.orderId,o.partnerId AS orderUserPartnerId,
og.goodsNums AS shopNum,og.goodsPrice AS shopPrice,
og.agentPrice AS agentProportion,
(og.goodsNums*og.goodsPrice*(og.agentPrice*0.01)) AS agentPrice,
g.goodsThums AS photo,
g.goodsName AS goodsname,
s.shopName
FROM
oto_orders AS o,
oto_order_goods AS og,
oto_goods AS g,
oto_shops AS s
WHERE
 o.orderId=og.orderId  AND g.goodsId=og.goodsId AND g.shopId=s.shopId AND  o.partnerId<>'null' AND og.agentPrice<>'null' AND  o.orderId='{$orderId}'";
        
        $info = M()->query($sql);
        // echo M()->_sql();
        
        return $info;
    }
    
    // 查询所有pid不是null的用户就是有上级的用户
    public function orderUserInfo($partnerId)
    {
        $userinfo = M('users')->field('userId,partnerId,loginName,userName')->select();
        // dump($userinfo);
        // echo M()->_sql();
        $agentInfo = $this->orderAgentSetInfo();
        $info = $this->getMenuTree($userinfo, $partnerId, 0, $agentInfo['agentLevel']);
        
        return $info;
    }

    public function orderInfo($orderId)
    {
        $info = M('orders')->where(array(
            'orderId' => $orderId
        ))->find();
        return $info;
    }

    public function getMenuTree($arrCat, $parent_id = 0, $level = 0)
    {
        static $arrTree = array(); // 使用static代替global
        if (empty($arrCat)) {
            return FALSE;
        }
        $level ++;
        foreach ($arrCat as $key => $value) {
            // if($value['partnerId'] == $parent_id)
            if ($value['userId'] == $parent_id) {
                $value['level'] = $level;
                $arrTree[] = $value;
                unset($arrCat[$key]); // 注销当前节点数据，减少已无用的遍历
                if ($level < $this->info['agentLevel']) {
                    self::getMenuTree($arrCat, $value['partnerId'], $level);
                }
            }
        }
        
        return $arrTree;
    }
    
    // 处理分佣订单and计算个个推荐人
    public function orderAgentAction($orderId)
    {
        $order = $this->orderCountPrice($orderId);
        // if(empty($order)){return false;}
        
        $orderInfo = $this->orderInfo($orderId);
        $agentset = $this->orderAgentSetInfo();
        
        $var = explode("|", $agentset['agentProportion']);
        
        $user = $this->userInfo($orderInfo['partnerId']);
        if (empty($user)) {
            return false;
        } // 下单用户没有推荐人返回
        $LOG = M('agentRevenueLog');
        $USER = M('users');
        $ORDER = M('orders');
        $LOG->startTrans();
        $USER->startTrans();
        $ORDER->startTrans();
        
        foreach ($order as $key2 => $value2) {
            foreach ($user as $key => $value) {
                $list[$key2][$key] = $value2;
                
                $list[$key2][$key] = array_merge($list[$key2][$key], $value);
                
                $data['addTime'] = time();
                
                $data['agentCount'] = (float) (($var[$key] * 0.01) * $list[$key2][$key]['agentPrice']);
                
                $data['agentAdminProportion'] = $var[$key];
                
                $list[$key2][$key] = array_merge($list[$key2][$key], $data);
                $LOG->add($list[$key2][$key]);
                
                $USER->where(array(
                    'userId' => $list[$key2][$key]['userId']
                ))->setInc('agentTotalPrice', $data['agentCount']);
                $USER->where(array(
                    'userId' => $list[$key2][$key]['userId']
                ))->setInc('agentBalance', $data['agentCount']);
            }
        }
        $check = $ORDER->where(array(
            'orderId' => $orderId
        ))->setField('agentStauts', 1);
        if ($check) {
            $LOG->commit();
            $USER->commit();
            $ORDER->commit();
        } else {
            $ORDER->rollback();
            $LOG->rollback();
            $USER->rollback();
        }
        
        return $check;
    }

    public function orderQueryByPage()
    {
        $m = M('goods');
        $shopName = I('shopName');
        $orderNo = I('orderNo');
        $areaId1 = (int) I('areaId1', 0);
        $areaId2 = (int) I('areaId2', 0);
        $areaId3 = (int) I('areaId3', 0);
        $orderStatus = (int) I('orderStatus', - 9999);
        $sql = "select o.orderId,o.orderNo,o.totalMoney,o.orderStatus,o.deliverMoney,o.payType,o.createTime,s.shopName,o.userName,o.agentStauts from __PREFIX__orders o
	 	         left join __PREFIX__shops s on o.shopId=s.shopId  where o.orderFlag=1 AND o.partnerId<>'null' and  o.orderStatus=4";
        if ($areaId1 > 0)
            $sql .= " and s.areaId1=" . $areaId1;
        if ($areaId2 > 0)
            $sql .= " and s.areaId2=" . $areaId2;
        if ($areaId3 > 0)
            $sql .= " and s.areaId3=" . $areaId3;
        if ($shopName != '')
            $sql .= " and (s.shopName like '%" . $shopName . "%' or s.shopSn like '%" . $shopName . "%')";
        if ($orderNo != '')
            $sql .= " and o.orderNo like '%" . $orderNo . "%' ";
        if ($orderStatus != - 9999 && $orderStatus != - 100)
            $sql .= " and o.orderStatus=" . $orderStatus;
        if ($orderStatus == - 100)
            $sql .= " and o.orderStatus in(-6,-7)";
        $sql .= " order by orderId desc";
        $page = $m->pageQuery($sql);
        // 获取涉及的订单及商品
        if (count($page['root']) > 0) {
            $orderIds = array();
            foreach ($page['root'] as $key => $v) {
                $orderIds[] = $v['orderId'];
            }
            $sql = "select og.orderId,og.goodsThums,og.goodsName,og.goodsId from __PREFIX__order_goods og
			        where og.orderId in(" . implode(',', $orderIds) . ")";
            $rs = $this->query($sql);
            $goodslist = array();
            foreach ($rs as $key => $v) {
                $goodslist[$v['orderId']][] = $v;
            }
            foreach ($page['root'] as $key => $v) {
                $page['root'][$key]['goodslist'] = $goodslist[$v['orderId']];
            }
        }
        return $page;
    }

    public function orderListQuery($orderdata)
    {
        if (count($orderdata) > 0) {
            foreach ($orderdata as $key => $value) {
                
                $psql = "SELECT * from __PREFIX__orders as o,__PREFIX__users as u where o.orderId={$value['orderId']} and  u.userId=o.userId and u.partnerId  is not null";
                $preslut = M()->query($psql);
                if ($preslut) {
                    
                    $orderIds[] = $value['orderId'];
                }
            }
            
            $sql = "select o.shopId,o.orderId,o.orderNo,o.totalMoney,o.orderStatus,o.deliverMoney,o.payType,o.createTime,s.shopName,o.userName from __PREFIX__orders o
	 	         left join __PREFIX__shops s on o.shopId=s.shopId  where  o.orderId IN (" . implode(',', $orderIds) . ")";
            
            $page = $this->pageQuery($sql);
            
            foreach ($page['root'] as $key => $v) {
                $orderIds[] = $v['orderId'];
            }
            $sql = "select og.orderId,og.goodsThums,og.goodsName,og.goodsId from __PREFIX__order_goods og
			        where og.orderId in(" . implode(',', $orderIds) . ")";
            $rs = $this->query($sql);
            $goodslist = array();
            foreach ($rs as $key => $v) {
                $goodslist[$v['orderId']][] = $v;
            }
            foreach ($page['root'] as $key => $v) {
                $page['root'][$key]['goodslist'] = $goodslist[$v['orderId']];
            }
        }
        
        return $page;
    }
    
    /**
     * @author peng	
     * @date 2017-01-05
     * @descreption 对原来的进行修改
     */    
    // 返回满足条件的订单 --天数--未分佣
    public function orderCheckStatus()
    {
        $sql = "SELECT
            	og.*,o.*
            FROM
            	__PREFIX__orders AS o
            LEFT JOIN __PREFIX__order_goods AS og ON og.orderId = o.orderId
            
            WHERE
            	o.orderStatus = '3'
            AND o.is_fencheng = 1
            AND o.isAgent = 0
            GROUP BY
            	o.orderId
            ORDER BY
            	o.orderId DESC";
        
        /*$sql = "select o.signTime,o.orderType,o.payType,o.needPay,o.orderId,o.orderNo,og.goodsName,ga.gameName,v.vName,o.createTime,s.shopName,o.orderStatus,og.goodsThums
        from __PREFIX__orders as o left join __PREFIX__shops as s on o.shopId=s.shopId left join __PREFIX__order_goods as og
        on og.orderId=o.orderId  left join __PREFIX__game as ga on ga.id=og.gid
        left join __PREFIX__versions as v on v.id=og.vid  left join __PREFIX__autocomfirm as at on at.orderId=o.orderId  where o.orderStatus='3' and s.agentStatus=1 and o.isAgent=0 and o.orderFlag=1    and at.agentMoney>0    GROUP BY o.orderId   order by o.orderId desc ";
        */
        $tempdata = M()->query($sql);
        
        /*foreach ($tempdata as $k => $v) {
            if ($v['orderType'] == 1) {
                $tempdata[$k]['scope'] = '首充号';
            } else 
                if ($v['orderType'] == 2) {
                    $tempdata[$k]['scope'] = '首充号代充';
                } 
        }
      
        foreach ($tempdata as $key => $value) {
            
            if ($value['signTime']) {
                $logTime = strtotime($value['signTime']);
                $nowTime = time();
                $intervalTime = $this->info['agentLogDay'] * 86400;
                if (($nowTime - $logTime) < $intervalTime) {
                    unset($tempdata[$key]);
                }
            } else {
                unset($tempdata[$key]);
            }
        }*/
        
        return $tempdata;
    }        
    
    

    /**
     * 获取订单详细信息
     */
    public function orderGetDetail()
    {
        $m = M('orders');
        $id = (int) I('id', 0);
        $orderId = $id;
        $data = array();
        $where = " where o.orderId = $orderId ";
        
        $sql = "select o.orderType,o.userAddress,o.roleName,o.profession,o.userPhone,o.orderNo,o.qq,og.goodsNums,o.userName,o.orderStatus,o.needPay,o.orderId,o.orderNo,og.goodsName,ga.gameName,v.vName,o.createTime,s.shopName,o.orderStatus,og.goodsThums
		from __PREFIX__orders as o left join __PREFIX__shops as s on o.shopId=s.shopId left join __PREFIX__order_goods as og
		on og.orderId=o.orderId  left join __PREFIX__game as ga on ga.id=og.gid
		left join __PREFIX__versions as v on v.id=og.vid $where order by orderId desc ";
        
        $order = $this->queryRow($sql);
        
        if ($order['orderType'] == 1 || $order['orderType'] == 2) {
            $data['fahuo'] = M('fahuo')->where(array(
                'orderId' => $orderId
            ))->select();
        }
        
        $data["order"] = $order;
        $sql = "SELECT * FROM __PREFIX__log_orders WHERE orderId = $orderId ";
        $logs = $this->query($sql);
        $data["logs"] = $logs;
        
        return $data;
    }

    public function orderActionReturnMoney()
    {
        $id = (int) I('post.orderId');
//        $id = 409;
        $fhTime = M('orders')->field('UNIX_TIMESTAMP(fahuoTime) as ftime')->where('orderId='.$id)->find();
//        de($fhTime);
        if(time()-$fhTime['ftime'] < C('dividedTime'))
        {
            $status = array(
                'status',
                false,
                'error' => '现在处于距发货7天内，不能分成'
            );
            return $status;
        }
        if ($id < 0) {
            $status = array(
                'status',
                false,
                'error' => '非法操作'
            );
            return $status;
        }
        // echo '<meta charset="utf-8">';
        
        if ($id > 0 && $this->info['status'] == 1) {
            $sql = "select shops.agentStatus,orders.userId from __PREFIX__orders as orders,__PREFIX__shops as shops where orders.orderId='{$id}' and shops.shopId=orders.shopId";
            $result = M()->query($sql);
            
            // dump(!!!($result[0]['agentStatus']==1));
            // dump($result[0]);
            if (! ! ! ($result[0]['agentStatus'] == 1)) {
                // dump($result);
                $status = array(
                    'status',
                    false,
                    'error' => '该店铺没开启分销模块'
                );
            } else {
                
                $price = $this->orderAboutMoney($id);
                $users = $this->orderAgentUser($result[0]['userId']);
                $res = $this->orderAgentEnd($price, $users);
                if ($res) {
                    $status = array(
                        'status' => $res
                    );
                } else {
                    $status = array(
                        'status' => $res,
                        'error' => '分佣失败请检查数据'
                    );
                }
                //
            }
        } else {
            $status = array(
                'status' => false,
                'error' => '平台未开启分销模块'
            );
        }
        
        return $status;
    }
    
    // 计算等级相应金额
    /**
     *
     * @param
     *            $orderid
     * @return array 返回订单商品数量*平台设置比例*商品分佣价格
     */
    private function orderAboutMoney($orderid)
    {
        $reslut = M('order_goods')->where(array(
            'orderId' => $orderid
        ))->select();
        $payUser = M('orders')->where(array(
            'orderId' => $orderid
        ))
            ->field('userId,createTime,orderNo')
            ->find();
        $goodsModel = M('goods');
        $bili = explode('|', $this->info['agentProportion']);
        $data = array();
        
        $info=M('autocomfirm')->where(array('orderId'=>$orderid))->find();
        
        //只有一个商品，因为没有购物车，这里只循环一次
        foreach ($reslut as $key => $value) {
            $goodsInfo = $goodsModel->where(array(
                'goodsId' => $value['goodsId']
            ))->find();
            $agentPrice = round($goodsInfo['agentPrice'] * $value['goodsNums'], 2);
            foreach ($bili as $key2 => $value2) {
                if ($key2 < $this->info['agentLevel']) {
                    $data[$key]['orderId'] = $orderid;
                    $data[$key]['goodsId'] = $value['goodsId'];
                    $data[$key]['cid'] = $payUser['userId'];
                    $data[$key]['time'] = strtotime($payUser['createTime']);
                    $data[$key]['orderNo'] = $payUser['orderNo'];
                    $data[$key]['goodsNums'] = $value['goodsNums'];
                    $data[$key]['goodsThums'] = $goodsInfo['goodsThums'];
                    $data[$key]['goodsName'] = $value['goodsName'];
                    //$data[$key]['agentPrice'] = $goodsInfo['agentPrice'];
                    $data[$key]['agentPrice'] = $info['agentMoney'];//读取是自动收货时，分出来的佣金
                    $data[$key]['goodsContent'] = $value['goodsName'] . '(' . $goodsInfo['goodsDesc'] . ')购买了' . $value['goodsNums'] . $goodsInfo['goodsUnit'];
                    $data[$key]['bili'] = serialize($bili);
                    $data[$key]['price'][$key2] = round((((float) $value2 / 100) * $agentPrice), 2);
                    // echo '商品:'.$value['goodsName'].((float)$value2/100).'*'.$agentPrice.'='.((float)$value2/100)*$agentPrice.' ';
                    // echo '\t';
                }
            }
        }
        // print_r($data);
        return $data;
    }

    private function orderAgentUser($uid)
    {
        $users = M('users')->field('userId,partnerId,loginName,userName')->select();
        $pid = M('users')->where(array(
            'userId' => $uid
        ))->find();
        
        $userinfo = $this->getMenuTree($users, $pid['partnerId']);
        
        return $userinfo;
    }

    private function orderAgentEnd($goods, $users)
    {
        M()->startTrans();
        // print_r($goods);
        $BalanceStatus = true;
        $re = false;
        $data = array();
        $orderid = '';
        $ndata = array();
        $time = time();
        $i = 0;
        $priceAll = 0;
        foreach ($users as $key => $value) {
            foreach ($goods as $key2 => $value2) {
                
                $data['orderId'] = $value2['orderId'];
                $orderid = $value2['orderId'];
                $data['goodsId'] = $value2['goodsId'];
                $data['goodsName'] = $value2['goodsName'];
                $data['goodsThums'] = $value2['goodsThums'];
                // $data['goodsThums'] = $value2['goodsThums'];
                $data['uid'] = $value['userId'];
                $data['cid'] = $value2['cid'];
                
                $data['agentPrice'] = $value2['agentPrice'];
                $data['goodsContent'] = $value2['goodsContent'];
                $data['gain_price'] = $value2['price'][$i];
                $data['loginName'] = $value['loginName'];
                $data['level'] = $value['level'];
                $data['goodsNums'] = $value2['goodsNums'];
                $bili = unserialize($value2['bili']);
                
                $data['bili'] = $bili[$i];
                $data['time'] = $value2['time'];
                
                // $Balance = M('users')->where(array('userId' => $data['uid']))->setInc('agentBalance', $data['gain_price']);
                // $sql =$USERS->getLastSql();
                
                $sql = "UPDATE  `__PREFIX__users` SET agentBalance=agentBalance+{$data['gain_price']},agentTotalPrice=agentTotalPrice+{$data['gain_price']} where `userId`= {$data['uid']};";
                // $sql.="UPDATE `__PREFIX__users` SET agentTotalPrice=agentTotalPrice+{$data['gain_price']} where `userId`= {$data['uid']}";
                // $Balance = M('users')->where(array('userId' => $data['uid']))->setInc('agentBalance', $data['gain_price']);
                //计算分销者佣金加起来的总和
                $priceAll = $priceAll + $data['gain_price'];

                $Balance = M()->query($sql);
                if ($Balance === false) {
                    $BalanceStatus = false;
                }
                $ndata[] = $data;
            }
            
            $i ++;
        }
        /**
         * 魏永就
         * distribution_log 记录平台获取分销分成后的金额
         */
        $pfData['uid'] = 0;
        $pfData['orderId'] = $goods[0]['orderId'];
        $pfData['goodsName'] = $goods[0]['goodsName'];
        $pfData['goodsId'] = $goods[0]['goodsId'];
        $pfData['goodsThums'] = $goods[0]['goodsThums'];
        $pfData['goodsContent'] = $goods[0]['goodsContent'];
        $pfData['gain_price'] = $goods[0]['agentPrice'] - $priceAll;
        $pfData['goodsNums'] = $goods[0]['goodsNums'];
        $pfData['agentPrice'] = $goods[0]['agentPrice'];
        $pfData['level'] = 0;
        $pfData['time'] = $goods[0]['time'];
        $r = M('distribution_log')->add($pfData);


        $res = M('distribution_log')->addAll($ndata);

//        $sql = "UPDATE  `__PREFIX__orders` SET isAgent=1 where `orderId`= {$orderid}";
//        de($sql);
//        $orderStatus = M()->query($sql);
        $orderStatus = M('orders')->where('orderId='.$orderid)->setField('isAgent',1);

        //记录平台金额流水
        $platRes = M('platfrom_account')->add(array(
            'orderId'   =>  $goods[0]['orderId'],
            'time'      =>  time(),
            'income'    =>  $pfData['gain_price'],
            'remark'    =>  '管理员操作，分成成功',
            'orderNo'   =>  $goods[0]['orderNo']
        ));
//        de($platRes);
        //更新平台暂时的金额
        $dataTemRes = M('data_tmp')->where('id=1')->setInc('value',$pfData['gain_price']);
        // $orderStatus;
        // $d['isAgent']=1;
        // $orderStatus = M('orders')->where(array('orderId'=>$orderid))->save($d);
        // $sql= M('orders')->getLastSql();
        if ($BalanceStatus && $res && $r && $orderStatus && $platRes && $dataTemRes !== false) {
            
            M()->commit();
            $re = true;
        } else {
            M()->rollback();
        }
        
        // print_r($re);
        
        return $re;
    }

    /**
     * ***********************agent_order_log_module_end****************************
     */
    
    /**
     * *****************agent_apply_module_star********************
     */
    public function applyList()
    {
        $data = array();
        
        $tempdata = M('agentApply')->select();
        
        foreach ($tempdata as $key => $value) {
            
            // $value['action']="<button class='btn btn-info' id=".$value['userId'].">".'通过'."</button>".'&nbsp;&nbsp;&nbsp;'."<button class='btn btn-success' id=".$value['userId'].">".'处理'."</button>";
            $value['action'] = $this->applycheckAction($value['status'], $value['id']);
            $data[$key]['action'] = $value['action'];
            
            $value['time'] = date("Y-m-d", $value['time']);
            $value['status'] = $this->applycheckStatus($value['status']);
            $data[$key]['time'] = $value['time'];
            $data[$key]['status'] = $value['status'];
            $value['applyType'] = $this->applyTypeAction($value['applyType']);
            $data[$key]['applyType'] = $value['applyType'];
            
            $data[$key] = $value;
        }
        
        // dump(($data));
        
        return $data;
    }

    public function applyCheckStatus($status)
    {
        switch ($status) {
            case 0:
                $status = '待处理';
                break;
            
            case 1:
                $status = '处理中';
                break;
            case 2:
                $status = '通过';
                break;
            
            case 3:
                $status = '不通过';
                break;
            default:
                
                break;
        }
        
        return $status;
    }

    public function applyTypeAction($status)
    {
        switch ($status) {
            case 0:
                $status = '银行转账';
                break;
            case 1:
                $status = '充值余额';
                break;
            default:
                
                break;
        }
        
        return $status;
    }

    public function applyCheckAction($status, $id)
    {
        switch ($status) {
            case 0:
                $action = "<button class='btn btn-info btn-sm' onclick='changeStatus($id,1)'>" . '处理' . "</button>";
                break;
            
            case 1:
                $action = "<button class='btn btn-success btn-sm' onclick=changeStatus($id,2)>" . '完成' . "</button>" . '&nbsp;&nbsp;&nbsp;' . "<button class='btn btn-danger btn-sm' onclick='changeStatus($id,3)'>" . '故障' . "</button>";
                break;
            
            case 2:
                $action = "<button class='btn btn-success btn-sm' >" . '完成' . "</button>";
                
                break;
            
            case 3:
                $action = "<button class='btn btn-danger btn-sm'>" . '故障' . "</button>";
                
                break;
            default:
                
                break;
        }
        
        return $action;
    }

    public function applyCheckEdit()
    {
        $USERS = M('users');
        $AGENTAPPLY = M('agentApply');
        $AGENTAPPLYLOG = M('agentApplyLog');
        $ADMIN = session('WST_STAFF');
        
        // $AGENTAPPLY->startTrans();
        
        $id = (int) I('post.id');
        $status = (int) I('post.status');
        
        $tempdata = $AGENTAPPLY->where(array(
            'id' => $id
        ))->setField('status', $status);
        $applyData = $AGENTAPPLY->where(array(
            'id' => $id
        ))->find();
        if ($tempdata) {
            
            $addData['action'] = $this->applyCheckStatus($status);
            $addData['applyOrderId'] = $id;
            $addData['adminId'] = $ADMIN['staffId'];
            $addData['adminName'] = $ADMIN['loginName'];
            $addData['ip'] = $ADMIN['lastIP'];
            $addData['userName'] = $applyData['loginName'];
            $addData['userId'] = $applyData['userId'];
            $addData['roleName'] = $ADMIN['roleName'];
            $addData['userType'] = $applyData['userType'];
            $addData['addtime'] = time();
            
            $data = $AGENTAPPLYLOG->add($addData);
            
            if ($status == 2 or $status == 3) {
                
                $WaitPrice = $USERS->where(array(
                    'userId' => $applyData['userId']
                ))->setDec('agentWaitPrice', $applyData['applyPrice']);
                if ($status == 2) {
                    if ($applyData['applyType'] == 1) { // 转到余额
                        $pay = $USERS->where(array(
                            'userId' => $applyData['userId']
                        ))->setInc('agentPayPrice', $applyData['applyPrice']);
                        // 用户余额增加
                        M('users')->where(array(
                            'userId' => $applyData['userId']
                        ))->setInc('userMoney', $applyData['applyPrice']);
                        // 添加余额变动记录
                        $balance=M('users')->where(array(
                            'userId' => $applyData['userId']
                        ))->getField('userMoney');
                        $bdata['type'] = 11;
                        $bdata['money'] = $applyData['applyPrice'];
                        $bdata['time'] = time();
                        $bdata['ip'] = get_client_ip();
                        $bdata['orderNo'] = $applyData['id'];
                        $bdata['IncDec'] = 1;
                        $bdata['userid'] = $applyData['userId'];
                        $bdata['balance'] = $balance;
                        $bdata['remark'] = '分销提现到余额';
                        $bdata['payWay'] = 0;
                        M('money_record')->add($bdata);
                        //
                    } else {
                        $pay = $USERS->where(array(
                            'userId' => $applyData['userId']
                        ))->setInc('agentPayPrice', $applyData['applyPrice']);
                    }
                } elseif ($status == 3) {
                    $Balance = $USERS->where(array(
                        'userId' => $applyData['userId']
                    ))->setInc('agentBalance', $applyData['applyPrice']);
                }
                
                /*
                 * if($WaitPrice && $pay && $Balance ){
                 * $AGENTAPPLY-commit();
                 *
                 * }else{
                 * $AGENTAPPLY->rollback();
                 * }
                 */
            }
        }
        return $data;
    }

    /**
     * *****************agent_apply_module_end********************
     */
    
    /**
     * *************agent_setting_module_star***************
     */
    // 分销设置-编辑
    public function settingEdit()
    {
        $rd = array(
            'status' => - 1
        );
        $id = I("id", 0);
        $data = array();
        $data["status"] = (int) I("status");
        $data["agentLevel"] = (int) I("agentLevel");
        $data["minApplyPrice"] = (int) I("minApplyPrice");
        $data["maxApplyPrice"] = (int) I("maxApplyPrice");
        $data["applyPw"] = I("applyPw");
        $data["applyDay"] = (int) I("applyDay");
        $data["agentProportion"] = I("agentProportion");
        $data["agentLogDay"] = (int) I("agentLogDay");
        $data["agentLogStatus"] = I("agentLogStatus");
        if ($this->checkEmpty($data)) {
            $m = M('agentset');
            $rs = $m->where("id=1")->save($data);
            if (false !== $rs) {
                $rd['status'] = 1;
            }
        }
        return $rd;
    }
    
    // 分销设置-查看
    public function settingGet()
    {
        $m = M('agentset')->where(array(
            'id' => 1
        ))->find();
        return $m;
    }
    
    // 分销设置-分页
    public function settingQueryByPage()
    {
        $m = M('agentset');
        $sql = "select * from oto_agentset";
        return $m->pageQuery($sql);
    }
    
    // 分销设置-列表
    public function settingQueryByList()
    {
        $m = M('agentset');
        $sql = "select * from oto_agentset order by id desc";
        return $m->select($sql);
    }
    
    // 分销设置-是否启用分销功能模块
    public function settingEditIsStatus()
    {
        $rd = array(
            'status' => - 1
        );
        $m = M('agentset');
        $m->status = ((int) I('status') == 1) ? 1 : 0;
        if ($m->status == 0) {
            
            $sql = "UPDATE  `__PREFIX__shops` SET agentStatus=0";
            
            $reslut = M()->query($sql);
        }
        $rs = $m->where("id=1")->save();
        if (false !== $rs) {
            $rd['status'] = 1;
        }
        return $rd;
    }
/**
 * *************agent_setting_end***************
 */
}
;
?>